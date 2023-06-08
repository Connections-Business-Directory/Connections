<?php

namespace Connections_Directory\API\REST\Route;

use cnArray;
use cnOutput;
use Connections_Directory\API\REST\Route;
use Connections_Directory\Utility\_;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Recently_Viewed
 *
 * @package Connections_Directory\API\REST\Endpoint
 */
class Recently_Viewed extends Entry {

	use Route;

	/**
	 * @since 9.10
	 */
	const VERSION = '1';

	/**
	 * @since 9.10
	 * @var string
	 */
	protected $namespace;

	/**
	 * @since 9.10
	 *
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
		$this->rest_base = 'recently_viewed';
	}

	/**
	 * Registers the route for the Recently Viewed controller.
	 *
	 * @since 9.10
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Process the recently viewed cookie to get the array Entry ID and post ID.
	 *
	 * @since 9.10
	 *
	 * @return array
	 */
	protected function getRecent() {

		$cookie = cnArray::get( $_COOKIE, 'cnRecentlyViewed', '[]' );
		$recent = _::maybeJSONdecode( html_entity_decode( stripslashes( $cookie ) ) );
		// error_log( var_export( $recent, true ) );

		if ( ! is_array( $recent ) ) {

			return array( $recent );
		}

		return $recent;
	}

	/**
	 * Get a collection of Entries.
	 *
	 * @since 9.10
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$request->set_param( 'recently_viewed', $this->getRecent() );

		return parent::get_items( $request );
	}

	/**
	 * @since 9.10
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param array           $untrusted
	 *
	 * @return array
	 */
	protected function get_entries( WP_REST_Request $request, array $untrusted = array() ): array {

		$queryParams = $request->get_query_params();
		$recent      = $request->get_param( 'recently_viewed' );

		$id = wp_list_pluck( $recent, 'entryID' );

		if ( empty( $id ) ) {

			return array();
		}

		$atts = array(
			'id'               => $id,
			'id__not_in'       => cnArray::get( $queryParams, 'exclude', null ),
			'limit'            => cnArray::get( $queryParams, 'per_page', 10 ),
			'offset'           => cnArray::get( $queryParams, 'offset', 0 ),
			'order_by'         => 'id|SPECIFIED',
			'parse_request'    => false,
			'suppress_filters' => true,
		);

		return Connections_Directory()->retrieve->entries( $atts );
	}

	/**
	 * Set the directory home page of the Entry based on the page/post it was viewed on.
	 *
	 * @since 9.10
	 *
	 * @param cnOutput        $entry
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $entry, $request ): WP_REST_Response {

		$recent = $request->get_param( 'recently_viewed' );
		$key    = array_search( $entry->getId(), array_column( $recent, 'entryID' ), true );

		if ( false !== $key ) {

			$entry->directoryHome(
				array(
					'page_id' => $recent[ $key ]['postID'],
				)
			);
		}

		return parent::prepare_item_for_response( $entry, $request );
	}
}
