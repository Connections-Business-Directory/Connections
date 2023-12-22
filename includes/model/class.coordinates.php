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
	 * @param $latitude
	 * @param $longitude
	 *
	 * @return cnCoordinates|WP_Error
	 */
	public static function create( $latitude, $longitude ) {

		if ( is_null( $latitude ) || is_null( $longitude ) ) {

			return new WP_Error( 'null_values_supplied', 'Value can not be NULL.' );
		}

		if ( ! filter_var( $latitude, FILTER_VALIDATE_FLOAT ) ) {

			$latitude = filter_var( $latitude, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		}

		if ( ! filter_var( $longitude, FILTER_VALIDATE_FLOAT ) ) {

			$longitude = filter_var( $longitude, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		}

		if ( $latitude < - 90 || $latitude > 90 ) {
			return new WP_Error(
				'invalid_value',
				sprintf( 'Latitude should be between -90 and 90. Got: %s', $latitude )
			);
		}

		if ( $longitude < - 180 || $longitude > 180 ) {
			return new WP_Error(
				'invalid_value',
				sprintf( 'Longitude should be between -180 and 180. Got: %s', $longitude )
			);
		}

		return new self( $latitude, $longitude );
	}

	/**
	 * @param $value
	 *
	 * @return cnCoordinates|WP_Error
	 */
	public static function createFrom( $value ) {

		if ( is_string( $value ) ) {

			return self::fromString( $value );

		} elseif ( is_array( $value ) || $value instanceof \ArrayObject ) {

			return self::fromArray( $value );
		}

		return new WP_Error( 'invalid_value', 'Unable to create Coordinates' );
	}

	/**
	 * @param $value
	 *
	 * @return cnCoordinates|WP_Error
	 */
	public static function fromString( $value ) {

		list( $latitude, $longitude ) = explode( ',', $value );

		return self::create( $latitude, $longitude );
	}

	/**
	 * @param $value
	 *
	 * @return cnCoordinates|WP_Error
	 */
	public static function fromArray( $value ) {

		$keys = array(
			array( 0, 1, 2 ),
			array( 'lat', 'lng', 'alt' ),
			array( 'latitude', 'longitude', 'altitude' ),
		);

		foreach ( $keys as $key ) {

			if ( isset( $value[ $key[0] ] ) && isset( $value[ $key[1] ] ) ) {

				return self::create( $value[ $key[0] ], $value[ $key[1] ] );
			}
		}

		return new WP_Error( 'invalid_value', 'Unable to create Coordinates' );
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

		return (float) $this->latitude ? $this->latitude : null;
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

		return (float) $this->longitude ? $this->longitude : null;
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

	/**
	 * Returns the coordinates as a tuple.
	 *
	 * @return array
	 */
	public function toArray() {

		return array( $this->getLongitude(), $this->getLatitude() );
	}

	/**
	 * @return string
	 */
	public function __toString() {

		return (string) "{$this->getLatitude()},{$this->getLongitude()}";
	}
}
