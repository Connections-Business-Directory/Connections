<?php

namespace Connections_Directory;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Settings\Tab;
use Connections_Directory\Settings\Section;

/**
 * Settings API.
 *
 * @package Connections_Directory\Settings
 * @author  Steven A. Zahm
 * @since   8.30
 */
class Settings {

	///**
	// * Stores the registered tabs and fields.
	// *
	// * @since 8.30
	// * @var array
	// */
	//private static $registry = array(
	//	'tabs'    => array(),
	//	'fields'  => array(),
	//	'plugins' => array(),
	//);
	//
	///**
	// * Array of all WP core settings sections.
	// *
	// * @since 8.30
	// * @var array
	// */
	//private static $coreSections = array(
	//	'default',
	//	'remote_publishing',
	//	'post_via_email',
	//	'avatars',
	//	'embeds',
	//	'uploads',
	//	'optional',
	//);


	/**
	 * @since 8.30
	 * @var string
	 */
	private $pluginID;

	/**
	 * @since 8.30
	 * @var Tab[]
	 */
	private $tabs = array();

	/**
	 * @since 8.30
	 * @var Section[]
	 */
	private $sections = array();

	/**
	 * @since 8.30
	 * @var
	 */
	private $fields = array();

	///**
	// * Initiate the settings registry.
	// *
	// * NOTE: The filters for the tabs, sections and fields should be added before running init()
	// *
	// * NOTE: The recommended action to hook into is plugins_loaded. This will ensure the actions
	// *       within this class are run at the appropriate times.
	// *
	// * NOTE: The high priority is used to make sure the actions registered in this API are run
	// *       first. This is to help ensure registered settings are available to other actions registered
	// *       to the admin_init and init hooks.
	// *
	// * @since 0.7.3.0
	// */
	//public static function init() {
	//
	//	// Register the settings tabs.
	//	add_action( 'admin_init', array( __CLASS__, 'tabRegistry' ), 0 );
	//
	//	// Register the settings sections.
	//	add_action( 'admin_init', array( __CLASS__, 'sectionRegistry' ), 0 );
	//
	//	// Register the sections fields.
	//	add_action( 'admin_init', array( __CLASS__, 'addSettingsField' ), 0 );
	//	add_action( 'init', array( __CLASS__, 'fieldRegistry' ), 0 );
	//}
	//
	///**
	// * Stores the registered tabs to the registry.
	// *
	// * The array construct for registering a tab:
	// *    array(
	// *        'id'        => 'string', // ID used to identify this tab and with which to register the settings sections
	// *        'position'  => int,      // Set the position of the section. The lower the int the further left the tab will be place in the bank.
	// *        'title'     => 'string', // Title of the tab to be displayed on the admin page
	// *        'page_hook' => 'string'  // Admin page on which to add this section of options
	// *    }
	// *
	// * @since 0.7.3.0
	// */
	//public static function tabRegistry() {
	//
	//	$tabs = array();
	//
	//	$tabs = apply_filters( 'cn_register_settings_tabs', $tabs );
	//	$tabs = apply_filters( 'cn_filter_settings_tabs', $tabs );  // @todo:  At some point delete this line
	//
	//	if ( ! empty( $tabs ) ) {
	//
	//		foreach ( $tabs as $key => $tab ) {
	//
	//			self::$registry['tabs'][ $tab['page_hook'] ][] = $tab;
	//		}
	//	}
	//}
	//
	//public static function getTabs() {
	//
	//	return self::$registry['tabs'];
	//}
	//
	///**
	// * Registers the settings sections with the WordPress Settings API.
	// *
	// * The array construct for registering a settings section:
	// *    array(
	// *        'tab'       => 'string', // The tab ID in which the settings section is to be hooked to. [optional]
	// *        'id'        => 'string', // ID used to identify this section and with which to register setting fields [required]
	// *        'position'  => int,      // Set the position of the section. Lower int will place the section higher on the settings page. [optional]
	// *        'title'     => 'string', // Title to be displayed on the admin page [required]
	// *        'callback'  => 'string', // Callback used to render the description of the section [required]
	// *        'page_hook' => 'string'  // Admin page on which to add this section of options [required]
	// *    }
	// *
	// * NOTE: Use the one of the following to hook a settings section to one of the WP core settings pages.
	// *    page_hook: discussion
	// *    page_hook: general
	// *    page_hook: media
	// *    page_hook: permalink
	// *    page_hook: privacy
	// *    page_hook: reading
	// *    page_hook: writing
	// *
	// * @since 0.7.3.0
	// */
	//public static function sectionRegistry() {
	//
	//	$sections = array();
	//	$sort     = array();
	//
	//	$sections = apply_filters('cn_register_settings_sections', $sections);
	//	$sections = apply_filters('cn_filter_settings_sections', $sections);  // @todo:  At some point delete this line
	//
	//	if ( empty( $sections ) ) return;
	//
	//	foreach ( $sections as $key => $section ) {
	//
	//		// Store the position values so an array multi sort can be done to position the tab sections in the desired order.
	//		( isset( $section['position'] ) && ! empty( $section['position'] ) ) ? $sort[] = $section['position'] : $sort[] = 0;
	//	}
	//
	//	if ( ! empty( $sections ) ) {
	//
	//		array_multisort( $sort, $sections );
	//
	//		foreach ( $sections as $section ) {
	//
	//			$id = isset( $section['plugin_id'] ) && $section['plugin_id'] !== substr( $section['id'], 0, strlen( $section['plugin_id'] ) ) ? $section['plugin_id'] . '_' . $section['id'] : $section['id'];
	//
	//			if ( isset( $section['tab'] ) && ! empty( $section['tab'] ) ) $section['page_hook'] = $section['page_hook'] . '-' . $section['tab'];
	//
	//			if ( ! isset( $section['callback'] ) || empty( $section['callback'] ) ) $section['callback'] = '__return_false';
	//
	//			/*
	//			 * Reference:
	//			 * http://codex.wordpress.org/Function_Reference/add_settings_section
	//			 */
	//			add_settings_section(
	//				$id,
	//				$section['title'],
	//				$section['callback'],
	//				$section['page_hook']
	//			);
	//			//global $wp_settings_sections;print_r($wp_settings_sections);
	//		}
	//	}
	//
	//}
	//
	///**
	// * Registers the settings fields to the registered settings sections with the WordPress Settings API.
	// *
	// * The array construct for registering a settings section:
	// *    array(
	// *        'plugin_id' => 'string', // A unique ID for the plugin registering its settings. Recommend using the plugin slug.
	// *        'id'        => 'string', // ID used to identify this field. [required]
	// *                                 // *must be unique. Recommend prefix with plugin slug if not registered to a settings section.
	// *        'position'  => int,      // Set the position of the field. Lower int will place the field higher on the section. [optional]
	// *        'page_hook' => 'string', // Admin page on which to add this section of options [required]
	// *        'tab'       => 'string', // The tab ID in which the field is to be hooked to. [optional]
	// *                                 //    *required, if the field is to be shown on a specific registered tab.
	// *        'section'   => 'string', // The section in which the field is to be hooked to. [optional]
	// *                                 //    *required, if field is to be shown in a specific registered section. Recommend prefix with plugin slug.
	// *        'title'     => 'string', // The field title. [required]
	// *        'type'      => 'string', // The field type. [required] Valid values : text, textarea, checkbox, multicheckbox, radio, select, rte
	// *        'size'      => 'string,  // The field size. [optional] Valid values : small | regular | large *only used for the text field type.
	// *        'show_option_none'  => 'string'  // The string to show when no value has been chosen. [required *only for the page field type] *only used for the page field type.
	// *        'option_none_value' => 'string'  // The value to use when no value has been chosen. [required *only for the page field type] *only used for the page field type.
	// *        'desc'      => 'string',         // The field description text. [optional]
	// *        'help'      => 'string',        // The field help text. [optional]
	// *        'options'   => array|string,    // The fields options. [optional]
	// *        'default'   => array|string,    // The fields default values. [optional]
	// *        'sanitize_callback' => 'string' // A callback function that sanitizes the settings's value. [optional]
	// *    }
	// *
	// * SUPPORTED FIELD TYPES:
	// *  checkbox
	// *  number
	// *  multicheckbox
	// *  radio
	// *  select
	// *  multiselect
	// *  text
	// *  textarea
	// *  quicktag
	// *  rte
	// *  page [shows a drop down with the WordPress pages.]
	// *  category [shows a drop down of Connections categories]
	// *
	// * RECOMMENDED: The following sanitize_callback to use based on field type.
	// *    Reference: http://codex.wordpress.org/Data_Validation
	// *
	// *    rte = wp_kses_post
	// *    quicktag = wp_kses_data
	// *    textarea = esc_textarea [for plain text]
	// *    textarea = esc_html [for text containing HTML]
	// *    text = sanitize_text_field [for plain text]
	// *    text = esc_url_raw [for URLs, not safe for display, use esc_url when displaying.]
	// *    checkbox = intval [checkbox values should be saved as either 1 or 0]
	// *
	// * NOTE:
	// *    Fields registered to a section will be saved as a serialized associative array where the section ID is the
	// *    option_name in the DB and with each field ID being the array keys.
	// *
	// *    Fields not registered to a section will be stored as a single row in the DB where the field ID is the
	// *    option_name.
	// *
	// * NOTE:
	// *    Because the filter 'cn_register_settings_fields' runs on the 'init' hook you can not use the value stored in
	// *    a variable returned from add_menu_page() or add_submenu_page() because it will not be available. Manually set
	// *    the page_hook to the string returned from those functions.
	// *
	// * NOTE: Use the one of the following to hook a settings field to one of the core settings pages.
	// *    page_hook: discussion => section: default [optional]
	// *    page_hook: discussion => section: avatars
	// *    page_hook: general => section: default [optional]
	// *    page_hook: media => section: default [optional]
	// *    page_hook: media => section: embeds
	// *    page_hook: media => section: uploads
	// *    page_hook: permalink => section: default [optional]
	// *    page_hook: permalink => section: optional
	// *    page_hook: privacy => section: default [optional]
	// *    page_hook: reading => section: default [optional]
	// *    page_hook: writing => section: default [optional]
	// *    page_hook: writing => section: post_via_email
	// *    page_hook: writing => section: remote_publishing
	// *
	// * NOTE: Even though settings fields can be registered to a WP core settings page or a custom settings page
	// *       without being registered to a section it would be best practice to avoid doing this. It is recommended
	// *       that sections be registered and then settings fields be hooked to those sections.
	// *
	// * @since  0.7.3.0
	// */
	//public static function fieldRegistry() {
	//
	//	$fields  = array();
	//	$sort    = array();
	//	$options = array();
	//
	//	$fields = apply_filters('cn_register_settings_fields', $fields);
	//	$fields = apply_filters('cn_filter_settings_fields', $fields); // @todo:  At some point delete this line
	//	//var_dump($fields);
	//
	//	if ( empty( $fields ) ) return;
	//
	//	foreach ( $fields as $key => $field ) {
	//		// Store the position values so an array multi sort can be done to position the fields in the desired order.
	//		$sort[] = ( isset( $field['position'] ) && ! empty( $field['position'] ) ) ? $field['position'] : 0;
	//	}
	//
	//	array_multisort( $sort, $fields );
	//
	//	foreach ( $fields as $field ) {
	//
	//		// Add the tab id to the page hook if the field was registered to a specific tab.
	//		if ( isset( $field['tab'] ) && ! empty( $field['tab'] ) ) $field['page_hook'] = $field['page_hook'] . '-' . $field['tab'];
	//
	//		// If the section was not set or supplied empty set the value to 'default'. This is WP core behavior.
	//		if ( ! isset( $field['section'] ) || empty( $field['section'] ) ) {
	//
	//			$section = 'default';
	//
	//		} elseif ( in_array( $field['section'], self::$coreSections ) ) {
	//
	//			$section = $field['section'];
	//
	//		} else {
	//
	//			$section = $field['plugin_id'] !== substr( $field['section'], 0, strlen( $field['plugin_id'] ) ) ? $field['plugin_id'] . '_' . $field['section'] : $field['section'];
	//		}
	//
	//		// If the option was not registered to a section or registered to a WP core section, set the option_name to the setting id.
	//		// $optionName = isset( $field['section'] ) && ! empty( $field['section'] ) && ! in_array($field['section'], self::$coreSections) ? $field['section'] : $field['id'];
	//		if ( isset( $field['section'] ) && ! empty( $field['section'] ) && ! in_array( $field['section'], self::$coreSections ) ) {
	//
	//			$optionName = $field['plugin_id'] !== substr( $field['section'], 0, strlen( $field['plugin_id'] ) ) ? $field['section'] = $field['plugin_id'] . '_' . $field['section'] : $field['section'];
	//
	//		} else {
	//
	//			$optionName = $field['id'];
	//		}
	//
	//		$options['id'] = $field['id'];
	//		$options['type'] = $field['type'];
	//		if ( isset( $field['desc'] ) ) $options['desc'] = $field['desc'];
	//		if ( isset( $field['help'] ) ) $options['help'] = $field['help'];
	//		if ( isset( $field['options'] ) ) $options['options'] = $field['options'];
	//
	//		$options = array(
	//			/*'tab'             => $field['tab'],*/
	//			'section'           => $section,
	//			'id'                => $field['id'],
	//			'type'              => $field['type'],
	//			'size'              => isset( $field['size'] ) ? $field['size'] : NULL,
	//			'title'             => isset( $field['title'] ) ? $field['title'] : '',
	//			'desc'              => isset( $field['desc'] ) ? $field['desc'] : '',
	//			'help'              => isset( $field['help'] ) ? $field['help'] : '',
	//			'show_option_none'  => isset( $field['show_option_none'] ) ? $field['show_option_none'] : '',
	//			'option_none_value' => isset( $field['option_none_value'] ) ? $field['option_none_value'] : '',
	//			'options'           => isset( $field['options'] ) ? $field['options'] : array()/*,
	//				'default'           => isset( $field['default'] ) && ! empty( $field['default'] ) ? $field['default'] : FALSE,*/
	//		);
	//
	//		// Set the field sanitation callback.
	//		$callback = isset( $field['sanitize_callback'] ) && ! empty( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : '';
	//
	//		/**
	//		 * Since this setting is handled by the Customizer API, no need to add it to the fields
	//		 * to be registered with the WordPress Settings API.
	//		 */
	//		if ( 'customizer' !== $field['type'] ) {
	//
	//			self::$registry['fields'][] = array(
	//				'id'                => $field['id'],
	//				'title'             => $field['title'],
	//				'callback'          => array( 'cnSettingsAPI', 'field' ),
	//				'page_hook'         => $field['page_hook'],
	//				'section'           => $section,
	//				'options'           => $options,
	//				'option_name'       => $optionName,
	//				'sanitize_callback' => $callback,
	//			);
	//		}
	//
	//		/*
	//		 * Store the default settings values.
	//		 */
	//		$defaultValue = ( isset( $field['default'] ) /*&& ! empty( $field['default'] )*/ ) ? $field['default'] : '';
	//
	//		// Register the plugin.
	//		if ( ! array_key_exists( $field['plugin_id'], self::$registry['plugins'] ) ) {
	//
	//			self::$registry['plugins'][$field['plugin_id']] = array();
	//		}
	//
	//		if ( ! array_key_exists( $optionName, self::$registry['plugins'][$field['plugin_id']] ) ) {
	//
	//			if ( in_array( $section, self::$coreSections ) ) {
	//				// If the field was registered to one of the WP core sections, store the default value as a singular item.
	//				self::$registry['plugins'][$field['plugin_id']][$optionName] = $defaultValue;
	//			} else {
	//				// If the field was registered to a section, store the default values as an array. // This is the recommended behaviour.
	//				self::$registry['plugins'][$field['plugin_id']][$optionName] = array( $field['id'] => $defaultValue );
	//			}
	//
	//		} else {
	//			self::$registry['plugins'][$field['plugin_id']][$optionName][$field['id']] = $defaultValue;
	//		}
	//	}
	//
	//	/*
	//	 * Add the options and the default values to the db.
	//	 *
	//	 * NOTE: Since individual values can not reliably be verified, only check to see
	//	 * if the option already if it exists in the db and if it doesn't add it with the
	//	 * registered default values. If no default values have been supplied just add the
	//	 * option to the db.
	//	 */
	//	foreach ( self::$registry['plugins'] as $plugin => $options ) {
	//
	//		foreach ( $options as $optionName => $value ) {
	//
	//			// TRUE and FALSE should be stored as 1 and 0 in the db so get_option must be strictly compared.
	//			if ( get_option( $optionName ) === FALSE ) {
	//
	//				if ( ! empty( $value ) ) {
	//					// If the option doesn't exist, the default values can safely be saved.
	//					update_option( $optionName, $value );
	//				} else {
	//					add_option( $optionName );
	//				}
	//			}
	//		}
	//	}
	//
	//}
	//
	///**
	// * Add all fields registered using this API.
	// * This method is run on the admin_init action hook.
	// *
	// * @link http://codex.wordpress.org/Function_Reference/add_settings_field
	// *
	// * @return void
	// */
	//public static function addSettingsField() {
	//
	//	foreach ( self::$registry['fields'] as $field ) {
	//		/*
	//		 * Reference:
	//		 * http://codex.wordpress.org/Function_Reference/add_settings_field
	//		 */
	//		add_settings_field(
	//			$field['id'],
	//			$field['title'],
	//			$field['callback'],
	//			$field['page_hook'],
	//			$field['section'],
	//			$field['options']
	//		);
	//
	//		// Register the settings.
	//		register_setting(
	//			$field['page_hook'],
	//			$field['option_name'],
	//			$field['sanitize_callback']
	//		);
	//	}
	//}

