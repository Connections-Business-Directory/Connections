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
class Related extends Content_Block {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related';

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

		$related = Entry_Helper::relatedTo(
			$entry,
			array(
				'relation' => $this->get( 'relation' ),
			)
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
			"Connections_Directory/Content_Block/Entry/Related/{$this->shortName}/Attributes",
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
			"Connections_Directory/Content_Block/Entry/Related/{$this->shortName}/Before",
			$settings,
			$related,
			$template
		);

		echo PHP_EOL . '<div class="' . _escape::classNames( $classNames ) . '" id="' . _escape::id( 'slick-slider-content-block-' . self::ID . '-' . strtolower( $this->shortName ) ) . '" data-slick-slider-settings="' . _escape::json( $settings ) . '">' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// HTML is escaped in template.
		echo $this->renderTemplate( $template, $related, $carousel ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div><!--.slick-slider-section-->' . PHP_EOL;

		do_action(
			"Connections_Directory/Content_Block/Entry/Related/{$this->shortName}/After",
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
			"Connections_Directory/Content_Block/Entry/Related/{$this->shortName}/Template/{$template->getSlug()}/Attributes",
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
