<?php
/**
 * Plugin Name: ReBlock
 * Plugin URI:  https://github.com/excelsior-university-web-systems/reblock
 * Description: ReBlock creates a centralized content hub to efficiently manage common reusable content blocks, ensuring consistency, quality, and accessibility.
 * Author:      Ethan Lin
 * Author URI:  https://profiles.wordpress.org/eslin87/
 * Version:     1.0.1
 * License:     GPLv2 or later
 */

if ( !defined( 'ABSPATH' ) ) { exit; }

define( 'REBLOCK_PLUGIN_NAME', 'ReBlock' );
define( 'REBLOCK_POST_TYPE_NAME', 'reblock' );
define( 'EXCELSIOR_BOOTSTRAP_EDITOR_SUPPORT', is_plugin_active( 'excelsior-bootstrap-editor/excelsior-bootstrap-editor.php' ) );

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/includes/post-types.php';
require_once __DIR__ . '/includes/block.php';
require_once __DIR__ . '/includes/options.php';

register_activation_hook( __FILE__, 'eslin87\ReBlock\reblock_activate_plugin' );