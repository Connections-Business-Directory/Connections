<?php
/**
 * Sanitation.
 *
 * Handles the sanitation of input data.
 *
 * @package     Connections
 * @subpackage  Sanitation
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

use Connections_Directory\Utility\_sanitize;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnSanitize
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnSanitize {

	/**
	 * Merge user defined arguments into defaults array.
	 *
	 * This is the Connections equivalent to @see wp_parse_args().
	 * The difference is that it will discard any key/value pairs in $untrusted where the $key does not exist in $defaults.
	 *
	 * @link http://www.peterrknight.com/fear-and-surprise-improving-a-widespread-wordpress-pattern/
	 *
	 * @todo Add a third array param. This will define the sanitation to be used on each value in the untrusted array.
	 *
	 * @since 8.1.6
	 * @deprecated 10.4.26 Use _parse::parameters()
	 * @see \Connections_Directory\Utility\_parse::parameters()
	 *
	 * @param array|object|string $untrusted Value to merge with `$defaults`.
	 * @param array               $defaults  Array that serves as the defaults.
	 *
	 * @return array
	 */
	public static function args( $untrusted, $defaults ) {

		if ( ! is_array( $defaults ) ) {
			return $defaults;
		}

		if ( is_object( $untrusted ) ) {

			$args = get_object_vars( $untrusted );

		} elseif ( is_array( $untrusted ) ) {

			$args =& $untrusted;

		} elseif ( is_string( $untrusted ) ) {

			wp_parse_str( $untrusted, $args );
		}

		if ( ! isset( $args ) ) {
			return $defaults;
		}

		$intersect  = array_intersect_key( $args, $defaults ); // Get data for which is in the valid fields.
		$difference = array_diff_key( $defaults, $args ); // Get default data which is not supplied.

		return array_merge( $intersect, $difference ); // Merge the results. Contains only valid fields of all defaults.
	}

	/**
	 * Sanitizes text inputs
	 *
	 * Sanitizes string based on the string type.
	 *
	 * @since 0.8
	 * @deprecated 10.4.35
	 *
	 * @param string $type   Type of string to validate.
	 * @param string $string String to be sanitized.
	 *
	 * @return string Sanitized text.
	 */
	public static function string( $type, $string ) {

		_deprecated_function( __METHOD__, '10.4.35' );

		switch ( $type ) {

			case 'text':
				$string = sanitize_text_field( $string );
				break;

			case 'textarea':
				$string = trim( wp_unslash( $string ) );
				break;

			case 'quicktag':
				$string = self::quicktag( $string );
				break;

			case 'html':
				$string = self::html( $string );
				break;

			case 'url':
				$string = esc_url_raw( $string );
				break;

			case 'email':
				$string = sanitize_email( $string );
				break;

			case 'integer':
				$string = _sanitize::integer( $string );
				break;

			case 'color':
				$string = _sanitize::hexColor( $string );
				break;

			// Default should be unnecessary, but provided as a fallback anyway.
			default:
				$string = sanitize_text_field( $string );
		}

		return $string;
	}

	/**
	 * NOTE: This method is not complete and still under development, it should not be used.
	 *
	 * This is basically a Connections equivalent of @see sanitize_post_field().
	 * The only form fields currently supported by this method are the "name" fields.
	 * The $field var should be set to `name` to sanitize the "name" fields.
	 *
	 * The "name" fields include the following fields:
	 *
	 *  - honorable prefix
	 *  - first name
	 *  - middle name
	 *  - last name
	 *  - honorable suffix
	 *  - organization
	 *  - department
	 *  - title
	 *  - contact first name
	 *  - contact last name
	 *  - family name
	 *
	 * @internal
	 * @since 8.1.7
	 *
	 * @param string $field   The field to sanitize.
	 * @param string $value   The string to sanitize.
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string|WP_Error
	 */
	public static function field( $field, $value, $context = 'display' ) {

		switch ( $context ) {

			case 'raw':
				switch ( $field ) {

					case 'url':
						return esc_url_raw( $value );

					default:
						return $value;
				}

			case 'edit':
				switch ( $field ) {

					case 'excerpt':
						return sanitize_textarea_field( $value );

					case 'name':
					case 'street':
					case 'district':
					case 'county':
					case 'locality':
					case 'region':
					case 'postal-code':
					case 'country':
					case 'phone-number':
					case 'messenger-id':
					case 'date':
						// This is the same as the post title on the edit-form-advanced.php admin page.
						return esc_attr( esc_textarea( $value ) );

					case 'url':
						return esc_url( $value );

					case 'attribute':
						return esc_attr( $value );
				}

				break;

			case 'db':
				switch ( $field ) {

					case 'bio':
					case 'notes':
					case 'excerpt':
						/**
						 * Match the post content sanitation before being inserted in the db.
						 * See the `content_save_pre` filters.
						 */
						if ( false === current_user_can( 'unfiltered_html' ) ) {

							$value = wp_filter_post_kses( $value );
						}

						return wp_unslash( balanceTags( $value ) );

					case 'name':
					case 'street':
					case 'district':
					case 'county':
					case 'locality':
					case 'region':
					case 'postal-code':
					case 'country':
					case 'phone-number':
					case 'messenger-id':
					case 'date':
						/**
						 * Matches the post title sanitation before being inserted in the db.
						 * Aee the `title_save_pre` filters.
						 */
						return trim( wp_unslash( $value ) );

					case 'url':
						return esc_url_raw( $value );

					case 'attribute':
						return esc_attr( $value );
				}

				break;

			default:
				switch ( $field ) {

					case 'bio':
					case 'notes':
					case 'excerpt':
						/**
						 * Versions prior to 8.2.9 saved the bio and notes field slashed in the db.
						 * Unslash them when displaying before displaying them.
						 */
						return wp_unslash( $value );

					case 'name':
					case 'street':
					case 'district':
					case 'county':
					case 'locality':
					case 'region':
					case 'postal-code':
					case 'country':
					case 'phone-number':
					case 'messenger-id':
					case 'date':
						// This is the same as the filters applied via the `the_title` filter for the post title.
						return esc_html( trim( convert_chars( wptexturize( $value ) ) ) );

					case 'url':
						return esc_url( $value );

					case 'attribute':
						return esc_attr( $value );

					case 'js':
						return esc_js( $value );
				}

				break;
		}

		return new WP_Error( 'value_not_sanitized', __( 'Value not sanitized.', 'connections' ), $value );
	}

	/**
	 * Check the supplied value against an array of options.
	 * If the value exists as a key in the options array,
	 * it is returned, if it is not, the first key in the
	 * options array is returned instead. This is to provide
	 * a default value.
	 *
	 * This method is used to sanitize radio groups and selects.
	 *
	 * @since 0.8
	 *
	 * @param int|string $value   The value to check for in `$options`.
	 * @param array      $options An associative array of options.
	 * @param mixed      $default The value to return if value does not exist in the options array.
	 *
	 * @return mixed
	 */
	public static function option( $value, $options, $default = null ) {

		if ( array_key_exists( $value, $options ) ) {

			return $value;

		} else {

			if ( ! is_null( $default ) ) {

				return $default;

			} else {

				$key = array_keys( $options );

				return $key[0];
			}

		}
	}

	/**
	 * Check the supplied values against the supplied options.
	 *
	 * This method is used to sanitize checkbox groups.
	 *
	 * @since 0.8
	 *
	 * @param array $values   An index array of values.
	 * @param array $options  An associative array of the valid options.
	 *
	 * @return array
	 */
	public static function options( $values, $options ) {

		if ( empty( $values ) ) {
			return array();
		}

		// Let do a bit of array gymnastics...
		// array_flip $values so the values are the keys.
		// Use array_intersect_key to return only the values in $values from $options.
		// Finally, use array_keys to return the results from array_intersect_key.
		// The result will be only the valid $values in $options.
		return array_keys( array_intersect_key( $options, array_flip( $values ) ) );
	}

	/**
	 * Sanitizes checkbox input.
	 *
	 * WordPress core evaluates checkboxes as '1' or '0';
	 * to be consistent with core return '1' or '0'.
	 *
	 * @since 0.8
	 *
	 * @param string $value Value data to be sanitized.
	 *
	 * @return string
	 */
	public static function checkbox( $value ) {

		return $value ? '1' : '0';
	}

	/**
	 * Sanitizes the rte textarea input.
	 *
	 * @since 0.8
	 * @deprecated 10.4.28
	 *
	 * @param string $string The string to sanitize.
	 *
	 * @return string
	 */
	public static function html( $string ) {

		_deprecated_function( __METHOD__, '10.4.28', '\Connections_Directory\Utility\_sanitize::html()' );

		if ( false === current_user_can( 'unfiltered_html' ) ) {

			$string = wp_kses_post( $string );
		}

		return balanceTags( $string, true );
	}

	/**
	 * Sanitizes the quicktag textarea input.
	 *
	 * @since 0.8
	 * @deprecated 10.4.28
	 *
	 * @param string $string The string to sanitize.
	 *
	 * @return string
	 */
	public static function quicktag( $string ) {

		_deprecated_function( __METHOD__, '10.4.28', '\Connections_Directory\Utility\_sanitize::html()' );

		if ( false === current_user_can( 'unfiltered_html' ) ) {

			$string = wp_kses_data( $string );
		}

		return balanceTags( $string, true );
	}

	/**
	 * Sanitizes an array of IDs numbers or an ID number.
	 *
	 * @since 8.2.6
	 *
	 * @param int|int[] $id An integer or array of integers to sanitize.
	 *
	 * @return int|int[]
	 */
	public static function id( $id ) {

		if ( is_array( $id ) ) {

			// Ensure all IDs are positive integers.
			$id = array_map( 'absint', $id );

			// Filter anything that converted to 0 (i.e. non-integers).
			$id = array_filter( $id );

		} else {

			$id = absint( $id );
		}

		return $id;
	}

	/**
	 * Sanitizes a hex color.
	 *
	 * Returns either '' or a 3 or 6 digit hex color (with #).
	 *
	 * This function is borrowed from the class_wp_customize_manager.php file in WordPress core.
	 *
	 * @since 0.8
	 * @deprecated 10.4.9
	 * @see \Connections_Directory\Utility\_sanitize::hexColor()
	 *
	 * @param string $color The hashed (#) hex color to sanitize.
	 *
	 * @return string
	 */
	public static function hexColor( $color ) {

		_deprecated_function( __METHOD__, '10.4.19', '\Connections_Directory\Utility\_sanitize::hexColor()' );

		return _sanitize::hexColor( $color );
	}
}
