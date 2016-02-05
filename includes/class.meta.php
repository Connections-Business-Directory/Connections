<?php

/**
 * Metadata API.
 *
 * Provides methods to manage the meta data of the various Connections object types.
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
	 * Retrieve metadata for the specified object.
	 *
	 * NOTE: This is the Connections equivalent of @see get_metadata() in WordPress core ../wp-includes/meta.php
	 *
	 * @access public
	 * @since  8.1.7
	 * @static
	 *
	 * @uses   absint()
	 * @uses   apply_filters()
	 * @uses   wp_cache_get()
	 * @uses   cnMeta::updateCache()
	 * @uses   cnFormatting::maybeJSONdecode()
	 *
	 * @param string $type      Type of object metadata is for (e.g., entry, term).
	 * @param int    $id        ID of the object metadata is for.
	 * @param string $key       Optional. Metadata key. If not specified, retrieve all metadata for the specified object.
	 * @param bool   $single    Optional, default is FALSE. If true, return only the first value of the
	 *                          specified meta_key. This parameter has no effect if $key is not specified.
	 *
	 * @return bool|string|array Single metadata value, or array of values.
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
		 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 8.1.7
		 *
		 * @param null|array|string $value     The value should return a single metadata value, or an array of values.
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
	 * NOTE: This is the Connections equivalent of @see update_meta_cache() in WordPress core ../wp-includes/meta.php
	 *
	 * @access private
	 * @since  8.1.7
	 * @static
	 *
	 * @global wpdb     $wpdb       WordPress database abstraction object.
	 *
	 * @uses   cnMeta::tableName()
	 * @uses   sanitize_key()
	 * @uses   wp_cache_get()
	 * @uses   wpdb::get_results()
	 * @uses   wp_cache_add()
	 *
	 * @param string    $type       Type of object metadata is for (e.g., entry, term).
	 * @param int|array $object_ids array or comma delimited list of object IDs to update.
	 *
	 * @return mixed                array|bool Metadata for the specified objects, or FALSE on failure.
	 */
	public static function updateCache( $type, $object_ids ) {

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
	 * Add meta data to the supplied object type id.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @global wpdb  $wpdb    WordPress database abstraction object.
	 *
	 * @uses   absint()
	 * @uses   wp_unslash()
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
	 * @param string $type    The type of object the meta data is for; ie. entry and term.
	 * @param int    $id      The object ID.
	 * @param string $key     Metadata key.
	 * @param string $value   Metadata value.
	 * @param bool $unique    [optional] Whether the specified metadata key should be unique for the object.
	 *                        If TRUE, and the object already has a value for the specified metadata key, no change will be made.
	 *
	 * @return mixed          int|bool The metadata ID on successful insert or FALSE on failure.
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

		$key   = wp_unslash( $key );
		$value = wp_unslash( $value );
		$value = sanitize_meta( $key, $value, 'cn_' . $type );

		/**
		 * Filter whether to add metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 8.1.7
		 *
		 * @param null|bool $check  Whether to allow adding metadata for the given type.
		 * @param int       $id     Object ID.
		 * @param string    $key    Meta key.
		 * @param mixed $value      Meta value. Must be able to be json encoded if non-scalar.
		 *                          Use @see cnFormatting::maybeJSONencode().
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
		 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
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

		/**
		 * Fires immediately after meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
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
	 * Update metadata for the specified object. If no value already exists for the specified object
	 * ID and metadata key, the metadata will be added.
	 *
	 * NOTE: This is the Connections equivalent of @see update_metadata() in WordPress core ../wp-includes/meta.php
	 *
	 * @access public
	 * @since  8.1.7
	 * @static
	 *
	 * @global wpdb  $wpdb       WordPress database abstraction object.
	 *
	 * @uses   absint()
	 * @uses   cnMeta::tableName()
	 * @uses   sanitize_key()
	 * @uses   wp_unslash()
	 * @uses   sanitize_meta()
	 * @uses   apply_filters()
	 * @uses   cnMeta::get()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_col()
	 * @uses   cnMeta::add()
	 * @uses   cnFormatting::maybeJSONencode()
	 * @uses   do_action()
	 * @uses   wpdb::update()
	 * @uses   wp_cache_delete()
	 *
	 * @param string $type       Type of object metadata is for (e.g., entry, term)
	 * @param int    $id         ID of the object metadata is for
	 * @param string $key        Metadata key
	 * @param mixed  $value      Metadata value. Must be able to be JSON encoded if non-scalar.
	 *                           Use @see cnFormatting::maybeJSONencode().
	 * @param mixed  $prev_value Optional. If specified, only update existing metadata entries with
	 *                           the specified value. Otherwise, update all entries.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public static function update( $type, $id, $key, $value, $prev_value = '' ) {

		/** @var wpdb $wpdb */
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

		// expected_slashed ($meta_key)
		$key          = wp_unslash( $key );
		$passed_value = $value;
		$value        = wp_unslash( $value );
		$value        = sanitize_meta( $key, $value, 'cn_' . $type );

		/**
		 * Filter whether to update metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 8.1.7
		 *
		 * @param null|bool $check      Whether to allow updating metadata for the given type.
		 * @param int       $id         Object ID.
		 * @param string    $key        Meta key.
		 * @param mixed     $value      Meta value. Must be able to be JSON encoded if non-scalar.
		 *                              Use @see cnFormatting::maybeJSONencode().
		 * @param mixed     $prev_value Optional. If specified, only update existing
		 *                              metadata entries with the specified value.
		 *                              Otherwise, update all entries.
		 */
		$check = apply_filters(
			"cn_update_{$type}_metadata",
			NULL,
			$id,
			$key,
			$value,
			$prev_value
		);
		if ( NULL !== $check ) {
			return (bool) $check;
		}

		// Compare existing value to new value if no prev value given and the key exists only once.
		if ( empty( $prev_value ) ) {

			$old_value = self::get( $type, $id, $key );

			if ( count( $old_value ) == 1 ) {

				if ( $old_value[0] === $value ) {

					return FALSE;
				}
			}
		}

		$meta_ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT meta_id FROM $table WHERE meta_key = %s AND $column = %d", $key, $id )
		);

		if ( empty( $meta_ids ) ) {

			return self::add( $type, $id, $key, $passed_value );
		}

		$_meta_value = $value;
		$value       = cnFormatting::maybeJSONencode( $value );

		$data  = array( 'meta_value' => $value );
		$where = array( $column => $id, 'meta_key' => $key );

		if ( ! empty( $prev_value ) ) {

			$prev_value          = cnFormatting::maybeJSONencode( $prev_value );
			$where['meta_value'] = $prev_value;
		}

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately before updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
			 *
			 * @since 8.1.7
			 *
			 * @param int    $meta_id ID of the metadata entry to update.
			 * @param int    $id      Object ID.
			 * @param string $key     Meta key.
			 * @param mixed  $value   Meta value.
			 */
			do_action( "cn_update_{$type}_meta", $meta_id, $id, $key, $_meta_value );
		}

		$result = $wpdb->update( $table, $data, $where );

		if ( ! $result ) {

			return FALSE;
		}

		wp_cache_delete( $id, 'cn_' . $type . '_meta' );

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately after updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
			 *
			 * @since 8.1.7
			 *
			 * @param int    $meta_id ID of updated metadata entry.
			 * @param int    $id      Object ID.
			 * @param string $key     Meta key.
			 * @param mixed  $value   Meta value.
			 */
			do_action( "cn_updated_{$type}_meta", $meta_id, $id, $key, $_meta_value );
		}

		return TRUE;
	}

	/**
	 * Delete metadata for the specified object.
	 *
	 * NOTE: This is the Connections equivalent of @see delete_metadata() in WordPress core ../wp-includes/meta.php
	 *
	 * @access public
	 * @since  8.1.7
	 * @static
	 *
	 * @uses   absint()
	 * @uses   cnMeta::tableName()
	 * @uses   sanitize_key()
	 * @uses   wp_unslash()
	 * @uses   cnFormatting::maybeJSONencode()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_col()
	 * @uses   do_action()
	 * @uses   wpdb::query()
	 * @uses   wp_cache_delete()
	 *
	 * @global wpdb  $wpdb       WordPress database abstraction object.
	 *
	 * @param string $type       Type of object metadata is for (e.g., entry, term).
	 * @param int    $id         ID of the object metadata is for.
	 * @param string $key        Metadata key.
	 * @param mixed  $value      Optional. Metadata value. Must be able to be JSON encoded if non-scalar. If specified,
	 *                           only delete metadata entries with this value. Otherwise, delete all entries with the
	 *                           specified $key.
	 * @param bool   $delete_all Optional, default is FALSE. If true, delete matching metadata entries
	 *                           for all objects, ignoring the specified $id. Otherwise, only delete matching
	 *                           metadata entries for the specified $id.
	 *
	 * @return bool              TRUE on successful delete, FALSE on failure.
	 */
	public static function delete( $type, $id, $key, $value = '', $delete_all = FALSE ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! $type || ! $key || ! is_numeric( $id ) && ! $delete_all ) {
			return FALSE;
		}

		$id = absint( $id );
		if ( ! $id && ! $delete_all ) {
			return FALSE;
		}

		$table = self::tableName( $type );

		$type_column = sanitize_key( $type . '_id' );
		$key         = wp_unslash( $key );
		$value       = wp_unslash( $value );

		/**
		 * Filter whether to delete metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 8.1.7
		 *
		 * @param null|bool $delete     Whether to allow metadata deletion of the given type.
		 * @param int       $id         Object ID.
		 * @param string    $key        Meta key.
		 * @param mixed     $value      Meta value. Must be able to be JSON encoded if non-scalar.
		 *                              Use @see cnFormatting::maybeJSONencode().
		 * @param bool $delete_all      Whether to delete the matching metadata entries for all objects,
		 *                              ignoring the specified $id.
		 *                              Default FALSE.
		 */
		$check = apply_filters( "cn_delete_{$type}_metadata", NULL, $id, $key, $value, $delete_all );
		if ( NULL !== $check ) {
			return (bool) $check;
		}

		$_meta_value = $value;
		$value       = cnFormatting::maybeJSONencode( $value );

		$query = $wpdb->prepare( "SELECT meta_id FROM $table WHERE meta_key = %s", $key );

		if ( ! $delete_all ) {
			$query .= $wpdb->prepare( " AND $type_column = %d", $id );
		}

		if ( $value ) {
			$query .= $wpdb->prepare( " AND meta_value = %s", $value );
		}

		$meta_ids = $wpdb->get_col( $query );
		if ( ! count( $meta_ids ) ) {
			return FALSE;
		}

		if ( $delete_all ) {
			$object_ids = $wpdb->get_col(
				$wpdb->prepare( "SELECT $type_column FROM $table WHERE meta_key = %s", $key )
			);
		}

		/**
		 * Fires immediately before deleting metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$type`, refers to the meta object type (entry, term).
		 *
		 * @since 8.1.7
		 *
		 * @param array  $meta_ids An array of metadata IDs to delete.
		 * @param int    $id       Object ID.
		 * @param string $key      Meta key.
		 * @param mixed  $value    Meta value.
		 */
		do_action( "cn_delete_{$type}_meta", $meta_ids, $id, $key, $_meta_value );

		$query = "DELETE FROM $table WHERE meta_id IN( " . implode( ',', $meta_ids ) . " )";

		$count = $wpdb->query( $query );

		if ( ! $count ) {
			return FALSE;
		}

		if ( $delete_all && isset( $object_ids ) ) {

			foreach ( (array) $object_ids as $o_id ) {

				wp_cache_delete( $o_id, 'cn_' . $type . '_meta' );
			}

		} else {

			wp_cache_delete( $id, 'cn_' . $type . '_meta' );
		}

		/**
		 * Fires immediately after deleting metadata of a specific type.
		 *
		 * The dynamic portion of the hook name, `$type`, refers to the meta object type (entry, term).
		 *
		 * @since 8.1.7
		 *
		 * @param array  $meta_ids An array of deleted metadata entry IDs.
		 * @param int    $id       Object ID.
		 * @param string $key      Meta key.
		 * @param mixed  $value    Meta value.
		 */
		do_action( "cn_deleted_{$type}_meta", $meta_ids, $id, $key, $_meta_value );

		return TRUE;
	}

	/**
	 * Get meta by ID.
	 *
	 * NOTE: This is the Connections equivalent of @see get_metadata_by_mid() in WordPress core ../wp-includes/meta.php
	 *
	 * @access public
	 * @since  8.1.7
	 * @static
	 *
	 * @global wpdb  $wpdb  WordPress database abstraction object.
	 *
	 * @uses   absint()
	 * @uses   cnMeta::tableName()
	 * @uses   wpdb::prepare()
	 * @uses   wpdb::get_row()
	 * @uses   cnFormatting::maybeJSONdecode()
	 *
	 * @param string $type Type of object metadata is for (e.g., entry, term)
	 * @param int    $id   ID for a specific meta row
	 *
	 * @return mixed object|bool Meta object or FALSE.
	 */
	public static function getByID( $type, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! $type || ! is_numeric( $id ) ) {
			return FALSE;
		}

		$id = absint( $id );
		if ( ! $id ) {
			return FALSE;
		}

		$table = self::tableName( $type );

		$meta = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE meta_id = %d", $id ) );

		if ( empty( $meta ) ) {
			return FALSE;
		}

		if ( isset( $meta->meta_value ) ) {

			$meta->meta_value = cnFormatting::maybeJSONdecode( $meta->meta_value );
		}

		return $meta;
	}

	/**
	 * Update meta by ID.
	 *
	 * NOTE: This is the Connections equivalent of @see update_metadata_by_mid() in WordPress core ../wp-includes/meta.php
	 *
	 * @access public
	 * @since  8.1.7
	 * @static
	 *
	 * @global wpdb  $wpdb  WordPress database abstraction object.
	 *
	 * @uses   cnMeta::tableName()
	 * @uses   cnMeta::getByID()
	 * @uses   sanitize_meta()
	 * @uses   cnFormatting::maybeJSONencode()
	 * @uses   do_action()
	 * @uses   wpdb::update()
	 * @uses   wp_cache_delete()
	 *
	 * @param string $type  Type of object metadata is for (e.g., entry, term).
	 * @param int    $id    ID for a specific meta row.
	 * @param string $value Metadata value.
	 * @param mixed  $key   string|bool Optional, you can provide a meta key to update it.
	 *
	 * @return bool         TRUE on successful update, FALSE on failure.
	 */
	public static function updateByID( $type, $id, $value, $key = FALSE ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		// Make sure everything is valid.
		if ( ! $type || ! is_numeric( $id ) ) {

			return FALSE;
		}

		$id = absint( $id );
		if ( ! $id ) {
			return FALSE;
		}

		$table     = self::tableName( $type );
		$column    = sanitize_key( $type . '_id' );
		$id_column = 'meta_id';

		// Fetch the meta and go on if it's found.
		if ( $meta = self::getByID( $type, $id ) ) {

			$original_key = $meta->meta_key;
			$object_id    = $meta->{$column};

			// If a new meta_key (last parameter) was specified, change the meta key,
			// otherwise use the original key in the update statement.
			if ( FALSE === $key ) {

				$key = $original_key;

			} elseif ( ! is_string( $key ) ) {

				return FALSE;
			}

			// Sanitize the meta
			$_meta_value = $value;
			$value       = wp_unslash( $value );
			$value       = sanitize_meta( $key, $value, 'cn_' . $type );
			$value       = cnFormatting::maybeJSONencode( $value );

			// Format the data query arguments.
			$data = array(
				'meta_key'   => $key,
				'meta_value' => $value
			);

			// Format the where query arguments.
			$where               = array();
			$where[ $id_column ] = $id;

			/** This action is documented in includes/class.meta.php */
			do_action( "cn_update_{$type}_meta", $id, $object_id, $key, $_meta_value );

			// Run the update query, all fields in $data are %s, $where is a %d.
			$result = $wpdb->update( $table, $data, $where, '%s', '%d' );
			if ( ! $result ) {
				return FALSE;
			}

			// Clear the caches.
			wp_cache_delete( $object_id, 'cn_' . $type . '_meta' );

			/** This action is documented in includes/class.meta.php */
			do_action( "cn_updated_{$type}_meta", $id, $object_id, $key, $_meta_value );

			return TRUE;
		}

		// And if the meta was not found.
		return FALSE;
	}

	/**
	 * Delete meta data by meta ID.
	 *
	 * NOTE: This is the Connections equivalent of @see delete_metadata_by_mid() in WordPress core ../wp-includes/meta.php
	 *
	 * @access public
	 * @since  8.1.7
	 * @static
	 *
	 * @global wpdb  $wpdb WordPress database abstraction object.
	 *
	 * @uses   absint()
	 * @uses   sanitize_key()
	 * @uses   cnMeta::tableName()
	 * @uses   cnMeta::getByID()
	 * @uses   wpdb::delete()
	 * @uses   do_action()
	 *
	 * @param string $type Type of object metadata is for (e.g., entry, term)
	 * @param int    $id   ID for a specific meta row
	 *
	 * @return bool True on successful delete, false on failure.
	 */
	public static function deleteByID( $type, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		// Make sure everything is valid.
		if ( ! $type || ! is_numeric( $id ) ) {
			return FALSE;
		}

		$id = absint( $id );
		if ( ! $id ) {
			return FALSE;
		}

		$table = cnMeta::tableName( $type );

		// object and id columns
		$column = sanitize_key( $type . '_id' );

		// Fetch the meta and go on if it's found.
		if ( $meta = cnMeta::getByID( $type, $id ) ) {

			$object_id = $meta->{$column};

			/** This action is documented in wp-includes/meta.php */
			do_action( "cn_delete_{$type}_meta", (array) $id, $object_id, $meta->meta_key, $meta->meta_value );

			// Run the query, will return true if deleted, false otherwise
			$result = (bool) $wpdb->delete( $table, array( 'meta_id' => $id ) );

			// Clear the caches.
			wp_cache_delete( $object_id, 'cn_' . $type . '_meta' );

			/** This action is documented in wp-includes/meta.php */
			do_action( "cn_deleted_{$type}_meta", (array) $id, $object_id, $meta->meta_key, $meta->meta_value );

			return $result;
		}

		return FALSE;
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
	 * @return array         An array of meta keys.
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
	 * @access public
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
	public static function tableName( $type ) {

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

/**
 * Class cnMeta_Query extends the @see WP_Meta_Query overriding the @see WP_Meta_Query::get_sql() method so the custom
 * tables used by Connections for entry and term meta can be used.
 */
class cnMeta_Query extends WP_Meta_Query {

	/**
	 * Generates SQL clauses to be added to the query.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $type              Type of meta, eg 'entry', 'term'.
	 * @param string $primary_table     Database table where the object being filtered is stored (eg CN_ENTRY_TABLE).
	 * @param string $primary_id_column ID column for the filtered object in $primary_table.
	 * @param mixed  $context           object|null Optional. The main query object.
	 *
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	public function get_sql( $type, $primary_table, $primary_id_column, $context = null ) {

		$this->meta_table     = cnMeta::tableName( $type );
		$this->meta_id_column = sanitize_key( $type . '_id' );

		$this->primary_table     = $primary_table;
		$this->primary_id_column = $primary_id_column;

		$sql = $this->get_sql_clauses();

		/*
		 * If any JOINs are LEFT JOINs (as in the case of NOT EXISTS), then all JOINs should
		 * be LEFT. Otherwise posts with no metadata will be excluded from results.
		 */
		if ( false !== strpos( $sql['join'], 'LEFT JOIN' ) ) {
			$sql['join'] = str_replace( 'INNER JOIN', 'LEFT JOIN', $sql['join'] );
		}

		/**
		 * Filter the meta query's generated SQL.
		 *
		 * @since 8.2.5
		 *
		 * @param array $args {
		 *     An array of meta query SQL arguments.
		 *
		 *     @type array  $clauses           Array containing the query's JOIN and WHERE clauses.
		 *     @type array  $queries           Array of meta queries.
		 *     @type string $type              Type of meta.
		 *     @type string $primary_table     Primary table.
		 *     @type string $primary_id_column Primary column ID.
		 *     @type object $context           The main query object.
		 * }
		 */
		return apply_filters_ref_array( 'cn_get_meta_sql', array( $sql, $this->queries, $type, $primary_table, $primary_id_column, $context ) );
	}
}
