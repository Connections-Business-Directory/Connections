<?php
/**
 * REST API Autocomplete Controller
 *
 * @author   Steven A. Zahm
 * @category API
 * @package  Connections/API
 * @since    8.38
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Countries Controller.
 *
 * @package Connections/API
 * @extends WP_REST_Controller
 */
class CN_REST_Autocomplete_Controller extends WP_REST_Controller {

	/**
	 * @since 8.38
	 */
	const VERSION = '1';

	/**
	 * @since 8.38
	 * @var string
	 */
	protected $namespace;

	/**
	 * @since 8.38
	 * @var string
	 */
	protected $base = 'autocomplete';

	/**
	 * @since 8.38
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 8.38
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<type>name|last_name|title|organization|department|district|county|city|state|zipcode|country)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'autocomplete' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * @since 8.38
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function autocomplete( $request ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$response  = array();
		//$total     = 0;
		$endpoints = array(
			'name',
			'last_name',
			'title',
			'organization',
			'department',
			'district',
			'county',
			'city',
			'state',
			'zipcode',
			'country',
		);

		$args = array(
			//'exclude'    => $request['exclude'],
			//'include'    => $request['include'],
			'order'      => $request['order'],
			'orderby'    => $request['orderby'],
			//'post'       => $request['post'],
			//'hide_empty' => $request['hide_empty'],
			'number'     => cnArray::get( $request, 'per_page' ),
			'offset'     => cnArray::get( $request, 'offset' ),
			'search'     => cnArray::get( $request, 'search' ),
			'type'       => cnArray::get( $request, 'type' ),
			//'slug'       => $request['slug'],
		);

		if ( ! in_array( $args['type'], $endpoints ) ) {

			return new WP_Error(
				'invalid_type',
				__( 'Invalid autocomplete type.', 'connections' ),
				$args['type']
			);
		}

		// Need more than one character to return a response.
		if ( 1 >= strlen( $args['search'] ) ) {

			return rest_ensure_response( $response );

			//return new WP_Error(
			//	'invalid_string_length',
			//	__( 'Autocomplete query string must be two or more characters.', 'connections' ),
			//	$args['search']
			//);
		}

		$terms = preg_split( '/[\s,]+/', $args['search'] );

		// If the preg_split() fails, return.
		if ( FALSE === $terms ) {

			return rest_ensure_response( $response );
		}

		if ( ! empty( $request['offset'] ) ) {

			$args['offset'] = $request['offset'];

		} else {

			$args['offset'] = ( cnArray::get( $request, 'page' ) - 1 ) * $args['number'];
		}

		$index = array_search( $args['type'], $endpoints );
		$type  = $endpoints[ $index ];

		$select = array();
		$from   = array();
		$where  = array();
		$group  = array( $type );
		$limit  = absint( $args['number'] );
		$offset = absint( $args['offset'] );

		switch ( $type ) {

			case 'name':

				$select[] = 'name.*';
				$from[]   = '(SELECT ' . CN_ENTRY_TABLE . '.*, CASE `entry_type`
							  WHEN \'individual\' THEN CONCAT( `first_name`, \' \',  `last_name` )
							  WHEN \'organization\' THEN `organization`
							  WHEN \'connection_group\' THEN `family_name`
							  WHEN \'family\' THEN `family_name`
							END AS `name` FROM ' . CN_ENTRY_TABLE . ') AS `name`';

				if ( 1 === count( $terms ) ) {

					$where[] = $type . ' ' . $wpdb->prepare('LIKE %s', $wpdb->esc_like( $terms[0] ) . '%' );
					$where[] = $wpdb->prepare( 'OR `id` = %d', $terms[0] );

				} else {

					$where[] = $type . ' ' . cnQuery::in( $terms, '%s' );
					$where[] = 'OR `id` ' . cnQuery::in( $terms, '%d' );
				}

				break;

			case 'last_name':
			case 'family_name':
			case 'title':
			case 'organization':
			case 'department':

				$select[] = CN_ENTRY_TABLE . '.*';
				$from[]   = CN_ENTRY_TABLE;

				if ( 1 === count( $terms ) ) {

					$where[] = $type . ' ' . $wpdb->prepare('LIKE %s', $wpdb->esc_like( $terms[0] ) . '%' );

				} else {

					$where[] = $type . ' ' . cnQuery::in( $terms, '%s' );
				}

				break;

			case 'district':
			case 'county':
			case 'city':
			case 'state':
			case 'zipcode':
			case 'country':

				$select[] = CN_ENTRY_ADDRESS_TABLE . '.*';
				$from[]   = CN_ENTRY_ADDRESS_TABLE;

				if ( 1 === count( $terms ) ) {

					$where[] = $type . ' ' . $wpdb->prepare('LIKE %s', $wpdb->esc_like( $terms[0] ) . '%' );

				} else {

					$where[] = $type . ' ' . cnQuery::in( $terms, '%s' );
				}

				break;
		}

		$sql = sprintf(
			'SELECT SQL_CALC_FOUND_ROWS %s FROM %s WHERE %s GROUP BY %s LIMIT %d OFFSET %d',
			implode( ', ', $select ), // SELECT
			implode( ', ', $from ),   // FROM
			implode( ' ', $where ),   // WHERE
			implode( ', ', $group ),  // GROUP BY
			$limit,                         // LIMIT
			$offset                         // OFFSET
		);

		error_log( $sql );

		$response = $wpdb->get_results( $sql, ARRAY_A );
		$count    = $wpdb->get_results( 'SELECT FOUND_ROWS() AS total' );
		$total    = $count[0]->total;

		$response = rest_ensure_response( $response );

		// Store pagination values for headers then unset for count query.
		$per_page = (int) $args['number'];
		$page     = ceil( ( ( (int) $args['offset'] ) / $per_page ) + 1 );

		$response->header( 'X-WP-Total', (int) $total );

		$max_pages = ceil( $total / $per_page );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base = add_query_arg( $request->get_query_params(), rest_url( $this->namespace . '/' . $this->base . '/' . $args['type'] ) );

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
	 * Get the query params for collections
	 *
	 * @access public
	 * @since  8.5.26
	 *
	 * @return array
	 */
	public function get_collection_params() {

		$query_params = array(
			'context'                => $this->get_context_param(),
			'page'                   => array(
				'description'        => __( 'Current page of the collection.' ),
				'type'               => 'integer',
				'default'            => 1,
				'sanitize_callback'  => 'absint',
				'validate_callback'  => 'rest_validate_request_arg',
				'minimum'            => 1,
			),
			'per_page'               => array(
				'description'        => __( 'Maximum number of items to be returned in result set.' ),
				'type'               => 'integer',
				'default'            => 10,
				'minimum'            => 1,
				'maximum'            => 100,
				'sanitize_callback'  => 'absint',
				'validate_callback'  => 'rest_validate_request_arg',
			),
			'search'                 => array(
				'description'        => __( 'Limit results to those matching a string.' ),
				'type'               => 'string',
				'sanitize_callback'  => 'sanitize_text_field',
				'validate_callback'  => 'rest_validate_request_arg',
			),
		);

		$query_params['context']['default'] = 'view';

		//$query_params['exclude'] = array(
		//	'description'        => __( 'Ensure result set excludes specific ids.', 'connections' ),
		//	'type'               => 'array',
		//	'default'            => array(),
		//	'sanitize_callback'  => 'wp_parse_id_list',
		//);

		//$query_params['include'] = array(
		//	'description'        => __( 'Limit result set to specific ids.', 'connections' ),
		//	'type'               => 'array',
		//	'default'            => array(),
		//	'sanitize_callback'  => 'wp_parse_id_list',
		//);

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

		//$query_params['hide_empty'] = array(
		//	'description'           => __( 'Whether to hide resources not assigned to any posts.', 'connections' ),
		//	'type'                  => 'boolean',
		//	'default'               => FALSE,
		//	'validate_callback'     => 'rest_validate_request_arg',
		//);

		//$query_params['parent'] = array(
		//	'description'        => __( 'Limit result set to resources assigned to a specific parent.', 'connections' ),
		//	'type'               => 'integer',
		//	'sanitize_callback'  => 'absint',
		//	'validate_callback'  => 'rest_validate_request_arg',
		//);

		//$query_params['post'] = array(
		//	'description'           => __( 'Limit result set to resources assigned to a specific post.', 'connections' ),
		//	'type'                  => 'integer',
		//	'default'               => NULL,
		//	'validate_callback'     => 'rest_validate_request_arg',
		//);

		//$query_params['slug']    = array(
		//	'description'        => __( 'Limit result set to resources with a specific slug.', 'connections' ),
		//	'type'               => 'string',
		//	'validate_callback'  => 'rest_validate_request_arg',
		//);

		return $query_params;
	}
}
