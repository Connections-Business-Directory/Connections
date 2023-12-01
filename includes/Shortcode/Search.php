<?php
/**
 * The advances search shortcode.
 *
 * @since 10.4.40
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shortcode\Search
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );
namespace Connections_Directory\Shortcode;

use cnShortcode;
use cnTemplatePart;
use Connections_Directory\Request;
use Connections_Directory\Utility\_format;

/**
 * Class Search
 *
 * @package Connections_Directory\Shortcode
 */
final class Search {

	use Do_Shortcode;
	use Get_HTML;

	/**
	 * The shortcode tag.
	 *
	 * @since 10.4.40
	 */
	const TAG = 'cn-search';

	/**
	 * The shortcode attributes.
	 *
	 * @since 10.4.40
	 *
	 * @var array{force_home:string, home_id:int}
	 */
	private $attributes;

	/**
	 * The content from an enclosing shortcode.
	 *
	 * @since 10.4.40
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Register the shortcode.
	 *
	 * @since 10.4.40
	 */
	public static function add() {

		$register = apply_filters( 'Connections_Directory/Shortcode/Search/Maybe_Register', false );

		/*
		 * Do not register the shortcode when doing ajax requests.
		 * This is primarily implemented so the shortcodes are not run during Yoast SEO page score admin ajax requests.
		 * The page score can cause the ajax request to fail and/or prevent the page from saving when page score is
		 * being calculated on the output from the shortcode.
		 */
		if ( ! Request::get()->isAjax() && true === $register ) {

			add_filter( 'pre_do_shortcode_tag', array( __CLASS__, 'maybeDoShortcode' ), 10, 4 );
			add_action( 'Connections_Directory/Shortcode/View/Search', array( __CLASS__, 'view' ), 10, 3 );
			add_shortcode( self::TAG, array( __CLASS__, 'instance' ) );
		}
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.40
	 *
	 * @param array  $untrusted The shortcode arguments.
	 * @param string $content   The shortcode content.
	 * @param string $tag       The shortcode tag.
	 */
	public function __construct( array $untrusted, string $content = '', string $tag = self::TAG ) {

		// Add filter that parses the dynamic filter shortcode parameters. This filter is removed in the callback to ensure it only runs once.
		add_filter( 'shortcode_atts_' . self::TAG, array( $this, 'parseFilters' ), 10, 3 );

		$defaults  = $this->getDefaultAttributes();
		$untrusted = shortcode_atts( $defaults, $untrusted, $tag );

		$this->attributes = $this->prepareAttributes( $untrusted );
		$this->content    = $content;
		$this->html       = $this->generateHTML();
	}

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @internal
	 * @since 10.4.40
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
	 * Callback for `Connections_Directory/Shortcode/View/Search` action.
	 *
	 * @internal
	 * @since 10.4.40
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 */
	public static function view( array $atts, string $content = '', string $tag = self::TAG ) {

		self::instance( $atts, $content, $tag )->render();
	}

	/**
	 * The shortcode attribute defaults.
	 *
	 * @since 10.4.40
	 *
	 * @return array{
	 *     filters: array,
	 *     force_home:string,
	 *     home_id:int
	 * }
	 */
	private function getDefaultAttributes(): array {

		return array(
			'filters'    => array(),
			'force_home' => false,
			'home_id'    => cnShortcode::getHomeID(),
		);
	}

	/**
	 * Parse and prepare the shortcode attributes.
	 *
	 * @since 10.4.40
	 *
	 * @param array $untrusted The shortcode arguments.
	 *
	 * @return array
	 */
	private function prepareAttributes( array $untrusted ): array {

		$trusted['force_home'] = _format::toBoolean( $untrusted['force_home'] );

		$trusted['home_id'] = filter_var(
			$untrusted['home_id'],
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => cnShortcode::getHomeID(),
					'min_range' => 1,
				),
			)
		);

