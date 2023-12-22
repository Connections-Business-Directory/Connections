<?php
/**
 * @todo Clear the sitemaps cache if it exists.
 * @link https://connections-pro.com/ticket/540240/
 */

namespace Connections_Directory\Integration\SEO;

use cnQuery;
use cnSEO;
use Connections_Directory\Integration\SEO\Rank_Math\Provider;
use Connections_Directory\Request;
use Connections_Directory\Sitemaps\Registry;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_string;

/**
 * Class Rank_Math
 *
 * @package Connections_Directory\Integration\SEO
 */
final class Rank_Math {

	/**
	 * The image meta array to be used for the meta tags.
	 *
	 * @since 9.13
	 * @var array
	 */
	private static $imageMeta = array();

	/**
	 * Stores the instance of this class.
	 *
	 * @since 9.13
	 *
	 * @var static
	 */
	private static $instance;

	/**
	 * @since 9.13
	 */
	public function __construct() {  /* Do nothing here */ }

	/**
	 * @since 9.13
	 *
	 * @return static
	 */
	public static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) && class_exists( 'RankMath' ) ) {

			self::$instance = $self = new self();

			$self->hooks();
		}

		return self::$instance;
	}

	/**
	 * @since 9.13
	 */
	public function hooks() {

		// If the site is in debug mode, do not cache the sitemaps.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_filter( 'rank_math/sitemap/enable_caching', '__return_false' );
		}

		// Sitemaps
		add_filter( 'rank_math/sitemap/providers', array( __CLASS__, 'registerSitemapProviders' ) );
		add_filter( 'wp_sitemaps_max_urls', array( __CLASS__, 'maxURLs' ) );
		add_filter( 'Connections_Directory/Sitemaps/Provider/Sitemap_Entry', array( __CLASS__, 'sitemapEntry' ) );
		add_action( 'cn_saved-entry', array( __CLASS__, 'clearCache' ) );
		add_action( 'cn_updated-entry', array( __CLASS__, 'clearCache' ) );
		add_action( 'cn_deleted-entry', array( __CLASS__, 'clearCache' ) );

		add_action( 'wp', array( __CLASS__, 'maybeAddFilters' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'maybeRemoveCoreMetaDescription' ), 0 );
	}

	/**
	 * Callback for the `wp` action.
	 *
	 * Maybe add the filters.
	 *
	 * @todo SEO filters should not run if the [connections] shortcode is not in the content.
	 * @link https://wordpress.org/support/topic/name-path-in-url/
	 *
	 * @internal
	 * @since 10.2
	 */
	public static function maybeAddFilters() {

		$object = get_queried_object();

		if ( ! $object instanceof \WP_Post ) {

			return;
		}

		if ( has_shortcode( $object->post_content, 'connections' ) ||
			 has_block( 'connections-directory/shortcode-connections', $object )
		) {

			add_action( 'rank_math/head', array( __CLASS__, 'setupImageMeta' ) );

			// @todo Run `ping_search_engines()` after new Entry is published. Need to take care that this does not occur doing CSV imports and bulk operations.

			/*
			 * @todo Add the prev/next relative URLs for pagination.
			 */
			add_filter( 'rank_math/frontend/disable_adjacent_rel_links', array( __CLASS__, 'maybeDisableAdjacentURL' ) );

			add_filter( 'rank_math/frontend/title', array( __CLASS__, 'transformTitle' ), 10 );
			add_filter( 'rank_math/frontend/description', array( __CLASS__, 'transformDescription' ), 10 );
			add_filter( 'rank_math/frontend/canonical', array( __CLASS__, 'transformURL' ), 10 );
			add_filter( 'rank_math/frontend/robots', array( __CLASS__, 'robots' ) );
			add_filter( 'rank_math/opengraph/facebook/og_updated_time', array( __CLASS__, 'updatedTime' ) );
			add_filter( 'rank_math/opengraph/facebook/og_image', array( __CLASS__, 'imageURL' ) );
			add_filter( 'rank_math/opengraph/facebook/og_image_secure_url', array( __CLASS__, 'imageSecureURL' ) );
			add_filter( 'rank_math/opengraph/facebook/og_image_width', array( __CLASS__, 'imageWidth' ) );
			add_filter( 'rank_math/opengraph/facebook/og_image_height', array( __CLASS__, 'imageHeight' ) );
			add_filter( 'rank_math/opengraph/facebook/og_image_type', array( __CLASS__, 'imageType' ) );

			add_filter( 'rank_math/opengraph/twitter/image', array( __CLASS__, 'imageURL' ) );

			add_filter( 'cn_page_title_separator', array( __CLASS__, 'titleSeparator' ) );
		}
	}

	/**
	 * Callback for the `rank_math/sitemap/providers` filter.
	 *
	 * Register the sitemaps providers with Rank Math.
	 *
	 * @since 10.1
	 *
	 * @param \RankMath\Sitemap\Providers\Provider[] $externalProviders
	 *
	 * @return mixed
	 */
	public static function registerSitemapProviders( $externalProviders ) {

		$registry  = Registry::get();
		$providers = $registry->getProviders();

		if ( is_array( $providers ) ) {

			foreach ( $providers as $provider ) {

				array_push( $externalProviders, new Provider( $provider ) );
			}
		}

		return $externalProviders;
	}

	/**
	 * Callback for the `wp_sitemaps_max_urls` filter.
	 *
	 * @since 10.1
	 *
	 * @param int $maxURLs
	 *
	 * @return int
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpFullyQualifiedNameUsageInspection
	 */
	public static function maxURLs( $maxURLs ) {

		/**
		 * Filter the maximum number of entries per XML sitemap.
		 *
		 * After changing the output of the filter, make sure that you disable and enable the
		 * sitemaps to make sure the value is picked up for the sitemap cache.
		 *
		 * @since 10.1
		 *
		 * @param int $entries The maximum number of entries per XML sitemap.
		 */
		$maxURLs = absint( \RankMath\Helper::get_settings( 'sitemap.items_per_page', 100 ) );

		return $maxURLs;
	}

	/**
	 * Callback for the `Connections_Directory/Sitemaps/Provider/Sitemap_Entry` filter.
	 *
	 * Rank Math expects the `mod` key for the `lastmod` date.
	 *
	 * @since 10.1
	 *
	 * @param array $entry
	 *
	 * @return array
	 */
	public static function sitemapEntry( $entry ) {

		$modifiedDate = _array::get( $entry, 'lastmod', null );

		if ( ! is_null( $modifiedDate ) ) {

			_array::set( $entry, 'mod', $modifiedDate );
		}

		return $entry;
	}

	/**
	 * Callback for the `wp_head` action.
	 *
	 * Remove the core SEO meta description hook if the `rank_math/frontend/description` filter exists.
	 *
	 * @since 9.13
	 *
	 * @noinspection PhpUnused
	 */
	public static function maybeRemoveCoreMetaDescription() {

		if ( has_action( 'rank_math/head' ) ) {

			remove_filter( 'wp_title', array( 'cnSEO', 'filterMetaTitle' ), 20 );
			remove_filter( 'wp_head', array( 'cnSEO', 'metaDesc' ), 1 );
		}
	}

	/**
	 * Callback for the `rank_math/frontend/disable_adjacent_rel_links` filter.
	 *
	 * Do not display the Adjacent URL on the Entry detail/profile page.
	 *
	 * @since 9.13
	 *
	 * @noinspection PhpUnused
	 *
	 * @param bool $disable
	 *
	 * @return bool
	 */
	public static function maybeDisableAdjacentURL( $disable ) {

		if ( cnQuery::getVar( 'cn-entry-slug' ) ||
			 cnQuery::getVar( 'cn-cat-slug' )
		) {

			$disable = true;
		}

		return $disable;
	}

	/**
	 * Callback for the `cn_page_title_separator` filter.
	 *
	 * @since 9.12
	 *
	 * @param $separator
	 *
	 * @return mixed
	 */
	public static function titleSeparator( $separator ) {

		if ( method_exists( 'RankMath\Helper', 'get_settings' ) ) {

			/** @noinspection PhpFullyQualifiedNameUsageInspection */
			$separator = \RankMath\Helper::get_settings( 'titles.title_separator' );
		}

		return $separator;
	}

	/**
	 * Callback for the `rank_math/frontend/title' filter.
	 *
	 * @since 9.13
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public static function transformTitle( $title ) {

		if ( method_exists( 'RankMath\Helper', 'get_settings' ) ) {

			/** @noinspection PhpFullyQualifiedNameUsageInspection */
			$separator = \RankMath\Helper::get_settings( 'titles.title_separator' );
			$title     = cnSEO::metaTitle( $title, $separator );
		}

		return $title;
	}

	/**
	 * Callback for the `rank_math/frontend/description` filter.
	 *
	 * Dynamically set the page meta title to the Entry excerpt.
	 *
	 * @since 9.13
	 *
	 * @param string $description
	 *
	 * @return string
	 */
	public static function transformDescription( $description ) {

		$metaDescription = cnSEO::getMetaDescription();

		if ( 0 < strlen( $metaDescription ) ) {

			$description = $metaDescription;
		}

		return $description;
	}

	/**
	 * Callback for the `rank_math/opengraph/facebook/updated_time` filter.
	 *
	 * Dynamically set the last modified date of the post to the Entry last modified date time.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param $date
	 *
	 * @return string
	 */
	public static function updatedTime( $date ) {

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			$result = Connections_Directory()->retrieve->entries( array( 'slug' => urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) ) );

			// Make sure an entry is returned and if not, return $posts unaltered.
			if ( empty( $result ) ) {

				return $date;
			}

			$published = $result[0]->date_added ? date( 'Y-m-d H:i:s', $result[0]->date_added ) : $result[0]->ts;
			$modified  = $result[0]->ts;

			$published = mysql2date( DATE_W3C, $published, false );
			$modified  = mysql2date( DATE_W3C, $modified, false );

			if ( $modified !== $published ) {
				$date = $modified;
			}
		}

		return $date;
	}

	/**
	 * Callback for the `rank_math/frontend/canonical` filter.
	 *
	 * Note as of Rank Math 1.0.48.2 this is not required.
	 *
	 * @since 9.13
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function transformURL( $url ) {

		$id  = get_queried_object_id();
		$url = cnSEO::maybeTransformPermalink( $url, $id );

		return $url;
	}

	/**
	 * Callback for the `rank_math/frontend/robots` filter.
	 *
	 * Do not index paginated results, follow links.
	 *
	 * @param array $robots The meta robots directives to be echoed.
	 *
	 * @return array
	 */
	public static function robots( $robots ) {

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink', array() );
		$slug = _array::get( $base, 'category_base', 'cat' );
		$url  = home_url( Request\Server_Request_URI::input()->value() );

		if ( false !== strpos( $url, '/pg/' ) || false !== strpos( $url, "/{$slug}/" ) ) {

			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';

			return $robots;
		}

		return $robots;
	}

	/**
	 * Callback for the `rank_math/head` action.
	 *
	 * @since 9.13
	 */
	public static function setupImageMeta() {

		if ( is_array( $meta = cnSEO::getImageMeta() ) ) {

			self::$imageMeta = array(
				'url'    => _array::get( $meta, 'url', '' ),
				'width'  => _array::get( $meta, 'width', '' ),
				'height' => _array::get( $meta, 'height', '' ),
				'mime'   => _array::get( $meta, 'mime', '' ),
			);
		}
	}

	/**
	 * @since 9.13
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function imageURL( $url ) {

		if ( ! empty( self::$imageMeta ) ) {

			$url = _array::get( self::$imageMeta, 'url', '' );
		}

		return $url;
	}

	/**
	 * @since 9.13
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function imageSecureURL( $url ) {

		if ( ! empty( self::$imageMeta ) ) {

			$url = _array::get( self::$imageMeta, 'url', '' );
			$url = _string::startsWith( 'https://', $url ) ? $url : '';
		}

		return $url;
	}

	/**
	 * @since 9.13
	 *
	 * @param string $width
	 *
	 * @return string
	 */
	public static function imageWidth( $width ) {

		if ( ! empty( self::$imageMeta ) ) {

			$width = _array::get( self::$imageMeta, 'width', '' );
		}

		return $width;
	}

	/**
	 * @since 9.13
	 *
	 * @param string $height
	 *
	 * @return string
	 */
	public static function imageHeight( $height ) {

		if ( ! empty( self::$imageMeta ) ) {

			$height = _array::get( self::$imageMeta, 'height', '' );
		}

		return $height;
	}

	/**
	 * @since 9.13
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public static function imageType( $type ) {

		if ( ! empty( self::$imageMeta ) ) {

			$type = _array::get( self::$imageMeta, 'mime', '' );
		}

		return $type;
	}

	/**
	 * Clear the sitemaps from the cache.
	 *
	 * @since 10.2
	 */
	public static function clearCache() {

		$registry  = Registry::get();
		$providers = $registry->getProviders();

		if ( is_array( $providers ) ) {

			foreach ( $providers as $provider ) {

				$instances = $provider->getInstances();
				$name      = $provider->getName();
				$count     = count( $instances );

				foreach ( $instances as $instanceID => $instance ) {

					$type = 1 < $count ? "{$name}-{$instanceID}" : $name;

					\RankMath\Sitemap\Cache::invalidate_storage( $type );
				}

			}

			// Delete the sitemap index.
			\RankMath\Sitemap\Cache::invalidate_storage( '1' );
		}
	}
}
