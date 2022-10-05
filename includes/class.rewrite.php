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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Request;
use Connections_Directory\Taxonomy\Registry;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_string;

class cnRewrite {

	/**
	 * The rewrite tag namespace.
	 *
	 * @since 10.2
	 * @var string
	 */
	static $namespace = 'ConnectionsDirectory';

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public static function hooks() {

		// Remove the canonical redirect -- for testing.
		// remove_filter('template_redirect', 'redirect_canonical');
		add_filter( 'redirect_canonical', array( __CLASS__, 'frontPageCanonicalRedirect' ), 10, 2 );

		add_filter( 'query_vars', array( __CLASS__, 'queryVars' ) );
		add_filter( 'root_rewrite_rules', array( __CLASS__, 'addRootRewriteRules' ) );
		add_filter( 'page_rewrite_rules', array( __CLASS__, 'addPageRewriteRules' ) );
		// add_action( 'template_redirect', array( __CLASS__ , 'canonicalRedirectAction' ) );
		// add_filter( 'redirect_canonical', array( __CLASS__ , 'canonicalRedirectFilter') , 10, 2 );

		$cptOptions = get_option( 'connections_cpt' );

		if ( isset( $cptOptions['supported'] ) && ! empty( $cptOptions['supported'] ) && is_array( $cptOptions['supported'] ) ) {

			foreach ( $cptOptions['supported'] as $type ) {

				add_filter( $type . '_rewrite_rules', array( __CLASS__, 'addCPTRewriteRules' ) );
			}
		}

		add_action( 'wp_loaded', array( __CLASS__, 'registerRewriteTags' ) );
		add_action( 'init', array( __CLASS__, 'addEndPoints' ) );
		add_filter( 'request', array( __CLASS__, 'setImageEndpointQueryVar' ) );
	}

	/**
	 * The registered query vars.
	 *
	 * @since 10.3
	 *
	 * @return array
	 */
	public static function getRegisteredQueryVars() {

		$var = array();

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
		// $var[] = 'cn-char'; // initial character
		// $var[] = 'cn-s';   // search term
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
		// @todo this should be added using a filter in the cnImage class.
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

		// @todo Should this return the custom taxonomy query vars?

		return apply_filters( 'Connections_Directory/Rewrite/Query_Vars', $var );
	}

	/**
	 * Callback for the `query_vars` filter.
	 *
	 * Register the valid query variables.
	 *
	 * @internal
	 * @since  0.7.3.2
	 *
	 * @param array $vars Provide information about a function parameter.
	 *
	 * @return array
	 */
	public static function queryVars( $vars ) {

		return array_merge( $vars, self::getRegisteredQueryVars() );
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
		) {
			// In some cases WP misinterprets the request as a page request and returns a 404.
			$var['page'] = $var['pagename'] = $var['name'] = false;

			$var[ CN_IMAGE_ENDPOINT ] = true;
		}

