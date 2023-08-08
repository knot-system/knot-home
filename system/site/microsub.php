<?php

if( ! $core ) exit;


$active_channel = $core->route->get('channel'); // TODO rename to $channel
$action = $core->route->get('action');


if( $active_channel == 'manage' ) {
	$active_channel = false;
	$action = 'channels';
}


$microsub = new Microsub();


$channels = $microsub->get_channels();
if( ! array_key_exists( $active_channel, $channels ) ) $active_channel = false;


if( ! $active_channel ) {
	// NOTE: if no channel is selected, automatically show the first channel that is not 'notifications'
	$channels_cleaned = $channels;
	unset($channels_cleaned['notifications']);
	$active_channel = array_key_first($channels_cleaned);
}

$feeds = false;

if( $action == 'channels' ) {
	$active_channel = false;
	$feeds = false;
}


if( $active_channel ) {
	$feeds = $microsub->get_feeds( $active_channel );
}


// TODO: move exporting elsewhere?
if( $active_channel && $action == 'export' && ! empty($_GET['type']) ) {

	$type = $_GET['type'];

	$feeds = $microsub->get_feeds( $active_channel );

	$channel = $channels[$active_channel];
	$channel_name = $channel->name;
	$channel_name_sanitized = sanitize_string_for_url($channel_name);

	$filename = date('Y-m-d_H-i-s', time()).'_knot-home_'.$channel_name_sanitized.'_feedlist';

	$content = false;
	if( $type == 'opml' ) {

		$date_time = new DateTime();
		$date_time = $date_time->format(DateTime::RFC822);

		// spec: http://opml.org/spec2.opml
		$content = [];
		$content[] = '<?xml version="1.0" encoding="ISO-8859-1"?>';
		$content[] = '<opml version="2.0">';
		$content[] = '<head>';
		$content[] = '<title>Knot Home Feed List</title>';
		$content[] = '<dateCreated>'.$date_time.'</dateCreated>';
		$content[] = '<dateModified>'.$date_time.'</dateModified>';
		$content[] = '<docs>http://opml.org/spec2.opml</docs>';
		$content[] = '</head>';
		$content[] = '<body>';
		foreach( $feeds->items as $item ) {
			$url = $item->url;
			$name = $url;
			if( isset($item->name) ) $name = $item->name;
			$type = 'rss'; // TODO / CLEANUP: check this
			$content[] = '<outline text="'.$name.'" type="'.$type.'" xmlUrl="'.$url.'"/>';
		}
		$content[] = '</body>';
		$content[] = '</opml>';

		$content = implode( "\r\n", $content );

		$filename .= '.opml';

	} elseif( $type == 'json' ) {

		$content = json_encode( $feeds->items );

		$filename .= '.json';

	} elseif( $type == 'txt' ) {

		foreach( $feeds->items as $item ) {
			$content .= $item->url;
			$content .= "\r\n";
		}

		$filename .= '.txt';

	}


	if( $content ) {

		// force download
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename='.$filename );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		echo $content;

		exit;
	}

}


$active_source = false;
if( $active_channel && ! empty($action) ) {
	$active_source = $action;
}


$sidebar_content = '';


ob_start();
snippet( 'microsub-sidebar', [
	'channels' => $channels,
	'active_channel' => $active_channel,
	'active_source' => $active_source,
	'microsub' => $microsub,
] );
$sidebar_content = ob_get_contents();
ob_end_clean();



snippet( 'header', array(
	'sidebar-content' => $sidebar_content
) );


if( $action == 'channels' ) {
	$snippet = 'microsub-channels';
} elseif( $action == 'feeds' ) {
	$snippet = 'microsub-channel-feeds';
} elseif( $action == 'import' ) {
	$snippet = 'microsub-channel-import';
} elseif( $action == 'export' ) {
	$snippet = 'microsub-channel-export';
} elseif( $action == 'add' ) {
	$snippet = 'microsub-channel-add';
} elseif( $action == 'unfollow' ) {
	$snippet = 'microsub-channel-unfollow';
} else {
	$snippet = 'microsub-channel-list';
}


snippet($snippet, [
	'channels' => $channels,
	'active_channel' => $active_channel,
	'active_source' => $active_source,
	'feeds' => $feeds,
	'microsub' => $microsub
]);



snippet( 'footer' );
