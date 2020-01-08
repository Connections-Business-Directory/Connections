<?php
namespace Connections_Directory\Blocks;

use cnArray;
use cnEntry;
use cnTemplate as Template;

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
		register_meta(
			'post',
			'_cbd_carousel_blocks',
			array(
				'single'            => TRUE,
				'type'              => 'string',
				'auth_callback'     => function() {

					return current_user_can( 'edit_posts' );
				},
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'show_in_rest'      => array(
					'prepare_callback' => function( $value ) {

						// If value is empty, then return an empty JSON encoded array.
						if ( ! $value ) $value = wp_json_encode( array() );

						return $value;
						//return wp_json_encode( $value );
					},
				),
			)
		);

		/*
		 * Use the `wp_print_scripts` so the block styles are output after enqueued CSS is output
		 * but before the enqueued javascript is output.
		 */
		add_action( 'wp_print_scripts', array( __CLASS__, 'printStyle' ), 1 );

		//register_meta(
		//	'post',
		//	'_blocks',
		//	array(
		//		'single'        => FALSE,
		//		'type'          => 'object',
		//		'auth_callback' => function() {
		//
		//			return current_user_can( 'edit_posts' );
		//		},
		//		'show_in_rest'  => array(
		//			'schema' => array(
		//				'type'       => 'object',
		//				'properties' => array(
		//					'blockId'    => array(
		//						'type' => 'string',
		//					),
		//					'categories' => array(
		//						'type'  => 'array',
		//						'items' => array(
		//							'type' => 'integer',
		//						),
		//					),
		//					'listType'   => array(
		//						'type' => 'string',
		//					),
		//				),
		//			),
		//		),
		//	)
		//);

	}

	/**
	 * Callback for the `sanitize_callback` property when registering the `_cbd_carousel_blocks` custom post meta.
	 *
	 * @since 9.4
	 *
	 * @param string $meta_value
	 * @param string $meta_key
	 * @param $meta_type
	 *
	 * @return false|string
	 */
	public static function sanitize( $meta_value, $meta_key, $meta_type ) {

		$untrusted = json_decode( stripslashes( $meta_value ), TRUE );
		$sanitized = array();

		if ( ! is_array( $untrusted ) ) {

			return wp_json_encode( $sanitized );
		}

		/**
		 * @var array $block
		 */
		foreach ( $untrusted as $block ) {

			$carousel = array();

			/*
			 * If blockID does not exist, skip.
			 */
			if ( ! array_key_exists( 'blockId', $block ) || ! is_string( $block['blockId'] ) ) {

				continue;
			}

			/*
			 * Sanitize blockId.
			 */
			$carousel['blockId'] = sanitize_key( $block['blockId'] );

			/*
			 * Sanitize listType.
			 */
			if ( array_key_exists( 'listType', $block ) &&
			     in_array( $block['listType'], array( 'family', 'individual', 'organization' ) )
			) {

				$carousel['listType'] = sanitize_key( $block['listType'] );
			}

			/*
			 * Sanitize categories.
			 */
			if ( array_key_exists( 'categories', $block ) && is_array( $block['categories'] ) ) {

				$carousel['categories'] = array_map( 'absint', $block['categories'] );
			}

			/*
			 * Sanitize categoriesIn.
			 */
			if ( array_key_exists( 'categoriesIn', $block ) ) {

				$carousel['categoriesIn'] = rest_sanitize_boolean( $block['categoriesIn'] );
			}

			/*
			 * Sanitize categories to exclude.
			 */
			if ( array_key_exists( 'categoriesExclude', $block ) && is_array( $block['categoriesExclude'] ) ) {

				$carousel['categoriesExclude'] = array_map( 'absint', $block['categoriesExclude'] );
			}

			/*
			 * Sanitize number of slide limit.
			 */
			if ( array_key_exists( 'limit', $block ) ) {

				$carousel['limit'] = absint( $block['limit'] );
			}

			/*
			 * Sanitize number of slides per frame.
			 */
			if ( array_key_exists( 'slidesToShow', $block ) ) {

				$carousel['slidesToShow'] = absint( $block['slidesToShow'] );
			}

			/*
			 * Sanitize number of slides to scroll per frame.
			 */
			if ( array_key_exists( 'slidesToScroll', $block ) ) {

				$carousel['slidesToScroll'] = absint( $block['slidesToScroll'] );
			}

			/*
			 * Sanitize autoplay.
			 */
			if ( array_key_exists( 'autoplay', $block ) ) {

				$carousel['autoplay'] = rest_sanitize_boolean( $block['autoplay'] );
			}

			/*
			 * Sanitize the autoplay speed.
			 */
			if ( array_key_exists( 'autoplaySpeed', $block ) ) {

				$carousel['autoplaySpeed'] = absint( $block['autoplaySpeed'] );
			}

			/*
			 * Sanitize the autoplay slide/transition speed.
			 */
			if ( array_key_exists( 'speed', $block ) ) {

				$carousel['speed'] = absint( $block['speed'] );
			}

			/*
			 * Sanitize pause.
			 */
			if ( array_key_exists( 'pause', $block ) ) {

				$carousel['pause'] = rest_sanitize_boolean( $block['pause'] );
			}

			/*
			 * Sanitize infinite loop.
			 */
			if ( array_key_exists( 'infinite', $block ) ) {

				$carousel['infinite'] = rest_sanitize_boolean( $block['infinite'] );
			}

			/*
			 * Sanitize arrows.
			 */
			if ( array_key_exists( 'arrows', $block ) ) {

				$carousel['arrows'] = rest_sanitize_boolean( $block['arrows'] );
			}

			/*
			 * Sanitize dots.
			 */
			if ( array_key_exists( 'dots', $block ) ) {

				$carousel['dots'] = rest_sanitize_boolean( $block['dots'] );
			}

			/*
			 * Sanitize slider arrow and dots color.
			 */
			if ( array_key_exists( 'arrowDotsColor', $block ) ) {

				$carousel['arrowDotsColor'] = \cnSanitize::hexColor( $block['arrowDotsColor'] );
			}

			/*
			 * Sanitize slider background color.
			 */
			if ( array_key_exists( 'backgroundColor', $block ) ) {

				$carousel['backgroundColor'] = \cnSanitize::hexColor( $block['backgroundColor'] );
			}

			/*
			 * Sanitize slider text color.
			 */
			if ( array_key_exists( 'color', $block ) ) {

				$carousel['color'] = \cnSanitize::hexColor( $block['color'] );
			}

			/*
			 * Sanitize display title.
			 */
			if ( array_key_exists( 'displayTitle', $block ) ) {

				$carousel['displayTitle'] = rest_sanitize_boolean( $block['displayTitle'] );
			}

			/*
			 * Sanitize display excerpt.
			 */
			if ( array_key_exists( 'displayExcerpt', $block ) ) {

				$carousel['displayExcerpt'] = rest_sanitize_boolean( $block['displayExcerpt'] );
			}

			/*
			 * Sanitize display phone.
			 */
			if ( array_key_exists( 'displayPhone', $block ) ) {

				$carousel['displayPhone'] = rest_sanitize_boolean( $block['displayPhone'] );
			}

			/*
			 * Sanitize display email.
			 */
			if ( array_key_exists( 'displayEmail', $block ) ) {

				$carousel['displayEmail'] = rest_sanitize_boolean( $block['displayEmail'] );
			}

			/*
			 * Sanitize display social.
			 */
			if ( array_key_exists( 'displaySocial', $block ) ) {

				$carousel['displaySocial'] = rest_sanitize_boolean( $block['displaySocial'] );
			}

			/*
			 * Sanitize display drop shadow.
			 */
			if ( array_key_exists( 'displayDropShadow', $block ) ) {

				$carousel['displayDropShadow'] = rest_sanitize_boolean( $block['displayDropShadow'] );
			}

			/*
			 * Sanitize border color.
			 */
			if ( array_key_exists( 'borderColor', $block ) ) {

				$carousel['borderColor'] = \cnSanitize::hexColor( $block['borderColor'] );
			}

			/*
			 * Sanitize the border radius.
			 */
			if ( array_key_exists( 'borderRadius', $block ) ) {

				$carousel['borderRadius'] = absint( $block['borderRadius'] );
			}

			/*
			 * Sanitize the border width.
			 */
			if ( array_key_exists( 'borderWidth', $block ) ) {

				$carousel['borderWidth'] = absint( $block['borderWidth'] );
			}

			array_push( $sanitized, $carousel );
		}

		return wp_json_encode( $sanitized );
	}

	/**
	 * Callback for the `wp_print_scripts` action.
	 *
	 * Print Blocks style tag in header.
	 *
	 * @since 9.4
	 */
	public static function printStyle() {

		$post = get_queried_object();

		if ( ! $post instanceof \WP_Post ) {

			return;
		}

		$meta = \cnFunction::decodeJSON( $post->_cbd_carousel_blocks, TRUE );

		if ( is_wp_error( $meta ) ) {

			return;
		}

		$styleTags = array();

		foreach ( $meta as $carousel ) {

			$styles = array();
			$id     = "#slick-slider-block-{$carousel['blockId']}";

			$arrowDotsColor  = cnArray::get( $carousel, 'arrowDotsColor', '#000000' );
			$backgroundColor = cnArray::get( $carousel, 'backgroundColor', '#FFFFFF' );
			$color           = cnArray::get( $carousel, 'color', '#000000' );

			$borderColor  = cnArray::get( $carousel, 'borderColor', '#000000' );
			$borderRadius = cnArray::get( $carousel, 'borderRadius', '3' );
			$borderWidth  = cnArray::get( $carousel, 'borderWidth', '2' );

			if ( 0 === strlen( $arrowDotsColor ) ) {

				$arrowDotsColor = '#FFFFFF';
			}

			if ( 0 === strlen( $backgroundColor ) ) {

				$backgroundColor = '#FFFFFF';
			}

			if ( 0 === strlen( $color ) ) {

				$color = '#000000';
			}

			if ( 0 === strlen( $borderColor ) ) {

				$borderColor = '#000000';
			}

			if ( 0 === strlen( $borderRadius ) ) {

				$borderRadius = '3';
			}

			if ( 0 === strlen( $borderWidth ) ) {

				$borderWidth = '2';
			}

			$arrowDotsStyle = array(
				"color: {$arrowDotsColor}",
			);

			$blockStyle = array(
				"background-color: {$backgroundColor}",
				"color: {$color}",
			);

			$slideStyle = array(
				"border-color: {$borderColor}",
				"border-radius: {$borderRadius}px",
				'border-style: solid',
				"border-width: {$borderWidth}px",
			);

			$nameStyle = array(
				"color: {$color}",
			);

			$styles[] = $id . ' .slick-arrow.slick-next:before { ' . implode( '; ', $arrowDotsStyle ) . ' }';
			$styles[] = $id . ' .slick-arrow.slick-prev:before { ' . implode( '; ', $arrowDotsStyle ) . ' }';
			$styles[] = $id . ' .slick-dots li button:before { ' . implode( '; ', $arrowDotsStyle ) . ' }';
			$styles[] = $id . ' { ' . implode( '; ', $blockStyle ) . ' }';
			$styles[] = $id . ' .slick-slide { ' . implode( '; ', $slideStyle ) . ' }';
			$styles[] = $id . ' h3 { ' . implode( '; ', $nameStyle ) . ' }';
			$styles[] = $id . ' a { ' . implode( '; ', $nameStyle ) . '; text-decoration: none; }';

			$styles = PHP_EOL . implode( PHP_EOL, $styles ) . PHP_EOL;

			array_push(
				$styleTags,
				"<style type=\"text/css\" media=\"all\" id=\"slick-slider-block-{$carousel['blockId']}\">{$styles}</style>"
			);
		}

		if ( ! empty( $styleTags ) ) {

			echo implode( PHP_EOL, $styleTags ) . PHP_EOL;
		}
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

		global $post;

		$template = \cnTemplateFactory::loadTemplate( array( 'template' => 'block-carousel' ) );

		if ( ! $template instanceof Template ) {

			return '<p>' . __( 'Template not found.', 'connections' ) . '</p>';
		}

		/**
		 * @link https://iandunn.name/2016/10/22/accessing-post-meta-and-more-via-post-meta_key/
		 */
		//$post = get_queried_object();
		$meta = \cnFunction::decodeJSON( $post->_cbd_carousel_blocks, TRUE );

		if ( is_wp_error( $meta ) ) {

			return '';
		}

		/**
		 * @link https://stackoverflow.com/a/6661561/5351316
		 */
		$index    = array_search( $attributes['blockId'], array_column( $meta, 'blockId' ) );
		$carousel = $meta[ $index ];

		$category = cnArray::get( $carousel, 'categoriesIn', FALSE ) ? 'category_in' : 'category';

		$queryArgs = array(
			'list_type'        => cnArray::get( $carousel, 'listType', NULL ),
			$category          => cnArray::get( $carousel, 'categories', NULL ),
			'exclude_category' => cnArray::get( $carousel, 'categoriesExclude', NULL ),
			'limit'            => cnArray::get( $carousel, 'limit', 10 ),
			'lock'             => TRUE,
		);

		$queryResults = Connections_Directory()->retrieve->entries( $queryArgs );

		if ( 0 >= count( $queryResults ) ) {

			return '<p>' . __( 'No directory entries found.', 'connections' ) . '</p>' . PHP_EOL;
		}

		$settings = array(
			'arrows'           => cnArray::get( $carousel, 'arrows', TRUE ),
			'autoplay'         => cnArray::get( $carousel, 'autoplay', FALSE ),
			'autoplaySpeed'    => cnArray::get( $carousel, 'autoplaySpeed', 3000 ),
			'dots'             => cnArray::get( $carousel, 'dots', TRUE ),
			//'cssEase'          => 'ease',
			'infinite'         => cnArray::get( $carousel, 'infinite', FALSE ),
			'lazyLoad'         => 'progressive',
			'pauseOnFocus'     => cnArray::get( $carousel, 'pause', TRUE ),
			'pauseOnHover'     => cnArray::get( $carousel, 'pause', TRUE ),
			'pauseOnDotsHover' => cnArray::get( $carousel, 'pause', TRUE ),
			'rows'             => 1,
			'speed'            => cnArray::get( $carousel, 'speed', 500 ),
			'slidesToShow'     => cnArray::get( $carousel, 'slidesToShow', 1 ),
			'slidesToScroll'   => cnArray::get( $carousel, 'slidesToScroll', 1 ),
		);

		$settingsJSON = htmlspecialchars( wp_json_encode( $settings ), ENT_QUOTES, 'UTF-8' );

		$classNames = array( 'cn-list', 'slick-slider-block' );

		if ( cnArray::get( $carousel, 'arrows', TRUE ) ) array_push( $classNames, 'slick-slider-has-arrows' );
		if ( cnArray::get( $carousel, 'dots', TRUE ) ) array_push( $classNames, 'slick-slider-has-dots' );

		if ( cnArray::get( $carousel, 'displayDropShadow', TRUE ) ) array_push( $classNames, 'slick-slider-has-shadow' );

		array_push( $classNames, "slick-slider-slides-{$settings['slidesToShow']}" );

		$html = '';
		$html .= PHP_EOL . '<div class="' . implode( ' ', $classNames ) . '" id="slick-slider-block-' . $attributes['blockId'] . '" data-slick-slider-settings="' . $settingsJSON . '">' . PHP_EOL;
		$html .= self::renderTemplate( $template, $queryResults, $carousel );
		$html .= '</div><!--.slick-slider-section-->' . PHP_EOL;

		return $html;
	}

	/**
	 * @since 8.4
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
			"Connections_Directory/Block/Carousel/Render/Template/{$template->getSlug()}/Before",
			$attributes,
			$items,
			$template
		);

		foreach ( $items as $data ) {

			$entry = new \cnOutput( $data );

			do_action( 'cn_template-' . $template->getSlug(), $entry, $template, $attributes );
		}

		do_action(
			"Connections_Directory/Block/Carousel/Render/Template/{$template->getSlug()}/After",
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
