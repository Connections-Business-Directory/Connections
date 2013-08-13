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
	 * @return (object) cnAdminActions
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
	 * @return (void)
	 */
	private static function registerActions() {

		// Entry Actions
		add_action( 'cn_add_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_update_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_duplicate_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_delete_entry', array( __CLASS__, 'deleteEntry' ) );
		add_action( 'cn_set_status', array( __CLASS__, 'setEntryStatus' ) );

		// Save the user's manage admin page actions.
		add_action( 'cn_manage_actions', array( __CLASS__, 'entryManagement' ) );
		add_action( 'cn_filter', array( __CLASS__, 'userFilter' ) );

		// Role Actions
		add_action( 'cn_update_role_capabilities', array( __CLASS__, 'updateRoleCapabilities' ) );

		// Category Actions
		add_action( 'cn_add_category', array( __CLASS__, 'addCategory' ) );
		add_action( 'cn_update_category', array( __CLASS__, 'updateCategory' ) );
		add_action( 'cn_delete_category', array( __CLASS__, 'deleteCategory' ) );
		add_action( 'cn_category_bulk_actions', array( __CLASS__, 'categoryManagement' ) );

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
	 * @return (void)
	 */
	private static function doActions() {

		if ( isset( $_POST['cn-action'] ) ) {

			do_action( 'cn_' . $_POST['cn-action'] );
		}

		if ( isset( $_GET['cn-action'] ) ) {

			do_action( 'cn_' . $_GET['cn-action'] );
		}
	}

	/**
	 * Process controller for action taken by the user.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function entryManagement() {

		$form = new cnFormObjects();
		$queryVar = array();

		check_admin_referer( $form->getNonce( 'cn_manage_actions' ), '_cn_wpnonce' );

		/*
		 * Run user requested actions.
		 */

		// Process user selected filters
		if ( isset( $_POST['filter'] ) || isset( $_GET['filter'] ) ) self::saveUserFilters();

		// Grab the bulk action requesteed by user.
		$action = isset( $_POST['bulk_action'] ) && ( isset( $_POST['action'] ) && ! empty( $_POST['action'] ) ) ? $_POST['action'] : 'none';

		switch ( $action ) {

			case 'delete':

				// Bulk delete entries.
				self::deleteEntryBulk();
				break;

			case 'approve':

				// Bulk approve entries.
				self::setEntryStatusBulk( 'approved' );
				break;

			case 'unapprove':

				// Bulk unapprove entries.
				self::setEntryStatusBulk( 'pending' );
				break;

			case 'public':

				// Set entries to public visibility in bulk.
				self::setEntryVisibilityBulk( 'public' );
				break;

			case 'private':

				// Set entries to private visibility in bulk.
				self::setEntryVisibilityBulk( 'private' );
				break;

			case 'unlisted':

				// Set entries to unlisted visibility in bulk.
				self::setEntryVisibilityBulk( 'unlisted' );
				break;

			default:

				/* None, blank intentially. */

				break;
		}

		/*
		 * Setup the redirect.
		 */

		if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) )
			$queryVar['s'] = urlencode( $_POST['s'] );

		// if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) )
		// 	$queryVar['s'] = urlencode( $_GET['s'] );

		if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) )
			$queryVar['cn-char'] = urlencode( $_GET['cn-char'] );

		/*
		 * Do the redirect.
		 */

		wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( $queryVar, 'admin.php?page=connections_manage' ) ) );

		exit();
	}

	/**
	 * Add / Edit / Update / Copy an entry.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (array)  $data [optional]
	 * @param  (string) $action [optional]
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function processEntry() {
		global $wpdb, $connections;
		$entry = new cnEntry();
		$form = new cnFormObjects();

		$action = $_GET['cn-action'] ? $_GET['cn-action'] : $_POST['cn-action'];

		// Setup the redirect URL.
		$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_add';

		switch ( $action ) {

			case 'add_entry':

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'add_entry' ), '_cn_wpnonce' );

					cnEntry_Action::add( $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'duplicate_entry':

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'add_entry' ), '_cn_wpnonce' );

					cnEntry_Action::copy( $_GET['id'], $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'update_entry':

				// Setup the redirect URL.
				$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_manage';

				/*
				 * Check whether the current user can edit an entry.
				 */
				if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'update_entry' ), '_cn_wpnonce' );

					cnEntry_Action::update( $_GET['id'], $_POST );

				} else {

					cnMessage::set( 'error', 'capability_edit' );
				}

				break;
		}

		wp_redirect( get_admin_url( get_current_blog_id(), $redirect) );

		exit();
	}

	/**
	 * Set the entry status to pending or approved.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (int) $id [optional] Entry ID.
	 * @param (string) $status [optional] The entry status to be assigned.
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function setEntryStatus( $id = '', $status = '' ) {

		// If no entry ID was supplied, check $_GET.
		$id = empty( $id ) && ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) ? absint( $_GET['id'] ) : absint( $id );

		check_admin_referer( 'entry_status_' . $id );

		/*
		 * Check whether the current user can edit an entry.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			//  The permitted statuses.
			$permitted = array( 'pending', 'approved' );

			// If `status` was not supplied, check $_GET.
			if ( ( empty( $status ) ) && ( isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ) ) {

				$status = $_GET['status'];

			}

			// Ensure the supplied status is a permitted status, else default `status` to `pending`.
			// If no `status` was supplied, this will default `status` to `pending`.
			$status = in_array( $status, $permitted ) ? $status : 'pending';

			cnEntry_Action::status( $status, $id );

			switch ( $status ) {

				case 'pending':

					cnMessage::set( 'success', 'form_entry_pending' );
					break;

				case 'approve':

					cnMessage::set( 'success', 'form_entry_approve' );
					break;
			}


		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}

		wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Set the approval status of entries in bulk.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (string) $status The entry status that should be set.
	 * @return (void)
	 */
	public static function setEntryStatusBulk( $status ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'pending', 'approved' );

			if ( ! in_array( $status, $permitted ) ) return;

			cnEntry_Action::status( $status, $_POST['id'] );

			switch ( $status ) {

				case 'pending':

					cnMessage::set( 'success', 'form_entry_pending_bulk' );
					break;

				case 'approved':

					cnMessage::set( 'success', 'form_entry_approve_bulk' );
					break;
			}

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Set the visibility status of entries in bulk.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (string) $status The entry visibility that should be set.
	 * @return (void)
	 */
	static public function setEntryVisibilityBulk( $visibility ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'public', 'private', 'unlisted' );

			if ( ! in_array( $visibility, $permitted ) ) return;

			cnEntry_Action::visibility( $visibility, $_POST['id'] );

			cnMessage::set( 'success', 'form_entry_visibility_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Delete an entry.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (int) $id [optional] Entry ID.
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function deleteEntry( $id = '' ) {
		global $connections;

		// If no entry ID was supplied, check $_GET.
		$id = empty( $id ) && ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) ? $_GET['id'] : $id;

		check_admin_referer( 'entry_delete_' . $id );

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			cnEntry_Action::delete( $id );

			cnMessage::set( 'success', 'form_entry_delete' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

		wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Delete entries in bulk.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return (void)
	 */
	public static function deleteEntryBulk() {
		global $connections;

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			// @TODO $POST['id'] should be passed to the method as an attribute.
			if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) return;

			cnEntry_Action::delete( $_POST['id'] );

			cnMessage::set( 'success', 'form_entry_delete_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

	}

	/**
	 * Process user filteres.
	 *
	 * @access public
	 * @since 0.7.8
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function userFilter() {

		$queryVar = array();

		check_admin_referer( 'filter' );

		self::saveUserFilters();

		/*
		 * Setup the redirect.
		 */

		if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) )
			$queryVar['s'] = urlencode( $_POST['s'] );

		// if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) )
		// 	$queryVar['s'] = urlencode( $_GET['s'] );

		if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) )
			$queryVar['cn-char'] = urlencode( $_GET['cn-char'] );

		/*
		 * Do the redirect.
		 */

		wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( $queryVar, 'admin.php?page=connections_manage' ) ) );

		exit();
	}

	/**
	 * Save user filteres.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return (void)
	 */
	public static function saveUserFilters() {
		global $connections;

		// Set the moderation filter for the current user if set in the query string.
		if ( isset( $_GET['status'] ) ) $connections->currentUser->setFilterStatus( $_GET['status'] );

		if ( isset( $_POST['entry_type'] ) ) $connections->currentUser->setFilterEntryType( esc_attr( $_POST['entry_type'] ) );
		if ( isset( $_POST['visibility_type'] ) ) $connections->currentUser->setFilterVisibility( esc_attr( $_POST['visibility_type'] ) );

		if ( isset( $_POST['category'] ) && ! empty( $_POST['category'] ) ) $connections->currentUser->setFilterCategory( esc_attr( $_POST['category'] ) );
		if ( isset( $_GET['category'] ) && ! empty( $_GET['category'] ) ) $connections->currentUser->setFilterCategory( esc_attr( $_GET['category'] ) );

		if ( isset( $_POST['pg'] ) && ! empty( $_POST['pg'] ) ) {
			$page = new stdClass();

			$page->name = 'manage';
			$page->current = absint( $_POST['pg'] );

			$connections->currentUser->setFilterPage( $page );
		}

		if ( isset( $_GET['pg'] ) && ! empty( $_GET['pg'] ) ) {
			$page = new stdClass();

			$page->name = 'manage';
			$page->current = absint( $_GET['pg'] );

			$connections->currentUser->setFilterPage( $page );
		}

		if ( isset( $_POST['settings']['page']['limit'] ) ) {
			$page = new stdClass();

			$page->name = 'manage';
			$page->limit = $_POST['settings']['page']['limit'];

			$connections->currentUser->setFilterPage( $page );
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
			$category->setDescription( $format->sanitizeString( $_POST['category_description'], TRUE ) );

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
			$category->setDescription( $format->sanitizeString( $_POST['category_description'], TRUE ) );

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
	public static function categoryManagement() {
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
	public static function updateRoleCapabilities() {
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