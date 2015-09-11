<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class registering the metaboxes for add/edit an entry.
 *
 * @package     Connections
 * @subpackage  Metabox API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 *
 * @see cnMetaboxAPI::add()
 * @see cnMetabox_Render::fields()
 * <code>
 * add_action( 'cn_metabox', 'cnCustomMetaboxFieldDemo' );
 *
 * function cnCustomMetaboxFieldDemo() {
 *
 * 	$prefix = 'cn-demo-';
 *
 * 	$metabox = array(
 * 		'id'         => 'test_metabox_one',
 * 		'title'      => 'Metabox One',
 * 		'context'    => 'normal',
 * 		'priority'   => 'core',
 * 		'sections'   => array(
 * 			array(
 * 				'name'       => 'Section One',
 * 				'desc'       => 'The custom metabox / field API supports adding multiple sections to a metabox.',
 * 				'fields'     => array(
 * 					array(
 * 						'name'       => 'Test Text - SMALL',
 * 						'show_label' => TRUE, // Show field label
 * 						'desc'       => 'field description',
 * 						'id'         => $prefix . 'test_text_small',
 * 						'type'       => 'text',
 * 						'size'       => 'small',
 * 					),
 * 					array(
 * 						'name'       => 'Test Text - REGULAR',
 * 						'show_label' => FALSE, // Show field label
 * 						'desc'       => 'field description',
 * 						'id'         => $prefix . 'test_text_regular',
 * 						'type'       => 'text',
 * 						'size'       => 'regular',
 * 					),
 * 				),
 * 			),
 * 			array(
 * 				'name' => 'Section Two',
 * 				'desc'       => 'The custom metabox / field API supports text input fields with multiple sizes that match WordPress core.',
 * 				'fields' => array(
 * 					array(
 * 						'name'       => 'Checkbox',
 * 						'show_label' => TRUE, // Show field label
 * 						'desc'       => 'field description',
 * 						'id'         => 'checkbox_test',
 * 						'type'       => 'checkbox',
 * 					),
 * 					array(
 * 						'name'       => 'Checkbox Group',
 * 						'show_label' => TRUE, // Show field label
 * 						'desc'       => 'field description',
 * 						'id'         => 'checkboxgroup_test',
 * 						'type'       => 'checkboxgroup',
 * 						'options'    => array(
 * 								'option_one'   => 'Option One',
 * 								'option_two'   => 'Option Two',
 * 								'option_three' => 'Option Three',
 * 							),
 * 					),
 * 				),
 * 			),
 * 		),
 * 	);
 *
 * 	cnMetaboxAPI::add( $metabox );
 *
 * }
 * </code>
 *
 */

class cnMetaboxAPI {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.8
	 * @var object
	*/
	private static $instance;

