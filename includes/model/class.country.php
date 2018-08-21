<?php

/**
 * Class cnCountry
 *
 * @since 8.6
 */
final class cnCountry {

	/**
	 * The country attributes array.
	 *
	 * @since 8.7
	 * @var   array
	 */
	protected $attributes;

	/**
	 * @access public
	 * @since  8.6
	 *
	 * @param array $attributes
	 */
	public function __construct( $attributes ) {

		// Set the attributes
		$this->setAttributes( $attributes );
	}

	/**
	 * Get the attributes.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return array|null
	 */
	public function getAttributes() {

		return $this->attributes;
	}

	/**
	 * Set the attributes.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @param array $attributes
	 *
	 * @return $this
	 */
	public function setAttributes( $attributes ) {

		$this->attributes = $attributes;

		return $this;
	}

	/**
	 * Get data from attributes array using "dot" notation.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get( $key, $default = NULL ) {

		return cnArray::get( $this->attributes, $key, $default );
	}

	/**
	 * @access public
	 * @since  8.7
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {

		if ( cnArray::has( $this->attributes, $key ) ) {

			return cnArray::get( $this->attributes, $key );
		}

		return NULL;
	}

	/**
	 * @access public
	 * @since  8.7
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function __isset( $key ) {

		return cnArray::has( $this->attributes, $key );
	}

	/**
	 * Set single attribute.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set( $key, $value ) {

		$this->attributes[ $key ] = $value;

		return $this;
	}

	/**
	 * Returns the country name
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return string
	 */
	public function getName() {

		return $this->get( 'name.common' ) ? $this->get( 'name.common' ) : $this->get( 'name' );
	}

	/**
	 * Returns the country ISO code.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @deprecated 8.7 Use cnCountry::getIsoAlpha2()
	 * @see cnCountry::getIsoAlpha2()
	 *
	 * @return string
	 */
	public function getCode() {

		return $this->getIsoAlpha2();
	}

	/**
	 * Get the ISO 3166-1 alpha2.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return string|null
	 */
	public function getIsoAlpha2() {

		return $this->get( 'iso_3166_1_alpha2' );
	}

	/**
	 * Get the ISO 3166-1 alpha3.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return string|null
	 */
	public function getIsoAlpha3() {

		return $this->get( 'iso_3166_1_alpha3' );
	}

	/**
	 * Get the ISO 3166-1 numeric.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return string|null
	 */
	public function getIsoNumeric() {

		return $this->get( 'iso_3166_1_numeric' );
	}

	/**
	 * Get the address format.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return string|null
	 */
	public function getAddressFormat() {

		return $this->get( 'extra.address_format' );
	}

	/**
	 * Get the emoji.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return array|null
	 */
	public function getEmoji() {

		return $this->get( 'extra.emoji' ) ? $this->get( 'extra.emoji' ) : $this->get( 'emoji' );
	}

	/**
	 * Get the geographic data structure.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return string|null
	 */
	public function getGeoJson() {

		if ( ! ( $code = $this->getIsoAlpha2() ) ) {
			return NULL;
		}

		return file_exists(
			$file = CN_PATH . 'vendor/rinvex/resources/geodata/' . strtolower( $code ) . '.json'
		) ? json_decode( file_get_contents( $file ) ) : NULL;
	}

	/**
	 * Get the flag.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return string|null
	 */
	public function getFlag() {

		if ( ! ( $code = $this->getIsoAlpha2() ) ) {
			return NULL;
		}

		return file_exists(
			$file = CN_PATH . 'vendor/rinvex/resources/flags/' . strtolower( $code ) . '.svg'
		) ? file_get_contents( $file ) : NULL;
	}

	/**
	 * Get the divisions.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @return array|null
	 */
	public function getDivisions() {

		if ( ! ( $code = $this->getIsoAlpha2() ) ) {
			return NULL;
		}

		return file_exists(
			$file = CN_PATH . 'vendor/rinvex/resources/divisions/' . strtolower( $code ) . '.json'
		) ? json_decode( file_get_contents( $file ), TRUE ) : NULL;
	}

	/**
	 * Get the divisions.
	 *
	 * @access public
	 * @since  8.7
	 *
	 * @param string $division
	 *
	 * @return array|null
	 */
	public function getDivision( $division ) {

		$divisions = $this->getDivisions();

		return ! empty( $divisions ) && isset( $divisions[ $division ] ) ? $divisions[ $division ] : NULL;
	}

	/**
	 * Returns country attributes JSON encoded.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return string
	 */
	public function __toString() {

		return json_encode( $this->getAttributes() );
	}
}
