<?php

namespace Connections_Directory;

use cnScript;

/**
 * Class Content_Blocks
 *
 * @package Connections_Directory\Entry
 */
class Content_Blocks {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 9.6
	 *
	 * @var Content_Blocks
	 */
	private static $instance;

	/**
	 * @since 9.6
	 * @var Content_Block[]
	 */
	protected $blocks = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 9.6
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * @since 9.6
	 *
	 * @return Content_Blocks
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Content_Blocks ) ) {

			self::$instance = new Content_Blocks();

			/*
			 * Register the core Content Blocks actions/filters.
			 *
			 * Priority `19` on `init` to allow other plugins to register Content Blocks.
			 * Must run before priority `20` so Content Block are registered before the settings options are initialized.
			 *
			 * Enqueue scripts is set to priority to allow other Content Blocks to register scripts at default priority.
			 */
			add_action( 'init', array( __CLASS__, 'register' ), 19 );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ), 20 );
		}

		return self::$instance;
	}

	/**
	 * Register the Content Block render callback and save block in instance array.
	 *
	 * @since 9.6
	 *
	 * @param Content_Block $block
	 */
	public function add( $block ) {

		$callable = $block->get( 'render_callback' );

		if ( is_callable( $callable ) ) {

			add_action(
				"Connections_Directory/Content_Block/Render/{$block->getID()}",
				$block->get( 'render_callback' ),
				$block->get( 'priority' )
			);

			$this->blocks[ $block->getID() ] = apply_filters( 'Connections_Directory/Content_Block/Add', $block );
		}
	}

	/**
	 * Get a Content Block by its registered ID.
	 *
	 * @since 9.6
	 *
	 * @param string $id The ID of the registered block.
	 *
	 * @return Content_Block|bool Block parameters array or false.
	 */
	public function get( $id ) {

		return array_key_exists( $id, $this->blocks ) ? $this->blocks[ $id ] : false;
	}

	/**
	 * Remove a Content Block from the instance array by its registered ID.
	 *
	 * @since 9.6
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function remove( $id ) {

		$success = false;

		if ( array_key_exists( $id, $this->blocks ) ) {

			unset( $this->blocks[ $id ] );
			$success = true;
		}

		return $success;
	}

	/**
	 * Callback for the `init` action hook.
	 *
	 * Register the Content Block with the Settings API.
	 *
	 * @since 9.6
	 */
	public static function register() {

		$instance = self::instance();

		/**
		 * Use this action to register new Content Blocks.
		 *
		 * @since 10.2
		 *
		 * @param self $instance
		 */
		do_action( 'Connections_Directory/Content_Blocks/Register', $instance );

		$blocks = $instance->blocks;

		foreach ( $blocks as $block ) {

			/**
			 * @var Content_Block $block
			 */
			$block = apply_filters( 'Connections_Directory/Content_Block/Register_Option', $block );

			$register = apply_filters(
				"Connections_Directory/Content_Block/Register_Option/{$block->getID()}",
				$block->get( 'register_option', true )
			);

			if ( true !== $register ) {

				continue;
			}

			$filter = function ( $blocks ) use ( $block ) {
				$blocks[ $block->getID() ] = $block->get( 'name' );
				return $blocks;
			};

			$block->set( 'option_filter', $filter );

			switch ( $block->get( 'context' ) ) {

				case 'list':
					add_filter( 'cn_content_blocks-list', $filter );
					break;

				case 'single':
					add_filter( 'cn_content_blocks-single', $filter );
					break;

				default:
					add_filter( 'cn_content_blocks', $filter );
			}

		}
	}

	/**
	 * Callback for the `wp_enqueue_scripts` action.
	 *
	 * Enqueue the script handles registered with the Content Block attributes.
	 *
	 * @internal
	 * @since 9.6
	 */
	public static function enqueueScripts() {

		$instance = self::instance();

		$blocks = $instance->blocks;

		foreach ( $blocks as $block ) {

			$handle = $block->get( 'style_handle' );

			/**
			 * Frontend styles.
			 * @todo CSS should only be enqueued when on a page with either the Block or the shortcode.
			 * @see \cnScript::maybeEnqueueStyle()
			 */
			if ( $block->isActive() && ! empty( $handle ) && cnScript::maybeEnqueueStyle() ) {
				wp_enqueue_style( $handle );
			}

			/**
			 * The script is enqueued when the block content is rendered.
			 *
			 * @see Content_Block::render()
			 */
		}
	}

	/**
	 * Render a Content Block by its registered ID.
	 *
	 * @since 9.6
	 *
	 * @param string $id
	 * @param mixed  $object
	 * @param array  $atts
	 * @param bool   $echo
	 *
	 * @return string
	 */
	public function renderBlock( $id, $object, $atts = array(), $echo = true ) {

		$html  = '';
		$block = $this->get( $id );

		if ( $block instanceof Content_Block && $block->isPermitted() ) {

			$block->useObject( $object );
			$block->setProperties( $atts );

			$html = $block->asHTML();
		}

		if ( true === $echo ) {

			// Content Block output is escaped in the render action callback.
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html;
	}
}
