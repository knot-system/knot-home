<?php


class Micropub {

	private $me;
	private $name;

	private $api_url;
	private $access_token;
	private $scope;
	private $authorization;

	function __construct(){

		global $core;

		if( ! $core->user->get('me') || ! $core->user->get('name') ) return; // this should not happen, but just in case ..

		$this->me = $core->user->get('me');
		$this->name = $core->user->get('name');

		if( ! $core->user->get('micropub_endpoint') ) {
			// TODO: option to refresh the endpoint
			// or, try to refresh endpoint automatically once
			$this->show_error( 'no micropub endpoint found for '.$this->me );
		}
		$this->api_url = $core->user->get('micropub_endpoint');

		if( ! $core->user->get('access_token') ) {
			$this->show_error( 'no access token found for '.$this->me );
		}
		$this->access_token = $core->user->get('access_token');

		if( ! $core->user->get('scope') ) {
			$this->show_error( 'no scope found for '.$this->me );
		}
		$this->scope = explode( ' ', $core->user->get('scope') );


		if( ! in_array( 'create', $this->scope ) ) {
			$this->show_error( 'scope is not <em>create</em> (scope is <strong>'.implode( ' ', $core->user->get('scope')).'</strong>) for '.$this->me );
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
			'me' => $this->get_me(),
			'h' => 'entry',
			'name' => $post['title'],
			'content' => $post['content'],
			'post-status' => $post['status']
		);

		if( isset($post['slug']) && $post['slug'] != '' ) $data['slug'] = $post['slug'];

		if( $files && ! empty($files['image']) && ! empty($files['image']['name']) ) {

			if( empty($files['image']['size'])
			 || ( isset($files['image']['error']) && $files['image']['error'] != UPLOAD_ERR_OK )
			 || $files['image']['size'] <= 0
			 || empty($files['image']['tmp_name'])
			 || ! file_exists($files['image']['tmp_name'])
			) {

				global $core;
				$core->debug( 'image upload error', $data, $files );

				return [
					false,
					'<strong>Bad Request</strong> - image upload error'
				];
			}

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


		$request = new Request( $url );
		$request->set_headers( array( $this->authorization ) );
		$request->set_post_data( $data );
		$request->curl_request();

		$httpcode = $request->get_status_code();
		$headers = $request->get_headers();
		$body = $request->get_body();


		$message = false;
		$success = false;

		if( $httpcode == 201 ) {
			// HTTP 201 Created - success!

			$success = true;
			$message = 'New post created';
			if( ! empty($headers['location']) ) {
				$new_post_url = $headers['location'];
				$message .= ' at <a href="'.$new_post_url.'" target="_blank" rel="noopener">'.$new_post_url.'</a>';
			}

		} elseif( $httpcode == 401 ) {
			// HTTP 401 Unauthorized - No access token was provided in the request.

			$message = '<strong>Unauthorized</strong> - no access token was provided in the request.';

		} elseif( $httpcode == 403 ) {
			// HTTP 403 Forbidden - An access token was provided, but the authenticated user does not have permission to complete the request.

			$message = '<strong>Forbidden</strong> - the authenticated user does not have permission to complete the request.';

		} elseif( $httpcode == 400 ) {
			// HTTP 400 Bad Request - Something was wrong with the request, such as a missing "h" parameter, or other missing data. The response body may contain more human-readable information about the error.

			$message = '<strong>Bad Request</strong> - something was wrong with the request.';
			if( $body ) $message .= '<br>'.$body;

		} elseif( $httpcode == 500 ) {
			// HTTP 500 Internal Server Error

			$message = '<strong>Internal Server Error</strong> - something went wrong.';

		}

		if( ! $success ) {
			global $core;
			$core->debug( $url, $data, $httpcode, $headers, $body );
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
