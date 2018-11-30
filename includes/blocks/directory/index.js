const { __, _n, _nx, _x } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
	      InspectorControls,
	      InspectorAdvancedControls,
      } = wp.editor;
const {
	      ServerSideRender,
	      PanelBody,
	      TextControl,
	      ToggleControl
      } = wp.components;

// Import CSS
import './styles/editor.scss';
import './styles/public.scss';

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
		attributes:  {
			advancedBlockOptions: {
				type:    'string',
				default: '',
			},
			characterIndex:       {
				type:    'boolean',
				default: true,
			},
			isEditorPreview:       {
				type:    'boolean',
				default: true,
			},
			repeatCharacterIndex: {
				type:    'boolean',
				default: false,
			},
			sectionHead:          {
				type:    'boolean',
				default: false,
			}
		},
		edit:        function( { attributes, setAttributes } ) {

			const {
				      advancedBlockOptions,
				      characterIndex,
				      repeatCharacterIndex,
				      sectionHead
			      } = attributes;

			return [
				<InspectorControls>
					<PanelBody title={__( 'Settings', 'connections' )}>

						<ToggleControl
							label={__( 'Display Character Index?', 'connections' )}
							checked={!!characterIndex}
							onChange={() => setAttributes( { characterIndex: !characterIndex } )}
						/>

						<ToggleControl
							label={__( 'Repeat Character Index at Beginning of Character Group?', 'connections' )}
							checked={!!repeatCharacterIndex}
							onChange={() => setAttributes( { repeatCharacterIndex: !repeatCharacterIndex } )}
						/>

						<ToggleControl
							label={__( 'Display Current Character Heading?', 'connections' )}
							checked={!!sectionHead}
							onChange={() => setAttributes( { sectionHead: !sectionHead } )}
						/>

					</PanelBody>
				</InspectorControls>,
				<InspectorAdvancedControls>
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
