<?php
/**
 * REST API Category Controller.
 *
 * Handles requests to the category endpoint.
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
 * REST API Category Controller class.
 *
 * @package Connections/API
 * @extends WP_REST_Controller
 */
class CN_REST_Terms_Controller extends WP_REST_Controller {

	/**
	 * @since 8.5.26
	 */
	const VERSION = '1';

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $taxonomy = 'category';

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $namespace;

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $base;

	/**
	 * @since 8.5.26
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
		$this->base      = $this->taxonomy;
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @access public
	 * @since  8.5.26
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
							'description' => __( 'Required to be true, as terms do not support trashing.', 'connections' ),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check if a given request has access to read the terms.
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( 'edit' === $request['context'] && ! current_user_can( 'connections_edit_categories' ) ) {

			return new WP_Error(
				'rest_forbidden_context',
				__( 'Permission denied. Edit capability required.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return TRUE;
	}

	/**
	 * Get a collection of items
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$prepared_args = array(
			'exclude'    => $request['exclude'],
			'include'    => $request['include'],
			'order'      => $request['order'],
			'orderby'    => $request['orderby'],
			'post'       => $request['post'],
			'hide_empty' => $request['hide_empty'],
			'number'     => $request['per_page'],
			'search'     => $request['search'],
			'slug'       => $request['slug'],
		);

		if ( ! empty( $request['offset'] ) ) {

			$prepared_args['offset'] = $request['offset'];

		} else {

			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['number'];
		}

		//$taxonomy_obj = get_taxonomy( $this->taxonomy );

		//if ( $taxonomy_obj->hierarchical && isset( $request['parent'] ) ) {
		if ( 0 === $request['parent'] ) {
			// Only query top-level terms.
			$prepared_args['parent'] = 0;
		} else {
			if ( $request['parent'] ) {
				$prepared_args['parent'] = $request['parent'];
			}
		}
		//}

		/**
		 * Filter the query arguments, before passing them to `cnTerm::getTaxonomyTerms()`.
		 *
		 * Enables adding extra arguments or setting defaults for a terms
		 * collection request.
		 *
		 * @since 8.5.26
		 *
		 * @param array           $prepared_args Array of arguments to be
		 *                                       passed to get_terms.
		 * @param WP_REST_Request $request       The current request.
		 */
		$prepared_args = apply_filters( "cn_rest_{$this->taxonomy}_query", $prepared_args, $request );

		if ( ! empty( $prepared_args['post'] )  ) {

			$query_result = $this->get_terms_for_post( $prepared_args );
			$total_terms = $this->total_terms;

		} else {

			$query_result = cnTerm::getTaxonomyTerms( $this->taxonomy, $prepared_args );

			$count_args = $prepared_args;
			unset( $count_args['number'] );
			unset( $count_args['offset'] );
			$count_args['hide_empty'] = FALSE;
			$count_args['fields']     = 'count';
			$total_terms = cnTerm::getTaxonomyTerms( $this->taxonomy, $count_args );

			// Ensure we don't return results when offset is out of bounds
			// see https://core.trac.wordpress.org/ticket/35935
			if ( $prepared_args['offset'] >= $total_terms ) {

				$query_result = array();
			}

			// $total_terms can be a falsy value when the term has no children
			if ( ! $total_terms ) {
				$total_terms = 0;
			}
		}

		$response = array();

		foreach ( $query_result as $term ) {

			$data = $this->prepare_item_for_response( $term, $request );
			$response[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $response );

		// Store pagination values for headers then unset for count query.
		$per_page = (int) $prepared_args['number'];
		$page = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );

		$response->header( 'X-WP-Total', (int) $total_terms );

		$max_pages = ceil( $total_terms / $per_page );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base = add_query_arg( $request->get_query_params(), rest_url( $this->namespace . '/' . $this->rest_base ) );

