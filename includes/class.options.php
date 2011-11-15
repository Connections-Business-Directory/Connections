<?php

/**
 * Get and Set the plugin options
 */
class cnOptions
{
	/**
	 * Array of options returned from WP get_option method
	 * @var array
	 */
	private $options;
	
	/**
	 * String: plugin version
	 * @var string
	 */
	private $version;
	
	/**
	 * String: plugin db version
	 * @var string
	 */
	private $dbVersion;
	
	private $defaultCapabilities = array(
								'connections_view_dashboard' => 'View Dashboard',
								'connections_manage' => 'View List (Manage)',
								'connections_add_entry' => 'Add Entry',
								'connections_add_entry_moderated' => 'Add Entry Moderated',
								'connections_edit_entry' => 'Edit Entry',
								'connections_edit_entry_moderated' => 'Edit Entry Moderated',
								'connections_delete_entry' => 'Delete Entry',
								'connections_view_public' => 'View Public Entries',
								'connections_view_private' => 'View Private Entries',
								'connections_view_unlisted' => 'View Unlisted Entries',
								'connections_edit_categories' => 'Edit Categories',
								'connections_change_settings' => 'Change Settings',
								'connections_manage_template' => 'Manage Templates',
								'connections_change_roles' => 'Change Role Capabilities',
								'connections_view_help' => 'View Help'
							);
							
	private $defaultFamilyRelationValues = array(
											'' =>"Select Relation",
											'aunt' =>"Aunt",
											'brother' =>"Brother",
											'brotherinlaw' =>"Brother-in-law",
											'cousin' =>"Cousin",
											'daughter' =>"Daughter",
											'daughterinlaw' =>"Daughter-in-law",
											'father' =>"Father",
											'fatherinlaw' =>"Father-in-law",
											'granddaughter' =>"Grand Daughter",
											'grandfather' =>"Grand Father",
											'grandmother' =>"Grand Mother",
											'grandson' =>"Grand Son",
											'greatgrandmother' =>"Great Grand Mother",
											'greatgrandfather' =>"Great Grand Father",
											'husband' =>"Husband",
											'mother' =>"Mother",
											'motherinlaw' =>"Mother-in-law",
											'nephew' =>"Nephew",
											'niece' =>"Niece",
											'sister' =>"Sister",
											'sisterinlaw' =>"Sister-in-law",
											'son' =>"Son",
											'soninlaw' =>"Son-in-law",
											'stepbrother' =>"Step Brother",
											'stepdaughter' =>"Step Daughter",
											'stepfather' =>"Step Father",
											'stepmother' =>"Step Mother",
											'stepsister' =>"Step Sister",
											'stepson' =>"Step Son",
											'uncle' =>"Uncle",
											'wife' =>"Wife"
											);
	
	private $defaultAddressValues	=	array
											(
												'home'=>'Home',
												'work'=>'Work',
												'school'=>'School',
												'other'=>'Other'
											);
	
	private $defaultPhoneNumberValues	=	array
											(
												'homephone'=>'Home Phone',
												'homefax'=>'Home Fax',
												'cellphone'=>'Cell Phone',
												'workphone'=>'Work Phone',
												'workfax'=>'Work Fax'
											);
	
	private $defaultSocialMediaValues = array(
											 'delicious' => 'delicious',
											 'cdbaby' => 'CD Baby',
											 'facebook' => 'Facebook',
											 'flickr' => 'Flickr',
											 'itunes' => 'iTunes',
											 'linked-in' => 'Linked-in',
											 'mixcloud' => 'mixcloud',
											 'myspace' => 'MySpace',
											 'podcast' => 'Podcast',
											 'reverbnation' => 'ReverbNation',
											 'rss' => 'RSS',
											 'technorati' => 'Technorati',
											 'twitter' => 'Twitter',
											 'soundcloud' => 'SoundCloud',
											 'youtube' => 'YouTube'
											 );
	
	private $defaultIMValues  =   array
									(
										'aim'=>'AIM',
										'yahoo'=>'Yahoo IM',
										'jabber'=>'Jabber / Google Talk',
										'messenger'=>'Messenger',
										'skype' => 'Skype'
									);
	
	private $defaultEmailValues  =   array
									(
										'personal'=>'Personal Email',
										'work'=>'Work Email'
									);
	
