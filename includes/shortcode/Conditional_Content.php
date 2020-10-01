<?php
namespace Connections_Directory\Shortcode;

use cnQuery;
use cnRewrite;
use cnShortcode;
use WP_Post;

/**
 * Class Conditional_content
 *
 * @package Connections_Directory\Shortcode
 */
class Conditional_Content extends cnShortcode {

	/**
	 * @var array
	 */
	private $atts;

	/**
	 * @since 9.12
	 * @var string
	 */
	private $html = '';

	/**
	 * @since 9.12
	 * @var string
	 */
	private $tag = '';

	/**
	 * @since 9.12
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 */
	public function __construct( $atts, $content, $tag ) {

		$this->atts = $this->parseAtts( $atts );
		$this->tag  = $tag;

		// Ensure ID is a numeric value.
		if ( is_numeric( $this->atts['id'] ) ) {

			$this->render();
		}
	}

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @since 9.12
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return static
	 */
	public static function shortcode( $atts, $content, $tag ) {

		return new static( $atts, $content, $tag );
	}

	/**
	 * The shortcode defaults.
	 *
	 * @since 9.12
	 *
	 * @return array
	 */
	private function getDefaults() {

		$defaults = array(
			'id'        => false,
			'block'     => 'content',
			'condition' => 'is_main_query',
			'parameter' => '',
		);

		return $defaults;
	}

	/**
	 * Parse the user supplied atts.
	 *
	 * @since 9.12
	 *
	 * @param array  $atts
	 *
	 * @return array
	 */
	public function parseAtts( $atts ) {

		$defaults = $this->getDefaults();
		$atts     = shortcode_atts( $defaults, $atts, $this->tag );

		// Sanitize supplied atts.
		$atts['id'] = intval( $atts['id'] );

		if ( 0 === $atts['id'] ) {

			$atts['id'] = -1;
		}

		return $atts;
	}

	/**
	 * @since 9.12
	 */
	public function render() {

		if ( ! $this->isCondition() ) {
			return;
		}

		$post = get_post( $this->atts['id'] );

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		switch ( $this->atts['block'] ) {

			case 'content':
				// No recursive loops!
				$this->html = ( $this->atts['id'] === get_the_ID() || $this->atts['id'] === get_queried_object_id() )
					        ? ''
					        : apply_filters( 'the_content', $post->post_content );
				break;

			case 'title':
				$this->html = $post->post_title;
				break;
		}
	}

	/**
	 * @since 9.12
	 *
	 * @return bool
	 */
	private function isCondition() {

		global $wp_query;

		switch ( $this->atts['condition'] ) {

			case 'is_main_query':

				// Remove the cn-image query vars.
				$wp_query_vars = array_diff_key( (array) $wp_query->query_vars, array_flip( array( 'src', 'w', 'h', 'q', 'a', 'zc', 'f', 's', 'o', 'cc', 'ct' ) ) );

				// Grab the array containing all query vars registered by Connections.
				$registeredQueryVars = cnRewrite::queryVars( array() );
				$condition = ! (bool) array_intersect( $registeredQueryVars, array_keys( $wp_query_vars ) );
				break;

			case 'is_search':
				$condition = cnQuery::getVar( 'cn-s' ) ? true : false;
				break;

			case 'is_single':

				$condition = cnQuery::getVar( 'cn-entry-slug' ) ? true : false;
				break;

			default:
				$condition = false;
		}

		return $condition;
	}

	/**
	 * @since 9.12
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->html;
	}
}
