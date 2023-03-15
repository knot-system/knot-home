<?php

// Version: alpha.8

if( ! $core ) exit;


$active_channel = $args['active_channel'];
$microsub = $args['microsub'];


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

		$datetime_format = $core->config->get('datetime_format');
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