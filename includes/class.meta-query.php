<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnMeta_Query extends the @see WP_Meta_Query overriding the @see WP_Meta_Query::get_sql() method so the custom
 * tables used by Connections for entry and term meta can be used.
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnMeta_Query extends WP_Meta_Query {

	/**
	 * Generates SQL clauses to be added to the query.
	 *
	 * @since 8.2.5
	 *
	 * @param string $type              Type of meta, eg 'entry', 'term'.
	 * @param string $primary_table     Database table where the object being filtered is stored (eg CN_ENTRY_TABLE).
	 * @param string $primary_id_column ID column for the filtered object in $primary_table.
	 * @param mixed  $context           object|null Optional. The main query object.
	 *
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	public function get_sql( $type, $primary_table, $primary_id_column, $context = null ) {

		$this->meta_table     = cnMeta::tableName( $type );
		$this->meta_id_column = sanitize_key( $type . '_id' );

		$this->primary_table     = $primary_table;
		$this->primary_id_column = $primary_id_column;

		$sql = $this->get_sql_clauses();

		/*
		 * If any JOINs are LEFT JOINs (as in the case of NOT EXISTS), then all JOINs should
		 * be LEFT. Otherwise, posts with no metadata will be excluded from results.
		 */
		if ( false !== strpos( $sql['join'], 'LEFT JOIN' ) ) {
			$sql['join'] = str_replace( 'INNER JOIN', 'LEFT JOIN', $sql['join'] );
		}

		/**
		 * Filter the meta query's generated SQL.
		 *
		 * @since 8.2.5
		 *
		 * @param array $args {
		 *     An array of meta query SQL arguments.
		 *
		 *     @type array  $clauses           Array containing the query's JOIN and WHERE clauses.
		 *     @type array  $queries           Array of meta queries.
		 *     @type string $type              Type of meta.
		 *     @type string $primary_table     Primary table.
		 *     @type string $primary_id_column Primary column ID.
		 *     @type object $context           The main query object.
		 * }
		 */
		return apply_filters_ref_array( 'cn_get_meta_sql', array( $sql, $this->queries, $type, $primary_table, $primary_id_column, $context ) );
	}
}
