<?php

namespace Connections_Directory\Geocoder\Provider\Algolia;

use cnCollection as Collection;
use WP_Error;
use Connections_Directory\Geocoder\Model\Address_Builder;
use Connections_Directory\Geocoder\Query\Address as QueryAddress;
use Connections_Directory\Geocoder\Query\Coordinates as QueryCoordinates;
use Connections_Directory\Geocoder\Provider\Provider;

/**
 * Class Algolia
 *
 * @link https://community.algolia.com/places/rest.html
 *
 * @package Connections_Directory\Geocoder\Provider\Bing_Maps
 * @author Steven A. Zahm
 * @License MIT License
 * @since 8.27
 */
final class Algolia implements Provider {

	/**
	 * @since 8.27
	 * @var string
	 */
	const NAME = 'Algolia';

	/**
	 * @since 8.27
	 * @var string
	 */
	const ID = 'algolia';

	/**
	 * @since 8.27
	 * @var string
	 */
	const ADDRESS_GEOCODE_ENDPOINT = 'https://places-dsn.algolia.net/1/places/query';

	/**
	 * @since 8.27
	 * @var string
	 */
	const REVERSE_GEOCODE_ENDPOINT = 'https://places-dsn.algolia.net/1/places/reverse?aroundLatLng=%F,%F';

	/**
	 * @since 8.27
	 * @var string
	 */
	private $appID;

	/**
	 * @since 8.27
	 * @var string
	 */
	private $apiKey;

	/**
	 * @since 8.27
	 * @var string
	 */
	private $query = '';

	/**
	 * @since 8.27
	 *
	 * @param string $appID  The application ID.
	 * @param string $apiKey An API key
	 */
	public function __construct( $appID = '', $apiKey = '' ) {

		$this->appID  = $appID;
		$this->apiKey = $apiKey;
	}

	/**
	 * @since 8.27
	 *
	 * @param QueryAddress $query
	 *
	 * @return Collection|WP_Error
	 */
	public function geocode( QueryAddress $query ) {

		// API returns invalid data if IP address given.
		// This API doesn't handle IPs.
		if ( filter_var( $query->getText(), FILTER_VALIDATE_IP ) ) {

			return new WP_Error(
				'provider_unsupported_service',
				'The geocode provider does not support IP addresses, only street addresses.',
				$query->getText()
			);
		}

		$this->query = $query->getText();

		return $this->executeQuery( self::ADDRESS_GEOCODE_ENDPOINT, $query->getLocale(), $query->getLimit() );
	}

	/**
	 * @since 8.27
	 *
	 * @param QueryCoordinates $query
	 *
	 * @return Collection|WP_Error
	 */
	public function reverse( QueryCoordinates $query ) {

		$coordinates = $query->getCoordinates();

		/*
		 * Empty the query string otherwise the endpoint will return error code 400 response.
		 * Invalid query parameter for reverse geocode.
		 */
		$this->query = '';

		$url = sprintf(
			self::REVERSE_GEOCODE_ENDPOINT,
			$coordinates->getLatitude(),
			$coordinates->getLongitude()
		);

		return $this->executeQuery( $url, $query->getLocale(), $query->getLimit() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() {

		return self::NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getID() {

		return self::ID;
	}

	/**
	 * @since 8.27
	 *
	 * @param string $url
	 * @param string $locale
	 * @param int    $limit
	 *
	 * @return Collection|WP_Error
	 */
	private function executeQuery( $url, $locale, $limit ) {

		$headers = array();

		if ( 0 < strlen( $this->appID ) ) {
			$headers[] = array( 'X-Algolia-Application-Id' => $this->appID );
		}

		if ( 0 < strlen( $this->apiKey ) ) {

			$headers[] = array( 'X-Algolia-API-Key' => $this->apiKey );
		}

		$query = array(
			// 'type'        => 'address',
			'hitsPerPage' => $limit,
			'language'    => $locale,
		);

		if ( 0 < strlen( $this->query ) ) {

			$query['query'] = $this->query;
		}

		$args = array(
			'headers' => $headers,
			'body'    => $query,
		);

		$request = wp_remote_get( $url, $args );

		if ( is_wp_error( $request ) ) {

			return new WP_Error(
				'provider_http_request_failed',
				'Request to provider failed.',
				$url
			);
		}

		$content = wp_remote_retrieve_body( $request );

		$response = json_decode( $content );

		if ( is_null( $response ) ) {

			return new WP_Error(
				'geocode_provider_invalid_response',
				sprintf( 'The geocoder provider returned an invalid response for query: "%s".', $url )
			);
		}

		if ( ! is_array( $response->hits ) || ( is_array( $response->hits ) && 0 === count( $response->hits ) ) ) {

			return new WP_Error(
				'geocode_provider_no_results',
				__( 'Returned zero results.', 'connections' )
			);
		}

		$results = array();

		foreach ( $response->hits as $item ) {

			$builder = new Address_Builder();

			$builder->setMeta( 'geocode_provider', array( 'name' => $this->getName(), 'id' => $this->getID() ) );

			// Set official place id.
			if ( isset( $item->objectID ) ) {

				$builder->setMeta( 'objectID', $item->objectID );
			}

			$builder->setCoordinates( $item->_geoloc->lat, $item->_geoloc->lng );

			$builder->setStreetName( property_exists( $item, 'locale_names' ) ? $item->locale_names[0] : null );
			$builder->setLocality( property_exists( $item, 'city' ) ? $item->city[0] : null );
			$builder->setCounty( property_exists( $item, 'county' ) ? $item->county[0] : null );
			$builder->setRegion( property_exists( $item, 'administrative' ) && array_key_exists( 0, $item->administrative ) ? $item->administrative[0] : null );
			$builder->setPostalCode( property_exists( $item, 'postcode' ) && isset( $item->postcode[0] ) ? $item->postcode[0] : null );
			$builder->setCountry( property_exists( $item, 'country' ) ? $item->country : null );
			$builder->setCountryCode( property_exists( $item, 'country_code' ) ? $item->country_code : null );

			$results[] = $builder->build();

			if ( count( $results ) >= $limit ) {
				break;
			}

		}

		return new Collection( $results );
	}
}
