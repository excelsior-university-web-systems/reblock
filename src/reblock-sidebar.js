import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { TextareaControl, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

const ReblockPanel = () => {
    const postId = useSelect( (select) => select('core/editor').getCurrentPostId(), [] );
    const permalink = useSelect( (select) => select('core/editor').getPermalink(), [] );
    const postType = useSelect( (select) => select('core/editor').getCurrentPostType(), [] );
    const postTypeObj = useSelect( (select) => select('core').getPostType(postType), [] );

    const [copied, setCopied] = useState( false );

    if ( !postTypeObj || postTypeObj.viewable !== true ) return null;

    const iframeCode = `<iframe data-reblock="${postId}" style="width: 100%; height: auto; overflow: hidden; border: none;" scrolling="no" src="${permalink}"></iframe>`;

    const copyToClipboard = () => {
        navigator.clipboard.writeText( iframeCode ).then( () => {
            setCopied( true );
            setTimeout( () => setCopied( false ), 3000 );
        } );
    };

    return (
        <PluginDocumentSettingPanel
            name="reblock-panel"
            title="Reblock Embed Code"
            className="reblock-panel"
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
    render: ReblockPanel,
} );