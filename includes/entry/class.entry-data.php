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
	 * @since 8.5.14
	 * @var int
	 */
	private $order;

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
	 * @var cnEntry_Addresses
	 */
	public $addresses;

	/**
	 * @var cnEntry_Phone_Numbers
	 */
	public $phoneNumbers;

	/**
	 * @var cnEntry_Email_Addresses
	 */
	public $emailAddresses;

	/**
	 * @var cnEntry_Messenger_IDs
	 */
	public $im = '';

	/**
	 * @var cnEntry_Links
	 */
	public $links = '';

	/**
	 * @var string
	 */
	private $socialMedia = '';

	/**
	 * Unix time: Birthday.
	 *
	 * @var int|string unix time
	 */
	private $birthday = '';

	/**
	 * Unix time: Anniversary.
	 *
	 * @var int|string unix time
	 */
	private $anniversary = '';

	/**
	 * The date data stored serialized array.
	 *
	 * @var string
	 *
	 * @since 0.7.3.0
	 */
	private $dates = '';

	/**
	 * @since 8.19
	 * @var cnEntry_Image
	 */
	public $image;

	/**
	 * String: Entry biography.
	 *
	 * @var string
	 */
	private $bio = '';

	/**
	 * String: Entry notes.
	 *
	 * @var string
	 */
	private $notes = '';

	/**
	 * Entry excerpt.
	 *
	 * @since 8.6.7
	 * @var   string
	 */
	private $excerpt = '';

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
	 * An array of categories associated to an entry.
	 * @since unknown
	 * @var array
	 */
	private $categories = array();

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

		// Load the formatting class for sanitizing the get methods.
		$this->format = new cnFormatting();

		// Load the validation class.
		$this->validate = new cnValidate();

		$this->im    = new cnEntry_Messenger_IDs();
		$this->links = new cnEntry_Links();

		if ( ! is_null( $entry ) ) {

			if ( isset( $entry->id ) ) $this->id = (integer) $entry->id;
			if ( isset( $entry->user ) ) $this->user = (integer) $entry->user;
			if ( isset( $entry->ts ) ) $this->timeStamp = $entry->ts;
			if ( isset( $entry->date_added ) ) $this->dateAdded = (integer) $entry->date_added;
			if ( isset( $entry->ordo ) ) $this->order = (integer) $entry->ordo;

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

			$this->addresses      = isset( $entry->addresses ) ? new cnEntry_Addresses( $this->getId(), $entry->addresses ) : new cnEntry_Addresses( $this->getId() );
			$this->phoneNumbers   = isset( $entry->phone_numbers ) ? new cnEntry_Phone_Numbers( $this->getId(), $entry->phone_numbers ) : new cnEntry_Phone_Numbers( $this->getId() );
			$this->emailAddresses = isset( $entry->email ) ? new cnEntry_Email_Addresses( $this->getId(), $entry->email ) : new cnEntry_Email_Addresses( $this->getId() );

			$this->im->setEntryID( $this->getId() );
			$this->links->setEntryID( $this->getId() );

			if ( isset( $entry->im ) ) {

				$this->im->fromMaybeSerialized( $entry->im );
			}

			if ( isset( $entry->links ) ) {

				$this->links->fromMaybeSerialized( $entry->links );
			}

			if ( isset( $entry->social ) ) $this->socialMedia = $entry->social;
			if ( isset( $entry->dates ) ) $this->dates = $entry->dates;

			if ( isset( $entry->birthday ) ) $this->birthday = (integer) $entry->birthday;
			if ( isset( $entry->anniversary ) ) $this->anniversary = (integer) $entry->anniversary;

			if ( isset( $entry->bio ) ) $this->bio = $entry->bio;
			if ( isset( $entry->notes ) ) $this->notes = $entry->notes;
			if ( isset( $entry->excerpt ) ) $this->excerpt = $entry->excerpt;
			if ( isset( $entry->visibility ) ) $this->visibility = $entry->visibility;
			if ( isset( $entry->sort_column ) ) $this->sortColumn = $entry->sort_column;

			if ( isset( $entry->options ) ) {

				$this->options = maybe_unserialize( $entry->options );
				$this->options = cnFormatting::maybeJSONdecode( $this->options );

				if ( isset( $this->options['image'] ) ) {

					$this->imageLinked = $this->options['image']['linked'];
					$this->imageDisplay = $this->options['image']['display'];
				}

				if ( isset( $this->options['logo'] ) ) {

					$this->logoLinked = $this->options['logo']['linked'];
					$this->logoDisplay = $this->options['logo']['display'];
				}

				if ( isset( $this->options['connection_group'] ) ) $this->familyMembers = $this->options['connection_group']; // For compatibility with versions <= 0.7.0.4
				if ( isset( $this->options['group']['family'] ) ) $this->familyMembers = $this->options['group']['family'];
			}

			if ( isset( $entry->entry_type ) ) $this->entryType = $entry->entry_type;

			if ( isset( $entry->added_by ) ) $this->addedBy = $entry->added_by;
			if ( isset( $entry->edited_by ) ) $this->editedBy = $entry->edited_by;

			if ( isset( $entry->owner ) ) $this->owner = $entry->owner;
			if ( isset( $entry->user ) ) $this->user = $entry->user;

			if ( isset( $entry->status ) ) $this->status = $entry->status;

			$this->ruid = uniqid( $this->getId() , FALSE );

		} else {

			$this->addresses      = new cnEntry_Addresses();
			$this->phoneNumbers   = new cnEntry_Phone_Numbers();
			$this->emailAddresses = new cnEntry_Email_Addresses();
		}

		/*
		 * Init this last so the cnEntry object if fully built since cnEntry_Image requires some of the properties.
		 * This will perhaps change as cnEntry_Image is still a work in progress and should not be used by third parties.
		 */
		$this->image = new cnEntry_Image( $this );
	}

	/**
	 * The entry ID.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return int
	 */
	public function getId() {
		return (integer) $this->id;
	}

	/**
	 * Set entry ID.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param int $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns user ID.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @return int
	 */
	public function getUser() {
		return (integer) empty( $this->user ) ? 0 : $this->user;
	}

	/**
	 * Sets user ID.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param int $id
	 */
	public function setUser( $id ) {
		$this->user = $id;
	}

	/**
	 * Returns a runtime unique id.
	 *
	 * @access public
	 * @since  unknown
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

			$options = array(
				get_option( 'date_format', 'm/d/Y' ),
				get_option( 'time_format', 'g:ia' )
			);

			$format = implode( ' ', $options );
		}

		return date_i18n( $format, strtotime( $this->timeStamp ) + cnDate::getWPUTCOffset() );
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
		return human_time_diff( strtotime( $this->timeStamp ), current_time( 'timestamp', TRUE ) );
	}

	/**
	 * Get the formatted date that the entry was added.
	 *
	 * @todo Add logic to deal with the possibility that date() can return FALSE.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function getDateAdded( $format = NULL ) {

		if ( is_null( $format ) ) {

			$options = array(
				get_option( 'date_format', 'm/d/Y' ),
				get_option( 'time_format', 'g:ia' )
			);

			$format = implode( ' ', $options );
		}

		if ( $this->dateAdded != NULL ) {

			return date_i18n( $format, $this->dateAdded + cnDate::getWPUTCOffset() );

		} else {

			return __( 'Unknown', 'connections' );
		}
	}

	/**
	 * Get the order assigned to the entry.
	 *
	 * @access public
	 * @since  8.5.14
	 *
	 * @return int
	 */
	public function getOrder() {

		$order = is_null( $this->order ) ? 0 : $this->order;

		return cnSanitize::integer( $order );
	}

	/**
	 * Set the order assigned to the entry.
	 *
	 * @access public
	 * @since  8.5.14
	 *
	 *
	 * @param int $order
	 */
	public function setOrder( $order ) {

		$this->order = cnSanitize::integer( $order );
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

		$this->directoryHome = cnSanitize::args( apply_filters( 'cn_entry_directory_homepage', $atts, $this ), $defaults );
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

		$permalink = cnURL::permalink(
			array(
				'type'       => 'name',
				'slug'       => $this->getSlug(),
				'home_id'    => $this->directoryHome['page_id'],
				'force_home' => $this->directoryHome['force_home'],
				'data'       => 'url',
				'return'     => TRUE,
			)
		);

		return apply_filters( 'cn_entry_permalink', $permalink, $this );
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
		$slug = empty( $slug ) || ! is_string( $slug ) ? $this->getName( array( 'format' => '%first%-%last%' ), 'db' ) : $slug;

		$slug = sanitize_title( apply_filters( 'cn_entry_slug', $slug ) );

		// If the entry was entered with no name, use the entry ID instead.
		if ( empty( $slug ) ) return 'cn-id-' . $this->getId();

		// Query all matching slugs in one database query.
		$query = $wpdb->prepare( 'SELECT slug FROM ' . CN_ENTRY_TABLE . ' WHERE slug LIKE %s', $wpdb->esc_like( $slug ) . '%' );

		$slugs = $wpdb->get_col( $query );

		if ( ! empty( $slugs ) ) {

			$num = 0;

			// Keep incrementing $num, until a space for a unique slug is found.
			while( in_array( ( $slug . '-' . ++$num ), $slugs ) );

			// Update $slug with the suffix.
			$slug = $slug . "-$num";
		}

		return $this->slug = $slug;
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
	 * @param string $context The context in which it should be sanitized. This method will eventually be declared as
	 *                        private.
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
	 * Use @see cnEntry::getName() instead of calling this method directly.
	 *
	 * NOTE: This method will eventually be declared asprivate.
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
	 * Use @see cnEntry::getName() instead of calling this method directly.
	 *
	 * NOTE: This method will eventually be declared as private.
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
	 * Use @see cnEntry::getName() instead of calling this method directly.
	 *
	 * NOTE: This method will eventually be declared as private.
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
	 * Use @see cnEntry::getName() instead of calling this method directly.
	 *
	 * NOTE: This method will eventually be declared as private.
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
	 * Use @see cnEntry::getContactName() instead of calling this method directly.
	 *
	 * NOTE: This method will eventually be declared as private.
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
	 * Use @see cnEntry::getContactName() instead of calling this method directly.
	 *
	 * NOTE: This method will eventually be declared as private.
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
	 * Use @see cnEntry::getName() instead of calling this method directly. This method will eventually be declared as
	 * private.
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

		$relations = array();

		if ( ! empty( $this->familyMembers ) ) {

			/*
			 * The family relationship data was saved as an associative array where key was the entry ID and the value was
			 * the relationship key.
			 *
			 * The data is now saved in a multidimensional array. What this nifty little count does is compare the array
			 * count and against a recursive array count and if they are equal, it should be of the older data format
			 * so loop thru it and put it in the new data format.
			 */
			if ( count( $this->familyMembers ) == count( $this->familyMembers, COUNT_RECURSIVE ) ) {

				foreach ( $this->familyMembers as $key => $relation ) {

					$relations[] = array( 'entry_id' => $key, 'relation' => $relation );
				}

			} else {

				$relations = $this->familyMembers;
			}

		}

		return $relations;
	}

	/**
	 * Saves the family relational data.
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

				$family[] = array( 'entry_id' => $relation['entry_id'], 'relation' => $relation['relation'] );
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

		$defaults = array(
			'preferred'   => FALSE,
			'type'        => array(),
			'district'    => array(),
			'county'      => array(),
			'city'        => array(),
			'state'       => array(),
			'zipcode'     => array(),
			'country'     => array(),
			'coordinates' => array(),
			'limit'       => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		if ( $cached ) {

			$results = $this->addresses->filterBy( 'type', $atts['type'] )
			                           ->filterBy( 'district', $atts['district'] )
			                           ->filterBy( 'county', $atts['county'] )
			                           ->filterBy( 'city', $atts['city'] )
			                           ->filterBy( 'state', $atts['state'] )
			                           ->filterBy( 'zipcode', $atts['zipcode'] )
			                           ->filterBy( 'country', $atts['country'] )
			                           ->filterBy( 'preferred', $atts['preferred'] )
			                           ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
			                           ->escapeFor( $context )
			                           ->getCollectionAsObjects( $atts['limit'] );

		} else {

			if ( ! $saving ) $atts['visibility'] = Connections_Directory()->currentUser->canView();

			$results = $this->addresses->query( $atts )
			                           ->escapeFor( $context )
			                           ->getCollectionAsObjects();
		}

		// The filters need to be reset so additional calls to get addresses with different params return expected results.
		$this->addresses->resetFilters();

		return $results;
	}

	/**
	 * Caches the addresses for use and preps for saving and updating.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array     $data       {
	 *
	 *     @type int    $id         The address ID if it was retrieved from the db.
	 *     @type bool   $preferred  Whether the address is the preferred address or not.
	 *     @type string $type       The address type.
	 *     @type string $line_1     Address line 1.
	 *     @type string $line_2     Address line 2.
	 *     @type string $line_3     Address line 3.
	 *     @type string $line_4     Address line 4.
	 *     @type string $district   The address district.
	 *     @type string $country    The address county.
	 *     @type string $city       The address locality.
	 *     @type string $state      The address region.
	 *     @type string $country    The address country.
	 *     @type float  $latitude   The address latitude.
	 *     @type float  $longitude  The address longitude.
	 *     @type string $visibility The address visibility.
	 * }
	 *
	 * @return void
	 */
	public function setAddresses( $data ) {

		$this->addresses->updateFromArray( $data );
	}

	/**
	 * Returns as an array of objects containing the phone numbers per the defined options for the current entry.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array  $atts {
	 *     @type bool         $preferred Whether or not to return only the preferred phone number.
	 *                                   Default: false
	 *     @type array|string $type      The phone number types to return.
	 * }
	 *
	 * @param bool   $cached  Returns the cached phone number data rather than querying the db.
	 * @param bool   $saving  Set as TRUE if adding a new entry or updating an existing entry.
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return array
	 */
	public function getPhoneNumbers( $atts = array(), $cached = TRUE, $saving = FALSE, $context = 'display' ) {

		$defaults = array(
			'preferred'   => FALSE,
			'type'        => array(),
			'limit'       => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		if ( $cached ) {

			$results = $this->phoneNumbers->filterBy( 'type', $atts['type'] )
			                              ->filterBy( 'preferred', $atts['preferred'] )
			                              ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
			                              ->escapeFor( $context )
			                              ->getCollectionAsObjects( $atts['limit'] );

		} else {

			if ( ! $saving ) $atts['visibility'] = Connections_Directory()->currentUser->canView();

			$results = $this->phoneNumbers->query( $atts )
			                              ->escapeFor( $context )
			                              ->getCollectionAsObjects();
		}

		// The filters need to be reset so additional calls to get addresses with different params return expected results.
		$this->phoneNumbers->resetFilters();

		return $results;
	}

	/**
	 * Caches the phone numbers for use and preps for saving and updating.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array     $data       {
	 *
	 *     @type int    $id         The phone number ID if it was retrieved from the db.
	 *     @type bool   $preferred  Whether or not the phone number is the preferred.
	 *     @type string $type       The phone number type.
	 *     @type string $number     The phone number.
	 *     @type string $visibility The phone number visibility.
	 * }
	 *
	 * @return void
	 */
	public function setPhoneNumbers( $data ) {

		$this->phoneNumbers->updateFromArray( $data );
	}

	/**
	 * Returns as an array of objects containing the email addresses per the defined options for the current entry.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array  $atts
	 * @param bool   $cached  Returns the cached email address data rather than querying the db.
	 * @param bool   $saving  Set as TRUE if adding a new entry or updating an existing entry.
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return array
	 */
	public function getEmailAddresses( $atts = array(), $cached = TRUE, $saving = FALSE, $context = 'display' ) {

		$defaults = array(
			'preferred'   => FALSE,
			'type'        => array(),
			'limit'       => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		if ( $cached ) {

			$results = $this->emailAddresses->filterBy( 'type', $atts['type'] )
			                                ->filterBy( 'preferred', $atts['preferred'] )
			                                ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
			                                ->escapeFor( $context )
			                                ->getCollectionAsObjects( $atts['limit'] );

		} else {

			if ( ! $saving ) $atts['visibility'] = Connections_Directory()->currentUser->canView();

			$results = $this->emailAddresses->query( $atts )
			                                ->escapeFor( $context )
			                                ->getCollectionAsObjects();
		}

		// The filters need to be reset so additional calls to get addresses with different params return expected results.
		$this->emailAddresses->resetFilters();

		return $results;
	}

	/**
	 * Caches the email addresses for use and preps for saving and updating.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array $data
	 */
	public function setEmailAddresses( $data ) {

		$this->emailAddresses->updateFromArray( $data );
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
	 * @access  public
	 * @since   0.7.3
	 * @version 1.0
	 *
	 * @param array  $atts    Accepted values as noted above.
	 * @param bool   $cached  Returns the cached email addresses data rather than querying the db.
	 * @param bool   $saving  Whether or no the data is being saved to the db.
	 * @param string $context The context in which it should be sanitized.
	 *
	 * @return array
	 */
	public function getIm( $atts = array(), $cached = TRUE, $saving = FALSE, $context = 'display' ) {

		$defaults = array(
			'preferred'   => FALSE,
			'type'        => array(),
			'limit'       => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		if ( $cached ) {

			$results = $this->im->filterBy( 'type', $atts['type'] )
			                    ->filterBy( 'preferred', $atts['preferred'] )
			                    ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
			                    ->escapeFor( $context )
			                    ->getCollectionAsObjects( $atts['limit'] );

		} else {

			if ( ! $saving ) $atts['visibility'] = Connections_Directory()->currentUser->canView();

			$results = $this->im->query( $atts )
			                    ->escapeFor( $context )
			                    ->getCollectionAsObjects();
		}

		// The filters need to be reset so additional calls to get addresses with different params return expected results.
		$this->im->resetFilters();

		return $this->im->backCompatibility( $results );
	}

	/**
	 * Caches the IM IDs for use and preps for saving and updating.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array $data
	 */
	public function setIm( $data ) {

		$this->im->updateFromArray( $this->im->backCompatibility( $data ) );
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
	 * @since  0.7.3
	 * @version 1.0
	 *
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

					/**
					 * Allow plugins to filter raw data before object is setup.
					 *
					 * @since 8.5.19
					 *
					 * @param array $network
					 */
					$network = apply_filters( 'cn_social_network-pre_setup', $network );

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

			$socialMedia = $connections->retrieve->socialMedia( $atts, $saving );

			if ( empty( $socialMedia ) ) return $results;

			foreach ( $socialMedia as $network ) {

				/** This filter is documented in ../includes/entry/class.entry-data.php */
				$network = apply_filters( 'cn_social_network-pre_setup', $network );

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
				if ( 0 == strlen( $network['url'] ) ) {

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

				/** This filter is documented in ../includes/entry/class.entry-data.php */
				$network = apply_filters( 'cn_social_network-pre_setup', $network );

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

		$this->socialMedia = ! empty( $socialNetworks ) ? serialize( $socialNetworks ) : '';
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
	 *
	 * @param bool  $cached Returns the cached link data rather than querying the db.
	 * @param bool  $saving Whether or no the data is being saved to the db.
	 *
	 * @return array
	 */
	public function getLinks( $atts = array(), $cached = TRUE, $saving = FALSE, $context = 'display' ) {

		$defaults = array(
			'preferred' => FALSE,
			'type'      => array(),
			'image'     => FALSE,
			'logo'      => FALSE,
			'limit'     => NULL,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		if ( $cached ) {

			$results = $this->links->filterBy( 'type', $atts['type'] )
			                       ->filterBy( 'preferred', $atts['preferred'] )
			                       ->filterBy( 'image', $atts['image'] )
			                       ->filterBy( 'logo', $atts['logo'] )
			                       ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
			                       ->escapeFor( $context )
			                       ->getCollectionAsObjects( $atts['limit'] );

		} else {

			if ( ! $saving ) $atts['visibility'] = Connections_Directory()->currentUser->canView();

			$results = $this->links->query( $atts )
			                       ->escapeFor( $context )
			                       ->getCollectionAsObjects();
		}

		// The filters need to be reset so additional calls to get links with different params return expected results.
		$this->links->resetFilters();

		return $results;
	}

	/**
	 * Returns as an array of objects containing the websites per the defined options for the current entry.
	 *
	 * $atts['preferred'] (bool) Retrieve the preferred website.
	 *
	 * @access public
	 * @since  unknonwn
	 *
	 * @deprecated 0.7.2.0 Use cnEntry::getLinks()
	 * @see cnEntry::getLinks()
	 *
	 * @param array $atts   Accepted values as noted above.
	 * @param bool  $cached Returns the cached social medial URLs data rather than querying the db.
	 *
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
	 * @access public
	 * @since  0.7.3
	 *
	 * @param array $data {
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
	public function setLinks( $data ) {

		$this->links->updateFromArray( $data );
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

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

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

				/**
				 * Covert to an array if it was supplied as a comma delimited string
				 * @var array $type
				 */
				cnFunction::parseStringList( $type );

				foreach ( (array) $dates as $key => $date ) {

					/**
					 * Allow plugins to filter raw data before object is setup.
					 *
					 * @since 8.5.19
					 *
					 * @param array $date
					 */
					$date = apply_filters( 'cn_date-pre_setup', $date );

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
					$dateTypes = $instance->options->getDateOptions();
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

			$dates = $instance->retrieve->dates( $atts, $saving );

			if ( empty( $dates ) ) return $results;

			foreach ( $dates as $date ) {

				/** This filter is documented in ../includes/entry/class.entry-data.php */
				$date = apply_filters( 'cn_date-pre_setup', $date );

				$date->id = (int) $date->id;
				$date->order = (int) $date->order;
				$date->preferred = (bool) $date->preferred;
				$date->type = $this->format->sanitizeString( $date->type );
				$date->date = $this->format->sanitizeString( $date->date );
				$date->visibility = $this->format->sanitizeString( $date->visibility );

				/*
				 * Set the date name based on the date type.
				 */
				$dateTypes = $instance->options->getDateOptions();
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
		if ( empty( $anniversary ) ) $this->anniversary = '';
		if ( empty( $birthday ) ) $this->birthday = '';

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

				/** This filter is documented in ../includes/entry/class.entry-data.php */
				$date = apply_filters( 'cn_date-pre_setup', $date );

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

		$this->dates = ! empty( $dates ) ? serialize( $dates ) : '';
	}

	/**
	 * Get the entry's anniversary. If formatted with the year, the year will be the year of the next upcoming
	 * year of the anniversary. For example, if the month and day of the anniversary date has not yet passed the
	 * current date, the current year will be returned. If the month and day of the anniversary date has passed the
	 * current date, the next year will be returned.
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
		$this->anniversary = ! empty( $day ) && ! empty( $month ) ? gmmktime( 0, 0, 1, $month, $day, 1972 ) : '';
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
		$this->birthday = ! empty( $day ) && ! empty( $month ) ? gmmktime( 0, 0, 1, $month, $day, 1972 ) : '';
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

		/*
		 * Convert the timestamp to a string only to convert to a timestamp again.
		 * Why? Because doing it this way should keep PHP from timezone adjusting the output
		 * because the time and timezone offset are added (T00:00:00+00:00) to the timestamp when formatted as `c`.
		 * Use date_i18n() so the date is localized.
		 */
		return date_i18n( $format, strtotime( gmdate( 'c', $nextUDay ) ) );
		//return gmdate( $format, $nextUDay ); // Not used, change in 8.10 reference @link https://connections-pro.com/support/topic/month-names-in-upcoming-list/
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

		$bio = cnSanitize::field( 'bio', apply_filters( 'cn_bio', $this->bio ), $context );

		return is_string( $bio ) ? $bio : '';
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

		$notes = cnSanitize::field( 'notes', apply_filters( 'cn_notes', $this->notes ), $context );

		return is_string( $notes ) ? $notes : '';
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
	 *
	 * @param array  $atts
	 * @param string $context
	 *
	 * @return string
	 */
	public function getExcerpt( $atts = array(), $context = 'display' ) {

		if ( 'display' === $context ) {

			if ( 0 < strlen( $this->excerpt ) ) {

				$excerpt = $this->excerpt;

			} else {

				$excerpt = cnString::excerpt( $this->getBio( $context ), $atts );
			}

		} else {

			$excerpt = cnSanitize::field( 'excerpt', $this->excerpt, $context );
		}

		return apply_filters( 'cn_excerpt', $excerpt );
	}

	/**
	 * Create excerpt from the supplied text. Default is the bio.
	 *
	 * @access private
	 * @since  8.6.7
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function getExcerptEdit( $atts = array() ) {

		return $this->getExcerpt( $atts, 'edit' );
	}

	/**
	 * Set the entry excerpt.
	 *
	 * @access public
	 * @since  8.6.7
	 *
	 * @param string $excerpt
	 * @param string $context
	 */
	public function setExcerpt( $excerpt, $context = 'db' ) {

		$this->excerpt = cnSanitize::field( 'excerpt', $excerpt, $context );
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
			'private'  => __( 'Private', 'connections' ),
		);

		$visibility = $this->getVisibility();

		return $permittedValues[ $visibility ];
	}

	/**
	 * Returns the categories assigned to the entry.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @see    cnEntry::$category
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	public function getCategory( $atts = array() ) {

		$defaults = array(
			'child_of' => 0,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$id = $this->getId();

		if ( ! empty( $id ) ) {

			$terms = cnRetrieve::entryTerms( $id, 'category' );

			if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {

				if ( $atts['child_of'] ) {

					$term_ids = wp_list_pluck( $terms, 'term_id' );

					if ( ! empty( $term_ids ) ) {

						$terms = cnTerm::getTaxonomyTerms(
							'category',
							array(
								'child_of' => $atts['child_of'],
								'include'  => $term_ids,
							)
						);
					}
				}

				$this->categories = $terms;
			}
		}

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
	 * @param string $imageNameOriginal
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
	 *
	 * @param  array  $atts
	 *
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

		$this->setPropertyDefaultsByEntryType();

		do_action( 'cn_update-entry', $this );

		$result = $wpdb->update(
			CN_ENTRY_TABLE,
			array(
				'ts'                 => current_time( 'mysql', TRUE ),
				'ordo'               => $this->getOrder(),
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
				//'addresses'          => $this->addresses,
				//'phone_numbers'      => $this->phoneNumbers,
				//'email'              => $this->emailAddresses,
				//'im'                 => $this->im,
				'social'             => $this->socialMedia,
				//'links'              => $this->links,
				'dates'              => $this->dates,
				'options'            => wp_json_encode( $this->options ),
				'bio'                => $this->bio,
				'notes'              => $this->notes,
				'excerpt'            => $this->excerpt,
				'edited_by'          => $instance->currentUser->getID(),
				'user'               => $this->getUser(),
				'status'             => $this->status,
			),
			array(
				'id' => $this->id,
			),
			array(
				'%s', // ts
				'%d', // ordo
				'%s', // entry_type
				'%s', // visibility
				'%s', // slug
				'%s', // honorific_prefix
				'%s', // first_name
				'%s', // middle_name
				'%s', // last_name
				'%s', // honorific_suffix
				'%s', // title
				'%s', // organization
				'%s', // department
				'%s', // contact_first_name
				'%s', // contact_last_name
				'%s', // family_name
				'%s', // birthday
				'%s', // anniversary
				//'%s', // addresses
				//'%s', // phone_numbers
				//'%s', // email
				//'%s', // im
				'%s', // social
				//'%s', // links
				'%s', // dates
				'%s', // options
				'%s', // bio
				'%s', // notes
				'%s', // excerpt
				'%d', // edited_by
				'%d', // user
				'%s', // status
			),
			array(
				'%d',
			)
		);

		//print_r($wpdb->last_query);

		/*
		 * Only update the rest of the entry's data if the update to the ENTRY TABLE was successful.
		 */
		if ( FALSE !== $result ) {

			require_once CN_PATH . 'includes/entry/class.entry-db.php';
			$cnDb = new cnEntry_DB( $this->getId() );

			$this->addresses->save();
			$this->phoneNumbers->save();
			$this->emailAddresses->save();
			$this->im->save();
			$this->links->save();

			$cnDb->upsert(
				CN_ENTRY_SOCIAL_TABLE,
				array(
					'order'      => array( 'key' => 'order', 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
					'type'       => array( 'key' => 'type', 'format' => '%s' ),
					'url'        => array( 'key' => 'url', 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility', 'format' => '%s' ),
				),
				$this->getSocialMedia( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' ),
				)
			);

			$cnDb->upsert(
				CN_ENTRY_DATE_TABLE,
				array(
					'order'      => array( 'key' => 'order', 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
					'type'       => array( 'key' => 'type', 'format' => '%s' ),
					'date'       => array( 'key' => 'date', 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility', 'format' => '%s' ),
				),
				$this->getDates( array(), TRUE, TRUE ),
				array(
					'id' => array( 'key' => 'id', 'format' => '%d' ),
				)
			);

			$this->updateObjectCaches();
		}

		do_action( 'cn_updated-entry', $this );

		return $result;
	}

	/**
	 * Update the entries address, phone, etc. object caches on updates.
	 *
	 * This is to ensure the ID of each address, phone, etc. is updated to reflect the ID in the database
	 * vs. leaving it as `0`.
	 *
	 * @access private
	 * @since  8.5.29
	 */
	private function updateObjectCaches() {

		/** @var wpdb $wpdb */
		global $wpdb;

		$addresses = $this->getAddresses( array(), FALSE, TRUE, 'db' );
		$addresses = json_decode( json_encode( $addresses ), TRUE );

		$phoneNumbers = $this->getPhoneNumbers( array(), FALSE, TRUE, 'db' );
		$phoneNumbers = json_decode( json_encode( $phoneNumbers ), TRUE );

		$emailAddresses = $this->getEmailAddresses( array(), FALSE, TRUE, 'db' );
		$emailAddresses = json_decode( json_encode( $emailAddresses ), TRUE );

		$im = $this->getIm( array(), FALSE, TRUE, 'db' );
		$im = json_decode( json_encode( $im ), TRUE );

		$socialNetworks = $this->getSocialMedia( array(), FALSE, TRUE );
		$socialNetworks = json_decode( json_encode( $socialNetworks ), TRUE );

		$links = $this->getLinks( array(), FALSE, TRUE, 'db' );
		$links = json_decode( json_encode( $links ), TRUE );

		$dates = $this->getDates( array(), FALSE, TRUE );
		$dates = json_decode( json_encode( $dates ), TRUE );

		//$this->phoneNumbers   = serialize( $phoneNumbers );
		//$this->emailAddresses = serialize( $emailAddresses );
		//$this->im             = serialize( $im );
		$this->socialMedia    = serialize( $socialNetworks );
		//$this->links          = serialize( $links );
		$this->dates          = serialize( $dates );

		$wpdb->update(
			CN_ENTRY_TABLE,
			array(
				'ts'            => current_time( 'mysql', TRUE ),
				'addresses'     => serialize( $addresses ),
				'phone_numbers' => serialize( $phoneNumbers ),
				'email'         => serialize( $emailAddresses ),
				'im'            => serialize( $im ),
				'social'        => $this->socialMedia,
				'links'         => serialize( $links ),
				'dates'         => $this->dates,
			),
			array( 'id' => $this->id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', ),
			array( '%d' )
		);
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

		$this->setPropertyDefaultsByEntryType();

		do_action( 'cn_save-entry', $this );

		$result = $wpdb->insert(
			CN_ENTRY_TABLE,
			array(
				'ts'                 => current_time( 'mysql', TRUE ),
				'date_added'         => current_time( 'timestamp', TRUE ),
				'ordo'               => $this->getOrder(),
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
				//'addresses'          => $this->addresses,
				//'phone_numbers'      => $this->phoneNumbers,
				//'email'              => $this->emailAddresses,
				//'im'                 => $this->im,
				'social'             => $this->socialMedia,
				//'links'              => $this->links,
				'dates'              => $this->dates,
				'birthday'           => $this->birthday,
				'anniversary'        => $this->anniversary,
				'bio'                => $this->bio,
				'notes'              => $this->notes,
				'excerpt'            => $this->excerpt,
				'options'            => wp_json_encode( $this->options ),
				'added_by'           => $connections->currentUser->getID(),
				'edited_by'          => $connections->currentUser->getID(),
				'owner'              => $connections->currentUser->getID(),
				'user'               => $this->getUser(),
				'status'             => $this->status,
			),
			array(
				'%s', // ts
				'%d', // date_added
				'%d', // ordo
				'%s', // entry_type
				'%s', // visibility
				'%s', // slug
				'%s', // family_name
				'%s', // honorific_prefix
				'%s', // first_name
				'%s', // middle_name
				'%s', // last_name
				'%s', // honorific suffix
				'%s', // title
				'%s', // organization
				'%s', // department
				'%s', // contact_first_name
				'%s', // contact_last_name
				//'%s', // addresses
				//'%s', // phone_numbers
				//'%s', // email
				//'%s', // im
				'%s', // social
				//'%s', // links
				'%s', // dates
				'%s', // birthday
				'%s', // anniversary
				'%s', // bio
				'%s', // notes
				'%s', // excerpt
				'%s', // options
				'%d', // added_by
				'%d', // edited_by
				'%d', // owner
				'%d', // user
				'%s'  // status
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

			$this->addresses->setEntryID( $this->getId() )->save();
			$this->phoneNumbers->setEntryID( $this->getId() )->save();
			$this->emailAddresses->setEntryID( $this->getId() )->save();
			$this->im->setEntryID( $this->getId() )->save();
			$this->links->setEntryID( $this->getId() )->save();

			$cnDb->insert(
				CN_ENTRY_SOCIAL_TABLE,
				array(
					'order'      => array( 'key' => 'order', 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
					'type'       => array( 'key' => 'type', 'format' => '%s' ),
					'url'        => array( 'key' => 'url', 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility', 'format' => '%s' ),
				),
				$this->getSocialMedia( array(), TRUE, TRUE )
			);

			$cnDb->insert(
				CN_ENTRY_DATE_TABLE,
				array(
					'order'      => array( 'key' => 'order', 'format' => '%d' ),
					'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
					'type'       => array( 'key' => 'type', 'format' => '%s' ),
					'date'       => array( 'key' => 'date', 'format' => '%s' ),
					'visibility' => array( 'key' => 'visibility', 'format' => '%s' ),
				),
				$this->getDates( array(), TRUE, TRUE )
			);

			$this->updateObjectCaches();
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
