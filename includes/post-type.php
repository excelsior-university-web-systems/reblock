<?php
namespace eslin87\ReBlock;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Activates the ReBlock plugin and assigns capabilities.
 *
 * - Temporarily registers the ReBlock post type to retrieve its capabilities.
 * - Grants all ReBlock capabilities to the administrator role.
 * - Updates an option flag indicating that capabilities have been initialized.
 * - Flushes rewrite rules to ensure proper permalink handling.
 *
 * @return void
 */
function reblock_activate_plugin() {
    if ( ! post_type_exists( REBLOCK_POST_TYPE_NAME ) ) {
        // Temporary register to get capabilities
        register_post_type( REBLOCK_POST_TYPE_NAME, array(
            'capability_type' => REBLOCK_POST_TYPE_NAME,
            'map_meta_cap' => true
        ) );
    }

    $post_type_object = get_post_type_object( REBLOCK_POST_TYPE_NAME );

    if ( ! $post_type_object ) return;

    $capabilities = $post_type_object->cap;

    $admin_role = get_role( 'administrator' );

    if ( $admin_role ) {
        foreach ( $capabilities as $cap ) {
            if ( ! $admin_role->has_cap( $cap ) ) {
                $admin_role->add_cap( $cap );
            }
        }
    }

    update_option( 'reblock_caps_initialized', true );
    flush_rewrite_rules();
}

/**
 * Registers the ReBlock custom post type with dynamic visibility and searchability.
 *
 * - Public visibility and search inclusion are determined by plugin options.
 * - Configures post type labels using REBLOCK_PLUGIN_NAME.
 * - Supports title, editor, author, revisions, and custom fields.
 * - Includes a custom SVG icon for the admin menu.
 * - Initializes a default block template with Excelsior Bootstrap if enabled.
 * - Registers an associated taxonomy via `reblock_register_category_taxonomy()`.
 *
 * @return void
 */
