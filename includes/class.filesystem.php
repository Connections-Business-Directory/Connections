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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnFileSystem
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnFileSystem {

	private function __construct() { /* Do Nothing Here. */ }

	/**
	 * Recursively create a folder in the supplied path.
	 *
	 * @TODO This should be redone to use the WP Filesystem API, but this will do for now.
	 *
	 * @since 0.7.5
	 *
	 * @param string $path The path including the folder to create.
	 *
	 * @return bool
	 */
	public static function mkdir( $path ) {
		return wp_mkdir_p( $path );
	}

	/**
	 * Create a file and set its content. If the path does not exist this will create the path, recursively.
	 *
	 * @TODO This should be redone to use the WP Filesystem API, but this will do for now.
	 *
	 * @since 0.7.5
	 *
	 * @param string $path     The path which the file is to be created in.
	 * @param string $name     The file name.
	 * @param string $contents The contents of the file being created.
	 */
	public static function mkFile( $path, $name, $contents ) {
		$path = trailingslashit( $path );

		// Make the path first if it does exist.
		self::mkdir( $path );

		if ( ! file_exists( $path . $name ) ) {
			@file_put_contents( $path . $name, $contents );
		}
	}

	/**
	 * Create a blank index.php file.
	 *
	 * @since 0.7.5
	 *
	 * @param string $path The path which the file is to be created in.
	 */
	public static function mkIndex( $path ) {
		self::mkFile( $path, 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
	}

	/**
	 * Will overwrite an .htaccess an add "Options -Indexes" in the supplied path. If the .htaccess file does not exist, it creates the file and add the rule.
	 *
	 * @since 0.7.5
	 *
	 * @param string $path The path in which the file is to be created in.
	 */
	public static function noIndexes( $path ) {
		$path = trailingslashit( $path );

		$rules = 'Options -Indexes';

		if ( file_exists( $path . '.htaccess' ) ) {

			$contents = @file_get_contents( $path . '.htaccess' );

			if ( false === strpos( $contents, 'Options -Indexes' ) || ! $contents ) {

				@file_put_contents( $path . '.htaccess', $rules );
			}

		} else {

			@file_put_contents( $path . '.htaccess', $rules );
		}
	}

	/**
	 * Attempt to set the folder writable per
	 *
	 * @link http://codex.wordpress.org/Changing_File_Permissions#Using_the_Command_Line
	 *       If the supplied path does not exist, it'll be created recursively.
	 *
	 * @since 0.7.5
	 *
	 * @param string $path The path in which to make writable.
	 *
	 * @return bool
	 */
	public static function mkdirWritable( $path ) {
		$path = untrailingslashit( $path );

		if ( ! self::mkdir( $path ) ) {
			return false;
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0746 );
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0747 );
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0756 );
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0757 );
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0764 );
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0765 );
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0766 );
		}

		if ( file_exists( $path ) && ! is_writeable( $path ) ) {
			@chmod( $path, 0767 );
		}

		if ( file_exists( $path ) && is_writeable( $path ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copy a file, or recursively copy a folder and its contents.
	 *
	 * @since 8.1
	 *
	 * @url    http://stackoverflow.com/a/12763962
	 *
	 * @param string $source      Source path.
	 * @param string $dest        Destination path.
	 * @param int    $permissions New folder creation permissions.
	 *
	 * @return bool Returns true on success, false on failure
	 */
	public static function xcopy( $source, $dest, $permissions = 0755 ) {

		// Check for symlinks.
		if ( is_link( $source ) ) {

			return symlink( readlink( $source ), $dest );
		}

		// Simple copy for a file.
		if ( is_file( $source ) ) {

			return copy( $source, $dest );
		}

		// Make destination directory.
		if ( ! is_dir( $dest ) ) {

			self::mkdir( $dest, $permissions );
		}

		// Loop through the folder.
		$dir = dir( $source );

		while ( false !== $entry = $dir->read() ) {

			// Skip pointers.
			if ( '.' === $entry || '..' === $entry ) {

				continue;
			}

			// Deep copy directories.
			self::xcopy( "$source/$entry", "$dest/$entry" );
		}

		// Clean up.
		$dir->close();

		return true;
	}

	/**
	 * Recursively delete all directories and files starting at the defined $path.
	 *
	 * @url    http://stackoverflow.com/a/3352564
	 * @url    http://stackoverflow.com/a/7288067
	 *
	 * @since 8.1
	 *
	 * @param string  $path       Absolute directory path.
	 * @param boolean $deleteRoot Where or not to delete the origin directory.
	 */
	public static function xrmdir( $path, $deleteRoot = true ) {

		// If the $path does not exist, bail.
		if ( ! file_exists( $path ) ) {
			return;
		}

		// SKIP_DOTS Requires PHP >= 5.3
		// $it = new RecursiveDirectoryIterator( $path, RecursiveDirectoryIterator::SKIP_DOTS );
		$it = new RecursiveDirectoryIterator( $path );
		$it = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );

		foreach ( $it as $file ) {

			if ( is_callable( $file, 'isDot' ) ) {

				// isDot() Requires PHP >= 5.3.
				if ( $file->isDot() ) {
					continue;
				}

			} else {

				// Required for PHP 5.2 support.
				if ( basename( $file ) == '..' || basename( $file ) == '.' ) {
					continue;
				}
			}

			if ( $file->isDir() ) {

				@rmdir( $file->getPathname() );

			} else {

				@unlink( $file->getPathname() );
			}

		}

		if ( $deleteRoot ) {
			@rmdir( $path );
		}
	}
}
