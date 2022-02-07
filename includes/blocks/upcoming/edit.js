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
	ExternalLink,
	PanelBody,
	RadioControl,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import { RangeControl } from '@Connections-Directory/components';

const { dateTypes } = cbDir.blockSettings;

const Edit = ( { attributes, setAttributes } ) => {
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
		yearType,
	} = attributes;

	const dateTypeSelectOptions = [];

	for ( const property in dateTypes ) {
		dateTypeSelectOptions.push( {
			label: dateTypes[ property ],
			value: property,
		} );
	}

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'connections' ) }>
					<SelectControl
						label={ __( 'Type', 'connections' ) }
						value={ listType }
						options={ dateTypeSelectOptions }
						onChange={ ( value ) =>
							setAttributes( { listType: value } )
						}
					/>

					<SelectControl
						label={ __( 'Style', 'connections' ) }
						value={ template }
						options={ [
							{ label: 'Light', value: 'anniversary-light' },
							{ label: 'Dark', value: 'anniversary-dark' },
						] }
						onChange={ ( value ) =>
							setAttributes( { template: value } )
						}
					/>

					<TextControl
						label={ __( 'Heading', 'connections' ) }
						help={
							/* translators: Number of days from settings field. */
							__(
								'Type %d to insert the number of days in the heading.',
								'connections'
							)
						}
						placeholder={ __(
							'Type the heading here.',
							'connections'
						) }
						value={ heading }
						onChange={ ( value ) => {
							setAttributes( {
								heading: value,
							} );
						} }
					/>

					<ToggleControl
						label={ __( 'Display last name?', 'connections' ) }
						checked={ !! displayLastName }
						onChange={ () =>
							setAttributes( {
								displayLastName: ! displayLastName,
							} )
						}
					/>

					<RangeControl
						label={ __(
							'The number of days ahead to display.',
							'connections'
						) }
						help={ __(
							'To display date events for today only, slide the slider to 0 and enable the Include today option.',
							'connections'
						) }
						value={ days }
						onChange={ ( value ) =>
							setAttributes( { days: value } )
						}
						min={ 0 }
						max={ 90 }
						allowReset={ true }
						initialPosition={ 30 }
					/>

					<ToggleControl
						label={ __( 'Include today?', 'connections' ) }
						help={ __(
							'Whether or not to include the date events for today.',
							'connections'
						) }
						checked={ !! includeToday }
						onChange={ () =>
							setAttributes( {
								includeToday: ! includeToday,
							} )
						}
					/>

					<RadioControl
						label={ __( 'Year Display', 'connections' ) }
						// help={__( '', 'connections' )}
						selected={ yearType }
						options={ [
							{
								label: __( 'Original Year', 'connections' ),
								value: 'original',
							},
							{
								label: __( 'Upcoming Year', 'connections' ),
								value: 'upcoming',
							},
							{
								label: __( 'Years Since', 'connections' ),
								value: 'since',
							},
						] }
						onChange={ ( newValue ) => {
							setAttributes( {
								yearType: newValue,
							} );
						} }
					/>

					<TextControl
						label={ __( 'No Results Notice', 'connections' ) }
						help={ __(
							'This message is displayed when there are no upcoming event dates within the specified number of days.',
							'connections'
						) }
						placeholder={ __(
							'Type the no result message here.',
							'connections'
						) }
						value={ noResults }
						onChange={ ( newValue ) => {
							setAttributes( {
								noResults: newValue,
							} );
						} }
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorAdvancedControls>
				<TextControl
					label={ __( 'Date Format', 'connections' ) }
					help={
						<ExternalLink
							href="https://codex.wordpress.org/Formatting_Date_and_Time"
							target="_blank"
						>
							{ __(
								'Documentation on date and time formatting.',
								'connections'
							) }
						</ExternalLink>
					}
					value={ dateFormat }
					onChange={ ( newValue ) => {
						setAttributes( {
							dateFormat: newValue,
						} );
					} }
				/>

				<TextControl
					label={ __( 'Years Since Format', 'connections' ) }
					help={
						<ExternalLink
							href="https://www.php.net/manual/en/dateinterval.format.php"
							target="_blank"
						>
							{ __(
								'Documentation on date interval formatting.',
								'connections'
							) }
						</ExternalLink>
					}
					value={ yearFormat }
					onChange={ ( newValue ) => {
						setAttributes( {
							yearFormat: newValue,
						} );
					} }
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
				attributes={ { ...attributes, ...{ isEditorPreview: true } } }
				block="connections-directory/shortcode-upcoming"
			/>
		</Fragment>
	);
};
export default Edit;
