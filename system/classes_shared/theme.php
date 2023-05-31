<?php

// update: 2023-05-31


class Theme {

	public $folder_path;
	public $path;
	public $url;
	public $config;

	public $stylesheets = array();
	public $scripts = array();
	public $metatags = array();
	public $headers = array();


	function __construct() {

		global $core;

		$theme_name = get_config('theme');

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

		return $this;
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
		$global_url = $core->baseurl.'system/site/assets/';

		if( $type == 'theme' && file_exists($this->path.$path) ) {
			$type = 'theme';
			$url = $this->url.$path;
		} elseif( $type == 'global' && file_exists($global_path.$path) ) {
			$type = 'global';
			$url = $global_url.$path;
		} else {
			return $this;
		}

		$this->stylesheets[$path] = [
			'path' => $path,
			'url' => $url,
			'type' => $type
		];

		return $this;
	}


	function remove_stylesheet( $path ) {
		if( ! array_key_exists($path, $this->stylesheets) ) return $this;

		unset($this->stylesheets[$path]);

		return $this;
	}


	function print_stylesheets() {

		global $core;

		foreach( $this->stylesheets as $stylesheet ) {
			if( $stylesheet['type'] == 'global' ) {
				$version = $core->version();
			} else {
				$version = $this->get('version');
			}

			if( get_config('debug') ) {
				$version .= '.'.time();
			}

			$url = $stylesheet['url'];

			$url = str_replace( ['https:', 'http:'], '', $url ); // NOTE: urls should start with "//domain.tld", because then it doesn't matter if the page get's loaded via http or https - webfonts may have problems otherwise

			$url .= '?v='.$version;

		?>
	<link rel="stylesheet" href="<?= $url ?>">
<?php
		}

		return $this;
	}


	function add_script( $path, $type = 'theme', $loading = false, $footer = false ) {

		// $loading is meant for 'async' or 'defer' attributes

		global $core;

		$global_path = $core->abspath.'system/site/assets/';
		$global_url = $core->baseurl.'system/site/assets/';

		if( $type == 'theme' && file_exists($this->path.$path) ) {
			$type = 'theme';
			$url = $this->url.$path;
		} elseif( $type == 'global' && file_exists($global_path.$path) ) {
			$type = 'global';
			$url = $global_url.$path;
		} else {
			return $this;
		}

		$this->scripts[$path] = [
			'path' => $path,
			'url' => $url,
			'type' => $type,
			'loading' => $loading,
			'footer' => $footer
		];

		return $this;
	}


	function remove_script( $path ) {
		if( ! array_key_exists($path, $this->scripts) ) return $this;

		unset($this->scripts[$path]);

		return $this;
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

			if( get_config('debug') ) {
				$version .= '.'.time();
			}

			$loading = '';
			if( ! empty($script['loading']) ) $loading = ' '.$script['loading'];

			$url = $script['url'];

			$url = str_replace( ['https:', 'http:'], '', $url ); // NOTE: urls should start with "//domain.tld", because then it doesn't matter if the page get's loaded via http or https

			$url .= '?v='.$version;

		?>
	<script<?= $loading ?> src="<?= $url ?>"></script>
<?php
		}

		return $this;
	}


	function add_metatag( $name, $string, $position = false ) {

		if( ! $position ) $position = 'header';

		if( ! array_key_exists( $position, $this->metatags ) ) $this->metatags[$position] = array();

		if( array_key_exists($name, $this->metatags) ) {
			global $core;
			$core->debug('a metatag with this name already exists, it gets overwritten', $name, $string);
		}

		$this->metatags[$position][$name] = $string;

		return $this;
	}


	function remove_metatag( $name, $position ) {

		if( ! empty($this->metatags[$position]) && ! array_key_exists($name, $this->metatags[$position]) ) return $this;

		unset($this->metatags[$position][$name]);

		return $this;
	}


	function print_metatags( $position = false ) {

		if( ! $position ) $position = 'header';

		if( empty($this->metatags[$position]) ) return $this;

		foreach( $this->metatags[$position] as $name => $string ) {
			echo "\n	".$string;
		}

		return $this;
	}


	function add_header( $name, $header ) {

		if( array_key_exists($name, $this->headers) ) {
			global $core;
			$core->debug('a header with this name already exists, it gets overwritten', $name, $header);
		}

		$header_parts = explode(':', $header);

		$header_name = array_shift($header_parts);
		$header_content = implode(':', $header_parts);

		$this->headers[$name] = [
			'name' => $header_name,
			'content' => $header_content
		];

		return $this;
	}

	function remove_header( $name ) {

		if( ! array_key_exists($name, $this->headers) ) return $this;

		unset($this->headers[$name]);

		return $this;
	}

	function print_headers() {

		if( empty($this->headers) ) return $this;

		$print_headers = [];

		foreach( $this->headers as $header ) {
			$name = $header['name'];
			$content = $header['content'];

			if( array_key_exists($name, $print_headers) ) {
				$print_headers[$name] .= ', '.trim($content);
			} else {
				$print_headers[$name] = $name.': '.trim($content);
			}

		}

		foreach( $print_headers as $header ) {
			header($header);
		}

		return $this;
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
