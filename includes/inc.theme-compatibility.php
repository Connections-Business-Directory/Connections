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
