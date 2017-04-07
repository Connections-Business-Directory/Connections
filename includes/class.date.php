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
	 * Returns an associative array containing days 1 through 31
	 *
	 * @var array
	 */
	public $days = array(
		NULL => 'Day',
		'01' => '1st',
		'02' => '2nd',
		'03' => '3rd',
		'04' => '4th',
		'05' => '5th',
		'06' => '6th',
		'07' => '7th',
		'08' => '8th',
		'09' => '9th',
		'10' => '10th',
		'11' => '11th',
		'12' => '12th',
		'13' => '13th',
		'14' => '14th',
		'15' => '15th',
		'16' => '16th',
		'17' => '17th',
		'18' => '18th',
		'19' => '19th',
		'20' => '20th',
		'21' => '21st',
		'22' => '22nd',
		'23' => '23rd',
		'24' => '24th',
		'25' => '25th',
		'26' => '26th',
		'27' => '27th',
		'28' => '28th',
		'29' => '29th',
		'30' => '30th',
		'31' => '31st',
	);

	/**
	 * Returns an associative array of months Jan through Dec
	 *
	 * @var array
	 */
	public $months = array(
		NULL => 'Month',
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December',
	);

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

	public function getMonth( $data ) {

		if ( $data != NULL ) {
			$month = date( "m", strtotime( $data ) );
		} else {
			$month = NULL;
		}

		return $month;
	}

	public function getDay( $data ) {

		if ( $data != NULL ) {
			$day = date( "d", strtotime( $data ) );
		} else {
			$day = NULL;
		}

		return $day;

	}

	/**
	 * Returns the current (or relative week's dates) week's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getWeekDates( $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}
		if ( empty( $weekStart ) ) {
			$weekStart = 'sunday';
		}

		// If current day is the same as the start of the week, advance the day by one so the proper week will eval.
		if ( strtolower( date( 'l', $relativeDate ) ) == $weekStart ) {
			$relativeDate = strtotime( '+1 day', $relativeDate );
		}

		$date['start'] = strtotime( 'last ' . $weekStart, $relativeDate );
		$date['end']   = strtotime( '-1 day next ' . $weekStart, $relativeDate );

		$i = $date['start'];
		while ( $i <= $date['end'] ) {
			$day                 = strtolower( date( 'l', $i ) );
			$date['day'][ $day ] = $i;
			$i                   = strtotime( '+ 1 day', $i );
		}

		if ( ! empty( $format ) ) {
			$date['start'] = date( $format, $date['start'] );
			$date['end']   = date( $format, $date['end'] );

			foreach ( $date['day'] as $key => $timestamp ) {
				$date['day'][ $key ] = date( $format, $timestamp );
			}
		}

		return $date;
	}

	/**
	 * Returns the previous (or relative week's dates) week's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getPreviousWeekDates( $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}

		$relativeDate = strtotime( '-1 weeks', $relativeDate );

		return $this->getWeekDates( $format, $weekStart, $relativeDate );
	}

	/**
	 * Returns the next (or relative week's dates) week's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getNextWeekDates( $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}

		$relativeDate = strtotime( '+1 weeks', $relativeDate );

		return $this->getWeekDates( $format, $weekStart, $relativeDate );
	}

	/**
	 * Returns X number of previous (or relative week's dates) week's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getXPreviousWeekDates( $x, $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}
		if ( empty( $weekStart ) ) {
			$weekStart = 'sunday';
		}

		$i = 1;
		while ( $i <= $x ) {
			$previousWeekX  = strtotime( '-' . $i . ' weeks', $relativeDate );
			$week['week'][] = $this->getWeekDates( $format, $weekStart, $previousWeekX );

			$i ++;
		}

		$week['start'] = $week['week'][ $i - 2 ]['start'];
		$week['end']   = $week['week'][0]['end'];

		return $week;
	}

	/**
	 * Returns X number of next (or relative week's dates) week's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getXNextWeekDates( $x, $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}
		if ( empty( $weekStart ) ) {
			$weekStart = 'sunday';
		}

		$i = 1;
		while ( $i <= $x ) {
			$previousWeekX  = strtotime( '+' . $i . ' weeks', $relativeDate );
			$week['week'][] = $this->getWeekDates( $format, $weekStart, $previousWeekX );

			$i ++;
		}

		$week['start'] = $week['week'][0]['start'];
		$week['end']   = $week['week'][ $i - 2 ]['end'];

		return $week;
	}

	/**
	 * Returns the current (or relative month's dates) month's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getMonthDates( $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}

		$date['start'] = mktime( 0, 0, 0, date( 'n', $relativeDate ), 1, date( 'Y', $relativeDate ) );

		// 0 for day will cause mktime to eval to the last day of the previous month.
		$date['end'] = mktime( 0, 0, 0, date( 'n', $relativeDate ) + 1, 0, date( 'Y', $relativeDate ) );

		// Set the offset if the $weekStart is defined
		if ( ! empty( $weekStart ) ) {
			$offset         = date( 'w', strtotime( $weekStart ) );
			$date['offset'] = $offset;
		}

		// Set the while loop iteration variables.
		$i = $date['start'];
		$j = 0;

		while ( $i <= $date['end'] ) {
			$day                               = strtolower( date( 'l', $i ) );
			$date['week'][ $j ]['day'][ $day ] = $i;

			// If the $offset is set, calculate the new day of week and next day values numerically.
			// date('w') returns 0 = Sunday, 1 = Monday, 2 = Tuesday, 3 = Wednesday, 4 = Thursday, 5 = Friday, 6 = Saturday
			// The offset is calculated based on this and shifts the values based on the user supplied start of week.
			if ( isset( $offset ) ) {
				if ( $offset <= date( 'w', $i ) ) {
					$offsetWeekStart = date( 'w', $i ) - $offset;
				} else {
					$offsetWeekStart = ( date( 'w', $i ) + 7 ) - $offset;
				}

				if ( $offset <= date( 'w', strtotime( '+ 1 day', $i ) ) ) {
					$offsetWeekStartNextDay = date( 'w', strtotime( '+ 1 day', $i ) ) - $offset;
				} else {
					$offsetWeekStartNextDay = ( date( 'w', strtotime( '+ 1 day', $i ) ) + 7 ) - $offset;
				}
			}

			// If the current day of the week (numerically) is greater than or equal to the next day of the week [numerically],
			// advance the week number count by one. The else takes into account if the user supplied a preferred start of week.
			if ( ! isset( $offset ) ) {
				if ( date( 'w', $i ) >= date( 'w', strtotime( '+ 1 day', $i ) ) ) {
					$j ++;
				}
			} else {
				if ( $offsetWeekStart >= $offsetWeekStartNextDay ) {
					$j ++;
				}
			}

			// Move to the next day and repeat.
			$i = strtotime( '+ 1 day', $i );
		}

		// Format all date timestamps if format is supplied.
		if ( ! empty( $format ) ) {
			$date['start'] = date( $format, $date['start'] );
			$date['end']   = date( $format, $date['end'] );

			// Walk through the week/day arrays and format.
			foreach ( $date['week'] as $weekKey => $week ) {
				foreach ( $week['day'] as $dayKey => $timestamp ) {
					$date['week'][ $weekKey ]['day'][ $dayKey ] = date( $format, $timestamp );
				}
			}
		}

		return $date;
	}

	/**
	 * Returns the previous (or relative month's dates) month's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getPreviousMonthDates( $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}

		$relativeDate = mktime( 0, 0, 0, date( 'n', $relativeDate ) -1, 1, date( 'Y', $relativeDate ) );

		return $this->getMonthDates( $format, $weekStart, $relativeDate );
	}

	/**
	 * Returns the next (or relative month's dates) month's dates as UNIX timestamp or formatted string.
	 *
	 * $format can accept all formatting stings supported by date()
	 * $weekStart accepts sunday, monday, tuesday, wednesday, thursday, friday, saturday
	 * $relativeDate accepts UNIX timestamp
	 *
	 * @param         unknown : string $format
	 * @param         unknown : string $weekStart
	 * @param integer $relativeDate
	 *
	 * @return array
	 */
	public function getNextMonthDates( $format = NULL, $weekStart = NULL, $relativeDate = NULL ) {

		if ( empty( $relativeDate ) ) {
			$relativeDate = time();
		}

		$relativeDate = mktime( 0, 0, 0, date( 'n', $relativeDate ) + 1, 1, date( 'Y', $relativeDate ) );

		return $this->getMonthDates( $format, $weekStart, $relativeDate );
	}

	/**
	 * Create a regex used to parse the supplied datetime format.
	 *
	 * @access public
	 * @since  8.6.4
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
				if ( $parsed[ $keys[ $char ][0] ] ) {

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
