=== Rapid Cache Buster ===
Contributors: peterwilsoncc
Tags: css, javascript, assets, htaccess, caching
Tested up to: 4.2
Stable tag: 1.0
License: GPL

Replace cache busting query strings with server side rewrites to improve browser side caching.

== Description ==
WordPress uses query strings for cache busting JavaScript and CSS files. 

In most circumstances this is just dandy, although in some cases visitors to your site via a proxy server may miss out on browser site caching. This plugin bypasses the effect a [poorly configured proxy server](http://www.stevesouders.com/blog/2008/08/23/revving-filenames-dont-use-querystring/).

=Read the installation guide=

In most cases you can ignore a plugins installation guide, this plugin requires changes to your  `.htaccess` file so you *must* configure it properly. Otherwise all your assets will break.

== Installation ==
= Update your server configuration =

This plugin requires you update your servers rewrites.

**Apache users**

Add the following your `.htaccess` file:

	RewriteEngine on
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_URI} ^(.*)\\.([0-9A-Fa-f]+)\\.(js|css)$
	RewriteRule ^(.*)\\.([0-9A-Fa-f]+)\\.(js|css)$ $1.$3 [L]

**nginx users**

Add the following to your configuration

	location / {
		if (!-e $request_filename){
			rewrite ^/(.*)\\.([0-9A-Fa-f]+)\\.(js|css)$ /$1.$3 break;
		}
	}

= Upload and activate the plugin =

1. Upload the plugin to  your plugins directory
1. Test the rewrites, these urls should all point to the admin-bar css
    * http://example.com/wp-includes/css/admin-bar.min.css?ver=a1234567890
    * http://example.com/wp-includes/css/admin-bar.min.a1234567890.css
    * http://example.com/wp-includes/css/admin-bar.min.css 
1. Activate the plugin through the \'Plugins\' menu in WordPress



== Frequently Asked Questions ==
= What http headers do I need to send to enable browser caching of assets? =

You need to send the `Cache-Control` and `Expires` headers. In your `.htaccess` file, the h5bp sample apache configuration has a section for setting up [browser caching using expires headers](https://github.com/h5bp/server-configs-apache/blob/master/dist/.htaccess#L836).

= This plugin broke my CSS and JavaScript =

First off, disable the plugin. Getting your site online is most important.

Please follow the instructions on the installation page for setting up server side rewrites, if you continue to have problems you may need to ask your web hosting provider for assistance. Server configurations vary widely so I can only really provide generic advice here.

If this plugin doesn\'t work with your server configuration, you can still set distant expiry headers for your CSS and JavaScript. Most visitors will still get the advantage.

= Why is there an FAQ for every plugin =

When submitting a plugin, the WordPress plugin repository checks for an FAQ in the readme.txt of every plugin. Most FAQs are just made up.

== Changelog ==
= 1.0 =
Initial version