<?php

/**
 * Class for working with an entry object.
 *
 * @package     Connections
 * @subpackage  Entry
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Entry class
 */
class cnEntry {
	/**
	 * @var integer
	 */
	private $id;

	/**
	 * @var string
	 */
	private $ruid;

	/**
	 * @var integer unix timestamp
	 */
	private $timeStamp;

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var integer unix timestamp
	 */
	private $dateAdded;

	/**
	 * @var string
	 */
	private $honorificPrefix = '';

	/**
	 * @var string
	 */
	private $firstName = '';

	/**
	 * @var string
	 */
	private $middleName = '';

	/**
	 * @var string
	 */
	private $lastName = '';

	/**
	 * @var string
	 */
	private $honorificSuffix = '';

	/**
	 * @var string
	 */
	private $title = '';

	/**
	 * @var string
	 */
	private $organization = '';

	/**
	 * @var string
	 */
	private $department = '';

	/**
	 * @var string
	 */
	private $contactFirstName = '';

	/**
	 * @var string
	 */
	private $contactLastName = '';

	/**
	 * @var string
	 */
	private $familyName = '';

	/**
	 * Associative array of addresses
	 *
	 * @var null|string
	 */
	private $addresses;

	/**
	 * Associative array of phone numbers
	 *
	 * @var null|string
	 */
	private $phoneNumbers;

	/**
	 * Associative array of email addresses
	 *
	 * @var
	 */
	private $emailAddresses;

	/**
	 * Associative array of websites
	 *
	 * @deprecated since 0.7.2.0
	 * @var array
	 */
	//private $websites;

	/**
	 * Associative array of links
	 *
	 * @var null|string
	 */
	private $links;

	/**
	 * Associative array of instant messengers IDs
	 *
	 * @var null|string
	 */
	private $im;

	/**
	 * @var null|string
	 */
	private $socialMedia;

	/**
	 * Unix time: Birthday.
	 *
	 * @var int|string unix time
	 */
	private $birthday;

	/**
	 * Unix time: Anniversary.
	 *
	 * @var int|string unix time
	 */
	private $anniversary;

	/**
	 * The date data stored serialized array.
	 *
	 * @var null|string
	 *
	 * @since 0.7.3.0
	 */
	private $dates;

	/**
	 * String: Entry notes.
	 *
	 * @var string
	 */
	private $bio;

	/**
	 * String: Entry biography.
	 *
	 * @var string
	 */
	private $notes;

	/**
	 * String: Visibility Type; public, private, unlisted
	 *
	 * @var string
	 */
	private $visibility = NULL;

	/**
	 * @since unknown
	 * @var array|string
	 */
	private $options;

	/**
	 * @var bool
	 */
	private $imageLinked;

	/**
	 * @since unknown
	 * @var bool
	 */
	private $imageDisplay;

	//private $imageNameThumbnail;
	//private $imageNameCard;
	//private $imageNameProfile;
	//private $imageNameOriginal;

	/**
	 * @since unknown
	 * @var bool
	 */
	private $logoLinked;

	/**
	 * @since unknown
	 * @var bool
	 */
	private $logoDisplay;

	//private $logoName;

	/**
	 * @since unknown
	 * @var string
	 */
	private $entryType;

	/**
	 * @since unknown
	 * @var array|string
	 */
	private $familyMembers;

	/**
	 * @since unknown
	 * @var mixed array|WP_Error An array of categories associated to an entry.
	 */
	private $categories;

	/**
	 * @since unknown
	 * @var int
	 */
	private $addedBy;

	/**
	 * @since unknown
	 * @var int
	 */
	private $editedBy;

	/**
	 * @since unknown
	 * @var int
	 */
	private $owner;

	/**
	 * @since unknown
	 * @var int
	 */
	private $user;

	/**
	 * @since unknown
	 * @var string
	 */
	private $status;

	/**
	 * @since unknown
	 * @var cnFormatting
	 */
	public $format;

	/**
	 * An instance of cnValidate.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @var cnValidate
	 */
	public $validate;

	/**
	 * @access private
	 * @since  unknown
	 *
	 * @var string
	 */
	private $sortColumn;

	//private $updateObjectCache = FALSE;

	/**
	 * Stored the directory home page ID and whether or no to force permalinks to the directory home.
	 *
	 * @access public
	 * @since  8.1.6
	 *
	 * @var array
	 */
	public $directoryHome = array();

	/**
	 * Setup the entry object.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param mixed object|null $entry
	 */
	public function __construct( $entry = NULL ) {

		/**
		 * @var connectionsLoad $connections
		 */
		global $connections;

		// Load the formatting class for sanitizing the get methods.
		$this->format = new cnFormatting();

		// Load the validation class.
		$this->validate = new cnValidate();

		if ( ! is_null( $entry ) ) {

			if ( isset( $entry->id ) ) $this->id = (integer) $entry->id;
			if ( isset( $entry->user ) ) $this->user = (integer) $entry->user;
			if ( isset( $entry->ts ) ) $this->timeStamp = $entry->ts;
			if ( isset( $entry->date_added ) ) $this->dateAdded = (integer) $entry->date_added;

			if ( isset( $entry->slug ) ) $this->slug = $entry->slug;

			if ( isset( $entry->honorific_prefix ) ) $this->honorificPrefix = $entry->honorific_prefix;
			if ( isset( $entry->first_name ) ) $this->firstName = $entry->first_name;
			if ( isset( $entry->middle_name ) ) $this->middleName = $entry->middle_name;
			if ( isset( $entry->last_name ) ) $this->lastName = $entry->last_name;
			if ( isset( $entry->honorific_suffix ) ) $this->honorificSuffix = $entry->honorific_suffix;
			if ( isset( $entry->title ) ) $this->title = $entry->title;
			if ( isset( $entry->organization ) ) $this->organization = $entry->organization;
			if ( isset( $entry->contact_first_name ) ) $this->contactFirstName = $entry->contact_first_name;
			if ( isset( $entry->contact_last_name ) ) $this->contactLastName = $entry->contact_last_name;
			if ( isset( $entry->department ) ) $this->department = $entry->department;
			if ( isset( $entry->family_name ) ) $this->familyName = $entry->family_name;

			if ( isset( $entry->addresses ) ) $this->addresses = $entry->addresses;
			if ( isset( $entry->phone_numbers ) ) $this->phoneNumbers = $entry->phone_numbers;
			if ( isset( $entry->email ) ) $this->emailAddresses = $entry->email;
			if ( isset( $entry->im ) ) $this->im = $entry->im;
			if ( isset( $entry->social ) ) $this->socialMedia = $entry->social;
			if ( isset( $entry->links ) ) $this->links = $entry->links;
			if ( isset( $entry->dates ) ) $this->dates = $entry->dates;

			if ( isset( $entry->birthday ) ) $this->birthday = (integer) $entry->birthday;
			if ( isset( $entry->anniversary ) ) $this->anniversary = (integer) $entry->anniversary;

			if ( isset( $entry->bio ) ) $this->bio = $entry->bio;
			if ( isset( $entry->notes ) ) $this->notes = $entry->notes;
			if ( isset( $entry->visibility ) ) $this->visibility = $entry->visibility;
			if ( isset( $entry->sort_column ) ) $this->sortColumn = $entry->sort_column;

			if ( isset( $entry->options ) ) {
				$this->options = unserialize( $entry->options );

				if ( isset( $this->options['image'] ) ) {
					$this->imageLinked = $this->options['image']['linked'];
					$this->imageDisplay = $this->options['image']['display'];

					//if ( isset( $this->options['image']['name'] ) ) {
					//	$this->imageNameThumbnail = isset( $this->options['image']['name']['thumbnail'] ) ? $this->options['image']['name']['thumbnail'] : '';
					//	$this->imageNameCard = isset( $this->options['image']['name']['entry'] ) ? $this->options['image']['name']['entry'] : '';
					//	$this->imageNameProfile = isset( $this->options['image']['name']['profile'] ) ? $this->options['image']['name']['profile'] : '';
					//	$this->imageNameOriginal = isset( $this->options['image']['name']['original'] ) ? $this->options['image']['name']['original'] : '';
					//}
				}

				if ( isset( $this->options['logo'] ) ) {
					$this->logoLinked = $this->options['logo']['linked'];
					$this->logoDisplay = $this->options['logo']['display'];

					//if ( isset( $this->options['logo']['name'] ) ) {
					//	$this->logoName =$this->options['logo']['name'];
					//}
				}

				if ( isset( $this->options['entry']['type'] ) ) $this->entryType = $this->options['entry']['type'];
				if ( isset( $this->options['connection_group'] ) ) $this->familyMembers = $this->options['connection_group']; // For compatibility with versions <= 0.7.0.4
				if ( isset( $this->options['group']['family'] ) ) $this->familyMembers = $this->options['group']['family'];
			}

			if ( isset( $entry->id ) ) $this->categories = $connections->retrieve->entryCategories( $this->getId() );

			if ( isset( $entry->added_by ) ) $this->addedBy = $entry->added_by;
			if ( isset( $entry->edited_by ) ) $this->editedBy = $entry->edited_by;

			if ( isset( $entry->owner ) ) $this->owner = $entry->owner;
			if ( isset( $entry->user ) ) $this->user = $entry->user;

			if ( isset( $entry->status ) ) $this->status = $entry->status;

			$this->ruid = uniqid( $this->getId() , FALSE );

			// Move any legacy images and logo, pre 8.1, to the new folder structure.
			$this->processLegacyImages( $this->getImageNameOriginal() );
			$this->processLegacyLogo( $this->getLogoName() );
		}
	}

	/**
	 * Returns $id.
	 */
	public function getId() {
		return (integer) $this->id;
	}

	/**
	 * Sets $id.
	 *
	 * @param integer $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns $userID.
	 */
	public function getUser() {
		return (integer) empty( $this->user ) ? 0 : $this->user;
	}

	/**
	 * Sets $userID.
	 *
	 * @param integer $id
	 */
	public function setUser( $id ) {
		$this->user = $id;
	}

	/**
	 * Returns a runtime unique id.
	 *
	 * @return string
	 */
	public function getRuid() {
		return $this->ruid;
	}

	/**
	 * Timestamp format can be sent as a string variable.
	 * Returns $timeStamp
	 *
	 * @param mixed string|null  $format
	 *
	 * @return string
	 */
	public function getFormattedTimeStamp( $format = NULL ) {

		if ( is_null( $format ) ) {
			$format = 'm/d/Y';
		}

		return date( $format, strtotime( $this->timeStamp ) );
	}

	/**
	 * Timestamp format can be sent as a string variable.
	 * Returns $unixTimeStamp
	 *
	 * @see entry::$timeStamp
	 */
	public function getUnixTimeStamp() {
		return $this->timeStamp;
	}

	/**
	 * The human readable difference between the date the entry was last edited and the current date.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function getHumanTimeDiff() {
		return human_time_diff( strtotime( $this->timeStamp ), current_time( 'timestamp' ) );
	}

	/**
	 * Get the formatted date that the entry was added.
	 *
	 * @todo Add logic to deal with the possibility that date() can return FALSE.
	 * @todo Date should be run thru date_i18n().
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function getDateAdded( $format = 'm/d/Y' ) {

		if ( $this->dateAdded != NULL ) {

			return date( $format, $this->dateAdded );

		} else {

			return 'Unknown';
		}
	}

	/**
	 * Set the values to be used to determine the page ID to be used for the directory links.
	 *
	 * @access public
	 * @since  0.7.9
	 *
	 * @see cnEntry::$directoryHome
	 *
	 * @param  array $atts {
	 *     Optional.
	 *
	 *     @type int  $page_id    The page ID of the directory home page.
	 *     @type bool $force_home Whether or not to force the permalinks to resolve to the directory home page.
	 * }
	 *
	 * @return void
	 */
	public function directoryHome( $atts = array() ) {

		$defaults = array(
			'page_id'    => cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
			'force_home' => FALSE,
		);

		$this->directoryHome = cnSanitize::args( $atts, $defaults );
	}

	/**
	 * Returns the permalink for the entry.
	 *
	 * @access public
	 * @since  8.1.6
	 *
	 * @uses   cnURL::permalink()
	 *
	 * @return string
	 */
	public function getPermalink() {

		return cnURL::permalink(
			array(
				'type'       => 'name',
				'slug'       => $this->getSlug(),
				'home_id'    => $this->directoryHome['page_id'],
				'force_home' => $this->directoryHome['force_home'],
				'data'       => 'url',
				'return'     => TRUE,
			)
		);
	}

	/**
	 * Returns $slug.
	 *
	 * @see cnEntry::$slug
	 */
	public function getSlug() {

		return ( empty( $this->slug ) ? $this->getUniqueSlug() : $this->slug );
	}

	/**
	 * Sets $slug.
	 *
	 * @param string $slug
	 */
	public function setSlug( $slug ) {

		$this->slug = $this->getUniqueSlug( $slug );
	}

	/**
	 * Returns a unique sanitized slug for insertion in the database.
	 *
	 * NOTE: If the entry name is UTF8 it will be URL encoded by the sanitize_title() function.
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	private function getUniqueSlug( $slug = '' ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		// WP function -- formatting class
		$slug = empty( $slug ) || ! is_string( $slug ) ? sanitize_title( $this->getName( array( 'format' => '%first%-%last%' ) ) ) : sanitize_title( $slug );

		// If the entry was entered with no name, use the entry ID instead.
		if ( empty( $slug ) ) return 'cn-id-' . $this->getId();

		$query = $wpdb->prepare( 'SELECT slug FROM ' . CN_ENTRY_TABLE . ' WHERE slug = %s', $slug );

		if ( $wpdb->get_var( $query ) ) {
			$num = 2;
			do {
				$alt_slug = $slug . "-$num";
				$num++;
				$slug_check = $wpdb->get_var( $wpdb->prepare( 'SELECT slug FROM ' . CN_ENTRY_TABLE . ' WHERE slug = %s', $alt_slug ) );
			}
			while ( $slug_check );

			$slug = $alt_slug;
		}

		return $slug;
	}

	/**
	 * Returns the name of the entry based on its type.
	 *
	 * @example
	 * If an entry is an individual this would return their name as Last Name, First Name
	 *
	 * $this->getName( array( 'format' => '%last%, %first% %middle%' ) );
	 *
	 * @param array $atts   {
	 *     Optional
	 *
	 *     @type string $format The format the name should be returned as.
	 *                          Default '%prefix% %first% %middle% %last% %suffix%'.
	 *                          Accepts any combination of the following tokens: '%prefix%', '%first%', '%middle%', '%last%', '%suffix%'
	 * }
	 * @param string $context   The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getName( $atts = array(), $context = 'display' ) {

		$defaults = array(
			'format' => '%prefix% %first% %middle% %last% %suffix%',
		);

		/**
		 * Filter the arguments.
		 *
		 * @since unknown
		 *
		 * @param array $atts An array of arguments.
		 */
		$atts = cnSanitize::args( apply_filters( 'cn_name_atts', $atts ), $defaults );

		switch ( $this->getEntryType() ) {

			case 'organization':

				$name = $this->getOrganization( $context );
				break;

			case 'family':

				$name = $this->getFamilyName( $context );
				break;

			default:

				$name = $this->getIndividualName( $atts, $context );
				break;
		}

