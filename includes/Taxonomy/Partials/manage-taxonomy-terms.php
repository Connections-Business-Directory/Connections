<?php
/**
 * @since 10.2
 *
 * @var Connections_Directory\Taxonomy $taxonomy
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

use Connections_Directory\Request;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_nonce;

if ( ! $taxonomy->showUI() ) {

	wp_die( esc_html__( 'Sorry, you are not allowed to edit terms in this taxonomy.', 'connections' ) );
}

if ( ! current_user_can( $taxonomy->getCapabilities()->manage_terms ) ) {

	wp_die(
		'<h1>' . esc_html__( 'You need a higher level of permission.', 'connections' ) . '</h1><p>' . esc_html__( 'Sorry, you are not allowed to manage terms in this taxonomy.', 'connections' ) . '</p>',
		403
	);
}

// // Grab an instance of the Connections object.
// $instance = Connections_Directory();
$form       = new cnFormObjects();
$adminPage  = Request\Admin_Page::input()->value();
$searchTerm = Request\Search::input()->value();

/**
 * @var CN_Term_Admin_List_Table $table
 */
$table = cnTemplatePart::table(
	'term-admin',
	array(
		'screen'   => get_current_screen()->id,
		'taxonomy' => $taxonomy->getSlug(),
	)
);

