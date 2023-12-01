<?php
/**
 * Add the "Edit Entry" node to the admin menu bar.
 *
 * @since 10.4.59
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Hook\Action
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Action;

use Connections_Directory\Request;
use Connections_Directory\Utility\_nonce;
use WP_Admin_Bar;

/**
 * Class Admin_Bar
 *
 * @package Connections_Directory\Hook\Action
 */
final class Admin_Bar {

	/**
	 * Add the entry actions to the admin bar
	 *
	 * @since 8.2
	 *
	 * @param WP_Admin_Bar $admin_bar The WP_Admin_Bar instance, passed by reference.
	 */
	public static function addEditEntry( WP_Admin_Bar $admin_bar ) {

		$request = Request::get();

		if ( $request->isSingle() ) {

			$entry = Connections_Directory()->retrieve->entry( $request->getVar( 'cn-entry-slug' ) );

			if ( is_object( $entry )
				 && ( current_user_can( 'connections_manage' ) && current_user_can( 'connections_view_menu' ) )
				 && ( current_user_can( 'connections_edit_entry_moderated' ) || current_user_can( 'connections_edit_entry' ) )
			) {

				$id  = $entry->id;
				$url = _nonce::url( "admin.php?page=connections_manage&cn-action=edit_entry&id={$id}", 'entry_edit', $id );

				$admin_bar->add_node(
					array(
						'parent' => false,
						'id'     => 'cn-edit-entry',
						'title'  => __( 'Edit Entry', 'connections' ),
						'href'   => admin_url( $url ),
						'meta'   => array(
							// 'class' => 'edit',
							'title' => __( 'Edit Entry', 'connections' ),
						),
					)
				);
			}
		}
	}
}
