<?php

class IndieAuth {

	private $request;


	private $scopes;
	private $authorization_endpoint;
	private $token_endpoint;

	function __construct() {

	}

	function login( $url, $scopes = false ) {

		$url = $this->normalize_url( $url );

		if( ! $url ) {
			return $this->error( 'invalid_url' );
		}

		$authorization_endpoint = $this->discover_endpoint( 'authorization_endpoint', $url );

		if( ! $authorization_endpoint ) {
			return $this->error( 'no_authorization_endpoint' );
		}

		$this->authorization_endpoint = $authorization_endpoint;

		$this->scopes = $this->parse_scopes( $scopes );

		if( count($this->scopes) ) {

			$token_endpoint = $this->discover_endpoint( 'token_endpoint', $url );

			if( ! $token_endpoint ) {
				return $this->error( 'no_token_endpoint' );
			}

			$this->token_endpoint = $token_endpoint;
		}

		echo '<pre>';
		var_dump('found auth endpoint', $authorization_endpoint);
		var_dump('found token endpoint', $token_endpoint);
		echo '</pre>';

		// TODO: authorize at endpoint.

		return false;
	}


	function parse_scopes( $scopes ) {
		$scopes = explode( ' ', $scopes );
		$scopes = array_unique( $scopes );
		$scopes = array_filter( $scopes ); // remove empty entries

		return $scopes;
	}


	function error( $error_code ) {

		$error = [
			'error' => $error_code
		];

		return $error;
	}


	function normalize_url( $url ) {

		$url = parse_url($url);

		if( array_key_exists('path', $url) && $url['path'] == '' ) {
			return false;
		}

		// parse_url returns just "path" for naked domains, so
		// move that into the "host" instead
		if( count($url) == 1 && array_key_exists('path', $url) ) {
			if( preg_match('/([^\/]+)(\/.+)/', $url['path'], $match) ) {
				$url['host'] = $match[1];
				$url['path'] = $match[2];
			} else {
				$url['host'] = $url['path'];
				unset($url['path']);
			}
		}

		if( ! array_key_exists('scheme', $url) ) {
			$url['scheme'] = 'http';
		}

		if( ! array_key_exists('path', $url) ) {
			$url['path'] = '/';
		}

		// Invalid scheme
		if( ! in_array($url['scheme'], array('http','https')) ) {
			return false;
		}

		// fragment not allowed
		if( array_key_exists('fragment', $url) ) {
			return false;
		}

		return $this->build_url($url);
	}

	function build_url($parsed_url) {
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = trailing_slash_it( isset($parsed_url['path']) ? $parsed_url['path'] : '' );
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

		return "$scheme$user$pass$host$port$path$query$fragment";
	}


	function discover_endpoint( $name, $url ) {

		if( ! $this->url_is_valid($url) ) return false;

		$this->request()->set_url($url);

		$body = $this->request->get_body();

		if( ! $body ) return false;

		$dom = new Dom( $body );

		$endpoint = $dom->find( 'link', $name );

		if( ! $endpoint ) return false;

		return $endpoint;
	}


	function url_is_valid( $url ) {

		$url = parse_url( $url );

		if( ! $url ) return false;
		if( ! array_key_exists('scheme', $url) ) return false;
		if( ! in_array($url['scheme'], array('http','https')) ) return false;
		if( ! array_key_exists('host', $url) ) return false;
		
		return true;
	}

	function request(){
		if( ! $this->request ) {
			$this->request = new Request();
		}

		return $this->request;
	}



}
