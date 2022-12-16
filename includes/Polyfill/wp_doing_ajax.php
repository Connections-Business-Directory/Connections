<?php
/**
 * Polyfill for the `wp_doing_ajax`.
 *
 * @since 10.4.36
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Polyfill
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

if ( ! function_exists( 'wp_doing_ajax' ) ) :
	/**
	 * Determines whether the current request is a WordPress Ajax request.
	 *
	 * NOTE: This function was added in WP 4.7. Add to be backwards compatible with previous version of WordPress.
	 *
	 * @since 8.5.33
	 *
	 * @return bool True if it's a WordPress Ajax request, false otherwise.
	 */
	function wp_doing_ajax() {

		/**
		 * Filters whether the current request is a WordPress Ajax request.
		 *
		 * @since 8.5.33
		 *
		 * @param bool $wp_doing_ajax Whether the current request is a WordPress Ajax request.
		 */
		return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
endif;
