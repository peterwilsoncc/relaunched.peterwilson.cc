<?php
/*
	Plugin Name: WP Performance Pack
	Plugin URI: http://wordpress.org/plugins/wp-performance-pack
	Description: Performance optimizations for WordPress. Improve localization performance and image handling, serve images through CDN.  
	Version: 1.10.4
	Text Domain: wppp
	Domain Path: /languages/
	Author: Bj&ouml;rn Ahrens
	Author URI: http://www.bjoernahrens.de
	License: GPL2 or later
*/ 

/*
	Copyright 2014 Björn Ahrens (email : bjoern@ahrens.net) 
	This program is free software; you can redistribute it and/or modify 
	it under the terms of the GNU General Public License, version 2 or 
	later, as published by the Free Software Foundation. This program is 
	distributed in the hope that it will be useful, but WITHOUT ANY 
	WARRANTY; without even the implied warranty of MERCHANTABILITY or 
	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License 
	for more details. You should have received a copy of the GNU General
	Public License along with this program; if not, write to the Free 
	Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, 
	MA 02110-1301 USA 
*/


abstract class WPPP_Module {
	protected $wppp = NULL;
	protected $renderer = NULL;

	public abstract function get_default_options ();
	// return if module is available, i.e. can be activated
	public abstract function is_available ();
	public abstract function spawn_module ();
	// validate options
	// input contains actual input
	// output contains the output so far
	// unset processed options in input
	public abstract function validate_options( &$input, $output );

	public function __construct ( $parent ) { $this->wppp = $parent; }
	// initializations done at WPPP construction
	public function early_init () {}
	// initializations done at init action
	public function init () {}
	// initializations done at admin_init action
	public function admin_init () {}

	// admin render functions
	public function tabName () { return 'unnamed'; }
	public function description () { return 'no description'; }
	public function load_renderer ( $view ) {}
	public function enqueue_scripts_and_styles ( $renderer ) {
		$this->load_renderer( $renderer->view );
		if ( $this->renderer !== NULL ) {
			$this->renderer->enqueue_scripts_and_styles( $renderer );
		}
	}
	public function add_help_tab ( $renderer ) {
		$this->load_renderer( $renderer->view );
		if ( $this->renderer !== NULL ) {
			$this->renderer->add_help_tab();
		}
	}
	public function render_options ( $renderer ) {
		$this->load_renderer( $renderer->view );
		if ( $this->renderer !== NULL ) {
			$this->renderer->render_options( $renderer );
		}
	}
}

class WP_Performance_Pack {
	const cache_group = 'wppp1.0'; 	// WPPP cache group name = wppp + version of last change to cache. 
									// This way no cache conflicts occur while old cache entries just expire.
	const wppp_version = '1.10.4';
	const wppp_options_name = 'wppp_option';

	public static $options_default = array(
		'debug' => false,
		'advanced_admin_view' => false,
	);
	private $admin_opts = NULL;
	private $late_updates = array();

	public $dbg_textdomains = array ();
	public $is_network = false;
	public $modules = array();
	public $options = NULL;
	public $plugin_dir = NULL;
	public $plugin_url = NULL;

	function get_options_default () {
		$def_opts = WP_Performance_Pack::$options_default;
		foreach ( $this->modules as $modname => $module ) {
			$def_opts['mod_' . $modname] = true;
			$def_opts = array_merge( $def_opts, $module->get_default_options() );
		}
		return $def_opts;
	}

	function load_options () {
		if ( $this->options == NULL ) {
			$this->options = $this->get_option( self::wppp_options_name );
			$def_opts = $this->get_options_default();
			foreach ( $def_opts as $key => $value ) {
				if ( !isset( $this->options[$key] ) ) {
					$this->options[$key] = $def_opts[$key];
				}
			}
		}
	}

	function get_option( $option_name ) {
		if ( $this->is_network ) {
			return get_site_option( $option_name );
		} else {
			return get_option( $option_name );
		}
	}

	function update_option( $option_name, $data ) {
		if ( $this->is_network ) {
			return update_site_option( $option_name, $data );
		} else {
			return update_option( $option_name, $data );
		}
	}

