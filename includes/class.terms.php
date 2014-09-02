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
	 * Holds the array that shows the term parent relationship as array.
	 * key == the parent ID
	 * value == array of the child objects
	 *
	 * @var array
	 */
	private $termChildren = array();

	private $termChildrenIDs = array();

	private $terms = array();

	/**
	 * Returns all the terms under a taxonomy type.
	 *
	 * $taxonomies currently this will only accept a string of the specified taxonomy
	 * @TODO: Add the code necessary to accept arrays for requesting multiple taxonomy types
	 * @TODO: Add default arguments see /wp-includes/taxonomy.php ->  line 515 to get terms specific to a type
	 *
	 * @access public
	 * @since unknown
	 * @param array $taxonomies
	 * @param array $atts [optional]
	 *
	 * @return array
	 */
	public function getTerms( $taxonomies, $atts = NULL ) {
		global $wpdb;

		// return cnTerm::tree( $taxonomies, $atts );

		$defaults = array(
			'orderby'       => 'name', //(string|array)
			'order'         => 'ASC', //(string|array)
			/*'hide_empty'    => true,  to work in as needed to match core WP
			'exclude'       => array(),
			'exclude_tree'  => array(),
			'include'       => array(),
			'number'        => '',
			'fields'        => 'all',
			'slug'          => '',
			'parent'         => '',
			'hierarchical'  => true,
			'child_of'      => 0,
			'get'           => '',
			'name__like'    => '',
			'pad_counts'    => false,
			'offset'        => '',
			'search'        => '',
			'cache_domain'  => 'core'*/
		);

		$atts = wp_parse_args( $atts, $defaults );


		// If the term query has alread been run and the parent/child relationship built,
		// return the stored version rather than quering/building again and again.
		if ( ! empty( $this->terms ) ) return $this->terms;

		$query = 'SELECT t.*, tt.* FROM ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN (\'' . ( is_array( $taxonomies) ? implode( "', '", $taxonomies ) : $taxonomies ) . '\')';

		//allows for both a array match on orderby and order and single string values
		if ( is_array( $atts['orderby'] ) ) {

			$query .= ' ORDER BY ';
			$i = 0;

			foreach ( $atts['orderby'] as $orderby ) {

				if ( is_array( $atts['order'] ) && isset( $atts['order'][ $i ] ) ) {

					$order = $atts['order'][ $i ]; // lines up with the first value in orderby

				} else {

					$order = is_array( $atts['order'] ) ? $atts['order'][0] : $atts['order'];
				}

				$query .= sprintf(' ' . ( $i > 0 ? ', ' :' ' ) . ' %s %s', $atts['orderby'][ $i ], $order );

				$i++;
			}

		} else {

			if ( is_array( $atts['order'] ) ) {

				// orderby was a string but for some odd reason an array
				//was passed for the order so we assume the  0 index
				$order = $atts['order'][0];

			} else {

				$order = $atts['order'];
			}

			$query .= sprintf(' ORDER BY %s %s', $atts['orderby'], $order );
		}

		// var_dump($query);
		$terms = $wpdb->get_results( $query );
		// print_r($terms);

		/*
		 * Loop thru the results and build an array where key == parent ID and the value == the child objects
		 *
		 * NOTE: Currently $taxonomies does not need to be sent, it's not being used in the method. It's
		 * 		 being left in place for future use.
		 */
		foreach ( $terms as $term ) {

			$this->buildChildrenArray( $term->term_id, $terms, $taxonomies );
		}

		/*
		 * Loop thru the results again adding the children objects from $this->termChildren to the parent object.
		 *
		 * NOTE: Currently $taxonomies does not need to be sent, it's not being used in the method. It's
		 * 		 being left in place for future use.
		 */
		foreach( $terms as $key => $term ) {

			$term->children = $this->getChildren( $term->term_id, $terms, $taxonomies );
		}

		/*
		 * Loop thru the results once more and remove all child objects from the base array leaving only parent objects
		 */
		foreach( $terms as $key => $term ) {

			if ( $this->isChild( $term->term_id ) ) unset( $terms[ $key ] );
		}

		$this->terms = $terms;

		//return $this->termChildren;
		return $terms;
	}

	public function getTerm($id, $taxonomy)
	{
		global $wpdb;

		$query = "SELECT t.*, tt.* from " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy='$taxonomy' AND t.term_id='$id'";

		return $wpdb->get_row($query);
	}

	/**
	 * Get term data by 'name', 'id' or 'slug'.
	 *
	 * @param string $field
	 * @param string | int -- Search term
	 * @param string $taxonomy
	 *
	 * @return mixed | False or term object
	 */
	public function getTermBy( $field, $value, $taxonomy ) {
		global $wpdb;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( 'slug' == $field ) {

			$field = 't.slug';
			$value = sanitize_title($value);
			if ( empty($value) ) return false;

		} else if ( 'name' == $field ) {

			// Assume already escaped
			$value = stripslashes($value);
			$field = 't.name';

		} else {

			$field = 't.term_id';
			$value = (int) $value;
		}

		$sql = $wpdb->prepare( "SELECT t.*, tt.* FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND $field = %s LIMIT 1", $taxonomy, $value);

		if ( ! $results = $instance->retrieve->results( $sql ) ) {

			$results = $wpdb->get_row( $sql );
			$instance->retrieve->cache( $sql, $results );
		}

		if ( ! $results ) {

			return FALSE;

		} else {

			return $results;
		}

	}

	private function getChildren($termID, $terms, $taxonomies)
	{
		foreach ($terms as $key => $term)
		{
			if ($termID == $term->parent)
			{
				$termList[] = $term;
			}
		}
		if ( isset($termList) ) return $termList;
		//return $this->termChildren[$termID];
	}

	private function buildChildrenArray($termID, $terms, $taxonomies)
	{
		foreach ($terms as $term)
		{
			// Skip the term if it is itself
			if ($termID == $term->term_id) continue;

			if ($termID == $term->parent)
			{
				$this->termChildren[$termID][] = $term;
			}
		}
	}

	private function isChild($termID)
	{
		$isChild = FALSE;

		foreach ($this->termChildren as $parentID => $children)
		{
			foreach ($children as $child)
			{
				if ($termID == $child->term_id)
				{
					$isChild = TRUE;
				}
			}
		}

		if ($isChild)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Returns all the children term IDs of the parent term ID recursively.
	 *
	 * @param interger $id
	 * @param string $taxonomy
	 * @param object $_CNpreviousIDs [optional]
	 * @return array || NULL
	 */
	public function getTermChildrenIDs($id, $taxonomy, $_CNpreviousIDs = NULL)
	{
		/*
		 * @TODO: Should be able to remove the $_CNpreviousIDs global as it shouldn't be need.
		 * Keeping it around for now incase I have to revert the code.
		 */
		global $wpdb, $_CNpreviousIDs;
		$termChildrenIDs = array();

		$query = $wpdb->prepare( "SELECT DISTINCT tt.term_id from " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE parent = %d ", $id);

		$childrenIDs = $wpdb->get_col($query);

		//print_r($childrenIDs);
		if ( !empty($childrenIDs) )
		{
			foreach ($childrenIDs as $ttID)
			{
				//$this->termChildrenIDs[] = $ttID;
				//$_CNpreviousIDs[] = $ttID;
				$termChildrenIDs[] = $ttID;

				$result = $this->getTermChildrenIDs($ttID, $taxonomy, $_CNpreviousIDs);

				$termChildrenIDs = array_merge($termChildrenIDs, (array) $result);
			}
		}
		else
		{
			return NULL;
		}

		return $termChildrenIDs;
		//return $_CNpreviousIDs;
		//return $this->termChildrenIDs;
	}

	/**
	 * Returns all the children terms of the parent term recursively by 'term_id', 'name' or 'slug'.
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $taxonomy
	 * @param object $_previousResults [optional]
	 *
	 * @return array
	 */
	public function getTermChildrenBy($field, $value, $taxonomy, $_previousResults = NULL)
	{
		global $wpdb;
		$results = array();

		// Only run this query if the field is not term_id.
		if ( $field !== 'term_id')
		{
			$queryTermID = $wpdb->prepare( "SELECT DISTINCT tt.term_id from " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE $field = %s ", $value);
			//print_r($queryTermID . '<br /><br />');

			$termID = $wpdb->get_var($queryTermID);
			//print_r($termID . '<br /><br />');

			// If the term is a root parent, skip continue.
			if ( empty($termID) ) return;
		}
		else
		{
			$termID = $value;
		}


		$queryChildrenIDs = $wpdb->prepare( "SELECT DISTINCT * from " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE parent = %d ", $termID);
		//print_r($queryChildrenIDs . '<br /><br />');

		$terms = $wpdb->get_results($queryChildrenIDs);
		if ( empty($terms) ) return;

		foreach ($terms as $term)
		{
			// If the term is a root parent, skip continue.
			if ( $term->parent == 0 ) continue;

			$result = $this->getTermChildrenBy('term_id', $term->term_id, $taxonomy, $terms);

			$results = array_merge( (array) $results, (array) $result);
		}

		return array_merge( (array) $terms, (array) $results );
	}

	/**
	 * Adds a new term.
	 *
	 * $term - (string) Term name.
	 * $taxonomy - (string) taxonomy of the term to be updated
	 * $attributes - (array)	slug - (string)
	 * 							parent - (int)
	 * 							description - (string)
	 *
	 * @access public
	 * @param string $term
	 * @param string $taxonomy
	 * @param array  $attributes
	 * @return int                The term id.
	 */
	public function addTerm( $term, $taxonomy, $attributes ) {
		global $wpdb;

		$slug        = $attributes['slug'];
		$description = $attributes['description'];
		$parent      = $attributes['parent'];
		$slug        = $this->getUniqueSlug( $slug, $term );

		$wpdb->insert(
			CN_TERMS_TABLE,
			array(
				'name'       => $term,
				'slug'       => $slug,
				'term_group' => 0,
				),
			array(
				'%s',
				'%s',
				'%d',
				)
			);

		$termID = $wpdb->insert_id;

		$wpdb->insert(
			CN_TERM_TAXONOMY_TABLE,
			array(
				'term_id'     => $termID,
				'taxonomy'    => $taxonomy,
				'description' => $description,
				'count'       => 0,
				'parent'      => $parent,
				),
			array(
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				)
			);

		cnCache::clear( TRUE, 'transient', "cn_{$taxonomy}" );

		return $termID;
	}

	/**
	 * Updates a term.
	 *
	 * $termID - (int) ID of the term to be updated
	 * $taxonomy - (string) taxonomy of the term to be updated
	 * $attributes - (array)	name - (string)
	 * 							slug - (string)
	 * 							parent - (int)
	 * 							description - (string)
	 *
	 * @todo Update queries to properly use $wpdb->prepare rather than escaping.
	 * @param int $termID
	 * @param string $taxonomy
	 * @param array $attributes
	 * @return bool
	 */
	public function updateTerm( $termID, $taxonomy, $attributes ) {
		global $wpdb;

		$name        = $attributes['name'];
		$slug        = $attributes['slug'];
		$parent      = $attributes['parent'];
		$description = $attributes['description'];

		/*
		 * Empty the slug first so the update won't fail because
		 * of the need of a unique slug.
		 *
		 * Why can't a row be updated that must have a unique value
		 * if the slug value isn't being changed??????
		 */
		$wpdb->update(
			CN_TERMS_TABLE,
			array( 'slug' => '' ),
			array( 'term_id' => $termID ),
			'%s',
			'%d'
			);

		$slug = $this->getUniqueSlug( $slug, $name );

		$wpdb->update(
			CN_TERMS_TABLE,
			array(
				'name'       => $name,
				'slug'       => $slug,
				'term_group' => 0
				),
			array( 'term_id' => $termID ),
			array( '%s', '%s', '%d' ),
			'%d'
			);

		$ttID = $wpdb->get_var( $wpdb->prepare( "SELECT tt.term_taxonomy_id FROM " . CN_TERM_TAXONOMY_TABLE . " AS tt INNER JOIN " . CN_TERMS_TABLE . " AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id = %d", $taxonomy, $termID ) );

		$wpdb->update(
			CN_TERM_TAXONOMY_TABLE,
			array(
				'term_id'     => $termID,
				'taxonomy'    => $taxonomy,
				'description' => $description,
				'parent'      => $parent
				),
			array( 'term_taxonomy_id' => $ttID ),
			array( '%d', '%s', '%s', '%d' ),
			'%d'
			);

		cnCache::clear( TRUE, 'transient', "cn_{$taxonomy}" );

		/**
		 * @TODO: Error check the insert and return error
		 */
		return TRUE;
	}

	/**
	 * Remove a term from the database.
	 *
	 * If the term contains children terms, the children terms will be updated
	 * to the deleted term parent.
	 *
	 * @param int $id Term ID
	 * @param int $id Term Parent ID
	 * @param string $taxonomy Taxonomy Name
	 * @return bool
	 */
	public function deleteTerm($id, $parent, $taxonomy)
	{
		global $wpdb;
		$term = $this->getTermBy('id', $id, 'category');

		// Store the entry ids that are using the term to be deleted.
		$termRelations = $wpdb->get_col($wpdb->prepare( "SELECT DISTINCT entry_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d", $term->term_taxonomy_id ) );

		$childrenTerms = $wpdb->get_col($wpdb->prepare( "SELECT term_taxonomy_id FROM " . CN_TERM_TAXONOMY_TABLE . " WHERE parent = %d", (int) $id ) );

		// Move the children terms to the parent term.
		foreach ($childrenTerms as $childID)
		{
			/**
			 * @TODO: Error check the insert and return error
			 */
			$wpdb->query($wpdb->prepare("UPDATE " . CN_TERM_TAXONOMY_TABLE . " SET parent = %d WHERE parent	= %d", (int) $parent, (int) $id ));
		}

		// Delete the term relationships.
		// If delete fails return FALSE.
		$wpdb->query($wpdb->prepare("DELETE FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d", $term->term_taxonomy_id ));

		// Delete the term taxonomy.
		// If delete fails return FALSE.
		if (!$wpdb->query($wpdb->prepare("DELETE FROM " . CN_TERM_TAXONOMY_TABLE . " WHERE term_taxonomy_id = %d", $term->term_taxonomy_id ))) return FALSE;

		// Delete the term if no taxonomies use it.
		if ( !$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_TAXONOMY_TABLE . " WHERE term_id = %d", $id ) ) )
		{
			$wpdb->query( $wpdb->prepare( "DELETE FROM " . CN_TERMS_TABLE . " WHERE term_id = %d", $id ) );
		}

		/*
		 * Cycle through each of the entry ids that used the term that was deleted.
		 * If the count is null update the the term relationship to include the Uncategoried term.
		 * Then update the Uncategorized term count.
		 */
		foreach ($termRelations as $entryID)
		{
			if ( !$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d", $entryID ) ) )
			{
				// Retrieve the Uncategorized term data
				$term = $this->getTermBy('slug', 'uncategorized', 'category');

				$wpdb->query( $wpdb->prepare( "INSERT INTO " . CN_TERM_RELATIONSHIP_TABLE . " SET entry_id = %d, term_taxonomy_id = %d, term_order = 0", $entryID, $term->term_taxonomy_id) );

				$termCount = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d", $term->term_taxonomy_id) );
				$wpdb->query( $wpdb->prepare( "UPDATE " . CN_TERM_TAXONOMY_TABLE . " SET count = %d WHERE term_taxonomy_id = %d", $termCount, $term->term_taxonomy_id) );
			}
		}

		cnCache::clear( TRUE, 'transient', "cn_{$taxonomy}" );

		// If everthing went well, return TRUE.
		return TRUE;
	}

	/**
	 * Returns a unique sanitized slug for insertion in the database.
	 *
	 * @param string $slug
	 * @param string $term Name
	 * @return string
	 */
	private function getUniqueSlug($slug, $term)
	{
		global $wpdb;

		if (empty($slug))
		{
			//If the slug is empty assign the $slug the $term name
			$slug = $term;
		}

		// WP function -- formatting class
		$slug = sanitize_title($slug);

		$query = $wpdb->prepare( "SELECT slug FROM " . CN_TERMS_TABLE . " WHERE slug = %s", $slug );

		if ( $wpdb->get_var( $query ) )
		{
			$num = 2;
			do
			{
				$alt_slug = $slug . "-$num";
				$num++;
				$slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM " . CN_TERMS_TABLE . " WHERE slug = %s", $alt_slug ) );
			}
			while ( $slug_check );
			$slug = $alt_slug;
		}

		return $slug;

	}

	/**
	 * Creates the entry and term relationships.
	 *
	 * If the term $IDs is empty then the uncatergorized catergory is set as the relationship.
	 * NOTE: Only if the taxonomy is 'category'
	 *
	 * @param int $entryID
	 * @param array $termIDs
	 *
	 * @return bool
	 */
	public function setTermRelationships($entryID, $termIDs, $taxonomy)
	{
		/**
		 * @TODO: Return success/fail bool on insert.
		 */
		global $wpdb;

		// Purge all ralationships currently related to an entry if rationships exist.
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d", $entryID) ) )
		{
			// Before the purge, grab the current term relationships so the term counts can be properly updated.
			$previousTermIDs = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT term_taxonomy_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d", $entryID) );

			// Purge all term relationships.
			$this->deleteTermRelationships($entryID);
		}

		// Create the new relationships. Else if empty then the uncatorgorized category is set.
		if (!empty($termIDs))
		{
			foreach ($termIDs as $termID)
			{
				$termTaxonomyID = $wpdb->get_var( 'SELECT term_taxonomy_id FROM ' . CN_TERM_TAXONOMY_TABLE . ' WHERE term_id=' . $termID );
				$wpdb->query( $wpdb->prepare( "INSERT INTO " . CN_TERM_RELATIONSHIP_TABLE . " SET entry_id = %d, term_taxonomy_id = %d, term_order = 0", $entryID, $termTaxonomyID) );
			}
		}
		else
		{
			/*
			 * @TODO: this should only happen if the taxonomy is 'category'.
			 */

			// Retrieve the Uncategorized term data
			$term = $this->getTermBy('slug', 'uncategorized', 'category');

			// Set the $IDs array for updating the term counts.
			$termIDs[] = $term->term_taxonomy_id;

			$wpdb->query( $wpdb->prepare( "INSERT INTO " . CN_TERM_RELATIONSHIP_TABLE . " SET entry_id = %d, term_taxonomy_id = %d, term_order = 0", $entryID, $term->term_id) );
		}

		// Merge the entry's previous term IDs with the newly selected term IDs unless it already exists in the current term IDs array.
		if (!empty($previousTermIDs))
		{
			foreach ($previousTermIDs as $currentID)
			{
				if (!in_array($currentID, $termIDs))
				{
					$termIDs[] = $currentID;
				}
			}
		}

		// Now the term counts need to be updated.
		if (!empty($termIDs))
		{
			foreach ($termIDs as $termID)
			{
				$termTaxonomyID = $wpdb->get_var( 'SELECT term_taxonomy_id FROM ' . CN_TERM_TAXONOMY_TABLE . ' WHERE term_id=' . $termID );
				$termCount = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d", $termTaxonomyID) );
				$wpdb->query( $wpdb->prepare( "UPDATE " . CN_TERM_TAXONOMY_TABLE . " SET count = %d WHERE term_taxonomy_id = %d", $termCount, $termTaxonomyID) );

				// $termCount = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d", $termID) );
			}
		}

	}

	/**
	 * Retrieve the entry's term relationships.
	 *
	 * @param integer $entryID
	 *
	 * @return mixed | False or array of term relationships.
	 */
	public function getTermRelationships($entryID)
	{
		/**
		 * @TODO: Return success/fail bool on select.
		 */
		global $wpdb;

		$termRelationships = $wpdb->get_col( $wpdb->prepare( "SELECT t.term_id FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id INNER JOIN " . CN_TERM_RELATIONSHIP_TABLE . " AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = 'category' AND tr.entry_id = %d ", $entryID) );

		return $termRelationships;
	}

	/**
	 * Deletes all entry's relationships.
	 *
	 * @param interger $entryID
	 *
	 * @return bool
	 */
	public function deleteTermRelationships($entryID)
	{
		/**
		 * @TODO: Return success/fail bool on insert.
		 */
		global $wpdb;

		// Purge all relationships currently related to an entry if rationships exist.
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d", $entryID) ) )
		{
			// Before the purge, grab the current term relationships so the term counts can be properly updated.
			$termIDs = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT term_taxonomy_id FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d", $entryID) );

			$wpdb->query( $wpdb->prepare( "DELETE FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE entry_id = %d", $entryID) );
		}

		// Now the term counts need to be updated.
		if (!empty($termIDs))
		{
			foreach ($termIDs as $termID)
			{
				$termCount = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d", $termID) );
				$wpdb->query( $wpdb->prepare( "UPDATE " . CN_TERM_TAXONOMY_TABLE . " SET count = %d WHERE term_taxonomy_id = %d", $termCount, $termID) );
			}
		}
	}
}

class cnTerm {

	/**
	 * An array that contains the term parent relationship as array.
	 * key == the parent ID
	 * value == array of the child objects
	 *
	 * @access private
	 * @since  8.1
	 * @var array
	 */
	private static $termRelationships = array();

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
	 * @uses   $wpdb->get_row()
	 * @uses   $wpdb->prepare()
	 * @uses   sanitize_term()
	 * @uses   apply_filters()
	 *
	 * @param  int|object $term     If integer, will query from database. If object will apply filters and return $term.
	 * @param  string     $taxonomy Taxonomy name that $term is part of.
	 * @param  string     $output   Constant OBJECT, ARRAY_A, or ARRAY_N
	 * @param  string     $filter   Optional, default is raw or no WordPress defined filter will applied.
	 * @return mixed|null|WP_Error  Term data. Will return null if $term is empty. If taxonomy does not exist then WP_Error will be returned.
	 */
	private static function filter( $term, $taxonomy, $output = OBJECT, $filter = 'raw' ) {
		global $wpdb;

		// if ( empty( $term ) ) {

		// 	$error = new WP_Error('invalid_term', __('Empty Term'));
		// 	return $error;
		// }

		// if ( ! taxonomy_exists( $taxonomy ) ) {

		// 	$error = new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));
		// 	return $error;
		// }

		if ( is_object( $term ) && empty( $term->filter ) ) {

			// wp_cache_add( $term->term_id, $term, $taxonomy );
			$_term = $term;

		} else {

			if ( is_object( $term ) ) {

				$term = $term->term_id;
			}

			if ( ! $term = (int) $term ) {

				return NULL;
			}

			// if ( ! $_term = wp_cache_get( $term, $taxonomy ) ) {
			if ( TRUE ) {

				$_term = $wpdb->get_row( $wpdb->prepare( 'SELECT t.*, tt.* FROM ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND t.term_id = %d LIMIT 1', $taxonomy, $term ) );

				if ( ! $_term ) {

					return NULL;
				}

				// wp_cache_add($term, $_term, $taxonomy);
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
	 *    slug ( string )
	 *        Default: ''
	 *        Returns terms whose 'slug' matches this value.
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
	 *        Valid:   term_id | name | slug | term_group | parent | count
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
	 * @global $wpdb
	 * @uses   apply_filters()
	 * @uses   wp_parse_args()
	 * @uses   wp_parse_id_list()
	 * @uses   sanitize_title()
	 * @uses   $wpdb->prepare()
	 * @uses   like_escape()
	 * @uses   absint()
	 * @uses   $wpdb->get_results()
	 * @uses   self::filter()
	 * @uses   self::descendants()
	 * @uses   self::childrenIDs()
	 * @uses   self::padCounts()
	 * @uses   self::children()
	 *
	 * @param  string|array $taxonomies Taxonomy name or array of taxonomy names.
	 * @param  string|array $args       The values of what to search for when returning terms.
	 * @return array|WP_Error           Indexed array of term objects. Will return WP_Error, if any of $taxonomies do not exist.
	 */
	public static function get( $taxonomies = array( 'category' ), $atts = NULL ) {
		global $wpdb;

		$select  = array();
		$where   = array();
		$orderBy = array();

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
			'pad_counts'   => FALSE,
			'offset'       => 0,
			'number'       => 0,
			'search'       => '',
		);

		/*
		 * Filter the terms query arguments.
		 *
		 * @since 8.1
		 *
		 * @param array        $atts       An array of arguments.
		 * @param string|array $taxonomies A taxonomy or array of taxonomies.
		 */
		$atts = apply_filters( 'cn_get_terms_atts', $atts, $taxonomies );

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

		/*
		 * Construct the ORDER By query clause.
		 */
		if ( is_array( $atts['orderby'] ) ) {

			$orderBy[] = 'ORDER BY';
			$orderByQueryClause = '';
			$i = 0;

			foreach ( $atts['orderby'] as $orderby ) {

				if ( is_array( $atts['order'] ) && isset( $atts['order'][ $i ] ) ) {

					// Align with the first value in orderby.
					$order = $atts['order'][ $i ];

				} else {

					$order = is_array( $atts['order'] ) ? $atts['order'][0] : $atts['order'];
				}

				switch ( $atts['orderby'][ $i ] ) {

					case 'id':
					case 'term_id':
						$atts['orderby'][ $i ] = 't.term_id';
						break;

					case 'slug':
						$atts['orderby'][ $i ] = 't.slug';
						break;

					case 'term_group':
						$atts['orderby'][ $i ] = 't.term_group';
						break;

					case 'parent':
						$atts['orderby'][ $i ] = 'tt.parent';
						break;

					case 'count':
						$atts['orderby'][ $i ] = 'tt.count';
						break;

					default:

						$atts['orderby'][ $i ] = 't.name';
						break;
				}

				$order = strtoupper( $order );
				$order = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';

				$orderByQueryClause .= sprintf( ( $i > 0 ? ', ' : '' ) . '%s %s', $atts['orderby'][ $i ], $order );

				$i++;
			}

			if ( ! empty( $orderByQueryClause ) ) $orderBy[] = $orderByQueryClause;

		} else {

			switch ( $atts['orderby'] ) {

				case 'id':
				case 'term_id':
					$atts['orderby'] = 't.term_id';
					break;

				case 'slug':
					$atts['orderby'] = 't.slug';
					break;

				case 'term_group':
					$atts['orderby'] = 't.term_group';
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

				// orderby was a string but for some reason an array
				// was passed for the order so we assume the 0 index
				$order = $atts['order'][0];

			} else {

				$order = $atts['order'];
			}

			$order = strtoupper( $order );
			$order = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';

			$orderBy[] = sprintf( 'ORDER BY %s %s', $atts['orderby'], $order );
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
		$orderBy = apply_filters( 'cn_term_orderby', implode( ' ', $orderBy ), $atts, $taxonomies );

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
					(array) cnTerms::get( $taxonomies[0], array( 'child_of' => intval( $extrunk ), 'fields' => 'ids', 'hide_empty' => 0 ) )
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

			$slug    = sanitize_title($slug);
			$where[] = $wpdb->prepare( ' AND t.slug = %s', $atts['slug'] );
		}

		if ( ! empty( $atts['name__like'] ) ) {

			$atts['name__like'] = like_escape( $atts['name__like'] );
			$where[]            = $wpdb->prepare( 'AND t.name LIKE %s', '%' . $atts['name__like'] . '%' );
		}

		if ( ! empty( $atts['description__like'] ) ) {

			$atts['description__like'] = like_escape( $atts['description__like'] );
			$where[]                   = $wpdb->prepare( 'AND tt.description LIKE %s', '%' . $atts['description__like'] . '%' );
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

			$atts['search'] = like_escape( $atts['search'] );
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
				$order   = '';
				$select  = array( 'COUNT(*)' );
				break;

			case 'id=>name':
				$select = array( 't.term_id', 't.name' );
				break;

			case 'id=>slug':
				$select = array( 't.term_id', 't.slug' );
				break;
		}

		/*
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

		/*
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

		if ( $atts['child_of'] ) {

			$children = self::childrenIDs( reset( $taxonomies ) );

			if ( ! empty( $children ) ) {

				$terms = self::descendants( $atts['child_of'], $terms, reset( $taxonomies ) );
			}
		}

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

		/** This filter is documented in wp-includes/taxonomy */
		$terms = apply_filters( 'cn_terms', $terms, $taxonomies, $atts );

		return $terms;
	}

	/**
	 * Retrieves children of taxonomy as term IDs.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 * @uses   self::get()
	 * @param  string $taxonomy Taxonomy name.
	 * @return array  Empty if $taxonomy isn't hierarchical or returns children as term IDs.
	 */
	private static function childrenIDs( $taxonomy ) {

		// if ( !is_taxonomy_hierarchical($taxonomy) )
		// 	return array();
		// $children = get_option("{$taxonomy}_children");

		// if ( is_array( $children ) )
		// 	return $children;

		$children = array();
		$terms    = self::get( $taxonomy, array( 'get' => 'all', 'orderby' => 'id', 'fields' => 'id=>parent') );

		foreach ( $terms as $term_id => $parent ) {

			if ( $parent > 0 ){

				$children[ $parent ][] = $term_id;
			}

		}

		// update_option("{$taxonomy}_children", $children);

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
	 * @uses   self::descendants()
	 * @uses   self::childrenIDs()
	 * @uses   self::filter()
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

				// if ( is_wp_error( $term ) )
				// 	return $term;

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
	 * @access private
	 * @since  8.1
	 * @static
	 * @global $wpdb
	 * @uses   childrenIDs()
	 * @uses   is_user_logged_in()
	 * @uses   current_user_can()
	 * @uses   $wpdb->get_results()
	 * @param  array $terms List of Term IDs
	 * @param  string $taxonomy Term Context
	 * @return null Will break from function if conditions are not met.
	 */
	private static function padCounts( &$terms, $taxonomy ) {
		global $wpdb;

		// Grab an instance of the Connections object.
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

			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {

				if ( current_user_can( 'connections_view_public' ) ) $visibility[]                 = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[]                = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';

			} else {

				$visibility[] = $atts['visibility'];
			}

		} else {
			//var_dump( $instance->options->getAllowPublic() ); die;

			// Display the 'public' entries if the user is not required to be logged in.
			if ( $instance->options->getAllowPublic() ) $visibility[] = 'public';

			// Display the 'public' entries if the public override shortcode option is enabled.
			if ( $instance->options->getAllowPublicOverride() ) {
				if ( $atts['allow_public_override'] == TRUE ) $visibility[] = 'public';
			}

			// Display the 'public' & 'private' entries if the private override shortcode option is enabled.
			if ( $instance->options->getAllowPrivateOverride() ) {
				// If the user can view private entries then they should be able to view public entries too, so we'll add it. Just check to see if it is already set first.
				if ( ! in_array( 'public', $visibility ) && $atts['private_override'] == TRUE ) $visibility[] = 'public';
				if ( $atts['private_override'] == TRUE ) $visibility[] = 'private';
			}
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
	 * Reorganizes results returned from self::get() into parent/child relationship.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @uses   self::get()
	 * @uses   self::buildChildrenArray()
	 * @uses   self::getChildren()
	 * @uses   seld::isChild()
	 * @param  array  $taxonomies
	 * @param  array  $atts
	 * @return array
	 */
	public static function tree( $taxonomies = array( 'category' ), $atts = NULL ) {

		$terms = self::get( $taxonomies, $atts );

		/*
		 * Loop thru the results and build an array where key == parent ID and the value == the child objects
		 *
		 * NOTE: Currently $taxonomies does not need to be sent, it's not being used in the method. It's
		 * 		 being left in place for future use.
		 */
		foreach ( $terms as $term ) {

			self::buildChildrenArray( $term->term_id, $terms, $taxonomies );
		}

		/*
		 * Loop thru the results again adding the children objects from $this->termChildren to the parent object.
		 *
		 * NOTE: Currently $taxonomies does not need to be sent, it's not being used in the method. It's
		 * 		 being left in place for future use.
		 */
		foreach( $terms as $key => $term ) {

			$term->children = self::getChildren( $term->term_id, $terms, $taxonomies );
		}

		/*
		 * Loop thru the results once more and remove all child objects from the base array leaving only parent objects
		 */
		foreach( $terms as $key => $term ) {

			if ( self::isChild( $term->term_id ) ) unset( $terms[ $key ] );
		}

		//return $this->termChildren;
		return $terms;
	}

	private static function getChildren( $termID, $terms, $taxonomies ) {

		foreach ( $terms as $key => $term ) {

			if ( $termID == $term->parent ) {

				$termList[] = $term;
			}
		}

		if ( isset( $termList ) ) return $termList;
	}

	private static function buildChildrenArray( $termID, $terms, $taxonomies ) {

		foreach ( $terms as $term ) {

			// Skip the term if it is itself
			if ( $termID == $term->term_id ) continue;

			if ( $termID == $term->parent ) {

				self::$termRelationships[ $termID ][] = $term;
			}
		}
	}

	private static function isChild( $termID ) {

		$isChild = FALSE;

		foreach ( self::$termRelationships as $parentID => $children ) {

			foreach ( $children as $child ) {

				if ( $termID == $child->term_id ) {

					$isChild = TRUE;
				}
			}

		}

		if ( $isChild ) {

			return TRUE;

		} else {

			return FALSE;
		}

	}

}