	private $defaultLinkValues  =   array
									(
										'website'=>'Website',
										'blog' => 'Blog'
									);
									
	private $allowPublic;
	private $allowPublicOverride;
	
	private $allowPrivateOverride;
	
	private $imgThumbQuality;
	private $imgThumbX;
	private $imgThumbY;
	private $imgThumbCrop;
	private $imgThumbRatioCrop;
	private $imgThumbRatioFill;
	
	private $imgEntryQuality;
	private $imgEntryX;
	private $imgEntryY;
	private $imgEntryCrop;
	private $imgEntryRatioCrop;
	private $imgEntryRatioFill;
	
	private $imgProfileQuality;
	private $imgProfileX;
	private $imgProfileY;
	private $imgProfileCrop;
	private $imgProfileRatioCrop;
	private $imgProfileRatioFill;
	
	private $imgLogoQuality;
	private $imgLogoX;
	private $imgLogoY;
	private $imgLogoCrop;
	private $imgLogoRatioCrop;
	private $imgLogoRatioFill;
	
	private $defaultTemplatesSet;
	private $activeTemplates;
	
	private $debug;
	
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
	 * @param interger $userID
	 */
	public function __construct()
	{
		global $wpdb;
		
		$this->options = get_option('connections_options');
		$this->version = $this->options['version'];
		$this->dbVersion = $this->options['db_version'];
		
		$this->debug = $this->options['debug'];
		
		//$this->entryType = $this->options[$this->currentUserID]['filter']['entry_type'];
		//$this->visibilityType = $this->options[$this->currentUserID]['filter']['visibility_type'];
		
		$this->allowPublic = $this->options['settings']['allow_public'];
		$this->allowPublicOverride = $this->options['settings']['allow_public_override'];
		
		$this->allowPrivateOverride = $this->options['settings']['allow_private_override'];
		
		$this->imgThumbQuality = $this->options['settings']['image']['thumbnail']['quality'];
		$this->imgThumbX = $this->options['settings']['image']['thumbnail']['x'];
		$this->imgThumbY = $this->options['settings']['image']['thumbnail']['y'];
		$this->imgThumbCrop = $this->options['settings']['image']['thumbnail']['crop'];
		$this->imgThumbRatioCrop = $this->options['settings']['image']['thumbnail']['ratio_crop'];
		$this->imgThumbRatioFill = $this->options['settings']['image']['thumbnail']['ratio_fill'];
		
		$this->imgEntryQuality = $this->options['settings']['image']['entry']['quality'];
		$this->imgEntryX = $this->options['settings']['image']['entry']['x'];
		$this->imgEntryY = $this->options['settings']['image']['entry']['y'];
		$this->imgEntryCrop = $this->options['settings']['image']['entry']['crop'];
		$this->imgEntryRatioCrop = $this->options['settings']['image']['entry']['ratio_crop'];
		$this->imgEntryRatioFill = $this->options['settings']['image']['entry']['ratio_fill'];
		
		$this->imgProfileQuality = $this->options['settings']['image']['profile']['quality'];
		$this->imgProfileX = $this->options['settings']['image']['profile']['x'];
		$this->imgProfileY = $this->options['settings']['image']['profile']['y'];
		$this->imgProfileCrop = $this->options['settings']['image']['profile']['crop'];
		$this->imgProfileRatioCrop = $this->options['settings']['image']['profile']['ratio_crop'];
		$this->imgProfileRatioFill = $this->options['settings']['image']['profile']['ratio_fill'];
		
		$this->imgLogoQuality = $this->options['settings']['image']['logo']['quality'];
		$this->imgLogoX = $this->options['settings']['image']['logo']['x'];
		$this->imgLogoY = $this->options['settings']['image']['logo']['y'];
		$this->imgLogoCrop = $this->options['settings']['image']['logo']['crop'];
		$this->imgLogoRatioCrop = $this->options['settings']['image']['logo']['ratio_crop'];
		$this->imgLogoRatioFill = $this->options['settings']['image']['logo']['ratio_fill'];
		
		$this->defaultTemplatesSet = $this->options['settings']['template']['defaults_set'];
		$this->activeTemplates = (array) $this->options['settings']['template']['active'];
				
		$this->wpCurrentTime = current_time('timestamp');
		$this->currentTime = date('U');
		
		/*
		 * Because MySQL FROM_UNIXTIME returns timestamps adjusted to the local
		 * timezone it is handy to have the offset so it can be compensated for.
		 * One example is when using FROM_UNIXTIME, the timestamp returned will
		 * not be the actual stored timestamp, it will be the timestamp adjusted
		 * to the timezone set in MySQL.
		 */
		$mySQLTimeStamp = $wpdb->get_results('SELECT NOW() as timestamp');
		$this->sqlCurrentTime = strtotime( $mySQLTimeStamp[0]->timestamp );
		$this->sqlTimeOffset = time() - $this->sqlCurrentTime;
	}
	
