<?php

/**
 * Class for admin action.
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
	 * Setup the class, if it has already been initialized, return the intialized instance.
	 *
	 * @access private
	 * @since 0.7.5
	 * @see cnMessage::getInstance()
	 * @see cnMessage();
	 */
	public function __construct() {

		if ( isset( self::$instance ) ) {

			return self::$instance;

		} else {

			self::$instance = $this;

		}

		self::registerActions();
		self::doActions();
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

	private static function registerActions() {
		add_action( 'update_role_settings', array( __CLASS__, 'updateRoleSettings' ) );
	}

	private static function doActions() {

		if ( isset( $_POST['cn-action'] ) ) {
			do_action( $_POST['cn-action'] );
		}

		if ( isset( $_GET['cn-action'] ) ) {
			do_action( $_GET['cn-action'] );
		}
	}

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