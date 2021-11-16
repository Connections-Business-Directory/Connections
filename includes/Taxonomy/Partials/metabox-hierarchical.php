<?php
/**
 * The hierarchical taxonomy metabox.
 *
 * Based on @see post_categories_meta_box()
 * Removed the popular tab and adding new terms via AJAX.
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
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

$defaults = array(
	'name'     => 'taxonomy_terms',
	'taxonomy' => $taxonomy->getSlug(),
);

if ( ! isset( $metabox['args'] ) || ! is_array( $metabox['args'] ) ) {

	$args = array();

} else {

	$args = $metabox['args'];
}

$parsed_args = wp_parse_args( $args, $defaults );
$tax_name    = esc_attr( $taxonomy->getSlug() );

$parsed_args['selected'] = cnTerm::getRelationships(
	$entry->getID(),
	$taxonomy->getSlug(),
	array(
		'fields' => 'ids',
	)
);

?>
<div id="<?php echo esc_attr( "taxonomy-{$taxonomy->getSlug()}" ); ?>" class="categorydiv">
	<div id="<?php echo esc_attr( "{$taxonomy->getSlug()}-all" ); ?>" class="tabs-panel">
		<?php
		// Allows for an empty term set to be sent. 0 is an invalid term ID and will be ignored by empty() checks.
		echo "<input type='hidden' name='{$parsed_args['name']}[]' value='0' />"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		cnTemplatePart::walker(
			'term-checklist',
			$parsed_args
		);
		?>
	</div>
</div>
