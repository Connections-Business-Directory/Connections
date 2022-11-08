<?php
/**
 * The required actions so download, email, and generate/revoke remote URL for the system info.
 *
 * @since 10.4.32
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections_Directory\Hook\Action\Ajax
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Action\Ajax;

use cnCache;
use cnSystem_Info;
use Connections_Directory\Request;
use Connections_Directory\Utility\_string;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class System_Information
 *
 * @package Connections_Directory\Hook\Action\Ajax
 */
final class System_Information {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.32
	 */
	public static function register() {

		add_action( 'wp_ajax_download_system_info', array( __CLASS__, 'download' ) );
		add_action( 'wp_ajax_email_system_info', array( __CLASS__, 'email' ) );
		add_action( 'wp_ajax_generate_url', array( __CLASS__, 'generateRemoveViewURL' ) );
		add_action( 'wp_ajax_revoke_url', array( __CLASS__, 'revokeRemoteViewURL' ) );
	}

	/**
	 * Callback for the `wp_ajax_download_system_info` action.
	 *
	 * AJAX callback used to download the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function download() {

		$action = new self();

		if ( $action->isValid( 'download_system_info' ) ) {

			cnSystem_Info::download();

		} else {

			$action->respondError( __( 'You do not have sufficient permissions to perform this action.', 'connections' ), 403 );
		}
	}

	/**
	 * Callback for the `wp_ajax_email_system_info` action.
	 *
	 * AJAX callback to email the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function email() {

		$action = new self();

		/**
		 * Since email is sent via an ajax request, let's check for the appropriate header.
		 *
		 * @link https://davidwalsh.name/detect-ajax
		 */
		if ( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' !== strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$action->respondError( -1, 403 );
		}

		if ( $action->isValid( 'email_system_info' ) ) {

			$user    = wp_get_current_user();
			$request = Request\Email_System_Info::input();
			$email   = $request->value();

			if ( $request->hasFailedSchemaValidation() || $request->hasFailedSchemaSanitization() ) {

				$action->respondError( __( 'Required input values not provided.', 'connections' ), 403 );
			}

			$atts = array(
				'from_email' => $user->user_email,
				'from_name'  => $user->display_name,
				'to_email'   => $email['email'],
				'subject'    => $email['subject'],
				'message'    => $email['message'],
			);

			$response = cnSystem_Info::email( $atts );

			if ( $response ) {

				// Success, send success code.
				$action->respondSuccess( 1, 200 );

			} else {

				/** @var PHPMailer $phpmailer */
				global $phpmailer;

				// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
				$action->respondError( $phpmailer->ErrorInfo, 403 );
				// phpcs:enable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
			}

		} else {

			$action->respondError( __( 'You do not have sufficient permissions to perform this action.', 'connections' ), 403 );
		}
	}

	/**
	 * Callback for the `wp_ajax_generate_url` action.
	 *
	 * AJAX callback to create a secret URL for the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function generateRemoveViewURL() {

		$action = new self();

		if ( $action->isValid( 'generate_remote_system_info_url' ) ) {

			/* @todo need to check the $token is not WP_Error. */
			$token   = sanitize_key( _string::random( 32 ) );
			$expires = apply_filters( 'cn_system_info_remote_token_expire', DAY_IN_SECONDS * 3 );

			cnCache::set(
				'system_info_remote_token',
				$token,
				$expires,
				'option-cache'
			);

			$url = home_url() . '/?cn-system-info=' . $token;

			wp_send_json_success(
				array(
					'url'     => $url,
					'message' => __( 'Secret URL has been created.', 'connections' ),
				)
			);

		} else {

			$action->respondError( __( 'You do not have sufficient permissions to perform this action.', 'connections' ), 403 );
		}
	}

	/**
	 * Callback for the `wp_ajax_revoke_url` action.
	 *
	 * AJAX callback to revoke the secret URL for the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function revokeRemoteViewURL() {

		$action = new self();

		if ( $action->isValid( 'revoke_remote_system_info_url' ) ) {

			cnCache::clear( 'system_info_remote_token', 'option-cache' );

			$action->respondSuccess( __( 'Secret URL has been revoked.', 'connections' ), 200 );

		} else {

			$action->respondError( __( 'You do not have sufficient permissions to perform this action.', 'connections' ), 403 );
		}
	}

	/**
	 * Whether the request nonce is valid.
	 *
	 * @since 10.4.32
	 *
	 * @param string $action The nonce action name to validate.
	 *
	 * @return bool
	 */
	private function isValid( $action ) {

		return current_user_can( 'manage_options' ) &&
			   Request\Nonce::from( INPUT_POST, $action )->isValid();
	}

	/**
	 * AJAX error response.
	 *
	 * @since 10.4.32
	 *
	 * @param string   $message     The response message.
	 * @param int|null $status_code The HTTP status code to output. Default null.
	 */
	private function respondError( $message, $status_code = null ) {

		wp_send_json_error(
			array(
				'message' => $message,
			),
			$status_code
		);
	}

	/**
	 * AJAX success response.
	 *
	 * @since 10.4.32
	 *
	 * @param string   $message     The response message.
	 * @param int|null $status_code The HTTP status code to output. Default null.
	 */
	private function respondSuccess( $message, $status_code = null ) {

		wp_send_json_success(
			array(
				'message' => $message,
			),
			$status_code
		);
	}
}
