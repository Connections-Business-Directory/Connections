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
 * Callback for the `upload_size_limit` filter.
 *
 * Add to set WP core constants in case they do not exist.
 *
 * @link http://connections-pro.com/support/topic/images-wont-upload-maximum-upload-size/
 *
 * @access private
 * @since  8.2.21
 *
 * @param  int $bytes
 *
 * @return int An integer byte value.
 */
function cn_upload_size_limit( $bytes ) {

	if ( ! defined( 'KB_IN_BYTES' ) )
		define( 'KB_IN_BYTES', 1024 );

	if ( ! defined( 'MB_IN_BYTES' ) )
		define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );

	if ( ! defined( 'GB_IN_BYTES' ) )
		define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );

	if ( ! defined( 'TB_IN_BYTES' ) )
		define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );

	$u_bytes = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
	$p_bytes = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );

	return 0 < $bytes ? $bytes : min( $u_bytes, $p_bytes );
}
add_filter( 'upload_size_limit', 'cn_upload_size_limit' );

/**
 * Ensure WPSEO header items are not added to internal Connections pages.
 * @todo Should add Connections related header items to mimic WPSEO.
 *
 * @access private
 * @since  8.1.1
 * @return void
 */
function cn_remove_wpseo_head() {

	if ( cnQuery::getVar( 'cn-entry-slug' ) ||
		 cnQuery::getVar( 'cn-cat-slug' ) ||
		 cnQuery::getVar( 'cn-cat' ) ) {

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

/**
 * Add support for the Log Emails Plugin
 *
 *Filter is added on the `plugins_loaded` hook to ensure Log Emails has been loaded.
 *
 * @link https://wordpress.org/plugins/log-emails/
 *
 * @since 8.2.10
 */
add_action( 'plugins_loaded', 'cn_log_emails_add_email_filter' );

function cn_log_emails_add_email_filter() {

	if ( class_exists( 'LogEmailsPlugin' ) && method_exists( 'LogEmailsPlugin', 'getInstance' ) ) {

		$instance = LogEmailsPlugin::getInstance();

		add_filter( 'cn_email', array( $instance, 'wpMail' ) );
	}
}

/**
 * Prevent WP Super Cache from purging the post cache when inserting or deleting a log.
 * @link
 *
 * @since 8.2.10
 */
add_action( 'cn_pre_insert_log', 'cn_disable_wp_super_cache_purge' );
add_action( 'cn_pre_update_log', 'cn_disable_wp_super_cache_purge' );

function cn_disable_wp_super_cache_purge() {

	if ( isset( $GLOBALS['wp_super_cache_late_init'] ) ) {

		remove_action( 'delete_post', 'wp_cache_post_edit', 0 );
		remove_action( 'clean_post_cache', 'wp_cache_post_edit' );
	}
}

add_action( 'cn_post_insert_log', 'cn_enable_wp_super_cache_purge' );
add_action( 'wp_post_update_log', 'cn_enable_wp_super_cache_purge' );

function cn_enable_wp_super_cache_purge() {

	if ( isset( $GLOBALS['wp_super_cache_late_init'] ) ) {

		add_action( 'delete_post', 'wp_cache_post_edit', 0 );
		add_action( 'clean_post_cache', 'wp_cache_post_edit' );
	}
}

/**
 * Prevent WP Rocket from purging the post cache when inserting or deleting a log.
 * @link
 *
 * @since 8.2.10
 */
add_action( 'cn_pre_insert_log', 'cn_disable_wp_rocket_purge' );
add_action( 'cn_pre_update_log', 'cn_disable_wp_rocket_purge' );

function cn_disable_wp_rocket_purge() {

	if ( defined( 'WP_ROCKET_VERSION' ) ) {

		remove_action( 'create_term', 'rocket_clean_domain' );
		remove_action( 'edited_terms', 'rocket_clean_domain' );
		remove_action( 'delete_term', 'rocket_clean_domain' );
		remove_action( 'delete_post', 'rocket_clean_post' );
		remove_action( 'clean_post_cache', 'rocket_clean_post' );
	}
}

add_action( 'cn_post_insert_log', 'cn_enable_wp_rocket_purge' );
add_action( 'wp_post_update_log', 'cn_enable_wp_rocket_purge' );

function cn_enable_wp_rocket_purge() {

	if ( defined( 'WP_ROCKET_VERSION' ) ) {

		add_action( 'create_term', 'rocket_clean_domain' );
		add_action( 'edited_terms', 'rocket_clean_domain' );
		add_action( 'delete_term', 'rocket_clean_domain' );
		add_action( 'delete_post', 'rocket_clean_post' );
		add_action( 'clean_post_cache', 'rocket_clean_post' );
	}
}

/**
 * Clean Wordfence Falcon Cache on entry/term insert/update.
 *
 * @since 8.2.25
 */
add_action( 'cn_clean_entry_cache', 'wordfence_clean_falcon_cache' );
add_action( 'cn_clean_term_cache', 'wordfence_clean_falcon_cache' );

function wordfence_clean_falcon_cache() {

	if ( class_exists( 'wfCache' ) &&
	     method_exists( 'wfCache', 'clearPageCache' ) &&
	     is_callable( array( 'wfCache', 'clearPageCache' ) )
	) {

		wfCache::clearPageCache();
	}
}

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
