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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Callback for the `upload_size_limit` filter.
 *
 * Add to set WP core constants in case they do not exist.
 *
 * @link https://connections-pro.com/support/topic/images-wont-upload-maximum-upload-size/
 *
 * @access private
 * @since  8.2.21
 *
 * @param  int $bytes
 *
 * @return int An integer byte value.
 */
function cn_upload_size_limit( $bytes ) {

	if ( ! defined( 'KB_IN_BYTES' ) ) {
		define( 'KB_IN_BYTES', 1024 );
	}

	if ( ! defined( 'MB_IN_BYTES' ) ) {
		define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
	}

	if ( ! defined( 'GB_IN_BYTES' ) ) {
		define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
	}

	if ( ! defined( 'TB_IN_BYTES' ) ) {
		define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );
	}

	$u_bytes = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
	$p_bytes = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );

	return 0 < $bytes ? $bytes : min( $u_bytes, $p_bytes );
}
add_filter( 'upload_size_limit', 'cn_upload_size_limit' );

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

	if ( method_exists( 'WPML_Plugin', 'log_email' ) ) {

		global $WPML_Plugin;

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

	if ( method_exists( 'EmailLog', 'log_email' ) ) {

		global $EmailLog;

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

/**
 * If Maps Marker or Maps Marker Pro is installed/activated, deregister and register the inclusion of the
 * Google Maps JavaScript API so its admin notice/warning is not displayed.
 *
 * @since 8.27
 */
add_action( 'plugins_loaded', 'cn_maps_marker_pro' );

function cn_maps_marker_pro() {

	if ( class_exists( 'Leafletmapsmarker', false ) ||
		 class_exists( 'MMP_Globals', false ) ) {

		add_action( 'admin_notices', 'cn_deregister_google_maps_api', 9.999 );
		add_action( 'admin_notices', 'cn_register_google_maps_api', 10.001 );
	}
}

function cn_deregister_google_maps_api() {

	wp_deregister_script( 'cn-google-maps-api' );
}

function cn_register_google_maps_api() {

	// If script is registered, bail.
	if ( wp_script_is( 'cn-google-maps-api', $list = 'registered' ) ) {
		return;
	}

	$googleMapsAPIURL        = 'https://maps.googleapis.com/maps/api/js?v=3&libraries=geometry';
	$googleMapsAPIBrowserKey = cnSettingsAPI::get( 'connections', 'google_maps_geocoding_api', 'browser_key' );

	if ( 0 < strlen( $googleMapsAPIBrowserKey ) ) {

		$googleMapsAPIURL = add_query_arg( 'key', $googleMapsAPIBrowserKey, $googleMapsAPIURL );
	}

	wp_register_script(
		'cn-google-maps-api',
		$googleMapsAPIURL,
		array(),
		CN_CURRENT_VERSION,
		true
	);
}

/**
 * Exclude the Leaflet CSS from Autoptimize.
 *
 * @since 8.30.2
 */
add_filter( 'autoptimize_filter_css_exclude', 'cn_ao_override_css_exclude', 10, 1 );

function cn_ao_override_css_exclude( $exclude ) {

	// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	return $exclude . ", leaflet{$min}.css";
}

/**
 * Compatibility with the SiteOrigin Page Builder plugin.
 *
 * @since 9.16
 */
add_filter(
	'Connections_Directory/Shortcode/Conditional_Content/Post_Content',
	/**
	 * Apply the SiteOrigin renderer if the Page Builder plugin is active.
	 *
	 * @since 9.16
	 *
	 * @param string  $html
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	function ( $html, $post ) {

		if ( is_callable( array( 'SiteOrigin_Panels', 'renderer' ) ) ) {

			/** @noinspection PhpUndefinedClassInspection */
			$html = SiteOrigin_Panels::renderer()->render( $post->ID );
		}

		return $html;
	},
	10,
	2
);

/**
 * Compatibility with the Post Categories by User for WordPress plugin.
 *
 * Prevent this plugin from hiding the categories on the Connections' admin pages.
 *
 * @link https://codecanyon.net/item/post-categories-by-user-for-wordpress/9958036
 * @since 10.2
 */
add_action(
	'current_screen',
	function ( $screen ) {

		global $pcu_plugin;

		/** @noinspection PhpUndefinedClassInspection */
		if ( ! array_key_exists( 'pcu_plugin', $GLOBALS ) || ! $pcu_plugin instanceof plugin_pcu ) {
			return;
		}

		$pages = array( 'connections_page_connections_add', 'connections_page_connections_manage' );

		if ( in_array( $screen->id, $pages ) ) {
			$pcu_plugin->options['hide_terms'] = 0;
		}
	}
);

/**
 * Add overflow `x` and `y` to safe CSS attributes when filtering posts by kses.
 *
 * @since 10.4
 */
add_filter(
	'safe_style_css',
	function ( $attributes ) {

		$attributes[] = 'display';
		$attributes[] = 'overflow-x';
		$attributes[] = 'overflow-y';

		return $attributes;
	}
);

/**
 * Add support for the PageLayer plugin.
 *
 * For some reason PageLayer causes the `the_content` filter to remove the [connections] shortcode
 * even though there is only a single instance of it on the page.
 *
 * @since 10.4
 */
add_action(
	'plugins_loaded',
	static function () {

		if ( class_exists( 'PageLayer' ) ) {

			remove_filter( 'the_content', array( 'cnShortcode', 'single' ), 6 );
		}
	}
);

/**
 * Compatibility shim for the All-in-One Video Gallery plugin.
 *
 * Other plugins seem to also execute the code in this function, but some at a priority that is too soon
 * and the rewrite tags for Connections are not yet registered which breaks the Connections rewrite rules.
 * So, let run this code, but at a later priority to ensure Connections rewrite tags are registered and the
 * rewrite rules for Connections are built correctly.
 *
 * @link   https://gist.github.com/tott/9548734
 * @link   https://core.trac.wordpress.org/ticket/18450#comment:35
 * @link   https://wordpress.org/support/topic/add_action-wp_loaded-public-maybe_flush_rules/
 *
 * @since  10.4.2
 */
add_action(
	'wp_loaded',
	static function () {

		if ( ! class_exists( 'AIOVG_Init', false ) ) {

			return;
		}

		global $wp_rewrite;

		$rewrite_rules = get_option( 'rewrite_rules' );

		if ( $rewrite_rules && $wp_rewrite instanceof WP_Rewrite ) {

			$rewrite_rules_array = array();

			foreach ( $rewrite_rules as $rule => $rewrite ) {

				$rewrite_rules_array[ $rule ]['rewrite'] = $rewrite;
			}

			$rewrite_rules_array = array_reverse( $rewrite_rules_array, true );

			$maybe_missing = $wp_rewrite->rewrite_rules();
			$missing_rules = false;

			foreach ( $maybe_missing as $rule => $rewrite ) {

				if ( ! array_key_exists( $rule, $rewrite_rules_array ) ) {

					$missing_rules = true;
					break;
				}
			}

			if ( true === $missing_rules ) {

				flush_rewrite_rules();
			}
		}
	},
	99
);

/**
 * The Connections Form addon version <= 2.7.5 registers an instance the jQuery UI CSS with a different handle.
 * Dequeue the CSS with that handle, so it is not enqueued on the page twice.
 *
 * @since 10.4.29
 */
add_action(
	'wp_footer',
	static function () {
		wp_dequeue_style( 'cn-public-jquery-ui' );
	},
	9
);
