<?php
/**
 * Store and retrieve cached values supporting various core WP caching API/s.
 *
 * Class forked from the Pods Framework.
 * @url http://pods.io/
 *
 * @package     Connections
 * @subpackage  Cache
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.1
 */

namespace Connections_Directory\Utility;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnCache
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
class _cache {

	const PREFIX = 'cn';

	/**
	 * @var array $modes Array of available cache modes
	 */
	static $modes = array(
		'none',
		'transient',
		'site-transient',
		'cache',
		'option-cache',
	);

	private function __construct() {}

	/**
	 * Get a cached value.
	 *
	 * @since 8.1
	 *
	 * @param string $key      The cache key of the value to return.
	 * @param string $mode     (optional) From which cache method to retrieve the value from. Default: transient.
	 * @param string $group    (optional) Set the group of the value.
	 * @param string $callback (optional) Callback function to run to set the value if not cached.
	 *
	 * @return bool|mixed|null|void
	 */
	public static function get( $key, $mode = 'transient', $group = self::PREFIX, $callback = null ) {

		$object_cache = false;
		$group_key    = '';

		if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {

			$object_cache = true;
		}

		if ( ! in_array( $mode, self::$modes ) ) {

			$mode = 'transient';
		}

		if ( ! empty( $group ) ) {

			$group_key = $group . '_';
		}

		$original_key = $key;

		// Patch for limitations in DB.
		if ( 40 < strlen( $group_key . $key ) ) {

			$key = md5( $key );

			if ( empty( $group_key ) ) {

				$group_key = self::PREFIX . '_';
			}
		}

		$value  = null;
		$called = false;

		$nocache     = array();
		$get_nocache = isset( $_GET['nocache'] ) ? sanitize_text_field( $_GET['nocache'] ) : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( is_admin() && null !== $get_nocache ) {

			if ( 1 < strlen( $get_nocache ) ) {

				$nocache = explode( ',', $get_nocache );

			} else {

				$nocache = self::$modes;
			}

		}

		if ( apply_filters( 'cn_cache_get', false, $mode, $group_key . $key, $original_key, $group ) ) {

			$value = apply_filters( 'cn_cache_get_value', $value, $mode, $group_key . $key, $original_key, $group );

		} elseif ( 'transient' == $mode && ! in_array( $mode, $nocache ) ) {

			$value = get_transient( $group_key . $key );

		} elseif ( 'site-transient' == $mode && ! in_array( $mode, $nocache ) ) {

			$value = get_site_transient( $group_key . $key );

		} elseif ( 'cache' == $mode && $object_cache && ! in_array( $mode, $nocache ) ) {

			$value = wp_cache_get( $key, ( empty( $group ) ? 'cn_cache' : $group ) );

		} elseif ( 'option-cache' == $mode && ! in_array( $mode, $nocache ) ) {

			global $_wp_using_ext_object_cache;

			$pre = apply_filters( 'pre_transient_' . $key, false );

			if ( false !== $pre ) {

				$value = $pre;

			} elseif ( $_wp_using_ext_object_cache ) {

				$value   = wp_cache_get( $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );
				$timeout = wp_cache_get( '_timeout_' . $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );

				if ( ! empty( $timeout ) && $timeout < time() ) {

					if ( is_callable( $callback ) ) {

						// Callback function should do it's own set/update for cache.
						$callback_value = call_user_func( $callback, $original_key, $group, $mode );

						if ( null !== $callback_value && false !== $callback_value ) {

							$value = $callback_value;
						}

						$called = true;

					} else {

						$value = false;

						wp_cache_delete( $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );
						wp_cache_delete( '_timeout_' . $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );
					}
				}

			} else {

				$transient_option  = '_cn_option_' . $key;
				$transient_timeout = '_cn_option_timeout_' . $key;

				$value   = get_option( $transient_option );
				$timeout = get_option( $transient_timeout );

				if ( ! empty( $timeout ) && $timeout < time() ) {

					if ( is_callable( $callback ) ) {

						// Callback function should do it's own set/update for cache.
						$callback_value = call_user_func( $callback, $original_key, $group, $mode );

						if ( null !== $callback_value && false !== $callback_value ) {

							$value = $callback_value;
						}

						$called = true;

					} else {

						$value = false;

						delete_option( $transient_option );
						delete_option( $transient_timeout );
					}
				}
			}

			if ( false !== $value ) {

				$value = apply_filters( 'transient_' . $key, $value );
			}

		} else {

			$value = false;
		}

		if ( false === $value && is_callable( $callback ) && ! $called ) {

			// Callback function should do it's own set/update for cache.
			$callback_value = call_user_func( $callback, $original_key, $group, $mode );

			if ( null !== $callback_value && false !== $callback_value ) {

				$value = $callback_value;
			}
		}

		$value = apply_filters( 'cn_cache_get_' . $mode, $value, $original_key, $group );

		return $value;
	}

