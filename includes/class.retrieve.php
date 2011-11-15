<?php

class cnRetrieve
{
	/**
	 * @return array
	 */
	public function entries( $suppliedAttr = array() )
	{
		global $wpdb, $connections, $current_user;
		
		get_currentuserinfo();
		
		$entryIDs = array();
		$validate = new cnValidate();
		$select = array();
		$from[] = CN_ENTRY_TABLE;
		$join = array();
		$where[] = 'WHERE 1=1';
		$orderBy = array();
		
		$permittedEntryTypes = array('individual', 'organization', 'family', 'connection_group');
		$permittedEntryStatus = array('approved', 'pending');
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['list_type'] = NULL;
			$defaultAttr['category'] = NULL;
			$defaultAttr['category_in'] = NULL;
			$defaultAttr['exclude_category'] = NULL;
			$defaultAttr['category_name'] = NULL;
			$defaultAttr['wp_current_category'] = FALSE;
			$defaultAttr['id'] = NULL;
			$defaultAttr['slug'] = NULL;
			$defaultAttr['family_name'] = NULL;
			$defaultAttr['last_name'] = NULL;
			$defaultAttr['title'] = NULL;
			$defaultAttr['organization'] = NULL;
			$defaultAttr['department'] = NULL;
			$defaultAttr['city'] = NULL;
			$defaultAttr['state'] = NULL;
			$defaultAttr['zip_code'] = NULL;
			$defaultAttr['country'] = NULL;
			$defaultAttr['visibility'] = NULL;
			$defaultAttr['status'] = array();
			$defaultAttr['order_by'] = NULL;
			$defaultAttr['limit'] = NULL;
			$defaultAttr['offset'] = NULL;
			$defaultAttr['allow_public_override'] = FALSE;
			$defaultAttr['private_override'] = FALSE;
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		/*
		 * If in a post get the category names assigned to the post.
		 */
		if ( $atts['wp_current_category'] && !is_page() )
		{
			// Get the current post categories.
			$wpCategories = get_the_category();
			
			// Build an array of the post categories.
			foreach ($wpCategories as $wpCategory)
			{
				$categoryNames[] = $wpCategory->cat_name;
			}
		}
		
		/*
		 * Build and array of the supplied category names and their children.
		 */
		if ( !empty($atts['category_name']) )
		{
			// If value is a string convert to an array.
			if ( !is_array($atts['category_name']) )
			{
				$atts['category_name'] = explode(',', $atts['category_name']);
			}
			
			foreach ( $atts['category_name'] as $categoryName )
			{
				// Add the parent category to the array and remove any whitespace from the begining/end of the name in case the user added it when using the shortcode.
				$categoryNames[] = trim($categoryName);
				
				// Retrieve the children categories
				$results = $this->categoryChildren('name', $categoryName);
				
				foreach ( (array) $results as $term )
				{
					if ( !in_array($term->name, $categoryNames) ) $categoryNames[] = $term->name;
				}
			}
		}
		
		/*
		 * Build an array of all the categories and their children based on the supplied category IDs.
		 */
		if ( !empty($atts['category']) )
		{
			// If value is a string, string the white space and covert to an array.
			if ( !is_array($atts['category']) )
			{
				$atts['category'] = str_replace(' ', '', $atts['category']);
				
				$atts['category'] = explode(',', $atts['category']);
			}
			
			foreach ($atts['category'] as $categoryID)
			{
				// Add the parent category ID to the array.
				$categoryIDs[] = $categoryID;
				
				// Retrieve the children categories
				$results = $this->categoryChildren('term_id', $categoryID);
				//print_r($results);
				
				foreach ( (array) $results as $term )
				{
					if (!in_array($term->term_id, $categoryIDs) ) $categoryIDs[] = $term->term_id;
				}
			}
		}
		
		/*
		 * Exclude the specified categories by ID.
		 */
		if ( ! empty($atts['exclude_category']) )
		{
			// If value is a string, string the white space and covert to an array.
			if ( ! is_array($atts['exclude_category']) )
			{
				$atts['exclude_category'] = str_replace(' ', '', $atts['exclude_category']);
				
				$atts['exclude_category'] = explode(',', $atts['exclude_category']);
			}
			
			$categoryIDs = array_diff( (array) $categoryIDs, $atts['exclude_category']);
		}
		
		// Convert the supplied category IDs $atts['category_in'] to an array.
		if ( !empty($atts['category_in']) )
		{
			if ( !is_array($atts['category_in']) )
			{
				// Trim the space characters if present.
				$atts['category_in'] = str_replace(' ', '', $atts['category_in']);
				
				// Convert to array.
				$atts['category_in'] = explode(',', $atts['category_in']);
			}
			
			// Exclude any category IDs that may have been set.
			$atts['category_in'] = array_diff( $atts['category_in'], (array) $atts['exclude_category'] );
			
			// Build the query to retrieve entry IDs that are assigned to all the supplied category IDs; operational AND.
			$sql = 'SELECT DISTINCT tr.entry_id FROM ' . CN_TERM_RELATIONSHIP_TABLE . ' AS tr 
					INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) 
					WHERE 1=1 AND tt.term_id IN (\'' . implode("', '", $atts['category_in']) . '\') GROUP BY tr.entry_id HAVING COUNT(*) = ' . count($atts['category_in']) . ' ORDER BY tr.entry_id';
			
			/*
			 * @todo What was I thinking here? I should have used $wpdb->get_col() so the results do not need looped thru for the entry ID.
			 * Leave it for now since it works and I need to more time for testing than I have if I change it now.
			 */
			// Store the entryIDs that exist on all of the supplied category IDs
			$results = $wpdb->get_results($sql);
			//print_r($results);
			
			if ( ! empty($results) )
			{
				foreach ( $results as $result )
				{
					$entryIDs[] = $result->entry_id;
				}
			}
			else
			{
				/**
				 * @todo This is hack. This is being set because if no results are returned then this will not pass
				 * the empty() check for the entry IDs and then the main query will return all entries. Maybe it would
				 * be best to just return an empty array. Let's sleep on it.
				 */
				$entryIDs = array('NONE');
			}
			
			/*
			 * This is the query to use to return entry IDs that are in the same categories. The COUNT value
			 * should equal the number of category IDs in the IN() statement.
			 
			   SELECT DISTINCT tr.entry_id FROM `wp_connections_term_relationships` AS tr 
			   INNER JOIN `wp_connections_term_taxonomy` AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
			   WHERE 1=1 AND tt.term_id IN ('51','73','76') GROUP BY tr.entry_id HAVING COUNT(*) = 3 ORDER BY tr.entry_id
			 */
		}
		
		if ( ! empty($categoryIDs) || ! empty($categoryNames) )
		{
			// Set the query string to INNER JOIN the term relationship and taxonomy tables.
			$join[] = 'INNER JOIN ' . CN_TERM_RELATIONSHIP_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_TERM_RELATIONSHIP_TABLE . '.entry_id )';
			$join[] = 'INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' ON ( ' . CN_TERM_RELATIONSHIP_TABLE . '.term_taxonomy_id = ' . CN_TERM_TAXONOMY_TABLE . '.term_taxonomy_id )';
			$join[] = 'INNER JOIN ' . CN_TERMS_TABLE . ' ON ( ' . CN_TERMS_TABLE . '.term_id = ' . CN_TERM_TAXONOMY_TABLE . '.term_id )';
			
			// Set the query string to return entries within the category taxonomy.
			$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.taxonomy = \'category\'';
			
			if ( ! empty($categoryIDs) )
			{
				$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.term_id IN (\'' . implode("', '", $categoryIDs) . '\')';
				
				unset( $categoryIDs );
			}
			
			if ( ! empty($categoryNames) )
			{
				$where[] = 'AND ' . CN_TERMS_TABLE . '.name IN (\'' . implode("', '", (array) $categoryNames ) . '\')';
				
				unset( $categoryNames );
			}
		}
		
		/*
		 * // START --> Set up the query to only return the entries that match the supplied IDs.
		 *    NOTE: This includes the entry IDs returned for category_in.
		 */
			// Convert the supplied IDs $atts['id'] to an array.
			if ( ! is_array($atts['id']) && ! empty($atts['id']))
			{
				// Trim the space characters if present.
				$atts['id'] = str_replace(' ', '', $atts['id']);
				
				// Convert to array.
				$atts['id'] = explode(',', $atts['id']);
			}
			
			// Set query string to return specific entries.
			if ( ! empty($atts['id']) || ! empty($entryIDs) ) $where[] = 'AND `id` IN (\'' . implode("', '", array_unique( array_merge( (array) $atts['id'], (array) $entryIDs ) ) ) . '\')';
		/*
		 * // END --> Set up the query to only return the entries that match the supplied IDs.
		 */
		
		/*
		 * // START --> Set up the query to only return the entry that matches the supplied slug.
		 */
			if ( ! empty($atts['slug']) )
			{
				// Trim the white space from the ends.
				$atts['slug'] = trim($atts['slug']);
				
				$where[] = $wpdb->prepare( 'AND slug = %s' , $atts['slug'] );
			}
		/*
		 * // END --> Set up the query to only return the entry that matches the supplied slug.
		 */
		
		/*
		 * // START --> Set up the query to only return the entries that match the supplied entry type.
		 */
			// Convert the supplied entry types $atts['list_type'] to an array.
			if ( ! is_array($atts['list_type']) && ! empty($atts['list_type']) )
			{
				// Trim the space characters if present.
				$atts['list_type'] = str_replace(' ', '', $atts['list_type']);
				
				// Convert to array.
				$atts['list_type'] = explode(',', $atts['list_type']);
			}
			
			// Set query string for entry type.
			if ( ! empty($atts['list_type']) && (bool) array_intersect($atts['list_type'], $permittedEntryTypes) )
			{
				// Compatibility code to make sure any ocurrences of the deprecated entry type connections_group is changed to entry type family
				$atts['list_type'] = str_replace('connection_group', 'family', $atts['list_type']);
				
				$where[] = 'AND `entry_type` IN (\'' . implode("', '", (array) $atts['list_type']) . '\')';
			}
		/*
		 * // END --> Set up the query to only return the entries that match the supplied entry type.
		 */
		
		/*
		 * // START --> Set up the query to only return the entries that match the supplied filters.
		 */
			if ( ! empty($atts['family_name']) ) $where[] = $wpdb->prepare( 'AND `family_name` = %s' , $atts['family_name'] );
			if ( ! empty($atts['last_name']) ) $where[] = $wpdb->prepare( 'AND `last_name` = %s' , $atts['last_name'] );
			if ( ! empty($atts['title']) ) $where[] = $wpdb->prepare( 'AND `title` = %s' , $atts['title'] );
			if ( ! empty($atts['organization']) ) $where[] = $wpdb->prepare( 'AND `organization` = %s' , $atts['organization'] );
			if ( ! empty($atts['department']) ) $where[] = $wpdb->prepare( 'AND `department` = %s' , $atts['department'] );
			
			if ( ! empty($atts['city']) || ! empty($atts['state']) || ! empty($atts['zip_code']) || ! empty($atts['country']) )
			{
				if ( ! isset($join['address']) ) $join['address'] = 'INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id )';
				
				if ( ! empty($atts['city']) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.city = %s' , $atts['city'] );
				if ( ! empty($atts['state']) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.state = %s' , $atts['state'] );
				if ( ! empty($atts['zip_code']) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.zipcode = %s' , $atts['zip_code'] );
				if ( ! empty($atts['country']) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.country = %s' , $atts['country'] );
			}
		/*
		 * // END --> Set up the query to only return the entries that match the supplied filters.
		 */
		
		/*
		 * // START --> Set up the query to only return the entries based on user permissions.
		 */
			if ( is_user_logged_in() )
			{
				if ( !$atts['visibility'] )
				{
					if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
					if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
					if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
				}
				else
				{
					$visibility[] = $atts['visibility'];
				}
			}
			else
			{
				if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
				if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
				if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
			}
			
			$where[] = 'AND ' . CN_ENTRY_TABLE . '.visibility IN (\'' . implode("', '", (array) $visibility) . '\')';
		/*
		 * // END --> Set up the query to only return the entries based on user permissions.
		 */
		
		/*
		 * // START --> Set up the query to only return the entries based on status.
		 */
			// Convert the supplied entry statuses $atts['status'] to an array.
			if ( ! is_array($atts['status']) && ! empty($atts['status']) )
			{
				// Trim the space characters if present.
				$atts['status'] = str_replace(' ', '', $atts['status']);
				
				// Convert to array.
				$atts['status'] = explode(',', $atts['status']);
			}
			else
			{
				// Query the approved entries
				$atts['status'] = array('approved');
			}
			
			if ( is_user_logged_in() )
			{
				// if 'all' was supplied, set the array to all the permitted entry status types.
				if ( in_array('all', $atts['status']) ) $atts['status'] = $permittedEntryStatus;
				
				// Limit the viewable status per role capability assigned to the current user.
				if ( current_user_can('connections_edit_entry') )
				{
					$userPermittedEntryStatus = array('approved', 'pending');
					
					$atts['status'] = array_intersect($userPermittedEntryStatus, $atts['status']);
				}
				elseif ( current_user_can('connections_edit_entry_moderated') )
				{
					$userPermittedEntryStatus = array('approved');
					
					$atts['status'] = array_intersect($userPermittedEntryStatus, $atts['status']);
				}
				else
				{
					$userPermittedEntryStatus = array('approved');
					
					$atts['status'] = array_intersect($userPermittedEntryStatus, $atts['status']);
				}
			}
			else
			{
				// If no user is logged in, set the status for the query to approved.
				$atts['status'] = array('approved');
			}
			
			$where[] = 'AND ' . CN_ENTRY_TABLE . '.status IN (\'' . implode("', '", $atts['status']) . '\')';
		/*
		 * // END --> Set up the query to only return the entries based on status.
		 */
		
		/*
		 * // START --> Build the ORDER BY query segment.
		 */
			if ( empty($atts['order_by']) )
			{
				// Setup the default sort order if none were supplied.
				$orderBy[] = /*CN_ENTRY_TABLE .*/ 'sort_column';
				$orderBy[] = /*CN_ENTRY_TABLE .*/ 'last_name';
				$orderBy[] = /*CN_ENTRY_TABLE .*/ 'first_name';
			}
			else
			{
				$orderFields = array(
									'id',
									'date_added',
									'date_modified',
									'first_name',
									'last_name',
									'title',
									'organization',
									'department',
									'city',
									'state',
									'zipcode',
									'country',
									'birthday',
									'anniversary'
									);
				
				$orderFlags = array(
									'SPECIFIED' => 'SPECIFIED',
									'RANDOM' => 'RANDOM',
									'SORT_ASC' => 'ASC',
									'SORT_DESC' => 'DESC'
									);
				
				
				// Build an array of each field to order by and its sort order.
				$orderByFields = explode( ',' , $atts['order_by'] );
				
				// For each field the sort order can be defined.
				foreach ( $orderByFields as $orderByField )
				{
					$orderByAtts[] = explode( '|' , $orderByField );
				}
				
				// Build the ORDER BY query segment
				foreach ( $orderByAtts as $field )
				{
					// Trim any spaces the user may have supplied and set it to be lowercase.
					$field[0] = strtolower( trim( $field[0] ) );
					
					// Check to make sure the supplied field is one of the valid fields to order by.
					if ( in_array( $field[0] , $orderFields ) )
					{
						// The date_modified actually maps to the ts column in the db.
						if ( $field[0] == 'date_modified' ) $field[0] = 'ts';
						
						// If one of the order fields is an address region add the INNER JOIN to the CN_ENTRY_ADDRESS_TABLE
						if ( $field[0] == 'city' || $field[0] == 'state' || $field[0] == 'zipcode' || $field[0] == 'country' )
						{
							if ( ! isset($join['address']) ) $join['address'] = 'INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id )';
						}
						
						// Check to see if an order flag was set and is a valid order flag.
						if ( isset( $field[1] ) )
						{
							// Trim any spaces the user might have added and change the string to uppercase..
							$field[1] = strtoupper( trim( $field[1] ) );
							
							/*
							 * The SPECIFIED and RANDOM order flags are special use and should only be used with the id sort field.
							 * Set the default sort flag if it was use on any other sort field than id.
							 */
							if ( ( $orderFlags[$field[1]] == 'SPECIFIED' || $orderFlags[$field[1]] == 'RANDOM' ) && $field[0] != 'id' ) $field[1] = 'SORT_ASC';
							
							switch ( $orderFlags[$field[1]] )
							{
								/*
								 * Order the results based on the order of the supplied entry IDs
								 */
								case 'SPECIFIED':
									if ( ! empty($atts['id']) )
									{
										$orderBy = array('FIELD( id, ' . implode(', ', (array) $atts['id'] ) . ' )');
									}
									break;
								
								/*
								 * Randomize the order of the results.
								 */
								case 'RANDOM':
									/*
									 * Unfortunately this doesn't work when the joins for categories are added to the query.
									 * Keep this around to see if it can be made to work.
									 */
									/*$from = array('(SELECT id FROM wp_connections WHERE 1=1 ORDER BY RAND() ) AS cn_random');
									$join[] = 'JOIN ' . CN_ENTRY_TABLE . ' ON (' . CN_ENTRY_TABLE . '.id = cn_random.id)';*/
									
									/*
									 * @TODO: This seems fast enough, better profiling will need to be done.
									 * @TODO: The session ID can be used as the seed for RAND() to support randomized paginated results. 
									 */
									$select[] = CN_ENTRY_TABLE . '.id*0+RAND() AS random';
									$orderBy = array('random');
									break;
								
								/*
								 * Return the results in ASC or DESC order.
								 */
								default:
									// If a user included a sort flag that is invalid/mis-spelled it is skipped since it can not be used.
									if ( ! array_key_exists( $field[1] , $orderFlags ) )
									{
										$orderBy[] = $field[0];
									}
									else
									{
										$orderBy[] = $field[0] . ' ' . $orderFlags[$field[1]];
									}
									
									break;
							}
							
						}
						else
						{
							$orderBy[] = $field[0];
						}
					}
				}
			}
			
			( empty($orderBy) ) ? $orderBy = '' : $orderBy = 'ORDER BY ' . implode(', ', $orderBy);
		/*
		 * // END --> Build the ORDER BY query segment.
		 */
		
		/*
		 * // START --> Set up the query LIMIT and OFFSET.
		 */
			( empty($atts['limit']) ) ? $limit = NULL : $limit = ' LIMIT ' . $atts['limit'] . ' ';
			( empty($atts['offset']) ) ? $offset = NULL : $offset = ' OFFSET ' . $atts['offset'] . ' ';
		/*
		 * // END --> Set up the query LIMIT and OFFSET.
		 */
		
		/*
		 * // START --> Build the SELECT query segment.
		 */
			$select[] = CN_ENTRY_TABLE . '.*';
			$select[] = 'CASE `entry_type`
						  WHEN \'individual\' THEN `last_name`
						  WHEN \'organization\' THEN `organization`
						  WHEN \'connection_group\' THEN `family_name`
						  WHEN \'family\' THEN `family_name`
						END AS `sort_column`';
		/*
		 * // START --> Build the SELECT query segment.
		 */
		
		
		$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . implode(', ', $select) . 'FROM ' . implode(', ', $from) . ' ' . implode(' ', $join) . ' ' . implode(' ', $where) . ' ' . $orderBy . ' ' . $limit . $offset;
		
		$results = $wpdb->get_results($sql);
		
		// The most recent query to have been executed by cnRetrieve::entries
		$connections->lastQuery = $wpdb->last_query;
		
		// The most recent query error to have been generated by cnRetrieve::entries
		$connections->lastQueryError = $wpdb->last_error;
		
		// ID generated for an AUTO_INCREMENT column by the most recent INSERT query.
		$connections->lastInsertID = $wpdb->insert_id; 
		
		// The number of rows returned by the last query.
		$connections->resultCount = $wpdb->num_rows;
		
		// The number of rows returned by the last query without the limit clause set
		$foundRows = $wpdb->get_results('SELECT FOUND_ROWS()'); 
		$connections->resultCountNoLimit = $foundRows[0]->{'FOUND_ROWS()'}; 
		
		// The total number of entries based on user permissions.
		$connections->recordCount = $this->recordCount($atts['allow_public_override'], $atts['private_override']);
		
		// The total number of entries based on user permissions with the status set to 'pending'
		$connections->recordCountPending = $this->recordCount($atts['allow_public_override'], $atts['private_override'], array('pending') );
		
		// The total number of entries based on user permissions with the status set to 'approved'
		$connections->recordCountApproved = $this->recordCount($atts['allow_public_override'], $atts['private_override'], array('approved') );
		
		/*
		 * Reset the pagination filter for the current user, remove the offset from the query and re-run the
		 * query if the offset for the query is greater than the record count with no limit set in the query.
		 */
		if ( $atts['offset'] > $connections->resultCountNoLimit )
		{
			$connections->currentUser->resetFilterPage('manage');
			unset( $atts['offset'] );
			$results = $this->entries( $atts );
		}
		
		return $results;
		
		// Return the results ordered.
		//return $this->orderBy($results, $atts['order_by'], $atts['id']);
	}
	
	public function entry($id)
	{
		global $wpdb;
		return $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'connections WHERE id="' . $wpdb->escape($id) . '"');
	}
	
	public function entryCategories($id)
	{
		global $wpdb;
		
		// Retrieve the categories.
		$results =  $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id INNER JOIN " . CN_TERM_RELATIONSHIP_TABLE . " AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = 'category' AND tr.entry_id = %d ", $id) );
		//SELECT t.*, tt.* FROM wp_connections_terms AS t INNER JOIN wp_connections_term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN wp_connections_term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = 'category' AND tr.entry_id = 325
		
		if ( !empty($results) )
		{
			usort($results, array(&$this, 'sortTermsByName') );
		}
		
		return $results;
	}
	
	/**
	 * Returns as an array of objects the addresses per the defined options.
	 * 
	 * $atts['id'] (int) Retrieve the addresses of the specified entry by entry id.
	 * $atts['preferred'] (bool) Retrieve the preferred entry address; id must be supplied.
	 * $atts['type'] (array) || (string) Retrieve specific address types, id must be supplied.
	 * $atts['city'] (array) || (string) Retrieve addresses in a specific city; id is optional.
	 * $atts['state'] (array) || (string) Retrieve addresses in a specific state; id is optional.
	 * $atts['zipcode'] (array) || (string) Retrieve addresses in a specific zipcode; id is optional.
	 * $atts['country'] (array) || (string) Retrieve addresses in a specific country; id is optional.
	 * $atts['coordinates'] (array) Retrieve addresses in with specific coordinates; id is optional. Both latitude and longitude must be supplied.
	 * 
	 * @param array $suppliedAttr Accepted values as noted above.
	 * @param bool $returnIDs Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the address data will be returned.
	 * @return array
	 */
	public function addresses( $suppliedAttr, $returnData = TRUE )
	{
		global $wpdb, $connections, $current_user;
		
		get_currentuserinfo();
		$validate = new cnValidate();
		$where[] = 'WHERE 1=1';
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['id'] = NULL;
			$defaultAttr['preferred'] = NULL;
			$defaultAttr['type'] = NULL;
			$defaultAttr['city'] = NULL;
			$defaultAttr['state'] = NULL;
			$defaultAttr['zipcode'] = NULL;
			$defaultAttr['country'] = NULL;
			$defaultAttr['coordinates'] = array();
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		extract( $atts );
		
		
		if ( ! empty($id) )
		{
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );
			
			if ( ! empty($preferred) )
			{
				$where[] = $wpdb->prepare( 'AND `preferred` = $d', (bool) $preferred );
			}
			
			if ( ! empty($type) )
			{
				if ( ! is_array($type) ) $type = explode( ',' , trim($type) );
				
				$where[] = stripslashes ( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode("', '", (array) $type) ) );
			}
		}
		
		if ( ! empty($city) )
		{
			if ( ! is_array($city) ) $city = explode( ',' , trim($city) );
			
			$where[] = stripslashes ( $wpdb->prepare( 'AND `city` IN (\'%s\')', implode("', '", (array) $city) ) );
		}
		
		if ( ! empty($state) )
		{
			if ( ! is_array($state) ) $state = explode( ',' , trim($state) );
			
			$where[] = stripslashes ( $wpdb->prepare( 'AND `state` IN (\'%s\')', implode("', '", (array) $state) ) );
		}
		
		if ( ! empty($zipcode) )
		{
			if ( ! is_array($zipcode) ) $zipcode = explode( ',' , trim($zipcode) );
			
			$where[] = stripslashes ( $wpdb->prepare( 'AND `zipcode` IN (\'%s\')', implode("', '", (array) $zipcode) ) );
		}
		
		if ( ! empty($country) )
		{
			if ( ! is_array($country) ) $country = explode( ',' , trim($country) );
			
			$where[] = stripslashes ( $wpdb->prepare( 'AND `country` IN (\'%s\')', implode("', '", (array) $country) ) );
		}
		
		if ( ! empty($coordinates) )
		{
			if ( ! empty($coordinates['latitude']) && ! empty($coordinates['longitude']) )
			{
				$where[] = $wpdb->prepare( 'AND `latitude` = %d', $coordinates['latitude'] );
				$where[] = $wpdb->prepare( 'AND `longitude` = %d', $coordinates['longitude'] );
			}
		}
		
		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() )
		{
			if ( !$atts['visibility'] )
			{
				if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
				if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
				if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
			}
			else
			{
				$visibility[] = $atts['visibility'];
			}
		}
		else
		{
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}
		
		if ( ! empty($visibility) ) $where[] = 'AND `visibility` IN (\'' . implode("', '", (array) $visibility) . '\')';
		
		if ( $returnData )
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_ADDRESS_TABLE . '.* 
					
					FROM ' . CN_ENTRY_ADDRESS_TABLE . ' ' . ' ' .
					
					implode(' ', $where) . ' ' . 
					
					'ORDER BY `order`';
			
			//print_r($sql);
			
			$results = $wpdb->get_results($sql);
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id 
					
					FROM ' . CN_ENTRY_ADDRESS_TABLE . ' ' . ' ' . implode(' ', $where);
			
			//print_r($sql);
			$results = $wpdb->get_col($sql);
		}
		
		if ( empty($results) ) return array();
		
		//print_r($results);
		return $results;
	}
	
