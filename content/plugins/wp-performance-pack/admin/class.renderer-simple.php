<?php
/**
 * Admin settings simple renderer class. Functions for simplified settings.
 *
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Performance Pack
 * @since 0.9
 */
 
include( sprintf( '%s/class.admin-renderer.php', dirname( __FILE__ ) ) );

class WPPP_Admin_Renderer_Simple extends WPPP_Admin_Renderer {

	/*
	 * Settings page functions
	 */

	function enqueue_scripts_and_styles () {
		wp_register_script( 'jquery-ui-slider-pips', $this->wppp->plugin_url . 'common/js/jquery-ui-slider-pips.min.js', array ( 'jquery-ui-slider' ), false, true );
		wp_register_script( 'wppp-admin-script', $this->wppp->plugin_url . 'common/js/wppp_simple.js', array ( 'jquery-ui-slider-pips' ), false, true );
		wp_enqueue_script( 'wppp-admin-script' );

		wp_register_style( 'jquery-ui-slider-pips-styles', $this->wppp->plugin_url . 'common/css/jquery-ui-slider-pips.css' );
		wp_register_style( 'wppp-admin-styles-jqueryui', $this->wppp->plugin_url . 'common/css/styles.css' );
		wp_register_style( 'wppp-admin-styles', $this->wppp->plugin_url . 'common/css/wppp.css' );
		wp_enqueue_style( 'jquery-ui-slider-pips-styles' );
		wp_enqueue_style( 'wppp-admin-styles-jqueryui' );
		wp_enqueue_style( 'wppp-admin-styles' );
	}

	function add_help_tab () {
		$screen = get_current_screen();

		$screen->add_help_tab( array(
			'id'	=> 'wppp_simple_general',
			'title'	=> __('Overview'),
			'content'	=> '<p>' . __( "When you select an option, optimal settings will be applied. Applied settings will be displayed. If some settings couldn't be applied, e.g. due to missing requirements, these will be displayed in red and the next best setting (if available) will be chosen. Also hints as to why a setting couldn't be applied will be displayed. Advanced view offers more in depth control of WPPP settings.", 'wppp' ) .'</p>',
		) );

		$screen->add_help_tab( array(
			'id'	=> 'wppp_simple_l10n',
			'title'	=> __( 'Improve translation performance', 'wppp' ),
			'content'	=>	'<p>' . __( "WPPP offers different levels of improving translation performance. <em>Stable</em> should work on any WordPress blog, <em>Fast</em> further improves performance, but JIT script localization might cause issues with some plugins (if you encounter any problems please report them in the support forums).", 'wppp' ) . '</p>',
		) );

		$screen->add_help_tab( array(
			'id'	=> 'wppp_simple_dynimg',
			'title'	=> __( 'Improve image handling', 'wppp' ),
			'content'	=> '<p>' . __( "Improve image upload speed and web space usage using this setting. Creation of different image sizes will be delayed upon the first access to the respective image. <em>Fast</em> uses EXIF thumbs to create small image sizes, which might cause issues as EXIF thumbs and actual images might differ (depending on used image editing software). Use <em>Webspace</em> only for testing environments or if you are really low on webspace, as this option will slow down your blog because intermediate images don't get saved to disc.", 'wppp' ) . '</p>',
		) );

		$screen->set_help_sidebar(
			'<p><a href="http://wordpress.org/support/plugin/wp-performance-pack" target="_blank">' . __( 'Support Forums' ) . '</a></p>'
			. '<p><a href="http://www.bjoernahrens.de/software/wp-performance-pack/" target="_blank">' . __( 'Development Blog (german)', 'wppp' ) . '</a></p>'
		);
	}

	/*
	 * Setting page rendering functions
	 */

