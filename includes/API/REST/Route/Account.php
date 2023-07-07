<?php
/**
 * The User account actions such as user log in, registration, and password reset.
 *
 * @since 10.4.46
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections_Directory\API\REST\Route
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\API\REST\Route;

use Connections_Directory\API\REST\Route;
use Connections_Directory\Form;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_token;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_User;

/**
 * Class Account
 *
 * @package Connections_Directory\API\REST\Route
 */
class Account extends WP_REST_Controller {

	use Route;

	/**
	 * REST API version.
	 *
	 * @since 10.4.46
	 */
	const VERSION = '1';

	/**
	 * The REST namespace.
	 *
	 * @since 10.4.46
	 * @var string
	 */
	protected $namespace;

	/**
	 * The REST base name.
	 *
	 * @since 10.4.46
	 * @var string
	 */
	protected $base = 'account';

	/**
	 * Constructor.
	 *
	 * @since 10.4.46
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 10.4.46
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/login',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'login' ),
					'args'                => array(
						'_cnonce'  => array(
							'required'    => true,
							'description' => __( 'The request token.', 'connections' ),
							'type'        => 'string',
						),
						'log'      => array(
							'required'    => true,
							'description' => __( 'Username or email.', 'connections' ),
							'type'        => 'string',
						),
						'pwd'      => array(
							'required'    => true,
							'description' => __( 'Password.', 'connections' ),
							'type'        => 'string',
						),
						'redirect' => array(
							'required'          => false,
							'description'       => __( 'The URL to redirect to after form submission.', 'connections' ),
							'validate_callback' => 'wp_http_validate_url',
							'sanitize_callback' => 'sanitize_url',
							'type'              => 'string',
							'format'            => 'uri',
						),
					),
					'permission_callback' => '__return_true',
				),
				// 'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Login user.
	 *
	 * @param WP_REST_Request $request API request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function login( WP_REST_Request $request ) {

		$data      = array();
		$response  = new WP_REST_Response();
		$invalid   = new WP_Error( 'rest_invalid_user', esc_html__( 'Username or password is incorrect.', 'connections' ), array( 'status' => 401 ) );
		$forbidden = new WP_Error( 'rest_forbidden', esc_html__( 'Bad Request.', 'connections' ), array( 'status' => 400 ) );

		// Check permissions.
		if ( is_user_logged_in() ) {

			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Permission denied.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Initialize the form for validation.
		$form = new Form\User_Login();

		// Drop any request parameters that have no registered fields in the form.
		$parameters = array_intersect_key( $request->get_params(), $form->getFieldValues() );

		// Feed the request parameters into the form field values.
		$form->setFieldValues( $parameters );
		$form->setRedirect( _array::get( $request->get_params(), 'redirect', '' ) );

		// Validate the form fields against their registered schema.
		$isValid = $form->validate();

		// If the form fields do not pass their schema validation, return a bad request.
		if ( false === $isValid ) {

			return $forbidden;
		}

		// Ensure the supplied nonce token field is valid.
		if ( ! _token::isValid( $form->getFieldValue( '_cnonce' ), 'user/login' ) ) {

			return $forbidden;
		}

		if ( is_email( $form->getFieldValue( 'log' ) ) ) {

			$user = get_user_by( 'email', $form->getFieldValue( 'log' ) );

		} elseif ( validate_username( $form->getFieldValue( 'log' ) ) ) {

			$user = get_user_by( 'login', $form->getFieldValue( 'log' ) );

		} else {

			$user = false;
		}

		if ( ! $user instanceof WP_User ) {

			return $invalid;
		}

		if ( empty( $form->getFieldValue( 'pwd' ) ) ) {

			return $forbidden;
		}

		if ( false !== strpos( $form->getFieldValue( 'pwd' ), '\\' ) ) {

			return $forbidden;
		}

		// Verify password.
		if ( true !== wp_check_password( $form->getFieldValue( 'pwd' ), $user->get( 'user_pass' ), $user->get( 'ID' ) ) ) {

			return $invalid;
		}

		// Authenticate user.
		if ( ! is_user_logged_in() ) {

			do_action( 'Connections_Directory/API/REST/Route/Account/User/Login' );

			wp_signon(
				array(
					'user_login'    => $user->get( 'user_login' ),
					'user_password' => $form->getFieldValue( 'pwd' ),
					'remember'      => true,
				)
			);
		}

		// Setup response.
		$response->set_status( 200 );
		$data['id'] = $user->get( 'ID' );

		if ( 0 < strlen( $form->getRedirect() ) ) {

			// $response->set_status( 307 );
			$data['redirect'] = $form->getSafeRedirect();

		} else {

			$data['reload'] = true;
		}

		$response->set_data( $data );

		return $response;
	}
}
