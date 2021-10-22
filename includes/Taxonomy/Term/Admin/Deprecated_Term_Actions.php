<?php
namespace Connections_Directory\Taxonomy\Term\Admin\Deprecated_Actions;

use cnAdminActions;
use cnFormObjects;
use cnMessage;
use cnTerm;

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

		check_admin_referer( $form->getNonce( 'add-term' ), '_cn_wpnonce' );

		$result = cnTerm::insert(
			$_POST['term_name'],
			$_POST['taxonomy'],
			array(
				'slug'        => $_POST['term_slug'],
				'parent'      => $_POST['term_parent'],
				'description' => $_POST['term_description'],
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
 * Update a category.
 *
 * @internal
 * @since 8.6.12
 * @deprecated 10.2
 */
function updateTerm() {

	$form = new cnFormObjects();

	/*
	 * Check whether user can edit Settings
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( $form->getNonce( 'update-term' ), '_cn_wpnonce' );

		// Make sure the category isn't being set to itself as a parent.
		if ( $_POST['term_id'] === $_POST['term_parent'] ) {

			cnMessage::set( 'error', 'category_self_parent' );
		}

		remove_filter( 'pre_term_description', 'wp_filter_kses' );

		$result = cnTerm::update(
			$_POST['term_id'],
			$_POST['taxonomy'],
			array(
				'name'        => $_POST['term_name'],
				'slug'        => $_POST['term_slug'],
				'parent'      => $_POST['term_parent'],
				'description' => $_POST['term_description'],
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

	// Use legacy action callback when deleting categories, for now.
	if ( 'category' == $_REQUEST['taxonomy'] ) {

		cnAdminActions::deleteCategory();
	}

	/*
	 * Check whether user can edit terms.
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		$id = esc_attr( $_REQUEST['id'] );
		check_admin_referer( 'term_delete_' . $id );

		$result = cnTerm::delete( $id, $_REQUEST['taxonomy'] );

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

	$action   = '';

	if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) {

		$action = $_REQUEST['action'];

	} elseif ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) {

		$action = $_REQUEST['action2'];
	}

	/*
	 * Check whether user can edit terms.
	 */
	if ( current_user_can( 'connections_edit_categories' ) ) {

		check_admin_referer( 'bulk-terms' );

		switch ( $action ) {

			case 'delete':

				foreach ( (array) $_REQUEST[ $_REQUEST['taxonomy'] ] as $id ) {

					$result = cnTerm::delete( $id, $_REQUEST['taxonomy'] );

					if ( is_wp_error( $result ) ) {

						cnMessage::set( 'error', $result->get_error_message() );

					} else {

						cnMessage::set( 'success', 'term_deleted' );
					}
				}

				break;

			default:

				do_action( "bulk_term_action-{$_REQUEST['taxonomy']}-{$action}" );
		}

		$url = wp_get_raw_referer();

		if ( isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ) {

			$page = absint( $_REQUEST['paged'] );

			$url = add_query_arg( array( 'paged' => $page ) , $url );
		}

		wp_redirect( $url );

		exit();

	} else {

		cnMessage::set( 'error', 'capability_categories' );
	}

}
