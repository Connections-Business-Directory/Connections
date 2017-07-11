<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template part loader.
 *
 * This class searches template part files which will override the core
 * template part functions in class cnTemplatePart. It will also allow
 * the overriding of template cards.
 *
 * @package     Connections
 * @subpackage  Template Part Loader API
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

/**
 * Based on Template Loader version  1.1.0 for Plugins by Gary Jones.
 * @link      http://github.com/GaryJones/Gamajo-Template-Loader
 * @copyright 2013 Gary Jones
 * @license   GPL-2.0+
 *
 * Originally based on functions in Easy Digital Downloads (thanks Pippin!).
 */
class cnLocate {

	/**
	 * Locate the file paths of the template, CSS and JS files
	 * based on the supplied array of file names. The file name
	 * array should be in order of highest priority first, lowest
	 * priority last.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @uses   filePaths()
	 *
	 * @param  array $files An indexed array of file names to search for.
	 * @param  string $return
	 *
	 * @return mixed bool|string The absolute file system path to the located file. False is file not found.
	 */
	public static function file( $files, $return = 'path' ) {

		$path  = FALSE;
		$files = array_filter( (array) $files );

		// Try locating this template file by looping through the template paths.
		foreach ( self::filePaths() as $filePath ) {

			// Try to find a template file.
			foreach ( $files as $fileName ) {
				// var_dump( $filePath . $fileName );

				if ( file_exists( $filePath . $fileName ) ) {
					// var_dump( $filePath . $fileName );

					$path = $filePath . $fileName;
					break 2;
				}
			}
		}

		switch ( $return ) {

			case 'url':

				$result = $path ? cnURL::fromPath( $path ) : $path;
				break;

			default:

				$result = $path;
				break;
		}

		return $result;
	}

	/**
	 * Returns an array of file paths to be search for template files.
	 *
	 * The file paths is an indexed array where the highest priority path
	 * is first and the lowest priority is last.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @uses   trailingslashit()
	 * @uses   is_child_theme()
	 * @uses   trailingslashit()
	 * @uses   get_stylesheet_directory()
	 * @uses   get_template_directory()
	 * @uses   getPath()
	 * @uses   apply_filters()
	 *
	 * @return array An indexed array of file paths.
	 */
	private static function filePaths() {

		$path  = array();

		$template_directory = trailingslashit( 'connections-templates' );

		$upload_dir = wp_upload_dir();

		// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
		if ( is_child_theme() ) {

			$path[10] = trailingslashit( get_stylesheet_directory() ) . $template_directory;
		}

		$path[25] = trailingslashit( get_template_directory() ) . $template_directory;
		$path[35] = trailingslashit( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'connections-templates' );
		$path[50] = trailingslashit( $upload_dir['basedir'] ) . $template_directory;
		$path[90] = CN_CUSTOM_TEMPLATE_PATH;

		$path[999] = trailingslashit( CN_PATH . 'templates' );

		$path = apply_filters( 'cn_locate_file_paths', $path );

		// Sort the file paths based on priority.
		ksort( $path, SORT_NUMERIC );
		// var_dump( $path );

		return array_filter( $path );
	}

