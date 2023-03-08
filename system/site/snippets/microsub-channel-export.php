<?php

// Version: alpha.7

if( ! $sekretaer ) exit;

$active_channel = $args['active_channel'];

?>
<h2>Export Feed List</h2>

<form method="GET" action="<?= url('microsub/'.$active_channel.'/export/') ?>">
	<select name="type" required>
		<option value="">select type …</option>
		<option value="json">json</option>
		<option value="txt">txt</option>
		<option value="opml">opml (experimental)</option>
	</select>
	<button>Export</button>
</form>
