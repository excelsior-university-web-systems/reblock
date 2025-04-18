<?php
namespace eslin87\ReBlock;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Registers the `_reblock_used_in` post meta field for ReBlock posts.
 *
 * - Stores an array of objects indicating where the ReBlock is used.
 * - Each object includes `id` and `type` to identify the referencing post.
 * - Exposes the meta field in the REST API with a defined schema for block editor use.
 * - Defaults to an empty array if no references are found.
 *
 * @return void
 */
function reblock_register_tracker_meta() {
    register_post_meta( REBLOCK_POST_TYPE_NAME, '_reblock_used_in', [
        'single'       => true,
        'type'         => 'array',
        'show_in_rest' => [
            'schema' => [
                'type'  => 'array',
                'items' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'   => [ 'type' => 'integer' ],
                        'type' => [ 'type' => 'string'  ],
                    ],
                    'required'   => [ 'id', 'type' ],
                ],
            ],
        ],
        'default'      => [],
    ] );
}

add_action( 'init', __NAMESPACE__.'\\reblock_register_tracker_meta' );

/**
 * Recursively extracts ReBlock block ID attributes from an array of parsed blocks.
 *
 * - Searches for blocks of type `reblock/reblock-block-selector`.
 * - Collects the `blockId` attribute from each matching block.
 * - Traverses nested innerBlocks to find deeply nested ReBlock blocks.
 *
 * @param array $blocks The array of parsed Gutenberg blocks.
 * @return array An array of extracted block IDs.
 */
function reblock_extract_block_ids( $blocks ) {
    $ids = [];

    foreach ( $blocks as $block ) {
        // If this is our selector block, grab its blockId.
        if (
            isset( $block['blockName'], $block['attrs']['blockId'] ) &&
            $block['blockName'] === 'reblock/reblock-block-selector' &&
            $block['attrs']['blockId']
        ) {
            $ids[] = intval( $block['attrs']['blockId'] );
        }

        // If there are innerBlocks, recurse into them.
        if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
            $ids = array_merge( $ids, reblock_extract_block_ids( $block['innerBlocks'] ) );
        }
    }

    return $ids;
}

/**
 * Updates the reverse index for ReBlock usage when a post is saved.
 *
 * - Parses the post content to extract all referenced ReBlock IDs.
 * - Compares with previously stored references to detect additions/removals.
 * - Adds the current post to each referenced ReBlock’s `_reblock_used_in` list.
 * - Removes the post from any previously referenced ReBlocks that are no longer used.
 * - Updates the current post's `_reblock_references` meta with the latest ReBlock IDs.
 *
 * @param int     $post_id The ID of the post being saved.
 * @param WP_Post $post    The post object.
 * @param bool    $update  Whether this is an update to an existing post.
 * @return void
 */
function reblock_update_reverse_index( $post_id, $post, $update ) {
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    // Parse the content and pull all ReBlock IDs (including nested)
    $all_blocks  = parse_blocks( $post->post_content );
    $current_ids = array_unique( reblock_extract_block_ids( $all_blocks ) );

    // Fetch previously stored references
    $old_ids = get_post_meta( $post_id, '_reblock_references', true );
    $old_ids = is_array( $old_ids ) ? array_map( 'intval', $old_ids ) : [];

    // Determine which blocks were added or removed on this save
    $added   = array_diff( $current_ids, $old_ids );
    $removed = array_diff( $old_ids, $current_ids );

    // For each newly‑added ReBlock, add this post to its _reblock_used_in
    foreach ( $added as $block_id ) {
        $used_in = get_post_meta( $block_id, '_reblock_used_in', true );
        $used_in = is_array( $used_in ) ? $used_in : [];

        $entry = [
            'id'   => $post_id,
            'type' => $post->post_type,
        ];

        $exists = wp_list_filter( $used_in, [ 'id' => $post_id, 'type' => $post->post_type ] );
        if ( empty( $exists ) ) {
            $used_in[] = $entry;
            update_post_meta( $block_id, '_reblock_used_in', $used_in );
        }
    }

    // For each removed ReBlock, remove this post from its _reblock_used_in
    foreach ( $removed as $block_id ) {
        $used_in = get_post_meta( $block_id, '_reblock_used_in', true );
        if ( ! is_array( $used_in ) ) {
            continue;
        }

        // filter out any entries matching this post
        $filtered = array_filter( $used_in, function( $e ) use ( $post_id ) {
            return intval( $e['id'] ) !== $post_id;
        } );

        if ( empty( $filtered ) ) {
            delete_post_meta( $block_id, '_reblock_used_in' );
        } else {
            update_post_meta( $block_id, '_reblock_used_in', array_values( $filtered ) );
        }
    }

    // Finally, overwrite this post’s own reference list for next time
    if ( ! empty( $current_ids ) ) {
        update_post_meta( $post_id, '_reblock_references', $current_ids );
    } else {
        delete_post_meta( $post_id, '_reblock_references' );
    }
}

add_action( 'save_post', __NAMESPACE__.'\\reblock_update_reverse_index', 10, 3 );

/**
 * Cleans up the reverse index when a post is deleted.
 *
 * - Iterates over all ReBlock posts.
 * - Removes the deleted post from each ReBlock’s `_reblock_used_in` metadata.
 * - Deletes the meta field entirely if no more references remain.
 *
 * @param int $post_id The ID of the post being deleted.
 * @return void
 */
function reblock_cleanup_reverse_index( $post_id ) {
    $all_reblocks = get_posts( [ 
        'post_type'      => REBLOCK_POST_TYPE_NAME,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );

    foreach ( $all_reblocks as $block_id ) {
        $used_in = get_post_meta( $block_id, '_reblock_used_in', true );
        if ( is_array( $used_in ) ) {
            $filtered = array_filter( $used_in, fn( $e ) => intval( $e['id'] ) !== $post_id );
            if ( empty( $filtered ) ) {
                delete_post_meta( $block_id, '_reblock_used_in' );
            } else {
                update_post_meta( $block_id, '_reblock_used_in', array_values( $filtered ) );
            }
        }
    }
}

add_action( 'before_delete_post', __NAMESPACE__.'\\reblock_cleanup_reverse_index' );
?>