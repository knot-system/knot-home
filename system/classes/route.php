<?php


class Route {

	public $route;

	public $request;

	function __construct() {

		global $core;

		$request_string = $_SERVER['REQUEST_URI'];
		$request_string = preg_replace( '/^'.preg_quote($core->basefolder, '/').'/', '', $request_string );

		$query_string = false;

		$request = explode( '?', $request_string );
		if( count($request) > 1 ) $query_string = $request[1];
		$request = $request[0];

		$request = explode( '/', $request );

		$this->request = $request;


		if( $core->user->autologin() ) {
			$this->redirect( $request_string );
		}


		if( ! empty($request[0]) && $request[0] == 'action' ) {

			if( ! empty($request[1]) ) {
				$action = $request[1];

				$redirect_path = '';

				if( $action == 'logout' ) {

					$core->user->logout();

				} elseif( $action == 'login' ) {

					if( ! empty($_POST['path']) ) {
						$_SESSION['login_redirect_path'] = trailing_slash_it($_POST['path']);
					}

					$core->user->authorize( $_POST );
					exit;

				} elseif( $action == 'redirect' ) {

					$redirect_path = 'dashboard/';
					if( isset($_SESSION['login_redirect_path']) ) {
						$redirect_path = $_SESSION['login_redirect_path'];
					}

					$core->user->login();

				}

				if( isset($_SESSION['login_redirect_path']) ) {
					unset($_SESSION['login_redirect_path']);
				}
				
				$this->redirect( $redirect_path );

			}

			$this->route = array(
				'template' => '404',
			);

			return $this; // always end here if an action is set
		}


		if( $core->user->authorized() ) {

			if( empty($request[0]) ) {
				
				$homepage = trailing_slash_it($core->config->get('homepage'));

				$this->redirect($homepage);

			} elseif( ! empty($request[0]) && $request[0] == api_get_endpoint() ) {
				// api
				// NOTE: this is subject to change!

				if( api_check_request() ) {
					exit;
				}

				$this->route = array(
					'template' => '404'
				);

			} elseif( ! empty($request[0]) && $request[0] == 'remote-image' ) {
				// maybe cached remote image

				$hash = $request[1];

				if( file_exists($core->abspath.'cache/remote-image/'.$hash) ) {
					$image = new Image( $hash, 'remote' );
					$image->display();
					exit;
				}
				

				$this->route = array(
					'template' => '404'
				);

			} else {

				$template = $request[0];

				if( ! file_exists($core->abspath.'system/site/'.$template.'.php') ) {
					$template = '404';
				}

				// make sure that microsub/micropub is enabled, when using it as a template
				if( $template == 'microsub' && ! $core->config->get('microsub') ) {
					$template = '404';
				} elseif( $template == 'micropub' && ! $core->config->get('micropub') ) {
					$template = '404';
				}

				$channel = false;
				if( ! empty($request[1]) ) {
					$channel = $request[1];
				}

				$action = false;
				if( ! empty($request[2]) ) {
					$action = $request[2];
				}

				$this->route = array(
					'template' => $template,
					'channel' => $channel,
					'action' => $action
				);

			}

			
		} else {

			$this->route = array(
				'template' => 'login'
			);
	
		}
		
		return $this;
	}

	function get( $name = false ) {

		if( $name ) {

			if( ! is_array($this->route) ) return false;

			if( ! array_key_exists($name, $this->route) ) return false;

			return $this->route[$name];
		}

		return $this->route;
	}

	function redirect( $path ) {
		php_redirect( $path );
	}
	
}
