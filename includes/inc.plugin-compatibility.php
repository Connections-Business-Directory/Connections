<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     Connections
 * @subpackage  Functions/Compatibility
 * @copyright   @copyright   Copyright (c) 2015, Steven A. Zahm
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

		if ( isset( $GLOBALS['wpseo_front'] ) ) remove_action( 'wp_head', array( $GLOBALS['wpseo_front'], 'head' ), 1 );
	}

}
add_action( 'parse_query', 'cn_remove_wpseo_head' );

/**
 * Prevent s2member re-setting custom capabilities on re-activation.
 * gh-392
 */
add_filter( 'ws_plugin__s2member_lock_roles_caps', '__return_true' );

/**
 * Add support for the WP Mail Logging Plugin
 *
 * Filter is added on the `plugins_loaded` hook to ensure WPML has been loaded.
 *
 * @link https://wordpress.org/plugins/wp-mail-logging/
 *
 * @since 8.2.10
 */
add_action( 'plugins_loaded', 'cn_WPML_add_email_filter' );

function cn_WPML_add_email_filter() {

	global $WPML_Plugin;

	if ( method_exists( $WPML_Plugin, 'log_email' ) ) {

		add_filter( 'cn_email', array( $WPML_Plugin, 'log_email' ) );
	}
}

/**
 * Add support for the Email Log Plugin
 *
 * Filter is added on the `init` at priority 11 hook to ensure Email Log has been loaded.
 *
 * @link https://wordpress.org/plugins/email-log/
 *
 * @since 8.2.10
 */
add_action( 'init', 'cn_email_log_add_email_filter', 11 );

function cn_email_log_add_email_filter() {

	global $EmailLog;

	if ( method_exists( $EmailLog, 'log_email' ) ) {

		add_filter( 'cn_email', array( $EmailLog, 'log_email' ) );
	}
}
