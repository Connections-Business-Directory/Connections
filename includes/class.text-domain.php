<?php

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
	 * @access public
	 * @since  8.11
	 *
	 * @deprecated 8.16 Use cnText_Domain::register()
	 * @see cnText_Domain::register()
	 *
	 * @param string $domain
	 *
	 * @return static
	 */
	public static function create( $domain ) {

		return new static( $domain );
	}

	/**
	 * @access public
	 * @since  8.16
	 *
	 * @param string $domain
	 * @param int    $priority
	 *
	 * @return static
	 */
	public static function register( $domain, $priority = 10 ) {

		$instance = new static( $domain );
		$instance->addAction( $priority );

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
	 */
	public function __construct( $domain ) {

		$this->domain = $domain;
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
	 * @access public
	 * @since  8.11
	 *
	 * Load the localization.
	 */
	public function load() {

		// Plugin textdomain. This should match the one set in the plugin header.
		$domain = $this->domain;

		// Set filter for plugin's languages directory
		$languagesDirectory = apply_filters( "cn_{$domain}_languages_directory", CN_DIR_NAME . '/languages/' );

		// Traditional WordPress plugin locale filter
		$locale   = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$fileName = sprintf( '%1$s-%2$s.mo', $domain, $locale );

		// Setup paths to current locale file
		$local  = $languagesDirectory . $fileName;
		$global = WP_LANG_DIR . "/{$domain}/" . $fileName;

		if ( file_exists( $global ) ) {

			// Look in global `../wp-content/languages/{$domain}/` folder.
			load_textdomain( $domain, $global );

		} elseif ( file_exists( $local ) ) {

			// Look in local `../wp-content/plugins/{plugin-directory}/languages/` folder.
			load_textdomain( $domain, $local );

		} else {

			// Load the default language files
			load_plugin_textdomain( $domain, FALSE, $languagesDirectory );
		}
	}
}
