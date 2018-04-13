<?php

/**
 * Class to manage options using the Options API.
 *
 * @todo This really needs some cleaning up.
 *
 * @package     Connections
 * @subpackage  Manage the plugins options.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get and Set the plugin options
 */
class cnOptions {

	/**
	 * Array of options returned from WP get_option method.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * String: plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * String: plugin db version.
	 *
	 * @var string
	 */
	private $dbVersion;

	/**
	 * @var bool
	 */
	private $defaultTemplatesSet;

	/**
	 * @var array
	 */
	private $activeTemplates;

	/**
	 * @var bool
	 */
	private $defaultRolesSet;

	/**
	 * Current time as reported by PHP in Unix timestamp format.
	 *
	 * @var string
	 */
	public $currentTime;

	/**
	 * Current time as reported by WordPress in Unix timestamp format.
	 *
	 * @var integer
	 */
	public $wpCurrentTime;

	/**
	 * Current time as reported by MySQL in Unix timestamp format.
	 *
	 * @var integer
	 */
	public $sqlCurrentTime;

	/**
	 * The time offset difference between the PHP time and the MySQL time in Unix timestamp format.
	 *
	 * @var integer
	 */
	public $sqlTimeOffset;

	/**
	 * Sets up the plugin option properties. Requires the current WP user ID.
	 */
	public function __construct() {

		/** @var $wpdb wpdb */
		global $wpdb;

		$this->options = get_option( 'connections_options' );

		$this->version = ( isset( $this->options['version'] ) && ! empty( $this->options['version'] ) ) ? $this->options['version'] : CN_CURRENT_VERSION;
		$this->dbVersion = ( isset( $this->options['db_version'] ) && ! empty( $this->options['db_version'] ) ) ? $this->options['db_version'] : CN_DB_VERSION;

		$this->defaultTemplatesSet = $this->options['settings']['template']['defaults_set'];
		$this->activeTemplates = (array) $this->options['settings']['template']['active'];

		$this->defaultRolesSet = isset( $this->options['settings']['roles']['defaults_set'] ) && ! empty( $this->options['settings']['roles']['defaults_set'] ) ? $this->options['settings']['roles']['defaults_set'] : FALSE;

		$this->wpCurrentTime = current_time( 'timestamp' );
		$this->currentTime   = date( 'U' );

		/*
		 * Because MySQL FROM_UNIXTIME returns timestamps adjusted to the local
		 * timezone it is handy to have the offset so it can be compensated for.
		 * One example is when using FROM_UNIXTIME, the timestamp returned will
		 * not be the actual stored timestamp, it will be the timestamp adjusted
		 * to the timezone set in MySQL.
		 */
		$mySQLTimeStamp = $wpdb->get_results( 'SELECT NOW() as timestamp' );
		$this->sqlCurrentTime = strtotime( $mySQLTimeStamp[0]->timestamp );
		$this->sqlTimeOffset = time() - $this->sqlCurrentTime;

		/*
		 * Add `default_option_{option}` hooks to make getting a few core settings quick and easy.
		 */
		add_filter( 'default_option_cn_default_category', array( __CLASS__, 'getDefaultCategoryID' ) );
	}

	/**
	 * Saves the plug-in options to the database.
	 */
	public function saveOptions() {
		$this->options['version'] = $this->version;
		$this->options['db_version'] = $this->dbVersion;

		$this->options['settings']['template']['defaults_set'] = $this->defaultTemplatesSet;
		$this->options['settings']['template']['active'] = $this->activeTemplates;

		$this->options['settings']['roles']['defaults_set'] = $this->defaultRolesSet;

		update_option( 'connections_options', $this->options );
	}

	public function removeOptions() {
		delete_option( 'connections_options' );
	}

	/**
	 *
	 *
	 * @TODO This can likely be removed.
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param $options
	 *
	 * @TODO This can likely be removed.
	 */
	public function setOptions( $options ) {
		$this->options = $options;
	}

	/**
	 * Require the user to be logged in to view the directory.
	 *
	 * @since 0.7.3
	 * @return bool
	 */
	public function getAllowPublic() {
		global $connections;

		$required = $connections->settings->get( 'connections', 'connections_login', 'required' ) ? FALSE : TRUE;

		return $required;
	}

	/**
	 * Whether or not login is required to view the directory or not.
	 *
	 * @access public
	 * @since  8.2.5
	 * @static
	 *
	 * @uses   cnSettingsAPI::get()
	 *
	 * @return bool
	 */
	public static function loginRequired() {

		return cnSettingsAPI::get( 'connections', 'login', 'required' ) ? TRUE : FALSE;
	}

