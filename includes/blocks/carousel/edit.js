/**
 * WordPress dependencies
 */
const { apiFetch } = wp;
const { __, _n, _nx, _x } = wp.i18n;
// const { registerBlockType } = wp.blocks;
const {
	      ColorPalette,
	      InspectorControls,
	      InspectorAdvancedControls,
      } = wp.blockEditor;
const {
	      BaseControl,
	      ColorIndicator,
	      RadioControl,
	      // Placeholder,
	      // QueryControls,
	      PanelBody,
	      // RangeControl,
	      SelectControl,
	      Spinner,
	      TextControl,
	      // Toolbar,
	      // ButtonGroup,
	      // Button,
	      // TabPanel,
	      // Dashicon,
	      ToggleControl
      } = wp.components;
const { compose, withInstanceId } = wp.compose;
const {
	      // select,
	      // subscribe,
	      // withDispatch,
	      withSelect,
      } = wp.data;
const {
	      // ColorPalette,
	      // InspectorControls,
	      // InspectorAdvancedControls,
      } = wp.editor;
const { Component, Fragment } = wp.element;
const { addQueryArgs } = wp.url;

/**
 * External dependencies
 */
// import { CarouselProvider, DotGroup, Slider, Slide, ButtonBack, ButtonNext } from 'pure-react-carousel';
// import 'pure-react-carousel/dist/react-carousel.es.css';

import Slider from 'react-slick';
import 'slick-carousel/slick/slick.css';
import 'slick-carousel/slick/slick-theme.css';

/**
 * External dependencies
 */
import { cloneDeep, findIndex, has, isUndefined } from 'lodash';

/**
 * Internal dependencies
 */
import { HierarchicalTermSelector, RangeControl } from "@Connections-Directory/components";
// import EntryTypeSelectControl from './components/entry-type-select-control';

/**
 * Import styles.
 */
import './style.scss';

const {
	      entryTypes,
	      // dateTypes,
	      // templates
      } = cbDir.blockSettings;

const ENDPOINT = '/cn-api/v1/entry/';

const colorIndicator = ( label, value ) => (
	<Fragment>
		{ label }
		{ value && (
			<ColorIndicator
				colorValue={ value }
			/>
		) }
	</Fragment>
);

class Carousel extends Component {

	/**
	 * Constructor for the Carousel Component.
	 *
	 * Sets up state, and creates bindings for functions.
	 *
	 * @param {object} props Component properties.
	 */
	constructor( props ) {

		// super( ...arguments );
		super( props );
		console.log( this.props.name, ": constructor()" );
		// console.log( 'constructor()::arguments ', arguments );
		// console.log( 'constructor()::props ', props );
		console.log( 'constructor()::this.props ', this.props );

		const {
			      attributes: { blockId, blocks/*, carousels*/ },
			      clientId,
			      setAttributes,
		      } = this.props;

		this.getIndex = this.getIndex.bind( this );
		this.findIndex = this.findIndex.bind( this );
		this.getAttribute = this.getAttribute.bind( this );
		this.setAttributes = this.setAttributes.bind( this );
		this.prepareQueryArgs = this.prepareQueryArgs.bind( this );
		this.fetchAPI = this.fetchAPI.bind( this );
		this.fetchEntries = this.fetchEntries.bind( this );

		let id = isUndefined( blockId ) ? clientId : blockId;
		let index = this.findIndex( id, blocks );

		this.state = {
			// blocks:       blocks,
			blockId:      id,
			blockIndex:   index,
			// carousels:    carousels,
			queryArgs:    {},
			queryResults: [],
			isLoading:    true,
		};

		setAttributes( { blockId: id } );
	}

	componentDidMount() {
		console.log( this.props.name, ': componentDidMount()' );

		const {
			      attributes: { blocks, blockId },
			      setAttributes,
			      // setMetaFieldValue,
		      } = this.props;

		let index = this.getIndex();

		if ( - 1 === index ) {

			// this.setState( { blockIndex: 0 }, () => {
				// setAttributes( { blocks: [ { blockId: this.state.blockId, listType: 'all' } ] } );
				this.setAttributes( { listType: 'all' } );
			// } );

		}

		// const unsubscribe = subscribe( () => {
		//
		// 	// this.state.editorBlocks = select( 'core/block-editor' ).getBlocks();
		//
		// 	this.setState({
		// 		editorBlocks: select( 'core/block-editor' ).getBlocks()
		// 	});
		// } );

		this.fetchEntries();
	}

