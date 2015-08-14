/* global WebFont, PWCC_data */
/* jshint latedef: nofunc */

(function(P, W, C, c) {
	var w = {
		google: {
			families: ['Lato:400,700,400italic,700italic:latin']
		},
		fontdeck: { id: '47046' },
		/*
		custom: {
			families: ['fontello'],
			testStrings: {
				'fontello': '\uE801\uE802\uE808'
			}
		},
		*/
		timeout: 3000
	},
		e = P.documentElement,
		b = P.createElement(W),
		f = false,
		o = P.getElementsByTagName(W)[0],
		n, T = setTimeout(function() {
			e[C] = e[C].replace(/\bwf-loading\b/g, "") + c + "inactive";
		}, w.timeout);
	e[C] += c + "loading";
	b.src = '//ajax.googleapis.com/ajax/libs/webfont/1.5.10/webfont.js';
	b.onload = b.onreadystatechange = function() {
		n = this.readyState;
		if (f || n && n !== "complete" && n !== "loaded") {return;}
		f = true;
		clearTimeout(T);
		try {
			WebFont.load(w);
		} catch (x) {}
	};
	o.parentNode.insertBefore(b, o);
})(document, 'script', 'className', ' wf-');

/*
(function(document) {
	var assetsDir = PWCC_data.assetsHome;
	var b = {
		"fonts": [{
			"font_family": "fontello",
			"font_size_adjust": "1",
			"font_urls": {
				"eot": assetsDir + "fonts/fontello.eot",
				"ttf": assetsDir + "fonts/fontello.ttf",
				"woff": assetsDir + "fonts/fontello.woff",
				"svg": assetsDir + "fonts/fontello.svg"
			},
			"weight": "normal",
			"style": "normal",
			"name": "fontello"
		}]
	};
	return {
		a: function() {
			for (var code = "", a = 0, f = b.fonts.length; a < f; ++a) {
				code += "@font-face {font-family:'" + b.fonts[a].name + "';";
				code += "src:url('" + b.fonts[a].font_urls.eot + "');";
				code += "src:url('" + b.fonts[a].font_urls.eot + "?')format('embedded-opentype'),";
				code += "url('" + b.fonts[a].font_urls.woff + "')format('woff'),";
				code += "url('" + b.fonts[a].font_urls.ttf + "')format('opentype'),";
				code += "url('" + b.fonts[a].font_urls.svg + "')format('svg');";
				code += "font-weight:" + b.fonts[a].weight + ";";
				code += "font-style:" + b.fonts[a].style + ";";
				code += "}";
			}
			a = document.createElement("style");
			a.type = "text/css";
			document.getElementsByTagName("head")[0].appendChild(a);
			if ( a.styleSheet ) {
				a.styleSheet.cssText = code;
			}
			else {
				a.innerHTML = code;
			}
		}
	};
})(document).a();
*/

(function( window ) {

	if (typeof window.Element === "undefined" || "classList" in document.documentElement) {
		return;
	}

	var prototype = Array.prototype,
		push = prototype.push,
		splice = prototype.splice,
		join = prototype.join;

	function DOMTokenList(el) {
		this.el = el;
		// The className needs to be trimmed and split on whitespace
		// to retrieve a list of classes.
		var classes = el.className.replace(/^\s+|\s+$/g, '').split(/\s+/);
		for (var i = 0; i < classes.length; i++) {
			push.call(this, classes[i]);
		}
	}

	DOMTokenList.prototype = {
		add: function(token) {
			if (this.contains(token)) {
				return;
			}
			push.call(this, token);
			this.el.className = this.toString();
		},
		contains: function(token) {
			return this.el.className.indexOf(token) !== -1;
		},
		item: function(index) {
			return this[index] || null;
		},
		remove: function(token) {
			if (!this.contains(token)) {
				return;
			}
			for (var i = 0; i < this.length; i++) {
				if (this[i] === token) {
					break;
				}
			}
			splice.call(this, i, 1);
			this.el.className = this.toString();
		},
		toString: function() {
			return join.call(this, ' ');
		},
		toggle: function(token) {
			if (!this.contains(token)) {
				this.add(token);
			} else {
				this.remove(token);
			}

			return this.contains(token);
		}
	};

	window.DOMTokenList = DOMTokenList;

	function defineElementGetter(obj, prop, getter) {
		if (Object.defineProperty) {
			Object.defineProperty(obj, prop, {
				get: getter
			});
		} else {
			obj.__defineGetter__(prop, getter);
		}
	}

	defineElementGetter(Element.prototype, 'classList', function() {
		return new DOMTokenList(this);
	});

})( window );

var PWCC = window.PWCC || {};
var console = window.console || {  // jshint ignore:line
	error: function(){},
	info: function(){},
	log: function(){},
	warn: function(){}
};

