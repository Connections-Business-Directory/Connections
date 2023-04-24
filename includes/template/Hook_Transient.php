<?php
/**
 * The `[connections]` shortcode can be added to the page multiple times with different templates.
 *
 * Hooks registered with this will be removed after each shortcode instance to ensure
 * each template specific hooks are run in each shortcode instance.
 *
 * @since 10.4.40
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Template\Hook_Transient
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare(strict_types=1);
namespace Connections_Directory\Template;

use Connections_Directory\Utility\_array;

/**
 * Class Hook_Transient
 *
 * @package Connections_Directory\Template
 */
final class Hook_Transient {

	/**
	 * An array of hook names.
	 *
	 * @since 10.4.40
	 * @var string[]
	 */
	private $hooks = array();

	/**
	 * Object instance.
	 *
	 * @since 10.4.40
	 * @var self
	 */
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 10.4.40
	 */
	protected function __construct() {
		/* Do nothing here */
	}

	/**
	 * Get the object instance.
	 *
	 * @since 10.4.40
	 *
	 * @return self
	 */
	public static function instance(): self {

		if ( ! self::$instance instanceof self ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add a nook name to the transient template hooks.
	 *
	 * @since 10.4.40
	 *
	 * @param string $hook      The hook name.
	 * @param string $slug      The template slug.
	 * @param string $separator The separator to use when concatenating the hook name and template slug.
	 */
	public function add( string $hook, string $slug = '', string $separator = '-' ) {

		if ( 0 < strlen( $slug ) ) {

			$hook = $hook . $separator . $slug;
		}

		if ( ! in_array( $hook, self::instance()->hooks, true ) ) {

			self::instance()->hooks[] = $hook;
		}
	}

	/**
	 * Clear the transient hooks.
	 *
	 * @since 10.4.40
	 */
	public function clear() {

		global $wp_filter;

		foreach ( self::instance()->hooks as $hook ) {

			if ( _array::exists( $wp_filter, $hook ) ) {

				unset( $wp_filter[ $hook ] );
			}
		}
	}
}
