<?php

/**
 * Connections Settings API Wrapper Class
 *
 * @package Connections Settings API Wrapper Class
 * @copyright Copyright (c) 2012, Steven A. Zahm
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version 0.7.3.1
 */

/// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists('cnSettingsAPI') ) {

	class cnSettingsAPI {

		/**
	     * Singleton instance
	     *
	     * @var object
	     */
	    private static $instance;

		/**
		 * Array stores all tabs registered thru this API.
		 * @var array
		 */
		private static $tabs = array();

		/**
		 * Stores the settings fields registered using this API.
		 * @var
		 */
		private static $fields = array();

		/**
		 * Array of all WP core settings sections.
		 * @var array
		 */
		private static $coreSections = array('default', 'remote_publishing', 'post_via_email', 'avatars', 'embeds', 'uploads', 'optional');

		/**
		 * The array of all registerd quicktag textareas.
		 * @var array
		 */
		private static $quickTagIDs = array();

		/**
		 * Store the default values of registered settings.
		 * Will be use to store the default values if they do not exist in the db.
		 *
		 * @var array
		 */
		private static $registry = array();

		/**
		 * Return the singleton instance.
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @return void
		 */
		public static function getInstance()
		{
			if ( ! self::$instance )
			{
				self::$instance = new cnSettingsAPI();
				self::$instance->init();
			}

			return self::$instance;
		}

		/**
		 * Intiate the settings registry.
		 *
		 * NOTE: The filters for the tabs, sections and fields should be added before running init()
		 *
		 * NOTE: The recommended action to hook into is plugins_loaded. This will ensure the actions
		 * 	within this class are run at the appropiate times.
		 *
		 * NOTE: The high priority is used to make sure the actions registered in this API are run
		 * 	first. This is to help ensure registered settings are available to other actions registered
		 * 	to the admin_init and init hooks.
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @return void
		 */
		public static function init() {

			// Register the settings tabs.
			add_action( 'admin_init' , array( __CLASS__, 'registerTabs' ), .1 );

			// Register the settings sections.
			add_action( 'admin_init' , array( __CLASS__, 'registerSections' ), .1 );

			// Register the sections fields.
			add_action( 'admin_init' , array( __CLASS__, 'addSettingsField' ), .1 );
			add_action( 'init' , array( __CLASS__, 'registerFields' ), .1 );
		}

		/**
		 * Returns the registered tabs based on the supplied admin page hook.
		 *
		 * Filters:
		 * 	cn_register_admin_tabs	=>	Allow new tabs to be registered.
		 * 	cn_filter_admin_tabs	=>	Allow tabs to be filtered.
		 *
		 * The array construct for registering a tab:
		 * 	array(
		 * 		'id' => 'string',			// ID used to identify this tab and with which to register the settings sections
		 * 		'position' => int,			// Set the position of the section. The lower the int the further left the tab will be place in the bank.
		 * 		'title' => 'string',		// Title of the tab to be displayed on the admin page
		 * 		'page_hook' => 'string'		// Admin page on which to add this section of options
		 * 	}
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @return array
		 */
		public static function registerTabs() {

			$tabs = array();
			$out = array();

			$tabs = apply_filters('cn_register_settings_tabs', $tabs);
			$tabs = apply_filters('cn_filter_settings_tabs', $tabs);
			//var_dump($tabs);

			if ( empty($tabs) ) return array();

			foreach ( $tabs as $key => $tab ) {
				$out[$tab['page_hook']][] = $tab;
			}

			self::$tabs = $out;
		}

		/**
		 * Registers the settings sections with the WordPress Settings API.
		 *
		 * Filters:
		 * 	cn_register_admin_setting_section	=>	Register the settings sections.
		 * 	cn_filter_admin_setting_section	=>	Filter the settings sections.
		 *
		 * The array construct for registering a settings section:
		 * 	array(
		 * 		'tab' => 'string',			// The tab ID in which the settings section is to be hooked to. [optional]
		 * 		'id' => 'string',			// ID used to identify this section and with which to register setting fields [required]
		 * 		'position' => int,			// Set the position of the section. Lower int will place the section higher on the settings page. [optional]
		 * 		'title' => 'string',		// Title to be displayed on the admin page [required]
		 * 		'callback' => 'string',		// Callback used to render the description of the section [required]
		 * 		'page_hook' => 'string'		// Admin page on which to add this section of options [required]
		 * 	}
		 *
		 * NOTE: Use the one of the following to hook a settings section to one of the WP core settings pages.
		 * 	page_hook: discussion
		 * 	page_hook: general
		 * 	page_hook: media
		 * 	page_hook: permalink
		 * 	page_hook: privacy
		 * 	page_hook: reading
		 * 	page_hook: writing
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @return void
		 */
		public static function registerSections() {

			$sections = array();
			$sort = array();

			$sections = apply_filters('cn_register_settings_sections', $sections);
			$sections = apply_filters('cn_filter_settings_sections', $sections);
			//print_r($sections);

			if ( empty($sections) ) return;

			foreach ( $sections as $key => $section )
			{
				// Store the position values so an array multi sort can be done to postion the tab sections in the desired order.
				( isset( $section['position'] ) && ! empty( $section['position'] ) ) ? $sort[] = $section['position'] : $sort[] = 0;
			}

			if ( ! empty( $sections ) )
			{
				array_multisort( $sort , $sections );

				foreach ( $sections as $section )
				{
					if ( isset( $section['tab'] ) && ! empty( $section['tab'] ) ) $section['page_hook'] = $section['page_hook'] . '-' . $section['tab'];

					if ( ! isset( $section['callback'] ) || empty( $section['callback'] ) ) $section['callback'] = '__return_false';

					/*
					 * Reference:
					 * http://codex.wordpress.org/Function_Reference/add_settings_section
					 */
					add_settings_section(
						$section['id'] ,
						$section['title'] ,
						$section['callback'] ,
						$section['page_hook']
					);
					//global $wp_settings_sections;print_r($wp_settings_sections);
				}
			}

		}

		/**
		 * Registers the settings fields to the registered settings sections with the WordPress Settings API.
		 *
		 * Filters:
		 * 	cn_register_settings_fields	=>	Register the settings section fields.
		 * 	cn_filter_settings_fields	=>	Filter the settings section fields.
		 *
		 * The array construct for registering a settings section:
		 * 	array(
		 * 		'plugin_id',					// A unique ID for the plugin registering its settings. Recommend using the plugin slug.
		 * 		'id' => 'string',				// ID used to identify this field. [required]
		 * 										//	*must be unique. Recommend prefix with plugin slug if not registered to a settings section.
		 * 		'position' => int,				// Set the position of the field. Lower int will place the field higher on the section. [optional]
		 * 		'page_hook' => 'string',		// Admin page on which to add this section of options [required]
		 * 		'tab' => 'string',				// The tab ID in which the field is to be hooked to. [optional]
		 * 										//	*required, if the field is to be shown on a specific registered tab.
		 * 		'section' => 'string',			// The section in which the field is to be hooked to. [optional]
		 * 										//	*required, if field is to be shown in a specific registered section. Recommend prefix with plugin slug.
		 * 		'title' => 'string',			// The field title. [required]
		 * 		'type' => 'string',				// The field type. [required] Valid values : text, textarea, checkbox, multicheckbox, radio, select, rte
		 * 		'size' => 'string,				// The field size. [optional] Valid values : small | regular | large *only used for the text field type.
		 * 		'show_option_none' => 'string'	// The string to show when no value has been chosen. [required *only for the page field type] *only used for the page field type.
		 * 		'option_none_value' => 'string'	// The value to use when no value has been chosen. [required *only for the page field type] *only used for the page field type.
		 * 		'desc' => 'string',				// The field description text. [optional]
		 * 		'help' => 'string',				// The field help text. [optional]
		 * 		'options' => array||string,		// The fields options. [optional]
		 * 		'default' => array||string,		// The fields default values. [optional]
		 * 		'sanitize_callback' => 'string'	// A callback function that sanitizes the settings's value. [optional]
		 * 	}
		 *
		 * SUPPORTED FIELD TYPES:
		 * 	checkbox
		 * 	multicheckbox
		 * 	radio
		 * 	select
		 * 	multiselect
		 * 	text
		 * 	textarea
		 * 	quicktag
		 * 	rte
		 * 	page [shows a drop down with the WordPress pages.]
		 *
		 * RECOMMENDED: The following sanitize_callback to use based on field type.
		 * 	Reference: http://codex.wordpress.org/Data_Validation
		 *
		 * 	rte = wp_kses_post
		 * 	quicktag = wp_kses_data
		 * 	textarea = esc_textarea [for plain text]
		 * 	textarea = esc_html [for text containing HTML]
		 * 	text = sanitize_text_field [for plain text]
		 * 	text = esc_url_raw [for URLs, not safe for display, use esc_url when displaying.]
		 * 	checkbox = intval [checkbox values should be saved as either 1 or 0]
		 *
		 * NOTE:
		 * 	Fields registered to a section will be saved as a serialized associative array where the section ID is the option_name
		 * 	in the DB and with each field ID being the array keys.
		 *
		 * 	Fields not registered to a section will be stored as a single row in the DB where the field ID is the option_name.
		 *
		 * NOTE:
		 * 	Because the filter 'cn_register_settings_fields' runs on the 'init' hook you can not use the value stored in a variable
		 * 	returned from add_menu_page() or add_submenu_page() because it will not be available. Manually set the page_hook
		 * 	to the string returned from those functions.
		 *
		 * NOTE: Use the one of the following to hook a settings field to one of the core settings pages.
		 * 	page_hook: discussion => section: default [optional]
		 * 	page_hook: discussion => section: avatars
		 * 	page_hook: general => section: default [optional]
		 * 	page_hook: media => section: default [optional]
		 * 	page_hook: media => section: embeds
		 * 	page_hook: media => section: uploads
		 * 	page_hook: permalink => section: default [optional]
		 * 	page_hook: permalink => section: optional
		 * 	page_hook: privacy => section: default [optional]
		 * 	page_hook: reading => section: default [optional]
		 * 	page_hook: writing => section: default [optional]
		 * 	page_hook: writing => section: post_via_email
		 * 	page_hook: writing => section: remote_publishing
		 *
		 * NOTE: Even though settings fields can be registered to a WP core settings page or a custom settings page
		 * 	without being registered to a section it would be best practice to avoid doing this. It is recommended
		 *	that sections be registered and then settings fields be hooked to those sections.
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @return void
		 */
		public static function registerFields() {

			$fields = array();
			$sort = array();
			$options = array();

			$fields = apply_filters('cn_register_settings_fields', $fields);
			$fields = apply_filters('cn_filter_settings_fields', $fields);
			//var_dump($fields);

			if ( empty($fields) ) return;

			foreach ( $fields as $key => $field ) {
				// Store the position values so an array multi sort can be done to postion the fields in the desired order.
				( isset( $field['position'] ) && ! empty( $field['position'] ) ) ? $sort[] = $field['position'] : $field[] = 0;
			}

			array_multisort( $sort , $fields );

			foreach ( $fields as $field ) {

				// Add the tab id to the page hook if the field was registerd to a specific tab.
				if ( isset( $field['tab'] ) && ! empty( $field['tab'] ) ) $field['page_hook'] = $field['page_hook'] . '-' . $field['tab'];

				// If the section was not set or supplied empty set the value to 'default'. This is WP core behaviour.
				$section = ! isset($field['section']) || empty($field['section']) ? 'default' : $field['section'];

				// If the option was not registered to a section or registered to a WP core section, set the option_name to the setting id.
				$optionName = isset( $field['section'] ) && ! empty( $field['section'] ) && ! in_array($field['section'], self::$coreSections) ? $field['section'] : $field['id'];

				$options['id'] = $field['id'];
				$options['type'] = $field['type'];
				if ( isset( $field['desc'] ) ) $options['desc'] = $field['desc'];
				if ( isset( $field['help'] ) ) $options['help'] = $field['help'];
				if ( isset( $field['options'] ) ) $options['options'] = $field['options'];

				$options = array(
					/*'tab' => $field['tab'],*/
					'section' => $section,
					'id' => $field['id'],
					'type' => $field['type'],
					'size' => isset( $field['size'] ) ? $field['size'] : NULL,
					'title' => $field['title'],
					'desc' => isset( $field['desc'] ) ? $field['desc'] : '',
					'help' => isset( $field['help'] ) ? $field['help'] : '',
					'show_option_none' => isset( $field['show_option_none'] ) ? $field['show_option_none'] : '',
					'option_none_value' => isset( $field['option_none_value'] ) ? $field['option_none_value'] : '',
					'options' => isset( $field['options'] ) ? $field['options'] : array()/*,
					'default' => isset( $field['default'] ) && ! empty( $field['default'] ) ? $field['default'] : FALSE,*/
				);

				/*
				 * Reference:
				 * http://codex.wordpress.org/Function_Reference/add_settings_field
				 */
				/*add_settings_field(
					$field['id'],
					$field['title'],
					array(&$this, 'field'),
					$field['page_hook'],
					$section,
					$options
				);*/

				// Set the field sanitation callback.
				$callback = isset( $field['sanitize_callback'] ) && ! empty( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : '';

				// Register the settings.
				//register_setting( $field['page_hook'], $optionName, $callback );

				self::$fields[] = array(
					'id' => $field['id'],
					'title' => $field['title'],
					'callback' => array( __CLASS__, 'field' ),
					'page_hook' => $field['page_hook'],
					'section' => $section,
					'options' => $options,
					'option_name' => $optionName,
					'sanitize_callback' => $callback
					);

				/*
				 * Store the default settings values.
				 */
				$defaultValue = ( isset( $field['default'] ) && ! empty( $field['default'] ) ) ? $field['default'] : '';

				// Register the plugin.
				if ( ! array_key_exists( $field['plugin_id'], self::$registry ) ) self::$registry[$field['plugin_id']] = array();

				if ( ! array_key_exists( $optionName, self::$registry[$field['plugin_id']] ) ) {

					if ( in_array( $section , self::$coreSections ) ) {
						// If the field was registered to one of the WP core sections, store the default value as a singular item.
						self::$registry[$field['plugin_id']][$optionName] = $defaultValue;
					} else {
						// If the field was registered to a section, store the default values as an array. // This is the recommended behaviour.
						self::$registry[$field['plugin_id']][$optionName] = array( $field['id'] => $defaultValue );
					}

				} else {
					self::$registry[$field['plugin_id']][$optionName][$field['id']] = $defaultValue;
				}
			}

			//var_dump(self::registry);

			/*
			 * Add the options and the default values to the db.
			 *
			 * NOTE: Since individual values can not reliably be verified, only check to see
			 * if the option already if it exists in the db and if it doesn't add it with the
			 * registered default values. If no default values have been supplied just add the
			 * option to the db.
			 */
			foreach ( self::$registry as $plugin => $options ) {

				foreach ( $options as $optionName => $value ) {

					// TRUE and FALSE should be stored as 1 and 0 in the db so get_option must be strictly compared.
					if ( get_option($optionName) === FALSE ) {

						if ( ! empty($value) ) {
							// If the option doesn't exist, the default values can safely be saved.
							update_option( $optionName , $value );
						} else {
							add_option( $optionName );
						}
					}
				}
			}

		}

		/**
		 * Add all fields registered using this API.
		 * This method is run on the admin_init action hook.
		 *
		 * @return void
		 */
		public static function addSettingsField() {

			foreach ( self::$fields as $field ) {
				/*
				 * Reference:
				 * http://codex.wordpress.org/Function_Reference/add_settings_field
				 */
				add_settings_field(
					$field['id'],
					$field['title'],
					$field['callback'],
					$field['page_hook'],
					$field['section'],
					$field['options']
				);

				// Register the settings.
				register_setting( $field['page_hook'], $field['option_name'], $field['sanitize_callback'] );
			}
		}

		/**
		 * Output the settings page, if one has been hooked to the current admin page, and output
		 * the settings sections hooked to the current admin page/tab.
		 *
		 * WordPress core icons that can be used for the page and tab icons.
		 * 	- index
		 * 	- tools
		 * 	- edit
		 * 	- upload
		 * 	- link-manager
		 * 	- edit-pages
		 * 	- edit-comments
		 * 	- themes
		 * 	- plugins
		 * 	- users
		 * 	- options-general
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @param string $pageHook
		 * @param bool $return [optional]
		 * @return string
		 */
		public function form( $pageHook , $args = array() )
		{
			$defaults = array(
				'page_title' => '',
				'page_icon' => '',
				'tab_icon' => ''
				);

			$args = wp_parse_args( $args , $defaults );
			//var_dump($args);

			$out = '';
			$sort = array();

			// Page icon.
			if ( ! empty( $args['page_icon'] ) ) echo get_screen_icon( $args['page_icon'] );

			// Page title.
			if ( ! empty( $args['page_title'] ) ) echo '<h2>' , $args['page_title'] , '</h2>';

			// Display any registered settings errors and success messages.
			settings_errors();

			// Display the tab icon
			if ( ! empty( $args['tab_icon'] ) ) echo get_screen_icon( $args['tab_icon'] );

			// If the page hook was not supplied echo an empty string.
			if ( ! empty( $pageHook ) )
			{
				$tabs = self::$tabs[$pageHook]; //var_dump(self::$tabs[$pageHook]);

				// If there were no tabs returned echo out an empty string.
				if ( ! empty( $tabs ) )
				{
					echo '<h2 class="nav-tab-wrapper">';

					// Store the position values so an array multi sort can be done to postion the tabs in the desired order.
					foreach ( $tabs as $key => $tab )
					{
						$sort[] = ( isset( $tab['position'] ) && ! empty( $tab['position'] ) ) ? $tab['position'] : 0;
					}

					// Sort the tabs based on their position.
					array_multisort( $sort , $tabs );

					// If the current tab isn't set, set the current tab to the intial tab in the array.
					$currentTab = isset( $_GET['tab'] ) ? $_GET['tab'] : $tabs[0]['id'];

					foreach ( $tabs as $tab )
					{
						// Only show tabs registered to the current page.
						if ( ! isset( $tab['page_hook'] ) || $tab['page_hook'] !== $pageHook ) continue;

						echo '<a class="nav-tab' . ( ( $currentTab === $tab['id'] ) ? ' nav-tab-active' : '' ) . '" href="' . add_query_arg('tab', $tab['id']) . '">' . $tab['title'] . '</a>';
					}

					echo '</h2>';
				}
			}

			echo  '<form method="post" action="options.php">';

			/*
			 * If tabs were registered to the current page, set the hidden fields with the current tab id
			 * appended to the page hook. If this is not done the settings registered to the current tab will
			 * not be saved.
			 */
			//global $new_whitelist_options;print_r($new_whitelist_options);
			settings_fields( ( isset( $currentTab ) && ! empty( $currentTab ) ) ? $pageHook . '-' . $currentTab : $pageHook );

			/*
			 * Output any fields that were not registered to a specific section and defaulted to the default section.
			 * Mimics default core WP behaviour.
			 */
			echo '<table class="form-table">';
			do_settings_fields( ( isset( $currentTab ) && ! empty( $currentTab ) ) ? $pageHook . '-' . $currentTab : $pageHook , 'default');
			echo '</table>';

			/*
			 * Reference:
			 * http://codex.wordpress.org/Function_Reference/do_settings_sections
			 *
			 * If the section is hooked into a tab add the current tab to the page hook
			 * so only the settings registered to the current tab are displayed.
			 */
			do_settings_sections( ( isset( $currentTab ) && ! empty( $currentTab ) ) ? $pageHook . '-' . $currentTab : $pageHook );

			submit_button();


			echo '</form>';
		}

		/**
		 * The call back used to render the settings field types.
		 *
		 * Credit to Tareq. Some of the code to render the form fields were pickup from his Settings API
		 * 	http://tareq.wedevs.com/2012/06/wordpress-settings-api-php-class/
		 * 	https://github.com/tareq1988/wordpress-settings-api-class
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @access private
		 * @param array $field
		 * @return string
		 */
		public static function field( $field )
		{
			global $wp_version;
			$out = '';

			if ( in_array( $field['section'] , self::$coreSections ) )
			{
				$value = get_option( $field['id'] ); //print_r($value);
				$name = sprintf( '%1$s', $field['id'] );
			}
			else
			{
				$values = get_option( $field['section'] );
				$value = ( isset( $values[$field['id']] ) ) ? $values[$field['id']] : NULL; //print_r($value);
				$name = sprintf( '%1$s[%2$s]', $field['section'], $field['id'] );
			}

			switch ( $field['type'] )
			{
				case 'checkbox':
					$checked = isset( $value ) ? checked(1, $value, FALSE) : '';

					$out .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s" name="%1$s" value="1" %2$s/>', $name, $checked );
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<label for="%1$s"> %2$s</label>', $name, $field['desc'] );

					break;

				case 'multicheckbox':
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span class="description">%s</span><br />', $field['desc'] );

					foreach ( $field['options'] as $key => $label )
					{
						$checked = checked( TRUE , in_array($key, (array) $value) , FALSE );

						$out .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[]" value="%2$s"%3$s/>', $name, $key, $checked );
						$out .= sprintf( '<label for="%1$s[%2$s]"> %3$s</label><br />', $name, $key, $label );
					}

					break;

				case 'radio':
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span class="description">%s</span><br />', $field['desc'] );

					foreach ( $field['options'] as $key => $label )
					{
						$out .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s]" name="%1$s" value="%2$s" %3$s/>', $name, $key, checked( $value, $key, FALSE ) );
						$out .= sprintf( '<label for="%1$s[%3$s]"> %2$s</label><br />', $name, $label, $key );
					}

					break;

				case 'select':
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span class="description">%1$s</span><br />', $field['desc'] );

					$out .= sprintf( '<select name="%1$s" id="%1$s">', $name );

					foreach ( $field['options'] as $key => $label )
					{
						$out .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', $key, selected( $value, $key, FALSE ), $label );
					}

					$out .= '</select>';

					break;

				case 'multiselect':
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span class="description">%s</span><br />', $field['desc'] );

					$out .= '<span style="background-color: white; border-color: #DFDFDF; border-radius: 3px; border-width: 1px; border-style: solid; display: block; height: 90px; padding: 0 3px; overflow: auto; width: 25em;">';

					foreach ( $field['options'] as $key => $label )
					{
						$checked = checked( TRUE , in_array($key, $value) , FALSE );

						$out .= sprintf( '<label><input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[]" value="%2$s" %3$s/> %4$s</label><br />', $name, $key, $checked, $label );
					}

					$out .= "</span>";;

					break;

				case 'text':
					$size = isset( $field['size'] ) && ! empty( $field['size'] ) ? $field['size'] : 'regular';

					$out .= sprintf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>', $size, $name, $value );
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span  class="description"> %1$s</span>', $field['desc'] );

					break;

				case 'textarea':
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span class="description"> %1$s</span><br />', $field['desc'] );
					$out .= sprintf( '<textarea rows="10" cols="50" class="%1$s-text" id="%2$s" name="%2$s">%3$s</textarea>', 'large', $name, $value );

					break;

				case 'quicktag':
					if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span class="description"> %1$s</span><br />', $field['desc'] );

					$out .= '<div class="wp-editor-container">';
					$out .= sprintf( '<textarea class="wp-editor-area" rows="20" cols="40" id="%1$s" name="%1$s">%2$s</textarea>', $name, $value );
					$out .= '</div>';

					self::$quickTagIDs[] = $name;

					add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'quickTagJS' ) );

					break;

				case 'rte':
					$size = isset( $field['size'] ) && ! empty( $field['size'] ) ? $field['size'] : 'regular';

					if( $wp_version >= 3.3 && function_exists('wp_editor') )
					{
						if ( isset($field['desc']) && ! empty($field['desc']) ) echo sprintf( '<span class="description">%1$s</span><br />', $field['desc'] );
						wp_editor($value, sprintf('%1$s' , $name), array('textarea_name' => sprintf('%1$s' , $name) ) );

					}
					else
					{
						if ( isset($field['desc']) && ! empty($field['desc']) ) $out .= sprintf( '<span class="description">%1$s</span><br />', $field['desc'] );
						$out .= sprintf( '<textarea rows="10" cols="50" class="%1$s-text" id="%2$s" name="%2$s">%3$s</textarea>', $size, $name, $value );
					}

					break;

				case 'page':
					$out .= wp_dropdown_pages( array( 'name' => $name, 'echo' => 0, 'show_option_none' => $field['show_option_none'], 'option_none_value' => $field['option_none_value'], 'selected' => $value ) );

					break;
			}

			echo $out;
		}

		/**
		 * Outputs the JS necessary to support the quicktag textareas.
		 *
		 * @author Steven A. Zahm
		 * @access private
		 * @since 0.7.3.0
		 * @return void
		 */
		public static function quickTagJS() {
			echo '<script type="text/javascript">/* <![CDATA[ */';

			foreach ( self::$quickTagIDs as $id ) echo 'quicktags("' . $id . '");';

		    echo '/* ]]> */</script>';
		}

		/**
		 * Return all the settings for a specific plugin that was registered using this API.
		 * The optional parameters can be used to return a specific settings section or a
		 * specific option from within a section.
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @param string $pluginID The plugin_id the settings field was registered to.
		 * @param string $section [optional] The section id the settings field was registered to.
		 * @param string $option [optional] The settings field id that was used to register the option.
		 * @return mixed
		 */
		public static function get( $pluginID , $section = '' , $option = '' )
		{
			$settings = array();
			//var_dump($this->registry[$pluginID]);

			// Return all the specified plugin options registered using this API.
			if ( array_key_exists( $pluginID, self::$registry ) )
			{
				/*
				 * Since checkboxes are not returned if unchecked when submitting a form,
				 * the fields are/can not be saved. This basically traverses the registered
				 * settings array and adds them back into the options retrieved from the db via
				 * get_option() the missing key with an empty value. Using an empty value
				 * should be safe for the other field types too since that mimics the WP Settings API.
				 */
				foreach ( self::$registry[$pluginID] as $optionName => $values )
				{
					// TRUE and FALSE should be stored as 1 and 0 in the db so get_option must be strictly compared.
					if ( get_option($optionName) !== FALSE )
					{
						$settings[$optionName] = get_option($optionName);

						if ( is_array( self::$registry[$pluginID][$optionName] ) )
						{
							foreach ( self::$registry[$pluginID][$optionName] as $key => $value )
							{
								if ( ! isset( $settings[$optionName][$key] ) || empty( $settings[$optionName][$key] ) ) $settings[$optionName][$key] = '';
							}
						}
						elseif ( ! isset( $settings[$optionName] ) || empty( $settings[$optionName] ) )
						{
							$settings[$optionName] = '';
						}
					}
					else
					{
						return FALSE;
					}
				}

			}
			else
			{
				return FALSE;
			}

			if ( ! empty($section) )
			{
				if ( array_key_exists( $section , $settings ) )
				{
					if ( ! empty($option) )
					{
						if ( array_key_exists( $option , $settings[$section] ) )
						{
							return $settings[$section][$option];
						}
						else
						{
							return FALSE;
						}
					}
					else
					{
						return $settings[$section];
					}
				}
				else
				{
					return FALSE;
				}
			}

			return $settings;

		}

		/**
		 * Reset all the settings to the registered default values
		 * for a specific plugin that was registered using this API.
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @param string $pluginID
		 * @return mixed
		 */
		public static function reset( $pluginID )
		{
			if ( array_key_exists( $pluginID, self::$registry ) )
			{
				foreach ( self::$registry[$pluginID] as $optionName => $values )
				{
					update_option( $optionName , $values );
				}
			}
			else
			{
				return FALSE;
			}
		}

		/**
		 * Delete all the settings for a specific plugin that was registered using this API.
		 *
		 * @author Steven A. Zahm
		 * @since 0.7.3.0
		 * @param string $pluginID
		 * @return mixed
		 */
		public static function delete( $pluginID )
		{
			if ( array_key_exists( $pluginID, self::$registry ) )
			{
				foreach ( self::$registry[$pluginID] as $optionName => $values )
				{
					delete_option( $optionName , $values );
				}
			}
			else
			{
				return FALSE;
			}
		}
	}
}