function create_reblock_post_type() {
    $visibility     = get_option( 'reblock_is_public', true );
    $is_public      = ( $visibility == '1' );

    $searchability  = get_option( 'reblock_is_searchable', false );
    $is_searchable  = ( $searchability == '1' );

    $args = array(
        'labels' => array(
            'name'                  => REBLOCK_PLUGIN_NAME,
            'singular_name'         => REBLOCK_PLUGIN_NAME,
            'add_new'               => 'Add New ' . REBLOCK_PLUGIN_NAME,
            'add_new_item'          => 'Add New ' . REBLOCK_PLUGIN_NAME,
            'edit_item'             => 'Edit ' . REBLOCK_PLUGIN_NAME,
            'new_item'              => 'New ' . REBLOCK_PLUGIN_NAME,
            'view_item'             => 'View ' . REBLOCK_PLUGIN_NAME,
            'view_items'            => 'View ' . REBLOCK_PLUGIN_NAME,
            'search_items'          => 'Search ' . REBLOCK_PLUGIN_NAME,
            'not_found'             => 'No ' . REBLOCK_PLUGIN_NAME . ' found',
            'not_found_in_trash'    => 'No ' . REBLOCK_PLUGIN_NAME . ' found in trash',
            'all_items'             => 'All ' . REBLOCK_PLUGIN_NAME,
            'insert_into_item'      => 'Insert into ' . REBLOCK_PLUGIN_NAME,
            'uploaded_to_this_item' => 'Uploaded to this ' . REBLOCK_PLUGIN_NAME,
            'attributes'            => REBLOCK_PLUGIN_NAME . ' Attributes',
            'filter_items_list'     => 'Filter ' . REBLOCK_PLUGIN_NAME . ' list',
            'items_list'            => REBLOCK_PLUGIN_NAME . ' list',
            'item_published'        => REBLOCK_PLUGIN_NAME . ' published',
            'item_updated'          => REBLOCK_PLUGIN_NAME . ' updated',
            'item_trashed'          => REBLOCK_PLUGIN_NAME . ' trashed',
        ),
        'public'              => $is_public,
        'publicly_queryable'  => $is_public,
        'exclude_from_search' => ! $is_searchable,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'map_meta_cap'        => true,
        'capability_type'     => REBLOCK_POST_TYPE_NAME,
        'supports'            => array( 'title', 'editor', 'author', 'revisions', 'custom-fields' ),
        'has_archive'         => false,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode('<svg id="a" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 36 36"><path d="M28,0H4C1.79,0,0,1.79,0,4v24c0,2.21,1.79,4,4,4h24c2.21,0,4-1.79,4-4V4c0-2.21-1.79-4-4-4ZM17.11,26.84s-.04,0-.06,0c-5.99.57-11.31-3.82-11.88-9.81-.25-2.6.45-5.21,1.96-7.34l-1.99-1.99c-.07-.08-.12-.17-.13-.28-.02-.25.16-.47.41-.49l5.55-.51s.05,0,.08,0c.25.02.43.24.41.49l-.51,5.55c0,.11-.06.2-.13.28-.18.18-.46.18-.64,0l-1.75-1.75c-2.76,4.19-1.61,9.82,2.58,12.58,1.73,1.14,3.8,1.66,5.86,1.46.5-.06.95.29,1.02.78.06.5-.29.95-.78,1.02ZM26.88,24.94c-.08.08-.17.12-.28.13l-5.55.51c-.25.02-.47-.16-.49-.41,0-.03,0-.05,0-.08l.51-5.55c.02-.25.24-.43.49-.41.11,0,.2.06.28.13l1.74,1.74c2.32-3.59,1.89-8.38-1.14-11.41-1.99-1.99-4.68-2.87-7.28-2.62-.5.06-.95-.29-1.02-.78-.06-.5.29-.95.78-1.02.02,0,.04,0,.06,0,5.99-.57,11.31,3.82,11.88,9.81.25,2.57-.5,5.21-1.98,7.33l2,2c.18.18.18.46,0,.64Z" fill="black"/></svg>'),
        'delete_with_user'    => false,
    );

    if ( EXCELSIOR_BOOTSTRAP_EDITOR_SUPPORT && get_option( 'reblock_start_with_excelsior_bootstrap', false ) ) {
        $args['template'] = array( array( 'excelsior-bootstrap-editor/namespace' ) );
        $args['template_lock'] = 'insert';
    }

    register_post_type( REBLOCK_POST_TYPE_NAME, $args );
    reblock_register_category_taxonomy();
}

/**
 * Registers a hierarchical category taxonomy for the ReBlock post type.
 *
 * - Provides UI support in the WordPress admin for assigning categories.
 * - Does not expose the taxonomy publicly or in nav menus.
 * - Enables REST API support for block editor integration.
 *
 * @return void
 */
function reblock_register_category_taxonomy() {

    $labels = array(
        'name'              => _x( 'Categories', 'taxonomy general name', 'reblock' ),
        'singular_name'     => _x( 'Category', 'taxonomy singular name', 'reblock' ),
        'search_items'      => __( 'Search Categories', 'reblock' ),
        'all_items'         => __( 'All Categories', 'reblock' ),
        'parent_item'       => __( 'Parent Category', 'reblock' ),
        'parent_item_colon' => __( 'Parent Category:', 'reblock' ),
        'edit_item'         => __( 'Edit Category', 'reblock' ),
        'update_item'       => __( 'Update Category', 'reblock' ),
        'add_new_item'      => __( 'Add New Category', 'reblock' ),
        'new_item_name'     => __( 'New Category Name', 'reblock' ),
        'menu_name'         => __( 'Categories', 'reblock' ),
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => false, 
        'query_var'         => false,
        'rewrite'           => false,
        'show_in_rest'      => true
    );
    
    register_taxonomy(
        REBLOCK_POST_TYPE_NAME.'_category',
        array( REBLOCK_POST_TYPE_NAME ),
        $args
    );

}

/**
 * Initializes the ReBlock plugin by registering the custom post type and assigning capabilities.
 *
 * - Checks if capabilities have been initialized; if not, calls activation logic.
 * - Always registers the ReBlock custom post type.
 * - Hooks into the WordPress 'init' action.
 *
 * @return void
 */
