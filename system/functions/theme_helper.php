<?php


function head_html(){

	global $core;

	$core->theme->print_headers();

	$body_classes = array();

	$color_scheme = get_config('theme-color-scheme');
	if( $color_scheme ) $body_classes[] = 'theme-color-scheme-'.$color_scheme;

?><!DOCTYPE html>
<!--
 ____  __.              __      ___ ___                        
|    |/ _| ____   _____/  |_   /   |   \  ____   _____   ____  
|      <  /    \ /  _ \   __\ /    ~    \/  _ \ /     \_/ __ \ 
|    |  \|   |  (  <_> )  |   \    Y    (  <_> )  Y Y  \  ___/ 
|____|__ \___|  /\____/|__|    \___|_  / \____/|__|_|  /\___  >
        \/    \/                     \/              \/     \/   
-->
<html lang="en">
<head>
<?php
	$core->theme->print_metatags( 'header' );
?>


<?php
	$core->theme->print_stylesheets();
?>

<?php
	$core->theme->print_scripts();

	?>
	
</head>
<body<?= get_class_attribute($body_classes) ?>><?php

}

function foot_html(){

	global $core;

	$core->theme->print_metatags( 'footer' );
?>

<?php
	$core->theme->print_scripts( 'footer' );

?>


</body>
</html>
<?php
}


function php_redirect( $path ) {
	global $core;

	$new_location = $core->baseurl.$path;

	header( 'location:'.$new_location );
	exit;
}


function get_navigation(){

	global $core;

	$template = $core->route->get('template');

	$navigation = array();

	$navigation[] = array(
		'name' => '🗞️',
		'title' => 'Dashboard',
		'url' => url('dashboard'),
		'active' => ( $template == 'dashboard' )
	);

	if( get_config('microsub') ) {
		$navigation[] = array(
			'name' => 'Read',
			'title' => 'read feeds',
			'url' => url('microsub'),
			'active' => ( $template == 'microsub' )
		);
	}

	if( get_config('micropub') ) {
		$navigation[] = array(
			'name' => 'Write',
			'title' => 'write a new post',
			'url' => url('micropub'),
			'active' => ( $template == 'micropub' )
		);
	}

	return $navigation;
}