	/**
	 * Disable the shortcode option - public_override.
	 *
	 * @deprecated since 0.7.3
	 *
	 * @return bool
	 */
	public function getAllowPublicOverride() {

		/** @var connectionsLoad $connections */
		global $connections;

		return $connections->settings->get( 'connections', 'connections_visibility', 'allow_public_override' ) ? TRUE : FALSE;
	}

	/**
	 * Disable the shortcode option - private_override.
	 *
	 * @deprecated since 0.7.3
	 *
	 * @return bool
	 */
	public function getAllowPrivateOverride() {

		/** @var connectionsLoad $connections */
		global $connections;

		return $connections->settings->get( 'connections', 'connections_visibility', 'allow_private_override' ) ? TRUE : FALSE;
	}

	/**
	 * @return array
	 */
	public static function getEntryTypes() {

		return array(
			'individual'   => __( 'Individual', 'connections' ),
			'organization' => __( 'Organization', 'connections' ),
			'family'       => __( 'Family', 'connections' ),
		);
	}

	/**
	 * @return array
	 */
	public function getVisibilityOptions() {

		$options = array(
			'public'   => __( 'Public', 'connections' ),
			'private'  => __( 'Private', 'connections' ),
			'unlisted' => __( 'Unlisted', 'connections' )
			);

		foreach ( $options as $key => $option ) {

			if ( ! cnValidate::userPermitted( $key ) ) {

				unset( $options[ $key ] );
			}
		}

		return $options;
	}

	/**
	 * Returns $version.
	 *
	 * @see options::$version
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Sets $version.
	 *
	 * @param string $version
	 */
	public function setVersion( $version ) {
		$this->version = $version;
		$this->saveOptions();
	}

	/**
	 * Returns $dbVersion.
	 *
	 * @see options::$dbVersion
	 */
	public function getDBVersion() {
		return $this->dbVersion;
	}

	/**
	 * Sets $dbVersion.
	 *
	 * @param string $version
	 */
	public function setDBVersion( $version ) {
		$this->dbVersion = $version;
		$this->saveOptions();
	}

	/**
	 * Returns $defaultTemplatesSet.
	 *
	 * @see cnOptions::$defaultTemplatesSet
	 */
	public function getDefaultTemplatesSet() {
		return $this->defaultTemplatesSet;
	}

	/**
	 * Sets $defaultTemplatesSet.
	 *
	 * @param bool $defaultTemplatesSet
	 * @see cnOptions::$defaultTemplatesSet
	 */
	public function setDefaultTemplatesSet( $defaultTemplatesSet ) {
		$this->defaultTemplatesSet = $defaultTemplatesSet;
	}

	/**
	 * @return bool
	 */
	public function getCapabilitiesSet() {
		return $this->defaultRolesSet;
	}

	/**
	 * @param $defaultRolesSet
	 */
	public function defaultCapabilitiesSet( $defaultRolesSet ) {
		$this->defaultRolesSet = $defaultRolesSet;
	}

	/**
	 * Returns all active templates by type.
	 *
	 * @return array
	 */
	public function getAllActiveTemplates( ) {
		return $this->activeTemplates;
	}

	/**
	 * Returns the active templates by type.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function getActiveTemplate( $type ) {
		return empty( $this->activeTemplates[ $type ]['slug'] ) ? '' : $this->activeTemplates[ $type ]['slug'];
	}

	/**
	 * Sets $activeTemplate by type.
	 *
	 * @param string $type
	 * @param string $slug
	 */
	public function setActiveTemplate( $type, $slug ) {
		$this->activeTemplates[ $type ] = array( 'slug' => $slug );
	}

	public function setDefaultTemplates() {

		$this->setActiveTemplate( 'all', 'card' );
		$this->setActiveTemplate( 'individual', 'card' );
		$this->setActiveTemplate( 'organization', 'card' );
		$this->setActiveTemplate( 'family', 'card' );
		$this->setActiveTemplate( 'anniversary', 'anniversary-light' );
		$this->setActiveTemplate( 'birthday', 'birthday-light' );

		$this->defaultTemplatesSet = TRUE;
	}

