<?php


class User {

	private $session_id = false;
	private $user_id = false;
	private $fields = [];

	function __construct() {

		if( empty($_SESSION['session_id']) ) return;

		$session_id = $_SESSION['session_id'];

		if( ! $session_id ) {
			return;
		}

		$this->session_id = $session_id;

		if( ! $this->load_session_data() ) {
			return;
		}

	}


	function verify_access_token() {

		$session_cache = new Cache( 'session', $this->session_id, true );
		$session_data = $session_cache->get_data();

		if( ! $session_data ) {
			return false;
		}

		$session_data = json_decode($session_data, true);

		if( empty($session_data['me']) || empty($session_data['access_token']) ) {
			return false;
		}

		$me = $session_data['me'];
		$access_token = $session_data['access_token'];

		$refresh_token = false;
		if( ! empty($session_data['refresh_token']) ) $refresh_token = $session_data['refresh_token'];


		$indieauth = new IndieAuth();
		$url = $indieauth->normalize_url( $me );
		$token_endpoint = $indieauth->discover_endpoint( 'token_endpoint', $url );
		if( ! $token_endpoint ) {
			return false;
		}

		$this->token_endpoint = $token_endpoint;

		$token_endpoint_request = new Request( $token_endpoint );
		$token_endpoint_request->set_headers([
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		]);
		$token_endpoint_request->curl_request();

		$status_code = $token_endpoint_request->get_status_code();
		$headers = $token_endpoint_request->get_headers();
		$token_response = $token_endpoint_request->get_body();

		if( ! empty($headers['content-type']) && $headers['content-type'] == 'application/json' ) {
			$token_response = json_decode( $token_response, true );
		} else {
			// fallback to x-www-form-urlencoded
			$token_response = decode_formurlencoded( $token_response );
		}

		$access_token_valid = true;
		
		if( isset($token_response['active']) && ! $token_response['active'] ) {
			$access_token_valid = false;
		}

		if( ! isset($token_response['me']) || ! isset($token_response['scope']) ) {
			$access_token_valid = false;
		}

		if( ! $access_token_valid && $refresh_token ) {

			// access_token is no longer valid, but we have a refresh_token;
			// use the refresh_token to get a new access_token (& new refresh_token):

			$refresh_token_request = new Request( $token_endpoint );
			$refresh_token_request->set_post_data([
				'grant_type' => 'refresh_token',
				'refresh_token' => $refresh_token,
				'client_id' => $indieauth->client_id(),
			]);
			$refresh_token_request->curl_request();

			$refresh_token_response = $refresh_token_request->get_body();

			if( $refresh_token_response ) {
				$refresh_token_response = json_decode($refresh_token_response, true);

				if( ! empty($refresh_token_response['access_token']) && $refresh_token_response['access_token'] == true ) {
					
					// update current session with new access_token
					$session_data['access_token'] = $refresh_token_response['access_token'];
					if( ! empty($session_data['refresh_token']) ) $session_data['refresh_token'] = $refresh_token_response['refresh_token'];
					$session_data['scope'] = $refresh_token_response['scope'];
					$session_data['me'] = $refresh_token_response['me'];

					$session_cache->add_data( json_encode($session_data) );

					$access_token_valid = true;
				}
			}


		}

		return $access_token_valid;
	}


