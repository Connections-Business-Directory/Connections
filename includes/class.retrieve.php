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
	 * Runtime cache of query results.
	 *
	 * @access  private
	 * @since  0.8
	 * @var array
	 */
	private $results = array();

	/**
	 *
	 * The $atts['meta_query'] can have two different structures when passed to
	 * @see cnMeta_Query::parse_query_vars(), they are:
	 *
	 * array(
	 *     'meta_key'     => (string),
	 *     'meta_value    => (string|array),
	 *     'meta_type     => (string),
	 *     'meta_compare' => (string)
	 * )
	 *
	 * OR
	 *
	 * array(
	 *     'meta_query' =>
	 *         array(
	 *             'key'     => (string),
	 *             'value'   => (string|array),
	 *             'compare' => (string),
	 *             'type'    => (string)
	 *         ),
	 *         [array(...),]
	 * )
	 *
	 * The later, 'meta_query', can have multiple arrays.
	 *
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array
	 * @return array
	 */
	public function entries( $atts = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb, $current_user;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		get_currentuserinfo();

		$select[]             = CN_ENTRY_TABLE . '.*';
		$from[]               = CN_ENTRY_TABLE;
		$join                 = array();
		$where[]              = 'WHERE 1=1';
		$having               = array();
		$orderBy              = array();
		$random               = FALSE;
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
		$defaults['meta_query']            = array();
		$defaults['allow_public_override'] = FALSE;
		$defaults['private_override']      = FALSE;
		$defaults['search_terms']          = NULL;

		// $atts vars to support showing entries within a specified radius.
		$defaults['near_addr']             = NULL;
		$defaults['latitude']              = NULL;
		$defaults['longitude']             = NULL;
		$defaults['radius']                = 10;
		$defaults['unit']                  = 'mi';

		$defaults['lock']                  = FALSE;

		$atts = cnSanitize::args( $atts, $defaults );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		/*
		 * // START -- Process the query vars. \\
		 * NOTE: these will override any values supplied via $atts, which include via the shortcode.
		 */
		if ( ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! is_admin() ) && ! $atts['lock'] ) {

			// Category slug
			$queryCategorySlug = get_query_var( 'cn-cat-slug' );

			if ( ! empty( $queryCategorySlug ) ) {

				// If the category slug is a descendant, use the last slug from the URL for the query.
				$queryCategorySlug = explode( '/' , $queryCategorySlug );

				if ( isset( $queryCategorySlug[ count( $queryCategorySlug ) - 1 ] ) ) $atts['category_slug'] = $queryCategorySlug[ count( $queryCategorySlug ) - 1 ];
			}

			// Category ID
			$queryCategoryID = get_query_var( 'cn-cat' );
			if ( ! empty( $queryCategoryID ) ) $atts['category'] = $queryCategoryID;

			// Category in
			$queryCategoryIn = get_query_var( 'cn-cat-in' );
			if ( ! empty( $queryCategoryIn ) ) {

				$atts['category_in'] = get_query_var( 'cn-cat-in' );
			}

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
			// NOTE: The entry slug is saved in the DB URL encoded, so there's no need to urldecode().
			$queryEntrySlug = get_query_var( 'cn-entry-slug' );
			if ( ! empty( $queryEntrySlug ) ) $atts['slug'] = $queryEntrySlug;

			// Initial character.
			$queryInitialChar = get_query_var( 'cn-char' );
			if ( ! empty( $queryInitialChar ) ) $atts['char'] = urldecode( $queryInitialChar );

			// Pagination
			$queryPage = get_query_var( 'cn-pg' );
			$atts['offset'] = ( ! empty( $queryPage ) ) ? ( $queryPage - 1 ) * $atts['limit'] : $atts['offset'];
			$atts['offset'] = ( $atts['offset'] > 0 ) ? $atts['offset'] : NULL;

			// Search terms
			$searchTerms = get_query_var( 'cn-s' );
			if ( ! empty( $searchTerms ) ) $atts['search_terms'] = $searchTerms;

			// Geo-location
			$queryCoord = get_query_var( 'cn-near-coord' );

			if ( ! empty( $queryCoord ) ) {

				$queryCoord        = explode( ',', $queryCoord );
				$atts['latitude']  = $wpdb->prepare( '%f', $queryCoord[0] );
				$atts['longitude'] = $wpdb->prepare( '%f', $queryCoord[1] );

				// Get the radius, otherwise the default of 10.
				if ( get_query_var( 'cn-radius' ) ) $atts['radius'] = $wpdb->prepare( '%d', get_query_var( 'cn-radius' ) );

				// Sanitize and set the unit.
				$atts['unit'] = get_query_var( 'cn-unit' ) ? sanitize_key( get_query_var( 'cn-unit' ) ) : sanitize_key( $atts['unit'] );
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

			$atts['list_type']           = NULL;
			$atts['category']            = NULL;
			$atts['category_in']         = NULL;
			$atts['wp_current_category'] = NULL;
		}

		if ( ! empty( $atts['slug'] ) ) {

			$atts['near_addr'] = NULL;
			$atts['latitude']  = NULL;
			$atts['longitude'] = NULL;
			$atts['radius']    = 10;
			$atts['unit']      = 'mi';
		}
		/*
		 * // END -- Reset.
		 */

		/*
		 * If in a post get the category names assigned to the post.
		 */
		if ( $atts['wp_current_category'] && ! is_page() ) {

			// Get the current post categories.
			$wpCategories = get_the_category();

			// Build an array of the post categories.
			foreach ( $wpCategories as $wpCategory ) {

				$categoryNames[] = $wpdb->prepare( '%s', $wpCategory->cat_name );
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

				// Add the parent category to the array and remove any whitespace from the beginning/end of the name just in case the user added it when using the shortcode.
				$categoryNames[] = $wpdb->prepare( '%s', trim( $categoryName ) );

				// Retrieve the children categories
				$results = $this->categoryChildren( 'name', $categoryName );

				foreach ( (array) $results as $term ) {

					// Only add the name if it doesn't already exist. If it doesn't sanitize and add to the array.
					if ( ! in_array( $term->name, $categoryNames ) ) $categoryNames[] = $wpdb->prepare( '%s', $term->name );
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

				// Add the parent category to the array and remove any whitespace from the beginning/end of the name in case the user added it when using the shortcode.
				$categorySlugs[] = sanitize_title( trim( $categorySlug ) );

				// Retrieve the children categories.
				$results = $this->categoryChildren( 'slug', $categorySlug );

				foreach ( (array) $results as $term ) {

					// Only add the slug if it doesn't already exist. If it doesn't sanitize and add to the array.
					if ( ! in_array( $term->name, $categorySlugs ) ) $categorySlugs[] = sanitize_title( $term->slug );
				}
			}
		}

		/*
		 * Build an array of all the categories and their children based on the supplied category IDs.
		 */
		if ( ! empty( $atts['category'] ) ) {

			$atts['category'] = wp_parse_id_list( $atts['category'] );

			foreach ( $atts['category'] as $categoryID ) {

				// Add the parent category ID to the array.
				$categoryIDs[] = absint( $categoryID );

				// Retrieve the children categories
				$results = $this->categoryChildren( 'term_id', $categoryID );
				//print_r($results);

				foreach ( (array) $results as $term ) {

					// Only add the ID if it doesn't already exist. If it doesn't sanitize and add to the array.
					if ( ! in_array( $term->term_id, $categoryIDs ) ) $categoryIDs[] = absint( $term->term_id );
				}
			}
		}

		/*
		 * Exclude the specified categories by ID.
		 */
		if ( ! empty( $atts['exclude_category'] ) ) {

			if ( ! isset( $categoryIDs ) ) $categoryIDs = array();

			$atts['exclude_category'] = wp_parse_id_list( $atts['exclude_category'] );

			$categoryIDs = array_diff( (array) $categoryIDs, $atts['exclude_category'] );

			foreach ( $atts['exclude_category'] as $categoryID ) {

				// Add the parent category ID to the array.
				$categoryExcludeIDs[] = absint( $categoryID );

				// Retrieve the children categories
				$results = $this->categoryChildren( 'term_id', $categoryID );
				// var_dump($results);

				foreach ( (array) $results as $term ) {

					// Only add the ID if it doesn't already exist. If it doesn't sanitize and add to the array.
					if ( ! in_array( $term->term_id, $categoryExcludeIDs ) ) $categoryExcludeIDs[] = absint( $term->term_id );
				}
			}

			// Merge the children of the excluded category into the excluded category ID array.
			$atts['exclude_category'] = array_merge( $atts['exclude_category'], (array) $categoryExcludeIDs );

			// Ensure unique values only.
			$atts['exclude_category'] = array_unique( $atts['exclude_category'] );

			$sql = 'SELECT tr.entry_id FROM ' . CN_TERM_RELATIONSHIP_TABLE . ' AS tr
					INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
					WHERE 1=1 AND tt.term_id IN (' . implode( ", ", $atts['exclude_category'] ) . ')';

			// Store the entryIDs that are to be excluded.
			$results = $wpdb->get_col( $sql );
			//print_r($results);

			if ( ! empty( $results ) ) {

				$where[] = 'AND ' . CN_ENTRY_TABLE . '.id NOT IN (' . implode( ", ", $results ) . ')';
			} /*else {
				$where[] = 'AND 1=2';
			}*/
		}

		// Convert the supplied category IDs $atts['category_in'] to an array.
		if ( ! empty( $atts['category_in'] ) ) {

			$atts['category_in'] = wp_parse_id_list( $atts['category_in'] );

			// Remove empty values from the array.
			$atts['category_in'] = array_filter( $atts['category_in'] );

			// Ensure there is something to query after filtering the array.
			if ( ! empty( $atts['category_in'] ) ) {

				// Exclude any category IDs that may have been set.
				$atts['category_in'] = array_diff( $atts['category_in'], (array) $atts['exclude_category'] );

				// Build the query to retrieve entry IDs that are assigned to all the supplied category IDs; operational AND.
				$sql = 'SELECT DISTINCT tr.entry_id FROM ' . CN_TERM_RELATIONSHIP_TABLE . ' AS tr
						INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
						WHERE 1=1 AND tt.term_id IN (' . implode( ", ", $atts['category_in'] ) . ') GROUP BY tr.entry_id HAVING COUNT(*) = ' . count( $atts['category_in'] ) . ' ORDER BY tr.entry_id';

				// Store the entryIDs that exist on all of the supplied category IDs
				$results = $wpdb->get_col( $sql );
				//print_r($results);

				if ( ! empty( $results ) ) {
					$where[] = 'AND ' . CN_ENTRY_TABLE . '.id IN (' . implode( ", ", $results ) . ')';
				} else {
					$where[] = 'AND 1=2';
				}

			}

			/*
			 * This is the query to use to return entry IDs that are in the same categories. The COUNT value
			 * should equal the number of category IDs in the IN() statement.

			   SELECT DISTINCT tr.entry_id FROM `wp_connections_term_relationships` AS tr
			   INNER JOIN `wp_connections_term_taxonomy` AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
			   WHERE 1=1 AND tt.term_id IN ('51','73','76') GROUP BY tr.entry_id HAVING COUNT(*) = 3 ORDER BY tr.entry_id
			 */
		}

		if ( ! empty( $categoryIDs ) || ! empty( $categoryExcludeIDs ) || ! empty( $categoryNames ) || ! empty( $categorySlugs ) ) {
			// Set the query string to INNER JOIN the term relationship and taxonomy tables.
			$join[] = 'INNER JOIN ' . CN_TERM_RELATIONSHIP_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_TERM_RELATIONSHIP_TABLE . '.entry_id )';
			$join[] = 'INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' ON ( ' . CN_TERM_RELATIONSHIP_TABLE . '.term_taxonomy_id = ' . CN_TERM_TAXONOMY_TABLE . '.term_taxonomy_id )';
			$join[] = 'INNER JOIN ' . CN_TERMS_TABLE . ' ON ( ' . CN_TERMS_TABLE . '.term_id = ' . CN_TERM_TAXONOMY_TABLE . '.term_id )';

			// Set the query string to return entries within the category taxonomy.
			$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.taxonomy = \'category\'';

			if ( ! empty( $categoryIDs ) ) {
				$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.term_id IN (' . implode( ", ", $categoryIDs ) . ')';

				unset( $categoryIDs );
			}

			if ( ! empty( $categoryExcludeIDs ) ) {
				$where[] = 'AND ' . CN_TERM_TAXONOMY_TABLE . '.term_id NOT IN (' . implode( ", ", $categoryExcludeIDs ) . ')';

				unset( $categoryExcludeIDs );
			}

			if ( ! empty( $categoryNames ) ) {
				$where[] = 'AND ' . CN_TERMS_TABLE . '.name IN (' . implode( ", ", (array) $categoryNames ) . ')';

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
		if ( ! empty( $atts['id'] ) ) {

			$atts['id'] = wp_parse_id_list( $atts['id'] );

			// Set query string to return specific entries.
			$where[] = 'AND ' . CN_ENTRY_TABLE . '.id IN (\'' . implode( "', '", $atts['id'] ) . '\')';
		}
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

			} else {

				// Set $atts['order_by'] to the order the entry IDs were returned.
				// This is to support the relevancy order results being returned by self::search().
				$atts['order_by'] = 'id|SPECIFIED';
				$atts['id']       = $searchResults;

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
			// Compatibility code to make sure any occurrences of the deprecated entry type connections_group is changed to entry type family
			$atts['list_type'] = str_replace( 'connection_group', 'family', $atts['list_type'] );

			$where[] = 'AND `entry_type` IN (\'' . implode( "', '", (array) $atts['list_type'] ) . '\')';
		}
		/*
		 * // END --> Set up the query to only return the entries that match the supplied entry type.
		 */

		/*
		 * // START --> Set up the query to only return the entries that match the supplied filters.
		 */
		if ( ! empty( $atts['family_name'] ) ) $where[]  = $wpdb->prepare( 'AND `family_name` = %s' , $atts['family_name'] );
		if ( ! empty( $atts['last_name'] ) ) $where[]    = $wpdb->prepare( 'AND `last_name` = %s' , $atts['last_name'] );
		if ( ! empty( $atts['title'] ) ) $where[]        = $wpdb->prepare( 'AND `title` = %s' , $atts['title'] );
		if ( ! empty( $atts['organization'] ) ) $where[] = $wpdb->prepare( 'AND `organization` = %s' , $atts['organization'] );
		if ( ! empty( $atts['department'] ) ) $where[]   = $wpdb->prepare( 'AND `department` = %s' , $atts['department'] );

		if ( ! empty( $atts['city'] ) || ! empty( $atts['state'] ) || ! empty( $atts['zip_code'] ) || ! empty( $atts['country'] ) ) {

			if ( ! isset( $join['address'] ) ) $join['address'] = 'INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id )';

			if ( ! empty( $atts['city'] ) ) $where[]     = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.city = %s' , $atts['city'] );
			if ( ! empty( $atts['state'] ) ) $where[]    = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.state = %s' , $atts['state'] );
			if ( ! empty( $atts['zip_code'] ) ) $where[] = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.zipcode = %s' , $atts['zip_code'] );
			if ( ! empty( $atts['country'] ) ) $where[]  = $wpdb->prepare( 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.country = %s' , $atts['country'] );
		}

		if ( 0 < strlen( $atts['char'] ) ) {

			$having[] = $wpdb->prepare( 'HAVING sort_column LIKE %s', $wpdb->esc_like( $atts['char'] ) . '%' );
		}
		/*
		 * // END --> Set up the query to only return the entries that match the supplied filters.
		 */

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
				$userPermittedEntryStatus = array( 'approved', 'pending' );

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
			$atts['latitude']  = deg2rad( $atts['latitude'] );
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

			// This is required otherwise addresses the user may not have permissions to view will be included in the query
			// which could be confusing since entries could appear to be outside of the search radius when in fact the entry
			// is within the search radius, it is just the address used to determine that is not viewable to the user.
			$where[] = 'AND ' . CN_ENTRY_ADDRESS_TABLE . '.visibility IN (\'' . implode( "', '", (array) $visibility ) . '\')';

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
			'SPECIFIED'         => 'SPECIFIED',
			'RANDOM'            => 'RANDOM',
			'ASC'               => 'ASC',
			'SORT_ASC'          => 'ASC',       // Alias for ASC
			'DESC'              => 'DESC',
			'SORT_DESC'         => 'DESC',      // Alias for DESC
			'NUMERIC'           => '+0',
			'SORT_NUMERIC'      => '+0',        // Alias for NUMERIC
			'SORT_NUMERIC_ASC'  => '+0',        // Alias for NUMERIC
			'NUMERIC_DESC'      => '+0 DESC',
			'SORT_NUMERIC_DESC' => '+0 DESC',   // Alias for NUMERIC_DESC
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
			if ( in_array( $field[0], $orderFields ) || cnString::startsWith( 'meta_key:', $field[0] ) ) {
				// The date_modified actually maps to the `ts` column in the db.
				if ( $field[0] == 'date_modified' ) $field[0] = 'ts';

				// If one of the order fields is an address region add the INNER JOIN to the CN_ENTRY_ADDRESS_TABLE
				if ( $field[0] == 'city' || $field[0] == 'state' || $field[0] == 'zipcode' || $field[0] == 'country' ) {

					if ( ! isset( $join['address'] ) ) $join['address'] = 'INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' ON ( ' . CN_ENTRY_TABLE . '.id = ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id )';
				}

				if ( cnString::startsWith( 'meta_key:', $field[0] ) ) {

					// Extract the meta key name from $field[0].
					$meta = explode( ':', $field[0] );

					// Ensure the meta key does exist and is not empty before altering the query.
					if ( isset( $meta[1] ) && ! empty( $meta[1] ) ) {

						$atts['meta_query']['meta_query'][] = array( 'key' => $meta[1] );

						if ( 1 < count( $atts['meta_query']['meta_query'] ) ) {

							$field[0] = 'mt' . ( count( $atts['meta_query']['meta_query'] ) - 1 ) . '.meta_value';

						} else {

							$field[0] = CN_ENTRY_TABLE_META . '.meta_value';
						}
					}
				}

				// If we're ordering by anniversary or birthday, we need to convert the string to a UNIX timestamp so it is properly ordered.
				// Otherwise, it is sorted as a string which can give some very odd results compared to what is expected.
				if ( $field[0] == 'anniversary' || $field[0] == 'birthday' ) {

					$field[0] = 'FROM_UNIXTIME( ' . $field[0] . ' )';
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
									$orderBy = array( 'FIELD( ' . CN_ENTRY_TABLE . '.id, ' . implode( ', ', (array) $atts['id'] ) . ' )' );
								}
								break;

							/*
							 * Randomize the order of the results.
							 */
							case 'RANDOM':

								$random   = TRUE;
								break;

								/*
								 * Return the results in ASC or DESC order.
								 */
							default:

								$orderBy[] = $field[0] . ' ' . $orderFlags[ $field[1] ];
								break;
						}

					} else {

						$orderBy[] = $field[0];
					}

				} else {

					$orderBy[] = $field[0];
				}
			}
		}
		//}

		if ( ! empty( $atts['meta_query'] ) ) {

			$metaQuery = new cnMeta_Query();
			$metaQuery->parse_query_vars( $atts['meta_query'] );
			$metaClause = $metaQuery->get_sql( 'entry', CN_ENTRY_TABLE, 'id' );

			$join['meta']  = $metaClause['join'];
			$where['meta'] = $metaClause['where'];
		}

		$orderBy = empty( $orderBy ) ? 'ORDER BY sort_column, last_name, first_name' : 'ORDER BY ' . implode( ', ', $orderBy );
		/*
		 * // END --> Build the ORDER BY query segment.
		 */

		/*
		 * // START --> Set up the query LIMIT and OFFSET.
		 */
		$limit  = empty( $atts['limit'] )  ? '' : $wpdb->prepare( ' LIMIT %d ', $atts['limit'] );
		$offset = empty( $atts['offset'] ) ? '' : $wpdb->prepare( ' OFFSET %d ', $atts['offset'] );
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
		 * // END --> Build the SELECT query segment.
		 */

		/**
		 * NOTES:
		 *
		 * Many queries can produce multiple results per entry ID when we really only want it once.
		 * For example an entry maybe return once for each category it is assigned or once for each
		 * address an entry has that is within the search radius.
		 *
		 * Simple adding `GROUP BY CN_ENTRY_TABLE.id seems to fix this, but may be incorrect and might fail
		 * on db/s other than MySQL such as Oracle.
		 *
		 * Very useful links that provide more details that require further study:
		 *
		 *@link http://www.psce.com/blog/2012/05/15/mysql-mistakes-do-you-use-group-by-correctly/
		 * @link http://rpbouman.blogspot.com/2007/05/debunking-group-by-myths.html
		 */

		if ( $random ) {

			$seed = cnFormatting::stripNonNumeric( cnUtility::getIP() ) . date( 'Hdm', current_time( 'timestamp', 1 ) );

			$sql = 'SELECT SQL_CALC_FOUND_ROWS *, RAND(' . $seed . ') AS random FROM ( SELECT DISTINCT ' . implode( ', ', $select ) . ' FROM ' . implode( ', ', $from ) . ' ' . implode( ' ', $join ) . ' ' . implode( ' ', $where ) . ' GROUP BY ' . CN_ENTRY_TABLE . '.id ' . implode( ' ', $having ) . ') AS T ORDER BY random' . $limit . $offset;
			// print_r($sql);

		} else {

			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . implode( ', ', $select ) . ' FROM ' . implode( ', ', $from ) . ' ' . implode( ' ', $join ) . ' ' . implode( ' ', $where ) . ' GROUP BY ' . CN_ENTRY_TABLE . '.id ' . implode( ' ', $having ) . ' ' . $orderBy . ' ' . $limit . $offset;
			// print_r($sql);
		}

		if ( ! $results = $this->results( $sql ) ) {

			$results = $wpdb->get_results( $sql );

			$this->cache( $sql, $results );

			// The most recent query to have been executed by cnRetrieve::entries
			$instance->lastQuery      = $wpdb->last_query;

			// The most recent query error to have been generated by cnRetrieve::entries
			$instance->lastQueryError = $wpdb->last_error;

			// ID generated for an AUTO_INCREMENT column by the most recent INSERT query.
			$instance->lastInsertID   = $wpdb->insert_id;

			// The number of rows returned by the last query.
			$instance->resultCount    = $wpdb->num_rows;

			// The number of rows returned by the last query without the limit clause set
			$foundRows                       = $wpdb->get_results( 'SELECT FOUND_ROWS()' );
			$instance->resultCountNoLimit    = $foundRows[0]->{'FOUND_ROWS()'};
			$this->resultCountNoLimit        = $foundRows[0]->{'FOUND_ROWS()'};

		}

		// The total number of entries based on user permissions.
		// $instance->recordCount         = self::recordCount( array( 'public_override' => $atts['allow_public_override'], 'private_override' => $atts['private_override'] ) );

		// The total number of entries based on user permissions with the status set to 'pending'
		// $instance->recordCountPending  = self::recordCount( array( 'public_override' => $atts['allow_public_override'], 'private_override' => $atts['private_override'], 'status' => array( 'pending' ) ) );

		// The total number of entries based on user permissions with the status set to 'approved'
		// $instance->recordCountApproved = self::recordCount( array( 'public_override' => $atts['allow_public_override'], 'private_override' => $atts['private_override'], 'status' => array( 'approved' ) ) );

		/*
		 * ONLY in the admin.
		 *
		 * Reset the pagination filter for the current user, remove the offset from the query and re-run the
		 * query if the offset for the query is greater than the record count with no limit set in the query.
		 *
		 */
		if ( is_admin() && $atts['offset'] > $instance->resultCountNoLimit ) {

			$instance->currentUser->resetFilterPage( 'manage' );
			unset( $atts['offset'] );
			$results = $this->entries( $atts );

		} elseif ( $atts['offset'] > $instance->resultCountNoLimit ) {

			/*
			 * This is for the front end, reset the page and offset and re-run the query if the offset
			 * is greater than the record count with no limit.
			 *
			 * @TODO  this should somehow be precessed in the parse_request action hook so the URL
			 * permalink and query vars can be properly updated.
			 */

			set_query_var( 'cn-pg', 0 );
			$atts['offset'] = 0;
			$results = $this->entries( $atts );
		}

		return $results;
	}

	/**
	 * Retrieve a single entry by either `id` or `slug`.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param  mixed $slid int|string  The entry `id` or `slug`.
	 *
	 * @return mixed bool|object The entry data.
	 */
	public function entry( $slid ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( ctype_digit( (string) $slid ) ) {

			$field = 'id';
			$value = $slid;

		} else {

			$field = 'slug';
			$value = $slid;
		}

		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . CN_ENTRY_TABLE . ' WHERE ' . $field . '=%s', $value ) );

		if ( is_null( $result ) ) {

			return FALSE;

		} else {

			return $result;
		}
	}

	public static function individuals( $atts = array() ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$out = array();
		$where[] = 'WHERE 1=1';

		$defaults = array(
			'status'                => array( 'approved' ),
			'visibility'            => array(),
			'allow_public_override' => FALSE,
			'private_override'      => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Limit the results to the "individual" entry type.
		$where[] = 'AND `entry_type` = \'individual\'';

		// Limit the characters that are queried based on if the current user can view public, private or unlisted entries.
		$where = self::setQueryVisibility( $where, $atts );

		// Limit the characters that are queried based on if the current user can view approved and/or pending entries.
		$where = self::setQueryStatus( $where, $atts );

		// Create the "Last Name, First Name".
		$select = '`id`, CONCAT( `last_name`, \', \', `first_name` ) as name';

		$results = $wpdb->get_results( 'SELECT DISTINCT ' . $select . ' FROM ' . CN_ENTRY_TABLE . ' '  . implode( ' ', $where ) . ' ORDER BY `last_name`' );

		foreach ( $results as $row ) {

			$out[ $row->id ] = $row->name;
		}

		return $out;
	}

	/**
	 * Retrieve the unique initial characters of all entries in the entry table sorted by character.
	 *
	 * @access public
	 * @since 0.7.4
	 *
	 * @return array
	 */
	public static function getCharacters( $atts = array() ) {

		/** @var wpdb $wpdb */
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
	 * @param array $where
	 * @param array $atts
	 *
	 * @access private
	 * @since 0.7.4
	 *
	 * @uses wp_parse_args()
	 * @uses is_user_logged_in()
	 * @uses current_user_can()
	 *
	 * @return array
	 */
	public static function setQueryVisibility( $where, $atts = array() ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$visibility = array();

		$defaults = array(
			'table'                 => CN_ENTRY_TABLE,
			'visibility'            => array(),
			'allow_public_override' => FALSE,
			'private_override'      => FALSE
		);

		$atts = cnSanitize::args( $atts, $defaults );

		if ( is_user_logged_in() ) {

			if ( ! isset( $atts['visibility'] ) || empty( $atts['visibility'] ) ) {

				if ( current_user_can( 'connections_view_public' ) ) $visibility[] = 'public';
				if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';
				if ( current_user_can( 'connections_view_unlisted' ) && is_admin() ) $visibility[] = 'unlisted';

			} else {

				// Convert the supplied entry statuses $atts['visibility'] to an array.
				cnFunction::parseStringList( $atts['visibility'] );

				$visibility[] = $atts['visibility'];
			}

		} else {
			//var_dump( $connections->options->getAllowPublic() ); die;

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

		$where[] = 'AND ' . $atts['table'] . '.visibility IN (\'' . implode( "', '", $visibility ) . '\')';

		return $where;
	}

	/**
	 * Set up the query to only return the entries based on status.
	 *
	 * @param array $where
	 * @param array $atts
	 *
	 * @access private
	 * @since 0.7.4
	 *
	 * @uses wp_parse_args()
	 * @uses is_user_logged_in()
	 * @uses current_user_can()
	 *
	 * @return array
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

	/**
	 * Query the CN_ENTRY_DATE_TABLE for upcoming date events. Will return an array if entry ID/s
	 * or an array of objects ordered from sooner to later over spanning the course of n-days.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $atts {
	 *     Optional.
	 *
	 *     @type string       $type                  The date event type to query. Default is 'birthday'.
	 *                                               Accepts any array keys @see cnOptions::getDateOptions().
	 *     @type int          $days                  The number of days to look forward. Default is 30.
	 *     @type bool         $today                 Whether of not to include events occurring today. Default is FALSE.
	 *     @type array|string $visibility
	 *     @type bool         $allow_public_override Default is FALSE.
	 *     @type bool         $private_override      Default is FALSE.
	 *     @type string       $return                What to return. Default is 'data'. Accepts data|id.
	 * }
	 *
	 * @return array An array of entry ID/s or an array of objects.
	 */
	public function upcoming( $atts = array() ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$instance = Connections_Directory();

		$permitted = array_keys( $instance->options->getDateOptions() );
		$where     = array();
		$results   = array();

		$defaults = array(
			'type'                  => 'birthday',
			'days'                  => 30,
			'today'                 => TRUE,
			'visibility'            => array(),
			'allow_public_override' => FALSE,
			'private_override'      => FALSE,
			'return'                => 'data', // Valid options are `data` which are the results returned from self::entries() or `id` which are the entry ID/s.
		);

		$atts = cnSanitize::args( $atts, $defaults );

		// Ensure that the upcoming type is one of the supported types. If not, reset to the default.
		$atts['type'] = in_array( $atts['type'], $permitted ) ? $atts['type'] : 'birthday';

		$where[] = $wpdb->prepare( 'AND `type` = %s', $atts['type'] );

		// Get timestamp.
		$time = current_time( 'timestamp' );

		// Get today's date, formatted for use in the query.
		$date = gmdate( 'Y-m-d', current_time( 'timestamp' ) );

		// Whether or not to include the event occurring today or not.
		$includeToday = $atts['today'] ? '<=' : '<';

		$sql = $wpdb->prepare(
			'SELECT entry_id AS id, date FROM ' . CN_ENTRY_DATE_TABLE . ' WHERE '
			. '  ( YEAR( DATE_ADD( %s, INTERVAL %d DAY ) )'
			. ' - YEAR( date ) )'
			. ' - ( MID( DATE_ADD( %s, INTERVAL %d DAY ), 6, 5 )'
			. ' < MID( date, 6, 5 ) )'
			. ' > ( YEAR( %s )'
			. ' - YEAR( date ) )'
			. ' - ( MID( %s, 6, 5 )'
			. ' ' . $includeToday . ' MID( date, 6, 5 ) )'
			. ' ' . implode( ' ', $where ),
			$date,
			absint( $atts['days'] ),
			$date,
			absint( $atts['days'] ),
			$date,
			$date
			);
		// print_r($sql);

		$upcoming = $wpdb->get_results( $sql );
		// var_dump($upcoming);

		// The date is stored in YYYY-MM-DD format, we must convert to UNIX time.
		// We need to use PHP to do the conversion because MySQL UNIX_TIMESTAMP() will return 0 for pre 1970-01-01 dates.
		if ( ! empty( $upcoming ) ) {

			foreach ( $upcoming as $row ) {

				// Append the time/timezone offset so strtotime() does not shift it to the local server timezone.
				$row->date = strtotime( $row->date . ' 00:00:00+0000' );
			}
		}

		// We need to query the main table for anniversaries or birthdays so we can capture any that may have been
		// added before the implementation of the CN_ENTRY_DATE_TABLE table.
		if ( $atts['type'] == 'anniversary' || $atts['type'] == 'birthday' ) {

			$exclude = array();

			// Reset the WHERE clause.
			$where   = array();

			// Only select the entries with a date.
			$where[] = sprintf( 'AND ( `%s` != \'\' )', $atts['type'] );

			// Exclude any entries that already exist in the previous query results.
			foreach ( $upcoming as $row ) {

				$exclude[] = $row->id;
			}

			if ( ! empty( $exclude ) ) $where[] = 'AND `id` NOT IN (\'' . implode( '\', \'', $exclude ) . '\')';

			/*
			 * The FROM_UNIXTIME function will return the date offset to the local system timezone.
			 * The dates were not saved in GMT time and since FROM_UNIXTIME is adjusting for the local system timezone
			 * it could cause dates to shift days. The solution is to take the timezone shifted date from FROM_UNIXTIME
			 * and convert it using CONVERT_TZ from the local system timezone to GMT.
			 */
			$sql = $wpdb->prepare(
				'SELECT `id`, ' . $atts['type'] . ' AS `date` FROM ' . CN_ENTRY_TABLE . ' WHERE '
				. '  ( YEAR( DATE_ADD( %s, INTERVAL %d DAY ) )'
				. ' - YEAR( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ) ) )'
				. ' - ( MID( DATE_ADD( %s, INTERVAL %d DAY ), 5, 6 )'
				. ' < MID( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ), 5, 6 ) )'
				. ' > ( YEAR( %s )'
				. ' - YEAR( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ) ) )'
				. ' - ( MID( %s, 5, 6 )'
				. ' ' . $includeToday . ' MID( CONVERT_TZ( FROM_UNIXTIME( `' . $atts['type'] . '` ), @@session.time_zone, \'+00:00\' ), 5, 6 ) )'
				. ' ' . implode( ' ', $where ),
				$date,
				absint( $atts['days'] ),
				$date,
				absint( $atts['days'] ),
				$date,
				$date
				);
			// print_r($sql);

			$legacy = $wpdb->get_results( $sql );
			// var_dump($legacy);

			if ( ! empty( $legacy ) ) $upcoming = array_merge( $upcoming, $legacy );
		}

		if ( ! empty( $upcoming ) ) {

			$ids = array();
			$ts  = array();

			/*
			 * The SQL returns an array sorted by the birthday and/or anniversary date. However the year end wrap needs to be accounted for.
			 * Otherwise earlier months of the year show before the later months in the year. Example Jan before Dec. The desired output is to show
			 * Dec then Jan dates.  This function checks to see if the month is a month earlier than the current month. If it is the year is changed to the following year rather than the current.
			 * After a new list is built, it is resorted based on the date.
			 */
			foreach ( $upcoming as $row ) {

				$ids[] = $row->id;

				if ( gmmktime( 23, 59, 59, gmdate( 'm', $row->date ), gmdate( 'd', $row->date ), gmdate( 'Y', $time ) ) < $time ) {

					$ts[] = gmmktime( 0, 0, 0, gmdate( 'm', $row->date ), gmdate( 'd', $row->date ), gmdate( 'Y', $time ) + 1 );

				} else {

					$ts[] = gmmktime( 0, 0, 0, gmdate( 'm', $row->date ), gmdate( 'd', $row->date ), gmdate( 'Y', $time ) );
				}

			}

			array_multisort( $ts, SORT_ASC, SORT_NUMERIC, $ids );
			// var_dump( $ids );

			switch ( $atts['return'] ) {

				case 'id':

					return $ids;

					break;

				default:

					return self::entries(
						array(
							'lock'     => TRUE,
							'id'       => $ids,
							'order_by' => 'id|SPECIFIED',
						)
					);

					break;
			}

		}

		return $results;
	}

	/**
	 * Retrieve the entry categories.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param int $id
	 *
	 * @return mixed array|WP_Error An array of categories associated to an entry.
	 */
	public function entryCategories( $id ) {

		return self::entryTerms( $id, 'category' );
	}

	/**
	 * Retrieve the entry terms by taxonomy.
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @param int    $id
	 * @param string $taxonomy
	 * @param array  $atts     Optional. An array of arguments. @see cnTerm::getRelationships() for accepted arguments.
	 *
	 * @return mixed array|WP_Error An array of terms by taxonomy associated to an entry.
	 */
	public static function entryTerms( $id, $taxonomy, $atts = array() ) {

		/** @todo Check that entry exists */
		//if ( ! $id = get_entry( $id ) ) {
		//	return false;
		//}

		$terms = cnTerm::getRelationshipsCache( $id, $taxonomy );

		if ( FALSE === $terms ) {

			$terms = cnTerm::getRelationships( $id, $taxonomy, $atts );

			wp_cache_add( $id, $terms, "cn_{$taxonomy}_relationships" );

		} else {

			/**
			 * Filter the list of terms attached to the given entry.
			 *
			 * @since 8.2
			 *
			 * @param array|WP_Error $terms    List of attached terms, or WP_Error on failure.
			 * @param int            $id       Post ID.
			 * @param string         $taxonomy Name of the taxonomy.
			 * @param array          $atts     An array of arguments for retrieving terms for the given object.
			 */
			$terms = apply_filters( 'cn_get_object_terms', $terms, $id, $taxonomy, $atts );
		}

		return $terms;
	}

	/**
	 * Returns an indexed array of objects the addresses per the defined options.
	 *
	 * @param $atts {
	 *     @type string       $fields    The fields to return.
	 *                                   Default: all
	 *                                   Accepts: all, ids, locality, regions, postal-code, country
	 *     @type int          $id        The entry ID in which to retrieve the addresses for.
	 *     @type bool         $preferred Whether or not to return only the preferred address.
	 *                                   Default: false
	 *     @type array|string $type      The address types to return.
	 *                                   Default: array() which will return all registered address types.
	 *                                   Accepts: home, work, school, other and any other registered types.
	 *     @type array|string $city      Return address in the defined cities.
	 *     @type array|string $state     Return address in the defined states.
	 *     @type array|string $country   Return address in the defined countries.
	 *     @type array        $coordinates {
	 *         Return the addresses at the specific coordinates.
	 *         @type float $latitude
	 *         @type float $longitude
	 *     }
	 * }
	 *
	 * @return array
	 */
	public function addresses( $atts = array() ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$where = array( 'WHERE 1=1' );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'fields'      => 'all',
			'id'          => NULL,
			'preferred'   => FALSE,
			'type'        => array(),
			'city'        => array(),
			'state'       => array(),
			'zipcode'     => array(),
			'country'     => array(),
			'coordinates' => array(),
			'limit'       => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		/**
		 * @var int          $id
		 * @var bool         $preferred
		 * @var array|string $type
		 * @var array|string $city
		 * @var array|string $state
		 * @var array|string $zipcode
		 * @var array|string $country
		 * @var array        $coordinates
		 * @var null|int     $limit
		 */
		extract( $atts );

		/*
		 * Convert these to values to an array if they were supplied as a comma delimited string
		 */
		/** @var array $type */
		cnFunction::parseStringList( $type );

		/** @var array $city */
		cnFunction::parseStringList( $city );

		/** @var array $state */
		cnFunction::parseStringList( $state );

		/** @var array $zipcode */
		cnFunction::parseStringList( $zipcode );

		/** @var array $country */
		cnFunction::parseStringList( $country );

		switch ( $atts['fields'] ) {

			case 'ids':
				$select = array( 'a.id', 'a.entry_id' );
				break;

			case 'locality':
				$select = array( 'a.city' );
				break;

			case 'region':
				$select  = array( 'a.state' );
				break;

			case 'postal-code':
				$select = array( 'a.zipcode' );
				break;

			case 'country':
				$select = array( 'a.country' );
				break;

			default:
				$select = array( 'a.*' );
		}

		if ( ! empty( $id ) ) {

			$where[] = $wpdb->prepare( 'AND `entry_id` = %d', $id );
		}

		if ( ! empty( $preferred ) ) {

			$where[] = $wpdb->prepare( 'AND `preferred` = %d', (bool) $preferred );
		}

		if ( ! empty( $type ) ) {

			$where[] = $wpdb->prepare( 'AND `type` IN (' . cnFormatting::prepareINPlaceholders( $type ) . ')', $type );
		}

		if ( ! empty( $city ) ) {

			$where[] = $wpdb->prepare( 'AND `city` IN (' . cnFormatting::prepareINPlaceholders( $city ) . ')', $city );
		}

		if ( ! empty( $state ) ) {

			$where[] = $wpdb->prepare( 'AND `state` IN (' . cnFormatting::prepareINPlaceholders( $state ) . ')', $state );
		}

		if ( ! empty( $zipcode ) ) {

			$where[] = $wpdb->prepare( 'AND `zipcode` IN (' . cnFormatting::prepareINPlaceholders( $zipcode ) . ')', $zipcode );
		}

		if ( ! empty( $country ) ) {

			$where[] = $wpdb->prepare( 'AND `country` IN (' . cnFormatting::prepareINPlaceholders( $country ) . ')', $country );
		}

		if ( ! empty( $coordinates ) ) {

			if ( ! empty( $coordinates['latitude'] ) && ! empty( $coordinates['longitude'] ) ) {

				$where[] = $wpdb->prepare( 'AND `latitude` = %f', $coordinates['latitude'] );
				$where[] = $wpdb->prepare( 'AND `longitude` = %f', $coordinates['longitude'] );
			}
		}

		// Limit the characters that are queried based on if the current user can view public, private or unlisted entries.
		$where = self::setQueryVisibility( $where, array( 'table' => 'a' ) );

		$limit = is_null( $atts['limit'] ) ? '' : sprintf( ' LIMIT %d', $atts['limit'] );

		$sql = sprintf(
			'SELECT %1$s FROM %2$s AS a %3$s ORDER BY `order`%4$s',
			implode( ', ', $select ),
			CN_ENTRY_ADDRESS_TABLE,
			implode( ' ', $where ),
			$limit
		);

		$results = $wpdb->get_results( $sql );

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

		/** @var wpdb $wpdb */
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
		$defaultAttr['limit'] = NULL;

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

		$limit = is_null( $atts['limit'] ) ? '' : sprintf( ' LIMIT %d', $atts['limit'] );

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_PHONE_TABLE . '.*

					FROM ' . CN_ENTRY_PHONE_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`' . $limit;

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_PHONE_TABLE . '.entry_id

					FROM ' . CN_ENTRY_PHONE_TABLE . ' ' . ' ' . implode( ' ', $where ) . $limit;

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

		/** @var wpdb $wpdb */
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
		$defaultAttr['limit'] = NULL;

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

		$limit = is_null( $atts['limit'] ) ? '' : sprintf( ' LIMIT %d', $atts['limit'] );

		if ( $returnData ) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_EMAIL_TABLE . '.*

					FROM ' . CN_ENTRY_EMAIL_TABLE . ' ' . ' ' .

				implode( ' ', $where ) . ' ' .

				'ORDER BY `order`' . $limit;

			//print_r($sql);

			$results = $wpdb->get_results( $sql );
		}
		else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ' . CN_ENTRY_EMAIL_TABLE . '.entry_id

					FROM ' . CN_ENTRY_EMAIL_TABLE . ' ' . ' ' . implode( ' ', $where ) . $limit;

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

		/** @var wpdb $wpdb */
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

		/** @var wpdb $wpdb */
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

		/** @var wpdb $wpdb */
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

		/** @var wpdb $wpdb */
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
	 * @since  0.7.2.0
	 * @param  array   $atts [optional]
	 *
	 * @return array
	 */
	public function search( $atts = array() ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$results    = array();
		$scored     = array();
		$fields     = $instance->options->getSearchFields();

		$fields     = apply_filters( 'cn_search_fields', $fields );

		// If no search search fields are set, return an empty array.
		if ( empty( $fields ) ) return array();

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['terms'] = array();

		if ( in_array( 'family_name', $fields ) ) $defaults['fields']['entry'][]        = 'family_name';
		if ( in_array( 'first_name', $fields ) ) $defaults['fields']['entry'][]         = 'first_name';
		if ( in_array( 'middle_name', $fields ) ) $defaults['fields']['entry'][]        = 'middle_name';
		if ( in_array( 'last_name', $fields ) ) $defaults['fields']['entry'][]          = 'last_name';
		if ( in_array( 'title', $fields ) ) $defaults['fields']['entry'][]              = 'title';
		if ( in_array( 'organization', $fields ) ) $defaults['fields']['entry'][]       = 'organization';
		if ( in_array( 'department', $fields ) ) $defaults['fields']['entry'][]         = 'department';
		if ( in_array( 'contact_first_name', $fields ) ) $defaults['fields']['entry'][] = 'contact_first_name';
		if ( in_array( 'contact_last_name', $fields ) ) $defaults['fields']['entry'][]  = 'contact_last_name';
		if ( in_array( 'bio', $fields ) ) $defaults['fields']['entry'][]                = 'bio';
		if ( in_array( 'notes', $fields ) ) $defaults['fields']['entry'][]              = 'notes';

		if ( in_array( 'address_line_1', $fields ) ) $defaults['fields']['address'][]   = 'line_1';
		if ( in_array( 'address_line_2', $fields ) ) $defaults['fields']['address'][]   = 'line_2';
		if ( in_array( 'address_line_3', $fields ) ) $defaults['fields']['address'][]   = 'line_3';
		if ( in_array( 'address_city', $fields ) ) $defaults['fields']['address'][]     = 'city';
		if ( in_array( 'address_state', $fields ) ) $defaults['fields']['address'][]    = 'state';
		if ( in_array( 'address_zipcode', $fields ) ) $defaults['fields']['address'][]  = 'zipcode';
		if ( in_array( 'address_country', $fields ) ) $defaults['fields']['address'][]  = 'country';

		if ( in_array( 'phone_number', $fields ) ) $defaults['fields']['phone'][]       = 'number';

		$atts = wp_parse_args( $atts, apply_filters( 'cn_search_atts', $defaults ) );

		// @todo Validate each fiels array to ensure only permitted fields will be used.
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		// If no search terms were entered, return an empty array.
		if ( empty( $atts['terms'] ) ) return array();

		// If value is a string, stripe the white space and covert to an array.
		if ( ! is_array( $atts['terms'] ) ) $atts['terms'] = explode( ' ', trim( $atts['terms'] ) );

		// Trim any white space from around the terms in the array.
		array_walk( $atts['terms'] , 'trim' );

		$atts['terms'] = apply_filters( 'cn_search_terms', $atts['terms'] );

		// Remove any single characters and stop words from terms.
		$atts['terms'] = $this->parse_search_terms( $atts['terms'] );

		// If no search terms are left after removing stop words, return an empty array.
		if ( empty( $atts['terms'] ) ) return array();

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
		if ( cnSettingsAPI::get( 'connections', 'search', 'fulltext_enabled' ) ) {

			$terms      = array();
			$shortwords = array();

			/*
			 * Remove any shortwords from the FULLTEXT query since the db will drop them anyway.
			 * Add the shortwords to a separate array to be used to do a LIKE query.
			 */
			foreach ( $atts['terms'] as $key => $term ) {

				if ( strlen( $term ) >= 2 && strlen( $term ) <= 3 ) {

					unset( $atts['terms'][ $key ] );

					$shortwords[] = $term;
				}
			}

			if ( ! empty( $atts['terms'] ) ) {

				// Make each term required, functional AND query.
				$terms = apply_filters( 'cn_search_fulltext_terms', '+' . implode( ' +', $atts['terms'] ) );
			}

			/*
			 * Only search the primary records if at least one fields is selected to be searched.
			 */
			if ( ! empty( $atts['fields']['entry'] ) ) {

				$select = array();
				$from   = array();
				$where  = array();
				$like   = array();

				$select[] = 'SELECT ' . CN_ENTRY_TABLE . '.id';

				/*
				 * Set up the SELECT to return the results scored by relevance.
				 */
				if ( ! empty( $terms ) ) {

					$select[] = $wpdb->prepare(
						'MATCH (' . implode( ', ', $atts['fields']['entry'] ) . ') AGAINST (%s) AS score',
						$terms
						);
				}

				$from[] = CN_ENTRY_TABLE;

				/*
				 * If there are long word terms, perform a FULLTEXT query.
				 */
				if ( ! empty( $terms ) ) {

					$where[] = $wpdb->prepare(
						'MATCH (' . implode( ', ', $atts['fields']['entry'] ) . ') AGAINST (%s IN BOOLEAN MODE)',
						$terms
						);
				}

				/*
				 * If there are no long words and there are short words, perform a LIKE query for the short words.
				 */
				if ( empty( $terms ) && ! empty( $shortwords ) ) {

					foreach ( $shortwords as $word ) {

						$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $atts['fields']['entry'] ) . ' LIKE %s ', array_fill( 0, count( $atts['fields']['entry'] ), $wpdb->esc_like( $word ) . '%' ) );
					}

					$where[] = '( ' . implode( ') OR (' , $like ) . ')';

				}

				/*
				 * Return the query results ordered by the relevance score.
				 */
				$orderBy = empty( $terms ) ? '' : ' ORDER BY score';

				$sql     = implode( ', ', $select ) . ' FROM ' . implode( ',', $from ) . ' WHERE ' . implode( ' AND ', $where ) . $orderBy;

				$scored  = $wpdb->get_results( $sql, ARRAY_A );
			}

			/*
			 * Only search the address records if at least one fields is selected to be searched.
			 */
			if ( ! empty( $atts['fields']['address'] ) ) {

				$select = array();
				$from   = array();
				$where  = array();
				$like   = array();

				$select[] = 'SELECT ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id';

				/*
				 * Set up the SELECT to return the results scored by relevance.
				 */
				if ( ! empty( $terms ) ) {

					$select[] = $wpdb->prepare(
						'MATCH (' . implode( ', ', $atts['fields']['address'] ) . ') AGAINST (%s) AS score',
						$terms
						);
				}

				$from[] = CN_ENTRY_ADDRESS_TABLE;

				/*
				 * If there are long word terms, perform a FULLTEXT query.
				 */
				if ( ! empty( $terms ) ) {

					$where[] = $wpdb->prepare(
						'MATCH (' . implode( ', ', $atts['fields']['address'] ) . ') AGAINST (%s IN BOOLEAN MODE)',
						$terms
						);
				}

				/*
				 * If there are no long words and there are short words, perform a LIKE query for the short words.
				 */
				if ( empty( $terms ) && ! empty( $shortwords ) ) {

					foreach ( $shortwords as $word ) {

						$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $atts['fields']['address'] ) . ' LIKE %s ', array_fill( 0, count( $atts['fields']['address'] ), $wpdb->esc_like( $word ) . '%' ) );
					}

					$where[] = '( ' . implode( ') OR (' , $like ) . ')';

				}

				/*
				 * Return the query results ordered by the relevance score.
				 */
				$orderBy = empty( $terms ) ? '' : ' ORDER BY score';

				$sql = implode( ', ', $select ) . ' FROM ' . implode( ',', $from ) . ' WHERE ' . implode( ' AND ', $where ) . $orderBy;

				$ids = $wpdb->get_results( $sql, ARRAY_A );

				/*
				 * If any results are returned merge them in to the $scored results.
				 */
				if ( ! empty( $ids ) ) $scored = array_merge( $scored, $ids );
			}

			/*
			 * Only search the phone records if thefield is selected to be search.
			 */
			if ( ! empty( $atts['fields']['phone'] ) ) {

				$select = array();
				$from   = array();
				$where  = array();
				$like   = array();

				$select[] = 'SELECT ' . CN_ENTRY_PHONE_TABLE . '.entry_id';

				/*
				 * Set up the SELECT to return the results scored by relevance.
				 */
				if ( ! empty( $terms ) ) {

					$select[] = $wpdb->prepare(
						'MATCH (' . implode( ', ', $atts['fields']['phone'] ) . ') AGAINST (%s) AS score',
						$terms
						);
				}

				$from[] = CN_ENTRY_PHONE_TABLE;

				/*
				 * If there are long word terms, perform a FULLTEXT query.
				 */
				if ( ! empty( $terms ) ) {

					$where[] = $wpdb->prepare(
						'MATCH (' . implode( ', ', $atts['fields']['phone'] ) . ') AGAINST (%s IN BOOLEAN MODE)',
						$terms
						);
				}

				/*
				 * If there are no long words and there are short words, perform a LIKE query for the short words.
				 */
				if ( empty( $terms ) && ! empty( $shortwords ) ) {

					foreach ( $shortwords as $word ) {

						$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $atts['fields']['phone'] ) . ' LIKE %s ', array_fill( 0, count( $atts['fields']['phone'] ), $wpdb->esc_like( $word ) . '%' ) );
					}

					$where[] = '( ' . implode( ') OR (' , $like ) . ')';

				}

				/*
				 * Return the query results ordered by the relevance score.
				 */
				$orderBy = empty( $terms ) ? '' : ' ORDER BY score';

				$sql = implode( ', ', $select ) . ' FROM ' . implode( ',', $from ) . ' WHERE ' . implode( ' AND ', $where ) . $orderBy;

				$ids = $wpdb->get_results( $sql, ARRAY_A );

				/*
				 * If any results are returned merge them in to the $scored results.
				 */
				if ( ! empty( $ids ) ) $scored = array_merge( $scored, $ids );
			}

			$scored = apply_filters( 'cn_search_scored_results', $scored, $terms );

			/*
			 * The query results are stored in the $scored array ordered by relevance.
			 * Only the entry ID/s are needed to be returned. Setup the $results array
			 * with only the entry ID/s in the same order as returned by the relevance score.
			 */
			foreach ( $scored as $entry ) {

				$results[] = isset( $entry['id'] ) ? $entry['id'] : $entry['entry_id'];
			}

		}

		/*
		 * If no results are found, perhaps to the way MySQL performs FULLTEXT queries, FULLTEXT search being disabled
		 * or the DB not supporting FULLTEXT, run a LIKE query.
		 *
		 * Perform the search on each table individually because joining the tables doesn't scale when
		 * there are a large number of entries.
		 */
		if (
			(
				( cnSettingsAPI::get( 'connections', 'search', 'keyword_enabled' ) && empty( $results ) ) ||
				( cnSettingsAPI::get( 'connections', 'search', 'fulltext_enabled' ) && empty( $results ) )
			) &&
			! empty( $atts['terms'] ) ) {

			/*
			 * Only search the primary records if at least one fields is selected to be searched.
			 */
			if ( ! empty( $atts['fields']['entry'] ) ) {

				$like = array(); // Reset the like array.

				foreach ( $atts['terms'] as $term ) {
					/*
					 * Attempt to secure the query using $wpdb->prepare() and like_escape()
					 *
					 * Since $wpdb->prepare() required var for each directive in the query string we'll use array_fill
					 * where the count based on the number of columns that will be searched.
					 */
					$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $atts['fields']['entry'] ) . ' LIKE %s ' , array_fill( 0 , count( $atts['fields']['entry'] ) , '%' . $wpdb->esc_like( $term ) . '%' ) );
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
			if ( ! empty( $atts['fields']['address'] ) ) {

				$like = array(); // Reset the like array.

				foreach ( $atts['terms'] as $term ) {
					/*
					 * Attempt to secure the query using $wpdb->prepare() and like_escape()
					 *
					 * Since $wpdb->prepare() required var for each directive in the query string we'll use array_fill
					 * where the count based on the number of columns that will be searched.
					 */
					$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $atts['fields']['address'] ) . ' LIKE %s ' , array_fill( 0 , count( $atts['fields']['address'] ) , '%' . $wpdb->esc_like( $term ) . '%' ) );
				}

				$sql =  'SELECT ' . CN_ENTRY_ADDRESS_TABLE . '.entry_id
									FROM ' . CN_ENTRY_ADDRESS_TABLE . '
									WHERE (' . implode( ') OR (' , $like ) . ')';
				//print_r($sql);

				$results = array_merge( $results, $wpdb->get_col( $sql ) );
				//print_r($results);
			}

			/*
			 * Only search the phone records if the field is selected to be search.
			 */
			if ( ! empty( $atts['fields']['phone'] ) ) {

				$like = array(); // Reset the like array.

				foreach ( $atts['terms'] as $term ) {
					/*
					 * Attempt to secure the query using $wpdb->prepare() and like_escape()
					 *
					 * Since $wpdb->prepare() required var for each directive in the query string we'll use array_fill
					 * where the count based on the number of columns that will be searched.
					 */
					$like[] = $wpdb->prepare( implode( ' LIKE %s OR ' , $atts['fields']['phone'] ) . ' LIKE %s ' , array_fill( 0 , count( $atts['fields']['phone'] ) , '%' . $wpdb->esc_like( $term ) . '%' ) );
				}

				$sql =  'SELECT ' . CN_ENTRY_PHONE_TABLE . '.entry_id
									FROM ' . CN_ENTRY_PHONE_TABLE . '
									WHERE (' . implode( ') OR (' , $like ) . ')';
				//print_r($sql);

				$results = array_merge( $results, $wpdb->get_col( $sql ) );
				//print_r($results);
			}

		}

		return apply_filters( 'cn_search_results', $results, $atts );
	}

	/**
	 * Ripped from WP core. Unfortunately it can not be used
	 * directly because it is a protected method in the WP_Query class.
	 *
	 * Check if the terms are suitable for searching.
	 *
	 * Uses an array of stopwords (terms) that are excluded from the separate
	 * term matching when searching for posts. The list of English stopwords is
	 * the approximate search engines list, and is translatable.
	 *
	 * @since 8.1
	 *
	 * @param array $terms Terms to check.
	 *
	 * @return array Terms that are not stopwords.
	 */
	protected function parse_search_terms( $terms ) {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked = array();

		$stopwords = $this->get_search_stopwords();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match
			if ( preg_match( '/^".+"$/', $term ) )
				$term = trim( $term, "\"'" );
			else
				$term = trim( $term, "\"' " );

			// Avoid single A-Z.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z]$/i', $term ) ) )
				continue;

			if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) )
				continue;

			$checked[] = $term;
		}

		return $checked;
	}

	/**
	 * Ripped from WP core. Unfortunately it can not be used
	 * directly because it is a protected method in the WP_Query class.
	 *
	 * Retrieve stopwords used when parsing search terms.
	 *
	 * @since 8.1
	 *
	 * @return array Stopwords.
	 */
	protected function get_search_stopwords() {
		if ( isset( $this->stopwords ) )
			return $this->stopwords;

		/* translators: This is a comma-separated list of very common words that should be excluded from a search,
		 * like a, an, and the. These are usually called "stopwords". You should not simply translate these individual
		 * words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		 */
		$words = explode( ',', _x( 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
			'Comma-separated list of search stopwords in your language', 'connections' ) );

		$stopwords = array();
		foreach( $words as $word ) {
			$word = trim( $word, "\r\n\t " );
			if ( $word )
				$stopwords[] = $word;
		}

		/**
		 * Filter stopwords used when parsing search terms.
		 *
		 * @since 3.7.0
		 *
		 * @param array $stopwords Stopwords.
		 */
		$this->stopwords = apply_filters( 'wp_search_stopwords', $stopwords );
		return $this->stopwords;
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
	 * with the id field. The SPECIFIED flag must be used in conjunction
	 * with $suppliedIDs which can be either a comma delimited sting or
	 * an indexed array of entry IDs. If this is set, other sort fields/flags
	 * are ignored.
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated since unknown
	 *
	 * @param array   $entries A reference to an array of object $entries
	 * @param string  $orderBy
	 * @param mixed   array|string|NULL [optional]
	 *
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

		/** @var $connections connectionsLoad */
		global $connections;

		return $connections->term->getTerms( 'category' );
	}

	/**
	 * Returns category by ID.
	 *
	 * @param integer $id
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

	/**
	 * Cache a query results so results can be used again without an db hit,
	 * NOTE: This cache is good for each page load; not persistent.
	 *
	 * @access private
	 * @since  0.8
	 * @param  string $sql     The query statement.
	 * @param  array  $results The results of the query statement.
	 * @return void
	 */
	public function cache( $sql, $results ) {

		// Create a hash so the correct results can be returned.
		$hash = md5( json_encode( $sql ) );

		$this->results[ $hash ] = $results;
	}

	/**
	 * Return the results from a previously run query.
	 *
	 * @access private
	 * @since  0.8
	 * @param  string $sql The query statement,
	 * @return mixed       array | bool The results if the query is in the cache. False if it is not.
	 */
	public function results( $sql ) {

		// Create a hash so the correct results can be returned.
		$hash = md5( json_encode( $sql ) );

		// First check to see if the results have been queried already.
		// If not query the results, store and then return then.
		if ( array_key_exists( $hash, $this->results ) ) {

			return $this->results[ $hash ];

		} else {

			return FALSE;
		}

	}

}
