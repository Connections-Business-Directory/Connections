<?php

use Connections_Directory\Metabox;
use Connections_Directory\Utility\_array;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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
 *     $prefix = 'cn-demo-';
 *
 *     $metabox = array(
 *         'id'         => 'test_metabox_one',
 *         'title'      => 'Metabox One',
 *         'context'    => 'normal',
 *         'priority'   => 'core',
 *         'sections'   => array(
 *             array(
 *                 'name'       => 'Section One',
 *                 'desc'       => 'The custom metabox / field API supports adding multiple sections to a metabox.',
 *                 'fields'     => array(
 *                     array(
 *                         'name'       => 'Test Text - SMALL',
 *                         'show_label' => TRUE, // Show field label
 *                         'desc'       => 'field description',
 *                         'id'         => $prefix . 'test_text_small',
 *                         'type'       => 'text',
 *                         'size'       => 'small',
 *                     ),
 *                     array(
 *                         'name'       => 'Test Text - REGULAR',
 *                         'show_label' => FALSE, // Show field label
 *                         'desc'       => 'field description',
 *                         'id'         => $prefix . 'test_text_regular',
 *                         'type'       => 'text',
 *                         'size'       => 'regular',
 *                     ),
 *                 ),
 *             ),
 *             array(
 *                 'name' => 'Section Two',
 *                 'desc'       => 'The custom metabox / field API supports text input fields with multiple sizes that match WordPress core.',
 *                 'fields' => array(
 *                     array(
 *                         'name'       => 'Checkbox',
 *                         'show_label' => TRUE, // Show field label
 *                         'desc'       => 'field description',
 *                         'id'         => 'checkbox_test',
 *                         'type'       => 'checkbox',
 *                     ),
 *                     array(
 *                         'name'       => 'Checkbox Group',
 *                         'show_label' => TRUE, // Show field label
 *                         'desc'       => 'field description',
 *                         'id'         => 'checkboxgroup_test',
 *                         'type'       => 'checkboxgroup',
 *                         'options'    => array(
 *                                 'option_one'   => 'Option One',
 *                                 'option_two'   => 'Option Two',
 *                                 'option_three' => 'Option Three',
 *                             ),
 *                     ),
 *                 ),
 *             ),
 *         ),
 *     );
 *
 *     cnMetaboxAPI::add( $metabox );
 *
 * }
 * </code>
 */