	/**
	 * The metaboxes.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
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
	 * Setup the class, if it has already been initialized, return the initialized instance.
	 *
	 * @access public
	 * @since 0.8
	 * @see cnMetabox()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			add_action( 'init', array( __CLASS__, 'process' ), 0 );

		}
	}

	/**
	 * Return an instance of the class.
	 *
	 * @access public
	 * @since 0.8
	 *
	 * @return object cnMetabox
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Public method to add metaboxes.
	 *
	 * Accepted option for the $atts property are:
	 * 	id (string) The metabox ID. This value MUST be unique.
	 * 	title (string) The metabox title that is presented.
	 * 	callback (mixed) string | array [optional] The function name or class method to be used for custom metabox output.
	 * 	page_hook (string) string The admin page hooks the metabox is to be rendered on.
	 * 	context (string) [optional] The part of the admin page the metabox should be rendered. Valid options: 'normal', 'advanced', or 'side'. NOTE: note used on the frontend.
	 * 	priority (string) [optional] The priority within the context the metabox should be rendered. Valid options: 'high', 'core', 'default' or 'low'. NOTE: note used on the frontend.
	 * 	section (array) [optional] An array of sections and its fields to be to be rendered. NOTE: If sections are not required, use the fields option.
	 * 		name (string) The section name that is presented.
	 * 		desc (string) The description of the section that is presented.
	 * 		fields (array) The fields to be rendered. NOTE: Valid field options, @see cnMetabox_Render::fields().
	 * 	fields (array) The fields to be rendered. NOTE: Valid field options, @see cnMetabox_Render::fields().
	 *
	 * @access public
	 * @since 0.8
	 * @param array $metabox
	 *
	 * return void
	 */
	public static function add( array $metabox ) {

		/*
		 * Interestingly if either 'submitdiv' or 'linksubmitdiv' is used as
		 * the 'id' in the add_meta_box function it will show up as a metabox
		 * that can not be hidden when the Screen Options tab is output via the
		 * meta_box_prefs function.
		 */

		if ( is_admin() ) {

			$pageHooks = apply_filters( 'cn_admin_default_metabox_page_hooks', array( 'connections_page_connections_add', 'connections_page_connections_manage' ) );

			// Define the core pages and use them by default if no page hooks were defined.
			// Check DOING_AJAX because the page hooks are not defined when doing an AJAX request which causes undefined property errors.
			$pages = defined('DOING_AJAX') && DOING_AJAX ? array() : $pageHooks;

			$metabox['pages'] = empty( $metabox['pages'] ) ? $pages : $metabox['pages'];

		} else {

			$metabox['pages'] = 'public';
			$metabox['args']  = $metabox;
		}

		$metabox['context']  = empty( $metabox['context'] ) ? 'normal' : $metabox['context'];
		$metabox['priority'] = empty( $metabox['priority'] ) ? 'default' : $metabox['priority'];

		// Use the core metabox API to render the metabox unless the metabox was registered with a custom callback to be used to render the metabox.
		$metabox['callback'] = isset( $metabox['callback'] ) && ! empty( $metabox['callback'] ) ? $metabox['callback'] : array( new cnMetabox_Render(), 'render' );

		self::$metaboxes[ $metabox['id'] ] = $metabox;
	}

	/**
	 * Return self::$metaboxes array.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param null $id
	 *
	 * @return array
	 */
	public static function get( $id = NULL ) {

		if ( is_null( $id ) ) return self::$metaboxes;

		return isset( self::$metaboxes[ $id ] ) ? self::$metaboxes[ $id ] : array();
	}

