<?php

namespace Connections_Directory\Map\Layer;

use Connections_Directory\Map\Map_Object;

/**
 * Interface Layer
 *
 * @package Connections_Directory\Map\Layer
 * @author  Steven A. Zahm
 */
interface Layer extends Map_Object {

	/**
	 * @since 8.28
	 * @return string
	 */
	public function get();

	/**
	 * @since 8.28
	 * @return mixed
	 */
	public function render();

	/**
	 * @since 8.28
	 * @return mixed
	 */
	public function __toString();
}
