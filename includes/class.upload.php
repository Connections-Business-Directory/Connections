<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnUpload
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnUpload {

	/**
	 * The subdirectory of WP_CONTENT_DIR in which to upload the file to.
	 *
	 * @since 8.1
	 * @var string
	 */
	private $subDirectory = '';

	/**
	 * An associative array containing the key/value pair returned from wp_handle_upload().
	 *
	 * @since 8.1
	 * @var array|WP_Error
	 */
	private $result = array();

	/**
	 * Upload a file to the WP_CONTENT_DIR or in a defined subdirectory.
	 *
	 * @sinc 8.1
	 *
	 * @param array $file Reference to a single element of $_FILES.
	 * @param array $atts An associative array containing the upload params.
	 */
	public function __construct( $file, $atts = array() ) {

		$this->file( $file, $atts );
	}

	/**
	 * A Connections equivalent of @see wp_upload_dir().
	 *
	 * @since 8.1.1
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
			 * installation of WP. Additionally, we do not want the year/month sub dir appended.
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
			$info['img_base_url']     = trailingslashit( $info['base_url'] . CN_IMAGE_DIR_NAME );
			$info['img_base_rel_url'] = trailingslashit( $info['base_rel_url'] . CN_IMAGE_DIR_NAME );

		}

		return $info;
	}

	/**
	 * Upload a file to the WP_CONTENT_DIR or in a defined subdirectory.
	 *
	 * @since 8.1
	 *
	 * @global $wp_filter
	 *
	 * @param array $file Reference to a single element of $_FILES.
	 * @param array $atts An associative array containing the upload params.
	 *
	 * @return array|WP_Error On success an associative array of the uploaded file details. On failure, an instance of WP_Error.
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
		$filter['wp_handle_upload']           = isset( $wp_filter['wp_handle_upload'] ) ? $wp_filter['wp_handle_upload'] : '';

		/*
		 * Remove all filters hooked into the upload_dir filter to prevent conflicts.
		 */
		remove_all_filters( 'upload_dir' );
		remove_all_filters( 'wp_handle_upload_prefilter' );
		remove_all_filters( 'wp_handle_upload' );

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// Add filter to change the file upload destination directory.
		add_filter( 'upload_dir', array( $this, 'subDirectory' ) );

		// Add filter to process the data array returned by wp_handle_upload().
		add_filter( 'wp_handle_upload', array( $this, 'uploadData' ), 10, 2 );

		// Remove the unfiltered_upload capability to enforce the file mime type check.
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );

		// Set the subdirectory/folder in which to upload the file to.
		// If empty, it'll use the WP core default.
		if ( ! empty( $atts['sub_dir'] ) ) {
			$this->subDirectory = $atts['sub_dir'];
		}

		// Set up the wp_handle_upload() $options array.
		// Only add values to the array that are going to be overridden.
		// Passing options not intended to be overridden, even if pass empty causes bad things to happen to you.

		$options['action']    = empty( $atts['action'] ) ? '' : $atts['action'];
		$options['test_form'] = empty( $atts['post_action'] ) ? false : $atts['post_action'];

		if ( ! empty( $atts['mimes'] ) && is_array( $atts['mimes'] ) ) {
			$options['mimes'] = $atts['mimes'];
		}

		if ( ! empty( $atts['error_callback'] ) ) {
			$options['upload_error_handler'] = $atts['error_callback'];
		}

		if ( ! empty( $atts['filename_callback'] ) ) {
			$options['unique_filename_callback'] = $atts['filename_callback'];
		}

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
		if ( ! empty( $filter['wp_handle_upload_prefilter'] ) ) {
			$wp_filter['wp_handle_upload_prefilter'] = $filter['wp_handle_upload_prefilter'];
		}

		if ( ! empty( $filter['upload_dir'] ) ) {
			$wp_filter['upload_dir'] = $filter['upload_dir'];
		}

		if ( ! empty( $filter['wp_handle_upload'] ) ) {
			$wp_filter['wp_handle_upload'] = $filter['wp_handle_upload'];
		}

		return $this->result;
	}

	/**
	 * A filter to change the WP core upload path for files.
	 *
	 * @access private
	 * @since 8.1
	 *
	 * @param array $file The WP core upload path values.
	 *
	 * @return array
	 */
	public function subDirectory( $file ) {

		// If this is a multisite AND Connections is in multisite mode then the paths passed by WP can be used.
		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$file['subdir'] = empty( $this->subDirectory ) ? cnURL::preslashit( $file['subdir'] ) : cnURL::preslashit( $this->subDirectory );
			$file['path']   = untrailingslashit( $file['basedir'] ) . $file['subdir'];
			$file['url']    = untrailingslashit( $file['baseurl'] ) . $file['subdir'];

		// If Connections is on single site or in single site mode on a multisite setup use cnUpload::info() to get the path info.
		} else {

			// NOTE: Important! cnUpload::info() can not be used within this class when `if ( is_multisite() && CN_MULTISITE_ENABLED )`
			// because it will cause an infinite loop due to the filter added in $this->file() which add this method as a callback
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
	 * @since 8.1
	 *
	 * @param array  $file    Reference to a single element of $_FILES.
	 * @param string $message The error massage that is passed by wp_handle_upload().
	 *
	 * @return WP_Error Instance of WP_Error.
	 */
	public function uploadErrorHandler( $file, $message ) {

		return new WP_Error( 'image_upload_error', $message, $file );
	}

	/**
	 * The callback for the wp_handle_upload filter. Tweak the data array to better suit.
	 *
	 * @access private
	 * @since 8.1
	 *
	 * @param array  $file    An associative array containing the file upload details.
	 * @param string $context Accepts 'upload' or 'sideload'.
	 *
	 * @return array An associative array containing the file upload details.
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
	 * @since 8.5.5
	 *
	 * @param array  $caps    Returns the user's actual capabilities.
	 * @param string $cap     Capability name.
	 * @param int    $user_id The user ID.
	 * @param array  $args    Adds the context to the cap. Typically, the object ID.
	 *
	 * @return array
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {

		if ( 'unfiltered_upload' == $cap ) {

			$caps = array( '' );
		}

		return $caps;
	}

	/**
	 * Unique filename callback function.
	 *
	 * Change to add a hyphen before the number.
	 * Why, because squishing the iterator number right beside the filename is ugly.
	 *
	 * @url http://stackoverflow.com/a/15633243
	 *
	 * @access private
	 * @since 8.1
	 *
	 * @param string $dir  The file path.
	 * @param string $name The file name.
	 * @param string $ext  The file extension.
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

			$filename = $name . '-' . ( ++$number ) . $ext;
		}

		return $filename;
	}

	/**
	 * Returns the file meta data of a successful file upload.
	 *
	 * @since  8.1
	 *
	 * @return array|WP_Error On success an associative array of the uploaded file details. On failure, an instance of WP_Error.
	 */
	public function result() {

		return $this->result;
	}
}
