<?php

// NOTE: you can overwrite these options:
// - in your custom theme, via /theme/{themename}/config.php
// - and/or via the config.php in the root folder

return [
	'debug' => false,
	'logging' => false,
	'theme' => 'default',
	'scope' => array( 'read', 'create', 'follow' ),
	'microsub' => true,
	'micropub' => true,
	'cookie_lifetime' => 60*60*24*10, // 10 days
	'cache_lifetime' => 60*60*24*30, // 30 days, in seconds
];
