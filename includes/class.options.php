<?php

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
	 * Callback for the "Login Required" settings field.
	 * This ensure all roles are set to have the connections_view_public
	 * capability to ensures all roles can at least view the public entries.
	 *
	 * @access private
	 * @since 0.7.3
	 * @return int
	 */
	public function setAllowPublic( $loginRequired ) {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

		$currentRoles = $wp_roles->get_names();

		if ( $loginRequired ) {
			//$this->allowPublic = TRUE;

			foreach ( $currentRoles as $role => $name ) {
				$this->addCapability( $role, 'connections_view_public' );
			}
		}

		return $loginRequired;
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

	public function hasCapability( $role, $cap ) {
		global $wp_roles;

		/*
		 * Check to make sure $wp_roles has been initialized and set.
		 * If it hasn't it is initialized. This was done because this method
		 * can be called before the $wp_roles has been initialized.
		 */
		if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

		$wpRoleDataArray = $wp_roles->roles;
		$wpRoleCaps = $wpRoleDataArray[$role]['capabilities'];
		$wpRole = new WP_Role( $role, $wpRoleCaps );

		return $wpRole->has_cap( $cap );
	}

	public function addCapability( $role, $cap ) {
		global $wp_roles;

		/*
		 * Check to make sure $wp_roles has been initialized and set.
		 * If it hasn't it is initialized. This was done because this method
		 * can be called before the $wp_roles has been initialized.
		 */
		if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

		//$wpRole = get_role($role);
		if ( ! $this->hasCapability( $role, $cap ) ) $wp_roles->add_cap( $role, $cap );
	}

	public function removeCapability( $role, $cap ) {
		global $wp_roles;

		/*
		 * Check to make sure $wp_roles has been initialized and set.
		 * If it hasn't it is initialized. This was done because this method
		 * can be called before the $wp_roles has been initialized.
		 */
		if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

		//$wpRole = get_role($role);
		if ( $this->hasCapability( $role, $cap ) ) $wp_roles->remove_cap( $role, $cap );
	}

	/**
	 * Returns an array of the default capabilities.
	 *
	 * @access private
	 * @since unknown
	 * @return array
	 */
	public function getDefaultCapabilities() {
		return array(
			'connections_view_menu'            => __( 'View Admin Menu', 'connections' ),
			'connections_view_dashboard'       => __( 'View Dashboard', 'connections' ),
			'connections_manage'               => __( 'View List (Manage)', 'connections' ),
			'connections_add_entry'            => __( 'Add Entry', 'connections' ),
			'connections_add_entry_moderated'  => __( 'Add Entry Moderated', 'connections' ),
			'connections_edit_entry'           => __( 'Edit Entry', 'connections' ),
			'connections_edit_entry_moderated' => __( 'Edit Entry Moderated', 'connections' ),
			'connections_delete_entry'         => __( 'Delete Entry', 'connections' ),
			'connections_view_public'          => __( 'View Public Entries', 'connections' ),
			'connections_view_private'         => __( 'View Private Entries', 'connections' ),
			'connections_view_unlisted'        => __( 'View Unlisted Entries', 'connections' ),
			'connections_edit_categories'      => __( 'Edit Categories', 'connections' ),
			'connections_change_settings'      => __( 'Change Settings', 'connections' ),
			'connections_manage_template'      => __( 'Manage Templates', 'connections' ),
			'connections_change_roles'         => __( 'Change Role Capabilities', 'connections' )
		);
	}

	/**
	 * Reset all Connections' user role capabilities back to their default.
	 * If a role has been supplied, that role will have its capabilities
	 * reset to its defaults.
	 *
	 * @access private
	 * @since unknown
	 * @return void
	 */
	public function setDefaultCapabilities( $rolesToReset = NULL ) {
		global $wp_roles;

		/*
		 * Check to make sure $wp_roles has been initialized and set.
		 * If it hasn't it is initialized. This was done because this method
		 * can be called before the $wp_roles has been initialized.
		 */
		if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

		/**
		 * These are the roles that will default to having full access
		 * to all capabilites. This is to maintain plugin behavior that
		 * exisited prior to adding role/capability support.
		 */
		$defaultRoles = array( 'administrator', 'editor', 'author' );

		/**
		 * If no roles are supplied to the method to reset; the method
		 * will reset the capabilies of all roles defined.
		 */
		if ( ! isset( $rolesToReset ) ) $rolesToReset = $wp_roles->get_names();

		foreach ( $rolesToReset as $role => $name ) {
			$wpRole = get_role( $role );

			if ( in_array( $role, $defaultRoles ) ) {
				foreach ( $this->getDefaultCapabilities() as $cap => $name ) {
					if ( !$this->hasCapability( $role, $cap ) ) $wpRole->add_cap( $cap );
				}
			}
			else {
				foreach ( $this->getDefaultCapabilities() as $cap => $name ) {
					if ( $this->hasCapability( $role, $cap ) ) $wpRole->remove_cap( $cap );
				}
			}
		}

		// Make sure the capability to view public entries is set for all roles.
		$this->setAllowPublic( TRUE );
	}

	public function removeDefaultCapabilities() {
		global $wp_roles;

		$rolesToReset = $wp_roles->get_names();

		foreach ( $rolesToReset as $role => $name ) {
			$wpRole = get_role( $role );

			foreach ( $this->getDefaultCapabilities() as $cap => $name ) {
				if ( $this->hasCapability( $role, $cap ) ) $wpRole->remove_cap( $cap );
			}
		}
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

	/**
	 * Returns the active templates by type.
	 *
	 * @param string  $type
	 * @return object || NULL
	 */
	public function getActiveTemplate( $type ) {
		( !empty( $this->activeTemplates[$type] ) ) ? $result = (object) $this->activeTemplates[$type] : $result = NULL;
		return $result;
	}

	/**
	 * Sets $activeTemplate by type.
	 *
	 * @param string  $type
	 * @param object  $activeTemplate
	 */
	public function setActiveTemplate( $type, $activeTemplate ) {
		$this->activeTemplates[$type] = (array) $activeTemplate;
	}

	public function setDefaultTemplates() {
		$templates = new cnTemplate();
		$templates->buildCatalog();

		$all = $templates->getCatalog( 'all' );
		$anniversary = $templates->getCatalog( 'anniversary' );
		$birthday = $templates->getCatalog( 'birthday' );

		$this->setActiveTemplate( 'all', $all->card );
		$this->setActiveTemplate( 'individual', $all->card );
		$this->setActiveTemplate( 'organization', $all->card );
		$this->setActiveTemplate( 'family', $all->card );
		$this->setActiveTemplate( 'anniversary', $anniversary->{'anniversary-light'} );
		$this->setActiveTemplate( 'birthday', $birthday->{'birthday-light'} );

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
			'sister'           => __( 'Sister', 'connections' ),
			'sisterinlaw'      => __( 'Sister-in-law', 'connections' ),
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
	 * Return "1" if FULLTEXT support is enabled, otherwise, returns empty.
	 *
	 * @deprecated since 0.7.3
	 * @return mixed
	 */
	public function getSearchUsingFulltext() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_search', 'fulltext_enabled' );
	}

	/**
	 * Get the user's search field choices.
	 *
	 * @deprecated since 0.7.3
	 * @return array
	 */
	public function getSearchFields() {
		global $connections;

		return $connections->settings->get( 'connections', 'connections_search', 'fields' );;
	}

	/**
	 * Callback for the seetings search fields.
	 * Saves the user's search field choices and sets up the FULLTEXT indexes.
	 *
	 * @TODO this will fail on tables that do not support FULLTEXT. Should somehow check before processing
	 * and set FULLTEXT support to FALSE
	 *
	 * @access private
	 * @since 0.7.3
	 * @param unknown $settings array
	 * @return array
	 */
	public function setSearchFields( $settings ) {
		global $wpdb;

		$fields = $settings['fields'];
		//var_dump($fields);

		//$wpdb->show_errors();

		/*
		 * The permitted fields that are supported for FULLTEXT searching.
		 */
		/*$permittedFields['entry'] = array( 'family_name' ,
										'first_name' ,
										'middle_name' ,
										'last_name' ,
										'title' ,
										'organization' ,
										'department' ,
										'contact_first_name' ,
										'contact_last_name' ,
										'bio' ,
										'notes' );
		$permittedFields['address'] = array( 'line_1' ,
										'line_2' ,
										'line_3' ,
										'city' ,
										'state' ,
										'zipcode' ,
										'country' );
		$permittedFields['phone'] = array( 'number' );*/


		/*
		 * Build the array to store the user preferences.
		 */
		$search['family_name'] = in_array( 'family_name' , $fields ) ? TRUE : FALSE;
		$search['first_name'] = in_array( 'first_name' , $fields ) ? TRUE : FALSE;
		$search['middle_name'] = in_array( 'middle_name' , $fields ) ? TRUE : FALSE;
		$search['last_name'] = in_array( 'last_name' , $fields ) ? TRUE : FALSE;
		$search['title'] = in_array( 'title' , $fields ) ? TRUE : FALSE;
		$search['organization'] = in_array( 'organization' , $fields ) ? TRUE : FALSE;
		$search['department'] = in_array( 'department' , $fields ) ? TRUE : FALSE;
		$search['contact_first_name'] = in_array( 'contact_first_name' , $fields ) ? TRUE : FALSE;
		$search['contact_last_name'] = in_array( 'contact_last_name' , $fields ) ? TRUE : FALSE;
		$search['bio'] = in_array( 'bio' , $fields ) ? TRUE : FALSE;
		$search['notes'] = in_array( 'notes' , $fields ) ? TRUE : FALSE;

		$search['address_line_1'] = in_array( 'address_line_1' , $fields ) ? TRUE : FALSE;
		$search['address_line_2'] = in_array( 'address_line_2' , $fields ) ? TRUE : FALSE;
		$search['address_line_3'] = in_array( 'address_line_3' , $fields ) ? TRUE : FALSE;
		$search['address_city'] = in_array( 'address_city' , $fields ) ? TRUE : FALSE;
		$search['address_state'] = in_array( 'address_state' , $fields ) ? TRUE : FALSE;
		$search['address_zipcode'] = in_array( 'address_zipcode' , $fields ) ? TRUE : FALSE;
		$search['address_country'] = in_array( 'address_country' , $fields ) ? TRUE : FALSE;

		$search['phone_number'] = in_array( 'phone_number' , $fields ) ? TRUE : FALSE;

		//var_dump($search);

		/*
		 * Drop the current FULLTEXT indexes.
		 */
		$indexExists = @$wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_TABLE . ' WHERE KEY_NAME = \'search\'' ); //var_dump($indexExists);
		if ( $indexExists > 0 ) @$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_TABLE . ' DROP INDEX search' );

		$indexExists = @$wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE KEY_NAME = \'search\'' ); //var_dump($indexExists);
		if ( $indexExists > 0 ) @$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_ADDRESS_TABLE . ' DROP INDEX search' );

		$indexExists = @$wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_PHONE_TABLE . ' WHERE KEY_NAME = \'search\'' ); //var_dump($indexExists);
		if ( $indexExists > 0 ) @$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_PHONE_TABLE . ' DROP INDEX search' );

		/*
		 * Recreate the FULLTEXT indexes based on the user choices
		 */

		// Build the arrays that will be imploded in the query statement.
		if ( $search['family_name'] ) $column['entry'][] = 'family_name';
		if ( $search['first_name'] ) $column['entry'][] = 'first_name';
		if ( $search['middle_name'] ) $column['entry'][] = 'middle_name';
		if ( $search['last_name'] ) $column['entry'][] = 'last_name';
		if ( $search['title'] ) $column['entry'][] = 'title';
		if ( $search['organization'] ) $column['entry'][] = 'organization';
		if ( $search['department'] ) $column['entry'][] = 'department';
		if ( $search['contact_first_name'] ) $column['entry'][] = 'contact_first_name';
		if ( $search['contact_last_name'] ) $column['entry'][] = 'contact_last_name';
		if ( $search['bio'] ) $column['entry'][] = 'bio';
		if ( $search['notes'] ) $column['entry'][] = 'notes';

		if ( $search['address_line_1'] ) $column['address'][] = 'line_1';
		if ( $search['address_line_2'] ) $column['address'][] = 'line_2';
		if ( $search['address_line_3'] ) $column['address'][] = 'line_3';
		if ( $search['address_city'] ) $column['address'][] = 'city';
		if ( $search['address_state'] ) $column['address'][] = 'state';
		if ( $search['address_zipcode'] ) $column['address'][] = 'zipcode';
		if ( $search['address_country'] ) $column['address'][] = 'country';

		if ( $search['phone_number'] ) $column['phone'][] = 'number';

		// Add the FULLTEXT indexes.
		if ( ! empty( $column['entry'] ) ) $wpdb->query( 'ALTER TABLE ' . CN_ENTRY_TABLE . ' ADD FULLTEXT search (' . implode( ',', $column['entry'] ) . ')' );
		if ( ! empty( $column['address'] ) ) $wpdb->query( 'ALTER TABLE ' . CN_ENTRY_ADDRESS_TABLE . ' ADD FULLTEXT search (' . implode( ',', $column['address'] ) . ')' );
		if ( ! empty( $column['phone'] ) ) $wpdb->query( 'ALTER TABLE ' . CN_ENTRY_PHONE_TABLE . ' ADD FULLTEXT search (' . implode( ',', $column['phone'] ) . ')' );

		//$wpdb->hide_errors();

		//die;

		return $settings;
	}
}