	public function __construct( $fullinit = true ) {
		/* $code = '$translations = get_translations_for_domain( $domain );
					return $translations->translate( $text );';
		runkit_function_redefine( 'translate', '$text, $domain = "default"', $code ); */

		if ( $fullinit ) {
			// pluings_dir and plugins_url are required for modules, but cause
			// errors on server-dynamic-images/SHORTINIT
			$this->plugin_dir = dirname( __FILE__ );
			$this->plugin_url = plugin_dir_url( __FILE__ );
		}
		spl_autoload_register( array( $this, 'wppp_autoloader' ) );

		// initialize module skeletons
		$modules = glob( sprintf( "%s/modules/*", dirname( __FILE__ ) ), GLOB_ONLYDIR );
		foreach ( $modules as $module ) {
			$modname = basename( $module );
			if ( file_exists( "$module/class.wppp_$modname.php" ) ) {
				include( "$module/class.wppp_$modname.php" );
				$modclass = 'WPPP_' . $modname;
				$this->modules[ $modname ] = new $modclass ( $this );
			}
		}

		// initialize fields
		global $wp_version;
		if ( !function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( $fullinit ) {
			$this->is_network = is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) );
			$this->check_update();
			$this->load_options();
		} else {
			$this->is_network = is_multisite(); // TODO: add is_plugin_active... - removed for now, because fullinit is only set to false from serve-dynamic-images which should not be used when multisite is active (and plugin_basename would cause problems)
			$this->load_options();
			return;
		}

		// add actions
		add_action( 'init', array ( $this, 'init' ) );
		add_action( 'admin_init', array ( $this, 'admin_init' ) );

		// activate and initialize modules
		foreach ( $this->modules as $modname => $module ) {
			if ( $this->options[ 'mod_' . $modname ] === true ) { //  module->is_active() ) {
				$this->modules[$modname] = $module->spawn_module();
			}
			$this->modules[$modname]->early_init();
		}
		
