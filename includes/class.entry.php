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
	 * Interger: Entry ID
	 *
	 * @var integer
	 */
	private $id;

	/**
	 *
	 *
	 * @var string
	 */
	private $ruid;

	/**
	 * Unix timestamp
	 *
	 * @var integer unix timestamp
	 */
	private $timeStamp;

	/**
	 * Unique slug
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Date added.
	 *
	 * @var integer unix timestamp
	 */
	private $dateAdded;

	/**
	 * Honorific prefix.
	 *
	 * @var string
	 */
	private $honorificPrefix;

	/**
	 * String: First Name
	 *
	 * @var string
	 */
	private $firstName;

	/**
	 * Middle Name
	 *
	 * @var string
	 */
	private $middleName;

	/**
	 * String: Last Name
	 *
	 * @var string
	 */
	private $lastName;

	/**
	 * Honorific suffix
	 *
	 * @var string
	 */
	private $honorificSuffix;

	/**
	 * String: Title
	 *
	 * @var string
	 */
	private $title;

	/**
	 * String: Oranization
	 *
	 * @var string
	 */
	private $organization;

	/**
	 * String: Department
	 *
	 * @var string
	 */
	private $department;

	private $contactFirstName;

	private $contactLastName;

	/**
	 * String: Family Name
	 *
	 * @var string
	 */
	private $familyName;

	/**
	 * Associative array of addresses
	 *
	 * @var associative array
	 */
	private $addresses;

	/**
	 * Associative array of phone numbers
	 *
	 * @var associative arrya
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
	private $websites;

	/**
	 * Associative array of links
	 *
	 * @var array
	 */
	private $links;

	/**
	 * Associative array of instant messengers IDs
	 *
	 * @var array
	 */
	private $im;

	private $socialMedia;

	/**
	 * Unix time: Birthday.
	 *
	 * @var unix time
	 */
	private $birthday;

	/**
	 * Unix time: Anniversary.
	 *
	 * @var unix time
	 */
	private $anniversary;

	/**
	 * The date data stored serialized array.
	 *
	 * @var array
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
	 * String: Visibilty Type; public, private, unlisted
	 *
	 * @var string
	 */
	private $visibility;

	private $options;
	private $imageLinked;
	private $imageDisplay;
	private $imageNameThumbnail;
	private $imageNameCard;
	private $imageNameProfile;
	private $imageNameOriginal;
	private $logoLinked;
	private $logoDisplay;
	private $logoName;
	private $entryType;
	private $familyMembers;

	private $categories;

	private $addedBy;
	private $editedBy;

	private $owner;
	private $user;

	private $status;

	public $format;
	public $validate;

	private $sortColumn;

	private $updateObjectCache = FALSE;

	function __construct( $entry = NULL ) {

		global $connections;

		if ( isset( $entry ) ) {
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

			if ( isset( $entry->birthday ) ) (integer) $this->birthday = $entry->birthday;
			if ( isset( $entry->anniversary ) ) (integer) $this->anniversary = $entry->anniversary;

			if ( isset( $entry->bio ) ) $this->bio = $entry->bio;
			if ( isset( $entry->notes ) ) $this->notes = $entry->notes;
			if ( isset( $entry->visibility ) ) $this->visibility = $entry->visibility;
			if ( isset( $entry->sort_column ) ) $this->sortColumn = $entry->sort_column;

			if ( isset( $entry->options ) ) {
				$this->options = unserialize( $entry->options );

				if ( isset( $this->options['image'] ) ) {
					$this->imageLinked = $this->options['image']['linked'];
					$this->imageDisplay = $this->options['image']['display'];

					if ( isset( $this->options['image']['name'] ) ) {
						$this->imageNameThumbnail = $this->options['image']['name']['thumbnail'];
						$this->imageNameCard = $this->options['image']['name']['entry'];
						$this->imageNameProfile = $this->options['image']['name']['profile'];
						$this->imageNameOriginal = $this->options['image']['name']['original'];
					}
				}

				if ( isset( $this->options['logo'] ) ) {
					$this->logoLinked = $this->options['logo']['linked'];
					$this->logoDisplay = $this->options['logo']['display'];

					if ( isset( $this->options['logo']['name'] ) ) {
						$this->logoName =$this->options['logo']['name'];
					}
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
		}

		// Load the formatting class for sanitizing the get methods.
		$this->format = new cnFormatting();

		// Load the validation class.
		$this->validate = new cnValidate();
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
	 * @param string  $format
	 * @see entry::$timeStamp
	 */
	public function getFormattedTimeStamp( $format = NULL ) {
		if ( !$format ) {
			$format = "m/d/Y";
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

	public function getHumanTimeDiff() {
		return human_time_diff( strtotime( $this->timeStamp ), current_time( 'timestamp' ) );
	}

	public function getDateAdded( $format = NULL ) {
		if ( $this->dateAdded != NULL ) {
			if ( empty( $format ) ) $format = 'm/d/Y';

			return date( $format, $this->dateAdded );
		}
		else {
			return 'Unknown';
		}
	}

	/**
	 * Returns $slug.
	 *
	 * @see cnEntry::$slug
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Sets $slug.
	 *
	 * @param object  $slug
	 * @see cnEntry::$slug
	 */
	public function setSlug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Returns a unique sanitized slug for insertion in the database.
	 *
	 * @return string
	 */
	private function getUniqueSlug( $slug = NULL ) {
		global $wpdb;

		// WP function -- formatting class
		$slug = empty( $slug ) ? sanitize_title( $this->getName( array( 'format' => '%first%-%last%' ) ) ) : $slug;

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
	 * Accepted options for the $atts property are:
	 *  format (string) Tokens for the parts of the name.
	 *   Permitted Tokens:
	 *    %prefix%
	 *    %first%
	 *    %middle%
	 *    %last%
	 *    %suffix%
	 *
	 * Example:
	 *  If an entry is an individual this would return their name as Last Name, First Name
	 *
	 *  $this->getName( array( 'format' => '%last%, %first% %middle%' ) );
	 *
	 * @param array   $atts [optional]
	 * @return string
	 */
	public function getName( $atts = NULL ) {
		$defaultAtts = array( 'format' => '%prefix% %first% %middle% %last% %suffix%' );

		$atts = $this->validate->attributesArray( $defaultAtts, (array) $atts );

		$search = array( '%prefix%', '%first%', '%middle%', '%last%', '%suffix%' );
		$replace = array();

		switch ( $this->getEntryType() ) {
		case 'individual':

			( isset( $this->honorificPrefix ) ) ? $replace[] = $this->getHonorificPrefix() : $replace[] = '';

			( isset( $this->firstName ) ) ? $replace[] = $this->getFirstName() : $replace[] = '';

			( isset( $this->middleName ) ) ? $replace[] = $this->getMiddleName() : $replace[] = '';

			( isset( $this->lastName ) ) ? $replace[] = $this->getLastName() : $replace[] = '';

			( isset( $this->honorificSuffix ) ) ? $replace[] = $this->getHonorificSuffix() : $replace[] = '';

			return str_ireplace( $search, $replace, $atts['format'] );

		case 'organization':
			return $this->getOrganization();

		case 'family':
			return $this->getFamilyName();

		default:

			( isset( $this->honorificPrefix ) ) ? $replace[] = $this->getHonorificPrefix() : $replace[] = '';;

			( isset( $this->firstName ) ) ? $replace[] = $this->getFirstName() : $replace[] = '';

			( isset( $this->middleName ) ) ? $replace[] = $this->getMiddleName() : $replace[] = '';

			( isset( $this->lastName ) ) ? $replace[] = $this->getLastName() : $replace[] = '';

			( isset( $this->honorificSuffix ) ) ? $replace[] = $this->getHonorificSuffix() : $replace[] = '';

			return str_ireplace( $search, $replace, $atts['format'] );
		}
	}

	public function getHonorificPrefix() {
		return $this->format->sanitizeString( $this->honorificPrefix );
	}

	public function setHonorificPrefix( $honorificPrefix ) {
		$this->honorificPrefix = stripslashes( $honorificPrefix );
	}

	/**
	 * Returns the entries first name.
	 * Returns $firstName.
	 *
	 * @see entry::$firstName
	 */
	public function getFirstName() {
		return $this->format->sanitizeString( $this->firstName );
	}

	/**
	 * Sets $firstName.
	 *
	 * @param object  $firstName
	 * @see entry::$firstName
	 */
	public function setFirstName( $firstName ) {
		$this->firstName = stripslashes( $firstName );
	}

	public function getMiddleName() {
		return $this->format->sanitizeString( $this->middleName );
	}

	public function setMiddleName( $middleName ) {
		$this->middleName = stripslashes( $middleName );
	}

	/**
	 * The last name if the entry type is an individual.
	 * If entry type is set to connection group the method will return the group name.
	 * Returns $lastName.
	 *
	 * @see entry::$lastName
	 */
	public function getLastName() {
		return $this->format->sanitizeString( $this->lastName );
	}

	/**
	 * Sets $lastName.
	 *
	 * @param string  $lastName
	 * @see entry::$lastName
	 */
	public function setLastName( $lastName ) {
		// Unescape the string because all methods expect unescaped strings.
		$this->lastName = stripslashes( $lastName );
	}

	public function getHonorificSuffix() {
		return $this->format->sanitizeString( $this->honorificSuffix );
	}

	public function setHonorificSuffix( $honorificSuffix ) {
		$this->honorificSuffix = stripslashes( $honorificSuffix );
	}


	/**
	 * The entries full name if the entry type is an individual.
	 *
	 * Returns $fullFirstLastName.
	 *
	 * @see entry::$fullFirstLastName
	 */
	public function getFullFirstLastName() {
		return $this->getName( array( 'format' => '%first% %middle% %last%' ) );
	}

	/**
	 * The entries full name; last name first if the entry type is an individual.
	 * Returns $fullLastFirstName.
	 *
	 * @see entry::$fullLastFirstName
	 */
	public function getFullLastFirstName() {
		return $this->getName( array( 'format' => '%last%, %first% %middle%' ) );
	}

	/**
	 * Returns the entries Organization.
	 * Returns $organization.
	 *
	 * @see entry::$organization
	 */
	public function getOrganization() {
		return $this->format->sanitizeString( $this->organization );
	}

	/**
	 * Sets $organization.
	 *
	 * @param object  $organization
	 * @see entry::$organization
	 */
	public function setOrganization( $organization ) {
		// Unescape the string because all methods expect unescaped strings.
		$this->organization = stripslashes( $organization );
	}

	/**
	 * Returns the entries Title.
	 * Returns $title.
	 *
	 * @see entry::$title
	 */
	public function getTitle() {
		return $this->format->sanitizeString( $this->title );
	}

	/**
	 * Sets $title.
	 *
	 * @param object  $title
	 * @see entry::$title
	 */
	public function setTitle( $title ) {
		// Unescape the string because all methods expect unescaped strings.
		$this->title = stripslashes( $title );
	}

	/**
	 * Returns the entries Department.
	 * Returns $department.
	 *
	 * @see entry::$department
	 */
	public function getDepartment() {
		return $this->format->sanitizeString( $this->department );
	}

	/**
	 * Sets $department.
	 *
	 * @param object  $department
	 * @see entry::$department
	 */
	public function setDepartment( $department ) {
		// Unescape the string because all methods expect unescaped strings.
		$this->department = stripslashes( $department );
	}

	/**
	 * Returns the entry's contact name.
	 *
	 * Accepted options for the $atts property are:
	 *  format (string) Tokens for the parts of the name.
	 *   Permitted Tokens:
	 *    %first%
	 *    %last%
	 *
	 * @param array   $atts [optional]
	 * @return string
	 */
	public function getContactName( $atts = NULL ) {
		$defaultAtts = array( 'format' => '%first% %last%' );

		$atts = $this->validate->attributesArray( $defaultAtts, $atts );

		$search = array( '%first%', '%last%' );
		$replace = array();

		( isset( $this->contactFirstName ) ) ? $replace[] = $this->getContactFirstName() : $replace[] = '';

		( isset( $this->contactLastName ) ) ? $replace[] = $this->getContactLastName() : $replace[] = '';

		return str_ireplace( $search , $replace , $atts['format'] );
	}

	public function getContactFirstName() {
		return $this->format->sanitizeString( $this->contactFirstName );
	}

	public function setContactFirstName( $contactFirstName ) {
		// Unescape the string because all methods expect unescaped strings.
		$this->contactFirstName = stripslashes( $contactFirstName );
	}

	public function getContactLastName() {
		return $this->format->sanitizeString( $this->contactLastName );
	}

	public function setContactLastName( $contactLastName ) {
		// Unescape the string because all methods expect unescaped strings.
		$this->contactLastName = stripslashes( $contactLastName );
	}

	/**
	 * Returns $familyName.
	 *
	 * @see entry::$familyName
	 */
	public function getFamilyName() {
		return $this->format->sanitizeString( $this->familyName );
	}

	/**
	 * Sets $familyName.
	 *
	 * @param object  $familyName
	 * @see entry::$familyName
	 */
	public function setFamilyName( $familyName ) {
		// Unescape the string because all methods expect unescaped strings.
		$this->familyName = stripslashes( $familyName );
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
	 * Sets $familyMembers.
	 */
	public function setFamilyMembers( $familyMembers ) {
		/*
		 * The form to capture the user IDs and relationship stores the data
		 * in a two-dementional array as follows:
		 * 		array[0]
		 * 			array[entry_id]
		 * 				 [relation]
		 *
		 * This loop re-writes the data into an associative array entry_id => relation.
		 */
		if ( empty( $familyMembers ) ) {
			$family = array();
		}
		else {
			foreach ( $familyMembers as $relation ) {
				$family[ $relation['entry_id'] ] .= $relation['relation'];
			}
		}

		$this->options['group']['family'] = $family;
	}

	/**
	 * Returns as an array of objects contining the addresses per the defined options for the current entry.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry address.
	 *  type (array) || (string) Retrieve specific address types.
	 *   Permitted Types:
	 *    home
	 *    work
	 *    school
	 *    other
	 *  city (array) || (string) Retrieve addresses in a specific city.
	 *  state (array) || (string) Retrieve addresses in a specific state.
	 *  zipcode (array) || (string) Retrieve addresses in a specific zipcode.
	 *  country (array) || (string) Retrieve addresses in a specific country.
	 *  coordinates (array) Retrieve addresses in with specific coordinates. Both latitude and longitude must be supplied.
	 *
	 * Filters:
	 *  cn_address_atts => (array) Set the method attributes.
	 *  cn_address_cached => (bool) Define if the returned addresses should be from the object cache or queried from the db.
	 *  cn_address => (object) Individual address as it is processed thru the loop.
	 *  cn_addresses => (array) All addresses before it is returned.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached address data rather than querying the db.
	 * @param bool    $saving       Set as TRUE if adding a new entry or updating an existing entry.
	 * @return array
	 */
	public function getAddresses( $atts = array(), $cached = TRUE, $saving = FALSE ) {
		global $connections;

		$addresses = array();
		$results = array();

		$atts = apply_filters( 'cn_address_atts', $atts );
		$cached = apply_filters( 'cn_address_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = FALSE;
		$defaults['type'] = NULL;
		$defaults['city'] = NULL;
		$defaults['state'] = NULL;
		$defaults['zipcode'] = NULL;
		$defaults['country'] = NULL;
		$defaults['coordinates'] = array();

		$atts = $this->validate->attributesArray( $defaults, $atts );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->addresses ) ) {

				$addresses = unserialize( $this->addresses );
				if ( empty( $addresses ) ) return $results;

				extract( $atts );

				/*
				 * Covert these to values to an array if they were supplied as a comma delimited string
				 */
				if ( ! empty( $type ) && ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );
				if ( ! empty( $city ) && ! is_array( $city ) ) $city = explode( ',' , trim( $city ) );
				if ( ! empty( $state ) && ! is_array( $state ) ) $state = explode( ',' , trim( $state ) );
				if ( ! empty( $zipcode ) && ! is_array( $zipcode ) ) $zipcode = explode( ',' , trim( $zipcode ) );
				if ( ! empty( $country ) && ! is_array( $country ) ) $country = explode( ',' , trim( $country ) );

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

					//var_dump($address);

					$row = new stdClass();

					( isset( $address['id'] ) ) ? $row->id = (int) $address['id'] : $row->id = 0;
					( isset( $address['order'] ) ) ? $row->order = (int) $address['order'] : $row->order = 0;
					( isset( $address['preferred'] ) ) ? $row->preferred = (bool) $address['preferred'] : $row->preferred = FALSE;
					( isset( $address['type'] ) ) ? $row->type = $this->format->sanitizeString( $address['type'] ) : $row->type = '';
					( isset( $address['line_1'] ) ) ? $row->line_1 = $this->format->sanitizeString( $address['line_1'] ) : $row->line_1 = '';
					( isset( $address['line_2'] ) ) ? $row->line_2 = $this->format->sanitizeString( $address['line_2'] ) : $row->line_2 = '';
					( isset( $address['line_3'] ) ) ? $row->line_3 = $this->format->sanitizeString( $address['line_3'] ) : $row->line_3 = '';
					( isset( $address['city'] ) ) ? $row->city = $this->format->sanitizeString( $address['city'] ) : $row->city = '';
					( isset( $address['state'] ) ) ? $row->state = $this->format->sanitizeString( $address['state'] ) : $row->state = '';
					( isset( $address['zipcode'] ) ) ? $row->zipcode = $this->format->sanitizeString( $address['zipcode'] ) : $row->zipcode = '';
					( isset( $address['country'] ) ) ? $row->country = $this->format->sanitizeString( $address['country'] ) : $row->country = '';
					( isset( $address['latitude'] ) ) ? $row->latitude = (float) $address['latitude'] : $row->latitude = NULL;
					( isset( $address['longitude'] ) ) ? $row->longitude = (float) $address['longitude'] : $row->longitude = NULL;
					( isset( $address['visibility'] ) ) ? $row->visibility = $this->format->sanitizeString( $address['visibility'] ) : $row->visibility = '';

					/*
					 * Set the address name based on the address type.
					 */
					// Some previous versions did set the address type, so set the type to 'other'.
					if ( empty( $row->type ) ) $row->type = 'other';
					$addressTypes = $connections->options->getDefaultAddressValues();
					// Recent previous versions set the type to the Select string from the drop down, so set the type to 'other'.
					( $addressTypes[ $row->type ] == 'Select' ) ? $row->name = 'Other' : $row->name = $addressTypes[ $row->type ];

					/*
					 * // START -- Compatibility for previous versions.
					 */
					if ( isset( $address['address_line1'] ) && ! empty( $address['address_line1'] ) ) $row->line_1 = $this->format->sanitizeString( $address['address_line1'] );
					if ( isset( $address['address_line2'] ) && ! empty( $address['address_line2'] ) ) $row->line_2 = $this->format->sanitizeString( $address['address_line2'] );

					$row->line_one =& $row->line_1;
					$row->line_two =& $row->line_2;
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

					$results[] = apply_filters( 'cn_address', $row );
				}

			}

		} else {

			// Exit right away and return an empty array if the entry ID has not been set otherwise all addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$addresses = $connections->retrieve->addresses( $atts );
			//print_r($results);

			if ( empty( $addresses ) ) return $results;

			foreach ( $addresses as $address ) {

				$address->id = (int) $address->id;
				$address->order = (int) $address->order;
				$address->preferred = (bool) $address->preferred;
				$address->type = $this->format->sanitizeString( $address->type );
				$address->line_1 = $this->format->sanitizeString( $address->line_1 );
				$address->line_2 = $this->format->sanitizeString( $address->line_2 );
				$address->line_3 = $this->format->sanitizeString( $address->line_3 );
				$address->city = $this->format->sanitizeString( $address->city );
				$address->state = $this->format->sanitizeString( $address->state );
				$address->zipcode = $this->format->sanitizeString( $address->zipcode );
				$address->country = $this->format->sanitizeString( $address->country );

				$address->latitude = (float) $address->latitude;
				if ( empty( $address->latitude ) ) $address->latitude = NULL;
				$address->longitude = (float) $address->longitude;
				if ( empty( $address->longitude ) ) $address->longitude = NULL;

				$address->visibility = $this->format->sanitizeString( $address->visibility );

				/*
				 * Set the address name based on the address type.
				 */
				$addressTypes = $connections->options->getDefaultAddressValues();
				( ! isset( $addressTypes[ $address->type ] ) || $addressTypes[ $address->type ] === 'Select' ) ? $address->name = NULL : $address->name = $addressTypes[ $address->type ];

				/*
				 * // START -- Compatibility for previous versions.
				 */
				$address->line_one =& $address->line_1;
				$address->line_two =& $address->line_2;
				$address->line_three =& $address->line_3;
				/*
				 * // END -- Compatibility for previous versions.
				 */

				$results[] = apply_filters( 'cn_address', $address );
			}

		}

		return apply_filters( 'cn_addresses', $results );
	}

	/**
	 * Caches the addresses for use and preps for saving and updating.
	 *
	 * Valid values as follows.
	 *
	 * $addresses['id'] (int) Stores the address ID if it was retrieved from the db.
	 * $addresses['preferred'] (bool) Stores is the address is the preferred address or not.
	 * $addresses['type'] (string) Stores the address type.
	 * $addresses['line_1'] (string) Stores address line 1.
	 * $addresses['line_2'] (string) Stores address line 2.
	 * $addresses['line_3'] (string) Stores address line 3.
	 * $addresses['city'] (string) Stores the address city.
	 * $addresses['state'] (string) Stores the address state.
	 * $addresses['zipcode'] (string) Stores the address zipcode.
	 * $addresses['country'] (string) Stores the address country.
	 * $addresses['latitude'] (float) Stores the address latitude.
	 * $addresses['longitude'] (float) Stores the address longitude.
	 * $addresses['visibility'] (string) Stores the address visibility.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $addresses
	 * @return void
	 */
	public function setAddresses( $addresses ) {

		global $connections;

		$userPreferred = NULL;

		$validFields = array( 'id' => NULL, 'preferred' => NULL, 'type' => NULL, 'line_1' => NULL, 'line_2' => NULL, 'line_3' => NULL, 'city' => NULL, 'state' => NULL, 'zipcode' => NULL, 'country' => NULL, 'latitude' => NULL, 'longitude' => NULL, 'visibility' => NULL );

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
				$address = $this->validate->attributesArray( $validFields, $address );

				/*
				 * Geocode the address using Google Geocoding API
				 */
				if ( empty( $address['latitude'] ) || empty( $address['longitude'] ) ) {
					//$geocode = new cnGeo();
					$result = cnGeo::address( $address );

					if ( ! empty( $result ) ) {
						$addresses[ $key ]['latitude'] = $result->latitude;
						$addresses[ $key ]['longitude'] = $result->longitude;
					}

				}

				// Store the order attribute as supplied in the addresses array.
				$addresses[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $addresses[ $key ]['preferred'] = TRUE : $addresses[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a perferred address, save the $key value.
				 * This is going to be needed because if an address that the user
				 * does not have permission to edit is set to preferred, that address
				 * will have preference.
				 */
				if ( $addresses[ $key ]['preferred'] ) $userPreferred = $key;

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
						$connections->setErrorMessage( 'entry_preferred_overridden_address' );
					}
				}
			}
		}

		( ! empty( $addresses ) ) ? $this->addresses = serialize( $addresses ) : $this->addresses = NULL;
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

		global $connections;

		$phoneNumbers = array();
		$results = array();

		$atts = apply_filters( 'cn_phone_atts', $atts );
		$cached = apply_filters( 'cn_phone_cached', $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = FALSE;
		$defaults['type'] = NULL;

		$atts = $this->validate->attributesArray( $defaults, $atts );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->phoneNumbers ) ) {

				$phoneNumbers = unserialize( $this->phoneNumbers );
				if ( empty( $phoneNumbers ) ) return $results;

				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				if ( ! empty( $type ) && ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

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
					$phoneTypes = $connections->options->getDefaultPhoneNumberValues();
					$row->name = $phoneTypes[ $row->type ];

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

			}
		}
		else {
			// Exit right away and return an emtpy array if the entry ID has not been set otherwise all phone numbers will be returned by the query.
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
				$phoneTypes = $connections->options->getDefaultPhoneNumberValues();
				$phone->name = $phoneTypes[ $phone->type ];

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

		global $connections;

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
				$phoneNumber = $this->validate->attributesArray( $validFields, $phoneNumber );

				// If the number is empty, no need to store it.
				if ( empty( $phoneNumber['number'] ) ) {
					unset( $phoneNumbers[ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the addresses array.
				$phoneNumbers[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $phoneNumbers[ $key ]['preferred'] = TRUE : $phoneNumbers[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a perferred number, save the $key value.
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

					// If the number is preferred, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $preferred ) && $phone['preferred'] ) {
						$phoneNumbers[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						$connections->setErrorMessage( 'entry_preferred_overridden_phone' );
					}
				}
			}
		}

		( ! empty( $phoneNumbers ) ) ? $this->phoneNumbers = serialize( $phoneNumbers ) : $this->phoneNumbers = NULL;
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

		global $connections;

		$emailAddresses = array();
		$results = array();

		$atts = apply_filters( 'cn_email_atts', $atts );
		$cached = apply_filters( 'cn_email_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = FALSE;
		$defaults['type'] = NULL;

		$atts = $this->validate->attributesArray( $defaults, $atts );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( !empty( $this->emailAddresses ) ) {

				$emailAddresses = unserialize( $this->emailAddresses );
				if ( empty( $emailAddresses ) ) return $results;

				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				if ( ! empty( $type ) && ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

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

			}

		} else {

			// Exit right away and return an emtpy array if the entry ID has not been set otherwise all email addresses will be returned by the query.
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

		global $connections;

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
				$email = $this->validate->attributesArray( $validFields, $email );

				// If the address is empty, no need to store it.
				if ( empty( $email['address'] ) ) {
					unset( $emailAddresses [ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the addresses array.
				$emailAddresses[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $emailAddresses[ $key ]['preferred'] = TRUE : $emailAddresses[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a perferred address, save the $key value.
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

					// If the address is preferred, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $preferred ) && $email['preferred'] ) {
						$emailAddresses[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						$connections->setErrorMessage( 'entry_preferred_overridden_email' );
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
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached email addresses data rather than querying the db.
	 * @return array
	 */
	public function getIm( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		global $connections;

		$imIDs = array();
		$results = array();

		$atts = apply_filters( 'cn_messenger_atts', $atts );
		$cached = apply_filters( 'cn_messenger_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = FALSE;
		$defaults['type'] = NULL;

		$atts = $this->validate->attributesArray( $defaults, $atts );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */


		if ( $cached ) {

			if ( ! empty( $this->im ) ) {

				$networks = unserialize( $this->im );

				if ( empty( $networks ) ) return $results;

				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				if ( ! empty( $type ) && ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

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

			// Exit right away and return an emtpy array if the entry ID has not been set otherwise all email addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$imIDs = $connections->retrieve->imIDs( $atts );
			//print_r($results);

			if ( empty( $imIDs ) ) return $results;

			foreach ( $imIDs as $network ) {

				/*
				 * This will probably forever give me headaches,
				 * Previous versions stored the IM ID as id. Now that the data
				 * is stored in a seperate table, id is now the unique table `id`
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

		global $connections;

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
				$network = $this->validate->attributesArray( $validFields, $network );

				// If the id is emty, no need to store it.
				if ( empty( $network['id'] ) ) {
					unset( $im[ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the addresses array.
				$im[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $im[ $key ]['preferred'] = TRUE : $im[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a perferred network, save the $key value.
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

					// If the network is preferred, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $preferred ) && $network['preferred'] ) {

						$im[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						$connections->setErrorMessage( 'entry_preferred_overridden_im' );
					}
				}
			}
		}

		( ! empty( $im ) ) ? $this->im = serialize( $im ) : $this->im = NULL;
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
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached social medial URLs data rather than querying the db.
	 * @return array
	 */
	public function getSocialMedia( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		global $connections;

		$socialMediaIDs = array();
		$results = array();

		$atts = apply_filters( 'cn_social_network_atts', $atts );
		$cached = apply_filters( 'cn_social_network_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = FALSE;
		$defaults['type'] = NULL;

		$atts = $this->validate->attributesArray( $defaults, $atts );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->socialMedia ) ) {

				$networks = unserialize( $this->socialMedia );
				if ( empty( $networks ) ) return $results;

				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				if ( ! empty( $type ) && ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

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

			// Exit right away and return an emtpy array if the entry ID has not been set otherwise all email addresses will be returned by the query.
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

		global $connections;

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
				$network = $this->validate->attributesArray( $validFields, $network );

				// If the URL is empty, no need to save it.
				if ( empty( $network['url'] ) || $network['url'] == 'http://' ) {

					unset( $socialNetworks[ $key ] );
					continue;

				}

				// if the http protocol is not part of the url, add it.
				if ( preg_match( "/https?/" , $network['url'] ) == 0 ) $socialNetworks[ $key ]['url'] = 'http://' . $network['url'];

				// Store the order attribute as supplied in the addresses array.
				$socialNetworks[ $key ]['order'] = $order;

				( ( ! empty( $preferred ) ) && $preferred == $key ) ? $socialNetworks[ $key ]['preferred'] = TRUE : $socialNetworks[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a perferred network, save the $key value.
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

					// If the network is preferred, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $preferred ) && $network['preferred'] ) {

						$socialNetworks[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						$connections->setErrorMessage( 'entry_preferred_overridden_social' );

					}
				}
			}
		}

		( ! empty( $socialNetworks ) ) ? $this->socialMedia = serialize( $socialNetworks ) : $this->socialMedia = NULL;

	}

	/**
	 * Return an array of objects containing the links per the defined options for the current entry.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry link.
	 *  type (array) || (string) Retrieve specific link types[network].
	 *   Permitted Types:
	 *    website
	 *    blog
	 *  image (bool) Retrieve the link assigned to the image.
	 *  logo (bool) Retrieve the link assigned to the logo.
	 *
	 * Filters:
	 *  cn_link_atts => (array) Set the method attributes.
	 *  cn_link_cached => (bool) Define if the returned email addresses should be from the object cache or queried from the db.
	 *  cn_link => (object) Individual email address as it is processed thru the loop.
	 *  cn_links => (array) All phone numbers before it is returned.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached link data rather than querying the db.
	 * @return array
	 */
	public function getLinks( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		global $connections;

		$linkIDs = array();
		$results = array();

		$atts = apply_filters( 'cn_link_atts', $atts );
		$cached = apply_filters( 'cn_link_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = FALSE;
		$defaults['type'] = NULL;
		$defaults['image'] = FALSE;
		$defaults['logo'] = FALSE;

		$atts = $this->validate->attributesArray( $defaults, $atts );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		if ( $cached ) {

			if ( ! empty( $this->links ) ) {

				$links = unserialize( $this->links );
				if ( empty( $links ) ) return $results;

				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				if ( ! empty( $type ) && ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

				foreach ( (array) $links as $key => $link ) {

					$row = new stdClass();

					( isset( $link['id'] ) ) ? $row->id                 = (int) $link['id'] : $row->id = 0;
					( isset( $link['order'] ) ) ? $row->order           = (int) $link['order'] : $row->order = 0;
					( isset( $link['preferred'] ) ) ? $row->preferred   = (bool) $link['preferred'] : $row->preferred = FALSE;
					( isset( $link['type'] ) ) ? $row->type             = $this->format->sanitizeString( $link['type'] ) : $row->type = 'website';
					( isset( $link['title'] ) ) ? $row->title           = $this->format->sanitizeString( $link['title'] ) : $row->title = '';
					( isset( $link['address'] ) ) ? $row->address       = $this->format->sanitizeString( $link['address'] ) : $row->address = NULL;
					( isset( $link['url'] ) ) ? $row->url               = $this->format->sanitizeString( $link['url'] ) : $row->url = '';
					( isset( $link['target'] ) ) ? $row->target         = $this->format->sanitizeString( $link['target'] ) : $row->target = '_blank';
					( isset( $link['follow'] ) ) ? $row->follow         = (bool) $link['follow'] : $row->follow = FALSE;
					( isset( $link['image'] ) ) ? $row->image           = (bool) $link['image'] : $row->image = FALSE;
					( isset( $link['logo'] ) ) ? $row->logo             = (bool) $link['logo'] : $row->logo = FALSE;
					( isset( $link['visibility'] ) ) ? $row->visibility = $this->format->sanitizeString( $link['visibility'] ) : $row->visibility = '';

					/*
					 * Set the Link name based on type.
					 */
					$linkTypes = $connections->options->getDefaultLinkValues();
					( empty( $row->type ) ) ? $row->name = $linkTypes['website'] : $row->name = $linkTypes[ $row->type ];
					//var_dump($row->type);

					/*
					 * // START -- Compatibility for previous versions.
					 */
					if ( empty( $row->url ) ) $row->url         =& $row->address;
					if ( empty( $row->address ) ) $row->address =& $row->url;
					if ( empty( $row->title ) ) $row->title     = $row->address;
					if ( empty( $row->name ) ) $row->name       = 'Website';

					// Versions prior to 0.7.1.6 may not have visibility set, so we'll assume it was 'public' since it wasn't the option before.
					if ( ! isset( $link['visibility'] ) || empty( $link['visibility'] ) ) $row->visibility = 'public';
					/*
					 * // END -- Compatibility for previous versions.
					 */


					/*
					 * Set the dofollow/nofollow string based on the bool value.
					 */
					( $row->follow ) ? $row->followString = 'dofollow' : $row->followString = 'nofollow';

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
					if ( ! $this->validate->userPermitted( $row->visibility ) && ! $saving ) continue;

					$results[] = apply_filters( 'cn_link', $row );
				}

			}

		} else {

			// Exit right away and return an emtpy array if the entry ID has not been set otherwise all email addresses will be returned by the query.
			if ( ! isset( $this->id ) || empty( $this->id ) ) return array();

			$links = $connections->retrieve->links( $atts );
			//print_r($results);

			if ( empty( $links ) ) return $results;

			foreach ( $links as $link ) {

				$link->id         = (int) $link->id;
				$link->order      = (int) $link->order;
				$link->preferred  = (bool) $link->preferred;
				$link->type       = $this->format->sanitizeString( $link->type );
				$link->title      = $this->format->sanitizeString( $link->title );
				$link->url        = $this->format->sanitizeString( $link->url );
				$link->target     = $this->format->sanitizeString( $link->target );
				$link->follow     = (bool) $link->follow;
				$link->image      = (bool) $link->image;
				$link->logo       = (bool) $link->logo;
				$link->visibility = $this->format->sanitizeString( $link->visibility );

				/*
				 * Set the link name based on the link type.
				 */
				$linkTypes  = $connections->options->getDefaultLinkValues();
				$link->name = $linkTypes[ $link->type ];

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
				( $link->follow ) ? $link->followString = 'dofollow' : $link->followString = 'nofollow';

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

		global $connections;

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;

		$atts = $this->validate->attributesArray( $defaults, $atts );
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
	 * Valid values as follows.
	 *
	 * $link['id'] (int) Stores the link ID if it was retrieved from the db.
	 * $link['preferred'] (bool) If the link is the preferred network or not.
	 * $link['type'] (string) Stores the link type.
	 * $link['title'] (string) Stores the link title.
	 * $link['url'] (string) Stores link URL.
	 * $link['target'] (string) Stores the link target.
	 * $link['follow'] (bool) Sets the follow attribute.
	 * $link['visibility'] (string) Stores the link visibility.
	 *
	 * @TODO: Validate as valid web addresses.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $links
	 * @return void
	 */
	public function setLinks( $links ) {

		global $connections;

		$userPreferred = NULL;

		$validFields = array( 'id' => NULL, 'preferred' => NULL, 'type' => NULL, 'title' => NULL, 'url' => NULL, 'target' => NULL, 'follow' => NULL, 'visibility' => NULL );

		if ( ! empty( $links ) ) {

			$order = 0;
			$preferred = FALSE;
			$image = FALSE;
			$logo = FALSE;

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
				$link = $this->validate->attributesArray( $validFields, $link );

				// If the URL is empty, no need to save it.
				if ( empty( $link['url'] ) || $link['url'] == 'http://' ) {
					unset( $links[ $key ] );
					continue;
				}

				// if the http protocol is not part of the url, add it.
				if ( preg_match( "/https?/" , $link['url'] ) == 0 ) $links[ $key ]['url'] = 'http://' . $link['url'];

				// Store the order attribute as supplied in the addresses array.
				$links[ $key ]['order'] = $order;

				// Convert the do/nofollow string to an (int) so it is saved properly in the db
				( $link['follow'] == 'dofollow' ) ? $links[ $key ]['follow'] = 1 : $links[ $key ]['follow'] = 0;

				( ( ! empty( $preferred ) ) && $preferred == $key ) ? $links[ $key ]['preferred'] = TRUE : $links[ $key ]['preferred'] = FALSE;

				( ( ! empty( $image ) ) && $image == $key ) ? $links[ $key ]['image'] = TRUE : $links[ $key ]['image'] = FALSE;

				( ( ! empty( $logo ) ) && $logo == $key ) ? $links[ $key ]['logo'] = TRUE : $links[ $key ]['logo'] = FALSE;

				/*
				 * If the user set a preferred network, save the $key value.
				 * This is going to be needed because if a network that the user
				 * does not have permission to edit is set to preferred, that network
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
				if ( ! $this->validate->userPermitted( $link['visibility'] ) ) {
					$links[] = $link;

					// If the network is preferred, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $preferred ) && $link['preferred'] ) {
						$links[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						$connections->setErrorMessage( 'entry_preferred_overridden_link' );
					}

					// If the link is already assigned to an image, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $image ) && $link['image'] ) {
						$links[ $userImage ]['image'] = FALSE;

						// @TODO Create error message for the user.
					}

					// If the link is already assigned to an image, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $logo ) && $link['logo'] ) {
						$links[ $userLogo ]['logo'] = FALSE;

						// @TODO Create error message for the user.
					}
				}
			}
		}

		( ! empty( $links ) ) ? $this->links = serialize( $links ) : $this->links = NULL;
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
	 * @param array   $atts 		Accepted values as noted above.
	 * @param bool    $cached       Returns the cached date data rather than querying the db.
	 * @return array
	 */
	public function getDates( $atts = array(), $cached = TRUE, $saving = FALSE ) {

		global $connections;

		$dates = array();
		$results = array();

		$atts = apply_filters( 'cn_date_atts', $atts );
		$cached = apply_filters( 'cn_date_cached' , $cached );

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = FALSE;
		$defaults['type'] = NULL;

		$atts = $this->validate->attributesArray( $defaults, $atts );
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		/*
		 * Load back into the results the data from the legacy fields, anniversary and birthday,
		 * for backward compatibility with versions 0.7.2.6 and older.
		 */
		if ( ! empty( $this->anniversary ) ) {

			$anniversary =  new stdClass();

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
			$birthday =  new stdClass();

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

				extract( $atts );

				/*
				 * Covert to an array if it was supplied as a comma delimited string
				 */
				if ( ! empty( $type ) && ! is_array( $type ) ) $type = explode( ',' , trim( $type ) );

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
					if ( ( $row->type == 'anniversary' ) && ( isset( $results['anniversary'] ) ) && ( substr( $row->date, 5, 5 ) == $results['anniversary']->day ) ) unset( $results['anniversary'] );
					if ( ( $row->type == 'birthday' ) && ( isset( $results['birthday'] ) ) && ( substr( $row->date, 5, 5 ) == $results['birthday']->day ) ) unset( $results['birthday'] );

					/*
					 * // START -- Do not return dates that do not match the supplied $atts.
					 */
					if ( $preferred && ! $row->preferred ) continue;
					if ( ! empty( $type ) && ! in_array( $row->type, $type ) ) continue;
					/*
					 * // END -- Do not return dates that do not match the supplied $atts.
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
				if ( $date->type == 'anniversary' && isset( $results['anniversary'] ) && $date->date == $results['anniversary']->date ) unset( $results['anniversary'] );
				if ( $date->type == 'birthday' && isset( $results['birthday'] ) && $date->date == $results['birthday']->date ) unset( $results['birthday'] );

				/*
				 * If the date type is anniversary or birthday and the date is equal to the date
				 * saved in the legacy fields, unset the data imported from the legacy field.
				 * This is for compatibility with versions 0.7.2.6 and older.
				 *
				 * NOTE: Only the month and day will be compared because the legacy getAnniversary() and getBirthday() methods
				 * will return the year of the next anniversary or birthday. IE: if that date in the current year has already
				 * passed the year would be the next year.
				 */
				if ( ( $date->type == 'anniversary' ) && ( isset( $results['anniversary'] ) ) && ( substr( $date->date, 5, 5 ) == $results['anniversary']->day ) ) unset( $results['anniversary'] );
				if ( ( $date->type == 'birthday' ) && ( isset( $results['birthday'] ) ) && ( substr( $date->date, 5, 5 ) == $results['birthday']->day ) ) unset( $results['birthday'] );

				$results[] = apply_filters( 'cn_date', $date );
			}

		}

		return apply_filters( 'cn_dates', $results );
	}

	/**
	 * Caches the datess for use and preps for saving and updating.
	 *
	 * Valid values as follows.
	 *
	 * $date['id'] (int) Stores the date ID if it was retrieved from the db.
	 * $date['preferred'] (bool) If the date is the preferred date or not.
	 * $date['type'] (string) Stores the date type.
	 * $date['date'] (string) Stores date.
	 * $date['visibility'] (string) Stores the date visibility.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param array   $dates
	 * @return void
	 */
	public function setDates( $dates ) {

		global $connections;

		$userPreferred = NULL;

		/*
		 * These will be used to store the first anniversary and birthday entered by the user.
		 */
		$anniversary = array();
		$birthday = array();

		$validFields = array( 'id' => NULL, 'preferred' => NULL, 'type' => NULL, 'date' => NULL, 'visibility' => NULL );

		if ( !empty( $dates ) ) {

			$order = 0;
			$preferred = '';

			if ( isset( $dates['preferred'] ) ) {
				$preferred = $dates['preferred'];
				unset( $dates['preferred'] );
			}

			foreach ( $dates as $key => $date ) {

				// First validate the supplied data.
				$date = $this->validate->attributesArray( $validFields, $date );

				// If the date is empty, no need to store it.
				if ( empty( $date['date'] ) ) {
					unset( $dates[ $key ] );
					continue;
				}

				// Store the order attribute as supplied in the date array.
				$dates[ $key ]['order'] = $order;

				( ( isset( $preferred ) ) && $preferred == $key ) ? $dates[ $key ]['preferred'] = TRUE : $dates[ $key ]['preferred'] = FALSE;

				/*
				 * If the user set a perferred date, save the $key value.
				 * This is going to be needed because if a date that the user
				 * does not have permission to edit is set to preferred, that date
				 * will have preference.
				 */
				if ( $dates[ $key ]['preferred'] ) $userPreferred = $key;

				/*
				 * Format the supplied date correctly for the table column:  YYYY-MM-DD
				 */
				$currentDate = date_create( $date['date'] );
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

					// If the date is preferred, it takes precedence, so the user's choice is overriden.
					if ( ! empty( $preferred ) && $date['preferred'] ) {
						$dates[ $userPreferred ]['preferred'] = FALSE;

						// Throw the user a message so they know why their choice was overridden.
						$connections->setErrorMessage( 'entry_preferred_overridden_date' );
					}
				}
			}
		}
// var_dump( $dates ); var_dump( $this->birthday ); die();
		( ! empty( $dates ) ) ? $this->dates = serialize( $dates ) : $this->dates = NULL;
	}

	public function getAnniversary( $format = 'F jS' ) {

		if ( ! empty( $this->anniversary ) ) {
			return $this->getUpcoming( 'anniversary', $format );
		} else {
			return '';
		}

	}

	public function setAnniversary( $day, $month ) {

		//Create the anniversary with a default year and time since we don't collect the year. And this is needed so a proper sort can be done when listing them.
		( !empty( $day ) && !empty( $month ) ) ? $this->anniversary = gmmktime( 0, 0, 1, $month, $day, 1970 ) : $this->anniversary = NULL;

	}

	public function getBirthday( $format = 'F jS' ) {

		if ( ! empty( $this->birthday ) ) {
			return $this->getUpcoming( 'birthday', $format );
		} else {
			return '';
		}

	}

	public function setBirthday( $day, $month ) {

		//Create the birthday with a default year and time since we don't collect the year. And this is needed so a proper sort can be done when listing them.
		( !empty( $day ) && !empty( $month ) ) ? $this->birthday = gmmktime( 0, 0, 1, $month, $day, 1970 ) : $this->birthday = NULL;

	}

	/**
	 * Get the date of the entry's next anniversary or birthday. If the date of the anniversary or birthday has not
	 * yet occured in the current year, the current year will be used. If the date has already passed in the current
	 * year the next year will be used.
	 *
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @uses date_i18n()
	 * @param  string $type   The date type to get, anniversary or birthday.
	 * @param  string $format The date format to show the date in. Use PHP date formatting.
	 * @return string         The formatted date.
	 */
	public function getUpcoming( $type, $format = '' ) {

		global $connections;

		if ( empty( $this->$type ) ) return '';

		if ( empty( $format ) ) $format = cnSettingsAPI::get( 'connections', 'connections_display_general', 'date_format' );

		if ( gmmktime( 23, 59, 59, gmdate( 'm', $this->$type ), gmdate( 'd', $this->$type ), gmdate( 'Y', $connections->options->wpCurrentTime ) ) < $connections->options->wpCurrentTime ) {
			$nextUDay = gmmktime( 0, 0, 0, gmdate( 'm', $this->$type ), gmdate( 'd', $this->$type ), gmdate( 'Y', $connections->options->wpCurrentTime ) + 1 );
		}
		else {
			$nextUDay = gmmktime( 0, 0, 0, gmdate( 'm', $this->$type ), gmdate( 'd', $this->$type ), gmdate( 'Y', $connections->options->wpCurrentTime ) );
		}

		// Convert the date to a string to convert to a sting again. Why? Because doing it this way should keep PHP from timezone adjusting the output.
		// date_default_timezone_set('UTC')
		return date_i18n( $format, strtotime( gmdate( 'r', $nextUDay ) ), TRUE );

	}

	public function getBio() {

		return $this->format->sanitizeString( $this->bio, TRUE );

	}

	public function setBio( $bio ) {

		$this->bio = $bio;

	}

	public function getNotes() {

		//return $this->notes;
		return $this->format->sanitizeString( $this->notes, TRUE );

	}

	public function setNotes( $notes ) {

		$this->notes = $notes;

	}

	/**
	 * Create excerpt from the supplied text. Default is the bio.
	 *
	 * Filters:
	 *   cn_excerpt_length => change the default excerpt length of 55 words.
	 *   cn_excerpt_more  => change the default more string of &hellip;
	 *   cn_trim_excerpt  => change returned string
	 *
	 * @param (string)  $atts [optional]
	 * @param (string)  $text [optional]
	 * @return (string)
	 */
	public function getExcerpt( $atts = array(), $text = NULL ) {

		$defaults = array(
			'length' => apply_filters( 'cn_excerpt_length', 55 ),
			'more'   => apply_filters( 'cn_excerpt_more', '&hellip;' )
		);

		$atts = $this->validate->attributesArray( $defaults, $atts );

		$text = empty( $text ) ? $this->getBio() : $this->format->sanitizeString( $text, FALSE );

		$words = preg_split( "/[\n\r\t ]+/", $text, $atts['length'] + 1, PREG_SPLIT_NO_EMPTY );

		if ( count( $words ) > $atts['length'] ) {

			array_pop( $words );
			$text = implode( ' ', $words ) . $atts['more'];

		} else {

			$text = implode( ' ', $words );
		}

		return apply_filters( 'cn_trim_excerpt', $text );
	}

	/**
	 * Returns $visibility.
	 *
	 * @access public
	 * @since unknown
	 * @return string
	 */
	public function getVisibility() {
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
	 * @since unknown
	 * @return (string)
	 */
	public function displayVisibiltyType() {

		$permittedValues = array( 'unlisted' => __( 'Unlisted', 'connections' ), 'public' => __( 'Public', 'connections' ), 'private' => __( 'Private', 'connections' ) );

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
	 * Returns the entry type.
	 *
	 * Valid type are individual, organization and family.
	 *
	 * @return string
	 */
	public function getEntryType() {
		// This is to provide compatibility for versions >= 0.7.0.4
		if ( $this->entryType == 'connection_group' ) $this->entryType = 'family';

		return $this->entryType;
	}

	/**
	 * Sets $entryType.
	 *
	 * @param object  $entryType
	 * @see entry::$entryType
	 */
	public function setEntryType( $entryType ) {
		$this->options['entry']['type'] = $entryType;
		$this->entryType = $entryType;
	}


	public function getLogoDisplay() {
		return $this->logoDisplay;
	}

	public function setLogoDisplay( $logoDisplay ) {
		$this->options['logo']['display'] = $logoDisplay;
	}

	public function getLogoLinked() {
		return $this->logoLinked;
	}

	public function setLogoLinked( $logoLinked ) {
		$this->options['logo']['linked'] = $logoLinked;
	}

	public function getLogoName() {
		if ( empty( $this->options['logo']['name'] ) ) return NULL;
		return $this->options['logo']['name'];
	}

	public function setLogoName( $logoName ) {
		$this->options['logo']['name'] = $logoName;
	}

	/**
	 * Returns $imageDisplay.
	 *
	 * @see entry::$imageDisplay
	 */
	public function getImageDisplay() {
		return $this->imageDisplay;
	}

	/**
	 * Sets $imageDisplay.
	 *
	 * @param object  $imageDisplay
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
	 * @param object  $imageLinked
	 * @see entry::$imageLinked
	 */
	public function setImageLinked( $imageLinked ) {
		$this->options['image']['linked'] = $imageLinked;
	}

	/**
	 * Returns $imageNameCard.
	 *
	 * @see entry::$imageNameCard
	 */
	public function getImageNameCard() {
		if ( empty( $this->options['image']['name']['entry'] ) ) return NULL;
		return $this->options['image']['name']['entry'];
	}

	/**
	 * Sets $imageNameCard.
	 *
	 * @param object  $imageNameCard
	 * @see entry::$imageNameCard
	 */
	public function setImageNameCard( $imageNameCard ) {
		$this->options['image']['name']['entry'] = $imageNameCard;
	}

	/**
	 * Returns $imageNameProfile.
	 *
	 * @see entry::$imageNameProfile
	 */
	public function getImageNameProfile() {
		if ( empty( $this->options['image']['name']['profile'] ) ) return NULL;
		return $this->options['image']['name']['profile'];
	}

	/**
	 * Sets $imageNameProfile.
	 *
	 * @param object  $imageNameProfile
	 * @see entry::$imageNameProfile
	 */
	public function setImageNameProfile( $imageNameProfile ) {
		$this->options['image']['name']['profile'] = $imageNameProfile;
	}

	/**
	 * Returns $imageNameThumbnail.
	 *
	 * @see entry::$imageNameThumbnail
	 */
	public function getImageNameThumbnail() {
		if ( empty( $this->options['image']['name']['thumbnail'] ) ) return NULL;
		return $this->options['image']['name']['thumbnail'];
	}

	/**
	 * Sets $imageNameThumbnail.
	 *
	 * @param object  $imageNameThumbnail
	 * @see entry::$imageNameThumbnail
	 */
	public function setImageNameThumbnail( $imageNameThumbnail ) {
		$this->options['image']['name']['thumbnail'] = $imageNameThumbnail;
	}

	/**
	 * Returns $imageNameOriginal.
	 *
	 * @see entry::$imageNameOriginal
	 */
	public function getImageNameOriginal() {
		if ( empty( $this->options['image']['name']['original'] ) ) return NULL;
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

	public function getAddedBy() {
		$addedBy = get_userdata( $this->addedBy );

		if ( $addedBy ) {
			return $addedBy->display_name;
		}
		else {
			return 'Unknown';
		}
	}

	public function getSortColumn() {
		return $this->sortColumn;
	}

	public function getEditedBy() {
		$editedBy = get_userdata( $this->editedBy );

		if ( $editedBy ) {
			return $editedBy->display_name;
		}
		else {
			return __( 'Unknown', 'connections' );
		}
	}

	/**
	 * Returns the entry's status.
	 *
	 * @access public
	 * @since unknown
	 * @return (string)
	 */
	public function getStatus() {
		return sanitize_key( $this->status );
	}

	/**
	 * Sets the entry's status to one of the permitted values.
	 *
	 * @access public
	 * @since unknown
	 * @param (string) $status
	 * @return void
	 */
	public function setStatus( $status ) {

		$permittedValues = array( 'approved', 'pending' );

		$this->status = in_array( $status, $permittedValues ) ? sanitize_key( $status ) : 'pending';

	}


	/**
	 * Returns $options.
	 *
	 * @see entry::$options
	 */
	private function getOptions() {
		return $this->options;
	}

	/**
	 * Sets $options.
	 *
	 * @param object  $options
	 * @see entry::$options
	 */
	private function serializeOptions() {
		$this->options = serialize( $this->options );
	}


	public function set( $id ) {
		global $connections;
		$result = $connections->retrieve->entry( $id );
		$this->__construct( $result );
	}

	public function update() {
		global $wpdb, $connections;

		$this->serializeOptions();

		// Ensure fields that should be empty depending on the entry type.
		switch ( $this->getEntryType() ) {
			case 'individual':
				$this->familyName       = '';
				$this->familyMembers    = '';
				$this->contactFirstName = '';
				$this->contactLastName  = '';
				break;

			case 'organization':
				$this->familyName      = '';
				$this->honorificPrefix = '';
				$this->firstName       = '';
				$this->middleName      = '';
				$this->lastName        = '';
				$this->honorific       = '';
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
				$this->entryType  = 'individual';
				$this->familyName = '';
				break;
		}

		$wpdb->show_errors = true;

		/*
		 * Check to see if there is a slug; if not go fetch one.
		 */
		if ( empty( $this->slug ) ) $this->slug = $this->getUniqueSlug();

		$result = $wpdb->query( $wpdb->prepare(
			'UPDATE ' . CN_ENTRY_TABLE . ' SET
			ts                 = %s,
			entry_type         = %s,
			visibility         = %s,
			slug               = %s,
			honorific_prefix   = %s,
			first_name         = %s,
			middle_name        = %s,
			last_name          = %s,
			honorific_suffix   = %s,
			title              = %s,
			organization       = %s,
			department         = %s,
			contact_first_name = %s,
			contact_last_name  = %s,
			family_name        = %s,
			birthday           = %s,
			anniversary        = %s,
			addresses          = %s,
			phone_numbers      = %s,
			email              = %s,
			im                 = %s,
			social             = %s,
			links              = %s,
			dates              = %s,
			options            = %s,
			bio                = %s,
			notes              = %s,
			edited_by          = %d,
			user               = %d,
			status             = %s
			WHERE id           = %d',
			current_time( 'mysql' ),
			$this->entryType,
			$this->visibility,
			$this->slug,
			$this->honorificPrefix,
			$this->firstName,
			$this->middleName,
			$this->lastName,
			$this->honorificSuffix,
			$this->title,
			$this->organization,
			$this->department,
			$this->contactFirstName,
			$this->contactLastName,
			$this->familyName,
			$this->birthday,
			$this->anniversary,
			$this->addresses,
			$this->phoneNumbers,
			$this->emailAddresses,
			$this->im,
			$this->socialMedia,
			$this->links,
			$this->dates,
			$this->options,
			$this->bio,
			$this->notes,
			$connections->currentUser->getID(),
			$this->getUser(),
			$this->status,
			$this->id
			)
		);

		//print_r($wpdb->last_query);

		/*
		 * Only update the rest of the entry's data if the update to the ENTRY TABLE was successful.
		 */
		if ( $result !== FALSE ) {

			$where[] = 'WHERE 1=1';

			/*
			 * Retrieve entry details from the object caches
			 */
			$addresses = $this->getAddresses( array(), TRUE, TRUE );
			$phoneNumbers = $this->getPhoneNumbers( array(), TRUE, TRUE );
			$emailAddresses = $this->getEmailAddresses( array(), TRUE, TRUE );
			$imIDs = $this->getIm( array(), TRUE, TRUE );
			$socialNetworks = $this->getSocialMedia( array(), TRUE, TRUE );
			$links = $this->getLinks( array(), TRUE, TRUE );
			$dates = $this->getDates( array(), TRUE, TRUE );

			/*
			 * Create a sql segment for the entry ID that can be used in the queries.
			 */
			$where[] = 'AND `entry_id` = "' . $this->getId() . '"';

			/*
			 * Create an array to store the which records by visibility the user can edit.
			 * This is done to avoid removing or editing any records the user isn't permitted access.
			 */
			$notPermitted = array();
			if ( ! current_user_can( 'connections_view_public' ) ) $notPermitted[] = 'public';
			if ( ! current_user_can( 'connections_view_private' ) ) $notPermitted[] = 'private';
			if ( ! current_user_can( 'connections_view_unlisted' ) ) $notPermitted[] = 'unlisted';

			/*
			 * Create a sql segment for the visibility that can be used in the queries.
			 */
			$where['visibility'] = ! empty( $notPermitted ) ? 'AND `visibility` NOT IN (\'' . implode( "', '", $notPermitted ) . '\')' : '';

			/*
			 * Update and add addresses as necessary and removing the rest unless the current user does not have permission to view/edit.
			 */
			$keepIDs = array();

			if ( ! empty( $addresses ) ) {
				foreach ( $addresses as $address ) {
					/*
					 * If the $address->id is set, this address is already in the db so it will be updated.
					 * If the $address->id was not set, the add the address to the db.
					 */
					if ( isset( $address->id ) && ! empty( $address->id ) ) {
						$wpdb->query( $wpdb->prepare ( 'UPDATE ' . CN_ENTRY_ADDRESS_TABLE . ' SET
													`entry_id`			= %d,
													`order`				= %d,
													`preferred`			= %d,
													`type`				= %s,
													`line_1`			= %s,
													`line_2`			= %s,
													`line_3`			= %s,
													`city`				= %s,
													`state`				= %s,
													`zipcode`			= %s,
													`country`			= %s,
													`latitude`			= %f,
													`longitude`			= %f,
													`visibility`		= %s
													WHERE `id` 			= %d',
								$this->getId(),
								$address->order,
								$address->preferred,
								$address->type,
								$address->line_1,
								$address->line_2,
								$address->line_3,
								$address->city,
								$address->state,
								$address->zipcode,
								$address->country,
								$address->latitude,
								$address->longitude,
								$address->visibility,
								$address->id ) );

						// Save the address IDs that have been updated
						$keepIDs[] = $address->id;

					}
					else {
						$wpdb->query( $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_ADDRESS_TABLE . ' SET
														`entry_id`			= %d,
														`order`				= %d,
														`preferred`			= %d,
														`type`				= %s,
														`line_1`			= %s,
														`line_2`			= %s,
														`line_3`			= %s,
														`city`				= %s,
														`state`				= %s,
														`zipcode`			= %s,
														`country`			= %s,
														`latitude`			= %f,
														`longitude`			= %f,
														`visibility`		= %s',
								$this->getId(),
								$address->order,
								$address->preferred,
								$address->type,
								$address->line_1,
								$address->line_2,
								$address->line_3,
								$address->city,
								$address->state,
								$address->zipcode,
								$address->country,
								$address->latitude,
								$address->longitude,
								$address->visibility ) );

						// Save the address IDs that have been added
						$keepIDs[] = $wpdb->insert_id;
					}
				}
			}

			/*
			 * Now delete all the address IDs that have not been added/updated and
			 * make sure not to delete the entries that the user does not have permission to view/edit.
			 */
			//( ! empty($keepIDs) ) ? $IDs = '\'' . implode("', '", (array) $keepIDs) . '\'' : $IDs = '';
			if ( ! empty( $keepIDs ) ) $where['addresses'] = 'AND `id` NOT IN (\'' . implode( '\', \'', (array) $keepIDs ) . '\')';

			/*if ( ! empty($IDs) )
			{
				$sql = 'SELECT * FROM `' . CN_ENTRY_ADDRESS_TABLE . '` WHERE `entry_id` = "' . $this->getId() . '" AND `id` NOT IN ( ' . $IDs . ' ) ' . $sqlVisibility;

				$results = $wpdb->get_col( $sql );

				if ( ! empty($results) ) $wpdb->query( 'DELETE FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE `id` IN (\'' . implode("', '", (array) $results) . '\')' );
			}*/

			$wpdb->query( 'DELETE FROM `' . CN_ENTRY_ADDRESS_TABLE . '` ' . implode( ' ', $where ) );
			if ( isset( $where['addresses'] ) ) unset( $where['addresses'] );


			/*
			 * Update and add the phone numbers as necessary and removing the rest unless the current user does not have permission to view/edit.
			 */
			$keepIDs = array();

			if ( ! empty( $phoneNumbers ) ) {
				foreach ( $phoneNumbers as $phone ) {
					/*
					 * If the $phone->id is set, this phone number is already in the db so it will be updated.
					 * If the $phone->id was not set, the add the phone number to the db.
					 */
					if ( isset( $phone->id ) && ! empty( $phone->id ) ) {
						$wpdb->query( $wpdb->prepare ( 'UPDATE ' . CN_ENTRY_PHONE_TABLE . ' SET
													`entry_id`			= %d,
													`order`				= %d,
													`preferred`			= %d,
													`type`				= %s,
													`number`			= %s,
													`visibility`		= %s
													WHERE `id` 			= %d',
								$this->getId(),
								$phone->order,
								$phone->preferred,
								$phone->type,
								$phone->number,
								$phone->visibility,
								$phone->id ) );

						// Save the phone number IDs that have been updated
						$keepIDs[] = $phone->id;

					}
					else {
						$wpdb->query( $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_PHONE_TABLE . ' SET
														`entry_id`			= %d,
														`order`				= %d,
														`preferred`			= %d,
														`type`				= %s,
														`number`			= %s,
														`visibility`		= %s',
								$this->getId(),
								$phone->order,
								$phone->preferred,
								$phone->type,
								$phone->number,
								$phone->visibility ) );

						// Save the phone number IDs that have been added
						$keepIDs[] = $wpdb->insert_id;
					}
				}
			}

			/*
			 * Now delete all the phone numbers that have not been added/updated and
			 * make sure not to delete the entries that the user does not have permission to view/edit.
			 */
			if ( ! empty( $keepIDs ) ) $where['phone'] = 'AND `id` NOT IN (\'' . implode( '\', \'', (array) $keepIDs ) . '\')';

			$wpdb->query( 'DELETE FROM `' . CN_ENTRY_PHONE_TABLE . '` ' . implode( ' ', $where ) );
			if ( isset( $where['phone'] ) ) unset( $where['phone'] );


			/*
			 * Update and add the email addresses as necessary and removing the rest unless the current user does not have permission to view/edit.
			 */
			$keepIDs = array();

			if ( ! empty( $emailAddresses ) ) {
				foreach ( $emailAddresses as $email ) {
					/*
					 * If the $email->id is set, this email address is already in the db so it will be updated.
					 * If the $email->id was not set, the add the email address to the db.
					 */
					if ( isset( $email->id ) && ! empty( $email->id ) ) {
						$wpdb->query( $wpdb->prepare ( 'UPDATE ' . CN_ENTRY_EMAIL_TABLE . ' SET
													`entry_id`			= %d,
													`order`				= %d,
													`preferred`			= %d,
													`type`				= %s,
													`address`			= %s,
													`visibility`		= %s
													WHERE `id` 			= %d',
								$this->getId(),
								$email->order,
								$email->preferred,
								$email->type,
								$email->address,
								$email->visibility,
								$email->id ) );

						// Save the email address IDs that have been updated
						$keepIDs[] = $email->id;

					}
					else {
						$wpdb->query( $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_EMAIL_TABLE . ' SET
														`entry_id`			= %d,
														`order`				= %d,
														`preferred`			= %d,
														`type`				= %s,
														`address`			= %s,
														`visibility`		= %s',
								$this->getId(),
								$email->order,
								$email->preferred,
								$email->type,
								$email->address,
								$email->visibility ) );

						// Save the email address IDs that have been added
						$keepIDs[] = $wpdb->insert_id;
					}
				}
			}

			/*
			 * Now delete all the social network IDs that have not been added/updated and
			 * make sure not to delete the entries that the user does not have permission to view/edit.
			 */
			if ( ! empty( $keepIDs ) ) $where['email'] = 'AND `id` NOT IN (\'' . implode( '\', \'', (array) $keepIDs ) . '\')';

			$wpdb->query( 'DELETE FROM `' . CN_ENTRY_EMAIL_TABLE . '` ' . implode( ' ', $where ) );
			if ( isset( $where['email'] ) ) unset( $where['email'] );


			/*
			 * Update and add the IMs as necessary and removing the rest unless the current user does not have permission to view/edit.
			 */
			$keepIDs = array();

			if ( ! empty( $imIDs ) ) {
				foreach ( $imIDs as $network ) {
					/*
					 * If the $network->id is set, this IM ID is already in the db so it will be updated.
					 * If the $network->id was not set, the add the IM ID to the db.
					 */
					if ( isset( $network->uid ) && ! empty( $network->uid ) ) {
						$wpdb->query( $wpdb->prepare ( 'UPDATE ' . CN_ENTRY_MESSENGER_TABLE . ' SET
													`entry_id`			= %d,
													`order`				= %d,
													`preferred`			= %d,
													`type`				= %s,
													`uid`				= %s,
													`visibility`		= %s
													WHERE `id` 			= %d',
								$this->getId(),
								$network->order,
								$network->preferred,
								$network->type,
								$network->id,
								$network->visibility,
								$network->uid ) );

						// Save the IM IDs that have been updated
						$keepIDs[] = $network->uid;

					}
					else {
						$wpdb->query( $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_MESSENGER_TABLE . ' SET
														`entry_id`			= %d,
														`order`				= %d,
														`preferred`			= %d,
														`type`				= %s,
														`uid`				= %s,
														`visibility`		= %s',
								$this->getId(),
								$network->order,
								$network->preferred,
								$network->type,
								$network->id,
								$network->visibility ) );

						// Save the IM IDs that have been added
						$keepIDs[] = $wpdb->insert_id;
					}
				}
			}

			/*
			 * Now delete all the IM network IDs that have not been added/updated and
			 * make sure not to delete the entries that the user does not have permission to view/edit.
			 */
			if ( ! empty( $keepIDs ) ) $where['im'] = 'AND `id` NOT IN (\'' . implode( '\', \'', (array) $keepIDs ) . '\')';

			$wpdb->query( 'DELETE FROM `' . CN_ENTRY_MESSENGER_TABLE . '` ' . implode( ' ', $where ) );
			if ( isset( $where['im'] ) ) unset( $where['im'] );


			/*
			 * Update and add the social networks as necessary and removing the rest unless the current user does not have permission to view/edit.
			 */
			$keepIDs = array();

			if ( ! empty( $socialNetworks ) ) {
				foreach ( $socialNetworks as $network ) {
					/*
					 * If the $network->id is set, this IM ID is already in the db so it will be updated.
					 * If the $network->id was not set, the add the social network to the db.
					 */
					if ( isset( $network->id ) && ! empty( $network->id ) ) {
						$wpdb->query( $wpdb->prepare ( 'UPDATE ' . CN_ENTRY_SOCIAL_TABLE . ' SET
													`entry_id`			= %d,
													`order`				= %d,
													`preferred`			= %d,
													`type`				= %s,
													`url`				= %s,
													`visibility`		= %s
													WHERE `id` 			= %d',
								$this->getId(),
								$network->order,
								$network->preferred,
								$network->type,
								$network->url,
								$network->visibility,
								$network->id ) );

						// Save the social networks IDs that have been updated
						$keepIDs[] = $network->id;

					}
					else {
						$wpdb->query( $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_SOCIAL_TABLE . ' SET
														`entry_id`			= "%d",
														`order`				= "%d",
														`preferred`			= "%d",
														`type`				= "%s",
														`url`				= "%s",
														`visibility`		= "%s"',
								$this->getId(),
								$network->order,
								$network->preferred,
								$network->type,
								$network->url,
								$network->visibility ) );

						// Save the social networks IDs that have been added
						$keepIDs[] = $wpdb->insert_id;
					}
				}
			}

			/*
			 * Now delete all the social network IDs that have not been added/updated and
			 * make sure not to delete the entries that the user does not have permission to view/edit.
			 */
			if ( ! empty( $keepIDs ) ) $where['social'] = 'AND `id` NOT IN (\'' . implode( '\', \'', (array) $keepIDs ) . '\')';

			$wpdb->query( 'DELETE FROM `' . CN_ENTRY_SOCIAL_TABLE . '` ' . implode( ' ', $where ) );
			if ( isset( $where['social'] ) ) unset( $where['social'] );


			/*
			 * Update and add the links as necessary and removing the rest unless the current user does not have permission to view/edit.
			 */
			$keepIDs = array();

			if ( ! empty( $links ) ) {
				foreach ( $links as $link ) {
					/*
					 * If the $link->id is set, this link ID is already in the db so it will be updated.
					 * If the $link->id was not set, the add the link to the db.
					 */
					if ( isset( $link->id ) && ! empty( $link->id ) ) {
						$wpdb->query( $wpdb->prepare ( 'UPDATE ' . CN_ENTRY_LINK_TABLE . ' SET
													`entry_id`			= %d,
													`order`				= %d,
													`preferred`			= %d,
													`type`				= %s,
													`title`				= %s,
													`url`				= %s,
													`target`			= %s,
													`follow`			= %d,
													`image`				= %d,
													`logo`				= %d,
													`visibility`		= %s
													WHERE `id` 			= %d',
								$this->getId(),
								$link->order,
								$link->preferred,
								$link->type,
								$link->title,
								$link->url,
								$link->target,
								(int) $link->follow,
								(int) $link->image,
								(int) $link->logo,
								$link->visibility,
								$link->id ) );

						// Save the links IDs that have been updated
						$keepIDs[] = $link->id;

					}
					else {
						$wpdb->query( $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_LINK_TABLE . ' SET
														`entry_id`			= %d,
														`order`				= %d,
														`preferred`			= %d,
														`type`				= %s,
														`title`				= %s,
														`url`				= %s,
														`target`			= %s,
														`follow`			= %d,
														`image`				= %d,
														`logo`				= %d,
														`visibility`		= %s',
								$this->getId(),
								$link->order,
								$link->preferred,
								$link->type,
								$link->title,
								$link->url,
								$link->target,
								(int) $link->follow,
								(int) $link->image,
								(int) $link->logo,
								$link->visibility ) );

						// Save the link IDs that have been added
						$keepIDs[] = $wpdb->insert_id;
					}
				}
			}

			/*
			 * Now delete all the link IDs that have not been added/updated and
			 * make sure not to delete the entries that the user does not have permission to view/edit.
			 */
			if ( ! empty( $keepIDs ) ) $where['links'] = 'AND `id` NOT IN (\'' . implode( '\', \'', (array) $keepIDs ) . '\')';

			$wpdb->query( 'DELETE FROM `' . CN_ENTRY_LINK_TABLE . '` ' . implode( ' ', $where ) );
			if ( isset( $where['links'] ) ) unset( $where['links'] );


			/*
			 * Update and add the dates as necessary and removing the rest unless the current user does not have permission to view/edit.
			 */
			$keepIDs = array();

			if ( ! empty( $dates ) ) {
				foreach ( $dates as $date ) {
					/*
					 * If the $date->id is set, this date is already in the db so it will be updated.
					 * If the $date->id was not set, the add the date to the db.
					 */
					if ( isset( $date->id ) && ! empty( $date->id ) ) {
						$wpdb->query( $wpdb->prepare ( 'UPDATE ' . CN_ENTRY_DATE_TABLE . ' SET
													`entry_id`			= %d,
													`order`				= %d,
													`preferred`			= %d,
													`type`				= %s,
													`date`				= %s,
													`visibility`		= %s
													WHERE `id` 			= %d',
								$this->getId(),
								$date->order,
								$date->preferred,
								$date->type,
								$date->date,
								$date->visibility,
								$date->id ) );

						// Save the date IDs that have been updated
						$keepIDs[] = $date->id;

					}
					else {
						$wpdb->query( $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_DATE_TABLE . ' SET
														`entry_id`			= %d,
														`order`				= %d,
														`preferred`			= %d,
														`type`				= %s,
														`date`				= %s,
														`visibility`		= %s',
								$this->getId(),
								$date->order,
								$date->preferred,
								$date->type,
								$date->date,
								$date->visibility ) );

						// Save the date IDs that have been added
						$keepIDs[] = $wpdb->insert_id;
					}
				}
			}

			/*
			 * Now delete all the dates that have not been added/updated and
			 * make sure not to delete the entries that the user does not have permission to view/edit.
			 */
			if ( ! empty( $keepIDs ) ) $where['date'] = 'AND `id` NOT IN (\'' . implode( '\', \'', (array) $keepIDs ) . '\')';

			$wpdb->query( 'DELETE FROM `' . CN_ENTRY_DATE_TABLE . '` ' . implode( ' ', $where ) );
			if ( isset( $where['date'] ) ) unset( $where['date'] );
		}

		$wpdb->show_errors = FALSE;

		do_action( 'cn_process_update-entry', $this );

		return $result;
	}

	public function save() {
		global $wpdb, $connections;

		$this->serializeOptions();

		// Ensure fields that should be empty depending on the entry type.
		switch ( $this->getEntryType() ) {
			case 'individual':
				$this->familyName    = '';
				$this->familyMembers = '';
				break;

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
				$this->honorificPrefix = '';
				$this->firstName       = '';
				$this->middleName      = '';
				$this->lastName        = '';
				$this->honorificSuffix = '';
				$this->title           = '';
				$this->birthday        = '';
				$this->anniversary     = '';
				break;

			default:
				$this->entryType  = 'individual';
				$this->familyName = '';
				break;
		}

		$wpdb->show_errors = true;

		/*
		 * Check to see if there is a slug; if not go fetch one.
		 * NOTE: When adding a new entry, a new unique slug should be created and set.
		 */
		/*if ( empty( $this->slug ) )*/ $this->slug = $this->getUniqueSlug();

		$sql = $wpdb->prepare(
			'INSERT INTO ' . CN_ENTRY_TABLE . ' SET
			ts                 = %s,
			date_added         = %d,
			entry_type         = %s,
			visibility         = %s,
			slug               = %s,
			family_name        = %s,
			honorific_prefix   = %s,
			first_name         = %s,
			middle_name        = %s,
			last_name          = %s,
			honorific_suffix   = %s,
			title              = %s,
			organization       = %s,
			department         = %s,
			contact_first_name = %s,
			contact_last_name  = %s,
			addresses          = %s,
			phone_numbers      = %s,
			email              = %s,
			im                 = %s,
			social             = %s,
			links              = %s,
			dates              = %s,
			birthday           = %s,
			anniversary        = %s,
			bio                = %s,
			notes              = %s,
			options            = %s,
			added_by           = %d,
			edited_by          = %d,
			owner              = %d,
			user               = %d,
			status             = %s',
			current_time( 'mysql' ),
			current_time( 'timestamp' ),
			$this->entryType,
			$this->visibility,
			$this->slug,
			$this->familyName,
			$this->honorificPrefix,
			$this->firstName,
			$this->middleName,
			$this->lastName,
			$this->honorificSuffix,
			$this->title,
			$this->organization,
			$this->department,
			$this->contactFirstName,
			$this->contactLastName,
			$this->addresses,
			$this->phoneNumbers,
			$this->emailAddresses,
			$this->im,
			$this->socialMedia,
			$this->links,
			$this->dates,
			$this->birthday,
			$this->anniversary,
			$this->bio,
			$this->notes,
			$this->options,
			$connections->currentUser->getID(),
			$connections->currentUser->getID(),
			$connections->currentUser->getID(),
			$this->getUser(),
			$this->status
		);

		$result = $wpdb->query( $sql );

		$connections->lastQuery = $wpdb->last_query;
		$connections->lastQueryError = $wpdb->last_error;
		$connections->lastInsertID = $wpdb->insert_id;

		if ( $result !== FALSE ) {
			$addresses = $this->getAddresses( array(), TRUE, TRUE );
			$phoneNumbers = $this->getPhoneNumbers( array(), TRUE, TRUE );
			$emailAddresses = $this->getEmailAddresses( array(), TRUE, TRUE );
			$imIDs = $this->getIm( array(), TRUE, TRUE );
			$socialNetworks = $this->getSocialMedia( array(), TRUE, TRUE );
			$links = $this->getLinks( array(), TRUE, TRUE );
			$dates = $this->getDates( array(), TRUE, TRUE );

			if ( ! empty( $addresses ) ) {
				foreach ( $addresses as $address ) {
					$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_ADDRESS_TABLE . ' SET
											`entry_id`			= %d,
											`order`				= %d,
											`preferred`			= %d,
											`type`				= %s,
											`line_1`			= %s,
											`line_2`			= %s,
											`line_3`			= %s,
											`city`				= %s,
											`state`				= %s,
											`zipcode`			= %s,
											`country`			= %s,
											`latitude`			= %f,
											`longitude`			= %f,
											`visibility`		= %s',
						$connections->lastInsertID,
						$address->order,
						$address->preferred,
						$address->type,
						$address->line_1,
						$address->line_2,
						$address->line_3,
						$address->city,
						$address->state,
						$address->zipcode,
						$address->country,
						$address->latitude,
						$address->longitude,
						$address->visibility );

					$wpdb->query( $sql );
				}
			}

			if ( ! empty( $phoneNumbers ) ) {
				foreach ( $phoneNumbers as $phone ) {
					$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_PHONE_TABLE . ' SET
											`entry_id`			= %d,
											`order`				= %d,
											`preferred`			= %d,
											`type`				= %s,
											`number`			= %s,
											`visibility`		= %s',
						$connections->lastInsertID,
						$phone->order,
						$phone->preferred,
						$phone->type,
						$phone->number,
						$phone->visibility );

					$wpdb->query( $sql );
				}
			}

			if ( ! empty( $emailAddresses ) ) {
				foreach ( $emailAddresses as $email ) {
					$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_EMAIL_TABLE . ' SET
											`entry_id`			= %d,
											`order`				= %d,
											`preferred`			= %d,
											`type`				= %s,
											`address`			= %s,
											`visibility`		= %s',
						$connections->lastInsertID,
						$email->order,
						$email->preferred,
						$email->type,
						$email->address,
						$email->visibility );

					$wpdb->query( $sql );
				}
			}

			if ( ! empty( $imIDs ) ) {
				foreach ( $imIDs as $network ) {
					$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_MESSENGER_TABLE . ' SET
											`entry_id`			= %d,
											`order`				= %d,
											`preferred`			= %d,
											`type`				= %s,
											`uid`				= %s,
											`visibility`		= %s',
						$connections->lastInsertID,
						$network->order,
						$network->preferred,
						$network->type,
						$network->id,
						$network->visibility );

					$wpdb->query( $sql );
				}
			}

			if ( ! empty( $socialNetworks ) ) {
				foreach ( $socialNetworks as $network ) {
					$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_SOCIAL_TABLE . ' SET
											`entry_id`			= %d,
											`order`				= %d,
											`preferred`			= %d,
											`type`				= %s,
											`url`				= %s,
											`visibility`		= %s',
						$connections->lastInsertID,
						$network->order,
						$network->preferred,
						$network->type,
						$network->url,
						$network->visibility );

					$wpdb->query( $sql );
				}
			}

			if ( ! empty( $links ) ) {
				foreach ( $links as $link ) {
					$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_LINK_TABLE . ' SET
											`entry_id`			= %d,
											`order`				= %d,
											`preferred`			= %d,
											`type`				= %s,
											`title`				= %s,
											`url`				= %s,
											`target`			= %s,
											`follow`			= %d,
											`image`				= %d,
											`logo`				= %d,
											`visibility`		= %s',
						$connections->lastInsertID,
						$link->order,
						$link->preferred,
						$link->type,
						$link->title,
						$link->url,
						$link->target,
						(int) $link->follow,
						(int) $link->image,
						(int) $link->logo,
						$link->visibility );

					$wpdb->query( $sql );
				}
			}

			if ( ! empty( $dates ) ) {
				foreach ( $dates as $date ) {
					$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_DATE_TABLE . ' SET
											`entry_id`			= %d,
											`order`				= %d,
											`preferred`			= %d,
											`type`				= %s,
											`date`				= %s,
											`visibility`		= %s',
						$connections->lastInsertID,
						$date->order,
						$date->preferred,
						$date->type,
						$date->date,
						$date->visibility );

					$wpdb->query( $sql );
				}
			}
		}

		$wpdb->show_errors = FALSE;

		do_action( 'cn_process_save-entry', $this );

		return $result;
	}

	public function delete( $id ) {
		global $wpdb, $connections;

		/*
		 * Delete images assigned to the entry.
		 *
		 * Versions previous to 0.6.2.1 did not not make a duplicate copy of images when
		 * copying an entry so it was possible multiple entries could share the same image.
		 * Only images created after the date that version .0.7.0.0 was released will be deleted,
		 * plus a couple weeks for good measure.
		 */

		$compatiblityDate = mktime( 0, 0, 0, 6, 1, 2010 );

		if ( is_file( CN_IMAGE_PATH . $this->getImageNameOriginal() ) ) {
			if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $this->getImageNameOriginal() ) ) unlink( CN_IMAGE_PATH . $this->getImageNameOriginal() );
		}

		if ( is_file( CN_IMAGE_PATH . $this->getImageNameThumbnail() ) ) {
			if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $this->getImageNameThumbnail() ) ) unlink( CN_IMAGE_PATH . $this->getImageNameThumbnail() );
		}

		if ( is_file( CN_IMAGE_PATH . $this->getImageNameCard() ) ) {
			if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $this->getImageNameCard() ) ) unlink( CN_IMAGE_PATH . $this->getImageNameCard() );
		}

		if ( is_file( CN_IMAGE_PATH . $this->getImageNameProfile() ) ) {
			if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $this->getImageNameProfile() ) ) unlink( CN_IMAGE_PATH . $this->getImageNameProfile() );
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

		do_action( 'cn_process_delete-entry', $this );
	}

}