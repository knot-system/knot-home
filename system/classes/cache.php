<?php

// NOTE: in system/classes/core.php there is also the 'refresh_cache()' function
// that takes care of deleting old, obsolete cache files

class Cache {

	private $cache_folder = 'cache/';
	private $cache_file;

	public $type;
	public $name;
	public $hash;
	public $cache_file_name;
	public $filesize;
	
	function __construct( $type, $input, $use_hash = false ) {

		// TODO: add config option to disable cache
		// TODO: add method to force a cache refresh?

		global $sekretaer;

		if( $type == 'image' || $type == 'image-preview' ) {
			$this->cache_folder .= 'images/';
		} elseif( $type == 'link' ) {
			$this->cache_folder .= 'link-previews/';
		} elseif( $type == 'session' ) {
			$this->cache_folder .= 'sessions/';
		} else {
			$this->cache_folder .= $type.'/';
		}

		$this->check_cache_folder();

		$this->type = $type;

		if( $use_hash ) {

			$this->hash = $input;

		} else {

			$this->name = $input;

			$this->hash = get_hash( $this->name );

		}

		$this->cache_file = $this->cache_folder.$this->hash;
		$this->cache_file_name = $this->hash;

	}


	function get_data() {
		if( ! file_exists($this->cache_file) ) return false;

		$this->filesize = filesize($this->cache_file);

		$cache_content = file_get_contents($this->cache_file);

		return $cache_content;
	}


	function add_data( $data ) {
		global $sekretaer;

		if( ! file_put_contents( $sekretaer->abspath.$this->cache_file, $data ) ) {
			$sekretaer->debug( 'could not create cache file', $this->cache_file );
		}

		return $this;
	}


	function remove() {
		if( ! file_exists($this->cache_file) ) return;

		unlink($this->cache_file);
	}


	private function check_cache_folder(){
		global $sekretaer;

		if( is_dir($sekretaer->abspath.$this->cache_folder) ) return;

		if( mkdir( $sekretaer->abspath.$this->cache_folder, 0777, true ) === false ) {
			$sekretaer->debug( 'could not create cache dir', $this->cache_folder );
		}

		return $this;
	}


};
