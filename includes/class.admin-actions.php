<?php

/**
 * Class for processing admin action.
 *
 * @package     Connections
 * @subpackage  Admin Actions
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnAdminActions {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.7.5
	 * @var (object)
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.7.5
	 * @see cnAdminActions::init()
	 * @see cnAdminActions();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class, if it has already been initialized, return the intialized instance.
	 *
	 * @access public
	 * @since 0.7.5
	 * @see cnAdminActions()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			self::registerActions();
			self::doActions();

		}

	}

	/**
	 * Return an instance of the class.
	 *
	 * @access public
	 * @since 0.7.5
	 * @return object cnMessage
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Register admin actions.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses add_action()
	 * @return void
	 */
	private static function registerActions() {

		// Role Actions
		add_action( 'cn_update_role_settings', array( __CLASS__, 'updateRoleSettings' ) );

		// Category Actions
		add_action( 'cn_add_category', array( __CLASS__, 'addCategory' ) );
		add_action( 'cn_update_category', array( __CLASS__, 'updateCategory' ) );
		add_action( 'cn_delete_category', array( __CLASS__, 'deleteCategory' ) );
		add_action( 'cn_category', array( __CLASS__, 'categoryActions' ) );

		// Template Actions
		add_action( 'cn_activate_template', array( __CLASS__, 'activateTemplate' ) );
		add_action( 'cn_install_template', array( __CLASS__, 'installTemplate' ) );
		add_action( 'cn_delete_template', array( __CLASS__, 'deleteTemplate' ) );
	}

	/**
	 * Run admin actions.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses do_action()
	 * @return void
	 */
	private static function doActions() {

		if ( isset( $_POST['cn-action'] ) ) {
			do_action( $_POST['cn-action'] );
		}

		if ( isset( $_GET['cn-action'] ) ) {
			do_action( $_GET['cn-action'] );
		}
	}

	/**
	 * Add a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function addCategory() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( $form->getNonce( 'add_category' ), '_cn_wpnonce' );

			$category = new cnCategory();
			$format = new cnFormatting();

			$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
			$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
			$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
			$category->setDescription( $format->sanitizeString( $_POST['category_description'] ) );

			$category->save();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Update a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function updateCategory() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( $form->getNonce( 'update_category' ), '_cn_wpnonce' );

			$category = new cnCategory();
			$format = new cnFormatting();

			$category->setID( $format->sanitizeString( $_POST['category_id'] ) );
			$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
			$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
			$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
			$category->setDescription( $format->sanitizeString( $_POST['category_description'] ) );

			$category->update();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Delete a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function deleteCategory() {
		global $connections;

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			$id = esc_attr( $_GET['id'] );
			check_admin_referer( 'category_delete_' . $id );

			$result = $connections->retrieve->category( $id );
			$category = new cnCategory( $result );
			$category->delete();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Bulk category actions.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function categoryActions() {
		global $connections;

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			$form = new cnFormObjects();

			switch ( $_POST['action'] ) {

				case 'delete':

					check_admin_referer( $form->getNonce( 'bulk_delete_category' ), '_cn_wpnonce' );

					foreach ( (array) $_POST['category'] as $cat_ID ) {

						$cat_ID = esc_attr( $cat_ID );

						$result = $connections->retrieve->category( attribute_escape( $cat_ID ) );
						$category = new cnCategory( $result );
						$category->delete();
					}

					break;
				}

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Install a legacy template.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses unzip_file()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function installTemplate() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

			check_admin_referer( $form->getNonce( 'install_template' ), '_cn_wpnonce' );

			require_once ABSPATH . 'wp-admin/includes/file.php';

			WP_Filesystem();

			if ( unzip_file( $_FILES['template']['tmp_name'], CN_CUSTOM_TEMPLATE_PATH ) ) {
				cnMessage::set( 'success', 'template_installed' );
			} else {
				cnMessage::set( 'error', 'template_install_failed' );
			}

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Activate a template.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function activateTemplate() {
		global $connections;

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

			$templateName = esc_attr( $_GET['template'] );
			check_admin_referer( 'activate_' . $templateName );

			$type = esc_attr( $_GET['type'] );
			$slug = esc_attr( $_GET['template'] );

			$connections->options->setActiveTemplate( $type, $slug );

			$connections->options->saveOptions();

			$connections->setSuccessMessage( 'template_change_active' );

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Delete a template.
	 *
	 * @TODO Move the delete to a generic method in cnFileSystem()
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function deleteTemplate() {

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

			$templateName = esc_attr( $_GET['template'] );
			check_admin_referer( 'delete_' . $templateName );

			function removeDirectory( $directory ) {
				$deleteError = FALSE;
				$currentDirectory = opendir( $directory );

				while ( ( $file = readdir( $currentDirectory ) ) !== FALSE ) {

					if ( $file != "." && $file != ".." ) {

						chmod( $directory . $file, 0777 );

						if ( is_dir( $directory . $file ) ) {

							chdir( '.' );
							removeDirectory( $directory . $file . '/' );
							rmdir( $directory . $file ) or $deleteError = TRUE;

						} else {

							@unlink( $directory . $file ) or $deleteError = TRUE;
						}

						if ( $deleteError ) return FALSE;
					}
				}

				closedir( $currentDirectory );

				if ( ! rmdir( $directory ) ) return FALSE;

				return TRUE;
			}

			if ( removeDirectory( CN_CUSTOM_TEMPLATE_PATH . '/' . $templateName . '/' ) ) {
				cnMessage::set( 'success', 'template_deleted' );
			} else {
				cnMessage::set( 'error', 'template_delete_failed' );
			}

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Update the role settings.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function updateRoleSettings() {
		global $wp_roles;

		$form = new cnFormObjects();

		/*
		 * Check whether user can edit roles
		 */
		if ( current_user_can( 'connections_change_roles' ) ) {

			check_admin_referer( $form->getNonce( 'update_role_settings' ), '_cn_wpnonce' );


			if ( isset( $_POST['roles'] ) ) {

				// Cycle thru each role available because checkboxes do not report a value when not checked.
				foreach ( $wp_roles->get_names() as $role => $name ) {

					if ( ! isset( $_POST['roles'][ $role ] ) ) continue;

					foreach ( $_POST['roles'][ $role ]['capabilities'] as $capability => $grant ) {

						// the admininistrator should always have all capabilities
						if ( $role == 'administrator' ) continue;

						if ( $grant == 'true' ) {
							cnRole::add( $role, $capability );
						} else {
							cnRole::remove( $role, $capability );
						}

					}
				}
			}

			if ( isset( $_POST['reset'] ) ) cnRole::reset( $_POST['reset'] );

			if ( isset( $_POST['reset_all'] ) ) cnRole::reset();

			cnMessage::set( 'success', 'role_settings_updated' );


			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_roles' ) );
			exit();

		} else {

			cnMessage::set( 'error', 'capability_roles' );
		}

	}

}