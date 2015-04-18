// Deal with JS Support
window.PWCC = window.PWCC || {};

(function( window, undefined ){
	var document = window.document,
		html = document.documentElement,
		fonts = ['lato-n4','lato-n7','lato-i4','lato-i7','unisansregular-n4','unisansbold-n7'],
		fontClasses = ' wf-' + fonts.join('-loading wf-') + '-loading ';
		
	
	html.className=html.className.replace(/\bno-js\b/,'')+' js wf-loading '+ fontClasses;
	
	window.addComment = {
		moveForm:function(){
			return true;
		}
	};
	

}( window ));