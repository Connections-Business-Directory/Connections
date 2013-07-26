<?php

/**
 * Static class for filtering permalinks, changing page/post titles and
 * adding page/page meta descriptions.
 *
 * @package     Connections
 * @subpackage  SEO
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnSEO {

	/**
	 * Whether or not to filter the permalink.
	 *
	 * @access private
	 * @since 0.7.8
	 * @var boolean
	 */
	private static $filterPermalink = TRUE;

	/**
	 * Register the default template actions.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses add_filter()
	 * @return (void)
	 */
	public static function init() {

		// Add the page meta description.
		add_action( 'wp_head', array( __CLASS__, 'metaDesc' ), 10 );

		// These filters are a hack. Used to add/remove the permalink/title filters so they do not not affect the nav menu.
		add_filter( 'wp_nav_menu_args', array( __CLASS__, 'startNav' ) );
		add_filter( 'wp_page_menu', array( __CLASS__, 'endNav' ), 10, 2 );
		add_filter( 'wp_nav_menu', array( __CLASS__, 'endNav' ), 10, 2 );

		// Filter the get_parmalink() function to append the Connections related items to the URL.
		add_filter( 'page_link', array( __CLASS__, 'filterPermalink' ), 10, 3 );

		// Filter the meta title to reflect the current Connections filter.
		add_filter( 'wp_title', array( __CLASS__, 'filterMetaTitle' ), 10, 3 );

		// Filter the page title to reflect the current Connection filter.
		add_filter( 'the_title', array( __CLASS__, 'filterPostTitle' ), 10, 2 );

		// remove_action( 'wp_head', 'index_rel_link'); // Removes the index link
		// remove_action( 'wp_head', 'parent_post_rel_link'); // Removes the prev link
		// remove_action( 'wp_head', 'start_post_rel_link'); // Removes the start link
		// remove_action( 'wp_head', 'adjacent_posts_rel_link'); // Removes the relational links for the posts adjacent to the current post.
		// remove_action( 'wp_head', 'rel_canonical'); // Remove the canonical link
	}

	/**
	 * This can be called to turn on/off the filters applied in cnSEO.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (bool) $do [optional]
	 * @return (void)
	 */
	public static function doFilterPermalink( $do = TRUE ) {

		self::$filterPermalink = $do;
	}

	/**
	 * Add the Connections URL segments to the page permalink.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses get_option()
	 * @uses trailingslashit()
	 * @uses get_query_var()
	 * @uses user_trailingslashit()
	 * @uses esc_url()
	 * @param  (string) $link The permalink.
	 * @param  (int) $ID Page ID.
	 * @param  (bool) $sample Is it a sample permalink.
	 * @return (string)
	 */
	public static function filterPermalink( $link, $ID, $sample ) {
		global $wp_rewrite, $post/*, $connections*/;

		// Only filter the the permalink for the current post/page being viewed otherwise the nex/prev relational links are filtered too, which we don't want.
		// Same for the links in the nav, do not change those.
		if ( ( isset( $post->ID ) &&  $post->ID != $ID ) || ! self::$filterPermalink ) return $link;


		if ( $wp_rewrite->using_permalinks() ) {

			// Get the settings for the base of each data type to be used in the URL.
			$base = get_option( 'connections_permalink' );

			$link = trailingslashit( $link );

			if ( get_query_var( 'cn-cat-slug' ) )
				$link = esc_url( trailingslashit( $link . $base['category_base'] . '/' . get_query_var( 'cn-cat-slug' ) ) );


			if ( get_query_var( 'cn-country' ) )
				$link = esc_url( trailingslashit( $link . $base['country_base'] . '/' . urlencode( get_query_var( 'cn-country' ) ) ) );


			if ( get_query_var( 'cn-region' ) )
				$link = esc_url( trailingslashit( $link . $base['region_base'] . '/' . urlencode( get_query_var( 'cn-region' ) ) ) );


			if ( get_query_var( 'cn-locality' ) )
				$link = esc_url( trailingslashit( $link . $base['locality_base'] . '/' . urlencode( get_query_var( 'cn-locality' ) ) ) );


			if ( get_query_var( 'cn-postal-code' ) )
				$link = esc_url( trailingslashit( $link . $base['postal_code_base'] . '/' . urlencode( get_query_var( 'cn-postal-code' ) ) ) );


			if ( get_query_var( 'cn-organization' ) )
				$link = esc_url( trailingslashit( $link . $base['organization_base'] . '/' . urlencode( get_query_var( 'cn-organization' ) ) ) );


			if ( get_query_var( 'cn-department' ) )
				$link = esc_url( trailingslashit( $link . $base['department_base'] . '/' . urlencode( get_query_var( 'cn-department' ) ) ) );


			if ( get_query_var( 'cn-entry-slug' ) )
				$link = esc_url( trailingslashit( $link . $base['name_base'] . '/' . urlencode( get_query_var( 'cn-entry-slug' ) ) ) );


			$link = user_trailingslashit( $link, 'page' );

		} else {

			if ( get_query_var( 'cn-cat-slug' ) )
				$link = esc_url( add_query_arg( array( 'cn-cat-slug' => get_query_var( 'cn-cat-slug' ) ) , $link ) );


			if ( get_query_var( 'cn-country' ) )
				$link = esc_url( add_query_arg( array( 'cn-country' => get_query_var( 'cn-country' ) ) , $link ) );


			if ( get_query_var( 'cn-region' ) )
				$link = esc_url( add_query_arg( array( 'cn-region' => get_query_var( 'cn-region' ) ) , $link ) );


			if ( get_query_var( 'cn-locality' ) )
				$link = esc_url( add_query_arg( array( 'cn-locality' => get_query_var( 'cn-locality' ) ) , $link ) );


			if ( get_query_var( 'cn-postal-code' ) )
				$link = esc_url( add_query_arg( array( 'cn-postal-code' => get_query_var( 'cn-postal-code' ) ) , $link ) );


			if ( get_query_var( 'cn-organization' ) )
				$link = esc_url( add_query_arg( array( 'cn-organization' => get_query_var( 'cn-organization' ) ) , $link ) );


			if ( get_query_var( 'cn-department' ) )
				$link = esc_url( add_query_arg( array( 'cn-department' => get_query_var( 'cn-department' ) ) , $link ) );


			if ( get_query_var( 'cn-entry-slug' ) )
				$link = esc_url( add_query_arg( array( 'cn-entry-slug' => get_query_var( 'cn-entry-slug' ) ) , $link ) );

		}

		return $link;
	}

	/**
	 * Add the the current Connections directory location/query to the page meta title.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses get_query_var()
	 * @param  (string) $title The browser tab/window title.
	 * @param  (string) $sep [optional] The title separator.
	 * @param  (string) $seplocation [optional] The separator location.
	 * @return (string)
	 */
	public static function filterMetaTitle( $title, $sep = '&raquo;', $seplocation = '' ) {
		global $connections;

		// Whether or not to filter the page meta title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'connections_seo_meta', 'page_title' ) ) return $title;

		// Coerce $title to be an array.
		$title = (array) $title;

		if ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			array_unshift( $title, $category->getName() );
		}

		if ( get_query_var( 'cn-cat' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'id', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			array_unshift( $title, $category->getName() );
		}

		if ( get_query_var( 'cn-country' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-country' ) ) );

		if ( get_query_var( 'cn-postal-code' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-postal-code' ) ) );

		if ( get_query_var( 'cn-region' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-region' ) ) );

		if ( get_query_var( 'cn-locality' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-locality' ) ) );

		if ( get_query_var( 'cn-organization' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-organization' ) ) );

		if ( get_query_var( 'cn-department' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-department' ) ) );

		if ( get_query_var( 'cn-entry-slug' ) ) {

			$result = $connections->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			$entry = new cnEntry( $result[0] );

			array_unshift( $title, $entry->getName() );
		}

		return implode( " $sep ", $title );
	}

	/**
	 * Add the the current Connections directory location/query to the page title.
	 *
	 * NOTE: $id really isn't optionaly, some plugins fail to use the `the_title` filter correctly,
	 * ie. "Display Posts Shortcode", causes Connections to crash an burn if not supplied.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses get_query_var()
	 * @param  (string) $title The browser tab/window title.
	 * @param  (int) $id [optional] The page/post ID.
	 * @return (string)
	 */
	public static function filterPostTitle( $title, $id = 0 ) {
		global $post, $connections;

		// Whether or not to filter the page title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'connections_seo', 'page_title' ) ) return $title;

		if ( $post->ID != $id || ! self::$filterPermalink ) return $title;

		// Coerce $title to be an array.
		$title = (array) $title;

		if ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			array_unshift( $title, $category->getName() );
		}

		if ( get_query_var( 'cn-cat' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'id', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			array_unshift( $title, $category->getName() );
		}

		if ( get_query_var( 'cn-country' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-country' ) ) );

		if ( get_query_var( 'cn-postal-code' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-postal-code' ) ) );

		if ( get_query_var( 'cn-region' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-region' ) ) );

		if ( get_query_var( 'cn-locality' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-locality' ) ) );

		if ( get_query_var( 'cn-organization' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-organization' ) ) );

		if ( get_query_var( 'cn-department' ) )
			array_unshift( $title, urldecode( get_query_var( 'cn-department' ) ) );

		if ( get_query_var( 'cn-entry-slug' ) ) {

			$result = $connections->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			$entry = new cnEntry( $result[0] );

			array_unshift( $title, $entry->getName() );
		}

		return implode( ' &raquo; ', $title );
	}

	/**
	 * Add the the current Connections category description or entry bio excerpt  as the page meta description.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses get_query_var()
	 * @uses esc_attr()
	 * @uses strip_shortcodes()
	 * @return (string) | (void)
	 */
	public static function metaDesc() {
		global $connections;

		// Whether or not to filter the page title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'connections_seo_meta', 'page_desc' ) ) return;

		if ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			$desciption = $category->getExcerpt( array( 'length' => 160 ) );
		}

		if ( get_query_var( 'cn-cat' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'id', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			$desciption = $category->getExcerpt( array( 'length' => 160 ) );
		}

		if ( get_query_var( 'cn-entry-slug' ) ) {

			$result = $connections->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			$entry = new cnEntry( $result[0] );

			$desciption = $entry->getExcerpt( array( 'length' => 160 ) );
		}

		if ( empty( $desciption ) ) return;

		echo '<meta name="description" content="' . esc_attr( trim( strip_shortcodes( strip_tags( stripslashes( $desciption ) ) ) ) ) . '"/>' . "\n";

	}

	/**
	 * This method is run during the wp_nav_menu_args filter.
	 * The only purpose is to set self::doFilterPermalink to FALSE.
	 * This is set to ensure the permalinks and titles in the nav are
	 * not run thru the cnSEO filters.
	 *
	 * @access private
	 * @since 0.7.8
	 * @param  (array) $args The arguments passed to wp_nav_menu().
	 * @return (array)
	 */
	public static function startNav( $args ) {

		self::doFilterPermalink( FALSE );

		return $args;
	}

	/**
	 * This method is run during the wp_page_menu & wp_nav_menu filters.
	 * The only purpose is to set self::doFilterPermalink to TRUE.
	 *
	 * @access private
	 * @since 0.7.8
	 * @see self::startNav()
	 * @see self::doFilterPermalink()
	 * @param  (string) $menu
	 * @param  (array) $args $args The arguments passed to wp_nav_menu().
	 * @return (string)
	 */
	public static function endNav( $menu, $args ) {

		self::doFilterPermalink();

		return $menu;
	}

}