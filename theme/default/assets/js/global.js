(function(){

	var MobileMenu = {

		init: function(){
			var menuButton = document.getElementById( 'menu-icon' );
			if( ! menuButton ) return;

			menuButton.addEventListener( 'click', function(){
				if( document.body.classList.contains('menu-open') ) {
					document.body.classList.remove('menu-open');
				} else {
					document.body.classList.add('menu-open');
				}
			}, false );
		}

	};

	window.addEventListener( 'load', function(){
		MobileMenu.init();
	});
	
})();
