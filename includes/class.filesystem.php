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
	 * Will overwrite an .htaccess an add "Options -Indexes" in the supplied path. If the .htaccess file does not exist, it create the file and add the rule.
	 *
	 * @access public
	 * @since 0.7.5
	 * @uses trailingslashit()
	 * @param (string) $path The path in which the file is to be created in.
	 * @return void
	 */
	public static function noIndexes( $path ) {
		$path = trailingslashit( $path );

		$rules = 'Options -Indexes';

		if ( file_exists( $path . '.htaccess' ) ) {

			$contents = @file_get_contents( $path . '.htaccess' );

			if ( FALSE === strpos( $contents, 'Options -Indexes' ) || ! $contents ) {

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
	 *       If the supplied path does not exist, it'll be create recursively.
	 *
	 * @access public
	 * @since  0.7.5
	 * @uses   untrailingslashit()
	 *
	 * @param  string $path The path in which to make writable.
	 *
	 * @return bool
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

	/**
	 * Copy a file, or recursively copy a folder and its contents.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @url    http://stackoverflow.com/a/12763962
	 * @uses   self::mkdir()
	 * @param  string   $source      Source path
	 * @param  string   $dest        Destination path
	 * @param  int      $permissions New folder creation permissions
	 *
	 * @return bool     Returns true on success, false on failure
	 */
	public static function xcopy( $source, $dest, $permissions = 0755 ) {

		// Check for symlinks
		if ( is_link( $source ) ) {

			return symlink( readlink( $source ), $dest );
		}

		// Simple copy for a file
		if ( is_file( $source ) ) {

			return copy( $source, $dest );
		}

		// Make destination directory
		if ( ! is_dir( $dest ) ) {

			self::mkdir( $dest, $permissions );
		}

		// Loop through the folder
		$dir = dir( $source );

		while ( FALSE !== $entry = $dir->read() ) {

			// Skip pointers
			if ( $entry == '.' || $entry == '..' ) {

				continue;
			}

			// Deep copy directories
			self::xcopy( "$source/$entry", "$dest/$entry" );
		}

		// Clean up
		$dir->close();

		return TRUE;
	}

	/**
	 * Recursively delete all directories and files starting at the defined $path.
	 * @url    http://stackoverflow.com/a/3352564
	 * @url    http://stackoverflow.com/a/7288067
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @param  string  $path       Absolute directory path.
	 * @param  boolean $deleteRoot Where or not to delete the origin directory.
	 *
	 * @return void
	 */
	public static function xrmdir( $path, $deleteRoot = TRUE ) {

		// If the $path does not exist, bail.
		if ( ! file_exists( $path ) ) return;

		// SKIP_DOTS Requires PHP >= 5.3
		// $it = new RecursiveDirectoryIterator( $path, RecursiveDirectoryIterator::SKIP_DOTS );
		$it = new RecursiveDirectoryIterator( $path );
		$it = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );

		foreach ( $it as $file ) {

			if ( is_callable( $file, 'isDot' ) ) {

				// isDot() Requires PHP >= 5.3
				if ( $file->isDot() ) { continue; }

			} else {

				// Required for PHP 5.2 support.
				if ( basename( $file ) == '..' || basename( $file ) == '.' ) { continue; }
			}

			if ( $file->isDir() ) {

				@rmdir( $file->getPathname() );

			} else {

				@unlink( $file->getPathname() );
			}

		}

		if ( $deleteRoot ) @rmdir( $path );
	}

}

class cnUpload {

	/**
	 * The subdirectory of WP_CONTENT_DIR in which to upload the file to.
	 *
	 * @access private
	 * @since  8.1
	 * @var string
	 */
	private $subDirectory = '';

	/**
	 * An associative array containing the key/value pair returned from wp_handle_upload().
	 *
	 * @access private
	 * @since  8.1
	 * @var array
	 */
	private $result = array();

	/**
	 * Upload a file to the WP_CONTENT_DIR or in a defined subdirectory.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @param array  $file Reference to a single element of $_FILES.
	 * @param array  $atts An associative array containing the upload params.
	 *
	 * @return mixed array | object On success an associative array of the uploaded file details. On failure, an instance of WP_Error.
	 */
	public function __construct( $file, $atts = array() ) {

		$this->file( $file, $atts );
	}

	/**
	 * A Connections equivalent of @see wp_upload_dir().
	 *
	 * @access public
	 * @since  8.1.1
	 * @static
	 *
	 * @return array Returns an array containing the Connections upload paths.
	 */
	public static function info() {

		$info = array();

		/*
		 * Core constants that can be overridden in wp-config.php
		 * which enable support for multi-site file locations.
		 */
		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			// Get the core WP uploads info.
			$uploadInfo = wp_upload_dir();

			$info['base_path']    = trailingslashit( $uploadInfo['basedir'] );
			$info['base_url']     = trailingslashit( $uploadInfo['baseurl'] );
			$info['base_rel_url'] = str_replace( home_url(), '', $info['base_url'] );

			$info['img_base_path']    = trailingslashit( $info['base_path'] . CN_IMAGE_DIR_NAME );
			$info['img_base_url']     = trailingslashit( $info['base_url'] . CN_IMAGE_DIR_NAME );
			$info['img_base_rel_url'] = trailingslashit( $info['base_rel_url'] . CN_IMAGE_DIR_NAME );

		} else {

			/**
			 * Pulled this block of code from wp_upload_dir(). Using this rather than simply using wp_upload_dir()
			 * because @see wp_upload_dir() will always return the upload dir/url (/sites/{id}/) for the current network site.
			 *
			 * We do not want this behavior if forcing Connections into single site mode on a multisite
			 * install of WP. Additionally we do not want the year/month sub dir appended.
			 *
			 * A filter could be used, hooked into `upload_dir` but that would be a little heavy as every time the custom
			 * dir/url would be needed the filter would have to be added and then removed not to mention other plugins could
			 * interfere by hooking into `upload_dir`.
			 *
			 * --> START <--
			 */
			$siteurl     = site_url();
			$upload_path = trim( get_option( 'upload_path' ) );

			if ( empty( $upload_path ) || 'wp-content/uploads' == $upload_path ) {

				$dir = WP_CONTENT_DIR . '/uploads';

			} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {

				// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
				$dir = path_join( ABSPATH, $upload_path );

			} else {

				$dir = $upload_path;
			}

			if ( ! $url = get_option( 'upload_url_path' ) ) {

				if ( empty( $upload_path ) || ( 'wp-content/uploads' == $upload_path ) || ( $upload_path == $dir ) ) {

					$url = content_url( '/uploads' );

				} else {

					$url = trailingslashit( $siteurl ) . $upload_path;
				}

			}

			// Obey the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
			// We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
			if ( defined( 'UPLOADS' ) && ! ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) ) {

				$dir = ABSPATH . UPLOADS;
				$url = trailingslashit( $siteurl ) . UPLOADS;
			}
			/*
			 * --> END <--
			 */

			$info['base_path']    = trailingslashit( $dir );
			$info['base_url']     = trailingslashit( $url );
			$info['base_rel_url'] = str_replace( network_home_url(), '', $info['base_url'] );

			$info['img_base_path']    = trailingslashit( $info['base_path'] . CN_IMAGE_DIR_NAME );
			$info['img_base_url']     = trailingslashit( $info['base_url']  . CN_IMAGE_DIR_NAME );
			$info['img_base_rel_url'] = trailingslashit( $info['base_rel_url'] . CN_IMAGE_DIR_NAME );

		}

		return $info;
	}

	/**
	 * Upload a file to the WP_CONTENT_DIR or in a defined subdirectory.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @global $wp_filter
	 *
	 * @uses   wp_parse_args()
	 * @uses   remove_all_filters()
	 * @uses   add_filter()
	 * @uses   wp_handle_upload()
	 * @uses   remove_filter()
	 *
	 * @param array  $file Reference to a single element of $_FILES.
	 * @param array  $atts An associative array containing the upload params.
	 *
	 * @return mixed array | object On success an associative array of the uploaded file details. On failure, an instance of WP_Error.
	 */
	public function file( $file, $atts = array() ) {
		global $wp_filter;

		$options = array();
		$filter  = array();

		$defaults = array(
			'action'            => '',
			'post_action'       => '',
			'sub_dir'           => '',
			'mimes'             => array(),
			'error_callback'    => array( $this, 'uploadErrorHandler' ),
			'filename_callback' => array( $this, 'uniqueFilename' ),
			);

		$atts = wp_parse_args( $atts, $defaults );

		/*
		 * Temporarily store the filters hooked to the upload_dir filter.
		 */
		$filter['wp_handle_upload_prefilter'] = isset( $wp_filter['wp_handle_upload_prefilter'] ) ? $wp_filter['wp_handle_upload_prefilter'] : '';
		$filter['upload_dir']                 = isset( $wp_filter['upload_dir'] ) ? $wp_filter['upload_dir'] : '';
		$filter['wp_handle_upload'] = isset( $wp_filter['wp_handle_upload'] ) ? $wp_filter['wp_handle_upload'] : '';

		/*
		 * Remove all filters hooked into the upload_dir filter to prevent conflicts.
		 */
		remove_all_filters( 'upload_dir' );
		remove_all_filters( 'wp_handle_upload_prefilter' );
		remove_all_filters( 'wp_handle_upload' );

		if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// Add filter to change the file upload destination directory.
		add_filter( 'upload_dir', array( $this, 'subDirectory' ) );

		// Add filter to process the data array returned by wp_handle_upload()
		add_filter( 'wp_handle_upload', array( $this, 'uploadData' ), 10, 2 );

		// Remove the unfiltered_upload capability to enforce the file mime type check.
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );

		// Set the sub directory/folder in which to upload the file to.
		// If empty, it'll use the WP core default.
		if ( ! empty( $atts['sub_dir'] ) ) $this->subDirectory = $atts['sub_dir'];

		// Setup the wp_handle_upload() $options array.
		// Only add values to the array that are going to be overridden.
		// Passing options not intended to be overridden, even if pass empty causes bad things to happen to you.

		$options['action']    = empty( $atts['action'] ) ? '' : $atts['action'];
		$options['test_form'] = empty( $atts['post_action'] ) ? FALSE : $atts['post_action'];

		if ( ! empty( $atts['mimes'] ) && is_array( $atts['mimes'] ) ) $options['mimes']  = $atts['mimes'];
		if ( ! empty( $atts['error_callback'] ) ) $options['upload_error_handler']        = $atts['error_callback'];
		if ( ! empty( $atts['filename_callback'] ) ) $options['unique_filename_callback'] = $atts['filename_callback'];

		/**
		 * The default overrides passed to wp_handle_uploads().
		 *
		 * @since 8.2.9
		 *
		 * @param array $options {
		 *     @type string       $action                   The form action. Expected and default value set by @see wp_handle_upload() is 'wp_handle_upload'.
		 *                                                  Default: empty string, @see wp_handle_upload() will set this to 'wp_handle_upload'
		 *     @type bool         $test_form                Whether or not $action == $_POST['action'] should be checked to ensure a valid form POST.
		 *                                                  Default: FALSE
		 *     @type array        $mimes                    Key is the file extension with value as the mime type.
		 *                                                  Default: empty array.
		 *     @type array|string $upload_error_handler     Custom error handler callback.
		 *                                                  Default: array( $this, 'uploadErrorHandler' )
		 *     @type array|string $unique_filename_callback Custom unique filename callback.
		 *                                                  Default: array( $this, 'uniqueFilename' )
		 * }
		 */
		$options = apply_filters( 'cn_upload_file_options', $options, $file );

		$this->result = wp_handle_upload( $file, $options );

		// Remove the filter that changes the upload destination directory.
		remove_filter( 'upload_dir', array( $this, 'subDirectory' ) );

		// Remove the data array filter.
		remove_filter( 'wp_handle_upload', array( $this, 'uploadData' ), 10 );

		// Remove the filter which removed the unfiltered_upload capability.
		remove_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10 );

		/*
		 * Be a good citizen and add the filters that were hooked back to into upload_dir filter.
		 */
		if ( ! empty( $filter['wp_handle_upload_prefilter'] ) ) $wp_filter['wp_handle_upload_prefilter'] = $filter['wp_handle_upload_prefilter'];
		if ( ! empty( $filter['upload_dir'] ) ) $wp_filter['upload_dir'] = $filter['upload_dir'];
		if ( ! empty( $filter['wp_handle_upload'] ) ) $wp_filter['wp_handle_upload'] = $filter['wp_handle_upload'];

		return $this->result;
	}

	/**
	 * A filter to change the WP core upload path for files.
	 *
	 * @access private
	 * @static
	 * @since  8.1
	 * @param  array $file The WP core upload path values.
	 *
	 * @return array
	 */
	public function subDirectory( $file ) {

		// If this is a multi site AND Connections is in multi site mode then the the paths passed by WP can be used.
		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$file['subdir'] = empty( $this->subDirectory ) ? cnURL::preslashit( $file['subdir'] ) : cnURL::preslashit( $this->subDirectory );
			$file['path']   = untrailingslashit( $file['basedir'] ) . $file['subdir'];
			$file['url']    = untrailingslashit( $file['baseurl'] ) . $file['subdir'];

		// If Connections is on single site or in single site mode on a multi site setup use cnUpload::info() to get the path info.
		} else {

			// NOTE: Important! cnUpload::info() can not be used within this class when `if ( is_multisite() && CN_MULTISITE_ENABLED )`
			// because it will cause a infinite loop due to the filter added in $this->file() which add this method as a callback
			// to the `upload_dir` hook.
			$info = cnUpload::info();

			$file['subdir'] = empty( $this->subDirectory ) ? cnURL::preslashit( $file['subdir'] ) : cnURL::preslashit( $this->subDirectory );
			$file['path']   = untrailingslashit( $info['base_path'] ) . $file['subdir'];
			$file['url']    = untrailingslashit( $info['base_url'] ) . $file['subdir'];
		}

		return $file;
	}

	/**
	 * The file upload error handler callback.
	 *
	 * @access private
	 * @since  8.1
	 * @param  array  $file    Reference to a single element of $_FILES
	 * @param  string $message Error massage passed by wp_handle_upload()
	 *
	 * @return object          Instance of WP_Error.
	 */
	public function uploadErrorHandler( $file, $message ) {

		return new WP_Error( 'image_upload_error', $message, $file );
	}

	/**
	 * The callback for the wp_handle_upload filter. Tweak the data array to better suit.
	 *
	 * @access private
	 * @since  8.1
	 * @param  array  $file    An associative array containing the file upload details.
	 * @param  string $context Accepts 'upload' or 'sideload'
	 * @return string          An associative array containing the file upload details.
	 */
	public function uploadData( $file, $context ) {

		$file['path'] = $file['file'];
		$file['name'] = basename( $file['path'] );

		unset( $file['file'] );

		return $file;
	}

	/**
	 * The callback for the map_meta_cap filter to remove the unfiltered_upload capability from the current user.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @param array  $caps    Returns the user's actual capabilities.
	 * @param string $cap     Capability name.
	 * @param int    $user_id The user ID.
	 * @param array  $args    Adds the context to the cap. Typically the object ID.
	 *
	 * @return array
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {

		if ( 'unfiltered_upload' == $cap ) {

			$caps = array('');
		}

		return $caps;
	}

	/**
	 * Unique filename callback function.
	 *
	 * Change to add a hyphen before the number.
	 * Why, because squishing the iterator number right beside the filename is ugly.
	 *
	 * @url    http://stackoverflow.com/a/15633243
	 *
	 * @access private
	 * @since  8.1
	 * @param  string $dir  The file path.
	 * @param  string $name The file name.
	 * @param  string $ext  The file extension.
	 *
	 * @return string       The unique file name.
	 */
	public function uniqueFilename( $dir, $name, $ext ) {

		$name = pathinfo( $name, PATHINFO_FILENAME );

		$filename = $name . $ext;
		$number   = 0;

		// while ( file_exists( "$dir/$filename" ) ) {

		// 	if ( count( glob( "$dir/$filename", GLOB_NOSORT ) ) > 0 ) {

		// 		$filename = $name . '-' . count( glob( "$dir/$name*$ext" ) ) . $ext;

		// 	}
		// }

		while ( file_exists( "$dir/$filename" ) ) {

			$filename = $name . '-' . ++$number . $ext;
		}

		return $filename;
	}

	/**
	 * Returns the file meta data of a successful file upload.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return array|WP_Error On success an associative array of the uploaded file details. On failure, an instance of WP_Error.
	 */
	public function result() {

		return $this->result;
	}
}