	/**
	 * Settings constructor.
	 *
	 * @since 8.30
	 *
	 * @param string $pluginID
	 */
	public function __construct( $pluginID ) {

		$this->pluginID = $pluginID;

		$this->hooks();
	}

	/**
	 * @since 8.30
	 */
	public function hooks() {

		// Register the setting's tabs.
		add_filter( 'cn_register_settings_tabs', array( $this, 'registerTabs' ) );

		// Register the setting's sections.
		add_filter( 'cn_register_settings_sections', array( $this, 'registerSections' ) );

		// Register the section's fields.
		add_filter( 'cn_register_settings_fields', array( $this, 'registerFields' ) );
	}

	/**
	 * @since 8.30
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public function registerTabs( $tabs ) {

		foreach ( $this->tabs as $tab ) {

			$tabs[] = $tab->toArray();
		}

		return $tabs;
	}

	/**
	 * @since 8.30
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function registerSections( $sections ) {

		foreach ( $this->sections as $section ) {

			$sections[] = $section->toArray();
		}

		return $sections;
	}

	/**
	 * @since 8.30
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function registerFields( $fields ) {

		foreach ( $this->fields as $field ) {

			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * @since 8.30
	 *
	 * @param string $id
	 * @param string $pageHook
	 * @param array  $options
	 *
	 * @return Tab
	 */
	public function addTab( $id, $pageHook, $options ) {

		$defaults = array(
			'position' => 10,
			'title'    => '',
		);

		$options = wp_parse_args( $options, $defaults );

		$tab = new Tab( $id, $pageHook, $options, $this->pluginID );

		$this->tabs[] = $tab;

		return $tab;
	}

