<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnAddress;
use cnCoordinates;
use cnEntry;
use cnSettingsAPI;
use Connections_Directory\Content_Block;
use Connections_Directory\Map\Control\Layer\Layer_Control;
use Connections_Directory\Map\Layer\Raster\Provider\Google_Maps;
use Connections_Directory\Map\Layer\Raster\Provider\Nominatim;
use Connections_Directory\Map\Map;
use Connections_Directory\Map\UI\Marker;
use Connections_Directory\Map\UI\Popup;
use Connections_Directory\Model\Format\Address\As_String;

/**
 * Class Map_Block
 *
 * @package Connections_Directory\Content_Block
 */
class Map_Block extends Content_Block {

	/**
	 * @since 9.7
	 * @var string
	 */
	const ID = 'map-block';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'preferred' => null,
		'type'      => null,
		'zoom'      => 13,
		'height'    => '400px',
		'width'     => '100%',
	);

	/**
	 * Map_Block constructor.
	 *
	 * @param string $id
	 */
	public function __construct( $id ) {

		$atts = array(
			'name'                => __( 'Map: Interactive', 'connections' ),
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
	 * Renders the Map Block.
	 *
	 * @since 9.7
	 */
	public function content() {

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$addresses = $entry->getAddresses(
			array(
				'preferred' => $this->get( 'preferred' ),
				'type'      => $this->get( 'type' ),
			)
		);

		if ( 0 < count( $addresses ) ) {

			$createMap    = false;
			$layers       = array();
			$layerControl = Layer_Control::create( 'layerControl' )->setCollapsed( false );

			$googleMapsAPIBrowserKey = cnSettingsAPI::get(
				'connections',
				'google_maps_geocoding_api',
				'browser_key'
			);

			// Strings to be used for setting the Leaflet maps `attribution`.
			$leaflet  = '<a href="https://leafletjs.com/" target="_blank" title="Leaflet">Leaflet</a>';
			$backlink = '<a href="https://connections-pro.com/" target="_blank" title="Connections Business Directory plugin for WordPress">Connections Business Directory</a> | ' . $leaflet;

			$attribution = array( $backlink );

			if ( 0 < strlen( $googleMapsAPIBrowserKey ) ) {

				$roadMap = Google_Maps::create( 'roadmap' );

				$roadMap->setAttribution( implode( ' | ', $attribution ) )
						->setOption( 'name', 'Roadmap' );

				$layerControl->addBaseLayer( $roadMap );

				$hybrid = Google_Maps::create( 'hybrid' );

				$hybrid->setAttribution( implode( ' | ', $attribution ) )
					   ->setOption( 'name', 'Satellite' );

				$layerControl->addBaseLayer( $hybrid );

			} else {

				$baseMap = Nominatim::create();

				$attribution[] = $baseMap->getAttribution();

				$baseMap->setAttribution( implode( ' | ', $attribution ) );

				/*
				 * Adding a base layer, creates a layer switch control, add base map tiles as a normal layer to
				 * prevent a the empty layer control from being displayed.
				 */
				// $layerControl->addBaseLayer( $baseMap );
				$layers[] = $baseMap;
			}

			foreach ( $addresses as $address ) {

				$coordinates = cnCoordinates::create( $address->latitude, $address->longitude );

				if ( ! is_wp_error( $coordinates ) ) {

					$formatted = As_String::format( new cnAddress( (array) $address ) );

					$directionsURL = add_query_arg(
						array(
							'saddr' => '',
							'daddr' => "{$coordinates->getLatitude()},{$coordinates->getLongitude()}",
						),
						'https://www.google.com/maps'
					);

					$buttonText = esc_html__( 'Get Directions', 'connections' );

					$directionsButton = "<a href=\"{$directionsURL}\" target=\"_blank\"><button>{$buttonText}</button></a>";

					$popup = "<p>{$formatted}</p><div>{$directionsButton}</div>";

					$layers[] = Marker::create( 'default', $coordinates )
									  ->bindPopup( Popup::create( 'default', $popup ) );

					$createMap = true;
				}

			}

			if ( $createMap ) {

				$html = Map::create(
					'cn-map-' . $entry->getRuid(),
					array(
						'center' => new cnCoordinates( 39.8283, -98.5795 ),
						'zoom'   => $this->get( 'zoom', 13 ),
					)
				)->setHeight( $this->get( 'height', '400px' ) )
				 ->setWidth( $this->get( 'width', '100%' ) )
				 ->addLayers( $layerControl->getBaseLayers() )
				 ->addLayers( $layers )
				 ->addControl( $layerControl );

				do_action(
					"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
					$entry
				);

				// Map HTML is dynamically generated using static text, no user input.
				echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				do_action(
					"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
					$entry
				);
			}

		}
	}
}
