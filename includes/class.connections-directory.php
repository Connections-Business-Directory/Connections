<?php

use Connections_Directory\Blocks;
use Connections_Directory\Content_Blocks;
use Connections_Directory\Hook\Action;
use Connections_Directory\Integration;
use Connections_Directory\Request;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Connections
 */
final class Connections_Directory {

	/**
	 * The plugin version.
	 *
	 * @since 8.16
	 */
	const VERSION = '10.4.30';

	/**
	 * Stores the instance of this class.
	 *
	 * @since 0.7.9
	 *
	 * @var connectionsLoad
	 */
	private static $instance;

	/**
	 * The absolute path this file.
	 *
	 * @since  8.16
	 *
	 * @var string
	 */
	private static $file = '';

	/**
	 * The URL to the plugin's folder.
	 *
	 * @since  8.16
	 *
	 * @var string
	 */
	private static $pluginURL = '';

	/**
	 * The absolute path to this plugin's folder.
	 *
	 * @since 8.16
	 *
	 * @var string
	 */
	private static $path = '';

	/**
	 * The basename of the plugin.
	 *
	 * @since 8.16
	 *
	 * @var string
	 */
	private static $basename = '';

	/**
	 * @since 8.5.26
	 *
	 * @var cnAPI
	 */
	private $api;

	/**
	 * @since unknown
	 *
	 * @var cnUser
	 */
	public $currentUser, $user;

	/**
	 * @since unknown
	 *
	 * @var cnOptions
	 */
	public $options;

	/**
	 * @since unknown
	 *
	 * @var cnRetrieve
	 */
	public $retrieve;

	/**
	 * @since unknown
	 *
	 * @var cnTerms
	 */
	public $term;

	/**
	 * Stores the page hook values returned from the add_menu_page & add_submenu_page functions
	 *
	 * @since unknown
	 *
	 * @var object
	 */
	public $pageHook;

	/**
	 * The Connections Settings API Wrapper class.
	 *
	 * @since unknown
	 *
	 * @var cnSettingsAPI
	 */
	public $settings;

	/**
	 * Do the database upgrade.
	 *
	 * @since unknown
	 *
	 * @var bool
	 */
	public $dbUpgrade = false;

	/**
	 * Stores the template parts object and any templates activated by the cnTemplateFactory object.
	 *
	 * NOTE: Technically not necessary to load the template parts into this object, but it's required
	 * for backward compatibility for templates expecting to find those methods as part of this object.
	 *
	 * @since 0.7.6
	 *
	 * @var cnTemplatePart
	 */
	public $template;

	/**
	 * @since unknown
	 *
	 * @var cnURL
	 */
	public $url;

	/**
	 * Used in the cnEntry and cnRetrieve classes.
	 *
	 * @todo Code should be refactored to remove usage.
	 * @var string
	 */
	public $lastQuery;

	/**
	 * Used in the cnEntry and cnRetrieve classes.
	 *
	 * @todo Code should be refactored to remove usage.
	 * @var string
	 */
	public $lastQueryError;

	/**
	 * Used in the cnEntry and cnRetrieve classes.
	 *
	 * @todo Code should be refactored to remove usage.
	 * @var int
	 */
	public $lastInsertID;

	/**
	 * Used in the cnRetrieve class.
	 *
	 * @todo Code should be refactored to remove usage.
	 * @var int
	 */
	public $resultCount;

	/**
	 * Used in the cnRetrieve class.
	 *
	 * @todo Code should be refactored to remove usage.
	 * @var int
	 */
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
	 * @param string $file
	 *
	 * @return connectionsLoad
	 */
	public static function instance( $file = '' ) {

		if ( ! empty( $file ) && ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self();

			self::$file      = $file;
			self::$pluginURL = plugin_dir_url( $file );
			self::$path      = plugin_dir_path( $file );
			self::$basename  = plugin_basename( $file );

			require_once self::$path . 'includes/inc.constants.php';

			require_once CN_PATH . 'includes/class.dependency.php';
			cnDependency::register();

			/*
			 * cnMetaboxAPI has to load before cnAdminFunction otherwise
			 * the action to save the meta is not added in time to run.
			 */
			cnMetaboxAPI::init();

			// Init the Image API.
			cnImage::init();

			// Init email logging of email sent through cnEmail.
			cnLog_Email::init();

			// Init the email template API.
			cnEmail_Template::init();

			// Register the default email templates.
			cnEmail_DefaultTemplates::init();

			// Register the core action/filter hooks.
			self::hooks();

			self::$instance->options     = new cnOptions();
			self::$instance->settings    = cnSettingsAPI::getInstance();
			self::$instance->pageHook    = new stdClass();
			self::$instance->currentUser = new cnUser();
			self::$instance->user        = &self::$instance->currentUser;
			self::$instance->retrieve    = new cnRetrieve();
			self::$instance->term        = new cnTerms();
			self::$instance->template    = new cnTemplatePart();
			self::$instance->url         = new cnURL();
			self::$instance->api         = new cnAPI();

			// Register editor blocks.
			Blocks::register();

			// Init the Content Blocks API.
			Content_Blocks::instance();

			// Activation/Deactivation hooks.
			register_activation_hook( dirname( $file ) . '/connections.php', array( __CLASS__, 'activate' ) );
			register_deactivation_hook( dirname( $file ) . '/connections.php', array( __CLASS__, 'deactivate' ) );

			// @TODO: Create uninstall method to remove options and tables.
			// register_uninstall_hook( dirname($file) . '/connections.php', array('connectionsLoad', 'uninstall') );

			// Init the options if there is a version change just in case there were any changes.
			if ( version_compare( self::$instance->options->getVersion(), CN_CURRENT_VERSION ) < 0 ) {
				self::$instance->initOptions();
			}
			// self::$instance->options->setDBVersion('0.1.9'); self::$instance->options->saveOptions();
		}

		return self::$instance;
	}

