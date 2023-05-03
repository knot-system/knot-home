<?php

// Version: 0.1.2

if( ! $core ) exit;


$active_channel = $args['active_channel'];
$active_source = $args['active_source'];
$microsub = $args['microsub'];



$items_args = array(
	'channel' => $active_channel,
	'limit' => 20,
);

if( $active_source ) {
	$items_args['source'] = $active_source;
}

if( isset($_GET['after']) ) {
	$items_args['after'] = $_GET['after'];
}
if( isset($_GET['before']) ) {
	$items_args['before'] = $_GET['before'];
}

$items = $microsub->api_get( 'timeline', $items_args );

if( $items && isset($items->items) && count($items->items) ) {

	if( ! empty($items->paging) ) {

		snippet( 'microsub-pagination', [
			'paging' => $items->paging,
			'active_channel' => $active_channel,
			'active_source' => $active_source
		]);

	}

	?>
	<ul class="posts">
	<?php
	foreach( $items->items as $item ) {
		snippet( 'microsub-channel-list-item', [
			'item' => $item,
			'active_channel' => $active_channel
		]);
	}
	?>
	</ul>
	<?php


	if( ! empty($items->paging) ) {

		snippet( 'microsub-pagination', [
			'paging' => $items->paging,
			'active_channel' => $active_channel,
			'active_source' => $active_source
		]);

	}


} else {

	echo '<p>- no posts found -</p>';
	
	if( ! empty($items->paging) ) {
		$paging = $items->paging;
		if( ! empty($items_args['before']) || ! empty($items_args['after']) ) {
			$link = 'microsub/'.$active_channel.'/';
			if( $active_source ) $link .= $active_source;
			echo '<a class="button" href="'.url($link).'">go to first page</a>';
		}
	}

}
