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
class CN_REST_Entry_Controller extends WP_REST_Controller {

	/**
	 * @since 8.5.26
	 */
	const VERSION = '1';

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $namespace;

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $base = 'entry';

	/**
	 * @since 8.5.26
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
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
	 * Check if a given request has access to read /entry.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! is_user_logged_in() && cnOptions::loginRequired() ) {

			return new WP_Error(
				'rest_forbidden_context',
				__( 'Permission denied. Login required.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return TRUE;
	}

	/**
	 * Get a collection of posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$results = $this->get_entries( $request );

		$entries = array();

		foreach ( $results as $result ) {

			$entry = new cnEntry( $result );

			$data = $this->prepare_item_for_response( $entry, $request );
			$entries[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $entries );

		return $response;
	}

	/**
	 * Checks if a given request has access to read an entry.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {

		if ( is_user_logged_in() ) {

			/**
			 * @todo
			 *
			 * $request['context'] can be one of: view, embed, edit
			 * When user logged in, view context should be evaluated to ensure user has
			 * read capabilities for the requested entry.
			 */

			if ( 'edit' === $request['context'] &&
			     ( ! current_user_can( 'connections_edit_entry' ) || ! current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				return new WP_Error(
					'rest_forbidden_context',
					__( 'Permission denied. Current user does not have required capability(ies) assigned.', 'connections' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}

		} else {

			if ( cnOptions::loginRequired() ) {

				return new WP_Error(
					'rest_forbidden_context',
					__( 'Permission denied. Login required.', 'connections' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		return TRUE;
	}

	/**
	 * Retrieves a single entry.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {

		$atts = array(
			'id' => (int) $request['id'],
		);

		$result = $this->get_entries( $request, $atts );

		if ( empty( $atts['id'] ) || empty( $result ) ) {
			return new WP_Error( 'rest_entry_invalid_id', __( 'Invalid entry ID.', 'connections' ), array( 'status' => 404 ) );
		}

		$entry = new cnEntry( $result[0] );

		$data     = $this->prepare_item_for_response( $entry, $request );
		$response = rest_ensure_response( $data );

		//if ( is_post_type_viewable( get_post_type_object( $post->post_type ) ) ) {
		//	$response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );
		//}

		return $response;
	}

	/**
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @param array           $untrusted
	 *
	 * @return array
	 */
	protected function get_entries( $request, $untrusted = array() ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$defaults = array(
			'id'     => NULL,
			'limit'  => $request['per_page'],
			'offset' => $request['offset'],
		);

		$atts = cnSanitize::args( $untrusted, $defaults );

		$results = $instance->retrieve->entries( $atts );

		return $results;
	}

	/**
	 * Prepare a single entry output for response.
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

		/**
		 * @todo `raw` should only be sent in the `edit` context to match WP core API endpoints.
		 * @link https://github.com/WP-API/WP-API/issues/2948#issuecomment-265433906
		 */

		$data['id']   = $entry->getId();
		$data['type'] = $entry->getEntryType();
		$data['slug'] = $entry->getSlug();

		$data['name'] = array(
			'raw'      => $entry->getName( array(), 'raw' ),
			'rendered' => $entry->getName(),
		);

		$data['honorific_prefix'] = array(
			'raw'      => $entry->getHonorificPrefix( 'raw' ),
			'rendered' => $entry->getHonorificPrefix(),
		);

		$data['given_name'] = array(
			'raw'      => $entry->getFirstName( 'raw' ),
			'rendered' => $entry->getFirstName(),
		);

		$data['additional_name'] = array(
			'raw'      => $entry->getMiddleName( 'raw' ),
			'rendered' => $entry->getMiddleName(),
		);

		$data['family_name'] = array(
			'raw'      => $entry->getLastName( 'raw' ),
			'rendered' => $entry->getLastName(),
		);

		$data['honorific_suffix'] = array(
			'raw'      => $entry->getHonorificSuffix( 'raw' ),
			'rendered' => $entry->getHonorificSuffix(),
		);

		$data['job_title'] = array(
			'raw'      => $entry->getTitle( 'raw' ),
			'rendered' => $entry->getTitle(),
		);

		$data['org'] = array(
			'organization_name' => array(
				'raw'      => $entry->getDepartment( 'raw' ),
				'rendered' => $entry->getDepartment(),
			),
			'organization_unit' => array(
				'raw'      => $entry->getOrganization( 'raw' ),
				'rendered' => $entry->getOrganization(),
			),
		);

		$data['contact'] = array(
			'given_name' => array(
				'raw'      => $entry->getContactFirstName( 'raw' ),
				'rendered' => $entry->getContactFirstName(),
			),
			'family_name' => array(
				'raw'      => $entry->getContactLastName( 'raw' ),
				'rendered' => $entry->getContactLastName(),
			),
		);

		$data = $this->prepare_address_for_response( $entry, $request, $data );

		$data['bio'] = array(
			'raw'      => $entry->getBio( 'raw' ),
			'rendered' => $entry->getBio(),
		);

		$data['notes'] = array(
			'raw'      => $entry->getNotes( 'raw' ),
			'rendered' => $entry->getNotes(),
		);

		$data['visibility'] = $entry->getVisibility();
		$data['status']     = $entry->getStatus();

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Prepare a single address output for response.
	 *
	 * @param cnEntry         $entry   Post object.
	 * @param WP_REST_Request $request Request object.
	 * @param array           $data
	 *
	 * @return array $data
	 */
	private function prepare_address_for_response( $entry, $request, $data ) {

		$data['adr'] = array();
		$addresses   = $entry->getAddresses( array(), TRUE, FALSE, 'raw' );

		if ( empty( $addresses ) ) return $data;

		foreach ( $addresses as $address ) {

			$item = array(
				'id'               => $address->id,
				'order'            => $address->order,
				'preferred'        => $address->preferred,
				'type'             => $address->type,
				'street_address'   => array(
					'raw'      => $address->line_1,
					'rendered' => cnSanitize::field( 'street', $address->line_1, 'display' ),
				),
				'extended_address' => array(
					'raw'      => $address->line_2,
					'rendered' => cnSanitize::field( 'street', $address->line_2, 'display' ),
				),
				'extended_address_2' => array(
					'raw'      => $address->line_3,
					'rendered' => cnSanitize::field( 'street', $address->line_3, 'display' ),
				),
				'extended_address_3' => array(
					'raw'      => $address->line_4,
					'rendered' => cnSanitize::field( 'street', $address->line_4, 'display' ),
				),
				'district'         => array(
					'raw'      => $address->district,
					'rendered' => cnSanitize::field( 'district', $address->district, 'display' ),
				),
				'county'           => array(
					'raw'      => $address->county,
					'rendered' => cnSanitize::field( 'county', $address->county, 'display' ),
				),
				'locality'         => array(
					'raw'      => $address->city,
					'rendered' => cnSanitize::field( 'locality', $address->city, 'display' ),
				),
				'region'           => array(
					'raw'      => $address->state,
					'rendered' => cnSanitize::field( 'region', $address->state, 'display' ),
				),
				'postal_code'      => array(
					'raw'      => $address->zipcode,
					'rendered' => cnSanitize::field( 'postal-code', $address->zipcode, 'display' ),
				),
				'country_name'     => array(
					'raw'      => $address->country,
					'rendered' => cnSanitize::field( 'country', $address->country, 'display' ),
				),
				'latitude'         => $address->latitude,
				'longitude'        => $address->longitude,
				'visibility'       => $address->visibility,
			);

			$data['adr'][] = $item;
		}

		return $data;
	}

	/**
	 * Get the entry's schema, conforming to JSON Schema.
	 *
	 * Schema based on JSON Schema examples and hCard microformat spec which itself is based off the vCard 4 spec.
	 * Uses underscores as spaces to match WP core naming.
	 *  - JSON Schema example @link http://json-schema.org/card
	 *  - hCard Spec @link http://microformats.org/wiki/h-card
	 *
	 * Resource links:
	 *
	 * JSON Schema Validator @link http://www.jsonschemavalidator.net/
	 *
	 * @since 8.5.26
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'description' => 'A representation of a person, company, organization, or place',
			'title'       => $this->base,
			'type'        => 'object',
			'properties'  => array(
				'id'   => array(
					'description' => __( 'Unique identifier for the entry.', 'connections' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => TRUE,
				),
				'type' => array(
					'description' => __( 'Type of entry.', 'connections' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => TRUE,
				),
				'slug' => array(
					'description' => __( 'An alphanumeric identifier for the object unique to its type.', 'connections' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'fn' => array(
					'description' => __( 'The full formatted name of the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Name of the entry, as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML name of the entry, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'honorific_prefix' => array(
					'description' => __( 'An honorific prefix preceding a person\'s name such as Dr/Mrs/Mr.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Honorific prefix as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML honorific prefix, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'given_name' => array(
					'description' => __( 'Given name. In the U.S., the first name of a person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'First name as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML first name, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'additional_name' => array(
					'description' => __( 'An additional name for a person. The middle name of a person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Middle name as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML middle name, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'family_name' => array(
					'description' => __( 'Family name. In the U.S., the last name of an person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Last name as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML last name, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'honorific_suffix' => array(
					'description' => __( 'An honorific suffix preceding a person\'s name such as M.D. /PhD/MSCSW.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Honorific suffix as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML honorific suffix, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'job_title' => array(
					'description' => __( 'The job title of the person (for example, Financial Manager).', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Job title as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML job title, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					),
				),
				'org' => array(
					'description' => __( 'The organization object for the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'organization_name' => array(
							'type'        => 'object',
							'properties'  => array(
								'raw'      => array(
									'description' => __( 'Organization name as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML organization name, transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
								),
							),
						),
						'organization_unit' => array(
							'type'        => 'object',
							'properties'  => array(
								'raw'      => array(
									'description' => __( 'Department (organization unit) as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML department (organization unit), transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
								),
							),
						),
					),
				),
				'contact' => array(
					'description' => __( 'The contact name object for the entry of type organization.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'given_name' => array(
							'type'        => 'object',
							'properties'  => array(
								'raw'      => array(
									'description' => __( 'First name as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML first name, transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
								),
							),
						),
						'family_name' => array(
							'type'        => 'object',
							'properties'  => array(
								'raw'      => array(
									'description' => __( 'Last name as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML last name, transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
								),
							),
						),
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
