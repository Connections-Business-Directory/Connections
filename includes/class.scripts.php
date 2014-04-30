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

class cnScript {

	/**
	 * Used to store the values of core jQuery.
	 *
	 * @access private
	 * @since 0.7.7
	 * @var array
	 */
	private static $corejQuery = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.7.6.4
	 * @return (void)
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Init the class.
	 *
	 * @access public
	 * @since 0.7.6.4
	 * @return (void)
	 */
	public static function init() {

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
		add_action( 'wp_enqueue_scripts', array( 'cnScript', 'enqueueStyles' ) );

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
	 * @since 0.7.3.2
	 * @global $connections
	 * @uses wp_register_script()
	 * @return void
	 */
	public static function registerScripts() {
		global $connections;

		// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		/*
		 * If the Google Maps API is disabled, do not register it and change the dependencies of
		 * both goMap and MarkerClusterer. Allowing the Google Maps API to be turned "off" provides
		 * compatibility with themes and other plugins that enqueue Google Maps but do not provide a
		 * method to disable it. So I will, unless we're in the admin, because the geocode function
		 * requires it.
		 */
		if ( $connections->options->getGoogleMapsAPI() || is_admin() ) {

			if ( ! is_ssl() ) wp_register_script( 'cn-google-maps-api', 'http://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
			if ( is_ssl() ) wp_register_script( 'cn-google-maps-api', 'https://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );

			wp_register_script( 'jquery-gomap', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' , 'cn-google-maps-api' ), '1.3.2', $connections->options->getJavaScriptFooter() );
			wp_register_script( 'jquery-markerclusterer', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'cn-google-maps-api' , 'jquery-gomap' ), '2.0.15', $connections->options->getJavaScriptFooter() );

		} else {

			wp_register_script( 'jquery-gomap', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' ), '1.3.2', $connections->options->getJavaScriptFooter() );
			wp_register_script( 'jquery-markerclusterer', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'jquery-gomap' ), '2.0.15', $connections->options->getJavaScriptFooter() );
		}

		wp_register_script( 'jquery-preloader', CN_URL . "assets/js/jquery.preloader$min.js", array( 'jquery' ), '1.1', $connections->options->getJavaScriptFooter() );

		if ( is_admin() ) {

			wp_register_script( 'cn-ui-admin', CN_URL . "assets/js/cn-admin$min.js", array( 'jquery', 'jquery-preloader' ), CN_CURRENT_VERSION, TRUE );
			wp_register_script( 'cn-widget', CN_URL . "assets/js/widgets$min.js", array( 'jquery' ), CN_CURRENT_VERSION, TRUE );

		} else {

			wp_register_script( 'cn-ui', CN_URL . "assets/js/cn-user$min.js", array( 'jquery', 'jquery-preloader' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
		}

		wp_register_script( 'jquery-qtip', CN_URL . "assets/js/jquery.qtip$min.js", array( 'jquery' ), '2.0.1', $connections->options->getJavaScriptFooter() );

		// Disble this for now, Elegant Theme uses the same registration name in the admin which causes errors.
		// wp_register_script('jquery-spin', CN_URL . 'js/jquery.spin.js', array('jquery'), '1.2.5', $connections->options->getJavaScriptFooter() );

		// Registering  with the handle 'jquery-chosen-min' for legacy support. Remove this at some point. 04/30/2014
		wp_register_script( 'jquery-chosen', CN_URL . "vendor/chosen/chosen.jquery$min.js", array( 'jquery' ), '1.1.0', $connections->options->getJavaScriptFooter() );
		wp_register_script( 'jquery-chosen-min', CN_URL . "vendor/chosen/chosen.jquery$min.js", array( 'jquery' ), '1.1.0', $connections->options->getJavaScriptFooter() );

		wp_register_script( 'jquery-validate' , CN_URL . "vendor/validation/jquery.validate$min.js", array( 'jquery', 'jquery-form' ) , '1.11.1' , $connections->options->getJavaScriptFooter() );
	}

	/**
	 * Registers the CSS libraries that may be enqueued in the admin or frontend.
	 *
	 * @access private
	 * @since 0.7.3.2
	 * @uses wp_register_style()
	 * @return void
	 */
	public static function registerCSS() {

		// Add a filter so cnLocate will search the plugins CSS folder.
		add_filter( 'cn_locate_file_paths', array( __CLASS__, 'coreCSSPath' ) );

		// If SCRIPT_DEBUG is set and TRUE load the non-minified CSS files, otherwise, load the minified files.
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		if ( is_admin() ) {

			wp_register_style( 'cn-admin', CN_URL . "assets/css/cn-admin$min.css", array(), CN_CURRENT_VERSION );
			wp_register_style( 'cn-admin-jquery-ui', CN_URL . 'assets/css/jquery-ui-' . ( 'classic' == get_user_option( 'admin_color' ) ? 'classic' : 'fresh' ) . "$min.css", array(), CN_CURRENT_VERSION );

		} else {

			if ( cnSettingsAPI::get( 'connections', 'compatibility', 'css' ) ) {

				// This will locate the CSS file to be enqueued.
				$coreCSS = cnLocate::file( cnLocate::fileNames( 'cn-user', NULL, NULL, 'css' ), 'url' );
				// var_dump($coreCSS);

				// Registering the CSS with 'connections-user' for legacy support. Remove this at some point. 04/01/2014
				wp_register_style( 'connections-user', $coreCSS, array(), CN_CURRENT_VERSION );

				wp_register_style( 'cn-public', $coreCSS, array(), CN_CURRENT_VERSION );

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

		wp_register_style( 'cn-qtip', CN_URL . "assets/css/jquery.qtip$min.css", array(), '2.0.1' );
		wp_register_style( 'cn-chosen', CN_URL . "vendor/chosen/chosen$min.css", array(), '1.1.0' );
		wp_register_style( 'cn-font-awesome', CN_URL . "vendor/font-awesome/css/font-awesome$min.css", array(), '4.0.3' );

		// Remove the filter that adds the core CSS path to cnLocate.
		remove_filter( 'cn_locate_file_paths', array( __CLASS__, 'coreCSSPath' ) );
	}

	/**
	 * Enqueues the Connections JavaScript libraries on required admin pages.
	 *
	 * @access private
	 * @since 0.7.3.2
	 * @global $connections
	 * @uses wp_enqueue_script()
	 * @param  string $pageHook The current admin page hook.
	 * @return void
	 */
	public static function enqueueAdminScripts( $pageHook ) {
		global $connections;

		if ( ! current_user_can( 'connections_view_menu') ) return;

		// Load on all the Connections admin pages.
		if ( in_array( $pageHook, get_object_vars( $connections->pageHook ) ) ) {

			wp_enqueue_script( 'cn-ui-admin' );
		}

		/*
		 * TinyMCE in WordPress Plugins
		 * http://www.keighl.com/2010/01/tinymce-in-wordpress-plugins/
		 *
		 * For full editor see:
		 * http://dannyvankooten.com/450/tinymce-wysiwyg-editor-in-wordpress-plugin/
		 *
		 * Load the tinyMCE scripts on these pages.
		 */
		$editorPages = array( $connections->pageHook->manage, $connections->pageHook->add );

		if ( in_array( $pageHook, $editorPages ) ) {
			global $concatenate_scripts, $compress_scripts, $compress_css;

			wp_enqueue_script( 'jquery-gomap' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-chosen' );

			if ( version_compare( $GLOBALS['wp_version'], '3.2.999', '<' ) ) {
				$compress_scripts = FALSE; // If the script are compress the TinyMCE doesn't seem to function.

				wp_tiny_mce(
					FALSE , // true makes the editor "teeny"
					array(
						'editor_selector'         => 'tinymce',
						'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
						'theme_advanced_buttons2' => '',
						'inline_styles'           => TRUE,
						'relative_urls'           => FALSE,
						'remove_linebreaks'       => FALSE,
						'plugins'                 => 'paste'
					)
				);
			}
		}

		// Load the core JavaScripts required for meta box UI.
		$metaBoxPages = array( $connections->pageHook->dashboard, $connections->pageHook->manage, $connections->pageHook->add );

		if ( in_array( $pageHook, $metaBoxPages ) ) {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
			wp_enqueue_script( 'cn-widget' );
		}
	}

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
	 * @since 0.7.3.2
	 * @uses wp_enqueue_script()
	 * @return void
	 */
	public static function enqueueScripts() {
		/*
		 * http://beerpla.net/2010/01/13/wordpress-plugin-development-how-to-include-css-and-javascript-conditionally-and-only-when-needed-by-the-posts/
		 * http://beerpla.net/2010/01/15/follow-up-to-loading-css-and-js-conditionally/
		 * http://scribu.net/wordpress/optimal-script-loading.html
		 */

		wp_enqueue_script( 'cn-ui' );
	}

	/**
	 * Attempt to re-register the bundled version of jQuery
	 *
	 * @access private
	 * @since 0.7.6
	 * @uses wp_deregister_script()
	 * @uses wp_register_script()
	 * @return (void)
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
	 * @since 0.7.7
	 * @uses WP_Scripts
	 * @param  (object) $scripts WP_Scripts
	 * @return (void)
	 */
	public static function storeCorejQuery( &$scripts ) {

		self::$corejQuery['jquery'] = $scripts->registered['jquery'];
		self::$corejQuery['jquery-core'] = isset( $scripts->registered['jquery-core'] ) && $scripts->registered['jquery-core'] ? $scripts->registered['jquery-core'] : FALSE;
		self::$corejQuery['jquery-migrate'] = isset( $scripts->registered['jquery-migrate'] ) && $scripts->registered['jquery-migrate'] ? $scripts->registered['jquery-migrate'] : FALSE;
	}

	/**
	 * Enqueues the Connections CSS on the required admin pages.
	 *
	 * @access private
	 * @since 0.7.3.2
	 * @global $connections
	 * @uses wp_enqueue_style()
	 * @param  string $pageHook The current admin page hook.
	 * @return void
	 */
	public static function enqueueAdminStyles( $pageHook ) {
		global $connections;

		if ( ! current_user_can( 'connections_view_menu') ) return;

		// Load on all the Connections admin pages.
		if ( in_array( $pageHook, get_object_vars( $connections->pageHook ) ) ) {
			wp_enqueue_style( 'cn-admin' );
			wp_enqueue_style( 'cn-admin-jquery-ui' );
			wp_enqueue_style( 'cn-font-awesome' );
		}

		// Load the WordPress widgets styles only on these pages.
		$adminPageEntryEdit = array( $connections->pageHook->manage, $connections->pageHook->add );

		if ( in_array( $pageHook, $adminPageEntryEdit ) ) {

			// Earlier version of WP had the widgets CSS in a seperate file.
			if ( version_compare( $GLOBALS['wp_version'], '3.2.999', '<' ) ) wp_enqueue_style( 'connections-admin-widgets', get_admin_url() . 'css/widgets.css' );

			wp_enqueue_style( 'cn-chosen' );
		}
	}

	/**
	 * Enqueues the Connections CSS on the frontend.
	 *
	 * @access private
	 * @since  0.7.3.2
	 * @static
	 * @uses   wp_enqueue_style()
	 *
	 * @return void
	 */
	public static function enqueueStyles() {

		if ( cnSettingsAPI::get( 'connections', 'compatibility', 'css' ) ) {

			wp_enqueue_style( 'cn-public' );
			wp_enqueue_style( 'cn-chosen' );

			// If the custom CSS file was registered, lets enqueue it.
			if ( wp_style_is( 'cn-public-custom', 'registered' ) ) {

				wp_enqueue_style( 'cn-public-custom' );
			}

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
	 * @see     cnLocate::fileNames()
	 * @param  array  $files An indexed array of file names to search for.
	 * @param  string $base The base file name. Passed via filter from  cnLocate::fileNames().
	 * @param  string $name The template part name. Passed via filter from  cnLocate::fileNames().
	 * @param  string $slug The template part slug. Passed via filter from  cnLocate::fileNames().
	 * @param  string $ext  The template file name extension. Passed via filter from  cnLocate::fileNames().
	 *
	 * @return array        An indexed array of file names to search for.
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

				// Increment the insert position. Adding `2` to take into account the updated insert postion
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
	 * @see    registerCSS()
	 * @see    cnLocate::filePaths()
	 * @param  array  $paths An index array containing the file paths to be searched.
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
	 * @see    registerCSS()
	 * @param  array  $paths An index array containing the file paths to be searched.
	 * @return array
	 */
	public static function coreJSPath( $paths ) {

		$paths[9999] = CN_PATH . 'assets/js/';

		return $paths;
	}

}

// Init the class.
cnScript::init();