	/**
	 * Set a cached value.
	 *
	 * @since 8.1
	 *
	 * @param string $key     The cache key.
	 * @param mixed  $value   Value to add to the cache.
	 * @param int    $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
	 * @param string $mode    (optional) Decides the caching method to use. Default: transient
	 * @param string $group   (optional) Set the group of the value.
	 *
	 * @return bool|mixed|null|string|void
	 */
	public static function set( $key, $value, $expires = 0, $mode = 'transient', $group = self::PREFIX ) {

		$object_cache = false;
		$group_key    = '';

		if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {

			$object_cache = true;
		}

		if ( ! in_array( $mode, self::$modes ) ) {

			$mode = 'transient';
		}

		if ( ! empty( $group ) ) {

			$group_key = $group . '_';
		}

		$original_key = $key;

		// Patch for limitations in DB.
		if ( 40 < strlen( $group_key . $key ) ) {

			$key = md5( $key );

			if ( empty( $group_key ) ) {

				$group_key = self::PREFIX . '_';
			}
		}

		if ( apply_filters( 'cn_cache_set', false, $mode, $group_key . $key, $original_key, $value, $expires, $group ) ) {

			return $value;

		} elseif ( 'transient' == $mode ) {

			set_transient( $group_key . $key, $value, $expires );

		} elseif ( 'site-transient' == $mode ) {

			set_site_transient( $group_key . $key, $value, $expires );

		} elseif ( 'cache' == $mode && $object_cache ) {

			wp_cache_set( $key, $value, ( empty( $group ) ? 'cn_cache' : $group ), $expires );

		} elseif ( 'option-cache' == $mode ) {

			global $_wp_using_ext_object_cache;

			$value = apply_filters( 'pre_set_transient_' . $key, $value );

			if ( $_wp_using_ext_object_cache ) {

				$result = wp_cache_set( $key, $value, ( empty( $group ) ? 'cn_option_cache' : $group ) );

				if ( $expires ) {

					$result = wp_cache_set( '_timeout_' . $key, $expires, ( empty( $group ) ? 'cn_option_cache' : $group ) );
				}

			} else {

				$transient_timeout = '_cn_option_timeout_' . $key;
				$key               = '_cn_option_' . $key;

				if ( false === get_option( $key ) ) {

					if ( $expires ) {

						add_option( $transient_timeout, time() + $expires, '', 'no' );
					}

					$result = add_option( $key, $value, '', 'no' );

				} else {

					if ( $expires ) {

						update_option( $transient_timeout, time() + $expires );
					}

					$result = update_option( $key, $value );
				}

			}

			if ( $result ) {

				/**
				 * Fires after the value for a specific transient has been set.
				 *
				 * The dynamic portion of the hook name, `$key`, refers to the transient name.
				 *
				 * @param mixed  $value   Transient value.
				 * @param int    $expires Time until expiration in seconds.
				 * @param string $key     The name of the transient.
				 */
				do_action( "set_transient_{$key}", $value, $expires, $key );

				/**
				 * Fires after the value for a transient has been set.
				 *
				 * @param string $key     The name of the transient.
				 * @param mixed  $value   Transient value.
				 * @param int    $expires Time until expiration in seconds.
				 */
				do_action( 'setted_transient', $key, $value, $expires );
			}

		}

		do_action( 'cn_cache_set_' . $mode, $original_key, $value, $expires, $group );

		return $value;
	}

