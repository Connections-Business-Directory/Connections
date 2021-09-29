<?php

/**
 * Class for adding the admin menus and show its pages.
 *
 * @package     Connections
 * @subpackage  Admin Menu
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnAdminMenu
 */
class cnAdminMenu {

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
	 * @see cnAdminMenu::init()
	 * @see cnAdminMenu();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class, if it has already been initialized, return the intialized instance.
	 *
	 * @access public
	 * @since 0.7.9
	 * @see cnAdminMenu()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;
			self::menu();
			self::addSupportMenuItem();
			self::stylizeSupportMenuItems();

			add_action( 'admin_footer', array( __CLASS__, 'supportMenuItemTargetBlank' ) );
		}
	}

	/**
	 * Register the admin menus.
	 *
	 * @access private
	 * @since unknown
	 */
	public static function menu() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Set the capability string to be used in the add_sub_menu function per role capability assigned to the current user.
		$addEntryCapability = current_user_can( 'connections_add_entry' ) ? 'connections_add_entry' : 'connections_add_entry_moderated';

		// Register the top level menu item.
		if ( current_user_can( 'connections_view_menu') ) {

			$instance->pageHook->topLevel = add_menu_page( 'Connections', 'Connections', 'connections_view_dashboard', 'connections_dashboard', array( __CLASS__, 'showPage' ), CN_URL . 'assets/images/menu.png', CN_ADMIN_MENU_POSITION );
		}

		$submenu[0]   = array( 'hook' => 'dashboard', 'page_title' => 'Connections : ' . __( 'Dashboard', 'connections' ), 'menu_title' => __( 'Dashboard', 'connections' ), 'capability' => 'connections_view_dashboard', 'menu_slug' => 'connections_dashboard', 'function' => array( __CLASS__, 'showPage' ) );
		$submenu[20]  = array( 'hook' => 'manage', 'page_title' => 'Connections : ' . __( 'Manage', 'connections' ), 'menu_title' => __( 'Manage', 'connections' ), 'capability' => 'connections_manage', 'menu_slug' => 'connections_manage', 'function' => array( __CLASS__, 'showPage' ) );
		$submenu[40]  = array( 'hook' => 'add', 'page_title' => 'Connections : ' . __( 'Add Entry', 'connections' ), 'menu_title' => __( 'Add Entry', 'connections' ), 'capability' => $addEntryCapability, 'menu_slug' => 'connections_add', 'function' => array( __CLASS__, 'showPage' ) );
		//$submenu[60]  = array( 'hook' => 'categories', 'page_title' => 'Connections : ' . __( 'Categories', 'connections' ), 'menu_title' => __( 'Categories', 'connections' ), 'capability' => 'connections_edit_categories', 'menu_slug' => 'connections_categories', 'function' => array( __CLASS__, 'showPage' ) );
		$submenu[80]  = array( 'hook' => 'templates', 'page_title' => 'Connections : ' . __( 'Templates', 'connections' ), 'menu_title' => __( 'Templates', 'connections' ), 'capability' => 'connections_manage_template', 'menu_slug' => 'connections_templates', 'function' => array( __CLASS__, 'showPage' ) );
		$submenu[100] = array( 'hook' => 'roles', 'page_title' => 'Connections : ' . __( 'Roles &amp; Capabilities', 'connections' ), 'menu_title' => __( 'Roles', 'connections' ), 'capability' => 'connections_change_roles', 'menu_slug' => 'connections_roles', 'function' => array( __CLASS__, 'showPage' ) );
		$submenu[110] = array( 'hook' => 'tools', 'page_title' => 'Connections : ' . __( 'Tools', 'connections' ), 'menu_title' => __( 'Tools', 'connections' ), 'capability' => 'edit_posts', 'menu_slug' => 'connections_tools', 'function' => array( __CLASS__, 'showPage' ) );
		$submenu[120] = array( 'hook' => 'settings', 'page_title' => 'Connections : ' . __( 'Settings', 'connections' ), 'menu_title' => __( 'Settings', 'connections' ), 'capability' => 'connections_change_settings', 'menu_slug' => 'connections_settings', 'function' => array( __CLASS__, 'showPage' ) );

		$submenu = apply_filters( 'cn_submenu', $submenu );

		uksort( $submenu, 'strnatcasecmp' );

		foreach ( $submenu as $menu ) {

			/**
			 * @var string       $hook
			 * @var string       $page_title
			 * @var string       $menu_title
			 * @var string       $capability
			 * @var string       $menu_slug
			 * @var array|string $function
			 */
			extract( $menu );

			$instance->pageHook->{ $hook } = add_submenu_page( 'connections_dashboard', $page_title, $menu_title, $capability, $menu_slug, $function );
		}

	}

	/**
	 * Add the support sub menu item at the bottom of the Connections menu items.
	 *
	 * @since 8.39.1
	 */
	private static function addSupportMenuItem() {

		global $submenu;

		$permalink = apply_filters(
			'Connections_Directory/Admin/Menu/Submenu/Support/Permalink',
			'https://wordpress.org/support/plugin/connections/'
		);

		$title = apply_filters(
			'Connections_Directory/Admin/Menu/Submenu/Support/Title',
			__( 'Support', 'connections' )
		);

		$title     = esc_html( $title );
		$permalink = esc_url( $permalink );

		$submenu['connections_dashboard'][] = array( $title, 'manage_options', $permalink );
	}

	/**
	 * Add span tag with class and id around the menu item URL so it can be targeted with CSS/JS.
	 *
	 * @since 8.39.1
	 */
	private static function stylizeSupportMenuItems() {

		global $submenu;

		foreach ( $submenu['connections_dashboard'] as &$item ) {

			$template = '<span class="cn-submenu-item" id="cn-submenu-item-%s">%s</span>';
			$slug     = strtolower( preg_replace( "/[^[:alnum:][:space:]]/u", '', $item[0] ) );

			$item[0] = sprintf( $template, $slug, $item[0] );
		}
	}

	/**
	 * Opens the support forum sub menu item in a new browser tab.
	 *
	 * @since 8.39.1
	 */
	public static function supportMenuItemTargetBlank() {

		?>
		<script type="text/javascript">
			( function( $ ) {
				$( '[id^=cn-submenu-item-support]' ).parent().attr( 'target', '_blank' );
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * This is the registered function calls for the Connections admin pages as registered
	 * using the add_menu_page() and add_submenu_page() WordPress functions.
	 *
	 * @access private
	 * @since unknown
	 */
	public static function showPage() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( $instance->dbUpgrade ) {

			include_once CN_PATH . 'includes/inc.upgrade.php';
			connectionsShowUpgradePage();
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

		switch ( $page ) {

			case 'connections_dashboard':
				include_once CN_PATH . 'includes/admin/pages/dashboard.php';
				connectionsShowDashboardPage();
				break;

			case 'connections_manage':
				include_once CN_PATH . 'includes/admin/pages/manage.php';
				// phpcs:ignore WordPress.Security.NonceVerification
				$action = ( isset( $_GET['cn-action'] ) && ! empty( $_GET['cn-action'] ) ) ? sanitize_key( $_GET['cn-action'] ) : '';
				connectionsShowViewPage( $action );
				break;

			case 'connections_add':
				include_once CN_PATH . 'includes/admin/pages/manage.php';
				connectionsShowViewPage( 'add_entry' );
				break;

			case 'connections_settings':
				include_once CN_PATH . 'includes/admin/pages/settings.php';
				connectionsShowSettingsPage();
				break;

			case 'connections_tools':
				include_once CN_PATH . 'includes/admin/pages/tools.php';
				connectionsShowToolsPage();
				break;

			case 'connections_templates':
				include_once CN_PATH . 'includes/admin/pages/templates.php';
				connectionsShowTemplatesPage();
				break;

			case 'connections_roles':
				include_once CN_PATH . 'includes/admin/pages/roles.php';
				connectionsShowRolesPage();
				break;
		}
	}

}
