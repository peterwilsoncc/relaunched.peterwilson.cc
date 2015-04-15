<?php

class WPPP_L10n_Improvements_Simple {
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
			'id'	=> 'wppp_simple_l10n',
			'title'	=> __( 'Improve translation performance', 'wppp' ),
			'content'	=>	'<p>' . __( "WPPP offers different levels of improving translation performance. <em>Stable</em> should work on any WordPress blog, <em>Fast</em> further improves performance, but JIT script localization might cause issues with some plugins (if you encounter any problems please report them in the support forums).", 'wppp' ) . '</p>',
		) );
	}

	function l10n_detect_current_setting ( $renderer ) {
		// off - all options turned off
		if ( !$renderer->wppp->options['use_mo_dynamic']
			&& !$renderer->wppp->options['use_jit_localize']
			&& !$renderer->wppp->options['disable_backend_translation']
			&& !$renderer->wppp->options['dbt_allow_user_override']
			&& !$renderer->wppp->options['use_native_gettext']
			&& !$renderer->wppp->options['mo_caching'] )
			return 0;

		// stable - mo-dynamic/native, caching
		if ( ( $renderer->wppp->options['use_mo_dynamic'] || $renderer->is_native_gettext_available() === 0 )
			&& !$renderer->wppp->options['use_jit_localize']
			&& !$renderer->wppp->options['disable_backend_translation']
			&& !$renderer->wppp->options['dbt_allow_user_override']
			&& ( $renderer->wppp->options['use_native_gettext'] || $renderer->is_native_gettext_available() !== 0 )
			&& ( $renderer->wppp->options['mo_caching'] || !$renderer->is_object_cache_installed() || $renderer->is_native_gettext_available() === 0 ) )
			return 1;

		// faster - mo-dynamic/native, caching, jit, disable backend, allow user override
		if ( ( $renderer->wppp->options['use_mo_dynamic'] || $renderer->is_native_gettext_available() === 0 )
			&& ( $renderer->wppp->options['use_jit_localize'] || !$renderer->is_jit_available() )
			&& $renderer->wppp->options['disable_backend_translation']
			&& $renderer->wppp->options['dbt_allow_user_override']
			&& ( $renderer->wppp->options['use_native_gettext'] || $renderer->is_native_gettext_available() !== 0 )
			&& ( $renderer->wppp->options['mo_caching'] || !$renderer->is_object_cache_installed() || $renderer->is_native_gettext_available() === 0 ) )
			return 2;

		// else custom 
		return 3;
	}

	function l10n_output_active_settings ( $renderer, $level ) {
		echo '<ul>';
		if ( $level == 0 ) {
			// Off
			$renderer->e_li_error( __( 'All translation settings turned off.', 'wppp' ) );
		} else if ( $level < 3 ) {
			// Stable and Speed
			if ( $renderer->is_native_gettext_available() == 0 ) {
				$renderer->e_li_check( __( 'Use gettext', 'wppp' ) );
			} else {
				$renderer->e_li_error( __( 'Gettext not available.', 'wppp' ) );
				$renderer->e_li_check( __( 'Use alternative MO reader', 'wppp' ) );
				if ( $renderer->is_object_cache_installed() ) {
					$renderer->e_li_check( __( 'Use caching', 'wppp' ) );
				} else {
					$renderer->e_li_error( __( 'No persistent object cache installed.', 'wppp' ) );
				}
			}

			if ( $level > 1 ) {
				if ( $renderer->is_jit_available() ) {
					$renderer->e_li_check( __( 'Use JIT localize', 'wppp' ) );
				} else {
					$renderer->e_li_error( __( 'JIT localize not available', 'wppp' ) );
				}

				$renderer->e_li_check( __( 'Disable back end translation', 'wppp' ) . ' (' . __( 'Allow user override', 'wppp' ) . ')' );
			}
		} else {
			// Custom
			if ( $renderer->wppp->options['use_native_gettext'] ) {
				$renderer->e_li_check( __( 'Use gettext', 'wppp' ) );
			}
			if ( $renderer->wppp->options['use_mo_dynamic'] ) {
				$renderer->e_li_check( __( 'Use alternative MO reader', 'wppp' ) );
			}
			if ( $renderer->wppp->options['mo_caching'] ) {
				$renderer->e_li_check( __( 'Use caching', 'wppp' ) );
			}
			if ( $renderer->wppp->options['use_jit_localize'] ) {
				$renderer->e_li_check( __( 'Use JIT localize', 'wppp' ) );
			}
			if ( $renderer->wppp->options['disable_backend_translation'] ) {
				$renderer->e_li_check( __( 'Disable back end translation', 'wppp' ) . ( $renderer->wppp->options['dbt_allow_user_override'] ? ' (' . __( 'Allow user override', 'wppp' ) . ')' : '' ) );
			}
		}
		echo '</ul>';
	}

	public function render_options ( $renderer ) {
		$option_keys = array_keys( $renderer->wppp->get_options_default() );
		unset ( $option_keys [ array_search( 'advanced_admin_view', $option_keys ) ] );
		wp_localize_script( 'wppp-admin-script', 'wpppData', array( json_encode( array(
			'l10n' => array( 'current' => $this->l10n_detect_current_setting( $renderer ),
							// sequence: stable, speed, current
							'settings' => array( 'use_mo_dynamic' => array(	$renderer->is_native_gettext_available() != 0,
																			$renderer->is_native_gettext_available() != 0,
																			$renderer->wppp->options['use_mo_dynamic'] ),
												'use_jit_localize' => array(	false,
																				$renderer->is_jit_available(),
																				$renderer->wppp->options['use_jit_localize'] ),
												'disable_backend_translation' => array(	false,
																						true,
																						$renderer->wppp->options['disable_backend_translation'] ),
												'dbt_allow_user_override' => array(	false,
																					true,
																					$renderer->wppp->options['dbt_allow_user_override'] ),
												'dbt_user_default_translated' => array(	true,
																						false,
																						$renderer->wppp->options['dbt_user_default_translated'] ),
												'use_native_gettext' => array(	$renderer->is_native_gettext_available() == 0,
																				$renderer->is_native_gettext_available() == 0,
																				$renderer->wppp->options['use_native_gettext'] ),
												'mo_caching' => array(	$renderer->is_native_gettext_available() != 0 && $renderer->is_object_cache_installed(),
																		$renderer->is_native_gettext_available() != 0 && $renderer->is_object_cache_installed(),
																		$renderer->wppp->options['mo_caching'] ),
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
		<input type="hidden" <?php $renderer->e_opt_name('use_mo_dynamic'); ?> value="<?php echo $renderer->wppp->options['use_mo_dynamic'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('use_jit_localize'); ?> value="<?php echo $renderer->wppp->options['use_jit_localize'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('disable_backend_translation'); ?> value="<?php echo $renderer->wppp->options['disable_backend_translation'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('dbt_allow_user_override'); ?> value="<?php echo $renderer->wppp->options['dbt_allow_user_override'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('use_native_gettext'); ?> value="<?php echo $renderer->wppp->options['use_native_gettext'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $renderer->e_opt_name('mo_caching'); ?> value="<?php echo $renderer->wppp->options['mo_caching'] ? 'true' : 'false' ?>" />

		<h3 class="title"><?php _e( 'Improve localization performance', 'wppp' ); ?></h3>
		<table style="empty-cells:show; width:100%;">
			<tr>
				<td valign="top" style="width:9em;"><div id="l10n-slider" style="margin-top:1em; margin-bottom: 1em;"></div></td>
				<td valign="top" style="padding-left:2em;">
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Localization improvements turned off', 'wppp' ); ?></h4>
						<?php $this->l10n_output_active_settings( $renderer, 0 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Fast WordPress localization', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Safe settings that should work with any WordPress installation.', 'wppp' );?></p>
						<?php $this->l10n_output_active_settings( $renderer, 1 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Fastest WordPress localization', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Fastest localization settings. If any problems occur after activating, switch to stable setting.', 'wppp' ); ?></p>
						<?php $this->l10n_output_active_settings( $renderer, 2 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Custom settings', 'wppp' ); ?></h4>
						<p class="description"><?php _e( 'Select your own settings. Customize via advanced view.', 'wppp' ); ?></p>
						<?php $this->l10n_output_active_settings( $renderer, 3 ); ?>
					</div>
				</td>
				<td valign="top" style="width:30%">
					<div class="wppp-l10n-hint" style="display:none"></div>
					<div class="wppp-l10n-hint" style="display:none">
						<?php 
							$native = $renderer->do_hint_gettext( false );
							if ( $native != 0 ) {
								$renderer->do_hint_caching();
							}
						?>
					</div>
					<div class="wppp-l10n-hint" style="display:none">
						<?php 
							$renderer->do_hint_gettext( false ); 
							if ( $native != 0 ) {
								$renderer->do_hint_caching();
							}
							$renderer->do_hint_jit( false );
						?>
					</div>
					<div class="wppp-l10n-hint" style="display:none"></div>
				</td>
			</tr>
		</table>
		<hr/>
	<?php
	}
}

?>