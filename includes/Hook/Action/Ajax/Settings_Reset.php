<?php
/**
 * Reset the settings to their default values.
 *
 * @since 10.4.62
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Hook\Action\Ajax
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Hook\Action\Ajax;

use cnSettingsAPI;
use Connections_Directory\Request;
use Connections_Directory\Utility\_array;

/**
 * Class Settings_Export_Import
 *
 * @package Connections_Directory\Hook\Action\Ajax
 */
final class Settings_Reset {

	use Response;

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.62
	 */
	public static function register() {

		add_action( 'wp_ajax_settings-reset', array( __CLASS__, 'reset' ) );
	}

	/**
	 * Callback for the `wp_ajax_settings-reset` action.
	 *
	 * AJAX callback to download the settings in a JSON encoded text file.
	 *
	 * @internal
	 * @since 10.4.62
	 */
	public static function reset() {

		$action = new self();

		if ( $action->isValid( 'settings-reset' ) ) {

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce validation occurs in the isValid() method.
			$confirmation = _array::get( $_POST, 'settings-reset-confirmation', false );

			if ( 'reset' !== $confirmation ) {

				$action->error(
					__( 'Please enter "reset" into the confirmation field to reset the settings.', 'connections' ),
					null,
					403
				);
			}

			$settings = cnSettingsAPI::getAll();
			$slugs    = array_keys( $settings );

			foreach ( $slugs as $slug ) {
				cnSettingsAPI::reset( $slug );
			}

			$action->success( __( 'Settings have been reset to their default values.', 'connections' ) );

		} else {

			$action->error(
				__( 'You do not have sufficient permissions to reset the settings.', 'connections' ),
				null,
				403
			);
		}
	}

	/**
	 * Whether the request nonce is valid.
	 *
	 * @since 10.4.62
	 *
	 * @param string $action The nonce action name to validate.
	 *
	 * @return bool
	 */
	private function isValid( $action ) {

		return current_user_can( 'manage_options' ) &&
			   Request\Nonce::from( INPUT_POST, $action )->isValid();
	}
}