		return $var;
	}

	/**
	 * Callback for the `wp_loaded` action.
	 *
	 * NOTE: This should run on the `wp_loaded` action because rules are generated right before the `parse_request` action.
	 *       Running on the `wp_loaded` action is as late as possible to allow CPT's to be registered.
	 *
	 * Register the rewrites tags to be used generate the rewrite rules.
	 *
	 * @since 10.2
	 */
	public static function registerRewriteTags() {

		$namespace = self::$namespace;

		foreach ( get_post_types( array( '_builtin' => false, 'publicly_queryable' => true, 'rewrite' => true ), 'objects' ) as $postType ) {
			// var_dump( $postType );

			$postTypeToken = "%{$namespace}CPT_{$postType->name}%";

			if ( $postType->hierarchical ) {

				$regex = '(.+?)';
				$query = $postType->query_var ? "{$postType->query_var}=" : "post_type={$postType->name}&pagename=";

			} else {

				$regex = '([^/]+)';
				$query = $postType->query_var ? "{$postType->query_var}=" : "post_type={$postType->name}&name=";
			}

			add_rewrite_tag( $postTypeToken, $regex, $query );
		}

		// The extra .? at the beginning prevents clashes with other regular expressions in the rules array.
		add_rewrite_tag( "%{$namespace}_pagename%", '(.?.+?)', 'pagename=' );
		add_rewrite_tag( "%{$namespace}_page%", '?([0-9]{1,})', 'cn-pg=' );
		add_rewrite_tag( "%{$namespace}_country%", '([^/]*)', 'cn-country=' );
		add_rewrite_tag( "%{$namespace}_region%", '([^/]*)', 'cn-region=' );
		add_rewrite_tag( "%{$namespace}_locality%", '([^/]*)', 'cn-locality=' );
		add_rewrite_tag( "%{$namespace}_postal_code%", '([^/]*)', 'cn-postal-code=' );
		add_rewrite_tag( "%{$namespace}_district%", '([^/]*)', 'cn-district=' );
		add_rewrite_tag( "%{$namespace}_county%", '([^/]*)', 'cn-county=' );
		add_rewrite_tag( "%{$namespace}_organization%", '([^/]*)', 'cn-organization=' );
		add_rewrite_tag( "%{$namespace}_department%", '([^/]*)', 'cn-department=' );
		add_rewrite_tag( "%{$namespace}_character%", '([^/]*)', 'cn-char=' );
		add_rewrite_tag( "%{$namespace}_name%", '([^/]*)', 'cn-entry-slug=' );
	}

	/**
	 * Retrieve the user defined permalink slugs from the settings.
	 *
	 * NOTE: After the `setup_theme` action hook, this will also return the registered taxonomy slugs.
	 *
	 * NOTE: The array keys will be utilized as the tokens when generating rewrite rules.
	 * @see cnRewrite::generateRule()
	 *
	 * @since 10.2
	 *
	 * @return array
	 */
	public static function getPermalinkSlugs() {

		/*
		 * Get the settings for the base of each data type to be used in the URL.
		 *
		 * NOTE: The base permalink slugs can not conflict with core WordPress permalinks slugs such as `category` and `tag`.
		 */
		$base  = get_option( 'connections_permalink', array() );
		$slugs = array();

		$slugs['character']    = _array::get( $base, 'character_base', 'char' );
		$slugs['country']      = _array::get( $base, 'country_base', 'country' );
		$slugs['region']       = _array::get( $base, 'region_base', 'region' );
		$slugs['locality']     = _array::get( $base, 'locality_base', 'locality' );
		$slugs['postal_code']  = _array::get( $base, 'postal_code_base', 'postal_code' );
		$slugs['district']     = _array::get( $base, 'district_base', 'district' );
		$slugs['county']       = _array::get( $base, 'county_base', 'county' );
		$slugs['organization'] = _array::get( $base, 'organization_base', 'organization' );
		$slugs['department']   = _array::get( $base, 'department_base', 'department' );
		$slugs['name']         = _array::get( $base, 'name_base', 'name' );

		/**
		 * @since 10.2
		 *
		 * @param array $slugs The permalink slugs.
		 */
		return apply_filters( 'Connections_Directory/Rewrite/Permalink_Slugs', $slugs );
	}

	/**
	 * Generates rewrite rules from a permalink structure.
	 *
	 * This is the Connections equivalent of @see WP_Rewrite::generate_rewrite_rules()
	 *
	 * @since 10.2
	 *
	 * @param string $structure The permalink structure.
	 * @param array  $args      {
	 *     @see WP_Rewrite::generate_rewrite_rules()
	 *     @type int    $ep_mask             Optional. Endpoint mask defining what endpoints are added to the structure.
	 *                                       Accepts `EP_NONE`, `EP_PERMALINK`, `EP_ATTACHMENT`, `EP_DATE`, `EP_YEAR`,
	 *                                       `EP_MONTH`, `EP_DAY`, `EP_ROOT`, `EP_COMMENTS`, `EP_SEARCH`, `EP_CATEGORIES`,
	 *                                       `EP_TAGS`, `EP_AUTHORS`, `EP_PAGES`, `EP_ALL_ARCHIVES`, and `EP_ALL`.
	 *                                       Default `EP_NONE`.
	 *     @type bool   $paged               Optional. Whether archive pagination rules should be added for the structure.
	 *                                       Default true.
	 *     @type bool   $feed                Optional Whether feed rewrite rules should be added for the structure.
	 *                                       Default true.
	 *     @type bool   $forcomments         Optional. Whether the feed rules should be a query for a comments feed.
	 *                                       Default false.
	 *     @type bool   $walk_dirs           Optional. Whether the 'directories' making up the structure should be walked
	 *                                       over and rewrite rules built for each in-turn. Default true.
	 *     @type bool   $endpoints           Optional. Whether endpoints should be applied to the generated rewrite rules.
	 *                                       Default true.
	 * }
	 * @param array  $addQuery  Query args to append to the rewrite query.
	 * @param bool   $isRoot
	 *
	 * @return string[]
	 */
	public static function generateRule( $structure, $args = array(), $addQuery = array(), $isRoot = false ) {

		global $wp_rewrite;

		$namespace = self::$namespace;
		$pageID    = get_option( 'page_on_front' );
		$slugs     = self::getPermalinkSlugs();

		// The taxonomy rewrite tags are dealt with in the Taxonomy API.
		$taxonomies = Registry::get()->getTaxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			_array::forget( $slugs, $taxonomy->getSlug() );
		}

		// Replace the `%pagename%` and `%page%` tokens with the namespaced versions so they do not conflict with the core WP rewrite tags.
		$structure = str_ireplace(
			array( '%pagename%', '%page%' ),
			array( "%{$namespace}_pagename%", "%{$namespace}_page%" ),
			$structure
		);

		// Replace the rewrite tag tokens with registered namespaced rewrite tag tokens.
		foreach ( $slugs as $key => $slug ) {

			$structure = str_ireplace(
				"%{$key}%",
				"{$slug}/%{$namespace}_{$key}%",
				$structure
			);
		}

		$rules = $wp_rewrite->generate_rewrite_rules(
			$structure,
			_array::get( $args, 'ep_mask', EP_NONE ),
			_array::get( $args, 'paged', true ),
			_array::get( $args, 'feed', true ),
			_array::get( $args, 'forcomments', false ),
			_array::get( $args, 'walk_dirs', true ),
			_array::get( $args, 'endpoints', true )
		);

		// Connections utilizes additional query args to determine the view state, if supplied, append to the rewrite query.
		foreach ( $rules as $regex => &$query ) {

			/*
			 * Need to `urldecode()` as `add_query_arg()` passes URL through `urlencode_deep()`.
			 * Rewrite query URLs are not encoded.
			 */
			$query = urldecode( add_query_arg( $addQuery, $query ) );

			/*
			 * If writing the root rewrite rules and a page is set to front, add the `page_id` query to the front of the query request.
			 */
			if ( $isRoot && get_option( 'page_on_front' ) ) {

				$query = _string::insert( $query, "page_id={$pageID}&", strpos( $query, '?' ) + 1 );
			}
		}

		return $rules;
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
	 * @param array $root_rewrite
	 *
	 * @return array
	 */
	public static function addRootRewriteRules( $root_rewrite ) {

		// If a page has not been set to be the front, exit, because these rules would not apply.
		if ( ! get_option( 'page_on_front' ) ) {
			return $root_rewrite;
		}

		$rule = array();

		/** @var string $pageID Get the page id of the user selected front page. */
		$pageID = get_option( 'page_on_front' );

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		$character    = isset( $base['character_base'] ) && $base['character_base'] ? $base['character_base'] : 'char';
		// $category     = isset( $base['category_base'] ) && $base['category_base'] ? $base['category_base'] : 'cat';
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

		$rule = apply_filters( 'Connections_Directory/Rewrite/Root_Rules/Taxonomy', $rule, $pageID );

		// Country root rewrite rules.
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $region . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-region=$matches[2]&cn-view=card';

		$rule[ $country . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $country . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-view=card';

		// Country root w/o region [state/province] rules.
		$rule[ $country . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $postal . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule[ $country . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $country . '/([^/]*)/' . $locality . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-country=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		// Region root rewrite rules.
		$rule[ $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule[ $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		$rule[ $region . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $region . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-region=$matches[1]&cn-view=card';

		// Locality and postal code rewrite rules.
		$rule[ $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-postal-code=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $postal . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-postal-code=$matches[1]&cn-view=card';

		$rule[ $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-locality=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $locality . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-locality=$matches[1]&cn-view=card';

		// District rewrite rules.
		$rule[ $district . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-district=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $district . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-district=$matches[1]&cn-view=card';

		// County rewrite rules.
		$rule[ $county . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-county=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $county . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-county=$matches[1]&cn-view=card';

		// Organization rewrite rules.
		$rule[ $organization . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-organization=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $organization . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-organization=$matches[1]&cn-view=card';

		// Department rewrite rules.
		$rule[ $department . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-department=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $department . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-department=$matches[1]&cn-view=card';

		// Initial character rewrite rules.
		$rule[ $character . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-char=$matches[1]&cn-pg=$matches[2]&cn-view=card';
		$rule[ $character . '/([^/]*)?/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-char=$matches[1]&cn-view=card';

		// Edit entry.
		$rule[ $name . '/([^/]*)/edit/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-view=detail&cn-process=edit';

		// Moderate entry.
		$rule[ $name . '/([^/]*)/moderate/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-process=moderate';

		// Delete entry.
		$rule[ $name . '/([^/]*)/delete/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-process=delete';

		// View entry detail / profile / bio.
		$rule[ $name . '/([^/]*)/detail/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-view=detail';

		// Download the vCard.
		$rule[ $name . '/([^/]*)/vcard/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-process=vcard';

		// Single entry.
		$rule[ $name . '/([^/]*)/?$' ]
			= 'index.php?page_id=' . $pageID . '&cn-entry-slug=$matches[1]&cn-view=detail';

		// View all entries.
		$rule['view/all/?$']
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

		return $root_rewrite;
	}

	/**
	 * Callback the for the `page_rewrite_rules` filter.
	 *
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
		// $category     = isset( $base['category_base'] ) && $base['category_base'] ? $base['category_base'] : 'cat';
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

		$rule = apply_filters( 'Connections_Directory/Rewrite/Page_Rules/Taxonomy', $rule );

		// Country root rewrite rules.
		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-postal-code=$matches[4]&cn-view=card';

		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-pg=$matches[5]&cn-view=card';
		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-locality=$matches[4]&cn-view=card';

		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $region . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-region=$matches[3]&cn-view=card';

		$rule[ '(.?.+?)/' . $country . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $country . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-view=card';

		// Country root w/o region [state/province] rules.
		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $postal . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ '(.?.+?)/' . $country . '/([^/]*)/' . $locality . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-country=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		// Region root rewrite rules.
		$rule[ '(.?.+?)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ '(.?.+?)/' . $region . '/([^/]*)/' . $postal . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-postal-code=$matches[3]&cn-view=card';

		$rule[ '(.?.+?)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-pg=$matches[4]&cn-view=card';
		$rule[ '(.?.+?)/' . $region . '/([^/]*)/' . $locality . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-locality=$matches[3]&cn-view=card';

		$rule[ '(.?.+?)/' . $region . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $region . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-region=$matches[2]&cn-view=card';

		// Locality and postal code rewrite rules.
		$rule[ '(.?.+?)/' . $postal . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-postal-code=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $postal . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-postal-code=$matches[2]&cn-view=card';

		$rule[ '(.?.+?)/' . $locality . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-locality=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $locality . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-locality=$matches[2]&cn-view=card';

		// District rewrite rules.
		$rule[ '(.?.+?)/' . $district . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-district=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $district . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-district=$matches[2]&cn-view=card';

		// County rewrite rules.
		$rule[ '(.?.+?)/' . $county . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-county=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $county . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-county=$matches[2]&cn-view=card';

		// Organization rewrite rules.
		$rule[ '(.?.+?)/' . $organization . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-organization=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $organization . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-organization=$matches[2]&cn-view=card';

		// Department rewrite rules.
		$rule[ '(.?.+?)/' . $department . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-department=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $department . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-department=$matches[2]&cn-view=card';

		// Initial character rewrite rules.
		$rule[ '(.?.+?)/' . $character . '/([^/]*)/pg/?([0-9]{1,})/?$' ]
			= 'index.php?pagename=$matches[1]&cn-char=$matches[2]&cn-pg=$matches[3]&cn-view=card';
		$rule[ '(.?.+?)/' . $character . '/([^/]*)?/?$' ]
			= 'index.php?pagename=$matches[1]&cn-char=$matches[2]&cn-view=card';

		// Edit entry.
		$rule[ '(.?.+?)/' . $name . '/([^/]*)/edit/?$' ]
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail&cn-process=edit';

		// Moderate entry.
		$rule[ '(.?.+?)/' . $name . '/([^/]*)/moderate/?$' ]
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-process=moderate';

		// Delete entry.
		$rule[ '(.?.+?)/' . $name . '/([^/]*)/delete/?$' ]
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-process=delete';

		// View entry detail / profile / bio.
		$rule[ '(.?.+?)/' . $name . '/([^/]*)/detail/?$' ]
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-view=detail';

		// Download the vCard.
		$rule[ '(.?.+?)/' . $name . '/([^/]*)/vcard/?$' ]
			= 'index.php?pagename=$matches[1]&cn-entry-slug=$matches[2]&cn-process=vcard';

		// Single entry.
		$rule[ '(.?.+?)/' . $name . '/([^/]*)/?$' ]
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

		return $page_rewrite;
	}

	/**
	 * Callback for the `{$permastructname}_rewrite_rules` filter.
	 *
	 * @noinspection PhpUnused
	 * @access private
	 * @since  8.5.14
	 *
	 * @param array $rules The rewrite rules array.
	 *
	 * @return array
	 */
	public static function addCPTRewriteRules( $rules ) {

		/*
		 * The filter `$permastructname . '_rewrite_rules'` does not pass the CPT permalink structure name.
		 * Lets grab it by parsing the current filter name.
		 */
		$postType = preg_replace( '/_rewrite_rules$/', '', current_filter() );
		$post     = get_post_type_object( $postType );

		$namespace = cnRewrite::$namespace;

		$postSlug  = $post->rewrite['slug'];
		$postToken = "%{$namespace}CPT_{$post->name}%";

		$rewriteArgs = array(
			'with_front'   => false,
			'hierarchical' => false,
			'ep_mask'      => EP_NONE,
			'paged'        => false,
			'feed'         => false,
			'forcomments'  => false,
			'walk_dirs'    => false,
			'endpoints'    => false,
		);

		$landingRules = array_merge(
			// Submit entry page.
			self::generateRule( "{$postSlug}/{$postToken}/submit", $rewriteArgs, array( 'cn-view' => 'submit' ) ),
			// Landing page.
			self::generateRule( "{$postSlug}/{$postToken}/landing", $rewriteArgs, array( 'cn-view' => 'landing' ) ),
			// Search page.
			self::generateRule( "{$postSlug}/{$postToken}/search", $rewriteArgs, array( 'cn-view' => 'search' ) ),
			// Search results page.
			self::generateRule( "{$postSlug}/{$postToken}/results", $rewriteArgs, array( 'cn-view' => 'results' ) )
		);

		/**
		 * @since 10.2
		 *
		 * @param array        $taxonomyRules The taxonomy rewrite rules.
		 * @param WP_Post_Type $post          A WP Post object.
		 */
		$landingRules = apply_filters( 'Connections_Directory/Rewrite/CPT_Rules/Landing', $landingRules, $post );

		/**
		 * @since 10.2
		 *
		 * @param array        $taxonomyRules The taxonomy rewrite rules.
		 * @param WP_Post_Type $post          A WP Post object.
		 */
		$taxonomyRules = apply_filters( 'Connections_Directory/Rewrite/CPT_Rules/Taxonomy', array(), $post );

		$regionRules = array_merge(
			// Country + region + postal code rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%region%/%postal_code%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%region%/%postal_code%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Country + region + locality rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%region%/%locality%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%region%/%locality%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Country + region rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%region%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%region%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Country rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%country%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%country%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Country + postal code rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%postal_code%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%postal_code%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Country + locality rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%locality%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%country%/%locality%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Region + postal code rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%region%/%postal_code%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%region%/%postal_code%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Region + locality rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%region%/%locality%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%region%/%locality%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Region rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%region%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%region%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Postal code rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%postal_code%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%postal_code%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Locality rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%locality%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%locality%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// District rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%district%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%district%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Organization rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%county%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%county%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// County rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%organization%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%organization%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Department rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%department%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%department%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			// Character rewrite rules.
			self::generateRule( "{$postSlug}/{$postToken}/%character%/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) ),
			self::generateRule( "{$postSlug}/{$postToken}/%character%", $rewriteArgs, array( 'cn-view' => 'card' ) )
		);

		// Entry and entry actions rules.
		$entryRules = array_merge(
			// Edit entry.
			self::generateRule( "{$postSlug}/{$postToken}/%name%/edit", $rewriteArgs, array( 'cn-view' => 'detail', 'cn-process' => 'edit' ) ),
			// Moderate entry.
			self::generateRule( "{$postSlug}/{$postToken}/%name%/moderate", $rewriteArgs, array( 'cn-process' => 'moderate' ) ),
			// Delete entry.
			self::generateRule( "{$postSlug}/{$postToken}/%name%/delete", $rewriteArgs, array( 'cn-process' => 'delete' ) ),
			// View entry detail / profile / bio.
			self::generateRule( "{$postSlug}/{$postToken}/%name%/detail", $rewriteArgs, array( 'cn-view' => 'detail' ) ),
			// Download the vCard.
			self::generateRule( "{$postSlug}/{$postToken}/%name%/vcard", $rewriteArgs, array( 'cn-process' => 'vcard' ) ),
			// Single entry.
			self::generateRule( "{$postSlug}/{$postToken}/%name%", $rewriteArgs, array( 'cn-view' => 'detail' ) )
		);

		// View all entries.
		$viewRules = self::generateRule( "{$postSlug}/{$postToken}/view/all", $rewriteArgs, array( 'cn-view' => 'all' ) );

		/**
		 * @since 10.2
		 *
		 * @param array        $taxonomyRules The taxonomy rewrite rules.
		 * @param WP_Post_Type $post          A WP Post object.
		 */
		$viewRules = apply_filters( 'Connections_Directory/Rewrite/CPT_Rules/View', $viewRules, $post );

		// Pagination rules.
		$paginationRules = self::generateRule( "{$postSlug}/{$postToken}/pg/%page%", $rewriteArgs, array( 'cn-view' => 'card' ) );

		$allRules = array_merge(
			$landingRules,
			$taxonomyRules,
			$regionRules,
			$entryRules,
			$viewRules,
			$paginationRules
		);

		// Add the Connections rewrite rules before the default CPT rewrite rules.
		$rules = array_merge( $allRules, $rules );

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
	 *
	 * @return false|void
	 */
	public function canonicalRedirectAction() {
		global $wp_rewrite, $connections;

		// Right now, lets only support pages. Not a page, punt...
		if ( ! is_page() ) {
			return false;
		}

		if ( is_404() ) {
			return false;
		}

		// The URL in the address bar.
		$requestedURL  = is_ssl() ? 'https://' : 'http://';
		$requestedURL .= Request\Server_HTTP_Host::input()->value();
		$requestedURL .= Request\Server_Request_URI::input()->value();

		$originalURL = $requestedURL;
		$parsedURL   = @parse_url( $requestedURL );

		// Ensure array index is set, prevent PHP error notice.
		if ( ! isset( $parsedURL['query'] ) ) {
			$parsedURL['query'] = '';
		}

		$redirectURL = explode( '?', $requestedURL );
		$redirectURL = trailingslashit( $redirectURL[0] );

		if ( false === $originalURL ) {
			return false;
		}

		// We only need to process the URL and redirect  if the user is using pretty permalinks.
		if ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) {

			// Get the settings for the base of each data type to be used in the URL.
			$base = get_option( 'connections_permalink' );

			// Categories.
			if ( cnQuery::getVar( 'cn-cat' ) ) {

				$slug               = array();
				$categoryID         = (int) cnQuery::getVar( 'cn-cat' );
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

				if ( ! empty( $slug ) && ! stripos( $redirectURL, $base['category_base'] . '/' . implode( '/', $slug ) ) ) {
					$redirectURL .= user_trailingslashit( $base['category_base'] . '/' . implode( '/', $slug ) );
				}
				// var_dump( $redirectURL ); //exit();
			}

			// If paged, append pagination.
			if ( cnQuery::getVar( 'cn-pg' ) ) {

				$page               = (int) cnQuery::getVar( 'cn-pg' );
				$parsedURL['query'] = remove_query_arg( 'cn-pg', $parsedURL['query'] );

				if ( $page > 1 && ! stripos( $redirectURL, "pg/$page" ) ) {
					$redirectURL .= user_trailingslashit( "pg/$page", 'page' );
				}
				// var_dump( $redirectURL ); //exit();
			}
		}

		// Add back on to the URL any remaining query string values.
		$parsedURL['query'] = preg_replace( '#^\??&*?#', '', $parsedURL['query'] );

		if ( $redirectURL && ! empty( $parsedURL['query'] ) ) {
			parse_str( $parsedURL['query'], $_parsed_query );
			$_parsed_query = array_map( 'rawurlencode_deep', $_parsed_query );
			$redirectURL   = add_query_arg( $_parsed_query, $redirectURL );
		}

		if ( ! $redirectURL || $redirectURL == $requestedURL ) {
			return false;
		}

		wp_safe_redirect( $redirectURL, 301 );
		exit();
	}

	/**
	 * Checks the requested URL for Connections' query vars and if found rewrites the URL
	 * and passes the new URL back to finish being processed by the redirect_canonical() function.
	 *
	 * Hooks into the redirect_canonical filter.
	 *
	 * @internal
	 * @since 0.7.3.2
	 *
	 * @param string $redirectURL
	 * @param string $requestedURL
	 *
	 * @return string
	 */
	public function canonicalRedirectFilter( $redirectURL, $requestedURL ) {

		$originalURL = $redirectURL;
		$parsedURL   = @parse_url( $requestedURL );

		$redirectURL = explode( '?', $redirectURL );
		$redirectURL = $redirectURL[0];

		// Ensure array index is set, prevent PHP error notice.
		if ( ! isset( $parsedURL['query'] ) ) {
			$parsedURL['query'] = '';
		}

		// If paged, append pagination.
		if ( cnQuery::getVar( 'cn-pg' ) ) {

			$page               = (int) cnQuery::getVar( 'cn-pg' );
			$parsedURL['query'] = remove_query_arg( 'cn-pg', $parsedURL['query'] );
			if ( $page > 1 && ! stripos( $redirectURL, "pg/$page" ) ) {
				$redirectURL .= user_trailingslashit( "pg/$page", 'page' );
			}

			// var_dump( $redirectURL );
			// exit();
		}

		// Add back on to the URL any remaining query string values.
		$parsedURL['query'] = preg_replace( '#^\??&*?#', '', $parsedURL['query'] );

		if ( $redirectURL && ! empty( $parsedURL['query'] ) ) {
			parse_str( $parsedURL['query'], $_parsed_query );
			$_parsed_query = array_map( 'rawurlencode', $_parsed_query );
			$redirectURL   = add_query_arg( $_parsed_query, $redirectURL );
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
		$post                = get_queried_object();

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

		} elseif ( is_home() && cnShortcode::isSupportedPostType( $post ) ) {

			return $requestedURL;
		}

		return $redirectURL;
	}
}
