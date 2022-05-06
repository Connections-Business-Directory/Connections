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

use Connections_Directory\Taxonomy;
use Connections_Directory\Taxonomy\Registry;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnTerms
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnTerms {

	/**
	 * Returns all the terms under a taxonomy type.
	 *
	 * $taxonomies currently this will only accept a string of the specified taxonomy
	 *
	 * @since  unknown
	 * @deprecated 8.1.6 Use {@see cnTerm::tree()} instead.
	 * @see cnTerm::tree()
	 *
	 * @param array $taxonomies
	 * @param array $atts
	 *
	 * @return array
	 */
	public function getTerms( $taxonomies, $atts = array() ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::tree()' );

		return cnTerm::tree( $taxonomies, $atts );
	}

	/**
	 * @deprecated 8.1.6 Use {@see cnTerm::get()} instead.
	 * @see cnTerm::get()
	 *
	 * @param $id
	 * @param $taxonomy
	 *
	 * @return array|null|cnTerm_Object|WP_Error
	 */
	public function getTerm( $id, $taxonomy ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::get()' );

		return cnTerm::get( $id, $taxonomy );
	}

	/**
	 * Get term object by 'name', 'id' or 'slug'.
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::getBy()} instead.
	 * @see cnTerm::getBy()
	 *
	 * @param string     $field
	 * @param string|int $value    Search term.
	 * @param string     $taxonomy
	 *
	 * @return array|false|null|cnTerm_Object|WP_Error
	 */
	public function getTermBy( $field, $value, $taxonomy ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::getBy()' );

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

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::children()' );

		return cnTerm::children( $id, $taxonomy );
	}

	/**
	 * Returns all the children terms of the parent term recursively by 'term_id', 'name' or 'slug'.
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $taxonomy
	 * @param array  $_previousResults
	 *
	 * @return array
	 */
	public function getTermChildrenBy( $field, $value, $taxonomy, $_previousResults = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;
		$results = array();

		// Only run this query if the field is not term_id.
		if ( 'term_id' !== $field ) {

			$queryTermID = $wpdb->prepare(
				'SELECT DISTINCT tt.term_id from ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE $field = %s ",
				$value
			);
			// print_r($queryTermID . '<br /><br />');

			$termID = $wpdb->get_var( $queryTermID );
			// print_r($termID . '<br /><br />');

			// If the term is a root parent, skip continue.
			if ( empty( $termID ) ) {

				return array();
			}

		} else {

			$termID = $value;
		}

		$queryChildrenIDs = $wpdb->prepare(
			'SELECT DISTINCT * from ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id WHERE parent = %d ',
			$termID
		);
		// print_r($queryChildrenIDs . '<br /><br />');

		$terms = $wpdb->get_results( $queryChildrenIDs );

		if ( empty( $terms ) || ! is_array( $terms ) ) {

			return array();
		}

		foreach ( $terms as $term ) {

			// If the term is a root parent, skip continue.
			if ( 0 == $term->parent ) {

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
	 * @deprecated 8.1.6 Use {@see cnTerm::insert()} instead.
	 * @see cnTerm::insert()
	 *
	 * @param string $term
	 * @param string $taxonomy
	 * @param array  $attributes
	 *
	 * @return array|WP_Error An array containing the term_id and term_taxonomy_id, WP_Error otherwise.
	 */
	public function addTerm( $term, $taxonomy, $attributes ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::insert()' );

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
	 * @return array|WP_Error
	 */
	public function updateTerm( $termID, $taxonomy, $attributes ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::update()' );

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
	 * @param int    $parent   Term parent ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return bool|int|WP_Error
	 */
	public function deleteTerm( $id, $parent, $taxonomy ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::delete()' );

		$result = cnTerm::delete( $id, $taxonomy );

		return $result;
	}

	/**
	 * Creates the entry and term relationships.
	 *
	 * If the term $IDs is empty then the uncategorized category is set as the relationship.
	 * NOTE: Only if the taxonomy is 'category'
	 *
	 * NOTE: This is the Connections equivalent of @see wp_set_post_terms() in WordPress core ../wp-includes/post.php
	 *
	 * @deprecated 8.1.6 Use {@see cnTerm::setRelationships()} instead.
	 * @see cnTerm::setRelationships()
	 *
	 * @param int    $entryID
	 * @param array  $termIDs
	 * @param string $taxonomySlug
	 *
	 * @return array|WP_Error
	 */
	public function setTermRelationships( $entryID, $termIDs, $taxonomySlug ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::setRelationships()' );

		if ( ! is_array( $termIDs ) ) {

			$termIDs = array( $termIDs );
		}

		$taxonomy = Registry::get()->getTaxonomy( $taxonomySlug );

		if ( $taxonomy instanceof Taxonomy ) {

			/*
			 * Hierarchical taxonomies must always pass IDs rather than names so that
			 * children with the same names but different parents aren't confused.
			 */
			if ( $taxonomy->isHierarchical() ) {

				$termIDs = array_unique( array_map( 'intval', $termIDs ) );
			}

		} else {

			/*
			 * If the taxonomy is not a registered with the API, assume it is a "legacy" taxonomy
			 * and ensure term ID are all integers as done in all version prior to 10.2.
			 */
			$termIDs = array_unique( array_map( 'intval', $termIDs ) );
		}

		$result = cnTerm::setRelationships( $entryID, $termIDs, $taxonomySlug );

		if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
			cnTerm::updateCount( $result, $taxonomySlug );
		}

		cnCache::clear( true, 'transient', "cn_{$taxonomySlug}" );

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
	 * @return array|WP_Error Array of term relationships.
	 */
	public function getTermRelationships( $entryID ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::getRelationships()' );

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
	 * @return bool|WP_Error
	 */
	public function deleteTermRelationships( $entryID ) {

		_deprecated_function( __METHOD__, '9.15', 'cnTerm::deleteRelationships()' );

		$terms  = cnTerm::getRelationships( $entryID, 'category', array( 'fields' => 'ids' ) );
		$result = cnTerm::deleteRelationships( $entryID, $terms, 'category' );

		cnCache::clear( true, 'transient', 'cn_category' );

		return $result;
	}
}
