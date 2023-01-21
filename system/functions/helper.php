<?php


function get_system_version( $abspath ){
	return trim(file_get_contents($abspath.'system/version.txt'));
}


function url( $path = '', $trailing_slash = true ) {
	global $sekretaer;
	
	$path = $sekretaer->baseurl.$path;

	if( $trailing_slash ) {
		$path = trailing_slash_it($path);
	}
	
	return $path;
}


function trailing_slash_it( $string ){
	// add a slash at the end, if there isn't already one ..

	$string = preg_replace( '/\/*$/', '', $string );
	$string .= '/';

	return $string;
}


function un_trailing_slash_it( $string ) {
	// remove slash at the end

	$string = preg_replace( '/\/*$/', '', $string );

	return $string;
}


function add_stylesheet( $path, $type = 'theme' ) {
	global $sekretaer;
	$sekretaer->theme->add_stylesheet( $path, $type );
}

function remove_stylesheet( $path, $type = 'theme' ) {
	global $sekretaer;
	$sekretaer->theme->remove_stylesheet( $path, $type );
}


function add_script( $path, $type = 'theme', $loading = false, $footer = false ) {
	global $sekretaer;
	$sekretaer->theme->add_script( $path, $type, $loading, $footer );
}

function remove_script( $path, $type = 'theme' ) {
	global $sekretaer;
	$sekretaer->theme->remove_script( $path, $type );
}


function add_metatag( $name, $string, $position = false ) {
	global $sekretaer;
	$sekretaer->theme->add_metatag( $name, $string, $position );
}

function remove_metatag( $name, $position = false ) {
	global $sekretaer;
	$sekretaer->theme->remove_metatag( $name, $position );
}


function snippet( $path, $args = array(), $return = false ) {
	global $sekretaer;
	return $sekretaer->theme->snippet( $path, $args, $return );
}


function get_class_attribute( $classes ) {

	if( ! is_array( $classes ) ) $classes = explode( ' ', $classes );

	$classes = array_unique( $classes ); // remove double class names
	$classes = array_filter( $classes ); // remove empty class names

	if( ! count($classes) ) return '';

	return ' class="'.implode( ' ', $classes ).'"';
}


function sanitize_string_for_url( $string ) {

	// remove non-printable ASCII
	$string = preg_replace('/[\x00-\x1F\x7F]/u', '', $string);

	$string = mb_strtolower($string);

	$string = str_replace(array("ä", "ö", "ü", "ß"), array("ae", "oe", "ue", "ss"), $string);

	// replace special characters with '-'
	$string = preg_replace('/[^\p{L}\p{N}]+/u', '-', $string);

	$string = trim($string, '-');
	
	return $string;
}


function read_folder( $folderpath, $recursive = false ) {

	global $sekretaer;

	$files = [];

	if( ! is_dir( $folderpath ) ) {
		$sekretaer->debug( $folderpath.' is no directory' );
		return array();
	}

	$filename = false;
	if( $handle = opendir($folderpath) ){
		while( false !== ($file = readdir($handle)) ){
			if( substr($file,0,1) == '.' ) continue; // skip hidden files, ./ and ../

			if( is_dir($folderpath.$file) ) {

				if( $recursive ) {
					$files = array_merge( $files, read_folder($folderpath.$file.'/', $recursive));
				}

				continue;
			}

			$files[] = $folderpath.$file;

		}
		closedir($handle);
	} else {
		$sekretaer->debug( 'could not open dir', $folderpath );
		return array();
	}

	return $files;
}


function head_html(){

	global $sekretaer;

	$body_classes = array();

?><!DOCTYPE html>
<!--
  _________       __                    __      /\/\             
 /   _____/ ____ |  | _________   _____/  |____)/)/_____ 
 \_____  \_/ __ \|  |/ /\_  __ \_/ __ \   ____  \\_  __ \
 /        \  ___/|    <  |  | \/\  ___/|  | / __ \|  | \/
/_______  /\___  >__|_ \ |__|    \___  >__|(____  /__|   
        \/     \/     \/             \/         \/    
-->
<html lang="en">
<head>
<?php
	$sekretaer->theme->print_metatags( 'header' );
?>


<?php
	$sekretaer->theme->print_stylesheets();
?>

<?php
	$sekretaer->theme->print_scripts();

	?>
	
</head>
<body<?= get_class_attribute($body_classes) ?>><?php

}

function foot_html(){

	global $sekretaer;

	$sekretaer->theme->print_metatags( 'footer' );
?>

<?php
	$sekretaer->theme->print_scripts( 'footer' );

?>


</body>
</html>
<?php
}


function php_redirect( $path ) {
	global $sekretaer;

	$new_location = $sekretaer->baseurl.$path;

	header( 'location:'.$new_location );
	exit;
}


function get_navigation(){

	global $sekretaer;

	$template = $sekretaer->route->get('template');

	$navigation = array();

	$navigation[] = array(
		'name' => 'Dashboard',
		'url' => url(),
		'active' => ( $template == 'dashboard' )
	);

	if( $sekretaer->config->get('microsub') ) {
		$navigation[] = array(
			'name' => 'Read',
			'url' => url('microsub'),
			'active' => ( $template == 'microsub' )
		);
	}

	if( $sekretaer->config->get('micropub') ) {
		$navigation[] = array(
			'name' => 'Write',
			'url' => url('micropub'),
			'active' => ( $template == 'micropub' )
		);
	}

	$navigation[] = array(
		'name' => 'Logout',
		'url' => url('action/logout'),
		'active' => false
	);

	return $navigation;
}
