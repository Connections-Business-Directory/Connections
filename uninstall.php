<?php
/**
 * Uninstall Connections Business Directory plugin.
 *
 * This will remove the custom role capabilities, delete the options, and drop the database tables.
 *
 * @since      10.4.63
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
// Exit if accessed directly.
 ! defined( 'WP_UNINSTALL_PLUGIN' ) && exit;

if ( is_plugin_active( 'connections/connections.php' ) || ! current_user_can( 'delete_plugins' ) ) {
	return;
}

$cn_Uninstall      = get_option( 'connections_uninstall', array() );
$cn_MaybeUninstall = isset( $cn_Uninstall['maybe_uninstall'] ) && 1 === $cn_Uninstall['maybe_uninstall'];

if ( true !== $cn_MaybeUninstall ) {

	return;
}

$cn_UninstallRoleCapabilities = function ( $uninstall ) {

	$capabilities = isset( $uninstall['capabilities'] ) && is_array( $uninstall['capabilities'] ) ? $uninstall['capabilities'] : array();
	$roles        = wp_roles();

	foreach ( $roles->roles as $slug => $role ) {

		$wp_role = $roles->get_role( $slug );

		if ( ! $wp_role instanceof WP_Role ) {
			continue;
		}

		foreach ( $capabilities as $cap ) {

			if ( $wp_role->has_cap( $cap ) ) {

				$wp_role->remove_cap( $cap );

				error_log( "Removed `{$cap}` capability from the `{$slug}` role." );
			}
		}
	}
};

$cn_UninstallOptions = function ( $uninstall ) {

	$options = isset( $uninstall['options'] ) && is_array( $uninstall['options'] ) ? $uninstall['options'] : array();

	foreach ( $options as $optionName ) {

		delete_option( $optionName );

		error_log( "Deleted `{$optionName}` option." );
	}
};

$cn_EscapeSQLIdent = function ( $idents ) {

	$applyEscape = static function ( $value ) {

		// Escape any backticks in the identifier by doubling.
		return '`' . str_replace( '`', '``', $value ) . '`';
	};

	if ( is_string( $idents ) ) {
		return $applyEscape( $idents );
	}

	return array_map( $applyEscape, $idents );
};

$cn_UninstallTables = function ( $uninstall ) use ( $cn_EscapeSQLIdent ) {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

	global $wpdb;

	$tables = isset( $uninstall['tables'] ) && is_array( $uninstall['tables'] ) ? $uninstall['tables'] : array();

	foreach ( $tables as $table ) {

		$drop = '0' !== $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
				DB_NAME,
				$table
			)
		);

		if ( $drop ) {

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $cn_EscapeSQLIdent( $table ) );

			error_log( "Table `{$table}` dropped." );
		}
	}
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
};

$cn_UninstallRoleCapabilities( $cn_Uninstall );
$cn_UninstallOptions( $cn_Uninstall );
$cn_UninstallTables( $cn_Uninstall );
