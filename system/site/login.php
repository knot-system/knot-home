<?php

if( ! $sekretaer ) exit;

head_html();

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

			<label><span style="display: inline-block; width: 60px;">URL:</span> <input type="url" name="url" placeholder="https://www.example.com" autofocus style="width: 250px;" required autocomplete="username"></label>

<?php /* TODO
			<span style="display: inline-block; width: 60px;"></span> <label style="display: inline-block"><input type="checkbox" name="autologin" value="true"> stay logged in (this sets a cookie)</label>
*/ ?>

			<br><br>

			<span style="display: inline-block; width: 60px;"></span> <button>Login</button> <span id="login-loader" class="loading hidden"></span>

			<input type="hidden" name="path" value="<?= implode('/', $sekretaer->route->request) ?>">

		</form>

	</section>

</main>

<footer>
	<a href="https://github.com/maxhaesslein/sekretaer" target="_blank" rel="noopener">Sekretär</a> v.<?= $sekretaer->version() ?>
</footer>

<?php
foot_html();
