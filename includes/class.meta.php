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

class cnMeta {

	/**
	 * Previously queried meta will be saved to keep db access
	 * to a minimum. Cache is not persistent between page loads.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $cache = array();

	/**
	 * Get the meta data for the supplied object type and id.
	 *
	 * @access public
	 * @since 0.8
	 * @global $wpdb
	 * @uses   maybe_unserialize()
	 * @param  string  $type   The type of object to which to get the meta data for.
	 * @param  mixed   $ids    array | int  The object id or an array of object IDs.
	 * @param  int     $key    The meta ID to retrieve.
	 *
	 * @return mixed           bool | array
	 */
	public static function get( $type, $ids, $key = 0 ) {
		global $wpdb;

		// The object IDs to query from the db.
		$query = array();

		// Set the default value for the query results to FALSE.
		$result = FALSE;

		// An array which contains the meta data of the object IDs that was requested.
		$meta = array();


		// If an array of object IDs are being requested; first loop thru the cache
		// and pull the ones that already exist so they do not need queried again.
		if ( is_array( $ids ) ) {

			foreach ( $ids as $id ) {

				if ( ! isset( self::$cache[ $id ] ) ) $query[] = $id;
			}

		} else {

			if ( ! isset( self::$cache[ $ids ] ) ) $query[] = $ids;
		}

		// Query the meta data for the objects IDs not in the cache.
		if ( ! empty( $query ) ) {

			$result = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT meta_id, %1$s, meta_key, meta_value FROM %2$s WHERE %1$s IN ( %3$s ) %4$s ORDER BY meta_id, meta_key',
					$column = sanitize_key( $type . '_id' ),
					CN_ENTRY_TABLE_META,
					is_array( $query ) ? implode( ', ', array_map( 'absint', $query ) ) : absint( $query ),
					$key === 0 ? '' : $wpdb->prepare( 'AND meta_key = %s', $key )
				),
				ARRAY_A
			);

		}


		// If there are any results add them to the cache.
		if ( ! empty( $result ) ) {

			foreach ( $result as $row ) {

				$entryID   = intval( $row[ $column ] );
				$metaID    = $row['meta_id'];
				$metaKey   = $row['meta_key'];
				$metaValue = cnFormatting::maybeJSONdecode( $row['meta_value'] );

				// Force subkeys to be array type:
				if ( ! isset( self::$cache[ $entryID ] ) || ! is_array( self::$cache[ $entryID ] ) ) self::$cache[ $entryID ] = array();
				if ( ! isset( self::$cache[ $entryID ][ $metaID ] ) || ! is_array( self::$cache[ $entryID ][ $metaID ] ) ) self::$cache[ $entryID ][ $metaID ] = array();

				self::$cache[ $entryID ][ $metaID ] = array( 'meta_key' => $metaKey, 'meta_value' => $metaValue );
			}

		} else {

			if ( is_array( $ids ) ) {

				foreach ( $ids as $id ) {

					if ( ! isset( self::$cache[ $id ] ) ) self::$cache[ $ids ] = NULL;
				}

			} else {

				if ( ! isset( self::$cache[ $ids ] ) ) self::$cache[ $ids ] = NULL;
			}

		}

		// Return the requested meta data from the cache.
		if ( is_array( $ids ) ) {

			foreach ( $ids as $id ) {

				$meta[ $id ] = self::$cache[ $id ];
			}

			if ( ! empty( $meta ) ) return $meta;

		} else {

			if ( isset( self::$cache[ $ids ] ) ) return self::$cache[ $ids ];
		}

		return FALSE;
	}

	/**
	 * Add meta data to the supplied object type.
	 *
	 * @access public
	 * @since 0.8
	 * @global $wpdb
	 * @uses absint()
	 * @uses wp_unslash()
	 * @uses stripslashes_deep()
	 * @uses do_action()
	 * @param string  $type   The type of object the meta dats is for; ie. entry and taxonomy.
	 * @param int     $id     The object ID.
	 * @param string  $key    Metadata key.
	 * @param string  $value  Metadata value.
	 * @param bool    $unique [optional] Whether the specified metadata key should be
	 *                        unique for the object. If TRUE, and the object already has
	 *                        a value for the specified metadata key, no change will be made.
	 *
	 * @return mixed          int | bool The metadata ID on succesfull insert or FALSE on failure.
	 */
	public static function add( $type, $id, $key, $value, $unique = FALSE ) {
		global $wpdb;

		if ( ! $type || ! $key ) return FALSE;
		if ( ! $id = absint( $id ) ) return FALSE;

		$column = sanitize_key( $type . '_id' );

		// The wp_unslash() is only available in WP >= 3.6 use stripslashes_deep() for backward compatibility.
		$key   = function_exists( 'wp_unslash' ) ? wp_unslash( $key )   : stripslashes_deep( $key );
		$value = function_exists( 'wp_unslash' ) ? wp_unslash( $value ) : stripslashes_deep( $value );

		if ( $unique && $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . CN_ENTRY_TABLE_META . " WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) ) ) {

			return FALSE;
		}

		do_action( "cn_add_meta-$type", $id, $key, $value );

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		$result = $wpdb->insert(
			CN_ENTRY_TABLE_META,
			array(
				$column      => $id,
				'meta_key'   => $key,
				'meta_value' => cnFormatting::maybeJSONencode( $value ) )
		);

		if ( ! $result ) return FALSE;

		$metaID = (int) $wpdb->insert_id;

		// Add the meta to the cache.
		self::$cache[ $id ][ $metaID ] = array( 'meta_key' => $key, 'meta_value' => $value );

		do_action( "cn_added_meta-$type", $metaID, $id, $key, $value );

		return $metaID;
	}

	/**
	 * Update the meta of the specified object.
	 *
	 * @access public
	 * @since 0.8
	 * @global $wpdb
	 * @uses absint()
	 * @uses sanitize_key()
	 * @uses wp_unslash()
	 * @uses stripslashes_deep()
	 * @uses sanitize_meta()
	 * @uses do_action()
	 * @param  string $type   The oject type.
	 * @param  int    $id     The object ID.
	 * @param  string $key    The metadata key.
	 * @param  string $value  The metadata value.
	 * @param  string $oldValue  [optional] The previous metadata value.
	 * @param  string $oldKey    [optional] The previous metadata key.
	 * @param  int    $oldKey    [optional] The previous metadata ID.
	 *
	 * @return mixed          int | bool The number of affected rows or FALSE on failure.
	 */
	public static function update( $type, $id, $key, $value, $oldValue = NULL, $oldKey = NULL, $metaID = 0 ) {
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

		// Adds the filters necessary for custom sanitization functions.
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
	 * @since 0.8
	 * @global $wpdb
	 * @uses absint()
	 * @uses sanitize_key()
	 * @uses do_action()
	 * @param  string $type   The object type.
	 * @param  int    $id     The object ID.
	 * @param  int    $metaID [optional] The meta ID.
	 *
	 * @return mixed          int | bool The number of affected rows or FALSE on failure.
	 */
	public static function delete( $type, $id, $metaID = NULL ) {
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
	 * @since 0.8
	 * @param  string  $type   The object type.
	 * @param  int     $limit  Limit the number of keys to retrieve.
	 * @param  boolean $unique Whether or not to retrieve only unique keys.
	 *
	 * @return array           An array of meta keys.
	 */
	public static function key( $type, $limit = 30, $unique = TRUE ) {
		global $wpdb;

		// $column = sanitize_key( $type . '_id' );

		// $keys  = array();
		$limit = (int) apply_filters( "cn_metakey_limit-$type", $limit );

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		// The query will not retrieve any meta key that begin with an '_' [underscore].
		$sql = $wpdb->prepare( 'SELECT meta_key FROM ' . CN_ENTRY_TABLE_META . ' %1$s GROUP BY meta_key HAVING meta_key NOT LIKE \'\\_%%\' ORDER BY meta_key LIMIT %2$d',
				empty( $key ) ? '' : ' WHERE meta_key IN ("' . implode( '", "', $keys ) . '") ',
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
	 * @since 0.8
	 * @param  string  $key  The key to check.
	 * @param  string  $type The object type.
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
}
