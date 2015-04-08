<?php
/**
 * Override of WordPress WP_Scripts class
 *
 * Delays localization into print_extra_script.
 *
 * @author Björn Ahrens
 * @package WP Performance Pack
 * @since 0.2.3
 */

 
class WPPP_Scripts_Override extends WP_Scripts {
	var $l10ns = array ();

	function print_extra_script( $handle, $echo = true ) {
		if ( isset( $this->l10ns[$handle] ) ) {
			if ( $this->get_data( $handle, 'data' ) === '//%wppp_dummy%' ) {
				parent::add_data( $handle, 'data', '' ); // clear dummy value - for details see below
			}
			foreach ( $this->l10ns[$handle] as $l10n ) {
				$this->jit_localize ( $handle, $l10n['name'], $l10n['l10n'] );
			}
			unset( $this->l10ns[$handle] );
		}

		return parent::print_extra_script( $handle, $echo );
	}

	/**
	 * Localizes a script
	 *
	 * Localizes only if the script has already been added
	 */
	function localize ( $handle, $object_name, $l10n) {
		if ( $handle === 'jquery' )
			$handle = 'jquery-core';

		if ( !isset( $this->registered[$handle] ) )
			return false;

		if ( !isset( $this->l10ns[$handle] ) ) {
			$this->l10ns[$handle] = array();
		}
		$this->l10ns[$handle][] = array ('name' => $object_name, 'l10n' => $l10n);

		parent::add_data( $handle, 'data', '//%wppp_dummy%' ); 	// set dummy data - plugins like bwp minify check the extra['data'] 
																// if a scripts needs l10n - as wppp localizes jit this isn't set 
																// until print_extra_script, which in turn never gets called because
																// extra['data'] wasn't set. to prevent this the dummy value is set.
																// this works only until some plugin uses that dummy value directly...

		return true;
	}
	
	function jit_localize( $handle, $object_name, $l10n ) {
		if ( $handle === 'jquery' )
			$handle = 'jquery-core';

		if ( is_array($l10n) && isset($l10n['l10n_print_after']) ) { // back compat, preserve the code in 'l10n_print_after' if present
			$after = $l10n['l10n_print_after'];
			unset($l10n['l10n_print_after']);
		}

		if ( $l10n instanceof LabelsObject ) {
			$jit_l10n = array();
			foreach ( $l10n as $key => $value ) {
				if ( !is_scalar($value) )
					$jit_l10n[$key] = $value;
				else
					$jit_l10n[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
			}
			$script = "var $object_name = " . json_encode($jit_l10n) . ';';
		} else {
			foreach ( (array) $l10n as $key => $value ) {
				if ( !is_scalar($value) )
					continue;
				$l10n[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
			}
			$script = "var $object_name = " . json_encode($l10n) . ';';
		}

		if ( !empty($after) )
			$script .= "\n$after;";

		$data = $this->get_data( $handle, 'data' );

		if ( !empty( $data ) )
			$script = "$data\n$script";

		return $this->add_data( $handle, 'data', $script );
	}
	
	public function add_data( $handle, $key, $value ) {
		if ( $key == 'data' && $this->get_data( $handle, 'data' ) == '//%wppp_dummy%' ) {
			$value = str_replace( '//%wppp_dummy%\n', '', $value );
			$value = str_replace( '//%wppp_dummy%', '', $value );
		}
		return parent::add_data( $handle, $key, $value );
	}
}
