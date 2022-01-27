<?php

namespace Connections_Directory\Blocks;

use cnTemplate as Template;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_url;

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

		/**
		 * Scripts and styles need to be registered before the block is registered,
		 * so the scrip and style hooks are available when registering the block.
		 */
		self::registerStyles();

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
						'default' => false,
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
						'default' => true,
					),
					'displayDropShadow'    => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'displayExcerpt'       => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'displayPhone'         => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'displaySocial'        => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'displayTitle'         => array(
						'type'    => 'boolean',
						'default' => true,
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
						'default' => false,
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
				// 'editor_script'   => '', // Registered script handle. Enqueued only on the editor page.
				// Not needed since styles are enqueued in Connections_Directory\Blocks\enqueueEditorAssets()
				// 'editor_style'    => '', // Registered CSS handle. Enqueued only on the editor page.
				// 'script'          => '', // Registered script handle. Global, enqueued on the editor page and frontend.
				'style'           => 'Connections_Directory/Block/Team/Style', // Registered CSS handle. Global, enqueued on the editor page and frontend.
				// The callback function used to render the block.
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Register the styles.
	 *
	 * @since 10.4.11
	 */
	private static function registerStyles() {

		$path     = Connections_Directory()->pluginPath();
		$urlBase  = _url::makeProtocolRelative( Connections_Directory()->pluginURL() );
		$rtl      = is_rtl() ? '.rtl' : '';
		$filename = "style{$rtl}.css";

		wp_register_style(
			'Connections_Directory/Block/Team/Style',
			"{$urlBase}assets/dist/block/team/{$filename}",
			array(),
			filemtime( "{$path}assets/dist/block/team/{$filename}" )
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

		// $classes[] = "cn-image-shape-{$attributes['imageShape']}";

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

			return '<p>' . esc_html__( 'Template not found.', 'connections' ) . '</p>';
		}

		$entryTypes = \cnOptions::getEntryTypeOptions();

		if ( ! array_key_exists( $attributes['listType'], $entryTypes ) ) {

			$attributes['listType'] = null;
		}

		$categories = \cnFunction::decodeJSON( $attributes['categories'] );

		if ( is_wp_error( $categories ) ) {

			$attributes['categories'] = null;

		} else {

			$attributes['categories'] = $categories;
		}

		$category = $attributes['categoriesIn'] ? 'category_in' : 'category';

		$excludeCategories = \cnFunction::decodeJSON( $attributes['categoriesExclude'] );

		if ( is_wp_error( $excludeCategories ) ) {

			$attributes['categoriesExclude'] = null;

		} else {

			$attributes['categoriesExclude'] = $excludeCategories;
		}

		$options = array(
			'list_type'        => $attributes['listType'],
			$category          => $attributes['categories'],
			'exclude_category' => $attributes['categoriesExclude'],
			// Limit the number of entries displayed to 50ish (based on number of set columns), only in editor preview. max is 100ish.
			'limit'            => $attributes['isEditorPreview'] ? 50 - ( 50 % $attributes['columns'] ) : 100 - ( 100 % $attributes['columns'] ),
			// Allow the Additional Options field to override the `parse_request` parameter.
			'parse_request'    => false,
		);

		$other = shortcode_parse_atts( trim( $attributes['advancedBlockOptions'] ) );

		if ( is_array( $other ) && ! empty( $other ) ) {

			$options = array_replace( $options, $other );
		}

		$options['limit'] = filter_var(
			$options['limit'],
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => 100,
					'min_range' => 1,
					'max_range' => 100,
				),
			)
		);

		// $options['parse_request'] = false;

		$results = Connections_Directory()->retrieve->entries( $options );

		if ( 0 >= count( $results ) ) {

			return '<p>' . esc_html__( 'No entries found.', 'connections' ) . '</p>';
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

		if ( false === $html ) {

			$html = '<p>' . esc_html__( 'Error rendering template.', 'connections' ) . '</p>';
		}

		return $html;
	}
}
