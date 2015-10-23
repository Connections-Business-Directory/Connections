<?php
/**
 * Plugin Name: Connections
 * Plugin URI: http://connections-pro.com/
 * Description: A business directory and address book manager.
 * Version: 8.5.2
 * Author: Steven A. Zahm
 * Author URI: http://connections-pro.com/
 * Text Domain: connections
 * Domain Path: languages
 *
 * Copyright 2015  Steven A. Zahm  ( email : helpdesk@connections-pro.com )
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
 * @version 8.5.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Connections Class.
 *
 * @since unknown
 */
if ( ! class_exists( 'connectionsLoad' ) ) {

	/**
	 * Class connectionsLoad
	 */
	final class connectionsLoad {

		/**
		 * Stores the instance of this class.
		 *
		 * @access private
		 * @since  0.7.9
		 *
		 * @var connectionsLoad
		 */
		private static $instance;

		/**
		 * @access private
		 * @since  unknown
		 *
		 * @var cnUser
		 */
		public $currentUser;

		/**
		 * @access public
		 * @since  unknown
		 *
		 * @var cnOptions
		 */
		public $options;

		/**
		 * @access public
		 * @since  unknown
		 *
		 * @var cnRetrieve
		 */
		public $retrieve;

		/**
		 * @access public
		 * @since  unknown
		 *
		 * @var cnTerms
		 */
		public $term;

		/**
		 * Stores the page hook values returned from the add_menu_page & add_submenu_page functions
		 *
		 * @access public
		 * @since  unknown
		 *
		 * @var object
		 */
		public $pageHook;

		/**
		 * The Connections Settings API Wrapper class.
		 *
		 * @access public
		 * @since  unknown
		 *
		 * @var cnSettingsAPI
		 */
		public $settings;

		/**
		 * Do the database upgrade.
		 *
		 * @access public
		 * @since  unknown
		 *
		 * @var bool
		 */
		public $dbUpgrade = FALSE;

		/**
		 * Stores the template parts object and any templates activated by the cnTemplateFactory object.
		 *
		 * NOTE: Technically not necessary to load the template parts into this opject but it's required
		 * for backward compatibility for templates expecting to find those methods as part of this object.
		 *
		 * @access public
		 * @since  0.7.6
		 *
		 * @var cnTemplatePart
		 */
		public $template;

		/**
		 * @access public
		 * @since  unknown
		 *
		 * @var cnURL
		 */
		public $url;

		/**
		 * The following vars are being set in the cnEntry and cnRetrieve classes.
		 * @todo Code should be refactor to remove their usage.
		 */
		public $lastQuery;
		public $lastQueryError;
		public $lastInsertID;
		public $resultCount;
		public $resultCountNoLimit;

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @access public
		 * @since 0.7.9
		 */
		public function __construct() { /* Do nothing here */ }

		/**
		 * @access private
		 * @since  unknown
		 * @static
		 *
		 * @return connectionsLoad
		 */
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

				/**
				 * NOTE: Any calls to load_plugin_textdomain should be in a function attached to the `plugins_loaded` action hook.
				 * @link http://ottopress.com/2013/language-packs-101-prepwork/
				 *
				 * NOTE: Any portion of the plugin w/ translatable strings should be bound to the plugins_loaded action hook or later.
				 *
				 * NOTE: Priority set at -1 to allow extensions to use the `connections` text domain. Since extensions are
				 *       generally loaded on the `plugins_loaded` action hook, any strings with the `connections` text
				 *       domain will be merged into it. The purpose is to allow the extensions to use strings known to
				 *       in the core plugin to reuse those strings and benefit if they are already translated.
				 */
				add_action( 'plugins_loaded', array( __CLASS__ , 'loadTextdomain' ), -1 );

				// Activation/Deactivation hooks
				register_activation_hook( dirname( __FILE__ ) . '/connections.php', array( __CLASS__, 'activate' ) );
				register_deactivation_hook( dirname( __FILE__ ) . '/connections.php', array( __CLASS__, 'deactivate' ) );

				// @TODO: Create uninstall method to remove options and tables.
				// register_uninstall_hook( dirname(__FILE__) . '/connections.php', array('connectionsLoad', 'uninstall') );

				// Init the options if there is a version change just in case there were any changes.
				if ( version_compare( self::$instance->options->getVersion(), CN_CURRENT_VERSION ) < 0 ) self::$instance->initOptions();
				//self::$instance->options->setDBVersion('0.1.9'); self::$instance->options->saveOptions();

				do_action( 'cn_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Define the core constants.
		 *
		 * @access private
		 * @since  unknown
		 * @return void
		 */
		private static function defineConstants() {
			global $wpdb, $blog_id;

			if ( ! defined( 'CN_LOG' ) ) {
				/** @var string CN_LOG Whether or not to log actions and results for debugging. */
				define( 'CN_LOG', FALSE );
			}

			/** @var string CN_CURRENT_VERSION The current version. */
			define( 'CN_CURRENT_VERSION', '8.5.2' );

			/** @var string CN_DB_VERSION The current DB version. */
			define( 'CN_DB_VERSION', '0.2' );

			/** @var string CN_UPDATE_URL The plugin update URL used for EDD SL Updater */
			define( 'CN_UPDATE_URL', 'http://connections-pro.com/edd-sl-api' );

			/** @var string CN_BASE_NAME */
			define( 'CN_BASE_NAME', plugin_basename( __FILE__ ) );

			/** @var string CN_DIR_NAME */
			define( 'CN_DIR_NAME', dirname( CN_BASE_NAME ) );

			/** @var string CN_PATH */
			define( 'CN_PATH', plugin_dir_path( __FILE__ ) );

			/** @var string CN_URL */
			define( 'CN_URL', plugin_dir_url( __FILE__ ) );

			/*
			 * Core constants that can be overridden by setting in wp-config.php.
			 */
			if ( ! defined( 'CN_TEMPLATE_PATH' ) ) {

				/** @var string CN_TEMPLATE_PATH */
				define( 'CN_TEMPLATE_PATH', CN_PATH . 'templates' . DIRECTORY_SEPARATOR );
			}

			if ( ! defined( 'CN_TEMPLATE_URL' ) ) {

				/** @var string CN_TEMPLATE_URL */
				define( 'CN_TEMPLATE_URL', CN_URL . 'templates' . DIRECTORY_SEPARATOR );
			}

			if ( ! defined( 'CN_CACHE_PATH' ) ) {

				/** @var string CN_CACHE_PATH */
				define( 'CN_CACHE_PATH', CN_PATH . 'cache' . DIRECTORY_SEPARATOR );
			}

			if ( ! defined( 'CN_ADMIN_MENU_POSITION' ) ) {

				/** @var int CN_ADMIN_MENU_POSITION */
				define( 'CN_ADMIN_MENU_POSITION', NULL );
			}

			/*
			 * To run Connections in single site mode on multi-site.
			 * Add to wp-config.php: define('CN_MULTISITE_ENABLED', FALSE);
			 *
			 * @credit lancelot-du-lac
			 * @url http://wordpress.org/support/topic/plugin-connections-support-multisite-in-single-mode
			 */
			if ( ! defined( 'CN_MULTISITE_ENABLED' ) ) {

				if ( is_multisite() ) {

					/** @var bool CN_MULTISITE_ENABLED */
					define( 'CN_MULTISITE_ENABLED', TRUE );

				} else {

					/** @var bool CN_MULTISITE_ENABLED */
					define( 'CN_MULTISITE_ENABLED', FALSE );
				}
			}

			// Set the root image permalink endpoint name.
			if ( ! defined( 'CN_IMAGE_ENDPOINT' ) ) {

				/** @var string CN_IMAGE_ENDPOINT */
				define( 'CN_IMAGE_ENDPOINT', 'cn-image' );
			}

			// Set images subdirectory folder name.
			if ( ! defined( 'CN_IMAGE_DIR_NAME' ) ){

				/** @var string CN_IMAGE_DIR_NAME */
				define( 'CN_IMAGE_DIR_NAME', 'connections-images' );
			}

			/*
			 * Core constants that can be overridden in wp-config.php
			 * which enable support for multi-site file locations.
			 */
			if ( is_multisite() && CN_MULTISITE_ENABLED ) {

				// Get the core WP uploads info.
				$uploadInfo = wp_upload_dir();

				if ( ! defined( 'CN_IMAGE_PATH' ) ) {

					/** @var string CN_IMAGE_PATH */
					define( 'CN_IMAGE_PATH', trailingslashit( $uploadInfo['basedir'] ) . CN_IMAGE_DIR_NAME . DIRECTORY_SEPARATOR );
					// define( 'CN_IMAGE_PATH', WP_CONTENT_DIR . '/sites/' . $blog_id . '/connection_images/' );
				}

				if ( ! defined( 'CN_IMAGE_BASE_URL' ) ) {

					/** @var string CN_IMAGE_BASE_URL */
					define( 'CN_IMAGE_BASE_URL', trailingslashit( $uploadInfo['baseurl'] ) . CN_IMAGE_DIR_NAME . '/' );
					// define( 'CN_IMAGE_BASE_URL', network_home_url( '/wp-content/sites/' . $blog_id . '/connection_images/' ) );
				}

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_PATH' ) ) {

					/** @var string CN_CUSTOM_TEMPLATE_PATH */
					define( 'CN_CUSTOM_TEMPLATE_PATH', WP_CONTENT_DIR . '/blogs.dir/' . $blog_id . '/connections_templates/' );
				}

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_URL' ) ) {

					/** @var string CN_CUSTOM_TEMPLATE_URL */
					define( 'CN_CUSTOM_TEMPLATE_URL', network_home_url( '/wp-content/blogs.dir/' . $blog_id . '/connections_templates/' ) );
				}

				// Define the relative URL/s.
				/** @var string CN_RELATIVE_URL */
				define( 'CN_RELATIVE_URL', str_replace( network_home_url(), '', CN_URL ) );

				/** @var string CN_TEMPLATE_RELATIVE_URL */
				define( 'CN_TEMPLATE_RELATIVE_URL', str_replace( network_home_url(), '', CN_URL . 'templates/' ) );

				/** @var string CN_IMAGE_RELATIVE_URL */
				define( 'CN_IMAGE_RELATIVE_URL', str_replace( network_home_url(), '', CN_IMAGE_BASE_URL ) );

				/** @var string CN_CUSTOM_TEMPLATE_RELATIVE_URL */
				define( 'CN_CUSTOM_TEMPLATE_RELATIVE_URL', str_replace( network_home_url(), '', CN_CUSTOM_TEMPLATE_URL ) );

			} else {

				/**
				 * Pulled this block of code from wp_upload_dir(). Using this rather than simply using wp_upload_dir()
				 * because @see wp_upload_dir() will always return the upload dir/url (/sites/{id}/) for the current network site.
				 *
				 * We do not want this behavior if forcing Connections into single site mode on a multisite
				 * install of WP. Additionally we do not want the year/month sub dir appended.
				 *
				 * A filter could be used, hooked into `upload_dir` but that would be a little heavy as every time the custom
				 * dir/url would be needed the filter would have to be added and then removed not to mention other plugins could
				 * interfere by hooking into `upload_dir`.
				 *
				 * --> START <--
				 */
				$siteurl     = site_url();
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

						$url = content_url( '/uploads' );

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

				if ( ! defined( 'CN_IMAGE_PATH' ) ){

					/** @var string CN_IMAGE_PATH */
					define( 'CN_IMAGE_PATH', $dir . DIRECTORY_SEPARATOR . CN_IMAGE_DIR_NAME . DIRECTORY_SEPARATOR );
				}
				if ( ! defined( 'CN_IMAGE_BASE_URL' ) ) {

					/** @var string CN_IMAGE_BASE_URL */
					define( 'CN_IMAGE_BASE_URL', $url . '/' . CN_IMAGE_DIR_NAME . '/' );
				}

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_PATH' ) ) {

					/** @var string CN_CUSTOM_TEMPLATE_PATH */
					define( 'CN_CUSTOM_TEMPLATE_PATH', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'connections_templates' . DIRECTORY_SEPARATOR );
				}

				if ( ! defined( 'CN_CUSTOM_TEMPLATE_URL' ) ) {

					/** @var string CN_CUSTOM_TEMPLATE_URL */
					define( 'CN_CUSTOM_TEMPLATE_URL', content_url() . '/connections_templates/' );
				}

				// Define the relative URL/s.
				/** @var string CN_RELATIVE_URL */
				define( 'CN_RELATIVE_URL', str_replace( home_url(), '', CN_URL ) );

				/** @var string CN_TEMPLATE_RELATIVE_URL */
				define( 'CN_TEMPLATE_RELATIVE_URL', str_replace( home_url(), '', CN_URL . 'templates/' ) );

				/** @var string CN_IMAGE_RELATIVE_URL */
				define( 'CN_IMAGE_RELATIVE_URL', str_replace( home_url(), '', CN_IMAGE_BASE_URL ) );

				/** @var string CN_CUSTOM_TEMPLATE_RELATIVE_URL */
				define( 'CN_CUSTOM_TEMPLATE_RELATIVE_URL', str_replace( home_url(), '', CN_CUSTOM_TEMPLATE_URL ) );
			}

			/*
			 * Set the table prefix accordingly depending if Connections is installed on a multisite WP installation.
			 */
			$prefix = ( is_multisite() && CN_MULTISITE_ENABLED ) ? $wpdb->prefix : $wpdb->base_prefix;

			/*
			 * Define the constants that can be used to reference the custom tables
			 */
			/** @var string CN_ENTRY_TABLE */
			define( 'CN_ENTRY_TABLE', $prefix . 'connections' );

			/** @var string CN_ENTRY_ADDRESS_TABLE */
			define( 'CN_ENTRY_ADDRESS_TABLE', $prefix . 'connections_address' );

			/** @var string CN_ENTRY_PHONE_TABLE */
			define( 'CN_ENTRY_PHONE_TABLE', $prefix . 'connections_phone' );

			/** @var string CN_ENTRY_EMAIL_TABLE */
			define( 'CN_ENTRY_EMAIL_TABLE', $prefix . 'connections_email' );

			/** @var string CN_ENTRY_MESSENGER_TABLE */
			define( 'CN_ENTRY_MESSENGER_TABLE', $prefix . 'connections_messenger' );

			/** @var string CN_ENTRY_SOCIAL_TABLE */
			define( 'CN_ENTRY_SOCIAL_TABLE', $prefix . 'connections_social' );

			/** @var string CN_ENTRY_LINK_TABLE */
			define( 'CN_ENTRY_LINK_TABLE', $prefix . 'connections_link' );

			/** @var string CN_ENTRY_DATE_TABLE */
			define( 'CN_ENTRY_DATE_TABLE', $prefix . 'connections_date' );

			/** @var string CN_ENTRY_TABLE_META */
			define( 'CN_ENTRY_TABLE_META', $prefix . 'connections_meta' );

			/** @var string CN_TERMS_TABLE */
			define( 'CN_TERMS_TABLE', $prefix . 'connections_terms' );

			/** @var string CN_TERM_TAXONOMY_TABLE */
			define( 'CN_TERM_TAXONOMY_TABLE', $prefix . 'connections_term_taxonomy' );

			/** @var string CN_TERM_RELATIONSHIP_TABLE */
			define( 'CN_TERM_RELATIONSHIP_TABLE', $prefix . 'connections_term_relationships' );

			/** @var string CN_TERM_META_TABLE */
			define( 'CN_TERM_META_TABLE', $prefix . 'connections_term_meta' );
		}

		private static function includes() {

			/**
			 * @TODO: Load dependencies as needed. For example load only classes needed in the admin and frontend
			 */

			// Add the default filters.
			require_once CN_PATH . 'includes/inc.default-filters.php';

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

			// Sanitation.
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

			// The class that inits the registered query vars, rewrite urls and canonical redirects.
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

			// Log APIs.
			require_once CN_PATH . 'includes/log/class.log.php';
			require_once CN_PATH . 'includes/log/class.log-stateless.php';

			// Log email sent through the Email API.
			require_once CN_PATH . 'includes/log/class.log-email.php';

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

				// Class used for managing role capabilities.
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

				// The Term Meta UI class.
				require_once CN_PATH . 'includes/admin/class.term-meta-ui.php';

				// Load the templates used on the Dashboard admin page.
				include_once CN_PATH . 'templates/dashboard-recent-added/dashboard-recent-added.php';
				include_once CN_PATH . 'templates/dashboard-recent-modified/dashboard-recent-modified.php';
				include_once CN_PATH . 'templates/dashboard-upcoming/dashboard-upcoming.php';

			} else {

				// Class for SEO
				require_once CN_PATH . 'includes/class.seo.php';

			}

			// Include the core templates that use the Template APIs introduced in 0.7.6
			// Must include BEFORE class.template-api.php.
			include_once CN_PATH . 'templates/names/names.php';
			include_once CN_PATH . 'templates/card/card-default.php';
			include_once CN_PATH . 'templates/profile/profile.php';
			include_once CN_PATH . 'templates/anniversary-dark/anniversary-dark.php';
			include_once CN_PATH . 'templates/anniversary-light/anniversary-light.php';
			include_once CN_PATH . 'templates/birthday-dark/birthday-dark.php';
			include_once CN_PATH . 'templates/birthday-light/birthday-light.php';

			// Template APIs.
			// Must require AFTER the core templates.
			require_once CN_PATH . 'includes/template/class.template-api.php';
			require_once CN_PATH . 'includes/template/class.template-parts.php';
			require_once CN_PATH . 'includes/template/class.template-shortcode.php';
			require_once CN_PATH . 'includes/template/class.template-compatibility.php';
			require_once CN_PATH . 'includes/template/class.template.php';

			require_once CN_PATH . 'includes/inc.plugin-compatibility.php';
			require_once CN_PATH . 'includes/inc.theme-compatibility.php';

			// System Info
			require_once CN_PATH . 'includes/system-info/class.system-info.php';

			// Include the Template Customizer files.
			add_action( 'plugins_loaded', array( __CLASS__, 'includeCustomizer' ) );
		}

		/**
		 * This callback run on the plugins_loaded hook to include the Customizer classes.
		 *
		 * Matches core WordPress @see _wp_customize_include().
		 *
		 * @access private
		 * @since  8.4
		 */
		public static function includeCustomizer() {

			if ( ! ( ( isset( $_REQUEST['wp_customize'] ) && 'on' == $_REQUEST['wp_customize'] ) ||
			         ( is_admin() && 'customize.php' == basename( $_SERVER['PHP_SELF'] ) ) ) ) {
				return;
			}

			require_once CN_PATH . 'includes/template/class.template-customizer.php';

			/**
			 * Convenience actions that templates can hook into to load their Customizer config files.
			 *
			 * @since 8.4
			 */
			do_action( 'cn_template_customizer_include' );
		}

		/**
		 * Load the plugin translation.
		 *
		 * Credit: Adapted from Ninja Forms / Easy Digital Downloads.
		 *
		 * @access private
		 * @since  0.7.9
		 *
		 * @uses apply_filters()
		 * @uses get_locale()
		 * @uses load_textdomain()
		 * @uses load_plugin_textdomain()
		 *
		 * @return void
		 */
		public static function loadTextdomain() {

			// Plugin textdomain. This should match the one set in the plugin header.
			$domain = 'connections';

			// Set filter for plugin's languages directory
			$languagesDirectory = apply_filters( "cn_{$domain}_languages_directory", CN_DIR_NAME . '/languages/' );

			// Traditional WordPress plugin locale filter
			$locale   = apply_filters( 'plugin_locale', get_locale(), $domain );
			$fileName = sprintf( '%1$s-%2$s.mo', $domain, $locale );

			// Setup paths to current locale file
			$local  = $languagesDirectory . $fileName;
			$global = WP_LANG_DIR . "/{$domain}/" . $fileName;

			if ( file_exists( $global ) ) {

				// Look in global `../wp-content/languages/{$domain}/` folder.
				load_textdomain( $domain, $global );

			} elseif ( file_exists( $local ) ) {

				// Look in local `../wp-content/plugins/{plugin-directory}/languages/` folder.
				load_textdomain( $domain, $local );

			} else {

				// Load the default language files
				load_plugin_textdomain( $domain, FALSE, $languagesDirectory );
			}
		}

		/**
		 * During activation this will initiate the options.
		 */
		private function initOptions() {
			$version = $this->options->getVersion();

			switch ( TRUE ) {

				/** @noinspection PhpMissingBreakStatementInspection */
				case ( version_compare( $version, '0.7.3', '<' ) ) :
					/*
					 * Retrieve the settings stored prior to 0.7.3 and migrate them
					 * so they will be accessible in the structure supported by the
					 * Connections WordPress Settings API Wrapper Class.
					 */
					if ( FALSE !== get_option( 'connections_options' ) ) {
						$options = get_option( 'connections_options' );

						if ( FALSE === get_option( 'connections_login' ) ) {
							update_option( 'connections_login' , array(
									'required' => $options['settings']['allow_public'],
									'message' => 'Please login to view the directory.'
								)
							);
						}

						if ( FALSE === get_option( 'connections_visibility' ) ) {
							update_option( 'connections_visibility' , array(
									'allow_public_override' => $options['settings']['allow_public_override'],
									'allow_private_override' => $options['settings']['allow_private_override']
								)
							);
						}

						if ( FALSE === get_option( 'connections_image_thumbnail' ) ) {
							update_option( 'connections_image_thumbnail' , array(
									'quality' => $options['settings']['image']['thumbnail']['quality'],
									'width' => $options['settings']['image']['thumbnail']['x'],
									'height' => $options['settings']['image']['thumbnail']['y'],
									'ratio' => $options['settings']['image']['thumbnail']['crop']
								)
							);
						}
						if ( FALSE === get_option( 'connections_image_medium' ) ) {
							update_option( 'connections_image_medium' , array(
									'quality' => $options['settings']['image']['entry']['quality'],
									'width' => $options['settings']['image']['entry']['x'],
									'height' => $options['settings']['image']['entry']['y'],
									'ratio' => $options['settings']['image']['entry']['crop']
								)
							);
						}

						if ( FALSE === get_option( 'connections_image_large' ) ) {
							update_option( 'connections_image_large' , array(
									'quality' => $options['settings']['image']['profile']['quality'],
									'width' => $options['settings']['image']['profile']['x'],
									'height' => $options['settings']['image']['profile']['y'],
									'ratio' => $options['settings']['image']['profile']['crop']
								)
							);
						}

						if ( FALSE === get_option( 'connections_image_logo' ) ) {
							update_option( 'connections_image_logo' , array(
									'quality' => $options['settings']['image']['logo']['quality'],
									'width' => $options['settings']['image']['logo']['x'],
									'height' => $options['settings']['image']['logo']['y'],
									'ratio' => $options['settings']['image']['logo']['crop']
								)
							);
						}

						if ( FALSE === get_option( 'connections_compatibility' ) ) {
							update_option( 'connections_compatibility' , array(
									'google_maps_api' => $options['settings']['advanced']['load_google_maps_api'],
									'javascript_footer' => $options['settings']['advanced']['load_javascript_footer'] )
							);
						}

						if ( FALSE === get_option( 'connections_debug' ) ) {
							update_option( 'connections_debug' , array( 'debug_messages' => $options['debug'] ) );
						}

						unset( $options );
					}

				/** @noinspection PhpMissingBreakStatementInspection */
				case ( version_compare( $version, '0.7.4', '<' ) ) :
					/*
					 * The option to disable keyword search was added in version 0.7.4. Set this option to be enabled by default.
					 */
					$options = get_option( 'connections_search' );
					$options['keyword_enabled'] = 1;

					update_option( 'connections_search', $options );
					unset( $options );

				/** @noinspection PhpMissingBreakStatementInspection */
				case ( version_compare( $version, '0.8', '<' ) ) :
					/*
					 * The option to disable keyword search was added in version 0.7.4. Set this option to be enabled by default.
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

			if ( NULL === $this->options->getDefaultTemplatesSet() ) $this->options->setDefaultTemplates();

			// Class used for managing role capabilities.
			if ( ! class_exists( 'cnRole' ) ) require_once CN_PATH . 'includes/admin/class.capabilities.php';

			if ( TRUE != $this->options->getCapabilitiesSet() ) {

				cnRole::reset();
				$this->options->defaultCapabilitiesSet( TRUE );
			}

			/**
			 * @todo NOTE: Update BUG!!!
			 *
			 *       If a user updates an old version of Connections while deactivated, when activated the db version will
			 *       be incremented and since the version is incremented to the current version NONE of the db update
			 *       routines will be run.
			 */

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
		 *
		 * @param $type    string
		 * @param $message string
		 *
		 * @return void
		 */
		public function setRuntimeMessage( $type, $message ) {
			cnMessage::runtime( $type, $message );
		}

		/**
		 * Store an error code.
		 * This is a deprecated helper function left in place until all instances of it are removed from the code base.
		 *
		 * @access public
		 * @since unknown
		 * @deprecated 0.7.5
		 *
		 * @param  $code string
		 *
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
		 *
		 * @param  $code string
		 *
		 * @return void
		 */
		public function setSuccessMessage( $code ) {
			cnMessage::set( 'success', $code );
		}

		/**
		 * Called when activating Connections via the activation hook.
		 */
		public static function activate() {

			/** @var $connections connectionsLoad */
			global $connections;

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
			// cnFileSystem::noIndexes( CN_IMAGE_PATH ); // Causes some servers to respond w/ 403 when serving images.
			// cnFileSystem::noIndexes( CN_CUSTOM_TEMPLATE_PATH );

			// Create a .htaccess file in the TimThumb folder to allow it to be called directly.
			//cnFileSystem::permitTimThumb( CN_PATH . 'vendor/timthumb' );

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
		}
	}

	/**
	 * The main function responsible for returning the Connections instance
	 * to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * NOTE: Declaring an instance in the global @var $connections connectionsLoad to provide backward
	 * compatibility with many internal methods, template and extensions that expect it.
	 *
	 * Example: <?php $instance = Connections_Directory(); ?>
	 *
	 * @access public
	 * @since  0.7.9
	 * @global $connections
	 * @return connectionsLoad
	 */
	function Connections_Directory() {
		global $connections;

		$connections = connectionsLoad::instance();
		return $connections;
	}

	// Start Connections.
	Connections_Directory();
}
