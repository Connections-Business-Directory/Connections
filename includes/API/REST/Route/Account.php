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
						'_cnonce'    => array(
							'required'    => true,
							'description' => __( 'The request token.', 'connections' ),
							'type'        => 'string',
						),
						'log'        => array(
							'required'    => true,
							'description' => __( 'Username or email.', 'connections' ),
							'type'        => 'string',
						),
						'pwd'        => array(
							'required'    => true,
							'description' => __( 'Password.', 'connections' ),
							'type'        => 'string',
						),
						'redirect'   => array(
							'required'          => false,
							'description'       => __( 'The URL to redirect to after form submission.', 'connections' ),
							'validate_callback' => 'wp_http_validate_url',
							'sanitize_callback' => 'sanitize_url',
							'type'              => 'string',
							'format'            => 'uri',
						),
						'rememberme' => array(
							'required'    => false,
							'description' => __( 'Remember Me', 'connections' ),
							'type'        => 'string',
							'enum'        => array( '0', '1' ),
						),
					),
					'permission_callback' => '__return_true',
				),
				// 'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/request-reset-password',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'requestResetPassword' ),
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

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/reset-password',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'resetPassword' ),
					'args'                => array(
						'_cnonce'  => array(
							'required'    => true,
							'description' => __( 'The request token.', 'connections' ),
							'type'        => 'string',
						),
						'pass1'    => array(
							'required'    => true,
							'description' => __( 'New password.', 'connections' ),
							'type'        => 'string',
						),
						'pass2'    => array(
							'required'    => true,
							'description' => __( 'Confirm password.', 'connections' ),
							'type'        => 'string',
						),
						'pw_weak'  => array(
							'required'          => false,
							'description'       => __( 'Confirm use of weak password.', 'connections' ),
							'type'              => 'boolean',
							// Specify the sanitization callback for the checkbox value.
							'sanitize_callback' => static function( $value, $request, $param ) {
								// Return a boolean value, cast from the input value.
								return boolval( $value );
							},
							// Specify the validation callback for the checkbox value.
							'validate_callback' => static function( $value, $request, $param ) {
								// Return true if the value is a boolean, false otherwise.
								return is_bool( $value );
							},
						),
						'key'      => array(
							'required'    => true,
							'description' => __( 'password reset key.', 'connections' ),
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
					'remember'      => '1' === $form->getFieldValue( 'rememberme' ),
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

	/**
	 * User request reset password.
	 *
	 * @since 10.4.47
	 *
	 * @param WP_REST_Request $request API request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function requestResetPassword( WP_REST_Request $request ) {

		$data      = array();
		$response  = new WP_REST_Response();
		$invalid   = new WP_Error( 'rest_invalid_user', esc_html__( 'Username or password is incorrect.', 'connections' ), array( 'status' => 401 ) );
		$forbidden = new WP_Error( 'rest_forbidden', esc_html__( 'Bad Request.', 'connections' ), array( 'status' => 400 ) );

		// Check permissions. @todo This should move to the `permission_callback` callback.
		if ( is_user_logged_in() && ! current_user_can( 'edit_users' ) ) {

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
		if ( ! _token::isValid( $form->getFieldValue( '_cnonce' ), 'user/request-reset-password' ) ) {

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

		$result = retrieve_password( $user->get( 'user_email' ) );

		if ( $result instanceof WP_Error ) {

			return $result;
		}

		// Setup response.
		$response->set_status( 200 );
		$data['id'] = $user->get( 'ID' );

		if ( 0 < strlen( $form->getRedirect() ) ) {

			// $response->set_status( 307 );
			$data['redirect'] = $form->getSafeRedirect();

		} else {

			$data['reset'] = true;
		}

		$response->set_data( $data );

		return $response;
	}

	/**
	 * Reset user password.
	 *
	 * @since 10.4.48
	 *
	 * @param WP_REST_Request $request API request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function resetPassword( WP_REST_Request $request ) {

		$cookieName   = 'wp-resetpass-' . COOKIEHASH;
		$cookieDomain = is_string( COOKIE_DOMAIN ) ? COOKIE_DOMAIN : '';
		$response     = new WP_REST_Response();
		$forbidden    = new WP_Error( 'rest_forbidden', esc_html__( 'Bad Request.', 'connections' ), array( 'status' => 400 ) );

		// Check permissions. @todo This should move to the `permission_callback` callback.
		if ( is_user_logged_in() && ! current_user_can( 'edit_users' ) ) {

			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Permission denied.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Initialize the form for validation.
		$form = new Form\Reset_Password();

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
		if ( ! _token::isValid( $form->getFieldValue( '_cnonce' ), 'user/reset-password' ) ) {

			return $forbidden;
		}

		if ( isset( $_COOKIE[ $cookieName ] ) && 0 < strpos( $_COOKIE[ $cookieName ], ':' ) ) {

			list( $userlogin, $resetKey ) = explode( ':', wp_unslash( $_COOKIE[ $cookieName ] ), 2 );

			$user = check_password_reset_key( $resetKey, $userlogin );

			if ( $form->getFieldValue( 'pass1' ) && ! hash_equals( $resetKey, $form->getFieldValue( 'key' ) ) ) {

				$user = false;
			}

		} else {

			$user = false;
		}

		if ( ! $user || is_wp_error( $user ) ) {

			setcookie( $cookieName, ' ', time() - YEAR_IN_SECONDS, '/', $cookieDomain, is_ssl(), true );

			if ( $user && $user->get_error_code() === 'expired_key' ) {

				return new WP_Error(
					'rest_forbidden',
					esc_html__( 'Your password reset link has expired.', 'connections' ),
					array( 'status' => rest_authorization_required_code() )
				);

			} else {

				return new WP_Error(
					'rest_forbidden',
					esc_html__( 'Your password reset link appears to be invalid.', 'connections' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		// Check if password is one or all empty spaces.
		if ( ! empty( $form->getFieldValue( 'pass1' ) ) ) {

			$password = trim( $form->getFieldValue( 'pass1' ) );

			if ( empty( $password ) ) {

				return new WP_Error(
					'password_reset_empty_space',
					esc_html__( 'The password cannot be a space or all spaces.', 'connections' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		// Check if password fields do not match.
		if ( ! empty( $form->getFieldValue( 'pass1' ) ) && trim( $request->get_param( 'pass2' ) ) !== $form->getFieldValue( 'pass1' ) ) {

			return new WP_Error(
				'password_reset_mismatch',
				esc_html__( 'The passwords do not match.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		reset_password( $user, $form->getFieldValue( 'pass1' ) );
		setcookie( $cookieName, ' ', time() - YEAR_IN_SECONDS, '/', $cookieDomain, is_ssl(), true );

		// Setup response.
		$response->set_status( 200 );
		$data['id'] = $user->get( 'ID' );

		if ( 0 < strlen( $form->getRedirect() ) ) {

			// $response->set_status( 307 );
			$data['redirect'] = $form->getSafeRedirect();

		} else {

			$data['reset'] = true;
		}

		$response->set_data( $data );

		return $response;
	}
}
