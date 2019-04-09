<?php

namespace Connections_Directory\Blocks;

use cnTemplate as Template;

/**
 * Class Team
 *
 * Icon Resource:
 * https://www.fiverr.com/graphicszoone/do-unique-creative-and-professional-stationary-design
 *
 * Layout Switching
 * @links https://wpstackable.com/blog/introducing-stackable-premium/
 *
 * Design Ideas
 * @link https://powerpackelements.com/demo/team-member/
 * @link http://widgetkit.themesgrove.com/team-demo/
 * @link https://www.livemeshthemes.com/elementor-addons/team-member-profiles/
 * @link https://www.wpsuperstars.net/team-member-plugins-for-wordpress/
 * @link https://demo.awsm.in/team/wp-demo/
 *
 * @package Connections_Directory\Blocks
 * @since   8.31
 */
class Team {

	/**
	 * Callback for the `init` action.
	 *
	 * @since 8.39
	 */
	public static function register() {

		register_block_type(
			'connections-directory/team',
			array(
				// When displaying the block using ServerSideRender the attributes need to be defined
				// otherwise the REST API will reject the block request with a server response code 400 Bad Request
				// and display the "Error loading block: Invalid parameter(s): attributes" message.
				'attributes'      => array(
					'advancedBlockOptions' => array(
						'type'    => 'string',
						'default' => '',
					),
					'categories'           => array(
						'type'    => 'string',
						'default' => '[]',
					),
					'categoriesExclude'    => array(
						'type'    => 'string',
						'default' => '[]',
					),
					'categoriesIn'         => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'columns'              => array(
						'type'    => 'integer',
						'default' => 3,
					),
					'borderColor'          => array(
						'type'    => 'string',
						'default' => '#BABABA',
					),
					'borderRadius'         => array(
						'type'    => 'integer',
						'default' => 12,
					),
					'borderWidth'          => array(
						'type'    => 'integer',
						'default' => 1,
					),
					'displayEmail'         => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'displayDropShadow'    => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'displayExcerpt'       => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'displayPhone'         => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'displaySocial'        => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'displayTitle'         => array(
						'type'    => 'boolean',
						'default' => TRUE,
					),
					'gutterWidth'          => array(
						'type'    => 'integer',
						'default' => 25,
					),
					'excerptWordLimit'     => array(
						'type'    => 'integer',
						'default' => 10,
					),
					'imageBorderColor'     => array(
						'type'    => 'string',
						'default' => '#BABABA',
					),
					'imageBorderRadius'    => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'imageBorderWidth'     => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'imageCropMode'        => array(
						'type'    => 'string',
						'default' => '1',
					),
					'imageShape'           => array(
						'type'    => 'string',
						'default' => 'square',
					),
					'isEditorPreview'      => array(
						'type'    => 'boolean',
						'default' => FALSE,
					),
					'imageType'            => array(
						'type'    => 'string',
						'default' => 'photo',
					),
					'layout'               => array(
						'type'    => 'string',
						'default' => 'grid',
					),
					'listType'             => array(
						'type'    => 'string',
						'default' => 'all',
					),
					//'rows'              => array(
					//	'type'    => 'int',
					//	'default' => 1,
					//),
					'position'             => array(
						'type'    => 'string',
						'default' => 'left',
					),
					'style'                => array(
						'type'    => 'string',
						'default' => 'clean',
					),
					'variation'            => array(
						'type'    => 'string',
						'default' => 'card',
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
	 * Create the team container class names from supplied attributes.
	 *
	 * @since 8.39
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	private static function createClasses( $attributes ) {

		$classes   = array( 'cn-team-container' );
		$classes[] = "cn-{$attributes['layout']}";

		if ( 'grid' === $attributes['layout'] ) {

			$classes[] = "cn-columns-{$attributes['columns']}";
		}

		if ( 'grid' === $attributes['layout'] ) {

			$classes[] = "cn-{$attributes['variation']}";
			$classes[] = "cn-{$attributes['style']}";
		}

		//$classes[] = "cn-image-shape-{$attributes['imageShape']}";

		if ( $attributes['displayDropShadow'] ) {

			$classes[] = 'cn-box-shadow';
		}

		array_walk( $classes, 'sanitize_html_class' );

		return implode( ' ', $classes );
	}

	/**
	 * @since 8.39
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	private static function createID( $attributes ) {

		return 'cn-team-container-' . hash( 'crc32', json_encode( $attributes ) );
	}

	/**
	 * Callback for the `render_callback` block parameter.
	 *
	 * @since 8.39
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function render( $attributes ) {

		$template = self::getTemplate( $attributes );

		if ( ! $template instanceof Template ) {

			return '<p>' . __( 'Template not found.', 'connections' ) . '</p>';
		}

		$entryTypes = \cnOptions::getEntryTypeOptions();

		if ( ! array_key_exists( $attributes['listType'], $entryTypes ) ) {

			$attributes['listType'] = NULL;
		}

		$categories = \cnFunction::decodeJSON( $attributes['categories'] );

		if ( is_wp_error( $categories ) ) {

			$attributes['categories'] = NULL;

		} else {

			$attributes['categories'] = $categories;
		}

		$category = $attributes['categoriesIn'] ? 'category_in' : 'category';

		$excludeCategories = \cnFunction::decodeJSON( $attributes['categoriesExclude'] );

		if ( is_wp_error( $excludeCategories ) ) {

			$attributes['categoriesExclude'] = NULL;

		} else {

			$attributes['categoriesExclude'] = $excludeCategories;
		}

		$options = array(
			'list_type'         => $attributes['listType'],
			$category           => $attributes['categories'],
			'exclude_category'  => $attributes['categoriesExclude'],
		);

		$other = shortcode_parse_atts( trim( $attributes['advancedBlockOptions'] ) );

		if ( is_array( $other ) && ! empty( $other ) ) {

			$options = array_merge( $other, $options );
		}

		// Limit the number of entries displayed to 10, only in editor preview.
		if ( $attributes['isEditorPreview'] ) {

			$options['limit'] = 50;
		}

		$results = Connections_Directory()->retrieve->entries( $options );

		if ( 0 >= count( $results ) ) {

			return '<p>' . __( 'No entries found.', 'connections' ) . '</p>';
		}

		$id = self::createID( $attributes );

		return sprintf(
			'%1$s<div class="%2$s" id="%3$s">%4$s</div>',
			self::renderStyle( $template, $attributes, $id ),
			self::createClasses( $attributes ),
			$id,
			self::renderTemplate( $template, $results, $attributes )
		);
	}

	/**
	 * @since 8.39
	 *
	 * @param array $atts {
	 *
	 * @type string $layout
	 * @type string $variation
	 * @type string $style
	 * }
	 *
	 * @return \cnTemplate|FALSE
	 */
	private static function getTemplate( $atts ) {

		$defaults = array(
			'layout'    => 'grid',
			'variation' => 'card',
			'style'     => 'clean',
			'position'  => 'left',
		);

		$atts = \cnSanitize::args( $atts, $defaults );

		switch ( $atts['layout'] ) {

			case 'grid':

				$slug = "block-team-{$atts['layout']}-{$atts['variation']}-{$atts['style']}";
				break;

			case 'list':

				$slug = "block-team-{$atts['layout']}";
				break;

			case 'table':

				$slug = "block-team-{$atts['layout']}";
				break;
		}

		return \cnTemplateFactory::loadTemplate( array( 'template' => $slug ) );
	}

	/**
	 * @since 8.39
	 *
	 * @param Template $template
	 * @param array    $attributes
	 * @param string   $id
	 *
	 * @return string
	 */
	private static function renderStyle( $template, $attributes, $id = '' ) {

		$style = '';

		if ( method_exists( $template->getSelf(), 'inlineCSS' ) ) {

			$style = $template->getSelf()->inlineCSS( $attributes, $id );
		}

		return $style;
	}

	/**
	 * @since 8.39
	 *
	 * @param Template $template
	 * @param array    $items
	 * @param array    $attributes
	 *
	 * @return string
	 */
	private static function renderTemplate( $template, $items, $attributes ) {

		ob_start();

		do_action(
			"Connections_Directory/Block/Team/Render/Template/{$template->getSlug()}/Before",
			$attributes,
			$items,
			$template
		);

		foreach ( $items as $data ) {

			$entry = new \cnOutput( $data );

			do_action( 'cn_template-' . $template->getSlug(), $entry, $template, $attributes );
		}

		do_action(
			"Connections_Directory/Block/Team/Render/Template/{$template->getSlug()}/After",
			$attributes,
			$items,
			$template
		);

		$html = ob_get_clean();

		if ( FALSE === $html ) {

			$html = '<p>' . __( 'Error rendering template.', 'connections' ) . '</p>';
		}

		return $html;
	}
}
