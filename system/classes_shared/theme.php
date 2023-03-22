<?php

// update: 2023-03-22


class Theme {

	public $folder_path;
	public $path;
	public $url;
	public $config;

	public $stylesheets = array();
	public $scripts = array();
	public $metatags = array();


	function __construct( $core ) {

		$theme_name = $core->config->get('theme');

		if( ! file_exists( $core->abspath.'theme/'.$theme_name.'/theme.php' ) ) {
			$theme_name = 'default';
		}

		$file_path = $core->abspath.'theme/'.$theme_name.'/theme.php';
		$this->config = $this->load_theme_config_from_file( $file_path );

		$this->folder_name = $theme_name;
		$this->path = 'theme/'.$theme_name.'/';
		$this->url = url('theme/'.$theme_name.'/');


		// expand core config options:
		$config_path = $core->abspath.'theme/'.$theme_name.'/config.php';
		if( file_exists( $config_path ) ) {
			$core->config->load_config_file( $config_path );
			// we need to overwrite it with the local user config again:
			$core->config->load_config_file( $core->abspath.'config.php' );
		}

	}


	function load(){
		global $core;
		$core->include( $this->path.'functions.php' );
	}


	function load_theme_config_from_file( $file_path ) {

		global $core;

		if( ! file_exists($file_path) ) {
			$core->debug( 'no config file found', $file_path );
			exit;
		}

		$config = include( $file_path );

		return $config;
	}


	function get( $key = false ) {

		if( ! $key ) return $this->config;

		if( array_key_exists($key, $this->config) ) return $this->config[$key];
		
		return false;
	}


	function add_stylesheet( $path, $type = 'theme' ) {

		global $core;

		$global_path = $core->abspath.'system/site/assets/';
		$global_url = $core->baseurl.'/system/site/assets/';

		if( $type == 'theme' && file_exists($this->path.$path) ) {
			$type = 'theme';
			$url = $this->url.$path;
		} elseif( $type == 'global' && file_exists($global_path.$path) ) {
			$type = 'global';
			$url = $global_url.$path;
		} else {
			return;
		}

		$this->stylesheets[$path] = [
			'path' => $path,
			'url' => $url,
			'type' => $type
		];
	}


	function remove_stylesheet( $path ) {
		if( ! array_key_exists($path, $this->stylesheets) ) return;

		unset($this->stylesheets[$path]);
	}


	function print_stylesheets() {

		global $core;

		foreach( $this->stylesheets as $stylesheet ) {
			if( $stylesheet['type'] == 'global' ) {
				$version = $core->version();
			} else {
				$version = $this->get('version');
			}

			if( $core->config->get('debug') ) {
				$version .= '.'.time();
			}

		?>
	<link rel="stylesheet" href="<?= $stylesheet['url'] ?>?v=<?= $version ?>">
<?php
		}

	}


	function add_script( $path, $type = 'theme', $loading = false, $footer = false ) {

		// $loading is meant for 'async' or 'defer' attributes

		global $core;

		$global_path = $core->abspath.'system/site/assets/';
		$global_url = $core->baseurl.'/system/site/assets/';

		if( $type == 'theme' && file_exists($this->path.$path) ) {
			$type = 'theme';
			$url = $this->url.$path;
		} elseif( $type == 'global' && file_exists($global_path.$path) ) {
			$type = 'global';
			$url = $global_url.$path;
		} else {
			return;
		}

		$this->scripts[$path] = [
			'path' => $path,
			'url' => $url,
			'type' => $type,
			'loading' => $loading,
			'footer' => $footer
		];
	}


	function remove_script( $path ) {
		if( ! array_key_exists($path, $this->scripts) ) return;

		unset($this->scripts[$path]);
	}


	function print_scripts( $position = false ) {

		global $core;

		foreach( $this->scripts as $script ) {

			if( $script['footer'] && $position != 'footer' ) continue;
			elseif( ! $script['footer'] && $position == 'footer' ) continue;

			if( $script['type'] == 'global' ) {
				$version = $core->version();
			} else {
				$version = $this->get('version');
			}

			if( $core->config->get('debug') ) {
				$version .= '.'.time();
			}

			$loading = '';
			if( ! empty($script['loading']) ) $loading = ' '.$script['loading'];

		?>
	<script<?= $loading ?> src="<?= $script['url'] ?>?v=<?= $version ?>"></script>
<?php
		}

	}


	function add_metatag( $name, $string, $position = false ) {

		if( ! $position ) $position = 'header';

		if( ! array_key_exists( $position, $this->metatags ) ) $this->metatags[$position] = array();

		if( array_key_exists($name, $this->metatags) ) {
			global $core;
			$core->debug('a metatag with this name already exists, it gets overwritten', $name, $string);
		}

		$this->metatags[$position][$name] = $string;
	}


	function remove_metatag( $name, $position ) {

		if( ! empty($this->metatags[$position]) && ! array_key_exists($name, $this->metatags[$position]) ) return;

		unset($this->metatags[$position][$name]);

	}


	function print_metatags( $position = false ) {

		if( ! $position ) $position = 'header';

		if( empty($this->metatags[$position]) ) return;

		foreach( $this->metatags[$position] as $name => $string ) {
			echo "\n	".$string;
		}

	}


	function snippet( $path, $args = array(), $return = false ) {
		
		global $core;

		$snippet_path = 'snippets/'.$path.'.php';

		if( file_exists($this->path.$snippet_path) ) {
			$include_path = $this->path.$snippet_path;
		} else {
			$include_path = 'system/site/'.$snippet_path;
		}

		if( ! file_exists( $core->abspath.$include_path) ) return;

		ob_start();

		$core->include( $include_path, $args );

		$snippet = ob_get_contents();
		ob_end_clean();

		if( $return === true ) {
			return $snippet;
		}

		echo $snippet;

	}


}
