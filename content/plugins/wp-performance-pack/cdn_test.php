<?php
define( 'SHORTINIT', true );

// search and load wp-load.php
$folder = dirname( __FILE__ );
while ( $folder != dirname( $folder ) ) {
	if ( file_exists( $folder . '/wp-load.php' ) ) {
		break;
	} else {
		$folder = dirname( $folder );
	}
}
require( $folder . '/wp-load.php' ); // will fail if while loop didn't find wp-load.php

require( ABSPATH . 'wp-includes/link-template.php' ); // required for site_url()
require( ABSPATH . 'wp-includes/formatting.php' ); // requried for untrailingslashit()

echo 'WPPP CDN TEST ', site_url();
?>