	/**
	 * Saves the plug-in options to the database.
	 */
	public function saveOptions()
	{
		$this->options['version'] = $this->version;
		$this->options['db_version'] = $this->dbVersion;
		
		$this->options['debug'] = $this->debug;
		
		//$this->options[$this->currentUserID]['filter']['entry_type'] = $this->entryType;
		//$this->options[$this->currentUserID]['filter']['visibility_type'] = $this->visibilityType;
		
		$this->options['settings']['allow_public'] = $this->allowPublic;
		$this->options['settings']['allow_public_override'] = $this->allowPublicOverride;
		
		$this->options['settings']['allow_private_override'] = $this->allowPrivateOverride;
		
		$this->options['settings']['image']['thumbnail']['quality'] = $this->imgThumbQuality;
		$this->options['settings']['image']['thumbnail']['x'] = $this->imgThumbX;
		$this->options['settings']['image']['thumbnail']['y'] = $this->imgThumbY;
		$this->options['settings']['image']['thumbnail']['crop'] = $this->imgThumbCrop;
		$this->options['settings']['image']['thumbnail']['ratio_crop'] = $this->imgThumbRatioCrop;
		$this->options['settings']['image']['thumbnail']['ratio_fill'] = $this->imgThumbRatioFill;
		
		$this->options['settings']['image']['entry']['quality'] = $this->imgEntryQuality;
		$this->options['settings']['image']['entry']['x'] = $this->imgEntryX;
		$this->options['settings']['image']['entry']['y'] = $this->imgEntryY;
		$this->options['settings']['image']['entry']['crop'] = $this->imgEntryCrop;
		$this->options['settings']['image']['entry']['ratio_crop'] = $this->imgEntryRatioCrop;
		$this->options['settings']['image']['entry']['ratio_fill'] = $this->imgEntryRatioFill;
		
		$this->options['settings']['image']['profile']['quality'] = $this->imgProfileQuality;
		$this->options['settings']['image']['profile']['x'] = $this->imgProfileX;
		$this->options['settings']['image']['profile']['y'] = $this->imgProfileY;
		$this->options['settings']['image']['profile']['crop'] = $this->imgProfileCrop;
		$this->options['settings']['image']['profile']['ratio_crop'] = $this->imgProfileRatioCrop;
		$this->options['settings']['image']['profile']['ratio_fill'] = $this->imgProfileRatioFill;
		
		$this->options['settings']['image']['logo']['quality'] = $this->imgLogoQuality;
		$this->options['settings']['image']['logo']['x'] = $this->imgLogoX;
		$this->options['settings']['image']['logo']['y'] = $this->imgLogoY;
		$this->options['settings']['image']['logo']['crop'] = $this->imgLogoCrop;
		$this->options['settings']['image']['logo']['ratio_crop'] = $this->imgLogoRatioCrop;
		$this->options['settings']['image']['logo']['ratio_fill'] = $this->imgLogoRatioFill;
		
		$this->options['settings']['template']['defaults_set'] = $this->defaultTemplatesSet;
		$this->options['settings']['template']['active'] = $this->activeTemplates;
		
		update_option('connections_options', $this->options);
	}
	
	public function removeOptions()
	{
		delete_option('connections_options');
	}
        
    /**
     * Returns $allowPublic.
     * @see pluginOptions::$allowPublic
     */
    public function getAllowPublic()
    {
        return $this->allowPublic;
    }
    