	/**
	 * Gets the basename of a plugin.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @return string
	 */
	public function pluginBasename() {

		return self::$basename;
	}

	/**
	 * Get the absolute directory path (with trailing slash) for the plugin.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @return string
	 */
	public function pluginPath() {

		return self::$path;
	}

	/**
	 * Get the URL directory path (with trailing slash) for the plugin.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @return string
	 */
	public function pluginURL() {

		return self::$pluginURL;
	}

	/**
	 * Register the plugin's hooks.
	 *
	 * @access private
	 * @since  unknown
	 */
	private static function hooks() {

		// Include the Template Customizer files.
		add_action( 'plugins_loaded', array( 'cnDependency', 'customizer' ) );

		// Add the core Content Blocks.
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Categories::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Custom_Fields::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Google_Static_Map::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Last_Viewed::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Management::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Map_Block::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Meta::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Nearby::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Recently_Viewed::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Category::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Postal_Code::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Region::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Locality::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\County::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\District::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Department::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Organization::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Title::class, 'add' ) );
		add_action( 'plugins_loaded', array( Content_Blocks\Entry\Related\Last_Name::class, 'add' ) );

		/*
		 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
		 */
		add_filter( 'cn_register_settings_tabs', array( 'cnRegisterSettings', 'registerSettingsTabs' ), 10, 1 );
		add_filter( 'cn_register_settings_sections', array( 'cnRegisterSettings', 'registerSettingsSections' ), 10, 1 );
		add_filter( 'cn_register_settings_fields', array( 'cnRegisterSettings', 'registerSettingsFields' ), 10, 1 );

		// cnAdminMenu must load before the cnMetaboxAPI so the admin page hooks are defined.
		add_action( 'admin_menu', array( 'cnAdminMenu', 'init' ) );

		// Register the core entry metabox and fields.
		add_action( 'cn_metabox', array( 'cnEntryMetabox', 'init' ), 1 );

		// Register the scripts hooks.
		cnScript::hooks();

		// Add actions which purges caches after adding/editing and entry.
		add_action( 'cn_post_process_add-entry', array( 'cnEntry_Action', 'clearCache' ) );
		add_action( 'cn_post_process_update-entry', array( 'cnEntry_Action', 'clearCache' ) );
		add_action( 'cn_process_status', array( 'cnEntry_Action', 'clearCache' ) );
		add_action( 'cn_process_visibility', array( 'cnEntry_Action', 'clearCache' ) );
		add_action( 'cn_process_bulk_delete', array( 'cnEntry_Action', 'clearCache' ) );
		add_action( 'update_option_permalink_structure', array( 'cnEntry_Action', 'clearCache' ) );

		// Add actions to update the term taxonomy counts when entry status or visibility has been updated via the bulk actions.
		add_action( 'cn_process_status', array( 'cnEntry_Action', 'updateTermCount' ) );
		add_action( 'cn_process_visibility', array( 'cnEntry_Action', 'updateTermCount' ) );

		// Add the "Edit Entry" menu items to the admin bar.
		add_action( 'admin_bar_menu', array( 'cnEntry_Action', 'adminBarMenuItems' ), 90 );

		// Register the shortcode hooks.
		cnShortcode::hooks();

		// Register all valid query variables.
		cnRewrite::hooks();
		add_action( 'init', array( Request\Entry_Initial_Character::class, 'registerQueryVar' ) );
		add_action( 'init', array( Request\Entry_Search_Term::class, 'registerQueryVar' ) );

		/*
		 * Action added in the init hook to allow other plugins time to register there log types.
		 * The priority is set at -1 because the post types and taxonomy are registered in the
		 * init hook at priority 1.
		 */
		add_action( 'init', array( 'cnLog', 'instance' ), -1 );

		// Register the Dashboard metaboxes.
		add_action( 'cn_metabox', array( 'cnDashboardMetabox', 'init' ), 1 );

		/*
		 * Register the admin actions and filters.
		 */
		add_action( 'admin_init', array( 'cnAdminFunction', 'init' ) );
		add_action( 'admin_init', array( Action::class, 'run' ) );
		add_action( 'admin_init', array( Action\Admin\Footer::class, 'register' ) );

		/*
		 * Register action and filter callbacks at priority 9,
		 * so they are registered before {@see Action::run()} in executed.
		 */
		add_action( 'admin_init', array( Action\Admin\Role_Capability::class, 'register' ), 9 );
		add_action( 'admin_init', array( Action\Admin\Template::class, 'register' ), 9 );

		add_action( 'load-plugins.php', array( Action\Admin\Plugin_Row::class, 'register' ) );

		/*
		 * Add the filter to update the user settings when the "Apply" button is clicked.
		 * NOTE: This relies on the Screen Options class by Janis Elsts.
		 * NOTE: Set priority 99 so the filter will hopefully run last to help prevent other plugins
		 *       which do not hook into `set-screen-option` properly from breaking Connections.
		 */
		add_filter( 'set_screen_option_connections', array( 'cnAdminFunction', 'setManageScreenOptions' ), 99, 3 );

		// Init the class.
		add_action( 'init', array( 'cnSEO', 'hooks' ) );

		// Init the Template Factory API
		// NOTE: The priority can not be >10 otherwise it will break older templates
		// which init'd on `plugins_loaded` at priority 11.
		add_action( 'plugins_loaded', array( 'cnTemplateFactory', 'init' ) );

		// Register the Template Parts API hooks.
		cnTemplatePart::hooks();
		cnTemplate_Compatibility::hooks();

		// Init sitemaps. Priority must be `11` because the shortcode are registered on `init` at priority `10`.
		add_action( 'init', 'Connections_Directory\Sitemaps\init', 11 );

		// Register email log type.
		add_filter( 'cn_email_log_types', array( 'cnSystem_Info', 'registerEmailLogType' ) );

		// Register the log view.
		add_filter( 'cn_log_views', array( 'cnSystem_Info', 'registerLogView' ) );

		// Register the callback to display the email log detail view.
		add_action( 'template_redirect', array( 'cnSystem_Info', 'view' ) );

		// Register the callback to support downloading of vCards.
		add_action( 'template_redirect', array( 'cnEntry_vCard', 'download' ) );

		// Geocode the address using Google Geocoding API.
		add_filter( 'cn_set_address', array( 'cnEntry_Action', 'geoCode' ) );

		// Parse the request query variables.
		add_action( 'parse_request', array( Request::class, 'parse' ) );

		// Init the taxonomies. The `setup_theme` action is the action run closest after initializing of the $wp_rewrite global variable.
		add_action( 'setup_theme', 'Connections_Directory\Taxonomy\init' );

		// Integrations
		// Priority 15 because Yoast SEO inits on priority 14 on the plugins_loaded action.
		add_action( 'plugins_loaded', array( Integration\SEO\Yoast_SEO::class, 'init' ), 15 );
		add_action( 'plugins_loaded', array( Integration\SEO\Rank_Math::class, 'init' ), 15 );
		// Priority 11 because Gravity Forms addon init on priority 10 on the plugins_loaded action.
		add_action( 'plugins_loaded', array( Integration\Gravity_Forms::class, 'init' ), 11 );
	}

	/**
	 * During activation this will initiate the options.
	 */
	private function initOptions() {
		$version = $this->options->getVersion();

		switch ( true ) {

			case ( version_compare( $version, '0.7.3', '<' ) ):
				/*
				 * Retrieve the settings stored prior to 0.7.3 and migrate them
				 * so they will be accessible in the structure supported by the
				 * Connections WordPress Settings API Wrapper Class.
				 */
				if ( false !== get_option( 'connections_options' ) ) {
					$options = get_option( 'connections_options' );

					if ( false === get_option( 'connections_login' ) ) {
						update_option(
							'connections_login',
							array(
								'required' => $options['settings']['allow_public'],
								'message'  => 'Please login to view the directory.',
							)
						);
					}

					if ( false === get_option( 'connections_visibility' ) ) {
						update_option(
							'connections_visibility',
							array(
								'allow_public_override'  => $options['settings']['allow_public_override'],
								'allow_private_override' => $options['settings']['allow_private_override'],
							)
						);
					}

					if ( false === get_option( 'connections_image_thumbnail' ) ) {
						update_option(
							'connections_image_thumbnail',
							array(
								'quality' => $options['settings']['image']['thumbnail']['quality'],
								'width'   => $options['settings']['image']['thumbnail']['x'],
								'height'  => $options['settings']['image']['thumbnail']['y'],
								'ratio'   => $options['settings']['image']['thumbnail']['crop'],
							)
						);
					}

					if ( false === get_option( 'connections_image_medium' ) ) {
						update_option(
							'connections_image_medium',
							array(
								'quality' => $options['settings']['image']['entry']['quality'],
								'width'   => $options['settings']['image']['entry']['x'],
								'height'  => $options['settings']['image']['entry']['y'],
								'ratio'   => $options['settings']['image']['entry']['crop'],
							)
						);
					}

					if ( false === get_option( 'connections_image_large' ) ) {
						update_option(
							'connections_image_large',
							array(
								'quality' => $options['settings']['image']['profile']['quality'],
								'width'   => $options['settings']['image']['profile']['x'],
								'height'  => $options['settings']['image']['profile']['y'],
								'ratio'   => $options['settings']['image']['profile']['crop'],
							)
						);
					}

					if ( false === get_option( 'connections_image_logo' ) ) {
						update_option(
							'connections_image_logo',
							array(
								'quality' => $options['settings']['image']['logo']['quality'],
								'width'   => $options['settings']['image']['logo']['x'],
								'height'  => $options['settings']['image']['logo']['y'],
								'ratio'   => $options['settings']['image']['logo']['crop'],
							)
						);
					}

					if ( false === get_option( 'connections_compatibility' ) ) {
						update_option(
							'connections_compatibility',
							array(
								'google_maps_api'   => $options['settings']['advanced']['load_google_maps_api'],
								'javascript_footer' => $options['settings']['advanced']['load_javascript_footer'],
							)
						);
					}

					if ( false === get_option( 'connections_debug' ) ) {
						update_option( 'connections_debug', array( 'debug_messages' => $options['debug'] ) );
					}

					unset( $options );
				}

			case ( version_compare( $version, '0.7.4', '<' ) ):
				/*
				 * The option to disable keyword search was added in version 0.7.4. Set this option to be enabled by default.
				 */
				$options                    = get_option( 'connections_search' );
				$options['keyword_enabled'] = 1;

				update_option( 'connections_search', $options );
				unset( $options );

			case ( version_compare( $version, '0.8', '<' ) ):
				/*
				 * The option to disable keyword search was added in version 0.7.4. Set this option to be enabled by default.
				 */
				$options        = get_option( 'connections_compatibility' );
				$options['css'] = 1;

				update_option( 'connections_compatibility', $options );
				unset( $options );

				$options                   = get_option( 'connections_display_results' );
				$options['search_message'] = 1;

				update_option( 'connections_display_results', $options );
				unset( $options );

			case ( version_compare( $version, '8.5.19', '<' ) ):
				$options = get_option( 'connections_permalink' );

				$options['district_base'] = 'district';
				$options['county_base']   = 'county';

				update_option( 'connections_permalink', $options );
		}

		if ( null === $this->options->getDefaultTemplatesSet() ) {
			$this->options->setDefaultTemplates();
		}

		// Class used for managing role capabilities.
		if ( ! class_exists( 'cnRole' ) ) {
			require_once CN_PATH . 'includes/admin/class.capabilities.php';
		}

		if ( true !== $this->options->getCapabilitiesSet() ) {

			cnRole::reset();
			$this->options->defaultCapabilitiesSet( true );
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

		// Save the options.
		$this->options->saveOptions();

		/*
		 * This option is added for a check that will force a flush_rewrite() in connectionsLoad::adminInit() once.
		 * Should save the user from having to "save" the permalink settings.
		 */
		update_option( 'connections_flush_rewrite', '1' );
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
 * Back-compatible due to renaming class.
 */
class_alias( 'Connections_Directory', 'connectionsLoad' );

/**
 * The main function responsible for returning the Connections instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * NOTE: Declaring an instance in the global @var $connections Connections_Directory to provide backward
 * compatibility with many internal methods, template and extensions that expect it.
 *
 * Example: <?php $instance = Connections_Directory(); ?>
 *
 * @access public
 * @since  0.7.9
 *
 * @global $connections
 *
 * @return Connections_Directory
 */
function Connections_Directory() {

	global $connections;

	$connections = Connections_Directory::instance();

	return $connections;
}
