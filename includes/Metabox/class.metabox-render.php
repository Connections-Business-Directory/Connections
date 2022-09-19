<?php
/**
 * Class rendering the metaboxes for add/edit an entry.
 *
 * NOTE: This is a private class and should not be accessed directly.
 *
 * @package    Connections
 * @subpackage Metabox
 * @copyright  Copyright (c) 2013, Steven A. Zahm
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      0.8
 */

use Connections_Directory\Form\Field;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use function Connections_Directory\Form\Field\remapOptions as remapFieldOptions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnMetabox_Render
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnMetabox_Render {

	/**
	 * The array containing the registered metabox attributes.
	 *
	 * @since 0.8
	 *
	 * @var array
	 */
	private $metabox = array();

	/**
	 * The object being worked with.
	 *
	 * @since 0.8
	 *
	 * @var object
	 */
	public $object;

	/**
	 * The array of all registered slider settings.
	 *
	 * @since 0.8
	 *
	 * @var array
	 */
	private static $slider = array();

	/**
	 * Blank constructor.
	 */
	public function __construct() { /* Intentionally left blank. */ }

	/**
	 * Register the metaboxes with WordPress.
	 *
	 * NOTE: This method can be used to "late" register a metabox.
	 * Meaning if you need to register a metabox right before render.
	 * See the `manage.php` admin page file for a working example.
	 *
	 * @since 0.8
	 *
	 * @param string $pageHook The page hood / post type in which to add the metabox.
	 * @param array  $metabox  The array of metaboxes to add. NOTE: Valid field options, @see cnMetaboxAPI::add().
	 */
	public static function add( $pageHook, array $metabox ) {

		// Bail if params are empty.
		if ( empty( $pageHook ) || empty( $metabox ) ) {
			return;
		}

		// Use the core metabox API to render the metabox unless the metabox was registered with a custom callback to be used to render the metabox.
		// $callback = isset( $metabox['callback'] ) && ! empty( $metabox['callback'] ) ? $metabox['callback'] : array( new cnMetabox_Render(), 'render' );

		if ( is_admin() ) {

			add_meta_box(
				$metabox['id'],
				$metabox['title'],
				$metabox['callback'],
				$pageHook,
				$metabox['context'],
				$metabox['priority'],
				$metabox
			);

		} else {

			$metabox['field'] = isset( $metabox['field'] ) && ! empty( $metabox['field'] ) ? $metabox['field'] : array( 'public' );

			cnMetaboxAPI::add( $metabox );
		}

	}

	/**
	 * Used to render the registered metaboxes on the frontend.
	 * NOTE: To render the metaboxes on an admin page use do_meta_boxes().
	 *
	 * Accepted option for the $atts property are:
	 *     id (array) The metabox ID to render.
	 *     order (array) An indexed array of metabox IDs that should be rendered in the order in the array.
	 *         NOTE: Any registered metabox ID not supplied in `order` means `exclude` is implied.
	 *     exclude (array) An indexed array of metabox IDs that should be excluded from being rendered.
	 *     include (array) An indexed array of metabox IDs that should be rendered.
	 *         NOTE: Metabox IDs in `exclude` outweigh metabox IDs in include. Meaning if the same metabox ID
	 *         exists in both, the metabox will be excluded.
	 *
	 * @since 0.8
	 *
	 * @param array  $atts   The attributes array.
	 * @param object $object An instance the cnEntry object.
	 */
	public static function metaboxes( array $atts, $object ) {

		$metaboxes = array();

		$defaults = array(
			'id'      => '',
			'order'   => array(),
			'exclude' => array(), //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'include' => array(),
			'hide'    => array(),
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! empty( $atts['id'] ) ) {

			$metaboxes[ $atts['id'] ] = cnMetaboxAPI::get( $atts['id'] );

		} elseif ( ! empty( $atts['order'] ) ) {

			// If the metabox order has been supplied, sort them as supplied. Exclude is implied.
			// Meaning, if a metabox ID is not supplied in $atts['order'], they will be excluded.
			foreach ( $atts['order'] as $id ) {

				$metaboxes[ $id ] = cnMetaboxAPI::get( $id );
			}

		} else {

			$metaboxes = cnMetaboxAPI::get();
		}

		foreach ( $metaboxes as $id => $metabox ) {

			// Since custom metaboxes can be enabled/disabled, there's a possibility that there will
			// be a saved metabox in the settings that no longer exists. Let's catch this and continue.
			if ( empty( $metabox ) ) {
				continue;
			}

			// Exclude/Include the metaboxes that have been requested to exclude/include.
			if ( ! empty( $atts['exclude'] ) ) {

				if ( in_array( $id, $atts['exclude'] ) && ! in_array( $id, $atts['hide'] ) ) {
					continue;
				}

			} else {

				if ( ! empty( $atts['include'] ) ) {

					if ( ! in_array( $id, $atts['include'] ) && ! in_array( $id, $atts['hide'] ) ) {
						continue;
					}
				}
			}

			$display = in_array( $id, $atts['hide'] ) ? 'none' : 'block';

			// Static string, not user input, no need to escape.
			echo '<div id="' . esc_attr( "cn-metabox-{$metabox['id']}" ) . '" class="cn-metabox" style="display: ' . $display . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<h3 class="cn-metabox-title">' . esc_html( $metabox['title'] ) . '</h3>';
				echo '<div class="cn-metabox-inside">';

					if ( is_callable( $metabox['callback'] ) ) {

						call_user_func( $metabox['callback'], $object, $metabox );

					} else {

						if ( is_string( $metabox['callback'] ) ) {

							$callback = $metabox['callback'];

						} elseif ( is_array( $metabox['callback'] ) ) {

							if ( is_object( $metabox['callback'][0] ) ) {

								$callback = get_class( $metabox['callback'][0] ) . '::' . $metabox['callback'][1];

							} else {

								$callback = implode( '::', $metabox['callback'] );
							}

						}
						/* translators: Callback function name. */
						echo '<p>' , esc_html( sprintf( __( 'Invalid callback: %s', 'connections' ), $callback ) ) , '</p>';
					}

				echo '<div class="cn-clear"></div>';
				echo '</div>';
			echo '</div>';
		}

	}

	/**
	 * Render the metabox.
	 *
	 * @internal
	 * @since 0.8
	 *
	 * @param $object
	 * @param $metabox
	 */
	public function render( $object, $metabox ) {

		$this->object  = $object;
		$this->metabox = $metabox['args'];
		$sections      = isset( $metabox['args']['sections'] ) && ! empty( $metabox['args']['sections'] ) ? $metabox['args']['sections'] : array();
		$fields        = isset( $metabox['args']['fields'] ) && ! empty( $metabox['args']['fields'] ) ? $metabox['args']['fields'] : array();

		// Use nonce for verification.
		echo '<input type="hidden" name="wp_meta_box_nonce" value="' , esc_attr( wp_create_nonce( basename( __FILE__ ) ) ) , '" />';

		// If metabox sections have been registered, loop through them.
		if ( ! empty( $sections ) ) {

			foreach ( $sections as $section ) {

				$this->section( $section );
			}
		}

		// If metabox fields have been supplied, loop through them.
		if ( ! empty( $fields ) ) {

			echo '<div class="cn-metabox-section">';

			echo '<table class="cn-metabox-table form-table"><tbody>';

				$this->fields( $fields );

			echo '</tbody></table>';

			echo '</div>';
		}
	}

	/**
	 * Render the metabox sections.
	 *
	 * @since 0.8
	 *
	 * @param array $section An array containing the sections of the metabox.
	 */
	private function section( $section ) {

		echo '<div class="cn-metabox-section">';

		if ( isset( $section['name'] ) && ! empty( $section['name'] ) ) {

			printf(
				'<h4 class="cn_metabox_section_name">%1$s</h4>',
				esc_html( $section['name'] )
			);
		}

		if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {

			printf(
				'<p>%1$s</p>',
				esc_html( $section['desc'] )
			);
		}

		if ( isset( $section['fields'] ) && ! empty( $section['fields'] ) ) {

			echo '<table class="cn-metabox-table form-table"><tbody>';

				$this->fields( $section['fields'] );

			echo '</tbody></table>';
		}


		echo '</div>';
	}

	/**
	 * Render the fields registered to the metabox.
	 *
	 * The $fields property is an indexed array of fields and their properties.
	 * Accepted option for are:
	 *     id (string) The field ID. This value MUST be unique.
	 *     desc (string) [optional] The field description.
	 *     type (string) The type of field which should be registered. This can be any of the supported field types or a custom field type.
	 *         Core supported field types are:
	 *             checkbox
	 *             checkboxgroup
	 *             radio
	 *             radio_inline
	 *             select
	 *             text (input)
	 *             textarea
	 *             datepicker
	 *             slider
	 *             quicktag
	 *             rte
	 *     value (mixed) string | array [optional] The function name or class method to be used retrieve a value for the field.
	 *     size (string) [optional] The size if the text input and textarea field types.
	 *         NOTE: Only used for the `text` field type. Valid options: small', 'regular' or 'large'
	 *         NOTE: Only used for the `textarea` field type. Valid options: small' or 'large'
	 *     options (mixed) string | array [optional] Valid value depends on the field type being rendered.
	 *         Field type / valid value for options
	 *             checkboxgroup (array) An associative array where the key is the checkbox value and the value is the checkbox label.
	 *             radio / radio_inline (array) An associative array where the key is the radio value and the value is the radio label.
	 *             select (array) An associative array where the key is the option value and the value is the option name.
	 *             rte (array) @link http://codex.wordpress.org/Function_Reference/wp_editor#Arguments
	 *             slider (array) The slider options.
	 *                 min (int) The minimum slider step.
	 *                 max (int) The maximum slider step.
	 *                 step (int) The step the slider steps at.
	 *     default    (mixed) The default value to be used.
	 *
	 * @since 0.8
	 *
	 * @param array $fields An indexed array of fields to render.
	 */
	private function fields( $fields ) {

		foreach ( $fields as $field ) {

			$defaults = array(
				'before' => '',
				'after'  => '',
				'desc'   => '',
			);

			$field = wp_parse_args( $field, $defaults );

			// If the meta field has a specific method defined call the method and set the field value.
			// Otherwise, assume pulling from the meta table of the supplied object.
			if ( isset( $field['value'] ) && ! empty( $field['value'] ) ) {

				$value = call_user_func( array( $this->object, $field['value'] ) );

			} else {

				$value = $this->object->getMeta( array( 'key' => $field['id'], 'single' => true ) );
			}

			if ( empty( $value ) ) {
				$value = isset( $field['default'] ) ? $field['default'] : '';
			}

			/**
			 * Apply custom classes to a metabox table.
			 *
			 * @since 8.3.4
			 *
			 * @param array  $class An indexed array of classes that should be applied to the table element.
			 * @param string $type  The field type.
			 * @param string $id    The field id.
			 */
			$class = apply_filters( 'cn_metabox_table_class', array( "cn-metabox-type-{$field['type']}" ), $field['type'], $field['id'] );

			/**
			 * Apply a custom id to a metabox table.
			 *
			 * @since 8.3.4
			 *
			 * @param string $id The field id.
			 */
			$id = apply_filters( 'cn_metabox_table_id', "cn-metabox-id-{$field['id']}" );

			/**
			 * Apply custom classes to a metabox table.
			 *
			 * @since 8.3.4
			 *
			 * @param array  $css  An associative array of inline style attributes where the array key is the property and the array value is the property value.
			 * @param string $type The field type.
			 * @param string $id   The field id.
			 */
			$css = apply_filters( 'cn_metabox_table_style', array(), $field['type'], $field['id'] );

			$css   = _escape::css( _html::stringifyCSSAttributes( $css ) );
			$style = 0 < strlen( $css ) ? ' style="' . $css . '"' : '';

			// The `$style` attribute tag is escaped above. If it is an empty string, no style tag is added to the `tr`.
			echo '<tr class="' , _escape::classNames( $class ) , '" id="' , _escape::id( $id ) , '"' , $style , '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			// For a label to be rendered the $field['name'] has to be supplied.
			// Show the label if $field['show_label'] is TRUE, OR, if it is not supplied assume TRUE and show it anyway.
			// The result will be the label will be shown unless specifically $field['show_label'] is set to FALSE.
			if ( ( isset( $field['name'] ) && ! empty( $field['name'] ) ) && ( ! isset( $field['show_label'] ) || true == $field['show_label'] ) ) {

				echo '<th class="cn-metabox-label">' . esc_html( $field['name'] ) . '</th>';

			} elseif ( ( isset( $field['name'] ) && ! empty( $field['name'] ) ) && ( isset( $field['show_label'] ) && true == $field['show_label'] ) ) {

				echo '<th class="cn-metabox-label">' . esc_html( $field['name'] ) . '</th>';

			} elseif ( ! isset( $field['show_label'] ) || false == $field['show_label'] ) {

				echo '<th class="cn-metabox-label-empty">&nbsp;</th>';
			}

			echo '<td>';

			// Developer parameter, not user input.
			echo empty( $field['before'] ) ? '' : $field['before']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			/**
			 * Apply custom classes to the field type container element.
			 *
			 * NOTE: The dynamic portion of the hook is the field type.
			 *
			 * @since 8.3.4
			 *
			 * @param array  $class An indexed array of classes that should be applied to the element.
			 * @param string $id    The field id.
			 */
			$class = apply_filters( "cn_metabox_{$field['type']}_class", array( "cn-meta-field-type-{$field['type']}" ), $field['id'] );

			/**
			 * Apply a custom id to the field type container element.
			 *
			 * NOTE: The dynamic portion of the hook is the field type.
			 *
			 * @since 8.3.4
			 *
			 * @param string $id The field id.
			 */
			$id = apply_filters( "cn_metabox_{$field['type']}_id", '' );

			/**
			 * Apply custom classes to a field type container element.
			 *
			 * NOTE: The dynamic portion of the hook is the field type.
			 *
			 * @since 8.3.4
			 *
			 * @param array  $style An associative array of inline style attributes
			 *                      where the array key is the property and the array value is the property value.
			 * @param string $id    The field id.
			 */
			$style = apply_filters( "cn_metabox_{$field['type']}_style", array(), $field['id'] );

			$class = _escape::classNames( $class );
			$class = 0 < strlen( $class ) ? " class=\"{$class}\"" : '';

			$id = _escape::id( $id );
			$id = 0 < strlen( $id ) ? " id=\"{$id}\"" : '';

			$css   = _escape::css( _html::stringifyCSSAttributes( $style ) );
			$style = 0 < strlen( $css ) ? " style=\"{$css}\"" : '';

			/**
			 * Chance to manipulate the field value before rendering the field.
			 *
			 * NOTE: The dynamic portion of the hook is the field id.
			 *
			 * @since 0.8
			 *
			 * @param mixed $value The field value.
			 * @param array $field The field attributes array.
			 */
			$value = apply_filters( "cn_meta_field_value-{$field['id']}", $value, $field, $this );

			switch ( $field['type'] ) {

				case 'checkbox':
					Field\Checkbox::create()
								  ->setId( $field['id'] )
								  ->addClass( 'cn-checkbox' )
								  ->setName( $field['id'] )
								  ->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
								  ->setRequired( isset( $field['required'] ) && true === $field['required'] )
								  ->maybeIsChecked( $value )
								  ->addLabel(
									  Field\Label::create()
												 ->setFor( $field['id'] )
												 ->text( $field['desc'] )
								  )
								  ->prepend( '<div' . $class . $id . $style . '>' )
								  ->append( '</div>' )
								  ->render();

					break;

				case 'checkboxgroup':
				case 'checkbox-group':
					remapFieldOptions( $field );

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					Field\Checkbox_Group::create()
										->setId( $field['id'] )
										->addClass( 'cn-checkbox' )
										->setName( $field['id'] )
										->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
										->createInputsFromArray( $field['options'] )
										->addAttribute( 'aria-describedby', "{$field['id']}-description" )
										->setValue( $value )
										->prepend( '<div' . $class . $id . $style . '>' )
										->append( '</div>' )
										->render();

					break;

				case 'radio':
					remapFieldOptions( $field );

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					Field\Radio_Group::create()
									 ->setId( $field['id'] )
									 ->addClass( 'cn-radio-option' )
									 ->setName( $field['id'] )
									 ->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
									 ->createInputsFromArray( $field['options'] )
									 ->addAttribute( 'aria-describedby', "{$field['id']}-description" )
									 ->setValue( $value )
									 ->prepend( '<div' . $class . $id . $style . '>' )
									 ->append( '</div>' )
									 ->render();

					break;

				case 'radio_inline':
				case 'radio-inline':
					remapFieldOptions( $field );

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					Field\Radio_Group::create()
									 ->setId( $field['id'] )
									 ->addClass( 'cn-radio-option' )
									 ->setName( $field['id'] )
									 ->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
									 ->createInputsFromArray( $field['options'] )
									 ->addAttribute( 'aria-describedby', "{$field['id']}-description" )
									 ->setValue( $value )
									 ->setContainer( 'span' )
									 ->prepend( '<div' . $class . $id . $style . '>' )
									 ->append( '</div>' )
									 ->render();

					break;

				case 'select':
					remapFieldOptions( $field );

					Field\Select::create()
						->setId( $field['id'] )
						->addClass( 'cn-select' )
						->setName( $field['id'] )
						->createOptionsFromArray( $field['options'] )
						->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
						->setRequired( isset( $field['required'] ) && true === $field['required'] )
						->addAttribute( 'aria-describedby', "{$field['id']}-description" )
						->setValue( $value )
						->prepend( '<div' . $class . $id . $style . '>' )
						->append( '</div>' )
						// ->setEnhanced( true )
						// ->addDefaultOption( Field\Option::create()->setText( 'Choose an Option' ) )
						->render();

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					break;

				case 'text':
					$sizes = array( 'small', 'regular', 'large' );
					$size  = _array::get( $field, 'size', 'large' );

					Field\Text::create()
							  ->setId( $field['id'] )
							  ->addClass( in_array( $size, $sizes ) ? "{$size}-text" : 'large-text' )
							  ->setName( $field['id'] )
							  ->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
							  ->addAttribute( 'aria-describedby', "{$field['id']}-description" )
							  ->setValue( $value )
							  ->prepend( '<div' . $class . $id . $style . '>' )
							  ->append( '</div>' )
							  ->render();

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					break;

				case 'textarea':
					$sizes = array( 'small', 'large' );
					$size  = _array::get( $field, 'size', 'small' );

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					Field\Textarea::create()
								  ->setId( $field['id'] )
								  ->addClass( in_array( $size, $sizes ) ? "{$size}-text" : 'small-text' )
								  ->setName( $field['id'] )
								  ->addAttribute( 'aria-describedby', "{$field['id']}-description" )
								  ->addAttribute( 'rows', 10 )
								  ->addAttribute( 'cols', 50 )
								  ->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
								  ->setValue( $value )
								  ->prepend( '<div' . $class . $id . $style . '>' )
								  ->append( '</div>' )
								  ->render();

					break;

				case 'datepicker':
					printf(
						'<input type="text" class="cn-datepicker" id="%1$s" name="%1$s" value="%2$s"%3$s/>',
						esc_attr( $field['id'] ),
						! empty( $value ) ? esc_attr( date( 'm/d/Y', strtotime( $value ) ) ) : '',
						// Static string, not user input, no need to escape.
						isset( $field['readonly'] ) && true === $field['readonly'] ? ' readonly="readonly"' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);

					wp_enqueue_script( 'jquery-ui-datepicker' );
					wp_enqueue_style( 'cn-admin-jquery-datepicker' );
					add_action( 'admin_print_footer_scripts', array( __CLASS__, 'datepickerJS' ) );
					add_action( 'wp_footer', array( __CLASS__, 'datepickerJS' ) );

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					break;

				case 'colorpicker':
					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					printf(
						'<input type="text" class="cn-colorpicker" id="%1$s" name="%1$s" value="%2$s"%3$s/>',
						esc_attr( $field['id'] ),
						esc_attr( $value ),
						isset( $field['readonly'] ) && true === $field['readonly'] ? ' readonly="readonly"' : ''
					);

					wp_enqueue_style( 'wp-color-picker' );

					if ( is_admin() ) {

						wp_enqueue_script( 'wp-color-picker' );
						add_action( 'admin_print_footer_scripts', array( __CLASS__, 'colorpickerJS' ) );

					} else {

						/*
						 * WordPress seems to only register the color picker scripts for use in the admin.
						 * So, for the frontend, we must manually register and then enqueue.
						 * @url http://wordpress.stackexchange.com/a/82722/59053
						 */

						//phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
						wp_enqueue_script(
							'iris',
							admin_url( 'js/iris.min.js' ),
							array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
							false,
							1
						);

						//phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
						wp_enqueue_script(
							'wp-color-picker',
							admin_url( 'js/color-picker.min.js' ),
							array( 'iris' ),
							false,
							1
						);

						$colorpicker_l10n = array(
							'clear'         => __( 'Clear', 'connections' ),
							'defaultString' => __( 'Default', 'connections' ),
							'pick'          => __( 'Select Color', 'connections' ),
							'current'       => __( 'Current Color', 'connections' ),
						);

						wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );

						add_action( 'wp_footer', array( __CLASS__, 'colorpickerJS' ) );
					}

					break;

				case 'slider':
					// Set the slider defaults.
					$defaults = array(
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
						'value' => 0,
					);

					$field['options'] = wp_parse_args( isset( $field['options'] ) ? $field['options'] : array(), $defaults );

					printf(
						'<div class="cn-slider-container" id="cn-slider-%1$s"></div><input type="text" class="small-text" id="%1$s" name="%1$s" value="%2$s"%3$s/>',
						esc_attr( $field['id'] ),
						absint( $value ),
						isset( $field['readonly'] ) && true === $field['readonly'] ? ' readonly="readonly"' : ''
					);

					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					$field['options']['value'] = absint( $value );

					self::$slider[ $field['id'] ] = $field['options'];

					wp_enqueue_script( 'jquery-ui-slider' );
					add_action( 'admin_print_footer_scripts', array( __CLASS__, 'sliderJS' ) );
					add_action( 'wp_footer', array( __CLASS__, 'sliderJS' ) );

					break;

				case 'quicktag':
					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					Field\Quicktag::create()
								  ->setId( $field['id'] )
								  ->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
								  ->setValue( $value )
								  ->render();

					break;

				case 'rte':
					Field\Description::create()
									 ->addClass( 'description' )
									 ->setId( "{$field['id']}-description" )
									 ->text( $field['desc'] )
									 ->render();

					// Set the rte defaults.
					$defaults = array(
						'textarea_name' => sprintf( '%1$s', $field['id'] ),
					);

					$settings = wp_parse_args( _array::get( $field, 'options', array() ), $defaults );

					Field\Rich_Text::create()
								   ->setId( $field['id'] )
								   ->setPrefix( 'cn' )
								   ->rteSettings( $settings )
								   ->setValue( $value )
								   ->render();

					break;

				default:
					do_action( 'cn_meta_field-' . $field['type'], $field, $value, $this->object );
					break;
			}

			// Developer parameter, not user input.
			echo empty( $field['after'] ) ? '' : $field['after']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '</td>' , '</tr>';
		}
	}

	/**
	 * Outputs the JS necessary to support the datepicker.
	 *
	 * NOTE: Incredibly I came to the same solution as used in WordPress core @see wp_localize_jquery_ui_datepicker()
	 *
	 * @todo Should use @see cnFormatting::dateFormatPHPTojQueryUI() instead to convert the PHP datetime format to a
	 *       compatible jQueryUI Datepicker compatibly format.
	 *
	 * @internal
	 * @since 0.8
	 */
	public static function datepickerJS() {

?>

<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the jQuery UI Datepicker to the date input fields.
 */
jQuery(document).ready( function($){

	if ($.fn.datepicker) {

		$('.postbox, .cn-metabox').on( 'focus', '.cn-datepicker', function(e) {

			$(this).datepicker({
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				yearRange: 'c-100:c+10',
				dateFormat: 'yy-mm-dd',
				// beforeShow: function(i) { if ( $( i ).attr('readonly') ) { return false; } }
			}).keydown(false);

			e.preventDefault();
		});
	}
});
/* ]]> */</script>

<?php

	}

	/**
	 * Outputs the JS necessary to support the color picker.
	 *
	 * @internal
	 * @since 0.8
	 */
	public static function colorpickerJS() {

?>

<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the Color Picker to the input fields.
 */
jQuery(document).ready( function($){

	$('.cn-colorpicker').wpColorPicker();
});
/* ]]> */</script>

<?php

	}

	/**
	 * Outputs the JS necessary to support the sliders.
	 *
	 * @internal
	 * @since 0.8
	 */
	public static function sliderJS() {
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact,Generic.WhiteSpace.ScopeIndent.Incorrect,PEAR.Functions.FunctionCallSignature.Indent
?>

<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the jQuery UI Slider input fields.
 */
jQuery(document).ready( function($){

<?php
foreach ( self::$slider as $id => $option ) {

	printf(
	'$( "#cn-slider-%1$s" ).slider({
		value: %2$d,
		min: %3$d,
		max: %4$d,
		step: %5$d,
		slide: function( event, ui ) {
			$( "#%1$s" ).val( ui.value );
		}
	});',
		esc_attr( $id ),
		wp_json_encode( $option['value'] ),
		wp_json_encode( $option['min'] ),
		wp_json_encode( $option['max'] ),
		wp_json_encode( $option['step'] )
	);

}
?>
});
/* ]]> */</script>

<?php
// phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact,Generic.WhiteSpace.ScopeIndent.Incorrect,PEAR.Functions.FunctionCallSignature.Indent
	}

}
