<?php
/**
 * Connections REST API
 *
 * @author   Steven A. Zahm
 * @category API
 * @package  Connections/API
 * @since    8.5.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnAPI
 */
class cnAPI {

	/**
	 * Setup class.
	 *
	 * @access public
	 * @since  8.5.26
	 */
	public function __construct() {

		// WP REST API.
		$this->init();
	}

	/**
	 * Init REST API.
	 *
	 * @access private
	 * @since  8.5.26
	 */
	private function init() {

		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->includes();

		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'registerRoutes' ), 10 );
	}

	/**
	 * Include REST API classes.
	 *
	 * @access private
	 * @since  8.5.26
	 */
	private function includes() {

		// Abstract controllers.
		//include_once( CN_PATH  . 'includes/api/abstracts/abstract.cn-rest-controller.php' );

		// REST API functions.
		include_once( CN_PATH  . 'includes/api/inc.functions.php' );

		// REST API controllers.
		include_once( CN_PATH  . 'includes/api/endpoints/class.cn-rest-entry-controller.php' );
		//include_once( CN_PATH  . 'includes/api/endpoints/class.cn-rest-entry-json-ld-controller.php' );
		//include_once( CN_PATH  . 'includes/api/endpoints/class.cn-rest-entry-geojson-controller.php' );
		include_once( CN_PATH  . 'includes/api/endpoints/class.cn-rest-terms-controller.php' );
		include_once( CN_PATH  . 'includes/api/endpoints/class.cn-rest-countries-controller.php' );
		include_once( CN_PATH  . 'includes/api/endpoints/class.cn-rest-autocomplete-controller.php' );
		include_once( CN_PATH  . 'includes/api/endpoints/Recently_Viewed.php' );

		include_once( CN_PATH  . 'includes/api/endpoints/class.settings.php' );
	}

	/**
	 * Register REST API routes.
	 *
	 * @access public
	 * @since  8.5.26
	 */
	public function registerRoutes() {

		$controllers = array(
			'CN_REST_Entry_Controller',
			//'CN_REST_Entry_JSONLD_Controller',
			//'CN_REST_Entry_GeoJSON_Controller',
			'CN_REST_Terms_Controller',
			'CN_REST_Countries_Controller',
			'CN_REST_Autocomplete_Controller',
			'Connections_Directory\REST_API\Endpoint\Settings',
			'Connections_Directory\API\REST\Endpoint\Recently_Viewed',
		);

		foreach ( $controllers as $controller ) {

			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}
}
