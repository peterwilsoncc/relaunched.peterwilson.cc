<?php
/*
Plugin Name: Rapid Canonical URLs
Version: 0.2.1
Author: Peter Wilson
Author URI: http://peterwilson.cc/
Description: Reduce 301 redirects and HTTP requests by using HTML5’s history API to show visitors the correct, canonical URL.
License: GPLv2 or later
*/

// Exit if this file is directly accessed
if ( !defined( 'ABSPATH' ) ) exit;

$pwcc_rcu_initial_redirect = false;
$pwcc_rcu_canonical_url = false;


function pwcc_rcu_check_redirect_canonical( $redirect_url ) {
	global $pwcc_rcu_initial_redirect;
	
	$pwcc_rcu_initial_redirect = $redirect_url;
	
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'pwcc_rcu_check_redirect_canonical', 0 );

	
function pwcc_rcu_filter_redirect_canonical( $redirect_url, $requested_url )	{
	global $pwcc_rcu_initial_redirect, $pwcc_rcu_canonical_url;
	
	if ( false == $redirect_url ) {
		// no redirect required
		return false;
	}
	
	$redirect_parsed = parse_url( $redirect_url );
	$requested_parsed = parse_url( $requested_url );
	
	if (
		( $pwcc_rcu_initial_redirect == $redirect_url ) &&
		( $redirect_parsed['scheme'] == $requested_parsed['scheme'] ) &&
		( $redirect_parsed['host'] == $requested_parsed['host'] ) &&
		( !is_404() )
	   ) {
		// redirect is on same domain and is not
		// recovering from a 404 error
		$via_js = true;
	}
	else {
		// cross domain redirects don't work
		// if redirecting from a 404, a real redirect is needed.
		$via_js = false;
	}
	
	if ( true == $via_js ) {
		// set up to use the history API. 
		$pwcc_rcu_canonical_url = $redirect_url;
		add_action( 'wp_head', 'pwcc_rcu_action_history_replace', 1 );
		return false;
	} else {
		// return the original URL, allowing the redirect to go ahead
		return $redirect_url;
	}
	
}
add_filter( 'redirect_canonical', 'pwcc_rcu_filter_redirect_canonical', 99, 2 );


function pwcc_rcu_action_history_replace() {
	global $pwcc_rcu_canonical_url;
	// output the javascript. The output version is compressed.
	echo '<script>';
	echo "(function(w,u,h){";
	echo "h=w.history;";
	echo "if(h.replaceState){";
	echo "h.replaceState({u:u},'',u+w.location.hash);";
	echo "}";
	echo "}(this,'$pwcc_rcu_canonical_url'))";
	echo '</script>' . "\n";
}

