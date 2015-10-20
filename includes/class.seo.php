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

/**
 * Class cnSEO
 */
class cnSEO {

	/**
	 * Whether or not to filter the permalink.
	 *
	 * @access private
	 * @since  0.7.8
	 * @static
	 *
	 * @var boolean
	 */
	private static $filterPermalink = TRUE;

	/**
	 * Register the default template actions.
	 *
	 * @access private
	 * @since  0.7.8
	 * @static
	 *
	 * @uses   add_filter()
	 *
	 * @return void
	 */
	public static function init() {

		// Update the post dates to reflect the dates of the entry.
		add_action( 'the_posts', array( __CLASS__, 'postDates'), 10, 2 );

		// Add the page meta description.
		add_action( 'wp_head', array( __CLASS__, 'metaDesc' ), 10 );

		// These filters are a hack. Used to add/remove the permalink/title filters so they do not not affect the nav menu.
		add_filter( 'wp_nav_menu_args', array( __CLASS__, 'startNav' ) );
		add_filter( 'wp_page_menu', array( __CLASS__, 'endNav' ), 10, 2 );
		add_filter( 'wp_nav_menu', array( __CLASS__, 'endNav' ), 10, 2 );

		add_filter( 'wp_list_pages_excludes', array( __CLASS__, 'startNav' ) );
		add_filter( 'wp_list_pages', array( __CLASS__, 'endNav' ), 10, 2 );

		// This could cause problems since the filter is not re-enabled.
		// add_filter( 'widget_posts_args', array( __CLASS__, 'startNav' ) );

		// Filter the get_permalink() function to append the Connections related items to the URL.
		/**
		 * @todo Perhaps this filter should only be applied while in the page head so on the meta canonical link if affected.
		 * That would eliminate issues like this:
		 * @link http://connections-pro.com/support/topic/the-link-of-the-address-book-doesnt-work-after-the-choice-a-category/
		 */
		add_filter( 'page_link', array( __CLASS__, 'filterPermalink' ), 10, 3 );

		// Filter the meta title to reflect the current Connections filter.
		// User priority 20 because WordPress SEP by Yoast uses priority 15. This filter should run after.
		add_filter( 'wp_title', array( __CLASS__, 'filterMetaTitle' ), 20, 3 );

		// Filter the page title to reflect the current Connection filter.
		add_filter( 'the_title', array( __CLASS__, 'filterPostTitle' ), 10, 2 );

		// Remove the page/post specific comments feed w/ registered query vars.
		add_action( 'wp_head', array( __CLASS__, 'removeCommentFeed' ), -1 );

		// remove_action( 'wp_head', 'index_rel_link'); // Removes the index link
		// remove_action( 'wp_head', 'parent_post_rel_link'); // Removes the prev link
		// remove_action( 'wp_head', 'start_post_rel_link'); // Removes the start link
		// remove_action( 'wp_head', 'adjacent_posts_rel_link'); // Removes the relational links for the posts adjacent to the current post.
		// remove_action( 'wp_head', 'rel_canonical'); // Remove the canonical link
		// remove_action( 'wp_head', 'feed_links', 2 ); // Remove the feed links.
		// remove_action( 'wp_head', 'feed_links_extra', 3 ); // Remove page/post specific comments feed.
	}

	/**
	 * This can be called to turn on/off the filters applied in cnSEO.
	 *
	 * @access public
	 * @since  0.7.8
	 * @static
	 *
	 * @param  bool $do
	 *
	 * @return void
	 */
	public static function doFilterPermalink( $do = TRUE ) {

		self::$filterPermalink = $do;
	}

