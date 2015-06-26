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
		'12' => 'December'
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

}
