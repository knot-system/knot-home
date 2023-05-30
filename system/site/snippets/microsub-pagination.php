<?php

// Version: 0.1.4

if( ! $core ) exit;


$paging = $args['paging'];
$active_channel = $args['active_channel'];
$active_source = $args['active_source'];



$baseurl = 'microsub/'.$active_channel.'/';
if( $active_source ) $baseurl .= $active_source.'/';


$refresh_url = $baseurl;
if( ! empty($_GET['before']) ) {
	$refresh_url .= '?before='.$_GET['before'];
} elseif( ! empty($_GET['after']) ) {
	$refresh_url .= '?after='.$_GET['after'];
}

?>
<ul class="pagination">
	<?php
	if( ! empty($paging->before) ) {
		?>
		<li>
			<a class="button" href="<?= url($baseurl.'?before='.$paging->before, false) ?>">&laquo; previous page</a>
		</li>
		<?php
	}
	?>
	<li>
		<a class="button" href="<?= url($refresh_url, false) ?>">refresh</a>
	</li>
	<?php
	if( ! empty($paging->after) ) {
		?>
		<li>
			<a class="button" href="<?= url($baseurl.'?after='.$paging->after, false) ?>">next page &raquo;</a>
		</li>
		<?php
	}
	?>
</ul>
