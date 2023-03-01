<?php

// NOTE: you can overwrite these options:
// - in your custom theme, via /theme/{themename}/config.php
// - and/or via the config.php in the root folder

return [
	'debug' => false,
	'logging' => false,
	'theme' => 'default',
	'theme-color-scheme' => 'default', // depends on the theme; the default theme supports 'blue', 'green', 'red', 'lilac'
	'scope' => array( 'read', 'create', 'follow', 'channels', 'follow', 'mute', 'block' ),
	'microsub' => true,
	'micropub' => true,
	'cookie_lifetime' => 60*60*24*10, // 10 days, in seconds
	'cache_lifetime' => 60*60*24*30, // 30 days, in seconds
	'datetime_format' => 'Y-m-d H:i', // see this list for possible parameters: https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters
	'allowed_html_elements' => [ 'p', 'br', 'i', 'b', 'em', 'strong', 'a', 'ul', 'ol', 'li', 'span', 'img', 'video' ], // allowed html elements for post content (in read mode), everything else (like <script> or <iframe> tags) gets stripped
];
