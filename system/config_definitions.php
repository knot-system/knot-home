<?php

// these options are displayed in the 'homestead-control' module

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
		'description' => '',
	],
	'micropub' => [
		'type' => 'bool',
		'description' => '',
	],
	'datetime_format' => [
		'type' => 'string',
		'description' => '',
	],
	'link_preview_nojs_refresh' => [
		'type' => 'bool',
		'description' => '',
	],
	'link_preview_autorefresh' => [
		'type' => 'bool',
		'description' => '',
	],
	'show_item_content' => [
		'type' => 'bool',
		'description' => '',
	],
];
