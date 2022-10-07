<?php
/**
 * Helper methods to validate user input.
 *
 * @package Connections_Directory\Utility
 */

namespace Connections_Directory\Utility;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class _sanitize
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _validate {

	/**
	 * Helper function for {@see check_admin_referer()} that will use {@see _nonce::NAME} query argument as the default.
	 *
	 * @since 10.4.29
	 *
	 * @param string      $action        Nonce action name.
	 * @param null|string $item          Item name. Use when protecting multiple items on the same page.
	 * @param null|string $queryArgument Key to check for nonce in `$_REQUEST`.
	 *
	 * @return false|int|null
	 */
	public static function adminReferer( $action, $item = null, $queryArgument = null ) {

		$nonceAction   = is_scalar( $item ) ? _nonce::action( $action, $item ) : _nonce::action( $action );
		$queryArgument = is_scalar( $queryArgument ) ? $queryArgument : _nonce::NAME;

		return check_admin_referer( $nonceAction, $queryArgument );
	}

	/**
	 * Helper function for {@see check_ajax_referer()} that will use {@see _nonce::NAME} query argument as the default.
	 *
	 * @since 10.4.29
	 *
	 * @param string      $action        Nonce action name.
	 * @param null|string $item          Item name. Use when protecting multiple items on the same page.
	 * @param null|string $queryArgument Key to check for nonce in `$_REQUEST`.
	 * @param bool        $die           Whether to die early when the nonce cannot be verified.
	 *
	 * @return false|int|null
	 */
	public static function ajaxReferer( $action, $item = null, $queryArgument = null, $die = true ) {

		$nonceAction   = is_scalar( $item ) ? _nonce::action( $action, $item ) : _nonce::action( $action );
		$queryArgument = is_scalar( $queryArgument ) ? $queryArgument : _nonce::NAME;

		return check_ajax_referer( $nonceAction, $queryArgument, $die );
	}

	/**
	 * Is file a CSV file.
	 *
	 * @since 10.4.4
	 *
	 * @param string $file The full path file to check.
	 *
	 * @return bool
	 */
	public static function isCSV( $file ) {

		/**
		 * Filter CSV valid file types.
		 *
		 * @since 3.6.5
		 *
		 * @param array $mimeTypes List of valid file types.
		 */
		$mimeTypes = apply_filters(
			'Connections_Directory/Utility/Validate/isCSV/MIME_Types',
			array(
				'csv' => 'text/csv',
				'txt' => 'text/plain',
			)
		);

		$filetype = wp_check_filetype( $file, $mimeTypes );

		if ( in_array( $filetype['type'], $mimeTypes, true ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Whether the supplied value is a float.
	 *
	 * @since 10.4.6
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool
	 */
	public static function isFloat( $value ) {

		return ! ( false === filter_var( $value, FILTER_VALIDATE_FLOAT ) );
	}

	/**
	 * Whether the supplied value is a hashed (#) hex color.
	 *
	 * @since 10.4.19
	 *
	 * @param string $color The hashed (#) hex color to validate.
	 *
	 * @return bool
	 */
	public static function isHexColor( $color ) {

		$sanitized = _sanitize::hexColor( $color );

		return ! empty( $sanitized );
	}

	/**
	 * Determine if supplied value is an integer.
	 *
	 * Reliable consistent method vs `is_int()`.
	 *
	 * @link  https://stackoverflow.com/a/29018655/5351316
	 *
	 * @since 10.4.1
	 *
	 * @param int|string $value Value to validate.
	 *
	 * @return bool
	 */
	public static function isInteger( $value ) {

		return false !== filter_var( $value, FILTER_VALIDATE_INT );
	}

	/**
	 * Determine if supplied value is a positive integer.
	 *
	 * Negative integers will return `false`.
	 *
	 * @link  https://stackoverflow.com/a/29018655/5351316
	 *
	 * @since 10.4.1
	 *
	 * @param int|string $value Value to validate.
	 *
	 * @return bool
	 */
	public static function isPositiveInteger( $value ) {

		return ctype_digit( strval( $value ) );
	}
}
