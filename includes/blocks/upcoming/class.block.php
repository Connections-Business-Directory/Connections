<?php

namespace Connections_Directory\Blocks;

use Connections_Directory\Shortcode\Upcoming_List;
use Connections_Directory\Utility\_;

/**
 * Class Directory
 *
 * @package Connections_Directory\Blocks
 * @since   8.31
 */
class Upcoming {

	/**
	 * Callback for the `init` action.
	 *
	 * Register the block.
	 *
	 * @since 8.32
	 */
	public static function register() {

		/**
		 * In WordPress >= 5.8 the preferred method to register blocks is the block.json file.
		 *
		 * NOTE: When the minimum supported version of WP is 5.8. Convert block to API version 2.
		 *       The `block.json` file will have to be imported into the javascript and passed to
		 *       the `registerBlockType()` function.
		 *
		 *       @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#javascript-client-side
		 *
		 * @link https://make.wordpress.org/core/2021/06/23/block-api-enhancements-in-wordpress-5-8/
		 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/
		 * @see  \WP_Block_Type::__construct()
		 */
		if ( _::isWPVersion( '5.8' ) ) {

			register_block_type(
				__DIR__,
				array(
					'render_callback' => array( __CLASS__, 'render' ),
				)
			);

			return;
		}

		register_block_type(
			'connections-directory/shortcode-upcoming',
			array(
				// When displaying the block using ServerSideRender the attributes need to be defined
				// otherwise the REST API will reject the block request with a server response code 400 Bad Request
				// and display the "Error loading block: Invalid parameter(s): attributes" message.
				'attributes'      => array(
					'advancedBlockOptions' => array(
						'type'    => 'string',
						'default' => '',
					),
					'displayLastName'      => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'dateFormat'           => array(
						'type'    => 'string',
						'default' => 'F jS',
					),
					'days'                 => array(
						'type'    => 'integer',
						'default' => 30,
					),
					'heading'              => array(
						'type'    => 'string',
						'default' => '',
					),
					'includeToday'         => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'isEditorPreview'      => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'listType'             => array(
						'type'    => 'string',
						'default' => 'birthday',
					),
					'noResults'            => array(
						'type'    => 'string',
						'default' => 'No results.',
					),
					'template'             => array(
						'type'    => 'string',
						'default' => 'anniversary-light',
					),
					'yearFormat' => array(
						'type'    => 'string',
						'default' => '%y ' . __( 'Year(s)', 'connections' ),
					),
					'yearType' => array(
						'type'    => 'string',
						'default' => 'upcoming',
					),
				),
				// Not needed since script is enqueued in Connections_Directory\Blocks\enqueueEditorAssets()
				// 'editor_script'   => '', // Registered script handle. Enqueued only on the editor page.
				// Not needed since styles are enqueued in Connections_Directory\Blocks\enqueueEditorAssets()
				// 'editor_style'    => '', // Registered CSS handle. Enqueued only on the editor page.
				// 'script'          => '', // Registered script handle. Global, enqueued on the editor page and frontend.
				// 'style'           => '', // Registered CSS handle. Global, enqueued on the editor page and frontend.
				// The callback function used to render the block.
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Callback for the `render_callback` block parameter.
	 *
	 * @since 8.31
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function render( $attributes ) {

		// error_log( '$atts ' .  json_encode( $attributes, 128 ) );

		$options = array(
			'list_type'     => $attributes['listType'],
			'days'          => $attributes['days'],
			'include_today' => $attributes['includeToday'],
			'date_format'   => $attributes['dateFormat'],
			'show_lastname' => $attributes['displayLastName'],
			'show_title'    => false,
			'list_title'    => $attributes['heading'],
			'no_results'    => $attributes['noResults'],
			'template'      => $attributes['template'],
			'year_format'   => $attributes['yearFormat'],
			'year_type'     => $attributes['yearType'],
		);

		$other = shortcode_parse_atts( trim( $attributes['advancedBlockOptions'] ) );

		if ( is_array( $other ) && ! empty( $other ) ) {

			$options = array_merge( $other, $options );
		}

		if ( 0 < strlen( $options['list_title'] ) ) {

			$options['show_title'] = true;
			$options['list_title'] = str_replace( '%d', absint( $options['days'] ), $options['list_title'] );
		}

		// error_log( '$options ' .  json_encode( $options, 128 ) );

		return Upcoming_List::instance( $options )->getHTML();
	}
}
