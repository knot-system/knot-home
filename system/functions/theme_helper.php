<?php


function head_html(){

	global $core;

	$body_classes = array();

	$color_scheme = get_config('theme-color-scheme');
	if( $color_scheme ) $body_classes[] = 'theme-color-scheme-'.$color_scheme;

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
