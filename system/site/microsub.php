<?php

if( ! $sekretaer ) exit;

snippet( 'header' );


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


// NOTE: see https://aperture.p3k.io/docs for aperture docs

// needs 'read' scope (?)
$channels = np_ms_api_get( 'channels' );
if( $channels && isset($channels->channels) && count($channels->channels) ) {
	// overview: list channels
	?>
	<h2>Channels</h3>
	<ul>
	<?php
	foreach( $channels->channels as $channel ) {

		if( isset($_GET['channel']) && $channel->uid == $_GET['channel'] ) {
			?>
			<li><?= $channel->name ?></li>
			<?php
		} else {
			?>
			<li><a href="<?= url('microsub') ?>?channel=<?= $channel->uid; ?>"><?= $channel->name.' ['.$channel->unread.' unread]'; ?></a></li>
			<?php
		}
	}
	?>
	</ul>
	<?php
} else {
	echo '<strong>CHANNELS ERROR:</strong>';
	echo '<code><pre>';
	var_dump($channels);
	echo '</pre></code>';
}


if( isset($_GET['channel']) ) {

	// content of channel

	// needs 'follow' scope
	$feeds = np_ms_api_get( 'follow', array( 'channel' => $_GET['channel'] ) );
	if( $feeds && isset($feeds->items) && count($feeds->items) ) {
		?>
		<hr>
		<h2>Feeds</h2>
		<ul>
		<?php
		foreach( $feeds->items as $item ) {
			?>
			<li>
				<?php
				if( ! empty($item->name) ) echo $item->name.' (';
				echo $item->url;
				if( ! empty($item->name) ) echo ')';
				?>
				[<a href="<?= url('microsub/?channel='.$_GET['channel'].'&action=unfollow&feed='.urlencode($item->url), false) ?>">unfollow</a>]
				<?php
				// TODO: add 'mute'/'unmute' button
				?>
			</li>
			<?php
		}
		?>
			<li>
				<a href="<?= url('microsub/?channel='.$_GET['channel'].'&action=add', false ) ?>">add a new feed to this channel</a>
			</li>
		</ul>
		<?php
	} else {
		echo '<strong>FEEDS ERROR:</strong>';
		echo '<code><pre>';
		var_dump($feeds);
		echo '</pre></code>';
	}

	if( isset($_GET['action']) ) {
		
		echo '<hr>';

		if( $_GET['action'] == 'add' ) {
			// add new feed

			echo '<p><a href="'.url('microsub?channel='.$_GET['channel'], false).'">&laquo; abort adding a new feed</a></p>';

			if( isset($_POST['url']) ) {

				$search_url = $_POST['url'];

				// TODO: search for feed urls: https://indieweb.org/Microsub-spec#Search
				//$sources = np_ms_api_get( 'search', array( 'query' => $search_url ) );

				// TODO: add feed validation (currently, everything gets added, even if its not a valid feed)

				// follow feed - https://indieweb.org/Microsub-spec#Following
				$response = np_ms_api_post( 'follow', [
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

				$response = np_ms_api_post( 'unfollow', [
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
				echo '<p>[<a href="'.url('microsub/?channel='.$_GET['channel'].'&action=unfollow&confirmation=true&feed='.$_GET['feed'], false).'">yes<a>] [<a href="'.url('microsub/?channel='.$_GET['channel'], false).'">no</a>]</p>';

			}

		} else {

			echo '<p><strong>ERROR:</strong> unknown action: <em>'.$_GET['action'].'</em></p>';

		}

	} else {
		// list posts

		$items_args = array( 'channel' => $_GET['channel'] );
		$items = np_ms_api_get( 'timeline', $items_args );
		if( $items && isset($items->items) && count($items->items) ) {
			?>
			<hr>
			<h2>Posts</h2>
			<ul class="posts">
			<?php
			foreach( $items->items as $item ) {

				$date = new DateTimeImmutable($item->published);

				$datetime_format = $sekretaer->config->get('datetime_format');
				$datetime = $date->format( $datetime_format );

				?>
				<li>
					<?php
					if( isset($_GET['debug']) ) {
						?>
						<pre><?php var_dump($item); ?></pre>
						<?php
					}
					?>
					<?php if( ! empty( $item->photo[0] ) ) echo '<img src="'.$item->photo[0].'"><br>'; ?>
					<h3><?= $item->name ?></h3>
					<p>
						<?php if( ! empty($item->content->html) ) echo $item->content->html; ?>
					</p>
					<p><small><a href="<?= $item->author->url ?>" target="_blank" rel="noopener"><?= $item->author->name ?></a>, <?= $datetime ?></small></p>
					<p><a class="button" href="<?= $item->url ?>" target="_blank" rel="noopener">read full post <sup>🡥</sup></a> <a class="button" href="<?= url('micropub') ?>?content=<?= urlencode($item->url) ?>">share this post</a></p>
				</li>
				<?php
			}
			?>
			</ul>
			<?php
		} else {
			echo '<strong>POSTS ERROR:</strong>';
			echo '<code><pre>';
			var_dump($items);
			echo '</pre></code>';
		}

	}
	
}


function np_ms_api_get( $action, $args = array() ) {

	$api_url = $_SESSION['microsub_endpoint'];

	$authorization = 'Authorization: Bearer '.$_SESSION['access_token'];

	$url = $api_url.'?action='.$action;

	if( count($args) ) {
		foreach( $args as $key => $value ) {
			$url .= '&'.$key.'='.$value; // TODO: sanitize
		}
	}

	$cache = new Cache( 'microsub', $url, false, 60*3 ); // cache for 3 minutes

	$data = $cache->get_data();
	if( $data ) return json_decode($data);


	if( isset($_REQUEST['debug']) ) {
		echo '<p><strong>API request to:</strong> '.$url.'</p>';
		echo '<pre><code>';
	}

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result);

	if( isset($_REQUEST['debug']) ) {
		echo '</code></pre>';
	}

	$cache->add_data( json_encode($json) );

	return $json;
}


function np_ms_api_post( $action, $args = array() ) {

	$api_url = $_SESSION['microsub_endpoint'];

	$authorization = 'Authorization: Bearer '.$_SESSION['access_token'];

	$url = $api_url.'?action='.$action;

	$post_args = array();
	if( count($args) ) {
		foreach( $args as $key => $value ) {
			$post_args[] = $key.'='.$value; // TODO: sanitize
		}
	}
	$post_args = implode('&', $post_args);

	if( isset($_REQUEST['debug']) ) {
		echo '<p><strong>API request to:</strong> '.$url.'</p>';
		echo '<pre><code>';
	}

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array($authorization) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_args );
	$server_output = curl_exec($ch);
	curl_close($ch);

	if( isset($_REQUEST['debug']) ) {
		echo '</code></pre>';
	}

	return $server_output;
}


snippet( 'footer' );
