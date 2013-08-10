<?php

/**
 * Class for admin related functions.
 *
 * @package     Connections
 * @subpackage  Admin Functions
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnAdminFunction {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.7.9
	 * @var (object)
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.7.9
	 * @see cnAdminFunction::init()
	 * @see cnAdminFunction();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class, if it has already been initialized, return the intialized instance.
	 *
	 * @access public
	 * @since 0.7.9
	 * @see cnAdminFunction()
 	 * @uses WP_Error()
	 * @uses get_option()
	 * @uses delete_option()
	 * @uses add_action()
	 * @uses add_filter()
	 * @uses add_screen_options_panel()
	 * @return (void)
	 */
	public static function init() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			// Initiate admin messages.
			cnMessage::init();

			// Initiate admin actions.
			cnAdminActions::init();

			// If the user changed the base slugs for the permalinks, flush the rewrite rules.
			if ( get_option( 'connections_flush_rewrite' ) ) {

				flush_rewrite_rules();
				delete_option( 'connections_flush_rewrite' );
			}

			/*
			 * If the home page has not been set, nag the user to set it.
			 */
			$directoryHome = $instance->settings->get( 'connections', 'connections_home_page', 'page_id' );

			if ( ! $directoryHome ) cnMessage::create( 'notice', 'home_page_set_failed' );

			// Check if the db requires updating, display message if it does.
			if ( version_compare( $instance->options->getDBVersion(), CN_DB_VERSION, '<' ) ) {

				$instance->dbUpgrade = TRUE;

				add_action( 'current_screen', array( __CLASS__, 'displayDBUpgradeNotice' ) );
			}

			/*
			 * Add admin notices if required directories are not present or not writeable.
			 */
			if ( ! file_exists( CN_IMAGE_PATH ) ) cnMessage::create( 'notice', 'image_path_exists_failed' );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) cnMessage::create( 'notice', 'image_path_writeable_failed' );

			if ( ! file_exists( CN_CUSTOM_TEMPLATE_PATH ) ) cnMessage::create( 'notice', 'template_path_exists_failed' );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) cnMessage::create( 'notice', 'template_path_writeable_failed' );

			if ( ! file_exists( CN_CACHE_PATH ) ) cnMessage::create( 'notice', 'cache_path_exists_failed' );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) cnMessage::create( 'notice', 'cache_path_writeable_failed' );

			// Add Settings link to the plugin actions
			add_action( 'plugin_action_links_' . CN_BASE_NAME, array( __CLASS__, 'addActionLinks' ) );

			// Add FAQ, Support and Donate links
			add_filter( 'plugin_row_meta', array( __CLASS__, 'addMetaLinks' ), 10, 2 );

			// Add Changelog table row in the Manage Plugins admin page.
			add_action( 'after_plugin_row_' . CN_BASE_NAME, array( __CLASS__, 'displayUpgradeNotice' ), 1, 0 );
			// @todo Maybe should use this action hook instead: in_plugin_update_message-{$file}

			/*
			 * In instances such as WP AJAX requests the add_menu() and add_sub_menu() functions are
			 * not run in the admin_menu action, so the properties would not exist and will throw
			 * PHP notices when attempting to access them. If the menus have been added then the
			 * properties will exist so it will be safe to add the actions using the properties.
			 */
			if ( get_object_vars( $instance->pageHook ) && current_user_can( 'connections_view_menu') ) {

				// Register the edit metaboxes.
				add_action( 'load-' . $instance->pageHook->add, array( __CLASS__, 'registerEditMetaboxes' ) );
				add_action( 'load-' . $instance->pageHook->manage, array( __CLASS__, 'registerEditMetaboxes' ) );

				// Register the Dashboard metaboxes.
				add_action( 'load-' . $instance->pageHook->dashboard, array( __CLASS__, 'registerDashboardMetaboxes' ) );

				/*
				 * Add the panel to the "Screen Options" box to the manage page.
				 * NOTE: This relies on the the Screen Options class by Janis Elsts
				 */
				add_screen_options_panel( 'cn-manage-page-limit' , 'Show on screen' , array( __CLASS__, 'managePageLimit' ) , $instance->pageHook->manage , array( __CLASS__, 'managePageLimitSaveAJAX' ) , FALSE );
			}

		}

	}

	/**
	 * Display the database upgrade notice. This will only be shown on non-Connections pages.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses get_current_screen()
	 * @return (void)
	 */
	public static function displayDBUpgradeNotice() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$screen = get_current_screen();

		if ( ! in_array( $screen->id, (array) $instance->pageHook ) ) cnMessage::create( 'notice', 'db_update_required' );
	}

	/**
	 * Add the Settings link to the plugin admin page.
	 *
	 * @access private
	 * @since unknown
	 * @param (array) $links
	 * @return (void)
	 */
	public static function addActionLinks( $links ) {

		$new_links = array();

		$new_links[] = '<a href="admin.php?page=connections_settings">' . __( 'Settings', 'connections' ) . '</a>';

		return array_merge( $new_links, $links );
	}

	/**
	 * Add the links for premium templates, extensions and support info.
	 *
	 * @access private
	 * @since unknown
	 * @param (array) $links
	 * @param (string) $file
	 * @return (void)
	 */
	public static function addMetaLinks( $links, $file ) {

		if ( $file == CN_BASE_NAME ) {

			$links[] = '<a href="http://connections-pro.com/?page_id=29" target="_blank">' . __( 'Extensions', 'connections' ) . '</a>';
			$links[] = '<a href="http://connections-pro.com/?page_id=419" target="_blank">' . __( 'Templates', 'connections' ) . '</a>';
			$links[] = '<a href="http://connections-pro.com/documentation/connections/" target="_blank">' . __( 'Documentation', 'connections' ) . '</a>';
			$links[] = '<a href="http://connections-pro.com/support" target="_blank">' . __( 'Support Forums', 'connections' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Add the changelog as a table row on the Manage Plugin admin screen.
	 * Code based on Changelogger.
	 *
	 * @access private
	 * @since unknown
	 * @uses get_option()
	 * @uses get_transient()
	 * @return (string)
	 */
	public static function displayUpgradeNotice() {

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		//echo "<tr><td colspan='5'>TEST</td></tr>";
		//$api = plugins_api('plugin_information', array('slug' => 'connections', 'fields' => array('tested' => true, 'requires' => false, 'rating' => false, 'downloaded' => false, 'downloadlink' => false, 'last_updated' => false, 'homepage' => false, 'tags' => false, 'sections' => true) ));
		//print_r($api);

		if ( version_compare( $GLOBALS['wp_version'], '2.9.999', '>' ) ) // returning bool if at least WP 3.0 is running
			$current = get_option( '_site_transient_update_plugins' );

		elseif ( version_compare( $GLOBALS['wp_version'], '2.7.999', '>' ) ) // returning bool if at least WP 2.8 is running
			$current = get_transient( 'update_plugins' );

		else
			$current = get_option( 'update_plugins' );

		//print_r($current);

		if ( !isset( $current->response[ CN_BASE_NAME ] ) ) return NULL;

		$r = $current->response[ CN_BASE_NAME ]; // response should contain the slug and upgrade_notice within an array.
		//print_r($r);

		if ( isset( $r->upgrade_notice ) ) {

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
	 * Register the metaboxes used for editing an entry.
	 *
	 * @access private
	 * @since 0.7.1.3
	 * @uses add_filter()
	 * @uses current_filter()
	 * @return (void)
	 */
	public static function registerEditMetaboxes() {

		// The meta boxes do not need diplayed/registered if no action is being taken on an entry. Such as copy/edit.
		if ( $_GET['page'] === 'connections_manage' && ! isset( $_GET['cn-action'] ) )  return;

		$form = new cnFormObjects();

		$form->registerEditMetaboxes( substr( current_filter(), 5 ) );

		add_filter( 'screen_layout_columns', array( __CLASS__, 'screenLayout' ), 10, 2 );
	}

	/**
	 * Register the metaboxes used for the Dashboard.
	 *
	 * @access private
	 * @since 0.7.1.6
	 * @uses add_filter()
	 * @return (void)
	 */
	public static function registerDashboardMetaboxes() {

		$form = new cnFormObjects();
		$form->registerDashboardMetaboxes();

		add_filter( 'screen_layout_columns', array( __CLASS__, 'screenLayout' ), 10, 2 );
	}

	/**
	 * Register the number of columns permitted for metabox use on the edit entry page.
	 *
	 * @access private
	 * @since 0.7.1.3
	 * @param $columns (array)
	 * @param $screen (string)
	 * @return (array)
	 */
	public static function screenLayout( $columns, $screen ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$columns[ $instance->pageHook->dashboard ] = 2;
		$columns[ $instance->pageHook->manage ] = 2;
		$columns[ $instance->pageHook->add ] = 2;

		return $columns;
	}

	/**
	 * Add the page limit panel to the screen options of the manage page.
	 * NOTE: This relies on the the Screen Options class by Janis Elsts
	 *
	 * @access private
	 * @since unknown
	 * @return (string)
	 */
	public static function managePageLimit() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$page = $instance->currentUser->getFilterPage( 'manage' );

		$out = '<label><input type="text" class="entry-per-page" name="wp_screen_options[value]" id="edit_entry_per_page" maxlength="3" value="' . $page->limit . '" />' . __( 'Entries', 'connections' ) . '</label>';
		$out .= '<input type="hidden" name="wp_screen_options[option]" id="edit_entry_per_page_name" value="connections" />';
		$out .= '<input type="submit" name="screen-options-apply" id="entry-per-page-apply" class="button" value="Apply"  />';

		return $out;
	}

	/**
	 * Save the user setting for the page limit on the screen options of the manage page.
	 * NOTE: This is only run during the AJAX callback which is currently disabled.
	 * NOTE: This relies on the the Screen Options class by Janis Elsts
	 *
	 * @access private
	 * @since unknown
	 * @return (void)
	 */
	public static function managePageLimitSaveAJAX() {

		include_once CN_PATH . 'includes/admin/inc.processes.php';
		processSetUserFilter();
	}

	/**
	 * Save the user entered value for display n-number of entries on the manage admin page.
	 *
	 * @access private
	 * @since unknown
	 * @uses get_user_meta()
	 * @uses absint()
	 * @param (bool) $false
	 * @param (string) $option
	 * @param (int) $value
	 * @return (array)
	 */
	public static function managePageLimitSave( $false = FALSE , $option , $value ) {

		if ( $option !== 'connections' ) return $false;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$user_meta = get_user_meta( $instance->currentUser->getID() , $option, TRUE );

		$user_meta['filter']['manage']['limit'] = absint( $value );
		$user_meta['filter']['manage']['current'] = 1;

		return $user_meta;
	}

}