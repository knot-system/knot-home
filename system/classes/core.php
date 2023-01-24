<?php

class Sekretaer {

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

		global $sekretaer;
		$sekretaer = $this;


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
			echo '<hr><strong>ERROR</strong>';
			foreach( $messages as $message ) {
				echo '<br>'.$message;
			}
		}

	}

	function include( $file_path, $args = array() ) {

		$sekretaer = $this;

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



	function version() {
		return $this->version;
	}


	function refresh_cache() {
		// NOTE: see system/classes/cache.php for general cache handling
		// this function clears out old cache files.

		$lifetime = $this->config->get( 'cache_lifetime' );

		$folderpath = $this->abspath.'cache/';

		$files = read_folder( $folderpath, true );

		foreach( $files as $file ) {
			$timestamp = filemtime($file);
			
			if( time()-$timestamp > $lifetime ) { // cachefile too old
				@unlink($file); // delete old cache file; fail silently
			}

		}

	}


}

