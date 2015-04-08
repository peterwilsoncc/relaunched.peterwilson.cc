<?php

class WPPP_CDN_Support extends WPPP_Module {
	protected static $options_default = array(
		'cdn' => false,
		'cdnurl' => '',
		'cdn_images' => 'both',
		'dyn_links' => false,
		'dyn_links_subst' => false,
	);

	public function load_renderer ( $view ) {
		if ( $this->renderer == NULL ) {
			if ( $view = 'advanced' ) {
				$this->renderer = new WPPP_CDN_Support_Advanced ();
			} else {
				$this->renderer = new WPPP_CDN_Support_Simple ();
			}
		}
	}

	public function get_default_options () { return static::$options_default; }

	function is_active () { 
		// always load cdn support for dynamic links - to keep once substituted urls working even if dyn_links is disabled
		return $this->wppp->options['cdn'] || $this->wppp->options['dyn_links'];
	}

	function is_available () { return true; } // always available

	function spawn_module () {
		return new WPPP_CDN_Support_Base ( $this->wppp );
	}

	function validate_options ( &$input, $output ) {
		// option: cdn
		if ( isset( $input['cdn'] ) ) {
			$value = trim( sanitize_text_field( $input['cdn'] ) );
			unset( $input['cdn'] );
		} else {
			$value = '';
		}
		switch ( $value ) {
			case 'coralcdn'		:
			case 'maxcdn' 		:
			case 'customcdn'	: $output['cdn'] = $value;
								break;
			default				: $output['cdn'] = false;
								break;
		}

		// option: cdnurl
		if ( isset( $input['cdnurl'] ) ) {
			$value = trim( sanitize_text_field( $input['cdnurl'] ) );
			unset( $input['cdnurl'] );
		} else {
			$value = '';
		}
		if ( !empty( $value ) ) {
			$scheme = parse_url( $value, PHP_URL_SCHEME );
			if ( empty( $scheme ) ) {
				$value = 'http://' . $value;
			}
		}
		$output['cdnurl'] = $value;

		// option: cdn_images
		if ( isset ( $input['cdn_images'] ) ) {
			$value = trim( sanitize_text_field( $input['cdn_images'] ) );
			unset( $input['cdn_images'] );
		} else {
			$value = '';
		}
		switch ( $value ) {
			case 'front'	:
			case 'back'		: $output['cdn_images'] = $value;
							break;
			default			: $output['cdn_images'] = 'both';
							break;
		}

		if ( isset( $input['dyn_links'] ) ) {
			$output['dyn_links'] = ( $input['dyn_links'] == 'true' ? true : false );
			unset( $input['dyn_links'] );
		} else {
			$output['dyn_links'] = static::$options_default['dyn_links'];
		}

		if ( isset( $input['dyn_links_subst'] ) ) {
			$output['dyn_links_subst'] = ( $input['dyn_links_subst'] == 'true' ? true : false );
			unset( $input['dyn_links_subst'] );
		} else {
			$output['dyn_links_subst'] = static::$options_default['dyn_links_subst'];
		}

		// postprocessing of values
		if ( $output['cdn'] !== 'customcdn' 
			&& $output['cdn'] !== 'maxcdn' )  {
			$output['cdnurl'] = '';
		}

		delete_transient( 'wppp_cdntest' ); // cdn settings might have changed, so delete last test result
		return $output;
	}
}

?>