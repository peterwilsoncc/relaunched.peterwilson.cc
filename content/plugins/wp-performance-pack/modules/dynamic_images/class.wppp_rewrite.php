<?php

class WPPP_Rewrite extends WP_Rewrite {
	public $wppp_method = '';
	public $wppp_enabled = false;

	function flush_rules($hard = true) {
		delete_option('rewrite_rules');
		$this->wp_rewrite_rules();
		/**
		 * Filter whether a "hard" rewrite rule flush should be performed when requested.
		 *
		 * A "hard" flush updates .htaccess (Apache) or web.config (IIS).
		 *
		 * @since 3.7.0
		 *
		 * @param bool $hard Whether to flush rewrite rules "hard". Default true.
		 */
		if ( ! $hard || ! apply_filters( 'flush_rewrite_rules_hard', true ) ) {
			return;
		}
		/* if ( function_exists( 'save_mod_rewrite_rules' ) )
			save_mod_rewrite_rules();
		if ( function_exists( 'iis7_save_url_rewrite_rules' ) )
			iis7_save_url_rewrite_rules(); */

		// call own save_mod_rewrite_rules function
		$this->save_mod_rewrite_rules();
	}

	function save_mod_rewrite_rules() {
		// copy from network.php function network_step2 line 319
		$slashed_home      = trailingslashit( get_option( 'home' ) );
		$base              = parse_url( $slashed_home, PHP_URL_PATH );
		$document_root_fix = str_replace( '\\', '/', realpath( $_SERVER['DOCUMENT_ROOT'] ) );
		$abspath_fix       = str_replace( '\\', '/', ABSPATH );
		$home_path         = 0 === strpos( $abspath_fix, $document_root_fix ) ? $document_root_fix . $base : get_home_path();
		$wp_siteurl_subdir = preg_replace( '#^' . preg_quote( $home_path, '#' ) . '#', '', $abspath_fix );
		$rewrite_base      = ! empty( $wp_siteurl_subdir ) ? ltrim( trailingslashit( $wp_siteurl_subdir ), '/' ) : '';

		$subdomain_install = defined( 'SUBDOMAIN_INSTALL' ) && ( SUBDOMAIN_INSTALL == true );
		$subdir_match          = $subdomain_install ? '' : '([_0-9a-zA-Z-]+/)?';
		$subdir_replacement_01 = $subdomain_install ? '' : '$1';
		$subdir_replacement_12 = $subdomain_install ? '$1' : '$2';

		// TODO: not sure if ms_files_rewriting works with WPPP - have to check that
		$ms_files_rewriting = '';
		if ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) {
			$ms_files_rewriting = "\n# uploaded files\nRewriteRule ^";
			$ms_files_rewriting .= $subdir_match . "files/(.+) {$rewrite_base}" . WPINC . "/ms-files.php?file={$subdir_replacement_12} [L]" . "\n";
		}

		if ( $this->wppp_enabled ) {
			if ( $this->wppp_method === 'use_themes' ) {
				$wppp_file = 'serve-dynamic-images-ut.php';
			} else {
				$wppp_file = 'serve-dynamic-images.php';
			}
			$wppp = "RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)-([0-9]+)x([0-9]+)?\.((?i)jpeg|jpg|png|gif) {$rewrite_base}wp-content/plugins/wp-performance-pack/modules/dynamic_images/{$wppp_file} [QSA,L]";
		} else {
			$wppp = '';
		}

		$htaccess_file = $home_path.'.htaccess';
		$htaccess_contents = <<<EOF
RewriteEngine On
RewriteBase {$base}
RewriteRule ^index\.php$ - [L]
{$wppp}
{$ms_files_rewriting}
# add a trailing slash to /wp-admin
RewriteRule ^{$subdir_match}wp-admin$ {$subdir_replacement_01}wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^{$subdir_match}(wp-(content|admin|includes).*) {$rewrite_base}{$subdir_replacement_12} [L]
RewriteRule ^{$subdir_match}(.*\.php)$ {$rewrite_base}$subdir_replacement_12 [L]
RewriteRule . index.php [L]
EOF;

		// If the file doesn't already exist check for write access to the directory and whether we have some rules.
		// else check for write access to the file.
		
		// TODO: just using got_mod_rewrite can cause errors as it seems this may get called too early for 
		// got_mod_rewrite to have been loaded - have to look into that.
		$mod_rewrite_enabled = function_exists('got_mod_rewrite') ? got_mod_rewrite() : false;
		if ( ( $mod_rewrite_enabled ) && ( ( !file_exists( $htaccess_file ) && is_writable( $home_path ) && $wp_rewrite->using_mod_rewrite_permalinks() ) || is_writable( $htaccess_file ) ) ) {
			$rules = explode( "\n", $htaccess_contents );
			insert_with_markers( $htaccess_file, 'WordPress', $rules );
		}
	}
}

?>