	/**
	 * Clear a cached value.
	 *
	 * Examples:
	 * <code>
	 * <?php
	 * // Clear all transients that start with `cn`.
	 * cnCache::clear( TRUE, 'transient', 'cn' );
	 *
	 * // Clear all transients.
	 * cnCache::clear( TRUE, 'transient' );
	 * ?>
	 * </code>
	 *
	 * @since 8.1
	 *
	 * @param mixed  $key   string|bool The cache key to clear or bool to clear a cache group.
	 * @param string $mode  (optional)  Which cache type to clear.
	 * @param string $group (optional)  The cache group to clear.
	 *
	 * @return bool
	 */
	public static function clear( $key = true, $mode = 'transient', $group = '' ) {
		global $wpdb;

		$object_cache = false;

		if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {

			$object_cache = true;
		}

		if ( ! in_array( $mode, self::$modes ) ) {

			$mode = 'transient';
		}

		$group_key = '';

		if ( ! empty( $group ) ) {

			$group_key = $group . '_';
		}

		$full_key = $original_key = $key;

		if ( true !== $key ) {

			// Patch for limitations in DB.
			if ( 40 < strlen( $group_key . $key ) ) {

				$key = md5( $key );

				if ( empty( $group_key ) ) {

					$group_key = self::PREFIX . '_';
				}
			}

			$full_key = $group_key . $key;
		}

		if ( apply_filters( 'cn_cache_clear', false, $mode, $full_key, $original_key, '', 0, $group ) ) {

			return true;

		} elseif ( 'transient' == $mode ) {

			if ( true === $key ) {

				// $group_key = like_escape( $group_key );
				$group_key = $wpdb->esc_like( $group_key );

				$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_{$group_key}%' OR option_name LIKE '_transient_timeout_{$group_key}%'" );
				// $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_timeout_{$group_key}%'" );

				if ( $object_cache ) {

					wp_cache_flush();
				}

			} else {

				delete_transient( $group_key . $key );
			}

		} elseif ( 'site-transient' == $mode ) {

			if ( true === $key ) {

				// $group_key = like_escape( $group_key );
				$group_key = $wpdb->esc_like( $group_key );

				$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_site_transient_{$group_key}%' OR option_name LIKE '_site_transient_timeout_{$group_key}%'" );
				// $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_site_transient_timeout_{$group_key}%'" );

				if ( $object_cache ) {

					wp_cache_flush();
				}

			} else {

				delete_site_transient( $group_key . $key );
			}

		} elseif ( 'cache' == $mode && $object_cache ) {

			if ( true === $key ) {

				wp_cache_flush();

			} else {

				wp_cache_delete( ( empty( $key ) ? 'cn_cache' : $key ), 'cn_cache' );
			}

		} elseif ( 'option-cache' == $mode ) {

			global $_wp_using_ext_object_cache;

			do_action( 'delete_transient_' . $key, $key );

			if ( $_wp_using_ext_object_cache ) {

				$result = wp_cache_delete( $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );

				wp_cache_delete( '_timeout_' . $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );

			} else {

				$option_timeout = '_cn_option_timeout_' . $key;
				$option         = '_cn_option_' . $key;

				$result = delete_option( $option );

				if ( $result ) {

					delete_option( $option_timeout );
				}

			}

			if ( $result ) {

				do_action( 'deleted_transient', $key );
			}

		}

		do_action( 'cn_cache_clear_' . $mode, $original_key, $group );

		return true;
	}
}
