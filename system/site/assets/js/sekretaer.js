/*
NOTE: you can remove this file in your custom themes functions.php via:
remove_script('js/sekretaer.js');
*/
(function(){
	
var Login = {

	init: function(){

		var main = document.querySelector('main.login');

		if( ! main ) return;

		var loader = document.getElementById('login-loader'),
			form = document.getElementById('login-form');

		if( ! loader || ! form ) return;

		var button = form.querySelector('button'),
			inputs = form.querySelectorAll('input');

		form.addEventListener( 'submit', function(e){

			loader.classList.remove('hidden');

			if( button ) button.disabled = true;

			for( var input of inputs ) {
				input.readOnly = true;
			}

		}, false );
	}

};

window.addEventListener( 'load', function(e){
	Login.init();
});

})();