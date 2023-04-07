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
 * $fragment = new _fragment( 'unique-key', 'fragment-group' );
 *
 * if ( ! $fragment->get() ) {
 *
 *     functions_that_do_stuff_live();
 *     these_should_echo();
 *
 *     echo 'All output should be echoed';
 *
 *     // IMPORTANT: YOU CANNOT FORGET THIS. If you do, the site will break.
 *     $fragment->save();
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

	/**
	 * The default cache group prefix.
	 */
	const PREFIX = 'cn';

	/**
	 * The cache key.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The cache group.
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * The time to live value.
	 *
	 * @var int
	 */
	protected $ttl = WEEK_IN_SECONDS;

	/**
	 * The cached fragment or false if one was not found.
	 *
	 * @var false|mixed
	 */
	protected $fragment = '';

	/**
	 * Setup the fragment cache values.
	 *
	 * @since 8.1
	 *
	 * @param string $key   The cache key.
	 * @param string $group The fragment cache group that the fragment belongs to.
	 */
	public function __construct( $key, $group = '' ) {

		$this->key      = $key;
		$this->group    = $group;
		$this->fragment = _cache::get( $this->key, 'transient', $this->group );
	}

	/**
	 * Echo a cached fragment if found.
	 *
	 * @since 8.1
	 *
	 * @return bool
	 */
	public function get(): bool {

		if ( $this->exists() ) {

			echo $this->fragment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return true;

		} else {

			ob_start();
			return false;
		}
	}

	/**
	 * Whether a fragment exists.
	 *
	 * @since 10.4.40
	 *
	 * @return bool
	 */
	public function exists(): bool {

		if ( false !== $this->fragment ) {

			return true;
		}

		return false;
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

		_cache::clear(
			$key,
			'transient',
			empty( $group ) ? self::PREFIX : $group
		);
	}

	/**
	 * Return the object cache as string.
	 *
	 * @since 10.4.40
	 *
	 * @return string
	 */
	public function __toString() {

		return is_string( $this->fragment ) ? $this->fragment : '';
	}
}