	public function on_do_options_page() {
		$option_keys = array_keys( $this->wppp->get_options_default() );
		unset ( $option_keys [ array_search( 'advanced_admin_view', $option_keys ) ] );
		wp_localize_script( 'wppp-admin-script', 'wpppData', array( json_encode( array(
			'l10n' => array( 'current' => $this->l10n_detect_current_setting(),
							// sequence: stable, speed, current
							'settings' => array( 'use_mo_dynamic' => array(	$this->is_native_gettext_available() != 0,
																			$this->is_native_gettext_available() != 0,
																			$this->wppp->options['use_mo_dynamic'] ),
												'use_jit_localize' => array(	false,
																				$this->is_jit_available(),
																				$this->wppp->options['use_jit_localize'] ),
												'disable_backend_translation' => array(	false,
																						true,
																						$this->wppp->options['disable_backend_translation'] ),
												'dbt_allow_user_override' => array(	false,
																					true,
																					$this->wppp->options['dbt_allow_user_override'] ),
												'dbt_user_default_translated' => array(	true,
																						false,
																						$this->wppp->options['dbt_user_default_translated'] ),
												'use_native_gettext' => array(	$this->is_native_gettext_available() == 0,
																				$this->is_native_gettext_available() == 0,
																				$this->wppp->options['use_native_gettext'] ),
												'mo_caching' => array(	$this->is_native_gettext_available() != 0 && $this->is_object_cache_installed(),
																		$this->is_native_gettext_available() != 0 && $this->is_object_cache_installed(),
																		$this->wppp->options['mo_caching'] ),
							),
			),

			'dynimg' => array( 'current' => $this->dynimg_detect_current_setting(),
								// sequence: stable, speed, webspace, current, cdn_off, cdn_stable, cdn_speed, current
								'settings' => array(	'dynamic_images' => array(	$this->is_dynamic_images_available(),
																					$this->is_dynamic_images_available(),
																					$this->is_dynamic_images_available(),
																					$this->wppp->options['dynamic_images'] ),
														'dynamic_images_nosave' => array(	false,
																							false,
																							true,
																							$this->wppp->options['dynamic_images_nosave'] ),
														'dynamic_images_cache' => array(	false,
																							false,
																							$this->is_object_cache_installed(),
																							$this->wppp->options['dynamic_images_cache'] ),
														'dynamic_images_rthook' => array(	false,
																							false,
																							$this->is_regen_thumbs_available(),
																							$this->wppp->options['dynamic_images_rthook'] ),
														'dynamic_images_rthook_force' => array(	false,
																								false,
																								false,
																								$this->wppp->options['dynamic_images_rthook'] ),
														'dynamic_images_exif_thumbs' => array(	false,
																								$this->is_exif_available(),
																								$this->is_exif_available(),
																								$this->wppp->options['dynamic_images_exif_thumbs'] ),
														'dynimg_quality' => array(	80,
																					80,
																					80,
																					$this->wppp->options['dynimg_quality'] ),
								),
			),

			'labels' => array( 'Off' => __( 'Off', 'wppp' ),
								'Stable' => __( 'Stable', 'wppp' ),
								'Speed' => __( 'Speed', 'wppp' ),
								'Custom' => __( 'Custom', 'wppp' ), 
								'Webspace' => __( 'Webspace', 'wppp' )
			),
		) ) ) );
	}

