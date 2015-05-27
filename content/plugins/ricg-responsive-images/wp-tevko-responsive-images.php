<?php
defined( 'ABSPATH' ) or die( "No script kiddies please!" );

/**
 * @link              https://github.com/ResponsiveImagesCG/wp-tevko-responsive-images
 * @since             2.0.0
 * @package           http://www.smashingmagazine.com/2015/02/24/ricg-responsive-images-for-wordpress/
 *
 * @wordpress-plugin
 * Plugin Name:       RICG Responsive Images
 * Plugin URI:        http://www.smashingmagazine.com/2015/02/24/ricg-responsive-images-for-wordpress/
 * Description:       Bringing automatic default responsive images to wordpress
 * Version:           2.3.1
 * Author:            The RICG
 * Author URI:        http://responsiveimages.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// List includes
require_once plugin_dir_path( __FILE__ ) . 'class-respimg.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wp-image-editor-respimg.php';

/**
 * Enqueue bundled version of the Picturefill library.
 */
function tevkori_get_picturefill() {
	wp_enqueue_script( 'picturefill', plugins_url( 'js/picturefill.min.js', __FILE__ ), array(), '2.3.1', true );
}
add_action( 'wp_enqueue_scripts', 'tevkori_get_picturefill' );

/**
 * Return a source size attribute for an image from an array of values.
 *
 * @since 2.2.0
 *
 * @param int    $id   Image attachment ID.
 * @param string $size Optional. Name of image size. Default value: 'thumbnail'.
 * @param array  $args {
 *     Optional. Arguments to retrieve posts.
 *
 *     @type array|string $sizes An array or string containing of size information.
 * }
 * @return string|bool A valid source size value for use in a 'sizes' attribute or false.
 */
function tevkori_get_sizes( $id, $size = 'thumbnail', $args = null ) {

	// See which image is being returned and bail if none is found.
	if ( ! $img = image_downsize( $id, $size ) ) {
		return false;
	};

	// Get the image width.
	$img_width = $img[1] . 'px';

	// Set up our default values.
	$defaults = array(
		'sizes' => array(
			array(
				'size_value' => '100vw',
				'mq_value'   => $img_width,
				'mq_name'    => 'max-width'
			),
			array(
				'size_value' => $img_width
			),
		)
	);

	$args = wp_parse_args( $args, $defaults );

	// If sizes is passed as a string, just use the string.
	if ( is_string( $args['sizes'] ) ) {
		$size_list = $args['sizes'];

	// Otherwise, breakdown the array and build a sizes string.
	} elseif ( is_array( $args['sizes'] ) ) {

		$size_list = '';

		foreach ( $args['sizes'] as $size ) {

			// Use 100vw as the size value unless something else is specified.
			$size_value = ( $size['size_value'] ) ? $size['size_value'] : '100vw';

			// If a media length is specified, build the media query.
			if ( ! empty( $size['mq_value'] ) ) {

				$media_length = $size['mq_value'];

				// Use max-width as the media condition unless min-width is specified.
				$media_condition = ( ! empty( $size['mq_name'] ) ) ? $size['mq_name'] : 'max-width';

				// If a media_length was set, create the media query.
				$media_query = '(' . $media_condition . ": " . $media_length . ') ';

			} else {

				// If not meda length was set, $media_query is blank.
				$media_query = '';
			}

			// Add to the source size list string.
			$size_list .= $media_query . $size_value . ', ';
		}

		// Remove the trailing comma and space from the end of the string.
		$size_list = substr( $size_list, 0, -2 );
	}

	// If $size_list is defined set the string, otherwise set false.
	$size_string = ( $size_list ) ? $size_list : false;

	return $size_string;
}

/**
 * Return a source size list for an image from an array of values.
 *
 * @since 2.2.0
 *
 * @param int    $id   Image attachment ID.
 * @param string $size Optional. Name of image size. Default value: 'thumbnail'.
 * @param array  $args {
 *     Optional. Arguments to retrieve posts.
 *
 *     @type array|string $sizes An array or string containing of size information.
 * }
 * @return string|bool A valid source size list as a 'sizes' attribute or false.
 */
