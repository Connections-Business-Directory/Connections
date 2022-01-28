/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import {
	ColorPalette,
	InspectorControls,
	InspectorAdvancedControls,
} from '@wordpress/block-editor';
import {
	BaseControl,
	ColorIndicator,
	RadioControl,
	PanelBody,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl
} from '@wordpress/components';
import {
	compose,
	withInstanceId
} from '@wordpress/compose';
import {
	select,
	withDispatch,
	withSelect,
} from '@wordpress/data';
import {
	Component,
	Fragment
} from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

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
import { findIndex, has, isUndefined } from 'lodash';

/**
 * Internal dependencies
 */
import { HierarchicalTermSelector, RangeControl } from "@Connections-Directory/components";
import {
	EntryName,
	EntryTitle,
	EntryImage,
	EntryPhoneNumbers,
	EntryEmail,
	EntrySocialNetworks,
	EntryExcerpt,
} from "@Connections-Directory/components";

import { isNumber } from "@Connections-Directory/components/utility";

const {
	entryTypes,
	// dateTypes,
	// templates,
} = cbDir.blockSettings;

const ENDPOINT = '/cn-api/v1/entry/';

const colorIndicator = ( label, value ) => (
	<Fragment>
		<p>
			{ label }
			{ value && (
				<ColorIndicator
					colorValue={ value }
					style={ { background: value, verticalAlign: 'bottom' } }
				/>
			) }
		</p>
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
		// console.log( this.props.name, ": constructor()" );
		// console.log( 'constructor()::arguments ', arguments );
		// console.log( 'constructor()::props ', props );
		// console.log( 'constructor()::this.props ', this.props );

		const {
			      attributes: { blockId },
			      metaCarousels,
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

		// this.isNumber = this.isNumber.bind( this );

		const id = isUndefined( blockId ) ? clientId : blockId;
		const blocks = JSON.parse( metaCarousels );
		const index = this.findIndex( id, blocks );

		/**
		 * Slider key state will change when toggling autoPlay.
		 *
		 * If autoplay is changed state will be updated in componentDidUpdate().
		 *
		 * @link https://github.com/akiran/react-slick/issues/1634
		 */

		this.state = {
			blockId:      id,
			blockIndex:   index,
			queryResults: [],
			isLoading:    true,
			sliderKey:    new Date().getTime() / 1000
		};

		// console.log( 'constructor()::this.state ', this.state );
		// console.log( 'constructor()::metaCarousels ', metaCarousels );

		setAttributes( { blockId: id } );
	}

	componentDidMount() {
		// console.log( this.props.name, ': componentDidMount()' );

		const index = this.getIndex();

		if ( - 1 === index ) {

			this.setAttributes( { listType: 'all' } );
		}

		// Add `style` tag to page header for block styles.
		const styleTag = document.createElement( 'style' );

		styleTag.setAttribute( 'id', 'slick-slider-block-' + this.state.blockId );

		document.head.appendChild( styleTag );

		this.fetchEntries();
	}

	/**
	 * @param {object} prevProps
	 * @param {object} prevState
	 */
	componentDidUpdate( prevProps, prevState ) {
		// console.log( this.props.name, ': componentDidUpdate()' );
		// console.log( 'componentDidUpdate()::prevProps ', prevProps );
		// console.log( 'componentDidUpdate()::props ', this.props );

		const {
			      metaCarousels: prevMetaCarousels,
		      } = prevProps;

		// console.log( 'componentDidUpdate()::prevMetaCarousels ', prevMetaCarousels );

		const {
			      metaCarousels,
		      } = this.props;

		const prevBlocks = JSON.parse( prevMetaCarousels );
		const blocks = JSON.parse( metaCarousels );
		const index = this.findIndex( this.state.blockId, blocks );
		const prevCarousel = prevBlocks[ index ];
		const carousel = blocks[ index ];

		if ( index !== this.state.blockIndex ) {

			// console.log( 'componentDidUpdate()::new index ', index );

			this.setState( {
				blockIndex: index,
			} );
		}

		/*
		 * See note in constructor() about the sliderKey.
		 */
		if ( ! isUndefined( prevCarousel ) && prevCarousel.autoplay !== carousel.autoplay ) {

			this.setState( {
				sliderKey: new Date().getTime() / 1000
			} );
		}

		const element = document.getElementById( 'slick-slider-block-' + this.state.blockId );

		if ( null != element && 'undefined' != typeof element ) {

			let arrowDotsColor = this.getAttribute( 'arrowDotsColor', '#000000' );
			let backgroundColor = this.getAttribute( 'backgroundColor', '#FFFFFF' );
			let borderColor = this.getAttribute( 'borderColor', '#000000' );
			let borderRadius = this.getAttribute( 'borderRadius', 0 );
			let borderWidth = this.getAttribute( 'borderWidth', 0 );
			let color = this.getAttribute( 'color', '#000000' );

			let imageBorderColor = this.getAttribute( 'imageBorderColor', '#000000' );
			let imageBorderRadius = this.getAttribute( 'imageBorderRadius', 0 );
			let imageBorderWidth = this.getAttribute( 'imageBorderWidth', 0 );
			let imageShape = this.getAttribute( 'imageShape', 'square' );

			// Using the "Clear" button set the value to empty string. Use default color.
			if ( ! arrowDotsColor ) { color = '#000000'; }
			if ( ! backgroundColor ) { backgroundColor = '#FFFFFF'; }
			if ( ! borderColor ) { borderColor = '#000000'; }
			if ( ! color ) { color = '#000000'; }
			if ( ! imageBorderColor ) { imageBorderColor = '#000000'; }

			const id = '#slick-slider-block-' + this.state.blockId;

			const arrowDotStyle = [
				`color: ${ arrowDotsColor };`,
			];

			const blockStyle = [
				`background-color: ${ backgroundColor }`,
				`color: ${ color }`,
			];

			const slideStyle = [
				`border-color: ${ borderColor }`,
				`border-radius: ${ borderRadius }px`,
				`border-style: solid`,
				`border-width: ${ borderWidth }px`,
			];

			imageBorderRadius = 'circle' === imageShape ? '50%' : imageBorderRadius + 'px';

			const imageStyle = [
				`border-color: ${ imageBorderColor }`,
				`border-radius: ${ imageBorderRadius }`,
				`border-style: solid`,
				`border-width: ${ imageBorderWidth }px`,
				'overflow: hidden'
			];

			const nameStyle = [
				`color: ${ color };`,
			];

			let css = '';

			css += `\n${id} .slick-arrow.slick-next:before { ${arrowDotStyle.join('\n')} }`;
			css += `\n${id} .slick-arrow.slick-prev:before { ${arrowDotStyle.join('\n')} }`;
			css += `\n${id} .slick-dots li button:before { ${arrowDotStyle.join('\n')} }`;

			css += `\n${id} { ${blockStyle.join(';\n')} }`;
			css += `\n${id} .slick-slide { ${slideStyle.join(';\n')} }`;
			css += `\n${id} .slick-slide h3 { ${nameStyle.join('\n')} }`;
			css += `\n${id} .slick-slide .cn-image-style { ${imageStyle.join(';\n')} }`;

			element.innerHTML = css;
		}
	}

	componentWillUnmount() {
		// console.log( this.props.name, ': componentWillUnmount()' );

		const {
			      attributes: { blockId },
			      // isSelected,
			      metaCarousels,
			      setMetaFieldValue,
		      } = this.props;

		const editorBlocks = select( 'core/block-editor' ).getBlocks();

		/*
		 * Because `select( 'core/block-editor' ).getBlocks()` return a nested array where the `innerBlocks` property
		 * can contain nested blocks, it needs flattened first so it can be filtered by block name and then searched
		 * for the current `blockId`.
		 *
		 * @link https://stackoverflow.com/a/35272973/5351316
		 */
		const flatten = ( into, node ) => {
			if ( node == null ) return into;
			if ( Array.isArray( node ) ) return node.reduce( flatten, into );
			into.push( node );
			return flatten( into, node.innerBlocks );
		};

		const blocksFlattend = flatten( [], editorBlocks );

		// Filter blocks by block name.
		const selectEditorBlocks = blocksFlattend.filter( ( block ) => {
			return this.props.name === block.name;
		} );

		// Find this block within the editor blocks.
		const blockExists = selectEditorBlocks.find( ( block ) => {
			return blockId === block.attributes.blockId;
		} );

		// If this block was not found the `blockExists` var will be undefined, remove it from the post meta.
		if ( isUndefined( blockExists ) ) {

			const blocks = JSON.parse( metaCarousels );
			const index = this.findIndex( this.state.blockId, blocks );

			// console.log( 'componentWillUnmount()::blocks : before ', blocks );
			// console.log( 'componentWillUnmount()::index ', index );

			blocks.splice( index, 1 );

			// console.log( 'componentWillUnmount()::blocks : after ', blocks );

			const blocksJSON = JSON.stringify( blocks );

			setMetaFieldValue( blocksJSON );
		}
	}

	/**
	 * @param {object} args
	 */
	prepareQueryArgs( args ) {

		const {
			      attributes: { carousels },
		      } = this.props;

		let query = {};
		const index = this.getIndex();
		const blocks = JSON.parse( carousels );

		// console.log( 'prepareQueryArgs::blocks ', blocks );

		if ( -1 < index ) {

			const block = blocks[ index ];

			if ( has( block, 'listType' ) ) {

				query['type'] = block.listType;
			}

			if ( has( block, 'categories' ) ) {

				query['categories'] = block.categories;
			}

			if ( has( block, 'categoriesIn' ) && true === block.categoriesIn ) {

				query['category_in'] = true;
			}

			if ( has( block, 'categoriesExclude' ) ) {

				query['categories_exclude'] = block.categoriesExclude;
			}

			if ( has( block, 'limit' ) ) {

				query['per_page'] = block.limit;
			}

			if ( has( block, 'excerptWordLimit' ) ) {

				if ( isNumber( block.excerptWordLimit ) ) {

					query['_excerpt'] = { length: block.excerptWordLimit };

				} else {

					query['_excerpt'] = {};
				}

			}

			let zc = has( block, 'imageCropMode' ) ? block.imageCropMode : 1;

			if ( has( args, 'imageCropMode' ) ) {

				zc = args.imageCropMode;
				delete args.imageCropMode;
			}

			query['_images'] = [
				{ type: 'logo', size: 'custom', width: 600, height: 600, zc: zc },
				{ type: 'photo', size: 'custom', width: 600, height: 600, zc: zc }
			];

			query['_fields'] = 'fn.rendered,job_title.rendered,phone,email,social,excerpt.rendered,images';

			query = { ...query, ...args };
		}

		// console.log( query );

		return query;
	}

	fetchAPI( query ) {

		const path = addQueryArgs(
			ENDPOINT,
			{
				...query,
				context: 'view',
			}
		);

		// console.log( 'Fetching... ', query );

		return apiFetch( { path: path } );
	}

	/**
	 * @param {object} args
	 */
	fetchEntries( args = {} ) {

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

		// console.log( 'findIndex::blocks ', blocks );
		// console.log( 'findIndex::blockId ', id );

		const index = findIndex( blocks, ( o ) => {
			// console.log( 'findIndex::o ', o );
			return ! isUndefined( o ) && o.blockId === id;
		});

		// console.log( 'findIndex:: ', index );

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
			      metaCarousels,
		      } = this.props;

		const index = this.getIndex();
		const blocks = JSON.parse( metaCarousels );

		// console.log( 'getAttributes::typeof blocks ', typeof blocks );
		// console.log( 'getAttributes::blocks ', blocks );
		// console.log( 'getAttributes::key ', key );

		if ( - 1 === index || !has( blocks, [ index, key ] ) ) {

			// console.log( 'getAttributes::defaultValue ', defaultValue );

			return defaultValue;

		} else {

			// console.log( 'getAttributes::value ', blocks[ index ][ key ] );

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
			      metaCarousels,
			      setMetaFieldValue,
		      } = this.props;

		const blocks = JSON.parse( metaCarousels );
		let index = this.getIndex();

		// console.log( 'setAttributes::blocks ', blocks );

		if ( -1 < index ) {

			let block = blocks[ index ];
			block = { ...block, ...attributes };
			blocks[ index ] = block;

			// console.log( 'setAttributes::block (hasIndex) ', block );

		} else {

			let blockCount = blocks.push( { blockId: this.state.blockId, ...attributes } );

			this.setState( { blockIndex: ( blockCount - 1 ) } );

			// console.log( 'setAttributes::block (pushNew) ', blockCount - 1 );
		}

		// console.log( 'setAttributes::blocks (updated) ', blocks );

		const blocksJSON = JSON.stringify( blocks );

		// console.log( 'setAttributes::blocksJSON ', blocksJSON );

		setMetaFieldValue( blocksJSON );
	}

	render() {
		// console.log( this.props.name, ': render()' );

		const {
			      attributes,
		      } = this.props;

		const {
			      blockId,
		      } = attributes;

		// const blockIndex = this.getIndex();
		const entryTypeSelectOptions = [];

		// console.log( 'render::blockIndex ', blockIndex );

		for ( let property in entryTypes ) {

			// noinspection JSUnfilteredForInLoop
			entryTypeSelectOptions.push( {
				label: entryTypes[ property ],
				value: property
			} )
		}

		const categoriesIn = this.getAttribute( 'categoriesIn', false );
		const arrows = this.getAttribute( 'arrows', true );
		const autoplay = this.getAttribute( 'autoplay', false );
		const dots = this.getAttribute( 'dots', true );
		const infinite = this.getAttribute( 'infinite', false );
		const pause = this.getAttribute( 'pause', true );

		const displayTitle = this.getAttribute( 'displayTitle', true );
		const displayExcerpt = this.getAttribute( 'displayExcerpt', true );
		const displayPhone = this.getAttribute( 'displayPhone', true );
		const displayEmail = this.getAttribute( 'displayEmail', true );
		const displaySocial = this.getAttribute( 'displaySocial', true );

		const backgroundColor = this.getAttribute( 'backgroundColor', '#FFFFFF' );
		const textColor = this.getAttribute( 'color', '#000000' );
		const arrowDotsColor = this.getAttribute( 'arrowDotsColor', '#000000' );
		const borderColor = this.getAttribute( 'borderColor', '#000000' );
		const borderRadius = this.getAttribute( 'borderRadius', 0 );
		const borderWidth = this.getAttribute( 'borderWidth', 0 );
		const displayDropShadow = this.getAttribute( 'displayDropShadow', false );

		const imageBorderColor = this.getAttribute( 'imageBorderColor', '#000000' );
		const imageBorderRadius = this.getAttribute( 'imageBorderRadius', 0 );
		const imageBorderWidth = this.getAttribute( 'imageBorderWidth', 0 );
		const imageCropMode = this.getAttribute( 'imageCropMode', '1' );
		const imageShape = this.getAttribute( 'imageShape', 'square' );
		const imageType = this.getAttribute( 'imageType', 'photo' );

		const excerptWordLimit = this.getAttribute( 'excerptWordLimit', '' );

		const inspectorControls = (
			<Fragment>
				<InspectorControls>

					<PanelBody
						title={ __( 'Carousel', 'connections' ) }
						initialOpen={ true }
					>
						<div style={ { marginTop: '20px' } }>

							<RangeControl
								label={ __( 'Maximum Number of Slides', 'connections' ) }
								value={ this.getAttribute( 'limit', 10 ) }
								min={ 1 }
								max={ 100 }
								initialPosition={ 10 }
								allowReset={ true }
								onChange={ ( value ) => {
									this.setAttributes( { limit: value } );
									this.fetchEntries( { per_page: value } );
								} }
							/>

							<RangeControl
								label={ __( 'Number of Slides to Display per Frame', 'connections' ) }
								value={ this.getAttribute( 'slidesToShow', 1 ) }
								min={ 1 }
								max={ 4 }
								initialPosition={ 1 }
								allowReset={ true }
								onChange={ ( value ) => {

									let parameters = { slidesToShow: value };
									const slidesToScroll = this.getAttribute( 'slidesToScroll', 1 );

									if ( value <= slidesToScroll ) {

										parameters['slidesToScroll'] = value;
									}

									this.setAttributes( parameters );
								} }
							/>

							<RangeControl
								label={ __( 'Number of Slides to Scroll per Frame', 'connections' ) }
								value={ this.getAttribute( 'slidesToScroll', 1 ) }
								min={ 1 }
								max={ this.getAttribute( 'slidesToShow', 1 ) }
								initialPosition={ 1 }
								allowReset={ true }
								onChange={ ( value ) => this.setAttributes( { slidesToScroll: value } ) }
							/>

							<div style={ { marginTop: '20px' } }>

								<ToggleControl
									label={ __( 'Autoplay?', 'connections' ) }
									// help={__( '', 'connections' )}
									checked={ ! ! autoplay }
									onChange={ () => this.setAttributes( { autoplay: ! autoplay } ) }
								/>

								{ autoplay &&
								<Fragment>

									<ToggleControl
										label={ __( 'Pause on hover?', 'connections' ) }
										// help={__( '', 'connections' )}
										checked={ ! ! pause }
										onChange={ () => this.setAttributes( { pause: ! pause } ) }
									/>

									<RangeControl
										label={ __( 'Frame Advance Speed in Milliseconds', 'connections' ) }
										value={ this.getAttribute( 'autoplaySpeed', 3000 ) }
										min={ 100 }
										max={ 10000 }
										initialPosition={ 3000 }
										allowReset={ true }
										onChange={ ( value ) => this.setAttributes( { autoplaySpeed: value } ) }
									/>

								</Fragment>
								}

								<ToggleControl
									label={ __( 'Infinite loop?', 'connections' ) }
									// help={__( '', 'connections' )}
									checked={ ! ! infinite }
									onChange={ () => this.setAttributes( { infinite: ! infinite } ) }
								/>

								<ToggleControl
									label={ __( 'Display arrows?', 'connections' ) }
									// help={__( '', 'connections' )}
									checked={ ! ! arrows }
									onChange={ () => this.setAttributes( { arrows: ! arrows } ) }
								/>

								<ToggleControl
									label={ __( 'Display dots?', 'connections' ) }
									// help={__( '', 'connections' )}
									checked={ ! ! dots }
									onChange={ () => this.setAttributes( { dots: ! dots } ) }
								/>

								<RangeControl
									label={ __( 'Frame Animation Speed in Milliseconds', 'connections' ) }
									value={ this.getAttribute( 'speed', 500 ) }
									min={ 100 }
									max={ 5000 }
									initialPosition={ 500 }
									allowReset={ true }
									onChange={ ( value ) => this.setAttributes( { speed: value } ) }
								/>

							</div>

						</div>

					</PanelBody>

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
								this.fetchEntries( { categories: value } );
							} }
						/>

						<div style={ { marginTop: '20px' } }>
							<ToggleControl
								label={ __( 'Entries must be assigned to all the above chosen categories?', 'connections' ) }
								// help={__( '', 'connections' )}
								checked={ ! ! categoriesIn }
								onChange={ () => {
									this.setAttributes( { categoriesIn: ! categoriesIn } );
									this.fetchEntries( { category_in: ! categoriesIn } );
								} }
							/>
						</div>

						<div style={ { marginTop: '20px' } }>
							<p>
								{ __( 'Choose the categories to exclude from the entry list.', 'connections' ) }
							</p>
						</div>

						<HierarchicalTermSelector
							taxonomy='category'
							terms={ this.getAttribute( 'categoriesExclude', [] ) }
							onChange={ ( value ) => {
								this.setAttributes( { categoriesExclude: value } );
								this.fetchEntries( { categories_exclude: value } );
							} }
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
								onChange={ ( value ) => this.setAttributes( { imageType: value } ) }
							/>

							<RadioControl
								label={ __( 'Shape', 'connections' ) }
								selected={ imageShape }
								options={ [
									{ value: 'circle', label: __( 'Circle', 'connections' ) },
									{ value: 'square', label: __( 'Square', 'connections' ) },
								] }
								onChange={ ( value ) => {

									let atts = { imageShape: value };

									if ( 'square' === value ) {
										atts.imageBorderRadius = 0;
									}

									this.setAttributes( atts );
								} }
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
								onChange={ () => this.setAttributes( { displayTitle: ! displayTitle } ) }
							/>

							<ToggleControl
								label={ __( 'Display Primary Phone?', 'connections' ) }
								checked={ ! ! displayPhone }
								onChange={ () => this.setAttributes( { displayPhone: ! displayPhone } ) }
							/>

							<ToggleControl
								label={ __( 'Display Primary Email?', 'connections' ) }
								checked={ ! ! displayEmail }
								onChange={ () => this.setAttributes( { displayEmail: ! displayEmail } ) }
							/>

							<ToggleControl
								label={ __( 'Display Excerpt?', 'connections' ) }
								checked={ ! ! displayExcerpt }
								onChange={ () => this.setAttributes( { displayExcerpt: ! displayExcerpt } ) }
							/>

							<ToggleControl
								label={ __( 'Display Social Networks?', 'connections' ) }
								checked={ ! ! displaySocial }
								onChange={ () => this.setAttributes( { displaySocial: ! displaySocial } ) }
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
								label={ colorIndicator( __( 'Background Color', 'connections' ), backgroundColor ) }
							>
								<ColorPalette
									className='editor-color-palette-control__color-palette'
									value={ backgroundColor }
									onChange={ ( value ) => this.setAttributes( { backgroundColor: value } ) }
								/>
							</BaseControl>

							<BaseControl
								className='editor-color-palette-control'
								label={ colorIndicator( __( 'Text Color', 'connections' ), textColor ) }
							>
								<ColorPalette
									className='editor-color-palette-control__color-palette'
									value={ textColor }
									onChange={ ( value ) => this.setAttributes( { color: value } ) }
								/>
							</BaseControl>

							<BaseControl
								className='editor-color-palette-control'
								label={ colorIndicator( __( 'Arrow & Dots Color', 'connections' ), arrowDotsColor ) }
							>
								<ColorPalette
									className='editor-color-palette-control__color-palette'
									value={ arrowDotsColor }
									onChange={ ( value ) => this.setAttributes( { arrowDotsColor: value } ) }
								/>
							</BaseControl>

							<BaseControl
								className='editor-color-palette-control'
								label={ colorIndicator( __( 'Border Color', 'connections' ), borderColor ) }
							>
								<ColorPalette
									className='editor-color-palette-control__color-palette'
									value={ borderColor }
									onChange={ ( value ) => this.setAttributes( { borderColor: value } ) }
								/>
							</BaseControl>

							<RangeControl
								label={ __( 'Border Radius', 'connections' ) }
								value={ borderRadius }
								min={ 0 }
								max={ 20 }
								initialPosition={ 3 }
								allowReset={ true }
								onChange={ ( value ) => this.setAttributes( { borderRadius: value } ) }
							/>

							<RangeControl
								label={ __( 'Border Width', 'connections' ) }
								value={ borderWidth }
								min={ 0 }
								max={ 5 }
								initialPosition={ 2 }
								allowReset={ true }
								onChange={ ( value ) => this.setAttributes( { borderWidth: value } ) }
							/>

							<ToggleControl
								label={ __( 'Display Drop Shadow?', 'connections' ) }
								checked={ ! ! displayDropShadow }
								onChange={ () => this.setAttributes( { displayDropShadow: ! displayDropShadow } ) }
							/>

							<BaseControl
								className='editor-color-palette-control'
								label={ colorIndicator( __( 'Image Border Color', 'connections' ), imageBorderColor ) }
							>
								<ColorPalette
									className='editor-color-palette-control__color-palette'
									value={ imageBorderColor }
									onChange={ ( value ) => this.setAttributes( { imageBorderColor: value } ) }
								/>
							</BaseControl>

							<RangeControl
								label={ __( 'Image Border Width', 'connections' ) }
								value={ imageBorderWidth }
								min={ 0 }
								max={ 5 }
								initialPosition={ 0 }
								allowReset={ true }
								onChange={ ( value ) => this.setAttributes( { imageBorderWidth: value } ) }
							/>

							{ imageShape === 'square' &&
							<RangeControl
								label={ __( 'Image Border Radius', 'connections' ) }
								value={ imageBorderRadius }
								min={ 0 }
								max={ 20 }
								initialPosition={ 0 }
								allowReset={ true }
								onChange={ ( value ) => this.setAttributes( { imageBorderRadius: value } ) }
							/>
							}

						</div>

					</PanelBody>

				</InspectorControls>
				<InspectorAdvancedControls>

					<div style={ { marginTop: '20px' } }>
						<RadioControl
							label={ __( 'Image Crop Mode', 'connections' ) }
							selected={ String( imageCropMode ) }
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
							onChange={ ( value ) => {
								this.setAttributes( { imageCropMode: value } );
								this.fetchEntries( { imageCropMode: value } );
							} }
						/>

						<TextControl
							label={ __( 'Excerpt Word Limit', 'connections' ) }
							help={ __( 'Enter 0 for the first sentence only. If the excerpt exceeds the word limit, the excerpt will be truncated at the of the next sentence if it can be determined automatically, so the excerpt word limit may be exceeded in order to display a complete sentence.', 'connections' ) }
							type={ 'number' }
							value={ excerptWordLimit }
							onChange={ ( value ) => {
								this.setAttributes( { excerptWordLimit: value } );

								if ( value ) {

									this.fetchEntries( { _excerpt: { length: value } } );

								} else {

									this.fetchEntries( { _excerpt: {} } );
								}
							} }
						/>

					</div>

				</InspectorAdvancedControls>
			</Fragment>
		);

		let entries = this.state.queryResults;

		const hasEntries = Array.isArray( entries ) && entries.length;

		if ( !hasEntries ) {

			return (
				<Fragment>
					{ inspectorControls }
					<div>
						{ this.state.isLoading ?
							<p>{ __( 'Loading...', 'connections' ) } <Spinner /></p> :
							<p>{ __( 'No directory entries found.', 'connections' ) }</p>
						}
					</div>
				</Fragment>
			)

		} else {

			let settings = {
				arrows:           arrows,
				autoplay:         autoplay,
				autoplaySpeed:    this.getAttribute( 'autoplaySpeed', 3000 ),
				dots:             dots,
				infinite:         infinite,
				lazyLoad:         false,
				pauseOnFocus:     pause,
				pauseOnHover:     pause,
				pauseOnDotsHover: pause,
				speed:            this.getAttribute( 'speed', 500 ),
				slidesToShow:     this.getAttribute( 'slidesToShow', 1 ),
				slidesToScroll:   this.getAttribute( 'slidesToScroll', 1 ),
			};

			// const imageSize = 'photo' === imageType ? 'large' : 'scaled';

			const slides = entries.map( ( entry, i ) => {

					return (
						<div key={ i }>
							<div className='slick-slide-grid'>
								<div className='slick-slide-column'>
									<EntryImage entry={ entry } type={ imageType } size='custom' />
									<EntryName tag='h3' entry={ entry } />
									{ displayTitle && <EntryTitle entry={ entry } />}
									{ displayPhone && <EntryPhoneNumbers entry={ entry } preferred={ true } />}
									{ displayEmail && <EntryEmail entry={ entry } preferred={ true } /> }
									{ displaySocial && <EntrySocialNetworks entry={ entry } />}
								</div>
								<div className='slick-slide-column'>
									{ displayExcerpt && <EntryExcerpt entry={ entry } />}
								</div>
							</div>
						</div>
					)
				}
			);

			const classNames = [ 'slick-slider-block' ];

			if ( arrows ) classNames.push( 'slick-slider-has-arrows' );
			if ( dots ) classNames.push( 'slick-slider-has-dots' );
			if ( displayDropShadow ) classNames.push( 'slick-slider-has-shadow' );

			classNames.push( `slick-slider-slides-${ settings.slidesToShow }` );

			return (
				<Fragment>
					{ inspectorControls }
					<div className={ classNames.join( ' ' ) }
					     id={ 'slick-slider-block-' + blockId }
					>
						<Slider key={ this.state.sliderKey } { ...settings }>
							{ slides }
						</Slider>
					</div>
				</Fragment>
			)

		}

	}
}

export default compose( [
	withDispatch( ( dispatch, { value } ) => {

		return {
			setMetaFieldValue: ( value ) => {
				dispatch( 'core/editor' ).editPost( { meta: { _cbd_carousel_blocks: value } } );
			},
		};

	} ),
	withSelect( ( select, props ) => {

		return {
			// editorBlocks: select( 'core/editor' ).getBlocks(),
			metaCarousels: select( 'core/editor' ).getEditedPostAttribute( 'meta' )._cbd_carousel_blocks,
		};

	} ),
	withInstanceId
	]
)( Carousel )
