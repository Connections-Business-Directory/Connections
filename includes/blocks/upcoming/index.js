/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import block from './block.json';
import edit from './edit';
import save from './save';

/**
 * Register Block
 */
export default registerBlockType( block.name, {
	title: block.title,
	description: block.description,
	category: block.category,
	// icon: giveLogo,
	keywords: block.keywords,
	supports: block.supports,
	attributes: block.attributes,
	edit,
	save,
} );
