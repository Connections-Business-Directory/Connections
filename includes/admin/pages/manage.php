<?php
/**
 * The manage admin page.
 *
 * @package     Connections
 * @subpackage  The manage admin page.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Form\Field;
use Connections_Directory\Request;
use Connections_Directory\Taxonomy;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_nonce;
use Connections_Directory\Utility\_validate;

function connectionsShowViewPage( $action = null ) {

	// Grab an instance of the Connections object.
	$instance = Connections_Directory();

	echo '<div class="wrap">';

	switch ( $action ) {

		case 'add_entry':
			echo '<h1>Connections : ', esc_html__( 'Add Entry', 'connections' ), '</h1>';

			/*
			 * Check whether current user can add an entry.
			 */
			if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

				$form  = new cnFormObjects();
				$entry = new cnOutput();

				$attr = array(
					'id'      => 'cn-form',
					'method'  => 'post',
					'enctype' => 'multipart/form-data',
				);

				$form->open( $attr );

				$field = array(
					'id'       => 'metabox-name',
					'title'    => esc_html__( 'Name', 'connections' ),
					'context'  => 'normal',
					'priority' => 'high',
					'callback' => array( 'cnEntryMetabox', 'name' ),
				);

				cnMetabox_Render::add( $instance->pageHook->add, $field );

				echo '<div id="poststuff">';

					echo '<div id="post-body" class="metabox-holder columns-' . ( 1 == get_current_screen()->get_columns() ? '1' : '2' ) . '">';

						wp_nonce_field( 'cn-manage-metaboxes' );
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

						_nonce::field( 'add_entry', null, null, false );

						do_action( 'cn_admin_form_add_entry_before', $entry, $form );

						echo '<div id="postbox-container-1" class="postbox-container">';

						do_meta_boxes( $instance->pageHook->add, 'side', $entry );

						echo '</div> <!-- #postbox-container-1 -->';

						echo '<div id="postbox-container-2" class="postbox-container">';
							echo '<div id="normal-sortables" class="meta-box-sortables ui-sortable">';
								do_meta_boxes( $instance->pageHook->add, 'normal', $entry );
							echo '</div> <!-- #normal-sortables -->';
						echo '</div> <!-- #postbox-container-2 -->';

						do_action( 'cn_admin_form_add_entry_after', $entry, $form );

					echo '</div> <!-- #post-body -->';

					echo '<br class="clear">';

				echo '</div> <!-- #poststuff -->';

				echo '</form>';

				unset( $entry );

			} else {

				cnMessage::render( 'error', esc_html__( 'You are not authorized to add entries. Please contact the admin if you received this message in error.', 'connections' ) );
			}

			break;

		case 'copy_entry':
			echo '<h1>Connections : ', esc_html__( 'Copy Entry', 'connections' ), '</h1>';

			/*
			 * Check whether current user can add an entry.
			 */
			if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

				$id = Request\ID::input()->value();

				_validate::adminReferer( 'entry_copy', $id );

				$form  = new cnFormObjects();
				$entry = new cnOutput( $instance->retrieve->entry( $id ) );

				$resetID = function ( $item ) {
					cnArray::set( $item, 'id', 0 );
					return $item;
				};

				$resetUID = function ( $item ) {
					cnArray::set( $item, 'uid', 0 );
					return $item;
				};

				add_filter( 'cn_address-pre_setup', $resetID );
				add_filter( 'cn_phone-pre_setup', $resetID );
				add_filter( 'cn_email-pre_setup', $resetID );
				add_filter( 'cn_im-pre_setup', $resetUID );
				add_filter( 'cn_link-pre_setup', $resetID );
				add_filter( 'cn_date-pre_setup', $resetID );
				add_filter( 'cn_social_network-pre_setup', $resetID );

				$attr = array(
					'id'      => 'cn-form',
					'method'  => 'post',
					'enctype' => 'multipart/form-data',
				);

				$form->open( $attr );

				$field = array(
					'id'       => 'metabox-name',
					'title'    => esc_html__( 'Name', 'connections' ),
					'context'  => 'normal',
					'priority' => 'high',
					'callback' => array( 'cnEntryMetabox', 'name' ),
				);

				cnMetabox_Render::add( $instance->pageHook->manage, $field );

				echo '<div id="poststuff">';

					echo '<div id="post-body" class="metabox-holder columns-' . ( 1 == get_current_screen()->get_columns() ? '1' : '2' ) . '">';

						wp_nonce_field( 'cn-manage-metaboxes' );
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

						_nonce::field( 'add_entry', null, null, false );

						do_action( 'cn_admin_form_copy_entry_before', $entry, $form );

						echo '<div id="postbox-container-1" class="postbox-container">';

						do_meta_boxes( $instance->pageHook->manage, 'side', $entry );

						echo '</div> <!-- #postbox-container-1 -->';

						echo '<div id="postbox-container-2" class="postbox-container">';
							echo '<div id="normal-sortables" class="meta-box-sortables ui-sortable">';
								do_meta_boxes( $instance->pageHook->manage, 'normal', $entry );
							echo '</div> <!-- #normal-sortables -->';
						echo '</div> <!-- #postbox-container-2 -->';

						do_action( 'cn_admin_form_copy_entry_after', $entry, $form );

					echo '</div> <!-- #post-body -->';

					echo '<br class="clear">';

				echo '</div> <!-- #poststuff -->';

				echo '</form>';

				unset( $entry );

			} else {

				cnMessage::render( 'error', esc_html__( 'You are not authorized to add entries. Please contact the admin if you received this message in error.', 'connections' ) );
			}

			break;

		case 'edit_entry':
			echo '<h1>Connections : ', esc_html__( 'Edit Entry', 'connections' ), '</h1>';

			/*
			 * Check whether the current user can edit entries.
			 */
			if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

				$id = Request\ID::input()->value();

				_validate::adminReferer( 'entry_edit', $id );

				$form  = new cnFormObjects();
				$entry = new cnOutput( $instance->retrieve->entry( $id ) );

				$attr = array(
					'id'      => 'cn-form',
					'action'  => 'admin.php?connections_process=true&process=manage&action=update&id=' . $id,
					'method'  => 'post',
					'enctype' => 'multipart/form-data',
				);

				$form->open( $attr );

				$field = array(
					'id'       => 'metabox-name',
					'title'    => esc_html__( 'Name', 'connections' ),
					'context'  => 'normal',
					'priority' => 'high',
					'callback' => array( 'cnEntryMetabox', 'name' ),
				);

				cnMetabox_Render::add( $instance->pageHook->manage, $field );

				echo '<div id="poststuff">';

					echo '<div id="post-body" class="metabox-holder columns-' . ( 1 == get_current_screen()->get_columns() ? '1' : '2' ) . '">';

						wp_nonce_field( 'cn-manage-metaboxes' );
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

						_nonce::field( 'update_entry', $entry->getId(), null, false );

						do_action( 'cn_admin_form_edit_entry_before', $entry, $form );

						echo '<div id="postbox-container-1" class="postbox-container">';

						do_meta_boxes( $instance->pageHook->manage, 'side', $entry );

						echo '</div> <!-- #postbox-container-1 -->';

						echo '<div id="postbox-container-2" class="postbox-container">';
							echo '<div id="normal-sortables" class="meta-box-sortables ui-sortable">';
								do_meta_boxes( $instance->pageHook->manage, 'normal', $entry );
							echo '</div> <!-- #normal-sortables -->';
						echo '</div> <!-- #postbox-container-2 -->';

						do_action( 'cn_admin_form_edit_entry_after', $entry, $form );

					echo '</div> <!-- #post-body -->';

					echo '<br class="clear">';

				echo '</div> <!-- #poststuff -->';

				echo '</form>';

				unset( $entry );

			} else {

				cnMessage::render( 'error', esc_html__( 'You are not authorized to edit entries. Please contact the admin if you received this message in error.', 'connections' ) );
			}

			break;

		default:
			echo '<h1>Connections : ', esc_html__( 'Manage', 'connections' ), ' <a class="button add-new-h2" href="admin.php?page=connections_add">', esc_html__( 'Add New', 'connections' ), '</a></h1>';

			/*
			 * Check whether user can view the entry list
			 */
			if ( current_user_can( 'connections_manage' ) ) {

				$filters = array(
					'category'   => $instance->user->getScreenOption( 'manage', 'filter.category', '' ),
					'status'     => $instance->user->getScreenOption( 'manage', 'filter.status', 'approved' ),
					'type'       => $instance->user->getScreenOption( 'manage', 'filter.type', '' ),
					'visibility' => $instance->user->getScreenOption( 'manage', 'filter.visibility', '' ),
				);

				if ( ! in_array( $filters['visibility'], $instance->user->canView() ) ) {

					_array::set( $filters, 'visibility', '' );
				}

				$page = $instance->user->getScreenOption( 'manage', 'pagination' );
				$page = wp_parse_args(
					$page,
					array(
						'current' => 1,
						'limit'   => 50,
					)
				);

				_array::set( $page, 'offset', --$page['current'] * $page['limit'] );

				$retrieveAttr['list_type'] = $filters['type'];
				$retrieveAttr['category']  = $filters['category'];

				$retrieveAttr['visibility'] = $filters['visibility'];
				$retrieveAttr['status']     = $filters['status'];

				$retrieveAttr['limit']  = $page['limit'];
				$retrieveAttr['offset'] = $page['offset'];

				$retrieveAttr['char'] = Request\Entry_Initial_Character::input()->value();

				$retrieveAttr['search_terms'] = Request\Search::input()->value();

				$results = $instance->retrieve->entries( $retrieveAttr );
				?>

				<?php if ( current_user_can( 'connections_edit_entry' ) ) { ?>

				<ul class="subsubsub">

					<?php

					$statuses = array(
						'all'      => esc_html__( 'All', 'connections' ),
						'approved' => esc_html__( 'Approved', 'connections' ),
						'pending'  => esc_html__( 'Moderate', 'connections' ),
					);

					foreach ( $statuses as $key => $status ) {

						$subsubsub[] = sprintf(
							'<li><a%1$shref="%2$s">%3$s</a> <span class="count">(%4$d)</span></li>',
							$filters['status'] == $key ? ' class="current" ' : ' ',
							esc_url(
								_nonce::url(
									add_query_arg(
										array(
											'page'      => 'connections_manage',
											'cn-action' => 'filter',
											'status'    => $key,
										)
									),
									'filter'
								)
							),
							$status,
							cnRetrieve::recordCount( array( 'status' => $key ) )
						);
					}

					echo wp_kses(
						implode( ' | ', $subsubsub ),
						array(
							'a'    => array(
								'class' => true,
								'href'  => true,
							),
							'li'   => array(),
							'span' => array(),
						)
					);

					?>

				</ul>

				<?php } ?>

				<form method="get">

					<p class="search-box">
						<label class="screen-reader-text" for="entry-search-input"><?php _e( 'Search Entries', 'connections' ); ?>:</label>
						<input type="search" id="entry-search-input" name="s" value="<?php echo esc_attr( Request\Search::input()->value() ); ?>" />
						<?php submit_button( esc_attr__( 'Search Entries', 'connections' ), '', '', false, array( 'id' => 'search-submit' ) ); ?>
					</p>

					<?php _nonce::field( 'cn_manage_actions' ); ?>

					<input type="hidden" name="cn-action" value="manage_actions"/>

					<div class="tablenav">

						<div class="alignleft actions">
							<?php

							Field\Term_Select::create()
											 ->setId( 'category' )
											 ->addClass( 'postform' )
											 ->setName( 'category' )
											 ->setValue( $filters['category'] )
											 ->setFieldOptions(
												 array(
													 'show_option_all' => esc_html__( 'Show All Categories', 'connections' ),
													 'hide_empty'      => false,
													 'hierarchical'    => true,
													 'show_count'      => false,
													 'orderby'         => 'name',
													 'value_field'     => 'term_id',
												 )
											 )
											 ->addLabel(
												 Field\Label::create()
															->setFor( 'category' )
															->addClass( 'screen-reader-text' )
															->text(
																Taxonomy\Registry::get()
																				 ->getTaxonomy( 'category' )
																				 ->getLabels()->filter_by_item
															),
												 'before'
											 )
											 ->render();

							Field\Select::create()
										->setId( 'cn-entry_type' )
										->setName( 'type' )
										->createOptionsFromArray(
											array(
												array(
													'label' => esc_html__( 'Show All Entries', 'connections' ),
													'value' => 'all',
												),
												array(
													'label' => esc_html__( 'Show Individuals', 'connections' ),
													'value' => 'individual',
												),
												array(
													'label' => esc_html__( 'Show Organizations', 'connections' ),
													'value' => 'organization',
												),
												array(
													'label' => esc_html__( 'Show Families', 'connections' ),
													'value' => 'family',
												),
											)
										)
										->setValue( $filters['type'] )
										->render();

							/*
							 * Builds the visibility select list base on current user capabilities.
							 */
							$visibilitySelect = array(
								array(
									'label' => esc_html__( 'Show All', 'connections' ),
									'value' => 'all',
								),
							);

							if ( current_user_can( 'connections_view_public' ) || $instance->options->getAllowPublic() ) {

								$visibilitySelect[] = array(
									'label' => esc_html__( 'Show Public', 'connections' ),
									'value' => 'public',
								);
							}

							if ( current_user_can( 'connections_view_private' ) ) {

								$visibilitySelect[] = array(
									'label' => esc_html__( 'Show Private', 'connections' ),
									'value' => 'private',
								);
							}

							if ( current_user_can( 'connections_view_unlisted' ) ) {

								$visibilitySelect[] = array(
									'label' => esc_html__( 'Show Unlisted', 'connections' ),
									'value' => 'unlisted',
								);
							}

							Field\Select::create()
										->setId( 'cn-visibility_type' )
										->setName( 'visibility' )
										->createOptionsFromArray( $visibilitySelect )
										->setValue( $filters['visibility'] )
										->render();

							submit_button( esc_html__( 'Filter', 'connections' ), '', 'filter', false, array( 'id' => 'entry-filters' ) );
							?>
						</div>

						<div class="tablenav-pages">
							<?php

							/* translators: Number of items. */
							echo '<span class="displaying-num">' . sprintf( esc_html__( 'Displaying %1$d of %2$d entries.', 'connections' ), absint( $instance->resultCount ), absint( $instance->resultCountNoLimit ) ) . '</span>';

							/*
							 * // START --> Pagination
							 *
							 * Grab the pagination data again in case a filter reset the values
							 * or the user input an invalid number which the retrieve query would have reset.
							 */
							$page = $instance->currentUser->getScreenOption( 'manage', 'pagination' );
							$page = wp_parse_args(
								$page,
								array(
									'current' => 1,
									'limit'   => 50,
								)
							);

							$pageCount = ceil( $instance->resultCountNoLimit / $page['limit'] );

							if ( $pageCount > 1 ) {

								$pageDisabled   = array();
								$pageFilterURL  = array();
								$pageValue      = array();
								$currentPageURL = add_query_arg(
									array(
										'page'      => false,
										'cn-action' => 'filter',
										// To support quote characters, first they are decoded from &quot; entities, then URL encoded.
										's'         => rawurlencode( htmlspecialchars_decode( Request\Search::input()->value() ) ),
									)
								);

								$pageValue['first_page']    = 1;
								$pageValue['previous_page'] = ( $page['current'] - 1 >= 1 ) ? $page['current'] - 1 : 1;
								$pageValue['next_page']     = ( $page['current'] + 1 <= $pageCount ) ? $page['current'] + 1 : $pageCount;
								$pageValue['last_page']     = $pageCount;

								$pageDisabled['first_page']    = ( $page['current'] > 1 ) ? '' : ' disabled';
								$pageDisabled['previous_page'] = ( $page['current'] - 1 >= 1 ) ? '' : ' disabled';
								$pageDisabled['next_page']     = ( $page['current'] + 1 <= $pageCount ) ? '' : ' disabled';
								$pageDisabled['last_page']     = ( $page['current'] < $pageCount ) ? '' : ' disabled';

								/*
								 * Generate the page link token URL.
								 */
								$pageFilterURL['first_page'] = esc_url(
									_nonce::url(
										add_query_arg( array( 'pg' => $pageValue['first_page'] ), $currentPageURL ),
										'filter'
									)
								);

								$pageFilterURL['previous_page'] = esc_url(
									_nonce::url(
										add_query_arg( array( 'pg' => $pageValue['previous_page'] ), $currentPageURL ),
										'filter'
									)
								);

								$pageFilterURL['next_page'] = esc_url(
									_nonce::url(
										add_query_arg( array( 'pg' => $pageValue['next_page'] ), $currentPageURL ),
										'filter'
									)
								);

								$pageFilterURL['last_page'] = esc_url(
									_nonce::url(
										add_query_arg( array( 'pg' => $pageValue['last_page'] ), $currentPageURL ),
										'filter'
									)
								);

								echo '<span class="page-navigation" id="page-input">';

								echo '<a href="' . esc_url( $pageFilterURL['first_page'] ) . '" title="' . esc_attr__( 'Go to the first page.', 'connections' ) . '" class="first-page button', esc_attr( $pageDisabled['first_page'] ), '">&laquo;</a> ';
								echo '<a href="' . esc_url( $pageFilterURL['previous_page'] ) . '" title="' . esc_attr__( 'Go to the previous page.', 'connections' ) . '" class="prev-page button', esc_attr( $pageDisabled['previous_page'] ), '">&lsaquo;</a> ';

								echo '<span class="paging-input"><input type="text" size="2" value="' . esc_attr( $page['current'] ) . '" name="pg" title="' . esc_attr__( 'Current page', 'connections' ) . '" class="current-page"> ' . esc_html__( 'of', 'connections' ) . ' <span class="total-pages">' . absint( $pageCount ) . '</span></span> ';

								echo '<a href="' . esc_url( $pageFilterURL['next_page'] ) . '" title="' . esc_attr__( 'Go to the next page.', 'connections' ) . '" class="next-page button', esc_attr( $pageDisabled['next_page'] ), '">&rsaquo;</a> ';
								echo '<a href="' . esc_url( $pageFilterURL['last_page'] ) . '" title="' . esc_attr__( 'Go to the last page.', 'connections' ) . '" class="last-page button', esc_attr( $pageDisabled['last_page'] ), '">&raquo;</a>';

								echo '</span>';
							}

							/*
							 * // END --> Pagination
							 */
							?>
						</div>

					</div>
					<div class="clear"></div>
					<div class="tablenav" style="height: auto;">

						<?php

						if ( current_user_can( 'connections_edit_entry' )
							 || current_user_can( 'connections_edit_entry_moderated' )
							 || current_user_can( 'connections_delete_entry' )
						) {
							echo '<div class="alignleft actions bulkactions">';

							$bulkActions = array();

							$bulkActions[] = array(
								'label' => __( 'Bulk Actions', 'connections' ),
								'value' => '-1',
							);

							if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

								$bulkActions[] = array(
									'label' => __( 'Approve', 'connections' ),
									'value' => 'approve',
								);

								$bulkActions[] = array(
									'label' => __( 'Unapprove', 'connections' ),
									'value' => 'unapprove',
								);

								$bulkActions[] = array(
									'label' => __( 'Set Public', 'connections' ),
									'value' => 'public',
								);

								$bulkActions[] = array(
									'label' => __( 'Set Private', 'connections' ),
									'value' => 'private',
								);

								$bulkActions[] = array(
									'label' => __( 'Set Unlisted', 'connections' ),
									'value' => 'unlisted',
								);
							}

							if ( current_user_can( 'connections_delete_entry' ) ) {

								$bulkActions[] = array(
									'label' => __( 'Delete', 'connections' ),
									'value' => 'delete',
								);
							}

							Field\Select::create()
										->setId( 'bulk-action-selector' )
										->setName( 'action' )
										->createOptionsFromArray( $bulkActions )
										->setValue( '-1' )
										->render();

							submit_button( esc_attr__( 'Apply', 'connections' ), 'action', '', false, array( 'id' => 'doaction' ) );
							echo '</div>';
						}
						?>

						<div class="tablenav-pages" style="height: auto; max-width: 75%; text-align: right;">
							<?php

							/*
							 * Display the character filter control.
							 */
							echo '<span class="displaying-num">', esc_html__( 'Filter by character:', 'connections' ), '</span>';
							cnTemplatePart::index(
								array(
									'status'     => $filters['status'],
									'visibility' => $filters['visibility'],
									'tag'        => 'span',
								)
							);
							cnTemplatePart::currentCharacter();
							?>
						</div>
					</div>
					<div class="clear"></div>

					<table class="widefat connections">
						<thead>
						<tr>
							<td class="manage-column column-cb check-column" id="cb"><input type="checkbox"/></td>
							<th class="col" style="width:10%;"></th>
							<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
							<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
							<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td class="manage-column column-cb check-column"><input type="checkbox"/></td>
							<th class="col" style="width:10%;"></th>
							<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
							<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
							<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
						</tr>
						</tfoot>
						<tbody>

				<?php

				foreach ( $results as $row ) {
					/*
					 * @TODO: Use the Output class to show entry details.
					 */
					$entry = new cnOutput( $row );

					/*
					 * Generate the edit, copy and delete URLs with nonce tokens.
					 */
					$editNonceURL      = _nonce::url( 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $entry->getId(), 'entry_edit', $entry->getId() );
					$copyNonceURL      = _nonce::url( 'admin.php?page=connections_manage&cn-action=copy_entry&id=' . $entry->getId(), 'entry_copy', $entry->getId() );
					$deleteNonceURL    = _nonce::url( 'admin.php?cn-action=delete_entry&id=' . $entry->getId(), 'entry_delete', $entry->getId() );
					$approvedNonceURL  = _nonce::url( 'admin.php?cn-action=set_status&status=approved&id=' . $entry->getId(), 'entry_status', $entry->getId() );
					$unapproveNonceURL = _nonce::url( 'admin.php?cn-action=set_status&status=pending&id=' . $entry->getId(), 'entry_status', $entry->getId() );

					switch ( $entry->getStatus() ) {
						case 'pending':
							$statusClass = 'unapproved';
							break;

						case 'approved':
							$statusClass = 'approved';
							break;

						default:
							$statusClass = '';
							break;
					}

					echo '<tr id="row-', esc_attr( $entry->getId() ), '" class="' . _escape::classNames( array( 'parent-row', $statusClass ) ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo "<th class='check-column' scope='row'><input type='checkbox' value='" . esc_attr( $entry->getId() ) . "' name='id[]'/></th> \n";
					echo '<td>';
					$entry->getImage(
						array(
							'image'    => $instance->user->getScreenOption( 'manage', 'thumbnail', 'photo' ),
							'height'   => 54,
							'width'    => 80,
							'zc'       => 2,
							'fallback' => array(
								'type'   => 'block',
								'string' => esc_html__( 'No Image Available', 'connections' ),
							),
						)
					);
					echo '</td>';
					echo '<td  colspan="2">';

					echo '<div style="float:right"><a href="#top" title="Return to top."><img src="' . esc_url( CN_URL . 'assets/images/uparrow.gif' ) . '" /></a></div>';

					$name = $entry->getName( array( 'format' => '%last%, %first%' ) );

					echo '<strong>';

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

						echo '<a class="row-title" title="Edit ' . esc_attr( $name ) . '" href="' . esc_url( $editNonceURL ) . '">' . esc_html( $name ) . '</a>';

					} else {

						echo esc_html( $name );
					}

					/**
					 * @since 8.35
					 *
					 * @param array   $array An array of entry states.
					 * @param cnEntry $entry
					 */
					$entryStates = apply_filters( 'cn_display_entry_states', array(), $entry );

					if ( is_array( $entryStates ) && ! empty( $entryStates ) ) {

						echo ' &mdash; ';

						foreach ( $entryStates as $entryState ) {

							echo '<span class="post-state">' . esc_html( $entryState ) . '</span>';
						}
					}

					echo '</strong>';

					echo '<div class="row-actions">';

					$fullName       = $entry->getName( array( 'format' => '%first% %middle% %last%' ), 'display' );
					$rowActions     = array();
					$rowEditActions = array();

					$rowActions['toggle_details'] = '<a class="detailsbutton" id="' . esc_attr( "row-{$entry->getId()}" ) . '" title="' . esc_attr__( 'Click to show details.', 'connections' ) . '" >' . esc_html__( 'Show Details', 'connections' ) . '</a>';

					$rowActions['vcard'] = $entry->vcard(
						array(
							'text'   => esc_html__( 'vCard', 'connections' ),
							'return' => true,
						)
					);

					if ( 'approved' === $entry->getStatus() ) {
						$rowActions['view'] = cnURL::permalink(
							array(
								'slug'   => $entry->getSlug(),
								// translators: The directory entry name.
								'title'  => sprintf( esc_html__( 'View %s', 'connections' ), $entry->getName( array( 'format' => '%first% %last%' ) ) ),
								'text'   => esc_html__( 'View', 'connections' ),
								'return' => true,
							)
						);
					}

					if ( $entry->getStatus() == 'approved' && current_user_can( 'connections_edit_entry' ) ) {

						$rowEditActions['unapprove'] = '<a class="action unapprove" href="' . esc_url( $unapproveNonceURL ) . '" title="' . esc_attr__( 'Unapprove', 'connections' ) . ' ' . $fullName . '">' . esc_html__( 'Unapprove', 'connections' ) . '</a>';
					}

					if ( $entry->getStatus() == 'pending' && current_user_can( 'connections_edit_entry' ) ) {

						$rowEditActions['approve'] = '<a class="action approve" href="' . esc_url( $approvedNonceURL ) . '" title="' . esc_attr__( 'Approve', 'connections' ) . ' ' . $fullName . '">' . esc_html__( 'Approve', 'connections' ) . '</a>';
					}

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

						$rowEditActions['edit'] = '<a class="editbutton" href="' . esc_url( $editNonceURL ) . '" title="' . esc_attr__( 'Edit', 'connections' ) . ' ' . $fullName . '">' . esc_html__( 'Edit', 'connections' ) . '</a>';
					}

					// phpcs:disable Squiz.PHP.CommentedOutCode.Found, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar
					// if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {
					//
					// 	$rowEditActions['copy'] = '<a class="copybutton" href="' . esc_url( $copyNonceURL ) . '" title="' . esc_html__( 'Copy', 'connections' ) . ' ' . $fullName . '">' . esc_html__( 'Copy', 'connections' ) . '</a>';
					// }
					// phpcs:enable Squiz.PHP.CommentedOutCode.Found, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar

					if ( current_user_can( 'connections_delete_entry' ) ) {

						$rowEditActions['delete'] = '<a class="submitdelete" onclick="return confirm(\'You are about to delete this entry. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . esc_url( $deleteNonceURL ) . '" title="' . esc_attr__( 'Delete', 'connections' ) . ' ' . $fullName . '">' . esc_html__( 'Delete', 'connections' ) . '</a>';
					}

					/**
					 * @since 8.35
					 *
					 * @param array   $rowEditActions An array of entry edit action links.
					 * @param cnEntry $entry
					 */
					$rowEditActions = apply_filters( 'cn_entry_row_edit_actions', $rowEditActions, $entry );

					/**
					 * @since 8.35
					 *
					 * @param array   $rowActions An array of entry action links.
					 * @param cnEntry $entry
					 */
					$rowActions = apply_filters( 'cn_entry_row_actions', $rowActions, $entry );

					if ( is_array( $rowEditActions ) && ! empty( $rowEditActions ) ) {

						echo implode( ' | ', $rowEditActions ), '<br/>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

					if ( is_array( $rowActions ) && ! empty( $rowActions ) ) {

						echo implode( ' | ', $rowActions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

					echo '</div>';
					echo "</td> \n";
					echo "<td > \n";

					$categories = $entry->getCategory();

					if ( ! empty( $categories ) ) {
						$i = 0;

						foreach ( $categories as $category ) {
							/*
							 * Generate the category link token URL.
							 */
							$categoryFilterURL = _nonce::url( 'admin.php?cn-action=filter&category=' . $category->term_id, 'filter' ); //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

							echo '<a href="' . esc_url( $categoryFilterURL ) . '">' . esc_html( $category->name ) . '</a>';

							$i++;
							if ( count( $categories ) > $i ) {
								echo ', ';
							}
						}

						unset( $i );
					}

					echo "</td> \n";
					echo '<td >';
						echo '<strong>' . esc_html__( 'On', 'connections' ) . ':</strong> ' . esc_html( $entry->getFormattedTimeStamp() ) . '<br />';
						echo '<strong>' . esc_html__( 'By', 'connections' ) . ':</strong> ' . esc_html( $entry->getEditedBy() ) . '<br />';
						echo '<strong>' . esc_html__( 'Visibility', 'connections' ) . ':</strong> ' . esc_html( $entry->displayVisibilityType() ) . '<br />';

						$user = $entry->getUser() ? get_userdata( $entry->getUser() ) : false;

					/**
					 * NOTE: WP 3.5 introduced get_edit_user_link()
					 *
					 * @link https://codex.wordpress.org/Function_Reference/get_edit_user_link
					 *
					 * @TODO Use get_edit_user_link() to simplify this code when WP hits >= 3.9.
					 */
					if ( $user ) {

						if ( get_current_user_id() == $user->ID ) {

							$editUserLink = get_edit_profile_url( $user->ID );

						} else {

							$editUserLink = add_query_arg( 'user_id', $user->ID, self_admin_url( 'user-edit.php' ) );
						}

						echo '<strong>' . esc_html__( 'Linked to:', 'connections' ) . '</strong> <a href="' . esc_url( $editUserLink ) . '">' . esc_html( $user->display_name ) . '</a>'; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					}

					echo "</td> \n";
					echo "</tr> \n";

					echo '<tr class="' . _escape::classNames( array( "child-row-{$entry->getId()}", 'cn-entry-details' ) ) . '" id="' . esc_attr( "contact-{$entry->getId()}-detail" ) . '" style="display: none;">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<td colspan="2">&nbsp;</td>', "\n";
					// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
					// echo "<td >&nbsp;</td> \n";
					echo '<td colspan="2">';

					$relations = $entry->getFamilyMembers();

					if ( $relations ) {

						$relationsHTML = array();

						foreach ( $relations as $relationData ) {

							$relation = new cnEntry();
							$relation->set( $relationData['entry_id'] );

							if ( $relation->getId() ) {

								if ( current_user_can( 'connections_edit_entry' ) ) {

									$editRelationTokenURL = _nonce::url( 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $relation->getId(), 'entry_edit', $relation->getId() );

									$relationsHTML[] = '<strong>' . $instance->options->getFamilyRelation( $relationData['relation'] ) . ':</strong> <a href="' . esc_url( $editRelationTokenURL ) . '" title="' . esc_attr__( 'Edit', 'connections' ) . ' ' . $relation->getName() . '">' . $relation->getName() . '</a>';

								} else {

									$relationsHTML[] = '<strong>' . $instance->options->getFamilyRelation( $relationData['relation'] ) . ':</strong> ' . $relation->getName();
								}
							}
						}

						if ( ! empty( $relationsHTML ) ) {

							echo implode( '<br />' . PHP_EOL, $relationsHTML ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}

					if ( $entry->getContactFirstName() || $entry->getContactLastName() ) {

						echo '<strong>' . esc_html__( 'Contact', 'connections' ) . ':</strong> ' . $entry->getContactFirstName() . ' ' . $entry->getContactLastName() . '<br />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

					if ( $entry->getTitle() ) {

						echo '<strong>' . esc_html__( 'Title', 'connections' ) . ':</strong> ' . $entry->getTitle() . '<br />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

					if ( $entry->getOrganization() && $entry->getEntryType() !== 'organization' ) {

						echo '<strong>' . esc_html__( 'Organization', 'connections' ) . ':</strong> ' . $entry->getOrganization() . '<br />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

					if ( $entry->getDepartment() ) {

						echo '<strong>' . esc_html__( 'Department', 'connections' ) . ':</strong> ' . $entry->getDepartment() . '<br />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

					$entry->addresses->escapeForDisplay()->render( 'admin' );

					echo '</td>', "\n";

					echo '<td>';

					$entry->phoneNumbers->escapeForDisplay()->render( 'admin' );
					$entry->emailAddresses->escapeForDisplay()->render( 'admin' );
					$entry->im->escapeForDisplay()->render( 'admin' );
					$entry->socialMedia->escapeForDisplay()->render( 'admin' );
					$entry->links->escapeForDisplay()->render( 'admin' );

					echo "</td> \n";

					echo '<td>';
					$entry->dates->escapeForDisplay()->render( 'admin' );
					echo "</td> \n";
					echo "</tr> \n";

					echo '<tr class="' . _escape::classNames( array( "child-row-{$entry->getId()}", 'entrynotes' ) ) . '" id="' . esc_attr( "contact-{$entry->getId()}-detail-notes" ) . '" style="display: none;">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo "<td colspan='2'>&nbsp;</td> \n";
					// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
					// echo "<td >&nbsp;</td> \n";
					echo "<td colspan='3'>";
					echo ( $entry->getBio() ) ? '<strong>' . esc_html__( 'Bio', 'connections' ) . ':</strong> ' . _escape::html( $entry->getBio() ) . '<br />' : '&nbsp;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo ( $entry->getNotes() ) ? '<strong>' . esc_html__( 'Notes', 'connections' ) . ':</strong> ' . _escape::html( $entry->getNotes() ) : '&nbsp;';      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo "</td> \n";
					echo '<td>
						  <span style="display: block;"><strong>' . esc_html__( 'Entry ID', 'connections' ) . ':</strong> ' . esc_html( $entry->getId() ) . '</span>
						  <span style="display: block;"><strong>' . esc_html__( 'Entry Slug', 'connections' ) . ':</strong> ' . esc_html( $entry->getSlug() ) . '</span>
						  <span style="display: block;"><strong>' . esc_html__( 'Date Added', 'connections' ) . ':</strong> ' . esc_html( $entry->getDateAdded() ) . '</span>
						  <span style="display: block;"><strong>' . esc_html__( 'Added By', 'connections' ) . ':</strong> ' . esc_html( $entry->getAddedBy() ) . '</span>';
					echo '<span style="display: block;"><strong>' . esc_html__( 'Image Linked', 'connections' ) . ':</strong> ' . ( ( ! $entry->getImageLinked() ) ? esc_html__( 'No', 'connections' ) : esc_html__( 'Yes', 'connections' ) ) . '</span>';
					echo '<span style="display: block;"><strong>' . esc_html__( 'Display', 'connections' ) . ':</strong> ' . ( ( $entry->getImageLinked() && $entry->getImageDisplay() ) ? esc_html__( 'Yes', 'connections' ) : esc_html__( 'No', 'connections' ) ) . '</span>';
					echo "</td> \n";
					echo "</tr> \n";

				}
				?>
								</tbody>
							</table>
							</form>


					<script type="text/javascript">
						/* <![CDATA[ */
						(function($){
							$(document).ready(function(){
								$('#doaction, #doaction2').click(function(){
									if ( $('select[name^="action"]').val() == 'delete' ) {
										var m = 'You are about to delete the selected entry(ies).\n  \'Cancel\' to stop, \'OK\' to delete.';
										return showNotice.warn(m);
									}
								});
							});
						})(jQuery);
						/* ]]> */
					</script>
				<?php

			} else {

				cnMessage::set( 'error', 'capability_view_entry_list' );
			}

			break;
	}

	echo '</div> <!-- .wrap -->';
}
