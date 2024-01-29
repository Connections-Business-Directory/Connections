<?php
/**
 * Commands for managing the Connections Directory database tables.
 *
 * @since 10.4.61
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

use Connections_Directory\Utility\_string;
use Exception;
use WP_CLI;
use WP_CLI_Command;
use WP_CLI\Utils;

/**
 * Perform database table operations.
 *
 * @package Connections_Directory\API\CLI\Command
 */
final class Tables extends WP_CLI_Command {

	/**
	 * Register the `tables` wp-cli command.
	 *
	 * @since 10.4.61
	 *
	 * @throws Exception
	 */
	public static function register() {

		if ( ! method_exists( 'cnSchema', 'create' ) ) {
			require_once CN_PATH . 'includes/class.schema.php';
		}

		// Create a string from the current WP_CLI command.
		$currentCommand = implode( ' ', WP_CLI::get_runner()->arguments );

		// When running the commands to create the database tables, remove the filter that registers the settings.
		// This is to prevent table does not exist errors when setting default taxonomy terms (ie. "Uncategorized").
		if ( in_array( $currentCommand, array( 'connections_directory tables create', 'eval cnSchema::create();' ) ) ) {
			remove_filter( 'cn_register_settings_fields', array( 'cnRegisterSettings', 'registerSettingsFields' ) );
			// remove_action( 'init', array( 'cnSettingsAPI', 'registerFields' ), 20 );
			// remove_filter( 'default_option_cn_default_category', array( 'cnOptions', 'getDefaultCategoryID' ) );
		}

		WP_CLI::add_command( 'connections_directory tables', __CLASS__ );
	}

