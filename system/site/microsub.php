<?php

if( ! $sekretaer ) exit;



$microsub = new Microsub();

$channels = $microsub->get_channels();

$active_channel = false;
if( isset($_GET['channel']) ) $active_channel = $_GET['channel'];
if( ! array_key_exists( $active_channel, $channels ) ) $active_channel = false;


if( ! $active_channel ) {
	// Note: if no channel is selected, automatically show the first channel that is not 'notifications'
	$channels_cleaned = $channels;
	unset($channels_cleaned['notifications']);
	$active_channel = array_key_first($channels_cleaned);
}


$action = false;
if( isset($_GET['action']) ) $action = $_GET['action'];



$sidebar_content = '';


ob_start();
if( ! empty($channels) ) {
	// overview: list channels
	?>
	<p class="edit-link"><a href="<?= url('microsub/?action=channels', false) ?>">Edit</a></p>
	<ul class="channels-list">
	<?php
	foreach( $channels as $channel ) {

		$classes = [];
		if( $active_channel && $channel->uid == $active_channel ) {
			$classes[] = 'active';
		}
		
		?>
		<li<?= get_class_attribute($classes) ?>>
			<a href="<?= url('microsub') ?>?channel=<?= $channel->uid; ?>">
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
	<p class="edit-link"><a href="<?= url('microsub/?channel='.$active_channel.'&action=feeds', false) ?>">Edit</a></p>
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
		echo '<p>- no feeds found -</p>';
	}
}


$sidebar_content = ob_get_contents();
ob_end_clean();



snippet( 'header', array(
	'sidebar-content' => $sidebar_content
) );



if( $action == 'channels' ) {

	echo '<h2>Manage Channels</h2>';


	// TODO: manage channels
	// - add new channel
	// - reorder channels
	// - rename channel
	// - hide/unhide channel

	echo '<p>not implemented yet</p>'; // DEBUG
	snippet( 'footer' ); // DEBUG
	exit; // DEBUG


	echo '<ul class="channels-list">';
	foreach( $channels as $channel ) {
		?>
		<li>
			<?= $channel->name ?>	
		</li>
		<?php
	}
	echo '</ul>';


	snippet( 'footer' );
	exit;

}


