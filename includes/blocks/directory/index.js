/**
 * WordPress dependencies
 */
const { __, _n, _nx, _x } = wp.i18n;
// const { select } = wp.data;
const { registerBlockType } = wp.blocks;
const {
	      InspectorControls,
	      InspectorAdvancedControls,
      } = wp.editor;
const {
	      ServerSideRender,
	      PanelBody,
	      // CheckboxControl,
	      SelectControl,
	      TextControl,
	      ToggleControl
      } = wp.components;

/**
 * Internal dependencies
 */
import PageSelect from '../components/page-select';
import HierarchicalTermSelector from '../components/hierarchical-term-selector';

// Import CSS
import './styles/editor.scss';
import './styles/public.scss';

const {
	      entryTypes,
	      dateTypes,
	      templates
      } = cbDir.blockSettings;

/**
 * Register Block
 */
export default registerBlockType(
	'connections-directory/shortcode-connections',
	{
		title:       __( 'Directory', 'connections' ),
		description: __( 'Display the Connections Business Directory.', 'connections' ),
		category:    'connections-directory',
		// icon:        giveLogo,
		keywords:    [
			'connections',
			__( 'directory', 'connections' ),
		],
		supports:    {
			// Remove the support for the generated className.
			className:       false,
			// Remove the support for the custom className.
			customClassName: false,
			// Remove the support for editing the block using the block HTML editor.
			html:            false,
		},
		attributes: {
			advancedBlockOptions: {
				type:    'string',
				default: '',
			},
			categories:           {
				type:    'string',
				default: '[]', // needs to be a valid empty JSON array.
			},
			characterIndex:       {
				type:    'boolean',
				default: true,
			},
			excludeCategories:           {
				type:    'string',
				default: '[]', // needs to be a valid empty JSON array.
			},
			forceHome:            {
				type:    'boolean',
				default: false,
			},
			homePage:             {
				type:    'string',
				default: ''
			},
			inCategories:      {
				type:    'boolean',
				default: false,
			},
			isEditorPreview:      {
				type:    'boolean',
				default: true,
			},
			listType:             {
				type:    'string',
				default: 'all',
			},
			order:                {
				type:    'string',
				default: 'asc',
			},
			orderBy:              {
				type:    'string',
				default: 'default',
			},
			orderRandom:          {
				type:    'boolean',
				default: false,
			},
			parseQuery:           {
				type:    'boolean',
				default: true
			},
			repeatCharacterIndex: {
				type:    'boolean',
				default: false,
			},
			sectionHead:          {
				type:    'boolean',
				default: false,
			},
			template:             {
				type:    'string',
				default: templates.active
			}
		},
		edit:        function( { attributes, setAttributes } ) {

			const {
				      advancedBlockOptions,
				      categories,
				      characterIndex,
				      excludeCategories,
				      forceHome,
				      homePage,
				      inCategories,
				      listType,
				      order,
				      orderBy,
				      orderRandom,
				      parseQuery,
				      repeatCharacterIndex,
				      sectionHead,
				      template
			      } = attributes;

			// const { getCurrentPostId } = select( 'core/editor' );
			// const postId               = getCurrentPostId();

			const templateOptions        = [];
			const entryTypeSelectOptions = [];
			const dateTypeSelectOptions  = [];

			for ( let property in templates.registered ) {

				// noinspection JSUnfilteredForInLoop
				templateOptions.push({
					label: templates.registered[ property ],
					value: property
				})
			}

			for ( let property in entryTypes ) {

				// noinspection JSUnfilteredForInLoop
				entryTypeSelectOptions.push({
					label: entryTypes[ property ],
					value: property
				})
			}

			for ( let property in dateTypes ) {

				// noinspection JSUnfilteredForInLoop
				dateTypeSelectOptions.push({
					label: __( 'Date:', 'connections' ) + ' ' + dateTypes[ property ],
					value: property
				})
			}

			return [
				<InspectorControls>
					<PanelBody
						title={__( 'Character Index', 'connections' )}
						initialOpen={false}
					>

						<ToggleControl
							label={__( 'Display Character Index?', 'connections' )}
							help={__( 'Display the A-Z index above the directory.', 'connections' )}
							checked={!!characterIndex}
							onChange={() => setAttributes( { characterIndex: !characterIndex } )}
						/>

						<ToggleControl
							label={__( 'Repeat Character Index?', 'connections' )}
							help={__( 'Repeat the Character Index at the beginning of each character group.', 'connections' )}
							checked={!!repeatCharacterIndex}
							onChange={() => setAttributes( { repeatCharacterIndex: !repeatCharacterIndex } )}
						/>

						<ToggleControl
							label={__( 'Display Current Character Heading?', 'connections' )}
							help={__( 'Display the current character heading at the beginning of each character group.', 'connections' )}
							checked={!!sectionHead}
							onChange={() => setAttributes( { sectionHead: !sectionHead } )}
						/>

					</PanelBody>

					<PanelBody
						title={__( 'Template', 'connections' )}
						initialOpen={false}
					>
						<SelectControl
							label={__( 'Template', 'connections' )}
							help={__( 'Select which to use when displaying the directory.', 'connections' )}
							value={template}
							options={templateOptions}
							onChange={( template ) => setAttributes( { template: template } )}
						/>
					</PanelBody>

					<PanelBody
						title={__( 'Select', 'connections' )}
						initialOpen={true}
					>
						<p>
							{__( 'This section controls which entries from your directory will be displayed.', 'connections' )}
						</p>

						<div style={{ marginTop: '20px' }}>
							<SelectControl
								label={__( 'Entry Type', 'connections' )}
								help={__( 'Select which entry type to display. The default is to display all.', 'connections' )}
								value={listType}
								options={[
									{ label: __( 'All', 'connections' ), value: 'all' },
									...entryTypeSelectOptions
								]}
								onChange={( listType ) => setAttributes( { listType: listType } )}
							/>
						</div>

						<div style={{ marginTop: '20px' }}>
							<p>
								{__( 'Choose the categories to include in the entry list.', 'connections' )}
							</p>
						</div>

						<HierarchicalTermSelector
							taxonomy='category'
							terms={ JSON.parse( categories ) }
							onChange={( categories ) => setAttributes( { categories: JSON.stringify( categories ) } )}
						/>

						<div style={{ marginTop: '20px' }}>
							<ToggleControl
								label={__( 'Entries must be assigned to all the above chosen categories?', 'connections' )}
								// help={__( '', 'connections' )}
								checked={!!inCategories}
								onChange={() => setAttributes( { inCategories: !inCategories } )}
							/>
						</div>

						<div style={{ marginTop: '20px' }}>
							<p>
								{__( 'Choose the categories to exclude from the entry list.', 'connections' )}
							</p>
						</div>

						<HierarchicalTermSelector
							taxonomy='category'
							terms={ JSON.parse( excludeCategories ) }
							onChange={( excludeCategories ) => setAttributes( { excludeCategories: JSON.stringify( excludeCategories ) } )}
						/>

					</PanelBody>

					<PanelBody
						title={__( 'Order', 'connections' )}
						initialOpen={false}
					>
						<p>
							{__( 'This section controls the order in which the selected entries will be displayed.', 'connections' )}
						</p>

						<SelectControl
							label={__( 'Order By', 'connections' )}
							value={orderBy}
							options={[
								{ label: __( 'Default', 'connections' ), value: 'default' },
								{ label: __( 'First Name', 'connections' ), value: 'first_name' },
								{ label: __( 'Last Name', 'connections' ), value: 'last_name' },
								{ label: __( 'Title', 'connections' ), value: 'title' },
								{ label: __( 'Organization', 'connections' ), value: 'organization' },
								{ label: __( 'Department', 'connections' ), value: 'department' },
								{ label: __( 'City', 'connections' ), value: 'city' },
								{ label: __( 'State', 'connections' ), value: 'state' },
								{ label: __( 'Zipcode', 'connections' ), value: 'zipcode' },
								{ label: __( 'Country', 'connections' ), value: 'country' },
								{ label: __( 'Date: Entry Added', 'connections' ), value: 'date_added' },
								{ label: __( 'Date: Entry Last Modified', 'connections' ), value: 'date_modified' },
								...dateTypeSelectOptions
							]}
							onChange={( orderBy ) => setAttributes( { orderBy: orderBy } )}
							disabled={!!orderRandom}
						/>

						<SelectControl
							label={__( 'Order', 'connections' )}
							value={order}
							options={[
								{ label: __( 'Ascending', 'connections' ), value: 'asc' },
								{ label: __( 'Descending', 'connections' ), value: 'desc' },
								{ label: __( 'Random', 'connections' ), value: 'random' },
							]}
							onChange={( order ) => setAttributes( {
								order:       order,
								orderBy:     'random' === order ? 'default' : orderBy,
								orderRandom: 'random' === order
							} )}
						/>

					</PanelBody>

				</InspectorControls>,
				<InspectorAdvancedControls>

					<p>
						{__( 'This section controls advanced options which effect the directory features and functions.', 'connections' )}
					</p>

					<ToggleControl
						label={__( 'Parse query?', 'connections' )}
						help={__( 'Permit the Directory block instance to parse queries in order to affect the displayed results. Example, allowing keyword searches. The default is to allow query parsing.', 'connections' )}
						checked={!!parseQuery}
						onChange={() => setAttributes( { parseQuery: !parseQuery } )}
					/>

					<PageSelect
						// postType={'post'}
						label={__( 'Directory Home Page', 'connections' )}
						noOptionLabel={__( 'Current Page', 'connections' )}
						value={homePage}
						onChange={( homePage ) => setAttributes( { homePage: homePage } )}
						disabled={!!forceHome}
					/>

					<ToggleControl
						label={__( 'Force directory permalinks to resolve to the Global Directory Homes page?', 'connections' )}
						checked={!!forceHome}
						onChange={() => setAttributes( {
							forceHome: !forceHome,
							homePage: '',
						} )}
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

				</InspectorAdvancedControls>,
				<ServerSideRender
					attributes={attributes}
					block='connections-directory/shortcode-connections'
				/>
			];

		},
		save:        function() {
			// Server side rendering via shortcode.
			return null;
		},
	}
);