	/**
	 * Returns as an array of objects containing the phone numbers per the defined options.
	 * 
	 * $atts['id'] (int) Retrieve the phone numbers of the specified entry by entry id.
	 * $atts['preferred'] (bool) Retrieve the preferred phone number; id must be supplied.
	 * $atts['type'] (array) || (string) Retrieve specific phone number types, id must be supplied.
	 * 
	 * @param array $suppliedAttr Accepted values as noted above.
	 * @param bool $returnIDs Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the phone number data will be returned.
	 * @return array
	 */
	public function phoneNumbers( $suppliedAttr, $returnData = TRUE )
	{
		global $wpdb, $connections, $current_user;
		
		get_currentuserinfo();
		$validate = new cnValidate();
		$where[] = 'WHERE 1=1';
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['id'] = NULL;
			$defaultAttr['preferred'] = NULL;
			$defaultAttr['type'] = NULL;
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		extract( $atts );
		
		
		if ( ! empty($id) )
		{
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );
			
			if ( ! empty($preferred) )
			{
				$where[] = $wpdb->prepare( 'AND `preferred` = $d', (bool) $preferred );
			}
			
			if ( ! empty($type) )
			{
				if ( ! is_array($type) ) $type = explode( ',' , trim($type) );
				
				$where[] = stripslashes ( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode("', '", (array) $type) ) );
			}
		}
		
		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() )
		{
			if ( !$atts['visibility'] )
			{
				if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
				if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
				if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
			}
			else
			{
				$visibility[] = $atts['visibility'];
			}
		}
		else
		{
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}
		
		if ( ! empty($visibility) ) $where[] = 'AND `visibility` IN (\'' . implode("', '", (array) $visibility) . '\')';
		
		if ( $returnData )
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_PHONE_TABLE . '.* 
					
					FROM ' . CN_ENTRY_PHONE_TABLE . ' ' . ' ' .
					
					implode(' ', $where) . ' ' . 
					
					'ORDER BY `order`';
			
			//print_r($sql);
			
			$results = $wpdb->get_results($sql);
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_PHONE_TABLE . '.entry_id 
					
					FROM ' . CN_ENTRY_PHONE_TABLE . ' ' . ' ' . implode(' ', $where);
			
			//print_r($sql);
			$results = $wpdb->get_col($sql);
		}
		
		if ( empty($results) ) return array();
		
		//print_r($results);
		return $results;
	}
	
	/**
	 * Returns as an array of objects containing the email addresses per the defined options.
	 * 
	 * $atts['id'] (int) Retrieve the addresses of the specified entry by entry id.
	 * $atts['preferred'] (bool) Retrieve the preferred entry address; id must be supplied.
	 * $atts['type'] (array) || (string) Retrieve specific address types, id must be supplied.
	 * 
	 * @param array $suppliedAttr Accepted values as noted above.
	 * @param bool $returnIDs Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the email address data will be returned.
	 * @return array
	 */
	public function emailAddresses( $suppliedAttr, $returnData = TRUE )
	{
		global $wpdb, $connections, $current_user;
		
		get_currentuserinfo();
		$validate = new cnValidate();
		$where[] = 'WHERE 1=1';
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['id'] = NULL;
			$defaultAttr['preferred'] = NULL;
			$defaultAttr['type'] = NULL;
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		extract( $atts );
		
		
		if ( ! empty($id) )
		{
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );
			
			if ( ! empty($preferred) )
			{
				$where[] = $wpdb->prepare( 'AND `preferred` = $d', (bool) $preferred );
			}
			
			if ( ! empty($type) )
			{
				if ( ! is_array($type) ) $type = explode( ',' , trim($type) );
				
				$where[] = stripslashes ( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode("', '", (array) $type) ) );
			}
		}
		
		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() )
		{
			if ( !$atts['visibility'] )
			{
				if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
				if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
				if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
			}
			else
			{
				$visibility[] = $atts['visibility'];
			}
		}
		else
		{
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}
		
		if ( ! empty($visibility) ) $where[] = 'AND `visibility` IN (\'' . implode("', '", (array) $visibility) . '\')';
		
		if ( $returnData )
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_EMAIL_TABLE . '.* 
					
					FROM ' . CN_ENTRY_EMAIL_TABLE . ' ' . ' ' .
					
					implode(' ', $where) . ' ' . 
					
					'ORDER BY `order`';
			
			//print_r($sql);
			
			$results = $wpdb->get_results($sql);
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_EMAIL_TABLE . '.entry_id 
					
					FROM ' . CN_ENTRY_EMAIL_TABLE . ' ' . ' ' . implode(' ', $where);
			
			//print_r($sql);
			$results = $wpdb->get_col($sql);
		}
		
		if ( empty($results) ) return array();
		
		//print_r($results);
		return $results;
	}
	
	/**
	 * Returns as an array of objects containing the IM IDs per the defined options.
	 * 
	 * $atts['id'] (int) Retrieve the IM IDs of the specified entry by entry id.
	 * $atts['preferred'] (bool) Retrieve the preferred entry IM ID; id must be supplied.
	 * $atts['type'] (array) || (string) Retrieve specific IM ID types, id must be supplied.
	 * 
	 * @param array $suppliedAttr Accepted values as noted above.
	 * @param bool $returnData Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the IM IDs data will be returned.
	 * @return array
	 */
	public function imIDs( $suppliedAttr, $returnData = TRUE )
	{
		global $wpdb, $connections, $current_user;
		
		get_currentuserinfo();
		$validate = new cnValidate();
		$where[] = 'WHERE 1=1';
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['id'] = NULL;
			$defaultAttr['preferred'] = NULL;
			$defaultAttr['type'] = NULL;
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		extract( $atts );
		
		
		if ( ! empty($id) )
		{
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );
			
			if ( ! empty($preferred) )
			{
				$where[] = $wpdb->prepare( 'AND `preferred` = $d', (bool) $preferred );
			}
			
			if ( ! empty($type) )
			{
				if ( ! is_array($type) ) $type = explode( ',' , trim($type) );
				
				$where[] = stripslashes ( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode("', '", (array) $type) ) );
			}
		}
		
		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() )
		{
			if ( !$atts['visibility'] )
			{
				if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
				if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
				if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
			}
			else
			{
				$visibility[] = $atts['visibility'];
			}
		}
		else
		{
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}
		
		if ( ! empty($visibility) ) $where[] = 'AND `visibility` IN (\'' . implode("', '", (array) $visibility) . '\')';
		
		if ( $returnData )
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_MESSENGER_TABLE . '.* 
					
					FROM ' . CN_ENTRY_MESSENGER_TABLE . ' ' . ' ' .
					
					implode(' ', $where) . ' ' . 
					
					'ORDER BY `order`';
			
			//print_r($sql);
			
			$results = $wpdb->get_results($sql);
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_MESSENGER_TABLE . '.entry_id 
					
					FROM ' . CN_ENTRY_MESSENGER_TABLE . ' ' . ' ' . implode(' ', $where);
			
			//print_r($sql);
			$results = $wpdb->get_col($sql);
		}
		
		if ( empty($results) ) return array();
		
		//print_r($results);
		return $results;
	}
	
	/**
	 * Returns as an array of objects containing the social media networks per the defined options.
	 * 
	 * $atts['id'] (int) Retrieve the social of the specified entry by entry id.
	 * $atts['preferred'] (bool) Retrieve the preferred entry social network; id must be supplied.
	 * $atts['type'] (array) || (string) Retrieve specific social network types, id must be supplied.
	 * 
	 * @param array $suppliedAttr Accepted values as noted above.
	 * @param bool $returnData Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the social network data will be returned.
	 * @return array
	 */
	public function socialMedia( $suppliedAttr, $returnData = TRUE )
	{
		global $wpdb, $connections, $current_user;
		
		get_currentuserinfo();
		$validate = new cnValidate();
		$where[] = 'WHERE 1=1';
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['id'] = NULL;
			$defaultAttr['preferred'] = NULL;
			$defaultAttr['type'] = NULL;
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		extract( $atts );
		
		
		if ( ! empty($id) )
		{
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );
			
			if ( ! empty($preferred) )
			{
				$where[] = $wpdb->prepare( 'AND `preferred` = $d', (bool) $preferred );
			}
			
			if ( ! empty($type) )
			{
				if ( ! is_array($type) ) $type = explode( ',' , trim($type) );
				
				$where[] = stripslashes ( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode("', '", (array) $type) ) );
			}
		}
		
		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() )
		{
			if ( !$atts['visibility'] )
			{
				if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
				if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
				if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
			}
			else
			{
				$visibility[] = $atts['visibility'];
			}
		}
		else
		{
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}
		
		if ( ! empty($visibility) ) $where[] = 'AND `visibility` IN (\'' . implode("', '", (array) $visibility) . '\')';
		
		if ( $returnData )
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_SOCIAL_TABLE . '.* 
					
					FROM ' . CN_ENTRY_SOCIAL_TABLE . ' ' . ' ' .
					
					implode(' ', $where) . ' ' . 
					
					'ORDER BY `order`';
			
			//print_r($sql);
			
			$results = $wpdb->get_results($sql);
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_SOCIAL_TABLE . '.entry_id 
					
					FROM ' . CN_ENTRY_SOCIAL_TABLE . ' ' . ' ' . implode(' ', $where);
			
			//print_r($sql);
			$results = $wpdb->get_col($sql);
		}
		
		if ( empty($results) ) return array();
		
		//print_r($results);
		return $results;
	}
	
	/**
	 * Returns as an array of objects containing the links per the defined options.
	 * 
	 * $atts['id'] (int) Retrieve the links for the specified entry by id.
	 * $atts['preferred'] (bool) Retrieve the preferred entry link; id must be supplied.
	 * $atts['type'] (array) || (string) Retrieve specific link, id must be supplied.
	 * 
	 * @param array $suppliedAttr Accepted values as noted above.
	 * @param bool $returnData Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the link data will be returned.
	 * @return array
	 */
	public function links( $suppliedAttr, $returnData = TRUE )
	{
		global $wpdb, $connections, $current_user;
		
		get_currentuserinfo();
		$validate = new cnValidate();
		$where[] = 'WHERE 1=1';
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['id'] = NULL;
			$defaultAttr['preferred'] = NULL;
			$defaultAttr['type'] = NULL;
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		extract( $atts );
		
		
		if ( ! empty($id) )
		{
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );
			
			if ( ! empty($preferred) )
			{
				$where[] = $wpdb->prepare( 'AND `preferred` = $d', (bool) $preferred );
			}
			
			if ( ! empty($type) )
			{
				if ( ! is_array($type) ) $type = explode( ',' , trim($type) );
				
				$where[] = stripslashes ( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode("', '", (array) $type) ) );
			}
		}
		
		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() )
		{
			if ( !$atts['visibility'] )
			{
				if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
				if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
				if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
			}
			else
			{
				$visibility[] = $atts['visibility'];
			}
		}
		else
		{
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}
		
		if ( ! empty($visibility) ) $where[] = 'AND `visibility` IN (\'' . implode("', '", (array) $visibility) . '\')';
		
		if ( $returnData )
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_LINK_TABLE . '.* 
					
					FROM ' . CN_ENTRY_LINK_TABLE . ' ' . ' ' .
					
					implode(' ', $where) . ' ' . 
					
					'ORDER BY `order`';
			
			//print_r($sql);
			
			$results = $wpdb->get_results($sql);
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_LINK_TABLE . '.entry_id 
					
					FROM ' . CN_ENTRY_LINK_TABLE . ' ' . ' ' . implode(' ', $where);
			
			//print_r($sql);
			$results = $wpdb->get_col($sql);
		}
		
		if ( empty($results) ) return array();
		
		//print_r($results);
		return $results;
	}
	
	/**
	 * Return an array of entry ID/s found with the supplied search terms.
	 * 
	 * @todo Allow the fields for each table to be defined as a comma delimited list, convert an array and validate against of list of valid table fields.
	 * @todo Add a filter to allow the search fields to be changed.
	 * 
	 * @author Steven A. Zahm
	 * @since 0.7.2.0
	 * @param array $suppliedAttr [optional]
	 * @return array
	 */
	public function search( $suppliedAttr = array() )
	{
		global $wpdb, $connections;
		
		$validate = new cnValidate();
		$results = array();
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['search'] = array();
			$defaultAttr['fields_entry'] = array( 'family_name' ,
											'first_name' ,
											'middle_name' ,
											'last_name' ,
											'title' ,
											'organization' ,
											'department' ,
											'contact_first_name' ,
											'contact_last_name' ,
											'bio' ,
											'notes' );
			$defaultAttr['fields_address'] = array( 'line_1' ,
											'line_2' ,
											'line_3' ,
											'city' ,
											'state' ,
											'zipcode' ,
											'country' );
			$defaultAttr['fields_phone'] = array( 'number' );
			
			$atts = $validate->attributesArray($defaultAttr, $suppliedAttr);
			//print_r($atts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		// If no search terms were entered, return an empty array.
		if ( empty( $atts['search'] ) ) return array();
		
		// If value is a string, string the white space and covert to an array.
		if ( ! is_array( $atts['search'] ) ) $atts['search'] = explode( ' ' , trim( $atts['search'] ) );
		
		// Trim any white space from around the terms in the array.
		array_walk( $atts['search'] , 'trim' );
		
		// Convert the search terms to a string adding the wild card to the end of each term to allow wider search results.
		$searchTerms = implode( '* ' , $atts['search'] ) . '*';
		
		$sql = $wpdb->prepare( 'SELECT ' . CN_ENTRY_TABLE . '.id 
								FROM ' . CN_ENTRY_TABLE . ' 
								LEFT JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id ) 
								LEFT JOIN ' . CN_ENTRY_PHONE_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_PHONE_TABLE . '.entry_id ) 
								WHERE MATCH (' . implode( ', ' , $atts['fields_entry'] ) . ') AGAINST (%s IN BOOLEAN MODE) 
								OR MATCH (' . implode( ', ' , $atts['fields_address'] ) . ') AGAINST (%s IN BOOLEAN MODE) 
								OR MATCH (' . implode( ', ' , $atts['fields_phone'] ) . ') AGAINST (%s IN BOOLEAN MODE)' , 
								$searchTerms , $searchTerms , $searchTerms );
		//print_r($sql);
			
		$results = $wpdb->get_col($sql);
		//print_r($results);
		
		return $results;
	}
	
	/**
	 * Sort the entries by the user set attributes.
	 * 
	 * $object	--	syntax is field|SORT_ASC(SORT_DESC)|SORT_REGULAR(SORT_NUMERIC)(SORT_STRING)
	 * 				
	 * example  --	'state|SORT_ASC|SORT_STRING, last_name|SORT_DESC|SORT_REGULAR
	 * 
	 * 
	 * Available order_by fields:
	 * 	id
	 *  date_added
	 *  date_modified
	 *  first_name
	 * 	last_name
	 * 	organization
	 * 	department
	 * 	city
	 * 	state
	 * 	zipcode
	 * 	country
	 * 	birthday
	 * 	anniversary
	 * 
	 * Order Flags:
	 * 	SORT_ACS
	 * 	SORT_DESC
	 *  SPECIFIED**
	 * 	RANDOM**
	 * 
	 * Sort Types:
	 * 	SORT_REGULAR
	 * 	SORT_NUMERIC
	 * 	SORT_STRING
	 * 
	 * **NOTE: The SPECIFIED and RANDOM Order Flags can only be used
	 * with the id field. The SPECIFIED flag must be used in conjuction
	 * with $suppliedIDs which can be either a comma delimited sting or
	 * an indexed array of entry IDs. If this is set, other sort fields/flags
	 * are ignored.
	 * 
	 * @param array of object $entries
	 * @param string $orderBy
	 * @param string || array $ids [optional]
	 * @return array of objects
	 */
	private function orderBy(&$entries, $orderBy, $suppliedIDs = NULL)
	{
		if ( empty($entries) || empty($orderBy) ) return $entries;
		
		$orderFields = array(
							'id',
							'date_added',
							'date_modified',
							'first_name',
							'last_name',
							'title',
							'organization',
							'department',
							'city',
							'state',
							'zipcode',
							'country',
							'birthday',
							'anniversary'
							);
		
		$sortFlags = array(
							'SPECIFIED' => 'SPECIFIED',
							'RANDOM' => 'RANDOM',
							'SORT_ASC' => SORT_ASC,
							'SORT_DESC' => SORT_DESC,
							'SORT_REGULAR' => SORT_REGULAR,
							'SORT_NUMERIC' => SORT_NUMERIC,
							'SORT_STRING' => SORT_STRING
							);
		
		$specifiedIDOrder = FALSE;
		
		// Build an array of each field to sort by and attributes.
		$sortFields = explode(',', $orderBy);
		
		// For each field the sort order can be defined as well as the sort type
		foreach ($sortFields as $sortField)
		{
			$sortAtts[] = explode('|', $sortField);
		}
		
		/*
		 * Dynamically build the variables that will be used for the array_multisort.
		 * 
		 * The field type should be the first item in the array if the user
		 * constructed the shortcode attribute correctly.
		 */
		foreach ($sortAtts as $field)
		{
			// Trim any spaces the user might have added to the shortcode attribute.
			$field[0] = strtolower(trim($field[0]));
			
			// If a user included a sort field that is invalid/mis-spelled it is skipped since it can not be used.
			if(!in_array($field[0], $orderFields)) continue;
			
			// The dynamic variable are being created and populated.
			foreach ($entries as $key => $row)
			{
				$entry = new cnEntry($row);
				
				switch ($field[0])
				{
					case 'id':
						${$field[0]}[$key] = $entry->getId();
					break;
					
					case 'date_added':
						${$field[0]}[$key] = $entry->getDateAdded('U');
					break;
					
					case 'date_modified':
						${$field[0]}[$key] = $entry->getUnixTimeStamp();
					break;
					
					case 'first_name':
						${$field[0]}[$key] = $entry->getFirstName();
					break;
					
					case 'last_name':
						${$field[0]}[$key] = $entry->getLastName();
					break;
					
					case 'title':
						${$field[0]}[$key] = $entry->getTitle();
					break;
					
					case 'organization':
						${$field[0]}[$key] = $entry->getOrganization();
					break;
					
					case 'department':
						${$field[0]}[$key] = $entry->getDepartment();
					break;
					
					case ($field[0] === 'city' || $field[0] === 'state' || $field[0] === 'zipcode' || $field[0] === 'country'):
						if ($entry->getAddresses())
						{
							$addresses = $entry->getAddresses();
							
							foreach ($addresses as $address)
							{
								//${$field[0]}[$key] = $address[$field[0]];
								${$field[0]}[$key] = $address->$field[0];
								
								// Only set the data from the first address.
								break;
							}
							
						}
						else
						{
							${$field[0]}[$key] = NULL;
						}
					break;
					
					case 'birthday':
						${$field[0]}[$key] = strtotime($entry->getBirthday());
					break;
					
					case 'anniversary':
						${$field[0]}[$key] = strtotime($entry->getAnniversary());
					break;
				}
				
			}
			// The sorting order to be determined by a lowercase copy of the original array.
			$$field[0] = array_map('strtolower', $$field[0]);
			
			// The arrays to be sorted must be passed by reference or it won't work.
			$sortParams[] = &$$field[0];
			
			// Add the flag and sort type to the sort parameters if they were supplied in the shortcode attribute.
			foreach($field as $key => $flag)
			{
				// Trim any spaces the user might have added and change the string to uppercase..
				$flag = strtoupper(trim($flag));
				
				// If a user included a sort tag that is invalid/mis-spelled it is skipped since it can not be used.
				if (!array_key_exists($flag, $sortFlags)) continue;
				
				/* 
				 * If the order is specified set the variable to true and continue
				 * because SPECIFIED should not be added to the $sortParams array
				 * as that would be an invalid argument for the array multisort.
				 */
				if ( $flag === 'SPECIFIED' || $flag === 'RANDOM' )
				{
					$idOrder = $flag;
					continue;
				}
				
				// Must be pass as reference or the multisort will fail.
				$sortParams[] = &$sortFlags[$flag];
				unset($flag);
			}
		}
		
		/*
		 * 
		 */
		if ( isset($id) && isset($idOrder) )
		{
			switch ($idOrder)
			{
				case 'SPECIFIED':
					$sortedEntries = array();
					
					/*
					 * Convert the supplied IDs value to an array if it is not.
					 */
					if ( !is_array( $suppliedIDs ) && !empty( $suppliedIDs ) )
					{
						// Trim the space characters if present.
						$suppliedIDs = str_replace(' ', '', $suppliedIDs);
						// Convert to array.
						$suppliedIDs = explode(',', $suppliedIDs);
					}
					
					foreach ( $suppliedIDs as $entryID )
					{
						$sortedEntries[] = $entries[array_search($entryID, $id)];
					}
					
					$entries = $sortedEntries;
					return $entries;
				break;
				
				case 'RANDOM':
					shuffle($entries);
					return $entries;
				break;
			}
		}
		
		/*print_r($sortParams);
		print_r($first_name);
		print_r($last_name);
		print_r($state);
		print_r($zipcode);
		print_r($organization);
		print_r($department);
		print_r($birthday);
		print_r($anniversary);*/
		
		// Must be pass as reference or the multisort will fail.
		$sortParams[] = &$entries;
		
		//$sortParams = array(&$state, SORT_ASC, SORT_REGULAR, &$zipcode, SORT_DESC, SORT_STRING, &$entries);
		call_user_func_array('array_multisort', $sortParams);
		
		return $entries;
	}
	
	/**
	 * Sorts terms by name.
	 * 
	 * @param object $a
	 * @param object $b
	 * @return integer
	 */
	private function sortTermsByName($a, $b)
	{
		return strcmp($a->name, $b->name);
	}
	
	/**
	 * Sorts terms by ID.
	 * 
	 * @param object $a
	 * @param object $b
	 * @return integer
	 */
	private function sortTermsByID($a, $b)
	{
		if ( $a->term_id > $b->term_id )
		{
			return 1;
		}
		elseif ( $a->term_id < $b->term_id )
		{
			return -1;
		} 
		else
		{
			return 0;
		}
	}
	
	/**
	 * Total record count based on current user permissions.
	 * 
	 * @param bool $allowPublicOverride
	 * @param bool $allowPrivateOverride
	 * @param string $status
	 * @return integer
	 */
	private function recordCount($allowPublicOverride, $allowPrivateOverride, $status = array() )
	{
		global $wpdb, $connections;
		
		$where[] = 'WHERE 1=1';
		
		if ( is_user_logged_in() )
		{
			if ( current_user_can('connections_view_public') ) $visibility[] = 'public';
			if ( current_user_can('connections_view_private') ) $visibility[] = 'private';
			if ( current_user_can('connections_view_unlisted') && is_admin() ) $visibility[] = 'unlisted';
			
			// Set query status per role capability assigned to the current user.
			if ( current_user_can('connections_edit_entry') )
			{
				// Set the entry statuses the user is permitted to view based on their role.
				$userPermittedEntryStatus = array('approved', 'pending');
				
				$status = array_intersect($userPermittedEntryStatus, $status);
			}
			elseif ( current_user_can('connections_edit_entry_moderated') )
			{
				// Set the entry statuses the user is permitted to view based on their role.
				$userPermittedEntryStatus = array('approved');
				
				$status = array_intersect($userPermittedEntryStatus, $status);
			}
			else
			{
				// Set the entry statuses the user is permitted to view based on their role.
				$userPermittedEntryStatus = array('approved');
				
				$status = array_intersect($userPermittedEntryStatus, $status);
			}
			
		}
		else
		{
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $allowPublicOverride == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $allowPrivateOverride == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
			
			$status = array('approved');
		}
		
		$where[] = 'AND `status` IN (\'' . implode("', '", $status) . '\')';
		$where[] = 'AND `visibility` IN (\'' . implode("', '", $visibility) . '\')';
		
		//if ( !empty($status) ) $where[] = 'AND `status` IN (\'' . implode("', '", (array) $status) . '\')';
		
		return $wpdb->get_var( 'SELECT COUNT(`id`) FROM ' . CN_ENTRY_TABLE . ' ' . implode(' ', $where) );
	}
	
	/**
	 * Limit the returned results.
	 * 
	 * This is more or less a hack until limit is properly implemented in the retrieve query.
	 * 
	 * @version 1.0
	 * @since 0.7.1.6
	 * @param array $results
	 * @return array
	 */
	public function limitList($results)
	{
		$limit = 12;
		
		return array_slice($results, 0, $limit, TRUE);
	}
	
	/**
	 * Remove the entries from the list where the date added was not recorded.
	 * 
	 * This is more or less a hack to remove the entries from the list where the date added was not recorded which would be entries added before 0.7.1.1.
	 * 
	 * @version 1.0
	 * @since 0.7.1.6
	 * @param array $results
	 * @return array
	 */
	public function removeUnknownDateAdded($results)
	{
		foreach ( $results as $key => $entry )
		{
			if ( empty($entry->date_added) ) unset( $results[$key] );
		}
		
		return $results;
	}
	
	/**
	 * Returns all the category terms.
	 * 
	 * @return object
	 */
	public function categories()
	{
		global $connections;
		
		return $connections->term->getTerms('category');
	}
	
	/**
	 * Returns category by ID.
	 * 
	 * @param interger $id
	 * @return object
	 */
	public function category($id)
	{
		global $connections;
		
		return $connections->term->getTerm($id, 'category');
	}
	
	/**
	 * Retrieve the children of the supplied parent.
	 * 
	 * @param interger $id
	 * @return array
	 */
	public function categoryChildren($field, $value)
	{
		global $connections;
		
		return $connections->term->getTermChildrenBy($field, $value, 'category');
	}
	
}

?>