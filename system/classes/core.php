<?php

// update: 2023-03-15

class Core {

	public $version;

	public $abspath;
	public $basefolder;
	public $baseurl;

	public $config;
	public $log;
	public $theme;

	private $user;

	public $route;

	function __construct() {

		global $core;
		$core = $this;


		$abspath = realpath(dirname(__FILE__)).'/';
		$abspath = preg_replace( '/system\/classes\/$/', '', $abspath );
		$this->abspath = $abspath;

		$basefolder = str_replace( 'index.php', '', $_SERVER['PHP_SELF']);
		$this->basefolder = $basefolder;

		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) $baseurl = 'https://';
		else $baseurl = 'http://';
		$baseurl .= $_SERVER['HTTP_HOST'];
		$baseurl .= $basefolder;
		$this->baseurl = $baseurl;


		$this->version = get_system_version( $abspath );


		session_start();
		$this->user = new User( $this );


		$this->config = new Config( $this );
		$this->log = new Log( $this );
		$this->theme = new Theme( $this );

		$this->route = new Route( $this );

		$this->refresh_cache();
	}

	function debug( ...$messages ) {

		if( $this->config->get('logging') ) {
			$this->log->message( ...$messages );
		}

		if( $this->config->get('debug') ) {
			echo '<div class="debugmessage"><strong class="debugmessage-head">DEBUGMESSAGE</strong><pre>';
			$first = true;
			foreach( $messages as $message ) {
				if( is_array($message) || is_object($message) ) $message = var_export($message, true);
				if( ! $first ) echo '<br>';
				echo $message;
				$first = false;
			}
			echo '</pre></div>';
		}

	}

	function include( $file_path, $args = array() ) {

		$core = $this;

		$full_file_path = $this->abspath.$file_path;

		if( ! file_exists($full_file_path) ) {
			$this->debug( 'include not found' );
			exit;
		}

		include( $full_file_path );

	}


	function authorized() {
		if( $this->user->authorized() ) {
			return true;
		}

		return false;
	}

	function authorize( $post ) {
		$this->user->authorize( $post );

		return $this;
	}

	function login() {
		$this->user->login();

		return $this;
	}

	function logout() {
		$this->user->logout();

		return $this;
	}

	function me() {
		return $this->user->get('me');
	}



	function version() {
		return $this->version;
	}


	function refresh_cache() {
		
		$core = $this;

		$cache = new Cache( false, false );

		$cache->clear_cache_folder();

	}


}

