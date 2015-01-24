<?php

/**
 * Metadata API.
 *
 * Provides methods to manage the meta data of the vaious Connections object types.
 *
 * @package     Connections
 * @subpackage  Meta
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnMeta
 */
class cnMeta {

	/**
	 * Previously queried meta will be saved to keep db access
	 * to a minimum. Cache is not persistent between page loads.
	 *
	 * @access private
	 * @since  0.8
	 * @var array
	 */
	private static $cache = array();

	/**
	 * Retrieve metadata for the specified object.
	 *
	 * @since 8.1.7
	 *
	 * @param string $type      Type of object metadata is for (e.g., entry, term)
	 * @param int    $id        ID of the object metadata is for
	 * @param string $key       Optional. Metadata key. If not specified, retrieve all metadata for
	 *                          the specified object.
	 * @param bool   $single    Optional, default is false. If true, return only the first value of the
	 *                          specified meta_key. This parameter has no effect if meta_key is not specified.
	 *
	 * @return bool|string|array Single metadata value, or array of values
	 */
	public static function get( $type, $id, $key = '', $single = FALSE ) {

		if ( ! $type || ! is_numeric( $id ) ) {

			return FALSE;
		}

		$id = absint( $id );

		if ( ! $id ) {

			return FALSE;
		}

		/**
		 * Filter whether to retrieve metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta object type (entry, term).
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 8.1.7
		 *
		 * @param null|array|string $value     The value should return
		 *                                     a single metadata value,
		 *                                     or an array of values.
		 * @param int               $id        Object ID.
		 * @param string            $key       Meta key.
		 * @param string|array      $single    Meta value, or an array of values.
		 */
		$check = apply_filters( "cn_get_{$type}_metadata", NULL, $id, $key, $single );

		if ( NULL !== $check ) {

			if ( $single && is_array( $check ) ) {

				return $check[0];

			} else {

				return $check;
			}
		}

		$meta_cache = wp_cache_get( $id, 'cn_' . $type . '_meta' );

		if ( ! $meta_cache ) {

			$meta_cache = self::updateCache( $type, array( $id ) );
			$meta_cache = $meta_cache[ $id ];
		}

		if ( ! $key ) {

			return $meta_cache;
		}

		if ( isset( $meta_cache[ $key ] ) ) {

			if ( $single ) {

				return cnFormatting::maybeJSONdecode( $meta_cache[ $key ][0] );

			} else {

				return array_map( array( 'cnFormatting', 'maybeJSONdecode' ), $meta_cache[ $key ] );
			}
		}

		if ( $single ) {

			return '';

		} else {

			return array();
		}
	}

	/**
	 * Update the metadata cache for the specified objects.
	 *
	 * @since 8.1.7
	 *
	 * @global wpdb     $wpdb       WordPress database abstraction object.
	 *
	 * @param string    $type       Type of object metadata is for (e.g., comment, post, or user)
	 * @param int|array $object_ids array or comma delimited list of object IDs to update cache for
	 *
	 * @return mixed Metadata cache for the specified objects, or false on failure.
	 */
	private static function updateCache( $type, $object_ids ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! $type || ! $object_ids ) {
			return FALSE;
		}

		$table  = self::tableName( $type );
		$column = sanitize_key( $type . '_id');

		if ( ! is_array( $object_ids ) ) {

			$object_ids = preg_replace('|[^0-9,]|', '', $object_ids );
			$object_ids = explode(',', $object_ids);
		}

		$object_ids = array_map( 'intval', $object_ids );

		$cache_key = 'cn_' . $type . '_meta';
		$ids       = array();
		$cache     = array();

		foreach ( $object_ids as $id ) {

			$cached_object = wp_cache_get( $id, $cache_key );

			if ( FALSE === $cached_object ) {

				$ids[] = $id;

			} else {

				$cache[ $id ] = $cached_object;
			}
		}

		if ( empty( $ids ) ) {

			return $cache;
		}

		// Get meta data.
		$id_list   = join( ',', $ids );
		$meta_list = $wpdb->get_results(
			"SELECT $column, meta_key, meta_value FROM $table WHERE $column IN ($id_list) ORDER BY meta_id ASC",
			ARRAY_A
		);

