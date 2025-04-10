const reblock = document.querySelector( '#reblock-' + reblock_obj.postId );
let currentHeight = document.documentElement.offsetHeight;

if ( reblock ) {
    const logDocHeight = () => {
        const docHeight = document.documentElement.offsetHeight;
        if ( currentHeight != docHeight ) {
            currentHeight = docHeight;
			window.parent.postMessage( { id: reblock_obj.postId, height: currentHeight }, '*' );
        }
    };
    window.parent.postMessage( { id: reblock_obj.postId, height: currentHeight }, '*' );
    window.addEventListener( 'resize', logDocHeight );
}
