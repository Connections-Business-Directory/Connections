<?php
/**
 * The non-hierarchical taxonomy metabox.
 *
 * Based on @see post_tags_meta_box()
 *
 * @since 10.2
 *
 * @var Connections_Directory\Taxonomy $taxonomy
 * @var cnEntry $entry   An instance of the cnEntry object.
 * @var array   $metabox {
 *     Hierarchical taxonomy metabox arguments.
 *
 *     @type string   $id       Metabox 'id' attribute.
 *     @type string   $title    Metabox title.
 *     @type callable $callback Metabox display callback.
 *     @type array    $args     Extra meta box arguments.
 * }
 */

$defaults = array(
	'taxonomy' => $taxonomy->getSlug(),
);

if ( ! isset( $metabox['args'] ) || ! is_array( $metabox['args'] ) ) {

	$args = array();

} else {

	$args = $metabox['args'];
}

$parsed_args           = wp_parse_args( $args, $defaults );
$tax_name              = esc_attr( $taxonomy->getSlug() );
$user_can_assign_terms = current_user_can( $taxonomy->getCapabilities()->assign_terms );
$delimiter             = _x( ',', 'Term delimiter.', 'connections' );
$terms_to_edit         = '';

$terms = cnTerm::getRelationships(
	$entry->getID(),
	$taxonomy->getSlug(),
	array(
		'fields' => 'names',
	)
);

if ( is_array( $terms ) ) {

	$term_names = array();

	foreach ( $terms as $name ) {
		$term_names[] = $name;
	}

	$terms_to_edit = esc_attr( implode( ',', $term_names ) );
}
?>
<div class="tagsdiv" id="<?php echo $tax_name; ?>">
	<div class="jaxtag">
		<div class="nojs-tags hide-if-js" style="display: block;">
			<label for="tax-input-<?php echo $tax_name; ?>"><?php echo $taxonomy->getLabels()->add_or_remove_items; ?></label>
			<p><textarea name="<?php echo $args['name']; ?>" rows="3" cols="20" class="the-tags" id="tax-input-<?php echo $tax_name; ?>" <?php disabled( ! $user_can_assign_terms ); ?> aria-describedby="new-tag-<?php echo $tax_name; ?>-desc"><?php echo str_replace( ',', $delimiter . ' ', $terms_to_edit ); // textarea_escaped by esc_attr() ?></textarea></p>
		</div>
		<?php if ( $user_can_assign_terms ) : ?>
			<p class="howto" id="new-tag-<?php echo $tax_name; ?>-desc"><?php echo $taxonomy->getLabels()->separate_items_with_commas; ?></p>
		<?php elseif ( empty( $terms_to_edit ) ) : ?>
			<p><?php echo $taxonomy->getLabels()->no_terms; ?></p>
		<?php endif; ?>
	</div>
</div>

