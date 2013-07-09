<?php

/**
 * Template HTML Output.
 *
 * @package     Connections
 * @subpackage  Template HTML Output
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( is_admin() ) {
	if ( !isset( $form ) ) $form = new cnFormObjects();

	$editTokenURL = $form->tokenURL( 'admin.php?page=connections_manage&action=edit&id=' . $entry->getId(), 'entry_edit_' . $entry->getId() );

	if ( current_user_can( 'connections_edit_entry' ) ) {
		echo '<span class="cn-entry-name"><a class="row-title" title="Edit ' , $entry->getName() , '" href="' , $editTokenURL , '"> ' , $entry->getName() . '</a></span> <span class="cn-list-date">' , $entry->getFormattedTimeStamp( 'm/d/Y g:ia' ) , '</span>';
	}
	else {
		echo '<span class="cn-entry-name">' , $entry->getName() , '</span> <span class="cn-list-date">' , $entry->getFormattedTimeStamp( 'm/d/Y g:ia' ) , '</span>';
	}

}
?>