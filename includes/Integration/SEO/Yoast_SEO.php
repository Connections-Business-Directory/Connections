<?php

namespace Connections_Directory\Integration\SEO;

use cnEntry;
use cnQuery;
use cnSEO;
use Connections_Directory\Utility\_array;

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
	public function __constructor() {  /* Do nothing here */ }

	/**
	 * @since 9.12
	 *
	 * @return static
	 */
	public static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof static ) ) {

			self::$instance = $self = new static();

			$self->hooks();
		}

		return static::$instance;
	}

	/**
	 * @since 9.12
	 */
	public function hooks() {

		add_action( 'wp_head', array( __CLASS__, 'maybeRemoveCoreMetaDescription' ), 0 );

		/*
		 * @todo Should hook into this filter and add the prev/next relative URLs for pagination.
		 */
		add_filter( 'wpseo_adjacent_rel_url', array( __CLASS__, 'maybeDisplayAdjacentURL' ) );

		add_filter( 'wpseo_title', array( __CLASS__, 'transformTitle' ), 10, 2 );
		add_filter( 'wpseo_metadesc', array( __CLASS__, 'transformDescription' ), 10, 2 );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'transformURL' ), 10, 2 );

		add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'transformTitle' ), 10, 2 );
		add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'transformDescription' ), 10, 2 );
		add_filter( 'wpseo_opengraph_url', array( __CLASS__, 'transformURL' ), 10, 2 );
		add_filter( 'wpseo_add_opengraph_images', array( __CLASS__, 'addImage' ) );
		//add_filter( 'wpseo_opengraph_image', array( __CLASS__, 'transformImage'), 10, 2 );

		add_filter( 'wpseo_twitter_title', array( __CLASS__, 'transformTitle' ), 10, 2 );
		add_filter( 'wpseo_twitter_description', array( __CLASS__, 'transformDescription' ), 10, 2 );
		add_filter( 'wpseo_twitter_image', array( __CLASS__, 'transformImage'), 10, 2 );

		add_filter( 'cn_page_title_separator', array( __CLASS__, 'titleSeparator' ) );
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

		if ( method_exists( 'WPSEO_Utils', 'get_title_separator' ) ) {

			/** @noinspection PhpFullyQualifiedNameUsageInspection */
			/** @noinspection PhpUndefinedClassInspection */
			$separator = \WPSEO_Utils::get_title_separator();
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

		/** @noinspection PhpFullyQualifiedNameUsageInspection */
		$separator = \WPSEO_Utils::get_title_separator();
		$title     = cnSEO::metaTitle( $title, $separator );

		return trim( $title, " \t\n\r\0\x0B$separator");
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
	 * @param string $url
	 * @param Yoast\WP\SEO\Presentations\Indexable_Presentation $presentation
	 *
	 * @return string
	 */
	public static function transformURL( $url, $presentation ) {

		/** @noinspection PhpUndefinedFieldInspection */
		$url = cnSEO::maybeTransformPermalink( $url, $presentation->model->object_id );

		return $url;
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

		if ( is_array( $meta = self::getImageMeta() ) ) {

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

		if ( is_array( $meta = self::getImageMeta() ) ) {

			$url = _array::get( $meta, 'url', '' );
		}

		return $url;
	}

}
