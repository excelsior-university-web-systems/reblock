<?php
namespace eslin87\ReBlock;

if ( !defined( 'ABSPATH' ) ) { exit; }

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

function reblock_enqueue_block_editor_assets() {
    register_block_type( plugin_dir_path( __FILE__ ) . '../build', array(
        'render_callback' => __NAMESPACE__.'\\reblock_content_renderer'
    ) );
}

add_action( 'init', __NAMESPACE__.'\\reblock_enqueue_block_editor_assets' );

function reblock_content_renderer( $attributes ) {
    
    $block_id = isset( $attributes['blockId'] ) ? $attributes['blockId'] : 0;
    $inside_excelsior_bootstrap = isset( $attributes['isBootstrap'] ) ? $attributes['isBootstrap'] : false;
    
    if ( ! $block_id ) {
        return '';
    }

    $post = get_post( $block_id );

    if ( $post && $post->post_type === REBLOCK_POST_TYPE_NAME ) {

        $content = apply_filters( 'the_content', $post->post_content );

        if ( $inside_excelsior_bootstrap ) {
            $content = modify_reblock_content( $content );
        }

        return $content;
    }

    return '';
}

/************ helpers ************/

function modify_reblock_content( $content ) {
    if ( empty( $content ) ) {
        return $content;
    }

    // Load content into DOMDocument
    $dom = new \DOMDocument( '1.0', 'UTF-8' );
    libxml_use_internal_errors( true ); // Suppress parsing errors

    $dom->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    libxml_clear_errors();

    // Get the document body
    $xpath = new \DOMXPath( $dom );

    // Remove all comment nodes
    foreach ( $xpath->query( '//comment()' ) as $comment ) {
        $comment->parentNode->removeChild( $comment );
    }

    // Find div#excelsior-bootstrap
    $bootstrapContainer = $xpath->query( '//div[@id="excelsior-bootstrap"]' )->item(0);

    if ( $bootstrapContainer ) {
        // Find div.page-container inside it
        $pageContainer = $xpath->query( './/div[contains(@class, "page-container")]', $bootstrapContainer )->item( 0 );

        if ( $pageContainer ) {
            // Move all children from .page-container to bootstrapContainer's parent
            while ( $pageContainer->firstChild ) {
                $bootstrapContainer->parentNode->insertBefore( $pageContainer->firstChild, $bootstrapContainer );
            }

            // Remove div.page-container
            $pageContainer->parentNode->removeChild( $pageContainer );
        }

        // Remove div#excelsior-bootstrap
        $bootstrapContainer->parentNode->removeChild( $bootstrapContainer );
    }

    // Get HTML output and remove any remaining comments
    $htmlOutput = $dom->saveHTML();
    $htmlOutput = preg_replace('/<!--(.*?)-->/', '', $htmlOutput); // Remove all comments

    return $htmlOutput;
}
?>