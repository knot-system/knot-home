<?php

// Version: 0.1.2

if( ! $core ) exit;


$paging = $args['paging'];
$active_channel = $args['active_channel'];
$active_source = $args['active_source'];



$baseurl = 'microsub/'.$active_channel.'/';
if( $active_source ) $baseurl .= $active_source.'/';

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
	if( ! empty($paging->after) ) {
		?>
		<li>
			<a class="button" href="<?= url($baseurl.'?after='.$paging->after, false) ?>">next page &raquo;</a>
		</li>
		<?php
	}
	?>
</ul>
