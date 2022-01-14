<?php

namespace Connections_Directory\Query;

use cnTerm;
use WP_Error;
use WP_Tax_Query;
use wpdb;
use function Connections_Directory\Taxonomy\exists as taxonomy_exists;
use function Connections_Directory\Taxonomy\isHierarchical as is_taxonomy_hierarchical;

/**
 * Class Taxonomy
 *
 * NOTE: This is the Connections equivalent of @see \WP_Tax_Query in WordPress core ../wp-includes/class-wp-tax-query.php
 *
 * @package Connections_Directory\Query
 */
final class Taxonomy extends WP_Tax_Query {

	/**
	 * Standard response when the query should not return any rows.
	 *
	 * @since 10.3
	 * @var array
	 */
	private static $no_results = array(
		'join'  => array( '' ),
		'where' => array( '0 = 1' ),
	);

	/**
	 * Generate SQL JOIN and WHERE clauses for a "first-order" query clause.
	 *
	 * @since 10.3
	 *
	 * @global wpdb $wpdb The WordPress database abstraction object.
	 *
	 * @param array $clause       Query clause (passed by reference).
	 * @param array $parent_query Parent query array.
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to a first-order query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	public function get_sql_for_clause( &$clause, $parent_query ) {
		global $wpdb;

		$sql = array(
			'where' => array(),
			'join'  => array(),
		);

		$join  = '';
		$where = '';

		$this->clean_query( $clause );

		if ( is_wp_error( $clause ) ) {
			return self::$no_results;
		}

		$terms    = $clause['terms'];
		$operator = strtoupper( $clause['operator'] );

		if ( 'IN' === $operator ) {

			if ( empty( $terms ) ) {
				return self::$no_results;
			}

			$terms = implode( ',', $terms );

			/*
			 * Before creating another table join, see if this clause has a
			 * sibling with an existing join that can be shared.
			 */
			$alias = $this->find_compatible_table_alias( $clause, $parent_query );
			if ( false === $alias ) {
				$i     = count( $this->table_aliases );
				$alias = $i ? 'tt' . $i : CN_TERM_RELATIONSHIP_TABLE;

				// Store the alias as part of a flat array to build future iterators.
				$this->table_aliases[] = $alias;

				// Store the alias with this clause, so later siblings can use it.
				$clause['alias'] = $alias;

				$join .= ' LEFT JOIN ' . CN_TERM_RELATIONSHIP_TABLE;
				$join .= $i ? " AS $alias" : '';
				$join .= " ON ($this->primary_table.$this->primary_id_column = $alias.entry_id)";
			}

