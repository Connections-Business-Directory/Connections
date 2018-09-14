<?php

namespace Connections_Directory\Map;

/**
 * Interface Map_Object
 *
 * @package Connections_Directory\
 * @author  Steven A. Zahm
 * @since   8.28
 */
interface Map_Object {

	/**
	 * Add object to the map.
	 *
	 * @since 8.28
	 *
	 * @param Map $map The map.
	 *
	 * @return $this
	 */
	public function addTo( $map );
}
