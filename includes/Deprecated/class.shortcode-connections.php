<?php
/**
 * Deprecated file, still required as a couple addons presently call the methods.
 *
 * @deprecated 10.4.42
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shortcode\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

use Connections_Directory\Shortcode;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_sanitize;
use function Connections_Directory\Utility\_deprecated\_file as _deprecated_file;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

_deprecated_file( basename( __FILE__ ), '10.4.42' );

/**
 * The core [connections] shortcode.
 *
 * @package     Connections
 * @subpackage  Shortcode API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */
class cnShortcode_Connections {

	/**
	 * Callback for the `[connections]` shortcode.
	 *
	 * Display results based on query var `cn-view`.
	 *
	 * @internal
	 * @since 0.7.3
	 * @since 10.4.40 Moved from {@file class.shortcode.php}.
	 * @deprecated 10.4.41
	 *
	 * @param array|string $atts    Shortcode attributes array or empty string.
	 * @param string|null  $content The content of a shortcode when it wraps some content.
	 * @param string       $tag     Shortcode name.
	 *
	 * @return string
	 */
	public static function view( $atts, $content = '', $tag = 'connections' ) {

		_deprecated_function( __METHOD__, '10.4.41', 'Directory_View::instance()' );

		return Shortcode\Directory_View::instance( $atts, $content, $tag )->getHTML();
	}

