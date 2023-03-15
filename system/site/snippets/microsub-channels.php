<?php

// Version: alpha.8

if( ! $core ) exit;

$channels = $args['channels'];
$microsub = $args['microsub'];


// TODO: split up into multiple snippets


// TODO: reorder channels
// TODO: hide/unhide channel


?>
<h2>Manage Channels</h2>

<?php
if( isset($_GET['new']) ) {

	if( ! empty($_POST['name']) ) {

		$new_name = $_POST['name'];

		$response = $microsub->api_post( 'channels', [
			'name' => $new_name,
		] );

		echo '<p><strong>server response:</strong></p>';
		echo '<pre>';
		var_dump($response);
		echo '</pre>';

		echo '<a href="'.url('microsub/manage/?refresh=true', false).'">&raquo; back to channel management</a>';

	} else {

		?>
		<a class="button" href="<?= url('microsub/manage/', false) ?>">cancel</a>

		<form method="POST" action="<?= url('microsub/manage/?new', false ) ?>" style="margin-top: 2em;">
			<label style="display: inline-block;"><input type="text" name="name" placeholder="New Channel Name" required autofocus></label>
			<button>add channel</button>
		</form>

		<?php

	}

} elseif( ! empty($_GET['rename']) ) {

	$uid = urldecode($_GET['rename']);

	if( ! array_key_exists( $uid, $channels) ) exit;

	$old_channel = $channels[$uid];
	$old_name = $old_channel->name;

	if( ! empty($_POST['name']) ) {

		$new_name = $_POST['name'];

		$response = $microsub->api_post( 'channels', [
			'channel' => $uid,
			'name' => $new_name,
		] );

		echo '<p><strong>server response:</strong></p>';
		echo '<pre>';
		var_dump($response);
		echo '</pre>';

		echo '<a href="'.url('microsub/manage/?refresh=true', false).'">&raquo; back to channel management</a>';

	} else {

		?>
		<a class="button" href="<?= url('microsub/manage/', false) ?>">cancel</a>

		<form method="POST" action="<?= url('microsub/manage/?rename='.$uid, false ) ?>" style="margin-top: 2em;">
			<label style="display: inline-block;"><input type="text" name="name" value="<?= $old_name ?>" placeholder="<?= $old_name ?>" required autofocus></label>
			<button>rename channel '<?= $old_name ?>'</button>
		</form>

		<?php

	}

} elseif( ! empty($_GET['delete']) ) {

	$uid = urldecode($_GET['delete']);

	if( ! array_key_exists( $uid, $channels) ) exit;

	$selected_channel = $channels[$uid];
	$selected_name = $selected_channel->name;

	if( ! empty($_POST['aknowledge']) ) {

		$response = $microsub->api_post( 'channels', [
			'channel' => $uid,
			'method' => 'delete',
		] );

		echo '<p><strong>server response:</strong></p>';
		echo '<pre>';
		var_dump($response);
		echo '</pre>';

		echo '<a href="'.url('microsub/manage/?refresh=true', false).'">&raquo; back to channel management</a>';

	} else {

		?>
		<a class="button" href="<?= url('microsub/manage/', false) ?>">cancel</a>

		<form method="POST" action="<?= url('microsub/manage/?delete='.$uid, false ) ?>" style="margin-top: 2em;">
			<label><input type="checkbox" name="aknowledge" value="true" required> yes, delete the channel <?= $selected_name ?> and all of its content</label>
			<button>delete channel '<?= $selected_name ?>'</button>
		</form>

		<?php

	}

} else {

	?>
	<a class="button add-channel" href="<?= url('microsub/manage/?new', false) ?>">+ add a new channel</a>
	<?php

	echo '<ul class="channels-list" style="margin-top: 2em;">';
	foreach( $channels as $channel ) {

		if( $channel->uid == 'notifications' ) continue; // skip Notifications channel

		$feed_count = 0;

		$feeds = $microsub->get_feeds($channel->uid);
		if( $feeds && ! empty($feeds->items) ) {
			$feed_count = count($feeds->items);
		}

		?>
		<li>
			<span><?= $channel->name ?> (<?= $feed_count ?> Feeds)</span>
			<br>
			<a class="button button-small disabled">hide</a>
			<a class="button button-small" href="<?= url('microsub/manage/?rename='.urlencode($channel->uid), false) ?>">rename</a>
			<a class="button button-small" href="<?= url('microsub/manage/?delete='.urlencode($channel->uid), false) ?>">delete</a>
		</li>
		<?php
	}

	echo '</ul>';
}


snippet( 'footer' );
exit;