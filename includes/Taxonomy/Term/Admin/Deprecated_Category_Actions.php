<?php

namespace Connections_Directory\Taxonomy\Category\Admin\Deprecated_Actions;

use cnCategory;
use cnFormatting;
use cnFormObjects;
use cnMessage;

/**
 * Callback for the `cn_add_category` action.
 *
 * Add a category.
 *
 * @internal
 * @since 0.7.7
 * @deprecated 10.2
 */
function addCategory() {

	$form = new cnFormObjects();

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( $form->getNonce( 'add_category' ), '_cn_wpnonce' );

		$category = new cnCategory();
		$format   = new cnFormatting();

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
 * Callback for the `cn_update_category` action.
 *
 * Update a category.
 *
 * @internal
 * @since 0.7.7
 * @deprecated 10.2
 */
function updateCategory() {

	$form = new cnFormObjects();

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( $form->getNonce( 'update_category' ), '_cn_wpnonce' );

		$category = new cnCategory();
		$format   = new cnFormatting();

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
 * Callback for the `cn_delete_category` action.
 *
 * Delete a category.
 *
 * @internal
 * @since 0.7.7
 * @deprecated 10.2
 */
function deleteCategory() {

	global $connections;

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		$id = esc_attr( $_GET['id'] );
		check_admin_referer( 'term_delete_' . $id );

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
 * Callback for the `cn_category_bulk_actions` action.
 *
 * Bulk category actions.
 *
 * @internal
 * @since 0.7.7
 * @deprecated 10.2
 */
function categoryManagement() {

	// Grab an instance of the Connections object.
	$instance = Connections_Directory();
	$action   = '';

	if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) {

		$action = $_REQUEST['action'];

	} elseif ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) {

		$action = $_REQUEST['action2'];
	}

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		switch ( $action ) {

			case 'delete':

				check_admin_referer( 'bulk-terms' );

				foreach ( (array) $_POST['category'] as $id ) {

					$result   = $instance->retrieve->category( absint( $id ) );
					$category = new cnCategory( $result );
					$category->delete();
				}

				break;

			default:

				do_action( "bulk_term_action-category-{$action}" );
		}

		$url = get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' );

		if ( isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ) {

			$page = absint( $_REQUEST['paged'] );

			$url = add_query_arg( array( 'paged' => $page ), $url );
		}

		wp_redirect( $url );

		exit();

	} else {

		cnMessage::set( 'error', 'capability_categories' );
	}
}

/**
 * Callback for the `cn_process_taxonomy-category` action.
 *
 * Add, update or delete the entry categories.
 *
 * @internal
 * @since 0.8
 * @deprecated 10.2 Use the `Connections_Directory/Attach/Taxonomy/{$taxonomySlug}` action hook.
 * @see \Connections_Directory\Taxonomy::attachTerms()
 *
 * @param string $action The action to being performed to an entry.
 * @param int    $id     The entry ID.
 */
function processEntryCategory( $action, $id ) {

	// Grab an instance of the Connections object.
	$instance = Connections_Directory();

	/*
	 * Save the entry category(ies). If none were checked, send an empty array
	 * which will add the entry to the default category.
	 */
	if ( isset( $_POST['entry_category'] ) && ! empty( $_POST['entry_category'] ) ) {

		$instance->term->setTermRelationships( $id, $_POST['entry_category'], 'category' );

	} else {

		$default = get_option( 'cn_default_category' );

		$instance->term->setTermRelationships( $id, $default, 'category' );
	}

}