	/**
	 * Returns an array of the default family relation types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultFamilyRelationValues() {

		$options = array(
			'aunt'             => __( 'Aunt', 'connections' ),
			'brother'          => __( 'Brother', 'connections' ),
			'brotherinlaw'     => __( 'Brother-in-law', 'connections' ),
			'cousin'           => __( 'Cousin', 'connections' ),
			'daughter'         => __( 'Daughter', 'connections' ),
			'daughterinlaw'    => __( 'Daughter-in-law', 'connections' ),
			'father'           => __( 'Father', 'connections' ),
			'fatherinlaw'      => __( 'Father-in-law', 'connections' ),
			'friend'           => __( 'Friend', 'connections' ),
			'granddaughter'    => __( 'Grand Daughter', 'connections' ),
			'grandfather'      => __( 'Grand Father', 'connections' ),
			'grandmother'      => __( 'Grand Mother', 'connections' ),
			'grandson'         => __( 'Grand Son', 'connections' ),
			'greatgrandmother' => __( 'Great Grand Mother', 'connections' ),
			'greatgrandfather' => __( 'Great Grand Father', 'connections' ),
			'husband'          => __( 'Husband', 'connections' ),
			'mother'           => __( 'Mother', 'connections' ),
			'motherinlaw'      => __( 'Mother-in-law', 'connections' ),
			'nephew'           => __( 'Nephew', 'connections' ),
			'niece'            => __( 'Niece', 'connections' ),
			'partner'          => __( 'Partner', 'connections' ),
			'significant_other'=> __( 'Significant Other', 'connections' ),
			'sister'           => __( 'Sister', 'connections' ),
			'sisterinlaw'      => __( 'Sister-in-law', 'connections' ),
			'spouse'           => __( 'Spouse', 'connections' ),
			'son'              => __( 'Son', 'connections' ),
			'soninlaw'         => __( 'Son-in-law', 'connections' ),
			'stepbrother'      => __( 'Step Brother', 'connections' ),
			'stepdaughter'     => __( 'Step Daughter', 'connections' ),
			'stepfather'       => __( 'Step Father', 'connections' ),
			'stepmother'       => __( 'Step Mother', 'connections' ),
			'stepsister'       => __( 'Step Sister', 'connections' ),
			'stepson'          => __( 'Step Son', 'connections' ),
			'uncle'            => __( 'Uncle', 'connections' ),
			'wife'             => __( 'Wife', 'connections' )
		);

		return apply_filters( 'cn_family_relation_options', $options );
	}

	/**
	 * Returns the family relation name based on the supplied key.
	 *
	 * @access private
	 * @since  unknown
	 * @param  $value string
	 * @return string
	 */
	public function getFamilyRelation( $value ) {
		$relations = $this->getDefaultFamilyRelationValues();

		return $relations[$value];
	}

	/**
	 * Returns an associative array all core address types
	 * including those registered via the `cn_address_options` filter.
	 *
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @return array
	 */
	public static function getCoreAddressTypes() {

		$types = array(
			'home'   => __( 'Home' , 'connections' ),
			'work'   => __( 'Work' , 'connections' ),
			'school' => __( 'School' , 'connections' ),
			'other'  => __( 'Other' , 'connections' )
		);

		// Return all registered types, including the "core" types.
		return array_merge( apply_filters( 'cn_address_options', $types ), $types );
	}

	/**
	 * Return an associative array of "ACTIVE" address types as set on the Settings admin page.
	 * Including those registered via the `cn_address_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @return array
	 */
	public static function getAddressTypeOptions() {

		$options = get_option( 'connections_fieldset-address' );

		if ( FALSE === $options ) {

			$options = self::getCoreAddressTypes();

		} else {

			$registered = self::getCoreAddressTypes();

			$type    = cnArray::get( $options, 'address-types.type', $registered );
			$active  = cnArray::get( $options, 'address-types.active', array_flip( $registered ) );
			$order   = cnArray::get( $options, 'address-types.order', array() );

			// Add active address types registered via the `cn_address_options` filter.
			// Use array_filter to remove "false" values that could be potentially be passed by the `cn_address_options` filter.
			$active  = array_merge( $active, array_flip( array_filter( apply_filters( 'cn_address_options', $active ) ) ) );

			// Remove address types from the order if they do not exist in the registered address types to account for removed address types.
			$order   = array_flip( array_intersect_key( array_flip( $order ), array_merge( $registered, $type ) ) );

			// Reorder the saved types to the user defined order.
			$type    = array_replace( array_flip( $order ), $registered, $type );

			// Remove inactive types.
			$options = array_intersect_key( $type, array_flip( $active ) );

			foreach ( $options as &$option ) {

				$option = __( $option, 'connections' );
			}
		}

		return $options;
	}

	/**
	 * Returns an associative array all registered address types.
	 * Including those registered via the `cn_address_options` filter and added via the Settings admin page.
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated 8.7 Use cnOptions::getAddressTypeOptions()
	 * @see cnOptions::getAddressTypeOptions()
	 *
	 * @return array
	 */
	public function getDefaultAddressValues() {

		return self::getAddressTypeOptions();
	}

	/**
	 * Returns an associative array all registered address types.
	 * Including those registered via the `cn_address_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @return array
	 */
	public static function getRegisteredAddressTypes() {

		$options = get_option( 'connections_fieldset-address' );

		$core = self::getCoreAddressTypes();
		$type = cnArray::get( $options, 'address-types.type', $core );

		return array_replace( $core, $type );
	}

