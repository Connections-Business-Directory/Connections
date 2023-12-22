<?php
namespace Connections_Directory\Entry;

use cnAddress;
use cnEmail_Address;
use cnEntry;
use cnLink;
use cnOutput;
use cnPhone;
use cnSanitize;
use Connections_Directory\Utility\_string;

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
			// 'taxonomy' => 'category',
			'order_by' => 'id|RANDOM',
			'limit'    => 8,
		);

		$atts = cnSanitize::args( $atts, $default );

		$queryParameters = array(
			'id__not_in' => $entry->getId(),
			'limit'      => $atts['limit'],
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
				$terms                       = $entry->getCategory();
				$termIDs                     = wp_list_pluck( $terms, 'term_id' );
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
		$seed = function ( $seed, $atts ) {

			/*
			 * Make a has from the `cnRetrieve::entries()` $atts.
			 * Limiting has length to `1` because the seed for the `RAND()` function can not
			 * exceed the value for BIGINT.
			 *
			 * This will make a collision in the seed highly likely, per IP address but that should be acceptable
			 * in this use case.
			 */
			$hash = _string::toNumericHash( json_encode( $atts ), 1 );

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

		if ( ! $address instanceof cnAddress ) {

			return $nearBy;
		}

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

		if ( empty( $queryParameters['latitude'] ) && empty( $queryParameters['longitude'] ) ) {

			return $nearBy;
		}

		$queryParameters['lock'] = true;

		/*
		 * Callback for the `cn_entry_query_random_seed` filter.
		 *
		 * This is to ensure a different `RAND()` seed value is used when querying related Entries.
		 */
		$seed = function ( $seed, $atts ) {

			/*
			 * Make a has from the `cnRetrieve::entries()` $atts.
			 * Limiting has length to `1` because the seed for the `RAND()` function can not
			 * exceed the value for BIGINT.
			 *
			 * This will make a collision in the seed highly likely, per IP address but that should be acceptable
			 * in this use case.
			 */
			$hash = _string::toNumericHash( json_encode( $atts ), 1 );

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
	 * @param cnEntry $entry Instance of the Entry object.
	 *
	 * @return false|cnAddress
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

	/**
	 * Get preferred Entry phone number if set, if not, then get first phone number attached to Entry.
	 *
	 * @since 10.4.40
	 *
	 * @param cnEntry $entry Instance of the Entry object.
	 *
	 * @return false|cnPhone
	 */
	public static function getPhone( cnEntry $entry ) {

		// Try to get the preferred phone number.
		$phone = $entry->phoneNumbers->getPreferred();

		// If no preferred is set, grab the first phone number.
		if ( ! $phone instanceof cnPhone ) {

			$phone = $entry->phoneNumbers->getCollection()->first();
		}

		// The filters need to be reset so additional calls to get phone numbers with different params return expected results.
		$entry->phoneNumbers->resetFilters();

		if ( $phone instanceof cnPhone ) {

			return $phone;
		}

		return false;
	}

	/**
	 * Get preferred Entry email address if set, if not, then get first email address attached to Entry.
	 *
	 * @since 10.4.40
	 *
	 * @param cnEntry $entry Instance of the Entry object.
	 *
	 * @return false|cnEmail_Address
	 */
	public static function getEmail( cnEntry $entry ) {

		// Try to get the preferred email address.
		$email = $entry->emailAddresses->getPreferred();

		// If no preferred is set, grab the first email address.
		if ( ! $email instanceof cnEmail_Address ) {

			$email = $entry->emailAddresses->getCollection()->first();
		}

		// The filters need to be reset so additional calls to get email addresses with different params return expected results.
		$entry->emailAddresses->resetFilters();

		if ( $email instanceof cnEmail_Address ) {

			return $email;
		}

		return false;
	}

	/**
	 * Get preferred Entry link if set, if not, then get first link attached to Entry.
	 *
	 * @since 10.4.19
	 *
	 * @param cnEntry $entry Instance of the Entry object.
	 *
	 * @return false|cnLink
	 */
	public static function getLink( $entry ) {

		// Try to get the preferred link.
		$link = $entry->links->getPreferred();

		// If no preferred is set, grab the first link.
		if ( ! $link instanceof cnLink ) {

			$link = $entry->links->getCollection()->first();
		}

		// The filters need to be reset so additional calls to get links with different params return expected results.
		$entry->links->resetFilters();

		if ( $link instanceof cnLink ) {

			return $link;
		}

		return false;
	}

	// function popular() {}
}
