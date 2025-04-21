import { useBlockProps, InspectorControls, BlockControls } from '@wordpress/block-editor';
import { PanelBody, ComboboxControl, Notice, ToolbarGroup, ToolbarButton, ToggleControl, __experimentalSpacer as Spacer } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { Fragment, useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { debounce } from 'lodash';
import { pencil, update } from '@wordpress/icons';

export default function edit( { attributes, setAttributes, clientId } ) {

	const { blockId, blockTitle, hasExcelsiorBootstrap, useIframe, metadata } = attributes;

    const blockProps = useBlockProps({});

    const [ post, setPost ] = useState(null);
    const [ refreshKey, setRefreshKey ] = useState(0);
    const [ error, setError ] = useState( null );
    const [ options, setOptions ] = useState( [{value: blockId, label: blockTitle}] );
    const [ selectedOption, setSelectedOption ] = useState( blockId );
    const [ searchQuery, setSearchQuery ] = useState('');
    const dynamicBlockId = `${blockId}-${refreshKey}`;

    // check for Excelsior Bootstrap Editor support
    const hasExcelsiorBootstrapNamespace = useSelect( ( select ) => {
        const { getBlockParents, getBlock } = select( 'core/block-editor' );
        const parentBlocks = getBlockParents( clientId );
    
        return parentBlocks.some( ( parentId ) => {
            const parentBlock = getBlock( parentId );
            return parentBlock?.name === 'excelsior-bootstrap-editor/container';
        } );
    }, [clientId] );

    // check if Excelsior Bootstrap post type
    // if yes, set useIframe to true and disable it
    const isExcelsiorBootstrapPostType = useSelect( ( select ) => {
        const postType = select( 'core/editor' )?.getCurrentPostType?.();
        return postType === 'excelsior_bootstrap';
    }, [] );
    
    const meta = useSelect(
        ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {},
        []
    );
    const usedIn = Array.isArray( meta._reblock_used_in ) ? meta._reblock_used_in : [];
    const usedInReBlock = usedIn.filter( ( item ) => item.type == 'reblock' );

    useEffect( () => {
        if ( isExcelsiorBootstrapPostType ) {
            setAttributes( { useIframe: true } );
        }
    }, [ isExcelsiorBootstrapPostType ] );

    const fetchDeepExcludedIds = async ( initialIds ) => {
        const visited = new Set();
        const toVisit = [ ...initialIds ];
    
        while ( toVisit.length > 0 ) {
            const currentId = toVisit.pop();
    
            if ( visited.has( currentId ) ) {
                continue;
            }
    
            visited.add( currentId );
    
            try {
                const post = await apiFetch( { path: `/wp/v2/reblock/${currentId}` } );
    
                const nestedMeta = post.meta?._reblock_used_in ?? [];
                const nestedRefs = nestedMeta.filter( ref => ref.type === 'reblock' ).map( ref => ref.id );
    
                for ( const refId of nestedRefs ) {
                    if ( !visited.has( refId ) ) {
                        toVisit.push( refId );
                    }
                }
            } catch ( error ) {
                console.warn( `Failed to load ReBlock ${currentId}`, error );
            }
        }
        return Array.from( visited );
    };    

    const fetchPosts = debounce( async ( query ) => {

        const currentPostType = wp.data.select('core/editor').getCurrentPostType();
        let searchPath = `/wp/v2/reblock?search=${query}&per_page=10&status=publish`;
        
        if ( currentPostType === 'reblock' ) {
            const currentPostId = wp.data.select('core/editor').getCurrentPostId();
    
            const initialIds = [ currentPostId ];

            usedInReBlock.forEach( item => {
                initialIds.push( item.id );
            } );
    
            const excludeIds = await fetchDeepExcludedIds( initialIds );
    
            searchPath += `&exclude=${ excludeIds.join(',') }`;
        }

        apiFetch( { path: searchPath } )
            .then( ( posts ) => {
                const formattedOptions = posts.map( post => ( {
                    value: post.id,
                    label: post.title.rendered,
                } ) );
                setOptions( formattedOptions );
                setError( null );
            } )
            .catch( ( error ) => {
                setError( 'Failed to search ReBlock. ' + error.message );
            } );
    }, 500 ); // Debounce the search with a 500ms delay

    useEffect(() => {
        if ( !blockId ) {
            fetchPosts('');
        }
    }, []);

    useEffect(() => {
        if ( blockId ) {
            apiFetch( { path: `/wp/v2/reblock/${blockId}` } )
                .then( ( postData ) => {
                    const content = checkForBootstrap( postData.content.rendered );
                    const postStatus = postData.status;

                    if ( postStatus != 'publish' ) {
                        setError( `The requested ReBlock (ID: ${blockId}) could not be found. Please ensure that it exists and has been published.` );
                        return;
                    }

                    postData.content.rendered = content;
                    setPost( postData );

                    if ( !metadata ) {

                        setAttributes( {
                            metadata: {
                                name: postData.title.rendered
                            }
                        } );

                    } else {

                        if ( metadata.name && ( metadata.name != postData.title.rendered ) ) {
                            setAttributes( {
                                metadata: {
                                    ...metadata,
                                    name: postData.title.rendered,
                                }
                            } );
                        }
                        
                    }

                    setError( null );
                    
                } )
                .catch( ( error ) => {
                    setPost( null );
                    setError( 'There was an error fetching the ReBlock content. ' + error.message );
                } );
        }
    }, [dynamicBlockId] );

    const getBlockTitleById = ( id ) => {
        const block = options.filter( ( option ) => option.value == id );
        return block.length ? block[0].label : '';
    }; 

    const handleInputChange = ( value ) => {
        if ( !value & !blockId ) {
            return;
        }

        if ( !value ) {
            value = getBlockTitleById(blockId);
        }
        
        setSearchQuery( value );
        fetchPosts( value );
    };

    const onComboboxChange  = ( newValue ) => {
        setSelectedOption( newValue );
        setAttributes({ blockId: Number( newValue ), blockTitle: getBlockTitleById( newValue ) });
    };

    const handleRefresh = () => {
        setRefreshKey( ( prevKey ) => prevKey + 1 );
    };

    const checkForBootstrap = ( content ) => {

        if ( hasExcelsiorBootstrapNamespace ) {

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

                    if ( !hasExcelsiorBootstrap ) {
                        setAttributes( { hasExcelsiorBootstrap: true } );
                    }
    
                }
    
                bootstrapContainer.remove();
                return doc.body.innerHTML;
    
            }
            
        }

        if ( hasExcelsiorBootstrap ) {
            setAttributes( { hasExcelsiorBootstrap: false } );
        }

        return content;

    };

	return (
        <>
        <Fragment>
            <BlockControls>
                <ToolbarGroup>
                        <ToolbarButton
                            className='reblock-disabled-btn-title'
                            description={'A ReBlock content ' + ( post ? ' from ' + post.title.rendered : '') }
                            variant='tertiary'
                            __next40pxDefaultSize
                            text={post ? post.title.rendered : 'ReBlock' }
                            disabled={ true }
                        />
                    </ToolbarGroup>
                <ToolbarGroup>
                    <ToolbarButton 
                        href={'/wp-admin/post.php?post=' + post?.id + '&action=edit'}
                        target='_blank'
                        __next40pxDefaultSize
                        icon={pencil}
                        text='Edit Original'
                        disabled={ !post }
                    />
                    <ToolbarButton 
                        __next40pxDefaultSize
                        icon={update}
                        text='Refresh'
                        onClick={ handleRefresh }
                        disabled={ !post }
                    />
                </ToolbarGroup>
            </BlockControls>
        </Fragment>
        <InspectorControls>
            <PanelBody title='ReBlock Settings'>
                <ComboboxControl
                    label="ReBlock"
                    help='To prevent direct or indirect circular references (i.e., infinite loops), ReBlock posts that reference this current ReBlock post are excluded from search.'
                    value={selectedOption}
                    onChange={onComboboxChange}
                    onFilterValueChange={handleInputChange}
                    options={options}
                    renderItem={ (item) => (
                        <span>{item.label}</span>
                    )}
                    placeholder="Search"
                    allowReset={false}
                    expandOnFocus={false}
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                />
                <Spacer as='div' />
                <ToggleControl 
                    label='Embed via iFrame'
                    help={
                        !isExcelsiorBootstrapPostType &&
                        'Toggle on to show this ReBlock content outside WordPress, or to allow circular references (rare occurrence).'
                    }
                    checked={useIframe}
                    disabled={isExcelsiorBootstrapPostType}
                    __nextHasNoMarginBottom
                    onChange={(value) => setAttributes({ useIframe: value })}
                />
                
                {isExcelsiorBootstrapPostType && (
                    <Notice isDismissible={false} status="info">
                        ReBlock can only be embedded as an iFrame for Excelsior Bootstrap.
                    </Notice>
                ) }
            </PanelBody>
        </InspectorControls>
		<div { ...blockProps }>
            {error ? (
                <Notice status="error" isDismissible={false}>{error}</Notice>
            ) : post ? (
                <div dangerouslySetInnerHTML={{ __html: post.content.rendered }} />
            ) : (
                blockId === 0 ? (
                    <div className='reblock-select'>
                        <ComboboxControl
                            className='editor-combobox'
                            label="ReBlock"
                            help='To prevent direct or indirect circular references (i.e., infinite loops), ReBlock posts that reference this current ReBlock post are excluded from search.'
                            value={selectedOption}
                            onChange={onComboboxChange}
                            onFilterValueChange={handleInputChange}
                            options={options}
                            renderItem={ (item) => (
                                <span>{item.label}</span>
                            )}
                            __next40pxDefaultSize
                            __nextHasNoMarginBottom
                            placeholder="Search"
                            allowReset={false}
                            expandOnFocus={false}
                        />
                    </div>
                ) : (
                    <div className='loading-message'><span class="loader"></span> Loading...</div>
                )
                
            ) }
		</div>
        </>
	);
}