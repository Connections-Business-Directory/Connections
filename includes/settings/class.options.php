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
			''                 => __( 'Select Relation', 'connections' ),
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

}