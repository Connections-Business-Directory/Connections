<?php
/**
 * REST API Countries Controller
 *
 * @author   Steven A. Zahm
 * @category API
 * @package  Connections/API
 * @since    8.7
 */

namespace Connections_Directory\API\REST\Route;

use cnCountries;
use cnCountry;
use Connections_Directory\API\REST\Route;
use Connections_Directory\Utility\_array;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API Countries Controller.
 *
 * @package Connections/API
 * @extends WP_REST_Controller
 */
class Countries extends WP_REST_Controller {

	use Route;

	/**
	 * REST API version.
	 *
	 * @since 8.7
	 */
	const VERSION = '1';

	/**
	 * The REST namespace.
	 *
	 * @since 8.7
	 * @var string
	 */
	protected $namespace;

	/**
	 * The REST base name.
	 *
	 * @since 8.7
	 * @var string
	 */
	protected $base = 'countries';

	/**
	 * Constructor.
	 *
	 * @since 8.7
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 8.7
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		$countries    = cnCountries::getAll( false, ARRAY_A );
		$countryCodes = array_keys( $countries );

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<code>[a-z]{2,2})',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
						'code'    => array(
							'type' => 'string',
							'enum' => $countryCodes,
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<code>[a-z]{2,2})/geojson',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item_geojson' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
						'code'    => array(
							'type' => 'string',
							'enum' => $countryCodes,
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<code>[a-z]{2,2})/regions',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item_regions' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
						'code'    => array(
							'type' => 'string',
							'enum' => $countryCodes,
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<code>[a-z]{2,2})/region/(?P<region>[0-9a-zA-Z]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item_region' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
						'code'    => array(
							'type' => 'string',
							'enum' => $countryCodes,
						),
						'region'  => array(
							'type' => 'string',
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get a collection of items
	 *
	 * @since 8.7
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$detailed = _array::get( $request, 'detailed', false );

		$countries = cnCountries::getAll( $detailed, ARRAY_A );

		return rest_ensure_response( $countries );
	}

	/**
	 * Get one item from the collection
	 *
	 * @since 8.7
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$code = _array::get( $request, 'code' );

		$response = cnCountries::getByCode( $code, ARRAY_A );

		if ( is_wp_error( $response ) ) {

			$response = new WP_Error(
				'country_data_not_found',
				__( 'Country code may be misspelled, invalid, or data not found.', 'connections' ),
				$code
			);
		}

		return rest_ensure_response( $response );
	}

	/**
	 *
	 *
	 * @since 8.7
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_geojson( $request ) {

		$code = _array::get( $request, 'code' );

		$response = cnCountries::getByCode( $code );

		if ( is_wp_error( $response ) || ! $response instanceof cnCountry ) {

			$response = new WP_Error(
				'country_data_not_found',
				__( 'Country code may be misspelled, invalid, or data not found.', 'connections' ),
				$code
			);
		}

		$response = $response->getGeoJson();

		return rest_ensure_response( $response );
	}

	/**
	 *
	 *
	 * @since 8.7
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_regions( $request ) {

		$code = _array::get( $request, 'code', false );

		$response = cnCountries::getByCode( $code );

		if ( is_wp_error( $response ) || ! $response instanceof cnCountry ) {

			$response = new WP_Error(
				'country_data_not_found',
				__( 'Country code may be misspelled, invalid, or data not found.', 'connections' ),
				$code
			);

		}

		$response = $response->getDivisions();

		return rest_ensure_response( $response );
	}

	/**
	 *
	 *
	 * @since 8.7
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_region( $request ) {

		$code   = _array::get( $request, 'code' );
		$region = _array::get( $request, 'region' );

		$response = cnCountries::getByCode( $code );

		if ( is_wp_error( $response ) || ! $response instanceof cnCountry ) {

			$response = new WP_Error(
				'country_data_not_found',
				__( 'Country code may be misspelled, invalid, or data not found.', 'connections' ),
				$code
			);
		}

		$response = $response->getDivision( strtoupper( $region ) );

		if ( is_null( $response ) ) {

			$response = new WP_Error(
				'region_data_not_found',
				__( 'Region code may be misspelled, invalid, or data not found.', 'connections' ),
				$region
			);
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Get the query params for collections
	 *
	 * @since 8.7
	 *
	 * @return array
	 */
	public function get_item_schema(): array {

		$schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'description' => 'Returns country specific meta.',
			'title'       => $this->base,
			'type'        => 'object',
			'properties'  => array(),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections
	 *
	 * @since 8.7
	 *
	 * @return array
	 */
	public function get_collection_params(): array {

		$query_params = array(
			'context'  => array( 'default' => 'view' ),
			'detailed' => array(
				'description' => __( 'Whether or not to returned detailed country meta. Default is to return only the country name, ISO codes, calling code and emoji.', 'connections' ),
				'type'        => 'boolean',
				'default'     => false,
			),
		);

		$query_params['detailed'] = array(
			'description' => __( 'Whether to retrieve detailed country data.', 'connections' ),
			'type'        => 'boolean',
		);

		return $query_params;
	}
}
