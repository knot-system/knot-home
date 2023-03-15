<?php

// add theme stylesheets:
add_stylesheet( 'assets/fonts/fonts.css' );
add_stylesheet( 'assets/css/global.css' );


// add theme javascript:
add_script( 'assets/js/global.js', 'theme', 'async', true );


// preload important webfonts:
add_metatag( 'font_preload_nunito-400', '<link rel="preload" href="'.$core->theme->url.'assets/fonts/nunito-v25-latin/nunito-v25-latin-regular.woff2" as="font" type="font/woff2" crossorigin="anonymous">' );
add_metatag( 'font_preload_patua-one-400', '<link rel="preload" href="'.$core->theme->url.'assets/fonts/patua-one-v16-latin/patua-one-v16-latin-regular.woff2" as="font" type="font/woff2" crossorigin="anonymous">' );


// change the 'generator' meta-tag to include the current theme:
remove_metatag( 'generator' );
add_metatag( 'generator', '<meta tag="generator" content="Sekretär v.'.$core->version().' with '.$core->theme->get('name').' v.'.$core->theme->get('version').'">' );


// add favicon
add_metatag( 'favicon', '<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%2210 0 100 100%22><text y=%22.90em%22 font-size=%2290%22>🗞️</text></svg>">' );
