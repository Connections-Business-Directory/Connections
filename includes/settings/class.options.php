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
	 * @var float
	 */
	private $version;

	/**
	 * String: plugin db version.
	 *
	 * @var float
	 */
	private $dbVersion;

	private $defaultTemplatesSet;
	private $activeTemplates;

	/**
	 * Current time as reported by PHP in Unix timestamp format.
	 *
	 * @var integer
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
	 *
	 * @param interger $userID
	 */
	public function __construct() {
		global $wpdb;

		$this->options = get_option( 'connections_options' );

		$this->version = ( isset( $this->options['version'] ) && ! empty( $this->options['version'] ) ) ? $this->options['version'] : CN_CURRENT_VERSION;
		$this->dbVersion = ( isset( $this->options['db_version'] ) && ! empty( $this->options['db_version'] ) ) ? $this->options['db_version'] : CN_DB_VERSION;

		$this->defaultTemplatesSet = $this->options['settings']['template']['defaults_set'];
		$this->activeTemplates = (array) $this->options['settings']['template']['active'];

		$this->defaultRolesSet = isset( $this->options['settings']['roles']['defaults_set'] ) && ! empty( $this->options['settings']['roles']['defaults_set'] ) ? $this->options['settings']['roles']['defaults_set'] : FALSE;

		$this->wpCurrentTime = current_time( 'timestamp' );
		$this->currentTime = date( 'U' );

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
	 *
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
	 * Disable the shortcode option - public_override.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getAllowPublicOverride() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_visibility', 'allow_public_override' ) ? TRUE : FALSE;
	}

	/**
	 * Disable the shortcode option - private_override.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getAllowPrivateOverride() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_visibility', 'allow_private_override' ) ? TRUE : FALSE;
	}

	public function getVisibilityOptions() {

		$options = array(
			'public'   =>'Public',
			'private'  =>'Private',
			'unlisted' =>'Unlisted'
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
	 * @param object  $version
	 * @see options::$version
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
	 * @param string  $dbVersion
	 * @see options::$dbVersion
	 */
	public function setDBVersion( $version ) {
		$this->dbVersion = $version;
		$this->saveOptions();
	}

	/**
	 * Medium image quality.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgEntryQuality() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_medium', 'quality' );
	}

	/**
	 * Medium width.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgEntryX() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_medium', 'width' );
	}

	/**
	 * Medium height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgEntryY() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_medium', 'height' );
	}

	/**
	 * Medium height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgEntryCrop() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_medium', 'ratio' );
	}

	/**
	 * Medium image ratio crop.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgEntryRatioCrop() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_medium', 'ratio' );

		switch ( $imgRatio ) {
		case 'none':
			$imgRatioCrop = false;
			break;

		case 'crop':
			$imgRatioCrop = true;
			break;

		case 'fill':
			$imgRatioCrop = false;
			break;
		}

		return $imgRatioCrop;
	}

	/**
	 * Medium image ratio fill.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgEntryRatioFill() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_medium', 'ratio' );

		switch ( $imgRatio ) {
		case 'none':
			$imgRatioFill = false;
			break;

		case 'crop':
			$imgRatioFill = false;
			break;

		case 'fill':
			$imgRatioFill = true;
			break;
		}

		return $imgRatioFill;
	}

	/**
	 * Large image quality.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgProfileQuality() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_large', 'quality' );
	}

	/**
	 * Large width.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgProfileX() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_large', 'width' );
	}

	/**
	 * Large height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgProfileY() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_large', 'height' );
	}

	/**
	 * Large height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgProfileCrop() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_large', 'ratio' );
	}

	/**
	 * Large image ratio crop.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgProfileRatioCrop() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_large', 'ratio' );

		switch ( $imgRatio ) {
		case 'none':
			$imgRatioCrop = false;
			break;

		case 'crop':
			$imgRatioCrop = true;
			break;

		case 'fill':
			$imgRatioCrop = false;
			break;
		}

		return $imgRatioCrop;
	}

	/**
	 * Large image ratio fill.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgProfileRatioFill() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_large', 'ratio' );

		switch ( $imgRatio ) {
		case 'none':
			$imgRatioFill = false;
			break;

		case 'crop':
			$imgRatioFill = false;
			break;

		case 'fill':
			$imgRatioFill = true;
			break;
		}

		return $imgRatioFill;
	}

	/**
	 * Thumbnail image quality.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgThumbQuality() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_thumbnail', 'quality' );
	}

	/**
	 * Thumbnail width.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgThumbX() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_thumbnail', 'width' );
	}

	/**
	 * Thumbnail height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgThumbY() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_thumbnail', 'height' );
	}

	/**
	 * Thumbnail height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgThumbCrop() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_thumbnail', 'ratio' );
	}

	/**
	 * Thumbnail image ratio crop.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgThumbRatioCrop() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_thumbnail', 'ratio' );

		switch ( $imgRatio ) {
		case 'none':
			$imgRatioCrop = false;
			break;

		case 'crop':
			$imgRatioCrop = true;
			break;

		case 'fill':
			$imgRatioCrop = false;
			break;
		}

		return $imgRatioCrop;
	}

	/**
	 * Thumbnail image ratio fill.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgThumbRatioFill() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_thumbnail', 'ratio' );

		switch ( $imgRatio ) {
		case 'none':
			$imgRatioFill = false;
			break;

		case 'crop':
			$imgRatioFill = false;
			break;

		case 'fill':
			$imgRatioFill = true;
			break;
		}

		return $imgRatioFill;
	}

	/**
	 * Logo image quality.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgLogoQuality() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_logo', 'quality' );
	}

	/**
	 * Logo width.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgLogoX() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_logo', 'width' );
	}

	/**
	 * Logo height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgLogoY() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_logo', 'height' );
	}

	/**
	 * Medium height.
	 *
	 * @deprecated since 0.7.3
	 * @return int
	 */
	public function getImgLogoCrop() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_image_logo', 'ratio' );
	}

	/**
	 * Logo image ratio crop.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgLogoRatioCrop() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_logo', 'ratio' );

		switch ( $imgRatio ) {
			case 'none':
				$imgRatioCrop = false;
				break;

			case 'crop':
				$imgRatioCrop = true;
				break;

			case 'fill':
				$imgRatioCrop = false;
				break;
			}

		return $imgRatioCrop;
	}

	/**
	 * Logo image ratio fill.
	 *
	 * @access private
	 * @since unknown
	 * @return string
	 */
	public function getImgLogoRatioFill() {
		global $connections;

		$imgRatio = $connections->settings->get( 'connections', 'connections_image_logo', 'ratio' );

		switch ( $imgRatio ) {
			case 'none':
				$imgRatioFill = false;
				break;

			case 'crop':
				$imgRatioFill = false;
				break;

			case 'fill':
				$imgRatioFill = true;
				break;
		}

		return $imgRatioFill;
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
	 * @param object  $defaultTemplatesSet
	 * @see cnOptions::$defaultTemplatesSet
	 */
	public function setDefaultTemplatesSet( $defaultTemplatesSet ) {
		$this->defaultTemplatesSet = $defaultTemplatesSet;
	}

	public function getCapabilitiesSet() {
		return $this->defaultRolesSet;
	}

	public function defaultCapabilitiesSet( $defaultRolesSet ) {
		$this->defaultRolesSet = $defaultRolesSet;
	}

	/**
	 * Returns all active templates by type.
	 *
	 * @return (array)
	 */
	public function getAllActiveTemplates( ) {
		return $this->activeTemplates;
	}

	/**
	 * Returns the active templates by type.
	 *
	 * @param string  $type
	 * @return (array)
	 */
	public function getActiveTemplate( $type ) {
		return empty( $this->activeTemplates[ $type ]['slug'] ) ? '' : $this->activeTemplates[ $type ]['slug'];
	}

	/**
	 * Sets $activeTemplate by type.
	 *
	 * @param string  $type
	 * @param object  $activeTemplate
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
		return array(
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
	}

	/**
	 * Returns the fmaily relation name based on the supplied key.
	 *
	 * @access private
	 * @since unknown
	 * @param unknown $value string
	 * @return string
	 */
	public function getFamilyRelation( $value ) {
		$relations = $this->getDefaultFamilyRelationValues();

		return $relations[$value];
	}

	/**
	 * Returns an array of the default address types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultAddressValues() {
		$defaultAddressValues = array(
			'home'   => __( 'Home' , 'connections' ),
			'work'   => __( 'Work' , 'connections' ),
			'school' => __( 'School' , 'connections' ),
			'other'  => __( 'Other' , 'connections' )
		);

		return $defaultAddressValues;
	}

	/**
	 * Returns an array of the default phone types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultPhoneNumberValues() {
		$defaultPhoneNumberValues = array(
			'homephone' => __( 'Home Phone' , 'connections' ),
			'homefax'   => __( 'Home Fax' , 'connections' ),
			'cellphone' => __( 'Cell Phone' , 'connections' ),
			'workphone' => __( 'Work Phone' , 'connections' ),
			'workfax'   => __( 'Work Fax' , 'connections' )
		);

		return $defaultPhoneNumberValues;
	}

	/**
	 * Returns an array of the default social media types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultSocialMediaValues() {
		return array(
			'delicious'     => 'delicious',
			'cdbaby'        => 'CD Baby',
			'facebook'      => 'Facebook',
			'flickr'        => 'Flickr',
			'foursquare'    => 'foursquare',
			'googleplus'    => 'Google+',
			'itunes'        => 'iTunes',
			'linked-in'     => 'Linked-in',
			'mixcloud'      => 'mixcloud',
			'myspace'       => 'MySpace',
			'odnoklassniki' => 'Odnoklassniki',
			'pinterest'     => 'Pinterest',
			'podcast'       => 'Podcast',
			'reverbnation'  => 'ReverbNation',
			'rss'           => 'RSS',
			'soundcloud'    => 'SoundCloud',
			'technorati'    => 'Technorati',
			'tripadvisor'   => 'TripAdvisor',
			'twitter'       => 'Twitter',
			'vimeo'         => 'vimeo',
			'vk'            => 'VK',
			'yelp'          => 'Yelp',
			'youtube'       => 'YouTube'
		);
	}

	/**
	 * Returns an array of the default IM types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultIMValues() {
		return array(
			'aim'       => 'AIM',
			'yahoo'     => 'Yahoo IM',
			'jabber'    => 'Jabber / Google Talk',
			'messenger' => 'Messenger',
			'skype'     => 'Skype',
			'icq'       => 'ICQ'
		);
	}

	/**
	 * Returns an array of the default email types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultEmailValues() {
		$defaultEmailValues = array(
			'personal' => __( 'Personal Email' , 'connections' ),
			'work'     => __( 'Work Email' , 'connections' )
		);

		return $defaultEmailValues;
	}

	/**
	 * Returns an array of the default link types.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultLinkValues() {
		$defaultLinkValues = array(
			'website' => __( 'Website' , 'connections' ),
			'blog'    => __( 'Blog' , 'connections' )
		);

		return $defaultLinkValues;
	}

	/**
	 * Returns an array of the default date types.
	 *
	 * @access private
	 * @since 0.7.3
	 * @return array
	 */
	public function getDateOptions() {
		$dateOptions = array(
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

		return $dateOptions;
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
	 * Get Base Country
	 *
	 * @since
	 * @return string $country The two letter country code for the base country
	 */
	public static function getCountry() {
		global $connections;
		$base_country = $connections->settings->get( 'connections', 'connections_general', 'base_country' );
		$country = $base_country ? $base_country : 'US';
		return apply_filters( 'cn_country', $country );
	}
	
	/**
	 * Get Base State
	 *
	 * @since
	 * @return string $state The base state name
	 */
	public static function getRegion() {
		global $connections;
		$base_region = $connections->settings->get( 'connections', 'connections_general', 'base_region' );
		$region = $base_region ? $base_region : 'WA';
		return apply_filters( 'cn_state', $region );
	}
	/*
	* Look up the Country name for the code given
	* @returns array|false
	*/
	public static function getCountryByCode($code="fail"){
		$countries = cnOptions::getCountries();
		$country = isset($countries[strtoupper($code)])?$countries[strtoupper($code)]:false;
		return $country;
	}

	/*
	* @returns array of the codes only
	*/
	public static function getCountryCodes(){
		$keys = array_keys($this->getCountries());
		return $keys;
	}
	
	/**
	 * Get regions
	 *
	 * @since
	 *
	 * @param null $country
	 * @return mixed|void  A list of regions for the base country
	 */
	public static function getRegions( $country = null ) {
		global $connections;
	
		if( empty( $country ) )
			$country = cnOptions::getCountry();
	
		switch( $country ) :
	
			case 'US' :
				$regions = cnOptions::get_US_Regions();
				break;
			case 'CA' :
				$regions = cnOptions::get_provinces_list();
				break;
			case 'AU' :
				$regions = cnOptions::get_austrailian_regions_list();
				break;
			case 'BR' :
				$regions = cnOptions::get_brazil_regions_list();
				break;
			case 'CN' :
				$regions = cnOptions::get_chinese_regions_list();
				break;
			case 'HK' :
				$regions = cnOptions::get_hong_kong_regions_list();
				break;
			case 'HU' :
				$regions = cnOptions::get_hungary_regions_list();
				break;
			case 'ID' :
				$regions = cnOptions::get_indonesian_regions_list();
				break;
			case 'IN' :
				$regions = cnOptions::get_indian_regions_list();
				break;
			case 'MY' :
				$regions = cnOptions::get_malaysian_regions_list();
				break;
			case 'NZ' :
				$regions = cnOptions::get_new_zealand_regions_list();
				break;
			case 'TH' :
				$regions = cnOptions::get_thailand_regions_list();
				break;
			case 'ZA' :
				$regions = cnOptions::get_south_african_regions_list();
				break;
			default :
				$regions = array();
				break;
	
		endswitch;
	
		return apply_filters( 'cn_regions', $regions );
	}
	
	
	/**
	 * Get Country List
	 *
	 * @since 1.0
	 * @return array $countries A list of the available countries
	 */
	public static function getCountries() {
		$countries = array(
			'US' => 'United States',
			'CA' => 'Canada',
			'GB' => 'United Kingdom',
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darrussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CD' => 'Congo, Democratic People\'s Republic',
			'CG' => 'Congo, Republic of',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote d\'Ivoire',
			'HR' => 'Croatia/Hrvatska',
			'CU' => 'Cuba',
			'CY' => 'Cyprus Island',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'GQ' => 'Equatorial Guinea',
			'SV' => 'El Salvador',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GR' => 'Greece',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard and McDonald Islands',
			'VA' => 'Holy See (City Vatican State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourgh',
			'MO' => 'Macau',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'Mv' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova, Republic of',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KR' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territories',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Phillipines',
			'PN' => 'Pitcairn Island',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion Island',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovak Republic',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia',
			'KP' => 'South Korea',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TH' => 'Thailand',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'UY' => 'Uruguay',
			'UM' => 'US Minor Outlying Islands',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (USA)',
			'WF' => 'Wallis and Futuna Islands',
			'EH' => 'Western Sahara',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'YU' => 'Yugoslavia',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);
	
		return apply_filters( 'countries', $countries );
	}
	
	/**
	 * Get regions List
	 *
	 * @access      public
	 * @since       
	 * @return      array
	 */
	public static function get_US_Regions() {
		$regions = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AS' => 'American Samoa',
			'CZ' => 'Canal Zone',
			'CM' => 'Commonwealth of the Northern Mariana Islands',
			'FM' => 'Federated regions of Micronesia',
			'GU' => 'Guam',
			'MH' => 'Marshall Islands',
			'MP' => 'Northern Mariana Islands',
			'PW' => 'Palau',
			'PI' => 'Philippine Islands',
			'PR' => 'Puerto Rico',
			'TT' => 'Trust Territory of the Pacific Islands',
			'VI' => 'Virgin Islands',
			'AA' => 'Armed Forces - Americas',
			'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
			'AP' => 'Armed Forces - Pacific'
		);
	
		return apply_filters( 'us_regions', $regions );
	}
	
	/**
	 * Get Provinces List
	 *
	 * @access      public
	 * @since       1.2
	 * @return      array
	 */
	public static function get_provinces_list() {
		$provinces = array(
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland and Labrador',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon'
		);
	
		return apply_filters( 'canada_provinces', $provinces );
	}
	
	/**
	 * Get Australian regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_austrailian_regions_list() {
		$regions = array(
			'ACT' => 'Australian Capital Territory',
			'NSW' => 'New South Wales',
			'NT'  => 'Northern Territory',
			'QLD' => 'Queensland',
			'SA'  => 'South Australia',
			'TAS' => 'Tasmania',
			'VIC' => 'Victoria',
			'WA'  => 'Western Australia'
		);
	
		return apply_filters( 'australian_regions', $regions );
	}
	
	/**
	 * Get Brazil regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_brazil_regions_list() {
		$regions = array(
			'AC' => 'Acre',
			'AL' => 'Alagoas',
			'AP' => 'Amap&aacute;',
			'AM' => 'Amazonas',
			'BA' => 'Bahia',
			'CE' => 'Cear&aacute;',
			'DF' => 'Distrito Federal',
			'ES' => 'Esp&iacute;rito Santo',
			'GO' => 'Goi&aacute;s',
			'MA' => 'Maranh&atilde;o',
			'MT' => 'Mato Grosso',
			'MS' => 'Mato Grosso do Sul',
			'MG' => 'Minas Gerais',
			'PA' => 'Par&aacute;',
			'PB' => 'Para&iacute;ba',
			'PR' => 'Paran&aacute;',
			'PE' => 'Pernambuco',
			'PI' => 'Piau&iacute;',
			'RJ' => 'Rio de Janeiro',
			'RN' => 'Rio Grande do Norte',
			'RS' => 'Rio Grande do Sul',
			'RO' => 'Rond&ocirc;nia',
			'RR' => 'Roraima',
			'SC' => 'Santa Catarina',
			'SP' => 'S&atilde;o Paulo',
			'SE' => 'Sergipe',
			'TO' => 'Tocantins'
		);
	
		return apply_filters( 'brazil_regions', $regions );
	}
	
	/**
	 * Get Hong Kong regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_hong_kong_regions_list() {
		$regions = array(
			'HONG KONG'       => 'Hong Kong Island',
			'KOWLOON'         => 'Kowloon',
			'NEW TERRITORIES' => 'New Territories'
		);
	
		return apply_filters( 'hong_kong_regions', $regions );
	}
	
	/**
	 * Get Hungary regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	function get_hungary_regions_list() {
		$regions = array(
			'BK' => 'BÃ¡cs-Kiskun',
			'BE' => 'BÃ©kÃ©s',
			'BA' => 'Baranya',
			'BZ' => 'Borsod-AbaÃºj-ZemplÃ©n',
			'BU' => 'Budapest',
			'CS' => 'CsongrÃ¡d',
			'FE' => 'FejÃ©r',
			'GS' => 'GyÅ‘r-Moson-Sopron',
			'HB' => 'HajdÃº-Bihar',
			'HE' => 'Heves',
			'JN' => 'JÃ¡sz-Nagykun-Szolnok',
			'KE' => 'KomÃ¡rom-Esztergom',
			'NO' => 'NÃ³grÃ¡d',
			'PE' => 'Pest',
			'SO' => 'Somogy',
			'SZ' => 'Szabolcs-SzatmÃ¡r-Bereg',
			'TO' => 'Tolna',
			'VA' => 'Vas',
			'VE' => 'VeszprÃ©m',
			'ZA' => 'Zala'
		);
	
		return apply_filters( 'hungary_regions', $regions );
	}
	
	/**
	 * Get Chinese regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_chinese_regions_list() {
		$regions = array(
			'CN1'  => 'Yunnan / &#20113;&#21335;',
			'CN2'  => 'Beijing / &#21271;&#20140;',
			'CN3'  => 'Tianjin / &#22825;&#27941;',
			'CN4'  => 'Hebei / &#27827;&#21271;',
			'CN5'  => 'Shanxi / &#23665;&#35199;',
			'CN6'  => 'Inner Mongolia / &#20839;&#33945;&#21476;',
			'CN7'  => 'Liaoning / &#36797;&#23425;',
			'CN8'  => 'Jilin / &#21513;&#26519;',
			'CN9'  => 'Heilongjiang / &#40657;&#40857;&#27743;',
			'CN10' => 'Shanghai / &#19978;&#28023;',
			'CN11' => 'Jiangsu / &#27743;&#33487;',
			'CN12' => 'Zhejiang / &#27993;&#27743;',
			'CN13' => 'Anhui / &#23433;&#24509;',
			'CN14' => 'Fujian / &#31119;&#24314;',
			'CN15' => 'Jiangxi / &#27743;&#35199;',
			'CN16' => 'Shandong / &#23665;&#19996;',
			'CN17' => 'Henan / &#27827;&#21335;',
			'CN18' => 'Hubei / &#28246;&#21271;',
			'CN19' => 'Hunan / &#28246;&#21335;',
			'CN20' => 'Guangdong / &#24191;&#19996;',
			'CN21' => 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;',
			'CN22' => 'Hainan / &#28023;&#21335;',
			'CN23' => 'Chongqing / &#37325;&#24198;',
			'CN24' => 'Sichuan / &#22235;&#24029;',
			'CN25' => 'Guizhou / &#36149;&#24030;',
			'CN26' => 'Shaanxi / &#38485;&#35199;',
			'CN27' => 'Gansu / &#29976;&#32899;',
			'CN28' => 'Qinghai / &#38738;&#28023;',
			'CN29' => 'Ningxia Hui / &#23425;&#22799;',
			'CN30' => 'Macau / &#28595;&#38376;',
			'CN31' => 'Tibet / &#35199;&#34255;',
			'CN32' => 'Xinjiang / &#26032;&#30086;'
		);
	
		return apply_filters( 'chinese_regions', $regions );
	}
	
	/**
	 * Get New Zealand regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_new_zealand_regions_list() {
		$regions = array(
			'AK' => 'Auckland',
			'BP' => 'Bay of Plenty',
			'CT' => 'Canterbury',
			'HB' => 'Hawke&rsquo;s Bay',
			'MW' => 'Manawatu-Wanganui',
			'MB' => 'Marlborough',
			'NS' => 'Nelson',
			'NL' => 'Northland',
			'OT' => 'Otago',
			'SL' => 'Southland',
			'TK' => 'Taranaki',
			'TM' => 'Tasman',
			'WA' => 'Waikato',
			'WE' => 'Wellington',
			'WC' => 'West Coast'
		);
	
		return apply_filters( 'new_zealand_regions', $regions );
	}
	
	/**
	 * Get Indonesian regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_indonesian_regions_list() {
		$regions  = array(
			'AC' => 'Daerah Istimewa Aceh',
			'SU' => 'Sumatera Utara',
			'SB' => 'Sumatera Barat',
			'RI' => 'Riau',
			'KR' => 'Kepulauan Riau',
			'JA' => 'Jambi',
			'SS' => 'Sumatera Selatan',
			'BB' => 'Bangka Belitung',
			'BE' => 'Bengkulu',
			'LA' => 'Lampung',
			'JK' => 'DKI Jakarta',
			'JB' => 'Jawa Barat',
			'BT' => 'Banten',
			'JT' => 'Jawa Tengah',
			'JI' => 'Jawa Timur',
			'YO' => 'Daerah Istimewa Yogyakarta',
			'BA' => 'Bali',
			'NB' => 'Nusa Tenggara Barat',
			'NT' => 'Nusa Tenggara Timur',
			'KB' => 'Kalimantan Barat',
			'KT' => 'Kalimantan Tengah',
			'KI' => 'Kalimantan Timur',
			'KS' => 'Kalimantan Selatan',
			'KU' => 'Kalimantan Utara',
			'SA' => 'Sulawesi Utara',
			'ST' => 'Sulawesi Tengah',
			'SG' => 'Sulawesi Tenggara',
			'SR' => 'Sulawesi Barat',
			'SN' => 'Sulawesi Selatan',
			'GO' => 'Gorontalo',
			'MA' => 'Maluku',
			'MU' => 'Maluku Utara',
			'PA' => 'Papua',
			'PB' => 'Papua Barat'
		);
	
		return apply_filters( 'indonesia_regions', $regions );
	}
	
	/**
	 * Get Indian regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_indian_regions_list() {
		$regions = array(
			'AP' => 'Andra Pradesh',
			'AR' => 'Arunachal Pradesh',
			'AS' => 'Assam',
			'BR' => 'Bihar',
			'CT' => 'Chhattisgarh',
			'GA' => 'Goa',
			'GJ' => 'Gujarat',
			'HR' => 'Haryana',
			'HP' => 'Himachal Pradesh',
			'JK' => 'Jammu and Kashmir',
			'JH' => 'Jharkhand',
			'KA' => 'Karnataka',
			'KL' => 'Kerala',
			'MP' => 'Madhya Pradesh',
			'MH' => 'Maharashtra',
			'MN' => 'Manipur',
			'ML' => 'Meghalaya',
			'MZ' => 'Mizoram',
			'NL' => 'Nagaland',
			'OR' => 'Orissa',
			'PB' => 'Punjab',
			'RJ' => 'Rajasthan',
			'SK' => 'Sikkim',
			'TN' => 'Tamil Nadu',
			'TR' => 'Tripura',
			'UT' => 'Uttaranchal',
			'UP' => 'Uttar Pradesh',
			'WB' => 'West Bengal',
			'AN' => 'Andaman and Nicobar Islands',
			'CH' => 'Chandigarh',
			'DN' => 'Dadar and Nagar Haveli',
			'DD' => 'Daman and Diu',
			'DL' => 'Delhi',
			'LD' => 'Lakshadeep',
			'PY' => 'Pondicherry (Puducherry)'
		);
	
		return apply_filters( 'indian_regions', $regions );
	}
	
	/**
	 * Get Malaysian regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_malaysian_regions_list() {
		$regions = array(
			'JHR' => 'Johor',
			'KDH' => 'Kedah',
			'KTN' => 'Kelantan',
			'MLK' => 'Melaka',
			'NSN' => 'Negeri Sembilan',
			'PHG' => 'Pahang',
			'PRK' => 'Perak',
			'PLS' => 'Perlis',
			'PNG' => 'Pulau Pinang',
			'SBH' => 'Sabah',
			'SWK' => 'Sarawak',
			'SGR' => 'Selangor',
			'TRG' => 'Terengganu',
			'KUL' => 'W.P. Kuala Lumpur',
			'LBN' => 'W.P. Labuan',
			'PJY' => 'W.P. Putrajaya'
		);
	
		return apply_filters( 'malaysian_regions', $regions );
	}
	
	/**
	 * Get South African regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_south_african_regions_list() {
		$regions = array(
			'EC'  => 'Eastern Cape',
			'FS'  => 'Free State',
			'GP'  => 'Gauteng',
			'KZN' => 'KwaZulu-Natal',
			'LP'  => 'Limpopo',
			'MP'  => 'Mpumalanga',
			'NC'  => 'Northern Cape',
			'NW'  => 'North West',
			'WC'  => 'Western Cape'
		);
	
		return apply_filters( 'south_african_regions', $regions );
	}
	
	/**
	 * Get Thailand regions
	 *
	 * @since
	 * @return array $regions A list of regions
	 */
	public static function get_thailand_regions_list() {
		$regions = array(
			'TH-37' => 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)',
			'TH-15' => 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)',
			'TH-14' => 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)',
			'TH-10' => 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)',
			'TH-38' => 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)',
			'TH-31' => 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)',
			'TH-24' => 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)',
			'TH-18' => 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)',
			'TH-36' => 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)',
			'TH-22' => 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-50' => 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)',
			'TH-57' => 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)',
			'TH-20' => 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)',
			'TH-86' => 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)',
			'TH-46' => 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)',
			'TH-62' => 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)',
			'TH-71' => 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-40' => 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)',
			'TH-81' => 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)',
			'TH-52' => 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)',
			'TH-51' => 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)',
			'TH-42' => 'Loei (&#3648;&#3621;&#3618;)',
			'TH-16' => 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)',
			'TH-58' => 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)',
			'TH-44' => 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)',
			'TH-49' => 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)',
			'TH-26' => 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)',
			'TH-73' => 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)',
			'TH-48' => 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)',
			'TH-30' => 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)',
			'TH-60' => 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)',
			'TH-80' => 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)',
			'TH-55' => 'Nan (&#3609;&#3656;&#3634;&#3609;)',
			'TH-96' => 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)',
			'TH-39' => 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)',
			'TH-43' => 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)',
			'TH-12' => 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-13' => 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)',
			'TH-94' => 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)',
			'TH-82' => 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)',
			'TH-93' => 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)',
			'TH-56' => 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)',
			'TH-67' => 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)',
			'TH-76' => 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)',
			'TH-66' => 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)',
			'TH-65' => 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)',
			'TH-54' => 'Phrae (&#3649;&#3614;&#3619;&#3656;)',
			'TH-83' => 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)',
			'TH-25' => 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-77' => 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)',
			'TH-85' => 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)',
			'TH-70' => 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)',
			'TH-21' => 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)',
			'TH-45' => 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)',
			'TH-27' => 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)',
			'TH-47' => 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)',
			'TH-11' => 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)',
			'TH-74' => 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)',
			'TH-75' => 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)',
			'TH-19' => 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)',
			'TH-91' => 'Satun (&#3626;&#3605;&#3641;&#3621;)',
			'TH-17' => 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)',
			'TH-33' => 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)',
			'TH-90' => 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)',
			'TH-64' => 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)',
			'TH-72' => 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)',
			'TH-84' => 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)',
			'TH-32' => 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)',
			'TH-63' => 'Tak (&#3605;&#3634;&#3585;)',
			'TH-92' => 'Trang (&#3605;&#3619;&#3633;&#3591;)',
			'TH-23' => 'Trat (&#3605;&#3619;&#3634;&#3604;)',
			'TH-34' => 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)',
			'TH-41' => 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)',
			'TH-61' => 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)',
			'TH-53' => 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)',
			'TH-95' => 'Yala (&#3618;&#3632;&#3621;&#3634;)',
			'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)'
		);
	
		return apply_filters( 'thailand_regions', $regions );
	}

	/**
	 * Get Phone Codes
	 *
	 * @since
	 * @return array $phone_codes A list of Phone Codes indexed by country codes
	 */
	public static function getCountriesPhoneCodes(){
		$phone_codes = array(
			"AF" => "+93",
			"AL" => "+355",
			"DZ" => "+213",
			"AS" => "+1",
			"AD" => "+376",
			"AO" => "+244",
			"AI" => "+1",
			"AG" => "+1",
			"AR" => "+54",
			"AM" => "+374",
			"AW" => "+297",
			"AU" => "+61",
			"AT" => "+43",
			"AZ" => "+994",
			"BH" => "+973",
			"BD" => "+880",
			"BB" => "+1",
			"BY" => "+375",
			"BE" => "+32",
			"BZ" => "+501",
			"BJ" => "+229",
			"BM" => "+1",
			"BT" => "+975",
			"BO" => "+591",
			"BA" => "+387",
			"BW" => "+267",
			"BR" => "+55",
			"IO" => "+246",
			"VG" => "+1",
			"BN" => "+673",
			"BG" => "+359",
			"BF" => "+226",
			"MM" => "+95",
			"BI" => "+257",
			"KH" => "+855",
			"CM" => "+237",
			"CA" => "+1",
			"CV" => "+238",
			"KY" => "+1",
			"CF" => "+236",
			"TD" => "+235",
			"CL" => "+56",
			"CN" => "+86",
			"CO" => "+57",
			"KM" => "+269",
			"CK" => "+682",
			"CR" => "+506",
			"CI" => "+225",
			"HR" => "+385",
			"CU" => "+53",
			"CY" => "+357",
			"CZ" => "+420",
			"CD" => "+243",
			"DK" => "+45",
			"DJ" => "+253",
			"DM" => "+1",
			"DO" => "+1",
			"EC" => "+593",
			"EG" => "+20",
			"SV" => "+503",
			"GQ" => "+240",
			"ER" => "+291",
			"EE" => "+372",
			"ET" => "+251",
			"FK" => "+500",
			"FO" => "+298",
			"FM" => "+691",
			"FJ" => "+679",
			"FI" => "+358",
			"FR" => "+33",
			"GF" => "+594",
			"PF" => "+689",
			"GA" => "+241",
			"GE" => "+995",
			"DE" => "+49",
			"GH" => "+233",
			"GI" => "+350",
			"GR" => "+30",
			"GL" => "+299",
			"GD" => "+1",
			"GP" => "+590",
			"GU" => "+1",
			"GT" => "+502",
			"GN" => "+224",
			"GW" => "+245",
			"GY" => "+592",
			"HT" => "+509",
			"HN" => "+504",
			"HK" => "+852",
			"HU" => "+36",
			"IS" => "+354",
			"IN" => "+91",
			"ID" => "+62",
			"IR" => "+98",
			"IQ" => "+964",
			"IE" => "+353",
			"IL" => "+972",
			"IT" => "+39",
			"JM" => "+1",
			"JP" => "+81",
			"JO" => "+962",
			"KZ" => "+7",
			"KE" => "+254",
			"KI" => "+686",
			"XK" => "+381",
			"KW" => "+965",
			"KG" => "+996",
			"LA" => "+856",
			"LV" => "+371",
			"LB" => "+961",
			"LS" => "+266",
			"LR" => "+231",
			"LY" => "+218",
			"LI" => "+423",
			"LT" => "+370",
			"LU" => "+352",
			"MO" => "+853",
			"MK" => "+389",
			"MG" => "+261",
			"MW" => "+265",
			"MY" => "+60",
			"MV" => "+960",
			"ML" => "+223",
			"MT" => "+356",
			"MH" => "+692",
			"MQ" => "+596",
			"MR" => "+222",
			"MU" => "+230",
			"YT" => "+262",
			"MX" => "+52",
			"MD" => "+373",
			"MC" => "+377",
			"MN" => "+976",
			"ME" => "+382",
			"MS" => "+1",
			"MA" => "+212",
			"MZ" => "+258",
			"NA" => "+264",
			"NR" => "+674",
			"NP" => "+977",
			"NL" => "+31",
			"AN" => "+599",
			"NC" => "+687",
			"NZ" => "+64",
			"NI" => "+505",
			"NE" => "+227",
			"NG" => "+234",
			"NU" => "+683",
			"NF" => "+672",
			"KP" => "+850",
			"MP" => "+1",
			"NO" => "+47",
			"OM" => "+968",
			"PK" => "+92",
			"PW" => "+680",
			"PS" => "+970",
			"PA" => "+507",
			"PG" => "+675",
			"PY" => "+595",
			"PE" => "+51",
			"PH" => "+63",
			"PL" => "+48",
			"PT" => "+351",
			"PR" => "+1",
			"QA" => "+974",
			"CG" => "+242",
			"RE" => "+262",
			"RO" => "+40",
			"RU" => "+7",
			"RW" => "+250",
			"BL" => "+590",
			"SH" => "+290",
			"KN" => "+1",
			"MF" => "+590",
			"PM" => "+508",
			"VC" => "+1",
			"WS" => "+685",
			"SM" => "+378",
			"ST" => "+239",
			"SA" => "+966",
			"SN" => "+221",
			"RS" => "+381",
			"SC" => "+248",
			"SL" => "+232",
			"SG" => "+65",
			"SK" => "+421",
			"SI" => "+386",
			"SB" => "+677",
			"SO" => "+252",
			"ZA" => "+27",
			"KR" => "+82",
			"ES" => "+34",
			"LK" => "+94",
			"LC" => "+1",
			"SD" => "+249",
			"SR" => "+597",
			"SZ" => "+268",
			"SE" => "+46",
			"CH" => "+41",
			"SY" => "+963",
			"TW" => "+886",
			"TJ" => "+992",
			"TZ" => "+255",
			"TH" => "+66",
			"BS" => "+1",
			"GM" => "+220",
			"TL" => "+670",
			"TG" => "+228",
			"TK" => "+690",
			"TO" => "+676",
			"TT" => "+1",
			"TN" => "+216",
			"TR" => "+90",
			"TM" => "+993",
			"TC" => "+1",
			"TV" => "+688",
			"UG" => "+256",
			"UA" => "+380",
			"AE" => "+971",
			"GB" => "+44",
			"US" => "+1",
			"UY" => "+598",
			"VI" => "+1",
			"UZ" => "+998",
			"VU" => "+678",
			"VA" => "+39",
			"VE" => "+58",
			"VN" => "+84",
			"WF" => "+681",
			"YE" => "+967",
			"ZM" => "+260",
			"ZW" => "+263"
		);
		return $phone_codes;	
	}




}
