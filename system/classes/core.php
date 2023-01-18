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

		return $this;
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

	function login( $post ) {
		$this->user->login( $post );

		return $this;
	}

	function logout() {
		$this->user->logout();

		return $this;
	}



	function version() {
		return $this->version;
	}

}

