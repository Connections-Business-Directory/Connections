<?php
/**
 * @todo Clear the sitemaps cache if it exists.
 * @link https://connections-pro.com/ticket/540240/
 */

namespace Connections_Directory\Integration\SEO;

use cnEntry;
use cnQuery;
use cnSEO;
use Connections_Directory\Integration\SEO\Yoast_SEO\Provider;
use Connections_Directory\Request;
use Connections_Directory\Sitemaps\Registry;
use Connections_Directory\Utility\_array;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

/**
 * Class Yoast_SEO
 *
 * @package Connections_Directory\Integration\SEO
 */
final class Yoast_SEO {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 9.12
	 *
	 * @var static
	 */
	private static $instance;

	/**
	 * @since 9.12
	 */
	public function __construct() {  /* Do nothing here */ }

	/**
	 * Callback for the `plugins_loaded` action. Action is run at priority 15 because Yoast SEO inits at priority 14.
	 *
	 * @since 9.12
	 *
	 * @return static
	 */
	public static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) && function_exists( 'wpseo_init' ) ) {

			self::$instance = $self = new self();

			if ( defined( 'WPSEO_VERSION' ) && version_compare( WPSEO_VERSION, '15.2', '>=' ) ) {

				$self->hooks();
			}
		}

		return self::$instance;
	}

	/**
	 * @since 9.12
	 */
	public function hooks() {

		// If the site is in debug mode, do not cache the sitemaps.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_filter( 'wpseo_enable_xml_sitemap_transient_caching', '__return_false' );
		}

		// Do not init the core WordPress sitemaps integration.
		// remove_action( 'init', 'Connections_Directory\Sitemaps\init', 11 );

		// Sitemaps
		add_action( 'init', array( __CLASS__, 'registerSitemapProviders' ), 12 );
		add_filter( 'wp_sitemaps_max_urls', array( __CLASS__, 'maxURLs' ) );
		add_filter( 'Connections_Directory/Sitemaps/Provider/Sitemap_Entry', array( __CLASS__, 'sitemapEntry' ) );

		// @todo Run `ping_search_engines()` after new Entry is published. Need to take care that this does not occur doing CSV imports and bulk operations.

		add_action( 'wp_head', array( __CLASS__, 'maybeAddFilters' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'maybeRemoveCoreMetaDescription' ), 0 );

		// Remove the persistent logs from sitemaps.
		add_filter(
			'option_wpseo_titles',
			function ( $options ) {

				_array::set( $options, 'noindex-cn_log', true );
				_array::set( $options, 'display-metabox-pt-cn_log', false );
				_array::set( $options, 'noindex-tax-cn_log_type', true );
				_array::set( $options, 'display-metabox-tax-cn_log_type', false );

				return $options;
			}
		);
	}

	/**
	 * Callback for the `wp_head` action.
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

			/*
			 * @todo Should hook into this filter and add the prev/next relative URLs for pagination.
			 */
			add_filter( 'wpseo_adjacent_rel_url', array( __CLASS__, 'maybeDisplayAdjacentURL' ) );

			add_filter( 'wpseo_title', array( __CLASS__, 'transformTitle' ), 10, 2 );
			add_filter( 'wpseo_metadesc', array( __CLASS__, 'transformDescription' ), 10, 2 );
			add_filter( 'wpseo_canonical', array( __CLASS__, 'transformURL' ), 10, 2 );
			add_filter( 'wpseo_robots_array', array( __CLASS__, 'robots' ) );

			add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'transformTitle' ), 10, 2 );
			add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'transformDescription' ), 10, 2 );
			add_filter( 'wpseo_opengraph_url', array( __CLASS__, 'transformURL' ), 10, 2 );
			add_filter( 'wpseo_add_opengraph_images', array( __CLASS__, 'addImage' ) );
			// add_filter( 'wpseo_opengraph_image', array( __CLASS__, 'transformImage'), 10, 2 );

			add_filter( 'wpseo_twitter_title', array( __CLASS__, 'transformTitle' ), 10, 2 );
			add_filter( 'wpseo_twitter_description', array( __CLASS__, 'transformDescription' ), 10, 2 );
			add_filter( 'wpseo_twitter_image', array( __CLASS__, 'transformImage' ), 10, 2 );

			add_filter( 'cn_page_title_separator', array( __CLASS__, 'titleSeparator' ) );
		}
	}

	/**
	 * Callback for the `init` action.
	 *
	 * Run at priority 12 because the core Connections sitemaps providers are registered on the `init` action at priority 11.
	 *
	 * Register the sitemaps providers with Yoast SEO.
	 *
	 * NOTE: The `wpseo_sitemaps_providers` is not being used because Yoast SEO registers its sitemaps providers on
	 * the `after_setup_theme` action hook. This is too early for Connections since they are registered on
	 * the `init` action hook. So we'll push them into the `providers` property in the WPSEO_Sitemaps object
	 * since it is public.
	 *
	 * @since 10.1
	 */
	public static function registerSitemapProviders() {

		/**
		 * @var \WPSEO_Sitemaps $wpseo_sitemaps
		 * @noinspection PhpFullyQualifiedNameUsageInspection
		 */
		$wpseo_sitemaps = $GLOBALS['wpseo_sitemaps'];

		/*
		 * @todo instance check on `$wpseo_sitemaps` to make sure it is an `WPSEO_Sitemap_Provider` abject.
		 * @link https://wordpress.org/support/topic/directory-front-end-broken-with-upgrade-to-wp-5-8-2/#post-15135430
		 */

		$registry  = Registry::get();
		$providers = $registry->getProviders();

		if ( is_array( $providers )
			 && is_object( $wpseo_sitemaps )
			 && property_exists( $wpseo_sitemaps, 'providers' )
			 && is_array( $wpseo_sitemaps->providers )
		) {

			foreach ( $providers as $provider ) {

				array_push( $wpseo_sitemaps->providers, new Provider( $provider ) );
			}
		}
	}

	/**
	 * Callback for the `wp_sitemaps_max_urls` filter.
	 *
	 * Return the max URLs per sitemap from Yoast SEO.
	 *
	 * NOTE: The WPSEO_Sitemaps::get_entries_per_page() method is protected.
	 *       Apply the `wpseo_sitemap_entries_per_page` filter.
	 *
	 * @since 10.1
	 *
	 * @param int $maxURLs
	 *
	 * @return int
	 * @noinspection PhpUnusedParameterInspection
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
		$maxURLs = (int) apply_filters( 'wpseo_sitemap_entries_per_page', 1000 );

		return $maxURLs;
	}

	/**
	 * Callback for the `Connections_Directory/Sitemaps/Provider/Sitemap_Entry` filter.
	 *
	 * Yoast SEO expects the `mod` key for the `lastmod` date.
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
	 * Remove the core SEO meta description hook if the `wpseo_head` filter exists.
	 *
	 * @since 9.12
	 *
	 * @noinspection PhpUnused
	 */
	public static function maybeRemoveCoreMetaDescription() {

		if ( has_filter( 'wpseo_head' ) ) {

			remove_filter( 'wp_title', array( 'cnSEO', 'filterMetaTitle' ), 20 );
			remove_filter( 'wp_head', array( 'cnSEO', 'metaDesc' ), 1 );
		}
	}

	/**
	 * Callback for the `wpseo_adjacent_rel_url` filter.
	 *
	 * Do not display the Adjacent URL on he Entry detail/profile page.
	 *
	 * @since 9.12
	 *
	 * @noinspection PhpUnused
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function maybeDisplayAdjacentURL( $url ) {

		if ( cnQuery::getVar( 'cn-entry-slug' ) ||
			 cnQuery::getVar( 'cn-cat-slug' )
		) {

			$url = '';
		}

		return $url;
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

		if ( function_exists( 'YoastSEO' )
			 && method_exists( '\Yoast\WP\SEO\Helpers\Options_Helper', 'get_title_separator' )
		) {

			$separator = YoastSEO()->helpers->options->get_title_separator();
		}

		return $separator;
	}

	/**
	 * Callback for the `wpseo_title`, `wpseo_opengraph_title`, and 'wpseo_twitter_title' filters.
	 *
	 * @since 9.12
	 *
	 * @noinspection PhpUndefinedNamespaceInspection
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUnusedParameterInspection
	 *
	 * @param string                                            $title
	 * @param Yoast\WP\SEO\Presentations\Indexable_Presentation $presentation
	 *
	 * @return string
	 */
	public static function transformTitle( $title, $presentation ) {

		if ( is_admin() ) {

			return $title;
		}

		if ( function_exists( 'YoastSEO' )
			 && method_exists( '\Yoast\WP\SEO\Helpers\Options_Helper', 'get_title_separator' )
		) {

			$separator = YoastSEO()->helpers->options->get_title_separator();
			$title     = cnSEO::metaTitle( $title, $separator );
		}

		return $title;
	}

	/**
	 * Callback for the `wpseo_metadesc`, `wpseo_opengraph_desc`, and `wpseo_twitter_description` filters.
	 *
	 * Dynamically set the page meta title to the Entry excerpt.
	 *
	 * @since 9.12
	 *
	 * @noinspection PhpUndefinedNamespaceInspection
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUnusedParameterInspection
	 *
	 * @param string                                            $description
	 * @param Yoast\WP\SEO\Presentations\Indexable_Presentation $presentation
	 *
	 * @return string
	 */
	public static function transformDescription( $description, $presentation ) {

		$metaDescription = cnSEO::getMetaDescription();

		if ( 0 < strlen( $metaDescription ) ) {

			$description = $metaDescription;
		}

		return $description;
	}

	/**
	 * Callback for the `wpseo_canonical` and `wpseo_opengraph_url` filters.
	 *
	 * @since 9.12
	 *
	 * @noinspection PhpUndefinedNamespaceInspection
	 * @noinspection PhpUndefinedClassInspection
	 *
	 * @param string                                            $url
	 * @param Yoast\WP\SEO\Presentations\Indexable_Presentation $presentation
	 *
	 * @return string
	 */
	public static function transformURL( $url, $presentation ) {

		$url = cnSEO::maybeTransformPermalink( $url, $presentation->model->object_id );

		return $url;
	}

	/**
	 * Callback for the `wpseo_robots_array` filter.
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
	 * @since 9.12
	 * @deprecated 9.13
	 *
	 * @return array|string
	 */
	private static function getImageMeta() {

		_deprecated_function( __METHOD__, '9.13', 'cnSEO::getImageMeta()' );

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();
			$result   = $instance->retrieve->entries( array( 'slug' => urldecode( cnQuery::getVar( 'cn-entry-slug' ) ) ) );

			// Make sure an entry is returned and then echo the meta desc.
			if ( ! empty( $result ) ) {

				$entry           = new cnEntry( $result[0] );
				$imageProperties = apply_filters(
					'Connections_Directory/Integration/SEO/Yoast_SEO/Image_Properties',
					array(
						'size'      => 'custom',
						'width'     => 1200,
						'height'    => 800,
						'crop_mode' => 3,
					)
				);

				if ( 'organization' === $entry->getEntryType() ) {

					_array::set( $imageProperties, 'type', 'logo' );
				}

				$meta = $entry->getImageMeta( $imageProperties );

				if ( is_array( $meta ) ) {

					return $meta;
				}
			}
		}

		return '';
	}

	/**
	 * Callback for the `wpseo_add_opengraph_images` filter.
	 *
	 * @since 9.12
	 *
	 * @noinspection PhpUndefinedNamespaceInspection
	 * @noinspection PhpUndefinedClassInspection
	 *
	 * @param Yoast\WP\SEO\Values\Open_Graph\Images $container
	 *
	 * @return Yoast\WP\SEO\Values\Open_Graph\Images
	 */
	public static function addImage( $container ) {

		if ( is_array( $meta = cnSEO::getImageMeta() ) ) {

			$image = array(
				'url'    => _array::get( $meta, 'url', '' ),
				'width'  => _array::get( $meta, 'width', '' ),
				'height' => _array::get( $meta, 'height', '' ),
			);

			/** @noinspection PhpUndefinedMethodInspection */
			$container->add_image( $image );
		}

		return $container;
	}

	/**
	 * Callback for the `wpseo_opengraph_image` and `wpseo_twitter_image` filters.
	 *
	 * @since 9.12
	 *
	 * @noinspection PhpUndefinedNamespaceInspection
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUnusedParameterInspection
	 *
	 * @param string                                            $url
	 * @param Yoast\WP\SEO\Presentations\Indexable_Presentation $presentation
	 *
	 * @return mixed
	 */
	public static function transformImage( $url, $presentation ) {

		if ( is_array( $meta = cnSEO::getImageMeta() ) ) {

			$url = _array::get( $meta, 'url', '' );
		}

		return $url;
	}
}
