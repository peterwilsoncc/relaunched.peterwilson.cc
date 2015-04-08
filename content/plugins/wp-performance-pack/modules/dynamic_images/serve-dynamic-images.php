<?php
/**
 * Serve intermediate images on demand. Is called via mod_rewrite rule.
 * Based on Dynamic Image Resizer (http://ottopress.com) by Samuel Wood (http://ottodestruct.com).
 *
 * @author Björn Ahrens
 * @package WP Performance Pack
 * @since 1.1
 */

if ( preg_match( '/(.*)-([0-9]+)x([0-9]+)?\.(jpeg|jpg|png|gif)/i', $_SERVER['REQUEST_URI'], $matches ) ) {
	// should always match as this file is called using mod_rewrite using the exact same regexp

	define( 'SHORTINIT', true );

	// search and load wp-load.php
	$folder = dirname( __FILE__ );
	while ( $folder != dirname( $folder ) ) {
		if ( file_exists( $folder . '/wp-load.php' ) ) {
			break;
		} else {
			$folder = dirname( $folder );
		}
	}
	require( $folder . '/wp-load.php' ); // will fail if while loop didn't find wp-load.php

	include( sprintf( "%s/wp-performance-pack.php", dirname( dirname( dirname( __FILE__ ) ) ) ) );
	global $wp_performance_pack;
	$wppp = $wp_performance_pack;
	$wppp->load_options();
	if ( $wppp->options['dynamic_images'] !== true ) {
		header('HTTP/1.0 404 Not Found');
		echo 'WPPP dynamic images deactivated for this site';
		exit;
	}


	// dummy add_shortcode required for media.php - we don't need any shortcodes so don't load that file and use a dummy instead
	function add_shortcode() {}
	require( ABSPATH . 'wp-includes/media.php' );

	require( ABSPATH . 'wp-includes/formatting.php' );
	// formatting.php is more than 120kb big - too big to include for just some small functions, so use copies of needed functions
	// untrailingslashit from wp-includes/formatting.php. is required for get_option
	// trailingslashit and sanitize_key are needed for meta data retrieval
	/*function untrailingslashit($string) {
		return rtrim($string, '/');
	}
	function trailingslashit($string) {
		return untrailingslashit($string) . '/';
	}
	function sanitize_key( $key ) {
		$raw_key = $key;
		$key = strtolower( $key );
		$key = preg_replace( '/[^a-z0-9_\-]/', '', $key );
		return apply_filters( 'sanitize_key', $key, $raw_key );
	}*/

	if ( ! defined( 'WP_CONTENT_URL' ) ) {
		define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	}

	// required for home_url
	require( ABSPATH . 'wp-includes/link-template.php' );

	// required for wp_get_attachment_metadata
	require( ABSPATH . 'wp-includes/post.php' );
	require( ABSPATH . 'wp-includes/meta.php' );

	if ( is_multisite() ) {
		require ( ABSPATH . 'wp-includes/ms-functions.php' );
	}

	/*
	 * "Normalize" URLs to make them comparable. Partial copy from url_to_postid in rewrite.php
	 */
	function normalize_url ( $url ) {
		if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
			$url = str_replace('://', '://www.', $url);

		if ( false === strpos(home_url(), '://www.') )
			$url = str_replace('://www.', '://', $url);

		if ( false !== strpos( trailingslashit( $url ), home_url( '/' ) ) ) {
			$url = str_replace(home_url(), '', $url);
		} else {
			$home_path = parse_url( home_url( '/' ) );
			$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
			$url = preg_replace( sprintf( '#^%s#', preg_quote( $home_path ) ), '', trailingslashit( $url ) );
		}

		$url = trim($url, '/');
 
		return( $url );
	}

	/*
	 * Get attachment ID for image url
	 * Source: http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 */
	function pn_get_attachment_id_from_url( $attachment_url = '' ) {
		global $wpdb;
		$attachment_id = false;
		if ( '' !== $attachment_url ) {
			$upload_dir_paths = wp_upload_dir();
			$baseurl = normalize_url( $upload_dir_paths['baseurl'] );
			$attachment_url = normalize_url( $attachment_url );
 
			if ( false !== strpos( $attachment_url, $baseurl ) ) {
				$attachment_url = str_replace( $baseurl . '/', '', $attachment_url );
				$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
			}
		}
		return $attachment_id;
	}

	$filename 		= urldecode( $matches[1] . '.' . $matches[4] );
	$uploads_dir 	= wp_upload_dir();
	$temp 			= parse_url( $uploads_dir['baseurl'] );
	$upload_path 	= $temp['path'];
	$findfile 		= str_replace( $upload_path, '', $filename );
	$basefile 		= $uploads_dir['basedir'] . $findfile;

	if ( file_exists( $basefile ) ) {
		$width 			= $matches[2];
		$height 		= $matches[3];
		$crop 			= false;
		$suffix 		= $width . 'x' . $height;

		if ( $wppp->options['dynamic_images_cache'] && ( false !== ( $data = wp_cache_get ( $basefile . $suffix, 'wppp' ) ) ) ) {
			header( 'Content-Type: ' . $data['mimetype'] );
			echo $data['data'];
			exit;
		}

		// get defined image sizes - no way to get them all here, because
		// this would require to initialize the template and all plugins
		// that's why they are stored as an option
		$sizes = get_option( 'wppp_dynimg_sizes' );	

		// test if image is an attachment and get its meta data
		$attachment_id = pn_get_attachment_id_from_url( $filename );
		if ( $attachment_id === false ) {
			header('HTTP/1.0 404 Not Found');
			echo 'Unknown attachment';
			exit;
		}
		$attachment_meta = wp_get_attachment_metadata( $attachment_id );
		if ( !$attachment_meta ) {
			header('HTTP/1.0 404 Not Found');
			echo 'No attachment meta data';
			exit;
		}

		// search meta data for matching size
		// without this test any image request would create intermediate images thus potentially filling up server space
		$found = false;
		foreach ( $attachment_meta['sizes'] as $size => $size_data ) {
			$found = ( $size_data['width'] == $width ) && ( $size_data['height'] == $height );
			if ( $found ) {
				$found = $size;
				if ( isset( $sizes[$size] ) ) {
					$crop = $sizes[$size]['crop'];
				} // TODO: if size isn't in $sizes, then it's an "old" size and shouldn't be used any more... delete it?
				break;
			}
		}
		if ( false === $found ) {
			// Size not found in attachment meta data. Maybe meta data isn't up to date.
			$base_size = getimagesize( $basefile);
			foreach ( $sizes as $size => $size_data ) {
				if ( !isset( $attachment_meta['sizes'][$size] ) ) {
					// only check if size isn't in meta data
					$new_size = image_resize_dimensions( $base_size[0], $base_size[1], $size_data['width'], $size_data['height'], $size_data['crop'] );
					$found = ( $new_size[4] == $width ) && ( $new_size[5] == $height );
					if ( $found ) {
						$found = $size;
						$crop = $size_data['crop'];
						break;
					}
				}
			}
			if ( false === $found ) {
				header('HTTP/1.0 404 Not Found');
				echo 'Unknown image size';
				exit;
			}
		}

		// the requested image size could be found so load and resize it

		if ( !$wppp->options['dynamic_images_nosave'] ) {
			// test for EWWW Image optimizer
			$plugins = get_option( 'active_plugins' );
			$ewww = false;
			if ( is_array( $plugins ) ) {
				if ( in_array( 'ewww-image-optimizer/ewww-image-optimizer.php', $plugins ) ) {
					$ewww = '/ewww-image-optimizer/ewww-image-optimizer.php';
				} else if ( in_array( 'ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php', $plugins ) ) {
					$ewww = '/ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php';
				}
			}
		}

		if ( !$wppp->options['dynamic_images_nosave'] && $ewww !== false ) {
			// load EWWW IO 
			require_once( ABSPATH . 'wp-includes/default-constants.php' ); // it seems that sometimes something else already includes this, so do require_once to not get redeclaration errors
			wp_plugin_directory_constants();
			wp_load_translations_early();
			$GLOBALS['wp_plugin_paths'] = array();
			wp_register_plugin_realpath( dirname( dirname( __FILE__ ) ) . $ewww );
			include ( dirname( dirname( __FILE__ ) ) . $ewww );
		} else {
			// dummy definition, required for ?
			function __( $text ) { return $text; };
		}

		if ( $wppp->options['dynamic_images_exif_thumbs'] 
				&& $width <= 320 && $height <= 320 //( $found == 'thumbnail' )
				&& extension_loaded( 'exif' )
				&& function_exists( 'exif_thumbnail' )
				&& function_exists( 'imagecreatefromstring' ) ) {
			include( sprintf( "%s/class.wp-image-editor-gd-exif.php", dirname( __FILE__ ) ) );
			$image = new WP_Image_Editor_GD_EXIF( $basefile );
			if ( is_wp_error( $image->load() ) ) {
				// exif load failed (maybe no exif data or no thumb), so load full image
				$image = wp_get_image_editor( $basefile );
			} else {
				// test if EXIF thumb is big enough
				$exif_size = $image->get_size();
				if ( $width > $exif_size['width'] || $height > $exif_size['height'] ) {
					// EXIF thumb too small, load full image
					$image = wp_get_image_editor( $basefile );
				}
			}
		} else {
			$image = wp_get_image_editor( $basefile );
		}

		if ( ! is_wp_error( $image ) ) {
			$image->set_quality( $wppp->options['dynimg_quality'] );
			$image->resize( $width, $height, $crop );
			if ( !$wppp->options['dynamic_images_nosave'] ) {
				$image->save( $image->generate_filename( $suffix ) );
			} else {
				if ( $wppp->options['dynamic_images_cache'] ) {
					$data = array();
					// get image mime type - WP_Image_Editor has functions for this, but they are all protected :(
					// so use the code from get_mime_type directly
					$mime_types = wp_get_mime_types();
					$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
					$extensions = array_keys( $mime_types );
					foreach( $extensions as $_extension ) {
						if ( preg_match( "/{$extension}/i", $_extension ) ) {
							$data['mimetype'] = $mime_types[$_extension];
						}
					}
					ob_start();
					$image->stream();
					$data['data'] = ob_get_contents(); // read from buffer
					ob_end_clean();
					wp_cache_set( $basefile . $suffix, $data, 'wppp', HOUR_IN_SECONDS );
				}

				// if intermediate images are not saved, explicitly set cache headers for browser caching
				header( 'Pragma: public' );
				header( 'Cache-Control: max-age=' . 24 * HOUR_IN_SECONDS );
				header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 24 * HOUR_IN_SECONDS) . ' GMT' );
			}
			$image->stream();
			exit;
		} else {
			header('HTTP/1.0 500 Internal Server Error');
			echo 'Could not load image';
		}
	} else {
		header('HTTP/1.0 404 Not Found');
		echo 'Base file not found';
	}
}

// return 404 else
header('HTTP/1.0 404 Not Found');
echo 'Unkown error';
?>