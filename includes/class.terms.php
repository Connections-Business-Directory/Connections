<?php

/**
 * Taxonomy management.
 *
 * @package     Connections
 * @subpackage  Taxonomy
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnTerms
{

	/**
	 * Returns all the terms under a taxonomy type.
	 *
	 * $taxonomies currently this will only accept a string of the specified taxonomy
	 *
	 * @access public
	 * @since  unknown
	 * @deprecated 8.1.6 Use {@see cnTerm::tree()} instead.
	 * @see cnTerm::tree()
	 *
	 * @param array $taxonomies
	 * @param array $atts [optional]
	 *
	 * @return array
	 */
	public function getTerms( $taxonomies, $atts = array() ) {

		return cnTerm::tree( $taxonomies, $atts );
	}

	/**
	 * @deprecated 8.1.6 Use {@see cnTerm::get()} instead.
	 * @see cnTerm::get()
	 *
	 * @param $id
	 * @param $taxonomy
	 *
	 * @return mixed|null|WP_Error
	 */
	public function getTerm($id, $taxonomy) {

		return cnTerm::get( $id, $taxonomy );
	}

	/**
	 * Get term data by 'name', 'id' or 'slug'.
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::getBy()} instead.
	 * @see cnTerm::getBy()
	 *
	 * @param string $field
	 * @param string | int -- Search term
	 * @param string $taxonomy
	 *
	 * @return mixed | False or term object
	 */
	public function getTermBy( $field, $value, $taxonomy ) {

		return cnTerm::getBy( $field, $value, $taxonomy );
	}

	/**
	 * Returns all the children term IDs of the parent term ID recursively.
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::children()} instead.
	 * @see cnTerm::children()
	 *
	 * @param integer $id
	 * @param string  $taxonomy
	 *
	 * @return array
	 */
	public function getTermChildrenIDs( $id, $taxonomy ) {

		return cnTerm::children( $id, $taxonomy );
	}

	/**
	 * Returns all the children terms of the parent term recursively by 'term_id', 'name' or 'slug'.
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $taxonomy
	 * @param array  $_previousResults [optional]
	 *
	 * @return array
	 */
	public function getTermChildrenBy( $field, $value, $taxonomy, $_previousResults = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;
		$results = array();

		// Only run this query if the field is not term_id.
		if ( $field !== 'term_id' ) {

			$queryTermID = $wpdb->prepare(
				"SELECT DISTINCT tt.term_id from " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE $field = %s ",
				$value
			);
			//print_r($queryTermID . '<br /><br />');

			$termID = $wpdb->get_var( $queryTermID );
			//print_r($termID . '<br /><br />');

			// If the term is a root parent, skip continue.
			if ( empty( $termID ) ) {

				return array();
			}

		} else {

			$termID = $value;
		}


		$queryChildrenIDs = $wpdb->prepare(
			"SELECT DISTINCT * from " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE parent = %d ",
			$termID
		);
		//print_r($queryChildrenIDs . '<br /><br />');

		$terms = $wpdb->get_results( $queryChildrenIDs );

		if ( empty( $terms ) ) {

			return array();
		}

		foreach ( $terms as $term ) {

			// If the term is a root parent, skip continue.
			if ( $term->parent == 0 ) {

				continue;
			}

			$result = $this->getTermChildrenBy( 'term_id', $term->term_id, $taxonomy, $terms );

			$results = array_merge( (array) $results, (array) $result );
		}

		return array_merge( (array) $terms, (array) $results );
	}

	/**
	 * Adds a new term.
	 *
	 * $term - (string) Term name.
	 * $taxonomy - (string) taxonomy of the term to be updated
	 * $attributes - (array)    slug - (string)
	 *                          parent - (int)
	 *                          description - (string)
	 *
	 * @access public
	 * @deprecated 8.1.6 Use {@see cnTerm::insert()} instead.
	 * @see cnTerm::insert()
	 *
	 * @param string $term
	 * @param string $taxonomy
	 * @param array  $attributes
	 *
	 * @return int                The term id.
	 */
	public function addTerm( $term, $taxonomy, $attributes ) {

		$result = cnTerm::insert( $term, $taxonomy, $attributes );

		return $result;
	}

	/**
	 * Updates a term.
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::update()} instead.
	 * @see cnTerm::update()
	 *
	 * $termID - (int) ID of the term to be updated
	 * $taxonomy - (string) taxonomy of the term to be updated
	 * $attributes - (array)    name - (string)
	 *                          slug - (string)
	 *                          parent - (int)
	 *                          description - (string)
	 *
	 * @param int    $termID
	 * @param string $taxonomy
	 * @param array  $attributes
	 *
	 * @return bool
	 */
	public function updateTerm( $termID, $taxonomy, $attributes ) {

		$result = cnTerm::update( $termID, $taxonomy, $attributes );

		return $result;
	}

	/**
	 * Remove a term from the database.
	 *
	 * If the term contains children terms, the children terms will be updated
	 * to the deleted term parent.
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::delete()} instead.
	 * @see cnTerm::delete()
	 *
	 * @param int    $id       Term ID.
	 * @param int    $id       Term parent ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return bool
	 */
	public function deleteTerm( $id, $parent, $taxonomy ) {

		$result = cnTerm::delete( $id, $taxonomy );

		return $result;
	}

	/**
	 * Creates the entry and term relationships.
	 *
	 * If the term $IDs is empty then the uncategorized category is set as the relationship.
	 * NOTE: Only if the taxonomy is 'category'
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::setRelationships()} instead.
	 * @see cnTerm::setRelationships()
	 *
	 * @param int    $entryID
	 * @param array  $termIDs
	 * @param string $taxonomy
	 *
	 * @return bool
	 */
	public function setTermRelationships( $entryID, $termIDs, $taxonomy ) {

		if ( ! is_array( $termIDs ) ) {

			$termIDs = array( $termIDs );
		}

		$termIDs = array_map( 'intval', $termIDs );
		$result  = cnTerm::setRelationships( $entryID, $termIDs, $taxonomy );

		if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
			cnTerm::updateCount( $result, $taxonomy );
		}

		cnCache::clear( TRUE, 'transient', "cn_{$taxonomy}" );

		return $result;
	}

	/**
	 * Retrieve the entry's term relationships.
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::getRelationships()} instead.
	 * @see cnTerm::getRelationships()
	 *
	 * @param integer $entryID
	 *
	 * @return mixed | False or array of term relationships.
	 */
	public function getTermRelationships( $entryID ) {

		return cnTerm::getRelationships( $entryID, 'category', array( 'fields' => 'ids' ) );
	}

	/**
	 * Deletes all entry's relationships.
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::deleteRelationships()} instead.
	 * @see cnTerm::deleteRelationships()
	 *
	 * @param integer $entryID
	 *
	 * @return bool
	 */
	public function deleteTermRelationships( $entryID ) {

		$terms  = cnTerm::getRelationships( $entryID, 'category', array( 'fields' => 'ids' ) );
		$result = cnTerm::deleteRelationships( $entryID, $terms, 'category' );

		cnCache::clear( TRUE, 'transient', "cn_category" );

		return $result;
	}
}

/**
 * Class cnTerm
 */
class cnTerm {

	/**
	 * Retrieves the terms associated with the given object(s), in the supplied taxonomies.
	 *
	 * The fields argument also decides what will be returned. If 'all' or
	 * 'all_with_object_id' is chosen or the default kept intact, then all matching
	 * terms objects will be returned. If either 'ids' or 'names' is used, then an
	 * array of all matching term ids or term names will be returned respectively.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_get_object_terms() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   wp_parse_args()
	 * @uses   wpdb::get_results()
	 * @uses   sanitize_term()
	 * @uses   update_term_cache()
	 * @uses   wpdb::get_col()
	 * @uses   sanitize_term_field()
	 * @uses   apply_filters()
	 *
	 * @param int|array    $object_ids The ID(s) of the object(s) to retrieve.
	 * @param string|array $taxonomies The taxonomies to retrieve terms from.
	 * @param array|string $args {
	 *    Optional. Change what is returned
	 *
	 *    @type string 'orderby' Default: name. Accepts: name | slug | term_id | term_group | term_order | count | none
	 *    @type string 'order'   Default: ASC. Accepts: ASC | DESC
	 *    @type string 'fields'  Default: all. Accepts: all | ids | names | slugs | all_with_entry_id
	 * }
	 *
	 * @return array|WP_Error The requested term data or empty array if no terms found. WP_Error if any of the $taxonomies don't exist.
	 */
	public static function getRelationships( $object_ids, $taxonomies, $args = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$select = array();

		if ( empty( $object_ids ) || empty( $taxonomies ) ) {

			return array();
		}

		if ( ! is_array( $taxonomies ) ) {

			$taxonomies = array( $taxonomies );
		}

		// @todo Add the taxonomy check.
		foreach ( $taxonomies as $taxonomy ) {

			//if ( ! taxonomy_exists($taxonomy) )
			//	return new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));
		}

		if ( ! is_array( $object_ids ) ) {

			$object_ids = array( $object_ids );
		}

		$object_ids = array_map( 'intval', $object_ids );

		$defaults = array(
			'orderby' => 'name',
			'order'   => 'ASC',
			'fields'  => 'all'
		);

		$args     = wp_parse_args( $args, $defaults );

		$terms    = array();

		// @todo Implement the following block of code.
		//if ( count($taxonomies) > 1 ) {
		//	foreach ( $taxonomies as $index => $taxonomy ) {
		//		$t = get_taxonomy($taxonomy);
		//		if ( isset($t->args) && is_array($t->args) && $args != array_merge($args, $t->args) ) {
		//			unset($taxonomies[$index]);
		//			$terms = array_merge($terms, self::getRelationships($object_ids, $taxonomy, array_merge($args, $t->args)));
		//		}
		//	}
		//} else {
		//	$t = get_taxonomy($taxonomies[0]);
		//	if ( isset($t->args) && is_array($t->args) )
		//		$args = array_merge($args, $t->args);
		//}

		$orderby = $args['orderby'];
		$order   = $args['order'];
		//$fields  = $args['fields'];

		if ( 'count' == $orderby ) {
			$orderby = 'tt.count';
		} else if ( 'name' == $orderby ) {
			$orderby = 't.name';
		} else if ( 'slug' == $orderby ) {
			$orderby = 't.slug';
		} else if ( 'term_group' == $orderby ) {
			$orderby = 't.term_group';
		} else if ( 'term_order' == $orderby ) {
			$orderby = 'tr.term_order';
		} else if ( 'none' == $orderby ) {
			$orderby = '';
			$order   = '';
		} else {
			$orderby = 't.term_id';
		}

		// tt_ids queries can only be none or tr.term_taxonomy_id
		if ( ( 'tt_ids' == $args['fields'] ) && ! empty( $orderby ) ) {

			$orderby = 'tr.term_taxonomy_id';
		}

		if ( ! empty( $orderby ) ) {

			$orderby = "ORDER BY $orderby";
		}

		$order = strtoupper( $order );

		if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {

			$order = 'ASC';
		}

		$taxonomies = "'" . implode( "', '", $taxonomies ) . "'";
		$object_ids = implode( ', ', $object_ids );

		switch ( $args['fields'] ) {

			case 'all':
				$select = array( 't.*', 'tt.*' );
				break;

			case 'ids':
				$select = array( 't.term_id' );
				break;

			case 'names':
				$select = array( 't.name' );
				break;

			case 'slugs':
				$select  = array( 't.slug' );
				break;

			case 'all_with_entry_id':
				$select = array( 't.*', 'tt.*', 'tr.entry_id' );
				break;
		}

		/**
		 * --> START <-- This block of code deviates quite a bit from the code copied
		 * from core WP to add filters which can be hooked into.
		 */

		/**
		 * Filter the fields to select in the terms query.
		 *
		 * @since 8.2
		 *
		 * @param array        $select     An array of fields to select for the terms query.
		 * @param array        $args       An array of term query arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$fields  = implode( ', ', apply_filters( 'cn_get_term_relationship_fields', $select, $args, $taxonomies ) );

		$join    = 'INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id INNER JOIN ' . CN_TERM_RELATIONSHIP_TABLE . ' AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id';

		$where   = array( "tt.taxonomy IN ($taxonomies) AND tr.entry_id IN ($object_ids)" );

		$orderBy = "$orderby $order";

		$pieces  = array( 'fields', 'join', 'where', 'orderBy' );

		/**
		 * Filter the terms query SQL clauses.
		 *
		 * @since 8.2
		 *
		 * @param array        $pieces     Terms query SQL clauses.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 * @param array        $atts       An array of terms query arguments.
		 */
		$clauses = apply_filters( 'cn_term_relationship_clauses', compact( $pieces ), $taxonomies, $args );

		foreach ( $pieces as $piece ) {

			$$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';
		}

		$query = sprintf(
			'SELECT %1$s FROM %2$s AS t %3$s WHERE %4$s %5$s',
			$fields,
			CN_TERMS_TABLE,
			$join,
			implode( ' ', $where ),
			$orderBy
		);

		/**
		 * --> END <--
		 */

		//$query = "SELECT $select_this FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON tt.term_id = t.term_id INNER JOIN " . CN_TERM_RELATIONSHIP_TABLE . " AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ($taxonomies) AND tr.entry_id IN ($object_ids) $orderby $order";

		$objects = FALSE;

		/*
		 * @todo There is very likely a bug in the code that uses the $taxonomy var as it is never explicitly set.
		 * The $taxonomy var is from the supplied $taxonomies var required by this method. The $taxonomies var can
		 * be an array or a string, when supplied as a string it is converted to an array. The $taxonomy var
		 * only happens to be set because it is being set in a foreach loop. The likely bug is that if multiple
		 * taxonomies are supplied, only the last one supplied will be used in the sanitize_term() and sanitize_term_field()
		 * calls.
		 */

		if ( 'all' == $args['fields'] || 'all_with_entry_id' == $args['fields'] ) {

			$_terms = $wpdb->get_results( $query );

			foreach ( $_terms as $key => $term ) {

				$_terms[ $key ] = sanitize_term( $term, 'cn_' . $taxonomy, 'raw' );
			}

			$terms = array_merge( $terms, $_terms );

			update_term_cache( $terms, 'cn_' . $taxonomy );

			$objects = TRUE;

		} else if ( 'ids' == $args['fields'] || 'names' == $args['fields'] || 'slugs' == $args['fields'] ) {

			$_terms = $wpdb->get_col( $query );
			$_field = ( 'ids' == $args['fields'] ) ? 'term_id' : 'name';

			foreach ( $_terms as $key => $term ) {

				$_terms[ $key ] = sanitize_term_field( $_field, $term, $term, 'cn_' . $taxonomy, 'raw' );
			}

			$terms = array_merge( $terms, $_terms );

		} else if ( 'tt_ids' == $args['fields'] ) {

			$terms = $wpdb->get_col(
				"SELECT tr.term_taxonomy_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " AS tr INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tr.entry_id IN ($object_ids) AND tt.taxonomy IN ($taxonomies) $orderby $order"
			);

			foreach ( $terms as $key => $tt_id ) {

				$terms[ $key ] = sanitize_term_field(
					'term_taxonomy_id',
					$tt_id,
					0,
					'cn_' . $taxonomy,
					'raw'
				); // 0 should be the term id, however is not needed when using raw context.

			}
		}

		if ( ! $terms ) {

			$terms = array();

		} elseif ( $objects && 'all_with_entry_id' !== $args['fields'] ) {

			$_tt_ids = array();
			$_terms  = array();

			foreach ( $terms as $term ) {

				if ( in_array( $term->term_taxonomy_id, $_tt_ids ) ) {
					continue;
				}

				$_tt_ids[] = $term->term_taxonomy_id;
				$_terms[]  = $term;
			}

			$terms = $_terms;

		} elseif ( ! $objects ) {

			$terms = array_values( array_unique( $terms ) );
		}

		/**
		 * Filter the terms for a given object or objects.
		 *
		 * @since 8.1.6
		 *
		 * @param array        $terms      An array of terms for the given object or objects.
		 * @param array|int    $object_ids Object ID or array of IDs.
		 * @param array|string $taxonomies A taxonomy or array of taxonomies.
		 * @param array        $args       An array of arguments for retrieving terms for
		 *                                 the given object(s).
		 */
		return apply_filters( 'cn_get_object_terms', $terms, $object_ids, $taxonomies, $args );
	}

