<?php

namespace Connections_Directory\Blocks;

/**
 * Class Directory
 *
 * @package Connections_Directory\Blocks
 * @since   8.31
 */
class Directory {

	/**
	 * Callback for the `init` action.
	 *
	 * Register the test block.
	 *
	 * @since 8.31
	 */
	public static function register() {

		register_block_type(
			'connections-directory/shortcode-connections',
			array(
				// When displaying the block using ServerSideRender the attributes need to be defined
				// otherwise the REST API will reject the block request with a server response code 400 Bad Request
				// and display the "Error loading block: Invalid parameter(s): attributes" message.
				'attributes'      => array(
					'advancedBlockOptions' => array(
						'type'    => 'string',
						'default' => '',
					),
					'characterIndex'       => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'isEditorPreview'      => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'repeatCharacterIndex' => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'sectionHead'          => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
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

		//error_log( '$atts ' .  json_encode( $attributes, 128 ) );

		$options = array(
			//'limit'             => 3,
			'show_alphaindex'   => $attributes['characterIndex'],
			'repeat_alphaindex' => $attributes['repeatCharacterIndex'],
			'show_alphahead'    => $attributes['sectionHead'],
		);

		$other = shortcode_parse_atts( trim(  $attributes['advancedBlockOptions'] ) );

		if ( is_array( $other ) && ! empty( $other ) ) {

			$options = array_merge( $other, $options );
		}

		// Limit the number of entries displayed to 10, only in editor preview.
		if ( $attributes['isEditorPreview'] ) {

			$options['limit'] = 10;
		}

		//error_log( '$options ' .  json_encode( $options, 128 ) );

		$html = \cnShortcode_Connections::shortcode( $options );

		// Strip link URL/s, only in editor preview.
		if ( $attributes['isEditorPreview'] ) {

			$html = preg_replace( '/(href=.)[^\'|"]+/', '$1#', $html );
		}

		return $html;
	}
}
