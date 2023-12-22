<?php

namespace Connections_Directory\Geocoder\Provider\Nominatim;

use cnCollection as Collection;
use WP_Error;
use Connections_Directory\Geocoder\Model\Address_Builder;
use Connections_Directory\Geocoder\Query\Address as QueryAddress;
use Connections_Directory\Geocoder\Query\Coordinates as QueryCoordinates;
use Connections_Directory\Geocoder\Provider\Provider;

/**
 * Class Nominatim
 *
 * @package Connections_Directory\Geocoder\Provider\Nominatim
 * @license MIT License
 * @since 8.26
 */
final class Nominatim implements Provider {

	/**
	 * @since 8.26
	 * @var string
	 */
	const NAME = 'Nominatim';

	/**
	 * @since 8.26
	 * @var string
	 */
	const ID = 'nominatim';

	/**
	 * @since 8.26
	 * @var string
	 */
	const DEFAULT_PROVIDER_ENDPOINT = 'https://nominatim.openstreetmap.org';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $providerEndpoint;

	/**
	 * @since 8.26
	 *
	 * @param string $providerEndpoint Root URL of the nominatim server
	 */
	public function __construct( $providerEndpoint = null ) {

		$this->providerEndpoint = null === $providerEndpoint ? self::DEFAULT_PROVIDER_ENDPOINT : rtrim( $providerEndpoint, '/' );
	}

	/**
	 * @since 8.26
	 *
	 * @param QueryAddress $query
	 *
	 * @return Collection|WP_Error
	 */
	public function geocode( QueryAddress $query ) {

		$address = $query->getText();

		// API returns invalid data if IP address given.
		// This API doesn't handle IPs.
		if ( filter_var( $address, FILTER_VALIDATE_IP ) ) {

			return new WP_Error(
				'provider_unsupported_service',
				'The geocode provider does not support IP addresses, only street addresses.',
				$query->getText()
			);
		}

		$url = sprintf( $this->getGeocodeEndpointUrl(), urlencode( $address ), $query->getLimit() );

		return $this->executeQuery( $url, $query->getLocale(), $query->getLimit() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function reverse( QueryCoordinates $query ) {

		$coordinates = $query->getCoordinates();

		$url = sprintf(
			$this->getReverseEndpointUrl(),
			$coordinates->getLatitude(),
			$coordinates->getLongitude(),
			$query->getData( 'zoom', 18 )
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
	 * Get provider endpoint for address geocode query.
	 *
	 * @since 8.26
	 *
	 * @return string
	 */
	private function getGeocodeEndpointUrl() {

		return $this->providerEndpoint . '/search?q=%s&format=jsonv2&addressdetails=1&limit=%d';
	}

	/**
	 * Get provider endpoint for reverse geocode.
	 *
	 * @since 8.26
	 *
	 * @return string
	 */
	private function getReverseEndpointUrl() {

		return $this->providerEndpoint . '/reverse?format=jsonv2&lat=%F&lon=%F&addressdetails=1&zoom=%d';
	}

	/**
	 * Get user agent for query request. Required per terms and conditions of usage.
	 *
	 * @since 8.26
	 *
	 * @return string
	 */
	private function getUserAgent() {

		return 'Connections Business Directory/' . CN_CURRENT_VERSION . '; ' . get_bloginfo( 'url' );
	}

	/**
	 * Get referer for query request. Required per terms and conditions of usage.
	 *
	 * @since 8.26
	 *
	 * @return string
	 */
	private function getReferer() {

		return get_bloginfo( 'url' );
	}

	/**
	 * @since 8.26
	 *
	 * @param string $url
	 * @param string $locale
	 * @param int    $limit
	 *
	 * @return Collection|WP_Error
	 */
	private function executeQuery( $url, $locale, $limit ) {

		if ( null !== $locale ) {

			$url = sprintf( '%s&accept-language=%s', $url, $locale );
		}

		$args = array(
			'user-agent' => $this->getUserAgent(),
			'headers'    => array( 'Referer' => $this->getReferer() ),
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

		if ( 0 === count( $response ) ) {

			return new WP_Error(
				'geocode_provider_no_results',
				__( 'Returned zero results.', 'connections' ),
				property_exists( $response, 'errorMessage' ) ? $response->error_message : $response->status
			);
		}

		$results = array();

		$data = is_array( $response ) ? $response : array( $response );

		foreach ( $data as $item ) {

			$builder = new Address_Builder();

			$builder->setMeta( 'geocode_provider', array( 'name' => $this->getName(), 'id' => $this->getID() ) );

			// Set official OSM place id.
			if ( isset( $item->place_id ) ) {

				$builder->setMeta( 'place_id', $item->place_id );
			}

			$builder->setCoordinates( $item->lat, $item->lon );

			if ( isset( $item->boundingbox ) && is_array( $item->boundingbox ) && 0 < count( $item->boundingbox ) ) {

				$builder->setBounds( $item->boundingbox[0], $item->boundingbox[2], $item->boundingbox[1], $item->boundingbox[3] );
			}

			$builder->setStreetNumber( property_exists( $item->address, 'house_number' ) ? $item->address->house_number : null );
			$builder->setStreetName( property_exists( $item->address, 'road' ) ? $item->address->road : null );
			$builder->setLocality( property_exists( $item->address, 'city' ) ? $item->address->city : null );
			$builder->setCounty( property_exists( $item->address, 'county' ) ? $item->address->county : null );
			$builder->setRegion( property_exists( $item->address, 'state' ) ? $item->address->state : null );
			$builder->setPostalCode( property_exists( $item->address, 'postcode' ) ? $item->address->postcode : null );
			$builder->setCountry( property_exists( $item->address, 'country' ) ? $item->address->country : null );
			$builder->setCountryCode( property_exists( $item->address, 'country_code' ) ? $item->address->country_code : null );

			$results[] = $builder->build();

			if ( count( $results ) >= $limit ) {
				break;
			}
		}

		return new Collection( $results );
	}
}
