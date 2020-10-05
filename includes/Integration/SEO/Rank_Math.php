<?php

namespace Connections_Directory\Integration\SEO;

use cnQuery;
use cnSEO;
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
	public function __constructor() {  /* Do nothing here */ }

	/**
	 * @since 9.13
	 *
	 * @return static
	 */
	public static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof static ) && class_exists( 'RankMath' ) ) {

			self::$instance = $self = new static();

			$self->hooks();
		}

		return static::$instance;
	}

	/**
	 * @since 9.13
	 */
	public function hooks() {

		add_action( 'wp_head', array( __CLASS__, 'maybeRemoveCoreMetaDescription' ), 0 );
		add_filter( 'rank_math/head', array( __CLASS__, 'setupImageMeta' ) );

		/*
		 * @todo Add the prev/next relative URLs for pagination.
		 */
		add_filter( 'rank_math/frontend/disable_adjacent_rel_links', array( __CLASS__, 'maybeDisableAdjacentURL' ) );

		add_filter( 'rank_math/frontend/title', array( __CLASS__, 'transformTitle' ), 10 );
		add_filter( 'rank_math/frontend/description', array( __CLASS__, 'transformDescription' ), 10 );
		//add_filter( 'rank_math/frontend/canonical', array( __CLASS__, 'transformURL' ), 10 );
		add_filter( 'rank_math/opengraph/facebook/og_image', array( __CLASS__, 'imageURL' ) );
		add_filter( 'rank_math/opengraph/facebook/og_image_secure_url', array( __CLASS__, 'imageSecureURL' ) );
		add_filter( 'rank_math/opengraph/facebook/og_image_width', array( __CLASS__, 'imageWidth' ) );
		add_filter( 'rank_math/opengraph/facebook/og_image_height', array( __CLASS__, 'imageHeight' ) );
		add_filter( 'rank_math/opengraph/facebook/og_image_type', array( __CLASS__, 'imageType' ) );

		add_filter( 'rank_math/opengraph/twitter/image', array( __CLASS__, 'imageURL' ) );

		add_filter( 'cn_page_title_separator', array( __CLASS__, 'titleSeparator' ) );
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

		if ( has_filter( 'rank_math/head' ) ) {

			remove_filter( 'wp_title', array( 'cnSEO', 'filterMetaTitle' ), 20 );
			remove_filter( 'wp_head', array( 'cnSEO', 'metaDesc' ), 1 );
		}
	}

	/**
	 * Callback for the `rank_math/frontend/disable_adjacent_rel_links` filter.
	 *
	 * Do not display the Adjacent URL on he Entry detail/profile page.
	 *
	 * @since 9.13
	 *
	 * @noinspection PhpUnused
	 *
	 * @param bool $disable
	 *
	 * @return string
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

			/** @noinspection PhpUndefinedClassInspection */
			/** @noinspection PhpUndefinedNamespaceInspection */
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

			/** @noinspection PhpUndefinedClassInspection */
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpFullyQualifiedNameUsageInspection */
			$separator = \RankMath\Helper::get_settings( 'titles.title_separator' );
			$title     = cnSEO::metaTitle( $title, $separator );
			$title     = trim( $title, " \t\n\r\0\x0B$separator");
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
}
