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
class CN_REST_Entry_GeoJSON_Controller extends CN_REST_Entry_Controller {

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $base = 'entry/geojson';

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
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$results = $this->get_entries( $request );

		$entries = array(
			'@context' => array(
				'geojson'            => 'http://ld.geojson.org/vocab#',
				'Feature'            => 'geojson:Feature',
				'FeatureCollection'  => 'geojson:FeatureCollection',
				'GeometryCollection' => 'geojson:GeometryCollection',
				'LineString'         => 'geojson:LineString',
				'MultiLineString'    => 'geojson:MultiLineString',
				'MultiPoint'         => 'geojson:MultiPoint',
				'MultiPolygon'       => 'geojson:MultiPolygon',
				'Point'              => 'geojson:Point',
				'Polygon'            => 'geojson:Polygon',
				'bbox'               => array(
					'@container' => '@list',
					'@id'        => 'geojson:bbox',
				),
				'coordinates'        => 'geojson:coordinates',
				'features'           => array(
					'@container' => '@set',
					'@id'        => 'geojson:features',
				),
				'geometry'           => 'geojson:geometry',
				'properties'         => 'geojson:properties',
				'type'               => '@type',
				'Person'             => 'https://schema.org/Person',
				'Organization'       => 'https://schema.org/Organization',
				'id'                 => 'https://schema.org/Integer',
				'name'               => 'https://schema.org/name',
				'title'              => 'https://schema.org/jobTitle',
				'department'         => 'https://schema.org/department',
				'organization'       => 'https://schema.org/worksFor',
			),
			'type'     => 'FeatureCollection',
			'features' => array(),
		);

		foreach ( $results as $result ) {

			$entry = new cnEntry( $result );

			$data = $this->prepare_item_for_response( $entry, $request );
			$entries['features'][] = $this->prepare_response_for_collection( $data );
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

		//$entry->directoryHome(
		//	array(
		//		'page_id'    => $homeID,
		//		'force_home' => $forceHome,
		//	)
		//);

		$addresses = $entry->getAddresses();

		/**
		 * NOTES:
		 *
		 *  - The `coordinates` index array value must not have indexes otherwise it'll be converted to an object
		 * which is invalid geoJSON.
		 *  - The `coordinates` must but cast as floats otherwise they'll be converted to strings.
		 *  - The `coordinates` must be longitude, latitude order per the geoJSON spec.
		 *
		 * @todo Loop thu each address within an entry so a geoJSON `feature` is added for each address the entry may have.
		 * @todo The entry only needs to be added to $entries if it has at least one address and those address has both a latitude and longitude.
		 *
		 * @link http://connections-pro.com/support/topic/map-view/#post-319981
		 */

		if ( ( ! isset( $addresses[0]->latitude ) || empty( $addresses[0]->latitude ) ) && ( ! isset( $addresses[0]->longitude ) || empty( $addresses[0]->longitude ) ) ) {

			//return;
		}

		switch ( $entry->getEntryType() ) {

			case 'individual':
				$type = 'Person';
				break;

			case  'organization':
				$type = 'Organization';
				break;

			case 'family':
				$type = 'Family';
				break;

			default:
				$type = NULL;
		}

		$data = array(
			'type'       => 'Feature',
			'geometry'   => array(
				'type'        => 'Point',
				'coordinates' => array(
					(float) $addresses[0]->longitude,
					(float) $addresses[0]->latitude,
				),
			),
			'properties' => array(
				'id'        => $entry->getId(),
				'type'      => $type,
				'slug'      => $entry->getSlug(),
				'permalink' => $entry->getPermalink(),
				'name'      => $entry->getName(),
				'title'        => $entry->getTitle(),
				'department'   => $entry->getDepartment() ? array( '@type' => 'Organization', 'name' => $entry->getDepartment() ) : NULL,
				'organization' => $entry->getOrganization() ? array( '@type' => 'Organization', 'name' => $entry->getOrganization() ) : NULL,
				//'addresses'    => $addresses,
				//'phone'        => $entry->getPhoneNumbers(),
				//'email'        => $entry->getEmailAddresses(),
				//'im'           => $entry->getIm(),
				//'social'       => $entry->getSocialMedia(),
				//'dates'        => $entry->getDates(),
				'bio'          => $entry->getBio(),
				'notes'        => $entry->getNotes(),
				//'categories'   => $entry->getCategory(),
				//'meta'         => $entry->getMeta(),
			),
		);

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Get the entry's schema, conforming to JSON Schema.
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
