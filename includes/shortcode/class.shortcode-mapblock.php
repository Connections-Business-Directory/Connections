<?php

namespace Connections_Directory\Shortcode;

use cnFormatting;
use cnSettingsAPI as Option;
use cnCoordinates as Coordinates;
use Connections_Directory\Map\Map;
use Connections_Directory\Map\UI\Popup;
use Connections_Directory\Map\UI\Marker;
use Connections_Directory\Map\Layer\Group\Layer_Group;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class mapBlock
 *
 * @package Connections_Directory\Shortcode
 */
class mapBlock {

	/**
	 * @var Map
	 */
	private $map;

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content = '', $tag = 'cn-mapblock' ) {

		return new static( $atts, $content, $tag );
	}

	/**
	 * mapBlock constructor.
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 */
	public function __construct( $atts, $content, $tag ) {

		$atts = $this->parseAtts( $atts, $tag );

		$this->map = Map::create(
			$atts['id'],
			array(
				'center' => new Coordinates( $atts['latitude'], $atts['longitude'] ),
				'zoom'   => $atts['zoom'],
			)
		);

		$googleMapsAPIBrowserKey = Option::get(
			'connections',
			'google_maps_geocoding_api',
			'browser_key'
		);

		// Strings to be used for setting the Leaflet maps `attribution`.
		$leaflet  = '<a href="https://www.leafletjs.com" target="_blank" title="Leaflet">Leaflet</a>';
		$backlink = '<a href="https://connections-pro.com/" target="_blank" title="Connections Business Directory plugin for WordPress">Connections Business Directory</a> | ' . $leaflet;

		$attribution = array( $backlink );

		if ( 0 < strlen( $googleMapsAPIBrowserKey ) ) {

			$baseMap = \Connections_Directory\Map\Layer\Raster\Provider\Google_Maps::create();

		} else {

			$baseMap = \Connections_Directory\Map\Layer\Raster\Provider\Wikimedia::create();

			$attribution[] = $baseMap->getAttribution();
		}

		$baseMap->setAttribution( implode( ' | ', $attribution )  );

		$this->map->setHeight( $atts['height'] )
		          ->setWidth( $atts['width'] )
		          ->setCenter( new Coordinates( $this->getDefaults()['latitude'], $this->getDefaults()['longitude'] ) )
		          ->addLayer( $baseMap );

		$content = $this->parseLayers( $content );
		$content = $this->parseMarkers( $content );

		if ( $atts['marker'] ) {

			//$this->markers->add( new Coordinates( $atts['latitude'], $atts['longitude'] ), $content );

			$coordinates = Coordinates::create( $atts['latitude'], $atts['longitude'] );

			if ( ! is_wp_error( $coordinates ) ) {

				Marker::create( 'default', $coordinates )
				      ->bindPopup( Popup::create( 'default', $content ) )
				      ->addTo( $this->map );
			}
		}

	}

	/**
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return mapBlock
	 */
	public static function create( $atts, $content, $tag ) {

		return new static( $atts, $content, $tag );
	}

	/**
	 * The shortcode defaults.
	 *
	 * @return array
	 */
	private function getDefaults() {

		return array(
			'id'        => 'cn-map-' . uniqid(),
			'latitude'  => 39.8283,
			'longitude' => -98.5795,
			'zoom'      => 16,
			'height'    => '400px',
			'width'     => '100%',
			'marker'    => TRUE,
		);
	}

	/**
	 * Parse the user supplied atts.
	 *
	 * @param array  $atts
	 * @param string $tag
	 *
	 * @return array
	 */
	public function parseAtts( $atts, $tag ) {

		$defaults = $this->getDefaults();
		$atts     = shortcode_atts( $defaults, $atts, $tag );

		cnFormatting::toBoolean( $atts['marker'] );

		return $atts;
	}

	/**
	 * Parse the shortcode content for layers and their markers.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function parseLayers( $content ) {

		$pattern = get_shortcode_regex( array( 'maplayer' ) );

		$content = preg_replace_callback(
			"/$pattern/",
			function( $match ) {

				// If there is no content, then there are no markers to parse, return.
				if ( 0 == strlen( $match[5]) ) return '';

				$defaults = array(
					'name'    => 'default',
					'control' => FALSE,
				);

				$atts = $this->parseShortcodeAtts( $match[3] );
				$atts = shortcode_atts( $defaults, $atts );

				cnFormatting::toBoolean( $atts['control'] );

				$layerGroup = Layer_Group::create( $atts['name'] )->addTo( $this->map );

				$this->parseMarkers( $match[5], $layerGroup );

				return '';
			},
			$content
		);

		return trim( $content );
	}

	/**
	 * Parse supplied content for markers.
	 *
	 * @param string      $content
	 * @param Layer_Group $layer
	 *
	 * @return string
	 */
	private function parseMarkers( $content, $layer = NULL ) {

		$pattern = get_shortcode_regex( array( 'mapmarker' ) );

		$content = preg_replace_callback(
			"/$pattern/",
			function( $match ) use ( $layer ) {

				$defaults = array(
					'id'        => 'marker',
					'latitude'  => NULL,
					'longitude' => NULL,
				);

				$atts = shortcode_parse_atts( $match[3] );
				$atts = shortcode_atts( $defaults, $atts );

				$coordinates = Coordinates::create( $atts['latitude'], $atts['longitude'] );

				if ( ! is_wp_error( $coordinates ) ) {

					$marker = Marker::create( $atts['id'], $coordinates );

					if ( 0 < strlen( $match[5] ) ) {

						$marker->bindPopup( Popup::create( 'default', $match[5] ) );
					}

					if ( $layer instanceof Layer_Group ) {

						$layer->addLayer( $marker );

					} else {

						$marker->addTo( $this->map );
					}

				}

				return '';
			},
			$content
		);

		return trim( $content );
	}

	/**
	 * Wrapper function for core WP `shortcode_parse_atts()`.
	 * Decodes selected HTML entities.
	 *
	 * @param string $text
	 *
	 * @return array|string
	 */
	private function parseShortcodeAtts( $text ) {

		$text = str_replace(
			array(
				'&#8220;',
				'&Prime;',
				'&#8221;',
				'&#8243;',
				'&#8217;',
				'&#8242;',
				'&nbsp;&raquo;',
				'&#187;',
				'&quot;',
			),
			array( '"', '"', '"', '"', '\'', '\'', '"', '"', '"' ),
			$text
		);

		return shortcode_parse_atts( $text );
	}

	/**
	 * @return string
	 */
	public function __toString() {

		return (string) $this->map;
	}
}
