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
		// TODO: rewrite with Request class

		$api_url = $this->api_url;
		$authorization = $this->authorization;

		$url = $api_url.'?action='.$action;

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


		// TODO: move to Request class
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($result);


		$cache->add_data( json_encode($json) );

		return $json;
	}

	function api_post( $action, $args = array() ) {
		// TODO: rewrite with Request class

		$api_url = $this->api_url;
		$authorization = $this->authorization;

		$url = $api_url.'?action='.$action;
		
		if( ! isset($args['me']) ) {
			$args['me'] = $this->me;
		}

		$post_args = array();
		if( count($args) ) {
			foreach( $args as $key => $value ) {
				$post_args[] = $key.'='.$value; // TODO: sanitize
			}
		}

		$post_args = implode('&', $post_args);

		// TODO: move to Request class
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array($authorization) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_args );
		$server_output = curl_exec($ch);
		curl_close($ch);


		return $server_output;
	}


}
