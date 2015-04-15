<?php
/**
 * Admin settings renderer class.
 *
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Performance Pack
 * @since 0.9
 */
 
class WPPP_Admin_Renderer {
	public $wppp = NULL;
	private $admin = NULL;
	public $view = '';
	public $current_tab = '';

	public function __construct( $wppp_parent ) {
		$this->wppp = $wppp_parent;
	}

	function enqueue_scripts_and_styles() {}

	public function add_help_tab() {
		$screen = get_current_screen();

		if ( $this->current_tab === '' ) {
			$this->current_tab = ( isset ( $_GET['tab'] ) && isset( $this->wppp->modules[ $_GET['tab'] ] ) ) ? $_GET['tab'] : 'general';
		}

		if ( isset( $this->wppp->modules[ $this->current_tab ] ) ) {
			$this->wppp->modules[ $this->current_tab ]->enqueue_scripts_and_styles( $this );
			$this->wppp->modules[ $this->current_tab ]->add_help_tab( $this );
		} else if ( $this->current_tab === 'general' ) {
			$screen->add_help_tab( array(
				'id'	=> 'wppp_general',
				'title'	=> __('Overview'),
				'content'	=> '<p>' . __( "Welcome to WP Performance Pack, your first choice for speeding up WordPress core the easy way. Simple view helps you to easily apply optimal settings for your blog. If available you can select different options, for which optimal settings will be applied. Applied settings will be displayed. If some settings couldn't be applied, e.g. due to missing requirements, these will be displayed in red and the next best setting (if available) will be chosen. Also hints as to why a setting couldn't be applied will be displayed. Advanced view offers more in depth control of WPPP settings.", 'wppp' ) . '</p>',
			) );

			$screen->add_help_tab( array(
				'id'	=> 'wppp_modules',
				'title'	=> __('Modules'),
				'content'	=> '<p>' . __( "WPPP offers different modules to improve performance of your blog. If you disable a module you don't need, it's settings won't be displayed and the module will not be loaded. Module settings will be remembered when disabling and reenabling a module.", 'wppp' ) . '</p>',
			) );

			if ( $this->view !== 'simple' ) {
				$screen->add_help_tab( array(
					'id'	=> 'wppp_advanced_debugging',
					'title'	=> __( 'Debugging', 'wppp' ),
					'content'	=> '<p>' . sprintf( __( 'WPPP supports debugging using the %s plugin. When installed and activated, debugging adds a new panel to the Debug Bar showing information about loaded textdomains, used translation implementations and translation calls, as well as information about gettext support and other details.', 'wppp' ), '<a href="http://wordpress.org/plugins/debug-bar/">Debug Bar</a>' ) . '</p>',
				) );
			}
		}

		$screen->set_help_sidebar(
			'<p><a href="http://wordpress.org/support/plugin/wp-performance-pack" target="_blank">' . __( 'Support Forums' ) . '</a></p>'
			. '<p><a href="http://www.bjoernahrens.de/software/wp-performance-pack/" target="_blank">' . __( 'Development Blog (german)', 'wppp' ) . '</a></p>'
		);
	}

	public function on_do_options_page() {}

