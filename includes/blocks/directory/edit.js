/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import {
	InspectorAdvancedControls,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import {
	FilterTagSelector,
	HierarchicalTermSelector,
	PageSelect,
} from '@Connections-Directory/components';

const { entryTypes, dateTypes, templates } = cbDir.blockSettings;

const Edit = ( { attributes, setAttributes } ) => {
	const {
		advancedBlockOptions,
		categories,
		characterIndex,
		city,
		county,
		country,
		department,
		district,
		excludeCategories,
		forceHome,
		fullName,
		homePage,
		inCategories,
		lastName,
		listType,
		order,
		orderBy,
		orderRandom,
		organization,
		parseQuery,
		repeatCharacterIndex,
		sectionHead,
		state,
		template,
		title,
		zipcode,
	} = attributes;

	// const { getCurrentPostId } = select( 'core/editor' );
	// const postId               = getCurrentPostId();

	const templateOptions = [];
	const entryTypeSelectOptions = [];
	const dateTypeSelectOptions = [];

	for ( const property in templates.registered ) {
		templateOptions.push( {
			label: templates.registered[ property ],
			value: property,
		} );
	}

	for ( const property in entryTypes ) {
		entryTypeSelectOptions.push( {
			label: entryTypes[ property ],
			value: property,
		} );
	}

	for ( const property in dateTypes ) {
		dateTypeSelectOptions.push( {
			label: __( 'Date:', 'connections' ) + ' ' + dateTypes[ property ],
			value: property,
		} );
	}

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody
					title={ __( 'Character Index', 'connections' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __(
							'Display Character Index?',
							'connections'
						) }
						help={ __(
							'Display the A-Z index above the directory.',
							'connections'
						) }
						checked={ !! characterIndex }
						onChange={ () =>
							setAttributes( {
								characterIndex: ! characterIndex,
							} )
						}
					/>

					<ToggleControl
						label={ __( 'Repeat Character Index?', 'connections' ) }
						help={ __(
							'Repeat the Character Index at the beginning of each character group.',
							'connections'
						) }
						checked={ !! repeatCharacterIndex }
						onChange={ () =>
							setAttributes( {
								repeatCharacterIndex: ! repeatCharacterIndex,
							} )
						}
					/>

					<ToggleControl
						label={ __(
							'Display Current Character Heading?',
							'connections'
						) }
						help={ __(
							'Display the current character heading at the beginning of each character group.',
							'connections'
						) }
						checked={ !! sectionHead }
						onChange={ () =>
							setAttributes( {
								sectionHead: ! sectionHead,
							} )
						}
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Template', 'connections' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Template', 'connections' ) }
						help={ __(
							'Select which to use when displaying the directory.',
							'connections'
						) }
						value={ template }
						options={ templateOptions }
						onChange={ ( value ) =>
							setAttributes( { template: value } )
						}
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Select', 'connections' ) }
					initialOpen={ true }
				>
					<p>
						{ __(
							'This section controls which entries from your directory will be displayed.',
							'connections'
						) }
					</p>

					<div style={ { marginTop: '20px' } }>
						<SelectControl
							label={ __( 'Entry Type', 'connections' ) }
							help={ __(
								'Select which entry type to display. The default is to display all.',
								'connections'
							) }
							value={ listType }
							options={ [
								{
									label: __( 'All', 'connections' ),
									value: 'all',
								},
								...entryTypeSelectOptions,
							] }
							onChange={ ( value ) =>
								setAttributes( { listType: value } )
							}
						/>
					</div>

					<div style={ { marginTop: '20px' } }>
						<p>
							{ __(
								'Choose the categories to include in the entry list.',
								'connections'
							) }
						</p>
					</div>

					<HierarchicalTermSelector
						taxonomy="category"
						terms={ JSON.parse( categories ) }
						onChange={ ( value ) =>
							setAttributes( {
								categories: JSON.stringify( value ),
							} )
						}
					/>

					<div style={ { marginTop: '20px' } }>
						<ToggleControl
							label={ __(
								'Entries must be assigned to all the above chosen categories?',
								'connections'
							) }
							// help={__( '', 'connections' )}
							checked={ !! inCategories }
							onChange={ () =>
								setAttributes( {
									inCategories: ! inCategories,
								} )
							}
						/>
					</div>

					<div style={ { marginTop: '20px' } }>
						<p>
							{ __(
								'Choose the categories to exclude from the entry list.',
								'connections'
							) }
						</p>
					</div>

					<HierarchicalTermSelector
						taxonomy="category"
						terms={ JSON.parse( excludeCategories ) }
						onChange={ ( value ) =>
							setAttributes( {
								excludeCategories: JSON.stringify( value ),
							} )
						}
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Filters', 'connections' ) }
					initialOpen={ false }
				>
					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<p>
							{ __(
								'Only the entries which match all of the selected filters below will be displayed in the results list.',
								'connections'
							) }
						</p>
						<p
							className={ 'components-base-control__help' }
							style={ { marginTop: '4px' } }
						>
							{ __(
								'Start typing for suggestions. You must choose a suggested term for the filter to be applied. More than one term can be entered per filter. If more than one is entered, as long as the entry has a term in any of the inputs, it will display as a result.',
								'connections'
							) }
						</p>
					</div>

					<div style={ { marginTop: '20px' } }>
						<FilterTagSelector
							type={ 'name' } // The autocomplete type.
							label={ __( 'Full Name', 'connections' ) }
							renderField={ 'name' } // The field to display as tags / in autocomplete list.
							getFields={ 'id,name' } // The fields to return from the REST query.
							returnField={ 'id' } // This is the field to return and saved.
							messages={ {
								added: __(
									'Full Name Added to Filter',
									'connections'
								),
								removed: __(
									'Full Name Removed',
									'connections'
								),
								remove: __(
									'Remove Full Name from Filter',
									'connections'
								),
							} }
							terms={ fullName }
							onChange={ ( value ) =>
								setAttributes( { fullName: value } )
							}
						/>
					</div>

					<div style={ { marginTop: '20px' } }>
						<FilterTagSelector
							type={ 'last_name' } // The autocomplete type.
							label={ __( 'Last Name', 'connections' ) }
							renderField={ 'last_name' } // The field to display as tags / in autocomplete list.
							getFields={ 'last_name' } // The fields to return from the REST query.
							returnField={ 'last_name' } // This is the field to return and saved.
							messages={ {
								added: __(
									'Last Name Added to Filter',
									'connections'
								),
								removed: __(
									'Last Name Removed',
									'connections'
								),
								remove: __(
									'Remove Last Name from Filter',
									'connections'
								),
							} }
							terms={ lastName }
							onChange={ ( value ) =>
								setAttributes( { lastName: value } )
							}
						/>
					</div>

					<div style={ { marginTop: '20px' } }>
						<FilterTagSelector
							type={ 'title' } // The autocomplete type.
							label={ __( 'Title', 'connections' ) }
							renderField={ 'title' } // The field to display as tags / in autocomplete list.
							getFields={ 'title' } // The fields to return from the REST query.
							returnField={ 'title' } // This is the field to return and saved.
							messages={ {
								added: __(
									'Title Added to Filter',
									'connections'
								),
								removed: __( 'Title Removed', 'connections' ),
								remove: __(
									'Remove Title from Filter',
									'connections'
								),
							} }
							terms={ title }
							onChange={ ( value ) =>
								setAttributes( { title: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'department' } // The autocomplete type.
							label={ __( 'Department', 'connections' ) }
							renderField={ 'department' } // The field to display as tags / in autocomplete list.
							getFields={ 'department' } // The fields to return from the REST query.
							returnField={ 'department' } // This is the field to return and saved.
							messages={ {
								added: __(
									'Department Added to Filter',
									'connections'
								),
								removed: __(
									'Department Removed',
									'connections'
								),
								remove: __(
									'Remove Department from Filter',
									'connections'
								),
							} }
							terms={ department }
							onChange={ ( value ) =>
								setAttributes( { department: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'organization' } // The autocomplete type.
							label={ __( 'Organization', 'connections' ) }
							renderField={ 'organization' } // The field to display as tags / in autocomplete list.
							getFields={ 'organization' } // The fields to return from the REST query.
							returnField={ 'organization' } // This is the field to return and saved.
							messages={ {
								added: __(
									'Organization Added to Filter',
									'connections'
								),
								removed: __(
									'Organization Removed',
									'connections'
								),
								remove: __(
									'Remove Organization from Filter',
									'connections'
								),
							} }
							terms={ organization }
							onChange={ ( value ) =>
								setAttributes( { organization: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'district' } // The autocomplete type.
							label={ __( 'District', 'connections' ) }
							renderField={ 'district' } // The field to display as tags / in autocomplete list.
							getFields={ 'district' } // The fields to return from the REST query.
							returnField={ 'district' } // This is the field to return and saved.
							messages={ {
								added: __(
									'District Added to Filter',
									'connections'
								),
								removed: __(
									'District Removed',
									'connections'
								),
								remove: __(
									'Remove District from Filter',
									'connections'
								),
							} }
							terms={ district }
							onChange={ ( value ) =>
								setAttributes( { district: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'county' } // The autocomplete type.
							label={ __( 'County', 'connections' ) }
							renderField={ 'county' } // The field to display as tags / in autocomplete list.
							getFields={ 'county' } // The fields to return from the REST query.
							returnField={ 'county' } // This is the field to return and saved.
							messages={ {
								added: __(
									'County Added to Filter',
									'connections'
								),
								removed: __( 'County Removed', 'connections' ),
								remove: __(
									'Remove County from Filter',
									'connections'
								),
							} }
							terms={ county }
							onChange={ ( value ) =>
								setAttributes( { county: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'city' } // The autocomplete type.
							label={ __( 'City', 'connections' ) }
							renderField={ 'city' } // The field to display as tags / in autocomplete list.
							getFields={ 'city' } // The fields to return from the REST query.
							returnField={ 'city' } // This is the field to return and saved.
							messages={ {
								added: __(
									'City Added to Filter',
									'connections'
								),
								removed: __( 'City Removed', 'connections' ),
								remove: __(
									'Remove City from Filter',
									'connections'
								),
							} }
							terms={ city }
							onChange={ ( value ) =>
								setAttributes( { city: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'state' } // The autocomplete type.
							label={ __( 'State', 'connections' ) }
							renderField={ 'state' } // The field to display as tags / in autocomplete list.
							getFields={ 'state' } // The fields to return from the REST query.
							returnField={ 'state' } // This is the field to return and saved.
							messages={ {
								added: __(
									'State Added to Filter',
									'connections'
								),
								removed: __( 'State Removed', 'connections' ),
								remove: __(
									'Remove State from Filter',
									'connections'
								),
							} }
							terms={ state }
							onChange={ ( value ) =>
								setAttributes( { state: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'zipcode' } // The autocomplete type.
							label={ __( 'Zipcode', 'connections' ) }
							renderField={ 'zipcode' } // The field to display as tags / in autocomplete list.
							getFields={ 'zipcode' } // The fields to return from the REST query.
							returnField={ 'zipcode' } // This is the field to return and saved.
							messages={ {
								added: __(
									'Zipcode Added to Filter',
									'connections'
								),
								removed: __( 'Zipcode Removed', 'connections' ),
								remove: __(
									'Remove Zipcode from Filter',
									'connections'
								),
							} }
							terms={ zipcode }
							onChange={ ( value ) =>
								setAttributes( { zipcode: value } )
							}
						/>
					</div>

					<div
						className={ 'components-base-control' }
						style={ { marginTop: '20px' } }
					>
						<FilterTagSelector
							type={ 'country' } // The autocomplete type.
							label={ __( 'Country', 'connections' ) }
							renderField={ 'country' } // The field to display as tags / in autocomplete list.
							getFields={ 'country' } // The fields to return from the REST query.
							returnField={ 'country' } // This is the field to return and saved.
							messages={ {
								added: __(
									'Country Added to Filter',
									'connections'
								),
								removed: __( 'Country Removed', 'connections' ),
								remove: __(
									'Remove Country from Filter',
									'connections'
								),
							} }
							terms={ country }
							onChange={ ( value ) =>
								setAttributes( { country: value } )
							}
						/>
					</div>
				</PanelBody>

				<PanelBody
					title={ __( 'Order', 'connections' ) }
					initialOpen={ false }
				>
					<p>
						{ __(
							'This section controls the order in which the selected entries will be displayed.',
							'connections'
						) }
					</p>

					<SelectControl
						label={ __( 'Order By', 'connections' ) }
						value={ orderBy }
						options={ [
							{
								label: __( 'Default', 'connections' ),
								value: 'default',
							},
							{
								label: __( 'First Name', 'connections' ),
								value: 'first_name',
							},
							{
								label: __( 'Last Name', 'connections' ),
								value: 'last_name',
							},
							{
								label: __( 'Title', 'connections' ),
								value: 'title',
							},
							{
								label: __( 'Organization', 'connections' ),
								value: 'organization',
							},
							{
								label: __( 'Department', 'connections' ),
								value: 'department',
							},
							{
								label: __( 'City', 'connections' ),
								value: 'city',
							},
							{
								label: __( 'State', 'connections' ),
								value: 'state',
							},
							{
								label: __( 'Zipcode', 'connections' ),
								value: 'zipcode',
							},
							{
								label: __( 'Country', 'connections' ),
								value: 'country',
							},
							{
								label: __( 'Date: Entry Added', 'connections' ),
								value: 'date_added',
							},
							{
								label: __(
									'Date: Entry Last Modified',
									'connections'
								),
								value: 'date_modified',
							},
							...dateTypeSelectOptions,
						] }
						onChange={ ( value ) =>
							setAttributes( { orderBy: value } )
						}
						disabled={ !! orderRandom }
					/>

					<SelectControl
						label={ __( 'Order', 'connections' ) }
						value={ order }
						options={ [
							{
								label: __( 'Ascending', 'connections' ),
								value: 'asc',
							},
							{
								label: __( 'Descending', 'connections' ),
								value: 'desc',
							},
							{
								label: __( 'Random', 'connections' ),
								value: 'random',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( {
								order: value,
								orderBy:
									'random' === value ? 'default' : orderBy,
								orderRandom: 'random' === value,
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorAdvancedControls>
				<p>
					{ __(
						'This section controls advanced options which effect the directory features and functions.',
						'connections'
					) }
				</p>

				<ToggleControl
					label={ __( 'Parse query?', 'connections' ) }
					help={ __(
						'Permit the Directory block instance to parse queries in order to affect the displayed results. Example, allowing keyword searches. The default is to allow query parsing.',
						'connections'
					) }
					checked={ !! parseQuery }
					onChange={ () =>
						setAttributes( { parseQuery: ! parseQuery } )
					}
				/>

				<PageSelect
					// postType={'post'}
					label={ __( 'Directory Home Page', 'connections' ) }
					noOptionLabel={ __( 'Current Page', 'connections' ) }
					value={ homePage }
					onChange={ ( value ) =>
						setAttributes( { homePage: value } )
					}
					disabled={ !! forceHome }
				/>

				<ToggleControl
					label={ __(
						'Force directory permalinks to resolve to the Global Directory Homes page?',
						'connections'
					) }
					checked={ !! forceHome }
					onChange={ () =>
						setAttributes( {
							forceHome: ! forceHome,
							homePage: '',
						} )
					}
				/>

				<TextControl
					label={ __( 'Additional Options', 'connections' ) }
					value={ advancedBlockOptions }
					onChange={ ( newValue ) => {
						setAttributes( {
							advancedBlockOptions: newValue,
						} );
					} }
				/>
			</InspectorAdvancedControls>
			<ServerSideRender
				attributes={ attributes }
				block="connections-directory/shortcode-connections"
			/>
		</Fragment>
	);
};
export default Edit;
