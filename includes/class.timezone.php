<?php

/**
 * Class cnTimezone
 */
class cnTimezone {

	/**
	 * @since 8.6.9
	 * @var int
	 */
	private $dstOffset = 0;

	/**
	 * @since 8.6.9
	 * @var int
	 */
	private $rawOffset = 0;

	/**
	 * Alias for @see cnTimezone::$timeZoneId
	 *
	 * @since 8.6.9
	 * @var string
	 */
	private $id   = '';

	/**
	 * @since 8.6.9
	 * @var string
	 */
	private $timeZoneId = '';

	/**
	 * Alias for @see cnTimezone::$timeZoneName
	 *
	 * @since 8.6.9
	 * @var string
	 */
	private $name = '';

	/**
	 * @since 8.6.9
	 * @var string
	 */
	private $timeZoneName = '';

	/**
	 * cnTimezone constructor.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @param object $data The JSON decoded response from the Google Timezone API.
	 */
	public function __construct( $data ) {

		if ( property_exists( $data, 'dstOffset' ) ) {

			$this->dstOffset = $data->dstOffset;
		}

		if ( property_exists( $data, 'rawOffset' ) ) {

			$this->rawOffset = $data->rawOffset;
		}

		if ( property_exists( $data, 'timeZoneId' ) ) {

			$this->id = $this->timeZoneId = $data->timeZoneId;
		}

		if ( property_exists( $data, 'timeZoneName' ) ) {

			$this->name = $this->timeZoneName = $data->timeZoneName;
		}
	}

	/**
	 * Return the private properties.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {

		return $this->{$name};
	}

	/**
	 * Returns the offset for daylight-savings time in seconds.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @return int
	 */
	public function get_dst_offset() {

		return $this->dstOffset;
	}

	/**
	 * Renders the offset for daylight-savings time in seconds.
	 *
	 * @access public
	 * @since  8.6.9
	 */
	public function dst_offset() {

		echo $this->get_dst_offset();
	}

	/**
	 * Returns the offset from UTC, in seconds.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @return int
	 */
	public function get_raw_offset() {

		return $this->dstOffset;
	}

	/**
	 * Renders the offset from UTC, in seconds.
	 *
	 * @access public
	 * @since  8.6.9
	 */
	public function raw_offset() {

		echo $this->get_raw_offset();
	}

	/**
	 * Returns a string containing the ID of the time zone, such as "America/Los_Angeles" or "Australia/Sydney".
	 * These IDs are defined by Unicode Common Locale Data Repository (CLDR) project.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @return string
	 */
	public function get_id() {

		return $this->id;
	}

	/**
	 * Renders the ID of the time zone.
	 *
	 * @access public
	 * @since  8.6.9
	 */
	public function id() {

		echo $this->get_id();
	}

	/**
	 * Returns a string containing the long form name of the time zone.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->name;
	}

	/**
	 * Renders the time zone name.
	 *
	 * @access public
	 * @since  8.6.9
	 */
	public function name() {

		echo $this->get_name();
	}

	/**
	 * Returns the local time of the time zone.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @return DateTime
	 */
	public function get_current_time() {

		$timestamp = current_time('timestamp', TRUE ) /*+ $this->get_raw_offset() + $this->get_dst_offset()*/;

		$datetime = new DateTime( "@$timestamp" );
		$datetime->setTimezone( new DateTimeZone( $this->get_id() ) );

		//return date( $format, $timestamp );
		return $datetime;
	}

	/**
	 * Renders the local time of the time zone with a given format.
	 *
	 * @access public
	 * @since  8.6.9
	 *
	 * @param string $format
	 */
	public function current_time( $format = 'c' ) {

		echo $this->get_current_time()->format( $format );
	}
}
