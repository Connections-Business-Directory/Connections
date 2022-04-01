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
use function Connections_Directory\Utility\_deprecated\_argument as _deprecated_argument;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
				$string = self::integer( $string );
				break;

			case 'currency':
				$string = self::currency( $string );
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
	 * Sanitize the input string. HTML tags can be permitted.
	 * The permitted tags can be supplied in an array.
	 *
	 * @since unknown
	 * @deprecated 9.11
	 *
	 * @param string $string
	 * @param bool   $allowHTML
	 * @param array  $permittedTags
	 *
	 * @return string
	 */
	public static function sanitizeString( $string, $allowHTML = false, $permittedTags = array() ) {

		_deprecated_function( __METHOD__, '9.11' );
		_deprecated_argument( __METHOD__, '10.4.6', 'The permitted_tags argument is deprecated.' ); // Never implemented.

		// Strip all tags except the permitted.
		if ( ! $allowHTML ) {

			// Ensure all tags are closed. Uses WordPress method balanceTags().
			$balancedText = balanceTags( $string, true );

			$strippedText = strip_tags( $balancedText );

			// Strip all script and style tags.
			$strippedText = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $strippedText );

			// Escape text using the WordPress method and then strip slashes.
			$escapedText = stripslashes( esc_attr( $strippedText ) );

			// Remove line breaks and trim white space.
			$escapedText = preg_replace( '/[\r\n\t ]+/', ' ', $escapedText );

			return trim( $escapedText );

		} else {

			// Strip all script and style tags.
			$strippedText = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
			$strippedText = preg_replace( '/&lt;(script|style).*?&gt;.*?&lt;\/\\1&gt;/si', '', stripslashes( $strippedText ) );

			/*
			 * Use WordPress method make_clickable() to make links clickable and
			 * use kses for filtering.
			 *
			 * http://ottopress.com/2010/wp-quickie-kses/
			 */
			return wptexturize( wpautop( make_clickable( wp_kses_post( $strippedText ) ) ) );
		}

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

					case 'excerpt':
						return self::string( 'textarea', $value );

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
	 * @access public
	 * @since 0.8
	 * @param mixed $value
	 * @param array $options An associative array of options.
	 * @param mixed $default [optional] The value to return if value does not exist in the options array.
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
	 * @todo Implement $defaults.
	 *
	 * @access public
	 * @since 0.8
	 * @param array $values   An index array of values.
	 * @param array $options  An associative array of the valid options.
	 * @param array $defaults [optional] The values to return if no values exists in the options array.
	 *
	 * @return array
	 */
	public static function options( $values, $options, $defaults = array() ) {

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

		if ( false === current_user_can( 'unfiltered_html' ) ) {

			$string = wp_kses_post( $string );
		}

		return balanceTags( $string, true );
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

		if ( false === current_user_can( 'unfiltered_html' ) ) {

			$string = wp_kses_data( $string );
		}

		return balanceTags( $string, true );
	}

	/**
	 * Sanitizes an array of IDs numbers or an ID number.
	 *
	 * @since  8.2.6
	 *
	 * @param int|int[] $id
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
	public static function currency( $input ) {

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
	 * @since 0.8
	 * @deprecated 10.4.9
	 * @see \Connections_Directory\Utility\_sanitize::hexColor()
	 *
	 * @param string $color
	 *
	 * @return string
	 */
	public static function hexColor( $color ) {

		_deprecated_function( __METHOD__, '10.4.19', '\Connections_Directory\Utility\_sanitize::hexColor()' );

		return _sanitize::hexColor( $color );
	}

	/**
	 * Sanitizes a hex color without a hash.
	 *
	 * Returns either '' or a 3 or 6 digit hex color (without a #).
	 *
	 * @since 8.1
	 * @deprecated 10.4.19
	 * @see sanitize_hex_color_no_hash()
	 *
	 * @param string $color
	 *
	 * @return string|null
	 */
	public static function hexColorNoHash( $color ) {

		_deprecated_function( __METHOD__, '10.4.19', 'sanitize_hex_color_no_hash()' );

		return sanitize_hex_color_no_hash( $color );
	}
}
