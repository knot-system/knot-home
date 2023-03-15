<?php

if( ! $core ) exit;

snippet( 'header' );

?>
<h2>Write a new Post</h2>
<?php


$micropub = new Micropub();


if( isset($_POST['action']) && $_POST['action'] == 'post' ) {

	// form was sent; show error or success message

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


$content = '';
if( isset($_GET['content']) ) $content = urldecode($_GET['content']);

// form
snippet( 'micropub-form', array(
	'tags' => $micropub->get_tags(),
	'content' => $content,
	'me' => $micropub->get_me(),
	'name' => $micropub->get_name(),
	'url' => url('micropub')
));


snippet( 'footer' );
