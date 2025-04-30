<?php
namespace eslin87\ReBlock;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Adds a custom block category for the ReBlock plugin in the block editor.
 *
 * - Prepends a new category specific to the ReBlock post type.
 * - Uses the post type name as the slug and plugin name as the title.
 *
 * @param array $categories Existing block categories.
 * @param WP_Post $post The current post being edited.
 * @return array Modified list of block categories with ReBlock category added.
 */
function reblock_add_category( $categories, $post ) {
    return array_merge(
        array(
            array(
                'slug' => REBLOCK_POST_TYPE_NAME.'-blocks',
                'title' => REBLOCK_PLUGIN_NAME,
            ),
        ),
        $categories
    );
}

add_filter( 'block_categories_all', __NAMESPACE__.'\\reblock_add_category' , 10, 2 );

/**
 * Registers the ReBlock block type with a custom render callback.
 *
 * - Loads block registration from the compiled build directory.
 * - Uses `reblock_content_renderer` to dynamically render block output.
 *
 * @return void
 */
function reblock_register_block_type() {
    register_block_type( plugin_dir_path( __FILE__ ) . '../build', array(
        'render_callback' => __NAMESPACE__.'\\reblock_content_renderer'
    ) );
}

add_action( 'init', __NAMESPACE__.'\\reblock_register_block_type' );

/**
 * Renders the content of a ReBlock block based on its attributes.
 *
 * - Outputs an iframe if `useIframe` is true.
 * - Otherwise, renders the post content directly.
 * - Optionally processes the content through `modify_reblock_content` if wrapped in Excelsior Bootstrap.
 *
 * @param array $attributes Block attributes including `blockId`, `hasExcelsiorBootstrap`, and `useIframe`.
 * @return string The rendered HTML output or an empty string if invalid.
 */
function reblock_content_renderer( $attributes ) {
    
    $block_id = isset( $attributes['blockId'] ) ? (int) $attributes['blockId'] : 0;
    $inside_excelsior_bootstrap = !empty( $attributes['hasExcelsiorBootstrap'] );
    $use_iframe = !empty( $attributes['useIframe'] );
    
    if ( ! $block_id ) {
        return '';
    }

    $post = get_post( $block_id );

    if ( $use_iframe && $post ) {
        $permalink = get_permalink( $post );
        return sprintf(
            '<iframe data-reblock="%d" style="width: 100%%; height: auto; overflow: hidden; border: none;" scrolling="no" src="%s"></iframe>',
            $post->ID,
            esc_url( $permalink )
        );
    }

    if ( $post && $post->post_type === REBLOCK_POST_TYPE_NAME ) {

        $content = apply_filters( 'the_content', $post->post_content );

        if ( $inside_excelsior_bootstrap ) {
            $content = modify_reblock_content( $content );
        }

        return $content;
    }

    return '';
}

/**
 * Enqueues block editor assets for the ReBlock post type.
 *
 * - Adds custom CSS to hide the slug field if slug hashing is enabled.
 * - Enqueues a custom sidebar script for the ReBlock editor.
 * - Applies only when editing posts of the ReBlock post type.
 *
 * @return void
 */
function reblock_enqueue_block_editor_assets() {
    $screen = get_current_screen();

    if ( $screen->post_type === REBLOCK_POST_TYPE_NAME ) {

        if ( get_option( 'reblock_hash_slug_option', false ) ) {
            $css = '
                .editor-post-url > div:nth-of-type(2):not(.block-editor-inspector-popover-header) > div:first-of-type {
                    display: none !important;
                }
                .editor-post-url__input .components-base-control__field,
                .editor-post-url__input input {
                    display: none !important;
                }
            ';
    
            wp_add_inline_style( 'reblock-reblock-block-selector-editor-style', $css );
        }

        wp_enqueue_script(
            'reblock-sidebar',
            plugin_dir_url(__FILE__) . '../build/reblock-sidebar.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'],
            filemtime(__FILE__),
            true
        );

    }
}

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\reblock_enqueue_block_editor_assets' );


function reblock_inject_editor_option( $editor_settings, $context ) {
    $editor_settings['reblock_post_type_is_public'] = (bool) get_option( 'reblock_is_public', false );
    return $editor_settings;
}

add_filter( 'block_editor_settings_all', __NAMESPACE__.'\\reblock_inject_editor_option', 10, 2 );


/************ helpers ************/

/**
 * Cleans and modifies ReBlock content HTML before rendering.
 *
 * - Removes all HTML comment nodes.
 * - Extracts inner content from the `.page-container` inside `#excelsior-bootstrap`.
 * - Strips out the `#excelsior-bootstrap` wrapper and its children.
 * - Handles encoding to preserve special characters during processing.
 *
 * @param string $content The original post content.
 * @return string The cleaned and modified HTML content.
 */
function modify_reblock_content( $content ) {
    if ( empty( $content ) ) {
        return $content;
    }

    $dom = new \DOMDocument( '1.0', 'UTF-8' );
    libxml_use_internal_errors( true );

    // Encode special characters as HTML entities to preserve them
    $encodedContent = htmlentities( $content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
    $dom->loadHTML( $encodedContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    libxml_clear_errors();

    $xpath = new \DOMXPath( $dom );

    // Remove all comment nodes
    foreach ( $xpath->query( '//comment()' ) as $comment ) {
        $comment->parentNode->removeChild( $comment );
    }

    $bootstrapContainer = $xpath->query( '//div[@id="excelsior-bootstrap"]' )->item( 0 );

    if ( $bootstrapContainer ) {
        $pageContainer = $xpath->query( './/div[contains(@class, "page-container")]', $bootstrapContainer )->item( 0 );

        if ( $pageContainer ) {
            while ( $pageContainer->firstChild ) {
                $bootstrapContainer->parentNode->insertBefore( $pageContainer->firstChild, $bootstrapContainer );
            }
            $pageContainer->parentNode->removeChild( $pageContainer );
        }

        $bootstrapContainer->parentNode->removeChild( $bootstrapContainer );
    }

    return html_entity_decode( $dom->saveHTML(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}
?>