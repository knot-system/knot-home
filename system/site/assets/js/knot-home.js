/*
NOTE: you can remove this file in your custom themes functions.php via:
remove_script('js/knot-home.js');
*/
(function(){


window.addEventListener( 'load', function(e){
	Login.init();
	Micropub.init();
	LinkPreview.init();
});

	
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


var Micropub = {

	init: function(){

		var canvas = document.getElementById('template-micropub');

		if( ! canvas ) return;

		var form = document.getElementById('micropub-form');

		if( ! form ) return;
		
		Micropub.handleSlug( form );
		Micropub.handleTagSelector( form );
		Micropub.handleImagePreview( form );

	},

	handleSlug: function( form ) {

		var title = form.querySelector('input[name="title"]');
		if( ! title ) return;

		var slug = form.querySelector('input[name="slug"]');
		if( ! slug ) return;

		var sanitizeSlug = function( slug ){
			// slug sanitize function found here: https://mhagemann.medium.com/the-ultimate-way-to-slugify-a-url-string-in-javascript-b8e4a0d849e1
			const a = '\u00e0\u00e1\u00e2\u00e4\u00e6\u00e3\u00e5\u0101\u0103\u0105\u00e7\u0107\u010d\u0111\u010f\u00e8\u00e9\u00ea\u00eb\u0113\u0117\u0119\u011b\u011f\u01f5\u1e27\u00ee\u00ef\u00ed\u012b\u012f\u00ec\u0131\u0130\u0142\u1e3f\u00f1\u0144\u01f9\u0148\u00f4\u00f6\u00f2\u00f3\u0153\u00f8\u014d\u00f5\u0151\u1e55\u0155\u0159\u00df\u015b\u0161\u015f\u0219\u0165\u021b\u00fb\u00fc\u00f9\u00fa\u016b\u01d8\u016f\u0171\u0173\u1e83\u1e8d\u00ff\u00fd\u017e\u017a\u017c\u00b7/_,:;'
			const b = 'aaaaaaaaaacccddeeeeeeeegghiiiiiiiilmnnnnoooooooooprrsssssttuuuuuuuuuwxyyzzz------'
			const p = new RegExp(a.split('').join('|'), 'g')
			var sanitized = slug.toLowerCase()
				.replace(/\s+/g, '-') // Replace spaces with -
				.replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
				.replace(/&/g, '-and-') // Replace & with 'and'
				.replace(/[^\w\-]+/g, '') // Remove all non-word characters
				.replace(/\-\-+/g, '-') // Replace multiple - with single -
				.replace(/^-+/, '') // Trim - from start of text
				.replace(/-+$/, '') ;// Trim - from end of text

			return sanitized;
		}

		var updateSlug = function( el ){

			var val = el.value;

			if( ! val ) {
				slug.value = '';
				return;
			}

			var sanitized = sanitizeSlug( val );

			slug.value = sanitized;
		};

		var updateSlugThis = function(){
			updateSlug( this );
		};

		var timeout;
		var updateSlugWithDelay = function(){
			clearTimeout( timeout );
			var el = this;
			timeout = setTimeout( function(){
				updateSlug( el );
			}, 500 );
		};

		title.addEventListener( 'change', updateSlugThis );
		title.addEventListener( 'keyup', updateSlugThis );

		slug.addEventListener( 'change', updateSlugThis );
		slug.addEventListener( 'keyup', updateSlugWithDelay );

	},

	handleTagSelector: function( form ) {

		var tagSelector = form.querySelector( 'ul.tag-selector' );

		if( ! tagSelector ) return;

		tagSelector.style.display = 'block';

		var tags = tagSelector.querySelectorAll( 'li' );

		var tagsInput = document.querySelector('input[name="tags"]');
		if( ! tagsInput ) return;

		for( var tag of tags ) {
			tag.addEventListener( 'click', function(){
				var el = this;

				var tag = el.innerHTML;
				if( ! tag ) return;

				if( tagsInput.value ) tag = ', '+tag;

				tagsInput.value = tagsInput.value + tag;
			});
		}

	},

	handleImagePreview: function( form ) {

		var file = form.querySelector('input[name="image"]');
		if( ! file ) return;

		var previewArea = form.querySelector('.image-preview');
		if( ! previewArea ) return;

		file.addEventListener("change", function () {
			var files = file.files[0];
			if( ! files ) return;

			var fileReader = new FileReader();

			fileReader.readAsDataURL(files);

			fileReader.addEventListener("load", function() {
				previewArea.innerHTML = '<img src="' + this.result + '" />';
			});    
			
		});

	}

};


var LinkPreview = {

	elements: [],

	init: function(){

		var elements = document.querySelectorAll( 'a.link-preview-needs-refresh' );

		if( ! elements || ! elements.length ) return;

		LinkPreview.elements = Array.from(elements);

		setTimeout( LinkPreview.loadNextLink, 1000 );

	},

	loadNextLink: function(){

		if( LinkPreview.elements.length <= 0 ) return;

		var link = LinkPreview.elements.shift(),
			id = link.id.replace('link-', '');

		LinkPreview.refresh( id );

	},

	refresh: function( id ) {
		
		var url = Knot.API.url+'?link_preview='+id;
		fetch( url, {
			mode: 'same-origin'
		}).then( response => response.json() ).then(function(response){

			if( ! response.success ) {
				LinkPreview.loadNextLink();
				return;
			}

			var data = response.data;

			if( ! data.url || ! data.id ) {
				LinkPreview.loadNextLink();
				return;
			}

			if( data.id != id ) {
				LinkPreview.loadNextLink();
				return;
			}

			var linkPreviewElement = document.getElementById('link-'+data.id);

			var previewHash = linkPreviewElement.dataset.previewHash;

			if( previewHash && data.preview_html_hash == previewHash ) {
				LinkPreview.loadNextLink();
				return;
			}

			if( Knot.API.linkpreview_refresh ) {

				refreshInline = true;

			} else {

				// NOTE: check if element is below viewport; if so, replace the HTML directly, if not, show a refresh button. We do this so that we don't have a layout shift above or in the viewport.

				var bounding = linkPreviewElement.getBoundingClientRect(),
					linkPreviewElementTopOffset = bounding.top,
					viewportHeight = window.innerHeight,
					refreshInline = false;

				if( linkPreviewElementTopOffset > viewportHeight ) {
					refreshInline = true;
				}

			}

			if( refreshInline ) {

				linkPreviewElement.innerHTML = data.preview_html;
				
			} else {

				var refreshButton = document.createElement('div');
				refreshButton.classList.add('link-preview-refresh');

				refreshButton.addEventListener( 'click', function(e){
					e.preventDefault();
					this.parentNode.innerHTML = data.preview_html;
				});

				linkPreviewElement.appendChild(refreshButton);

			}

			linkPreviewElement.classList.remove('link-preview-needs-refresh');

			LinkPreview.loadNextLink();

		}).catch(function(error){
			console.warn('AJAX error', error); // DEBUG
		});

	}

};	


})();