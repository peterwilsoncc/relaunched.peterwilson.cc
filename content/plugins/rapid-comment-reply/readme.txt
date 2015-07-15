=== Rapid Comment Reply ===
Contributors: peterwilsoncc
Tags: comments, javascript
Requires at least: 4.1
Tested up to: 4.3-alpha
Stable tag: 1.0.1
License: GPL
License URI: https://wordpress.org/about/gpl/

Reworking of WordPress's frontend comment-reply.js to be unobtrusive. Refer to trac ticket #31590.

== Description ==
Reworking of WordPress's frontend comment-reply.js to be unobtrusive. Refer to trac ticket [#31590](https://core.trac.wordpress.org/ticket/31590).

I am writing this as a plugin for the purposes of dogfooding, I hope to contribute the changes back to core.

= Browser support =

All browsers support comment replies using this plugin.

Visitors using older browsers will use a non-JavaScript fallback when replying to comments. In practical terms, on most sites the fallback will be limited to visitors using IE8 or earlier.

= Contributing =

Development of this plugin is done on [Github](https://github.com/peterwilsoncc/rapid-comment-reply). Pull requests and issue reports are welcome.

== Installation ==
Install this from your WordPress dashboard

== Changelog ==

= 1.0.1 =
* Fix incompatibility with Jetpack Comments
* Use config object for class names and IDs

= 1.0 =
* Refactor the move form code to use modern web techniques

= 0.4 =
* Move getElementByID alias out of addComment scope
* Replicate changes to link format in WordPress core

= 0.3 =

* Check for modern events and selectors in browsers (cuts the mustard)
* Set version of JavaScript file correctly
* Give class instance a PHP global
* Initialise after plugins have loaded

= 0.2 =

* Unobtrusive JS using the existing functions. 

= 0.1 =

* Initial version: replaces the WordPress comment-reply.js with the plugin's version

== Upgrade Notice ==

= 1.0.1 =
Now compatible with Jetpack comments.