<?php
/*
Plugin Name: PWCC's Indieweb features
Plugin URI: https://peterwilson.cc/
Description: Requires HM CMB plugin
Version: 1.0
License: GPL-2.0+
Author: Peter Wilson
Author URI: https://peterwilson.cc/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Location data

function pwccindieweb_location_meta_boxes( array $meta_boxes ) {
	
	$location_fields = array(
		array(
			'id'      => 'geo_public',
			'name'    => 'Include location',
			'type'    => 'checkbox',
			'default' => false,
			'class'   => 'pwccindieweb-location--include',
			'cols'    => 3
		),
		array(
			'id'    => 'geo_address',
			'name'  => 'Location name',
			'type'  => 'text',
			'class' => 'pwccindieweb-location--name',
			'cols'  => 9
		),
		array(
			'id'    => 'geo_latitude',
			'name'  => 'Latitude',
			'type'  => 'text',
			'class' => 'pwccindieweb-location--latitude'
		),
		array(
			'id'    => 'geo_longitude',
			'name'  => 'Longitude',
			'type'  => 'text',
			'class' => 'pwccindieweb-location--longitude'
		)
	);
	
	$fields = array(
		array(
			'id'         => '_pwccindieweb_location',
			'name'       => 'Location data',
			'desc'       => 'Record location data against this post',
			'type'       => 'group',
			'class'      => 'pwccindieweb-location',
			'repeatable' => false,
			'fields'     => $location_fields
		)
	);
	
	
	$meta_boxes[] = array(
		'id' => 'pwccindieweb_location_metabox',
		'title' => 'Location data',
		'pages' => array( 'post', 'pwcc_notes' ),
		'context'    => 'normal',
		'priority'   => 'high',
		'fields' => $location_fields // an array of fields - see individual field documentation.
	);
	
	return $meta_boxes;
	
}
add_filter( 'cmb_meta_boxes', 'pwccindieweb_location_meta_boxes' );


// Location admin javascript

function pwccindieweb_location_javascript() {
	?>
	<script>
	var PWCC = this.PWCC || {};
	PWCC.indieweb = PWCC.indieweb || {};
	
	(PWCC.indieweb.locationAdmin = function( window, undefined ){
		if ( !window.jQuery ) {
			// dependancies not loaded
			return;
		}
		
		var $ = window.jQuery,
			navigator = window.navigator,
			hasInitialisedName = 'pwccIndiewebLocationHasInitialised',
			$metaBox = $( '#pwccindieweb_location_metabox' ),
			$form = $metaBox.closest( 'form' );
		
		if ( !navigator.geolocation ) {
			// I got no idea where you are
			$metaBox.hide();
			return;
		}
		
		initAllSections();
		CMB.addCallbackForClonedField( 'CMB_Group_Field', initAllSections );
		$form.on( 'submit.pwccindieweb-location', clearUnwantedData );
		
		function initAllSections() {
			var $sections  = $metaBox,
				i,l;

			for ( i=0, l=$sections.length; i<l; i++ ) {
				initSectionCheck( $sections[i] );
			}

		}
		
		function clearUnwantedData( e ) {
			var $sections = $metaBox.find( '[data-class="CMB_Group_Field"]' ),
				i,l;

			for ( i=0, l=$sections.length; i<l; i++ ) {
				clearSection( $sections[i] );
			}

			function clearSection( section ) {
				var $section = $( section ),
					$include = $section.find( '.pwccindieweb-location--include' ),
					$lat = $section.find( '.pwccindieweb-location--latitude' ),
					$long = $section.find( '.pwccindieweb-location--longitude' ),
					$name =  $section.find( '.pwccindieweb-location--name' );
				
				if ( false === $include.is( ':checked' ) ) {
					console.log( 'clearing' );
					$lat.val( '' );
					$long.val( '' );
				}
				
			}

		}
		
		function initSectionCheck( section ) {
			var $section = $( section ),
				hasInitialised = $section.data( hasInitialisedName );
			
			if ( true === hasInitialised ) {
				// already done
				return;
			}
			$section.data( hasInitialisedName, true );
			
			initSection( section );
		}
		
		function initSection( section ) {
			var $section = $( section ),
				$include = $section.find( '.pwccindieweb-location--include' ),
				$lat = $section.find( '.pwccindieweb-location--latitude' ),
				$long = $section.find( '.pwccindieweb-location--longitude' ),
				$name =  $section.find( '.pwccindieweb-location--name' );
			
			
			console.log( 'initSection', $section );
			
			setLocation();
			// $lat.closest( '.field' ).hide();
			// $long.closest( '.field' ).hide();
			$include.on( 'change.pwccindieweb-location', setLocation );
			
			function setLocation(){
				if ( $include.is( ':checked' ) ) {
					navigator.geolocation.getCurrentPosition( function(geo) {
						var lat = geo.coords.latitude,
							long = geo.coords.longitude,
							where;
						
						lat = +(Math.round(lat + "e+2")  + "e-2");
						long = +(Math.round(long + "e+2")  + "e-2");
						where = lat + ',' + long;
						$lat.val( lat );
						$long.val( long );
					} );
				}
				else {
					$lat.val( '' );
					$long.val( '' );
				}
			}
			
			
		}
		
	}( window ));
	</script>
	<?php
}
add_action('admin_footer-post.php', 'pwccindieweb_location_javascript');
add_action('admin_footer-post-new.php', 'pwccindieweb_location_javascript');


// People content type

function pwccindieweb_people_register_content_type() {
	
	$labels = array(
		'name'               => 'People',
		'singular_name'      => 'Person',
		'menu_name'          => 'People',
		'name_admin_bar'     => 'Person',
		'add_new_item'       => 'Add New Person',
		'new_item'           => 'New Person',
		'edit_item'          => 'Edit Person',
		'view_item'          => 'View Person',
		'all_items'          => 'All People',
		'search_items'       => 'Search People',
		'parent_item_colon'  => 'Parents:',
		'not_found'          => 'No people found.',
		'not_found_in_trash' => 'No people found in Trash.'
	);
	
	$args = array(
		'label'             => 'People',
		'labels'            => $labels,
		'public'            => false,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'capability_type'   => 'post',
		'has_archive'       => false,
		'hierarchical'      => false,
		'supports'          => array( 'title', 'thumbnail', 'excerpt' )
	);
	
	register_post_type( 'pwcc_people', $args );
}
add_action( 'init', 'pwccindieweb_people_register_content_type' );

// People data

function pwccindieweb_people_meta_boxes( array $meta_boxes ) {
	$people_fields = array(
		array(
			'id'       => '_pwccindieweb_person_website',
			'name'     => 'Website',
			'type'     => 'text_url',
			'class'    => 'pwccindieweb-people--website',
			'cols'     => 6
		),
		array(
			'id'       => '_pwccindieweb_person_twitter',
			'name'     => 'Twitter handle',
			'type'     => 'text',
			'class'    => 'pwccindieweb-people--twitter',
			'cols'     => 6
		),
		array(
			'id'       => '_pwccindieweb_person_facebook',
			'name'     => 'Facebook URI',
			'type'     => 'text_url',
			'class'    => 'pwccindieweb-people--facebook',
			'cols'     => 6
		),
		array(
			'id'       => '_pwccindieweb_person_instagram',
			'name'     => 'Instagram handle',
			'type'     => 'text',
			'class'    => 'pwccindieweb-people--instagram',
			'cols'     => 6
		),
		array(
			'id'       => '_pwccindieweb_person_display-as',
			'name'     => 'Display as',
			'desc'     => 'How to display and link to this person in notes',
			'type'     => 'select',
			'options'  => array(
				'name:website'    => 'Show name, link to site (preferred)',
				'name:twitter'    => 'Show name, link to twitter',
				'name:facebook'    => 'Show name, link to facebook',
				'twitter:twitter' => 'Show twitter, link to twitter'
			),
			'class'   => 'pwccindieweb-people--display-as',
			'cols'    => 12
		)
	);


	$meta_boxes[] = array(
		'id' => 'pwccindieweb_people_metabox',
		'title' => 'Personal details',
		'pages' => array( 'pwcc_people' ),
		'context'    => 'normal',
		'priority'   => 'high',
		'fields' => $people_fields // an array of fields - see individual field documentation.
	);

		
	return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'pwccindieweb_people_meta_boxes' );


// Notes content type

function pwccindieweb_notes_register_content_type() {
	
	$labels = array(
		'name'               => 'Notes',
		'singular_name'      => 'Note',
		'menu_name'          => 'Notes',
		'name_admin_bar'     => 'Note',
		'add_new_item'       => 'Add New Note',
		'new_item'           => 'New Note',
		'edit_item'          => 'Edit Note',
		'view_item'          => 'View Note',
		'all_items'          => 'All Notes',
		'search_items'       => 'Search Notes',
		'parent_item_colon'  => 'Parent note:',
		'not_found'          => 'No notes found.',
		'not_found_in_trash' => 'No notes found in Trash.'
	);
	
	$args = array(
		'label'             => 'Notes',
		'labels'            => $labels,
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'capability_type'   => 'post',
		'has_archive'       => true,
		'hierarchical'      => false,
		'supports'          => array( 'comments', 'author', 'editor', 'title' ),
		'rewrite'           => array( 'slug' => '~' )
	);
	
	register_post_type( 'pwcc_notes', $args );

}
add_action( 'init', 'pwccindieweb_notes_register_content_type' );


// notes custom meta boxes

function pwccindieweb_notes_meta_boxes( array $meta_boxes ) {
	
	$silo_fields = array(
		array(
			'id' => 'text',
			'name' => 'Text',
			'type' => 'textarea',
			'rows' => '3',
			'class' => 'pwccindieweb-note-text'
		),
		array( 
			'id'   => 'post_on_twitter', 
			'name' => 'Post on twitter', 
			'type' => 'checkbox', 
			'cols' => '6',
			'default' => true,
			'class' => 'pwccindieweb-note-post-on-twitter'
		),
		array( 
			'id'   => 'append_url', 
			'name' => 'Append URL to post', 
			'type' => 'checkbox',
			'cols' => '6',
			'class'=> 'pwccindieweb-note-append-url'
		),
		array(
			'id'  => 'images',
			'name'=> 'Images to include',
			'type'=> 'image',
			'class'=> 'pwccindieweb-note-images',
			'repeatable' => true,
			'repeatable_max' => 4
		)
	);

	$fields = array(
		array( 
			'id' => '_pwccindieweb-note',
			'desc' => 'Post to Twitter',
			'name' => 'Twitter',
			'type' => 'group',
			'class'=> 'pwccindieweb-twitter',
			'repeatable' => false,
			'fields' => $silo_fields
		)
	);
	
	
	$meta_boxes[] = array(
		'id' => 'pwccindieweb_notes_metabox',
		'title' => 'Notes',
		'pages' => array( 'post', 'page', 'pwcc_notes' ),
		'context'    => 'normal',
		'priority'   => 'high',
		'fields' => $fields // an array of fields - see individual field documentation.
	);
	
	
	return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'pwccindieweb_notes_meta_boxes' );


function pwccindieweb_notes_admin_enqueue_scripts( $hook ) {
	if ( ( 'post-new.php' == $hook) OR ( 'post.php' == $hook) OR ( 'edit.php' == $hook) ) {
		wp_enqueue_script( 'pwccindieweb_twitter-text', plugin_dir_url( __FILE__ ) . 'twitter-text.js', null, '1.9.4', true );
	}
}
add_action('admin_enqueue_scripts', 'pwccindieweb_notes_admin_enqueue_scripts');

function pwccindieweb_notes_javascript() {
	?>
	<script>
	var PWCC = this.PWCC || {};
	PWCC.indieweb = PWCC.indieweb || {};
	
	(PWCC.indieweb.notesAdmin = function( window, undefined ){
		if ( !window.jQuery || !window.twttr || !window.twttr.txt ) {
			// dependancies not loaded
			return;
		}
		
	var $ = window.jQuery,
		twttr = window.twttr,
		hasInitialisedName = 'pwccIndiewebNotesHasInitialised',
		$metaBox = $( '#pwccindieweb_notes_metabox' ),
		$editor = $( '#content' ),
		$form = $metaBox.closest( 'form' ),
		options = {
			short_url_length: 22,
			short_url_length_https: 23
		};


	// initEditor();
	initAllSections();
	CMB.addCallbackForClonedField( 'CMB_Group_Field', initAllSections );
	
	function initAllSections() {
		var $sections  = $metaBox.find( '[data-class="CMB_Group_Field"]' ),
			i,l;

		for ( i=0, l=$sections.length; i<l; i++ ) {
			initSectionCheck( $sections[i] );
		}


		if ( 'pwcc_notes' == $( 'input#post_type' ).val() ) {
			// $( '#post-body-content' ).hide();
		}

	}
	
	function initSectionCheck( section ) {
		var $section = $( section ),
			hasInitialised = $section.data( hasInitialisedName );
		
		if ( true === hasInitialised ) {
			// already done
			return;
		}
		$section.data( hasInitialisedName, true );
		
		initSection( section );
	}
	
	function initSection( section ) {
		var $section = $( section ),
			$text = $section.find( '.pwccindieweb-note-text' ),
			$textLabel = $section.find( 'label[for="' + $text.attr( 'id' ) + '"]'),
			$textCount = $textLabel.find( '.pwccindieweb-note-text-counter' ).first(),
			$appendUrl = $section.find( '.pwccindieweb-note-append-url' ),
			$images = $section.find( '.CMB_Image_Field.repeatable' );
		
		if ( $textCount.length === 0 ) {
			$textLabel.append( ' <span class="pwccindieweb-note-text-counter">140</span>');
			$textCount = $textLabel.find( '.pwccindieweb-note-text-counter' ).first();
		}
		
		calculateRemaining();
		$section.on( 'change.pwccindieweb-notes click.pwccindieweb-notes', calculateRemaining );
		$text.on( 'keyup.pwccindieweb-notes', calculateRemaining );
		
		function calculateRemaining() {
			var remaining = 140,
				counterHtml;
			if ( $appendUrl.is( ':checked' ) ) {
				remaining = remaining - 1 - options.short_url_length_https;
			}
		
			var imageCount = $images.find( '.field-item' ).not( '.hidden' ).find( 'img' ).length;
			console.log( imageCount );
		
			if ( imageCount > 0 ) {
				remaining = remaining - 1 - options.short_url_length_https;
			}
		
			remaining = remaining - twttr.txt.getTweetLength( $text.val() );
			if ( 10 >= remaining ) {
				counterHtml = "<span style='color:#d40d12'>" + remaining + '</span>';
			}
			else if ( 20 >= remaining ) {
				counterHtml = "<span style='color:#5c0002'>" + remaining + '</span>';
			}
			else {
				counterHtml = remaining
			}
			$textCount.html( counterHtml );
		}

	}



	function initEditor( ) {
		var $editor = $( '#content' );
		
		$editor.on( 'change keyup', calulateRemianing );
		
		function calulateRemianing() {
			var remaining = 140,
				text = $( '<div />' ).html( $editor.val() ).text(),
				$counterHtml;
			
			remaining = remaining - twttr.txt.getTweetLength( text );
			console.log( remaining );
		}
	}



	}( window ));
	
	</script>
	<?php
}
add_action('admin_footer-post.php', 'pwccindieweb_notes_javascript');
add_action('admin_footer-post-new.php', 'pwccindieweb_notes_javascript');


function pwccindieweb_notes_save_post( $post_id, $post, $update ) {
	
	$post_type = 'pwcc_notes';
	$new_data = array();
	
	if ( $post_type != $post->post_type ) {
		return;
	}
	
	// verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if( isset( $_POST['action'] ) && ( $_POST['action'] == 'inline-save' ) ) {
		return;
	}

	if ( !current_user_can( 'edit_post', $post_id ) ) {
		 return;
	}

	$note = get_post_meta( $post_id, '_pwccindieweb-note', true );
	$note[ 'text' ] = isset( $note['text'] ) ? trim( $note['text'] ) : '';
	$attachments = is_array( $note[ 'images' ] ) ? $note[ 'images' ] : array();
	
	
	if ( '' == trim( get_post_field( 'post_content', $post_id ) ) ) {
		// the content needs to change
		
		$new_content = '';
		
		if ( isset( $note['text'] ) ) {
		
			$new_content = $note[ 'text' ];
		
			// let WP make links clickable
			$new_content = make_clickable( $new_content );
		
			// autolink twitter handles
			$new_content = preg_replace('/(^|\s)@([a-z0-9_]+)/i',
							'$1<a href="https://twitter.com/$2">@$2</a>',
							$new_content);
			
			foreach ( $attachments as $media_id ) {
				if ( false == wp_attachment_is_image( $media_id ) ) {
					continue;
				}
				
				$new_content .= "\n\n" . wp_get_attachment_image( $media_id, 'full', false, array(
					'class' => 'attachment-full aligncenter'
				) );
			}
			
		}
		
		if ( '' != trim( $new_content ) ) {
			$new_data['post_content'] = $new_content;
			$new_data['ID'] = $post_id;
		}
		
	}
	
	$tweet_this = isset( $note[ 'post_on_twitter' ] ) ? true : false;
	$attachments = array();
	if ( 'publish' != $post->post_status ) {
		$tweet_this = false;
	}
	if ( true == $tweet_this ) {
		$twitter_id = get_post_meta( $post_id, 'twitter_id', true );
		$twitter_url = get_post_meta( $post_id, 'twitter_permalink', true );
		
		$append_permalink = isset( $note[ 'append_url' ] )? true : false;
		
		if ( '' == $twitter_id ) {
			// never been tweeted
			
			$remaining_text = 140;
			$link_length = 23;
			$tweet_text = '';
			
			if ( $append_permalink ) {
				$remaining_text = 140 - $link_length - 1; //url length plus space;
				$tweet_text = $tweet_text . ' ' . wp_get_shortlink( $post_id );
			}
			
			if ( 0 != count( $attachments ) ) {
				$remaining_text = 140 - $link_length - 1; //url length plus space;
				// $tweet_text = $tweet_text . ' ' . wp_get_shortlink( $post_id );
				
			}
			
			$tweet_text = $note[ 'text' ] . $tweet_text;
			
			// echo $tweet_text;
		}
		else {
			$tweet_this = false;
		}
	}
	
	if ( true == $tweet_this ) {
		$twitter_response = pwccindieweb_send_tweet( $tweet_text, $attachments );
		if ( false !== $twitter_response ) {
			
			$twitter_id = $twitter_response->id_str;
			$twitter_user = $twitter_response->user->screen_name;
			$twitter_url = "https://twitter.com/" . $twitter_user . "/status/" . $twitter_id;
			
			update_post_meta( $post_id, 'twitter_id', $twitter_id );
			update_post_meta( $post_id, 'twitter_permalink', $twitter_url );
		}
	}
	
	
	if ( isset( $new_data['ID'] ) ) {
		// remove this filter
		remove_filter ( 'save_post', 'pwccindieweb_notes_save_post', 20, 3 );
		wp_update_post( $new_data );
		// add it back
		add_filter ( 'save_post', 'pwccindieweb_notes_save_post', 20, 3 );
	}
	
	
}

add_filter ( 'save_post', 'pwccindieweb_notes_save_post', 20, 3 );


require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

function pwccindieweb_send_tweet( $text, $media = array(), $in_reply_to_status_id = '' ) {

	if ( 
		!defined( 'PWCC_TWTTR_CONSUMER_KEY' ) ||
		!defined( 'PWCC_TWTTR_CONSUMER_SECRET' ) ||
		!defined( 'PWCC_TWTTR_ACCESS_TOKEN' ) ||
		!defined( 'PWCC_TWTTR_ACCESS_SECRET' ) 
	   ) {
		// nothing can be done
		return false;
	}



	$tw_images = array();

	$consumer_key =        PWCC_TWTTR_CONSUMER_KEY;
	$consumer_secret =     PWCC_TWTTR_CONSUMER_SECRET;
	$access_token =        PWCC_TWTTR_ACCESS_TOKEN;
	$access_token_secret = PWCC_TWTTR_ACCESS_SECRET;

	$connection =  new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
	$status_update = array();


	if ( 0 < count( $media ) ) {
		
		foreach ( $media as $media_id ) {
			if ( false == wp_attachment_is_image( $media_id ) ) {
				continue;
			}
			
			
			// get image meta data
			$tweet_image_meta = wp_get_attachment_metadata( $media_id );
			$tweet_image = get_attached_file( $media_id );
			$tweet_image = dirname( $tweet_image );
			$tweet_image = untrailingslashit( $tweet_image ) . '/' . basename( $tweet_image_meta['file'] );
			
			
			$imageUpload = $connection->upload('media/upload', array('media' => $tweet_image));
			if ($connection->getLastHttpCode() == 200) {
				$tw_images[] = $imageUpload->media_id_string;
			}
		}
		
		$image_ids = implode( ',', $tw_images );
		if ( '' != $image_ids ) {
			$status_update['media_ids'] = $image_ids;
		}
	}
	
	if ( '' != $in_reply_to_status_id ) {
		$status_update['in_reply_to_status_id'] = $in_reply_to_status_id;
	}
	
	$status_update['status'] = $text;
	$reaction = $connection->post( "statuses/update", $status_update );
	if ($connection->getLastHttpCode() == 200) {
		return $reaction;
	}
	return false;
}