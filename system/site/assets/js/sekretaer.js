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

				if( input.type == 'hidden' ) continue;

				input.readOnly = true;

				if( input.type == 'checkbox' ) {
					// NOTE: input[type="checkbox"] can't have a readOnly attribute,
					// so we need a 'disabled' attribute, but then the value will
					// not be POSTed, so we need to add a additional hidden input:

					var inputHidden = document.createElement('input');
					inputHidden.type = 'hidden';
					inputHidden.name = input.name;
					inputHidden.value = input.checked;

					form.appendChild(inputHidden);

					input.disabled = true;
				}
			}

		}, false );
	}

};

window.addEventListener( 'load', function(e){
	Login.init();
});

})();