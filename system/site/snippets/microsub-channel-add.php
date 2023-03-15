<?php

// Version: alpha.8

if( ! $core ) exit;

$active_channel = $args['active_channel'];
$microsub = $args['microsub'];

// TODO: move handling elsewhere?

echo '<p><a class="button" href="'.url('microsub/'.$active_channel.'/feeds/', false).'">cancel</a></p>';

if( isset($_POST['url']) ) {

	
	if( empty($_REQUEST['selected_feeds']) ) {

		$search_url = $_POST['url'];

		$feeds = $microsub->find_feeds( $search_url );

		if( ! count($feeds) ) {

			echo '<p>no feed found at '.$search_url.'</p>';

		} else {

			?>
			<form class="add-feed-select-form" method="POST" action="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">

				<?php
				if( count($feeds) > 1 ) {
					echo '<p>Found multiple feeds, please selected the feeds you want to follow:</p>';
				}
				?>
				<ul>
				<?php
				foreach( $feeds as $feed ) {

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
								<input type="checkbox" name="selected_feeds[]" value="<?= $url ?>" <?php if( count($feeds) == 1 ) echo ' checked'; ?>>
								<?php
								if( $image ) echo '<img src="'.$image.'">';  // TODO: cache locally, so we don't leak the client IP
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
				<button>follow the selected feed<?php if( count($feeds) > 1 ) echo 's'; ?></button>
				<input type="hidden" name="url" value="<?= $search_url ?>">
			</form>
			<?php
		}

	} else {

		$selected_feeds = $_REQUEST['selected_feeds'];

		if( ! is_array($selected_feeds) ) $selected_feeds = array($selected_feeds);

		foreach( $selected_feeds as $selected_feed ) {

			$response = $microsub->subscribe_feed( $selected_feed, $active_channel );

			if( $response == 'success' ) {
				echo '<p><strong>sucessfully subscribed to '.$selected_feed.'</strong></p>';
			} else {
				echo '<p><strong>Error:</strong> could not subscribe to <strong>'.$selected_feed.'</strong>:<br>'.$response.'</p>';
			}

		}

		echo '<a href="'.url('microsub/'.$active_channel.'/feeds/add/?refresh=true', false).'">&raquo; back to the feed management</a>';

	}


} else {
	?>
	<form method="POST" action="<?= url('microsub/'.$active_channel.'/add/', false ) ?>">
		<label style="display: inline-block;">website address or feed URL (json, rss, atom):<br><input type="text" name="url" placeholder="example.com" autofocus style="min-width:400px;"></label>
		<button>add feed</button>
		<p>you don't need to add the feed url directly, you can also add the website url - we try to find the correct feed url automatically.</p>
	</form>
	<?php
}
