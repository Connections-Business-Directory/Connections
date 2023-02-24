<?php
/**
 * Polyfill for the `ctype_digit`.
 *
 * @since      10.4.39
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Polyfill
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

if ( ! function_exists( 'ctype_digit' ) ) :
	/**
	 * Returns TRUE if every character in the string text is a decimal digit, FALSE otherwise.
	 *
	 * @link https://php.net/ctype-digit
	 *
	 * @param mixed $text String to validate.
	 *
	 * @return bool
	 */
	function ctype_digit( $text ) {

		$return = false;

		if ( ( is_string( $text ) && '' !== $text ) && 1 === preg_match( '`^\d+$`', $text ) ) {
			$return = true;
		}

		return $return;
	}
endif;
