<?php

$abspath = realpath(dirname(__FILE__)).'/';
$abspath = preg_replace( '/system\/$/', '', $abspath );


if( ! file_exists($abspath.'config.php')
 || ! file_exists($abspath.'.htaccess')
 || isset($_GET['setup'])
) {
	// run the setup if we are missing required files

	include_once( $abspath.'system/setup.php' );

} elseif( isset($_GET['update'])
 && ( file_exists($abspath.'update') || file_exists($abspath.'update.txt') )
) {
	// run the update if we request it

	include_once( $abspath.'system/update.php' );
	exit;
	
}


include_once( $abspath.'system/functions.php' );
include_once( $abspath.'system/classes.php' );


$sekretaer = new Sekretaer();


// here we gooo

$sekretaer->theme->load();


$template = $sekretaer->route->get('template');
if( ! file_exists( $sekretaer->abspath.'system/site/'.$template.'.php') ){
	$sekretaer->debug( 'template not found!', $template );
	exit;
}

$sekretaer->include( 'system/site/'.$template.'.php' );
