<?php

// these options are displayed in the 'knot-control' module

return [
	'theme' => [
		'type' => 'theme',
		'description' => 'you can add more themes in the <code>theme/</code> subfolder',
	],
	'theme-color-scheme' => [
		'type' => 'array',
		'description' => 'not all themes support (all) color schemes',
		'options' => ['default' => 'Default (blue)', 'green' => 'Green', 'red' => 'Red', 'lilac' => 'Lilac'],
	],
	'microsub' => [
		'type' => 'bool',
		'description' => 'set to <code>false</code> to disable the <code>read</code> tab',
	],
	'micropub' => [
		'type' => 'bool',
		'description' => 'set to <code>false</code> to disable the <code>write</code> tab',
	],
	'datetime_format' => [
		'type' => 'string',
		'description' => 'the format for date and time; see <a href="https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters" target="_blank">this list</a> for possible parameters',
	],
	'allowed_urls' => [
		'type' => 'complex',
		'description' => 'an array with urls of allowed users (<em>me</em> parameters)',
	],
	'link_preview_nojs_refresh' => [
		'type' => 'bool',
		'description' => 'refresh link previews via PHP; this makes pageloading with preview links slower, but does not rely on JavaScript to fetch link previews in the background',
	],
	'link_preview_autorefresh' => [
		'type' => 'bool',
		'description' => 'set to <code>true</code> to automatically refresh link previews instead of showing a <em>refresh</em> icon; this will result in layout shifts after the site is loaded',
	],
	'show_item_content' => [
		'type' => 'bool',
		'description' => 'if set to <code>true</code>, show the content of feed items; or if set to <code>false</code>, show only preview-links without the content',
	],
];
