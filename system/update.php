<?php
// this file can update the system with the latest release from github. create a empty file called 'update' or 'update.txt' in the root directory, and then add '?update' to the url, to trigger the update

$api_url = 'https://api.github.com/repos/maxhaesslein/sekretaer/releases';

$step = false;
if( ! empty($_GET['step']) ) $step = $_GET['step'];



$basefolder = str_replace( 'index.php', '', $_SERVER['PHP_SELF']);

if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) $baseurl = 'https://';
else $baseurl = 'http://';
$baseurl .= $_SERVER['HTTP_HOST'];
$baseurl .= $basefolder;


include_once( 'functions/helper.php' );
include_once( 'functions/request.php' );

	
$sekretaer_version = get_system_version( $abspath );

?>
<h1>Sekretär System Update</h1>
<?php


if( $step == 'check' ) {

	$json = get_remote_json( $api_url );
	
	if( ! $json || ! is_array($json) ) {
		?>
		<p><strong>Error:</strong> could not get release information from GitHub</p>
		<?php
		exit;
	}

	$latest_release = $json[0];

	$release_name = $latest_release->name;

	?>
	<p>Latest release: <strong><?= $release_name ?></strong><br>
	Currently installed: <strong><?= $sekretaer_version ?></strong></p>
	<?php

	$release_notes = array();

	$new_version_available = false;
	if( $release_name != $sekretaer_version ) {
		$version_number_old = explode('.', str_replace('alpha.', '0.0.', $sekretaer_version));
		$version_number_new = explode('.', str_replace('alpha.', '0.0.', $release_name));

		for( $i = 0; $i < count($version_number_new); $i++ ){
			$dot_old = $version_number_old[$i];
			$dot_new = $version_number_new[$i];
			if( $dot_new > $dot_old ) $new_version_available = true;
		}

		if( $new_version_available ) {

			foreach( $json as $release ) {
				$release_number = explode('.', str_replace('alpha.', '0.0.', $release->tag_name));

				$newer_version = false;
				for( $i = 0; $i < count($release_number); $i++ ){
					$dot_old = $version_number_old[$i];
					$dot_new = $release_number[$i];
					if( $dot_new > $dot_old ) $newer_version = true;
				}

				if( ! $newer_version ) break;

				$release_notes[] = '<h3>'.$release->tag_name."</h3>\n\n".$release->body;
			}
		
			echo '<p><strong>New version available!</strong> You should update your system.</p>';

			if( count($release_notes) ) {
				?>
				<h2>Release notes:</h2>
				<?php
				include_once($abspath.'system/classes/text.php');
				$text = new Text( implode("\n\n\n", $release_notes) );
				echo $text->auto_p()->get();
			}
		}

	}
	?>
	<hr>

	<form action="<?= $baseurl ?>" method="GET">
		<input type="hidden" name="update" value="true">
		<input type="hidden" name="step" value="install">
		<button><?php if( $new_version_available ) echo 'update system'; else echo 're-install system'; ?></button> (this may take some time, please be patient)
	</form>

	<?php
	exit;

} elseif( $step == 'install' )  {

	$json = get_remote_json( $api_url );
	
	if( ! $json || ! is_array($json) ) {
		?>
		<p><strong>Error:</strong> could not get release information from GitHub</p>
		<?php
		exit;
	}

	$latest_release = $json[0];

	$zipball = $latest_release->zipball_url;

	if( ! $zipball ) {
		?>
		<p><strong>Error:</strong> could not get new .zip file from GitHub</p>
		<?php
		exit;
	}

	echo '<p>Downloading new .zip from GitHub … ';
	flush();

	$temp_zip_file = $abspath.'cache/_new_release.zip';
	if( file_exists($temp_zip_file) ) unlink($temp_zip_file);

	$file_handle = fopen( $temp_zip_file, 'w+' );

	$ch = curl_init( $zipball );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_USERAGENT, 'maxhaesslein/sekretaer/'.$sekretaer_version );
	curl_setopt( $ch, CURLOPT_FILE, $file_handle );
	curl_exec( $ch );
	curl_close( $ch );

	fclose($file_handle);

	echo 'done.</p>';

	echo '<p>Extracting .zip file … ';
	flush();

	function deleteDirectory( $dirPath ) {

		if( ! is_dir($dirPath) ) return;

		$objects = scandir($dirPath);
		foreach ($objects as $object) {
			if( $object == "." || $object == "..") continue;

			if( is_dir($dirPath . DIRECTORY_SEPARATOR . $object) ){
				deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
			} else {
				unlink($dirPath . DIRECTORY_SEPARATOR . $object);
			}
		}
		rmdir($dirPath);
	}


	$temp_folder = $abspath.'cache/_new_release/';
	if( is_dir($temp_folder) ) deleteDirectory($temp_folder);
	mkdir( $temp_folder );

	$zip = new ZipArchive;
	$res = $zip->open($temp_zip_file);
	if( $res !== TRUE ) {
		echo '<p><strong>Error:</strong> could not extract .zip file</p>';
		exit;
	}
	$zip->extractTo( $temp_folder );
	$zip->close();

	echo 'done.</p>';

	$subfolder = false;
	foreach( scandir( $temp_folder ) as $obj ) {
		if( $obj == '.' || $obj == '..' ) continue;
		if( ! is_dir($temp_folder.$obj) ) continue;
		if( ! str_starts_with($obj, 'maxhaesslein-sekretaer-') ) continue;
		// the zip file should have exactly one subfolder, called 'maxhaesslein-sekretaer-{hash}'. this is what we want to get here
		$subfolder = $temp_folder.$obj.'/';
	}

	if( ! $subfolder ) {
		echo '<p><strong>Error:</strong> something went wrong with the .zip file</p>';
		exit;
	}

	echo '<p>Deleting old files … ';
	flush();

	deleteDirectory( $abspath.'theme/default/' );
	deleteDirectory( $abspath.'system/' );
	unlink( $abspath.'.htacces' );
	unlink( $abspath.'index.php' );
	unlink( $abspath.'README.md');
	unlink( $abspath.'changelog.txt');

	echo 'done.</p>';

	echo '<p>Moving new files to new location … ';
	flush();

	rename( $subfolder.'theme/default', $abspath.'theme/default' );
	rename( $subfolder.'system', $abspath.'system' );
	rename( $subfolder.'index.php', $abspath.'index.php' );
	rename( $subfolder.'README.md', $abspath.'README.md' );
	rename( $subfolder.'changelog.txt', $abspath.'changelog.txt' );

	echo 'done.</p>';
	echo '<p>Cleaning up …';
	@unlink( $abspath.'update.txt' );
	@unlink( $abspath.'update' );

	@session_destroy();
	setcookie( 'sekretaer-session', false, array(
		'expires' => -1,
		'path' => '/'
	));

	deleteDirectory( $abspath.'cache/');
	mkdir( $abspath.'cache/' );

	echo 'done.</p>';

	echo '<p>Checking snippets in custom themes … ';
	flush();

	$custom_theme_dir = $abspath.'theme/';
	$custom_themes = [];
	foreach( scandir( $custom_theme_dir ) as $theme_name ) {
		if( $theme_name == '.' || $theme_name == '..' ) continue;
		if( ! is_dir($custom_theme_dir.$theme_name) ) continue;
		if( $theme_name == 'default' ) continue;

		$custom_themes[] = $theme_name;
	}

	if( count($custom_themes) ) {

		echo '<ul>';

		$displayed_update_message = false;

		foreach( $custom_themes as $custom_theme ) {

			$custom_theme_snippets = $abspath.'theme/'.$custom_theme.'/snippets/';
			if( ! is_dir($custom_theme_snippets) ) continue;

			foreach( scandir( $custom_theme_snippets ) as $snippet_name ) {
				if( $snippet_name == '.' || $snippet_name == '..' ) continue;
				if( ! str_ends_with($snippet_name, '.php') ) continue;

				$file_contents = file_get_contents( $custom_theme_snippets.$snippet_name );
				if( preg_match( '/\/\/ Version: (.*)/i', $file_contents, $matches ) ) {

					$custom_theme_snippet_version = trim($matches[1]);

					$system_file_contents = file_get_contents( $abspath.'system/site/snippets/'.$snippet_name );
					if( preg_match( '/\/\/ Version: (.*)/i', $system_file_contents, $system_matches ) ) {
						$system_snippet_version = trim($system_matches[1]);

						if( $custom_theme_snippet_version == $system_snippet_version ) continue;
					
						echo '<li>Custom theme <em>'.$custom_theme.'</em>: snippet <strong>'.$snippet_name.'</strong> needs to be updated! (theme version: '.$custom_theme_snippet_version.', system version: '.$system_snippet_version.')</li>';

						$displayed_update_message = true;

					}

				}

			}

		}

		if( ! $displayed_update_message ) echo '<li>all snippets in all custom themes are up to date</li>';

		echo '</ul>';

	} else {
		echo 'no custom themes found. ';
	}

	echo 'done.</p>';
	flush();

	echo '<p>Please <a href="'.$baseurl.'">refresh this page</a></p>';

} else {
	?>

	<p><strong>Warning: please backup your <em>config.php</em> file and maybe your <em>theme/custom-theme</em> folder before updating!</strong></p>
	<p>Also, read the <a href="https://github.com/maxhaesslein/sekretaer/releases/latest/" target="_blank" rel="noopener">latest release notes</a> before continuing.</p>

	<p>Currently installed version: <em><?= $sekretaer_version ?></em></p>

	<form action="<?= $baseurl ?>" method="GET">
		<input type="hidden" name="update" value="true">
		<input type="hidden" name="step" value="check">
		<button>check for update</button>
	</form>

	<?php

	exit;
}
