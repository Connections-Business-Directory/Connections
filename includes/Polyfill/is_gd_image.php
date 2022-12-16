<?php
/**
 * Polyfill for the `is_gd_image`.
 *
 * @since 10.4.36
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Polyfill
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

if ( ! function_exists( 'is_gd_image' ) ) :
	/**
	 * Determines whether the value is an acceptable type for GD image functions.
	 *
	 * In PHP 8.0, the GD extension uses GdImage objects for its data structures.
	 * This function checks if the passed value is either a resource of type `gd`
	 * or a GdImage object instance. Any other type will return false.
	 *
	 * NOTE: This function was added in WP 5.6. Add to be backwards compatible with previous version of WordPress.
	 *
	 * @since 10.4.4
	 *
	 * @param resource|GdImage|false $image A value to check the type for.
	 *
	 * @return bool True if $image is either a GD image resource or GdImage instance,
	 *              false otherwise.
	 */
	function is_gd_image( $image ) {

		if ( is_resource( $image ) && 'gd' === get_resource_type( $image ) || is_object( $image ) && $image instanceof GdImage ) {
			return true;
		}

		return false;
	}
endif;
