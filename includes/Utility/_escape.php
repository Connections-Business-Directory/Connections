<?php

namespace Connections_Directory\Utility;

/**
 * Class _escape
 *
 * @package Connections_Directory\Utility
 */
final class _escape {

	/**
	 * Wrapper function for core WordPress function @see esc_attr()
	 *
	 * @since 10.4
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	public static function attribute( $attribute ) {

		return esc_attr( $attribute );
	}

	/**
	 * Escape HTML class name or array of class names.
	 *
	 * @since 10.4
	 *
	 * @param array|string $classNames
	 * @param string       $delimiter
	 *
	 * @return string
	 */
	public static function classNames( $classNames, $delimiter = ' ' ) {

		if ( ! is_array( $classNames ) ) {

			$classNames = explode( $delimiter, $classNames );
		}

		$classNames = array_map( 'sanitize_html_class', $classNames );
		$escaped    = array_map( 'esc_attr', $classNames );

		// Remove any empty array values.
		$escaped = array_filter( $escaped );
		$escaped = array_unique( $escaped );

		return implode( ' ', $escaped );
	}

	/**
	 * KSES Strips Evil Scripts; ensures that only the allowed HTML element names, attribute names, attribute values,
	 * and HTML entities will occur in the given text string.
	 *
	 * @since 10.4
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public static function html( $html ) {

		static $callback = null;

		if ( is_null( $callback ) ) {

			$callback = function( $tags, $context ) {
				global $allowedposttags;

				if ( 'Connections_Directory/Escape/HTML' === $context ) {

					return apply_filters( 'Connections_Directory/Utility/Escape/HTML', $allowedposttags );
				}

				return $tags;
			};
		}

		if ( false === has_filter( 'wp_kses_allowed_html', $callback ) ) {

			add_filter( 'wp_kses_allowed_html', $callback, 10, 2 );
		}

		return wp_kses( (string) $html, 'Connections_Directory/Escape/HTML' );
	}

	/**
	 * Escape HTML id attribute.
	 *
	 * @since 10.4
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	public static function id( $attribute ) {

		return esc_attr( _string::replaceWhatWith( $attribute, ' ', '-' ) );
	}

	/**
	 * Wrapper function for core WordPress function @see tag_escape()
	 *
	 * @since 10.4
	 *
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function tagName( $tag ) {

		return tag_escape( $tag );
	}
}