$table->prepare_items();
?>
<div class="wrap nosubsub">

	<h1 class="wp-heading-inline">Connections : <?php echo esc_html( $taxonomy->getLabels()->name ); ?></h1>

	<?php
	if ( strlen( $searchTerm ) ) {
		echo '<span class="subtitle">';
		printf(
			/* translators: %s: Search query. */
			esc_html__( 'Search results for: %s', 'connections' ),
			'<strong>' . esc_html( $searchTerm ) . '</strong>'
		);
		echo '</span>';
	}
	?>

	<hr class="wp-header-end">

	<div id="ajax-response"></div>

	<form class="search-form wp-clearfix" action="" method="get">
		<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy->getSlug() ); ?>" />
		<input type="hidden" name="page" value="<?php echo esc_attr( $adminPage ); ?>" />
		<?php $table->search_box( $taxonomy->getLabels()->search_items, 'term' ); ?>
	</form>

	<?php $can_edit_terms = current_user_can( $taxonomy->getCapabilities()->edit_terms ); ?>
	<div id="col-container" class="wp-clearfix">

		<div id="col-left">
			<div class="col-wrap">

				<?php
				/**
				 * Fires before the Add Term form for all taxonomies.
				 *
				 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
				 *
				 * @since 10.2
				 *
				 * @param string $taxonomy The taxonomy slug.
				 */
				do_action( "cn_{$taxonomy->getSlug()}_pre_add_form", $taxonomy->getSlug() );
				?>

				<div class="form-wrap">
					<h2><?php echo esc_html( $taxonomy->getLabels()->add_new_item ); ?></h2>
					<?php
					$form->open(
						array(
							'class'  => 'validate',
							'id'     => 'addtag',
							// 'action' => '',
							'method' => 'post',
						)
					);

					_nonce::field( 'add-term' );
					?>
					<input type="hidden" name="cn-action" value="add-term" />
					<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy->getSlug() ); ?>" />

					<div class="form-field form-required term-name-wrap">
						<label for="term-name"><?php _ex( 'Name', 'term name', 'connections' ); ?></label>
						<input name="term-name" id="term-name" type="text" value="" size="40" aria-required="true" />
						<p><?php echo esc_html( $taxonomy->getLabels()->name_field_description ); ?></p>
					</div>

					<div class="form-field term-slug-wrap">
						<label for="term-slug"><?php _e( 'Slug', 'connections' ); ?></label>
						<input name="term-slug" id="term-slug" type="text" value="" size="40" />
						<p><?php echo esc_html( $taxonomy->getLabels()->slug_field_description ); ?></p>
					</div>
					<?php if ( $taxonomy->isHierarchical() ) : ?>
						<div class="form-field term-parent-wrap">
							<label for="term-parent"><?php echo esc_html( $taxonomy->getLabels()->parent_item ); ?></label>
							<?php
							$dropdown_args = array(
								'hide_empty'       => 0,
								'hide_if_empty'    => false,
								'taxonomy'         => $taxonomy->getSlug(),
								'name'             => 'term-parent',
								'orderby'          => 'name',
								'hierarchical'     => true,
								'show_option_none' => __( 'None', 'connections' ),
							);

							/**
							 * Filter the taxonomy parent drop-down on the Edit Term page.
							 *
							 * @since 10.2
							 *
							 * @param array   $dropdown_args    {
							 *                                  An array of taxonomy parent drop-down arguments.
							 *
							 * @type int|bool $hide_empty       Whether to hide terms not attached to any posts. Default 0|false.
							 * @type bool     $hide_if_empty    Whether to hide the drop-down if no terms exist. Default false.
							 * @type string   $taxonomySlug     The taxonomy slug.
							 * @type string   $name             Value of the name attribute to use for the drop-down select element.
							 *                                      Default 'parent'.
							 * @type string   $orderby          The field to order by. Default 'name'.
							 * @type bool     $hierarchical     Whether the taxonomy is hierarchical. Default true.
							 * @type string   $show_option_none Label to display if there are no terms. Default 'None'.
							 * }
							 *
							 * @param string  $taxonomySlug     The taxonomy slug.
							 */
							$dropdown_args = apply_filters(
								'cn_taxonomy_parent_dropdown_args',
								$dropdown_args,
								$taxonomy->getSlug()
							);

							cnTemplatePart::walker( 'term-select', $dropdown_args );
							?>

							<?php if ( 'category' === $taxonomy->getSlug() ) : ?>
								<p class="description"><?php esc_html_e( 'Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.', 'connections' ); ?></p>
							<?php else : ?>
								<p class="description"><?php echo esc_html( $taxonomy->getLabels()->parent_field_description ); ?></p>
							<?php endif; ?>
						</div><!-- /term-parent-wrap -->
					<?php endif; // isHierarchical(). ?>
					<div class="form-field term-description-wrap">
						<?php

						$tinymcePlugins = array(
							'tabfocus',
							'paste',
							'wordpress',
							'wplink',
							'wpdialogs',
						);

						wp_editor(
							'',
							'term-description',
							array(
								'media_buttons' => false,
								'tinymce'       => array(
									'editor_selector'   => 'tinymce',
									'toolbar1'          => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, alignleft, aligncenter, alignright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
									'toolbar2'          => '',
									'inline_styles'     => true,
									'relative_urls'     => false,
									'remove_linebreaks' => false,
									'plugins'           => implode( ',', $tinymcePlugins ),
								),
							)
						);

						?>
					</div>

					<?php
					/**
					 * Fires after the Add Term form fields for hierarchical taxonomies.
					 *
					 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
					 *
					 * @since 10.2
					 *
					 * @param string $taxonomySlug The taxonomy slug.
					 */
					do_action( "cn_{$taxonomy->getSlug()}_add_form_fields", $taxonomy->getSlug() );
					?>
					<p class="submit">
						<?php submit_button( $taxonomy->getLabels()->add_new_item, 'primary', 'submit', false ); ?>
					</p>
					<?php

					/**
					 * Fires at the end of the Add Term form for all taxonomies.
					 *
					 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
					 *
					 * @since 10.2
					 *
					 * @param string $taxonomySlug The taxonomy slug.
					 */
					do_action( "cn_{$taxonomy->getSlug()}_add_form", $taxonomy->getSlug() );
					?>

					<?php echo '</form>'; ?>
				</div><!-- /form-wrap -->
			</div><!-- /col-wrap -->
		</div><!-- /col-left -->

		<div id="col-right">
			<div class="col-wrap">
				<?php
				$form->open(
					array(
						'action' => '',
						'method' => 'post',
					)
				);
				?>
				<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy->getSlug() ); ?>" />
				<input type="hidden" name="cn-action" value="bulk-term-action" />
				<?php
				$table->display();
				echo '</form>';
				?>

				<script type="text/javascript">
					/* <![CDATA[ */
					( function( $ ) {
						$( document ).ready( function() {

							$( '#doaction' ).on( 'click', function( event ) {

								if ( 'delete' === $( 'select[name^="action"]' ).val() ) {

									// Confirms the deletion, a negative response means the deletion must not be executed.
									var response = showNotice.warn();

									if ( ! response ) {
										event.preventDefault();
									}
								}
							});

							$( '#the-list' ).on( 'click', '.delete-tag', function( event ) {

								// Confirms the deletion, a negative response means the deletion must not be executed.
								var response = showNotice.warn();

								if ( ! response ) {
									event.preventDefault();
								}
							});
						});
					})( jQuery );
					/* ]]> */
				</script>

				<?php if ( 'category' === $taxonomy->getSlug() ) : ?>
				<div class="form-wrap edit-term-notes">
					<p>
						<?php

						$defaultCategory = get_option( 'connections_category' );

						if ( is_array( $defaultCategory ) ) {

							$categoryID = _array::get( $defaultCategory, 'default', false );

							if ( is_numeric( $categoryID ) ) {

								$category = cnTerm::get( (int) $defaultCategory );

								printf(
									/* translators: %s: Default category. */
									esc_html__( 'Deleting a category does not delete the posts in that category. Instead, posts that were only assigned to the deleted category are set to the default category %s. The default category cannot be deleted.', 'connections' ),
									'<strong>' . esc_html( $category->name ) . '</strong>'
								);
							}
						}

						?>
					</p>
				</div>
					<?php
				endif;

				/**
				 * Fires after the taxonomy list table.
				 *
				 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
				 *
				 * @since 10.2
				 *
				 * @param string $taxonomy The taxonomy name.
				 */
				do_action( "cn_after-{$taxonomy->getSlug()}-table", $taxonomy->getSlug() );
				?>

			</div><!-- /col-wrap -->
		</div><!-- /col-right -->
	</div><!-- /col-container -->
</div><!-- /wrap -->
