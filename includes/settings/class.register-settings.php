<?php
/**
 * Register the tabs, settings sections and section settings using the Settings API.
 *
 * @package     Connections
 * @subpackage  Manage the settings.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.3.0
 */

use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_sanitize;
use Connections_Directory\Utility\_string;
use Connections_Directory\Utility\_validate;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnRegisterSettings
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnRegisterSettings {

	/**
	 * Register the tabs for the Connections : Settings admin page.
	 *
	 * @since 0.7.3.0
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public static function registerSettingsTabs( $tabs ) {

		$settings = 'connections_page_connections_settings';

		// Register the core tab banks.
		$tabs[] = array(
			'id'        => 'general',
			'position'  => 10,
			'title'     => __( 'General', 'connections' ),
			'page_hook' => $settings,
		);

		$tabs[] = array(
			'id'        => 'display',
			'position'  => 15,
			'title'     => __( 'Display', 'connections' ),
			'page_hook' => $settings,
		);

		$tabs[] = array(
			'id'        => 'field-configuration',
			'position'  => 18,
			'title'     => __( 'Fieldset Configuration', 'connections' ),
			'page_hook' => $settings,
		);

		$tabs[] = array(
			'id'        => 'images',
			'position'  => 20,
			'title'     => __( 'Images', 'connections' ),
			'page_hook' => $settings,
		);

		$tabs[] = array(
			'id'        => 'search',
			'position'  => 30,
			'title'     => __( 'Search', 'connections' ),
			'page_hook' => $settings,
		);

		$tabs[] = array(
			'id'        => 'seo',
			'position'  => 40,
			'title'     => __( 'SEO', 'connections' ),
			'page_hook' => $settings,
		);

		$tabs[] = array(
			'id'        => 'advanced',
			'position'  => 60,
			'title'     => __( 'Advanced', 'connections' ),
			'page_hook' => $settings,
		);

		return $tabs;
	}

	/**
	 * Register the settings sections.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.3.0
	 * @param array $sections
	 * @return array
	 */
	public static function registerSettingsSections( $sections ) {

		$settings = 'connections_page_connections_settings';

		/*
		 * The sections registered to the General tab.
		 */
		$sections[] = array(
			'tab'          => 'general',
			'id'           => 'connections_home_page',
			'position'     => 5,
			'title'        => __( 'Home', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Choose the page where your directory is located. This should be the page where you used the &#91;connections&#93; shortcode.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'The WordPress Page set as the directory home page.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'general',
			'id'           => 'connections_login',
			'position'     => 10,
			'title'        => __( 'Require Login', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Whether or not a login is required to view the directory. If login is required, the message to display to the user.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'general',
			'id'           => 'category',
			'position'     => 20,
			'title'        => __( 'Default Category', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'The default category to assign to entries if none is selected when adding an entry.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'general',
			'id'           => 'geo',
			'position'     => 30,
			'title'        => __( 'Base country and region.', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'The base country and region.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'       => 'general',
			'id'        => 'connections_visibility',
			'position'  => 40,
			'title'     => __( 'Shortcode Visibility Overrides', 'connections' ),
			'callback'  => function () {
				esc_html_e(
					'The &#91;connections&#93; shortcode has two options available to show an entry or an entire directory if the entry(ies) has been set to private or the user is required to be logged to view the directory. These options, when used, will only be applied to the current shortcode instance.',
					'connections'
				);
			},
			'page_hook' => $settings,
		);

		/*
		 * The sections registered to the Display tab.
		 */
		$sections[] = array(
			'tab'          => 'display',
			'id'           => 'connections_display_general',
			'position'     => 5,
			'title'        => __( 'General', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'The displayed date format.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'display',
			'id'           => 'connections_display_results',
			'position'     => 10,
			'title'        => __( 'Results List', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'The display settings for the directory results list.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'display',
			'id'           => 'list_actions',
			'position'     => 15,
			'title'        => __( 'Result List Actions', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Enable or disable various actions that are displayed above the result list.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Enable or disable various actions that are displayed above the result list.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'display',
			'id'           => 'connections_display_list',
			'position'     => 20,
			'title'        => __( 'Result List', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'The following settings are applied when viewing the entry results list.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Enable or disable the content blocks to be displayed in the results list view.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'display',
			'id'           => 'entry_actions',
			'position'     => 25,
			'title'        => __( 'Entry Actions', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Enable or disable various actions that are shown above the single entry in the detail view.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Enable or disable various actions that are shown above the single entry in the detail view.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'display',
			'id'           => 'connections_display_single',
			'position'     => 30,
			'title'        => __( 'Single Entry', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'The following settings are applied when viewing a single entry in the detail view. Which details are shown are dependant on the current template being used.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Enable or disable the content blocks to be displayed in the single entry view.',
					'connections'
				),
			),
		);

		/*
		 * The sections registered to the Form Fields tab.
		 */
		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-publish',
			'position'     => 10,
			'title'        => __( 'Publish Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Choose which entry types are displayed as options.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-name',
			'position'     => 20,
			'title'        => __( 'Name Fieldset', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Changing the active name fields will not effect existing entries, they will continue to display the existing name as they were previously saved. You will not be able to edit the existing name field unless the field is enabled.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Select the which name fields are to be displayed for each entry type.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-address',
			'position'     => 30,
			'title'        => __( 'Address Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the address fieldset options.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-phone',
			'position'     => 40,
			'title'        => __( 'Phone Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the phone fieldset options.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-email',
			'position'     => 50,
			'title'        => __( 'Email Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the email fieldset options.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-messenger',
			'position'     => 60,
			'title'        => __( 'Instant Messaging Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the instant messenger fieldset options.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-link',
			'position'     => 70,
			'title'        => __( 'Link Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the link fieldset options.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-date',
			'position'     => 80,
			'title'        => __( 'Date Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the date fieldset options.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'field-configuration',
			'id'           => 'fieldset-social-networks',
			'position'     => 90,
			'title'        => __( 'Social Networks Fieldset', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the social media network fieldset options.',
					'connections'
				),
			),
		);

		/*
		 * The sections registered to the Images tab.
		 */
		$sections[] = array(
			'tab'          => 'images',
			'id'           => 'connections_image_thumbnail',
			'position'     => 10,
			'title'        => __( 'Thumbnail Image', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Default settings are: Quality: 80%; Width: 80px; Height: 54px; Crop',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the thumbnail image settings.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'images',
			'id'           => 'connections_image_medium',
			'position'     => 20,
			'title'        => __( 'Medium Image', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Default settings are: Quality: 80%; Width: 225px; Height: 150px; Crop',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the medium image settings.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'images',
			'id'           => 'connections_image_large',
			'position'     => 30,
			'title'        => __( 'Large Image', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Default settings are: Quality: 80%; Width: 300px; Height: 225px; Crop',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the large image settings.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'images',
			'id'           => 'connections_image_logo',
			'position'     => 30,
			'title'        => __( 'Logo', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Default settings are: Quality: 80%; Width: 225px; Height: 150px; Fill',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the logo image settings.',
					'connections'
				),
			),
		);

		/*
		 * The sections registered to the Search tab.
		 */
		$sections[] = array(
			'tab'       => 'search',
			'id'        => 'connections_search_instructions',
			'position'  => 10,
			'title'     => __( 'Search Instructions', 'connections' ),
			'callback'  => function () {
				esc_html_e(
					'Search on the front end of the website is enabled in select premium templates only and does not integrate with the core WordPress search. None of the supplied templates include the search feature. These settings will affect the results of search on both the Manage admin page and the front end of the website.',
					'connections'
				);
			},
			'page_hook' => $settings,
		);

		$sections[] = array(
			'tab'          => 'search',
			'id'           => 'connections_search',
			'position'     => 20,
			'title'        => __( 'Search Fields', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'The fields to search when performing a query.',
					'connections'
				),
			),
		);

		/*
		 * The sections registered to the SEO tab.
		 */
		$sections[] = array(
			'tab'          => 'seo',
			'id'           => 'connections_seo_meta',
			'position'     => 10,
			'title'        => __( 'Page Meta', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the page meta tags.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'seo',
			'id'           => 'connections_seo',
			'position'     => 20,
			'title'        => __( 'Page Display', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Whether or not to dynamically update the page title.',
					'connections'
				),
			),
		);

		/*
		 * The sections registered to the Advanced tab.
		 */
		$sections[] = array(
			'tab'          => 'advanced',
			'id'           => 'connections_permalink',
			'position'     => 10,
			'title'        => __( 'Permalink', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Configure permalink support. Avoid using permalink structure names that will conflict with WordPress, such category and tag.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Configure the permalink base strings.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'tab'          => 'advanced',
			'id'           => 'connections_link',
			'position'     => 15,
			'title'        => __( 'Links', 'connections' ),
			'callback'     => function () {
				esc_html_e(
					'Enable certain entry data to become links.',
					'connections'
				);
			},
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Whether or not certain entry data to become links which can be clicked to filter the displayed results.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'advanced',
			'id'           => 'cpt',
			'position'     => 18,
			'title'        => __( 'Custom Post Type Support', 'connections' ),
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Enable support for Custom Post Type rewrite rules.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'advanced',
			'id'           => 'google_maps_geocoding_api',
			'position'     => 19,
			'title'        => __( 'Google Maps Geocoding API', 'connections' ),
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => __(
					'Google Maps API keys.',
					'connections'
				),
			),
		);

		$sections[] = array(
			'plugin_id'    => 'connections',
			'tab'          => 'advanced',
			'id'           => 'compatibility',
			'position'     => 20,
			'title'        => __( 'Compatibility', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array(
				'type'        => 'object',
				'description' => 'Configure various compatibility options.',
			),
		);

		$sections[] = array(
			'tab'          => 'advanced',
			'id'           => 'connections_debug',
			'position'     => 30,
			'title'        => __( 'Debug', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array( 'type' => 'object' ),
		);

		$sections[] = array(
			'tab'          => 'advanced',
			'id'           => 'connections_uninstall',
			'position'     => 30,
			'title'        => __( 'Uninstall', 'connections' ),
			'callback'     => '',
			'page_hook'    => $settings,
			'show_in_rest' => true,
			'schema'       => array( 'type' => 'object' ),
		);

		return $sections;
	}

	/**
	 * Register the settings sections.
	 *
	 * @access private
	 * @since  0.7.3.0
	 * @static
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function registerSettingsFields( $fields ) {

		$settings = 'connections_page_connections_settings';

		$homePageType = 'page';
		$excludeCPT   = array( 'attachment', 'revision', 'nav_menu_item', 'post' );
		$includeCPT   = array( 'page' );
		$cptOptions   = get_option( 'connections_cpt' );

		if ( isset( $cptOptions['enabled'] ) && 1 == $cptOptions['enabled'] ) {

			$homePageType = 'cpt-pages';
		}

		if ( isset( $cptOptions['supported'] ) && ! empty( $cptOptions['supported'] ) && is_array( $cptOptions['supported'] ) ) {

			$includeCPT = array_merge( $cptOptions['supported'], $includeCPT );
		}

		/*
		 * The General tab fields.
		 */
		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'page_id',
			'position'          => 5,
			'page_hook'         => $settings,
			'tab'               => 'general',
			'section'           => 'home_page',
			'title'             => __( 'Page', 'connections' ),
			'desc'              => '',
			'help'              => '',
			'type'              => $homePageType,
			'options'           => array(
				'exclude_cpt' => $excludeCPT,
				'include_cpt' => $includeCPT,
			),
			'show_option_none'  => __( 'Select Page', 'connections' ),
			'option_none_value' => '0',
			'schema'            => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'required',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'general',
			'section'           => 'connections_login',
			'title'             => __( 'Login Required', 'connections' ),
			'desc'              => __(
				'Require registered users to login before showing the directory.',
				'connections'
			),
			'help'              => '',
			'type'              => 'checkbox',
			'default'           => 0,
			// Only need to add this once on this tab, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'setAllowPublic' ),
			'schema'            => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'message',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'general',
			'section'   => 'connections_login',
			'title'     => __( 'Message', 'connections' ),
			'desc'      => __(
				'The message to display to site visitors or registered users not logged in.',
				'connections'
			),
			'help'      => '',
			'type'      => 'rte',
			'default'   => 'Please login to view the directory.',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'default',
			'position'  => 5,
			'page_hook' => $settings,
			'tab'       => 'general',
			'section'   => 'category',
			'title'     => __( 'Category', 'connections' ),
			'desc'      => '',
			'help'      => '',
			'type'      => 'category',
			'default'   => get_option( 'cn_default_category' ),
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'base_country',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'general',
			'section'           => 'geo',
			'title'             => __( 'Base Country', 'connections' ),
			'desc'              => '',
			'help'              => '',
			'type'              => 'select',
			'options'           => cnGeo::getCountries(),
			'default'           => 'US',
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'setGEOBase' ),
			'schema'            => array( 'type' => 'string' ),
		);

		// cnGEO::getRegions() when called without the $country code @param
		// will use the result from cnOptions::getBaseCountry() to define which
		// regions to return. If there are no regions an empty array will be returned.
		// So, if there are no regions, the is no reason to render this option.
		$regions = cnGeo::getRegions();

		if ( ! empty( $regions ) ) {

			$fields[] = array(
				'plugin_id' => 'connections',
				'id'        => 'base_region',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'general',
				'section'   => 'geo',
				'title'     => __( 'Base Region', 'connections' ),
				'desc'      => '',
				'help'      => '',
				'type'      => 'select',
				'options'   => cnGeo::getRegions(),
				'default'   => cnOptions::getBaseRegion(),
				'schema'    => array( 'type' => 'string' ),
			);
		}

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'allow_public_override',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'general',
			'section'   => 'connections_visibility',
			'title'     => __( 'Enable allow_public_override', 'connections' ),
			'desc'      => __(
				'By default, all entries whose status is Public will be visible to all site visitors or registered users not logged in. If the option to require login has been enabled, the <em>allow_public_override</em> shortcode option allows you to override requiring the site visitor to be logged in. This setting is useful in multi author sites where those authors may have a need to display specific entries to the public. For security reasons this option is disabled by default. If checked, this enables this shortcode option.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'allow_private_override',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'general',
			'section'   => 'connections_visibility',
			'title'     => __( 'Enable private_override', 'connections' ),
			'desc'      => __(
				'Entries can be set to a Private status which requires the user to be logged in to the site in order for them to be able to view those entries. The <em>private_override</em> shortcode option allows you to override their "Private" status. This setting is useful in multi author sites where those authors may have a need to display specific private entries to the public. For security reasons this option is disabled by default. If checked, this enables this shortcode option.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
		);

		/*
		 * The Display tab fields.
		 */
		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'date_format',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_general',
			'title'     => __( 'Date Format', 'connections' ),
			'desc'      => '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">' . __( 'Documentation on date and time formatting.', 'connections' ) . '</a>',
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => esc_attr( get_option( 'date_format' ) ),
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'search_message',
			'position'  => 3,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'display_results',
			'title'     => __( 'Show Clear Search Message', 'connections' ),
			'desc'      => __(
				'Display a message box above the search results with information about the current query and the option (a button) to clear results.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'cat_desc',
			'position'  => 5,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_results',
			'title'     => __( 'Category Description', 'connections' ),
			'desc'      => __( 'Display the current category description before the results list.', 'connections' ),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'index',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_results',
			'title'     => __( 'Character Index', 'connections' ),
			'desc'      => __( 'Show the character index at the top of the results list.', 'connections' ),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'index_repeat',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_results',
			'title'     => '',
			'desc'      => __( 'Repeat the character index at the beginning of each character group.', 'connections' ),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'show_current_character',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_results',
			'title'     => '',
			'desc'      => __( 'Show the current character at the beginning of each character group.', 'connections' ),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$listActionsOptions['items']    = apply_filters(
			'cn_list_action_options',
			array(
				'view_all' => __(
					'Show a "View All" link. When this option is enabled a "View All" link will be displayed.',
					'connections'
				),
			)
		);
		$listActionsOptions['required'] = apply_filters( 'cn_list_action_options_required', array() );

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'actions',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'list_actions',
			'title'     => '',
			'desc'      => __(
				'Whether or not a list action should be shown. Actions can be dragged and dropped in the desired order to be shown.',
				'connections'
			),
			'help'      => '',
			'type'      => 'sortable_checklist',
			'options'   => $listActionsOptions,
			'default'   => 0,
			'schema'    => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'content_block',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_list',
			'title'     => __( 'Content Blocks', 'connections' ),
			'desc'      => sprintf(
				wp_kses(
					/* translators: Plugin documentation URI. */
					__(
						'Whether a content block should be shown. <a href="%s" target="_blank">Read more by clicking this link.</a> NOTE: Content block support must be enabled in the template to have an effect. All the core templates support this feature. If you have purchased a commercial template, it may need to be updated in order to support this feature.',
						'connections'
					),
					array( 'a' => array( 'href' => array(), 'target' => array() ) )
				),
				esc_url( 'https://connections-pro.com/documentation/settings/#Content_Blocks' )
			),
			'help'      => '',
			'type'      => 'sortable_checklist',
			'options'   => cnOptions::getContentBlocks( null, 'list' ),
			'default'   => 0,
			'schema'    => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
			),
		);

		$entryActionsOptions['items']    = apply_filters(
			'cn_entry_action_options',
			array(
				'back'  => __( 'Show the "Back to Directory" link.', 'connections' ),
				'vcard' => __(
					'Show the "Add to Address Book" link. This link allows the download of the entry\'s vCard.',
					'connections'
				),
			)
		);
		$entryActionsOptions['required'] = apply_filters( 'cn_entry_action_options_required', array() );

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'actions',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'entry_actions',
			'title'     => '',
			'desc'      => __(
				'Whether or not an entry action should be shown. Actions can be dragged and dropped in the desired order to be shown.',
				'connections'
			),
			'help'      => '',
			'type'      => 'sortable_checklist',
			'options'   => $entryActionsOptions,
			'default'   => array(
				'order'  => array(
					'back',
					'vcard',
				),
				'active' => array(
					'back',
					'vcard',
				),
			),
			'schema'    => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
			),
		);

		//$fields[] = array(
		//	'plugin_id' => 'connections',
		//	'id'        => 'template',
		//	'position'  => 10,
		//	'page_hook' => $settings,
		//	'tab'       => 'display',
		//	'section'   => 'connections_display_single',
		//	'title'     => __( 'Template', 'connections' ),
		//	'desc'      => __(
		//		'Display a single entry using the active template based on entry type. For example, if the entry is an organization it will be displayed using the template that is activated for the "Organization" template type found on the Connections : Templates admin page.',
		//		'connections'
		//	),
		//	'help'      => '',
		//	'type'      => 'checkbox',
		//	'default'   => 0
		//);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'content_block',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_single',
			'title'     => __( 'Content Blocks', 'connections' ),
			'desc'      => sprintf(
				wp_kses(
					/* translators: Plugin documentation URI. */
					__(
						'Whether a content block should be shown. <a href="%s" target="_blank">Read more by clicking this link.</a> NOTE: Content block support must be enabled in the template to have an effect. All the core templates support this feature. If you have purchased a commercial template, it may need to be updated in order to support this feature.',
						'connections'
					),
					array( 'a' => array( 'href' => array(), 'target' => array() ) )
				),
				esc_url( 'https://connections-pro.com/documentation/settings/#Content_Blocks-2' )
			),
			'help'      => '',
			'type'      => 'sortable_checklist',
			'options'   => cnOptions::getContentBlocks( null, 'single' ),
			'default'   => 0,
			'schema'    => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
			),
		);

		/*
		 * The Field Configuration tab fields.
		 */
		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'entry-type',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-publish',
			'title'     => __( 'Entry Type Options', 'connections' ),
			'desc'      => __(
				'Choose which entry types are displayed as options. Drag and drop to change the display order. Disabling entry type options will not effect existing entries, they will retain the type they were saved with. When editing an entry of a type which has been disabled, it will default to the selected Default Entry Type.',
				'connections'
			),
			'help'      => '',
			'type'      => 'sortable_checklist',
			'options'   => array(
				'items'    => cnOptions::getEntryTypes(),
				'required' => array(),
			),
			'default'   => array(
				'order' => array(
					'individual',
					'organization',
					'family',
				),
				'active' => array(
					'individual',
					'organization',
					'family',
				),
			),
			'schema'    => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'default-entry-type',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-publish',
			'title'     => __( 'Default Entry Type', 'connections' ),
			'desc'      => __(
				'Select the default selected entry type.',
				'connections'
			),
			'help'      => '',
			'type'      => 'select',
			'options'   => cnOptions::getEntryTypes(),
			'default'   => 'individual',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'default-publish-status',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-publish',
			'title'     => __( 'Default Publish Visibility', 'connections' ),
			'desc'      => __(
				'Select the default selected publish visibility status.',
				'connections'
			),
			'help'      => '',
			'type'      => 'select',
			'options'   => array(
				'public'   => __( 'Public', 'connections' ),
				'private'  => __( 'Private', 'connections' ),
				'unlisted' => __( 'Unlisted', 'connections' ),
			),
			'default'   => 'public',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'individual-name-fields',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-name',
			'title'     => __( 'Individual Name Fields', 'connections' ),
			'desc'      => __(
				'Select the which name fields are to be displayed for the Individual entry type. Required fields are not displayed as options.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox-group',
			'options'   => array(
				'prefix'       => __( 'Prefix', 'connections' ),
				// 'first'        => __( 'First Name', 'connections' ),
				'middle'       => __( 'Middle Name', 'connections' ),
				// 'last'         => __( 'Last Name', 'connections' ),
				'suffix'       => __( 'Suffix', 'connections' ),
				'title'        => __( 'Title', 'connections' ),
				'organization' => __( 'Organization', 'connections' ),
				'department'   => __( 'Department', 'connections' ),
			),
			'default'   => array(
				'prefix',
				// 'first',
				'middle',
				// 'last',
				'suffix',
				'title',
				'organization',
				'department',
			),
			'schema'    => array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'organization-name-fields',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-name',
			'title'     => __( 'Organization Name Fields', 'connections' ),
			'desc'      => __(
				'Select the which name fields are to be displayed for the Organization entry type. Required fields are not displayed as options.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox-group',
			'options'   => array(
				// 'organization'       => __( 'Organization', 'connections' ),
				'department'         => __( 'Department', 'connections' ),
				'contact_first_name' => __( 'Contact First Name', 'connections' ),
				'contact_last_name'  => __( 'Contact Last Name', 'connections' ),
			),
			'default'   => array(
				'department',
				'contact_first_name',
				'contact_last_name',
			),
			'schema'    => array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'repeatable',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Repeatable', 'connections' ),
			'desc'      => __(
				'Make the address fieldset repeatable to allow multiple addresses to be added to a single entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'count',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => '',
			'desc'      => __(
				'The minimum number of address fieldsets to display.',
				'connections'
			),
			'help'      => '',
			'type'      => 'number',
			'size'      => 'small',
			'default'   => 0,
			'schema'    => array( 'type' => 'integer' ),
		);

		// Grab the address types.
		$addressTypes = cnOptions::getCoreAddressTypes();

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'address-types',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'field-configuration',
			'section'           => 'fieldset-address',
			'title'             => __( 'Address Type Options', 'connections' ),
			'desc'              => __(
				'Choose which address types are displayed as options. Drag and drop to change the display order. The top active item will be the default selected type when adding a new address. Deactivating an address type will not effect previously saved entries. Add custom address types by clicking the "Add" button. Custom address types can be removed but only if no addresses are saved with that type. The "core" address types of "Home, School, Work and Other" can not be removed. A "Remove" button will display for address types which can be safely removed.',
				'connections'
			),
			'help'              => '',
			'type'              => 'sortable_input-repeatable',
			'options'           => array(
				'items'    => $addressTypes,
				// Any types registered via the `cn_address_options` need to be set as required.
				'required' => array_keys( apply_filters( 'cn_address_options', array() ) ),
			),
			'default'           => array(
				'order'  => array_keys( $addressTypes ),
				// Any types registered via the `cn_address_options` filter should be set as active (enabled).
				// The `cn_address_options` filter is applied in case a user has removed types using the filter.
				// This ensures they default to inactive (disabled).
				'active' => array_keys( apply_filters( 'cn_address_options', $addressTypes ) ),
			),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeAddressFieldsetSettings' ),
			'schema'            => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'type'   => array(
						'type'       => 'object',
						'properties' => array_fill_keys(
							array_keys( cnOptions::getRegisteredAddressTypes() ),
							array( 'type' => 'string' )
						),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-preferred',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Preferred Address', 'connections' ),
			'desc'      => __(
				'Enable this option to set a preferred address when adding addresses to an entry. This is used when exporting the entry as a vCard. Disabling this option will not effect existing addresses which have been set as preferred. When editing an entry with a preferred address with this option disabled, the preferred setting of the address will be removed.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-visibility',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Per Address Visibility', 'connections' ),
			'desc'      => __(
				'Enable this option to set per address visibility. When disabled, all addresses will default to public. Changing this option will not effect the visibility status of previously saved addresses.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'active-fields',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Address Fields', 'connections' ),
			'desc'      => __(
				'Select the address fields to be displayed. Required fields are not displayed as options.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox-group',
			'options'   => array(
				'line_2'   => __( 'Address Line 2', 'connections' ),
				'line_3'   => __( 'Address Line 3', 'connections' ),
				'line_4'   => __( 'Address Line 4', 'connections' ),
				'district' => __( 'District', 'connections' ),
				'county'   => __( 'County', 'connections' ),
				'country'  => __( 'Country', 'connections' ),
			),
			'default'   => array(
				'line_2',
				'line_3',
				'line_4',
				'district',
				'county',
				'country',
			),
			'schema'    => array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'autofill-region',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Autofill Region', 'connections' ),
			'desc'      => __(
				'Autofill the region (state) field with the default region.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'autofill-country',
			'position'  => 60,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Autofill Country', 'connections' ),
			'desc'      => __(
				'Autofill the country field with the default country. When utilizing the autocomplete country field, the default country will be automatically selected',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'autocomplete-country',
			'position'  => 70,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Autocomplete Country', 'connections' ),
			'desc'      => __(
				'Utilize an autocomplete field for country selection instead of a text input field.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'translation',
			'position'  => 80,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-address',
			'title'     => __( 'Country Translation', 'connections' ),
			'desc'      => __(
				'Display the country names in English or their native translation.',
				'connections'
			),
			'help'      => '',
			'type'      => 'radio',
			'options'   => array(
				'english' => __( 'English', 'connections' ),
				'native'  => __( 'Native', 'connections' ),
			),
			'default'   => 'native',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'repeatable',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-phone',
			'title'     => __( 'Repeatable', 'connections' ),
			'desc'      => __(
				'Make the phone fieldset repeatable to allow multiple phone numbers to be added to a single entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'count',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-phone',
			'title'     => '',
			'desc'      => __(
				'The minimum number of phone number fieldsets to display.',
				'connections'
			),
			'help'      => '',
			'type'      => 'number',
			'size'      => 'small',
			'default'   => 0,
			'schema'    => array( 'type' => 'integer' ),
		);

		// Grab the phone types.
		$phoneTypes = cnOptions::getCorePhoneTypes();

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'phone-types',
			'position'          => 30,
			'page_hook'         => $settings,
			'tab'               => 'field-configuration',
			'section'           => 'fieldset-phone',
			'title'             => __( 'Phone Type Options', 'connections' ),
			'desc'              => __(
				'Choose which phone types are displayed as options. Drag and drop to change the display order. The top active item will be the default selected type when adding a new phone. Deactivating an phone type will not effect previously saved entries. Add custom phone types by clicking the "Add" button. Custom phone types can be removed but only if no phone are saved with that type. The "core" phone types of "Home Phone, Home Fax, Cell Phone, Work Phone and Work Fax" can not be removed. A "Remove" button will display for phone types which can be safely removed.',
				'connections'
			),
			'help'              => '',
			'type'              => 'sortable_input-repeatable',
			'options'           => array(
				'items'    => $phoneTypes,
				// Any types registered via the `cn_phone_options` need to be set as required.
				'required' => array_keys( apply_filters( 'cn_phone_options', array() ) ),
			),
			'default'           => array(
				'order'  => array_keys( $phoneTypes ),
				// Any types registered via the `cn_phone_options` filter should be set as active (enabled).
				// The `cn_phone_options` filter is applied in case a user has removed types using the filter.
				// This ensures they default to inactive (disabled).
				'active' => array_keys( apply_filters( 'cn_phone_options', $phoneTypes ) ),
			),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizePhoneFieldsetSettings' ),
			'schema'            => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'type'   => array(
						'type'       => 'object',
						'properties' => array_fill_keys(
							array_keys( cnOptions::getRegisteredPhoneTypes() ),
							array( 'type' => 'string' )
						),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-preferred',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-phone',
			'title'     => __( 'Preferred Phone', 'connections' ),
			'desc'      => __(
				'Enable this option to set a preferred phone number when adding a number to an entry. This is used when exporting the entry as a vCard. Disabling this option will not effect existing phone numbers which have been set as preferred. When editing an entry with a preferred phone number with this option disabled, the preferred setting of the phone number will be removed.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-visibility',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-phone',
			'title'     => __( 'Per Phone Visibility', 'connections' ),
			'desc'      => __(
				'Enable this option to set per phone number visibility. When disabled, all phone numbers will default to public. Changing this option will not effect the visibility status of previously saved phone numbers.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'repeatable',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-email',
			'title'     => __( 'Repeatable', 'connections' ),
			'desc'      => __(
				'Make the email fieldset repeatable to allow multiple email addresses to be added to a single entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'count',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-email',
			'title'     => '',
			'desc'      => __(
				'The minimum number of email address fieldsets to display.',
				'connections'
			),
			'help'      => '',
			'type'      => 'number',
			'size'      => 'small',
			'default'   => 0,
			'schema'    => array( 'type' => 'integer' ),
		);

		// Grab the email types.
		$emailTypes = cnOptions::getCoreEmailTypes();

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'email-types',
			'position'          => 30,
			'page_hook'         => $settings,
			'tab'               => 'field-configuration',
			'section'           => 'fieldset-email',
			'title'             => __( 'Email Type Options', 'connections' ),
			'desc'              => __(
				'Choose which email types are displayed as options. Drag and drop to change the display order. The top active item will be the default selected type when adding a new email address. Deactivating an email type will not effect previously saved entries. Add custom email types by clicking the "Add" button. Custom email types can be removed but only if no email addresses are saved with that type. The "core" email types of "Personal Email and Work Email" can not be removed. A "Remove" button will display for email types which can be safely removed.',
				'connections'
			),
			'help'              => '',
			'type'              => 'sortable_input-repeatable',
			'options'           => array(
				'items'    => $emailTypes,
				// Any types registered via the `cn_email_options` need to be set as required.
				'required' => array_keys( apply_filters( 'cn_email_options', array() ) ),
			),
			'default'           => array(
				'order'  => array_keys( $emailTypes ),
				// Any types registered via the `cn_email_options` filter should be set as active (enabled).
				// The `cn_email_options` filter is applied in case a user has removed types using the filter.
				// This ensures they default to inactive (disabled).
				'active' => array_keys( apply_filters( 'cn_email_options', $emailTypes ) ),
			),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeEmailFieldsetSettings' ),
			'schema'            => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'type'   => array(
						'type'       => 'object',
						'properties' => array_fill_keys(
							array_keys( cnOptions::getRegisteredEmailTypes() ),
							array( 'type' => 'string' )
						),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-preferred',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-email',
			'title'     => __( 'Preferred Email', 'connections' ),
			'desc'      => __(
				'Enable this option to set a preferred email address when adding an email address to an entry. This is used when exporting the entry as a vCard. Disabling this option will not effect existing email addresses which have been set as preferred. When editing an entry with a preferred email address with this option disabled, the preferred setting of the email will be removed.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-visibility',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-email',
			'title'     => __( 'Per Email Visibility', 'connections' ),
			'desc'      => __(
				'Enable this option to set per email address visibility. When disabled, all email addresses will default to public. Changing this option will not effect the visibility status of previously saved email addresses.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'repeatable',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-messenger',
			'title'     => __( 'Repeatable', 'connections' ),
			'desc'      => __(
				'Make the Instant Messenger fieldset repeatable to allow multiple instant messaging ID\'s to be added to a single entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'count',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-messenger',
			'title'     => '',
			'desc'      => __(
				'The minimum number of Instant Messenger fieldsets to display.',
				'connections'
			),
			'help'      => '',
			'type'      => 'number',
			'size'      => 'small',
			'default'   => 0,
			'schema'    => array( 'type' => 'integer' ),
		);

		// Grab the messenger types.
		$imTypes = cnOptions::getCoreMessengerTypes();

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'messenger-types',
			'position'          => 30,
			'page_hook'         => $settings,
			'tab'               => 'field-configuration',
			'section'           => 'fieldset-messenger',
			'title'             => __( 'Messenger Type Options', 'connections' ),
			'desc'              => __(
				'Choose which instant messenger types are displayed as options. Drag and drop to change the display order. The top active item will be the default selected type when adding a new messenger ID. Deactivating an instant messenger type will not effect previously saved entries. Add custom instant messenger types by clicking the "Add" button. Custom instant messenger types can be removed but only if no messenger ID\'s are saved with that type. The "core" instant messenger types can not be removed. A "Remove" button will display for instant messenger types which can be safely removed.',
				'connections'
			),
			'help'              => '',
			'type'              => 'sortable_input-repeatable',
			'options'           => array(
				'items'    => $imTypes,
				// Any types registered via the `cn_instant_messenger_options` need to be set as required.
				'required' => array_keys( apply_filters( 'cn_instant_messenger_options', array() ) ),
			),
			'default'           => array(
				'order'  => array_keys( $imTypes ),
				// Any types registered via the `cn_instant_messenger_options` filter should be set as active (enabled).
				// The `cn_instant_messenger_options` filter is applied in case a user has removed types using the filter.
				// This ensures they default to inactive (disabled).
				'active' => array_keys( apply_filters( 'cn_instant_messenger_options', $imTypes ) ),
			),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeMessengerFieldsetSettings' ),
			'schema'            => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'type'   => array(
						'type'       => 'object',
						'properties' => array_fill_keys(
							array_keys( cnOptions::getRegisteredMessengerTypes() ),
							array( 'type' => 'string' )
						),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-preferred',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-messenger',
			'title'     => __( 'Preferred Messenger ID', 'connections' ),
			'desc'      => __(
				'Enable this option to set a preferred instant messenger service when adding an instant messenger ID to an entry. Disabling this option will not effect existing instant messenger ID\'s which have been set as preferred. When editing an entry with a preferred instant messenger ID with this option disabled, the preferred setting of the instant messenger ID will be removed.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-visibility',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-messenger',
			'title'     => __( 'Per Instant Messenger Visibility', 'connections' ),
			'desc'      => __(
				'Enable this option to set per instant messenger ID visibility. When disabled, all instant messenger ID\'s will default to public. Changing this option will not effect the visibility status of previously saved instant messenger ID\'s.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'repeatable',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Repeatable', 'connections' ),
			'desc'      => __(
				'Make the Link fieldset repeatable to allow multiple links to be added to a single entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'count',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => '',
			'desc'      => __(
				'The minimum number of Instant Messenger fieldsets to display.',
				'connections'
			),
			'help'      => '',
			'type'      => 'number',
			'size'      => 'small',
			'default'   => 0,
			'schema'    => array( 'type' => 'integer' ),
		);

		// Grab the link types.
		$linkTypes = cnOptions::getCoreLinkTypes();

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'link-types',
			'position'          => 30,
			'page_hook'         => $settings,
			'tab'               => 'field-configuration',
			'section'           => 'fieldset-link',
			'title'             => __( 'Link Type Options', 'connections' ),
			'desc'              => __(
				'Choose which link types are displayed as options. Drag and drop to change the display order. The top active item will be the default selected type when adding a new link. Deactivating a link type will not effect previously saved entries. Add custom link types by clicking the "Add" button. Custom link types can be removed but only if no links are saved with that type. The "core" link types can not be removed. A "Remove" button will display for link types which can be safely removed.',
				'connections'
			),
			'help'              => '',
			'type'              => 'sortable_input-repeatable',
			'options'           => array(
				'items'    => $linkTypes,
				// Any types registered via the `cn_link_options` need to be set as required.
				'required' => array_keys( apply_filters( 'cn_link_options', array() ) ),
			),
			'default'           => array(
				'order'  => array_keys( $linkTypes ),
				// Any types registered via the `cn_link_options` filter should be set as active (enabled).
				// The `cn_link_options` filter is applied in case a user has removed types using the filter.
				// This ensures they default to inactive (disabled).
				'active' => array_keys( apply_filters( 'cn_link_options', $linkTypes ) ),
			),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeLinkFieldsetSettings' ),
			'schema'            => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'type'   => array(
						'type'       => 'object',
						'properties' => array_fill_keys(
							array_keys( cnOptions::getRegisteredLinkTypes() ),
							array( 'type' => 'string' )
						),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-preferred',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Preferred Link', 'connections' ),
			'desc'      => __(
				'Enable this option to set a preferred link when adding a link to an entry. Disabling this option will not effect existing links which have been set as preferred. When editing an entry with a preferred link with this option disabled, the preferred setting of the link will be removed.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-visibility',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Per Link Visibility', 'connections' ),
			'desc'      => __(
				'Enable this option to set per link visibility. When disabled, links will default to public. Changing this option will not effect the visibility status of previously saved links.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'default-target',
			'position'  => 60,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Default Target', 'connections' ),
			'desc'      => __(
				'Choose the default selected option for whether a link should open in a new window/tab or the same window/tab.',
				'connections'
			),
			'help'      => '',
			'type'      => 'select',
			'options'   => array(
				'new'  => __( 'New Window', 'connections' ),
				'same' => __( 'Same Window', 'connections' ),
			),
			'default'   => 'new',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'follow-link',
			'position'  => 70,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Follow Link', 'connections' ),
			'desc'      => __(
				'Whether or not search engines should follow the link.',
				'connections'
			),
			'help'      => '',
			'type'      => 'select',
			'options'   => array(
				'nofollow' => __( 'Do Not Follow', 'connections' ),
				'dofollow' => __( 'Follow', 'connections' ),
			),
			'default'   => 'nofollow',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-target',
			'position'  => 80,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Per Link Target', 'connections' ),
			'desc'      => __(
				'Enable this option to set the per link target when adding a link to an entry. When this option is disabled, the default link target will be assigned to the link being added.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-follow',
			'position'  => 90,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Per Link Follow', 'connections' ),
			'desc'      => __(
				'Enable this option to set the per link follow when adding a link to an entry. When this option is disabled, the Follow Link default will be assigned to the link being added.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-assign',
			'position'  => 100,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-link',
			'title'     => __( 'Per Link Assign Image', 'connections' ),
			'desc'      => __(
				'Enable this option to set the per link image assignment when adding a link to an entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'repeatable',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-date',
			'title'     => __( 'Repeatable', 'connections' ),
			'desc'      => __(
				'Make the Date fieldset repeatable to allow multiple date events to be added to a single entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'count',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-date',
			'title'     => '',
			'desc'      => __(
				'The minimum number of Date fieldsets to display.',
				'connections'
			),
			'help'      => '',
			'type'      => 'number',
			'size'      => 'small',
			'default'   => 0,
			'schema'    => array( 'type' => 'integer' ),
		);

		// Grab the date types.
		$dateTypes = cnOptions::getCoreDateTypes();

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'date-types',
			'position'          => 30,
			'page_hook'         => $settings,
			'tab'               => 'field-configuration',
			'section'           => 'fieldset-date',
			'title'             => __( 'Date Type Options', 'connections' ),
			'desc'              => __(
				'Choose which date event types are displayed as options. Drag and drop to change the display order. The top active item will be the default selected type when adding a date event. Deactivating a date type will not effect previously saved entries. Add custom date types by clicking the "Add" button. Custom date types can be removed but only if no date events are saved with that type. The "core" date types can not be removed. A "Remove" button will display for date types which can be safely removed.',
				'connections'
			),
			'help'              => '',
			'type'              => 'sortable_input-repeatable',
			'options'           => array(
				'items'    => $dateTypes,
				// Any types registered via the `cn_date_options` need to be set as required.
				'required' => array_keys( apply_filters( 'cn_date_options', array() ) ),
			),
			'default'           => array(
				'order'  => array_keys( $dateTypes ),
				// Any types registered via the `cn_date_options` filter should be set as active (enabled).
				// The `cn_date_options` filter is applied in case a user has removed types using the filter.
				// This ensures they default to inactive (disabled).
				'active' => array_keys( apply_filters( 'cn_date_options', $dateTypes ) ),
			),
			// Only need to add this once, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeDateFieldsetSettings' ),
			'schema'            => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'type'   => array(
						'type'       => 'object',
						'properties' => array_fill_keys(
							array_keys( cnOptions::getRegisteredDateTypes() ),
							array( 'type' => 'string' )
						),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-preferred',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-date',
			'title'     => __( 'Preferred Date Type', 'connections' ),
			'desc'      => __(
				'Enable this option to set a preferred date type when adding an date event to an entry. Disabling this option will not effect existing dates which have been set as preferred. When editing an entry with a date with this option disabled, the preferred setting of the date will be removed.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-visibility',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-date',
			'title'     => __( 'Per Date Visibility', 'connections' ),
			'desc'      => __(
				'Enable this option to set per date visibility. When disabled, all dates will default to public. Changing this option will not effect the visibility status of previously saved dates.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'repeatable',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => __( 'Repeatable', 'connections' ),
			'desc'      => __(
				'Make the Social Network fieldset repeatable to allow multiple social networks  to be added to a single entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'count',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => '',
			'desc'      => __(
				'The minimum number of Social Network fieldsets to display.',
				'connections'
			),
			'help'      => '',
			'type'      => 'number',
			'size'      => 'small',
			'default'   => 0,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'shape',
			'position'  => 22,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => __( 'Shape', 'connections' ),
			'desc'      => __(
				'Choose social network icon shape.',
				'connections'
			),
			'help'      => '',
			'type'      => 'radio',
			'options'   => array(
				'circle'  => __( 'Circle', 'connections' ),
				'rounded' => __( 'Rounded Corners', 'connections' ),
				'square'  => __( 'Square', 'connections' ),
			),
			'default'   => 'rounded',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'color-scheme',
			'position'  => 23,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => __( 'Color Scheme', 'connections' ),
			'desc'      => __(
				'Choose social network icon color scheme.',
				'connections'
			),
			'help'      => '',
			'type'      => 'radio',
			'options'   => array(
				'individual' => __( 'Individual Icon Colors', 'connections' ),
				'global'     => __( 'Global Icon Colors', 'connections' ),
			),
			'default'   => 'individual',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'background-transparent',
			'position'  => 24,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => '',
			'desc'      => __(
				'Force icon background colors to be transparent. Enabling this option will override the colors set for the global background and background hover colors.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'background-color',
			'position'  => 25,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => '',
			'desc'      => __(
				'Choose the global icon background color.',
				'connections'
			),
			'help'      => '',
			'type'      => 'colorpicker',
			'default'   => '#BEBDBD',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'background-color-hover',
			'position'  => 27,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => '',
			'desc'      => __(
				'Choose the global icon background hover color.',
				'connections'
			),
			'help'      => '',
			'type'      => 'colorpicker',
			'default'   => '#E8E8E8',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'foreground-color',
			'position'  => 28,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => '',
			'desc'      => __(
				'Choose the global icon foreground color.',
				'connections'
			),
			'help'      => '',
			'type'      => 'colorpicker',
			'default'   => '#FFFFFF',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'foreground-color-hover',
			'position'  => 28,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => '',
			'desc'      => __(
				'Choose the global icon foreground hover color.',
				'connections'
			),
			'help'      => '',
			'type'      => 'colorpicker',
			'default'   => '#FFFFFF',
			'schema'    => array( 'type' => 'string' ),
		);

		// Grab the social types.
		$socialMediaTypes = cnOptions::getCoreSocialNetworkTypes();

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'social-network-types',
			'position'          => 30,
			'page_hook'         => $settings,
			'tab'               => 'field-configuration',
			'section'           => 'fieldset-social-networks',
			'title'             => __( 'Social Network Type Options', 'connections' ),
			'desc'              => __(
				'Choose which social network types are displayed as options. Drag and drop to change the display order. The top active item will be the default selected type when adding a social network. Deactivating a social network will not effect previously saved entries. Add custom social network types by clicking the "Add" button. Custom social network types can be removed but only if no social networks of that type are assigned to an entry. The "core" social network types can not be removed. A "Remove" button will display for social network types which can be safely removed.',
				'connections'
			),
			'help'              => '',
			'type'              => 'sortable_iconpicker-repeatable',
			'options'           => array(
				'items'    => $socialMediaTypes,
				// Any types registered via the `cn_social_network_options` need to be set as required.
				'required' => array_keys( apply_filters( 'cn_social_network_options', array() ) ),
			),
			'default'           => array(
				'order'  => array_keys( $socialMediaTypes ),
				// Any types registered via the `cn_social_network_options` filter should be set as active (enabled).
				// The `cn_social_network_options` filter is applied in case a user has removed types using the filter.
				// This ensures they default to inactive (disabled).
				'active' => array_keys( apply_filters( 'cn_social_network_options', $socialMediaTypes ) ),
			),
			// Only need to add this once, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeSocialNetworkFieldsetSettings' ),
			'schema'            => array(
				'type'       => 'object',
				'properties' => array(
					'order'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'icon' => array(
						'type'       => 'object',
						'properties' => array_fill_keys(
							array_keys( cnOptions::getRegisteredSocialNetworkTypes() ),
							array(
								'type'       => 'object',
								'properties' => array(
									'slug'                   => array( 'type' => 'string' ),
									'background-color'       => array( 'type' => 'string' ),
									'background-color-hover' => array( 'type' => 'string' ),
									'background-transparent' => array( 'type' => 'string' ),
									'foreground-color'       => array( 'type' => 'string' ),
									'foreground-color-hover' => array( 'type' => 'string' ),
									'name'                   => array( 'type' => 'string' ),
								),
							)
						),
					),
					'active' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-preferred',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => __( 'Preferred Social Network Type', 'connections' ),
			'desc'      => __(
				'Enable this option to set a preferred social network type when adding a social network to an entry. Disabling this option will not effect existing social networks which have been set as preferred. When editing an entry with a social network with this option disabled, the preferred setting of the social network will be removed.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'permit-visibility',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'field-configuration',
			'section'   => 'fieldset-social-networks',
			'title'     => __( 'Per Social Network Visibility', 'connections' ),
			'desc'      => __(
				'Enable this option to set per social network visibility. When disabled, all social networks will default to public. Changing this option will not effect the visibility status of previously saved social networks.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		// Filter to remove the "Remove" button if a custom fieldset type is in use.
		add_filter( 'cn_settings_field-sortable_input-repeatable-item', array( __CLASS__, 'fieldsetTypeRemovable' ), 10, 2 );
		add_filter( 'cn_settings_field-sortable_iconpicker-repeatable-item', array( __CLASS__, 'socialNetworkTypesInUse' ), 10, 2 );

		/*
		 * The Images tab fields.
		 */
		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'quality',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'images',
			'section'           => 'connections_image_thumbnail',
			'title'             => __( 'JPEG Quality', 'connections' ),
			'desc'              => '%',
			'help'              => '',
			'type'              => 'text',
			'size'              => 'small',
			'default'           => 80,
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' ),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'schema'            => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 100,
				'exclusiveMinimum' => true,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'width',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_thumbnail',
			'title'     => __( 'Width', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 80,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'height',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_thumbnail',
			'title'     => __( 'Height', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 54,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'ratio',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_thumbnail',
			'title'     => __( 'Crop Mode', 'connections' ),
			'desc'      => '',
			'help'      => '',
			'type'      => 'radio',
			'options'   => array(
				'crop' => __(
					'Crop and resize proportionally to best fit the specified dimensions, maintaining the aspect ratio.',
					'connections'
				),
				'fill' => __(
					'Resize proportionally to fit entire image into the specified dimensions and add margins if required.',
					'connections'
				),
				'fit'  => __(
					'Resize proportionally adjusting the size of scaled image so there are no margins added.',
					'connections'
				),
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' ),
			),
			'default'   => 'crop',
			'schema'    => array(
				'type' => 'string',
				'enum' => array( 'crop', 'fill', 'fit', 'none' ),
			),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'quality',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'images',
			'section'           => 'connections_image_medium',
			'title'             => __( 'JPEG Quality', 'connections' ),
			'desc'              => '%',
			'help'              => '',
			'type'              => 'text',
			'size'              => 'small',
			'default'           => 80,
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' ),
			'schema'            => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 100,
				'exclusiveMinimum' => true,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'width',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_medium',
			'title'     => __( 'Width', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 225,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'height',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_medium',
			'title'     => __( 'Height', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 150,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'ratio',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_medium',
			'title'     => __( 'Crop Mode', 'connections' ),
			'desc'      => '',
			'help'      => '',
			'type'      => 'radio',
			'options'   => array(
				'crop' => __(
					'Crop and resize proportionally to best fit the specified dimensions, maintaining the aspect ratio.',
					'connections'
				),
				'fill' => __(
					'Resize proportionally to fit entire image into the specified dimensions and add margins if required.',
					'connections'
				),
				'fit'  => __(
					'Resize proportionally adjusting the size of scaled image so there are no margins added.',
					'connections'
				),
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' ),
			),
			'default'   => 'crop',
			'schema'    => array(
				'type' => 'string',
				'enum' => array( 'crop', 'fill', 'fit', 'none' ),
			),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'quality',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'images',
			'section'           => 'connections_image_large',
			'title'             => __( 'JPEG Quality', 'connections' ),
			'desc'              => '%',
			'help'              => '',
			'type'              => 'text',
			'size'              => 'small',
			'default'           => 80,
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' ),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'schema'            => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 100,
				'exclusiveMinimum' => true,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'width',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_large',
			'title'     => __( 'Width', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 300,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'height',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_large',
			'title'     => __( 'Height', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 225,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'ratio',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_large',
			'title'     => __( 'Crop Mode', 'connections' ),
			'desc'      => '',
			'help'      => '',
			'type'      => 'radio',
			'options'   => array(
				'crop' => __(
					'Crop and resize proportionally to best fit the specified dimensions, maintaining the aspect ratio.',
					'connections'
				),
				'fill' => __(
					'Resize proportionally to fit entire image into the specified dimensions and add margins if required.',
					'connections'
				),
				'fit'  => __(
					'Resize proportionally adjusting the size of scaled image so there are no margins added.',
					'connections'
				),
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' ),
			),
			'default'   => 'crop',
			'schema'    => array(
				'type' => 'string',
				'enum' => array( 'crop', 'fill', 'fit', 'none' ),
			),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'quality',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'images',
			'section'           => 'connections_image_logo',
			'title'             => __( 'JPEG Quality', 'connections' ),
			'desc'              => '%',
			'help'              => '',
			'type'              => 'text',
			'size'              => 'small',
			'default'           => 80,
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' ),
			// Only need to add this once per image size, otherwise it would be run for each field.
			'schema'            => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 100,
				'exclusiveMinimum' => true,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'width',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_logo',
			'title'     => __( 'Width', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 225,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'height',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_logo',
			'title'     => __( 'Height', 'connections' ),
			'desc'      => __( 'px', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => 150,
			'schema'    => array( 'type' => 'integer' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'ratio',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'images',
			'section'   => 'connections_image_logo',
			'title'     => __( 'Crop Mode', 'connections' ),
			'desc'      => '',
			'help'      => '',
			'type'      => 'radio',
			'options'   => array(
				'crop' => __(
					'Crop and resize proportionally to best fit the specified dimensions, maintaining the aspect ratio.',
					'connections'
				),
				'fill' => __(
					'Resize proportionally to fit entire image into the specified dimensions and add margins if required.',
					'connections'
				),
				'fit'  => __(
					'Resize proportionally adjusting the size of scaled image so there are no margins added.',
					'connections'
				),
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' ),
			),
			'default'   => 'fill',
			'schema'    => array(
				'type' => 'string',
				'enum' => array( 'crop', 'fill', 'fit', 'none' ),
			),
		);

		/*
		 * The Search tab fields.
		 */

		$searchOptions['items'] = apply_filters(
			'cn_search_field_options',
			array(
				'family_name'        => __( 'Family Name', 'connections' ),
				'first_name'         => __( 'First Name', 'connections' ),
				'middle_name'        => __( 'Middle Name', 'connections' ),
				'last_name'          => __( 'Last Name', 'connections' ),
				'title'              => __( 'Title', 'connections' ),
				'organization'       => __( 'Organization', 'connections' ),
				'department'         => __( 'Department', 'connections' ),
				'contact_first_name' => __( 'Contact First Name', 'connections' ),
				'contact_last_name'  => __( 'Contact Last Name', 'connections' ),
				'bio'                => __( 'Biography', 'connections' ),
				'notes'              => __( 'Notes', 'connections' ),
				'address_line_1'     => __( 'Address Line One', 'connections' ),
				'address_line_2'     => __( 'Address Line Two', 'connections' ),
				'address_line_3'     => __( 'Address Line Three', 'connections' ),
				'address_line_4'     => __( 'Address Line Four', 'connections' ),
				'address_district'   => __( 'Address District', 'connections' ),
				'address_county'     => __( 'Address County', 'connections' ),
				'address_city'       => __( 'Address City', 'connections' ),
				'address_state'      => __( 'Address State', 'connections' ),
				'address_zipcode'    => __( 'Address Zip Code', 'connections' ),
				'address_country'    => __( 'Address Country', 'connections' ),
				'phone_number'       => __( 'Phone Number', 'connections' ),
			)
		);

		$searchOptions['default'] =
			apply_filters(
				'cn_search_field_options_default',
				array(
					'family_name',
					'first_name',
					'middle_name',
					'last_name',
					'title',
					'organization',
					'department',
					'contact_first_name',
					'contact_last_name',
					'bio',
					'notes',
					'address_line_1',
					'address_line_2',
					'address_line_3',
					'address_line_4',
					'address_district',
					'address_county',
					'address_city',
					'address_state',
					'address_zipcode',
					'address_country',
					'phone_number',
				)
			);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'fields',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'search',
			'section'           => 'connections_search',
			'title'             => __( 'Fields', 'connections' ),
			'desc'              => __( 'The selected fields will be searched.', 'connections' ),
			'help'              => '',
			'type'              => 'multicheckbox',
			'options'           => $searchOptions['items'],
			'default'           => $searchOptions['default'],
			// Only need to add this once, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'setSearchFields' ),
			'schema'            => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
					'enum' => array_keys( $searchOptions['items'] ),
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'fulltext_enabled',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'search',
			'section'   => 'connections_search',
			'title'     => __( 'FULLTEXT', 'connections' ),
			'desc'      => __( 'Enable FULLTEXT query support.', 'connections' ),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'keyword_enabled',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'search',
			'section'   => 'connections_search',
			'title'     => __( 'Keyword Search', 'connections' ),
			'desc'      => __(
				'Enable LIKE query support. Disabling this option can improve search results if the server configuration supports FULLTEXT queries. If you disable this option and searches do not yield results, this indicates that the server does not support FULLTEXT queries. If that is the case, re-enable this option and disable the FULLTEXT option. NOTE: If the FULLTEXT option is disabled, this option must be enabled. Additionally, search terms with three characters or less will be ignored. This can not be changed as this is a database limitation.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		/*
		 * The SEO Tab fields.
		 */
		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'page_title',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'seo',
			'section'   => 'connections_seo_meta',
			'title'     => __( 'Page Title', 'connections' ),
			'desc'      => __(
				'Update the browser tab/window title to reflect the current location being viewed in the directory. For example, the current category name.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'page_desc',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'seo',
			'section'   => 'connections_seo_meta',
			'title'     => __( 'Page Description', 'connections' ),
			'desc'      => __(
				'Use an excerpt of the current category description or current entry bio.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'page_title',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'seo',
			'section'   => 'connections_seo',
			'title'     => __( 'Page Title', 'connections' ),
			'desc'      => __(
				'Update the page title to reflect the current location being viewed in the directory. For example, the current entry name.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		/*
		 * The Advanced tab fields
		 */
		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'character_base',
			'position'          => 05,
			'page_hook'         => $settings,
			'tab'               => 'advanced',
			'section'           => 'connections_permalink',
			'title'             => __( 'Character Base', 'connections' ),
			'desc'              => __(
				'Enter a custom structure for the initial character in the URL.',
				'connections'
			),
			'help'              => '',
			'type'              => 'text',
			'size'              => 'regular',
			'default'           => 'char',
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeURLBase' ),
			// Only need to add this once, otherwise it would be run for each field.
			'schema'            => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'country_base',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Country Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the country in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'country',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'region_base',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Region Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the region (state/province) in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'region',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'locality_base',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Locality Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the locality (city) in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'locality',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'postal_code_base',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Postal Code Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the postal code in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'postal-code',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'district_base',
			'position'  => 59,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'District Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the district in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'district',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'county_base',
			'position'  => 59,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'County Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the county in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'county',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'organization_base',
			'position'  => 60,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Organization Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the organization in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'organization',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'department_base',
			'position'  => 70,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Department Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the department in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'department',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'name_base',
			'position'  => 80,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Name Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the entry slug in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'name',
			'schema'    => array(
				'type' => 'string',
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'name',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Name', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of every entry into a link. Clicking the link will take you to the page with only that entry.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'organization',
			'position'  => 13,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Organization', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of organization into a link. Clicking the link will take you to the page filtered by that organization.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'department',
			'position'  => 16,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Department', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of department into a link. Clicking the link will take you to the page filtered by that department.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'district',
			'position'  => 19,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'District', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of district into a link. Clicking the link will take you to the page filtered by that district.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'county',
			'position'  => 19,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'County', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of county into a link. Clicking the link will take you to the page filtered by that county.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'locality',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Locality', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of locality (city) into a link. Clicking the link will take you to the page filtered by that locality.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'region',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Region', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of region (state/province) into a link. Clicking the link will take you to the page filtered by that region.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'postal_code',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Postal Code', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the postal code into a link. Clicking the link will take you to the page filtered by that postal code.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'country',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Country', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn the name of country into a link. Clicking the link will take you to the page filtered by that country.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'phone',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_link',
			'title'     => __( 'Telephone Number', 'connections' ),
			'desc'      => __(
				'Enabling this option will turn every telephone number into a link that when clicked by the user on a mobile phone or computer with a telephony application installed will dial the number.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'enabled',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'cpt',
			'title'     => __( 'Enable?', 'connections' ),
			'desc'      => __(
				'To add support for Custom Post Types, enable this option.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'supported',
			'position'          => 20,
			'page_hook'         => $settings,
			'tab'               => 'advanced',
			'section'           => 'cpt',
			'title'             => __( 'Enable support for:', 'connections' ),
			'help'              => '',
			'type'              => 'cpt-checkbox-group',
			'options'           => array(),
			'default'           => array(),
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeSupportedCPTs' ),
			// Only need to add this once, otherwise it would be run for each field.
			'schema'            => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'server_key',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'google_maps_geocoding_api',
			'title'     => __( 'Server Key', 'connections' ),
			/* translators: Google Maps API key URI. */
			'desc'      => sprintf( __( 'Enter your Google Maps Geocoding API <strong>Server Key</strong>. Learn how to <a href="%s">get a key</a>.', 'connections' ), 'https://developers.google.com/maps/documentation/geocoding/get-api-key' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'large',
			'default'   => '',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'browser_key',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'google_maps_geocoding_api',
			'title'     => __( 'Browser Key', 'connections' ),
			/* translators: Google Maps API key URI. */
			'desc'      => sprintf( __( 'Enter your Google Maps Geocoding API <strong>Browser Key</strong>. Learn how to <a href="%s">get a key</a>.', 'connections' ), 'https://developers.google.com/maps/documentation/javascript/get-api-key' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'large',
			'default'   => '',
			'schema'    => array( 'type' => 'string' ),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'jquery',
			'position'  => 40,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_compatibility',
			'title'     => 'jQuery',
			'desc'      => __(
				'Themes and plugins sometimes load a version of jQuery that is not bundled with WordPress. This is generally considered bad practice which can result in breaking plugins. Enabling this option will attempt to fix this issue. You should only enable this option at the direction of support.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'strip_rnt',
			'position'  => 50,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_compatibility',
			'title'     => __( 'Templates', 'connections' ),
			'desc'      => __(
				'Themes can break plugin shortcodes that output content on the page causing the content not to render correctly. If the templates do not display as expected try enabling this option.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'debug_messages',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_debug',
			'title'     => __( 'Debug Messages', 'connections' ),
			'desc'      => __( 'Display debug messages.', 'connections' ),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0,
			'schema'    => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'maybe_uninstall',
			'position'          => 10,
			'page_hook'         => $settings,
			'tab'               => 'advanced',
			'section'           => 'connections_uninstall',
			'title'             => __( 'Remove Data on Uninstall', 'connections' ),
			'desc'              => __( 'By selecting the box, all data and settings will be permanently erased upon deleting the plugin. It is important to note that this action is irreversible. As such, creating a site backup before proceeding is highly recommended.', 'connections' ),
			'help'              => '',
			'type'              => 'checkbox',
			'default'           => 0,
			// Only need to add this once on this tab, otherwise it would be run for each field.
			'sanitize_callback' => array( __CLASS__, 'maybeUninstall' ),
			'schema'            => array(
				'type'             => 'integer',
				'minimum'          => 0,
				'maximum'          => 1,
				'exclusiveMinimum' => false,
				'exclusiveMaximum' => false,
			),
		);

		return $fields;
	}

	/**
	 * Callback for the "Login Required" settings field.
	 * This ensures all roles are set to have the connections_view_public
	 * capability to ensures all roles can at least view the public entries.
	 *
	 * @internal
	 * @since 0.7.3
	 *
	 * @param string[] $loginRequired
	 *
	 * @return string[]
	 */
	public static function setAllowPublic( $loginRequired ) {
		global $wp_roles;

		if ( $loginRequired ) {

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}

			$roles = $wp_roles->get_names();

			foreach ( $roles as $role => $name ) {

				cnRole::add( $role, 'connections_view_public' );
			}
		}

		return $loginRequired;
	}

	/**
	 * Callback function to sanitize the address fieldset settings.
	 *
	 * @internal
	 * @since 8.7
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	public static function sanitizeAddressFieldsetSettings( $settings ) {

		$settings = self::prepareCommonRepeatableSettings( $settings );

		$active = _array::get( $settings, 'address-types.active', array() );

		// If no address types have been selected, force select the top type.
		if ( empty( $active ) ) {

			$types    = _array::get( $settings, 'address-types.type' );
			$keys     = array_flip( $types );
			$active[] = array_shift( $keys );
		}

		_array::set( $settings, 'address-types.active', $active );

		return $settings;
	}

	/**
	 * Callback function to sanitize the phone fieldset settings.
	 *
	 * @internal
	 * @since 8.8
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	public static function sanitizePhoneFieldsetSettings( $settings ) {

		$settings = self::prepareCommonRepeatableSettings( $settings );

		$active = _array::get( $settings, 'phone-types.active', array() );

		// If no phone types have been selected, force select the top type.
		if ( empty( $active ) ) {

			$types    = _array::get( $settings, 'phone-types.type' );
			$keys     = array_flip( $types );
			$active[] = array_shift( $keys );
		}

		_array::set( $settings, 'phone-types.active', $active );

		return $settings;
	}

	/**
	 * Callback function to sanitize the email fieldset settings.
	 *
	 * @internal
	 * @since 8.9
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	public static function sanitizeEmailFieldsetSettings( $settings ) {

		$settings = self::prepareCommonRepeatableSettings( $settings );

		$active = _array::get( $settings, 'email-types.active', array() );

		// If no email types have been selected, force select the top type.
		if ( empty( $active ) ) {

			$types    = _array::get( $settings, 'email-types.type' );
			$keys     = array_flip( $types );
			$active[] = array_shift( $keys );
		}

		_array::set( $settings, 'email-types.active', $active );

		return $settings;
	}

	/**
	 * Callback function to sanitize the messenger fieldset settings.
	 *
	 * @internal
	 * @since 8.16
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	public static function sanitizeMessengerFieldsetSettings( $settings ) {

		$settings = self::prepareCommonRepeatableSettings( $settings );

		$active = _array::get( $settings, 'messenger-types.active', array() );

		// If no email types have been selected, force select the top type.
		if ( empty( $active ) ) {

			$types    = _array::get( $settings, 'messenger-types.type' );
			$keys     = array_flip( $types );
			$active[] = array_shift( $keys );
		}

		_array::set( $settings, 'messenger-types.active', $active );

		return $settings;
	}

	/**
	 * Callback function to sanitize the link fieldset settings.
	 *
	 * @internal
	 * @since 8.17
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	public static function sanitizeLinkFieldsetSettings( $settings ) {

		$settings = self::prepareCommonRepeatableSettings( $settings );

		$active = _array::get( $settings, 'link-types.active', array() );

		// If no link types have been selected, force select the top type.
		if ( empty( $active ) ) {

			$types    = _array::get( $settings, 'link-types.type' );
			$keys     = array_flip( $types );
			$active[] = array_shift( $keys );
		}

		_array::set( $settings, 'link-types.active', $active );

		return $settings;
	}

	/**
	 * Callback function to sanitize the date fieldset settings.
	 *
	 * @internal
	 * @since 8.22
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	public static function sanitizeDateFieldsetSettings( $settings ) {

		$settings = self::prepareCommonRepeatableSettings( $settings );

		$active = _array::get( $settings, 'date-types.active', array() );

		// If no date types have been selected, force select the top type.
		if ( empty( $active ) ) {

			$types    = _array::get( $settings, 'date-types.type' );
			$keys     = array_flip( $types );
			$active[] = array_shift( $keys );
		}

		_array::set( $settings, 'date-types.active', $active );

		return $settings;
	}

	/**
	 * Callback function to sanitize the social network fieldset settings.
	 *
	 * @internal
	 * @since 8.45
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	public static function sanitizeSocialNetworkFieldsetSettings( $settings ) {

		$settings = self::prepareCommonRepeatableSettings( $settings );

		$active = _array::get( $settings, 'social-network-types.active', array() );

		// If no date types have been selected, force select the top type.
		if ( empty( $active ) ) {

			$types    = _array::get( $settings, 'social-network-types.icon' );
			$keys     = array_keys( $types );
			$active[] = array_shift( $keys );
		}

		_array::set( $settings, 'social-network-types.active', $active );

		return $settings;
	}

	/**
	 * Repeatable fields have duplicate settings that are shared. This helper method is to remove code duplication.
	 *
	 * @since 10.4.23
	 *
	 * @param array $settings The array of setting to validate and sanitize.
	 *
	 * @return array
	 */
	private static function prepareCommonRepeatableSettings( $settings ) {

		$count      = _array::get( $settings, 'count', 0 );
		$repeatable = _array::get( $settings, 'repeatable', 0 );

		$count      = _validate::isPositiveInteger( $count ) ? absint( $count ) : 0;
		$repeatable = 0 < $count ? $repeatable : 1;

		_array::set( $settings, 'count', (string) $count );
		_array::set( $settings, 'repeatable', (string) $repeatable );

		return $settings;
	}

	/**
	 * Callback for the `cn_settings_field-sortable_input-repeatable-item` filter.
	 *
	 * Do not display the "Remove" button if the address type is currently in use/associated with an address.
	 *
	 * @see cnSettingsAPI::field()
	 *
	 * @internal
	 * @since 8.7
	 *
	 * @param string $html
	 * @param array  $atts
	 *
	 * @return string
	 */
	public static function fieldsetTypeRemovable( $html, $atts ) {

		/**
		 * @var array $field
		 * @var string $key
		 * @var string $hidden
		 * @var string $checkbox
		 * @var string $input
		 * @var string $removeButton
		 */
		extract( $atts );

		switch ( $field['id'] ) {

			case 'address-types':
				$callable = array( 'cnOptions', 'getAddressTypesInUse' );
				break;

			case 'phone-types':
				$callable = array( 'cnOptions', 'getPhoneTypesInUse' );
				break;

			case 'email-types':
				$callable = array( 'cnOptions', 'getEmailTypesInUse' );
				break;

			case 'messenger-types':
				$callable = array( 'cnOptions', 'getMessengerTypesInUse' );
				break;

			case 'link-types':
				$callable = array( 'cnOptions', 'getLinkTypesInUse' );
				break;

			case 'date-types':
				$callable = array( 'cnOptions', 'getDateTypesInUse' );
				break;
		}

		$inuse = call_user_func( $callable );

		$html = sprintf(
			'<li><i class="fa fa-sort"></i> %1$s%2$s%3$s %4$s</li>',
			$hidden,
			$checkbox,
			$input,
			! array_key_exists( $key, $field['options']['items'] ) && ! array_key_exists( $key, $inuse ) ? $removeButton : ''
		);

		return $html;
	}

	/**
	 * Callback for the `cn_settings_field-sortable_iconpicker-repeatable-item` filter.
	 *
	 * Do not display the "Remove" button if the address type is currently in use/associated with a social network.
	 *
	 * @see cnSettingsAPI::field()
	 *
	 * @internal
	 * @since 8.45
	 *
	 * @param string $html
	 * @param array  $atts
	 *
	 * @return string
	 */
	public static function socialNetworkTypesInUse( $html, $atts ) {

		/**
		 * @var array $field
		 * @var string $key
		 * @var string $hidden
		 * @var string $checkbox
		 * @var string $iconButton
		 * @var string $input
		 * @var string $removeButton
		 */
		extract( $atts );

		$inuse = cnOptions::getSocialNetworkTypesInUse();

		$html = sprintf(
			'<li><i class="fa fa-sort"></i> %1$s%2$s %3$s %4$s %5$s</li>',
			$hidden,
			$checkbox,
			$iconButton,
			$input,
			! array_key_exists( $key, $field['options']['items'] ) && ! array_key_exists( $key, $inuse ) ? $removeButton : ''
		);

		return $html;
	}

	/**
	 * Callback function to sanitize the image settings.
	 *
	 * @internal
	 * @since 0.7.7
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function sanitizeImageSettings( $settings ) {

		$defaults = array(
			'quality' => 80,
			'height'  => 150,
			'width'   => 225,
			'ratio'   => 'crop',
		);

		$settings = cnSanitize::args( $settings, $defaults );

		// Ensure positive int values.
		$settings['quality'] = absint( $settings['quality'] );
		$settings['height']  = absint( $settings['height'] );
		$settings['width']   = absint( $settings['width'] );

		// If the values is empty, set a default.
		$settings['quality'] = empty( $settings['quality'] ) ? 80 : $settings['quality'];
		$settings['height']  = empty( $settings['height'] ) ? 150 : $settings['height'];
		$settings['width']   = empty( $settings['width'] ) ? 225 : $settings['width'];

		// The valid ratio options.
		$ratio = array( 'crop', 'fill', 'fit', 'none' );

		// Make sure the value is one of the permitted options and if it is not, set it to the 'crop' value.
		$settings['ratio'] = in_array( $settings['ratio'], $ratio ) ? $settings['ratio'] : 'crop';

		return $settings;
	}

	/**
	 * Sanitize the slug to help prevent some unfriendly slugs that users might enter
	 *
	 * @internal
	 * @since 0.7.3
	 *
	 * @param string[] $settings An array of permalink slugs.
	 *
	 * @return string[]
	 */
	public static function sanitizeURLBase( $settings ) {

		/*
		 * Make sure there is a value saved for each permalink base.
		 */
		$defaults = array(
			'character_base'    => 'char',
			'category_base'     => 'cat',
			'name_base'         => 'name',
			'department_base'   => 'department',
			'organization_base' => 'organization',
			'locality_base'     => 'locality',
			'district_base'     => 'district',
			'county_base'       => 'county',
			'postal_code_base'  => 'postal_code',
			'region_base'       => 'region',
			'country_base'      => 'country',
		);

		$sanitized = array();

		foreach ( $defaults as $key => $slug ) {

			if ( ! array_key_exists( $key, $settings ) || empty( $settings[ $key ] ) ) {

				$sanitized[ $key ] = $slug;

			} else {

				$sanitized[ $key ] = _string::toKebabCase( $settings[ $key ] );
			}
		}

		self::flushRewriteRules();

		return $sanitized;
	}

	/**
	 * Callback action to sanitize the user selected supported CTPs.
	 *
	 * @internal
	 * @since 8.5.14
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function sanitizeSupportedCPTs( $settings ) {

		self::flushRewriteRules();

		return $settings;
	}

	/**
	 * This option is added for a check that will force a flush_rewrite() in connectionsLoad::adminInit().
	 *
	 * @internal
	 * @since 8.5.14
	 */
	private static function flushRewriteRules() {

		update_option( 'connections_flush_rewrite', '1' );
	}

	/**
	 * Callback for the settings search fields.
	 * Saves the user's search field choices and sets up the FULLTEXT indexes.
	 *
	 * @TODO this will fail on tables that do not support FULLTEXT. Should somehow check before processing
	 * and set FULLTEXT support to FALSE
	 *
	 * @internal
	 * @since 0.7.3
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function setSearchFields( $settings ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$fields = $settings['fields'];
		// var_dump($fields);

		// $wpdb->show_errors();

		/*
		 * The permitted fields that are supported for FULLTEXT searching.
		 */
		/*$permittedFields['entry'] = array( 'family_name' ,
										'first_name' ,
										'middle_name' ,
										'last_name' ,
										'title' ,
										'organization' ,
										'department' ,
										'contact_first_name' ,
										'contact_last_name' ,
										'bio' ,
										'notes' );
		$permittedFields['address'] = array( 'line_1' ,
										'line_2' ,
										'line_3' ,
										'city' ,
										'state' ,
										'zipcode' ,
										'country' );
		$permittedFields['phone'] = array( 'number' );*/


		/*
		 * Build the array to store the user preferences.
		 */
		$search['family_name']        = in_array( 'family_name', $fields );
		$search['first_name']         = in_array( 'first_name', $fields );
		$search['middle_name']        = in_array( 'middle_name', $fields );
		$search['last_name']          = in_array( 'last_name', $fields );
		$search['title']              = in_array( 'title', $fields );
		$search['organization']       = in_array( 'organization', $fields );
		$search['department']         = in_array( 'department', $fields );
		$search['contact_first_name'] = in_array( 'contact_first_name', $fields );
		$search['contact_last_name']  = in_array( 'contact_last_name', $fields );
		$search['bio']                = in_array( 'bio', $fields );
		$search['notes']              = in_array( 'notes', $fields );

		$search['address_line_1']   = in_array( 'address_line_1', $fields );
		$search['address_line_2']   = in_array( 'address_line_2', $fields );
		$search['address_line_3']   = in_array( 'address_line_3', $fields );
		$search['address_line_4']   = in_array( 'address_line_4', $fields );
		$search['address_district'] = in_array( 'address_district', $fields );
		$search['address_county']   = in_array( 'address_county', $fields );
		$search['address_city']     = in_array( 'address_city', $fields );
		$search['address_state']    = in_array( 'address_state', $fields );
		$search['address_zipcode']  = in_array( 'address_zipcode', $fields );
		$search['address_country']  = in_array( 'address_country', $fields );

		$search['phone_number'] = in_array( 'phone_number', $fields );

		// var_dump($search);

		/*
		 * Drop the current FULLTEXT indexes.
		 */
		$indexExists = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_TABLE . ' WHERE KEY_NAME = \'search\'' ); // var_dump($indexExists);
		if ( $indexExists > 0 ) {
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_TABLE . ' DROP INDEX search' );
		}

		$indexExists = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE KEY_NAME = \'search\'' ); // var_dump($indexExists);
		if ( $indexExists > 0 ) {
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_ADDRESS_TABLE . ' DROP INDEX search' );
		}

		$indexExists = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_PHONE_TABLE . ' WHERE KEY_NAME = \'search\'' ); // var_dump($indexExists);
		if ( $indexExists > 0 ) {
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_PHONE_TABLE . ' DROP INDEX search' );
		}

		/*
		 * Recreate the FULLTEXT indexes based on the user choices
		 */

		// Build the arrays that will be imploded in the query statement.
		if ( $search['family_name'] ) {
			$column['entry'][] = 'family_name';
		}

		if ( $search['first_name'] ) {
			$column['entry'][] = 'first_name';
		}

		if ( $search['middle_name'] ) {
			$column['entry'][] = 'middle_name';
		}

		if ( $search['last_name'] ) {
			$column['entry'][] = 'last_name';
		}

		if ( $search['title'] ) {
			$column['entry'][] = 'title';
		}

		if ( $search['organization'] ) {
			$column['entry'][] = 'organization';
		}

		if ( $search['department'] ) {
			$column['entry'][] = 'department';
		}

		if ( $search['contact_first_name'] ) {
			$column['entry'][] = 'contact_first_name';
		}

		if ( $search['contact_last_name'] ) {
			$column['entry'][] = 'contact_last_name';
		}

		if ( $search['bio'] ) {
			$column['entry'][] = 'bio';
		}

		if ( $search['notes'] ) {
			$column['entry'][] = 'notes';
		}

		if ( $search['address_line_1'] ) {
			$column['address'][] = 'line_1';
		}

		if ( $search['address_line_2'] ) {
			$column['address'][] = 'line_2';
		}
		if ( $search['address_line_3'] ) {
			$column['address'][] = 'line_3';
		}
		if ( $search['address_line_4'] ) {
			$column['address'][] = 'line_4';
		}

		if ( $search['address_district'] ) {
			$column['address'][] = 'district';
		}

		if ( $search['address_county'] ) {
			$column['address'][] = 'county';
		}

		if ( $search['address_city'] ) {
			$column['address'][] = 'city';
		}

		if ( $search['address_state'] ) {
			$column['address'][] = 'state';
		}

		if ( $search['address_zipcode'] ) {
			$column['address'][] = 'zipcode';
		}

		if ( $search['address_country'] ) {
			$column['address'][] = 'country';
		}

		if ( $search['phone_number'] ) {
			$column['phone'][] = 'number';
		}

		// Add the FULLTEXT indexes.
		if ( isset( $settings['fulltext_enabled'] ) ) {

			if ( ! empty( $column['entry'] ) ) {
				$wpdb->query(
					'ALTER TABLE ' . CN_ENTRY_TABLE . ' ADD FULLTEXT search (' . implode( ',', $column['entry'] ) . ')'
				);
			}

			if ( ! empty( $column['address'] ) ) {
				$wpdb->query(
					'ALTER TABLE ' . CN_ENTRY_ADDRESS_TABLE . ' ADD FULLTEXT search (' . implode( ',', $column['address'] ) . ')'
				);
			}

			if ( ! empty( $column['phone'] ) ) {
				$wpdb->query(
					'ALTER TABLE ' . CN_ENTRY_PHONE_TABLE . ' ADD FULLTEXT search (' . implode( ',', $column['phone'] ) . ')'
				);
			}
		}
		// $wpdb->hide_errors();

		// die;

		// Ensure at least keyword search enabled if user decides to try to disable both keyword and FULLTEXT searching.
		if ( empty( $settings['fulltext_enabled'] ) && empty( $settings['keyword_enabled'] ) ) {
			$settings['keyword_enabled'] = 1;
		}

		return $settings;
	}

	public static function setGEOBase( $settings ) {

		$regions = cnGeo::getRegions( $settings['base_country'] );

		if ( ! array_key_exists( $settings['base_region'], $regions ) ) {

			$settings['base_region'] = current( array_keys( $regions ) );
		}

		return $settings;
	}

	/**
	 * Callback for the `maybe_uninstall` setting option.
	 *
	 * @since 10.4.63
	 *
	 * @param array $settings The settings option values for the `maybe_uninstall` setting.
	 *
	 * @return array
	 */
	public static function maybeUninstall( $settings ) {

		$value     = _array::get( $settings, 'maybe_uninstall', 0 );
		$sanitized = _sanitize::integer( $value );

		_array::set( $settings, 'maybe_uninstall', $sanitized );

		return $settings;
	}
}
