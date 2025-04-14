=== ReBlock ===

Contributors: eslin87
Tags: reusable, centralized, content, block
Requires at least: 5.0
Requires PHP: 8.0
Tested up to: 6.8
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ReBlock creates a centralized content hub to efficiently manage common reusable content blocks, ensuring consistency, quality, and accessibility.

== Description ==

ReBlock creates a centralized content hub that efficiently organizes, edits, and hosts common and reused content down to individual blocks of content. This hub allows instructional designers, media strategists, and technologists to manage and deploy content across multiple platforms, ensuring consistency, quality, and accessibility.

== Installation ==

This section describes how to install the plugin and get it working.

= Requirements =

1. PHP 8.0 is the minimum version you should be using.
1. Gutenberg (or block) editor is enabled.

= Instructions =

1. Upload the `reblock` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Look for the ReBlock menu item in the admin menu.

== Screenshots ==

1. An example of ReBlock

== Changelog ==

= 1.0.1 (4/15/52025) =

* Add height adjustment support for ReBlock iFrame embed (send new height value when page resizes).
* Add embed code and copy button to the Page Settings sidebar (for Public post only).
* Add a new "Embed via iFrame" toggle control to the ReBlock block settings to embed the ReBlock content in an iFrame.
* Disable "Embed via iFrame" toggle when the post type is Excelsior Bootstrap (Editor), so that all ReBlock content is loaded via iFrame.
* Other fixes and improvements.

= 1.0.0 (04/09/2025) =

* First release