<?php

// NOTE: you can overwrite these options:
// - in your custom theme, via /theme/{themename}/config.php
// - and/or via the config.php in the root folder

return [
	'debug' => false, // show additional information when an error occurs
	'logging' => true, // write logfiles into the /log directory
	'theme' => 'default',
	'theme-color-scheme' => 'default', // depends on the theme; the default theme supports 'blue' (default), 'green', 'red', 'lilac'
	'scope' => array( 'read', 'create', 'follow', 'channels', 'follow', 'mute', 'block' ),
	'microsub' => true, // set to false to disable the 'read' tab
	'micropub' => true, // set to false to disable the 'write' tab
	'homepage' => 'dashboard', // can be 'dashboard', 'microsub' or 'micropub'
	'cookie_lifetime' => 60*60*24*10, // 10 days, in seconds
	'cache_lifetime' => 60*60*24*30, // 30 days, in seconds
	'session_lifetime' => 60*60*24*10, // 10 days, in seconds
	'datetime_format' => 'Y-m-d H:i', // see this list for possible parameters: https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters
	'allowed_html_elements' => [ 'del', 'pre', 'blockquote', 'code', 'b', 'strong', 'u', 'i', 'em', 'ul', 'ol', 'li', 'p', 'br', 'span', 'a', 'img', 'video', 'audio' ], // allowed html elements for post content (in read mode), everything else (like <script> or <iframe> tags) gets stripped
	'allowed_urls' => [], // an array with urls of allowed users ('me' parameters)
	'user_agent' => 'maxhaesslein/sekretaer/', // version will be automatically appended
	'image_target_width' => 800,
	'preview_target_width' => 800,
	'image_jpg_quality' => 70, // quality of jpg images; you neeed to empty the cache when changing this option
	'image_png_to_jpg' => true, // convert png images to jpg (faster, but looses transparency); you need to empty the cache when changing this option
	'image_background_color' => [ 255, 255, 255 ], // backgroundcolor for transparent images, when 'image_png_to_jpg' option is set to true; you need to empty the cache when changing this option
	'link_preview_max_age' => 60*60*6, // refresh link previews after x seconds
	'link_preview_nojs_refresh' => false, // refresh link previews via PHP; this makes pageloading with preview links slower, but does not rely on JavaScript to fetch link previews in the background
	'show_item_content' => true, // show content of feed items (set to true) or only preview-links (set to false)
];
