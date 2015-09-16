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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnCache {

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
	 * @access public
	 * @since  8.1
	 * @static
	 * @param string $key      The cache key of the value to return.
	 * @param string $mode     (optional) From which cache method to retrieve the value from. Default: transient
	 * @param string $group    (optional) Set the group of the value.
	 * @param string $callback (optional) Callback function to run to set the value if not cached.
	 *
	 * @return bool|mixed|NULL|void
	 */
	public static function get( $key, $mode = 'transient', $group = self::PREFIX, $callback = NULL ) {

		$object_cache = FALSE;
		$group_key    = '';

		if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {

			$object_cache = TRUE;
		}

		if ( ! in_array( $mode, self::$modes ) ) {

			$mode = 'transient';
		}

		if ( ! empty( $group ) ) {

			$group_key = $group . '_';
		}

		$original_key = $key;

		// Patch for limitations in DB
		if ( 40 < strlen( $group_key . $key ) ) {

			$key = md5( $key );

			if ( empty( $group_key ) ) {

				$group_key = self::PREFIX . '_';
			}
		}

		$value  = NULL;
		$called = FALSE;

		$nocache     = array();
		$get_nocache = isset( $_GET['nocache'] ) ? $_GET['nocache'] : NULL;

		if ( is_admin() && NULL !== $get_nocache ) {

			if ( 1 < strlen( $get_nocache ) ) {

				$nocache = explode( ',', $get_nocache );

			} else {

				$nocache = self::$modes;
			}

		}

		if ( apply_filters( 'cn_cache_get', FALSE, $mode, $group_key . $key, $original_key, $group ) ) {

			$value = apply_filters( 'cn_cache_get_value', $value, $mode, $group_key . $key, $original_key, $group );

		} elseif ( 'transient' == $mode && ! in_array( $mode, $nocache ) ) {

			$value = get_transient( $group_key . $key );

		} elseif ( 'site-transient' == $mode && ! in_array( $mode, $nocache ) ) {

			$value = get_site_transient( $group_key . $key );

		} elseif ( 'cache' == $mode && $object_cache && ! in_array( $mode, $nocache ) ) {

			$value = wp_cache_get( $key, ( empty( $group ) ? 'cn_cache' : $group ) );

		} elseif ( 'option-cache' == $mode && ! in_array( $mode, $nocache ) ) {

			global $_wp_using_ext_object_cache;

			$pre = apply_filters( 'pre_transient_' . $key, FALSE );

			if ( FALSE !== $pre ) {

				$value = $pre;

			} elseif ( $_wp_using_ext_object_cache ) {

				$value   = wp_cache_get( $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );
				$timeout = wp_cache_get( '_timeout_' . $key, ( empty( $group ) ? 'cn_option_cache' : $group ) );

				if ( ! empty( $timeout ) && $timeout < time() ) {

					if ( is_callable( $callback ) ) {

						// Callback function should do it's own set/update for cache
						$callback_value = call_user_func( $callback, $original_key, $group, $mode );

						if ( NULL !== $callback_value && FALSE !== $callback_value ) {

							$value = $callback_value;
						}

						$called = TRUE;

					} else {

						$value = FALSE;

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

						// Callback function should do it's own set/update for cache
						$callback_value = call_user_func( $callback, $original_key, $group, $mode );

						if ( NULL !== $callback_value && FALSE !== $callback_value ) {

							$value = $callback_value;
						}

						$called = TRUE;

					} else {

						$value = FALSE;

						delete_option( $transient_option );
						delete_option( $transient_timeout );
					}
				}
			}

			if ( FALSE !== $value ) {

				$value = apply_filters( 'transient_' . $key, $value );
			}

		} else {

			$value = FALSE;
		}

		if ( FALSE === $value && is_callable( $callback ) && ! $called ) {

			// Callback function should do it's own set/update for cache
			$callback_value = call_user_func( $callback, $original_key, $group, $mode );

			if ( NULL !== $callback_value && FALSE !== $callback_value ) {

				$value = $callback_value;
			}
		}

		$value = apply_filters( 'cn_cache_get_' . $mode, $value, $original_key, $group );

		return $value;
	}

	/**
	 * Set a cached value.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @param string $key     The cache key.
	 * @param mixed  $value   Value to add to the cache.
	 * @param int    $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
	 * @param string $mode    (optional) Decides the caching method to use. Default: transient
	 * @param string $group   (optional) Set the group of the value.
	 *
	 * @return mixed          bool|mixed|NULL|string|void
	 */
	public static function set( $key, $value, $expires = 0, $mode = 'transient', $group = self::PREFIX ) {

		$object_cache = FALSE;
		$group_key    = '';

		if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {

			$object_cache = TRUE;
		}

		if ( ! in_array( $mode, self::$modes ) ) {

			$mode = 'transient';
		}

		if ( ! empty( $group ) ) {

			$group_key = $group . '_';
		}

		$original_key = $key;

		// Patch for limitations in DB
		if ( 40 < strlen( $group_key . $key ) ) {

			$key = md5( $key );

			if ( empty( $group_key ) ) {

				$group_key = self::PREFIX . '_';
			}
		}

		if ( apply_filters( 'cn_cache_set', FALSE, $mode, $group_key . $key, $original_key, $value, $expires, $group ) ) {

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

				if ( FALSE === get_option( $key ) ) {

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

				do_action( 'set_transient_' . $key );
				do_action( 'setted_transient', $key );
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
	 * @access public
	 * @since  8.1
	 * @static
	 * @param  mixed  $key   string|bool The cache key to clear or bool to clear a cache group.
	 * @param  string $mode  (optional)  Which cache type to clear.
	 * @param  string $group (optional)  The cache group to clear.
	 *
	 * @return bool
	 */
	public static function clear( $key = TRUE, $mode = 'transient', $group = '' ) {
		global $wpdb;

		$object_cache = FALSE;

		if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {

			$object_cache = TRUE;
		}

		if ( ! in_array( $mode, self::$modes ) ) {

			$mode = 'transient';
		}

		$group_key = '';

		if ( ! empty( $group ) ) {

			$group_key = $group . '_';
		}

		$full_key = $original_key = $key;

		if ( TRUE !== $key ) {

			// Patch for limitations in DB
			if ( 40 < strlen( $group_key . $key ) ) {

				$key = md5( $key );

				if ( empty( $group_key ) ) {

					$group_key = self::PREFIX . '_';
				}
			}

			$full_key = $group_key . $key;
		}

		if ( apply_filters( 'cn_cache_clear', FALSE, $mode, $full_key, $original_key, '', 0, $group ) ) {

			return TRUE;

		} elseif ( 'transient' == $mode ) {

			if ( TRUE === $key ) {

				//$group_key = like_escape( $group_key );
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

			if ( TRUE === $key ) {

				//$group_key = like_escape( $group_key );
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

			if ( TRUE === $key ) {

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

		return TRUE;
	}

}

/**
 * Fragment caching based on class by Mark Jaquith.
 * @url   http://markjaquith.wordpress.com/2013/04/26/fragment-caching-in-wordpress/
 *
 * <code>
 * $fragment = new cnFragment( 'unique-key', 3600 );
 *
 * if ( ! $fragment->get() ) {
 *
 *     functions_that_do_stuff_live();
 *     these_should_echo();
 *
 *     echo 'All output should be echo'd';
 *
 *     // IMPORTANT: YOU CANNOT FORGET THIS. If you do, the site will break.
 *     $frag->save();
 *
 * }
 * </code>
 *
 * @since 8.1
 * @uses  cnCache
 */
class cnFragment {

	const PREFIX = 'cn';
	protected $key;
	protected $group;
	protected $ttl = WEEK_IN_SECONDS;

	/**
	 * Setup the fragment cache values.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @param  string $key   The cache key.
	 * @param  string $group (optional) The fragment cache group that the fragment belongs to.
	 *
	 * @return \cnFragment
	 */
	public function __construct( $key, $group = '' ) {

		$this->key   = $key;
		$this->group = $group;
	}

	/**
	 * Echo a cached fragment if found.
	 *
	 * @access public
	 * @since  8.1
	 * @uses   cnCache::get()
	 *
	 * @return bool
	 */
	public function get() {

		$fragment = cnCache::get( $this->key, 'transient', $this->group );

		if ( $fragment !== FALSE ) {

			echo $fragment;
			return TRUE;

		} else {

			ob_start();
			return FALSE;
		}
	}

	/**
	 * Save fragment in the cache.
	 *
	 * @access public
	 * @since  8.1
	 * @uses   cnCache::set()
	 *
	 * @param  null $ttl The number of seconds the fragment should live. Defaults to WEEK_IN_SECONDS.
	 *
	 * @return void
	 */
	public function save( $ttl = NULL ) {

		$ttl = is_null( $ttl ) ? $this->ttl : $ttl;

		cnCache::set( $this->key, ob_get_flush(), $ttl, 'transient', $this->group );
	}

	/**
	 * Clear a fragment cache object or object group.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 * @param  mixed  $key   bool | string The cache key to clear. When set to TRUE, clear a fragment cache group.
	 * @param  string $group The cache group to clear
	 * @return void
	 */
	public static function clear( $key, $group = '' ) {

		if ( TRUE !== $key ) {

			cnCache::clear( $key, 'transient', self::PREFIX );

		} else {

			$group_key = empty( $group ) ? self::PREFIX : $group;

			cnCache::clear( TRUE, 'transient', $group_key );
		}

	}
}
