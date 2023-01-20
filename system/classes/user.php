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

		// Redirect user to their authorization endpoint, which will then redirect to the '/action/redirect/' URL
		header( 'Location: '.$authorization_url );
		exit;
	}

	function login() {

		$indieauth = new IndieAuth();

		$response = $indieauth->complete( $_GET );

		if( ! empty($response['error']) ) {
			php_redirect( '?error='.$response['error'] );
			exit;
		}


		if( ! empty($response['access_token']) ) {
			$_SESSION['access_token'] = $response['access_token'];
		}
		if( ! empty($response['scope']) ) {
			$_SESSION['scope'] = $response['scope'];
		}

		$_SESSION['me'] = $response['me'];

		$_SESSION['user_id'] = $response['me'];

		$this->user_id = $response['me'];

		// TODO: set cookie, if we need one

		return $this;
	}


	function logout() {
		session_destroy();
		
		$this->user_id = false;

		// TODO: delete cookie, if one is set

		return $this;
	}


}