	/**
	 * Get the default address type.
	 *
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @return array
	 */
	public static function getDefaultAddressType() {

		$types = self::getAddressTypeOptions();

		$value = reset( $types );
		$key   = key( $types );

		return array( $key => $value );
	}

	/**
	 * Return the address types that have been associated to entries.
	 *
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @return array
	 */
	public static function getAddressTypesInUse() {
		global $wpdb;

		$types = $wpdb->get_col( 'SELECT `type` FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE `type` <> "" GROUP BY `type`' );

		return array_intersect_key( self::getRegisteredAddressTypes(), array_flip( $types ) );
	}

	/**
	 * Returns an associative array all core phone types
	 * including those registered via the `cn_phone_options` filter.
	 *
	 * @access public
	 * @since  8.8
	 * @static
	 *
	 * @return array
	 */
	public static function getCorePhoneTypes() {

		$types = array(
			'homephone' => __( 'Home Phone' , 'connections' ),
			'homefax'   => __( 'Home Fax' , 'connections' ),
			'cellphone' => __( 'Cell Phone' , 'connections' ),
			'workphone' => __( 'Work Phone' , 'connections' ),
			'workfax'   => __( 'Work Fax' , 'connections' )
		);

		// Return all registered types, including the "core" types.
		return array_merge( apply_filters( 'cn_phone_options', $types ), $types );
	}

	/**
	 * Return an associative array of "ACTIVE" phone types as set on the Settings admin page.
	 * Including those registered via the `cn_phone_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.8
	 * @static
	 *
	 * @return array
	 */
	public static function getPhoneTypeOptions() {

		$options = get_option( 'connections_fieldset-phone' );

		if ( FALSE === $options ) {

			$options = self::getCorePhoneTypes();

		} else {

			$registered = self::getCorePhoneTypes();

			$type    = cnArray::get( $options, 'phone-types.type', $registered );
			$active  = cnArray::get( $options, 'phone-types.active', array_flip( $registered ) );
			$order   = cnArray::get( $options, 'phone-types.order', array() );

			// Add active phone type registered via the `cn_phone_options` filter.
			// Use array_filter to remove "false" values that could be potentially be passed by the `cn_phone_options` filter.
			$active  = array_merge( $active, array_flip( array_filter( apply_filters( 'cn_phone_options', $active ) ) ) );

			// Remove phone types from the order if they do not exist in the registered phone types to account for removed phone types.
			$order   = array_flip( array_intersect_key( array_flip( $order ), array_merge( $registered, $type ) ) );

			// Reorder the saved types to the user defined order.
			$type    = array_replace( array_flip( $order ), $registered, $type );

			// Remove inactive types.
			$options = array_intersect_key( $type, array_flip( $active ) );

			foreach ( $options as &$option ) {

				$option = __( $option, 'connections' );
			}
		}

		return $options;
	}

	/**
	 * Returns an array of the default phone types.
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated 8.8 Use cnOptions::getPhoneTypeOptions()
	 * @see cnOptions::getPhoneTypeOptions()
	 *
	 * @return array
	 */
	public function getDefaultPhoneNumberValues() {

		return self::getPhoneTypeOptions();
	}

	/**
	 * Returns an associative array all registered phone types.
	 * Including those registered via the `cn_phone_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.8
	 * @static
	 *
	 * @return array
	 */
	public static function getRegisteredPhoneTypes() {

		$options = get_option( 'connections_fieldset-phone' );

		$core = self::getCorePhoneTypes();
		$type = cnArray::get( $options, 'phone-types.type', $core );

		return array_replace( $core, $type );
	}

	/**
	 * Get the default phone type.
	 *
	 * @access public
	 * @since  8.8
	 * @static
	 *
	 * @return array
	 */
	public static function getDefaultPhoneType() {

		$types = self::getPhoneTypeOptions();

		$value = reset( $types );
		$key   = key( $types );

		return array( $key => $value );
	}

	/**
	 * Return the phone types that have been associated to entries.
	 *
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @return array
	 */
	public static function getPhoneTypesInUse() {
		global $wpdb;

		$types = $wpdb->get_col( 'SELECT `type` FROM ' . CN_ENTRY_PHONE_TABLE . ' WHERE `type` <> "" GROUP BY `type`' );

		return array_intersect_key( self::getRegisteredPhoneTypes(), array_flip( $types ) );
	}

	/**
	 * Returns an associative array all core email types
	 * including those registered via the `cn_email_options` filter.
	 *
	 * @access public
	 * @since  8.9
	 * @static
	 *
	 * @return array
	 */
	public static function getCoreEmailTypes() {

		$types = array(
			'personal' => __( 'Personal Email' , 'connections' ),
			'work'     => __( 'Work Email' , 'connections' )
		);

		// Return all registered types, including the "core" types.
		return array_merge( apply_filters( 'cn_email_options', $types ), $types );
	}

