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
				'attributes' => array(
					'advancedBlockOptions' => array(
						'type'    => 'string',
						'default' => '',
					),
					'categories'           => array(
						'type'    => 'string',
						'default' => '[]',
					),
					'characterIndex'       => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'city'                 => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'county'               => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'country'              => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'department'           => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'district'             => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'excludeCategories'    => array(
						'type'    => 'string',
						'default' => '[]',
					),
					'forceHome'            => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'fullName'             => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'integer',
						),
					),
					'homePage'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'inCategories'         => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'isEditorPreview'      => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'lastName'             => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'listType'             => array(
						'type'    => 'string',
						'default' => 'all',
					),
					'order'                => array(
						'type'    => 'string',
						'default' => 'asc',
					),
					'orderBy'              => array(
						'type'    => 'string',
						'default' => 'default',
					),
					'orderRandom'          => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'organization'         => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'parseQuery'           => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'repeatCharacterIndex' => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'sectionHead'          => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'state'                => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'template'             => array(
						'type'    => 'string',
						'default' => Connections_Directory()->options->getActiveTemplate( 'all' ),
					),
					'title'                => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'zipcode'              => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
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

		$entryTypes = \cnOptions::getEntryTypeOptions();
		$dateTypes  = \cnOptions::getDateTypeOptions();

		if ( ! array_key_exists( $attributes['listType'], $entryTypes ) ) {

			$attributes['listType'] = NULL;
		}

		$categories = \cnFunction::decodeJSON( $attributes['categories'] );

		if ( is_wp_error( $categories ) ) {

			$attributes['categories'] = NULL;

		} else {

			$attributes['categories'] = $categories;
		}

		$category = $attributes['inCategories'] ? 'category_in' : 'category';

		$excludeCategories = \cnFunction::decodeJSON( $attributes['excludeCategories'] );

		if ( is_wp_error( $excludeCategories ) ) {

			$attributes['excludeCategories'] = NULL;

		} else {

			$attributes['excludeCategories'] = $excludeCategories;
		}

		$orderByFields = array(
			'id',
			'date_added',
			'date_modified',
			'first_name',
			'last_name',
			'title',
			'organization',
			'department',
			'city',
			'state',
			'zipcode',
			'country',
		);

		if ( $attributes['orderRandom'] ) {

			$attributes['orderBy'] = 'id';
		}

		if ( in_array( $attributes['orderBy'], $orderByFields ) ) {

			$orderBy = $attributes['orderBy'] . '|' . strtoupper( $attributes['order'] );

		} elseif ( array_key_exists( $attributes['orderBy'], $dateTypes ) ) {

			$orderBy = $attributes['orderBy'] . '|' . strtoupper( $attributes['order'] );

		} else {

			$orderBy = array(
				'sort_column' . '|' . strtoupper( $attributes['order'] ),
				'last_name',
				'first_name'
			);
		}

		$options = array(
			'show_alphaindex'   => $attributes['characterIndex'],
			'repeat_alphaindex' => $attributes['repeatCharacterIndex'],
			'show_alphahead'    => $attributes['sectionHead'],
			'template'          => $attributes['template'],
			'list_type'         => $attributes['listType'],
			$category           => $attributes['categories'],
			'exclude_category'  => $attributes['excludeCategories'],
			'id'                => $attributes['fullName'],
			'last_name'         => $attributes['lastName'],
			'title'             => $attributes['title'],
			'department'        => $attributes['department'],
			'organization'      => $attributes['organization'],
			'district'          => $attributes['district'],
			'county'            => $attributes['county'],
			'state'             => $attributes['state'],
			'city'              => $attributes['city'],
			'zip_code'          => $attributes['zipcode'],
			'country'           => $attributes['country'],
			'order_by'          => $orderBy,
			'lock'              => ! $attributes['parseQuery'],
			'force_home'        => $attributes['forceHome'],
		);

		if ( ! empty( $attributes['homePage'] ) ) {

			$options['home_id'] = $attributes['homePage'];
		}

		$other = shortcode_parse_atts( trim(  $attributes['advancedBlockOptions'] ) );

		if ( is_array( $other ) && ! empty( $other ) ) {

			$options = array_merge( $other, $options );
		}

		// Limit the number of entries displayed to 10, only in editor preview.
		if ( $attributes['isEditorPreview'] ) {

			$options['limit'] = 10;
		}

		error_log( '$options ' .  json_encode( $options, 128 ) );

		$html = \cnShortcode_Connections::shortcode( $options );

		// Strip link URL/s, only in editor preview.
		if ( $attributes['isEditorPreview'] ) {

			$html = preg_replace( '/(href=.)[^\'|"]+/', '$1#', $html );
		}

		return $html;
	}
}
