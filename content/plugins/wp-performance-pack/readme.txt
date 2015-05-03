=== WP Performance Pack ===
Contributors: greencp, linushoppe
Tags: performance, speed, optimize, optimization, tuning, i18n, internationalization, translation, translate, l10n, localization, localize, language, languages, mo, gettext, thumbnails, images, intermediate, resize, quality, regenerate, exif, fast, upload, cdn, maxcdn, coralcdn, photon, dynamic links
Requires at least: 3.8.1
Tested up to: 4.2
Stable tag: 1.10.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Boost WordPress performance: Faster localization, (on the fly) dynamic image resizing and CDN support for images.

== Description ==

WP Performance Pack is your first choice for speeding up WordPress core the easy way, no core patching required. It features options to improve localization performance and image handling (faster upload, reduced webspace usage). Combined with CDN support for images, both on the site and in the admin area, this offers similar image acceleration as [Jetpack's Photon](http://jetpack.me/support/photon/).

= Features =

**CDN support**

* Serve (dynamically generated) images through CDN. Applies to all images uploaded via media library both in posts and backend. No need to save thumbnails locally.
* Fallback to local serving if CDN fails to return a valid response.
* Dynamic image links: Image urls are generated dynamically when displaying post content.
* Supported CDNs: CoralCDN, MaxCDN, Custom

**Improve image handling**

* Don't create intermediate images on upload.
* Dynamically create intermediate images on access.
* Either save or cache created images for fast subsequent access.
* Use EXIF thumbnail (if available) as source for thumbnail images. This improves memory and cpu usage as the source for the thumbnail is much smaller.
* Adjust quality settings for intermediate images.
* Regenerate Thumbnails integration: Hook into the thumbnail regeneration process to delete existing intermediate images. Supported plugins: [Regenerate Thumbnails](http://wordpress.org/plugins/regenerate-thumbnails/), [AJAX Thumbnail Rebuild](http://wordpress.org/plugins/ajax-thumbnail-rebuild/), [Simple Image Sizes](http://wordpress.org/plugins/simple-image-sizes/)
* [EWWW Image Optimizer](https://wordpress.org/plugins/ewww-image-optimizer/) support. If installed and activated EWWW Image Optimizer (and it's Cloud version) will be used to optimized saved intermediate images. Not saved images won't get optimized!

**Improve localization performance**

* Simple user interface to automatically set best available settings
* Dynamic loading of translation files, only loading and localizing used strings.
* Use of PHP gettext extension if available.
* Disable back end localization while maintaining front end localization.
* Allow individual users to reactivate Dashboard localization via profile setting.
* Just in time localization of javascripts (requires WordPress version >= 3.8.1).
* Caching of localizations to further improve translation performance. A persistent object cache has to be installed for this to be effective.
* [Debug Bar](http://wordpress.org/plugins/debug-bar/) integration


WPPP is available in the following languages: english, german, spanish (WebHostingHub)

== Screenshots ==

1. MO-Dynamic benchmark: Comparing front page of a "fresh" WordPress 3.8.1 installation with active apc cache using different configurations. As you can see, using MO-Dynamic with active caching is just as fast as not translating the blog or using native gettext. Benchmarked version 0.6, times are mean of four test runs measured using XDebug.
2. Settings, simple view (v1.6)
3. Localization settings, advanced view (v1.6)
4. Image settings and debugging, advanced view (v1.6)
5. Debug Bar integration (v1.0)

== Installation ==

**Requires PHP >= 5.3 and WordPress >= 3.8.1**

* Download, install and activate. Usage of MO-Dynamic is enabled by default.
* Gettext support requires PHP Gettext extension and the languages folder (*wp-content/languages*) must be writeable for php.
* Caching is only effective if a persisten object cache is installed
* Debugging requires [Debug Bar](http://wordpress.org/plugins/debug-bar/) to be installed and activated

== Frequently Asked Questions ==

= How do I check if caching works? =

Caching only works when using alternative MO implementation. To check if the cache works, activate WPPP debugging (requires [Debug Bar](http://wordpress.org/plugins/debug-bar/)) Plugin). This adds the panel *WP Performance Pack* to the Debug Bar. Textdomains using *MO_dynamic* implementation show information about translations loaded from cache. If no translations are getting loaded from cache, cache persistence isn't working.

= Which persisten object cache plugins are recommended? =

Any persisten object cache will do, but it has to be supported in your hosting environment. Check if any caches like APC, XCache, Memcache, etc. are installed on your webserver and select a suitable cache plugin respectively. File based object caches should work always and might improve performance, same goes for data base based caches. Performance gains depend on the available caching method and its configuration.

= Does WPPP support multisite? =

Localization improvements are supported on multisite installations. When installed network wide only the network admin can see and edit WPPP options.
**Image handling improvements are only available if WPPP is network activated**

= What's the difference between Dynamic Image Resizer and WPPPs dynamic images? =

In previous versions, WPPPs dynamic image resizing feature was based on [Dynamic Image Resizer](http://wordpress.org/plugins/dynamic-image-resizer/), at first with only some improvements. The first big change was a completely different way to serve the dynamically created images (using rewrite rules instead of the 404 handler), including support for the latest WordPress features. Since WPPP version 1.8 the way how creation of intermediate images at upload works also changed completely. Dynamic Image Resizer did prevent this by using different hooks called at upload. WPPP now overrides the registered image editors (those didn't exist when Dynamic Image Resizer was written) to only create the necessary metadata. This is way more robust and also works when editing images with WordPress.

According to its author, Dynamic Image Resizer is intended only as a proof of concept. You might say, WPPPs dynamic image feature is the working implementation of that proof of concept.

= Dynamic links broke my site, how do I restore static links? =

Your first try should be the button "Restore static links" in WPPP settigns advanced view. That function will also be executed on deactivation of WPPP.
If any errors occur (please post them in the support forums so I can try to improve the restore function), you can execute the following SQL query manually to restore the static links:

*UPDATE wp_posts SET post_content = REPLACE ( post_content, '{{wpppdynamic}}', 'http://your.base-url/wp-content/uploads/' )*

You have to change the base URL (third parameter of REPLACE) to your uploads URL!

== Other Notes == 

= How localization improvements work =

WPPP overrides WordPress' default implementation by using the *override_load_textdomain* hook. The fastest way for translations is using the native gettext implementation. This requires the PHP Gettext extension to be installed on the server. WPPPs Gettext implementation is based on *Bernd Holzmuellers* [Translate_GetText_Native](http://oss.tiggerswelt.net/wordpress/3.3.1/) implementation. Gettext support is still a bit tricky and having the gettext extension installed doesn't mean it will work. 

As second option WPPP features a complete rewrite of WordPress' MO imlementation: MO_dynamic (the alternative MO reader). The default WordPress implementaion loads the complete mo file right after a call to *load_textdomain*, whether any transaltions from this textdomain are needed or not. This needs quite some time and even more memory. Mo_dynamic features on demand loading. It doesn't load a mo file until the first translation call to that specific textdomain. And it doesn't load the entire mo file either, only the requested translation. Though the (highly optimized) search for an individual translation is slower, the vastly improved loading time and reduced memory foot print result in an overall performance gain.

Caching can further improve performance. When using MO_dynamic with activated caching, translations get cached using WordPress Object Cache API. Front end pages usually don't use many translations, so for all front end pages one cache is used per textdomain. Back end pages on the other hand use many translations. So back end pages get each their own individual translation cache with one *base cache* for each textdomain. This *base cache* consists of those translations that are used on all back end pages (i.e. they have been used up to *admin_init* hook). Later used translations are cached for each page. All this is to reduce cache size, which is very limited on many caching methods like APC. To even further reduce cache size, the transaltions get compressed before being saved to cache.

= How dynamic image resizing works =

Images don't get resized on upload, instead only the meta data for the resized images is created and the actual images are created on demand. WPPP extends all registered image editors to prevent creation of intermediate image sizes by overriding the *multi_resize* function. As the classes get extended dynamically this should work with an image editor implementation. Serving the intermediate sizes is done using rewrite rules. Requests to none existent intermediate images are redirected to a special PHP file which uses SHORTINIT to only load a minimum of necessary PHP code to improve performance. Redirection is done via htaccess. If the requested file does exists it is served directly.

When a none existend image is requested WPPP first checks if the full size version of the requested image exists in the database. If it does, next is checked if the requested image size corresponds to a registered image size (either one of the default sizes "thumbnail", "medium" or "large" or any by themes or plugins registered sizes). This check also tells WPPP if to crop the image while resizing. Only if this check passes the intermediate image is created. This prevents unwanted creation of thumbnails.

== Changelog ==

= 1.10.4 =

* [mo-dynamic] Minor speed improvements
* [jit] Added WP 4.2 support
* [general] More updated help texts.

= 1.10.3 =

* [dynimg] Multisite rewrite htaccess bugfix
* [native gettext] Cached mo files now stored in wp-content/languages/wppp[...] prefixed with original file name
* [jit] Bugfix: Some localizations resulted in requests to wp-admin/[Object%20object]
* [general] More help texts for modules in advanced view.

= 1.10.2 =

* [dynimg] Multisite bug fix
* [general] Multisite bug fix


= 1.10.1 =

* [general] Modules active by default now

= 1.10 =

* [general] NEW! Tabbed UI
* [general] NEW! Modules: Deactivate what you don't need
* [dynimg] NEW! Choose between two methods for serving images: SHORTINIT = true or WP_USE_THEMES = false. SHORTINIT is faster. When using WP_USE_THEMES all plugins get loaded so any installed image plugins (if the are based on WP_Image_Editor) will be used to create intermediate images. Only available via advanced view.
* [general] jQuery UI Slider Pips updated to 1.9
* [jit] added WP 4.1 and 4.1.1 support
* [general] Modularization almost complete.

= 1.9.2 =

* [dynimg] fixed wrong path for exif class
* [jit] added WP 4.0.1 support

= 1.9.1 =

* [cdn] Another fix in dynamic links and deactivated substitution. Image sources were changed to CDN URL in the database.
* [cdn] Restore static links no longer uses hardcoded table names

= 1.9 =

* [cdn] Dynamic links don't alter post content by default anymore. Substituting base URLs to improve performance is now optional. When upgrading this option will be activated if dynamic links were enabled to keep the previous behaviour. For now manual restore of static URLs is required when deactivating substitution.
* [dynimg] fixes in flushing rewrite rules
* [general] more progress on modularization (haven't had much time lately to make the progress I had planned...)

= 1.8.7 =

* [general] again a bugfix in plugin deactivation
* [general] still working on modularization of WPPP
* [l10n] added spanish translations (thanks to WebHostingHub)

= 1.8.6 =

* [general] bugfix in plugin deactivation

= 1.8.5 =

* [jit] WordPress 4.0 support
* [general] still refactoring code
* [cdn] restore static links displays errors if any occur (see FAQ for manual restore)

= 1.8.4 =

* [dynimg] fixed a stupid permalink bug
* [l10n] german translation correntions

= 1.8.3 =

* [dynimg] multisite support added - only available when network activated
* [general] further internal refactoring
* [general] updated jQuery UI Slider Pips to v1.6.1
* [l10n] translations updated (added missing texts, fixed some german translations)

= 1.8.2 =

* [dynimg] bugfix: prevent duplicate declaration of WPPP image editor classes (could cause problems with media manager)
* [l10n] translations updated, german translation included

= 1.8.1 =

* [jit] WordPress 3.9.2 support
* [mo-dynamic] Using MO dynamic no longer breaks plugin/theme updates. WP failed to delete plugin/theme folder because some language files were still opened. WPPP now unloads all language files prior to any upgrade.

= 1.8 =

* [dynimg] reworked internals, now also works when editing images in WordPress
* [mo-dynamic] scope issue resolved (get_byteorder copied from class MO into MO_dynamic)
* [general] more internal code changes (still work in progress)
* [cdn] option to restore static links (links will be restored automatically on deactivation)

= 1.7.6 =

* [general] (stupid) bug fixed ( "Undefined class constant '_options_name'...")

= 1.7.5 =

* [dynimg] bugfix in EWWW Image Optimizer integration
* [native gettext] translations API compatibility
* [general] bugfix: error on activation caused by update check
* [general] I'm working on some mayor code changes (work in progress)

= 1.7.4 =

* [mo-dynamic] Added test for mo file integrity. Corrupted mo files could cause an "Allowed memory size exhausted" error (e.g. the french translation of All In One SEO Pack 2.1.6).

= 1.7.3 =

* [cdn] bugfix in advanced view: CDN URL didn't get saved
* [cdn] improved CDN test: warn about missing CDN URL, only display CDN test result if cdn is activated

= 1.7.2 =

* [cdn] better feedback on active cdn settings
* [cdn] some small ui changes

= 1.7.1 = 

* [cdn] bugfix caused by duplicate in wp_get_attachment_url filter

= 1.7 =

* [cdn] **NEW** CDN support
* [cdn] **NEW** Dynamic image linking

= 1.6.6 =

* [jit] added WordPress 3.9.1 support
* [general] fixed issue that could cause a warning when switching views

= 1.6.5 =

* [general] more bugfixing, options should get saved again

= 1.6.4 =

* [general] bugfix on simple view

= 1.6.3 =

* [dynimg] EWWW Image Optimizer support (only when "No Save" option is disabled!)
* [general] misc. code refactoring

= 1.6.2 =

* [dynimg] disabling image settings on multisite installations (as it isn't supported on multisite)
* [wpmu] view switch fixed

= 1.6.1 = 

* [general] bugfix in simple view which could break the settings page

= 1.6 =

* [dynimg] Added AJAX Thumbnail Rebuild and Simple Image Sizes integration
* [dynimg] EXIF use for all image sizes smaller than 321x321 (if the EXIF thumbnail is bigger)
* [dynimg] added simple view for dynamic image resizing
* [general] misc. code refactoring
* [general] misc. ui changes
* [l10n] removed plugin translations as too many texts have changed (will be readded with the next update)

= 1.5 =

* [jit] WordPress 3.9 support
* [dynimg] NEW! Adjust quality for newly created intermediate images.
* [general] misc. code cleanup
* [general] misc. UI changes
* [general] jQuery UI Slider Pips updated to 1.4.0

= 1.4 =

* [jit] WordPress 3.8.3 included
* [dynimg] NEW! use exif thumbnails for thumbail images, requires exif extension
* [dynimg] check for pretty permalinks and regenrate thumbnails
* [general] misc. ui changes

= 1.3.1 =

* [dynimg] bugfix: thumbnail quality was set to 10 instead of 80 - will be user selectable in future version

= 1.3 =

* [dynimg] NEW! Regenerate Tumbnails integration to delete old/existing thumbnails
* [dynimg] only serve images uploaded via media library and existing image sizes
* [dynimg] bugfix for missing wp_basename
* [general] misc. smaller fixes and changes
* [general] UI changes (accordion removed)

= 1.2.2 =

* [jit] added WordPress 3.8.2 support

= 1.2.1 =

* [dynimg] bug fixed which caused intermedate images to be created on upload
* [multisite] bugfix: settings page is displayed again

= 1.2 =

* [dynimg] optional caching of intermediate images using WP Cache API
* [dynimg] automatically flush rewrite rules on feature activation/deactivation
* [backend translation] moved user override option to personal options
* [general] changed all files' encoding to utf-8
* [general] php version check on activation

= 1.1 =

* NEW dynamic image resizing
* [translation] uasort-warning bugfix

= 1.0 =

* [mo-dynamic] cache soft expire
* [mo-dynamic] optimizations (faster hash calculation) and code cleanup
* [mo-dynamic] object cache test now checks existence of object-cache.php and class name of wp_object_cache
* [override textdomain] bugfix so alternative folders for theme and plugin translations are searched again
* [l10n] textdomain added to plugin description
* [native gettext] bugfix in native gettext test
* [debug] reworked display of loaded textdomains
* [debug] show cached translation count when using mo-dynamic and caching
* [general] added uninstall to clean up created translations from native gettext

= 0.9 =

* [mo-dynamic] mo table caching removed (small speed improvement vs. big cache usage)
* [mo-dynamic] reduced cache space usage (reused admin "base" translations, data compression)
* [mo-dynamic] some small fixes
* [general] more refactoring to reduce loaded code
* [l10n] texts and translations updated

= 0.8 =

* [jit] fixed broken file upload (e.g. when editing posts)
* [general] code refactoring to reduce loaded code
* [general] selectable user default for backend transaltion if allow override is enabled
* [l10n] translations updated

= 0.7.3 =

* [general] file encoding could cause problems

= 0.7.2 =

* [general] script bugfix in simple view

= 0.7.1 =

* [general] bugfix: save settings changed view

= 0.7 =

* [general] new user interface with simple and advanced view
* [general] extended tests for support of gettext, object cache and jit
* [mo-dynamic] bugfix: removed HTML illegal chars from some translations

= 0.6.2 =

* [jit] script l10n now works with bwp minify, and hopefully other script minify plugins as well

= 0.6.1 =

* [jit] no jit when *IFRAME_REQUEST* is defined (broke theme customize)
* [jit] fixed multiple localizations per handle

= 0.6 =

* [mo-dynamic] use hash table if mo file contains one
* [mo-dynamic] optional caching implemented

= 0.5.2 =

* [debug] show translation calls when using MO-Dynamic
* [debug] test if WPPP is loaded as first plugin
* [l10n] translations updated

= 0.5.1 =

* [debug] show class used for textdomain
* [debug] added debugging option, so WP_DEBUG isn't required anymore
* [l10n] translations updated

= 0.5 =

* [native gettext] langage directory set to WP_LANG_DIR
* [general] allow user override to reactivate backend translation
* [debug] Debug Bar integration for debugging
* [l10n] translations updated

= 0.4 =

* [l10n] german translation added
* [general] admin interface reworked
* [native gettext] use of LC_MESSAGES instead of LC_ALL
* [native gettext] append codeset to locale

= 0.3 =

* [general] added multisite support (network installation)

= 0.2.4 =

* [jit] bugfixs in LabelsObject and WP_Scripts_override

= 0.2.3 =

* [jit] complete rework of JIT localize - it shouldn't break scripts anymore
* [general] bugfix in changing plugin load order (WPPP has to be the first plugin to be loaded)

= 0.2.2 =

* [general] bugfix in form validation
* [native gettext] test if *putenv* is disabled

= 0.2.1 =

* [mo-dynamic] bugfix - empty string got translated to headers
* [mo-dynamic] performance tweaking
* [native gettext] possible multisite fix - using get_locale instead of global $locale

= 0.2 =

* [general] added native gettext support using *Bernd Holzmuellers* [Translate_GetText_Native](http://oss.tiggerswelt.net/wordpress/3.3.1/) implementation 
* [general] Just in time script localization (WP 3.6 and 3.8.1 supported)

= 0.1 =

* Initial release
