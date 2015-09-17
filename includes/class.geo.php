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
		$googleAddrURL = 'http://maps.googleapis.com/maps/api/geocode/%s?address=%s&sensor=false';

		$defaults = array(
			'provider' => 'google',
			'output' => 'json'
		);

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
			$query = urlencode( utf8_encode( str_replace( ' ', '+', $query ) ) );

			$request = wp_remote_get( sprintf( $googleAddrURL , $atts['output'] , $query ) );
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
	 * Return formatted address use the supplied latitude and longtude.
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
		$googleLatLngURL = 'http://maps.googleapis.com/maps/api/geocode/%s?latlng=%s&sensor=false';

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
	 * @param  string $country The country code.
	 *
	 * @return array           An associative array where the key is the region abbr and the value is the full region
	 *                         name.
	 */
	public static function getRegions( $country = '' ) {

		if ( empty( $country ) ) {

			$country = cnOptions::getBaseCountry();
		}

		$country = strtoupper( $country );

		switch ( $country ) {

			case 'US' :
				$regions = self::US_Regions();
				break;
			case 'CA' :
				$regions = self::CA_Regions();
				break;
			case 'AU' :
				$regions = self::AU_Regions();
				break;
			case 'BD' :
				$regions = self::BD_Regions();
				break;
			case 'BG' :
				$regions = self::BG_Regions();
				break;
			case 'BR' :
				$regions = self::BR_Regions();
				break;
			case 'CN' :
				$regions = self::CN_Regions();
				break;
			case 'ES' :
				$regions = self::ES_Regions();
				break;
			case 'HK' :
				$regions = self::HK_Regions();
				break;
			case 'HU' :
				$regions = self::HU_Regions();
				break;
			case 'ID' :
				$regions = self::ID_Regions();
				break;
			case 'IN' :
				$regions = self::IN_Regions();
				break;
			case 'IR' :
				$regions = self::IR_Regions();
				break;
			case 'IT' :
				$regions = self::IT_Regions();
				break;
			case 'JP' :
				$regions = self::JP_Regions();
				break;
			case 'MX' :
				$regions = self::MX_Regions();
				break;
			case 'MY' :
				$regions = self::MY_Regions();
				break;
			case 'NP' :
				$regions = self::NP_Regions();
				break;
			case 'NZ' :
				$regions = self::NZ_Regions();
				break;
			case 'PE' :
				$regions = self::PE_Regions();
				break;
			case 'TH' :
				$regions = self::TH_Regions();
				break;
			case 'TR' :
				$regions = self::TR_Regions();
				break;
			case 'ZA' :
				$regions = self::ZA_Regions();
				break;
			default :
				$regions = array();
				break;

		};

		return apply_filters( 'cn_regions', $regions, $country );
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

		$countries = array(
			'US' => 'United States',
			'CA' => 'Canada',
			'GB' => 'United Kingdom',
			'AF' => 'Afghanistan',
			'AX' => '&#197;land Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darrussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CD' => 'Congo, Democratic People\'s Republic',
			'CG' => 'Congo, Republic of',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			//'CI' => 'Cote d\'Ivoire',
			'HR' => 'Croatia/Hrvatska',
			'CU' => 'Cuba',
			'CW' => 'Cura&Ccedil;ao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'GQ' => 'Equatorial Guinea',
			'SV' => 'El Salvador',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GR' => 'Greece',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard and McDonald Islands',
			'VA' => 'Holy See (City Vatican State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macau',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova, Republic of',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KR' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territories',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Phillipines',
			'PN' => 'Pitcairn Island',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion Island',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barth&eacute;lemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin (French)',
			'SX' => 'Saint Martin (Dutch)',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'SM' => 'San Marino',
			'ST' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovak Republic',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia',
			'KP' => 'South Korea',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'UY' => 'Uruguay',
			'UM' => 'US Minor Outlying Islands',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (USA)',
			'WF' => 'Wallis and Futuna Islands',
			'EH' => 'Western Sahara',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'YU' => 'Yugoslavia',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);

		return apply_filters( 'cn_countries', $countries );
	}

	/**
	 * Get the US regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function US_Regions() {

		$regions = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AS' => 'American Samoa',
			'CZ' => 'Canal Zone',
			'CM' => 'Commonwealth of the Northern Mariana Islands',
			'FM' => 'Federated regions of Micronesia',
			'GU' => 'Guam',
			'MH' => 'Marshall Islands',
			'MP' => 'Northern Mariana Islands',
			'PW' => 'Palau',
			'PI' => 'Philippine Islands',
			'PR' => 'Puerto Rico',
			'TT' => 'Trust Territory of the Pacific Islands',
			'VI' => 'Virgin Islands',
			'AA' => 'Armed Forces - Americas',
			'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
			'AP' => 'Armed Forces - Pacific'
		);

		return apply_filters( 'cn_us_regions', $regions );
	}

	/**
	 * Get the Bulgarian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function BG_Regions() {

		$regions = array(
			'BG-01' => 'Blagoevgrad',
			'BG-02' => 'Burgas',
			'BG-08' => 'Dobrich',
			'BG-07' => 'Gabrovo',
			'BG-26' => 'Haskovo',
			'BG-09' => 'Kardzhali',
			'BG-10' => 'Kyustendil',
			'BG-11' => 'Lovech',
			'BG-12' => 'Montana',
			'BG-13' => 'Pazardzhik',
			'BG-14' => 'Pernik',
			'BG-15' => 'Pleven',
			'BG-16' => 'Plovdiv',
			'BG-17' => 'Razgrad',
			'BG-18' => 'Ruse',
			'BG-27' => 'Shumen',
			'BG-19' => 'Silistra',
			'BG-20' => 'Sliven',
			'BG-21' => 'Smolyan',
			'BG-23' => 'Sofia',
			'BG-22' => 'Sofia-Grad',
			'BG-24' => 'Stara Zagora',
			'BG-25' => 'Targovishte',
			'BG-03' => 'Varna',
			'BG-04' => 'Veliko Tarnovo',
			'BG-05' => 'Vidin',
			'BG-06' => 'Vratsa',
			'BG-28' => 'Yambol'
		);

		return apply_filters( 'cn_bulgarian_regions', $regions );
	}

	/**
	 * Get the Canadian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function CA_Regions() {

		$regions = array(
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland and Labrador',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon'
		);

		return apply_filters( 'cn_ca_regions', $regions );
	}

	/**
	 * Get the Australian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function AU_Regions() {

		$regions = array(
			'ACT' => 'Australian Capital Territory',
			'NSW' => 'New South Wales',
			'NT'  => 'Northern Territory',
			'QLD' => 'Queensland',
			'SA'  => 'South Australia',
			'TAS' => 'Tasmania',
			'VIC' => 'Victoria',
			'WA'  => 'Western Australia'
		);

		return apply_filters( 'cn_au_regions', $regions );
	}

	/**
	 * Get the Bangladeshi regions (Districts).
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	function BD_Regions() {

		$regions = array(
			'BAG' => 'Bagerhat',
			'BAN' => 'Bandarban',
			'BAR' => 'Barguna',
			'BARI'=> 'Barisal',
			'BHO' => 'Bhola',
			'BOG' => 'Bogra',
			'BRA' => 'Brahmanbaria',
			'CHA' => 'Chandpur',
			'CHI' => 'Chittagong',
			'CHU' => 'Chuadanga',
			'COM' => 'Comilla',
			'COX' => 'Cox\'s Bazar',
			'DHA' => 'Dhaka',
			'DIN' => 'Dinajpur',
			'FAR' => 'Faridpur',
			'FEN' => 'Feni',
			'GAI' => 'Gaibandha',
			'GAZI'=> 'Gazipur',
			'GOP' => 'Gopalganj',
			'HAB' => 'Habiganj',
			'JAM' => 'Jamalpur',
			'JES' => 'Jessore',
			'JHA' => 'Jhalokati',
			'JHE' => 'Jhenaidah',
			'JOY' => 'Joypurhat',
			'KHA' => 'Khagrachhari',
			'KHU' => 'Khulna',
			'KIS' => 'Kishoreganj',
			'KUR' => 'Kurigram',
			'KUS' => 'Kushtia',
			'LAK' => 'Lakshmipur',
			'LAL' => 'Lalmonirhat',
			'MAD' => 'Madaripur',
			'MAG' => 'Magura',
			'MAN' => 'Manikganj',
			'MEH' => 'Meherpur',
			'MOU' => 'Moulvibazar',
			'MUN' => 'Munshiganj',
			'MYM' => 'Mymensingh',
			'NAO' => 'Naogaon',
			'NAR' => 'Narail',
			'NARG'=> 'Narayanganj',
			'NARD'=> 'Narsingdi',
			'NAT' => 'Natore',
			'NAW' => 'Nawabganj',
			'NET' => 'Netrakona',
			'NIL' => 'Nilphamari',
			'NOA' => 'Noakhali',
			'PAB' => 'Pabna',
			'PAN' => 'Panchagarh',
			'PAT' => 'Patuakhali',
			'PIR' => 'Pirojpur',
			'RAJB'=> 'Rajbari',
			'RAJ' => 'Rajshahi',
			'RAN' => 'Rangamati',
			'RANP'=> 'Rangpur',
			'SAT' => 'Satkhira',
			'SHA' => 'Shariatpur',
			'SHE' => 'Sherpur',
			'SIR' => 'Sirajganj',
			'SUN' => 'Sunamganj',
			'SYL' => 'Sylhet',
			'TAN' => 'Tangail',
			'THA' => 'Thakurgaon'
		);

		return apply_filters( 'cn_bangladeshi_regions', $regions );
	}

	/**
	 * Get the Brazil regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function BR_Regions() {

		$regions = array(
			'AC' => 'Acre',
			'AL' => 'Alagoas',
			'AP' => 'Amap&aacute;',
			'AM' => 'Amazonas',
			'BA' => 'Bahia',
			'CE' => 'Cear&aacute;',
			'DF' => 'Distrito Federal',
			'ES' => 'Esp&iacute;rito Santo',
			'GO' => 'Goi&aacute;s',
			'MA' => 'Maranh&atilde;o',
			'MT' => 'Mato Grosso',
			'MS' => 'Mato Grosso do Sul',
			'MG' => 'Minas Gerais',
			'PA' => 'Par&aacute;',
			'PB' => 'Para&iacute;ba',
			'PR' => 'Paran&aacute;',
			'PE' => 'Pernambuco',
			'PI' => 'Piau&iacute;',
			'RJ' => 'Rio de Janeiro',
			'RN' => 'Rio Grande do Norte',
			'RS' => 'Rio Grande do Sul',
			'RO' => 'Rond&ocirc;nia',
			'RR' => 'Roraima',
			'SC' => 'Santa Catarina',
			'SP' => 'S&atilde;o Paulo',
			'SE' => 'Sergipe',
			'TO' => 'Tocantins'
		);

		return apply_filters( 'cn_br_regions', $regions );
	}

	/**
	 * Get the Spain regions.
	 *
	 * @access public
	 * @since  8.1.1
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function ES_Regions() {

		$regions = array(
			'C'  => 'A Coru&ntilde;a',
			'VI' => 'Araba',
			'AB' => 'Albacete',
			'A'  => 'Alicante',
			'AL' => 'Almer&iacute;a',
			'O'  => 'Asturias',
			'AV' => '&Aacute;vila',
			'BA' => 'Badajoz',
			'PM' => 'Baleares',
			'B'  => 'Barcelona',
			'BU' => 'Burgos',
			'CC' => 'C&aacute;ceres',
			'CA' => 'C&aacute;diz',
			'S'  => 'Cantabria',
			'CS' => 'Castell&oacute;n',
			'CE' => 'Ceuta',
			'CR' => 'Ciudad Real',
			'CO' => 'C&oacute;rdoba',
			'CU' => 'Cuenca',
			'GI' => 'Girona',
			'GR' => 'Granada',
			'GU' => 'Guadalajara',
			'SS' => 'Gipuzkoa',
			'H'  => 'Huelva',
			'HU' => 'Huesca',
			'J'  => 'Ja&eacute;n',
			'LO' => 'La Rioja',
			'GC' => 'Las Palmas',
			'LE' => 'Le&oacute;n',
			'L'  => 'Lleida',
			'LU' => 'Lugo',
			'M'  => 'Madrid',
			'MA' => 'M&aacute;laga',
			'ML' => 'Melilla',
			'MU' => 'Murcia',
			'NA' => 'Navarra',
			'OR' => 'Ourense',
			'P'  => 'Palencia',
			'PO' => 'Pontevedra',
			'SA' => 'Salamanca',
			'TF' => 'Santa Cruz de Tenerife',
			'SG' => 'Segovia',
			'SE' => 'Sevilla',
			'SO' => 'Soria',
			'T'  => 'Tarragona',
			'TE' => 'Teruel',
			'TO' => 'Toledo',
			'V'  => 'Valencia',
			'VA' => 'Valladolid',
			'BI' => 'Bizkaia',
			'ZA' => 'Zamora',
			'Z'  => 'Zaragoza'
		);

		return apply_filters( 'cn_es_regions', $regions );
	}

	/**
	 * Get the Hong Kong regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function HK_Regions() {

		$regions = array(
			'HONG KONG'       => 'Hong Kong Island',
			'KOWLOON'         => 'Kowloon',
			'NEW TERRITORIES' => 'New Territories'
		);

		return apply_filters( 'cn_hk_regions', $regions );
	}

	/**
	 * Get the Hungary regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function HU_Regions() {

		$regions = array(
			'BK' => 'Bács-Kiskun',
			'BE' => 'Békés',
			'BA' => 'Baranya',
			'BZ' => 'Borsod-Abaúj-Zemplén',
			'BU' => 'Budapest',
			'CS' => 'Csongrád',
			'FE' => 'Fejér',
			'GS' => 'Győr-Moson-Sopron',
			'HB' => 'Hajdú-Bihar',
			'HE' => 'Heves',
			'JN' => 'Jász-Nagykun-Szolnok',
			'KE' => 'Komárom-Esztergom',
			'NO' => 'Nógrád',
			'PE' => 'Pest',
			'SO' => 'Somogy',
			'SZ' => 'Szabolcs-Szatmár-Bereg',
			'TO' => 'Tolna',
			'VA' => 'Vas',
			'VE' => 'Veszprém',
			'ZA' => 'Zala'
		);

		return apply_filters( 'cn_hu_regions', $regions );
	}

	/**
	 * Get the Chinese regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function CN_Regions() {

		$regions = array(
			'CN1'  => 'Yunnan / &#20113;&#21335;',
			'CN2'  => 'Beijing / &#21271;&#20140;',
			'CN3'  => 'Tianjin / &#22825;&#27941;',
			'CN4'  => 'Hebei / &#27827;&#21271;',
			'CN5'  => 'Shanxi / &#23665;&#35199;',
			'CN6'  => 'Inner Mongolia / &#20839;&#33945;&#21476;',
			'CN7'  => 'Liaoning / &#36797;&#23425;',
			'CN8'  => 'Jilin / &#21513;&#26519;',
			'CN9'  => 'Heilongjiang / &#40657;&#40857;&#27743;',
			'CN10' => 'Shanghai / &#19978;&#28023;',
			'CN11' => 'Jiangsu / &#27743;&#33487;',
			'CN12' => 'Zhejiang / &#27993;&#27743;',
			'CN13' => 'Anhui / &#23433;&#24509;',
			'CN14' => 'Fujian / &#31119;&#24314;',
			'CN15' => 'Jiangxi / &#27743;&#35199;',
			'CN16' => 'Shandong / &#23665;&#19996;',
			'CN17' => 'Henan / &#27827;&#21335;',
			'CN18' => 'Hubei / &#28246;&#21271;',
			'CN19' => 'Hunan / &#28246;&#21335;',
			'CN20' => 'Guangdong / &#24191;&#19996;',
			'CN21' => 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;',
			'CN22' => 'Hainan / &#28023;&#21335;',
			'CN23' => 'Chongqing / &#37325;&#24198;',
			'CN24' => 'Sichuan / &#22235;&#24029;',
			'CN25' => 'Guizhou / &#36149;&#24030;',
			'CN26' => 'Shaanxi / &#38485;&#35199;',
			'CN27' => 'Gansu / &#29976;&#32899;',
			'CN28' => 'Qinghai / &#38738;&#28023;',
			'CN29' => 'Ningxia Hui / &#23425;&#22799;',
			'CN30' => 'Macau / &#28595;&#38376;',
			'CN31' => 'Tibet / &#35199;&#34255;',
			'CN32' => 'Xinjiang / &#26032;&#30086;'
		);

		return apply_filters( 'cn_cn_regions', $regions );
	}

	/**
	 * Get the New Zealand regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function NZ_Regions() {

		$regions = array(
			'AK' => 'Auckland',
			'BP' => 'Bay of Plenty',
			'CT' => 'Canterbury',
			'HB' => 'Hawke&rsquo;s Bay',
			'MW' => 'Manawatu-Wanganui',
			'MB' => 'Marlborough',
			'NS' => 'Nelson',
			'NL' => 'Northland',
			'OT' => 'Otago',
			'SL' => 'Southland',
			'TK' => 'Taranaki',
			'TM' => 'Tasman',
			'WA' => 'Waikato',
			'WE' => 'Wellington',
			'WC' => 'West Coast'
		);

		return apply_filters( 'cn_nz_regions', $regions );
	}

	/**
	 * Get the Indonesian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function ID_Regions() {

		$regions  = array(
			'AC' => 'Daerah Istimewa Aceh',
			'SU' => 'Sumatera Utara',
			'SB' => 'Sumatera Barat',
			'RI' => 'Riau',
			'KR' => 'Kepulauan Riau',
			'JA' => 'Jambi',
			'SS' => 'Sumatera Selatan',
			'BB' => 'Bangka Belitung',
			'BE' => 'Bengkulu',
			'LA' => 'Lampung',
			'JK' => 'DKI Jakarta',
			'JB' => 'Jawa Barat',
			'BT' => 'Banten',
			'JT' => 'Jawa Tengah',
			'JI' => 'Jawa Timur',
			'YO' => 'Daerah Istimewa Yogyakarta',
			'BA' => 'Bali',
			'NB' => 'Nusa Tenggara Barat',
			'NT' => 'Nusa Tenggara Timur',
			'KB' => 'Kalimantan Barat',
			'KT' => 'Kalimantan Tengah',
			'KI' => 'Kalimantan Timur',
			'KS' => 'Kalimantan Selatan',
			'KU' => 'Kalimantan Utara',
			'SA' => 'Sulawesi Utara',
			'ST' => 'Sulawesi Tengah',
			'SG' => 'Sulawesi Tenggara',
			'SR' => 'Sulawesi Barat',
			'SN' => 'Sulawesi Selatan',
			'GO' => 'Gorontalo',
			'MA' => 'Maluku',
			'MU' => 'Maluku Utara',
			'PA' => 'Papua',
			'PB' => 'Papua Barat'
		);

		return apply_filters( 'cn_id_regions', $regions );
	}

	/**
	 * Get the Indian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function IN_Regions() {

		$regions = array(
			'AP' => 'Andhra Pradesh',
			'AR' => 'Arunachal Pradesh',
			'AS' => 'Assam',
			'BR' => 'Bihar',
			'CT' => 'Chhattisgarh',
			'GA' => 'Goa',
			'GJ' => 'Gujarat',
			'HR' => 'Haryana',
			'HP' => 'Himachal Pradesh',
			'JK' => 'Jammu and Kashmir',
			'JH' => 'Jharkhand',
			'KA' => 'Karnataka',
			'KL' => 'Kerala',
			'MP' => 'Madhya Pradesh',
			'MH' => 'Maharashtra',
			'MN' => 'Manipur',
			'ML' => 'Meghalaya',
			'MZ' => 'Mizoram',
			'NL' => 'Nagaland',
			'OR' => 'Orissa',
			'PB' => 'Punjab',
			'RJ' => 'Rajasthan',
			'SK' => 'Sikkim',
			'TN' => 'Tamil Nadu',
			'TG' => 'Telangana',
			'TR' => 'Tripura',
			'UT' => 'Uttarakhand',
			'UP' => 'Uttar Pradesh',
			'WB' => 'West Bengal',
			'AN' => 'Andaman and Nicobar Islands',
			'CH' => 'Chandigarh',
			'DN' => 'Dadar and Nagar Haveli',
			'DD' => 'Daman and Diu',
			'DL' => 'Delhi',
			'LD' => 'Lakshadweep',
			'PY' => 'Pondicherry (Puducherry)'
		);

		return apply_filters( 'cn_in_regions', $regions );
	}

	/**
	 * Get the Iranian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function IR_Regions() {

		$regions = array(
			'KHZ' => 'Khuzestan',
			'THR' => 'Tehran',
			'ILM' => 'Ilaam',
			'BHR' => 'Bushehr',
			'ADL' => 'Ardabil',
			'ESF' => 'Isfahan',
			'YZD' => 'Yazd',
			'KRH' => 'Kermanshah',
			'KRN' => 'Kerman',
			'HDN' => 'Hamadan',
			'GZN' => 'Ghazvin',
			'ZJN' => 'Zanjan',
			'LRS' => 'Luristan',
			'ABZ' => 'Alborz',
			'EAZ' => 'East Azerbaijan',
			'WAZ' => 'West Azerbaijan',
			'CHB' => 'Chaharmahal and Bakhtiari',
			'SKH' => 'South Khorasan',
			'RKH' => 'Razavi Khorasan',
			'NKH' => 'North Khorasan',
			'SMN' => 'Semnan',
			'FRS' => 'Fars',
			'QHM' => 'Qom',
			'KRD' => 'Kurdistan',
			'KBD' => 'Kohgiluyeh and BoyerAhmad',
			'GLS' => 'Golestan',
			'GIL' => 'Gilan',
			'MZN' => 'Mazandaran',
			'MKZ' => 'Markazi',
			'HRZ' => 'Hormozgan',
			'SBN' => 'Sistan and Baluchestan'
		);

		return apply_filters( 'cn_iranian_regions', $regions );
	}

	/**
	 * Get the Italian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function IT_Regions() {

		$regions = array(
			'AG' => 'Agrigento',
			'AL' => 'Alessandria',
			'AN' => 'Ancona',
			'AO' => 'Aosta',
			'AR' => 'Arezzo',
			'AP' => 'Ascoli Piceno',
			'AT' => 'Asti',
			'AV' => 'Avellino',
			'BA' => 'Bari',
			'BT' => 'Barletta-Andria-Trani',
			'BL' => 'Belluno',
			'BN' => 'Benevento',
			'BG' => 'Bergamo',
			'BI' => 'Biella',
			'BO' => 'Bologna',
			'BZ' => 'Bolzano',
			'BS' => 'Brescia',
			'BR' => 'Brindisi',
			'CA' => 'Cagliari',
			'CL' => 'Caltanissetta',
			'CB' => 'Campobasso',
			'CI' => 'Caltanissetta',
			'CE' => 'Caserta',
			'CT' => 'Catania',
			'CZ' => 'Catanzaro',
			'CH' => 'Chieti',
			'CO' => 'Como',
			'CS' => 'Cosenza',
			'CR' => 'Cremona',
			'KR' => 'Crotone',
			'CN' => 'Cuneo',
			'EN' => 'Enna',
			'FM' => 'Fermo',
			'FE' => 'Ferrara',
			'FI' => 'Firenze',
			'FG' => 'Foggia',
			'FC' => 'Forli-Cesena',
			'FR' => 'Frosinone',
			'GE' => 'Genova',
			'GO' => 'Gorizia',
			'GR' => 'Grosseto',
			'IM' => 'Imperia',
			'IS' => 'Isernia',
			'SP' => 'La Spezia',
			'AQ' => 'L&apos;Aquila',
			'LT' => 'Latina',
			'LE' => 'Lecce',
			'LC' => 'Lecco',
			'LI' => 'Livorno',
			'LO' => 'Lodi',
			'LU' => 'Lucca',
			'MC' => 'Macerata',
			'MN' => 'Mantova',
			'MS' => 'Massa-Carrara',
			'MT' => 'Matera',
			'ME' => 'Messina',
			'MI' => 'Milano',
			'MO' => 'Modena',
			'MB' => 'Monza e della Brianza',
			'NA' => 'Napoli',
			'NO' => 'Novara',
			'NU' => 'Nuoro',
			'OT' => 'Olbia-Tempio',
			'OR' => 'Oristano',
			'PD' => 'Padova',
			'PA' => 'Palermo',
			'PR' => 'Parma',
			'PV' => 'Pavia',
			'PG' => 'Perugia',
			'PU' => 'Pesaro e Urbino',
			'PE' => 'Pescara',
			'PC' => 'Piacenza',
			'PI' => 'Pisa',
			'PT' => 'Pistoia',
			'PN' => 'Pordenone',
			'PZ' => 'Potenza',
			'PO' => 'Prato',
			'RG' => 'Ragusa',
			'RA' => 'Ravenna',
			'RC' => 'Reggio Calabria',
			'RE' => 'Reggio Emilia',
			'RI' => 'Rieti',
			'RN' => 'Rimini',
			'RM' => 'Roma',
			'RO' => 'Rovigo',
			'SA' => 'Salerno',
			'VS' => 'Medio Campidano',
			'SS' => 'Sassari',
			'SV' => 'Savona',
			'SI' => 'Siena',
			'SR' => 'Siracusa',
			'SO' => 'Sondrio',
			'TA' => 'Taranto',
			'TE' => 'Teramo',
			'TR' => 'Terni',
			'TO' => 'Torino',
			'OG' => 'Ogliastra',
			'TP' => 'Trapani',
			'TN' => 'Trento',
			'TV' => 'Treviso',
			'TS' => 'Trieste',
			'UD' => 'Udine',
			'VA' => 'Varesa',
			'VE' => 'Venezia',
			'VB' => 'Verbano-Cusio-Ossola',
			'VC' => 'Vercelli',
			'VR' => 'Verona',
			'VV' => 'Vibo Valentia',
			'VI' => 'Vicenza',
			'VT' => 'Viterbo'
		);

		return apply_filters( 'cn_italian_regions', $regions );
	}

	/**
	 * Get the Japanese regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function JP_Regions() {

		$regions = array(
			''     => '',
			'JP01' => 'Hokkaido',
			'JP02' => 'Aomori',
			'JP03' => 'Iwate',
			'JP04' => 'Miyagi',
			'JP05' => 'Akita',
			'JP06' => 'Yamagata',
			'JP07' => 'Fukushima',
			'JP08' => 'Ibaraki',
			'JP09' => 'Tochigi',
			'JP10' => 'Gunma',
			'JP11' => 'Saitama',
			'JP12' => 'Chiba',
			'JP13' => 'Tokyo',
			'JP14' => 'Kanagawa',
			'JP15' => 'Niigata',
			'JP16' => 'Toyama',
			'JP17' => 'Ishikawa',
			'JP18' => 'Fukui',
			'JP19' => 'Yamanashi',
			'JP20' => 'Nagano',
			'JP21' => 'Gifu',
			'JP22' => 'Shizuoka',
			'JP23' => 'Aichi',
			'JP24' => 'Mie',
			'JP25' => 'Shiga',
			'JP26' => 'Kyouto',
			'JP27' => 'Osaka',
			'JP28' => 'Hyougo',
			'JP29' => 'Nara',
			'JP30' => 'Wakayama',
			'JP31' => 'Tottori',
			'JP32' => 'Shimane',
			'JP33' => 'Okayama',
			'JP34' => 'Hiroshima',
			'JP35' => 'Yamaguchi',
			'JP36' => 'Tokushima',
			'JP37' => 'Kagawa',
			'JP38' => 'Ehime',
			'JP39' => 'Kochi',
			'JP40' => 'Fukuoka',
			'JP41' => 'Saga',
			'JP42' => 'Nagasaki',
			'JP43' => 'Kumamoto',
			'JP44' => 'Oita',
			'JP45' => 'Miyazaki',
			'JP46' => 'Kagoshima',
			'JP47' => 'Okinawa'
		);

		return apply_filters( 'cn_japanese_regions', $regions );
	}

	/**
	 * Get the Mexican regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function MX_Regions() {

		$regions = array(
			'DIF' => 'Distrito Federal',
			'JAL' => 'Jalisco',
			'NLE' => 'Nuevo Le&oacute;n',
			'AGU' => 'Aguascalientes',
			'BCN' => 'Baja California Norte',
			'BCS' => 'Baja California Sur',
			'CAM' => 'Campeche',
			'CHP' => 'Chiapas',
			'CHH' => 'Chihuahua',
			'COA' => 'Coahuila',
			'COL' => 'Colima',
			'DUR' => 'Durango',
			'GUA' => 'Guanajuato',
			'GRO' => 'Guerrero',
			'HID' => 'Hidalgo',
			'MEX' => 'Edo. de M&eacute;xico',
			'MIC' => 'Michoac&aacute;n',
			'MOR' => 'Morelos',
			'NAY' => 'Nayarit',
			'OAX' => 'Oaxaca',
			'PUE' => 'Puebla',
			'QUE' => 'Quer&eacute;taro',
			'ROO' => 'Quintana Roo',
			'SLP' => 'San Luis Potos&iacute;',
			'SIN' => 'Sinaloa',
			'SON' => 'Sonora',
			'TAB' => 'Tabasco',
			'TAM' => 'Tamaulipas',
			'TLA' => 'Tlaxcala',
			'VER' => 'Veracruz',
			'YUC' => 'Yucat&aacute;n',
			'ZAC' => 'Zacatecas'
		);

		return apply_filters( 'cn_mexican_regions', $regions );
	}

	/**
	 * Get the Malaysian regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function MY_Regions() {

		$regions = array(
			'JHR' => 'Johor',
			'KDH' => 'Kedah',
			'KTN' => 'Kelantan',
			'MLK' => 'Melaka',
			'NSN' => 'Negeri Sembilan',
			'PHG' => 'Pahang',
			'PRK' => 'Perak',
			'PLS' => 'Perlis',
			'PNG' => 'Pulau Pinang',
			'SBH' => 'Sabah',
			'SWK' => 'Sarawak',
			'SGR' => 'Selangor',
			'TRG' => 'Terengganu',
			'KUL' => 'W.P. Kuala Lumpur',
			'LBN' => 'W.P. Labuan',
			'PJY' => 'W.P. Putrajaya'
		);

		return apply_filters( 'cn_my_regions', $regions );
	}

	/**
	 * Get the Nepalese regions (Districts).
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function NP_Regions() {

		$regions = array(
			'ILL' => 'Illam',
			'JHA' => 'Jhapa',
			'PAN' => 'Panchthar',
			'TAP' => 'Taplejung',
			'BHO' => 'Bhojpur',
			'DKA' => 'Dhankuta',
			'MOR' => 'Morang',
			'SUN' => 'Sunsari',
			'SAN' => 'Sankhuwa',
			'TER' => 'Terhathum',
			'KHO' => 'Khotang',
			'OKH' => 'Okhaldhunga',
			'SAP' => 'Saptari',
			'SIR' => 'Siraha',
			'SOL' => 'Solukhumbu',
			'UDA' => 'Udayapur',
			'DHA' => 'Dhanusa',
			'DLK' => 'Dolakha',
			'MOH' => 'Mohottari',
			'RAM' => 'Ramechha',
			'SAR' => 'Sarlahi',
			'SIN' => 'Sindhuli',
			'BHA' => 'Bhaktapur',
			'DHD' => 'Dhading',
			'KTM' => 'Kathmandu',
			'KAV' => 'Kavrepalanchowk',
			'LAL' => 'Lalitpur',
			'NUW' => 'Nuwakot',
			'RAS' => 'Rasuwa',
			'SPC' => 'Sindhupalchowk',
			'BAR' => 'Bara',
			'CHI' => 'Chitwan',
			'MAK' => 'Makwanpur',
			'PAR' => 'Parsa',
			'RAU' => 'Rautahat',
			'GOR' => 'Gorkha',
			'KAS' => 'Kaski',
			'LAM' => 'Lamjung',
			'MAN' => 'Manang',
			'SYN' => 'Syangja',
			'TAN' => 'Tanahun',
			'BAG' => 'Baglung',
			'PBT' => 'Parbat',
			'MUS' => 'Mustang',
			'MYG' => 'Myagdi',
			'AGR' => 'Agrghakanchi',
			'GUL' => 'Gulmi',
			'KAP' => 'Kapilbastu',
			'NAW' => 'Nawalparasi',
			'PAL' => 'Palpa',
			'RUP' => 'Rupandehi',
			'DAN' => 'Dang',
			'PYU' => 'Pyuthan',
			'ROL' => 'Rolpa',
			'RUK' => 'Rukum',
			'SAL' => 'Salyan',
			'BAN' => 'Banke',
			'BDA' => 'Bardiya',
			'DAI' => 'Dailekh',
			'JAJ' => 'Jajarkot',
			'SUR' => 'Surkhet',
			'DOL' => 'Dolpa',
			'HUM' => 'Humla',
			'JUM' => 'Jumla',
			'KAL' => 'Kalikot',
			'MUG' => 'Mugu',
			'ACH' => 'Achham',
			'BJH' => 'Bajhang',
			'BJU' => 'Bajura',
			'DOT' => 'Doti',
			'KAI' => 'Kailali',
			'BAI' => 'Baitadi',
			'DAD' => 'Dadeldhura',
			'DAR' => 'Darchula',
			'KAN' => 'Kanchanpur'
		);

		return apply_filters( 'cn_nepalese_regions', $regions );
	}

	/**
	 * Get the Peruvian regions.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function PE_Regions() {

		$regions = array(
			'CAL' => 'El Callao',
			'LMA' => 'Municipalidad Metropolitana de Lima',
			'AMA' => 'Amazonas',
			'ANC' => 'Ancash',
			'APU' => 'Apur&iacute;mac',
			'ARE' => 'Arequipa',
			'AYA' => 'Ayacucho',
			'CAJ' => 'Cajamarca',
			'CUS' => 'Cusco',
			'HUV' => 'Huancavelica',
			'HUC' => 'Hu&aacute;nuco',
			'ICA' => 'Ica',
			'JUN' => 'Jun&iacute;n',
			'LAL' => 'La Libertad',
			'LAM' => 'Lambayeque',
			'LIM' => 'Lima',
			'LOR' => 'Loreto',
			'MDD' => 'Madre de Dios',
			'MOQ' => 'Moquegua',
			'PAS' => 'Pasco',
			'PIU' => 'Piura',
			'PUN' => 'Puno',
			'SAM' => 'San Mart&iacute;n',
			'TAC' => 'Tacna',
			'TUM' => 'Tumbes',
			'UCA' => 'Ucayali'
		);

		return apply_filters( 'cn_peruvian_regions', $regions );
	}

	/**
	 * Get the South African regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function ZA_Regions() {

		$regions = array(
			'EC'  => 'Eastern Cape',
			'FS'  => 'Free State',
			'GP'  => 'Gauteng',
			'KZN' => 'KwaZulu-Natal',
			'LP'  => 'Limpopo',
			'MP'  => 'Mpumalanga',
			'NC'  => 'Northern Cape',
			'NW'  => 'North West',
			'WC'  => 'Western Cape'
		);

		return apply_filters( 'cb_za_regions', $regions );
	}

	/**
	 * Get the Thailand regions.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return array An associative array of regions where the key is the region abbr and the value is the full region name.
	 */
	public static function TH_Regions() {

		$regions = array(
			'TH-37' => 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)',
			'TH-15' => 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)',
			'TH-14' => 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)',
			'TH-10' => 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)',
			'TH-38' => 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)',
			'TH-31' => 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)',
			'TH-24' => 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)',
			'TH-18' => 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)',
			'TH-36' => 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)',
			'TH-22' => 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-50' => 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)',
			'TH-57' => 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)',
			'TH-20' => 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)',
			'TH-86' => 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)',
			'TH-46' => 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)',
			'TH-62' => 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)',
			'TH-71' => 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-40' => 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)',
			'TH-81' => 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)',
			'TH-52' => 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)',
			'TH-51' => 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)',
			'TH-42' => 'Loei (&#3648;&#3621;&#3618;)',
			'TH-16' => 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)',
			'TH-58' => 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)',
			'TH-44' => 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)',
			'TH-49' => 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)',
			'TH-26' => 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)',
			'TH-73' => 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)',
			'TH-48' => 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)',
			'TH-30' => 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)',
			'TH-60' => 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)',
			'TH-80' => 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)',
			'TH-55' => 'Nan (&#3609;&#3656;&#3634;&#3609;)',
			'TH-96' => 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)',
			'TH-39' => 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)',
			'TH-43' => 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)',
			'TH-12' => 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-13' => 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)',
			'TH-94' => 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)',
			'TH-82' => 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)',
			'TH-93' => 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)',
			'TH-56' => 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)',
			'TH-67' => 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)',
			'TH-76' => 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)',
			'TH-66' => 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)',
			'TH-65' => 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)',
			'TH-54' => 'Phrae (&#3649;&#3614;&#3619;&#3656;)',
			'TH-83' => 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)',
			'TH-25' => 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-77' => 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)',
			'TH-85' => 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)',
			'TH-70' => 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)',
			'TH-21' => 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)',
			'TH-45' => 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)',
			'TH-27' => 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)',
			'TH-47' => 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)',
			'TH-11' => 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)',
			'TH-74' => 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)',
			'TH-75' => 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)',
			'TH-19' => 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)',
			'TH-91' => 'Satun (&#3626;&#3605;&#3641;&#3621;)',
			'TH-17' => 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)',
			'TH-33' => 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)',
			'TH-90' => 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)',
			'TH-64' => 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)',
			'TH-72' => 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)',
			'TH-84' => 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)',
			'TH-32' => 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)',
			'TH-63' => 'Tak (&#3605;&#3634;&#3585;)',
			'TH-92' => 'Trang (&#3605;&#3619;&#3633;&#3591;)',
			'TH-23' => 'Trat (&#3605;&#3619;&#3634;&#3604;)',
			'TH-34' => 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)',
			'TH-41' => 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)',
			'TH-61' => 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)',
			'TH-53' => 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)',
			'TH-95' => 'Yala (&#3618;&#3632;&#3621;&#3634;)',
			'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)'
		);

		return apply_filters( 'cn_th_regions', $regions );
	}

	/**
	 * Get Turkey States
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public static function TR_Regions() {

		$regions = array(
			'TR01' => 'Adana',
			'TR02' => 'Ad&#305;yaman',
			'TR03' => 'Afyon',
			'TR04' => 'A&#287;r&#305;',
			'TR05' => 'Amasya',
			'TR06' => 'Ankara',
			'TR07' => 'Antalya',
			'TR08' => 'Artvin',
			'TR09' => 'Ayd&#305;n',
			'TR10' => 'Bal&#305;kesir',
			'TR11' => 'Bilecik',
			'TR12' => 'Bing&#246;l',
			'TR13' => 'Bitlis',
			'TR14' => 'Bolu',
			'TR15' => 'Burdur',
			'TR16' => 'Bursa',
			'TR17' => '&#199;anakkale',
			'TR18' => '&#199;ank&#305;kesir',
			'TR19' => '&#199;orum',
			'TR20' => 'Denizli',
			'TR21' => 'Diyarbak&#305;r',
			'TR22' => 'Edirne',
			'TR23' => 'Elaz&#305;&#287;',
			'TR24' => 'Erzincan',
			'TR25' => 'Erzurum',
			'TR26' => 'Eski&#351;ehir',
			'TR27' => 'Gaziantep',
			'TR28' => 'Giresun',
			'TR29' => 'G&#252;m&#252;&#351;hane',
			'TR30' => 'Hakkari',
			'TR31' => 'Hatay',
			'TR32' => 'Isparta',
			'TR33' => '&#304;&#231;el',
			'TR34' => '&#304;stanbul',
			'TR35' => '&#304;zmir',
			'TR36' => 'Kars',
			'TR37' => 'Kastamonu',
			'TR38' => 'Kayseri',
			'TR39' => 'K&#305;rklareli',
			'TR40' => 'K&#305;r&#351;ehir',
			'TR41' => 'Kocaeli',
			'TR42' => 'Konya',
			'TR43' => 'K&#252;tahya',
			'TR44' => 'Malatya',
			'TR45' => 'Manisa',
			'TR46' => 'Kahramanmara&#351;',
			'TR47' => 'Mardin',
			'TR48' => 'Mu&#287;la',
			'TR49' => 'Mu&#351;',
			'TR50' => 'Nev&#351;ehir',
			'TR51' => 'Ni&#287;de',
			'TR52' => 'Ordu',
			'TR53' => 'Rize',
			'TR54' => 'Sakarya',
			'TR55' => 'Samsun',
			'TR56' => 'Siirt',
			'TR57' => 'Sinop',
			'TR58' => 'Sivas',
			'TR59' => 'Tekirda&#287;',
			'TR60' => 'Tokat',
			'TR61' => 'Trabzon',
			'TR62' => 'Tunceli',
			'TR63' => '&#350;anl&#305;urfa',
			'TR64' => 'U&#351;ak',
			'TR65' => 'Van',
			'TR66' => 'Yozgat',
			'TR67' => 'Zonguldak',
			'TR68' => 'Aksaray',
			'TR69' => 'Bayburt',
			'TR70' => 'Karaman',
			'TR71' => 'K&#305;r&#305;kkale',
			'TR72' => 'Batman',
			'TR73' => '&#350;&#305;rnak',
			'TR74' => 'Bart&#305;n',
			'TR75' => 'Ardahan',
			'TR76' => 'I&#287;d&#305;r',
			'TR77' => 'Yalova',
			'TR78' => 'Karab&#252;k',
			'TR79' => 'Kilis',
			'TR80' => 'Osmaniye',
			'TR81' => 'D&#252;zce'
		);

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

		$codes = array(
			'AF' => '+93',
			'AL' => '+355',
			'DZ' => '+213',
			'AS' => '+1',
			'AD' => '+376',
			'AO' => '+244',
			'AI' => '+1',
			'AG' => '+1',
			'AR' => '+54',
			'AM' => '+374',
			'AW' => '+297',
			'AU' => '+61',
			'AT' => '+43',
			'AZ' => '+994',
			'BH' => '+973',
			'BD' => '+880',
			'BB' => '+1',
			'BY' => '+375',
			'BE' => '+32',
			'BZ' => '+501',
			'BJ' => '+229',
			'BM' => '+1',
			'BT' => '+975',
			'BO' => '+591',
			'BA' => '+387',
			'BW' => '+267',
			'BR' => '+55',
			'IO' => '+246',
			'VG' => '+1',
			'BN' => '+673',
			'BG' => '+359',
			'BF' => '+226',
			'MM' => '+95',
			'BI' => '+257',
			'KH' => '+855',
			'CM' => '+237',
			'CA' => '+1',
			'CV' => '+238',
			'KY' => '+1',
			'CF' => '+236',
			'TD' => '+235',
			'CL' => '+56',
			'CN' => '+86',
			'CO' => '+57',
			'KM' => '+269',
			'CK' => '+682',
			'CR' => '+506',
			'CI' => '+225',
			'HR' => '+385',
			'CU' => '+53',
			'CY' => '+357',
			'CZ' => '+420',
			'CD' => '+243',
			'DK' => '+45',
			'DJ' => '+253',
			'DM' => '+1',
			'DO' => '+1',
			'EC' => '+593',
			'EG' => '+20',
			'SV' => '+503',
			'GQ' => '+240',
			'ER' => '+291',
			'EE' => '+372',
			'ET' => '+251',
			'FK' => '+500',
			'FO' => '+298',
			'FM' => '+691',
			'FJ' => '+679',
			'FI' => '+358',
			'FR' => '+33',
			'GF' => '+594',
			'PF' => '+689',
			'GA' => '+241',
			'GE' => '+995',
			'DE' => '+49',
			'GH' => '+233',
			'GI' => '+350',
			'GR' => '+30',
			'GL' => '+299',
			'GD' => '+1',
			'GP' => '+590',
			'GU' => '+1',
			'GT' => '+502',
			'GN' => '+224',
			'GW' => '+245',
			'GY' => '+592',
			'HT' => '+509',
			'HN' => '+504',
			'HK' => '+852',
			'HU' => '+36',
			'IS' => '+354',
			'IN' => '+91',
			'ID' => '+62',
			'IR' => '+98',
			'IQ' => '+964',
			'IE' => '+353',
			'IL' => '+972',
			'IT' => '+39',
			'JM' => '+1',
			'JP' => '+81',
			'JO' => '+962',
			'KZ' => '+7',
			'KE' => '+254',
			'KI' => '+686',
			'XK' => '+381',
			'KW' => '+965',
			'KG' => '+996',
			'LA' => '+856',
			'LV' => '+371',
			'LB' => '+961',
			'LS' => '+266',
			'LR' => '+231',
			'LY' => '+218',
			'LI' => '+423',
			'LT' => '+370',
			'LU' => '+352',
			'MO' => '+853',
			'MK' => '+389',
			'MG' => '+261',
			'MW' => '+265',
			'MY' => '+60',
			'MV' => '+960',
			'ML' => '+223',
			'MT' => '+356',
			'MH' => '+692',
			'MQ' => '+596',
			'MR' => '+222',
			'MU' => '+230',
			'YT' => '+262',
			'MX' => '+52',
			'MD' => '+373',
			'MC' => '+377',
			'MN' => '+976',
			'ME' => '+382',
			'MS' => '+1',
			'MA' => '+212',
			'MZ' => '+258',
			'NA' => '+264',
			'NR' => '+674',
			'NP' => '+977',
			'NL' => '+31',
			'AN' => '+599',
			'NC' => '+687',
			'NZ' => '+64',
			'NI' => '+505',
			'NE' => '+227',
			'NG' => '+234',
			'NU' => '+683',
			'NF' => '+672',
			'KP' => '+850',
			'MP' => '+1',
			'NO' => '+47',
			'OM' => '+968',
			'PK' => '+92',
			'PW' => '+680',
			'PS' => '+970',
			'PA' => '+507',
			'PG' => '+675',
			'PY' => '+595',
			'PE' => '+51',
			'PH' => '+63',
			'PL' => '+48',
			'PT' => '+351',
			'PR' => '+1',
			'QA' => '+974',
			'CG' => '+242',
			'RE' => '+262',
			'RO' => '+40',
			'RU' => '+7',
			'RW' => '+250',
			'BL' => '+590',
			'SH' => '+290',
			'KN' => '+1',
			'MF' => '+590',
			'PM' => '+508',
			'VC' => '+1',
			'WS' => '+685',
			'SM' => '+378',
			'ST' => '+239',
			'SA' => '+966',
			'SN' => '+221',
			'RS' => '+381',
			'SC' => '+248',
			'SL' => '+232',
			'SG' => '+65',
			'SK' => '+421',
			'SI' => '+386',
			'SB' => '+677',
			'SO' => '+252',
			'ZA' => '+27',
			'KR' => '+82',
			'ES' => '+34',
			'LK' => '+94',
			'LC' => '+1',
			'SD' => '+249',
			'SR' => '+597',
			'SZ' => '+268',
			'SE' => '+46',
			'CH' => '+41',
			'SY' => '+963',
			'TW' => '+886',
			'TJ' => '+992',
			'TZ' => '+255',
			'TH' => '+66',
			'BS' => '+1',
			'GM' => '+220',
			'TL' => '+670',
			'TG' => '+228',
			'TK' => '+690',
			'TO' => '+676',
			'TT' => '+1',
			'TN' => '+216',
			'TR' => '+90',
			'TM' => '+993',
			'TC' => '+1',
			'TV' => '+688',
			'UG' => '+256',
			'UA' => '+380',
			'AE' => '+971',
			'GB' => '+44',
			'US' => '+1',
			'UY' => '+598',
			'VI' => '+1',
			'UZ' => '+998',
			'VU' => '+678',
			'VA' => '+39',
			'VE' => '+58',
			'VN' => '+84',
			'WF' => '+681',
			'YE' => '+967',
			'ZM' => '+260',
			'ZW' => '+263'
		);

		return apply_filters( 'cn_phone_codes', $codes );
	}

}