	/**
	 * Renders the entry directory.
	 *
	 * @since 0.8
	 * @deprecated 10.4.41
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content = '', $tag = 'connections' ) {

		_deprecated_function( __METHOD__, '10.4.41', 'Entry_Directory::instance()' );

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$html = '';

		if ( is_customize_preview() ) {

			/**
			 * Hook to allow the active template to be overridden and set to the current template being customized.
			 *
			 * @since 8.4
			 *
			 * @param array $atts {
			 *     @type string $template The template slug of the template being customized.
			 * }
			 */
			$atts = apply_filters( 'cn_template_customizer_template', $atts );
		}

		/** @var cnTemplate $template */
		$template = cnTemplateFactory::loadTemplate( $atts );

		if ( false === $template ) {
			return cnTemplatePart::loadTemplateError( $atts );
		}

		/*
		 * This filter adds the current template paths to cnLocate so when template
		 * part file overrides are being searched for, it'll also search in template
		 * specific paths. This filter is then removed at the end of the shortcode.
		 */
		add_filter( 'cn_locate_file_paths', array( $template, 'templatePaths' ) );
		cnShortcode::addFilterRegistry( 'cn_locate_file_paths' );

		/**
		 * @todo Move to to {@see cnTemplateFactory::loadTemplate()}???
		 *       Note: These same actions are also in the [upcoming_list] and [cn-entry] shortcodes.
		 */
		do_action( 'cn_template_include_once-' . $template->getSlug() );
		do_action( 'cn_template_enqueue_js-' . $template->getSlug() );

		/*
		 * Now that the template has been loaded, Validate the user supplied shortcode atts.
		 */
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

		$defaults = apply_filters( 'cn_list_atts_permitted', $defaults );
		$defaults = apply_filters( 'cn_list_atts_permitted-' . $template->getSlug(), $defaults );

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$atts = apply_filters( 'cn_list_atts', $atts );
		$atts = apply_filters( 'cn_list_atts-' . $template->getSlug(), $atts );

		/*
		 * Convert some $atts values in the array to boolean.
		 */
		_format::toBoolean( $atts['allow_public_override'] );
		_format::toBoolean( $atts['private_override'] );
		_format::toBoolean( $atts['show_alphaindex'] );
		_format::toBoolean( $atts['repeat_alphaindex'] );
		_format::toBoolean( $atts['show_alphahead'] );
		_format::toBoolean( $atts['lock'] );
		_format::toBoolean( $atts['force_home'] );

		/*
		 * Sanitize integer values.
		 */
		$atts['width'] = _sanitize::integer( $atts['width'] );

		/*
		 * The post editor entity encodes the post text we have to decode it
		 * so a match can be made when the query is run.
		 */
		$atts['family_name']   = html_entity_decode( $atts['family_name'] );
		$atts['last_name']     = is_array( $atts['last_name'] ) ? array_map( 'html_entity_decode', $atts['last_name'] ) : html_entity_decode( $atts['last_name'] );
		$atts['title']         = is_array( $atts['title'] ) ? array_map( 'html_entity_decode', $atts['title'] ) : html_entity_decode( $atts['title'] );
		$atts['organization']  = is_array( $atts['organization'] ) ? array_map( 'html_entity_decode', $atts['organization'] ) : html_entity_decode( $atts['organization'] );
		$atts['department']    = is_array( $atts['department'] ) ? array_map( 'html_entity_decode', $atts['department'] ) : html_entity_decode( $atts['department'] );
		$atts['city']          = is_array( $atts['city'] ) ? array_map( 'html_entity_decode', $atts['city'] ) : html_entity_decode( $atts['city'] );
		$atts['state']         = is_array( $atts['state'] ) ? array_map( 'html_entity_decode', $atts['state'] ) : html_entity_decode( $atts['state'] );
		$atts['zip_code']      = is_array( $atts['zip_code'] ) ? array_map( 'html_entity_decode', $atts['zip_code'] ) : html_entity_decode( $atts['zip_code'] );
		$atts['country']       = is_array( $atts['country'] ) ? array_map( 'html_entity_decode', $atts['country'] ) : html_entity_decode( $atts['country'] );
		$atts['category_name'] = html_entity_decode( $atts['category_name'] );

		if ( 0 < strlen( $atts['meta_query'] ) ) {

			// The meta query syntax follows the JSON standard, except, the WordPress Shortcode API does not allow
			// brackets within shortcode options, so parenthesis have to be used instead, so, lets swap them
			// that was json_decode can be run and the resulting array used in cnRetrieve::entries().
			$atts['meta_query'] = str_replace( array( '(', ')' ), array( '[', ']' ), $atts['meta_query'] );

			$metaQuery = _::maybeJSONdecode( $atts['meta_query'] );

			$atts['meta_query'] = is_array( $metaQuery ) ? $metaQuery : array();
		}

		$atts = apply_filters( 'cn_list_retrieve_atts', $atts );
		$atts = apply_filters( 'cn_list_retrieve_atts-' . $template->getSlug(), $atts );

		$results = $instance->retrieve->entries( $atts );
		// $html .= print_r( $instance->lastQuery, TRUE );

		// Apply any registered filters to the results.
		if ( ! empty( $results ) ) {

			$results = apply_filters( 'cn_list_results', $results );
			$results = apply_filters( 'cn_list_results-' . $template->getSlug(), $results );
			cnShortcode::addFilterRegistry( 'cn_list_results-' . $template->getSlug() );
		}

		$class    = array( 'cn-template', "cn-{$template->getSlug()}" );
		$isSingle = cnQuery::getVar( 'cn-entry-slug' ) ? true : false;

		if ( $isSingle ) {

			array_push( $class, 'cn-template-is-single' );
		}

		ob_start();

			// Prints the template's CSS file.
			// NOTE: This is primarily to support legacy templates which included a CSS
			// file which was not enqueued in the page header.
			do_action( 'cn_template_inline_css-' . $template->getSlug(), $atts );

			// The return to top anchor.
			do_action( 'cn_list_return_to_target', $atts );

		$html .= ob_get_clean();

		$html .= sprintf(
			'<div class="cn-list" id="cn-list" data-connections-version="%1$s-%2$s"%3$s>',
			esc_attr( $instance->options->getVersion() ),
			esc_attr( $instance->options->getDBVersion() ),
			empty( $atts['width'] ) ? '' : ' style="' . _escape::css( "width: {$atts['width']}px;" ) . '"'
		);

		$html .= sprintf(
			'<div class="%1$s" id="cn-%2$s" data-template-version="%3$s">',
			_escape::classNames( $class ),
			esc_attr( $template->getSlug() ),
			esc_attr( $template->getVersion() )
		);

		// The filter should check $content that content is not empty before processing $content.
		// And if it is empty the filter should return (bool) FALSE, so the core template parts can be executed.
		$content = apply_filters( "cn_shortcode_content-$tag", false, $content, $atts, $results, $template );

		if ( false === $content ) {

			ob_start();

			// Render the core result list header.
			cnTemplatePart::header( $atts, $results, $template );

			// Render the core result list body.
			cnTemplatePart::body( $atts, $results, $template );

			// Render the core result list footer.
			cnTemplatePart::footer( $atts, $results, $template );

			$html .= ob_get_clean();

		} else {

			$html .= $content;
		}

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-' . esc_attr( $template->getSlug() ) . ' -->' : '' ) . PHP_EOL;

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list -->' : '' ) . PHP_EOL;

		// Clear any filters that have been added.
		// This allows support using the shortcode multiple times on the same page.
		cnShortcode::clearFilterRegistry();

		// @todo This should be run via a filter.
		return cnShortcode::removeEOL( $html );
	}
}