		if ( ! empty( $meta_list ) ) {

			foreach ( $meta_list as $metarow ) {

				$mpid = intval( $metarow[ $column ] );
				$mkey = $metarow['meta_key'];
				$mval = $metarow['meta_value'];

				// Force sub keys to be array type.
				if ( ! isset( $cache[ $mpid ] ) || ! is_array( $cache[ $mpid ] ) ) {

					$cache[ $mpid ] = array();
				}

				if ( ! isset( $cache[ $mpid ][ $mkey ] ) || ! is_array( $cache[ $mpid ][ $mkey ] ) ) {

					$cache[ $mpid ][ $mkey ] = array();
				}

				// Add a value to the current pid/key:
				$cache[ $mpid ][ $mkey ][] = $mval;
			}
		}

		foreach ( $ids as $id ) {

			if ( ! isset( $cache[ $id ] ) ) {

				$cache[ $id ] = array();
			}

			wp_cache_add( $id, $cache[ $id ], $cache_key );
		}

		return $cache;
	}

	/**
	 * Add meta data to the supplied object type.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @global wpdb  $wpdb    WordPress database abstraction object.
	 *
	 * @uses   absint()
	 * @uses   wp_unslash()
	 * @uses   stripslashes_deep()
	 * @uses   do_action()
	 * @uses   cnMeta::tableName()
	 * @uses   sanitize_key()
	 * @uses   sanitize_meta()
	 * @uses   apply_filters()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_var()
	 * @uses   wpdb::inset()
	 * @uses   wp_cache_delete()
	 *
	 * @param string $type    The type of object the meta data is for; ie. entry and taxonomy.
	 * @param int    $id      The object ID.
	 * @param string $key     Metadata key.
	 * @param string $value   Metadata value.
	 * @param bool   $unique  [optional] Whether the specified metadata key should be
	 *                        unique for the object. If TRUE, and the object already has
	 *                        a value for the specified metadata key, no change will be made.
	 *
	 * @return mixed          int | bool The metadata ID on successful insert or FALSE on failure.
	 */
	public static function add( $type, $id, $key, $value, $unique = FALSE ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( ! $type || ! $key || ! is_numeric( $id ) ) {
			return FALSE;
		}

		$id = absint( $id );
		if ( ! $id ) {
			return FALSE;
		}

		$table  = self::tableName( $type );
		$column = sanitize_key( $type . '_id' );

		// The wp_unslash() is only available in WP >= 3.6 use stripslashes_deep() for backward compatibility.
		$key   = function_exists( 'wp_unslash' ) ? wp_unslash( $key )   : stripslashes_deep( $key );
		$value = function_exists( 'wp_unslash' ) ? wp_unslash( $value ) : stripslashes_deep( $value );
		$value = sanitize_meta( $key, $value, 'cn_' . $type );

		/**
		 * Filter whether to add metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta object type (entry, term).
		 *
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 8.1.7
		 *
		 * @param null|bool $check  Whether to allow adding metadata for the given type.
		 * @param int       $id     Object ID.
		 * @param string    $key    Meta key.
		 * @param mixed     $value  Meta value. Must be json encoded if non-scalar. Use @see cnFormatting::maybeJSONencode().
		 * @param bool      $unique Whether the specified meta key should be unique
		 *                          for the object. Optional. Default false.
		 */
		$check = apply_filters( "cn_add_{$type}_metadata", NULL, $id, $key, $value, $unique );

		if ( NULL !== $check ) {
			return $check;
		}

		if ( $unique && $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM " . $table . " WHERE meta_key = %s AND $column = %d",
					$key,
					$id
				)
			)
		) {

			return FALSE;
		}

		/**
		 * Fires immediately before meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (comment, post, or user).
		 *
		 * @since 8.1.7
		 *
		 * @param int    $id    Object ID.
		 * @param string $key   Meta key.
		 * @param mixed  $value Meta value.
		 */
		do_action( "cn_add_{$type}_meta", $id, $key, $value );

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		$result = $wpdb->insert(
			$table,
			array(
				$column      => $id,
				'meta_key'   => $key,
				'meta_value' => cnFormatting::maybeJSONencode( $value ) )
		);

		if ( ! $result ) {
			return FALSE;
		}

		$metaID = (int) $wpdb->insert_id;

		wp_cache_delete( $id, 'cn_' . $type . '_meta' );

		// Add the meta to the cache.
		self::$cache[ $id ][ $metaID ] = array( 'meta_key' => $key, 'meta_value' => $value );

		/**
		 * Fires immediately after meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (comment, post, or user).
		 *
		 * @since 8.1.7
		 *
		 * @param int    $metaID The meta ID after successful update.
		 * @param int    $id     Object ID.
		 * @param string $key    Meta key.
		 * @param mixed  $value  Meta value.
		 */
		do_action( "cn_added_{$type}_meta", $metaID, $id, $key, $value );

		return $metaID;
	}

	/**
	 * Update the meta of the specified object.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @global wpdb   $wpdb     WordPress database abstraction object.
	 *
	 * @uses   absint()
	 * @uses   sanitize_key()
	 * @uses   wp_unslash()
	 * @uses   stripslashes_deep()
	 * @uses   sanitize_meta()
	 * @uses   do_action()
	 *
	 * @param  string $type     The object type.
	 * @param  int    $id       The object ID.
	 * @param  string $key      The metadata key.
	 * @param  string $value    The metadata value.
	 * @param  string $oldValue [optional] The previous metadata value.
	 * @param  string $oldKey   [optional] The previous metadata key.
	 * @param  int    $metaID   [optional] The previous metadata ID.
	 *
	 * @return mixed          int | bool The number of affected rows or FALSE on failure.
	 */
	public static function update( $type, $id, $key, $value, $oldValue = NULL, $oldKey = NULL, $metaID = 0 ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$data = array();
		$where = array();

		if ( ! $type || ! $key ) return FALSE;
		if ( ! $id = absint( $id ) ) return FALSE;

		$column = sanitize_key( $type . '_id' );

		// The wp_unslash() is only available in WP >= 3.6; use stripslashes_deep() for backward compatibility.
		$key   = function_exists( 'wp_unslash' ) ? wp_unslash( $key )   : stripslashes_deep( $key );
		$value = function_exists( 'wp_unslash' ) ? wp_unslash( $value ) : stripslashes_deep( $value );

		if ( $oldValue !== NULL ) {

			$oldValue = function_exists( 'wp_unslash' ) ? wp_unslash( $oldValue ) : stripslashes_deep( $oldValue );
		}

		if ( $oldKey !== NULL ) {

			$oldKey = function_exists( 'wp_unslash' ) ? wp_unslash( $oldKey ) : stripslashes_deep( $oldKey );
		}

		if ( $metaID !== 0 ) {

			$metaID = absint( $metaID );
		}

		// Adds the filters necessary for custom sanitation functions.
		$value = sanitize_meta( $key, $value, 'cn_' . $type );

		// Compare existing value to new value if no prev value given and the key exists only once.
		if ( $oldValue === NULL ) {

			$results = self::get( $type, $id, $key );

			if ( count( $results ) == 1 ) {

				$meta = is_array( $results ) ? array_shift( $results ) : $results;

				if ( $meta['meta_value'] === $value ) {

					return FALSE;
				}
			}
		}

		$metaExists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM " . CN_ENTRY_TABLE_META . " WHERE meta_key = %s AND $column = %d",
				$oldKey === NULL ? $key : $oldKey,
				$id
			)
		);

		if ( ! $metaExists ) {

			return self::add( $type, $id, $key, $value );
		}

		do_action( "cn_update_meta-$type", $id, $key, $value );

		// Update the `meta_key` field only if previous value is supplied and add to the $data array for $wpdb->update().
		if ( $oldKey !== NULL ) {

			if ( $key !== $oldKey ) $data['meta_key'] = $key;
		}

		// Add the `meta_value` value to the $data array for $wpdb->update().
		$data['meta_value'] = cnFormatting::maybeJSONencode( $value );

		// Add the `*_id` value to the $where array for $wpdb->update().
		// This represents the object id.
		if ( $metaID !== 0 ) {

			$where['meta_id'] = $metaID;
		}

		// Add the `meta_id` value to the $where array for $wpdb->update().
		$where[ $column ] = $id;

		// Add the `meta_key` value to the $where array for $wpdb->update().
		$where['meta_key'] = $oldKey === NULL ? $key : $oldKey;

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		$result = $wpdb->update(
			CN_ENTRY_TABLE_META,
			$data,
			$where
		);

		do_action( "cn_updated_meta-$type", $id, $key, $value );

		// Update the meta in the cache.
		self::$cache[ $id ][ $metaID ] = array( 'meta_key' => $key, 'meta_value' => $value );

		// Result will be FALSE on failure or the number of rows affected is successful.
		return $result;
	}

	/**
	 * Delete the meta of the specified object.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @global wpdb   $wpdb   WordPress database abstraction object.
	 *
	 * @uses   absint()
	 * @uses   sanitize_key()
	 * @uses   do_action()
	 *
	 * @param  string $type   The object type.
	 * @param  int    $id     The object ID.
	 * @param  int    $metaID [optional] The meta ID.
	 *
	 * @return mixed          int | bool The number of affected rows or FALSE on failure.
	 */
	public static function delete( $type, $id, $metaID = NULL ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$where = array();

		if ( ! $type ) return FALSE;
		if ( ! $id = absint( $id ) ) return FALSE;

		$column = sanitize_key( $type . '_id' );

		do_action( "cn_delete_meta-$type", $id );

		// The meta of the supplied object type to delete.
		$where[ $column ] = $id;

		// Only delete the specified meta.
		if ( $metaID !== NULL && $metaID = absint( $metaID ) ) {

			if ( ! $metaID = absint( $metaID ) ) return FALSE;

			$where[ 'meta_id' ] = $metaID;
		}

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		$result = $wpdb->delete(
			CN_ENTRY_TABLE_META,
			$where
		);

		do_action( "cn_deleted_meta-$type", $id );

		// Remove the meta in the cache.
		unset( self::$cache[ $id ][ $metaID ] );

		// Result will be FALSE on failure or the number of rows affected is successful.
		return $result;
	}

	/**
	 * Retrieve the specified meta keys.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @global wpdb   $wpdb  WordPress database abstraction object.
	 *
	 * @param  string $type  The object type.
	 * @param  int    $limit Limit the number of keys to retrieve.
	 *
	 * @return array           An array of meta keys.
	 */
	public static function key( $type, $limit = 30 ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		// $column = sanitize_key( $type . '_id' );

		// $keys  = array();
		$limit = (int) apply_filters( "cn_metakey_limit-$type", $limit );

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		// The query will not retrieve any meta key that begin with an '_' [underscore].
		$sql = $wpdb->prepare( 'SELECT meta_key FROM ' . CN_ENTRY_TABLE_META . ' GROUP BY meta_key HAVING meta_key NOT LIKE \'\\_%%\' ORDER BY meta_key LIMIT %d',
				//empty( $key ) ? '' : ' WHERE meta_key IN ("' . implode( '", "', $keys ) . '") ',
				absint( $limit )
			);

		$keys = $wpdb->get_col( $sql );

		if ( $keys ) natcasesort( $keys );

		foreach ( $keys as $i => $key ) {

			if ( self::isPrivate( $key ) ) unset( $keys[ $i ] );
		}

		return $keys;
	}

	/**
	 * Checks whether or not the `key` is private or not.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  string $key  The key to check.
	 * @param  string $type The object type.
	 *
	 * @return boolean
	 */
	public static function isPrivate( $key, $type = NULL ) {

		$private = FALSE;

		if ( is_string( $key ) && strlen( $key ) > 0 ) {

			$private = ( '_' == $key[0] );

			// Grab the registered metaboxes from the options table.
			// $metaboxes = get_option( 'connections_metaboxes', array() );
			$metaboxes = cnMetaboxAPI::get();

			// Loop thru all fields registered as part of a metabox.
			// If one id found consider it private and exit the loops.
			//
			// NOTE: All fields registered via the metabox  API are considered private.
			// The expectation is an action will be called to render the metadata.
			foreach ( $metaboxes as $metabox ) {

				if ( isset( $metabox['fields'] ) ) {

					foreach ( $metabox['fields'] as $field ) {

						if ( $field['id'] == $key ) {

							// Field found, it's private ... exit loop.
							$private = TRUE;
							continue;
						}
					}
				}

				if ( isset( $metabox['sections'] ) ) {

					foreach ( $metabox['sections'] as $section ) {

						foreach ( $section['fields'] as $field ) {

							if ( $field['id'] == $key ) {

								// Field found, it's private ... exit the loops.
								$private = TRUE;
								continue(2);
							}
						}
					}
				}
			}

		}

		return apply_filters( 'cn_is_private_meta', $private, $key, $type );
	}

	/**
	 * Retrieve the name of the metadata table for the specified meta type.
	 *
	 * @access private
	 * @since  8.1.7
	 * @static
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @uses   is_multisite()
	 *
	 * @param string $type Type of object to get metadata table name for (e.g., entry, term).
	 *
	 * @return string
	 */
	private static function tableName( $type ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		/*
		 * Set the table prefix accordingly depending if Connections is installed on a multisite WP installation.
		 */
		$prefix = ( is_multisite() && CN_MULTISITE_ENABLED ) ? $wpdb->prefix : $wpdb->base_prefix;

		if ( 'entry' == $type ) {

			$name = CN_ENTRY_TABLE_META;

		} else {

			$name = "{$prefix}connections_{$type}_meta";
		}

		return $name;
	}
}
