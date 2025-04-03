import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ComboboxControl, Button, PanelRow, Notice } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { debounce } from 'lodash';

export default function edit( { attributes, setAttributes, clientId } ) {

	const { blockId, isBootstrap } = attributes;

    const blockProps = useBlockProps( {
        className: 'reblock-preview'
    } );

    const [ post, setPost ] = useState(null);
    const [ refreshKey, setRefreshKey ] = useState(0);
    const [ error, setError ] = useState( null );
    const [ options, setOptions ] = useState( [{value:0, label:'Select a ReBlock'}] );
    const [ selectedOption, setSelectedOption ] = useState( blockId );
    const [ searchQuery, setSearchQuery ] = useState('');
    const [ noBlockFound, setNoBlockFound ] = useState( false );
    const dynamicBlockId = `${blockId}-${refreshKey}`;

    const usePrevious = ( value ) => {
        const ref = useRef();
        
        useEffect(() => {
            ref.current = value;
        }, [value]);
        
        return ref.current;
    };

    const isBootstrapContainer = useSelect( ( select ) => {
        const { getBlockParents, getBlock } = select( 'core/block-editor' );
        const parentBlocks = getBlockParents( clientId );
    
        return parentBlocks.some( ( parentId ) => {
            const parentBlock = getBlock( parentId );
            return parentBlock?.name === 'excelsior-bootstrap-editor/container';
        } );
    }, [clientId] );

    const fetchPosts = debounce( ( query ) => {
        apiFetch( {
            path: `/wp/v2/reblock?search=${query}&per_page=10`,
        } )
            .then( ( posts ) => {
                const formattedOptions = posts.map( post => ( {
                    value: post.id,
                    label: post.title.rendered,
                } ) );
                setOptions( formattedOptions );
            } )
            .catch( ( error ) => {
                console.error( 'Error fetching posts:', error );
            } );
    }, 500 ); // Debounce the search with a 500ms delay

    useEffect(() => {
        if ( blockId ) {
            apiFetch( { path: `/wp/v2/reblock/${blockId}` } )
                .then( ( postData ) => {
                    const content = checkForBootstrap( postData.content.rendered );
                    postData.content.rendered = content;
                    setPost( postData );
                    setError( null );
                } )
                .catch( ( error ) => {
                    setPost( null );
                    setError( 'There was an error fetching the ReBlock content. ' + error.message );
                } );
        }
    }, [dynamicBlockId] );

    const prevOptions = usePrevious( options );

    useEffect( () => {

        if ( prevOptions === null ) {
            return;
        }

        if ( options.length <= 0 ) {
            setNoBlockFound( true );
        } else {
            setNoBlockFound( false );
        }

    }, [options] );

    const handleInputChange = ( value ) => {
        setSearchQuery( value );
        fetchPosts( value );
    };

    const onComboboxChange  = ( newValue ) => {
        setSelectedOption( newValue );
        setAttributes({ blockId: Number( newValue ) });
    };

    const handleRefresh = () => {
        setRefreshKey( ( prevKey ) => prevKey + 1 );
    };

    const checkForBootstrap = ( content ) => {

        if ( isBootstrapContainer ) {

            const parser = new DOMParser();
            const doc = parser.parseFromString( content, 'text/html' );
            const bootstrapContainer = doc.querySelector( '#excelsior-bootstrap' );
    
            if ( bootstrapContainer ) {
                
                const pageContainer = bootstrapContainer.querySelector( '.page-container' );
    
                if ( pageContainer ) {

                    while ( pageContainer.firstChild ) {
                        bootstrapContainer.parentNode.insertBefore( pageContainer.firstChild, bootstrapContainer );
                    }
    
                    pageContainer.remove();

                    if ( !isBootstrap ) {
                        setAttributes( { isBootstrap: true } );
                    }
    
                }
    
                bootstrapContainer.remove();
                return doc.body.innerHTML;
    
            }
            
        }

        if ( isBootstrap ) {
            setAttributes( { isBootstrap: false } );
        }

        return content;

    };

	return (
        <>
        <InspectorControls>
            <PanelBody title='ReBlock Settings'>
                { noBlockFound ? <Notice status="warning" isDismissible={false}><strong>No ReBlock found.</strong> Please try different key words. Current input will not be saved.</Notice> : '' }
                <ComboboxControl
                    label="ReBlock"
                    value={selectedOption}
                    onChange={onComboboxChange}
                    onFilterValueChange={handleInputChange}
                    options={options}
                    renderItem={ (item) => (
                        <span>{item.label}</span>
                    )}
                    expandOnFocus={false}
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                />
                <PanelRow>
                    <Button 
                        variant="primary"
                        onClick={ handleRefresh }
                        __next40pxDefaultSize
                        text='Refresh Content'
                    />
                </PanelRow>
            </PanelBody>
        </InspectorControls>
		<div { ...blockProps }>
            <div class="label">
                <span>Common Block{ post ? ': ' + post.title.rendered : '' }</span>
                <Button 
                    variant='link'
                    size='small'
                    iconSize={12}
                    disabled={ !post }
                    href={'/wp-admin/post.php?post=' + post?.id + '&action=edit'}
                    target='_blank'
                    __next40pxDefaultSize
                    icon={<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                        <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
                      </svg>}
                    label='Edit'
                />
                <Button 
                    variant='link'
                    __next40pxDefaultSize
                    size='small'
                    iconSize={16}
                    disabled={ !post }
                    icon={<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                        </svg>}
                    label='Refresh Content'
                    onClick={ handleRefresh }
                />
            </div>
            {error ? (
                <Notice status="error" isDismissible={false}>{error}</Notice>
            ) : post ? (
                <div dangerouslySetInnerHTML={{ __html: post.content.rendered }} />
            ) : (

                blockId === 0 ? (
                    <>
                    <ComboboxControl
                        className='editor-combobox'
                        label="ReBlock"
                        value={selectedOption}
                        onChange={onComboboxChange}
                        onFilterValueChange={handleInputChange}
                        options={options}
                        renderItem={ (item) => (
                            <span>{item.label}</span>
                        )}
                        expandOnFocus={false}
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                    />
                    { noBlockFound ? <Notice status="warning" isDismissible={false}><strong>No ReBlock found.</strong> Please try different key words. Current input will not be saved.</Notice> : '' }
                    </>
                ) : (
                    <div className='loading-message'><span class="loader"></span> Loading content...</div>
                )
                
            ) }
		</div>
        </>
	);
}