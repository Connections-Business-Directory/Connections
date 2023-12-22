<?php

namespace Connections_Directory\Taxonomy\Category\Admin\Deprecated_Actions;

use cnCategory;
use cnFormObjects;
use cnMessage;
use Connections_Directory\Request;
use Connections_Directory\Utility\_array;

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
	$term = Request\Term::input()->value();

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( $form->getNonce( 'add_category' ), '_cn_wpnonce' );

		$category = new cnCategory();

		// `$_POST` data is escaped in `cnTerm::insert()` utilizing `sanitize_term()`.
		$category->setName( $term['term-name'] );
		$category->setSlug( $term['term-slug'] );
		$category->setParent( $term['term-parent'] );
		$category->setDescription( $term['term-description'] );

		$category->save();

		wp_safe_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

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
	$term = Request\Term::input()->value();

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( $form->getNonce( 'update_category' ), '_cn_wpnonce' );

		$category = new cnCategory();

		// `$_POST` data is escaped in `cnTerm::update()` utilizing `sanitize_term()`.
		$category->setID( $term['term-id'] );
		$category->setName( $term['term-name'] );
		$category->setParent( $term['term-parent'] );
		$category->setSlug( $term['term-slug'] );
		$category->setDescription( $term['term-description'] );

		$category->update();

		wp_safe_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

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

		$id = Request\ID::input()->value();
		check_admin_referer( "term_delete_{$id}" );

		$result   = $connections->retrieve->category( $id );
		$category = new cnCategory( $result );
		$category->delete();

		wp_safe_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

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

	$request = Request\List_Table_Taxonomy::input()->value();
	$action  = $request['action'];
	$url     = get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' );

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		switch ( $action ) {

			case 'delete':
				check_admin_referer( 'bulk-terms' );

				foreach ( $request['selected'] as $id ) {

					$result   = Connections_Directory()->retrieve->category( absint( $id ) );
					$category = new cnCategory( $result );
					$category->delete();
				}

				break;

			default:
				do_action( "bulk_term_action-category-{$action}" );
		}

		if ( 1 < $request['paged'] ) {

			$url = add_query_arg( array( 'paged' => $request['paged'] ), $url );
		}

		wp_safe_redirect( $url );

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

	$terms = _array::get( $_POST, 'entry_category', array() );

	/*
	 * Save the entry category(ies). If none were checked, send an empty array
	 * which will add the entry to the default category.
	 */
	if ( 0 < count( $terms ) ) {

		$terms = array_map( 'absint', $terms );

		Connections_Directory()->term->setTermRelationships( $id, $terms, 'category' );

	} else {

		$default = get_option( 'cn_default_category' );

		Connections_Directory()->term->setTermRelationships( $id, $default, 'category' );
	}
}
