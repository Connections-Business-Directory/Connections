<?php

namespace Connections_Directory\Taxonomy;

use stdClass;
use WP_Error;
use wpdb;

/**
 * Class Term
 *
 * NOTE: This is the Connections equivalent of @see WP_Term in WordPress core ../wp-includes/class-wp-term.php
 *
 * @package Connections_Directory\Taxonomy
 */
final class Term {

	/**
	 * Term ID.
	 *
	 * @since 8.5.10
	 * @var int
	 */
	public $term_id;

	/**
	 * The term's name.
	 *
	 * @since 8.5.10
	 * @var string
	 */
	public $name = '';

	/**
	 * The term order value.
	 *
	 * Since 10.4.40.
	 * @var int
	 */
	public $order;

	/**
	 * The term's slug.
	 *
	 * @since 8.5.10
	 * @var string
	 */
	public $slug = '';

	/**
	 * The term's term_group.
	 *
	 * @since 8.5.10
	 * @var string
	 */
	public $term_group = '';

	/**
	 * Term Taxonomy ID.
	 *
	 * @since 8.5.10
	 * @var int
	 */
	public $term_taxonomy_id = 0;

	/**
	 * The term's taxonomy name.
	 *
	 * @since 8.5.10
	 * @var string
	 */
	public $taxonomy = '';

	/**
	 * The term's description.
	 *
	 * @since 8.5.10
	 * @var string
	 */
	public $description = '';

	/**
	 * ID of a term's parent term.
	 *
	 * @since 8.5.10
	 * @var int
	 */
	public $parent = 0;

	/**
	 * Cached object count for this term.
	 *
	 * @since 8.5.10
	 * @var int
	 */
	public $count = 0;

	/**
	 * Stores the term object's sanitization level.
	 *
	 * Does not correspond to a database field.
	 *
	 * @since 8.5.10
	 * @var string
	 */
	public $filter = 'raw';

	/**
	 * Retrieve Term instance.
	 *
	 * @since  8.5.10
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int         $term_id  Term ID.
	 * @param null|string $taxonomy Optional. Limit matched terms to those matching `$taxonomy`.
	 *                              Only used for disambiguating potentially shared terms.
	 *
	 * @return Term|WP_Error|false Term object, if found. WP_Error if `$term_id` is shared between taxonomies and
	 *                                      there's insufficient data to distinguish which term is intended.
	 *                                      False for other failures.
	 */
	public static function get( $term_id, $taxonomy = null ) {
		global $wpdb;

		$term_id = (int) $term_id;
		if ( ! $term_id ) {
			return false;
		}

		$_term = wp_cache_get( $term_id, 'cn_terms' );

		// If there isn't a cached version, hit the database.
		if ( ! $_term || ( $taxonomy && $taxonomy !== $_term->taxonomy ) ) {
			// Any term found in the cache is not a match, so don't use it.
			$_term = false;

			// Grab all matching terms, in case any are shared between taxonomies.
			$terms = $wpdb->get_results( $wpdb->prepare( 'SELECT t.*, tt.* FROM ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id WHERE t.term_id = %d', $term_id ) );
			if ( ! $terms ) {
				return false;
			}

			// If a taxonomy was specified, find a match.
			if ( ! is_null( $taxonomy ) && is_string( $taxonomy ) && ! empty( $taxonomy ) ) {
				foreach ( $terms as $match ) {
					if ( $taxonomy === $match->taxonomy ) {
						$_term = $match;
						break;
					}
				}

				// If only one match was found, it's the one we want.
			} elseif ( 1 === count( $terms ) ) {
				$_term = reset( $terms );

				// Otherwise, the term must be shared between taxonomies.
			} else {
				// If the term is shared only with invalid taxonomies, return the one valid term.
				foreach ( $terms as $t ) {
					// @todo Add check.
					// if ( ! taxonomy_exists( $t->taxonomy ) ) {
					// 	continue;
					// }

					// Only hit if we've already identified a term in a valid taxonomy.
					if ( $_term ) {
						return new WP_Error( 'ambiguous_term_id', __( 'Term ID is shared between multiple taxonomies', 'connections' ), $term_id );
					}

					$_term = $t;
				}
			}

			if ( ! $_term ) {
				return false;
			}

			// @todo Add check.
			// Don't return terms from invalid taxonomies.
			// if ( ! taxonomy_exists( $_term->taxonomy ) ) {
			// 	return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy', 'connections' ) );
			// }

			$_term = sanitize_term( $_term, 'cn_' . $_term->taxonomy, 'raw' );

			// Don't cache terms that are shared between taxonomies.
			if ( 1 === count( $terms ) ) {

				wp_cache_add( $term_id, $_term, 'cn_terms' );
			}
		}

		$term_obj = new Term( $_term );
		$term_obj->filter( $term_obj->filter );

		return $term_obj;
	}

	/**
	 * Constructor.
	 *
	 * @since 8.5.10
	 * @param Term|object $term Term object.
	 */
	public function __construct( $term ) {
		foreach ( get_object_vars( $term ) as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Sanitizes term fields, according to the filter type provided.
	 *
	 * @since 8.5.10
	 *
	 * @param string $filter Filter context. Accepts 'edit', 'db', 'display', 'attribute', 'js', 'raw'.
	 */
	public function filter( $filter ) {
		sanitize_term( $this, 'cn_' . $this->taxonomy, $filter );
	}

	/**
	 * Converts an object to array.
	 *
	 * @since 8.5.10
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Getter.
	 *
	 * @since 8.5.10
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'data':
				$data    = new stdClass();
				$columns = array( 'term_id', 'name', 'slug', 'term_group', 'term_taxonomy_id', 'taxonomy', 'description', 'parent', 'count' );
				foreach ( $columns as $column ) {
					$data->{$column} = isset( $this->{$column} ) ? $this->{$column} : null;
				}

				return sanitize_term( $data, 'cn_' . $data->taxonomy, 'raw' );
		}
	}
}