function reblock_initialize() {
    if ( ! get_option( 'reblock_caps_initialized' ) ) {
        reblock_activate_plugin();
    }

    create_reblock_post_type();
}

add_action( 'init', __NAMESPACE__.'\\reblock_initialize' );

/**
 * Reorders the category column in the ReBlock admin post list table.
 *
 * - Moves the custom taxonomy column to appear before the "Author" column.
 * - Ensures the column is only reordered if it exists.
 *
 * @param array $columns The original array of admin post table columns.
 * @return array The reordered columns array.
 */
function reblock_reorder_columns( $columns ) {
    if ( ! isset( $columns['taxonomy-'.REBLOCK_POST_TYPE_NAME.'_category'] ) ) {
        return $columns;
    }

    $cat_label = $columns['taxonomy-'.REBLOCK_POST_TYPE_NAME.'_category'];
    unset( $columns['taxonomy-'.REBLOCK_POST_TYPE_NAME.'_category'] );

    $new_columns = [];
    foreach ( $columns as $key => $label ) {
        if ( 'author' === $key ) {
            $new_columns['taxonomy-'.REBLOCK_POST_TYPE_NAME.'_category'] = $cat_label;
        }
        $new_columns[ $key ] = $label;
    }
    
    return $new_columns;
}

add_filter( 'manage_edit-reblock_columns', __NAMESPACE__.'\\reblock_reorder_columns', 20 );
add_filter( 'manage_reblock_posts_columns', __NAMESPACE__.'\\reblock_reorder_columns', 20 );

/**
 * Adds a secure category filter dropdown to the ReBlock admin post list.
 *
 * - Applies only to the ReBlock post type screen.
 * - Uses a nonce for validation to ensure safe input handling.
 * - Allows filtering by assigned or unassigned categories.
 *
 * @param string $post_type The current post type.
 * @return void
 */
function reblock_add_category_filter( $post_type ) {
    if ( $post_type !== REBLOCK_POST_TYPE_NAME ) {
        return;
    }

    wp_nonce_field( 'reblock_category_filter_action', 'reblock_category_filter_nonce', false, true );

    $taxonomy = REBLOCK_POST_TYPE_NAME . '_category';
    $selected = '';

    if ( isset( $_REQUEST['reblock_category_filter_nonce'] ) && wp_verify_nonce(
           sanitize_key( wp_unslash( $_REQUEST['reblock_category_filter_nonce'] ) ),
           'reblock_category_filter_action'
         )
    ) {
        $selected = isset( $_GET[ $taxonomy ] )
             ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) )
             : '';
    }

    wp_dropdown_categories( array(
        'show_option_all' => __( 'All Categories', 'reblock' ),
        'show_option_none'  => __( 'No Categories',  'reblock' ),
        'option_none_value' => 'none',
        'taxonomy'        => $taxonomy,
        'name'            => $taxonomy,
        'orderby'         => 'name',
        'selected'        => $selected,
        'show_count'      => false,
        'hide_empty'      => true,
    ) );
}

add_action( 'restrict_manage_posts', __NAMESPACE__.'\\reblock_add_category_filter' );

/**
 * Applies category filtering to ReBlock posts in the admin list view.
 *
 * - Validates the filter using a nonce for security.
 * - Applies only on the ReBlock post type edit screen.
 * - Supports filtering by specific category term or posts without any category.
 * - Modifies the main query via a `tax_query`.
 *
 * @param WP_Query $query The current query object.
 * @return void
 */