		self::plugin_load_first(); // check WPPP is loaded as first plugin
	}

	public function init () {
		//wp_enqueue_script( 'wppp-media-manager-sizes', plugins_url( '/js/test.js', __FILE__ ), array( 'media-editor' ) );

		// execute "late" updates
		foreach ( $this->late_updates as $updatefunc ) {
			call_user_func( $updatefunc );
		}

		if ( $this->options['debug'] ) {
			add_filter( 'debug_bar_panels', array ( $this, 'add_debug_bar_wppp' ), 10 );
		}

		if ( is_admin() ) {
			// admin pages
			load_plugin_textdomain( 'wppp', false, dirname( plugin_basename( __FILE__ ) ) . '/lang');

			if ( current_user_can ( 'manage_options' ) ) {
				include( sprintf( "%s/admin/class.wppp-admin.php", dirname( __FILE__ ) ) );
				$this->admin_opts = new WPPP_Admin ($this);
			}
		}

		foreach ( $this->modules as $module ) {
			$module->init();
		}
	}

	public function admin_init () {
		foreach ( $this->modules as $module ) {
			$module->admin_init();
		}
	}

	function add_debug_bar_wppp ( $panels ) {
		if ( class_exists( 'Debug_Bar' ) ) {
			include( sprintf( "%s/admin/class.debug-bar-wppp.php", dirname( __FILE__ ) ) );
			$panel = new Debug_Bar_WPPP ();
			$panel->textdomains = &$this->dbg_textdomains;
			$panel->plugin_base = plugin_basename( __FILE__ );
			$panels[] = $panel;
			return $panels;
		}
	}

	/**
	 * Make sure WPPP is loaded as first plugin. Important for e.g. usage of dynamic MOs with all text domains.
	 */
	public static function plugin_load_first() {
		$path = plugin_basename( __FILE__ );

		if ( $plugins = get_option( 'active_plugins' ) ) {
			if ( 0 != ( $key = array_search( $path, $plugins ) ) ) {
				array_splice( $plugins, $key, 1 );
				array_unshift( $plugins, $path );
				update_option( 'active_plugins', $plugins );
			}
		}
	}

	public function activate() { 
		// doesn't fire on update, only on manual activation through admin
		// is called after check_update (which is called at construction)

		if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) { 
			deactivate_plugins( basename(__FILE__) ); // Deactivate self - does that really work at this stage?
			wp_die( 'WP Performance pack requries PHP version >= 5.3' );
		}

		// if is active in network of multisite
		if ( is_multisite() && isset( $_GET['networkwide'] ) && 1 == $_GET['networkwide'] ) {
			add_site_option( self::wppp_options_name, self::$options_default );
			add_site_option( 'wppp_version', self::wppp_version );
		} else {
			add_option( self::wppp_options_name, self::$options_default );
			add_option( 'wppp_version', self::wppp_version );
		}
		self::plugin_load_first();
	}

	public function deactivate() {
		if ( $this->options['dynamic_images'] ) {
			// Delete rewrite rules from htaccess
			//TODO: Module->deactivate(); //WPPP_Dynamic_Images_Base::flush_rewrite_rules( false ); // hopefully WPPP_Dynamic_images didn't get initialized elsewhere. Not sure at which point deactivation occurs, but I think it's save to assume DynImg didn't get initialized so rewrite rules didn't get set.
		}

		if ( is_multisite() && isset( $_GET['networkwide'] ) && 1 == $_GET['networkwide'] ) {
			delete_site_option( self::wppp_options_name );
		} else {
			delete_option( self::wppp_options_name );
		}
		delete_option( 'wppp_dynimg_sizes' );
		delete_option( 'wppp_version' );
		
		// restore static links
		WPPP_CDN_Support_Base::restore_static_links();
	}

	function wppp_autoloader ( $class ) {
		$class = strtolower( $class );
		if ( strncmp( $class, 'wppp_', 5 ) === 0 || $class == 'labelsobject' ) {
			if ( file_exists( sprintf( "%s/classes/class.$class.php", dirname( __FILE__ ) ) ) ) {
				include( sprintf( "%s/classes/class.$class.php", dirname( __FILE__ ) ) );
				return;
			} else if (  file_exists( sprintf( "%s/admin/class.$class.php", dirname( __FILE__ ) ) ) ) {
				include( sprintf( "%s/admin/class.$class.php", dirname( __FILE__ ) ) );
				return;
			}
			// search module folders
			foreach ( $this->modules as $modname => $module ) {
				if ( file_exists( sprintf( "%s/modules/$modname/class.$class.php", dirname( __FILE__ ) ) ) ) {
					include( sprintf( "%s/modules/$modname/class.$class.php", dirname( __FILE__ ) ) );
					return;
				}
			}
		}
	}

	function check_update () {
		if ( ! $opts = $this->get_option( self::wppp_options_name ) ) {
			// if get_option fails, this is activation, so no update necessary
			return;
		}
	
		$installed = $this->get_option( 'wppp_version' );
		if ( version_compare( $installed, self::wppp_version, '!=' ) ) {
			// if installed version differs from version saved in options then do update
			// it is assumed that the options-version is always less or equal to the installed version
			if ( $installed === false || empty( $installed ) ) {
				// pre 1.6.3 version didn't have the wppp_version option

				// serve-dynamic-images.php location has changed, so update rewrite-rules
				if ( isset( $opts['dynamic_images'] ) && $opts['dynamic_images'] ) {
					$this->late_updates[] = array( $this, 'update_163' );
				}
				$installed = '1.6.3';
			}

			if ( version_compare( $installed, '1.9' ) == -1 ) {
				// new in version 1.9: url substitution when using dynamic links is optional
				// if dynamic links were used, substitution was active, so keep it that way
				if ( $opts['dyn_links'] ) {
					$opts['dyn_links_subst'] = true;
					$this->update_option( self::wppp_options_name, $opts );
				}
				// serve-dynamic-images.php location has changed, so update rewrite-rules
				if ( isset( $opts['dynamic_images'] ) && $opts['dynamic_images'] ) {
					$this->late_updates[] = array( $this, 'update_163' );
				}
				$installed = '1.9';
			}

			$this->update_option ( 'wppp_version', self::wppp_version );
		}
	}
	
	function update_163 () {
		//TODO: if module then flush //WPPP_Dynamic_Images_Base::flush_rewrite_rules( true );
	}
} 

if ( class_exists( 'WP_Performance_Pack' ) ) { 
	// instantiate the plugin
	global $wp_performance_pack;
	$wp_performance_pack = new WP_Performance_Pack( !defined( 'SHORTINIT' ) || SHORTINIT == false );
	if ( !defined( 'SHORTINIT' ) || SHORTINIT == false ) {
		register_activation_hook( __FILE__, array( $wp_performance_pack, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $wp_performance_pack, 'deactivate' ) );
	}
}

?>