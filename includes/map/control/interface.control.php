<?php
namespace Connections_Directory\Map\Control;

use Connections_Directory\Map\Map_Object;

/**
 * Interface Layer
 *
 * @package Connections_Directory\Map\Control
 * @author  Steven A. Zahm
 */
interface Control extends Map_Object {

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
