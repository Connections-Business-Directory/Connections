<?php

use Connections_Directory\Taxonomy;
use Connections_Directory\Taxonomy\Registry;

/**
 * Register a taxonomy.
 *
 * @since 10.2
 *
 * @param string $taxonomy
 * @param array  $args
 *
 * @return Taxonomy|WP_Error
 */
function cn_register_taxonomy( $taxonomy, $args = array() ) {

	// Get the taxonomy registry.
	$taxonomies = Registry::get();

	return $taxonomies->register( $taxonomy, $args );
}
