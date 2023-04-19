<?php


class User {

	private $user_id;
	private $fields = [];

	function __construct() {

		if( empty($_SESSION['user_id']) ) return;

		$user_id = $_SESSION['user_id'];
		$this->user_id = $user_id;


		global $core;

		if( ! isset($_SESSION['_version']) || $_SESSION['_version'] != $core->version() ) {
			// was logged in in an old version, reset session
			$this->logout();
		}


		// check, if me is allowed
		$allowed_user_ids = $core->config->get('allowed_urls');
		if( is_array($allowed_user_ids) && count($allowed_user_ids) ) {
			$canonical_user_id = un_trailing_slash_it($user_id);

			$allowed_user_ids = array_map( 'un_trailing_slash_it', $allowed_user_ids );

			if( ! in_array($canonical_user_id, $allowed_user_ids) ) {
				$core->debug( 'this url is not allowed', $user_id );
				$this->logout();
			}

		}


		$fields = [];
		// TODO: get info from session cache file instead of $_SESSION variable; $_SESSION variable should only contain the user id and session id in the future
		// TODO: $fields['me'] & $fields['user_id'] are the same values, do we need both fields?
		$fields['user_id'] = $_SESSION['user_id'];
		$fields['me'] = $_SESSION['me'];
		$fields['name'] = $_SESSION['name'];
		if( ! empty($_SESSION['access_token']) ) {
			$fields['access_token'] = $_SESSION['access_token'];
		}
		if( ! empty($_SESSION['scope']) ) {
			$fields['scope'] = $_SESSION['scope'];
		}
		if( ! empty($_SESSION['microsub_endpoint']) ) {
			$fields['microsub_endpoint'] = $_SESSION['microsub_endpoint'];
		}
		if( ! empty($_SESSION['micropub_endpoint']) ) {
			$fields['micropub_endpoint'] = $_SESSION['micropub_endpoint'];
		}
		$this->fields = $fields;


	}


	function authorized() {
		if( $this->user_id ) {
			return true;
		}

		return false;
	}


	function authorize( $post ) {

		$url = false;

		if( ! empty($post['url']) ) $url = $post['url'];

		global $core;

		if( ! empty($post['rememberurl']) && $post['rememberurl'] == 'true' ) {

			$cookie_lifetime = $core->config->get('cookie_lifetime');

			setcookie( 'sekretaer-url', $url, array(
				'expires' => time()+$cookie_lifetime,
				'path' => $core->basefolder
			));

		} elseif( isset($_COOKIE['sekretaer-url']) ) {

			setcookie( 'sekretaer-url', null, array(
				'expires' => -1,
				'path' => $core->basefolder
			));

		}

		$indieauth = new IndieAuth();

		$scope = $core->config->get( 'scope' );

		$authorization_url = $indieauth->login( $url, $scope );

		if( ! empty($authorization_url['error']) ) {
			php_redirect( '?error='.$authorization_url['error'] );
			exit;
		}

		$autologin = false;
		if( ! empty($post['autologin']) && $post['autologin'] == 'true' ) {
			$autologin = true;
		}
		$_SESSION['login_set_autologin'] = $autologin;

		// Redirect user to their authorization endpoint, which will then redirect to the '/action/redirect/' URL
		header( 'Location: '.$authorization_url );
		exit;
	}

	function login() {

		$indieauth = new IndieAuth();

		$response = $indieauth->complete( $_GET );

		$autologin = $_SESSION['login_set_autologin'];
		unset($_SESSION['login_set_autologin']);


		if( ! empty($response['error']) ) {
			php_redirect( '?error='.$response['error'] );
			exit;
		}

		if( ! empty($response['response']['access_token']) ) {
			$_SESSION['access_token'] = $response['response']['access_token'];
		}
		if( ! empty($response['response']['scope']) ) {
			$_SESSION['scope'] = $response['response']['scope'];
		}
		if( ! empty($response['microsub_endpoint']) ) {
			$_SESSION['microsub_endpoint'] = $response['microsub_endpoint'];
		}
		if( ! empty($response['micropub_endpoint']) ) {
			$_SESSION['micropub_endpoint'] = $response['micropub_endpoint'];
		}

		$this->user_id = $response['me'];
		$_SESSION['user_id'] = $response['me'];

		$_SESSION['me'] = $response['me'];
		$_SESSION['name'] = $this->create_short_name( $response['me'] );


		global $core;
		$_SESSION['_version'] = $core->version();


		if( $autologin ) {

			$cookie_session_id = uniqid();

			global $core;

			$session_data = $_SESSION;
			unset($session_data['login_redirect_path']);
			$session_data = json_encode($session_data);

			$cookie = new Cache( 'session', $cookie_session_id, true );
			$cookie->add_data( $session_data );

			$cookie_lifetime = $core->config->get('cookie_lifetime');

			setcookie( 'sekretaer-session', $cookie_session_id, array(
				'expires' => time()+$cookie_lifetime,
				'path' => $core->basefolder
			));

		}

		return $this;
	}


	function create_short_name( $me ) {

		$short_name = str_replace( array('http://www.', 'https://www.', 'http://', 'https://'), '', $me );
		$short_name = trim( $short_name, '/' );

		return $short_name;
	}


	function get( $field ) {

		if( ! array_key_exists($field, $this->fields) ) return false;

		return $this->fields[$field];
	}


	function logout() {

		global $core;

		if( ! empty($_COOKIE['sekretaer-session']) ) {
			$cookie_session_id = $_COOKIE['sekretaer-session'];

			$cache = new Cache( 'session', $cookie_session_id, true, 20 );
			$cache->remove();

			setcookie( 'sekretaer-session', null, array(
				'expires' => -1,
				'path' => $core->basefolder
			));
		}

		session_destroy();

		$this->user_id = false;

		return $this;
	}


}
