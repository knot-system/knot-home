<?php

// Version: 0.1.4

if( ! $core ) exit;

$item = $args['item'];
$active_channel = $args['active_channel'];


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

$content = false;
if( ! empty($item->content->html) ) {
	$content = $item->content->html;
} elseif( ! empty($item->content->text) ) {
	$content = $item->content->text;
}

$content = str_replace(array("\r\n", "\r", "\n"), ' ', $content );

$text = new Text( $content );
$content = $text->cleanup()->get();

$link_preview = $text->get_link_preview();

if( $link_preview ) {
	$link_preview = '<div class="link-preview-container">'.$link_preview.'</div>';
}


$source = false;
if( ! empty($item->_source) ) {
	$source = $item->_source;
}


?>
<li>
	<span class="item-content">
		<?php

		if( $source && ! empty($source->_id) && ! empty($source->name) ) {

			$feed_title = $source->name;
			$feed_link = url('microsub/'.$active_channel.'/'.$source->_id.'/#active-feed', false);

			?>
			<span class="item-feed-title"><a href="<?= $feed_link ?>"><?= $feed_title ?></a></span>
			<?php
		}

		if( ! empty($item->category) ) {
			$categories = $item->category;
			$categories = array_map(function($c){return '#'.$c;}, $categories);
			if( is_array($categories) ) $categories = implode(' ', $categories);
			echo ' '.$categories;
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

	<?= $link_preview ?>
	
	<p class="item-meta">
		<small>
			<?php

			if( $author_name ) {
				echo 'by ';
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

	<p class="item-actions">
		<a class="button post-read-full" href="<?= $item->url ?>" target="_blank" rel="noopener">read full post</a> <a class="button post-share" href="<?= url('micropub') ?>?content=<?= urlencode($item->url) ?>">share this post</a>
	</p>

	<hr>

</li>