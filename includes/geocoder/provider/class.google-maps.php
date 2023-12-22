<?php

namespace Connections_Directory\Geocoder\Provider\Google_Maps;

use cnCollection as Collection;
use WP_Error;
use Connections_Directory\Geocoder\Model\Address_Builder;
use Connections_Directory\Geocoder\Query\Address as QueryAddress;
use Connections_Directory\Geocoder\Query\Coordinates as QueryCoordinates;
use Connections_Directory\Geocoder\Provider\Provider;

/**
 * Class Google_Maps
 *
 * @package Connections_Directory\Geocoder\Provider\Google_Maps
 * @license MIT License
 */
final class Google_Maps implements Provider {

	/**
	 * @since 8.26
	 * @var string
	 */
	const NAME = 'Google Maps';

	/**
	 * @since 8.26
	 * @var string
	 */
	const ID = 'google_maps';

	/**
	 * @since 8.26
	 * @var string
	 */
	const ADDRESS_GEOCODE_ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s';

	/**
	 * @since 8.26
	 * @var string
	 */
	const REVERSE_GEOCODE_ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%F,%F';

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $region;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $apiKey;

	/**
	 * @since 8.26
	 * @var string
	 */
	private $clientId;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $privateKey;

	/**
	 * @since 8.26
	 * @var string|null
	 */
	private $channel;

	/**
	 * Google Maps for Business
	 *
	 * @link  https://developers.google.com/maps/documentation/business/
	 *
	 * @since 8.26
	 *
	 * @param string $clientId   Your Client ID
	 * @param string $privateKey Your Private Key (optional)
	 * @param string $region     Region biasing (optional)
	 * @param string $apiKey     Google Geocoding API key (optional)
	 * @param string $channel    Google Channel parameter (optional)
	 *
	 * @return Google_Maps
	 */
	public static function business( $clientId, $privateKey = null, $region = null, $apiKey = null, $channel = null ) {

		$provider             = new self( $region, $apiKey );
		$provider->clientId   = $clientId;
		$provider->privateKey = $privateKey;
		$provider->channel    = $channel;

		return $provider;
	}

	/**
	 * @since 8.26
	 *
	 * @param string $region Region biasing (optional)
	 * @param string $apiKey Google Geocoding API key (optional)
	 */
	public function __construct( $apiKey, $region = null ) {

		$this->apiKey = $apiKey;
		$this->region = $region;
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

		$url = sprintf( self::ADDRESS_GEOCODE_ENDPOINT, rawurlencode( $query->getText() ) );

		if ( null !== $bounds = $query->getBounds() ) {

			$url .= sprintf(
				'&bounds=%s,%s|%s,%s',
				$bounds->getSouth(),
				$bounds->getWest(),
				$bounds->getNorth(),
				$bounds->getEast()
			);
		}

		return $this->executeQuery(
			$url,
			$query->getLocale(),
			$query->getLimit(),
			$query->getData( 'region', $this->region )
		);
	}

