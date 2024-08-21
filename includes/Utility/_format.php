<?php

namespace Connections_Directory\Utility;

use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class _format
 *
 * @package Connections_Directory\Utility
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _format {

	/**
	 * Uses WordPress function to sanitize the input string.
	 *
	 * Limits the output to alphanumeric characters, underscore (_) and dash (-).
	 * Whitespace becomes a dash.
	 *
	 * NOTE: Used in several custom addons; can not remove.
	 *
	 * @since unknown
	 * @deprecated 9.11
	 *
	 * @param string $string The string to convert to kebab case.
	 *
	 * @return string
	 */
	public static function sanitizeStringStrong( $string ) {

		_deprecated_function( __METHOD__, '9.11', '_string::toKebabCase()' );

		return _string::toKebabCase( $string );
	}

	/**
	 * Converts the following strings: yes/no; true/false and 0/1 to boolean values.
	 * If the supplied string does not match one of those values the method will return NULL.
	 *
	 * @since unknown
	 *
	 * @param string|int|bool|null $value The value to convert to a boolean value.
	 *
	 * @return bool
	 */
	public static function toBoolean( &$value ) {

		// Already a bool, return it.
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_null( $value ) ) {
			return false;
		}

		$value = filter_var( strtolower( $value ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		if ( is_null( $value ) ) {

			$value = false;
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
	public static function maybeJSONdecode( $value, $array = true ) {

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
	 * @since  8.1
	 * @deprecated 9.11
	 * @see maybe_hash_hex_color()
	 *
	 * @param string $color
	 *
	 * @return string
	 */
	public static function maybeHashHEXColor( $color ) {

		_deprecated_function( __METHOD__, '9.11', 'maybe_hash_hex_color()' );

		return maybe_hash_hex_color( $color );
	}

	/**
	 * Create excerpt from the supplied string.
	 *
	 * @since  8.1.5
	 *
	 * @deprecated 8.2.9 Use {@see _string::excerpt()} instead.
	 * @see _string::excerpt()
	 *
	 * @param string $string String to create the excerpt from.
	 * @param array  $atts {
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
	 * Convert number of seconds to human-readable string format.
	 *
	 * The `$limit` argument sets the number of time interval periods to display.
	 * Example, (int) 12345 will display "3 hours 25 minutes 45 seconds".
	 * Setting `$limit` to (int) 2 will display "3 hours 25 minutes".
	 * Displaying only the two largest time interval periods.
	 *
	 * @since 10.4.34
	 *
	 * @link  https://stackoverflow.com/a/19680778/5351316
	 * @link  https://stackoverflow.com/a/50281019/5351316
	 * @link  https://stackoverflow.com/a/59901330/5351316
	 *
	 * @param int $seconds Number of seconds.
	 * @param int $limit   The number of time period intervals to limit the return string.
	 *
	 * @return string|null
	 */
	public static function secondsToHuman( $seconds = 0, $limit = 0 ) {

		$dateFrom = new \DateTime( '@0' );
		$dateTo   = new \DateTime( "@$seconds" );
		$interval = $dateFrom->diff( $dateTo );
		$fragment = array();
		$limit    = filter_var( $limit, FILTER_VALIDATE_INT, array( 'options' => array( 'default' => 6, 'min_range' => 1, 'max_range' => 6 ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

		$properties = array(
			'y' => 'year',
			'm' => 'month',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);

		$pluralize = function ( $count, $text ) {
			return $count . ( 1 == $count ? " $text" : " {$text}s" );
		};

		foreach ( $properties as $period => $string ) {

			if ( $limit && count( $fragment ) >= $limit ) {
				break;
			}

			if ( $interval->{$period} >= 1 ) {
				$fragment[] = $pluralize( $interval->{$period}, $string );
			}
		}

		return ! empty( $fragment ) ? implode( ' ', $fragment ) . ( $interval->invert ? ' ago' : '' ) : null;
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
			'd' => 'dd',
			'D' => 'D',
			'j' => 'd',
			'l' => 'DD',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			// Week.
			'W' => '',
			// Month.
			'F' => 'MM',
			'm' => 'mm',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			// Year.
			'L' => '',
			'o' => '',
			'Y' => 'yy',
			'y' => 'y',
			// Time.
			'a' => 'tt',
			'A' => 'TT',
			'B' => '',
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'c',
		);

		$format   = '';
		$escaping = false;

		for ( $i = 0; $i < strlen( $string ); $i++ ) {

			$char = $string[ $i ];

			// PHP date format escaping character.
			if ( '\\' === $char ) {

				$i++;

				if ( $escaping ) {

					$format .= $string[ $i ];

				} else {

					$format .= '\'' . $string[ $i ];
				}

				$escaping = true;

			} else {

				if ( $escaping ) {

					$format  .= '\'';
					$escaping = false;
				}

				if ( isset( $map[ $char ] ) ) {

					$format .= $map[ $char ];

				} else {

					$format .= $char;
				}
			}
		}

		// If the escaping is still open, make sure to close it. So formatting like this will work: `H\h i\m\i\n`.
		if ( $escaping ) {
			$format .= '\'';
		}

		return $format;
	}
}
