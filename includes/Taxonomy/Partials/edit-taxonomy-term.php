<?php
/**
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

use Connections_Directory\Request;
use Connections_Directory\Utility\_nonce;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @since 10.2
 *
 * @var Connections_Directory\Taxonomy $taxonomy
 */

$id = Request\ID::input()->value();
check_admin_referer( "{$taxonomy->getSlug()}_edit_{$id}" );

$form = new cnFormObjects();
$term = cnTerm::get( $id );

$referer = remove_query_arg( array( 'cn-action', 'go-back', 'id', '_wpnonce' ) );

/**
 * Fires before the Edit Term form for all taxonomies.
 *
 * The dynamic portion of the hook name, `$taxonomy`, refers to
 * the taxonomy slug.
 *
 * @since 10.2
 *
 * @param object $tag      Current taxonomy term object.
 * @param string $taxonomy Current $taxonomy slug.
 */
do_action( "cn_{$taxonomy->getSlug()}_pre_edit_form", $term, $taxonomy->getSlug() );

?>

<div class="wrap">
	<h1><?php echo esc_html( $taxonomy->getLabels()->edit_item ); ?></h1>

	<div id="ajax-response"></div>

	<?php
	$form->open(
		array(
			'name'   => 'editterm',
			'class'  => 'validate',
			'id'     => 'edittag',
			// 'action' => '',
			'method' => 'post',
		)
	);

	_nonce::field( 'update-term', $term->term_id );
	?>
	<input type="hidden" name="cn-action" value="update-term" />
	<input type="hidden" name="term-id" value="<?php echo esc_attr( $term->term_id ); ?>"/>
	<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy->getSlug() ); ?>" />
	<?php

	/**
	 * Fires at the beginning of the Edit Term form.
	 *
	 * At this point, the required hidden fields and nonces have already been output.
	 *
	 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
	 *
	 * @since 10.2
	 *
	 * @param cnTerm $term         Current taxonomy term object.
	 * @param string $taxonomySlug Current $taxonomy slug.
	 */
	do_action( "cn_{$taxonomy->getSlug()}_term_edit_form_top", $term, $taxonomy->getSlug() );
	?>
	<table class="form-table" role="presentation">
		<tr class="form-field form-required term-name-wrap">
			<th scope="row"><label for="term-name"><?php _ex( 'Name', 'term name', 'connections' ); ?></label></th>
			<td>
				<input name="term-name" id="term-name" type="text" value="<?php echo esc_attr( $term->name ); ?>" size="40" aria-required="true" />
				<p class="description"><?php echo esc_html( $taxonomy->getLabels()->name_field_description ); ?></p>
			</td>
		</tr>

		<tr class="form-field term-slug-wrap">
			<th scope="row"><label for="term-slug"><?php _e( 'Slug', 'connections' ); ?> </label></th>
			<td>
				<input name="term-slug" id="term-slug" type="text" value="<?php echo esc_attr( $term->slug ); ?>" size="40" />
				<p><?php echo esc_html( $taxonomy->getLabels()->slug_field_description ); ?></p>
			</td>
		</tr>
		<?php if ( $taxonomy->isHierarchical() ) : ?>
		<tr class="form-field term-parent-wrap">
			<th scope="row"><label for="term-parent"><?php echo esc_html( $taxonomy->getLabels()->parent_item ); ?></label></th>
			<td>
				<?php
				cnTemplatePart::walker(
					'term-select',
					array(
						'hide_empty'       => 0,
						'hide_if_empty'    => false,
						'name'             => 'term-parent',
						'orderby'          => 'name',
						'taxonomy'         => $taxonomy->getSlug(),
						'selected'         => $term->parent,
						'exclude_tree'     => $term->term_id,
						'hierarchical'     => true,
						'show_option_none' => __( 'None', 'connections' ),
						// 'return'           => TRUE,
					)
				);
				?>
				<?php if ( 'category' === $taxonomy->getSlug() ) : ?>
					<p class="description"><?php esc_html_e( 'Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.', 'connections' ); ?></p>
				<?php else : ?>
					<p class="description"><?php echo esc_html( $taxonomy->getLabels()->parent_field_description ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; // isHierarchical() ?>
		<tr class="form-field term-description-wrap">
			<th scope="row"><label for="description"><?php _e( 'Description', 'connections' ); ?></label></th>
			<td>
				<?php

				$tinymcePlugins = array(
					'tabfocus',
					'paste',
					'wordpress',
					'wplink',
					'wpdialogs',
				);

				wp_editor(
					wp_kses_post( $term->description ),
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
				<p class="description"><?php echo esc_html( $taxonomy->getLabels()->desc_field_description ); ?></p>
			</td>
		</tr>

		<?php
		/**
		 * Fires after the Edit Term form fields are displayed.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to
		 * the taxonomy slug.
		 *
		 * @since 10.2
		 *
		 * @param object $tag      Current taxonomy term object.
		 * @param string $taxonomy Current taxonomy slug.
		 */
		do_action( "cn_{$taxonomy->getSlug()}_edit_form_fields", $term, $taxonomy->getSlug() );
		?>
	</table>
	<?php
	/**
	 * Fires at the end of the Edit Term form for all taxonomies.
	 *
	 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
	 *
	 * @since 10.2
	 *
	 * @param object $tag      Current taxonomy term object.
	 * @param string $taxonomy Current taxonomy slug.
	 */
	do_action( "{$taxonomy->getSlug()}_edit_form", $term, $taxonomy->getSlug() );
	?>

	<div class="edit-tag-actions">

		<a class="button button-warning" href="<?php echo esc_url( wp_validate_redirect( esc_url_raw( $referer ), admin_url( "admin.php?page=connections_manage_{$taxonomy->getSlug()}_terms" ) ) ); ?>"><?php _e( 'Cancel', 'connections' ); ?></a>

		<?php submit_button( esc_html__( 'Update', 'connections' ), 'primary', null, false ); ?>

		<?php if ( current_user_can( $taxonomy->getCapabilities()->delete_terms, $term->term_id ) ) : ?>
			<span id="delete-link">
				<a class="delete" href="<?php echo esc_url( admin_url( wp_nonce_url( "admin.php?cn-action=delete-term&id={$term->term_id}&taxonomy={$taxonomy->getSlug()}&_wp_http_referer={$referer}", "term_delete_{$term->term_id}" ) ) ); ?>"><?php _e( 'Delete', 'connections' ); ?></a>
			</span>
		<?php endif; ?>

	</div>

	<?php echo '</form>'; ?>

	<script type="text/javascript">
		/* <![CDATA[ */
		( function( $ ) {
			$( document ).ready( function() {

				$( '#edittag' ).on( 'click', '.delete', function( event ) {

					if ( 'undefined' === typeof showNotice ) {
						return true;
					}

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

</div>
