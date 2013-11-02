<?php

/**
 * Class registering the metaboxes for add/edit an entry.
 *
 * @package     Connections
 * @subpackage  Metabox API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnMetaboxAPI {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.8
	 * @var (object)
	*/
	private static $instance;

	/**
	 * The metaboxes.
	 * @var (array)
	 */
	private static $metaboxes = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.8
	 * @see cnMetabox::init()
	 * @see cnMetabox();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class, if it has already been initialized, return the intialized instance.
	 *
	 * @access public
	 * @since 0.8
	 * @see cnMetabox()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			// Action for extensions to hook into to add custom metaboxes/fields.
			do_action( 'cn_metabox', self::$instance );

			// Add the actions to show the meatboxes on the registered pages.
			foreach ( self::$metaboxes as $id => $metabox ) {

				foreach ( $metabox['pages'] as $page ){

					add_action( 'load-' . $page, array( __CLASS__, 'register' ) );
				}
			}
		}
	}

	/**
	 * Return an instance of the class.
	 *
	 * @access public
	 * @since 0.8
	 * @return (object) cnMetabox
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Public method to add metaboxes.
	 *
	 * @access public
	 * @since 0.8
	 * @param (array) $metabox
	 */
	public static function add( array $metaboxes ) {

		// Grab an instance of Connections.
		$instance = Connections_Directory();

		foreach ( $metaboxes as $metabox ) {

			$metabox['pages']    = empty( $metabox['pages'] ) ? array( $instance->pageHook->add, $instance->pageHook->manage ) : $metabox['pages'];
			$metabox['context']  = empty( $metabox['context'] ) ? 'normal' : $metabox['context'];
			$metabox['priority'] = empty( $metabox['priority'] ) ? 'default' : $metabox['priority'];

			self::$metaboxes[ $metabox['id'] ] = $metabox;
		}
	}

	/**
	 * Remove a registered metabox.
	 *
	 * @access public
	 * @since 0.8
	 * @param  (string) $id The metabox id to remove.
	 * @return (bool)
	 */
	public static function remove( string $id ) {

		if ( isset( self::$metaboxes[ $id ] ) ) {

			unset( self::$metaboxes[ $id ] );
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Register the metaboxes.
	 *
	 * @access private
	 * @since 0.8
	 * @return (void)
	 */
	public static function register() {

		global $hook_suffix;

		foreach ( self::$metaboxes as $metabox ) {

			if ( in_array( $hook_suffix, $metabox['pages'] ) ) cnMetabox_Render::add( $hook_suffix, $metabox );
		}
	}

}

/**
 * Class rendering the metaboxes for add/edit an entry.
 *
 * NOTE: This is a private class and should not be accessed directly.
 *
 * @package     Connections
 * @subpackage  Metabox
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

class cnMetabox_Render {

	/**
	 * The array containing the registered metaboxes.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private $metabox = array();

	/**
	 * The array containing the current metabox sections.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	// private $sections = array();

	/**
	 * The object being worked with.
	 *
	 * @access private
	 * @since 0.8
	 * @var object
	 */
	private $object;

	/**
	 * The meta data for a cnEntry object.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private $meta = array();

	/**
	 * The array of all registerd quicktag textareas.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $quickTagIDs = array();

	/**
	 * The array of all registerd slider settings.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $slider = array();

	public function __construct() {

		/* Intentially left blank. */
	}

	/**
	 * Register the metaboxes with WordPress.
	 *
	 * @access private
	 * @since 0.8
	 * @uses add_meta_box()
	 * @param string $pageHook The page hood / post type in which to add the metabox.
	 * @param array  $metabox  The array of metaboxes to add.
	 */
	public static function add( $pageHook = '', array $metabox = array() ) {

		if ( ! empty( $pageHook) && ! empty( $metabox ) ) {

			$callback = isset( $metabox['callback'] ) && ! empty( $metabox['callback'] ) ? $metabox['callback'] : array( new cnMetabox_Render(), 'render' );

			add_meta_box(
				$metabox['id'],
				$metabox['title'],
				$callback,
				$pageHook,
				$metabox['context'],
				$metabox['priority'],
				$metabox
			);

		}
	}

	/**
	 * Render the metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @return void
	 */
	public function render( $object, $metabox ) {

		$this->object  = $object;
		$this->metabox = $metabox['args'];
		$sections      = isset( $metabox['args']['sections'] ) && ! empty( $metabox['args']['sections'] ) ? $metabox['args']['sections'] : array();
		$fields        = isset( $metabox['args']['fields'] )   && ! empty( $metabox['args']['fields'] )   ? $metabox['args']['fields'] : array();

		// Use nonce for verification
		echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';

		// If metabox sections have be registered, loop thru them.
		if ( ! empty( $sections ) ) {

			foreach ( $sections as $section ) {

				$this->section( $section );
			}
		}

		// If metabox fields have been supplied, loop thru them.
		if ( ! empty( $fields ) ) {

			echo '<div class="cn-metabox-section">';

			echo '<table class="form-table cn-metabox"><tbody>';

				$this->fields( $fields );

			echo '</tbody></table>';

			echo '</div>';
		}
	}

	/**
	 * Render the metabox sections.
	 *
	 * @access private
	 * @since 0.8
	 * @param  array $section An array containing the sections of the metabox.
	 * @return string
	 */
	private function section( $section ) {

		echo '<div class="cn-metabox-section">';

		if ( isset( $section['name'] ) && ! empty( $section['name'] ) ) {

			printf( '<h4 class="cn_metabox_section_name">%1$s</h4>',
				esc_html( $section['name'] )
			);
		}

		if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {

			printf( '<p>%1$s</p>',
				esc_html( $section['desc'] )
			);
		}

		if ( isset( $section['fields'] ) && ! empty( $section['fields'] ) ) {

			echo '<table class="form-table cn-metabox"><tbody>';

				$this->fields( $section['fields'] );

			echo '</tbody></table>';
		}


		echo '</div>';
	}

	/**
	 * Render the fields registered to the metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @global $wp_version
	 * @param $fields	array 	Render the metabox section fields.
	 * @return string
	 */
	private function fields( $fields ) {
		global $wp_version;

		// do_action( 'cn_metabox_table_before', $entry, $meta, $this->metabox );

		// echo '<table class="form-table cn-metabox"><tbody>';

		foreach ( $fields as $field ) {

			// If the meta field has a specific method defined call the method  and set the field value.
			// Otherwise, assume pulling from the meta table of the supplied object.
			if ( isset( $field['value'] ) && ! empty( $field['value'] ) ) {

				$value = call_user_func( array( $this->object, $field['value'] ) );

			} else {

				$value = $this->object->getMeta( $field['id'], TRUE );
			}

			echo '<tr class="cn-metabox-type-'. sanitize_html_class( $field['type'] ) .' cn-metabox-id-'. sanitize_html_class( $field['id'] ) .'">';

			// For a label to be rendered the $field['name'] has to be supplied.
			// Show the label if $field['show_label'] is TRUE, OR, if it is not supplied and $field['show_label'] show it anyway.
			// The result will be the label will be shown unless specifically $field['show_label'] is set to FALSE.
			if ( ( isset( $field['name'] ) && ! empty( $field['name'] ) ) && ( ! isset( $field['show_label'] ) || $field['show_label'] == TRUE ) ) {

				printf( '<th><label for="%1$s">%2$s</label></th>',
					esc_attr( $field['id'] ),
					esc_html( $field['name'] )
				);

			} elseif ( isset( $field['name'] ) && ! empty( $field['name'] ) ) {

				printf( '<td><label for="%1$s">%2$s</label></td>',
					esc_attr( $field['id'] ),
					esc_html( $field['name'] )
				);

			} else {

				echo '<td style="display:none;">&nbsp;</td>';
			}

			echo '<td>';

			echo empty( $field['before'] ) ? '' : $field['before'];

			switch ( $field['type'] ) {

				case 'checkbox':

					$value = 1;

					printf( '<input type="checkbox" class="checkbox" id="%1$s" name="%1$s" value="1" %2$s/>',
						esc_attr( $field['id'] ),
						$checked = isset( $value ) ? checked( 1, $value, FALSE ) : ''
					);

					break;

				case 'checkboxgroup':

					// For input groups we want to render the description before the field.
					// Lets render it and unset it so it does not render twice.
					if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

						printf( '<div class="description"> %1$s</div>',
							esc_html( $field['desc'] )
						);

						unset( $field['desc'] );
					}

					echo '<div class="cn-checkbox-group">';

					foreach ( $field['options'] as $key => $label ) {

						echo '<div class="cn-checkbox-option">';

						printf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[]" value="%2$s"%3$s/>',
							esc_attr( $field['id'] ),
							esc_attr( $key ),
							checked( TRUE , in_array( $key, (array) $value ) , FALSE )
						);

						printf( '<label for="%1$s[%2$s]"> %3$s</label>',
							esc_attr( $field['id'] ),
							esc_attr( $key ),
							esc_html( $label )
						);

						echo '</div>';
					}

					echo '</div>';

					break;

				case 'radio':

					// For input groups we want to render the description before the field.
					// Lets render it and unset it so it does not render twice.
					if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

						printf( '<div class="description"> %1$s</div>',
							esc_html( $field['desc'] )
						);

						unset( $field['desc'] );
					}

					echo '<div class="cn-radio-group">';

					foreach ( $field['options'] as $key => $label ) {

						echo '<div class="cn-radio-option">';

						printf( '<input type="radio" class="checkbox" id="%1$s[%2$s]" name="%1$s[]" value="%2$s"%3$s/>',
							esc_attr( $field['id'] ),
							esc_attr( $key ),
							checked( TRUE , in_array( $key, (array) $value ) , FALSE )
						);

						printf( '<label for="%1$s[%2$s]"> %3$s</label>',
							esc_attr( $field['id'] ),
							esc_attr( $key ),
							esc_html( $label )
						);

						echo '</div>';
					}

					echo '</div>';

					break;

				case 'radio_inline':

					// For input groups we want to render the description before the field.
					// Lets render it and unset it so it does not render twice.
					if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

						printf( '<div class="description"> %1$s</div>',
							esc_html( $field['desc'] )
						);

						unset( $field['desc'] );
					}

					echo '<div class="cn-radio-group">';

					foreach ( $field['options'] as $key => $label ) {

						echo '<span class="cn-radio-option">';

						printf( '<input type="radio" class="checkbox" id="%1$s[%2$s]" name="%1$s[]" value="%2$s"%3$s/>',
							esc_attr( $field['id'] ),
							esc_attr( $key ),
							checked( TRUE , in_array( $key, (array) $value ) , FALSE )
						);

						printf( '<label for="%1$s[%2$s]"> %3$s</label>',
							esc_attr( $field['id'] ),
							esc_attr( $key ),
							esc_html( $label )
						);

						echo '</span>';
					}

					echo '</div>';

					break;

				case 'text':

					$sizes = array( 'small', 'regular', 'large' );

					printf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>',
						isset( $field['size'] ) && ! empty( $field['size'] ) && in_array( $field['size'], $sizes ) ? esc_attr( $field['size'] ) : 'large',
						esc_attr( $field['id'] ),
						sanitize_text_field( $value )
					);

					break;

				case 'textarea':

					$sizes = array( 'small', 'large' );

					// For text areas we want to render the description before the field.
					// Lets render it and unset it so it does not render twice.
					if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

						printf( '<div class="description"> %1$s</div>',
							esc_html( $field['desc'] )
						);

						unset( $field['desc'] );
					}

					printf( '<textarea rows="10" cols="50" class="%1$s-text" id="%2$s" name="%2$s">%3$s</textarea>',
						isset( $field['size'] ) && ! empty( $field['size'] ) && in_array( $field['size'], $sizes ) ? esc_attr( $field['size'] ) : 'small',
						esc_attr( $field['id'] ),
						esc_textarea( '$value' )
					);

					break;

				case 'datepicker':

					printf( '<input type="text" class="cn-datepicker" id="%1$s" name="%1$s" value="%2$s"/>',
						esc_attr( $field['id'] ),
						date( 'm/d/Y', strtotime( 'August 22, 2013' ) )
					);

					wp_enqueue_script('jquery-ui-datepicker');
					add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'datepickerJS' ) );

					break;

				case 'colorpicker':


					break;

				case 'slider':

					$value = 10;

					// Set the slider defaults.
					$defaults = array(
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
						'value' => 0
					);

					$atts = wp_parse_args( isset( $field['options'] ) ? $field['options'] : array(), $defaults );

					printf( '<div class="cn-slider-container" id="cn-slider-%1$s"></div><input type="text" class="small-text" id="%1$s" name="%1$s" value="%2$s"/>',
						esc_attr( $field['id'] ),
						absint( $value )
					);

					$field['options']['value'] = absint( $value );

					self::$slider[ $field['id'] ] = $field['options'];

					wp_enqueue_script('jquery-ui-slider');
					add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'sliderJS' ) );

					break;

				case 'quicktag':

					// For text areas we want to render the description before the field.
					// Lets render it and unset it so it does not render twice.
					if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

						printf( '<div class="description"> %1$s</div>',
							esc_html( $field['desc'] )
						);

						unset( $field['desc'] );
					}

					echo '<div class="wp-editor-container">';

					printf( '<textarea class="wp-editor-area" rows="20" cols="40" id="%1$s" name="%1$s">%2$s</textarea>',
						esc_attr( $field['id'] ),
						wp_kses_data( '$value ')
					);

					echo '</div>';

					self::$quickTagIDs[] = esc_attr( $field['id'] );

					add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'quickTagJS' ) );

					break;

				case 'rte':

					$size = isset( $field['size'] ) && $field['size'] != 'regular' ? $field['size'] : 'regular';

					// For text areas we want to render the description before the field.
					// Lets render it and unset it so it does not render twice.
					if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

						printf( '<div class="description"> %1$s</div>',
							esc_html( $field['desc'] )
						);

						unset( $field['desc'] );
					}

					if ( $wp_version >= 3.3 && function_exists('wp_editor') ) {

						// Set the rte defaults.
						$defaults = array(
							'textarea_name' => sprintf( '%1$s' , $field['id'] ),
						);

						$atts = wp_parse_args( isset( $field['options'] ) ? $field['options'] : array(), $defaults );

						wp_editor(
							wp_kses_post( $value ),
							sprintf( '%1$s' , $field['id'] ),
							$atts
						);

					} else {

						/*
						 * If this is pre WP 3.3, lets drop in the quick tag editor instead.
						 */

						echo '<div class="wp-editor-container">';

						printf( '<textarea class="wp-editor-area" rows="20" cols="40" id="%1$s" name="%1$s">%2$s</textarea>',
							esc_attr( $field['id'] ),
							wp_kses_data( '$value ')
						);

						echo '</div>';

						self::$quickTagIDs[] = esc_attr( $field['id'] );

						add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'quickTagJS' ) );
					}

					break;

				case 'repeatable':

					echo '<table id="' . esc_attr( $field['id'] ) . '-repeatable" class="meta_box_repeatable" cellspacing="0">';
						echo '<tbody>';

							$i = 0;

							// create an empty array
							if ( $meta == '' || $meta == array() ) {

								$keys = wp_list_pluck( $field['repeatable'], 'id' );
								$meta = array ( array_fill_keys( $keys, NULL ) );
							}

							$meta = array_values( $meta );

							foreach( $meta as $row ) {

								echo '<tr>
										<td><span class="sort hndle"></span></td><td>';

								// foreach ( $field['repeatable'] as $repeatable ) {

								// 	if ( ! array_key_exists( $repeatable['id'], $meta[ $field['id'] ] ) ) $meta[ $field['id'] ][ $repeatable['id'] ] = NULL;

								// 	echo '<label>' . $repeatable['label']  . '</label><p>';
								// 	self::fields( $repeatable, $meta[ $i ][ $repeatable['id'] ], array( $field['id'], $i ) );
								// 	echo '</p>';
								// }

								self::fields( $field['repeatable'] );

								echo '</td><td><a class="meta_box_repeatable_remove" href="#"></a></td></tr>';

								$i++;

							} // end each row

						echo '</tbody>';
						echo '
							<tfoot>
								<tr>
									<th colspan="4"><a class="meta_box_repeatable_add" href="#"></a></th>
								</tr>
							</tfoot>';
					echo '</table>';

					break;

				default:

					// do_action('cn_metabox_render_' . $field['type'] , $field, $meta );

					break;
			}

			if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

				printf( '<span class="description"> %1$s</span>',
					esc_html( $field['desc'] )
				);
			}


			echo empty( $field['after'] ) ? '' : $field['after'];

			echo '</td>' , '</tr>';
		}

		// echo '</tbody></table>';

		// do_action( 'cn_metabox_table_after', $entry, $meta, $this->metabox );
	}

	/**
	 * Outputs the JS necessary to support the quicktag textareas.
	 *
	 * @access private
	 * @since 0.8
	 * @return void
	 */
	public static function quickTagJS() {
		echo '<script type="text/javascript">/* <![CDATA[ */';

		foreach ( self::$quickTagIDs as $id ) echo 'quicktags("' . $id . '");';

	    echo '/* ]]> */</script>';
	}

	/**
	 * Outputs the JS necessary to support the datepicker.
	 *
	 * @access private
	 * @since 0.8
	 * @return void
	 */
	public static function datepickerJS() {

?>

<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the jQuery UI Datepicker to the date input fields.
 */
;jQuery(document).ready( function($){

	if ($.fn.datepicker) {

		$('.cn-datepicker').live('focus', function() {
			$(this).datepicker({
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				yearRange: 'c-100:c+10'
			});
		});
	};
});
/* ]]> */</script>

<?php

	}

	/**
	 * Outputs the JS necessary to support the sliders.
	 *
	 * @access private
	 * @since 0.8
	 * @return void
	 */
	public static function sliderJS() {

?>

<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the jQuery UI Datepicker to the date input fields.
 */
;jQuery(document).ready( function($){

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
	$id,
	$option['value'],
	$option['min'],
	$option['max'],
	$option['step']
	);

}
?>
});
/* ]]> */</script>

<?php

	}

}
