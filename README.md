# ReBlock #

**Contributors:** [eslin87](https://profiles.wordpress.org/eslin87/)  
**Tags:** reusable, centralized, content, block  
**Requires at least:** 5.0  
**Requires PHP:** 8.0  
**Tested up to:** 6.8  
**Stable tag:** 1.1.2  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

ReBlock creates a centralized content hub to efficiently manage common reusable content blocks, ensuring consistency, quality, and accessibility.

## Description ##

ReBlock creates a centralized content hub that efficiently organizes, edits, and hosts common and reused content down to individual blocks of content. This hub allows users to manage and deploy content across other post types or platforms, ensuring consistency, quality, and accessibility.

## Installation ##

This section describes how to install the plugin and get it working.

### Requirements ###

1. PHP 8.0 is the minimum version you should be using.
1. Gutenberg (or block) editor is enabled.

### Instructions ###

1. Upload the `reblock` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Look for the ReBlock menu item in the admin menu.

## Screenshots ##

### 1. An example of ReBlock ###
![An example of ReBlock](.wordpress-org/screenshot-1.png)


## Changelog ##

### 1.1.2 (05/21/2025) ###

* Refactor height change message to use `ResizeObserver` instead of `resize` event listener. This way new height is posted whenever it changed by any interaction.
* Fix the issue when required Excelsior Bootstrap style and script are not loaded.
* Minor fixes and improvements.

### 1.1.1 (04/30/2025) ###

* Fix taxonomy assignment permission.
* "Embed as an iframe" option is disabled/enabled according to post types visibility.
* Add script to auto-adjust ReBlock iframe embed's height on browser viewport resize.
* Minor fixes and improvements.

### 1.1.0 (04/23/2025) ###

* Add Categories taxonomy.
* Now tracks where ReBlock blocks are being used.
* Display ReBlock usage information (where it is being used) in the ReBlock post editor.
* The ReBlock's input field no longer returns posts that directly or indirectly nested the current ReBlock post. This is to prevent circular references (infinite loops).

### 1.0.2 (pre-release) ###

* Fix HTML character encodings for ReBlock content that uses an Excelsior Bootstrap container and is inserted into another Excelsior Bootstrap container.
* Minor fixes and improvements.

### 1.0.1 (04/16/2025) ###

* Add height adjustment support for ReBlock iFrame embed (send new height value when page resizes).
* Add embed code and copy button to the Page Settings sidebar (for Public post only).
* Add a new "Embed via iFrame" toggle control to the ReBlock block settings to embed the ReBlock content in an iFrame.
* Disable "Embed via iFrame" toggle when the post type is Excelsior Bootstrap (Editor), so that all ReBlock content is loaded via iFrame.
* Other fixes and improvements.

### 1.0.0 (04/09/2025) ###

* First release