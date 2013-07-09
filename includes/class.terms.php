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
	 * @param array $taxonomies
	 * @param array $arguments [optional]
	 * @return array
	 */
	public function getTerms($taxonomies, $arguments = NULL)
	{
		global $wpdb;

		// If the term query has alread been run and the parent/child relationship built, return the stored version rather than quering/building again and again.
		if ( ! empty( $this->terms ) ) return $this->terms;

		$query = "SELECT t.*, tt.* from " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('$taxonomies') ORDER BY name";

		$terms = $wpdb->get_results($query);
		//print_r($terms);
		/*
		 * Loop thru the results and build an array where key == parent ID and the value == the child objects
		 *
		 * NOTE: Currently $taxonomies does not need to be sent, it's not being used in the method. It's
		 * 		 being left in place for future use.
		 */
		foreach ($terms as $term)
		{
			$this->buildChildrenArray($term->term_id, $terms, $taxonomies);
		}

		/*
		 * Loop thru the results again adding the children objects from $this->termChildren to the parent object.
		 *
		 * NOTE: Currently $taxonomies does not need to be sent, it's not being used in the method. It's
		 * 		 being left in place for future use.
		 */
		foreach($terms as $key => $term)
		{
			$term->children = $this->getChildren($term->term_id, $terms, $taxonomies);
		}

		/*
		 * Loop thru the results once more and remove all child objects from the base array leaving only parent objects
		 */
		foreach($terms as $key => $term)
		{
			if ($this->isChild($term->term_id)) unset($terms[$key]);
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
	public function getTermBy($field, $value, $taxonomy)
	{
		global $wpdb;

		if ( 'slug' == $field )
		{
			$field = 't.slug';
			$value = sanitize_title($value);
			if ( empty($value) ) return false;
		}
		else if ( 'name' == $field )
		{
			// Assume already escaped
			$value = stripslashes($value);
			$field = 't.name';
		} else
		{
			$field = 't.term_id';
			$value = (int) $value;
		}

		$term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND $field = %s LIMIT 1", $taxonomy, $value) );

		if ( !$term )
		{
			return FALSE;
		}
		else
		{
			return $term;
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
	 * @param int $term
	 * @param string $taxonomy
	 * @param array $attributes
	 * @return bool
	 */
	public function addTerm($term, $taxonomy, $attributes)
	{
		global $wpdb, $connections;

		$slug = $attributes['slug'];
		$description = $attributes['description'];
		$parent = $attributes['parent'];

		$slug = $this->getUniqueSlug($slug, $term);

		/**
		 * @TODO: Make sure the term doesn't exist before adding it.
		 * If term does exist, only the taxonomy table needs to be updated.
		 */
		$sql = "INSERT INTO " . CN_TERMS_TABLE . " SET
			name    	= '" . $wpdb->escape($term) . "',
			slug    	= '" . $wpdb->escape($slug) . "',
			term_group	= '0'";

		// If insert fails return NULL.
		$wpdb->query($sql);
		unset($sql);

		$sql = "INSERT INTO " . CN_TERM_TAXONOMY_TABLE . " SET
			term_id    	= '" . $wpdb->insert_id . "',
			taxonomy   	= '" . $wpdb->escape($taxonomy) . "',
			description	= '" . $wpdb->escape($description) . "',
			count		= '0',
			parent		= '" . $wpdb->escape($parent) . "'";

		/**
		 * @TODO: Error check the insert and return error
		 */
		$wpdb->query($sql);
		unset($sql);

		return TRUE;
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
		global $wpdb, $connections;

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
		$sql = "UPDATE " . CN_TERMS_TABLE . " SET
				slug		= ''
				WHERE term_id = '" . $wpdb->escape( $termID ) . "'";

		// If insert fails return NULL.
		if ( ! $wpdb->query( $sql ) ) return;
		unset( $sql );

		$slug = $this->getUniqueSlug( $slug, $name );

		$sql = "UPDATE " . CN_TERMS_TABLE . " SET
			name		= '" . $wpdb->escape( $name ) . "',
			slug		= '" . $wpdb->escape( $slug ) . "',
			term_group	= '0'
			WHERE term_id = '" . $wpdb->escape( $termID ) . "'";

		// If insert fails return NULL.
		if ( ! $wpdb->query( $sql ) ) return;
		unset( $sql );

		$ttID = $wpdb->get_var( $wpdb->prepare( "SELECT tt.term_taxonomy_id FROM " . CN_TERM_TAXONOMY_TABLE . " AS tt INNER JOIN " . CN_TERMS_TABLE . " AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id = %d", $taxonomy, $termID ) );

		$sql = "UPDATE " . CN_TERM_TAXONOMY_TABLE . " SET
			term_id		= '" . $wpdb->escape( $termID ) . "',
			taxonomy	= '" . $wpdb->escape( $taxonomy ) . "',
			description	= '" . $wpdb->escape( $description ) . "',
			parent		= '" . $wpdb->escape( $parent ) . "'
			WHERE term_taxonomy_id 	= '" . $wpdb->escape( $ttID ) . "'";

		/**
		 * @TODO: Error check the insert and return error
		 */
		$wpdb->query( $sql );
		unset( $sql );

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

				$termCount = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_TERM_RELATIONSHIP_TABLE . " WHERE term_taxonomy_id = %d", $termID) );
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