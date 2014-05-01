<?php

/**
 * Functions for backwards compatibility with previous versions of Connections.
 *
 * @package     Connections
 * @subpackage  Functions for backwards compatibility.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add / Edit / Update / Copy an entry.
 *
 * @access private
 * @deprecated
 * @param  (array) $data
 * @param  (string) $action
 * @return (bool)
 */
function processEntry( $data, $action ) {

	// If copying/editing an entry, the entry data is loaded into the class
	// properties and then properties are overwritten by the POST data as needed.
	if ( isset( $_GET['id'] ) ) {

		$id = absint( $_GET['id'] );
	}


	switch ( $action ) {
		case 'add':
			return cnEntry_Action::add( $data );
			break;

		case 'copy':
			return cnEntry_Action::copy( $id, $data );
			break;

		case 'update':
			return cnEntry_Action::update( $id, $data );
			break;

	}
}
