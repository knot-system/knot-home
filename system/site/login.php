<?php

if( ! $core ) exit;

head_html();


$prefill_url = '';
$cookie = new Cookie('knot-home-url');
if( $cookie->exists() ) {
	$cookie->refresh();
	$prefill_url = $cookie->get();
}

if( isset($_GET['login_url']) ) {
	$prefill_url = $_GET['login_url'];
}

?>

<main class="login">

	<section class="login-content">

		<h2>Please log in</h2>

		<?php
		if( isset($_GET['error']) ) {
			// TODO: better error displaying
			echo '<p><strong>Error:</strong> '.$_GET['error'].'</p>';
		}
		?>


		<form id="login-form" action="<?= url('action/login') ?>" method="POST">

			<label class="login-form-url"><span class="spacer">URL:</span> <input type="url" name="url" placeholder="https://www.example.com" value="<?= $prefill_url ?>" autofocus style="width: 100%; max-width: 340px;" required autocomplete="username"></label>

			<span class="spacer"></span> <label style="display: inline-block"><input type="checkbox" name="autologin" value="true"> stay logged in <small>(this sets a cookie)</small></label>
			
			<br><span class="spacer"></span> <label style="display: inline-block"><input type="checkbox" name="rememberurl" value="true"<?php if( $prefill_url ) echo ' checked'; ?>> remember URL on this page <small>(this sets a cookie)</small></label>

			<br><br>

			<span class="spacer"></span> <button>Login</button> <span id="login-loader" class="loading hidden"></span>

			<input type="hidden" name="path" value="<?= implode('/', $core->route->request) ?>">

		</form>
		

	</section>

</main>

<footer>
	<a href="https://github.com/knot-system/knot-home" target="_blank" rel="noopener">Knot Home</a> v.<?= $core->version() ?>
</footer>

<?php
foot_html();
