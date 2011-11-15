<?php
function connectionsShowCategoriesPage()
{
	/*
	 * Check whether user can edit catgories.
	 */
	if (!current_user_can('connections_edit_categories'))
	{
		wp_die('<p id="error-page" style="-moz-background-clip:border;
				-moz-border-radius:11px;
				background:#FFFFFF none repeat scroll 0 0;
				border:1px solid #DFDFDF;
				color:#333333;
				display:block;
				font-size:12px;
				line-height:18px;
				margin:25px auto 20px;
				padding:1em 2em;
				text-align:center;
				width:700px">You do not have sufficient permissions to access this page.</p>');
	}
	else
	{
		global $connections;
		$form = new cnFormObjects();
		$categoryObjects = new cnCategoryObjects();
		
		if ( isset($_GET['action']) )
		{
			$action = $_GET['action'];
		}
		else
		{
			$action = NULL;
		}
		
		if ($action === 'edit')
		{
			$id = esc_attr($_GET['id']);
			check_admin_referer('category_edit_' . $id);
			?>
			<div class="wrap">
				<div class="form-wrap" style="width:600px; margin: 0 auto;">
					<h2><a name="new"></a>Edit Category</h2>
			
					<?php
						$attr = array(
									 'action' => 'admin.php?connections_process=true&process=category&action=update',
									 'method' => 'post',
									 'id' => 'addcat',
									 'name' => 'updatecategory'
									 );
						
						$form->open($attr);
						$form->tokenField('update_category');
						
						$categoryObjects->showForm($connections->retrieve->category($id));
					?>
					
					<p class="submit"><a class="button button-warning" href="admin.php?page=connections_categories">Cancel</a> <input class="button-primary" type="submit" value="Update Category" name="update" class="button"/></p>
					
					<?php $form->close(); ?>
			
				</div>
			</div>
			<?php
		}
		else
		{
			?>
				<div class="wrap nosubsub">
					<div class="icon32" id="icon-connections"><br/></div>
					<h2>Connections : Categories</h2>
					<?php echo $connections->displayMessages(); ?>
					<div id="col-container">
					
						<div id="col-right">
							<div class="col-wrap">
								<?php
									$attr = array(
												 'action' => 'admin.php?connections_process=true&process=category&action=bulk_delete',
												 'method' => 'post',
												 );
									
									$form->open($attr);
									$form->tokenField('bulk_delete_category');
								?>
								
									<div class="tablenav">
										<div class="alignleft actions">
											<select name="action">
												<option selected="selected" value="">Bulk Actions</option>
												<option value="delete">Delete</option>
											</select>
											<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="Apply"/>
										</div>
										
										<br class="clear"/>
									</div>
									
									<div class="clear"/></div>
								
									<table cellspacing="0" class="widefat fixed">
										<thead>
											<tr>
												<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
												<th class="manage-column column-name" id="name" scope="col">Name</th>
												<th class="manage-column column-description" id="description" scope="col">Description</th>
												<th class="manage-column column-slug" id="slug" scope="col">Slug</th>
												<th class="manage-column column-posts" id="posts" scope="col">Info</th>
											</tr>
										</thead>
									
										<tfoot>
											<tr>
												<th class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
												<th class="manage-column column-name" scope="col">Name</th>
												<th class="manage-column column-description" scope="col">Description</th>
												<th class="manage-column column-slug" scope="col">Slug</th>
												<th class="manage-column column-posts" scope="col">Info</th>
											</tr>
										</tfoot>
									
										<tbody class="list:cat" id="the-list">
											<?php
												echo $categoryObjects->buildCategoryRow('table', $connections->retrieve->categories());
											?>
										</tbody>
									</table>
								<?php $form->close(); ?>
								
								<script type="text/javascript">
									/* <![CDATA[ */
									(function($){
										$(document).ready(function(){
											$('#doaction, #doaction2').click(function(){
												if ( $('select[name^="action"]').val() == 'delete' ) {
													var m = 'You are about to delete the selected category(ies).\n  \'Cancel\' to stop, \'OK\' to delete.';
													return showNotice.warn(m);
												}
											});
										});
									})(jQuery);
									/* ]]> */
								</script>
								
								<div class="form-wrap">
								<p><strong>Note:</strong><br/>Deleting a category which has been assigned to an entry will reassign that entry as <strong>Uncategorized</strong>.</p>
								</div>
							
							</div>
						</div><!-- right column -->
						
						<div id="col-left">
							<div class="col-wrap">
								<div class="form-wrap">
									<h3>Add Category</h3>
										<?php
											$attr = array(
														 'action' => 'admin.php?connections_process=true&process=category&action=add',
														 'method' => 'post',
														 'id' => 'addcat',
														 'name' => 'addcat'
														 );
											
											$form->open($attr);
											$form->tokenField('add_category');
											
											$categoryObjects->showForm();
										?>
										<p class="submit"><input type="submit" value="Save Category" name="add" class="button"/></p>
									<?php $form->close(); ?>
								</div>
							</div>
						</div><!-- left column -->
					
					</div><!-- Column container -->
				</div>
			<?php
		}
		
	}
}
?>