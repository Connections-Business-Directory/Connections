<?php

namespace Connections_Directory\Map\Layer;

use Connections_Directory\Map\Common\Options;
use Connections_Directory\Map\Map;

/**
 * Class Abstract_Layer
 *
 * @package Connections_Directory\Map\Layer
 * @auther  Steven A. Zahm
 * @since   8.28
 */
abstract class Abstract_Layer implements Layer {

	use Options;

	/**
	 * @since 8.28
	 * @var string
	 */
	private $id;

	/**
	 * The connected map.
	 *
	 * @since 8.28
	 *
	 * @var Map
	 */
	protected $map;

	/**
	 * Abstract_Layer constructor.
	 *
	 * @since 8.28
	 *
	 * @param $id
	 */
	public function __construct( $id ) {

		$this->id = $id;
	}

	/**
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getId() {

		return $this->id;
	}

	/**
	 * @since 8.28
	 *
	 * @param Map $map
	 *
	 * @return $this|Layer
	 */
	public function addTo( $map ) {

		$this->map = $map;
		$map->addLayer( $this );

		return $this;
	}
}
