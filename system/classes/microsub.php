<?php

class Microsub {

	private $me;
	private $name;

	private $api_url;
	private $access_token;
	private $scope;
	private $authorization;

	function __construct(){

		if( empty($_SESSION['me']) || empty($_SESSION['name']) ) return; // this should not happen, but just in case ..

		$this->me = $_SESSION['me'];
		$this->name = $_SESSION['name'];

		if( ! isset($_SESSION['microsub_endpoint']) ) {
			// TODO: option to refresh the endpoint
			$this->show_error( 'no microsub endpoint found for '.$this->me );
		}
		$this->api_url = $_SESSION['microsub_endpoint'];

		if( ! isset($_SESSION['access_token']) ) {
			$this->show_error( 'no access token found for '.$this->me );
		}
		$this->access_token = $_SESSION['access_token'];

		if( ! isset($_SESSION['scope']) ) {
			$this->show_error( 'no scope found for '.$this->me );
		}
		$this->scope = explode( ' ', $_SESSION['scope'] );


		if( ! in_array( 'create', $this->scope ) ) {
			$this->show_error( 'scope is not <em>create</em> (scope is <strong>'.implode( ' ', $_SESSION['scope']).'</strong>) for '.$this->me );
		}

		$this->authorization = 'Authorization: Bearer '.$this->access_token;

	}


	function api_get( $action, $args = array() ) {

		$url = $this->api_url.'?action='.$action;

		if( ! isset($args['me']) ) {
			$args['me'] = $this->me;
		}

		if( count($args) ) {
			foreach( $args as $key => $value ) {
				$url .= '&'.$key.'='.$value; // TODO: sanitize
			}
		}

		$cache = new Cache( 'microsub', $url, false, 60*3 ); // cache for 3 minutes
		$data = $cache->get_data();
		if( $data ) return json_decode($data);

		$request = new Request( $url );
		$request->set_headers( array('Content-Type: application/json', $this->authorization) );
		$request->curl_request();

		$body = $request->get_body();

		if( $body ) $body = json_decode($body);

		$cache->add_data( json_encode($body) );

		return $body;
	}

	function api_post( $action, $args = array() ) {

		$url = $this->api_url.'?action='.$action;
		
		if( ! isset($args['me']) ) {
			$args['me'] = $this->me;
		}

		$request = new Request( $url );
		$request->set_headers( array($this->authorization) );
		$request->set_post_data( $args );
		$request->curl_request();

		$status_code = $request->get_status_code();
		$headers = $request->get_headers();
		$body = $request->get_body();

		return [
			'status_code' => $status_code,
			'headers' => $headers,
			'body' => $body
		];
	}


}
