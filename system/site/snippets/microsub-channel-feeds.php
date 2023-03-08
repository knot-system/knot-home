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
<a class="button disabled">import feeds</a>
<a class="button export-feed" href="<?= url('microsub/'.$active_channel.'/export/', false ) ?>">export feed list</a>

<ul class="feeds-list" style="margin-top: 2em;">
	<li>
	</li>
	<?php
	foreach( $feeds->items as $item ) {
		?>
		<li>
			<?php
			$name = $item->url;
			if( ! empty($item->name) ) $name = $item->name;

			echo '<span title="'.$item->url.'">'.$name.'</span>';

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
