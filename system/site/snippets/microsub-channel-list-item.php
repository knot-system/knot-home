<?php

// Version: 0.1.4

if( ! $core ) exit;

$item = $args['item'];
$active_channel = $args['active_channel'];


$date = new DateTimeImmutable($item->published);

$datetime_format = get_config('datetime_format');
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


$show_item_content = get_config('show_item_content');


$link_previews = false;

if( $show_item_content ) {

	$content = false;
	if( ! empty($item->content->html) ) {
		$content = $item->content->html;
	} elseif( ! empty($item->content->text) ) {
		$content = $item->content->text;
	}

	$content = str_replace(array("\r\n", "\r", "\n"), ' ', $content );

	$text = new Text( $content );
	$content = $text->cleanup()->get();

	$link_previews = $text->get_link_preview();

} else {

	$link_url = $item->url;

	global $core;

	$content = '<div class="link-preview-container"><ol class="link-preview-list">';
	
	$link = new Link( $link_url );
	$link_id = $link->id;

	$link_info = $link->get_preview();

	$classes = array( 'link-preview' );

	$max_age = get_config('link_preview_max_age');

	if( empty($link_info['last_refresh']) || time()-$link_info['last_refresh'] > $max_age ) {

		$classes[] = 'link-preview-needs-refresh';

		$nojs_refresh = get_config('link_preview_nojs_refresh');
		if( $nojs_refresh && ! isset($core->is_link_refreshing) ) {
			// NOTE: we refresh only on link for every request, because this can take a few seconds,
			// depending on the url and how fast the other server is.
			// by default, the link refresh also happens async via js, so all the links that don't get
			// refreshed with this request, should be done by the time this page refreshes again.
			// this is just a fallback, if js is not active, or doesn't get executed, or is removed by the theme
			$core->is_link_refreshing = true;
			$link_info = $link->get_info()->get_preview();
		}
		
	}

	$content .= '			<li>
		<a id="'.$link_id.'" class="'.implode(' ', $classes).'" name="'.$link->short_url.'" href="'.$link->url.'" target="_blank" rel="noopener" data-preview-hash="'.$link_info['preview_html_hash'].'">'.$link_info['preview_html'].'</span></a>
	</li>';

	$content .= '</ol></div>';

}



$source = false;
if( ! empty($item->_source) ) {
	$source = $item->_source;
}


?>
<li>
	<span class="item-content">
		<?php

		if( $show_item_content ) {

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

		}

		?>
		<p>
			<?= $content ?>
		</p>

	</span>

	<?php
	if( $link_previews ) {
		?>
		<div class="link-preview-container">
			<?= $link_previews ?>
		</div>
		<?php
	}
	?>
	
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