<?php

class Micropub {

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

		if( ! isset($_SESSION['micropub_endpoint']) ) {
			// TODO: option to refresh the endpoint
			$this->show_error( 'no micropub endpoint found for '.$this->me );
		}
		$this->api_url = $_SESSION['micropub_endpoint'];

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


	function get_tags() {

		$url = $this->api_url.'?q=config';

		$cache = new Cache( 'micropub', $url, false, 60*5 ); // cache for 5 minutes

		$data = $cache->get_data();
		if( $data ) {

			$tags = json_decode($data);

		} else {

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $this->authorization ));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($result);

			$config = $json;

			$tags = [];
			if( isset($config->categories) ) {
				$tags = $config->categories;
			}

			$cache->add_data( json_encode($tags) );

		}
		
		return $tags;
	}

	function get_me() {
		return $this->me;
	}

	function get_name() {
		return $this->name;
	}


	function post( $post, $files = false ) {

		$data = array(
			'h' => 'entry',
			'name' => $post['title'],
			'content' => $post['content'],
			'post-status' => $post['status']
		);

		if( isset($post['slug']) && $post['slug'] != '' ) $data['slug'] = $post['slug'];

		if( $files && ! empty($files['image']) && ! empty($files['image']['name']) ) {
			// TODO: more error handling; check if $files['image']['error'] == 0 and $files['image']['size'] is > 0 and if tmp_name exists on the disk and so on ...
			$data['photo'] = curl_file_create( $files['image']['tmp_name'], $files['image']['type'], $files['image']['name'] );
		}

		if( trim($post['tags']) ) {
			$tags = explode( ',', $post['tags'] );
			$tags = array_map( 'trim', $tags );

			$tags = array_unique( $tags );
			$tags = array_filter( $tags ); // remove empty elements

			if( count($tags) ) $data['category'] = implode( ',', $tags );
		}

		$url = $this->api_url;

		// TODO: use Request class for this

		$ch = curl_init( $url );

		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $this->authorization) );

		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$result = curl_exec( $ch );
		$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		$new_post_url = false;
		$curl_info = curl_getinfo( $ch );
		if( $httpcode == 201 ) {
			$headers = substr($result, 0, $curl_info["header_size"]);
			preg_match("!\r\n(?:Location): *(.*?) *\r\n!i", $headers, $matches);
			$new_post_url = $matches[1];
		}

		curl_close($ch);


		$message = false;
		$success = false;

		if( $httpcode == 201 ) {
			// HTTP 201 Created - success!

			$success = true;
			$message = 'New post created at <a href="'.$new_post_url.'" target="_blank" rel="noopener">'.$new_post_url.'</a>';

		} elseif( $httpcode == 401 ) {
			// HTTP 401 Unauthorized - No access token was provided in the request.

			$message = '<strong>Unauthorized</strong> - no access token was provided in the request.';

		} elseif( $httpcode == 403 ) {
			// HTTP 403 Forbidden - An access token was provided, but the authenticated user does not have permission to complete the request.

			$message = '<strong>Forbidden</strong> - the authenticated user does not have permission to complete the request.';

		} elseif( $httpcode == 400 ) {
			// HTTP 400 Bad Request - Something was wrong with the request, such as a missing "h" parameter, or other missing data. The response body may contain more human-readable information about the error.

			$message = '<strong>Bad Request</strong> - something was wrong with the request.';
			// TODO: append response body to $message

		} elseif( $httpcode == 500 ) {
			// HTTP 500 Internal Server Error

			$message = '<strong>Internal Server Error</strong> - something went wrong.';

		}

		if( ! $success ) {
			global $sekretaer;
			$sekretaer->debug( $url, $data, $httpcode, $result );
		}

		return [
			$success,
			$message
		];
	}


	function show_error( $error_message ) {

		echo '<p><strong>Error!</strong></p>';
		echo '<p>'.$error_message.'</p>';

		snippet( 'footer' );
		exit;
	}

}
