<?php
/**
 * JIT localization of scripts using LabelsObjects
 *
 * @author Björn Ahrens
 * @since 1.8.5
 */
 
function wp_jit_default_scripts( &$scripts ) {
	include( ABSPATH . WPINC . '/version.php' ); // include an unmodified $wp_version

	$develop_src = false !== strpos( $wp_version, '-src' );

	if ( ! defined( 'SCRIPT_DEBUG' ) ) {
		define( 'SCRIPT_DEBUG', $develop_src );
	}

	if ( ! $guessurl = site_url() ) {
		$guessed_url = true;
		$guessurl = wp_guess_url();
	}

	$scripts->base_url = $guessurl;
	$scripts->content_url = defined('WP_CONTENT_URL')? WP_CONTENT_URL : '';
	$scripts->default_version = get_bloginfo( 'version' );
	$scripts->default_dirs = array('/wp-admin/js/', '/wp-includes/js/');

	$suffix = SCRIPT_DEBUG ? '' : '.min';
	$dev_suffix = $develop_src ? '' : '.min';

	$scripts->add( 'utils', "/wp-includes/js/utils$suffix.js" );
	did_action( 'init' ) && $scripts->localize( 'utils', 'userSettings', array(
		'url' => (string) SITECOOKIEPATH,
		'uid' => (string) get_current_user_id(),
		'time' => (string) time(),
		'secure' => (string) ( 'https' === parse_url( site_url(), PHP_URL_SCHEME ) ),
	) );

	$scripts->add( 'common', "/wp-admin/js/common$suffix.js", array('jquery', 'hoverIntent', 'utils'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'common', 'commonL10n', new LabelsObject( '__', array(
		'warnDelete' => "You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete."
	) ) );

	$scripts->add( 'sack', "/wp-includes/js/tw-sack$suffix.js", array(), '1.6.1', 1 );

	$scripts->add( 'quicktags', "/wp-includes/js/quicktags$suffix.js", array(), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'quicktags', 'quicktagsL10n', new LabelsObject( array(
		'closeAllOpenTags'      => array( 'func' => esc_attr__( 'Close all open tags' ) ),
		'closeTags'             => array( 'func' => esc_attr__( 'close tags' ) ),
		'enterURL'              => array( '__', 'Enter the URL' ),
		'enterImageURL'         => array( '__', 'Enter the URL of the image' ),
		'enterImageDescription' => array( '__', 'Enter a description of the image' ),
		'fullscreen'            => array( '__', 'fullscreen' ),
		'toggleFullscreen'      => array( 'func' => esc_attr__( 'Toggle fullscreen mode' ) ),
		'textdirection'         => array( 'func' => esc_attr__( 'text direction' ) ),
		'toggleTextdirection'   => array( 'func' => esc_attr__( 'Toggle Editor Text Direction' ) ),
		'dfw'                   => array( 'func' => esc_attr__( 'Distraction-free writing mode' ) )
	) ) );

	$scripts->add( 'colorpicker', "/wp-includes/js/colorpicker$suffix.js", array('prototype'), '3517m' );

	$scripts->add( 'editor', "/wp-admin/js/editor$suffix.js", array('utils','jquery'), false, 1 );

	$scripts->add( 'wp-fullscreen', "/wp-admin/js/wp-fullscreen$suffix.js", array('jquery'), false, 1 );

	$scripts->add( 'wp-ajax-response', "/wp-includes/js/wp-ajax-response$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'wp-ajax-response', 'wpAjax', new LabelsObject( array(
		'noPerm' => 'You do not have permission to do that.',
		'broken' => 'An unidentified error has occurred.'
	) ) );

	$scripts->add( 'wp-pointer', "/wp-includes/js/wp-pointer$suffix.js", array( 'jquery-ui-widget', 'jquery-ui-position' ), '20111129a', 1 );
	did_action( 'init' ) && $scripts->localize( 'wp-pointer', 'wpPointerL10n', new LabelsObject( '__', array(
		'dismiss' => 'Dismiss',
	) ) );

	$scripts->add( 'autosave', "/wp-includes/js/autosave$suffix.js", array('heartbeat'), false, 1 );

	$scripts->add( 'heartbeat', "/wp-includes/js/heartbeat$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'heartbeat', 'heartbeatSettings',
		/**
		 * Filter the Heartbeat settings.
		 *
		 * @since 3.6.0
		 *
		 * @param array $settings Heartbeat settings array.
		 */
		apply_filters( 'heartbeat_settings', array() )
	);

	$scripts->add( 'wp-auth-check', "/wp-includes/js/wp-auth-check$suffix.js", array('heartbeat'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'wp-auth-check', 'authcheckL10n', new LabelsObject( array(
		'beforeunload' => array( '__', 'Your session has expired. You can log in again from this page or go to the login page.' ),

		/**
		 * Filter the authentication check interval.
		 *
		 * @since 3.6.0
		 *
		 * @param int $interval The interval in which to check a user's authentication.
		 *                      Default 3 minutes in seconds, or 180.
		 */
		'interval' => array( 'func' => apply_filters( 'wp_auth_check_interval', 3 * MINUTE_IN_SECONDS ) ),
	) ) );

	$scripts->add( 'wp-lists', "/wp-includes/js/wp-lists$suffix.js", array( 'wp-ajax-response', 'jquery-color' ), false, 1 );

	// WordPress no longer uses or bundles Prototype or script.aculo.us. These are now pulled from an external source.
	$scripts->add( 'prototype', '//ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js', array(), '1.7.1');
	$scripts->add( 'scriptaculous-root', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js', array('prototype'), '1.9.0');
	$scripts->add( 'scriptaculous-builder', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/builder.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-dragdrop', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/dragdrop.js', array('scriptaculous-builder', 'scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-effects', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/effects.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-slider', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/slider.js', array('scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-sound', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/sound.js', array( 'scriptaculous-root' ), '1.9.0' );
	$scripts->add( 'scriptaculous-controls', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/controls.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous', false, array('scriptaculous-dragdrop', 'scriptaculous-slider', 'scriptaculous-controls') );

	// not used in core, replaced by Jcrop.js
	$scripts->add( 'cropper', '/wp-includes/js/crop/cropper.js', array('scriptaculous-dragdrop') );

	// jQuery
	$scripts->add( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), '1.11.1' );
	$scripts->add( 'jquery-core', '/wp-includes/js/jquery/jquery.js', array(), '1.11.1' );
	$scripts->add( 'jquery-migrate', "/wp-includes/js/jquery/jquery-migrate$suffix.js", array(), '1.2.1' );

	// full jQuery UI
	$scripts->add( 'jquery-ui-core', "/wp-includes/js/jquery/ui/core$dev_suffix.js", array('jquery'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-core', "/wp-includes/js/jquery/ui/effect$dev_suffix.js", array('jquery'), '1.11.2', 1 );

	$scripts->add( 'jquery-effects-blind', "/wp-includes/js/jquery/ui/effect-blind$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-bounce', "/wp-includes/js/jquery/ui/effect-bounce$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-clip', "/wp-includes/js/jquery/ui/effect-clip$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-drop', "/wp-includes/js/jquery/ui/effect-drop$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-explode', "/wp-includes/js/jquery/ui/effect-explode$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-fade', "/wp-includes/js/jquery/ui/effect-fade$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-fold', "/wp-includes/js/jquery/ui/effect-fold$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-highlight', "/wp-includes/js/jquery/ui/effect-highlight$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-puff', "/wp-includes/js/jquery/ui/effect-puff$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-pulsate', "/wp-includes/js/jquery/ui/effect-pulsate$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-scale', "/wp-includes/js/jquery/ui/effect-scale$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-shake', "/wp-includes/js/jquery/ui/effect-shake$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-size', "/wp-includes/js/jquery/ui/effect-size$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-slide', "/wp-includes/js/jquery/ui/effect-slide$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-effects-transfer', "/wp-includes/js/jquery/ui/effect-transfer$dev_suffix.js", array('jquery-effects-core'), '1.11.2', 1 );

	$scripts->add( 'jquery-ui-accordion', "/wp-includes/js/jquery/ui/accordion$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-autocomplete', "/wp-includes/js/jquery/ui/autocomplete$dev_suffix.js", array('jquery-ui-menu'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-button', "/wp-includes/js/jquery/ui/button$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-datepicker', "/wp-includes/js/jquery/ui/datepicker$dev_suffix.js", array('jquery-ui-core'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-dialog', "/wp-includes/js/jquery/ui/dialog$dev_suffix.js", array('jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-button', 'jquery-ui-position'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-draggable', "/wp-includes/js/jquery/ui/draggable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-droppable', "/wp-includes/js/jquery/ui/droppable$dev_suffix.js", array('jquery-ui-draggable'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-menu', "/wp-includes/js/jquery/ui/menu$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-mouse', "/wp-includes/js/jquery/ui/mouse$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-position', "/wp-includes/js/jquery/ui/position$dev_suffix.js", array('jquery'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-progressbar', "/wp-includes/js/jquery/ui/progressbar$dev_suffix.js", array('jquery-ui-widget'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-resizable', "/wp-includes/js/jquery/ui/resizable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-selectable', "/wp-includes/js/jquery/ui/selectable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-selectmenu', "/wp-includes/js/jquery/ui/selectmenu$dev_suffix.js", array('jquery-ui-menu'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-slider', "/wp-includes/js/jquery/ui/slider$dev_suffix.js", array('jquery-ui-mouse'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-sortable', "/wp-includes/js/jquery/ui/sortable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-spinner', "/wp-includes/js/jquery/ui/spinner$dev_suffix.js", array( 'jquery-ui-button' ), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-tabs', "/wp-includes/js/jquery/ui/tabs$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-tooltip', "/wp-includes/js/jquery/ui/tooltip$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.11.2', 1 );
	$scripts->add( 'jquery-ui-widget', "/wp-includes/js/jquery/ui/widget$dev_suffix.js", array('jquery'), '1.11.2', 1 );

	// deprecated, not used in core, most functionality is included in jQuery 1.3
	$scripts->add( 'jquery-form', "/wp-includes/js/jquery/jquery.form$suffix.js", array('jquery'), '3.37.0', 1 );

	// jQuery plugins
	$scripts->add( 'jquery-color', "/wp-includes/js/jquery/jquery.color.min.js", array('jquery'), '2.1.1', 1 );
	$scripts->add( 'suggest', "/wp-includes/js/jquery/suggest$suffix.js", array('jquery'), '1.1-20110113', 1 );
	$scripts->add( 'schedule', '/wp-includes/js/jquery/jquery.schedule.js', array('jquery'), '20m', 1 );
	$scripts->add( 'jquery-query', "/wp-includes/js/jquery/jquery.query.js", array('jquery'), '2.1.7', 1 );
	$scripts->add( 'jquery-serialize-object', "/wp-includes/js/jquery/jquery.serialize-object.js", array('jquery'), '0.2', 1 );
	$scripts->add( 'jquery-hotkeys', "/wp-includes/js/jquery/jquery.hotkeys$suffix.js", array('jquery'), '0.0.2m', 1 );
	$scripts->add( 'jquery-table-hotkeys', "/wp-includes/js/jquery/jquery.table-hotkeys$suffix.js", array('jquery', 'jquery-hotkeys'), false, 1 );
	$scripts->add( 'jquery-touch-punch', "/wp-includes/js/jquery/jquery.ui.touch-punch.js", array('jquery-ui-widget', 'jquery-ui-mouse'), '0.2.2', 1 );

	// Masonry v2 depended on jQuery. v3 does not. The older jquery-masonry handle is a shiv.
	// It sets jQuery as a dependency, as the theme may have been implicitly loading it this way.
	$scripts->add( 'masonry', "/wp-includes/js/masonry.min.js", array(), '3.1.2', 1 );
	$scripts->add( 'jquery-masonry', "/wp-includes/js/jquery/jquery.masonry$dev_suffix.js", array( 'jquery', 'masonry' ), '3.1.2', 1 );

	$scripts->add( 'thickbox', "/wp-includes/js/thickbox/thickbox.js", array('jquery'), '3.1-20121105', 1 );
	did_action( 'init' ) && $scripts->localize( 'thickbox', 'thickboxL10n', new LabelsObject( array(
			'next' => array( '__', 'Next &gt;' ),
			'prev' => array( '__', '&lt; Prev' ),
			'image' => array( '__', 'Image' ),
			'of' => array( '__', 'of' ),
			'close' => array( '__', 'Close' ),
			'noiframes' => array( '__', 'This feature requires inline frames. You have iframes disabled or your browser does not support them.' ),
			'loadingAnimation' => array( 'func' => includes_url('js/thickbox/loadingAnimation.gif') ),
	) ) );

	$scripts->add( 'jcrop', "/wp-includes/js/jcrop/jquery.Jcrop.min.js", array('jquery'), '0.9.12');

	$scripts->add( 'swfobject', "/wp-includes/js/swfobject.js", array(), '2.2-20120417');

	// error message for both plupload and swfupload
	$uploader_l10n = new LabelsObject( '__', array(
		'queue_limit_exceeded' => 'You have attempted to queue too many files.',
		'file_exceeds_size_limit' => '%s exceeds the maximum upload size for this site.',
		'zero_byte_file' => 'This file is empty. Please try another.',
		'invalid_filetype' => 'This file type is not allowed. Please try another.',
		'not_an_image' => 'This file is not an image. Please try another.',
		'image_memory_exceeded' => 'Memory exceeded. Please try another smaller file.',
		'image_dimensions_exceeded' => 'This is larger than the maximum size. Please try another.',
		'default_error' => 'An error occurred in the upload. Please try again later.',
		'missing_upload_url' => 'There was a configuration error. Please contact the server administrator.',
		'upload_limit_exceeded' => 'You may only upload 1 file.',
		'http_error' => 'HTTP error.',
		'upload_failed' => 'Upload failed.',
		'big_upload_failed' => 'Please try uploading this file with the %1$sbrowser uploader%2$s.',
		'big_upload_queued' => '%s exceeds the maximum upload size for the multi-file uploader when used in your browser.',
		'io_error' => 'IO error.',
		'security_error' => 'Security error.',
		'file_cancelled' => 'File canceled.',
		'upload_stopped' => 'Upload stopped.',
		'dismiss' => 'Dismiss',
		'crunching' => 'Crunching&hellip;',
		'deleted' => 'moved to the trash.',
		'error_uploading' => '&#8220;%s&#8221; has failed to upload.'
	) );

	$scripts->add( 'plupload', '/wp-includes/js/plupload/plupload.full.min.js', array(), '2.1.1' );
	// Back compat handles:
	foreach ( array( 'all', 'html5', 'flash', 'silverlight', 'html4' ) as $handle ) {
		$scripts->add( "plupload-$handle", false, array( 'plupload' ), '2.1.1' );
	}

	$scripts->add( 'plupload-handlers', "/wp-includes/js/plupload/handlers$suffix.js", array( 'plupload', 'jquery' ) );
	did_action( 'init' ) && $scripts->localize( 'plupload-handlers', 'pluploadL10n', $uploader_l10n );

	$scripts->add( 'wp-plupload', "/wp-includes/js/plupload/wp-plupload$suffix.js", array( 'plupload', 'jquery', 'json2', 'media-models' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'wp-plupload', 'pluploadL10n', $uploader_l10n );

	// keep 'swfupload' for back-compat.
	$scripts->add( 'swfupload', '/wp-includes/js/swfupload/swfupload.js', array(), '2201-20110113');
	$scripts->add( 'swfupload-swfobject', '/wp-includes/js/swfupload/plugins/swfupload.swfobject.js', array('swfupload', 'swfobject'), '2201a');
	$scripts->add( 'swfupload-queue', '/wp-includes/js/swfupload/plugins/swfupload.queue.js', array('swfupload'), '2201');
	$scripts->add( 'swfupload-speed', '/wp-includes/js/swfupload/plugins/swfupload.speed.js', array('swfupload'), '2201');
	$scripts->add( 'swfupload-all', false, array('swfupload', 'swfupload-swfobject', 'swfupload-queue'), '2201');
	$scripts->add( 'swfupload-handlers', "/wp-includes/js/swfupload/handlers$suffix.js", array('swfupload-all', 'jquery'), '2201-20110524');
	did_action( 'init' ) && $scripts->localize( 'swfupload-handlers', 'swfuploadL10n', $uploader_l10n );

	$scripts->add( 'comment-reply', "/wp-includes/js/comment-reply$suffix.js", array(), false, 1 );

	$scripts->add( 'json2', "/wp-includes/js/json2$suffix.js", array(), '2011-02-23');

	$scripts->add( 'underscore', "/wp-includes/js/underscore$dev_suffix.js", array(), '1.6.0', 1 );
	$scripts->add( 'backbone', "/wp-includes/js/backbone$dev_suffix.js", array( 'underscore','jquery' ), '1.1.2', 1 );

	$scripts->add( 'wp-util', "/wp-includes/js/wp-util$suffix.js", array('underscore', 'jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'wp-util', '_wpUtilSettings', array(
		'ajax' => array(
			'url' => admin_url( 'admin-ajax.php', 'relative' ),
		),
	) );

	$scripts->add( 'wp-backbone', "/wp-includes/js/wp-backbone$suffix.js", array('backbone', 'wp-util'), false, 1 );

	$scripts->add( 'revisions', "/wp-admin/js/revisions$suffix.js", array( 'wp-backbone', 'jquery-ui-slider', 'hoverIntent' ), false, 1 );

	$scripts->add( 'imgareaselect', "/wp-includes/js/imgareaselect/jquery.imgareaselect$suffix.js", array('jquery'), '0.9.10', 1 );

	$scripts->add( 'mediaelement', "/wp-includes/js/mediaelement/mediaelement-and-player.min.js", array('jquery'), '2.15.1', 1 );
	did_action( 'init' ) && $scripts->localize( 'mediaelement', 'mejsL10n', array(
		'language' => get_bloginfo( 'language' ),
		'strings'  => new LabelsObject ( '__', array(
			'Close'               => 'Close',
			'Fullscreen'          => 'Fullscreen',
			'Download File'       => 'Download File',
			'Download Video'      => 'Download Video',
			'Play/Pause'          => 'Play/Pause',
			'Mute Toggle'         => 'Mute Toggle',
			'None'                => 'None',
			'Turn off Fullscreen' => 'Turn off Fullscreen',
			'Go Fullscreen'       => 'Go Fullscreen',
			'Unmute'              => 'Unmute',
			'Mute'                => 'Mute',
			'Captions/Subtitles'  => 'Captions/Subtitles'
		) ),
	) );


	$scripts->add( 'wp-mediaelement', "/wp-includes/js/mediaelement/wp-mediaelement.js", array('mediaelement'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'mediaelement', '_wpmejsSettings', array(
		'pluginPath' => includes_url( 'js/mediaelement/', 'relative' ),
	) );

	$scripts->add( 'wp-playlist', "/wp-includes/js/mediaelement/wp-playlist.js", array( 'wp-util', 'backbone', 'mediaelement' ), false, 1 );

	$scripts->add( 'zxcvbn-async', "/wp-includes/js/zxcvbn-async$suffix.js", array(), '1.0' );
	did_action( 'init' ) && $scripts->localize( 'zxcvbn-async', '_zxcvbnSettings', array(
		'src' => empty( $guessed_url ) ? includes_url( '/js/zxcvbn.min.js' ) : $scripts->base_url . '/wp-includes/js/zxcvbn.min.js',
	) );

	$scripts->add( 'password-strength-meter', "/wp-admin/js/password-strength-meter$suffix.js", array( 'jquery', 'zxcvbn-async' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'password-strength-meter', 'pwsL10n', new LabelsObject( array(
		'empty' => array( '__', 'Strength indicator' ),
		'short' => array( '__', 'Very weak' ),
		'bad' => array( '__', 'Weak' ),
		/* translators: password strength */
		'good' => array( '_x', 'Medium', 'password strength' ),
		'strong' => array( '__', 'Strong' ),
		'mismatch' => array( '__', 'Mismatch' )
	) ) );

	$scripts->add( 'user-profile', "/wp-admin/js/user-profile$suffix.js", array( 'jquery', 'password-strength-meter', 'wp-util' ), false, 1 );
	$scripts->add( 'language-chooser', "/wp-admin/js/language-chooser$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'user-suggest', "/wp-admin/js/user-suggest$suffix.js", array( 'jquery-ui-autocomplete' ), false, 1 );

	$scripts->add( 'admin-bar', "/wp-includes/js/admin-bar$suffix.js", array(), false, 1 );

	$scripts->add( 'wplink', "/wp-includes/js/wplink$suffix.js", array( 'jquery' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'wplink', 'wpLinkL10n', new LabelsObject( '__', array(
		'title' => 'Insert/edit link',
		'update' => 'Update',
		'save' => 'Add Link',
		'noTitle' => '(no title)',
		'noMatchesFound' => 'No results found.'
	) ) );

	$scripts->add( 'wpdialogs', "/wp-includes/js/wpdialog$suffix.js", array( 'jquery-ui-dialog' ), false, 1 );

	$scripts->add( 'word-count', "/wp-admin/js/word-count$suffix.js", array( 'jquery' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'word-count', 'wordCountL10n', array(
		/* translators: If your word count is based on single characters (East Asian characters),
		   enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
		'type' => 'characters' == _x( 'words', 'word count: words or characters?' ) ? 'c' : 'w',
	) );

	$scripts->add( 'media-upload', "/wp-admin/js/media-upload$suffix.js", array( 'thickbox', 'shortcode' ), false, 1 );

	$scripts->add( 'hoverIntent', "/wp-includes/js/hoverIntent$suffix.js", array('jquery'), 'r7', 1 );

	$scripts->add( 'customize-base',     "/wp-includes/js/customize-base$suffix.js",     array( 'jquery', 'json2', 'underscore' ), false, 1 );
	$scripts->add( 'customize-loader',   "/wp-includes/js/customize-loader$suffix.js",   array( 'customize-base' ), false, 1 );
	$scripts->add( 'customize-preview',  "/wp-includes/js/customize-preview$suffix.js",  array( 'customize-base' ), false, 1 );
	$scripts->add( 'customize-models',   "/wp-includes/js/customize-models.js", array( 'underscore', 'backbone' ), false, 1 );
	$scripts->add( 'customize-views',    "/wp-includes/js/customize-views.js",  array( 'jquery', 'underscore', 'imgareaselect', 'customize-models' ), false, 1 );
	$scripts->add( 'customize-controls', "/wp-admin/js/customize-controls$suffix.js", array( 'customize-base' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'customize-controls', '_wpCustomizeControlsL10n', new LabelsObject( '__', array(
		'activate'  => 'Save &amp; Activate',
		'save'      => 'Save &amp; Publish',
		'saveAlert' => 'The changes you made will be lost if you navigate away from this page.',
		'saved'     => 'Saved',
		'cancel'    => 'Cancel',
		'close'     => 'Close',
		'cheatin'   => 'Cheatin&#8217; uh?',

		// Used for overriding the file types allowed in plupload.
		'allowedFiles' => 'Allowed Files',
	) ) );

	$scripts->add( 'customize-widgets', "/wp-admin/js/customize-widgets$suffix.js", array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-droppable', 'wp-backbone', 'customize-controls' ), false, 1 );
	$scripts->add( 'customize-preview-widgets', "/wp-includes/js/customize-preview-widgets$suffix.js", array( 'jquery', 'wp-util', 'customize-preview' ), false, 1 );

	$scripts->add( 'accordion', "/wp-admin/js/accordion$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'shortcode', "/wp-includes/js/shortcode$suffix.js", array( 'underscore' ), false, 1 );
	$scripts->add( 'media-models', "/wp-includes/js/media-models$suffix.js", array( 'wp-backbone' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'media-models', '_wpMediaModelsL10n', array(
		'settings' => array(
			'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
			'post' => array( 'id' => 0 ),
		),
	) );

	// To enqueue media-views or media-editor, call wp_enqueue_media().
	// Both rely on numerous settings, styles, and templates to operate correctly.
	$scripts->add( 'media-views',  "/wp-includes/js/media-views$suffix.js",  array( 'utils', 'media-models', 'wp-plupload', 'jquery-ui-sortable', 'wp-mediaelement' ), false, 1 );
	$scripts->add( 'media-editor', "/wp-includes/js/media-editor$suffix.js", array( 'shortcode', 'media-views' ), false, 1 );
	$scripts->add( 'media-audiovideo', "/wp-includes/js/media-audiovideo$suffix.js", array( 'media-editor' ), false, 1 );
	$scripts->add( 'mce-view', "/wp-includes/js/mce-view$suffix.js", array( 'shortcode', 'media-models', 'media-audiovideo', 'wp-playlist' ), false, 1 );

	if ( is_admin() ) {
		$scripts->add( 'admin-tags', "/wp-admin/js/tags$suffix.js", array('jquery', 'wp-ajax-response'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-tags', 'tagsl10n', new LabelsObject( '__', array(
			'noPerm' => 'You do not have permission to do that.',
			'broken' => 'An unidentified error has occurred.'
		) ) );

		$scripts->add( 'admin-comments', "/wp-admin/js/edit-comments$suffix.js", array('wp-lists', 'quicktags', 'jquery-query'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-comments', 'adminCommentsL10n', new LabelsObject( array(
			'hotkeys_highlight_first' => array( 'func' => isset($_GET['hotkeys_highlight_first']) ),
			'hotkeys_highlight_last' => array( 'func' => isset($_GET['hotkeys_highlight_last']) ),
			'replyApprove' => array( '__', 'Approve and Reply' ),
			'reply' => array( '__', 'Reply' )
		) ) );

		$scripts->add( 'xfn', "/wp-admin/js/xfn$suffix.js", array('jquery'), false, 1 );

		$scripts->add( 'postbox', "/wp-admin/js/postbox$suffix.js", array('jquery-ui-sortable'), false, 1 );

		$scripts->add( 'post', "/wp-admin/js/post$suffix.js", array('suggest', 'wp-lists', 'postbox'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'post', 'postL10n', new LabelsObject( array(
			'ok' => array( '__', 'OK' ),
			'cancel' => array( '__', 'Cancel' ),
			'publishOn' => array( '__', 'Publish on:' ),
			'publishOnFuture' =>  array( '__', 'Schedule for:' ),
			'publishOnPast' => array( '__', 'Published on:' ),
			/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
			'dateFormat' => array( '__', '%1$s %2$s, %3$s @ %4$s : %5$s' ),
			'showcomm' => array( '__', 'Show more comments' ),
			'endcomm' => array( '__', 'No more comments found.' ),
			'publish' => array( '__', 'Publish' ),
			'schedule' => array( '__', 'Schedule' ),
			'update' => array( '__', 'Update' ),
			'savePending' => array( '__', 'Save as Pending' ),
			'saveDraft' => array( '__', 'Save Draft' ),
			'private' => array( '__', 'Private' ),
			'public' => array( '__', 'Public' ),
			'publicSticky' => array( '__', 'Public, Sticky' ),
			'password' => array( '__', 'Password Protected' ),
			'privatelyPublished' => array( '__', 'Privately Published' ),
			'published' => array( '__', 'Published' ),
			'comma' => array( '_x', ',', 'tag delimiter' ),
			'saveAlert' => array( '__', 'The changes you made will be lost if you navigate away from this page.' ),
			'savingText' => array( '__', 'Saving Draft&#8230;' ),
		) ) );

		$scripts->add( 'editor-expand', "/wp-admin/js/editor-expand$suffix.js", array( 'jquery' ), false, 1 );

		$scripts->add( 'link', "/wp-admin/js/link$suffix.js", array( 'wp-lists', 'postbox' ), false, 1 );

		$scripts->add( 'comment', "/wp-admin/js/comment$suffix.js", array( 'jquery', 'postbox' ) );
		$scripts->add_data( 'comment', 'group', 1 );
		did_action( 'init' ) && $scripts->localize( 'comment', 'commentL10n', new LabelsObject( '__', array(
			'submittedOn' => 'Submitted on:'
		) ) );

		$scripts->add( 'admin-gallery', "/wp-admin/js/gallery$suffix.js", array( 'jquery-ui-sortable' ) );

		$scripts->add( 'admin-widgets', "/wp-admin/js/widgets$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), false, 1 );

		$scripts->add( 'theme', "/wp-admin/js/theme$suffix.js", array( 'wp-backbone' ), false, 1 );

		$scripts->add( 'inline-edit-post', "/wp-admin/js/inline-edit-post$suffix.js", array( 'jquery', 'suggest' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-post', 'inlineEditL10n', new LabelsObject( array(
			'error' => array( '__', 'Error while saving the changes.' ),
			'ntdeltitle' => array( '__', 'Remove From Bulk Edit' ),
			'notitle' => array( '__', '(no title)' ),
			'comma' => trim( _x( ',', 'tag delimiter' ) ),
		) ) );

		$scripts->add( 'inline-edit-tax', "/wp-admin/js/inline-edit-tax$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-tax', 'inlineEditL10n', new LabelsObject( '__', array(
			'error' => 'Error while saving the changes.'
		) ) );

		$scripts->add( 'plugin-install', "/wp-admin/js/plugin-install$suffix.js", array( 'jquery', 'thickbox' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'plugin-install', 'plugininstallL10n', new LabelsObject( '__',  array(
			'plugin_information' => 'Plugin Information:',
			'ays' => 'Are you sure you want to install this plugin?'
		) ) );

		$scripts->add( 'updates', "/wp-admin/js/updates$suffix.js", array( 'jquery' ) );

		$scripts->add( 'farbtastic', '/wp-admin/js/farbtastic.js', array('jquery'), '1.2' );

		$scripts->add( 'iris', '/wp-admin/js/iris.min.js', array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), '1.0.7', 1 );
		$scripts->add( 'wp-color-picker', "/wp-admin/js/color-picker$suffix.js", array( 'iris' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'wp-color-picker', 'wpColorPickerL10n', new LabelsObject( '__', array(
			'clear' => 'Clear',
			'defaultString' => 'Default',
			'pick' => 'Select Color',
			'current' => 'Current Color',
		) ) );

		$scripts->add( 'dashboard', "/wp-admin/js/dashboard$suffix.js", array( 'jquery', 'admin-comments', 'postbox' ), false, 1 );

		$scripts->add( 'list-revisions', "/wp-includes/js/wp-list-revisions$suffix.js" );

		$scripts->add( 'media-grid', "/wp-includes/js/media-grid$suffix.js", array( 'media-editor' ), false, 1 );
		$scripts->add( 'media', "/wp-admin/js/media$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'media', 'attachMediaBoxL10n', new LabelsObject( '__', array(
			'error' => 'An error has occurred. Please reload the page and try again.',
		) ) );

		$scripts->add( 'image-edit', "/wp-admin/js/image-edit$suffix.js", array('jquery', 'json2', 'imgareaselect'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'image-edit', 'imageEditL10n', new LabelsObject( '__', array(
			'error' => 'Could not load the preview image. Please reload the page and try again.'
		) ) );

		$scripts->add( 'set-post-thumbnail', "/wp-admin/js/set-post-thumbnail$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'set-post-thumbnail', 'setPostThumbnailL10n', new LabelsObject( '__', array(
			'setThumbnail' => 'Use as featured image',
			'saving' => 'Saving...',
			'error' => 'Could not set that as the thumbnail image. Try a different attachment.',
			'done' => 'Done'
		) ) );

		// Navigation Menus
		$scripts->add( 'nav-menu', "/wp-admin/js/nav-menu$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-lists', 'postbox' ) );
		did_action( 'init' ) && $scripts->localize( 'nav-menu', 'navMenuL10n', new LabelsObject( array(
			'noResultsFound' => array( '__', 'No results found.' ),
			'warnDeleteMenu' => array( '__', "You are about to permanently delete this menu. \n 'Cancel' to stop, 'OK' to delete." ),
			'saveAlert' => array( '__', 'The changes you made will be lost if you navigate away from this page.' ),
			'untitled' => array( '_x', '(no label)', 'missing menu item navigation label' )
		) ) );

		$scripts->add( 'custom-header', "/wp-admin/js/custom-header.js", array( 'jquery-masonry' ), false, 1 );
		$scripts->add( 'custom-background', "/wp-admin/js/custom-background$suffix.js", array( 'wp-color-picker', 'media-views' ), false, 1 );
		$scripts->add( 'media-gallery', "/wp-admin/js/media-gallery$suffix.js", array('jquery'), false, 1 );

		$scripts->add( 'svg-painter', '/wp-admin/js/svg-painter.js', array( 'jquery' ), false, 1 );
	}
}

?>