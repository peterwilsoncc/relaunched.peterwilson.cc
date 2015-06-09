PWCC = window.PWCC || {};
PWCC.commentReply = (function( window, undefined ){
	// Avoid scope lookups on commonly used variables
	var document = window.document;
	var PWCC = window.PWCC;

	// initialise the events
	init();

	/**
	 * Add events to links classed .comment-reply-link.
	 *
	 * Searches the context for reply links and adds the JavaScript events
	 * required to move the comment form. To allow for lazy loading of
	 * comments this method is exposed as PWCC.commentReply.init()
	 *
	 * @since 0.2
	 *
	 * @param {HTMLElement} context The parent DOM element to search for links.
	 */
	function init( context ) {
		var links = replyLinks();
		var i,l;
		var element;

		for ( i=0, l=links.length; i<l; i++ ) {
			element = links[i];

			addEvent(element, "click", clickEvent );
		}
	}


	/**
	 * Return all links classed .comment-reply-link
	 *
	 * @since 0.2
	 *
	 * @param {HTMLElement} context The parent DOM element to search for links.
	 *
	 * @return {HTMLCollection|NodeList|Array}
	 */
	function replyLinks( context ) {
		var selectorClass = 'comment-reply-link';
		var allReplyLinks;
		var allLinks;
		var i,l;

		// childNodes is a handy check to ensure the context is a HTMLElement
		if ( !context || !context.childNodes ) {
			context = document;
		}

		if ( document.getElementsByClassName ) {
			// fastest
			allReplyLinks = context.getElementsByClassName( selectorClass );
		}
		else if ( document.querySelectorAll ) {
			// fast
			allReplyLinks = context.querySelectorAll( '.' + selectorClass );
		}
		else {
			// slow (IE7 and earlier)
			allReplyLinks = [];
			allLinks = context.getElementsByTagName( 'a' );

			for ( i=0,l=allLinks.length; i<l; i++ ) {
				if ( hasClass( allLinks[i], selectorClass ) ) {
					allReplyLinks.push( allLinks[i] );
				}
			}
		}

		return allReplyLinks;
	}


	/**
	 * Check if an element includes a particular class
	 *
	 * @since 0.2
	 *
	 * @param {HTMLElement} element   The element to check for the class
	 * @param {String}      className The class to check for
	 * @returns Boolean
	 */
	function hasClass( element, className ) {
		var elementClass = ' ' + element.className + ' ';
		className = ' ' + className + ' ';

		if ( elementClass.indexOf( className ) === -1 ) {
			// class not found
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * Click event handler
	 *
	 * @since 0.2
	 *
	 * @param {Event} event The calling event
	 */
	function clickEvent( event ) {
		var replyLink = this,
			commId = replyLink.getAttribute( 'data-add-below-element'),
			parentId = replyLink.getAttribute( 'data-comment-id' ),
			respondId = replyLink.getAttribute( 'data-respond-element'),
			postId =  replyLink.getAttribute( 'data-post-id');

		addComment.moveForm(commId, parentId, respondId, postId);
		event.preventDefault();
	}


	// written by Dean Edwards, 2005
	// with input from Tino Zijdel, Matthias Miller, Diego Perini

	// http://dean.edwards.name/weblog/2005/10/add-event/

	function addEvent(element, type, handler) {
		if (element.addEventListener) {
			element.addEventListener(type, handler, false);
		} else {
			// assign each event handler a unique ID
			if (!handler.$$guid) handler.$$guid = addEvent.guid++;
			// create a hash table of event types for the element
			if (!element.events) element.events = {};
			// create a hash table of event handlers for each element/event pair
			var handlers = element.events[type];
			if (!handlers) {
				handlers = element.events[type] = {};
				// store the existing event handler (if there is one)
				if (element["on" + type]) {
					handlers[0] = element["on" + type];
				}
			}
			// store the event handler in the hash table
			handlers[handler.$$guid] = handler;
			// assign a global event handler to do all the work
			element["on" + type] = handleEvent;
		}
	}

	// a counter used to create unique IDs
	addEvent.guid = 1;

	function removeEvent(element, type, handler) {
		if (element.removeEventListener) {
			element.removeEventListener(type, handler, false);
		} else {
			// delete the event handler from the hash table
			if (element.events && element.events[type]) {
				delete element.events[type][handler.$$guid];
			}
		}
	}

	function handleEvent(event) {
		var returnValue = true;
		// grab the event object (IE uses a global event object)
		event = event || fixEvent(((this.ownerDocument || this.document || this).parentWindow || window).event);
		// get a reference to the hash table of event handlers
		var handlers = this.events[event.type];
		// execute each event handler
		for (var i in handlers) {
			this.$$handleEvent = handlers[i];
			if (this.$$handleEvent(event) === false) {
				returnValue = false;
			}
		}
		return returnValue;
	}

	function fixEvent(event) {
		// add W3C standard event methods
		event.preventDefault = fixEvent.preventDefault;
		event.stopPropagation = fixEvent.stopPropagation;
		return event;
	}

	fixEvent.preventDefault = function() {
		this.returnValue = false;
	};

	fixEvent.stopPropagation = function() {
		this.cancelBubble = true;
	};



	var addComment = {
		moveForm : function(commId, parentId, respondId, postId) {
			var t = this, div, comm = t.I(commId), respond = t.I(respondId), cancel = t.I('cancel-comment-reply-link'), parent = t.I('comment_parent'), post = t.I('comment_post_ID');

			if ( ! comm || ! respond || ! cancel || ! parent )
				return;

			t.respondId = respondId;
			postId = postId || false;

			if ( ! t.I('wp-temp-form-div') ) {
				div = document.createElement('div');
				div.id = 'wp-temp-form-div';
				div.style.display = 'none';
				respond.parentNode.insertBefore(div, respond);
			}

			comm.parentNode.insertBefore(respond, comm.nextSibling);
			if ( post && postId )
				post.value = postId;
			parent.value = parentId;
			cancel.style.display = '';

			cancel.onclick = function() {
				var t = addComment, temp = t.I('wp-temp-form-div'), respond = t.I(t.respondId);

				if ( ! temp || ! respond )
					return;

				t.I('comment_parent').value = '0';
				temp.parentNode.insertBefore(respond, temp);
				temp.parentNode.removeChild(temp);
				this.style.display = 'none';
				this.onclick = null;
				return false;
			};

			try { t.I('comment').focus(); }
			catch(e) {}

			return false;
		},

		I : function(e) {
			return document.getElementById(e);
		}
	};



	return {
		init: init
	};

})( window );