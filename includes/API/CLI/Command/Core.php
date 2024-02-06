<?php
/**
 * Commands for managing the Connections Directory.
 *
 * @since      10.4.61
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

use cnRole;
use cnSettingsAPI;
use Exception;
use WP_CLI;
use WP_CLI\ExitException;
use WP_CLI\Utils;
use WP_CLI_Command;

/**
 * Perform database table operations.
 *
 * @package Connections_Directory\API\CLI\Command
 */
final class Core extends WP_CLI_Command {

	/**
	 * Register the `tables` wp-cli command.
	 *
	 * @since 10.4.61
	 *
	 * @throws Exception
	 */
	public static function register() {

		WP_CLI::add_command( 'connections_directory core', __CLASS__ );
	}

	/**
	 * Output the current version.
	 *
	 * ## EXAMPLES
	 *
	 *     # Display the version.
	 *     wp connections_directory core version
	 *
	 * @since 10.4.61
	 *
	 * @return void
	 */
	public function version() {

		WP_CLI::log( \Connections_Directory::VERSION );
	}

	/**
	 * Maybe include the `plugin.php` functions.
	 *
	 * @since 10.4.63
	 */
	protected function maybeIncludes() {

		// Include the necessary WordPress Plugin Administration API.
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	/**
	 * Uninstall the Connections Directory plugin, removing all tables and settings.
	 *
	 * ## OPTIONS
	 *
	 * [--network]
	 * : If set, the plugin will be deactivated for the entire multisite network.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # Uninstall the plugin.
	 *     wp connections_directory core uninstall
	 *
	 * @since 10.4.63
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 *
	 * @throws ExitException
	 */
	public function uninstall( $args, $assoc_args ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

		global $wpdb;

		WP_CLI::confirm( 'This action will delete all database tables and settings permanently. Backing up your data is highly recommended before proceeding. Are you sure you want to uninstall the Connections Directory?', $assoc_args );

		$network_wide = Utils\get_flag_value( $assoc_args, 'network' );

		$this->maybeIncludes();

		$plugin   = Connections_Directory()->pluginBasename();
		$settings = cnSettingsAPI::getAll();
		$status   = $this->getStatus( $plugin );
		$tables   = $this->getTableNames();
		$warnings = 0;

		// Network active plugins must be explicitly deactivated.
		if ( ! $network_wide && 'active-network' === $status ) {
			WP_CLI::error( 'Plugin is network active and must be deactivated with --network flag.' );
		}

		Connections_Directory()->options->removeOptions();
		WP_CLI::log( 'Deleted `connections_options` option.' );

		cnRole::purge();
		WP_CLI::log( 'Removed role capabilities.' );

		// Deactivate the plugin.
		WP_CLI::log( "Deactivating `$plugin`." );
		deactivate_plugins( $plugin, false, $network_wide );

		if ( ! is_network_admin() ) {
			update_option(
				'recently_activated',
				array( $plugin => time() ) + (array) get_option( 'recently_activated' )
			);
		} else {
			update_site_option(
				'recently_activated',
				array( $plugin => time() ) + (array) get_site_option( 'recently_activated' )
			);
		}

		foreach ( $settings as $options ) {

			foreach ( $options as $optionName => $value ) {

				if ( delete_option( $optionName ) ) {

					WP_CLI::log( "Deleted `{$optionName}` option." );

				} else {

					WP_CLI::warning( "Failed to delete `{$optionName}` option." );
					++$warnings;
				}
			}
		}

		foreach ( $tables as $table ) {

			$drop = '0' !== $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s', DB_NAME, $table ) );

			if ( $drop ) {

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'DROP TABLE IF EXISTS ' . self::escSQLIdent( $table ) );

				WP_CLI::log( "Table `{$table}` dropped." );

			} else {

				WP_CLI::warning( "Failed to drop the `{$table}` table." );
				++$warnings;
			}
		}

		if ( 0 === $warnings ) {

			WP_CLI::log( 'To delete the Connections Directory plugin, run command: wp plugin delete connections' );
			WP_CLI::success( 'The Connections Directory has been uninstalled.' );

		} else {

			WP_CLI::error( 'The Connections Directory was not fully uninstalled.' );
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	}

	/**
	 * Check the activation status of a plugin.
	 *
	 * @since 10.4.63
	 *
	 * @param string $file The plugin basename.
	 *
	 * @return string
	 */
	protected function getStatus( string $file ): string {

		if ( is_plugin_active_for_network( $file ) ) {
			return 'active-network';
		}

		if ( is_plugin_active( $file ) ) {
			return 'active';
		}

		return 'inactive';
	}

	/**
	 * Get the core table names.
	 *
	 * @since 10.4.63
	 *
	 * @return string[]
	 */
	private function getTableNames(): array {

		return array(
			CN_ENTRY_TABLE,
			CN_ENTRY_ADDRESS_TABLE,
			CN_ENTRY_PHONE_TABLE,
			CN_ENTRY_EMAIL_TABLE,
			CN_ENTRY_MESSENGER_TABLE,
			CN_ENTRY_SOCIAL_TABLE,
			CN_ENTRY_LINK_TABLE,
			CN_ENTRY_DATE_TABLE,
			CN_ENTRY_TABLE_META,
			CN_TERMS_TABLE,
			CN_TERM_TAXONOMY_TABLE,
			CN_TERM_RELATIONSHIP_TABLE,
			CN_TERM_META_TABLE,
		);
	}

	/**
	 * Escapes (backticks) MySQL identifiers (aka schema object names)
	 * - i.e. column names, table names, and database/index/alias/view etc names.
	 *
	 * @link  https://dev.mysql.com/doc/refman/5.5/en/identifiers.html
	 *
	 * @since 10.4.63
	 *
	 * @param string|array $idents A single identifier or an array of identifiers.
	 *
	 * @return string|array An escaped string if given a string, or an array of escaped strings if given an array of strings.
	 */
	private static function escSQLIdent( $idents ) {

		$applyEscape = static function ( $value ) {

			// Escape any backticks in the identifier by doubling.
			return '`' . str_replace( '`', '``', $value ) . '`';
		};

		if ( is_string( $idents ) ) {
			return $applyEscape( $idents );
		}

		return array_map( $applyEscape, $idents );
	}
}
