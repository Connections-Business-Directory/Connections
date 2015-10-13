<?php

/**
 * Register the tabs, settings sections and section settings using the Settings API.
 *
 * @package     Connections
 * @subpackage  Manage the settings.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnRegisterSettings
 */
class cnRegisterSettings {

	/**
	 * Register the tabs for the Connections : Settings admin page.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.3.0
	 * @param $tabs array
	 * @return array
	 */
	public static function registerSettingsTabs( $tabs ) {

		$settings = 'connections_page_connections_settings';

		// Register the core tab banks.
		$tabs[] = array(
			'id'        => 'general' ,
			'position'  => 10 ,
			'title'     => __( 'General' , 'connections' ) ,
			'page_hook' => $settings
		);

		$tabs[] = array(
			'id'        => 'display' ,
			'position'  => 15 ,
			'title'     => __( 'Display' , 'connections' ) ,
			'page_hook' => $settings
		);

		$tabs[] = array(
			'id'        => 'images' ,
			'position'  => 20 ,
			'title'     => __( 'Images' , 'connections' ) ,
			'page_hook' => $settings
		);

		$tabs[] = array(
			'id'        => 'search' ,
			'position'  => 30 ,
			'title'     => __( 'Search' , 'connections' ) ,
			'page_hook' => $settings
		);

		$tabs[] = array(
			'id'        => 'seo' ,
			'position'  => 40 ,
			'title'     => __( 'SEO' , 'connections' ) ,
			'page_hook' => $settings
		);

		$tabs[] = array(
			'id'        => 'advanced' ,
			'position'  => 60 ,
			'title'     => __( 'Advanced' , 'connections' ) ,
			'page_hook' => $settings
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
			'tab'       => 'general',
			'id'        => 'connections_home_page',
			'position'  => 5,
			'title'     => __( 'Home', 'connections' ),
			'callback'  => create_function(
				'',
				'echo \'' . __(
					'Choose the page where your directory is located. This should be the page where you used the [connections] shortcode.',
					'connections'
				) . '\';'
			),
			'page_hook' => $settings
		);

		$sections[] = array(
			'tab'       => 'general',
			'id'        => 'connections_login',
			'position'  => 10,
			'title'     => __( 'Require Login', 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);

		$sections[] = array(
			'plugin_id' => 'connections',
			'tab'       => 'general',
			'id'        => 'category',
			'position'  => 20,
			'title'     => __( 'Default Category', 'connections' ),
			'callback'  => '',
			'page_hook' => $settings,
		);

		$sections[] = array(
			'plugin_id' => 'connections',
			'tab'       => 'general',
			'id'        => 'geo',
			'position'  => 30,
			'title'     => __( 'Base country and region.', 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);

		$sections[] = array(
			'tab'       => 'general',
			'id'        => 'connections_visibility',
			'position'  => 40,
			'title'     => __( 'Shortcode Visibility Overrides', 'connections' ),
			'callback'  => create_function(
				'',
				'echo \'' . __(
					'The [connections] shortcode has two options available to show an entry or an entire directory if the entry(ies) has been set to private or the user is required to be logged to view the directory. These options, when used, will only be applied to the current shortcode instance.',
					'connections'
				) . '\';'
			),
			'page_hook' => $settings
		);

		/*
		 * The sections registered to the Display tab.
		 */
		$sections[] = array(
			'tab'       => 'display',
			'id'        => 'connections_display_general',
			'position'  => 5,
			'title'     => __( 'General' , 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'display',
			'id'        => 'connections_display_results',
			'position'  => 10,
			'title'     => __( 'Results List' , 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);
		$sections[] = array(
			'plugin_id' => 'connections',
			'tab'       => 'display',
			'id'        => 'list_actions',
			'position'  => 15,
			'title'     => __( 'Result List Actions' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo \'' . __( 'Enable or disable various actions that are displayed above the result list.', 'connections' ) . '\';'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'display',
			'id'        => 'connections_display_list',
			'position'  => 20,
			'title'     => __( 'Result List' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo \'' . esc_html__( 'The following settings are applied when viewing the entry results list.', 'connections' ) . '\';'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'plugin_id' => 'connections',
			'tab'       => 'display',
			'id'        => 'entry_actions',
			'position'  => 25,
			'title'     => __( 'Entry Actions' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo \'' . esc_html__( 'Enable or disable various actions that are shown above the single entry in the detail view.', 'connections' ) . '\';'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'display',
			'id'        => 'connections_display_single',
			'position'  => 30,
			'title'     => __( 'Single Entry' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo \'' . __( 'The following settings are applied when viewing a single entry in the detail view. Which details are shown are dependant on the current template being used.', 'connections' ) . '\';'
				),
			'page_hook' => $settings
		);

		/*
		 * The sections registered to the Images tab.
		 */
		$sections[] = array(
			'tab'       => 'images',
			'id'        => 'connections_image_instructions',
			'position'  => 10,
			'title'     => __( 'Image and Logo Instructions' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'When an image or logo is uploaded to an entry, various sizes are created and cached. This helps to reduce server load during the rendering of the directory. If these settings are changed, they will only affect images uploaded after the change has been made. All previous images will remain at their previously cached sizes. NOTE: the active template will determine which image(s) is used or may dynaically create a custom image size.', 'connections' ) . '";'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'images',
			'id'        => 'connections_image_thumbnail',
			'position'  => 10,
			'title'     => __( 'Thumbnail Image' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'Default settings are: Quality: 80%; Width: 80px; Height: 54px; Crop', 'connections' ) . '";'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'images',
			'id'        => 'connections_image_medium',
			'position'  => 20,
			'title'     => __( 'Medium Image' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'Default settings are: Quality: 80%; Width: 225px; Height: 150px; Crop', 'connections' ) . '";'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'images',
			'id'        => 'connections_image_large',
			'position'  => 30,
			'title'     => __( 'Large Image' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'Default settings are: Quality: 80%; Width: 300px; Height: 225px; Crop', 'connections' ) . '";'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'images',
			'id'        => 'connections_image_logo',
			'position'  => 30,
			'title'     => __( 'Logo' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'Default settings are: Quality: 80%; Width: 225px; Height: 150px; Fill', 'connections' ) . '";'
				),
			'page_hook' => $settings
		);

		/*
		 * The sections registered to the Search tab.
		 */
		$sections[] = array(
			'tab'       => 'search',
			'id'        => 'connections_search_instructions',
			'position'  => 10,
			'title'     => __( 'Search Instructions' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'Search on the front end of the website is enabled in select premium templates only and does not integrate with the core WordPress search. None of the supplied templates include the search feature. These settings will affect the results of search on both the Manage admin page and the front end of the website.', 'connections' ) . '";'
				),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'search',
			'id'        => 'connections_search',
			'position'  => 20,
			'title'     => __( 'Search Fields' , 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);

		/*
		 * The sections registered to the SEO tab.
		 */
		$sections[] = array(
			'tab'       => 'seo',
			'id'        => 'connections_seo_meta',
			'position'  => 10,
			'title'     => __( 'Page Meta' , 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab'       => 'seo',
			'id'        => 'connections_seo',
			'position'  => 20,
			'title'     => __( 'Page Display' , 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);

		/*
		 * The sections registered to the Advance tab.
		 */
		$sections[] = array(
			'tab'       => 'advanced',
			'id'        => 'connections_permalink',
			'position'  => 10,
			'title'     => __( 'Permalink' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'Configure permalink support. Avoid using permalink structure names that will conflict with WordPress, such category and tag.' , 'connections' ) . '";'
				),
			'page_hook' => $settings
		);

		$sections[] = array(
			'tab'       => 'advanced',
			'id'        => 'connections_link',
			'position'  => 15,
			'title'     => __( 'Links' , 'connections' ),
			'callback'  => create_function(
				'',
				'echo "' . __( 'Enable certain entry data to become links.' , 'connections' ) . '";'
				),
			'page_hook' => $settings
		);

		$sections[] = array(
			'plugin_id' => 'connections',
			'tab'       => 'advanced',
			'id'        => 'compatibility',
			'position'  => 20,
			'title'     => __( 'Compatibility' , 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
		);

		$sections[] = array(
			'tab'       => 'advanced',
			'id'        => 'connections_debug',
			'position'  => 30,
			'title'     => __( 'Debug' , 'connections' ),
			'callback'  => '',
			'page_hook' => $settings
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

		/*
		 * The General tab fields.
		 */
		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => 'page_id',
			'position'          => 5,
			'page_hook'         => $settings,
			'tab'               => 'general',
			'section'           => 'connections_home_page',
			'title'             => __( 'Page', 'connections' ),
			'desc'              => '',
			'help'              => '',
			'type'              => 'page',
			'show_option_none'  => __( 'Select Page', 'connections' ),
			'option_none_value' => '0'
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
			'sanitize_callback' => array( 'cnRegisterSettings', 'setAllowPublic' )
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
			'default'   => 'Please login to view the directory.'
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
			'options'           => cnGEO::getCountries(),
			'default'           => 'US',
			// Only need to add this once per image size, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'setGEOBase' )
		);

		// cnGEO::getRegions() when called without the $country code @param
		// will use the result from cnOptions::getBaseCountry() to define which
		// regions to return. If there are no regions an empty array will be returned.
		// So, if there are no regions, the is no reason to render this option.
		$regions = cnGEO::getRegions();

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
				'options'   => cnGEO::getRegions(),
				'default'   => cnOptions::getBaseRegion()
			);
		}

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'allow_public_override',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'general',
			'section'   => 'connections_visibility',
			'title'     => __( 'Enable public_override', 'connections' ),
			'desc'      => __(
				'By default all entries whose status is Public will be visible to all site visitors or registered users not logged in. If the option to require login has been enabled, the <em>public_override</em> shortcode option allows you to override requiring the site vistor to be logged in. This setting is useful in multi author sites where those authors may have a need to display specific entries to the public. For security reasons this option is disabled by default. If checked, this enables this shortcode option.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0
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
			'default'   => 0
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
			'desc'      => __(
				'<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">Documentation on date and time formatting</a>.',
				'connections'
			),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'small',
			'default'   => esc_attr( get_option( 'date_format' ) )
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
			'default'   => 1
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
		);

		$listActionsOptions['items']    = apply_filters(
			'cn_list_action_options',
			array(
				'view_all' => __(
					'Show a "View All" link. When this option is enabled a "View All" link will be displayed.',
					'connections'
				)
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
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'content_block',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_list',
			'title'     => __( 'Content Blocks', 'connections' ),
			'desc'      => __(
				'Whether a content block should be shown. Read more by clicking this link. NOTE: Content block support must be enabled in the template to have an effect. All the core templates support this feature. If you have purchase a commercial template, it may need to be updated in order to support this feature.',
				'connections'
			),
			'help'      => '',
			'type'      => 'sortable_checklist',
			'options'   => cnOptions::getContentBlocks( NULL, 'list' ),
			'default'   => 0,
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
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'template',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_single',
			'title'     => __( 'Template', 'connections' ),
			'desc'      => __(
				'Display a single entry using the active template based on entry type. For example, if the entry is an organization it will be displayed using the template that is activated for the "Organization" template type found on the Connections : Templates admin page.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 0
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'content_block',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'display',
			'section'   => 'connections_display_single',
			'title'     => __( 'Content Blocks', 'connections' ),
			'desc'      => __(
				'Whether a content block should be shown. Read more by clicking this link. NOTE: Content block support must be enabled in the template to have an effect. All the core templates support this feature. If you have purchase a commercial template, it may need to be updated in order to support this feature.',
				'connections'
			),
			'help'      => '',
			'type'      => 'sortable_checklist',
			'options'   => cnOptions::getContentBlocks( NULL, 'single' ),
			'default'   => 0,
		);

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
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' )
			// Only need to add this once per image size, otherwise it would be run for each field.
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
			'default'   => 80
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
			'default'   => 54
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
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' )
			),
			'default'   => 'crop'
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
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' )
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
			'default'   => 225
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
			'default'   => 150
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
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' )
			),
			'default'   => 'crop'
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
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' )
			// Only need to add this once per image size, otherwise it would be run for each field.
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
			'default'   => 300
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
			'default'   => 225
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
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' )
			),
			'default'   => 'crop'
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
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeImageSettings' )
			// Only need to add this once per image size, otherwise it would be run for each field.
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
			'default'   => 225
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
			'default'   => 150
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
				'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' )
			),
			'default'   => 'fill'
		);

		/*
		 * The Search tab fields.
		 */
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
			'options'           => array(
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
				'address_city'       => __( 'Address City', 'connections' ),
				'address_state'      => __( 'Address State', 'connections' ),
				'address_zipcode'    => __( 'Address Zip Code', 'connections' ),
				'address_country'    => __( 'Address Country', 'connections' ),
				'phone_number'       => __( 'Phone Number', 'connections' )
			),
			'default'           => array(
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
				'address_city',
				'address_state',
				'address_zipcode',
				'address_country',
				'phone_number'
			),
			// Only need to add this once, otherwise it would be run for each field.
			'sanitize_callback' => array( 'cnRegisterSettings', 'setSearchFields' )
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
			'default'   => 1
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
			'default'   => 1
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
			'default'   => 1
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
			'default'   => 1
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
			'default'   => 1
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
			'sanitize_callback' => array( 'cnRegisterSettings', 'sanitizeURLBase' )
			// Only need to add this once, otherwise it would be run for each field.
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'category_base',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_permalink',
			'title'     => __( 'Category Base', 'connections' ),
			'desc'      => __( 'Enter a custom structure for the category in the URL.', 'connections' ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => 'cat'
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
			'default'   => 'country'
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
			'default'   => 'region'
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
			'default'   => 'locality'
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
			'default'   => 'postal-code'
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
			'default'   => 'organization'
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
			'default'   => 'department'
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
			'default'   => 'name'
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
			'default'   => 1
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'google_maps_api',
			'position'  => 10,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_compatibility',
			'title'     => __( 'Google Maps API v3', 'connections' ),
			'desc'      => __(
				'If the current active theme or another plugin loads the Google Maps API v3 uncheck this to prevent Connections from loading the Google Maps API. This could prevent potential conflicts. NOTE: This only applies to templates that utilize Google Maps.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'javascript_footer',
			'position'  => 20,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'connections_compatibility',
			'title'     => __( 'JavaScript', 'connections' ),
			'desc'      => __(
				'By default Connections loads it\'s JavaScripts in the page footer uncheck this box to load them in the page header.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1
		);

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => 'css',
			'position'  => 30,
			'page_hook' => $settings,
			'tab'       => 'advanced',
			'section'   => 'compatibility',
			'title'     => 'CSS',
			'desc'      => __(
				'Enqueue the core styles. Disable this option if you do not want the core styles to be loaded.',
				'connections'
			),
			'help'      => '',
			'type'      => 'checkbox',
			'default'   => 1
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
			'default'   => 0
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
			'default'   => 0
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
			'default'   => 0
		);

		return $fields;
	}

	/**
	 * Callback for the "Login Required" settings field.
	 * This ensure all roles are set to have the connections_view_public
	 * capability to ensures all roles can at least view the public entries.
	 *
	 * @access private
	 * @since 0.7.3
	 * @return int
	 */
	public static function setAllowPublic( $loginRequired ) {
		global $wp_roles;

		if ( $loginRequired ) {

			if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

			$roles = $wp_roles->get_names();

			foreach ( $roles as $role => $name ) {

				cnRole::add( $role, 'connections_view_public' );
			}
		}

		return $loginRequired;
	}

	public static function sanitizeImageSettings( $settings ) {

		$validate = new cnValidate();

		$defaults = array(
			'quality' => 80,
			'height'  => 150,
			'width'   => 225,
			'ratio'   => 'crop'
			);

		// Use this instead of wp_parse_args since it doesn't drop invalid atts. NOTE: could use shortcode_atts() instead, I suppose.
		$settings = $validate->attributesArray( $defaults, $settings );

		// Ensure positive int values
		$settings['quality'] = absint( $settings['quality'] );
		$settings['height']  = absint( $settings['height'] );
		$settings['width']   = absint( $settings['width'] );

		// If the values is empty, set a default.
		$settings['quality'] = empty( $settings['quality'] ) ? 80 : $settings['quality'];
		$settings['height']  = empty( $settings['height'] ) ? 150 : $settings['height'];
		$settings['width']   = empty( $settings['width'] ) ? 225 : $settings['width'];

		// The valid ratio options
		$ratio = array( 'crop', 'fill', 'fit', 'none' );

		// Make sure the value is one of the permitted options and if it is not, set it to the 'crop' value.
		$settings['ratio'] = in_array( $settings['ratio'], $ratio ) ? $settings['ratio'] : 'crop';

		return $settings;
	}

	/**
	 * Sanitize the slug to help prevent some unfriendly slugs that users might enter
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses update_option()
	 * @uses sanitize_title_with_dashes()
	 * @param array $settings
	 * @return array
	 */
	public static function sanitizeURLBase( $settings ) {

		/*
		 * Make sure there is a value saved for each permalink base.
		 */
		if ( ! isset( $settings['character_base'] ) || empty( $settings['character_base'] ) ) $settings['character_base']          = 'char';
		if ( ! isset( $settings['category_base'] ) || empty( $settings['category_base'] ) ) $settings['category_base']             = 'cat';
		if ( ! isset( $settings['country_base'] ) || empty( $settings['country_base'] ) ) $settings['country_base']                = 'country';
		if ( ! isset( $settings['region_base'] ) || empty( $settings['region_base'] ) ) $settings['region_base']                   = 'region';
		if ( ! isset( $settings['locality_base'] ) || empty( $settings['locality_base'] ) ) $settings['locality_base']             = 'locality';
		if ( ! isset( $settings['postal_code_base'] ) || empty( $settings['postal_code_base'] ) ) $settings['postal_code_base']    = 'postal_code';
		if ( ! isset( $settings['name_base'] ) || empty( $settings['name_base'] ) ) $settings['name_base']                         = 'name';
		if ( ! isset( $settings['organization_base'] ) || empty( $settings['organization_base'] ) ) $settings['organization_base'] = 'organization';
		if ( ! isset( $settings['department_base'] ) || empty( $settings['department_base'] ) ) $settings['department_base']       = 'department';

		$settings = array_map( array( 'cnFormatting', 'sanitizeStringStrong' ), $settings );

		// This option is added for a check that will force a flush_rewrite() in connectionsLoad::adminInit().
		update_option('connections_flush_rewrite', '1');

		return $settings;
	}

	/**
	 * Callback for the settings search fields.
	 * Saves the user's search field choices and sets up the FULLTEXT indexes.
	 *
	 * @TODO this will fail on tables that do not support FULLTEXT. Should somehow check before processing
	 * and set FULLTEXT support to FALSE
	 *
	 * @access private
	 * @since 0.7.3
	 * @param array $settings
	 * @return array
	 */
	public static function setSearchFields( $settings ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$fields = $settings['fields'];
		//var_dump($fields);

		//$wpdb->show_errors();

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
		$search['family_name']        = in_array( 'family_name' , $fields ) ? TRUE : FALSE;
		$search['first_name']         = in_array( 'first_name' , $fields ) ? TRUE : FALSE;
		$search['middle_name']        = in_array( 'middle_name' , $fields ) ? TRUE : FALSE;
		$search['last_name']          = in_array( 'last_name' , $fields ) ? TRUE : FALSE;
		$search['title']              = in_array( 'title' , $fields ) ? TRUE : FALSE;
		$search['organization']       = in_array( 'organization' , $fields ) ? TRUE : FALSE;
		$search['department']         = in_array( 'department' , $fields ) ? TRUE : FALSE;
		$search['contact_first_name'] = in_array( 'contact_first_name' , $fields ) ? TRUE : FALSE;
		$search['contact_last_name']  = in_array( 'contact_last_name' , $fields ) ? TRUE : FALSE;
		$search['bio']                = in_array( 'bio' , $fields ) ? TRUE : FALSE;
		$search['notes']              = in_array( 'notes' , $fields ) ? TRUE : FALSE;

		$search['address_line_1']     = in_array( 'address_line_1' , $fields ) ? TRUE : FALSE;
		$search['address_line_2']     = in_array( 'address_line_2' , $fields ) ? TRUE : FALSE;
		$search['address_line_3']     = in_array( 'address_line_3' , $fields ) ? TRUE : FALSE;
		$search['address_city']       = in_array( 'address_city' , $fields ) ? TRUE : FALSE;
		$search['address_state']      = in_array( 'address_state' , $fields ) ? TRUE : FALSE;
		$search['address_zipcode']    = in_array( 'address_zipcode' , $fields ) ? TRUE : FALSE;
		$search['address_country']    = in_array( 'address_country' , $fields ) ? TRUE : FALSE;

		$search['phone_number']       = in_array( 'phone_number' , $fields ) ? TRUE : FALSE;

		//var_dump($search);

		/*
		 * Drop the current FULLTEXT indexes.
		 */
		$indexExists = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_TABLE . ' WHERE KEY_NAME = \'search\'' ); //var_dump($indexExists);
		if ( $indexExists > 0 ) $wpdb->query( 'ALTER TABLE ' . CN_ENTRY_TABLE . ' DROP INDEX search' );

		$indexExists = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE KEY_NAME = \'search\'' ); //var_dump($indexExists);
		if ( $indexExists > 0 ) $wpdb->query( 'ALTER TABLE ' . CN_ENTRY_ADDRESS_TABLE . ' DROP INDEX search' );

		$indexExists = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_PHONE_TABLE . ' WHERE KEY_NAME = \'search\'' ); //var_dump($indexExists);
		if ( $indexExists > 0 ) $wpdb->query( 'ALTER TABLE ' . CN_ENTRY_PHONE_TABLE . ' DROP INDEX search' );

		/*
		 * Recreate the FULLTEXT indexes based on the user choices
		 */

		// Build the arrays that will be imploded in the query statement.
		if ( $search['family_name'] ) $column['entry'][]        = 'family_name';
		if ( $search['first_name'] ) $column['entry'][]         = 'first_name';
		if ( $search['middle_name'] ) $column['entry'][]        = 'middle_name';
		if ( $search['last_name'] ) $column['entry'][]          = 'last_name';
		if ( $search['title'] ) $column['entry'][]              = 'title';
		if ( $search['organization'] ) $column['entry'][]       = 'organization';
		if ( $search['department'] ) $column['entry'][]         = 'department';
		if ( $search['contact_first_name'] ) $column['entry'][] = 'contact_first_name';
		if ( $search['contact_last_name'] ) $column['entry'][]  = 'contact_last_name';
		if ( $search['bio'] ) $column['entry'][]                = 'bio';
		if ( $search['notes'] ) $column['entry'][]              = 'notes';

		if ( $search['address_line_1'] ) $column['address'][]   = 'line_1';
		if ( $search['address_line_2'] ) $column['address'][]   = 'line_2';
		if ( $search['address_line_3'] ) $column['address'][]   = 'line_3';
		if ( $search['address_city'] ) $column['address'][]     = 'city';
		if ( $search['address_state'] ) $column['address'][]    = 'state';
		if ( $search['address_zipcode'] ) $column['address'][]  = 'zipcode';
		if ( $search['address_country'] ) $column['address'][]  = 'country';

		if ( $search['phone_number'] ) $column['phone'][]       = 'number';

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
		//$wpdb->hide_errors();

		//die;

		// Ensure at least keyword search enabled if user decides to try to disable both keyword and FULLTEXT searching.
		if ( empty( $settings['fulltext_enabled'] ) && empty( $settings['keyword_enabled'] ) ) $settings['keyword_enabled'] = 1;

		return $settings;
	}

	public static function setGEOBase( $settings ) {

		$regions = cnGEO::getRegions( $settings['base_country'] );

		if ( ! array_key_exists( $settings['base_region'], $regions ) ) {

			$settings['base_region'] = current( array_keys( $regions ) );
		}

		return $settings;
	}
}

/*
 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
 */
add_filter( 'cn_register_settings_tabs', array( 'cnRegisterSettings', 'registerSettingsTabs' ), 10, 1 );
add_filter( 'cn_register_settings_sections', array( 'cnRegisterSettings', 'registerSettingsSections' ), 10, 1 );
add_filter( 'cn_register_settings_fields', array( 'cnRegisterSettings', 'registerSettingsFields' ), 10, 1 );
