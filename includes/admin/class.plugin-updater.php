<?php
/**
 * Plugin update processor for Connections Extensions, Templates and Connectors.
 *
 * CREDIT: This class was based on "EDD_SL_plugin_Updater.php" from Easy Digital Downloads Software Licenses.
 *
 * @package     Connections
 * @subpackage  Plugin Updater
 * @copyright   Copyright (c) 2016, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.27
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if ( ! class_exists( 'cnPlugin_Updater' ) ) :

/**
 * Class cnPlugin_Updater
 */
class cnPlugin_Updater {

	/**
	 * @since 8.5.27
	 * @var   array
	 */
	private static $plugins = array();

	/**
	 * @access public
	 * @since  8.5.27
	 *
	 * @param string $file The full path and filename of the file.
	 * @param array  $data {
	 *     @type int    $item_id   The plugin ID.
	 *                             Optional if $item_name provided otherwise it is required.
	 *     @type string $item_name The plugin name exactly as in the store.
	 *                             Optional if the $item_id is provided otherwise it is required.
	 *     @type string $author    The plugin author name.
	 *     @type string $version   The current plugin version; not, the latest version.
	 *                             Required.
	 *     @type string $license   The license key. Optional.
	 * }
	 *
	 * @return bool|WP_Error  TRUE on success, WP_Error on failure.
	 */
	public static function register( $file, array $data ) {

		$defaults = array(
			//'file'      => '',
			//'basename'  => '',
			//'slug'      => '',
			'item_id'   => 0,
			'item_name' => '',
			'author'    => '',
			'version'   => '',
			'license'   => '',
			'beta'      => FALSE,
		);

		$plugin = cnSanitize::args( $data, $defaults );

		if ( empty( $plugin['item_name'] ) && empty( $plugin['item_id'] ) ) {

			return new WP_Error( 'plugin_id_or_name_not_provided', esc_html__( 'Plugin name or ID is required.', 'connections' ), $plugin );
		}

		if ( empty( $plugin['version'] ) ) {

			return new WP_Error( 'plugin_version_not_provided', esc_html__( 'Plugin version is required.', 'connections' ), $plugin );
		}

		//$plugin['file']     = $file;
		$plugin['basename'] = plugin_basename( $file );
		$plugin['slug']     = basename( $file, '.php' );
		$plugin['item_id']  = absint( $plugin['item_id'] );

		self::$plugins[ $plugin['basename'] ] = $plugin;

		return TRUE;
	}

	/**
	 * Retrieve a registered plugin data by its plugin basename.
	 *
	 * @access public
	 * @since  8.5.27
	 *
	 * @param string $basename
	 *
	 * @return false|array
	 */
	public static function get_plugin_by_basename( $basename ) {

		if ( isset( self::$plugins[ $basename ] ) ) {

			return self::$plugins[ $basename ];
		}

		return FALSE;
	}

	/**
	 * Retrieve a registered plugin data by its basename slug.
	 *
	 * @access public
	 * @since  8.5.27
	 *
	 * @param string $slug
	 *
	 * @return false|array
	 */
	public static function get_plugin_by_slug( $slug ) {

		foreach ( self::$plugins as $basename => $plugin ) {

			if ( $slug === $plugin['slug'] ) {

				return $plugin;
			}
		}

		return FALSE;
	}

	/**
	 * Init the plugin updater.
	 *
	 * This method is run when the file is included and should not be called directly.
	 *
	 * @access private
	 * @since  8.5.27
	 */
	public static function init() {

		// Uncomment for testing.
		//delete_site_transient( 'update_plugins' );

		self::hooks();
	}

