<?php
/**
 * Theme Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     Connections
 * @subpackage  Functions/Compatibility
 * @copyright   @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add the "img-as-is" class to the image and logo if the ProPhoto theme is active.
 *
 * @since 8.3
 *
 * @param string $class
 *
 * @return string
 */
function cn_add_img_as_is_class( $class ) {

	if ( class_exists( 'ppContentFilter' ) && method_exists( 'ppContentFilter', 'modifyImgs' ) ) {

		$class = $class . ' img-as-is';
	}

	return $class;
}

add_filter( 'cn_image_class', 'cn_add_img_as_is_class' );

/**
 * Enfold uses ID CSS selectors which override the core Chosen CSS selectors.
 * This will load an alternative CSS file where all the core Chosen CSS selectors are prefixed with #cn-list.
 * This in theory should override the Enfold CSS selectors.
 *
 * @link https://connections-pro.com/support/topic/enfold-theme-issues/
 */
function cn_enqueue_enfold_css_override() {

	// If SCRIPT_DEBUG is set and TRUE load the non-minified CSS files, otherwise, load the minified files.
	$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
	$url = cnURL::makeProtocolRelative( CN_URL );

	$theme  = wp_get_theme();
	$parent = $theme->parent();

	if ( FALSE === $parent || NULL === $parent ) {

		$enqueue = in_array( $theme->get( 'Name' ), array( 'Enfold' ), TRUE );

	} elseif ( $parent instanceof WP_Theme ) {

		$enqueue = in_array( $parent->get( 'Name' ), array( 'Enfold' ), TRUE );

	} else {

		$enqueue = FALSE;
	}

	if ( $enqueue ) {

		wp_deregister_style( 'cn-chosen' );
		wp_register_style( 'cn-chosen', $url . "vendor/chosen/chosen-cn-list$min.css", array(), CN_CURRENT_VERSION );
	}
}

add_action( 'wp', 'cn_enqueue_enfold_css_override', 11 ); // Priority 11 to run after core CSS.