	/**
	 * Return an associative array of "ACTIVE" email types as set on the Settings admin page.
	 * Including those registered via the `cn_email_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.9
	 * @static
	 *
	 * @return array
	 */
	public static function getEmailTypeOptions() {

		$options = get_option( 'connections_fieldset-email' );

		if ( FALSE === $options ) {

			$options = self::getCoreEmailTypes();

		} else {

			$registered = self::getCoreEmailTypes();

			$type    = cnArray::get( $options, 'email-types.type', $registered );
			$active  = cnArray::get( $options, 'email-types.active', array_flip( $registered ) );
			$order   = cnArray::get( $options, 'email-types.order', array() );

			// Add active email type registered via the `cn_email_options` filter.
			// Use array_filter to remove "false" values that could be potentially be passed by the `cn_email_options` filter.
			$active  = array_merge( $active, array_flip( array_filter( apply_filters( 'cn_email_options', $active ) ) ) );

			// Remove email types from the order if they do not exist in the registered email types to account for removed email types.
			$order   = array_flip( array_intersect_key( array_flip( $order ), array_merge( $registered, $type ) ) );

			// Reorder the saved types to the user defined order.
			$type    = array_replace( array_flip( $order ), $registered, $type );

			// Remove inactive types.
			$options = array_intersect_key( $type, array_flip( $active ) );

			foreach ( $options as &$option ) {

				$option = __( $option, 'connections' );
			}
		}

		return $options;
	}

	/**
	 * Returns an array of the default email types.
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated 8.9 Use cnOptions::getEmailTypeOptions()
	 * @see cnOptions::getEmailTypeOptions()
	 *
	 * @return array
	 */
	public function getDefaultEmailValues() {

		return self::getEmailTypeOptions();
	}

	/**
	 * Returns an associative array all registered email types.
	 * Including those registered via the `cn_email_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.9
	 * @static
	 *
	 * @return array
	 */
	public static function getRegisteredEmailTypes() {

		$options = get_option( 'connections_fieldset-email' );

		$core = self::getCoreEmailTypes();
		$type = cnArray::get( $options, 'email-types.type', $core );

		return array_replace( $core, $type );
	}

	/**
	 * Get the default email type.
	 *
	 * @access public
	 * @since  8.9
	 * @static
	 *
	 * @return array
	 */
	public static function getDefaultEmailType() {

		$types = self::getEmailTypeOptions();

		$value = reset( $types );
		$key   = key( $types );

		return array( $key => $value );
	}

	/**
	 * Return the email types that have been associated to entries.
	 *
	 * @access public
	 * @since  8.9
	 * @static
	 *
	 * @return array
	 */
	public static function getEmailTypesInUse() {
		global $wpdb;

		$types = $wpdb->get_col( 'SELECT `type` FROM ' . CN_ENTRY_EMAIL_TABLE . ' WHERE `type` <> "" GROUP BY `type`' );

		return array_intersect_key( self::getRegisteredEmailTypes(), array_flip( $types ) );
	}

	/**
	 * Returns an array of the default social media types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultSocialMediaValues() {

		$options = array(
			'angieslist'    => 'Angie\'s List',
			'delicious'     => 'delicious',
			'cdbaby'        => 'CD Baby',
			'facebook'      => 'Facebook',
			'flickr'        => 'Flickr',
			'foursquare'    => 'foursquare',
			'goodreads'     => 'Goodreads',
			'googleplus'    => 'Google+',
			'houzz'         => 'Houzz',
			'imdb'          => 'IMDb',
			'instagram'     => 'Instagram',
			'itunes'        => 'iTunes',
			'linked-in'     => 'LinkedIn',
			'mixcloud'      => 'mixcloud',
			'myspace'       => 'MySpace',
			'odnoklassniki' => 'Odnoklassniki',
			'pinterest'     => 'Pinterest',
			'podcast'       => 'Podcast',
			'reverbnation'  => 'ReverbNation',
			'rss'           => 'RSS',
			'smugmug'       => 'Smugmug',
			'soundcloud'    => 'SoundCloud',
			'stitcher'      => 'Stitcher',
			'technorati'    => 'Technorati',
			'tripadvisor'   => 'TripAdvisor',
			'tumblr'        => 'Tumblr',
			'twitter'       => 'Twitter',
			'vimeo'         => 'vimeo',
			'vk'            => 'VK',
			'yelp'          => 'Yelp',
			'youtube'       => 'YouTube'
		);

		return apply_filters( 'cn_social_network_options', $options );
	}

	/**
	 * Returns an array of the default IM types.
	 *
	 * @access public
	 * @since  8.16
	 * @static
	 *
	 * @return array
	 */
	public static function getCoreMessengerTypes() {

		$options = array(
			'aim'       => 'AIM',
			'yahoo'     => 'Yahoo IM',
			'jabber'    => 'Jabber / Google Talk',
			'messenger' => 'Messenger',
			'skype'     => 'Skype',
			'icq'       => 'ICQ'
		);

		// Return all registered types, including the "core" types.
		return apply_filters( 'cn_instant_messenger_options', $options );
	}