    /**
     * Sets $allowPublic.
     * @param object $allowPublic
     * @see pluginOptions::$allowPublic
     */
    public function setAllowPublic($allowPublic)
    {
        global $wp_roles;
		
		if (!isset($wp_roles))
		{
			$wp_roles = new WP_Roles();
		}
		
		$currentRoles = $wp_roles->get_names();
		
		if($allowPublic)
		{
			$this->allowPublic = TRUE;
			
			foreach ($currentRoles as $role => $name)
			{
				$this->addCapability($role, 'connections_view_public');
			}
		}
		else
		{
			$this->allowPublic = FALSE;
			/*foreach ($currentRoles as $role => $name)
			{
				$this->removeCapability($role, 'connections_view_public');
			}*/
		}
		
    }

    /**
     * Returns $allowPublicOverride.
     * @see pluginOptions::$allowPublicOverride
     */
    public function getAllowPublicOverride()
    {
        return $this->allowPublicOverride;
    }
    
    /**
     * Sets $allowPublicOverride.
     * @param object $allowPublicOverride
     * @see pluginOptions::$allowPublicOverride
     */
    public function setAllowPublicOverride($value)
    {
        $this->allowPublicOverride = $value;
    }
	
	public function getAllowPrivateOverride()
    {
        return $this->allowPrivateOverride;
    }
    
     public function setAllowPrivateOverride($value)
    {
        $this->allowPrivateOverride = $value;
    }
	
	public function hasCapability($role, $cap)
	{
		global $wp_roles;
		
		/* 
		 * Check to make sure $wp_roles has been initialized and set.
		 * If it hasn't it is initialized. This was done because this method 
		 * can be called before the $wp_roles has been initialized.
		 */
		if (!isset($wp_roles))
		{
			$wp_roles = new WP_Roles();
		}
		
		$wpRoleDataArray = $wp_roles->roles;
		$wpRoleCaps = $wpRoleDataArray[$role]['capabilities'];
		$wpRole = new WP_Role($role, $wpRoleCaps);
		
		return $wpRole->has_cap($cap);
	}
	
	public function addCapability($role, $cap)
	{
		global $wp_roles;
		
		/* 
		 * Check to make sure $wp_roles has been initialized and set.
		 * If it hasn't it is initialized. This was done because this method 
		 * can be called before the $wp_roles has been initialized.
		 */
		if (!isset($wp_roles))
		{
			$wp_roles = new WP_Roles();
		}
		
		//$wpRole = get_role($role);
		if (!$this->hasCapability($role, $cap)) $wp_roles->add_cap($role, $cap);
	}
	
	public function removeCapability($role, $cap)
	{
		global $wp_roles;
		
		/* 
		 * Check to make sure $wp_roles has been initialized and set.
		 * If it hasn't it is initialized. This was done because this method 
		 * can be called before the $wp_roles has been initialized.
		 */
		if (!isset($wp_roles))
		{
			$wp_roles = new WP_Roles();
		}
		
		//$wpRole = get_role($role);
		if ($this->hasCapability($role, $cap)) $wp_roles->remove_cap($role, $cap);
	}
	
	public function getDefaultCapabilities()
	{
		return $this->defaultCapabilities;
	}
	
	public function setDefaultCapabilities($rolesToReset = null)
	{
		global $wp_roles;
		
		/**
		 * These are the roles that will default to having full access
		 * to all capabilites. This is to maintain plugin behavior that
		 * exisited prior to adding role/capability support.
		 */
		$defaultRoles = array('administrator', 'editor', 'author');
		
		/**
		 * If no roles are supplied to the method to reset; the method
		 * will reset the capabilies of all roles defined.
		 */
		if (!isset($rolesToReset)) $rolesToReset = $wp_roles->get_names();
		
		foreach ($rolesToReset as $role => $name)
		{
			$wpRole = get_role($role);
			
			if (in_array($role, $defaultRoles))
			{
				foreach ($this->defaultCapabilities as $cap => $name)
				{
					if (!$this->hasCapability($role, $cap)) $wpRole->add_cap($cap);
				}
			}
			else
			{
				foreach ($this->defaultCapabilities as $cap => $name)
				{
					if ($this->hasCapability($role, $cap)) $wpRole->remove_cap($cap);
				}
			}
		}
		
		// Make sure the capability to view public entries is set for the roles based on the previously saved preference.
		$this->setAllowPublic($this->allowPublic);
	}
	