	/**
	 * Remove a registered metabox.
	 *
	 * @access public
	 * @since 0.8
	 * @param  string $id The metabox id to remove.
	 *
	 * @return bool
	 */
	public static function remove( $id ) {

		if ( isset( self::$metaboxes[ $id ] ) ) {

			unset( self::$metaboxes[ $id ] );
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Method responsible for processing the registered metaboxes.
	 * This is a private method that is ran on the `admin_init` action
	 * if is_admin() or the `init` if not is_admin().
	 *
	 * Extensions should hook into the `cn_metabox` action to register
	 * their metaboxes.
	 *
	 * @access private
	 * @since 0.8
	 * @uses add_action()
	 *
	 * @return void
	 */
	public static function process() {

		// Action for extensions to hook into to add custom metaboxes/fields.
		do_action( 'cn_metabox', self::$instance );

		// Store the registered metaboxes in the options table that way the data can be used
		// even if this API is not loaded; for example in the frontend to check if a field ID
		// is private so it will not be rendered.
		//
		// NOTE: All fields registered via this API are considered private.
		// The expectation is an action will be called to render the metadata.
		// Do not update table when doing an AJAX request.
		// if ( ! defined('DOING_AJAX') /*&& ! DOING_AJAX*/ ) update_option( 'connections_metaboxes', self::$metaboxes );

		// Process the metaboxes added via the `cn_metabox` action.
		foreach ( self::$metaboxes as $id => $metabox ) {

			if ( is_admin() ) {

				foreach ( $metabox['pages'] as $page ){

					// Add the actions to show the metaboxes on the registered pages.
					add_action( 'load-' . $page, array( __CLASS__, 'register' ) );
				}

			} else {

				// Add the metabox so it can be used on the site frontend.
				cnMetabox_Render::add( 'public', $metabox );
			}

			// Add action to save the field metadata.
			add_action( 'cn_process_meta-entry', array( new cnMetabox_Process( $metabox ), 'process' ), 10, 2 );

		}

	}

	/**
	 * Register the metaboxes.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @global string $hook_suffix The current admin page hook.
	 *
	 * @return void
	 */
	public static function register() {
		global $hook_suffix;

		// Grab an instance of the Connections object.
		$instance  = Connections_Directory();

		// The metaboxes only need to be added on the manage page if performing an action to an entry.
		// This is to prevent the metaboxes from showing on the Screen Option tab on the Manage
		// admin page when viewing the manage entries table.
		if ( $hook_suffix == $instance->pageHook->manage && ! isset( $_GET['cn-action'] ) ) return;

		foreach ( self::$metaboxes as $metabox ) {

			if ( in_array( $hook_suffix, $metabox['pages'] ) ) cnMetabox_Render::add( $hook_suffix, $metabox );
		}
	}

	/**
	 * All registered fields thru this class are considered to be private.
	 * This filter checks the supplied `key` against all registered fields
	 * and return a bool indicating whether or not the `$key` is private.
	 *
	 * @access private
	 * @param  bool    $private Passed by the `cn_is_private_meta` filter.
	 * @param  string  $key     The key name.
	 * @param  string  $type    The object type.
	 *
	 * @return boolean
	 */
	public static function isPrivate( $private, $key, $type ) {

		foreach ( self::$metaboxes as $metabox ) {

			if ( isset( $metabox['fields'] ) ) {

				foreach ( $metabox['fields'] as $field ) {

					if ( $field['id'] == $key ) return TRUE;
				}
			}

			if ( isset( $metabox['sections'] ) ) {

				foreach ( $metabox['sections'] as $section ) {

					foreach ( $section['fields'] as $field ) {

						if ( $field['id'] == $key ) return TRUE;
					}
				}
			}
		}

		return FALSE;
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
	 * The metaboxes that were registered to render.
	 *
	 * NOTE: This array will only be used to render
	 * the metaboxes on the frontend.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @var array
	 */
	private static $metaboxes = array();

	/**
	 * The array containing the registered metabox attributes.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @var array
	 */
	private $metabox = array();

	/**
	 * The array containing the current metabox sections.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @var array
	 */
	// private $sections = array();

	/**
	 * The object being worked with.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @var object
	 */
	private $object;

	/**
	 * The meta data for a cnEntry object.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @var array
	 */
	private $meta = array();

	/**
	 * The array of all registerd quicktag textareas.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @var array
	 */
	private static $quickTagIDs = array();

	/**
	 * The array of all registerd slider settings.
	 *
	 * @access private
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
	 * @access public
	 * @since 0.8
	 * @uses add_meta_box()
	 * @param string $pageHook The page hood / post type in which to add the metabox.
	 * @param array  $metabox  The array of metaboxes to add. NOTE: Valid field options, @see cnMetaboxAPI::add().
	 *
	 * @return void
	 */
	public static function add( $pageHook, array $metabox ) {

		// Bail if params are empty.
		if ( empty( $pageHook ) || empty( $metabox ) ) return;

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

			// self::$metaboxes[ $metabox['id'] ] = array(
			// 	'id'        => $metabox['id'],
			// 	'title'     => $metabox['title'],
			// 	'callback'  => $metabox['callback'],
			// 	'page_hook' => $pageHook,
			// 	'context'   => $metabox['context'],
			// 	'priority'  => $metabox['priority'],
			// 	'args'      => $metabox
			// 	);

			$metabox['field'] = isset( $metabox['field'] ) && ! empty( $metabox['field']  ) ? $metabox['field'] : array( 'public' );

			cnMetaboxAPI::add( $metabox );
		}

	}

	/**
	 * Use to render the registered metaboxes on the frontend.
	 * NOTE: To render the metaboxes on an admin page use do_meta_boxes().
	 *
	 * Accepted option for the $atts property are:
	 * 	id (array) The metabox ID to render.
	 * 	order (array) An indexed array of metabox IDs that should be rendered in the order in the array.
	 * 		NOTE: Any registered metabox ID not supplied in `order` means `exclude` is implied.
	 * 	exclude (array) An indexed array of metabox IDs that should be excluded from being rendered.
	 * 	include (array) An indexed array of metabox IDs that should be rendered.
	 * 		NOTE: Metabox IDs in `exclude` outweigh metabox IDs in include. Meaning if the same metabox ID
	 * 		exists in both, the metabox will be excluded.
	 *
	 * @access public
	 * @since 0.8
	 * @param  array  $atts   The attributes array.
	 * @param  object $object An instance the the cnEntry object.
	 *
	 * @return string         The HTML output of the registered metaboxes.
	 */
	public static function metaboxes( array $atts = array(), $object ) {

		$metaboxes = array();

		$defaults  = array(
			'id'      => '',
			'order'   => array(),
			'exclude' => array(),
			'include' => array(),
			'hide'    => array(),
			);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! empty( $atts['id'] ) ) {

			$metaboxes[ $atts['id'] ] = cnMetaboxAPI::get( $atts['id'] );

		} else if ( ! empty( $atts['order'] ) ) {

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
			// be a saved metabox in the settings that no longer exists. Lets catch this and continue.
			if ( empty( $metabox ) ) continue;

			// Exclude/Include the metaboxes that have been requested to exclude/include.
			if ( ! empty( $atts['exclude'] ) ) {

				if ( in_array( $id, $atts['exclude'] ) && ! in_array( $id, $atts['hide'] ) ) continue;

			} else {

				if ( ! empty( $atts['include'] ) ) {

					if ( ! in_array( $id, $atts['include'] ) && ! in_array( $id, $atts['hide'] ) ) continue;
				}
			}

			$display = in_array( $id, $atts['hide'] ) ? 'none' : 'block';

			echo '<div id="cn-metabox-' . $metabox['id'] . '" class="cn-metabox" style="display: ' . $display . '">';
				echo '<h3 class="cn-metabox-title">' . $metabox['title'] . '</h3>';
				echo '<div class="cn-metabox-inside">';

					if ( is_callable( $metabox['callback'] ) ) {

						call_user_func( $metabox['callback'], $object, $metabox );

					} else {

						if ( is_string( $metabox['callback'] ) ) {

							$callback = $metabox['callback'];

						} else if ( is_array( $metabox['callback'] ) ) {

							if ( is_object( $metabox['callback'][0] ) ) {

								$callback = get_class( $metabox['callback'][0] ) . '::' . $metabox['callback'][1];

							} else {

								$callback = implode( '::', $metabox['callback'] );
							}

						}

						echo '<p>' , __( sprintf( 'Invalid callback: %s', $callback ), 'connections' ) , '</p>';
					}

				echo '<div class="cn-clear"></div>';
				echo '</div>';
			echo '</div>';
		}

	}

	/**
	 * Render the metabox.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @param $object
	 * @param $metabox
	 */
	public function render( $object, $metabox ) {

		$this->object  = $object;
		$this->metabox = $metabox['args'];
		$sections      = isset( $metabox['args']['sections'] ) && ! empty( $metabox['args']['sections'] ) ? $metabox['args']['sections'] : array();
		$fields        = isset( $metabox['args']['fields'] )   && ! empty( $metabox['args']['fields'] )   ? $metabox['args']['fields'] : array();

		// Use nonce for verification
		echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';

		// If metabox sections have been registered, loop thru them.
		if ( ! empty( $sections ) ) {

			foreach ( $sections as $section ) {

				$this->section( $section );
			}
		}

		// If metabox fields have been supplied, loop thru them.
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
	 * @access private
	 * @since 0.8
	 * @param  array $section An array containing the sections of the metabox.
	 *
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
	 * 	id (string) The field ID. This value MUST be unique.
	 * 	desc (string) [optional] The field description.
	 * 	type (string) The type of field which should be registered. This can be any of the supported field types or a custom field type.
	 * 		Core supported field types are:
	 * 			checkbox
	 * 			checkboxgroup
	 * 			radio
	 * 			radio_inline
	 * 			select
	 * 			text (input)
	 * 			textarea
	 * 			datepicker
	 * 			slider
	 * 			quicktag
	 * 			rte
	 * 	value (mixed) string | array [optional] The function name or class method to be used retrieve a value for the field.
	 * 	size (string) [optional] The size if the text input and textarea field types.
	 * 		NOTE: Only used for the `text` field type. Valid options: small', 'regular' or 'large'
	 * 		NOTE: Only used for the `textarea` field type. Valid options: small' or 'large'
	 * 	options (mixed) string | array [optional] Valid value depends on the field type being rendered.
	 * 		Field type / valid value for options
	 * 			checkboxgroup (array) An associative array where the key is the checkbox value and the value is the checkbox label.
	 * 			radio / radio_inline (array) An associative array where the key is the radio value and the value is the radio label.
	 * 			select (array) An associative array where the key is the option value and the value is the option name.
	 * 			rte (array) @link http://codex.wordpress.org/Function_Reference/wp_editor#Arguments
	 * 			slider (array) The slider options.
	 * 				min (int) The minimum slider step.
	 * 				max (int) The maximum slider step.
	 * 				step (int) The step the slider steps at.
	 * 	default	(mixed) The default value to be used.
	 *
	 * @access private
	 * @since 0.8
	 * @global $wp_version
	 * @param $fields	array 	An indexed array of fields to render..
	 *
	 * @return string
	 */
	private function fields( $fields ) {
		global $wp_version;

		// do_action( 'cn_metabox_table_before', $entry, $meta, $this->metabox );

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

				$value = $this->object->getMeta( array( 'key' => $field['id'], 'single' => TRUE ) );
			}

			if ( empty( $value ) ) $value = isset( $field['default'] ) ? $field['default'] : '';

			/**
			 * Apply custom classes to a metabox table.
			 *
			 * @since 8.3.4
			 *
			 * @param array  $class An indexed array of classes that should applied to the table element.
			 * @param string $type  The field type.
			 * @param string $id    The field id.
			 */
			$class = apply_filters( 'cn_metabox_table_class', array( 'cn-metabox-type-' . $field['type'] ), $field['type'], $field['id'] );

			/**
			 * Apply a custom id to a metabox table.
			 *
			 * @since 8.3.4
			 *
			 * @param string $id The field id.
			 */
			$id    = apply_filters( 'cn_metabox_table_id', 'cn-metabox-id-' . $field['id'] );

			/**
			 * Apply custom classes to a metabox table.
			 *
			 * @since 8.3.4
			 *
			 * @param array  $style An associative array of inline style attributes where the array key is the property and the array value is the property value.
			 * @param string $type  The field type.
			 * @param string $id    The field id.
			 */
			$style = apply_filters( 'cn_metabox_table_style', array(), $field['type'], $field['id'] );

			$class = cnHTML::attribute( 'class', $class );
			$id    = cnHTML::attribute( 'id', $id );
			$style = cnHTML::attribute( 'style', $style );

			echo '<tr' . $class . $id . $style . '>';

			// For a label to be rendered the $field['name'] has to be supplied.
			// Show the label if $field['show_label'] is TRUE, OR, if it is not supplied assume TRUE and show it anyway.
			// The result will be the label will be shown unless specifically $field['show_label'] is set to FALSE.
			if ( ( isset( $field['name'] ) && ! empty( $field['name'] ) ) && ( ! isset( $field['show_label'] ) || $field['show_label'] == TRUE ) ) {

				echo '<th class="cn-metabox-label">' . esc_html( $field['name'] ) . '</th>';

			} elseif ( ( isset( $field['name'] ) && ! empty( $field['name'] ) ) && ( isset( $field['show_label'] ) && $field['show_label'] == TRUE ) ) {

				echo '<th class="cn-metabox-label">' . esc_html( $field['name'] ) . '</th>';

			} elseif ( ! isset( $field['show_label'] ) || $field['show_label'] == FALSE ) {

				echo '<th class="cn-metabox-label-empty">&nbsp;</th>';
			}

			echo '<td>';

			echo empty( $field['before'] ) ? '' : $field['before'];

			/**
			 * Apply custom classes to the field type container element.
			 *
			 * NOTE: The dynamic portion of the hook is the field type.
			 *
			 * @since 8.3.4
			 *
			 * @param array  $class An indexed array of classes that should applied to the element.
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
			$id    = apply_filters( "cn_metabox_{$field['type']}_id", '' );

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

			$class = cnHTML::attribute( 'class', $class );
			$id    = cnHTML::attribute( 'id', $id );
			$style = cnHTML::attribute( 'style', $style );

			/**
			 * Chance to manipulate the field value before rendering the field.
			 *
			 * NOTE: The dynamic portion of the hook is the field type.
			 *
			 * @since 0.8
			 *
			 * @param mixed $value The field value.
			 * @param array $field The field attributes array.
			 */
			$value = apply_filters( "cn_meta_field_value-{$field['type']}", $value, $field );

			switch ( $field['type'] ) {

				case 'checkbox':

					cnHTML::field(
						array(
							'type'    => 'checkbox',
							'prefix'  => '',
							'class'   => 'cn-checkbox',
							'id'      => $field['id'],
							'name'    => $field['id'],
							'label'   => $field['desc'],
							'before'  => '<div' . $class . $id . $style . '>',
							'after'   => '</div>',
						),
						$value
					);

					break;

				case 'checkboxgroup':
				case 'checkbox-group':

				self::fieldDescription( $field['desc'] );

					cnHTML::field(
						array(
							'type'    => 'checkbox-group',
							'prefix'  => '',
							'class'   => 'cn-checkbox',
							'id'      => $field['id'],
							'name'    => $field['id'] . '[]',
							'display' => 'block',
							'options' => $field['options'],
							'before'  => '<div' . $class . $id . $style . '>',
							'after'   => '</div>',
						),
						$value
					);

					break;

				case 'radio':

					self::fieldDescription( $field['desc'] );

					cnHTML::field(
						array(
							'type'    => 'radio',
							'prefix'  => '',
							'class'   => 'cn-radio-option',
							'id'      => $field['id'],
							'name'    => $field['id'] . '[]',
							'display' => 'block',
							'options' => $field['options'],
							'before'  => '<div' . $class . $id . $style . '>',
							'after'   => '</div>',
						),
						$value
					);

					break;

				case 'radio_inline':
				case 'radio-inline':

					self::fieldDescription( $field['desc'] );

					cnHTML::field(
						array(
							'type'    => 'radio',
							'prefix'  => '',
							'class'   => 'cn-radio-option',
							'id'      => $field['id'],
							'name'    => $field['id'] . '[]',
							'display' => 'inline',
							'options' => $field['options'],
							'before'  => '<div' . $class . $id . $style . '>',
							'after'   => '</div>',
						),
						$value
					);

					break;

				case 'select':

					cnHTML::field(
						array(
							'type'    => 'select',
							'prefix'  => '',
							'class'   => 'cn-select',
							'id'      => $field['id'],
							'name'    => $field['id'],
							'display' => 'inline',
							'options' => $field['options'],
							'before'  => '<div' . $class . $id . $style . '>',
							'after'   => '</div>',
						),
						$value
					);

					self::fieldDescription( $field['desc'] );

					break;

				case 'text':

					$sizes = array( 'small', 'regular', 'large' );

					cnHTML::field(
						array(
							'type'    => 'text',
							'prefix'  => '',
							'class'   => isset( $field['size'] ) && ! empty( $field['size'] ) && in_array( $field['size'], $sizes ) ? esc_attr( $field['size'] ) . '-text' : 'large-text',
							'id'      => $field['id'],
							'name'    => $field['id'],
							'before'  => '<div' . $class . $id . $style . '>',
							'after'   => '</div>',
						),
						$value
					);

					self::fieldDescription( $field['desc'] );

					break;

				case 'textarea':

					$sizes = array( 'small', 'large' );

					self::fieldDescription( $field['desc'] );

					cnHTML::field(
						array(
							'type'    => 'textarea',
							'prefix'  => '',
							'class'   => isset( $field['size'] ) && ! empty( $field['size'] ) && in_array( $field['size'], $sizes ) ? esc_attr( $field['size'] ) . '-text' : 'small-text',
							'id'      => $field['id'],
							'name'    => $field['id'],
							'rows'    => 10,
							'cols'    => 50,
							'before'  => '<div' . $class . $id . $style . '>',
							'after'   => '</div>',
						),
						$value
					);

					break;

				case 'datepicker':

					printf( '<input type="text" class="cn-datepicker" id="%1$s" name="%1$s" value="%2$s"/>',
						esc_attr( $field['id'] ),
						! empty( $value ) ? date( 'm/d/Y', strtotime( $value ) ) : ''
					);

					wp_enqueue_script('jquery-ui-datepicker');
					wp_enqueue_style( 'cn-admin-jquery-datepicker' );
					add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'datepickerJS' ) );
					add_action( 'wp_footer' , array( __CLASS__ , 'datepickerJS' ) );

					self::fieldDescription( $field['desc'] );

					break;

				case 'colorpicker':

					self::fieldDescription( $field['desc'] );

					printf('<input type="text" class="cn-colorpicker" id="%1$s" name="%1$s" value="%2$s"/>',
						esc_attr( $field['id'] ),
						esc_attr( $value )
					);

					wp_enqueue_style('wp-color-picker');

					if ( is_admin() ) {

						wp_enqueue_script('wp-color-picker');
						add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'colorpickerJS' ) );

					} else {

						/*
						 * WordPress seems to only register the color picker scripts for use in the admin.
						 * So, for the frontend, we must manually register and then enqueue.
						 * @url http://wordpress.stackexchange.com/a/82722/59053
						 */

						wp_enqueue_script(
							'iris',
							admin_url( 'js/iris.min.js' ),
							array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
							FALSE,
							1
						);

						wp_enqueue_script(
							'wp-color-picker',
							admin_url( 'js/color-picker.min.js' ),
							array( 'iris' ),
							FALSE,
							1
						);

						$colorpicker_l10n = array(
							'clear'         => __( 'Clear', 'connections' ),
							'defaultString' => __( 'Default', 'connections' ),
							'pick'          => __( 'Select Color', 'connections' ),
							'current'       => __( 'Current Color', 'connections' ),
						);

						wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );

						add_action( 'wp_footer' , array( __CLASS__ , 'colorpickerJS' ) );
					}

					break;

				case 'slider':

					// Set the slider defaults.
					$defaults = array(
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
						'value' => 0
					);

					$field['options'] = wp_parse_args( isset( $field['options'] ) ? $field['options'] : array(), $defaults );

					printf( '<div class="cn-slider-container" id="cn-slider-%1$s"></div><input type="text" class="small-text" id="%1$s" name="%1$s" value="%2$s"/>',
						esc_attr( $field['id'] ),
						absint( $value )
					);

					self::fieldDescription( $field['desc'] );

					$field['options']['value'] = absint( $value );

					self::$slider[ $field['id'] ] = $field['options'];

					wp_enqueue_script('jquery-ui-slider');
					add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'sliderJS' ) );
					add_action( 'wp_footer' , array( __CLASS__ , 'sliderJS' ) );

					break;

				case 'quicktag':

					self::fieldDescription( $field['desc'] );

					echo '<div class="wp-editor-container">';

					printf( '<textarea class="wp-editor-area" rows="20" cols="40" id="%1$s" name="%1$s">%2$s</textarea>',
						esc_attr( $field['id'] ),
						wp_kses_data( $value )
					);

					echo '</div>';

					self::$quickTagIDs[] = esc_attr( $field['id'] );

					wp_enqueue_script('jquery');
					add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'quickTagJS' ) );
					add_action( 'wp_print_footer_scripts' , array( __CLASS__ , 'quickTagJS' ) );

					break;

				case 'rte':

					self::fieldDescription( $field['desc'] );

					if ( $wp_version >= 3.3 && function_exists('wp_editor') ) {

						// Set the rte defaults.
						$defaults = array(
							'textarea_name' => sprintf( '%1$s' , $field['id'] ),
						);

						$atts = wp_parse_args( isset( $field['options'] ) ? $field['options'] : array(), $defaults );

						wp_editor(
							cnSanitize::html( $value ),
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
							cnSanitize::quicktag( $value )
						);

						echo '</div>';

						self::$quickTagIDs[] = esc_attr( $field['id'] );

						wp_enqueue_script('jquery');
						add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'quickTagJS' ) );
						add_action( 'wp_print_footer_scripts' , array( __CLASS__ , 'quickTagJS' ) );
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

							foreach ( $meta as $row ) {

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

					do_action( 'cn_meta_field-' . $field['type'], $field, $value, $this->object );

					break;
			}

			echo empty( $field['after'] ) ? '' : $field['after'];

			echo '</td>' , '</tr>';
		}

		// do_action( 'cn_metabox_table_after', $entry, $meta, $this->metabox );
	}

	/**
	 * Print the field description.
	 *
	 * @access private
	 * @since  8.3.4
	 *
	 * @param string $desc
	 */
	private static function fieldDescription( $desc ) {

		if ( ! empty( $desc ) ) {

			printf( '<p class="description"> %1$s</p>', esc_html( $desc ) );
		}
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

		$('.postbox, .cn-metabox').on( 'focus', '.cn-datepicker', function(e) {

			$(this).datepicker({
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				yearRange: 'c-100:c+10'
			});

			e.preventDefault();
		});
	};
});
/* ]]> */</script>

