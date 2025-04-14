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
} );

