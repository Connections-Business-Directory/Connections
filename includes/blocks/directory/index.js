/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import json from './block.json';
import edit from './edit';
import save from './save';

// Destructure the json file to get the name of the block.
const { name } = json;

/**
 * Register Block
 */
export default registerBlockType( name, {
	title: __( 'Directory', 'connections' ),
	description: __(
		'Display the Connections Business Directory.',
		'connections'
	),
	category: 'connections-directory',
	// icon:        giveLogo,
	keywords: [ 'connections', __( 'directory', 'connections' ) ],
	supports: {
		// Remove the support for the generated className.
		className: false,
		// Remove the support for the custom className.
		customClassName: false,
		// Remove the support for editing the block using the block HTML editor.
		html: false,
	},
	attributes: {
		// Valid attribute types are: string, boolean, object, null, array, integer, number
		// @see link https://github.com/WordPress/gutenberg/blob/master/packages/blocks/src/api/parser.js
		advancedBlockOptions: {
			type: 'string',
			default: '',
		},
		categories: {
			type: 'string',
			default: '[]',
		},
		characterIndex: {
			type: 'boolean',
			default: true,
		},
		city: {
			type: 'array',
			default: [],
		},
		county: {
			type: 'array',
			default: [],
		},
		country: {
			type: 'array',
			default: [],
		},
		department: {
			type: 'array',
			default: [],
		},
		district: {
			type: 'array',
			default: [],
		},
		excludeCategories: {
			type: 'string',
			default: '[]',
		},
		forceHome: {
			type: 'boolean',
			default: false,
		},
		fullName: {
			type: 'array',
			default: [],
		},
		homePage: {
			type: 'string',
			default: '',
		},
		inCategories: {
			type: 'boolean',
			default: false,
		},
		isEditorPreview: {
			type: 'boolean',
			default: true,
		},
		lastName: {
			type: 'array',
			default: [],
		},
		listType: {
			type: 'string',
			default: 'all',
		},
		order: {
			type: 'string',
			default: 'asc',
		},
		orderBy: {
			type: 'string',
			default: 'default',
		},
		orderRandom: {
			type: 'boolean',
			default: false,
		},
		organization: {
			type: 'array',
			default: [],
		},
		parseQuery: {
			type: 'boolean',
			default: true,
		},
		repeatCharacterIndex: {
			type: 'boolean',
			default: false,
		},
		sectionHead: {
			type: 'boolean',
			default: false,
		},
		state: {
			type: 'array',
			default: [],
		},
		template: {
			type: 'string',
			// default: templates.active
		},
		title: {
			type: 'array',
			default: [],
		},
		zipcode: {
			type: 'array',
			default: [],
		},
	},
	edit,
	save,
} );
