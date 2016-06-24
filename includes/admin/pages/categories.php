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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function connectionsShowCategoriesPage() {

	/*
	 * Check whether user can edit categories.
	 */
	if ( ! current_user_can( 'connections_edit_categories' ) ) {

		wp_die(
			'<p id="error-page" style="-moz-background-clip:border;
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
				width:700px">' . __(
				'You do not have sufficient permissions to access this page.',
				'connections'
			) . '</p>'
		);

	} else {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$form     = new cnFormObjects();
		$taxonomy = 'category';
		$action   = '';

		if ( isset( $_GET['cn-action'] ) ) {

			$action = $_GET['cn-action'];
		}

		if ( $action === 'edit_category' ) {

			$id = absint( $_GET['id'] );
			check_admin_referer( 'category_edit_' . $id );

			$term     = $instance->retrieve->category( $id );
			$category = new cnCategory( $term );

			/**
			 * Fires before the Edit Term form for all taxonomies.
			 *
			 * The dynamic portion of the hook name, `$taxonomy`, refers to
			 * the taxonomy slug.
			 *
			 * @since 3.0.0
			 *
			 * @param object $tag      Current taxonomy term object.
			 * @param string $taxonomy Current $taxonomy slug.
			 */
			do_action( "cn_{$taxonomy}_pre_edit_form", $term, $taxonomy );

			?>

			<div class="wrap">
				<div class="form-wrap" style="width:600px; margin: 0 auto;">
					<h1><a name="new"></a><?php _e( 'Edit Category', 'connections' ); ?></h1>

					<?php
					$attr = array(
						'action' => '',
						'method' => 'post',
						'id'     => 'edit-term',
						'name'   => 'updatecategory'
					);

					$form->open( $attr );
					$form->tokenField( 'update_category' );

					/**
					 * Fires inside the Edit Term form tag.
					 *
					 * The dynamic portion of the hook name, `$taxonomy`, refers to
					 * the taxonomy slug.
					 *
					 * @since 3.7.0
					 */
					do_action( "cn_{$taxonomy}_term_edit_form_tag" );
					?>

					<div class="form-field form-required term-name-wrap">
						<label for="category_name"><?php _e( 'Name', 'connections' ) ?></label>
						<input type="text" aria-required="true" size="40" value="<?php echo esc_attr( $category->getName() ); ?>" id="category_name" name="category_name"/>
						<input type="hidden" value="<?php echo esc_attr( $category->getID() ); ?>" id="category_id" name="category_id"/>

						<p><?php _e( 'The name is how it appears on your site.', 'connections' ); ?></p>
					</div>

					<div class="form-field term-slug-wrap">
						<label for="category_slug"><?php _e( 'Slug', 'connections' ); ?> </label>
						<input type="text" size="40" value="<?php echo esc_attr( $category->getSlug() ); ?>" id="category_slug" name="category_slug"/>

						<p><?php _e(
								'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.',
								'connections'
							); ?></p>
					</div>

					<div class="form-field term-parent-wrap">
						<label for="category_parent"><?php _e( 'Parent', 'connections' ); ?></label>

						<?php
						cnTemplatePart::walker(
							'term-select',
							array(
								'hide_empty'       => 0,
								'hide_if_empty'    => FALSE,
								'name'             => 'category_parent',
								'orderby'          => 'name',
								'taxonomy'         => 'category',
								'selected'         => $category->getParent(),
								'exclude_tree'     => $category->getID(),
								'hierarchical'     => TRUE,
								'show_option_none' => __( 'None', 'connections' ),
								//'return'           => TRUE,
							)
						);
						?>
						<p><?php _e(
								'Categories can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.',
								'connections'
							); ?></p>
					</div>


					<div class="form-field term-description-wrap">
						<?php
						ob_start();

						/*
						 * Now we're going to have to keep track of which TinyMCE plugins
						 * WP core supports based on version, sigh.
						 */
						if ( version_compare( $GLOBALS['wp_version'], '3.8.999', '<' ) ) {

							$tinymcePlugins = array(
								'inlinepopups',
								'tabfocus',
								'paste',
								'wordpress',
								'wplink',
								'wpdialogs'
							);

						} else {

							$tinymcePlugins = array( 'tabfocus', 'paste', 'wordpress', 'wplink', 'wpdialogs' );
						}

						wp_editor(
							wp_kses_post( $category->getDescription() ),
							'category_description',
							array(
								'media_buttons' => FALSE,
								'tinymce'       => array(
									'editor_selector'   => 'tinymce',
									'toolbar1'          => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, alignleft, aligncenter, alignright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
									'toolbar2'          => '',
									'inline_styles'     => TRUE,
									'relative_urls'     => FALSE,
									'remove_linebreaks' => FALSE,
									'plugins'           => implode( ',', $tinymcePlugins )
								)
							)
						);

						echo ob_get_clean();
						?>
					</div>

					<?php
					/**
					 * Fires after the Edit Term form fields are displayed.
					 *
					 * The dynamic portion of the hook name, `$taxonomy`, refers to
					 * the taxonomy slug.
					 *
					 * @since 3.0.0
					 *
					 * @param object $tag      Current taxonomy term object.
					 * @param string $taxonomy Current taxonomy slug.
					 */
					do_action( "cn_{$taxonomy}_edit_form_fields", $term, $taxonomy );

					/**
					 * Fires at the end of the Edit Term form for all taxonomies.
					 *
					 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
					 *
					 * @since 3.0.0
					 *
					 * @param object $tag      Current taxonomy term object.
					 * @param string $taxonomy Current taxonomy slug.
					 */
					do_action( "{$taxonomy}_edit_form", $term, $taxonomy );
					?>

					<input type="hidden" name="cn-action" value="update_category"/>

					<p class="submit">
						<a class="button button-warning" href="admin.php?page=connections_categories"><?php _e( 'Cancel', 'connections' ); ?></a>
						<input type="submit" name="update" id="update" class="button button-primary" value="<?php _e( 'Update Category', 'connections' ); ?>"/>
					</p>

					<?php $form->close(); ?>

				</div>
			</div>
		<?php
		} else {

			/**
			 * @var CN_Term_Admin_List_Table $table
			 */
			$table = cnTemplatePart::table( 'term-admin', array( 'screen' => get_current_screen()->id ) );
			$table->prepare_items();
			?>
			<div class="wrap nosubsub">

				<h1>Connections : <?php _e( 'Categories', 'connections' ); ?></h1>

				<form class="search-form" action="" method="get">

					<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>"/>
					<?php $table->search_box( __( 'Search Categories', 'connections' ), 'category' ); ?>

				</form>
				<br class="clear"/>

				<div id="col-container">

					<div id="col-right">
						<div class="col-wrap">
							<?php

							$attr = array(
								'action' => '',
								'method' => 'post'
							);

							$form->open( $attr );
							//$form->tokenField( 'bulk_delete_category' );
							?>
							<input type="hidden" name="cn-action" value="category_bulk_actions"/>
							<?php
							$table->display();

							$form->close();

							?>

							<br class="clear" />

							<script type="text/javascript">
								/* <![CDATA[ */
								(function ($) {
									$(document).ready(function () {
										$('#doaction, #doaction2').click(function () {
											if ($('select[name^="action"]').val() == 'delete') {
												var m = 'You are about to delete the selected category(ies).\n  \'Cancel\' to stop, \'OK\' to delete.';
												return showNotice.warn(m);
											}
										});
									});
								})(jQuery);
								/* ]]> */
							</script>

							<div class="form-wrap">
								<p><?php _e(
										'<strong>Note:</strong><br/>Deleting a category which has been assigned to an entry will reassign that entry to the default category.',
										'connections'
									); ?></p>
							</div>

							<?php
							/**
							 * Fires after the taxonomy list table.
							 *
							 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
							 *
							 * @since 3.0.0
							 *
							 * @param string $taxonomy The taxonomy name.
							 */
							do_action( "cn_after-{$taxonomy}-table", $taxonomy );
							?>

						</div>
					</div>
					<!-- right column -->

					<div id="col-left">
						<div class="col-wrap">

							<?php
							/**
							 * Fires before the Add Term form for all taxonomies.
							 *
							 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
							 *
							 * @since 3.0.0
							 *
							 * @param string $taxonomy The taxonomy slug.
							 */
							do_action( "cn_{$taxonomy}_pre_add_form", $taxonomy );
							?>

							<div class="form-wrap">
								<h3><?php _e( 'Add New Category', 'connections' ); ?></h3>

								<?php
								$attr = array(
									'id'     => 'add-term',
									'action' => '',
									'method' => 'post'
								);

								$form->open( $attr );
								$form->tokenField( 'add_category' );

								/**
								 * Fires at the beginning of the Add Tag form.
								 *
								 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
								 *
								 * @since 3.7.0
								 */
								do_action( "cn_{$taxonomy}_term_new_form_tag" );
								?>
								<div class="form-field form-required term-name-wrap">
									<label for="category_name"><?php _e( 'Name', 'connections' ); ?></label>
									<input type="text" aria-required="true" size="40" value="" id="category_name" name="category_name"/>
									<input type="hidden" value="" id="category_id" name="category_id"/>

									<p><?php _e( 'The name is how it appears on your site.', 'connections' ); ?></p>
								</div>

								<div class="form-field term-slug-wrap">
									<label for="category_slug"><?php _e( 'Slug', 'connections' ); ?></label>
									<input type="text" size="40" value="" id="category_slug" name="category_slug"/>

									<p><?php _e(
											'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.',
											'connections'
										); ?></p>
								</div>

								<div class="form-field term-parent-wrap">
									<label for="category_parent"><?php _e( 'Parent', 'connections' ); ?></label>

									<?php
									$dropdown_args = array(
										'hide_empty'       => 0,
										'hide_if_empty'    => FALSE,
										'taxonomy'         => 'category',
										'name'             => 'category_parent',
										'orderby'          => 'name',
										'hierarchical'     => TRUE,
										'show_option_none' => __( 'None', 'connections' ),
									);

									/**
									 * Filter the taxonomy parent drop-down on the Edit Term page.
									 *
									 * @since 3.7.0
									 *
									 * @param array   $dropdown_args    {
									 *                                  An array of taxonomy parent drop-down arguments.
									 *
									 * @type int|bool $hide_empty       Whether to hide terms not attached to any posts. Default 0|false.
									 * @type bool     $hide_if_empty    Whether to hide the drop-down if no terms exist. Default false.
									 * @type string   $taxonomy         The taxonomy slug.
									 * @type string   $name             Value of the name attribute to use for the drop-down select element.
									 *                                      Default 'parent'.
									 * @type string   $orderby          The field to order by. Default 'name'.
									 * @type bool     $hierarchical     Whether the taxonomy is hierarchical. Default true.
									 * @type string   $show_option_none Label to display if there are no terms. Default 'None'.
									 * }
									 *
									 * @param string  $taxonomy         The taxonomy slug.
									 */
									$dropdown_args = apply_filters(
										'cn_taxonomy_parent_dropdown_args',
										$dropdown_args,
										'category'
									);

									cnTemplatePart::walker( 'term-select', $dropdown_args );
									?>

									<p><?php _e(
											'Categories can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.',
											'connections'
										); ?></p>
								</div>

								<div class="form-field term-description-wrap">
									<?php
									ob_start();

									/*
									 * Now we're going to have to keep track of which TinyMCE plugins
									 * WP core supports based on version, sigh.
									 */
									if ( version_compare( $GLOBALS['wp_version'], '3.8.999', '<' ) ) {

										$tinymcePlugins = array(
											'inlinepopups',
											'tabfocus',
											'paste',
											'wordpress',
											'wplink',
											'wpdialogs'
										);

									} else {

										$tinymcePlugins = array(
											'tabfocus',
											'paste',
											'wordpress',
											'wplink',
											'wpdialogs'
										);
									}

									wp_editor(
										'',
										'category_description',
										array(
											'media_buttons' => FALSE,
											'tinymce'       => array(
												'editor_selector'   => 'tinymce',
												'toolbar1'          => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, alignleft, aligncenter, alignright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
												'toolbar2'          => '',
												'inline_styles'     => TRUE,
												'relative_urls'     => FALSE,
												'remove_linebreaks' => FALSE,
												'plugins'           => implode( ',', $tinymcePlugins )
											)
										)
									);

									echo ob_get_clean();
									?>

								</div>

								<input type="hidden" name="cn-action" value="add_category"/>

								<?php
								/**
								 * Fires after the Add Term form fields for hierarchical taxonomies.
								 *
								 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
								 *
								 * @since 3.0.0
								 *
								 * @param string $taxonomy The taxonomy slug.
								 */
								do_action( "cn_{$taxonomy}_add_form_fields", $taxonomy );

								submit_button( __( 'Add New Category', 'connections' ), 'primary', 'add' );

								/**
								 * Fires at the end of the Add Term form for all taxonomies.
								 *
								 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
								 *
								 * @since 3.0.0
								 *
								 * @param string $taxonomy The taxonomy slug.
								 */
								do_action( "cn_{$taxonomy}_add_form", $taxonomy );
								?>

								<?php $form->close(); ?>
							</div>
						</div>
					</div>
					<!-- left column -->

				</div>
				<!-- Column container -->
			</div>
		<?php
		}
	}
}
