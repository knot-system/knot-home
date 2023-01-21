<?php

// Version: alpha.1

if( ! $sekretaer ) exit;

head_html();


$navigation = get_navigation();

?>

<header>
	<h1><a href="<?= url() ?>">Sekretär</a></h1>

	<nav>
		<ul>
			<?php
			foreach( $navigation as $element ) {
				$classes = array();
				if( $element['active'] ) $classes[] = 'current-nav-item';
			?>
			<li<?= get_class_attribute($classes) ?>>
				<a href="<?= $element['url'] ?>"><?= $element['name'] ?></a>
			</li>
			<?php
			}
			?>
		</ul>
	</nav>

</header>

<main>
