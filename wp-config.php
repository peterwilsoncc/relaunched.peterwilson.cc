<?php
// ===================================================
// Load database info and local development parameters
// ===================================================
if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
	include( dirname( __FILE__ ) . '/local-config.php' );
} else {
	define( 'WP_LOCAL_DEV', false );
	define( 'DB_NAME', '%%DB_NAME%%' );
	define( 'DB_USER', '%%DB_USER%%' );
	define( 'DB_PASSWORD', '%%DB_PASSWORD%%' );
	define( 'DB_HOST', '%%DB_HOST%%' ); // Probably 'localhost'
}

// ========================
// Custom Content Directory
// ========================
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/content' );
define( 'WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content' );

// ================================================
// You almost certainly do not want to change these
// ================================================
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// ==============================================================
// Salts, for security
// Grab these from: https://api.wordpress.org/secret-key/1.1/salt
// These are defaults, real values should be definded in local-config
// ==============================================================
if ( !defined('AUTH_KEY') )
	define('AUTH_KEY',         '0jBHF&9h1%qies|<eY4W|a}-Jq*l<y2f%4U?}*tdRqMfkx$r[&G@F_|avm]G-F`m');
if ( !defined('SECURE_AUTH_KEY') )
	define('SECURE_AUTH_KEY',  'p++861;UB>7v`G4gYB3:0+DW!<8xv&n|og8+Fkx,d6(i-w&-wtLz`zKS`fGopZ5~');
if ( !defined('LOGGED_IN_KEY') )
	define('LOGGED_IN_KEY',    '+bW4Quj@o){-;I-JV2ZWQcCp4%o0DgsDGq|q{gEii*Hr&gm4jUO H!Y>-j9@wDkt');
if ( !defined('NONCE_KEY') )
	define('NONCE_KEY',        '_y(#0Rc+l/!CZ&I|Sm*(X;7W&(YgxVX:LcI :t>%cjIYax7k!5xFVT/&ZQn,o%u8');
if ( !defined('AUTH_SALT') )
	define('AUTH_SALT',        'H$-0^ez+o;.tFzH}qX9 KCq9x0%O?`wQd4wa@^&Fd0hAji)pM-qec-i)|*OIVVeJ');
if ( !defined('SECURE_AUTH_SALT') )
	define('SECURE_AUTH_SALT', 'J9`c-q?1fur:.8Y6o,lxeVhbE0DW-f0u7ioa9Mx@`7ufVVfJU[L1;Ba [W@KP4`c');
if ( !defined('LOGGED_IN_SALT') )
	define('LOGGED_IN_SALT',   's;M0U85DF)!AOPul$h7}23`PH(3KQ#Iqxt>`Wp57LbIUru^<Zb8%)f;LHUQ( HD9');
if ( !defined('NONCE_SALT') )
	define('NONCE_SALT',       'gVL.Vyu+<Ugz%cj3]vuNosot2xv[Ag$a2K~z;/e&8QjxEsM/rv<nRQ(lw)_||6^Y');
// ==============================================================
// Table prefix
// Change this if you have multiple installs in the same database
// ==============================================================
$table_prefix  = 'wp_';

// ================================
// Language
// Leave blank for American English
// ================================
define( 'WPLANG', '' );

// ===========
// Hide errors
// ===========
if ( true !== PWCC_DEBUG_DISPLAY ) {
	ini_set( 'display_errors', 0 );
	define( 'WP_DEBUG_DISPLAY', false );
}

// ===================
// Misc config options
// ===================
define( 'WP_POST_REVISIONS', 3 );
define('FORCE_SSL_ADMIN', true);


// =================================================================
// Debug mode
// Debugging? Enable these. Can also enable them in local-config.php
// =================================================================
// define( 'SAVEQUERIES', true );
// define( 'WP_DEBUG', true );

// ======================================
// Load a Memcached config if we have one
// ======================================
if ( file_exists( dirname( __FILE__ ) . '/memcached.php' ) )
	$memcached_servers = include( dirname( __FILE__ ) . '/memcached.php' );

// ===========================================================================================
// This can be used to programatically set the stage when deploying (e.g. production, staging)
// ===========================================================================================
define( 'WP_STAGE', '%%WP_STAGE%%' );
define( 'STAGING_DOMAIN', '%%WP_STAGING_DOMAIN%%' ); // Does magic in WP Stack to handle staging domain rewriting

// ===================
// Bootstrap WordPress
// ===================
if ( !defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/wp/' );
require_once( ABSPATH . 'wp-settings.php' );
