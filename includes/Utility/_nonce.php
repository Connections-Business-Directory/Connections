<?php
/**
 * Helper methods for nonce.
 *
 * Adds a namespace to the nonce action and uses custom query argument.
 *
 * @since 10.4.29
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Utility\_nonce
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Utility;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class _nonce
 *
 * @package Connections_Directory\Utility
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _nonce {

	/**
	 * The query argument nonce name.
	 *
	 * @since 10.4.29
	 */
	const NAME = '_cnonce';

	/**
	 * Generate the complete nonce string, from the nonce base, the action and an item.
	 *
	 * @since 10.4.29
	 *
	 * @param string      $action Action name.
	 * @param null|string $item   Item name. Use when protecting multiple items on the same page.
	 *
	 * @return string
	 */
	public static function action( $action, $item = null ) {

		$namespace = 'Connections_Directory';

		return is_scalar( $item ) ? "{$namespace}/{$action}-{$item}" : "{$namespace}/{$action}";
	}

	/**
	 * Create a nonce using the namespace action and item names.
	 *
	 * @since 10.4.31
	 *
	 * @param string      $action Action name.
	 * @param null|string $item   Item name. Use when protecting multiple items on the same page.
	 *
	 * @return false|string
	 */
	public static function create( $action, $item = null ) {

		$action = self::action( $action, $item );

		return wp_create_nonce( $action );
	}

	/**
	 * Retrieves or displays the nonce field for forms using {@see wp_nonce_field()}.
	 *
	 * @since 10.4.29
	 *
	 * @param string      $action  Action name.
	 * @param null|string $item    Item name. Use when protecting multiple items on the same page.
	 * @param null|string $name    Nonce name.
	 * @param bool        $referer Whether to set and display the referrer field for validation.
	 * @param bool        $echo    Whether to display or return the form field.
	 *
	 * @return string Nonce field HTML markup.
	 */
	public static function field( $action, $item = null, $name = null, $referer = true, $echo = true ) {

		$action = self::action( $action, $item );
		$name   = is_scalar( $name ) ? $name : self::NAME;
		$field  = wp_nonce_field( $action, $name, $referer, false );

		if ( $echo ) {
			echo $field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $field;
	}

	/**
	 * Retrieves URL with nonce added to the query string.
	 *
	 * @since 10.4.29
	 *
	 * @param string      $url    URL to add nonce action.
	 * @param int|string  $action Nonce action name.
	 * @param null|string $item   Item name. Use when protecting multiple items on the same page.
	 * @param null|string $name   The nonce name.
	 *
	 * @return string Escaped URL with nonce action added.
	 */
	public static function url( $url, $action, $item = null, $name = null ) {

		$action = self::action( $action, $item );
		$name   = is_scalar( $name ) ? $name : self::NAME;

		return wp_nonce_url( $url, $action, $name );
	}
}
