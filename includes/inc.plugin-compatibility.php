<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     Connections
 * @subpackage  Functions/Compatibility
 * @copyright   @copyright   Copyright (c) 2014, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Ensure WPSEO header items are not added to internal Connections pages.
 * @todo Should add Connections related header items to mimic WPSEO.
 *
 * @access private
 * @since  8.1.1
 * @return void
 */
function cn_remove_wpseo_head() {

	if ( get_query_var( 'cn-entry-slug' ) ||
		 get_query_var( 'cn-cat-slug' ) ||
		 get_query_var( 'cn-cat' ) ) {

		remove_action( 'wp_head', array( $GLOBALS['wpseo_front'], 'head' ), 1 );
	}

}
add_action( 'parse_query', 'cn_remove_wpseo_head' );
