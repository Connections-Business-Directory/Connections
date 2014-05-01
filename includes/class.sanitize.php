<?php

/**
 * Sanitation.
 *
 * Handles the sanitation of input data.
 *
 * @package     Connections
 * @subpackage  Sanitation
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnSanitize {

	/**
	 * Sanitizes text inputs
	 *
	 * Sanitizes string based on the the string type.
	 *
	 * @access public
	 * @since 0.8
	 * @param  string $type   Type of string to validate.
	 * @param  string $string String to be sanitized.
	 *
	 * @return string Sanitized text.
	 */
	public static function string( $type, $string ) {

		switch ( $type ) {

			case 'text':

				$string = sanitize_text_field( $string );
				break;

			case 'textarea':

				$string = esc_textarea( $string );
				break;

			case 'quicktag':

				$string = self::quicktag( $string );
				break;

			case 'html':

				$string = self::html( $string );
				break;

			case 'url':

				$string = esc_url_raw( $string );
				break;

			case 'email':

				$string = sanitize_email( $string );
				break;

			case 'integer':

				$string = self::integer( $string );
				break;

			case 'currency':

				$string = self::currency( $string );
				break;

			case 'color':

				$string = self::hex_color( $string );
				break;

			// Default should be unnecessary, but provided as a fallback anyway.
			default:
				$string = sanitize_text_field( $string );
		}

		return $string;
	}

	/**
	 * Check the supplied value against an array of options.
	 * If the value exists as a key in the options array,
	 * it is returned, if it is not, the first key in the
	 * options array is returned instead. This is to provide
	 * a default value.
	 *
	 * This method is used to sanitize radio groups and selects.
	 *
	 * @access public
	 * @since 0.8
	 * @param  mixed $value
	 * @param  array $options An associtive array of options.
	 * @param  mixed $default [optional] The value to return if value does not exist in the options array.
	 *
	 * @return mixed
	 */
	public static function option( $value, $options, $default = NULL ) {

		if ( array_key_exists( $value, $options ) ) {

			return $value;

		} else {

			if ( ! is_null( $default ) ) {

				return $default;

			} else {

				$key = array_keys( $options );

				return $key[0];
			}

		}

	}

	/**
	 * Check the supplied values against the supplied options.
	 *
	 * This method is used to sanitize checkbox groups.
	 *
	 * @todo Implement $defaults.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $values   An index array of values.
	 * @param  array  $options  An associative array of the valid options.
	 * @param  array  $defaults [optional] The values to return if no values exists in the options array.
	 *
	 * @return array
	 */
	public static function options( $values, $options, $defaults = array() ) {

		if ( empty( $values ) ) return;

		// Let do a bit of array gymnastics...
		// array_flip $values so the values are the keys.
		// Use array_intersect_key to return only the values in $values from $options.
		// Finally, use array_keys to return the results from array_intersect_key.
		// The result will be only the valid $values in $options.
		return array_keys( array_intersect_key( $options, array_flip( $values ) ) );
	}

	/**
	 * Sanitizes checkbox input.
	 *
	 * WordPress core evaluates checkboxes as '1' or '0';
	 * to be consistant with core return '1' or '0'.
	 *
	 * @access public
	 * @since 0.8
	 * @param  string $input Value data to be sanitized.
	 *
	 * @return string
	 */
	public static function checkbox( $value ) {

		return $value ? '1' : '0';
	}

	/**
	 * Sanitizes the quicktag textarea input.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_kses_post()
	 * @uses force_balance_tags
	 * @param  string $string
	 *
	 * @return string
	 */
	public static function html( $string ) {

		return wp_kses_post( force_balance_tags( $string ) );
	}

	/**
	 * Sanitizes the quicktag textarea input.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_kses_data()
	 * @uses force_balance_tags
	 * @param  string $string
	 *
	 * @return string
	 */
	public static function quicktag( $string ) {

		return wp_kses_data( force_balance_tags( $string ) );
	}

	/**
	 * Return integer.
	 *
	 * @access public
	 * @since 0.8
	 * @param  int $value
	 *
	 * @return int
	 */
	public static function integer( $value ) {

		return intval( $value );
	}

	/**
	 * Sanitizes currency input.
	 *
	 * Returns the currency value of the $input.
	 *
	 * @access public
	 * @since 0.8
	 * @param  string $input Input data to be sanitized.
	 *
	 * @return string Returns the $valid string after sanitization.
	 */
	public function currency( $input ) {

		if ( is_numeric( $input ) ) {

			return $input ? number_format( $input, 2 ) : '';

		} else {

			return '';
		}

	}

	/**
	 * Validates a hex color.
	 *
	 * Returns either '', a 3 or 6 digit hex color (with #), or null.
	 * This function is borrowed directly from the class_wp_customize_manager.php
	 * file in WordPress core.
	 *
	 * @access public
	 * @since 0.8
	 * @param  string $color
	 *
	 * @return mixed  string | null
	 */
	public function hex_color( $color ) {

		// Returns empty string if input was an empty string.
		if ( '' === $color ) {

			return '';
		}

		// Returns 3 or 6 hex digits, or the empty string.
		if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {

			return $color;
		}

		return null;
	}
}
