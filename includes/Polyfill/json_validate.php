<?php
/**
 * Polyfill for the `json_validate()` function introduced in PHP 8.3.
 *
 * @since 10.4.33
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Polyfill
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

if ( ! function_exists( 'json_validate' ) ) :
	/**
	 * Whether the supplied string is valid JSON.
	 *
	 * @link https://stackoverflow.com/a/45241792/5351316
	 * @link https://wiki.php.net/rfc/json_validate
	 *
	 * @since 10.4.33
	 *
	 * @param string $value The string to validate.
	 *
	 * @return bool
	 */
	function json_validate( $value ) {

		// Numeric strings are always valid JSON.
		if ( is_numeric( $value ) ) {
			return true;
		}

		// A non-string value can never be a JSON string.
		if ( ! is_string( $value ) ) {
			return false;
		}

		// Any non-numeric JSON string must be longer than 2 characters.
		if ( strlen( $value ) < 2 ) {
			return false;
		}

		// "null", "true" and "false" are valid JSON strings.
		if ( in_array( $value, array( 'null', 'true', 'false' ), true ) ) {
			return true;
		}

		// Any other JSON string has to be wrapped in {}, [] or "".
		if ( '{' != $value[0] && '[' != $value[0] && '"' != $value[0] ) {
			return false;
		}

		// Verify that the trailing character matches the first character.
		$last_char = $value[ strlen( $value ) - 1 ];

		if ( '{' == $value[0] && '}' != $last_char ) {
			return false;
		}

		if ( '[' == $value[0] && ']' != $last_char ) {
			return false;
		}

		if ( '"' == $value[0] && '"' != $last_char ) {
			return false;
		}

		// See if the string contents are valid JSON.
		return null !== json_decode( $value );
	}
endif;
