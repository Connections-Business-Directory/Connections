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
					'callback'            => array( $this, 'userLogin' ),
					'args'                => array(
						'_cnonce'     => array(
							'required'    => true,
							'description' => __( 'The request token.', 'connections' ),
							'type'        => 'string',
						),
						'log'         => array(
							'required'    => true,
							'description' => __( 'Username or email.', 'connections' ),
							'type'        => 'string',
						),
						'pwd'         => array(
							'required'    => true,
							'description' => __( 'Password.', 'connections' ),
							'type'        => 'string',
						),
						'redirect_to' => array(
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
					'permission_callback' => static function () {

						if ( is_user_logged_in() ) {

							return new WP_Error(
								'rest_forbidden',
								esc_html__( 'Permission denied.', 'connections' ),
								array( 'status' => rest_authorization_required_code() )
							);
						}

						return true;
					},
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
						'_cnonce'     => array(
							'required'    => true,
							'description' => __( 'The request token.', 'connections' ),
							'type'        => 'string',
						),
						'log'         => array(
							'required'    => true,
							'description' => __( 'Username or email.', 'connections' ),
							'type'        => 'string',
						),
						'redirect_to' => array(
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
						'_cnonce'     => array(
							'required'    => true,
							'description' => __( 'The request token.', 'connections' ),
							'type'        => 'string',
						),
						'pass1'       => array(
							'required'    => true,
							'description' => __( 'New password.', 'connections' ),
							'type'        => 'string',
						),
						'pass2'       => array(
							'required'    => true,
							'description' => __( 'Confirm password.', 'connections' ),
							'type'        => 'string',
						),
						'pw_weak'     => array(
							'required'          => false,
							'description'       => __( 'Confirm use of weak password.', 'connections' ),
							'type'              => 'boolean',
							// Specify the sanitization callback for the checkbox value.
							'sanitize_callback' => static function ( $value, $request, $param ) {
								// Return a boolean value, cast from the input value.
								return boolval( $value );
							},
							// Specify the validation callback for the checkbox value.
							'validate_callback' => static function ( $value, $request, $param ) {
								// Return true if the value is a boolean, false otherwise.
								return is_bool( $value );
							},
						),
						'key'         => array(
							'required'    => true,
							'description' => __( 'Password reset key.', 'connections' ),
							'type'        => 'string',
						),
						'redirect_to' => array(
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
			'/' . $this->base . '/register',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'userRegister' ),
					'args'                => array(
						'_cnonce'     => array(
							'required'    => true,
							'description' => __( 'The request token.', 'connections' ),
							'type'        => 'string',
							'pattern'     => '^[a-fA-F0-9]{10}$',
						),
						'user_login'  => array(
							'required'    => true,
							'description' => __( 'Username.', 'connections' ),
							'type'        => 'string',
							/*
							 * Max `user_login` is 60 characters.
							 * @link https://codex.wordpress.org/Database_Description#Table:_wp_users
							 */
							'maxLength'   => 60,
						),
						'user_email'  => array(
							'required'    => true,
							'description' => __( 'Email.', 'connections' ),
							'type'        => 'string',
							/*
							 * Max `user_email` is 100 characters.
							 * @link https://codex.wordpress.org/Database_Description#Table:_wp_users
							 */
							'maxLength'   => 100,
						),
						'redirect_to' => array(
							'required'          => false,
							'description'       => __( 'The URL to redirect to after form submission.', 'connections' ),
							'validate_callback' => 'wp_http_validate_url',
							'sanitize_callback' => 'sanitize_url',
							'type'              => 'string',
							'format'            => 'uri',
						),
					),
					'permission_callback' => static function () {

						if ( is_user_logged_in() && ! current_user_can( 'create_users' ) ) {

							return new WP_Error(
								'rest_forbidden',
								esc_html__( 'Permission denied.', 'connections' ),
								array( 'status' => rest_authorization_required_code() )
							);
						}

						return true;
					},
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
	public function userLogin( WP_REST_Request $request ) {

		$data          = array();
		$response      = new WP_REST_Response();
		$invalid       = new WP_Error( 'rest_invalid_user', esc_html__( 'Username or password is incorrect.', 'connections' ), array( 'status' => 401 ) );
		$forbidden     = new WP_Error( 'rest_forbidden', esc_html__( 'Bad Request.', 'connections' ), array( 'status' => 400 ) );
		$secure_cookie = '';

		// Initialize the form for validation.
		$form = new Form\User_Login();

		// Drop any request parameters that have no registered fields in the form.
		$parameters = array_intersect_key( $request->get_params(), $form->getFieldValues() );

		// Feed the request parameters into the form field values.
		$form->setFieldValues( $parameters );
		$form->setRedirect( _array::get( $request->get_params(), 'redirect_to', '' ) );

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

			$user = get_user_by( 'email', sanitize_email( $form->getFieldValue( 'log' ) ) );

		} elseif ( validate_username( $form->getFieldValue( 'log' ) ) ) {

			$user = get_user_by( 'login', sanitize_user( $form->getFieldValue( 'log' ) ) );

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

		// If the user wants SSL but the session is not SSL, force a secure cookie.
		if ( ! force_ssl_admin() ) {

			if ( get_user_option( 'use_ssl', $user->ID ) ) {
				$secure_cookie = true;
				force_ssl_admin( true );
			}
		}

		if ( 0 < strlen( $form->getRedirect() ) ) {

			$redirect_to = $form->getRedirect();

			// Redirect to HTTPS if user wants SSL.
			if ( $secure_cookie && str_contains( $redirect_to, 'wp-admin' ) ) {
				$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
			}

		} else {

			$redirect_to = admin_url();
		}

		// Ensure the core WP default filters exists for WP User authentication.
		$this->ensureDefaultFilters();

		$user = wp_signon(
			array(
				'user_login'    => $user->get( 'user_login' ),
				'user_password' => $form->getFieldValue( 'pwd' ),
				'remember'      => '1' === $form->getFieldValue( 'rememberme' ),
			)
		);

		/**
		 * Filters the login redirect URL.
		 *
		 * @since 10.4.50
		 *
		 * @param string           $redirect_to           The redirect destination URL.
		 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
		 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
		 */
		$redirect_to = apply_filters(
			'login_redirect', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$redirect_to,
			$form->getRedirect(),
			$user
		);

		if ( ! $user instanceof WP_User ) {

			return $user;
		}

		if ( ( empty( $redirect_to ) || 'wp-admin/' == $redirect_to || admin_url() == $redirect_to ) ) {

			// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
			if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) ) {

				$redirect_to = user_admin_url();

			} elseif ( is_multisite() && ! $user->has_cap( 'read' ) ) {

				$redirect_to = get_dashboard_url( $user->ID );

			} elseif ( ! $user->has_cap( 'edit_posts' ) ) {

				$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();
			}
		}

		$form->setRedirect( $redirect_to );

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
	 * It seems that something is removing core WP filters required for
	 * user authentication via a REST API request. Check for each of the
	 * default filters and register them if they are missing.
	 *
	 * Ref: Ticket ID:591997
	 *
	 * @since 10.4.57
	 */
	private function ensureDefaultFilters() {

		if ( false === has_filter( 'authenticate', 'wp_authenticate_username_password' ) ) {

			add_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
		}

		if ( false === has_filter( 'authenticate', 'wp_authenticate_email_password' ) ) {

			add_filter( 'authenticate', 'wp_authenticate_email_password', 20, 3 );
		}

		if ( false === has_filter( 'authenticate', 'wp_authenticate_application_password' ) ) {

			add_filter( 'authenticate', 'wp_authenticate_application_password', 20, 3 );
		}

		if ( false === has_filter( 'authenticate', 'wp_authenticate_spam_check' ) ) {

			add_filter( 'authenticate', 'wp_authenticate_spam_check', 99 );
		}

		if ( false === has_filter( 'authenticate', 'wp_authenticate_cookie' ) ) {

			add_filter( 'authenticate', 'wp_authenticate_cookie', 30, 3 );
		}
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
		$form = new Form\Request_Reset_Password();

		// Drop any request parameters that have no registered fields in the form.
		$parameters = array_intersect_key( $request->get_params(), $form->getFieldValues() );

		// Feed the request parameters into the form field values.
		$form->setFieldValues( $parameters );
		$form->setRedirect( _array::get( $request->get_params(), 'redirect_to', '' ) );

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

			$user = get_user_by( 'email', sanitize_email( $form->getFieldValue( 'log' ) ) );

		} elseif ( validate_username( $form->getFieldValue( 'log' ) ) ) {

			$user = get_user_by( 'login', sanitize_user( $form->getFieldValue( 'log' ) ) );

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

			$data['confirmation'] = sprintf(
				/* translators: %s: Link to the login page. */
				__( 'Check your email for the confirmation link, then visit the <a href="%s">login page</a>.', 'connections' ),
				wp_login_url()
			);

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
		$form->setRedirect( _array::get( $request->get_params(), 'redirect_to', '' ) );

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

	/**
	 * Register new user.
	 *
	 * @since 10.4.49
	 *
	 * @param WP_REST_Request $request API request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function userRegister( WP_REST_Request $request ) {

		$forbidden = new WP_Error( 'rest_forbidden', esc_html__( 'Bad Request.', 'connections' ), array( 'status' => 400 ) );
		$response  = new WP_REST_Response();

		if ( ! get_option( 'users_can_register' ) ) {

			return new WP_Error(
				'registerdisabled',
				esc_html__( 'User registration is currently not allowed.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Initialize the form for validation.
		$form = new Form\User_Register();

		// Drop any request parameters that have no registered fields in the form.
		$parameters = array_intersect_key( $request->get_params(), $form->getFieldValues() );

		// Feed the request parameters into the form field values.
		$form->setFieldValues( $parameters );
		$form->setRedirect( _array::get( $request->get_params(), 'redirect_to', '' ) );

		// Validate the form fields against their registered schema.
		$isValid = $form->validate();

		// If the form fields do not pass their schema validation, return a bad request.
		if ( false === $isValid ) {

			return $forbidden;
		}

		// Ensure the supplied nonce token field is valid.
		if ( ! _token::isValid( $form->getFieldValue( '_cnonce' ), 'user/register' ) ) {

			return $forbidden;
		}

		$result = register_new_user( $form->getFieldValue( 'user_login' ), $form->getFieldValue( 'user_email' ) );

		if ( $result instanceof WP_Error ) {

			return $result;
		}

		// Setup response.
		$response->set_status( 200 );

		$data['id'] = $result;

		if ( 0 < strlen( $form->getRedirect() ) ) {

			// $response->set_status( 307 );
			$data['redirect'] = $form->getSafeRedirect();

		} else {

			$data['confirmation'] = sprintf(
				/* translators: %s: Link to the login page. */
				__( 'Registration complete. Please check your email, then visit the <a href="%s">login page</a>.', 'connections' ),
				wp_login_url()
			);

			$data['reset'] = true;
		}

		$response->set_data( $data );

		return $response;
	}
}
