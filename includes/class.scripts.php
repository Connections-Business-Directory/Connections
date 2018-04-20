<?php

/**
 * Class to manage registration and enqueueing of the CSS and JS files.
 *
 * @package     Connections
 * @subpackage  Scripts
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnScript
 */
class cnScript {

	/**
	 * Used to store the values of core jQuery.
	 *
	 * @access private
	 * @since  0.7.7
	 * @var array
	 */
	private static $corejQuery = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since  0.7.6.4
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Init the class.
	 *
	 * @access public
	 * @since  0.7.6.4
	 * @static
	 *
	 * @uses   add_filter()
	 * @uses   is_admin()
	 * @uses   add_action()
	 *
	 * @return void
	 */
	public static function hooks() {

		// This filter will add the minified CSS and JS to the search paths
		// if SCRIPT_DEBUG is not defined or set to FALSE.
		add_filter( 'cn_locate_file_names', array( __CLASS__, 'minifiedFileNames' ), 10, 5 );

		// Register the CSS JavaScript libraries.
		// NOTE: Seems the `wp` action hook is not fired in the admin or at least not fired
		// when the CODEX says it is in the admin. So the actions will have to be hooked.
		// based on if we're in the admin or not.
		// @see http://codex.wordpress.org/Plugin_API/Action_Reference
		//
		// This is required because cnScript depends on cnLocate which depends on get_query_var()
		// in the frontend but is not needed or available in the admin.
		if ( is_admin() ) {

			add_action( 'admin_init', array( 'cnScript', 'registerScripts' ) );
			add_action( 'admin_init', array( 'cnScript', 'registerCSS' ) );

		} else {

			add_action( 'wp', array( 'cnScript', 'registerScripts' ) );
			add_action( 'wp', array( 'cnScript', 'registerCSS' ) );
		}

		// Enqueue the frontend scripts and CSS.
		// add_action( 'wp', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( 'cnScript', 'enqueueScripts' ) );
		add_action( 'wp_enqueue_scripts', array( 'cnScript', 'enqueueStyles' ), 999 );

		// Enqueue the admin scripts and CSS.
		add_action( 'admin_enqueue_scripts', array( 'cnScript', 'enqueueAdminScripts' ) );
		add_action( 'admin_enqueue_scripts', array( 'cnScript', 'enqueueAdminStyles' ) );

		add_action( 'wp_print_scripts', array( __CLASS__, 'jQueryFixr' ), 999 );
		add_action( 'wp_default_scripts', array( __CLASS__, 'storeCorejQuery'), 999 );
	}

