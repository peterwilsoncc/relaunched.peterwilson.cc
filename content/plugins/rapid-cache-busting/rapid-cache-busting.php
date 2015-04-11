<?php
/*
Plugin Name: Rapid Cache Busting
Version: 1.0
Author: Peter Wilson
Author URI: https://peterwilson.cc/
Description: Uses filename cache busting for assets ratehr than query string. WARNING: Requires rewrite changes
License: GPLv2 or later
*/

class PWCC_Rapid_Cache_Busting {
	
	var $managed_hosts;
	
	function __construct(){
		// This only works on hosts managed by the site owner.
		add_action( 'wp_enqueue_scripts', array( $this, 'configure_managed_hosts' ), 1 );
		
		// Filter CSS and JS assets only
		add_filter( 'style_loader_src',  array( $this, 'filter_asset_src' ) );
		add_filter( 'script_loader_src', array( $this, 'filter_asset_src' ) );
	}
	
	function configure_managed_hosts() {
		// by default, only the home_url host is considered managed
		$this->managed_hosts = array( parse_url( home_url(), PHP_URL_HOST ) );
		
		// allow site owners to filter the managed hosts
		$this->managed_hosts = apply_filters( 'pwcc_rapid_cache_busting_managed_hosts', $this->managed_hosts );
		
		// ensure the managed hosts is an array
		if ( ! is_array( $this->managed_hosts ) ) {
			$this->managed_hosts = preg_split( '#[\s,]+#', $this->managed_hosts );
		}
		
	}

	function filter_asset_src( $src ) {
		$src_info = parse_url( $src );
		$managed_hosts = $this->managed_hosts;
		$is_managed = false;
		
		foreach ( $managed_hosts as $host ) {
			if ( $src_info['host'] == $host ) {
				$is_managed = true;
			}
		}
		
		if ( false == $is_managed ) {
			// not hosted on this server
			// assume no control over rewrites
			return $src;
		}
		
		
		// extract the version info from the src
		parse_str( $src_info['query'], $arguments );
		$ver = $arguments['ver'];
		
		// remove the ver querystring
		$src = remove_query_arg( 'ver', $src );

		// get the path info
		$path_info = pathinfo( $src );
		
		// get the file extension
		$ext = $path_info['extension'];
		
		// get the path without the file extension
		$src_sans_ext = $path_info['dirname'] . '/' . $path_info['filename'];
		
		// hash the version info so chars are only [0-9abcdef]
		$ver = md5( $ver );

		
		$src = $src_sans_ext . '.' . $ver . '.' . $ext;
		
		return $src;
	}

}

new PWCC_Rapid_Cache_Busting();