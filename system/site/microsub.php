<?php

if( ! $sekretaer ) exit;


$active_channel = $sekretaer->route->get('channel'); // TODO
$action = $sekretaer->route->get('action'); // TODO


$microsub = new Microsub();


$channels = $microsub->get_channels();
if( ! array_key_exists( $active_channel, $channels ) ) $active_channel = false;


if( ! $active_channel ) {
	// NOTE: if no channel is selected, automatically show the first channel that is not 'notifications'
	$channels_cleaned = $channels;
	unset($channels_cleaned['notifications']);
	$active_channel = array_key_first($channels_cleaned);
}


$snippet = false; // TODO





if( $action == 'channels' ) {
	$active_channel = false; // don't select a channel while managing
}


if( $active_channel && $action == 'export' && ! empty($_GET['type']) ) {

	$type = $_GET['type'];

	$feeds = $microsub->get_feeds( $active_channel );

	$channel = $channels[$active_channel];
	$channel_name = $channel->name;
	$channel_name_sanitized = sanitize_string_for_url($channel_name);

	$filename = date('Y-m-d_H-i-s', time()).'_sekretaer_'.$channel_name_sanitized.'_feedlist';

	$content = false;
	if( $type == 'opml' ) {

		$date_time = new DateTime();
		$date_time = $date_time->format(DateTime::RFC822);

		// spec: http://opml.org/spec2.opml
		$content = [];
		$content[] = '<?xml version="1.0" encoding="ISO-8859-1"?>';
		$content[] = '<opml version="2.0">';
		$content[] = '<head>';
		$content[] = '<title>Sekretaer Feed List</title>';
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



$sidebar_content = '';


ob_start();
if( ! empty($channels) ) {
	// overview: list channels
	?>
	<p class="manage-link"><a href="<?= url('microsub/?action=channels', false) ?>" title="manage channels">manage</a></p>
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


$sidebar_content = ob_get_contents();
ob_end_clean();



snippet( 'header', array(
	'sidebar-content' => $sidebar_content
) );



if( $action == 'channels' ) {
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

			echo '<a href="'.url('microsub/?action=channels&refresh=true', false).'">&raquo; back to channel management</a>';

		} else {

			?>
			<a class="button" href="<?= url('microsub/?action=channels', false) ?>">cancel</a>

			<form method="POST" action="<?= url('microsub/?action=channels&new', false ) ?>" style="margin-top: 2em;">
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

			echo '<a href="'.url('microsub/?action=channels&refresh=true', false).'">&raquo; back to channel management</a>';

		} else {

			?>
			<a class="button" href="<?= url('microsub/?action=channels', false) ?>">cancel</a>

			<form method="POST" action="<?= url('microsub/?action=channels&rename='.$uid, false ) ?>" style="margin-top: 2em;">
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

			echo '<a href="'.url('microsub/?action=channels&refresh=true', false).'">&raquo; back to channel management</a>';

		} else {

			?>
			<a class="button" href="<?= url('microsub/?action=channels', false) ?>">cancel</a>

			<form method="POST" action="<?= url('microsub/?action=channels&delete='.$uid, false ) ?>" style="margin-top: 2em;">
				<label><input type="checkbox" name="aknowledge" value="true" required> yes, delete the channel <?= $selected_name ?> and all of its content</label>
				<button>delete channel '<?= $selected_name ?>'</button>
			</form>

			<?php

		}

	} else {

		?>
		<a class="button add-channel" href="<?= url('microsub/?action=channels&new', false) ?>">+ add a new channel</a>
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
				<a class="button button-small" href="<?= url('microsub/?action=channels&rename='.urlencode($channel->uid), false) ?>">rename</a>
				<a class="button button-small" href="<?= url('microsub/?action=channels&delete='.urlencode($channel->uid), false) ?>">delete</a>
			</li>
			<?php
		}

		echo '</ul>';
	}


	snippet( 'footer' );
	exit;

}


