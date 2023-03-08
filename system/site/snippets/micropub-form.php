<?php

// Version: alpha.6

if( ! $sekretaer ) exit;

$url = $args['url'];
$content = $args['content'];
$tags = $args['tags'];
$me = $args['me'];
$name = $args['name'];

?>
<form id="micropub-form" action="<?= $url ?>" method="post" enctype="multipart/form-data">
	
	<input type="hidden" name="action" value="post">

	<ul>

		<li class="title-wrapper">
			<label>
				<strong>Title</strong><br>
				<input name="title" style="width: 100%;" placeholder="No Title">
			</label>
		</li>

		<li class="slug-wrapper">
			<label>
				<strong>Slug</strong><br>
				<input name="slug" style="width: 100%;" placeholder="no-title">
			</label>
		</li>

		<li class="content-wrapper">
			<label>
				<strong>Content</strong><br>
				<textarea name="content" style="width: 100%; height: 300px;" placeholder="Hello World!" autofocus><?= $content ?></textarea>
			</label>
		</li>

		<li class="tags-wrapper">
			<label>
				<strong>Tags</strong> <small>(comma separated)</small><br><input name="tags" style="width: 100%;" placeholder="tag1, tag2">
			</label>
			<?php if( count($tags) ) : ?>
				<ul class="tag-selector" style="display: none;">
					<?php foreach( $tags as $tag ) {
					?>
					<li><?= $tag ?></li>
					<?php
					}
					?>
				</ul>
			<?php endif; ?>
		</li>

		<li class="image-wrapper">
			<label>
				<strong>Image</strong> <small>(.jpg or .png)</small><br>
				<div class="image-preview"></div>
				<input type="file" name="image" accept="image/jpeg,image/png" style="width: 100%;">
			</label>
		</li>

		<li class="status-wrapper">
			<label>
				<strong>Status</strong><br>
				<select name="status" required><option value="draft">Draft</option><option selected value="published">Publish</option></select>
			</label>
		</li>

		<li class="button-wrapper">
			<strong>&nbsp;</strong><br>
			<button class="post-button">post to <?= $name ?></button><br>
		</li>

	</ul>

	<p><small>(this will be posted to <a href="<?= $me ?>" target="_blank" rel="noopener"><?= $me ?></a>)</small></p>

</form>