	/**
	 * Return an associative array of "ACTIVE" IM types as set on the Settings admin page.
	 * Including those registered via the `cn_instant_messenger_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.16
	 * @static
	 *
	 * @return array
	 */
	public static function getMessengerTypeOptions() {

		$options = get_option( 'connections_fieldset-messenger' );

		if ( FALSE === $options ) {

			$options = self::getCoreMessengerTypes();

		} else {

			$registered = self::getCoreMessengerTypes();

			$type    = cnArray::get( $options, 'messenger-types.type', $registered );
			$active  = cnArray::get( $options, 'messenger-types.active', array_flip( $registered ) );
			$order   = cnArray::get( $options, 'messenger-types.order', array() );

			// Add active IM types registered via the `cn_instant_messenger_options` filter.
			// Use array_filter to remove "false" values that could be potentially be passed by the `cn_instant_messenger_options` filter.
			$active  = array_merge( $active, array_flip( array_filter( apply_filters( 'cn_instant_messenger_options', $active ) ) ) );

			// Remove IM types from the order if they do not exist in the registered IM types to account for removed IM types.
			$order   = array_flip( array_intersect_key( array_flip( $order ), array_merge( $registered, $type ) ) );

			// Reorder the saved types to the user defined order.
			$type    = array_replace( array_flip( $order ), $registered, $type );

			// Remove inactive types.
			$options = array_intersect_key( $type, array_flip( $active ) );

			foreach ( $options as &$option ) {

				$option = __( $option, 'connections' );
			}
		}

		return $options;
	}

	/**
	 * Returns an array of the default IM types.
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated 8.16 Use cnOptions::getIMTypeOptions()
	 * @see cnOptions::getIMTypeOptions()
	 *
	 * @return array
	 */
	public function getDefaultIMValues() {

		return self::getMessengerTypeOptions();
	}

	/**
	 * Returns an associative array all registered IM types.
	 * Including those registered via the `cn_instant_messenger_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.16
	 * @static
	 *
	 * @return array
	 */
	public static function getRegisteredMessengerTypes() {

		$options = get_option( 'connections_fieldset-messenger' );

		$core = self::getCoreMessengerTypes();
		$type = cnArray::get( $options, 'im-types.type', $core );

		return array_replace( $core, $type );
	}

	/**
	 * Get the default IM type.
	 *
	 * @access public
	 * @since  8.16
	 * @static
	 *
	 * @return array
	 */
	public static function getDefaultMessengerType() {

		$types = self::getMessengerTypeOptions();

		$value = reset( $types );
		$key   = key( $types );

		return array( $key => $value );
	}

	/**
	 * Return the IM types that have been associated to entries.
	 *
	 * @access public
	 * @since  8.16
	 * @static
	 *
	 * @return array
	 */
	public static function getMessengerTypesInUse() {
		global $wpdb;

		$types = $wpdb->get_col( 'SELECT `type` FROM ' . CN_ENTRY_MESSENGER_TABLE . ' WHERE `type` <> "" GROUP BY `type`' );

		return array_intersect_key( self::getRegisteredMessengerTypes(), array_flip( $types ) );
	}

	/**
	 * Returns an associative array all core address types
	 * including those registered via the `cn_address_options` filter.
	 *
	 * @access public
	 * @since  8.17
	 * @static
	 *
	 * @return array
	 */
	public static function getCoreLinkTypes() {

		$types = array(
			'website' => __( 'Website' , 'connections' ),
			'blog'    => __( 'Blog' , 'connections' )
		);

		// Return all registered types, including the "core" types.
		return array_merge( apply_filters( 'cn_link_options', $types ), $types );
	}