	/**
	 * Add the Connections URL segments to the page permalink.
	 *
	 * @access private
	 * @since  0.7.8
	 * @static
	 *
	 * @uses   get_option()
	 * @uses   trailingslashit()
	 * @uses   get_query_var()
	 * @uses   user_trailingslashit()
	 * @uses   esc_url()
	 *
	 * @param  string $link   The permalink.
	 * @param  int    $ID     Page ID.
	 * @param  bool   $sample Is it a sample permalink.
	 *
	 * @return string
	 */
	public static function filterPermalink( $link, $ID, /** @noinspection PhpUnusedParameterInspection */ $sample ) {

		/** @var WP_rewrite $wp_rewrite */
		global $wp_rewrite, $post;

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
				$link = esc_url( trailingslashit( $link . $base['country_base'] . '/' . urlencode( urldecode( get_query_var( 'cn-country' ) ) ) ) );


			if ( get_query_var( 'cn-region' ) )
				$link = esc_url( trailingslashit( $link . $base['region_base'] . '/' . urlencode( urldecode( get_query_var( 'cn-region' ) ) ) ) );


			if ( get_query_var( 'cn-locality' ) )
				$link = esc_url( trailingslashit( $link . $base['locality_base'] . '/' . urlencode( urldecode( get_query_var( 'cn-locality' ) ) ) ) );


			if ( get_query_var( 'cn-postal-code' ) )
				$link = esc_url( trailingslashit( $link . $base['postal_code_base'] . '/' . urlencode( urldecode( get_query_var( 'cn-postal-code' ) ) ) ) );


			if ( get_query_var( 'cn-organization' ) )
				$link = esc_url( trailingslashit( $link . $base['organization_base'] . '/' . urlencode( urldecode( get_query_var( 'cn-organization' ) ) ) ) );


			if ( get_query_var( 'cn-department' ) )
				$link = esc_url( trailingslashit( $link . $base['department_base'] . '/' . urlencode( urldecode( get_query_var( 'cn-department' ) ) ) ) );


			if ( get_query_var( 'cn-entry-slug' ) )
				$link = esc_url( trailingslashit( $link . $base['name_base'] . '/' . urlencode( urldecode( get_query_var( 'cn-entry-slug' ) ) ) ) );


			$link = user_trailingslashit( $link, 'page' );

		} else {

			if ( get_query_var( 'cn-cat-slug' ) )
				$link = esc_url( add_query_arg( array( 'cn-cat-slug' => get_query_var( 'cn-cat-slug' ) ) , $link ) );


			if ( get_query_var( 'cn-country' ) )
				$link = esc_url( add_query_arg( array( 'cn-country' => urldecode( get_query_var( 'cn-country' ) ) ), $link ) );


			if ( get_query_var( 'cn-region' ) )
				$link = esc_url( add_query_arg( array( 'cn-region' => urldecode( get_query_var( 'cn-region' ) ) ), $link ) );


			if ( get_query_var( 'cn-locality' ) )
				$link = esc_url( add_query_arg( array( 'cn-locality' => urldecode( get_query_var( 'cn-locality' ) ) ), $link ) );


			if ( get_query_var( 'cn-postal-code' ) )
				$link = esc_url( add_query_arg( array( 'cn-postal-code' => urldecode( get_query_var( 'cn-postal-code' ) ) ), $link ) );


			if ( get_query_var( 'cn-organization' ) )
				$link = esc_url( add_query_arg( array( 'cn-organization' => urldecode( get_query_var( 'cn-organization' ) ) ), $link ) );


			if ( get_query_var( 'cn-department' ) )
				$link = esc_url( add_query_arg( array( 'cn-department' => urldecode( get_query_var( 'cn-department' ) ) ), $link ) );


			if ( get_query_var( 'cn-entry-slug' ) )
				$link = esc_url( add_query_arg( array( 'cn-entry-slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ), $link ) );

		}

		return $link;
	}

	/**
	 * Update the post date and post modified date to reflect the current entry being viewed.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 *
	 * @uses   is_main_query()
	 * @uses   get_query_var()
	 * @uses   get_gmt_from_date()
	 *
	 * @param  array  $posts    An array of WP_Post objects.
	 * @param  object $wp_query A reference to the WP_Query object
	 *
	 * @return array
	 */
	public static function postDates( $posts, $wp_query ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( $wp_query->is_main_query() && get_query_var( 'cn-entry-slug' ) ) {

			$result = $instance->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			// Make sure an entry is returned and if not, return $posts unaltered.
			if ( empty( $result ) ) {

				return $posts;
			}

			$modified = $result[0]->ts;
			$created  = $result[0]->date_added ? date( 'Y-m-d H:i:s', $result[0]->date_added ) : $result[0]->ts;

			if ( isset( $posts[0] ) ) {

				if ( isset( $posts[0]->post_date ) ) {

					$posts[0]->post_date     = $created;
					$posts[0]->post_date_gmt = get_gmt_from_date( $created );
				}

				if ( isset( $posts[0]->post_modified ) ) {

					$posts[0]->post_modified     = $modified;
					$posts[0]->post_modified_gmt = get_gmt_from_date( $modified );
				}
			}
		}

		return $posts;
	}

	/**
	 * Add the the current Connections directory location/query to the page meta title.
	 *
	 * @access private
	 * @since  0.7.8
	 * @static
	 *
	 * @uses   get_query_var()
	 *
	 * @param  string $title The browser tab/window title.
	 * @param  string $sep [optional] The title separator.
	 * @param  string $seplocation [optional] The separator location.
	 *
	 * @return string
	 */
	public static function filterMetaTitle( $title, $sep = '&raquo;', /** @noinspection PhpUnusedParameterInspection */ $seplocation = '' ) {

		// Whether or not to filter the page meta title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'seo_meta', 'page_title' ) ) {

			return $title;
		}

		// Coerce $title to be an array.
		$title = (array) $title;

		if ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = cnTerm::getBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			array_unshift( $title, $category->getName() );
		}

		if ( get_query_var( 'cn-cat' ) ) {

			if ( is_array( get_query_var( 'cn-cat' ) ) ) return implode( '', $title );

			$categoryID = get_query_var( 'cn-cat' );

			$term = cnTerm::getBy( 'id', $categoryID, 'category' );

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

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();
			$result   = $instance->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			// Make sure an entry is returned and if not, return $posts unaltered.
			if ( empty( $result ) ) {

				return implode( " $sep ", $title );
			}

			$entry = new cnEntry( $result[0] );

			array_unshift( $title, $entry->getName() );
		}

		return implode( " $sep ", $title );
	}

