<?php

// Version: alpha.7

if( ! $sekretaer ) exit;


$active_channel = $args['active_channel'];
$feeds = $args['feeds'];



// TODO: rename feed
// TODO: mute/unmute feed
// TODO: block/unblock feed

?>
<h2>Manage Feeds</h2>

<a class="button add-feed" href="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">+ add a new feed</a>
<a class="button import-feed" href="<?= url('microsub/'.$active_channel.'/import' ) ?>">import feeds</a>
<a class="button export-feed" href="<?= url('microsub/'.$active_channel.'/export' ) ?>">export feeds</a>

<ul class="feeds-list" style="margin-top: 2em;">
	<li>
	</li>
	<?php
	foreach( $feeds->items as $item ) {
		?>
		<li>
			<?php
			$url = $item->url;

			$name = $url;
			if( ! empty($item->name) ) $name = $item->name;

			$image = false;
			if( ! empty($item->photo) ) $image = $item->photo;

			$description = false;
			if( ! empty($item->description) ) $description = $item->description;

			echo '<span>';
				if( $image ) echo '<img src="'.$image.'">'; // TODO: cache locally, so we don't leak the client IP
				echo '<strong>'.$name.'</strong>';
			echo '</span>';

			if( $description ) echo '<br>'.$description;

			if( $url != $name ) echo '<br><small>'.$url.'</small>';

			?>
			<br>
			<a class="button button-small disabled">mute</a>
			<a class="button button-small disabled">block</a>
			<a class="button button-small" href="<?= url('microsub/'.$active_channel.'/unfollow?feed='.urlencode($item->url), false) ?>">unfollow</a>
		</li>
		<?php
	}
	?>
</ul>