	public function removeDefaultCapabilities()
	{
		global $wp_roles;
		
		$rolesToReset = $wp_roles->get_names();
		
		foreach ($rolesToReset as $role => $name)
		{
			$wpRole = get_role($role);
			
			foreach ($this->defaultCapabilities as $cap => $name)
			{
				if ($this->hasCapability($role, $cap)) $wpRole->remove_cap($cap);
			}
		}
	}
	
    /**
     * Returns $version.
     * @see options::$version
     */
    public function getVersion()
    {
        if ( empty($this->version) )
		{
			return NULL;
		}
		else
		{
			return $this->version;
		}
    }
    
    /**
     * Sets $version.
     * @param object $version
     * @see options::$version
     */
    public function setVersion($version)
    {
        $this->version = $version;
		$this->saveOptions();
    }
    
	/**
     * Returns $dbVersion.
     * @see options::$dbVersion
     */
    public function getDBVersion()
    {
        if ( empty($this->dbVersion) )
		{
			return NULL;
		}
		else
		{
			return $this->dbVersion;
		}
    }
    
    /**
     * Sets $dbVersion.
     * @param string $dbVersion
     * @see options::$dbVersion
     */
    public function setDBVersion($version)
    {
        $this->dbVersion = $version;
		$this->saveOptions();
    }
	
	/**
	 * Set the image default settings
	 */
	public function setDefaultImageSettings()
	{
		$this->imgThumbQuality = 80;
		$this->imgThumbX = 80;
		$this->imgThumbY = 54;
		$this->imgThumbCrop = 'crop';
		$this->imgThumbRatioCrop = true;
		$this->imgThumbRatioFill = false;
		
		$this->imgEntryQuality = 80;
		$this->imgEntryX = 225;
		$this->imgEntryY = 150;
		$this->imgEntryCrop = 'crop';
		$this->imgEntryRatioCrop = true;
		$this->imgEntryRatioFill = false;
		
		$this->imgProfileQuality = 80;
		$this->imgProfileX = 300;
		$this->imgProfileY = 225;
		$this->imgProfileCrop = 'crop';
		$this->imgProfileRatioCrop = true;
		$this->imgProfileRatioFill = false;
	}
	
    /**
     * Returns $imgEntryQuality.
     * @see pluginOptions::$imgEntryQuality
     */
    public function getImgEntryQuality()
    {
        return $this->imgEntryQuality;
    }
    
    /**
     * Sets $imgEntryQuality.
     * @param object $imgEntryQuality
     * @see pluginOptions::$imgEntryQuality
     */
    public function setImgEntryQuality($imgEntryQuality)
    {
        $this->imgEntryQuality = (integer) $imgEntryQuality;
    }
    
    /**
     * Returns $imgEntryX.
     * @see pluginOptions::$imgEntryX
     */
    public function getImgEntryX()
    {
        return $this->imgEntryX;
    }
    
    /**
     * Sets $imgEntryX.
     * @param object $imgEntryX
     * @see pluginOptions::$imgEntryX
     */
    public function setImgEntryX($imgEntryX)
    {
        $this->imgEntryX = (integer) $imgEntryX;
    }
    
    /**
     * Returns $imgEntryY.
     * @see pluginOptions::$imgEntryY
     */
    public function getImgEntryY()
    {
        return $this->imgEntryY;
    }
    
    /**
     * Sets $imgEntryY.
     * @param object $imgEntryY
     * @see pluginOptions::$imgEntryY
     */
    public function setImgEntryY($imgEntryY)
    {
        $this->imgEntryY = (integer) $imgEntryY;
    }

    /**
     * Returns $imgEntryCrop.
     * @see pluginOptions::$imgEntryCrop
     */
    public function getImgEntryCrop()
    {
        return $this->imgEntryCrop;
    }
    
