<?php

/**
 * Class cnCoordinates
 *
 * @since 8.6
 */
final class cnCoordinates {

	/**
	 * @since 8.6
	 * @var string
	 */
	private $latitude;

	/**
	 * @since 8.6
	 * @var string
	 */
	private $longitude;

	/**
	 * @access public
	 * @since  8.6
	 *
	 * @param float $latitude
	 * @param float $longitude
	 */
	public function __construct( $latitude, $longitude ) {

		$this->setLatitude( $latitude );
		$this->setLongitude( $longitude );
	}

	/**
	 * Returns the latitude.
	 *
	 * NOTE: Returns as formatted string for backward compatibility.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return null|string
	 */
	public function getLatitude() {

		return (float) $this->latitude ? $this->latitude : NULL;
	}

	/**
	 * @access public
	 * @since  8.6
	 *
	 * @param float $latitude
	 */
	public function setLatitude( $latitude ) {

		$this->latitude = number_format( (float) $latitude, 12 );
	}

	/**
	 * Returns the longitude.
	 *
	 * NOTE: Returns as formatted string for backward compatibility.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return null|string
	 */
	public function getLongitude() {

		return (float) $this->longitude ? $this->longitude : NULL;
	}

	/**
	 * @access public
	 * @since  8.6
	 *
	 * @param $longitude
	 */
	public function setLongitude( $longitude ) {

		$this->longitude = number_format( (float) $longitude, 12 );
	}
}
