<?php
/**
 * The `[cn-directory]` shortcode.
 *
 * @since      10.4.41
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Shortcode\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );
namespace Connections_Directory\Shortcode;

use cnQuery;
use cnSettingsAPI;
use cnShortcode;
use cnTemplate as Template;
use cnTemplateFactory;
use cnTemplatePart;
use Connections_Directory\Request;
use Connections_Directory\Template\Hook_Transient;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_sanitize;

/**
 * Class Directory
 *
 * @package Connections_Directory\Shortcode
 */
final class Entry_Directory {

	use Do_Shortcode;
	use Get_HTML;

	/**
	 * The shortcode tag.
	 *
	 * @since 10.4.41
	 */
	const TAG = 'cn-directory';

	/**
	 * The shortcode attributes.
	 *
	 * @since 10.4.41
	 *
	 * @var array
	 */
	private $attributes = array();

	/**
	 * The content from an enclosing shortcode.
	 *
	 * @since 10.4.41
	 *
	 * @var string
	 */
	private $content;

	/**
	 * The content from an enclosing shortcode.
	 *
	 * @since 10.4.42
	 *
	 * @var string
	 */
	private $tag;

	/**
	 * An instance of the cnTemplate or false.
	 *
	 * @since 10.4.41
	 *
	 * @var Template|false
	 */
	private $template;

	/**
	 * Register the shortcode.
	 *
	 * @since 10.4.41
	 */
	public static function add() {

		/*
		 * Do not register the shortcode when doing ajax requests.
		 * This is primarily implemented so the shortcodes are not run during Yoast SEO page score admin ajax requests.
		 * The page score can cause the ajax request to fail and/or prevent the page from saving when page score is
		 * being calculated on the output from the shortcode.
		 */
		if ( ! Request::get()->isAjax() ) {

			add_filter( 'pre_do_shortcode_tag', array( __CLASS__, 'maybeDoShortcode' ), 10, 4 );
			add_shortcode( self::TAG, array( __CLASS__, 'instance' ) );
		}
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.41
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
		$this->tag     = $tag;

		// Clear any filters that have been added.
		// This allows support using the shortcode multiple times on the same page.
		Hook_Transient::instance()->clear();
	}

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @internal
	 * @since 10.4.41
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 *
	 * @return static
	 */
	public static function instance( array $atts, string $content = '', string $tag = self::TAG ): self {

		return new self( $atts, $content, $tag );
	}

