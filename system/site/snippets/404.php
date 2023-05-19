<?php

// Version: 0.1.4

if( ! $core ) exit;

$homepage = trailing_slash_it(get_config('homepage'));

?>
<article>
	<h2>Nothing here :(</h2>
	<p>This page does not exist. Go to the <a href="<?= url($homepage) ?>">home page</a>.</p>
</article>