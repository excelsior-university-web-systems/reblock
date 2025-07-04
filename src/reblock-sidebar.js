import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { TextareaControl, Button, Spinner, Notice } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { store as coreDataStore } from '@wordpress/core-data';
import { escapeAttribute } from '@wordpress/escape-html';
import { caution } from '@wordpress/icons';

/**
 * Custom hook: Determines if the current post type is publicly viewable.
 *
 * Note:
 * - This hook must be called at the top level of a React component.
 * - Do not call inside event handlers, conditionals, or nested functions.
 * - Follows the Rules of Hooks: Hooks must be called unconditionally and in the same order on every render.
 */const usePublicViewable = () => {
    const postType = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostType(), [] );
    const postTypeObj = useSelect( ( select ) => select( 'core' ).getPostType(postType), [] );
    return postTypeObj && postTypeObj.viewable;
};

/**
 * Renders the "ReBlock Usage" panel in the block editor sidebar.
 *
 * - Retrieves `_reblock_used_in` meta to determine where the current ReBlock is used.
 * - Queries and displays linked posts and pages using `coreDataStore`.
 * - Displays a warning that external iframe usages are not included in the list.
 * - Uses a loading spinner while data is being fetched.
 *
 * @return JSX.Element The ReBlock usage tracker panel component.
 */
const ReBlockUsageTrackerPanel = () => {

    const publiclyViewable = usePublicViewable();
    const meta = useSelect(
        ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {},
        []
    );
    const usedIn = Array.isArray( meta._reblock_used_in ) ? meta._reblock_used_in : [];

    // Fetch the corresponding post records
    const posts = useSelect(
        ( select ) => {
            if ( ! usedIn.length ) {
                return []; // no IDs → never loading
            }

            const byType = usedIn.reduce( ( acc, { id, type } ) => {
                ( acc[type] = acc[type] || [] ).push( id );
                return acc;
            }, {} );

            let all = [];
            
            for ( const [ type, ids ] of Object.entries( byType ) ) {
                const recs = select( coreDataStore ).getEntityRecords( 'postType', type, {
                        include:  ids,
                        per_page: ids.length,
                    } );

                if ( recs === null ) {
                    return undefined;
                }

                all = all.concat( recs );
            }
            return all;
        },
        [ usedIn ]
    );

    return (
        <PluginDocumentSettingPanel
            name="reblock-usage-panel"
            title="ReBlock Usage"
            className="reblock-usage-panel"
            icon={caution}
            initialOpen={true}
        >
        { posts === undefined ? (
                <Spinner />
            ) : ! posts.length ? (
                <p><em>No references found.</em></p>
            ) : (
                <>
                <p>This ReBlock is used in the following content items. Updates to this ReBlock will affect all listed items.</p>
                <ul style={{listStyle: 'revert', padding: 'revert'}}>
                    { posts.map( post => (
                        <li key={ post.id }>
                            <a href={ post.link } target="_blank" rel="noopener noreferrer" >
                                { post.title.rendered || `#${ post.id }` }
                            </a>
                        </li>
                    ) ) }
                </ul>
                { publiclyViewable && (
                    <Notice status='warning' __unstableHTML={true} isDismissible={false}>
                        <strong>Warning:</strong> This list does not include ReBlock content embedded as an iframe on external sites. Updates to this ReBlock will still apply to those embeds.
                    </Notice>
                ) }
                </>
            ) }
        </PluginDocumentSettingPanel>
    );
    
};

registerPlugin( 'reblock-usage-tracker', {
    render: ReBlockUsageTrackerPanel,
    icon: caution
} );

/**
 * Renders the ReBlock Embed Code panel in the block editor sidebar.
 *
 * - Displays an iframe embed snippet based on the current post's ID and permalink.
 * - Allows users to copy the embed code to clipboard with a confirmation message.
 * - Only renders for viewable post types.
 *
 * @return JSX.Element|null The ReBlock embed panel component or null if not viewable.
 */
const ReBlockEmbedPanel = () => {
    const postId = useSelect( (select) => select('core/editor').getCurrentPostId(), [] );
    const postTitle = useSelect( (select) => select('core/editor').getEditedPostAttribute( 'title' ), [] );
    const permalink = useSelect( (select) => select('core/editor').getPermalink(), [] );
    const publiclyViewable = usePublicViewable();

    if ( !publiclyViewable ) return null;

    const [copied, setCopied] = useState( false );
    const iframeCode = `<div><iframe title="${escapeAttribute(postTitle)}" data-reblock="${postId}" style="width: 100%; height: auto; overflow: hidden; border: none;" scrolling="no" src="${permalink}"></iframe></div>`;

    const copyToClipboard = () => {
        navigator.clipboard.writeText( iframeCode ).then( () => {
            setCopied( true );
            setTimeout( () => setCopied( false ), 3000 );
        } );
    };

    return (
        <PluginDocumentSettingPanel
            name="reblock-embed"
            title="Reblock Embed Code"
            className="reblock-embed"
            initialOpen={true}
        >
            <TextareaControl
                label="Embed Code"
                value={iframeCode}
                readOnly
                __nextHasNoMarginBottom
            />
            <Button
                variant="primary"
                onClick={copyToClipboard}
                disabled={copied}
                style={{ marginTop: '6px' }}
                __nextHasNoMarginBottom
                __next40pxDefaultSize	

            >
                {copied ? 'Copied' : 'Copy' }
            </Button>
        </PluginDocumentSettingPanel>
    );
};

registerPlugin( 'reblock-embed-meta-box', {
    render: ReBlockEmbedPanel,
} );