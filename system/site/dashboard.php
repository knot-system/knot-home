<?php

if( ! $core ) exit;

snippet( 'header' );


?>

<h2>Dashboard</h2>

<h3>Session Info:</h3>
<ul>
	<li><strong>Login Username:</strong> <?= $core->user->get('me') ?></li>
	<li><strong>Short Username:</strong> <?= $core->user->get('name') ?></li>
	<?php
	if( $core->user->get('access_token') ) {
		echo '<li><strong>Access Token:</strong> <abbr title="'.$core->user->get('access_token').'">*******</abbr></li>';
	}
	if( $core->user->get('scope') ) {
		echo '<li><strong>Scope:</strong> '.$core->user->get('scope').'</li>';
	}
	if( $core->user->get('microsub_endpoint') ) {
		echo '<li><strong>Microsub Endpoint:</strong> '.$core->user->get('microsub_endpoint').'</li>';
	}
	if( $core->user->get('micropub_endpoint') ) {
		echo '<li><strong>Micropub Endpoint:</strong> '.$core->user->get('micropub_endpoint').'</li>';
	}
	?>
</ul>

<?php

snippet( 'footer' );
