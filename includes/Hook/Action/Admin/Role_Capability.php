<?php
/**
 * Modify role capabilities.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Hook\Action\Admin
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Action\Admin;

use cnMessage;
use cnRole;
use Connections_Directory\Request;
use Connections_Directory\Utility\_array;

/**
 * Class Role_Capability
 *
 * @package Connections_Directory\Hook\Action\Admin
 */
final class Role_Capability {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.31
	 */
	public static function register() {

		add_action( 'cn_update_role_capabilities', array( __CLASS__, 'update' ) );
	}

	/**
	 * Callback for the `cn_update_role_capabilities` action.
	 *
	 * Update the role settings.
	 *
	 * @internal
	 * @since 10.4.31
	 */
	public static function update() {

		$action = new self();

		if ( $action->isValid() ) {

			$request     = Request\Role_Capability::input()->value();
			$modifyRoles = _array::get( $request, 'roles', array() );
			$resetAll    = _array::get( $request, 'reset_all' );
			$resetRoles  = _array::get( $request, 'reset' );

			/*
			 * Rest all role capabilities and redirect as there is no need to do further processing.
			 */
			if ( true === $resetAll ) {

				cnRole::reset();
				cnMessage::set( 'success', __( 'All role capabilities have been reset.', 'connections' ) );
				$action->redirect();
			}

			/*
			 * Reset the individually selected roles.
			 * Remove the reset roles from the modified role capabilities since the reset option was selected.
			 */
			if ( is_array( $resetRoles ) ) {

				cnRole::reset( $resetRoles );
				cnMessage::set( 'success', __( 'Selected role capabilities have been reset.', 'connections' ) );

				foreach ( $resetRoles as $roleSlug => $roleName ) {

					_array::forget( $modifyRoles, $roleSlug );
				}
			}

			/*
			 * Modify the individual role capabilities.
			 */
			foreach ( $modifyRoles as $roleSlug => $capabilities ) {

				foreach ( $capabilities['capabilities'] as $capability => $grant ) {

					if ( true === $grant ) {

						cnRole::add( $roleSlug, $capability );

					} else {

						cnRole::remove( $roleSlug, $capability );
					}
				}
			}

			cnMessage::set( 'success', __( 'Role capabilities have been updated.', 'connections' ) );

		} else {

			cnMessage::set( 'error', __( 'You are not authorized to edit role capabilities. Please contact the admin if you received this message in error.', 'connections' ) );
		}

		$action->redirect();
	}

	/**
	 * Whether the current user has the required role capability and
	 * that the request nonce is valid.
	 *
	 * @since 10.4.31
	 *
	 * @return bool
	 */
	private function isValid() {

		return current_user_can( 'connections_change_roles' ) &&
			   Request\Nonce::from( INPUT_POST, 'update_role_settings' )->isValid();
	}

	/**
	 * Redirect back to admin page.
	 *
	 * @since  10.4.31
	 */
	private function redirect() {

		wp_safe_redirect(
			get_admin_url(
				get_current_blog_id(),
				'admin.php?page=connections_roles'
			)
		);

		exit();
	}
}