if( $active_channel ) {
	// content of channel

	if( $action == 'feeds' ) {
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
		<?php

	} elseif( $action == 'export' ) {

		?>
		<h2>Export Feed List</h2>

		<form method="GET" action="<?= url('microsub') ?>">
			<input type="hidden" name="channel" value="<?= $active_channel ?>">
			<input type="hidden" name="action" value="export">
			<select name="type" required>
				<option value="">select type …</option>
				<option value="json">json</option>
				<option value="txt">txt</option>
				<option value="opml">opml (experimental)</option>
			</select>
			<button>Export</button>
		</form>

		<?php

	} elseif( $action == 'add' ) {
		// add new feed

		echo '<p><a class="button" href="'.url('microsub/'.$active_channel.'/feeds/', false).'">cancel</a></p>';

		if( isset($_POST['url']) ) {

			$search_url = $_POST['url'];
			
			$result = false;

			if( ! empty($_REQUEST['selected_url']) ) {

				$result = $_REQUEST['selected_url'];

			} else {

				$search_result = $microsub->api_post( 'search', [ 'query' => $search_url ] );

				if( $search_result['status_code'] == 200 ) {

					$json = json_decode($search_result['body']);

					$results = [];
					if( ! empty($json->results) ) {
						$results = $json->results;
					}

					if( count($results) < 1 ) {

						$result = $seacrh_url; // try adding the query directly as a feed

					} elseif( count($results) == 1 ) {

						$result = $results[0]->url; // only 1 result, use this

					} else {
						// multiple results, show to user
						?>
						<form method="POST" action="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">
							<p>Found multiple feeds, please choose one:</p>
							<ul>
							<?php
							foreach( $results as $feed ) {

								$url = $feed->url;

								// TODO: check name, description, ..

								?>
								<li>
									<label><input type="radio" name="selected_url" value="<?= $url ?>" required> <?= $url ?></label>
								</li>
								<?php
							}
							?>
							</ul>
							<button>follow the selected feed</button>
							<input type="hidden" name="url" value="<?= $search_url ?>">
						</form>
						<?php

						snippet( 'footer' );

						exit;
					}

				} else {
					// something went wrong
					// TODO: better error display
					global $sekretaer;
					$sekretaer->debug( 'something went wrong while searching for feeds, the site return an unexpected status code', $search_result['status_code'], $search_url );

					snippet( 'footer' );

					exit;
				}

			}

			if( $result ) {

				// TODO: add feed validation (currently, everything gets added, even if its not a valid feed)

				// follow feed - https://indieweb.org/Microsub-spec#Following
				$response = $microsub->api_post( 'follow', [
					'channel' => $active_channel,
					'url' => $result
				] );

				echo '<p><strong>server response:</strong></p>';
				echo '<pre>';
				var_dump($response);
				echo '</pre>';

			} else {

				global $sekretaer;
				$sekretaer->debug( 'something went wrong while searching for feeds, no feed found', $search_url );

				snippet( 'footer' );

				exit;

			}

			echo '<a href="'.url('microsub/'.$active_channel.'/feeds/?refresh=true', false).'">&raquo; back to the channel overview</a>';

		} else {
			?>
			<form method="POST" action="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">
				<label style="display: inline-block;">website address or feed URL (json, rss, atom):<br><input type="text" name="url" placeholder="example.com" autofocus style="min-width:400px;"></label>
				<button>add feed</button>
				<p>you don't need to add the feed url directly, you can also add the website url - we try to find the correct feed url automatically.</p>
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

			echo '<a href="'.url('microsub/'.$active_channel.'/?refresh=true', false).'">&raquo; back to the channel overview</a>';

		} else {

			echo '<p>do you really want to unfollow <strong>'.$feed.'</strong>?</p>';
			echo '<p><a class="button" href="'.url('microsub/'.$active_channel.'/unfollow?confirmation=true&feed='.$_GET['feed'], false).'">yes, unfollow<a> <a class="button" href="'.url('microsub/'.$active_channel.'/feeds/', false).'">no, abort</a></p>';

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
					echo '<li><a class="button" href="'.url('microsub/'.$active_channel.'/?before='.$paging->before, false).'">&laquo; previous page</a></li>';
				}
				if( ! empty($paging->after) ) {
					echo '<li><a class="button" href="'.url('microsub/'.$active_channel.'/?after='.$paging->after, false).'">next page &raquo;</a></li>';
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
					echo '<li><a class="button" href="'.url('microsub/'.$active_channel.'/?before='.$paging->before, false).'">&laquo; previous page</a></li>';
				}
				if( ! empty($paging->after) ) {
					echo '<li><a class="button" href="'.url('microsub/'.$active_channel.'/?after='.$paging->after, false).'">next page &raquo;</a></li>';
				}
				echo '</ul>';

			}

			?>
			</ul>
			<?php
		} else {
			echo '<p>- no posts found -</p>';
			echo '<p><a class="button" href="'.url('microsub/'.$active_channel.'/?refresh', false).'">force refresh</a></p>';
			if( ! empty($items->paging) ) {

				$paging = $items->paging;
				if( ! empty($items_args['before']) || ! empty($items_args['after']) ) {
					echo '<a class="button" href="'.url('microsub/'.$active_channel.'/', false).'">go to first page</a>';
				}

			}
		}

	}
	
}


snippet( 'footer' );
