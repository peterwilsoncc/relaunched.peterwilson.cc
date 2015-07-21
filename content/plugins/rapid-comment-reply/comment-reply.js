var addComment;
addComment = (function( window, undefined ){
	// Avoid scope lookups on commonly used variables
	var document = window.document;

	// settings
	var config = {
		commentReplyClass : 'comment-reply-link',
		cancelReplyId     : 'cancel-comment-reply-link',
		commentFieldId    : 'comment',
		temporaryFormId   : 'wp-temp-form-div',
		parentIdFieldId   : 'comment_parent',
		postIdFieldId     : 'comment_post_ID'
	};
	
	// check browser cuts the mustard
	var cutsTheMustard = 'querySelector' in document && 'addEventListener' in window;

	// check browser supports dataset
	// !! sets the variable to truthy if the property exists.
	var supportsDataset = !!document.body.dataset;
	
	// for holding the cancel element
	var cancelElement;
	
	// for holding the comment field element
	var commentFieldElement;
	
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

		// get required elements
		cancelElement = getElementById( config.cancelReplyId );
		commentFieldElement = getElementById( config.commentFieldId );

		// no cancel element, no replies
		if ( ! cancelElement ) {
			return;
		}
		
		cancelElement.addEventListener( 'touchstart', cancelEvent );
		cancelElement.addEventListener( 'click',      cancelEvent );

		var links = replyLinks();
		var i,l;
		var element;

		for ( i=0, l=links.length; i<l; i++ ) {
			element = links[i];

			element.addEventListener( 'touchstart', clickEvent );
			element.addEventListener( 'click',      clickEvent );
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
		var selectorClass = config.commentReplyClass;
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
		var temporaryFormId  = config.temporaryFormId;
		var temporaryElement = getElementById( temporaryFormId );
		
		if ( ! temporaryElement || ! respondElement ) {
			// conditions for cancel link fail
			return;
		}

		getElementById( config.parentIdFieldId ).value = '0';
		
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
			commId    = getDataAttribute( replyLink, 'belowelement'),
			parentId  = getDataAttribute( replyLink, 'commentid' ),
			respondId = getDataAttribute( replyLink, 'respondelement'),
			postId    = getDataAttribute( replyLink, 'postid'),
			follow    = true;

		// third party comments systems can hook into this function via the gloabl scope.
		// therefore the click event needs to reference the gloabl scope.
		follow = window.addComment.moveForm(commId, parentId, respondId, postId);
		if ( false === follow ) {
			event.preventDefault();
		}
	}


	/**
	 * Backward compatible getter of data-* attribute
	 *
	 * Uses element.dataset if it exists, otherwise uses getAttribute
	 *
	 * @since 1.1
	 *
	 * @param {HTMLElement} element DOM element with the attribute
	 * @param {String}      attribute the attribute to get
	 *
	 * @return {String}
	 */
	function getDataAttribute( element, attribute ) {
		if ( supportsDataset ) {
			return element.dataset[attribute];
		}
		else {
			return element.getAttribute( 'data-' + attribute );
		}
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
		var parentIdField   = getElementById( config.parentIdFieldId );
		var postIdField     = getElementById( config.postIdFieldId );
		
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
		
		// this uglyness is for backward compatibility with third party commenting systems
		// hooking into the event using older techniques.
		cancelElement.onclick = function(){
			return false;
		};
		
		// focus on the comment field
		try {
			commentFieldElement.focus();
		}
		catch(e) {
			
		}
		
		// false is returned for backward compatibilty with third party commenting systems
		// hooking into this function. Eg Jetpack Comments.
		return false;
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
		var temporaryFormId  = config.temporaryFormId;
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
		init: init,
		moveForm: moveForm
	};

})( window );