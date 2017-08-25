<?php

/**
 * A PHP wrapper for the Google Maps Time Zone API.
 *
 * Heavily based on the code by Ivan Melgrati. Changes include:
 *  - Removed unused code
 *  - Set default language based in WP local.
 *  - Use the Connections Google Maps API key if not set.
 *  - Use WP_Error when dealing with error returned by the Timezone API.
 *  - Use WP HTTP API to call teh Timezone API and parse the response
 *
 * @package cnGoogleMapsTimeZone
 * @author  Steven A Zahm
 */

if ( ! class_exists( 'cnGoogleMapsTimeZone' ) ) {

	/**
	 * A PHP wrapper for the Google Maps Time Zone API.
	 *
	 * @author    Ivan Melgrati
	 * @author    Steve A Zahm
	 * @copyright Copyright 2016 by Ivan Melgrati
	 * @copyright 2017 Steven A. Zahm
	 * @license   https://github.com/imelgrat/google-time-zone/blob/master/LICENSE
	 * @link      https://developers.google.com/maps/documentation/timezone/intro
	 */
	class cnGoogleMapsTimeZone {

		/**
		 * Domain portion of the Google Maps Time Zone API URL.
		 *
		 * @since  8.6.9
		 */
		const URL_DOMAIN = 'maps.googleapis.com';

		/**
		 * Path portion of the Google Maps Time Zone API URL.
		 *
		 * @since  8.6.9
		 */
		const URL_PATH = '/maps/api/timezone/';

		/**
		 * Latitude to obtain the Timezone from.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @var float|string $latitude
		 */
		protected $latitude;

		/**
		 * Longitude to obtain the Timezone from.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @var float|string $longitude
		 */
		protected $longitude;

		/**
		 * Desired time as seconds since midnight, January 1, 1970 UTC.
		 * The Google Maps Time Zone API uses the timestamp to determine whether or not Daylight Savings should be applied.
		 * Times before 1970 can be expressed as negative values.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @var integer $timestamp
		 */
		protected $timestamp;

		/**
		 * Language code in which to return results.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @var string $language
		 */
		protected $language;

		/**
		 * Google Maps API key to authenticate with.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @var string $apiKey
		 */
		protected $apiKey;

		/**
		 * Google Maps API Client ID for Business clients.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @var string $clientId
		 */
		protected $clientId;

		/**
		 * Google Maps API Cryptographic signing key for Business clients.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @var string $signingKey
		 */
		protected $signingKey;

		/**
		 * Constructor. The request is not executed until `queryTimeZone()` is called.
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  float   $latitude  Latitude of the location to get time zone information from.
		 * @param  float   $longitude Longitude of the location to get time zone information from.
		 * @param  integer $timestamp Point in time to get time zone information from. Default: 0 (current time).
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function __construct( $latitude = 0.0, $longitude = 0.0, $timestamp = NULL ) {

			if ( NULL === $timestamp ) {

				$timestamp = current_time( 'timestamp', TRUE );
			}

			$this->setLatitudeLongitude( $latitude, $longitude )->setTimestamp( $timestamp );

			return $this;
		}

		/**
		 * Set the latitude/longitude of the location to get time zone information from.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  float|string $latitude  Latitude of the location to get time zone information from.
		 * @param  float|string $longitude Longitude of the location to get time zone information from.
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setLatitudeLongitude( $latitude, $longitude ) {

			$this->setLatitude( $latitude )->setLongitude( $longitude );

			return $this;
		}

		/**
		 * Get the latitude/longitude of the location to get time zone information from in comma-separated format.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @return string|false Comma-separated coordinates, or false if not set.
		 */
		public function getLatitudeLongitude() {

			$latitude  = $this->getLatitude();
			$longitude = $this->getLongitude();

			if ( $latitude && $longitude ) {
				return $latitude . ',' . $longitude;
			} else {
				return FALSE;
			}
		}

		/**
		 * Set the latitude of the location to get time zone information from.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  float|string $latitude Latitude of the location to get time zone information from.
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setLatitude( $latitude ) {

			$this->latitude = $latitude;

			return $this;
		}

		/**
		 * Get the latitude of the location to get time zone information from.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return float|string Latitude of the location to get time zone information from.
		 */
		public function getLatitude() {

			return $this->latitude;
		}

		/**
		 * Set the longitude of the location to get time zone information from.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  float|string $longitude Longitude of the location to get time zone information from.
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setLongitude( $longitude ) {

			$this->longitude = $longitude;

			return $this;
		}

		/**
		 * Get the longitude of the location to get time zone information from.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return float|string Longitude of the location to get time zone information from.
		 */
		public function getLongitude() {

			return $this->longitude;
		}

		/**
		 * Set the point in time to get time zone information from (used to determine whether or not DST is active).
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  integer $timestamp Point in time to get time zone information from. Default: 0 (current time).
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setTimestamp( $timestamp = 0 ) {

			$this->timestamp = intval( $timestamp );

			return $this;
		}

		/**
		 * Get the point in time to get time zone information from (used to determine whether or not DST is active).
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Usage
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return integer Point in time to get time zone information from.
		 */
		public function getTimestamp() {

			return $this->timestamp;
		}

		/**
		 * Set the language code in which to return results.
		 *
		 * @link   https://developers.google.com/maps/faq#languagesupport
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  string $language Language code.
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setLanguage( $language ) {

			$this->language = $language;

			return $this;
		}

		/**
		 * Get the language code in which to return results.
		 *
		 * @link   https://developers.google.com/maps/faq#languagesupport
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return string Language code.
		 */
		public function getLanguage() {

			// If the API language parameter was not set, default to the default WP local language.
			if ( empty( $this->language ) ) {

				$this->language = substr( get_locale(), 0, 2 );
			}

			return $this->language;
		}

		/**
		 * Set the API key to authenticate with.
		 *
		 * @link   https://developers.google.com/console/help/new/#UsingKeys
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  string $apiKey API key.
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setApiKey( $apiKey ) {

			$this->apiKey = $apiKey;

			return $this;
		}

		/**
		 * Get the API key to authenticate with.
		 *
		 * @link   https://developers.google.com/console/help/new/#UsingKeys
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return string API key.
		 */
		public function getApiKey() {

			if ( empty( $this->apiKey ) ) {

				$this->apiKey = cnSettingsAPI::get( 'connections', 'google_maps_geocoding_api', 'server_key' );
			}

			return $this->apiKey;
		}

		/**
		 * Set the client ID for Business clients.
		 *
		 * @link   https://developers.google.com/maps/documentation/business/webservices/#client_id
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  string $clientId Client ID.
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setClientId( $clientId ) {

			$this->clientId = $clientId;

			return $this;
		}

		/**
		 * Get the client ID for Business clients.
		 *
		 * @link   https://developers.google.com/maps/documentation/business/webservices/#client_id
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return string Client ID.
		 */
		public function getClientId() {

			return $this->clientId;
		}

		/**
		 * Set the cryptographic signing key for Business clients.
		 *
		 * @link   https://developers.google.com/maps/documentation/business/webservices/#cryptographic_signing_key
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  string $signingKey Cryptographic signing key.
		 *
		 * @return cnGoogleMapsTimeZone
		 */
		public function setSigningKey( $signingKey ) {

			$this->signingKey = $signingKey;

			return $this;
		}

		/**
		 * Get the cryptographic signing key for Business clients.
		 *
		 * @link   https://developers.google.com/maps/documentation/business/webservices/#cryptographic_signing_key
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return string Cryptographic signing key.
		 */
		public function getSigningKey() {

			return $this->signingKey;
		}

		/**
		 * Whether the request is for a Business client.
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @return bool Whether the request is for a Business client.
		 */
		public function isBusinessClient() {

			return $this->getClientId() && $this->getSigningKey();
		}

		/**
		 * Generate the signature for a Business client time zone request.
		 *
		 * @link   https://developers.google.com/maps/documentation/business/webservices/auth#digital_signatures
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @param  string $pathQueryString Path and query string of the request.
		 *
		 * @return string Base64 encoded Signature that's URL safe.
		 */
		protected function generateSignature( $pathQueryString ) {

			$decodedSigningKey = self::base64DecodeUrlSafe( $this->getSigningKey() );

			$signature = hash_hmac( 'sha1', $pathQueryString, $decodedSigningKey, TRUE );
			$signature = self::base64EncodeUrlSafe( $signature );

			return $signature;
		}

		/**
		 * Build the query string with all set parameters of the time zone request.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Requests
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @return string Encoded query string of the time zone request.
		 */
		protected function timezoneQueryString() {

			$queryString = array();

			// Get Latitude and Longitude of the location to get time zone information from.
			$queryString['location'] = $this->getLatitudeLongitude();

			// Get language of the query results.
			// Optional language parameter.
			$queryString['language'] = trim( $this->getLanguage() );

			// Remove any unset parameters.
			$queryString = array_filter( $queryString );

			// Get timestamp as seconds since midnight, January 1, 1970 UTC
			// Optional language parameter (specified after array_filter to prevent deletion when timestamp = 0).
			$queryString['timestamp'] = intval( $this->getTimestamp() );

			// Get point in time to get time zone information from.

			// The signature is added later using the path + query string.
			if ( $this->isBusinessClient() ) {
				$queryString['client'] = $this->getClientId();
			} elseif ( $this->getApiKey() ) {
				$queryString['key'] = $this->getApiKey();
			}

			// Convert array to proper query string.
			return http_build_query( $queryString );
		}

		/**
		 * Build the URL (with query string) of the time zone request.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Requests
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @return string URL of the time zone request.
		 */
		protected function timezoneURL() {

			// HTTPS is always required.
			$scheme = 'https';

			$pathQueryString = self::URL_PATH . 'json' . '?' . $this->timezoneQueryString();

			if ( $this->isBusinessClient() ) {
				$pathQueryString .= '&signature=' . $this->generateSignature( $pathQueryString );
			}

			return $scheme . '://' . self::URL_DOMAIN . $pathQueryString;
		}

		/**
		 * Execute the time zone request. The return type is based on the requested
		 * format: associative array if JSON, SimpleXMLElement object if XML.
		 *
		 * @link   https://developers.google.com/maps/documentation/timezone/intro#Responses
		 *
		 * @access public
		 * @since  8.6.9
		 *
		 * @param  bool $raw Whether to return the raw (string) response.
		 *
		 * @return cnTimezone|string|WP_Error
		 */
		public function queryTimeZone( $raw = FALSE ) {

			$key = $this->getLatitudeLongitude();

			if ( FALSE === $key || 0 === strlen( $key ) ) {

				return new WP_Error(
					'no_latitude_or_longitude', __( 'No latitude or longitude supplied.', 'connections' ),
					$key
				);
			}

			$request = cnCache::get( $key );

			if ( ! $request ) {

				$request = wp_remote_get( $this->timezoneURL() );

				cnCache::set( $key, $request, DAY_IN_SECONDS );
			}

			$content = wp_remote_retrieve_body( $request );

			if ( empty( $content ) ) {

				return new WP_Error(
					'empty_response', __( 'Empty response received.', 'connections' ),
					$request
				);
			}

			if ( 403 == wp_remote_retrieve_response_code( $request ) ) {

				return new WP_Error(
					'response_message', wp_remote_retrieve_response_message( $request ),
					$request
				);
			}

			$response = json_decode( $content );

			if ( is_null( $response ) ) {

				return new WP_Error(
					'empty_response', __( 'Response could not be decoded.', 'connections' ),
					$request
				);
			}

			/**
			 * @link https://developers.google.com/maps/documentation/timezone/intro#Responses
			 */
			switch ( $response->status ) {

				case 'ZERO_RESULTS':

					return new WP_Error(
						'no_results', __( 'Returned zero results.', 'connections' ),
						$response->errorMessage
					);

					break;

				case 'OVER_QUERY_LIMIT':

					return new WP_Error(
						'over_query_limit',
						__( 'Daily query limit has been exceeded.', 'connections' ),
						$response->errorMessage
					);

					break;

				case 'REQUEST_DENIED':

					return new WP_Error(
						'request_denied', __( 'Request has been denied.', 'connections' ), $response->errorMessage
					);

					break;

				case 'INVALID_REQUEST':

					return new WP_Error(
						'invalid_request',
						__( 'An invalid request has been received.', 'connections' ),
						property_exists( $response, 'errorMessage' ) ? $response->errorMessage : $response->status
					);

					break;

				case 'UNKNOWN_ERROR':

					return new WP_Error(
						'unknown_error', __( 'An unknown error has occurred.', 'connections' ), $response->errorMessage
					);

					break;

				case 'OK':

					if ( $raw ) {
						return $content;
					} else {
						return new cnTimezone( $response );
					}

					break;

				default:

					return new WP_Error(
						'unknown_status',
						__( 'An unknown status response has been received.', 'connections' ),
						$response->status
					);
			}

		}

		/**
		 * Encode a string with Base64 using only URL safe characters.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @param  string $value Value to encode.
		 *
		 * @return string encoded Value.
		 */
		protected static function base64EncodeUrlSafe( $value ) {

			return strtr( base64_encode( $value ), '+/', '-_' );
		}

		/**
		 * Decode a Base64 string that uses only URL safe characters.
		 *
		 * @access protected
		 * @since  8.6.9
		 *
		 * @param  string $value Value to decode.
		 *
		 * @return string decoded Value.
		 */
		protected static function base64DecodeUrlSafe( $value ) {

			return base64_decode( strtr( $value, '-_', '+/' ) );
		}
	}
}
