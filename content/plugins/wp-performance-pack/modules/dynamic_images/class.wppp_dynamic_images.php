<?php

class WPPP_Dynamic_Images extends WPPP_Module {
	protected static $options_default = array(
		'dynamic_images' => false,
		'dynamic_images_nosave' => false,
		'dynamic_images_cache' => false,
		'dynamic_images_rthook' => false,
		'dynamic_images_rthook_force' => false,
		'dynamic_images_exif_thumbs' => false,
		'dynimg_quality' => 80,
		'dynimg_serve_method' => 'short_init', 
	);

	public function load_renderer ( $view ) {
		if ( $this->renderer == NULL ) {
			if ( $view === 'advanced' ) {
				$this->renderer = new WPPP_Dynamic_Images_Advanced ();
			} else {
				$this->renderer = new WPPP_Dynamic_Images_Simple ();
			}
		}
	}

	public function get_default_options () { return static::$options_default; }

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
		$flush = false;
		foreach ( $defopts as $key => $value ) {
			if ( isset( $input[$key] ) ) {
				switch( $key ) {
					case 'dynamic_images'		: $val = ( $input[$key] == 'true' ? true : false );
												  $flush = ( $this->wppp->options[ $key ] !== $val ) || $flush;
												  $output[ $key ] = $val;
												  break;
					case 'dynimg_quality'		: $output[$key] = ( is_numeric( $input[$key] ) && $input[$key] >= 10 && $input[$key] <= 100 ) ? $input[ $key] : $val;
												  break;
					case 'dynimg_serve_method'	: $val = ( $input[$key] === 'use_themes' ) ? 'use_themes' : 'short_init';
												  $flush = ( $this->wppp->options[ $key ] !== $val ) || $flush;
												  $output[ $key ] = $val;
												  break;
					default						: $output[$key] = ( $input[$key] == 'true' ? true : false );
												  break;
				}
				unset( $input[$key] );
			} else {
				$output[$key] = $value;
			}
		}

		if ( $flush ) {
			$this->flush_rewrite_rules( $output[ 'dynamic_images' ], $output[ 'dynimg_serve_method' ] );
		}
		
		return $output;
	}
	
	public function flush_rewrite_rules ( $enabled, $method = false ) {} // Dummy
	
	public function tabName() { return __( 'Images', 'wppp' );  }

	public function description() { return __( 'Improve WordPress image handling by creating intermediate images (thumbnails) on demand.', 'wppp' ); }
}

?>