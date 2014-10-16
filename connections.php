<?php
/**
 * Plugin Name: Connections
 * Plugin URI: http://connections-pro.com/
 * Description: A business directory and address book manager.
 * Version: 8.1.5
 * Author: Steven A. Zahm
 * Author URI: http://connections-pro.com/
 * Text Domain: connections
 * Domain Path: languages
 *
 * Copyright 2014  Steven A. Zahm  ( email : helpdesk@connections-pro.com )
 *
 * Connections is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Connections; if not, see <http://www.gnu.org/licenses/>.
 *
 * @package Connections
 * @category Core
 * @author Steven A. Zahm
 * @version 8.1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Connections Class.
 *
 * @since unknown
 */
if ( ! class_exists( 'connectionsLoad' ) ) {

	final class connectionsLoad {

		/**
		 * @var Stores the instance of this class.
		 *
		 * @access private
		 * @since 0.7.9
		 */
		private static $instance;

		public $currentUser;
		public $options;
		public $retrieve;
		public $term;

		/**
		 * Stores the page hook values returned from the add_menu_page & add_submenu_page functions
		 *
		 * @access public
		 * @since unknown
		 * @var (object)
		 */
		public $pageHook;

		/**
		 * The Connections Settings API Wrapper class.
		 *
		 * @access public
		 * @since unknown
		 * @var (object)
		 */
		public $settings;

		/**
		 * Do the database upgrade.
		 *
		 * @access public
		 * @since unknown
		 * @var (bool)
		 */
		public $dbUpgrade = FALSE;

		/**
		 * Stores the template parts object and any templates activated by the cnTemplateFactory object.
		 *
		 * NOTE: Technically not necessary to load the template parts into this opject but it's required
		 * for backward compatibility for templates expecting to find those methods as part of this object.
		 *
		 * @access public
		 * @since 0.7.6
		 * @var (object)
		 */
		public $template;

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @access public
		 * @since 0.7.9
		 */
		public function __construct() { /* Do nothing here */ }

		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof connectionsLoad ) ) {

				self::$instance = new connectionsLoad;

				self::defineConstants();
				self::includes();

				self::$instance->options     = new cnOptions();
				self::$instance->settings    = cnSettingsAPI::getInstance();
				self::$instance->pageHook    = new stdClass();
				self::$instance->currentUser = new cnUser();
				self::$instance->retrieve    = new cnRetrieve();
				self::$instance->term        = new cnTerms();
				self::$instance->template    = new cnTemplatePart();
				self::$instance->url         = new cnURL();

				/*
				 * Load translation. NOTE: This should be ran on the init action hook because
				 * function calls for translatable strings, like __() or _e(), execute before
				 * the language files are loaded will not be loaded.
				 *
				 * NOTE: Any portion of the plugin w/ translatable strings should be bound to the init action hook or later.
				 * NOTE: Priority set at -1 because the Metabox API runs at priority 0. The translation files need to be
				 * 	loaded before the metaboxes are registered by the API or the metabox head will not display with the translated strings.
				 */
				add_action( 'init', array( __CLASS__ , 'loadTextdomain' ), -1 );

				/*
				 * Process front end actions.
				 */
				add_action( 'template_redirect' , array( __CLASS__, 'frontendActions' ) );

				// Activation/Deactivation hooks
				register_activation_hook( dirname( __FILE__ ) . '/connections.php', array( __CLASS__, 'activate' ) );
				register_deactivation_hook( dirname( __FILE__ ) . '/connections.php', array( __CLASS__, 'deactivate' ) );

				// @TODO: Create uninstall method to remove options and tables.
				// register_uninstall_hook( dirname(__FILE__) . '/connections.php', array('connectionsLoad', 'uninstall') );

				// Init the options if there is a version change just in case there were any changes.
				if ( version_compare( self::$instance->options->getVersion(), CN_CURRENT_VERSION ) < 0 ) self::$instance->initOptions();
				// $connections->options->setDBVersion('0.1.8'); $connections->options->saveOptions();

				do_action( 'cn_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Define the core constants.
		 *
		 * @access private
		 * @since unknown
		 * @return (void)
		 */
		private static function defineConstants() {
			global $wpdb, $blog_id;

			// Whether or not to log actions and results for debugging.
			if ( ! defined( 'CN_LOG' ) ) define( 'CN_LOG', FALSE );

			/*
			 * Version Constants
			 */
			define( 'CN_CURRENT_VERSION', '8.1.5' );
			define( 'CN_DB_VERSION', '0.1.9' );

			/*
 			 * Used for EDD SL Updater
 			 */
 			define( 'CN_UPDATE_URL', 'http://connections-pro.com' );

			/*
			 * Core Constants
			 */
			define( 'CN_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CN_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CN_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CN_URL', plugin_dir_url( __FILE__ ) );

			/*
			 * Core constants that can be overridden by setting in wp-config.php.
			 *
			 * NOTE: If CN_CACHE_PATH is overridden, the path will need updated in timthumb-config.php also.
			 */
			if ( ! defined( 'CN_TEMPLATE_PATH' ) )
				define( 'CN_TEMPLATE_PATH', CN_PATH . 'templates/' );

			if ( ! defined( 'CN_TEMPLATE_URL' ) )
				define( 'CN_TEMPLATE_URL', CN_URL . 'templates/' );

			if ( ! defined( 'CN_CACHE_PATH' ) )
				define( 'CN_CACHE_PATH', CN_PATH . 'cache/' );

			if ( ! defined( 'CN_ADMIN_MENU_POSITION' ) )
				define( 'CN_ADMIN_MENU_POSITION', NULL );

			/*
			 * To run Connections in single site mode on multi-site.
			 * Add to wp-config.php: define('CN_MULTISITE_ENABLED', FALSE);
			 *
			 * @credit lancelot-du-lac
			 * @url http://wordpress.org/support/topic/plugin-connections-support-multisite-in-single-mode
			 */
			if ( ! defined( 'CN_MULTISITE_ENABLED' ) ) {

				if ( is_multisite() ) {

					define( 'CN_MULTISITE_ENABLED', TRUE );

				} else {

					define( 'CN_MULTISITE_ENABLED', FALSE );
				}
			}

			// Set the root image permalink endpoint name.
			if ( ! defined( 'CN_IMAGE_ENDPOINT' ) )
				define( 'CN_IMAGE_ENDPOINT', 'cn-image' );

			// Set images subdirectory folder name.
			if ( ! defined( 'CN_IMAGE_DIR_NAME' ) )
				define( 'CN_IMAGE_DIR_NAME', 'connections-images' );

			/*
			 * Core constants that can be overrideen in wp-config.php
			 * which enable support for multi-site file locations.
			 */
			if ( is_multisite() && CN_MULTISITE_ENABLED ) {

				// Get the core WP uploads info.
				$uploadInfo = wp_upload_dir();

				if ( ! defined( 'CN_IMAGE_PATH' ) ) {

					// define( 'CN_IMAGE_PATH', WP_CONTENT_DIR . '/sites/' . $blog_id . '/connection_images/' );
					define( 'CN_IMAGE_PATH', trailingslashit( $uploadInfo['basedir'] ) . CN_IMAGE_DIR_NAME . DIRECTORY_SEPARATOR );
				}

				if ( ! defined( 'CN_IMAGE_BASE_URL' ) ) {

					// define( 'CN_IMAGE_BASE_URL', network_home_url( '/wp-content/sites/' . $blog_id . '/connection_images/' ) );
					define( 'CN_IMAGE_BASE_URL', trailingslashit( $uploadInfo['baseurl'] ) . CN_IMAGE_DIR_NAME . '/' );
				}

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_PATH' ) )
					define( 'CN_CUSTOM_TEMPLATE_PATH', WP_CONTENT_DIR . '/blogs.dir/' . $blog_id . '/connections_templates/' );

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_URL' ) )
					define( 'CN_CUSTOM_TEMPLATE_URL', network_home_url( '/wp-content/blogs.dir/' . $blog_id . '/connections_templates/' ) );

				// Define the relative URL/s.
				define( 'CN_RELATIVE_URL', str_replace( network_home_url(), '', CN_URL ) );
				define( 'CN_TEMPLATE_RELATIVE_URL', str_replace( network_home_url(), '', CN_URL . 'templates/' ) );
				define( 'CN_IMAGE_RELATIVE_URL', str_replace( network_home_url(), '', CN_IMAGE_BASE_URL ) );
				define( 'CN_CUSTOM_TEMPLATE_RELATIVE_URL', str_replace( network_home_url(), '', CN_CUSTOM_TEMPLATE_URL ) );

			} else {

				/*
				 * Pulled this block of code from wp_upload_dir(). Using this rather than simply using wp_upload_dir()
				 * because wp_upload_dir() will always return the upload dir/url (/sites/{id}/) for the current network site.
				 *
				 * We do not want this behavior if forcing Connections into single site mode on a multisite
				 * install of WP. Addtionally we do not want the year/month sub dir appended.
				 *
				 * A filter could be used, hooked into `upload_dir` but that would be a little heavy as everytime the custom
				 * dir/url would be needed the filter would have to be added and then removed not to mention other plugins could
				 * interfere by hooking into `upload_dir`.
				 *
				 * --> START <--
				 */
				$siteurl     = get_option( 'siteurl' );
				$upload_path = trim( get_option( 'upload_path' ) );

				if ( empty( $upload_path ) || 'wp-content/uploads' == $upload_path ) {

					$dir = WP_CONTENT_DIR . '/uploads';

				} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {

					// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
					$dir = path_join( ABSPATH, $upload_path );

				} else {

					$dir = $upload_path;
				}

				if ( ! $url = get_option( 'upload_url_path' ) ) {

					if ( empty($upload_path) || ( 'wp-content/uploads' == $upload_path ) || ( $upload_path == $dir ) ) {

						$url = WP_CONTENT_URL . '/uploads';

					} else {

						$url = trailingslashit( $siteurl ) . $upload_path;
					}

				}

				// Obey the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
				// We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
				if ( defined( 'UPLOADS' ) && ! ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) ) {

					$dir = ABSPATH . UPLOADS;
					$url = trailingslashit( $siteurl ) . UPLOADS;
				}
				/*
				 * --> END <--
				 */

				if ( ! defined( 'CN_IMAGE_PATH' ) )
					define( 'CN_IMAGE_PATH', $dir . DIRECTORY_SEPARATOR . CN_IMAGE_DIR_NAME . DIRECTORY_SEPARATOR );

				if ( ! defined( 'CN_IMAGE_BASE_URL' ) )
					define( 'CN_IMAGE_BASE_URL', $url . '/' . CN_IMAGE_DIR_NAME . '/' );

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_PATH' ) )
					define( 'CN_CUSTOM_TEMPLATE_PATH', WP_CONTENT_DIR . '/connections_templates/' );

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_URL' ) )
					define( 'CN_CUSTOM_TEMPLATE_URL', $url . '/connections_templates/' );

				// Define the relative URL/s.
				define( 'CN_RELATIVE_URL', str_replace( home_url(), '', CN_URL ) );
				define( 'CN_TEMPLATE_RELATIVE_URL', str_replace( home_url(), '', CN_URL . 'templates/' ) );
				define( 'CN_IMAGE_RELATIVE_URL', str_replace( home_url(), '', CN_IMAGE_BASE_URL ) );
				define( 'CN_CUSTOM_TEMPLATE_RELATIVE_URL', str_replace( home_url(), '', CN_CUSTOM_TEMPLATE_URL ) );
			}

			/*
			 * Set the table prefix accordingly depedning if Connections is installed on a multisite WP installation.
			 */
			$prefix = ( is_multisite() && CN_MULTISITE_ENABLED ) ? $wpdb->prefix : $wpdb->base_prefix;

			/*
			 * Define the constants that can be used to reference the custom tables
			 */
			define( 'CN_ENTRY_TABLE', $prefix . 'connections' );
			define( 'CN_ENTRY_ADDRESS_TABLE', $prefix . 'connections_address' );
			define( 'CN_ENTRY_PHONE_TABLE', $prefix . 'connections_phone' );
			define( 'CN_ENTRY_EMAIL_TABLE', $prefix . 'connections_email' );
			define( 'CN_ENTRY_MESSENGER_TABLE', $prefix . 'connections_messenger' );
			define( 'CN_ENTRY_SOCIAL_TABLE', $prefix . 'connections_social' );
			define( 'CN_ENTRY_LINK_TABLE', $prefix . 'connections_link' );
			define( 'CN_ENTRY_DATE_TABLE', $prefix . 'connections_date' );

			define( 'CN_ENTRY_TABLE_META', $prefix . 'connections_meta' );
			define( 'CN_TERMS_TABLE', $prefix . 'connections_terms' );
			define( 'CN_TERM_TAXONOMY_TABLE', $prefix . 'connections_term_taxonomy' );
			define( 'CN_TERM_RELATIONSHIP_TABLE', $prefix . 'connections_term_relationships' );

		}

		private static function includes() {

			/**
			 * @TODO: Load dependencies as needed. For example load only classes needed in the admin and frontend
			 */

			//Current User objects
			require_once CN_PATH . 'includes/class.user.php'; // Required for activation
			//Terms Objects
			require_once CN_PATH . 'includes/class.terms.php'; // Required for activation
			//Category Objects
			require_once CN_PATH . 'includes/class.category.php'; // Required for activation, entry list
			//Retrieve objects from the db.
			require_once CN_PATH . 'includes/class.retrieve.php'; // Required for activation
			//HTML FORM objects
			require_once CN_PATH . 'includes/class.form.php'; // Required for activation
			//date objects
			require_once CN_PATH . 'includes/class.date.php'; // Required for activation, entry list, add entry
			// cnCache
			require_once CN_PATH . 'includes/class.cache.php';

			// The class for managing metaboxes.
			// Must require BEFORE class.functions.php.
			require_once CN_PATH . 'includes/class.metabox-api.php';

			// The class for registering the core metaboxes and fields for the add/edit entry admin pages.
			// Must require AFTER class.metabox-api.php.
			require_once CN_PATH . 'includes/class.metabox-entry.php';

			/*
			 * Entry classes. // --> START <-- \\
			 */

			// Entry data.
			require_once CN_PATH . 'includes/entry/class.entry-data.php'; // Required for activation, entry list

			// Entry HTML template blocks.
			require_once CN_PATH . 'includes/entry/class.entry-output.php'; // Required for activation, entry list
			require_once CN_PATH . 'includes/entry/class.entry-html.php';
			require_once CN_PATH . 'includes/entry/class.entry-shortcode.php';

			// Entry vCard.
			require_once CN_PATH . 'includes/entry/class.entry-vcard.php'; // Required for front end

			// Entry actions.
			require_once CN_PATH . 'includes/entry/class.entry-actions.php';

			/*
			 * Entry classes. // --> END <-- \\
			 */

			// HTML elements class.
			require_once CN_PATH . 'includes/class.html.php';

			// Meta API
			require_once CN_PATH . 'includes/class.meta.php';

			//plugin utility objects
			require_once CN_PATH . 'includes/class.utility.php'; // Required for activation, entry list

			// Sanitization.
			require_once CN_PATH . 'includes/class.sanitize.php';

			// geocoding
			require_once CN_PATH . 'includes/class.geo.php'; // Required

			// thumbnails
			require_once CN_PATH . 'includes/image/class.image.php';

			// Shortcodes
			// NOTE This is required in both the admin and frontend. The shortcode callback is used on the Dashboard admin page.
			require_once CN_PATH . 'includes/shortcode/inc.shortcodes.php';
			require_once CN_PATH . 'includes/shortcode/class.shortcode.php';
			require_once CN_PATH . 'includes/shortcode/class.shortcode-connections.php';
			require_once CN_PATH . 'includes/shortcode/class.shortcode-thumbnail.php';
			require_once CN_PATH . 'includes/shortcode/class.shortcode-thumbnail-responsive.php';

			// require_once CN_PATH . 'includes/class.shortcode-upcoming_list.php';

			// The class that inits the registered query vars, rewites reuls and canonical redirects.
			require_once CN_PATH . 'includes/class.rewrite.php';

			// Load the Connections Settings API Wrapper Class.
			require_once CN_PATH . 'includes/settings/class.settings-api.php';

			// plugin option objects
			require_once CN_PATH . 'includes/settings/class.options.php'; // Required for activation

			// Load the Connections core settings admin page tabs, section and fields using the WordPress Settings API.
			require_once CN_PATH . 'includes/settings/class.settings.php';

			// Load the class that manages the registration and enqueueing of CSS and JS files.
			require_once CN_PATH . 'includes/class.locate.php';
			require_once CN_PATH . 'includes/class.scripts.php';

			// Class for processing email.
			require_once CN_PATH . 'includes/email/class.email.php';

			// Class for handling email template registration and management.
			require_once CN_PATH . 'includes/email/class.email-template-api.php';

			// Class for registering the core email templates.
			require_once CN_PATH . 'includes/email/class.default-template.php';

			// The class for working with the file system.
			require_once CN_PATH . 'includes/class.filesystem.php';

			// require_once CN_PATH . 'includes/class.results.php';

			if ( is_admin() ) {

				/*
				 * Include the Screen Options class by Janis Elsts
				 * http://w-shadow.com/blog/2010/06/29/adding-stuff-to-wordpress-screen-options/
				 */
				include_once CN_PATH . 'includes/libraries/screen-options/screen-options.php';

				// The class for handling admin notices.
				require_once CN_PATH . 'includes/admin/class.message.php';

				// Class used for managing role capabilites.
				require_once CN_PATH . 'includes/admin/class.capabilities.php';

				// The class for adding admin menu and registering the menu callbacks.
				require_once CN_PATH . 'includes/admin/class.menu.php';

				// The class for registering the core metaboxes for the dashboard admin page.
				// Must require AFTER class.metabox-api.php.
				require_once CN_PATH . 'includes/admin/class.metabox-dashboard.php';

				// The class for processing admin actions.
				require_once CN_PATH . 'includes/admin/class.actions.php';

				// The class for registering general admin actions.
				// Must require AFTER class.metabox-api.php and class.actions.php.
				require_once CN_PATH . 'includes/admin/class.functions.php';

				// The class for managing license keys and settings.
				require_once CN_PATH . 'includes/admin/class.license.php';

			} else {

				// Class for SEO
				require_once CN_PATH . 'includes/class.seo.php';

			}

			// Include the core templates that use the Template APIs introduced in 0.7.6
			// Must include BEFORE class.template-api.php.
			include_once CN_PATH . 'templates/names/names.php';
			include_once CN_PATH . 'templates/card/card-default.php';
			include_once CN_PATH . 'templates/card-bio/card-bio.php';
			include_once CN_PATH . 'templates/card-single/card-single-default.php';
			include_once CN_PATH . 'templates/card-tableformat/card-table-format.php';
			include_once CN_PATH . 'templates/profile/profile.php';
			include_once CN_PATH . 'templates/anniversary-dark/anniversary-dark.php';
			include_once CN_PATH . 'templates/anniversary-light/anniversary-light.php';
			include_once CN_PATH . 'templates/birthday-dark/birthday-dark.php';
			include_once CN_PATH . 'templates/birthday-light/birthday-light.php';
			include_once CN_PATH . 'templates/dashboard-recent-added/dashboard-recent-added.php';
			include_once CN_PATH . 'templates/dashboard-recent-modified/dashboard-recent-modified.php';
			include_once CN_PATH . 'templates/dashboard-upcoming/dashboard-upcoming.php';

			// Template APIs.
			// Must require AFTER the core templates.
			require_once CN_PATH . 'includes/template/class.template-api.php';
			require_once CN_PATH . 'includes/template/class.template-parts.php';
			require_once CN_PATH . 'includes/template/class.template-shortcode.php';
			require_once CN_PATH . 'includes/template/class.template-compatibility.php';
			require_once CN_PATH . 'includes/template/class.template.php';

			require_once CN_PATH . 'includes/inc.plugin-compatibility.php';
		}

		/**
		 * Load the plugin translation.
		 *
		 * Credit: Adapted from Ninja Forms / Easy Digital Downloads.
		 *
		 * @access private
		 * @since 0.7.9
		 * @uses apply_filters()
		 * @uses get_locale()
		 * @uses load_textdomain()
		 * @uses load_plugin_textdomain()
		 * @return (void)
		 */
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections';

			// Filter for the plugin languages folder.
			$languagesDirectory = apply_filters( 'connections_lang_dir', CN_DIR_NAME . '/languages/' );

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Filter for WordPress languages directory.
			$wpLanguagesDirectory = apply_filters(
				'connections_wp_lang_dir',
				WP_LANG_DIR . '/connections/' . sprintf( '%1$s-%2$s.mo', $textdomain, $locale )
			);

			// Translations: First, look in WordPress' "languages" folder = custom & update-secure!
			load_textdomain( $textdomain, $wpLanguagesDirectory );

			// Translations: Secondly, look in plugin's "languages" folder = default.
			load_plugin_textdomain( $textdomain, FALSE, $languagesDirectory );
		}

		/**
		 * During activation this will initiate the options.
		 */
		private function initOptions() {
			$version = $this->options->getVersion();

			switch ( TRUE ) {

				case ( version_compare( $version, '0.7.3', '<' ) ) :
					/*
					 * Retrieve the settings stored prior to 0.7.3 and migrate them
					 * so they will be accessible in the structure supported by the
					 * Connections WordPress Settings API Wrapper Class.
					 */
					if ( get_option( 'connections_options' ) !== FALSE ) {
						$options = get_option( 'connections_options' );

						if ( get_option( 'connections_login' ) === FALSE ) {
							update_option( 'connections_login' , array(
									'required' => $options['settings']['allow_public'],
									'message' => 'Please login to view the directory.'
								)
							);
						}

						if ( get_option( 'connections_visibility' ) === FALSE ) {
							update_option( 'connections_visibility' , array(
									'allow_public_override' => $options['settings']['allow_public_override'],
									'allow_private_override' => $options['settings']['allow_private_override']
								)
							);
						}

						if ( get_option( 'connections_image_thumbnail' ) === FALSE ) {
							update_option( 'connections_image_thumbnail' , array(
									'quality' => $options['settings']['image']['thumbnail']['quality'],
									'width' => $options['settings']['image']['thumbnail']['x'],
									'height' => $options['settings']['image']['thumbnail']['y'],
									'ratio' => $options['settings']['image']['thumbnail']['crop']
								)
							);
						}
						if ( get_option( 'connections_image_medium' ) === FALSE ) {
							update_option( 'connections_image_medium' , array(
									'quality' => $options['settings']['image']['entry']['quality'],
									'width' => $options['settings']['image']['entry']['x'],
									'height' => $options['settings']['image']['entry']['y'],
									'ratio' => $options['settings']['image']['entry']['crop']
								)
							);
						}

						if ( get_option( 'connections_image_large' ) === FALSE ) {
							update_option( 'connections_image_large' , array(
									'quality' => $options['settings']['image']['profile']['quality'],
									'width' => $options['settings']['image']['profile']['x'],
									'height' => $options['settings']['image']['profile']['y'],
									'ratio' => $options['settings']['image']['profile']['crop']
								)
							);
						}

						if ( get_option( 'connections_image_logo' ) === FALSE ) {
							update_option( 'connections_image_logo' , array(
									'quality' => $options['settings']['image']['logo']['quality'],
									'width' => $options['settings']['image']['logo']['x'],
									'height' => $options['settings']['image']['logo']['y'],
									'ratio' => $options['settings']['image']['logo']['crop']
								)
							);
						}

						if ( get_option( 'connections_compatibility' ) === FALSE ) {
							update_option( 'connections_compatibility' , array(
									'google_maps_api' => $options['settings']['advanced']['load_google_maps_api'],
									'javascript_footer' => $options['settings']['advanced']['load_javascript_footer'] )
							);
						}

						if ( get_option( 'connections_debug' ) === FALSE ) update_option( 'connections_debug' , array( 'debug_messages' => $options['debug'] ) );

						unset( $options );

					}


				case ( version_compare( $version, '0.7.4', '<' ) ) :
					/*
					 * The option to disable keyowrd search was added in version 0.7.4. Set this option to be enabled by default.
					 */
					$options = get_option( 'connections_search' );
					$options['keyword_enabled'] = 1;

					update_option( 'connections_search', $options );
					unset( $options );

				case ( version_compare( $version, '0.8', '<' ) ) :
					/*
					 * The option to disable keyowrd search was added in version 0.7.4. Set this option to be enabled by default.
					 */
					$options = get_option( 'connections_compatibility' );
					$options['css'] = 1;

					update_option( 'connections_compatibility', $options );
					unset( $options );

					$options = get_option( 'connections_display_results' );
					$options['search_message'] = 1;

					update_option( 'connections_display_results', $options );
					unset( $options );
			}

			if ( $this->options->getDefaultTemplatesSet() === NULL ) $this->options->setDefaultTemplates();

			// Class used for managing role capabilites.
			if ( ! class_exists( 'cnRole' ) ) require_once CN_PATH . 'includes/admin/class.capabilities.php';

			if ( $this->options->getCapabilitiesSet() != TRUE ) {

				cnRole::reset();
				$this->options->defaultCapabilitiesSet( TRUE );
			}

			// Increment the version number.
			$this->options->setVersion( CN_CURRENT_VERSION );

			// Save the options
			$this->options->saveOptions();

			/*
			 * This option is added for a check that will force a flush_rewrite() in connectionsLoad::adminInit() once.
			 * Should save the user from having to "save" the permalink settings.
			 */
			update_option( 'connections_flush_rewrite', '1' );
		}

		/**
		 * This is a deprecated helper function left in place until all instances of it are removed from the code base.
		 * This purposefully is blank.
		 *
		 * @access public
		 * @since unknown
		 * @deprecated 0.7.5
		 * @return void
		 */
		public function displayMessages() { /* Do nothing here */ }

		/**
		 * Set a runtime action/error message.
		 * This is a deprecated helper function left in place until all instances of it are removed from the code base.
		 *
		 * @access public
		 * @since unknown
		 * @deprecated 0.7.5
		 * @return void
		 */
		public function setRuntimeMessage( $type , $message ) {
			cnMessage::runtime( $type, $message );
		}

		/**
		 * Store an error code.
		 * This is a deprecated helper function left in place until all instances of it are removed from the code base.
		 *
		 * @access public
		 * @since unknown
		 * @deprecated 0.7.5
		 * @return void
		 */
		public function setErrorMessage( $code ) {
			cnMessage::set( 'error', $code );
		}

		/**
		 * Store a success code.
		 * This is a deprecated helper function left in place until all instances of it are removed from the code base.
		 *
		 * @access public
		 * @since unknown
		 * @deprecated 0.7.5
		 * @return void
		 */
		public function setSuccessMessage( $code ) {
			cnMessage::set( 'success', $code );
		}

		/**
		 * Called when activating Connections via the activation hook.
		 */
		public static function activate() {
			global $wpdb, $connections;

			require_once CN_PATH . 'includes/class.schema.php';

			// Create the table structure.
			cnSchema::create();

			// Create the required directories and attempt to make them writable.
			cnFileSystem::mkdirWritable( CN_CACHE_PATH );
			cnFileSystem::mkdirWritable( CN_IMAGE_PATH );
			// cnFileSystem::mkdirWritable( CN_CUSTOM_TEMPLATE_PATH );

			// Add a blank index.php file.
			cnFileSystem::mkIndex( CN_IMAGE_PATH );
			// cnFileSystem::mkIndex( CN_CUSTOM_TEMPLATE_PATH );

			// Add an .htaccess file, create it if one doesn't exist, and add the no indexes option.
			// cnFileSystem::noIndexes( CN_IMAGE_PATH ); // Causes some servers to respond w/ 403 when servering images.
			// cnFileSystem::noIndexes( CN_CUSTOM_TEMPLATE_PATH );

			// Create a .htaccess file in the timthumb folder to allow it to be called directly.
			cnFileSystem::permitTimThumb( CN_PATH . '/vendor/timthumb' );

			$connections->initOptions();

			/*
			 * Add the page rewrite rules.
			 */
			add_filter( 'root_rewrite_rules', array( 'cnRewrite', 'addRootRewriteRules' ) );
			add_filter( 'page_rewrite_rules', array( 'cnRewrite', 'addPageRewriteRules' ) );

			// Flush so they are rebuilt.
			flush_rewrite_rules();
		}

		/**
		 * Called when deactivating Connections via the deactivation hook.
		 */
		public static function deactivate() {
			/*
			 * Since we're adding the rewrite rules using a filter, make sure to remove the filter
			 * before flushing, otherwise the rules will not be removed.
			 */
			remove_filter( 'root_rewrite_rules', array( 'cnRewrite', 'addRootRewriteRules' ) );
			remove_filter( 'page_rewrite_rules', array( 'cnRewrite', 'addPageRewriteRules' ) );

			// Flush so they are rebuilt.
			flush_rewrite_rules();

			//global $options;

			/* This should be occur in the unistall hook
			$this->options->removeDefaultCapabilities();
			*/

			//  DROP TABLE `cnpfresh_connections`, `cnpfresh_connections_terms`, `cnpfresh_connections_term_relationships`, `cnpfresh_connections_term_taxonomy`;
			//  DELETE FROM `nhonline_freshcnpro`.`cnpfresh_options` WHERE `cnpfresh_options`.`option_name` = 'connections_options'
		}

		/**
		 * This action will handle frontend process requests, currently only creating the vCard for download.
		 *
		 * @TODO If no vcard is found should redirect to an error message.
		 * @access private
		 * @since 0.7.3
		 * @return void
		 */
		public static function frontendActions() {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			$process = get_query_var( 'cn-process' );
			$token = get_query_var( 'cn-token' );
			$id = (integer) get_query_var( 'cn-id' );

			if ( $process === 'vcard' ) {

				$slug = get_query_var( 'cn-entry-slug' ); //var_dump($slug);

				/*
				 * If the token and id values were set, the link was likely from the admin.
				 * Check for those values and validate the token. The primary reason for this
				 * to be able to download vCards of entries that are set to "Unlisted".
				 */
				if ( ! empty( $id ) && ! empty( $token ) ) {

					if ( ! wp_verify_nonce( $token, 'download_vcard_' . $id ) ) wp_die( 'Invalid vCard Token' );

					$entry = $instance->retrieve->entry( $id );

					// Die if no entry was found.
					if ( empty( $entry ) ) wp_die( __( 'vCard not available for download.', 'connections' ) );

					$vCard = new cnvCard( $entry ); //var_dump($vCard);die;

				} else {

					$entry = $instance->retrieve->entries( array( 'slug' => $slug ) ); //var_dump($entry);die;

					// Die if no entry was found.
					if ( empty( $entry ) ) wp_die( __( 'vCard not available for download.', 'connections' ) );

					$vCard = new cnvCard( $entry[0] ); //var_dump($vCard);die;
				}


				$filename = sanitize_file_name( $vCard->getName() ); //var_dump($filename);
				$data     = $vCard->getvCard(); //var_dump($data);die;


				header( 'Content-Description: File Transfer');
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . $filename . '.vcf' );
				header( 'Content-Length: ' . strlen( $data ) );
				header( 'Pragma: public' );
				header( "Pragma: no-cache" );
				//header( "Expires: 0" );
				header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
				header( 'Cache-Control: private' );
				// header( 'Connection: close' );
				ob_clean();
				flush();

				echo $data;
				exit;
			}
		}
	}

	/**
	 * The main function responsible for returning the Connections instance
	 * to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * NOTE: Decalring an instance in the global var @connections to provide backward
	 * compatibility with many internal methods, template and extensions that expect it.
	 *
	 * Example: <?php $instance = Connections_Directory(); ?>
	 *
	 * @access public
	 * @since 0.7.9
	 * @global $connections
	 * @return (object)
	 */
	function Connections_Directory() {
		global $connections;

		$connections = connectionsLoad::instance();
		return $connections;
	}

	// Start Connections.
	Connections_Directory();
}
