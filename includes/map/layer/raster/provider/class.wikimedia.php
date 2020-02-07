<?php

namespace Connections_Directory\Map\Layer\Raster\Provider;

use Connections_Directory\Map\Layer\Raster\Tile_Layer;

/**
 * Class Wikimedia
 *
 * @package Connections_Directory\Map\Layer\Provider
 * @author  Steven A. Zahm
 * @since   8.28
 */
class Wikimedia extends Tile_Layer {

	/**
	 * Wikimedia constructor.
	 *
	 * @since 8.28
	 *
	 * @param string $id
	 */
	public function __construct( $id ) {

		$osm       = '&copy; <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
		$wikimedia = '<a target="_blank" href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>';

		parent::__construct(
			$id,
			'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png',
			array(
				'subdomains'  => '',
				'attribution' => $wikimedia . ' | ' . $osm,
				'minZoom'     => 1,
				'maxZoom'     => 19,
			)
		);
	}

	/**
	 * @since 8.28
	 *
	 * @return Wikimedia
	 */
	public static function create() {

		return new static( 'wikimedia' );
	}
}
