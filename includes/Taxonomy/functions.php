<?php

namespace Connections_Directory\Taxonomy;

use cnMeta;
use cnTerm;
use Connections_Directory\Taxonomy\Term;
use Connections_Directory\Taxonomy;
use WP_Error;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the subset of $terms that are descendants of $term_id.
 *
 * If $terms is an array of objects, then _children returns an array of objects.
 * If $terms is an array of IDs, then _children returns an array of IDs.
 *
 * NOTE: This is the Connections equivalent of @see _get_term_children() in WordPress core ../wp-includes/taxonomy.php
 *
 * @internal
 * @since 10.3
 *
 * @param int    $term_id   The ancestor term: all returned terms should be descendants of $term_id.
 * @param array  $terms     The set of terms---either an array of term objects or term IDs---from which those that are descendants of $term_id will be chosen.
 * @param string $taxonomy  The taxonomy which determines the hierarchy of the terms.
 * @param array  $ancestors Optional. Term ancestors that have already been identified. Passed by reference, to keep
 *                          track of found terms when recursing the hierarchy. The array of located ancestors is used
 *                          to prevent infinite recursion loops. For performance, `term_ids` are used as array keys,
 *                          with 1 as value. Default empty array.
 *
 * @return array|WP_Error  The subset of $terms that are descendants of $term_id.
 */
function _getTermChildren( $term_id, $terms, $taxonomy, &$ancestors = array() ) {

	if ( empty( $terms ) ) {

		return array();
	}

	$term_id      = (int) $term_id;
	$term_list    = array();
	$has_children = _getTermHierarchy( $taxonomy );

	if ( ( 0 !== $term_id ) && ! isset( $has_children[ $term_id ] ) ) {

		return array();
	}

	// Include the term itself in the ancestors array, so we can properly detect when a loop has occurred.
	if ( empty( $ancestors ) ) {

		$ancestors[ $term_id ] = 1;
	}

	foreach ( (array) $terms as $term ) {

		$use_id = false;

		if ( ! is_object( $term ) ) {

			$term = cnTerm::get( $term, $taxonomy );

			if ( is_wp_error( $term ) ) {

				return $term;
			}

			$use_id = true;
		}

		// Don't recurse if we've already identified the term as a child - this indicates a loop.
		if ( isset( $ancestors[ $term->term_id ] ) ) {

			continue;
		}

		if ( (int) $term->parent === $term_id ) {

			if ( $use_id ) {

				$term_list[] = $term->term_id;

			} else {

				$term_list[] = $term;
			}

			if ( ! isset( $has_children[ $term->term_id ] ) ) {

				continue;
			}

			$ancestors[ $term->term_id ] = 1;

			$children = _getTermChildren( $term->term_id, $terms, $taxonomy, $ancestors );

			if ( $children ) {

				$term_list = array_merge( $term_list, $children );
			}

		}
	}

	return $term_list;
}

/**
 * Retrieves children of taxonomy as term IDs.
 *
 * NOTE: This is the Connections equivalent of @see _get_term_hierarchy() in WordPress core ../wp-includes/taxonomy.php
 *
 * @internal
 * @since 10.3
 *
 * @param string $taxonomy Taxonomy name.
 *
 * @return array  Empty if $taxonomy isn't hierarchical or returns children as term IDs.
 */
function _getTermHierarchy( $taxonomy ) {

	if ( ! isHierarchical( $taxonomy ) ) {
		return array();
	}

	$children = get_option( "cn_{$taxonomy}_children" );

	if ( is_array( $children ) ) {

		return $children;
	}

	$children = array();
	$terms    = cnTerm::getTaxonomyTerms( $taxonomy, array( 'get' => 'all', 'orderby' => 'id', 'fields' => 'id=>parent' ) );

	foreach ( $terms as $term_id => $parent ) {

		if ( $parent > 0 ) {

			$children[ $parent ][] = $term_id;
		}

	}

	update_option( "cn_{$taxonomy}_children", $children );

	return $children;
}

/**
 * Add count of children to parent count.
 *
 * Recalculates term counts by including items from child terms. Assumes all relevant children are already in the $terms argument.
 *
 * NOTE: This is the Connections equivalent of @see _pad_term_counts() in WordPress core ../wp-includes/taxonomy.php
 *
 * @internal
 * @since 10.3
 *
 * @global wpdb $wpdb
 *
 * @param array  $terms    List of Term IDs.
 * @param string $taxonomy Term Context.
 */
