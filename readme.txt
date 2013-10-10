=== Clever SEO Keywords ===
Contributors: MMDeveloper
Donate link: 
Tags: seo, plugin, keyword, keywords, meta, metadata, description
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Auto create keywords based on your headings.

== Description ==

A wordpress plugin that allows you to auto create metadata keywords/description based on the headings within your pages.

This plugin uses Simple Html DOM, for info go to http://sourceforge.net/projects/simplehtmldom/

== Installation ==

1) Install WordPress 3.6 or higher

2) Download the latest from:

http://wordpress.org/extend/plugins/tom-m8te 

http://wordpress.org/extend/plugins/clever-seo-keywords

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.


== Changelog ==

= 4.2 =

* Bug fix, this bug isn't too obvious, no point into trying to display keywords on a page that doesn't exist.

= 4.1 =

* Don't display keywords or description meta tags if you haven't selected keywords to display.

= 4.0 =

* If you have upgraded from a previous version, please remove the clever keyword code from your template (The one that looks like: <meta name="keywords" content="<?php if (function_exists('print_clever_seo_keywords')) {print_clever_seo_keywords();} ?>" />). This version doesn't need it. New feature - this plugin now creates/modifies your page's description metadata.

* Even if you have an existing description or keywords metadata, it will append to it. This is great if you use another plugin that displays keywords or description.

= 3.0 =

* Allows you to select which keywords it finds on the page, that you actually want to use.

= 2.2 =

* Improved dependency checker.

= 2.1 =

* Moved the monthly notice to a better position.

= 2.0 =

* Get the user to update the keywords, across the site, every 30 days.

= 1.0 =

* Initial Commit

== Upgrade notice ==

= 4.2 =

* Bug fix, this bug isn't too obvious, no point into trying to display keywords on a page that doesn't exist.

= 4.1 =

* Don't display keywords or description meta tags if you haven't selected keywords to display.

= 4.0 =

* If you have upgraded from a previous version, please remove the clever keyword code from your template (The one that looks like: <meta name="keywords" content="<?php if (function_exists('print_clever_seo_keywords')) {print_clever_seo_keywords();} ?>" />). This version doesn't need it. New feature - this plugin now creates/modifies your page's description metadata.

* Even if you have an existing description or keywords metadata, it will append to it. This is great if you use another plugin that displays keywords or description.

= 3.0 =

* Allows you to select which keywords it finds on the page, that you actually want to use.

= 2.2 =

* Improved dependency checker.

= 2.1 =

* Moved the monthly notice to a better position.

= 2.0 =

* Get the user to update the keywords, across the site, every 30 days.

= 1.0 =

* Initial Commit