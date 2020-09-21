<?php

namespace Connections_Directory\Utility;

use cnHTML;
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
	 * @since 8.5.19
	 * @deprecated 9.11
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public static function isDimensionalArray( array $value ) {

		_deprecated_function( __METHOD__, '9.11', '_array::isDimensional()' );

		return _array::isDimensional( $value );
	}

	/**
	 * Recursively implode a multi-dimensional array.
	 *
	 * @since 8.2
	 * @deprecated 9.11
	 *
	 * @param string $glue
	 * @param array  $pieces
	 *
	 * @return string
	 */
	public static function implodeDeep( $glue, $pieces ) {

		_deprecated_function( __METHOD__, '9.11', '_array::implodeDeep()' );

		return _array::implodeDeep( $glue, $pieces );
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
	 * @since 8.3
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
	 * JSON encode objects and arrays.
	 *
	 * @since 0.8
	 *
	 * @param mixed $value The value to maybe json_encode.
	 *
	 * @return mixed
	 */
	public static function maybeJSONencode( $value ) {

		if ( is_null( $value ) ) {

			return '';
		}

		if ( ! is_scalar( $value ) ) {

			return json_encode( $value );

		} else {

			return $value;
		}
	}

	/**
	 * Maybe json_decode the supplied value.
	 *
	 * @since 0.8
	 *
	 * @param mixed   $value The value to decode.
	 * @param boolean $array [optional] Whether or not the JSON decoded value should an object or an associative array.
	 *
	 * @return mixed
	 */
	public static function maybeJSONdecode( $value, $array = true ) {

		if ( ! is_string( $value ) || 0 == strlen( $value ) ) {

			return $value;
		}

		// A JSON encoded string will start and end with either a square bracket of curly bracket.
		if ( ( $value[0] == '[' && $value[ strlen( $value ) - 1 ] == ']' ) || ( $value[0] == '{' && $value[ strlen( $value ) - 1 ] == '}' ) ) {

			// @todo, this should be refactored to utilize self::decodeJSON()
			$value = json_decode( $value, $array );
		}

		if ( is_null( $value ) ) {

			return '';

		} else {

			return $value;
		}
	}

	/**
	 * Escapes HTML attribute value or array of attribute values.
	 *
	 * @since 8.5.18
	 *
	 * @param array|string $attr
	 * @param string       $glue
	 *
	 * @return array|string
	 */
	public static function escAttributeDeep( $attr, $glue = ' ' ) {

		_deprecated_function( __METHOD__, '9.11', 'cnHTML::escapeAttributes()' );

		return cnHTML::escapeAttributes( $attr, $glue );
	}

	/**
	 * Get user IP.
	 *
	 * @since 0.8
	 *
	 * @return string The IP address.
	 * @link   http://stackoverflow.com/a/6718472
	 */
	public static function getIP() {

		foreach ( array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key ) {

			if ( TRUE === array_key_exists( $key, $_SERVER ) ) {

				foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) {

					if ( FALSE !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {

						return $ip;
					}
				}
			}
		}
	}

	/**
	 * Returns v4 compliant UUID.
	 *
	 * @since 0.8
	 *
	 * @link http://www.php.net/manual/en/function.uniqid.php#94959
	 * @link http://stackoverflow.com/a/15875555
	 *
	 * @return string
	 */
	public static function getUUID() {

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {

			$data    = openssl_random_pseudo_bytes(16);

			if ( FALSE !== $data ) {

				$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 ); // set version to 0010
				$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10

				return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
			}

		}

		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff )
		);

	}

	/**
	 * Convert a value within one range to a value within another range, maintaining ratio.
	 *
	 * Converted Python script from:
	 * @link http://stackoverflow.com/a/15537393
	 *
	 * @since 8.1
	 *
	 * @param float $x    Original value.
	 * @param float $oMin Old minimum.
	 * @param float $oMax Old maximum.
	 * @param float $nMin New minimum.
	 * @param float $nMax New maximum.
	 *
	 * @return bool|float Return false on failure, or return new value within new range.
	 */
	public static function remapRange( $x, $oMin, $oMax, $nMin, $nMax ) {

		#range check
		if ( $oMin == $oMax ) {

			return FALSE;
		}

		if ( $nMin == $nMax ) {

			return FALSE;
		}

		#check reversed input range
		$reverseInput = FALSE;
		$oldMin       = min( $oMin, $oMax );
		$oldMax       = max( $oMin, $oMax );

		if ( ! $oldMin == $oMin ) {

			$reverseInput = TRUE;
		}

		#check reversed output range
		$reverseOutput = FALSE;
		$newMin        = min( $nMin, $nMax );
		$newMax        = max( $nMin, $nMax );

		if ( ! $newMin == $nMin ) {

			$reverseOutput = TRUE;
		}

		$portion = ( $x - $oldMin ) * ( $newMax - $newMin ) / ( $oldMax - $oldMin );

		if ( $reverseInput ) {

			$portion = ( $oldMax - $x ) * ( $newMax - $newMin ) / ( $oldMax - $oldMin );
		}

		$result = $portion + $newMin;

		if ( $reverseOutput ) {

			$result = $newMax - $portion;
		}

		return $result;
	}

}