	/**
	 * Add the hooks required to hook into the core WordPress plugin update process.
	 *
	 * @access public
	 * @since  8.5.27
	 */
	private static function hooks() {

		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'check' ), 99 );
		add_filter( 'plugins_api', array( __CLASS__, 'plugins_api_filter' ), 10, 3 );
		add_filter( 'http_request_args', array( __CLASS__, 'http_request_args' ), 5, 2 );
		add_action( 'delete_site_transient_update_plugins', array( __CLASS__, 'clear_cached_response' ) );
	}

	/**
	 * Do not send plugin info to wp.org
	 *
	 * This should prevent accidental plugin "collision" where wp.org plugin matches
	 * a plugin registered with this updater.
	 *
	 * Based on logic from this blog post by Mark Jaquith. Updated logic to deal with values
	 * being JSON encoded vs. serialized.
	 *
	 * @link https://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
	 *
	 * @access private
	 * @since  8.5.27
	 *
	 * @param array  $r   An array of HTTP request arguments.
	 * @param string $url The request URL.
	 *
	 * @return array
	 */
	public static function http_request_args( $r, $url ) {

		$api_url = 'http://api.wordpress.org/plugins/update-check/';

		/*
		 * WP core sets the plugin api URL to either http or https based on whether the site supports SSL connections.
		 *
		 * So, lets set the plugin api URL schema using the same login WP core uses to set it in wp_update_plugins().
		 */
		if ( wp_http_supports( array( 'ssl' ) ) ) {

			$api_url = set_url_scheme( $api_url, 'https' );
		}

		// Not a plugin update request. Bail immediately.
		if ( 0 !== strpos( $url, $api_url ) ) {

			return $r;
		}

		$plugins = json_decode( $r['body']['plugins'], TRUE );

		foreach ( self::$plugins as $basename => $plugin ) {

			unset( $plugins['plugins'][ $basename ] );
			unset( $plugins['active'][ array_search( $basename, $plugins['active'] ) ] );
		}

		// Rebase array keys after unsetting array values.
		$plugins['active'] = array_values( $plugins['active'] );

		$r['body']['plugins'] = wp_json_encode( $plugins );

		return $r;
	}

	/**
	 * Callback for the pre_set_site_transient_update_plugins filter.
	 *
	 * NOTE: The @see wp_update_plugins() function calls set_site_transient( 'update_plugins', $data ) twice which causes
	 *       this filter to run twice when doing plugin update checks.
	 *
	 * @access private
	 * @since  8.5.27
	 *
	 * @param stdClass $transient
	 *
	 * @return stdClass
	 */
	public static function check( $transient ) {

		/*
		 * The update_plugins transient should always be an object, if it is not ensure it is.
		 */
		if ( ! is_object( $transient ) ) {

			$transient = new stdClass;
		}

		/*
		 * Since wp_update_plugins() calls set_site_transient( 'update_plugins', $data ) twice,
		 * we need to merge the data from the first call back into the second call.
		 */
		$transient = self::get_cached_response( $transient );

		/**
		 * Determine if an update check needs to occur.
		 *
		 * This should ensure update checks only occur once even though this filter callback function will be run twice
		 * due to two calls to set_site_transient( 'update_plugins', $data ) in @see wp_update_plugins() which causes
		 * this filter callback to run twice.
		 *
		 * Base on WordPress core @see wp_update_plugins()
		 *
		 * --> START <--
		 */

		$timeout = self::get_update_check_timeout();
		$checked = array();

		$time_not_changed = isset( $transient->last_checked ) && $timeout > ( time() - $transient->last_checked );

		if ( $time_not_changed ) {

			$plugins        = get_plugins();
			$plugin_changed = FALSE;

			foreach ( $plugins as $file => $p ) {

				$checked[ $file ] = $p['Version'];

				if ( ( ! isset( $transient->checked[ $file ] ) || strval( $transient->checked[ $file ] ) !== strval( $p['Version'] ) )
				     && array_key_exists( $file, self::$plugins ) /* Skip all plugins not registered with this class. */
				) {

					$plugin_changed = TRUE;
				}
			}

			if ( isset( $transient->response ) && is_array( $transient->response ) ) {

				foreach ( $transient->response as $plugin_file => $update_details ) {

					if ( ! isset( $plugins[ $plugin_file ] )
					     && array_key_exists( $plugin_file, self::$plugins ) /* Skip all plugins not registered with this class. */
					) {

						$plugin_changed = TRUE;
						break;
					}
				}
			}

			// Bail if we've checked recently and if nothing has changed.
			if ( ! $plugin_changed ) {

				return $transient;
			}
		}

		/**
		 * Determine if an update check needs to occur.
		 * Base on WordPress core @see wp_update_plugins
		 *
		 * --> END <--
		 */

		$response = self::request();

		if ( FALSE !== $response && is_array( $response ) ) {

			$update    = array();
			$no_update = array();

			foreach ( $response as $plugin ) {

				if ( version_compare( self::$plugins[ $plugin->plugin ]['version'], $plugin->new_version, '<' ) ) {

					$transient->response[ $plugin->plugin ] = $plugin;
					$update[ $plugin->plugin ] = $plugin;

					// Delete the plugin info transient set in the plugin_api filter for the view details/version links.
					$cache_key = 'cn-edd_sl_rest_request_' . substr( md5( serialize( $plugin->slug ) ), 0, 15 );
					delete_site_transient( $cache_key );

				} else {

					$transient->no_update[ $plugin->plugin ] = $plugin;
					$no_update[ $plugin->plugin ] = $plugin;
				}

				//$transient->checked[ $plugin->plugin ] = self::$plugins[ $plugin->plugin ]['version'];
			}

			/*
			 * Cache the results so they can be merged into the transient data if no plugin updates found.
			 */
			self::set_cached_response( $update, $no_update, $checked );

			if ( ! isset( $transient->last_checked ) ) $transient->last_checked = time();
		}

		// Update the license statuses.
		cnLicense_Status::check();

		return $transient;
	}

	/**
	 * Get the plugin update check timeout.
	 *
	 * Based on @see wp_update_plugins().
	 *
	 * @access private
	 * @since  8.5.27
	 *
	 * @return int
	 */
	private static function get_update_check_timeout() {

		global $pagenow;

		switch ( $pagenow ) {

			case 'update-core.php' :
				$timeout = MINUTE_IN_SECONDS;
				break;

			case 'plugins.php' :
				$timeout = HOUR_IN_SECONDS;
				break;

			default :

				if ( defined( 'DOING_CRON' ) && DOING_CRON ||
				     defined( 'DOING_AJAX' ) && DOING_AJAX
				) {

					$timeout = 0;

				} else {

					$timeout = 12 * HOUR_IN_SECONDS;
				}
		}

		return $timeout;
	}

	/**
	 * NOTE: Using an option instead of a transient because there are too many sites out there with broken
	 *       object caching which causes update checks to occur on every page load rather than when transients expire.
	 *
	 * @access private
	 * @since  8.5.27
	 *
	 * @param object $transient The update_plugins transient.
	 *
	 * @return object
	 */
	private static function get_cached_response( $transient ) {

		$timeout = self::get_update_check_timeout();

		$cached = get_option( 'cn_update_plugins', FALSE );

		if ( FALSE !== $cached ) {

			$last_checked = isset( $cached['last_checked'] ) && ! empty( $cached['last_checked'] ) ? $cached['last_checked'] : time() - $timeout;

			if ( $timeout > ( time() - $last_checked ) ) {

				$response  = isset( $cached['response'] )  ? $cached['response']  : array();
				$no_update = isset( $cached['no_update'] ) ? $cached['no_update'] : array();
				$checked   = isset( $cached['checked'] )   ? $cached['checked']   : array();

				$transient->response  = isset( $transient->response )  ? array_merge( $transient->response, $response )   : $response;
				$transient->no_update = isset( $transient->no_update ) ? array_merge( $transient->no_update, $no_update ) : $no_update;
				$transient->checked   = isset( $transient->checked )   ? array_merge( $transient->checked, $checked )     : $checked;
			}
		}

		return $transient;
	}

	/**
	 * Retrieve the cached update check response.
	 *
	 * @access private
	 * @since  8.5.27
	 *
	 * @param array $updates
	 * @param array $no_update
	 * @param array $checked
	 */
	private static function set_cached_response( $updates = array(), $no_update = array(), $checked = array() ) {

		update_option(
			'cn_update_plugins',
			array(
				'response'     => $updates,
				'no_update'    => $no_update,
				'checked'      => $checked,
				'last_checked' => time(),
			),
			FALSE
		);
	}

	/**
	 * Callback for the `delete_site_transient_update_plugins` filter.
	 *
	 * Delete the cached update check response.
	 *
	 * @access private
	 * @since  8.5.27
	 */
	public static function clear_cached_response() {

		delete_option( 'cn_update_plugins' );
	}

	/**
	 * Callback for the `plugins_api` filter.
	 *
	 * Queries the plugin information to display when the "View details" or "View version x.x details" thickbox.
	 *
	 * Results are cached in a transient for an hour.
	 *
	 * Based on @see EDD_SL_Plugin_Updater::plugins_api_filter().
	 *
	 * @access private
	 * @since  8.5.27
	 *
	 * @param false  $result The result object or array. Default false.
	 * @param string $action The type of information being requested from the Plugin Install API.
	 * @param object $args   Plugin API arguments.
	 *
	 * @return boolean|object
	 */
	public static function plugins_api_filter( $result, $action, $args ) {

		if ( 'plugin_information' != $action ) {

			return $result;
		}

		$plugin = self::get_plugin_by_slug( $args->slug );

		if ( ! isset( $args->slug ) || ( $args->slug != $plugin['slug'] ) ) {

			return $result;
		}

		$cache_key = 'cn-edd_sl_rest_request_' . substr( md5( serialize( $plugin['slug'] ) ), 0, 15 );

		// Get the transient where we store the api request for this plugin for 1 hour.
		$transient = get_site_transient( $cache_key );

		if ( FALSE === $transient && FALSE !== $plugin ) {

			$response = self::request( $plugin );

			if ( FALSE !== $response ) {

				// Expires in 1 hour.
				set_site_transient( $cache_key, $response, HOUR_IN_SECONDS );

				$result = $response;
			}

		} elseif ( is_object( $transient ) ) {

			$result = $transient;
		}

		return $result;
	}

	/**
	 * @access private
	 * @since  8.5.27
	 *
	 * @param array $plugin
	 *
	 * @return false|object
	 */
	private static function request( $plugin = array() ) {

		$response = FALSE;

		/**
		 * Timeout logic base on WP core.
		 * @see wp_update_plugins()
		 */
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {

			$timeout = 30;

		} elseif ( ! empty( $plugin ) ) {

			$timeout = 5;

		} else {

			// Five seconds, plus one extra second for every 10 plugins.
			$timeout = 5 + (int) ( count( self::$plugins ) / 10 );
		}

		$options = array(
			'timeout'   => $timeout,
			'sslverify' => cnHTTP::verifySSL(),
			'body'      => array(
				'url'        => home_url(),
				'action'     => ! empty( $plugin ) ? 'info' : 'update-check',
				'plugins'    => ! empty( $plugin ) ? wp_json_encode( $plugin ) : wp_json_encode( self::$plugins ),
			),
			'user-agent' => 'Connections/' . CN_CURRENT_VERSION . '; ' . get_bloginfo( 'url' ),
		);

		$url = sprintf( 'http://connections-pro.com/wp-json/cn-plugin/v1/%s/', ( ! empty( $plugin ) ? 'info' : 'update-check' ) );

		if ( wp_http_supports( array( 'ssl' ) ) ) {

			$url = set_url_scheme( $url, 'https' );
		}

		/**
		 * Allow plugins to change the API URL.
		 *
		 * @since 8.5.27
		 *
		 * @param string $url    The plugin updater API URL.
		 * @param array  $plugin The plugin data to get the version info for.
		 */
		$url = apply_filters( 'cn_plugin_updater_request_url', $url, $plugin );

		/**
		 * Allow plugins to modify the request params before it is made.
		 *
		 * @since 8.5.27
		 *
		 * @param array $options The options being passed to wp_remote_post().
		 * @param array $plugin  The plugin data to get the version info for.
		 */
		$options = apply_filters( 'cn_plugin_updater_request_options', $options, $plugin );

		$request = wp_remote_post( $url, $options );

		if ( ! is_wp_error( $request ) ) {

			$response = json_decode( wp_remote_retrieve_body( $request ) );

			$response = self::maybe_unserialize_response( $response );
		}

		/**
		 * Allow plugin to alter the response return by request.
		 *
		 * @since 8.5.27
		 *
		 * @param bool|object $response The request response.
		 * @param array       $plugin   The plugin data to get the version info for.
		 */
		return apply_filters( 'cn_plugin_updater_request_response', $response, $plugin );
	}

	/**
	 * Unserialize plugin data received from REST response.
	 *
	 * @access private
	 * @since  8.11
	 *
	 * @param array|stdClass $response
	 *
	 * @return array|stdClass
	 */
	private static function maybe_unserialize_response( $response ) {

		if ( is_array( $response ) ) {

			foreach ( $response as $plugin ) {

				if ( isset( $plugin->sections ) ) {

					$plugin->sections = maybe_unserialize( $plugin->sections );
				}

				if ( isset( $plugin->banners ) ) {

					$plugin->banners = maybe_unserialize( $plugin->banners );
				}

				if ( isset( $plugin->icons ) ) {

					$plugin->icons = maybe_unserialize( $plugin->icons );
				}
			}

		} else {

			if ( isset( $response->sections ) ) {

				$response->sections = maybe_unserialize( $response->sections );
			}

			if ( isset( $response->banners ) ) {

				$response->banners = maybe_unserialize( $response->banners );
			}

			if ( isset( $response->icons ) ) {

				$response->icons = maybe_unserialize( $response->icons );
			}
		}

		return $response;
	}
}

	// Init the plugin updater API!
	cnPlugin_Updater::init();

endif; // End class_exists check.
