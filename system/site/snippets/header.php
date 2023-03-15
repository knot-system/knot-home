<?php

// Version: alpha.8

if( ! $core ) exit;

head_html();

$sidebar_content = '';
if( isset($args['sidebar-content']) ) $sidebar_content = $args['sidebar-content'];


$navigation = get_navigation();

?>

<div id="template-<?= str_replace(array('.','/'), '-', $core->route->get('template')) ?>" class="canvas">

	<div id="menu-icon" class="menu-icon"><div class="icon"></div></div>

	<div class="nav-area">

		<header>
			<nav>
				<ul>
					<?php
					foreach( $navigation as $element ) {
						$classes = array();
						$button_classes = array('button');
						if( $element['active'] ) {
							$classes[] = 'current-nav-item';
							$button_classes[] = 'disabled';
						}
					?>
					<li<?= get_class_attribute($classes) ?>>
						<a<?= get_class_attribute($button_classes) ?> href="<?= $element['url'] ?>"><?= $element['name'] ?></a>
					</li>
					<?php
					}
					?>
				</ul>
			</nav>

			<hr>

			<?= $sidebar_content ?>

			<span class="spacer"></span>

			<footer>

				<ul>
					<li<?= get_class_attribute($classes) ?>>
						<a class="button button-small" href="<?= url('action/logout') ?>">Logout</a>
					</li>
				</ul>

				<span class="generator"><a href="https://github.com/maxhaesslein/sekretaer" target="_blank" rel="noopener">Sekretär</a> v.<?= $core->version() ?></span>

			</footer>

		</header>

	</div>

	<div class="content-area">

		<main>
