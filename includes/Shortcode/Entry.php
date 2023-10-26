<?php
/**
 * The `[cn-entry]` shortcode.
 *
 * @since      9.5
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections\Shortcode\Entry
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );
namespace Connections_Directory\Shortcode;

use cnSettingsAPI;
use cnShortcode;
use cnTemplate as Template;
use cnTemplateFactory;
use cnTemplatePart;
use Connections_Directory\Request;
use Connections_Directory\Shortcode;
use Connections_Directory\Template\Hook_Transient;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_format;

/**
 * Class Entry
 *
 * @package Connections_Directory\Shortcode
 */
final class Entry extends Shortcode {

	use Do_Shortcode;
	use Get_HTML;

	/**
	 * The shortcode tag.
	 *
	 * Shortcode support hyphens in the tag name. Bug was fixed:
	 *
	 * @link https://core.trac.wordpress.org/ticket/17657
	 *
	 * @since 9.12
	 * @since 9.15 Change from private to protected.
	 * @since 10.4.40 Change to constant.
	 *
	 * @var string
	 */
	const TAG = 'cn-entry';

	/**
	 * An instance of the cnTemplate or false.
	 *
	 * @since 10.4.40
	 *
	 * @var Template|false
	 */
	private $template;

	/**
	 * Register the shortcode.
	 *
	 * @since 9.14
	 */
	public static function add() {

		/*
		 * Do not register the shortcode when doing ajax requests.
		 * This is primarily implemented so the shortcodes are not run during Yoast SEO page score admin ajax requests.
		 * The page score can cause the ajax request to fail and/or prevent the page from saving when page score is
		 * being calculated on the output from the shortcode.
		 */
		if ( ! Request::get()->isAjax() ) {

			add_shortcode( self::TAG, array( __CLASS__, 'instance' ) );
			add_filter( 'pre_do_shortcode_tag', array( __CLASS__, 'maybeDoShortcode' ), 10, 4 );
		}
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 9.5
	 *
	 * @param array  $untrusted The shortcode arguments.
	 * @param string $content   The shortcode content.
	 * @param string $tag       The shortcode tag.
	 */
	public function __construct( array $untrusted, string $content = '', string $tag = self::TAG ) {

		$template = _array::get( $untrusted, 'template', '' );

		$this->loadTemplate( $template );

		if ( $this->template instanceof Template ) {

			$defaults  = $this->getDefaultAttributes();
			$untrusted = shortcode_atts( $defaults, $untrusted, $tag );

			$untrusted = apply_filters(
				"cn_list_atts-{$this->template->getSlug()}",
				apply_filters( 'cn_list_atts', $untrusted )
			);

			$this->attributes = $this->prepareAttributes( $untrusted );
			$this->html       = $this->generateHTML();

		} else {

			$this->html = cnTemplatePart::loadTemplateError( $untrusted );
		}

		$this->content = $content;

		// Clear any filters that have been added.
		// This allows support using the shortcode multiple times on the same page.
		Hook_Transient::instance()->clear();
	}

	/**
	 * Load the template.
	 *
	 * @since 10.4.40
	 *
	 * @param string $slug The template slug.
	 */
	private function loadTemplate( string $slug ) {

		$this->template = cnTemplateFactory::loadTemplate( array( 'template' => $slug ) );

		if ( $this->template instanceof Template ) {

			/*
			 * This filter adds the current template paths to cnLocate so when template
			 * part file overrides are being searched for, it'll also search in template
			 * specific paths. This filter is then removed at the end of the shortcode.
			 */
			add_filter( 'cn_locate_file_paths', array( $this->template, 'templatePaths' ) );
			Hook_Transient::instance()->add( 'cn_locate_file_paths' );

			/**
			 * @todo Move to to {@see cnTemplateFactory::loadTemplate()}???
			 *       Note: These same actions are also in the [connections] and [upcoming_list] shortcodes.
			 */
			do_action( "cn_template_include_once-{$this->template->getSlug()}" );
			do_action( "cn_template_enqueue_js-{$this->template->getSlug()}" );
		}
	}

	/**
	 * The shortcode defaults.
	 *
	 * @since 9.5
	 * @since 10.4.40 Change method name from `getDefaults` to `getDefaultAttributes`.
	 *
	 * @return array
	 */
	protected function getDefaultAttributes(): array {

		$defaults = array(
			'id'         => null,
			'template'   => null,
			'force_home' => false,
			'random'     => false,
			'home_id'    => in_the_loop() && is_page() ? get_the_ID() : cnSettingsAPI::get( 'connections', 'home_page', 'page_id' ),
		);

		return apply_filters(
			"cn_list_atts_permitted-{$this->template->getSlug()}",
			apply_filters( 'cn_list_atts_permitted', $defaults )
		);
	}

	/**
	 * Parse the user supplied atts.
	 *
	 * @since 9.5
	 * @since 10.4.40 Change method name from `parseAtts` to `prepareAttributes`.
	 *
	 * @param array $attributes The shortcode arguments.
	 *
	 * @return array
	 */
	protected function prepareAttributes( array $attributes ): array {

		// Force some specific defaults.
		$attributes['content']         = '';
		$attributes['lock']            = true;
		$attributes['show_alphaindex'] = false;
		$attributes['show_alphahead']  = false;
		$attributes['limit']           = 1;

		_format::toBoolean( $attributes['force_home'] );
		_format::toBoolean( $attributes['random'] );

		// If `id` is not numeric, set it to a string which will be evaluated to a `0` (zero) in `cnRetrieve::entries()` and return no results.
		if ( ! is_numeric( $attributes['id'] ) ) {

			$attributes['id'] = 'none';
		}

		if ( true === $attributes['random'] ) {

			// If random is set, set `id` to `null`.
			$attributes['id']       = null;
			$attributes['order_by'] = 'id|RANDOM';
		}

		return $attributes;
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.40
	 *
	 * @return string
	 */
	protected function generateHTML(): string {

		$html = '';

		$this->attributes = apply_filters( 'cn_list_retrieve_atts', $this->attributes );
		$this->attributes = apply_filters( "cn_list_retrieve_atts-{$this->template->getSlug()}", $this->attributes );

		$results = Connections_Directory()->retrieve->entries( $this->attributes );

		// Apply any registered filters to the results.
		if ( ! empty( $results ) ) {

			$results = apply_filters( 'cn_list_results', $results );
			$results = apply_filters( "cn_list_results-{$this->template->getSlug()}", $results );
			Hook_Transient::instance()->add( "cn_list_results-{$this->template->getSlug()}" );

		} else {

			return '<p>' . esc_html__( 'Entry not found.', 'connections' ) . '</p>';
		}

		ob_start();

		// Prints the template's CSS file.
		// NOTE: This is primarily to support legacy templates which included a CSS
		// file which was not enqueued in the page header.
		do_action( "cn_template_inline_css-{$this->template->getSlug()}", $this->attributes );

		// The return to top anchor.
		do_action( 'cn_list_return_to_target', $this->attributes );

		$html .= ob_get_clean();

		$html .= sprintf(
			'<div class="cn-list" id="cn-list" data-connections-version="%1$s-%2$s">',
			esc_attr( Connections_Directory()->options->getVersion() ),
			esc_attr( Connections_Directory()->options->getDBVersion() )
		);

		$html .= sprintf(
			'<div class="cn-template cn-%1$s" id="cn-%1$s" data-template-version="%2$s">',
			esc_attr( $this->template->getSlug() ),
			esc_attr( $this->template->getVersion() )
		);

		$html .= $this->renderTemplate( $results );

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-' . $this->template->getSlug() . ' -->' : '' ) . PHP_EOL;

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list -->' : '' ) . PHP_EOL;

		// @todo This should be run via a filter.
		return cnShortcode::removeEOL( $html );
	}

	/**
	 * Generate the template HTML.
	 *
	 * @since 9.5
	 *
	 * @param array $items An array of entry data objects.
	 *
	 * @return string
	 */
	private function renderTemplate( array $items ): string {

		ob_start();

		do_action(
			"Connections_Directory/Render/Template/{$this->template->getSlug()}/Before",
			$this->attributes,
			$items,
			$this->template
		);

		cnTemplatePart::cards( $this->attributes, $items, $this->template );

		do_action(
			"Connections_Directory/Render/Template/{$this->template->getSlug()}/After",
			$this->attributes,
			$items,
			$this->template
		);

		$html = ob_get_clean();

		if ( false === $html ) {

			$html = '<p>' . esc_html__( 'Error rendering template.', 'connections' ) . '</p>';
		}

		return $html;
	}
}
