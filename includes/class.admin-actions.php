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
		add_action( 'update_role_settings', array( __CLASS__, 'updateRoleSettings' ) );
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