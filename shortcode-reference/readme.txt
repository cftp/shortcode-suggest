=== Shortcode Reference ===
Contributors: bartee
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7T3QDZA9SP7D2
Tags: shortcode, reference, post, page, links
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: trunk

This plugin will provide a list and details about available shortcodes in your current installment. All when you need it most - when editing content.  

== Description ==

One of the fancy things within Wordpress-plugins, is the availability of shortcodes. 
These codes will provide access to plugin-specific things, like displaying a gallery, or a Google-Map. 
The downside about this, is that there's no generic overview of all available shortcodes within your environment.

This plugin will provide a list of all available shortcodes, right where the action is. When you're editing your content. And it won't skip the details: it'll show you what its origin is. 
Most of all, if it's available in the sourcecode, the documentation will be shown. 

The plugin is largely based on [PHP5's Reflection functionality](http://php.net/manual/en/book.reflection.php), and therefore only available from PHP version 5.0.0. 
  

== Installation ==

To install Shortcode Reference:

1. Download & unzip the 'shortcode-reference.zip' into the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You'll have an extra meta-box when editing posts, pages and links, providing realtime information about your available shortcodes and how to use them - if the code they use is  documented properly

== Frequently Asked Questions ==

N/A

== Screenshots ==

1. This little meta-box will be provided when you're editing posts, pages or links.

== Upgrade notice == 

It's only a first release, so there's nothing to upgrade.

== Changelog ==
= 0.2 = 
* Added reference box to all public post types. 
* Changed resolving of the plugin's css/js files
* Made file lookup case sensitive

= 0.1 =
* First release
* Get a list of all available shortcodes in a meta-box when editing pages or posts.
* Get the details of a shortcode
* Get or generate a link to a place where more info is available
