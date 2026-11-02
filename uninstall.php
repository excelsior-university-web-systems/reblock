<?php
namespace eslin87\ReBlock;

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

/**
 * Removes ReBlock settings and role capabilities from the current site.
 *
 * - Deletes all ReBlock options.
 * - Removes ReBlock capabilities from every role, including meta capabilities granted by earlier versions.
 * - Keeps ReBlock posts, categories, and post meta.
 *
 * @return void
 */
function reblock_uninstall_site() {
    $options = array(
        'reblock_hash_slug_option',
        'reblock_is_public',
        'reblock_is_searchable',
        'reblock_start_with_excelsior_bootstrap',
        'reblock_show_wp_admin_bar',
        'reblock_allow_global_styles',
        'reblock_allowed_styles_scripts',
        'reblock_allowed_roles',
        'reblock_caps_initialized',
    );

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // The post type is not registered during uninstall, so capabilities are listed explicitly.
    $capabilities = array(
        'edit_reblock',
        'read_reblock',
        'delete_reblock',
        'edit_reblocks',
        'edit_others_reblocks',
        'delete_reblocks',
        'publish_reblocks',
        'read_private_reblocks',
        'delete_private_reblocks',
        'delete_published_reblocks',
        'delete_others_reblocks',
        'edit_private_reblocks',
        'edit_published_reblocks',
        'manage_reblock_categories',
    );

    foreach ( wp_roles()->role_objects as $role ) {
        foreach ( $capabilities as $cap ) {
            if ( $role->has_cap( $cap ) ) {
                $role->remove_cap( $cap );
            }
        }
    }
}

if ( is_multisite() ) {
    foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $site_id ) {
        switch_to_blog( $site_id );
        reblock_uninstall_site();
        restore_current_blog();
    }
} else {
    reblock_uninstall_site();
}
