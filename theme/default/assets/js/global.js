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


	var ItemShortener = {

		init: function(){

			var items = document.querySelectorAll('.item-content');

			for( var item of items ) {
				item.classList.add('shortened');

				if( item.scrollHeight > item.clientHeight ) {

					var button = document.createElement('span');
					button.classList.add('button');
					button.classList.add('expand-button');
					button.innerHTML = 'read more …';
					button.addEventListener( 'click', ItemShortener.expand );

					item.after(button);

				}

			}

		},

		expand: function(){
			var button = this,
				item = button.parentNode.querySelector('.item-content');

			item.classList.remove('shortened');

			button.remove();
		}

	};


	window.addEventListener( 'load', function(){
		MobileMenu.init();
		ItemShortener.init();
	});
	
})();
