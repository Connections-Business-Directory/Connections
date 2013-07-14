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

		// Register the CSS JavaScript libraries.
		add_action( 'init', array( 'cnScript', 'registerScripts' ) );
		add_action( 'init', array( 'cnScript', 'registerCSS' ) );

		// Enqueue the frontend scripts and CSS.
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
		 * compatibility with themes and other plugins the enqueue Google Maps but do not provide a
		 * method to disable it. So I will, unless we're in the admin, because the geocode function will
		 * require it.
		 */
		if ( $connections->options->getGoogleMapsAPI() || is_admin() ) {
			if ( ! is_ssl() ) wp_register_script( 'cn-google-maps-api', 'http://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
			if ( is_ssl() ) wp_register_script( 'cn-google-maps-api', 'https://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );


			wp_register_script( 'jquery-gomap-min', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' , 'cn-google-maps-api' ), '1.3.2', $connections->options->getJavaScriptFooter() );
			wp_register_script( 'jquery-markerclusterer-min', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'cn-google-maps-api' , 'jquery-gomap-min' ), '2.0.15', $connections->options->getJavaScriptFooter() );
		} else {
			wp_register_script( 'jquery-gomap-min', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' ), '1.3.2', $connections->options->getJavaScriptFooter() );
			wp_register_script( 'jquery-markerclusterer-min', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'jquery-gomap-min' ), '2.0.15', $connections->options->getJavaScriptFooter() );
		}

		if ( is_admin() ) {
			wp_register_script( 'cn-ui-admin', CN_URL . "assets/js/cn-admin$min.js", array( 'jquery' ), CN_CURRENT_VERSION, TRUE );
			wp_register_script( 'cn-widget', CN_URL . "assets/js/widgets$min.js", array( 'jquery' ), CN_CURRENT_VERSION, TRUE );
		} else {
			wp_register_script( 'cn-ui', CN_URL . "assets/js/cn-user$min.js", array( 'jquery', 'jquery-preloader' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
		}

		wp_register_script( 'jquery-qtip', CN_URL . "assets/js/jquery.qtip$min.js", array( 'jquery' ), '2.0.1', $connections->options->getJavaScriptFooter() );
		wp_register_script( 'jquery-preloader', CN_URL . "assets/js/jquery.preloader$min.js", array( 'jquery' ), '1.1', $connections->options->getJavaScriptFooter() );

		// Disble this for now, Elegant Theme uses the same registration name in the admin which causes errors.
		// wp_register_script('jquery-spin', CN_URL . 'js/jquery.spin.js', array('jquery'), '1.2.5', $connections->options->getJavaScriptFooter() );

		wp_register_script( 'jquery-chosen-min', CN_URL . "assets/js/jquery.chosen$min.js", array( 'jquery' ), '0.9.11', $connections->options->getJavaScriptFooter() );
		wp_register_script( 'jquery-validate' , CN_URL . "assets/js/jquery.validate$min.js", array( 'jquery', 'jquery-form' ) , '1.9.0' , $connections->options->getJavaScriptFooter() );
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

		// If SCRIPT_DEBUG is set and TRUE load the non-minified CSS files, otherwise, load the minified files.
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		if ( is_admin() ) {
			wp_register_style( 'cn-admin', CN_URL . "assets/css/cn-admin$min.css", array(), CN_CURRENT_VERSION );
			wp_register_style( 'cn-admin-jquery-ui', CN_URL . 'assets/css/jquery-ui-' . ( 'classic' == get_user_option( 'admin_color' ) ? 'classic' : 'fresh' ) . "$min.css", array(), CN_CURRENT_VERSION );
		} else {
			wp_register_style( 'connections-user', CN_URL . "assets/css/cn-user$min.css", array(), CN_CURRENT_VERSION );
			wp_register_style( 'connections-qtip', CN_URL . "assets/css/jquery.qtip$min.css", array(), '2.0.1' );
		}

		wp_register_style( 'connections-chosen', CN_URL . "assets/css/chosen$min.css", array(), '0.9.11' );
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
			wp_enqueue_script( 'jquery-preloader' );
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

			wp_enqueue_script( 'jquery-gomap-min' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-chosen-min' );

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
		}

		// Load the WordPress widgets styles only on these pages.
		$adminPageEntryEdit = array( $connections->pageHook->manage, $connections->pageHook->add );

		if ( in_array( $pageHook, $adminPageEntryEdit ) ) {

			// Earlier version of WP had the widgets CSS in a seperate file.
			if ( version_compare( $GLOBALS['wp_version'], '3.2.999', '<' ) ) wp_enqueue_style( 'connections-admin-widgets', get_admin_url() . 'css/widgets.css' );

			wp_enqueue_style( 'connections-chosen' );
		}
	}

	/**
	 * Enqueues the Connections CSS on the frontend.
	 *
	 * @access private
	 * @since 0.7.3.2
	 * @uses wp_enqueue_style()
	 * @return void
	 */
	public static function enqueueStyles() {
		wp_enqueue_style( 'connections-user' );
		wp_enqueue_style( 'connections-chosen' );
		wp_enqueue_style( 'connections-qtip' );
	}

}