	componentDidUpdate( prevProps, prevState ) {
		console.log( this.props.name, ': componentDidUpdate()' );
		// console.log( 'componentDidUpdate()::prevProps ', prevProps );
		// console.log( 'componentDidUpdate()::prevState ', prevState );
		// console.log( 'componentDidUpdate()::this.props ', this.props );
		// console.log( 'componentDidUpdate()::this.state ', this.state );

		const {
			      attributes: { blocks },
		      } = this.props;

		let index = this.findIndex( this.state.blockId, blocks );

		if ( index !== this.state.blockIndex ) {

			console.log( 'componentDidUpdate()::new index ', index );

			this.setState( {
				blockIndex: index,
			} );
		}

	}

	componentWillUnmount() {
		console.log( this.props.name, ': componentWillUnmount()' );

		const {
			      attributes: { blocks },
			      setAttributes,
		      } = this.props;

		const blocksClone = cloneDeep( blocks );
		let index = this.getIndex();

		console.log( 'componentWillUnmount()::blocks : before ', blocksClone );
		console.log( 'componentWillUnmount()::index ', index );

		blocksClone.splice( index, 1 );

		// index = this.findIndex( blockId, blocks );
		let rnd = (0|Math.random()*6.04e7).toString(36);

		console.log( 'componentWillUnmount()::blocks : after ', blocksClone );

		let blocksJSON = JSON.stringify( blocksClone );

		setAttributes( {
			blocks:    blocksClone,
			// carousels: blocksJSON,
			// listType:  rnd,
		} );
	}

	/**
	 * @param {object} args
	 */
	prepareQueryArgs( args ) {

		const { attributes: { blocks } } = this.props;

		let query = {};
		let index = this.getIndex();

		console.log( 'getQueryArgs::blocks ', blocks );

		if ( -1 < index ) {

			query = {
				type:     blocks[ index ].listType,
				category: blocks[ index ].categories,
				...args
			}
		}

		return query;
	}

	fetchAPI( query ) {

		const path = addQueryArgs(
			ENDPOINT,
			{
				...query,
				context: 'edit',
			}
		);

		console.log( 'Fetching... ', query );

		return apiFetch( { path: path } );
	}

	/**
	 * @param {object} args
	 */
	fetchEntries( args ) {

		this.fetchAPI( this.prepareQueryArgs( args ) ).then( ( results ) => {

			this.setState( { isLoading: false, queryResults: results } );
		} );
	};

	/**
	 * @param {string} id
	 * @param {array} blocks
	 *
	 * @return {number}
	 */
	findIndex( id, blocks ) {

		// const { attributes: { blocks } } = this.props;
		// let blocks = this.state.blocks;

		console.log( 'findIndex::blocks ', blocks );
		console.log( 'findIndex::blockId ', id );

		let index = findIndex( blocks, ( o ) => {
			console.log( 'findIndex::o ', o );
			return ! isUndefined( o ) && o.blockId === id;
		});

		console.log( 'findIndex:: ', index );

		return index
	}

	/**
	 * @return {number}
	 */
	getIndex() {

		return this.state.blockIndex;
	}

	/**
	 * @param {string} key
	 * @param {*} defaultValue
	 *
	 * @return {*}
	 */
	getAttribute( key, defaultValue = null ) {

		const {
			      attributes: { blocks, blockId },
			      // setAttributes,
			      // setMetaFieldValue,
		      } = this.props;

		let index = this.getIndex();

		// if ( -1 === index || isUndefined( blocks[ index ][ key ] ) ) {
		if ( - 1 === index || !has( blocks, [ index, key ] ) ) {

			return defaultValue;

		} else {

			return blocks[ index ][ key ];
		}
	}

