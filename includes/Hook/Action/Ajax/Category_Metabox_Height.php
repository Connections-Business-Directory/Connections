<?php
/**
 * Save the user preference for the category metabox height.
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

use Connections_Directory\Request;
use Connections_Directory\Utility\_nonce;

/**
 * Class Category_Metabox_Height
 *
 * @package Connections_Directory\Hook\Action\Ajax
 */
final class Category_Metabox_Height {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.32
	 */
	public static function register() {

		add_action( 'wp_ajax_set_category_div_height', array( __CLASS__, 'save' ) );
	}

	/**
	 * Callback for the `wp_ajax_set_category_div_height` action.
	 *
	 * Save the user's defined height of the category metabox.
	 *
	 * @internal
	 * @since 8.6.5
	 */
	public static function save() {

		$action = new self();

		if ( $action->isValid() ) {

			$height = Request\Integer::from( INPUT_POST, 'height' )->value();
			$height = 200 > $height ? 200 : $height;

			if ( Connections_Directory()->currentUser->setCategoryDivHeight( $height ) ) {

				$action->respondSuccess( "Set height to {$height}", 200 );

			} else {

				$action->respondError( __( 'Failed to set user category height.', 'connections' ), 500 );
			}

		} else {

			$action->respondError( __( 'Invalid nonce.', 'connections' ), 403 );
		}
	}

	/**
	 * Whether the request nonce is valid.
	 *
	 * @since 10.4.32
	 *
	 * @return bool
	 */
	private function isValid() {

		return Request\Nonce::from( INPUT_POST, 'set_category_div_height' )->isValid();
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
				'_cnonce' => _nonce::create( 'set_category_div_height' ),
			),
			$status_code
		);
	}
}
