<?php

// 2023-03-22


function add_stylesheet( $path, $type = 'theme' ) {
	global $core;
	$core->theme->add_stylesheet( $path, $type );
}

function remove_stylesheet( $path, $type = 'theme' ) {
	global $core;
	$core->theme->remove_stylesheet( $path, $type );
}


function add_script( $path, $type = 'theme', $loading = false, $footer = false ) {
	global $core;
	$core->theme->add_script( $path, $type, $loading, $footer );
}

function remove_script( $path, $type = 'theme' ) {
	global $core;
	$core->theme->remove_script( $path, $type );
}


function add_metatag( $name, $string, $position = false ) {
	global $core;
	$core->theme->add_metatag( $name, $string, $position );
}

function remove_metatag( $name, $position = false ) {
	global $core;
	$core->theme->remove_metatag( $name, $position );
}


function snippet( $path, $args = array(), $return = false ) {
	global $core;
	return $core->theme->snippet( $path, $args, $return );
}
