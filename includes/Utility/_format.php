<?php

namespace Connections_Directory\Utility;

use cnSanitize;

/**
 * Class _format
 *
 * @package Connections_Directory\Utility
 */
final class _format {

	/**
	 * Sanitize the input string. HTML tags can be permitted.
	 * The permitted tags can be supplied in an array.
	 *
	 * @TODO: Finish the code needed to support the $permittedTags array.
	 *
	 * @param string $string
	 * @param bool $allowHTML [optional]
	 * @param array $permittedTags [optional]
	 * @return string
	 */
	public function sanitizeString( $string, $allowHTML = FALSE, $permittedTags = array() ) {
		// Strip all tags except the permitted.
		if ( ! $allowHTML ) {
			// Ensure all tags are closed. Uses WordPress method balanceTags().
			$balancedText = balanceTags( $string, TRUE );

			$strippedText = strip_tags( $balancedText );

			// Strip all script and style tags.
			$strippedText = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $strippedText );

			// Escape text using the WordPress method and then strip slashes.
			$escapedText = stripslashes( esc_attr( $strippedText ) );

			// Remove line breaks and trim white space.
			$escapedText = preg_replace( '/[\r\n\t ]+/', ' ', $escapedText );

			return trim( $escapedText );
		} else {
			// Strip all script and style tags.
			$strippedText = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
			$strippedText = preg_replace( '/&lt;(script|style).*?&gt;.*?&lt;\/\\1&gt;/si', '', stripslashes( $strippedText ) );

			/*
			 * Use WordPress method make_clickable() to make links clickable and
			 * use kses for filtering.
			 *
			 * http://ottopress.com/2010/wp-quickie-kses/
			 */
			return wptexturize( wpautop( make_clickable( wp_kses_post( $strippedText ) ) ) );
		}

	}

	/**
	 * Uses WordPress function to sanitize the input string.
	 *
	 * Limits the output to alphanumeric characters, underscore (_) and dash (-).
	 * Whitespace becomes a dash.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function sanitizeStringStrong( $string ) {
		$string = str_ireplace( '%', '-', $string ); // Added this because sanitize_title_with_dashes will still allow % to passthru.
		$string = sanitize_title_with_dashes( $string );
		return $string;
	}

	/**
	 * Strips all numeric characters from the supplied string and returns the string.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function stripNonNumeric( $string ) {

		return preg_replace( '/[^0-9]/', '', $string );
	}

	/**
	 * Converts the following strings: yes/no; true/false and 0/1 to boolean values.
	 * If the supplied string does not match one of those values the method will return NULL.
	 *
	 * @access public
	 * @since  unknown
	 * @static
	 *
	 * @param  string|int|bool $value
	 *
	 * @return bool
	 */
	public static function toBoolean( &$value ) {

		// Already a bool, return it.
		if ( is_bool( $value ) ) return $value;

		$value = filter_var( strtolower( $value ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		if ( is_null( $value ) ) {

			$value = FALSE;
		}

		return $value;
	}

	/**
	 * Return localized Yes or No.
	 *
	 * @author Alex Rabe (http://alexrabe.de/)
	 * @since  0.7.1.6
	 *
	 * @param  bool $bool
	 *
	 * @return string Returns 'Yes' | 'No'
	 */
	public static function toYesNo( $bool ) {

		if ( $bool ) {

			return __( 'Yes', 'connections' );

		} else {

			return __( 'No', 'connections' );
		}
	}

	/**
	 * JSON encode objects and arrays.
	 *
	 * @since 0.8
	 * @deprecated 9.11
	 *
	 * @param mixed $value The value to maybe json_encode.
	 *
	 * @return mixed
	 */
	public static function maybeJSONencode( $value ) {

		_deprecated_function( __METHOD__, '9.11', '_::maybeJSONencode()' );

		return _::maybeJSONencode( $value );
	}

	/**
	 * Maybe json_decode the supplied value.
	 *
	 * @since 0.8
	 * @deprecated 9.11
	 *
	 * @param mixed   $value The value to decode.
	 * @param boolean $array [optional] Whether or not the JSON decoded value should an object or an associative array.
	 *
	 * @return mixed
	 */
	public static function maybeJSONdecode( $value, $array = TRUE ) {

		_deprecated_function( __METHOD__, '9.11', '_::maybeJSONdecode()' );

		return _::maybeJSONdecode( $value, $array );
	}

	/**
	 * Ensures that any hex color is properly hashed.
	 * Otherwise, returns value unaltered.
	 *
	 * This function is borrowed from the class_wp_customize_manager.php
	 * file in WordPress core.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $color
	 *
	 * @return string
	 */
	public static function maybeHashHEXColor( $color ) {

		if ( $unhashed = cnSanitize::hexColorNoHash( $color ) ) {

			return '#' . $unhashed;
		}

		return $color;
	}

	/**
	 * Create excerpt from the supplied string.
	 *
	 * @since  8.1.5
	 *
	 * @deprecated 8.2.9 Use {@see _string::excerpt()} instead.
	 * @see _string::excerpt()
	 *
	 * @param  string  $string String to create the excerpt from.
	 * @param  array   $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type int    $length       The length, number of words, of the excerpt to create.
	 *                                If set to `p` the excerpt will be the first paragraph, no word limit.
	 *                                Default: 55.
	 *     @type string $more         The string appended to the end of the excerpt when $length is exceeded.
	 *                                Default: &hellip
	 *     @type array  $allowed_tags An array containing the permitted tags.
	 * }
	 *
	 * @return string
	 */
	public static function excerpt( $string, $atts = array() ) {

		_deprecated_function( __METHOD__, '8.2.9', '_string::excerpt()' );

		return _string::excerpt( $string, $atts );
	}

	/**
	 * Prepare the placeholders to be used in a IN query clause using @see wpdb::prepare().
	 *
	 * @access public
	 * @since  8.1.5
	 * @static
	 *
	 * @param array  $items The array of items to be used in the IN query clause.
	 * @param string $type  The type of placeholder to be used.
	 *                      Default: %s
	 *                      Accepted: %d, %f, %s
	 *
	 * @return string
	 */
	public static function prepareINPlaceholders( $items, $type = '%s' ) {

		$placeholders = array_fill( 0, count( $items ), $type );

		return implode( ', ', $placeholders );
	}

	/**
	 * Convert supplied string to camelCase.
	 *
	 * @since 8.5.19
	 * @deprecated 9.11
	 *
	 * @link http://stackoverflow.com/a/2792045/5351316
	 *
	 * @param string $string
	 * @param bool   $capitaliseInitial
	 *
	 * @return string
	 */
	public static function toCamelCase( $string, $capitaliseInitial = FALSE ) {

		_deprecated_function( __METHOD__, '9.11', '_string::toCamelCase()' );

		return _string::toCamelCase( $string, $capitaliseInitial );
	}

	/**
	 * Convert a PHP format string to a jQueryUI Datepicker/DateTimepicker compatible datetime format string.
	 *
	 * @access public
	 * @since  8.6.4
	 *
	 * @link http://stackoverflow.com/a/16725290/5351316
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function dateFormatPHPTojQueryUI( $string ) {

		$map = array(
			// PHP Date format character => jQueryUI Datepicker/DateTimepicker format character.
			// Day.
			'd' => 'dd', 'D' => 'D', 'j' => 'd', 'l' => 'DD', 'N' => '', 'S' => '', 'w' => '', 'z' => 'o',
			// Week.
			'W' => '',
			// Month.
			'F' => 'MM', 'm' => 'mm', 'M' => 'M', 'n' => 'm', 't' => '',
			// Year.
			'L' => '', 'o' => '', 'Y' => 'yy', 'y' => 'y',
			// Time.
			'a' => 'tt', 'A' => 'TT', 'B' => '',
			'g' => 'h', 'G' => 'H', 'h' => 'hh', 'H' => 'HH', 'i' => 'mm', 's' => 'ss', 'u' => 'c',
		);

		$format   = '';
		$escaping = FALSE;

		for ( $i = 0; $i < strlen( $string ); $i++ ) {

			$char = $string[ $i ];

			// PHP date format escaping character.
			if ( $char === '\\' ) {

				$i++;

				if ( $escaping ) {

					$format .= $string[ $i ];

				} else {

					$format .= '\'' . $string[ $i ];
				}

				$escaping = TRUE;

			} else {

				if ( $escaping ) {

					$format .= '\'';
					$escaping = FALSE;
				}

				if ( isset( $map[ $char ] ) ) {

					$format .= $map[ $char ];

				} else {

					$format .= $char;
				}
			}
		}

		//If the escaping is still open, make sure to close it. So formatting like this will work: `H\h i\m\i\n`.
		if ( $escaping ) $format .= '\'';

		return $format;
	}
}
