<?php

namespace Connections_Directory\Geocoder\Model;

use Connections_Directory\Model\Address;
use cnCountry as Country;
use cnCoordinates as Coordinates;
use Connections_Directory\Model\Bounds;

/**
 * Class Address_Builder
 *
 * @package cnGeocoder\Model
 * @author  Tobias Nyholm <tobias.nyholm@gmail.com>
 * @license MIT License
 * @since 8.26
 */
final class Address_Builder {

	/**
	 * @since 8.26
	 * @var Coordinates|null
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
	private $streetNumber;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $streetName;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $locality;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $county;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $region;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $postalCode;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $postalCodeSuffix;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $country;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $countryCode;

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
	 * @since 8.26
	 *
	 * @param string $class
	 *
	 * @return Address
	 */
	public function build( $class = 'Connections_Directory\Model\Address' ) {

		if ( ! is_a( $class, 'Connections_Directory\Model\Address', true ) ) {

			throw new \LogicException(
				'First parameter to Address_Builder::build must be a class name extending Connections_Directory\Model\Address.'
			);
		}

		$country = null;

		if ( ! empty( $this->country ) || ! empty( $this->country ) ) {

			$country = new Country( array( 'name' => $this->country, 'iso_3166_1_alpha2' => $this->countryCode ) );
		}

		return new $class(
			array(
				'line_1'      => trim( $this->streetNumber . ' ' . $this->streetName ),
				'locality'    => $this->locality,
				'county'      => $this->county,
				'region'      => $this->region,
				'postal_code' => trim( $this->postalCode . ' ' . $this->postalCodeSuffix ),
				'country'     => $country,
				'coordinates' => $this->coordinates,
				'bounds'      => $this->bounds,
				'timezone'    => $this->timezone,
				'meta'        => $this->meta,
			)
		);
	}

	/**
	 * @since 8.26
	 *
	 * @param float $south
	 * @param float $west
	 * @param float $north
	 * @param float $east
	 *
	 * @return Address_Builder
	 */
	public function setBounds( $south, $west, $north, $east ) {

		try {
			$this->bounds = new Bounds( $south, $west, $north, $east );
		} catch ( \InvalidArgumentException $e ) {
			$this->bounds = null;
		}

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param float $latitude
	 * @param float $longitude
	 *
	 * @return Address_Builder
	 */
	public function setCoordinates( $latitude, $longitude ) {

		$this->coordinates = new Coordinates( $latitude, $longitude );

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $streetNumber
	 *
	 * @return Address_Builder
	 */
	public function setStreetNumber( $streetNumber ) {

		$this->streetNumber = $streetNumber;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $streetName
	 *
	 * @return Address_Builder
	 */
	public function setStreetName( $streetName ) {

		$this->streetName = $streetName;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $locality
	 *
	 * @return Address_Builder
	 */
	public function setLocality( $locality ) {

		$this->locality = $locality;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $county
	 *
	 * @return Address_Builder
	 */
	public function setCounty( $county ) {

		$this->county = $county;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $region
	 *
	 * @return Address_Builder
	 */
	public function setRegion( $region ) {

		$this->region = $region;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $postalCode
	 *
	 * @return Address_Builder
	 */
	public function setPostalCode( $postalCode ) {

		$this->postalCode = $postalCode;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $postalCodeSuffix
	 *
	 * @return Address_Builder
	 */
	public function setPostalCodeSuffix( $postalCodeSuffix ) {

		$this->postalCodeSuffix = $postalCodeSuffix;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $country
	 *
	 * @return Address_Builder
	 */
	public function setCountry( $country ) {

		$this->country = $country;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $countryCode
	 *
	 * @return Address_Builder
	 */
	public function setCountryCode( $countryCode ) {

		$this->countryCode = $countryCode;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param null|string $timezone
	 *
	 * @return Address_Builder
	 */
	public function setTimezone( $timezone ) {

		$this->timezone = $timezone;

		return $this;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return Address_Builder
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
	public function getMeta( $name, $default = null ) {

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
