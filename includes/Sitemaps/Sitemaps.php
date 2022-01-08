<?php

namespace Connections_Directory\Sitemaps;

use cnOptions;
use cnShortcode;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_format;
use WP_Error;
use WP_Post;

/**
 * Sitemaps are enabled only when login is not required and the Entry Name permalink option is enabled.
 *
 * @since 10.0
 *
 * @return bool
 */
function isEnabled() {

	$loginOptions  = get_option( 'connections_login', array() );
	$loginRequired = _array::get( $loginOptions, 'required', false );

	$permalinkOptions = get_option( 'connections_link', array() );
	$permalinkEnabled = _array::get( $permalinkOptions, 'name', false );

	_format::toBoolean( $loginRequired );
	_format::toBoolean( $permalinkEnabled );

	$isEnabled = ! $loginRequired && $permalinkEnabled;

	/**
	 * Whether or not the directory sitemaps are enabled based on whether or not login is required to view the directory.
	 *
	 * @since 10.0
	 *
	 * @param bool $isEnabled Whether sitemaps are enabled or not.
	 */
	return (bool) apply_filters( 'Connections_Directory/Sitemaps/Is_Enabled', $isEnabled );
}

/**
 * Callback for the `init` action.
 *
 * Initialize the sitemaps.
 *
 * @since 10.0
 * @private
 *
 * @return Registry|false
 */
function init() {

	/*
	 * WordPress 5.5 or greater is required. If the WP Sitemaps Server does not exist, bail.
	 */
	if ( ! function_exists( 'wp_sitemaps_get_server' ) ) {
		return false;
	}

	if ( ! isEnabled() ) {
		return false;
	}

	registerDirectoryHomepage();

	$registry = Registry::get();

	/**
	 * Fires after initializing the Registry object.
	 *
	 * Additional sitemaps providers should be registered on this hook.
	 *
	 * @since 10.0
	 *
	 * @param Registry $registry Sitemaps object.
	 */
	do_action( 'Connections_Directory/Sitemaps/Init', $registry );

	foreach ( $registry->getProviders() as $provider ) {

		wp_register_sitemap_provider( $provider->getName(), $provider );
	}

	return $registry;
}

/**
 * Create Provider instance given a post ID.
 *
 * Parse the post content for `[connections]` and Directory Block instances and create `Provider` instance for each.
 *
 * This function is to be used to create the Provider instance when registering a new post as sitemap provider.
 *
 * @since 10.0
 *
 * @see registerProvider()
 *
 * @param int    $id   The post ID to create providers for.
 * @param string $name Unique name for the sitemap provider.
 *
 * @return Provider|WP_Error An array of `Provider` instances for a given post ID. WP_Error on failure.
 */
function createProvider( $id, $name ) {

	if ( ! is_int( $id ) ) {
		return new WP_Error( 'invalid_parameter_value', 'The post ID must be an integer.', $id );
	}

	$post = get_post( $id );

	if ( ! $post instanceof WP_Post ) {
		return new WP_Error( 'post_not_found', 'Post does not exist.', $post );
	}

	$postStatus = get_post_status( $post );

	if ( 'publish' !== $postStatus ) {
		return new WP_Error( 'invalid_post_status', 'Post must have a `publish` status.', $postStatus );
	}

	if ( ! has_shortcode( $post->post_content, 'connections' ) &&
		 ! has_block( 'connections-directory/shortcode-connections', $post )
	) {
		return new WP_Error( 'shortcode_or_block_not_found', 'Post does not seem to have the shortcode or block.', $post );
	}

	$instances = parseProviders( $post );

	if ( ! is_array( $instances ) ) {

		return new WP_Error( 'shortcode_or_block_not_found', 'Post does not seem to have the shortcode or block.', $post );
	}

	$provider = new Provider( $name );

	// Create an array of supported post types.
	$supportedPostTypes = array( 'page' );
	$CPTOptions         = get_option( 'connections_cpt', array() );
	$supportedCPTTypes  = _array::get( $CPTOptions, 'supported', array() );

	// The `$supportedCPTTypes` should always be an array, but had at least one user where this was not the case.
	// To prevent PHP error notice, do an array check.
	if ( is_array( $supportedCPTTypes ) ) {

		$supportedPostTypes = array_merge( $supportedPostTypes, $supportedCPTTypes );
	}

	// If the current post is a supported post type, use the post ID.
	// If is is not, use the post ID of the page set as the Directory Homepage.
	if ( in_array( $post->post_type, $supportedPostTypes ) ) {

		$postID = $post->ID;

	} else {

		$options = get_option( 'connections_home_page', array() );
		$postID  = (int) _array::get( $options, 'page_id', false );

		if ( false === $postID ) {
			return new WP_Error( 'directory_homepage_not_set', 'Directory Homepage not set.', $options );
		}
	}

	foreach ( $instances as $index => $instance ) {

		// Ensure instance is an array.
		if ( ! is_array( $instance ) ) {
			$instance = array();
		}

		// Ensure the `home_id` and `force_home` indexes are set so the URLs are created with the correct permalink.
		_array::set( $instance, 'force_home', _array::get( $instance, 'force_home', false ) );
		_array::set( $instance, 'home_id', $postID );

		/**
		 * Filters the sitemap entry for an individual post.
		 *
		 * @since 10.0
		 *
		 * @param array  $instance A shortcode instance attributes or instance of block parameters.
		 * @param int    $id       The post ID to create providers for.
		 * @param string $name     Unique name for the sitemap provider.
		 */
		$instance = apply_filters(
			'Connections_Directory/Sitemaps/Create_Provider/Instance',
			$instance,
			$id,
			$name
		);

		$provider->addInstance( "instance_{$id}-{$index}", $instance );
	}

	return $provider;
}

