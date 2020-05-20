<?php

namespace Connections_Directory\Entry;

use cnEntry;
use cnSanitize;
use function Sodium\add;

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
	 * @var array
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

			self::$instance = new Content_Blocks;
		}

		return self::$instance;
	}

	/**
	 * @since 9.6
	 *
	 * @param string $id
	 * @param array  $atts {
	 *
	 *     @type string       $context             The context in which to add the Content Block.
	 *                                             Valid: list|single
	 *     @type string       $name                The Content Block name. This will be shown as the setting option name and  the heading name.
	 *     @type string       $slug                The Content Block container ID.
	 *     @type string       $heading             The Content Block heading. This will override the $name attribute when displaying the
	 *                                             heading on the frontend.
	 *     @type array|string $permission_callback The permission required in order to view the Content Block.
	 *     @type string       $script              The registered JavaScript handle to enqueue.
	 *     @type string       $style               The registered CSS handle to enqueue.
	 *     @type array|string $render_callback     The function/method called to display the Content Block.
	 *     @type int          $priority            The priority used when registering the $render_callback.
	 *     @type string       $block_tag           The Content Block container tag.
	 *                                             Default: div
	 *     @type string       $heading_tag         The Content Block heading tag.
	 *                                             Default: h3
	 * }
	 */
	public function add( $id, $atts ) {

		$defaults = array(
			'context'             => null,
			'name'                => ucwords( str_replace( array( '-', '_' ), ' ', $id ) ),
			'slug'                => '',
			'heading'             => '',
			'permission_callback' => '__return_true',
			'script'              => '',
			'style'               => '',
			'render_callback'     => function( $entry, $atts, $template ) {
				echo __( 'No Content Block callback.', 'connections' );
			},
			'priority'            => 10,
			'block_tag'           => 'div',
			'heading_tag'         => 'h3',
		);

		$atts = cnSanitize::args( $atts, $defaults );

		if ( ! in_array( $atts['context'], array( null, 'list', 'single' ), true ) ) {

			$atts['context'] = null;
		}

		$atts['slug'] = 0 < strlen( $atts['slug'] ) ? $atts['slug'] : $id;

		$this->blocks[ $id ] = apply_filters( 'Connections_Directory/Entry/Content_Block/Add', $atts );
	}

	/**
	 * @since 9.6
	 *
	 * @param string $id The ID of the registered block.
	 *
	 * @return array|bool Block parameters array or false.
	 */
	public function get( $id ) {

		return array_key_exists( $id, $this->blocks ) ? $this->blocks[ $id ] : false;
	}

	/**
	 * @since 9.6
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function remove( $id ) {

		$success = FALSE;

		if ( array_key_exists( $id, $this->blocks ) ) {

			unset( $this->blocks[ $id ] );
			$success = TRUE;
		}

		return $success;
	}

	/**
	 * @since 9.6
	 */
	public static function register() {

		$instance = self::instance();

		$blocks = $instance->blocks;

		foreach ( $blocks as $id => &$block ) {

			// Check permission specified on the content block.
			if ( ! $instance->checkPermission( $block ) ) {

				continue;
			}

			$block['option_filter'] = function( $blocks ) use ( $id, $block ) {
				$blocks[ $id ] = $block['name'];
				return $blocks;
			};

			if ( 'list' === $block['context'] ) {

				add_filter( 'cn_content_blocks-list', $block['option_filter'] );

			} elseif ( 'single' === $block['context'] ) {

				add_filter( 'cn_content_blocks-single', $block['option_filter'] );

			} elseif ( NULL === $block['context'] ) {

				add_filter( 'cn_content_blocks', $block['option_filter'] );
			}

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ) );
			add_action( "Connections_Directory/Entry/Content_Block/Render/{$id}", $block['render_callback'], $block['priority'] );
		}
	}

	/**
	 * @since 9.6
	 *
	 * @param array $block Content Block attributes.
	 *
	 * @return bool
	 */
	private function checkPermission( $block ) {

		$permitted = TRUE;

		if ( array_key_exists( 'permission_callback', $block ) && ! empty( $block['permission_callback'] ) ) {

			$permitted = call_user_func( $block['permission_callback'], $block );

			if ( is_wp_error( $permitted ) ) {

				return FALSE;

			} elseif ( FALSE === $permitted || NULL === $permitted ) {

				return FALSE;
			}
		}

		return (bool) $permitted;
	}

	/**
	 * Callback for the `wp_enqueue_scripts` action.
	 *
	 * Enqueue the script handles registered with the Content Block attributes.
	 *
	 * @since 9.6
	 */
	public static function enqueueScripts() {

		$instance = self::instance();

		$blocks = $instance->blocks;

		foreach ( $blocks as $id => $block ) {

			// Frontend styles.
			if ( ! empty( $block['style'] ) ) {
				wp_enqueue_style( $block['style'] );
			}

			// Frontend script.
			if ( ! empty( $block['script'] ) ) {
				wp_enqueue_script( $block['script'] );
			}
		}
	}

	/**
	 * @since 9.6
	 *
	 * @param string  $id
	 * @param cnEntry $entry
	 * @param bool    $echo
	 *
	 * @return string
	 */
	public function renderBlock( $id, $entry, $echo = false ) {

		$html  = '';
		$block = $this->get( $id );

		if ( is_array( $block ) ) {

			$content = $this->renderBlockContent( $id, $entry );

			if ( 0 < strlen( $content ) ) {

				$html = sprintf(
					'<%1$s class="cn-entry-content-block cn-entry-content-block-%2$s" id="cn-entry-content-block-%3$s">%4$s%5$s</%1$s>' . PHP_EOL,
					$block['block_tag'],
					$block['slug'],
					$block['slug'] . '-' . $entry->getSlug(),
					$this->renderBlockHeading( $id ),
					$content
				);
			}
		}

		if ( true === $echo ) {

			echo $html;
		}

		return $html;
	}

	/**
	 * @since 9.6
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	private function renderBlockHeading( $id ) {

		$html  = '';
		$block = $this->get( $id );

		if ( is_array( $block ) ) {

			if ( array_key_exists( 'heading', $block ) && 0 < strlen( $block['heading'] ) ) {

				$heading = $block['heading'];

			} elseif ( array_key_exists( 'name', $block ) && 0 < strlen( $block['name'] ) ) {

				$heading = $block['name'];

			} else {

				$heading = ucwords( str_replace( array( '-', '_' ), ' ', $id ) );
			}

			$html = sprintf( '<%1$s>%2$s</%1$s>', $block['heading_tag'], $heading );
		}

		return $html;
	}

	/**
	 * @param string  $id
	 * @param cnEntry $entry
	 *
	 * @return string
	 */
	private function renderBlockContent( $id, $entry ) {

		$html = '';
		$hook = "Connections_Directory/Entry/Content_Block/Render/{$id}";

		if ( has_action( $hook ) ) {

			ob_start();

			do_action( $hook, $entry );

			$html = ob_get_clean();
		}

		return $html;
	}

}
