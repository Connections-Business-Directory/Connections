<?php
/**
 * Shortcode abstract class.
 *
 * @since 10.4.55
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections\Shortcode
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory;

use Connections_Directory\Shortcode\Do_Shortcode;
use Connections_Directory\Shortcode\Get_HTML;

/**
 * Class Shortcode
 *
 * @package Connections_Directory
 */
abstract class Shortcode {

	use Do_Shortcode;
	use Get_HTML;

	/**
	 * The shortcode tag.
	 *
	 * @since 10.4.55
	 */
	const TAG = '';

	/**
	 * The shortcode attributes.
	 *
	 * @since 10.4.55
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The content from an enclosing shortcode.
	 *
	 * @since 10.4.55
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * The shortcode tag.
	 *
	 * @since 10.4.55
	 *
	 * @var string
	 */
	protected $tag = '';

	/**
	 * Register the shortcode.
	 *
	 * @since 10.4.55
	 */
	public static function add() {

		/*
		 * Do not register the shortcode when doing ajax requests.
		 * This is primarily implemented so the shortcodes are not run during Yoast SEO page score admin ajax requests.
		 * The page score can cause the ajax request to fail and/or prevent the page from saving when page score is
		 * being calculated on the output from the shortcode.
		 */
		if ( ! Request::get()->isAjax() ) {

			add_filter( 'pre_do_shortcode_tag', array( get_called_class(), 'maybeDoShortcode' ), 10, 4 );
			add_shortcode( static::TAG, array( get_called_class(), 'instance' ) );
		}
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.55
	 *
	 * @param array  $untrusted The shortcode arguments.
	 * @param string $content   The shortcode content.
	 * @param string $tag       The shortcode tag.
	 */
	abstract public function __construct( array $untrusted, string $content = '', string $tag = '' );

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @internal
	 * @since 10.4.55
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 *
	 * @return static
	 */
	public static function instance( array $atts, string $content = '', string $tag = '' ): self {

		return new static( $atts, $content, $tag );
	}

	/**
	 * The shortcode attribute defaults.
	 *
	 * @since 10.4.55
	 *
	 * @return array
	 */
	abstract protected function getDefaultAttributes(): array;

	/**
	 * Parse and prepare the shortcode attributes.
	 *
	 * @since 10.4.55
	 *
	 * @param array $attributes The shortcode arguments.
	 *
	 * @return array
	 */
	abstract protected function prepareAttributes( array $attributes ): array;

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.55
	 *
	 * @return string
	 */
	abstract protected function generateHTML(): string;
}
