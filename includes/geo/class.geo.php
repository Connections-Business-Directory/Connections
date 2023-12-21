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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\Convert\_length;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

/**
 * This Geocoding API is still a work in progress.
 * It is not recommended to be used in production
 * outside the core Connections WordPress plugin.
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
	 * @since 0.7.30
	 *
	 * @param array $origin
	 * @param array $destination
	 * @param array $atts        [optional]
	 *
	 * @return float
	 */
	public static function distance( $origin, $destination, $atts = array() ) {
		$defaultOrig = array(
			'lat' => 0,
			'lng' => 0,
		);

		$orig = wp_parse_args( $origin, $defaultOrig );

		$defaultDest = array(
			'lat' => 0,
			'lng' => 0,
		);

		$dest = wp_parse_args( $destination, $defaultDest );

		$defaults = array(
			'echo' => ! _array::get( $atts, 'return', true ),
		);

		$atts = wp_parse_args( $atts, $defaults );

		$radius = 6371;  // Mean radius in km.

		$degreeLat = deg2rad( $dest['lat'] - $orig['lat'] );
		$degreeLng = deg2rad( $dest['lng'] - $orig['lng'] );

		$a = sin( $degreeLat / 2 ) * sin( $degreeLat / 2 ) + cos( deg2rad( $orig['lat'] ) ) * cos( deg2rad( $dest['lat'] ) ) * sin( $degreeLng / 2 ) * sin( $degreeLng / 2 );
		$c = 2 * asin( sqrt( $a ) );

		$distance = $radius * $c; // Result is in (SI) km.

		if ( $atts['echo'] ) {

			echo $distance; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $distance;
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
	 * @since 0.7.3
	 *
	 * @param array $atts
	 *
	 * @return float|int|string
	 */
	public static function convert( $atts ) {

		_deprecated_function( __METHOD__, '10.3', 'Connections_Directory\Utility\Convert\_length()' );

		$defaults = array(
			'value'         => 0,
			'from'          => 'km',
			'to'            => 'mi',
			'format'        => true,
			// 'suffix'        => true,
			'decimals'      => 2,
			'dec_point'     => '.',
			'thousands_sep' => ',',
			'echo'          => ! _array::get( $atts, 'return', true ),
		);

		$atts = wp_parse_args( $atts, $defaults );

		$length = new _length( $atts['value'], $atts['from'] );
		$value  = $length->to( $atts['to'] );

		// Format the number.
		if ( $atts['format'] ) {

			$value = $length->format( $atts['dec_point'], $atts['thousands_sep'], $atts['decimals'] );
		}

		if ( $atts['echo'] ) {

			echo esc_html( $value );
		}

		return $value;
	}

	///**
	// * Find the n closest locations
	// *
	// * @author https://github.com/luckymushroom/ci_haversine/blob/develop/haversine.php
	// * @param float   $lat latitude of the point of interest
	// * @param float   $lng longitude of the point of interest
	// * @return array
	// */
	//public function closest( $lat, $lng, $max_distance = 25, $max_locations = 10, $units = 'mi', $fields = false ) {
	//	/*
	//     *  Allow for changing of units of measurement
	//     */
	//	switch ( $units ) {
	//	case 'mi':
	//		//radius of the great circle in miles
	//		$gr_circle_radius = 3959;
	//		break;
	//	case 'km':
	//		//radius of the great circle in kilometers
	//		$gr_circle_radius = 6371;
	//		break;
	//	}
	//
	//	/*
	//     *  Support the selection of certain fields
	//     */
	//	if ( ! $fields ) {
	//		$this->db->select( '*' );
	//	}
	//	else {
	//		foreach ( $fields as $field ) {
	//			$this->db->select( $field );
	//		}
	//	}
	//
	//	/*
	//     *  Generate the select field for disctance
	//     */
	//	$disctance_select = sprintf(
	//		"( %d * acos( cos( radians(%s) ) " .
	//		" * cos( radians( lat ) ) " .
	//		" * cos( radians( lng ) - radians(%s) ) " .
	//		" + sin( radians(%s) ) * sin( radians( lat ) ) " .
	//		") " .
	//		") " .
	//		"AS distance",
	//		$gr_circle_radius,
	//		$lat,
	//		$lng,
	//		$lat
	//	);
	//
	//	/*
	//     *  Add distance field
	//     */
	//	$this->db->select( $disctance_select, false );
	//
	//	/*
	//     *  Make sure the results are within the search criteria
	//     */
	//	$this->db->having( 'distance <', $max_distance, false );
	//
	//	/*
	//     *  Limit the number of results that the search will return
	//     */
	//	$this->db->limit( $max_locations );
	//
	//	/*
	//     *  Return the results by the closest locations first
	//     */
	//	$this->db->order_by( 'distance', 'ASC' );
	//
	//	/*
	//     *  Define the table that we are querying
	//     */
	//	$this->db->from( $this->table_name );
	//
	//	$query = $this->db->get();
	//
	//	return $query->result();
	//}

	/**
	 * Retrieve country name based on the country code.
	 *
	 * @since 0.8
	 *
	 * @param string $code The country code.
	 *
	 * @return false|string The country name for the supplied code; FALSE if not found.
	 */
	public static function getCountryByCode( $code ) {

		if ( ! is_string( $code ) || empty( $code ) ) {

			return false;
		}

		$countries = self::getCountries();
		$country   = isset( $countries[ strtoupper( $code ) ] ) ? $countries[ strtoupper( $code ) ] : false;

		return $country;
	}

	/**
	 * Given a country and state code, return the state name
	 *
	 * @since 8.6.13
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
	 * @since 0.8
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
	 * @since 0.8
	 *
	 * @param string $code The country code.
	 *
	 * @return array An associative array where the key is the region abbr and the value is the full region name.
	 */
	public static function getRegions( $code = '' ) {

		if ( empty( $code ) ) {

			$code = cnOptions::getBaseCountry();
		}

		$country = cnCountries::getByCode( $code );
		$regions = null;

		if ( $country instanceof cnCountry ) {

			$regions = $country->getDivisions();
		}

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
	 * @since 0.8
	 *
	 * @return array An associative array of countries where the key is the country abbr and the value is the full country name.
	 */
	public static function getCountries() {

		$translation = cnSettingsAPI::get( 'connections', 'fieldset-address', 'translation' );

		switch ( $translation ) {

			case 'english':
				$translation = 'name';
				break;

			case 'native':
			case 'native_name':
				$translation = 'native_name';
				break;

			default:
				$translation = 'native_name';
		}

		$countries = cnCountries::getAll();
		$countries = wp_list_pluck( $countries, $translation, 'iso_3166_1_alpha2' );
		natsort( $countries );

		if ( 'native_name' === $translation ) {

			/**
			 * Official language of Israel is Hebrew not Arabic.
			 *
			 * @link https://en.wikipedia.org/wiki/Languages_of_Israel#Official_language
			 */
			$countries['IL'] = 'ישראל';

			// Correct Italy. It seems the German is being returned instead of the Italian.
			$countries['IT'] = 'Italia';

			// Correct Spain. It seems the Catalan translation is returned instead of the Spanish.
			$countries['ES'] = 'España';
		}

		// Push a few select countries to the top of the list.
		$countries = array_replace( array( 'US' => '', 'CA' => '', 'GB' => '' ), $countries );

		return apply_filters( 'cn_countries', $countries );
	}

	/**
	 * Get the US regions.
	 *
	 * @since 0.8
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
	 * Retrieve country dial code based on the country code.
	 *
	 * @since 0.8
	 *
	 * @param string $code The country code.
	 *
	 * @return false|string The country dial code for the supplied code; FALSE if not found.
	 */
	public static function getPhoneCodeByCountryCode( $code ) {

		if ( ! is_string( $code ) || empty( $code ) ) {

			return false;
		}

		$phoneCodes = self::getCountryPhoneCodes();
		$phoneCode  = isset( $phoneCodes[ strtoupper( $code ) ] ) ? $phoneCodes[ strtoupper( $code ) ] : false;

		return $phoneCode;
	}

	/**
	 * Retrieve the country phone codes.
	 *
	 * @since 0.8
	 *
	 * @return array An associative array where the key is the country code and the value is the country dial code.
	 */
	public static function getCountryPhoneCodes() {

		$codes = cnCountries::getAll();
		$codes = wp_list_pluck( $codes, 'calling_code', 'iso_3166_1_alpha2' );
		ksort( $codes );

		return apply_filters( 'cn_phone_codes', $codes );
	}
}
