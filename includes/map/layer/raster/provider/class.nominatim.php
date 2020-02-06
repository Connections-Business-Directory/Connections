<?php

namespace Connections_Directory\Map\Layer\Raster\Provider;

use Connections_Directory\Map\Layer\Raster\Tile_Layer;

/**
 * Class Nominatim
 *
 * @package Connections_Directory\Map\Layer\Provider
 * @author  Steven A. Zahm
 * @since   8.28
 */
class Nominatim extends Tile_Layer {

	/**
	 * Nominatim constructor.
	 *
	 * @since 8.28
	 *
	 * @param string $id
	 */
	public function __construct( $id ) {

		parent::__construct(
			$id,
			'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			array(
				'subdomains'  => 'abc',
				'attribution' => '&copy; <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
				'minZoom'     => 1,
				'maxZoom'     => 17,
			)
		);
	}

	/**
	 * @since 8.28
	 *
	 * @return Nominatim
	 */
	public static function create() {

		return new static( 'nominatim' );
	}
}