	/**
	 * Register the external JS libraries that may be enqueued in either the admin or frontend.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 *
	 * @global $connections
	 *
	 * @uses   is_admin()
	 * @uses   is_ssl()
	 * @uses   wp_register_script()
	 * @uses   wp_max_upload_size()
	 * @uses   size_format()
	 * @uses   esc_html()
	 * @uses   wp_localize_script()
	 *
	 * @return void
	 */
	public static function registerScripts() {

		/**
		 * @global connectionsLoad $connections
		 */
		global $connections;

		// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$url = cnURL::makeProtocolRelative( CN_URL );

		/*
		 * If the Google Maps API is disabled, do not register it and change the dependencies of
		 * both goMap and MarkerClusterer. Allowing the Google Maps API to be turned "off" provides
		 * compatibility with themes and other plugins that enqueue Google Maps but do not provide a
		 * method to disable it. So I will, unless we're in the admin, because the geocode function
		 * requires it.
		 */
		if ( $connections->options->getGoogleMapsAPI() || is_admin() ) {

			$endpoint = 'https://maps.googleapis.com/maps/api/js?libraries=geometry';
			$key      = cnSettingsAPI::get( 'connections', 'google_maps_geocoding_api', 'browser_key' );

			if ( 0 < strlen( $key ) ) {

				$endpoint = $endpoint . '&key=' . urlencode( $key );
			}

			wp_register_script( 'cn-google-maps-api', $endpoint, array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );

			wp_register_script( 'jquery-gomap', $url . "vendor/jquery-gomap/jquery.gomap$min.js", array( 'jquery' , 'cn-google-maps-api' ), '1.3.3', $connections->options->getJavaScriptFooter() );
			wp_register_script( 'jquery-markerclusterer', $url . "vendor/markerclusterer/markerclusterer$min.js", array( 'jquery' , 'cn-google-maps-api' , 'jquery-gomap' ), '2.1.2', $connections->options->getJavaScriptFooter() );

		} else {

			wp_register_script( 'jquery-gomap', $url . "vendor/jquery-gomap/jquery.gomap$min.js", array( 'jquery' ), '1.3.3', $connections->options->getJavaScriptFooter() );
			wp_register_script( 'jquery-markerclusterer', $url . "vendor/markerclusterer/markerclusterer$min.js", array( 'jquery' , 'jquery-gomap' ), '2.0.15', $connections->options->getJavaScriptFooter() );
		}

		// The Quform unregisters this script, so lets ensure its registered so it can be enqueued.
		if ( ! wp_script_is( 'jquery-form', 'registered') ) {
			wp_register_script( 'jquery-form', "/wp-includes/js/jquery/jquery.form$min.js", array( 'jquery' ), '4.2.1', TRUE );
		}

		if ( is_admin() ) {

			wp_register_script( 'cn-ui-admin', $url . "assets/js/cn-admin$min.js", array( 'jquery', 'jquery-validate', 'jquery-ui-sortable', 'jquery-ui-resizable' ), CN_CURRENT_VERSION, TRUE );
			wp_register_script( 'cn-system-info', $url . "assets/js/cn-system-info$min.js", array( 'jquery', 'jquery-validate', 'jquery-form', 'wp-util' ), CN_CURRENT_VERSION, TRUE );
			wp_register_script( 'cn-setting-sortable-repeatable-input-list', $url . "assets/js/cn-setting-sortable-repeatable-input-list$min.js", array( 'jquery', 'jquery-ui-sortable' ), CN_CURRENT_VERSION, TRUE );
			wp_register_script( 'cn-csv-export', $url . "assets/js/cn-csv-export$min.js", array( 'jquery', 'wp-util' ), CN_CURRENT_VERSION, TRUE );
			wp_register_script( 'cn-csv-import', $url . "assets/js/cn-csv-import$min.js", array( 'jquery', 'wp-util', 'shortcode' ), CN_CURRENT_VERSION, TRUE );
			wp_register_script( 'cn-widget', $url . "assets/js/widgets$min.js", array( 'jquery' ), CN_CURRENT_VERSION, TRUE );

			$strings = array(
				'showDetails'              => __( 'Show Details', 'connections' ),
				'hideDetails'              => __( 'Hide Details', 'connections' ),
				'showDetailsTitle'         => __( 'Click to show details.', 'connections' ),
				'hideDetailsTitle'         => __( 'Click to hide details.', 'connections' ),
				'imageMaxFileSize'         => wp_max_upload_size(),
				'imageMaxFileSizeExceeded' => __(
					sprintf(
						'Selected image exceeds maximum upload file size of %s. Please choose a different image.',
						esc_html( size_format( wp_max_upload_size() ) )
					),
					'connections'
				),
				'categoryDiv'              => array(
					'nonce'  => wp_create_nonce( 'set_category_div_height' ),
					'height' => Connections_Directory()->currentUser->getCategoryDivHeight(),
				),
			);

			wp_localize_script( 'cn-ui-admin', 'cn_string', $strings );

			$stringsSystemInfo = array(
				'strSend'                   => __( 'Send Email', 'connections' ),
				'strSending'                => __( 'Sending...', 'connections' ),
				'strSubmitted'              => __( 'Your message has been sent. Thank You!', 'connections' ),
				'strErrMsgAction'           => __( 'Invalid AJAX action or nonce validation failed.', 'connections' ),
				'strErrMsgMissingEmail'     => __( 'Please enter a valid email address.', 'connections' ),
				'strErrMsgMissingSubject'   => __( 'Please enter a subject.', 'connections' ),
				'strErrMsgMissingMessage'   => __( 'Please enter a message.', 'connections' ),
				'strErrMsgUserNotPermitted' => __( 'You do not have sufficient permissions to perform this action.', 'connections' ),
				'strAJAXSubmitErrMsg'       => __( 'Unknown error has occurred!', 'connections' ),
				'strAJAXHeaderErrMsg'       => __( 'AJAX Headers not set.', 'connections' ),
			);

			wp_localize_script( 'cn-system-info', 'cn_system_info', apply_filters( 'connections_contact_ajax_messages', $stringsSystemInfo ) );

		} else {

			wp_register_script( 'cn-ui', $url . "assets/js/cn-user$min.js", array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
		}

		wp_register_script( 'jquery-qtip', $url . "vendor/jquery-qtip/jquery.qtip$min.js", array( 'jquery' ), '2.2.1', $connections->options->getJavaScriptFooter() );

		// Registering  with the handle 'jquery-chosen-min' for legacy support. Remove this at some point. 04/30/2014
		wp_register_script( 'jquery-chosen', $url . "vendor/chosen/chosen.jquery$min.js", array( 'jquery' ), '1.7', $connections->options->getJavaScriptFooter() );
		wp_register_script( 'jquery-chosen-min', $url . "vendor/chosen/chosen.jquery$min.js", array( 'jquery' ), '1.7', $connections->options->getJavaScriptFooter() );

		wp_register_script( 'jquery-validate' , $url . "vendor/validation/jquery.validate$min.js", array( 'jquery', 'jquery-form' ) , '1.17.0' , $connections->options->getJavaScriptFooter() );

		wp_register_script( 'picturefill', $url . "vendor/picturefill/picturefill$min.js", array(), '2.3.1', $connections->options->getJavaScriptFooter() );
	}

	/**
	 * Registers the CSS libraries that may be enqueued in the admin or frontend.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 *
	 * @uses   add_filter()
	 * @uses   is_admin()
	 * @uses   wp_register_style()
	 * @uses   get_user_option()
	 * @uses   is_rtl()
	 * @uses   cnSettingsAPI::get()
	 * @uses   cnLocate::file()
	 * @uses   cnLocate::fileNames()
	 * @uses   wp_style_is()
	 * @uses   remove_filter()
	 *
	 * @return void
	 */
	public static function registerCSS() {

		// Add a filter so cnLocate will search the plugins CSS folder.
		add_filter( 'cn_locate_file_paths', array( __CLASS__, 'coreCSSPath' ) );

		// If SCRIPT_DEBUG is set and TRUE load the non-minified CSS files, otherwise, load the minified files.
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$url = cnURL::makeProtocolRelative( CN_URL );

		if ( is_admin() ) {

			wp_register_style( 'cn-admin', $url . "assets/css/cn-admin$min.css", array(), CN_CURRENT_VERSION );
			wp_register_style( 'cn-admin-jquery-ui', $url . 'assets/css/jquery-ui-' . ( 'classic' == get_user_option( 'admin_color' ) ? 'classic' : 'fresh' ) . "$min.css", array(), CN_CURRENT_VERSION );
			wp_register_style( 'cn-admin-jquery-datepicker', $url . "assets/css/datepicker$min.css", array( 'cn-admin-jquery-ui' ), CN_CURRENT_VERSION );

			if ( is_rtl() ) {

				wp_register_style( 'cn-admin-rtl', $url . "assets/css/cn-admin-rtl$min.css", array('cn-admin'), CN_CURRENT_VERSION );
			}

		} else {

			// This will locate the CSS file to be enqueued.
			$coreCSS = cnLocate::file( cnLocate::fileNames( 'cn-user', NULL, NULL, 'css' ), 'url' );
			// var_dump($coreCSS);

			// Registering the CSS with 'connections-user' for legacy support. Remove this at some point. 04/01/2014
			wp_register_style( 'connections-user', $coreCSS, array(), CN_CURRENT_VERSION );
			wp_register_style( 'cn-public', $coreCSS, array(), CN_CURRENT_VERSION );

			if ( is_rtl() ) {

				wp_register_style( 'cn-public-rtl', $url . "assets/css/cn-user-rtl$min.css", array('cn-public'), CN_CURRENT_VERSION );
			}

			// This will locate the custom CSS file to be enqueued.
			$customCSS = cnLocate::file( cnLocate::fileNames( 'cn-custom', NULL, NULL, 'css' ), 'url' );
			// var_dump($customCSS);

			// If a custom CSS file was found, lets register it.
			if ( $customCSS ) {

				// Check to see if the core CSS file was registered since it can be disabled.
				// Add it to the $required array to be used when registering the custom CSS file.
				$required = wp_style_is( 'cn-public', 'registered' ) ? array( 'cn-public' ) : array();

				wp_register_style( 'cn-public-custom', $customCSS, $required, CN_CURRENT_VERSION );
			}

		}

		wp_register_style( 'cn-qtip', $url . "vendor/jquery-qtip/jquery.qtip$min.css", array(), '2.2.1' );
		wp_register_style( 'cn-chosen', $url . "vendor/chosen/chosen$min.css", array(), '1.7' );
		wp_register_style( 'cn-font-awesome', $url . "vendor/font-awesome/css/font-awesome$min.css", array(), '4.4.0' );

		// Remove the filter that adds the core CSS path to cnLocate.
		remove_filter( 'cn_locate_file_paths', array( __CLASS__, 'coreCSSPath' ) );
	}

	/**
	 * Enqueues the Connections JavaScript libraries on required admin pages.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 *
	 * @global $concatenate_scripts
	 * @global $compress_scripts
	 * @global $compress_css
	 *
	 * @uses   Connections_Directory()
	 * @uses   wp_enqueue_script()
	 * @uses   do_action()
	 * @uses   add_action()
	 * @uses   apply_filters()
	 *
	 * @param  string $pageHook The current admin page hook.
	 *
	 * @return void
	 */
	public static function enqueueAdminScripts( $pageHook ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Load on all the Connections admin pages.
		if ( in_array( $pageHook, get_object_vars( $instance->pageHook ) ) ) {

			wp_enqueue_script( 'picturefill' );
			wp_enqueue_script( 'cn-ui-admin' );

			do_action( 'cn_admin_enqueue_scripts', $pageHook );
		}

		$editPageHooks = array();

		if ( property_exists( $instance->pageHook, 'manage') ) $editPageHooks[] = $instance->pageHook->manage;
		if ( property_exists( $instance->pageHook, 'add') ) $editPageHooks[] = $instance->pageHook->add;

		$editPages = apply_filters( 'cn_admin_required_edit_scripts', $editPageHooks );

		if ( in_array( $pageHook, $editPages ) ) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			global $concatenate_scripts, $compress_scripts, $compress_css;

			wp_enqueue_script( 'jquery-gomap' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-chosen' );

			do_action( 'cn_admin_enqueue_edit_scripts', $pageHook );
		}

		$metaboxPageHooks = array();

		if ( property_exists( $instance->pageHook, 'dashboard') ) $metaboxPageHooks[] = $instance->pageHook->dashboard;
		if ( property_exists( $instance->pageHook, 'manage') ) $metaboxPageHooks[] = $instance->pageHook->manage;
		if ( property_exists( $instance->pageHook, 'add') ) $metaboxPageHooks[] = $instance->pageHook->add;

		// Load the core JavaScripts required for metabox UI.
		$metaboxPages = apply_filters( 'cn_admin_required_metabox_scripts', $metaboxPageHooks );

		if ( in_array( $pageHook, $metaboxPages ) ) {

			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
			wp_enqueue_script( 'cn-widget' );

			do_action( 'cn_admin_enqueue_metabox_scripts', $pageHook );

			add_action( 'admin_footer-' . $instance->pageHook->dashboard, array( __CLASS__ , 'adminFooterScript' ) );
			add_action( 'admin_footer-' . $instance->pageHook->manage, array( __CLASS__ , 'adminFooterScript' ) );
			add_action( 'admin_footer-' . $instance->pageHook->add, array( __CLASS__ , 'adminFooterScript' ) );
		}
	}

	/**
	 * @access private
	 * @since  unknown
	 * @static
	 */
	public static function adminFooterScript() {
		?>
		<script>postboxes.add_postbox_toggles(pagenow);</script>
		<?php
	}

	/**
	 * @access private
	 * @since  unknown
	 * @static
	 */
	public static function enqueue() {
		global $wp_query;

		$posts   = $wp_query->posts;
		$pattern = get_shortcode_regex();

		foreach ( $posts as $post ) {

			if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
				&& array_key_exists( 2, $matches )
				&& in_array( 'connections', $matches[2] ) )
			{
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ) );
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueStyles' ) );

				break;
			}
		}

	}

	/**
	 * Enqueues the Connections JavaScript libraries on the frontend.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 *
	 * @uses   wp_enqueue_script()
	 *
	 * @return void
	 */
	public static function enqueueScripts() {
		/*
		 * http://beerpla.net/2010/01/13/wordpress-plugin-development-how-to-include-css-and-javascript-conditionally-and-only-when-needed-by-the-posts/
		 * http://beerpla.net/2010/01/15/follow-up-to-loading-css-and-js-conditionally/
		 * http://scribu.net/wordpress/optimal-script-loading.html
		 */

		// wp_enqueue_script( 'cn-ui' );
		wp_enqueue_script( 'picturefill' );
	}

	/**
	 * Attempt to re-register the bundled version of jQuery
	 *
	 * @access private
	 * @since  0.7.6
	 * @static
	 *
	 * @uses   wp_deregister_script()
	 * @uses   wp_register_script()
	 *
	 * @return void
	 */
	public static function jQueryFixr() {

		if ( ! cnSettingsAPI::get( 'connections', 'connections_compatibility', 'jquery' ) ) return;

		wp_deregister_script( 'jquery' );

		if ( self::$corejQuery['jquery-core'] && self::$corejQuery['jquery-migrate'] ) {

			wp_register_script( 'jquery', FALSE, array( 'jquery-core', 'jquery-migrate' ), self::$corejQuery['jquery-core']->ver );
			wp_register_script( 'jquery-core', '/wp-includes/js/jquery/jquery.js', array(), self::$corejQuery['jquery-core']->ver );
			wp_register_script( 'jquery-migrate', '/wp-includes/js/jquery/jquery-migrate.js', array(), self::$corejQuery['jquery-migrate']->ver );

		} else {

			wp_register_script( 'jquery', '/wp-includes/js/jquery/jquery.js', array(), self::$corejQuery['jquery']->ver );

		}

	}

	/**
	 * Store the values of core jQuery.
	 *
	 * @access private
	 * @since  0.7.7
	 *
	 * @uses   WP_Scripts
	 *
	 * @param  object $scripts WP_Scripts
	 *
	 * @return void
	 */
	public static function storeCorejQuery( $scripts ) {

		self::$corejQuery['jquery'] = $scripts->registered['jquery'];
		self::$corejQuery['jquery-core'] = isset( $scripts->registered['jquery-core'] ) && $scripts->registered['jquery-core'] ? $scripts->registered['jquery-core'] : FALSE;
		self::$corejQuery['jquery-migrate'] = isset( $scripts->registered['jquery-migrate'] ) && $scripts->registered['jquery-migrate'] ? $scripts->registered['jquery-migrate'] : FALSE;
	}

	/**
	 * Enqueues the Connections CSS on the required admin pages.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 *
	 * @uses   Connections_Directory()
	 * @uses   wp_enqueue_style()
	 * @uses   do_action()
	 * @uses   apply_filters()
	 *
	 * @param  string $pageHook The current admin page hook.
	 *
	 * @return void
	 */
	public static function enqueueAdminStyles( $pageHook ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Load on all the Connections admin pages.
		if ( in_array( $pageHook, get_object_vars( $instance->pageHook ) ) ) {

			wp_enqueue_style( 'cn-admin' );
			wp_enqueue_style( 'cn-admin-jquery-ui' );
			wp_enqueue_style( 'cn-admin-jquery-datepicker' );
			wp_enqueue_style( 'cn-font-awesome' );

			if ( is_rtl() ) {

				wp_enqueue_style( 'cn-public-rtl' );
			}

			do_action( 'cn_admin_enqueue_styles', $pageHook );
		}

		$editPageHooks = array();

		if ( property_exists( $instance->pageHook, 'manage') ) $editPageHooks[] = $instance->pageHook->manage;
		if ( property_exists( $instance->pageHook, 'add') ) $editPageHooks[] = $instance->pageHook->add;

		$editPages = apply_filters( 'cn_admin_required_edit_scripts', $editPageHooks );

		if ( in_array( $pageHook, $editPages ) ) {

			wp_enqueue_style( 'cn-chosen' );

			do_action( 'cn_admin_enqueue_edit_styles', $pageHook );
		}
	}

	/**
	 * Enqueues the Connections CSS on the frontend.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 *
	 * @uses   wp_enqueue_style()
	 * @uses   wp_style_is()
	 *
	 * @return void
	 */
	public static function enqueueStyles() {

		wp_enqueue_style( 'cn-public' );

		if ( is_rtl() ) {

			wp_enqueue_style( 'cn-admin-rtl' );
		}

		wp_enqueue_style( 'cn-chosen' );

		// If the custom CSS file was registered, lets enqueue it.
		if ( wp_style_is( 'cn-public-custom', 'registered' ) ) {

			wp_enqueue_style( 'cn-public-custom' );
		}
	}

	/**
	 * This is the callback function that will add the minified CSS and JS
	 * file names to the file name array.
	 *
	 * The minified file names will only be added if SCRIPT_DEBUG is defined
	 * and set to true.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @see    cnLocate::fileNames()
	 *
	 * @param  array  $files An indexed array of file names to search for.
	 * @param  string $base  The base file name. Passed via filter from  cnLocate::fileNames().
	 * @param  string $name  The template part name. Passed via filter from  cnLocate::fileNames().
	 * @param  string $slug  The template part slug. Passed via filter from  cnLocate::fileNames().
	 * @param  string $ext   The template file name extension. Passed via filter from  cnLocate::fileNames().
	 *
	 * @return array         An indexed array of file names to search for.
	 */
	public static function minifiedFileNames( $files, $base, $name, $slug, $ext ) {

		// If SCRIPT_DEBUG is set and TRUE the minified file names
		// do not need added to the $files name array.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) return $files;

		if ( $ext == 'css' || $ext == 'js' ) {

			$i = 0;

			foreach ( $files as $key => $fileName ) {

				// Create the minified file name.
				$position = strrpos( $fileName, '.' );
				$minified = substr( $fileName, 0, $position ) . '.min' . substr( $fileName, $position );

				// Insert the minified file name into the array.
				array_splice( $files, $i, 0, $minified );

				// Increment the insert position. Adding `2` to take into account the updated insert position
				// due to an item being inserted into the array.
				$i = $i + 2;
			}

		}

		return $files;
	}

	/**
	 * This a callback for the filter `cn_locate_file_paths` which adds
	 * the core plugin CSS file path to the file paths which are searched
	 * when locating a CSS file.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @see    registerCSS()
	 * @see    cnLocate::filePaths()
	 *
	 * @param  array  $paths An index array containing the file paths to be searched.
	 *
	 * @return array
	 */
	public static function coreCSSPath( $paths ) {

		$paths[9999] = CN_PATH . 'assets/css/';

		return $paths;
	}

	/**
	 * This a callback for the filter `cn_locate_file_paths` which adds
	 * the core plugin JS file path to the file paths which are searched
	 * when locating a JS file.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @see    registerCSS()
	 *
	 * @param  array  $paths An index array containing the file paths to be searched.
	 *
	 * @return array
	 */
	public static function coreJSPath( $paths ) {

		$paths[9999] = CN_PATH . 'assets/js/';

		return $paths;
	}

}
