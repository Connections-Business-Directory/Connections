<?php

use Connections_Directory\Shortcode\Upcoming_List;
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

/**
 * Template tag to call the entry list. All options can be passed as an
 * associative array. The options are identical to those available to the
 * shortcode.
 *
 * EXAMPLE:   connectionsEntryList( array('id' => 325) );
 *
 * @access public
 * @since  unknown
 *
 * @param array $atts
 */
function connectionsEntryList( $atts ) {
	// HTML is escaped within shortcode callback and the template files.
	echo cnShortcode_Connections::view( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Template tag to call the upcoming list. All options can be passed as an
 * associative array. The options are identical to those available to the
 * shortcode.
 *
 * EXAMPLE:   connectionsUpcomingList(array('days' => 30));
 *
 * @param array $atts
 */
function connectionsUpcomingList( $atts ) {

	Upcoming_List::instance( $atts )->render();
}
