<?php
/**
 * Helper methods to sanitize user input.
 *
 * @package Connections_Directory\Utility
 */

namespace Connections_Directory\Utility;

/**
 * Class _sanitize
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _sanitize {

	/**
	 * Sanitizes a string character.
	 *
	 * @since 10.4.4
	 *
	 * @param string $character The character.
	 *
	 * @return string Sanitized character.
	 */
	public static function character( $character ) {

		if ( ! is_scalar( $character ) || ( ! empty( $character ) && 1 !== mb_strlen( $character ) ) ) {
			$character = '';
		}

		return sanitize_text_field( $character );
	}

	/**
	 * Sanitizes a string key.
	 *
	 * Keys are used as internal identifiers.
	 * Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed.
	 *
	 * @since  10.4.4
	 *
	 * @param string $key String key.
	 *
	 * @return string Sanitized key.
	 */
	public static function key( $key ) {

		$rawKey = $key;
		$key    = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

		/**
		 * Filter a sanitized key string.
		 *
		 * @since 10.4.4
		 *
		 * @param string $key    Sanitized key.
		 * @param string $rawKey The key prior to sanitization.
		 */
		return apply_filters( 'Connections_Directory/Utility/Sanitize/Key', $key, $rawKey );
	}

	/**
	 * Sanitize a file path.
	 *
	 * @since 10.4.4
	 *
	 * @param string $value The file path to sanitize.
	 *
	 * @return string
	 */
	public static function filepath( $value ) {

		$filepath = pathinfo( $value, PATHINFO_DIRNAME );
		$filename = sanitize_file_name( pathinfo( $value, PATHINFO_BASENAME ) );

		$path = realpath( $filepath . DIRECTORY_SEPARATOR . $filename );

		return is_string( $path ) ? $path : '';
	}

	/**
	 * Sanitizes a hex color.
	 *
	 * Returns either '', a 3 or 6 digit hashed (#) hex color.
	 *
	 * Use this over the core WP `sanitize_hex_color()` because
	 * this will return a string instead of void if it fails,
	 * and it provides a fallback option.
	 *
	 * @see sanitize_hex_color()
	 *
	 * @since 10.4.19
	 *
	 * @param string $color    The hashed (#) hex color to sanitize.
	 * @param string $fallback Optional. The value to return if the sanitization fails.
	 *                         Default: An empty string.
	 *
	 * @return string
	 */
	public static function hexColor( $color, $fallback = '' ) {

		$sanitized = $fallback;

		// Returns 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {

			$sanitized = $color;
		}

		return $sanitized;
	}

	/**
	 * Sanitizes search term.
	 *
	 * @since 10.4.4
	 *
	 * @param string $term The search term to sanitize.
	 *
	 * @return string Sanitized search term.
	 */
	public static function search( $term ) {

		// Fairly large, potentially too large, upper bound for search string lengths.
		if ( ! is_scalar( $term ) || ( ! empty( $term ) && mb_strlen( $term ) > 1600 ) ) {
			$term = '';
		}

		return sanitize_text_field( $term );
	}
}
