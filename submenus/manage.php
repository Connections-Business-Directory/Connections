<?php
function connectionsShowViewPage( $action = NULL )
{
	global $wpdb, $connections;
	
	$connections->displayMessages();
	
	switch ( $action )
	{
		case 'add':
			
			echo '<div class="wrap">
					<div class="icon32" id="icon-connections"><br/></div>
					<h2>Connections : Add Entry</h2>';
			
			/*
			 * Check whether current user can add an entry.
			 */
			if ( current_user_can('connections_add_entry') || current_user_can('connections_add_entry_moderated') )
			{
				add_meta_box('metabox-name', 'Name', array(&$form, 'metaboxName'), $connections->pageHook->add, 'normal', 'high');
				
				$form = new cnFormObjects();
				$entry = new cnEntry();
				
				echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
					
					$attr = array(
								 'action' => 'admin.php?connections_process=true&process=manage&action=add',
								 'method' => 'post',
								 'enctype' => 'multipart/form-data',
								 );
					
					$form->open($attr);
					
					$form->tokenField('add_entry');
					wp_nonce_field('cn-add-metaboxes');
					wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
					wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
					
					echo '<input type="hidden" name="action" value="save_cn_add_metaboxes" />';
					
					echo '<div id="side-info-column" class="inner-sidebar">';
						do_meta_boxes($connections->pageHook->add, 'side', $entry);
					echo '</div>';
					
					
					echo '<div id="post-body" class="has-sidebar">';
						echo '<div id="post-body-content" class="has-sidebar-content">';
							do_meta_boxes($connections->pageHook->add, 'normal', $entry);
						echo '</div>';
					echo '</div>';
					
					$form->close();
						
				echo '</div>';
				?>
				
				<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					// close postboxes that should be closed
					$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					// postboxes setup
					postboxes.add_postbox_toggles('<?php echo $connections->pageHook->add ?>');
				});
				//]]>
				</script>
				
				<?php
			
				unset($entry);
			}
			else
			{
				$connections->setErrorMessage('capability_add');
			}
		break;
		
		case 'copy':
			
			echo '<div class="wrap">
					<div class="icon32" id="icon-connections"><br/></div>
					<h2>Connections : Copy Entry</h2>';
			
			/*
			 * Check whether current user can add an entry.
			 */
			if ( current_user_can('connections_add_entry') || current_user_can('connections_add_entry_moderated') )
			{
				$id = esc_attr($_GET['id']);
				check_admin_referer('entry_copy_' . $id);
				
				add_meta_box('metabox-name', 'Name', array(&$form, 'metaboxName'), $connections->pageHook->manage, 'normal', 'high');
				
				$form = new cnFormObjects();
				$entry = new cnEntry( $connections->retrieve->entry($id) );
				
				echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
					
					$attr = array(
								 'action' => 'admin.php?connections_process=true&process=manage&action=add&id=' . $id,
								 'method' => 'post',
								 'enctype' => 'multipart/form-data',
								 );
					
					$form->open($attr);
					
					$form->tokenField('add_entry');
					wp_nonce_field('cn-manage-metaboxes');
					wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
					wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
					
					echo '<input type="hidden" name="action" value="save_cn_add_metaboxes" />';
					
					echo '<div id="side-info-column" class="inner-sidebar">';
						do_meta_boxes($connections->pageHook->manage, 'side', $entry);
					echo '</div>';
					
					
					echo '<div id="post-body" class="has-sidebar">';
						echo '<div id="post-body-content" class="has-sidebar-content">';
							do_meta_boxes($connections->pageHook->manage, 'normal', $entry);
						echo '</div>';
					echo '</div>';
					
					$form->close();
					
				echo '</div>';
				?>
				
				<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					// close postboxes that should be closed
					$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					// postboxes setup
					postboxes.add_postbox_toggles('<?php echo $connections->pageHook->manage ?>');
				});
				//]]>
				</script>
				
				<?php
			
				unset($entry);
			}
			else
			{
				$connections->setErrorMessage('capability_add');
			}
		break;
		
		case 'edit':
			
			echo '<div class="wrap">
					<div class="icon32" id="icon-connections"><br/></div>
					<h2>Connections : Edit Entry</h2>';
					
			/*
			 * Check whether the current user can edit entries.
			 */
			if ( current_user_can('connections_edit_entry') || current_user_can('connections_edit_entry_moderated') )
			{
				$id = esc_attr($_GET['id']);
				check_admin_referer('entry_edit_' . $id);
				
				add_meta_box('metabox-name', 'Name', array(&$form, 'metaboxName'), $connections->pageHook->manage, 'normal', 'high');
				
				$form = new cnFormObjects();
				$entry = new cnEntry( $connections->retrieve->entry($id) );
				
				echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
					
					$attr = array(
								 'action' => 'admin.php?connections_process=true&process=manage&action=update&id=' . $id,
								 'method' => 'post',
								 'enctype' => 'multipart/form-data',
								 );
					
					$form->open($attr);
					
					$form->tokenField('update_entry');
					wp_nonce_field('cn-manage-metaboxes');
					wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
					wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
					
					echo '<input type="hidden" name="action" value="save_cn_add_metaboxes" />';
					
					echo '<div id="side-info-column" class="inner-sidebar">';
						do_meta_boxes($connections->pageHook->manage, 'side', $entry);
					echo '</div>';
					
					
					echo '<div id="post-body" class="has-sidebar">';
						echo '<div id="post-body-content" class="has-sidebar-content">';
							do_meta_boxes($connections->pageHook->manage, 'normal', $entry);
						echo '</div>';
					echo '</div>';
					
					$form->close();
					
				echo '</div>';
				
				?>
				
				<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					// close postboxes that should be closed
					$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					// postboxes setup
					postboxes.add_postbox_toggles('<?php echo $connections->pageHook->manage ?>');
				});
				//]]>
				</script>
				
				<?php
				
				unset($entry);
			}
			else
			{
				$connections->setErrorMessage('capability_edit');
			}
		break;
		
		default:
			$form = new cnFormObjects();
			$categoryObjects = new cnCategoryObjects();
			$url = new cnURL();
			
			$page = $connections->currentUser->getFilterPage('manage');
			$offset = ( $page->current - 1 ) * $page->limit;
			
			echo '<div class="wrap">
					<div class="icon32" id="icon-connections"><br/></div>
					<h2>Connections : Manage</h2>';
			
			/*
			 * Check whether user can view the entry list
			 */
			if(current_user_can('connections_manage'))
			{
				?>
					
					<?php
						$retrieveAttr['list_type'] = $connections->currentUser->getFilterEntryType();
						$retrieveAttr['category'] = $connections->currentUser->getFilterCategory();
						$retrieveAttr['visibility'] = $connections->currentUser->getFilterVisibility();
						$retrieveAttr['status'] = $connections->currentUser->getFilterStatus();
						
						$retrieveAttr['limit'] = $page->limit;
						$retrieveAttr['offset'] = $offset;
						
						if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) )
						{
							$searchResults = $connections->retrieve->search( array( 'search' => $_GET['s'] ) );
							//print_r($searchResults);
							
							$retrieveAttr['id'] = $searchResults;
							
							$results = ( ! empty($searchResults) ) ? $connections->retrieve->entries($retrieveAttr) : array();
							//print_r($connections->lastQuery);
						}
						else
						{
							$results = $connections->retrieve->entries($retrieveAttr);
							//print_r($connections->lastQuery);
						}
						
					?>
						
						<?php if ( current_user_can('connections_edit_entry') ) { ?>
						<ul class="subsubsub">
							<li><a <?php if ( $connections->currentUser->getFilterStatus() == 'all' ) echo 'class="current" ' ?> href="admin.php?page=connections_manage&status=all">All</a> | </li>
							<li><a <?php if ( $connections->currentUser->getFilterStatus() == 'approved' ) echo 'class="current" ' ?> href="admin.php?page=connections_manage&status=approved">Approved <span class="count">(<?php echo $connections->recordCountApproved; ?>)</span></a> | </li>
							<li><a <?php if ( $connections->currentUser->getFilterStatus() == 'pending' ) echo 'class="current" ' ?> href="admin.php?page=connections_manage&status=pending">Moderate <span class="count">(<?php echo $connections->recordCountPending; ?>)</span></a></li>
						</ul>
						<?php } ?>
						
						<form action="admin.php?connections_process=true&process=manage&action=do" method="post">
						
						<p class="search-box">
							<label class="screen-reader-text" for="post-search-input">Search Entries:</label>
							<input type="text" id="entry-search-input" name="s" value="<?php if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) echo $_GET['s'] ; ?>" />
							<input type="submit" name="" id="search-submit" class="button" value="Search Entries"  />
						</p>
						
						<?php $form->tokenField('bulk_action'); ?>
						
						<div class="tablenav">
							
							<div class="alignleft actions">
								<?php
									echo '<select class="postform" id="category" name="category">';
										echo '<option value="-1">Show All Categories</option>';
										echo $categoryObjects->buildCategoryRow('option', $connections->retrieve->categories(), 0, $connections->currentUser->getFilterCategory());
									echo '</select>';
									
									echo $form->buildSelect('entry_type', array('all'=>'Show All Enties', 'individual'=>'Show Individuals', 'organization'=>'Show Organizations', 'family'=>'Show Families'), $connections->currentUser->getFilterEntryType());
								?>
								
								<?php
									/*
									 * Builds the visibilty select list base on current user capabilities.
									 */
									if (current_user_can('connections_view_public') || $connections->options->getAllowPublic()) $visibilitySelect['public'] = 'Show Public';
									if (current_user_can('connections_view_private'))	$visibilitySelect['private'] = 'Show Private';
									if (current_user_can('connections_view_unlisted'))	$visibilitySelect['unlisted'] = 'Show Unlisted';
									
									if (isset($visibilitySelect))
									{
										/*
										 * Add the 'Show All' option and echo the list.
										 */
										$showAll['all'] = 'Show All';
										$visibilitySelect = $showAll + $visibilitySelect;
										echo $form->buildSelect('visibility_type', $visibilitySelect, $connections->currentUser->getFilterVisibility());
									}
								?>
								<input id="doaction" class="button-secondary action" type="submit" name="filter" value="Filter" />
								<input type="hidden" name="formId" value="do_action" />
								<input type="hidden" name="token" value="<?php echo $form->token("do_action"); ?>" />
							</div>
							
							<div class="tablenav-pages">
								<?php
									
									echo '<span class="displaying-num">Displaying ' , $connections->resultCount , ' of ' , $connections->resultCountNoLimit , ' records.</span>';
									
									/*
									 * // START --> Pagination
									 * 
									 * Grab the pagination data again incase a filter reset the values
									 * or the user input an invalid number which the retrieve query would have reset.
									 */
									$page = $connections->currentUser->getFilterPage('manage');
									
									$pageCount = ceil( $connections->resultCountNoLimit / $page->limit );
									
									if ( $pageCount > 1 )
									{
										$pageDisabled = array();
										$pageFilterURL = array();
										$pageValue = array();
										$currentPageURL = add_query_arg( array( 'page' => FALSE , 'connections_process' => TRUE , 'process' => 'manage' , 'action' => 'filter' )  );
										
										$pageValue['first_page'] = 1;
										$pageValue['previous_page'] = ( $page->current - 1 >= 1 ) ? $page->current - 1 : 1;
										$pageValue['next_page'] = ( $page->current + 1 <= $pageCount ) ? $page->current + 1 : $pageCount;
										$pageValue['last_page'] = $pageCount;
										
										( $page->current > 1 ) ? $pageDisabled['first_page'] = '' : $pageDisabled['first_page'] = ' disabled';
										( $page->current - 1 >= 1 ) ? $pageDisabled['previous_page'] = '' : $pageDisabled['previous_page'] = ' disabled';
										( $page->current + 1 <= $pageCount ) ? $pageDisabled['next_page'] = '' : $pageDisabled['next_page'] = ' disabled';
										( $page->current < $pageCount ) ? $pageDisabled['last_page'] = '' : $pageDisabled['last_page'] = ' disabled';
										
										/*
										 * Genreate the page link token URL.
										 */
										//print_r( add_query_arg( array( 'pg' => $pageValue['first_page'] ) , $currentPageURL ) );
										$pageFilterURL['first_page'] = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['first_page'] ) , $currentPageURL ) , 'filter');
										$pageFilterURL['previous_page'] = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['previous_page'] ) , $currentPageURL ) , 'filter');
										$pageFilterURL['next_page'] = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['next_page'] ) , $currentPageURL ) , 'filter');
										$pageFilterURL['last_page'] = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['last_page'] ) , $currentPageURL ) , 'filter');
										
										echo '<span class="page-navigation" id="page-input">';
											
											echo '<a href="' . $pageFilterURL['first_page'] . '" title="Go to the first page" class="first-page' , $pageDisabled['first_page'] , '">«</a> ';
											echo '<a href="' . $pageFilterURL['previous_page'] . '" title="Go to the previous page" class="prev-page' , $pageDisabled['previous_page'] , '">‹</a> ';
											
											echo '<span class="paging-input"><input type="text" size="2" value="' . $page->current . '" name="pg" title="Current page" class="current-page"> of <span class="total-pages">' . $pageCount . '</span></span> ';
											
											echo '<a href="' . $pageFilterURL['next_page'] . '" title="Go to the next page" class="next-page' , $pageDisabled['next_page'] , '">›</a> ';
											echo '<a href="' . $pageFilterURL['last_page'] . '" title="Go to the last page" class="last-page' , $pageDisabled['last_page'] , '">»</a>';
											
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
														
							if ( current_user_can('connections_edit_entry') || current_user_can('connections_delete_entry') )
							{
								echo '<div class="alignleft actions">';
									echo '<select name="action">';
										echo '<option value="" SELECTED>Bulk Actions</option>';
											
											$bulkActions = array();
											
											if ( current_user_can('connections_edit_entry')  || current_user_can('connections_edit_entry_moderated') )
											{
												$bulkActions['unapprove'] = 'Unapprove';
												$bulkActions['approve'] = 'Approve';
												$bulkActions['public'] = 'Set Public';
												$bulkActions['private'] = 'Set Private';
												$bulkActions['unlisted'] = 'Set Unlisted';
											}
											
											if ( current_user_can('connections_delete_entry') )
											{
												$bulkActions['delete'] = 'Delete';
											}
											
											$bulkActions = apply_filters('cn_manage_bulk_actions', $bulkActions);	
											
											foreach ( $bulkActions as $action => $string )
											{
												echo '<option value="', $action, '">', $string, '</option>';
											}
																	
									echo '</select>';
									echo '<input id="doaction" class="button-secondary action" type="submit" name="doaction" value="Apply" />';
								echo '</div>';
							}
							?>
							
							<div class="tablenav-pages">
								<?php
									echo '<span class="displaying-num">Jump to:</span>';
									
									/*
									 * Dynamically builds the alpha index based on the available entries.
									 */
									$previousLetter = NULL;
									$setAnchor = NULL;
									
									foreach ($results as $row)
									{
										$entry = new cnEntry($row);
										$currentLetter = strtoupper(mb_substr($entry->getSortColumn(), 0, 1));
										if ($currentLetter != $previousLetter)
										{
											$setAnchor .= '<a href="#' . $currentLetter . '">' . $currentLetter . '</a> ';
											$previousLetter = $currentLetter;
										}
									}
									
									echo $setAnchor;
								?>
							</div>
						</div>
						<div class="clear"></div>
						
				       	<table cellspacing="0" class="widefat connections">
							<thead>
					            <tr>
					                <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
									<th class="col" style="width:10%;"></th>
									<th scope="col" colspan="2" style="width:40%;">Name</th>
									<th scope="col" style="width:30%;">Categories</th>
									<th scope="col" style="width:20%;">Last Modified</th>
					            </tr>
							</thead>
							<tfoot>
					            <tr>
					                <th class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
									<th class="col" style="width:10%;"></th>
									<th scope="col" colspan="2" style="width:40%;">Name</th>
									<th scope="col" style="width:30%;">Categories</th>
									<th scope="col" style="width:20%;">Last Modified</th>
					            </tr>
							</tfoot>
							<tbody>
								
								<?php
								
								foreach ($results as $row) {
									/**
									 * @TODO: Use the Output class to show entry details.
									 */								
									$entry = new cnvCard($row);
									$vCard =& $entry;
									
									$currentLetter = strtoupper(mb_substr($entry->getSortColumn(), 0, 1));
									if ($currentLetter != $previousLetter) {
										$setAnchor = "<a name='$currentLetter'></a>";
										$previousLetter = $currentLetter;
									} else {
										$setAnchor = null;
									}
									
									/*
									 * Genreate the edit, copy and delete URLs with nonce tokens.
									 */
									$editTokenURL = $form->tokenURL('admin.php?page=connections_manage&action=edit&id=' . $entry->getId(), 'entry_edit_' . $entry->getId());
									$copyTokenURL = $form->tokenURL('admin.php?page=connections_manage&action=copy&id=' . $entry->getId(), 'entry_copy_' . $entry->getId());
									$deleteTokenURL = $form->tokenURL('admin.php?connections_process=true&process=manage&action=delete&id=' . $entry->getId(), 'entry_delete_' . $entry->getId());
									$approvedTokenURL = $form->tokenURL('admin.php?connections_process=true&process=manage&action=approve&id=' . $entry->getId(), 'entry_status_' . $entry->getId());
									$unapproveTokenURL = $form->tokenURL('admin.php?connections_process=true&process=manage&action=unapprove&id=' . $entry->getId(), 'entry_status_' . $entry->getId());
									
									switch ( $entry->getStatus() )
									{
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
										echo "<th class='check-column' scope='row'><input type='checkbox' value='" . $entry->getId() . "' name='entry[]'/></th> \n";
											echo '<td>';
												//echo $entry->getThumbnailImage( array( 'place_holder' => TRUE ) );
												$entry->getImage( array( 'image' => 'photo' , 'preset' => 'thumbnail' , 'height' => 54 , 'width' => 80 , 'zc' => 2 , 'fallback' => array( 'type' => 'block' , 'string' => 'No Photo Available' ) ) );
											echo '</td>';
											echo '<td  colspan="2">';
												if ($setAnchor) echo $setAnchor;
												echo '<div style="float:right"><a href="#wphead" title="Return to top."><img src="' . WP_PLUGIN_URL . '/connections/images/uparrow.gif" /></a></div>';
												
												if ( current_user_can('connections_edit_entry') || current_user_can('connections_edit_entry_moderated') )
												{
													echo '<a class="row-title" title="Edit ' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '" href="' . $editTokenURL . '"> ' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '</a><br />';
												}
												else
												{
													echo '<strong>' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '</strong>';
												}
												
												echo '<div class="row-actions">';
													$rowActions = array();
													$rowEditActions = array();
													
													$rowActions[] = '<a class="detailsbutton" id="row-' . $entry->getId() . '">Show Details</a>';
													$rowActions[] = $vCard->download( array('anchorText' => 'vCard', 'title' => 'Download vCard', 'return' => TRUE) );
													
													if ( $entry->getStatus() == 'approved' && current_user_can('connections_edit_entry') ) $rowEditActions[] = '<a class="action unapprove" href="' . $unapproveTokenURL . '" title="Unapprove ' . $entry->getFullFirstLastName() . '">Unapprove</a>';
													if ( $entry->getStatus() == 'pending' && current_user_can('connections_edit_entry') ) $rowEditActions[] = '<a class="action approve" href="' . $approvedTokenURL . '" title="Approve ' . $entry->getFullFirstLastName() . '">Approve</a>';

													if ( current_user_can('connections_edit_entry') || current_user_can('connections_edit_entry_moderated') ) $rowEditActions[] = '<a class="editbutton" href="' . $editTokenURL . '" title="Edit ' . $entry->getFullFirstLastName() . '">Edit</a>';
													if ( current_user_can('connections_add_entry') || current_user_can('connections_add_entry_moderated') ) $rowEditActions[] = '<a class="copybutton" href="' . $copyTokenURL . '" title="Copy ' . $entry->getFullFirstLastName() . '">Copy</a>';
													if ( current_user_can('connections_delete_entry') ) $rowEditActions[] = '<a class="submitdelete" onclick="return confirm(\'You are about to delete this entry. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . $deleteTokenURL . '" title="Delete ' . $entry->getFullFirstLastName() . '">Delete</a>';
													
													if ( ! empty($rowEditActions) ) echo implode(' | ', $rowEditActions) , '<br/>';
													if ( ! empty($rowActions) ) echo implode(' | ', $rowActions);
													
												echo '</div>';
										echo "</td> \n";
										echo "<td > \n";
											
											$categories = $entry->getCategory();
											
											if ( !empty($categories) )
											{
												$i = 0;
												
												foreach ($categories as $category)
												{
													/*
													 * Genreate the category link token URL.
													 */
													$categoryFilterURL = $form->tokenURL('admin.php?connections_process=true&process=manage&action=filter&category_id=' . $category->term_id, 'filter');
													
													echo '<a href="' . $categoryFilterURL . '">' . $category->name . '</a>';
													
													$i++;
													if ( count($categories) > $i ) echo ', ';
												}
												
												unset($i);
											}
											
										echo "</td> \n";											
										echo '<td >';
											echo '<strong>On:</strong> ' . $entry->getFormattedTimeStamp('m/d/Y g:ia') . '<br />';
											echo '<strong>By:</strong> ' . $entry->getEditedBy(). '<br />';
											echo '<strong>Visibility:</strong> ' . $entry->displayVisibiltyType();
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
											if ($entry->getFamilyMembers())
											{
												$count = count($entry->getFamilyMembers());
												$i = 0;
												
												foreach ($entry->getFamilyMembers() as $key => $value)
												{
													$relation = new cnEntry();
													$relation->set($key);
													$editRelationTokenURL = $form->tokenURL('admin.php?page=connections&action=edit&id=' . $relation->getId(), 'entry_edit_' . $relation->getId());
													
													if ($relation->getId())
													{
														if (current_user_can('connections_edit_entry'))
														{
															echo '<strong>' . $connections->options->getFamilyRelation($value) . ':</strong> ' . '<a href="' . $editRelationTokenURL . '" title="Edit ' . $relation->getFullFirstLastName() . '">' . $relation->getFullFirstLastName() . '</a><br />' . "\n";
														}
														else
														{
															echo '<strong>' . $connections->options->getFamilyRelation($value) . ':</strong> ' . $relation->getFullFirstLastName() . '<br />' . "\n";
														}
													}
													
													if ($count - 1 == $i) echo '<br />'; // Insert a break after all connections are listed.
													$i++;
													unset($relation);
												}
												unset($i);
												unset($count);
											}
											
											if ($entry->getContactFirstName() || $entry->getContactLastName()) echo '<strong>Contact:</strong> ' . $entry->getContactFirstName() . ' ' . $entry->getContactLastName() . '<br />';
											if ($entry->getTitle()) echo '<strong>Title:</strong> ' . $entry->getTitle() . '<br />';
											if ($entry->getOrganization() && $entry->getEntryType() !== 'organization' ) echo '<strong>Organization:</strong> ' . $entry->getOrganization() . '<br />';
											if ($entry->getDepartment()) echo '<strong>Department:</strong> ' . $entry->getDepartment() . '<br />';
											
											$addresses = $entry->getAddresses();
											//print_r($addresses);
											
											if ( ! empty($addresses) )
											{
												foreach ($addresses as $address)
												{
													$outCache = array();
													
													echo '<div style="margin: 10px 0;">';
														( $address->preferred ) ? $preferred = '*' : $preferred = '';
														
														if ( ! empty($address->name) ) echo '<span style="display: block"><strong>' , $address->name , $preferred , '</strong></span>';
														if ( ! empty($address->line_1) ) echo '<span style="display: block">' , $address->line_1 , '</span>';
														if ( ! empty($address->line_2) ) echo '<span style="display: block">' , $address->line_2 , '</span>';
														if ( ! empty($address->line_3) ) echo '<span style="display: block">' , $address->line_3 , '</span>';
														
														if ( ! empty($address->city) ) $outCache[] = '<span>' . $address->city . '</span>';
														if ( ! empty($address->state) ) $outCache[] = '<span>' . $address->state . '</span>';
														if ( ! empty($address->zipcode) ) $outCache[] = '<span>' . $address->zipcode . '</span>';
														
														if ( ! empty($outCache) ) echo '<span style="display: block">' , implode('&nbsp;', $outCache) , '</span>';
														
														if ( ! empty($address->country) ) echo '<span style="display: block">' , $address->country , '</span>';
														if ( ! empty($address->latitude) && ! empty($address->longitude) ) echo '<span style="display: block">' , '<strong>Latitude:</strong>' , ' ' , $address->latitude , ' ' , '<strong>Longitude:</strong>' , ' ', $address->longitude , '</span>';
													echo '</div>';														
												}
												
												unset($outCache);
											}
										echo '</td>' , "\n";
										
										echo '<td>';
											
											$phoneNumbers = $entry->getPhoneNumbers();
											
											if ( ! empty($phoneNumbers) )
											{
												echo '<div class="phone-numbers">';
												
												foreach ($phoneNumbers as $phone) 
												{
													( $phone->preferred ) ? $preferred = '*' : $preferred = '';
													
													echo '<span class="phone"><strong>' , $phone->name , '</strong>: ' ,  $phone->number , $preferred , '</span>';
												}
												
												echo '</div>';
											}
											
											$emailAddresses = $entry->getEmailAddresses();
											
											if ( ! empty($emailAddresses) )
											{
												echo '<div class="email-addresses">';
												
												foreach ($emailAddresses as $email)
												{
													( $email->preferred ) ? $preferred = '*' : $preferred = '';
													
													echo '<span class="email"><strong>' , $email->name , ':</strong> <a href="mailto:' , $email->address , '">' , $email->address , '</a>' , $preferred , '</span>';
												}
												
												echo '</div>';
											}
											
											$imIDs = $entry->getIm();
											
											if ( ! empty($imIDs) 	)
											{
												echo '<div class="im-ids">';
												
												foreach ($imIDs as $im)
												{
													( $im->preferred ) ? $preferred = '*' : $preferred = '';
													
													echo '<span class="im"><strong>' , $im->name , ':</strong> ' , $im->id , $preferred , '</span>';
												}
												
												echo '</div>';
											}
											
											$socialNetworks = $entry->getSocialMedia();
											
											if ( ! empty($socialNetworks) )
											{
												echo '<div class="social-networks">';
												
												foreach ($entry->getSocialMedia() as $network)
												{
													( $network->preferred ) ? $preferred = '*' : $preferred = '';
													
													echo '<span class="social-network"><strong>' , $network->name , ':</strong> <a target="_blank" href="' , $network->url , '">' , $network->url . '</a>' , $preferred , '</span>';
												}
												
												echo '</div>';
											}
											
											$links = $entry->getLinks();
											
											if ( ! empty($links) )
											{
												echo '<div class="links">';
												
												foreach ( $links as $link )
												{
													( $link->preferred ) ? $preferred = '*' : $preferred = '';
													
													echo '<span class="link"><strong>' , $link->name , ':</strong> <a target="_blank" href="' , $link->url , '">' , $link->url , '</a>' , $preferred , '</span>';
												}
												
												echo '</div>';
											}
											
										echo "</td> \n";
																				
										echo "<td>";
											if ($entry->getBirthday()) echo "<strong>Birthday:</strong><br />" . $entry->getBirthday() . "<br /><br />";
											if ($entry->getAnniversary()) echo "<strong>Anniversary:</strong><br />" . $entry->getAnniversary();
										echo "</td> \n";
									echo "</tr> \n";
									
									echo "<tr class='child-row-" . $entry->getId() . " entrynotes' id='contact-" . $entry->getId() . "-detail-notes' style='display:none;'>";
										echo "<td colspan='2'>&nbsp;</td> \n";
										//echo "<td >&nbsp;</td> \n";
										echo "<td colspan='3'>";
											if ($entry->getBio()) echo "<strong>Bio:</strong> " . $entry->getBio() . "<br />"; else echo "&nbsp;";
											if ($entry->getNotes()) echo "<strong>Notes:</strong> " . $entry->getNotes(); else echo "&nbsp;";
										echo "</td> \n";
										echo '<td>
											<strong>Entry ID:</strong> ' . $entry->getId() . '<br />' . '
											<strong>Entry Slug:</strong> ' . $entry->getSlug() . '<br />' . '
											<strong>Date Added:</strong> ' . $entry->getDateAdded('m/d/Y g:ia') . '<br />
											<strong>Added By:</strong> ' . $entry->getAddedBy() . '<br />';
											if (!$entry->getImageLinked()) echo "<br /><strong>Image Linked:</strong> No"; else echo "<br /><strong>Image Linked:</strong> Yes";
											if ($entry->getImageLinked() && $entry->getImageDisplay()) echo "<br /><strong>Display:</strong> Yes"; else echo "<br /><strong>Display:</strong> No";
										echo "</td> \n";
									echo "</tr> \n";
																			
								} ?>
							</tbody>
				        </table>
						</form>
						<p style="font-size:smaller; text-align:center">This is version <?php echo $connections->options->getVersion(), '-', $connections->options->getDBVersion(); ?> of Connections.</p>
						
						
				
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
			}
			else
			{
				$connections->setErrorMessage('capability_view_entry_list');
			}
			
		break;
	}
	?>
	</div>
<?php
}
?>