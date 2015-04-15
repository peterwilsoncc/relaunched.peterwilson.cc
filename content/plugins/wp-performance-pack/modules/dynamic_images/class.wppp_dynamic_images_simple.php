<?php

class WPPP_Dynamic_Images_Simple {
	public function enqueue_scripts_and_styles ( $renderer ) {
		wp_register_script( 'jquery-ui-slider-pips', $renderer->wppp->plugin_url . 'common/js/jquery-ui-slider-pips.min.js', array ( 'jquery-ui-slider' ), false, true );
		wp_register_script( 'wppp-admin-script', $renderer->wppp->plugin_url . 'common/js/wppp_simple.js', array ( 'jquery-ui-slider-pips' ), false, true );
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
			'id'	=> 'wppp_simple_dynimg',
			'title'	=> __( 'Improve image handling', 'wppp' ),
			'content'	=> '<p>' . __( "Improve image upload speed and web space usage using this setting. Creation of different image sizes will be delayed upon the first access to the respective image. <em>Fast</em> uses EXIF thumbs to create small image sizes, which might cause issues as EXIF thumbs and actual images might differ (depending on used image editing software). Use <em>Webspace</em> only for testing environments or if you are really low on webspace, as this option will slow down your blog because intermediate images don't get saved to disc.", 'wppp' ) . '</p>',
		) );
	}

	function dynimg_detect_current_setting ( $renderer ) {
		// off - all options turend off
		if ( !$renderer->wppp->options['dynamic_images'] ) {
			return 0;
		}
		
		// stable - dynimg enabled, image quality 80%
		if ( $renderer->wppp->options['dynamic_images']
			&& $renderer->wppp->options['dynimg_quality'] == 80
			&& !$renderer->wppp->options['dynamic_images_nosave']
			&& !$renderer->wppp->options['dynamic_images_rthook']
			&& !$renderer->wppp->options['dynamic_images_exif_thumbs'] ) {
			return 1;
		}

		// speed - same as stable, including exif
		if ( $renderer->wppp->options['dynamic_images']
			&& $renderer->wppp->options['dynimg_quality'] == 80
			&& !$renderer->wppp->options['dynamic_images_nosave']
			&& !$renderer->wppp->options['dynamic_images_rthook']
			&& $renderer->wppp->options['dynamic_images_exif_thumbs'] ) {
			return 2;
		}

		// webspace - same as speed, including no_save, cache and regen-integration
		if ( $renderer->wppp->options['dynamic_images']
			&& $renderer->wppp->options['dynimg_quality'] == 80
			&& $renderer->wppp->options['dynamic_images_nosave']
			&& ( $renderer->wppp->options['dynamic_images_cache'] || !$renderer->is_object_cache_installed() )
			&& ( $renderer->wppp->options['dynamic_images_rthook'] || !$renderer->is_regen_thumbs_available() )
			&& !$renderer->wppp->options['dynamic_images_rthook_force']
			&& ( $renderer->wppp->options['dynamic_images_exif_thumbs'] || !$renderer->is_exif_available() ) ) {
			return 3;
		}

		// else custom
		return 4;
	}

	function dynimg_output_active_settings ( $renderer, $level ) {
		echo '<ul>';
		if ( $level == 0 ) {
			// Off
			$renderer->e_li_error( __( 'All improved image handling settings disabled.', 'wppp' ) );
		} else {
			if ( !$renderer->is_dynamic_images_available() ) {
				$renderer->e_li_error( __( 'Pretty Permalinks must be enabled for improved image handling', 'wppp' ) );
			} else {
				$renderer->e_li_check( __( 'Dynamic image resizing enabled', 'wppp' ) );
				if ( $level < 4 ) {
					$renderer->e_li_check( __( 'Intermediate image quality set to 80%', 'wppp' ) );
					if ( $level > 1 ) {
						if ( $renderer->is_exif_available() ) {
							$renderer->e_li_check( __( 'Use EXIF thumbnails if available.', 'wppp' ) );
						} else {
							$renderer->e_li_error( __( 'EXIF extension not installed', 'wppp' ) );
						}
						if ( $level > 2 ) {
							$renderer->e_li_check( __( "Don't save intermediate images", 'wppp' ) );
							if ( $renderer->is_object_cache_installed() ) {
								$renderer->e_li_check( __( 'Use caching', 'wppp' ) );
							} else {
								$renderer->e_li_error( __( 'No persistent object cache installed.', 'wppp' ) );
							}
							if ( $renderer->is_regen_thumbs_available() ) {
								$renderer->e_li_check( __( 'Regenerate Thumbnails integration', 'wppp' ) );
							} else {
								$renderer->e_li_error( __( 'No Regenerate Thumbnails plugin installed', 'wppp' ) );
							}
						}
					}
				} else {
					// custom
					if ( $renderer->wppp->options['dynamic_images_nosave'] ) {
						$renderer->e_li_check( __( "Don't save intermediate images", 'wppp' ) );
					}
					if ( $renderer->wppp->options['dynamic_images_cache'] ) {
						$renderer->e_li_check( __( 'Use caching', 'wppp' ) );
					}
					if ( $renderer->wppp->options['dynamic_images_rthook'] ) {
						$renderer->e_li_check( __( 'Regenerate Thumbnails integration', 'wppp' ) );
						if ( $renderer->wppp->options['dynamic_images_rthook_force'] ) {
							$renderer->e_li_check( __( 'Force delte all on Regenerate Thumbnails', 'wppp' ) );
						}
					}
					if ( $renderer->wppp->options['dynamic_images_exif_thumbs'] ) {
						$renderer->e_li_check( __( 'Use EXIF thumbnails if available.', 'wppp' ) );
					}
					$renderer->e_li_check( sprintf( __( 'Intermediate image quality set to %s%%', 'wppp' ), $renderer->wppp->options['dynimg_quality'] ) );
					if ( $renderer->wppp->options['dyn_links'] ) {
						$renderer->e_li_check( __( 'Dynamic image links', 'wppp' ) );
					}
				}
			}
		}
		echo '</ul>';
	}

	public function render_options ( $renderer ) {
		$option_keys = array_keys( $renderer->wppp->get_options_default() );
		unset ( $option_keys [ array_search( 'advanced_admin_view', $option_keys ) ] );
		wp_localize_script( 'wppp-admin-script', 'wpppData', array( json_encode( array(
			'dynimg' => array( 'current' => $this->dynimg_detect_current_setting( $renderer ),
								// sequence: stable, speed, webspace, current, cdn_off, cdn_stable, cdn_speed, current
								'settings' => array(	'dynamic_images' => array(	$renderer->is_dynamic_images_available(),
																					$renderer->is_dynamic_images_available(),
																					$renderer->is_dynamic_images_available(),
																					$renderer->wppp->options['dynamic_images'] ),
														'dynamic_images_nosave' => array(	false,
																							false,
																							true,
																							$renderer->wppp->options['dynamic_images_nosave'] ),
														'dynamic_images_cache' => array(	false,
																							false,
																							$renderer->is_object_cache_installed(),
																							$renderer->wppp->options['dynamic_images_cache'] ),
														'dynamic_images_rthook' => array(	false,
																							false,
																							$renderer->is_regen_thumbs_available(),
																							$renderer->wppp->options['dynamic_images_rthook'] ),
														'dynamic_images_rthook_force' => array(	false,
																								false,
																								false,
																								$renderer->wppp->options['dynamic_images_rthook'] ),
														'dynamic_images_exif_thumbs' => array(	false,
																								$renderer->is_exif_available(),
																								$renderer->is_exif_available(),
																								$renderer->wppp->options['dynamic_images_exif_thumbs'] ),
														'dynimg_quality' => array(	80,
																					80,
																					80,
																					$renderer->wppp->options['dynimg_quality'] ),
								),
			),

			'labels' => array( 'Off' => __( 'Off', 'wppp' ),
								'Stable' => __( 'Stable', 'wppp' ),
								'Speed' => __( 'Speed', 'wppp' ),
								'Custom' => __( 'Custom', 'wppp' ), 
								'Webspace' => __( 'Webspace', 'wppp' )
			),
		) ) ) );
		?>
		<input type="hidden" <?php $renderer->e_opt_name('dynamic_images'); ?> value="<?php echo $renderer->wppp->options['dynamic_images'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('dynamic_images_nosave'); ?> value="<?php echo $renderer->wppp->options['dynamic_images_nosave'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('dynamic_images_cache'); ?> value="<?php echo $renderer->wppp->options['dynamic_images_cache'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('dynamic_images_rthook'); ?> value="<?php echo $renderer->wppp->options['dynamic_images_rthook'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('dynamic_images_rthook_force'); ?> value="<?php echo $renderer->wppp->options['dynamic_images_rthook_force'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('dynamic_images_exif_thumbs'); ?> value="<?php echo $renderer->wppp->options['dynamic_images_exif_thumbs'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('dynimg_quality'); ?> value="<?php echo $renderer->wppp->options['dynimg_quality']; ?>" />

		<h3 class="title"><?php _e( 'Improve image handling', 'wppp' );?></h3>
		<?php if ( $renderer->is_dynamic_images_available() ) : ?>
		<table style="empty-cells:show; width:100%;">
			<tr>
				<td valign="top" style="width:9em;"><div id="dynimg-slider" style="margin-top:1em; margin-bottom:1em;"></div></td>
				<td valign="top" style="padding-left:2em;">
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'All image handling improvements turned off', 'wppp' );?></h4>
						<?php $this->dynimg_output_active_settings( $renderer, 0 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster image upload', 'wppp' );?></h4>
						<p class="description"><?php _e( 'Improved upload performance due to dynamically created intermediate images. Once created images are saved to disc and served directly.', 'wppp' );?></p>
						<?php $this->dynimg_output_active_settings( $renderer, 1 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster image upload and thumbnail creation', 'wppp' );?></h4>
						<p class="description"><?php _e( 'Dynamically created intermediate images, use of EXIF thumbnails if available. <strong>Thumbnails may differ from actual image depending on EXIF thumbnail.</strong>', 'wppp' );?></p>
						<?php $this->dynimg_output_active_settings( $renderer, 2 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster upload / thumbnail creation creation and reduced disc space usage.', 'wppp' );?></h4>
						<p class="description"><?php _e( '<strong>Without CDN not recommended for production sites!</strong><br/>Intermediate images are created on demand but are not saved to disc.', 'wppp' );?></p>
						<?php $this->dynimg_output_active_settings( $renderer, 3 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Custom settings', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Select your own settings. Customize via advanced view.', 'wppp' ); ?></p>
						<?php $this->dynimg_output_active_settings( $renderer, 4 ); ?>
					</div>
				</td>
				<td valign="top" style="width:30%">
					<div class="wppp-dynimg-hint" style="display:none"></div>
					<div class="wppp-dynimg-hint" style="display:none"></div>
					<div class="wppp-dynimg-hint" style="display:none">
						<?php $renderer->do_hint_exif( false ); ?>
					</div>
					<div class="wppp-dynimg-hint" style="display:none">
						<?php 
							$renderer->do_hint_exif( false ); 
							$renderer->do_hint_caching();
							$renderer->do_hint_regen_thumbs( false );
						?>
					</div>
					<div class="wppp-dynimg-hint" style="display:none"></div>
				</td>
			</tr>
		</table>
		<?php else : ?>
			<?php $renderer->do_hint_permalinks( true ); ?>
		<?php endif; ?>
		
		<hr/>
		<?php
	}
}

?>