		return $name;
	}

	/**
	 * Returns the honorable prefix.
	 *
	 * Use @see cnEntry::getName() instead of calling this method directly.
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized. This method will eventually be declared as private.
	 *
	 * @return string
	 */
	public function getHonorificPrefix( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->honorificPrefix, $context );
	}

	/**
	 * Sets the honorable prefix.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $prefix
	 * @param string $context The context in which it should be sanitized.
	 */
	public function setHonorificPrefix( $prefix, $context = 'db' ) {

		$this->honorificPrefix = cnSanitize::field( 'name', $prefix, $context );
	}

	/**
	 * Returns the first name.
	 *
	 * Use @see cnEntry::getName() instead of calling this method directly. This method will eventually be declared as private.
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getFirstName( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->firstName, $context );
	}

	/**
	 * Sets the first name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $firstName
	 * @param string $context   The context in which it should be sanitized.
	 */
	public function setFirstName( $firstName, $context = 'db' ) {

		$this->firstName = cnSanitize::field( 'name', $firstName, $context );
	}

	/**
	 * Returns the middle name.
	 *
	 * Use @see cnEntry::getName() instead of calling this method directly. This method will eventually be declared as private.
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getMiddleName( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->middleName, $context );
	}

	/**
	 * Sets the middle name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $middleName
	 * @param string $context    The context in which it should be sanitized.
	 */
	public function setMiddleName( $middleName, $context = 'db' ) {

		$this->middleName = cnSanitize::field( 'name', $middleName, $context );
	}

	/**
	 * Returns the last name.
	 *
	 * Use @see cnEntry::getName() instead of calling this method directly. This method will eventually be declared as private.
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getLastName( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->lastName, $context );
	}

	/**
	 * Sets the last name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $lastName
	 * @param string $context  The context in which it should be sanitized.
	 */
	public function setLastName( $lastName, $context = 'db' ) {

		$this->lastName = cnSanitize::field( 'name', $lastName, $context );
	}

	/**
	 * Returns the entry's name suffix.
	 *
	 * Use @see cnEntry::getName() instead of calling this method directly. This method will eventually be declared as private.
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getHonorificSuffix( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->honorificSuffix, $context );
	}

	/**
	 * Sets the honorable suffix.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $suffix
	 * @param string $context The context in which it should be sanitized.
	 */
	public function setHonorificSuffix( $suffix, $context = 'db' ) {

		$this->honorificSuffix = cnSanitize::field( 'name', $suffix, $context );
	}

	/**
	 * Returns the name of the Individual.
	 *
	 * @access private
	 * @since  8.1.7
	 *
	 * @uses cnString::normalize()
	 *
	 * @param array $atts {
	 *     Optional
	 *
	 *     @type string $format The format the name should be returned as.
	 *                          Default '%prefix% %first% %middle% %last% %suffix%'.
	 *                          Accepts any combination of the following tokens: '%prefix%', '%first%', '%middle%', '%last%', '%suffix%'
	 * }
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	private function getIndividualName( $atts = array(), $context = 'display' ) {

		$search  = array( '%prefix%', '%first%', '%middle%', '%last%', '%suffix%' );
		$replace = array(
			$this->getHonorificPrefix( $context ),
			$this->getFirstName( $context ),
			$this->getMiddleName( $context ),
			$this->getLastName( $context ),
			$this->getHonorificSuffix( $context ),
		);

		$name = str_ireplace(
			$search,
			$replace,
			empty( $atts['format'] ) ? '%prefix% %first% %middle% %last% %suffix%' : $atts['format']
		);

		return cnString::normalize( $name );
	}

	/**
	 * Get the name, in format "first middle last".
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated 8.1.7 Use {@see cnEntry::getName()} instead.
	 * @see cnEntry::getName()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getFullFirstLastName( $context = 'display' ) {

		return $this->getName( array( 'format' => '%first% %middle% %last%' ), $context );
	}

	/**
	 * Get the name, in format "last, first middle".
	 *
	 * @access private
	 * @since  unknown
	 * @deprecated 8.1.7 Use {@see cnEntry::getName()} instead.
	 * @see cnEntry::getName()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getFullLastFirstName( $context = 'display' ) {

		return $this->getName( array( 'format' => '%last%, %first% %middle%' ), $context );
	}

	/**
	 * Get the organization name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getOrganization( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->organization, $context );
	}

	/**
	 * Set the organization name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field
	 *
	 * @param string $organization
	 * @param string $context      The context in which it should be sanitized.
	 */
	public function setOrganization( $organization, $context = 'db' ) {

		$this->organization = cnSanitize::field( 'name', $organization, $context );
	}

	/**
	 * Get the title.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getTitle( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->title, $context );
	}

	/**
	 * Set the title.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $title
	 * @param string $context The context in which it should be sanitized.
	 */
	public function setTitle( $title, $context = 'db' ) {

		$this->title = cnSanitize::field( 'name', $title, $context );
	}

	/**
	 * Get the department.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getDepartment( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->department, $context );
	}

	/**
	 * Set the department.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field
	 *
	 * @param string $department
	 * @param string $context    The context in which it should be sanitized.
	 */
	public function setDepartment( $department, $context = 'db' ) {

		$this->department = cnSanitize::field( 'name', $department, $context );
	}

	/**
	 * Get the contact name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnString::normalize()
	 *
	 * @param array  $atts {
	 *     Optional
	 *
	 *     @type string $format The format the name should be returned as.
	 *                          Default '%first% %last%'.
	 *                          Accepts any combination of the following tokens: '%first%', '%last%''
	 * }
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getContactName( $atts = array(), $context = 'display' ) {

		$defaults = array( 'format' => '%first% %last%' );

		$atts = cnSanitize::args( apply_filters( 'cn_contact_name_atts', $atts ), $defaults );

		$search  = array( '%first%', '%last%' );
		$replace = array();

		$replace[] = $this->contactFirstName ? $this->getContactFirstName( $context ) : '';

		$replace[] = $this->contactLastName ? $this->getContactLastName( $context ) : '';

		$name = str_ireplace( $search, $replace, $atts['format'] );

		return cnString::normalize( $name );
	}

	/**
	 * Get the contact first name.
	 *
	 * Use @see cnEntry::getContactName() instead of calling this method directly. This method will eventually be declared as private.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getContactFirstName( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->contactFirstName, $context );
	}

	/**
	 * Set the contact first name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $firstName
	 * @param string $context   The context in which it should be sanitized.
	 */
	public function setContactFirstName( $firstName, $context = 'db'  ) {

		$this->contactFirstName = cnSanitize::field( 'name', $firstName, $context );
	}

	/**
	 * Get the contact last name.
	 *
	 * Use @see cnEntry::getContactName() instead of calling this method directly. This method will eventually be declared as private.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getContactLastName( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->contactLastName, $context );
	}

	/**
	 * Set the contact last name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $lastName
	 * @param string $context  The context in which it should be sanitized.
	 */
	public function setContactLastName( $lastName, $context = 'db' ) {

		$this->contactLastName = cnSanitize::field( 'name', $lastName, $context );
	}

	/**
	 * Get the family name.
	 *
	 * Use @see cnEntry::getName() instead of calling this method directly. This method will eventually be declared as private.
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return string
	 */
	public function getFamilyName( $context = 'display' ) {

		return cnSanitize::field( 'name', $this->familyName, $context );
	}

	/**
	 * Set the family name.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   cnSanitize::field()
	 *
	 * @param string $familyName
	 * @param string $context    The context in which it should be sanitized.
	 */
	public function setFamilyName( $familyName, $context = 'db' ) {

		$this->familyName = cnSanitize::field( 'name', $familyName, $context );
	}

	/**
	 * Returns family member member entry ID and relation.
	 */
	public function getFamilyMembers() {
		if ( ! empty( $this->familyMembers ) ) {
			return $this->familyMembers;
		}
		else {
			return array();
		}
	}

	/**
	 * The form to capture the user IDs and relationship stores the data
	 * in a two-dimensional array as follows:
	 * 		array[0]
	 * 			array[entry_id]
	 * 				 [relation]
	 *
	 * This re-writes the data into an associative array entry_id => relation.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $relations
	 */
	public function setFamilyMembers( $relations ) {

		$family = array();

		if ( ! empty( $relations ) ) {

			foreach ( $relations as $relation ) {

				$family[ $relation['entry_id'] ] = $relation['relation'];
			}
		}

		$this->options['group']['family'] = $family;
	}

	/**
	 * Returns as an array of objects containing the addresses per the defined options for the current entry.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array  $atts {
	 *     @type bool         $preferred Whether or not to return only the preferred address.
	 *                                   Default: false
	 *     @type array|string $type      The address types to return.
	 *                                   Default: array() which will return all registered address types.
	 *                                   Accepts: home, work, school, other and any other registered types.
	 *     @type array|string $city      Return address in the defined cities.
	 *     @type array|string $state     Return address in the defined states.
	 *     @type array|string $country   Return address in the defined countries.
	 *     @type array        $coordinates {
	 *         Return the addresses at the specific coordinates.
	 *         @type float $latitude
	 *         @type float $longitude
	 *     }
	 * }
	 *
	 * @param bool   $cached  Returns the cached address data rather than querying the db.
	 * @param bool   $saving  Set as TRUE if adding a new entry or updating an existing entry.
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return array
	 */
	public function getAddresses( $atts = array(), $cached = TRUE, $saving = FALSE, $context = 'display' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$addressTypes = $instance->options->getDefaultAddressValues();
		$results = array();

		$atts = apply_filters( 'cn_address_atts', $atts );
		$cached = apply_filters( 'cn_address_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred'   => FALSE,
			'type'        => array(),
			'city'        => array(),
			'state'       => array(),
			'zipcode'     => array(),
			'country'     => array(),
			'coordinates' => array(),
			'limit'       => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->addresses ) ) {

				$addresses = unserialize( $this->addresses );
				if ( empty( $addresses ) ) return $results;

				/**
				 * @var bool         $preferred
				 * @var array|string $type
				 * @var array|string $city
				 * @var array|string $state
				 * @var array|string $zipcode
				 * @var array|string $country
				 * @var array        $coordinates
				 */
				extract( $atts );

				/*
				 * Covert these to values to an array if they were supplied as a comma delimited string
				 */
				cnFunction::parseStringList( $type );
				cnFunction::parseStringList( $city );
				cnFunction::parseStringList( $state );
				cnFunction::parseStringList( $zipcode );
				cnFunction::parseStringList( $country );

				foreach ( (array) $addresses as $key => $address ) {

					if ( empty( $address ) ) continue;

					/*
					 * Previous versions stored empty arrays for addresses, check for them, continue if found.
					 * NOTE: Checking only the fields available in the previous versions.
					 */
					if ( empty( $address['line_1'] ) &&
						empty( $address['line_2'] ) &&
						empty( $address['address_line1'] ) &&
						empty( $address['address_line2'] ) &&
						empty( $address['city'] ) &&
						empty( $address['state'] ) &&
						empty( $address['zipcode'] ) &&
						empty( $address['country'] ) &&
						empty( $address['latitude'] ) &&
						empty( $address['longitude'] ) ) continue;

					$row = new stdClass();

					$row->id         = isset( $address['id'] ) ? (int) $address['id'] : 0;
					$row->order      = isset( $address['order'] ) ? (int) $address['order'] : 0;
					$row->preferred  = isset( $address['preferred'] ) ? (bool) $address['preferred'] : FALSE;
					$row->type       = isset( $address['type'] ) ? cnSanitize::field( 'attribute', $address['type'], $context ) : '';
					$row->line_1     = isset( $address['line_1'] ) ? cnSanitize::field( 'street', $address['line_1'], $context ) : '';
					$row->line_2     = isset( $address['line_2'] ) ? cnSanitize::field( 'street', $address['line_2'], $context ) : '';
					$row->line_3     = isset( $address['line_3'] ) ? cnSanitize::field( 'street', $address['line_3'], $context ) : '';
					$row->city       = isset( $address['city'] ) ? cnSanitize::field( 'locality', $address['city'], $context ) : '';
					$row->state      = isset( $address['state'] ) ? cnSanitize::field( 'region', $address['state'], $context ) : '';
					$row->zipcode    = isset( $address['zipcode'] ) ? cnSanitize::field( 'postal-code', $address['zipcode'], $context ) : '';
					$row->country    = isset( $address['country'] ) ? cnSanitize::field( 'country', $address['country'] ) : '';
					$row->latitude   = isset( $address['latitude'] ) ? number_format( (float) $address['latitude'], 12 ) : NULL;
					$row->longitude  = isset( $address['longitude'] ) ? number_format( (float) $address['longitude'], 12 ) : NULL;
					$row->visibility = isset( $address['visibility'] ) ? cnSanitize::field( 'attribute', $address['visibility'], $context ) : '';

					/*
					 * Set the address name based on the address type.
					 */
					// Some previous versions did set the address type, so set the type to 'other'.
					if ( empty( $row->type ) || ! isset( $addressTypes[ $row->type ] ) ) $row->type = 'other';

					// Recent previous versions set the type to the Select string from the drop down, so set the name to 'Other'.
					$row->name = ! isset( $addressTypes[ $row->type ] ) || $addressTypes[ $row->type ] == 'Select' ? 'Other' : $addressTypes[ $row->type ];

					/*
					 * // START -- Compatibility for previous versions.
					 */
					if ( isset( $address['address_line1'] ) && ! empty( $address['address_line1'] ) ) $row->line_1 = cnSanitize::field( 'street', $address['address_line1'], $context );
					if ( isset( $address['address_line2'] ) && ! empty( $address['address_line2'] ) ) $row->line_2 = cnSanitize::field( 'street', $address['address_line2'], $context );

					$row->line_one   =& $row->line_1;
					$row->line_two   =& $row->line_2;
					$row->line_three =& $row->line_3;

					// Versions prior to 0.7.1.6 may not have visibility set, so we'll assume it was 'public' since it wasn't the option before.
					if ( ! isset( $address['visibility'] ) || empty( $address['visibility'] ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */

					/*
					 * // START -- Do not return addresses that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					if ( ! empty( $city ) && ! in_array( $row->city, $city ) ) continue;
					if ( ! empty( $state ) && ! in_array( $row->state, $state ) ) continue;
					if ( ! empty( $zipcode ) && ! in_array( $row->zipcode, $zipcode ) ) continue;
					if ( ! empty( $country ) && ! in_array( $row->country, $country ) ) continue;
					/*
					 * // END -- Do not return addresses that do not match the supplied $atts.
					 */

					// If the user does not have permission to view the address, do not return it.
					if ( ! $this->validate->userPermitted( $row->visibility ) && ! $saving ) continue;

					/**
					 * An address object.
					 *
					 * @since unknown
					 *
					 * @param object $row {
					 *     @type int    $id         The address ID if it was retrieved from the db.
					 *     @type bool   $preferred  Whether the address is the preferred address or not.
					 *     @type string $type       The address type.
					 *     @type string $line_1     Address line 1.
					 *     @type string $line_2     Address line 2.
					 *     @type string $line_3     Address line 3.
					 *     @type string $city       The address locality.
					 *     @type string $state      The address region.
					 *     @type string $country    The address country.
					 *     @type float  $latitude   The address latitude.
					 *     @type float  $longitude  The address longitude.
					 *     @type string $visibility The address visibility.
					 * }
					 */
					$results[] = apply_filters( 'cn_address', $row );
				}

				/*
				 * Limit the number of results.
				 */
				if ( ! is_null( $atts['limit'] ) && 1 < count( $results ) ) {

					$results = array_slice( $results, 0, absint( $atts['limit'] ) );
				}

			}

		} else {

			// Exit right away and return an empty array if the entry ID has not been set otherwise all addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$addresses = $instance->retrieve->addresses( $atts );

			if ( empty( $addresses ) ) return $results;

			foreach ( $addresses as $address ) {

				$address->id         = (int) $address->id;
				$address->order      = (int) $address->order;
				$address->preferred  = (bool) $address->preferred;
				$address->type       = cnSanitize::field( 'attribute', $address->type, $context );
				$address->line_1     = cnSanitize::field( 'street', $address->line_1, $context );
				$address->line_2     = cnSanitize::field( 'street', $address->line_2, $context );
				$address->line_3     = cnSanitize::field( 'street', $address->line_3, $context );
				$address->city       = cnSanitize::field( 'locality', $address->city, $context );
				$address->state      = cnSanitize::field( 'region', $address->state, $context );
				$address->zipcode    = cnSanitize::field( 'postal-code', $address->zipcode, $context );
				$address->country    = cnSanitize::field( 'country', $address->country, $context );
				$address->latitude   = empty( $address->latitude ) ? NULL : number_format( (float) $address->latitude, 12 );
				$address->longitude  = empty( $address->longitude )? NULL : number_format( (float) $address->longitude, 12 );
				$address->visibility = cnSanitize::field( 'attribute', $address->visibility, $context );

				/*
				 * Set the address name based on the address type.
				 */
				$address->name = ( ! isset( $addressTypes[ $address->type ] ) || $addressTypes[ $address->type ] === 'Select' ) ? 'Other' : $addressTypes[ $address->type ];

				/*
				 * // START -- Compatibility for previous versions.
				 */
				$address->line_one   =& $address->line_1;
				$address->line_two   =& $address->line_2;
				$address->line_three =& $address->line_3;
				/*
				 * // END -- Compatibility for previous versions.
				 */

				/**
				 * This filter is documented in @see cnEntry::getAddresses().
				 */
				$results[] = apply_filters( 'cn_address', $address );
			}

		}

		/**
		 * An index array of address objects.
		 *
		 * @since unknown
		 *
		 * @param array $results. See the documentation for the `cn_address` filter for the params of each item in the
		 *                        addresses array.
		 */
		return apply_filters( 'cn_addresses', $results );
	}

	/**
	 * Caches the addresses for use and preps for saving and updating.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array  $addresses {
	 *     @type int    $id         The address ID if it was retrieved from the db.
	 *     @type bool   $preferred  Whether the address is the preferred address or not.
	 *     @type string $type       The address type.
	 *     @type string $line_1     Address line 1.
	 *     @type string $line_2     Address line 2.
	 *     @type string $line_3     Address line 3.
	 *     @type string $city       The address locality.
	 *     @type string $state      The address region.
	 *     @type string $country    The address country.
	 *     @type float  $latitude   The address latitude.
	 *     @type float  $longitude  The address longitude.
	 *     @type string $visibility The address visibility.
	 * }
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return void
	 */
	public function setAddresses( $addresses, $context = 'db' ) {

		$userPreferred = NULL;

		$validFields = array(
			'id'         => NULL,
			'preferred'  => NULL,
			'type'       => NULL,
			'line_1'     => NULL,
			'line_2'     => NULL,
			'line_3'     => NULL,
			'city'       => NULL,
			'state'      => NULL,
			'zipcode'    => NULL,
			'country'    => NULL,
			'latitude'   => NULL,
			'longitude'  => NULL,
			'visibility' => NULL
		);

		if ( ! empty( $addresses ) ) {

			//print_r($addresses);
			$order = 0;
			$preferred = '';

			if ( isset( $addresses['preferred'] ) ) {
				$preferred = $addresses['preferred'];
				unset( $addresses['preferred'] );
			}

			foreach ( $addresses as $key => $address ) {

				// Permit only the valid fields.
				$addresses[ $key ] = cnSanitize::args( $address, $validFields );

				// Store the order attribute as supplied in the addresses array.
				$addresses[ $key ]['order'] = $order;

				$addresses[ $key ]['preferred'] = isset( $preferred ) && $preferred == $key ? TRUE : FALSE;

				/*
				 * If the user set a preferred address, save the $key value.
				 * This is going to be needed because if an address that the user
				 * does not have permission to edit is set to preferred, that address
				 * will have preference.
				 */
				if ( $addresses[ $key ]['preferred'] ) $userPreferred = $key;

				$addresses[ $key ] = apply_filters( 'cn_set_address', $addresses[ $key ] );

				$order++;
			}
		}

		/*
		 * Before storing the data, add back into the array from the cache the addresses
		 * the user may not have had permission to edit so the cache stays current.
		 */
		$cached = unserialize( $this->addresses );

		if ( ! empty( $cached ) ) {

			foreach ( $cached as $address ) {

				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( ! isset( $address['visibility'] ) || empty( $address['visibility'] ) ) $address['visibility'] = 'public';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				if ( ! $this->validate->userPermitted( $address['visibility'] ) ) {

					$addresses[] = $address;

					// If the address is preferred, it takes precedence, the user's choice is overridden.
					if ( ! empty( $preferred ) && $address['preferred'] ) {

						$addresses[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						cnMessage::set( 'error', 'entry_preferred_overridden_address' );
					}
				}
			}
		}

		$this->addresses = ! empty( $addresses ) ? serialize( apply_filters( 'cn_set_addresses', $addresses ) ) : NULL;
	}

	/**
	 * Returns as an array of objects containing the phone numbers per the defined options for the current entry.
	 *
	 * Accepted options for the $atts property are:
	 * preferred (bool) Retrieve the preferred entry phone number.
	 *  type (array) || (string) Retrieve specific phone number types.
	 *   Permitted Types:
	 *    homephone
	 *    homefax
	 *    cellphone
	 *    workphone
	 *    workfax
	 *
	 * Filters:
	 *  cn_phone_atts => (array) Set the method attributes.
	 *  cn_phone_cached => (bool) Define if the returned phone numbers should be from the object cache or queried from the db.
	 *  cn_phone_number => (object) Individual phone number as it is processed thru the loop.
	 *  cn_phone_numbers => (array) All phone numbers before it is returned.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached phone numbers data rather than querying the db.
	 * @param bool    $saving       Set as TRUE if adding a new entry or updating an existing entry.
	 * @return array
	 */
	public function getPhoneNumbers( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		/**
		 * @var connectionsLoad $connections
		 */
		global $connections;

		$phoneTypes = $connections->options->getDefaultPhoneNumberValues();
		$results = array();

		$atts = apply_filters( 'cn_phone_atts', $atts );
		$cached = apply_filters( 'cn_phone_cached', $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			'limit'     => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->phoneNumbers ) ) {

				$phoneNumbers = unserialize( $this->phoneNumbers );
				if ( empty( $phoneNumbers ) ) return $results;

				/**
				 * @var bool   $preferred
				 * @var string $type
				 */
				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				cnFunction::parseStringList( $type );

				foreach ( (array) $phoneNumbers as $key => $number ) {
					/*
					 * Previous versions stored empty arrays for phone numbers, check for a number, continue if not found.
					 */
					if ( ! isset( $number['number'] ) || empty( $number['number'] ) ) continue;

					$row = new stdClass();

					( isset( $number['id'] ) ) ? $row->id = (int) $number['id'] : $row->id = 0;
					( isset( $number['order'] ) ) ? $row->order = (int) $number['order'] : $row->order = 0;
					( isset( $number['preferred'] ) ) ? $row->preferred = (bool) $number['preferred'] : $row->preferred = FALSE;
					( isset( $number['type'] ) ) ? $row->type = $this->format->sanitizeString( $number['type'] ) : $row->type = 'homephone';
					( isset( $number['number'] ) ) ? $row->number = $this->format->sanitizeString( $number['number'] ) : $row->number = '';
					( isset( $number['visibility'] ) ) ? $row->visibility = $this->format->sanitizeString( $number['visibility'] ) : $row->visibility = '';

					/*
					 * // START -- Compatibility for previous versions.
					 */
					switch ( $row->type ) {
						case 'home':
							$row->type = 'homephone';
							break;
						case 'cell':
							$row->type = 'cellphone';
							break;
						case 'work':
							$row->type = 'workphone';
							break;
						case 'fax':
							$row->type = 'workfax';
							break;
					}

					if ( ! isset( $number['visibility'] ) || empty( $number['visibility'] ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */

					/*
					 * Set the phone name based on the type.
					 */
					$row->name = ! isset( $phoneTypes[ $row->type ] )  ? 'Other' : $phoneTypes[ $row->type ];

					/*
					 * // START -- Do not return phone numbers that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					/*
					 * // END -- Do not return phone numbers that do not match the supplied $atts.
					 */

					// If the user does not have permission to view the address, do not return it.
					if ( ! $this->validate->userPermitted( $row->visibility ) && ! $saving ) continue;

					$results[] = apply_filters( 'cn_phone_number', $row );
				}

				/*
				 * Limit the number of results.
				 */
				if ( ! is_null( $atts['limit'] ) && 1 < count( $results ) ) {

					$results = array_slice( $results, 0, absint( $atts['limit'] ) );
				}

			}

		} else {
			// Exit right away and return an empty array if the entry ID has not been set otherwise all phone numbers will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$phoneNumbers = $connections->retrieve->phoneNumbers( $atts );

			if ( empty( $phoneNumbers ) ) return $results;

			foreach ( $phoneNumbers as $phone ) {
				$phone->id = (int) $phone->id;
				$phone->order = (int) $phone->order;
				$phone->preferred = (bool) $phone->preferred;
				$phone->type = $this->format->sanitizeString( $phone->type );
				$phone->number = $this->format->sanitizeString( $phone->number );
				$phone->visibility = $this->format->sanitizeString( $phone->visibility );

				/*
				 * // START -- Compatibility for previous versions.
				 */
				switch ( $phone->type ) {
					case 'home':
						$phone->type = "homephone";
						break;
					case 'cell':
						$phone->type = "cellphone";
						break;
					case 'work':
						$phone->type = "workphone";
						break;
					case 'fax':
						$phone->type = "workfax";
						break;
				}
				/*
				 * // END -- Compatibility for previous versions.
				 */

				/*
				 * Set the phone name based on the phone type.
				 */
				$phone->name = ! isset( $phoneTypes[ $phone->type ] )  ? 'Other' : $phoneTypes[ $phone->type ];

				$results[] = apply_filters( 'cn_phone_number', $phone );
			}

		}

		return apply_filters( 'cn_phone_numbers', $results );
	}

	/**
	 * Caches the phone numbers for use and preps for saving and updating.
	 *
	 * Valid values as follows.
	 *
	 * $phoneNumber['id'] (int) Stores the phone number ID if it was retrieved from the db.
	 * $phoneNumber['preferred'] (bool) If the phone number is the number or not.
	 * $phoneNumber['type'] (string) Stores the phone number type.
	 * $phoneNumber['number'] (string) Stores phone number.
	 * $phoneNumber['visibility'] (string) Stores the phone number visibility.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $phoneNumbers
	 * @return void
	 */
	public function setPhoneNumbers( $phoneNumbers ) {

		$userPreferred = NULL;

		$validFields = array( 'id' => NULL, 'preferred' => NULL, 'type' => NULL, 'number' => NULL, 'visibility' => NULL );

		if ( ! empty( $phoneNumbers ) ) {
			$order = 0;
			$preferred = '';

			if ( isset( $phoneNumbers['preferred'] ) ) {
				$preferred = $phoneNumbers['preferred'];
				unset( $phoneNumbers['preferred'] );
			}

			foreach ( $phoneNumbers as $key => $phoneNumber ) {

				// First validate the supplied data.
				$phoneNumber = cnSanitize::args( $phoneNumber, $validFields );

				// If the number is empty, no need to store it.
				if ( empty( $phoneNumber['number'] ) ) {
					unset( $phoneNumbers[ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the addresses array.
				$phoneNumbers[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $phoneNumbers[ $key ]['preferred'] = TRUE : $phoneNumbers[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a preferred number, save the $key value.
				 * This is going to be needed because if a number that the user
				 * does not have permission to edit is set to preferred, that number
				 * will have preference.
				 */
				if ( $phoneNumbers[ $key ]['preferred'] ) $userPreferred = $key;

				$order++;
			}
		}

		/*
		 * Before storing the data, add back into the array from the cache the phone numbers
		 * the user may not have had permission to edit so the cache stays current.
		 */
		$cached = unserialize( $this->phoneNumbers );

		if ( ! empty( $cached ) ) {
			foreach ( $cached as $phone ) {
				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( ! isset( $phone['visibility'] ) || empty( $phone['visibility'] ) ) $phone['visibility'] = 'public';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				if ( ! $this->validate->userPermitted( $phone['visibility'] ) ) {
					$phoneNumbers[] = $phone;

					// If the number is preferred, it takes precedence, so the user's choice is overridden.
					if ( ! empty( $preferred ) && $phone['preferred'] ) {
						$phoneNumbers[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						cnMessage::set( 'error', 'entry_preferred_overridden_phone' );
					}
				}
			}
		}

		$this->phoneNumbers = ! empty( $phoneNumbers ) ? serialize( $phoneNumbers ) : NULL;
	}

	/**
	 * Returns as an array of objects containing the email addresses per the defined options for the current entry.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry email addresses.
	 *  type (array) || (string) Retrieve specific email addresses types.
	 *   Permitted Types:
	 *    personal
	 *    work
	 *
	 * Filters:
	 *  cn_email_atts => (array) Set the method attributes.
	 *  cn_email_cached => (bool) Define if the returned email addresses should be from the object cache or queried from the db.
	 *  cn_email_address => (object) Individual email address as it is processed thru the loop.
	 *  cn_email_addresses => (array) All phone numbers before it is returned.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached email addresses data rather than querying the db.
	 * @param bool    $saving       Set as TRUE if adding a new entry or updating an existing entry.
	 * @return array
	 */
	public function getEmailAddresses( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		/**
		 * @var connectionsLoad $connections
		 */
		global $connections;

		$results = array();

		$atts = apply_filters( 'cn_email_atts', $atts );
		$cached = apply_filters( 'cn_email_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			'limit'     => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->emailAddresses ) ) {

				$emailAddresses = unserialize( $this->emailAddresses );
				if ( empty( $emailAddresses ) ) return $results;

				/**
				 * @var bool   $preferred
				 * @var string $type
				 */
				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				cnFunction::parseStringList( $type );

				foreach ( (array) $emailAddresses as $key => $email ) {

					/*
					 * Previous versions stored empty arrays for email addresses, check for an address, continue if not found.
					 */
					if ( ! isset( $email['address'] ) || empty( $email['address'] ) ) continue;

					$row = new stdClass();

					( isset( $email['id'] ) ) ? $row->id = (int) $email['id'] : $row->id = 0;
					( isset( $email['order'] ) ) ? $row->order = (int) $email['order'] : $row->order = 0;
					( isset( $email['preferred'] ) ) ? $row->preferred = (bool) $email['preferred'] : $row->preferred = FALSE;
					( isset( $email['type'] ) ) ? $row->type = $this->format->sanitizeString( $email['type'] ) : $row->type = '';
					( isset( $email['address'] ) ) ? $row->address = $this->format->sanitizeString( $email['address'] ) : $row->address = '';
					( isset( $email['visibility'] ) ) ? $row->visibility = $this->format->sanitizeString( $email['visibility'] ) : $row->visibility = '';

					/*
					 * Set the email name based on type.
					 */
					$emailTypes = $connections->options->getDefaultEmailValues();
					$row->name = $emailTypes[ $row->type ];

					/*
					 * // START -- Compatibility for previous versions.
					 */
					// Versions prior to 0.7.1.6 may not have visibility set, so we'll assume it was 'public' since it wasn't an option before.
					if ( ! isset( $email['visibility'] ) || empty( $email['visibility'] ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */

					/*
					 * // START -- Do not return email addresses that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					/*
					 * // END -- Do not return email addresses that do not match the supplied $atts.
					 */

					// If the user does not have permission to view the address, do not return it.
					if ( ! $this->validate->userPermitted( $row->visibility ) && ! $saving ) continue;

					$results[] = apply_filters( 'cn_email_address', $row );
				}

				/*
				 * Limit the number of results.
				 */
				if ( ! is_null( $atts['limit'] ) && 1 < count( $results ) ) {

					$results = array_slice( $results, 0, absint( $atts['limit'] ) );
				}

			}

		} else {

			// Exit right away and return an empty array if the entry ID has not been set otherwise all email addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$emailAddresses = $connections->retrieve->emailAddresses( $atts );
			//print_r($results);

			if ( empty( $emailAddresses ) ) return $results;

			foreach ( $emailAddresses as $email ) {

				$email->id = (int) $email->id;
				$email->order = (int) $email->order;
				$email->preferred = (bool) $email->preferred;
				$email->type = $this->format->sanitizeString( $email->type );
				$email->address = $this->format->sanitizeString( $email->address );
				$email->visibility = $this->format->sanitizeString( $email->visibility );

				/*
				 * Set the email name based on the email type.
				 */
				$emailTypes = $connections->options->getDefaultEmailValues();
				$email->name = $emailTypes[ $email->type ];

				$results[] = apply_filters( 'cn_email_address', $email );
			}

		}

		return apply_filters( 'cn_email_addresses', $results );
	}

	/**
	 * Caches the email addresses for use and preps for saving and updating.
	 *
	 * Valid values as follows.
	 *
	 * $email['id'] (int) Stores the email address ID if it was retrieved from the db.
	 * $email['preferred'] (bool) Is the email address is the preferred address or not.
	 * $email['type'] (string) Stores the email address type.
	 * $email['address'] (string) Stores email address.
	 * $email['visibility'] (string) Stores the email address visibility.
	 *
	 * @TODO: Validate as valid email address.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $emailAddresses
	 * @return void
	 */
	public function setEmailAddresses( $emailAddresses ) {

		$userPreferred = NULL;
		$validFields = array( 'id' => NULL, 'preferred' => NULL, 'type' => NULL, 'address' => NULL, 'visibility' => NULL );

		if ( ! empty( $emailAddresses ) ) {

			$order = 0;
			$preferred = '';

			if ( isset( $emailAddresses['preferred'] ) ) {
				$preferred = $emailAddresses['preferred'];
				unset( $emailAddresses['preferred'] );
			}

			foreach ( $emailAddresses as $key => $email ) {

				// First validate the supplied data.
				$email = cnSanitize::args( $email, $validFields );

				// If the address is empty, no need to store it.
				if ( empty( $email['address'] ) ) {
					unset( $emailAddresses [ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the addresses array.
				$emailAddresses[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $emailAddresses[ $key ]['preferred'] = TRUE : $emailAddresses[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a preferred address, save the $key value.
				 * This is going to be needed because if an address that the user
				 * does not have permission to edit is set to preferred, that address
				 * will have preference.
				 */
				if ( $emailAddresses[ $key ]['preferred'] ) $userPreferred = $key;

				$order++;
			}
		}

		/*
		 * Before storing the data, add back into the array from the cache the email addresses
		 * the user may not have had permission to edit so the cache stays current.
		 */
		$cached = unserialize( $this->emailAddresses );

		if ( ! empty( $cached ) ) {
			foreach ( $cached as $email ) {
				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( ! isset( $email['visibility'] ) || empty( $email['visibility'] ) ) $email['visibility'] = 'public';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				if ( ! $this->validate->userPermitted( $email['visibility'] ) ) {
					$emailAddresses[] = $email;

					// If the address is preferred, it takes precedence, so the user's choice is overridden.
					if ( ! empty( $preferred ) && $email['preferred'] ) {
						$emailAddresses[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						cnMessage::set( 'error', 'entry_preferred_overridden_email' );
					}
				}
			}
		}

		( ! empty( $emailAddresses ) ) ? $this->emailAddresses = serialize( $emailAddresses ) : $this->emailAddresses = NULL;
	}

	/**
	 * Returns as an array of objects containing the IM IDs per the defined options for the current entry.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry IM IDs.
	 *  type (array) || (string) Retrieve specific IM types[network].
	 *   Permitted Types:
	 *    aim
	 *    yahoo
	 *    jabber
	 *    messenger
	 *    skype
	 *
	 * Filters:
	 *  cn_messenger_atts => (array) Set the method attributes.
	 *  cn_messenger_cached => (bool) Define if the returned email addresses should be from the object cache or queried from the db.
	 *  cn_messenger_id => (object) Individual email address as it is processed thru the loop.
	 *  cn_messenger_ids => (array) All phone numbers before it is returned.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $atts         Accepted values as noted above.
	 * @param bool    $cached       Returns the cached email addresses data rather than querying the db.
	 * @param bool    $saving       Whether or no the data is being saved to the db.
	 *
	 * @return array
	 */
	public function getIm( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		global $connections;

		$results = array();

		$atts = apply_filters( 'cn_messenger_atts', $atts );
		$cached = apply_filters( 'cn_messenger_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->im ) ) {

				$networks = unserialize( $this->im );

				if ( empty( $networks ) ) return $results;

				/**
				 * @var bool   $preferred
				 * @var string $type
				 */
				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				cnFunction::parseStringList( $type );

				foreach ( (array) $networks as $key => $network ) {

					/*
					 * Previous versions stored empty arrays for IM IDs, check for an ID, continue if not found.
					 */
					if ( ! isset( $network['id'] ) || empty( $network['id'] ) ) continue;

					$row = new stdClass();

					// This stores the table `id` value.
					( isset( $network['uid'] ) ) ? $row->uid = (int) $network['uid'] : $row->uid = 0;

					( isset( $network['order'] ) ) ? $row->order = (int) $network['order'] : $row->order = 0;
					( isset( $network['preferred'] ) ) ? $row->preferred = (bool) $network['preferred'] : $row->preferred = FALSE;
					( isset( $network['type'] ) ) ? $row->type = $this->format->sanitizeString( $network['type'] ) : $row->type = '';

					// Unlike the other entry contact details, this actually stores the user IM id and not the table `id` value.
					( isset( $network['id'] ) ) ? $row->id = $this->format->sanitizeString( $network['id'] ) : $row->id = 0;

					( isset( $network['visibility'] ) ) ? $row->visibility = $this->format->sanitizeString( $network['visibility'] ) : $row->visibility = '';

					/*
					 * Set the IM name based on type.
					 */
					$imTypes = $connections->options->getDefaultIMValues();
					$row->name = $imTypes[ $row->type ];

					/*
					 * // START -- Compatibility for previous versions.
					 */
					switch ( $row->type ) {
						case 'AIM':
							$row->type = 'aim';
							break;
						case 'Yahoo IM':
							$row->type = 'yahoo';
							break;
						case 'Jabber / Google Talk':
							$row->type = 'jabber';
							break;
						case 'Messenger':
							$row->type = 'messenger';
							break;
					}

					// Versions prior to 0.7.1.6 may not have visibility set, so we'll assume it was 'public' since it wasn't the option before.
					if ( ! isset( $network['visibility'] ) || empty( $network['visibility'] ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */

					/*
					 * // START -- Do not return IM IDs that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					/*
					 * // END -- Do not return IM IDs that do not match the supplied $atts.
					 */

					// If the user does not have permission to view the IM ID, do not return it.
					if ( ! $this->validate->userPermitted( $row->visibility ) && ! $saving ) continue;

					$results[] = apply_filters( 'cn_messenger_id', $row );
				}

			}

		} else {

			// Exit right away and return an empty array if the entry ID has not been set otherwise all email addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$imIDs = $connections->retrieve->imIDs( $atts );
			//print_r($results);

			if ( empty( $imIDs ) ) return $results;

			foreach ( $imIDs as $network ) {

				/*
				 * This will probably forever give me headaches,
				 * Previous versions stored the IM ID as id. Now that the data
				 * is stored in a separate table, id is now the unique table `id`
				 * and uid is the IM ID.
				 *
				 * So I have to make sure to properly map the values. Unfortunately
				 * this differs from the rest of the entry data is where `id` equals
				 * the unique table `id`. So lets map the table `id` to uid and the
				 * the table `uid` to id.
				 *
				 * Basically swapping the values. This should maintain compatibility
				 * with previous versions.
				 */
				$userID = $this->format->sanitizeString( $network->uid );
				$uniqueID = (int) $network->id;

				$network->uid = $uniqueID;
				$network->order = (int) $network->order;
				$network->preferred = (bool) $network->preferred;
				$network->type = $this->format->sanitizeString( $network->type );
				$network->id = $userID;
				$network->visibility = $this->format->sanitizeString( $network->visibility );

				/*
				 * Set the network name based on the network type.
				 */
				$imTypes = $connections->options->getDefaultIMValues();
				$network->name = $imTypes[ $network->type ];

				$results[] = apply_filters( 'cn_messenger_id', $network );
			}

		}

		return apply_filters( 'cn_messenger_ids', $results );
	}

	/**
	 * Caches the IM IDs for use and preps for saving and updating.
	 *
	 * Valid values as follows.
	 *
	 * $network['uid'] (int) Stores the network ID if it was retrieved from the db.
	 * $network['preferred'] (bool) If the network is the preferred network or not.
	 * $network['type'] (string) Stores the network type.
	 * $network['id'] (string) Stores network URL.
	 * $network['visibility'] (string) Stores the network visibility.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $im
	 * @return void
	 */
	public function setIm( $im ) {

		$userPreferred = NULL;

		$validFields = array( 'uid' => NULL, 'preferred' => NULL, 'type' => NULL, 'id' => NULL, 'visibility' => NULL );

		if ( ! empty( $im ) ) {

			$order = 0;
			$preferred = '';

			if ( isset( $im['preferred'] ) ) {
				$preferred = $im['preferred'];
				unset( $im['preferred'] );
			}

			foreach ( $im as $key => $network ) {

				// First validate the supplied data.
				$network = cnSanitize::args( $network, $validFields );

				// If the id is empty, no need to store it.
				if ( empty( $network['id'] ) ) {
					unset( $im[ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the addresses array.
				$im[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $im[ $key ]['preferred'] = TRUE : $im[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a preferred network, save the $key value.
				 * This is going to be needed because if a network that the user
				 * does not have permission to edit is set to preferred that network
				 * will have preference.
				 */
				if ( $im[ $key ]['preferred'] ) $userPreferred = $key;

				$order++;
			}
		}

		/*
		 * Before storing the data, add back into the array from the cache the networks
		 * the user may not have had permission to edit so the cache stays current.
		 */
		$cached = unserialize( $this->im );

		if ( ! empty( $cached ) ) {

			foreach ( $cached as $network ) {

				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( ! isset( $network['visibility'] ) || empty( $network['visibility'] ) ) $network['visibility'] = 'public';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				if ( ! $this->validate->userPermitted( $network['visibility'] ) ) {

					$im[] = $network;

					// If the network is preferred, it takes precedence, so the user's choice is overridden.
					if ( ! empty( $preferred ) && $network['preferred'] ) {

						$im[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						cnMessage::set( 'error', 'entry_preferred_overridden_im' );
					}
				}
			}
		}

		$this->im = ! empty( $im ) ? serialize( $im ) : NULL;
	}

	/**
	 * Returns as an array of objects containing the social medial URLs per the defined options for the current entry.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry social medial URLs.
	 *  type (array) || (string) Retrieve specific social medial URLs types[network].
	 *   Permitted Types:
	 *    delicious
	 *    cdbaby
	 *    facebook
	 *    flickr
	 *    itunes
	 *    linked-in
	 *    mixcloud
	 *    myspace
	 *    podcast
	 *    reverbnation
	 *    rss
	 *    technorati
	 *    twitter
	 *    soundcloud
	 *    youtube
	 *
	 * Filters:
	 *  cn_social_network_atts => (array) Set the method attributes.
	 *  cn_social_network_cached => (bool) Define if the returned email addresses should be from the object cache or queried from the db.
	 *  cn_social_network => (object) Individual email address as it is processed thru the loop.
	 *  cn_social_networks => (array) All phone numbers before it is returned.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $atts         Accepted values as noted above.
	 * @param bool    $cached       Returns the cached social medial URLs data rather than querying the db.
	 * @param bool    $saving       Whether or no the data is being saved to the db.
	 *
	 * @return array
	 */
	public function getSocialMedia( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		/**
		 * @var connectionsLoad $connections
		 */
		global $connections;

		$results = array();

		$atts = apply_filters( 'cn_social_network_atts', $atts );
		$cached = apply_filters( 'cn_social_network_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->socialMedia ) ) {

				$networks = unserialize( $this->socialMedia );
				if ( empty( $networks ) ) return $results;

				/**
				 * @var bool   $preferred
				 * @var string $type
				 */
				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				cnFunction::parseStringList( $type );

				foreach ( (array) $networks as $key => $network ) {
					/*
					 * Previous versions stored empty arrays for the URL, check for the URL, continue if not found.
					 */
					if ( ! isset( $network['url'] ) || empty( $network['url'] ) ) continue;

					$row = new stdClass();

					( isset( $network['id'] ) ) ? $row->id = (int) $network['id'] : $row->id = 0;
					( isset( $network['order'] ) ) ? $row->order = (int) $network['order'] : $row->order = 0;
					( isset( $network['preferred'] ) ) ? $row->preferred = (bool) $network['preferred'] : $row->preferred = FALSE;
					( isset( $network['type'] ) ) ? $row->type = $this->format->sanitizeString( $network['type'] ) : $row->type = '';
					( isset( $network['url'] ) ) ? $row->url = $this->format->sanitizeString( $network['url'] ) : $row->url = '';
					( isset( $network['visibility'] ) ) ? $row->visibility = $this->format->sanitizeString( $network['visibility'] ) : $row->visibility = '';

					/*
					 * Set the social network name based on type.
					 */
					$socialTypes = $connections->options->getDefaultSocialMediaValues();
					$row->name = $socialTypes[ $row->type ];

					/*
					 * // START -- Compatibility for previous versions.
					 */
					// Versions prior to 0.7.1.6 may not have visibility set, so we'll assume it was 'public' since it wasn't the option before.
					if ( ! isset( $network['visibility'] ) || empty( $network['visibility'] ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */

					/*
					 * // START -- Do not return social networks that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					/*
					 * // END -- Do not return social networks that do not match the supplied $atts.
					 */

					// If the user does not have permission to view the social network, do not return it.
					if ( ! $this->validate->userPermitted( $row->visibility ) && ! $saving ) continue;

					$results[] = apply_filters( 'cn_social_network', $row );
				}

			}

		} else {

			// Exit right away and return an empty array if the entry ID has not been set otherwise all email addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$socialMedia = $connections->retrieve->socialMedia( $atts );

			if ( empty( $socialMedia ) ) return $results;

			foreach ( $socialMedia as $network ) {

				$network->id = (int) $network->id;
				$network->order = (int) $network->order;
				$network->preferred = (bool) $network->preferred;
				$network->type = $this->format->sanitizeString( $network->type );
				$network->url = $this->format->sanitizeString( $network->url );
				$network->visibility = $this->format->sanitizeString( $network->visibility );

				/*
				 * Set the social network name based on the network type.
				 */
				$networkTypes = $connections->options->getDefaultSocialMediaValues();
				$network->name = $networkTypes [ $network->type ];

				$results[] = apply_filters( 'cn_social_network', $network );
			}

		}

		return apply_filters( 'cn_social_networks', $results );
	}

	/**
	 * Caches the social networks for use and preps for saving and updating.
	 *
	 * Valid values as follows.
	 *
	 * $network['id'] (int) Stores the network ID if it was retrieved from the db.
	 * $network['preferred'] (bool) If the network is the preferred network or not.
	 * $network['type'] (string) Stores the network type.
	 * $network['url'] (string) Stores network URL.
	 * $network['visibility'] (string) Stores the network visibility.
	 *
	 * @TODO: Validate as valid url.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $socialNetworks
	 * @return void
	 */
	public function setSocialMedia( $socialNetworks ) {

		$userPreferred = NULL;

		$validFields = array( 'id' => NULL, 'preferred' => NULL, 'type' => NULL, 'url' => NULL, 'visibility' => NULL );

		if ( ! empty( $socialNetworks ) ) {

			$order = 0;
			$preferred = '';

			if ( isset( $socialNetworks['preferred'] ) ) {
				$preferred = $socialNetworks['preferred'];
				unset( $socialNetworks['preferred'] );
			}

			foreach ( $socialNetworks as $key => $network ) {

				// First validate the supplied data.
				$network = cnSanitize::args( $network, $validFields );

				// If the URL is empty, no need to save it.
				if ( empty( $network['url'] ) || 'http://' == $network['url'] ) {
					unset( $socialNetworks[ $key ] );
					continue;
				}

				// If the http protocol is not part of the url, add it.
				$socialNetworks[ $key ]['url'] = cnURL::prefix( $network['url'] );

				// Store the order attribute as supplied in the addresses array.
				$socialNetworks[ $key ]['order'] = $order;

				( ( ! empty( $preferred ) ) && $preferred == $key ) ? $socialNetworks[ $key ]['preferred'] = TRUE : $socialNetworks[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a preferred network, save the $key value.
				 * This is going to be needed because if a network that the user
				 * does not have permission to edit is set to preferred, that network
				 * will have preference.
				 */
				if ( $socialNetworks[ $key ]['preferred'] ) $userPreferred = $key;

				$order++;

			}
		}

		/*
		 * Before storing the data, add back into the array from the cache the networks
		 * the user may not have had permission to edit so the cache stays current.
		 */
		$cached = unserialize( $this->socialMedia );

		if ( ! empty( $cached ) ) {

			foreach ( $cached as $network ) {

				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( ! isset( $network['visibility'] ) || empty( $network['visibility'] ) ) $network['visibility'] = 'public';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				// Add back to the data array the networks that user does not have permission to view and edit.
				if ( ! $this->validate->userPermitted( $network['visibility'] ) ) {

					$socialNetworks[] = $network;

					// If the network is preferred, it takes precedence, so the user's choice is overridden.
					if ( ! empty( $preferred ) && $network['preferred'] ) {

						$socialNetworks[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						cnMessage::set( 'error', 'entry_preferred_overridden_social' );
					}
				}
			}
		}

		$this->socialMedia = ! empty( $socialNetworks ) ? serialize( $socialNetworks ) : NULL;
	}

	/**
	 * Return an array of objects containing the links per the defined options for the current entry.
	 *
	 * Filters:
	 *  cn_link_atts => (array) Set the method attributes.
	 *  cn_link_cached => (bool) Define if the returned email addresses should be from the object cache or queried from the db.
	 *  cn_link => (object) Individual email address as it is processed thru the loop.
	 *  cn_links => (array) All phone numbers before it is returned.
	 *
	 * @access  public
	 * @since   0.7.3
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type bool  $preferred Return only the link set as the preferred link.
	 *                            Default: FALSE
	 *     @type array $type      An indexed array to define which link types to return.
	 *                            Default: array() (All link types.)
	 *                            Accepts: Array keys of @see cnOptions::getDefaultLinkValues()
	 *     @type bool  $image     Return only the link that was assigned to the image.
	 *                            Default: FALSE
	 *     @type bool  $logo      Return only the link that was assigned to the logo.
	 *                            Default: FALSE
	 * }
	 * @param bool  $cached Returns the cached link data rather than querying the db.
	 * @param bool  $saving Whether or no the data is being saved to the db.
	 *
	 * @return array
	 */
	public function getLinks( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$results = array();

		$atts   = apply_filters( 'cn_link_atts', $atts );
		$cached = apply_filters( 'cn_link_cached' , $cached );

		$types  = $instance->options->getDefaultLinkValues();

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred' => FALSE,
			'type'      => array_keys( $types ),
			'image'     => FALSE,
			'logo'      => FALSE,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->links ) ) {

				$links = unserialize( $this->links );
				if ( empty( $links ) ) return $results;

				/**
				 * @var bool   $preferred
				 * @var string $type
				 * @var bool   $image
				 * @var bool   $logo
				 */
				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				cnFunction::parseStringList( $type );

				foreach ( (array) $links as $key => $link ) {

					$row = new stdClass();

					$row->id         = isset( $link['id'] ) ? (int) $link['id'] : 0;
					$row->order      = isset( $link['order'] ) ? (int) $link['order'] : 0;
					$row->preferred  = isset( $link['preferred'] ) ? (bool) $link['preferred'] : FALSE;
					$row->type       = isset( $link['type'] ) ? $this->format->sanitizeString( $link['type'] ) : 'website';
					$row->title      = isset( $link['title'] ) ? $this->format->sanitizeString( $link['title'] ) : '';
					$row->address    = isset( $link['address'] ) ? cnSanitize::field( 'url', $link['address'], 'raw' ) : '';
					$row->url        = isset( $link['url'] ) ? cnSanitize::field( 'url', $link['url'], 'raw' ) :'';
					$row->target     = isset( $link['target'] ) ? $this->format->sanitizeString( $link['target'] ) : 'same';
					$row->follow     = isset( $link['follow'] ) ? (bool) $link['follow'] : FALSE;
					$row->image      = isset( $link['image'] ) ? (bool) $link['image'] : FALSE;
					$row->logo       = isset( $link['logo'] ) ? (bool) $link['logo'] : FALSE;
					$row->visibility = isset( $link['visibility'] ) ? $this->format->sanitizeString( $link['visibility'] ) : 'public';

					/*
					 * Set the Link name based on type.
					 */
					$row->name = empty( $row->type ) ? $types['website'] : $types[ $row->type ];
					//var_dump($row->type);

					/*
					 * // START -- Compatibility for previous versions.
					 */
					if ( empty( $row->url ) ) $row->url         =& $row->address;
					if ( empty( $row->address ) ) $row->address =& $row->url;
					if ( empty( $row->title ) ) $row->title     = $row->address;
					if ( empty( $row->name ) ) $row->name       = 'Website';

					// Versions prior to 0.7.1.6 may not have visibility set, so we'll assume it was 'public' since it wasn't the option before.
					if ( empty( $row->visibility ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */

					/*
					 * Set the dofollow/nofollow string based on the bool value.
					 */
					$row->followString = $row->follow ? 'dofollow' : 'nofollow';

					/*
					 * // START -- Do not return links that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					if ( $image && ! $row->image ) continue;
					if ( $logo && ! $row->logo ) continue;
					/*
					 * // END -- Do not return links that do not match the supplied $atts.
					 */

					// If the user does not have permission to view the link, do not return it.
					if ( ! cnValidate::userPermitted( $row->visibility ) && ! $saving ) continue;

					$results[] = apply_filters( 'cn_link', $row );
				}

			}

		} else {

			// Exit right away and return an empty array if the entry ID has not been set otherwise all email addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$links = $instance->retrieve->links( $atts );
			//print_r($results);

			if ( empty( $links ) ) return $results;

			foreach ( $links as $link ) {

				$link->id         = (int) $link->id;
				$link->order      = (int) $link->order;
				$link->preferred  = (bool) $link->preferred;
				$link->type       = $this->format->sanitizeString( $link->type );
				$link->title      = $this->format->sanitizeString( $link->title );
				$link->url        = cnSanitize::field( 'url', $link->url, 'raw' );
				$link->target     = $this->format->sanitizeString( $link->target );
				$link->follow     = (bool) $link->follow;
				$link->image      = (bool) $link->image;
				$link->logo       = (bool) $link->logo;
				$link->visibility = $this->format->sanitizeString( $link->visibility );

				/*
				 * Set the link name based on the link type.
				 */
				$link->name = $types[ $link->type ];

				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( empty( $link->title ) ) $link->title = $link->url;
				$link->address =& $link->url;
				if ( empty( $link->name ) ) $link->name = 'Website';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				/*
				 * Set the dofollow/nofollow string based on the bool value.
				 */
				$link->followString = $link->follow ? 'dofollow' : 'nofollow';

				$results[] = apply_filters( 'cn_link', $link );
			}

		}

		return apply_filters( 'cn_links', $results );
	}

	/**
	 * Returns as an array of objects containing the websites per the defined options for the current entry.
	 *
	 * $atts['preferred'] (bool) Retrieve the preferred website.
	 *
	 * @deprecated since 0.7.2.0
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached social medial URLs data rather than querying the db.
	 * @return array
	 */
	public function getWebsites( $atts = array(), $cached = TRUE ) {

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred' => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		$atts['type'] = array( 'personal', 'website' ); // The 'personal' type is provided for legacy support. Versions 0.7.1.6 an older.
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		return $this->getLinks( $atts, $cached );
	}

	/**
	 * Caches the links for use and preps for saving and updating.
	 *
	 * @todo Validate as valid web addresses.
	 *
	 * @access  public
	 * @since   0.7.3
	 *
	 * @param   array $links {
	 *     Optional. An array of arguments.
	 *
	 *     @type int    $id         The unique link ID as queried from the DB.
	 *     @type mixed  $preferred  The array key of the link to be set as the preferred link.
	 *                              Default: ''
	 *     @type string $type       The link type.
	 *                              Default: @todo Should have a default if one is not supplied.
	 *                              Accepts: The array keys returned from @see cnOptions::getDefaultLinkValues()
	 *     @type string $title      The link text (also used for the link title attribute).
	 *                              Default: ''
	 *     @type string $url        The link URL.
	 *     @type string $target     The link target attribute.
	 *                              Default: same
	 *                              Accepts: new|same
	 *     @type bool   $follow     Whether or not the link should be followed.
	 *                              Default: nofollow
	 *                              Accepts: dofollow | nofollow
	 *     @type string $visibility The visibility status of the link.
	 *                              Default: public
	 *                              Accepts: public|private|unlisted
	 *     @type mixed  $image      The array key of the link to be assigned to the entry image.
	 *                              Default: ''
	 *     @type mixed  $logo       The array key of the link to be assigned to the entry logo.
	 *                              Default: ''
	 * }
	 *
	 * @return void
	 */
	public function setLinks( $links ) {

		$userPreferred = NULL;

		$validFields = array(
			'id'         => NULL,
			'preferred'  => NULL,
			'type'       => NULL,
			'title'      => NULL,
			'url'        => NULL,
			'target'     => NULL,
			'follow'     => NULL,
			'visibility' => NULL,
		);

		if ( ! empty( $links ) ) {

			$order     = 0;
			$preferred = '';
			$image     = '';
			$logo      = '';

			if ( isset( $links['preferred'] ) ) {
				$preferred = $links['preferred'];
				unset( $links['preferred'] );
			}

			if ( isset( $links['image'] ) ) {
				$image = $links['image'];
				unset( $links['image'] );
			}

			if ( isset( $links['logo'] ) ) {
				$logo = $links['logo'];
				unset( $links['logo'] );
			}

			foreach ( $links as $key => $link ) {

				// First validate the supplied data.
				$link = cnSanitize::args( $link, $validFields );

				// If the URL is empty, no need to save it.
				if ( empty( $link['url'] ) || 'http://' == $link['url'] ) {
					unset( $links[ $key ] );
					continue;
				}

				// If the http protocol is not part of the url, add it.
				$links[ $key ]['url'] = cnURL::prefix( $link['url'] );

				// Sanitize the URL.
				$links[ $key ]['url'] = cnSanitize::field( 'url', $links[ $key ]['url'], 'db' );

				// Store the order attribute as supplied in the addresses array.
				$links[ $key ]['order'] = $order;

				// Convert the do/nofollow string to an (int) so it is saved properly in the db
				$links[ $key ]['follow']    = 'dofollow' == $link['follow'] ? 1 : 0;
				$links[ $key ]['preferred'] = ! empty( $preferred ) && $preferred == $key ? TRUE : FALSE;
				$links[ $key ]['image']     = ! empty( $image ) && $image == $key ? TRUE : FALSE;
				$links[ $key ]['logo']      = ! empty( $logo ) && $logo == $key ? TRUE : FALSE;

				/*
				 * If the user set a preferred link, save the $key value.
				 * This is going to be needed because if a link that the user
				 * does not have permission to edit is set to preferred, that link
				 * will have preference.
				 */
				if ( $links[ $key ]['preferred'] ) $userPreferred = $key;
				if ( $links[ $key ]['image'] ) $userImage = $key;
				if ( $links[ $key ]['logo'] ) $userLogo = $key;

				$order++;
			}
		}

		/*
		 * Before storing the data, add back into the array from the cache the networks
		 * the user may not have had permission to edit so the cache stays current.
		 */
		$cached = unserialize( $this->links );

		if ( ! empty( $cached ) ) {

			foreach ( $cached as $link ) {

				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( ! isset( $link['visibility'] ) || empty( $link['visibility'] ) ) $link['visibility'] = 'public';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				// Add back to the data array the networks that user does not have permission to view and edit.
				if ( ! cnValidate::userPermitted( $link['visibility'] ) ) {
					$links[] = $link;

					// If the network is preferred, it takes precedence, so the user's choice is overridden.
					if ( isset( $userPreferred ) && ! empty( $preferred ) && $link['preferred'] ) {
						$links[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						cnMessage::set( 'error', 'entry_preferred_overridden_link' );
					}

					// If the link is already assigned to an image, it takes precedence, so the user's choice is overridden.
					if ( isset( $userImage ) && ! empty( $image ) && $link['image'] ) {
						$links[ $userImage ]['image'] = FALSE;

						// @todo Create error message for the user.
					}

					// If the link is already assigned to an image, it takes precedence, so the user's choice is overridden.
					if ( isset( $userLogo ) && ! empty( $logo ) && $link['logo'] ) {
						$links[ $userLogo ]['logo'] = FALSE;

						// @todo Create error message for the user.
					}
				}
			}
		}

		$this->links = ! empty( $links ) ? serialize( $links ) : NULL;
		//print_r($links); die;
	}

	/**
	 * Returns as an array of objects containing the dates per the defined options for the current entry.
	 *
	 * Accepted options for the $atts property are:
	 * preferred (bool) Retrieve the preferred entry date.
	 *  type (array) || (string) Retrieve specific date types.
	 *   Permitted Types:
	 *    baptism
	 *    certification
	 *    employment
	 *    membership
	 *    graduate_high_school
	 *    graduate_college
	 *    ordination
	 *
	 * Filters:
	 *  cn_date_atts => (array) Set the method attributes.
	 *  cn_date_cached => (bool) Define if the returned dates should be from the object cache or queried from the db.
	 *  cn_date => (object) Individual date as it is processed thru the loop.
	 *  cn_dates => (array) All dates before they are returned.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $atts         Accepted values as noted above.
	 * @param bool    $cached       Returns the cached date data rather than querying the db.
	 * @param bool    $saving       Whether or no the data is being saved to the db.
	 *
	 * @return array
	 */
	public function getDates( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		global $connections;

		$results = array();

		$atts = apply_filters( 'cn_date_atts', $atts );
		$cached = apply_filters( 'cn_date_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		/*
		 * Load back into the results the data from the legacy fields, anniversary and birthday,
		 * for backward compatibility with versions 0.7.2.6 and older.
		 */
		if ( ! empty( $this->anniversary ) ) {

			$anniversary = new stdClass();

			$anniversary->id = 0;
			$anniversary->order = 0;
			$anniversary->preferred = FALSE;
			$anniversary->type = 'anniversary';
			$anniversary->name = __( 'Anniversary', 'connections' );
			$anniversary->date = $this->getAnniversary( 'Y-m-d' );
			$anniversary->day = $this->getAnniversary( 'm-d' );
			$anniversary->visibility = 'public';

			$results['anniversary'] = $anniversary;
		}

		if ( ! empty( $this->birthday ) ) {
			$birthday = new stdClass();

			$birthday->id = 0;
			$birthday->order = 0;
			$birthday->preferred = FALSE;
			$birthday->type = 'birthday';
			$birthday->name = __( 'Birthday', 'connections' );
			$birthday->date = $this->getBirthday( 'Y-m-d' );
			$birthday->day = $this->getBirthday( 'm-d' );
			$birthday->visibility = 'public';

			$results['birthday'] = $birthday;
		}

		if ( $cached ) {

			if ( ! empty( $this->dates ) ) {

				$dates = unserialize( $this->dates );
				if ( empty( $dates ) ) return $results;

				/**
				 * @var bool   $preferred
				 * @var string $type
				 */
				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				cnFunction::parseStringList( $type );

				foreach ( (array) $dates as $key => $date ) {

					$row = new stdClass();

					( isset( $date['id'] ) ) ? $row->id = (int) $date['id'] : $row->id = 0;
					( isset( $date['order'] ) ) ? $row->order = (int) $date['order'] : $row->order = 0;
					( isset( $date['preferred'] ) ) ? $row->preferred = (bool) $date['preferred'] : $row->preferred = FALSE;
					( isset( $date['type'] ) ) ? $row->type = $this->format->sanitizeString( $date['type'] ) : $row->type = '';
					( isset( $date['date'] ) ) ? $row->date = $this->format->sanitizeString( $date['date'] ) : $row->date = '';
					( isset( $date['visibility'] ) ) ? $row->visibility = $this->format->sanitizeString( $date['visibility'] ) : $row->visibility = '';

					/*
					 * Set the date name based on the type.
					 */
					$dateTypes = $connections->options->getDateOptions();
					$row->name = $dateTypes[ $row->type ];

					/*
					 * If the date type is anniversary or birthday and the date is equal to the date
					 * saved in the legacy fields, unset the data imported from the legacy field.
					 * This is for compatibility with versions 0.7.2.6 and older.
					 *
					 * NOTE: Only the month and day will be compared because the legacy getAnniversary() and getBirthday() methods
					 * will return the year of the next anniversary or birthday. IE: if that date in the current year has already
					 * passed the year would be the next year.
					 */
					if ( ( 'anniversary' == $row->type ) && ( isset( $results['anniversary'] ) ) && ( substr( $row->date, 5, 5 ) == $results['anniversary']->day ) ) unset( $results['anniversary'] );
					if ( ( 'birthday' == $row->type ) && ( isset( $results['birthday'] ) ) && ( substr( $row->date, 5, 5 ) == $results['birthday']->day ) ) unset( $results['birthday'] );

					/*
					 * // START -- Do not return dates that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					/*
					 * // END -- Do not return dates that do not match the supplied $atts.
					 */

					/*
					 * // START -- Compatibility for previous versions.
					 */
					// Versions prior to 8.1.5 may not have visibility set, so we'll assume it was 'public' since it wasn't the option before.
					if ( ! isset( $date['visibility'] ) || empty( $date['visibility'] ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */

					// If the user does not have permission to view the address, do not return it.
					if ( ! $this->validate->userPermitted( $row->visibility ) && ! $saving ) continue;

					$results[] = apply_filters( 'cn_date', $row );
				}

			}
		} else {

			// Exit right away and return an emtpy array if the entry ID has not been set otherwise all dates will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$dates = $connections->retrieve->dates( $atts );

			if ( empty( $dates ) ) return $results;

			foreach ( $dates as $date ) {

				$date->id = (int) $date->id;
				$date->order = (int) $date->order;
				$date->preferred = (bool) $date->preferred;
				$date->type = $this->format->sanitizeString( $date->type );
				$date->date = $this->format->sanitizeString( $date->date );
				$date->visibility = $this->format->sanitizeString( $date->visibility );

				/*
				 * Set the date name based on the date type.
				 */
				$dateTypes = $connections->options->getDateOptions();
				$date->name = $dateTypes[ $date->type ];

				/*
				 * If the date type is anniversary or birthday and the date is equal to the date
				 * saved in the legacy fields are the same, unset the data imported from the legacy field.
				 * This is for compatibility with versions 0.7.2.6 and older.
				 */
				if ( 'anniversary' == $date->type && isset( $results['anniversary'] ) && $date->date == $results['anniversary']->date ) unset( $results['anniversary'] );
				if ( 'birthday' == $date->type && isset( $results['birthday'] ) && $date->date == $results['birthday']->date ) unset( $results['birthday'] );

				/*
				 * If the date type is anniversary or birthday and the date is equal to the date
				 * saved in the legacy fields, unset the data imported from the legacy field.
				 * This is for compatibility with versions 0.7.2.6 and older.
				 *
				 * NOTE: Only the month and day will be compared because the legacy getAnniversary() and getBirthday() methods
				 * will return the year of the next anniversary or birthday. IE: if that date in the current year has already
				 * passed the year would be the next year.
				 */
				if ( ( 'anniversary' == $date->type ) && ( isset( $results['anniversary'] ) ) && ( substr( $date->date, 5, 5 ) == $results['anniversary']->day ) ) unset( $results['anniversary'] );
				if ( ( 'birthday' == $date->type ) && ( isset( $results['birthday'] ) ) && ( substr( $date->date, 5, 5 ) == $results['birthday']->day ) ) unset( $results['birthday'] );

				$results[] = apply_filters( 'cn_date', $date );
			}

		}

		return apply_filters( 'cn_dates', $results );
	}

	/**
	 * Caches the dates for use and preps for saving and updating.
	 *
	 * Valid values as follows.
	 *
	 * $date['id'] (int) Stores the date ID if it was retrieved from the db.
	 * $date['preferred'] (bool) If the date is the preferred date or not.
	 * $date['type'] (string) Stores the date type.
	 * $date['date'] (string) Stores date.
	 * $date['visibility'] (string) Stores the date visibility.
	 *
	 * @TODO Consider using strtotime on $date['date'] to help ensure date_create() does not return FALSE.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $dates
	 * @return void
	 */
	public function setDates( $dates ) {

		$userPreferred = NULL;

		/*
		 * These will be used to store the first anniversary and birthday entered by the user.
		 */
		$anniversary = array();
		$birthday = array();

		$validFields = array( 'id' => NULL, 'preferred' => NULL, 'type' => NULL, 'date' => NULL, 'visibility' => NULL );

		if ( ! empty( $dates ) ) {

			$order = 0;
			$preferred = '';

			if ( isset( $dates['preferred'] ) ) {
				$preferred = $dates['preferred'];
				unset( $dates['preferred'] );
			}

			foreach ( $dates as $key => $date ) {

				// First validate the supplied data.
				$date = cnSanitize::args( $date, $validFields );

				// If the date is empty, no need to store it.
				if ( empty( $date['date'] ) ) {
					unset( $dates[ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the date array.
				$dates[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $dates[ $key ]['preferred'] = TRUE : $dates[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a preferred date, save the $key value.
				 * This is going to be needed because if a date that the user
				 * does not have permission to edit is set to preferred, that date
				 * will have preference.
				 */
				if ( $dates[ $key ]['preferred'] ) $userPreferred = $key;

				/*
				 * Format the supplied date correctly for the table column:  YYYY-MM-DD
				 * @TODO Consider using strtotime on $date['date'] to help ensure date_create() does not return FALSE.
				 */
				$currentDate = date_create( $date['date'] );

				/*
				 * Make sure the date object created correctly.
				 */
				if ( FALSE === $currentDate ) continue;

				$dates[ $key ]['date'] = date_format( $currentDate, 'Y-m-d' );

				/*
				 * Check to see if the date is an anniversary or birthday and store them.
				 * These will then be sent and saved using the legacy methods for backward compatibility
				 * with version 0.7.2.6 and older.
				 */
				switch ( $date['type'] ) {
					case 'anniversary':

						if ( empty( $anniversary ) ) {
							$anniversary['month'] = date_format( $currentDate , 'm' );
							$anniversary['day'] = date_format( $currentDate , 'd' );

							$this->setAnniversary( $anniversary['day'], $anniversary['month'] );
						}

						break;

					case 'birthday':

						if ( empty( $birthday ) ) {
							$birthday['month'] = date_format( $currentDate , 'm' );
							$birthday['day'] = date_format( $currentDate , 'd' );

							$this->setBirthday( $birthday['day'], $birthday['month'] );
						}

						break;
				}

				$order++;
			}
		}

		/*
		 * If no anniversary or birthday date types were set, ensure the dates stored are emptied
		 * for backward compatibility with version 0.7.2.6 and older.
		 */
		if ( empty( $anniversary ) ) $this->anniversary = NULL;
		if ( empty( $birthday ) ) $this->birthday = NULL;

		/*
		 * Before storing the data, add back into the array from the cache the dates
		 * the user may not have had permission to edit so the cache stays current.
		 */
		$cached = unserialize( $this->dates );

		if ( ! empty( $cached ) ) {

			foreach ( $cached as $date ) {

				/*
				 * // START -- Compatibility for previous versions.
				 */
				if ( ! isset( $date['visibility'] ) || empty( $date['visibility'] ) ) $date['visibility'] = 'public';
				/*
				 * // END -- Compatibility for previous versions.
				 */

				if ( ! $this->validate->userPermitted( $date['visibility'] ) ) {
					//$dates[] = $date;

					// If the date is preferred, it takes precedence, so the user's choice is overridden.
					if ( ! empty( $preferred ) && $date['preferred'] ) {
						$dates[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						cnMessage::set( 'error', 'entry_preferred_overridden_date' );
					}
				}
			}
		}

		$this->dates = ! empty( $dates ) ? serialize( $dates ) : NULL;
	}

	/**
	 * Get the entry's anniversary. If formatted with the year, the year will be the year of the next upcoming
	 * year of the anniversary. For example, if the month and day of the anniversary date has not yet passed the current date,
	 * the current year will be returned. If the month and day of the anniversary date has passed the current date, the
	 * next year will be returned.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @deprecated Unknown Use {@see cnEntry::getDates()} instead.
	 * @see cnEntry::getDates()
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function getAnniversary( $format = 'F jS' ) {

		if ( empty( $this->anniversary ) ) {

			$anniversaries = $this->getDates( array( 'type' => 'anniversary' ) );

			if ( ! empty( $anniversaries ) ) {

				$date = date_create( $anniversaries[0]->date );

				$this->setAnniversary( date_format( $date, 'd' ), date_format( $date, 'm' ) );
			}

		}

		return $this->getUpcoming( 'anniversary', $format );
	}

	/**
	 * Set the entry's anniversary.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @deprecated Unknown Use {@see cnEntry::getDates()} instead.
	 * @see cnEntry::getDates()
	 *
	 * @param int $day
	 * @param int $month
	 */
	public function setAnniversary( $day, $month ) {

		//Create the anniversary with a default year and time since we don't collect the year. And this is needed so a proper sort can be done when listing them.
		$this->anniversary = ! empty( $day ) && ! empty( $month ) ? gmmktime( 0, 0, 1, $month, $day, 1970 ) : NULL;
	}

	/**
	 * Get the entry's birthday. If formatted with the year, the year will be the year of the next upcoming
	 * year of the birthday. For example, if the month and day of the birthday date has not yet passed the current date,
	 * the current year will be returned. If the month and day of the birthday date has passed the current date, the
	 * next year will be returned.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @deprecated Unknown Use {@see cnEntry::getDates()} instead.
	 * @see cnEntry::getDates()
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function getBirthday( $format = 'F jS' ) {

		if ( empty( $this->birthday ) ) {

			$birthdays = $this->getDates( array( 'type' => 'birthday' ) );

			if ( ! empty( $birthdays ) ) {

				$date = date_create( $birthdays[0]->date );

				$this->setBirthday( date_format( $date, 'd' ), date_format( $date, 'm' ) );
			}

		}

		return $this->getUpcoming( 'birthday', $format );
	}

	/**
	 * Set the entry's birthday.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @deprecated Unknown Use {@see cnEntry::getDates()} instead.
	 * @see cnEntry::getDates()
	 *
	 * @param int $day
	 * @param int $month
	 */
	public function setBirthday( $day, $month ) {

		//Create the birthday with a default year and time since we don't collect the year. And this is needed so a proper sort can be done when listing them.
		$this->birthday = ! empty( $day ) && ! empty( $month ) ? gmmktime( 0, 0, 1, $month, $day, 1970 ) : NULL;
	}

	/**
	 * Get the date of the entry's next anniversary or birthday. If the date of the anniversary or birthday has not
	 * yet occurred in the current year, the current year will be used. If the date has already passed in the current
	 * year the next year will be used.
	 *
	 * @access  public
	 * @since   unknown
	 *
	 * @uses    date_i18n()
	 * @uses    current_time()
	 *
	 * @param  string $type   The date type to get, anniversary or birthday.
	 * @param  string $format The date format to show the date in. Use PHP date formatting.
	 *
	 * @return string         The formatted date.
	 */
	public function getUpcoming( $type, $format = '' ) {

		if ( empty( $this->$type ) ) return '';

		$timeStamp = current_time( 'timestamp' );

		if ( empty( $format ) ) $format = cnSettingsAPI::get( 'connections', 'display_general', 'date_format' );

		if ( gmmktime( 23, 59, 59, gmdate( 'm', $this->$type ), gmdate( 'd', $this->$type ), gmdate( 'Y', $timeStamp ) ) < $timeStamp ) {

			/** @noinspection PhpWrongStringConcatenationInspection */
			$nextUDay = gmmktime( 0, 0, 0, gmdate( 'm', $this->$type ), gmdate( 'd', $this->$type ), gmdate( 'Y', $timeStamp ) + 1 );

		} else {

			$nextUDay = gmmktime( 0, 0, 0, gmdate( 'm', $this->$type ), gmdate( 'd', $this->$type ), gmdate( 'Y', $timeStamp ) );
		}

		// Convert the date to a string to convert to a string again.
		// Why? Because doing it this way should keep PHP from timezone adjusting the output.
		// date_default_timezone_set('UTC')
		return date_i18n( $format, strtotime( gmdate( 'r', $nextUDay ) ), TRUE );
	}

	/**
	 * Get the entry bio.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $context
	 *
	 * @return string
	 */
	public function getBio( $context = 'display' ) {

		return cnSanitize::field( 'bio', apply_filters( 'cn_bio', $this->bio ), $context );
	}

	/**
	 * Set the entry bio.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $bio
	 * @param string $context
	 */
	public function setBio( $bio, $context = 'db' ) {

		$this->bio = cnSanitize::field( 'bio', $bio, $context );
	}

	/**
	 * Get the entry notes.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $context
	 *
	 * @return string
	 */
	public function getNotes( $context = 'display' ) {

		return cnSanitize::field( 'bio', apply_filters( 'cn_notes', $this->notes ), $context );
	}

	/**
	 * Set the entry notes.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $notes
	 * @param string $context
	 */
	public function setNotes( $notes, $context = 'db' ) {

		$this->notes = cnSanitize::field( 'notes', $notes, $context );
	}

	/**
	 * Create excerpt from the supplied text. Default is the bio.
	 *
	 * @access public
	 * @since  unknown
	 * @param  array   $atts [optional]
	 * @param  string  $text [optional]
	 *
	 * @return string
	 */
	public function getExcerpt( $atts = array(), $text = '' ) {

		return cnString::excerpt( $text = empty( $text ) ? $this->getBio() : $text, $atts );
	}

	/**
	 * Returns $visibility.
	 *
	 * @access public
	 * @since unknown
	 * @return string
	 */
	public function getVisibility() {

		if ( is_null( $this->visibility ) ) $this->visibility = 'public';

		return sanitize_key( $this->visibility );
	}

	/**
	 * Sets the entry visibility status.
	 *
	 * @access public
	 * @since unknown
	 * @param (string) $visibility
	 * @return void
	 */
	public function setVisibility( $visibility ) {

		$permittedValues = array( 'unlisted', 'public', 'private' );

		$this->visibility = in_array( $visibility, $permittedValues ) ? sanitize_key( $visibility ) : 'unlisted';

	}

	/**
	 * The screen display value of the entry's visibility status.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function displayVisibilityType() {

		$permittedValues = array(
			'unlisted' => __( 'Unlisted', 'connections' ),
			'public'   => __( 'Public', 'connections' ),
			'private'  => __( 'Private', 'connections' )
		);

		$visibility = $this->getVisibility();

		return $permittedValues[ $visibility ];
	}

	/**
	 * Returns $category.
	 *
	 * @see cnEntry::$category
	 */
	public function getCategory() {
		return $this->categories;
	}

	/**
	 * Returns the entry meta data.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @uses   wp_parse_args()
	 * @uses   cnMeta::get()
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *
	 * @type string $key       Metadata key. If not specified, retrieve all metadata for the specified object.
	 * @type bool   $single    Default is FALSE. If TRUE, return only the first value of the specified meta_key.
	 *                         This parameter has no effect if $key is not specified.
	 * }
	 *
	 * @return mixed array|bool|string Array of the entry meta data.
	 *                                 String if $single is set to TRUE.
	 *                                 FALSE on failure.
	 */
	public function getMeta( $atts = array() ) {

		$defaults = array(
			'key'    => '',
			'single' => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		return cnMeta::get( 'entry', $this->getId(), $atts['key'], $atts['single'] );
	}

	/**
	 * Returns the entry type.
	 *
	 * Valid type are individual, organization and family.
	 *
	 * @return string
	 */
	public function getEntryType() {
		// This is to provide compatibility for versions >= 0.7.0.4
		if ( 'connection_group' == $this->entryType ) $this->entryType = 'family';

		return $this->entryType;
	}

	/**
	 * Sets $entryType.
	 *
	 * @param string $entryType
	 */
	public function setEntryType( $entryType ) {
		$this->options['entry']['type'] = $entryType;
		$this->entryType = $entryType;
	}

	/**
	 * Whether or not the logo is set to be displayed or not.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return bool
	 */
	public function getLogoDisplay() {
		return isset( $this->options['logo']['display'] ) ? $this->options['logo']['display'] : FALSE;
	}

	/**
	 * Set whether or not the logo should be displayed.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param bool $logoDisplay
	 */
	public function setLogoDisplay( $logoDisplay ) {
		$this->options['logo']['display'] = $logoDisplay;
	}

	/**
	 * Whether or not the logo is linked or not.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return bool
	 */
	public function getLogoLinked() {
		return isset( $this->options['logo']['linked'] ) ? $this->options['logo']['linked'] : FALSE;
	}

	/**
	 * Set whether or not the logo is linked.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param bool $logoLinked
	 */
	public function setLogoLinked( $logoLinked ) {
		$this->options['logo']['linked'] = $logoLinked;
	}

	/**
	 * Returns the filename of the original uploaded logo image.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function getLogoName() {

		if ( empty( $this->options['logo']['name'] ) ) {
			return '';
		}

		return $this->options['logo']['name'];
	}

	/**
	 * Saves the file name of the originally uploaded logo.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $logoName
	 */
	public function setLogoName( $logoName ) {
		$this->options['logo']['name'] = $logoName;
	}

	/**
	 * Returns $imageDisplay.
	 *
	 * @return bool
	 */
	public function getImageDisplay() {
		return $this->options['image']['display'];
	}

	/**
	 * Sets $imageDisplay.
	 *
	 * @param bool  $imageDisplay
	 * @see entry::$imageDisplay
	 */
	public function setImageDisplay( $imageDisplay ) {
		$this->options['image']['display'] = $imageDisplay;
	}

	/**
	 * Returns $imageLinked.
	 *
	 * @see entry::$imageLinked
	 */
	public function getImageLinked() {
		return $this->imageLinked;
	}

	/**
	 * Sets $imageLinked.
	 *
	 * @param bool  $imageLinked
	 * @see entry::$imageLinked
	 */
	public function setImageLinked( $imageLinked ) {
		$this->options['image']['linked'] = $imageLinked;
	}

	/**
	 * Returns $imageNameCard.
	 *
	 * @access public
	 * @since unknown
	 *
	 * @deprecated since 8.1.6. Use {@see cnEntry::getImageMeta()} instead.
	 * @see cnEntry::getImageMeta()
	 *
	 * @return string
	 */
	public function getImageNameCard() {

		if ( empty( $this->options['image']['name']['entry'] ) ) {
			return '';
		}

		return $this->options['image']['name']['entry'];
	}

	/**
	 * Returns $imageNameProfile.
	 *
	 * @access public
	 * @since unknown
	 *
	 * @deprecated since 8.1.6. Use {@see cnEntry::getImageMeta()} instead.
	 * @see cnEntry::getImageMeta()
	 *
	 * @return string
	 */
	public function getImageNameProfile() {

		if ( empty( $this->options['image']['name']['profile'] ) ) {
			return '';
		}

		return $this->options['image']['name']['profile'];
	}

	/**
	 * Returns $imageNameThumbnail.
	 *
	 * @access public
	 * @since unknown
	 *
	 * @deprecated since 8.1.6. Use {@see cnEntry::getImageMeta()} instead.
	 * @see cnEntry::getImageMeta()
	 *
	 * @return string
	 */
	public function getImageNameThumbnail() {

		if ( empty( $this->options['image']['name']['thumbnail'] ) ) {
			return '';
		}

		return $this->options['image']['name']['thumbnail'];
	}

	/**
	 * Returns the filename of the original uploaded image.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function getImageNameOriginal() {

		if ( empty( $this->options['image']['name']['original'] ) ) {
			return '';
		}

		return $this->options['image']['name']['original'];
	}

	/**
	 * Sets $imageNameOriginal.
	 *
	 * @param object  $imageNameOriginal
	 * @see entry::$imageNameOriginal
	 */
	public function setImageNameOriginal( $imageNameOriginal ) {
		$this->options['image']['name']['original'] = $imageNameOriginal;
	}

	/**
	 * Saves the logo image meta data (the result of cnImage::get()).
	 *
	 * @access public
	 * @since  8.1
	 * @param  array  $meta
	 */
	public function setOriginalLogoMeta( $meta ) {

		$this->options['logo']['meta'] = $meta;
	}

	/**
	 * Saves the photo image meta data (the result of cnImage::get()).
	 *
	 * @access public
	 * @since  8.1
	 * @param  array  $meta
	 */
	public function setOriginalImageMeta( $meta ) {

		$this->options['image']['meta']['original'] = $meta;
	}

	/**
	 * Get the original logo/photo absolute image path.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @uses   cnEntry::getSlug()
	 * @uses   cnEntry::getLogoName()
	 * @uses   cnEntry::getImageNameOriginal()
	 *
	 * @param  string $type The image path to return, logo | photo.
	 *
	 * @return string       The absolute image path.
	 */
	public function getOriginalImagePath( $type ) {

		if ( empty( $type ) ) {

			return '';
		}

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->getSlug() );

		switch ( $type ) {

			case 'logo':

				// Build the URL to the original image.
				$path = CN_IMAGE_PATH . $slug . DIRECTORY_SEPARATOR . $this->getLogoName();
				break;

			case 'photo':

				// Build the URL to the original image.
				$path = CN_IMAGE_PATH . $slug . DIRECTORY_SEPARATOR . $this->getImageNameOriginal();
				break;

			default:

				$path = '';
				break;
		}

		if ( file_exists( $path ) && ! is_dir( $path ) ) {

			return $path;
		}

		return '';
	}

	/**
	 * Get the original logo/photo image URL.
	 *
	 * @todo Consider using cnUpload::info() instead of the CN_IMAGE_BASE_URL constant.
	 * @link http://connections-pro.com/support/topic/error-the-img_path-variable-has-not-been-set/#post-318897
	 *
	 * @access public
	 * @since  8.1
	 * @uses   wp_upload_dir()
	 * @uses   trailingslashit()
	 * @uses   self::getSlug()
	 * @uses   self::getLogoName()
	 * @uses   self::getImageNameOriginal()
	 * @param  string $type The image URL to return, logo | photo.
	 * @return string       The image URL.
	 */
	public function getOriginalImageURL( $type ) {

		$url = '';

		if ( empty( $type ) ) return '';

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->getSlug() );

		switch ( $type ) {

			case 'logo':

				$url = CN_IMAGE_BASE_URL . $slug . '/' . $this->getLogoName();
				break;

			case 'photo':

				$url = CN_IMAGE_BASE_URL . $slug . '/' . $this->getImageNameOriginal();
				break;
		}

		return $url;
	}

	/**
	 * Return an array of image meta data.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string) Valid options: logo | photo | custom. Default: photo
	 * 	size (string) Valid options depend on `type`.
	 * 		If `type` is `logo`: original | scaled. Default: original
	 * 		If `type` is `photo`: original | thumbnail | medium | large. Default: original
	 * 		If `type` is `custom`: Not used, use the `width` and `height` to set the custom size.
	 * 	width (int) The width of the `custom` size.
	 * 	height (int) The height of the `custom` size.
	 * 	crop_mode (int) Which crop mode to utilize when rescaling the image. Valid range is 0–3. Default: 1
	 * 		0 == Resize to Fit specified dimensions with no cropping. Aspect ratio will not be maintained.
	 * 		1 == Crop and resize to best fit dimensions maintaining aspect ration. Default.
	 * 		2 == Resize proportionally to fit entire image into specified dimensions, and add margins if required.
	 * 			Use the canvas_color option to set the color to be used when adding margins.
	 * 		3 == Resize proportionally adjusting size of scaled image so there are no margins added.
	 * 	quality (int) The image quality to be used when saving the image. Valid range is 1–100. Default: 80
	 *
	 * The return array will contain the following keys and their value:
	 * 	name   => (string) The image name.
	 * 	path   => (string) The absolute image path.
	 * 	url    => (string) The image URL.
	 * 	width  => (int) The image width.
	 * 	height => (int) The image height.
	 * 	size   => (string) The image size in a string, `height="yyy" width="xxx"`, that can be used directly in an img tag.
	 * 	mime   => (string) The image mime type.
	 * 	type   => (int) The IMAGETYPE_XXX constants indicating the type of the image.
	 *
	 * @access public
	 * @since  8.1
	 * @uses   apply_filters()
	 * @uses   wp_parse_args()
	 * @uses   self::getOriginalImageURL()
	 * @uses   self::getOriginalImagePath()
	 * @uses   cnImage::get()
	 * @uses   WP_Error
	 * @uses   is_wp_error()
	 * @param  array  $atts
	 * @return mixed array|WP_Error
	 */
	public function getImageMeta( $atts = array() ) {

		$cropMode = array( 0 => 'none', 1 => 'crop', 2 => 'fill', 3 => 'fit' );
		$sizes    = array( 'thumbnail', 'medium', 'large' );
		$meta     = array();

		$defaults = array(
			'type'      => 'photo',
			'size'      => 'original',
			'width'     => 0,
			'height'    => 0,
			'crop_mode' => 1,
			'quality'   => 80,
		);

		$defaults = apply_filters( 'cn_default_atts_image_meta', $defaults );

		$atts = wp_parse_args( $atts, $defaults );

		if ( empty( $atts['type'] ) ) return $meta;

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->getSlug() );

		if ( 'custom' == $atts['size'] ) {

			$meta = cnImage::get(
				$this->getOriginalImageURL( $atts['type'] ),
				array(
					'crop_mode' => empty( $atts['crop_mode'] ) && $atts['crop_mode'] !== 0 ? 1 : $atts['crop_mode'],
					'width'     => empty( $atts['width'] ) ? NULL : $atts['width'],
					'height'    => empty( $atts['height'] ) ? NULL : $atts['height'],
					'quality'   => $atts['quality'],
					'sub_dir'   => $slug,
				),
				'data'
			);

			if ( ! is_wp_error( $meta ) ) {

				$meta['source'] = 'file';
			}

			return $meta;
		}

		switch ( $atts['type'] ) {

			case 'logo':

				switch ( $atts['size'] ) {

					case 'original':
						$meta['path'] = $this->getOriginalImagePath( $atts['type'] );
						$meta['url']  = $this->getOriginalImageURL( $atts['type'] );

						if ( isset( $this->options['logo']['meta'] ) ) {

							$meta = $this->options['logo']['meta'];

							// This needs to be here to ensure that the path and URL stored is updated
							// to the current path to account for users moving their site or changing
							// the site's folder structure.
							$meta['path'] = $this->getOriginalImagePath( $atts['type'] );
							$meta['url']  = $this->getOriginalImageURL( $atts['type'] );

							$meta['source'] = 'db';

						} else {

							/** @noinspection PhpUsageOfSilenceOperatorInspection */
							if ( is_file( $meta['path'] ) && $image = @getimagesize( $meta['path'] ) ) {

								$meta['width']  = $image[0];
								$meta['height'] = $image[1];
								$meta['size']   = $image[3];
								$meta['mime']   = $image['mime'];
								$meta['type']   = $image[2];
								$meta['source'] = 'file';

							} else {

								$meta = new WP_Error( 'image_not_found', __( sprintf( 'The file %s is not an image.', basename( $meta['path'] ) ), 'connections' ), $meta['path'] );
							}

						}

						break;

					default:

						$meta = cnImage::get(
							$this->getOriginalImageURL( $atts['type'] ),
							array(
								'crop_mode' => ( $key = array_search( cnSettingsAPI::get( 'connections', 'image_logo', 'ratio' ), $cropMode ) ) || $key === 0 ? $key : 2,
								'width'     => cnSettingsAPI::get( 'connections', 'image_logo', 'width' ),
								'height'    => cnSettingsAPI::get( 'connections', 'image_logo', 'height' ),
								'quality'   => cnSettingsAPI::get( 'connections', 'image_logo', 'quality' ),
								'sub_dir'   => $slug,
							),
							'data'
						);

						if ( ! is_wp_error( $meta ) ) {

							$meta['source'] = 'file';
						}

						break;

				}

				break;

			case 'photo':

				switch ( $atts['size'] ) {

					case 'original':

						$meta['path'] = $this->getOriginalImagePath( $atts['type'] );
						$meta['url']  = $this->getOriginalImageURL( $atts['type'] );

						if ( isset( $this->options['image']['meta']['original'] ) ) {

							$meta = $this->options['image']['meta']['original'];

							// This needs to be here to ensure that the path and URL stored is updated
							// to the current path to account for users moving their site or changing
							// the site's folder structure.
							$meta['path'] = $this->getOriginalImagePath( $atts['type'] );
							$meta['url']  = $this->getOriginalImageURL( $atts['type'] );

							$meta['source'] = 'db';

						} else {

							/** @noinspection PhpUsageOfSilenceOperatorInspection */
							if ( is_file( $meta['path'] ) && $image = @getimagesize( $meta['path'] ) ) {

								$meta['width']  = $image[0];
								$meta['height'] = $image[1];
								$meta['size']   = $image[3];
								$meta['mime']   = $image['mime'];
								$meta['type']   = $image[2];
								$meta['source'] = 'file';

							} else {

								$meta = new WP_Error( 'image_not_found', __( sprintf( 'The file %s is not an image.', basename( $meta['path'] ) ), 'connections' ), $meta['path'] );
							}

						}

						break;

					default:

						if ( in_array( $atts['size'], $sizes ) ) {

							$meta = cnImage::get(
								$this->getOriginalImageURL( $atts['type'] ),
								array(
									'crop_mode' => ( $key = array_search( cnSettingsAPI::get( 'connections', "image_{$atts['size']}", 'ratio' ), $cropMode ) ) || $key === 0 ? $key : 2,
									'width'     => cnSettingsAPI::get( 'connections', "image_{$atts['size']}", 'width' ),
									'height'    => cnSettingsAPI::get( 'connections', "image_{$atts['size']}", 'height' ),
									'quality'   => cnSettingsAPI::get( 'connections', "image_{$atts['size']}", 'quality' ),
									'sub_dir'   => $slug,
								),
								'data'
							);

							if ( ! is_wp_error( $meta ) ) {

								$meta['source'] = 'file';
							}

						}

						break;
				}

				break;

		}

		return $meta;
	}

	/**
	 * Copy or move the originally uploaded image to the new folder structure, post 8.1.
	 *
	 * NOTE: If the original logo already exists in the new folder structure, this will
	 * return TRUE without any further processing.
	 *
	 * NOTE: Versions previous to 0.6.2.1 did not not make a duplicate copy of images when
	 * copying an entry so it was possible multiple entries could share the same image.
	 * Only images created after the date that version .0.7.0.0 was released will be moved,
	 * plus a couple weeks for good measure. Images before that date will be copied instead
	 * so it is available to be copied to the new folder structure, post 8.1, for any other
	 * entries that may require it.
	 *
	 * @access private
	 * @since  8.1
	 * @uses   wp_upload_dir()
	 * @uses   trailingslashit()
	 * @param  string $filename The original image file name.
	 *
	 * @return mixed            bool|WP_Error TRUE on success, an instance of WP_Error on failure.
	 */
	protected function processLegacyImages( $filename ) {
		global $blog_id;

		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$legacyPath = WP_CONTENT_DIR . '/blogs.dir/' . $blog_id . '/connection_images/';

		} else {

			$legacyPath = WP_CONTENT_DIR . '/connection_images/';
		}

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->getSlug() );

		// Ensure the entry slug is not empty in case a user added an entry with no name.
		if ( empty( $slug ) ) return new WP_Error( 'image_empty_slug', __( sprintf( 'Failed to move legacy image %s.', $filename ), 'connections' ), $legacyPath . $filename );

		// Get the core WP uploads info.
		// $uploadInfo = wp_upload_dir();

		// Build the destination image path.
		$path = CN_IMAGE_PATH . $slug . DIRECTORY_SEPARATOR;

		/*
		 * NOTE: is_file() will always return false if teh folder/file does not
		 * have the execution bit set (ie 0775) on some hosts apparently. Need to
		 * come up with an alternative method which may not be possible without using
		 * WP_Filesystem and that causes a whole bunch of issues when credentials are
		 * required.
		 *
		 * Maybe chmodding the path to 0755 first, sounds safe?
		 * @link http://codex.wordpress.org/Changing_File_Permissions#Permission_Scheme_for_WordPress
		 * @link http://stackoverflow.com/a/11005
		 */

		// If the source image already exists in the new folder structure, post 8.1, bail, nothing to do.
		if ( is_file( $path . $filename ) ) {

			return TRUE;
		}

		if ( is_file( $legacyPath . $filename ) ) {

			// The modification file date that image will be deleted to maintain compatibility with 0.6.2.1 and older.
			$compatibilityDate = mktime( 0, 0, 0, 6, 1, 2010 );

			// Build path to the original file.
			$original = $legacyPath . $filename;

			// Get original file info.
			$info = pathinfo( $original );

			// Ensure the destination directory exists.
			if ( cnFileSystem::mkdir( $path ) ) {

				// Copy or move the original image.
				/** @noinspection PhpUsageOfSilenceOperatorInspection */
				if ( $compatibilityDate < @filemtime( $legacyPath . $filename ) ) {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @rename( $original, $path . $filename );

				} else {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @copy( $original, $path . $filename );
				}

				// Delete any of the legacy size variations if the copy/move was successful.
				if ( TRUE === $result ) {

					// NOTE: This is a little greedy as it will also delete any variations of any duplicate images used by other entries.
					// This should be alright because we will not need those variations anyway since they will be made from the original using cnImage.
					$files         = new DirectoryIterator( $legacyPath );
					$filesFiltered = new RegexIterator(
						$files,
						sprintf(
							'/%s(?:_thumbnail|_entry|_profile)(?:_\d+)?\.%s/i',
							preg_quote( preg_replace( '/(?:_original(?:_\d+)?)/i', '', $info['filename'] ) ),
							preg_quote( $info['extension'] )
						)
					);

					foreach ( $filesFiltered as $file ) {

						if ( $file->isDot() ) { continue; }

						/** @noinspection PhpUsageOfSilenceOperatorInspection */
						@unlink( $file->getPathname() );
					}

					return TRUE;
				}

			}

		}

		return new WP_Error( 'image_move_legacy_image_error', __( sprintf( 'Failed to move legacy image %s.', $filename ), 'connections' ), $legacyPath . $filename );
	}

	/**
	 * Copy or move the originally uploaded logo to the new folder structure, post 8.1.
	 *
	 * NOTE: If the original logo already exists in the new folder structure, this will
	 * return TRUE without any further processing.
	 *
	 * NOTE: Versions previous to 0.6.2.1 did not not make a duplicate copy of logos when
	 * copying an entry so it was possible multiple entries could share the same logo.
	 * Only logos created after the date that version .0.7.0.0 was released will be moved,
	 * plus a couple weeks for good measure. Images before that date will be copied instead
	 * so it is available to be copied to the new folder structure, post 8.1, for any other
	 * entries that may require it.
	 *
	 * @access private
	 * @since  8.1
	 * @uses   wp_upload_dir()
	 * @uses   trailingslashit()
	 * @param  string $filename The original logo file name.
	 *
	 * @return mixed            bool|WP_Error TRUE on success, an instance of WP_Error on failure.
	 */
	protected function processLegacyLogo( $filename ) {
		global $blog_id;

		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$legacyPath = WP_CONTENT_DIR . '/blogs.dir/' . $blog_id . '/connection_images/';

		} else {

			$legacyPath = WP_CONTENT_DIR . '/connection_images/';
		}

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->getSlug() );

		// Ensure the entry slug is not empty in case a user added an entry with no name.
		if ( empty( $slug ) ) return new WP_Error( 'image_empty_slug', __( sprintf( 'Failed to move legacy logo %s.', $filename ), 'connections' ), $legacyPath . $filename );

		// Get the core WP uploads info.
		// $uploadInfo = wp_upload_dir();

		// Build the destination logo path.
		$path = CN_IMAGE_PATH . $slug . DIRECTORY_SEPARATOR;

		// If the source logo already exists in the new folder structure, post 8.1, bail, nothing to do.
		if ( is_file( $path . $filename ) ) {

			return TRUE;
		}

		if ( is_file( $legacyPath . $filename ) ) {

			// The modification file date that logo will be deleted to maintain compatibility with 0.6.2.1 and older.
			$compatibilityDate = mktime( 0, 0, 0, 6, 1, 2010 );

			// Build path to the original file.
			$original = $legacyPath . $filename;

			// Ensure the destination directory exists.
			if ( cnFileSystem::mkdir( $path ) ) {

				// Copy or move the logo.
				/** @noinspection PhpUsageOfSilenceOperatorInspection */
				if ( $compatibilityDate < @filemtime( $legacyPath . $filename ) ) {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @rename( $original, $path . $filename );

				} else {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @copy( $original, $path . $filename );
				}

				if ( TRUE === $result ) return TRUE;
			}

		}

		return new WP_Error( 'image_move_legacy_logo_error', __( sprintf( 'Failed to move legacy logo %s.', $filename ), 'connections' ), $legacyPath . $filename );
	}

	/**
	 * Return the display name of user who add the entry.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function getAddedBy() {

		$addedBy = get_userdata( $this->addedBy );

		if ( $addedBy ) {
			return $addedBy->get( 'display_name' );
		} else {
			return 'Unknown';
		}
	}

	/**
	 * The sort column returned from @see cnRetrieve::entries().
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function getSortColumn() {
		return $this->sortColumn;
	}

	/**
	 * Return the display name of user who last edited the entry.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function getEditedBy() {

		$editedBy = get_userdata( $this->editedBy );

		if ( $editedBy ) {
			return $editedBy->get( 'display_name' );
		} else {
			return __( 'Unknown', 'connections' );
		}
	}

	/**
	 * Returns the entry's status.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return string
	 */
	public function getStatus() {
		return sanitize_key( $this->status );
	}

	/**
	 * Sets the entry's status to one of the permitted values.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $status
	 *
	 * @return void
	 */
	public function setStatus( $status ) {

		$permittedValues = array( 'approved', 'pending' );

		$this->status = in_array( $status, $permittedValues ) ? sanitize_key( $status ) : 'pending';
	}


	/**
	 * Returns $options.
	 */
	private function getOptions() {
		return $this->options;
	}

	/**
	 * Sets $options.
	 */
	private function serializeOptions() {
		$this->options = serialize( $this->options );
	}

	/**
	 * Sets up the current instance of cnEntry to pull in the values of the supplied ID.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param int $id The entry ID to query from the database.
	 *
	 * @return bool Whether of not the instance of cnEntry has been setup with the values of the new entry ID.
	 */
	public function set( $id ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( $result = $instance->retrieve->entry( $id ) ) {

			$this->__construct( $result );

		} else {

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Ensure fields that should be empty depending on the entry type.
	 *
	 * @access private
	 * @since  8.2.6
	 */
	private function setPropertyDefaultsByEntryType() {

		switch ( $this->getEntryType() ) {

			case 'organization':
				$this->familyName      = '';
				$this->honorificPrefix = '';
				$this->firstName       = '';
				$this->middleName      = '';
				$this->lastName        = '';
				$this->honorificSuffix = '';
				$this->title           = '';
				$this->familyMembers   = '';
				$this->birthday        = '';
				$this->anniversary     = '';
				break;

			case 'family':
				$this->honorificPrefix  = '';
				$this->firstName        = '';
				$this->middleName       = '';
				$this->lastName         = '';
				$this->honorificSuffix  = '';
				$this->title            = '';
				$this->contactFirstName = '';
				$this->contactLastName  = '';
				$this->birthday         = '';
				$this->anniversary      = '';
				break;

			default:
				$this->familyName       = '';
				$this->familyMembers    = '';
				$this->contactFirstName = '';
				$this->contactLastName  = '';
				break;
		}
	}

	/**
	 * Update the entry in the db.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return false|int
	 */
	public function update() {

		/** @var wpdb $wpdb */
		global $wpdb;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$this->serializeOptions();
		$this->setPropertyDefaultsByEntryType();

		do_action( 'cn_update-entry', $this );

		$result = $wpdb->update(
			CN_ENTRY_TABLE,
			array(
				'ts'                 => current_time( 'mysql' ),
				'entry_type'         => $this->entryType,
				'visibility'         => $this->getVisibility(),
				'slug'               => $this->getSlug(),
				'honorific_prefix'   => $this->honorificPrefix,
				'first_name'         => $this->firstName,
				'middle_name'        => $this->middleName,
				'last_name'          => $this->lastName,
				'honorific_suffix'   => $this->honorificSuffix,
				'title'              => $this->title,
				'organization'       => $this->organization,
				'department'         => $this->department,
				'contact_first_name' => $this->contactFirstName,
				'contact_last_name'  => $this->contactLastName,
				'family_name'        => $this->familyName,
				'birthday'           => $this->birthday,
				'anniversary'        => $this->anniversary,
				'addresses'          => $this->addresses,
				'phone_numbers'      => $this->phoneNumbers,
				'email'              => $this->emailAddresses,
				'im'                 => $this->im,
				'social'             => $this->socialMedia,
				'links'              => $this->links,
				'dates'              => $this->dates,
				'options'            => $this->options,
				'bio'                => $this->bio,
				'notes'              => $this->notes,
				'edited_by'          => $instance->currentUser->getID(),
				'user'               => $this->getUser(),
				'status'             => $this->status,
			),
			array(
				'id' => $this->id
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
			),
			array(
				'%d'
			)
		);

		//print_r($wpdb->last_query);

		/*
		 * Only update the rest of the entry's data if the update to the ENTRY TABLE was successful.
		 */
		if ( FALSE !== $result ) {

			require_once CN_PATH . 'includes/entry/class.entry-db.php';
			$cnDb = new cnEntry_DB( $this->getId() );

			$cnDb->upsert(
				CN_ENTRY_ADDRESS_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'line_1'     => array( 'key' => 'line_1' , 'format' => '%s' ),
					'line_2'     => array( 'key' => 'line_2' , 'format' => '%s' ),
					'line_3'     => array( 'key' => 'line_3' , 'format' => '%s' ),
					'city'       => array( 'key' => 'city' , 'format' => '%s' ),
					'state'      => array( 'key' => 'state' , 'format' => '%s' ),
					'zipcode'    => array( 'key' => 'zipcode' , 'format' => '%s' ),
					'country'    => array( 'key' => 'country' , 'format' => '%s' ),
					'latitude'   => array( 'key' => 'latitude' , 'format' => '%f' ),
					'longitude'  => array( 'key' => 'longitude' , 'format' => '%f' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getAddresses( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' )
				)
			);

			$cnDb->upsert(
				CN_ENTRY_PHONE_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'number'     => array( 'key' => 'number' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getPhoneNumbers( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' )
				)
			);

			$cnDb->upsert(
				CN_ENTRY_EMAIL_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'address'    => array( 'key' => 'address' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getEmailAddresses( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' )
				)
			);

			$cnDb->upsert(
				CN_ENTRY_MESSENGER_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'uid'        => array( 'key' => 'id' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getIm( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'uid', 'format' => '%d' )
				)
			);

			$cnDb->upsert(
				CN_ENTRY_SOCIAL_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'url'        => array( 'key' => 'url' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getSocialMedia( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' )
				)
			);

			$cnDb->upsert(
				CN_ENTRY_LINK_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'title'      => array( 'key' => 'title' , 'format' => '%s' ),
					'url'        => array( 'key' => 'url' , 'format' => '%s' ),
					'target'     => array( 'key' => 'target' , 'format' => '%s' ),
					'follow'     => array( 'key' => 'follow' , 'format' => '%d' ),
					'image'      => array( 'key' => 'image' , 'format' => '%d' ),
					'logo'       => array( 'key' => 'logo' , 'format' => '%d' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getLinks( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' )
				)
			);

			$cnDb->upsert(
				CN_ENTRY_DATE_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'date'       => array( 'key' => 'date' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getDates( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' )
				)
			);
		}

		do_action( 'cn_updated-entry', $this );

		return $result;
	}

	/**
	 * Save the entry to the db.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return false|int
	 */
	public function save() {

		/**
		 * @var connectionsLoad $connections
		 * @var wpdb            $wpdb
		 */
		global $wpdb, $connections;

		$this->serializeOptions();
		$this->setPropertyDefaultsByEntryType();

		do_action( 'cn_save-entry', $this );

		$result = $wpdb->insert(
			CN_ENTRY_TABLE,
			array(
				'ts'                 => current_time( 'mysql' ),
				'date_added'         => current_time( 'timestamp' ),
				'entry_type'         => $this->entryType,
				'visibility'         => $this->getVisibility(),
				'slug'               => $this->getSlug(), /* NOTE: When adding a new entry, a new unique slug should always be created and set. */
				'family_name'        => $this->familyName,
				'honorific_prefix'   => $this->honorificPrefix,
				'first_name'         => $this->firstName,
				'middle_name'        => $this->middleName,
				'last_name'          => $this->lastName,
				'honorific_suffix'   => $this->honorificSuffix,
				'title'              => $this->title,
				'organization'       => $this->organization,
				'department'         => $this->department,
				'contact_first_name' => $this->contactFirstName,
				'contact_last_name'  => $this->contactLastName,
				'addresses'          => $this->addresses,
				'phone_numbers'      => $this->phoneNumbers,
				'email'              => $this->emailAddresses,
				'im'                 => $this->im,
				'social'             => $this->socialMedia,
				'links'              => $this->links,
				'dates'              => $this->dates,
				'birthday'           => $this->birthday,
				'anniversary'        => $this->anniversary,
				'bio'                => $this->bio,
				'notes'              => $this->notes,
				'options'            => $this->options,
				'added_by'           => $connections->currentUser->getID(),
				'edited_by'          => $connections->currentUser->getID(),
				'owner'              => $connections->currentUser->getID(),
				'user'               => $this->getUser(),
				'status'             => $this->status
			),
			array(
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s'
			)
		);

		/**
		 * @todo Are these really needed? If they are, this should be refactored to remove their usage.
		 */
		$connections->lastQuery = $wpdb->last_query;
		$connections->lastQueryError = $wpdb->last_error;
		$connections->lastInsertID = $wpdb->insert_id;

		if ( FALSE !== $result ) {

			$this->setId( $wpdb->insert_id );

			require_once CN_PATH . 'includes/entry/class.entry-db.php';
			$cnDb = new cnEntry_DB( $this->getId() );

			$cnDb->insert(
				CN_ENTRY_ADDRESS_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'line_1'     => array( 'key' => 'line_1' , 'format' => '%s' ),
					'line_2'     => array( 'key' => 'line_2' , 'format' => '%s' ),
					'line_3'     => array( 'key' => 'line_3' , 'format' => '%s' ),
					'city'       => array( 'key' => 'city' , 'format' => '%s' ),
					'state'      => array( 'key' => 'state' , 'format' => '%s' ),
					'zipcode'    => array( 'key' => 'zipcode' , 'format' => '%s' ),
					'country'    => array( 'key' => 'country' , 'format' => '%s' ),
					'latitude'   => array( 'key' => 'latitude' , 'format' => '%f' ),
					'longitude'  => array( 'key' => 'longitude' , 'format' => '%f' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getAddresses( array(), TRUE, TRUE )
			);

			$cnDb->insert(
				CN_ENTRY_PHONE_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'number'     => array( 'key' => 'number' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getPhoneNumbers( array(), TRUE, TRUE )
			);

			$cnDb->insert(
				CN_ENTRY_EMAIL_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'address'    => array( 'key' => 'address' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getEmailAddresses( array(), TRUE, TRUE )
			);

			$cnDb->insert(
				CN_ENTRY_MESSENGER_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'uid'        => array( 'key' => 'id' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getIm( array(), TRUE, TRUE )
			);

			$cnDb->insert(
				CN_ENTRY_SOCIAL_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'url'        => array( 'key' => 'url' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getSocialMedia( array(), TRUE, TRUE )
			);

			$cnDb->insert(
				CN_ENTRY_LINK_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'title'      => array( 'key' => 'title' , 'format' => '%s' ),
					'url'        => array( 'key' => 'url' , 'format' => '%s' ),
					'target'     => array( 'key' => 'target' , 'format' => '%s' ),
					'follow'     => array( 'key' => 'follow' , 'format' => '%d' ),
					'image'      => array( 'key' => 'image' , 'format' => '%d' ),
					'logo'       => array( 'key' => 'logo' , 'format' => '%d' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getLinks( array(), TRUE, TRUE )
			);

			$cnDb->insert(
				CN_ENTRY_DATE_TABLE,
				array(
					'order'      => array( 'key' => 'order' , 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred' , 'format' => '%d' ),
					'type'       => array( 'key' => 'type' , 'format' => '%s' ),
					'date'       => array( 'key' => 'date' , 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility' , 'format' => '%s' )
				),
				$this->getDates( array(), TRUE, TRUE )
			);
		}

		do_action( 'cn_saved-entry', $this );

		return $result;
	}

	/**
	 * Delete the entry.
	 *
	 * @access public
	 * @since  Unknown
	 *
	 * @param int $id The entry ID.
	 */
	public function delete( $id ) {

		/**
		 * @var connectionsLoad $connections
		 * @var wpdb            $wpdb
		 */
		global $wpdb, $connections;

		do_action( 'cn_delete-entry', $this );
		do_action( 'cn_process_delete-entry', $this );  // KEEP! This action must exist for Link, however, do not ever use it!

		// Get the core WP uploads info.
		// $uploadInfo = wp_upload_dir();

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->getSlug() );

		// Ensure the entry slug is not empty in case a user added an entry with no name.
		// If this check is not done all the images in the CN_IMAGE_DIR_NAME will be deleted
		// by cnFileSystem::xrmdir() which would be very bad, indeed.
		if ( ! empty( $slug ) ) {

			// Build path to the subfolder in which all the entry's images are saved.
			$path = CN_IMAGE_PATH . $slug . DIRECTORY_SEPARATOR;

			// Delete the entry image and its variations.
			cnEntry_Action::deleteImages( $this->getImageNameOriginal(), $slug );

			// Delete any legacy images, pre 8.1, that may exist.
			cnEntry_Action::deleteLegacyImages( $this );

			// Delete the entry logo.
			cnEntry_Action::deleteImages( $this->getLogoName(), $slug );

			// Delete logo the legacy logo, pre 8.1.
			cnEntry_Action::deleteLegacyLogo( $this );

			// Delete the entry subfolder from CN_IMAGE_DIR_NAME.
			cnFileSystem::xrmdir( $path );
		}

		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_TABLE . ' WHERE id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the addresses if deleting the entry was successful
		 */
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE entry_id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the phone numbers if deleting the entry was successful
		 */
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_PHONE_TABLE . ' WHERE entry_id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the email addresses if deleting the entry was successful
		 */
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_EMAIL_TABLE . ' WHERE entry_id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the IM IDs if deleting the entry was successful
		 */
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_MESSENGER_TABLE . ' WHERE entry_id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the social network IDs if deleting the entry was successful
		 */
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_SOCIAL_TABLE . ' WHERE entry_id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the links if deleting the entry was successful
		 */
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_LINK_TABLE . ' WHERE entry_id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the dates if deleting the entry was successful
		 */
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . CN_ENTRY_DATE_TABLE . ' WHERE entry_id = %d' , $id ) );

		/**
		 *
		 *
		 * @TODO Only delete the category relationships if deleting the entry was successful
		 */
		$connections->term->deleteTermRelationships( $id );

		do_action( 'cn_deleted-entry', $this );
	}

}