	/**
	 * Make a clone of the Connections Directory database tables.
	 *
	 * ## OPTIONS
	 *
	 * [--clean]
	 * : Drop the clone tables.
	 *
	 * [--list]
	 * : List the clone table names.
	 *
	 * [--format=<format>]
	 * : Render the list in a particular format.
	 * ---
	 * default: list
	 * options:
	 *  - list
	 *  - csv
	 *
	 * [--prefix=<value>]
	 * : The clone table prefix.
	 * ---
	 * default: clone
	 * ---
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # List clone table names with the default `clone` table prefix.
	 *     wp connections_directory tables clone --list
	 *
	 *     # List clone table names with the `custom` table prefix.
	 *     wp connections_directory tables clone --list --prefix=custom
	 *
	 *     # List all clone tables names.
	 *     wp connections_directory tables clone --prefix=* --list
	 *
	 *     # List clone table names with the default `clone` table prefix in CSV format.
	 *     wp connections_directory tables clone --list --format=csv
	 *
	 *     # Create a set of clone tables for the Connections Directory tables with the default `clone` table prefix.
	 *     wp connections_directory tables clone
	 *
	 *     # Create a set of clone tables for the Connections Directory tables with the `custom` table prefix.
	 *     wp connections_directory tables clone --prefix=custom
	 *
	 *     # Drop a set of clone tables for the Connections Directory tables with the default `clone` table prefix.
	 *     wp connections_directory tables clone --clean
	 *
	 *     # Drop a set of clone tables for the Connections Directory tables with the `custom` table prefix.
	 *     wp connections_directory tables clone --prefix=custom --clean
	 *
	 * @since 10.4.61
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function clone( $args, $assoc_args ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		global $wpdb;

		$clean  = Utils\get_flag_value( $assoc_args, 'clean', false );
		$list   = Utils\get_flag_value( $assoc_args, 'list', false );
		$prefix = Utils\get_flag_value( $assoc_args, 'prefix', 'clone' );

		if ( true === $list ) {

			$args = array( "{$prefix}_connections*" );

			$assoc_args['all-tables'] = true;
			$assoc_args['exclude']    = self::getTableNames(); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude

			$this->listTables( $args, $assoc_args );

			exit;
		}

		if ( false === $clean ) {

			WP_CLI::confirm( 'Are you sure you want to clone the Connections Directory database?', $assoc_args );

		} else {

			WP_CLI::confirm( 'Are you sure you want to drop the clone tables?', $assoc_args );
		}

		if ( preg_match( '|[^a-z0-9_]|i', $prefix ) ) {
			WP_CLI::error( 'Invalid table prefix.' );
		}

		$tables = $this->getTableBaseNames();

		foreach ( $tables as $table ) {

			$source = "{$wpdb->prefix}{$table}";
			$clone  = "{$prefix}_{$table}";

			$drop = '0' !== $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s', DB_NAME, $clone ) );

			if ( $drop ) {

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'DROP TABLE IF EXISTS ' . self::escSQLIdent( $clone ) );

				WP_CLI::log( "Table {$clone} dropped." );
			}

			if ( false === $clean ) {

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$create = $wpdb->query( 'CREATE TABLE IF NOT EXISTS ' . self::escSQLIdent( $clone ) . ' LIKE ' . self::escSQLIdent( $source ) );

				if ( $create ) {
					WP_CLI::log( "Created `{$clone}` of `{$source}`." );
				}

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$insert = $wpdb->query( 'INSERT INTO ' . self::escSQLIdent( $clone ) . ' SELECT * FROM ' . self::escSQLIdent( $source ) );

				if ( false === $insert ) {

					WP_CLI::log( "Failed to insert into `{$clone}` from `{$source}`." );

				} else {

					WP_CLI::log( "Inserted {$insert} rows from `{$source}` into `{$clone}`." );
				}
			}
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	}

	/**
	 * Create the Connections Directory tables.
	 *
	 * ## EXAMPLES
	 *
	 *     # Create the Connections Directory tables, loading only the Connection plugin to ensure no potential conflicts.
	 *     wp connections_directory tables create --skip-themes --skip-plugins=$(wp plugin list --skip-plugins --skip-themes --field=name | grep -v ^connections$ | tr  '\n' ',')
	 *
	 * @since 10.4.61
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 */
	public function create( $args, $assoc_args ) {

		// Get a list of all plugins except Connections.
		$plugins = WP_CLI::runcommand(
			'plugin list',
			array(
				'return'       => true,
				'command_args' => array(
					'--skip-plugins',
					'--skip-themes',
					'--quiet',
					"--field=name | grep -v ^connections$ | tr  '\n' ','",
				),
			)
		);

		// Delete the default category option, so when `cnSchema::create()` is run the default "Uncategorized" category will be created.
		delete_option( 'connections_category' );

		// Run `cnSchema::create()` to create the database tables and default category.
		// Run the command excluding all plugin and themes to help ensure no conflicts.
		WP_CLI::runcommand(
			"eval 'cnSchema::create();'",
			array(
				'command_args' => array(
					"--skip-plugins={$plugins}",
					'--skip-themes',
				),
			)
		);

		WP_CLI::success( 'Database tables created.' );
	}

	/**
	 * Drop the Connections Directory database tables.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # Drop the Connections Directory tables.
	 *     wp connections_directory tables drop
	 *
	 * @since 10.4.61
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 */
	public function drop( $args, $assoc_args ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

		global $wpdb;

		WP_CLI::confirm( 'Are you sure you want to drop the Connections Database tables?', $assoc_args );

		$tables = $this->getTableNames();

		foreach ( $tables as $table ) {

			$drop = '0' !== $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s', DB_NAME, $table ) );

			if ( $drop ) {

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'DROP TABLE IF EXISTS ' . self::escSQLIdent( $table ) );

				WP_CLI::log( "Table {$table} dropped." );
			}
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	}

