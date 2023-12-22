<?php
/**
 * REST API Settings Controller
 *
 * @author     Steven A. Zahm
 * @category   API
 * @package    Connections
 * @subpackage REST_API
 * @since      9.3
 */

namespace Connections_Directory\API\REST\Route;

use cnSettingsAPI;
use Connections_Directory\API\REST\Route;
use Connections_Directory\Utility\_array;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Settings_Controller;

/**
 * Manage a site's settings via the REST API.
 *
 * Extend the core WordPress Setting REST API to add support for getting option by name.
 *
 * @since 9.3
 *
 * @see WP_REST_Settings_Controller
 */
class Settings extends WP_REST_Settings_Controller {

	use Route;

	/**
	 * API version.
	 *
	 * @since 9.3
	 * @var   string
	 */
	const VERSION = '1';

	/**
	 * Constructor.
	 *
	 * @since 9.3
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
		$this->rest_base = 'settings';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 9.3
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

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<option>[a-zA-Z0-9-_]+)',
			array(
				'args'   => array(
					'option' => array(
						'description' => __( 'Unique identifier for the object.', 'connections' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get a settings option group.
	 *
	 * @since 9.3
	 *
	 * @param WP_REST_Request $request The Request object.
	 *
	 * @return array|mixed|void|WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$options = $this->get_registered_options();

		if ( ! _array::exists( $options, $request['option'] ) ) {

			return new WP_Error(
				'rest_invalid_option_name',
				/* translators: Setting option name. */
				sprintf( __( 'The %s option name is invalid.', 'connections' ), $request['option'] ),
				array( 'status' => 404 )
			);
		}

		$name = $request['option'];
		$args = _array::get( $options, $name, null );

		/**
		 * Filters the value of a setting recognized by the REST API.
		 *
		 * Allow hijacking the setting value and overriding the built-in behavior by returning a
		 * non-null value.  The returned value will be presented as the setting value instead.
		 *
		 * @since 4.7.0
		 *
		 * @param mixed  $result Value to use for the requested setting. Can be a scalar
		 *                       matching the registered schema for the setting, or null to
		 *                       follow the default get_option() behavior.
		 * @param string $name   Setting name (as shown in REST API responses).
		 * @param array  $args   Arguments passed to register_setting() for this setting.
		 */
		$response = apply_filters(
			'Connections_Directory/API/REST/Route/Settings/Before/Get/Value',
			null,
			$name,
			$args
		);

		if ( is_null( $response ) ) {
			// Default to a null value as "null" in the response means "not set".
			$response = get_option( $args['option_name'], $args['schema']['default'] );
		}

		/*
		 * Because get_option() is lossy, we have to
		 * cast values to the type they are registered with.
		 */
		return $this->prepare_value( $response, $args['schema'] );
	}

	/**
	 * Retrieves the settings.
	 *
	 * @since 9.3
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array|WP_Error Array on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		$options  = $this->get_registered_options();
		$response = array();

		foreach ( $options as $name => $args ) {
			/**
			 * Filters the value of a setting recognized by the REST API.
			 *
			 * Allow hijacking the setting value and overriding the built-in behavior by returning a
			 * non-null value.  The returned value will be presented as the setting value instead.
			 *
			 * Filter added to match core WP Settings REST endpoint.
			 *
			 * @since 9.3
			 *
			 * @param mixed  $result Value to use for the requested setting. Can be a scalar
			 *                       matching the registered schema for the setting, or null to
			 *                       follow the default get_option() behavior.
			 * @param string $name   Setting name (as shown in REST API responses).
			 * @param array  $args   Arguments passed to register_setting() for this setting.
			 */
			$response[ $name ] = apply_filters(
				'Connections_Directory/API/REST/Route/Settings/Before/Get/Value',
				null,
				$name,
				$args
			);

			if ( is_null( $response[ $name ] ) ) {
				// Default to a null value as "null" in the response means "not set".
				$response[ $name ] = get_option( $args['option_name'], $args['schema']['default'] );
			}

			/*
			 * Because get_option() is lossy, we have to
			 * cast values to the type they are registered with.
			 */
			$response[ $name ] = $this->prepare_value( $response[ $name ], $args['schema'] );
		}

		return $response;
	}

	/**
	 * Updates settings for the settings object.
	 *
	 * @since 9.3
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array|mixed|true|void|WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$options = $this->get_registered_options();
		$name    = $request['option'];
		$params  = $request->get_params();

		// The request params also contain the option name, remove it from the array.
		_array::forget( $params, 'option' );

		if ( ! _array::exists( $options, $name ) ) {

			return new WP_Error(
				'rest_invalid_option_name',
				/* translators: Setting option name. */
				sprintf( __( 'The %s option name is invalid.', 'connections' ), $request['option'] ),
				array( 'status' => 404 )
			);
		}

		$args = _array::get( $options, $name, null );

		/**
		 * Filters whether to preempt a setting value update.
		 *
		 * Allows hijacking the setting update logic and overriding the built-in behavior by
		 * returning true.
		 *
		 * Filter added to match core WP Settings REST endpoint.
		 *
		 * @since 9.3
		 *
		 * @param bool   $result Whether to override the default behavior for updating the
		 *                       value of a setting.
		 * @param string $name   Setting name (as shown in REST API responses).
		 * @param mixed  $value  Updated setting value.
		 * @param array  $args   Arguments passed to register_setting() for this setting.
		 */
		$updated = apply_filters(
			'Connections_Directory/API/REST/Route/Settings/Before/Update/Value',
			false,
			$name,
			$params,
			$args
		);

		if ( $updated ) {

			return $this->get_item( $request );
		}

		/*
		 * A null value for an option would have the same effect as
		 * deleting the option from the database, and relying on the
		 * default value.
		 */
		if ( empty( $params ) ) {

			/*
			 * A null value is returned in the response for any option
			 * that has a non-scalar value.
			 *
			 * To protect clients from accidentally including the null
			 * values from a response object in a request, we do not allow
			 * options with values that don't pass validation to be updated to null.
			 * Without this added protection a client could mistakenly
			 * delete all options that have invalid values from the
			 * database.
			 */
			if ( is_wp_error( rest_validate_value_from_schema( get_option( $args['option_name'], false ), $args['schema'] ) ) ) {
				return new WP_Error(
					'rest_invalid_stored_value',
					/* translators: Setting option name. */
					sprintf( __( 'The %s property has an invalid stored value, and cannot be updated to null.', 'connections' ), $name ),
					array( 'status' => 500 )
				);
			}

			/*
			 * This matches the core WP Settings REST API route but does seem odd...
			 * Added a DELETE route which should be used.
			 *
			 * @todo Perhaps return an error message instead, informing client to use the DELETE route instead.
			 */
			delete_option( $args['option_name'] );

		} else {

			// Validate supplied params against the setting's schema.
			$isValid = rest_validate_value_from_schema( $params, $args['schema'], $args['option_name'] );

			if ( is_wp_error( $isValid ) ) {

				return $isValid;

			} else {

				// Sanitize the supplied params using the setting's schema and update the option.
				update_option( $args['option_name'], rest_sanitize_value_from_schema( $params, $args['schema'] ) );
			}

		}

		return $this->get_item( $request );
	}

	/**
	 * Deletes a setting options group.
	 *
	 * @since 9.3
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {

		$options = $this->get_registered_options();

		if ( ! _array::exists( $options, $request['option'] ) ) {

			return new WP_Error(
				'rest_invalid_option_name',
				/* translators: Setting option name. */
				sprintf( __( 'The %s option name is invalid.', 'connections' ), $request['option'] ),
				array( 'status' => 404 )
			);
		}

		$name = $request['option'];
		$args = _array::get( $options, $name, null );

		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $this->get_item( $request ),
			)
		);

		$result = delete_option( $args['option_name'] );

		if ( ! $result ) {

			return new WP_Error(
				'rest_cannot_delete',
				__( 'The option cannot be deleted.', 'connections' ),
				array( 'status' => 500 )
			);
		}

		return $response;
	}

	/**
	 * Retrieves all the registered REST options from the core WP Settings API
	 * but limited to those registered with the Connections Settings API.
	 *
	 * @since 9.3
	 *
	 * @see cnSettingsAPI.
	 *
	 * @return array
	 */
	protected function get_registered_options() {

		return array_intersect_key(
			parent::get_registered_options(),
			cnSettingsAPI::getRegisteredRESTOptionProperties()
		);
	}
}
