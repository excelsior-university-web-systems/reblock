import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import edit from './edit';
import save from './save';

registerBlockType( metadata, {
    icon: {
        src: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" aria-hidden="true" focusable="false"><path d="M4 6h12V4.5H4V6Zm16 4.5H4V9h16v1.5ZM4 15h16v-1.5H4V15Zm0 4.5h16V18H4v1.5Z"></path></svg>
    },
    edit,
    save
} );