	/**
	 * An indexed array of file names to be searched for.
	 *
	 * The file names is an index array of file names where the
	 * highest priority is first and the lowest priority is last.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @uses   apply_filters()
	 * @uses   cnQuery::getVar()
	 * @param  string $base The base file name. Typically `card` for a template file and the template slug for CSS and JS files.
	 * @param  string $name The template part name; such as `single` or `category`.
	 * @param  string $slug The template part slug; such as an entry slug or category slug.
	 * @param  string $ext  [optional] The template file name extension. Defaults to `php`.
	 *
	 * @return array        An indexed array of file names to search for.
	 */
	public static function fileNames( $base, $name = NULL, $slug = NULL, $ext = 'php' ) {

		$files = array();

		if ( cnQuery::getVar( 'cn-cat' ) ) {

			$categoryID = cnQuery::getVar( 'cn-cat' );

			// Since the `cn-cat` query var can be an array, we'll only add the category slug
			// template name when querying a single category.
			if ( ! is_array( $categoryID ) ) {

				$term = cnTerm::getBy( 'id', $categoryID, 'category' );

				$files[] = self::fileName( $base, 'category', $term->slug, $ext );
			}

			$files[] = self::fileName( $base, 'category', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

			$files[] = self::fileName( $base, 'category', cnQuery::getVar( 'cn-cat-slug'), $ext );
			$files[] = self::fileName( $base, 'category', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-country' ) ) {

			$country = self::queryVarSlug( cnQuery::getVar( 'cn-country' ) );

			$files[] = self::fileName( $base, 'country', $country, $ext );
			$files[] = self::fileName( $base, 'country', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-region' ) ) {

			$region  = self::queryVarSlug( cnQuery::getVar( 'cn-region' ) );

			$files[] = self::fileName( $base, 'region', $region, $ext );
			$files[] = self::fileName( $base, 'region', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-postal-code' ) ) {

			$zipcode = self::queryVarSlug( cnQuery::getVar( 'cn-postal-code' ) );

			$files[] = self::fileName( $base, 'postal-code', $zipcode, $ext );
			$files[] = self::fileName( $base, 'postal-code', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-locality' ) ) {

			$locality = self::queryVarSlug( cnQuery::getVar( 'cn-locality' ) );

			$files[] = self::fileName( $base, 'locality', $locality, $ext );
			$files[] = self::fileName( $base, 'locality', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-organization' ) ) {

			$organization = self::queryVarSlug( cnQuery::getVar( 'cn-organization' ) );

			$files[] = self::fileName( $base, 'organization', $organization, $ext );
			$files[] = self::fileName( $base, 'organization', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-department' ) ) {

			$department = self::queryVarSlug( cnQuery::getVar( 'cn-department' ) );

			$files[] = self::fileName( $base, 'department', $department, $ext );
			$files[] = self::fileName( $base, 'department', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			$files[] = self::fileName( $base, NULL, cnQuery::getVar( 'cn-entry-slug'), $ext );
			$files[] = self::fileName( $base, 'single', NULL, $ext );
			// var_dump( $files );
		}

		// If `$name` was supplied, add it to the files to search for.
		if ( ! is_null( $name ) ) $files[] = self::fileName( $base, $name, NULL, $ext );

		// Add the base as the least priority, since it is required.
		$files[] = self::fileName( $base, NULL, NULL, $ext );

		/**
		 * Allow template choices to be filtered.
		 *
		 * The resulting array should be in the order of most specific first, least specific last.
		 * e.g. 0 => card-single.php, 1 => card.php
		 */
		$files = apply_filters( 'cn_locate_file_names', $files, $base, $name, $slug, $ext );
		// var_dump( $files );

		// Sort the files based on priority
		ksort( $files, SORT_NUMERIC );
		// var_dump( $files );

		return array_filter( $files );
	}

	/**
	 * Create file name from supplied attributes.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @param  string $base The base file name.
	 * @param  string $name The template part name.
	 * @param  string $slug The template part slug.
	 * @param  string $ext  The template file name extension.
	 *
	 * @return string       The file name.
	 */
	private static function fileName( $base, $name = NULL, $slug = NULL, $ext = 'php' ) {

		$name = array( $base, $name, $slug );
		$name = array_filter( $name );
		$name = implode( '-', $name ) . '.' . $ext;

		//return strtolower( sanitize_file_name( $name ) );
		return strtolower( $name );
	}

	/**
	 * Takes a supplied query var and creates a file system safe slug.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @uses   sanitize_file_name()
	 * @param  string $queryVar A query var.
	 *
	 * @return string           A file system safe string.
	 */
	private static function queryVarSlug( $queryVar ) {

		return strtolower( sanitize_file_name( urldecode( $queryVar ) ) );
	}

}
