<?php

namespace Connections_Directory\Taxonomy\Term\Admin;

use cnMessage;
use cnMeta;
use cnTerm;
use Connections_Directory\Request;
use Connections_Directory\Taxonomy;
use Connections_Directory\Taxonomy\Registry;
use Connections_Directory\Utility\_validate;
use function Connections_Directory\Taxonomy\Term\Admin\Deprecated_Actions\addTerm;
use function Connections_Directory\Taxonomy\Term\Admin\Deprecated_Actions\bulkTerm;
use function Connections_Directory\Taxonomy\Term\Admin\Deprecated_Actions\deleteTerm;
use function Connections_Directory\Taxonomy\Term\Admin\Deprecated_Actions\updateTerm;

/**
 * Class Actions
 *
 * @package Connections_Directory\Taxonomy\Admin
 */
final class Actions {

	/**
	 * Callback for the `cn_add-term` action.
	 *
	 * Add a term.
	 *
	 * @internal
	 * @since 8.6.12
	 */
	public static function addTerm() {

		$term     = Request\Term::input()->value();
		$taxonomy = Registry::get()->getTaxonomy( $term['taxonomy'] );

		self::doLegacyTermActions( 'add', $term['taxonomy'] );
		self::invalidTaxonomyRedirect( $taxonomy, wp_get_raw_referer() );

		if ( current_user_can( $taxonomy->getCapabilities()->edit_terms ) ) {

			_validate::adminReferer( 'add-term' );

			// `$_REQUEST` data is escaped in `cnTerm::insert()` utilizing `sanitize_term()`.
			$result = cnTerm::insert(
				$term['term-name'],
				$taxonomy->getSlug(),
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

		} else {

			$message = sprintf(
			/* translators: Taxonomy name. */
				__( 'You are not authorized to add %s. Please contact the admin if you received this message in error.', 'connections' ),
				strtolower( $taxonomy->getLabels()->name )
			);

			cnMessage::set( 'error', $message );
		}

		wp_safe_redirect( wp_get_raw_referer() );

		exit();
	}

	/**
	 * Callback for the `cn_update-term` action.
	 *
	 * Update a term.
	 *
	 * @internal
	 * @since 8.6.12
	 */
	public static function updateTerm() {

		$term     = Request\Term::input()->value();
		$taxonomy = Registry::get()->getTaxonomy( $term['taxonomy'] );

		self::doLegacyTermActions( 'update', $term['taxonomy'] );
		self::invalidTaxonomyRedirect( $taxonomy, wp_get_raw_referer() );

		if ( current_user_can( $taxonomy->getCapabilities()->edit_terms ) ) {

			_validate::adminReferer( 'update-term', $term['term-id'] );

			// Make sure the term isn't being set to itself as a parent.
			if ( $term['term-id'] === $term['term-parent'] ) {

				cnMessage::set( 'error', 'category_self_parent' );
			}

			// `$_REQUEST` data is escaped in `cnTerm::update()` utilizing `sanitize_term()`.
			$result = cnTerm::update(
				$term['term-id'],
				$taxonomy->getSlug(),
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

				$referer = remove_query_arg( array( 'cn-action', 'id', '_wpnonce' ), wp_get_raw_referer() );
				$goBack  = '<a href="' . esc_url( wp_validate_redirect( esc_url_raw( $referer ), admin_url( "admin.php?page=connections_manage_{$taxonomy->getSlug()}_terms" ) ) ) . '">' . esc_html( $taxonomy->getLabels()->back_to_items ) . '</a>';
				$message = sprintf(
					/* translators: Taxonomy name. */
					_x( '%s has been updated.', 'taxonomy term has been updated', 'connections' ),
					$taxonomy->getLabels()->singular_name
				);

				cnMessage::set( 'success', "{$message}<br>{$goBack}" );
			}

		} else {

			$message = sprintf(
				/* translators: Taxonomy name. */
				__( 'You are not authorized to edit %s. Please contact the admin if you received this message in error.', 'connections' ),
				strtolower( $taxonomy->getLabels()->name )
			);

			cnMessage::set( 'error', $message );
		}

		wp_safe_redirect( wp_get_raw_referer() );

		exit();
	}

	/**
	 * Callback for the `cn_delete-term` action.
	 *
	 * @internal
	 * @since 8.6.12
	 */
	public static function deleteTerm() {

		$slug     = Request\Taxonomy::from( INPUT_GET )->value();
		$taxonomy = Registry::get()->getTaxonomy( $slug );

		self::doLegacyTermActions( 'delete', $slug );
		self::invalidTaxonomyRedirect( $taxonomy, wp_get_raw_referer() );

		$id = Request\ID::input()->value();

		if ( current_user_can( $taxonomy->getCapabilities()->delete_terms, $id ) ) {

			_validate::adminReferer( 'term_delete', $id );

			$result = cnTerm::delete( $id, $taxonomy->getSlug() );

			if ( is_wp_error( $result ) ) {

				cnMessage::set( 'error', $result->get_error_message() );

			} else {

				cnMessage::set( 'success', 'term_deleted' );
			}

		} else {

			$message = sprintf(
				/* translators: Taxonomy name. */
				__( 'You are not authorized to delete %s. Please contact the admin if you received this message in error.', 'connections' ),
				strtolower( $taxonomy->getLabels()->name )
			);

			cnMessage::set( 'error', $message );
		}

		wp_safe_redirect( wp_get_raw_referer() );

		exit();
	}

	/**
	 * Callback for the `cn_bulk-term-action` action.
	 *
	 * Bulk term actions.
	 *
	 * @internal
	 * @since 8.6.12
	 */
	public static function bulkTerm() {

		$request  = Request\List_Table_Taxonomy::input()->value();
		$action   = $request['action'];
		$slug     = $request['taxonomy'];
		$taxonomy = Registry::get()->getTaxonomy( $slug );
		$url      = wp_get_raw_referer();

		self::doLegacyTermActions( 'bulk', $slug );
		self::invalidTaxonomyRedirect( $taxonomy, wp_get_raw_referer() );

		if ( current_user_can( $taxonomy->getCapabilities()->delete_terms ) ) {

			check_admin_referer( 'bulk-terms' );

			switch ( $action ) {

				case 'delete':
					foreach ( $request['selected'] as $id ) {

						$result = cnTerm::delete( $id, $taxonomy->getSlug() );

						if ( is_wp_error( $result ) ) {

							cnMessage::set( 'error', $result->get_error_message() );

						} else {

							cnMessage::set( 'success', 'term_deleted' );
						}
					}

					break;

				default:
					do_action( "bulk_term_action-{$taxonomy->getSlug()}-{$action}" );
			}

			if ( 1 < $request['paged'] ) {

				$url = add_query_arg( array( 'paged' => $request['paged'] ), $url );
			}

		} else {

			$message = sprintf(
				/* translators: Taxonomy name. */
				__( 'You are not authorized to delete %s. Please contact the admin if you received this message in error.', 'connections' ),
				strtolower( $taxonomy->getLabels()->name )
			);

			cnMessage::set( 'error', $message );
		}

		wp_safe_redirect( $url );

		exit();
	}

	/**
	 * Callback for the `cn_delete_term` action.
	 *
	 * Delete the term meta when a term is deleted.
	 *
	 * @internal
	 * @since 8.2
	 *
	 * @param int    $term          Term ID.
	 * @param int    $tt_id         Term taxonomy ID.
	 * @param string $taxonomy      Taxonomy slug.
	 * @param mixed  $deleted_term  Copy of the already-deleted term, in the form specified
	 *                              by the parent function. WP_Error otherwise.
	 */
	public static function deleteTermMeta( $term, $tt_id, $taxonomy, $deleted_term ) {

		if ( ! is_wp_error( $deleted_term ) ) {

			$meta = cnMeta::get( 'term', $term );

			if ( ! empty( $meta ) ) {

				foreach ( $meta as $key => $value ) {

					cnMeta::delete( 'term', $term, $key );
				}
			}
		}
	}

	/**
	 * @since 10.2
	 *
	 * @param string $action
	 * @param string $taxonomy The taxonomy slug.
	 */
	private static function doLegacyTermActions( $action, $taxonomy ) {

		$legacy = array(
			'affiliate',
			'certification',
			'degree',
			'discipline',
			'facility',
			'partnership',
			'school',
		);

		$taxonomies = array_keys( Registry::get()->getTaxonomies() );

		// Ensure the taxonomy is not registered with the API before proceeding.
		if ( in_array( $taxonomy, $taxonomies ) ) {

			return;
		}

		// Ensure the taxonomy is one of the defined "legacy" taxonomies before proceeding.
		if ( ! in_array( $taxonomy, $legacy ) ) {

			return;
		}

		require_once 'Deprecated_Term_Actions.php';

		switch ( $action ) {

			case 'add':
				addTerm();
				break;

			case 'bulk':
				bulkTerm();
				break;

			case 'delete':
				deleteTerm();
				break;

			case 'update':
				updateTerm();
				break;
		}
	}

	/**
	 * Do safe redirect if `$taxonomy` is not an instance of Taxonomy.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param Taxonomy $taxonomy Instance of the Taxonomy object.
	 * @param string   $redirect The redirect URL.
	 */
	private static function invalidTaxonomyRedirect( $taxonomy, $redirect ) {

		if ( ! $taxonomy instanceof Taxonomy ) {

			cnMessage::set( 'error', __( 'Invalid taxonomy.', 'connections' ) );

			wp_safe_redirect( $redirect );

			exit();
		}
	}
}
