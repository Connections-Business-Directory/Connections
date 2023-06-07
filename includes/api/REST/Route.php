<?php
/**
 *
 *
 * @since
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\API\REST;

/**
 * Trait Route
 *
 * @package Connections_Directory\API\REST\Endpoint
 */
trait Route {

	/**
	 * Register REST Route Endpoints.
	 *
	 * @since 10.4.45
	 */
	public static function register() {

		$controller = new self();
		$controller->register_routes();
	}
}
