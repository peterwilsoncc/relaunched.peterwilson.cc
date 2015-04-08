<?php

if ( class_exists( 'EWWWIO_GD_Editor' ) ) {
	class WP_Image_Editor_GD_EXIF_Base extends EWWWIO_GD_Editor {}
} else {
	require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
	require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
	class WP_Image_Editor_GD_EXIF_Base extends WP_Image_Editor_GD {}
}

class WP_Image_Editor_GD_EXIF extends WP_Image_Editor_GD_EXIF_Base {
	public function load() {
		if ( $this->image )
			return true;

		if ( ! is_file( $this->file ) && ! preg_match( '|^https?://|', $this->file ) )
			return new WP_Error( 'error_loading_image', __('File doesn&#8217;t exist?'), $this->file );

		/**
		 * Filter the memory limit allocated for image manipulation.
		 *
		 * @since 3.5.0
		 *
		 * @param int|string $limit Maximum memory limit to allocate for images. Default WP_MAX_MEMORY_LIMIT.
		 *                          Accepts an integer (bytes), or a shorthand string notation, such as '256M'.
		 */
		// Set artificially high because GD uses uncompressed images in memory
		@ini_set( 'memory_limit', apply_filters( 'image_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		$this->image = @imagecreatefromstring( exif_thumbnail( $this->file, $thumb_w, $thumb_h, $thumb_type ) );

		if ( ! is_resource( $this->image ) )
			return new WP_Error( 'invalid_image', __('Error reading thumb from EXIF.'), $this->file );

		if ( function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) ) {
			imagealphablending( $this->image, false );
			imagesavealpha( $this->image, true );
		}

		$this->update_size( $thumb_w, $thumb_h );
		$this->mime_type = image_type_to_mime_type($thumb_type);

		return $this->set_quality( $this->quality );
	}
}

?>