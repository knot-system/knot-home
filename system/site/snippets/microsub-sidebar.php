<?php

// Version: 0.1.2

if( ! $core ) exit;


$channels = $args['channels'];
$active_channel = $args['active_channel'];
$active_source = $args['active_source'];
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
	<span class="channel-meta">
		<strong><?= $channels[$active_channel]->name ?></strong>
		<p class="manage-link"><a href="<?= url('microsub/'.$active_channel.'/feeds/', false) ?>" title="manage feeds">manage</a>
		</p>
	</span>
	<?php

	if( $feeds && isset($feeds->items) && count($feeds->items) ) {
		?>
		<ul class="feeds-list">
		<?php
		foreach( $feeds->items as $item ) {

			$classes = [];
			if( $active_source && $active_source == $item->_id ) {
				$classes[] = 'active';
			}

			?>
			<li<?= get_class_attribute($classes) ?>>
				<?php
				$source_id = false;
				if( ! empty($item->_id) ) $source_id = $item->_id;

				$name = $item->url;
				if( ! empty($item->name) ) $name = $item->name;

				$image = false;
				if( ! empty($item->photo) ) $image = $item->photo;

				if( $source_id ) {
					echo '<a href="'.url('microsub/'.$active_channel.'/'.$source_id.'/', false).'">';
				}

				?>
				<span title="<?= $item->url ?>"><?php
				if( $image ) echo '<img src="'.$image.'">'; // TODO: cache locally, so we don't leak the client IP
				echo $name;
				?></span>
				<?php

				if( $source_id ) {
					echo '</a>';
				}

				?>
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
