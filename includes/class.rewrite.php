<?php

/**
 * Methods to be used in actions and filters to register query vars,
 * rewrite rules and canonical redirects.
 *
 * @package     Connections
 * @subpackage  Rewrite Rules and Registered Query Vars
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.3.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnRewrite {

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public static function hooks() {

		// Remove the canonical redirect -- for testing.
		// remove_filter('template_redirect', 'redirect_canonical');
		add_filter( 'redirect_canonical', array( __CLASS__ , 'frontPageCanonicalRedirect') , 10, 2 );

		add_filter( 'query_vars', array( __CLASS__ , 'queryVars' ) );
		add_filter( 'root_rewrite_rules', array( __CLASS__ , 'addRootRewriteRules' ) );
		add_filter( 'page_rewrite_rules', array( __CLASS__ , 'addPageRewriteRules' ) );
		// add_action( 'template_redirect', array( __CLASS__ , 'canonicalRedirectAction' ) );
		// add_filter( 'redirect_canonical', array( __CLASS__ , 'canonicalRedirectFilter') , 10, 2 );

		$cptOptions = get_option( 'connections_cpt' );

		if ( isset( $cptOptions['supported'] ) && ! empty( $cptOptions['supported'] ) && is_array( $cptOptions['supported'] ) ) {

			foreach ( $cptOptions['supported'] as $type ) {

				add_filter( $type . '_rewrite_rules', array( __CLASS__, 'addCPTRewriteRules' ) );
			}
		}

		add_action( 'init', array( __CLASS__, 'addEndPoints') );
		add_filter( 'request', array( __CLASS__, 'setImageEndpointQueryVar' ) );
	}

	/**
	 * Register the valid query variables.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 *
	 * @param array $var Provide information about a function parameter.
	 *
	 * @return array
	 */
	public static function queryVars( $var ) {

		$var[] = 'cn-cat';   // category id
		$var[] = 'cn-cat-slug';  // category slug
		$var[] = 'cn-cat-in';  // category in
		$var[] = 'cn-country';  // country
		$var[] = 'cn-region';  // state
		$var[] = 'cn-locality';  // city
		$var[] = 'cn-postal-code'; // zipcode
		$var[] = 'cn-county';
		$var[] = 'cn-district';
		$var[] = 'cn-organization'; // organization
		$var[] = 'cn-department'; // department
		$var[] = 'cn-char'; // initial character
		$var[] = 'cn-s';   // search term
		$var[] = 'cn-pg';   // page
		$var[] = 'cn-entry-slug'; // entry slug
		$var[] = 'cn-token';  // security token; WP nonce
		$var[] = 'cn-id';   // entry ID
		$var[] = 'cn-vc';   // download vCard, BOOL 1 or 0 [used only in links from the admin for unlisted entry's vCard]
		$var[] = 'cn-process';  // various processes [ vcard || add || edit || moderate || delete ]
		$var[] = 'cn-view';   // current view [ landing || list || detail ]

		// Query vars to support showing entries within a specified radius.
		$var[] = 'cn-near-coord'; // latitude and longitude
		$var[] = 'cn-near-addr'; // address url encoded
		$var[] = 'cn-radius';  // distance
		$var[] = 'cn-unit';   // unit of distance

		// Query vars for cnImage.
		$var[] = 'src';
		$var[] = 'w';
		$var[] = 'h';
		$var[] = 'q';
		$var[] = 'a';
		$var[] = 'zc';
		$var[] = 'f';
		$var[] = 's';
		$var[] = 'o';
		$var[] = 'cc';
		$var[] = 'ct';

		// var_dump( $var ); exit();
		return $var;
	}

	/**
	 * Add the endpoint for images to the root rewrite rules.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 * @uses   add_rewrite_endpoint()
	 * @return void
	 */
	public static function addEndPoints() {

		add_rewrite_endpoint( CN_IMAGE_ENDPOINT, EP_ROOT );
	}

	/**
	 * Set the query var for the CN_IMAGE_ENDPOINT to TRUE.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 * @param  array $var An associative array of the parsed query vars where the key is the query var key.
	 * @return array $var
	 */
	public static function setImageEndpointQueryVar( $var ) {

		if ( ! empty( $var[ CN_IMAGE_ENDPOINT ] ) ) {

			return $var;
		}

		// When a static page was set as front page, the WordPress endpoint API
		// does some strange things. Let's fix that.
		// @url http://wordpress.stackexchange.com/a/91195
		if ( isset( $var[ CN_IMAGE_ENDPOINT ] ) ||
			( isset( $var['pagename'] ) && CN_IMAGE_ENDPOINT === $var['pagename'] ) ||
			( isset( $var['name'] ) && CN_IMAGE_ENDPOINT === $var['name'] )
			)
		{
			// In some cases WP misinterprets the request as a page request and returns a 404.
			$var['page'] = $var['pagename'] = $var['name'] = FALSE;
			$var[ CN_IMAGE_ENDPOINT ] = TRUE;
		}

		return $var;
	}

	/**
	 * Add the root rewrite rules.
	 *
	 * NOTE: Using a filter so I can add the rules right after the default root rules.
	 * This *should* prevent any rule conflicts.
	 *
	 * @access private
	 * @since  0.7.3.2
	 *
	 * @param array   $root_rewrite
	 *
	 * @return array
	 */
	public static function addRootRewriteRules( $root_rewrite ) {

		// If a page has not been set to be the front, exit, because these rules would not apply.
		if ( ! get_option('page_on_front') ) return $root_rewrite;

		$rule = array();

		/** @var string $pageID Get the page id of the user selected front page. */
		$pageID = get_option('page_on_front');

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		$character    = isset( $base['character_base'] ) && $base['character_base'] ? $base['character_base'] : 'char';
		$category     = isset( $base['category_base'] ) && $base['category_base'] ? $base['category_base'] : 'cat';
		$country      = isset( $base['country_base'] ) && $base['country_base'] ? $base['country_base'] : 'country';
		$region       = isset( $base['region_base'] ) && $base['region_base'] ? $base['region_base'] : 'region';
		$locality     = isset( $base['locality_base'] ) && $base['locality_base'] ? $base['locality_base'] : 'locality';
		$postal       = isset( $base['postal_code_base'] ) && $base['postal_code_base'] ? $base['postal_code_base'] : 'postal_code';
		$district     = isset( $base['district_base'] ) && $base['district_base'] ? $base['district_base'] : 'district';
		$county       = isset( $base['county_base'] ) && $base['county_base'] ? $base['county_base'] : 'county';
		$organization = isset( $base['organization_base'] ) && $base['organization_base'] ? $base['organization_base'] : 'organization';
		$department   = isset( $base['department_base'] ) && $base['department_base'] ? $base['department_base'] : 'department';
		$name         = isset( $base['name_base'] ) && $base['name_base'] ? $base['name_base'] : 'name';

		// Submit new entry page.
		$rule['submit/?$']
			= 'index.php?page_id=' . $pageID . '&cn-view=submit';

		// Landing page.
		$rule['landing/?$']
			= 'index.php?page_id=' . $pageID . '&cn-view=landing';

		// Search page.
		$rule['search/?$']
			= 'index.php?page_id=' . $pageID . '&cn-view=search';

		// Search results  page.
		$rule['results/?$']
			= 'index.php?page_id=' . $pageID . '&cn-view=results';

		/**
		 * Allows extensions to insert custom landing pages.
		 *
		 * @since 8.5.17
		 *
		 * @param array $rule   The root page rewrite rules.
		 * @param int   $pageID The front page ID.
		 */
		$rule = apply_filters( 'cn_root_rewrite_rule-landing', $rule, $pageID );

		// Category root rewrite rules.
		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-pg=$matches[5]&cn-view=card';

		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-view=card';

		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-view=card';

		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $category . '/(.+?)/' . $country . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-country=$matches[2]&cn-view=card';

		$rule[ $category . '/(.+?)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-region=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $category . '/(.+?)/' . $region . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-region=$matches[2]&cn-view=card';

		$rule[ $category . '/(.+?)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $category . '/(.+?)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		$rule[ $category . '/(.+?)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $category . '/(.+?)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule[ $category . '/(.+?)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $category . '/(.+?)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-cat-slug=$matches[1]&cn-view=card';

		// Country root rewrite rules.
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-view=card';

		$rule[ $country . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $country . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-view=card';

		// Country root w/o region [state/province] rules.
		$rule[ $country . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule[ $country . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		// Region root rewrite rules.
		$rule[ $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule[ $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		$rule[ $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $region . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-view=card';

		// Locality and postal code rewrite rules.
		$rule[ $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-postal-code=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $postal . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-postal-code=$matches[1]&cn-view=card';

		$rule[ $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-locality=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $locality . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-locality=$matches[1]&cn-view=card';

		// District rewrite rules.
		$rule[ $district . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-district=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $district . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-district=$matches[1]&cn-view=card';

		// County rewrite rules.
		$rule[ $county . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-county=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $county . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-county=$matches[1]&cn-view=card';

		// Organization rewrite rules.
		$rule[ $organization . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-organization=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $organization . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-organization=$matches[1]&cn-view=card';

		// Department rewrite rules.
		$rule[ $department . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-department=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $department . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-department=$matches[1]&cn-view=card';

		// Initial character rewrite rules.
		$rule[ $character . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-char=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $character . '/([^/]*)?/?$']
			= 'index.php?page_id=' . $pageID . '&cn-char=$matches[1]&cn-view=card';

		// Edit entry.
		$rule[ $name . '/([^/]*)/edit/?$']
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-view=detail&cn-process=edit';

		// Moderate entry.
		$rule[ $name . '/([^/]*)/moderate/?$']
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-process=moderate';

		// Delete entry.
		$rule[ $name . '/([^/]*)/delete/?$']
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-process=delete';

		// View entry detail / profile / bio.
		$rule[ $name . '/([^/]*)/detail/?$']
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-view=detail';

		// Download the vCard.
		$rule[ $name . '/([^/]*)/vcard/?$']
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-process=vcard';

		// Single entry.
		$rule[ $name . '/([^/]*)/?$']
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-view=detail';

		// View all entries.
		$rule[ 'view/all/?$']
			= 'index.php?&page_id=' . $pageID . '&cn-view=all';

		/**
		 * Allows extensions to insert custom view pages.
		 *
		 * @since 8.5.17
		 *
		 * @param array $rule   The root page rewrite rules.
		 * @param int   $pageID The front page ID.
		 */
		$rule = apply_filters( 'cn_root_rewrite_rule-view', $rule, $pageID );

		// Base Pagination.
		$rule['pg/([0-9]{1,})/?$']
			= 'index.php?page_id=' . $pageID . '&cn-pg=$matches[1]&cn-view=card';
		/*$rule['(.?.+?)/pg/([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-pg=$matches[2]&cn-view=card';*/
		/*$rule['pg/([0-9]{1,})/?$']
			= 'index.php?cn-pg=$matches[1]&cn-view=card';*/

		// Add the Connections rewrite rules to before the default page rewrite rules.
		$root_rewrite = array_merge( $root_rewrite, $rule );

		// var_dump($page_rewrite);
		return $root_rewrite;
	}

	/**
	 * Add the page rewrite rules.
	 *
	 * NOTE: Using a filter so I can add the rules right before the default page rules.
	 * This *should* prevent any rule conflicts.
	 *
	 * @access private
	 * @since  0.7.3
	 *
	 * @param array $page_rewrite
	 *
	 * @return array
	 */
	public static function addPageRewriteRules( $page_rewrite ) {

		$rule = array();

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		$character    = isset( $base['character_base'] ) && $base['character_base'] ? $base['character_base'] : 'char';
		$category     = isset( $base['category_base'] ) && $base['category_base'] ? $base['category_base'] : 'cat';
		$country      = isset( $base['country_base'] ) && $base['country_base'] ? $base['country_base'] : 'country';
		$region       = isset( $base['region_base'] ) && $base['region_base'] ? $base['region_base'] : 'region';
		$locality     = isset( $base['locality_base'] ) && $base['locality_base'] ? $base['locality_base'] : 'locality';
		$postal       = isset( $base['postal_code_base'] ) && $base['postal_code_base'] ? $base['postal_code_base'] : 'postal_code';
		$district     = isset( $base['district_base'] ) && $base['district_base'] ? $base['district_base'] : 'district';
		$county       = isset( $base['county_base'] ) && $base['county_base'] ? $base['county_base'] : 'county';
		$organization = isset( $base['organization_base'] ) && $base['organization_base'] ? $base['organization_base'] : 'organization';
		$department   = isset( $base['department_base'] ) && $base['department_base'] ? $base['department_base'] : 'department';
		$name         = isset( $base['name_base'] ) && $base['name_base'] ? $base['name_base'] : 'name';

		// Submit entry page.
		$rule['(.?.+?)/submit/?$']
			= 'index.php?pagename=$matches[1]&cn-view=submit';

		// Landing page.
		$rule['(.?.+?)/landing/?$']
			= 'index.php?pagename=$matches[1]&cn-view=landing';

		// Search page.
		$rule['(.?.+?)/search/?$']
			= 'index.php?pagename=$matches[1]&cn-view=search';

		// Search results page.
		$rule['(.?.+?)/results/?$']
			= 'index.php?pagename=$matches[1]&cn-view=results';

		/**
		 * Allows extensions to insert custom landing pages.
		 *
		 * @since 8.5.17
		 *
		 * @param array $rule The page rewrite rules.
		 */
		$rule = apply_filters( 'cn_page_rewrite_rule-landing', $rule );

		// Category root rewrite rules.
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-postal-code=$matches[5]&cn-pg=$matches[6]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-postal-code=$matches[5]&cn-pg=$matches[6]&cn-view=card';

		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-locality=$matches[5]&cn-pg=$matches[6]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-locality=$matches[5]&cn-view=card';

		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-view=card';

		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $category . '/(.+?)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-region=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $region . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-region=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $category . '/(.+?)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $category . '/(.+?)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $category . '/(.+?)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $category . '/(.+?)?/?$']
			= 'index.php?pagename=$matches[1]&cn-cat-slug=$matches[2]&cn-view=card';

		// Country root rewrite rules.
		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-view=card';

		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-view=card';

		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $country . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $country . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-view=card';

		// Country root w/o region [state/province] rules.
		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $country . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		// Region root rewrite rules.
		$rule['(.?.+?)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule['(.?.+?)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		$rule['(.?.+?)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $region . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-view=card';

		// Locality and postal code rewrite rules.
		$rule['(.?.+?)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule['(.?.+?)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		// District rewrite rules.
		$rule['(.?.+?)/' . $district . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-district=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $district . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-district=$matches[2]&cn-view=card';

		// County rewrite rules.
		$rule['(.?.+?)/' . $county . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-county=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $county . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-county=$matches[2]&cn-view=card';

		// Organization rewrite rules.
		$rule['(.?.+?)/' . $organization . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-organization=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $organization . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-organization=$matches[2]&cn-view=card';

		// Department rewrite rules.
		$rule['(.?.+?)/' . $department . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-department=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $department . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-department=$matches[2]&cn-view=card';

		// Initial character rewrite rules.
		$rule['(.?.+?)/' . $character . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-char=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule['(.?.+?)/' . $character . '/([^/]*)?/?$']
			= 'index.php?pagename=$matches[1]&cn-char=$matches[2]&cn-view=card';

		// Edit entry.
		$rule['(.?.+?)/' . $name . '/([^/]*)/edit/?$']
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail&cn-process=edit';

		// Moderate entry.
		$rule['(.?.+?)/' . $name . '/([^/]*)/moderate/?$']
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-process=moderate';

		// Delete entry.
		$rule['(.?.+?)/' . $name . '/([^/]*)/delete/?$']
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-process=delete';

		// View entry detail / profile / bio.
		$rule['(.?.+?)/' . $name . '/([^/]*)/detail/?$']
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail';

		// Download the vCard.
		$rule['(.?.+?)/' . $name . '/([^/]*)/vcard/?$']
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-process=vcard';

		// Single entry.
		$rule['(.?.+?)/' . $name . '/([^/]*)/?$']
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail';

		// View all entries.
		$rule['(.?.+?)/view/all/?$']
			= 'index.php?pagename=$matches[1]&cn-view=all';

		/**
		 * Allows extensions to insert custom view pages.
		 *
		 * @since 8.5.17
		 *
		 * @param array $rule The page rewrite rules.
		 */
		$rule = apply_filters( 'cn_page_rewrite_rule-view', $rule );

		// Base Pagination.
		$rule['(.?.+?)/pg/([0-9]{1,})/?$']
			= 'index.php?pagename=$matches[1]&cn-pg=$matches[2]&cn-view=card';

		// Add the Connections rewrite rules to before the default page rewrite rules.
		$page_rewrite = array_merge( $rule, $page_rewrite );

		//var_dump($page_rewrite);
		return $page_rewrite;
	}

	/**
	 * @access private
	 * @since  8.5.14
	 *
	 * @param array  $rules The rewrite rules array.
	 *
	 * @return array
	 */
	public static function addCPTRewriteRules( $rules ) {

		/*
		 * The filter `$permastructname . '_rewrite_rules'` does not pass the CPT permalink structure name.
		 * Lets grab it by parsing the current filter name.
		 */
		$slug = preg_replace( '/_rewrite_rules$/', '', current_filter() );
		$rule = array();

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		$character    = isset( $base['character_base'] ) && $base['character_base'] ? $base['character_base'] : 'char';
		$category     = isset( $base['category_base'] ) && $base['category_base'] ? $base['category_base'] : 'cat';
		$country      = isset( $base['country_base'] ) && $base['country_base'] ? $base['country_base'] : 'country';
		$region       = isset( $base['region_base'] ) && $base['region_base'] ? $base['region_base'] : 'region';
		$locality     = isset( $base['locality_base'] ) && $base['locality_base'] ? $base['locality_base'] : 'locality';
		$postal       = isset( $base['postal_code_base'] ) && $base['postal_code_base'] ? $base['postal_code_base'] : 'postal_code';
		$district     = isset( $base['district_base'] ) && $base['district_base'] ? $base['district_base'] : 'district';
		$county       = isset( $base['county_base'] ) && $base['county_base'] ? $base['county_base'] : 'county';
		$organization = isset( $base['organization_base'] ) && $base['organization_base'] ? $base['organization_base'] : 'organization';
		$department   = isset( $base['department_base'] ) && $base['department_base'] ? $base['department_base'] : 'department';
		$name         = isset( $base['name_base'] ) && $base['name_base'] ? $base['name_base'] : 'name';

		// Submit entry page.
		$rule[ $slug . '/(.+?)/submit/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-view=submit';

		// Landing page.
		$rule[ $slug . '/(.+?)/landing/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-view=landing';

		// Search page.
		$rule[ $slug . '/(.+?)/search/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-view=search';

		// Search results page.
		$rule[ $slug . '/(.+?)/results/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-view=results';

		/**
		 * Allows extensions to insert custom landing pages.
		 *
		 * @since 8.5.17
		 *
		 * @param array $rule The root page rewrite rules.
		 * @param int   $slug The CPT slug.
		 */
		$rule = apply_filters( 'cn_cpt_rewrite_rule-landing', $rule, $slug );

		// Category root rewrite rules.
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-postal-code=$matches[5]&cn-pg=$matches[6]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-postal-code=$matches[5]&cn-pg=$matches[6]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-locality=$matches[5]&cn-pg=$matches[6]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-locality=$matches[5]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-region=$matches[4]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $country . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-country=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-region=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $region . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-region=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $category . '/(.+?)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-cat-slug=$matches[2]&cn-view=card';

		// Country root rewrite rules.
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-view=card';

		// Country root w/o region [state/province] rules.
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $country . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-country=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		// Region root rewrite rules.
		$rule[ $slug . '/(.+?)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-region=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $region . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-region=$matches[2]&cn-view=card';

		// Locality and postal code rewrite rules.
		$rule[ $slug . '/(.+?)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $postal . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule[ $slug . '/(.+?)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $locality . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		// District rewrite rules.
		$rule[ $slug . '/(.+?)/' . $district . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-district=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $district . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-district=$matches[2]&cn-view=card';

		// County rewrite rules.
		$rule[ $slug . '/(.+?)/' . $county . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-county=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $county . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-county=$matches[2]&cn-view=card';

		// Organization rewrite rules.
		$rule[ $slug . '/(.+?)/' . $organization . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-organization=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $organization . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-organization=$matches[2]&cn-view=card';

		// Department rewrite rules.
		$rule[ $slug . '/(.+?)/' . $department . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-department=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $department . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-department=$matches[2]&cn-view=card';

		// Initial character rewrite rules.
		$rule[ $slug . '/(.+?)/' . $character . '/([^/]*)/pg/?([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-char=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $slug . '/(.+?)/' . $character . '/([^/]*)?/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-char=$matches[2]&cn-view=card';

		// Edit entry.
		$rule[ $slug . '/(.+?)/' . $name . '/([^/]*)/edit/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail&cn-process=edit';

		// Moderate entry.
		$rule[ $slug . '/(.+?)/' . $name . '/([^/]*)/moderate/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-entry-slug=$matches[2]&cn-process=moderate';

		// Delete entry.
		$rule[ $slug . '/(.+?)/' . $name . '/([^/]*)/delete/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-entry-slug=$matches[2]&cn-process=delete';

		// View entry detail / profile / bio.
		$rule[ $slug . '/(.+?)/' . $name . '/([^/]*)/detail/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail';

		// Download the vCard.
		$rule[ $slug . '/(.+?)/' . $name . '/([^/]*)/vcard/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-entry-slug=$matches[2]&cn-process=vcard';

		// Single entry.
		$rule[ $slug . '/(.+?)/' . $name . '/([^/]*)/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail';

		// View all entries.
		$rule[ $slug . '/(.+?)/view/all/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-view=all';

		/**
		 * Allows extensions to insert custom view pages.
		 *
		 * @since 8.5.17
		 *
		 * @param array $rule The root page rewrite rules.
		 * @param int   $slug The CPT slug.
		 */
		$rule = apply_filters( 'cn_cpt_rewrite_rule-view', $rule, $slug );

		// Base Pagination.
		$rule[ $slug . '/(.+?)/pg/([0-9]{1,})/?$']
			= 'index.php?' . $slug . '=$matches[1]&cn-pg=$matches[2]&cn-view=card';

		// Add the Connections rewrite rules to before the default CPT rewrite rules.
		$rules = array_merge( $rule, $rules );

		return $rules;
	}

	/**
	 * Check the requested URL for Connections' query vars and if found rewrites the URL
	 * and redirects to the new URL.
	 *
	 * Hooks into the template_redirect action.
	 *
	 * @access private
	 * @since 0.7.3.2
	 * @uses is_page()
	 * @uses is_404()
	 * @uses is_ssl()
	 * @uses cnQuery::getVar()
	 * @uses get_option()
	 * @uses remove_query_arg()
	 * @uses user_trailingslashit()
	 * @uses add_query_arg()
	 * @uses wp_redirect()
	 * @return void
	 */
	public function canonicalRedirectAction() {
		global $wp_rewrite, $connections;

		// Right now, lets only support pages. Not a page, punt...
		if ( ! is_page() ) return FALSE;
		if ( is_404() ) return FALSE;

		// The URL in the address bar
		$requestedURL  = is_ssl() ? 'https://' : 'http://';
		$requestedURL .= $_SERVER['HTTP_HOST'];
		$requestedURL .= $_SERVER['REQUEST_URI'];

		$originalURL = $requestedURL;
		$parsedURL   = @parse_url( $requestedURL );

		// Ensure array index is set, prevent PHP error notice.
		if( ! isset( $parsedURL['query'] ) ) $parsedURL['query'] ='';

		$redirectURL = explode( '?', $requestedURL );
		$redirectURL = trailingslashit( $redirectURL[0] );

		if ( FALSE === $originalURL ) return FALSE;

		// We only need to process the URL and redirect  if the user is using pretty permalinks.
		if ( is_object ( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) {

			// Get the settings for the base of each data type to be used in the URL.
			$base = get_option( 'connections_permalink' );

			// Categories
			if ( cnQuery::getVar( 'cn-cat' ) ) {

				$slug = array();
				$categoryID = (int) cnQuery::getVar( 'cn-cat' );
				$parsedURL['query'] = remove_query_arg( 'cn-cat', $parsedURL['query'] );

				$category = $connections->retrieve->category( $categoryID );
				// var_dump( $category ); //exit();

				// @todo This is going to create quite a few db hits. Should optimize somehow.
				if ( ! empty( $category ) ) {

					do {
						array_unshift( $slug, $category->slug );
						$category = $connections->retrieve->category( $category->parent );
					} while ( ! empty( $category->parent ) );

				}
				// var_dump( $slug ); //exit();

				if ( ! empty( $slug ) && ! stripos( $redirectURL , $base['category_base'] . '/' . implode( '/', $slug ) ) ) $redirectURL .= user_trailingslashit( $base['category_base'] . '/' . implode( '/', $slug ) );
				// var_dump( $redirectURL ); //exit();
			}

			// If paged, append pagination
			if ( cnQuery::getVar( 'cn-pg' ) ) {

				$page = (int) cnQuery::getVar('cn-pg');
				$parsedURL['query'] = remove_query_arg( 'cn-pg', $parsedURL['query'] );

				if ( $page > 1 && ! stripos( $redirectURL , "pg/$page" ) ) $redirectURL .= user_trailingslashit( "pg/$page", 'page' );
				// var_dump( $redirectURL ); //exit();
			}
		}

		// Add back on to the URL any remaining query string values.
		$parsedURL['query'] = preg_replace( '#^\??&*?#', '', $parsedURL['query'] );

		if ( $redirectURL && ! empty( $parsedURL['query'] ) ) {
			parse_str( $parsedURL['query'], $_parsed_query );
			$_parsed_query = array_map( 'rawurlencode_deep', $_parsed_query );
			$redirectURL = add_query_arg( $_parsed_query, $redirectURL );
		}

		if ( ! $redirectURL || $redirectURL == $requestedURL ) return FALSE;

		wp_redirect( $redirectURL, 301 );
		exit();
	}

	/**
	 * Checks the requested URL for Connections' query vars and if found rewrites the URL
	 * and passes the new URL back to finish being processed by the redirect_canonical() function.
	 *
	 * Hooks into the redirect_canonical filter.
	 *
	 * @access private
	 * @since 0.7.3.2
	 * @uses cnQuery::getVar()
	 * @uses remove_query_arg()
	 * @uses user_trailingslashit()
	 * @param string  $redirectURL
	 * @param string  $requestedURL
	 * @return string
	 */
	public function canonicalRedirectFilter( $redirectURL, $requestedURL ) {

		$originalURL = $redirectURL;
		$parsedURL   = @parse_url( $requestedURL );

		$redirectURL = explode( '?', $redirectURL );
		$redirectURL = $redirectURL[0];

		// Ensure array index is set, prevent PHP error notice.
		if( ! isset( $parsedURL['query'] ) ) $parsedURL['query'] ='';

		// If paged, append pagination
		if ( cnQuery::getVar( 'cn-pg' ) ) {

			$page = (int) cnQuery::getVar('cn-pg');
			$parsedURL['query'] = remove_query_arg( 'cn-pg', $parsedURL['query'] );
			if ( $page > 1 && ! stripos( $redirectURL , "pg/$page" ) ) $redirectURL .= user_trailingslashit( "pg/$page", 'page' );

			// var_dump( $redirectURL );
			// exit();
		}

		// Add back on to the URL any remaining query string values.
		$parsedURL['query'] = preg_replace( '#^\??&*?#', '', $parsedURL['query'] );

		if ( $redirectURL && ! empty( $parsedURL['query'] ) ) {
			parse_str( $parsedURL['query'], $_parsed_query );
			$_parsed_query = array_map( 'rawurlencode', $_parsed_query );
			$redirectURL = add_query_arg( $_parsed_query, $redirectURL );
		}

		return $redirectURL;
	}

	/**
	 * Disable the canonical redirect when on the front page.
	 *
	 * NOTE: This is required to allow search queries to be properly redirected to the front page.
	 * If this were not in place the user would be redirected to the blog home page.
	 *
	 * @link http://wordpress.stackexchange.com/questions/51530/rewrite-rules-problem-when-rule-includes-homepage-slug
	 * @link https://core.trac.wordpress.org/ticket/16373
	 *
	 * @TODO Perhaps the redirect should only be prevented when the page ID matches the directory home page ID.
	 *
	 * @access private
	 * @since  0.7.6.4
	 *
	 * @param string $redirectURL  The URL to redirect to.
	 * @param string $requestedURL The original requested URL.
	 *
	 * @return string URL
	 */
	public static function frontPageCanonicalRedirect( $redirectURL, $requestedURL ) {
		global $wp_query;
		// $homeID = cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' );

		// Grab the array containing all query vars registered by Connections.
		// Remove the cn-image query vars.
		$registeredQueryVars = array_diff(
			cnRewrite::queryVars( array() ),
			array( 'src', 'w', 'h', 'q', 'a', 'zc', 'f', 's', 'o', 'cc', 'ct' )
		);

		$registeredQueryVars = array_flip( $registeredQueryVars );
		$found               = array_intersect(
			array_keys( $registeredQueryVars ),
			array_keys( (array) $wp_query->query_vars )
		);

		// Solution implement by another plugin.
		//if ( $main_page_id = wpbdp_get_page_id( 'main' ) ) {
		//	if ( is_page() && !is_feed() && isset( $wp_query->queried_object ) &&
		//	     get_option( 'show_on_front' ) == 'page' &&
		//	     get_option( 'page_on_front' ) == $wp_query->queried_object->ID ) {
		//		return $requestedURL;
		//	}
		//}

		// Do not do the redirect if one of the core query vars is in the HTTP request.
		if ( is_front_page() &&
		     get_option( 'show_on_front' ) == 'page' &&
		     ! empty( $found )
		) {

			return $requestedURL;
		}

		return $redirectURL;
	}
}
