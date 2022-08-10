<?php
/**
 *
 *
 * @since
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Utility;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class _parse
 *
 * @package Connections_Directory\Utility
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _parse {

	/**
	 * Recursive {@see wp_parse_args()}.
	 *
	 * @param string|array|object $args     Value to merge with $defaults.
	 * @param array               $defaults Optional. Array that serves as the defaults.
	 *                                      Default empty array.
	 *
	 * @return array Merged user defined values with defaults.
	 */
	public static function args( $args, $defaults = array() ) {

		if ( is_object( $args ) ) {
			$parsed = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$parsed =& $args;
		} else {
			wp_parse_str( $args, $parsed );
		}

		if ( is_array( $defaults ) && $defaults ) {
			$parsed = array_merge( $defaults, $parsed );
		}

		foreach ( $parsed as $k => &$v ) {
			if ( is_array( $v ) && isset( $defaults[ $k ] ) ) {
				$parsed[ $k ] = self::args( $v, $defaults[ $k ] );
			} else {
				$parsed[ $k ] = $v;
			}
		}

		return $parsed;
	}

	/**
	 * Clean up an array, comma- or space-separated list of IDs.
	 *
	 * @since 8.2.9
	 *
	 * @param string|array $list
	 * @param string       $delimiters The characters in which to split the supplied string. Should be preg_split() safe.
	 *                                 Default: '\s,' This will split strings delimited with comma and spaces to an array.
	 *
	 * @return array
	 */
	public static function stringList( &$list, $delimiters = '\s,' ) {

		// Convert to an array if the supplied list is not.
		if ( ! is_array( $list ) ) {

			$list = preg_split( '/[' . $delimiters . ']+/', $list );
		}

		// Remove NULL, FALSE and empty strings (""), but leave values of 0 (zero).
		$list = array_filter( $list, 'strlen' );

		// Cleanup any excess whitespace.
		$list = array_map( 'trim', $list );

		// Return only unique values.
		return array_unique( $list );
	}
}
