<?php

/**
 * Class to geocode addresses.
 *
 * @package     Connections
 * @subpackage  Geocode
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This Geocoding API is still a work in progress.
 * It is not recommended to be used in production
 * outside of the core Connections WordPress plugin.
 *
 * References:
 * http://www.movable-type.co.uk/scripts/latlong.html
 * http://www.movable-type.co.uk/scripts/latlong-db.html
 *
 * http://www.movable-type.co.uk/scripts/latlong-vincenty.html
 * http://www.movable-type.co.uk/scripts/latlong-vincenty-direct.html
 *
 * http://stackoverflow.com/questions/2096385/formulas-to-calculate-geo-proximity
 */
class cnGeo {
	/**
	 * Geocode the supplied address.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version .5
	 * @todo Add support for the bounds, language and regions params
	 * @todo support Open Street Map
	 * @uses wp_parse_args()
	 * @uses wp_remote_get()
	 * @uses wp_remote_retrieve_body()
	 * @param object  $address
	 * @param array   $atts    [optional]
	 * @return object
	 */
	public static function address( $address , $atts = array() ) {
		$result = new stdClass();
		$query = array();
		$googleAddrURL = 'https://maps.googleapis.com/maps/api/geocode/%s?address=%s';

		$defaults = array(
			'provider' => 'google',
			'output' => 'json'
		);

		$key = cnSettingsAPI::get( 'connections', 'google_maps_geocoding_api', 'server_key' );

		if ( 0 < strlen( $key ) ) {

			$googleAddrURL = $googleAddrURL . '&key=' . urlencode( $key );
		}

		$atts = wp_parse_args( $atts, $defaults );

		if ( is_array( $address ) ) $address = (object) $address;

		switch ( $atts['provider'] ) {
		case 'google':

			if ( ! empty( $address->line_1 ) ) $query[] = trim( $address->line_1 );
			if ( ! empty( $address->line_2 ) ) $query[] = trim( $address->line_2 );
			if ( ! empty( $address->line_3 ) ) $query[] = trim( $address->line_3 );
			if ( ! empty( $address->city ) ) $query[] = trim( $address->city );
			if ( ! empty( $address->state ) ) $query[] = trim( $address->state );
			if ( ! empty( $address->zipcode ) ) $query[] = trim( $address->zipcode );
			if ( ! empty( $address->country ) ) $query[] = trim( $address->country );

			// Convert the array to a string for the URL
			$query = implode( ',' , $query );

			// Remove non alpha numeric chars such as extra spaces and replace w/ a plus.
			//$query = preg_replace("[^A-Za-z0-9]", '+', $query );
			//$query = urlencode( utf8_encode( str_replace( ' ', '+', $query ) ) );
			$query = urlencode( $query );
			$query = sprintf( $googleAddrURL , $atts['output'] , $query );

			$request = wp_remote_get( $query );
			$body = wp_remote_retrieve_body( $request );

			if ( $body ) {
				$json = json_decode( $body );

				if ( is_object( $json ) && 'OK' === $json->status ) {
					// Rewrite the response from the Google API to be a bit more user friendly even though the nomenclature may not be the most accurate.
					// Address types from the API are documented here:  https://developers.google.com/maps/documentation/geocoding/#Types

					// The formatted address.
					$result->formatted = $json->results[0]->formatted_address;

					// Setup the rest of the properties.
					$result->street_number = '';
					$result->route = '';
					$result->locality = '';
					$result->region = '';
					$result->region_abbr = '';
					$result->county = '';
					$result->township = '';
					$result->postal_code = '';
					$result->country = '';
					$result->country_abbr = '';

					foreach ( $json->results[0]->address_components as $component ) {
						if ( isset( $component->types ) ) {
							// Street Number
							if ( in_array( 'street_number' , $component->types ) ) $result->street_number = $component->long_name;

							// Road
							if ( in_array( 'route' , $component->types ) ) $result->route = $component->long_name;

							// City
							if ( in_array( 'locality' , $component->types ) ) $result->locality = $component->long_name;

							// State || Province or similar based on country political boundaries.
							if ( in_array( 'administrative_area_level_1' , $component->types ) ) $result->region = $component->long_name;
							if ( in_array( 'administrative_area_level_1' , $component->types ) ) $result->region_abbr = $component->short_name;

							// County or similar based on country political boundaries.
							if ( in_array( 'administrative_area_level_2' , $component->types ) ) $result->county = $component->long_name;

							// Township or similar based on country political boundaries.
							if ( in_array( 'administrative_area_level_3' , $component->types ) ) $result->township = $component->long_name;

							// Postal Code
							if ( in_array( 'postal_code' , $component->types ) ) $result->postal_code = $component->long_name;

							// Country
							if ( in_array( 'country' , $component->types ) ) $result->country = $component->long_name;
							if ( in_array( 'country' , $component->types ) ) $result->country_abbr = $component->short_name;
						}
					}

					$result->latitude = $json->results[0]->geometry->location->lat;
					$result->longitude = $json->results[0]->geometry->location->lng;
					//var_dump($json);
					//var_dump($result);die;
				}
			}

			break;

		case 'osm':

			break;
		}

		return $result;
	}

