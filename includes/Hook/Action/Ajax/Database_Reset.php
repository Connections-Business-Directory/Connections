<?php
/**
 * Drop and create the core data tables.
 *
 * @since 10.4.61
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

use Connections_Directory\Request;
use Connections_Directory\Utility\_array;

/**
 * Class Database_Reset
 *
 * @package Connections_Directory\Hook\Action\Ajax
 */
final class Database_Reset {

	use Response;

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Reset the database tables.
	 *
	 * @since 10.4.61
	 */
	public static function register() {

		add_action( 'wp_ajax_database-reset', array( __CLASS__, 'reset' ) );
	}

	/**
	 * Callback for the `wp_ajax_database-reset` action.
	 *
	 * Validate the request to drop and create the core data tables.
	 *
	 * @internal
	 * @since 10.4.61
	 */
	public static function reset() {

		$action = new self();

		if ( $action->isValid( 'database-reset' ) ) {

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce validation occurs in the isValid() method.
			$confirmation = _array::get( $_POST, 'database-reset-confirmation', false );

			if ( 'reset' !== $confirmation ) {

				$action->error(
					__( 'Please enter "reset" into the confirmation field to reset the database.', 'connections' ),
					null,
					403
				);
			}

			$action->drop();
			$action->create();

			$action->success( __( 'The Connections Business Directory database tables have been reset.', 'connections' ) );

		} else {

			$action->error(
				__( 'You do not have sufficient permissions to reset the database.', 'connections' ),
				null,
				403
			);
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
	private function isValid( string $action ): bool {

		return current_user_can( 'manage_options' )
			   && Request\Nonce::from( INPUT_POST, $action )->isValid();
	}

	/**
	 * Create the core data tables.
	 *
	 * @since 10.4.61
	 */
	private function create() {

		if ( ! method_exists( 'cnSchema', 'create' ) ) {
			require_once CN_PATH . 'includes/class.schema.php';
		}

		// Delete the default category option, so when `cnSchema::create()` is run the default "Uncategorized" category will be created.
		delete_option( 'connections_category' );

		\cnSchema::create();
	}

	/**
	 * Drop the core data tables.
	 *
	 * @since 10.4.61
	 */
	private function drop() {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

		global $wpdb;

		$tables = $this->getTableNames();

		foreach ( $tables as $table ) {

			$drop = '0' !== $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s', DB_NAME, $table ) );

			if ( $drop ) {

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'DROP TABLE IF EXISTS ' . self::escSQLIdent( $table ) );
			}
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
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
