<?php

// Version: 0.1.3

if( ! $core ) exit;

$homepage = trailing_slash_it($core->config->get('homepage'));

?>
<article>
	<h2>Nothing here :(</h2>
	<p>This page does not exist. Go to the <a href="<?= url($homepage) ?>">home page</a>.</p>
</article>