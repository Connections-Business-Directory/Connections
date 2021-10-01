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

function connectionsShowViewPage( $action = null ) {

	// Grab an instance of the Connections object.
	$instance  = Connections_Directory();
	$queryVars = array();

	echo '<div class="wrap">';

	switch ( $action ) {

		case 'add_entry':
			echo '<h1>Connections : ' , esc_html__( 'Add Entry', 'connections' ) , '</h1>';

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
					'title'    => __( 'Name', 'connections' ),
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

						$form->tokenField( 'add_entry', false, '_cn_wpnonce', false );

						do_action( 'cn_admin_form_add_entry_before', $entry, $form );

						echo '<div id="postbox-container-1" class="postbox-container">';
							echo '<div id="side-sortables" class="meta-box-sortables ui-sortable">';
								do_meta_boxes( $instance->pageHook->add, 'side', $entry );
							echo '</div> <!-- #side-sortables -->';
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

				$form->close();

				unset( $entry );

			} else {

				cnMessage::render( 'error', __( 'You are not authorized to add entries. Please contact the admin if you received this message in error.', 'connections' ) );
			}

			break;

		case 'copy_entry':
			echo '<h1>Connections : ' , esc_html__( 'Copy Entry', 'connections' ) , '</h1>';

			/*
			 * Check whether current user can add an entry.
			 */
			if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

				$id = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
				check_admin_referer( 'entry_copy_' . $id );

				$form  = new cnFormObjects();
				$entry = new cnOutput( $instance->retrieve->entry( $id ) );

				$resetID = function( $item ) {
					cnArray::set( $item, 'id', 0 );
					return $item;
				};

				$resetUID = function( $item ) {
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
					'title'    => __( 'Name', 'connections' ),
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

						$form->tokenField( 'add_entry', false, '_cn_wpnonce', false );

						do_action( 'cn_admin_form_copy_entry_before', $entry, $form );

						echo '<div id="postbox-container-1" class="postbox-container">';
							echo '<div id="side-sortables" class="meta-box-sortables ui-sortable">';
								do_meta_boxes( $instance->pageHook->manage, 'side', $entry );
							echo '</div> <!-- #side-sortables -->';
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

				$form->close();

				unset( $entry );

			} else {

				cnMessage::render( 'error', __( 'You are not authorized to add entries. Please contact the admin if you received this message in error.', 'connections' ) );
			}

			break;

		case 'edit_entry':
			echo '<h1>Connections : ' , esc_html__( 'Edit Entry', 'connections' ) , '</h1>';

			/*
			 * Check whether the current user can edit entries.
			 */
			if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

				$id = absint( $_GET['id'] );
				check_admin_referer( 'entry_edit_' . $id );

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
					'title'    => __( 'Name', 'connections' ),
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

						$form->tokenField( 'update_entry', false, '_cn_wpnonce', false );

						do_action( 'cn_admin_form_edit_entry_before', $entry, $form );

						echo '<div id="postbox-container-1" class="postbox-container">';
							echo '<div id="side-sortables" class="meta-box-sortables ui-sortable">';
								do_meta_boxes( $instance->pageHook->manage, 'side', $entry );
							echo '</div> <!-- #side-sortables -->';
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

				$form->close();

				unset( $entry );

			} else {

				cnMessage::render( 'error', __( 'You are not authorized to edit entries. Please contact the admin if you received this message in error.', 'connections' ) );
			}

			break;

		default:
			$form   = new cnFormObjects();
			$page   = (object) $instance->currentUser->getScreenOption( 'manage', 'pagination', array( 'current' => 1, 'limit' => 50 ) );
			$offset = ( $page->current - 1 ) * $page->limit;

			echo '<h1>Connections : ' , __( 'Manage', 'connections' ) , ' <a class="button add-new-h2" href="admin.php?page=connections_add">' , __( 'Add New', 'connections' ) , '</a></h1>';

			/*
			 * Check whether user can view the entry list
			 */
			if ( current_user_can( 'connections_manage' ) ) {

				$retrieveAttr['list_type']  = $instance->currentUser->getFilterEntryType();
				$retrieveAttr['category']   = $instance->currentUser->getFilterCategory();

				$retrieveAttr['char']       = isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) ? esc_attr( $_GET['cn-char'] ) : '';
				$retrieveAttr['visibility'] = $instance->currentUser->getFilterVisibility();
				$retrieveAttr['status']     = $instance->currentUser->getFilterStatus();

				$retrieveAttr['limit']      = $page->limit;
				$retrieveAttr['offset']     = $offset;

				if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {

					$retrieveAttr['search_terms'] = wp_unslash( $_REQUEST['s'] );
				}

				$results = $instance->retrieve->entries( $retrieveAttr );
				// print_r($instance->lastQuery);
				?>

				<?php if ( current_user_can( 'connections_edit_entry' ) ) { ?>

				<ul class="subsubsub">

					<?php

					$statuses = array(
						'all'      => __( 'All', 'connections' ),
						'approved' => __( 'Approved', 'connections' ),
						'pending'  => __( 'Moderate', 'connections' ),
					);

					foreach ( $statuses as $key => $status ) {

						$subsubsub[] = sprintf( '<li><a%1$shref="%2$s">%3$s</a> <span class="count">(%4$d)</span></li>',
							$instance->currentUser->getFilterStatus() == $key ? ' class="current" ' : ' ',
							esc_url( $form->tokenURL( add_query_arg( array( 'page' => 'connections_manage', 'cn-action' => 'filter', 'status' => $key ) ), 'filter' ) ),
							$status,
							cnRetrieve::recordCount( array( 'status' => $key ) )
						 );
					}

					echo implode( ' | ', $subsubsub );

					?>

				</ul>

				<?php } ?>

				<form method="post">

					<?php $searchTerm = isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ? $_REQUEST['s'] : ''; ?>

					<p class="search-box">
						<label class="screen-reader-text" for="entry-search-input"><?php _e( 'Search Entries', 'connections' ); ?>:</label>
						<input type="search" id="entry-search-input" name="s" value="<?php echo esc_attr( wp_unslash( $searchTerm ) ); ?>" />
						<?php submit_button( esc_attr__( 'Search Entries', 'connections' ), '', '', false, array( 'id' => 'search-submit' ) ); ?>
					</p>

					<?php $form->tokenField( 'cn_manage_actions' ); ?>

					<input type="hidden" name="cn-action" value="manage_actions"/>

					<div class="tablenav">

						<div class="alignleft actions">
							<?php

							cnTemplatePart::walker(
								'term-select',
								array(
									'name'            => 'category',
									'show_option_all' => __( 'Show All Categories', 'connections' ),
									'hide_empty'      => FALSE,
									'hierarchical'    => TRUE,
									'show_count'      => FALSE,
									'orderby'         => 'name',
									'selected'        => $instance->currentUser->getFilterCategory(),
								)
							);

							Field\Select::create()
										->setId( 'cn-entry_type' )
										->setName( 'entry_type' )
										->createOptionsFromArray(
											array(
												array(
													'label' => __( 'Show All Entries', 'connections' ),
													'value' => 'all',
												),
												array(
													'label' => __( 'Show Individuals', 'connections' ),
													'value' => 'individual',
												),
												array(
													'label' => __( 'Show Organizations', 'connections' ),
													'value' => 'organization',
												),
												array(
													'label' => __( 'Show Families', 'connections' ),
													'value' => 'family',
												),
											)
										)
										->setValue( $instance->currentUser->getFilterEntryType() )
										->render();

							/*
							 * Builds the visibility select list base on current user capabilities.
							 */
							$visibilitySelect = array(
								array(
									'label' => __( 'Show All', 'connections' ),
									'value' => 'all',
								),
							);

							if ( current_user_can( 'connections_view_public' ) || $instance->options->getAllowPublic() ) {

								$visibilitySelect[] = array( 'label' => __( 'Show Public', 'connections' ), 'value' => 'public' );
							}

							if ( current_user_can( 'connections_view_private' ) ) {

								$visibilitySelect[] = array( 'label' => __( 'Show Private', 'connections' ), 'value' => 'private' );
							}

							if ( current_user_can( 'connections_view_unlisted' ) ) {

								$visibilitySelect[] = array( 'label' => __( 'Show Unlisted', 'connections' ), 'value' => 'unlisted' );
							}

							Field\Select::create()
										->setId( 'cn-visibility_type' )
										->setName( 'visibility_type' )
										->createOptionsFromArray( $visibilitySelect )
										->setValue( $instance->currentUser->getFilterVisibility() )
										->render();

							?>

							<input class="button-secondary action" type="submit" name="filter" value="Filter"/>

						</div>

						<div class="tablenav-pages">
							<?php

							echo '<span class="displaying-num">' . sprintf( __( 'Displaying %1$d of %2$d entries.', 'connections' ), $instance->resultCount, $instance->resultCountNoLimit ) . '</span>';

							/*
							 * // START --> Pagination
							 *
							 * Grab the pagination data again in case a filter reset the values
							 * or the user input an invalid number which the retrieve query would have reset.
							 */
							$page = (object) $instance->currentUser->getScreenOption( 'manage', 'pagination', array( 'current' => 1, 'limit' => 50 ) );

							$pageCount = ceil( $instance->resultCountNoLimit / $page->limit );

							if ( $pageCount > 1 ) {

								$pageDisabled   = array();
								$pageFilterURL  = array();
								$pageValue      = array();
								$currentPageURL = add_query_arg(
									array(
										'page'      => FALSE,
										//'connections_process' => TRUE,
										//'process' => 'manage',
										'cn-action' => 'filter',
										's'         => isset( $_REQUEST['s'] ) ? urlencode( $_REQUEST['s'] ) : '',
									)
								);

								$pageValue['first_page']    = 1;
								$pageValue['previous_page'] = ( $page->current - 1 >= 1 ) ? $page->current - 1 : 1;
								$pageValue['next_page']     = ( $page->current + 1 <= $pageCount ) ? $page->current + 1 : $pageCount;
								$pageValue['last_page']     = $pageCount;

								$pageDisabled['first_page']    = ( $page->current > 1 ) ? '' : ' disabled';
								$pageDisabled['previous_page'] = ( $page->current - 1 >= 1 ) ? '' : ' disabled';
								$pageDisabled['next_page']     = ( $page->current + 1 <= $pageCount ) ? '' : ' disabled';
								$pageDisabled['last_page']     = ( $page->current < $pageCount ) ? '' : ' disabled';

								/*
								 * Generate the page link token URL.
								 */
								$pageFilterURL['first_page'] = esc_url(
									$form->tokenURL(
										add_query_arg( array( 'pg' => $pageValue['first_page'] ), $currentPageURL ),
										'filter'
									)
								);

								$pageFilterURL['previous_page'] = esc_url(
									$form->tokenURL(
										add_query_arg( array( 'pg' => $pageValue['previous_page'] ), $currentPageURL ),
										'filter'
									)
								);

								$pageFilterURL['next_page'] = esc_url(
									$form->tokenURL(
										add_query_arg( array( 'pg' => $pageValue['next_page'] ), $currentPageURL ),
										'filter'
									)
								);

								$pageFilterURL['last_page'] = esc_url(
									$form->tokenURL(
										add_query_arg( array( 'pg' => $pageValue['last_page'] ), $currentPageURL ),
										'filter'
									)
								);

								echo '<span class="page-navigation" id="page-input">';

								echo '<a href="' . $pageFilterURL['first_page'] . '" title="' . __( 'Go to the first page.', 'connections' ) . '" class="first-page button' , $pageDisabled['first_page'] , '">&laquo;</a> ';
								echo '<a href="' . $pageFilterURL['previous_page'] . '" title="' . __( 'Go to the previous page.', 'connections' ) . '" class="prev-page button' , $pageDisabled['previous_page'] , '">&lsaquo;</a> ';

								echo '<span class="paging-input"><input type="text" size="2" value="' . $page->current . '" name="pg" title="' . __( 'Current page', 'connections' ) . '" class="current-page"> ' . __( 'of', 'connections' ) . ' <span class="total-pages">' . $pageCount . '</span></span> ';

								echo '<a href="' . $pageFilterURL['next_page'] . '" title="' . __( 'Go to the next page.', 'connections' ) . '" class="next-page button' , $pageDisabled['next_page'] , '">&rsaquo;</a> ';
								echo '<a href="' . $pageFilterURL['last_page'] . '" title="' . __( 'Go to the last page.', 'connections' ) . '" class="last-page button' , $pageDisabled['last_page'] , '">&raquo;</a>';

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

						if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_delete_entry' ) ) {
							echo '<div class="alignleft actions">';
							echo '<select name="action">';
							echo '<option value="" SELECTED>' , __( 'Bulk Actions', 'connections' ) , '</option>';

							$bulkActions = array();

							if ( current_user_can( 'connections_edit_entry' )  || current_user_can( 'connections_edit_entry_moderated' ) ) {
								$bulkActions['unapprove'] = __( 'Unapprove', 'connections' );
								$bulkActions['approve']   = __( 'Approve', 'connections' );
								$bulkActions['public']    = __( 'Set Public', 'connections' );
								$bulkActions['private']   = __( 'Set Private', 'connections' );
								$bulkActions['unlisted']  = __( 'Set Unlisted', 'connections' );
							}

							if ( current_user_can( 'connections_delete_entry' ) ) {
								$bulkActions['delete'] = __( 'Delete', 'connections' );
							}

							$bulkActions = apply_filters( 'cn_manage_bulk_actions', $bulkActions );

							foreach ( $bulkActions as $action => $string ) {
								echo '<option value="', $action, '">', $string, '</option>';
							}

							echo '</select>';
							echo '<input class="button-secondary action" type="submit" name="bulk_action" value="' , __( 'Apply', 'connections' ) , '" />';
							echo '</div>';
						}
						?>

						<div class="tablenav-pages" style="height: auto; max-width: 75%; text-align: right;">
							<?php

							/*
							 * Display the character filter control.
							 */
							echo '<span class="displaying-num">' , __( 'Filter by character:', 'connections' ) , '</span>';
							cnTemplatePart::index(
								array(
									'status' => $instance->currentUser->getFilterStatus(),
									'visibility' => $instance->currentUser->getFilterVisibility(),
									'tag' => 'span',
								)
							);
							cnTemplatePart::currentCharacter();
							?>
						</div>
					</div>
					<div class="clear"></div>

			       	<table cellspacing="0" class="widefat connections">
						<thead>
				            <tr>
				                <td class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></td>
								<th class="col" style="width:10%;"></th>
								<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
								<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
								<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
				            </tr>
						</thead>
						<tfoot>
				            <tr>
				                <td class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></td>
								<th class="col" style="width:10%;"></th>
								<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
								<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
								<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
				            </tr>
						</tfoot>
						<tbody>

				<?php

				foreach ( $results as $row ) {
					/**
					 *
					 *
					 * @TODO: Use the Output class to show entry details.
					 */
					$entry = new cnOutput( $row );

					/*
					 * Generate the edit, copy and delete URLs with nonce tokens.
					 */
					$editTokenURL      = esc_url( $form->tokenURL( 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $entry->getId(), 'entry_edit_' . $entry->getId() ) );
					$copyTokenURL      = esc_url( $form->tokenURL( 'admin.php?page=connections_manage&cn-action=copy_entry&id=' . $entry->getId(), 'entry_copy_' . $entry->getId() ) );
					$deleteTokenURL    = esc_url( $form->tokenURL( 'admin.php?cn-action=delete_entry&id=' . $entry->getId(), 'entry_delete_' . $entry->getId() ) );
					$approvedTokenURL  = esc_url( $form->tokenURL( 'admin.php?cn-action=set_status&status=approved&id=' . $entry->getId(), 'entry_status_' . $entry->getId() ) );
					$unapproveTokenURL = esc_url( $form->tokenURL( 'admin.php?cn-action=set_status&status=pending&id=' . $entry->getId(), 'entry_status_' . $entry->getId() ) );

					switch ( $entry->getStatus() ) {
						case 'pending' :
							$statusClass = ' unapproved';
							break;

						case 'approved' :
							$statusClass = ' approved';
							break;

						default:
							$statusClass = '';
							break;
					}

					echo '<tr id="row-' , $entry->getId() , '" class="parent-row' . $statusClass .'">';
					echo "<th class='check-column' scope='row'><input type='checkbox' value='" . $entry->getId() . "' name='id[]'/></th> \n";
					echo '<td>';
					$entry->getImage(
						array(
							'image'    => $instance->user->getScreenOption( 'manage', 'thumbnail', 'photo' ),
							'height'   => 54,
							'width'    => 80,
							'zc'       => 2,
							'fallback' => array(
								'type'   => 'block',
								'string' => __( 'No Image Available', 'connections' ),
							),
						)
					);
					echo '</td>';
					echo '<td  colspan="2">';

					echo '<div style="float:right"><a href="#wphead" title="Return to top."><img src="' . CN_URL . 'assets/images/uparrow.gif" /></a></div>';

					$name = $entry->getName( array( 'format' => '%last%, %first%' ) );

					echo '<strong>';

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

						echo '<a class="row-title" title="Edit ' . $name . '" href="' . $editTokenURL . '">' . $name . '</a>';

					} else {

						echo $name;
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
						echo '<span class="post-state">' . implode( '</span>, <span class="post-state">', $entryStates ) . '</span>';
					}

					echo '</strong>';

					echo '<div class="row-actions">';

					$fullName       = $entry->getName( array( 'format' => '%first% %middle% %last%' ), 'display' );
					$rowActions     = array();
					$rowEditActions = array();

					$rowActions['toggle_details'] = '<a class="detailsbutton" id="row-' . $entry->getId() . '" title="' . __( 'Click to show details.', 'connections' ) . '" >' . __( 'Show Details', 'connections' ) . '</a>';
					$rowActions['vcard']          = $entry->vcard( array( 'text' => __( 'vCard', 'connections' ), 'return' => TRUE ) );
					$rowActions['view']           = cnURL::permalink( array(
							'slug' => $entry->getSlug(),
							'title' => sprintf( __( 'View %s', 'connections' ) , $entry->getName( array( 'format' => '%first% %last%' ) ) ),
							'text' => __( 'View', 'connections' ),
							'return' => TRUE
						)
					);

					if ( $entry->getStatus() == 'approved' && current_user_can( 'connections_edit_entry' ) ) {

						$rowEditActions['unapprove'] = '<a class="action unapprove" href="' . $unapproveTokenURL . '" title="' . __( 'Unapprove', 'connections' ) . ' ' . $fullName . '">' . __( 'Unapprove', 'connections' ) . '</a>';
					}

					if ( $entry->getStatus() == 'pending' && current_user_can( 'connections_edit_entry' ) ) {

						$rowEditActions['approve'] = '<a class="action approve" href="' . $approvedTokenURL . '" title="' . __( 'Approve', 'connections' ) . ' ' . $fullName . '">' . __( 'Approve', 'connections' ) . '</a>';
					}

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

						$rowEditActions['edit'] = '<a class="editbutton" href="' . $editTokenURL . '" title="' . __( 'Edit', 'connections' ) . ' ' . $fullName . '">' . __( 'Edit', 'connections' ) . '</a>';
					}

					//if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {
					//
					//	$rowEditActions['copy'] = '<a class="copybutton" href="' . $copyTokenURL . '" title="' . __( 'Copy', 'connections' ) . ' ' . $fullName . '">' . __( 'Copy', 'connections' ) . '</a>';
					//}

					if ( current_user_can( 'connections_delete_entry' ) ) {

						$rowEditActions['delete'] = '<a class="submitdelete" onclick="return confirm(\'You are about to delete this entry. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . $deleteTokenURL . '" title="' . __( 'Delete', 'connections' ) . ' ' . $fullName . '">' . __( 'Delete', 'connections' ) . '</a>';
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

						echo implode( ' | ', $rowEditActions ) , '<br/>';
					}

					if ( is_array( $rowActions ) && ! empty( $rowActions ) ) {

						echo implode( ' | ', $rowActions );
					}

					echo '</div>';
					echo "</td> \n";
					echo "<td > \n";

					$categories = $entry->getCategory();

					if ( !empty( $categories ) ) {
						$i = 0;

						foreach ( $categories as $category ) {
							/*
							 * Generate the category link token URL.
							 */
							$categoryFilterURL = $form->tokenURL( 'admin.php?cn-action=filter&category=' . $category->term_id, 'filter' );

							echo '<a href="' . esc_url( $categoryFilterURL ) . '">' . $category->name . '</a>';

							$i++;
							if ( count( $categories ) > $i ) echo ', ';
						}

						unset( $i );
					}

					echo "</td> \n";
					echo '<td >';
						echo '<strong>' . __( 'On', 'connections' ) . ':</strong> ' . $entry->getFormattedTimeStamp() . '<br />';
						echo '<strong>' . __( 'By', 'connections' ) . ':</strong> ' . $entry->getEditedBy() . '<br />';
						echo '<strong>' . __( 'Visibility', 'connections' ) . ':</strong> ' . $entry->displayVisibilityType() . '<br />';

						$user = $entry->getUser() ? get_userdata( $entry->getUser() ) : FALSE;

						/**
						 * NOTE: WP 3.5 introduced get_edit_user_link()
						 * REF:  http://codex.wordpress.org/Function_Reference/get_edit_user_link
						 *
						 * @TODO Use get_edit_user_link() to simplify this code when WP hits >= 3.9.
						 */
						if ( $user ) {

							if ( get_current_user_id() == $user->ID ) {

								$editUserLink = get_edit_profile_url( $user->ID );

							} else {

								$editUserLink = add_query_arg( 'user_id', $user->ID, self_admin_url( 'user-edit.php' ) );
							}

							echo '<strong>' . __( 'Linked to:', 'connections' ) . '</strong> ' . '<a href="' . esc_url( $editUserLink ) .'">'. esc_attr( $user->display_name ) .'</a>';
						}

					echo "</td> \n";
					echo "</tr> \n";

					echo "<tr class='child-row-" . $entry->getId() . " cn-entry-details' id='contact-" . $entry->getId() . "-detail' style='display:none;'>";
					echo '<td colspan="2">&nbsp;</td>' , "\n";
					//echo "<td >&nbsp;</td> \n";
					echo '<td colspan="2">';

					if ( $relations = $entry->getFamilyMembers() ) {

						$relationsHTML = array();

						foreach ( $relations as $relationData ) {

							$relation = new cnEntry();
							$relation->set( $relationData['entry_id'] );

							if ( $relation->getId() ) {

								if ( current_user_can( 'connections_edit_entry' ) ) {

									$editRelationTokenURL = esc_url( $form->tokenURL( 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $relation->getId(), 'entry_edit_' . $relation->getId() ) );

									$relationsHTML[] = '<strong>' . $instance->options->getFamilyRelation( $relationData['relation'] ) . ':</strong> ' . '<a href="' . $editRelationTokenURL . '" title="' . __( 'Edit', 'connections' ) . ' ' . $relation->getName() . '">' . $relation->getName() . '</a>';

								} else {

									$relationsHTML[] = '<strong>' . $instance->options->getFamilyRelation( $relationData['relation'] ) . ':</strong> ' . $relation->getName();
								}
							}
						}

						if ( ! empty( $relationsHTML ) ) echo implode( '<br />' . PHP_EOL, $relationsHTML );
					}

					if ( $entry->getContactFirstName() || $entry->getContactLastName() ) echo '<strong>' . __( 'Contact', 'connections' ) . ':</strong> ' . $entry->getContactFirstName() . ' ' . $entry->getContactLastName() . '<br />';
					if ( $entry->getTitle() ) echo '<strong>' . __( 'Title', 'connections' ) . ':</strong> ' . $entry->getTitle() . '<br />';
					if ( $entry->getOrganization() && $entry->getEntryType() !== 'organization' ) echo '<strong>' . __( 'Organization', 'connections' ) . ':</strong> ' . $entry->getOrganization() . '<br />';
					if ( $entry->getDepartment() ) echo '<strong>' . __( 'Department', 'connections' ) . ':</strong> ' . $entry->getDepartment() . '<br />';

					$entry->addresses->escapeForDisplay()->render( 'admin' );

					echo '</td>' , "\n";

					echo '<td>';

					$entry->phoneNumbers->escapeForDisplay()->render( 'admin' );
					$entry->emailAddresses->escapeForDisplay()->render( 'admin' );
					$entry->im->escapeForDisplay()->render( 'admin' );

					$socialNetworks = $entry->getSocialMedia();

					if ( ! empty( $socialNetworks ) ) {
						echo '<div class="social-networks">';

						foreach ( $entry->getSocialMedia() as $network ) {
							( $network->preferred ) ? $preferred = '*' : $preferred = '';

							echo '<span class="social-network"><strong>' , $network->name , ':</strong> <a target="_blank" href="' , $network->url , '">' , $network->url . '</a>' , $preferred , '</span>';
						}

						echo '</div>';
					}

					$entry->links->escapeForDisplay()->render( 'admin' );

					echo "</td> \n";

					echo "<td>";
					$entry->dates->escapeForDisplay()->render( 'admin' );
					echo "</td> \n";
					echo "</tr> \n";

					echo "<tr class='child-row-" . $entry->getId() . " entrynotes' id='contact-" . $entry->getId() . "-detail-notes' style='display:none;'>";
					echo "<td colspan='2'>&nbsp;</td> \n";
					//echo "<td >&nbsp;</td> \n";
					echo "<td colspan='3'>";
					echo ( $entry->getBio() ) ? '<strong>' . __( 'Bio', 'connections' ) . ':</strong> ' . $entry->getBio() . '<br />' : '&nbsp;';
					echo ( $entry->getNotes() ) ? '<strong>' . __( 'Notes', 'connections' ) . ':</strong> ' . $entry->getNotes() : '&nbsp;';
					echo "</td> \n";
					echo '<td>
						  <span style="display: block;"><strong>' . __( 'Entry ID', 'connections' ) . ':</strong> ' . $entry->getId() . '</span>' . '
						  <span style="display: block;"><strong>' . __( 'Entry Slug', 'connections' ) . ':</strong> ' . $entry->getSlug() . '</span>' . '
						  <span style="display: block;"><strong>' . __( 'Date Added', 'connections' ) . ':</strong> ' . $entry->getDateAdded() . '</span>
						  <span style="display: block;"><strong>' . __( 'Added By', 'connections' ) . ':</strong> ' . $entry->getAddedBy() . '</span>';
					echo '<span style="display: block;"><strong>' . __( 'Image Linked', 'connections' ) . ':</strong> ' . ( ( ! $entry->getImageLinked() ) ? __( 'No', 'connections' ) : __( 'Yes', 'connections' ) ) . '</span>';
					echo '<span style="display: block;"><strong>' . __( 'Display', 'connections' ) . ':</strong> ' . ( ( $entry->getImageLinked() && $entry->getImageDisplay() ) ? __( 'Yes', 'connections' ) : __( 'No', 'connections' ) ) . '</span>';
					echo "</td> \n";
					echo "</tr> \n";

				} ?>
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