	/**
	 * Return an associative array of "ACTIVE" link types as set on the Settings admin page.
	 * Including those registered via the `cn_link_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.17
	 * @static
	 *
	 * @return array
	 */
	public static function getLinkTypeOptions() {

		$options = get_option( 'connections_fieldset-link' );

		if ( FALSE === $options ) {

			$options = self::getCoreLinkTypes();

		} else {

			$registered = self::getCoreLinkTypes();

			$type    = cnArray::get( $options, 'link-types.type', $registered );
			$active  = cnArray::get( $options, 'link-types.active', array_flip( $registered ) );
			$order   = cnArray::get( $options, 'link-types.order', array() );

			// Add active link types registered via the `cn_link_options` filter.
			// Use array_filter to remove "false" values that could be potentially be passed by the `cn_link_options` filter.
			$active  = array_merge( $active, array_flip( array_filter( apply_filters( 'cn_link_options', $active ) ) ) );

			// Remove link types from the order if they do not exist in the registered link types to account for removed link types.
			$order   = array_flip( array_intersect_key( array_flip( $order ), array_merge( $registered, $type ) ) );

			// Reorder the saved types to the user defined order.
			$type    = array_replace( array_flip( $order ), $registered, $type );

			// Remove inactive types.
			$options = array_intersect_key( $type, array_flip( $active ) );

			foreach ( $options as &$option ) {

				$option = __( $option, 'connections' );
			}
		}

		return $options;
	}

	/**
	 * Returns an array of the default link types.
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated 8.17 Use cnOptions::getLinkTypeOptions()
	 * @see cnOptions::getLinkTypeOptions()
	 *
	 * @return array
	 */
	public function getDefaultLinkValues() {

		return self::getLinkTypeOptions();
	}

	/**
	 * Returns an associative array all registered link types.
	 * Including those registered via the `cn_link_options` filter and added via the Settings admin page.
	 *
	 * @access public
	 * @since  8.17
	 * @static
	 *
	 * @return array
	 */
	public static function getRegisteredLinkTypes() {

		$options = get_option( 'connections_fieldset-link' );

		$core = self::getCoreLinkTypes();
		$type = cnArray::get( $options, 'link-types.type', $core );

		return array_replace( $core, $type );
	}

	/**
	 * Get the default link type.
	 *
	 * @access public
	 * @since  8.17
	 * @static
	 *
	 * @return array
	 */
	public static function getDefaultLinkType() {

		$types = self::getCoreLinkTypes();

		$value = reset( $types );
		$key   = key( $types );

		return array( $key => $value );
	}

	/**
	 * Return the link types that have been associated to entries.
	 *
	 * @access public
	 * @since  8.17
	 * @static
	 *
	 * @return array
	 */
	public static function getLinkTypesInUse() {
		global $wpdb;

		$types = $wpdb->get_col( 'SELECT `type` FROM ' . CN_ENTRY_LINK_TABLE . ' WHERE `type` <> "" GROUP BY `type`' );

		return array_intersect_key( self::getRegisteredLinkTypes(), array_flip( $types ) );
	}

	/**
	 * Returns an array of the default date types.
	 *
	 * @access private
	 * @since 0.7.3
	 * @return array
	 */
	public function getDateOptions() {

		$options = array(
			'anniversary'          => __( 'Anniversary' , 'connections' ),
			'baptism'              => __( 'Baptism' , 'connections' ),
			'birthday'             => __( 'Birthday' , 'connections' ),
			'deceased'             => __( 'Deceased' , 'connections' ),
			'certification'        => __( 'Certification' , 'connections' ),
			'employment'           => __( 'Employment' , 'connections' ),
			'membership'           => __( 'Membership' , 'connections' ),
			'graduate_high_school' => __( 'Graduate High School' , 'connections' ),
			'graduate_college'     => __( 'Graduate College' , 'connections' ),
			'ordination'           => __( 'Ordination' , 'connections' )
		);

		return apply_filters( 'cn_date_options', $options );
	}

