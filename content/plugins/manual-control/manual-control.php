<?php
/*
Plugin Name: Manual Control for Jetpack
Description: Prevents the Jetpack plugin from auto-activating its new features.
Version: 0.2
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

class CWS_Manual_Control_for_Jetpack_Plugin {
	static $instance;

	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ), 11 );
	}

	function init() {
		add_filter( 'jetpack_get_default_modules', array( $this, 'empty_array' ), 99 );
	}

	function empty_array() {
		return array();
	}
}

new CWS_Manual_Control_for_Jetpack_Plugin;
