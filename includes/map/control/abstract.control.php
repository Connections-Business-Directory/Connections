<?php

namespace Connections_Directory\Map\Control;

use Connections_Directory\Map\Common\Options;
use Connections_Directory\Map\Map;

/**
 * Class Abstract_Control
 *
 * @package Connections_Directory\Map\Control
 * @auther  Steven A. Zahm
 * @since   8.29
 */
abstract class Abstract_Control implements Control {

	use Options;

	/**
	 * @since 8.29
	 * @var string
	 */
	private $id;

	/**
	 * The connected map.
	 *
	 * @since 8.29
	 *
	 * @var Map
	 */
	protected $map;

	/**
	 * Abstract_Layer constructor.
	 *
	 * @since 8.29
	 *
	 * @param $id
	 */
	public function __construct( $id ) {

		$this->id = $id;
	}

	/**
	 * @since 8.29
	 *
	 * @return string
	 */
	public function getId() {

		return $this->id;
	}

	/**
	 * Set position.
	 *
	 * @since 8.29
	 *
	 * @param string $position Position.
	 *
	 * @return $this
	 */
	public function setPosition( $position ) {

		return $this->setOption( 'position', $position );
	}

	/**
	 * Get the position.
	 *
	 * @since 8.29
	 *
	 * @return string
	 */
	public function getPosition() {

		return $this->getOption( 'position', 'topright' );
	}

	/**
	 * Add control to the map.
	 *
	 * @since 8.29
	 *
	 * @param Map $map The leaflet map.
	 *
	 * @return $this|Control
	 */
	public function addTo( $map ) {

		$this->map = $map;
		$map->addControl( $this );

		return $this;
	}
}