			$where = "$alias.term_taxonomy_id $operator ($terms)";

		} elseif ( 'NOT IN' === $operator ) {

			if ( empty( $terms ) ) {
				return $sql;
			}

			$terms = implode( ',', $terms );

			$where = "$this->primary_table.$this->primary_id_column NOT IN (
				SELECT entry_id
				FROM " . CN_TERM_RELATIONSHIP_TABLE . "
				WHERE term_taxonomy_id IN ($terms)
			)";

		} elseif ( 'AND' === $operator ) {

			if ( empty( $terms ) ) {
				return $sql;
			}

			$num_terms = count( $terms );

			$terms = implode( ',', $terms );

			$where = '(
				SELECT COUNT(1)
				FROM ' . CN_TERM_RELATIONSHIP_TABLE . "
				WHERE term_taxonomy_id IN ($terms)
				AND entry_id = $this->primary_table.$this->primary_id_column
			) = $num_terms";

		} elseif ( 'NOT EXISTS' === $operator || 'EXISTS' === $operator ) {

			$where = $wpdb->prepare(
				"$operator (
				SELECT 1
				FROM " . CN_TERM_RELATIONSHIP_TABLE . '
				INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . '
				ON ' . CN_TERM_TAXONOMY_TABLE . '.term_taxonomy_id = ' . CN_TERM_RELATIONSHIP_TABLE . '.term_taxonomy_id
				WHERE ' . CN_TERM_TAXONOMY_TABLE . '.taxonomy = %s
				AND ' . CN_TERM_RELATIONSHIP_TABLE . ".entry_id = $this->primary_table.$this->primary_id_column
			)",
				$clause['taxonomy']
			);

		}

		$sql['join'][]  = $join;
		$sql['where'][] = $where;
		return $sql;
	}

	/**
	 * Validates a single query.
	 *
	 * @since 10.3
	 *
	 * @param array $query The single query. Passed by reference.
	 */
	private function clean_query( &$query ) {
		if ( empty( $query['taxonomy'] ) ) {
			if ( 'term_taxonomy_id' !== $query['field'] ) {
				$query = new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.', 'connections' ) );
				return;
			}

			// So long as there are shared terms, 'include_children' requires that a taxonomy is set.
			$query['include_children'] = false;
		} elseif ( ! taxonomy_exists( $query['taxonomy'] ) ) {
			$query = new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.', 'connections' ) );
			return;
		}

		$query['terms'] = array_unique( (array) $query['terms'] );

		if ( is_taxonomy_hierarchical( $query['taxonomy'] ) && $query['include_children'] ) {
			$this->transform_query( $query, 'term_id' );

			if ( is_wp_error( $query ) ) {
				return;
			}

			$children = array();
			foreach ( $query['terms'] as $term ) {
				$children   = array_merge( $children, cnTerm::children( $term, $query['taxonomy'] ) );
				$children[] = $term;
			}
			$query['terms'] = $children;
		}

		$this->transform_query( $query, 'term_taxonomy_id' );
	}

	/**
	 * Transforms a single query, from one field to another.
	 *
	 * Operates on the `$query` object by reference. In the case of error,
	 * `$query` is converted to a WP_Error object.
	 *
	 * @since 10.3
	 *
	 * @global wpdb $wpdb The WordPress database abstraction object.
	 *
	 * @param array  $query           The single query. Passed by reference.
	 * @param string $resulting_field The resulting field. Accepts 'slug', 'name', 'term_taxonomy_id',
	 *                                or 'term_id'. Default 'term_id'.
	 */
	public function transform_query( &$query, $resulting_field ) {
		if ( empty( $query['terms'] ) ) {
			return;
		}

		if ( $query['field'] == $resulting_field ) {
			return;
		}

		$resulting_field = sanitize_key( $resulting_field );

		// Empty 'terms' always results in a null transformation.
		$terms = array_filter( $query['terms'] );
		if ( empty( $terms ) ) {
			$query['terms'] = array();
			$query['field'] = $resulting_field;
			return;
		}

		$args = array(
			'get'                    => 'all',
			'number'                 => 0,
			'taxonomy'               => $query['taxonomy'],
			'update_term_meta_cache' => false,
			'orderby'                => 'none',
		);

		// Term query parameter name depends on the 'field' being searched on.
		switch ( $query['field'] ) {
			case 'slug':
				$args['slug'] = $terms;
				break;
			case 'name':
				$args['name'] = $terms;
				break;
			case 'term_taxonomy_id':
				$args['term_taxonomy_id'] = $terms;
				break;
			default:
				$args['include'] = wp_parse_id_list( $terms );
				break;
		}

		$term_query = new Term();
		$term_list  = $term_query->query( $args );

		if ( is_wp_error( $term_list ) ) {
			$query = $term_list;
			return;
		}

		if ( 'AND' === $query['operator'] && count( $term_list ) < count( $query['terms'] ) ) {
			$query = new WP_Error( 'inexistent_terms', __( 'Inexistent terms.', 'connections' ) );
			return;
		}

		$query['terms'] = wp_list_pluck( $term_list, $resulting_field );
		$query['field'] = $resulting_field;
	}
}
