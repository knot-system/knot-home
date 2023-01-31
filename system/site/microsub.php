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


if( isset($_GET['channel']) ) {
	// content of channel

	echo '<p><a href="'.url('microsub').'">&laquo; All Channels</a></p>';


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
				<a href="<?= $item->url ?>" target="_blank" rel="noopener"><?= $item->url ?></a>
			</li>
			<?php
		}
		?>
		</ul>
		<?php
	} else {
		echo '<strong>FEEDS ERROR:</strong>';
		echo '<code><pre>';
		var_dump($feeds);
		echo '</pre></code>';
	}


	// needs 'read' scope (?)
	$items = np_ms_api_get( 'timeline', array( 'channel' => $_GET['channel'] ) );
	if( $items && isset($items->items) && count($items->items) ) {
		?>
		<hr>
		<h2>Posts</h2>
		<ul class="posts">
		<?php
		foreach( $items->items as $item ) {
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
					<?= $item->content->html ?>
				</p>
				<p><small><a href="<?= $item->author->url ?>" target="_blank" rel="noopener"><?= $item->author->name ?></a> @ <?= $item->published ?></small></p>
				<p>[<a href="<?= $item->url ?>" target="_blank" rel="noopener">read full post</a>] [<a href="<?= url('micropub') ?>?content=<?= urlencode($item->url) ?>">share this post</a>]</p>
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


} else {
	// overview: list channels

	// needs 'read' scope (?)
	$channels = np_ms_api_get( 'channels' );
	if( $channels && isset($channels->channels) && count($channels->channels) ) {
		?>
		<h2>Channels</h3>
		<ul>
		<?php
		foreach( $channels->channels as $channel ) {
			?>
			<li><a href="<?= url('microsub') ?>?channel=<?= $channel->uid; ?>"><?= $channel->name.' ('.$channel->uid.') ['.$channel->unread.' unread]'; ?></a></li>
			<?php
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
}


function np_ms_api_get( $action, $args = array() ) {

	$api_url = $_SESSION['microsub_endpoint'];

	$authorization = 'Authorization: Bearer '.$_SESSION['access_token'];

	$url = $api_url.'?action='.$action;

	$cache = new Cache( 'microsub', $url, false, 60*3 ); // cache for 3 minutes

	$data = $cache->get_data();
	if( $data ) return json_decode($data);

	if( count($args) ) {
		foreach( $args as $key => $value ) {
			$url .= '&'.$key.'='.$value; // TODO: sanitize
		}
	}

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


snippet( 'footer' );