	/**
	 * Rename the Connections Directory tables with a prefix to a new prefix.
	 *
	 * ## OPTIONS
	 *
	 * [--from]
	 * : The table prefix to rename.
	 * ---
	 * default: `$wpdb->prefix`
	 * ---
	 *
	 * [--to]
	 * : The new table prefix.
	 * ---
	 * default: backup
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Rename the Connections Directory database tables that have the prefix `clone` to `backup`.
	 *     wp connections_directory tables rename --from=clone
	 *
	 *     # Rename the Connections Directory database tables that have the prefix `backup` to `clone`.
	 *     wp connections_directory tables rename --from=backup --to=clone
	 *
	 * @since 10.4.61
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function rename( $args, $assoc_args ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		global $wpdb;

		$from = Utils\get_flag_value( $assoc_args, 'from', rtrim( $wpdb->prefix, '_' ) );
		$to   = Utils\get_flag_value( $assoc_args, 'to', 'backup' );

		if ( preg_match( '|[^a-z0-9_]|i', $to ) ) {
			WP_CLI::error( 'Invalid table prefix.' );
		}

		$this->listTables(
			array( "{$from}_connections*" ),
			array(
				'all-tables' => true,
			)
		);

		WP_CLI::confirm(
			sprintf(
				'Are you sure you want to rename the tables in the list with the prefix of `%1$s` to a prefix of `%2$s`?',
				$from,
				$to
			),
			$assoc_args
		);

		$tables = Utils\wp_get_table_names(
			array( "{$from}_connections*" ),
			array(
				'all-tables' => true,
			)
		);

		$pieces = array();

		foreach ( $tables as $table ) {
			$pieces[] = self::escSQLIdent( $table ) . ' TO ' . self::escSQLIdent( _string::applyPrefix( $to, _string::removePrefix( $from, $table ) ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'RENAME TABLE ' . implode( ', ', $pieces ) );

		$this->listTables(
			array( "{$to}_connections*" ),
			array(
				'all-tables' => true,
			)
		);

		WP_CLI::success( 'Renaming tables is completed.' );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * List the Connections Directory database tables.
	 *
	 * This command is similar to the `wp db tables` command, but limits the table list to Connections Business Directory tables.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: list
	 * options:
	 *  - list
	 *  - csv
	 *
	 * ## EXAMPLES
	 *
	 *     # List the Connections Directory tables.
	 *     wp connections_directory tables list
	 *
	 * @since 10.4.61
	 * @link https://github.com/wp-cli/db-command
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 */
	public function list( $args, $assoc_args ) {
		global $wpdb;

		$args = array( "{$wpdb->prefix}connections*" );

		$assoc_args['all-tables-with-prefix'] = true;

		$this->listTables( $args, $assoc_args );
	}

	/**
	 * Output a list of table names.
	 *
	 * @since 10.4.61
	 *
	 * @param array $args       The positional arguments.
	 * @param array $assoc_args The --key=value, --flag or, --no-flag arguments.
	 */
	private function listTables( array $args, array $assoc_args ) {

		$exclude = Utils\get_flag_value( $assoc_args, 'exclude', array() );
		$format  = Utils\get_flag_value( $assoc_args, 'format' );
		$tables  = Utils\wp_get_table_names( $args, $assoc_args );

		if ( 0 < count( $exclude ) ) {

			$tables = array_values( array_diff( $tables, $exclude ) );
		}

		if ( 'csv' === $format ) {
			WP_CLI::log( implode( ',', $tables ) );
		} else {
			foreach ( $tables as $table ) {
				WP_CLI::log( $table );
			}
		}
	}

	/**
	 * Get the core table names.
	 *
	 * @since 10.4.61
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
	 * Get the core table names without the WP DB prefix.
	 *
	 * @since 10.4.61
	 *
	 * @return string[]
	 */
	private function getTableBaseNames(): array {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$tables = $this->getTableNames();

		return array_map(
			static function ( $haystack ) use ( $prefix ) {
				return _string::removePrefix( $prefix, $haystack );
			},
			$tables
		);
	}

	/**
	 * Escapes (backticks) MySQL identifiers (aka schema object names)
	 * - i.e. column names, table names, and database/index/alias/view etc names.
	 *
	 * @link https://dev.mysql.com/doc/refman/5.5/en/identifiers.html
	 *
	 * @since 10.4.61
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
