<?php

/**
 * Class containing all the necessary methods to run queries on the database.
 *
 * @package     Connections
 * @subpackage  SQL
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnRetrieve {
	/**
	 * The result count from the query with no limit.
	 *
	 * @var integer
	 */
	public $resultCountNoLimit;

	/**
	 *
	 *
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array
	 * @return array
	 */
	public function entries( $atts = array() ) {
		global $wpdb, $connections, $current_user;

		get_currentuserinfo();

		$validate             = new cnValidate();
		$select[]             = CN_ENTRY_TABLE . '.*';
		$from[]               = CN_ENTRY_TABLE;
		$join                 = array();
		$where[]              = 'WHERE 1=1';
		$having               = array();
		$orderBy              = array();
		$visibility           = array();

		$permittedEntryTypes  = array( 'individual', 'organization', 'family', 'connection_group' );
		$permittedEntryStatus = array( 'approved', 'pending' );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['list_type']             = NULL;
		$defaults['category']              = NULL;
		$defaults['category_in']           = NULL;
		$defaults['exclude_category']      = NULL;
		$defaults['category_name']         = NULL;
		$defaults['category_slug']         = NULL;
		$defaults['wp_current_category']   = FALSE;
		$defaults['char']                  = '';
		$defaults['id']                    = NULL;
		$defaults['slug']                  = NULL;
		$defaults['family_name']           = NULL;
		$defaults['last_name']             = NULL;
		$defaults['title']                 = NULL;
		$defaults['organization']          = NULL;
		$defaults['department']            = NULL;
		$defaults['city']                  = NULL;
		$defaults['state']                 = NULL;
		$defaults['zip_code']              = NULL;
		$defaults['country']               = NULL;
		$defaults['visibility']            = NULL;
		$defaults['status']                = array( 'approved' );
		$defaults['order_by']              = array( 'sort_column', 'last_name', 'first_name' );
		$defaults['limit']                 = NULL;
		$defaults['offset']                = 0;
		$defaults['allow_public_override'] = FALSE;
		$defaults['private_override']      = FALSE;
		$defaults['search_terms']          = NULL;

		// $atts vars to support showing entries within a specified radius.
		$defaults['near_addr']             = NULL;
		$defaults['latitude']              = NULL;
		$defaults['longitude']             = NULL;
		$defaults['radius']                = 10;
		$defaults['unit']                  = 'mi';

		$defaults['lock']                  = TRUE;

		$atts = $validate->attributesArray( $defaults, $atts );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		/*
		 * // START -- Process the query vars. \\
		 * NOTE: these will override any values supplied via $atts, which include via the shortcode.
		 */
		if ( ! is_admin() && ! $atts['lock'] ) {
			// Category slug
			$queryCategorySlug = get_query_var( 'cn-cat-slug' );
			if ( ! empty( $queryCategorySlug ) ) {
				// If the category slug is a descendant, use the last slug from the URL for the query.
				$queryCategorySlug = explode( '/' , $queryCategorySlug );

				if ( isset( $queryCategorySlug[count( $queryCategorySlug )-1] ) ) $atts['category_slug'] = $queryCategorySlug[count( $queryCategorySlug )-1];
			}

			// Category ID
			$queryCategoryID = get_query_var( 'cn-cat' );
			if ( ! empty( $queryCategoryID ) ) $atts['category'] = $queryCategoryID;

			// Country
			$queryCountry = get_query_var( 'cn-country' );
			if ( ! empty( $queryCountry ) ) $atts['country'] = urldecode( $queryCountry );

			// Postal Code
			$queryPostalCode = get_query_var( 'cn-postal-code' );
			if ( ! empty( $queryPostalCode ) ) $atts['zip_code'] = urldecode( $queryPostalCode );

			// Region [State]
			$queryRegion = get_query_var( 'cn-region' );
			if ( ! empty( $queryRegion ) ) $atts['state'] = urldecode( $queryRegion );

			// Locality [City]
			$queryLocality = get_query_var( 'cn-locality' );
			if ( ! empty( $queryLocality ) ) $atts['city'] = urldecode( $queryLocality );

			// Organization
			$queryOrganization = get_query_var( 'cn-organization' );
			if ( ! empty( $queryOrganization ) ) $atts['organization'] = urldecode( $queryOrganization );

			// Department
			$queryDeparment = get_query_var( 'cn-department' );
			if ( ! empty( $queryDeparment ) ) $atts['department'] = urldecode( $queryDeparment );

			// Entry slug
			$queryEntrySlug = get_query_var( 'cn-entry-slug' );
			if ( ! empty( $queryEntrySlug ) ) $atts['slug'] = urldecode( $queryEntrySlug );

			// Initial character.
			$queryInitialChar = get_query_var( 'cn-char' );
			if ( ! empty( $queryInitialChar ) ) $atts['char'] = urldecode( $queryInitialChar );

			// Pagination
			$queryPage = get_query_var( 'cn-pg' );
			$atts['offset'] = ( ! empty( $queryPage ) ) ? ( $queryPage - 1 ) * $atts['limit'] : $atts['offset'];
			$atts['offset'] = ( $atts['offset'] > 0 ) ? $atts['offset'] : NULL;

			// Search terms
			$searchTerms = get_query_var( 'cn-s' );
			if ( ! empty( $searchTerms ) ) $atts['search_terms'] = urldecode( $searchTerms );

			// Geo-location
			$queryCoord = get_query_var( 'cn-near-coord' );
			if ( ! empty( $queryCoord ) ) {
				$queryCoord = explode( ',', $queryCoord );
				$atts['latitude'] = (float) $queryCoord[0];
				$atts['longitude'] = (float) $queryCoord[1];

				// Get the radius, otherwise the defaultf of 10.
				if ( get_query_var( 'cn-radius' ) ) $atts['radius'] = (int) get_query_var( 'cn-radius' );

				// Set the unit.
				$atts['unit'] = ( get_query_var( 'cn-unit' ) ) ? get_query_var( 'cn-unit' ) : $atts['unit'];
			}
		}
		/*
		 * // END -- Process the query vars. \\
		 */

		/*
		 * // START -- Reset some of the $atts based if category_slug or entry slug
		 * is being used. This is done to prevent query conflicts. This should be safe because
		 * if a specific entry or category is being queried the other $atts can be discarded.
		 * This has to be done in order to reconcile values passed via the shortcode and the
		 * query string values.
		 *
		 * @TODO I know there are more scenarios to consider ... but this is all I can think of at the moment.
		 * Thought --- maybe resetting to the default $atts should be done --- something to consider.
		 */
		if ( ! empty( $atts['slug'] ) || ! empty( $atts['category_slug'] ) ) {
			$atts['list_type'] = NULL;
			$atts['category'] = NULL;
			$atts['category_in'] = NULL;
			$atts['wp_current_category'] = NULL;
		}

		if ( ! empty( $atts['slug'] ) ) {
			$atts['near_addr'] = NULL;
			$atts['latitude'] = NULL;
			$atts['longitude'] = NULL;
			$atts['radius'] = 10;
			$atts['unit'] = 'mi';
		}
		/*
		 * // END -- Reset.
		 */

		/*
		 * If in a post get the category names assigned to the post.
		 */
		if ( $atts['wp_current_category'] && !is_page() ) {
			// Get the current post categories.
			$wpCategories = get_the_category();

			// Build an array of the post categories.
			foreach ( $wpCategories as $wpCategory ) {
				$categoryNames[] = $wpCategory->cat_name;
			}
		}

		/*
		 * Build and array of the supplied category names and their children.
		 */
		if ( ! empty( $atts['category_name'] ) ) {
			// If value is a string convert to an array.
			if ( ! is_array( $atts['category_name'] ) ) {
				$atts['category_name'] = explode( ',', $atts['category_name'] );
			}

			foreach ( $atts['category_name'] as $categoryName ) {
				// Add the parent category to the array and remove any whitespace from the begining/end of the name in case the user added it when using the shortcode.
				$categoryNames[] = htmlspecialchars( trim( $categoryName ) );

				// Retrieve the children categories
				$results = $this->categoryChildren( 'name', $categoryName );

				foreach ( (array) $results as $term ) {
					if ( ! in_array( $term->name, $categoryNames ) ) $categoryNames[] = htmlspecialchars( $term->name );
				}
			}
		}

		/*
		 * Build and array of the supplied category slugs and their children.
		 */
		if ( ! empty( $atts['category_slug'] ) ) {
			$categorySlugs = array();

			// If value is a string convert to an array.
			if ( ! is_array( $atts['category_slug'] ) ) {
				$atts['category_slug'] = explode( ',', $atts['category_slug'] );
			}

			foreach ( $atts['category_slug'] as $categorySlug ) {
				// Add the parent category to the array and remove any whitespace from the begining/end of the name in case the user added it when using the shortcode.
				$categorySlugs[] = trim( $categorySlug );

				// Retrieve the children categories
				$results = $this->categoryChildren( 'slug', $categorySlug );

				foreach ( (array) $results as $term ) {
					if ( ! in_array( $term->name, $categorySlugs ) ) $categorySlugs[] = $term->slug;
				}
			}
		}

		/*
		 * Build an array of all the categories and their children based on the supplied category IDs.
		 */
		if ( ! empty( $atts['category'] ) ) {
			// If value is a string, string the white space and covert to an array.
			if ( ! is_array( $atts['category'] ) ) {
				$atts['category'] = str_replace( ' ', '', $atts['category'] );

				$atts['category'] = explode( ',', $atts['category'] );
			}

			foreach ( $atts['category'] as $categoryID ) {
				// Add the parent category ID to the array.
				$categoryIDs[] = $categoryID;

				// Retrieve the children categories
				$results = $this->categoryChildren( 'term_id', $categoryID );
				//print_r($results);

				foreach ( (array) $results as $term ) {
					if ( ! in_array( $term->term_id, $categoryIDs ) ) $categoryIDs[] = $term->term_id;
				}
			}
		}

		/*
		 * Exclude the specified categories by ID.
		 */
		if ( ! empty( $atts['exclude_category'] ) ) {

			// If value is a string, string the white space and covert to an array.
			if ( ! is_array( $atts['exclude_category'] ) ) {
				$atts['exclude_category'] = str_replace( ' ', '', $atts['exclude_category'] );

				$atts['exclude_category'] = explode( ',', $atts['exclude_category'] );
			}

			$categoryIDs = array_diff( (array) $categoryIDs, $atts['exclude_category'] );

			foreach ( $atts['exclude_category'] as $categoryID ) {

				// Add the parent category ID to the array.
				$categoryExcludeIDs[] = $categoryID;

				// Retrieve the children categories
				$results = $this->categoryChildren( 'term_id', $categoryID );
				//print_r($results);

				foreach ( (array) $results as $term ) {

					if ( ! in_array( $term->term_id, $categoryExcludeIDs ) ) $categoryExcludeIDs[] = $term->term_id;
				}
			}
		}

		// Convert the supplied category IDs $atts['category_in'] to an array.
		if ( ! empty( $atts['category_in'] ) ) {
			if ( ! is_array( $atts['category_in'] ) ) {
				// Trim the space characters if present.
				$atts['category_in'] = str_replace( ' ', '', $atts['category_in'] );

				// Convert to array.
				$atts['category_in'] = explode( ',', $atts['category_in'] );
			}

			// Exclude any category IDs that may have been set.
			$atts['category_in'] = array_diff( $atts['category_in'], (array) $atts['exclude_category'] );

			// Build the query to retrieve entry IDs that are assigned to all the supplied category IDs; operational AND.
			$sql = 'SELECT DISTINCT tr.entry_id FROM ' . CN_TERM_RELATIONSHIP_TABLE . ' AS tr
					INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
					WHERE 1=1 AND tt.term_id IN (\'' . implode( "', '", $atts['category_in'] ) . '\') GROUP BY tr.entry_id HAVING COUNT(*) = ' . count( $atts['category_in'] ) . ' ORDER BY tr.entry_id';

			// Store the entryIDs that exist on all of the supplied category IDs
			$results = $wpdb->get_col( $sql );
			//print_r($results);

			if ( ! empty( $results ) ) {
				$where[] = 'AND ' . CN_ENTRY_TABLE . '.id IN (\'' . implode( "', '", $results ) . '\')';
			} else {
				$where[] = 'AND 1=2';
			}

			/*
			 * This is the query to use to return entry IDs that are in the same categories. The COUNT value
			 * should equal the number of category IDs in the IN() statement.

			   SELECT DISTINCT tr.entry_id FROM `wp_connections_term_relationships` AS tr
			   INNER JOIN `wp_connections_term_taxonomy` AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
			   WHERE 1=1 AND tt.term_id IN ('51','73','76') GROUP BY tr.entry_id HAVING COUNT(*) = 3 ORDER BY tr.entry_id
			 */
		}

		if ( ! empty( $categoryIDs ) || ! empty( $categoryNames ) || ! empty( $categorySlugs ) ) {
			// Set the query string to INNER JOIN the term relationship and taxonomy tables.
			$join[] = 'INNER JOIN ' . CN_TERM_RELATIONSHIP_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_TERM_RELATIONSHIP_TABLE . '.entry_id )';
			$join[] = 'INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' ON ( ' . CN_TERM_RELATIONSHIP_TABLE . '.term_taxonomy_id = ' . CN_TERM_TAXONOMY_TABLE . '.term_taxonomy_id )';
			$join[] = 'INNER JOIN ' . CN_TERMS_TABLE . ' ON ( ' . CN_TERMS_TABLE . '.term_id = ' . CN_TERM_TAXONOMY_TABLE . '.term_id )';

			// Set the query string to return entries within the category taxonomy.
			$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.taxonomy = \'category\'';

			if ( ! empty( $categoryIDs ) ) {
				$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.term_id IN (\'' . implode( "', '", $categoryIDs ) . '\')';

				unset( $categoryIDs );
			}

			if ( ! empty( $categoryExcludeIDs ) ) {
				$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.term_id NOT IN (\'' . implode( "', '", $categoryExcludeIDs ) . '\')';

				unset( $categoryIDs );
			}

			if ( ! empty( $categoryNames ) ) {
				$where[] = 'AND ' . CN_TERMS_TABLE . '.name IN (\'' . implode( "', '", (array) $categoryNames ) . '\')';

				unset( $categoryNames );
			}

			if ( ! empty( $categorySlugs ) ) {
				$where[] = 'AND ' . CN_TERMS_TABLE . '.slug IN (\'' . implode( "', '", (array) $categorySlugs ) . '\')';

				unset( $categorySlugs );
			}
		}

		/*
		 * // START --> Set up the query to only return the entries that match the supplied IDs.
		 *    NOTE: This includes the entry IDs returned for category_in.
		 */
		// Convert the supplied IDs $atts['id'] to an array if it was not supplied as an array.
		if ( ! empty( $atts['id'] ) && ! is_array( $atts['id'] ) ) $atts['id'] = explode( ',' , trim( $atts['id'] ) );

		// Set query string to return specific entries.
		if ( ! empty( $atts['id'] ) ) $where[] = 'AND ' . CN_ENTRY_TABLE . '.id IN (\'' . implode( "', '", $atts['id'] ) . '\')';
		/*
		 * // END --> Set up the query to only return the entries that match the supplied IDs.
		 */


		/*
		 * // START --> Set up the query to only return the entries that match the supplied search terms.
		 */
		if ( ! empty( $atts['search_terms'] ) ) {
			$searchResults = $this->search( array( 'terms' => $atts['search_terms'] ) );
			//print_r($searchResults);

			// If there were no results, add a WHERE clause that will not return results when performing the whole query.
			if ( empty( $searchResults ) ) {
				$where[] = 'AND 1=2';
			}
			else {
				// Set the entry IDs to be the search results.
				$where[] = 'AND ' . CN_ENTRY_TABLE . '.id IN (\'' . implode( "', '", $searchResults ) . '\')';
			}
		}
		/*
		 * // END --> Set up the query to only return the entries that match the supplied search terms.
		 */


		/*
		 * // START --> Set up the query to only return the entry that matches the supplied slug.
		 */
		if ( ! empty( $atts['slug'] ) ) {
			// Trim the white space from the ends.
			$atts['slug'] = trim( $atts['slug'] );

			$where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_TABLE . '.slug = %s' , $atts['slug'] );
		}
		/*
		 * // END --> Set up the query to only return the entry that matches the supplied slug.
		 */

		/*
		 * // START --> Set up the query to only return the entries that match the supplied entry type.
		 */
		// Convert the supplied entry types $atts['list_type'] to an array.
		if ( ! is_array( $atts['list_type'] ) && ! empty( $atts['list_type'] ) ) {
			// Trim the space characters if present.
			$atts['list_type'] = str_replace( ' ', '', $atts['list_type'] );

			// Convert to array.
			$atts['list_type'] = explode( ',', $atts['list_type'] );
		}

		// Set query string for entry type.
		if ( ! empty( $atts['list_type'] ) && (bool) array_intersect( $atts['list_type'], $permittedEntryTypes ) ) {
			// Compatibility code to make sure any ocurrences of the deprecated entry type connections_group is changed to entry type family
			$atts['list_type'] = str_replace( 'connection_group', 'family', $atts['list_type'] );

			$where[] = 'AND `entry_type` IN (\'' . implode( "', '", (array) $atts['list_type'] ) . '\')';
		}
		/*
		 * // END --> Set up the query to only return the entries that match the supplied entry type.
		 */

		/*
		 * // START --> Set up the query to only return the entries that match the supplied filters.
		 */
		if ( ! empty( $atts['family_name'] ) ) $where[] = $wpdb->prepare( 'AND `family_name` = %s' , $atts['family_name'] );
		if ( ! empty( $atts['last_name'] ) ) $where[] = $wpdb->prepare( 'AND `last_name` = %s' , $atts['last_name'] );
		if ( ! empty( $atts['title'] ) ) $where[] = $wpdb->prepare( 'AND `title` = %s' , $atts['title'] );
		if ( ! empty( $atts['organization'] ) ) $where[] = $wpdb->prepare( 'AND `organization` = %s' , $atts['organization'] );
		if ( ! empty( $atts['department'] ) ) $where[] = $wpdb->prepare( 'AND `department` = %s' , $atts['department'] );

		if ( ! empty( $atts['city'] ) || ! empty( $atts['state'] ) || ! empty( $atts['zip_code'] ) || ! empty( $atts['country'] ) ) {
			if ( ! isset( $join['address'] ) ) $join['address'] = 'INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id )';

			if ( ! empty( $atts['city'] ) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.city = %s' , $atts['city'] );
			if ( ! empty( $atts['state'] ) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.state = %s' , $atts['state'] );
			if ( ! empty( $atts['zip_code'] ) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.zipcode = %s' , $atts['zip_code'] );
			if ( ! empty( $atts['country'] ) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.country = %s' , $atts['country'] );
		}

		if ( 0 < strlen( $atts['char'] ) ) {
			$having[] = $wpdb->prepare( 'HAVING sort_column LIKE %s' , like_escape ( $atts['char'] ) . '%' );
		}
		/*
		 * // END --> Set up the query to only return the entries that match the supplied filters.
		 */

		/*
		 * // START --> Set up the query to only return the entries based on user permissions.
		 */
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			//var_dump( $connections->options->getAllowPublic() ); die;

			// Display the 'public' entries if the user is not required to be logged in.
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';

			// Display the 'public' entries if the public override shortcode option is enabled.
			if ( $connections->options->getAllowPublicOverride() ) {
				if ( $atts['allow_public_override'] == TRUE ) $visibility[] = 'public';
			}

			// Display the 'public' & 'private' entries if the private override shortcode option is enabled.
			if ( $connections->options->getAllowPrivateOverride() ) {
				// If the user can view private entries then they should be able to view public entries too, so we'll add it. Just check to see if it is already set first.
				if ( ! in_array( 'public', $visibility ) && $atts['private_override'] == TRUE ) $visibility[] = 'public';
				if ( $atts['private_override'] == TRUE ) $visibility[] = 'private';
			}
		}

		$where[] = 'AND ' . CN_ENTRY_TABLE . '.visibility IN (\'' . implode( "', '", (array) $visibility ) . '\')';
		/*
		 * // END --> Set up the query to only return the entries based on user permissions.
		 */

		/*
		 * // START --> Set up the query to only return the entries based on status.
		 */
		// Convert the supplied entry statuses $atts['status'] to an array.
		if ( ! is_array( $atts['status'] ) /*&& ! empty($atts['status'])*/ ) {
			// Trim the space characters if present.
			$atts['status'] = str_replace( ' ', '', $atts['status'] );

			// Convert to array.
			$atts['status'] = explode( ',', $atts['status'] );
		}
		/*else
			{
				// Query the approved entries
				$atts['status'] = array('approved');
			}*/

		if ( is_user_logged_in() ) {
			// if 'all' was supplied, set the array to all the permitted entry status types.
			if ( in_array( 'all', $atts['status'] ) ) $atts['status'] = $permittedEntryStatus;

			// Limit the viewable status per role capability assigned to the current user.
			if ( current_user_can( 'connections_edit_entry' ) ) {
				$userPermittedEntryStatus = array( 'approved', 'pending' );

				$atts['status'] = array_intersect( $userPermittedEntryStatus, $atts['status'] );
			}
			elseif ( current_user_can( 'connections_edit_entry_moderated' ) ) {
				$userPermittedEntryStatus = array( 'approved' );

				$atts['status'] = array_intersect( $userPermittedEntryStatus, $atts['status'] );
			}
			else {
				$userPermittedEntryStatus = array( 'approved' );

				$atts['status'] = array_intersect( $userPermittedEntryStatus, $atts['status'] );
			}
		}
		/*else
			{
				// If no user is logged in, set the status for the query to approved.
				$atts['status'] = array('approved');
			}*/

		$where[] = 'AND ' . CN_ENTRY_TABLE . '.status IN (\'' . implode( "', '", $atts['status'] ) . '\')';
		/*
		 * // END --> Set up the query to only return the entries based on status.
		 */

		/*
		 * // START --> Geo-limit the query.
		 */
		//$atts['latitude'] = 40.3663671;
		//$atts['longitude'] = -75.8876941;

		if ( ! empty( $atts['latitude'] ) && ! empty( $atts['longitude'] ) ) {
			$earthRadius = 6371;  // Earth's radius in (SI) km.

			// Convert the supplied radius from the supplied unit to (SI) km.
			$atts['radius'] = cnGeo::convert( array( 'value' => $atts['radius'] , 'from' => $atts['unit'] , 'to' => 'km' , 'format' => FALSE , 'return' => TRUE ) );

			// Limiting bounding box (in degrees).
			$minLat = $atts['latitude'] - rad2deg( $atts['radius']/$earthRadius );
			$maxLat = $atts['latitude'] + rad2deg( $atts['radius']/$earthRadius );
			$minLng = $atts['longitude'] - rad2deg( $atts['radius']/$earthRadius/cos( deg2rad( $atts['latitude'] ) ) );
			$maxLng = $atts['longitude'] + rad2deg( $atts['radius']/$earthRadius/cos( deg2rad( $atts['latitude'] ) ) );

			// Convert origin of geographic circle to radians.
			$atts['latitude'] = deg2rad( $atts['latitude'] );
			$atts['longitude'] = deg2rad( $atts['longitude'] );

			// Add the SELECT statement that adds the `radius` column.
			$select[] = $wpdb->prepare( 'acos(sin(%f)*sin(radians(latitude)) + cos(%f)*cos(radians(latitude))*cos(radians(longitude)-%f))*6371 AS distance' , $atts['latitude'] , $atts['latitude'] , $atts['longitude'] );

			// Create a subquery that will limit the rows that have the cosine law applied to within the bounding box.
			$geoSubselect = $wpdb->prepare( '(SELECT entry_id FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE latitude>%f AND latitude<%f AND longitude>%f AND longitude<%f) AS geo_bound' , $minLat , $maxLat , $minLng , $maxLng );
			// The subquery needs to be added to the beginning of the array so the inner joins on the other tables are joined to the CN_ENTRY_TABLE
			array_unshift( $from, $geoSubselect );

			// Add the JOIN for the address table. NOTE: This JOIN is also set in the ORDER BY section. The 'address' index is to make sure it doea not get added to the query twice.
			if ( ! isset( $join['address'] ) ) $join['address'] = 'INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id )';

			// Add the WHERE statement to limit the query to a geographic circle per the defined radius.
			$where[] = $wpdb->prepare( 'AND acos(sin(%f)*sin(radians(latitude)) + cos(%f)*cos(radians(latitude))*cos(radians(longitude)-%f))*6371 < %f' , $atts['latitude'] , $atts['latitude'] , $atts['longitude'] , $atts['radius'] );

			// Temporarily set the sort order to 'radius' for testing.
			//$atts['order_by'] = array('radius');
		}
		/*
		 * // END --> Geo-limit the query.
		 */

		/*
		 * // START --> Build the ORDER BY query segment.
		 */
		//if ( empty($atts['order_by']) )
		//{
		// Setup the default sort order if none were supplied.
		//$orderBy = array('sort_column', 'last_name', 'first_name');
		//}
		//else
		//{
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
			'anniversary',
			'sort_column'
		);

		$orderFlags = array(
			'SPECIFIED' => 'SPECIFIED',
			'RANDOM'    => 'RANDOM',
			'SORT_ASC'  => 'ASC',
			'SORT_DESC' => 'DESC'
		);

		// If a geo-bound query is being performed the `radius` order field can be used.
		if ( ! empty( $atts['latitude'] ) && ! empty( $atts['longitude'] ) ) array_push( $orderFields, 'distance' );

		// Convert to an array
		if ( ! is_array( $atts['order_by'] ) ) {
			// Trim the space characters if present.
			$atts['order_by'] = str_replace( ' ', '', $atts['order_by'] );

			// Build an array of each field to order by and its sort order.
			$atts['order_by'] = explode( ',' , $atts['order_by'] );
		}

		// For each field the sort order can be defined.
		foreach ( $atts['order_by'] as $orderByField ) {
			$orderByAtts[] = explode( '|' , $orderByField );
		}

		// Build the ORDER BY query segment
		foreach ( $orderByAtts as $field ) {
			// Trim any spaces the user may have supplied and set it to be lowercase.
			$field[0] = strtolower( trim( $field[0] ) );

			// Check to make sure the supplied field is one of the valid fields to order by.
			if ( in_array( $field[0] , $orderFields ) ) {
				// The date_modified actually maps to the `ts` column in the db.
				if ( $field[0] == 'date_modified' ) $field[0] = 'ts';

				// If one of the order fields is an address region add the INNER JOIN to the CN_ENTRY_ADDRESS_TABLE
				if ( $field[0] == 'city' || $field[0] == 'state' || $field[0] == 'zipcode' || $field[0] == 'country' ) {
					if ( ! isset( $join['address'] ) ) $join['address'] = 'INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id )';
				}

				// Check to see if an order flag was set and is a valid order flag.
				if ( isset( $field[1] ) ) {
					// Trim any spaces the user might have added and change the string to uppercase..
					$field[1] = strtoupper( trim( $field[1] ) );

					// If a user included a sort flag that is invalid/mis-spelled it is skipped since it can not be used.
					if ( array_key_exists( $field[1] , $orderFlags ) ) {
						/*
								 * The SPECIFIED and RANDOM order flags are special use and should only be used with the id sort field.
								 * Set the default sort flag if it was use on any other sort field than id.
								 */
						if ( ( $orderFlags[$field[1]] == 'SPECIFIED' || $orderFlags[$field[1]] == 'RANDOM' ) && $field[0] != 'id' ) $field[1] = 'SORT_ASC';

						switch ( $orderFlags[$field[1]] ) {
							/*
									 * Order the results based on the order of the supplied entry IDs
									 */
						case 'SPECIFIED':
							if ( ! empty( $atts['id'] ) ) {
								$orderBy = array( 'FIELD( id, ' . implode( ', ', (array) $atts['id'] ) . ' )' );
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
							$orderBy = array( 'random' );
							break;

							/*
									 * Return the results in ASC or DESC order.
									 */
						default:
							$orderBy[] = $field[0] . ' ' . $orderFlags[$field[1]];
							break;
						}
					}
					else {
						$orderBy[] = $field[0];
					}
				}
				else {
					$orderBy[] = $field[0];
				}
			}
		}
		//}

		( empty( $orderBy ) ) ? $orderBy = 'ORDER BY sort_column, last_name, first_name' : $orderBy = 'ORDER BY ' . implode( ', ', $orderBy );
		/*
		 * // END --> Build the ORDER BY query segment.
		 */

		/*
		 * // START --> Set up the query LIMIT and OFFSET.
		 */
		( empty( $atts['limit'] ) ) ? $limit = NULL : $limit = ' LIMIT ' . $atts['limit'] . ' ';
		( empty( $atts['offset'] ) ) ? $offset = NULL : $offset = ' OFFSET ' . $atts['offset'] . ' ';
		/*
		 * // END --> Set up the query LIMIT and OFFSET.
		 */

		/*
		 * // START --> Build the SELECT query segment.
		 */
		$select[] = 'CASE `entry_type`
						  WHEN \'individual\' THEN `last_name`
						  WHEN \'organization\' THEN `organization`
						  WHEN \'connection_group\' THEN `family_name`
						  WHEN \'family\' THEN `family_name`
						END AS `sort_column`';
		/*
		 * // START --> Build the SELECT query segment.
		 */


		$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . implode( ', ', $select ) . ' FROM ' . implode( ', ', $from ) . ' ' . implode( ' ', $join ) . ' ' . implode( ' ', $where ) . ' ' . ' ' . implode( ' ', $having ) . ' ' . $orderBy . ' ' . $limit . $offset;
		// print_r($sql); die;

		$results = $wpdb->get_results( $sql );

		// The most recent query to have been executed by cnRetrieve::entries
		$connections->lastQuery      = $wpdb->last_query;

		// The most recent query error to have been generated by cnRetrieve::entries
		$connections->lastQueryError = $wpdb->last_error;

		// ID generated for an AUTO_INCREMENT column by the most recent INSERT query.
		$connections->lastInsertID   = $wpdb->insert_id;

		// The number of rows returned by the last query.
		$connections->resultCount    = $wpdb->num_rows;

		// The number of rows returned by the last query without the limit clause set
		$foundRows                       = $wpdb->get_results( 'SELECT FOUND_ROWS()' );
		$connections->resultCountNoLimit = $foundRows[0]->{'FOUND_ROWS()'};
		$this->resultCountNoLimit        = $foundRows[0]->{'FOUND_ROWS()'};

		// The total number of entries based on user permissions.
		// $connections->recordCount         = self::recordCount( array( 'public_override' => $atts['allow_public_override'], 'private_override' => $atts['private_override'] ) );

		// The total number of entries based on user permissions with the status set to 'pending'
		// $connections->recordCountPending  = self::recordCount( array( 'public_override' => $atts['allow_public_override'], 'private_override' => $atts['private_override'], 'status' => array( 'pending' ) ) );

		// The total number of entries based on user permissions with the status set to 'approved'
		// $connections->recordCountApproved = self::recordCount( array( 'public_override' => $atts['allow_public_override'], 'private_override' => $atts['private_override'], 'status' => array( 'approved' ) ) );

		/*
		 * ONLY in the admin.
		 *
		 * Reset the pagination filter for the current user, remove the offset from the query and re-run the
		 * query if the offset for the query is greater than the record count with no limit set in the query.
		 *
		 */
		if ( is_admin() && $atts['offset'] > $connections->resultCountNoLimit ) {

			$connections->currentUser->resetFilterPage( 'manage' );
			unset( $atts['offset'] );
			$results = $this->entries( $atts );

		} elseif ( $atts['offset'] > $connections->resultCountNoLimit ) {

			/*
			 * This is for the front end, reset the page and offset and re-run the query if the offset
			 * is greater than the record count with no limit.
			 *
			 * @TODO  this should somehow be precessed in the parse_request action hook so the URL
			 * permlink and query vars can be properly updated.
			 */

			set_query_var( 'cn-pg', 0 );
			$atts['offset'] = 0;
			$results = $this->entries( $atts );
		}

		return $results;

		// Return the results ordered.
		//return $this->orderBy($results, $atts['order_by'], $atts['id']);
	}

	public function entry( $id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . CN_ENTRY_TABLE . ' WHERE id="%d"' , $id ) );
	}

	/**
	 * Retrieve the unique initial characters of all entries in the entry table sorted by character.
	 *
	 * @access public
	 * @since 0.7.4
	 * @return (array)
	 */
	public static function getCharacters( $atts = array() ) {
		global $wpdb;
		$where[] = 'WHERE 1=1';

		$defaults = array(
			'status'                => array( 'approved' ),
			'visibility'            => array(),
			'allow_public_override' => FALSE,
			'private_override'      => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Limit the characters that are queried based on if the current user can view public, private or unlisted entries.
		$where = self::setQueryVisibility( $where, $atts );

		// Limit the characters that are queried based on if the current user can view approved and/or pending entries.
		$where = self::setQueryStatus( $where, $atts );


		$select = 'SUBSTRING( CASE `entry_type`
					  WHEN \'individual\' THEN `last_name`
					  WHEN \'organization\' THEN `organization`
					  WHEN \'connection_group\' THEN `family_name`
					  WHEN \'family\' THEN `family_name`
					END, 1, 1 ) AS `char`';

		return $wpdb->get_col( 'SELECT DISTINCT ' . $select . ' FROM ' . CN_ENTRY_TABLE . ' '  . implode( ' ', $where ) . ' ORDER BY `char`' );
	}

	/**
	 * Set up the query to only return the entries based on user permissions.
	 *
	 * @param (array) $where
	 * @param (array) $atts
	 *
	 * @access private
	 * @since 0.7.4
	 * @uses wp_parse_args()
	 * @uses is_user_logged_in()
	 * @uses current_user_can()
	 * @return (array)
	 */
	public static function setQueryVisibility( $where, $atts = array() ) {
		global $connections;
		$visibility = array();

		$defaults = array(
			'visibility'            => array(),
			'allow_public_override' => FALSE,
			'private_override'      => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( is_user_logged_in() ) {

			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			} else {
				// Convert the supplied entry statuses $atts['visibility'] to an array.
				if ( ! is_array( $atts['visibility'] ) ) {
					// Trim the space characters if present.
					$atts['visibility'] = str_replace( ' ', '', $atts['visibility'] );

					// Convert to array.
					$atts['visibility'] = explode( ',', $atts['visibility'] );
				}

				$visibility[] = $atts['visibility'];
			}

		} else {
			//var_dump( $connections->options->getAllowPublic() ); die;

			// Display the 'public' entries if the user is not required to be logged in.
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';

			// Display the 'public' entries if the public override shortcode option is enabled.
			if ( $connections->options->getAllowPublicOverride() ) {
				if ( $atts['allow_public_override'] == TRUE ) $visibility[] = 'public';
			}

			// Display the 'public' & 'private' entries if the private override shortcode option is enabled.
			if ( $connections->options->getAllowPrivateOverride() ) {
				// If the user can view private entries then they should be able to view public entries too, so we'll add it. Just check to see if it is already set first.
				if ( ! in_array( 'public', $visibility ) && $atts['private_override'] == TRUE ) $visibility[] = 'public';
				if ( $atts['private_override'] == TRUE ) $visibility[] = 'private';
			}

		}

		$where[] = 'AND ' . CN_ENTRY_TABLE . '.visibility IN (\'' . implode( "', '", $visibility ) . '\')';

		return $where;
	}

	/**
	 * Set up the query to only return the entries based on status.
	 *
	 * @param (array) $where
	 * @param (array) $atts
	 *
	 * @access private
	 * @since 0.7.4
	 * @uses wp_parse_args()
	 * @uses is_user_logged_in()
	 * @uses current_user_can()
	 * @return (array)
	 */
	public static function setQueryStatus( $where, $atts = array() ) {
		$valid = array( 'approved', 'pending' );

		$defaults = array(
			'status' => array( 'approved' )
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Convert the supplied entry statuses $atts['status'] to an array.
		if ( ! is_array( $atts['status'] ) ) {
			// Trim the space characters if present.
			$status = str_replace( ' ', '', $atts['status'] );

			// Convert to array.
			$status = explode( ',', $status );
		} else {
			$status = $atts['status'];
		}

		if ( is_user_logged_in() ) {
			// If 'all' was supplied, set the array to all the permitted entry status types.
			if ( in_array( 'all', $status ) ) $status = $valid;

			// Limit the viewable status per role capability assigned to the current user.
			if ( current_user_can( 'connections_edit_entry' ) ) {
				$permitted = array( 'approved', 'pending' );

				$status = array_intersect( $permitted, $status );

			} elseif ( current_user_can( 'connections_edit_entry_moderated' ) ) {
				$permitted = array( 'approved' );

				$status = array_intersect( $permitted, $status );

			} else {
				$permitted = array( 'approved' );

				$status = array_intersect( $permitted, $status );
			}
		}

		$where[] = 'AND ' . CN_ENTRY_TABLE . '.status IN (\'' . implode( "', '", $status ) . '\')';

		return $where;
	}

	public function upcoming( $atts = array() ) {
		global $wpdb, $connections;

		$validate = new cnValidate();
		$where = array();
		$results = array();

		$permittedUpcomingTypes = array( 'anniversary', 'birthday' );

		$defaults = array(
			'type'                  => 'birthday',
			'days'                  => '30',
			'today'                 => TRUE,
			'visibility'            => array(),
			'allow_public_override' => FALSE,
			'private_override'      => FALSE
		);

		$atts = $validate->attributesArray( $defaults, $atts );

		// Ensure that the upcoming type is one of the supported types. If not, reset to the default.
		$atts['type'] = in_array( $atts['type'], $permittedUpcomingTypes ) ? $atts['type'] : 'birthday';


		// Show only public or private [if permitted] entries.
		/*if ( is_user_logged_in() || $atts['private_override'] != FALSE ) {
			$visibilityfilter = " AND (visibility='private' OR visibility='public') AND (".$atts['list_type']." != '')";
		} else {
			$visibilityfilter = " AND (visibility='public') AND (`".$atts['list_type']."` != '')";
		}*/


		/*
		 * // START --> Set up the query to only return the entries based on user permissions.
		 */
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			//var_dump( $connections->options->getAllowPublic() ); die;

			// Display the 'public' entries if the user is not required to be logged in.
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';

			// Display the 'public' entries if the public override shortcode option is enabled.
			if ( $connections->options->getAllowPublicOverride() ) {
				if ( $atts['allow_public_override'] == TRUE ) $visibility[] = 'public';
			}

			// Display the 'public' & 'private' entries if the private override shortcode option is enabled.
			if ( $connections->options->getAllowPrivateOverride() ) {
				// If the user can view private entries then they should be able to view public entries too, so we'll add it. Just check to see if it is already set first.
				if ( ! in_array( 'public', $visibility ) && $atts['private_override'] == TRUE ) $visibility[] = 'public';
				if ( $atts['private_override'] == TRUE ) $visibility[] = 'private';
			}
		}

		$where[] = 'AND visibility IN (\'' . implode( "', '", $visibility ) . '\')';
		//$where[] = "AND (visibility='private' OR visibility='public')";
		/*
		 * // END --> Set up the query to only return the entries based on user permissions.
		 */

		// Only select the entries with a date.
		$where[] = "AND ( `".$atts['type']."` != '' )";

		// Get the current date from WP which should have the current time zone offset.
		// $wpCurrentDate = date( 'Y-m-d', $connections->options->wpCurrentTime );

		// Get todays date, formatted for use in the query.
		$date = gmdate( 'Y-m-d', current_time( 'timestamp' ) );

		// FROM_UNIXTIME automatically adjusts for the local time. Offset is applied in query to ensure event is returned in current WP set timezone.
		// $sqlTimeOffset = $connections->options->sqlTimeOffset;
		// $results       = $wpdb->get_results( 'SELECT TIME_FORMAT( TIMEDIFF(NOW(), UTC_TIMESTAMP), \'%H:%i‌​\' ) AS offset' );
		// $offset        = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $results[0]->offset ); // The result from SQL seems to have some non-printing control characters, remove them.
		// $offset = (int) $offset >= 0 ? '+' . $offset : $offset;
		// var_dump( $offset );

		// Whether or not to include the event occurring today or not.
		$includeToday = ( $atts['today'] ) ? '<=' : '<';

		// $sql = "SELECT * FROM ".CN_ENTRY_TABLE." WHERE"
		// 	. "  (YEAR(DATE_ADD('$wpCurrentDate', INTERVAL ".$atts['days']." DAY))"
		// 	. " - YEAR(DATE_ADD(FROM_UNIXTIME(`".$atts['type']."`), INTERVAL ".$sqlTimeOffset." SECOND)) )"
		// 	. " - ( MID(DATE_ADD('$wpCurrentDate', INTERVAL ".$atts['days']." DAY),5,6)"
		// 	. " < MID(DATE_ADD(FROM_UNIXTIME(`".$atts['type']."`), INTERVAL ".$sqlTimeOffset." SECOND),5,6) )"
		// 	. " > ( YEAR('$wpCurrentDate')"
		// 	. " - YEAR(DATE_ADD(FROM_UNIXTIME(`".$atts['type']."`), INTERVAL ".$sqlTimeOffset." SECOND)) )"
		// 	. " - ( MID('$wpCurrentDate',5,6)"
		// 	. " ".$includeToday." MID(DATE_ADD(FROM_UNIXTIME(`".$atts['type']."`), INTERVAL ".$sqlTimeOffset." SECOND),5,6) )"
		// 	. " ".implode( ' ', $where );
		// print_r($sql);

		/*
		 * The FROM_UNIXTIME function will return the date offset to the local system timezone.
		 * The dates were not saved in GMT time and since FROM_UNIXTIME is adjusting for the local system timezone
		 * it could cause dates to shift days. The solution is to take the timezone shifted date from FROM_UNIXTIME
		 * and convert it using CONVERT_TZ from the local system timezone to GMT.
		 */
		$sql = 'SELECT * FROM ' . CN_ENTRY_TABLE . ' WHERE '
			. '  ( YEAR( DATE_ADD( \'' . $date . '\', INTERVAL ' . absint( $atts['days'] ) . ' DAY ) )'
			. ' - YEAR( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ) ) )'
			. ' - ( MID( DATE_ADD( \'' . $date . '\' , INTERVAL ' . absint( $atts['days'] ) . ' DAY ), 5, 6 )'
			. ' < MID( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ), 5, 6 ) )'
			. ' > ( YEAR( \'' . $date . '\' )'
			. ' - YEAR( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ) ) )'
			. ' - ( MID( \'' . $date . '\', 5, 6 )'
			. ' ' . $includeToday . ' MID( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ), 5, 6 ) )'
			. ' ' . implode( ' ', $where );
		// print_r($sql);

		$results = $wpdb->get_results( $sql );
		// print_r($results);


		if ( ! empty( $results ) ) {
			/*The SQL returns an array sorted by the birthday and/or anniversary date. However the year end wrap needs to be accounted for.
			Otherwise earlier months of the year show before the later months in the year. Example Jan before Dec. The desired output is to show
			Dec then Jan dates.  This function checks to see if the month is a month earlier than the current month. If it is the year is changed to the following year rather than the current.
			After a new list is built, it is resorted based on the date.*/
			foreach ( $results as $key => $row ) {
				if ( gmmktime( 23, 59, 59, gmdate( 'm', $row->$atts['type'] ), gmdate( 'd', $row->$atts['type'] ), gmdate( 'Y', $connections->options->wpCurrentTime ) ) < $connections->options->wpCurrentTime ) {
					$dateSort[] = $row->$atts['type'] = gmmktime( 0, 0, 0, gmdate( 'm', $row->$atts['type'] ), gmdate( 'd', $row->$atts['type'] ), gmdate( 'Y', $connections->options->wpCurrentTime ) + 1 );
				}
				else {
					$dateSort[] = $row->$atts['type'] = gmmktime( 0, 0, 0, gmdate( 'm', $row->$atts['type'] ), gmdate( 'd', $row->$atts['type'] ), gmdate( 'Y', $connections->options->wpCurrentTime ) );
				}
			}

			array_multisort( $dateSort, SORT_ASC, $results );
		}


		return $results;
	}

	public function entryCategories( $id ) {
		global $wpdb;

		// Retrieve the categories.
		$results =  $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM " . CN_TERMS_TABLE . " AS t INNER JOIN " . CN_TERM_TAXONOMY_TABLE . " AS tt ON t.term_id = tt.term_id INNER JOIN " . CN_TERM_RELATIONSHIP_TABLE . " AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = 'category' AND tr.entry_id = %d ", $id ) );
		//SELECT t.*, tt.* FROM wp_connections_terms AS t INNER JOIN wp_connections_term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN wp_connections_term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = 'category' AND tr.entry_id = 325

		if ( ! empty( $results ) ) {
			usort( $results, array( &$this, 'sortTermsByName' ) );
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
	 * @param array   $suppliedAttr Accepted values as noted above.
	 * @param bool    $returnIDs    Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the address data will be returned.
	 * @return array
	 */
	public function addresses( $suppliedAttr , $returnData = TRUE ) {
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

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract( $atts );


		if ( ! empty( $id ) ) {
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );

			if ( ! empty( $preferred ) ) {
				$where[] = $wpdb->prepare( 'AND `preferred` = %d', (bool) $preferred );
			}

			if ( ! empty( $type ) ) {
				if ( ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				$where[] = stripslashes( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode( "', '", (array) $type ) ) );
			}
		}

		if ( ! empty( $city ) ) {
			if ( ! is_array( $city ) ) $city = explode( ',' , trim( $city ) );

			$where[] = stripslashes( $wpdb->prepare( 'AND `city` IN (\'%s\')', implode( "', '", (array) $city ) ) );
		}

		if ( ! empty( $state ) ) {
			if ( ! is_array( $state ) ) $state = explode( ',' , trim( $state ) );

			$where[] = stripslashes( $wpdb->prepare( 'AND `state` IN (\'%s\')', implode( "', '", (array) $state ) ) );
		}

		if ( ! empty( $zipcode ) ) {
			if ( ! is_array( $zipcode ) ) $zipcode = explode( ',' , trim( $zipcode ) );

			$where[] = stripslashes( $wpdb->prepare( 'AND `zipcode` IN (\'%s\')', implode( "', '", (array) $zipcode ) ) );
		}

		if ( ! empty( $country ) ) {
			if ( ! is_array( $country ) ) $country = explode( ',' , trim( $country ) );

			$where[] = stripslashes( $wpdb->prepare( 'AND `country` IN (\'%s\')', implode( "', '", (array) $country ) ) );
		}

		if ( ! empty( $coordinates ) ) {
			if ( ! empty( $coordinates['latitude'] ) && ! empty( $coordinates['longitude'] ) ) {
				$where[] = $wpdb->prepare( 'AND `latitude` = %d', $coordinates['latitude'] );
				$where[] = $wpdb->prepare( 'AND `longitude` = %d', $coordinates['longitude'] );
			}
		}

		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", (array) $visibility ) . '\')';

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_ADDRESS_TABLE . '.*

					FROM ' . CN_ENTRY_ADDRESS_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`';

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id

					FROM ' . CN_ENTRY_ADDRESS_TABLE . ' ' . ' ' . implode( ' ', $where );

			//print_r($sql);
			$results = $wpdb->get_col( $sql );
		}

		if ( empty( $results ) ) return array();

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
	 * @param array   $suppliedAttr Accepted values as noted above.
	 * @param bool    $returnIDs    Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the phone number data will be returned.
	 * @return array
	 */
	public function phoneNumbers( $suppliedAttr , $returnData = TRUE ) {
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

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract( $atts );


		if ( ! empty( $id ) ) {
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );

			if ( ! empty( $preferred ) ) {
				$where[] = $wpdb->prepare( 'AND `preferred` = %d', (bool) $preferred );
			}

			if ( ! empty( $type ) ) {
				if ( ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				$where[] = stripslashes( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode( "', '", (array) $type ) ) );
			}
		}

		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", (array) $visibility ) . '\')';

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_PHONE_TABLE . '.*

					FROM ' . CN_ENTRY_PHONE_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`';

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_PHONE_TABLE . '.entry_id

					FROM ' . CN_ENTRY_PHONE_TABLE . ' ' . ' ' . implode( ' ', $where );

			//print_r($sql);
			$results = $wpdb->get_col( $sql );
		}

		if ( empty( $results ) ) return array();

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
	 * @param array   $suppliedAttr Accepted values as noted above.
	 * @param bool    $returnIDs    Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the email address data will be returned.
	 * @return array
	 */
	public function emailAddresses( $suppliedAttr , $returnData = TRUE ) {
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

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract( $atts );


		if ( ! empty( $id ) ) {
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );

			if ( ! empty( $preferred ) ) {
				$where[] = $wpdb->prepare( 'AND `preferred` = $d', (bool) $preferred );
			}

			if ( ! empty( $type ) ) {
				if ( ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				$where[] = stripslashes( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode( "', '", (array) $type ) ) );
			}
		}

		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", (array) $visibility ) . '\')';

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_EMAIL_TABLE . '.*

					FROM ' . CN_ENTRY_EMAIL_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`';

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_EMAIL_TABLE . '.entry_id

					FROM ' . CN_ENTRY_EMAIL_TABLE . ' ' . ' ' . implode( ' ', $where );

			//print_r($sql);
			$results = $wpdb->get_col( $sql );
		}

		if ( empty( $results ) ) return array();

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
	 * @param array   $suppliedAttr Accepted values as noted above.
	 * @param bool    $returnData   Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the IM IDs data will be returned.
	 * @return array
	 */
	public function imIDs( $suppliedAttr , $returnData = TRUE ) {
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

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract( $atts );


		if ( ! empty( $id ) ) {
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );

			if ( ! empty( $preferred ) ) {
				$where[] = $wpdb->prepare( 'AND `preferred` = %d', (bool) $preferred );
			}

			if ( ! empty( $type ) ) {
				if ( ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				$where[] = stripslashes( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode( "', '", (array) $type ) ) );
			}
		}

		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", (array) $visibility ) . '\')';

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_MESSENGER_TABLE . '.*

					FROM ' . CN_ENTRY_MESSENGER_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`';

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_MESSENGER_TABLE . '.entry_id

					FROM ' . CN_ENTRY_MESSENGER_TABLE . ' ' . ' ' . implode( ' ', $where );

			//print_r($sql);
			$results = $wpdb->get_col( $sql );
		}

		if ( empty( $results ) ) return array();

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
	 * @param array   $suppliedAttr Accepted values as noted above.
	 * @param bool    $returnData   Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the social network data will be returned.
	 * @return array
	 */
	public function socialMedia( $suppliedAttr , $returnData = TRUE ) {
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

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract( $atts );


		if ( ! empty( $id ) ) {
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );

			if ( ! empty( $preferred ) ) {
				$where[] = $wpdb->prepare( 'AND `preferred` = %d', (bool) $preferred );
			}

			if ( ! empty( $type ) ) {
				if ( ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				$where[] = stripslashes( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode( "', '", (array) $type ) ) );
			}
		}

		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", (array) $visibility ) . '\')';

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_SOCIAL_TABLE . '.*

					FROM ' . CN_ENTRY_SOCIAL_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`';

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_SOCIAL_TABLE . '.entry_id

					FROM ' . CN_ENTRY_SOCIAL_TABLE . ' ' . ' ' . implode( ' ', $where );

			//print_r($sql);
			$results = $wpdb->get_col( $sql );
		}

		if ( empty( $results ) ) return array();

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
	 * @param array   $suppliedAttr Accepted values as noted above.
	 * @param bool    $returnData   Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the link data will be returned.
	 * @return array
	 */
	public function links( $suppliedAttr , $returnData = TRUE ) {
		global $wpdb, $connections, $current_user;

		get_currentuserinfo();
		$validate = new cnValidate();
		$where[] = 'WHERE 1=1';

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaultAttr['id'] = NULL;
		$defaultAttr['preferred'] = NULL;
		$defaultAttr['image'] = NULL;
		$defaultAttr['logo'] = NULL;
		$defaultAttr['type'] = NULL;

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract( $atts );


		if ( ! empty( $id ) ) {
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );

			if ( ! empty( $preferred ) ) {
				$where[] = $wpdb->prepare( 'AND `preferred` = %d', (bool) $preferred );
			}

			if ( ! empty( $image ) ) {
				$where[] = $wpdb->prepare( 'AND `image` = %d', (bool) $image );
			}

			if ( ! empty( $logo ) ) {
				$where[] = $wpdb->prepare( 'AND `logo` = %d', (bool) $logo );
			}

			if ( ! empty( $type ) ) {
				if ( ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				$where[] = stripslashes( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode( "', '", (array) $type ) ) );
			}
		}

		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", (array) $visibility ) . '\')';

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_LINK_TABLE . '.*

					FROM ' . CN_ENTRY_LINK_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`';

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_LINK_TABLE . '.entry_id

					FROM ' . CN_ENTRY_LINK_TABLE . ' ' . ' ' . implode( ' ', $where );

			//print_r($sql);
			$results = $wpdb->get_col( $sql );
		}

		if ( empty( $results ) ) return array();

		//print_r($results);
		return $results;
	}


	/**
	 * Returns as an array of objects containing the dates per the defined options.
	 *
	 * $atts['id'] (int) Retrieve the dates of the specified entry by entry id.
	 * $atts['preferred'] (bool) Retrieve the preferred date; id must be supplied.
	 * $atts['type'] (array) || (string) Retrieve specific date types, id must be supplied.
	 *
	 * @param array   $suppliedAttr Accepted values as noted above.
	 * @param bool    $returnIDs    Query just the entry IDs or not. If set to FALSE, only the entry IDs would be returned as an array. If set TRUE, the date data will be returned.
	 * @return array
	 */
	public function dates( $suppliedAttr , $returnData = TRUE ) {
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

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract( $atts );


		if ( ! empty( $id ) ) {
			$where[] = $wpdb->prepare( 'AND `entry_id` = "%d"', $id );

			if ( ! empty( $preferred ) ) {
				$where[] = $wpdb->prepare( 'AND `preferred` = %d', (bool) $preferred );
			}

			if ( ! empty( $type ) ) {
				if ( ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				$where[] = stripslashes( $wpdb->prepare( 'AND `type` IN (\'%s\')', implode( "', '", (array) $type ) ) );
			}
		}

		// Set query string for visibility based on user permissions if logged in.
		if ( is_user_logged_in() ) {
			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {
				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';
			}
			else {
				$visibility[] = $atts['visibility'];
			}
		}
		else {
			if ( $connections->options->getAllowPublic() ) $visibility[] = 'public';
			if ( $atts['allow_public_override'] == TRUE && $connections->options->getAllowPublicOverride() ) $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';
		}

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", (array) $visibility ) . '\')';

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_DATE_TABLE . '.*

					FROM ' . CN_ENTRY_DATE_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`';

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_DATE_TABLE . '.entry_id

					FROM ' . CN_ENTRY_DATE_TABLE . ' ' . ' ' . implode( ' ', $where );

			//print_r($sql);
			$results = $wpdb->get_col( $sql );
		}

		if ( empty( $results ) ) return array();

		//print_r($results);
		return $results;
	}


	/**
	 * Return an array of entry ID/s found with the supplied search terms.
	 *
	 * @todo Allow the fields for each table to be defined as a comma delimited list, convert an array and validate against of list of valid table fields.
	 * @todo Add a filter to allow the search fields to be changed.
	 *
	 * Resources used:
	 *  http://devzone.zend.com/26/using-mysql-full-text-searching/
	 *  http://onlamp.com/onlamp/2003/06/26/fulltext.html
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.2.0
	 * @param array   $suppliedAttr [optional]
	 * @return array
	 */
	public function search( $suppliedAttr = array() ) {
		global $wpdb, $connections;

		$validate = new cnValidate();
		$results = array();
		$like = array();
		$search = $connections->options->getSearchFields();

		// If no search search fields are set, return an empty array.
		if ( empty( $search ) ) return array();

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaultAttr['terms'] = array();

		if ( in_array( 'family_name' , $search ) ) $defaultAttr['fields_entry'][] = 'family_name';
		if ( in_array( 'first_name' , $search ) ) $defaultAttr['fields_entry'][] = 'first_name';
		if ( in_array( 'middle_name' , $search ) ) $defaultAttr['fields_entry'][] = 'middle_name';
		if ( in_array( 'last_name' , $search ) ) $defaultAttr['fields_entry'][] = 'last_name';
		if ( in_array( 'title' , $search ) ) $defaultAttr['fields_entry'][] = 'title';
		if ( in_array( 'organization' , $search ) ) $defaultAttr['fields_entry'][] = 'organization';
		if ( in_array( 'department' , $search ) ) $defaultAttr['fields_entry'][] = 'department';
		if ( in_array( 'contact_first_name' , $search ) ) $defaultAttr['fields_entry'][] = 'contact_first_name';
		if ( in_array( 'contact_last_name' , $search ) ) $defaultAttr['fields_entry'][] = 'contact_last_name';
		if ( in_array( 'bio' , $search ) ) $defaultAttr['fields_entry'][] = 'bio';
		if ( in_array( 'notes' , $search ) ) $defaultAttr['fields_entry'][] = 'notes';

		if ( in_array( 'address_line_1' , $search ) ) $defaultAttr['fields_address'][] = 'line_1';
		if ( in_array( 'address_line_2' , $search ) ) $defaultAttr['fields_address'][] = 'line_2';
		if ( in_array( 'address_line_3' , $search ) ) $defaultAttr['fields_address'][] = 'line_3';
		if ( in_array( 'address_city' , $search ) ) $defaultAttr['fields_address'][] = 'city';
		if ( in_array( 'address_state' , $search ) ) $defaultAttr['fields_address'][] = 'state';
		if ( in_array( 'address_zipcode' , $search ) ) $defaultAttr['fields_address'][] = 'zipcode';
		if ( in_array( 'address_country' , $search ) ) $defaultAttr['fields_address'][] = 'country';

		if ( in_array( 'phone_number' , $search ) ) $defaultAttr['fields_phone'][] = 'number';

		$atts = $validate->attributesArray( $defaultAttr, $suppliedAttr );
		//print_r($atts);

		// @todo Validate each fiels array to ensure only permitted fields will be used.
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		// If no search terms were entered, return an empty array.
		if ( empty( $atts['terms'] ) ) return array();

		// If value is a string, string the white space and covert to an array.
		if ( ! is_array( $atts['terms'] ) ) $atts['terms'] = explode( ' ' , trim( $atts['terms'] ) );

		// Trim any white space from around the terms in the array.
		array_walk( $atts['terms'] , 'trim' );


		/*
		 * Perform search using FULLTEXT if enabled.
		 *
		 * Perform the search on each table individually because joining the tables doesn't scale when
		 * there are a large number of entries.
		 *
		 * NOTES:
		 * 	The following is the error reported by MySQL when DB does not support FULLTEXT:  'The used table type doesn't support FULLTEXT indexes'
		 * 	If DB does not support FULLTEXT the query will fail and the $results will be an empty array.
		 *
		 * 	FULLTEXT Restrictions as noted here: http://onlamp.com/onlamp/2003/06/26/fulltext.html
		 *
		 * 		Some of the default behaviors of these restrictions can be changed in your my.cnf or using the SET command
		 *
		 * 		FULLTEXT indices are NOT supported in InnoDB tables.
		 * 		MySQL requires that you have at least three rows of data in your result set before it will return any results.
		 * 		By default, if a search term appears in more than 50% of the rows then MySQL will not return any results.
		 * 		By default, your search query must be at least four characters long and may not exceed 254 characters.
		 * 		MySQL has a default stopwords file that has a list of common words (i.e., the, that, has) which are not returned in your search. In other words, searching for the will return zero rows.
		 * 		According to MySQL's manual, the argument to AGAINST() must be a constant string. In other words, you cannot search for values returned within the query.
		 */
		if ( $connections->settings->get( 'connections', 'connections_search', 'fulltext_enabled' ) ) {
			// Convert the search terms to a string adding the wild card to the end of each term to allow wider search results.
			//$terms = implode( '* ' , $atts['terms'] ) . '*';
			$terms = '+' . implode( ' +' , $atts['terms'] );
			//$terms = implode( ' ' , $atts['terms'] );

			/*
			 * Only search the primary records if at least one fields is selected to be searched.
			 */
			if ( ! empty( $defaultAttr['fields_entry'] ) ) {
				$sql = $wpdb->prepare( 'SELECT ' . CN_ENTRY_TABLE . '.id
											FROM ' . CN_ENTRY_TABLE . '
											WHERE MATCH (' . implode( ', ' , $atts['fields_entry'] ) . ') AGAINST (%s IN BOOLEAN MODE)' , $terms );
				//print_r($sql);
				$results = $wpdb->get_col( $sql );
			}

			/*
			 * Only search the address records if at least one fields is selected to be searched.
			 */
			if ( ! empty( $defaultAttr['fields_address'] ) ) {
				$sql = $wpdb->prepare( 'SELECT ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id
											FROM ' . CN_ENTRY_ADDRESS_TABLE . '
											WHERE MATCH (' . implode( ', ' , $atts['fields_address'] ) . ') AGAINST (%s IN BOOLEAN MODE)' , $terms );
				//print_r($sql);
				$results = array_merge( $results, $wpdb->get_col( $sql ) );
			}

			/*
			 * Only search the phone records if thefield is selected to be search.
			 */
			if ( ! empty( $defaultAttr['fields_phone'] ) ) {
				$sql = $wpdb->prepare( 'SELECT ' . CN_ENTRY_PHONE_TABLE . '.entry_id
											FROM ' . CN_ENTRY_PHONE_TABLE . '
											WHERE MATCH (' . implode( ', ' , $atts['fields_phone'] ) . ') AGAINST (%s IN BOOLEAN MODE)' , $terms );
				//print_r($sql);
				$results = array_merge( $results, $wpdb->get_col( $sql ) );
			}
		}

		/*
		 * If no results are found, perhaps to the way MySQL performs FULLTEXT queries, FULLTEXT search being disabled
		 * or the DB not supporting FULLTEXT, run a LIKE query.
		 *
		 * Perform the search on each table individually because joining the tables doesn't scale when
		 * there are a large number of entries.
		 */
		if ( $connections->settings->get( 'connections', 'connections_search', 'keyword_enabled' ) ) {
			/*
			 * Only search the primary records if at least one fields is selected to be searched.
			 */
			if ( ! empty( $defaultAttr['fields_entry'] ) ) {
				foreach ( $atts['terms'] as $term ) {
					/*
					 * Attempt to secure the query using $wpdb->prepare() and like_escape()
					 *
					 * Since $wpdb->prepare() required var for each directive in the query string we'll use array_fill
					 * where the count based on the number of columns that will be searched.
					 */
					$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $defaultAttr['fields_entry'] ) . ' LIKE %s ' , array_fill( 0 , count( $defaultAttr['fields_entry'] ) , '%' . like_escape( $term ) . '%' ) );
				}

				$sql =  'SELECT ' . CN_ENTRY_TABLE . '.id
									FROM ' . CN_ENTRY_TABLE . '
									WHERE (' . implode( ') OR (' , $like ) . ')';
				//print_r($sql);

				$results = array_merge( $results, $wpdb->get_col( $sql ) );
				//print_r($results);die;
			}

			/*
			 * Only search the address records if at least one fields is selected to be searched.
			 */
			if ( ! empty( $defaultAttr['fields_address'] ) ) {
				$like = array(); // Reset the like array.

				foreach ( $atts['terms'] as $term ) {
					/*
					 * Attempt to secure the query using $wpdb->prepare() and like_escape()
					 *
					 * Since $wpdb->prepare() required var for each directive in the query string we'll use array_fill
					 * where the count based on the number of columns that will be searched.
					 */
					$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $defaultAttr['fields_address'] ) . ' LIKE %s ' , array_fill( 0 , count( $defaultAttr['fields_address'] ) , '%' . like_escape( $term ) . '%' ) );
				}

				$sql =  'SELECT ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id
									FROM ' . CN_ENTRY_ADDRESS_TABLE . '
									WHERE (' . implode( ') OR (' , $like ) . ')';
				//print_r($sql);

				$results = array_merge( $results, $wpdb->get_col( $sql ) );
				//print_r($results);
			}

			/*
			 * Only search the phone records if thefield is selected to be search.
			 */
			if ( ! empty( $defaultAttr['fields_phone'] ) ) {
				$like = array(); // Reset the like array.

				foreach ( $atts['terms'] as $term ) {
					/*
					 * Attempt to secure the query using $wpdb->prepare() and like_escape()
					 *
					 * Since $wpdb->prepare() required var for each directive in the query string we'll use array_fill
					 * where the count based on the number of columns that will be searched.
					 */
					$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $defaultAttr['fields_phone'] ) . ' LIKE %s ' , array_fill( 0 , count( $defaultAttr['fields_phone'] ) , '%' . like_escape( $term ) . '%' ) );
				}

				$sql =  'SELECT ' . CN_ENTRY_PHONE_TABLE . '.entry_id
									FROM ' . CN_ENTRY_PHONE_TABLE . '
									WHERE (' . implode( ') OR (' , $like ) . ')';
				//print_r($sql);

				$results = array_merge( $results, $wpdb->get_col( $sql ) );
				//print_r($results);
			}

		}

		return array_unique( $results );
	}

	/**
	 * Sort the entries by the user set attributes.
	 *
	 * $object -- syntax is field|SORT_ASC(SORT_DESC)|SORT_REGULAR(SORT_NUMERIC)(SORT_STRING)
	 *
	 * example  -- 'state|SORT_ASC|SORT_STRING, last_name|SORT_DESC|SORT_REGULAR
	 *
	 *
	 * Available order_by fields:
	 *  id
	 *  date_added
	 *  date_modified
	 *  first_name
	 *  last_name
	 *  organization
	 *  department
	 *  city
	 *  state
	 *  zipcode
	 *  country
	 *  birthday
	 *  anniversary
	 *
	 * Order Flags:
	 *  SORT_ACS
	 *  SORT_DESC
	 *  SPECIFIED**
	 *  RANDOM**
	 *
	 * Sort Types:
	 *  SORT_REGULAR
	 *  SORT_NUMERIC
	 *  SORT_STRING
	 *
	 * **NOTE: The SPECIFIED and RANDOM Order Flags can only be used
	 * with the id field. The SPECIFIED flag must be used in conjuction
	 * with $suppliedIDs which can be either a comma delimited sting or
	 * an indexed array of entry IDs. If this is set, other sort fields/flags
	 * are ignored.
	 *
	 * @access private
	 * @since unknown
	 * @version 1.0
	 * @deprecated since unkown
	 * @param array   of object $entries
	 * @param string  $orderBy
	 * @param string  || array $ids [optional]
	 * @return array of objects
	 */
	private function orderBy( &$entries, $orderBy, $suppliedIDs = NULL ) {
		if ( empty( $entries ) || empty( $orderBy ) ) return $entries;

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
		$sortFields = explode( ',', $orderBy );

		// For each field the sort order can be defined as well as the sort type
		foreach ( $sortFields as $sortField ) {
			$sortAtts[] = explode( '|', $sortField );
		}

		/*
		 * Dynamically build the variables that will be used for the array_multisort.
		 *
		 * The field type should be the first item in the array if the user
		 * constructed the shortcode attribute correctly.
		 */
		foreach ( $sortAtts as $field ) {
			// Trim any spaces the user might have added to the shortcode attribute.
			$field[0] = strtolower( trim( $field[0] ) );

			// If a user included a sort field that is invalid/mis-spelled it is skipped since it can not be used.
			if ( !in_array( $field[0], $orderFields ) ) continue;

			// The dynamic variable are being created and populated.
			foreach ( $entries as $key => $row ) {
				$entry = new cnEntry( $row );

				switch ( $field[0] ) {
				case 'id':
					${$field[0]}[$key] = $entry->getId();
					break;

				case 'date_added':
					${$field[0]}[$key] = $entry->getDateAdded( 'U' );
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

				case ( $field[0] === 'city' || $field[0] === 'state' || $field[0] === 'zipcode' || $field[0] === 'country' ):
					if ( $entry->getAddresses() ) {
						$addresses = $entry->getAddresses();

						foreach ( $addresses as $address ) {
							//${$field[0]}[$key] = $address[$field[0]];
							${$field[0]}[$key] = $address->$field[0];

							// Only set the data from the first address.
							break;
						}

					}
					else {
						${$field[0]}[$key] = NULL;
					}
					break;

				case 'birthday':
					${$field[0]}[$key] = strtotime( $entry->getBirthday() );
					break;

				case 'anniversary':
					${$field[0]}[$key] = strtotime( $entry->getAnniversary() );
					break;
				}

			}
			// The sorting order to be determined by a lowercase copy of the original array.
			$$field[0] = array_map( 'strtolower', $$field[0] );

			// The arrays to be sorted must be passed by reference or it won't work.
			$sortParams[] = &$$field[0];

			// Add the flag and sort type to the sort parameters if they were supplied in the shortcode attribute.
			foreach ( $field as $key => $flag ) {
				// Trim any spaces the user might have added and change the string to uppercase..
				$flag = strtoupper( trim( $flag ) );

				// If a user included a sort tag that is invalid/mis-spelled it is skipped since it can not be used.
				if ( !array_key_exists( $flag, $sortFlags ) ) continue;

				/*
				 * If the order is specified set the variable to true and continue
				 * because SPECIFIED should not be added to the $sortParams array
				 * as that would be an invalid argument for the array multisort.
				 */
				if ( $flag === 'SPECIFIED' || $flag === 'RANDOM' ) {
					$idOrder = $flag;
					continue;
				}

				// Must be pass as reference or the multisort will fail.
				$sortParams[] = &$sortFlags[$flag];
				unset( $flag );
			}
		}

		/*
		 *
		 */
		if ( isset( $id ) && isset( $idOrder ) ) {
			switch ( $idOrder ) {
			case 'SPECIFIED':
				$sortedEntries = array();

				/*
					 * Convert the supplied IDs value to an array if it is not.
					 */
				if ( !is_array( $suppliedIDs ) && !empty( $suppliedIDs ) ) {
					// Trim the space characters if present.
					$suppliedIDs = str_replace( ' ', '', $suppliedIDs );
					// Convert to array.
					$suppliedIDs = explode( ',', $suppliedIDs );
				}

				foreach ( $suppliedIDs as $entryID ) {
					$sortedEntries[] = $entries[array_search( $entryID, $id )];
				}

				$entries = $sortedEntries;
				return $entries;
				break;

			case 'RANDOM':
				shuffle( $entries );
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
		call_user_func_array( 'array_multisort', $sortParams );

		return $entries;
	}

	/**
	 * Sorts terms by name.
	 *
	 * @param object  $a
	 * @param object  $b
	 * @return integer
	 */
	private function sortTermsByName( $a, $b ) {
		return strcmp( $a->name, $b->name );
	}

	/**
	 * Sorts terms by ID.
	 *
	 * @param object  $a
	 * @param object  $b
	 * @return integer
	 */
	private function sortTermsByID( $a, $b ) {
		if ( $a->term_id > $b->term_id ) {
			return 1;
		}
		elseif ( $a->term_id < $b->term_id ) {
			return -1;
		}
		else {
			return 0;
		}
	}

	/**
	 * Total record count based on current user permissions.
	 *
	 * @access public
	 * @since unknown
	 * @global $wpdb
	 * @global $connections
	 * @uses wp_parse_args()
	 * @uses is_user_logged_in()
	 * @uses current_user_can()
	 * @uses $wpdb->get_var()
	 * @param (array)
	 * @return (integer)
	 */
	public static function recordCount( $atts ) {
		global $wpdb, $connections;

		$where[]    = 'WHERE 1=1';
		$visibility = array();
		$permitted  = array( 'approved', 'pending' );

		$defaults = array(
			'public_override'  => TRUE,
			'private_override' => TRUE,
			'status'           => array(),
			 );

		$atts = wp_parse_args( $atts, $defaults );

		// Convert the supplied statuses into an array.
		if ( ! is_array( $atts['status'] ) ) {

			// Remove whitespace.
			$atts['status'] = trim( str_replace( ' ', '', $atts['status'] ) );

			$atts['status'] = explode( ',', $atts['status'] );
		}

		// Permit only the support status to be queried.
		$atts['status'] = array_intersect( $atts['status'], $permitted );

		if ( is_user_logged_in() ) {

			if ( current_user_can( 'connections_view_public' ) ) $visibility[]                 = 'public';
			if ( current_user_can( 'connections_view_private' ) ) $visibility[]                = 'private';
			if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';

			// Set query status per role capability assigned to the current user.
			if ( current_user_can( 'connections_edit_entry' ) ) {

				// Set the entry statuses the user is permitted to view based on their role.
				$userPermitted = array( 'approved', 'pending' );

				$status = array_intersect( $userPermitted, $atts['status'] );

			} elseif ( current_user_can( 'connections_edit_entry_moderated' ) ) {

				// Set the entry statuses the user is permitted to view based on their role.
				$userPermitted = array( 'approved' );

				$status = array_intersect( $userPermitted, $atts['status'] );

			} else {

				// Set the entry statuses the user is permitted to view based on their role.
				$userPermitted = array( 'approved' );

				$status = array_intersect( $userPermitted, $atts['status'] );
			}

		} else {

			if ( $connections->options->getAllowPublic() )                                               $visibility[] = 'public';
			if ( $atts['public_override'] == TRUE && $connections->options->getAllowPublicOverride() )   $visibility[] = 'public';
			if ( $atts['private_override'] == TRUE && $connections->options->getAllowPrivateOverride() ) $visibility[] = 'private';

			$status = array( 'approved' );
		}

		if ( ! empty( $status ) )     $where[] = 'AND `status` IN (\'' . implode( "', '", $status ) . '\')';

		if ( ! empty( $visibility ) ) $where[] = 'AND `visibility` IN (\'' . implode( "', '", $visibility ) . '\')';

		$results = $wpdb->get_var( 'SELECT COUNT(`id`) FROM ' . CN_ENTRY_TABLE . ' ' . implode( ' ', $where ) );

		return ! empty( $results ) ? $results : 0;
	}

	/**
	 * Limit the returned results.
	 *
	 * This is more or less a hack until limit is properly implemented in the retrieve query.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.1.6
	 * @param array   $results
	 * @return array
	 */
	public function limitList( $results ) {
		$limit = 12;

		return array_slice( $results, 0, $limit, TRUE );
	}

	/**
	 * Remove the entries from the list where the date added was not recorded.
	 *
	 * This is more or less a hack to remove the entries from the list where the date added was not recorded which would be entries added before 0.7.1.1.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.1.6
	 * @param array   $results
	 * @return array
	 */
	public function removeUnknownDateAdded( $results ) {
		foreach ( $results as $key => $entry ) {
			if ( empty( $entry->date_added ) ) unset( $results[$key] );
		}

		return $results;
	}

	/**
	 * Returns all the category terms.
	 *
	 * @return object
	 */
	public function categories() {
		global $connections;

		return $connections->term->getTerms( 'category' );
	}

	/**
	 * Returns category by ID.
	 *
	 * @param interger $id
	 * @return object
	 */
	public function category( $id ) {
		global $connections;

		return $connections->term->getTerm( $id, 'category' );
	}

	/**
	 * Retrieve the children of the supplied parent.
	 *
	 * @param interger $id
	 * @return array
	 */
	public function categoryChildren( $field, $value ) {
		global $connections;

		return $connections->term->getTermChildrenBy( $field, $value, 'category' );
	}

}