function tevkori_get_sizes_string( $id, $size = 'thumbnail', $args = null ) {
	$sizes = tevkori_get_sizes( $id, $size, $args );
	$sizes_string = $sizes ? 'sizes="' . $sizes . '"' : false;

	return $sizes_string;
}

/**
 * Get an array of image sources candidates for use in a 'srcset' attribute.
 *
 * @param int    $id   Image attachment ID.
 * @param string $size Optional. Name of image size. Default value: 'thumbnail'.
 * @return array|bool  An array of of srcset values or false.
 */
function tevkori_get_srcset_array( $id, $size = 'thumbnail' ) {
	$arr = array();

	// See which image is being returned and bail if none is found.
	if ( ! $img = wp_get_attachment_image_src( $id, $size ) ) {
		return false;
	};

	// Break image data into url, width, and height.
	list( $img_url, $img_width, $img_height ) = $img;

	// If we have no width to work with, we should bail (see issue #118).
	if ( 0 == $img_width ) {
		return false;
	}

	// Get the image meta data.
	$img_meta = wp_get_attachment_metadata( $id );

	// Build an array with image sizes.
	$img_sizes = $img_meta['sizes'];

	// Add full size to the img_sizes array.
	$img_sizes['full'] = array(
		'width'  => $img_meta['width'],
		'height' => $img_meta['height'],
		'file'   => $img_meta['file'] 
	);
	if ( strrpos( $img_meta['file'], '/' ) !== false ) {
		$img_sizes['full']['file'] = substr( $img_meta['file'], strrpos( $img_meta['file'], '/' ) + 1 );
	}

	// Get the image base url.
	$img_base_url = substr( $img_url, 0, strrpos( $img_url, '/' ) + 1 );

	// Calculate the image aspect ratio.
	$img_ratio = $img_height / $img_width;

	// Only use sizes with same aspect ratio.
	foreach ( $img_sizes as $img_size => $img ) {

		// Calculate the height we would expect if the image size has the same aspect ratio.
		$expected_height = (int) round( $img['width'] * $img_ratio );

		// If image height doesn't varies more than 2px over the expected, use it.
		if ( $img['height'] >= $expected_height - 2 && $img['height'] <= $expected_height + 2  ) {
			$arr[] = $img_base_url . $img['file'] . ' ' . $img['width'] .'w';
		}
	}

	if ( empty( $arr ) ) {
		return false;
	}

	return $arr;
}

/**
 * Get the value for the 'srcset' attribute.
 *
 * @since 2.3.0
 *
 * @param int    $id   Image attachment ID.
 * @param string $size Optional. Name of image size. Default value: 'thumbnail'.
 * @return string|bool A 'srcset' value string or false.
 */
function tevkori_get_srcset( $id, $size = 'thumbnail' ) {
	$srcset_array = tevkori_get_srcset_array( $id, $size );

	if ( empty( $srcset_array ) ) {
		return false;
	}

	return implode( ', ', $srcset_array );
}

/**
 * Create a 'srcset' attribute.
 *
 * @since 2.1.0
 *
 * @param int    $id   Image attachment ID.
 * @param string $size Optional. Name of image size. Default value: 'thumbnail'.
 * @return string|bool A full 'srcset' string or false.
 */
function tevkori_get_srcset_string( $id, $size = 'thumbnail' ) {
	$srcset_value = tevkori_get_srcset( $id, $size );

	if ( empty( $srcset_value ) ) {
		return false;
	}

	return 'srcset="' . $srcset_value . '"';
}

/**
 * Create a 'srcset' attribute.
 *
 * @deprecated 2.1.0
 * @deprecated Use tevkori_get_srcset_string instead.
 * @see tevkori_get_srcset_string
 *
 * @param int $id Image attachment ID.
 * @return string|bool A full 'srcset' string or false.
 */
function tevkori_get_src_sizes( $id, $size = 'thumbnail' ) {
	return tevkori_get_srcset_string( $id, $size );
}

/**
 * Filter for extending image tag to include srcset attribute.
 *
 * @see images_send_to_editor
 * @return string HTML for image.
 */
