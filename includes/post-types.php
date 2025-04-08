<?php
namespace eslin87\ReBlock;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Registers the custom post type "ReBlock".
 *
 * - Defines UI labels, capabilities, support features, and visibility settings.
 * - Assigns all post type capabilities to the administrator role.
 * - Enables block editor, revisions, and REST API support.
 *
 * @return void Registers the custom post type and assigns capabilities.
 */
function create_reblock_post_type() {

    $visibility = get_option( 'reblock_is_public', true );
    $is_public = ( $visibility == '1' );

    $searchability = get_option( 'reblock_is_searchable', false );
    $is_searchable = ( $searchability == '1' );
    
    $args = array(
        'labels'                    => array(
            'name'                  =>  REBLOCK_PLUGIN_NAME,
            'singular_name'         =>  REBLOCK_PLUGIN_NAME,
            'add_new'               => 'Add New ' . REBLOCK_PLUGIN_NAME,
            'add_new_item'          => 'Add New ' . REBLOCK_PLUGIN_NAME,
            'edit_item'             => 'Edit ' . REBLOCK_PLUGIN_NAME,
            'new_item'              => 'New ' . REBLOCK_PLUGIN_NAME,
            'view_item'             => 'View ' . REBLOCK_PLUGIN_NAME,
            'view_items'            => 'View ' . REBLOCK_PLUGIN_NAME,
            'search_items'          => 'Search ' . REBLOCK_PLUGIN_NAME,
            'not_found'             => 'No ' . REBLOCK_PLUGIN_NAME .' found',
            'not_found_in_trash'    => 'No ' . REBLOCK_PLUGIN_NAME .' found in trash',
            'all_items'             => 'All ' . REBLOCK_PLUGIN_NAME,
            'insert_into_item'      => 'Insert into ' . REBLOCK_PLUGIN_NAME,
            'uploaded_to_this_item' => 'Uploaded to this ' . REBLOCK_PLUGIN_NAME,
            'attributes'            =>  REBLOCK_PLUGIN_NAME . ' Attributes',
            'filter_items_list'     => 'Filter '. REBLOCK_PLUGIN_NAME .' list',
            'items_list'            =>  REBLOCK_PLUGIN_NAME . ' list',
            'item_published'        =>  REBLOCK_PLUGIN_NAME .' published',
            'item_updated'          =>  REBLOCK_PLUGIN_NAME . ' updated',
            'item_trashed'          =>  REBLOCK_PLUGIN_NAME . ' trashed'
        ),
        'public'              => $is_public,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'map_meta_cap'        => true,
        'capability_type'     => REBLOCK_POST_TYPE_NAME,
        'supports'            => array( 'title', 'editor', 'author', 'revisions', 'custom-fields' ),
        'has_archive'         => false,
        'exclude_from_search' => !$is_searchable,
        'publicly_queryable'  => $is_public,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode('<svg id="a" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 36 36"><path d="M28,0H4C1.79,0,0,1.79,0,4v24c0,2.21,1.79,4,4,4h24c2.21,0,4-1.79,4-4V4c0-2.21-1.79-4-4-4ZM17.11,26.84s-.04,0-.06,0c-5.99.57-11.31-3.82-11.88-9.81-.25-2.6.45-5.21,1.96-7.34l-1.99-1.99c-.07-.08-.12-.17-.13-.28-.02-.25.16-.47.41-.49l5.55-.51s.05,0,.08,0c.25.02.43.24.41.49l-.51,5.55c0,.11-.06.2-.13.28-.18.18-.46.18-.64,0l-1.75-1.75c-2.76,4.19-1.61,9.82,2.58,12.58,1.73,1.14,3.8,1.66,5.86,1.46.5-.06.95.29,1.02.78.06.5-.29.95-.78,1.02ZM26.88,24.94c-.08.08-.17.12-.28.13l-5.55.51c-.25.02-.47-.16-.49-.41,0-.03,0-.05,0-.08l.51-5.55c.02-.25.24-.43.49-.41.11,0,.2.06.28.13l1.74,1.74c2.32-3.59,1.89-8.38-1.14-11.41-1.99-1.99-4.68-2.87-7.28-2.62-.5.06-.95-.29-1.02-.78-.06-.5.29-.95.78-1.02.02,0,.04,0,.06,0,5.99-.57,11.31,3.82,11.88,9.81.25,2.57-.5,5.21-1.98,7.33l2,2c.18.18.18.46,0,.64Z" fill="black"/></svg>'),
        'delete_with_user'    => false,
        "template"
    );

    if ( EXCELSIOR_BOOTSTRAP_EDITOR_SUPPORT && get_option( 'reblock_start_with_excelsior_bootstrap', false ) ) {
        $args['template'] = array( array( 'excelsior-bootstrap-editor/namespace' ) );
        $args['template_lock'] = 'insert';
    }

    // Unregister the post type first (if it already exists)
    if ( post_type_exists( REBLOCK_POST_TYPE_NAME ) ) {
        unregister_post_type( REBLOCK_POST_TYPE_NAME );
    }

    register_post_type( REBLOCK_POST_TYPE_NAME, $args );

    // Retrieve capabilities for the custom post type
    $post_type_object = get_post_type_object( REBLOCK_POST_TYPE_NAME );
    $capabilities = $post_type_object->cap;
    

    // Assign capabilities to allowed roles
    $required_roles = array( 'administrator' );
    $allowed_roles = get_option( 'reblock_allowed_roles', 'administrator' );
    $allowed_roles_array = array_map( 'trim', explode( ',', $allowed_roles ) );
    $combined_roles = array_filter( array_unique( array_merge( $required_roles, $allowed_roles_array ) ) );

    reblock_remove_capability_from_all_except_admin( $capabilities );

    foreach ( $combined_roles as $allowed_role ) {
        $role = get_role( $allowed_role );

        if ( $role ) {
            foreach ( $capabilities as $cap ) {
                if ( ! $role->has_cap( $cap ) ) {
                    $role->add_cap( $cap );
                }
            }
        }
    }
    
    flush_rewrite_rules();

}

