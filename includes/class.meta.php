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
	 * to a minimum.
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
	 * @param  mixed   $ids     array | int  The object id or an array of object IDs.
	 *
	 * @return mixed           bool | array
	 */
	public static function get( $type, $ids ) {
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

			if ( !  isset( self::$cache[ $ids ] ) ) $query[] = $ids;
		}

		// Query the meta data for the objects IDs not in the cache.
		if ( ! empty( $query ) ) {

			$result = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT meta_id, %1$s, meta_key, meta_value FROM %2$s WHERE %1$s IN ( %3$s ) ORDER BY meta_id, meta_key',
					$column = sanitize_key( $type . '_id' ),
					CN_ENTRY_TABLE_META,
					is_array( $query ) ? implode( ', ', array_map( 'absint', $query ) ) : absint( $query )
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
				$metaValue = maybe_unserialize( $row['meta_value'] );

				// Force subkeys to be array type:
				if ( ! isset( self::$cache[ $entryID ] ) || ! is_array( self::$cache[ $entryID ] ) ) self::$cache[ $entryID ] = array();
				if ( ! isset( self::$cache[ $entryID ][ $metaID ] ) || ! is_array( self::$cache[ $entryID ][ $metaID ] ) ) self::$cache[ $entryID ][ $metaID ] = array();

				self::$cache[ $entryID ][ $metaID ] = array( 'meta_key' => $metaKey, 'meta_value' => $metaValue );
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

		if ( $unique && $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM CN_ENTRY_TABLE_META WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) ) ) {

			return FALSE;
		}

		do_action( "cn_add_meta-{ $type }", $id, $key, $value );

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		$result = $wpdb->insert(
			CN_ENTRY_TABLE_META,
			array(
				$column      => $id,
				'meta_key'   => $key,
				'meta_value' => maybe_serialize( $value ) )
		);

		if ( ! $result ) return FALSE;

		$metaID = (int) $wpdb->insert_id;

		do_action( "cn_added_meta-{ $type }", $metaID, $id, $key, $value );

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
	 * @uses do_action()
	 * @param  string $type   The oject type.
	 * @param  int    $id     The object ID.
	 * @param  int    $metaID The meta ID.
	 * @param  string $key    The metadata key.
	 * @param  string $value  The metadata value
	 *
	 * @return mixed          int | bool The number of affected rows or FALSE on failure.
	 */
	public static function update( $type, $id, $metaID, $key, $value ) {
		global $wpdb;

		if ( ! $type || ! $key ) return FALSE;
		if ( ! $id = absint( $id ) ) return FALSE;

		$column = sanitize_key( $type . '_id' );

		// The wp_unslash() is only available in WP >= 3.6 use stripslashes_deep() for backward compatibility.
		$key   = function_exists( 'wp_unslash' ) ? wp_unslash( $key )   : stripslashes_deep( $key );
		$value = function_exists( 'wp_unslash' ) ? wp_unslash( $value ) : stripslashes_deep( $value );

		do_action( "cn_update_meta-{ $type }", $id, $key, $value );

		$results = self::get( $type, $id );

		if ( $results[ $metaID ]['meta_key'] !== $key || $results[ $metaID ]['meta_value'] !== $value ) {

			// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
			$result = $wpdb->update(
				CN_ENTRY_TABLE_META,
				array(
					$column      => $id,
					'meta_key'   => $key,
					'meta_value' => maybe_serialize( $value ) ),
				array( 'meta_id' => $metaID )
			);

			do_action( "cn_updated_meta-{ $type }", $id, $key, $value );

			// Result will be FALSE on failure or the number of rows affected is successful.
			return $result;
		}

		return FALSE;
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

		do_action( "cn_delete_meta-{ $type }", $id, $key, $value );

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

		do_action( "cn_deleted_meta-{ $type }", $id, $key, $value );

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
	public static function key( $type, $limit =30, $unique = TRUE ) {
		global $wpdb;

		// $column = sanitize_key( $type . '_id' );

		$keys  = array();
		$limit = (int) apply_filters( "cn_metakey_limit-{ $type }", $limit );

		// Hard code the entry meta table for now. As other meta tables are added this will have to change based $type.
		// The query will not retrieve any meta key that begin with an '_' [underscore].
		$sql = $wpdb->prepare( 'SELECT meta_key FROM %1$s %2$s GROUP BY meta_key HAVING meta_key NOT LIKE \'\_%%\' ORDER BY meta_key LIMIT %3$d',
				CN_ENTRY_TABLE_META,
				empty( $key ) ? '' : ' WHERE meta_key IN ("' . implode( '", "', $keys ) . '") ',
				absint( $limit )
			);

		$keys = $wpdb->get_col( $sql );

		if ( $keys ) natcasesort( $keys );

		return $keys;
	}
}
