<?php

/**
 * Class cnDependency
 *
 * @since  8.5.24
 */
class cnDependency {

	/**
	 * Load the plugin dependencies.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 */
	public static function register() {

		self::manual();

		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Manually load dependencies.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 */
	private static function manual() {

		// Add the default filters.
		require_once CN_PATH . 'includes/inc.default-filters.php';

		// Shortcodes
		// NOTE This is required in both the admin and frontend. The shortcode callback is used on the Dashboard admin page.
		require_once CN_PATH . 'includes/shortcode/inc.shortcodes.php';

		if ( is_admin() ) {

			// Load the templates used on the Dashboard admin page.
			include_once CN_TEMPLATE_PATH . 'dashboard-recent-added/dashboard-recent-added.php';
			include_once CN_TEMPLATE_PATH . 'dashboard-recent-modified/dashboard-recent-modified.php';
			include_once CN_TEMPLATE_PATH . 'dashboard-upcoming/dashboard-upcoming.php';

		}

		// Include the core templates that use the Template APIs introduced in 0.7.6
		// Must include BEFORE class.template-api.php.
		$coreTemplates = array(
			'names/names.php',
			'card/card-default.php',
			'profile/profile.php',
			'anniversary-dark/anniversary-dark.php',
			'anniversary-light/anniversary-light.php',
			'birthday-dark/birthday-dark.php',
			'birthday-light/birthday-light.php',
		);

		foreach ( $coreTemplates as $path ) {

			file_exists( CN_TEMPLATE_PATH . $path ) AND include_once CN_TEMPLATE_PATH . $path;
		}

		// Theme and plugin compatibility hacks.
		require_once CN_PATH . 'includes/inc.plugin-compatibility.php';
		require_once CN_PATH . 'includes/inc.theme-compatibility.php';

		// Include the autoloader for the Pear IMC classes.
		if ( ! class_exists( 'File_IMC' ) ) include_once CN_PATH . 'vendor/pear/IMC.php';
	}

	/**
	 * SPL autoloader callback.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 *
	 * @param string $class
	 */
	public static function autoload( $class ) {

		$hashTable = self::classRegistry();

		if ( ! isset( $hashTable[ $class ] ) ) {

			return;
		}

		$file = CN_PATH . $hashTable[ $class ];

		// if the file exists, require it
		if ( file_exists( $file ) ) {

			require $file;

		} else {

			wp_die( esc_html( "The file attempting to be loaded at $file does not exist." ) );
		}

	}

	/**
	 * This callback run on the plugins_loaded hook to include the Customizer classes.
	 *
	 * Matches core WordPress @see _wp_customize_include().
	 *
	 * @access private
	 * @since  8.4
	 */
	public static function customizer() {

		$is_customize_admin_page = ( is_admin() && 'customize.php' == basename( $_SERVER['PHP_SELF'] ) );
		$should_include = (
			$is_customize_admin_page
			||
			( isset( $_REQUEST['wp_customize'] ) && 'on' == $_REQUEST['wp_customize'] )
			||
			( ! empty( $_GET['customize_changeset_uuid'] ) || ! empty( $_POST['customize_changeset_uuid'] ) )
		);

		if ( ! $should_include ) {
			return;
		}

		require_once CN_PATH . 'includes/template/class.template-customizer.php';

		// Init the Template Customizer.
		new cnTemplate_Customizer();

		/**
		 * Convenience actions that templates can hook into to load their Customizer config files.
		 *
		 * @since 8.4
		 */
		do_action( 'cn_template_customizer_include' );
	}

	/**
	 * Hash table of classes.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 *
	 * @return array
	 */
	private static function classRegistry() {

		return array(

			// Localization
			'cnText_Domain'            => 'includes/class.text-domain.php',

			// Current User
			'cnUser'                   => 'includes/class.user.php',

			// Terms Objects
			'cnTerm'                   => 'includes/class.terms.php',
			'cnTerms'                  => 'includes/class.terms.php',
			'cnTerm_Object'            => 'includes/class.terms.php',

			// Category Objects
			'cnCategory'               => 'includes/class.category.php',

			// Retrieve objects from the db.
			'cnQuery'                  => 'includes/class.query.php',
			'cnRetrieve'               => 'includes/class.retrieve.php',

			// HTML form elements.
			'cnFormObjects'            => 'includes/class.form.php',

			// Date methods.
			'cnDate'                   => 'includes/class.date.php',

			// Caching.
			'cnCache'                  => 'includes/class.cache.php',
			'cnFragment'               => 'includes/class.cache.php',

			// Metabox API.
			'cnMetaboxAPI'             => 'includes/class.metabox-api.php',
			'cnMetabox_Render'         => 'includes/class.metabox-api.php',
			'cnMetabox_Process'        => 'includes/class.metabox-api.php',

			// Register the core metaboxes and fields for the add/edit entry admin pages.
			'cnEntryMetabox'           => 'includes/class.metabox-entry.php',

			// Entry data model.
			'cnEntry'                  => 'includes/entry/class.entry-data.php',

			// Entry HTML template blocks.
			'cnOutput'                 => 'includes/entry/class.entry-output.php',
			'cnEntry_HTML'             => 'includes/entry/class.entry-html.php',
			'cnEntry_Shortcode'        => 'includes/entry/class.entry-shortcode.php',
			'cnEntry_Object_Collection' => 'includes/entry/class.entry-object-collection.php',
			'cnEntry_Collection_Item'   => 'includes/entry/class.entry-collection-item.php',

			// Entry vCard.
			'cnEntry_vCard'            => 'includes/entry/class.entry-vcard.php',

			// Entry actions.
			'cnEntry_Action'           => 'includes/entry/class.entry-actions.php',

			// HTML elements class.
			'cnHTML'                   => 'includes/class.html.php',

			// Entry Meta API.
			'cnMeta'                   => 'includes/class.meta.php',
			'cnMeta_Query'             => 'includes/class.meta.php',

			// Utility methods.
			'cnColor'                  => 'includes/class.utility.php',
			'cnFormatting'             => 'includes/class.utility.php',
			'cnFunction'               => 'includes/class.utility.php',
			'cnSiteShot'               => 'includes/class.utility.php',
			'cnString'                 => 'includes/class.utility.php',
			'cnURL'                    => 'includes/class.utility.php',
			'cnUtility'                => 'includes/class.utility.php',
			'cnValidate'               => 'includes/class.utility.php',
			'cnSanitize'               => 'includes/class.sanitize.php',

			// Geocoding.
			'cnGeo'                    => 'includes/class.geo.php',

			// Image API.
			'cnImage'                  => 'includes/image/class.image.php',

			// Shortcodes.
			'cnShortcode'              => 'includes/shortcode/class.shortcode.php',
			'cnShortcode_Connections'  => 'includes/shortcode/class.shortcode-connections.php',
			'cnThumb'                  => 'includes/shortcode/class.shortcode-thumbnail.php',
			'cnThumb_Responsive'       => 'includes/shortcode/class.shortcode-thumbnail-responsive.php',

			// Register the query vars, rewrite URL/s and canonical redirects.
			'cnRewrite'                => 'includes/class.rewrite.php',

			// Settings API.
			'cnSettingsAPI'            => 'includes/settings/class.settings-api.php',
			'cnOptions'                => 'includes/settings/class.options.php',

			// Register the core settings options via the Settings API.
			'cnRegisterSettings'       => 'includes/settings/class.settings.php',

			// Load the class that manages the registration and enqueueing of CSS and JS files.
			'cnLocate'                 => 'includes/class.locate.php',
			'cnScript'                 => 'includes/class.scripts.php',

			// Email API.
			'cnEmail'                  => 'includes/email/class.email.php',

			// Logging API/s.
			'cnLog'                    => 'includes/log/class.log.php',
			'cnLog_Stateless'          => 'includes/log/class.log-stateless.php',

			// Log email sent through the Email API.
			'cnLog_Email'              => 'includes/log/class.log-email.php',

			// Class for handling email template registration and management.
			'cnEmail_Template'         => 'includes/email/class.email-template-api.php',

			// Class for registering the core email templates.
			'cnEmail_DefaultTemplates' => 'includes/email/class.default-template.php',

			// The class for working with the file system.
			'cnFileSystem'             => 'includes/class.filesystem.php',
			'cnUpload'                 => 'includes/class.filesystem.php',

			// The class for handling admin notices.
			'cnMessage'                => 'includes/admin/class.message.php',

			// Class used for managing role capabilities.
			'cnRole'                   => 'includes/admin/class.capabilities.php',

			// The class for adding admin menu and registering the menu callbacks.
			'cnAdminMenu'              => 'includes/admin/class.menu.php',

			// The class for registering the core metaboxes for the dashboard admin page.
			'cnDashboardMetabox'       => 'includes/admin/class.metabox-dashboard.php',

			// The class for processing admin actions.
			'cnAdminActions'           => 'includes/admin/class.actions.php',

			// The class for registering general admin actions.
			'cnAdminFunction'          => 'includes/admin/class.functions.php',

			// The class for managing license keys and settings.
			'cnLicense'                => 'includes/admin/class.license.php',

			// The Term Meta UI class.
			'cnTerm_Meta_UI'           => 'includes/admin/class.term-meta-ui.php',

			// Class for SEO
			'cnSEO'                    => 'includes/class.seo.php',

			// Custom Customizer Controls
			'cnCustomizer_Control_Checkbox_Group' => 'includes/customizer/controls/checkbox-group/class.checkbox-group.php',
			'cnCustomizer_Control_Slider'         => 'includes/customizer/controls/slider/class.slider.php',

			// Template API/s.
			'cnTemplateFactory'        => 'includes/template/class.template-api.php',
			'cnTemplatePart'           => 'includes/template/class.template-parts.php',
			'cnTemplate_Shortcode'     => 'includes/template/class.template-shortcode.php',
			'cnTemplate_Compatibility' => 'includes/template/class.template-compatibility.php',
			'cnTemplate'               => 'includes/template/class.template.php',

			// System Info
			'cnSystem_Info'            => 'includes/system-info/class.system-info.php',

			// REST API.
			'cnAPI'                    => 'includes/api/class.api.php',

			// Collections
			'cnToArray'                => 'includes/class.to-array.php',
			'cnArray'                  => 'includes/class.array.php',
			'cnCollection'             => 'includes/class.collection.php',

			// Address objects.
			'cnEntry_Addresses'        => 'includes/entry/address/class.entry-addresses.php',
			'cnAddress'                => 'includes/entry/address/class.address.php',
			'cnCountry'                => 'includes/entry/address/class.country.php',
			'cnCoordinates'            => 'includes/entry/address/class.coordinates.php',

			// Phone objects
			'cnEntry_Phone_Numbers'    => 'includes/entry/phone/class.entry-phone-numbers.php',
			'cnPhone'                  => 'includes/entry/phone/class.phone.php',

			// Email Address objects
			'cnEntry_Email_Addresses'  => 'includes/entry/email/class.entry-email-addresses.php',
			'cnEmail_Address'          => 'includes/entry/email/class.email.php',

			// Messenger ID objects
			'cnEntry_Messenger_IDs'    => 'includes/entry/messenger/class.entry-messenger-ids.php',
			'cnMessenger'              => 'includes/entry/messenger/class.messenger.php',

			// Link objects
			'cnEntry_Links'            => 'includes/entry/link/class.entry-links.php',
			'cnLink'                   => 'includes/entry/link/class.link.php',

			// Entry image object.
			'cnEntry_Image'            => 'includes/entry/image/class.entry-image.php',

			// Database Classes
			'cnEntry_DB'               => 'includes/entry/class.entry-db.php',

			// HTTP request utility methods.
			'cnHTTP'                   => 'includes/class.http.php',

			// Timezone.
			'cnGoogleMapsTimeZone'     => 'includes/class.google-maps-timezone-api.php',
			'cnTimezone'               => 'includes/class.timezone.php',

			// Countries
			'cnCountries'              => 'includes/geo/class.countries.php',

			// Third Party Libraries
			//'Rinvex\Country\Country'                => 'vendor/rinvex/country/Country.php',
			//'Rinvex\Country\CountryLoader'          => 'vendor/rinvex/country/CountryLoader.php',
			//'Rinvex\Country\CountryLoaderException' => 'vendor/rinvex/country/CountryLoaderException.php',
		);
	}
}
