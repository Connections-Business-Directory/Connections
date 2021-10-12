<?php

namespace Connections_Directory\Geocoder;

use WP_Error;
use cnCollection;
use Connections_Directory\Model\Bounds;
use Connections_Directory\Geocoder\Query\Address;
use Connections_Directory\Geocoder\Query\Coordinates;
use Connections_Directory\Geocoder\Provider\Provider;

/**
 * Geocode with multiple service provider support. Heavily based on the PHP Geocoding library.
 *
 * @package Connections_Directory\Geocoder
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Steven A. Zahm
 * @since 8.26
 *
 * @license MIT License
 */
class Geocoder {

	/**
	 * The default result limit.
	 *
	 * @since 8.26
	 * @var int
	 */
	const RESULT_LIMIT = 5;

	/**
	 * @since 8.26
	 * @var string
	 */
	private $locale;

	/**
	 * @since 8.26
	 * @var Bounds
	 */
	private $bounds;

	/**
	 * @since 8.26
	 * @var int
	 */
	private $limit;

	/**
	 * @since 8.26
	 * @var Provider
	 */
	private $provider;

	/**
	 * Use to register geocoding providers.
	 *
	 * @since 8.26
	 * @param Provider $provider
	 */
	public static function register( $provider ) {
	}

	/**
	 * cnGecoder constructor.
	 *
	 * @since 8.26
	 *
	 * @param Provider    $provider
	 * @param null|string $locale
	 */
	public function __construct( $provider, $locale = null ) {

		$this->provider = $provider;
		$this->locale   = $locale;
		$this->limit    = self::RESULT_LIMIT;
	}

	/**
	 * @since 8.26
	 *
	 * @param Address $query
	 *
	 * @return cnCollection|WP_Error
	 */
	public function geocode( $query ) {

		$locale = $query->getLocale();

		if ( empty( $locale ) && null !== $this->locale ) {

			$query = $query->withLocale( $this->locale );
		}

		$bounds = $query->getBounds();

		if ( empty( $bounds ) && null !== $this->bounds ) {

			$query = $query->withBounds( $this->bounds );
		}

		return $this->provider->geocode( $query );
	}

	/**
	 * @since 8.26
	 *
	 * @param Coordinates $query
	 *
	 * @return cnCollection|WP_Error
	 */
	public function reverse( $query ) {

		$locale = $query->getLocale();

		if ( empty( $locale ) && null !== $this->locale ) {

			$query = $query->withLocale( $this->locale );
		}

		return $this->provider->reverse( $query );
	}

	/**
	 * @since 8.26
	 *
	 * @param string $ip
	 *
	 * @return cnCollection|WP_Error
	 */
	public function ip( $ip ) {

		return new cnCollection();
	}
}
