<?php
/**
 * Log admin actions.
 *
 * @since 10.4.33
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

use cnLog;
use cnMessage;
use Connections_Directory\Request;

/**
 * Class Log_Management
 *
 * @package Connections_Directory\Hook\Action\Admin
 */
final class Log_Management {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.33
	 */
	public static function register() {

		add_action( 'cn_log_bulk_actions', array( __CLASS__, 'bulkAction' ) );
		add_action( 'cn_delete_log', array( __CLASS__, 'delete' ) );
	}

	/**
	 * Callback for the `cn_log_bulk_actions` hook which processes the action and then redirects back to the current admin page.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function bulkAction() {

		$action  = new self();
		$request = Request\List_Table_Logs::input()->value();
		$type    = $request['type'];

		if ( $action->isValid( 'bulk-email-logs' ) ) {

			switch ( $request['action'] ) {

				case 'delete':
					foreach ( $request['selected'] as $id ) {

						cnLog::delete( $id );
					}

					cnMessage::set( 'success', __( 'The logs have been deleted.', 'connections' ) );

					break;
			}

		} else {

			cnMessage::set( 'error', __( 'You are not authorized to manage logs. Please contact the admin if you received this message in error.', 'connections' ) );
		}

		$url = add_query_arg(
			array(
				'type'      => ! empty( $type ) && '-1' !== $type ? $type : false,
				'cn-action' => false,
				'action'    => false,
				'action2'   => false,
			),
			wp_get_referer()
		);

		$action->redirect( $url );
	}

	/**
	 * Callback for the `cn_delete_log` hook which processes the delete action and then redirects back to the current admin page.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function delete() {

		$action = new self();
		$id     = Request\ID::input()->value();
		$type   = Request\Log_Type::input()->value();

		if ( $action->isValid( 'log_delete', $id ) ) {

			cnLog::delete( $id );

			cnMessage::set( 'success', __( 'The log has been deleted.', 'connections' ) );
		}

		$url = add_query_arg(
			array(
				'type' => ! empty( $type ) && '-1' !== $type ? $type : false,
			),
			wp_get_referer()
		);

		$action->redirect( $url );
	}

	/**
	 * Whether the current user has the required role capability and
	 * that the request nonce is valid.
	 *
	 * @since 10.4.33
	 *
	 * @param string      $action Nonce action name.
	 * @param null|string $item   Item name. Use when protecting multiple items on the same page.
	 *
	 * @return bool
	 */
	private function isValid( $action, $item = null ) {

		return current_user_can( 'install_plugins' ) &&
			   Request\Nonce::input( $action, $item )->isValid();
	}

	/**
	 * Redirect back to admin page.
	 *
	 * @since 10.4.33
	 *
	 * @param string $url The admin URL to redirect to.
	 */
	private function redirect( $url ) {

		wp_safe_redirect( $url );
		exit();
	}
}
