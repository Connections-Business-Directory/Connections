/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Block dependencies
 */
import edit from './edit';

/**
 * Register Block
 */
export default registerBlockType(
	'connections-directory/carousel',
	{
		title:       __( 'Carousel', 'connections' ),
		description: __( 'Display members of your team in a carousel.', 'connections' ),
		category:    'connections-directory',
		// icon:        giveLogo,
		keywords:    [
			'connections',
			__( 'carousel', 'connections' ),
			__( 'slider', 'connections' ),
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
			blockId:           {
				type:    'string',
				// default: '',
			},
			borderRadius:      {
				type:    'integer',
				default: 12,
			},
			borderWidth:       {
				type:    'integer',
				default: 1,
			},
			carousels:         {
				type:    'string',
				default:  '[]',
				source:  'meta',
				meta:    '_cbd_carousel_blocks'
			},
			displayDropShadow: {
				type:    'boolean',
				default: true,
			},
			excerptWordLimit:  {
				type:    'string',
				default: '10',
			},
			enablePermalink: {
				type: 'boolean',
				default: false,
			}
			,forceHome: {
				type: 'boolean',
				default: false,
			},
			homePage: {
				type: 'integer',
				default: 0,
			},
			imageBorderColor:  {
				default: '#BABABA',
			},
			imageBorderRadius: {
				type:    'integer',
				default: 0,
			},
			imageBorderWidth:  {
				type:    'integer',
				default: 0,
			},
			imageCropMode:     {
				type:    'string',
				default: '1',
			},
			imageShape:        {
				type:    'string',
				default: 'square',
			},
			imageType:         {
				type:    'string',
				default: 'photo',
			},
		},
		edit,
		save:        () => {
			// Server side rendering via shortcode.
			return null;
		},
	}
)
