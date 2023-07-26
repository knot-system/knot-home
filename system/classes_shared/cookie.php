<?php

// update: 2023-07-26


class Cookie {
	
	private $name;
	private $content;

	function __construct( $name ){
		
		$this->name = $name;

		if( empty($_COOKIE[$name]) ) {
			return;
		}

		$this->content = $_COOKIE[$name];

	}


	function set( $content, $lifetime = false ) {

		if( ! $this->name ) {
			return;
		}

		global $core;

		if( ! $lifetime ) {
			$lifetime = get_config('cookie_lifetime');
		}

		$lifetime += time();

		setcookie( $this->name, $content, array(
			'expires' => $lifetime,
			'path' => $core->basefolder
		));

		$this->content = $content;

		return $this;
	}


	function exists() {

		if( ! $this->content ) {
			return false;
		}

		return true;
	}


	function remove() {

		if( ! $this->name ) {
			return;
		}

		global $core;

		setcookie( $this->name, false, array(
			'expires' => -1,
			'path' => $core->basefolder
		));

		$this->content = false;

		return $this;
	}


	function get() {

		if( ! $this->exists() ) {
			return false;
		}

		return $this->content;
	}


	function refresh( $lifetime = false ) {

		if( ! $this->exists() ) {
			return;
		}

		$this->set( $this->content, $lifetime );

		return $this;
	}

}
