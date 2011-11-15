<?php
/*
Plugin Name: Connections
Plugin URI: http://connections-pro.com/
Description: A business directory and address book manager.
Version: 0.7.1.7
Author: Steven A. Zahm
Author URI: http://connections-pro.com/

	Copyright 2009  Steven A. Zahm  (email : shazahm1@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * Credits:
 * 
 * Connections is based on Little Black Book  1.1.2 by Gerald S. Fuller
 * Little Black Book is based on Addressbook 0.7 by Sam Wilson
 * 
 * Uses a function here and there from NextGEN Gallery by Alex Rabe.
 * http://wordpress.org/extend/plugins/nextgen-gallery/
 * 
 * Update Notice in plugin admin inspired by Changelogger 1.2.8 by Oliver Schlöbe.
 * http://wordpress.org/extend/plugins/changelogger/
 * 
 * class.upload.php by Colin Verot.
 * http://www.verot.net
 * 
 * TimThumb by Ben Gillbanks and Mark Maunder.
 * Based on work done by Tim McDaniels and Darren Hoyt.
 * http://code.google.com/p/timthumb/
 * 
 * vCard class is a modified version by Troy Wolf.
 * http://www.troywolf.com/articles/php/class_vcard/
 * 
 * Screen Options class by Janis Elsts
 * http://w-shadow.com/blog/2010/06/29/adding-stuff-to-wordpress-screen-options/
 * 
 * spin.js by Felix Gnass
 * http://fgnass.github.com/spin.js/
 */

/**
 * @TODO: Add support for SSL using the CN_PLUGIN_URL constant throughout.
 */


