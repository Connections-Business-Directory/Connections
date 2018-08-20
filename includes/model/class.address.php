<?php

namespace Connections_Directory\Model;

use WP_Error;
use cnSanitize;
use cnCountry as Country;
use cnCoordinates as Coordinates;
use Connections_Directory\Model\Bounds;
use cnTimezone as Timezone;
use cnGoogleMapsTimeZone;

/**
 * Class Address
 *
 * @package Connections_Directory\Model
 *
 * @since 8.26
 *
 * @author Steven A Zahm
 * @license MIT License
 */
class Address {

	/**
	 * @since 8.26
	 * @var string
	 */
	private $line_1 = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $line_2 = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $line_3 = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $line_4 = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $district = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $county = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $locality = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $region = '';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $postal_code = '';

	/**
	 * @since 8.26
	 * @var Country
	 */
	private $country = '';

	/**
	 * @since 8.26
	 * @var Coordinates
	 */
	private $coordinates;

	/**
	 * @since 8.26
	 * @var Bounds|null
	 */
	private $bounds;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $timezone;

	/**
	 * A storage for extra parameters.
	 *
	 * @since 8.26
	 * @var array
	 */
	private $meta = array();

	/**
	 * Address constructor.
	 *
	 * @since 8.26
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$address = array(
			'line_1'      => NULL,
			'line_2'      => NULL,
			'line_3'      => NULL,
			'line_4'      => NULL,
			'district'    => NULL,
			'county'      => NULL,
			'locality'    => NULL, //city
			'region'      => NULL, //state
			'postal_code' => NULL, //zipcode
			'country'     => NULL,
			'coordinates' => NULL,
			'bounds'      => NULL,
			'timezone'    => NULL,
			'meta'        => array(),
		);

		$address = wp_parse_args( $data, $address );

		$this->line_1 = $address['line_1'];
		$this->line_2 = $address['line_2'];
		$this->line_3 = $address['line_3'];
		$this->line_4 = $address['line_4'];

		$this->district = $address['district'];
		$this->county   = $address['county'];

		$this->locality    = $address['locality'];
		$this->region      = $address['region'];
		$this->postal_code = $address['postal_code'];

		$this->country     = $address['country'];
		$this->coordinates = $address['coordinates'];
		$this->bounds      = $address['bounds'];
		$this->timezone    = $address['timezone'];

		$this->meta = $address['meta'];
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getLineOne() {

		return $this->line_1;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $street
	 *
	 * @return Address
	 */
	public function setLineOne( $street ) {

		$this->line_1 = cnSanitize::field( 'street', $street, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getLineTwo() {

		return $this->line_2;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $street
	 *
	 * @return Address
	 */
	public function setLineTwo( $street ) {

		$this->line_2 = cnSanitize::field( 'street', $street, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getLineThree() {

		return $this->line_3;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $street
	 *
	 * @return Address
	 */
	public function setLineThree( $street ) {

		$this->line_3 = cnSanitize::field( 'street', $street, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getLineFour() {

		return $this->line_4;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $street
	 *
	 * @return Address
	 */
	public function setLineFour( $street ) {

		$this->line_4 = cnSanitize::field( 'street', $street, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getDistrict() {

		return $this->district;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $district
	 *
	 * @return Address
	 */
	public function setDistrict( $district ) {

		$this->district = cnSanitize::field( 'district', $district, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getCounty() {

		return $this->county;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $county
	 *
	 * @return Address
	 */
	public function setCounty( $county ) {

		$this->county = cnSanitize::field( 'county', $county, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getLocality() {

		return $this->locality;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $locality
	 *
	 * @return Address
	 */
	public function setLocality( $locality ) {

		$this->locality = cnSanitize::field( 'locality', $locality, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getRegion() {

		return $this->region;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $region
	 *
	 * @return Address
	 */
	public function setRegion( $region ) {

		$this->region = cnSanitize::field( 'region', $region, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getPostalCode() {

		return $this->postal_code;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $postal_code
	 *
	 * @return Address
	 */
	public function setPostalCode( $postal_code ) {

		$this->postal_code = cnSanitize::field( 'postal-code', $postal_code, 'raw' );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getCountry() {

		return $this->country->getName();
	}

	/**
	 * @since 8.26
	 *
	 * @param string $country
	 * @param string $code
	 *
	 * @return Address
	 */
	public function setCountry( $country, $code = '' ) {

		$this->country = new Country(
			array(
				'name'              => cnSanitize::field( 'country', $country, 'raw' ),
				'iso_3166_1_alpha2' => $code,
			)
		);

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param float $latitude
	 * @param float $longitude
	 *
	 * @return Address
	 */
	public function setCoordinates( $latitude, $longitude ) {

		if ( NULL === $latitude || NULL === $longitude ) {

			$this->coordinates = NULL;
		}

		$this->coordinates = new Coordinates( $latitude, $longitude );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @return string|null
	 */
	public function getLatitude() {

		if ( NULL === $this->coordinates ) {
			return NULL;
		}

		return $this->coordinates->getLatitude();
	}

	/**
	 * @since 8.26
	 *
	 * @return string|null
	 */
	public function getLongitude() {

		if ( NULL === $this->coordinates ) {
			return NULL;
		}

		return $this->coordinates->getLongitude();
	}

	public function setBounds() {

	}

	/**
	 * Utilize the Google Maps TimeZone API to get the time zone info of the address.
	 *
	 * @since 8.26
	 *
	 * @return Timezone|WP_Error An instance of cnTimezone on success and WP_Error instance on failure.
	 */
	public function getTimezone() {

		// Create GoogleMapsTimeZone object with default properties.
		$gmtAPI = new cnGoogleMapsTimeZone( $this->getLatitude(), $this->getLongitude() );

		// Perform query
		return $gmtAPI->queryTimeZone();
	}

	/**
	 * @since 8.26
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return Address
	 */
	public function setMeta( $name, $value ) {

		$this->meta[ $name ] = $value;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param string     $name
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public function getMeta( $name, $default = NULL ) {

		if ( $this->hasMeta( $name ) ) {
			return $this->meta[ $name ];
		}

		return $default;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasMeta( $name ) {

		return array_key_exists( $name, $this->meta );
	}
}