function reblock_filter_posts_by_category( $query ) {
    global $pagenow;
    
    if ( $pagenow !== 'edit.php' || $query->get( 'post_type' ) !== REBLOCK_POST_TYPE_NAME ) {
        return;
    }

    if ( empty( $_REQUEST['reblock_category_filter_nonce'] ) || ! wp_verify_nonce(
            sanitize_key( wp_unslash( $_REQUEST['reblock_category_filter_nonce'] ) ),
            'reblock_category_filter_action'
        )
    ) {
        return;
    }

    $taxonomy = REBLOCK_POST_TYPE_NAME . '_category';
    $value    = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) ) : '';

    if ( '' === $value || '0' === $value ) {
        return;
    }

    if ( 'none' === $value ) {
        $query->set( 'tax_query', array( array(
            'taxonomy' => $taxonomy,
            'operator' => 'NOT EXISTS',
        ) ) );
        return;
    }

    if ( is_numeric( $value ) && intval( $value ) > 0 ) {
        $query->set( 'tax_query', array( array(
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => intval( $value ),
        ) ) );
    }
}

add_filter( 'parse_query', __NAMESPACE__.'\\reblock_filter_posts_by_category' );

/**
 * Loads a custom blank template for ReBlock single posts.
 *
 * - Applies only to singular posts of the REBLOCK post type.
 * - Returns the plugin-provided blank template if it exists.
 * - Falls back to the default template otherwise.
 *
 * @param string $template The default single post template path.
 * @return string The modified or original template path.
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
 * Removes all styles and scripts on ReBlock single posts except allowed ones and enqueues the ReBlock frontend script.
 *
 * - Registers and enqueues the `reblock-single` script from the build directory.
 * - Passes the current post ID to JavaScript via `wp_localize_script`.
 * - Retains required assets such as global styles, Excelsior Bootstrap, and allowed user-defined handles.
 * - Optionally disables the WordPress admin bar and its associated styles.
 * - Dequeues all non-allowed styles and scripts for a clean front-end rendering.
 *
 * @return void
 */
function reblock_remove_all_styles_and_scripts() {
    if ( !is_singular( REBLOCK_POST_TYPE_NAME ) ) {
        return;
    }

    $reblock_single_asset = include plugin_dir_path( __FILE__ ) . '../build/reblock-single.asset.php';

    wp_register_script(
        'reblock-single',
        plugin_dir_url( __FILE__ ) . '../build/reblock-single.js',
        $reblock_single_asset['dependencies'],
        $reblock_single_asset['version'],
        array (
            'strategy' => 'defer',
            'in_footer' => true
        )
    );
    wp_enqueue_script( 'reblock-single' );
    wp_localize_script( 'reblock-single', 'reblock_obj', array(
        'postId' => get_the_ID()
    ) );

    global $wp_styles, $wp_scripts;

    $required = [ 'reblock-single' ];

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
 * Generates a hashed slug for new ReBlock posts if slug hashing is enabled.
 *
 * - Applies only to posts of the REBLOCK post type.
 * - Checks the plugin option `reblock_hash_slug_option` before applying.
 * - For new posts, generates a unique slug using microtime and a random password.
 * - Hashes the base string using MD5 to ensure a unique, fixed-length slug.
 *
 * @param array $data    Sanitized post data.
 * @param array $postarr Raw post data.
 * @return array Modified post data with hashed slug if applicable.
 */
function reblock_hash_slug( $data, $postarr ) {
    if ( $data['post_type'] !== REBLOCK_POST_TYPE_NAME ) {
        return $data;
    }

    if ( ! get_option( 'reblock_hash_slug_option', false ) ) {
        return $data;
    }

    // Only generate slug if it's a new post
    $is_new_post = empty( $postarr['ID'] ) || get_post_status( $postarr['ID'] ) === false;

    if ( $is_new_post ) {
        $time = microtime( true );
        $rand = wp_generate_password( 6, false );
        $base = 'reblock-' . $time . '-' . $rand;

        $data['post_name'] = md5( $base );
    }

    return $data;
}

add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\\reblock_hash_slug', 10, 2 );

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

/**
 * Excludes ReBlock posts from frontend search results if the setting is enabled.
 *
 * - Applies only to the main search query on the frontend.
 * - Checks the `reblock_is_searchable` option before excluding.
 * - Removes REBLOCK_POST_TYPE_NAME from the search query.
 * - Prevents any ReBlock results by setting a dummy post type if it's the only one.
 *
 * @param WP_Query $query The current WordPress query object.
 * @return void
 */
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
?>