	function render_options () {
		?>
		<input type="hidden" <?php $this->e_opt_name('use_mo_dynamic'); ?> value="<?php echo $this->wppp->options['use_mo_dynamic'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('use_jit_localize'); ?> value="<?php echo $this->wppp->options['use_jit_localize'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('disable_backend_translation'); ?> value="<?php echo $this->wppp->options['disable_backend_translation'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dbt_allow_user_override'); ?> value="<?php echo $this->wppp->options['dbt_allow_user_override'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('use_native_gettext'); ?> value="<?php echo $this->wppp->options['use_native_gettext'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('mo_caching'); ?> value="<?php echo $this->wppp->options['mo_caching'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('debug'); ?> value="<?php echo $this->wppp->options['debug'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images'); ?> value="<?php echo $this->wppp->options['dynamic_images'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_nosave'); ?> value="<?php echo $this->wppp->options['dynamic_images_nosave'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_cache'); ?> value="<?php echo $this->wppp->options['dynamic_images_cache'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_rthook'); ?> value="<?php echo $this->wppp->options['dynamic_images_rthook'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_rthook_force'); ?> value="<?php echo $this->wppp->options['dynamic_images_rthook_force'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_exif_thumbs'); ?> value="<?php echo $this->wppp->options['dynamic_images_exif_thumbs'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynimg_quality'); ?> value="<?php echo $this->wppp->options['dynimg_quality']; ?>" />
		<input type="hidden" id="dynamic-links" <?php $this->e_opt_name( 'dyn_links' ); ?> value="<?php echo $this->wppp->options['dyn_links'] ? 'true' : 'false'; ?>" />
		<input type="hidden" id="cdn-url" <?php $this->e_opt_name( 'cdnurl' ); ?> value="<?php echo $this->wppp->options['cdnurl']; ?>"/>
		<input type="hidden" <?php $this->e_opt_name('cdn_images'); ?> value="<?php echo $this->wppp->options['cdn_images']; ?>"/>
		<input type="hidden" <?php $this->e_opt_name('dyn_links_subst'); ?> value="<?php echo $this->wppp->options['dyn_links_subst'] ? 'true' : 'false'; ?>" />

		<hr/>
		<h3 class="title"><?php _e( 'Improve localization performance', 'wppp' ); ?></h3>
		<table style="empty-cells:show; width:100%;">
			<tr>
				<td valign="top" style="width:9em;"><div id="l10n-slider" style="margin-top:1em; margin-bottom: 1em;"></div></td>
				<td valign="top" style="padding-left:2em;">
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Localization improvements turned off', 'wppp' ); ?></h4>
						<?php $this->l10n_output_active_settings( 0 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Fast WordPress localization', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Safe settings that should work with any WordPress installation.', 'wppp' );?></p>
						<?php $this->l10n_output_active_settings( 1 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Fastest WordPress localization', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Fastest localization settings. If any problems occur after activating, switch to stable setting.', 'wppp' ); ?></p>
						<?php $this->l10n_output_active_settings( 2 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Custom settings', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Select your own settings. Customize via advanced view.', 'wppp' ); ?></p>
						<?php $this->l10n_output_active_settings( 3 ); ?>
					</div>
				</td>
				<td valign="top" style="width:30%">
					<div class="wppp-l10n-hint" style="display:none"></div>
					<div class="wppp-l10n-hint" style="display:none">
						<?php 
							$native = $this->do_hint_gettext( false );
							if ( $native != 0 ) {
								$this->do_hint_caching();
							}
						?>
					</div>
					<div class="wppp-l10n-hint" style="display:none">
						<?php 
							$this->do_hint_gettext( false ); 
							if ( $native != 0 ) {
								$this->do_hint_caching();
							}
							$this->do_hint_jit( false );
						?>
					</div>
					<div class="wppp-l10n-hint" style="display:none"></div>
				</td>
			</tr>
		</table>
		<hr/>
		<h3 class="title"><?php _e( 'Improve image handling', 'wppp' );?></h3>
		<?php if ( $this->is_dynamic_images_available() ) : ?>
		<table style="empty-cells:show; width:100%;">
			<tr>
				<td valign="top" style="width:9em;"><div id="dynimg-slider" style="margin-top:1em; margin-bottom:1em;"></div></td>
				<td valign="top" style="padding-left:2em;">
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'All image handling improvements turned off', 'wppp' );?></h4>
						<?php $this->dynimg_output_active_settings( 0 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster image upload', 'wppp' );?></h4>
						<p class="description"><?php _e( 'Improved upload performance due to dynamically created intermediate images. Once created images are saved to disc and served directly.', 'wppp' );?></p>
						<?php $this->dynimg_output_active_settings( 1 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster image upload and thumbnail creation', 'wppp' );?></h4>
						<p class="description"><?php _e( 'Dynamically created intermediate images, use of EXIF thumbnails if available. <strong>Thumbnails may differ from actual image depending on EXIF thumbnail.</strong>', 'wppp' );?></p>
						<?php $this->dynimg_output_active_settings( 2 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster upload / thumbnail creation creation and reduced disc space usage.', 'wppp' );?></h4>
						<p class="description"><?php _e( '<strong>Without CDN not recommended for production sites!</strong><br/>Intermediate images are created on demand but are not saved to disc.', 'wppp' );?></p>
						<?php $this->dynimg_output_active_settings( 3 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Custom settings', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Select your own settings. Customize via advanced view.', 'wppp' ); ?></p>
						<?php $this->dynimg_output_active_settings( 4 ); ?>
					</div>
				</td>
				<td valign="top" style="width:30%">
					<div class="wppp-dynimg-hint" style="display:none"></div>
					<div class="wppp-dynimg-hint" style="display:none"></div>
					<div class="wppp-dynimg-hint" style="display:none">
						<?php $this->do_hint_exif( false ); ?>
					</div>
					<div class="wppp-dynimg-hint" style="display:none">
						<?php 
							$this->do_hint_exif( false ); 
							$this->do_hint_caching();
							$this->do_hint_regen_thumbs( false );
						?>
					</div>
					<div class="wppp-dynimg-hint" style="display:none"></div>
				</td>
			</tr>
		</table>
		<?php else : ?>
			<?php $this->do_hint_permalinks( true ); ?>
		<?php endif; ?>
		
		<hr/>
		
		<h3 class="title"><?php _e( 'Use CDN for images', 'wppp' );?></h3>
		
		<p class="description"><?php _e( 'Using a CDN for images improves loading times and eliminates the need to save intermediate images locally (select Webspace). The default settings when activating CDN support are activate dynamic image linking and serving images through CDN on both front and back end. These settings can be adjusted via advanced view.', 'wppp' );?></p>

		<?php
			if ( $this->wppp->options['cdn'] ) {
				$cdn_test = get_transient( 'wppp_cdntest' );
				if ( false !== $cdn_test ) {
					if ( 'ok' === $cdn_test ) { ?>
						<div class="ui-state-highlight ui-corner-all" style="padding:.5em; background: #fff; border: thin solid #7ad03a;"><span class="ui-icon ui-icon-check" style="float:left; margin-top:.2ex; margin-right:.5ex;"></span><?php _e( 'CDN active and working.', 'wppp' );?></div>
						<?php
					} else {
						?>
						<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span><strong><?php _e( 'CDN error!', 'wppp' );?></strong> <?php prinftf( __( "Either the CDN is down or CDN configuration isn't working. CDN will be retested every 15 minutes until the configuration is changed or the CDN is back up. CDN test error message: <em>%s</em>", 'wppp' ), $cdn_test );?></div>
						<?php
					}
					?> <br/> <?php
				}
			}
		?>

		<table>
			<tr valign="top">
				<th scope="row" style="text-align:left"><?php _e( 'Select CDN provider', 'wppp' ); ?></th>
				<td style="padding-left:2em;">
					<select id="wppp-cdn-select" <?php $this->e_opt_name( 'cdn' ) ?> >
						<option value="false" <?php echo $this->wppp->options['cdn'] === false ? 'selected="selected"' : ''; ?>><?php _e( 'None', 'wppp' );?></option>
						<option value="coralcdn" <?php echo $this->wppp->options['cdn'] === 'coralcdn' ? 'selected="selected"' : ''; ?>>CoralCDN</option>
						<option value="maxcdn" <?php echo $this->wppp->options['cdn'] === 'maxcdn' ? 'selected="selected"' : ''; ?>>MaxCDN</option>
						<option value="customcdn" <?php echo $this->wppp->options['cdn'] === 'customcdn' ? 'selected="selected"' : ''; ?>><?php _e( 'Custom', 'wppp' );?></option>
					</select>
					<span id="wppp-maxcdn-signup" <?php echo $this->wppp->options['cdn'] === 'maxcdn' ? '' : 'style="display:none;"'; ?> ><a class="button" href="http://tracking.maxcdn.com/c/92472/3982/378" target="_blank"><?php _e( 'Sign up with MaxCDN', 'wppp' );?></a> <?php _e( '<strong>Use <em>WPPP</em> as coupon code to save 25%!</strong>', 'wppp' );?></span>
					<div id="wppp-nocdn" class="wppp-cdn-div" <?php echo $this->wppp->options['cdn'] !== false ? 'style="display:none"' : ''; ?>>
						<p class="description"><?php _e( 'CDN support is disabled. Choose a CDN provider to activate serving images through the selected CDN.', 'wppp' );?></p>
					</div>
					<div id="wppp-coralcdn" class="wppp-cdn-div" <?php echo $this->wppp->options['cdn'] !== 'coralcdn' ? 'style="display:none"' : ''; ?>>
						<p class="description"><?php _e( '<a href="http://www.coralcdn.org" target="_blank">CoralCDN</a> does not require any additional settings.', 'wppp' );?></p>
					</div>
					<div id="wppp-maxcdn"  class="wppp-cdn-div" <?php echo $this->wppp->options['cdn'] !== 'maxcdn' ? 'style="display:none"' : ''; ?>>
						<p><label for="cdn-url"><?php _e( 'MaxCDN Pull Zone URL:', 'wppp' );?><br/><input id="maxcdn-url" type="text" value="<?php echo $this->wppp->options['cdnurl']; ?>" style="width:80%"/></label></p>
						<p class="description"><?php _e( '<a href="https://cp.maxcdn.com" target="_blank">Log in</a> to your <a href="http://www.maxcdn.com" target="_blank">MaxCDN</a> account, create a pull zone for your WordPress site and enter the CDN URL for that zone.', 'wppp' );?></p>
					</div>
					<div id="wppp-customcdn" class="wppp-cdn-div" <?php echo $this->wppp->options['cdn'] !== 'customcdn' ? 'style="display:none"' : ''; ?>>
						<p><label for="cdn-url"><?php _e( 'CDN URL:', 'wppp' );?><br/><input id="customcdn-url" type="text" value="<?php echo $this->wppp->options['cdnurl']; ?>" style="width:80%"/></label></p>
						<p class="description"><?php _e( 'Enter your CDN URL. This will be used to substitute the host name in image links.', 'wppp' );?></p>
					</div>
					<br/>
				</td>
			</tr>
		</table>

		<hr/>

		<?php
	}

	/*
	 * Simple view helper functions
	 */

	function l10n_detect_current_setting () {
		// off - all options turned off
		if ( !$this->wppp->options['use_mo_dynamic']
			&& !$this->wppp->options['use_jit_localize']
			&& !$this->wppp->options['disable_backend_translation']
			&& !$this->wppp->options['dbt_allow_user_override']
			&& !$this->wppp->options['use_native_gettext']
			&& !$this->wppp->options['mo_caching'] )
			return 0;

		// stable - mo-dynamic/native, caching
		if ( ( $this->wppp->options['use_mo_dynamic'] || $this->is_native_gettext_available() === 0 )
			&& !$this->wppp->options['use_jit_localize']
			&& !$this->wppp->options['disable_backend_translation']
			&& !$this->wppp->options['dbt_allow_user_override']
			&& ( $this->wppp->options['use_native_gettext'] || $this->is_native_gettext_available() !== 0 )
			&& ( $this->wppp->options['mo_caching'] || !$this->is_object_cache_installed() || $this->is_native_gettext_available() === 0 ) )
			return 1;

		// faster - mo-dynamic/native, caching, jit, disable backend, allow user override
		if ( ( $this->wppp->options['use_mo_dynamic'] || $this->is_native_gettext_available() === 0 )
			&& ( $this->wppp->options['use_jit_localize'] || !$this->is_jit_available() )
			&& $this->wppp->options['disable_backend_translation']
			&& $this->wppp->options['dbt_allow_user_override']
			&& ( $this->wppp->options['use_native_gettext'] || $this->is_native_gettext_available() !== 0 )
			&& ( $this->wppp->options['mo_caching'] || !$this->is_object_cache_installed() || $this->is_native_gettext_available() === 0 ) )
			return 2;

		// else custom 
		return 3;
	}

	function dynimg_detect_current_setting () {
		// off - all options turend off
		if ( !$this->wppp->options['dynamic_images'] ) {
			return 0;
		}
		
		// stable - dynimg enabled, image quality 80%
		if ( $this->wppp->options['dynamic_images']
			&& $this->wppp->options['dynimg_quality'] == 80
			&& !$this->wppp->options['dynamic_images_nosave']
			&& !$this->wppp->options['dynamic_images_rthook']
			&& !$this->wppp->options['dynamic_images_exif_thumbs'] ) {
			return 1;
		}

		// speed - same as stable, including exif
		if ( $this->wppp->options['dynamic_images']
			&& $this->wppp->options['dynimg_quality'] == 80
			&& !$this->wppp->options['dynamic_images_nosave']
			&& !$this->wppp->options['dynamic_images_rthook']
			&& $this->wppp->options['dynamic_images_exif_thumbs'] ) {
			return 2;
		}

		// webspace - same as speed, including no_save, cache and regen-integration
		if ( $this->wppp->options['dynamic_images']
			&& $this->wppp->options['dynimg_quality'] == 80
			&& $this->wppp->options['dynamic_images_nosave']
			&& ( $this->wppp->options['dynamic_images_cache'] || !$this->is_object_cache_installed() )
			&& ( $this->wppp->options['dynamic_images_rthook'] || !$this->is_regen_thumbs_available() )
			&& !$this->wppp->options['dynamic_images_rthook_force']
			&& ( $this->wppp->options['dynamic_images_exif_thumbs'] || !$this->is_exif_available() ) ) {
			return 3;
		}

		// else custom
		return 4;
	}

	function e_li_error ( $text ) {
		echo '<li class="ui-state-error" style="border:none; background:none;"><span class="ui-icon ui-icon-closethick" style="float:left; margin-top:.2ex; margin-right:.5ex;"></span>' . $text . '</li>';
	}

	function e_li_check ( $text ) {
		echo '<li class="ui-state-highlight" style="border:none; background:none;"><span class="ui-icon ui-icon-check" style="float:left; margin-top:.2ex; margin-right:.5ex;"></span>' . $text . '</li>';
	}

	function dynimg_output_active_settings ( $level ) {
		echo '<ul>';
		if ( $level == 0 ) {
			// Off
			$this->e_li_error( __( 'All improved image handling settings disabled.', 'wppp' ) );
		} else {
			if ( !$this->is_dynamic_images_available() ) {
				$this->e_li_error( __( 'Pretty Permalinks must be enabled for improved image handling', 'wppp' ) );
			} else {
				$this->e_li_check( __( 'Dynamic image resizing enabled', 'wppp' ) );
				if ( $level < 4 ) {
					$this->e_li_check( __( 'Intermediate image quality set to 80%', 'wppp' ) );
					if ( $level > 1 ) {
						if ( $this->is_exif_available() ) {
							$this->e_li_check( __( 'Use EXIF thumbnails if available.', 'wppp' ) );
						} else {
							$this->e_li_error( __( 'EXIF extension not installed', 'wppp' ) );
						}
						if ( $level > 2 ) {
							$this->e_li_check( __( "Don't save intermediate images", 'wppp' ) );
							if ( $this->is_object_cache_installed() ) {
								$this->e_li_check( __( 'Use caching', 'wppp' ) );
							} else {
								$this->e_li_error( __( 'No persistent object cache installed.', 'wppp' ) );
							}
							if ( $this->is_regen_thumbs_available() ) {
								$this->e_li_check( __( 'Regenerate Thumbnails integration', 'wppp' ) );
							} else {
								$this->e_li_error( __( 'No Regenerate Thumbnails plugin installed', 'wppp' ) );
							}
						}
					}
				} else {
					// custom
					if ( $this->wppp->options['dynamic_images_nosave'] ) {
						$this->e_li_check( __( "Don't save intermediate images", 'wppp' ) );
					}
					if ( $this->wppp->options['dynamic_images_cache'] ) {
						$this->e_li_check( __( 'Use caching', 'wppp' ) );
					}
					if ( $this->wppp->options['dynamic_images_rthook'] ) {
						$this->e_li_check( __( 'Regenerate Thumbnails integration', 'wppp' ) );
						if ( $this->wppp->options['dynamic_images_rthook_force'] ) {
							$this->e_li_check( __( 'Force delte all on Regenerate Thumbnails', 'wppp' ) );
						}
					}
					if ( $this->wppp->options['dynamic_images_exif_thumbs'] ) {
						$this->e_li_check( __( 'Use EXIF thumbnails if available.', 'wppp' ) );
					}
					$this->e_li_check( sprintf( __( 'Intermediate image quality set to %s%%', 'wppp' ), $this->wppp->options['dynimg_quality'] ) );
					if ( $this->wppp->options['dyn_links'] ) {
						$this->e_li_check( __( 'Dynamic image links', 'wppp' ) );
					}
				}
			}
		}
		echo '</ul>';
	}

	function l10n_output_active_settings ( $level ) {
		echo '<ul>';
		if ( $level == 0 ) {
			// Off
			$this->e_li_error( __( 'All translation settings turned off.', 'wppp' ) );
		} else if ( $level < 3 ) {
			// Stable and Speed
			if ( $this->is_native_gettext_available() == 0 ) {
				$this->e_li_check( __( 'Use gettext', 'wppp' ) );
			} else {
				$this->e_li_error( __( 'Gettext not available.', 'wppp' ) );
				$this->e_li_check( __( 'Use alternative MO reader', 'wppp' ) );
				if ( $this->is_object_cache_installed() ) {
					$this->e_li_check( __( 'Use caching', 'wppp' ) );
				} else {
					$this->e_li_error( __( 'No persistent object cache installed.', 'wppp' ) );
				}
			}

			if ( $level > 1 ) {
				if ( $this->is_jit_available() ) {
					$this->e_li_check( __( 'Use JIT localize', 'wppp' ) );
				} else {
					$this->e_li_error( __( 'JIT localize not available', 'wppp' ) );
				}

				$this->e_li_check( __( 'Disable back end translation', 'wppp' ) . ' (' . __( 'Allow user override', 'wppp' ) . ')' );
			}
		} else {
			// Custom
			if ( $this->wppp->options['use_native_gettext'] ) {
				$this->e_li_check( __( 'Use gettext', 'wppp' ) );
			}
			if ( $this->wppp->options['use_mo_dynamic'] ) {
				$this->e_li_check( __( 'Use alternative MO reader', 'wppp' ) );
			}
			if ( $this->wppp->options['mo_caching'] ) {
				$this->e_li_check( __( 'Use caching', 'wppp' ) );
			}
			if ( $this->wppp->options['use_jit_localize'] ) {
				$this->e_li_check( __( 'Use JIT localize', 'wppp' ) );
			}
			if ( $this->wppp->options['disable_backend_translation'] ) {
				$this->e_li_check( __( 'Disable back end translation', 'wppp' ) . ( $this->wppp->options['dbt_allow_user_override'] ? ' (' . __( 'Allow user override', 'wppp' ) . ')' : '' ) );
			}
		}
		echo '</ul>';
	}
}
?>