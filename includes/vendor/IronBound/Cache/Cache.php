<?php
/**
 * Cache objects using wp object cache.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\Cache;

/**
 * Class Cache
 * @package IronBound\Cache
 */
final class Cache {

	/**
	 * Add an item to the cache.
	 *
	 * Returns false if item is already in the cache.
	 *
	 * @since 1.0
	 *
	 * @param Cacheable $object
	 *
	 * @return boolean
	 */
	public static function add( Cacheable $object ) {
		return wp_cache_add( $object->get_pk(), $object->get_data_to_cache(), $object::get_cache_group() );
	}

	/**
	 * Update an item in the cache.
	 *
	 * @since 1.0
	 *
	 * @param Cacheable $object
	 *
	 * @return bool
	 */
	public static function update( Cacheable $object ) {
		return wp_cache_set( $object->get_pk(), $object->get_data_to_cache(), $object::get_cache_group() );
	}

	/**
	 * Get an item from the cache.
	 *
	 * Returns false if item does not exist.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk    Primary key.
	 * @param string     $group Group name. ( default "df-{$table_slug}" )
	 *
	 * @return bool|array
	 */
	public static function get( $pk, $group ) {
		return wp_cache_get( $pk, $group );
	}

	/**
	 * Delete an item from the cache.
	 *
	 * @since 1.0
	 *
	 * @param Cacheable $object
	 *
	 * @return bool
	 */
	public static function delete( Cacheable $object ) {
		return wp_cache_delete( $object->get_pk(), $object::get_cache_group() );
	}
}