	/**
	 * Return "1" if debug messages are enabled, otherwise, returns empty.
	 *
	 * @deprecated since 0.7.3
	 * @return mixed
	 */
	public function getDebug() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_debug', 'debug_messages' );
	}

	/**
	 * Return "1" if the Google Maps API is to be loaded, otherwise, returns empty.
	 *
	 * @deprecated since 0.7.3
	 * @return mixed
	 */
	public function getGoogleMapsAPI() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_compatibility', 'google_maps_api' );
	}

	/**
	 * Return "1" if the javascript are to be loaded in the page footer, otherwise, returns empty.
	 *
	 * @deprecated since 0.7.3
	 * @return mixed
	 */
	public function getJavaScriptFooter() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_compatibility', 'javascript_footer' );
	}

	/**
	 * Get the user's search field choices.
	 *
	 * @deprecated since 0.7.3
	 * @return array
	 */
	public function getSearchFields() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_search', 'fields' );
	}

	/**
	 * Return the registered content blocks.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @param string|null $item [optional] The content block key id to return the title.
	 * @param string|null $type
	 *
	 * @return array|string|false An associated array where the key if the content block ID and the value is the content block name.
	 *                            Or just the content block name if the id is supplied.
	 *                            False is return if no blocks are found.
	 */
	public static function getContentBlocks( $item = NULL, $type = NULL ) {

		$blocks['items']    = apply_filters( 'cn_content_blocks', array( 'meta' => __( 'Custom Fields', 'connections' ) ) );
		$blocks['required'] = apply_filters( 'cn_content_blocks_required', array() );

		if ( ! is_null( $type ) && is_string( $type ) ) {

			$blockType['items']    = apply_filters( "cn_content_blocks-{$type}", array() );
			$blockType['required'] = apply_filters( "cn_content_blocks_required-{$type}", array() );

			$blocks['items']    = array_merge( $blocks['items'], $blockType['items'] );
			$blocks['required'] = array_merge( $blocks['required'], $blockType['required'] );
		}

		if ( is_null( $item ) && is_string( $type ) ) return $blocks;

		foreach ( $blocks['items'] as $block => $name ) {

			if ( $item == $block ) return $name;
		}

		return FALSE;
	}

	/**
	 * Get the default category ID.
	 *
	 * NOTE: This is also the callback for the `default_option_{name}` filter @see get_option().
	 *
	 * NOTE: Uses the @see get_option() and @see update_option() functions instead of the @see cnSettingsAPI()
	 *       because it is used in places where the cnSettingsAPI() has not yet been fully initialized.
	 *
	 * @access public
	 * @since  8.3.3
	 * @static
	 *
	 * @uses   remove_filter()
	 * @uses   get_option()
	 * @uses   cnTerm::exists()
	 * @uses   cnTerm::getBy()
	 * @uses   cnTerm::insert()
	 * @uses   update_option()
	 * @uses   is_wp_error()
	 * @uses   add_filter()
	 *
	 * @return int
	 */
	public static function getDefaultCategoryID() {

		$id = 0;

		// Remove filter to prevent an infinite loop.
		remove_filter( 'default_option_cn_default_category', array( __CLASS__, 'getDefaultCategoryID' ) );

		// Use get_option() rather than cnSettingsAPI::get() because the class may not yet be initialized.
		$category = get_option( 'connections_category' );

		// Check to ensure the default category ID is saved in the options table before returning it.
		if ( FALSE === $category || ! isset( $category['default'] ) || empty( $category['default'] ) ) {

			// If there was no default category set, check for the "Uncategorized" category. If it exists return its
			// `id` and if it does not, then create it an return the `id`.
			if ( cnTerm::exists( 'uncategorized', 'category' ) ) {

				$category = cnTerm::getBy( 'slug', 'uncategorized', 'category', ARRAY_A );

				// Ensure nothing went wrong when checking for the "Uncategorized" category.
				// If not, save the `id` in the options table.
				if ( FALSE !== $category  ) {

					$id = $category['term_id'];

					// Use update_option() rather than cnSettingsAPI::set() because the class may not yet be initialized.
					update_option( 'connections_category', array( 'default' => $id ) );
				}

			} else {

				$category = cnTerm::insert( __( 'Uncategorized', 'connections' ), 'category' );

				// Ensure nothing went wrong when inserting the "Uncategorized" category.
				// If not, save the `id` in the options table.
				if ( ! is_wp_error( $category ) ) {

					$id = $category['term_id'];

					// Use update_option() rather than cnSettingsAPI::set() because the class may not yet be initialized.
					update_option( 'connections_category', array( 'default' => $id ) );
				}
			}

		} else {

			$id = $category['default'];
		}

		// Add the filter back.
		add_filter( 'default_option_cn_default_category', array( __CLASS__, 'getDefaultCategoryID' ) );

		/**
		 * Allows the opportunity to change the default category.
		 *
		 * @since 8.3.3
		 *
		 * @param int $id The default category ID.
		 */
		return apply_filters( 'cn_default_category', $id );
	}

	/**
	 * Get Base Country
	 *
	 * @since
	 * @return string $country The two letter country code for the base country.
	 */
	public static function getBaseCountry() {

		// Have to use get_option rather than cnSettingsAPI::get() because the class is not yet initialized.
		$baseGEO = get_option( 'connections_geo', array( 'base_country' => 'US', 'base_region' => 'WA' ) );

		return apply_filters( 'cn_base_country', $baseGEO['base_country'] );
	}

	/**
	 * Get Base State
	 *
	 * @since
	 * @return string $state The base region code.
	 */
	public static function getBaseRegion() {

		// Have to use get_option rather than cnSettingsAPI::get() because the class is not yet initialized.
		$baseGEO = get_option( 'connections_geo', array( 'base_country' => 'US', 'base_region' => 'WA' ) );

		return apply_filters( 'cn_base_region', $baseGEO['base_region'] );
	}
}