function tevkori_extend_image_tag( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
	add_filter( 'editor_max_image_size', 'tevkori_editor_image_size' );

	$sizes = tevkori_get_sizes( $id, $size );

	// Build the data-sizes attribute if sizes were returned.
	if ( $sizes ) {
		$sizes = 'data-sizes="' . $sizes . '"';
	}

	// Build the srcset attribute.
	$srcset = tevkori_get_srcset_string( $id, $size );

	remove_filter( 'editor_max_image_size', 'tevkori_editor_image_size' );

	$html = preg_replace( '/(src\s*=\s*"(.+?)")/', '$1 ' . $sizes . ' ' . $srcset, $html );

	return $html;
}
add_filter( 'image_send_to_editor', 'tevkori_extend_image_tag', 0, 8 );

/**
 * Filter to add srcset and sizes attributes to post thumbnails and gallery images.
 *
 * @see wp_get_attachment_image_attributes
 * @return array Attributes for image.
 */
function tevkori_filter_attachment_image_attributes( $attr, $attachment, $size ) {
	$attachment_id = $attachment->ID;

	if ( ! isset( $attr['sizes'] ) ) {
		$sizes = tevkori_get_sizes( $attachment_id, $size );

		// Build the sizes attribute if sizes were returned.
		if ( $sizes ) {
			$attr['sizes'] = $sizes;
		}
	}

	if ( ! isset( $attr['srcset'] ) ) {
		$srcset = tevkori_get_srcset( $attachment_id, $size );
		$attr['srcset'] = $srcset;
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'tevkori_filter_attachment_image_attributes', 0, 3 );

/**
 * Disable the editor size constraint applied for images in TinyMCE.
 *
 * @param array $max_image_size An array with the width as the first element, and the height as the second element.
 * @return array A width & height array so large it shouldn't constrain reasonable images.
 */
function tevkori_editor_image_size( $max_image_size ) {
	return array( 99999, 99999 );
}

/**
 * Load admin scripts.
 *
 * @param string $hook Admin page file name.
 */
function tevkori_load_admin_scripts( $hook ) {
	if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
		wp_enqueue_script( 'wp-tevko-responsive-images', plugin_dir_url( __FILE__ ) . 'js/wp-tevko-responsive-images.js', array( 'wp-backbone' ), '2.0.0', true );
	}
}
add_action( 'admin_enqueue_scripts', 'tevkori_load_admin_scripts' );


/**
 * Filter for the_content to replace data-size attributes with size attributes.
 *
 * @since 2.2.0
 *
 * @param string $content The raw post content to be filtered.
 */
function tevkori_filter_content_sizes( $content ) {
	$images = '/(<img\s.*?)data-sizes="([^"]+)"/i';
	$sizes = '${2}';
	$content = preg_replace( $images, '${1}sizes="' . $sizes . '"', $content );

	return $content;
}
add_filter( 'the_content', 'tevkori_filter_content_sizes' );


/**
 * Filter to add php-respimg as an image editor.
 *
 * @since 2.3.0
 *
 * @return array Editors.
 **/
function tevkori_wp_image_editors( $editors ) {
	if ( current_theme_supports( 'advanced-image-compression' ) ) {
		array_unshift( $editors, 'WP_Image_Editor_Respimg' );
	}

	return $editors;
}
add_filter( 'wp_image_editors', 'tevkori_wp_image_editors' );

/**
 * Ajax handler for updating the srcset when an image is changed in the editor.
 *
 * @since 2.3.0
 *
 * @return string A sourcest value.
 */
function tevkori_ajax_srcset() {

	// Bail early if no post ID is passed.
	if ( ! $postID = $_POST['postID'] ) {
		return;
	};

	// Grab the image size being passed from the AJAX request.
	$size = $_POST['size'];

	// Get the srcset value for our image.
	$srcset = tevkori_get_srcset( $postID, $size );

	// For AJAX requests, we echo the result and then die.
	wp_send_json( $srcset );
}
add_action( 'wp_ajax_tevkori_ajax_srcset', 'tevkori_ajax_srcset' );
