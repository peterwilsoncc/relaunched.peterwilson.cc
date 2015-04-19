<?php
/**
 * Admin settings base class (abstract). Adds admin menu and contains functions for both
 * simple and advanced view.
 *
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Performance Pack
 * @since 0.8
 */

class WPPP_Admin {
	private $wppp = NULL;
	private $renderer = NULL;
	private $show_update_info = false;

	public function __construct($wppp_parent) {
		$this->wppp = $wppp_parent;

		if ( $this->wppp->is_network ) {
			add_action( 'network_admin_menu', array( $this, 'add_menu_page' ) );
		} else {
			// register setting only if not multisite - multisite performs validate on its own
			register_setting( 'wppp_options', WP_Performance_Pack::wppp_options_name, array( $this, 'validate' ) );
			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		}
		add_action('wp_ajax_wpppsupport', array( $this, 'support_dialog' ) );
		add_action('wp_ajax_hidewpppsupportbox', array( $this, 'hide_support_box' ) );
		add_action('wp_ajax_wppp_restore_all_links', array( $this, 'restore_all_links' ) );
	}

	function restore_all_links () {
		WPPP_CDN_Support_Base::restore_static_links( true );
		exit();
	}

	function hide_support_box () {
		$today = new DateTime();
		set_transient( 'wppp-support-box', $today->format('Y-m-d'), DAY_IN_SECONDS );
	}

	function support_dialog () {
		?>
		<p>Include the following information along with your bug / issue / question when posting in the support forums:</p>
		<textarea rows="25" style="width:100%">
WPPP version: <?php echo WP_Performance_Pack::wppp_version; ?>

WPPP settings: <?php
	foreach ( $this->wppp->options as $opt => $val ) {
		echo $opt, ' = "', $val, '", ';
	}
?>

----------
WordPress version: <?php global $wp_version; echo $wp_version; ?>

Multisite: <?php echo is_multisite() ? 'yes' : 'no'; ?>

Plugins: <?php
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$all_plugins = get_plugins();
	foreach ( $all_plugins as $plugin => $plugin_data ) {
		if ( is_plugin_active( $plugin ) ) {
			echo '** ', $plugin_data['Name'], ' **';
		} else {
			echo $plugin_data['Name'];
		}
		echo ', ';
	}
?>

----------
OS: <?php echo php_uname(); ?>

PHP version: <?php echo phpversion(); ?>

Loaded extensions: <?php
	$exts = get_loaded_extensions();
	asort( $exts );
	echo implode( ', ', $exts );
?>
		</textarea>
		<?php
		exit();
	}

	public function add_menu_page() {
		if ( $this->wppp->is_network ) {
			$wppp_options_hook = add_submenu_page( 'settings.php', __( 'WP Performance Pack', 'wppp' ), __( 'Performance Pack', 'wppp' ), 'manage_options', 'wppp_options_page', array( $this, 'do_options_page' ) );
		} else {
			$wppp_options_hook = add_options_page( __( 'WP Performance Pack', 'wppp' ), __( 'Performance Pack', 'wppp' ), 'manage_options', 'wppp_options_page', array( $this, 'do_options_page' ) );
		}
		add_action('load-'.$wppp_options_hook, array ( $this, 'load_admin_page' ) );
	}

	/*
	 * Save and validate settings functions
	 */

	public function validate( $input ) {
		$output = $this->wppp->options; // default output are current settings
		if ( isset( $input ) && is_array( $input ) ) {

			// test if view mode has changed. if so, leave all other settings as they are
			if ( isset( $input['advanced_admin_view'] ) ) {
				$view = $input['advanced_admin_view'] == 'true' ? true : false;
				if ( $view != $this->wppp->options['advanced_admin_view'] ) {
					$output['advanced_admin_view'] = $view;
					return $output;
				}
			}

			$current = ( isset ( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';
			if ( isset( $this->wppp->modules[ $current ] ) ) {
				// process module options
				$output = $this->wppp->modules[ $current ]->validate_options( $input, $output );
			} else {
				// process WPPP general options
				foreach ( WP_Performance_Pack::$options_default as $key => $val ) {
					if ( isset( $input[$key] ) ) {
						// validate set input values
						switch ( $key ) {
							case 'advanced_admin_view' 	: $output[$key] = $this->wppp->options['advanced_admin_view'];
														  break;
							default						: $output[$key] = ( $input[$key] == 'true' ? true : false );
														  break;
						}
						unset( $input[$key] );
					} else {
						// not set values are assumed as false or the respective value (not necessarily the default value)
						switch ( $key ) {
							case 'advanced_admin_view' 	: $output[$key] = $this->wppp->options['advanced_admin_view'];
														  break;
							default						: $output[$key] = false;
														  break;
						}
					} // if isset...
				} // foreach
				
				// process module activation
				foreach ( $this->wppp->modules as $modname => $modinstance ) {
					if ( isset( $input[ 'mod_' . $modname ] ) ) {
						// validate set input values
						$output[ 'mod_' . $modname ] = ( $input[ 'mod_' . $modname ] == 'true' ? true : false );
						unset( $input[ 'mod_' . $modname ] );
					} else {
						// not set values are assumed as false or the respective value (not necessarily the default value)
						$output[ 'mod_' . $modname ] = false;
					} // if isset...
				} // foreach
			}
		}
		return $output;
	}

	function update_wppp_settings () {
		if ( current_user_can( 'manage_network_options' ) ) {
			check_admin_referer( 'update_wppp', 'wppp_nonce' );
			$input = array();
			$def_opts = $this->wppp->get_options_default();
			foreach ( $def_opts as $key => $value ) {
				if ( isset( $_POST['wppp_option'][$key] ) ) {
					$input[$key] = sanitize_text_field( $_POST['wppp_option'][$key] );
				}
			}
			$this->wppp->options = $this->validate( $input );
			$this->wppp->update_option( WP_Performance_Pack::wppp_options_name, $this->wppp->options );
		}
	}

	/*
	 * Settings page functions
	 */

	private function load_renderer () {
		if ( $this->renderer == NULL) {
			include( sprintf( "%s/class.admin-renderer.php", dirname( __FILE__ ) ) );
			$this->renderer = new WPPP_Admin_Renderer( $this->wppp );
			if ( $this->wppp->options['advanced_admin_view'] ) {
				$this->renderer->view = 'advanced';
			} else {
				$this->renderer->view = 'simple';
			}
		}
	}

	function load_admin_page () {
		if ( $this->wppp->is_network ) {
			if ( isset( $_GET['action'] ) && $_GET['action'] === 'update_wppp' ) {
				$this->update_wppp_settings();
				$this->show_update_info = true;
			}
		}
		$this->load_renderer();
		$this->renderer->enqueue_scripts_and_styles();
		$this->renderer->add_help_tab();
	}

	public function do_options_page() {
		$current = ( isset ( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';
		if ( $this->wppp->is_network ) {
			$formaction = network_admin_url('settings.php?page=wppp_options_page&action=update_wppp&tab=' . $current);
		} else {
			$formaction = admin_url( 'options.php?tab=' . $current );
		}

		if ( $this->show_update_info ) {
			echo '<div class="updated"><p>', __( 'Settings saved.' ), '</p></div>';
		}

		$this->load_renderer();
		$this->renderer->render_page( $formaction );
	}
}
?>