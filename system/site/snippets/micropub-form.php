<?php

// Version: alpha.6

if( ! $sekretaer ) exit;;

$url = $args['url'];
$content = $args['content'];
$tags = $args['tags'];
$me = $args['me'];
$name = $args['name'];

?>
<form id="micropub-form" action="<?= $url ?>" method="post" enctype="multipart/form-data">

	<p><label><strong>Status</strong>:<br><select name="status" required><option value="draft">Draft</option><option selected value="published">Publish</option></select></label></p>

	<p><label><strong>Title</strong> <small>(optional)</small>:<br><input name="title" style="width: 100%;" placeholder="No Title"></label></p>

	<p><label><strong>Slug</strong> <small>(optional)</small>:<br><input name="slug" style="width: 100%;" placeholder="no-title"></label></p>

	<p><label><strong>Content</strong> <small>(required)</small>:<br><textarea name="content" style="width: 100%; height: 300px;" placeholder="Hello World!" autofocus required><?= $content ?></textarea></label></p>

	<p><label><strong>Tags</strong> <small>(optional, comma separated)</small>:<br><input name="tags" style="width: 100%;" placeholder="tag1, tag2"></label></p>
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

	<p><label><strong>Image</strong> <small>(optional, .jpg or .png)</small>:<br><input type="file" name="image" accept="image/jpeg,image/png" style="width: 100%;"><div class="image-preview"></div></label></p>
	<?php /* TODO: limit max file size? depends on server receiving the image */ ?>

	<input type="hidden" name="action" value="post">

	<p>&nbsp;</p>

	<p><button>post to <?= $name ?></button></p>
	<p><small>(this will be posted to <a href="<?= $me ?>" target="_blank" rel="noopener"><?= $me ?></a>)</small></p>

</form>