	/**
	 * Load the template.
	 *
	 * @since 10.4.41
	 *
	 * @param string $slug The template slug.
	 */
	private function loadTemplate( string $slug ) {

		$attributes = array( 'template' => $slug );

		if ( is_customize_preview() ) {

			/**
			 * Hook to allow the active template to be overridden and set to the current template being customized.
			 *
			 * @since 8.4
			 *
			 * @param array $attributes {
			 *     @type string $template The template slug of the template being customized.
			 * }
			 */
			$attributes = apply_filters( 'cn_template_customizer_template', $attributes );
		}

		$this->template = cnTemplateFactory::loadTemplate( $attributes );

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
	 * @since 10.4.41
	 *
	 * @return array
	 */
	private function getDefaultAttributes(): array {

		$defaults = array(
			'id'                    => null,
			'slug'                  => null,
			'category'              => '',
			'category_in'           => '',
			'exclude_category'      => '',
			'category_name'         => '',
			'category_slug'         => '',
			'allow_public_override' => false,
			'private_override'      => false,
			'show_alphaindex'       => cnSettingsAPI::get( 'connections', 'display_results', 'index' ),
			'repeat_alphaindex'     => cnSettingsAPI::get( 'connections', 'display_results', 'index_repeat' ),
			'show_alphahead'        => cnSettingsAPI::get( 'connections', 'display_results', 'show_current_character' ),
			'list_type'             => null,
			'order_by'              => '',
			'limit'                 => null,
			'offset'                => null,
			'family_name'           => '',
			'last_name'             => '',
			'title'                 => '',
			'organization'          => '',
			'department'            => '',
			'district'              => null,
			'county'                => null,
			'city'                  => '',
			'state'                 => '',
			'zip_code'              => '',
			'country'               => '',
			'meta_query'            => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'content'               => '', // @todo Unused needs remove after all templates are updated to remove it.
			'near_addr'             => null,
			'latitude'              => null,
			'longitude'             => null,
			'radius'                => 10,
			'unit'                  => 'mi',
			'template'              => null, /* @since version 0.7.1.0 */
			'width'                 => null,
			'lock'                  => false,
			'force_home'            => false,
			'home_id'               => cnShortcode::getHomeID(),
		);

		return apply_filters(
			"cn_list_atts_permitted-{$this->template->getSlug()}",
			apply_filters( 'cn_list_atts_permitted', $defaults )
		);
	}

	/**
	 * Parse the user supplied atts.
	 *
	 * @since 10.4.41
	 *
	 * @param array $attributes The shortcode arguments.
	 *
	 * @return array
	 */
	private function prepareAttributes( array $attributes ): array {

		/*
		 * Convert some $attributes values in the array to boolean.
		 */
		_format::toBoolean( $attributes['allow_public_override'] );
		_format::toBoolean( $attributes['private_override'] );
		_format::toBoolean( $attributes['show_alphaindex'] );
		_format::toBoolean( $attributes['repeat_alphaindex'] );
		_format::toBoolean( $attributes['show_alphahead'] );
		_format::toBoolean( $attributes['lock'] );
		_format::toBoolean( $attributes['force_home'] );

		/*
		 * Sanitize integer values.
		 */
		$attributes['width'] = _sanitize::integer( $attributes['width'] );

		/*
		 * The post editor entity encodes the post text we have to decode it
		 * so a match can be made when the query is run.
		 */
		$attributes['family_name']   = html_entity_decode( $attributes['family_name'] );
		$attributes['last_name']     = is_array( $attributes['last_name'] ) ? array_map( 'html_entity_decode', $attributes['last_name'] ) : html_entity_decode( $attributes['last_name'] );
		$attributes['title']         = is_array( $attributes['title'] ) ? array_map( 'html_entity_decode', $attributes['title'] ) : html_entity_decode( $attributes['title'] );
		$attributes['organization']  = is_array( $attributes['organization'] ) ? array_map( 'html_entity_decode', $attributes['organization'] ) : html_entity_decode( $attributes['organization'] );
		$attributes['department']    = is_array( $attributes['department'] ) ? array_map( 'html_entity_decode', $attributes['department'] ) : html_entity_decode( $attributes['department'] );
		$attributes['city']          = is_array( $attributes['city'] ) ? array_map( 'html_entity_decode', $attributes['city'] ) : html_entity_decode( $attributes['city'] );
		$attributes['state']         = is_array( $attributes['state'] ) ? array_map( 'html_entity_decode', $attributes['state'] ) : html_entity_decode( $attributes['state'] );
		$attributes['zip_code']      = is_array( $attributes['zip_code'] ) ? array_map( 'html_entity_decode', $attributes['zip_code'] ) : html_entity_decode( $attributes['zip_code'] );
		$attributes['country']       = is_array( $attributes['country'] ) ? array_map( 'html_entity_decode', $attributes['country'] ) : html_entity_decode( $attributes['country'] );
		$attributes['category_name'] = html_entity_decode( $attributes['category_name'] );

		if ( 0 < strlen( $attributes['meta_query'] ) ) {

			// The meta query syntax follows the JSON standard, except, the WordPress Shortcode API does not allow
			// brackets within shortcode options, so parenthesis have to be used instead, so, lets swap them
			// that was json_decode can be run and the resulting array used in cnRetrieve::entries().
			$attributes['meta_query'] = str_replace( array( '(', ')' ), array( '[', ']' ), $attributes['meta_query'] );

			$metaQuery = _::maybeJSONdecode( $attributes['meta_query'] );

			$attributes['meta_query'] = is_array( $metaQuery ) ? $metaQuery : array();
		}

		return $attributes;
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.41
	 *
	 * @return string
	 */
	private function generateHTML(): string {

		$html = '';

		$this->attributes = apply_filters( 'cn_list_retrieve_atts', $this->attributes );
		$this->attributes = apply_filters( "cn_list_retrieve_atts-{$this->template->getSlug()}", $this->attributes );

		$results = Connections_Directory()->retrieve->entries( $this->attributes );

		// Apply any registered filters to the results.
		if ( ! empty( $results ) ) {

			$results = apply_filters( 'cn_list_results', $results );
			$results = apply_filters( "cn_list_results-{$this->template->getSlug()}", $results );
			Hook_Transient::instance()->add( "cn_list_results-{$this->template->getSlug()}" );
		}

		$class    = array( 'cn-template', "cn-{$this->template->getSlug()}" );
		$isSingle = cnQuery::getVar( 'cn-entry-slug' ) ? true : false;

		if ( $isSingle ) {

			array_push( $class, 'cn-template-is-single' );
		}

		ob_start();

		// Prints the template's CSS file.
		// NOTE: This is primarily to support legacy templates which included a CSS
		// file which was not enqueued in the page header.
		do_action( 'cn_template_inline_css-' . $this->template->getSlug(), $this->attributes );

		// The return to top anchor.
		do_action( 'cn_list_return_to_target', $this->attributes );

		$html .= ob_get_clean();

		$html .= sprintf(
			'<div class="cn-list" id="cn-list" data-connections-version="%1$s-%2$s"%3$s>',
			esc_attr( Connections_Directory()->options->getVersion() ),
			esc_attr( Connections_Directory()->options->getDBVersion() ),
			empty( $atts['width'] ) ? '' : ' style="' . _escape::css( "width: {$atts['width']}px;" ) . '"'
		);

		$html .= sprintf(
			'<div class="%1$s" id="cn-%2$s" data-template-version="%3$s">',
			_escape::classNames( $class ),
			esc_attr( $this->template->getSlug() ),
			esc_attr( $this->template->getVersion() )
		);

		$html .= $this->renderTemplate( $results );

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-' . esc_attr( $this->template->getSlug() ) . ' -->' : '' ) . PHP_EOL;

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list -->' : '' ) . PHP_EOL;

		// @todo This should be run via a filter.
		return cnShortcode::removeEOL( $html );
	}

	/**
	 * Generate the template HTML.
	 *
	 * @since 10.4.41
	 *
	 * @param array $items An array of entry data objects.
	 *
	 * @return string
	 */
	private function renderTemplate( array $items ): string {

		$html = '';

		// The filter should check $content that content is not empty before processing $content.
		// And if it is empty the filter should return (bool) FALSE, so the core template parts can be executed.
		$content = apply_filters( "cn_shortcode_content-{$this->tag}", false, $this->content, $this->attributes, $items, $this->template );

		if ( false === $content ) {

			ob_start();

			// Render the core result list header.
			cnTemplatePart::header( $this->attributes, $items, $this->template );

			// Render the core result list body.
			cnTemplatePart::body( $this->attributes, $items, $this->template );

			// Render the core result list footer.
			cnTemplatePart::footer( $this->attributes, $items, $this->template );

			$html .= ob_get_clean();

		} else {

			$html .= $content;
		}

		return $html;
	}
}
