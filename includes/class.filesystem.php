<?php

/**
 * Class for working with the file system.
 *
 * @package     Connections
 * @subpackage  File System
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnFileSystem {

	private function __construct() { /* Do Nothing Here. */ }

	/**
	 * Recursively create a folder in the supplied path.
	 *
	 * @TODO This should be redone to use the WP Filesystem API, but this'll do for now.
	 *
	 * @access public
	 * @since 0.7.5
	 * @uses wp_mkdir_p()
	 * @param (string) $path The path including the folder to create.
	 * @return (bool)
	 */
	public static function mkdir( $path ) {
		return wp_mkdir_p( $path );
	}

	/**
	 * Create a file and set its content. If the path does not exist this will create the path, recursively.
	 *
	 * @TODO This should be redone to use the WP Filesystem API, but this'll do for now.
	 *
	 * @access public
	 * @since 0.7.5
	 * @uses trailingslashit()
	 * @param (string) $path The path inwhich the file is to be created in.
	 * @param (string) $name The file name.
	 * @param (string) $content The contents of the file being created.
	 * @return void
	 */
	public static function mkFile( $path, $name, $contents ) {
		$path = trailingslashit( $path );

		// Make the path irst if it does exist.
		self::mkdir( $path );

		if ( ! file_exists( $path . $name ) ) {
			@file_put_contents( $path . $name, $contents );
		}
	}

	/**
	 * Create a blank index.php file.
	 *
	 * @access public
	 * @since 0.7.5
	 * @param (string) $path The path inwhich the file is to be created in.
	 * @return void
	 */
	public static function mkIndex( $path ) {
		self::mkFile( $path, 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
	}

	/**
	 * Will add Options -Indexes to an existing .htaccess file in the supplied path. If the .htaccess file does not exist, it create the file and add the rule.
	 *
	 * @access public
	 * @since 0.7.5
	 * @uses trailingslashit()
	 * @param (string) $path The path inwhich the file is to be created in.
	 * @return void
	 */
	public static function noIndexes( $path ) {
		$path = trailingslashit( $path );

		$rules = 'Options -Indexes';

		if ( file_exists( $path . '.htaccess' ) ) {

			$contents = @file_get_contents( $path . '.htaccess' );

			if( false === strpos( $contents, 'Options -Indexes' ) || ! $contents ) {
				@file_put_contents( $path . '.htaccess', $rules );
			}
		} else {
			@file_put_contents( $path . '.htaccess', $rules );
		}
	}

	/**
	 * Attempt to set the folder writeable per http://codex.wordpress.org/Changing_File_Permissions#Using_the_Command_Line
	 * If the suplied path does not exist, it'll be create recursively.
	 *
	 * @access public
	 * @since 0.7.5
	 * @uses untrailingslashit()
	 * @param (string) $path The path inwhich to make writable.
	 * @return (bool)
	 */
	public static function mkdirWritable( $path ) {
		$path = untrailingslashit( $path );

		if ( ! self::mkdir( $path ) ) return FALSE;

		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0746 );
		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0747 );
		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0756 );
		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0757 );
		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0764 );
		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0765 );
		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0766 );
		if ( file_exists( $path ) && ! is_writeable( $path ) ) @chmod( $path , 0767 );

		if ( file_exists( $path ) && is_writeable( $path ) ) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

}