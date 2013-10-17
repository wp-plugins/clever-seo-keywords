=== Clever SEO Keywords ===
Contributors: MMDeveloper
Donate link: 
Tags: seo, plugin, keyword, keywords, meta, metadata, description
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 4.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily create keywords, descriptions and headings based on the content of your page.

== Description ==

A wordpress plugin that allows you to create metadata keywords/description based on the headings within your pages, which helps boost your overall SEO score. You can also boost your SEO score by adding hidden generated headings with your keywords as well.

This plugin uses Simple Html DOM, for info go to http://sourceforge.net/projects/simplehtmldom/

== Installation ==

1) Install WordPress 3.6 or higher

2) Download the latest from:

http://wordpress.org/extend/plugins/tom-m8te 

http://wordpress.org/extend/plugins/clever-seo-keywords

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.


== Changelog ==

= 4.6 =

* Added the ability to add dynamic hidden headings which contain the page's keywords to a widget. This basically boosts your SEO score and doesn't affect how the site looks at all.

= 4.5.1 =

* I've noticed in older templates that the post id is empty. Clever Keywords needs the post id so that it can accurately add the relavent description and keywords to the page. Since it can't always do this, now we find the post id by slug.

= 4.5 =

* Able to extract keywords from more html tags on the page. Now you can extract keywords from li, strong, em, th, span, dt, dd and a tags.

= 4.4 =

* Noticed bugs with projects using sessions. So I've cleaned that up. 

= 4.3 =

* Fixed non obvious bug, WP Error Log picked it up. Called a method on a null object.

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

= 4.6 =

* Added the ability to add dynamic hidden headings which contain the page's keywords to a widget. This basically boosts your SEO score and doesn't affect how the site looks at all.

= 4.5.1 =

* I've noticed in older templates that the post id is empty. Clever Keywords needs the post id so that it can accurately add the relavent description and keywords to the page. Since it can't always do this, now we find the post id by slug.

= 4.5 =

* Able to extract keywords from more html tags on the page. Now you can extract keywords from li, strong, em, th, span, dt, dd and a tags.

= 4.4 =

* Noticed bugs with projects using sessions. So I've cleaned that up. 

= 4.3 =

* Fixed non obvious bug, WP Error Log picked it up. Called a method on a null object.

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