    /**
     * Sets $imgEntryCrop.
     * @param object $imgEntryCrop
     * @see pluginOptions::$imgEntryCrop
     */
    public function setImgEntryCrop($imgEntryCrop)
    {
        switch ($imgEntryCrop)
		{
        	case 'none':
        		$this->imgEntryRatioCrop = false;
				$this->imgEntryRatioFill = false;
				$this->imgEntryCrop = 'none';
        		break;
			
			case 'crop':
        		$this->imgEntryRatioCrop = true;
				$this->imgEntryRatioFill = false;
				$this->imgEntryCrop = 'crop';
        		break;
				
			case 'fill':
        		$this->imgEntryRatioCrop = false;
				$this->imgEntryRatioFill = true;
				$this->imgEntryCrop = 'fill';
        		break;
        	
        	default:
        		$this->imgEntryRatioCrop = true;
				$this->imgEntryRatioFill = false;
				$this->imgEntryCrop = 'crop';
        		break;
        }
		
    }

    /**
     * Returns $imgEntryRatioCrop.
     * @see pluginOptions::$imgEntryRatioCrop
     */
    public function getImgEntryRatioCrop()
    {
        return $this->imgEntryRatioCrop;
    }
    
    /**
     * Returns $imgEntryRatioFill.
     * @see pluginOptions::$imgEntryRatioFill
     */
    public function getImgEntryRatioFill()
    {
        return $this->imgEntryRatioFill;
    }
    
    /**
     * Returns $imgProfileQuality.
     * @see pluginOptions::$imgProfileQuality
     */
    public function getImgProfileQuality()
    {
        return $this->imgProfileQuality;
    }
    
    /**
     * Sets $imgProfileQuality.
     * @param object $imgProfileQuality
     * @see pluginOptions::$imgProfileQuality
     */
    public function setImgProfileQuality($imgProfileQuality)
    {
        $this->imgProfileQuality = (integer) $imgProfileQuality;
    }
    
    /**
     * Returns $imgProfileX.
     * @see pluginOptions::$imgProfileX
     */
    public function getImgProfileX()
    {
        return $this->imgProfileX;
    }
    
    /**
     * Sets $imgProfileX.
     * @param object $imgProfileX
     * @see pluginOptions::$imgProfileX
     */
    public function setImgProfileX($imgProfileX)
    {
        $this->imgProfileX = (integer) $imgProfileX;
    }
    
    /**
     * Returns $imgProfileY.
     * @see pluginOptions::$imgProfileY
     */
    public function getImgProfileY()
    {
        return $this->imgProfileY;
    }
    
    /**
     * Sets $imgProfileY.
     * @param object $imgProfileY
     * @see pluginOptions::$imgProfileY
     */
    public function setImgProfileY($imgProfileY)
    {
        $this->imgProfileY = (integer) $imgProfileY;
    }

    /**
     * Returns $imgProfileCrop.
     * @see pluginOptions::$imgProfileCrop
     */
    public function getImgProfileCrop()
    {
        return $this->imgProfileCrop;
    }
    
    /**
     * Sets $imgProfileCrop.
     * @param object $imgProfileCrop
     * @see pluginOptions::$imgProfileCrop
     */
    public function setImgProfileCrop($imgProfileCrop)
    {
        switch ($imgProfileCrop)
		{
        	case 'none':
        		$this->imgProfileRatioCrop = false;
				$this->imgProfileRatioFill = false;
				$this->imgProfileCrop = 'none';
        		break;
			
			case 'crop':
        		$this->imgProfileRatioCrop = true;
				$this->imgProfileRatioFill = false;
				$this->imgProfileCrop = 'crop';
        		break;
				
			case 'fill':
        		$this->imgProfileRatioCrop = false;
				$this->imgProfileRatioFill = true;
				$this->imgProfileCrop = 'fill';
        		break;
        	
        	default:
        		$this->imgProfileRatioCrop = true;
				$this->imgProfileRatioFill = false;
				$this->imgProfileCrop = 'crop';
        		break;
        }
    }
	
    /**
     * Returns $imgProfileRatioCrop.
     * @see pluginOptions::$imgProfileRatioCrop
     */
    public function getImgProfileRatioCrop()
    {
        return $this->imgProfileRatioCrop;
    }
    
    /**
     * Returns $imgProfileRatioFill.
     * @see pluginOptions::$imgProfileRatioFill
     */
    public function getImgProfileRatioFill()
    {
        return $this->imgProfileRatioFill;
    }
    