	/**
	 * Create term and taxonomy relationships.
	 *
	 * Relates an object (entry) to a term and taxonomy.
	 * Creates the term and taxonomy relationship if it does not already exist.
	 * Creates a term if it does not exist (using the slug).
	 *
	 * A relationship means that the term is grouped in or belongs to the taxonomy.
	 * A term has no meaning until it is given context by defining which taxonomy it
	 * exists under.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_set_object_terms() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global @wpdb
	 *
	 * @uses   cnTerm::getRelationships()
	 * @uses   cnTerm::exists()
	 * @uses   cnTerm::insert()
	 * @uses   is_wp_error()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_var()
	 * @uses   do_action()
	 * @uses   wpdb::insert()
	 * @uses   cnTerm::updateCount()
	 * @uses   wpdb::get_col()
	 * @uses   cnTerm::deleteRelationships()
	 * @uses   wp_cache_delete()
	 *
	 * @param int              $object_id The object to relate to.
	 * @param array|int|string $terms     A single term slug, single term id, or array of either term slugs or ids.
	 *                                    Will replace all existing related terms in this taxonomy.
	 * @param array|string     $taxonomy  The context in which to relate the term to the object.
	 * @param bool             $append    Optional. If false will delete difference of terms. Default false.
	 *
	 * @return array|WP_Error Affected Term IDs.
	 */
	public static function setRelationships( $object_id, $terms, $taxonomy, $append = FALSE ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$object_id = (int) $object_id;

		//if ( ! taxonomy_exists($taxonomy) )
		//	return new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));

		if ( ! is_array( $terms ) ) {

			$terms = array( $terms );
		}

		if ( ! $append ) {

			$old_tt_ids = self::getRelationships(
				$object_id,
				$taxonomy,
				array( 'fields' => 'tt_ids', 'orderby' => 'none' )
			);

		} else {

			$old_tt_ids = array();
		}

		$tt_ids     = array();
		$term_ids   = array();
		$new_tt_ids = array();

		foreach ( (array) $terms as $term ) {

			if ( ! strlen( trim( $term ) ) ) {

				continue;
			}

			if ( ! $term_info = self::exists( $term, $taxonomy ) ) {

				// Skip if a non-existent term ID is passed.
				if ( is_int( $term ) ) {
					continue;
				}

				$term_info = self::insert( $term, $taxonomy );
			}

			if ( is_wp_error( $term_info ) ) {

				return $term_info;
			}

			$term_ids[] = $term_info['term_id'];
			$tt_id      = $term_info['term_taxonomy_id'];
			$tt_ids[]   = $tt_id;

			if ( $wpdb->get_var(
				$wpdb->prepare(
					"SELECT term_taxonomy_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d AND term_taxonomy_id = %d",
					$object_id,
					$tt_id
				)
			)
			) {
				continue;
			}

			/**
			 * Fires immediately before an object-term relationship is added.
			 *
			 * @since 8.1.6
			 *
			 * @param int $object_id Object ID.
			 * @param int $tt_id     Term taxonomy ID.
			 */
			do_action( 'cn_add_term_relationship', $object_id, $tt_id );

			$wpdb->insert(
				CN_TERM_RELATIONSHIP_TABLE,
				array( 'entry_id' => $object_id, 'term_taxonomy_id' => $tt_id )
			);

			/**
			 * Fires immediately after an object-term relationship is added.
			 *
			 * @since 8.1.6
			 *
			 * @param int $object_id Object ID.
			 * @param int $tt_id     Term taxonomy ID.
			 */
			do_action( 'cn_added_term_relationship', $object_id, $tt_id );

			$new_tt_ids[] = $tt_id;
		}

		if ( $new_tt_ids ) {

			self::updateCount( $new_tt_ids, $taxonomy );
		}

		if ( ! $append ) {

			$delete_tt_ids = array_diff( $old_tt_ids, $tt_ids );

			if ( $delete_tt_ids ) {

				$in_delete_tt_ids = "'" . implode( "', '", $delete_tt_ids ) . "'";

				$delete_term_ids  = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT tt.term_id FROM " . CN_TERM_TAXONOMY_TABLE . " AS tt WHERE tt.taxonomy = %s AND tt.term_taxonomy_id IN ($in_delete_tt_ids)",
						$taxonomy
					)
				);

				$delete_term_ids  = array_map( 'intval', $delete_term_ids );

				$remove = self::deleteRelationships( $object_id, $delete_term_ids, $taxonomy );

				if ( is_wp_error( $remove ) ) {

					return $remove;
				}

			}
		}

		// @todo Implement the following block of code.
		//$t = get_taxonomy($taxonomy);

		//if ( ! $append && isset($t->sort) && $t->sort ) {
		//
		//	$values = array();
		//	$term_order = 0;
		//	$final_tt_ids = self::getRelationships($object_id, $taxonomy, array('fields' => 'tt_ids'));
		//
		//	foreach ( $tt_ids as $tt_id )
		//		if ( in_array($tt_id, $final_tt_ids) )
		//			$values[] = $wpdb->prepare( "(%d, %d, %d)", $object_id, $tt_id, ++$term_order);
		//	if ( $values )
		//		if ( false === $wpdb->query( "INSERT INTO " . CN_TERM_RELATIONSHIP_TABLE . " (object_id, term_taxonomy_id, term_order) VALUES " . join( ',', $values ) . " ON DUPLICATE KEY UPDATE term_order = VALUES(term_order)" ) )
		//			return new WP_Error( 'db_insert_error', __( 'Could not insert term relationship into the database' ), $wpdb->last_error );
		//}

		wp_cache_delete( $object_id, 'cn_' . $taxonomy . '_relationships' );

		/**
		 * Fires after an object's terms have been set.
		 *
		 * @since 8.1.6
		 *
		 * @param int    $object_id  Object ID.
		 * @param array  $terms      An array of object terms.
		 * @param array  $tt_ids     An array of term taxonomy IDs.
		 * @param string $taxonomy   Taxonomy slug.
		 * @param bool   $append     Whether to append new terms to the old terms.
		 * @param array  $old_tt_ids Old array of term taxonomy IDs.
		 */
		do_action( 'cn_set_object_terms', $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids );

		return $tt_ids;
	}

	/**
	 * Remove term(s) associated with a given entry.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   cnTerm::exists()
	 * @uses   is_wp_error()
	 * @uses   do_action()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::query()
	 * @uses   cnTerm::updateCount()
	 *
	 * @param int              $object_id The ID of the object from which the terms will be removed.
	 * @param array|int|string $terms     The slug(s) or ID(s) of the term(s) to remove.
	 * @param array|string     $taxonomy  Taxonomy name.
	 *
	 * @return bool|WP_Error True on success, false or WP_Error on failure.
	 */
	public static function deleteRelationships( $object_id, $terms, $taxonomy ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$object_id = (int) $object_id;

		// @todo Implement the taxonomy check.
		//if ( ! taxonomy_exists( $taxonomy ) ) {
		//	return new WP_Error( 'invalid_taxonomy', __( 'Invalid Taxonomy' ) );
		//}

		if ( ! is_array( $terms ) ) {

			$terms = array( $terms );
		}

		$tt_ids = array();

		foreach ( (array) $terms as $term ) {

			if ( ! strlen( trim( $term ) ) ) {
				continue;
			}

			if ( ! $term_info = self::exists( $term, $taxonomy ) ) {

				// Skip if a non-existent term ID is passed.
				if ( is_int( $term ) ) {
					continue;
				}
			}

			if ( is_wp_error( $term_info ) ) {

				return $term_info;
			}

			$tt_ids[] = $term_info['term_taxonomy_id'];
		}

		if ( $tt_ids ) {

			$in_tt_ids = "'" . implode( "', '", $tt_ids ) . "'";

			/**
			 * Fires immediately before an object-term relationship is deleted.
			 *
			 * @since 8.1.6
			 *
			 * @param int   $object_id Object ID.
			 * @param array $tt_ids    An array of term taxonomy IDs.
			 */
			do_action( 'cn_delete_term_relationships', $object_id, $tt_ids );

			$deleted = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d AND term_taxonomy_id IN ($in_tt_ids)",
					$object_id
				)
			);

			/**
			 * Fires immediately after an object-term relationship is deleted.
			 *
			 * @since 8.1.6
			 *
			 * @param int   $object_id Object ID.
			 * @param array $tt_ids    An array of term taxonomy IDs.
			 */
			do_action( 'cn_deleted_term_relationships', $object_id, $tt_ids );

			self::updateCount( $tt_ids, $taxonomy );

			return (bool) $deleted;
		}

		return FALSE;
	}

	/**
	 * Retrieves the taxonomy relationship to the object id.
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @uses   wp_cache_get()
	 *
	 * @param int|      $id       Object ID.
	 * @param string    $taxonomy Taxonomy Name.
	 *
	 * @return mixed array|bool Array of terms if found, FALSE if not found.
	 */
	public static function getRelationshipsCache( $id, $taxonomy ) {

		$cache = wp_cache_get( $id, "cn_{$taxonomy}_relationships" );

		return $cache;
	}

	/**
	 * Updates the amount of terms in taxonomy.
	 *
	 * If there is a taxonomy callback applied, then it will be called for updating
	 * the count.
	 *
	 * The default action is to count what the amount of terms have the relationship
	 * of term ID. Once that is done, then update the database.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_update_term_count() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @uses   cnTerm::updateCountNow()
	 * @uses   cnTerm::deferCount()
	 *
	 * @param int|array $terms       The term_taxonomy_id of the terms
	 * @param string    $taxonomy    The context of the term.
	 * @param bool      $do_deferred Whether or not to process the deferred count updating.
	 *
	 * @return bool If no terms will return false, and if successful will return true.
	 */
	public static function updateCount( $terms, $taxonomy, $do_deferred = FALSE ) {

		static $_deferred = array();

		if ( $do_deferred ) {

			foreach ( (array) array_keys( $_deferred ) as $tax ) {

				self::updateCountNow( $_deferred[ $tax ], $tax );
				unset( $_deferred[ $tax ] );
			}

		}

		if ( empty( $terms ) ) {

			return FALSE;
		}

		if ( ! is_array( $terms ) ) {

			$terms = array( $terms );
		}

		if ( self::deferCount() ) {

			if ( ! isset( $_deferred[ $taxonomy ] ) ) {

				$_deferred[ $taxonomy ] = array();
			}

			$_deferred[ $taxonomy ] = array_unique( array_merge( $_deferred[ $taxonomy ], $terms ) );

			return TRUE;
		}

		return self::updateCountNow( $terms, $taxonomy );
	}

	/**
	 * Enable or disable term counting.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_defer_term_counting() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @uses   cnTerm::updateCount()
	 *
	 * @param bool $defer Optional. Enable if true, disable if false.
	 *
	 * @return bool Whether term counting is enabled or disabled.
	 */
	public static function deferCount( $defer = NULL ) {

		static $_defer = FALSE;

		if ( is_bool( $defer ) ) {

			$_defer = $defer;

			// flush any deferred counts
			if ( ! $defer ) {

				self::updateCount( NULL, NULL, TRUE );
			}

		}

		return $_defer;
	}

	/**
	 * Perform term count update immediately.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_update_term_count_now() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @uses   cnTerm::updateRelationshipCount()
	 * @uses   cnTerm::cleanCache()
	 *
	 * @param array  $terms    The term_taxonomy_id of terms to update.
	 * @param string $taxonomy The context of the term.
	 *
	 * @return bool Always true when complete.
	 */
	public static function updateCountNow( $terms, $taxonomy ) {

		$terms = array_map( 'intval', $terms );

		//$taxonomy = get_taxonomy( $taxonomy );

		//if ( ! empty( $taxonomy->update_count_callback ) ) {

			//call_user_func( $taxonomy->update_count_callback, $terms, $taxonomy );

		//} else {

			//$object_types = (array) $taxonomy->object_type;

			//foreach ( $object_types as &$object_type ) {

				//if ( 0 === strpos( $object_type, 'attachment:' ) ) {

				//	list( $object_type ) = explode( ':', $object_type );
				//}

			//}

			//if ( $object_types == array_filter( $object_types, 'post_type_exists' ) ) {

				// Only post types are attached to this taxonomy
				self::updateRelationshipCount( $terms, $taxonomy );

			//} else {

				// Default count updater
			//	_update_generic_term_count( $terms, $taxonomy );
			//}

		//}

		self::cleanCache( $terms, '', FALSE );

		return TRUE;
	}

	/**
	 * Will update term count based on object types of the current taxonomy.
	 *
	 * Private function for the default callback for post_tag and category
	 * taxonomies.
	 *
	 * NOTE: This is the Connections equivalent of @see _update_post_term_count() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access private
	 * @since  8.1.6
	 *
	 * @global $wpdb
	 *
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_var()
	 * @uses   do_action()
	 * @uses   wpdb::update()
	 *
	 * @param array  $terms    List of Term taxonomy IDs
	 * @param string $taxonomy Current taxonomy object of terms
	 */
	private static function updateRelationshipCount( $terms, $taxonomy ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		//$object_types = (array) $taxonomy->object_type;
		//
		//foreach ( $object_types as &$object_type ) {
		//
		//	list( $object_type ) = explode( ':', $object_type );
		//}
		//
		//$object_types = array_unique( $object_types );

		/** Not needed, entries do not have attachments, yet. */
		//if ( FALSE !== ( $check_attachments = array_search( 'attachment', $object_types ) ) ) {
		//
		//	unset( $object_types[ $check_attachments ] );
		//	$check_attachments = TRUE;
		//}

		//if ( $object_types ) {
		//
		//	$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );
		//}

		foreach ( (array) $terms as $term ) {

			$count = 0;

			/** Not needed, entries do not have attachments, yet. */
			// Attachments can be 'inherit' status, we need to base count off the parent's status if so
			//if ( $check_attachments ) {
			//	$count += (int) $wpdb->get_var(
			//		$wpdb->prepare(
			//			"SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . ", " . CN_ENTRY_TABLE . " p1 WHERE p1.id = " . CN_TERM_RELATIONSHIP_TABLE . ".entry_id AND ( status = 'approved' OR ( post_status = 'inherit' AND post_parent > 0 AND ( SELECT status FROM " . CN_ENTRY_TABLE . " WHERE id = p1.post_parent ) = 'publish' ) ) AND post_type = 'attachment' AND term_taxonomy_id = %d",
			//			$term
			//		)
			//	);
			//}

			//if ( $object_types ) {

				$count += (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . ", " . CN_ENTRY_TABLE . " WHERE " . CN_ENTRY_TABLE . ".id = " . CN_TERM_RELATIONSHIP_TABLE . ".entry_id AND status = 'approved' AND visibility != 'unlisted' AND term_taxonomy_id = %d",
						$term
					)
				);

			//}

			/** This action is documented in @see cnTerm::update() */
			do_action( 'cn_edit_term_taxonomy', $term, $taxonomy );

			$wpdb->update( CN_TERM_TAXONOMY_TABLE, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );

			/** This action is documented in @see cnTerm::update() */
			do_action( 'cn_edited_term_taxonomy', $term, $taxonomy );
		}
	}

	/**
	 * Check if a term exists.
	 *
	 * NOTE: This is the Connections equivalent of @see term_exists() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   wpdb::get_row()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_var()
	 * @uses   wp_unslash()
	 * @uses   sanitize_title()
	 *
	 * @param int|string $term     The term to check.
	 * @param string     $taxonomy The taxonomy name.
	 * @param int        $parent   ID of parent term under which to confine the exists search.
	 *
	 * @return mixed Returns 0 if the term does not exist. Returns the term ID if no taxonomy is specified
	 *               and the term ID exists. Returns an array of the term ID and the term taxonomy ID
	 *               if the taxonomy is specified and the pairing exists.
	 */
	public static function exists( $term, $taxonomy = '', $parent = 0 ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$select     = "SELECT term_id FROM " . CN_TERMS_TABLE . " as t WHERE ";
		$tax_select = "SELECT tt.term_id, tt.term_taxonomy_id FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " as tt ON tt.term_id = t.term_id WHERE ";

		if ( is_int( $term ) ) {

			if ( 0 == $term ) {
				return 0;
			}

			$where = 't.term_id = %d';

			if ( ! empty( $taxonomy ) ) {

				return $wpdb->get_row(
					$wpdb->prepare( $tax_select . $where . " AND tt.taxonomy = %s", $term, $taxonomy ),
					ARRAY_A
				);

			} else {

				return $wpdb->get_var( $wpdb->prepare( $select . $where, $term ) );
			}
		}

		$term = trim( wp_unslash( $term ) );

		if ( '' === $slug = sanitize_title( $term ) ) {

			return 0;
		}

		$where             = 't.slug = %s';
		$else_where        = 't.name = %s';
		$where_fields      = array( $slug );
		$else_where_fields = array( $term );

		if ( ! empty( $taxonomy ) ) {

			$parent = (int) $parent;

			if ( $parent > 0 ) {

				$where_fields[]      = $parent;
				$else_where_fields[] = $parent;
				$where .= ' AND tt.parent = %d';
				$else_where .= ' AND tt.parent = %d';
			}

			$where_fields[]      = $taxonomy;
			$else_where_fields[] = $taxonomy;

			if ( $result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT tt.term_id, tt.term_taxonomy_id FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " as tt ON tt.term_id = t.term_id WHERE $where AND tt.taxonomy = %s",
					$where_fields
				),
				ARRAY_A
			)
			) {
				return $result;
			}

			return $wpdb->get_row(
				$wpdb->prepare(
					"SELECT tt.term_id, tt.term_taxonomy_id FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " as tt ON tt.term_id = t.term_id WHERE $else_where AND tt.taxonomy = %s",
					$else_where_fields
				),
				ARRAY_A
			);
		}

		if ( $result = $wpdb->get_var(
			$wpdb->prepare( "SELECT term_id FROM " . CN_TERMS_TABLE . " as t WHERE $where", $where_fields )
		)
		) {
			return $result;
		}

		return $wpdb->get_var(
			$wpdb->prepare( "SELECT term_id FROM " . CN_TERMS_TABLE . " as t WHERE $else_where", $else_where_fields )
		);
	}

	/**
	 * Add a new term to the database.
	 *
	 * A non-existent term is inserted in the following sequence:
	 *
	 * 1. The term is added to the term table, then related to the taxonomy.
	 * 2. If everything is correct, several actions are fired.
	 * 3. The 'term_id_filter' is evaluated.
	 * 4. The term cache is cleaned.
	 * 5. Several more actions are fired.
	 * 6. An array is returned containing the term_id and term_taxonomy_id.
	 *
	 * If the 'slug' argument is not empty, then it is checked to see if the term
	 * is invalid. If it is not a valid, existing term, it is added and the term_id
	 * is given.
	 *
	 * If the taxonomy is hierarchical, and the 'parent' argument is not empty,
	 * the term is inserted and the term_id will be given.
	 * Error handling:
	 * If $taxonomy does not exist or $term is empty,
	 * a WP_Error object will be returned.
	 *
	 * If the term already exists on the same hierarchical level,
	 * or the term slug and name are not unique, a WP_Error object will be returned.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_insert_term() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * Actions:
	 *    cn_edit_terms
	 *        Passes: (int) $term_id, (string) $taxonomy
	 *
	 *    cn_edited_terms
	 *        Passes: (int) $term_id, (string) $taxonomy
	 *
	 *    cn_create_term
	 *        Passes: (int) $term_id, (int) $taxonomy_term_id, (string) $taxonomy
	 *
	 *    cn_create_$taxonomy
	 *        Passes: (int) $term_id, (int) $taxonomy_term_id
	 *
	 *    cn_created_term
	 *        Passes: (int) $term_id, (int) $taxonomy_term_id, (string) $taxonomy
	 *
	 *    cn_created_$taxonomy
	 *       Passes: (int) $term_id, (int) $taxonomy_term_id
	 *
	 * Filters:
	 *    cn_pre_insert_term
	 *        Passes: (string) $term, (string) $taxonomy
	 *        Return: $term
	 *
	 *    cn_term_id_filter
	 *        Passes: (int) $term_id, (int) $taxonomy_term_id
	 *        Return: $term_id
	 *
	 * @global wpdb  $wpdb            The WordPress database object.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @param string $term            The term to add or update.
	 * @param string $taxonomy        The taxonomy to which to add the term
	 * @param array  $args            {
	 *    Optional. Arguments to change values of the inserted term.
	 *
	 *    @type string 'alias_of'        Slug of the term to make this term an alias of.
	 *                                   Default: empty string.
	 *                                   Accepts a term slug.
	 *    @type string 'description'     The term description.
	 *                                   Default: empty string.
	 *    @type int    'parent'          The id of the parent term.
	 *                                   Default: 0.
	 *    @type string 'slug'            The term slug to use.
	 *                                   Default: empty string.
	 * }
	 *
	 * @return array|WP_Error         An array containing the term_id and term_taxonomy_id, WP_Error otherwise.
	 */
	public static function insert( $term, $taxonomy, $args = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		// @todo Implement taxonomy check.
		//if ( ! taxonomy_exists($taxonomy) ) {
		//	return new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));
		//}
		/**
		 * Filter a term before it is sanitized and inserted into the database.
		 *
		 * @since 8.1.6
		 *
		 * @param string $term     The term to add or update.
		 * @param string $taxonomy Taxonomy slug.
		 */
		$term = apply_filters( 'cn_pre_insert_term', $term, $taxonomy );

		if ( is_wp_error( $term ) ) {

			return $term;
		}

		if ( is_int( $term ) && 0 == $term ) {

			return new WP_Error( 'invalid_term_id', __( 'Invalid term ID', 'connections' ) );
		}

		if ( '' == trim( $term ) ) {

			return new WP_Error( 'empty_term_name', __( 'A name is required for this term', 'connections' ) );
		}

		$defaults = array(
			'alias_of'    => '',
			'description' => '',
			'parent'      => 0,
			'slug'        => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['parent'] > 0 && ! self::exists( (int) $args['parent'] ) ) {

			return new WP_Error( 'missing_parent', __( 'Parent term does not exist.', 'connections' ) );
		}

		$args['name']     = $term;
		$args['taxonomy'] = $taxonomy;
		$args             = sanitize_term( $args, 'cn_' . $taxonomy, 'db' );

		// expected_slashed ($name)
		$name        = wp_unslash( $args['name'] );
		$description = wp_unslash( $args['description'] );
		$parent      = (int) $args['parent'];

		$slug_provided = ! empty( $args['slug'] );

		if ( ! $slug_provided ) {

			$_name         = trim( $name );
			$existing_term = self::getBy( 'name', $_name, $taxonomy );

			if ( $existing_term ) {

				$slug = $existing_term->slug;

			} else {

				$slug = sanitize_title( $name );
			}

		} else {

			$slug = $args['slug'];
		}

		$term_group = 0;

		if ( $args['alias_of'] ) {

			$alias = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT term_id, term_group FROM " . CN_TERMS_TABLE . " WHERE slug = %s",
					$args['alias_of']
				)
			);

			if ( $alias->term_group ) {

				// The alias we want is already in a group, so let's use that one.
				$term_group = $alias->term_group;

			} else {

				// The alias isn't in a group, so let's create a new one and firstly add the alias term to it.
				$term_group = $wpdb->get_var( "SELECT MAX(term_group) FROM " . CN_TERMS_TABLE ) + 1;

				/**
				 * Fires immediately before the given terms are edited.
				 *
				 * @since 8.1.6
				 *
				 * @param int    $term_id  Term ID.
				 * @param string $taxonomy Taxonomy slug.
				 */
				do_action( 'cn_edit_terms', $alias->term_id, $taxonomy );

				$wpdb->update( CN_TERMS_TABLE, compact( 'term_group' ), array( 'term_id' => $alias->term_id ) );

				/**
				 * Fires immediately after the given terms are edited.
				 *
				 * @since 8.1.6
				 *
				 * @param int    $term_id  Term ID
				 * @param string $taxonomy Taxonomy slug.
				 */
				do_action( 'cn_edited_terms', $alias->term_id, $taxonomy );
			}
		}

		if ( $term_id = self::exists( $slug ) ) {

			$existing_term = $wpdb->get_row(
				$wpdb->prepare( "SELECT name FROM " . CN_TERMS_TABLE . " WHERE term_id = %d", $term_id ),
				ARRAY_A
			);

			// We've got an existing term in the same taxonomy, which matches the name of the new term:
			if ( /*is_taxonomy_hierarchical($taxonomy) &&*/
				$existing_term['name'] == $name && $exists = self::exists( (int) $term_id, $taxonomy )
			) {

				// Hierarchical, and it matches an existing term, Do not allow same "name" in the same level.
				$siblings = self::getTaxonomyTerms(
					$taxonomy,
					array( 'fields' => 'names', 'get' => 'all', 'parent' => $parent )
				);

				if ( in_array( $name, $siblings ) ) {

					if ( $slug_provided ) {
						return new WP_Error(
							'term_exists',
							__( 'A term with the name and slug provided already exists with this parent.', 'connections' ),
							$exists['term_id']
						);

					} else {

						return new WP_Error(
							'term_exists',
							__( 'A term with the name provided already exists with this parent.', 'connections' ),
							$exists['term_id']
						);
					}

				} else {

					$slug = self::unique_slug( $slug, (object) $args );

					if ( FALSE === $wpdb->insert( CN_TERMS_TABLE, compact( 'name', 'slug', 'term_group' ) ) ) {

						return new WP_Error(
							'db_insert_error',
							__( 'Could not insert term into the database', 'connections' ),
							$wpdb->last_error
						);
					}

					$term_id = (int) $wpdb->insert_id;
				}

			} elseif ( $existing_term['name'] != $name ) {

				// We've got an existing term, with a different name, Create the new term.
				$slug = self::unique_slug( $slug, (object) $args );

				if ( FALSE === $wpdb->insert( CN_TERMS_TABLE, compact( 'name', 'slug', 'term_group' ) ) ) {

					return new WP_Error(
						'db_insert_error',
						__( 'Could not insert term into the database', 'connections' ),
						$wpdb->last_error
					);
				}

				$term_id = (int) $wpdb->insert_id;

			} elseif ( $exists = self::exists( (int) $term_id, $taxonomy ) ) {

				// Same name, same slug.
				return new WP_Error(
					'term_exists',
					__( 'A term with the name and slug provided already exists.', 'connections' ),
					$exists['term_id']
				);

			}

		} else {

			// This term does not exist at all in the database, Create it.
			$slug = self::unique_slug( $slug, (object) $args );

			if ( FALSE === $wpdb->insert( CN_TERMS_TABLE, compact( 'name', 'slug', 'term_group' ) ) ) {

				return new WP_Error(
					'db_insert_error',
					__( 'Could not insert term into the database', 'connections' ),
					$wpdb->last_error
				);

			}

			$term_id = (int) $wpdb->insert_id;
		}

		// Seems unreachable, However, Is used in the case that a term name is provided, which sanitizes to an empty string.
		if ( empty( $slug ) ) {

			$slug = sanitize_title( $slug, $term_id );

			/** @see cnTerm::insert() */
			do_action( 'cn_edit_terms', $term_id, $taxonomy );
			$wpdb->update( CN_TERMS_TABLE, compact( 'slug' ), compact( 'term_id' ) );

			/** @see cnTerm::insert() */
			do_action( 'cn_edited_terms', $term_id, $taxonomy );
		}

		$tt_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT tt.term_taxonomy_id FROM " . CN_TERM_TAXONOMY_TABLE . " AS tt INNER JOIN " . CN_TERMS_TABLE . " AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id = %d",
				$taxonomy,
				$term_id
			)
		);

		if ( ! empty( $tt_id ) ) {

			return array( 'term_id' => $term_id, 'term_taxonomy_id' => $tt_id );
		}

		$wpdb->insert(
			CN_TERM_TAXONOMY_TABLE,
			compact( 'term_id', 'taxonomy', 'description', 'parent' ) + array( 'count' => 0 )
		);

		$tt_id = (int) $wpdb->insert_id;

		/**
		 * Fires immediately after a new term is created, before the term cache is cleaned.
		 *
		 * @since 8.6.1
		 *
		 * @param int    $term_id  Term ID.
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		do_action( "cn_create_term", $term_id, $tt_id, $taxonomy );

		/**
		 * Fires after a new term is created for a specific taxonomy.
		 *
		 * The dynamic portion of the hook name, $taxonomy, refers
		 * to the slug of the taxonomy the term was created for.
		 *
		 * @since 8.6.1
		 *
		 * @param int $term_id Term ID.
		 * @param int $tt_id   Term taxonomy ID.
		 */
		do_action( "cn_create_$taxonomy", $term_id, $tt_id );

		/**
		 * Filter the term ID after a new term is created.
		 *
		 * @since 8.6.1
		 *
		 * @param int $term_id Term ID.
		 * @param int $tt_id   Taxonomy term ID.
		 */
		$term_id = apply_filters( 'cn_term_id_filter', $term_id, $tt_id );

		self::cleanCache( $term_id, $taxonomy );

		/**
		 * Fires after a new term is created, and after the term cache has been cleaned.
		 *
		 * @since 8.6.1
		 */
		do_action( "cn_created_term", $term_id, $tt_id, $taxonomy );

		/**
		 * Fires after a new term in a specific taxonomy is created, and after the term
		 * cache has been cleaned.
		 *
		 * @since 8.6.1
		 *
		 * @param int $term_id Term ID.
		 * @param int $tt_id   Term taxonomy ID.
		 */
		do_action( "cn_created_$taxonomy", $term_id, $tt_id );

		return array( 'term_id' => $term_id, 'term_taxonomy_id' => $tt_id );
	}

	/**
	 * Update term based on arguments provided.
	 *
	 * The $args will indiscriminately override all values with the same field name.
	 * Care must be taken to not override important information needed to update or the
	 * update will fail (or perhaps create a new term, neither would be acceptable).
	 *
	 * Defaults will set 'alias_of', 'description', 'parent', and 'slug' if not
	 * defined in $args already.
	 *
	 * 'alias_of' will create a term group, if it does not already exist, and update
	 * it for the $term.
	 *
	 * If the 'slug' argument in $args is missing, then the 'name' in $args will be
	 * used. It should also be noted that if you set 'slug' and it isn't unique then
	 * a WP_Error will be passed back. If you don't pass any slug, then a unique one
	 * will be created for you.
	 *
	 * For what can be overrode in $args, check the term scheme can contain and stay
	 * away from the term keys.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_update_term() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * Actions:
	 *    cn_edit_terms
	 *        Passes: (int) $term_id, (string) $taxonomy
	 *
	 *    cn_edited_terms
	 *        Passes: (int) $term_id, (string) $taxonomy
	 *
	 *    cn_edit_term_taxonomy
	 *        Passes: (int) $term_taxonomy_id, (string) $taxonomy
	 *
	 *    cn_edited_term_taxonomy
	 *        Passes: (int) $term_taxonomy_id, (string) $taxonomy
	 *
	 *    cn_edit_term
	 *        Passes: (int) $term_id, (int) $taxonomy_term_id, (string) $taxonomy
	 *
	 *    cn_edit_$taxonomy
	 *        Passes: (int) $taxonomy_term_id, (string) $taxonomy
	 *
	 *    cn_edited_term
	 *        Passes: (int) $term_id, (int) $taxonomy_term_id, (string) $taxonomy
	 *
	 *    cn_edited_$taxonomy
	 *        Passes: (int) $taxonomy_term_id, (string) $taxonomy
	 *
	 * Filters:
	 *
	 *    cn_update_term_parent
	 *        Passes: (int) $parent_term_id, (int) $term_id, (string) $taxonomy, (array) $parsed_args, (array) $args
	 *        Returns: $parent_term_id
	 *
	 *    cn_term_id_filter
	 *        Passes: (int) $term_id, (int) $taxonomy_term_id
	 *        Return: $term_id
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global       $wpdb
	 *
	 * @param int    $term_id  The ID of the term
	 * @param string $taxonomy The context in which to relate the term to the object.
	 * @param array  $args     {
	 *    Optional. Overwrite term field values.
	 *
	 *    @type string 'alias_of'        Slug of the term to make this term an alias of.
	 *                                   Default: empty string.
	 *                                   Accepts a term slug.
	 *    @type string 'description'     The term description.
	 *                                   Default: empty string.
	 *    @type int    'parent'          The id of the parent term.
	 *                                   Default: 0.
	 *    @type string 'slug'            The term slug to use.
	 *                                   Default: empty string.
	 * }
	 *
	 * @return array|WP_Error Returns Term ID and Taxonomy Term ID or an instance of the WP_Error object.
	 */
	public static function update( $term_id, $taxonomy, $args = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		//@todo Add taxonomy check.
		//if ( ! taxonomy_exists($taxonomy) )
		//	return new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));

		$term_id = (int) $term_id;

		// First, get all of the original args
		$term = self::get( $term_id, $taxonomy, ARRAY_A );

		if ( is_wp_error( $term ) ) {

			return $term;
		}

		// Escape data pulled from DB.
		$term = wp_slash( $term );

		// Merge old and new args with new args overwriting old ones.
		$args = array_merge( $term, $args );

		$defaults    = array( 'alias_of' => '', 'description' => '', 'parent' => 0, 'slug' => '' );
		$args        = wp_parse_args( $args, $defaults );
		$args        = sanitize_term( $args, 'cn_' . $taxonomy, 'db' );
		$parsed_args = $args;

		// expected_slashed ($name)
		$name        = wp_unslash( $args['name'] );
		$description = wp_unslash( $args['description'] );

		$parsed_args['name']        = $name;
		$parsed_args['description'] = $description;

		if ( '' == trim( $name ) ) {

			return new WP_Error( 'empty_term_name', __( 'A name is required for this term', 'connections' ) );
		}

		$empty_slug = FALSE;

		if ( empty( $args['slug'] ) ) {

			$empty_slug = TRUE;
			$slug       = sanitize_title( $name );

		} else {

			$slug = $args['slug'];
		}

		$parsed_args['slug'] = $slug;

		$term_group = isset( $parsed_args['term_group'] ) ? $parsed_args['term_group'] : 0;

		if ( $args['alias_of'] ) {

			$alias = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT term_id, term_group FROM " . CN_TERMS_TABLE . " WHERE slug = %s",
					$args['alias_of']
				)
			);

			if ( $alias->term_group ) {

				// The alias we want is already in a group, so let's use that one.
				$term_group = $alias->term_group;

			} else {

				// The alias isn't in a group, so let's create a new one and firstly add the alias term to it.
				$term_group = $wpdb->get_var( "SELECT MAX(term_group) FROM " . CN_TERMS_TABLE ) + 1;

				/** @see cnTerm::insert() */
				do_action( 'cn_edit_terms', $alias->term_id, $taxonomy );
				$wpdb->update( CN_TERMS_TABLE, compact( 'term_group' ), array( 'term_id' => $alias->term_id ) );

				/** @see cnTerm::insert() */
				do_action( 'cn_edited_terms', $alias->term_id, $taxonomy );
			}

			$parsed_args['term_group'] = $term_group;
		}

		/**
		 * Filter the term parent.
		 *
		 * Hook to this filter to see if it will cause a hierarchy loop.
		 *
		 * @since 8.1.6
		 *
		 * @param int    $parent      ID of the parent term.
		 * @param int    $term_id     Term ID.
		 * @param string $taxonomy    Taxonomy slug.
		 * @param array  $parsed_args An array of potentially altered update arguments for the given term.
		 * @param array  $args        An array of update arguments for the given term.
		 */
		$parent = apply_filters( 'cn_update_term_parent', $args['parent'], $term_id, $taxonomy, $parsed_args, $args );

		// Check for duplicate slug
		$duplicate = self::getBy( 'slug', $slug, $taxonomy );

		if ( $duplicate && $duplicate->term_id != $term_id ) {

			// If an empty slug was passed or the parent changed, reset the slug to something unique.
			// Otherwise, bail.
			if ( $empty_slug || ( $parent != $term['parent'] ) ) {

				$slug = self::unique_slug( $slug, (object) $args );

			} else {

				return new WP_Error(
					'duplicate_term_slug',
					sprintf(
						__( 'The slug &#8220;%s&#8221; is already in use by another term', 'connections' ),
						$slug
					)
				);
			}
		}

		/** @see cnTerm::insert() */
		do_action( 'cn_edit_terms', $term_id, $taxonomy );

		$wpdb->update( CN_TERMS_TABLE, compact( 'name', 'slug', 'term_group' ), compact( 'term_id' ) );

		if ( empty( $slug ) ) {

			$slug = sanitize_title( $name, $term_id );
			$wpdb->update( CN_TERMS_TABLE, compact( 'slug' ), compact( 'term_id' ) );
		}

		/** @see cnTerm::insert() */
		do_action( 'cn_edited_terms', $term_id, $taxonomy );

		$tt_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT tt.term_taxonomy_id FROM " . CN_TERM_TAXONOMY_TABLE . " AS tt INNER JOIN " . CN_TERMS_TABLE . " AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id = %d",
				$taxonomy,
				$term_id
			)
		);

		/**
		 * Fires immediate before a term-taxonomy relationship is updated.
		 *
		 * @since 8.1.6
		 *
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		do_action( 'cn_edit_term_taxonomy', $tt_id, $taxonomy );

		$wpdb->update(
			CN_TERM_TAXONOMY_TABLE,
			compact( 'term_id', 'taxonomy', 'description', 'parent' ),
			array( 'term_taxonomy_id' => $tt_id )
		);

		/**
		 * Fires immediately after a term-taxonomy relationship is updated.
		 *
		 * @since 8.1.6
		 *
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		do_action( 'cn_edited_term_taxonomy', $tt_id, $taxonomy );

		// Clean the relationship caches for all object types using this term
		$objects = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT entry_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d",
				$tt_id
			)
		);

		//@todo implement the following block of code.
		//$tax_object = get_taxonomy( $taxonomy );
		//foreach ( $tax_object->object_type as $object_type ) {
		//	self::cleanRelationshipCache( $objects, $object_type );
			self::cleanRelationshipCache( $objects, $taxonomy ); // Clean the entry/term relationships directly until get_taxonomy() is implemented.
		//}

		/**
		 * Fires after a term has been updated, but before the term cache has been cleaned.
		 *
		 * @since 8.1.6
		 *
		 * @param int    $term_id  Term ID.
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		do_action( "cn_edit_term", $term_id, $tt_id, $taxonomy );

		/**
		 * Fires after a term in a specific taxonomy has been updated, but before the term
		 * cache has been cleaned.
		 *
		 * The dynamic portion of the hook name, $taxonomy, refers to the taxonomy slug.
		 *
		 * @since 8.1.6
		 *
		 * @param int $term_id Term ID.
		 * @param int $tt_id   Term taxonomy ID.
		 */
		do_action( "cn_edit_$taxonomy", $term_id, $tt_id );

		/** @see cnTerm::insert() */
		$term_id = apply_filters( 'cn_term_id_filter', $term_id, $tt_id );

		self::cleanCache( $term_id, $taxonomy );

		/**
		 * Fires after a term has been updated, and the term cache has been cleaned.
		 *
		 * @since 8.1.6
		 *
		 * @param int    $term_id  Term ID.
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		do_action( "cn_edited_term", $term_id, $tt_id, $taxonomy );

		/**
		 * Fires after a term for a specific taxonomy has been updated, and the term
		 * cache has been cleaned.
		 *
		 * The dynamic portion of the hook name, $taxonomy, refers to the taxonomy slug.
		 *
		 * @since 8.1.6
		 *
		 * @param int $term_id Term ID.
		 * @param int $tt_id   Term taxonomy ID.
		 */
		do_action( "cn_edited_$taxonomy", $term_id, $tt_id );

		return array( 'term_id' => $term_id, 'term_taxonomy_id' => $tt_id );
	}

	/**
	 * Removes a term from the database.
	 *
	 * If the term is a parent of other terms, then the children will be updated to
	 * that term's parent.
	 *
	 * The $args 'default' will only override the terms found, if there is only one
	 * term found. Any other and the found terms are used.
	 *
	 * The $args 'force_default' will force the term supplied as default to be
	 * assigned even if the object was not going to be termless
	 *
	 * NOTE: This is the Connections equivalent of @see wp_delete_term() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   cnTerm::exists()
	 * @uses   is_wp_error()
	 * @uses   cnTerm::getBy()
	 * @uses   wp_parse_args()
	 * @uses   cnTerm::get()
	 * @uses   wpdb::get_col()
	 * @uses   wpdb::update()
	 * @uses   do_action()
	 * @uses   wpdb::prepare()
	 * @uses   cnTerm::getRelationships()
	 * @uses   cnTerm::setRelationships()
	 * @uses   wpdb::delete()
	 * @uses   wpdb::get_var()
	 * @uses   cnTerm::cleanCache()
	 *
	 * @param int          $term     Term ID
	 * @param string       $taxonomy Taxonomy Name
	 * @param array|string $args     Optional. Change 'default' term id and override found term ids.
	 *
	 * @return bool|WP_Error Returns false if not term; true if completes delete action.
	 */
	public static function delete( $term, $taxonomy, $args = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$term = (int) $term;

		if ( ! $ids = self::exists( $term, $taxonomy ) ) {

			return FALSE;
		}

		if ( is_wp_error( $ids ) ) {

			return $ids;
		}

		$tt_id = $ids['term_taxonomy_id'];

		$defaults = array();

		if ( 'category' == $taxonomy ) {

			$defaults['default'] = get_option( 'cn_default_category' );

			// Don't delete the default category
			if ( $defaults['default'] == $term ) {

				return FALSE;
			}

		}

		$args = wp_parse_args( $args, $defaults );

		if ( isset( $args['default'] ) ) {

			$default = (int) $args['default'];

			if ( ! self::exists( $default, $taxonomy ) ) {

				unset( $default );
			}
		}

		if ( isset( $args['force_default'] ) ) {

			$force_default = $args['force_default'];
		}

		//@todo Implement the is_taxonomy_hierarchical() conditional statement.
		// Update children to point to new parent
		//if ( is_taxonomy_hierarchical($taxonomy) ) {
		if ( TRUE ) { //temp hack...

			$term_obj = self::get( $term, $taxonomy );

			if ( is_wp_error( $term_obj ) ) {

				return $term_obj;
			}

			$parent = $term_obj->parent;

			$edit_tt_ids = $wpdb->get_col(
				"SELECT term_taxonomy_id FROM " . CN_TERM_TAXONOMY_TABLE . " WHERE parent = " . (int) $term_obj->term_id
			);

			/**
			 * Fires immediately before a term to delete's children are reassigned a parent.
			 *
			 * @since 8.1.6
			 *
			 * @param array $edit_tt_ids An array of term taxonomy IDs for the given term.
			 */
			do_action( 'cn_edit_term_taxonomies', $edit_tt_ids );

			$wpdb->update(
				CN_TERM_TAXONOMY_TABLE,
				compact( 'parent' ),
				array( 'parent' => $term_obj->term_id ) + compact( 'taxonomy' )
			);

			/**
			 * Fires immediately after a term to delete's children are reassigned a parent.
			 *
			 * @since 8.1.6
			 *
			 * @param array $edit_tt_ids An array of term taxonomy IDs for the given term.
			 */
			do_action( 'cn_edited_term_taxonomies', $edit_tt_ids );
		}

		$objects = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT entry_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d",
				$tt_id
			)
		);

		foreach ( (array) $objects as $object ) {

			$terms = self::getRelationships( $object, $taxonomy, array( 'fields' => 'ids', 'orderby' => 'none' ) );

			if ( 1 == count( $terms ) && isset( $default ) ) {

				$terms = array( $default );

			} else {

				$terms = array_diff( $terms, array( $term ) );

				if ( isset( $default ) && isset( $force_default ) && $force_default ) {

					$terms = array_merge( $terms, array( $default ) );
				}

			}

			$terms = array_map( 'intval', $terms );

			self::setRelationships( $object, $terms, $taxonomy );
		}

		// Clean the relationship caches for all object types using this term
		//@todo Implement the following block of code.
		//$tax_object = get_taxonomy( $taxonomy );
		//foreach ( $tax_object->object_type as $object_type )
		//	self::cleanRelationshipCache( $objects, $object_type );
			self::cleanRelationshipCache( $objects, $taxonomy ); // Clean the entry/term relationships directly until get_taxonomy() is implemented.

		// Get the object before deletion so we can pass to actions below
		$deleted_term = self::get( $term, $taxonomy );

		/**
		 * Fires immediately before a term taxonomy ID is deleted.
		 *
		 * @since 8.1.6
		 *
		 * @param int $tt_id Term taxonomy ID.
		 */
		do_action( 'cn_delete_term_taxonomy', $tt_id );

		$wpdb->delete( CN_TERM_TAXONOMY_TABLE, array( 'term_taxonomy_id' => $tt_id ) );

		/**
		 * Fires immediately after a term taxonomy ID is deleted.
		 *
		 * @since 8.1.6
		 *
		 * @param int $tt_id Term taxonomy ID.
		 */
		do_action( 'cn_deleted_term_taxonomy', $tt_id );

		// Delete the term if no taxonomies use it.
		if ( ! $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_TAXONOMY_TABLE . " WHERE term_id = %d", $term )
		)
		) {
			$wpdb->delete( CN_TERMS_TABLE, array( 'term_id' => $term ) );
		}

		self::cleanCache( $term, $taxonomy );

		/**
		 * Fires after a term is deleted from the database and the cache is cleaned.
		 *
		 * @since 8.1.6
		 *
		 * @param int    $term          Term ID.
		 * @param int    $tt_id         Term taxonomy ID.
		 * @param string $taxonomy      Taxonomy slug.
		 * @param mixed  $deleted_term  Copy of the already-deleted term, in the form specified
		 *                              by the parent function. WP_Error otherwise.
		 */
		do_action( 'cn_delete_term', $term, $tt_id, $taxonomy, $deleted_term );

		/**
		 * Fires after a term in a specific taxonomy is deleted.
		 *
		 * The dynamic portion of the hook name, $taxonomy, refers to the specific
		 * taxonomy the term belonged to.
		 *
		 * @since 8.1.6
		 *
		 * @param int   $term           Term ID.
		 * @param int   $tt_id          Term taxonomy ID.
		 * @param mixed $deleted_term   Copy of the already-deleted term, in the form specified
		 *                              by the parent function. WP_Error otherwise.
		 */
		// @todo Re-implement the action, currently it conflicts with the `cn_delete_category` action in:
		// ../includes/admin/class.actions.php
		//do_action( "cn_delete_$taxonomy", $term, $tt_id, $deleted_term );

		return TRUE;
	}

	/**
	 * Will make slug unique, if it isn't already.
	 *
	 * The $slug has to be unique global to every taxonomy, meaning that one
	 * taxonomy term can't have a matching slug with another taxonomy term. Each
	 * slug has to be globally unique for every taxonomy.
	 *
	 * The way this works is that if the taxonomy that the term belongs to is
	 * hierarchical and has a parent, it will append that parent to the $slug.
	 *
	 * If that still does not return an unique slug, then it try to append a number
	 * until it finds a number that is truly unique.
	 *
	 * The only purpose for $term is for appending a parent, if one exists.
	 *
	 * NOTE: This is the Connections equivalent of @see wp_unique_term_slug() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access private
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   cnTerm::exists()
	 * @uses   cnTerm::get()
	 * @uses   is_wp_error()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_var()
	 *
	 * @param string $slug The string that will be tried for a unique slug
	 * @param object $term The term object that the $slug will belong too
	 *
	 * @return string Will return a true unique slug.
	 */
	private static function unique_slug( $slug, $term ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( ! self::exists( $slug ) ) {

			return $slug;
		}

		// If the taxonomy supports hierarchy and the term has a parent, make the slug unique
		// by incorporating parent slugs.
		if ( /*is_taxonomy_hierarchical($term->taxonomy) &&*/ ! empty( $term->parent ) ) {

			$the_parent = $term->parent;

			while ( ! empty( $the_parent ) ) {

				$parent_term = self::get( $the_parent, $term->taxonomy );

				if ( is_wp_error( $parent_term ) || empty( $parent_term ) ) {

					break;
				}

				$slug .= '-' . $parent_term->slug;

				if ( ! self::exists( $slug ) ) {

					return $slug;
				}

				if ( empty( $parent_term->parent ) ) {

					break;
				}
				$the_parent = $parent_term->parent;
			}

		}

		// If we didn't get a unique slug, try appending a number to make it unique.
		if ( ! empty( $term->term_id ) ) {

			$query = $wpdb->prepare(
				"SELECT slug FROM " . CN_TERMS_TABLE . " WHERE slug = %s AND term_id != %d",
				$slug,
				$term->term_id
			);

		} else {

			$query = $wpdb->prepare( "SELECT slug FROM " . CN_TERMS_TABLE . " WHERE slug = %s", $slug );
		}

		if ( $wpdb->get_var( $query ) ) {

			$num = 2;

			do {

				$alt_slug = $slug . "-$num";
				$num ++;

				$slug_check = $wpdb->get_var(
					$wpdb->prepare( "SELECT slug FROM " . CN_TERMS_TABLE . " WHERE slug = %s", $alt_slug )
				);

			} while ( $slug_check );

			$slug = $alt_slug;
		}

		return $slug;
	}

	/**
	 * Will remove all of the term ids from the cache.
	 *
	 * NOTE: This is the Connections equivalent of @see clean_term_cache() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   wpdb::get_results()
	 * @uses   wp_cache_delete()
	 * @uses   delete_option()
	 * @uses   cnTerm::get_hierarchy()
	 * @uses   do_action()
	 * @uses   wp_cache_set()
	 *
	 * @param int|array $ids            Single or list of Term IDs
	 * @param string    $taxonomy       Can be empty and will assume tt_ids, else will use for context.
	 * @param bool      $clean_taxonomy Whether to clean taxonomy wide caches (true), or just individual term object caches (false). Default is true.
	 */
	public static function cleanCache( $ids, $taxonomy = '', $clean_taxonomy = TRUE ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( ! is_array( $ids ) ) {

			$ids = array( $ids );
		}

		$taxonomies = array();

		// If no taxonomy, assume tt_ids.
		if ( empty( $taxonomy ) ) {

			$tt_ids = array_map( 'intval', $ids );
			$tt_ids = implode( ', ', $tt_ids );

			$terms  = $wpdb->get_results(
				"SELECT term_id, taxonomy FROM " . CN_TERM_TAXONOMY_TABLE . " WHERE term_taxonomy_id IN ($tt_ids)"
			);

			$ids    = array();

			foreach ( (array) $terms as $term ) {

				$taxonomies[] = $term->taxonomy;
				$ids[]        = $term->term_id;
				wp_cache_delete( $term->term_id, 'cn_' . $term->taxonomy );
			}

			$taxonomies = array_unique( $taxonomies );

		} else {

			$taxonomies = array( $taxonomy );

			foreach ( $taxonomies as $taxonomy ) {

				foreach ( $ids as $id ) {

					wp_cache_delete( $id, 'cn_' . $taxonomy );
				}
			}
		}

		foreach ( $taxonomies as $taxonomy ) {

			if ( $clean_taxonomy ) {

				wp_cache_delete( 'all_ids', 'cn_' . $taxonomy );
				wp_cache_delete( 'get', 'cn_' . $taxonomy );
				delete_option( "cn_{$taxonomy}_children" );

				// Regenerate {$taxonomy}_children
				self::get_hierarchy( $taxonomy );
			}

			/**
			 * Fires once after each taxonomy's term cache has been cleaned.
			 *
			 * @since 8.1.6
			 *
			 * @param array  $ids      An array of term IDs.
			 * @param string $taxonomy Taxonomy slug.
			 */
			do_action( 'cn_clean_term_cache', $ids, $taxonomy );
		}

		wp_cache_set( 'last_changed', microtime(), 'cn_terms' );

		// Clear any transients/cache fragments that were set.
		cnCache::clear( TRUE, 'transient', "cn_{$taxonomy}" );
	}

	/**
	 * Removes the taxonomy relationship to terms from the cache.
	 *
	 * Will remove the entire taxonomy relationship containing term $object_id. The
	 * term IDs have to exist within the taxonomy $object_type for the deletion to
	 * take place.
	 *
	 * NOTE: This is the Connections equivalent of @see get_object_taxonomies() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @param int|array    $object_ids  Single or list of term object ID(s)
	 * @param array|string $object_type The taxonomy object type
	 */
	public static function cleanRelationshipCache( $object_ids, $object_type ) {

		if ( ! is_array( $object_ids ) ) {

			$object_ids = array( $object_ids );
		}

		//$taxonomies = get_object_taxonomies( $object_type );
		if ( ! is_array( $object_type ) ) {

			$taxonomies = array( $object_type );

		} else {

			$taxonomies = $object_type;
		}

		foreach ( $object_ids as $id ) {

			foreach ( $taxonomies as $taxonomy ) {

				wp_cache_delete( $id, "cn_{$taxonomy}_relationships" );
			}
		}

		/**
		 * Fires after the object term cache has been cleaned.
		 *
		 * @since 8.2
		 *
		 * @param array  $object_ids An array of object IDs.
		 * @param string $object_type Object type.
		 */
		do_action( 'cn_clean_object_term_cache', $object_ids, $object_type );
	}

	/**
	 * Retrieves children of taxonomy as Term IDs.
	 *
	 * Stores all of the children in "cn_{$taxonomy}_children" option.
	 * That is the prefix "cn_", the name of the taxonomy, followed by '_children' suffix.
	 *
	 * NOTE: This is the Connections equivalent of @see _get_term_hierarchy() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access private
	 * @since  8.1.6
	 * @static
	 *
	 * @uses   update_option()
	 * @uses   cnTerm::getTaxonomyTerms()
	 *
	 * @param  string $taxonomy Taxonomy Name.
	 *
	 * @return array Empty if $taxonomy isn't hierarchical or returns children as Term IDs.
	 */
	public static function get_hierarchy( $taxonomy ) {

		// Implement taxonomy check.
		//if ( !is_taxonomy_hierarchical($taxonomy) )
		//	return array();

		$children = get_option( "cn_{$taxonomy}_children" );

		if ( is_array( $children ) ) {

			return $children;
		}

		$children = array();

		$terms    = self::getTaxonomyTerms(
			$taxonomy,
			array( 'get' => 'all', 'orderby' => 'id', 'fields' => 'id=>parent' )
		);

		foreach ( $terms as $term_id => $parent ) {

			if ( $parent > 0 ) {

				$children[ $parent ][] = $term_id;
			}
		}

		update_option( "cn_{$taxonomy}_children", $children );

		return $children;
	}

	/**
	 * Query term data from database by term ID.
	 *
	 * Filters:
	 *    cn_term
	 *        Passes: ( id | object ) $term, (string) $taxonomy
	 *        Return: $term
	 *
	 *    cn_$taxonomy - $taxonomy will be the taxonomy name
	 *        Passes: ( id | object ) $term, (string) $taxonomy
	 *        Return: $term
	 *
	 * The usage of the filter method is to apply filters to a term object. It
	 * is possible to get a term object from the database before applying the
	 * filters.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @global $wpdb
	 * @uses   wpdb::get_row()
	 * @uses   wpdb::prepare()
	 * @uses   sanitize_term()
	 * @uses   apply_filters()
	 *
	 * @param  int|object $term     If integer, will query from database. If object will apply filters and return $term.
	 * @param  string     $taxonomy Taxonomy name that $term is part of.
	 * @param  string     $output   Constant OBJECT, ARRAY_A, or ARRAY_N
	 * @param  string     $filter   Optional, default is raw or no WordPress defined filter will applied.
	 *
	 * @return mixed|null|WP_Error  Term data. Will return null if $term is empty. If taxonomy does not exist then WP_Error will be returned.
	 */
	private static function filter( $term, $taxonomy, $output = OBJECT, $filter = 'raw' ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		 if ( empty( $term ) ) {

			return new WP_Error( 'invalid_term', __( 'Empty Term', 'connections' ) );
		 }

		// if ( ! taxonomy_exists( $taxonomy ) ) {

		//	return new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));
		//}

		if ( is_object( $term ) && empty( $term->filter ) ) {

			 wp_cache_add( $term->term_id, $term, 'cn_' . $taxonomy );
			$_term = $term;

		} else {

			if ( is_object( $term ) ) {

				$term = $term->term_id;
			}

			if ( ! $term = (int) $term ) {

				return NULL;
			}

			if ( ! $_term = wp_cache_get( $term, 'cn_' . $taxonomy ) ) {
			//if ( TRUE ) {

				$_term = $wpdb->get_row( $wpdb->prepare( 'SELECT t.*, tt.* FROM ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND t.term_id = %d LIMIT 1', $taxonomy, $term ) );

				if ( ! $_term ) {

					return NULL;
				}

				wp_cache_add( $term, $_term, 'cn_' . $taxonomy );
			}

		}

		/**
		 * Filter a term.
		 *
		 * @since 8.1
		 *
		 * @param int|object $_term    Term object or ID.
		 * @param string     $taxonomy The taxonomy slug.
		 */
		$_term = apply_filters( 'cn_term', $_term, $taxonomy );

		/**
		 * Filter a taxonomy.
		 *
		 * The dynamic portion of the filter name, $taxonomy, refers
		 * to the taxonomy slug.
		 *
		 * @since 8.1
		 *
		 * @param int|object $_term    Term object or ID.
		 * @param string     $taxonomy The taxonomy slug.
		 */
		$_term = apply_filters( "cn_$taxonomy", $_term, $taxonomy );
		$_term = sanitize_term( $_term, $taxonomy, $filter );

		if ( $output == OBJECT ) {

			return $_term;

		} elseif ( $output == ARRAY_A ) {

			$__term = get_object_vars( $_term );
			return $__term;

		} elseif ( $output == ARRAY_N ) {

			$__term = array_values( get_object_vars( $_term ) );
			return $__term;

		} else {

			return $_term;
		}

	}

	/**
	 * Retrieve the terms in a given taxonomy or list of taxonomies.
	 *
	 * NOTE: This is the Connections equivalent of @see get_terms() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * Filters:
	 *    cn_get_terms_atts - The method variables.
	 *        Passes: (array) $atts, (array) $taxonomies
	 *        Return: $atts
	 *
	 *    cn_get_terms_fields - The fields for the SELECT query clause.
	 *        Passes: (array) $select, (array) $atts, (array) $taxonomies
	 *        Return: $select
	 *
	 *    cn_term_inclusions - Query clause which includes terms.
	 *        Passes: (string) $inclusions, (array) $atts, (array) $taxonomies
	 *        Return: $inclusions
	 *
	 *    cn_term_exclusions - Query clause which excludes terms.
	 *        Passes: (string) $exclusions, (array) $atts, (array) $taxonomies
	 *        Return: $exclusions
	 *
	 *    cn_term_orderby - The ORDER BY query clause.
	 *        Passes: (string) $orderBy, (array) $atts, (array) $taxonomies
	 *        Return: $orderBy
	 *
	 *    cn_terms_clauses - An array containing the the query clause segments.
	 *        Passes: (array) $pieces, (array) $taxonomies, (array) $atts
	 *        Return: $pieces
	 *
	 * Accepted option for the $atts property are:
	 *
	 *    get ( string )
	 *        Default: ''
	 *        Valid:   all
	 *        If set to 'all' instead of its default empty string,
	 *        returns terms regardless of ancestry or whether the terms are empty.
	 *
	 *    fields ( string )
	 *        Default: 'all'
	 *        Valid:   all | ids | id=>parent | names | count | id=>name | id=>slug
	 *        Default is 'all', which returns an array of term objects.
	 *        If 'fields' is 'ids' or 'names', returns an array of integers or strings, respectively.
	 *
	 *    include ( string | array )
	 *        Default: array()
	 *        Valid:   An indexed array, comma- or space-delimited string of term_id.
	 *
	 *    exclude_tree ( string | array )
	 *        Default: array()
	 *        Valid:   An indexed array, comma- or space-delimited string of term_id.
	 *        If 'include' is non-empty, 'exclude_tree' is ignored.
	 *
	 *    exclude ( string | array )
	 *        Default: array()
	 *        Valid:   An indexed array, comma- or space-delimited string of term_id.
	 *        If 'include' is non-empty, 'exclude' is ignored.
	 *
	 *    slug ( string | array  )
	 *        Default: ''
	 *        Slug or array of slugs to return term(s) for.
	 *
	 *    hide_empty ( bool )
	 *        Default: TRUE
	 *        Will not return empty terms, which means terms whose count is 0.
	 *
	 *    hierarchical ( bool )
	 *        Default: TRUE
	 *        Whether to include terms that have non-empty descendants, even if 'hide_empty' is set to TRUE.
	 *
	 *    orderby ( string | array )
	 *        Default: name
	 *        Valid:   term_id | name | slug | term_group | parent | count | include
	 *
	 *    order ( string | array )
	 *        Default: ASC
	 *        Valid:   ASC | DESC
	 *
	 *    number ( int )
	 *        Default: 0
	 *        The maximum number of terms to return. Default is to return them all.
	 *
	 *    offset ( int )
	 *        Default: 0
	 *        The number by which to offset the terms query.
	 *
	 *    search ( string )
	 *        Default: ''
	 *        Returned terms' names will contain the value of 'search', case-insensitive.
	 *
	 *    name__like ( string )
	 *        Default: ''
	 *        Return terms' names will contain the value of 'name__like', case-insensitive.
	 *
	 *    description__like ( string )
	 *        Default: ''
	 *        Return terms' descriptions will contain the value of 'description__like', case-insensitive.
	 *
	 *    child_of ( int )
	 *        Default: 0
	 *        The 'child_of' argument, when used, should be set to the integer of a term ID.
	 *        If set to a non-zero value, all returned terms will be descendants
	 *        of that term according to the given taxonomy.
	 *        Hence 'child_of' is set to 0 if more than one taxonomy is passed in $taxonomies,
	 *        because multiple taxonomies make term ancestry ambiguous.
	 *
	 *    parent ( string | int )
	 *        Default: ''
	 *        The integer of a term ID.
	 *        If set to an integer value, all returned terms will have as an immediate
	 *        ancestor the term whose ID is specified by that integer according to the given taxonomy.
	 *        The 'parent' argument is different from 'child_of' in that a term X is considered a 'parent'
	 *        of term Y only if term X is the father of term Y, not its grandfather or great-grandfather, etc.
	 *
	 *    pad_counts ( bool )
	 *        Default: FALSE
	 *        If set to true, include the quantity of a term's children
	 *        in the quantity of each term's 'count' property.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @param  string|array $taxonomies Taxonomy name or array of taxonomy names.
	 * @param  array        $atts
	 *
	 * @uses   apply_filters()
	 * @uses   wp_parse_args()
	 * @uses   wp_parse_id_list()
	 * @uses   sanitize_title()
	 * @uses   wpdb::prepare()
	 * @uses   $wpdb::esc_like()
	 * @uses   absint()
	 * @uses   wpdb::get_results()
	 * @uses   cnTerm::filter()
	 * @uses   cnTerm::descendants()
	 * @uses   cnTerm::childrenIDs()
	 * @uses   cnTerm::padCounts()
	 * @uses   cnTerm::children()
	 *
	 * @return array|WP_Error Indexed array of term objects. Will return WP_Error, if any of $taxonomies do not exist.*
	 */
	public static function getTaxonomyTerms( $taxonomies = array( 'category' ), $atts = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$select        = array();
		$where         = array();
		$orderBy       = array();
		$orderByClause = '';

		/*
		 * @TODO $taxonomies need to be checked against registered taxonomies.
		 * Presently $taxonomies only support a string rather than array.
		 * Additionally, category is the only supported taxonomy.
		 */

		$single_taxonomy = ! is_array( $taxonomies ) || 1 === count( $taxonomies );

		if ( ! is_array( $taxonomies ) ) {

			$taxonomies = array( $taxonomies );
		}

		$defaults = array(
			'get'          => '',
			'orderby'      => 'name',
			'order'        => 'ASC',
			'hide_empty'   => TRUE,
			'exclude'      => array(),
			'exclude_tree' => array(),
			'include'      => array(),
			'fields'       => 'all',
			'slug'         => '',
			'parent'       => '',
			'hierarchical' => TRUE,
			'child_of'     => 0,
			'name__like'   => '',
			'meta_query'   => array(),
			'pad_counts'   => FALSE,
			'offset'       => 0,
			'number'       => 0,
			'search'       => '',
		);

		/**
		 * Filter the terms query arguments.
		 *
		 * @since 8.1
		 *
		 * @param array        $atts       An array of arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$atts = apply_filters( 'cn_get_terms_args', $atts, $taxonomies );

		$atts = wp_parse_args( $atts, $defaults );

		// @TODO Implement is_taxonomy_hierarchical().
		if ( ! $single_taxonomy ||
			 /*! is_taxonomy_hierarchical( reset( $taxonomies ) ) ||*/
			 ( '' !== $atts['parent'] && 0 !== $atts['parent'] )
			) {

			$atts['child_of']     = 0;
			$atts['hierarchical'] = FALSE;
			$atts['pad_counts']   = FALSE;
		}

		if ( 'all' == $atts['get'] ) {

			$atts['child_of']     = 0;
			$atts['hide_empty']   = 0;
			$atts['hierarchical'] = FALSE;
			$atts['pad_counts']   = FALSE;
		}

		if ( $atts['child_of'] ) {

			$hierarchy = self::childrenIDs( reset( $taxonomies ) );

			if ( ! isset( $hierarchy[ $atts['child_of'] ] ) ) {

				return array();
			}
		}

		if ( $atts['parent'] ) {

			$hierarchy = self::childrenIDs( reset( $taxonomies ) );

			if ( ! isset( $hierarchy[ $atts['parent'] ] ) ) {

				return array();
			}
		}

		// $args can be whatever, only use the args defined in defaults to compute the key
		$filter_key   = ( has_filter( 'cn_term_exclusions' ) ) ? serialize( $GLOBALS['wp_filter']['cn_term_exclusions'] ) : '';
		$key          = md5( serialize( wp_array_slice_assoc( $atts,
		                                                      array_keys( $defaults ) ) ) . serialize( $taxonomies ) . $filter_key );
		$last_changed = wp_cache_get( 'last_changed', 'cn_terms' );

		if ( ! $last_changed ) {

			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, 'cn_terms' );
		}

		$cache_key = "cn_get_terms:$key:$last_changed";
		$cache     = wp_cache_get( $cache_key, 'cn_terms' );

		if ( FALSE !== $cache ) {

			/**
			 * Filter the given taxonomy's terms cache.
			 *
			 * @since 8.1.6
			 *
			 * @param array        $cache      Cached array of terms for the given taxonomy.
			 * @param string|array $taxonomies A taxonomy or array of taxonomies.
			 * @param array        $args       An array of arguments to get terms.
			 */
			$cache = apply_filters( 'cn_terms', $cache, $taxonomies, $atts );

			return $cache;
		}

		/*
		 * Construct the ORDER By query clause.
		 */
		if ( is_array( $atts['orderby'] ) ) {

			foreach ( $atts['orderby'] as $i => $value ) {

				if ( ! isset ( $order ) ) $order = 'ASC';

				switch ( $value ) {

					case 'id':
					case 'term_id':
						$orderField = 't.term_id';
						break;

					case 'slug':
						$orderField = 't.slug';
						break;

					case 'include':
						$include = implode( ',', wp_parse_id_list( $atts['include'] ) );
						$orderField = "FIELD( t.term_id, $include )";
						break;

					case 'term_group':
						$orderField = 't.term_group';
						break;

					case 'none':
						$orderField = '';

						// If an `none` order field was supplied, break out of both the switch and foreach statements.
						break(2);

					case 'parent':
						$orderField = 'tt.parent';
						break;

					case 'count':
						$orderField = 'tt.count';
						break;

					default:

						$orderField = 't.name';
						break;
				}

				// Set the $order to align with $atts['orderby'].
				if ( is_array( $atts['order'] ) && isset( $atts['order'][ $i ] ) ) {

					$order = $atts['order'][ $i ];

				// If an aligned $atts['order'] does not exist use the last $order set otherwise use $atts['order'].
				} else {

					$order = is_array( $atts['order'] ) ? $order : $atts['order'];
				}

				$order = strtoupper( $order );

				$order = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';

				$orderBy[] = sprintf( '%s %s', $orderField, $order );

			}

			// The @var $value will be set to the last value from the $atts['orderby'] foreach loop.
			// If a `none` $atts['orderby'] was found in the supplied array, no order by clause will be set.
			if ( ! empty( $orderBy ) && $value != 'none' ) $orderByClause = 'ORDER BY ' . implode( ', ', $orderBy );

		} else {

			switch ( $atts['orderby'] ) {

				case 'id':
				case 'term_id':
					$atts['orderby'] = 't.term_id';
					break;

				case 'slug':
					$atts['orderby'] = 't.slug';
					break;

				case 'include':
					$include = implode( ',', wp_parse_id_list( $atts['include'] ) );
					$atts['orderby'] = "FIELD( t.term_id, $include )";
					break;

				case 'term_group':
					$atts['orderby'] = 't.term_group';
					break;

				case 'none':
					$atts['orderby'] = '';
					break;

				case 'parent':
					$atts['orderby'] = 'tt.parent';
					break;

				case 'count':
					$atts['orderby'] = 'tt.count';
					break;

				default:

					$atts['orderby'] = 't.name';
					break;
			}

			if ( is_array( $atts['order'] ) ) {

				// $atts['orderby'] was a string but an array was passed for $atts['order'], assume the 0 index.
				$order = $atts['order'][0];

			} else {

				$order = $atts['order'];
			}

			if ( ! empty( $atts['orderby'] ) ) {

				$order = strtoupper( $order );
				$order = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';

				$orderByClause = 'ORDER BY ' . sprintf( '%s %s', $atts['orderby'], $order );
			}

		}

		/*
		 * Filter the ORDER BY clause of the terms query.
		 *
		 * @since 8.1
		 *
		 * @param string       $orderBy    ORDER BY clause of the terms query.
		 * @param array        $atts       An array of terms query arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$orderBy = apply_filters( 'cn_terms_orderby', $orderByClause, $atts, $taxonomies );

		/*
		 * Start construct the WHERE query clause.
		 */
		$where[] = 'tt.taxonomy IN (\'' . implode( '\', \'', $taxonomies ) . '\')';

		/*
		 * Define the included terms.
		 */
		$inclusions = '';

		if ( ! empty( $atts['include'] ) ) {

			$atts['exclude']      = '';
			$atts['exclude_tree'] = '';

			$inclusions = implode( ',', wp_parse_id_list( $atts['include'] ) );
		}

		if ( ! empty( $inclusions ) ) {

			$inclusions = 'AND t.term_id IN ( ' . $inclusions . ' )';
		}

		/**
		 * Filter the terms to be included in the terms query.
		 *
		 * @since 8.1
		 *
		 * @param string       $inclusions IN clause of the terms query.
		 * @param array        $atts       An array of terms query arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$inclusions = apply_filters( 'cn_term_inclusions', $inclusions, $atts, $taxonomies );

		if ( ! empty( $inclusions ) ) {

			$where[] = $inclusions;
		}

		/*
		 * Define the excluded terms.
		 */
		$exclusions = '';

		if ( ! empty( $atts['exclude_tree'] ) ) {

			$atts['exclude_tree'] = wp_parse_id_list( $atts['exclude_tree'] );
			$excluded_children    = $atts['exclude_tree'];

			foreach ( $atts['exclude_tree'] as $extrunk ) {

				$excluded_children = array_merge(
					$excluded_children,
					(array) cnTerm::getTaxonomyTerms( $taxonomies[0], array( 'child_of' => intval( $extrunk ), 'fields' => 'ids', 'hide_empty' => 0 ) )
				);
			}

			$exclusions = implode( ',', array_map( 'intval', $excluded_children ) );
		}

		if ( ! empty( $atts['exclude'] ) ) {

			$exterms = wp_parse_id_list( $atts['exclude'] );

			if ( empty( $exclusions ) ) {

				$exclusions = implode( ',', $exterms );

			} else {

				$exclusions .= ', ' . implode( ',', $exterms );
			}
		}

		if ( ! empty( $exclusions ) ) {

			$exclusions = 'AND t.term_id NOT IN (' . $exclusions . ')';
		}

		/**
		 * Filter the terms to exclude from the terms query.
		 *
		 * @since 8.1
		 *
		 * @param string       $exclusions NOT IN clause of the terms query.
		 * @param array        $atts       An array of terms query arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$exclusions = apply_filters( 'cn_term_exclusions', $exclusions, $atts, $taxonomies );

		if ( ! empty( $exclusions ) ) {

			$where[] = $exclusions;
		}

		if ( ! empty( $atts['slug'] ) ) {

			if ( is_array( $atts['slug'] ) ) {

				$slug = array_map( 'sanitize_title', $atts['slug'] );
				$where[] = " AND t.slug IN ('" . implode( "', '", $slug ) . "')";

			} else {

				$slug = sanitize_title( $atts['slug'] );
				$where[] = " AND t.slug = '$slug'";
			}

		}

		if ( ! empty( $atts['name__like'] ) ) {

			//$atts['name__like'] = like_escape( $atts['name__like'] );
			$where[] = $wpdb->prepare( " AND t.name LIKE %s", '%' . $wpdb->esc_like( $atts['name__like'] ) . '%' );
			//$where[]            = $wpdb->prepare( 'AND t.name LIKE %s', '%' . $atts['name__like'] . '%' );
		}

		if ( ! empty( $atts['description__like'] ) ) {

			//$atts['description__like'] = like_escape( $atts['description__like'] );
			$where[] = $wpdb->prepare( " AND tt.description LIKE %s", '%' . $wpdb->esc_like( $atts['description__like'] ) . '%' );
			//$where[]                   = $wpdb->prepare( 'AND tt.description LIKE %s', '%' . $atts['description__like'] . '%' );
		}

		if ( '' !== $atts['parent'] ) {

			$where[] = $wpdb->prepare( 'AND tt.parent = %d', $atts['parent'] );
		}

		if ( 'count' == $atts['fields'] ) {

			$atts['hierarchical'] = FALSE;
		}

		if ( $atts['hide_empty'] && ! $atts['hierarchical'] ) {

			$where[] = 'AND tt.count > 0';
		}

		// Do not limit the query results when we have to descend the family tree.
		if ( $atts['number'] && ! $atts['hierarchical'] && ! $atts['child_of'] && '' === $atts['parent'] ) {

			$atts['number'] = absint( $atts['number'] );
			$atts['offset'] = absint( $atts['offset'] );

			if ( $atts['offset'] ) {

				$limit = $wpdb->prepare( 'LIMIT %d,%d', $atts['offset'], $atts['number'] );

			} else {

				$limit = $wpdb->prepare( 'LIMIT %d', $atts['number'] );
			}

		} else {

			$limit = '';
		}

		if ( ! empty( $atts['search'] ) ) {

			//$atts['search'] = like_escape( $atts['search'] );
			$atts['search'] = $wpdb->esc_like( $atts['search'] );
			$where[]        = $wpdb->prepare( 'AND ( (t.name LIKE %s) OR (t.slug LIKE %s) )', '%' . $atts['search'] . '%', '%' . $atts['search'] . '%' );
		}


		switch ( $atts['fields'] ) {

			case 'all':
				$select = array( 't.*', 'tt.*' );
				break;

			case 'ids':
			case 'id=>parent':
				$select = array( 't.term_id', 'tt.parent', 'tt.count' );
				break;

			case 'names':
				$select = array( 't.term_id', 'tt.parent', 'tt.count', 't.name' );
				break;

			case 'count':
				$orderBy = '';
				//$order   = '';
				$select  = array( 'COUNT(*)' );
				break;

			case 'id=>name':
				$select = array( 't.term_id', 't.name' );
				break;

			case 'id=>slug':
				$select = array( 't.term_id', 't.slug' );
				break;
		}

		/**
		 * Filter the fields to select in the terms query.
		 *
		 * @since 8.1
		 *
		 * @param array        $select     An array of fields to select for the terms query.
		 * @param array        $atts       An array of term query arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$fields = implode( ', ', apply_filters( 'cn_get_terms_fields', $select, $atts, $taxonomies ) );

		$join   = 'INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id';

		$pieces = array( 'fields', 'join', 'where', 'orderBy', 'limit' );

		/**
		 * Filter the terms query SQL clauses.
		 *
		 * @since 8.1
		 *
		 * @param array        $pieces     Terms query SQL clauses.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 * @param array        $atts       An array of terms query arguments.
		 */
		$clauses = apply_filters( 'cn_terms_clauses', compact( $pieces ), $taxonomies, $atts );

		foreach ( $pieces as $piece ) {

			$$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';
		}

		$sql = sprintf( 'SELECT %1$s FROM %2$s AS t %3$s WHERE %4$s %5$s%6$s',
			$fields,
			CN_TERMS_TABLE,
			$join,
			implode( ' ', $where ),
			$orderBy,
			empty( $limit ) ? '' : ' ' . $limit
		);

		if ( 'count' == $atts['fields'] ) {

			$term_count = $wpdb->get_var( $sql );

			return $term_count;
		}

		$terms = $wpdb->get_results( $sql );

		if ( 'all' == $atts['fields'] ) {

			foreach ( $taxonomies as $taxonomy ) {

				update_term_cache( $terms, 'cn_' . $taxonomy );
			}

		}

		if ( empty( $terms ) ) {

			wp_cache_add( $cache_key, array(), 'cn_terms', DAY_IN_SECONDS );

			$terms = apply_filters( 'cn_terms', array(), $taxonomies, $atts );

			return $terms;
		}

		if ( $atts['child_of'] ) {

			$children = self::childrenIDs( reset( $taxonomies ) );

			if ( ! empty( $children ) ) {

				$terms = self::descendants( $atts['child_of'], $terms, reset( $taxonomies ) );
			}
		}

		/*
		 * @todo Add method to adjust counts based on user visibility permissions.
		 */

		// Update term counts to include children.
		if ( $atts['pad_counts'] && 'all' == $atts['fields'] ) {

			self::padCounts( $terms, reset( $taxonomies ) );
		}

		// Make sure we show empty categories that have children.
		if ( $atts['hierarchical'] && $atts['hide_empty'] && is_array( $terms ) ) {

			foreach ( $terms as $k => $term ) {

				if ( ! $term->count ) {

					$children = self::children( $term->term_id, reset( $taxonomies ) );

					if ( is_array( $children ) ) {

						foreach ( $children as $child_id ) {

							$child = self::filter( $child_id, reset( $taxonomies ) );

							if ( $child->count ) {

								continue 2;
							}
						}
					}

					// It really is empty
					unset( $terms[ $k ] );
				}
			}
		}

		reset( $terms );

		$_terms = array();

		if ( 'id=>parent' == $atts['fields'] ) {

			while ( $term = array_shift( $terms ) )
				$_terms[ $term->term_id ] = $term->parent;

		} elseif ( 'ids' == $atts['fields'] ) {

			while ( $term = array_shift( $terms ) )
				$_terms[] = $term->term_id;

		} elseif ( 'names' == $atts['fields'] ) {

			while ( $term = array_shift( $terms ) )
				$_terms[] = $term->name;

		} elseif ( 'id=>name' == $atts['fields'] ) {

			while ( $term = array_shift( $terms ) )
				$_terms[ $term->term_id ] = $term->name;

		} elseif ( 'id=>slug' == $atts['fields'] ) {

			while ( $term = array_shift( $terms ) )
				$_terms[ $term->term_id ] = $term->slug;
		}

		if ( ! empty( $_terms ) ) {

			$terms = $_terms;
		}

		if ( $atts['number'] && is_array( $terms ) && count( $terms ) > $atts['number'] ) {

			$terms = array_slice( $terms, $atts['offset'], $atts['number'] );
		}

		wp_cache_add( $cache_key, $terms, 'cn_terms', DAY_IN_SECONDS );

		$terms = apply_filters( 'cn_terms', $terms, $taxonomies, $atts );

		return $terms;
	}

	/**
	 * Get all term data from database by term field.
	 *
	 * Warning: $value is not escaped for 'name' $field. You must do it yourself, if required.
	 *
	 * The default $field is 'id', therefore it is possible to also use NULL for field,
	 * but not recommended that you do so.
	 *
	 * If $value does not exist, the return value will be FALSE. If $taxonomy exists
	 * and $field and $value combinations exist, the term will be returned.
	 *
	 * There are two hooks, one is specifically for each term, named 'cn_get_term', and
	 * the second is for the taxonomy name, 'cn_term_$taxonomy'. Both hooks are passed the
	 * term object, and the taxonomy name as parameters. Both hooks are expected to
	 * return a term object.
	 *
	 * Filters:
	 *    cn_get_term - The method variables.
	 *        Passes: (object) $term, (string) $taxonomy
	 *        Return: $term
	 *
	 *    cn_get_{$taxonomy} - The fields for the SELECT query clause.
	 *        Passes: (object) $term, (string) $taxonomy
	 *        Return: $term
	 *
	 * NOTE: This is the Connections equivalent of @see get_term_by() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   sanitize_title()
	 * @uses   wp_unslash()
	 * @uses   is_wp_error()
	 * @uses   wp_cache_add()
	 * @uses   apply_filters()
	 * @uses   sanitize_term() Cleanses the term based on $filter context before returning.
	 *
	 * @param string     $field    Either 'slug', 'name', 'id' (term_id), or 'term_taxonomy_id'
	 * @param string|int $value    Search for this term value
	 * @param string     $taxonomy Taxonomy Name
	 * @param string     $output   Constant OBJECT, ARRAY_A, or ARRAY_N
	 * @param string     $filter   Optional, default is raw or no WordPress defined filter will applied.
	 *
	 * @return mixed Term Row from database. Will return false if $taxonomy does not exist or $term was not found.
	 */
	public static function getBy( $field, $value, $taxonomy, $output = OBJECT, $filter = 'raw' ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		// @todo Implement the taxonomy check.
		//if ( ! taxonomy_exists( $taxonomy ) ) {
		//	return FALSE;
		//}

		if ( 'slug' == $field ) {

			$field = 't.slug';
			$value = sanitize_title( $value );

			if ( empty( $value ) ) {

				return FALSE;
			}

		} else if ( 'name' == $field ) {

			// Assume already escaped
			$value = wp_unslash( $value );
			$field = 't.name';

		} else if ( 'term_taxonomy_id' == $field ) {

			$value = (int) $value;
			$field = 'tt.term_taxonomy_id';

		} else {

			$term = self::get( (int) $value, $taxonomy, $output, $filter );

			if ( is_wp_error( $term ) ) {
				$term = FALSE;
			}

			return $term;
		}

		$term = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT t.*, tt.* FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND $field = %s LIMIT 1",
				$taxonomy,
				$value
			)
		);

		if ( ! $term ) {

			return FALSE;
		}

		wp_cache_add( $term->term_id, $term, 'cn_' . $taxonomy );

		$term = apply_filters( 'cn_get_term', $term, $taxonomy );

		$term = apply_filters( "cn_get_$taxonomy", $term, $taxonomy );

		$term = sanitize_term( $term, 'cn_' . $taxonomy, $filter );

		if ( $output == OBJECT ) {

			return $term;

		} elseif ( $output == ARRAY_A ) {

			return get_object_vars( $term );

		} elseif ( $output == ARRAY_N ) {

			return array_values( get_object_vars( $term ) );

		} else {

			return $term;
		}
	}

	/**
	 * Get term data from database by term ID.
	 *
	 * The usage of this method is to apply filters to a term object. It
	 * is possible to get a term object from the database before applying the
	 * filters.
	 *
	 * $term ID must be part of $taxonomy, to get from the database. Failure, might
	 * be able to be captured by the hooks. Failure would be the same value as $wpdb
	 * returns for the get_row method.
	 *
	 * There are two hooks, one is specifically for each term, named 'cn_get_term', and
	 * the second is for the taxonomy name, 'cn_term_$taxonomy'. Both hooks are passed the
	 * term object, and the taxonomy name as parameters. Both hooks are expected to
	 * return a term object.
	 *
	 * Filters:
	 *    cn_get_term - The method variables.
	 *        Passes: (object) $term, (string) $taxonomy
	 *        Return: $term
	 *
	 *    cn_get_{$taxonomy} - The fields for the SELECT query clause.
	 *        Passes: (object) $term, (string) $taxonomy
	 *        Return: $term
	 *
	 * NOTE: This is the Connections equivalent of @see get_term() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses  WP_Error
	 * @uses  wp_cache_get()
	 * @uses  wp_cache_add()
	 * @uses  apply_filters()
	 * @uses  sanitize_term() Cleanses the term based on $filter context before returning.
	 *
	 * @param int|object $term     If integer, will get from database. If object will apply filters and return $term.
	 * @param string     $taxonomy Taxonomy name that $term is part of.
	 * @param string     $output   Constant OBJECT, ARRAY_A, or ARRAY_N
	 * @param string     $filter   Optional, default is raw or no WordPress defined filter will applied.
	 *
	 * @return mixed|null|WP_Error Term Row from database. Will return null if $term is empty. If taxonomy does not
	 * exist then WP_Error will be returned.
	 */
	public static function get( $term, $taxonomy, $output = OBJECT, $filter = 'raw' ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( empty( $term ) ) {

			return new WP_Error( 'invalid_term', __( 'Empty Term', 'connections' ) );
		}

		// @todo Implement taxonomy check.
		//if ( ! taxonomy_exists( $taxonomy ) ) {
		//
		//	return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy' ) );
		//}

		if ( is_object( $term ) && empty( $term->filter ) ) {

			wp_cache_add( $term->term_id, $term, 'cn_' . $taxonomy );

			$_term = $term;

		} else {

			if ( is_object( $term ) ) {

				$term = $term->term_id;
			}

			if ( ! $term = (int) $term ) {

				return NULL;
			}

			if ( ! $_term = wp_cache_get( $term, 'cn_' . $taxonomy ) ) {

				$_term = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT t.*, tt.* FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND t.term_id = %d LIMIT 1",
						$taxonomy,
						$term
					)
				);

				if ( ! $_term ) {

					return NULL;
				}

				wp_cache_add( $term, $_term, 'cn_' . $taxonomy );
			}
		}

		/**
		 * Filter a term.
		 *
		 * @since 8.1.6
		 *
		 * @param int|object $_term    Term object or ID.
		 * @param string     $taxonomy The taxonomy slug.
		 */
		$_term = apply_filters( 'cn_get_term', $_term, $taxonomy );

		/**
		 * Filter a taxonomy.
		 *
		 * The dynamic portion of the filter name, $taxonomy, refers
		 * to the taxonomy slug.
		 *
		 * @since 8.1.6
		 *
		 * @param int|object $_term    Term object or ID.
		 * @param string     $taxonomy The taxonomy slug.
		 */
		$_term = apply_filters( "cn_get_$taxonomy", $_term, $taxonomy );
		$_term = sanitize_term( $_term, 'cn_' . $taxonomy, $filter );

		if ( $output == OBJECT ) {

			return $_term;

		} elseif ( $output == ARRAY_A ) {

			$__term = get_object_vars( $_term );


			return $__term;

		} elseif ( $output == ARRAY_N ) {

			$__term = array_values( get_object_vars( $_term ) );

			return $__term;

		} else {

			return $_term;
		}
	}

	/**
	 * Build an array of ancestor IDs for a given object.
	 *
	 * Filters:
	 *    cn_get_ancestors - The method variables.
	 *        Passes: (array) $ancestors, (int) $object_id, (string) $object_type
	 *        Return: $ancestors
	 *
	 * NOTE: This is the Connections equivalent of @see get_ancestors() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @uses   apply_filters()
	 * @uses   is_wp_error()
	 *
	 * @param int    $object_id   The ID of the object
	 * @param string $object_type The type of object for which we'll be retrieving ancestors.
	 *
	 * @return array of ancestors from lowest to highest in the hierarchy.
	 */
	public static function getAncestors( $object_id = 0, $object_type = '' ) {

		$object_id = (int) $object_id;

		$ancestors = array();

		if ( empty( $object_id ) ) {

			return apply_filters( 'cn_get_ancestors', $ancestors, $object_id, $object_type );
		}

		//if ( is_taxonomy_hierarchical( $object_type ) ) {

			$term = self::get( $object_id, $object_type );

			while ( ! is_wp_error( $term ) && ! empty( $term->parent ) && ! in_array( $term->parent, $ancestors ) ) {

				$ancestors[] = (int) $term->parent;
				$term        = self::get( $term->parent, $object_type );
			}

		//} elseif ( post_type_exists( $object_type ) ) {
		//
		//	$ancestors = get_post_ancestors( $object_id );
		//}

		/**
		 * Filter a given object's ancestors.
		 *
		 * @since 8.1.6
		 *
		 * @param array  $ancestors   An array of object ancestors.
		 * @param int    $object_id   Object ID.
		 * @param string $object_type Type of object.
		 */
		return apply_filters( 'cn_get_ancestors', $ancestors, $object_id, $object_type );
	}

	/**
	 * Check if a term is an ancestor of another term.
	 *
	 * You can use either an id or the term object for both parameters.
	 *
	 * NOTE: This is the Connections equivalent of @see term_is_ancestor_of() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @since 8.2
	 *
	 * @param int|object $term1    ID or object to check if this is the parent term.
	 * @param int|object $term2    The child term.
	 * @param string     $taxonomy Taxonomy name that $term1 and $term2 belong to.
	 *
	 * @return bool Whether $term2 is child of $term1
	 */
	public static function isAncestorOf( $term1, $term2, $taxonomy ) {

		if ( ! isset( $term1->term_id ) ) {

			$term1 = self::get( $term1, $taxonomy );
		}

		if ( ! isset( $term2->parent ) ) {

			$term2 = self::get( $term2, $taxonomy );
		}

		if ( empty( $term1->term_id ) || empty( $term2->parent ) ) {

			return FALSE;
		}
		if ( $term2->parent == $term1->term_id ) {

			return TRUE;
		}

		return self::isAncestorOf( $term1, self::get( $term2->parent, $taxonomy ), $taxonomy );
	}


	/**
	 * Retrieves children of taxonomy as term IDs.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 *
	 * @uses   get_option()
	 * @uses   cnTerm::getTaxonomyTerms()
	 * @uses   update_option()
	 *
	 * @param  string $taxonomy Taxonomy name.
	 *
	 * @return array  Empty if $taxonomy isn't hierarchical or returns children as term IDs.
	 */
	private static function childrenIDs( $taxonomy ) {

		// if ( !is_taxonomy_hierarchical($taxonomy) )
		// 	return array();

		 $children = get_option( "cn_{$taxonomy}_children" );

		 if ( is_array( $children ) ) {

		 	return $children;
		 }

		$children = array();
		$terms    = self::getTaxonomyTerms( $taxonomy, array( 'get' => 'all', 'orderby' => 'id', 'fields' => 'id=>parent') );

		foreach ( $terms as $term_id => $parent ) {

			if ( $parent > 0 ){

				$children[ $parent ][] = $term_id;
			}

		}

		 update_option ("cn_{$taxonomy}_children", $children );

		return $children;
	}

	/**
	 * Get the subset of $terms that are descendants of $term_id.
	 *
	 * If $terms is an array of objects, then _children returns an array of objects.
	 * If $terms is an array of IDs, then _children returns an array of IDs.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 *
	 * @uses   cnTerm::descendants()
	 * @uses   cnTerm::childrenIDs()
	 * @uses   cnTerm::filter()
	 *
	 * @param  int    $term_id The ancestor term: all returned terms should be descendants of $term_id.
	 * @param  array  $terms The set of terms---either an array of term objects or term IDs---from which those that are descendants of $term_id will be chosen.
	 * @param  string $taxonomy The taxonomy which determines the hierarchy of the terms.
	 *
	 * @return array  The subset of $terms that are descendants of $term_id.
	 */
	private static function descendants( $term_id, $terms, $taxonomy ) {

		if ( empty( $terms ) ) {

			return array();
		}

		$term_list    = array();
		$has_children = self::childrenIDs( $taxonomy );

		if  ( ( 0 != $term_id ) && ! isset( $has_children[ $term_id ] ) ) {

			return array();
		}

		foreach ( (array) $terms as $term ) {

			$use_id = FALSE;

			if ( ! is_object( $term ) ) {

				$term = self::filter( $term, $taxonomy );

				 if ( is_wp_error( $term ) ) {

					 return $term;
				 }

				$use_id = TRUE;
			}

			if ( $term->term_id == $term_id ) {

				continue;
			}

			if ( $term->parent == $term_id ) {

				if ( $use_id ) {

					$term_list[] = $term->term_id;

				} else {

					$term_list[] = $term;
				}

				if ( ! isset( $has_children[ $term->term_id ]) ) {

					continue;
				}

				if ( $children = self::descendants( $term->term_id, $terms, $taxonomy ) ) {

					$term_list = array_merge( $term_list, $children );
				}

			}
		}

		return $term_list;
	}

	/**
	 * Merge all term children into a single array of their IDs.
	 *
	 * This recursive function will merge all of the children of $term into the same
	 * array of term IDs. Only useful for taxonomies which are hierarchical.
	 *
	 * Will return an empty array if $term does not exist in $taxonomy.
	 *
	 * NOTE: This is the Connections equivalent of @see get_term_children() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @global $wpdb
	 * @uses   self::childrenIDs()
	 * @uses   self::children() Used to get the children of both $taxonomy and the parent $term.
	 *
	 * @param  string $term_id  ID of term to get children.
	 * @param  string $taxonomy Taxonomy name.
	 * @return array|WP_Error   Array of term IDs. WP_Error returned if $taxonomy does not exist.
	 */
	public static function children( $term_id, $taxonomy ) {

		// if ( ! taxonomy_exists($taxonomy) )
		// 	return new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));

		$term_id = intval( $term_id );

		$terms = self::childrenIDs( $taxonomy );

		if ( ! isset( $terms[ $term_id ] ) ) {

			return array();
		}

		$children = $terms[ $term_id ];

		foreach ( (array) $terms[ $term_id ] as $child ) {

			if ( $term_id == $child ) {

				continue;
			}

			if ( isset( $terms[ $child ] ) ) {

				$children = array_merge( $children, self::children( $child, $taxonomy ) );
			}

		}

		return $children;
	}

	/**
	 * Add count of children to parent count.
	 *
	 * Recalculates term counts by including items from child terms. Assumes all
	 * relevant children are already in the $terms argument.
	 *
	 * NOTE: This is the Connections equivalent of @see _pad_term_counts() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 *
	 * @global $wpdb
	 *
	 * @uses   childrenIDs()
	 * @uses   is_user_logged_in()
	 * @uses   current_user_can()
	 * @uses   wpdb::get_results()
	 *
	 * @param  array $terms List of Term IDs
	 * @param  string $taxonomy Term Context
	 *
	 * @return null Will break from function if conditions are not met.
	 */
	private static function padCounts( &$terms, $taxonomy ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$term_ids   = array();
		$visibility = array();

		// Grab an instance of the Connections object.
		/** @var connectionsLoad $instance */
		$instance = Connections_Directory();

		// This function only works for hierarchical taxonomies like post categories.
		// if ( !is_taxonomy_hierarchical( $taxonomy ) )
		// 	return;

		$term_hier = self::childrenIDs( $taxonomy );

		if ( empty( $term_hier ) ) {

			return;
		}

		$term_items = array();

		foreach ( (array) $terms as $key => $term ) {

			$terms_by_id[ $term->term_id ]       = &$terms[ $key ];
			$term_ids[ $term->term_taxonomy_id ] = $term->term_id;
		}

		/*
		 * // START --> Set up the query to only return the entries based on user permissions.
		 */
		if ( is_user_logged_in() ) {

			if ( current_user_can( 'connections_view_public' ) ) $visibility[]                 = 'public';
			if ( current_user_can( 'connections_view_private' ) ) $visibility[]                = 'private';
			if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';

		} else {

			// Display the 'public' entries if the user is not required to be logged in.
			$visibility[] = $instance->options->getAllowPublic() ? 'public' : '';
		}
		/*
		 * // END --> Set up the query to only return the entries based on user permissions.
		 */

		// Get the object and term ids and stick them in a lookup table
		// $tax_obj      = get_taxonomy( $taxonomy );
		$entry_types = array( 'individual', 'organization', 'family' );
		$results     = $wpdb->get_results("SELECT entry_id, term_taxonomy_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " INNER JOIN " . CN_ENTRY_TABLE . " ON entry_id = id WHERE term_taxonomy_id IN (" . implode(',', array_keys( $term_ids ) ) . ") AND entry_type IN ('" . implode( "', '", $entry_types ) . "') AND visibility IN ('" . implode( "', '", (array) $visibility ) . "')");

		foreach ( $results as $row ) {

			$id = $term_ids[ $row->term_taxonomy_id ];

			$term_items[ $id ][ $row->entry_id ] = isset( $term_items[ $id ][ $row->entry_id ] ) ? ++$term_items[ $id ][ $row->entry_id ] : 1;
		}

		// Touch every ancestor's lookup row for each post in each term
		foreach ( $term_ids as $term_id ) {

			$child = $term_id;

			while ( ! empty( $terms_by_id[ $child ] ) && $parent = $terms_by_id[ $child ]->parent ) {

				if ( ! empty( $term_items[ $term_id ] ) ) {

					foreach ( $term_items[ $term_id ] as $item_id => $touches ) {

						$term_items[ $parent ][ $item_id ] = isset( $term_items[ $parent ][ $item_id ] ) ? ++$term_items[ $parent ][ $item_id ]: 1;
					}
				}

				$child = $parent;
			}
		}

		// Transfer the touched cells
		foreach ( (array) $term_items as $id => $items ) {

			if ( isset( $terms_by_id[ $id ] ) ) {

				$terms_by_id[ $id ]->count = count( $items );
			}

		}

	}

	/**
	 * Generates a permalink for a taxonomy term.
	 *
	 * Filters:
	 *    cn_term_link
	 *        Passes: (string) $link, (object) $term, (string) $taxonomy
	 *        Return: $link
	 *
	 * NOTE: This is the Connections equivalent of get_term_link() in WordPress core ../wp-includes/taxonomy.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @uses   cnTerm::get()
	 * @uses   cnTerm::getBy()
	 * @uses   is_wp_error()
	 * @uses   cnTerm::getAncestors()
	 * @uses   cnURL::permalink()
	 * @uses   apply_filters()
	 *
	 * @param object|int|string $term
	 * @param string            $taxonomy (optional if $term is object)
	 *
	 * @return string|WP_Error URL  to taxonomy term on success, WP_Error if term does not exist.
	 */
	public static function permalink( $term, $taxonomy = '' ) {

		/** @var $wp_rewrite WP_Rewrite */
		//global $wp_rewrite;

		if ( ! is_object( $term ) ) {

			if ( is_int( $term ) ) {

				$term = self::get( $term, $taxonomy );

			} else {

				$term = self::getBy( 'slug', $term, $taxonomy );
			}
		}

		if ( ! is_object( $term ) ) {

			$term = new WP_Error( 'invalid_term', __( 'Empty Term', 'connections' ) );
		}

		if ( is_wp_error( $term ) ) {

			return $term;
		}

		$taxonomy = $term->taxonomy;

		//$link = $wp_rewrite->get_extra_permastruct( $taxonomy );

		//$slug = $term->slug;
		//$t    = get_taxonomy( $taxonomy );

		//if ( empty( $link ) ) {

			//if ( 'category' == $taxonomy ) {

				//$link = '?cat=' . $term->term_id;

			//} elseif ( $t->query_var ) {
			//
			//	$term_link = "?$t->query_var=$slug";

			//} else {

			//	$link = "?taxonomy=$taxonomy&term=$slug";
			//}

			//$link = home_url( $link );

		//} else {

			//if ( $t->rewrite['hierarchical'] ) {

				$slugs     = array();
				$ancestors = self::getAncestors( $term->term_id, $taxonomy );

				foreach ( (array) $ancestors as $ancestor ) {

					$ancestor_term = self::get( $ancestor, $taxonomy );
					$slugs[]       = $ancestor_term->slug;
				}

				$slugs   = array_reverse( $slugs );
				$slugs[] = $term->slug;

				$link = cnURL::permalink(
					array(
						'type'   => 'category',
						'slug'   => implode( '/', $slugs ),
						'title'  => $term->name,
						'text'   => $term->name,
						'data'   => 'url',
						'return' => TRUE,
					)
				);

			//} else {
			//
			//	$term_link = str_replace( "%$taxonomy%", $slug, $term_link );
			//}

			//$link = home_url( user_trailingslashit( $link, 'category' ) );
		//}

		/**
		 * Filter the term link.
		 *
		 * @since 8.1.6
		 *
		 * @param string $link Term link URL.
		 * @param object $term     Term object.
		 * @param string $taxonomy Taxonomy slug.
		 */
		return apply_filters( 'cn_term_link', $link, $term, $taxonomy );
	}

	/**
	 * Reorganizes results returned from @see cnTerm::getTaxonomyTerms() into parent/child relationship.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @uses   cnTerm::getTaxonomyTerms()
	 *
	 * @param  array  $taxonomies
	 * @param  array  $atts
	 *
	 * @return array
	 */
	public static function tree( $taxonomies = array( 'category' ), $atts = array() ) {

		$defaults = array(
			'get'          => '',
			'orderby'      => 'name',
			'order'        => 'ASC',
			'hide_empty'   => FALSE,
			'exclude'      => array(),
			'exclude_tree' => array(),
			'include'      => array(),
			'fields'       => 'all',
			'slug'         => '',
			'parent'       => '',
			'hierarchical' => TRUE,
			'child_of'     => 0,
			'name__like'   => '',
			'pad_counts'   => FALSE,
			'offset'       => 0,
			'number'       => 0,
			'search'       => '',
		);

		/**
		 * Filter the terms query arguments.
		 *
		 * @since 8.1.6
		 *
		 * @param array        $atts       An array of arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$atts = apply_filters( 'cn_get_terms_tree_atts', $atts, $taxonomies );

		$atts = wp_parse_args( $atts, $defaults );

		$terms = self::getTaxonomyTerms( $taxonomies, $atts );

		/**
		 * This is where the magic happens --> the building of the term hierarchy tree.
		 * Based in @link http://stackoverflow.com/a/3261351
		 *
		 * Since the term parents are not guaranteed to be in the $terms array before the term children
		 * two passes need to be made.
		 *
		 * The first pass is to build an indexed term array where the term_id will be set as
		 * the array index for the reference to the term object.
		 *
		 * The second pass builds the term hierarchy array from the term_id indexed nodes array.
		 */
		$nodes = array();
		$tree  = array();

		foreach ( $terms as &$node ) {

			$node->children = array();
			$nodes[ $node->term_id ] =& $node;
		}

		foreach ( $nodes as &$node ) {

			// If the $term->parent ID exists in the indexed nodes array, add a reference to the term in the $term->children array.
			if ( array_key_exists( $node->parent, $nodes ) ) {

				$nodes[ $node->parent ]->children[] =& $node;

			// If the $term->parent ID does NOT exist in the indexed nodes array, add a reference to term to the root of the hierarchy tree.
			// Only terms with parent ID of `0` or orphaned terms should end up in the root of the hierarchy tree.
			} else {

				$tree[] =& $node;
			}
		}

		return $tree;
	}

	/**
	 * Filter `cn_terms_clauses` and add support for a `meta_query` argument.
	 *
	 * @todo This can be integrated into @see cnTerm::getTaxonomyTerms(), for now it is fine as a filter.
	 *
	 * @access private
	 * @since  8.5.2
	 *
	 * @param array $pieces     Terms query SQL clauses.
	 * @param array $taxonomies An array of taxonomies.
	 * @param array $args       An array of terms query arguments.
	 *
	 * @return Array of query pieces, maybe modified.
	 */
	public static function getTaxonomyTermsClauses( $pieces = array(), $taxonomies = array(), $args = array() ) {

		if ( ! empty( $args['meta_query'] ) ) {

			// Get the meta query parts
			$meta_query = new cnMeta_Query( $args['meta_query'] );
			$meta_query->parse_query_vars( $args );

			// Combine pieces & meta-query clauses.
			if ( ! empty( $meta_query->queries ) ) {

				$meta_clauses = $meta_query->get_sql( 'term', 'tt', 'term_id', $taxonomies );

				$pieces['join']   .= $meta_clauses['join'];
				$pieces['where'][] = $meta_clauses['where'];
			}
		}

		return $pieces;
	}
}

// Make `meta_query` arguments work.
add_filter( 'cn_terms_clauses',  array( 'cnTerm', 'getTaxonomyTermsClauses'  ), 10, 3 );
