<?php

// Version: 0.1.0

if( ! $core ) exit;

$active_channel = $args['active_channel'];
$microsub = $args['microsub'];


$feed = urldecode($_GET['feed']);

// TODO: validate that this feed exists in the channel


if( isset($_GET['confirmation']) && $_GET['confirmation'] == 'true' ) {
	// TODO: move elswhere?

	$response = $microsub->api_post( 'unfollow', [
		'channel' => $active_channel,
		'url' => $feed
	] );

	if( $response['status_code'] == 200 ) {

		echo '<p>successfully unfollowed <strong>'.$feed.'</strong></p>';

	} else {

		echo '<p><strong>server response:</strong></p>';
		echo '<pre>';
		var_dump($response);
		echo '</pre>';
	}

	echo '<a href="'.url('microsub/'.$active_channel.'/feeds/', false).'">&raquo; back to the feeds management</a>';

} else {

	echo '<p>do you really want to unfollow <strong>'.$feed.'</strong>?</p>';
	echo '<p><a class="button" href="'.url('microsub/'.$active_channel.'/unfollow/?confirmation=true&feed='.$_GET['feed'], false).'">yes, unfollow<a> <a class="button" href="'.url('microsub/'.$active_channel.'/feeds/', false).'">no, abort</a></p>';

}

