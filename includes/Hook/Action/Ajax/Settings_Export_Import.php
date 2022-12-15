<?php
/**
 * Export/Import settings.
 *
 * @since 10.4.33
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

use cnSettingsAPI;
use Connections_Directory\Request;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_validate;

/**
 * Class Settings_Export_Import
 *
 * @package Connections_Directory\Hook\Action\Ajax
 */
final class Settings_Export_Import {

	use Response;

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.33
	 */
	public static function register() {

		add_action( 'wp_ajax_export_settings', array( __CLASS__, 'export' ) );
		add_action( 'wp_ajax_import_settings', array( __CLASS__, 'import' ) );
	}

	/**
	 * Callback for the `wp_ajax_export_settings` action.
	 *
	 * AJAX callback to download the settings in a JSON encoded text file.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function export() {

		$action = new self();

		if ( $action->isValid( 'export_settings' ) ) {

			cnSettingsAPI::download();

		} else {

			$action->error(
				__( 'You do not have sufficient permissions to export the settings.', 'connections' ),
				null,
				403
			);
		}
	}

	/**
	 * Callback for the `wp_ajax_import_settings` action.
	 *
	 * AJAX callback to import settings from a JSON encoded text file.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function import() {

		$action = new self();

		if ( $action->isValid( 'import_settings' ) ) {

			$name = _array::get( $_FILES, 'import_file.name', '' );
			$path = _array::get( $_FILES, 'import_file.tmp_name', '' );

			if ( file_exists( $path ) && _validate::isFileJSON( $path, $name ) ) {

				$json   = file_get_contents( $path ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
				$result = cnSettingsAPI::import( $json );

				if ( true === $result ) {

					$action->success( __( 'Settings have been imported.', 'connections' ) );

				} else {

					$action->error( $result, null, 403 );
				}

			} else {

				$action->error( __( 'Invalid JSON file.', 'connections' ), array( 'file' => $path ), 415 );
			}

		} else {

			$action->error( __( 'You do not have sufficient permissions to import the settings.', 'connections' ), null, 403 );
		}
	}

	/**
	 * Whether the request nonce is valid.
	 *
	 * @since 10.4.33
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