	/**
	 * The blockId index.
	 *
	 * The `blockAttributes` attribute is an array of block instance attributes. One for each block in the post.
	 * This will update the index in the `blockAttributes` attributes array with the current component state.
	 *
	 * @param {object} attributes
	 */
	setAttributes( attributes ) {

		const {
			      attributes: { blocks },
			      setAttributes,
		      } = this.props;

		const blocksClone = cloneDeep( blocks );
		let index = this.getIndex();

		// console.log( 'setAttributes::props ', this.props );
		console.log( 'setAttributes::blocks ', blocksClone );
		// console.log( 'setAttributes::blockId ', blockId );

		if ( -1 < index ) {

			let block = blocksClone[ index ];
			block = { ...block, ...attributes };
			blocksClone[ index ] = block;

			console.log( 'setAttributes::block (hasIndex) ', block );

		} else {

			let blockCount = blocksClone.push( { blockId: this.state.blockId, ...attributes } );

			this.setState( { blockIndex: ( blockCount - 1 ) } );

				console.log( 'setAttributes::block (pushNew) ', blockCount - 1 );
		}

		let blocksJSON = JSON.stringify( blocksClone );

		setAttributes( {
			blocks: blocksClone,
			// carousels: blocksJSON,
			// ...attributes
		} );
	}