if (!class_exists('connectionsLoad'))
{
	class connectionsLoad
	{
		public $currentUser;
		public $options;
		public $retrieve;
		public $term;
		
		/**
		 * Holds the string values returned from the add_menu_page & add_submenu_page functions
		 * @var object
		 */
		public $pageHook;
		
		public $message;
		/*public $errorMessages;
		public $successMessages;*/
		
		/**
		 * Do the database upgrade.
		 * @var bool
		 */
		private $dbUpgrade;
		
		public function __construct()
		{
			$this->loadConstants();
			$this->loadDependencies();
			$this->initDependencies();
			
			// Activation/Deactivation hooks
			register_activation_hook( dirname(__FILE__) . '/connections.php', array(&$this, 'activate') );
			register_deactivation_hook( dirname(__FILE__) . '/connections.php', array(&$this, 'deactivate') );
			
			//@TODO: Create uninstall method to remove options and tables.
			// register_uninstall_hook( dirname(__FILE__) . '/connections.php', array('connectionsLoad', 'uninstall') );
			
			// Calls the method to load the admin menus.
			if ( is_admin() ) add_action('admin_menu', array (&$this, 'loadAdminMenus'));
			
			// Add the rewrite rules.
			add_action( 'init', array(&$this, 'addRewriteRules') );
			
			
			// Start this plug-in once all other plugins are fully loaded
			add_action( 'plugins_loaded', array(&$this, 'start') );
		}
		
		public function start()
		{
			global $wpdb, /*$connections,*/ $current_user;
		
			get_currentuserinfo();
			$this->currentUser->setID($current_user->ID);
			
			//$this->options->setDBVersion('0.1.5');
			
			if ( is_admin() )
			{
				// Store the PHP mememory limit
				$this->phpMemoryLimit = ini_get('memory_limit');
				
				add_action( 'admin_init', array(&$this, 'adminInit') );
				
				// Parse admin queries.
				add_action( 'admin_init', array(&$this, 'adminActions') );
				
				/*
				 * Add the filter to update the user settings when the 'Apply" button is clicked.
				 * NOTE: This relies on the the Screen Options class by Janis Elsts
				 */
				add_filter( 'set-screen-option', array(&$this, 'managePageLimitSave'), 10 , 3 );
			}
			else
			{
				// Calls the methods to load the frontend scripts and CSS.
				add_action( 'wp_print_scripts', array(&$this, 'loadScripts' ) );
				add_action( 'wp_print_styles', array(&$this, 'loadStyles' ) );
				
				// Add a version number to the header
				// Disable as it fails the HTML5 validation
				//add_action( 'wp_head', create_function('', 'echo "\n<meta name=\'Connections\' content=\'' . $this->options->getVersion() . '-' . $this->options->getDBVersion() . '\' />\n";') );
				
				// Register all valid query variables.
				add_filter( 'query_vars', array(&$this, 'registerQueryVariables' ) );
				
				// Parse front end queries.
				add_action( 'parse_request', array(&$this, 'userActions') );
			}
			
		}
		
		private function loadConstants()
		{
			global $wpdb;
			
			define('CN_LOG', FALSE);
			
			define('CN_CURRENT_VERSION', '0.7.1.7');
			define('CN_DB_VERSION', '0.1.6');
			define('CN_IMAGE_PATH', WP_CONTENT_DIR . '/connection_images/');
			define('CN_IMAGE_BASE_URL', WP_CONTENT_URL . '/connection_images/');
			
			define('CN_ENTRY_TABLE', $wpdb->prefix . 'connections');
			define('CN_ENTRY_ADDRESS_TABLE', $wpdb->prefix . 'connections_address');
			define('CN_ENTRY_PHONE_TABLE', $wpdb->prefix . 'connections_phone');
			define('CN_ENTRY_EMAIL_TABLE', $wpdb->prefix . 'connections_email');
			define('CN_ENTRY_MESSENGER_TABLE', $wpdb->prefix . 'connections_messenger');
			define('CN_ENTRY_SOCIAL_TABLE', $wpdb->prefix . 'connections_social');
			define('CN_ENTRY_LINK_TABLE', $wpdb->prefix . 'connections_link');
			
			define('CN_ENTRY_TABLE_META', $wpdb->prefix . 'connections_meta');
			define('CN_TERMS_TABLE', $wpdb->prefix . 'connections_terms');
			define('CN_TERM_TAXONOMY_TABLE', $wpdb->prefix . 'connections_term_taxonomy');
			define('CN_TERM_RELATIONSHIP_TABLE', $wpdb->prefix . 'connections_term_relationships');
			
			define('CN_DIR_NAME', plugin_basename( dirname(__FILE__) ) );
			define('CN_BASE_NAME', plugin_basename( __FILE__ ) );
			define('CN_PATH', WP_PLUGIN_DIR . '/' . CN_DIR_NAME);
			define('CN_URL', WP_PLUGIN_URL . '/' . CN_DIR_NAME);
			define('CN_TEMPLATE_PATH', CN_PATH . '/templates');
			define('CN_TEMPLATE_URL', CN_URL . '/templates');
			define('CN_CUSTOM_TEMPLATE_PATH', WP_CONTENT_DIR . '/connections_templates');
			define('CN_CUSTOM_TEMPLATE_URL', WP_CONTENT_URL . '/connections_templates');
			define('CN_CACHE_PATH', CN_PATH . '/cache');
			
			$siteURL = get_option('siteurl');
			if( is_ssl() ) $siteURL = str_replace('http://', 'https://', $siteURL);
			
			/**
			 * Defines the URL to the plugin folder setting.
			 * @author: Ben Klocek
			 */
			define('CN_PLUGIN_URL', $siteURL . '/wp-content/plugins/' . CN_DIR_NAME);
		}
		
		private function loadDependencies()
		{
			/**
			 * @TODO: Load dependencies as needed. For example load only classes
			 * needed in the admin and frontend
			 */
			//Current User objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.user.php'); // Required for activation
			//Terms Objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.terms.php'); // Required for activation
			//Category Objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.category.php'); // Required for activation, entry list
			//Retrieve objects from the db.
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.retrieve.php'); // Required for activation
			//HTML FORM objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.form.php'); // Required for activation
			//date objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.date.php'); // Required for activation, entry list, add entry
			//entry objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.entry.php'); // Required for activation, entry list
			//plugin option objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.options.php'); // Required for activation
			//plugin utility objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.utility.php'); // Required for activation, entry list
			//plugin template objects
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.output.php'); // Required for activation, entry list
			//builds vCard
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.vcard.php'); // Required for front end
			
			//shortcodes
			require_once(WP_PLUGIN_DIR . '/connections/includes/inc.shortcodes.php'); // Required for front end
			
			//templates
			require_once(WP_PLUGIN_DIR . '/connections/includes/class.template.php'); // Required for the front end template processing
			
			/*
			 * Include the Screen Options class by Janis Elsts
			 * http://w-shadow.com/blog/2010/06/29/adding-stuff-to-wordpress-screen-options/
			 */
			include( CN_PATH . '/includes/screen-options/screen-options.php' );
			
		}
		
		private function initDependencies()
		{
			$this->options = new cnOptions();
			$this->currentUser = new cnUser();
			$this->retrieve = new cnRetrieve();
			$this->term = new cnTerms();
		}
		
		/**
		 * During install this will initiate the options.
		 */
		private function initOptions()
		{
			if ($this->options->getAllowPublic() === NULL) $this->options->setAllowPublic(TRUE);
			if ($this->options->getAllowPublicOverride() === NULL) $this->options->setAllowPublicOverride(FALSE);
			
			if ($this->options->getAllowPrivateOverride() === NULL) $this->options->setAllowPrivateOverride(FALSE);
			
			if ($this->options->getImgThumbQuality() === NULL) $this->options->setImgThumbQuality(80);
			if ($this->options->getImgThumbX() === NULL) $this->options->setImgThumbX(80);
			if ($this->options->getImgThumbY() === NULL) $this->options->setImgThumbY(54);
			if ($this->options->getImgThumbCrop() === NULL) $this->options->setImgThumbCrop('crop');
			
			if ($this->options->getImgEntryQuality() === NULL) $this->options->setImgEntryQuality(80);
			if ($this->options->getImgEntryX() === NULL) $this->options->setImgEntryX(225);
			if ($this->options->getImgEntryY() === NULL) $this->options->setImgEntryY(150);
			if ($this->options->getImgEntryCrop() === NULL) $this->options->setImgEntryCrop('crop');
			
			if ($this->options->getImgProfileQuality() === NULL) $this->options->setImgProfileQuality(80);
			if ($this->options->getImgProfileX() === NULL) $this->options->setImgProfileX(300);
			if ($this->options->getImgProfileY() === NULL) $this->options->setImgProfileY(225);
			if ($this->options->getImgProfileCrop() === NULL) $this->options->setImgProfileCrop('crop');
			
			if ($this->options->getImgLogoQuality() === NULL) $this->options->setImgLogoQuality(80);
			if ($this->options->getImgLogoX() === NULL) $this->options->setImgLogoX(225);
			if ($this->options->getImgLogoY() === NULL) $this->options->setImgLogoY(150);
			if ($this->options->getImgLogoCrop() === NULL) $this->options->setImgLogoCrop('crop');
			
			if ($this->options->getDefaultTemplatesSet() === NULL) $this->options->setDefaultTemplates();
			
			$this->options->setDefaultCapabilities();
			
			if ( $this->options->getVersion() === NULL ) $this->options->setVersion(CN_CURRENT_VERSION);
			if ( $this->options->getDBVersion() === NULL ) $this->options->setDBVersion(CN_DB_VERSION);
			
			if ( $this->options->getDebug() === NULL ) $this->options->setDebug(FALSE);
			
			$this->options->saveOptions();
		}
		
		public function displayMessages()
		{
			// Exit the method if $_GET['display_messages'] isn't set.
			if ( ! isset( $_GET['display_messages'] ) ) return;
			
			//global $connections;
			$output = '';
			
			$messages = $this->currentUser->getMessages();
			
			if ( !empty($messages) )
			{
				foreach ($messages as $message)
				{
					foreach($message as $type => $code)
					{
						switch ($type)
						{
							case 'error':
								$output .= '<div id="message" class="error"><p><strong>ERROR: </strong>' . $this->message->get_error_message($code) . '</p></div>';
							break;
							
							case 'error_runtime':
								$output .= '<div id="message" class="error"><p><strong>ERROR: </strong>' . $code . '</p></div>';
							break;
							
							case 'success':
								$output .= '<div id="message" class="updated fade"><p><strong>SUCCESS: </strong>' . $this->message->get_error_message($code) . '</p></div>';
							break;
							
							case 'success_runtime':
								$output .= '<div id="message" class="updated fade"><p><strong>SUCCESS: </strong>' . $code . '</p></div>';
							break;
						}
					}
				}
			}
			
			if ( $_GET['display_messages'] === 'true' ) $this->currentUser->resetMessages();
			
			echo $output;
		}
		
		public function setRuntimeMessage( $type , $message )
		{
			$messages = $this->currentUser->getMessages();
			
			$this->currentUser->setMessage( array( $type => $message ) );
		}
		
		/**
		 * Initiate the error messages for Connections using the WP_Error class.
		 */
		private function initErrorMessages()
		{
			//$this->errorMessages = new WP_Error();
			
			/*$this->errorMessages->add('session_path_does_not_exist', 'The $_SESSION save path does not exist.');
			$this->errorMessages->add('session_path_not_writable', 'The $_SESSION save path is not writable.');
			
			$this->errorMessages->add('form_token_mismatch', 'Token mismatch.');
			$this->errorMessages->add('form_no_entry_id', 'No entry ID.');
			$this->errorMessages->add('form_no_entry_token', 'No entry token.');
			$this->errorMessages->add('form_no_session_token', 'No session token.');
			$this->errorMessages->add('form_no_token', 'No form token.');*/
			
			$this->message->add('capability_view_entry_list', 'You are not authorized to view the entry list. Please contact the admin if you received this message in error.');
			
			$this->message->add('capability_add', 'You are not authorized to add entries. Please contact the admin if you received this message in error.');
			$this->message->add('capability_delete', 'You are not authorized to delete entries. Please contact the admin if you received this message in error.');
			$this->message->add('capability_edit', 'You are not authorized to edit entries. Please contact the admin if you received this message in error.');
			$this->message->add('capability_categories', 'You are not authorized to edit the categories. Please contact the admin if you received this message in error.');
			$this->message->add('capability_settings', 'You are not authorized to edit the settings. Please contact the admin if you received this message in error.');
			$this->message->add('capability_roles', 'You are not authorized to edit role capabilities. Please contact the admin if you received this message in error.');
			
			$this->message->add('category_duplicate_name', 'The category you are trying to create already exists.');
			$this->message->add('category_self_parent', 'Category can not be a parent of itself.');
			$this->message->add('category_delete_uncategorized', 'The Uncategorized category can not be deleted.');
			$this->message->add('category_update_uncategorized', 'The Uncategorized category can not be altered.');
			$this->message->add('category_add_uncategorized', 'The Uncategorized category already exists.');
			$this->message->add('category_add_failed', 'Failed to add category.');
			$this->message->add('category_update_failed', 'Failed to update category.');
			$this->message->add('category_delete_failed', 'Failed to delete category.');
			
			$this->message->add('entry_added_failed', 'Entry could not be added.');
			$this->message->add('entry_updated_failed', 'Entry could not be updated.');
			
			$this->message->add('entry_preferred_overridden_address', 'Your preferred setting for a address was overridden because another address that you are not permitted to view or edit is set as the preferred address. Please contact the admin if you received this message in error.');
			$this->message->add('entry_preferred_overridden_phone', 'Your preferred setting for a phone was overridden because another phone number that you are not permitted to view or edit is set as the preferred phone number. Please contact the admin if you received this message in error.');
			$this->message->add('entry_preferred_overridden_email', 'Your preferred setting for an email address was overridden because another email address that you are not permitted to view or edit is set as the preferred email address. Please contact the admin if you received this message in error.');
			$this->message->add('entry_preferred_overridden_im', 'Your preferred setting for a IM Network was overridden because another IM Network that you are not permitted to view or edit is set as the preferred IM Network. Please contact the admin if you received this message in error.');
			$this->message->add('entry_preferred_overridden_social', 'Your preferred setting for a social media network was overridden because another social media network that you are not permitted to view or edit is set as the preferred social media network. Please contact the admin if you received this message in error.');
			$this->message->add('entry_preferred_overridden_link', 'Your preferred setting for a link was overridden because another link that you are not permitted to view or edit is set as the preferred link. Please contact the admin if you received this message in error.');
			
			$this->message->add('image_upload_failed', 'Image upload failed.');
			$this->message->add('image_uploaded_failed', 'Uploaded image could not be saved to the destination folder.');
			$this->message->add('image_profile_failed', 'Profile image could not be created and/or saved to the destination folder.');
			$this->message->add('image_entry_failed', 'Entry image could not be created and/or saved to the destination folder.');
			$this->message->add('image_thumbnail_failed', 'Thumbnail image could not be created and/or saved to the destination folder.');
			
			$this->message->add('template_install_failed', 'The template installation has failed.');
			$this->message->add('template_delete_failed', 'The template could not be deleted.');
		}
		
		/**
		 * Returns one of the predefined Connections error messages.
		 * @return HTML div with error message.
		 * @param string
		 */
		public function getErrorMessage($errorMessage)
		{
			return '<div id="message" class="error"><p><strong>ERROR: </strong>' . $this->message->get_error_message($errorMessage) . '</p></div>';
		}
		
		/**
		 * Stores a predefined error messages in the user meta.
		 * @param string
		 */
		public function setErrorMessage($errorMessage)
		{
			$messages = $this->currentUser->getMessages();
			
			// If the error message is slready stored, no need to store it twice.
			if ( ! in_array( array('error' => $errorMessage) , $messages ) ) $this->currentUser->setMessage( array('error' => $errorMessage) );
		}
		
		/**
		 * Initiates the success messages for Connections using the WP_Error class.
		 */
		private function initSuccessMessages()
		{
			$this->message->add('form_entry_delete', 'The entry has been deleted.');
			$this->message->add('form_entry_delete_bulk', 'Entry(ies) have been deleted.');
			$this->message->add('form_entry_pending', 'The entry status have been set to pending.');
			$this->message->add('form_entry_pending_bulk', 'Entry(ies) status have been set to pending.');
			$this->message->add('form_entry_approve', 'The entry has been approved.');
			$this->message->add('form_entry_approve_bulk', 'Entry(ies) have been approved.');
			$this->message->add('form_entry_visibility_bulk', 'Entry(ies) visibility have been updated.');
			
			$this->message->add('category_deleted', 'Category(ies) have been deleted.');
			$this->message->add('category_updated', 'Category has been updated.');
			$this->message->add('category_added', 'Category has been added.');
			
			$this->message->add('entry_added', 'Entry has been added.');
			$this->message->add('entry_added_moderated', 'Pending review entry will be added.');
			$this->message->add('entry_updated', 'Entry has been updated.');
			$this->message->add('entry_updated_moderated', 'Pending review entry will be updated.');
			
			$this->message->add('image_uploaded', 'Uploaded image saved.');
			$this->message->add('image_profile', 'Profile image created and saved.');
			$this->message->add('image_entry', 'Entry image created and saved.');
			$this->message->add('image_thumbnail', 'Thumbnail image created and saved.');
			
			$this->message->add('settings_updated', 'Settings have been updated.');
			$this->message->add('role_settings_updated', 'Role capabilities have been updated.');
			
			$this->message->add('template_change_active', 'The default active template has been changed.');
			$this->message->add('template_installed', 'A new template has been installed.');
			$this->message->add('template_deleted', 'The template has been deleted.');
		}
		
		/**
		 * Returns one of the predefined Connections success messages.
		 * @return HTML div with error message.
		 * @param string
		 */
		public function getSuccessMessage($successMessage)
		{
			return '<div id="message" class="updated fade"><p><strong>SUCCESS: </strong>' . $this->message->get_error_message($successMessage) . '</p></div>';
		}
		
		/**
		 * Stores a predefined success messages in the user meta.
		 * @param string
		 */
		public function setSuccessMessage($successMessage)
		{
			$messages = $this->currentUser->getMessages();
			
			// If the success message is slready stored, no need to store it twice.
			if ( ! in_array( array('success' => $successMessage) , $messages ) ) $this->currentUser->setMessage( array('success' => $successMessage) );
		}
						
		/**
		 * Called when activating Connections via the activation hook.
		 */
		public function activate()
		{
			global $wpdb, $connections;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			$charsetCollate = '';
			
			if ( ! empty($wpdb->charset) ) $charsetCollate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) ) $charsetCollate .= " COLLATE $wpdb->collate";

			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_TABLE . "'") != CN_ENTRY_TABLE)
			{
				$entryTable = "CREATE TABLE " . CN_ENTRY_TABLE . " (
			        id bigint(20) NOT NULL AUTO_INCREMENT,
			        ts TIMESTAMP,
					date_added tinytext NOT NULL,
			        entry_type tinytext NOT NULL,
					visibility tinytext NOT NULL,
					slug tinytext NOT NULL,
					family_name tinytext NOT NULL,
					honorific_prefix tinytext NOT NULL,
					first_name tinytext NOT NULL,
					middle_name tinytext NOT NULL,
			        last_name tinytext NOT NULL,
					honorific_suffix tinytext NOT NULL,
					title tinytext NOT NULL,
					organization tinytext NOT NULL,
					department tinytext NOT NULL,
					contact_first_name tinytext NOT NULL,
					contact_last_name tinytext NOT NULL,
					addresses longtext NOT NULL,
					phone_numbers longtext NOT NULL,
					email longtext NOT NULL,
					im longtext NOT NULL,
					social longtext NOT NULL,
					links longtext NOT NULL,
					birthday tinytext NOT NULL,
					anniversary tinytext NOT NULL,
					bio longtext NOT NULL,
			        notes longtext NOT NULL,
					options longtext NOT NULL,
					added_by bigint(20) NOT NULL,
					edited_by bigint(20) NOT NULL,
					owner bigint(20) NOT NULL,
					status varchar(20) NOT NULL,
			        PRIMARY KEY  (id)
			    ) $charsetCollate";
			    
			    dbDelta($entryTable);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_TERMS_TABLE . "'") != CN_TERMS_TABLE)
			{
				$termsTable = "CREATE TABLE " . CN_TERMS_TABLE . " (
			        term_id bigint(20) NOT NULL AUTO_INCREMENT,
					name varchar(200) NOT NULL,
					slug varchar(200) NOT NULL,
					term_group bigint(10) NOT NULL,
			        PRIMARY KEY  (term_id),
					UNIQUE KEY slug (slug),
					INDEX name (name)
			    ) $charsetCollate";
			    
			    dbDelta($termsTable);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_TERM_TAXONOMY_TABLE . "'") != CN_TERM_TAXONOMY_TABLE)
			{
				$termTaxonomyTable = "CREATE TABLE " . CN_TERM_TAXONOMY_TABLE . " (
			        term_taxonomy_id bigint(20) NOT NULL AUTO_INCREMENT,
					term_id bigint(20) NOT NULL,
					taxonomy varchar(32) NOT NULL,
					description longtext NOT NULL,
					parent bigint(20) NOT NULL,
					count bigint(20) NOT NULL,
			        PRIMARY KEY  (term_taxonomy_id),
					UNIQUE KEY term_id_taxonomy (term_id, taxonomy),
					INDEX taxonomy (taxonomy)
			    ) $charsetCollate";
			    
			    dbDelta($termTaxonomyTable);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_TERM_RELATIONSHIP_TABLE . "'") != CN_TERM_RELATIONSHIP_TABLE)
			{
				$termTermRelationshipTable = "CREATE TABLE " . CN_TERM_RELATIONSHIP_TABLE . " (
			        entry_id bigint(20) NOT NULL,
					term_taxonomy_id bigint(20) NOT NULL,
					term_order int(11) NOT NULL,
			        PRIMARY KEY (entry_id,term_taxonomy_id),
					KEY term_taxonomy_id (term_taxonomy_id)
			    ) $charsetCollate";
			    
			    dbDelta($termTermRelationshipTable);
			}
			
			
			// Check if the Uncategorized term exists and if it doesn't create it.
			$term = $connections->term->getTermBy('slug', 'uncategorized', 'category');
			
			if ( empty($term) )
			{
				$attributes['slug'] = '';
				$attributes['parent'] = 0;
				$attributes['description'] = 'Entries not assigned to a category will automatically be assigned to this category and deleting a category which has been assigned to an entry will reassign that entry to this category. This category can not be edited or deleted.';
				
				$connections->term->addTerm('Uncategorized', 'category', $attributes);
			}
			
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_TABLE_META . "'") != CN_ENTRY_TABLE_META)
			{
				$entryTableMeta = "CREATE TABLE " . CN_ENTRY_TABLE_META . " (
			        meta_id bigint(20) unsigned NOT NULL auto_increment,
					entry_id bigint(20) unsigned NOT NULL default '0',
					meta_key varchar(255) default NULL,
					meta_value longtext,
					PRIMARY KEY  (meta_id),
					KEY entry_id (entry_id),
					KEY meta_key (meta_key)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableMeta);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_ADDRESS_TABLE . "'") != CN_ENTRY_ADDRESS_TABLE)
			{
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
				$entryTableAddress = "CREATE TABLE " . CN_ENTRY_ADDRESS_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`line_1` tinytext NOT NULL,
					`line_2` tinytext NOT NULL,
					`line_3` tinytext NOT NULL,
					`city` tinytext NOT NULL,
					`state` tinytext NOT NULL,
					`zipcode` tinytext NOT NULL,
					`country` tinytext NOT NULL,
					`latitude` decimal(15,12) default NULL,
					`longitude` decimal(15,12) default NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableAddress);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_PHONE_TABLE . "'") != CN_ENTRY_PHONE_TABLE)
			{
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
				$entryTablePhone = "CREATE TABLE " . CN_ENTRY_PHONE_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`number` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTablePhone);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_EMAIL_TABLE . "'") != CN_ENTRY_EMAIL_TABLE)
			{
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
				$entryTableEmail = "CREATE TABLE " . CN_ENTRY_EMAIL_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`address` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableEmail);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_MESSENGER_TABLE . "'") != CN_ENTRY_MESSENGER_TABLE)
			{
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
				$entryTableMessenger = "CREATE TABLE " . CN_ENTRY_MESSENGER_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`uid` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableMessenger);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_SOCIAL_TABLE . "'") != CN_ENTRY_SOCIAL_TABLE)
			{
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
				$entryTableSocialMedia = "CREATE TABLE " . CN_ENTRY_SOCIAL_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`url` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableSocialMedia);
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_LINK_TABLE . "'") != CN_ENTRY_LINK_TABLE)
			{
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
				$entryTableLink = "CREATE TABLE " . CN_ENTRY_LINK_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`title` tinytext NOT NULL,
					`url` tinytext NOT NULL,
					`target` tinytext NOT NULL,
					`follow` tinyint unsigned NOT NULL default '0',
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableLink);
			}
			
			
			// Create the cache folder.
			if ( ! file_exists( CN_CACHE_PATH ) ) mkdir( CN_CACHE_PATH );
			
			
			$this->initOptions();
			
			// Add the rewite rules and flush so they are rebuilt.
			/*$this->addRewriteRules();
			flush_rewrite_rules();*/
			
			//update_option('connections_activated', 'The Connections plug-in version ' . $this->options->getVersion() . ' has been activated.');
		}
		
		/**
		 * Called when deactivating Connections via the deactivation hook.
		 */
		public function deactivate()
		{
			flush_rewrite_rules();
			
			//global $options;
			
			/* This should be occur in the unistall hook
			$this->options->removeDefaultCapabilities();
			*/
			
			//  DROP TABLE `cnpfresh_connections`, `cnpfresh_connections_terms`, `cnpfresh_connections_term_relationships`, `cnpfresh_connections_term_taxonomy`;
			//  DELETE FROM `nhonline_freshcnpro`.`cnpfresh_options` WHERE `cnpfresh_options`.`option_name` = 'connections_options'
		}
		
		public function addDBUpgradeMessage()
		{
			echo '<div id="message" class="error"><p><strong>Connections database requires updating. <a class="button-highlighted" href="admin.php?page=connections_dashboard">START</a></strong></p></div>';
		}
		
		/**
		 * Remove the admin notice since it's confusing to see both the message and upgrade text at the same time.
		 * Called in action toplevel_page_connections_dashboard from adminInit()
		 */
		public function removeDBUpgradeMessage()
		{
			remove_action( 'admin_notices', array( &$this , 'addDBUpgradeMessage' ) );
		}
		
		/**
		 * Add the Rewrite rules.
		 * 
		 * @ TODO Add setting to allow a custom base URI for the permalink.
		 * 
		 * @return NULL
		 */
		public function addRewriteRules()
		{
			// base URI
			add_rewrite_rule( '(directory)/?$', 'index.php?cnpagename=$matches[1]', 'top' );
			
			add_rewrite_rule( '(directory)/(individual|organization|family)/?$', 'index.php?cnpagename=$matches[1]&cnlt=$matches[2]', 'top' );
			add_rewrite_rule( '(directory)/name/(.*)/?$', 'index.php?cnpagename=$matches[1]&cnname=$matches[2]', 'top' );
			
			//add_rewrite_rule( '(directory)/?([^/]*)/?([^/]*)$', 'index.php?cnpagename=$matches[1]&cnlisttype=$matches[2]&cnname=$matches[3]', 'top' );
			
			/* For testing only, NEVER leave uncommented in released versions. It'll slow WP down. */
			//flush_rewrite_rules();
		}
		
		/**
		 * Register the valid query variables.
		 * 
		 * @param object $query
		 * @return array
		 */
		public function registerQueryVariables($query)
		{
			$query[] = 'cnpagename';// page name
			$query[] = 'cnname';	// entry name (slug)
			$query[] = 'cntoken';	// security token; WP nonce
			$query[] = 'cntmpl';	// template name
			$query[] = 'cnid';		// comma delimited entry IDs
			$query[] = 'cncatid';	// comma delimited category IDs
			$query[] = 'cnexcatid';	// comma delimited category IDs to exclude
			$query[] = 'cncatnm';	// category names (slug)
			$query[] = 'cnlt';		// list type
			$query[] = 'cnpg';		// page
			$query[] = 'cnlm';		// pagination limit
			$query[] = 'cnoff';		// pagination offset
			$query[] = 'cnob';		// order by
			$query[] = 'cnvc';		// download vCard, BOOL 1 or 0
			
			return $query;
		}
		
		public function adminInit()
		{
			// Initiate admin messages.
			$this->message = new WP_Error();
			$this->initErrorMessages();
			$this->initSuccessMessages();
			
			//Check if the db requires updating, display message if it does.
			if ($this->options->getDBVersion() < CN_DB_VERSION) $this->dbUpgrade = add_action( 'admin_notices', array( &$this , 'addDBUpgradeMessage' ) );
			
			// Calls the methods to load the admin scripts and CSS.
			add_action('admin_print_scripts', array(&$this, 'loadAdminScripts') );
			add_action('admin_print_styles', array(&$this, 'loadAdminStyles') );
			
			// Add Settings link to the plugin actions
			add_action('plugin_action_links_' . CN_BASE_NAME, array(&$this, 'addActionLinks'));
			
			// Add FAQ, Support and Donate links
			add_filter('plugin_row_meta', array(&$this, 'addMetaLinks'), 10, 2);
			
			// Add the Add Entry item to the favorites dropdown.
			add_filter('favorite_actions', array(&$this, 'addEntryFavorite') );
			
			// Add Changelog table row in the Manage Plugins admin page.
			add_action('after_plugin_row_' . CN_BASE_NAME, array(&$this, 'displayUpgradeNotice'), 1, 0);
			// Maybe should use this action hook instead: in_plugin_update_message-{$file}
			
			// Register the edit metaboxes.
			add_action('load-' . $this->pageHook->add, array(&$this, 'registerEditMetaboxes'));
			add_action('load-' . $this->pageHook->manage, array(&$this, 'registerEditMetaboxes'));
			
			// Register the Dashboard metaboxes.
			add_action('load-' . $this->pageHook->dashboard, array(&$this, 'registerDashboardMetaboxes'));
			
			// Remove the admin database message one the following pages since it's confusing to see both the message and upgrade text at the same time.
			add_action('load-' . $this->pageHook->dashboard, array(&$this, 'removeDBUpgradeMessage'));
			add_action('load-' . $this->pageHook->manage, array(&$this, 'removeDBUpgradeMessage'));
			add_action('load-' . $this->pageHook->add, array(&$this, 'removeDBUpgradeMessage'));
			add_action('load-' . $this->pageHook->categories, array(&$this, 'removeDBUpgradeMessage'));
			add_action('load-' . $this->pageHook->templates, array(&$this, 'removeDBUpgradeMessage'));
			add_action('load-' . $this->pageHook->settings, array(&$this, 'removeDBUpgradeMessage'));
			add_action('load-' . $this->pageHook->roles, array(&$this, 'removeDBUpgradeMessage'));
			
			/*
			 * Add the panel to the "Screen Options" box to the manage page.
			 * NOTE: This relies on the the Screen Options class by Janis Elsts
			 */
			add_screen_options_panel( 'cn-manage-page-limit' , 'Show on screen' , array(&$this, 'managePageLimit') , $this->pageHook->manage , array(&$this, 'managePageLimitSaveAJAX') , FALSE );
		}
		
		/**
		 * Add the page limit panel to the screen options of the manage page.
		 * NOTE: This relies on the the Screen Options class by Janis Elsts
		 */
		public function managePageLimit()
		{
			global $connections;
			
			$page = $connections->currentUser->getFilterPage('manage');
			
			$out = '<label><input type="text" class="entry-per-page" name="wp_screen_options[value]" id="edit_entry_per_page" maxlength="3" value="' . $page->limit . '" />Entries</label>';
			$out .= '<input type="hidden" name="wp_screen_options[option]" id="edit_entry_per_page_name" value="connections" />';
			$out .= '<input type="submit" name="screen-options-apply" id="entry-per-page-apply" class="button" value="Apply"  />';
			return $out;
		}
		
		/**
		 * Save the user setting for the page limit on the screen options of the manage page.
		 * NOTE: This is only run during the AJAX callback which is currently disabled.
		 * NOTE: This relies on the the Screen Options class by Janis Elsts
		 */
		public function managePageLimitSaveAJAX()
		{
			include_once ( CN_PATH . '/includes/inc.processes.php' );
			
			processSetUserFilter();
		}
		
		public function managePageLimitSave( $false = FALSE , $option , $value)
		{
			global $connections;
			
			$user_meta = get_user_meta( $connections->currentUser->getID() , $option, TRUE);
		
			$user_meta['filter']['manage']['limit'] = absint($value);
			$user_meta['filter']['manage']['current'] = 1;
			
			return $user_meta;
		}
		
		/**
		 * Register the admin menus for Connections
		 */	
		public function loadAdminMenus()
		{
			// Set the capability string to be used in the add_sub_menu function per role capability assigned to the current user.		
			if ( current_user_can('connections_add_entry_moderated') )
			{
				$addEntryCapability = 'connections_add_entry_moderated';
			}
			elseif ( current_user_can('connections_add_entry') )
			{
				$addEntryCapability = 'connections_add_entry';
			}
			else
			{
				$addEntryCapability = 'connections_add_entry_moderated';
			}
			
			// Register the top level menu item.
			$this->pageHook->topLevel = add_menu_page('Connections', 'Connections', 'connections_view_dashboard', 'connections_dashboard', array (&$this, 'showPage'), WP_PLUGIN_URL . '/connections/images/menu.png');
			
			$submenu[0]   = array( 'hook' => 'dashboard', 'page_title' => 'Connections : Dashboard', 'menu_title' => 'Dashboard', 'capability' => 'connections_view_dashboard', 'menu_slug' => 'connections_dashboard', 'function' => array (&$this, 'showPage') );
			$submenu[20]  = array( 'hook' => 'manage', 'page_title' => 'Connections : Manage', 'menu_title' => 'Manage', 'capability' => 'connections_manage', 'menu_slug' => 'connections_manage', 'function' => array (&$this, 'showPage') );
			$submenu[40]  = array( 'hook' => 'add', 'page_title' => 'Connections : Add Entry', 'menu_title' => 'Add Entry', 'capability' => $addEntryCapability, 'menu_slug' => 'connections_add', 'function' => array (&$this, 'showPage') );
			$submenu[60]  = array( 'hook' => 'categories', 'page_title' => 'Connections : Categories', 'menu_title' => 'Categories', 'capability' => 'connections_edit_categories', 'menu_slug' => 'connections_categories', 'function' => array (&$this, 'showPage') );
			$submenu[80]  = array( 'hook' => 'templates', 'page_title' => 'Connections : Templates', 'menu_title' => 'Templates', 'capability' => 'connections_manage_template', 'menu_slug' => 'connections_templates', 'function' => array (&$this, 'showPage') );
			$submenu[100] = array( 'hook' => 'settings', 'page_title' => 'Connections : Settings', 'menu_title' => 'Settings', 'capability' => 'connections_change_settings', 'menu_slug' => 'connections_settings', 'function' => array (&$this, 'showPage') );
			$submenu[120] = array( 'hook' => 'roles', 'page_title' => 'Connections : Roles &amp; Capabilites', 'menu_title' => 'Roles', 'capability' => 'connections_change_roles', 'menu_slug' => 'connections_roles', 'function' => array (&$this, 'showPage') );
			$submenu[140] = array( 'hook' => 'help', 'page_title' => 'Connections : Help', 'menu_title' => 'Help', 'capability' => 'connections_view_help', 'menu_slug' => 'connections_help', 'function' => array (&$this, 'showPage') );
			
			$submenu = apply_filters('cn_submenu', $submenu);
			
			ksort($submenu);
			
			foreach ( $submenu as $menu )
			{
				extract($menu);
				$this->pageHook->{$hook} = add_submenu_page( 'connections_dashboard', $page_title, $menu_title, $capability, $menu_slug, $function );
			}
			
		}
		
		/**
		 * Register the metaboxes used for editing an entry.
		 * 
		 * Action added in connectionsLoad::loadAdminMenus
		 * 
		 * @author Steven A. Zahm
		 * @since 0.7.1.3
		 */
		public function registerEditMetaboxes()
		{
			// The meta boxes do not need diplayed/registered if no action is being taken on an entry. Such as copy/edit.
			if ( $_GET['page'] === 'connections_manage' &&  ! isset( $_GET['action'] ) )  return;
			
			$form = new cnFormObjects();
			
			$form->registerEditMetaboxes( substr(current_filter(), 5) );
			
			add_filter('screen_layout_columns', array(&$this, 'screenLayout'), 10, 2);
		}
		
		/**
		 * Register the metaboxes used for the Dashboard.
		 * 
		 * Action added in connectionsLoad::loadAdminMenus
		 * 
		 * @author Steven A. Zahm
		 * @since 0.7.1.6
		 */
		public function registerDashboardMetaboxes()
		{
			$form = new cnFormObjects();
			$form->registerDashboardMetaboxes();
			
			add_filter('screen_layout_columns', array(&$this, 'screenLayout'), 10, 2);
		}
		
		/**
		 * Register the number of columns permitted for metabox use on the edit entry page.
		 * 
		 * Filter added in connectionsLoad::registerEditMetaboxes
		 * 
		 * @author Steven A. Zahm
		 * @since 0.7.1.3
		 * @return array
		 */
		public function screenLayout($columns, $screen)
		{
			$columns[$this->pageHook->dashboard] = 2;
			$columns[$this->pageHook->manage] = 2;
			$columns[$this->pageHook->add] = 2;
			
			return $columns;
		}
		
		/**
		 * Loads the Connections javascripts only on required admin pages.
		 */
		public function loadAdminScripts()
		{
			// Exit the method if $_GET['page'] isn't set.
			if ( !isset($_GET['page']) ) return;
			
			$allPages = array( 'connections_dashboard', 'connections_manage',  'connections_add', 'connections_categories', 'connections_settings', 'connections_templates', 'connections_roles', 'connections_csv', 'connections_help' );
			
			if ( in_array($_GET['page'], $allPages) )
			{
				wp_enqueue_script('cn-ui-admin', WP_PLUGIN_URL . '/connections/js/cn-admin.js', array('jquery'), CN_CURRENT_VERSION, TRUE);
				wp_enqueue_script('jquery-preloader', WP_PLUGIN_URL . '/connections/js/jquery.preloader.js', array('jquery' , 'cn-ui-admin'), CN_CURRENT_VERSION, TRUE);
			}
			
			/*
			 * TinyMCE in WordPress Plugins
			 * http://www.keighl.com/2010/01/tinymce-in-wordpress-plugins/
			 * 
			 * For full editor see:
			 * http://dannyvankooten.com/450/tinymce-wysiwyg-editor-in-wordpress-plugin/
			 * 
			 * Load the tinyMCE scripts on these pages.
			 */
			$editorPages = array( 'connections_manage', 'connections_add' );
			
			if ( in_array( $_GET['page'], $editorPages ) )
			{
				global $concatenate_scripts, $compress_scripts, $compress_css;
				
				if( version_compare($GLOBALS['wp_version'], '3.2.999', '<') )
				{
					$compress_scripts = FALSE; // If the script are compress the TinyMCE doesn't seem to function.
					
					wp_tiny_mce( 	FALSE , // true makes the editor "teeny"
									array
									(
										'editor_selector' => 'tinymce',
										'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
										'theme_advanced_buttons2' => '',
										'inline_styles' => TRUE,
										'relative_urls' => FALSE,
										'remove_linebreaks' => FALSE,
										'plugins' => 'paste'
									)
								);
				}
			}
			
			// Load the core JavaScripts required for meta box UI.
			$metaBoxPages = array( 'connections_dashboard', 'connections_manage', 'connections_add' );
			
			if ( in_array( $_GET['page'], $metaBoxPages ) )
			{
				wp_enqueue_script('common');
				wp_enqueue_script('wp-lists');
				wp_enqueue_script('postbox');
				
				//wp_enqueue_script('jquery-ui-core');
				//wp_enqueue_script('hoverintent');
				//wp_enqueue_script('common');
				//wp_enqueue_script('jquery-color');
				//wp_enqueue_script('jquery-ui-widget');
				//wp_enqueue_script('jquery-ui-mouse');
				//wp_enqueue_script('jquery-ui-sortable');
				//wp_enqueue_script('jquery-ui-draggable');
				//wp_enqueue_script('admin-widgets');
				
				wp_enqueue_script('cn-widget', WP_PLUGIN_URL . '/connections/js/widgets.js', array('jquery'), CN_CURRENT_VERSION, TRUE);
			}
		}
		
		/**
		 * Loads the Connections javascripts on the WordPress frontend.
		 */
		public function loadScripts()
		{
			/*
			 * http://beerpla.net/2010/01/13/wordpress-plugin-development-how-to-include-css-and-javascript-conditionally-and-only-when-needed-by-the-posts/
			 * http://beerpla.net/2010/01/15/follow-up-to-loading-css-and-js-conditionally/
			 * http://scribu.net/wordpress/optimal-script-loading.html
			 */
			
			wp_enqueue_script('cn-ui', WP_PLUGIN_URL . '/connections/js/cn-user.js', array('jquery'), CN_CURRENT_VERSION, TRUE);
			wp_enqueue_script('jquery-preloader', WP_PLUGIN_URL . '/connections/js/jquery.preloader.js', array('jquery'), CN_CURRENT_VERSION, TRUE);
			//wp_enqueue_script('cn-spin', WP_PLUGIN_URL . '/connections/js/spin.js', array('jquery'), CN_CURRENT_VERSION, TRUE);
			//wp_enqueue_script('jquery-spin', WP_PLUGIN_URL . '/connections/js/jquery.spin.js', array('jquery'), CN_CURRENT_VERSION, TRUE);
		}
		
		/**
		 * Loads the Connections CSS only on required admin pages.
		 */
		public function loadAdminStyles()
		{
			// Exit the method if $_GET['page'] isn't set.
			if ( !isset($_GET['page']) ) return;
			
			global $is_IE;
			
			/*
			 * Load styles only on the Connections plug-in admin pages.
			 */
			$adminPages = array('connections_dashboard','connections_manage','connections_add','connections_categories','connections_settings','connections_templates','connections_roles','connections_csv','connections_help');
			
			if ( in_array($_GET['page'], $adminPages) )
			{
				wp_enqueue_style('connections', CN_URL . '/css/cn-admin.css', array(), CN_CURRENT_VERSION);
			}
			
			/*
			 * Load the WordPress widgets styles only of these pages.
			 */
			$adminPageEntryEdit = array('connections_manage','connections_add');
			if ( in_array($_GET['page'], $adminPageEntryEdit) )
			{
				//echo get_admin_url();
				wp_enqueue_style( 'connections-widgets', get_admin_url() . 'css/widgets.css' );
				
				//if ( $is_IE ) wp_enqueue_style( 'connections-ie', get_admin_url() . 'css/ie.css' );
			}
		}
		
		/**
		 * Loads the Connections CSS on the WordPress frontend.
		 */
		public function loadStyles()
		{
			//wp_register_style('member_template_styles', WP_PLUGIN_URL . '/connections/templates/member_template.css', array(), CN_CURRENT_VERSION);
			//wp_enqueue_style( 'member_template_styles' );
			wp_enqueue_style('connections', CN_URL . '/css/cn-user.css', array(), CN_CURRENT_VERSION);
		}
		
		/*
		 * Add items to the favorites drop down.
		 */
		public function addEntryFavorite($actions)
		{
			// Exit the method if $_GET['page'] isn't set.
			if ( !isset($_GET['page']) ) return $actions;
			
			switch ($_GET['page'])
			{
				
				case 'connections_manage':
					$cnActions = array( 'admin.php?page=connections_add' => array('Add Entry', 'connections_add_entry'),
										'admin.php?page=connections_categories' => array('Add Category<div class="favorite-action"><hr /></div>', 'connections_edit_categories')
										);
				break;
				
				case 'connections_add':
					$cnActions = array( 'admin.php?page=connections_categories' => array('Add Category', 'connections_edit_categories') );
				break;
				
				case 'connections_categories':
					$cnActions = array( 'admin.php?page=connections_add' => array('Add Entry', 'connections_add_entry') );
				break;
				
				case 'connections_templates':
				case 'connections_settings':
				case 'connections_roles':
				case 'connections_csv':
				case 'connections_help':
				case 'connections_dashboard':
					$cnActions = array( 'admin.php?page=connections_add' => array('Add Entry', 'connections_add_entry'),
										'admin.php?page=connections_categories' => array('Add Category<div class="favorite-action"><hr /></div>', 'connections_edit_categories')
									   );
				break;
			}
			
			return array_merge( (array) $cnActions, $actions);
		}
		
		// Add settings option
		public function addActionLinks($links) {
			$new_links = array();
			
			$new_links[] = '<a href="admin.php?page=connections_settings">Settings</a>';
			
			return array_merge($new_links, $links);
		}
		
		// Add FAQ and support information
		public function addMetaLinks($links, $file)
		{
			if ( $file == CN_BASE_NAME )
			{
				$links[] = '<a href="http://connections-pro.com/?page_id=29" target="_blank">Extend</a>';
				$links[] = '<a href="http://connections-pro.com/?page_id=419" target="_blank">Templates</a>';
				$links[] = '<a href="admin.php?page=connections_help" target="_blank">Help</a>';
				$links[] = '<a href="http://connections-pro.com/help-desk" target="_blank">Support</a>';
				//$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5070255" target="_blank">Donate</a>';
			}
			
			return $links;
		}
		
		/**
		 * Add the changelog as a table row on the Manage Plugin admin screen.
		 * Code based on Changelogger.
		 */
		public function displayUpgradeNotice()
		{
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
			//echo "<tr><td colspan='5'>TEST</td></tr>";
			//$api = plugins_api('plugin_information', array('slug' => 'connections', 'fields' => array('tested' => true, 'requires' => false, 'rating' => false, 'downloaded' => false, 'downloadlink' => false, 'last_updated' => false, 'homepage' => false, 'tags' => false, 'sections' => true) ));
			//print_r($api);
			
			if( version_compare($GLOBALS['wp_version'], '2.9.999', '>') ) // returning bool if at least WP 3.0 is running
			    $current = get_option( '_site_transient_update_plugins' );
			
			elseif( version_compare($GLOBALS['wp_version'], '2.7.999', '>') ) // returning bool if at least WP 2.8 is running
			    $current = get_transient( 'update_plugins' );
				
			else
			    $current = get_option( 'update_plugins' );
				
			//print_r($current);
			
			if ( !isset($current->response[ CN_BASE_NAME ]) ) return NULL;
			
			$r = $current->response[ CN_BASE_NAME ]; // response should contain the slug and upgrade_notice within an array.
			//print_r($r);
			
			if ( isset($r->upgrade_notice) )
			{
				$columns = CLOSMINWP28 ? 3 : 5;
				
				$output .= '<tr class="plugin-update-tr"><td class="plugin-update" colspan="' . $columns . '"><div class="update-message" style="font-weight: normal;">';
				$output .= '<strong>Upgrade notice for version: ' . $r->new_version . '</strong>';
				$output .= '<ul style="list-style-type: square; margin-left:20px;"><li>' . $r->upgrade_notice . '</li></ul>';
				$output .= '</div></td></tr>';
			
				echo $output;
			}
			
			
			/*stdClass Object
			(
			    [id] => 5801
			    [slug] => connections
			    [new_version] => 0.7.0.0
			    [upgrade_notice] => Upgrading to this version might break custom templates.
			    [url] => http://wordpress.org/extend/plugins/connections/
			    [package] => http://downloads.wordpress.org/plugin/connections.0.7.0.0.zip
			)*/
		}
		
		/**
		 * This is the registered function calls for the Connections admin pages as registered
		 * using the add_menu_page() and add_submenu_page() WordPress functions.
		 */
		public function showPage()
		{
			if  ( $this->options->getVersion() < CN_CURRENT_VERSION )
			{
				//echo $this->options->getVersion() . '<div id="message" class="error"><p><strong>ERROR: </strong>The version of Connections installed is newer than the version last activated. Please deactive and then reactivate Connections.</p></div>';
				//return;
				
				$this->initOptions(); // @TODO: a version change should not reset the roles and capabilites.
				$this->options->setVersion(CN_CURRENT_VERSION);
			}
			
			if ( $this->dbUpgrade )
			{
				include_once ( dirname (__FILE__) . '/includes/inc.upgrade.php' );
				connectionsShowUpgradePage();
				return;
			}
			
			
			switch ($_GET['page'])
			{
				case 'connections_dashboard':
					include_once ( CN_PATH . '/submenus/dashboard.php' );
					connectionsShowDashboardPage();
				break;
				
				case 'connections_manage':
					include_once ( CN_PATH . '/submenus/manage.php' );
					( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) ? $action = $_GET['action'] : $action = '';
					
					connectionsShowViewPage( $action );
				break;
				
				case 'connections_add':
					include_once ( CN_PATH . '/submenus/manage.php' );
					
					connectionsShowViewPage( 'add' );
				break;
				
				case 'connections_categories':
					include_once ( CN_PATH . '/submenus/categories.php' );
					connectionsShowCategoriesPage();
				break;
				
				case 'connections_settings':
					include_once ( CN_PATH . '/submenus/settings.php' );
					connectionsShowSettingsPage();
				break;
				
				case 'connections_templates':
					include_once ( CN_PATH . '/submenus/templates.php' );
					connectionsShowTemplatesPage();
				break;
				
				case 'connections_roles':
					include_once ( CN_PATH . '/submenus/roles.php' );
					connectionsShowRolesPage();
				break;
				
				case 'connections_help':
					include_once ( CN_PATH . '/submenus/help.php' );
					connectionsShowHelpPage();
				break;
			}
			
		}
		
		/**
		 * Verify and process requested actions in the admin.
		 */
		public function adminActions($wp)
		{
			// Exit the method if $_GET['connections_process'] isn't set.
			if ( !isset($_GET['connections_process']) ) return;
			
			global $connections;
			
			include_once ( CN_PATH . '/includes/inc.processes.php' );
			$form = new cnFormObjects();
			
			switch ( $_GET['process'] )
			{
				case 'manage':
					if ( isset($_GET['action']) && !empty( $_GET['action'] ) )
					{
						switch ( $_GET['action'] )
						{
							case 'add':
								/*
								 * Check whether the current user can add an entry.
								 */
								if ( current_user_can('connections_add_entry') || current_user_can('connections_add_entry_moderated') )
								{
									check_admin_referer($form->getNonce('add_entry'), '_cn_wpnonce');
									processEntry($_POST, 'add');
									wp_redirect('admin.php?page=connections_add&display_messages=true');
								}
								else
								{
									$connections->setErrorMessage('capability_add');
								}
							break;
							
							case 'update':
								/*
								 * Check whether the current user can edit an entry.
								 */
								if ( current_user_can('connections_edit_entry') || current_user_can('connections_edit_entry_moderated') )
								{
									check_admin_referer($form->getNonce('update_entry'), '_cn_wpnonce');
									processEntry($_POST, 'update');
									wp_redirect('admin.php?page=connections_manage&display_messages=true');
								}
								else
								{
									$connections->setErrorMessage('capability_edit');
								}
							break;
							
							case 'approve':
								/*
								 * Check whether the current user can edit an entry.
								 */
								if ( current_user_can('connections_edit_entry') )
								{
									processSetEntryStatus('approved');
									wp_redirect('admin.php?page=connections_manage&display_messages=true');
								}
								else
								{
									$connections->setErrorMessage('capability_edit');
								}
							break;
							
							case 'unapprove':
								/*
								 * Check whether the current user can edit an entry.
								 */
								if ( current_user_can('connections_edit_entry') )
								{
									processSetEntryStatus('pending');
									wp_redirect('admin.php?page=connections_manage&display_messages=true');
								}
								else
								{
									$connections->setErrorMessage('capability_edit');
								}
							break;
							
							case 'delete':
								/*
								 * Check whether the current user delete an entry.
								 */
								if (current_user_can('connections_delete_entry'))
								{
									processDeleteEntry();
									wp_redirect('admin.php?page=connections_manage&display_messages=true');
								}
								else
								{
									$connections->setErrorMessage('capability_delete');
								}
							break;
							
							case 'filter':
								check_admin_referer('filter');
								processSetUserFilter();
								//wp_redirect('admin.php?page=connections_manage');
								wp_redirect( add_query_arg( 's' , urlencode( $_GET['s'] ) , 'admin.php?page=connections_manage' ) );
							break;
							
							case 'do':
								if ( isset( $_POST['action'] ) && ! empty( $_POST['action'] ) )
								{
									switch ($_POST['action'])
									{
										case 'delete':
											/*
											 * Check whether the current user delete an entry.
											 */
											if (current_user_can('connections_delete_entry'))
											{
												check_admin_referer($form->getNonce('bulk_action'), '_cn_wpnonce');
												processDeleteEntries();
												wp_redirect('admin.php?page=connections_manage&display_messages=true');
											}
											else
											{
												$connections->setErrorMessage('capability_delete');
											}
										break;
										
										case 'approve':
											/*
											 * Check whether the current user delete an entry.
											 */
											if (current_user_can('connections_edit_entry'))
											{
												check_admin_referer($form->getNonce('bulk_action'), '_cn_wpnonce');
												processSetEntryStatuses('approved');
												wp_redirect('admin.php?page=connections_manage&display_messages=true');
											}
											else
											{
												$connections->setErrorMessage('capability_edit');
											}
										break;
										
										case 'unapprove':
											/*
											 * Check whether the current user delete an entry.
											 */
											if (current_user_can('connections_edit_entry'))
											{
												check_admin_referer($form->getNonce('bulk_action'), '_cn_wpnonce');
												processSetEntryStatuses('pending');
												wp_redirect('admin.php?page=connections_manage&display_messages=true');
											}
											else
											{
												$connections->setErrorMessage('capability_edit');
											}
										break;
										
										case 'public' || 'private' || 'unlisted':
											/*
											 * Check whether the current user can edit entries.
											 */
											if (current_user_can('connections_edit_entry'))
											{
												check_admin_referer($form->getNonce('bulk_action'), '_cn_wpnonce');
												processSetEntryVisibility();
												wp_redirect('admin.php?page=connections_manage&display_messages=true');
											}
											else
											{
												$connections->setErrorMessage('capability_edit');
											}
										break;
										
										default:
											wp_redirect('admin.php?page=connections_manage&display_messages=true');
										break;
									}
								}
								
								// Apply the user filters.
								if ( isset( $_POST['filter'] ) && ! empty( $_POST['filter'] ) )
								{
									check_admin_referer($form->getNonce('bulk_action'), '_cn_wpnonce');
									processSetUserFilter();
									wp_redirect('admin.php?page=connections_manage&display_messages=true');
									//wp_redirect( add_query_arg( 's' , urlencode( $_GET['s'] ) , 'admin.php?page=connections_manage' ) );
								}
								
								// Search.
								if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) )
								{
									check_admin_referer($form->getNonce('bulk_action'), '_cn_wpnonce');
									wp_redirect( add_query_arg( 's' , urlencode( $_POST['s'] ) , 'admin.php?page=connections_manage' ) );
								}
								else
								{
									wp_redirect('admin.php?page=connections_manage&display_messages=true');
								}
								
							break;
						}
					}
					
				break;
				
				//case 'connections_add':
					/*
					 * Check whether user can add entries
					 */
					/*if (current_user_can('connections_add_entry'))
					{
						if ($_POST['save'] && $_GET['action'] === 'add')
						{
							check_admin_referer($form->getNonce('add_entry'), '_cn_wpnonce');
							processEntry($_POST, 'add');
							wp_redirect('admin.php?page=connections_add&display_messages=true');
						}
					}
					else
					{
						$connections->setErrorMessage('capability_add');
					}*/
				//break;
				
				case 'category':
					/*
					 * Check whether user can edit Settings
					 */
					if (current_user_can('connections_edit_categories'))
					{
						if ($_GET['action'])
						{
							switch ($_GET['action'])
							{
								case 'add':
									check_admin_referer($form->getNonce('add_category'), '_cn_wpnonce');
									processAddCategory();
									wp_redirect('admin.php?page=connections_categories&display_messages=true');
								break;
								
								case 'update':
									check_admin_referer($form->getNonce('update_category'), '_cn_wpnonce');
									processUpdateCategory();
									wp_redirect('admin.php?page=connections_categories&display_messages=true');
								break;
								
								case 'delete':
									processDeleteCategory('delete');
									wp_redirect('admin.php?page=connections_categories&display_messages=true');
								break;
								
								case 'bulk_delete':
									check_admin_referer($form->getNonce('bulk_delete_category'), '_cn_wpnonce');
									processDeleteCategory('bulk_delete');
									wp_redirect('admin.php?page=connections_categories&display_messages=true');
								break;
							}
						}
					}
					else
					{
						$connections->setErrorMessage('capability_categories');
					}
				break;
				
				case 'setting':
					/*
					 * Check whether user can edit Settings
					 */
					if (current_user_can('connections_change_settings'))
					{
						if ($_POST['save'] && $_GET['action'] === 'update')
						{
							check_admin_referer($form->getNonce('update_settings'), '_cn_wpnonce');
							updateSettings();
							wp_redirect('admin.php?page=connections_settings&display_messages=true');
						}
					}
					else
					{
						$connections->setErrorMessage('capability_settings');
					}
				break;
				
				case 'template':
					/*
					 * Check whether user can manage Templates
					 */
					if (current_user_can('connections_manage_template'))
					{
						if ($_GET['action'])
						{
							switch ($_GET['action'])
							{
								case 'activate':
									processActivateTemplate();
									
									( !isset($_GET['type']) ) ? $tab = 'all' : $tab = esc_attr($_GET['type']);
									wp_redirect('admin.php?page=connections_templates&type=' . $tab . '&display_messages=true');
								break;
								
								case 'install':
									check_admin_referer($form->getNonce('install_template'), '_cn_wpnonce');
									processInstallTemplate();
									
									( !isset($_GET['type']) ) ? $tab = 'all' : $tab = esc_attr($_GET['type']);
									wp_redirect('admin.php?page=connections_templates&type=' . $tab . '&display_messages=true');
								break;
								
								case 'delete':
									processDeleteTemplate();
									
									( !isset($_GET['type']) ) ? $tab = 'all' : $tab = esc_attr($_GET['type']);
									wp_redirect('admin.php?page=connections_templates&type=' . $tab . '&display_messages=true');
								break;
							}
						}
					}
					else
					{
						// @TODO: Create template specific error message.
						$connections->setErrorMessage('capability_settings');
					}
				break;
				
				case 'role':
					
					/*
					 * Check whether user can edit roles
					 */
					if (current_user_can('connections_change_roles'))
					{
						if ($_GET['action'] === 'update')
						{
							check_admin_referer($form->getNonce('update_role_settings'), '_cn_wpnonce');
							updateRoleSettings();
							wp_redirect('admin.php?page=connections_roles&display_messages=true');
						}
					}
					else
					{
						$connections->setErrorMessage('capability_roles');
					}
				break;
			}
		}
		
		public function userActions($wp)
		{
			//print_r('<pre style="background-color: white;">'); print_r($wp->query_vars); print_r('</pre>');
			
			if ( array_key_exists('cnvc', $wp->query_vars) && $wp->query_vars['cnvc'] == '1' )
			{
				$token = esc_attr($_GET['cntoken']);
				$id = (integer) esc_attr($_GET['cnid']);
				
								
				if ( ! wp_verify_nonce( $token, 'download_vcard_' . $id) ) wp_die('Invalid vCard Token');
				
				global $connections;
				
				$entry = $connections->retrieve->entry($id);
				$vCard = new cnvCard($entry);
				
				$filename = sanitize_file_name($vCard->getFullFirstLastName());
				$data = $vCard->getvCard();
				
				header('Content-Type: text/x-vcard; charset=utf-8');
				header('Content-Disposition: attachment; filename=' . $filename . '.vcf');
				header('Content-Length: ' . strlen($data) );
				header('Pragma: public');
				header("Pragma: no-cache");
				header("Expires: 0");
				header('Connection: close');
				
				echo $data;
				exit;
			}
			
			if ( array_key_exists('cnpagename', $wp->query_vars) && $wp->query_vars['cnpagename'] == 'directory' )
			{
				global $template, $wp_query;
				
				/*
				 * Don't want WordPress applying these filters. Messes up the template with the auto P and texturize.
				 */
				remove_filter('the_content', 'wpautop');
				remove_filter('the_content', 'wptexturize');
				
				/*
				 * Clear the query just incase there were some custom queryies being called elsewhere.
				 */
				wp_reset_query();
				wp_reset_postdata();
				
				$atts = array();
				
				//if ( array_key_exists('cnlisttype', $wp->query_vars) && $wp->query_vars['cnlisttype'] != '' ) $atts['list_type'] = $wp->query_vars['cnlisttype'];
				//if ( array_key_exists('cnid', $wp->query_vars) && $wp->query_vars['cnid'] != '' ) $atts['id'] = $wp->query_vars['cnid'];
				
				/*
				 * Picked up this section from http://wordpress.org/extend/plugins/virtual-pages/
				 * Learned a bit from http://www.binarymoon.co.uk/2010/02/creating-wordpress-permalink-structure-custom-content/
				 * 
				 * 
				 * START
				 */
				
				// Pagination -- Will definately need this later.
				/*if(array_key_exists('paged', $wp->query_vars)) {
				    $this->paged = $wp->query_vars['paged'];
				}
				else {
				    $this->paged = 1;
				}*/
				
				/*
				 * Trick wp_query into thinking this is a page (necessary for wp_title() at least)
				 * Not sure if it's cheating or not to modify global variables in a filter
				 * but it appears to work and the codex doesn't directly say not to.
				 */
				$wp_query->is_page = TRUE;
				$wp_query->is_singular = TRUE;
				$wp_query->is_home = FALSE;
				$wp_query->is_archive = FALSE;
				$wp_query->is_category = FALSE;
				
				//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
				unset( $wp_query->query['error'] );
				$wp_query->query_vars['error'] = '';
				$wp_query->is_404 = FALSE;
				
				$wp_query->post_count = 1;
				
				/*
				 * Fake post ID to prevent WP from trying to show comments or 
				 * showing the edit link for a post that doesn't really exist.
				 * 
				 * Also used when creating the permalink. If ID >= 0 the filter
				 * that creates the permalink is not applied.
				 */
				$wp_query->post->ID = -1;
				
				/*
				 * The author ID for the post.  Usually 1 is the sys admin.
				 * @TODO Add option to the settings page to allow the defining of the page author.
				 */
				$wp_query->post->post_author = 94;
				
				/*
				 * When virtual pages are created in the admin, save the date created to be applied here.
				 */
				$wp_query->post->post_date = current_time('mysql');
				$wp_query->post->post_date_gmt = current_time('mysql', 1);
				
				$wp_query->post->post_content = '';
				
				/*
				 * Define the page title.
				 * @TODO Add option to the settings page to the page title.
				 */
				$wp_query->post->post_title = 'Connections Test';
				
				$wp_query->post->post_category = 0;
				
				$wp_query->post->post_excerpt = '';
				
				$wp_query->post->post_status = 'publish';
				
				$wp_query->post->comment_status = 'closed';
				
				$wp_query->post->ping_status = 'closed';
				
				$wp_query->post->post_password = '';
				
				/*
				 * This would be the page slug generated from the post title and used in the permalink.
				 * @TODO Well gotta set this.
				 */
				$wp_query->post->post_name = '';
				
				$wp_query->post->to_ping = '';
				
				$wp_query->post->pinged = '';
				
				/*
				 * When virtual pages are modified in the admin, save the date modfied to be applied here.
				 */
				$wp_query->post->post_modified = current_time('mysql');
				$wp_query->post->post_modified_gmt = current_time('mysql', 1);
				
				$wp_query->post->post_content_filtered = '';
				
				$wp_query->post->parent_post = 0;
				
				$wp_query->post->guid = esc_url("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				
				$wp_query->post->menu_order = 0;
				
				$wp_query->post->post_type = 'page';
				
				$wp_query->post->post_mime_type = '';
				
				$wp_query->post->comment_count = 0;
				
				$wp_query->post->restricted = 0;
				
				$wp_query->post->ancestors = array();
				
				$wp_query->post->filter = 'raw';
				
				
				// Dupe the post data the posts array;
				$wp_query->posts[] =& $wp_query->post;
				
				/*
				 * Picked up this section from http://wordpress.org/extend/plugins/virtual-pages/
				 * 
				 * END
				 */
				
				/*
				 * Setup the permalink.
				 */
				add_filter( 'page_link', array(&$this, 'createPermalink'), 10, 3 );
				
				/*
				 * Set the attributes and then populate the content.
				 */
				add_filter( 'cn_list_template_init', array(&$this, 'setconnectionsListAttsPre') );
				add_filter( 'cn_list_atts', array(&$this, 'setconnectionsListAttsPre') );
				add_action( 'the_content', '_connections_list' );
				
				/*
				 * Grab the path to the theme's page template.
				 * @TODO Add option to the settings page to allow the selection of custom page templates.
				 */
				$template = get_query_template('page');
				
				// Include the page template and if the $wp_query object was loaded correctly the page should 'just work'.
				include ($template);
				
				// Exit so WP doesn't continue to try to process the page.
				exit;
			}
			
		}
		
		public function setconnectionsListAttsPre($atts)
		{
			global $wp;
			
			if ( array_key_exists('cnlt', $wp->query_vars) && $wp->query_vars['cnlt'] != '' ) $atts['list_type'] = $wp->query_vars['cnlt'];
			if ( array_key_exists('cncatnm', $wp->query_vars) && $wp->query_vars['cncatnm'] != '' ) $atts['category_slug'] = $wp->query_vars['cncatnm'];
			if ( array_key_exists('cnid', $wp->query_vars) && $wp->query_vars['cnid'] != '' ) $atts['id'] = $wp->query_vars['cnid'];
			if ( array_key_exists('cnname', $wp->query_vars) && $wp->query_vars['cnname'] != '' ) $atts['slug'] = $wp->query_vars['cnname'];
			
			return $atts;	
		}
		
		public function createPermalink( $link = '', $id = 0, $sample = FALSE )
		{
			if ( $id >= 0 )
			{
				return $link;
			}
			else
			{
				return esc_url( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REDIRECT_URL'] );
			}
		}
	}
	
	global $connections;
	$connections = new connectionsLoad();
	
}