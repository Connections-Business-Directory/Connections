<?php
/**
 * This can be used in place of {@see _nonce} on the site frontend for page cache friendly "nonce".
 *
 * Example usage would be a contact form as a hidden field as `one` method of validation before processing a form submission.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Utility\_token
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Utility;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class _token
 *
 * @package Connections_Directory\Utility
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _token {

	/**
	 * Create the token.
	 *
	 * @since 10.4.31
	 *
	 * @param string $action Action name.
	 *
	 * @return false|string
	 */
	public static function create( $action ) {

		return substr( wp_hash( $action, 'nonce' ), -12, 10 );
	}

	/**
	 * Verify the token is valid.
	 *
	 * @since 10.4.31
	 *
	 * @param string $token  Token generated via {@see _token::create()}.
	 * @param string $action Action name.
	 *
	 * @return bool
	 */
	public static function isValid( $token, $action ) {

		return substr( wp_hash( $action, 'nonce' ), -12, 10 ) === $token;
	}
}
