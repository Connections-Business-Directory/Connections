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

		require_once CN_PATH . 'includes/inc.deprecated.php';
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
			'carousel/carousel.php',
			'carousel-related/carousel.php',
			'team-grid-card-clean/clean.php',
			'team-grid-card-flip/flip.php',
			'team-grid-card-slide/slide.php',
			'team-grid-card-overlay/overlay.php',
			'team-list/list.php',
			'team-table/table.php',
		);

		foreach ( $coreTemplates as $path ) {

			file_exists( CN_TEMPLATE_PATH . $path ) AND include_once CN_TEMPLATE_PATH . $path;
		}

		// Theme and plugin compatibility hacks.
		require_once CN_PATH . 'includes/inc.plugin-compatibility.php';
		require_once CN_PATH . 'includes/inc.theme-compatibility.php';

		// Include the autoloader for the Pear IMC classes.
		if ( ! class_exists( 'File_IMC' ) ) include_once CN_PATH . 'vendor/pear/IMC.php';

		// Include the Encoding class.
		if ( ! class_exists( '\ForceUTF8\Encoding' ) ) include_once CN_PATH . 'vendor/ForceUTF8/Encoding.php';
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

			// Utility
			'Connections_Directory\Utility\_array'  => 'includes/Utility/_array.php',
			//'Connections_Directory\Utility\_color'  => 'includes/Utility/_color.php',
			//'Connections_Directory\Utility\_date'   => 'includes/Utility/_date.php',
			//'Connections_Directory\Utility\_format' => 'includes/Utility/_format.php',
			//'Connections_Directory\Utility\_string' => 'includes/Utility/_string.php',

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

			// Entry helper functions.
			'Connections_Directory\Entry\Functions' => 'includes/entry/Functions.php',

			// Content Blocks API.
			'Connections_Directory\Content_Blocks' => 'includes/Content_Blocks.php',
			'Connections_Directory\Content_Block'  => 'includes/Content_Block.php',

			// Core Content Blocks.
			'Connections_Directory\Content_Blocks\Entry\Categories'        => 'includes/Content_Blocks/Entry/Categories.php',
			'Connections_Directory\Content_Blocks\Entry\Custom_Fields'     => 'includes/Content_Blocks/Entry/Custom_Fields.php',
			'Connections_Directory\Content_Blocks\Entry\Google_Static_Map' => 'includes/Content_Blocks/Entry/Google_Static_Map.php',
			'Connections_Directory\Content_Blocks\Entry\Management'        => 'includes/Content_Blocks/Entry/Management.php',
			'Connections_Directory\Content_Blocks\Entry\Map_Block'         => 'includes/Content_Blocks/Entry/Map_Block.php',
			'Connections_Directory\Content_Blocks\Entry\Meta'              => 'includes/Content_Blocks/Entry/Meta.php',
			'Connections_Directory\Content_Blocks\Entry\Nearby'               => 'includes/Content_Blocks/Entry/Nearby.php',
			'Connections_Directory\Content_Blocks\Entry\Related'              => 'includes/Content_Blocks/Entry/Related.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Category'     => 'includes/Content_Blocks/Entry/Related/Category.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Postal_Code'  => 'includes/Content_Blocks/Entry/Related/Postal_Code.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Region'       => 'includes/Content_Blocks/Entry/Related/Region.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Locality'     => 'includes/Content_Blocks/Entry/Related/Locality.php',
			'Connections_Directory\Content_Blocks\Entry\Related\County'       => 'includes/Content_Blocks/Entry/Related/County.php',
			'Connections_Directory\Content_Blocks\Entry\Related\District'     => 'includes/Content_Blocks/Entry/Related/District.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Department'   => 'includes/Content_Blocks/Entry/Related/Department.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Organization' => 'includes/Content_Blocks/Entry/Related/Organization.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Title'        => 'includes/Content_Blocks/Entry/Related/Title.php',
			'Connections_Directory\Content_Blocks\Entry\Related\Last_Name'    => 'includes/Content_Blocks/Entry/Related/Last_Name.php',

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
			'cnShortcode'                              => 'includes/shortcode/class.shortcode.php',
			'cnShortcode_Connections'                  => 'includes/shortcode/class.shortcode-connections.php',
			'Connections_Directory\Shortcode\mapBlock' => 'includes/shortcode/class.shortcode-mapblock.php',
			'Connections_Directory\Shortcode\Entry'    => 'includes/shortcode/class.shortcode-entry.php',
			'cnThumb'                                  => 'includes/shortcode/class.shortcode-thumbnail.php',
			'cnThumb_Responsive'                       => 'includes/shortcode/class.shortcode-thumbnail-responsive.php',

			// Register the query vars, rewrite URL/s and canonical redirects.
			'cnRewrite'                => 'includes/class.rewrite.php',

			// Settings API.
			'Connections_Directory\Settings'           => 'includes/settings/class.settings.php',
			'Connections_Directory\Settings\Tab'       => 'includes/settings/class.tab.php',
			'Connections_Directory\Settings\Section'   => 'includes/settings/class.section.php',
			'cnSettingsAPI'                            => 'includes/settings/class.settings-api.php',
			'cnOptions'                                => 'includes/settings/class.options.php',

			// Register the core settings options via the Settings API.
			'cnRegisterSettings'       => 'includes/settings/class.register-settings.php',

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

			// Date objects
			'cnEntry_Dates'            => 'includes/entry/date/class.entry-dates.php',
			'cnEntry_Date'             => 'includes/entry/date/class.date.php',

			// Date objects
			'cnEntry_Social_Networks'  => 'includes/entry/social/class.entry-social-networks.php',
			'cnEntry_Social_Network'   => 'includes/entry/social/class.social-network.php',

			// Entry image object.
			'cnEntry_Image'            => 'includes/entry/image/class.entry-image.php',

			// Database Classes
			'cnEntry_DB'               => 'includes/entry/class.entry-db.php',

			// HTTP request utility methods.
			'cnHTTP'                   => 'includes/class.http.php',

			// Timezone.
			'cnGoogleMapsTimeZone'     => 'includes/class.google-maps-timezone-api.php',

			// Countries
			'cnCountries'              => 'includes/geo/class.countries.php',

			// Models
			'Connections_Directory\Model\Address' => 'includes/model/class.address.php',
			'cnCountry'                           => 'includes/model/class.country.php',
			'cnCoordinates'                       => 'includes/model/class.coordinates.php',
			'Connections_Directory\Model\Bounds'  => 'includes/model/class.bounds.php',
			'cnTimezone'                          => 'includes/model/class.timezone.php',

			// Format
			'Connections_Directory\Model\Format\Address\As_String' => 'includes/model/format/address/class.as-string.php',

			// Geocoder
			'Connections_Directory\Geocoder\Geocoder'                         => 'includes/geocoder/class.geocoder.php',
			'Connections_Directory\Geocoder\Assert'                           => 'includes/geocoder/class.assert.php',
			//'Connections_Directory\Geocoder\Exception\Exception'              => 'includes/geocoder/exception/interface.exception.php',
			//'Connections_Directory\Geocoder\Exception\Invalid_Argument'       => 'includes/geocoder/exception/class.invalid-argument.php',
			//'Connections_Directory\Geocoder\Exception\Logic_Exception'        => 'includes/geocoder/exception/class.logic-exception.php',
			//'Connections_Directory\Geocoder\Model\Address'                    => 'includes/geocoder/model/class.address.php',
			'Connections_Directory\Geocoder\Model\Address_Builder'            => 'includes/geocoder/model/class.address-builder.php',
			'Connections_Directory\Geocoder\Query\Query'                      => 'includes/geocoder/query/interface.query.php',
			'Connections_Directory\Geocoder\Query\Address'                    => 'includes/geocoder/query/class.address.php',
			'Connections_Directory\Geocoder\Query\Coordinates'                => 'includes/geocoder/query/class.reverse.php',
			'Connections_Directory\Geocoder\Provider\Provider'                => 'includes/geocoder/provider/interface.provider.php',
			'Connections_Directory\Geocoder\Provider\Algolia\Algolia'         => 'includes/geocoder/provider/class.algolia.php',
			'Connections_Directory\Geocoder\Provider\Google_Maps\Google_Maps' => 'includes/geocoder/provider/class.google-maps.php',
			'Connections_Directory\Geocoder\Provider\Bing_Maps\Bing_Maps'     => 'includes/geocoder/provider/class.bing-maps.php',
			'Connections_Directory\Geocoder\Provider\Nominatim\Nominatim'     => 'includes/geocoder/provider/class.nominatim.php',

			// Map
			'Connections_Directory\Map\Map'                                   => 'includes/map/class.map.php',
			'Connections_Directory\Map\Map_Object'                            => 'includes/map/interface.map-object.php',
			//'Connections_Directory\Map\Layer'                                 => 'includes/map/class.layer.php',
			'Connections_Directory\Map\Layer\Layer'                           => 'includes/map/layer/interface.layer.php',
			'Connections_Directory\Map\Layer\Abstract_Layer'                  => 'includes/map/layer/abstract.layer.php',
			'Connections_Directory\Map\Layer\Group\Layer_Group'               => 'includes/map/layer/group/class.layer-group.php',
			'Connections_Directory\Map\Layer\Raster\Tile_Layer'               => 'includes/map/layer/raster/class.tile-layer.php',
			'Connections_Directory\Map\Layer\Raster\Provider\Nominatim'       => 'includes/map/layer/raster/provider/class.nominatim.php',
			'Connections_Directory\Map\Layer\Raster\Provider\Wikimedia'       => 'includes/map/layer/raster/provider/class.wikimedia.php',
			'Connections_Directory\Map\Layer\Raster\Provider\Google_Maps'     => 'includes/map/layer/raster/provider/class.google-maps.php',
			'Connections_Directory\Map\Control\Control'                       => 'includes/map/control/interface.control.php',
			'Connections_Directory\Map\Control\Abstract_Control'              => 'includes/map/control/abstract.control.php',
			'Connections_Directory\Map\Control\Layer\Layer_Control'           => 'includes/map/control/layer/class.layer.php',
			'Connections_Directory\Map\UI\Marker'                             => 'includes/map/ui/class.marker.php',
			'Connections_Directory\Map\UI\Popup'                              => 'includes/map/ui/class.popup.php',
			//'Connections_Directory\Map\Marker'                                => 'includes/map/class.marker.php',
			//'Connections_Directory\Map\Marker_Collection'                     => 'includes/map/class.marker-collection.php',
			'Connections_Directory\Map\Common\Options'                        => 'includes/map/common/trait.options.php',
			'Connections_Directory\Map\Common\Popup_Trait'                    => 'includes/map/common/trait.popup.php',

			// Gutenberg Blocks
			'Connections_Directory\Blocks'           => 'includes/blocks/class.blocks.php',
			'Connections_Directory\Blocks\Carousel'  => 'includes/blocks/carousel/class.block.php',
			'Connections_Directory\Blocks\Directory' => 'includes/blocks/directory/class.block.php',
			'Connections_Directory\Blocks\Team'      => 'includes/blocks/team/class.block.php',
			'Connections_Directory\Blocks\Upcoming'  => 'includes/blocks/upcoming/class.block.php',

			// Third Party Libraries
			//'Rinvex\Country\Country'                => 'vendor/rinvex/country/Country.php',
			//'Rinvex\Country\CountryLoader'          => 'vendor/rinvex/country/CountryLoader.php',
			//'Rinvex\Country\CountryLoaderException' => 'vendor/rinvex/country/CountryLoaderException.php',
		);
	}
}