(PWCC.fns = function( window, PWCC_data, undefined ){
	'use strict';
	
	// var hljs = window.hljs;
	var faux_guid_characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".split('');
	var faux_guid_characters_length = faux_guid_characters.length;
	var document = window.document;
	var pluginName = "pwcc_theme_fns_";
	
	if ( !document.querySelectorAll || !window.addEventListener ) {
		// you're not a very good browser
		return;
	}
	
	document.addEventListener( "DOMContentLoaded", domReadyEvent );
	
	function domReadyEvent() {
		PWCC.hljs( window );
		commentForm();
		fitVids();
		fullWidthBlocks();
		
		enqueueCss();
	}


	/*!
	loadCSS: load a CSS file asynchronously.
	[c]2014 @scottjehl, Filament Group, Inc.
	Licensed MIT
	*/
	function loadCSS( href, before, media, callback ){
		// Arguments explained:
		// `href` is the URL for your CSS file.
		// `before` optionally defines the element we'll use as a reference for injecting our <link>
		// By default, `before` uses the first <script> element in the page.
		// However, since the order in which stylesheets are referenced matters, you might need a more specific location in your document.
		// If so, pass a different reference element to the `before` argument and it'll insert before that instead
		// note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
		var ss = window.document.createElement( "link" );
		var ref = before || window.document.getElementsByTagName( "script" )[ 0 ];
		var sheets = window.document.styleSheets;
		ss.rel = "stylesheet";
		ss.href = href;
		// temporarily, set media to something non-matching to ensure it'll fetch without blocking render
		ss.media = "only x";
		// DEPRECATED
		if( callback ) {
			ss.onload = callback;
		}

		// inject link
		ref.parentNode.insertBefore( ss, ref );
		// This function sets the link's media back to `all` so that the stylesheet applies once it loads
		// It is designed to poll until document.styleSheets includes the new sheet.
		ss.onloadcssdefined = function( cb ){
			var defined;
			for( var i = 0; i < sheets.length; i++ ){
				if( sheets[ i ].href && sheets[ i ].href.indexOf( href ) > -1 ){
					defined = true;
				}
			}
			if( defined ){
				cb();
			}
			else {
				setTimeout(function() {
					ss.onloadcssdefined( cb );
				});
			}
		};
		ss.onloadcssdefined(function() {
			ss.media = media || "all";
		});
		return ss;
	}
	
	function enqueueCss(){
		var css = PWCC_data.loadCss,
			i,
			l=css.length;
		
		if ( 0 === l ) {
			return;
		}
		
		for (i=0; i<l; i++ ) {
			loadCSS( css[i].src );
		}
		
		document.cookie = 'pwccsscache=' + PWCC_data.cssCookieVal + '; path=/';
		document.documentElement.classList.add('pwcc-status-cssloaded');
		
		// echo 'PWCC.loadCSS( "' . $css . '", document.getElementById("pwcc-inline-js") );';
		// echo 'document.cookie = "pwccsscache=' . $pwcc_css_ver . '; path=/";';
	}
	
	function commentForm() {
		var $form = $( '#commentform' ),
			$inputs,
			preChecked = false,
			i,l;
		
		if ( 0 === $form.length ) {
			return;
		}
		
		if ( true === $form[nameSpaceIt('commentForm', 'hasInitialised')] ) {
			return;
		}
		$form[nameSpaceIt('commentForm', 'hasInitialised')] = true;
		
		
		$inputs = $form[0].querySelectorAll( 'input,textarea' );

		if ( 0 === $inputs.length ) {
			return;
		}

		$form[0].addEventListener( 'submit', eventValidateForm );
		for ( i=0,l=$inputs.length; i<l; i++ ) {
			$inputs[i].addEventListener( 'keyup', eventValidateInput );
		}
		
		function eventValidateForm( event ) {
			var formValid = validateCommentForm();
			if ( false === formValid ) {
				console.log( formValid );
				event.preventDefault();
			}
		}
		
		function eventValidateInput() {
			if ( false === preChecked ) {
				return;
			}
			validateCommentInput( this ); // jshint ignore:line
		}
		
		
		function validateCommentForm() {
			var formValid = true,
				$firstInvalid,
				i,l, 
				fieldValid;

			preChecked = true;
			
			for ( i=0,l=$inputs.length; i<l; i++ ) {
				fieldValid = validateCommentInput( $inputs[i] );
				if ( false === fieldValid ) {
					$firstInvalid = ( undefined === $firstInvalid ) ? $inputs[i] : $firstInvalid;
					$firstInvalid.focus();
					formValid = false;
				}
			}
			return formValid;
		}
		
		function validateCommentInput( $input ) {
			var //$label = $( 'label[for="' + $input.id + '"]' )[0],
				validator = function(){return true;},
				$inputSet = $input.parentNode;
			
			if ( 'url' === $input.getAttribute( 'type') ) {
				validator = validateWebsite;
			}
			else if ( 'email' === $input.getAttribute( 'type') ) {
				validator = validateEmail;
			}
			
			if ( ( '' === $input.value ) && ( "true" === $input.getAttribute( 'aria-required' ) ) ) {
				// invalid
				return isError();
			}
			else if ( false === validator( $input.value ) ) {
				// invalid
				return isError();
			}
			else {
				// valid
				return isValid();
			}
			
			function isError() {
				$inputSet.classList.add( 'is-error' );
				$inputSet.classList.remove( 'is-valid' );
				return false;
			}
			function isValid() {
				$inputSet.classList.remove( 'is-error' );
				$inputSet.classList.add( 'is-valid' );
				return true;
			}
		}
		
		
	}
	
	function fullWidthBlocks() {
		var $articleBody = $( '.js-Article_Body' ),
			$allImages, $img,
			i, j;
		
		// eventually set this to  
		var selectors = PWCC_data.Article_FullWidthBlock_Selectors;
		
		
		for( i=$articleBody.length-1 ; 0<=i ; i-- ) {
			$allImages = $articleBody[i].querySelectorAll( selectors.join( ',' ) ); // all images & code blocks
			
			for ( j=$allImages.length-1; 0<=j; j-- ) {
				$img = $allImages[j];
				$img.parentNode.classList.add( 'Article_FullWidthBlock' );
			}
		}
	}
	
	function fitVids() {
		var selectors = PWCC_data.FitVid_Selectors;

		var styles = [];

		measureAndWrap( selectors );
		createStyleElement();
		
		
		function measureAndWrap( selectors ) {
			var $allVids, $vid,
				$wrap, id,
				i,l,
				width,height,ratio,
				ratios = [];

			$allVids = document.querySelectorAll( selectors.join(',') );

			// read loop
			for (i = 0, l = $allVids.length; i < l; i++) {
				$vid = $allVids[i];

				// calculate ratio
				width = $vid.width;
				height = $vid.height;
				ratio = height / width;
				
				ratios[i] = ratio;
			}
			
			// write loop
			// l = $allVids.length // from above
			for (i = 0; i < l; i++) {
				$vid = $allVids[i];

				// generate ID
				id = nameSpaceIt( 'fluid-video-wrapper', guid() );

				// create wrapper element
				$wrap = document.createElement("div");
				$wrap.id = id;
				$wrap.className = "fluid-width-video-wrapper";
				// $wrap.style.paddingTop = (ratio * 100) + "%";
				pushRules(id, ratios[i]);

				// add wrapped video to document
				$vid.parentNode.replaceChild($wrap, $vid);
				$vid.removeAttribute("width");
				$vid.removeAttribute("height");
				$wrap.appendChild($vid);
			}
		}

		function pushRules(wrapId, ratio) {
			var selector = '#' + wrapId, // use wrapper's ID
				paddingTop = (ratio * 100) + "%", // video ratio
				maxWidth = (95 / ratio) + 'vh'; // 95% to give breathing space

			styles.push(selector + '{max-width:' + maxWidth + '}');
			styles.push(selector + ':before{padding-top:' + paddingTop + '}');
		}
		
		function createStyleElement() {
			if ( 0 === styles.length ) {
				// no work to do, break out
				return;
			}
			
			
			var $style = document.createElement('style'),
				$firstScript = document.querySelector( 'script' );

			$firstScript.parentNode.insertBefore( $style, $firstScript );

			if ($style.styleSheet) {
				$style.styleSheet.cssText = styles.join("\n");
			} 
			else {
				$style.innerHTML = styles.join("\n");
			}
			
		}
		
	}
	
	/* helper functions */
	function $( selector ) {  // jshint ignore:line
		return document.querySelectorAll( selector );
	}
	
	function validateEmail(email) {
		// http://stackoverflow.com/a/46181/11236
		
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return '' === email || re.test(email);
	}
	
	function validateWebsite(web) { 
  
	  var re = /^(?:(?:https?|ftp):\/\/){0,1}(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/i;
	    return '' === web || re.test(web);
	}
	
	function nameSpaceIt() {
		var namespaced = pluginName,
			i,l;


		for ( i=0, l=arguments.length; i<l; i++ ) {
			namespaced += '__' + arguments[i];
		}
		if ( 2 > l ) {
			namespaced += '__' + 'hasInitialised';
		}

		return namespaced;
	}
	
	function S4(val) {
		if (val === undefined) {
			val = "";
		}
		val += faux_guid_characters[Math.floor(Math.random() * faux_guid_characters_length)];
		val = (val.length === 4) ? val : S4(val);
		return val;
	}

	// create a faux GUID 
	function guid(){
		return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
	}
}( window, PWCC_data ));

/* jshint ignore: start */
PWCC.hljs = function( window, undefined ) {
	var document = window.document;

	if ( document.body.classList.contains("lte8") ) {
		return;
	}

	var hljs=new function(){function e(e){return e.replace(/&/gm,"&amp;").replace(/</gm,"&lt;").replace(/>/gm,"&gt;")}function t(e){return e.nodeName.toLowerCase()}function n(e,t){var n=e&&e.exec(t);return n&&0==n.index}function r(e){var t=(e.className+" "+(e.parentNode?e.parentNode.className:"")).split(/\s+/);return t=t.map(function(e){return e.replace(/^lang(uage)?-/,"")}),t.filter(function(e){return m(e)||/no(-?)highlight/.test(e)})[0]}function i(e,t){var n={};for(var r in e)n[r]=e[r];if(t)for(var r in t)n[r]=t[r];return n}function a(e){var n=[];return function r(e,i){for(var a=e.firstChild;a;a=a.nextSibling)3==a.nodeType?i+=a.nodeValue.length:1==a.nodeType&&(n.push({event:"start",offset:i,node:a}),i=r(a,i),t(a).match(/br|hr|img|input/)||n.push({event:"stop",offset:i,node:a}));return i}(e,0),n}function s(n,r,i){function a(){return n.length&&r.length?n[0].offset!=r[0].offset?n[0].offset<r[0].offset?n:r:"start"==r[0].event?n:r:n.length?n:r}function s(n){function r(t){return" "+t.nodeName+'="'+e(t.value)+'"'}l+="<"+t(n)+Array.prototype.map.call(n.attributes,r).join("")+">"}function o(e){l+="</"+t(e)+">"}function c(e){("start"==e.event?s:o)(e.node)}for(var u=0,l="",f=[];n.length||r.length;){var h=a();if(l+=e(i.substr(u,h[0].offset-u)),u=h[0].offset,h==n){f.reverse().forEach(o);do c(h.splice(0,1)[0]),h=a();while(h==n&&h.length&&h[0].offset==u);f.reverse().forEach(s)}else"start"==h[0].event?f.push(h[0].node):f.pop(),c(h.splice(0,1)[0])}return l+e(i.substr(u))}function o(e){function t(e){return e&&e.source||e}function n(n,r){return RegExp(t(n),"m"+(e.cI?"i":"")+(r?"g":""))}function r(a,s){if(!a.compiled){if(a.compiled=!0,a.k=a.k||a.bK,a.k){var o={},c=function(t,n){e.cI&&(n=n.toLowerCase()),n.split(" ").forEach(function(e){var n=e.split("|");o[n[0]]=[t,n[1]?Number(n[1]):1]})};"string"==typeof a.k?c("keyword",a.k):Object.keys(a.k).forEach(function(e){c(e,a.k[e])}),a.k=o}a.lR=n(a.l||/\b[A-Za-z0-9_]+\b/,!0),s&&(a.bK&&(a.b="\\b("+a.bK.split(" ").join("|")+")\\b"),a.b||(a.b=/\B|\b/),a.bR=n(a.b),a.e||a.eW||(a.e=/\B|\b/),a.e&&(a.eR=n(a.e)),a.tE=t(a.e)||"",a.eW&&s.tE&&(a.tE+=(a.e?"|":"")+s.tE)),a.i&&(a.iR=n(a.i)),void 0===a.r&&(a.r=1),a.c||(a.c=[]);var u=[];a.c.forEach(function(e){e.v?e.v.forEach(function(t){u.push(i(e,t))}):u.push("self"==e?a:e)}),a.c=u,a.c.forEach(function(e){r(e,a)}),a.starts&&r(a.starts,s);var l=a.c.map(function(e){return e.bK?"\\.?("+e.b+")\\.?":e.b}).concat([a.tE,a.i]).map(t).filter(Boolean);a.t=l.length?n(l.join("|"),!0):{exec:function(){return null}}}}r(e)}function c(t,r,i,a){function s(e,t){for(var r=0;r<t.c.length;r++)if(n(t.c[r].bR,e))return t.c[r]}function l(e,t){return n(e.eR,t)?e:e.eW?l(e.parent,t):void 0}function f(e,t){return!i&&n(t.iR,e)}function h(e,t){var n=x.cI?t[0].toLowerCase():t[0];return e.k.hasOwnProperty(n)&&e.k[n]}function g(e,t,n,r){var i=r?"":N.classPrefix,a='<span class="'+i,s=n?"":"</span>";return a+=e+'">',a+t+s}function p(){if(!w.k)return e(B);var t="",n=0;w.lR.lastIndex=0;for(var r=w.lR.exec(B);r;){t+=e(B.substr(n,r.index-n));var i=h(w,r);i?(y+=i[1],t+=g(i[0],e(r[0]))):t+=e(r[0]),n=w.lR.lastIndex,r=w.lR.exec(B)}return t+e(B.substr(n))}function v(){if(w.sL&&!E[w.sL])return e(B);var t=w.sL?c(w.sL,B,!0,L[w.sL]):u(B);return w.r>0&&(y+=t.r),"continuous"==w.subLanguageMode&&(L[w.sL]=t.top),g(t.language,t.value,!1,!0)}function b(){return void 0!==w.sL?v():p()}function d(t,n){var r=t.cN?g(t.cN,"",!0):"";t.rB?(M+=r,B=""):t.eB?(M+=e(n)+r,B=""):(M+=r,B=n),w=Object.create(t,{parent:{value:w}})}function R(t,n){if(B+=t,void 0===n)return M+=b(),0;var r=s(n,w);if(r)return M+=b(),d(r,n),r.rB?0:n.length;var i=l(w,n);if(i){var a=w;a.rE||a.eE||(B+=n),M+=b();do w.cN&&(M+="</span>"),y+=w.r,w=w.parent;while(w!=i.parent);return a.eE&&(M+=e(n)),B="",i.starts&&d(i.starts,""),a.rE?0:n.length}if(f(n,w))throw new Error('Illegal lexeme "'+n+'" for mode "'+(w.cN||"<unnamed>")+'"');return B+=n,n.length||1}var x=m(t);if(!x)throw new Error('Unknown language: "'+t+'"');o(x);for(var w=a||x,L={},M="",k=w;k!=x;k=k.parent)k.cN&&(M=g(k.cN,"",!0)+M);var B="",y=0;try{for(var C,I,j=0;;){if(w.t.lastIndex=j,C=w.t.exec(r),!C)break;I=R(r.substr(j,C.index-j),C[0]),j=C.index+I}R(r.substr(j));for(var k=w;k.parent;k=k.parent)k.cN&&(M+="</span>");return{r:y,value:M,language:t,top:w}}catch(A){if(-1!=A.message.indexOf("Illegal"))return{r:0,value:e(r)};throw A}}function u(t,n){n=n||N.languages||Object.keys(E);var r={r:0,value:e(t)},i=r;return n.forEach(function(e){if(m(e)){var n=c(e,t,!1);n.language=e,n.r>i.r&&(i=n),n.r>r.r&&(i=r,r=n)}}),i.language&&(r.second_best=i),r}function l(e){return N.tabReplace&&(e=e.replace(/^((<[^>]+>|\t)+)/gm,function(e,t){return t.replace(/\t/g,N.tabReplace)})),N.useBR&&(e=e.replace(/\n/g,"<br>")),e}function f(e,t,n){var r=t?R[t]:n,i=[e.trim()];return e.match(/(\s|^)hljs(\s|$)/)||i.push("hljs"),r&&i.push(r),i.join(" ").trim()}function h(e){var t=r(e);if(!/no(-?)highlight/.test(t)){var n;N.useBR?(n=document.createElementNS("http://www.w3.org/1999/xhtml","div"),n.innerHTML=e.innerHTML.replace(/\n/g,"").replace(/<br[ \/]*>/g,"\n")):n=e;var i=n.textContent,o=t?c(t,i,!0):u(i),h=a(n);if(h.length){var g=document.createElementNS("http://www.w3.org/1999/xhtml","div");g.innerHTML=o.value,o.value=s(h,a(g),i)}o.value=l(o.value),e.innerHTML=o.value,e.className=f(e.className,t,o.language),e.result={language:o.language,re:o.r},o.second_best&&(e.second_best={language:o.second_best.language,re:o.second_best.r})}}function g(e){N=i(N,e)}function p(){if(!p.called){p.called=!0;var e=document.querySelectorAll("pre code");Array.prototype.forEach.call(e,h)}}function v(){addEventListener("DOMContentLoaded",p,!1),addEventListener("load",p,!1)}function b(e,t){var n=E[e]=t(this);n.aliases&&n.aliases.forEach(function(t){R[t]=e})}function d(){return Object.keys(E)}function m(e){return E[e]||E[R[e]]}var N={classPrefix:"hljs-",tabReplace:null,useBR:!1,languages:void 0},E={},R={};this.highlight=c,this.highlightAuto=u,this.fixMarkup=l,this.highlightBlock=h,this.configure=g,this.initHighlighting=p,this.initHighlightingOnLoad=v,this.registerLanguage=b,this.listLanguages=d,this.getLanguage=m,this.inherit=i,this.IR="[a-zA-Z][a-zA-Z0-9_]*",this.UIR="[a-zA-Z_][a-zA-Z0-9_]*",this.NR="\\b\\d+(\\.\\d+)?",this.CNR="(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)",this.BNR="\\b(0b[01]+)",this.RSR="!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~",this.BE={b:"\\\\[\\s\\S]",r:0},this.ASM={cN:"string",b:"'",e:"'",i:"\\n",c:[this.BE]},this.QSM={cN:"string",b:'"',e:'"',i:"\\n",c:[this.BE]},this.PWM={b:/\b(a|an|the|are|I|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such)\b/},this.CLCM={cN:"comment",b:"//",e:"$",c:[this.PWM]},this.CBCM={cN:"comment",b:"/\\*",e:"\\*/",c:[this.PWM]},this.HCM={cN:"comment",b:"#",e:"$",c:[this.PWM]},this.NM={cN:"number",b:this.NR,r:0},this.CNM={cN:"number",b:this.CNR,r:0},this.BNM={cN:"number",b:this.BNR,r:0},this.CSSNM={cN:"number",b:this.NR+"(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",r:0},this.RM={cN:"regexp",b:/\//,e:/\/[gimuy]*/,i:/\n/,c:[this.BE,{b:/\[/,e:/\]/,r:0,c:[this.BE]}]},this.TM={cN:"title",b:this.IR,r:0},this.UTM={cN:"title",b:this.UIR,r:0}};hljs.registerLanguage("markdown",function(){return{aliases:["md","mkdown","mkd"],c:[{cN:"header",v:[{b:"^#{1,6}",e:"$"},{b:"^.+?\\n[=-]{2,}$"}]},{b:"<",e:">",sL:"xml",r:0},{cN:"bullet",b:"^([*+-]|(\\d+\\.))\\s+"},{cN:"strong",b:"[*_]{2}.+?[*_]{2}"},{cN:"emphasis",v:[{b:"\\*.+?\\*"},{b:"_.+?_",r:0}]},{cN:"blockquote",b:"^>\\s+",e:"$"},{cN:"code",v:[{b:"`.+?`"},{b:"^( {4}|	)",e:"$",r:0}]},{cN:"horizontal_rule",b:"^[-\\*]{3,}",e:"$"},{b:"\\[.+?\\][\\(\\[].*?[\\)\\]]",rB:!0,c:[{cN:"link_label",b:"\\[",e:"\\]",eB:!0,rE:!0,r:0},{cN:"link_url",b:"\\]\\(",e:"\\)",eB:!0,eE:!0},{cN:"link_reference",b:"\\]\\[",e:"\\]",eB:!0,eE:!0}],r:10},{b:"^\\[.+\\]:",rB:!0,c:[{cN:"link_reference",b:"\\[",e:"\\]:",eB:!0,eE:!0,starts:{cN:"link_url",e:"$"}}]}]}});hljs.registerLanguage("javascript",function(r){return{aliases:["js"],k:{keyword:"in if for while finally var new function do return void else break catch instanceof with throw case default try this switch continue typeof delete let yield const class",literal:"true false null undefined NaN Infinity",built_in:"eval isFinite isNaN parseFloat parseInt decodeURI decodeURIComponent encodeURI encodeURIComponent escape unescape Object Function Boolean Error EvalError InternalError RangeError ReferenceError StopIteration SyntaxError TypeError URIError Number Math Date String RegExp Array Float32Array Float64Array Int16Array Int32Array Int8Array Uint16Array Uint32Array Uint8Array Uint8ClampedArray ArrayBuffer DataView JSON Intl arguments require module console window document"},c:[{cN:"pi",b:/^\s*('|")use strict('|")/,r:10},r.ASM,r.QSM,r.CLCM,r.CBCM,r.CNM,{b:"("+r.RSR+"|\\b(case|return|throw)\\b)\\s*",k:"return throw case",c:[r.CLCM,r.CBCM,r.RM,{b:/</,e:/>;/,r:0,sL:"xml"}],r:0},{cN:"function",bK:"function",e:/\{/,eE:!0,c:[r.inherit(r.TM,{b:/[A-Za-z$_][0-9A-Za-z$_]*/}),{cN:"params",b:/\(/,e:/\)/,c:[r.CLCM,r.CBCM],i:/["'\(]/}],i:/\[|%/},{b:/\$[(.]/},{b:"\\."+r.IR,r:0}]}});hljs.registerLanguage("json",function(e){var t={literal:"true false null"},i=[e.QSM,e.CNM],l={cN:"value",e:",",eW:!0,eE:!0,c:i,k:t},c={b:"{",e:"}",c:[{cN:"attribute",b:'\\s*"',e:'"\\s*:\\s*',eB:!0,eE:!0,c:[e.BE],i:"\\n",starts:l}],i:"\\S"},n={b:"\\[",e:"\\]",c:[e.inherit(l,{cN:null})],i:"\\S"};return i.splice(i.length,0,c,n),{c:i,k:t,i:"\\S"}});hljs.registerLanguage("sql",function(e){var t={cN:"comment",b:"--",e:"$"};return{cI:!0,i:/[<>]/,c:[{cN:"operator",bK:"begin end start commit rollback savepoint lock alter create drop rename call delete do handler insert load replace select truncate update set show pragma grant merge describe use explain help declare prepare execute deallocate savepoint release unlock purge reset change stop analyze cache flush optimize repair kill install uninstall checksum restore check backup",e:/;/,eW:!0,k:{keyword:"abs absolute acos action add adddate addtime aes_decrypt aes_encrypt after aggregate all allocate alter analyze and any are as asc ascii asin assertion at atan atan2 atn2 authorization authors avg backup before begin benchmark between bin binlog bit_and bit_count bit_length bit_or bit_xor both by cache call cascade cascaded case cast catalog ceil ceiling chain change changed char_length character_length charindex charset check checksum checksum_agg choose close coalesce coercibility collate collation collationproperty column columns columns_updated commit compress concat concat_ws concurrent connect connection connection_id consistent constraint constraints continue contributors conv convert convert_tz corresponding cos cot count count_big crc32 create cross cume_dist curdate current current_date current_time current_timestamp current_user cursor curtime data database databases datalength date_add date_format date_sub dateadd datediff datefromparts datename datepart datetime2fromparts datetimeoffsetfromparts day dayname dayofmonth dayofweek dayofyear deallocate declare decode default deferrable deferred degrees delayed delete des_decrypt des_encrypt des_key_file desc describe descriptor diagnostics difference disconnect distinct distinctrow div do domain double drop dumpfile each else elt enclosed encode encrypt end end-exec engine engines eomonth errors escape escaped event eventdata events except exception exec execute exists exp explain export_set extended external extract fast fetch field fields find_in_set first first_value floor flush for force foreign format found found_rows from from_base64 from_days from_unixtime full function get get_format get_lock getdate getutcdate global go goto grant grants greatest group group_concat grouping grouping_id gtid_subset gtid_subtract handler having help hex high_priority hosts hour ident_current ident_incr ident_seed identified identity if ifnull ignore iif ilike immediate in index indicator inet6_aton inet6_ntoa inet_aton inet_ntoa infile initially inner innodb input insert install instr intersect into is is_free_lock is_ipv4 is_ipv4_compat is_ipv4_mapped is_not is_not_null is_used_lock isdate isnull isolation join key kill language last last_day last_insert_id last_value lcase lead leading least leaves left len lenght level like limit lines ln load load_file local localtime localtimestamp locate lock log log10 log2 logfile logs low_priority lower lpad ltrim make_set makedate maketime master master_pos_wait match matched max md5 medium merge microsecond mid min minute mod mode module month monthname mutex name_const names national natural nchar next no no_write_to_binlog not now nullif nvarchar oct octet_length of old_password on only open optimize option optionally or ord order outer outfile output pad parse partial partition password patindex percent_rank percentile_cont percentile_disc period_add period_diff pi plugin position pow power pragma precision prepare preserve primary prior privileges procedure procedure_analyze processlist profile profiles public publishingservername purge quarter query quick quote quotename radians rand read references regexp relative relaylog release release_lock rename repair repeat replace replicate reset restore restrict return returns reverse revoke right rlike rollback rollup round row row_count rows rpad rtrim savepoint schema scroll sec_to_time second section select serializable server session session_user set sha sha1 sha2 share show sign sin size slave sleep smalldatetimefromparts snapshot some soname soundex sounds_like space sql sql_big_result sql_buffer_result sql_cache sql_calc_found_rows sql_no_cache sql_small_result sql_variant_property sqlstate sqrt square start starting status std stddev stddev_pop stddev_samp stdev stdevp stop str str_to_date straight_join strcmp string stuff subdate substr substring subtime subtring_index sum switchoffset sysdate sysdatetime sysdatetimeoffset system_user sysutcdatetime table tables tablespace tan temporary terminated tertiary_weights then time time_format time_to_sec timediff timefromparts timestamp timestampadd timestampdiff timezone_hour timezone_minute to to_base64 to_days to_seconds todatetimeoffset trailing transaction translation trigger trigger_nestlevel triggers trim truncate try_cast try_convert try_parse ucase uncompress uncompressed_length unhex unicode uninstall union unique unix_timestamp unknown unlock update upgrade upped upper usage use user user_resources using utc_date utc_time utc_timestamp uuid uuid_short validate_password_strength value values var var_pop var_samp variables variance varp version view warnings week weekday weekofyear weight_string when whenever where with work write xml xor year yearweek zon",literal:"true false null",built_in:"array bigint binary bit blob boolean char character date dec decimal float int integer interval number numeric real serial smallint varchar varying int8 serial8 text"},c:[{cN:"string",b:"'",e:"'",c:[e.BE,{b:"''"}]},{cN:"string",b:'"',e:'"',c:[e.BE,{b:'""'}]},{cN:"string",b:"`",e:"`",c:[e.BE]},e.CNM,e.CBCM,t]},e.CBCM,t]}});hljs.registerLanguage("php",function(e){var c={cN:"variable",b:"\\$+[a-zA-Z_-ÿ][a-zA-Z0-9_-ÿ]*"},i={cN:"preprocessor",b:/<\?(php)?|\?>/},a={cN:"string",c:[e.BE,i],v:[{b:'b"',e:'"'},{b:"b'",e:"'"},e.inherit(e.ASM,{i:null}),e.inherit(e.QSM,{i:null})]},n={v:[e.BNM,e.CNM]};return{aliases:["php3","php4","php5","php6"],cI:!0,k:"and include_once list abstract global private echo interface as static endswitch array null if endwhile or const for endforeach self var while isset public protected exit foreach throw elseif include __FILE__ empty require_once do xor return parent clone use __CLASS__ __LINE__ else break print eval new catch __METHOD__ case exception default die require __FUNCTION__ enddeclare final try switch continue endfor endif declare unset true false trait goto instanceof insteadof __DIR__ __NAMESPACE__ yield finally",c:[e.CLCM,e.HCM,{cN:"comment",b:"/\\*",e:"\\*/",c:[{cN:"phpdoc",b:"\\s@[A-Za-z]+"},i]},{cN:"comment",b:"__halt_compiler.+?;",eW:!0,k:"__halt_compiler",l:e.UIR},{cN:"string",b:"<<<['\"]?\\w+['\"]?$",e:"^\\w+;",c:[e.BE]},i,c,{b:/->+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/},{cN:"function",bK:"function",e:/[;{]/,eE:!0,i:"\\$|\\[|%",c:[e.UTM,{cN:"params",b:"\\(",e:"\\)",c:["self",c,e.CBCM,a,n]}]},{cN:"class",bK:"class interface",e:"{",eE:!0,i:/[:\(\$"]/,c:[{bK:"extends implements"},e.UTM]},{bK:"namespace",e:";",i:/[\.']/,c:[e.UTM]},{bK:"use",e:";",c:[e.UTM]},{b:"=>"},a,n]}});hljs.registerLanguage("xml",function(){var t="[A-Za-z0-9\\._:-]+",e={b:/<\?(php)?(?!\w)/,e:/\?>/,sL:"php",subLanguageMode:"continuous"},c={eW:!0,i:/</,r:0,c:[e,{cN:"attribute",b:t,r:0},{b:"=",r:0,c:[{cN:"value",c:[e],v:[{b:/"/,e:/"/},{b:/'/,e:/'/},{b:/[^\s\/>]+/}]}]}]};return{aliases:["html","xhtml","rss","atom","xsl","plist"],cI:!0,c:[{cN:"doctype",b:"<!DOCTYPE",e:">",r:10,c:[{b:"\\[",e:"\\]"}]},{cN:"comment",b:"<!--",e:"-->",r:10},{cN:"cdata",b:"<\\!\\[CDATA\\[",e:"\\]\\]>",r:10},{cN:"tag",b:"<style(?=\\s|>|$)",e:">",k:{title:"style"},c:[c],starts:{e:"</style>",rE:!0,sL:"css"}},{cN:"tag",b:"<script(?=\\s|>|$)",e:">",k:{title:"script"},c:[c],starts:{e:"</script>",rE:!0,sL:"javascript"}},e,{cN:"pi",b:/<\?\w+/,e:/\?>/,r:10},{cN:"tag",b:"</?",e:"/?>",c:[{cN:"title",b:/[^ \/><\n\t]+/,r:0},c]}]}});hljs.registerLanguage("css",function(e){var c="[a-zA-Z-][a-zA-Z0-9_-]*",a={cN:"function",b:c+"\\(",rB:!0,eE:!0,e:"\\("};return{cI:!0,i:"[=/|']",c:[e.CBCM,{cN:"id",b:"\\#[A-Za-z0-9_-]+"},{cN:"class",b:"\\.[A-Za-z0-9_-]+",r:0},{cN:"attr_selector",b:"\\[",e:"\\]",i:"$"},{cN:"pseudo",b:":(:)?[a-zA-Z0-9\\_\\-\\+\\(\\)\\\"\\']+"},{cN:"at_rule",b:"@(font-face|page)",l:"[a-z-]+",k:"font-face page"},{cN:"at_rule",b:"@",e:"[{;]",c:[{cN:"keyword",b:/\S+/},{b:/\s/,eW:!0,eE:!0,r:0,c:[a,e.ASM,e.QSM,e.CSSNM]}]},{cN:"tag",b:c,r:0},{cN:"rules",b:"{",e:"}",i:"[^\\s]",r:0,c:[e.CBCM,{cN:"rule",b:"[^\\s]",rB:!0,e:";",eW:!0,c:[{cN:"attribute",b:"[A-Z\\_\\.\\-]+",e:":",eE:!0,i:"[^\\s]",starts:{cN:"value",eW:!0,eE:!0,c:[a,e.CSSNM,e.QSM,e.ASM,e.CBCM,{cN:"hexcolor",b:"#[0-9A-Fa-f]+"},{cN:"important",b:"!important"}]}}]}]}]}});

	hljs.configure({
		tabReplace: '  '
	});
	hljs.initHighlightingOnLoad();
	
	window.hljs = hljs;

};
/* jshint ignore: end */


