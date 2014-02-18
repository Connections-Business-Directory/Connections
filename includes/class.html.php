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

	/**
	 * Helper method that can be used within loops to
	 * dynamically call the correct field type to render.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $atts  The field attirbutes array.
	 * @param  string $value The field value.
	 *
	 * @return string        The rendered field.
	 */
	public static function field( $atts, $value = '' ) {

		switch ( $atts['type'] ) {

			case 'text':

				return self::text( $atts, $value );

				break;

			case 'checkbox':

				return self::checkbox( $atts, $value );

				break;

			case 'checkbox_group':

				return self::checkboxGroup( $atts, $value );

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

	/**
	 * Renders a text input field.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $atts  The field attributes.
	 * @param  string $value The field value.
	 *
	 * @return string        The rendered field.
	 */
	public static function text( $atts, $value = '' ) {

		$atts['type'] = 'text';

		return self::input( $atts, $value );
	}

	/**
	 * Renders a checkbox field.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $atts  The field attributes.
	 * @param  string $value The field value.
	 *
	 * @return string        The rendered field.
	 */
	public static function checkbox( $atts, $value = '' ) {

		$atts['type']    = 'checkbox';
		$atts['layout']  = '%field%%label%';
		$atts['checked'] = checked( '1', $value, FALSE );

		return self::input( $atts, '1' );
	}

	/**
	 * Renders a group of checkboxes.
	 *
	 * @todo This has not been tested att all, will likely contain bugs or not work correctly at all.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $atts  The field attributes.
	 * @param  string $value The field value.
	 *
	 * @return string        The rendered field group.
	 */
	public static function checkboxGroup( $atts, $value = '' ) {

		$atts['type'] = 'checkbox';

		return self::group( $atts, $value );
	}

	/**
	 * Renders a radio group.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $atts  The field attributes.
	 * @param  string $value The field value.
	 *
	 * @return string        The rendered field group.
	 */
	public static function radio( $atts, $value = '' ) {

		$atts['type'] = 'radio';

		return self::group( $atts, $value );
	}

	/**
	 * Prefixes the supplied string with the defined prefix.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_parse_args()
	 * @param  mixed  $value string | array  The value to add the defined prefix to.
	 * @param  array  $atts  The attrubutes array.
	 *
	 * @return mixed         string | array
	 */
	public static function prefix( $value, $atts = array() ) {

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

	/**
	 * Renders a HTML tag attribute.
	 *
	 * @access public
	 * @since 0.8
	 * @uses sanitize_html_class()
	 * @uses esc_attr()
	 * @param  string $type  The attribute name.
	 * @param  string $value The attribute value.
	 *
	 * @return string        The rendered attribute.
	 */
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

	/**
	 * Render a field lsbel.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_parse_args()
	 * @uses esc_attr()
	 * @param  array $atts  The label attributes.
	 *
	 * @return string       The rendered label.
	 */
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

	/**
	 * Renders an input field of the supplied type.
	 *
	 * @access private
	 * @since 0.8
	 * @uses wp_parse_args()
	 * @param  array  $atts  The field attributes.
	 * @param  string $value The field value.
	 *
	 * @return string        The rendered field.
	 */
	public static function input( $atts, $value = '' ) {

		$defaults = array(
			'type'     => 'text',
			'prefix'   => 'cn-',
			'class'    => array(),
			'id'       => '',
			'name'     => '',
			'style'    => array(),
			// 'value'    => '',
			'ckecked'  => '',
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

		// The field name. If not supplied, use the id.
		$name = ! empty( $atts['name'] ) ? $atts['name'] : $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// An array to store the replacement strings for the label and field.
		$replace = array();

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = self::prefix( $atts['class'], $atts );
			$atts['id']    = self::prefix( $atts['id'], $atts );
		}

		// Add "required" to any classes that may have been supplied.
		// If the field is required, cast $atts['class'] as an array in case a string was supplied
		// and then tack the "required" value to the end of the array.
		$atts['class'] = $atts['required'] ? array_merge( (array) $atts['class'], array('required') ) : $atts['class'];

		// Create the field label, if supplied.
		$replace[] = ! empty( $atts['label'] ) ? self::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => TRUE ) ) : '';

		$replace[] = sprintf( '<input %1$s %2$s %3$s %4$s %5$s %6$s %7$s/>',
			self::attribute( 'type', $atts['type'] ),
			self::attribute( 'class', $atts['class'] ),
			self::attribute( 'id', $atts['id'] ),
			self::attribute( 'name', $name ),
			self::attribute( 'style', $atts['style'] ),
			self::attribute( 'value', $value ),
			! empty( $atts['checked'] ) ? $atts['checked'] : ''
		);

		$out = str_ireplace( $search, $replace, $atts['layout'] );

		/*
		 * Return or echo the string.
		 */
		if ( $atts['return'] ) return ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] );
		echo ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] );
	}

	/**
	 * Renders either a radio or checkbox group.
	 *
	 * @access private
	 * @since 0.8
	 * @uses wp_parse_args()
	 * @uses esc_attr()
	 * @uses checked()
	 * @param  array  $atts  The group attributes.
	 * @param  string $value The group item that will be marked as "CHECKED".
	 *
	 * @return string        The rendered group.
	 */
	private static function group( $atts, $value = '' ) {

		$out = '';

		$defaults = array(
			'type'     => 'radio',
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

		$out .= '<span class="cn-' . esc_attr( $atts['type'] ) . '-group" style="display: ' . esc_attr( $atts['display'] ) . ';">';

		foreach ( $atts['options'] as $key => $label ) {

			// An array to store the replacement strings for the label and field.
			$replace = array();

			$out .= '<span class="cn-' . esc_attr( $atts['type'] ) . '-option" style="display: ' . esc_attr( $atts['display'] ) . ';">';

			// Create the field label, if supplied.
			$replace[] = ! empty( $label ) ? self::label( array( 'for' => $atts['id'] . '[' . $key . ']', 'label' => $label, 'return' => TRUE ) ) : '';

			$replace[] = self::input(
				array(
					'type'    => $atts['type'],
					'prefix'  => '',
					'class'   => $atts['class'],
					'id'      => $atts['id'] . '[' . $key . ']',
					'name'    => $name,
					'style'   => $atts['style'],
					'value'   => $value,
					'checked' => checked( TRUE , in_array( $key, (array) $value ) , FALSE ),
					'return'  => TRUE,
					),
				$key
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

	/**
	 * Renders a select field.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_parse_args()
	 * @uses esc_attr()
	 * @uses selected()
	 * @uses esc_html()
	 * @param  array  $atts  The field attributes.
	 * @param  string $value The selected option.
	 *
	 * @return string        The rendered field.
	 */
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
