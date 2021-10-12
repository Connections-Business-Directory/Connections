<?php

namespace Connections_Directory\Geocoder\Query;

use InvalidArgumentException;
use Connections_Directory\Geocoder\Geocoder;
use Connections_Directory\Model\Bounds;

/**
 * @package Connections_Directory\Geocoder\Query
 * @author  Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author  Steven A. Zahm
 * @license MIT License
 * @since 8.26
 */
final class Address implements Query {

	/**
	 * The address or text that should be geocoded.
	 *
	 * @since 8.26
	 * @var string
	 */
	private $text;

	/**
	 * @since 8.26
	 * @var Bounds|null
	 */
	private $bounds;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $locale;

	/**
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
	 * @param string $text
	 */
	private function __construct( $text ) {

		if ( empty( $text ) ) {
			throw new InvalidArgumentException( 'Geocode query cannot be empty' );
		}

		$this->text = $text;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $text
	 *
	 * @return Address
	 */
	public static function create( $text ) {

		return new self( $text );
	}

	/**
	 * @since 8.26
	 *
	 * @param string $text
	 *
	 * @return Address
	 */
	public function withText( $text ) {

		$new       = clone $this;
		$new->text = $text;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @param Bounds $bounds
	 *
	 * @return Address
	 */
	public function withBounds( $bounds ) {

		$new         = clone $this;
		$new->bounds = $bounds;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $locale
	 *
	 * @return Address
	 */
	public function withLocale( $locale ) {

		$new         = clone $this;
		$new->locale = $locale;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @param int $limit
	 *
	 * @return Address
	 */
	public function withLimit( $limit ) {

		$new        = clone $this;
		$new->limit = $limit;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return Address
	 */
	public function withData( $name, $value ) {

		$new                = clone $this;
		$new->data[ $name ] = $value;

		return $new;
	}

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function getText() {

		return $this->text;
	}

	/**
	 * @since 8.26
	 *
	 * @return Bounds|null
	 */
	public function getBounds() {

		return $this->bounds;
	}

	/**
	 * @since 8.26
	 *
	 * @return string|null
	 */
	public function getLocale() {

		return $this->locale;
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
			'Address Geocode Query: %s',
			json_encode(
				array(
					'text'   => $this->getText(),
					'bounds' => $this->getBounds() ? $this->getBounds()->toArray() : 'null',
					'locale' => $this->getLocale(),
					'limit'  => $this->getLimit(),
					'data'   => $this->getAllData(),
				)
			)
		);
	}
}
