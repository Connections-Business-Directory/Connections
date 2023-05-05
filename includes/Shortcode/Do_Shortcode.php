<?php
/**
 * Ensure an array is returned for `$atts` and a string for `$content` (`$m[5]`).
 * This is to ensure no PHP errors due to using parameter type hinting.
 *
 * @since 10.4.40
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shortcode
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );
namespace Connections_Directory\Shortcode;

/**
 * Trait Prepare_Attributes
 *
 * @package Connections_Directory\Shortcode
 */
trait Do_Shortcode {

	/**
	 * Callback for the `pre_do_shortcode_tag` filter.
	 *
	 * @internal
	 * @since 10.4.40
	 *
	 * @param false|string $shortCircuit Short-circuit return value.
	 * @param string       $tag          Shortcode name.
	 * @param array|string $atts         Shortcode attributes array or empty string.
	 * @param array        $m            Regular expression match array.
	 *
	 * @return false|string
	 */
	public static function maybeDoShortcode( $shortCircuit, string $tag, $atts, array $m ) {

		if ( self::TAG !== $tag ) {

			return $shortCircuit;
		}

		return self::doShortcode( $tag, $atts, $m );
	}

	/**
	 * Prepare the shortcode attributes and render the shortcode.
	 *
	 * @since 10.4.40
	 *
	 * @param string       $tag  Shortcode name.
	 * @param array|string $atts Shortcode attributes array or empty string.
	 * @param array        $m    Regular expression match array.
	 *
	 * @return string
	 */
	private static function doShortcode( string $tag, $atts, array $m ): string {

		global $shortcode_tags;

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$content = isset( $m[5] ) ? $m[5] : '';

		$output = $m[1] . call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag ) . $m[6];

		/**
		 * Filters the output created by a shortcode callback.
		 *
		 * Added to ensure the core WP filter is applied when the shortcode is
		 * short-circuited.
		 *
		 * @since 10.4.40
		 *
		 * @param string       $output Shortcode output.
		 * @param string       $tag    Shortcode name.
		 * @param array|string $attr   Shortcode attributes array or empty string.
		 * @param array        $m      Regular expression match array.
		 */
		return apply_filters( 'do_shortcode_tag', $output, $tag, $atts, $m );
	}
}
