<?php

class WPPP_L10n_Improvements_Base extends WPPP_L10n_Improvements {

	function is_jit_available () {
		global $wp_version;
		return isset( static::$jit_versions[$wp_version] );
	}

	public function early_init () {
		if ( $this->wppp->options['use_mo_dynamic'] 
			|| $this->wppp->options['use_native_gettext']
			|| $this->wppp->options['disable_backend_translation'] ) {

			global $l10n;
			$l10n['WPPP_NOOP'] = new NOOP_Translations;
			add_filter( 'override_load_textdomain', array( $this, 'wppp_load_textdomain_override' ), 0, 3 );
		}

		if ( $this->is_jit_available() && $this->wppp->options['use_jit_localize'] ) {
			global $wp_scripts;
			if ( !isset( $wp_scripts ) && !defined( 'IFRAME_REQUEST' ) ) {
				global $wp_version;
				include( sprintf( "%s/jit-by-version/wp" . static::$jit_versions[$wp_version] . ".php", dirname( __FILE__ ) ) );
				remove_action( 'wp_default_scripts', 'wp_default_scripts' );
				add_action( 'wp_default_scripts', 'wp_jit_default_scripts' );
				$wp_scripts = new WPPP_Scripts_Override();
			}
		}
	}
	
	function wppp_load_textdomain_override( $retval, $domain, $mofile ) {
		global $l10n;

		$result = false;
		$mo = NULL;

		if ( $this->wppp->options['disable_backend_translation'] 
			&& is_admin() 
			&& !( defined( 'DOING_AJAX' ) && DOING_AJAX && false === strpos( wp_get_referer(), '/wp-admin/' ) ) ) {
			if ( $this->wppp->options['dbt_allow_user_override'] ) {
				global $current_user;
				if ( !function_exists('wp_get_current_user')) {
					require_once(ABSPATH . "wp-includes/pluggable.php");
				}
				wp_cookie_constants();
				$current_user = wp_get_current_user();

				$user_setting = get_user_option ( 'wppp_translate_backend', $current_user->user_ID );
				$user_override = $user_setting === 'true' || ( $this->wppp->options['dbt_user_default_translated'] && $user_setting === false );
				if ( !$user_override ) {
					$mo = $l10n['WPPP_NOOP'];
					$result = true;
				}
			} else {
				$mo = $l10n['WPPP_NOOP'];
				$result = true;
			}
		}


		if ( $mo === NULL ) {
			do_action( 'load_textdomain', $domain, $mofile );
			$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

			if ( isset( $l10n[$domain] ) ) {
				if ( $l10n[$domain] instanceof WPPP_MO_dynamic && $l10n[$domain]->Mo_file_loaded( $mofile ) ) {
					return true;
				}
			}

			if ( $this->wppp->options['debug'] ) {
				$callers=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				$this->wppp->dbg_textdomains[$domain]['mofiles'][] = $mofile;
				$this->wppp->dbg_textdomains[$domain]['callers'][] = $callers;
			}

			if ( !is_readable( $mofile ) ) {
				if ( $this->wppp->options['debug'] ) {
					$this->wppp->dbg_textdomains[$domain]['mofileexists'][] = 'no';
				}
				return false; // return false is important so load_plugin_textdomain/load_theme_textdomain/... can call load_textdomain for different locations
			} elseif ( $this->wppp->options['debug'] ) {
				$this->wppp->dbg_textdomains[$domain]['mofileexists'][] = 'yes';
			}
		} else {
			if ( $this->wppp->options['debug'] ) {
				$callers=debug_backtrace();
				$this->wppp->dbg_textdomains[$domain]['mofiles'][] = $mofile;
				$this->wppp->dbg_textdomains[$domain]['mofileexists'][] = '-';
				$this->wppp->dbg_textdomains[$domain]['callers'][] = $callers;
			}
		}


		if ( $mo === NULL && $this->wppp->options['use_native_gettext'] && extension_loaded( 'gettext' ) ) {
			$mo = new WPPP_Native_Gettext ();
			if ( $mo->import_from_file( $mofile ) ) { 
				$result = true;
			} else {
				$mo = NULL;
			}
		}
	
		if ( $mo === NULL && $this->wppp->options['use_mo_dynamic'] ) {
			if ( $this->wppp->options['debug'] ) {
				$mo = new WPPP_MO_dynamic_Debug ( $domain, $this->wppp->options['mo_caching'] );
			} else {
				$mo = new WPPP_MO_dynamic ( $domain, $this->wppp->options['mo_caching'] );
			}
			if ( $mo->import_from_file( $mofile ) ) { 
				$result = true;
			} else {
				$mo->unhook_and_close();
				$mo = NULL;
			}
		}

		if ( $mo !== NULL ) {
			if ( isset( $l10n[$domain] ) ) {
				$mo->merge_with( $l10n[$domain] );
				if ( $l10n[$domain] instanceof WPPP_MO_dynamic ) {
					$l10n[$domain]->unhook_and_close();
				}
			}
			$l10n[$domain] = $mo;
		}

		return $result;
	}
}

?>