<?php

namespace Connections_Directory\Map;

use cnHTML as HTML;
use cnCoordinates as Coordinates;
use Connections_Directory\Map\Common\Options;
use Connections_Directory\Map\Layer\Layer;

/**
 * Generate map using custom HTML elements. Heavily based on the PHP Leaflet library.
 *
 * @package Connections_Directory\Map
 * @author  Steven A. Zahm
 * @since   8.28
 */
class Map {

	use Options;

	/**
	 * @since 8.28
	 * @var string
	 */
	private $id;

	/**
	 * @var Layer[]
	 */
	private $layers;

	/**
	 * Block constructor.
	 *
	 * @since 8.28
	 *
	 * @param string $id
	 * @param array  $options
	 */
	public function __construct( $id, $options ) {

		$this->setID( $id );
		$this->setOptions( $options );

		wp_enqueue_script( 'jquery-mapblock' );
	}

	/**
	 * @since 8.28
	 *
	 * @param string $id
	 * @param array  $options
	 *
	 * @return Map
	 */
	public static function create( $id, $options ) {

		return new static( $id, $options );
	}

	/**
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getID() {

		return $this->id;
	}

	/**
	 * @since 8.28
	 *
	 * @param string $id
	 */
	public function setID( $id ) {

		$this->id = $id;
	}

	/**
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getHeight() {

		return $this->getOption( 'height', '400px' );
	}

	/**
	 * Set the HTML block height. Rendered as an inline style.
	 *
	 * @since 8.28
	 *
	 * @param string $height
	 *
	 * @return Map
	 */
	public function setHeight( $height ) {

		return $this->store( 'height', (string) $height );
	}

	/**
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getWidth() {

		return $this->getOption( 'width', '100%' );
	}

	/**
	 * Set the HTML block width. Rendered as an inline style.
	 *
	 * @since 8.28
	 *
	 * @param string $width
	 *
	 * @return Map
	 */
	public function setWidth( $width ) {

		return $this->store( 'width', (string) $width );
	}

	/**
	 * @since 8.28
	 *
	 * @return Coordinates
	 */
	public function getCenter() {

		return $this->getOption( 'center', NULL );
	}

	/**
	 * @link https://leafletjs.com/reference.html#map-center
	 *
	 * @since 8.28
	 *
	 * @param Coordinates $center
	 *
	 * @return Map
	 */
	public function setCenter( $center ) {

		if ( ! $center instanceof Coordinates ) {

			$center = Coordinates::createFrom( $center );
		}

		if ( is_wp_error( $center ) ) {

			$center = Coordinates::create( 0, 0 );
		}

		return $this->store( 'center', $center );
	}

	/**
	 * @since 8.28
	 *
	 * @return float
	 */
	public function getZoom() {

		return $this->getOption( 'zoom', NULL );
	}

	/**
	 * @link https://leafletjs.com/reference.html#map-zoom
	 *
	 * @since 8.28
	 *
	 * @param int $zoom
	 *
	 * @return Map
	 */
	public function setZoom( $zoom ) {

		return $this->store( 'zoom', (float) $zoom );
	}

	/**
	 * Get map layers.
	 *
	 * @since 8.28
	 *
	 * @return Layer[]
	 */
	public function getLayers() {

		return $this->layers;
	}

	/**
	 * Add a layer to the map.
	 *
	 * @since 8.28
	 *
	 * @param Layer $layer
	 *
	 * @return $this
	 */
	public function addLayer( $layer ) {

		$this->layers[] = $layer;

		return $this;
	}

	/**
	 * Add multitple layers to a map.
	 *
	 * @since 8.28
	 *
	 * @param Layer[] $layers
	 *
	 * @return $this
	 */
	public function addLayers( $layers ) {

		foreach ( $layers as $layer ) {

			$this->addLayer( $layer );
		}

		return $this;
	}

	/**
	 * Whether or not the map has layers.
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function hasLayers() {

		return 0 < count( $this->getLayers() );
	}

	/**
	 * Returns the map block HTML.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function get() {

		$html = PHP_EOL;

		$html .= '<map-block';
		$html .= HTML::attribute( 'id', $this->getID() );
		$html .= HTML::attribute(
			'style',
			array(
				'display' => 'block',
				'width' => $this->getWidth(),
				'height' => $this->getHeight()
			)
		);
		$html .= $this->dataAttributes();
		$html .= '>' . PHP_EOL;

		if ( $this->hasLayers() ) {

			foreach ( $this->getLayers() as $layer ) {

				$html .= $layer;
			}
		}

		$html .= '</map-block>' . PHP_EOL;

		return $html;
	}

	/**
	 * Helper function to convert options to HTML data attributes.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	private function dataAttributes() {

		$options = array();

		foreach ( $this->getOptions() as $key => $value ) {

			array_push( $options, array( 'name' => $key, 'value' => (string) $value ) );
		}

		return HTML::attribute( 'data-array', $options );
	}

	/**
	 * Echo the HTML for the map block.
	 *
	 * @since 8.28
	 */
	public function render() {

		echo $this->get();
	}

	/**
	 * @since 8.28
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->get();
	}
}
