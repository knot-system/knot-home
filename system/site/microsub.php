<?php

if( ! $sekretaer ) exit;


$microsub = new Microsub();


$sidebar_content = '';


ob_start();
$channels = $microsub->api_get( 'channels' );
if( $channels && isset($channels->channels) && count($channels->channels) ) {
	// overview: list channels
	?>
	<ul class="channels-list">
	<?php
	foreach( $channels->channels as $channel ) {

		$classes = [];
		if( isset($_GET['channel']) && $channel->uid == $_GET['channel'] ) {
			$classes[] = 'active';
		}
			
		?>
		<li<?= get_class_attribute($classes) ?>><a href="<?= url('microsub') ?>?channel=<?= $channel->uid; ?>"><?php
		echo $channel->name;
		if( isset($channel->unread) ) echo ' ['.$channel->unread.' unread]';
		?></a></li>
		<?php
	}
	?>
	</ul>
	<?php
} else {
	if( isset($_GET['debug']) ) {
		echo '<strong>CHANNELS ERROR:</strong>';
		echo '<code><pre>';
		var_dump($channels);
		echo '</pre></code>';
	}
}

if( isset($_GET['channel']) ) {
	// needs 'follow' scope
	$feeds = $microsub->api_get( 'follow', array( 'channel' => $_GET['channel'] ) );
	if( $feeds && isset($feeds->items) && count($feeds->items) ) {
		?>
		<hr>
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
				<a class="button button-small" href="<?= url('microsub/?channel='.$_GET['channel'].'&action=unfollow&feed='.urlencode($item->url), false) ?>">unfollow</a>
				<?php
				// TODO: add 'mute'/'unmute' button
				?>
			</li>
			<?php
		}
		?>
			<li>
				<a class="button add-feed" href="<?= url('microsub/?channel='.$_GET['channel'].'&action=add', false ) ?>">+ add a new feed</a>
			</li>
		</ul>
		<?php
	} else {
		if( isset($_GET['debug']) ) {
			echo '<strong>FEEDS ERROR:</strong>';
			echo '<code><pre>';
			var_dump($feeds);
			echo '</pre></code>';
		}
	}
}


$sidebar_content = ob_get_contents();
ob_end_clean();



snippet( 'header', array(
	'sidebar-content' => $sidebar_content
) );


// TODO: this is copied from the quick prototype. we need to rewrite this.

if( ! isset($_SESSION['microsub_endpoint']) ) {
	echo '<p>no microsub endpoint found for '.$_SESSION['me'].'</p>';
	// TODO: option to refresh the endpoint
	snippet( 'footer' );
	exit;
}

if( ! isset($_SESSION['access_token']) ) {
	echo '<p>no access token found for '.$_SESSION['me'].'</p>';
	snippet( 'footer' );
	exit;
}

if( ! isset($_SESSION['scope']) || ! in_array( 'read', explode( ' ', $_SESSION['scope'] ) ) ) {
	echo '<p>scope not found or is not <em>read</em> (scope is <strong>'.$_SESSION['scope'].'</strong>) for '.$_SESSION['me'].'</p>';
	snippet( 'footer' );
	exit;
}


if( isset($_GET['channel']) ) {

	// content of channel

	if( isset($_GET['action']) ) {
		
		if( $_GET['action'] == 'add' ) {
			// add new feed

			echo '<p><a class="button" href="'.url('microsub?channel='.$_GET['channel'], false).'">cancel</a></p>';

			if( isset($_POST['url']) ) {

				$search_url = $_POST['url'];

				// TODO: search for feed urls: https://indieweb.org/Microsub-spec#Search
				//$sources = $microsub->api_get( 'search', array( 'query' => $search_url ) );

				// TODO: add feed validation (currently, everything gets added, even if its not a valid feed)

				// follow feed - https://indieweb.org/Microsub-spec#Following
				$response = $microsub->api_post( 'follow', [
					'channel' => $_GET['channel'],
					'url' => $search_url
				] );

				echo '<p><strong>server response:</strong></p>';
				echo '<pre>';
				var_dump($response);
				echo '</pre>';

				echo '<a href="'.url('microsub?channel='.$_GET['channel'].'&refresh=true', false).'">&raquo; back to the channel overview</a>';

			} else {
				?>
				<form method="POST" action="<?= url('microsub?channel='.$_GET['channel'].'&action=add', false ) ?>">
					<p><strong>currently, the url does not get validated. only add valid json, rss or atom feeds.</strong></p>
					<label style="display: inline-block;">Feed URL (json, rss, atom, ...): <input type="url" name="url" placeholder="https://www.example.com/feed/rss" style="min-width:400px;"></label>
					<button>add feed</button>
				</form>
				<?php
			}
		} elseif( $_GET['action'] == 'unfollow' ) {

			$feed = urldecode($_GET['feed']);

			// TODO: validate that this feed exists in the channel

			if( isset($_GET['confirmation']) && $_GET['confirmation'] == 'true' ) {

				$response = $microsub->api_post( 'unfollow', [
					'channel' => $_GET['channel'],
					'url' => $feed
				] );

				echo '<p><strong>server response:</strong></p>';
				echo '<pre>';
				var_dump($response);
				echo '</pre>';

				echo '<a href="'.url('microsub?channel='.$_GET['channel'].'&refresh=true', false).'">&raquo; back to the channel overview</a>';

			} else {

				echo '<p>do you really want to unfollow <strong>'.$feed.'</strong>?</p>';
				echo '<p><a class="button" href="'.url('microsub/?channel='.$_GET['channel'].'&action=unfollow&confirmation=true&feed='.$_GET['feed'], false).'">yes, unfollow<a> <a class="button" href="'.url('microsub/?channel='.$_GET['channel'], false).'">no, abort</a></p>';

			}

		} else {

			echo '<p><strong>ERROR:</strong> unknown action: <em>'.$_GET['action'].'</em></p>';

		}

	} else {
		// list posts

		$items_args = array(
			'channel' => $_GET['channel'],
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
					echo '<li><a class="button" href="'.url('microsub/?channel='.$_GET['channel'].'&before='.$paging->before, false).'">&laquo; previous page</a></li>';
				}
				if( ! empty($paging->after) ) {
					echo '<li><a class="button" href="'.url('microsub/?channel='.$_GET['channel'].'&after='.$paging->after, false).'">next page &raquo;</a></li>';
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
					<?php
					if( isset($_GET['debug']) ) {
						?>
						<pre><?php var_dump($item); ?></pre>
						<?php
					}

					?>
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
					echo '<li><a class="button" href="'.url('microsub/?channel='.$_GET['channel'].'&before='.$paging->before, false).'">&laquo; previous page</a></li>';
				}
				if( ! empty($paging->after) ) {
					echo '<li><a class="button" href="'.url('microsub/?channel='.$_GET['channel'].'&after='.$paging->after, false).'">next page &raquo;</a></li>';
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
					echo '<a class="button" href="'.url('microsub/?channel='.$_GET['channel'], false).'">go to first page</a>';
				}

			}
			if( isset($_GET['debug']) ) {
				echo '<strong>POSTS ERROR:</strong>';
				echo '<code><pre>';
				var_dump($items);
				echo '</pre></code>';
			}
		}

	}
	
}


snippet( 'footer' );
