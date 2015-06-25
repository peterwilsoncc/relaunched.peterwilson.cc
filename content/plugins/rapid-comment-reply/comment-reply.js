PWCC = window.PWCC || {};
PWCC.commentReply = (function( window, undefined ){
	// Avoid scope lookups on commonly used variables
	var document = window.document;
	var PWCC = window.PWCC;

	// check browser cuts the mustard
	var cutsTheMustard = 'querySelector' in document && 'addEventListener' in window;

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