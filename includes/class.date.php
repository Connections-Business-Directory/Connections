<?php

/**
 * Class for working with a dates and date ranges.
 *
 * @package     Connections
 * @subpackage  Dates
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnDate
 */
class cnDate {

	/**
	 * Date format characters and their name and regex structure.
	 *
	 * @access public
	 * @since  8.6.4
	 *
	 * @var array
	 */
	protected static $keys = array(
		'Y' => array( 'year', '\d{4}' ),            // Year with 4 Digits
		'y' => array( 'year', '\d{2}' ),            // Year with 2 Digits
		'm' => array( 'month', '\d{2}' ),           // Month with leading 0
		'n' => array( 'month', '\d{1,2}' ),         // Month without the leading 0
		'M' => array( 'month', '[A-Z][a-z]{2}' ),   // Month ABBR 3 letters
		'F' => array( 'month', '[A-Z][a-z]{2,8}' ), // Month Name
		'd' => array( 'day', '\d{2}' ),             // Day with leading 0
		'j' => array( 'day', '\d{1,2}' ),           // Day without leading 0
		'D' => array( 'day', '[A-Z][a-z]{2}' ),     // Day ABBR 3 Letters
		'l' => array( 'day', '[A-Z][a-z]{5,8}' ),   // Day Name
		'h' => array( 'hour', '\d{2}' ),            // Hour 12h formatted, with leading 0
		'H' => array( 'hour', '\d{2}' ),            // Hour 24h formatted, with leading 0
		'g' => array( 'hour', '\d{1,2}' ),          // Hour 12h formatted, without leading 0
		'G' => array( 'hour', '\d{1,2}' ),          // Hour 24h formatted, without leading 0
		'i' => array( 'minute', '\d{2}' ),          // Minutes with leading 0
		's' => array( 'second', '\d{2}' ),          // Seconds with leading 0
	    'u' => array( 'hour', '\d{1,6}' ),          // Microseconds
	    'a' => array( 'meridiem', '[ap]m' ),        // Lowercase ante meridiem and Post meridiem
		'A' => array( 'meridiem', '[AP]M' ),        // Uppercase ante meridiem and Post meridiem
	);

	/**
	 * Create a regex used to parse the supplied datetime format.
	 *
	 * @access public
	 * @since  8.6.4
	 * @static
	 *
	 * @param string $format The datetime format.
	 *
	 * @return string
	 */
	private static function getFormatRegex( $format ) {

		$keys = self::$keys;

		// Convert format string to regex.
		$regex = '';
		$chars = str_split( $format );

		foreach ( $chars as $n => $char ) {

			$lastChar    = isset( $chars[ $n - 1 ] ) ? $chars[ $n - 1 ] : '';
			$skipCurrent = '\\' == $lastChar;

			if ( ! $skipCurrent && isset( $keys[ $char ] ) ) {

				$regex .= '(?P<' . $keys[ $char ][0] . '>' . $keys[ $char ][1] . ')';

			} elseif ( '\\' == $char || '!' == $char ) {

				/*
				 * No need to add the date format escaping character to the regex since it should not exist in the
				 * supplied datetime string. Including it would cause the preg_match to fail.
				 */
				//$regex .= $char;

			} else {

				$regex .= preg_quote( $char );
			}
		}

		return '#^' . $regex . '$#';
	}

	/**
	 * PHP 5.2 does not have a version of @see date_parse_from_format(), this is a mostly PHP 5.2 compatible version.
	 *
	 * @link http://stackoverflow.com/a/14196482/5351316
	 *
	 * @access public
	 * @since  8.6.4
	 * @static
	 *
	 * @param string $format The datetime format.
	 * @param string $date   The datetime string to parse.
	 *
	 * @return array
	 */
	public static function parseFromFormat( $format, $date ) {

		/** Setup the default values to be returned, matching @see date_parse_from_format() */
		$dt = array(
			'year'          => FALSE,
			'month'         => FALSE,
			'day'           => FALSE,
			'hour'          => FALSE,
			'minute'        => FALSE,
			'second'        => FALSE,
			'fraction'      => FALSE,
			'warning_count' => 0,
			'warnings'      => array(),
			'error_count'   => 0,
			'errors'        => array(),
			'is_localtime'  => FALSE,
			'zone_type'     => 0,
			'zone'          => 0,
			'is_dst'        => '',
		);

		// Now try to match it.
		if ( preg_match( self::getFormatRegex( $format ), $date, $matches ) ) {

			/**
			 * @var int|string $k
			 * @var string     $v
			 */
			foreach ( $matches as $k => $v ) {

				// Remove unwanted indexes from resulting preg_match.
				if ( is_int( $k ) ) {

					unset( $matches[ $k ] );
				}

				// Year, month, day, hour, minute, second and fraction should be coerced from string to int.
				if ( in_array( $k, array( 'year', 'month', 'day', 'hour', 'minute', 'second', 'fraction' ) )
				     && is_numeric( $v ) ) {

					$matches[ $k ] = (int) $v;

				} elseif ( 'month' === $k ) {

					$parsed = date_parse( $v );
					$matches[ $k ] = (int) $parsed['month'];

				} elseif ( 'day' === $k ) {

					$parsed = date_parse( $v );
					$matches[ $k ] = (int) $parsed['day'];
				}
			}

		} else {

			$dt['error_count'] = 1;
			$dt['errors'][]    = 'Invalid date supplied.'; // @todo match error string from date_parse_from_format()
		}

		return wp_parse_args( $matches, $dt );
	}