<?php

	}

	/**
	 * Outputs the JS necessary to support the color picker.
	 *
	 * @access private
	 * @since 0.8
	 * @return void
	 */
	public static function colorpickerJS() {

?>

<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the Color Picker to the input fields.
 */
;jQuery(document).ready( function($){

	$('.cn-colorpicker').wpColorPicker();
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
 * Add the jQuery UI Slider input fields.
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

/**
 * Class for sanitizing and saving the user input from registered metaboxes.
 *
 * NOTE: This is a private class and should not be accessed directly.
 *
 * @package     Connections
 * @subpackage  Metabox Processing
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

class cnMetabox_Process {

	/**
	 * The array containing the registered metaboxes.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private $metabox = array();

	public function __construct( $metabox ) {

		$this->metabox = $metabox;
	}

	/**
	 * Loops thru the registered metaboxes sections and fields
	 * and save or update the meta data according to the current
	 * action being performed.
	 *
	 * @access private
	 * @since 0.8
	 * @param  string $action The action being performed.
	 * @param  int    $id     The object ID.
	 *
	 * @return void
	 */
	public function process( $action, $id ) {

		$sections = isset( $this->metabox['sections'] ) && ! empty( $this->metabox['sections'] ) ? $this->metabox['sections'] : array();
		$fields   = isset( $this->metabox['fields'] )   && ! empty( $this->metabox['fields'] )   ? $this->metabox['fields'] : array();

		// If metabox sections have been registered, loop thru them.
		if ( ! empty( $sections ) ) {

			foreach ( $sections as $section ) {

				if ( ! empty( $section['fields'] ) ) $this->save( $action, $id, $section['fields'] );
			}
		}

		// If metabox fields have been supplied, loop thru them.
		if ( ! empty( $fields ) ) {

				$this->save( $action, $id, $fields );
		}
	}

	/**
	 * Save and or update the objects meta data
	 * based on the action being performed to the object.
	 *
	 * @access private
	 * @since 0.8
	 * @param  string $action The action being performed.
	 * @param  int    $id     The object ID.
	 * @param  array  $fields An array of the registered fields to save and or update.
	 *
	 * @return void
	 */
	private function save( $action, $id, $fields ) {

		foreach ( $fields as $field ) {

			if ( ! $id = absint( $id ) ) return FALSE;

			// Quick and dirty hack to prevent the bio and notes fields from being saved in the meta table.
			// @todo Think of something better to do here.
			// There should be some type of flag to check before saving as meta.
			if ( $field['id'] === 'bio' || $field['id'] === 'notes' ) continue;

			$value = $this->sanitize(
				$field['type'],
				isset( $_POST[ $field['id'] ] ) ? $_POST[ $field['id'] ] : NULL,
				isset( $field['options'] ) ? $field['options'] : array(),
				isset( $field['default'] ) ? $field['default'] : NULL
			);

			switch ( $action ) {

				case 'add':

					cnMeta::add( 'entry', $id, $field['id'], $value );

					break;

				case 'copy':

					cnMeta::add( 'entry', $id, $field['id'], $value );

					break;

				case 'update':

					cnMeta::update( 'entry', $id, $field['id'], $value );

					break;
			}
		}
	}

	/**
	 * Sanitize use input based in field type.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @return mixed
	 */
	public function sanitize( $type, $value, $options = array(), $default = NULL ) {

		switch ( $type ) {

			case 'checkbox':

				$value = cnSanitize::checkbox( $value );
				break;

			case 'checkboxgroup':

				$value = cnSanitize::options( $value, $options, $default );
				break;

			case 'radio':

				$value = cnSanitize::option( $value, $options, $default );
				break;

			case 'radio_inline':

				$value = cnSanitize::option( $value, $options, $default );
				break;

			case 'select':

				$value = cnSanitize::option( $value, $options, $default );
				break;

			case 'text':

				$value = cnSanitize::string( 'text', $value );
				break;

			case 'textarea':

				$value = cnSanitize::string( 'textarea', $value );
				break;

			case 'slider':

				$value = absint( $value );
				break;

			case 'colorpicker':

				$value = cnSanitize::string( 'color', $value );
				break;

			case 'quicktag':

				$value = cnSanitize::string( 'quicktag', $value );
				break;

			case 'rte':

				$value = cnSanitize::string( 'html', $value );
				break;

			default:

				$value = apply_filters( 'cn_meta_sanitize_field-' . $type, $value, $options, $default );
				break;
		}

		return $value;
	}
}

// cnMetaboxAPI has to load before cnAdminFunction otherwise the action to save the meta is not added in time to run.
cnMetaboxAPI::init();
