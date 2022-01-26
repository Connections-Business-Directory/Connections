<?php

namespace Connections_Directory;

use cnOptions;
use cnScript;
use cnTemplateFactory;
use Connections_Directory\Utility\_url;

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
	 * @internal
	 * @since 8.31
	 */
	public static function enqueueEditorAssets() {

		$url  = _url::makeProtocolRelative( Connections_Directory()->pluginURL() );
		$path = Connections_Directory()->pluginPath();

		cnScript::enqueueStyles();

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
			true
		);

		wp_localize_script(
			'connections-block-directory',
			'cbDir',
			array(
				'blockSettings' => array(
					'templates'  => array(
						'active'     => Connections_Directory()->options->getActiveTemplate( 'all' ),
						'registered' => cnTemplateFactory::getOptions(),
					),
					'entryTypes' => cnOptions::getEntryTypeOptions(),
					'dateTypes'  => cnOptions::getDateTypeOptions(),
				),
			)
		);

		wp_set_script_translations( 'connections-block-directory', 'connections' );
	}

	/**
	 * Callback for the `block_categories` filter.
	 *
	 * Register the Connections category for the blocks.
	 *
	 * @internal
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
			'icon'  => null,
		);

		return $categories;
	}
}

