<?php
/**
 * The `[cn-entry]` shortcode.
 *
 * @since      9.5
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shortcode\Entry
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Shortcode;

use cnSettingsAPI;
use cnShortcode;
use cnTemplate as Template;
use cnTemplateFactory;
use cnTemplatePart;
use Connections_Directory\Utility\_format;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Entry
 *
 * @package Connections_Directory\Shortcode
 */
class Entry {

	/**
	 * The shortcode output HTML.
	 *
	 * @since 9.5
	 * @var string
	 */
	private $html = '';

	/**
	 * The shortcode tag.
	 *
	 * @since 9.12
	 * @since 9.15 Change from private to protected.
	 *
	 * @var string
	 */
	protected static $tag = 'cn-entry';

	/**
	 * @since 9.5
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 */
	public function __construct( $atts, $content, $tag ) {

		$template = cnTemplateFactory::loadTemplate( $atts );

		if ( false === $template ) {
			$this->html = cnTemplatePart::loadTemplateError( $atts );
			return;
		}

		/*
		 * This filter adds the current template paths to cnLocate so when template
		 * part file overrides are being searched for, it'll also search in template
		 * specific paths. This filter is then removed at the end of the shortcode.
		 */
		add_filter( 'cn_locate_file_paths', array( $template, 'templatePaths' ) );
		cnShortcode::addFilterRegistry( 'cn_locate_file_paths' );

		do_action( 'cn_template_include_once-' . $template->getSlug() );
		do_action( 'cn_template_enqueue_js-' . $template->getSlug() );

		$atts = $this->parseAtts( $atts, $template, $tag );

		$atts = apply_filters( 'cn_list_retrieve_atts', $atts );
		$atts = apply_filters( 'cn_list_retrieve_atts-' . $template->getSlug(), $atts );
		cnShortcode::addFilterRegistry( 'cn_list_retrieve_atts-' . $template->getSlug() );

		$results = Connections_Directory()->retrieve->entries( $atts );

		// Apply any registered filters to the results.
		if ( ! empty( $results ) ) {

			$results = apply_filters( 'cn_list_results', $results );
			$results = apply_filters( 'cn_list_results-' . $template->getSlug(), $results );
			cnShortcode::addFilterRegistry( 'cn_list_results-' . $template->getSlug() );

		} else {

			$this->html = '<p>' . esc_html__( 'Entry not found.', 'connections' ) . '</p>';
			return;
		}

		ob_start();

		// Prints the template's CSS file.
		// NOTE: This is primarily to support legacy templates which included a CSS
		// file which was not enqueued in the page header.
		do_action( 'cn_template_inline_css-' . $template->getSlug(), $atts );

		// The return to top anchor.
		do_action( 'cn_list_return_to_target', $atts );

		$this->html .= ob_get_clean();

		$this->html .= sprintf(
			'<div class="cn-list" id="cn-list" data-connections-version="%1$s-%2$s">',
			Connections_Directory()->options->getVersion(),
			Connections_Directory()->options->getDBVersion()
		);

		$this->html .= sprintf(
			'<div class="cn-template cn-%1$s" id="cn-%1$s" data-template-version="%2$s">',
			$template->getSlug(),
			$template->getVersion()
		);

		$this->html .= $this->renderTemplate( $template, $results, $atts );

		$this->html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-' . $template->getSlug() . ' -->' : '' ) . PHP_EOL;

		$this->html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list -->' : '' ) . PHP_EOL;

		// Clear any filters that have been added.
		// This allows support using the shortcode multiple times on the same page.
		cnShortcode::clearFilterRegistry();

		// @todo This should be run via a filter.
		$this->html = cnShortcode::removeEOL( $this->html );
	}

	/**
	 * Register the shortcode.
	 *
	 * @since 9.14
	 */
	public static function add() {

		add_shortcode( static::$tag, array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Callback for `add_shortcode()`.
	 *
	 * @since 9.5
	 *
	 * @see Entry::add()
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 *
	 * @return static
	 */
	public static function shortcode( $atts, $content, $tag ) {

		return new static( $atts, $content, $tag );
	}

	/**
	 * The shortcode defaults.
	 *
	 * @since 9.5
	 *
	 * @param Template $template Instance of Template.
	 *
	 * @return array
	 */
	private function getDefaults( $template ) {

		$defaults = array(
			'id'         => null,
			'template'   => null,
			'force_home' => false,
			'random'     => false,
			'home_id'    => in_the_loop() && is_page() ? get_the_ID() : cnSettingsAPI::get( 'connections', 'home_page', 'page_id' ),
		);

		$defaults = apply_filters( 'cn_list_atts_permitted', $defaults );
		$defaults = apply_filters( "cn_list_atts_permitted-{$template->getSlug()}", $defaults );
		cnShortcode::addFilterRegistry( 'cn_list_atts_permitted-' . $template->getSlug() );

		return $defaults;
	}

	/**
	 * Parse the user supplied atts.
	 *
	 * @since 9.5
	 *
	 * @param array    $atts     The shortcode arguments.
	 * @param Template $template The shortcode content.
	 * @param string   $tag      The shortcode tag.
	 *
	 * @return array
	 */
	public function parseAtts( $atts, $template, $tag ) {

		$defaults = $this->getDefaults( $template );
		$atts     = shortcode_atts( $defaults, $atts, $tag );

		$atts = apply_filters( 'cn_list_atts', $atts );
		$atts = apply_filters( "cn_list_atts-{$template->getSlug()}", $atts );
		cnShortcode::addFilterRegistry( 'cn_list_atts-' . $template->getSlug() );

		// Force some specific defaults.
		$atts['content']         = '';
		$atts['lock']            = true;
		$atts['show_alphaindex'] = false;
		$atts['show_alphahead']  = false;
		$atts['limit']           = 1;

		_format::toBoolean( $atts['force_home'] );
		_format::toBoolean( $atts['random'] );

		// If `id` is not numeric, set it to a string which will be evaluated to a `0` (zero) in `cnRetrieve::entries()` and return no results.
		if ( ! is_numeric( $atts['id'] ) ) {

			$atts['id'] = 'none';
		}

		if ( true === $atts['random'] ) {

			// If random is set, set `id` to `null`.
			$atts['id']       = null;
			$atts['order_by'] = 'id|RANDOM';
		}

		return $atts;
	}

	/**
	 * @since 9.5
	 *
	 * @param Template $template   An instance of Template.
	 * @param array    $items      An array of entry data objects.
	 * @param array    $attributes The shortcode arguments.
	 *
	 * @return string
	 */
	private function renderTemplate( $template, $items, $attributes ) {

		ob_start();

		do_action(
			"Connections_Directory/Render/Template/{$template->getSlug()}/Before",
			$attributes,
			$items,
			$template
		);

		//foreach ( $items as $data ) {
		//
		//	$entry = new \cnOutput( $data );
		//
		//	do_action( 'cn_template-' . $template->getSlug(), $entry, $template, $attributes );
		//}

		// Render the core result list header.
		//cnTemplatePart::header( $attributes, $items, $template );

		// Render the core result list body.
		//cnTemplatePart::body( $attributes, $items, $template );
		cnTemplatePart::cards( $attributes, $items, $template );

		// Render the core result list footer.
		//cnTemplatePart::footer( $attributes, $items, $template );

		do_action(
			"Connections_Directory/Render/Template/{$template->getSlug()}/After",
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

	/**
	 * Return the generated shortcode HTML.
	 *
	 * @since 9.5
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->html;
	}
}