	/**
	 * @since 8.30
	 *
	 * @param string $id
	 * @param array  $options
	 *
	 * @return Section
	 */
	public function addSection( $id, $options ) {

		$defaults = array(
			'position'  => 10,
			'title'     => '',
			'desc'      => '',
			'tab'       => '',
			'page_hook' => '',
		);

		$options = wp_parse_args( $options, $defaults );

		$options['id']        = $id;
		$options['plugin_id'] = $this->pluginID;

		if ( 0 < strlen( $options['desc'] ) ) {

			$options['callback'] = function () use ( $options ) {

				echo esc_html( $options['desc'] );
			};
		}

		$section = new Section( $id, $options['page_hook'], $options, $this->pluginID );

		$this->sections[] = $section;

		return $section;
	}

	/**
	 * @since 8.30
	 *
	 * @param string $id
	 * @param array  $field
	 *
	 * @return $this
	 */
	public function addField( $id, $field ) {

		$defaults = array(
			'position'  => 10,
			'title'     => '',
			'desc'      => '',
			'help'      => '',
			'type'      => '',
			'tab'       => '',
			'section'   => 'default',
			'page_hook' => '',
		);

		$field = wp_parse_args( $field, $defaults );

		$field['id']        = $id;
		$field['plugin_id'] = $this->pluginID;

		$this->fields[] = $field;

		return $this;
	}
}
