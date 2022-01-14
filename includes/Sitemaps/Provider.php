<?php

namespace Connections_Directory\Sitemaps;

use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_url;
use DateTime;
use Exception;
use stdClass;
use WP_Sitemaps_Provider;

/**
 * Class Provider
 *
 * @package Connections_Directory\Sitemaps
 */
final class Provider extends WP_Sitemaps_Provider {

	/**
	 * An associative array of `[connections]` shortcode options values
	 * and Directory Block parameters.
	 *
	 * @since 10.0
	 * @var array
	 */
	protected $instances = array();

	/**
	 * Create new instance of the Provider object.
	 *
	 * @since 10.0
	 *
	 * @param string $name Unique name for the sitemap provider.
	 */
	public function __construct( $name ) {

		$this->name        = $name;
		$this->object_type = 'directory_entry';
	}

	/**
	 * The object subtypes sitemaps features has been repurposed to store
	 * instances of the `[connections]` shortcode and Directory Block.
	 *
	 * If there is on a single instance, return and empty array, otherwise return all instances.
	 *
	 * @since 10.0
	 *
	 * @return array
	 */
	public function get_object_subtypes() {

		return 1 < count( $this->instances ) ? $this->instances : array();
	}

	/**
	 * Gets a URL list for a sitemap.
	 *
	 * @since 10.0
	 *
	 * @param int    $pageNumber Page of results.
	 * @param string $instanceID Optional. The instance ID.
	 *
	 * @throws Exception
	 * @return array Array of URLs for a sitemap.
	 */
	public function get_url_list( $pageNumber, $instanceID = '' ) {

		/**
		 * Filters the posts URL list before it is generated.
		 *
		 * Passing a non-null value will effectively short-circuit the generation, returning that value instead.
		 *
		 * @since 10.0
		 *
		 * @param array  $urlList    The URL list. Default null.
		 * @param string $post_type  Post type name.
		 * @param int    $pageNumber Page of results.
		 */
		$urlList = apply_filters(
			'Connections_Directory/Sitemaps/Provider/URL_List',
			null,
			$instanceID,
			$pageNumber
		);

		if ( null !== $urlList ) {

			return $urlList;
		}

		if ( empty( $instanceID ) ) {

			$instanceID = key( $this->instances );
		}

		$args = $this->getQueryArgs( $instanceID );

		if ( 0 < $pageNumber ) {

			$args['offset'] = ( $pageNumber - 1 ) * wp_sitemaps_get_max_urls( $this->object_type );
		}

		$results = Connections_Directory()->retrieve->entries( $args );

		$urlList = array();

		foreach ( $results as $data ) {

			/*
			 * NOTE: Working with the raw data is about twice as quick as creating `cnEntry` instances.
			 */

			$lastModified = new DateTime( $data->ts );

			$sitemap_entry = array(
				// 'changefreq' => '',
				'lastmod' => $lastModified->format( DATE_W3C ),
				'loc'     => _url::permalink(
					array(
						'data'       => 'url',
						'force_home' => _format::toBoolean( $args['force_home'] ),
						'home_id'    => (int) $args['home_id'],
						'return'     => true,
						'type'       => 'name',
						'slug'       => $data->slug,
					)
				),
				// 'priority'   => .5,
			);

			/**
			 * Filters the sitemap entry for an individual post.
			 *
			 * @since 10.0
			 *
			 * @param array    $sitemap_entry Sitemap data for the entry.
			 * @param stdClass $data          Entry database object.
			 * @param array    $args          The shortcode instance parameters.
			 */
			$sitemap_entry = apply_filters(
				'Connections_Directory/Sitemaps/Provider/Sitemap_Entry',
				$sitemap_entry,
				$data,
				$args
			);

			array_push( $urlList, $sitemap_entry );
		}

		return $urlList;
	}

	/**
	 * Gets the max number of pages available for the object type.
	 *
	 * @since 10.0
	 *
	 * @param string $instanceID Optional. The instance ID.
	 *
	 * @return int Total number of pages.
	 */
	public function get_max_num_pages( $instanceID = '' ) {

		if ( empty( $instanceID ) && empty( $this->instances ) ) {
			return 0;
		}

		if ( empty( $instanceID ) ) {

			$instanceID = key( $this->instances );
		}

		/**
		 * Filters the max number of pages before it is generated.
		 *
		 * Passing a non-null value will short-circuit the generation, returning that value instead.
		 *
		 * @since 10.0
		 *
		 * @param int|null $max_num_pages The maximum number of pages. Default null.
		 * @param string   $instanceID    The instance ID.
		 */
		$max_num_pages = apply_filters( 'Connections_Directory/Sitemaps/Provider/Max_Pages', null, $instanceID );

		if ( null !== $max_num_pages ) {
			return $max_num_pages;
		}

		$args = $this->getQueryArgs( $instanceID );

		Connections_Directory()->retrieve->entries( $args );

		return ceil( Connections_Directory()->resultCountNoLimit / wp_sitemaps_get_max_urls( $this->object_type ) );
	}

	/**
	 * @since 10.0
	 *
	 * @param string $id
	 * @param array  $queryArgs
	 */
	public function addInstance( $id, $queryArgs ) {

		$this->instances[ $id ] = $queryArgs;
	}

	/**
	 * Get provider instance by ID.
	 *
	 * @since 10.1
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function getInstance( $id ) {

		return $this->instances[ $id ];
	}

	/**
	 * Get all provider instances.
	 *
	 * @since 10.1
	 *
	 * @return array
	 */
	public function getInstances() {

		return $this->instances;
	}

	/**
	 * The provider name supplied when creating a new instance of Provider.
	 *
	 * @since 10.0
	 *
	 * @see   Provider
	 *
	 * @return string
	 */
	public function getName() {

		return $this->name;
	}

	/**
	 * @since 10.1
	 *
	 * @return string
	 */
	public function getObjectType() {

		return $this->object_type;
	}

	/**
	 * @since 10.0
	 *
	 * @param string $instanceID The instance ID.
	 *
	 * @return array
	 */
	public function getQueryArgs( $instanceID ) {

		$defaults = array(
			'limit'      => wp_sitemaps_get_max_urls( $this->object_type ),
			'lock'       => true,
			'order_by'   => array( 'id' ),
			// 'process_user_caps' => false,
			'status'     => array( 'approved' ),
			'visibility' => array( 'public' ),
		);

		$args = $this->instances[ $instanceID ];
		$args = array_replace( $args, $defaults );

		/**
		 * Filters the query arguments for post type sitemap queries.
		 *
		 * @since 10
		 *
		 * @see   \cnRetrieve::entries() for a full list of arguments.
		 *
		 * @param array $args Array of WP_Query arguments.
		 */
		$args = apply_filters(
			'Connections_Directory/Sitemaps/Provider/Query_Args',
			$args
		);

		return $args;
	}
}
