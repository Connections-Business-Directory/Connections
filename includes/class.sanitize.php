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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnSanitize
 */
class cnSanitize {

	/**
	 * Merge user defined arguments into defaults array.
	 *
	 * This is the Connections equivalent to @see wp_parse_args().
	 * The difference is that is will discard any key/value pairs in $untrusted where the $key does not exist in $defaults.
	 *
	 * @link http://www.peterrknight.com/fear-and-surprise-improving-a-widespread-wordpress-pattern/
	 *
	 * @todo Add a third array param. This will define the sanitation to be used on each value in the untrusted array.
	 *
	 * @access public
	 * @since  8.1.6
	 *
	 * @param array|object|string $untrusted
	 * @param $defaults
	 *
	 * @return array
	 */
	public static function args( $untrusted, $defaults ) {

		if ( ! is_array( $defaults ) ) return $defaults;

		if ( is_object( $untrusted ) ) {

			$args = get_object_vars( $untrusted );

		} elseif ( is_array( $untrusted ) ) {

			$args =& $untrusted;

		} elseif ( is_string( $untrusted ) ) {

			wp_parse_str( $untrusted, $args );
		}

		if ( ! isset( $args ) ) return $defaults;

		$intersect  = array_intersect_key( $args, $defaults ); // Get data for which is in the valid fields.
		$difference = array_diff_key( $defaults, $args ); // Get default data which is not supplied.

		return array_merge( $intersect, $difference ); // Merge the results. Contains only valid fields of all defaults.
	}

	/**
	 * Sanitizes text inputs
	 *
	 * Sanitizes string based on the the string type.
	 *
	 * @access public
	 * @since 0.8
	 * @param  string $type   Type of string to validate.
	 * @param  string $string String to be sanitized.
	 *
	 * @return string Sanitized text.
	 */
	public static function string( $type, $string ) {

		switch ( $type ) {

			case 'text':

				$string = sanitize_text_field( $string );
				break;

			case 'textarea':

				$string = esc_textarea( $string );
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

				$string = self::integer( $string );
				break;

			case 'currency':

				$string = self::currency( $string );
				break;

			case 'color':

				$string = self::hexColor( $string );
				break;

			// Default should be unnecessary, but provided as a fallback anyway.
			default:
				$string = sanitize_text_field( $string );
		}

		return $string;
	}

	/**
	 * NOTE: This method is not complete an still under development, it should not be used.
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
	 * @access private
	 * @since  8.1.7
	 * @static
	 *
	 * @param string $field   The field to sanitize.
	 * @param string $value   The string to sanitize.
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return mixed string|WP_Error
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

					case 'name':
					case 'street':
					case 'locality':
					case 'region':
					case 'postal-code':
					case 'country':

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

						/**
						 * Match the post content sanitation before being inserted in the db.
						 * See the `content_save_pre` filters.
						 */

						if ( FALSE == current_user_can( 'unfiltered_html' ) ) {

							$value = wp_filter_post_kses( $value );
						}

						return wp_unslash( balanceTags( $value ) );

					case 'name';
					case 'street':
					case 'locality':
					case 'region':
					case 'postal-code':
					case 'country':

						/**
						 * Matches the post title sanitation before being inserted in the db.
						 * Aee the `title_save_pre` filters.
						 */
						return trim( wp_unslash( $value ) );

					case 'url';

						return esc_url_raw( $value );
				}

				break;

			default:

				switch ( $field ) {

					case 'bio':
					case 'notes':

						/**
						 * Versions prior to 8.2.9 saved teh bio and notes field slashed in the db.
						 * Unslash them when displaying before displaying them.
						 */
						return wp_unslash( $value );

					case 'name':
					case 'street':
					case 'locality':
					case 'region':
					case 'postal-code':
					case 'country':

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
	 * @access public
	 * @since 0.8
	 * @param  mixed $value
	 * @param  array $options An associative array of options.
	 * @param  mixed $default [optional] The value to return if value does not exist in the options array.
	 *
	 * @return mixed
	 */
	public static function option( $value, $options, $default = NULL ) {

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
	 * @todo Implement $defaults.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $values   An index array of values.
	 * @param  array  $options  An associative array of the valid options.
	 * @param  array  $defaults [optional] The values to return if no values exists in the options array.
	 *
	 * @return array
	 */
	public static function options( $values, $options, $defaults = array() ) {

		if ( empty( $values ) ) return array();

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
	 * @access public
	 * @since 0.8
	 * @param  string $value Value data to be sanitized.
	 *
	 * @return string
	 */
	public static function checkbox( $value ) {

		return $value ? '1' : '0';
	}

	/**
	 * Sanitizes the rte textarea input.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_kses_post()
	 * @uses force_balance_tags
	 * @param  string $string
	 *
	 * @return string
	 */
	public static function html( $string ) {

		if ( FALSE == current_user_can( 'unfiltered_html' ) ) {

			$string = wp_kses_post( $string );
		}

		return balanceTags( $string, TRUE );
	}

	/**
	 * Sanitizes the quicktag textarea input.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_kses_data()
	 * @uses force_balance_tags
	 * @param  string $string
	 *
	 * @return string
	 */
	public static function quicktag( $string ) {

		if ( FALSE == current_user_can( 'unfiltered_html' ) ) {

			$string = wp_kses_data( $string );
		}

		return balanceTags( $string, TRUE );
	}

	/**
	 * Sanitizes an array of IDs numbers or an ID number.
	 *
	 * @access protected
	 * @since  8.2.6
	 *
	 * @uses   absint()
	 *
	 * @param $id
	 *
	 * @return array|int
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
	 * Return integer.
	 *
	 * @access public
	 * @since 0.8
	 * @param  int $value
	 *
	 * @return int
	 */
	public static function integer( $value ) {

		return intval( $value );
	}

	/**
	 * Sanitizes currency input.
	 *
	 * Returns the currency value of the $input.
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $input Input data to be sanitized.
	 *
	 * @return string Returns the $valid string after sanitization.
	 */
	public function currency( $input ) {

		if ( is_numeric( $input ) ) {

			return $input ? number_format( $input, 2 ) : '';

		} else {

			return '';
		}

	}

	/**
	 * Sanitizes a hex color.
	 *
	 * Returns either '' or a 3 or 6 digit hex color (with #).
	 *
	 * This function is borrowed from the class_wp_customize_manager.php
	 * file in WordPress core.
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $color
	 *
	 * @return string
	 */
	public static function hexColor( $color ) {

		// Returns empty string if input was an empty string.
		if ( '' === $color ) {

			return '';
		}

		// Returns 3 or 6 hex digits, or the empty string.
		if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {

			return $color;
		}

		return '';
	}

	/**
	 * Sanitizes a hex color without a hash. Use hexColor() when possible.
	 *
	 * Returns either '' or a 3 or 6 digit hex color (without a #).
	 *
	 * This function is borrowed from the class_wp_customize_manager.php
	 * file in WordPress core.
	 *
	 * @access public
	 * @since  8.1
	 * @uses   sanitize_hex_color()
	 * @param  string $color
	 *
	 * @return mixed  string | string
	 */
	public static function hexColorNoHash( $color ) {

		$color = ltrim( $color, '#' );

		if ( '' === $color )
			return '';

		return self::hexColor( '#' . $color ) ? $color : '';
	}

}
