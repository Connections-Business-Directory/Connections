/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import {
	BaseControl,
	ColorIndicator,
	ColorPalette,
	PanelBody,
	RadioControl,
	SelectControl,
	TextControl,
	ToggleControl
} from '@wordpress/components';
import {
	InspectorControls,
	InspectorAdvancedControls,
} from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import {
	HierarchicalTermSelector,
	RangeControl,
} from '@Connections-Directory/components';

const {
	entryTypes,
	// dateTypes,
	// templates,
} = cbDir.blockSettings;

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

/**
 * Register Block
 */
export default registerBlockType(
	'connections-directory/team',
	{
		title:       __( 'Team', 'connections' ),
		description: __( 'Display members of your team. Use multiple Team blocks to create a team page.', 'connections' ),
		category:    'connections-directory',
		// icon:        giveLogo,
		keywords:    [
			'connections',
			__( 'team', 'connections' ),
			__( 'person', 'connections' ),
		],
		supports:    {
			// Remove the support for the generated className.
			className:       false,
			// Remove the support for the custom className.
			customClassName: false,
			// Remove the support for editing the block using the block HTML editor.
			html:            false,
		},
		attributes:  {
			// Valid attribute types are: string, boolean, object, null, array, integer, number
			// @see link https://github.com/WordPress/gutenberg/blob/master/packages/blocks/src/api/parser.js
			advancedBlockOptions: {
				type:    'string',
				default: '',
			},
			categories:           {
				type:    'string',
				default: '[]',
			},
			categoriesExclude:    {
				type:    'string',
				default: '[]',
			},
			categoriesIn:         {
				type:    'boolean',
				default: false,
			},
			columns:              {
				type:    'integer',
				default: 3,
			},
			borderColor:          {
				default: '#BABABA',
			},
			borderRadius:         {
				type:    'integer',
				default: 12,
			},
			borderWidth:          {
				type:    'integer',
				default: 1,
			},
			displayDropShadow:    {
				type:    'boolean',
				default: true,
			},
			displayEmail:         {
				type:    'boolean',
				default: true,
			},
			displayExcerpt:       {
				type:    'boolean',
				default: false,
			},
			displayPhone:         {
				type:    'boolean',
				default: true,
			},
			displaySocial:        {
				type:    'boolean',
				default: true,
			},
			displayTitle:         {
				type:    'boolean',
				default: true,
			},
			excerptWordLimit:     {
				type:    'string',
				default: '10',
			},
			gutterWidth:          {
				type:    'integer',
				default: 25,
			},
			imageBorderColor:     {
				default: '#BABABA',
			},
			imageBorderRadius:    {
				type:    'integer',
				default: 0,
			},
			imageBorderWidth:     {
				type:    'integer',
				default: 0,
			},
			imageCropMode:        {
				type:    'string',
				default: '1',
			},
			imageShape:           {
				type:    'string',
				default: 'square',
			},
			imageType:            {
				type:    'string',
				default: 'photo',
			},
			isEditorPreview:      {
				type:    'boolean',
				default: true,
			},
			layout:               {
				type:    'string',
				default: 'grid',
			},
			listType:             {
				type:    'string',
				default: 'all',
			},
			// rows:              {
			// 	type:    'integer',
			// 	default: 1,
			// },
			position:             {
				type:    'string',
				default: 'left',
			},
			style:                {
				type:    'string',
				default: 'clean',
			},
			variation:            {
				type:    'string',
				default: 'card',
			},
		},
		edit:        ( { attributes, setAttributes } ) => {

			const {
				      advancedBlockOptions,
				      categories,
				      categoriesExclude,
				      categoriesIn,
				      columns,
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
				      gutterWidth,
				      imageBorderColor,
				      imageBorderRadius,
				      imageBorderWidth,
				      imageCropMode,
				      imageShape,
				      imageType,
				      layout,
				      listType,
				      position,
				      // rows,
				      style,
				      variation,
			      } = attributes;

			const entryTypeSelectOptions = [];

			for ( let property in entryTypes ) {

				// noinspection JSUnfilteredForInLoop
				entryTypeSelectOptions.push( {
					label: entryTypes[ property ],
					value: property
				} )
			}

			/*
			 * Ideas:
			 * - https://codecanyon.net/item/the-team-pro-team-showcase-wordpress-plugin/17521235
			 * - https://codecanyon.net/item/heroes-assemble-team-showcase-wordpress-plugin/11469747
			 * - https://wpdarko.com/items/team-members-pro/
			 * - http://teammembers.themescode.com/
			 *
			 * @Style Options
			 * - Grid
			 * -- Drawer
			 * --- Full Overlay
			 * --- Bottom Overlay
			 * -- Modal
			 * --- Full Overlay
			 * --- Bottom Overlay
			 * -- Slide
			 * --- Left
			 * ---- Full Overlay
			 * ---- Bottom Overlay
			 * --- Right
			 * ---- Full Overlay
			 * ---- Bottom Overlay
			 * -- Tile
			 * --- Slide Up
			 * --- Card Pop Up
			 * --- Overlay
			 * -- Circle
			 * --- Overlay
			 * --- Fade
			 * --- Flip
			 * -- Card
			 * --- Clean/Simple
			 * --- Overlay
			 * --- Flip
			 * - Table
			 * - List
			 * -- Circle
			 * --- Left
			 * --- Right
			 * -- Square
			 * --- Left
			 * --- Right
			 */

			const setLayoutDefaults = ( layout ) => {

				switch ( layout ) {

					case 'grid':

						setAttributes( {
							columns:           3,
							displayDropShadow: true,
							displayExcerpt:    false,
							excerptWordLimit:  10,
							gutterWidth:       25,
							imageShape:        'square',
							layout:            layout,
							style:             'clean',
							variation:         'card',
						} );
						break;

					case 'list':

						setAttributes( {
							borderRadius:      0,
							borderWidth:       0,
							displayDropShadow: false,
							displayExcerpt:    true,
							excerptWordLimit:  55,
							imageShape:        'square',
							layout:            layout,
							position:          'left',
						} );
						break;

					case 'table':

						setAttributes( {
							borderRadius:      0,
							borderWidth:       0,
							displayDropShadow: false,
							displayExcerpt:    true,
							excerptWordLimit:  55,
							imageShape:        'square',
							layout:            layout,
							// rows:       1,
						} );
						break
				}

			};

			const setVariationDefaults = ( variation ) => {

				switch ( variation ) {

					case 'card':

						setAttributes( {
							imageShape: 'square',
							style:      'clean',
							variation:  variation,
						} );
						break;

					case 'circle':

						setAttributes( {
							imageShape: 'circle',
							style:      'fade',
							variation:  variation,
						} );
						break;

					case 'drawer':

						setAttributes( {
							imageShape: 'square',
							style:      'overlay-bottom',
							variation:  variation,
						} );
						break;

					case 'modal':

						setAttributes( {
							imageShape: 'square',
							variation:  variation,
						} );
						break;

					case 'slide':

						setAttributes( {
							imageShape: 'square',
							style:      'overlay-bottom',
							position:   'left',
							variation:  variation,
						} );
						break;
				}
			};

			const variationOptions = () => {

				let options = [];

				switch ( layout ) {

					case 'grid':

						options = [
							{ value: 'card', label: __( 'Card', 'connections' ) },
							// { value: 'circle', label: __( 'Circle', 'connections' ) },
							// { value: 'drawer', label: __( 'Drawer', 'connections' ) },
							// { value: 'modal', label: __( 'Modal', 'connections' ) },
							// { value: 'slide', label: __( 'Slide', 'connections' ) },
						];

						return (
							<RadioControl
								label={ __( 'Variation', 'connections' ) }
								selected={ variation }
								options={ options }
								onChange={ ( value ) => {
									setVariationDefaults( value );
								} }
							/>
						);

					case 'list':

						return null;

					case 'table':
						return null;

					default:
						return null;
				}

			};

			const positionOptions = () => {

				if ( layout === 'list' || ( layout === 'grid' && variation === 'slide' ) ) {

					let options = [
						{ value: 'left', label: __( 'Left', 'connections' ) },
						{ value: 'right', label: __( 'Right', 'connections' ) },
					];

					return (
						<RadioControl
							label={ __( 'Position', 'connections' ) }
							selected={ position }
							options={ options }
							onChange={ ( value ) => setAttributes( { position: value } ) }
						/>
					);
				}
			};

			const styleOptions = () => {

				let options = [];

				if ( 'grid' === layout ) {

					switch ( variation ) {

						case 'card':

							options.push( { value: 'clean', label: __( 'Clean', 'connections' ) } );
							options.push( { value: 'flip', label: __( 'Flip', 'connections' ) } );
							options.push( { value: 'slide', label: __( 'Slide', 'connections' ) } );
							options.push( { value: 'overlay', label: __( 'Overlay', 'connections' ), disabled: true } );
							break;

						case 'circle':

							options.push( { value: 'fade', label: __( 'Fade', 'connections' ) } );
							options.push( { value: 'flip', label: __( 'Flip', 'connections' ) } );
							options.push( { value: 'overlay', label: __( 'Overlay', 'connections' ) } );
							break;

						case 'drawer':

							options.push( { value: 'overlay-bottom', label: __( 'Bottom Overlay', 'connections' ) } );
							options.push( { value: 'overlay-full', label: __( 'Full Overlay', 'connections' ) } );
							break;

						case 'modal':

							options.push( { value: 'overlay-bottom', label: __( 'Bottom Overlay', 'connections' ) } );
							options.push( { value: 'overlay-full', label: __( 'Full Overlay', 'connections' ) } );
							break;

						case 'slide':

							options.push( { value: 'overlay-bottom', label: __( 'Bottom Overlay', 'connections' ) } );
							options.push( { value: 'overlay-full', label: __( 'Full Overlay', 'connections' ) } );
							break;
					}
				}

				if ( 0 < options.length ) {

					return (
						<RadioControl
							label={ __( 'Style', 'connections' ) }
							selected={ style }
							options={ options }
							onChange={ ( value ) => setAttributes( { style: value } ) }
						/>
					);
				}
			};

			return (
				<Fragment>
					<InspectorControls>

						<PanelBody
							title={ __( 'Layout', 'connections' ) }
							initialOpen={ true }
						>

							<RadioControl
								label={ __( 'Layout', 'connections' ) }
								selected={ layout }
								options={ [
									{ value: 'grid', label: __( 'Grid', 'connections' ) },
									{ value: 'list', label: __( 'List', 'connections' ) },
									{ value: 'table', label: __( 'Table', 'connections' ) },
								] }
								onChange={ ( value ) => {
									setLayoutDefaults( value );
								} }
							/>

							{ variationOptions() }
							{ styleOptions() }
							{ positionOptions() }

							{ layout === 'grid' &&
							<Fragment>

								<RangeControl
									label={ __( 'Columns', 'connections' ) }
									value={ columns }
									min={ 1 }
									max={ 5 }
									initialPosition={ 3 }
									allowReset={ false }
									onChange={ ( value ) => setAttributes( { columns: value } ) }
								/>

								<RangeControl
									label={ __( 'Gutter Width', 'connections' ) }
									help={ __( 'The space between columns.', 'connections' ) }
									value={ gutterWidth }
									min={ 0 }
									max={ 50 }
									initialPosition={ 25 }
									allowReset={ true }
									onChange={ ( value ) => setAttributes( { gutterWidth: value } ) }
								/>

							</Fragment>
							}

							{ ( false && ( layout === 'list' || layout === 'table' ) ) &&
							<RangeControl
								label={ __( 'Rows', 'connections' ) }
								value={ rows }
								min={ 1 }
								max={ 10 }
								initialPosition={ 1 }
								allowReset={ false }
								onChange={ ( value ) => setAttributes( { rows: value } ) }
							/>
							}

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
									value={ listType }
									options={ [
										{ label: __( 'All', 'connections' ), value: 'all' },
										...entryTypeSelectOptions
									] }
									onChange={ ( value ) => setAttributes( { listType: value } ) }
								/>
							</div>

							<div style={ { marginTop: '20px' } }>
								<p>
									{ __( 'Choose the categories to include in the entry list.', 'connections' ) }
								</p>
							</div>

							<HierarchicalTermSelector
								taxonomy='category'
								terms={ JSON.parse( categories ) }
								onChange={ ( value ) => setAttributes( { categories: JSON.stringify( value ) } ) }
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
								onChange={ ( value ) => setAttributes( { categoriesExclude: JSON.stringify( value ) } ) }
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

								{ ! ( variation === 'circle' || variation === 'drawer' ) &&
								<RadioControl
									label={ __( 'Shape', 'connections' ) }
									selected={ imageShape }
									options={ [
										{ value: 'circle', label: __( 'Circle', 'connections' ) },
										{ value: 'square', label: __( 'Square', 'connections' ) },
									] }
									onChange={ ( value ) => setAttributes( { imageShape: value } ) }
								/>
								}
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
								{ ( layout === 'grid' || layout === 'list' ) &&
								<Fragment>

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

								</Fragment>
								}

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

							<TextControl
								label={ __( 'Additional Options', 'connections' ) }
								value={ advancedBlockOptions }
								onChange={ ( value ) => setAttributes( { advancedBlockOptions: value, } ) }
							/>
						</div>

					</InspectorAdvancedControls>
					<ServerSideRender
						attributes={ attributes }
						block='connections-directory/team'
					/>
				</Fragment>
			);
		},
		save:        () => {
			// Server side rendering via shortcode.
			return null;
		},
	}
);
