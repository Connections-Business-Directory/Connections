<?php

use function Connections_Directory\Sitemaps\createProvider;
use function Connections_Directory\Sitemaps\registerProvider;

/**
 * Add a new sitemap provider.
 *
 * NOTE: The `$name` parameter  must be characters a thru z only in all lowercase.
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
