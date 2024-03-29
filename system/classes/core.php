<?php


class Core {

	public $version;

	public $abspath;
	public $basefolder;
	public $baseurl;

	public $config;
	public $log;
	public $theme;

	public $user;

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

		$this->config = new Config();

		if( ! $this->config->get('debug') ) {
			error_reporting(0);
		}

		$this->log = new Log();


		session_start();
		$this->user = new User();



		$this->theme = new Theme();

		$this->theme->add_stylesheet( 'css/knot-home.css', 'global' );
		
		$link_preview_refresh = 'false'; // this is meant to be a string instead of a bool!
		if( $this->config->get('link_preview_autorefresh') ) {
			$link_preview_refresh = 'true'; // this is meant to be a string instead of a bool!
		}

		$this->theme->add_metatag( 'script_knot-home', '<script type="text/javascript">const Knot = { API: { url: "'.url(api_get_endpoint()).'", linkpreview_refresh: '.$link_preview_refresh.' } };</script>', 'footer' );
		$this->theme->add_script( 'js/knot-home.js', 'global', 'async', true );

		$this->theme->add_metatag( 'charset', '<meta charset="utf-8">' );
		$this->theme->add_metatag( 'viewport', '<meta name="viewport" content="width=device-width,initial-scale=1.0">' );
		$this->theme->add_metatag( 'title', '<title>Knot Home</title>' );

		$this->theme->add_metatag( 'generator', '<meta tag="generator" content="Knot Home v.'.$core->version().'">' );

		# pwa manifest
		$this->theme->add_metatag( 'pwa-manifest', '<link rel="manifest" href="'.$this->baseurl.'system/site/assets/json/manifest.json">' );


		$this->route = new Route();

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


	function version() {
		return $this->version;
	}


	function refresh_cache() {
		
		$core = $this;

		$cache = new Cache( false, false );

		$cache->clear_cache_folder();

	}


}