	render() {

		const {
			      attributes,
			      // entries,
			      instanceId,
			      // queryEntries,
			      setAttributes,
		      } = this.props;

		const {
			      // advancedBlockOptions,
			      blocks,
			      blockId,
			      categories,
			      categoriesExclude,
			      categoriesIn,
			      // columns,
			      borderColor,
			      borderRadius,
			      borderWidth,
			      displayDropShadow,
			      displayEmail,
			      displayExcerpt,
			      displayPhone,
			      displaySocial,
			      displayTitle,
			      excerptWordLimit,
			      // gutterWidth,
			      imageBorderColor,
			      imageBorderRadius,
			      imageBorderWidth,
			      imageCropMode,
			      imageShape,
			      imageType,
			      // layout,
			      // listType,
			      // position,
			      // rows,
			      // style,
			      // variation,
		      } = attributes;
console.log( 'render::blocks ', blocks );
		const blockIndex = this.getIndex();
		const entryTypeSelectOptions = [];
console.log( 'render::blockIndex ', blockIndex );
		for ( let property in entryTypes ) {

			// noinspection JSUnfilteredForInLoop
			entryTypeSelectOptions.push( {
				label: entryTypes[ property ],
				value: property
			} )
		}

		let entries = this.state.queryResults;

		const hasEntries = Array.isArray( entries ) && entries.length;

		if ( !hasEntries ) {

			return (
				<Fragment>
					<div>
						{ this.state.isLoading ?
							<p>{ __( 'Loading...', 'connections' ) } <Spinner /></p> :
							<p>{ __( 'No directory entries found.', 'connections' ) }</p>
						}
					</div>
				</Fragment>
			)
		}

		let settings = {
			autoplay:       true,
			dots:           true,
			infinite:       true,
			speed:          500,
			slidesToShow:   1,
			slidesToScroll: 1
		};

		const slides = entries.map( ( entry, i ) => {

				return (
					<div key={ i }>
						<h3>{entry.name.rendered}</h3>
						<div>Block ID: { blockId }</div>
					</div>
				)
			}
		);

		return (
			<Fragment>
				<InspectorControls>

					<PanelBody
						title={ __( 'Select', 'connections' ) }
						initialOpen={ false }
					>
						<p>
							{ __( 'This section controls which entries from your directory will be displayed.', 'connections' ) }
						</p>

						<div style={ { marginTop: '20px' } }>
							<SelectControl
								label={ __( 'Entry Type', 'connections' ) }
								help={ __( 'Select which entry type to display. The default is to display all.', 'connections' ) }
								value={ this.getAttribute( 'listType', 'all' ) }
								options={ [
									{ label: __( 'All', 'connections' ), value: 'all' },
									...entryTypeSelectOptions
								] }
								onChange={ ( value ) => {
									this.setAttributes( { listType: value } );
									this.fetchEntries( { type: value } );
								} }
							/>
						</div>

						<div style={ { marginTop: '20px' } }>
							<p>
								{ __( 'Choose the categories to include in the entry list.', 'connections' ) }
							</p>
						</div>

						<HierarchicalTermSelector
							taxonomy='category'
							terms={ this.getAttribute( 'categories', [] ) }
							onChange={ ( value ) => {
								this.setAttributes( { categories: value } );
								this.fetchEntries( { category: value } );
							} }
						/>

						<div style={ { marginTop: '20px' } }>
							<ToggleControl
								label={ __( 'Entries must be assigned to all the above chosen categories?', 'connections' ) }
								// help={__( '', 'connections' )}
								checked={ ! ! categoriesIn }
								onChange={ () => setAttributes( { categoriesIn: ! categoriesIn } ) }
							/>
						</div>

						<div style={ { marginTop: '20px' } }>
							<p>
								{ __( 'Choose the categories to exclude from the entry list.', 'connections' ) }
							</p>
						</div>

						<HierarchicalTermSelector
							taxonomy='category'
							terms={ JSON.parse( categoriesExclude ) }
							onChange={ ( value ) => setAttributes( { Categories: JSON.stringify( value ) } ) }
						/>

					</PanelBody>

					<PanelBody
						title={ __( 'Image', 'connections' ) }
						initialOpen={ false }
					>

						<div style={ { marginTop: '20px' } }>
							<RadioControl
								label={ __( 'Type', 'connections' ) }
								selected={ imageType }
								options={ [
									{ value: 'logo', label: __( 'Logo', 'connections' ) },
									{ value: 'photo', label: __( 'Photo', 'connections' ) },
								] }
								onChange={ ( value ) => setAttributes( { imageType: value } ) }
							/>

							<RadioControl
								label={ __( 'Shape', 'connections' ) }
								selected={ imageShape }
								options={ [
									{ value: 'circle', label: __( 'Circle', 'connections' ) },
									{ value: 'square', label: __( 'Square', 'connections' ) },
								] }
								onChange={ ( value ) => setAttributes( { imageShape: value } ) }
							/>
						</div>

					</PanelBody>

					<PanelBody
						title={ __( 'Display Details', 'connections' ) }
						initialOpen={ false }
					>

						<div style={ { marginTop: '20px' } }>
							<ToggleControl
								label={ __( 'Display Title?', 'connections' ) }
								checked={ ! ! displayTitle }
								onChange={ () => setAttributes( { displayTitle: ! displayTitle } ) }
							/>

							<ToggleControl
								label={ __( 'Display Excerpt?', 'connections' ) }
								checked={ ! ! displayExcerpt }
								onChange={ () => setAttributes( { displayExcerpt: ! displayExcerpt } ) }
							/>

							<ToggleControl
								label={ __( 'Display Primary Phone?', 'connections' ) }
								checked={ ! ! displayPhone }
								onChange={ () => setAttributes( { displayPhone: ! displayPhone } ) }
							/>

							<ToggleControl
								label={ __( 'Display Primary Email?', 'connections' ) }
								checked={ ! ! displayEmail }
								onChange={ () => setAttributes( { displayEmail: ! displayEmail } ) }
							/>

							<ToggleControl
								label={ __( 'Display Social Networks?', 'connections' ) }
								checked={ ! ! displaySocial }
								onChange={ () => setAttributes( { displaySocial: ! displaySocial } ) }
							/>
						</div>

					</PanelBody>

					<PanelBody
						title={ __( 'Style', 'connections' ) }
						initialOpen={ false }
					>

						<div style={ { marginTop: '20px' } }>

							<BaseControl
								className='editor-color-palette-control'
								label={ colorIndicator( __( 'Border Color', 'connections' ), borderColor ) }
							>
								<ColorPalette
									className='editor-color-palette-control__color-palette'
									value={ borderColor }
									onChange={ ( value ) => setAttributes( { borderColor: value } ) }
								/>
							</BaseControl>

							<RangeControl
								label={ __( 'Border Radius', 'connections' ) }
								value={ borderRadius }
								min={ 0 }
								max={ 20 }
								initialPosition={ 12 }
								allowReset={ true }
								onChange={ ( value ) => setAttributes( { borderRadius: value } ) }
							/>

							<RangeControl
								label={ __( 'Border Width', 'connections' ) }
								value={ borderWidth }
								min={ 0 }
								max={ 5 }
								initialPosition={ 1 }
								allowReset={ true }
								onChange={ ( value ) => setAttributes( { borderWidth: value } ) }
							/>

							<ToggleControl
								label={ __( 'Display Drop Shadow?', 'connections' ) }
								checked={ ! ! displayDropShadow }
								onChange={ () => setAttributes( { displayDropShadow: ! displayDropShadow } ) }
							/>

							<BaseControl
								className='editor-color-palette-control'
								label={ colorIndicator( __( 'Image Border Color', 'connections' ), imageBorderColor ) }
							>
								<ColorPalette
									className='editor-color-palette-control__color-palette'
									value={ imageBorderColor }
									onChange={ ( value ) => setAttributes( { imageBorderColor: value } ) }
								/>
							</BaseControl>

							<RangeControl
								label={ __( 'Image Border Width', 'connections' ) }
								value={ imageBorderWidth }
								min={ 0 }
								max={ 5 }
								initialPosition={ 0 }
								allowReset={ true }
								onChange={ ( value ) => setAttributes( { imageBorderWidth: value } ) }
							/>

							{ imageShape === 'square' &&
							<RangeControl
								label={ __( 'Image Border Radius', 'connections' ) }
								value={ imageBorderRadius }
								min={ 0 }
								max={ 20 }
								initialPosition={ 0 }
								allowReset={ true }
								onChange={ ( value ) => setAttributes( { imageBorderRadius: value } ) }
							/>
							}

						</div>


					</PanelBody>

				</InspectorControls>
				<InspectorAdvancedControls>

					<div style={ { marginTop: '20px' } }>
						<RadioControl
							label={ __( 'Image Crop Mode', 'connections' ) }
							selected={ imageCropMode }
							options={ [
								{
									value: '1',
									label: __( 'Crop and resize proportionally, maintaining the aspect ratio. Crop is based on image center.', 'connections' )
								},
								{
									value: '2',
									label: __( 'Resize proportionally to fit entire image and add margins if required.', 'connections' )
								},
								// {
								// 	value: '3',
								// 	label: __( 'Resize proportionally adjusting the size of scaled image so there are no margins added.', 'connections' )
								// },
								{
									value: '0',
									label: __( 'Resize to fit, no cropping. Image aspect ratio will not be maintained and will likely result in a stretch or squashed image.', 'connections' )
								},
							] }
							onChange={ ( value ) => setAttributes( { imageCropMode: value } ) }
						/>

						<TextControl
							label={ __( 'Excerpt Word Limit', 'connections' ) }
							help={ __( 'Enter 0 for the first sentence only. If the excerpt exceeds the word limit, the excerpt will be truncated at the of the next sentence if it can be determined automatically, so the excerpt word limit may be exceeded in order to display a complete sentence.', 'connections' ) }
							type={ 'number' }
							value={ excerptWordLimit }
							onChange={ ( value ) => setAttributes( { excerptWordLimit: value } ) }
						/>

					</div>

				</InspectorAdvancedControls>

				<div className='slick-slider-section'>
					<Slider { ...settings }>
						{ slides }
					</Slider>
				</div>

			</Fragment>
		)
	}
}

