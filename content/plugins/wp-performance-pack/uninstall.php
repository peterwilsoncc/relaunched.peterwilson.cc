<?php
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

function rmdir_recursive($dir) {
	foreach( scandir( $dir ) as $file ) {
		if ( '.' === $file || '..' === $file ) continue;
		if ( is_dir( "$dir/$file" ) ) rmdir_recursive( "$dir/$file" );
		else unlink( "$dir/$file" );
	}
	rmdir( $dir );
}

$locale=get_locale();
rmdir_recursive( WP_LANG_DIR . '/' . $locale );
?>