    /**
     * Returns $imgThumbQuality.
     * @see pluginOptions::$imgThumbQuality
     */
    public function getImgThumbQuality()
    {
        return $this->imgThumbQuality;
    }
    
    /**
     * Sets $imgThumbQuality.
     * @param object $imgThumbQuality
     * @see pluginOptions::$imgThumbQuality
     */
    public function setImgThumbQuality($imgThumbQuality)
    {
        $this->imgThumbQuality = (integer) $imgThumbQuality;
    }
    
    /**
     * Returns $imgThumbX.
     * @see pluginOptions::$imgThumbX
     */
    public function getImgThumbX()
    {
        return $this->imgThumbX;
    }
    
    /**
     * Sets $imgThumbX.
     * @param object $imgThumbX
     * @see pluginOptions::$imgThumbX
     */
    public function setImgThumbX($imgThumbX)
    {
        $this->imgThumbX = (integer) $imgThumbX;
    }
    
    /**
     * Returns $imgThumbY.
     * @see pluginOptions::$imgThumbY
     */
    public function getImgThumbY()
    {
        return $this->imgThumbY;
    }
    
    /**
     * Sets $imgThumbY.
     * @param object $imgThumbY
     * @see pluginOptions::$imgThumbY
     */
    public function setImgThumbY($imgThumbY)
    {
        $this->imgThumbY = (integer) $imgThumbY;
    }
	
	/**
     * Returns $imgThumbCrop.
     * @see pluginOptions::$imgThumbCrop
     */
    public function getImgThumbCrop()
    {
        return $this->imgThumbCrop;
    }
    
    /**
     * Sets $imgThumbCrop.
     * @param object $imgThumbCrop
     * @see pluginOptions::$imgThumbCrop
     */
    public function setImgThumbCrop($imgThumbCrop)
    {
        switch ($imgThumbCrop)
		{
        	case 'none':
        		$this->imgThumbRatioCrop = false;
				$this->imgThumbRatioFill = false;
				$this->imgThumbCrop = 'none';
        		break;
			
			case 'crop':
        		$this->imgThumbRatioCrop = true;
				$this->imgThumbRatioFill = false;
				$this->imgThumbCrop = 'crop';
        		break;
				
			case 'fill':
        		$this->imgThumbRatioCrop = false;
				$this->imgThumbRatioFill = true;
				$this->imgThumbCrop = 'fill';
        		break;
        	
        	default:
        		$this->imgThumbRatioCrop = true;
				$this->imgThumbRatioFill = false;
				$this->imgThumbCrop = 'crop';
        		break;
        }
		
    }

    /**
     * Returns $imgThumbRatioCrop.
     * @see pluginOptions::$imgThumbRatioCrop
     */
    public function getImgThumbRatioCrop()
    {
        return $this->imgThumbRatioCrop;
    }
    
    /**
     * Returns $imgThumbRatioFill.
     * @see pluginOptions::$imgThumbRatioFill
     */
    public function getImgThumbRatioFill()
    {
        return $this->imgThumbRatioFill;
    }
    
	
	
	public function getImgLogoQuality()
    {
        return $this->imgLogoQuality;
    }
    
    public function setImgLogoQuality($imgLogoQuality)
    {
        $this->imgLogoQuality = (integer) $imgLogoQuality;
    }
    
    public function getImgLogoX()
    {
        return $this->imgLogoX;
    }
    
    public function setImgLogoX($imgLogoX)
    {
        $this->imgLogoX = (integer) $imgLogoX;
    }
    
    public function getImgLogoY()
    {
        return $this->imgLogoY;
    }
    
    public function setImgLogoY($imgLogoY)
    {
        $this->imgLogoY = (integer) $imgLogoY;
    }
	
	public function getImgLogoCrop()
    {
        return $this->imgLogoCrop;
    }
    
    public function setImgLogoCrop($imgLogoCrop)
    {
        switch ($imgLogoCrop)
		{
        	case 'none':
        		$this->imgLogoRatioCrop = false;
				$this->imgLogoRatioFill = false;
				$this->imgLogoCrop = 'none';
        		break;
			
			case 'crop':
        		$this->imgLogoRatioCrop = true;
				$this->imgLogoRatioFill = false;
				$this->imgLogoCrop = 'crop';
        		break;
				
			case 'fill':
        		$this->imgLogoRatioCrop = false;
				$this->imgLogoRatioFill = true;
				$this->imgLogoCrop = 'fill';
        		break;
        	
        	default:
        		$this->imgLogoRatioCrop = true;
				$this->imgLogoRatioFill = false;
				$this->imgLogoCrop = 'crop';
        		break;
        }
		
    }

