<?php

namespace Connections_Directory;

use cnURL as URL;

/**
 * Class Blocks
 *
 * @package Connections_Directory
 */
class Blocks {

	/**
	 * @since 8.31
	 */
	public static function register() {

		if ( ! function_exists( 'register_block_type' ) ||
		     ! function_exists( 'wp_set_script_translations' ) // Required as the Gutenberg plugin does not have this function.
		) {

			return;
		}

		// Enqueue the editor assets for the blocks.
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueueEditorAssets' ) );

		// Enqueue the frontend block assets.
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueueAssets' ) );

		// Register Connections blocks category.
		add_filter( 'block_categories', array( __CLASS__, 'registerCategories' ), 10, 2 );

		// Register the editor blocks.
		add_action( 'init', 'Connections_Directory\Blocks\Carousel::register' );
		add_action( 'init', 'Connections_Directory\Blocks\Directory::register' );
		add_action( 'init', 'Connections_Directory\Blocks\Team::register' );
		add_action( 'init', 'Connections_Directory\Blocks\Upcoming::register' );
	}

	/**
	 * Callback for the `enqueue_block_editor_assets` action.
	 *
	 * Enqueues block assets for editor only.
	 *
	 * @since 8.31
	 */
	public static function enqueueEditorAssets() {

		// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
		//$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$url  = URL::makeProtocolRelative( Connections_Directory()->pluginURL() );
		$path = Connections_Directory()->pluginPath();

		\cnScript::enqueueStyles();

		$jsDependencies = array(
			'lodash',
			'wp-plugins',
			'wp-element',
			'wp-edit-post',
			'wp-i18n',
			'wp-api-request',
			'wp-data',
			'wp-hooks',
			'wp-plugins',
			'wp-components',
			'wp-blocks',
			'wp-editor',
			'wp-compose',
		);

		wp_enqueue_script(
			'connections-block-directory',
			"{$url}assets/dist/js/blocks-editor.js",
			$jsDependencies,
			\Connections_Directory::VERSION . '-' . filemtime( "{$path}assets/dist/js/blocks-editor.js" ),
			TRUE
		);

		wp_localize_script(
			'connections-block-directory',
			'cbDir',
			array(
				'blockSettings' => array(
					'templates'  => array(
						'active'     => Connections_Directory()->options->getActiveTemplate( 'all' ),
						'registered' => \cnTemplateFactory::getOptions(),
					),
					'entryTypes' => \cnOptions::getEntryTypeOptions(),
					'dateTypes'  => \cnOptions::getDateTypeOptions(),
				)
			)
		);

		wp_set_script_translations( 'connections-block-directory', 'connections' );
	}

	/**
	 * Callback for the `enqueue_block_assets` action.
	 *
	 * Enqueues block assets for both editor and frontend.
	 *
	 * @since 8.31
	 */
	public static function enqueueAssets() {

		// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
		//$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$url  = URL::makeProtocolRelative( Connections_Directory()->pluginURL() );
		$path = Connections_Directory()->pluginPath();

		/*
		 * Enqueue admin assets only.
		 */
		if ( is_admin() ) {}

		/*
		 * Enqueue frontend assets only.
		 */
		if ( ! is_admin() ) {

			wp_enqueue_script(
				'connections-blocks',
				"{$url}assets/dist/js/blocks-public.js",
				array( 'wp-element', 'wp-html-entities' ),
				\Connections_Directory::VERSION . '-' . filemtime( "{$path}assets/dist/js/blocks-public.js" ),
				TRUE
			);
		}

		/*
		 * Enqueue admin and frontend assets.
		 */
		wp_enqueue_style(
			'connections-blocks',
			"{$url}assets/dist/css/blocks-editor.css",
			array(),
			\Connections_Directory::VERSION . '-' . filemtime( "{$path}assets/dist/css/blocks-editor.css" )
		);
	}

	/**
	 * Callback for the `block_categories` filter.
	 *
	 * Register the Connections category for the blocks.
	 *
	 * @since 8.31
	 *
	 * @param array    $categories Array of block categories.
	 * @param \WP_Post $post       Post being loaded.
	 *
	 * @return array
	 */
	public static function registerCategories( $categories, $post ) {

		$categories[] = array(
			'slug'  => 'connections-directory',
			'title' => 'Connections Business Directory',
			'icon'  => NULL,
		);

		return $categories;
	}
}

