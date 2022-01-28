<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnArray;
use cnEntry;
use cnEntry_HTML;
use cnTemplate as Template;
use cnTemplateFactory;
use Connections_Directory\Content_Block;
use Connections_Directory\Entry\Functions as Entry_Helper;
use Connections_Directory\Utility\_escape;

/**
 * Class Related
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Nearby extends Content_Block {

	/**
	 * The Content Block ID.
	 *
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-nearby';

	/**
	 * Nearby constructor.
	 *
	 * @since 9.9
	 *
	 * @param string $id The Content Block ID.
	 */
	public function __construct( $id ) {

		$atts = array(
			'context'             => 'single',
			'name'                => __( 'Entries Nearby', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Entries Nearby', 'connections' ),
			'script_handle'       => 'Connections_Directory/Block/Carousel/Script',
			'style_handle'        => 'Connections_Directory/Block/Carousel/Style',
		);

		parent::__construct( $id, $atts );

		// $this->setProperties( $this->properties );
	}

	/**
	 * Renders the Related Entries Content Block.
	 *
	 * @since 9.8
	 */
	public function content() {

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$template = cnTemplateFactory::loadTemplate( array( 'template' => 'content-block-related-carousel' ) );

		if ( ! $template instanceof Template ) {

			return;
		}

		$related = Entry_Helper::nearBy(
			$entry,
			array()
		);

		if ( 0 >= count( $related ) ) {

			return;
		}

		$carousel = array();

		$settings = array(
			'arrows'           => cnArray::get( $carousel, 'arrows', true ),
			'autoplay'         => cnArray::get( $carousel, 'autoplay', false ),
			'autoplaySpeed'    => cnArray::get( $carousel, 'autoplaySpeed', 3000 ),
			'dots'             => cnArray::get( $carousel, 'dots', true ),
			// 'cssEase'          => 'ease',
			'infinite'         => cnArray::get( $carousel, 'infinite', false ),
			'lazyLoad'         => false,
			'pauseOnFocus'     => cnArray::get( $carousel, 'pause', true ),
			'pauseOnHover'     => cnArray::get( $carousel, 'pause', true ),
			'pauseOnDotsHover' => cnArray::get( $carousel, 'pause', true ),
			'rows'             => 1,
			'speed'            => cnArray::get( $carousel, 'speed', 500 ),
			'slidesToShow'     => cnArray::get( $carousel, 'slidesToShow', 2 ),
			'slidesToScroll'   => cnArray::get( $carousel, 'slidesToScroll', 2 ),
		);

		$settings = apply_filters(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Attributes",
			$settings
		);

		// $settingsJSON = htmlspecialchars( wp_json_encode( $settings ), ENT_QUOTES, 'UTF-8' );

		$classNames = array( 'cn-list', 'slick-slider-block', 'slick-slider-content-block' );

		if ( cnArray::get( $carousel, 'arrows', true ) ) {
			array_push( $classNames, 'slick-slider-has-arrows' );
		}

		if ( cnArray::get( $carousel, 'dots', true ) ) {
			array_push( $classNames, 'slick-slider-has-dots' );
		}

		if ( cnArray::get( $carousel, 'displayDropShadow', false ) ) {
			array_push( $classNames, 'slick-slider-has-shadow' );
		}

		array_push( $classNames, "slick-slider-slides-{$settings['slidesToShow']}" );

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
			$settings,
			$related,
			$template
		);

		$id = 'slick-slider-content-block-entry-' . strtolower( $this->shortName );

		echo PHP_EOL . '<div class="' . _escape::classNames( $classNames ) . '" id="' . _escape::id( $id ) . '" data-slick-slider-settings="' . _escape::json( $settings ) . '">' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// Escaping is done in the template.
		echo $this->renderTemplate( $template, $related, $carousel ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div><!--.slick-slider-section-->' . PHP_EOL;

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
			$settings,
			$related,
			$template
		);
	}

	/**
	 * Render the template HTML.
	 *
	 * @since 9.8
	 *
	 * @param Template       $template   Instance of the Template object.
	 * @param cnEntry_HTML[] $related    Array of Entry objects.
	 * @param array          $attributes The carousel instance attributes.
	 *
	 * @return string
	 */
	private function renderTemplate( $template, $related, $attributes ) {

		$defaults = array(
			'displayTitle'     => true,
			'displayPhone'     => true,
			'displayEmail'     => true,
			'displaySocial'    => true,
			'displayExcerpt'   => false,
			'imageType'        => 'photo',
			'imageCropMode'    => 2,
			'imageWidth'       => 600,
			'imageHeight'      => 520,
			'imagePermalink'   => true,
			'imagePlaceholder' => true,
			'excerptWordLimit' => 25,
			'namePermalink'    => true,
		);

		$attributes = wp_parse_args( $attributes, $defaults );

		$attributes = apply_filters(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Template/{$template->getSlug()}/Attributes",
			$attributes
		);

		ob_start();

		foreach ( $related as $entry ) {

			do_action( 'cn_template-' . $template->getSlug(), $entry, $template, $attributes );
		}

		$html = ob_get_clean();

		if ( false === $html ) {

			$html = '<p>' . esc_html__( 'Error rendering template.', 'connections' ) . '</p>';
		}

		return $html;
	}
}
