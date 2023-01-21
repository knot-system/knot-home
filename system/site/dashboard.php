<?php

if( ! $sekretaer ) exit;

snippet( 'header' );

?>

<h2>Dashboard</h2>

<h3>Session Info:</h3>
<ul>
	<li><strong>User:</strong> <?= $_SESSION['me'] ?></li>
	<li><strong>Short Username:</strong> <?= $_SESSION['name'] ?></li>
	<?php
	if( isset($_SESSION['access_token']) ) {
		echo '<li><strong>Access Token:</strong> <abbr title="'.$_SESSION['access_token'].'">*******</abbr></li>';
	}
	if( isset($_SESSION['scope']) ) {
		echo '<li><strong>Scope:</strong> '.$_SESSION['scope'].'</li>';
	}
	if( isset($_SESSION['microsubEndpoint']) ) {
		echo '<li><strong>Microsub Endpoint:</strong> '.$_SESSION['microsubEndpoint'].'</li>';
	}
	if( isset($_SESSION['micropubEndpoint']) ) {
		echo '<li><strong>Micropub Endpoint:</strong> '.$_SESSION['micropubEndpoint'].'</li>';
	}
	?>
</ul>


<pre style="font-size: 10px; opacity: 0.5; overflow: auto; margin: 40px 0;">
<?php var_dump($_SESSION); ?>
</pre>


<?php

snippet( 'footer' );
