<?php

class Request {

	private $user_agent;
	private $timeout;
	private $url;

	private $body;

	function __construct() {

		global $sekretaer;

		$this->user_agent = 'maxhaesslein/sekretaer/'.$sekretaer->version();
		$this->timeout = 10;

	}

	function set_url( $url ) {
		$this->url = $url;

		return $this;
	}


	function get_body(){

		$this->curl_request();

		if( ! $this->body ) return false;

		return $this->body;
	}

	function curl_request( $force = false ) {

		if( ! $this->url ) return false;

		if( $force || ! $this->body ) {

			$ch = curl_init( $this->url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_USERAGENT, $this->user_agent );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

			$this->body = curl_exec( $ch );
			curl_close( $ch );
		}

	}

}