	/**
	 * @since 8.26
	 *
	 * @param QueryCoordinates $query
	 *
	 * @return Collection|WP_Error
	 */
	public function reverse( QueryCoordinates $query ) {

		$coordinate = $query->getCoordinates();

		$url = sprintf(
			self::REVERSE_GEOCODE_ENDPOINT,
			$coordinate->getLatitude(),
			$coordinate->getLongitude()
		);

		if ( null !== $locationType = $query->getData( 'location_type' ) ) {
			$url .= '&location_type=' . urlencode( $locationType );
		}

		if ( null !== $resultType = $query->getData( 'result_type' ) ) {
			$url .= '&result_type=' . urlencode( $resultType );
		}

		return $this->executeQuery(
			$url,
			$query->getLocale(),
			$query->getLimit(),
			$query->getData( 'region', $this->region )
		);
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
	 * @param string      $url
	 * @param string      $locale
	 * @param null|string $region
	 *
	 * @return string query with extra params
	 */
	private function buildQuery( $url, $locale = null, $region = null ) {

		if ( null !== $locale ) {
			$url = sprintf( '%s&language=%s', $url, $locale );
		}

		if ( null !== $region ) {
			$url = sprintf( '%s&region=%s', $url, $region );
		}

		if ( null !== $this->apiKey ) {
			$url = sprintf( '%s&key=%s', $url, $this->apiKey );
		}

		if ( null !== $this->clientId ) {
			$url = sprintf( '%s&client=%s', $url, $this->clientId );

			if ( null !== $this->channel ) {
				$url = sprintf( '%s&channel=%s', $url, $this->channel );
			}

			if ( null !== $this->privateKey ) {
				$url = $this->signQuery( $url );
			}
		}

		return $url;
	}

	/**
	 * Sign a URL with a given crypto key
	 * Note that this URL must be properly URL-encoded
	 * @link http://gmaps-samples.googlecode.com/svn/trunk/urlsigning/UrlSigner.php-source
	 *
	 * @since 8.26
	 *
	 * @param string $query Query to be signed
	 *
	 * @return string $query query with signature appended
	 */
	private function signQuery( $query ) {

		$url = parse_url( $query );

		$urlPartToSign = $url['path'] . '?' . $url['query'];

		// Decode the private key into its binary format
		$decodedKey = base64_decode( str_replace( array( '-', '_' ), array( '+', '/' ), $this->privateKey ) );

		// Create a signature using the private key and the URL-encoded
		// string using HMAC SHA1. This signature will be binary.
		$signature = hash_hmac( 'sha1', $urlPartToSign, $decodedKey, true );

		$encodedSignature = str_replace( array( '+', '/' ), array( '-', '_' ), base64_encode( $signature ) );

		return sprintf( '%s&signature=%s', $query, $encodedSignature );
	}

	/**
	 * @since 8.26
	 *
	 * @param string $url
	 * @param string $locale
	 * @param int    $limit
	 * @param string $region
	 *
	 * @return Collection|WP_Error
	 */
	private function executeQuery( $url, $locale, $limit, $region ) {

		if ( empty( $this->apiKey ) ) {

			return new WP_Error(
				'provider_api_key_required',
				'Provider API key required.'
			);
		}

		$url = $this->buildQuery( $url, $locale, $region );

		$request = wp_remote_get( $url );

		if ( is_wp_error( $request ) ) {

			return new WP_Error(
				'provider_http_request_failed',
				'Request to provider failed.',
				$url
			);
		}

		$content = wp_remote_retrieve_body( $request );

		$json = $this->parseResponse( $url, $content );

		if ( is_wp_error( $json ) ) {

			return $json;
		}

		// no result
		if ( ! isset( $json->results ) || ! count( $json->results ) || 'OK' !== $json->status ) {

			return new Collection( array() );
		}

		$results = array();

		foreach ( $json->results as $result ) {

			$builder = new Address_Builder();

			$builder->setMeta( 'geocode_provider', array( 'name' => $this->getName(), 'id' => $this->getID() ) );

			// Set official Google place id.
			if ( isset( $result->place_id ) ) {

				$builder->setMeta( 'place_id', $result->place_id );
			}

			$this->parseCoordinates( $builder, $result );

			// Parse address components from provider response.
			foreach ( $result->address_components as $component ) {

				foreach ( $component->types as $type ) {

					$this->setAddressComponent( $builder, $type, $component );
				}
			}

			$results[] = $builder->build();

			if ( count( $results ) >= $limit ) {
				break;
			}
		}

		return new Collection( $results );
	}

	/**
	 * Decode the response content and validate it to make sure it does not have any errors.
	 *
	 * @since 8.26
	 *
	 * @param string $request
	 * @param string $content
	 *
	 * @return mixed|WP_Error result form json_decode()
	 */
	private function parseResponse( $request, $content ) {

		// Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
		if ( false !== strpos( $content, "Provided 'signature' is not valid for the provided client ID" ) ) {

			return new WP_Error(
				'geocode_provider_invalid_client_id',
				sprintf( 'Invalid client ID / API Key %s', $request )
			);
		}

		$response = json_decode( $content );

		if ( is_null( $response ) ) {

			return new WP_Error(
				'geocode_provider_invalid_response',
				sprintf( 'The geocoder provider returned an invalid response for query: "%s".', $request )
			);
		}

		/**
		 * @link https://developers.google.com/maps/documentation/geocoding/intro#StatusCodes
		 */

		switch ( $response->status ) {

			case 'ZERO_RESULTS':
				return new WP_Error(
					'geocode_provider_no_results',
					__( 'Returned zero results.', 'connections' ),
					property_exists( $response, 'errorMessage' ) ? $response->error_message : $response->status
				);

			case 'OVER_DAILY_LIMIT':
				return new WP_Error(
					'geocode_provider_over_daily_limit',
					__(
						'The API key is missing or invalid. OR Billing has not been enabled on your account. OR A self-imposed usage cap has been exceeded. OR The provided method of payment is no longer valid (for example, a credit card has expired).',
						'connections'
					),
					property_exists( $response, 'errorMessage' ) ? $response->error_message : $response->status
				);

			case 'OVER_QUERY_LIMIT':
				return new WP_Error(
					'geocode_provider_over_query_limit',
					__( 'Daily query limit has been exceeded.', 'connections' ),
					property_exists( $response, 'errorMessage' ) ? $response->error_message : $response->status
				);

			case 'REQUEST_DENIED':
				return new WP_Error(
					'geocode_provider_request_denied',
					__( 'Request has been denied.', 'connections' ),
					property_exists( $response, 'errorMessage' ) ? $response->error_message : $response->status
				);

			case 'INVALID_REQUEST':
				return new WP_Error(
					'geocode_provider_invalid_request',
					__( 'An invalid request has been received.', 'connections' ),
					property_exists( $response, 'errorMessage' ) ? $response->error_message : $response->status
				);

			case 'UNKNOWN_ERROR':
				return new WP_Error(
					'geocode_provider_unknown_error',
					__( 'An unknown error has occurred.', 'connections' ),
					property_exists( $response, 'errorMessage' ) ? $response->error_message : $response->status
				);

			case 'OK':
				return $response;

			default:
				return new WP_Error(
					'geocode_provider_unknown_status',
					__( 'An unknown status response has been received.', 'connections' ),
					$response->status
				);
		}
	}

	/**
	 * Update current resultSet with given key/value.
	 *
	 * @since 8.26
	 *
	 * @param Address_Builder $builder
	 * @param string          $type   Component type
	 * @param object          $values The component values
	 */
	private function setAddressComponent( $builder, $type, $values ) {

		switch ( $type ) {
			case 'postal_code':
				$builder->setPostalCode( $values->long_name );
				break;

			case 'postal_code_suffix':
				$builder->setPostalCodeSuffix( $values->long_name );
				break;

			case 'locality':
			case 'postal_town':
				$builder->setLocality( $values->long_name );
				break;

			case 'administrative_area_level_1':
				$builder->setRegion( $values->long_name );
				break;

			case 'administrative_area_level_2':
				$builder->setCounty( $values->long_name );
				break;

			case 'administrative_area_level_3':
			case 'administrative_area_level_4':
			case 'administrative_area_level_5':
				// $builder->addAdminLevel( intval( substr( $type, - 1 ) ), $values->long_name, $values->short_name );
				$builder->setMeta( $type, $values->long_name );
				break;

			case 'sublocality_level_1':
			case 'sublocality_level_2':
			case 'sublocality_level_3':
			case 'sublocality_level_4':
			case 'sublocality_level_5':
				$subLocalityLevel   = $builder->getMeta( 'subLocalityLevel', array() );
				$subLocalityLevel[] = array(
					'level' => intval( substr( $type, - 1 ) ),
					'name'  => $values->long_name,
					'code'  => $values->short_name,
				);
				$builder->setMeta( 'subLocalityLevel', $subLocalityLevel );
				break;

			case 'country':
				$builder->setCountry( $values->long_name );
				$builder->setCountryCode( $values->short_name );
				break;

			case 'street_number':
				$builder->setStreetNumber( $values->long_name );
				break;

			case 'route':
				$builder->setStreetName( $values->long_name );

				break;

			case 'sublocality':
				// $builder->setSubLocality( $values->long_name );
				$builder->setMeta( $type, $values->long_name );
				break;

			case 'street_address':
			case 'intersection':
			// case 'political':
			case 'colloquial_area':
			case 'ward':
			case 'neighborhood':
			case 'premise':
			case 'subpremise':
			case 'natural_feature':
			case 'airport':
			case 'park':
			case 'point_of_interest':
			case 'establishment':
				$builder->setMeta( $type, $values->long_name );
				break;

			default:
		}
	}

	/**
	 * Parse coordinates and bounds.
	 *
	 * @since 8.26
	 *
	 * @param Address_Builder $builder
	 * @param                 $result
	 */
	private function parseCoordinates( $builder, $result ) {

		$coordinates = $result->geometry->location;
		$builder->setCoordinates( $coordinates->lat, $coordinates->lng );

		if ( isset( $result->geometry->bounds ) ) {

			$builder->setBounds(
				$result->geometry->bounds->southwest->lat,
				$result->geometry->bounds->southwest->lng,
				$result->geometry->bounds->northeast->lat,
				$result->geometry->bounds->northeast->lng
			);

		} elseif ( isset( $result->geometry->viewport ) ) {

			$builder->setBounds(
				$result->geometry->viewport->southwest->lat,
				$result->geometry->viewport->southwest->lng,
				$result->geometry->viewport->northeast->lat,
				$result->geometry->viewport->northeast->lng
			);

		} elseif ( 'ROOFTOP' === $result->geometry->location_type ) {

			// Fake bounds
			$builder->setBounds(
				$coordinates->lat,
				$coordinates->lng,
				$coordinates->lat,
				$coordinates->lng
			);
		}
	}
}
