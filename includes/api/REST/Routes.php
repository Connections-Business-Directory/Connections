<?php
/**
 * Register the REST API routes.
 *
 * @since      2023
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\API\REST
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\API\REST;

/**
 * Class Routes
 *
 * @package Connections_Directory\API\REST
 */
final class Routes {

	/**
	 * Callback for the `rest_api_init` action hook.
	 *
	 * Register REST API routes.
	 *
	 * @internal
	 * @since  8.5.26
	 */
	public static function register() {

		$controllers = array(
			// 'CN_REST_Entry_JSONLD_Controller',
			// 'CN_REST_Entry_GeoJSON_Controller',
			'CN_REST_Terms_Controller',
			'CN_REST_Countries_Controller',
			'CN_REST_Autocomplete_Controller',
			'Connections_Directory\API\REST\Endpoint\Entry',
			'Connections_Directory\API\REST\Endpoint\Recently_Viewed',
			'Connections_Directory\API\REST\Endpoint\Settings',
		);

		foreach ( $controllers as $controller ) {

			$controller = new $controller();
			$controller->register_routes();
		}
	}
}
