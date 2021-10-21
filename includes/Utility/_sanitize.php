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
	 * @param string $path The file path to sanitize.
	 *
	 * @return string
	 */
	public static function filePath( $path ) {

		$path = sanitize_text_field( $path );

		return realpath( $path );
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
