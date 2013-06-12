<?php

/**
 * Static class for filtering permalinks, changing page/post titles and
 * adding page/page meta descriptions.
 *
 * @package     Connections
 * @subpackage  Template Parts
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnSEO {

	/**
	 * Register the default template actions.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses add_filter()
	 * @return (void)
	 */
	public static function init() {

		add_filter( 'page_link', array( __CLASS__, 'filterPermalink' ), 10, 3 );

		// These two filters are an ugly hack. Used to add/remove the permalink filter so it does not affect the nav menu
		add_filter( 'wp_nav_menu_args', array( __CLASS__, 'donotFilterPermalink' ) );
		add_filter( 'wp_nav_menu', array( __CLASS__, 'donotFilterPermalink' ), 10, 2 );

		// remove_action( 'wp_head', 'index_rel_link'); // Removes the index link
		// remove_action( 'wp_head', 'parent_post_rel_link'); // Removes the prev link
		// remove_action( 'wp_head', 'start_post_rel_link'); // Removes the start link
		// remove_action( 'wp_head', 'adjacent_posts_rel_link'); // Removes the relational links for the posts adjacent to the current post.
		// remove_action( 'wp_head', 'rel_canonical'); // Remove the canonical link
	}

	public static function filterPermalink( $link, $ID, $sample ) {
		global $wp_rewrite, $post, $connections;

		// Only filter the the permalink for the current post/page being viewed otherwise the nex/prev relational links are process too, which we don't want.
		if ( $post->ID != $ID ) return $link;

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		if ( $wp_rewrite->using_permalinks() ) {

			$link = trailingslashit( $link );

			if ( get_query_var( 'cn-cat-slug' ) ) {

				$link = esc_url( trailingslashit( $link . $base['category_base'] . '/' . get_query_var( 'cn-cat-slug' ) ) );

			}

			if ( get_query_var( 'cn-country' ) ) {

				$link = esc_url( trailingslashit( $link . $base['country_base'] . '/' . urlencode( get_query_var( 'cn-country' ) ) ) );

			}

			if ( get_query_var( 'cn-region' ) ) {

				$link = esc_url( trailingslashit( $link . $base['region_base'] . '/' . urlencode( get_query_var( 'cn-region' ) ) ) );

			}

			if ( get_query_var( 'cn-locality' ) ) {

				$link = esc_url( trailingslashit( $link . $base['locality_base'] . '/' . urlencode( get_query_var( 'cn-locality' ) ) ) );

			}

			if ( get_query_var( 'cn-postal-code' ) ) {

				$link = esc_url( trailingslashit( $link . $base['postal_code_base'] . '/' . urlencode( get_query_var( 'cn-postal-code' ) ) ) );

			}

			if ( get_query_var( 'cn-organization' ) ) {

				$link = esc_url( trailingslashit( $link . $base['organization_base'] . '/' . urlencode( get_query_var( 'cn-organization' ) ) ) );

			}

			if ( get_query_var( 'cn-department' ) ) {

				$link = esc_url( trailingslashit( $link . $base['department_base'] . '/' . urlencode( get_query_var( 'cn-department' ) ) ) );

			}

			if ( get_query_var( 'cn-entry-slug' ) ) {

				$link = esc_url( trailingslashit( $link . $base['name_base'] . '/' . urlencode( get_query_var( 'cn-entry-slug' ) ) ) );

			}

			$link = user_trailingslashit( $link, 'page' );

		} else {

			if ( get_query_var( 'cn-cat-slug' ) )
				$link = esc_url( add_query_arg( array( 'cn-cat-slug' => get_query_var( 'cn-cat-slug' ) ) , $link ) );

			if ( get_query_var( 'cn-entry-slug' ) )
				$link = esc_url( add_query_arg( array( 'cn-entry-slug' => get_query_var( 'cn-entry-slug' ) ) , $link ) );

		}

		return $link;
	}

	public static function donotFilterPermalink( $args ) {

		remove_filter( 'page_link', array( 'cnSEO', 'filterPermalink' ) );

		return $args;
	}

	public static function doFilterPermalink( $nav_menu ) {

		add_filter( 'page_link', array( 'cnSEO', 'filterPermalink' ), 10, 3 );

		return $nav_menu;
	}

}