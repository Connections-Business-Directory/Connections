<?php

use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

/**
 * @since 8.11
 *
 * Class cnText_Domain
 */
class cnText_Domain {

	/**
	 * @since 8.11
	 * @var string
	 */
	var $domain = '';

	/**
	 * @since 8.20
	 * @var string
	 */
	var $basename = '';

	/**
	 * @access public
	 * @since  8.11
	 *
	 * @deprecated 8.16 Use cnText_Domain::register()
	 * @see cnText_Domain::register()
	 *
	 * @param string $domain
	 * @param string $basename
	 *
	 * @return static
	 */
	public static function create( $domain, $basename = '' ) {

		_deprecated_function( __METHOD__, '9.15', 'cnText_Domain::register()' );

		return new self( $domain, $basename );
	}

	/**
	 * Registers a text domain.
	 *
	 * If $priority is set to an integer it will be loaded via an action ran on the `plugins_loaded` action hook.
	 * If $priority is set to `load` it will be loaded.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param string     $domain   The text domain to register.
	 * @param string     $basename The plugin basename of the text domain to be loaded.
	 * @param int|string $priority The priority to load the text domain on the `plugins_loaded` action hook.
	 *                             If `load` is passed as a value, then the text domain will be loaded.
	 *
	 * @return static
	 */
	public static function register( $domain, $basename = '', $priority = 10 ) {

		$instance = new self( $domain, $basename );

		if ( is_int( $priority ) ) {

			$instance->addAction( $priority );

		} elseif ( 'load' === $priority ) {

			$instance->load();
		}

		return $instance;
	}

	/**
	 * cnText_Domain constructor.
	 *
	 * NOTE: The text domain must match the slug of the plugin.
	 * NOTE: Textdomain should match the one set in the plugin header.
	 * NOTE: If the plugin is hosted on wordpress.org it must be the slug of the plugin URL (wordpress.org/plugins/<slug>).
	 * NOTE: The text domain name must use dashes and not underscores.
	 *
	 * @link https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#text-domains
	 *
	 * @access public
	 * @since  8.11
	 *
	 * @param string $domain
	 * @param string $basename
	 */
	public function __construct( $domain, $basename = '' ) {

		$this->domain   = $domain;
		$this->basename = $basename;
	}

	/**
	 * NOTE: Any calls to load_plugin_textdomain should be in a function attached to the `plugins_loaded` action hook.
	 * @link http://ottopress.com/2013/language-packs-101-prepwork/
	 *
	 * NOTE: Any portion of the plugin w/ translatable strings should be bound to the plugins_loaded action hook or later.
	 *
	 * @access public
	 * @since  8.11
	 *
	 * @param int $priority
	 *
	 * @return cnText_Domain
	 */
	public function addAction( $priority = 10 ) {

		add_action( 'plugins_loaded', array( $this, 'load' ), $priority );

		return $this;
	}

	/**
	 * Load the localization.
	 *
	 * 1 - ../wp-content/languages/{text-domain}/{textdomain}-{locale}              (Custom Folder)
	 * 2 - ../wp-content/languages/plugins/{textdomain}-{locale}                    (Language Pack Folder)
	 * 3 - ../wp-content/plugins/{plugin-directory}/languages/{textdomain}-{locale} (Distributed with plugin)
	 *
	 * @access public
	 * @since  8.11
	 */
	public function load() {

		// Plugin textdomain. This should match the one set in the plugin header.
		$domain = $this->domain;

		// Plugin folder name.
		$folder = 0 < strlen( $this->basename ) ? dirname( $this->basename ) : $domain;

		// Set filter for plugin's languages directory
		$relativePath = apply_filters( "cn_{$domain}_languages_directory", "{$folder}/languages/" );

		// Traditional WordPress plugin locale filter
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$file   = sprintf( '%1$s-%2$s.mo', $domain, $locale );

		// `../wp-content/languages/{$domain}/` folder. (Custom Folder)
		$custom = WP_LANG_DIR . "/{$domain}/{$file}";

		// Look in Custom Folder `../wp-content/languages/{$domain}/` folder.
		if ( file_exists( $custom ) ) {

			load_textdomain( $domain, $custom );

		// Load the default language files from the Language Packs folder and then from the those distributed with plugin.
		} else {

			load_plugin_textdomain( $domain, false, $relativePath );
		}
	}
}
