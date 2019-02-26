/**
 * WordPress dependencies
 */
const { __, _n, _nx, _x } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
	      InspectorControls,
	      InspectorAdvancedControls,
      } = wp.editor;
const {
	      ExternalLink,
	      PanelBody,
	      // RangeControl,
	      RadioControl,
	      SelectControl,
	      ServerSideRender,
	      TextControl,
	      ToggleControl,
      } = wp.components;

const {
	      // Component,
	      Fragment,
      } = wp.element;

/**
 * Internal dependencies
 */
import { RangeControl } from '@Connections-Directory/components';

// Import CSS
import './styles/editor.scss';
import './styles/public.scss';

const { dateTypes } = cbDir.blockSettings;

/**
 * Register Block
 */
export default registerBlockType(
	'connections-directory/shortcode-upcoming',
	{
		title:       __( 'Upcoming', 'connections' ),
		description: __( 'Display the list of upcoming event dates.', 'connections' ),
		category:    'connections-directory',
		// icon:        giveLogo,
		keywords:    [
			'connections',
			__( 'directory', 'connections' ),
			__( 'upcoming', 'connections' ),
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
			advancedBlockOptions: {
				type:    'string',
				default: '',
			},
			displayLastName:      {
				type:    'boolean',
				default: false,
			},
			dateFormat:           {
				type:    'string',
				default: 'F jS',
			},
			days:                 {
				type:    'integer',
				default: 30,
			},
			heading:              {
				type:    'string',
				default: '',
			},
			includeToday:         {
				type:    'boolean',
				default: true,
			},
			isEditorPreview:      {
				type:    'boolean',
				default: true,
			},
			listType:             {
				type:    'string',
				default: 'birthday',
			},
			template:             {
				type:    'string',
				default: 'anniversary-light',
			},
			noResults:            {
				type:    'string',
				default: __( 'No results.', 'connections' ),
			},
			yearFormat:           {
				type:    'string',
				default: '%y ' + __( 'Year(s)', 'connections' ),
			},
			yearType:             {
				type:    'string',
				default: 'upcoming',
			},
		},
		edit:        function( { attributes, setAttributes } ) {

			const {
				      advancedBlockOptions,
				      displayLastName,
				      dateFormat,
				      days,
				      heading,
				      includeToday,
				      listType,
				      template,
				      noResults,
				      yearFormat,
				      yearType
			      } = attributes;

			const dateTypeSelectOptions = [];

			for ( let property in dateTypes ) {

				// noinspection JSUnfilteredForInLoop
				dateTypeSelectOptions.push( {
					label: dateTypes[ property ],
					value: property
				} )
			}

			return (
				<Fragment>
					<InspectorControls>
						<PanelBody title={__( 'Settings', 'connections' )}>

							<SelectControl
								label={__( 'Type', 'connections' )}
								value={listType}
								options={dateTypeSelectOptions}
								onChange={( listType ) => setAttributes( { listType: listType } )}
							/>

							<SelectControl
								label={__( 'Style', 'connections' )}
								value={template}
								options={[
									{ label: 'Light', value: 'anniversary-light' },
									{ label: 'Dark', value: 'anniversary-dark' },
								]}
								onChange={( template ) => setAttributes( { template: template } )}
							/>

							<TextControl
								label={__( 'Heading', 'connections' )}
								help={__( 'Type %d to insert the number of days in the heading.', 'connections' )}
								placeholder={__( 'Type the heading here.', 'connections' )}
								value={heading}
								onChange={( newValue ) => {
									setAttributes( {
										heading: newValue,
									} );
								}}
							/>

							<ToggleControl
								label={__( 'Display last name?', 'connections' )}
								checked={!!displayLastName}
								onChange={() => setAttributes( { displayLastName: !displayLastName } )}
							/>

							<RangeControl
								label={__( 'The number of days ahead to display.', 'connections' )}
								help={__( 'To display date events for today only, slide the slider to 0 and enable the Include today option.', 'connections' )}
								value={days}
								onChange={( days ) => setAttributes( { days: days } )}
								min={0}
								max={90}
								allowReset={true}
								initialPosition={30}
							/>

							<ToggleControl
								label={__( 'Include today?', 'connections' )}
								help={__( 'Whether or not to include the date events for today.', 'connections' )}
								checked={!!includeToday}
								onChange={() => setAttributes( { includeToday: !includeToday } )}
							/>

							<RadioControl
								label={__( 'Year Display', 'connections' )}
								// help={__( '', 'connections' )}
								selected={yearType}
								options={[
									{ label: __( 'Original Year', 'connections' ), value: 'original' },
									{ label: __( 'Upcoming Year', 'connections' ), value: 'upcoming' },
									{ label: __( 'Years Since', 'connections' ), value: 'since' },
								]}
								onChange={( newValue ) => {
									setAttributes( {
										yearType: newValue,
									} );
								}}
							/>

							<TextControl
								label={__( 'No Results Notice', 'connections' )}
								help={__( 'This message is displayed when there are no upcoming event dates within the specified number of days.', 'connections' )}
								placeholder={__( 'Type the no result message here.', 'connections' )}
								value={noResults}
								onChange={( newValue ) => {
									setAttributes( {
										noResults: newValue,
									} );
								}}
							/>

						</PanelBody>
					</InspectorControls>
					<InspectorAdvancedControls>

						<TextControl
							label={__( 'Date Format', 'connections' )}
							help={
								<ExternalLink
									href='https://codex.wordpress.org/Formatting_Date_and_Time'
									target='_blank'
								>
									{__( 'Documentation on date and time formatting.', 'connections' )}
								</ExternalLink>
							}
							value={dateFormat}
							onChange={( newValue ) => {
								setAttributes( {
									dateFormat: newValue,
								} );
							}}
						/>

						<TextControl
							label={__( 'Years Since Format', 'connections' )}
							help={
								<ExternalLink
									href='http://php.net/manual/en/dateinterval.format.php'
									target='_blank'
								>
									{__( 'Documentation on date interval formatting.', 'connections' )}
								</ExternalLink>
							}
							value={yearFormat}
							onChange={( newValue ) => {
								setAttributes( {
									yearFormat: newValue,
								} );
							}}
						/>

						<TextControl
							label={__( 'Additional Options', 'connections' )}
							value={advancedBlockOptions}
							onChange={( newValue ) => {
								setAttributes( {
									advancedBlockOptions: newValue,
								} );
							}}
						/>

					</InspectorAdvancedControls>
					<ServerSideRender
						attributes={attributes}
						block='connections-directory/shortcode-upcoming'
					/>
				</Fragment>
			);

		},
		save:        function() {
			// Server side rendering via shortcode.
			return null;
		},
	}
);
