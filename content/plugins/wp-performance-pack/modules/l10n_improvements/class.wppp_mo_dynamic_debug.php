<?php
/**
 * Dynamic loading and parsing of MO files, debug version
 *
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Performance Pack
 * @since 0.1
 */

class WPPP_MO_dynamic_Debug extends WPPP_MO_dynamic {
	public $translate_hits = 0;
	public $translate_plural_hits = 0;
	public $search_translation_hits = 0;

	function import_domain_from_cache () {
		global $wp_performance_pack;
		if ( $wp_performance_pack->options['mo_caching'] ) {
			parent::import_domain_from_cache ();
			if ( ($c = count( $this->translations ) ) > 0 ) {
				$wp_performance_pack->dbg_textdomains[$this->domain]['cache'] = $c;
			}
			if ( $this->base_translations !== NULL ) {
				$wp_performance_pack->dbg_textdomains[$this->domain]['basecache'] = count( $this->base_translations );
			}
		}
	}

	function translate_plural ($singular, $plural, $count, $context = null) {
		$this->translate_plural_hits++;
		return parent::translate_plural($singular, $plural, $count, $context);
	}

	function translate ($singular, $context = null) {
		$this->translate_hits++;
		return parent::translate ($singular, $context);
	}
	
	protected function search_translation ( $key ) {
		$this->search_translation_hits++;
		return parent::search_translation( $key );
	}

	function merge_with( &$other ) {
		if ( $other instanceof WPPP_MO_dynamic_Debug ) {
			$this->translate_hits += $other->translate_hits;
			$this->translate_plural_hits += $other->translate_plural_hits;
			$this->search_translation_hits += $other->search_translation_hits;
		}
		parent::merge_with( $other );
	}
}

?>