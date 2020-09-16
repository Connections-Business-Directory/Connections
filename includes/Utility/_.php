<?php

namespace Connections_Directory\Utility;

use WP_Error;

/**
 * Class _
 *
 * @package Connections_Directory\Utility
 */
final class _ {

	/**
	 * Determine if supplied array is a multidimensional array or not.
	 *
	 * @since  8.5.19
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public static function isDimensionalArray( array $value ) {

		return ! ( count( $value ) === count( $value, COUNT_RECURSIVE ) );
	}

	/**
	 * Recursively implode a multi-dimensional array.
	 *
	 * @since  8.2
	 *
	 * @param string $glue
	 * @param array  $pieces
	 *
	 * @return string
	 */
	public static function implodeDeep( $glue, $pieces ) {

		$implode = array();

		if ( ! is_array( $pieces ) ) {

			$pieces = array( $pieces );
		}

		foreach ( $pieces as $piece ) {

			if ( is_array( $piece ) ) {

				$implode[] = self::implodeDeep( $glue, $piece );

			} else {

				$implode[] = $piece;
			}
		}

		return implode( $glue, $implode );
	}

	/**
	 * Clean up an array, comma- or space-separated list of IDs.
	 *
	 * @since  8.2.9
	 *
	 * @param string|array $list
	 *
	 * @param string       $delimiters The characters in which to split the supplied string. Should be preg_split() safe.
	 *                                 Default: '\s,' This will split strings delimited with comma and spaces to an array.
	 *
	 * @return array
	 */
	public static function parseStringList( &$list, $delimiters = '\s,' ) {

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

	/**
	 * Wrapper method for @see json_decode().
	 *
	 * On success this will return the decoded JSON. On error, it'll return an instance of @see WP_Error()
	 * with the result of @see json_last_error().
	 *
	 * @since  8.3
	 *
	 * @param string $json  The data to decode.
	 * @param bool   $assoc When TRUE, returned objects will be converted into associative arrays.
	 * @param int    $depth Recursion depth.
	 *
	 * @return array|mixed|WP_Error
	 */
	public static function decodeJSON( $json, $assoc = FALSE, $depth = 512 ) {

		$data = json_decode( $json, $assoc, $depth );

		switch ( json_last_error() ) {

			case JSON_ERROR_NONE:
				$result = $data;
				break;

			case JSON_ERROR_DEPTH:
				$result = new WP_Error( 'json_decode_error', __( 'Maximum stack depth exceeded.', 'connections' ) );
				break;

			case JSON_ERROR_STATE_MISMATCH:
				$result = new WP_Error( 'json_decode_error', __( 'Underflow or the modes mismatch.', 'connections' ) );
				break;

			case JSON_ERROR_CTRL_CHAR:
				$result = new WP_Error( 'json_decode_error', __( 'Unexpected control character found.', 'connections' ) );
				break;

			case JSON_ERROR_SYNTAX:
				$result = new WP_Error( 'json_decode_error', __( 'Syntax error, malformed JSON.', 'connections' ) );
				break;

			case JSON_ERROR_UTF8:
				$result = new WP_Error( 'json_decode_error', __( 'Malformed UTF-8 characters, possibly incorrectly encoded.', 'connections' ) );
				break;

			default:
				$result = new WP_Error( 'json_decode_error', __( 'Unknown error.', 'connections' ) );
				break;
		}

		return $result;
	}

	/**
	 * Escapes HTML attribute value or array of attribute values.
	 *
	 * @since  8.5.18
	 *
	 * @param array|string $attr
	 * @param string       $glue
	 *
	 * @return array|string
	 */
	public static function escAttributeDeep( $attr, $glue = ' ' ) {

		if ( is_array( $attr ) ) {

			// Ensure all IDs are positive integers.
			$attr = array_map( 'esc_attr', $attr );

			// Remove any empty array values.
			$attr = array_filter( $attr );

			$attr = implode( $glue, $attr );

		} else {

			$attr = esc_attr( $attr );
		}

		return $attr;
	}

}