	public function render_page ( $formaction ) {
		add_thickbox();
		?>
		<div class="wrap">
			<?php
			$tabs = array( 'general' => __( 'General', 'wppp' ) );
			foreach( $this->wppp->modules as $modname => $modinstance ) {
				if ( $this->wppp->options[ 'mod_' . $modname ] ) {
					$tabs[$modname] = $this->wppp->modules[ $modname ]->tabName();
				}
			}

			if ( $this->current_tab === '' ) {
				$this->current_tab = ( isset ( $_GET['tab'] ) && isset( $this->wppp->modules[ $_GET['tab'] ] ) ) ? $_GET['tab'] : 'general';
			}

			echo '<h2 class="nav-tab-wrapper">';
			echo '<div style="width:90px; height:32px; background: url('. plugins_url( 'img/wppp_logo_62x32.png' , __FILE__ ) .') no-repeat left; float:left;"><p style="float:right; margin-right:5px;">v' . WP_Performance_Pack::wppp_version . '</p></div>';
			foreach( $tabs as $tab => $name ){
				$class = ( $tab == $this->current_tab ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?page=wppp_options_page&tab=$tab'>$name</a>";
			}
			echo '</h2>';
			?>
			
			<div class="wppp-sticky" style="float:right; width:195px;">
				<?php
				$show_support = get_transient( 'wppp-support-box' );
				$today = new DateTime();
				if ( $show_support !== $today->format('Y-m-d') ) : ?>
					<div id="wppp-support-box" class="wppp-support" style="width:95%; border: 1px solid #ddd; background: #fff; padding: 5px; text-align:center; margin-bottom:2em;">
						<h3><?php _e( 'Support WPPP', 'wppp' ); ?></h3>
						<p><?php _e( 'Do you like this Plugin? If so, please support its development.', 'wppp' ); ?></p>
						<p><a href="http://wordpress.org/support/view/plugin-reviews/wp-performance-pack"><?php _e( 'Rate WPPP', 'wppp' );?></a></p>

						<div>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
								<input type="hidden" name="cmd" value="_s-xclick" />
								<input type="hidden" name="hosted_button_id" value="QCZP6B3QNVD8L" />
								<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online." />
								<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
							</form>
						</div>
						<br/>
						<p><small><a id="hidesupportbox" href="#" class="dismiss"><?php _e( 'Dismiss this message for today.', 'wppp' );?></a></small></p>
					</div>
				<?php endif; ?>

				<div style="width:95%; border: 1px solid #ddd; background: #fff; padding: 5px; text-align:center">
					<h3><?php _e( 'Need help?', 'wppp' );?></h3>
					<p><?php _e( 'Got any questions? Found a bug? Have any Suggestions?', 'wppp' );?></p>
					<p><a class="button" href="http://wordpress.org/support/plugin/wp-performance-pack" target="_blank"><?php _e( 'Visit the support forums', 'wppp' ); ?></a></p>
					<!--<p><a class="thickbox button" href="admin-ajax.php?action=wpppsupport&width=600&height=550" title="System report">Generate system report</a></p>-->
				</div>
			</div>
			
			<div style="margin-right: 200px;">
				<form id="wppp-settings" action="<?php echo $formaction; ?>" method="post">
					<?php 
						if ( $this->wppp->is_network ) {
							wp_nonce_field( 'update_wppp', 'wppp_nonce' );
						}
						settings_fields( 'wppp_options' );

						if ( isset( $this->wppp->modules[ $this->current_tab ] ) ) {
							$this->wppp->modules[ $this->current_tab ]->render_options( $this );
						} else { ?>
							<h3 class="title"><?php _e( 'Modules', 'wppp' ); ?></h3>
							<table class="form-table" style="clear:none">
								<?php foreach ( $this->wppp->modules as $modname => $modinstance ) { ?>
									<tr>
										<th><?php echo $modinstance->tabName(); ?></th>
										<td>
											<?php $this->e_radio_enable( 'id_mod_' . $modname, 'mod_' . $modname ); ?>
											<p class="description"><?php echo $modinstance->description(); ?></p>
										</td>
									</tr>
								<?php } ?>
							</table>
							<hr/>
							<?php if ( $this->view !== 'simple' ) { ?>
								<h3 class="title"><?php _e( 'Debugging', 'wppp' ); ?></h3>
								<table class="form-table" style="clear:none">
									<tr valign="top">
										<th scope="row"><?php _e( 'Debug Panel', 'wppp' ); ?></th>
										<td>
											<?php $this->e_radio_enable( 'debug-panel', 'debug', !class_exists( 'Debug_Bar' ) ); ?>
											<p class="description"><?php _e( 'Enables debugging, requires <a href="http://wordpress.org/plugins/debug-bar/">Debug Bar</a> Plugin.', 'wppp' ); ?></p>
										</td>
									</tr>
								</table>
								<hr/>
							<?php } ?>
						<?php }

						submit_button();
					?>
				</form>
				<?php $this->do_switch_view_button( $formaction, $this->wppp->options['advanced_admin_view'] ? 'false' : 'true' ); ?>
			</div>
		</div>
		<?php
	}

	/*
	 * Feature detection functions
	 */

	function is_object_cache_installed () {
		global $wp_object_cache;
		return ( file_exists ( WP_CONTENT_DIR . '/object-cache.php' )
				&& get_class( $wp_object_cache ) != 'WP_Object_Cache' );
	}

	function is_native_gettext_available () {
		static $result = NULL;
		if ( $result !== NULL) {
			return $result;
		}

		// gettext extension is required
		if ( !extension_loaded( 'gettext' ) ) {
			$result = 1;
			return 1;
		};

		// language dir must exist (an be writeable...)
		$locale = get_locale();
		$path = WP_LANG_DIR . '/' . $locale . '/LC_MESSAGES';
		if ( !is_dir ( $path ) ) {
			if ( !wp_mkdir_p ( $path ) ) {
				$result = 2;
				return 2;
			}
		}

		// load test translation and test if it translates correct
		$mo = new WPPP_Native_Gettext();
		if ( !$mo->import_from_file( sprintf( '%s/native-gettext-test.mo', dirname( __FILE__ ) ) ) ) {
			$result = 3;
			return 3;
		}

		if ( $mo->translate( 'native-gettext-test' ) !== 'success' ) {
			$result = 4;
			return 4;
		}

		// all tests successful => return 0
		$result = 0;
		return 0;
	}

	function is_jit_available () {
		global $wp_version;
		return isset( WPPP_L10n_Improvements_Base::$jit_versions[ $wp_version ] );
	}

	function is_dynamic_images_available () {
		return $this->wppp->modules['dynamic_images']->is_available();
	}

	function is_regen_thumbs_available () {
		return	$this->is_dynamic_images_available() &&
				( is_plugin_active( 'regenerate-thumbnails/regenerate-thumbnails.php' )
				 || is_plugin_active( 'ajax-thumbnail-rebuild/ajax-thumbnail-rebuild.php' )
				 || is_plugin_active( 'simple-image-sizes/simple_image_sizes.php' ) );
	}

	function is_exif_available () {
		return extension_loaded( 'exif' ) && function_exists( 'exif_thumbnail' ) && function_exists( 'imagecreatefromstring' ) && $this->is_dynamic_images_available();
	}

	/*
	 * Helper functions
	 */

	function do_hint_gettext ( $as_error ) {
		$native = $this->is_native_gettext_available(); 
		if ( $native != 0 ) {
			if ( $as_error ) {
				echo '<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span>';
			} else {
				echo '<div class="ui-state-highlight ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-info" style="float:left; margin-right:.3em;"></span>';
			}

			switch ( $native ) {
				case 0 :	break;
				case 1 :	printf( __( 'Gettext support requires the %s extension.', 'wppp' ), '<a href="http://www.php.net/gettext">PHP Gettext</a>' );
							break;
				case 2 :
				case 3 :	printf( __( 'Gettext support requires the language directory %s to be writeable for php.', 'wppp' ), '<code>wp-content/languages</code>' );
							break;
				case 4 :	_e( 'Gettext test failed. Activate WPPP debugging for additional info.', 'wppp' );
							break;
			}
			echo '</div>';
		}
		return $native;
	}

	function do_hint_caching () {
		if ( !$this->is_object_cache_installed() ) : ?>
			<div class="ui-state-highlight ui-corner-all" style="padding:.5em">
				<span class="ui-icon ui-icon-info" style="float:left; margin-right:.3em;"></span>
				<?php printf( __( 'Caching requires a persisten object cache to be effective. Different %sobject cache plugins%s are available for WordPress.', 'wppp' ), '<a href="http://wordpress.org/plugins/search.php?q=object+cache">', '</a>' ); ?>
			</div>
		<?php endif;
	}

	function do_hint_jit ( $as_error ) {
		if ( !$this->is_jit_available() ) {
			if ( $as_error ) {
				echo '<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span>';
			} else {
				echo '<div class="ui-state-highlight ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-info" style="float:left; margin-right:.3em;"></span>';
			}
			printf( __( 'JIT localization of scripts is only available for WordPress versions %s .', 'wppp' ), implode( ', ', array_keys( WPPP_L10n_Improvements::$jit_versions ) ) );
			echo '</div>';
		}
	}

	function do_hint_permalinks ( $as_error ) {
		if ( !$this->is_dynamic_images_available() ) {
			if ( $as_error ) {
				echo '<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span>';
			} else {
				echo '<div class="ui-state-highlight ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-info" style="float:left; margin-right:.3em;"></span>';
			}
			_e( 'Improved image handling requires Pretty Permalinks and is not available on multisite installations.', 'wppp' );
			echo '</div>';
		}
	}

	function do_hint_exif ( $as_error ) {
		if ( !$this->is_exif_available() ) {
			if ( $as_error ) {
				echo '<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span>';
			} else {
				echo '<div class="ui-state-highlight ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-info" style="float:left; margin-right:.3em;"></span>';
			}
			_e( 'Use of EXIF thumbnails requires the EXIF extension to be installed and GD support.', 'wppp' );
			echo '</div>';
		}
	}

	function do_hint_regen_thumbs ( $as_error ) {
		if ( !$this->is_regen_thumbs_available() ) {
			if ( $as_error ) {
				echo '<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span>';
			} else {
				echo '<div class="ui-state-highlight ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-info" style="float:left; margin-right:.3em;"></span>';
			}
			 printf( __( 'One of the following plugins has to be installed and activated for Regenerate Thumbnails integration: %s', 'wppp' ), '<a href="http://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerate Thumbnails</a>, <a href="http://wordpress.org/plugins/ajax-thumbnail-rebuild/">AJAX Thumbnail Rebuild</a>, <a href="http://wordpress.org/plugins/simple-image-sizes/">Simple Image Sizes</a>' );
			echo '</div>';
		}
	}

	function do_switch_view_button ( $formaction, $value ) {
		?>
		<form action="<?php echo $formaction; ?>" method="post">
			<?php if ( $this->wppp->is_network ) : ?>
				<?php wp_nonce_field( 'update_wppp', 'wppp_nonce' ); ?>
			<?php endif; ?>
			<?php settings_fields( 'wppp_options' ); ?>
			<input type="hidden" <?php $this->e_opt_name('advanced_admin_view'); ?> value="<?php echo $value; ?>" />
			<input type="submit" class="button" type="submit" value="<?php echo ( $value == 'true' ) ? __( 'Switch to advanced view', 'wppp') : __( 'Switch to simple view', 'wppp' ); ?>" />
		</form>
		<?php
	}

	public function e_opt_name ( $opt_name ) {
		echo 'name="'.WP_Performance_Pack::wppp_options_name.'['.$opt_name.']"';
	}

	public function e_checked ( $opt_name, $value = true ) {
		echo $this->wppp->options[$opt_name] === $value ? 'checked="checked" ' : ' ';
	}

	public function e_checked_or ( $opt_name, $value = true, $or_val = true ) {
		echo $this->wppp->options[$opt_name] === $value || $or_val ? 'checked="checked" ' : ' ';
	}

	public function e_checked_and ( $opt_name, $value = true, $and_val = true ) {
		echo $this->wppp->options[$opt_name] === $value && $and_val ? 'checked="checked" ' : ' ';
	}
	
	public function e_radio_enable ( $id, $opt_name, $disabled = false ) {
		?>
		<label for="<?php echo $id; ?>-enabled"><input id="<?php echo $id; ?>-enabled" type="radio" <?php $this->e_opt_name( $opt_name ); ?> value="true" <?php if ( $disabled ) { echo 'disabled="true" '; } else { $this->e_checked( $opt_name ); } ?>/><?php _e( 'Enabled', 'wppp' ); ?></label>&nbsp;
		<label for="<?php echo $id; ?>-disabled"><input id="<?php echo $id; ?>-disabled" type="radio" <?php $this->e_opt_name( $opt_name ); ?> value="false" <?php if( $disabled ) { echo 'disabled="true" checked="checked"'; } else { $this->e_checked( $opt_name, false ); } ?>/><?php _e( 'Disabled', 'wppp' ); ?></label>
		<?php
	}
	
	public function e_checkbox ( $id, $opt_name, $label, $disabled = false ) {
		?>
		<label for="<?php echo $id; ?>"><input id="<?php echo $id; ?>" type="checkbox" <?php $this->e_opt_name( $opt_name ); ?> value="true" <?php if ( $disabled ) { echo 'disabled="true" '; } else { $this->e_checked( $opt_name ); } ?>/><?php echo $label; ?></label>
		<?php
	}
	
	 /*
	 * Simple view helper functions
	 */

	function e_li_error ( $text ) {
		echo '<li class="ui-state-error" style="border:none; background:none;"><span class="ui-icon ui-icon-closethick" style="float:left; margin-top:.2ex; margin-right:.5ex;"></span>' . $text . '</li>';
	}

	function e_li_check ( $text ) {
		echo '<li class="ui-state-highlight" style="border:none; background:none;"><span class="ui-icon ui-icon-check" style="float:left; margin-top:.2ex; margin-right:.5ex;"></span>' . $text . '</li>';
	}
}

?>