		// @todo Need to validate and sanitize filter properties.
		$trusted['filters'] = $this->prepareFilters( $untrusted['filters'] );
// var_dump( $trusted );
		return $trusted;
	}

	/**
	 * Callback for the `shortcode_atts_{$tag}` filter.
	 *
	 * Parse the `filters{_+d}` parameter attributes to create an indexed array of associative arrays
	 * where the array key is the parameter names and the array value is the parameter value.
	 *
	 * Example:
	 *
	 * ```
	 * array(
	 *     1 => array(
	 *         'source' => 'keyword',
	 *         'type'   => 'text',
	 *     ),
	 *     2 => array(
	 *         'source' => 'taxonomy',
	 *         'id'     => '14',
	 *         'type'   => 'radio',
	 *     ),
	 * )
	 * ```
	 *
	 * @internal
	 * @since 10.4.40
	 *
	 * @param array $parsed    The shortcode user supplied arguments merged with the shortcode defaults.
	 * @param array $defaults  The shortcode defaults.
	 * @param array $untrusted The user supplied shortcode arguments.
	 *
	 * @return array
	 */
	public function parseFilters( array $parsed, array $defaults, array $untrusted ): array {

		$filters = array();

		foreach ( $untrusted as $subject => $value ) {

			if ( 1 === preg_match( '/^filter(?:_(\d+)$|$)/i', $subject, $matches ) ) {

				$index = 0;

				if ( isset( $matches[1] ) ) {

					$index = $matches[1];
				}

				$filters[ $index ] = $this->parseFilter( $value );
			}
		}

		// Sort the filters by their index as they will form fields will be rendered in this order.
		ksort( $filters );

		$parsed['filters'] = $filters;

		/*
		 * This filter is added each time this shortcode in initiated, if this filter is running,
		 * remove it, so it only runs once.
		 */
		remove_filter( current_filter(), array( $this, __FUNCTION__ ), 10 );

		return $parsed;
	}

	/**
	 * Parse the filter parameter array where the array key is the property and
	 * the array value is the property value.
	 *
	 * @since 10.4.40
	 *
	 * @param string $string The filter parameters and values.
	 *
	 * @return array
	 */
	private function parseFilter( string $string ): array {

		$array = explode( ';', trim( $string ) );

		if ( ! is_array( $array ) ) {
			return array();
		}

		$properties = array();

		foreach ( $array as $value ) {

			if ( 1 === preg_match( '/(.*?):(.*)/', $value, $matches ) ) {

				$properties[ trim( $matches[1] ) ] = trim( $matches[2] );
			}
		}

		return $properties;
	}

	/**
	 * @since 10.4.40
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	private function prepareFilters( array $filters ): array {

		foreach ( $filters as $i => &$filter ) {

			// If the source parameter is not set, the filter is invalid.
			if ( ! array_key_exists( 'source', $filter ) ) {
				unset( $filters[ $i ] );
				continue;
			}

			switch ( $filter['source'] ) {

				case 'county':
					break;

				case 'country':
					break;

				case 'district':
					break;

				case 'email':
					break;

				case 'region':
					break;

				case 'taxonomy':
					/**
					 * The default values for the taxonomy filter.
					 *
					 * @var array{
					 *     slug: string,
					 *     hide_empty: bool,
					 *     show_count: bool,
					 * } $defaults
					 */
					$defaults = array(
						'slug'              => 'category',
						'display_as'        => 'select', // Valid option are (checklist|radio|select).
						'child_of'          => 0,
						'orderby'           => 'name',
						'order'             => 'ASC',
						'hierarchical'      => true,
						'hide_empty'        => true,
						'show_count'        => false,
						// 'show_select_all'   => false,  // Whether to display the "Show All" option.
						// 'select_all_label'  => '',
						'show_option_none'  => true,   // Whether to display the "Select Category" option.
						'show_option_label' => __( 'Select Category', 'connections' ),
						'option_none_value' => -1,
						'value_field'       => 'term_id',
					);

					break;

				case 'keyword':
					$defaults = array(
						'placeholder' => '',
					);

					break;

				case 'postal_code':
					$defaults = array(
						'placeholder'    => '',
						'radius_options' => '',
						'radius_unit'    => '',
					);

					break;

				default:
					// If the source is invalid, remove it.
					unset( $filters[ $i ] );
			}
		}

		return $filters;
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.40
	 */
	private function generateHTML(): string {

		$html    = 'error';
		$partial = cnTemplatePart::get(
			'search',
			'advanced',
			array(
				'atts'    => $this->attributes,
				'content' => $this->content,
			),
			true,
			true,
			false
		);

		if ( is_string( $partial ) && 0 < strlen( $partial ) ) {

			$html = $partial;

		}

		return $html;
	}
}
