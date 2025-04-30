import domReady from '@wordpress/dom-ready';

domReady( () => {
    const reblock = document.querySelector( '#reblock-' + reblock_obj.postId );

    if ( reblock ) {
        const data = {
            id: reblock_obj.postId,
            type: 'reblock',
            height: document.documentElement.offsetHeight
        };
        const logDocHeight = () => {
            const docHeight = document.documentElement.offsetHeight;
            if ( data.height != docHeight ) {
                data.height = docHeight;
                window.parent.postMessage( data, '*' );
            }
        };
        window.parent.postMessage( data, '*' );
        window.addEventListener( 'resize', logDocHeight );
    }

    function adjustReBlockIFrameHeight() {
    
        const reblockIframes = [...document.querySelectorAll( 'iframe' )].filter( iframe =>
            iframe.src.includes( '/reblock/' )
        );
    
        if ( !reblockIframes.length ) return;
    
        window.addEventListener( 'message', ( { data } ) => {
            if ( !data?.id || !data?.type || !data?.height ) return;
            if ( data.type != 'reblock' ) return;
            const targetIframe = document.querySelector( `iframe[data-reblock='${data.id}']` );
            if ( !targetIframe ) return;
            targetIframe.style.height = `${data.height}px`;
        } );
    }

    adjustReBlockIFrameHeight();

} );

