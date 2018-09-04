=== Admin Bar Disabler ===
Contributors: sc0ttkclark
Donate link: https://www.scottkclark.com/
Tags: admin bar, admin menu, toolbar
Requires at least: 3.1
Tested up to: 4.9.8
Stable tag: 1.4

== Description ==

Pretty easy setup.. just install and activate it on the site of your choice (or network activate) and setup the settings however you'd like.

This plugin supports both Per-Site and Per-Network (WordPress Multisite) settings, so you can fine tune your options based on your needs.

Other plugins you might be interested in:

* Showing the Admin Bar in more cases - [Always Show Admin Bar plugin](http://wordpress.org/plugins/always-show-admin-bar/)
* Showing the Admin Bar to logged out users - [Logged Out Admin Bar plugin](http://wordpress.org/plugins/logged-out-admin-bar/)

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP plugin page)
1. Activate this plugin

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

== Screenshots ==

1. **Admin Bar Disabler Settings** - Set up per-site settings or if this plugin is activated network-wide then you can also set up network-wide settings through the Network Admin.

== Changelog ==

= 1.4 - September 3rd, 2018 =
* Fixed: Roles/capabilities blacklist logic is no longer inverted
* Fixed: Set priority of show_admin_bar to 999 to prevent conflict with other plugins

= 1.3 - January 8th, 2015 =
* Fixed: load_plugin_textdomain usage

= 1.2 - August 4th, 2014 =
* Fixed: 3.x personal option for Admin Bar toggle

= 1.1 - April 22nd, 2014 =
* Updated: Code refactor and logic cleanup
* Updated: Tested up to 3.9
* Fixed: Now showing admin bar when in admin area, previously hidden
* Fixed: Escape values in admin pages

= 1.0.3 - September 7th, 2012 =
* Bug fix on button, tested up to 3.5

= 1.0.2 - April 15th, 2011 =
* Localized plugin, thanks for the help from Anja Fokker

= 1.0.1 - February 24th, 2011 =
* Bug fix for warnings and added additional help text to settings pages

= 1.0 - February 24th, 2011 =
* Initial release