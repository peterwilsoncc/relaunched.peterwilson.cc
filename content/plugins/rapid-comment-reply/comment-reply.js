PWCC = window.PWCC || {};
PWCC.commentReply = (function( window, undefined ){
	// Avoid scope lookups on commonly used variables
	var document = window.document;
	var PWCC = window.PWCC;
	
	// check browser cuts the mustard
	var cutsTheMustard = 'querySelector' in document && 'addEventListener' in window;
	
	// for holding the cancel element
	var cancelElement;
	
	// the respond element
	var respondElement;

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
		if ( true !== cutsTheMustard ) {
			return;
		}

		// get the cancel element
		cancelElement = getElementById( 'cancel-comment-reply-link' );

		// no cancel element, no replies
		if ( ! cancelElement ) {
			return;
		}
		
		cancelElement.addEventListener( 'click', cancelEvent );

		var links = replyLinks();
		var i,l;
		var element;

		for ( i=0, l=links.length; i<l; i++ ) {
			element = links[i];

			element.addEventListener( 'click', clickEvent );
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

		// childNodes is a handy check to ensure the context is a HTMLElement
		if ( !context || !context.childNodes ) {
			context = document;
		}

		if ( document.getElementsByClassName ) {
			// fastest
			allReplyLinks = context.getElementsByClassName( selectorClass );
		}
		else {
			// fast
			allReplyLinks = context.querySelectorAll( '.' + selectorClass );
		}

		return allReplyLinks;
	}
	
	
	/**
	 * Cance event handler
	 * 
	 * @since 1.0
	 *
	 * @param {Event} event The calling event
	 */
	function cancelEvent( event ) {
		var cancelLink = this;
		var temporaryFormId  = "wp-temp-form-div";
		var temporaryElement = getElementById( temporaryFormId );
		
		if ( ! temporaryElement || ! respondElement ) {
			// conditions for cancel link fail
			return;
		}

		getElementById('comment_parent').value = '0';
		
		// move the respond form back in place of the tempory element
		temporaryElement.parentNode.replaceChild( respondElement ,temporaryElement );
		cancelLink.style.display = 'none';
		event.preventDefault();
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

		moveForm(commId, parentId, respondId, postId);
		event.preventDefault();
	}


	/**
	 * Get element by Id
	 *
	 * local alias for document.getElementById
	 *
	 * @since 0.4
	 *
	 * @param {HTMLElement} The requested element
	 */
	function getElementById( elementId ) {
		return document.getElementById( elementId );
	}


	/**
	 * moveForm
	 * 
	 * Moves the reply form from it's current position to the reply location
	 *
	 * @since 1.0
	 *
	 * @param {String} addBelowId HTML ID of element the form follows
	 * @param {String} commentId  Database ID of comment being replied to
	 * @param {String} respondId  HTML ID of 'respond' element
	 * @param {String} postId     Database ID of the post
	 */
	
	function moveForm( addBelowId, commentId, respondId, postId ) {
		// get elements based on their IDs
		var addBelowElement = getElementById( addBelowId );
		respondElement  = getElementById( respondId );
		
		// get the hidden fields
		var parentIdField   = getElementById( 'comment_parent' );
		var postIdField     = getElementById( 'comment_post_ID' );
		
		if ( ! addBelowElement || ! respondElement || ! parentIdField ) {
			// missing key elements, fail
			return;
		}
		
		addPlaceHolder( respondElement );
		
		// set the value of the post
		if ( postId && postIdField ) {
			postIdField.value = postId;
		}
		
		parentIdField.value = commentId;
		
		cancelElement.style.display = '';
		addBelowElement.parentNode.insertBefore( respondElement, addBelowElement.nextSibling );

	}


	/**
	 * add placeholder element 
	 *
	 * Places a place holder element above the #respond element for 
	 * the form to be returned to if needs be.
	 *
	 * @param {HTMLelement} respondElement the #respond element holding comment form
	 *
	 * @since 1.0
	 */
	function addPlaceHolder( respondElement ) {
		var temporaryFormId  = "wp-temp-form-div";
		var temporaryElement = getElementById( temporaryFormId );
		
		if ( temporaryElement ) {
			// the element already exists.
			// no need to recreate 
			return;
		}
		
		temporaryElement = document.createElement( 'div' );
		temporaryElement.id = temporaryFormId;
		temporaryElement.style.display = 'none';
		respondElement.parentNode.insertBefore( temporaryElement, respondElement );
		
		return;
	}


	return {
		init: init
	};

})( window );