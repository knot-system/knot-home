<?php

if( ! $sekretaer ) exit;

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


// form

$tags = $micropub->get_tags();

$content = '';
if( isset($_GET['content']) ) $content = urldecode($_GET['content']);

$me = $micropub->get_me();

snippet( 'micropub-form', array(
	'tags' => $tags,
	'content' => $content,
	'me' => $me,
	'name' => $micropub->get_name(),
	'url' => url('micropub')
));


snippet( 'footer' );
