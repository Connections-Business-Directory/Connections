<?php
/**
 * Connections Settings API Wrapper Class
 *
 * @package Connections Settings API Wrapper Class
 * @copyright Copyright (c) 2012, Steven A. Zahm
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version 0.7.3.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Form\Field;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_array;
use function Connections_Directory\Form\Field\remapOptions as remapFieldOptions;

/**
 * Class cnSettingsAPI
 */
class cnSettingsAPI {

	/**
	 * Singleton instance
	 *
	 * @var cnSettingsAPI
	 */
	private static $instance;

	/**
	 * Array stores all tabs registered through this API.
	 * @var array
	 */
	private static $tabs = array();

	/**
	 * Stores the settings fields registered using this API.
	 * @var
	 */
	private static $fields = array();

	/**
	 * Store the REST API field parameters for when the settings are registered with the REST API.
	 * @since 9.2
	 * @var array
	 */
	private static $rest = array();

	/**
	 * Array of all WP core settings sections.
	 * @var array
	 */
	private static $coreSections = array( 'default', 'remote_publishing', 'post_via_email', 'avatars', 'embeds', 'uploads', 'optional' );

	/**
	 * The array of all registered sortable IDs.
	 * @var array
	 */
	private static $sortableIDs = array();

	/**
	 * Store the default values of registered settings.
	 * Will be used to store the default values if they do not exist in the db.
	 *
	 * @var array
	 */
	private static $registry = array();

