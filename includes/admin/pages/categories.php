<?php

/**
 * The categories admin page.
 *
 * @package     Connections
 * @subpackage  The categories admin page.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function connectionsShowCategoriesPage() {
	/*
	 * Check whether user can edit catgories.
	 */
	if ( !current_user_can( 'connections_edit_categories' ) ) {

		wp_die( '<p id="error-page" style="-moz-background-clip:border;
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
				width:700px">' . __( 'You do not have sufficient permissions to access this page.', 'connections' ) . '</p>' );

	} else {

		global $connections;

		$form = new cnFormObjects();
		$categoryObjects = new cnCategoryObjects();

		if ( isset( $_GET['cn-action'] ) ) {
			$action = $_GET['cn-action'];
		} else {
			$action = NULL;
		}

		if ( $action === 'edit_category' ) {

			$id = esc_attr( $_GET['id'] );
			check_admin_referer( 'category_edit_' . $id );

			?>

			<div class="wrap">
				<div class="form-wrap" style="width:600px; margin: 0 auto;">
					<h2><a name="new"></a><?php _e( 'Edit Category', 'connections' ); ?></h2>

					<?php
					$attr = array(
						'action' => '',
						'method' => 'post',
						'id'     => 'addcat',
						'name'   => 'updatecategory'
					);

					$form->open( $attr );
					$form->tokenField( 'update_category' );

					$categoryObjects->showForm( $connections->retrieve->category( $id ) );
					?>

					<input type="hidden" name="cn-action" value="update_category"/>

					<p class="submit"><a class="button button-warning" href="admin.php?page=connections_categories"><?php _e( 'Cancel', 'connections' ); ?></a> <input class="button-primary" type="submit" value="<?php _e( 'Update Category', 'connections' ); ?>" name="update" class="button"/></p>

					<?php $form->close(); ?>

				</div>
			</div>
			<?php
		} else {
			?>
			<div class="wrap nosubsub">

				<?php echo get_screen_icon( 'connections' ); ?>

				<h2>Connections : <?php _e( 'Categories', 'connections' ); ?></h2>

				<div id="col-container">

					<div id="col-right">
						<div class="col-wrap">
							<?php
							$attr = array(
								'action' => '',
								'method' => 'post'
							);

							$form->open( $attr );
							$form->tokenField( 'bulk_delete_category' );

							?>

								<div class="tablenav">
									<div class="alignleft actions">
										<select name="action">
											<option selected="selected" value=""><?php _e( 'Bulk Actions', 'connections' ); ?></option>
											<option value="delete"><?php _e( 'Delete', 'connections' ); ?></option>
										</select>
										<input type="hidden" name="cn-action" value="category_bulk_actions"/>
										<input type="submit" class="button-secondary action" value="<?php _e( 'Apply', 'connections' ); ?>"/>
									</div>

									<br class="clear"/>
								</div>

								<div class="clear"/></div>

								<table cellspacing="0" class="widefat fixed">
									<thead>
										<tr>
											<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
											<th class="manage-column column-name" id="name" scope="col"><?php _e( 'Name', 'connections' ); ?></th>
											<th class="manage-column column-description" id="description" scope="col"><?php _e( 'Description', 'connections' ); ?></th>
											<th class="manage-column column-slug" id="slug" scope="col"><?php _e( 'Slug', 'connections' ); ?></th>
											<th class="manage-column column-posts" id="posts" scope="col"><?php _e( 'Info', 'connections' ); ?></th>
										</tr>
									</thead>

									<tfoot>
										<tr>
											<th class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
											<th class="manage-column column-name" scope="col"><?php _e( 'Name', 'connections' ); ?></th>
											<th class="manage-column column-description" scope="col"><?php _e( 'Description', 'connections' ); ?></th>
											<th class="manage-column column-slug" scope="col"><?php _e( 'Slug', 'connections' ); ?></th>
											<th class="manage-column column-posts" scope="col"><?php _e( 'Info', 'connections' ); ?></th>
										</tr>
									</tfoot>

									<tbody class="list:cat" id="the-list">

										<?php echo $categoryObjects->buildCategoryRow( 'table', $connections->retrieve->categories() ); ?>

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
								<p><?php _e( '<strong>Note:</strong><br/>Deleting a category which has been assigned to an entry will reassign that entry as <strong>Uncategorized</strong>.', 'connections' ); ?></p>
							</div>

						</div>
					</div><!-- right column -->

					<div id="col-left">
						<div class="col-wrap">
							<div class="form-wrap">
								<h3><?php _e( 'Add New Category', 'connections' ); ?></h3>

								<?php
								$attr = array(
									'action' => '',
									'method' => 'post'
								);

								$form->open( $attr );
								$form->tokenField( 'add_category' );

								$categoryObjects->showForm();
								?>

								<input type="hidden" name="cn-action" value="add_category"/>
								<p class="submit"><input type="submit" value="<?php _e( 'Add New Category', 'connections' ); ?>" name="add" class="button"/></p>

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