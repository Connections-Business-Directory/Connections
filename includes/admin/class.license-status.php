<?php
/**
 * License status processor for Connections Extensions, Templates and Connectors.
 *
 * @package     Connections
 * @subpackage  License Status
 * @copyright   Copyright (c) 2016, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.28
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if ( ! class_exists( 'cnLicense_Status' ) ) :

	/**
	 * Class cnPlugin_Updater
	 */
	class cnLicense_Status {

		/**
		 * @since 8.5.28
		 * @var   array
		 */
		private static $licenses = array();

		/**
		 * Init the plugin updater.
		 *
		 * This method is run when the file is included and should not be called directly.
		 *
		 * @access private
		 * @since  8.5.28
		 */
		public static function init() {

			self::hooks();
		}

		/**
		 * Add the hooks required to hook into the core WordPress plugin update process.
		 *
		 * @access private
		 * @since  8.5.28
		 */
		private static function hooks() {

			// Run the license status check before the plugin update check which is hooked into the 'load-plugins.php' action.
			add_action( 'load-plugins.php', array( __CLASS__, 'check' ), 9 );
			add_action( 'connections_page_connections_settings-licenses',  array( __CLASS__, 'check' ) );
		}

		/**
		 * Register a license for status checks.
		 *
		 * @access public
		 * @since  8.5.28
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
			//$plugin['slug']     = basename( $file, '.php' );
			$plugin['slug']     = self::get_slug( $plugin['item_name'] );
			$plugin['item_id']  = absint( $plugin['item_id'] );

			self::$licenses[ $plugin['basename'] ] = $plugin;

			return TRUE;
		}

		/**
		 * Retrieve a registered license data by its plugin basename.
		 *
		 * @access public
		 * @since  8.5.28
		 *
		 * @param string $basename
		 *
		 * @return false|array
		 */
		public static function get_by_basename( $basename ) {

			if ( isset( self::$licenses[ $basename ] ) ) {

				return self::$licenses[ $basename ];
			}

			return FALSE;
		}

		/**
		 * Retrieve a registered license data by its basename slug.
		 *
		 * @access public
		 * @since  8.5.28
		 *
		 * @param string $slug
		 *
		 * @return false|array
		 */
		public static function get_by_slug( $slug ) {

			foreach ( self::$licenses as $basename => $plugin ) {

				if ( $slug === $plugin['slug'] ) {

					return $plugin;
				}
			}

			return FALSE;
		}

		/**
		 * Get a license status by item slug.
		 *
		 * @access public
		 * @since  8.5.28
		 *
		 * @param string $slug
		 *
		 * @return object|WP_Error Item status on success. WP_Error on failure.
		 */
		public static function get( $slug ) {

			// Retrieve the items license data.
			$data = get_option( 'connections_license_data' );

			if ( isset( $data[ $slug ] ) ) {

				$status = $data[ $slug ];

			} else {

				$license = self::get_by_slug( $slug );

				if ( FALSE !== $license ) {

					/** @var WP_Error $response */
					$response = self::request( $license );

					if ( is_wp_error( $response ) ) {

						return $response;
					}

					$data[ $slug ] = $response;
					$status        = $data[ $slug ];

					update_option( 'connections_license_data', $data, FALSE );

				} else {

					$status = new WP_Error( 'unknown_item', esc_html__( 'Unknown item', 'connections' ), $slug );
				}
			}

			return $status;
		}

		/**
		 * Callback for the `load-plugins.php` and `connections_page_connections_settings-licenses` actions.
		 *
		 * Bulk check the registered licenses for their status.
		 *
		 * @access private
		 * @since  8.5.28
		 */
		public static function check() {

			// Retrieve the items license data.
			$data = get_option( 'connections_license_data' );

			$timeout      = self::get_status_check_timeout();
			$last_checked = isset( $data['last_checked'] ) ? $data['last_checked'] : time() - DAY_IN_SECONDS;

			$check = $timeout < ( time() - $last_checked );

			if ( $check ) {

				$response = self::request();

				if ( ! is_wp_error( $response ) ) {

					$data = array();
					wp_clean_plugins_cache();

					foreach ( $response as $plugin ) {

						$data[ $plugin->slug ] = $plugin;

						// Save license data in transient.
						//set_transient( 'connections_license-' . $plugin->slug, $plugin, DAY_IN_SECONDS );
					}

					$data['last_checked'] = time();
					update_option( 'connections_license_data', $data, FALSE );
				}

			}

		}

		/**
		 * Request the license status for a plugin, if supplied,
		 * and if not supplied will bulk check all registered items.
		 *
		 * @access private
		 * @since  8.5.28
		 *
		 * @param array $plugin
		 *
		 * @return array|object|WP_Error Array of objects if bulk checking all registered items.
		 *                               Object if checking a single item.
		 *                               WP_Error on failure.
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
				$timeout = 5 + (int) ( count( self::$licenses ) / 10 );
			}

			$options = array(
				'timeout'   => $timeout,
				'sslverify' => cnHTTP::verifySSL(),
				'body'      => array(
					'url'        => home_url(),
					'action'     => 'status',
					'plugins'    => ! empty( $plugin ) ? wp_json_encode( $plugin ) : wp_json_encode( self::$licenses ),
				),
				'user-agent' => 'Connections/' . CN_CURRENT_VERSION . '; ' . get_bloginfo( 'url' ),
			);

			//$url = 'http://connections-pro.com/wp-json/cn-plugin/v1/status/';
			$url = sprintf( 'http://connections-pro.com/wp-json/cn-plugin/v1/%s/', ( ! empty( $plugin ) ? 'item-status' : 'status' ) );

			if ( wp_http_supports( array( 'ssl' ) ) ) {

				$url = set_url_scheme( $url, 'https' );
			}

			/**
			 * Allow plugins to change the API URL.
			 *
			 * @since 8.5.28
			 *
			 * @param string $url    The plugin updater API URL.
			 * @param array  $plugin The plugin data to get the version info for.
			 */
			$url = apply_filters( 'cn_license_status_request_url', $url, $plugin );

			/**
			 * Allow plugins to modify the request params before it is made.
			 *
			 * @since 8.5.28
			 *
			 * @param array $options The options being passed to wp_remote_post().
			 * @param array $plugin  The plugin data to get the version info for.
			 */
			$options = apply_filters( 'cn_license_status_request_options', $options, $plugin );

			$request = wp_remote_post( $url, $options );

			if ( is_wp_error( $request ) ) {

				/** @var WP_Error $request */
				return $request;
			}

			$response = json_decode( wp_remote_retrieve_body( $request ) );

			if ( is_null( $response ) ) {

				return new WP_Error(
					'null_response',
					esc_html__( 'License check response returned NULL.', 'connections' ),
					$request
				);
			}

			/**
			 * Allow plugin to alter the response return by request.
			 *
			 * @since 8.5.28
			 *
			 * @param bool|object $response The request response.
			 * @param array       $plugin   The plugin data to get the version info for.
			 */
			return apply_filters( 'cn_license_status_request_response', $response, $plugin );
		}

		/**
		 * Create item slug from item name.
		 *
		 * @access private
		 * @since  8.5.28
		 * @static
		 *
		 * @param  string $name The item name.
		 *
		 * @return string       The item slug.
		 */
		private static function get_slug( $name ) {

			return preg_replace( '/[^a-z0-9_\-]/', '', str_replace( ' ', '_', strtolower( $name ) ) );
		}

		/**
		 * Get the plugin update check timeout.
		 *
		 * Based on @see wp_update_plugins().
		 *
		 * @access private
		 * @since  8.5.28
		 *
		 * @return int
		 */
		private static function get_status_check_timeout() {

			global $pagenow;

			switch ( $pagenow ) {

				case 'update-core.php' :
				case 'admin.php' :
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
	}

	// Init the plugin updater API!
	cnLicense_Status::init();

endif; // End class_exists check.
