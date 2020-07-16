<?php
namespace Connections_Directory\Entry;

use cnAddress;
use cnEntry;
use cnOutput;
use cnSanitize;
use cnUtility;

/**
 * Class Functions
 *
 * @package Connections_Directory\Entry
 */
class Functions {

	/**
	 * Get related Entries.
	 *
	 * @since 9.8
	 *
	 * @param cnEntry $entry
	 * @param array   $atts
	 *
	 * @return cnOutput[]
	 */
	public static function relatedTo( $entry, $atts = array() ) {

		$related = array();
		$default = array(
			'relation' => 'taxonomy',
			//'taxonomy' => 'category',
			'order_by' => 'id|RANDOM',
			'limit'    => 8,
		);

		$atts = cnSanitize::args( $atts, $default );

		$queryParameters = array(
			'id__not_in' => $entry->getId(),
			'limit'      => 8,
			'order_by'   => $atts['order_by'],
		);

		$address = self::getAddress( $entry );

		/*
		 * `$address` can be `false` or cnAddress object. Address components can be empty strings.
		 * When setting query parameters if `$address` is `false` or address component is empty,
		 * set query parameter to `1=2` as that is unlikely to return matches.
		 *
		 * If an unsupported `relation` is supplied, default to related taxonomy terms.
		 */
		switch ( $atts['relation'] ) {

			case 'last_name':

				$queryParameters['last_name'] = $entry->getName( array( 'format' => '%last%' ) );
				break;

			case 'title':

				$queryParameters['title'] = empty( $entry->getTitle() ) ? '1=2' : $entry->getTitle();
				break;

			case 'organization':

				$queryParameters['organization'] = empty( $entry->getOrganization() ) ? '1=2' : $entry->getOrganization();
				break;

			case 'department':

				$queryParameters['department'] = empty( $entry->getDepartment() ) ? '1=2' : $entry->getDepartment();
				break;

			case 'district':

				$queryParameters['district'] = false === $address || empty( $address->getDistrict() ) ? '1=2' : $address->getDistrict();
				break;

			case 'county':

				$queryParameters['county'] = false === $address || empty( $address->getCounty() ) ? '1=2' : $address->getCounty();
				break;

			case 'locality':

				$queryParameters['city'] = false === $address || empty( $address->getLocality() ) ? '1=2' : $address->getLocality();
				break;

			case 'region':

				$queryParameters['state'] = false === $address || empty( $address->getRegion() ) ? '1=2' : $address->getRegion();
				break;

			case 'postal_code':

				$queryParameters['zip_code'] = false === $address || empty( $address->getPostalCode() ) ? '1=2' : $address->getPostalCode();
				break;

			case 'taxonomy':

				$terms   = $entry->getCategory();
				$termIDs = wp_list_pluck( $terms, 'term_id' );
				$queryParameters['category'] = $termIDs;
				break;

			default:

				return $related;

		}

		$queryParameters = apply_filters(
			'Connections_Directory/Entry/Related/Query_Parameters',
			$queryParameters
		);

		$queryParameters['lock'] = true;

		/*
		 * Callback for the `cn_entry_query_random_seed` filter.
		 *
		 * This is to ensure a different `RAND()` seed value is used when querying related Entries.
		 */
		$seed = function( $seed, $atts ) {

			/*
			 * Make a has from the `cnRetrieve::entries()` $atts.
			 * Limiting has length to `1` because the seed for the `RAND()` function can not
			 * exceed the value for BIGINT.
			 *
			 * This will make a collision in the seed highly likely, per IP address but that should be acceptable
			 * in this use case.
			 */
			$hash = cnUtility::numHash( json_encode( $atts ), 1 );

			return $seed . $hash;
		};

		add_filter( 'cn_entry_query_random_seed', $seed, 10, 2 );

		$results = Connections_Directory()->retrieve->entries( $queryParameters );

		remove_filter( 'cn_entry_query_random_seed', $seed );

		if ( 0 < count( $results ) ) {

			foreach ( $results as $data ) {

				$relation = new cnOutput( $data );
				$relation->directoryHome( $entry->directoryHome ); // Setup the related Entry to the source Entry homepage.

				array_push( $related, $relation );
			}
		}

		return $related;
	}

	/**
	 * Get related Entries.
	 *
	 * @since 9.9
	 *
	 * @param cnEntry $entry
	 * @param array   $atts
	 *
	 * @return cnOutput[]
	 */
	public static function nearBy( $entry, $atts = array() ) {

		$nearBy  = array();
		$default = array(
			'radius' => 10,
			'unit'   => 'mi',
			'limit'  => 8,
		);

		$atts = cnSanitize::args( $atts, $default );

		$address = self::getAddress( $entry );

		$queryParameters = array(
			'id__not_in' => $entry->getId(),
			'latitude'   => $address->getLatitude(),
			'longitude'  => $address->getLongitude(),
			'radius'     => $atts['radius'],
			'unit'       => $atts['unit'],
			'limit'      => $atts['limit'],
			'order_by'   => 'distance',
		);


		$queryParameters = apply_filters(
			'Connections_Directory/Entry/Near/Query_Parameters',
			$queryParameters
		);
		//var_dump( $queryParameters );
		if ( empty( $queryParameters['latitude'] ) && empty( $queryParameters['longitude'] ) ) {

			return $nearBy;
		}

		$queryParameters['lock'] = true;

		/*
		 * Callback for the `cn_entry_query_random_seed` filter.
		 *
		 * This is to ensure a different `RAND()` seed value is used when querying related Entries.
		 */
		$seed = function( $seed, $atts ) {

			/*
			 * Make a has from the `cnRetrieve::entries()` $atts.
			 * Limiting has length to `1` because the seed for the `RAND()` function can not
			 * exceed the value for BIGINT.
			 *
			 * This will make a collision in the seed highly likely, per IP address but that should be acceptable
			 * in this use case.
			 */
			$hash = cnUtility::numHash( json_encode( $atts ), 1 );

			return $seed . $hash;
		};

		add_filter( 'cn_entry_query_random_seed', $seed, 10, 2 );

		$results = Connections_Directory()->retrieve->entries( $queryParameters );

		remove_filter( 'cn_entry_query_random_seed', $seed );

		if ( 0 < count( $results ) ) {

			foreach ( $results as $data ) {

				$relation = new cnOutput( $data );
				$relation->directoryHome( $entry->directoryHome ); // Setup the related Entry to the source Entry homepage.

				array_push( $nearBy, $relation );
			}
		}

		return $nearBy;
	}

	/**
	 * Get preferred Entry address if set, if not, then get first address attached to Entry.
	 *
	 * @since 9.8
	 *
	 * @param cnEntry $entry
	 *
	 * @return bool|cnAddress
	 */
	public static function getAddress( $entry ) {

		// Try to get the preferred address.
		$address = $entry->addresses->getPreferred();

		// If no preferred is set, grab the first address.
		if ( ! $address instanceof cnAddress ) {

			$address = $entry->addresses->getCollection()->first();
		}

		// The filters need to be reset so additional calls to get addresses with different params return expected results.
		$entry->addresses->resetFilters();

		if ( $address instanceof cnAddress ) {

			return $address;
		}

		return false;
	}

	//function popular() {}
}
