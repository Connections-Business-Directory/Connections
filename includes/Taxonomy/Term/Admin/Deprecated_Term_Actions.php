<?php
namespace Connections_Directory\Taxonomy\Term\Admin\Deprecated_Actions;

use cnAdminActions;
use cnFormObjects;
use cnMessage;
use cnTerm;
use Connections_Directory\Request;
use Connections_Directory\Utility\_validate;

/**
 * Callback for the `cn_add-term` action.
 *
 * Add a term.
 *
 * @internal
 * @since  8.6.12
 * @deprecated 10.2
 */
function addTerm() {

	/*
	 * Check whether user can edit terms.
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		$form = new cnFormObjects();
		$term = Request\Term::input()->value();

		check_admin_referer( $form->getNonce( 'add-term' ), '_cn_wpnonce' );

		// `$_POST` data is escaped in `cnTerm::insert()` utilizing `sanitize_term()`.
		$result = cnTerm::insert(
			$term['term-name'],
			$term['taxonomy'],
			array(
				'slug'        => $term['term-slug'],
				'parent'      => $term['term-parent'],
				'description' => $term['term-description'],
			)
		);

		if ( is_wp_error( $result ) ) {

			cnMessage::set( 'error', $result->get_error_message() );

		} else {

			cnMessage::set( 'success', 'term_added' );
		}

		wp_safe_redirect( wp_get_raw_referer() );

		exit();

	} else {

		cnMessage::set( 'error', 'capability_categories' );
	}
}

/**
 * Callback for the `cn_update-term` action.
 *
 * Update a term.
 *
 * @internal
 * @since 8.6.12
 * @deprecated 10.2
 */
function updateTerm() {

	$form = new cnFormObjects();
	$term = Request\Term::input()->value();

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( $form->getNonce( 'update-term' ), '_cn_wpnonce' );

		// Make sure the category isn't being set to itself as a parent.
		if ( $term['term-id'] === $term['term-parent'] ) {

			cnMessage::set( 'error', 'category_self_parent' );
		}

		remove_filter( 'pre_term_description', 'wp_filter_kses' );

		// `$_POST` data is escaped in `cnTerm::update()` utilizing `sanitize_term()`.
		$result = cnTerm::update(
			$term['term-id'],
			$term['taxonomy'],
			array(
				'name'        => $term['term-name'],
				'slug'        => $term['term-slug'],
				'parent'      => $term['term-parent'],
				'description' => $term['term-description'],
			)
		);

		if ( is_wp_error( $result ) ) {

			cnMessage::set( 'error', $result->get_error_message() );

		} else {

			cnMessage::set( 'success', 'term_updated' );
		}

		wp_safe_redirect( wp_get_raw_referer() );

		exit();

	} else {

		cnMessage::set( 'error', 'capability_categories' );
	}
}

/**
 * Callback for the `cn_delete-term` action.
 *
 * @internal
 * @since 8.6.12
 * @deprecated 10.2
 */
function deleteTerm() {

	$slug = Request\Taxonomy::from( INPUT_GET )->value();

	// Use legacy action callback when deleting categories, for now.
	if ( 'category' === $slug ) {

		cnAdminActions::deleteCategory();
	}

	/*
	 * Check whether user can edit terms.
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		$id = Request\ID::input()->value();

		_validate::adminReferer( 'term_delete', $id );

		$result = cnTerm::delete( $id, $slug );

		if ( is_wp_error( $result ) ) {

			cnMessage::set( 'error', $result->get_error_message() );

		} else {

			cnMessage::set( 'success', 'term_deleted' );
		}

		wp_safe_redirect( wp_get_raw_referer() );

		exit();

	} else {

		cnMessage::set( 'error', 'capability_categories' );
	}
}

/**
 * Callback for the `cn_bulk-term-action` action.
 *
 * Bulk term actions.
 *
 * @internal
 * @since  8.6.12
 * @deprecated 10.2
 */
function bulkTerm() {

	$request = Request\List_Table_Taxonomy::input()->value();
	$action  = $request['action'];
	$slug    = $request['taxonomy'];
	$url     = wp_get_raw_referer();

	/*
	 * Check whether user can edit terms.
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( 'bulk-terms' );

		switch ( $action ) {

			case 'delete':
				foreach ( $request['selected'] as $id ) {

					$result = cnTerm::delete( $id, $slug );

					if ( is_wp_error( $result ) ) {

						cnMessage::set( 'error', $result->get_error_message() );

					} else {

						cnMessage::set( 'success', 'term_deleted' );
					}
				}

				break;

			default:
				do_action( "bulk_term_action-{$slug}-{$action}" );
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
