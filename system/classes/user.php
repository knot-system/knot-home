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


	function login( $post ) {

		// $post is the content of $_POST

		// TODO: this is throwaway test code!
		$_SESSION['user_id'] = 'test';

		$this->user_id = $user_id;

		return $this;
	}


	function logout() {
		session_destroy();
		
		$this->user_id = false;

		// TODO: delete cookie, if one is set

		return $this;
	}


}
