<?php

class Config {

	public $config = array();

	function __construct( $sekretaer ) {

		// load site config / default config
		$this->load_config_file( $sekretaer->abspath.'system/site/config.php' );

		// NOTE: the file theme/{themename}/config.php may be loaded here; but because at this time we don't have the correct themename yet, this happens in system/classes/theme.php; after the theme config.php gets loaded, it gets overwritten by the local config again.
		
		// overwrite with custom local config
		$this->load_config_file( $sekretaer->abspath.'config.php' );

	}


	function load_config_file( $config_file ) {

		global $sekretaer;

		if( ! file_exists($config_file) ) {
			$sekretaer->debug( 'config file not found', $config_file );
			exit;
		}

		$config = include( $config_file );

		$this->config = array_merge( $this->config, $config );

		return $this;
	}
	

	function get( $option = false, $fallback = false ) {

		if( $option ) {
			if( ! array_key_exists( $option, $this->config ) ) {
				return $fallback;
			}
			return $this->config[$option];
		}

		return false;
	}

};
