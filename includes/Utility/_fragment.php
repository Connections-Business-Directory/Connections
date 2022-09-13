<?php
/**
 * Store and retrieve HTML fragments.
 *
 * @package     Connections
 * @subpackage  Fragment
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.1
 */

namespace Connections_Directory\Utility;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fragment caching based on class by Mark Jaquith.
 *
 * @url http://markjaquith.wordpress.com/2013/04/26/fragment-caching-in-wordpress/
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
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
class _fragment {

	const PREFIX = 'cn';
	protected $key;
	protected $group;
	protected $ttl = WEEK_IN_SECONDS;

	/**
	 * Setup the fragment cache values.
	 *
	 * @since 8.1
	 *
	 * @param string $key   The cache key.
	 * @param string $group The fragment cache group that the fragment belongs to.
	 */
	public function __construct( $key, $group = '' ) {

		$this->key   = $key;
		$this->group = $group;
	}

	/**
	 * Echo a cached fragment if found.
	 *
	 * @since 8.1
	 *
	 * @return bool
	 */
	public function get() {

		$fragment = _cache::get( $this->key, 'transient', $this->group );

		if ( false !== $fragment ) {

			echo $fragment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return true;

		} else {

			ob_start();
			return false;
		}
	}

	/**
	 * Save fragment in the cache.
	 *
	 * @since 8.1
	 *
	 * @param int|null $ttl The number of seconds the fragment should live. Defaults to WEEK_IN_SECONDS.
	 */
	public function save( $ttl = null ) {

		$ttl = is_null( $ttl ) ? $this->ttl : $ttl;

		_cache::set( $this->key, ob_get_flush(), $ttl, 'transient', $this->group );
	}

	/**
	 * Clear a fragment cache object or object group.
	 *
	 * @since 8.1.6
	 *
	 * @param true|string $key   The cache key to clear. When set to TRUE, clear a fragment cache group.
	 * @param string      $group The cache group to clear.
	 */
	public static function clear( $key, $group = '' ) {

		if ( true !== $key ) {

			_cache::clear( $key, 'transient', self::PREFIX );

		} else {

			$group_key = empty( $group ) ? self::PREFIX : $group;

			_cache::clear( true, 'transient', $group_key );
		}

	}
}
