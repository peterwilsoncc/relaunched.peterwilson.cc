<?php

class WPPP_Dynamic_Images extends WPPP_Module {
	protected static $options_default = array(
		'dynamic_images' => false,
		'dynamic_images_nosave' => false,
		'dynamic_images_cache' => false,
		'dynamic_images_rthook' => false,
		'dynamic_images_rthook_force' => false,
		'dynamic_images_exif_thumbs' => false,
	);

	public function load_renderer ( $view ) {
		if ( $this->renderer == NULL ) {
			if ( $view = 'advanced' ) {
				$this->renderer = new WPPP_Dynamic_Images_Advanced ();
			} else {
				$this->renderer = new WPPP_Dynamic_Images_Simple ();
			}
		}
	}

	public function get_default_options () { return static::$options_default; }

	public function is_active () { 
		return isset( $this->wppp->options['dynamic_images'] ) && $this->wppp->options['dynamic_images'] === true;
	}

	public function is_available () {
		global $wp_rewrite;
		if ( is_multisite() ) {
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			return $wp_rewrite->using_mod_rewrite_permalinks() && $this->wppp->is_network;
		} else {
			return $wp_rewrite->using_mod_rewrite_permalinks();
		}
	}

	public function spawn_module () {
		return new WPPP_Dynamic_Images_Base ( $this->wppp );
	}

	public function validate_options ( &$input, $output ) {
		$defopts = $this->get_default_options();
		foreach ( $defopts as $key => $value ) {
			if ( isset( $input[$key] ) ) {
				$output[$key] = ( $input[$key] == 'true' ? true : false );
				unset( $input[$key] );
			} else {
				$output[$key] = $value;
			}
		}
		return $output;
	}
}

?>