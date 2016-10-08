<?php
/**
 * REST API Entry Controller
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
 * REST API Entry Controller.
 *
 * @package Connections/API
 * @extends WP_REST_Controller
 */
class CN_REST_Entry_JSONLD_Controller extends CN_REST_Entry_Controller {

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $base = 'entry/json-ld';

	/**
	 * @since 8.5.26
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 8.5.26
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'default'     => FALSE,
							'description' => __( 'Required to be true, as resource does not support trashing.', 'connections' ),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get a collection of posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$results = $this->get_entries( $request );

		$entries = array(
			'@context' => 'http://schema.org',
			'@graph' => array(),
		);

		foreach ( $results as $result ) {

			$entry = new cnEntry( $result );

			$data = $this->prepare_item_for_response( $entry, $request );
			$entries['@graph'][] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $entries );

		return $response;
	}

	/**
	 * Prepare a single post output for response.
	 *
	 * @param cnEntry         $entry   Post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response $data
	 */
	public function prepare_item_for_response( $entry, $request ) {

		$data = array();

		//$entry->directoryHome(
		//	array(
		//		'page_id'    => $homeID,
		//		'force_home' => $forceHome,
		//	)
		//);

		switch ( $entry->getEntryType() ) {

			case 'individual':

				$data['@type']           = 'Person';
				$data['honorificPrefix'] = $entry->getHonorificPrefix();
				$data['givenName']       = $entry->getFirstName();
				$data['additionalName']  = $entry->getMiddleName();
				$data['familyName']      = $entry->getLastName();
				$data['honorificSuffix'] = $entry->getHonorificSuffix();
				break;

			case  'organization':

				$data['@type'] = 'Organization';
				$data['name']  = $entry->getName();
				break;

			case 'family':

				/**
				 * Unfortunately there is no "Family" type available.
				 * Use "Person" as the type with the "additionalType" set as "Family". Valid? Unsure.
				 *
				 * NOTES: Perhaps if "Family" is merged in from @link http://historical-data.org/ it can be used.
				 */


				$data['@type']          = 'Person';
				$data['additionalType'] = 'Family';
				$data['name']           = $entry->getName();
				break;

			default:
				/** @todo add filter for custom entry types */
		}

		/**
		 * NOTES:
		 *
		 *  - Social network links should use the sameAs property as an array.
		 */

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Get the entry's schema, conforming to JSON Schema.
	 *
	 * Resource links:
	 *
	 * Schema Definitions: @link https://schema.org/
	 * Validation: @link https://search.google.com/structured-data/testing-tool
	 *
	 * Example: @link https://www.distilled.net/resources/5-common-schema-problems-and-what-to-do-about-them/
	 * Example: @link https://whitespark.ca/blog/the-json-ld-markup-guide-to-local-business-schema/
	 *
	 * @since 8.5.26
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->base,
			'type'       => 'object',
			'properties' => array(
				'id'   => array(
					'description' => __( 'Unique identifier for the object.', 'connections' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => TRUE,
				),
				'name' => array(
					'description' => __( 'The name of the object.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Name of the object, as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML name of the object, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'slug' => array(
					'description' => __( 'An alphanumeric identifier for the object unique to its type.', 'connections' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 8.5.26
	 *
	 * @return array
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$params['slug'] = array(
			'description'       => __( 'Limit result set to entries with a specific slug.', 'connections' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}
}
