// Deal with JS Support
window.PWCC = window.PWCC || {};

(function( window, undefined ){
	var document = window.document,
		PWCC = window.PWCC,
		html = document.documentElement,
		fonts = ['lato-n4','lato-n7','lato-i4','lato-i7','unisansregular-n4','unisansbold-n7'],
		fontClasses = ' wf-' + fonts.join('-loading wf-') + '-loading ';
		
	PWCC.ready = false;
	html.className=html.className.replace(/\bno-js\b/,'')+' js wf-loading '+ fontClasses;
	
	window.addComment = {
		moveForm:function(){
			return true;
		}
	};
	
	document.addEventListener( "DOMContentLoaded", function(){
		PWCC.ready = true;
	} );

}( window ));