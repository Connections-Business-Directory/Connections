<?php
/**
 * Connections REST API
 *
 * @author   Steven A. Zahm
 * @category API
 * @package  Connections/API
 * @since    8.5.26
 */

/**
 * Class cnAPI
 */
class cnAPI {

	/**
	 * Register REST API routes.
	 *
	 * @access public
	 * @since  8.5.26
	 */
	public static function registerRoutes() {

		$controllers = array(
			'CN_REST_Entry_Controller',
			// 'CN_REST_Entry_JSONLD_Controller',
			// 'CN_REST_Entry_GeoJSON_Controller',
			'CN_REST_Terms_Controller',
			'CN_REST_Countries_Controller',
			'CN_REST_Autocomplete_Controller',
			'Connections_Directory\REST_API\Endpoint\Settings',
			'Connections_Directory\API\REST\Endpoint\Recently_Viewed',
		);

		foreach ( $controllers as $controller ) {

			$controller = new $controller();
			$controller->register_routes();
		}
	}
}