	/**
	 * Return the singleton instance.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.3.0
	 *
	 * @return cnSettingsAPI
	 */
	public static function getInstance() {

		if ( ! self::$instance ) {

			self::$instance = new cnSettingsAPI();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initiate the settings registry.
	 *
	 * NOTE: The filters for the tabs, sections and fields should be added before running init()
	 *
	 * NOTE: The recommended action to hook into is plugins_loaded. This will ensure the actions
	 *       within this class are run at the appropriate times.
	 *
	 * NOTE: The high priority is used to make sure the actions registered in this API are run
	 *       first. This is to help ensure registered settings are available to other actions registered
	 *       to the admin_init and init hooks.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.3.0
	 * @return void
	 */
	public static function init() {

		// Register the settings tabs.
		add_action( 'admin_init', array( __CLASS__, 'registerTabs' ), 0 );

		// Register the settings sections.
		add_action( 'admin_init', array( __CLASS__, 'registerSections' ), 0 );

		// Register the sections fields.
		add_action( 'admin_init', array( __CLASS__, 'addSettingsField' ), 20 );
		add_action( 'rest_api_init', array( __CLASS__, 'registerSettingsField' ), 20 );
		add_action( 'init', array( __CLASS__, 'registerFields' ), 20 );
	}

	/**
	 * Returns the registered tabs based on the supplied admin page hook.
	 *
	 * Filters:
	 *     cn_register_admin_tabs => Allow new tabs to be registered.
	 *     cn_filter_admin_tabs   => Allow tabs to be filtered.
	 *
	 * The array construct for registering a tab:
	 *     array(
	 *         'id'        => 'string', // ID used to identify this tab and with which to register the settings sections
	 *         'position'  => int,      // Set the position of the section. The lower the int the further left the tab will be place in the bank.
	 *         'title'     => 'string', // Title of the tab to be displayed on the admin page
	 *         'page_hook' => 'string'  // Admin page on which to add this section of options
	 *     }
	 *
	 * @access private
	 * @since  0.7.3.0
	 */
	public static function registerTabs() {

		$tabs = array();
		$out  = array();

		$tabs = apply_filters( 'cn_register_settings_tabs', $tabs );
		$tabs = apply_filters( 'cn_filter_settings_tabs', $tabs );
		// var_dump($tabs);

		if ( ! empty( $tabs ) ) {

			foreach ( $tabs as $key => $tab ) {

				$out[ $tab['page_hook'] ][] = $tab;
			}

			self::$tabs = $out;
		}
	}

	/**
	 * Registers the settings sections with the WordPress Settings API.
	 *
	 * Filters:
	 *     cn_register_admin_setting_section => Register the settings sections.
	 *     cn_filter_admin_setting_section   => Filter the settings sections.
	 *
	 * The array construct for registering a settings section:
	 *     array(
	 *         'tab'       => 'string', // The tab ID in which the settings section is to be hooked to. [optional]
	 *         'id'        => 'string', // ID used to identify this section and with which to register setting fields [required]
	 *         'position'  => int,      // Set the position of the section. Lower int will place the section higher on the settings page. [optional]
	 *         'title'     => 'string', // Title to be displayed on the admin page [required]
	 *         'callback'  => 'string', // Callback used to render the description of the section [required]
	 *         'page_hook' => 'string'  // Admin page on which to add this section of options [required]
	 *     }
	 *
	 * NOTE: Use the one of the following to hook a settings section to one of the WP core settings pages.
	 *     page_hook: discussion
	 *     page_hook: general
	 *     page_hook: media
	 *     page_hook: permalink
	 *     page_hook: privacy
	 *     page_hook: reading
	 *     page_hook: writing
	 *
	 * @access private
	 * @since  0.7.3.0
	 */
	public static function registerSections() {

		$sections = array();
		$sort     = array();

		$sections = apply_filters( 'cn_register_settings_sections', $sections );
		$sections = apply_filters( 'cn_filter_settings_sections', $sections );
		// print_r($sections);

		if ( empty( $sections ) ) {
			return;
		}

		foreach ( $sections as $key => $section ) {

			// Store the position values so an array multi sort can be done to position the tab sections in the desired order.
			( isset( $section['position'] ) && ! empty( $section['position'] ) ) ? $sort[] = $section['position'] : $sort[] = 0;
		}

		if ( ! empty( $sections ) ) {

			array_multisort( $sort, $sections );

			foreach ( $sections as $section ) {

				$id = isset( $section['plugin_id'] ) && substr( $section['id'], 0, strlen( $section['plugin_id'] ) ) !== $section['plugin_id'] ? $section['plugin_id'] . '_' . $section['id'] : $section['id'];

				if ( isset( $section['tab'] ) && ! empty( $section['tab'] ) ) {
					$section['page_hook'] = $section['page_hook'] . '-' . $section['tab'];
				}

				if ( ! isset( $section['callback'] ) || empty( $section['callback'] ) ) {
					$section['callback'] = '__return_false';
				}

				/*
				 * Reference:
				 * http://codex.wordpress.org/Function_Reference/add_settings_section
				 */
				add_settings_section(
					$id,
					$section['title'],
					$section['callback'],
					$section['page_hook']
				);
				// global $wp_settings_sections;print_r($wp_settings_sections);
			}
		}
	}

	/**
	 * Registers the settings fields to the registered settings sections with the WordPress Settings API.
	 *
	 * Filters:
	 *     cn_register_settings_fields => Register the settings section fields.
	 *     cn_filter_settings_fields   => Filter the settings section fields.
	 *
	 * The array construct for registering a settings section:
	 *     array(
	 *         'plugin_id',                    // A unique ID for the plugin registering its settings. Recommend using the plugin slug.
	 *         'id' => 'string',               // ID used to identify this field. [required]
	 *                                         // *must be unique. Recommend prefix with plugin slug if not registered to a settings section.
	 *         'position' => int,              // Set the position of the field. Lower int will place the field higher on the section. [optional]
	 *         'page_hook' => 'string',        // Admin page on which to add this section of options [required]
	 *         'tab' => 'string',              // The tab ID in which the field is to be hooked to. [optional]
	 *                                         // *required, if the field is to be shown on a specific registered tab.
	 *         'section' => 'string',          // The section in which the field is to be hooked to. [optional]
	 *                                         // *required, if field is to be shown in a specific registered section. Recommend prefix with plugin slug.
	 *         'title' => 'string',            // The field title. [required]
	 *         'type' => 'string',             // The field type. [required] Valid values : text, textarea, checkbox, multicheckbox, radio, select, rte
	 *         'size' => 'string,              // The field size. [optional] Valid values : small | regular | large *only used for the text field type.
	 *         'show_option_none' => 'string'  // The string to show when no value has been chosen. [required *only for the page field type] *only used for the page field type.
	 *         'option_none_value' => 'string' // The value to use when no value has been chosen. [required *only for the page field type] *only used for the page field type.
	 *         'desc' => 'string',             // The field description text. [optional]
	 *         'help' => 'string',             // The field help text. [optional]
	 *         'options' => array||string,     // The fields options. [optional]
	 *         'default' => array||string,     // The fields default values. [optional]
	 *         'sanitize_callback' => 'string' // A callback function that sanitizes the setting's value. [optional]
	 *     }
	 *
	 * SUPPORTED FIELD TYPES:
	 *  checkbox
	 *  number
	 *  multicheckbox
	 *  radio
	 *  select
	 *  multiselect
	 *  text
	 *  textarea
	 *  quicktag
	 *  rte
	 *  page [shows a dropdown with the WordPress pages.]
	 *  category [shows a dropdown of Connections categories]
	 *
	 * RECOMMENDED: The following sanitize_callback to use based on field type.
	 *     Reference: http://codex.wordpress.org/Data_Validation
	 *
	 *     rte = wp_kses_post
	 *     quicktag = wp_kses_data
	 *     textarea = esc_textarea [for plain text]
	 *     textarea = esc_html [for text containing HTML]
	 *     text = sanitize_text_field [for plain text]
	 *     text = esc_url_raw [for URLs, not safe for display, use esc_url when displaying.]
	 *     checkbox = intval [checkbox values should be saved as either 1 or 0]
	 *
	 * NOTE:
	 *     Fields registered to a section will be saved as a serialized associative array where the section ID is the option_name
	 *     in the DB and with each field ID being the array keys.
	 *
	 *     Fields not registered to a section will be stored as a single row in the DB where the field ID is the option_name.
	 *
	 * NOTE:
	 *     Because the filter 'cn_register_settings_fields' runs on the 'init' hook you can not use the value stored in a variable
	 *     returned from add_menu_page() or add_submenu_page() because it will not be available. Manually set the page_hook
	 *     to the string returned from those functions.
	 *
	 * NOTE: Use the one of the following to hook a settings field to one of the core settings pages.
	 *     page_hook: discussion => section: default [optional]
	 *     page_hook: discussion => section: avatars
	 *     page_hook: general => section: default [optional]
	 *     page_hook: media => section: default [optional]
	 *     page_hook: media => section: embeds
	 *     page_hook: media => section: uploads
	 *     page_hook: permalink => section: default [optional]
	 *     page_hook: permalink => section: optional
	 *     page_hook: privacy => section: default [optional]
	 *     page_hook: reading => section: default [optional]
	 *     page_hook: writing => section: default [optional]
	 *     page_hook: writing => section: post_via_email
	 *     page_hook: writing => section: remote_publishing
	 *
	 * NOTE: Even though settings fields can be registered to a WP core settings page or a custom settings page
	 *       without being registered to a section it would be best practice to avoid doing this. It is recommended
	 *       that sections be registered and then settings fields be hooked to those sections.
	 *
	 * @access private
	 * @since  0.7.3.0
	 */
	public static function registerFields() {

		$fields  = array();
		$sort    = array();
		$options = array();

		$fields = apply_filters( 'cn_register_settings_fields', $fields );
		$fields = apply_filters( 'cn_filter_settings_fields', $fields ); // @todo:  At some point delete this line
		// var_dump($fields);

		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as $key => $field ) {
			// Store the position values so an array multi sort can be done to position the fields in the desired order.
			$sort[] = ( isset( $field['position'] ) && ! empty( $field['position'] ) ) ? $field['position'] : 0;
		}

		array_multisort( $sort, $fields );

		foreach ( $fields as $field ) {

			// Add the tab id to the page hook if the field was registered to a specific tab.
			if ( isset( $field['tab'] ) && ! empty( $field['tab'] ) ) {
				$field['page_hook'] = $field['page_hook'] . '-' . $field['tab'];
			}

			// If the section was not set or supplied empty set the value to 'default'. This is WP core behavior.
			if ( ! isset( $field['section'] ) || empty( $field['section'] ) ) {

				$section = 'default';

			} elseif ( in_array( $field['section'], self::$coreSections ) ) {

				$section = $field['section'];

			} else {

				$section = substr( $field['section'], 0, strlen( $field['plugin_id'] ) ) !== $field['plugin_id'] ? $field['plugin_id'] . '_' . $field['section'] : $field['section'];
			}

			// If the option was not registered to a section or registered to a WP core section, set the option_name to the setting id.
			// $optionName = isset( $field['section'] ) && ! empty( $field['section'] ) && ! in_array($field['section'], self::$coreSections) ? $field['section'] : $field['id'];
			if ( isset( $field['section'] ) && ! empty( $field['section'] ) && ! in_array( $field['section'], self::$coreSections ) ) {

				$optionName = substr( $field['section'], 0, strlen( $field['plugin_id'] ) ) !== $field['plugin_id'] ? $field['section'] = $field['plugin_id'] . '_' . $field['section'] : $field['section'];

			} else {

				$optionName = $field['id'];
			}

			$options['id']   = $field['id'];
			$options['type'] = $field['type'];
			if ( isset( $field['desc'] ) ) {
				$options['desc'] = $field['desc'];
			}

			if ( isset( $field['help'] ) ) {
				$options['help'] = $field['help'];
			}

			if ( isset( $field['options'] ) ) {
				$options['options'] = $field['options'];
			}

			$options = array(
				/*'tab'             => $field['tab'],*/
				'section'           => $section,
				'id'                => $field['id'],
				'type'              => $field['type'],
				'size'              => isset( $field['size'] ) ? $field['size'] : null,
				'title'             => isset( $field['title'] ) ? $field['title'] : '',
				'desc'              => isset( $field['desc'] ) ? $field['desc'] : '',
				'help'              => isset( $field['help'] ) ? $field['help'] : '',
				'show_option_none'  => isset( $field['show_option_none'] ) ? $field['show_option_none'] : '',
				'option_none_value' => isset( $field['option_none_value'] ) ? $field['option_none_value'] : '',
				'options'           => isset( $field['options'] ) ? $field['options'] : array(),
				'default'           => isset( $field['default'] ) ? $field['default'] : null,
			);

			// Set the field sanitation callback.
			$callback = isset( $field['sanitize_callback'] ) && ! empty( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : '';

			/**
			 * Since this setting is handled by the Customizer API, no need to add it to the fields
			 * to be registered with the WordPress Settings API.
			 */
			if ( 'customizer' !== $field['type'] ) {

				self::$fields[] = array(
					'id'                => $field['id'],
					'title'             => $field['title'],
					'callback'          => array( __CLASS__, 'field' ),
					'page_hook'         => $field['page_hook'],
					'section'           => $section,
					'options'           => $options,
					'option_name'       => $optionName,
					'sanitize_callback' => $callback,
				);

				if ( //( isset( $field['show_in_rest'] ) && TRUE === $field['show_in_rest'] ) &&
					 ( isset( $field['schema'] ) && is_array( $field['schema'] ) )
				) {

					self::$rest = cnArray::add(
						self::$rest,
						"{$optionName}.schema.properties.{$field['id']}",
						$field['schema']
					);

					self::$rest = cnArray::add(
						self::$rest,
						"{$optionName}.schema.default.{$field['id']}",
						cnArray::get( $field, 'default', null )
					);
				}
			}

			/*
			 * Store the default settings values.
			 */
			$defaultValue = ( isset( $field['default'] ) /*&& ! empty( $field['default'] )*/ ) ? $field['default'] : '';

			// Register the plugin.
			if ( ! array_key_exists( $field['plugin_id'], self::$registry ) ) {
				self::$registry[ $field['plugin_id'] ] = array();
			}

			if ( ! array_key_exists( $optionName, self::$registry[ $field['plugin_id'] ] ) ) {

				if ( in_array( $section, self::$coreSections ) ) {
					// If the field was registered to one of the WP core sections, store the default value as a singular item.
					self::$registry[ $field['plugin_id'] ][ $optionName ] = $defaultValue;
				} else {
					// If the field was registered to a section, store the default values as an array. // This is the recommended behaviour.
					self::$registry[ $field['plugin_id'] ][ $optionName ] = array( $field['id'] => $defaultValue );
				}

			} else {
				self::$registry[ $field['plugin_id'] ][ $optionName ][ $field['id'] ] = $defaultValue;
			}
		}

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
				if ( get_option( $optionName ) === false ) {

					if ( ! empty( $value ) ) {
						// If the option doesn't exist, the default values can safely be saved.
						update_option( $optionName, $value );
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
	 * @link http://codex.wordpress.org/Function_Reference/add_settings_field
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
			register_setting(
				$field['page_hook'],
				$field['option_name'],
				$field['sanitize_callback']
			);
		}
	}

	/**
	 * Callback for the `rest_api_init` action.
	 *
	 * Registers the settings with the REST API.
	 *
	 * @access private
	 * @since  9.2
	 */
	public static function registerSettingsField() {

		$sections = apply_filters( 'cn_register_settings_sections', array() );

		foreach ( $sections as $section ) {

			$id = isset( $section['plugin_id'] ) && substr( $section['id'], 0, strlen( $section['plugin_id'] ) ) !== $section['plugin_id'] ? $section['plugin_id'] . '_' . $section['id'] : $section['id'];

			self::$rest = cnArray::add(
				self::$rest,
				"{$id}.show_in_rest",
				cnArray::get( $section, 'show_in_rest', false )
			);

			self::$rest = cnArray::add(
				self::$rest,
				"{$id}.schema.type",
				cnArray::get( $section, 'schema.type', 'string' )
			);

			self::$rest = cnArray::add(
				self::$rest,
				"{$id}.schema.description",
				cnArray::get( $section, 'schema.description', '' )
			);
		}

		foreach ( self::$fields as $field ) {

			$type   = cnArray::get( self::$rest, "{$field['option_name']}.schema.type", 'string' );
			$schema = false;

			if ( cnArray::get( self::$rest, "{$field['option_name']}.show_in_rest", false ) ) {

				$schema = cnArray::get( self::$rest, "{$field['option_name']}", false );
			}

			// Register the settings.
			register_setting(
				$field['page_hook'],
				$field['option_name'],
				array(
					'show_in_rest'      => $schema,
					'type'              => $type,
					'group'             => $field['page_hook'],
					'name'              => $field['option_name'],
					'description'       => cnArray::get( $schema, 'description', '' ),
					'sanitize_callback' => $field['sanitize_callback'],
				)
			);
		}
	}

	/**
	 * Use only be used after the `init` hook.
	 *
	 * @since 9.3
	 *
	 * @return array
	 */
	public static function getRegisteredRESTOptionProperties() {

		return self::$rest;
	}

	/**
	 * Output the settings page, if one has been hooked to the current admin page, and output
	 * the settings sections hooked to the current admin page/tab.
	 *
	 * @access public
	 * @since  0.7.3.0
	 *
	 * @param string $pageHook
	 * @param array  $args
	 */
	public static function form( $pageHook, $args = array() ) {

		//$defaults = array(
		//	'page_title' => '',
		//);

		//$args = wp_parse_args( $args , $defaults );
		//var_dump($args);

		$tabs = self::$tabs[ $pageHook ];
		//var_dump(self::$tabs[$pageHook]);

		$tabIDs = wp_list_pluck( $tabs, 'id' );
		//var_dump( $tabIDs );

		// If the current tab isn't set, set the current tab to the initial tab in the array.
		$currentTab = isset( $_GET['tab'] ) && array_search( $_GET['tab'], $tabIDs ) ? sanitize_text_field( $_GET['tab'] ) : $tabs[0]['id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Page title.
		//if ( ! empty( $args['page_title'] ) ) echo '<h1>' , $args['page_title'] , '</h1>';

		// Display any registered settings errors and success messages.
		settings_errors();

		?>
		<div class="wrap <?php echo 'wrap-' . sanitize_html_class( $currentTab ); ?>">

			<h1>Connections : <?php _e( 'Settings', 'connections' ); ?></h1>

			<?php

			// If there were no tabs returned echo out an empty string.
			if ( ! empty( $tabs ) ) {
				?>

				<div class="nav-tab-wrapper">

					<?php

					$sort = array();

					// Store the position values so an array multi sort can be done to position the tabs in the desired order.
					foreach ( $tabs as $key => $tab ) {

						$sort[] = ( isset( $tab['position'] ) && ! empty( $tab['position'] ) ) ? $tab['position'] : 0;
					}

					// Sort the tabs based on their position.
					array_multisort( $sort, $tabs );

					foreach ( $tabs as $tab ) {

						// Only show tabs registered to the current page.
						if ( ! isset( $tab['page_hook'] ) || $tab['page_hook'] !== $pageHook ) {
							continue;
						}

						echo '<a class="nav-tab' . ( $tab['id'] === $currentTab ? ' nav-tab-active' : '' ) . '" href="' . esc_url( add_query_arg( 'tab', $tab['id'] ) ) . '">' . esc_html( $tab['title'] ) . '</a>';
					}

					?>
				</div>
				<?php
			}

			?>

			<div id="tab_container">

				<form method="post" action="options.php">

					<?php

					$optionGroup = isset( $currentTab ) && ! empty( $currentTab ) ? $pageHook . '-' . $currentTab : $pageHook;

					/**
					 * Allow plugins to fire actions before tab content is displayed.
					 *
					 * @since 8.5.28
					 */
					do_action( $optionGroup );

					/*
					 * If tabs were registered to the current page, set the hidden fields with the current tab id
					 * appended to the page hook. If this is not done the settings registered to the current tab will
					 * not be saved.
					 */
					// global $new_whitelist_options;print_r($new_whitelist_options);
					?>
					<?php settings_fields( $optionGroup ); ?>

					<?php
					/*
					 * Output any fields that were not registered to a specific section and defaulted to the default section.
					 * Mimics default core WP behaviour.
					 */
					?>

					<table class="form-table">

					<?php do_settings_fields( $optionGroup, 'default' ); ?>

					</table>

					<?php
					/*
					 * Reference:
					 * http://codex.wordpress.org/Function_Reference/do_settings_sections
					 *
					 * If the section is hooked into a tab add the current tab to the page hook
					 * so only the settings registered to the current tab are displayed.
					 */
					?>
					<?php do_settings_sections( $optionGroup ); ?>

				<?php submit_button(); ?>
				</form>

			</div><!-- #tab_container -->
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * The callback used to render the settings field types.
	 *
	 * Credit to Tareq. Some code to render the form fields were pickup from his Settings API
	 *     http://tareq.wedevs.com/2012/06/wordpress-settings-api-php-class/
	 *     https://github.com/tareq1988/wordpress-settings-api-class
	 *
	 * @internal
	 * @since 0.7.3.0
	 *
	 * @param array $field
	 */
	public static function field( $field ) {

		$out = '';

		if ( in_array( $field['section'], self::$coreSections ) ) {

			$value = get_option( $field['id'] );
			$name  = sprintf( '%1$s', $field['id'] );

		} else {

			$values = get_option( $field['section'] );
			$value  = ( isset( $values[ $field['id'] ] ) ) ? $values[ $field['id'] ] : null;
			$name   = sprintf( '%1$s[%2$s]', $field['section'], $field['id'] );
		}

		switch ( $field['type'] ) {

			case 'checkbox':
				$out .= Field\Checkbox::create()
									  ->setId( $name )
									  ->addClass( 'checkbox' )
									  ->setName( $name )
									  ->maybeIsChecked( $value )
									  ->addLabel( Field\Label::create()->setFor( $name )->text( $field['desc'] ) )
									  ->getHTML();
				break;

			case 'checkbox-group':
			case 'multicheckbox':
				remapFieldOptions( $field );

				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$field['id']}-description" )
										 ->text( $field['desc'] )
										 ->getHTML();

				$out .= Field\Checkbox_Group::create()
											->setId( $name )
											->addClass( 'checkbox' )
											->setName( $name )
											->createInputsFromArray( $field['options'] )
											// ->addAttribute( 'aria-describedby', "{$field['id']}-description" )
											->setValue( $value )
											->getHTML();

				break;

			case 'number':
				$sizes = array( 'small', 'regular', 'large' );
				$size  = _array::get( $field, 'size', 'regular' );

				$out .= Field\Number::create()
									->setId( $name )
									->addClass(
										in_array( $size, $sizes ) ? "{$size}-text" : 'regular-text'
									)
									->setName( $name )
									->setDefaultValue( _array::get( $field, 'default', '' ) )
									->setValue( $value )
									->addAttribute( 'aria-describedby', "{$name}-description" )
									->getHTML();

				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$name}-description" )
										 ->text( $field['desc'] )
										 ->setTag( 'span' )
										 ->getHTML();

				break;

			/*
			 * Renders a checkbox group with the registered CPT/s.
			 * The options are set here rather than when registering the setting because settings are registered
			 * before CPT/s are registered with WordPress so `get_post_types()` would return `NULL`.
			 *
			 * Once the CTP options are set, this simply loops back and calls itself with the `checkbox-group`
			 * field type.
			 */
			case 'cpt-checkbox-group':
				$postTypes = get_post_types(
					array(
						'public'             => true,
						'publicly_queryable' => true,
						'rewrite'            => true,
						'show_in_menu'       => true,
						'_builtin'           => false,
					),
					'objects'
				);

				$postTypeOptions = array();

				foreach ( $postTypes as $type ) {

					$postTypeOptions[ $type->name ] = $type->labels->name;
				}

				$field['type']    = 'checkbox-group';
				$field['options'] = $postTypeOptions;

				self::field( $field );

				break;

			case 'radio':
				remapFieldOptions( $field );

				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$field['id']}-description" )
										 ->text( $field['desc'] )
										 ->getHTML();

				$out .= Field\Radio_Group::create()
										 ->setId( $name )
										 ->addClass( 'radio' )
										 ->setName( $name )
										 ->createInputsFromArray( $field['options'] )
										 // ->addAttribute( 'aria-describedby', "{$field['id']}-description" )
										 ->setValue( $value )
										 ->getHTML();

				break;

			case 'select':
				remapFieldOptions( $field );

				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$field['id']}-description" )
										 ->text( $field['desc'] )
										 ->getHTML();

				$out .= Field\Select::create()
									->setId( $name )
									->setName( $name )
									->createOptionsFromArray( $field['options'] )
									// ->addAttribute( 'aria-describedby', "{$field['id']}-description" )
									->setValue( $value )
									->getHTML();

				break;

			case 'multiselect':
				if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
					$out .= sprintf( '<span class="description">%s</span><br />', $field['desc'] );
				}

				$out .= '<span style="background-color: white; border-color: #DFDFDF; border-radius: 3px; border-width: 1px; border-style: solid; display: block; height: 90px; padding: 0 3px; overflow: auto; width: 25em;">';

				foreach ( $field['options'] as $key => $label ) {
					$checked = checked( true, ( is_array( $value ) ) ? ( in_array( $key, $value ) ) : ( $key == $value ), false );

					$out .= sprintf( '<label><input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[]" value="%2$s" %3$s/> %4$s</label><br />', $name, $key, $checked, $label );
				}

				$out .= '</span>';

				break;

			case 'text':
				$sizes = array( 'small', 'regular', 'large' );
				$size  = _array::get( $field, 'size', 'regular' );

				$out .= Field\Text::create()
								  ->setId( $name )
								  ->addClass( in_array( $size, $sizes ) ? "{$size}-text" : 'regular-text' )
								  ->setName( $name )
								  ->setDefaultValue( _array::get( $field, 'default', '' ) )
								  ->setValue( $value )
								  ->addAttribute( 'aria-describedby', "{$name}-description" )
								  ->getHTML();

				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$name}-description" )
										 ->text( $field['desc'] )
										 ->setTag( 'span' )
										 ->getHTML();

				break;

			case 'textarea':
				$sizes = array( 'small', 'large' );
				$size  = _array::get( $field, 'size', 'small' );

				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$name}-description" )
										 ->text( $field['desc'] )
										 ->setTag( 'p' )
										 ->getHTML();

