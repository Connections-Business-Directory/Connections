<?php

/**
 * HTML elements.
 *
 * @package     Connections
 * @subpackage  HTML
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnHTML {

	public static function attribute( $type, $value ) {

		switch ( $type ) {

			case 'class':

				if ( is_array( $value ) && ! empty( $value ) ) {

					array_walk( $value, 'sanitize_html_class' );

					return $value ? ' class="' . implode( $value, ' ' ) . '" ' : '';

				} elseif ( ! empty( $value ) ) {

					return ' class="' . sanitize_html_class( (string) $value ) . '" ';

				} else {

					return '';
				}

				break;

			case 'id':

				if ( ! empty( $value ) ) {

					return ' id="' . esc_attr( (string) $value ) . '" ';

				} else {

					return '';
				}

				break;

			case 'style':

				if ( is_array( $value ) && ! empty( $value ) ) {

					array_walk( $value, create_function( '&$i, $property', '$i = "$property: $i";' ) );

					return $value ? ' style="' . implode( $value, '; ' ) . '"' : '';
				}

				return '';

				break;

			case 'value':

				return ' value="' . esc_attr( (string) $value ) . '" ';

				break;

			default:

				if ( is_array( $value ) && ! empty( $value ) ) {

					array_walk( $value, 'esc_attr' );

					return $value ? ' ' . esc_attr( $type ) . '="' . implode( $value, ' ' ) . '" ' : '';

				} elseif ( ! empty( $value ) ) {

					return ' ' . esc_attr( $type ) . '="' . esc_attr( (string) $value ) . '" ';

				} else {

					return '';
				}

				break;
		}

	}

	public static function field( $atts, $value = '' ) {

		switch ( $atts['type'] ) {

			case 'text':

				return self::input( $atts, $value );

				break;

			case 'radio':

				return self::radio( $atts, $value );

				break;

			case 'select':

				return self::select( $atts, $value );

				break;

			default:
				# todo Put action and or filter here.
				break;
		}
	}

	private static function prefix( $value, $atts = array() ) {

		if ( empty( $value ) ) return;

		$defaults = array(
			'prefix'   => 'cn-',
			);

		$atts = wp_parse_args( $atts, $defaults );

		if ( is_array( $value ) ) {

			foreach ( $value as $key => $class ) {

				$value[ $key ] = $atts['prefix'] . $class;
			}

			return $value;

		} else {

			return $atts['prefix'] . $value;
		}
	}

	public static function label( $atts ) {

		$defaults = array(
			'for'      => '',
			'class'    => array(),
			'id'       => '',
			'style'    => array(),
			'label'    => '',
			'return'   => FALSE,
			);

		$atts = wp_parse_args( $atts, $defaults );

		$out = sprintf( '<label %1$s %2$s %3$s %4$s>%5$s</label>',
			self::attribute( 'for', $atts['for'] ),
			self::attribute( 'class', $atts['class'] ),
			self::attribute( 'id', $atts['id'] ),
			self::attribute( 'style', $atts['style'] ),
			esc_attr( $atts['label'] )
		);

		/*
		 * Return or echo the string.
		 */
		if ( $atts['return'] ) return $out;
		echo $out;
	}

	public static function input( $atts, $value = '' ) {

		$defaults = array(
			'prefix'   => 'cn-',
			'class'    => array(),
			'id'       => '',
			'style'    => array(),
			'value'    => '',
			'required' => FALSE,
			'label'    => '',
			'before'   => '',
			'after'    => '',
			'parts'    => array( '%label%', '%field%' ),
			'layout'   => '%label%%field%',
			'return'   => FALSE,
			);

		$atts = wp_parse_args( $atts, $defaults );

		// If no `id` was supplied, bail.
		if ( empty( $atts['id'] ) ) return '';

		// The field name.
		$name = $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// An array to store the replacement strings for the label and field.
		$replace = array();

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = self::prefix( $atts['class'] );
			$atts['id']    = self::prefix( $atts['id'] );
		}

		// Add "required" to any classes that may have been supplied.
		// If the field is required, cast $atts['class'] as an array in case a string was supplied
		// and then tack the "required" value to the end of the array.
		$atts['class'] = $atts['required'] ? array_merge( (array) $atts['class'], array('required') ) : $atts['class'];

		// Create the field label, if supplied.
		$replace[] = ! empty( $atts['label'] ) ? self::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => TRUE ) ) : '';

		$replace[] = sprintf( '<input type="text" %1$s %2$s %3$s %4$s %5$s/>',
			self::attribute( 'class', $atts['class'] ),
			self::attribute( 'id', $atts['id'] ),
			self::attribute( 'name', $name ),
			self::attribute( 'style', $atts['style'] ),
			self::attribute( 'value', $value )
		);

		$out = str_ireplace( $search, $replace, $atts['layout'] );

		/*
		 * Return or echo the string.
		 */
		if ( $atts['return'] ) return ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] );
		echo ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] );
	}

	public static function radio( $atts, $value = '' ) {

		$out = '';

		$defaults = array(
			'prefix'   => 'cn-',
			'class'    => array( 'radio' ),
			'id'       => '',
			'style'    => array(),
			'options'  => array(),
			'required' => FALSE,
			'label'    => '',
			'before'   => '',
			'after'    => '',
			'display'  => 'inline',
			'parts'    => array( '%label%', '%field%' ),
			'layout'   => '%field%%label%',
			'return'   => FALSE,
			);

		$atts = wp_parse_args( $atts, $defaults );

		// If no `id` was supplied, bail.
		if ( empty( $atts['id'] ) ) return '';

		// The field name.
		$name = $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = self::prefix( $atts['class'] );
			$atts['id']    = self::prefix( $atts['id'] );
		}

		// Add "required" to any classes that may have been supplied.
		// If the field is required, cast $atts['class'] as an array in case a string was supplied
		// and then tack the "required" value to the end of the array.
		$atts['class'] = $atts['required'] ? array_merge( (array) $atts['class'], array('required') ) : $atts['class'];

		$out .= '<span class="cn-radio-group" style="display: ' . esc_attr( $atts['display'] ) . ';">';

		foreach ( $atts['options'] as $key => $label ) {

			// An array to store the replacement strings for the label and field.
			$replace = array();

			$out .= '<span class="cn-radio-option" style="display: ' . esc_attr( $atts['display'] ) . ';">';

			// Create the field label, if supplied.
			$replace[] = ! empty( $label ) ? self::label( array( 'for' => $atts['id'] . '[' . $key . ']', 'label' => $label, 'return' => TRUE ) ) : '';

			$replace[] = sprintf( '<input type="radio" %1$s %2$s %3$s %4$s %5$s %6$s/>',
				self::attribute( 'class', $atts['class'] ),
				self::attribute( 'id', $atts['id'] . '[' . $key . ']' ),
				self::attribute( 'name', $name ),
				self::attribute( 'style', $atts['style'] ),
				self::attribute( 'value', $value ),
				checked( TRUE , in_array( $key, (array) $value ) , FALSE )
			);

			$out .= str_ireplace( $search, $replace, $atts['layout'] );

			$out .= '</span>' . PHP_EOL;
		}

		$out .= '</span>';

		/*
		 * Return or echo the string.
		 */
		if ( $atts['return'] ) return PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
		echo PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
	}

	public static function select( $atts, $value = '' ) {

		$out = '';

		$defaults = array(
			'prefix'   => 'cn-',
			'class'    => array(),
			'id'       => '',
			'style'    => array(),
			'default'  => array(),
			'options'  => array(),
			'enhanced' => FALSE,
			'label'    => '',
			'before'   => '',
			'after'    => '',
			'parts'    => array( '%label%', '%field%' ),
			'layout'   => '%label%%field%',
			'return'   => FALSE,
			);

		$atts = wp_parse_args( $atts, $defaults );

		// If no `id` was supplied, bail.
		if ( empty( $atts['id'] ) ) return '';

		// The field name.
		$name = $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// An array to store the replacement strings for the label and field.
		$replace = array();

		// Add the 'cn-enhanced-select' class for the jQuery Chosen Plugin will enhance the drop down.
		if ( $atts['enhanced'] ) $atts['class'] = array_merge( (array) $atts['class'], array('enhanced-select') );

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = self::prefix( $atts['class'] );
			$atts['id']    = self::prefix( $atts['id'] );
		}

		// Create the field label, if supplied.
		$replace['label'] = ! empty( $atts['label'] ) ? self::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => TRUE ) ) : '';

		// Open the select.
		$replace['field'] = sprintf( '<select %1$s %2$s %3$s %4$s %5$s>',
			self::attribute( 'class', $atts['class'] ),
			self::attribute( 'id', $atts['id'] ),
			self::attribute( 'name', $name ),
			self::attribute( 'style', $atts['style'] ),
			! empty( $atts['default'] ) && $atts['enhanced'] ? ' data-placeholder="' . esc_attr( (string) reset( $atts['default'] ) ) . '"' : ''
			);

		/*
		 * Build the select drop down options.
		 */

		// If the select is NOT a Chosen enhanced select; prepend the default option to the top of the options array.
		if ( ! empty( $atts['default'] ) && ! $atts['enhanced'] ) $atts['options'] = $atts['default'] + $atts['options'];

		// If the select IS a Chosen enhanced select; prepend the blank option required for Chosen.
		if ( ! empty( $atts['default'] ) && $atts['enhanced'] ) $atts['options'] = array( '' => '' ) + $atts['options'];

		// This fancy bit of code builds the options for the select.
		// array_walk( $atts['options'], create_function( '&$i, $key', '$i = "<option value=\"$key\">$i</option>";' ) );

		// Lastly, create the options as a string for output.
		// $out .= PHP_EOL . implode( $atts['options'], PHP_EOL ) . PHP_EOL;

		foreach ( $atts['options'] as $key => $label )	{

			$replace['field'] .= sprintf( '<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $key ),
				selected( $value, $key, FALSE ),
				esc_html( $label )
				);
		}

		// Close the select.
		$replace['field'] .= '</select>';

		$out = str_ireplace( $search, $replace, $atts['layout'] );

		/*
		 * Return or echo the string.
		 */
		if ( $atts['return'] ) return PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
		echo PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
	}
}
