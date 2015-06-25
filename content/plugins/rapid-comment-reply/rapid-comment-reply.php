<?php
/*
Plugin Name: Rapid Comment Reply
Description: Reworking of WordPress's frontend comment-reply.js to be unobtrusive. Refer to trac ticket #31590.
Version: 0.3
Author: Peter Wilson
Author URI: http://peterwilson.cc/
*/


class PWCC_RapidCommentReply {

	private $version;

	function __construct() {
		$this->version = "0.3";

		add_action( 'wp_enqueue_scripts', array( $this, 'replace_comment_reply_source' ), 99 );

		add_filter( 'comment_reply_link', array( $this, 'filter_comment_reply_link' ), 10, 4 );
	}

	function replace_comment_reply_source() {
		global $wp_scripts;

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		$wp_scripts->registered['comment-reply']->src = plugins_url( "comment-reply$suffix.js", __FILE__ );
		$wp_scripts->registered['comment-reply']->ver = $this->version;
	}

	function filter_comment_reply_link( $link, $args, $comment, $post ) {
		$link = $this->get_comment_reply_link( $args, $comment, $post );

		return $link;
	}


	/**
	 * Retrieve HTML content for reply to comment link.
	 *
	 * @since 2.7.0
	 *
	 * @param array $args {
	 *     Optional. Override default arguments.
	 *
	 *     @type string $add_below  The first part of the selector used to identify the comment to respond below.
	 *                              The resulting value is passed as the first parameter to addComment.moveForm(),
	 *                              concatenated as $add_below-$comment->comment_ID. Default 'comment'.
	 *     @type string $respond_id The selector identifying the responding comment. Passed as the third parameter
	 *                              to addComment.moveForm(), and appended to the link URL as a hash value.
	 *                              Default 'respond'.
	 *     @type string $reply_text The text of the Reply link. Default 'Reply'.
	 *     @type string $login_text The text of the link to reply if logged out. Default 'Log in to Reply'.
	 *     @type int    $depth'     The depth of the new comment. Must be greater than 0 and less than the value
	 *                              of the 'thread_comments_depth' option set in Settings > Discussion. Default 0.
	 *     @type string $before     The text or HTML to add before the reply link. Default empty.
	 *     @type string $after      The text or HTML to add after the reply link. Default empty.
	 * }
	 * @param int         $comment Comment being replied to. Default current comment.
	 * @param int|WP_Post $post    Post ID or WP_Post object the comment is going to be displayed on.
	 *                             Default current post.
	 * @return void|false|string Link to show comment form, if successful. False, if comments are closed.
	 */
	function get_comment_reply_link( $args = array(), $comment = null, $post = null ) {
		$defaults = array(
			'add_below'     => 'comment',
			'respond_id'    => 'respond',
			'reply_text'    => __( 'Reply' ),
			'reply_to_text' => __( 'Reply to %s' ),
			'login_text'    => __( 'Log in to Reply' ),
			'depth'         => 0,
			'before'        => '',
			'after'         => ''
		);

		$args = wp_parse_args( $args, $defaults );

		if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] ) {
			return;
		}

		$comment = get_comment( $comment );

		if ( empty( $post ) ) {
			$post = $comment->comment_post_ID;
		}

		$post = get_post( $post );

		if ( ! comments_open( $post->ID ) ) {
			return false;
		}

		/**
		 * Filter the comment reply link arguments.
		 *
		 * @since 4.1.0
		 *
		 * @param array   $args    Comment reply link arguments. See {@see get_comment_reply_link()}
		 *                         for more information on accepted arguments.
		 * @param object  $comment The object of the comment being replied to.
		 * @param WP_Post $post    The {@see WP_Post} object.
		 */
		$args = apply_filters( 'comment_reply_link_args', $args, $comment, $post );

		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			$link = sprintf( '<a rel="nofollow" class="comment-reply-login" href="%s">%s</a>',
				esc_url( wp_login_url( get_permalink() ) ),
				$args['login_text']
			);
		} else {
			$data_attributes = array(
				'comment-id'        => $comment->comment_ID,
				'post-id'           => $post->ID,
				'add-below-element' => $args['add_below'] . '-' . $comment->comment_ID,
				'respond-element'   => $args['respond_id'],
			);

			$data_attribute_string = '';

			foreach ( $data_attributes as $name => $value ) {
				$data_attribute_string .= " data-${name}=\"" . esc_attr( $value ) . "\"";
			}

			$data_attribute_string = trim( $data_attribute_string );

			$link = sprintf( "<a class='comment-reply-link' href='%s' %s aria-label='%s'>%s</a>",
				esc_url( add_query_arg( 'replytocom', $comment->comment_ID ) ) . "#" . $args['respond_id'],
				$data_attribute_string,
				esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ),
				$args['reply_text']
			);
		}
		/**
		 * Filter the comment reply link.
		 *
		 * @since 2.7.0
		 *
		 * @param string  $link    The HTML markup for the comment reply link.
		 * @param array   $args    An array of arguments overriding the defaults.
		 * @param object  $comment The object of the comment being replied.
		 * @param WP_Post $post    The WP_Post object.
		 */
		/**
		 * Filter disabled while editing to avoid infnite loops
		 */
		// return apply_filters( 'comment_reply_link', $args['before'] . $link . $args['after'], $args, $comment, $post );
		return $args['before'] . $link . $args['after'];
	}



}

function pwcc_rapid_comment_reply_load(){
	global $pwcc_rapid_comment_reply;
	$pwcc_rapid_comment_reply = new PWCC_RapidCommentReply();
}
add_action( 'plugins_loaded', 'pwcc_rapid_comment_reply_load' );