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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Utility\_nonce;
use Connections_Directory\Utility\_url;

/**
 * Class cnScript
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnScript {

	/**
	 * Used to store the values of core jQuery.
	 *
	 * @since 0.7.7
	 * @var array
	 */
	private static $corejQuery = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 0.7.6.4
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Init the class.
	 *
	 * @since 0.7.6.4
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

		add_action( 'cn_admin_enqueue_settings_styles', array( __CLASS__, 'inlineBrandiconStyles' ) );
		add_action( 'cn_frontend_enqueue_styles', array( __CLASS__, 'inlineBrandiconStyles' ) );

		add_action( 'wp_print_scripts', array( __CLASS__, 'jQueryFixr' ), 999 );
		add_action( 'wp_default_scripts', array( __CLASS__, 'storeCorejQuery' ), 999 );
	}

	/**
	 * Get asset metadata, to be used for enqueueing assets.
	 *
	 * The dependency metadata is generated via `@wordpress/dependency-extraction-webpack-plugin`.
	 *
	 * @link https://developer.wordpress.org/block-editor/reference-guides/packages/packages-dependency-extraction-webpack-plugin/
	 *
	 * @since 10.4.11
	 *
	 * @param string $key Asset key filename as defined by the script entry.
	 *
	 * @return array{dependencies: array, src: string, version: false|int}
	 */
	public static function getAssetMetadata( $key ) {

		$dependencyPath = Connections_Directory()->pluginPath() . 'assets/dist/require/dependencies.php';
		$assetsPath     = Connections_Directory()->pluginPath() . "assets/dist/{$key}";
		$urlBase        = _url::makeProtocolRelative( Connections_Directory()->pluginURL() );

		$asset = file_exists( $dependencyPath )
			? require $dependencyPath
			: array(
				'dependencies' => array(),
				'version'      => filemtime( $assetsPath ),
			);

		if ( array_key_exists( $key, $asset ) ) {

			$asset = $asset[ $key ];
		}

		$asset['src'] = "{$urlBase}assets/dist/{$key}";

		return $asset;
	}

	/**
	 * Callback for the `admin_init` and `wp` actions.
	 *
	 * Register the external JS libraries that may be enqueued in either the admin or frontend.
	 *
	 * @internal
	 * @since 0.7.3.2
	 */
	public static function registerScripts() {

		// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$url = cnURL::makeProtocolRelative( CN_URL );

		$path = Connections_Directory()->pluginPath();

		// Add noop callback to Google Maps API parameters.
		// @link https://stackoverflow.com/a/75212692
		$googleMapsAPIURL        = 'https://maps.googleapis.com/maps/api/js?v=3&libraries=geometry&callback=Function.prototype';
		$googleMapsAPIBrowserKey = cnSettingsAPI::get( 'connections', 'google_maps_geocoding_api', 'browser_key' );

		if ( 0 < strlen( $googleMapsAPIBrowserKey ) ) {

			$googleMapsAPIURL = add_query_arg( 'key', $googleMapsAPIBrowserKey, $googleMapsAPIURL );
		}

		/*
		 * NOTE: See inc.plugin-compatibility regarding registration of the Google Maps JavaScript API.
		 */
		// wp_register_script( 'google-loader', 'https://www.google.com/jsapi', array(), null, false );
		wp_register_script( 'cn-google-maps-api', $googleMapsAPIURL, array(), CN_CURRENT_VERSION, true );

		wp_register_script( 'jquery-gomap', $url . "assets/vendor/jquery-gomap/jquery.gomap$min.js", array( 'jquery', 'cn-google-maps-api' ), '1.3.3', true );
		wp_register_script( 'jquery-markerclusterer', $url . "assets/vendor/markerclusterer/markerclusterer$min.js", array( 'jquery', 'cn-google-maps-api', 'jquery-gomap' ), '2.1.2', true );

		// The Quform unregisters this script, so let's ensure its registered, so it can be enqueued.
		if ( ! wp_script_is( 'jquery-form', 'registered' ) ) {
			wp_register_script( 'jquery-form', "/wp-includes/js/jquery/jquery.form$min.js", array( 'jquery' ), '4.2.1', true );
		}

		wp_register_script(
			'leaflet',
			"{$url}assets/vendor/leaflet/leaflet.js",
			array(),
			'1.7.1',
			true
		);

		wp_register_script(
			'leaflet-control-geocoder',
			"{$url}assets/vendor/leaflet/geocoder/Control.Geocoder.js",
			array( 'leaflet' ),
			'2.4.0',
			true
		);

		/*
		 * Create an array of script handles of Leaflet related map dependencies.
		 * NOTE: `leaflet` is not added to the array because it is already required by `leaflet-control-geocoder`.
		 */
		$mapDependencies = array(
			'leaflet-control-geocoder',
		);

		// Only register the Google Maps API related Leaflet scripts if a browser API key has been entered by user.
		if ( 0 < strlen( $googleMapsAPIBrowserKey ) ) {

			wp_register_script(
				'leaflet-basemap-googlemaps',
				"{$url}assets/vendor/leaflet/basemap-providers/Leaflet.GoogleMutant.js",
				array( 'leaflet', 'cn-google-maps-api' ),
				'0.13.5',
				true
			);

			wp_register_script(
				'leaflet-control-geocoder-google-native',
				"{$url}assets/js/leaflet/geocoderGoogleNative/Geocoder.Google.Native{$min}.js",
				array( 'leaflet-control-geocoder', 'cn-google-maps-api' ),
				'1.0',
				true
			);

			/*
			 * Overwrite the map dependencies with the Google Maps API related dependencies.
			 * NOTE: The "core" Leaflet dependencies do not need to be added to array due to already being required
			 *       when registering the Google Maps API related dependencies.
			 */
			$mapDependencies = array(
				'leaflet-basemap-googlemaps',
				'leaflet-control-geocoder-google-native',
			);
		}

		wp_register_script(
			'jquery-mapblock',
			"{$url}assets/dist/content-block/map/script.js",
			// Merge in the map dependencies.
			array_merge(
				array(
					'jquery',
				),
				$mapDependencies
			),
			CN_CURRENT_VERSION,
			true
		);

		if ( is_admin() ) {

			wp_register_script(
				'cn-ui-admin',
				$url . "assets/js/cn-admin$min.js",
				// Merge in the map dependencies.
				array_merge(
					array(
						'jquery',
						'jquery-validate',
						'jquery-ui-sortable',
						'jquery-ui-resizable',
					),
					$mapDependencies
				),
				Connections_Directory::VERSION . '-' . filemtime( "{$path}assets/js/cn-admin{$min}.js" ),
				true
			);

			wp_register_script( 'cn-system-info', $url . "assets/js/cn-system-info$min.js", array( 'jquery', 'jquery-validate', 'jquery-form', 'wp-util' ), CN_CURRENT_VERSION, true );
			wp_register_script( 'cn-setting-sortable-repeatable-input-list', $url . "assets/js/cn-setting-sortable-repeatable-input-list$min.js", array( 'jquery', 'jquery-ui-sortable' ), CN_CURRENT_VERSION, true );
			wp_register_script( 'cn-csv-export', $url . "assets/js/cn-csv-export$min.js", array( 'jquery', 'wp-util' ), CN_CURRENT_VERSION, true );
			wp_register_script( 'cn-csv-import', $url . "assets/js/cn-csv-import$min.js", array( 'jquery', 'wp-util', 'shortcode' ), CN_CURRENT_VERSION, true );
			wp_register_script( 'cn-widget', $url . "assets/js/widgets$min.js", array( 'jquery' ), CN_CURRENT_VERSION, true );

			wp_register_script(
				'cn-icon-picker',
				"{$url}assets/dist/admin/icon-picker/script.js",
				array( 'jquery-ui-dialog' ),
				Connections_Directory::VERSION . '-' . filemtime( "{$path}assets/dist/admin/icon-picker/script.js" ),
				true
			);

			$strings = array(
				'showDetails'              => __( 'Show Details', 'connections' ),
				'hideDetails'              => __( 'Hide Details', 'connections' ),
				'showDetailsTitle'         => __( 'Click to show details.', 'connections' ),
				'hideDetailsTitle'         => __( 'Click to hide details.', 'connections' ),
				'imageMaxFileSize'         => wp_max_upload_size(),
				'imageMaxFileSizeExceeded' => sprintf(
				/* translators: max upload filesize. */
					__(
						'Selected image exceeds maximum upload file size of %s. Please choose a different image.',
						'connections'
					),
					esc_html( size_format( wp_max_upload_size() ) )
				),
				'categoryDiv'              => array(
					'_cnonce' => _nonce::create( 'set_category_div_height' ),
					'height'  => Connections_Directory()->currentUser->getCategoryDivHeight(),
				),
			);

			// Strings to be used for setting the Leaflet maps `attribution`.
			$leaflet  = '<a href="https://leafletjs.com/" target="_blank" title="Leaflet">Leaflet</a>';
			$backlink = '<a href="https://connections-pro.com/" target="_blank" title="Connections Business Directory plugin for WordPress">Connections Business Directory</a> | ' . $leaflet;

			$osm       = '&copy; <a target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
			$wikimedia = '<a target="_blank" href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>';

			// Leaflet related configuration parameters. Defines the tile provider and geocoder provider.
			$map = array(
				'basemapProviders' => array(
					'google-roadmap' => array(
						'group'        => 'google',
						'name'         => 'Google Roadmap',
						'layer'        => 'roadmap', // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
						'key_required' => true,
						'attribution'  => $backlink,
					),
					'osm'            => array(
						'group'        => 'osm',
						'name'         => 'OpenStreetMap',
						'tileLayer'    => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
						'subdomains'   => array( 'a', 'b', 'c', '' ),
						'key_required' => false,
						'attribution'  => $backlink . ' | ' . $osm,
						'maxZoom'      => '19',
					),
					'wikimedia'      => array(
						'group'        => 'osm',
						'name'         => 'Wikimedia',
						'tileLayer'    => '//maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png',
						'key_required' => false,
						'attribution'  => $backlink . ' | ' . $wikimedia . ' | ' . $osm,
						'maxZoom'      => '19',
					),
				),
				'basemapDefault'   => 'osm',
				'geocoderDefault'  => 'osm',
				'geocoderAPIKey'   => esc_js( trim( $googleMapsAPIBrowserKey ) ),
			);

			/*
			 * If a browser API key has been entered by user,
			 * change the tile provider and geocoder provider to Google Maps.
			 */
			if ( 0 < strlen( $googleMapsAPIBrowserKey ) ) {

				$map['basemapDefault']  = 'google-roadmap';
				$map['geocoderDefault'] = 'google';
			}

			$urls = array(
				'url'                   => Connections_Directory()->pluginURL(),
				'url_protocol_relative' => $url,
			);

			wp_localize_script( 'cn-ui-admin', 'cn_string', $strings );
			wp_localize_script( 'cn-ui-admin', 'cnMap', $map );
			wp_localize_script( 'cn-ui-admin', 'cnBase', $urls );

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

			wp_register_script( 'cn-ui', $url . "assets/js/cn-user$min.js", array( 'jquery' ), CN_CURRENT_VERSION, true );
		}

		// Registering  with the handle 'jquery-chosen-min' for legacy support. Remove this at some point. 04/30/2014
		wp_register_script( 'jquery-chosen', $url . "assets/vendor/chosen/chosen.jquery$min.js", array( 'jquery' ), '1.8.7', true );
		wp_register_script( 'jquery-chosen-min', $url . "assets/vendor/chosen/chosen.jquery$min.js", array( 'jquery' ), '1.8.7', true );

		wp_register_script(
			'jquery-validate',
			"{$url}assets/vendor/validation/jquery.validate.js",
			array(
				'jquery',
				'jquery-form',
			),
			'1.19.3',
			true
		);

		wp_register_script( 'picturefill', $url . "assets/vendor/picturefill/picturefill$min.js", array(), '3.0.2', true );
		wp_register_script( 'js-cookie', $url . 'assets/vendor/js-cookie/js.cookie.js', array(), '2.2.1', true );
	}

	/**
	 * Callback for the `admin_init` and `wp` actions.
	 *
	 * Registers the CSS libraries that may be enqueued in the admin or frontend.
	 *
	 * @internal
	 * @since 0.7.3.2
	 */
	public static function registerCSS() {

		// Add a filter so cnLocate will search the plugins CSS folder.
		add_filter( 'cn_locate_file_paths', array( __CLASS__, 'coreCSSPath' ) );

		// If SCRIPT_DEBUG is set and TRUE load the non-minified CSS files, otherwise, load the minified files.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$url = cnURL::makeProtocolRelative( CN_URL );
		$rtl = is_rtl() ? '.rtl' : '';

		$path = Connections_Directory()->pluginPath();

		wp_register_style(
			'leaflet',
			"{$url}assets/vendor/leaflet/leaflet.css",
			array(),
			'1.7.1'
		);

		wp_register_style(
			'leaflet-control-geocoder',
			"{$url}assets/vendor/leaflet/geocoder/Control.Geocoder.css",
			array( 'leaflet' ),
			'2.4.0'
		);

		wp_register_style(
			'cn-admin',
			"{$url}assets/dist/admin/style{$rtl}.css",
			array(),
			Connections_Directory::VERSION . '-' . filemtime( "{$path}assets/dist/admin/style{$rtl}.css" )
		);

		wp_register_style( 'cn-admin-jquery-ui', $url . 'assets/css/jquery-ui-' . ( 'classic' == get_user_option( 'admin_color' ) ? 'classic' : 'fresh' ) . "$min.css", array(), CN_CURRENT_VERSION );
		wp_register_style( 'cn-admin-jquery-datepicker', $url . "assets/css/datepicker$min.css", array( 'cn-admin-jquery-ui' ), CN_CURRENT_VERSION );

		// This will locate the CSS file to be enqueued.
		// $coreCSS = cnLocate::file( cnLocate::fileNames( 'cn-user', NULL, NULL, 'css' ), 'url' );
		// var_dump($coreCSS);

		// Registering the CSS with 'connections-user' for legacy support. Remove this at some point. 04/01/2014
		// wp_register_style( 'connections-user', $coreCSS, array(), CN_CURRENT_VERSION );
		wp_register_style(
			'cn-public',
			"{$url}assets/dist/frontend/style{$rtl}.css",
			array(),
			Connections_Directory::VERSION . '-' . filemtime( "{$path}assets/dist/frontend/style{$rtl}.css" )
		);

		// This will locate the custom CSS file to be enqueued.
		$customCSS = cnLocate::file( cnLocate::fileNames( 'cn-custom', null, null, 'css' ), 'url' );
		// var_dump($customCSS);

		// If a custom CSS file was found, lets register it.
		if ( $customCSS ) {

			// Check to see if the core CSS file was registered since it can be disabled.
			// Add it to the $required array to be used when registering the custom CSS file.
			$required = wp_style_is( 'cn-public', 'registered' ) ? array( 'cn-public' ) : array();

			wp_register_style( 'cn-public-custom', $customCSS, $required, CN_CURRENT_VERSION );
		}

		wp_register_style( 'cn-chosen', $url . "assets/vendor/chosen/chosen$min.css", array(), '1.8.7' );
		wp_register_style( 'cn-brandicons', $url . 'assets/vendor/icomoon-brands/style.css', array(), CN_CURRENT_VERSION );
		wp_register_style( 'cn-font-awesome', $url . "assets/vendor/fontawesome/css/all$min.css", array(), '5.8.1' );
		wp_register_style( 'cn-fonticonpicker', $url . 'assets/vendor/fonticonpicker/css/base/jquery.fonticonpicker.min.css', array(), '3.3.1' );
		wp_register_style( 'cn-fonticonpicker-theme-grey', $url . 'assets/vendor/fonticonpicker/css/themes/dark-grey-theme/jquery.fonticonpicker.darkgrey.min.css', array( 'cn-fonticonpicker' ), '3.3.1' );

		// Remove the filter that adds the core CSS path to cnLocate.
		remove_filter( 'cn_locate_file_paths', array( __CLASS__, 'coreCSSPath' ) );
	}

	/**
	 * Callback for the `admin_enqueue_scripts` action.
	 *
	 * Enqueues the Connections JavaScript libraries on required admin pages.
	 *
	 * @internal
	 * @since 0.7.3.2
	 *
	 * @global $concatenate_scripts
	 * @global $compress_scripts
	 * @global $compress_css
	 *
	 * @param string $pageHook The current admin page hook.
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

		if ( property_exists( $instance->pageHook, 'manage' ) ) {
			$editPageHooks[] = $instance->pageHook->manage;
		}

		if ( property_exists( $instance->pageHook, 'add' ) ) {
			$editPageHooks[] = $instance->pageHook->add;
		}

		$editPages = apply_filters( 'cn_admin_required_edit_scripts', $editPageHooks );

		if ( in_array( $pageHook, $editPages ) ) {
			global $concatenate_scripts, $compress_scripts, $compress_css;

			wp_enqueue_script( 'jquery-chosen' );

			do_action( 'cn_admin_enqueue_edit_scripts', $pageHook );
		}

		$metaboxPageHooks = array();

		if ( property_exists( $instance->pageHook, 'dashboard' ) ) {
			$metaboxPageHooks[] = $instance->pageHook->dashboard;
		}

		if ( property_exists( $instance->pageHook, 'manage' ) ) {
			$metaboxPageHooks[] = $instance->pageHook->manage;
		}

		if ( property_exists( $instance->pageHook, 'add' ) ) {
			$metaboxPageHooks[] = $instance->pageHook->add;
		}

		// Load the core JavaScripts required for metabox UI.
		$metaboxPages = apply_filters( 'cn_admin_required_metabox_scripts', $metaboxPageHooks );

		if ( in_array( $pageHook, $metaboxPages ) ) {

			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
			wp_enqueue_script( 'cn-widget' );

			do_action( 'cn_admin_enqueue_metabox_scripts', $pageHook );

			add_action( 'admin_footer-' . $instance->pageHook->dashboard, array( __CLASS__, 'adminFooterScript' ) );
			add_action( 'admin_footer-' . $instance->pageHook->manage, array( __CLASS__, 'adminFooterScript' ) );
			add_action( 'admin_footer-' . $instance->pageHook->add, array( __CLASS__, 'adminFooterScript' ) );
		}
	}

	/**
	 * Callback for the `admin_footer-{$page-hook}` callback.
	 *
	 * @internal
	 * @since unknown
	 */
	public static function adminFooterScript() {
		?>
		<script>postboxes.add_postbox_toggles(pagenow);</script>
		<?php
	}

	/**
	 * @internal
	 * @since unknown
	 */
	public static function enqueue() {
		global $wp_query;

		$posts   = $wp_query->posts;
		$pattern = get_shortcode_regex();

		foreach ( $posts as $post ) {

			if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches )
				&& array_key_exists( 2, $matches )
				&& in_array( 'connections', $matches[2] )
			) {
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ) );
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueStyles' ) );

				break;
			}
		}
	}

	/**
	 * Callback for the `wp_enqueue_scripts` action.
	 *
	 * Enqueues the Connections JavaScript libraries on the frontend.
	 *
	 * @internal
	 * @since 0.7.3.2
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
	 * Callback for the `wp_print_scripts` action.
	 *
	 * Attempt to re-register the bundled version of jQuery
	 *
	 * @internal
	 * @since 0.7.6
	 */
	public static function jQueryFixr() {

		if ( ! cnSettingsAPI::get( 'connections', 'connections_compatibility', 'jquery' ) ) {
			return;
		}

		wp_deregister_script( 'jquery' );

		if ( self::$corejQuery['jquery-core'] && self::$corejQuery['jquery-migrate'] ) {

			wp_register_script( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), self::$corejQuery['jquery-core']->ver );
			wp_register_script( 'jquery-core', '/wp-includes/js/jquery/jquery.js', array(), self::$corejQuery['jquery-core']->ver );
			wp_register_script( 'jquery-migrate', '/wp-includes/js/jquery/jquery-migrate.js', array(), self::$corejQuery['jquery-migrate']->ver );

		} else {

			wp_register_script( 'jquery', '/wp-includes/js/jquery/jquery.js', array(), self::$corejQuery['jquery']->ver );

		}
	}

	/**
	 * Callback for the `wp_default_scripts` action.
	 *
	 * Store the values of core jQuery.
	 *
	 * @internal
	 * @since 0.7.7
	 *
	 * @param WP_Scripts $scripts
	 */
	public static function storeCorejQuery( $scripts ) {

		self::$corejQuery['jquery']         = $scripts->registered['jquery'];
		self::$corejQuery['jquery-core']    = isset( $scripts->registered['jquery-core'] ) && $scripts->registered['jquery-core'] ? $scripts->registered['jquery-core'] : false;
		self::$corejQuery['jquery-migrate'] = isset( $scripts->registered['jquery-migrate'] ) && $scripts->registered['jquery-migrate'] ? $scripts->registered['jquery-migrate'] : false;
	}

	/**
	 * Callback for the `admin_enqueue_scripts` action.
	 *
	 * Enqueues the Connections CSS on the required admin pages.
	 *
	 * @internal
	 * @since 0.7.3.2
	 *
	 * @param string $pageHook The current admin page hook.
	 */
	public static function enqueueAdminStyles( $pageHook ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Load on all the Connections admin pages.
		if ( in_array( $pageHook, get_object_vars( $instance->pageHook ) ) ) {

			wp_enqueue_style( 'cn-admin' );
			// wp_enqueue_style( 'cn-admin-jquery-ui' );
			wp_enqueue_style( 'cn-fonticonpicker-theme-grey' );
			wp_enqueue_style( 'cn-font-awesome' ); // Must enqueue after fonticonpicker!
			// wp_enqueue_style( 'cn-brandicons' );
			wp_enqueue_style( 'leaflet-control-geocoder' );

			if ( is_rtl() ) {

				wp_enqueue_style( 'cn-public-rtl' );
			}

			do_action( 'cn_admin_enqueue_styles', $pageHook );
		}

		$editPageHooks = array();

		if ( property_exists( $instance->pageHook, 'manage' ) ) {
			$editPageHooks[] = $instance->pageHook->manage;
		}

		if ( property_exists( $instance->pageHook, 'add' ) ) {
			$editPageHooks[] = $instance->pageHook->add;
		}

		$editPages = apply_filters( 'cn_admin_required_edit_scripts', $editPageHooks );

		if ( in_array( $pageHook, $editPages ) ) {

			wp_enqueue_style( 'cn-admin-jquery-ui' );
			wp_enqueue_style( 'cn-chosen' );

			do_action( 'cn_admin_enqueue_edit_styles', $pageHook );
		}

		$settingsPageHooks = array();

		if ( property_exists( $instance->pageHook, 'settings' ) ) {
			$settingsPageHooks[] = $instance->pageHook->settings;
		}

		if ( in_array( $pageHook, $settingsPageHooks ) ) {

			wp_enqueue_style( 'cn-fonticonpicker-theme-grey' );
			wp_enqueue_style( 'cn-font-awesome' ); // Must enqueue after fonticonpicker!
			wp_enqueue_style( 'cn-brandicons' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );

			do_action( 'cn_admin_enqueue_settings_styles', $pageHook );
		}
	}

	/**
	 * Whether to enqueue a registered CSS file.
	 *
	 * @since 10.4
	 *
	 * @return bool
	 */
	public static function maybeEnqueueStyle() {

		$homeID = cnSettingsAPI::get( 'connections', 'home_page', 'page_id' );
		$object = get_queried_object();

		if ( ! $object instanceof \WP_Post ) {

			return false;
		}

		return has_shortcode( $object->post_content, 'connections' )
			   || has_shortcode( $object->post_content, 'cn-entry' )
			   || has_block( 'connections-directory/shortcode-connections', $object )
			   || $object->ID === (int) $homeID;
	}

	/**
	 * Callback for the `wp_enqueue_scripts` action.
	 *
	 * Enqueues the Connections CSS on the frontend.
	 *
	 * @internal
	 * @since 0.7.3.2
	 */
	public static function enqueueStyles() {

		wp_enqueue_style( 'cn-public' );
		wp_enqueue_style( 'cn-brandicons' );

		if ( self::maybeEnqueueStyle() ) {
			wp_enqueue_style( 'leaflet-control-geocoder' );
		}

		if ( is_rtl() ) {

			wp_enqueue_style( 'cn-admin-rtl' );
		}

		wp_enqueue_style( 'cn-chosen' );

		// If the custom CSS file was registered, lets enqueue it.
		if ( wp_style_is( 'cn-public-custom', 'registered' ) ) {

			wp_enqueue_style( 'cn-public-custom' );
		}

		do_action( 'cn_frontend_enqueue_styles' );
	}

	/**
	 * Callback for the `cn_admin_enqueue_settings_styles` and `cn_frontend_enqueue_styles` action hooks.
	 *
	 * Output the CSS for the user defined colors for the social network icons.
	 *
	 * @internal
	 * @since 8.44
	 *
	 * @link https://www.cssigniter.com/late-enqueue-inline-css-wordpress/
	 */
	public static function inlineBrandiconStyles() {

		$networks        = cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'social-network-types' );
		$shape           = cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'shape' );
		$scheme          = cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'color-scheme' );
		$transparent     = (bool) cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'background-transparent' );
		$background      = cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'background-color' );
		$backgroundHover = cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'background-color-hover' );
		$foreground      = cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'foreground-color' );
		$foregroundHover = cnSettingsAPI::get( 'connections', 'fieldset-social-networks', 'foreground-color-hover' );

		if ( false === $networks || ( ! isset( $networks['icon'] ) || ! is_array( $networks['icon'] ) ) ) {
			return;
		}

		$css = '';

		if ( 'global' === $scheme && ! is_admin() ) {

			$css .= "i[class^=cn-brandicon]::before { color: {$foreground}; }" . PHP_EOL;
			$css .= "i[class^=cn-brandicon]:hover::before { color: {$foregroundHover}; }" . PHP_EOL;

			if ( $transparent ) {

				$css .= "i[class^='cn-brandicon'] { background-color: transparent; }" . PHP_EOL;
				$css .= "i[class^='cn-brandicon']:hover { background-color: transparent; }" . PHP_EOL;

			} else {

				$css .= "i[class^='cn-brandicon'] { background-color: {$background}; }" . PHP_EOL;
				$css .= "i[class^='cn-brandicon']:hover { background-color: {$backgroundHover}; }" . PHP_EOL;
			}

		} else {

			foreach ( $networks['icon'] as $slug => $icon ) {

				if ( 0 < strlen( $icon['foreground-color'] ) ) {

					$css .= "i.cn-brandicon-{$icon['slug']}::before { color: var( --color, {$icon['foreground-color']} ); }" . PHP_EOL;
				}

				if ( 0 < strlen( $icon['foreground-color-hover'] ) ) {

					$css .= "i.cn-brandicon-{$icon['slug']}:hover::before { color: var( --color, {$icon['foreground-color-hover']} ); }" . PHP_EOL;
				}

				if ( '1' === $icon['background-transparent'] ) {

					$css .= "i.cn-brandicon-{$icon['slug']} { background-color: transparent; }" . PHP_EOL;
					$css .= "i.cn-brandicon-{$icon['slug']}:hover { background-color: transparent; }" . PHP_EOL;

				} else {

					if ( 0 < strlen( $icon['background-color'] ) ) {

						$css .= "i.cn-brandicon-{$icon['slug']} { background-color: {$icon['background-color']}; }" . PHP_EOL;
					}

					if ( 0 < strlen( $icon['background-color-hover'] ) ) {

						$css .= "i.cn-brandicon-{$icon['slug']}:hover { background-color: {$icon['background-color-hover']}; }" . PHP_EOL;
					}

				}

			}
		}

		switch ( $shape ) {

			case 'circle':
				$css .= 'i[class^=cn-brandicon] { border-radius: 50%; }' . PHP_EOL;
				break;

			case 'square':
				$css .= 'i[class^=cn-brandicon] { border-radius: 0; }' . PHP_EOL;
				break;
		}

		// $css .= "i[class^=cn-brandicon]:before { color: #000; }" . PHP_EOL;

		// wp_register_style( 'cn-brandicons-custom', FALSE );
		// wp_enqueue_style( 'cn-brandicons-custom' );

		if ( 0 < strlen( $css ) ) {

			wp_add_inline_style( 'cn-brandicons', trim( wp_strip_all_tags( $css ) ) );
		}
	}

	/**
	 * Callback for the `cn_locate_file_names` filter.
	 *
	 * This is the callback function that will add the minified CSS and JS
	 * file names to the file name array.
	 *
	 * The minified file names will only be added if SCRIPT_DEBUG is defined
	 * and set to true.
	 *
	 * @internal
	 * @since 0.8
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
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return $files;
		}

		if ( 'css' === $ext || 'js' === $ext ) {

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
	 * Callback for the `cn_locate_file_paths` filter.
	 *
	 * This a callback for the filter `cn_locate_file_paths` which adds
	 * the core plugin CSS file path to the file paths which are searched
	 * when locating a CSS file.
	 *
	 * @internal
	 * @since 0.8
	 *
	 * @see registerCSS()
	 * @see cnLocate::filePaths()
	 *
	 * @param array $paths An index array containing the file paths to be searched.
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
	 * @internal
	 * @since 0.8
	 *
	 * @see registerCSS()
	 *
	 * @param array $paths An index array containing the file paths to be searched.
	 *
	 * @return array
	 */
	public static function coreJSPath( $paths ) {

		$paths[9999] = CN_PATH . 'assets/js/';

		return $paths;
	}
}