	/**
	 * Return formatted address use the supplied latitude and longitude.
	 *
	 * $point['lat'] (float) The latitude.
	 * $point['lng'] (float) The longitude.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version .1
	 * @todo Add support for the bounds, language and regions params
	 * @todo support Open Street Map
	 * @uses wp_parse_args()
	 * @uses wp_remote_get()
	 * @uses wp_remote_retrieve_body()
	 * @param array   $point
	 * @param array   $atts  [optional]
	 * @return object
	 */
	public static function reverse( $point , $atts = array() ) {
		$result = new stdClass();
		$query = array();
		$googleLatLngURL = 'https://maps.googleapis.com/maps/api/geocode/%s?latlng=%s&sensor=false';

		$defaults = array(
			'provider' => 'google',
			'output' => 'json'
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( is_array( $point ) ) $point = (object) $point;

		switch ( $atts['provider'] ) {
		case 'google':

			if ( ! empty( $point->lat ) ) $query[] = trim( $point->lat );
			if ( ! empty( $point->lng ) ) $query[] = trim( $point->lng );

			// Convert the array to a string for the URL
			$query = implode( ',' , $query );

			$query = urlencode( utf8_encode( str_replace( ' ', '+', $query ) ) );

			$request = wp_remote_get( sprintf( $googleLatLngURL , $atts['output'] , $query ) );
			$body = wp_remote_retrieve_body( $request );

			if ( $body ) {
				$json = json_decode( $body );

				if ( $json->status === 'OK' ) {
					// Rewrite the responce from the Google API to be a bit more user friendly even though the nomenclature may not be the most accurate.
					// Address types from the API are documented here:  https://developers.google.com/maps/documentation/geocoding/#Types

					// The formatted address.
					$result->formatted = $json->results[0]->formatted_address;

					// Setup the rest of the properties.
					$result->street_number = '';
					$result->route = '';
					$result->locality = '';
					$result->region = '';
					$result->region_abbr = '';
					$result->county = '';
					$result->township = '';
					$result->postal_code = '';
					$result->country = '';
					$result->country_abbr = '';

					foreach ( $json->results[0]->address_components as $component ) {
						if ( isset( $component->types ) ) {
							// Street Number
							if ( in_array( 'street_number' , $component->types ) ) $result->street_number = $component->long_name;

							// Road
							if ( in_array( 'route' , $component->types ) ) $result->route = $component->long_name;

							// City
							if ( in_array( 'locality' , $component->types ) ) $result->locality = $component->long_name;

							// State || Province or similar based on country polical boundries.
							if ( in_array( 'administrative_area_level_1' , $component->types ) ) $result->region = $component->long_name;
							if ( in_array( 'administrative_area_level_1' , $component->types ) ) $result->region_abbr = $component->short_name;

							// County or similar based on country polical boundries.
							if ( in_array( 'administrative_area_level_2' , $component->types ) ) $result->county = $component->long_name;

							// Township or similar based on country polical boundries.
							if ( in_array( 'administrative_area_level_3' , $component->types ) ) $result->township = $component->long_name;

							// Postal Code
							if ( in_array( 'postal_code' , $component->types ) ) $result->postal_code = $component->long_name;

							// Country
							if ( in_array( 'country' , $component->types ) ) $result->country = $component->long_name;
							if ( in_array( 'country' , $component->types ) ) $result->country_abbr = $component->short_name;
						}
					}

					$result->latitude = $json->results[0]->geometry->location->lat;
					$result->longitude = $json->results[0]->geometry->location->lng;
					//var_dump($json);
					//var_dump($result);die;
				}
			}

			break;

		case 'osm':

			break;
		}

		return $result;
	}

	/**
	 * Return the distance between two lat and lng points using the haversine formula.
	 *
	 * Accepted options for the $origin property are:
	 *  lat (float) The origin latitude coordinate
	 *  lng (float) The origin latitude coordinate
	 *
	 * Accepted options for the $destination property are:
	 *  lat (float) The destination latitude coordinate
	 *  lng (float) The destination latitude coordinate
	 *
	 * Accepted options for the $destination $atts are:
	 *  return (bool) Return or echo the string. Default is to echo.
	 *
	 * NOTE:
	 * Credit for the haversine formula in PHP:
	 * http://www.codecodex.com/wiki/Calculate_Distance_Between_Two_Points_on_a_Globe#PHP
	 *
	 * @access public
	 * @since 0.7.30
	 * @version 1.0
	 * @uses wp_parse_args()
	 * @param array   $origin
	 * @param array   $destination
	 * @param array   $atts        [optional]
	 * @return float
	 */
	public static function distance( $origin , $destination , $atts = array() ) {
		$defaultOrig = array(
			'lat' => 0,
			'lng' => 0
		);

		$orig = wp_parse_args( $origin, $defaultOrig );

		$defaultDest = array(
			'lat' => 0,
			'lng' => 0
		);

		$dest = wp_parse_args( $destination, $defaultDest );

		$defaults = array(
			'return' => TRUE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$radius = 6371;  // Mean radius in km.

		$degreeLat = deg2rad( $dest['lat'] - $orig['lat'] );
		$degreeLng = deg2rad( $dest['lng'] - $orig['lng'] );

		$a = sin( $degreeLat/2 ) * sin( $degreeLat/2 ) + cos( deg2rad( $orig['lat'] ) ) * cos( deg2rad( $dest['lat'] ) ) * sin( $degreeLng/2 ) * sin( $degreeLng/2 );
		$c = 2 * asin( sqrt( $a ) );
		$distance = $radius * $c; // Result is in (SI) km.

		if ( $atts['return'] ) return $distance;
		echo $distance;
	}

	/**
	 * Convert a supplied number to the desired unit.
	 *
	 * Accepted options for the $atts property are:
	 *  value (float) The number to convert.
	 *  from (string) The lowercase abbr of the unit to convert from.
	 *  to (string) The lowercase abbr of the unit to conver to.
	 *  format (bool) Whether or not to format the number.
	 *  suffix (bool) Whether or not to add the unit suffix if the number is being formatted.
	 *  decimals (int) The number of decimal places of the formatted number.
	 *  dec_point (string) The char to be used for the decimal.
	 *  thousands_sep (string) The char to be used for the thousands separator
	 *  return (bool) Return or echo the string. Default is to echo.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @uses wp_parse_args()
	 * @param array  $atts
	 * @return mixed [int, float, string]
	 */
	static public function convert( $atts ) {
		$result = 0;
		$meters = 0;

		$defaults = array(
			'value' => 0,
			'from' => 'km',
			'to' => 'mi',
			'format' => TRUE,
			'suffix' => TRUE,
			'decimals' => 2,
			'dec_point' => '.',
			'thousands_sep' => ',',
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$ratio = array(
			'mm' => 1000, // (si) millimeters
			'cm' => 100, // (si) centimeters
			'dm' => 10,  // (si) decimeters
			'm' => 1,  // (si) meters
			'dam' => .1, // (si) decameters
			'hm' => .01, // (si) hectometers
			'km' => .001, // (si) kilometers
			'in' => 39.37007874016,
			'feet' => 3.280839895013,
			'yd' => 1.093613298338,
			'mi' => 0.0006213711922373,
			'li' => 4.9709695378987, // US survey -- link
			'sft' => 3.2808334366796, // US survey -- survey foot
			'rd' => 0.198838781516,  // US survey -- rod
			'ch' => 0.04970969537899, // US survey -- chain
			'fur' => 0.004970969537899, // US survey -- furlong
			'smi' => 0.00062136994937697, // US survey -- survey mile
			'lea' => 0.0002071237307458, // US survey -- league
			'nmi' => 0.000539956803456,  // Nautical Mile
		);

		// If the supplied units to convert from and to are not in the $ratio array, return FALSE.
		if ( ! array_key_exists( $atts['to'], $ratio ) && ! array_key_exists( $atts['from'], $ratio ) ) return FALSE;

		// If no value was supplied, return 0.
		if ( $atts['value'] == 0 ) return 0;

		// Convert to si (m)
		$meters = $atts['value'] * ( 1 / $ratio[$atts['from']] );;

		// Convert to desired unit
		$result = $meters * $ratio[$atts['to']];

		// Format the number.
		if ( $atts['format'] ) {
			$result = number_format( $result, $atts['decimals'], $atts['dec_point'], $atts['thousands_sep'] );

			// Add the unit suffix.
			if ( $atts['suffix'] ) $result = $result . $atts['to'];
		}

		if ( $atts['return'] ) return $result;
		echo $result;
	}

	/**
	 * Find the n closest locations
	 *
	 * @author https://github.com/luckymushroom/ci_haversine/blob/develop/haversine.php
	 * @param float   $lat latitude of the point of interest
	 * @param float   $lng longitude of the point of interest
	 * @return array
	 */
	public function closest( $lat, $lng, $max_distance = 25, $max_locations = 10, $units = 'mi', $fields = false ) {
		/*
         *  Allow for changing of units of measurement
         */
		switch ( $units ) {
		case 'mi':
			//radius of the great circle in miles
			$gr_circle_radius = 3959;
			break;
		case 'km':
			//radius of the great circle in kilometers
			$gr_circle_radius = 6371;
			break;
		}

		/*
         *  Support the selection of certain fields
         */
		if ( ! $fields ) {
			$this->db->select( '*' );
		}
		else {
			foreach ( $fields as $field ) {
				$this->db->select( $field );
			}
		}

		/*
         *  Generate the select field for disctance
         */
		$disctance_select = sprintf(
			"( %d * acos( cos( radians(%s) ) " .
			" * cos( radians( lat ) ) " .
			" * cos( radians( lng ) - radians(%s) ) " .
			" + sin( radians(%s) ) * sin( radians( lat ) ) " .
			") " .
			") " .
			"AS distance",
			$gr_circle_radius,
			$lat,
			$lng,
			$lat
		);

		/*
         *  Add distance field
         */
		$this->db->select( $disctance_select, false );

		/*
         *  Make sure the results are within the search criteria
         */
		$this->db->having( 'distance <', $max_distance, false );

		/*
         *  Limit the number of results that the search will return
         */
		$this->db->limit( $max_locations );

		/*
         *  Return the results by the closest locations first
         */
		$this->db->order_by( 'distance', 'ASC' );

		/*
         *  Define the table that we are querying
         */
		$this->db->from( $this->table_name );

		$query = $this->db->get();

		return $query->result();
	}

	/**
	 * Retrieve country name based on the country code.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  string $code The country code.
	 *
	 * @return mixed  string | bool The country name for the supplied code; FALSE if not found.
	 */
	public static function getCountryByCode( $code ) {

		if ( ! is_string( $code ) || empty( $code ) ) {

			return FALSE;
		}

		$countries = self::getCountries();
		$country   = isset( $countries[ strtoupper( $code ) ] ) ? $countries[ strtoupper( $code ) ] : FALSE;

		return $country;
	}

	/**
	 * Given a country and state code, return the state name
	 *
	 * @access public
	 * @since  8.6.13
	 * @static
	 *
	 * @param string $country_code The ISO Code for the country.
	 * @param string $region_code  The ISO Code for the region.
	 *
	 * @return string
	 */
	public static function getRegionName( $country_code = '', $region_code = '' ) {

		$regions = self::getRegions( $country_code );
		$name    = isset( $regions[ $region_code ] ) ? $regions[ $region_code ] : $region_code;

		return apply_filters( 'cn_get_region_name', $name, $region_code );
	}

	/**
	 * Return all country codes.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An indexed array of country codes.
	 */
	public static function getCountryCodes() {

		$keys = array_keys( self::getCountries() );

		return $keys;
	}

	/**
	 * Retrieve regions based on country code.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @param  string $code The country code.
	 *
	 * @return array An associative array where the key is the region abbr and the value is the full region name.
	 */
	public static function getRegions( $code = '' ) {

		if ( empty( $code ) ) {

			$code = cnOptions::getBaseCountry();
		}

		$regions = cnCountries::getByCode( $code )->getDivisions();

		if ( ! is_null( $regions ) && is_array( $regions ) ) {

			$regions = wp_list_pluck( $regions, 'name' );
			natsort( $regions );

		} else {

			$regions = array();
		}

		return apply_filters( 'cn_regions', apply_filters( "cn_{$code}_regions", $regions ), $code );
	}

	/**
	 * Get countries.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of countries where the key is the country abbr and the value is the full country name.
	 */
	public static function getCountries() {

		$countries = cnCountries::getAll();
		$countries = wp_list_pluck( $countries, 'name', 'iso_3166_1_alpha2' );
		natsort( $countries );

		// Push a few select countries to the top of the list.
		$countries = array_replace( array( 'US' => '', 'CA' => '', 'GB' => '' ), $countries );

		return apply_filters( 'cn_countries', $countries );
	}

	/**
	 * Get the US regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function US_Regions() {

		$regions = cnCountries::getByCode( 'us' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_us_regions', $regions );
	}

	/**
	 * Get Angola regions.
	 *
	 * @access public
	 * @since  8.6.10
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array $regions A list of regions
	 */
	public static function AO_Regions() {

		$regions = cnCountries::getByCode( 'ao' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_angola_regions', $regions );
	}

	/**
	 * Get the Bulgarian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function BG_Regions() {

		$regions = cnCountries::getByCode( 'bg' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_bulgarian_regions', $regions );
	}

	/**
	 * Get the Canadian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function CA_Regions() {

		$regions = cnCountries::getByCode( 'ca' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_ca_regions', $regions );
	}

	/**
	 * Get the Australian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function AU_Regions() {

		$regions = cnCountries::getByCode( 'au' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_au_regions', $regions );
	}

	/**
	 * Get the Bangladeshi regions (Districts).
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function BD_Regions() {

		$regions = cnCountries::getByCode( 'bd' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_bangladeshi_regions', $regions );
	}

	/**
	 * Get the Brazil regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function BR_Regions() {

		$regions = cnCountries::getByCode( 'br' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_br_regions', $regions );
	}

	/**
	 * Get the United Kingdom regions (counties).
	 *
	 * @access public
	 * @since  8.6.13
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function GB_Regions() {

		$regions = cnCountries::getByCode( 'gb' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );
		return apply_filters( 'cn_gb_regions', $regions );
	}

	/**
	 * Get the Spain regions.
	 *
	 * @access public
	 * @since  8.1.1
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function ES_Regions() {

		$regions = cnCountries::getByCode( 'es' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_es_regions', $regions );
	}

	/**
	 * Get the Hong Kong regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function HK_Regions() {

		$regions = cnCountries::getByCode( 'hk' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_hk_regions', $regions );
	}

	/**
	 * Get the Hungary regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function HU_Regions() {

		$regions = cnCountries::getByCode( 'hu' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_hu_regions', $regions );
	}

	/**
	 * Get the Chinese regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function CN_Regions() {

		$regions = cnCountries::getByCode( 'cn' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_cn_regions', $regions );
	}

	/**
	 * Get the New Zealand regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function NZ_Regions() {

		$regions = cnCountries::getByCode( 'nz' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_nz_regions', $regions );
	}

	/**
	 * Get the Indonesian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function ID_Regions() {

		$regions = cnCountries::getByCode( 'id' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_id_regions', $regions );
	}

	/**
	 * Get the Indian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function IN_Regions() {

		$regions = cnCountries::getByCode( 'in' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_in_regions', $regions );
	}

	/**
	 * Get the Iranian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function IR_Regions() {

		$regions = cnCountries::getByCode( 'ir' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_iranian_regions', $regions );
	}

	/**
	 * Get the Italian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function IT_Regions() {

		$regions = cnCountries::getByCode( 'it' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_italian_regions', $regions );
	}

	/**
	 * Get the Japanese regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function JP_Regions() {

		$regions = cnCountries::getByCode( 'jp' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_japanese_regions', $regions );
	}

	/**
	 * Get the Mexican regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function MX_Regions() {

		$regions = cnCountries::getByCode( 'mx' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_mexican_regions', $regions );
	}

	/**
	 * Get the Malaysian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function MY_Regions() {

		$regions = cnCountries::getByCode( 'my' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_my_regions', $regions );
	}

	/**
	 * Get the Nepalese regions (Districts).
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function NP_Regions() {

		$regions = cnCountries::getByCode( 'np' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_nepalese_regions', $regions );
	}

	/**
	 * Get the Peruvian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function PE_Regions() {

		$regions = cnCountries::getByCode( 'pe' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_peruvian_regions', $regions );
	}

	/**
	 * Get the South African regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function ZA_Regions() {

		$regions = cnCountries::getByCode( 'za' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cb_za_regions', $regions );
	}

	/**
	 * Get the Thailand regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function TH_Regions() {

		$regions = cnCountries::getByCode( 'th' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_th_regions', $regions );
	}

	/**
	 * Get Turkey States
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @deprecated 8.7
	 *
	 * @return array $states A list of states
	 */
	public static function TR_Regions() {

		$regions = cnCountries::getByCode( 'tr' )->getDivisions();
		$regions = wp_list_pluck( $regions, 'name' );
		natsort( $regions );

		return apply_filters( 'cn_turkey_regions', $regions );
	}

	/**
	 * Retrieve country dial code based on the country code.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  string $code The country code.
	 *
	 * @return mixed  string | bool The country dial code for the supplied code; FALSE if not found.
	 */
	public static function getPhoneCodeByCountryCode( $code ) {

		if ( ! is_string( $code ) || empty( $code ) ) {

			return FALSE;
		}

		$phoneCodes = self::getCountryPhoneCodes();
		$phoneCode  = isset( $phoneCodes[ strtoupper( $code ) ] ) ? $phoneCodes[ strtoupper( $code ) ] : FALSE;

		return $phoneCode;
	}

	/**
	 * Retrieve the country phone codes.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array  An associative array where the key is the country code and the values is the country dial code.
	 */
	public static function getCountryPhoneCodes(){

		$codes = cnCountries::getAll();
		$codes = wp_list_pluck( $codes, 'calling_code', 'iso_3166_1_alpha2' );
		ksort( $codes );

		return apply_filters( 'cn_phone_codes', $codes );
	}

}