				$out .= Field\Textarea::create()
									  ->setId( $name )
									  ->addClass( in_array( $size, $sizes ) ? "{$size}-text" : 'small-text' )
									  ->setName( $name )
									  ->addAttribute( 'rows', 10 )
									  ->addAttribute( 'cols', 50 )
									  ->setValue( $value )
									  ->getHTML();
				break;

			case 'quicktag':
				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$name}-description" )
										 ->text( $field['desc'] )
										 ->setTag( 'p' )
										 ->getHTML();

				$out . Field\Quicktag::create()
									 ->setId( $name )
									 ->setValue( $value )
									 ->getHTML();

				break;

			case 'rte':
				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->text( $field['desc'] )
										 ->setTag( 'div' )
										 ->getHTML();

				$out .= Field\Rich_Text::create()
									   ->setId( $field['id'] )
									   ->setName( $name )
									   ->setPrefix( 'cn' )
									   ->rteSettings( _array::get( $field, 'options', array() ) )
									   ->setValue( $value )
									   ->getHTML();

				break;

			case 'page':
				$out .= wp_dropdown_pages(
					array(
						'name'              => esc_html( $name ),
						'echo'              => 0,
						'show_option_none'  => esc_html( $field['show_option_none'] ),
						'option_none_value' => esc_html( $field['option_none_value'] ),
						'selected'          => absint( $value ),
					)
				);

				break;

			case 'cpt-pages':
				$defaults = array(
					'depth'                 => 0,
					'child_of'              => 0,
					'selected'              => $value,
					'echo'                  => 0,
					'name'                  => $name,
					'id'                    => '',
					'class'                 => '',
					'show_option_none'      => '',
					'show_option_no_change' => '',
					'option_none_value'     => '',
					'value_field'           => 'ID',
				);

				$atts = wp_parse_args( $field, $defaults );

				$postTypes = get_post_types(
					array(
						'public'       => true,
						'show_in_menu' => true,
					),
					'objects'
				);

				$class = ( ! empty( $atts['class'] ) ) ? " class='" . esc_attr( $atts['class'] ) . "'" : '';

				$select = "<select name='" . esc_attr( $atts['name'] ) . "'" . $class . " id='" . esc_attr( $atts['id'] ) . "'>\n";

				if ( $atts['show_option_no_change'] ) {

					$select .= "\t<option value=\"-1\">" . $atts['show_option_no_change'] . "</option>\n";
				}

				if ( $atts['show_option_none'] ) {

					$select .= "\t<option value=\"" . esc_attr( $atts['option_none_value'] ) . '">' . $atts['show_option_none'] . "</option>\n";
				}

				foreach ( $postTypes as $type ) {

					if ( in_array( $type->name, $atts['options']['exclude_cpt'] ) ) {

						continue;
					}

					if ( ! in_array( $type->name, $atts['options']['include_cpt'] ) ) {

						continue;
					}

					$select .= '<optgroup label="' . esc_attr( $type->labels->name ) . '">' . PHP_EOL;

					$atts['post_type'] = $type->name;
					$posts             = get_pages( $atts );

					if ( ! empty( $posts ) ) {

						$select .= walk_page_dropdown_tree( $posts, $atts['depth'], $atts );
					}

					$select .= '</optgroup>' . PHP_EOL;
				}

				$select .= '</select>' . PHP_EOL;

				$out = $select;

				break;

			case 'category':
				$out .= cnTemplatePart::walker(
					'term-select',
					array(
						'hide_empty'    => 0,
						'hide_if_empty' => false,
						'name'          => $name,
						'orderby'       => 'name',
						'taxonomy'      => 'category',
						'selected'      => $value,
						'hierarchical'  => true,
						'return'        => true,
					)
				);

				break;

			case 'sortable_checklist':
				// This will be used to store the order of the content blocks.
				$blocks = array();

				if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

					$out .= sprintf(
						'<p class="description"> %1$s</p>',
						wp_kses_post( $field['desc'] )
					);
				}

				$out .= sprintf(
					'<ul class="cn-sortable-checklist" id="%1$s">',
					esc_attr( $name )
				);

				// Create the array to be used to render the output in the correct order.
				// This will have to take into account content blocks being added and removed.
				// ref: http://stackoverflow.com/a/9098675
				if ( isset( $value['order'] ) && ! empty( $value['order'] ) ) {

					$order = array();

					// Remove any content blocks that no longer exist.
					$blocks = array_intersect_key( $field['options']['items'], array_flip( $value['order'] ) );

					// Add back in any new content blocks.
					$blocks = array_merge( $blocks, $field['options']['items'] );

					foreach ( $value['order'] as $key ) {

						if ( isset( $blocks[ $key ] ) ) {
							$order[] = $key;
						}
					}

					// Order the array as the user has defined in $value['order'].
					$blocks = array_merge( array_flip( $order ), $blocks );

				} else {

					// No order was set or saved yet, so use the field options order.
					$blocks = $field['options']['items'];
				}

				foreach ( $blocks as $key => $label ) {

					if ( isset( $field['options']['required'] ) && in_array( $key, $field['options']['required'] ) ) {

						$checkbox = cnHTML::input(
							array(
								'type'     => 'checkbox',
								'prefix'   => '',
								'id'       => esc_attr( $name ) . '[active][' . $key . ']',
								'name'     => esc_attr( $name ) . '[active][]',
								'checked'  => isset( $value['active'] ) ? checked( true, ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ), false ) : '',
								'disabled' => true,
								'label'    => $label,
								'layout'   => '%field%%label%',
								'return'   => true,
							),
							$key
						);

						$checkbox .= cnHTML::input(
							array(
								'type'    => 'hidden',
								'prefix'  => '',
								'id'      => esc_attr( $name ) . '[active][' . $key . ']',
								'name'    => esc_attr( $name ) . '[active][]',
								'checked' => isset( $value['active'] ) ? checked( true, ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ), false ) : '',
								'layout'  => '%field%',
								'return'  => true,
							),
							$key
						);

					} else {

						$checkbox = cnHTML::input(
							array(
								'type'    => 'checkbox',
								'prefix'  => '',
								'id'      => esc_attr( $name ) . '[active][' . $key . ']',
								'name'    => esc_attr( $name ) . '[active][]',
								'checked' => isset( $value['active'] ) ? checked( true, ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ), false ) : '',
								'label'   => $label,
								'layout'  => '%field%%label%',
								'return'  => true,
							),
							$key
						);
					}

					$hidden = cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'id'     => esc_attr( $name ) . '[order][' . $key . ']',
							'name'   => esc_attr( $name ) . '[order][]',
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						$key
					);

					$out .= sprintf(
						'<li value="%1$s"><i class="fa fa-sort"></i> %2$s%3$s</li>',
						$key,
						$hidden,
						$checkbox
					);
				}

				$out .= '</ul>';

				// Add the list to the sortable IDs.
				self::$sortableIDs[] = $name;

				// Add the script to the admin footer.
				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'sortableJS' ) );

				// Enqueue the jQuery UI Sortable Library.
				wp_enqueue_script( 'jquery-ui-sortable' );

				break;

			case 'sortable_input-repeatable':
			case 'sortable_input':
				// This will be used to store the order of the content blocks.
				$blocks = array();

				if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

					$out .= sprintf(
						'<p class="description"> %1$s</p>',
						esc_html( $field['desc'] )
					);
				}

				$out .= sprintf(
					'<ul class="cn-sortable-input%1$s" id="%2$s">',
					'sortable_input-repeatable' === $field['type'] ? '-repeatable' : '',
					esc_attr( $name )
				);

				// Create the array to be used to render the output in the correct order.
				// This will have to take into account content blocks being added and removed.
				// ref: http://stackoverflow.com/a/9098675
				if ( isset( $value['order'] ) && ! empty( $value['order'] ) ) {

					$order = array();

					// Remove any content blocks that no longer exist.
					// $blocks = array_intersect_key( $value['type'], array_flip( $value['order'] ) );
					$blocks = array_intersect_key(
						array_merge(
							$field['options']['items'],
							cnArray::get( $value, 'type', array() )
						),
						array_flip( $value['order'] )
					);

					// Add back in any new content blocks.
					$blocks = array_merge( $blocks, $field['options']['items'] );

					foreach ( $value['order'] as $key ) {

						if ( isset( $blocks[ $key ] ) ) {
							$order[] = $key;
						}
					}

					// Order the array as the user has defined in $value['order'].
					$blocks = array_replace( array_flip( $order ), $blocks );

				} else {

					// No order was set or saved yet, so use the field options order.
					$blocks = $field['options']['items'];
				}

				foreach ( $blocks as $key => $label ) {

					$removeButton = '';
					$checkbox     = '';
					$input        = '';
					$hidden       = '';

					if ( isset( $field['options']['required'] ) && in_array( $key, $field['options']['required'] ) ) {

						$checkbox = cnHTML::input(
							array(
								'type'     => 'checkbox',
								'prefix'   => '',
								'id'       => esc_attr( $name ) . '[active][' . $key . ']',
								'name'     => esc_attr( $name ) . '[active][]',
								'checked'  => 'checked="checked"',
								'disabled' => true,
								// 'label'   => $label,
								'layout'   => '%field%',
								'return'   => true,
							),
							$key
						);

						$checkbox .= cnHTML::input(
							array(
								'type'    => 'hidden',
								'prefix'  => '',
								'id'      => esc_attr( $name ) . '[active][' . $key . ']',
								'name'    => esc_attr( $name ) . '[active][]',
								'checked' => isset( $value['active'] ) ? checked( true, ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ), false ) : '',
								'layout'  => '%field%',
								'return'  => true,
							),
							$key
						);

						$input = cnHTML::input(
							array(
								'type'     => 'text',
								'prefix'   => '',
								'id'       => esc_attr( $name ) . '[type][' . $key . ']',
								'name'     => esc_attr( $name ) . '[type][' . $key . ']',
								// 'checked' => isset( $value['active'] ) ? checked( TRUE , ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ) , FALSE ) : '',
								// 'label'   => $label,
								'disabled' => true,
								'data'     => array_key_exists( $key, $field['options']['items'] ) ? array( 'registered' => 1 ) : array( 'custom' => 1 ),
								'layout'   => '%field%',
								'return'   => true,
							),
							sanitize_text_field( isset( $value['type'][ $key ] ) ? $value['type'][ $key ] : $field['options']['items'][ $key ] )
						);

					} else {

						$checkbox = cnHTML::input(
							array(
								'type'    => 'checkbox',
								'prefix'  => '',
								'id'      => esc_attr( $name ) . '[active][' . $key . ']',
								'name'    => esc_attr( $name ) . '[active][]',
								'checked' => isset( $value['active'] ) ? checked( true, ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ), false ) : '',
								'label'   => $label,
								'layout'  => '%field%',
								'return'  => true,
							),
							$key
						);

						$input = cnHTML::input(
							array(
								'type'   => 'text',
								'prefix' => '',
								'id'     => esc_attr( $name ) . '[type][' . $key . ']',
								'name'   => esc_attr( $name ) . '[type][' . $key . ']',
								// 'checked' => isset( $value['active'] ) ? checked( TRUE , ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ) , FALSE ) : '',
								// 'label'   => $label,
								'data'   => array_key_exists( $key, $field['options']['items'] ) ? array( 'registered' => 1 ) : array( 'custom' => 1 ),
								'layout' => '%field%',
								'return' => true,
							),
							sanitize_text_field( isset( $value['type'][ $key ] ) ? $value['type'][ $key ] : $field['options']['items'][ $key ] )
						);
					}

					$hidden = cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'id'     => esc_attr( $name ) . '[order][' . $key . ']',
							'name'   => esc_attr( $name ) . '[order][]',
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						$key
					);

					if ( 'sortable_input-repeatable' === $field['type'] ) {

						$removeButton = '<a href="#" class="cn-remove cn-button button">' . esc_html__( 'Remove', 'connections' ) . '</a>';
					}

					$row = sprintf(
						'<li><i class="fa fa-sort"></i> %1$s%2$s%3$s %4$s</li>',
						$hidden,
						$checkbox,
						$input,
						! array_key_exists( $key, $field['options']['items'] ) ? $removeButton : ''
					);

					$row = apply_filters(
						"cn_settings_field-{$field['type']}-item",
						$row,
						compact(
							'field',
							'key',
							'label',
							'hidden',
							'checkbox',
							'input',
							'removeButton'
						)
					);

					$out .= $row;
				}

				if ( 'sortable_input-repeatable' === $field['type'] ) {

					$out .= '<li><a href="#" class="cn-add cn-button button">' . esc_html__( 'Add', 'connections' ) . '</a></li>';
				}

				$out .= '</ul>';

				// Add the list to the sortable IDs.
				self::$sortableIDs[] = $name;

				// Add the script to the admin footer.
				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'sortableJS' ) );

				wp_enqueue_script( 'cn-setting-sortable-repeatable-input-list' );

				break;

			case 'sortable_iconpicker':
			case 'sortable_iconpicker-repeatable':
				// This will be used to store the order of the content blocks.
				$blocks = array();

				if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {

					$out .= sprintf(
						'<p class="description"> %1$s</p>' . PHP_EOL,
						esc_html( $field['desc'] )
					);
				}

				$out .= sprintf(
					'<ul class="cn-sortable-input%1$s cn-fieldset-social-networks" id="%2$s">',
					'sortable_iconpicker-repeatable' === $field['type'] ? '-repeatable' : '',
					esc_attr( $name )
				);

				$out = $out . PHP_EOL;

				// Add the template/token item to be used for cloning when adding a new item by the user.
				$field['options']['items'] = array( '%token%' => '%template%' ) + $field['options']['items'];

				// Create the array to be used to render the output in the correct order.
				// This will have to take into account content blocks being added and removed.
				// ref: http://stackoverflow.com/a/9098675
				if ( isset( $value['order'] ) && ! empty( $value['order'] ) ) {

					$order = array();

					// Remove any content blocks that no longer exist.
					// $blocks = array_intersect_key( $value['type'], array_flip( $value['order'] ) );
					$blocks = array_intersect_key(
						array_merge(
							$field['options']['items'],
							cnArray::get( $value, 'icon', array() )
						),
						array_flip( $value['order'] )
					);

					// Add back in any new content blocks.
					$blocks = array_merge( $blocks, $field['options']['items'] );

					foreach ( $value['order'] as $key ) {

						if ( isset( $blocks[ $key ] ) ) {
							$order[] = $key;
						}
					}

					// Order the array as the user has defined in $value['order'].
					$blocks = array_replace( array_flip( $order ), $blocks );

				} else {

					// No order was set or saved yet, so use the field options order.
					$blocks = $field['options']['items'];
				}

				// $blocks['%token%'] = 'template';
				// $blocks = array( '%token%' => '%template%') + $blocks;

				foreach ( $blocks as $key => $label ) {

					$removeButton = '';
					$checkbox     = '';
					$input        = '';
					$hidden       = '';

					/*
					 * Custom items the label is actually an array of the font icon meta,
					 * pull the name from the meta and set the label var. If it is not an
					 * array, label is assumed to be string and returned as teh default value for label.
					 */
					$label = cnArray::get( $label, 'name', $label );

					/**
					 * @todo The required section of code is very, very broken.
					 */
					if ( isset( $field['options']['required'] ) && in_array( $key, $field['options']['required'] ) ) {

						$checkbox = cnHTML::input(
							array(
								'type'     => 'checkbox',
								'prefix'   => '',
								'id'       => esc_attr( $name ) . '[active][' . $key . ']',
								'name'     => esc_attr( $name ) . '[active][]',
								'checked'  => 'checked="checked"',
								'disabled' => true,
								// 'label'   => $label,
								'layout'   => '%field%',
								'return'   => true,
							),
							$key
						);

						$checkbox .= cnHTML::input(
							array(
								'type'    => 'hidden',
								'prefix'  => '',
								'id'      => esc_attr( $name ) . '[active][' . $key . ']',
								'name'    => esc_attr( $name ) . '[active][]',
								'checked' => isset( $value['active'] ) ? checked( true, ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ), false ) : '',
								'layout'  => '%field%',
								'return'  => true,
							),
							$key
						);

						$input = cnHTML::input(
							array(
								'type'     => 'text',
								'prefix'   => '',
								'id'       => esc_attr( $name ) . '[type][' . $key . ']',
								'name'     => esc_attr( $name ) . '[type][' . $key . ']',
								// 'checked' => isset( $value['active'] ) ? checked( TRUE , ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ) , FALSE ) : '',
								// 'label'   => $label,
								'disabled' => true,
								'data'     => array_key_exists( $key, $field['options']['items'] ) ? array( 'registered' => 1 ) : array( 'custom' => 1 ),
								'layout'   => '%field%',
								'return'   => true,
							),
							sanitize_text_field( isset( $value['type'][ $key ] ) ? $value['type'][ $key ] : $field['options']['items'][ $key ] )
						);

					} else {

						$checkbox = cnHTML::input(
							array(
								'type'    => 'checkbox',
								'prefix'  => '',
								'id'      => esc_attr( $name ) . '[active][' . $key . ']',
								'name'    => esc_attr( $name ) . '[active][]',
								'data'    => array(
									'id'   => esc_attr( $name ) . '[active][%token%]',
									'name' => esc_attr( $name ) . '[active][]',
								),
								'checked' => isset( $value['active'] ) ? checked( true, ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ), false ) : '',
								'label'   => $label,
								'layout'  => '%field%',
								'return'  => true,
							),
							$key
						);

						$input = cnHTML::input(
							array(
								'type'   => 'text',
								'prefix' => '',
								'id'     => esc_attr( $name ) . '[icon][' . $key . '][name]',
								'name'   => esc_attr( $name ) . '[icon][' . $key . '][name]',
								// 'checked' => isset( $value['active'] ) ? checked( TRUE , ( is_array( $value['active'] ) ) ? ( in_array( $key, $value['active'] ) ) : ( $key == $value['active'] ) , FALSE ) : '',
								// 'label'   => $label,
								// 'data'    => array_key_exists( $key, $field['options']['items'] ) ? array( 'registered' => 1 ) : array( 'custom' => 1 ),
								'data'   => array(
									'id'         => esc_attr( $name ) . '[icon][%token%][name]',
									'name'       => esc_attr( $name ) . '[icon][%token%][name]',
									'custom'     => ! array_key_exists( $key, $field['options']['items'] ) ? 1 : 0,
									'registered' => array_key_exists( $key, $field['options']['items'] ) ? 1 : 0,
								),
								'layout' => '%field%',
								'return' => true,
							),
							sanitize_text_field( isset( $value['icon'][ $key ]['name'] ) ? $value['icon'][ $key ]['name'] : $field['options']['items'][ $key ] )
						);
					}

					// Default to the RSS icon when adding a new social network.
					$iconClass = '%token%' === $key ? 'rss' : $key;

					/**
					 * Need to add type="button" to <button> otherwise the first button or input with type="submit"
					 * is what is triggered. If you specifically set type="button", then it's removed from
					 * consideration by the browser.
					 *
					 * @link https://stackoverflow.com/a/12914700/5351316
					 */
					$iconButton = sprintf(
						'<a class="cn-social-network-icon-setting-button"><i class="cn-brandicon-%1$s cn-brandicon-size-24"></i></a>',
						sanitize_text_field( isset( $value['icon'][ $key ]['slug'] ) ? $value['icon'][ $key ]['slug'] : $iconClass )
					);

					$hidden = cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'id'     => esc_attr( $name ) . '[order][' . $key . ']',
							'name'   => esc_attr( $name ) . '[order][]',
							'data'   => array(
								'id'   => esc_attr( $name ) . '[order][%token%]',
								'name' => esc_attr( $name ) . '[order][]',
							),
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						$key
					);

					$hidden .= cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'class'  => array( 'cn-brandicon' ),
							'id'     => esc_attr( $name ) . '[icon][' . $key . '][slug]',
							'name'   => esc_attr( $name ) . '[icon][' . $key . '][slug]',
							'data'   => array(
								'id  ' => esc_attr( $name ) . '[icon][%token%][slug]',
								'name' => esc_attr( $name ) . '[icon][%token%][slug]',
							),
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						sanitize_text_field( isset( $value['icon'][ $key ]['slug'] ) ? $value['icon'][ $key ]['slug'] : $key )
					);

					$hidden .= cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'class'  => array( 'cn-brandicon-background-color' ),
							'id'     => esc_attr( $name ) . '[icon][' . $key . '][background-color]',
							'name'   => esc_attr( $name ) . '[icon][' . $key . '][background-color]',
							'data'   => array(
								'id  ' => esc_attr( $name ) . '[icon][%token%][background-color]',
								'name' => esc_attr( $name ) . '[icon][%token%][background-color]',
							),
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						sanitize_text_field( isset( $value['icon'][ $key ]['background-color'] ) ? $value['icon'][ $key ]['background-color'] : '' )
					);

					$hidden .= cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'class'  => array( 'cn-brandicon-hover-background-color' ),
							'id'     => esc_attr( $name ) . '[icon][' . $key . '][background-color-hover]',
							'name'   => esc_attr( $name ) . '[icon][' . $key . '][background-color-hover]',
							'data'   => array(
								'id'   => esc_attr( $name ) . '[icon][%token%][background-color-hover]',
								'name' => esc_attr( $name ) . '[icon][%token%][background-color-hover]',
							),
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						sanitize_text_field( isset( $value['icon'][ $key ]['background-color-hover'] ) ? $value['icon'][ $key ]['background-color-hover'] : '' )
					);

					$hidden .= cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'class'  => array( 'cn-brandicon-background-transparent' ),
							'id'     => esc_attr( $name ) . '[icon][' . $key . '][background-transparent]',
							'name'   => esc_attr( $name ) . '[icon][' . $key . '][background-transparent]',
							'data'   => array(
								'id'   => esc_attr( $name ) . '[icon][%token%][background-transparent]',
								'name' => esc_attr( $name ) . '[icon][%token%][background-transparent]',
							),
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						sanitize_text_field( isset( $value['icon'][ $key ]['background-transparent'] ) ? $value['icon'][ $key ]['background-transparent'] : '' )
					);

					$hidden .= cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'class'  => array( 'cn-brandicon-foreground-color' ),
							'id'     => esc_attr( $name ) . '[icon][' . $key . '][foreground-color]',
							'name'   => esc_attr( $name ) . '[icon][' . $key . '][foreground-color]',
							'data'   => array(
								'id  ' => esc_attr( $name ) . '[icon][%token%][foreground-color]',
								'name' => esc_attr( $name ) . '[icon][%token%][foreground-color]',
							),
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						sanitize_text_field( isset( $value['icon'][ $key ]['foreground-color'] ) ? $value['icon'][ $key ]['foreground-color'] : '' )
					);

					$hidden .= cnHTML::input(
						array(
							'type'   => 'hidden',
							'prefix' => '',
							'class'  => array( 'cn-brandicon-hover-foreground-color' ),
							'id'     => esc_attr( $name ) . '[icon][' . $key . '][foreground-color-hover]',
							'name'   => esc_attr( $name ) . '[icon][' . $key . '][foreground-color-hover]',
							'data'   => array(
								'id'   => esc_attr( $name ) . '[icon][%token%][foreground-color-hover]',
								'name' => esc_attr( $name ) . '[icon][%token%][foreground-color-hover]',
							),
							'label'  => '',
							'layout' => '%field%',
							'return' => true,
						),
						sanitize_text_field( isset( $value['icon'][ $key ]['foreground-color-hover'] ) ? $value['icon'][ $key ]['foreground-color-hover'] : '' )
					);

					if ( 'sortable_iconpicker-repeatable' === $field['type'] ) {

						$removeButton = '<a href="#" class="cn-remove cn-button button">' . esc_html__( 'Remove', 'connections' ) . '</a>';
					}

					$row = sprintf(
						'<li><i class="fa fa-sort"></i> %1$s%2$s %3$s %4$s %5$s</li>',
						$hidden,
						$checkbox,
						$iconButton,
						$input,
						! array_key_exists( $key, $field['options']['items'] ) ? $removeButton : ''
					);

					$row = apply_filters(
						"cn_settings_field-{$field['type']}-item",
						$row,
						compact(
							'field',
							'key',
							'label',
							'hidden',
							'checkbox',
							'iconButton',
							'input',
							'removeButton'
						)
					);

					if ( '%template%' === $label ) {

						$row = "<li><template>{$row}</template></li>";
					}

					$out .= $row . PHP_EOL;
				}

				if ( 'sortable_iconpicker-repeatable' === $field['type'] ) {

					$out .= '<li><a href="#" class="cn-add cn-button button">' . esc_html__( 'Add', 'connections' ) . '</a></li>';
				}

				$out .= '</ul>' . PHP_EOL;

				// Add the list to the sortable IDs.
				self::$sortableIDs[] = $name;

				// Add the script to the admin footer.
				add_action( 'admin_footer', array( __CLASS__, 'socialMediaIconOptions' ) );
				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'sortableJS' ) );

				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );

				wp_enqueue_script( 'cn-setting-sortable-repeatable-input-list' );
				wp_enqueue_script( 'cn-icon-picker' );

				break;

			case 'colorpicker':
				$out .= Field\Description::create()
										 ->addClass( 'description' )
										 ->setId( "{$field['id']}-description" )
										 ->text( $field['desc'] )
										 ->getHTML();

				$out .= Field\Color_Picker::create()
										  ->setId( $name )
										  ->setName( $name )
										  ->setReadOnly( isset( $field['readonly'] ) && true === $field['readonly'] )
										  ->setValue( $value )
										  ->getHTML();

				break;

			default:
				ob_start();

				do_action( 'cn_settings_field-' . $field['type'], $name, $value, $field );

				$out .= ob_get_clean();

				break;
		}

		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Outputs the JS necessary to make a checkbox list sortable using the jQuery UI Sortable library.
	 *
	 * @access private
	 * @since  0.8
	 */
	public static function sortableJS() {

		echo '<script type="text/javascript">/* <![CDATA[ */' . PHP_EOL;
			echo 'jQuery(function($) {' . PHP_EOL;

				foreach ( self::$sortableIDs as $id ) {

					echo '$(\'[id="' . esc_js( $id ) . '"]\').sortable();' . PHP_EOL;
				}

			echo '});' . PHP_EOL;
		echo '/* ]]> */</script>';
	}

	/**
	 * Callback for the `admin_footer` action.
	 *
	 * Outputs the HTML to support the font icon picker settings for the social networks.
	 *
	 * @since 8.44
	 */
	public static function socialMediaIconOptions() {

		?>
		<div id="cn-social-network-icon-settings-modal" style="box-sizing: border-box; display: none; max-width:800px; min-height: 600px; min-width: 386px;">
			<p>
				<label for="e9_element">Choose an icon:</label>
			</p>
			<p>
				<input type="text" id="e9_element" name="e9_element" />
			</p>
			<p>
				<label for="cn-icon-background-color">Choose the icon background color:</label>
			</p>
			<p>
				<input type="text" class="cn-icon-colorpicker" id="cn-icon-background-color" />
			</p>
			<p>
				<label for="cn-icon-hover-background-color">Choose the icon background hover color:</label>
			</p>
			<p>
				<input type="text" class="cn-icon-colorpicker" id="cn-icon-hover-background-color" />
			</p>
			<p>
				<input type="checkbox" class="checkbox" id="cn-icon-background-transparent" value="" /> <label for="cn-icon-background-transparent">Force icon background colors to be transparent. Enabling this option will override the colors set for the background and background hover colors.</label>
			</p>
			<p>
				<label for="cn-icon-foreground-color">Choose the icon foreground color:</label>
			</p>
			<p>
				<input type="text" class="cn-icon-colorpicker" id="cn-icon-foreground-color" />
			</p>
			<p>
				<label for="cn-icon-hover-foreground-color">Choose the icon foreground hover color:</label>
			</p>
			<p>
				<input type="text" class="cn-icon-colorpicker" id="cn-icon-hover-foreground-color" />
			</p>
		</div>
		<?php
	}

	/**
	 * Return all the settings for a specific plugin that was registered using this API.
	 * The optional parameters can be used to return a specific settings section or a
	 * specific option from within a section.
	 *
	 * @access public
	 * @since  0.7.3.0
	 * @static
	 *
	 * @param string $pluginID The plugin_id the settings field was registered to.
	 * @param string $section  [optional] The section id the settings field was registered to.
	 * @param string $option   [optional] The settings field id that was used to register the option.
	 *
	 * @return mixed
	 */
	public static function get( $pluginID, $section = '', $option = '' ) {

		$settings = array();
		// var_dump($this->registry[$pluginID]);

		// Return all the specified plugin options registered using this API.
		if ( array_key_exists( $pluginID, self::$registry ) ) {
			/*
			 * Since checkboxes are not returned if unchecked when submitting a form,
			 * the fields are/can not be saved. This basically traverses the registered
			 * settings array and adds them back into the options retrieved from the db via
			 * get_option() the missing key with an empty value. Using an empty value
			 * should be safe for the other field types too since that mimics the WP Settings API.
			 */
			foreach ( self::$registry[ $pluginID ] as $optionName => $values ) {

				// TRUE and FALSE should be stored as 1 and 0 in the db so get_option must be strictly compared.
				if ( false !== get_option( $optionName ) ) {

					$settings[ $optionName ] = get_option( $optionName );

					if ( is_array( self::$registry[ $pluginID ][ $optionName ] ) ) {

						foreach ( self::$registry[ $pluginID ][ $optionName ] as $key => $value ) {

							if ( ! isset( $settings[ $optionName ][ $key ] ) || empty( $settings[ $optionName ][ $key ] ) ) {

								if ( ! isset( $settings[ $optionName ] ) || ! is_array( $settings[ $optionName ] ) ) {

									$settings[ $optionName ] = array( $key => '' );

								} else {

									$settings[ $optionName ][ $key ] = '';
								}

							}
						}

					} elseif ( ! isset( $settings[ $optionName ] ) || empty( $settings[ $optionName ] ) ) {

						$settings[ $optionName ] = '';
					}

				} else {

					return false;
				}
			}

		} else {

			return false;
		}

		if ( ! empty( $section ) ) {

			if ( substr( $section, 0, strlen( $pluginID ) ) !== $pluginID ) {
				$section = $pluginID . '_' . $section;
			}

			if ( array_key_exists( $section, $settings ) ) {
				if ( ! empty( $option ) ) {
					if ( array_key_exists( $option, $settings[ $section ] ) ) {
						return $settings[ $section ][ $option ];
					} else {
						return false;
					}
				} else {
					return $settings[ $section ];
				}
			} else {
				return false;
			}
		}

		return $settings;
	}

	/**
	 * Set an option.
	 *
	 * NOTE: This is no finished and should not be usd yet.
	 *
	 * @todo Finish this method.
	 *
	 * @access public
	 * @since  8.3.3
	 * @static
	 *
	 * @param string $pluginID
	 * @param string $section
	 * @param string $option
	 * @param mixed  $value
	 */
	public static function set( $pluginID, $section, $option, $value ) {

		$optionName = "{$pluginID}_{$section}";

		if ( false !== $result = get_option( $optionName ) ) {

			if ( is_array( $result ) ) {

				$result[ $option ] = $value;

			} else {

				$result = $value;
			}

			update_option( $optionName, $result );
		}
	}

	/**
	 * Returns all the settings registered through this API.
	 *
	 * @since 8.3
	 * @since 10.4.62 Make method public.
	 *
	 * @return array
	 */
	public static function getAll() {

		$plugins  = array_keys( self::$registry );
		$settings = array();

		foreach ( $plugins as $id ) {

			$settings[ $id ] = self::get( $id );
		}

		return $settings;
	}

	/**
	 * Reset all the settings to the registered default values
	 * for a specific plugin that was registered using this API.
	 *
	 * @since 0.7.3.0
	 *
	 * @param string $pluginID
	 */
	public static function reset( $pluginID ) {

		if ( array_key_exists( $pluginID, self::$registry ) ) {

			foreach ( self::$registry[ $pluginID ] as $optionName => $values ) {

				update_option( $optionName, $values );
			}
		}
	}

	/**
	 * Delete all the settings for a specific plugin that was registered using this API.
	 *
	 * @since 0.7.3.0
	 *
	 * @param string $pluginID
	 */
	public static function delete( $pluginID ) {

		if ( array_key_exists( $pluginID, self::$registry ) ) {

			foreach ( self::$registry[ $pluginID ] as $optionName => $values ) {

				delete_option( $optionName );
			}
		}
	}

	/**
	 * Downloads all setting register through this API to a JSON encoded text file.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function download() {

		$filename = apply_filters(
			'cn_settings_export_filename',
			'cn-settings-export-' . current_time( 'Y-m-d_H-i-s' )
		);

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename . '.json' );
		header( 'Expires: 0' );

		echo json_encode( self::getAll() );
		exit;
	}

	/**
	 * Import settings from a JSON encoded string.
	 *
	 * @since 8.3
	 *
	 * @param string $json The JSON settings to import.
	 *
	 * @return true|string
	 */
	public static function import( $json ) {

		$result = _::decodeJSON( $json, true );

		if ( is_wp_error( $result ) ) {

			return $result->get_error_message( 'json_decode_error' );
		}

		foreach ( $result as $pluginID => $options ) {

			foreach ( $options as $id => $value ) {

				update_option( $id, $value );
			}
		}

		return true;
	}
}
