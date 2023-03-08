<?php

// Version: alpha.7

if( ! $sekretaer ) exit;


$channels = $args['channels'];
$active_channel = $args['active_channel'];
$microsub = $args['microsub'];


if( ! empty($channels) ) {
	// overview: list channels
	?>
	<p class="manage-link"><a href="<?= url('microsub/manage/', false) ?>" title="manage channels">manage</a></p>
	<ul class="channels-list">
	<?php
	foreach( $channels as $channel ) {

		$classes = [];
		if( $active_channel && $channel->uid == $active_channel ) {
			$classes[] = 'active';
		}
		
		?>
		<li<?= get_class_attribute($classes) ?>>
			<a href="<?= url('microsub/'.$channel->uid) ?>">
				<?php
				echo $channel->name;
				if( isset($channel->unread) ) echo '*'.$channel->unread;
				?>	
			</a>
		</li>
		<?php
	}
	?>
	</ul>
	<?php
}


if( $active_channel && $active_channel != 'notifications' ) {
	$feeds = $microsub->get_feeds( $active_channel );

	?>
	<hr>
	<p class="manage-link"><a href="<?= url('microsub/'.$active_channel.'/feeds/', false) ?>" title="manage feeds">manage</a></p>
	<?php
	if( $feeds && isset($feeds->items) && count($feeds->items) ) {
		?>
		<ul class="feeds-list">
		<?php
		foreach( $feeds->items as $item ) {
			?>
			<li>
				<?php
				$name = $item->url;
				if( ! empty($item->name) ) $name = $item->name;

				?>
				<span title="<?= $item->url ?>"><?= $name ?></span>
			</li>
			<?php
		}
		?>
		</ul>
		<?php
	} else {
		?>
		<p>(no feeds found)</p>
		<p><a class="button add-feed" href="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">+ add a new feed</a></p>
		<?php
	}
}