		if ( $page > 1 ) {

			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {

				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $page ) {

			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Check if a given request has access to read a term.
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( 'edit' === $request['context'] && ! current_user_can( 'connections_edit_categories' ) ) {

			return new WP_Error(
				'rest_forbidden_context',
				__( 'Permission denied. Edit capability required.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return TRUE;
	}

	/**
	 * Get one item from the collection
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$term = cnTerm::get( (int) $request['id'], $this->taxonomy );

		if ( ! $term || $term->taxonomy !== $this->taxonomy ) {

			return new WP_Error( 'rest_term_invalid', __( "Term doesn't exist.", 'connections' ), array( 'status' => 404 ) );
		}

		if ( is_wp_error( $term ) ) {

			return $term;
		}

		$response = $this->prepare_item_for_response( $term, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Check if a given request has access to create a term
	 *
	 * @access public
	 * @since 8.5.26
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {

		if ( ! current_user_can( 'connections_edit_categories' ) ) {

			return new WP_Error( 'rest_cannot_create', __( 'Permission denied. Edit capability required.', 'connections' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Create one item from the collection
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {

		if ( isset( $request['parent'] ) ) {

			//if ( ! is_taxonomy_hierarchical( $this->taxonomy ) ) {
			//
			//	return new WP_Error( 'rest_taxonomy_not_hierarchical', __( 'Can not set resource parent, taxonomy is not hierarchical.' ), array( 'status' => 400 ) );
			//}

			$parent = cnTerm::get( (int) $request['parent'], $this->taxonomy );

			if ( ! $parent ) {

				return new WP_Error( 'rest_term_invalid', __( "Parent term doesn't exist.", 'connections' ), array( 'status' => 400 ) );
			}
		}

		$prepared_term = $this->prepare_item_for_database( $request );

		$term = cnTerm::insert( $prepared_term->name, $this->taxonomy, $prepared_term );

		if ( is_wp_error( $term ) ) {

			// If we're going to inform the client that the term exists, give them the identifier they can actually use.

			if ( ( $term_id = $term->get_error_data( 'term_exists' ) ) ) {

				$existing_term = cnTerm::get( $term_id, $this->taxonomy );

				$term->add_data( $existing_term->term_id, 'term_exists' );
			}

			return $term;
		}

		$term = cnTerm::get( $term['term_id'], $this->taxonomy );

		/**
		 * Fires after a single term is created or updated via the REST API.
		 *
		 * @since 8.5.26
		 *
		 * @param WP_Term         $term     Inserted Term object.
		 * @param WP_REST_Request $request   Request object.
		 * @param boolean         $creating  True when creating term, false when updating.
		 */
		do_action( "cn_rest_insert_{$this->taxonomy}", $term, $request, true );

		$fields_update = $this->update_additional_fields_for_object( $term, $request );

		if ( is_wp_error( $fields_update ) ) {

			return $fields_update;
		}

		$request->set_param( 'context', 'view' );

		$response = $this->prepare_item_for_response( $term, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( $this->namespace . '/' . $this->rest_base . '/' . $term->term_id ) );

		return $response;
	}

	/**
	 * Check if a given request has access to update a term
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {

		$term = cnTerm::get( (int) $request['id'], $this->taxonomy );

		if ( ! $term ) {

			return new WP_Error( 'rest_term_invalid', __( "Term doesn't exist.", 'connections' ), array( 'status' => 404 ) );
		}

		if ( ! current_user_can( 'connections_edit_categories' ) ) {

			return new WP_Error( 'rest_cannot_update', __( 'Permission denied. Edit capability required.', 'connections' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return TRUE;
	}

	/**
	 * Update one item from the collection
	 *
	 * @access public
	 * @since 8.5.26
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {

		if ( isset( $request['parent'] ) ) {

			//if ( ! is_taxonomy_hierarchical( $this->taxonomy ) ) {
			//
			//	return new WP_Error( 'rest_taxonomy_not_hierarchical', __( 'Can not set resource parent, taxonomy is not hierarchical.' ), array( 'status' => 400 ) );
			//}

			$parent = cnTerm::get( (int) $request['parent'], $this->taxonomy );

			if ( ! $parent ) {

				return new WP_Error( 'rest_term_invalid', __( "Parent term doesn't exist.", 'connections' ), array( 'status' => 400 ) );
			}
		}

		$prepared_term = $this->prepare_item_for_database( $request );

		$term = cnTerm::get( (int) $request['id'], $this->taxonomy );

		// Only update the term if we haz something to update.
		if ( ! empty( $prepared_term ) ) {

			$update = cnTerm::update( $term->term_id, $term->taxonomy, (array) $prepared_term );

			if ( is_wp_error( $update ) ) {

				return $update;
			}
		}

		$term = cnTerm::get( (int) $request['id'], $this->taxonomy );

		/* This action is documented in lib/endpoints/class-wp-rest-terms-controller.php */
		do_action( "cn_rest_insert_{$this->taxonomy}", $term, $request, FALSE );

		$fields_update = $this->update_additional_fields_for_object( $term, $request );

		if ( is_wp_error( $fields_update ) ) {

			return $fields_update;
		}

		$request->set_param( 'context', 'view' );

		$response = $this->prepare_item_for_response( $term, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Check if a given request has access to delete a term
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {

		$term = cnTerm::get( (int) $request['id'], $this->taxonomy );

		if ( ! $term ) {

			return new WP_Error( 'rest_term_invalid', __( "Term doesn't exist.", 'connections' ), array( 'status' => 404 ) );
		}

		if ( ! current_user_can( 'connections_edit_categories' ) ) {

			return new WP_Error( 'rest_cannot_delete', __( 'Permission denied. Edit capability required.', 'connections' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return TRUE;
	}

	/**
	 * Delete a single term from a taxonomy
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param WP_REST_Request $request Full details about the request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {

		$force = isset( $request['force'] ) ? (bool) $request['force'] : FALSE;

		// We don't support trashing for this type, error out
		if ( ! $force ) {

			return new WP_Error( 'rest_trash_not_supported', __( 'Terms do not support trashing. Set force=true to delete.', 'connections' ), array( 'status' => 501 ) );
		}

		$term = cnTerm::get( (int) $request['id'], $this->taxonomy );

		$request->set_param( 'context', 'view' );
		$response = $this->prepare_item_for_response( $term, $request );

		$retval = cnTerm::delete( $term->term_id, $term->taxonomy );

		if ( ! $retval ) {

			return new WP_Error( 'rest_cannot_delete', __( 'Term cannot be deleted.', 'connections' ), array( 'status' => 500 ) );
		}

		/**
		 * Fires after a single term is deleted via the REST API.
		 *
		 * @param WP_Term          $term     The deleted term.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( "cn_rest_delete_{$this->taxonomy}", $term, $response, $request );

		return $response;
	}

	/**
	 * Prepare a single term for create or update.
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return object $prepared_term Term object.
	 */
	public function prepare_item_for_database( $request ) {

		$prepared_term = new stdClass;

		$schema = $this->get_item_schema();

		if ( isset( $request['name'] ) && ! empty( $schema['properties']['name'] ) ) {

			$prepared_term->name = $request['name'];
		}

		if ( isset( $request['slug'] ) && ! empty( $schema['properties']['slug'] ) ) {

			$prepared_term->slug = $request['slug'];
		}

		if ( isset( $request['taxonomy'] ) && ! empty( $schema['properties']['taxonomy'] ) ) {

			$prepared_term->taxonomy = $request['taxonomy'];
		}

		if ( isset( $request['description'] ) && ! empty( $schema['properties']['description'] ) ) {

			$prepared_term->description = $request['description'];
		}

		if ( isset( $request['parent'] ) && ! empty( $schema['properties']['parent'] ) ) {

			$parent_term_id = 0;
			$parent_term    = cnTerm::get( (int) $request['parent'], $this->taxonomy );

			if ( $parent_term ) {
				$parent_term_id = $parent_term->term_id;
			}

			$prepared_term->parent = $parent_term_id;
		}

		/**
		 * Filter term data before inserting term via the REST API.
		 *
		 * @since 8.5.26
		 *
		 * @param object          $prepared_term Term object.
		 * @param WP_REST_Request $request       Request object.
		 */
		return apply_filters( "cn_rest_pre_insert_{$this->taxonomy}", $prepared_term, $request );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @param mixed           $item    WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {

		$schema = $this->get_item_schema();
		$data   = array();

		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = (int) $item->term_id;
		}

		if ( ! empty( $schema['properties']['count'] ) ) {
			$data['count'] = (int) $item->count;
		}

		if ( ! empty( $schema['properties']['description'] ) ) {
			$data['description'] = $item->description;
		}

		if ( ! empty( $schema['properties']['link'] ) ) {
			$data['link'] = get_term_link( $item );
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			$data['name'] = $item->name;
		}

		if ( ! empty( $schema['properties']['slug'] ) ) {
			$data['slug'] = $item->slug;
		}

		if ( ! empty( $schema['properties']['taxonomy'] ) ) {
			$data['taxonomy'] = $item->taxonomy;
		}

		if ( ! empty( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $item->parent;
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filter a term item returned from the API.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param object           $item     The original term object.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters( "cn_rest_prepare_{$this->taxonomy}", $response, $item, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 * @since  8.5.26
	 *
	 * @param object $term Term object.
	 *
	 * @return array Links for the given term.
	 */
	protected function prepare_links( $term ) {

		$base  = $this->namespace . '/' . $this->base;
		$links = array(
			'self'       => array(
				/** @todo THIS IS WRONG, s/b set to directory home page! */
				'href' => rest_url( trailingslashit( $base ) . $term->term_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'about'      => array(
				'href' => rest_url( sprintf( 'cn-api/v1/taxonomies/%s', $this->taxonomy ) ),
			),
		);

		if ( $term->parent ) {

			$parent_term = cnTerm::get( (int) $term->parent, $term->taxonomy );

			if ( $parent_term ) {

				$links['up'] = array(
					'href'       => rest_url( trailingslashit( $base ) . $parent_term->term_id ),
					'embeddable' => TRUE,
				);
			}
		}

		//$taxonomy_obj = get_taxonomy( $term->taxonomy );

		//if ( empty( $taxonomy_obj->object_type ) ) {
		//
		//	return $links;
		//}

		//$post_type_links = array();

		//foreach ( $taxonomy_obj->object_type as $type ) {
		//
		//	$post_type_object = get_post_type_object( $type );
		//
		//	if ( empty( $post_type_object->show_in_rest ) ) {
		//
		//		continue;
		//	}
		//
		//	$rest_base         = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
		//	$post_type_links[] = array(
		//		'href' => add_query_arg( $this->rest_base, $term->term_id, rest_url( sprintf( 'wp/v2/%s', $rest_base ) ) ),
		//	);
		//}

		//if ( ! empty( $post_type_links ) ) {
		//
		//	$links['https://api.w.org/post_type'] = $post_type_links;
		//}

		return $links;
	}

	/**
	 * Get the Term's schema, conforming to JSON Schema
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => $this->taxonomy,
			'type'                 => 'object',
			'properties'           => array(
				'id'               => array(
					'description'  => __( 'Unique identifier for the resource.', 'connections' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'embed', 'edit' ),
					'readonly'     => true,
				),
				'count'            => array(
					'description'  => __( 'Number of published posts for the resource.', 'connections' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'description'      => array(
					'description'  => __( 'HTML description of the resource.', 'connections' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'link'             => array(
					'description'  => __( 'URL to the resource.', 'connections' ),
					'type'         => 'string',
					'format'       => 'uri',
					'context'      => array( 'view', 'embed', 'edit' ),
					'readonly'     => true,
				),
				'name'             => array(
					'description'  => __( 'HTML title for the resource.', 'connections' ),
					'type'         => 'string',
					'context'      => array( 'view', 'embed', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'     => true,
				),
				'slug'             => array(
					'description'  => __( 'An alphanumeric identifier for the resource unique to its type.', 'connections' ),
					'type'         => 'string',
					'context'      => array( 'view', 'embed', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => array( $this, 'sanitize_slug' ),
					),
				),
				'taxonomy'         => array(
					'description'  => __( 'Type attribution for the resource.', 'connections' ),
					'type'         => 'string',
					'enum'         => array( $this->taxonomy ),
					'context'      => array( 'view', 'embed', 'edit' ),
					'readonly'     => true,
				),
				'parent'           => array(
					'description'  => __( 'The id for the parent of the resource.', 'connections' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @return array
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();
		//$taxonomy = get_taxonomy( $this->taxonomy );

		$query_params['context']['default'] = 'view';

		$query_params['exclude'] = array(
			'description'        => __( 'Ensure result set excludes specific ids.', 'connections' ),
			'type'               => 'array',
			'default'            => array(),
			'sanitize_callback'  => 'wp_parse_id_list',
		);

		$query_params['include'] = array(
			'description'        => __( 'Limit result set to specific ids.', 'connections' ),
			'type'               => 'array',
			'default'            => array(),
			'sanitize_callback'  => 'wp_parse_id_list',
		);

		$query_params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.', 'connections' ),
			'type'               => 'integer',
			'sanitize_callback'  => 'absint',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		$query_params['order']      = array(
			'description'           => __( 'Order sort attribute ascending or descending.', 'connections' ),
			'type'                  => 'string',
			'sanitize_callback'     => 'sanitize_key',
			'default'               => 'asc',
			'enum'                  => array(
				'asc',
				'desc',
			),
			'validate_callback'     => 'rest_validate_request_arg',
		);

		$query_params['orderby']    = array(
			'description'           => __( 'Sort collection by resource attribute.', 'connections' ),
			'type'                  => 'string',
			'sanitize_callback'     => 'sanitize_key',
			'default'               => 'name',
			'enum'                  => array(
				'id',
				'include',
				'name',
				'slug',
				'term_group',
				'description',
				'count',
			),
			'validate_callback'     => 'rest_validate_request_arg',
		);

		$query_params['hide_empty'] = array(
			'description'           => __( 'Whether to hide resources not assigned to any posts.', 'connections' ),
			'type'                  => 'boolean',
			'default'               => FALSE,
			'validate_callback'     => 'rest_validate_request_arg',
		);

		$query_params['parent'] = array(
			'description'        => __( 'Limit result set to resources assigned to a specific parent.', 'connections' ),
			'type'               => 'integer',
			'sanitize_callback'  => 'absint',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		$query_params['post'] = array(
			'description'           => __( 'Limit result set to resources assigned to a specific post.', 'connections' ),
			'type'                  => 'integer',
			'default'               => NULL,
			'validate_callback'     => 'rest_validate_request_arg',
		);

		$query_params['slug']    = array(
			'description'        => __( 'Limit result set to resources with a specific slug.', 'connections' ),
			'type'               => 'string',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		return $query_params;
	}
}