	/**
	 * Add the the current Connections directory location/query to the page title.
	 *
	 * NOTE: $id really isn't optional, some plugins fail to use the `the_title` filter correctly,
	 * ie. "Display Posts Shortcode", causes Connections to crash an burn if not supplied.
	 *
	 * @access private
	 * @since  0.7.8
	 * @static
	 *
	 * @uses   get_query_var()
	 *
	 * @param  string $title The browser tab/window title.
	 * @param  int    $id    The page/post ID.
	 *
	 * @return string
	 */
	public static function filterPostTitle( $title, $id = 0 ) {
		global $wp_query, $post;

		// Whether or not to filter the page title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'seo', 'page_title' ) ) {

			return $title;
		}

		if ( ! is_object( $post ) ||
		     ( ! isset( $wp_query->post ) || ! isset( $wp_query->post->ID ) || $wp_query->post->ID != $id ) ||
		     ! self::$filterPermalink ) {

			return $title;
		}

		// Coerce $title to be an array.
		$title = (array) $title;

		if ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = cnTerm::getBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			array_unshift( $title, $category->getName() );
		}

		if ( get_query_var( 'cn-cat' ) ) {

			if ( is_array( get_query_var( 'cn-cat' ) ) ) return implode( '', $title );

			$categoryID = get_query_var( 'cn-cat' );

			$term = cnTerm::getBy( 'id', $categoryID, 'category' );

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

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();
			$result   = $instance->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			// Make sure an entry is returned and if not, return $title unaltered.
			if ( empty( $result ) ) {

				return implode( ' &raquo; ', $title );
			}

			$entry = new cnEntry( $result[0] );

			array_unshift( $title, $entry->getName() );
		}

		return implode( ' &raquo; ', $title );
	}

	/**
	 * Add the the current Connections category description or entry bio excerpt as the page meta description.
	 *
	 * @access private
	 * @since  0.7.8
	 * @static
	 *
	 * @uses   get_query_var()
	 * @uses   esc_attr()
	 * @uses   strip_shortcodes()
	 */
	public static function metaDesc() {

		// Whether or not to filter the page title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'seo_meta', 'page_desc' ) ) {

			return;
		}

		$description = '';

		if ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = cnTerm::getBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			$description = $category->getExcerpt( array( 'length' => 160 ) );
		}

		if ( get_query_var( 'cn-cat' ) ) {

			if ( is_array( get_query_var( 'cn-cat' ) ) ) return;

			$categoryID = get_query_var( 'cn-cat' );

			$term = cnTerm::getBy( 'id', $categoryID, 'category' );

			$category = new cnCategory( $term );

			$description = $category->getExcerpt( array( 'length' => 160 ) );
		}

		if ( get_query_var( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();
			$result   = $instance->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			// Make sure an entry is returned and then echo the meta desc.
			if ( ! empty( $result ) ) {

				$entry = new cnEntry( $result[0] );

				$description = $entry->getExcerpt( array( 'length' => 160 ) );
			}
		}

		if ( 0 == strlen( $description ) ) return;

		echo '<meta name="description" content="' . esc_attr( trim( strip_shortcodes( strip_tags( stripslashes( $description ) ) ) ) ) . '"/>' . "\n";
	}

	/**
	 * This method is run during the wp_nav_menu_args filter.
	 * The only purpose is to set self::doFilterPermalink to FALSE.
	 * This is set to ensure the permalinks and titles in the nav are
	 * not run thru the cnSEO filters.
	 *
	 * @access private
	 * @since  0.7.8
	 * @static
	 *
	 * @param  array $args The arguments passed to wp_nav_menu().
	 *
	 * @return array
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
	 * @since  0.7.8
	 * @static
	 *
	 * @see    self::startNav()
	 * @see    self::doFilterPermalink()
	 *
	 * @param  string $menu
	 * @param  array  $args The arguments passed to wp_nav_menu().
	 *
	 * @return string
	 */
	public static function endNav( $menu, /** @noinspection PhpUnusedParameterInspection */ $args ) {

		self::doFilterPermalink();

		return $menu;
	}

	/**
	 * Remove the comment feeds from th directory sub-pages.
	 * This is to prevent search engine crawl errors / 404s.
	 *
	 * @access private
	 * @since  0.7.9
	 * @static
	 *
	 * @global $wp_query
	 *
	 * @return void
	 */
	public static function removeCommentFeed() {
		global $wp_query;

		$registeredQueryVars = cnRewrite::queryVars( array() );

		// var_dump($registeredQueryVars);var_dump($wp_query->query_vars);
		// var_dump( array_intersect( $registeredQueryVars, $wp_query->query_vars ) );
		// var_dump( array_keys( $wp_query->query_vars ) );

		if ( (bool) array_intersect( $registeredQueryVars, array_keys( (array) $wp_query->query_vars ) ) ) remove_action( 'wp_head', 'feed_links_extra', 3 );
	}

}

// Init the class.
add_action( 'init', array( 'cnSEO' , 'init' ) );
