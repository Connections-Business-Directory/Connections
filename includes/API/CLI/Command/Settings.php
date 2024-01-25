<?php
/**
 * Commands for managing the Connections Directory setting options.
 *
 * @since 10.4.62
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\API\CLI\Command
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\API\CLI\Command;

use cnSettingsAPI;
use WP_CLI;
use WP_CLI\ExitException;
use WP_CLI\Utils;
use WP_CLI_Command;

/**
 * Manage settings.
 *
 * @package Connections_Directory\API\CLI\Command
 */
final class Settings extends WP_CLI_Command {

	/**
	 * Register the `settings` wp-cli command.
	 *
	 * @since 10.4.62
	 *
	 * @throws \Exception
	 */
	public static function register() {

		WP_CLI::add_command( 'connections_directory settings', __CLASS__ );
	}

	/**
	 * Validate the file path.
	 *
	 * @since 10.4.62
	 *
	 * @param string $path The path to validate.
	 *
	 * @return bool
	 * @throws ExitException
	 */
	private function validatePath( string $path ): bool {

		if ( ! is_dir( $path ) ) {
			WP_CLI::error( sprintf( "The directory '%s' does not exist.", $path ), false );
			return false;
		} elseif ( ! is_writable( $path ) ) { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_is_writable
			WP_CLI::error( sprintf( "The directory '%s' is not writable.", $path ), false );
			return false;
		}

		return true;
	}

	/**
	 * Validate the file name.
	 *
	 * A valid filename should not contain "/", "\", "?", "%", "*", ":", "|", """, "<", ">".
	 *
	 * @since 10.4.62
	 *
	 * @param string $filename the file name to validate.
	 *
	 * @return bool
	 */
	private function validateFilename( string $filename ): bool {

		if ( preg_match( '/[\/\\\?%*:\"<>|]/', $filename ) ) {
			WP_CLI::error( sprintf( "The file name '%s' is invalid.", $filename ), false );
			return false;
		}

		return true;
	}

	/**
	 * Export the settings to a JSON file.
	 *
	 * ## OPTIONS
	 *
	 * [--dir]
	 * : Full path to the directory where JSON export file should be stored. Defaults to the current working directory.
	 *
	 * [--filename]
	 * : The JSON filename. Defaults to `cn-settings-export-{datetime}.json`.
	 *
	 * ## EXAMPLES
	 *
	 *     # Export the settings to the `directory_settings.json` file.
	 *     wp connections_directory settings export --filename=directory_settings
	 *
	 * @since 10.4.62
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 *
	 * @throws ExitException
	 */
	public function export( $args, $assoc_args ) {

		$name = Utils\get_flag_value( $assoc_args, 'filename', 'cn-settings-export-' . current_time( 'Y-m-d_H-i-s' ) . '.json' );
		$path = Utils\get_flag_value( $assoc_args, 'dir', getcwd() );

		if ( false === $this->validatePath( $path ) || false === $this->validateFilename( $name ) ) {

			WP_CLI::halt( 1 );
		}

		$path = trailingslashit( $path );

		$settings = cnSettingsAPI::getAll();

		$JSON = wp_json_encode( $settings );

		if ( false === $JSON ) {

			WP_CLI::error( 'Failed to JSON encode the settings.' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$file = fopen( "{$path}{$name}", 'w' );

		if ( ! $file ) {
			WP_CLI::error( "Error opening {$name} for writing." );
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_fwrite
		$res = fwrite( $file, $JSON );

		if ( false === $res ) {
			WP_CLI::error( 'Error writing to export file.' );
		}

		WP_CLI::success( "Settings have been exported to {$name} in {$path}." );
	}

	/**
	 * Import a Connections Directory settings JSON file.
	 *
	 * ## OPTIONS
	 *
	 *     <file>
	 *     : Path to a valid JSON file for importing. Directories are also accepted.
	 *
	 * ## EXAMPLES
	 *
	 *     # Import a valid JSON settings file.
	 *     wp connections_directory settings import test/settings.json
	 *
	 * @since 10.4.62
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 *
	 * @throws ExitException
	 */
	public function import( $args, $assoc_args ) {

		$file = $args[0];

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "File '$file' doesn't exist." );
		}

		if ( ! is_readable( $file ) ) {
			WP_CLI::error( "Cannot read file '$file'." );
		}

		$json = file_get_contents( $file );

		if ( ! is_string( $json ) || ! json_validate( $json ) ) {

			WP_CLI::error( "Error reading '$file' or file is invalid JSON." );
		}

		$result = cnSettingsAPI::import( $json );

		if ( true !== $result ) {

			WP_CLI::error( "Failed to import file '$file'." );
		}

		WP_CLI::success( "Finished importing from '$file' file." );
	}

	/**
	 * Reset the Connections Directory setting options to their default values.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # Reset the settings options to their default values.
	 *     wp connections_directory settings reset
	 *
	 * @since 10.4.62
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 */
	public function reset( $args, $assoc_args ) {

		WP_CLI::confirm( 'Are you sure you want to reset the Connections Directory settings?', $assoc_args );

		$settings = cnSettingsAPI::getAll();
		$slugs    = array_keys( $settings );

		foreach ( $slugs as $slug ) {
			cnSettingsAPI::reset( $slug );
		}

		WP_CLI::success( 'Settings have been reset to their default values.' );
	}
}