	/**
	 * PHP 5.2 does not have a version of @see DateTime::createFromFormat(), this is a mostly PHP 5.2 compatible version.
	 *
	 * @link http://bordoni.me/date_parse_from_format-php-5-2/
	 *
	 * @access public
	 * @since  8.6.4
	 * @static
	 *
	 * @param  string $format  The datetime format.
	 * @param  string $date    The datetime string to parse.
	 *
	 * @return false|DateTime  Instance of DateTime, false on failure.
	 */
	public static function createFromFormat( $format, $date ) {

		$keys  = self::$keys;
		$pos   = strpos( $format, '!' );
		$chars = str_split( $format );

		// Setup default datetime values based on time now or Unix epoch based on if `!` if present in $format.
		if ( FALSE !== $pos ) {

			$datetime = array(
				'year'          => '1970',
				'month'         => '01',
				'day'           => '01',
				'hour'          => '00',
				'minute'        => '00',
				'second'        => '00',
				'fraction'      => '000000',
			);

		} else {

			/** @link http://stackoverflow.com/a/38334226/5351316 */
			list( $usec, $sec ) = explode( ' ', microtime() );

			$datetime = array(
				'year'          => date( 'Y', $sec ),
				'month'         => date( 'm', $sec ),
				'day'           => date( 'd', $sec ),
				'hour'          => date( 'H', $sec ),
				'minute'        => date( 'i', $sec ),
				'second'        => date( 's', $sec ),
				'fraction'      => substr( $usec, 2, 6 ),
			);
		}

		$parsed = self::parseFromFormat( $format, $date );

		foreach ( $chars as $n => $char ) {

			$lastChar    = isset( $chars[ $n - 1 ] ) ? $chars[ $n - 1 ] : '';
			$skipCurrent = '\\' == $lastChar;

			if ( ! $skipCurrent && isset( $keys[ $char ] ) ) {

				// Existing value exists in supplied parsed date.
				if ( array_key_exists( $keys[ $char ][0], $parsed ) &&
				     FALSE !== $parsed[ $keys[ $char ][0] ]
				) {

					/*
					 * Replace default datetime interval with the parsed datetime interval only if
					 * an `!` was found within the supplied $format and its position is
					 * greater than the current $format character position.
					 */
					if ( ! ( FALSE !== $pos && $pos > $n ) ) {

						$datetime[ $keys[ $char ][0] ] = $parsed[ $keys[ $char ][0] ];
					}
				}
			}
		}

		// If meridiem is set add/subtract 12 to the hour based on AM/PM so strtotime() will create the correct time.
		if ( array_key_exists( 'meridiem', $parsed ) &&
		     'PM' == strtoupper( $parsed['meridiem'] ) &&
		     12 > $datetime['hour']
		) {

			$datetime['hour'] = 12 + $datetime['hour'];

		} elseif ( array_key_exists( 'meridiem', $parsed ) &&
		           'AM' == strtoupper( $parsed['meridiem'] ) &&
		           12 <= $datetime['hour']
		) {

			$datetime['hour'] = $datetime['hour'] - 12;
		}

		// Ensure the datetime integers are correctly padded with leading zeros.
		$datetime['month']  = str_pad( $datetime['month'], 2, '0', STR_PAD_LEFT );
		$datetime['day']    = str_pad( $datetime['day'], 2, '0', STR_PAD_LEFT );
		$datetime['hour']   = str_pad( $datetime['hour'], 2, '0', STR_PAD_LEFT );
		$datetime['minute'] = str_pad( $datetime['minute'], 2, '0', STR_PAD_LEFT );
		$datetime['second'] = str_pad( $datetime['second'], 2, '0', STR_PAD_LEFT );

		// Parse the $datetime into a string which can be parsed by DateTime().
		$formatted = strtr( 'year-month-day hour:minute:second.fraction', $datetime );

		// Sanity check to make sure the datetime is valid.
		if ( ! strtotime( $formatted ) ) {

			return FALSE;
		}

		// Return a new DateTime instance.
		return new DateTime( $formatted );
	}
}
