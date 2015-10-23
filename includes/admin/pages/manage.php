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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function connectionsShowViewPage( $action = NULL ) {

	// Grab an instance of the Connections object.
	$instance  = Connections_Directory();
	$queryVars = array();

	echo '<div class="wrap">';

	switch ( $action ) {

		case 'add_entry':

			echo '<h1>Connections : ' , __( 'Add Entry', 'connections' ) , '</h1>';

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
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE );

						$form->tokenField( 'add_entry', FALSE, '_cn_wpnonce', FALSE );

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

			echo '<div class="wrap">';

			echo '<h1>Connections : ' , __( 'Copy Entry', 'connections' ) , '</h1>';

			/*
			 * Check whether current user can add an entry.
			 */
			if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

				$id = esc_attr( $_GET['id'] );
				check_admin_referer( 'entry_copy_' . $id );

				$form  = new cnFormObjects();
				$entry = new cnOutput( $instance->retrieve->entry( $id ) );

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
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE );

						$form->tokenField( 'add_entry', FALSE, '_cn_wpnonce', FALSE );

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

			echo '<h1>Connections : ' , __( 'Edit Entry', 'connections' ) , '</h1>';

			/*
			 * Check whether the current user can edit entries.
			 */
			if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

				$id = esc_attr( $_GET['id'] );
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
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE );

						$form->tokenField( 'update_entry', FALSE, '_cn_wpnonce', FALSE );

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
			$form = new cnFormObjects();

			$page = $instance->currentUser->getFilterPage( 'manage' );
			$offset = ( $page->current - 1 ) * $page->limit;

			echo '<div class="wrap">';

			echo '<h1>Connections : ' , __( 'Manage', 'connections' ) , ' <a class="button add-new-h2" href="admin.php?page=connections_add">' , __( 'Add New', 'connections' ) , '</a></h1>';

			/*
			 * Check whether user can view the entry list
			 */
			if ( current_user_can( 'connections_manage' ) ) {

				$retrieveAttr['list_type']  = $instance->currentUser->getFilterEntryType();
				$retrieveAttr['category']   = $instance->currentUser->getFilterCategory();

				$retrieveAttr['char']       = isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) ? $_GET['cn-char'] : '';
				$retrieveAttr['visibility'] = $instance->currentUser->getFilterVisibility();
				$retrieveAttr['status']     = $instance->currentUser->getFilterStatus();

				$retrieveAttr['limit']      = $page->limit;
				$retrieveAttr['offset']     = $offset;

				if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) $retrieveAttr['search_terms'] = $_GET['s'];

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

					<p class="search-box">
						<label class="screen-reader-text" for="entry-search-input"><?php _e( 'Search Entries', 'connections' ); ?>:</label>
						<input type="search" id="entry-search-input" name="s" value="<?php if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) echo $_GET['s'] ; ?>" />
						<input type="submit" name="" id="search-submit" class="button" value="<?php _e( 'Search Entries', 'connections' ); ?>"  />
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

							echo $form->buildSelect(
								'entry_type',
								array(
									'all' => __( 'Show All Entries', 'connections' ),
									'individual' => __( 'Show Individuals', 'connections' ),
									'organization' => __( 'Show Organizations', 'connections' ),
									'family' => __( 'Show Families', 'connections' )
								),
								$instance->currentUser->getFilterEntryType()
							);

							/*
							 * Builds the visibility select list base on current user capabilities.
							 */
							if ( current_user_can( 'connections_view_public' ) || $instance->options->getAllowPublic() ) $visibilitySelect['public'] = __( 'Show Public', 'connections' );
							if ( current_user_can( 'connections_view_private' ) ) $visibilitySelect['private'] = __( 'Show Private', 'connections' );
							if ( current_user_can( 'connections_view_unlisted' ) ) $visibilitySelect['unlisted'] = __( 'Show Unlisted', 'connections' );

							if ( isset( $visibilitySelect ) ) {

								/*
								 * Add the 'Show All' option and echo the list.
								 */
								$showAll['all'] = __( 'Show All', 'connections' );
								$visibilitySelect = $showAll + $visibilitySelect;
								echo $form->buildSelect( 'visibility_type', $visibilitySelect, $instance->currentUser->getFilterVisibility() );
							}

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
							$page = $instance->currentUser->getFilterPage( 'manage' );

							$pageCount = ceil( $instance->resultCountNoLimit / $page->limit );

							if ( $pageCount > 1 ) {

								$pageDisabled   = array();
								$pageFilterURL  = array();
								$pageValue      = array();
								$currentPageURL = add_query_arg( array( 'page' => FALSE , /*'connections_process' => TRUE , 'process' => 'manage' ,*/ 'cn-action' => 'filter' )  );

								$pageValue['first_page']    = 1;
								$pageValue['previous_page'] = ( $page->current - 1 >= 1 ) ? $page->current - 1 : 1;
								$pageValue['next_page']     = ( $page->current + 1 <= $pageCount ) ? $page->current + 1 : $pageCount;
								$pageValue['last_page']     = $pageCount;

								( $page->current > 1 ) ? $pageDisabled['first_page'] = '' : $pageDisabled['first_page'] = ' disabled';
								( $page->current - 1 >= 1 ) ? $pageDisabled['previous_page'] = '' : $pageDisabled['previous_page'] = ' disabled';
								( $page->current + 1 <= $pageCount ) ? $pageDisabled['next_page'] = '' : $pageDisabled['next_page'] = ' disabled';
								( $page->current < $pageCount ) ? $pageDisabled['last_page'] = '' : $pageDisabled['last_page'] = ' disabled';

								/*
								 * Generate the page link token URL.
								 */
								$pageFilterURL['first_page']    = esc_url( $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['first_page'] ) , $currentPageURL ) , 'filter' ) );
								$pageFilterURL['previous_page'] = esc_url( $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['previous_page'] ) , $currentPageURL ) , 'filter' ) );
								$pageFilterURL['next_page']     = esc_url( $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['next_page'] ) , $currentPageURL ) , 'filter' ) );
								$pageFilterURL['last_page']     = esc_url( $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['last_page'] ) , $currentPageURL ) , 'filter' ) );

								echo '<span class="page-navigation" id="page-input">';

								echo '<a href="' . $pageFilterURL['first_page'] . '" title="' . __( 'Go to the first page.', 'connections' ) . '" class="first-page' , $pageDisabled['first_page'] , '">&laquo;</a> ';
								echo '<a href="' . $pageFilterURL['previous_page'] . '" title="' . __( 'Go to the previous page.', 'connections' ) . '" class="prev-page' , $pageDisabled['previous_page'] , '">&lsaquo;</a> ';

								echo '<span class="paging-input"><input type="text" size="2" value="' . $page->current . '" name="pg" title="' . __( 'Current page', 'connections' ) . '" class="current-page"> ' . __( 'of', 'connections' ) . ' <span class="total-pages">' . $pageCount . '</span></span> ';

								echo '<a href="' . $pageFilterURL['next_page'] . '" title="' . __( 'Go to the next page.', 'connections' ) . '" class="next-page' , $pageDisabled['next_page'] , '">&rsaquo;</a> ';
								echo '<a href="' . $pageFilterURL['last_page'] . '" title="' . __( 'Go to the last page.', 'connections' ) . '" class="last-page' , $pageDisabled['last_page'] , '">&raquo;</a>';

								echo '</span>';
							}

							/*
							 * // END --> Pagination
							 */
							?>
						</div>

					</div>
					<div class="clear"></div>
					<div class="tablenav">

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

						<div class="tablenav-pages">
							<?php

							/*
							 * Display the character filter control.
							 */
							echo '<span class="displaying-num">' , __( 'Filter by character:', 'connections' ) , '</span>';
							cnTemplatePart::index( array( 'status' => $instance->currentUser->getFilterStatus(), 'tag' => 'span' ) );
							cnTemplatePart::currentCharacter();
							?>
						</div>
					</div>
					<div class="clear"></div>

			       	<table cellspacing="0" class="widefat connections">
						<thead>
				            <tr>
				                <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
								<th class="col" style="width:10%;"></th>
								<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
								<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
								<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
				            </tr>
						</thead>
						<tfoot>
				            <tr>
				                <th class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
								<th class="col" style="width:10%;"></th>
								<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
								<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
								<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
				            </tr>
						</tfoot>
						<tbody>

				<?php

				$previousLetter = '';

				foreach ( $results as $row ) {
					/**
					 *
					 *
					 * @TODO: Use the Output class to show entry details.
					 */
					$entry = new cnOutput( $row );

					$currentLetter = strtoupper( mb_substr( $entry->getSortColumn(), 0, 1 ) );
					if ( $currentLetter != $previousLetter ) {
						$setAnchor = "<a name='$currentLetter'></a>";
						$previousLetter = $currentLetter;
					} else {
						$setAnchor = null;
					}

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
					$entry->getImage( array( 'image' => 'photo', 'height' => 54, 'width' => 80, 'zc' => 2, 'fallback' => array( 'type' => 'block', 'string' => __( 'No Photo Available', 'connections' ) ) ) );
					echo '</td>';
					echo '<td  colspan="2">';
					if ( $setAnchor ) echo $setAnchor;
					echo '<div style="float:right"><a href="#wphead" title="Return to top."><img src="' . CN_URL . 'assets/images/uparrow.gif" /></a></div>';

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {
						echo '<a class="row-title" title="Edit ' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '" href="' . $editTokenURL . '"> ' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '</a><br />';
					}
					else {
						echo '<strong>' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '</strong>';
					}

					echo '<div class="row-actions">';
					$rowActions = array();
					$rowEditActions = array();

					$rowActions[] = '<a class="detailsbutton" id="row-' . $entry->getId() . '" title="' . __( 'Click to show details.', 'connections' ) . '" >' . __( 'Show Details', 'connections' ) . '</a>';
					$rowActions[] = $entry->vcard( array( 'text' => __( 'vCard', 'connections' ), 'return' => TRUE ) );
					$rowActions[] = cnURL::permalink( array(
							'slug' => $entry->getSlug(),
							'title' => sprintf( __( 'View %s', 'connections' ) , $entry->getName( array( 'format' => '%first% %last%' ) ) ),
							'text' => __( 'View', 'connections' ),
							'return' => TRUE
						)
					);

					if ( $entry->getStatus() == 'approved' && current_user_can( 'connections_edit_entry' ) ) $rowEditActions[] = '<a class="action unapprove" href="' . $unapproveTokenURL . '" title="' . __( 'Unapprove', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Unapprove', 'connections' ) . '</a>';
					if ( $entry->getStatus() == 'pending' && current_user_can( 'connections_edit_entry' ) ) $rowEditActions[] = '<a class="action approve" href="' . $approvedTokenURL . '" title="' . __( 'Approve', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Approve', 'connections' ) . '</a>';

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) $rowEditActions[] = '<a class="editbutton" href="' . $editTokenURL . '" title="' . __( 'Edit', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Edit', 'connections' ) . '</a>';
					if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) $rowEditActions[] = '<a class="copybutton" href="' . $copyTokenURL . '" title="' . __( 'Copy', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Copy', 'connections' ) . '</a>';
					if ( current_user_can( 'connections_delete_entry' ) ) $rowEditActions[] = '<a class="submitdelete" onclick="return confirm(\'You are about to delete this entry. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . $deleteTokenURL . '" title="' . __( 'Delete', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Delete', 'connections' ) . '</a>';

					if ( ! empty( $rowEditActions ) ) echo implode( ' | ', $rowEditActions ) , '<br/>';
					if ( ! empty( $rowActions ) ) echo implode( ' | ', $rowActions );

					echo '</div>';
					echo "</td> \n";
					echo "<td > \n";

					$categories = $entry->getCategory();

					if ( !empty( $categories ) ) {
						$i = 0;

						foreach ( $categories as $category ) {
							/*
							 * Genreate the category link token URL.
							 */
							$categoryFilterURL = $form->tokenURL( 'admin.php?cn-action=filter&category=' . $category->term_id, 'filter' );

							echo '<a href="' . $categoryFilterURL . '">' . $category->name . '</a>';

							$i++;
							if ( count( $categories ) > $i ) echo ', ';
						}

						unset( $i );
					}

					echo "</td> \n";
					echo '<td >';
						echo '<strong>' . __( 'On', 'connections' ) . ':</strong> ' . $entry->getFormattedTimeStamp( 'm/d/Y g:ia' ) . '<br />';
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

					/*
					 * Check if the entry has relations. Count the relations and then cycle thru each relation.
					 * Before the out check that the related entry still exists. If it does and the current user
					 * has edit capabilites the edit link will be displayed. If the user does not have edit capabilities
					 * the only the relation will be shown. After all relations have been output insert a <br>
					 * for spacing [@TODO: NOTE: this should be done with styles].
					 */
					if ( $entry->getFamilyMembers() ) {
						$count = count( $entry->getFamilyMembers() );
						$i = 0;

						foreach ( $entry->getFamilyMembers() as $key => $value ) {
							$relation = new cnEntry();
							$relation->set( $key );
							$editRelationTokenURL = $form->tokenURL( 'admin.php?page=connections&action=edit&id=' . $relation->getId(), 'entry_edit_' . $relation->getId() );

							if ( $relation->getId() ) {
								if ( current_user_can( 'connections_edit_entry' ) ) {
									echo '<strong>' . $instance->options->getFamilyRelation( $value ) . ':</strong> ' . '<a href="' . $editRelationTokenURL . '" title="' . __( 'Edit', 'connections' ) . ' ' . $relation->getFullFirstLastName() . '">' . $relation->getFullFirstLastName() . '</a><br />' . "\n";
								}
								else {
									echo '<strong>' . $instance->options->getFamilyRelation( $value ) . ':</strong> ' . $relation->getFullFirstLastName() . '<br />' . "\n";
								}
							}

							if ( $count - 1 == $i ) echo '<br />'; // Insert a break after all connections are listed.
							$i++;
							unset( $relation );
						}
						unset( $i );
						unset( $count );
					}

					if ( $entry->getContactFirstName() || $entry->getContactLastName() ) echo '<strong>' . __( 'Contact', 'connections' ) . ':</strong> ' . $entry->getContactFirstName() . ' ' . $entry->getContactLastName() . '<br />';
					if ( $entry->getTitle() ) echo '<strong>' . __( 'Title', 'connections' ) . ':</strong> ' . $entry->getTitle() . '<br />';
					if ( $entry->getOrganization() && $entry->getEntryType() !== 'organization' ) echo '<strong>' . __( 'Organization', 'connections' ) . ':</strong> ' . $entry->getOrganization() . '<br />';
					if ( $entry->getDepartment() ) echo '<strong>' . __( 'Department', 'connections' ) . ':</strong> ' . $entry->getDepartment() . '<br />';

					$addresses = $entry->getAddresses();
					//print_r($addresses);

					if ( ! empty( $addresses ) ) {
						foreach ( $addresses as $address ) {
							$outCache = array();

							echo '<div style="margin: 10px 0;">';
							( $address->preferred ) ? $preferred = '*' : $preferred = '';

							if ( ! empty( $address->name ) ) echo '<span style="display: block"><strong>' , $address->name , $preferred , '</strong></span>';
							if ( ! empty( $address->line_1 ) ) echo '<span style="display: block">' , $address->line_1 , '</span>';
							if ( ! empty( $address->line_2 ) ) echo '<span style="display: block">' , $address->line_2 , '</span>';
							if ( ! empty( $address->line_3 ) ) echo '<span style="display: block">' , $address->line_3 , '</span>';

							if ( ! empty( $address->city ) ) $outCache[] = '<span>' . $address->city . '</span>';
							if ( ! empty( $address->state ) ) $outCache[] = '<span>' . $address->state . '</span>';
							if ( ! empty( $address->zipcode ) ) $outCache[] = '<span>' . $address->zipcode . '</span>';

							if ( ! empty( $outCache ) ) echo '<span style="display: block">' , implode( '&nbsp;', $outCache ) , '</span>';

							if ( ! empty( $address->country ) ) echo '<span style="display: block">' , $address->country , '</span>';
							if ( ! empty( $address->latitude ) && ! empty( $address->longitude ) ) echo '<span style="display: block">' , '<strong>' , __( 'Latitude', 'connections' ) , ':</strong>' , ' ' , $address->latitude , ' ' , '<strong>' , __( 'Longitude', 'connections' ) , ':</strong>' , ' ', $address->longitude , '</span>';
							echo '</div>';
						}

						unset( $outCache );
					}
					echo '</td>' , "\n";

					echo '<td>';

					$phoneNumbers = $entry->getPhoneNumbers();

					if ( ! empty( $phoneNumbers ) ) {
						echo '<div class="phone-numbers">';

						foreach ( $phoneNumbers as $phone ) {
							( $phone->preferred ) ? $preferred = '*' : $preferred = '';

							echo '<span class="phone"><strong>' , $phone->name , '</strong>: ' ,  $phone->number , $preferred , '</span>';
						}

						echo '</div>';
					}

					$emailAddresses = $entry->getEmailAddresses();

					if ( ! empty( $emailAddresses ) ) {
						echo '<div class="email-addresses">';

						foreach ( $emailAddresses as $email ) {
							( $email->preferred ) ? $preferred = '*' : $preferred = '';

							echo '<span class="email"><strong>' , $email->name , ':</strong> <a href="mailto:' , $email->address , '">' , $email->address , '</a>' , $preferred , '</span>';
						}

						echo '</div>';
					}

					$imIDs = $entry->getIm();

					if ( ! empty( $imIDs )  ) {
						echo '<div class="im-ids">';

						foreach ( $imIDs as $im ) {
							( $im->preferred ) ? $preferred = '*' : $preferred = '';

							echo '<span class="im"><strong>' , $im->name , ':</strong> ' , $im->id , $preferred , '</span>';
						}

						echo '</div>';
					}

					$socialNetworks = $entry->getSocialMedia();

					if ( ! empty( $socialNetworks ) ) {
						echo '<div class="social-networks">';

						foreach ( $entry->getSocialMedia() as $network ) {
							( $network->preferred ) ? $preferred = '*' : $preferred = '';

							echo '<span class="social-network"><strong>' , $network->name , ':</strong> <a target="_blank" href="' , $network->url , '">' , $network->url . '</a>' , $preferred , '</span>';
						}

						echo '</div>';
					}

					$links = $entry->getLinks();

					if ( ! empty( $links ) ) {
						echo '<div class="links">';

						foreach ( $links as $link ) {
							( $link->preferred ) ? $preferred = '*' : $preferred = '';

							echo '<span class="link"><strong>' , $link->name , ':</strong> <a target="_blank" href="' , $link->url , '">' , $link->url , '</a>' , $preferred , '</span>';
						}

						echo '</div>';
					}

					echo "</td> \n";

					echo "<td>";
					$entry->getDateBlock();
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
												<span style="display: block;"><strong>' . __( 'Date Added', 'connections' ) . ':</strong> ' . $entry->getDateAdded( 'm/d/Y g:ia' ) . '</span>
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
