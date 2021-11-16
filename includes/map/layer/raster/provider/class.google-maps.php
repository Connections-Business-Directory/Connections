<?php

namespace Connections_Directory\Map\Layer\Raster\Provider;

use cnHTML as HTML;
use Connections_Directory\Map\Layer\Raster\Tile_Layer;

/**
 * Class Google_Maps
 *
 * @package Connections_Directory\Map\Layer\Provider
 * @author  Steven A. Zahm
 * @since   8.28
 */
class Google_Maps extends Tile_Layer {

	/**
	 * Google_Maps constructor.
	 *
	 * @since 8.28
	 *
	 * @param string $id
	 * @param string $type
	 */
	public function __construct( $id, $type ) {

		$this->setOption( 'type', $type );

		parent::__construct( $id, '' );
	}

	/**
	 * @since 8.28
	 *
	 * @param string $type
	 *
	 * @return Google_Maps
	 */
	public static function create( $type ) {

		return new static( "google-$type", $type );
	}

	/**
	 * Returns the map tile layer HTML.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function get() {

		$options = array(
			array( 'name' => 'id', 'value' => $this->getId() ),
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
	 * @return string
	 */
	public function __toString() {

		return $this->get();
	}
}
