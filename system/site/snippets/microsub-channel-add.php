<?php

// Version: alpha.7

if( ! $sekretaer ) exit;

$active_channel = $args['active_channel'];
$microsub = $args['microsub'];

// TODO: move handling elsewhere?

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

				$result = $search_url; // try adding the query directly as a feed

			} elseif( count($results) == 1 ) {

				$result = $results[0]->url; // only 1 result, use this

			} else {
				// multiple results, show to user
				?>
				<form class="add-feed-select-form" method="POST" action="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">
					<p>Found multiple feeds, please choose one:</p>
					<ul>
					<?php
					foreach( $results as $feed ) {

						$url = $feed->url;

						$title = $url;
						if( ! empty($feed->name) ) $title = $feed->name;

						$description = false;
						if( ! empty($feed->description) ) $description = $feed->description;

						$image = false;
						if( ! empty($feed->photo) ) $image = $feed->photo;

						?>
						<li>
							<label>
								<span>
									<input type="radio" name="selected_url" value="<?= $url ?>" required>
									<?php
									if( $image ) echo '<img src="'.$image.'">';
									echo '<strong>'.$title.'</strong>';
									if( $description ) echo '<br>'.$description;
									if( $title != $url ) echo '<br><small>'.$url.'</small>';
									?>
								</span>
							</label>
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

	echo '<a href="'.url('microsub/'.$active_channel.'/feeds/add/?refresh=true', false).'">&raquo; back to the feed management</a>';

} else {
	?>
	<form method="POST" action="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">
		<label style="display: inline-block;">website address or feed URL (json, rss, atom):<br><input type="text" name="url" placeholder="example.com" autofocus style="min-width:400px;"></label>
		<button>add feed</button>
		<p>you don't need to add the feed url directly, you can also add the website url - we try to find the correct feed url automatically.</p>
	</form>
	<?php
}