	function load_session_data() {

		$session_cache = new Cache( 'session', $this->session_id, true );
		$session_data = $session_cache->get_data();

		$session_cache->refresh_lifetime();


		if( ! $session_data ) {
			return false;
		}

		$session_data = json_decode($session_data, true);

		if( empty($session_data['me']) ) {
			return false;
		}



		// every X minutes check if access_token is still valid
		$access_token_stale_threshold = 60*5; // 1 minute in seconds
		if( empty($session_data['last_access_token_check']) || (time() - $session_data['last_access_token_check']) > $access_token_stale_threshold ) {

			if( $this->verify_access_token() ) {

				// refresh session data, because refresh_token could have been used:
				$session_data = $session_cache->get_data();
				$session_data = json_decode($session_data, true);

				$session_data['last_access_token_check'] = time();
				$session_cache->add_data( json_encode($session_data) );

			} else {
				$this->logout();
				return false;
			}

		}


		$this->user_id = $session_data['me'];


		global $core;
		if( ! isset($session_data['_version']) || $session_data['_version'] != $core->version() ) {
			// was logged in in an old version, reset session
			$this->logout();
		}


		// check, if me is allowed
		$allowed_user_ids = get_config('allowed_urls');
		if( is_array($allowed_user_ids) && count($allowed_user_ids) ) {
			$canonical_user_id = un_trailing_slash_it($user_id);

			$allowed_user_ids = array_map( 'un_trailing_slash_it', $allowed_user_ids );

			if( ! in_array($canonical_user_id, $allowed_user_ids) ) {
				$core->debug( 'this url is not allowed', $user_id );
				$this->logout();
			}

		}


		$this->fields = $session_data;

		return true;
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


		$cookie = new Cookie( 'sekretaer-url' );
		if( ! empty($post['rememberurl']) && $post['rememberurl'] == 'true' ) {

			$cookie->set($url);

		} else {

			$cookie->remove();

		}

		$indieauth = new IndieAuth();

		global $core;

		$scope = get_config( 'scope' );

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


		$session_data = [];

		if( ! empty($response['response']['access_token']) ) {
			$session_data['access_token'] = $response['response']['access_token'];
		}
		if( ! empty($response['response']['refresh_token']) ) {
			$session_data['refresh_token'] = $response['response']['refresh_token'];
		}
		if( ! empty($response['response']['scope']) ) {
			$session_data['scope'] = $response['response']['scope'];
		}
		if( ! empty($response['microsub_endpoint']) ) {
			$session_data['microsub_endpoint'] = $response['microsub_endpoint'];
		}
		if( ! empty($response['micropub_endpoint']) ) {
			$session_data['micropub_endpoint'] = $response['micropub_endpoint'];
		}

		$this->user_id = $response['me'];
		$session_data['user_id'] = $response['me'];

		$session_data['me'] = $response['me'];
		$session_data['name'] = $this->create_short_name( $response['me'] );


		global $core;
		$session_data['_version'] = $core->version();


		$session_id = get_hash( uniqid() );


		$session_data['autologin'] = false;

		if( $autologin ) {

			$session_data['autologin'] = true;

			// this is the cookie for the autologin
			$cookie = new Cookie( 'sekretaer-session' );
			$cookie->set( $session_id );

		}


		$session_lifetime = get_config('session_lifetime');
		$session_cache = new Cache( 'session', $session_id, true, $session_lifetime );
		$session_cache->add_data( json_encode($session_data) );


		$_SESSION['session_id'] = $session_id;


		return $this;
	}


	function autologin() {

		global $core;

		if( $this->authorized() ) return false;

		$cookie = new Cookie( 'sekretaer-session' );

		if( ! $cookie->exists() ) {
			return false;
		}

		$session_id = $cookie->get();

		$this->session_id = $session_id;

		if( ! $this->load_session_data() ) {

			// session expired, delete cookie
			$cookie->remove();
			
			return false;
		}


		// TODO: check additional safety options, like browser and location ? -- to make session cloning harder


		$_SESSION['session_id'] = $session_id;

		// refresh session cookie lifetime:
		$cookie->refresh();

		// refresh url cookie lifetime
		$url_cookie = new Cookie( 'sekretaer-url' );
		$url_cookie->refresh();

		return true;
	}


	function create_short_name( $me ) {

		$short_name = str_replace( array('http://www.', 'https://www.', 'http://', 'https://'), '', $me );
		$short_name = un_trailing_slash_it( $short_name );

		return $short_name;
	}


	function get( $field ) {

		if( ! array_key_exists($field, $this->fields) ) return false;

		return $this->fields[$field];
	}


	function logout() {

		// remove autologin cookie, if it exists
		$cookie = new Cookie( 'sekretaer-session' );
		$cookie->remove();

		$session_id = $this->session_id;

		if( $session_id ) {
			// remove session cache
			$session_cache = new Cache( 'session', $session_id, true );
			$session_cache->remove();
		}

		if( ! empty($this->fields['me']) && ! empty($this->fields['access_token']) ) {
			// try to revoke the access_token
			$indieauth = new IndieAuth();
			$revocation_endpoint = $indieauth->discover_endpoint( 'revocation_endpoint', $this->fields['me'] );
			if( $revocation_endpoint ) {
				$revocation_request = new Request( $revocation_endpoint );
				$revocation_request->set_post_data( [ 'token' => $this->fields['access_token'] ] );
				$revocation_request->curl_request();
			}

		}

		@session_destroy();

		$this->session_id = false;
		$this->user_id = false;
		$this->fields = [];

		return $this;
	}


}
