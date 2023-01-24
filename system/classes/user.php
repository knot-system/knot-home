<?php

class User {


	private $user_id;


	function __construct( $sekretaer ) {

		$user_id = false;

		if( ! empty($_SESSION['user_id']) ) $user_id = $_SESSION['user_id'];

		$this->user_id = $user_id;
	
		return $this;
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

		global $sekretaer;

		$indieauth = new IndieAuth();

		$scope = $sekretaer->config->get( 'scope' );

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


		if( $autologin ) {

			$cookie_session_id = uniqid();

			global $sekretaer;

			$cookie = new Cache( 'session', $cookie_session_id, true );
			$cookie->add_data( json_encode($_SESSION) );

			$cookie_lifetime = $sekretaer->config->get('cookie_lifetime');

			setcookie( 'sekretaer-session', $cookie_session_id, array(
				'expires' => time()+$cookie_lifetime,
				'path' => '/'
			));

		}

		return $this;
	}


	function create_short_name( $me ) {

		$short_name = str_replace( array('http://www.', 'https://www.', 'http://', 'https://'), '', $me );
		$short_name = trim( $short_name, '/' );

		return $short_name;
	}


	function logout() {

		if( ! empty($_COOKIE['sekretaer-session']) ) {
			$cookie_session_id = $_COOKIE['sekretaer-session'];

			$cache = new Cache( 'session', $cookie_session_id, true );
			$cache->remove();

			setcookie( 'sekretaer-session', null, array(
				'expires' => -1,
				'path' => '/'
			));
		}

		session_destroy();

		$this->user_id = false;

		return $this;
	}


}
