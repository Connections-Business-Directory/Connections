<?php

/**
 * The settings admin page.
 *
 * @package     Connections
 * @subpackage  The settings admin page.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function connectionsShowSettingsPage() {

	/*
	 * Check whether user can edit Settings
	 */
	if ( !current_user_can( 'connections_change_settings' ) ) {

		wp_die( '<p id="error-page" style="-moz-background-clip:border;
			-moz-border-radius:11px;
			background:#FFFFFF none repeat scroll 0 0;
			border:1px solid #DFDFDF;
			color:#333333;
			display:block;
			font-size:12px;
			line-height:18px;
			margin:25px auto 20px;
			padding:1em 2em;
			text-align:center;
			width:700px">' . __( 'You do not have sufficient permissions to access this page.', 'connections' ) . '</p>'
		);

	} else {

		/** @var connectionsLoad $connections */
		global $connections;

		cnSettingsAPI::form( $connections->pageHook->settings );
	}
}
