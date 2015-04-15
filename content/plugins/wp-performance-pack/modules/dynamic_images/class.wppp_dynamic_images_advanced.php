<?php

class WPPP_Dynamic_Images_Advanced {
	public function enqueue_scripts_and_styles ( $renderer ) {
		wp_register_script( 'jquery-ui-slider-pips', $renderer->wppp->plugin_url . 'common/js/jquery-ui-slider-pips.min.js', array ( 'jquery-ui-slider' ), false, true );
		wp_register_script( 'wppp-admin-script', $renderer->wppp->plugin_url . 'common/js/wppp_advanced.js', array ( 'jquery-ui-slider-pips' ), false, true );
		wp_enqueue_script( 'wppp-admin-script' );

		wp_register_style( 'jquery-ui-slider-pips-styles', $renderer->wppp->plugin_url . 'common/css/jquery-ui-slider-pips.css' );
		wp_register_style( 'wppp-admin-styles-jqueryui', $renderer->wppp->plugin_url . 'common/css/styles.css' );
		wp_register_style( 'wppp-admin-styles', $renderer->wppp->plugin_url . 'common/css/wppp.css' );
		wp_enqueue_style( 'jquery-ui-slider-pips-styles' );
		wp_enqueue_style( 'wppp-admin-styles-jqueryui' );
		wp_enqueue_style( 'wppp-admin-styles' );
	}

	public function add_help_tab () {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_dynimg',
			'title'	=> __( 'Improve image handling', 'wppp' ),
			'content'	=> '<p>' . __( "Using dynamic image resizing images don't get resized on upload. Instead resizing is done when an intermediate image size is first requested. This can significantly improve upload speed. Once created, the image can get saved and is then subsequently served directly.", 'wppp' ) . '</p>'
							. '<p>' . __ ( "Not saving intermediate images is only recommended for testing environments or when using caching or cdn for both front and back end.", 'wppp' ) . '</p>'
							. '<p>' . __( "Usage of EXIF thumbs for thumbnail creation improves peformance but be aware that EXIF thumbs might differ from the actual image, depending on the editing software used to create the image.", 'wppp' ) . '</p>',
		) );
	}

	public function render_options ( $renderer ) {
		wp_localize_script( 'wppp-admin-script', 'wpppData', array (
			'dynimg-quality' => $renderer->wppp->options['dynimg_quality'],
		));
		?>
		<input type="hidden" <?php $renderer->e_opt_name('dynimg_quality'); ?> value="<?php echo $renderer->wppp->options['dynimg_quality']; ?>" />

		<h3 class="title"><?php _e( 'Improve image handling', 'wppp' ); ?></h3>
		<table class="form-table" style="clear:none">
			<tr valign="top">
				<th scope="row"><?php _e( 'Dynamic image resizing', 'wppp' ); ?></th>
				<td>
					<?php $renderer->e_radio_enable( 'dynimg', 'dynamic_images', !$renderer->is_dynamic_images_available() ); ?>
					<p class="description"><?php _e( "Create intermediate images on demand, not on upload. If you deactive this option after some time of usage you might have to recreate thumbnails using a plugin like Regenerate Thumbnails.", 'wppp' ); ?></p>
					<?php $renderer->do_hint_permalinks( true ); ?>
					<br/>
					<?php $renderer->e_checkbox( 'dynimgexif', 'dynamic_images_exif_thumbs', __( 'Use EXIF thumbnail', 'wppp' ), !$renderer->is_exif_available() ); ?>
					<p class="description"><?php _e( 'If available use EXIF thumbnail to create image sizes smaller than the EXIF thumbnail. <strong>Note that, depending on image editing software, the EXIF thumbnail might differ from the actual image!</strong>', 'wppp'); ?></p>
					<br/>
					<?php $renderer->e_checkbox( 'dynimg-save', 'dynamic_images_nosave', __( "Don't save intermediate images to disc", 'wppp' ), !$renderer->is_dynamic_images_available() ); ?>
					<p class="description"><?php _e( 'Dynamically recreate intermediate images on each request.', 'wppp' ); ?></p>
					<br/>
					<?php $renderer->e_checkbox( 'dynimg-cache', 'dynamic_images_cache', __( 'Use caching', 'wppp' ), !$renderer->is_dynamic_images_available() ); ?>
					<p class="description"><?php printf( __( "Cache intermediate images using Use WordPress' Object Cache API. Only applied if %s is activated.", 'wppp' ), '"' . __( "Don't save intermediate images", 'wppp' ) . '"' ) ; ?></p>
					<?php $renderer->do_hint_caching(); ?>
					<br/>
					Serve image method:
					<select <?php $renderer->e_opt_name( 'dynimg_serve_method' ); ?> value="short_init">
						<option value="short_init" <?php echo ( $renderer->wppp->options[ 'dynimg_serve_method' ] === 'short_init' ) ? 'selected="selected"' : ''; ?>>SHORTINIT</option>
						<option value="use_themes" <?php echo ( $renderer->wppp->options[ 'dynimg_serve_method' ] === 'use_themes' ) ? 'selected="selected"' : ''; ?>>WP_USE_THEMES</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Image quality', 'wppp' );?></th>
				<td>
					<div id="dynimg-quality-slider" style="width:25em; margin-bottom:2em;"></div>
					<p class="description"><?php _e( 'Quality setting for newly created intermediate images.', 'wppp' );?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Regenerate Thumbnails integration', 'wppp' );?></th>
				<td>
					<?php $renderer->e_radio_enable( 'dynimgrthook', 'dynamic_images_rthook', !$renderer->is_regen_thumbs_available() || !$renderer->is_dynamic_images_available() ); ?>
					<p class="description"><?php _e( 'Integrate into thumbnail regeneration plugins to delete existing intermediate images.', 'wppp' ); ?></p>
					<?php $renderer->do_hint_regen_thumbs( false ); ?>
					<br/>
					<?php $renderer->e_checkbox( 'dynimg-rtforce', 'dynamic_images_rthook_force', __( 'Force delete of all potential thumbnails', 'wppp' ), !$renderer->is_regen_thumbs_available() || !$renderer->is_dynamic_images_available() ); ?>
					<p class="description"><?php _e( 'Delete all potential intermediate images (i.e. those matching the pattern "<em>imagefilename-*x*.ext</em>") while regenerating. <strong>Use with care as this option might delete files that are no thumbnails!</strong>', 'wppp' );?></p>
				</td>
			</tr>
		</table>

		<hr/>
		<?php
	}
}

?>