/**
 * The page/post must have either the `[connections]` shortcode or the Directory Block.
 *
 * @since 10.0
 *
 * @see   createProvider()
 *
 * @param string   $name     Unique name for the sitemap provider.
 *                           This name must be the one used for the `$name` parameter in createProvider().
 * @param Provider $provider A `Provider` instance.
 *
 * @return bool Whether or not the sitemap provider was added.
 */
function registerProvider( $name, $provider ) {

	if ( $provider instanceof Provider ) {

		return Registry::get()->addProvider( $name, $provider );
	}

	return false;
}

/**
 * Register the page set as the Directory Homepage as a sitemap provider.
 *
 * @since 10.0
 * @private
 *
 * @return bool Whether or not the Directory Homepage provider was registered.
 */
function registerDirectoryHomepage() {

	$options = get_option( 'connections_home_page', array() );
	$id      = (int) _array::get( $options, 'page_id', false );

	if ( false === $id ) {
		return false;
	}

	/**
	 * Allow the changing of the directory homepage sitemaps name.
	 *
	 * @since 10.0
	 *
	 * @param string $name The directory homepage sitemap name.
	 */
	$name = apply_filters( 'Connections_Directory/Sitemaps/Homepage/Name', 'directory' );

	$provider = createProvider( $id, $name );

	if ( ! $provider instanceof Provider ) {
		return false;
	}

	registerProvider( $provider->getName(), $provider );

	return true;
}

/**
 * Parse a post for instances of the `[connections]` shortcode and Directory Block.
 *
 * @since 10.0
 * @private
 *
 * @param WP_Post $post
 *
 * @return array An indexed array of shortcode attributes and block parameters.
 */
function parseProviders( $post ) {

	$instances = cnShortcode::find( 'connections', $post->post_content, 'atts' );

	/*
	 * The `cnShortcode::find()` method can return false and array is require. So if not an array, set as an empty array.
	 */
	if ( ! is_array( $instances ) ) {
		$instances = array();
	}

	if ( has_block( 'connections-directory/shortcode-connections', $post ) ) {

		$blocks = parse_blocks( $post->post_content );

		foreach ( $blocks as $index => $block ) {

			if ( 'connections-directory/shortcode-connections' === $block['blockName'] ) {

				$attributes = _array::get( $block, 'attrs', array() );

				$entryTypes = cnOptions::getEntryTypeOptions();

				if ( ! array_key_exists( _array::get( $attributes, 'listType', null ), $entryTypes ) ) {

					$attributes['listType'] = null;
				}

				$categories = _::decodeJSON( _array::get( $attributes, 'categories', '' ) );

				if ( is_wp_error( $categories ) ) {

					$attributes['categories'] = null;

				} else {

					$attributes['categories'] = $categories;
				}

				$category = _array::get( $attributes, 'inCategories', 'category' ) ? 'category' : 'category_in';

				$excludeCategories = _::decodeJSON( _array::get( $attributes, 'excludeCategories', '' ) );

				if ( is_wp_error( $excludeCategories ) ) {

					$attributes['excludeCategories'] = null;

				} else {

					$attributes['excludeCategories'] = $excludeCategories;
				}

				array_push(
					$instances,
					array(
						'list_type'        => _array::get( $attributes, 'ListType', null ),
						$category          => _array::get( $attributes, 'categories', null ),
						'exclude_category' => _array::get( $attributes, 'excludeCategories', null ),
						'id'               => _array::get( $attributes, 'fullName', null ),
						'last_name'        => _array::get( $attributes, 'lastName', null ),
						'title'            => _array::get( $attributes, 'title', null ),
						'department'       => _array::get( $attributes, 'department', null ),
						'organization'     => _array::get( $attributes, 'organization', null ),
						'district'         => _array::get( $attributes, 'district', null ),
						'county'           => _array::get( $attributes, 'county', null ),
						'state'            => _array::get( $attributes, 'state', null ),
						'city'             => _array::get( $attributes, 'city', null ),
						'zip_code'         => _array::get( $attributes, 'zip_code', null ),
						'country'          => _array::get( $attributes, 'country', null ),
						'home_id'          => _array::get( $attributes, 'home_id', $post->ID ),
						'force_home'       => _array::get( $attributes, 'force_home', false ),
					)
				);
			}
		}
	}

	return $instances;
}
