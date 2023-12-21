<?php
/**
 * Helper methods for parsing input/parameters.
 *
 * @since 10.4.26
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
	 * Optionally discard key/value pairs in `$parameters` where the index does not exist in `$defaults`.
	 *
	 * @link  http://www.peterrknight.com/fear-and-surprise-improving-a-widespread-wordpress-pattern/
	 *
	 * @since 10.4.26
	 *
	 * @param string|array|object $parameters Value to merge with $defaults.
	 * @param array               $defaults   Array that serves as the defaults.
	 * @param bool                $discard    Discard key/value pairs in `$parameters` where the index does not exist in `$defaults`.
	 * @param bool                $recursive  Whether to transverse the parameters array.
	 * @param array               $exclude    Array indexes to exclude when transversing the parameters array.
	 *
	 * @return array Merged user defined values with defaults.
	 */
	public static function parameters( $parameters, $defaults = array(), $discard = true, $recursive = true, $exclude = array() ) {

		if ( is_object( $parameters ) ) {
			$parsed = get_object_vars( $parameters );
		} elseif ( is_array( $parameters ) ) {
			$parsed =& $parameters;
		} else {
			wp_parse_str( $parameters, $parsed );
		}

		if ( is_array( $defaults ) && $defaults ) {
			$parsed = array_merge( $defaults, $parsed );
		}

		/*
		 * Filter to discard key/value pairs when `$discard` is `true`.
		 */
		$filter = static function ( $parameters, $defaults ) use ( $discard ) {

			// if ( $discard ) {
			// 	$intersect  = array_intersect_key( $parameters, $defaults ); // Get data for which is in the valid fields.
			// 	$difference = array_diff_key( $defaults, $parameters );      // Get default data which is not supplied.
			// 	return array_merge( $intersect, $difference );               // Merge the results. Contains only key/value pairs of all defaults.
			// }

			if ( $discard ) {
				foreach ( $parameters as $k => $parameter ) {
					if ( ! array_key_exists( $k, $defaults ) ) {
						unset( $parameters[ $k ] );
					}
				}
			}

			return $parameters;
		};

		$parsed = $filter( $parsed, $defaults );

		foreach ( $parsed as $k => &$v ) {

			if ( is_array( $v ) && isset( $defaults[ $k ] ) && $recursive && ! in_array( $k, $exclude ) ) {
				$parsed[ $k ] = self::parameters( $filter( $v, $defaults[ $k ] ), $defaults[ $k ], $discard, $recursive, $exclude );
			} elseif ( is_array( $v ) && isset( $defaults[ $k ] ) && $recursive && in_array( $k, $exclude ) ) {
				$parsed[ $k ] = $filter( $v, $defaults[ $k ] );
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
	 * @param string|array $list       List of values.
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
