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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Entry\Functions as Entry_Helper;

/**
 * Class cnSEO
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnSEO {

	/**
	 * Whether to filter the permalink.
	 *
	 * @since 0.7.8
	 *
	 * @var boolean
	 */
	private static $filterPermalink = true;

	/**
	 * Register the default template actions.
	 *
	 * @since 0.7.8
	 */
	public static function hooks() {

		// Bail if in admin.
		if ( is_admin() ) {

			return;
		}

		add_action( 'wp', array( __CLASS__, 'maybeAddFilters' ), 0 );
	}

	/**
	 * Callback for the `wp_head` action.
	 *
	 * Maybe add the filters.
	 *
	 * @internal
	 * @since 10.2
	 */
	public static function maybeAddFilters() {

		$object = get_queried_object();

		if ( ! $object instanceof WP_Post ) {

			return;
		}

		if ( has_shortcode( $object->post_content, 'connections' ) ||
			 has_block( 'connections-directory/shortcode-connections', $object )
		) {

			// Update the post dates to reflect the dates of the entry.
			add_filter( 'the_posts', array( __CLASS__, 'postDates' ), 10, 2 );

			/*
			 * Add the page meta description.
			 * @since 9.12 Change priority to `1` to match core WP _wp_render_title_tag() priority so the meta description
			 * renders close to the page title tag.
			 */
			add_action( 'wp_head', array( __CLASS__, 'metaDesc' ), 1 );

			// These filters are a hack. Used to add/remove the permalink/title filters, so they do not affect the nav menu.
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
			 * @link https://connections-pro.com/support/topic/the-link-of-the-address-book-doesnt-work-after-the-choice-a-category/
			 */
			// add_filter( 'page_link', array( __CLASS__, 'filterPermalink' ), 10, 3 );
			add_filter( 'get_canonical_url', array( __CLASS__, 'transformCanonical' ), 10, 2 );

			/*
			 * Issue #526625 Display Configuration etc. submitted by Richard M.
			 * Short circuit this filter to prevent both Divi and Rank Math from processing post title.
			 *
			 * Will break the Divi SEO settings and custom page title options.
			 * When I say break, I mean they will not have an effect.
			 *
			 * Disables the Rank Math page title features such as forced capitalization and custom per page/post meta titles.
			 *
			 * @todo Enable filter, but ensure it only runs on a Connections Entry detail page.
			 */
			// add_action(
			// 	'init',
			// 	function() {
			// 		add_filter( 'pre_get_document_title', '__return_empty_string', 16 );
			// 	}
			// );

			// Filter the meta title to reflect the current Connections filter.
			// Uses priority 20 because WordPress SEO by Yoast uses priority 15. This filter should run after.
			add_filter( 'wp_title', array( __CLASS__, 'filterMetaTitle' ), 20, 3 );

			// Filter the page title to reflect the current Connection filter.
			add_filter( 'the_title', array( __CLASS__, 'filterPostTitle' ), 10, 2 );
			add_filter( 'document_title_parts', array( __CLASS__, 'filterDocumentTitle' ), 10 );

			// Remove the page/post specific comments feed w/ registered query vars.
			add_action( 'wp_head', array( __CLASS__, 'removeCommentFeed' ), -1 );

			// Trigger 404 if entry is not found.
			// add_action( 'pre_handle_404', array( __CLASS__, 'trigger404_noShortcode' ) );
			add_action( 'pre_handle_404', array( __CLASS__, 'trigger404_entryNotFound' ) );

			// remove_action( 'wp_head', 'index_rel_link'); // Removes the index link
			// remove_action( 'wp_head', 'parent_post_rel_link'); // Removes the prev link
			// remove_action( 'wp_head', 'start_post_rel_link'); // Removes the start link
			// remove_action( 'wp_head', 'adjacent_posts_rel_link'); // Removes the relational links for the posts adjacent to the current post.
			// remove_action( 'wp_head', 'rel_canonical'); // Remove the canonical link
			// remove_action( 'wp_head', 'feed_links', 2 ); // Remove the feed links.
			// remove_action( 'wp_head', 'feed_links_extra', 3 ); // Remove page/post specific comments feed.

			// Prevent the "View All" page from being indexed.
			// add_action(
			// 	'wp_head',
			// 	static function() {
			//
			// 		$url = home_url( \Connections_Directory\Request\Server_Request_URI::input()->value() );
			//
			// 		if ( false !== strpos( $url, '/view/all/' ) ) {
			// 			echo '<meta name="robots" content="noindex, nofollow">';
			// 		}
			// 	}
			// );
		}
	}

	/**
	 * If shortcode is not found in post content and a registered query var is detected, trigger a 404.
	 *
	 * @see WP::handle_404()
	 *
	 * @internal
	 * @since 8.18
	 */
	public static function trigger404_noShortcode() {

		global $wp_query;

		// Get the queried object.
		$post = get_queried_object();

		// Ensure it is an instance of WP_Post.
		if ( $post instanceof WP_Post ) {

			// Grab the array containing all query vars registered by Connections.
			$registeredQueryVars = cnRewrite::queryVars( array() );

			// Remove the cn-image query vars.
			$wpQueryVars = array_diff_key( (array) $wp_query->query_vars, array_flip( array( 'src', 'w', 'h', 'q', 'a', 'zc', 'f', 's', 'o', 'cc', 'ct' ) ) );

			// If the shortcode is not found and a Connections query var is detected, return 404.
			if ( false === cnShortcode::find( 'connections', $post->post_content ) &&
				 true === (bool) array_intersect( $registeredQueryVars, array_keys( (array) $wpQueryVars ) )
			) {

				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
			}

		}

		return false;
	}

	/**
	 * If querying a single entry by slug, and it is not found, trigger a 404.
	 *
	 * @todo This should be expanded to all supported core query vars.
	 *
	 * @see WP::handle_404()
	 *
	 * @internal
	 * @since 8.5.26
	 */
	public static function trigger404_entryNotFound() {

		global $wp_query;

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			$atts = array(
				'slug' => urldecode( cnQuery::getVar( 'cn-entry-slug' ) ),
			);

			/**
			 * Allow plugins to filter the cnRetrieve::entries() param array.
			 *
			 * @since 8.5.28
			 *
			 * @param array $atts
			 */
			$atts = apply_filters( 'cn_pre_handle_404_retrieve_atts', $atts );

			$result = $instance->retrieve->entries( $atts );

			if ( empty( $result ) ) {

				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
			}
		}

		return false;
	}

	/**
	 * This can be called to turn on/off the filters applied in cnSEO.
	 *
	 * @since 0.7.8
	 *
	 * @param bool $do
	 */
	public static function doFilterPermalink( $do = true ) {

		self::$filterPermalink = $do;
	}

	/**
	 * Callback for the `get_canonical_url` filter.
	 *
	 * @since 9.17
	 *
	 * @param string  $url  The post's canonical URL.
	 * @param WP_Post $post Post object.
	 *
	 * @return string
	 */
	public static function transformCanonical( $url, $post ) {

		return static::maybeTransformPermalink( $url, $post->ID );
	}

	/**
	 * Add the Connections URL segments to the page permalink.
	 *
	 * @since 0.7.8
	 *
	 * @param string $link   The permalink.
	 * @param int    $ID     Page ID.
	 * @param bool   $sample Is it a sample permalink.
	 *
	 * @return string
	 */
	public static function filterPermalink( $link, $ID, /** @noinspection PhpUnusedParameterInspection */ $sample ) {

		global $post;

		// Only filter the permalink for the current post/page being viewed otherwise the nex/prev relational links are filtered too, which we don't want.
		// Same for the links in the nav, do not change those.
		if ( ( isset( $post->ID ) && $post->ID != $ID ) || ! self::$filterPermalink ) {
			return $link;
		}

		return static::maybeTransformPermalink( $link, $ID );
	}

	/**
	 * @since 9.12
	 *
	 * @param string $link
	 * @param int    $pageID
	 *
	 * @return string
	 */
	public static function maybeTransformPermalink( $link, $pageID ) {

		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {

			// Get the settings for the base of each data type to be used in the URL.
			$base = get_option( 'connections_permalink' );

			$link = trailingslashit( $link );

			if ( cnQuery::getVar( 'cn-cat-slug' ) ) {
				$link = esc_url( trailingslashit( $link . $base['category_base'] . '/' . cnQuery::getVar( 'cn-cat-slug' ) ) );
			}

			if ( cnQuery::getVar( 'cn-country' ) ) {
				$link = esc_url( trailingslashit( $link . $base['country_base'] . '/' . urlencode( urldecode( cnQuery::getVar( 'cn-country' ) ) ) ) );
			}

			if ( cnQuery::getVar( 'cn-region' ) ) {
				$link = esc_url( trailingslashit( $link . $base['region_base'] . '/' . urlencode( urldecode( cnQuery::getVar( 'cn-region' ) ) ) ) );
			}

			if ( cnQuery::getVar( 'cn-locality' ) ) {
				$link = esc_url( trailingslashit( $link . $base['locality_base'] . '/' . urlencode( urldecode( cnQuery::getVar( 'cn-locality' ) ) ) ) );
			}

			if ( cnQuery::getVar( 'cn-postal-code' ) ) {
				$link = esc_url( trailingslashit( $link . $base['postal_code_base'] . '/' . urlencode( urldecode( cnQuery::getVar( 'cn-postal-code' ) ) ) ) );
			}

			if ( cnQuery::getVar( 'cn-organization' ) ) {
				$link = esc_url( trailingslashit( $link . $base['organization_base'] . '/' . urlencode( urldecode( cnQuery::getVar( 'cn-organization' ) ) ) ) );
			}

			if ( cnQuery::getVar( 'cn-department' ) ) {
				$link = esc_url( trailingslashit( $link . $base['department_base'] . '/' . urlencode( urldecode( cnQuery::getVar( 'cn-department' ) ) ) ) );
			}

			if ( cnQuery::getVar( 'cn-entry-slug' ) ) {
				$link = esc_url( trailingslashit( $link . $base['name_base'] . '/' . urlencode( urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) ) ) );
			}

			if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == $pageID ) {

				$link = trailingslashit( $link );

			} else {

				if ( $wp_rewrite->use_trailing_slashes ) {

					$link = trailingslashit( $link );

				} else {

					$link = untrailingslashit( $link );
				}
			}

		} else {

			if ( cnQuery::getVar( 'cn-cat-slug' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-cat-slug' => cnQuery::getVar( 'cn-cat-slug' ) ), $link ) );
			}

			if ( cnQuery::getVar( 'cn-country' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-country' => urldecode( cnQuery::getVar( 'cn-country' ) ) ), $link ) );
			}

			if ( cnQuery::getVar( 'cn-region' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-region' => urldecode( cnQuery::getVar( 'cn-region' ) ) ), $link ) );
			}

			if ( cnQuery::getVar( 'cn-locality' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-locality' => urldecode( cnQuery::getVar( 'cn-locality' ) ) ), $link ) );
			}

			if ( cnQuery::getVar( 'cn-postal-code' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-postal-code' => urldecode( cnQuery::getVar( 'cn-postal-code' ) ) ), $link ) );
			}

			if ( cnQuery::getVar( 'cn-organization' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-organization' => urldecode( cnQuery::getVar( 'cn-organization' ) ) ), $link ) );
			}

			if ( cnQuery::getVar( 'cn-department' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-department' => urldecode( cnQuery::getVar( 'cn-department' ) ) ), $link ) );
			}

			if ( cnQuery::getVar( 'cn-entry-slug' ) ) {
				$link = esc_url( add_query_arg( array( 'cn-entry-slug' => urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) ), $link ) );
			}

		}

		return $link;
	}

	/**
	 * Callback for the `the_posts` filter.
	 *
	 * Update the post date and post modified date to reflect the current entry being viewed.
	 *
	 * @internal
	 * @since 8.1
	 *
	 * @param array    $posts    An array of WP_Post objects.
	 * @param WP_Query $wp_query A reference to the WP_Query object.
	 *
	 * @return array
	 */
	public static function postDates( $posts, $wp_query ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( $wp_query->is_main_query() && cnQuery::getVar( 'cn-entry-slug' ) ) {

			$result = $instance->retrieve->entries( array( 'slug' => urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) ) );

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
	 * Add the current Connections directory location/query to the page meta title.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param  string $title       The browser tab/window title.
	 * @param  string $separator   [optional] The title separator.
	 * @param  string $seplocation [optional] The separator location.
	 *
	 * @return string
	 */
	public static function filterMetaTitle( $title, $separator = '&raquo;', $seplocation = '' ) {

		return self::metaTitle( $title, $separator, $seplocation );
	}

	/**
	 * Callback for the `document_title_parts` filter.
	 *
	 * @internal
	 * @since 8.5.29
	 *
	 * @param array $parts {
	 *     The document title parts.
	 *
	 *     @type string $title   Title of the viewed page.
	 *     @type string $page    Optional. Page number if paginated.
	 *     @type string $tagline Optional. Site description when on home page.
	 *     @type string $site    Optional. Site title when not on home page.
	 * }
	 *
	 * @return array
	 */
	public static function filterDocumentTitle( $parts ) {

		$parts['title'] = self::metaTitle( $parts['title'] );

		return $parts;
	}

	/**
	 * @since 9.12
	 *
	 * @param string $title
	 *
	 * @return array
	 */
	public static function maybeTransformTitle( $title ) {

		$pieces = array( $title );

		if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/', cnQuery::getVar( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) {

				$categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];
			}

			$term = cnTerm::getBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			$pieces = array_merge( array( 'term-category-name' => $category->getName() ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-cat' ) ) {

			if ( is_array( cnQuery::getVar( 'cn-cat' ) ) ) {

				return $pieces;
			}

			$categoryID = cnQuery::getVar( 'cn-cat' );

			$term = cnTerm::getBy( 'id', $categoryID, 'category' );

			$category = new cnCategory( $term );

			$pieces = array_merge( array( 'term-category-name' => $category->getName() ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-country' ) ) {

			$pieces = array_merge( array( 'country' => urldecode( cnQuery::getVar( 'cn-country' ) ) ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-postal-code' ) ) {

			$pieces = array_merge( array( 'postal-code' => urldecode( cnQuery::getVar( 'cn-postal-code' ) ) ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-region' ) ) {

			$pieces = array_merge( array( 'region' => urldecode( cnQuery::getVar( 'cn-region' ) ) ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-locality' ) ) {

			$pieces = array_merge( array( 'locality' => urldecode( cnQuery::getVar( 'cn-locality' ) ) ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-organization' ) ) {

			$pieces = array_merge( array( 'organization' => urldecode( cnQuery::getVar( 'cn-organization' ) ) ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-department' ) ) {

			$pieces = array_merge( array( 'department' => urldecode( cnQuery::getVar( 'cn-department' ) ) ), $pieces );
		}

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();
			$result   = $instance->retrieve->entries( array( 'slug' => urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) ) );

			// Make sure an entry is returned and if not, return $title unaltered.
			if ( ! empty( $result ) ) {

				$entry  = new cnEntry( $result[0] );
				$pieces = array_merge( array( 'name' => $entry->getName() ), $pieces );
			}

		}

		return $pieces;
	}

	/**
	 * Add the current Connections directory location/query to the page meta title.
	 *
	 * @since 8.5.29
	 *
	 * @param string $title       The browser tab/window title.
	 * @param string $separator
	 * @param string $seplocation
	 *
	 * @return string
	 */
	public static function metaTitle( $title, $separator = '&raquo;', $seplocation = '' ) {

		// Whether to filter the page meta title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'seo_meta', 'page_title' ) ) {

			return $title;
		}

		$original = $title;
		$pieces   = static::maybeTransformTitle( $title );

		/**
		 * Filter the parts of the page title.
		 *
		 * @since 8.5.15
		 *
		 * @param string $title       The page title.
		 * @param array  $pieces      The pieces of the title.
		 * @param string $original    The original title. May have been altered by other filters hooked into the `the_title` filter.
		 * @param string $separator   The title separator.
		 * @param string $seplocation Location of the separator (left or right).
		 */
		$title = apply_filters( 'cn_meta_title', implode( " $separator ", $pieces ), $pieces, $original, $separator, $seplocation );

		$separator = html_entity_decode( $separator );

		return trim( $title, " \t\n\r\0\x0B{$separator}" );
	}

	/**
	 * Add the current Connections directory location/query to the page title.
	 *
	 * NOTE: $id really isn't optional, some plugins fail to use the `the_title` filter correctly,
	 * i.e. "Display Posts Shortcode", causes Connections to crash and burn if not supplied.
	 *
	 * @since 0.7.8
	 *
	 * @param string $title The browser tab/window title.
	 * @param int    $id    The page/post ID.
	 *
	 * @return string
	 */
	public static function filterPostTitle( $title, $id = 0 ) {

		global $wp_query, $post;

		if ( ! is_object( $post ) ||
			 ( ! isset( $wp_query->post ) || ! isset( $wp_query->post->ID ) || $wp_query->post->ID != $id ) ||
			 ! self::$filterPermalink
		) {

			return $title;
		}

		// Whether to filter the page title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'seo', 'page_title' ) ) {

			return $title;
		}

		/**
		 * Filter to allow the page title separator to be changed.
		 *
		 * @since 8.5.15
		 *
		 * @param string $separator The title separator.
		 */
		$separator = apply_filters( 'cn_page_title_separator', '&raquo;' );

		$original = $title;
		$pieces   = static::maybeTransformTitle( $title );
		$pieces   = array_filter( $pieces, '\Connections_Directory\Utility\_::notEmpty' );

		/**
		 * Filter the parts of the page title.
		 *
		 * @since 8.5.15
		 *
		 * @param string $title     The page title.
		 * @param array  $pieces    The pieces of the title.
		 * @param string $separator The title separator.
		 * @param string $original  The original title. May have been altered by other filters hooked into the `the_title` filter.
		 * @param int    $id        The post ID.
		 */
		$title = apply_filters( 'cn_page_title', implode( " $separator ", $pieces ), $pieces, $separator, $original, $id );

		return $title;
	}

	/**
	 * @since 9.12
	 *
	 * @return string
	 */
	public static function getMetaDescription() {

		$context     = '';
		$description = '';
		$object      = null;

		if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/', cnQuery::getVar( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) {

				$categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];
			}

			$term = cnTerm::getBy( 'slug', $categorySlug, 'category' );

			$object  = new cnCategory( $term );
			$context = 'taxonomy';

			$description = $object->getExcerpt( array( 'length' => 160 ) );
		}

		if ( cnQuery::getVar( 'cn-cat' ) ) {

			if ( is_array( cnQuery::getVar( 'cn-cat' ) ) ) {
				return '';
			}

			$categoryID = cnQuery::getVar( 'cn-cat' );

			$term = cnTerm::getBy( 'id', $categoryID, 'category' );

			$object  = new cnCategory( $term );
			$context = 'taxonomy';

			$description = $object->getExcerpt( array( 'length' => 160 ) );
		}

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();
			$result   = $instance->retrieve->entry( urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) );

			// Make sure an entry is returned and then echo the meta desc.
			if ( false !== $result ) {

				$object  = new cnEntry( $result );
				$context = 'entry';

				// Get the excerpt field which will truncate the bio field if empty.
				$description = $object->getExcerpt( array( 'length' => 160 ) );

				// If both the excerpt and bio field are empty, get the address and format it as a string to be used as the description.
				if ( 0 === strlen( $description ) ) {

					if ( 'organization' === $object->getEntryType() ) {

						$address = Entry_Helper::getAddress( $object );

						if ( $address instanceof cnAddress ) {

							$street = array_filter(
								array(
									'line_1' => $address->getLineOne(),
									'line_2' => $address->getLineTwo(),
									'line_3' => $address->getLineThree(),
									'line_4' => $address->getLineFour(),
								)
							);

							$region = array_filter(
								array(
									'locality' => $address->getLocality(),
									'region'   => $address->getRegion(),
								)
							);

							$pieces = array_filter(
								array(
									'street'  => implode( ', ', $street ),
									'region'  => implode( ' ', array( implode( ', ', $region ), $address->getPostalCode() ) ),
									'country' => $address->getCountry(),
								)
							);

							// $description = As_String::format( $address );
							$description = implode( ' | ', $pieces );
						}

					} elseif ( 'individual' === $object->getEntryType() ) {

						$pieces = array_filter(
							array(
								'title'        => $object->getTitle(),
								'department'   => $object->getDepartment(),
								'organization' => $object->getOrganization(),
							)
						);

						$description = implode( ' | ', $pieces );
					}

				}
			}
		}

		if ( 0 < strlen( $description ) ) {

			$description = wp_html_excerpt( $description, 156 );

			if ( 156 <= strlen( utf8_decode( $description ) ) ) {

				// Trim the auto-generated string to a word boundary.
				$description = substr( $description, 0, strrpos( $description, ' ' ) );
			}
		}

		/**
		 * @since 9.13
		 *
		 * @param string $description The page meta description.
		 * @param string $context     The current context, or view, of the directory.
		 * @param cnEntry|cnCategory  The current object being viewed.
		 */
		return apply_filters( 'Connections_Directory/SEO/Description', $description, $context, $object );
	}

	/**
	 * Add the current Connections category description or entry bio excerpt as the page meta description.
	 *
	 * @internal
	 * @since 0.7.8
	 */
	public static function metaDesc() {

		// Whether to filter the page title with the current directory location.
		if ( ! cnSettingsAPI::get( 'connections', 'seo_meta', 'page_desc' ) ) {

			return;
		}

		$description = self::getMetaDescription();

		if ( 0 === strlen( $description ) ) {

			return;
		}

		echo '<meta name="description" content="' . esc_attr( self::getMetaDescription() ) . '" />' . "\n";
	}

	/**
	 * Get the current Entry image meta, by type.
	 *
	 * @since 9.13
	 *
	 * @return array|false
	 */
	public static function getImageMeta() {

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();
			$result   = $instance->retrieve->entry( urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) );

			// Make sure an entry is returned and then echo the meta desc.
			if ( false !== $result ) {

				$entry           = new cnEntry( $result );
				$imageProperties = apply_filters(
					'Connections_Directory/SEO/Image_Properties',
					array(
						'type'      => 'organization' === $entry->getEntryType() ? 'logo' : 'photo',
						'size'      => 'custom',
						'width'     => 1200,
						'height'    => 800,
						'crop_mode' => 3,
					),
					$entry
				);

				$meta = $entry->getImageMeta( $imageProperties );

				if ( is_array( $meta ) ) {

					return $meta;
				}
			}
		}

		return false;
	}

	/**
	 * This method is run during the wp_nav_menu_args filter.
	 * The only purpose is to set self::doFilterPermalink to FALSE.
	 * This is set to ensure the permalinks and titles in the nav are
	 * not run through the cnSEO filters.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param array $args The arguments passed to wp_nav_menu().
	 *
	 * @return array
	 */
	public static function startNav( $args ) {

		self::doFilterPermalink( false );

		return $args;
	}

	/**
	 * This method is run during the wp_page_menu & wp_nav_menu filters.
	 * The only purpose is to set self::doFilterPermalink to TRUE.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @see self::startNav()
	 * @see self::doFilterPermalink()
	 *
	 * @param string $menu
	 * @param array  $args The arguments passed to wp_nav_menu().
	 *
	 * @return string
	 */
	public static function endNav( $menu, /** @noinspection PhpUnusedParameterInspection */ $args ) {

		self::doFilterPermalink();

		return $menu;
	}

	/**
	 * Remove the comment feeds from the directory sub-pages.
	 * This is to prevent search engine crawl errors / 404s.
	 *
	 * @internal
	 * @since 0.7.9
	 *
	 * @global $wp_query
	 */
	public static function removeCommentFeed() {
		global $wp_query;

		$registeredQueryVars = cnRewrite::queryVars( array() );

		// var_dump($registeredQueryVars);var_dump($wp_query->query_vars);
		// var_dump( array_intersect( $registeredQueryVars, $wp_query->query_vars ) );
		// var_dump( array_keys( $wp_query->query_vars ) );

		if ( (bool) array_intersect( $registeredQueryVars, array_keys( (array) $wp_query->query_vars ) ) ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}
	}
}
