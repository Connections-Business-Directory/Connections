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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_string;
use function Connections_Directory\Utility\_deprecated\_argument as _deprecated_argument;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

/**
 * Class cnHTML
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnHTML {

	/**
	 * Helper method that can be used within loops to
	 * dynamically call the correct field type to render.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The field attributes array.
	 * @param string $value The field value.
	 *
	 * @return string        The rendered field.
	 */
	public static function field( $atts, $value = '' ) {

		switch ( $atts['type'] ) {

			case 'text':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf(
						'The `%s` parameter is no longer supported. Use `\Connections_Directory\Form\Field\Text::create()`.',
						esc_attr( $atts['type'] )
					)
				);

				return self::text( $atts, $value );

			case 'checkbox':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf(
						'The `%s` parameter is no longer supported. Use `\Connections_Directory\Form\Field\Checkbox::create()`.',
						esc_attr( $atts['type'] )
					)
				);

				return self::checkbox( $atts, $value );

			case 'checkbox_group':
			case 'checkbox-group':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf(
						'The `%s` parameter is no longer supported. Use `\Connections_Directory\Form\Field\Checkbox_Group::create()`.',
						esc_attr( $atts['type'] )
					)
				);

				return self::checkboxGroup( $atts, $value );

			case 'radio':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf(
						'The `%s` parameter is no longer supported. Use `\Connections_Directory\Form\Field\Radio_Group::create()`.',
						esc_attr( $atts['type'] )
					)
				);

				return self::radio( $atts, $value );

			case 'select':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf(
						'The `%s` parameter is no longer supported. Use `\Connections_Directory\Form\Field\Select::create()`.',
						esc_attr( $atts['type'] )
					)
				);

				return self::select( $atts, $value );

			case 'submit':
				return self::input( $atts, $value );

			case 'textarea':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf(
						'The `%s` parameter is no longer supported. Use `\Connections_Directory\Form\Field\Textarea::create()`.',
						esc_attr( $atts['type'] )
					)
				);

				return self::textarea( $atts, $value );

			case 'hidden':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf(
						'The `%s` parameter is no longer supported. Use `\Connections_Directory\Form\Field\Hidden::create()`.',
						esc_attr( $atts['type'] )
					)
				);

				return self::input( $atts, $value );

			default:
				// todo Put action and or filter here.
				return '';
		}
	}

	/**
	 * Renders a text input field.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The field attributes.
	 * @param string $value The field value.
	 *
	 * @return string        The rendered field.
	 */
	public static function text( $atts, $value = '' ) {

		_deprecated_function( __METHOD__, '10.4', '\Connections_Directory\Form\Field\Text::create()' );

		$atts['type'] = 'text';

		return self::input( $atts, $value );
	}

	/**
	 * Renders a checkbox field.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The field attributes.
	 * @param string $value The field value.
	 *
	 * @return string        The rendered field.
	 */
	public static function checkbox( $atts, $value = '' ) {

		_deprecated_function( __METHOD__, '10.4', '\Connections_Directory\Form\Field\Checkbox::create()' );

		$atts['type']    = 'checkbox';
		$atts['layout']  = '%field%%label%';
		$atts['checked'] = checked( '1', $value, false );

		return self::input( $atts, '1' );
	}

	/**
	 * Renders a group of checkboxes.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The field attributes.
	 * @param string $value The field value.
	 *
	 * @return string        The rendered field group.
	 */
	public static function checkboxGroup( $atts, $value = '' ) {

		_deprecated_function( __METHOD__, '10.4', '\Connections_Directory\Form\Field\Checkbox_Group::create()' );

		$atts['type'] = 'checkbox';

		return self::group( $atts, $value );
	}

	/**
	 * Renders a radio group.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The field attributes.
	 * @param string $value The field value.
	 *
	 * @return string        The rendered field group.
	 */
	public static function radio( $atts, $value = '' ) {

		_deprecated_function( __METHOD__, '10.4', '\Connections_Directory\Form\Field\Radio_Group::create()' );

		$atts['type'] = 'radio';

		return self::group( $atts, $value );
	}

	/**
	 * Renders an HTML tag attribute.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param string       $type  The attribute name.
	 * @param array|string $value The attribute value.
	 *
	 * @return string        The rendered attribute.
	 */
	public static function attribute( $type, $value ) {

		switch ( $type ) {

			case 'class':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf( 'The `%s` parameter is no longer supported. Use _escape::classNames() instead.', esc_attr( $type ) )
				);

				if ( is_array( $value ) && ! empty( $value ) ) {

					array_walk( $value, 'sanitize_html_class' );

					return $value ? ' class="' . implode( ' ', $value ) . '" ' : '';

				} elseif ( ! empty( $value ) ) {

					return ' class="' . sanitize_html_class( (string) $value ) . '" ';

				} else {

					return '';
				}

			case 'id':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf( 'The `%s` parameter is no longer supported. Use _escape::id() instead.', esc_attr( $type ) )
				);

				if ( ! empty( $value ) ) {

					return ' id="' . esc_attr( (string) _string::replaceWhatWith( $value, ' ', '-' ) ) . '" ';

				} else {

					return '';
				}

			case 'style':
				_deprecated_argument(
					__METHOD__,
					'10.4',
					sprintf( 'The `%s` parameter is no longer supported. Use _html::stringifyCSSAttributes() and _escape::css() instead.', esc_attr( $type ) )
				);

				if ( is_array( $value ) && ! empty( $value ) ) {

					array_walk(
						$value,
						function ( &$i, $property ) {

							$i = "$property: $i";
						}
					);

					return $value ? ' style="' . implode( '; ', $value ) . '"' : '';
				}

				return '';

			case 'data':
				if ( is_array( $value ) && ! empty( $value ) ) {

					array_walk(
						$value,
						function ( &$i, $property ) {

							$i = "data-$property=\"$i\"";
						}
					);

					return $value ? implode( ' ', $value ) : '';
				}

				return '';

			case 'value':
				return ' value="' . esc_attr( (string) $value ) . '" ';

			case 'data-array':
				$data = array();

				/**
				 * Create valid HTML5 data attributes.
				 *
				 * @link http://stackoverflow.com/a/22753630/5351316
				 * @link https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/dataset
				 */
				if ( _array::isDimensional( $value ) ) {

					foreach ( $value as $_value ) {

						if ( isset( $_value['name'] ) && 0 < strlen( $_value['name'] ) ) {

							$name          = 'data-' . _string::toCamelCase( $_value['name'] );
							$data[ $name ] = $_value['value'];
						}

					}

				} else {

					if ( isset( $value['name'] ) && 0 < strlen( $value['name'] ) ) {

						$name          = 'data-' . _string::toCamelCase( $value['name'] );
						$data[ $name ] = $value['value'];
					}
				}

				if ( ! empty( $data ) ) {

					array_walk(
						$data,
						function ( &$i, $name ) {

							$i = $name . '="' . esc_attr( $i ) . '"';
						}
					);

					return ' ' . implode( ' ', $data );
				}

				return '';

			default:
				if ( is_array( $value ) && ! empty( $value ) ) {

					array_walk( $value, 'esc_attr' );

					return $value ? ' ' . esc_attr( $type ) . '="' . implode( ' ', $value ) . '" ' : '';

				} elseif ( ! empty( $value ) ) {

					return ' ' . esc_attr( $type ) . '="' . esc_attr( (string) $value ) . '" ';

				} else {

					return '';
				}

		}
	}

	/**
	 * Echo or return the supplied string.
	 *
	 * @since      8.3.4
	 * @deprecated 10.4
	 *
	 * @param bool   $return
	 * @param string $html
	 *
	 * @return string
	 */
	private static function echoOrReturn( $return, $html ) {

		if ( $return ) {

			return $html;

		} else {

			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return '';
		}
	}

	/**
	 * Render a field label.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array $atts The label attributes.
	 *
	 * @return string       The rendered label.
	 */
	public static function label( $atts ) {

		_deprecated_function( __METHOD__, '10.4', '\Connections_Directory\Form\Field\Label::create()' );

		$defaults = array(
			'for'    => '',
			'class'  => array(),
			'id'     => '',
			'style'  => array(),
			'label'  => '',
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$class = _escape::classNames( $atts['class'] );
		$class = 0 < strlen( $class ) ? " class=\"{$class}\"" : '';

		$id = _escape::id( $atts['id'] );
		$id = 0 < strlen( $id ) ? " id=\"{$id}\"" : '';

		$css   = _escape::css( _html::stringifyCSSAttributes( $atts['style'] ) );
		$style = 0 < strlen( $css ) ? " style=\"{$css}\"" : '';

		$out = sprintf(
			'<label %1$s %2$s %3$s %4$s>%5$s</label>',
			self::attribute( 'for', $atts['for'] ),
			$class,
			$id,
			$style,
			_escape::html( $atts['label'] )
		);

		$out = _string::replaceWhatWith( $out, ' ' );

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Renders an input field of the supplied type.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The field attributes.
	 * @param string $value The field value.
	 *
	 * @return string The rendered field.
	 */
	public static function input( $atts, $value = '' ) {

		$defaults = array(
			'type'         => 'text',
			'prefix'       => 'cn-',
			'class'        => array(),
			'id'           => '',
			'name'         => '',
			'style'        => array(),
			'data'         => array(),
			'autocomplete' => false,
			'checked'      => '',
			'readonly'     => false,
			'disabled'     => false,
			'required'     => false,
			'label'        => '',
			'before'       => '',
			'after'        => '',
			'parts'        => array( '%label%', '%field%' ),
			'layout'       => '%label%%field%',
			'return'       => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( in_array( $atts['type'], array( 'checkbox', 'hidden', 'number', 'radio', 'text' ), true ) ) {

			$messages = array(
				'checkbox' => 'Use `\Connections_Directory\Form\Field\Checkbox::create()`.',
				'hidden'   => 'Use `\Connections_Directory\Form\Field\Hidden::create()`.',
				'number'   => 'Use `\Connections_Directory\Form\Field\Number::create()`.',
				'radio'    => 'Use `\Connections_Directory\Form\Field\Radio::create()`.',
				'text'     => 'Use `\Connections_Directory\Form\Field\Text::create()`.',
			);

			_deprecated_argument(
				__METHOD__,
				'10.4',
				sprintf(
					'The `%s` parameter is no longer supported. %s',
					esc_attr( $atts['type'] ),
					esc_html( $messages[ $atts['type'] ] )
				)
			);

		} else {

			_deprecated_argument(
				__METHOD__,
				'10.4',
				sprintf(
					'The `%s` parameter is no longer supported.',
					esc_attr( $atts['type'] )
				)
			);
		}

		// If no `id` was supplied, bail.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		// The field name. If not supplied, use the id.
		$name = ! empty( $atts['name'] ) ? $atts['name'] : $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// An array to store the replacement strings for the label and field.
		$replace = array();

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = _string::applyPrefix( $atts['prefix'], $atts['class'] );
			$atts['id']    = _string::applyPrefix( $atts['prefix'], $atts['id'] );
		}

		// Add "required" to any classes that may have been supplied.
		// If the field is required, cast $atts['class'] as an array in case a string was supplied
		// and then tack the "required" value to the end of the array.
		$atts['class'] = $atts['required'] ? array_merge( (array) $atts['class'], array( 'required' ) ) : $atts['class'];

		$class = _escape::classNames( $atts['class'] );
		$class = 0 < strlen( $class ) ? " class=\"{$class}\"" : '';

		$id = _escape::id( $atts['id'] );
		$id = 0 < strlen( $id ) ? " id=\"{$id}\"" : '';

		$css   = _escape::css( _html::stringifyCSSAttributes( $atts['style'] ) );
		$style = 0 < strlen( $css ) ? " style=\"{$css}\"" : '';

		// Create the field label, if supplied.
		$replace[] = ! empty( $atts['label'] ) && 'hidden' !== $atts['type'] ? self::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => true ) ) : '';

		$replace[] = sprintf(
			'<input %1$s %2$s %3$s %4$s %5$s %6$s %7$s %8$s %9$s %10$s %11$s/>',
			self::attribute( 'type', $atts['type'] ),
			$class,
			$id,
			self::attribute( 'name', $name ),
			$style,
			self::attribute( 'data', $atts['data'] ),
			self::attribute( 'value', $value ),
			self::attribute( 'autocomplete', $atts['autocomplete'] ),
			! empty( $atts['checked'] ) ? $atts['checked'] : '',
			$atts['readonly'] ? 'readonly="readonly"' : '',
			disabled( $atts['disabled'], true, false )
		);

		$out = str_ireplace( $search, $replace, $atts['layout'] );

		$out = _string::replaceWhatWith( $out, ' ' );

		$html = $atts['before'] . $out . $atts['after'];

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * @deprecated 10.4
	 *
	 * @param array  $atts
	 * @param string $value
	 *
	 * @return string
	 */
	public static function textarea( $atts = array(), $value = '' ) {

		_deprecated_function( __METHOD__, '10.4', '\Connections_Directory\Form\Field\Textarea::create()' );

		$defaults = array(
			'type'        => 'text', // text | quicktag | rte
			'prefix'      => 'cn-',
			'class'       => array(),
			'id'          => '',
			'name'        => '',
			'cols'        => '',
			'rows'        => '',
			'maxlength'   => 0,
			'style'       => array(),
			'readonly'    => false,
			'disabled'    => false,
			'required'    => false,
			'placeholder' => '',
			'label'       => '',
			'before'      => '',
			'after'       => '',
			'parts'       => array( '%label%', '%field%' ),
			'layout'      => '%label%%field%',
			'return'      => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// If no `id` was supplied, bail.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		// The field name. If not supplied, use the id.
		$name = ! empty( $atts['name'] ) ? $atts['name'] : $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// An array to store the replacement strings for the label and field.
		$replace = array();

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = _string::applyPrefix( $atts['prefix'], $atts['class'] );
			$atts['id']    = _string::applyPrefix( $atts['prefix'], $atts['id'] );
		}

		// Add "required" to any classes that may have been supplied.
		// If the field is required, cast $atts['class'] as an array in case a string was supplied
		// and then tack the "required" value to the end of the array.
		$atts['class'] = $atts['required'] ? array_merge( (array) $atts['class'], array( 'required' ) ) : $atts['class'];

		// Create the field label, if supplied.
		$replace[] = ! empty( $atts['label'] ) ? self::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => true ) ) : '';

		$replace[] = sprintf(
			'<textarea %1$s %2$s %3$s %4$s %5$s %6$s %7$s %8$s %9$s %10$s>%11$s</textarea>',
			self::attribute( 'class', $atts['class'] ),
			self::attribute( 'id', $atts['id'] ),
			self::attribute( 'name', $name ),
			! empty( $atts['cols'] ) ? 'cols="' . absint( $atts['cols'] ) . '"' : '',
			! empty( $atts['rows'] ) ? 'rows="' . absint( $atts['rows'] ) . '"' : '',
			! empty( $atts['maxlength'] ) ? absint( $atts['maxlength'] ) : '',
			self::attribute( 'style', $atts['style'] ),
			$atts['readonly'] ? 'readonly="readonly"' : '',
			disabled( $atts['disabled'], true, false ),
			! empty( $atts['placeholder'] ) ? $atts['placeholder'] : '',
			esc_textarea( $value )
		);

		$out = str_ireplace( $search, $replace, $atts['layout'] );

		$out = _string::replaceWhatWith( $out, ' ' );

		$html = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Renders either a radio or checkbox group.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The group attributes.
	 * @param string $value The group item that will be marked as "CHECKED".
	 *
	 * @return string The rendered group.
	 */
	private static function group( $atts, $value = '' ) {

		$defaults = array(
			'type'     => 'radio',
			'prefix'   => 'cn-',
			'class'    => array( 'radio' ),
			'id'       => '',
			'style'    => array(),
			'options'  => array(),
			'readonly' => false,
			'required' => false,
			'label'    => '',
			'before'   => '',
			'after'    => '',
			'display'  => 'inline',
			'parts'    => array( '%label%', '%field%' ),
			'layout'   => '%field%%label%',
			'return'   => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( 'checkbox' === $atts['type'] ) {

			_deprecated_argument(
				__METHOD__,
				'10.4',
				sprintf(
					'The `%s` parameter is no longer supported. %s',
					esc_attr( $atts['type'] ),
					'\Connections_Directory\Form\Field\Checkbox_Group::create()'
				)
			);

		} elseif ( 'radio' === $atts['type'] ) {

			_deprecated_argument(
				__METHOD__,
				'10.4',
				sprintf(
					'The `%s` parameter is no longer supported. %s',
					esc_attr( $atts['type'] ),
					'\Connections_Directory\Form\Field\Radio_Group::create()'
				)
			);

		} else {

			_deprecated_argument(
				__METHOD__,
				'10.4',
				sprintf(
					'The `%s` parameter is no longer supported.',
					esc_attr( $atts['type'] )
				)
			);
		}

		// If no `id` was supplied, bail.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		// The field name.
		$name = ! empty( $atts['name'] ) ? $atts['name'] : $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = _string::applyPrefix( $atts['prefix'], $atts['class'] );
			$atts['id']    = _string::applyPrefix( $atts['prefix'], $atts['id'] );
		}

		// Add "required" to any classes that may have been supplied.
		// If the field is required, cast $atts['class'] as an array in case a string was supplied
		// and then tack the "required" value to the end of the array.
		$atts['class'] = $atts['required'] ? array_merge( (array) $atts['class'], array( 'required' ) ) : $atts['class'];

		$tag = 'block' == $atts['display'] ? 'div' : 'span';

		$out = '<' . $tag . ' class="cn-' . esc_attr( $atts['type'] ) . '-group">' . PHP_EOL;

		foreach ( $atts['options'] as $key => $label ) {

			// An array to store the replacement strings for the label and field.
			$replace = array();

			$out .= "\t" . '<' . $tag . ' class="cn-' . esc_attr( $atts['type'] ) . '-option">';

			// Create the field label, if supplied.
			$replace[] = ! empty( $label ) ? self::label( array( 'for' => $atts['id'] . '[' . $key . ']', 'label' => $label, 'return' => true ) ) : '';

			$replace[] = self::input(
				array(
					'type'     => $atts['type'],
					'prefix'   => '',
					'class'    => $atts['class'],
					'id'       => $atts['id'] . '[' . $key . ']',
					'name'     => $name,
					'style'    => $atts['style'],
					'readonly' => $atts['readonly'],
					'value'    => $value,
					'checked'  => checked( true, in_array( $key, (array) $value ), false ),
					'return'   => true,
				),
				$key
			);

			$out .= str_ireplace( $search, $replace, $atts['layout'] );

			$out .= "</$tag>" . PHP_EOL;
		}

		$out .= "</$tag>";

		$out = _string::replaceWhatWith( $out, ' ' );

		$html = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Renders a select field.
	 *
	 * @since      0.8
	 * @deprecated 10.4
	 *
	 * @param array  $atts  The field attributes.
	 * @param string $value The selected option.
	 *
	 * @return string The rendered field.
	 */
	public static function select( $atts, $value = '' ) {

		_deprecated_function( __METHOD__, '10.4', '\Connections_Directory\Form\Field\Select::create()' );

		$defaults = array(
			'prefix'   => 'cn-',
			'class'    => array(),
			'id'       => '',
			'style'    => array(),
			'default'  => '',
			'options'  => array(),
			'readonly' => false,
			'required' => false,
			'data'     => array(),
			'enhanced' => false,
			'label'    => '',
			'before'   => '',
			'after'    => '',
			'parts'    => array( '%label%', '%field%' ),
			'layout'   => '%label%%field%',
			'return'   => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// If no `id` was supplied, bail.
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		// The field name.
		$name = ! empty( $atts['name'] ) ? $atts['name'] : $atts['id'];

		// The field parts to be searched for in $atts['layout'].
		$search = $atts['parts'];

		// An array to store the replacement strings for the label and field.
		$replace = array();

		// Add the 'cn-enhanced-select' class for the jQuery Chosen Plugin will enhance the dropdown.
		if ( $atts['enhanced'] ) {

			$atts['class'] = array_merge( (array) $atts['class'], array( 'enhanced-select' ) );
		}

		// Prefix the `class` and `id` attribute.
		if ( ! empty( $atts['prefix'] ) ) {

			$atts['class'] = _string::applyPrefix( $atts['prefix'], $atts['class'] );
			$atts['id']    = _string::applyPrefix( $atts['prefix'], $atts['id'] );
		}

		// Add "required" to any classes that may have been supplied.
		// If the field is required, cast $atts['class'] as an array in case a string was supplied
		// and then tack the "required" value to the end of the array.
		if ( $atts['required'] ) {

			$atts['class'] = array_merge( (array) $atts['class'], array( 'required' ) );
		}

		$class = _escape::classNames( $atts['class'] );
		$class = 0 < strlen( $class ) ? " class=\"{$class}\"" : '';

		$id = _escape::id( $atts['id'] );
		$id = 0 < strlen( $id ) ? " id=\"{$id}\"" : '';

		$css   = _escape::css( _html::stringifyCSSAttributes( $atts['style'] ) );
		$style = 0 < strlen( $css ) ? " style=\"{$css}\"" : '';

		// Create the field label, if supplied.
		$replace['label'] = ! empty( $atts['label'] ) ? self::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => true ) ) : '';

		// Open the select.
		$replace['field'] = sprintf(
			'<select %1$s %2$s %3$s %4$s %5$s %6$s %7$s>',
			$class,
			$id,
			self::attribute( 'name', $name ),
			$style,
			self::attribute( 'data', $atts['data'] ),
			! empty( $atts['default'] ) && $atts['enhanced'] ? ' data-placeholder="' . esc_attr( $atts['default'] ) . '"' : '',
			$atts['readonly'] ? 'disabled="disabled"' : ''
		);

		// If the select is NOT a Chosen enhanced select; prepend the default option to the top of the options array.
		if ( ! empty( $atts['default'] ) && ! $atts['enhanced'] ) {

			$atts['options'] = (array) $atts['default'] + $atts['options'];
		}

		// If the select IS a Chosen enhanced select; prepend the blank option required for Chosen.
		if ( ! empty( $atts['default'] ) && $atts['enhanced'] ) {

			$atts['options'] = array( '' => '' ) + $atts['options'];
		}

		foreach ( $atts['options'] as $key => $label ) {

			$replace['field'] .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $key ),
				selected( $value, $key, false ),
				esc_html( $label )
			);
		}

		// Close the select.
		$replace['field'] .= '</select>';

		$out = str_ireplace( $search, $replace, $atts['layout'] );

		$out = _string::replaceWhatWith( $out, ' ' );

		if ( $atts['readonly'] ) {

			$out .= sprintf( '<input type="hidden" name="%1$s" value="%2$s" />', esc_attr( $name ), esc_attr( $value ) );
		}

		$html = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $html );
	}
}
