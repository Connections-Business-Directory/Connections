<?php
/**
 * Register the tabs, settings sections and section settings.
 * 
 * @author Steven A. Zahm
 * @since 0.7.3.0
 */
class cnRegisterSettings
{
	/**
	 * Register the tabs for the Connections : Settings admin page.
	 * 
	 * @author Steven A. Zahm
	 * @since 0.7.3.0
	 * @param $tabs array
	 * @return array
	 */
	public function registerSettingsTabs( $tabs )
	{
		global $connections;
		
		$settings = 'connections_page_connections_settings';
		
		// Register the core tab banks.
		$tabs[] = array( 
			'id' => 'general' , 
			'position' => 10 ,
			'title' => __( 'General' , 'connections' ) , 
			'page_hook' => $settings
		);
		
		$tabs[] = array( 
			'id' => 'images' , 
			'position' => 20 ,
			'title' => __( 'Images' , 'connections' ) , 
			'page_hook' => $settings
		);
		
		$tabs[] = array( 
			'id' => 'search' , 
			'position' => 30 ,
			'title' => __( 'Search' , 'connections' ) , 
			'page_hook' => $settings
		);
		$tabs[] = array( 
			'id' => 'advanced' , 
			'position' => 40 ,
			'title' => __( 'Advanced' , 'connections' ) , 
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
	public function registerSettingsSections( $sections )
	{
		global $connections;
		
		$settings = 'connections_page_connections_settings';
		
		/*
		 * The sections registered to the General tab.
		 */
		$sections[] = array(
			'tab' => 'general',
			'id' => 'connections_home_page', 
			'position' => 5, 
			'title' => __( 'Home' , 'connections' ), 
			'callback' => create_function( '', "_e('Choose the page where your directory is located. 
				This should be the page where you used the [connections] shortcode.', 'connections');" ), 
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab' => 'general',
			'id' => 'connections_login', 
			'position' => 10, 
			'title' => __( 'Require Login' , 'connections' ), 
			'callback' => '', 
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab' => 'general',
			'id' => 'connections_visibility', 
			'position' => 20, 
			'title' => __( 'Shortcode Visibility Overrides' , 'connections' ), 
			'callback' => create_function( '', "_e('The [connections] shortcode has two options available to show an entry or an entire directory 
				if the entry(ies) has been set to private or the user is required to be logged to view the directory. These options, when used, 
				will only be applied to the current shortcode instance.', 'connections');" ), 
			'page_hook' => $settings
		);
		
		/*
		 * The sections registered to the Images tab.
		 */
		$sections[] = array(
			'tab' => 'images',
			'id' => 'connections_image_instructions', 
			'position' => 10, 
			'title' => __( 'Image and Logo Instructions' , 'connections' ), 
			'callback' => create_function( '', "_e('When an image or logo is uploaded to an entry, various sizes are created and cached. This helps to reduce server load during the
				rendering of the directory. If these settings are changed, they will only affect images uploaded after the change has been made. All previous images will remain at their
				previously cached sizes. NOTE: the active template will determine which image(s) is used.', 'connections');" ),  
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab' => 'images',
			'id' => 'connections_image_thumbnail', 
			'position' => 10, 
			'title' => __( 'Thumbnail Image' , 'connections' ), 
			'callback' => create_function( '', "_e('Default settings are: Quality: 80%; Width: 80px; Height: 54px; Crop', 'connections');" ),
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab' => 'images',
			'id' => 'connections_image_medium', 
			'position' => 20, 
			'title' => __( 'Medium Image' , 'connections' ), 
			'callback' => create_function( '', "_e('Default settings are: Quality: 80%; Width: 225px; Height: 150px; Crop', 'connections');" ), 
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab' => 'images',
			'id' => 'connections_image_large', 
			'position' => 30, 
			'title' => __( 'Large Image' , 'connections' ), 
			'callback' => create_function( '', "_e('Default settings are: Quality: 80%; Width: 300px; Height: 225px; Crop', 'connections');" ),  
			'page_hook' => $settings
		);
		$sections[] = array(
			'tab' => 'images',
			'id' => 'connections_image_logo', 
			'position' => 30, 
			'title' => __( 'Logo' , 'connections' ), 
			'callback' => create_function( '', "_e('Default settings are: Quality: 80%; Width: 225px; Height: 150px; Crop', 'connections');" ),  
			'page_hook' => $settings
		);
		
		/*
		 * The sections registered to the Search tab.
		 */
		$sections[] = array(
			'tab' => 'search',
			'id' => 'connections_search', 
			'position' => 10, 
			'title' => __( 'Search Fields' , 'connections' ), 
			'callback' => '', 
			'page_hook' => $settings
		);
		
		/*
		 * The sections registered to the Advance tab.
		 */
		$sections[] = array(
			'tab' => 'advanced',
			'id' => 'connections_permalink', 
			'position' => 10, 
			'title' => __( 'Permalink' , 'connections' ), 
			'callback' => create_function( '', "echo '<p>' , __( 'Configure permalink support.' , 'connections' ) , '</p>';" ), 
			'page_hook' => $settings
		);
		
		$sections[] = array(
			'tab' => 'advanced',
			'id' => 'connections_link', 
			'position' => 15, 
			'title' => __( 'Links' , 'connections' ), 
			'callback' => create_function( '', "echo '<p>' , __( 'Enable certain entry data to become links.' , 'connections' ) , '</p>';" ), 
			'page_hook' => $settings
		);
		
		$sections[] = array(
			'tab' => 'advanced',
			'id' => 'connections_compatibility', 
			'position' => 20, 
			'title' => __( 'Compatibility' , 'connections' ), 
			'callback' => '', 
			'page_hook' => $settings
		);
		
		$sections[] = array(
			'tab' => 'advanced',
			'id' => 'connections_debug', 
			'position' => 30, 
			'title' => __( 'Debug' , 'connections' ), 
			'callback' => '', 
			'page_hook' => $settings
		);
		
		return $sections;
	}
	
	/**
	 * Register the settings sections.
	 * 
	 * @author Steven A. Zahm
	 * @since 0.7.3.0
	 */
	public function registerSettingsFields( $fields )
	{
		global $connections;
		
		$settings = 'connections_page_connections_settings';
		
		/*
		 * The General tab fields.
		 */
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'page_id',
			'position' => 5,
			'page_hook' => $settings,
			'tab' => 'general',
			'section' => 'connections_home_page',
			'title' => __('Page', 'connections'),
			'desc' => '',
			'help' => '',
			'type' => 'page',
			'show_option_none' => __('Select Page', 'connections'),
			'option_none_value' => '0'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'required',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'general',
			'section' => 'connections_login',
			'title' => __('Login Required', 'connections'),
			'desc' => __('Require registered users to login before showing the directory.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 0,
			'sanitize_callback' => array( &$connections->options , 'setAllowPublic' )
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'message',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'general',
			'section' => 'connections_login',
			'title' => __('Message', 'connections'),
			'desc' => __('The message to display to site visitors or registered users not logged in.', 'connections'),
			'help' => '',
			'type' => 'rte',
			'default' => 'Please login to view the directory.'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'allow_public_override',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'general',
			'section' => 'connections_visibility',
			'title' => __('Enable public_override', 'connections'),
			'desc' => __('By default all entry\'s whose status is "Public" will be visible to all site visitors or registered users not
				logged in. If the option to require login has been enabled, the <em>public_override</em> shortcode option allows you to override
				requiring the site vistor to be logged in. This setting is useful in multi author sites where those authors may have a need to 
				display specific entries to the public. For security reasons this option is disabled by default. If checked, this enables this shortcode option.', 'connection'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 0
		); 
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'allow_private_override',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'general',
			'section' => 'connections_visibility',
			'title' => __('Enable private_override', 'connections'),
			'desc' => __('Entries can be set to a "Private" status which requires the user to be logged in to the site in order for them
				to be able to view those entries.  
				The <em>private_override</em> shortcode option allows you to override their "Private" status. This setting is useful in 
				multi author sites where those authors may have a need to display specific private entries to the public. 
				For security reasons this option is disabled by default. If checked, this enables this shortcode option.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 0
		);
		
		/*
		 * The Images tab fields.
		 */
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'quality',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_thumbnail',
			'title' => __('JPEG Quality', 'connections'),
			'desc' => '%',
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 80
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'width',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_thumbnail',
			'title' => __('Width', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 80
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'height',
			'position' => 30,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_thumbnail',
			'title' => __('Height', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 54
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'ratio',
			'position' => 40,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_thumbnail',
			'title' => __('Ratio', 'connections'),
			'desc' => '',
			'help' => '',
			'type' => 'radio',
			'options' => array(
				'crop' => __('Crop, maintain aspect ratio.', 'connections'),
				'fill' => __('Fill, maintain aspect ratio.', 'connections'),
				'none' => __('None, scale to fit.', 'connections')
				),
			'default' => 'crop'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'quality',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_medium',
			'title' => __('JPEG Quality', 'connections'),
			'desc' => '%',
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 80
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'width',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_medium',
			'title' => __('Width', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 225
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'height',
			'position' => 30,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_medium',
			'title' => __('Height', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 150
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'ratio',
			'position' => 40,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_medium',
			'title' => __('Ratio', 'connections'),
			'desc' => '',
			'help' => '',
			'type' => 'radio',
			'options' => array(
				'crop' => __('Crop, maintain aspect ratio.', 'connections'),
				'fill' => __('Fill, maintain aspect ratio.', 'connections'),
				'none' => __('None, scale to fit.', 'connections')
				),
			'default' => 'crop'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'quality',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_large',
			'title' => __('JPEG Quality', 'connections'),
			'desc' => '%',
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 80
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'width',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_large',
			'title' => __('Width', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 300
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'height',
			'position' => 30,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_large',
			'title' => __('Height', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 225
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'ratio',
			'position' => 40,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_large',
			'title' => __('Ratio', 'connections'),
			'desc' => '',
			'help' => '',
			'type' => 'radio',
			'options' => array(
				'crop' => __('Crop, maintain aspect ratio.', 'connections'),
				'fill' => __('Fill, maintain aspect ratio.', 'connections'),
				'none' => __('None, scale to fit.', 'connections')
				),
			'default' => 'crop'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'quality',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_logo',
			'title' => __('JPEG Quality', 'connections'),
			'desc' => '%',
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 80
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'width',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_logo',
			'title' => __('Width', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 225
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'height',
			'position' => 30,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_logo',
			'title' => __('Height', 'connections'),
			'desc' => __('px', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'small',
			'default' => 150
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'ratio',
			'position' => 40,
			'page_hook' => $settings,
			'tab' => 'images',
			'section' => 'connections_image_logo',
			'title' => __('Ratio', 'connections'),
			'desc' => '',
			'help' => '',
			'type' => 'radio',
			'options' => array(
				'crop' => __('Crop, maintain aspect ratio.', 'connections'),
				'fill' => __('Fill, maintain aspect ratio.', 'connections'),
				'none' => __('None, scale to fit.', 'connections')
				),
			'default' => 'crop'
		);
		
		/*
		 * The Search tab fields.
		 */
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'fields',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'search',
			'section' => 'connections_search',
			'title' => __('Fields', 'connections'),
			'desc' => __('The selected fields will be searched.', 'connections'),
			'help' => '',
			'type' => 'multicheckbox',
			'options' => array(
				'family_name' => __('Family Name', 'connections'),
				'first_name' => __('First Name', 'connections'),
				'middle_name' => __('Middle Name', 'connections'),
				'last_name' => __('Last Name', 'connections'),
				'title' => __('Title', 'connections'),
				'organization' => __('Organization', 'connections'),
				'department' => __('Department', 'connections'),
				'contact_first_name' => __('Contact First Name', 'connections'),
				'contact_last_name' => __('Contact Last Name', 'connections'),
				'bio' => __('Biography', 'connections'),
				'notes' => __('Notes', 'connections'),
				'address_line_1' => __('Address Line One', 'connections'),
				'address_line_2' => __('Address Line Two', 'connections'),
				'address_line_3' => __('Address Line Three', 'connections'),
				'address_city' => __('Address City', 'connections'),
				'address_state' => __('Address State', 'connections'),
				'address_zipcode' => __('Address Zip Code', 'connections'),
				'address_country' => __('Address Country', 'connections'),
				'phone_number' => __('Phone Number', 'connections')
				),
			'default' => array(
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
			'sanitize_callback' => array( &$connections->options , 'setSearchFields' )
		);
		
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'fulltext_enabled',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'search',
			'section' => 'connections_search',
			'title' => __('FULLTEXT', 'connections'),
			'desc' => __('Enable FULLTEXT query support.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 1
		);
		
		/*
		 * The Advanced tab fields
		 */
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'category_base',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Category Base', 'connections'),
			'desc' => __('Enter a custom structure for the category in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'category',
			'sanitize_callback' => array( 'cnRegisterSettings' , 'flushRewrite' ) // Only need to add this once, otherwise it would be run for each field.
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'country_base',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Country Base', 'connections'),
			'desc' => __('Enter a custom structure for the country in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'country'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'region_base',
			'position' => 30,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Region Base', 'connections'),
			'desc' => __('Enter a custom structure for the region (state/province) in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'region'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'locality_base',
			'position' => 40,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Locality Base', 'connections'),
			'desc' => __('Enter a custom structure for the locality (city) in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'locality'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'postal_code_base',
			'position' => 50,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Postal Code Base', 'connections'),
			'desc' => __('Enter a custom structure for the postal code in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'postal-code'
		);
		/*$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'organization_base',
			'position' => 60,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Organization Base', 'connections'),
			'desc' => __('Enter a custom structure for the organization in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'organization'
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'department_base',
			'position' => 70,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Department Base', 'connections'),
			'desc' => __('Enter a custom structure for the department in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'department'
		);*/
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'name_base',
			'position' => 80,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_permalink',
			'title' => __('Name Base', 'connections'),
			'desc' => __('Enter a custom structure for the entry slug in the URL.', 'connections'),
			'help' => '',
			'type' => 'text',
			'size' => 'regular',
			'default' => 'name'
		);
		
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'name',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_link',
			'title' => __('Name', 'connections'),
			'desc' => __('Enabling this option will turn the name of every entry into a link. 
				Clicking the link will take you to the page with only that entry.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 0
		);
		
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'phone',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_link',
			'title' => __('Telephone Number', 'connections'),
			'desc' => __('Enabling this option will turn every telephone number into a link that when clicked by the user  
				on a mobile phone or computer with a telephony application installed will dial the number.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 0
		);
		
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'google_maps_api',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_compatibility',
			'title' => __('Google Maps API v3', 'connections'),
			'desc' => __('If the current active theme or another plugin loads the Google Maps API v3 uncheck this to prevent Connections from loading the Google Maps API. 
				This could prevent potential conflicts.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 1
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'javascript_footer',
			'position' => 20,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_compatibility',
			'title' => __('JavaScript', 'connections'),
			'desc' => __('By default Connections loads it\'s JavaScripts in the page footer uncheck this box to load them in the page header.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 1
		);
		$fields[] = array(
			'plugin_id' => 'connections',
			'id' => 'debug_messages',
			'position' => 10,
			'page_hook' => $settings,
			'tab' => 'advanced',
			'section' => 'connections_debug',
			'title' => __('Debug Messages', 'connections'),
			'desc' => __('Display debug messages.', 'connections'),
			'help' => '',
			'type' => 'checkbox',
			'default' => 0
		);
		
		return $fields;
	}
	
	/**
     * Get all the pages
     * 
     * @access private
     * @since 0.7.3
     * @uses get_pages()
     * @return array page names with key value pairs
     */
	private function getPages() {
	    $pages = get_pages();
	    $options = array( 0 => 'Select Page' );
		//var_dump($pages);
	    
		if ( ! empty($pages) )
		{
	        foreach ( $pages as $page )
			{
	            $options[$page->ID] = $page->post_title;
	        }
	    }
	
	    return $options;
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
	public static function flushRewrite($settings) {
		
		/*
		 * Make sure there is a value saved for each permalink base.
		 */
		if ( ! isset( $settings['category_base'] ) || empty( $settings['category_base'] ) ) $settings['category_base'] = 'category';
		if ( ! isset( $settings['country_base'] ) || empty( $settings['country_base'] ) ) $settings['country_base'] = 'country';
		if ( ! isset( $settings['region_base'] ) || empty( $settings['region_base'] ) ) $settings['region_base'] = 'region';
		if ( ! isset( $settings['locality_base'] ) || empty( $settings['locality_base'] ) ) $settings['locality_base'] = 'locality';
		if ( ! isset( $settings['postal_code_base'] ) || empty( $settings['postal_code_base'] ) ) $settings['postal_code_base'] = 'postal_code';
		if ( ! isset( $settings['name_base'] ) || empty( $settings['name_base'] ) ) $settings['name_base'] = 'name';
		
		function sanitize(&$item)
		{
			$item = str_ireplace('%', '-', $item); // Added this because sanitize_title_with_dashes will still allow % to passthru.
			return sanitize_title_with_dashes($item, '', 'save');
		}
		
		$settings = array_map('sanitize', $settings);
		
		// This option is added for a check that will force a flush_rewrite() in connectionsLoad::adminInit().
		update_option('connections_flush_rewrite', '1');
		
		return $settings;
	}
}
?>