<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnAddress;
use cnEntry;
use cnSettingsAPI;
use Connections_Directory\Content_Block;
use Connections_Directory\Entry\Functions as Entry_Helper;

/**
 * Class Google_Static_Map
 *
 * @package Connections_Directory\Content_Block
 */
class Google_Static_Map extends Content_Block {

	/**
	 * @since 9.7
	 * @var string
	 */
	const ID = 'google-static-map';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'maptype' => 'ROADMAP',
		'zoom'    => 13,
		'height'  => 400,
		'width'   => 640,
	);

	/**
	 * Google_Static_Map constructor.
	 *
	 * @param string $id
	 */
	public function __construct( $id ) {

		$atts = array(
			'name'                => __( 'Map: Static Google Map', 'connections' ),
			'register_option'     => false,
			'permission_callback' => array( $this, 'permission' ),
			'heading'             => __( 'Map', 'connections' ),
		);

		parent::__construct( $id, $atts );

		$this->setProperties( $this->properties );
	}

	/**
	 * @since 9.7
	 *
	 * @param string $property
	 * @param mixed  $value
	 */
	public function set( $property, $value ) {

		switch ( $property ) {

			case 'maptype':
				// Limit the map type to one of the valid types to prevent user error.
				$permittedMapTypes = array( 'HYBRID', 'ROADMAP', 'SATELLITE', 'TERRAIN' );

				$value = strtoupper( $value );
				$value = in_array( $value, $permittedMapTypes ) ? $value : 'ROADMAP';

				break;

			case 'height':
			case 'width':
				$value = absint( $value );

				$value = filter_var(
					$value,
					FILTER_VALIDATE_INT,
					array(
						'options' => array(
							'default'   => 640,
							'min_range' => 100,
							'max_range' => 640,
						),
					)
				);

				break;

			case 'zoom':
				$value = absint( $value );

				$value = filter_var(
					$value,
					FILTER_VALIDATE_INT,
					array(
						'options' => array(
							'default'   => 13,
							'min_range' => 0,
							'max_range' => 21,
						),
					)
				);

				break;
		}

		parent::set( $property, $value );
	}

	/**
	 * @since 9.7
	 *
	 * @return bool
	 */
	public function permission() {

		return true;
	}

	/**
	 * Renders the Google Static Map Content Block.
	 *
	 * @since 9.7
	 */
	public function content() {

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		// $address = $this->getAddress( $entry );
		$address = Entry_Helper::getAddress( $entry );

		if ( ! $address instanceof cnAddress ) {

			return;
		}

		$googleMapsAPIBrowserKey = cnSettingsAPI::get(
			'connections',
			'google_maps_geocoding_api',
			'browser_key'
		);

		$height = $this->get( 'height', 400 );
		$width  = $this->get( 'width', 400 );
		$query  = $this->createQueryString( $address, $googleMapsAPIBrowserKey );
		$url    = "https://maps.googleapis.com/maps/api/staticmap?{$query}";

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
			$entry
		);

		echo '<span class="cn-image-style" style="display: inline-block;">';
		echo '<span class="cn-image" style="height: ' . absint( $height ) . 'px; width: ' . absint( $width ) . 'px">';
		echo '<img width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $url ) . '"/>';
		echo '</span>';
		echo '</span>';

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
			$entry
		);
	}

	///**
	// * Get preferred Entry address if set, if not, then get first address attached to Entry.
	// *
	// * @since 9.7
	// *
	// * @param cnEntry $entry
	// *
	// * @return bool|cnAddress
	// */
	//private function getAddress( $entry ) {
	//
	//	// Try to get the preferred address.
	//	$address = $entry->addresses->getPreferred();
	//
	//	// If no preferred is set, grab the first address.
	//	if ( ! $address instanceof cnAddress ) {
	//
	//		$address = $entry->addresses->getCollection()->first();
	//	}
	//
	//	// The filters need to be reset so additional calls to get addresses with different params return expected results.
	//	$entry->addresses->resetFilters();
	//
	//	if ( $address instanceof cnAddress ) {
	//
	//		return $address;
	//	}
	//
	//	return false;
	//}

	/**
	 * @since 9.7
	 *
	 * @param cnAddress $address
	 * @param string    $key
	 *
	 * @return string
	 */
	private function createQueryString( $address, $key ) {

		$query = array();
		$adr   = array();

		if ( ! empty( $latitude = $address->getLatitude() ) && ! empty( $longitude = $address->getLongitude() ) ) {

			$adr['latitude']  = $latitude;
			$adr['longitude'] = $longitude;

		} else {

			array_push( $adr, $address->getLineOne() );
			array_push( $adr, $address->getLineTwo() );
			array_push( $adr, $address->getLocality() );
			array_push( $adr, $address->getRegion() );
			array_push( $adr, $address->getPostalCode() );
		}

		$query['center']  = implode( ',', $adr );
		$query['markers'] = $query['center'];
		$query['size']    = $this->get( 'width', 640 ) . 'x' . $this->get( 'height', 400 );
		$query['maptype'] = strtoupper( $this->get( 'maptype', 'ROADMAP' ) );
		$query['zoom']    = $this->get( 'zoom', 13 );
		// $query['scale'] = 2;
		$query['format'] = 'png';
		$query['key']    = $key;

		return http_build_query( $query, '', '&amp;' );
	}
}