function _padTermCounts( &$terms, $taxonomy ) {

	global $wpdb;

	$term_ids   = array();
	$visibility = array();

	// Grab an instance of the Connections object.
	$instance = Connections_Directory();

	// This function only works for hierarchical taxonomies like post categories.
	if ( ! isHierarchical( $taxonomy ) ) {

		return;
	}

	$term_hier = _getTermHierarchy( $taxonomy );

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

		if ( current_user_can( 'connections_view_public' ) ) {
			$visibility[] = 'public';
		}

		if ( current_user_can( 'connections_view_private' ) ) {
			$visibility[] = 'private';
		}

		if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) {
			$visibility[] = 'unlisted';
		}

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
	$results     = $wpdb->get_results( 'SELECT entry_id, term_taxonomy_id FROM ' . CN_TERM_RELATIONSHIP_TABLE . ' INNER JOIN ' . CN_ENTRY_TABLE . ' ON entry_id = id WHERE term_taxonomy_id IN (' . implode( ',', array_keys( $term_ids ) ) . ") AND entry_type IN ('" . implode( "', '", $entry_types ) . "') AND visibility IN ('" . implode( "', '", (array) $visibility ) . "')" );

	foreach ( $results as $row ) {

		$id = $term_ids[ $row->term_taxonomy_id ];

		$term_items[ $id ][ $row->entry_id ] = isset( $term_items[ $id ][ $row->entry_id ] ) ? ++$term_items[ $id ][ $row->entry_id ] : 1;
	}

	// Touch every ancestor's lookup row for each post in each term.
	foreach ( $term_ids as $term_id ) {

		$child     = $term_id;
		$ancestors = array();

		while ( ! empty( $terms_by_id[ $child ] ) && $parent = $terms_by_id[ $child ]->parent ) {

			$ancestors[] = $child;

			if ( ! empty( $term_items[ $term_id ] ) ) {

				foreach ( $term_items[ $term_id ] as $item_id => $touches ) {

					$term_items[ $parent ][ $item_id ] = isset( $term_items[ $parent ][ $item_id ] ) ? ++$term_items[ $parent ][ $item_id ] : 1;
				}
			}

			$child = $parent;

			if ( in_array( $parent, $ancestors ) ) {

				break;
			}
		}
	}

	// Transfer the touched cells.
	foreach ( (array) $term_items as $id => $items ) {

		if ( isset( $terms_by_id[ $id ] ) ) {

			$terms_by_id[ $id ]->count = count( $items );
		}

	}
}

/**
 * Whether or not a taxonomy is registered.
 *
 * @since 10.3
 *
 * @param string $slug
 *
 * @return bool
 */
function exists( $slug ) {

	return Registry::get()->exists( $slug );
}

/**
 * Whether or not a taxonomy is hierarchical.
 *
 * @since 10.3
 *
 * @param string $slug
 *
 * @return bool
 */
function isHierarchical( $slug ) {

	$taxonomy = Registry::get()->getTaxonomy( $slug );

	if ( $taxonomy instanceof Taxonomy ) {

		return $taxonomy->isHierarchical();
	}

	return false;
}

/**
 * Updates metadata cache for list of term IDs.
 *
 * Performs SQL query to retrieve all metadata for the terms matching `$term_ids` and stores them in the cache.
 * Subsequent calls to `get_term_meta()` will not need to query the database.
 *
 * @since 10.3
 *
 * @param array $term_ids List of term IDs.
 *
 * @return array|false An array of metadata on success, false if there is nothing to update.
 */
function updateTermMetaCache( $term_ids ) {

	return cnMeta::updateCache( 'term', $term_ids );
}

/**
 * Updates Terms to Taxonomy in cache.
 *
 * NOTE: This is the Connections equivalent of @see update_term_cache() in WordPress core ../wp-includes/taxonomy.php
 *
 * @since 10.3
 *
 * @param Term[] $terms Array of term objects to change.
 */
function updateTermCache( $terms ) {

	foreach ( (array) $terms as $term ) {

		// Create a copy in case the array was passed by reference.
		$_term = clone $term;

		// Object ID should not be cached.
		unset( $_term->object_id );

		wp_cache_add( $term->term_id, $_term, 'cn_terms' );
	}
}