export default compose( [
	// withDispatch( ( dispatch, { value } ) => {
	//
	// 	return {
	// 		queryEntries() {
	//
	// 			fetchEntries().then( ( entries ) => {
	//
	// 				// console.log( 'Dispatch.');
	//
	// 				dispatch( 'connections-directory/entries' ).addEntities( entries )
	// 			} );
	//
	// 		},
	// 		setMetaFieldValue: ( value ) => {
	// 			dispatch( 'core/editor' ).editPost( { meta: { _blocks: value } } );
	// 		},
	// 	};
	//
	// } ),
	// withSelect( ( select, props ) => {

		// const {
		// 	      blockAttributes,
		// 	      blockId,
		// 	      categories,
		// 	      listType
		//       } = props.attributes;
		//
		// // console.log('Select.');
		//
		// let entryType = 'all';
		// let index     = findIndex( blockAttributes, ( o ) => { return o.blockId === blockId } );
		//
		// if ( -1 < index ) {
		//
		// 	entryType = blockAttributes[ index ].listType;
		// }
		//
		// setEntryQueryArg( {
		// 	category: JSON.parse( categories ).toString(),
		// 	type:     entryType,
		// } );
		//
		// return {
		// 	entries: select( 'connections-directory/entries' ).getEntityRecords( entryQueryArgs ),
		// }

		// return {
		// 	editorBlocks: select( 'core/editor' ).getBlocks()
		// };

	// } ),
	withInstanceId
	]
)( Carousel )