/**
 * Class cnMetaboxAPI
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnMetaboxAPI {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 0.8
	 * @var self
	 */
	private static $instance;

	/**
	 * The metaboxes.
	 *
	 * @since 0.8
	 * @var array
	 */
	private static $metaboxes = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 0.8
	 * @see cnMetabox::init()
	 * @see cnMetabox();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Set up the class, if it has already been initialized, return the initialized instance.
	 *
	 * @internal
	 * @since 0.8
	 * @see cnMetabox()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self();

			add_action( 'init', array( __CLASS__, 'process' ), 11 );

		}
	}

	/**
	 * Return an instance of the class.
	 *
	 * @since 0.8
	 *
	 * @return self
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Public method to add metaboxes.
	 *
	 * Accepted option for the $atts property are:
	 *     id (string) The metabox ID. This value MUST be unique.
	 *     title (string) The metabox title that is presented.
	 *     callback (mixed) string | array [optional] The function name or class method to be used for custom metabox output.
	 *     page_hook (string) string The admin page hooks the metabox is to be rendered on.
	 *     context (string) [optional] The part of the admin page the metabox should be rendered. Valid options: 'normal', 'advanced', or 'side'. NOTE: note used on the frontend.
	 *     priority (string) [optional] The priority within the context the metabox should be rendered. Valid options: 'high', 'core', 'default' or 'low'. NOTE: note used on the frontend.
	 *     section (array) [optional] An array of sections and its fields to be rendered. NOTE: If sections are not required, use the fields option.
	 *         name (string) The section name that is presented.
	 *         desc (string) The description of the section that is presented.
	 *         fields (array) The fields to be rendered. NOTE: Valid field options, @see cnMetabox_Render::fields().
	 *     fields (array) The fields to be rendered. NOTE: Valid field options, @see cnMetabox_Render::fields().
	 *
	 * @since 0.8
	 * @param array $metabox
	 */
	public static function add( array $metabox ) {

		/**
		 * Interestingly if either 'submitdiv' or 'linksubmitdiv' are used as
		 * the 'id' in the {@see add_meta_box()} function it will show up as a metabox
		 * that can not be hidden when the Screen Options tab is output via the
		 * meta_box_prefs function.
		 */

		$metabox['args']     = $metabox;
		$metabox['context']  = empty( $metabox['context'] ) ? 'normal' : $metabox['context'];
		$metabox['pages']    = _array::get( $metabox, 'pages', Metabox::getPageHooks() );
		$metabox['priority'] = empty( $metabox['priority'] ) ? 'default' : $metabox['priority'];

		// Use the core metabox API to render the metabox unless the metabox was registered with a custom callback to be used to render the metabox.
		$metabox['callback'] = isset( $metabox['callback'] ) && ! empty( $metabox['callback'] ) ? $metabox['callback'] : array( new cnMetabox_Render(), 'render' );

		self::$metaboxes[ $metabox['id'] ] = $metabox;
	}

	/**
	 * Return self::$metaboxes array.
	 *
	 * @since 0.8
	 *
	 * @param null|string $id
	 *
	 * @return array
	 */
	public static function get( $id = null ) {

		if ( is_null( $id ) ) {
			return self::$metaboxes;
		}

		return isset( self::$metaboxes[ $id ] ) ? self::$metaboxes[ $id ] : array();
	}

	/**
	 * Remove a registered metabox.
	 *
	 * @since 0.8
	 *
	 * @param string $id The metabox id to remove.
	 *
	 * @return bool
	 */
	public static function remove( $id ) {

		if ( isset( self::$metaboxes[ $id ] ) ) {

			unset( self::$metaboxes[ $id ] );
			return true;
		}

		return false;
	}

	/**
	 * Method responsible for processing the registered metaboxes.
	 * This is a private method that is run on the `admin_init` action
	 * if is_admin() or the `init` if not is_admin().
	 *
	 * Extensions should hook into the `cn_metabox` action to register
	 * their metaboxes.
	 *
	 * @internal
	 * @since 0.8
	 */
	public static function process() {

		// Action for extensions to hook into to add custom metaboxes/fields.
		do_action( 'cn_metabox', self::$instance );

		// Store the registered metaboxes in the options table that way the data can be used
		// even if this API is not loaded; for example in the frontend to check if a field ID
		// is private, so it will not be rendered.
		//
		// NOTE: All fields registered via this API are considered private.
		// The expectation is an action will be called to render the metadata.
		// Do not update table when doing an AJAX request.
		// if ( ! defined('DOING_AJAX') /*&& ! DOING_AJAX*/ ) update_option( 'connections_metaboxes', self::$metaboxes );

		// Process the metaboxes added via the `cn_metabox` action.
		foreach ( self::$metaboxes as $id => $metabox ) {

			if ( is_admin() ) {

				foreach ( $metabox['pages'] as $page ) {

					// Add the actions to show the metaboxes on the registered pages.
					add_action( 'load-' . $page, array( __CLASS__, 'register' ) );
				}

			} else {

				// Add the metabox, so it can be used on the site frontend.
				cnMetabox_Render::add( 'public', $metabox );
			}

			// Add action to save the field metadata.
			add_action( 'cn_process_meta-entry', array( new cnMetabox_Process( $metabox ), 'process' ), 10, 2 );

			cnMetaboxAPI::registerSearchCustomField( $metabox );
		}
	}

	/**
	 * Register fields for search.
	 *
	 * @internal
	 * @since 8.6.8
	 *
	 * @param array $metabox
	 */
	private static function registerSearchCustomField( $metabox ) {

		$sections = cnArray::get( $metabox, 'sections', array() );
		$fields   = cnArray::get( $metabox, 'fields', array() );

		// If metabox sections have been registered, loop through them.
		if ( ! empty( $sections ) ) {

			foreach ( $sections as $section ) {

				$fields = array_merge( cnArray::get( $section, 'fields', array() ), $fields );
			}
		}

		if ( ! empty( $fields ) ) {

			foreach ( $fields as $field ) {

				if ( in_array( $field['type'], array( 'number', 'text', 'textarea' ) )
					 && ( isset( $field['id'] ) && ! empty( $field['id'] ) )
					 && ( isset( $field['name'] ) && ! empty( $field['name'] ) ) ) {

					add_filter(
						'cn_search_field_options',
						function ( $options ) use ( $field ) {

							$options[ $field['id'] ] = maybe_unserialize( str_replace( "'", "\'", maybe_serialize( $field['name'] ) ) );
							return $options;
						}
					);
				}
			}
		}
	}

	/**
	 * Register the metaboxes.
	 *
	 * @internal
	 * @since 0.8
	 *
	 * @global string $hook_suffix The current admin page hook.
	 */
	public static function register() {
		global $hook_suffix;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// The metaboxes only need to be added on the manage page if performing an action to an entry.
		// This is to prevent the metaboxes from showing on the Screen Option tab on the Manage
		// admin page when viewing the manage entries table.
		if ( $hook_suffix == $instance->pageHook->manage && ! isset( $_GET['cn-action'] ) ) {
			return;
		}

		foreach ( self::$metaboxes as $metabox ) {

			if ( in_array( $hook_suffix, $metabox['pages'] ) ) {
				cnMetabox_Render::add( $hook_suffix, $metabox );
			}
		}
	}

	/**
	 * All registered fields through this class are considered to be private.
	 * This filter checks the supplied `key` against all registered fields
	 * and return a bool indicating whether the `$key` is private.
	 *
	 * @internal
	 * @since unknown
	 *
	 * @param bool   $private Passed by the `cn_is_private_meta` filter.
	 * @param string $key     The key name.
	 * @param string $type    The object type.
	 *
	 * @return boolean
	 */
	public static function isPrivate( $private, $key, $type ) {

		foreach ( self::$metaboxes as $metabox ) {

			if ( isset( $metabox['fields'] ) ) {

				foreach ( $metabox['fields'] as $field ) {

					if ( $field['id'] == $key ) {
						return true;
					}
				}
			}

			if ( isset( $metabox['sections'] ) ) {

				foreach ( $metabox['sections'] as $section ) {

					foreach ( $section['fields'] as $field ) {

						if ( $field['id'] == $key ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * @since 8.5.21
	 *
	 * @param string $id
	 *
	 * @return bool|string
	 */
	public static function getFieldType( $id ) {

		$type = false;

		if ( is_string( $id ) && strlen( $id ) > 0 ) {

			// Grab the registered metaboxes from the options table.
			// $metaboxes = get_option( 'connections_metaboxes', array() );
			$metaboxes = cnMetaboxAPI::get();

			// Loop through all fields registered as part of a metabox.
			foreach ( $metaboxes as $metabox ) {

				if ( isset( $metabox['fields'] ) ) {

					foreach ( $metabox['fields'] as $field ) {

						if ( $field['id'] == $id ) {

							// Field found... exit loop.
							$type = $field['type'];
							continue;
						}
					}
				}

				if ( isset( $metabox['sections'] ) ) {

					foreach ( $metabox['sections'] as $section ) {

						foreach ( $section['fields'] as $field ) {

							if ( $field['id'] == $id ) {

								// Field found... exit the loops.
								$type = $field['type'];
								continue( 2 );
							}
						}
					}
				}
			}

		}

		return $type;
	}
}
