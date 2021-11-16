<?php

namespace Connections_Directory\Map\Layer\Raster;

use cnHTML as HTML;
use Connections_Directory\Map\Common\Options;
use Connections_Directory\Map\Layer\Abstract_Layer;

/**
 * Class Tile_Layer
 *
 * @package Connections_Directory\Map\Layer
 * @author  Steven A. Zahm
 * @since   8.26
 */
class Tile_Layer extends Abstract_Layer {

	use Options;

	/**
	 * @since 8.28
	 * @var string
	 */
	private $url;

	/**
	 * Tile_Layer constructor.
	 *
	 * @since 8.28
	 *
	 * @param string $id
	 * @param string $url
	 * @param array  $options
	 */
	public function __construct( $id, $url, $options = array() ) {

		parent::__construct( $id );

		$this->setUrl( $url );
		$this->setOptions( $options );
	}

	/**
	 * Set the tile url template.
	 *
	 * @since 8.28
	 *
	 * @param string $url Tile url template.
	 *
	 * @return $this
	 */
	public function setUrl( $url ) {

		$this->url = $url;

		return $this;
	}

	/**
	 * Get the tile url template.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getUrl() {

		return $this->url;
	}

	/**
	 * Set the min zoom.
	 *
	 * @link https://leafletjs.com/reference.html#tilelayer-minzoom
	 *
	 * @since 8.28
	 *
	 * @param int $zoom The zoom level.
	 *
	 * @return $this
	 */
	public function setMinZoom( $zoom ) {

		return $this->store( 'minZoom', (int) $zoom );
	}

	/**
	 * Get the min zoom level.
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getMinZoom() {

		return $this->getOption( 'minZoom', 1 );
	}

	/**
	 * Set the max zoom.
	 *
	 * @link https://leafletjs.com/reference.html#tilelayer-maxzoom
	 *
	 * @since 8.28
	 *
	 * @param int $zoom The zoom level.
	 *
	 * @return $this
	 */
	public function setMaxZoom( $zoom ) {

		return $this->store( 'maxZoom', (int) $zoom );
	}

	/**
	 * Get the max zoom level.
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getMaxZoom() {

		return $this->getOption( 'maxZoom', 18 );
	}

	/**
	 * @link https://leafletjs.com/reference.html#tilelayer-sudomains
	 *
	 * @param string|array $subdomains Subdomains of the tile layer.
	 *
	 * @return $this
	 */
	public function setSubdomains( $subdomains ) {

		return $this->store( 'subdomains', $subdomains );
	}

	/**
	 * Get the subdomains.
	 *
	 * @link https://leafletjs.com/reference.html#tilelayer-sudomains
	 *
	 * @since 8.28
	 *
	 * @return array|string
	 */
	public function getSubdomains() {

		return $this->getOption( 'subdomains', 'abc' );
	}

	/**
	 * Set the attribution.
	 *
	 * @link https://leafletjs.com/reference.html#tilelayer-attribution
	 *
	 * @since 8.28
	 *
	 * @param string $attribution Attribution string which is added to the attribution control.
	 *
	 * @return $this
	 */
	public function setAttribution( $attribution ) {

		return $this->store( 'attribution', $attribution );
	}

	/**
	 * Get the attribution.
	 *
	 * @link https://leafletjs.com/reference.html#tilelayer-attribution
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getAttribution() {

		return $this->getOption( 'attribution', '' );
	}

	/**
	 * Returns the tile layer HTML.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function get() {

		$options = array(
			array( 'name' => 'id', 'value' => $this->getId() ),
			array( 'name' => 'url', 'value' => $this->getUrl() ),
		);

		foreach ( $this->getOptions() as $key => $value ) {

			if ( 'attribution' === $key ) {
				continue;
			}

			array_push( $options, array( 'name' => $key, 'value' => (string) $value ) );
		}

		$data = HTML::attribute( 'data-array', $options );

		return (string) "<map-tilelayer {$data}>{$this->getAttribution()}</map-tilelayer>" . PHP_EOL;
	}

	/**
	 * Echos the tile layer HTML.
	 *
	 * @since 8.28
	 */
	public function render() {

		// HTML is dynamically generated using static text, no user input.
		echo $this->get(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