if( $active_channel ) {
	// content of channel

	if( $action == 'feeds' ) {

		echo '<h2>Manage Feeds</h2>';

		// TODO: manage feeds
		// - rename feed
		// - mute/unmute feed
		// - block/unblock feed

		?>
		<ul class="feeds-list">
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
					<a class="button button-small" href="<?= url('microsub/?channel='.$active_channel.'&action=unfollow&feed='.urlencode($item->url), false) ?>">unfollow</a>
				</li>
				<?php
			}
			?>
				<li>
					<a class="button add-feed" href="<?= url('microsub/?channel='.$active_channel.'&action=add', false ) ?>">+ add a new feed</a>
				</li>
			</ul>
		<?php

	} elseif( $action == 'add' ) {
		// add new feed

		echo '<p><a class="button" href="'.url('microsub?channel='.$active_channel.'&action=feeds', false).'">cancel</a></p>';

		if( isset($_POST['url']) ) {

			$search_url = $_POST['url'];

			// TODO: search for feed urls: https://indieweb.org/Microsub-spec#Search
			//$sources = $microsub->api_get( 'search', array( 'query' => $search_url ) );

			// TODO: add feed validation (currently, everything gets added, even if its not a valid feed)

			// follow feed - https://indieweb.org/Microsub-spec#Following
			$response = $microsub->api_post( 'follow', [
				'channel' => $active_channel,
				'url' => $search_url
			] );

			echo '<p><strong>server response:</strong></p>';
			echo '<pre>';
			var_dump($response);
			echo '</pre>';

			echo '<a href="'.url('microsub?channel='.$active_channel.'&action=feeds&refresh=true', false).'">&raquo; back to the channel overview</a>';

		} else {
			?>
			<form method="POST" action="<?= url('microsub?channel='.$active_channel.'&action=add', false ) ?>">
				<p><strong>currently, the url does not get validated. only add valid json, rss or atom feeds.</strong></p>
				<label style="display: inline-block;">Feed URL (json, rss, atom, ...): <input type="url" name="url" placeholder="https://www.example.com/feed/rss" style="min-width:400px;"></label>
				<button>add feed</button>
			</form>
			<?php
		}

	} elseif( $action == 'unfollow' ) {

		$feed = urldecode($_GET['feed']);

		// TODO: validate that this feed exists in the channel

		if( isset($_GET['confirmation']) && $_GET['confirmation'] == 'true' ) {

			$response = $microsub->api_post( 'unfollow', [
				'channel' => $active_channel,
				'url' => $feed
			] );

			echo '<p><strong>server response:</strong></p>';
			echo '<pre>';
			var_dump($response);
			echo '</pre>';

			echo '<a href="'.url('microsub?channel='.$active_channel.'&refresh=true', false).'">&raquo; back to the channel overview</a>';

		} else {

			echo '<p>do you really want to unfollow <strong>'.$feed.'</strong>?</p>';
			echo '<p><a class="button" href="'.url('microsub/?channel='.$active_channel.'&action=unfollow&confirmation=true&feed='.$_GET['feed'], false).'">yes, unfollow<a> <a class="button" href="'.url('microsub/?channel='.$active_channel.'&action=feeds', false).'">no, abort</a></p>';

		}

	} else {
		// list posts

		$items_args = array(
			'channel' => $active_channel,
			'limit' => 20,
		);

		if( isset($_GET['after']) ) {
			$items_args['after'] = $_GET['after'];
		}
		if( isset($_GET['before']) ) {
			$items_args['before'] = $_GET['before'];
		}

		$items = $microsub->api_get( 'timeline', $items_args );

		if( $items && isset($items->items) && count($items->items) ) {

			if( ! empty($items->paging) ) {

				$paging = $items->paging;

				echo '<ul class="pagination">';
				if( ! empty($paging->before) ) {
					echo '<li><a class="button" href="'.url('microsub/?channel='.$active_channel.'&before='.$paging->before, false).'">&laquo; previous page</a></li>';
				}
				if( ! empty($paging->after) ) {
					echo '<li><a class="button" href="'.url('microsub/?channel='.$active_channel.'&after='.$paging->after, false).'">next page &raquo;</a></li>';
				}
				echo '</ul>';

			}

			?>
			<ul class="posts">
			<?php
			foreach( $items->items as $item ) {

				$date = new DateTimeImmutable($item->published);

				$datetime_format = $sekretaer->config->get('datetime_format');
				$datetime = $date->format( $datetime_format );

				$author_name = false;
				if( ! empty($item->author->name) ) {
					$author_name = $item->author->name;
				} elseif( ! empty($item->author) ) {
					$author_name = $item->author;
				}

				$author_url = false;
				if( ! empty($item->author->url) ) {
					$author_url = $item->author->url;
				}

				$feed_title = false;
				$feed_link = false;
				if( ! empty($item->_source) ) {
					if( ! empty($item->_source->name) ) $feed_title = $item->_source->name;
					if( ! empty($item->_source->url) ) $feed_link = $item->_source->url;
				}

				$content = false;
				if( ! empty($item->content->html) ) {
					$html = $item->content->html;
					$html = str_replace(array("\r\n", "\r", "\n"), '', $html );
					$text = new Text( $html );
					$text = $text->remove_html_elements()->auto_p();
					$content = $text->get();
				} elseif( ! empty($item->content->text) ) {
					$content = $item->content->text;
				}


				?>
				<li>
					<span class="item-content">
						<?php			

						if( $feed_title ) {
							?>
							<span class="item-feed-title"><?php
							if( $feed_link ) echo '<a href="'.$feed_link.'" target="_blank" rel="noopener">';
								echo $feed_title;
							if( $feed_link ) echo '</a>';
							?></span>
							<?php

							if( ! empty($item->category) ) {
								$categories = $item->category;
								$categories = array_map(function($c){return '#'.$c;}, $categories);
								if( is_array($categories) ) $categories = implode(' ', $categories);
								echo ' '.$categories;
							}

						}

						if( ! empty($item->name) ) echo '<h3 class="item-title">'.$item->name.'</h3>';
					
						if( ! empty($item->photo) ) {
							if( ! is_array($item->photo) ) $item->photo = array($item->photo);

							foreach( $item->photo as $photo ) {
								echo '<img src="'.$photo.'"><br>';
							}
						}

						?>
						<p>
							<?= $content ?>
						</p>

					</span>

					<p class="item-meta">
						<small>
							<?php

							if( $author_name ) {
								if( $author_url ) {
									echo '<a href="'.$author_url.'" target="_blank" rel="noopener">';
								}
								if( $author_name ) echo $author_name;
								if( $author_url ) {
									echo '</a>';
								}

								echo ', ';
							}
							
							echo $datetime;

							?>
						</small>
					</p>
					<p class="item-actions"><a class="button" href="<?= $item->url ?>" target="_blank" rel="noopener">read full post <sup>🡥</sup></a> <a class="button" href="<?= url('micropub') ?>?content=<?= urlencode($item->url) ?>">share this post</a></p>
					<hr>
				</li>
				<?php
			}


			if( ! empty($items->paging) ) {

				$paging = $items->paging;

				echo '<ul class="pagination">';
				if( ! empty($paging->before) ) {
					echo '<li><a class="button" href="'.url('microsub/?channel='.$active_channel.'&before='.$paging->before, false).'">&laquo; previous page</a></li>';
				}
				if( ! empty($paging->after) ) {
					echo '<li><a class="button" href="'.url('microsub/?channel='.$active_channel.'&after='.$paging->after, false).'">next page &raquo;</a></li>';
				}
				echo '</ul>';

			}

			?>
			</ul>
			<?php
		} else {
			echo '<p>- no posts found -</p>';
			if( ! empty($items->paging) ) {

				$paging = $items->paging;
				if( ! empty($items_args['before']) || ! empty($items_args['after']) ) {
					echo '<a class="button" href="'.url('microsub/?channel='.$active_channel, false).'">go to first page</a>';
				}

			}
		}

	}
	
}


snippet( 'footer' );
