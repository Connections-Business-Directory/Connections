<?php

namespace Connections_Directory\Utility;

/**
 * Class _escape
 *
 * @package Connections_Directory\Utility
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _escape {

	/**
	 * Wrapper function for core WordPress function @see esc_attr()
	 *
	 * @since 10.4
	 *
	 * @param string $attribute The HTML attribute value to escape.
	 * @param bool   $echo      Whether to echo the escaped value.
	 *
	 * @return string
	 */
	public static function attribute( $attribute, $echo = false ) {

		$attribute = esc_attr( $attribute );

		self::maybeEcho( $attribute, $echo );

		return $attribute;
	}

	/**
	 * Escape HTML class name or array of class names.
	 *
	 * @since 10.4
	 *
	 * @param array|string $classNames An array of or string of class names to escape.
	 * @param string       $delimiter  The string delimiter if the class names are provided as a string.
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
	 * Escape the CSS property and values. Useful for inline style attribute and style tag.
	 *
	 * Wrapper function for core WordPress function @see safecss_filter_attr()
	 *
	 * @since 10.4.6
	 *
	 * @param string $css  A string of CSS rules.
	 *                     Example: 'color: #000000; background-color: #FFFFFF; border-radius: 10px;'.
	 * @param bool   $echo Whether to echo the escaped value.
	 *
	 * @return string
	 */
	public static function css( $css, $echo = false ) {

		$css = safecss_filter_attr( $css );

		self::maybeEcho( $css, $echo );

		return $css;
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

			// Private callback for the "wp_kses_allowed_html" filter used to return allowed HTML for "Connections_Directory/Escape/HTML" context.
			$callback = function( $tags, $context ) {
				global $allowedposttags;

				if ( 'Connections_Directory/Escape/HTML' === $context ) {

					/**
					 * Default allowable HTML post tags.
					 *
					 * Use override default tags.
					 *
					 * @since 10.4
					 *
					 * @param array $allowedposttags
					 */
					return apply_filters( 'Connections_Directory/Utility/Escape/HTML', $allowedposttags );
				}

				return $tags;
			};
		}

		if ( false === has_filter( 'wp_kses_allowed_html', $callback ) ) {

			add_filter( 'wp_kses_allowed_html', $callback, 10, 2 );
		}

		return wp_kses( force_balance_tags( (string) $html ), 'Connections_Directory/Escape/HTML' );
	}

	/**
	 * Escape HTML id attribute.
	 *
	 * @since 10.4
	 *
	 * @param string $id   The `id` to escape.
	 * @param bool   $echo Whether to echo the escaped value.
	 *
	 * @return string
	 */
	public static function id( $id, $echo = false ) {

		$id = esc_attr( _string::replaceWhatWith( $id, ' ', '-' ) );

		self::maybeEcho( $id, $echo );

		return $id;
	}

	/**
	 * Escape the supplied value for use as a data attribute in tag.
	 *
	 * @link https://github.com/WordPress/WordPress-Coding-Standards/issues/1270#issuecomment-354433835
	 *
	 * @since 10.4.6
	 *
	 * @param array|string $json Data to encode.
	 * @param bool         $echo Whether to echo the escaped value.
	 *
	 * @return string
	 */
	public static function json( $json, $echo = false ) {

		$json = htmlentities( wp_json_encode( $json ), ENT_QUOTES, 'UTF-8' );

		self::maybeEcho( $json, $echo );

		return $json;
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

	/**
	 * Whether to echo the supplied string.
	 *
	 * @since 10.4.6
	 *
	 * @param string $string The string to echo.
	 * @param bool   $echo   Whether to echo supplied string.
	 */
	private static function maybeEcho( $string, $echo = true ) {

		if ( true === $echo ) {
			echo $string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
