<?php

namespace Connections_Directory\Geocoder\Provider\Bing_Maps;

use cnCollection as Collection;
use WP_Error;
use Connections_Directory\Geocoder\Model\Address_Builder;
use Connections_Directory\Geocoder\Query\Address as QueryAddress;
use Connections_Directory\Geocoder\Query\Coordinates as QueryCoordinates;
use Connections_Directory\Geocoder\Provider\Provider;

/**
 * Class Bing_Maps
 *
 * @package Connections_Directory\Geocoder\Provider\Bing_Maps
 * @License MIT License
 * @since 8.26
 */
final class Bing_Maps implements Provider {

	/**
	 * @since 8.26
	 * @var string
	 */
	const NAME = 'Bing Maps';

	/**
	 * @since 8.26
	 * @var string
	 */
	const ID = 'bing_maps';

	/**
	 * @since 8.26
	 * @var string
	 */
	const ADDRESS_GEOCODE_ENDPOINT = 'https://dev.virtualearth.net/REST/v1/Locations/?maxResults=%d&q=%s&key=%s&incl=ciso2';

	/**
	 * @since 8.26
	 * @var string
	 */
	const REVERSE_GEOCODE_ENDPOINT = 'https://dev.virtualearth.net/REST/v1/Locations/%F,%F?key=%s&incl=ciso2';

	/**
	 * @since 8.26
	 * @var string
	 */
	private $apiKey;

	/**
	 * @since 8.26
	 * @param string $apiKey An API key
	 */
	public function __construct( $apiKey ) {

		$this->apiKey = $apiKey;
	}

	/**
	 * @since 8.26
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

		$url = sprintf(
			self::ADDRESS_GEOCODE_ENDPOINT,
			$query->getLimit(),
			urlencode( $query->getText() ),
			$this->apiKey
		);

		return $this->executeQuery( $url, $query->getLocale(), $query->getLimit() );
	}

	/**
	 * @since 8.26
	 *
	 * @param QueryCoordinates $query
	 *
	 * @return Collection|WP_Error
	 */
	public function reverse( QueryCoordinates $query ) {

		$coordinates = $query->getCoordinates();

		$url = sprintf(
			self::REVERSE_GEOCODE_ENDPOINT,
			$coordinates->getLatitude(),
			$coordinates->getLongitude(),
			$this->apiKey
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
	 * @since 8.26
	 *
	 * @param string $url
	 * @param string $locale
	 * @param int    $limit
	 *
	 * @return Collection|WP_Error
	 */
	private function executeQuery( $url, $locale, $limit ) {

		if ( empty( $this->apiKey ) ) {

			return new WP_Error(
				'provider_api_key_required',
				'Provider API key required.'
			);
		}

		if ( null !== $locale ) {
			$url = sprintf( '%s&culture=%s', $url, str_replace( '_', '-', $locale ) );
		}

		$request = wp_remote_get( $url );

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

		if ( ! isset( $response->resourceSets[0] ) || ! isset( $response->resourceSets[0]->resources ) ) {

			return new Collection( array() );
		}

		$data = (array) $response->resourceSets[0]->resources;

		$results = array();

		foreach ( $data as $item ) {

			$builder = new Address_Builder();

			$builder->setMeta( 'geocode_provider', array( 'name' => $this->getName(), 'id' => $this->getID() ) );

			$coordinates = (array) $item->geocodePoints[0]->coordinates;

			$builder->setCoordinates( $coordinates[0], $coordinates[1] );

			if ( isset( $item->bbox ) && is_array( $item->bbox ) && count( $item->bbox ) > 0 ) {

				$builder->setBounds( $item->bbox[0], $item->bbox[1], $item->bbox[2], $item->bbox[3] );
			}

			$builder->setStreetName( $item->address->addressLine ? $item->address->addressLine : null );
			$builder->setPostalCode( $item->address->postalCode ? $item->address->postalCode : null );
			$builder->setLocality( $item->address->locality ? $item->address->locality : null );
			$builder->setCounty( $item->address->adminDistrict2 ? $item->address->adminDistrict2 : null );
			$builder->setRegion( $item->address->adminDistrict ? $item->address->adminDistrict : null );
			$builder->setCountry( $item->address->countryRegion ? $item->address->countryRegion : null );
			$builder->setCountryCode( $item->address->countryRegionIso2 ? $item->address->countryRegionIso2 : null );

			$results[] = $builder->build();

			if ( count( $results ) >= $limit ) {
				break;
			}
		}

		return new Collection( $results );
	}
}
