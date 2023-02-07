<?php

if( ! $sekretaer ) exit;

snippet( 'header' );

?>
<h2>Write a new Post</h2>
<?php

// TODO: this is copied from the quick prototype. we need to rewrite this.

if( ! isset($_SESSION['micropub_endpoint']) ) {
	echo '<p>no micropub endpoint found for '.$_SESSION['me'].'</p>';
	// TODO: option to refresh the endpoint
	snippet( 'footer' );
	exit;
}

if( ! isset($_SESSION['access_token']) ) {
	echo '<p>no access token found for '.$_SESSION['me'].'</p>';
	snippet( 'footer' );
	exit;
}

if( ! isset($_SESSION['scope']) || ! in_array( 'create', explode( ' ', $_SESSION['scope']) ) ) {
	echo '<p>scope not found or is not <em>create</em> (scope is <strong>'.$_SESSION['scope'].'</strong>) for '.$_SESSION['me'].'</p>';
	snippet( 'footer' );
	exit;
}



$api_url = $_SESSION['micropub_endpoint'];
$authorization = 'Authorization: Bearer '.$_SESSION['access_token'];


if( isset($_POST['action']) && $_POST['action'] == 'post' ) {

	$data = array(
		'h' => 'entry',
		'name' => $_POST['title'],
		'content' => $_POST['content'],
		'post-status' => $_POST['status']
	);

	if( isset($_POST['slug']) && $_POST['slug'] != '' ) $data['slug'] = $_POST['slug'];

	if( isset($_FILES) && $_FILES && isset($_FILES['image']) && isset($_FILES['image']['name']) && $_FILES['image']['name'] ) {
		// TODO: more error handling; check if $_FILES['image']['error'] == 0 and $_FILES['image']['size'] is > 0 and if tmp_name exists on the disk and so on ...
		$data['photo'] = curl_file_create( $_FILES['image']['tmp_name'], $_FILES['image']['type'], $_FILES['image']['name'] );
	}

	if( trim($_POST['tags']) ) {
		$tags = explode( ',', $_POST['tags'] );
		$tags = array_map( 'trim', $tags );

		$tags = array_unique( $tags );
		$tags = array_filter( $tags ); // remove empty elements

		if( count($tags) ) $data['category'] = implode( ',', $tags );
	}

	$url = $api_url;

	$ch = curl_init( $url );

	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $authorization) );

	curl_setopt( $ch, CURLOPT_HEADER, true );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$result = curl_exec( $ch );
	$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

	$new_post_url = false;
	$curl_info = curl_getinfo( $ch );
	if( $httpcode == 201 ) {
		$headers = substr($result, 0, $curl_info["header_size"]);
		preg_match("!\r\n(?:Location): *(.*?) *\r\n!i", $headers, $matches);
		$new_post_url = $matches[1];
	}

	curl_close($ch);

	$show_response = true;
	if( $httpcode == 201 ) {
		// HTTP 201 Created - success!
		echo '<p><strong>Success!</strong> - post created at <a href="'.$new_post_url.'" target="_blank" rel="noopener">'.$new_post_url.'</a></p>';
	} elseif( $httpcode == 401 ) {
		// HTTP 401 Unauthorized - No access token was provided in the request.
		echo '<p><strong>Unauthorized</strong> - no access token was provided in the request.</p>';
	} elseif( $httpcode == 403 ) {
    	// HTTP 403 Forbidden - An access token was provided, but the authenticated user does not have permission to complete the request.
		echo '<p><strong>Forbidden</strong> - the authenticated user does not have permission to complete the request.</p>';
	} elseif( $httpcode == 400 ) {
		// HTTP 400 Bad Request - Something was wrong with the request, such as a missing "h" parameter, or other missing data. The response body may contain more human-readable information about the error.
		echo '<p><strong>Bad Request</strong> - something was wrong with the request.</p>';
	} elseif( $httpcode == 500 ) {
		// HTTP 500 Internal Server Error
		echo '<p><strong>Internal Server Error</strong> - something went wrong.</p>';
	//	$show_response = false;
	}

	echo '<p>--> <a href="micropub.php">new post</a></p>';

	if( $show_response ) {
		echo '<details><summary>Debug-Info</summary>';
			echo '<h3>Request</h3>';
			echo '<p><strong>Data:</strong>';
			echo '<pre>'; var_dump($data); echo '</pre>';
			echo '<p><strong>API request:</strong> '.$url.'</p>';
			echo '<hr>';
			echo '<h3>Answer</h3>';
			echo '<p><strong>HTTP Status Code:</strong> '.$httpcode.'</p>';
			echo '<p><strong>Server Response:</strong></p>';
			echo '<pre><code>';
			$body = substr($result, $curl_info['header_size']);
			var_dump($result);
			var_dump(json_decode($body));
			echo '</code></pre>';
		echo '</details>';
	}


} else {
	// form

	$tags = np_mp_get_tags();

	$content = '';
	if( isset($_GET['content']) ) $content = urldecode($_GET['content']);

	?>
	<p>(this will be posted to <a href="<?= $_SESSION['me'] ?>" target="_blank" rel="noopener"><?= $_SESSION['me'] ?></a>)</p>
	<form action="micropub.php" method="post" enctype="multipart/form-data">

		<p><label><strong>Status</strong>:<br><select name="status" required><option value="draft">Draft</option><option selected value="published">Publish</option></select></label></p>

		<p><label><strong>Title</strong> <small>(optional)</small>:<br><input name="title" style="width: 100%;" placeholder="No Title"></label></p>

		<p><label><strong>Slug</strong> <small>(optional)</small>:<br><input name="slug" style="width: 100%;" placeholder="no-title"></label></p>

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

		<p><label><strong>Tags</strong> <small>(optional, durch Komma getrennt)</small>:<br><input name="tags" style="width: 100%;" placeholder="tag1, tag2"></label></p>
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

		<p><button>post to <?= $_SESSION['name'] ?></button></p>

	</form>
	<?php

}



function np_mp_get_tags() {
	// get tags, if available:

	$api_url = $_SESSION['micropub_endpoint'];

	$url = $api_url.'?q=config';
	if( isset($_REQUEST['debug']) ) {
		echo '<p><strong>API request to:</strong> '.$url.'</p>';
		echo '<pre><code>';
	}


	$cache = new Cache( 'micropub', $url, false, 60*5 ); // cache for 5 minutes

	$data = $cache->get_data();
	if( $data ) {

		$config = json_decode($data);

	} else {

		$authorization = 'Authorization: Bearer '.$_SESSION['access_token'];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($result);

		if( isset($_REQUEST['debug']) ) {
			echo '<p><strong>result:</strong></p>';
			var_dump($result);
			echo '<p><strong>json:</strong></p>';
			var_dump($json);
			echo '</code></pre>';
		}
		$config = $json;

		$cache->add_data( json_encode($config) );

	}


	$tags = [];
	if( isset($config->categories) ) {
		$tags = $config->categories;
	}

	return $tags;
}



snippet( 'footer' );
