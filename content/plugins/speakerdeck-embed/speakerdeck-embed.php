<?php

/*
Plugin Name: Speaker Deck Embed
Description: Embed Speaker Deck slideshows
Version: 1.2
Author: Matt Wiebe
Author URI: http://somadesign.ca/
*/

add_action( 'init', 'speakerdeck_add_speakerdeck_oembed' );
function speakerdeck_add_speakerdeck_oembed() {
	wp_oembed_add_provider( '#https?://speakerdeck.com/.*#i', 'https://speakerdeck.com/oembed.json', true );
}

/**
 * Shortcode support
 * Usage is either:
 * - [speakerdeck https://speakerdeck.com/u/maxcutler/p/hack-with-me-unit-and-behavioral-tests]
 * - [speakerdeck url='https://speakerdeck.com/u/maxcutler/p/hack-with-me-unit-and-behavioral-tests']
 */
add_shortcode( 'speakerdeck', 'speakerdeck_shortcode' );
function speakerdeck_shortcode( $atts ) {
	global $wp_embed;

	if ( ! empty( $atts[0] ) )
		$url = esc_url_raw( $atts[0] );
	else if ( ! empty( $atts['url'] ) )
		$url = esc_url_raw( $atts['url'] );
	else
		return '';

	// Handles caching
	return $wp_embed->shortcode( $atts, $url );
}