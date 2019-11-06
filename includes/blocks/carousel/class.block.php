<?php
namespace Connections_Directory\Blocks;

/**
 * Class Carousel
 *
 * @package Connections_Directory\Blocks
 * @since   9.4
 */
class Carousel {

	/**
	 * Callback for the `init` action.
	 *
	 * @since 9.4
	 */
	public static function register() {

		register_block_type(
			'connections-directory/carousel',
			array(
				// When displaying the block using ServerSideRender the attributes need to be defined
				// otherwise the REST API will reject the block request with a server response code 400 Bad Request
				// and display the "Error loading block: Invalid parameter(s): attributes" message.
				'attributes'      => array(
				),
				// Not needed since script is enqueued in Connections_Directory\Blocks\enqueueEditorAssets()
				//'editor_script'   => '', // Registered script handle. Enqueued only on the editor page.
				// Not needed since styles are enqueued in Connections_Directory\Blocks\enqueueEditorAssets()
				//'editor_style'    => '', // Registered CSS handle. Enqueued only on the editor page.
				//'script'          => '', // Registered script handle. Global, enqueued on the editor page and frontend.
				//'style'           => '', // Registered CSS handle. Global, enqueued on the editor page and frontend.
				// The callback function used to render the block.
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);

		/**
		 * @todo At some point in the future the registering of the post meta data should be updated to be an object type.
		 * @link https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
		 * @link https://wordpress.stackexchange.com/a/341735
		 * @link https://github.com/WordPress/gutenberg/issues/5191#issuecomment-367915960
		 */
		//register_meta(
		//	'post',
		//	'_cbd_carousel_blocks',
		//	array(
		//		'single'            => TRUE,
		//		'type'              => 'string',
		//		'auth_callback'     => function() {
		//
		//			return current_user_can( 'edit_posts' );
		//		},
		//		'sanitize_callback' => function( $meta_value, $meta_key, $meta_type ) {
		//
		//			return wp_json_encode( json_decode( stripslashes( $meta_value ) ) );
		//		},
		//		'show_in_rest'      => array(
		//			'prepare_callback' => function( $value ) {
		//
		//				return wp_json_encode( $value );
		//			},
		//		),
		//	)
		//);

		//register_meta(
		//	'post',
		//	'_listType',
		//	array(
		//		'single'        => TRUE,
		//		'type'          => 'string',
		//		'auth_callback' => function() {
		//
		//			return current_user_can( 'edit_posts' );
		//		},
		//		'show_in_rest'  => array(
		//			'prepare_callback' => function( $value ) {
		//
		//				return $value;
		//				//return wp_json_encode( $value );
		//			},
		//		),
		//	)
		//);

		register_meta(
			'post',
			'_blocks',
			array(
				'single'        => FALSE,
				'type'          => 'object',
				'auth_callback' => function() {

					return current_user_can( 'edit_posts' );
				},
				'show_in_rest'  => array(
					'schema' => array(
						'type'       => 'object',
						'properties' => array(
							'blockId'    => array(
								'type' => 'string',
							),
							'categories' => array(
								'type'  => 'array',
								'items' => array(
									'type' => 'integer',
								),
							),
							'listType'   => array(
								'type' => 'string',
							),
						),
					),
				),
			)
		);

	}

	/**
	 * Callback for the `render_callback` block parameter.
	 *
	 * @since 9.4
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function render( $attributes ) {

		return '';
	}
}
