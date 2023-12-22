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
	 * Is file a JSON file.
	 *
	 * @since 10.4.33
	 *
	 * @param string $path     The full path file to check.
	 * @param string $filename The name of the file (may differ from $path due to $path being in a tmp directory).
	 *
	 * @return bool
	 * @noinspection PhpComposerExtensionStubsInspection
	 */
	public static function isFileJSON( $path, $filename ) {

		/**
		 * @link https://core.trac.wordpress.org/ticket/45633
		 * @link https://gist.github.com/christianwach/edebf9cb3cf1b412fb835dff73f09357
		 *
		 * @param array       $info      The existing file data array.
		 * @param string      $file      Full path to the file.
		 * @param string      $filename  The name of the file.
		 * @param array       $mimes     Key is the file extension with value as the mime type.
		 * @param string|bool $real_mime The actual mime type or false if the type cannot be determined.
		 *
		 * @return array $info The modified file data array.
		 */
		$callback = function ( $info, $file, $filename, $mimes, $real_mime ) {

			// Get filetype data.
			$wp_filetype     = wp_check_filetype( $filename, $mimes );
			$ext             = $wp_filetype['ext'];
			$type            = $wp_filetype['type'];
			$proper_filename = $wp_filetype['proper_filename'];

			// Use finfo_file if available to validate non-image files.
			if ( empty( $real_mime ) && function_exists( 'finfo_file' ) ) {
				$finfo     = finfo_open( FILEINFO_MIME_TYPE );
				$real_mime = finfo_file( $finfo, $file );
				finfo_close( $finfo );
			}

			// If the extension matches an alternate mime type, let's use it.
			if ( ! in_array( $real_mime, array( 'application/json', 'text/plain', 'text/html' ) ) ) {
				$ext  = false;
				$type = false;
			}

			return compact( 'ext', 'type', 'proper_filename' );
		};

		// Add filter for the `$callback`.
		add_filter( 'wp_check_filetype_and_ext', $callback, 10, 5 );

		$filetype = wp_check_filetype_and_ext( $path, $filename, array( 'json' => 'application/json' ) );
		$ext      = empty( $filetype['ext'] ) ? '' : $filetype['ext'];
		$type     = empty( $filetype['type'] ) ? '' : $filetype['type'];

		// Remove filter.
		remove_filter( 'wp_check_filetype_and_ext', $callback );

		if ( ! $type || ! $ext ) {

			return false;
		}

		$json = file_get_contents( $path );

		return self::isJSON( $json );
	}

	/**
	 * Whether the supplied string is valid JSON.
	 *
	 * @since 10.4.33
	 *
	 * @param string $value The string to validate.
	 *
	 * @return bool
	 */
	public static function isJSON( $value ) {

		return json_validate( $value );
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
	 * Example:
	 * '' === false
	 * ' ' === false
	 * '1' === true
	 * '0' === true
	 * '-1' === true
	 * 1 === true
	 * 0 === true
	 * -1 === true
	 * '00' === true
	 * '01' === true
	 * 1.0 === true
	 * '1.0' === true
	 * true === false
	 * false === false
	 * null === false
	 * 0x24 === true
	 * 1337e0 === true
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

		// return false !== filter_var( $value, FILTER_VALIDATE_INT );
		return is_numeric( $value ) && ( floatval( $value ) % 1 === 0 );
	}

	/**
	 * Validate that the supplied value is a string and not empty.
	 *
	 * Example:
	 * '' === false
	 * ' ' === true
	 * '1' === true
	 * '0' === true
	 * 1 === false
	 * 0 === false
	 * true === false
	 * false === false
	 * null === false
	 *
	 * @since 10.4.35
	 *
	 * @param string $value String to check.
	 *
	 * @return bool
	 */
	public static function isStringNotEmpty( $value ) {

		return is_string( $value ) && '' !== $value;
	}

	/**
	 * Determine if supplied value is a positive integer.
	 *
	 * Negative integers will return `false`.
	 *
	 * Example:
	 * '' === false
	 * ' ' === false
	 * '1' === true
	 * '0' === true
	 * '-1' === false
	 * 1 === true
	 * 0 === true
	 * -1 === false
	 * '00' === true
	 * '01' === true
	 * 1.0 === true
	 * '1.0' === true
	 * true === false
	 * false === false
	 * null === false
	 * 0x24 === true
	 * 1337e0 === true
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

		// return ! is_bool( $value ) && ctype_digit( strval( $value ) );
		return is_numeric( $value ) && ( floatval( $value ) >= 0 ) && ( floatval( $value ) % 1 === 0 );
	}
}
