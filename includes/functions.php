<?php

use Connections_Directory\Taxonomy;
use Connections_Directory\Taxonomy\Registry;
use function Connections_Directory\Sitemaps\createProvider;
use function Connections_Directory\Sitemaps\registerProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a new sitemap provider.
 *
 * NOTE: The `$name` parameter  must be characters a through z only in all lowercase.
 *       This is a requirement of the core WordPress sitemaps feature.
 *
 * @since 10.0
 *
 * @param int    $id   The post ID to create providers for.
 * @param string $name Unique name for the sitemap provider.
 *
 * @return bool
 */
function cn_register_sitemap_provider( $id, $name ) {

	$provider = createProvider( $id, $name );

	return registerProvider( $name, $provider );
}

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