    public function getImgLogoRatioCrop()
    {
        return $this->imgLogoRatioCrop;
    }
    
    public function getImgLogoRatioFill()
    {
        return $this->imgLogoRatioFill;
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
     * @param object $defaultTemplatesSet
     * @see cnOptions::$defaultTemplatesSet
     */
    public function setDefaultTemplatesSet($defaultTemplatesSet) {
        $this->defaultTemplatesSet = $defaultTemplatesSet;
    }
    
    
    /**
     * Returns the active template by type.
     * 
     * @param string $type
     * @return object || NULL
     */
	public function getActiveTemplate($type)
	{
        ( !empty($this->activeTemplates[$type]) ) ? $result = (object) $this->activeTemplates[$type] : $result = NULL;
		return $result;
    }
    
    /**
     * Sets $activeTemplate by type.
     *
     * @param string $type
     * @param object $activeTemplate
     * @see cnOptions::$activeTemplate
     */
    public function setActiveTemplate($type, $activeTemplate)
	{
       $this->activeTemplates[$type] = (array) $activeTemplate;
    }
    
	public function setDefaultTemplates()
	{
		$templates = new cnTemplate();
		$templates->buildCatalog();
		
		$all = $templates->getCatalog('all');
		$anniversary = $templates->getCatalog('anniversary');
		$birthday = $templates->getCatalog('birthday');
		
		$this->setActiveTemplate('all', $all->card);
		$this->setActiveTemplate('individual', $all->card);
		$this->setActiveTemplate('organization', $all->card);
		$this->setActiveTemplate('family', $all->card);
		$this->setActiveTemplate('anniversary', $anniversary->{'anniversary-light'});
		$this->setActiveTemplate('birthday', $birthday->{'birthday-light'});
		
		$this->defaultTemplatesSet = TRUE;
	}

    public function getDefaultFamilyRelationValues()
    {
        return $this->defaultFamilyRelationValues;
    }
    
	public function getFamilyRelation($value)
    {
        return $this->defaultFamilyRelationValues[$value];
    }
	
    /**
     * Returns $defaultAddressValues.
     *
     * @see cnOptions::$defaultAddressValues
     */
    public function getDefaultAddressValues() {
        return $this->defaultAddressValues;
    }
    
    
    /**
     * Returns $defaultPhoneNumberValues.
     *
     * @see cnOptions::$defaultPhoneNumberValues
     */
    public function getDefaultPhoneNumberValues() {
        return $this->defaultPhoneNumberValues;
    }
    
	
	public function getDefaultSocialMediaValues()
    {
        return $this->defaultSocialMediaValues;
    }
    
    /**
     * Returns $defaultIMValues.
     *
     * @see cnOptions::$defaultIMValues
     */
    public function getDefaultIMValues() {
        return $this->defaultIMValues;
    }
    
    /**
     * Returns $defaultEmailValues.
     *
     * @see cnOptions::$defaultEmailValues
     */
    public function getDefaultEmailValues() {
        return $this->defaultEmailValues;
    }
    
    /**
     * Returns $defaultLinkValues.
     *
     * @see cnOptions::$defaultLinkValues
     */
    public function getDefaultLinkValues() {
        return $this->defaultLinkValues;
    }
    
    /**
     * Sets $defaultLinkValues.
     *
     * @param object $defaultLinkValues
     * @see cnOptions::$defaultLinkValues
     */
    public function setDefaultLinkValues($defaultLinkValues) {
        $this->defaultLinkValues = $defaultLinkValues;
    }
    
	public function setDebug( $bool )
	{
		$this->debug = $bool;
	}
	
	public function getDebug()
	{
		return $this->debug;
	}
    
    /**
     * Returns $options.
     * @see pluginOptions::$options
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * Sets $options.
     * @param object $options
     * @see pluginOptions::$options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

}

?>