<?php
/**
 * Plugin update processor for Connections Extensions, Templates and Themes.
 *
 * CREDIT: This class was based on "EDD_SL_plugin_Updater.php" from Easy Digital Downloads Software Licenses.
 *
 * @package     Connections
 * @subpackage  Plugin Uppdater
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
	 * @since 8.5.27
	 * @var   array
	 */
	private static $response = array();

	/**
	 * @since 8.5.27
	 * @var   array
	 */
	private static $no_update = array();

	/**
	 * @since 8.5.27
	 * @var   array
	 */
	private static $checked = array();

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
	 * @return boolean|WP_Error  TRUE on success, WP_Error on failure.
	 */
	public static function register( $file, array $data ) {

		$defaults = array(
			//'file'      => '',
			'basename'  => '',
			'slug'      => '',
			'item_id'   => 0,
			'item_name' => '',
			'author'    => '',
			'version'   => '',
			'license'   => '',
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
	}

	/**
	 * Callback for the pre_set_site_transient_update_plugins filter.
	 *
	 * NOTE: The @see wp_update_plugins() function calls set_site_transient( 'update_plugins', $data ) twice which causes
	 *       this filter to run twice when doing plugin update checks.
	 * @access private
	 * @since  8.5.27
	 *
	 * @param stdClass $transient
	 *
	 * @return stdClass
	 */
	public static function check( $transient ) {

		global $pagenow;

		/*
		 * The update_plugins transient should always be an object, if it is not ensure it is.
		 */
		if ( ! is_object( $transient ) ) {

			$transient = new stdClass;
		}

		/*
		 * Multisite installations are handled separately. Why? Unknown.
		 */
		if ( 'plugins.php' == $pagenow && is_multisite() ) {

			return $transient;
		}

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

		$plugins = get_plugins();

		$new_option = new stdClass;
		$new_option->last_checked = time();

		switch ( $pagenow ) {

			case 'update-core.php' :
				$timeout = MINUTE_IN_SECONDS;
				break;

			case 'plugins.php' :
				$timeout = HOUR_IN_SECONDS;
				break;

			default :

				if ( defined( 'DOING_CRON' ) && DOING_CRON ) {

					$timeout = 0;

				} else {

					$timeout = 12 * HOUR_IN_SECONDS;
				}
		}

		$time_not_changed = isset( $transient->last_checked ) && $timeout > ( time() - $transient->last_checked );

		if ( $time_not_changed ) {

			$plugin_changed = FALSE;

			foreach ( $plugins as $file => $p ) {

				$new_option->checked[ $file ] = $p['Version'];

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

				/*
				 * Since wp_update_plugins() calls set_site_transient( 'update_plugins', $data ) twice,
				 * we need to merge the data from the first call back into the second call.
				 */
				$transient->response  = isset( $transient->response )  ? array_merge( $transient->response, self::$response )   : self::$response;
				$transient->no_update = isset( $transient->no_update ) ? array_merge( $transient->no_update, self::$no_update ) : self::$no_update;
				$transient->checked   = isset( $transient->checked )   ? array_merge( $transient->checked, self::$checked )     : self::$checked;

				return $transient;
			}
		}

		/**
		 * Determine if an update check needs to occur.
		 * Base on WordPress core @see wp_update_plugins
		 *
		 * --> END <--
		 */

		$response = self::request( 'update-check' );

		if ( FALSE !== $response ) {

			foreach ( $response as $plugin ) {

				if ( version_compare( self::$plugins[ $plugin->plugin ]['version'], $plugin->new_version, '<' ) ) {

					$transient->response[ $plugin->plugin ] = $plugin;

				} else {

					$transient->no_update[ $plugin->plugin ] = $plugin;
				}

				$transient->checked[ $plugin->plugin ] = self::$plugins[ $plugin->plugin ]['version'];
			}

			/*
			 * Cache the results so they can be merged into the transient data if no plugin updates found.
			 */
			if ( isset( $transient->response ) ) self::$response = $transient->response;
			if ( isset( $transient->no_update ) ) self::$no_update = $transient->no_update;
			if ( isset( $transient->checked ) ) self::$checked = $transient->checked;

			if ( ! isset( $transient->last_checked ) ) $transient->last_checked = time();
		}

		return $transient;
	}

	/**
	 * Callback for the `plugins_api` filter.
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

			$response = self::request( 'info', $plugin );

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
	 * @param string $action
	 * @param array  $plugin
	 *
	 * @return false|object
	 */
	private static function request( $action, $plugin = array() ) {

		$response = FALSE;

		$options = array(
			'timeout'   => 15,
			'sslverify' => FALSE,
			'body'      => array(
				'url'        => home_url(),
				'action'     => $action,
				'plugins'    => ! empty( $plugin ) ? wp_json_encode( $plugin ) : wp_json_encode( self::$plugins ),
			),
			'user-agent' => 'Connections/' . CN_CURRENT_VERSION . '; ' . get_bloginfo( 'url' ),
		);

		//$path = '/home/conne006/logs/sandbox.connections-pro.cnPlugin_Updater-response.log';
		//$date = date( "d-M-Y G:i:s e" );
		//
		//$message = '[' . $date . '] ' . json_encode( $options, JSON_PRETTY_PRINT ) . PHP_EOL;
		//error_log( $message, 3, $path );

		$url = sprintf( 'http://connections-pro.com/wp-json/cn-plugin/v1/%s/', ( ! empty( $plugin ) ? 'info' : 'update-check' ) );

		//if ( $ssl = wp_http_supports( array( 'ssl' ) ) ) {
		//
		//	$url = set_url_scheme( $url, 'https' );
		//}

		$request = wp_remote_post( $url, $options );

		if ( ! is_wp_error( $request ) ) {

			$response = json_decode( wp_remote_retrieve_body( $request ) );

			if ( isset( $response->sections ) ) {

				$response->sections = maybe_unserialize( $response->sections );
			}
		}

		return $response;
	}
}

	cnPlugin_Updater::init();

endif; // End class_exists check.