/**
 * Initializes ReBlock by registering custom post types.
 *
 * - Registers "ReBlock" and "ReBlock Blocks" post types.
 * - Flushes rewrite rules to ensure proper permalink handling.
 * - Hooks into the WordPress 'init' action.
 *
 * @return void
 */
function reblock_initialize() {
    create_reblock_post_type();
}

add_action( 'init', __NAMESPACE__.'\\reblock_initialize' );

/**
 * Loads a custom blank template for ReBlock single posts.
 *
 * - Applies only to singular posts of type ReBlock.
 * - Falls back to the default template if the custom one is not found.
 *
 * @param string $template The path to the default single post template.
 * @return string The path to the custom or default template.
 */
function reblock_blank_single_template( $template ) {
    if ( is_singular( REBLOCK_POST_TYPE_NAME ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/blank.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    
    return $template;
}

add_filter( 'single_template', __NAMESPACE__.'\\reblock_blank_single_template', 11 );

/**
 * Removes all enqueued styles and scripts on ReBlock pages except for allowed ones.
 *
 * - Applies only to singular pages of ReBlock post type.
 * - Respects user-defined allowed handles via the 'reblock_allowed_styles_scripts' option.
 * - Honors options for admin bar visibility and global styles.
 * - Retains Excelsior Bootstrap assets if supported.
 *
 * @return void
 */
function reblock_remove_all_styles_and_scripts() {
    if ( !is_singular( REBLOCK_POST_TYPE_NAME ) ) {
        return;
    }

    global $wp_styles, $wp_scripts;

    $required = [];

    // Disable admin bar if option is false
    if ( !get_option( 'reblock_show_wp_admin_bar', true ) ) {
        add_filter( 'show_admin_bar', '__return_false' );
        wp_dequeue_style( 'admin-bar' );
        wp_deregister_style( 'admin-bar' );
    }

    // Retain 'global-styles' if global styles are allowed
    if ( get_option( 'reblock_allow_global_styles', true ) ) {
        $required[] = 'global-styles';
    }

    // Retain Excelsior Bootstrap framework if supported
    if ( EXCELSIOR_BOOTSTRAP_EDITOR_SUPPORT ) {
        $required[] = 'excelsior-bootstrap-editor-frontend';
    }

    $allowed_option = get_option( 'reblock_allowed_styles_scripts', '*' );

    if ( $allowed_option === '*' ) {
        return;
    }

    $allowed = array_map( 'trim', explode( ',', $allowed_option ) );
    $combined = array_unique( array_merge( $required, $allowed ) );

    // Ensure admin-bar is preserved if admin bar is enabled
    if ( get_option( 'reblock_show_wp_admin_bar', true ) && !in_array( 'admin-bar', $combined, true ) ) {
        $combined[] = 'admin-bar';
    }

    // Ensure global-styles is removed if global styles are disabled
    if ( !get_option( 'reblock_allow_global_styles', true ) && in_array( 'global-styles', $combined, true ) ) {
        $global_styles_key = array_search( 'global-styles', $combined );
        unset( $combined[$global_styles_key] );
    }

    foreach ( $wp_styles->queue as $style ) {
        if ( !in_array( $style, $combined, true ) ) {
            wp_dequeue_style( $style );
            wp_deregister_style( $style );
        }
    }

    foreach ( $wp_scripts->queue as $script ) {
        if ( !in_array( $script, $combined, true ) ) {
            wp_dequeue_script( $script );
            wp_deregister_script( $script );
        }
    }
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\reblock_remove_all_styles_and_scripts', 99 );

/**
 * Generates a hashed slug for ReBlock posts if slug hashing is enabled.
 *
 * @param array $data    Sanitized post data.
 * @param array $postarr Raw post data.
 * @return array Modified post data with hashed slug if applicable.
 */
function reblock_hash_slug( $data, $postarr ) {
    if ( $data['post_type'] == REBLOCK_POST_TYPE_NAME && get_option( 'reblock_hash_slug_option', false ) ) {
        $title_hash = md5( 'reblock/' . $postarr['ID'] );
        $data['post_name'] = $title_hash;
    }
    return $data;
}

add_filter( 'wp_insert_post_data', __NAMESPACE__.'\\reblock_hash_slug', 10, 2 );

/**
 * Modifies the document title by removing site name and tagline for ReBlock pages.
 *
 * - Applies to single and archive pages of ReBlock post type.
 * - Cleans up the title for a more minimal presentation.
 *
 * @param array $title_parts The original parts of the document title.
 * @return array The modified title parts.
 */
function reblock_document_title_parts( $title_parts ) {
    if ( is_singular( REBLOCK_POST_TYPE_NAME ) ) {
        if ( isset( $title_parts['site'] ) ) {
            unset( $title_parts['site'] );
        }
        if ( isset( $title_parts['tagline'] ) ) {
            unset( $title_parts['tagline'] );
        }
        }
    return $title_parts;
}

add_filter( 'document_title_parts', __NAMESPACE__.'\\reblock_document_title_parts' );

/**
 * Disables the slug field in Quick Edit for ReBlock posts.
 *
 * - Targets posts of type ReBlock.
 * - Uses JavaScript to disable the slug input field when Quick Edit is opened.
 *
 * @return void Outputs inline JavaScript to modify Quick Edit behavior.
 */
function reblock_disable_slug_in_quick_edit() {
    if ( get_post_type() == REBLOCK_POST_TYPE_NAME && get_option( 'reblock_hash_slug_option', false ) ) {
        ?>
        <script type="text/javascript" id="reblock-disable-slug">
            document.addEventListener( 'DOMContentLoaded', function () {
                // Listen for clicks on the Quick Edit button
                document.querySelectorAll( 'button.editinline' ).forEach( function( editButton ) {
                    editButton.addEventListener( 'click', function() {
                        // Delay to ensure the Quick Edit form is loaded
                        setTimeout(function() {
                            const slugField = document.querySelector( 'input[name=post_name]' );
                            if ( slugField ) {
                                slugField.disabled = true; // Disable the slug field
                            }
                        }, 100);
                    } );
                } );
            } );
        </script>
        <?php
    }
}

add_action( 'admin_footer', __NAMESPACE__.'\\reblock_disable_slug_in_quick_edit' );

function reblock_exclude_from_search( $query ) {
    if ( $query->is_search() && ! is_admin() && $query->is_main_query() && !get_option( 'reblock_is_searchable', false ) ) {
        $post_type = $query->get( 'post_type' );

        // Forcefully remove 'reblock' from post_type query
        if ( $post_type === REBLOCK_POST_TYPE_NAME ) {
            $query->set( 'post_type', 'no_reblock_results' );
        }

        // exclude it even if mixed in an array
        if ( is_array( $post_type ) ) {
            $query->set( 'post_type', array_diff( $post_type, [ REBLOCK_POST_TYPE_NAME ] ) );
        }
    }
}

add_action( 'pre_get_posts', __NAMESPACE__.'\\reblock_exclude_from_search' );

function reblock_remove_capability_from_all_except_admin( $capabilities ) {
    global $wp_roles;
    foreach ( $wp_roles->roles as $role_key => $role_info ) {
        if ( 'administrator' === $role_key ) {
            continue;
        }

        $role = get_role( $role_key );

        if ( $role ) {
            foreach ( $capabilities as $cap ) {
                if ( $cap == 'read' ) continue;
                if ( $role->has_cap( $cap ) ) {
                    $role->remove_cap( $cap );
                }
            }
        }
    }
}
?>