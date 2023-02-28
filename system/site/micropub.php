<?php

if( ! $sekretaer ) exit;

snippet( 'header' );

?>
<h2>Write a new Post</h2>
<?php


$micropub = new Micropub();


if( isset($_POST['action']) && $_POST['action'] == 'post' ) {

	$files = false;
	if( isset($_FILES) ) $files = $_FILES;
	list( $success, $message ) = $micropub->post( $_POST, $files );

	if( $success ) {
		echo '<h3>Success!</h3>';
		echo '<p>'.$message.'</p>';
		$button_text = 'write another post';
	} else {
		echo '<h3>Error</h3>';
		echo '<p>'.$message.'</p>';
		$button_text = 'write a new post';
	}

	echo '<p><a class="button" href="'.url('micropub').'">'.$button_text.'</a></p>';

	snippet('footer');
	exit;

}


// form

$tags = $micropub->get_tags();

$content = '';
if( isset($_GET['content']) ) $content = urldecode($_GET['content']);

$me = $micropub->get_me();


// TODO: move form to snippet
?>
<form action="<?= url('micropub') ?>" method="post" enctype="multipart/form-data">

	<p><label><strong>Status</strong>:<br><select name="status" required><option value="draft">Draft</option><option selected value="published">Publish</option></select></label></p>

	<p><label><strong>Title</strong> <small>(optional)</small>:<br><input name="title" style="width: 100%;" placeholder="No Title"></label></p>

	<p><label><strong>Slug</strong> <small>(optional)</small>:<br><input name="slug" style="width: 100%;" placeholder="no-title"></label></p>

	<?php
	// TODO: move js to theme js file
	?>
	<script type="text/javascript">
		(function(){
			var title = document.querySelector('input[name="title"]');
			if( ! title ) return;

			var slug = document.querySelector('input[name="slug"]');
			if( ! slug ) return;

			var sanitizeSlug = function( slug ){
				// slug sanitize function found here: https://mhagemann.medium.com/the-ultimate-way-to-slugify-a-url-string-in-javascript-b8e4a0d849e1
				const a = '\u00e0\u00e1\u00e2\u00e4\u00e6\u00e3\u00e5\u0101\u0103\u0105\u00e7\u0107\u010d\u0111\u010f\u00e8\u00e9\u00ea\u00eb\u0113\u0117\u0119\u011b\u011f\u01f5\u1e27\u00ee\u00ef\u00ed\u012b\u012f\u00ec\u0131\u0130\u0142\u1e3f\u00f1\u0144\u01f9\u0148\u00f4\u00f6\u00f2\u00f3\u0153\u00f8\u014d\u00f5\u0151\u1e55\u0155\u0159\u00df\u015b\u0161\u015f\u0219\u0165\u021b\u00fb\u00fc\u00f9\u00fa\u016b\u01d8\u016f\u0171\u0173\u1e83\u1e8d\u00ff\u00fd\u017e\u017a\u017c\u00b7/_,:;'
				const b = 'aaaaaaaaaacccddeeeeeeeegghiiiiiiiilmnnnnoooooooooprrsssssttuuuuuuuuuwxyyzzz------'
				const p = new RegExp(a.split('').join('|'), 'g')
				var sanitized = slug.toLowerCase()
					.replace(/\s+/g, '-') // Replace spaces with -
					.replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
					.replace(/&/g, '-and-') // Replace & with 'and'
					.replace(/[^\w\-]+/g, '') // Remove all non-word characters
					.replace(/\-\-+/g, '-') // Replace multiple - with single -
					.replace(/^-+/, '') // Trim - from start of text
					.replace(/-+$/, '') ;// Trim - from end of text

				return sanitized;
			};

			var updateSlug = function( el ){

				var val = el.value;

				if( ! val ) return;

				var sanitized = sanitizeSlug( val );

				slug.value = sanitized;
			};

			var updateSlugThis = function(){
				updateSlug( this );
			};

			var timeout;
			var updateSlugWithDelay = function(){
				clearTimeout( timeout );
				var el = this;
				timeout = setTimeout( function(){
					updateSlug( el );
				}, 500 );
			};

			title.addEventListener( 'change', updateSlugThis );
			title.addEventListener( 'keyup', updateSlugThis );

			slug.addEventListener( 'change', updateSlugThis );
			slug.addEventListener( 'keyup', updateSlugWithDelay );

		})();
	</script>

	<p><label><strong>Content</strong> <small>(required)</small>:<br><textarea name="content" style="width: 100%; height: 300px;" placeholder="Hello World!" autofocus required><?= $content ?></textarea></label></p>

	<p><label><strong>Tags</strong> <small>(optional, comma separated)</small>:<br><input name="tags" style="width: 100%;" placeholder="tag1, tag2"></label></p>
	<?php if( count($tags) ) : ?>
		<ul class="tag-selector">
			<?php foreach( $tags as $tag ) {
			?>
			<li onclick="selectTag(this)"><?= $tag ?></li>
			<?php
			}
			?>
		</ul>
		<script type="text/javascript">
			function selectTag( el ){
				var tag = el.innerHTML;
				if( ! tag ) return;
				var tags = document.querySelector('input[name="tags"]');
				if( ! tags ) return;

				if( tags.value ) tag = ', '+tag;

				tags.value = tags.value + tag;
			}
		</script>
	<?php endif; ?>

	<p><label><strong>Image</strong> <small>(optional, .jpg or .png)</small>:<br><input type="file" name="image" accept="image/jpeg,image/png" style="width: 100%;"></label></p>
	<?php /* TODO: show preview image; TODO: limit max file size? depends on server receining the image */ ?>

	<input type="hidden" name="action" value="post">

	<p>&nbsp;<br>this will be posted to <a href="<?= $me ?>" target="_blank" rel="noopener"><?= $me ?></a></p>

	<p><button>post to <?= $micropub->get_name() ?></button></p>

</form>
<?php



snippet( 'footer' );
