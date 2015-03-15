<?php
defined('ABSPATH') or die("No script kiddies please!");
/**
 * @link              https://github.com/ResponsiveImagesCG/wp-tevko-responsive-images
 * @since             2.0.0
 * @package           http://www.smashingmagazine.com/2015/02/24/ricg-responsive-images-for-wordpress/
 *
 * @wordpress-plugin
 * Plugin Name:       RICG Responsive Images
 * Plugin URI:        http://www.smashingmagazine.com/2015/02/24/ricg-responsive-images-for-wordpress/
 * Description:       Bringing automatic default responsive images to wordpress
 * Version:           2.1.1
 * Author:            The RICG
 * Author URI:        http://responsiveimages.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


/**
 * Enqueue bundled version of the Picturefill library
 */
function tevkori_get_picturefill() {
	wp_enqueue_script( 'picturefill', plugins_url( 'js/picturefill.js', __FILE__ ), array(), '2.2.0', true );
}
add_action( 'wp_enqueue_scripts', 'tevkori_get_picturefill' );

/**
 * Get an array of image sources candidates for use in a 'srcset' attribute.
 *
 * @param int $id 			Image attacment ID.
 * @param string $size	Optional. Name of image size. Default value: 'thumbnail'.
 * @return array|bool 	An array of of srcset values or false.
 */
function tevkori_get_srcset_array( $id, $size ) {
	$arr = array();

	// See which image is being returned and bail if none is found
	if ( ! $image = wp_get_attachment_image_src( $id, $size ) ) {
		return false;
	};

	// break image data into url, width, and height
	list( $img_url, $img_width, $img_height ) = $image;

	// image meta
	$image_meta = wp_get_attachment_metadata( $id );

	// default sizes
	$default_sizes = $image_meta['sizes'];

	// add full size to the default_sizes array
	$default_sizes['full'] = array(
		'width' 	=> $image_meta['width'],
		'height'	=> $image_meta['height'],
		'file'		=> $image_meta['file']
	);

	// Remove any hard-crops
	foreach ( $default_sizes as $key => $image_size ) {

		// calculate the height we would expect if this is a soft crop given the size width
		$soft_height = (int) round( $image_size['width'] * $img_height / $img_width );

		// If image height varies more than 1px over the expected, throw it out.
		if ( $image_size['height'] <= $soft_height - 2 || $image_size['height'] >= $soft_height + 2  ) {
			unset( $default_sizes[$key] );
		}
	}

	// No sizes? Checkout early
	if( ! $default_sizes )
	return false;

	// Loop through each size we know should exist
	foreach( $default_sizes as $key => $size ) {

		// Reference the size directly by it's pixel dimension
		$image_src = wp_get_attachment_image_src( $id, $key );
		$arr[] = $image_src[0] . ' ' . $size['width'] .'w';
	}

	return $arr;
}

/**
 * Create a 'srcset' attribute.
 *
 * @param int $id 			Image attacment ID.
 * @param string $size	Optional. Name of image size. Default value: 'thumbnail'.
 * @return string|bool 	A full 'srcset' string or false.
 */
function tevkori_get_srcset_string( $id, $size ) {
	$srcset_array = tevkori_get_srcset_array( $id, $size );
	if ( empty( $srcset_array ) ) {
		return false;
	}
	return 'srcset="' . implode( ', ', $srcset_array ) . '"';
}

/**
 * Create a 'srcset' attribute.
 *
 * @deprecated 2.1.0
 * @deprecated Use tevkori_get_srcset_string
 * @see tevkori_get_srcset_string
 *
 * @param int $id 			Image attacment ID.
 * @return string|bool 	A full 'srcset' string or false.
 */
function tevkori_get_src_sizes($id, $size) {
	return tevkori_get_srcset_string( $id, $size );
}

/**
 * Filter for extending image tag to include srcset attribute
 *
 * @see 'images_send_to_editor'
 * @return string HTML for image.
 */
function tevkori_extend_image_tag( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
	add_filter( 'editor_max_image_size', 'tevkori_editor_image_size' );
	$srcset = tevkori_get_srcset_string( $id, $size );
	remove_filter( 'editor_max_image_size', 'tevkori_editor_image_size' );
	$html = preg_replace( '/(src\s*=\s*"(.+?)")/', '$1' . ' ' . $srcset, $html );
	return $html;
}
add_filter( 'image_send_to_editor', 'tevkori_extend_image_tag', 0, 8 );

/**
 * Filter to add srcset attributes to post_thumbnails
 *
 * @see 'post_thumbnail_html'
 * @return string HTML for image.
 */
function tevkori_filter_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	// if the HTML is empty, short circuit
	if ( '' === $html ) {
		return;
	}

	$srcset = tevkori_get_srcset_string( $post_thumbnail_id, $size );
	$html = preg_replace( '/(src\s*=\s*"(.+?)")/', '$1' . ' ' . $srcset, $html );
	return $html;
}
add_filter( 'post_thumbnail_html', 'tevkori_filter_post_thumbnail_html', 0, 5);

/**
 * Disable the editor size constraint applied for images in TinyMCE.
 *
 * @param  array $max_image_size An array with the width as the first element, and the height as the second element.
 * @return array A width & height array so large it shouldn't constrain reasonable images.
 */
function tevkori_editor_image_size( $max_image_size ){
	return array( 99999, 99999 );
}

/**
 * Load admin scripts
 *
 * @param string $hook Admin page file name.
 */
function tevkori_load_admin_scripts( $hook ) {
	if ($hook == 'post.php' || $hook == 'post-new.php') {
		wp_enqueue_script( 'wp-tevko-responsive-images', plugin_dir_url( __FILE__ ) . 'js/wp-tevko-responsive-images.js', array('wp-backbone'), '2.0.0', true );
	}
}
add_action( 'admin_enqueue_scripts', 'tevkori_load_admin_scripts' );
