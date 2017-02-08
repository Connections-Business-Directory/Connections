<?php
/**
 * Base table.
 *
 * @author   Steven A. Zahm
 * @category Database
 * @package  Connections\DB\Table
 * @since    8.5.34
 */

namespace Connections\DB\Table;

use IronBound\DB\Table\BaseTable;
use Connections\DB\Query\Query;

/**
 * Class Table
 *
 * @package Connections\DB\Table
 */
abstract class Table extends BaseTable {

	/**
	 * @var array
	 */
	protected $primary_keys = array();

	/**
	 * Set the table prefix accordingly depending if Connections is installed on a multisite WP installation.
	 *
	 * @return string
	 */
	public function get_prefix() {

		/** @var /wpdb $wpdb */
		global $wpdb;

		return ( is_multisite() && CN_MULTISITE_ENABLED ) ? $wpdb->prefix : $wpdb->base_prefix;
	}

	///**
	// * Retrieve the name of the database table.
	// *
	// * @since 1.0
	// *
	// * @return string
	// */
	//public function get_name() {
	//
	//	$prefix = $this->get_prefix();
	//
	//	return "$prefix{$this->get_slug()}";
	//}

	/**
	 * Get creation SQL.
	 *
	 * @since 1.0
	 *
	 * @param \wpdb $wpdb
	 *
	 * @return string
	 */
	public function get_creation_sql( \wpdb $wpdb ) {

		$sql  = '';
		$name = $this->get_table_name( $wpdb );

		$sql .= "CREATE TABLE {$name} (\n";
		$sql .= $this->get_columns_definition();

		if ( $keys = $this->get_keys_definition() ) {

			$sql .= ",\n{$keys}";
		}

		$sql .= "\n) {$this->get_engine_definition()} {$wpdb->get_charset_collate()};";

		return $sql;
	}

	///**
	// * Get the keys definition.
	// *
	// * @since 2.0
	// *
	// * @return string
	// */
	//protected function get_keys_definition() {
	//
	//	if ( $this->get_primary_key() ) {
	//
	//		array_unshift( $keys, "PRIMARY KEY  ({$this->get_primary_key()})" );
	//	}
	//
	//	return implode( ",\n", $keys );
	//}

	/**
	 * Get all keys on the table.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	protected function get_keys() {

		$keys = array();

		if ( $this->get_primary_key() ) {

			array_unshift( $this->primary_keys, $this->get_primary_key() );

			$keys[] = 'PRIMARY KEY  (' . implode( ', ', $this->primary_keys ) . ')';
		}

		return $keys;
	}

	/**
	 * Return default table engine based db version.
	 *
	 * Connections uses FULLTEXT indices for search.
	 * FULLTEXT indices were not supported in INNODB until version 5.6.4.
	 * Since InnoDB is preferred but require FULLTEXT indices, fallback to MyISAM
	 * as appropriate  based on the db version.
	 *
	 * @return string The table engine.
	 */
	protected function get_engine_definition() {

		/** @var /wpdb $wpdb */
		global $wpdb;

		if ( version_compare( $wpdb->db_version(), '5.6.4', '>=' ) ) {

			$engine = 'InnoDB';

		} else {

			$engine = 'MyISAM';
		}

		return "ENGINE=$engine";
	}

	/**
	 * @return bool
	 */
	public function create() {

		/** @var /wpdb $wpdb */
		global $wpdb;

		if ( ! $this->exists() ) {

			if ( ! function_exists( 'dbDelta' ) ) {

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			}

			dbDelta( $this->get_creation_sql( $wpdb ) );
		}

		// Check to see if the table was created successfully.
		return $this->exists();
	}

	/**
	 * Check if the given table exists
	 *
	 * @since  2.4
	 *
	 * @return bool If the table name exists
	 */
	public function exists() {

		/** @var /wpdb $wpdb */
		global $wpdb;

		$table = sanitize_text_field( $this->get_table_name( $wpdb ) );

		return $table === $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) );
	}

	/**
	 * Check if the table was ever installed
	 *
	 * @since  2.4
	 *
	 * @return bool Returns if the customers table was installed and upgrade routine run
	 */
	public function installed() {

		return $this->exists();
	}

	/**
	 * @return bool
	 */
	public function truncate() {

		/** @var /wpdb $wpdb */
		global $wpdb;

		if ( $this->exists() ) {

			$table = sanitize_text_field( $this->get_table_name( $wpdb ) );

			$wpdb->query(  "TRUNCATE TABLE `{$table}`" );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function drop() {

		/** @var /wpdb $wpdb */
		global $wpdb;

		if ( $this->exists() ) {

			$table = sanitize_text_field( $this->get_table_name( $wpdb ) );

			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return Query
	 */
	public function query(){

		return new Query( $this );
	}
}
