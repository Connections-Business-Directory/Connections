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

		parent::__construct(
			$id,
			'//maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png',
			array(
				'subdomains'  => '',
				'attribution' => '<a target="_blank" href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>',
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
