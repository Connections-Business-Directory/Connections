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

			// if ( ! file_exists( CN_CUSTOM_TEMPLATE_PATH ) ) cnMessage::create( 'notice', 'template_path_exists_failed' );
			// if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) cnMessage::create( 'notice', 'template_path_writeable_failed' );

			//if ( ! file_exists( CN_CACHE_PATH ) ) cnMessage::create( 'notice', 'cache_path_exists_failed' );
			//if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) cnMessage::create( 'notice', 'cache_path_writeable_failed' );

			// Add Settings link to the plugin actions
			add_action( 'plugin_action_links_' . CN_BASE_NAME, array( __CLASS__, 'addActionLinks' ) );

			// Add FAQ, Support and Donate links
			add_filter( 'plugin_row_meta', array( __CLASS__, 'addMetaLinks' ), 10, 2 );

			// Add Changelog table row in the Manage Plugins admin page.
			add_action( 'in_plugin_update_message-' . CN_BASE_NAME, array( __CLASS__, 'displayUpgradeNotice' ), 20, 2 );

			// Add the screen layout filter.
			add_filter( 'screen_layout_columns', array( __CLASS__, 'screenLayout' ), 10, 2 );

			add_action( 'current_screen', array( __CLASS__, 'screenOptionsPanel' ) );

			add_filter( 'admin_footer_text', array( __CLASS__, 'rateUs' ) );
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
	 * Display the upgrade notice and changelog on the Manage Plugin admin screen.
	 *
	 * Inspired by Changelogger. Code based on W3 Total Cache.
	 *
	 * @access private
	 * @since  unknown
	 * @uses   plugins_api()
	 * @param  array  $plugin_data An Array of the plugin metadata
	 * @param  object $r An array of metadata about the available plugin update.
	 *
	 * @return string
	 */
	public static function displayUpgradeNotice( $plugin_data, $r ) {

		// echo '<p>' . print_r( $r, TRUE ) .  '</p>';
		// echo '<p>' . print_r( $plugin_data, TRUE ) .  '</p>';

		// Show the upgrade notice if it exists.
		if ( isset( $r->upgrade_notice ) ) {

			echo '<p style="margin-top: 1em"><strong>' . sprintf( __( 'Upgrade notice for version: %s', 'connections' ), $r->new_version ) . '</strong></p>';
			echo '<ul style="list-style-type: square; margin-left:20px;"><li>' . $r->upgrade_notice . '</li></ul>';
		}

		// Grab the plugin info using the WordPress.org Plugins API.
		// First, check to see if the function exists, if it doesn't, include the file which contains it.
		if ( ! function_exists( 'plugins_api' ) )
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$plugin = plugins_api(
			'plugin_information',
			array(
				'slug'   => 'connections',
				'fields' => array(
					'tested'       => TRUE,
					'requires'     => FALSE,
					'rating'       => FALSE,
					'downloaded'   => FALSE,
					'downloadlink' => FALSE,
					'last_updated' => FALSE,
					'homepage'     => FALSE,
					'tags'         => FALSE,
					'sections'     => TRUE
					)
				)
			);
		// echo '<p>' . print_r( $plugin, TRUE ) .  '</p>';
		// echo '<p>' . print_r( $plugin->sections['changelog'], TRUE ) .  '</p>';

		// Create the regex that'll parse the changelog for the latest version.
		$regex = '~<h([1-6])>' . preg_quote( $r->new_version ) . '.+?</h\1>(.+?)<h[1-6]>~is';

		preg_match( $regex, $plugin->sections['changelog'], $matches );
		// echo '<p>' . print_r( $matches, TRUE ) .  '</p>';

		// If no changelog is found for the current version, return.
		if ( ! isset( $matches[2] ) || empty( $matches[2] ) ) return;

		preg_match_all( '~<li>(.+?)</li>~', $matches[2], $matches );
		// echo '<p>' . print_r( $matches, TRUE ) .  '</p>';

		// Make sure the change items were found and not entry before proceeding.
		if ( ! isset( $matches[1] ) || empty( $matches[1] ) ) return;

		$ul = FALSE;

		// Finally, lets render the changelog list.
		foreach ( $matches[1] as $key => $line ) {

			if ( ! $ul ) {

				echo '<p style="margin-top: 1em"><strong>' . __( 'Take a minute to update, here\'s why:', 'connections' ) . '</strong></p>';
				echo '<ul style="list-style-type: square; margin-left: 20px;margin-top:0px;">';
				$ul = TRUE;
			}

			echo '<li style="width: 50%; margin: 0; float: left;' . ( $key % 2 == 0 ? ' clear: left;' : '' ) . '">' . $line . '</li>';
		}

		if ( $ul ) {

			echo '</ul><div style="clear: left;">';
		}

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

		/*
		 * The Screen Layout options in the Screen Options tab only needs to be added on the manage page if performing an action to an entry.
		 * This is to prevent the Screen Layout options in the Screen Options tab from being displayed on the Manage
		 * admin page when viewing the manage entries table.
		 */
		if ( $screen == $instance->pageHook->manage && ! isset( $_GET['cn-action'] ) ) return $columns;

		$columns[ $instance->pageHook->dashboard ] = 2;
		$columns[ $instance->pageHook->manage ] = 2;
		$columns[ $instance->pageHook->add ] = 2;

		return $columns;
	}

	/**
	 * Adds the "Show on screen" option to limit number of entries per page on the Connections : Manage admin page.
	 *
	 * @access private
	 * @since  0.8.14
	 * @static
	 * @uses   get_object_vars()
	 * @uses   current_user_can()
	 * @uses   add_screen_options_panel()
	 * @param  object $screen An instance of the WordPress screen object.
	 * @return void
	 */
	public static function screenOptionsPanel( $screen ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * In instances such as WP AJAX requests the add_menu() and add_sub_menu() functions are
		 * not run in the admin_menu action, so the properties would not exist and will throw
		 * PHP notices when attempting to access them. If the menus have been added then the
		 * properties will exist so it will be safe to add the actions using the properties.
		 */
		if ( get_object_vars( $instance->pageHook ) && current_user_can( 'connections_view_menu') ) {

			/*
			 * The Screen Layout options in the Screen Option tab only needs to be added on the manage page when NOT performing an action to an entry.
			 * This is to prevent the Screen Layout options in the Screen Option tab on the Manage
			 * admin page when performing an action on an entry.
			 */
			if ( $screen->id == $instance->pageHook->manage && ! isset( $_GET['cn-action'] ) ) {

				/*
				 * Add the panel to the "Screen Options" box to the manage page.
				 * NOTE: This relies on the the Screen Options class by Janis Elsts
				 */
				add_screen_options_panel( 'cn-manage-page-limit', 'Show on screen', array( __CLASS__, 'managePageLimit' ), $instance->pageHook->manage );
			}

		}
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

		$out = '<label><input type="number" step="1" min="1" max="999" class="screen-per-page" name="wp_screen_options[value]" id="entries_per_page" maxlength="3" value="' . $page->limit . '" />' . __( 'Entries', 'connections' ) . '</label>';
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

		// include_once CN_PATH . 'includes/admin/inc.processes.php';
		// processSetUserFilter();
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
	public static function managePageLimitSave( $false, $option, $value ) {

		if ( $option !== 'connections' ) return $false;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$user_meta = get_user_meta( $instance->currentUser->getID(), 'connections', TRUE );

		$user_meta['filter']['manage']['limit'] = absint( $value );
		$user_meta['filter']['manage']['current'] = 1;

		return $user_meta;
	}

	/**
	 * Add rating links to the admin dashboard
	 *
	 * @access private
	 * @since  8.2.9
	 *
	 * @param  string $text The existing footer text
	 *
	 * @return string
	 */
	public static function rateUs( $text ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			return $text;
		}

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		//var_dump( get_current_screen()->id );
		//var_dump( $instance->pageHook );

		if ( in_array( get_current_screen()->id, get_object_vars( $instance->pageHook ) ) ) {
		//if ( in_array( get_current_screen()->id, (array) $instance->pageHook ) ) {

			$rate_text = sprintf(
				__(
					'Thank you for using <a href="%1$s" target="_blank">Connections Business Directory</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>',
					'connections'
				),
				'http://connections-pro.com',
				'https://wordpress.org/support/view/plugin-reviews/connections?filter=5#postform'
			);

			return str_replace( '</span>', '', $text ) . ' | ' . $rate_text . '</span>';

		} else {

			return $text;
		}
	}

}

// Adds the admin actions and filters.
add_action( 'admin_init', array( 'cnAdminFunction', 'init' ) );

/*
 * Add the filter to update the user settings when the "Apply" button is clicked.
 * NOTE: This relies on the the Screen Options class by Janis Elsts
 * NOTE: This filter must be added here otherwise it registers to late to be run.
 */
add_filter( 'set-screen-option', array( 'cnAdminFunction', 'managePageLimitSave' ), 10, 3 );
