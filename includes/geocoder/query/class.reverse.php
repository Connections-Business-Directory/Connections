<?php

namespace Connections_Directory\Geocoder\Query;

use Connections_Directory\Geocoder\Geocoder;
use cnCoordinates as Position;

/**
 * @package Connections_Directory\Geocoder\Query
 * @author  Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author  Steven A. Zahm
 * @license MIT License
 * @since 8.26
 */
final class Coordinates implements Query {

	/**
	 * @since 8.26
	 * @var Position
	 */
	private $coordinates;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $locale;

	/**
	 * @since 8.26
	 * @var int
	 */
	private $limit = Geocoder::RESULT_LIMIT;

	/**
	 * @since 8.26
	 * @var array
	 */
	private $data = array();

	/**
	 * @since 8.26
	 *
	 * @param Position $coordinates
	 */
	private function __construct( Position $coordinates ) {

		$this->coordinates = $coordinates;
	}

	/**
	 * @since 8.26
	 *
	 * @param Position $coordinates
	 *
	 * @return Coordinates
	 */
	public static function create( Position $coordinates ) {

		return new self( $coordinates );
	}

	/**
	 * @since 8.26
	 *
	 * @param float $latitude
	 * @param float $longitude
	 *
	 * @return Coordinates
	 */
	public static function fromCoordinates( $latitude, $longitude ) {

		return new self( new Position( $latitude, $longitude ) );
	}

	/**
	 * @since 8.26
	 *
	 * @param Coordinates $coordinates
	 *
	 * @return Coordinates
	 */
	public function withCoordinates( $coordinates ) {

		$new              = clone $this;
		$new->coordinates = $coordinates;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @param int $limit
	 *
	 * @return Coordinates
	 */
	public function withLimit( $limit ) {

		$new        = clone $this;
		$new->limit = $limit;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $locale
	 *
	 * @return Coordinates
	 */
	public function withLocale( $locale ) {

		$new         = clone $this;
		$new->locale = $locale;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return Coordinates
	 */
	public function withData( $name, $value ) {

		$new                = clone $this;
		$new->data[ $name ] = $value;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @return Position
	 */
	public function getCoordinates() {

		return $this->coordinates;
	}

	/**
	 * @since 8.26
	 *
	 * @return int
	 */
	public function getLimit() {

		return $this->limit;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getLocale() {

		return $this->locale;
	}

	/**
	 * @since 8.26
	 *
	 * @param string     $name
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public function getData( $name, $default = null ) {

		if ( ! array_key_exists( $name, $this->data ) ) {
			return $default;
		}

		return $this->data[ $name ];
	}

	/**
	 * @since 8.26
	 *
	 * @return array
	 */
	public function getAllData() {

		return $this->data;
	}

	/**
	 * String for logging. This is also a unique key for the query.
	 *
	 * @since 8.26
	 *
	 * @return string
	 */
	public function __toString() {

		return sprintf(
			'Coordinates Geocode Query: %s',
			json_encode(
				array(
					'lat'    => $this->getCoordinates()->getLatitude(),
					'lng'    => $this->getCoordinates()->getLongitude(),
					'locale' => $this->getLocale(),
					'limit'  => $this->getLimit(),
					'data'   => $this->getAllData(),
				)
			)
		);
	}
}
