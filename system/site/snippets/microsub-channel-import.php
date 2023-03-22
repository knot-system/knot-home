<?php

// Version: 0.1.0

if( ! $core ) exit;

$active_channel = $args['active_channel'];
$microsub = $args['microsub'];

?>
<h2>Import Feeds</h2>

<p>This importer is experimental. After the import, make sure that all the content was imported correctly, and add missing feeds manually.</p>

<?php
// TODO: move importing elsewhere?

$error = false;
$hide_form = false;

if( isset($_POST['selected_feeds']) ) {

	$import_feeds = $_POST['selected_feeds'];

	if( ! is_array($import_feeds) && ! empty($import_feeds) ) $import_feeds = array($import_feeds);

	if( is_array($import_feeds) && count($import_feeds) ) {

		foreach( $import_feeds as $import_feed ) {

			$response = $microsub->subscribe_feed( $import_feed, $active_channel );

			if( $response == 'success' ) {
				echo '<p><strong>sucessfully subscribed to '.$import_feed.'</strong></p>';
			} else {
				echo '<p><strong>Error:</strong> could not subscribe to <strong>'.$import_feed.'</strong>:<br>'.$response.'</p>';
			}

		}

		echo '<a href="'.url('microsub/'.$active_channel.'/feeds/add/?refresh=true', false).'">&raquo; back to the feed management</a>';
		
		$hide_form = true;

	} else {
		echo '<p><strong>no feeds imported</strong></p>';
	}


} elseif( isset($_POST['import_action']) ) {

	$file = $_FILES['feedlist'];

	if( empty($file['name']) || empty($file['tmp_name']) || empty($file['size']) || $file['size'] <= 0 || ! isset($file['error']) || $file['error'] != 0 ) {

		$error = 'upload';

	} else {

		$filename_exp = explode('.', $file['name']);
		$file_extension = strtolower(end($filename_exp));
		$file_contents = file_get_contents($file['tmp_name']);

		$urls = [];
		if( $file_extension == 'txt' ) {

			$file_contents = str_replace("\r", "\n", $file_contents);
			$urls = explode("\n", $file_contents);

		} elseif( $file_extension == 'json' ) {

			$json = json_decode($file_contents, true);

			if( is_array($json) ) {
				foreach( $json as $item ) {
					if( empty($item['url']) ) continue;
					$urls[] = $item['url'];
				}
			}


		} elseif( $file_extension == 'opml' ) {

			$xml = new SimpleXMLElement($file_contents);

			if( $xml->body->outline ) {
				foreach( $xml->body->outline as $feed ) {

					if( isset($feed->outline) ) {

						foreach( $feed->outline as $subfeed ) {

							if( ! isset($subfeed['xmlUrl']) ) continue;

							$url = $subfeed['xmlUrl']->__toString();
							$urls[] = $url;
						}

					} else {

						if( ! isset($feed['xmlUrl']) ) continue;

						$url = $feed['xmlUrl']->__toString();
						$urls[] = $url;
					}
				}
			}

		} else {
			// error
			$error = 'fileextension';
		}

	}

	if( ! $error ) {

		$hide_form = true;

		$urls = array_unique($urls);
		$urls = array_filter($urls); // remove empty entries
		$urls = array_map( 'trim', $urls );

		if( ! count($urls) ) {
			// error
			header('location: '.url('microsub/'.$active_channel.'/import/?error=nofeeds', false) );
			exit;	
		}

		?>
		<form method="POST" action="<?= url('microsub/'.$active_channel.'/import/') ?>">

			<p>select the feeds you want to import</p>
			<ul class="feeds-list">
			<?php
			foreach( $urls as $search_url ) {

				$feeds = $microsub->find_feeds( $search_url );

				if( ! count($feeds) ) {
					?>
					<li>
						<p><input type="checkbox" disabled> no feed found at <strong><?= $search_url ?></strong></p>
					</li>
					<?php
					continue;
				}

				?>
				<li>
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
						<label>
							<span>
								<input type="checkbox" name="selected_feeds[]" value="<?= $url ?>" checked>
								<?php
								if( $image ) echo '<img src="'.$image.'">';  // TODO: cache locally, so we don't leak the client IP
								echo '<strong>'.$title.'</strong>';
								if( $description ) echo '<br>'.$description;
								if( $title != $url ) echo '<br><small>'.$url.'</small>';
								if( $url != $search_url ) echo '<br><small>('.$search_url.')</small>';
								?>
							</span>
						</label>
						<?php
					}
					?>
				</li>
				<?php

			}
			?>
			</ul>

			<p><button>Import selected feeds</button></p>

		</form>
		<?php

	}

}



if( ! $hide_form ) {
	?>

	<form method="POST" action="<?= url('microsub/'.$active_channel.'/import/') ?>" enctype="multipart/form-data">
		<input type="hidden" name="import_action" value="true">
		<p><label><strong>.json</strong>, <strong>.opml</strong> or <strong>.txt</strong> (one feed per line)<br>
		<input type="file" name="feedlist" accept=".json,.opml,.txt" required></label></p>
		<p><button>Import</button></p>
	</form>

	<?php
	if( $error ) {
		
		echo '<p><strong>Error:</strong> ';

		if( $error == 'upload' ) {
			echo 'could not upload file';
		} elseif( $error == 'fileextension' ) {
			echo 'unknown file extension';
		} elseif( $error == 'nofeeds' ) {
			echo 'no feeds found';
		} else {
			echo 'something went wrong';
		}

		echo '</p>';
		// TODO: better error reporting
	}
	
}