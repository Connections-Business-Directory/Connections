<?php
/** @noinspection DuplicatedCode */

namespace Connections_Directory\Integration\SEO\Rank_Math;

use DateTime;
use Exception;
use RankMath\Sitemap\Router;

/**
 * Class Provider
 *
 * @package Connections_Directory\Integration\SEO\Rank_Math
 */
final class Provider implements \RankMath\Sitemap\Providers\Provider {

	/**
	 * @since 10.1
	 * @var \Connections_Directory\Sitemaps\Provider
	 */
	protected $provider;

	/**
	 * Provider constructor.
	 *
	 * @param \Connections_Directory\Sitemaps\Provider $provider
	 */
	public function __construct( $provider ) {

		$this->provider = $provider;
	}

	/**
	 * Check if provider supports given item type.
	 *
	 * @since 10.1
	 *
	 * @param string $type Type string to check for.
	 *
	 * @return boolean
	 */
	public function handles_type( $type ) {

		$name      = $this->provider->getName();
		$instances = $this->provider->getInstances();

		foreach ( $instances as $instanceID => $instance ) {

			if ( "{$name}-{$instanceID}" === $type ) {
				return true;
			} elseif ( $type === $name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get set of sitemaps index link data.
	 *
	 * @since 10.1
	 *
	 * @param int $max_entries Entries per sitemap.
	 *
	 * @throws Exception
	 * @return array
	 */
	public function get_index_links( $max_entries ) {

		$index     = array();
		$instances = $this->provider->getInstances();
		$maxURLs   = wp_sitemaps_get_max_urls( $this->provider->getObjectType() );
		$name      = $this->provider->getName();
		$count     = count( $instances );

		foreach ( $instances as $instanceID => $instance ) {

			$args     = $this->provider->getQueryArgs( $instanceID );
			$maxPages = $this->provider->get_max_num_pages( $instanceID );

			for ( $pageCounter = 0; $pageCounter < $maxPages; $pageCounter++ ) {

				$args['offset'] = $pageCounter * $maxURLs;

				// Query Entries in the current sitemap index.
				$results = Connections_Directory()->retrieve->entries( $args );

				// Sort the results by date, ascending.
				usort(
					$results,
					function ( $a, $b ) {

						$t1 = strtotime( $a->ts );
						$t2 = strtotime( $b->ts );

						return $t1 - $t2;
					}
				);

				// Grab the last modified from the sorted query results.
				$data = array_pop( $results );

				$currentPage  = ( $maxPages > 1 ) ? ( $pageCounter + 1 ) : '';
				$lastModified = new DateTime( $data->ts );

				$loc = 1 < $count ? "{$name}-{$instanceID}-sitemap{$currentPage}.xml" : "{$name}-sitemap{$currentPage}.xml";

				array_push(
					$index,
					array(
						'loc'     => Router::get_base_url( $loc ),
						'lastmod' => $lastModified->format( DATE_W3C ),
					)
				);
			}
		}

		return $index;
	}

	/**
	 * Get set of sitemap link data.
	 *
	 * @since 10.1
	 *
	 * @param string $type         Sitemap type.
	 * @param int    $max_entries  Entries per sitemap.
	 * @param int    $current_page Current page of the sitemap.
	 *
	 * @throws Exception
	 * @return array
	 */
	public function get_sitemap_links( $type, $max_entries, $current_page ) {

		$instances  = $this->provider->getInstances();
		$name       = $this->provider->getName();
		$count      = count( $instances );
		$instanceID = 1 < $count ? str_replace( "{$name}-", '', $type ) : key( $instances );

		return $this->provider->get_url_list( $current_page, $instanceID );
	}
}
