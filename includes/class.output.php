<?php

class cnOutput extends cnEntry
{
	/**
	 * Outputs the 'Entry Sized' image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getCardImage()
	{
		$this->getImage();
	}
	
	/**
	 * Outputs the 'Profile Sized' image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getProfileImage()
	{
		$this->getImage( array( 'image' => 'photo' , 'preset' => 'profile' ) );
	}
	
	/**
	 * Outputs the 'Thumbnail Sized' image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getThumbnailImage()
	{
		$this->getImage( array( 'image' => 'photo' , 'preset' => 'thumbnail' ) );
	}
	
	/**
	 * Outputs the logo image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getLogoImage( $atts = array() )
	{
		global $connections;
		
		/*
		 * Set some defaults so the result resembles how the previous rendered.
		 */
		$atts['image'] = 'logo';
		$atts['height'] = $connections->options->getImgLogoY();
		$atts['width'] = $connections->options->getImgLogoX();
		$atts['zc'] = 3;
		$this->getImage( $atts );
	}
	
	/**
	 * Echo the logo if associated in a HTML hCard compliant string.
	 * 
	 * Accepted option for the $atts property are:
	 * 	image (string) Select the image to display. Valid values are photo || logo
	 * 	preset (string) Select one of the predefined image sizes Must be used in conjunction with the 'image' option. Valid values are thumbnail || entry || profile
	 * 	fallback (array) Object to be shown when there is no image or logo. 
	 * 		type (string) Fallback type. Valid values are; none || default || block
	 * 		string (string) The string used with the block fallback
	 * 		height (int) Block height. [Required if a image custom size was set.]
	 * 		width (int) Block width.
	 * 	height (int) Override the values saved in the settings. [Required if providing custom size.]
	 * 	width (int) Override the values saved in the settings.
	 * 	zc (int) Crop format
	 * 		0 Resize to Fit specified dimensions (no cropping)
	 * 		1 Crop and resize to best fit the dimensions (default behaviour)
	 * 		2 Resize proportionally to fit entire image into specified dimensions, and add borders if required
	 * 		3 Resize proportionally adjusting size of scaled image so there are no borders gaps
	 * 	before (string) HTML to output before the image
	 * 	after (string) HTML to after before the image
	 * 	style (array) Customize an inline stlye tag for the image or the placeholder block. Array format key == attribute; value == value.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * NOTE: If only the height or width was set for a custom image size, the opposite image dimension must be set for
	 * the fallback block. This does not apply if the fallback is the default image.
	 * 
	 * @todo Enable support for a default image to be set.
	 * 
	 * @param array $atts [optional]
	 * @return string
	 */
	public function getImage( $suppliedAtts = array() )
	{
		global $connections;
		$displayImage = FALSE;
		$style = array();
		$tag = array();
		$out = '';
		
		$defaultAtts = array( 'image' => 'photo',
							  'preset' => 'entry',
							  'fallback' => array( 'type' => 'none',
							  					   'string' => '',
												   'height' => 0,
												   'width' => 0
												 ),
							  'height' => 0,
							  'width' => 0,
							  'zc' => 2,
							  'before' => '',
							  'after' => '',
							  'style' => array(),
							  'return' => FALSE
							);
		
		$atts = $this->validate->attributesArray( $defaultAtts , $suppliedAtts );
		$atts['fallback'] = $this->validate->attributesArray( $defaultAtts['fallback'] , $suppliedAtts['fallback'] );
		
		/*
		 * The $atts key that are not image tag attributes.
		 */
		$nonAtts = array( 'image' , 'preset' , 'fallback' , 'image_size' , 'zc' , 'before' , 'after' , 'return' );
		
		( ! empty($atts['height']) || ! empty($atts['width']) ) ? $customSize = TRUE : $customSize = FALSE;
		
		switch ( $atts['image'] )
		{
			case 'photo':
				if ( $this->getImageLinked() && $this->getImageDisplay() )
				{
					$displayImage = TRUE;
					$atts['class'] = 'photo';
					$atts['alt'] = 'Photo of ' . $this->getName();
					$atts['title'] = 'Photo of ' . $this->getName();
										
					if ( $customSize )
					{
						$atts['src'] = WP_CONTENT_URL . '/plugins/connections/includes/timthumb/timthumb.php?src=' .
									   CN_IMAGE_BASE_URL . $this->getImageNameOriginal() . 
									   ( ( empty($atts['height'] ) ) ? '' : '&amp;h=' . $atts['height'] ) . 
									   ( ( empty($atts['width'] ) ) ? '' : '&amp;w=' . $atts['width'] ) . 
									   ( ( empty($atts['zc'] ) ) ? '' : '&amp;zc=' . $atts['zc'] );
					}
					else
					{
						switch ( $atts['preset'])
						{
							case 'entry':
								$atts['image_size'] = getimagesize( CN_IMAGE_PATH . $this->getImageNameCard() );
								$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameCard();
								break;
							case 'profile':
								$atts['image_size'] = getimagesize( CN_IMAGE_PATH . $this->getImageNameProfile() );
								$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameProfile();
								break;
							case 'thumbnail':
								$atts['image_size'] = getimagesize( CN_IMAGE_PATH . $this->getImageNameThumbnail() );
								$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameThumbnail();
								break;
							default:
								$atts['image_size'] = getimagesize( CN_IMAGE_PATH . $this->getImageNameCard() );
								$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameCard();
								break;
						}
						
						if ( $atts['image_size'] !== FALSE )
						{
							$atts['width'] = $atts['image_size'][0];
							$atts['height'] = $atts['image_size'][1];
						}
					}
				}
			break;
			
			case 'logo':
				if ( $this->getLogoLinked() && $this->getLogoDisplay() )
				{
					$displayImage = TRUE;
					$atts['class'] = 'logo';
					$atts['alt'] = 'Logo for ' . $this->getName();
					$atts['title'] = 'Logo for ' . $this->getName();
					
					if ( $customSize )
					{
						$atts['src'] = WP_CONTENT_URL . '/plugins/connections/includes/timthumb/timthumb.php?src=' .
									   CN_IMAGE_BASE_URL . $this->getLogoName() . 
									   ( (empty($atts['height']) ) ? '' : '&amp;h=' . $atts['height'] ) . 
									   ( (empty($atts['width']) ) ? '' : '&amp;w=' . $atts['width'] ) . 
									   ( (empty($atts['zc']) ) ? '' : '&amp;zc=' . $atts['zc'] );
					}
					else
					{
						$atts['src'] = CN_IMAGE_BASE_URL . $this->getLogoName();
						$atts['image_size'] = getimagesize( CN_IMAGE_PATH . $this->getLogoName() );
						
						if ( $atts['image_size'] !== FALSE )
						{
							$atts['width'] = $atts['image_size'][0];
							$atts['height'] = $atts['image_size'][1];
						}
					}
				}
			break;
		}
		
		/*
		 * Add to the inline style the user supplied styles.
		 */
		foreach ( (array) $atts['style'] as $attr => $value )
		{
			if ( ! empty($value) ) $style[] = "$attr: $value";
		}
		
		if ( $displayImage )
		{
			foreach ( $atts as $attr => $value)
			{
				if ( ! empty($value) && ! in_array( $attr , $nonAtts ) ) $tag[] = "$attr=\"$value\"";
			}
			
			if ( ! empty($atts['height']) ) $style[] = 'height: ' . $atts['height'] . 'px';
			if ( ! empty($atts['width']) ) $style[] = 'width: ' . $atts['width'] . 'px';
			
			$out = '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image"' . ( ( empty($style) ) ? '' : ' style="' . implode('; ', $style) . ';"') . '><img ' . implode(' ', $tag) . ' /></span></span>';
		}
		else
		{
			if ( $customSize )
			{
				/*
				 * Set the size to the supplied custom. The fallback custom size would take priority if it has been supplied.
				 */
				( empty( $atts['fallback']['height'] ) ) ? $style[] = 'height: ' . $atts['height'] . 'px' : $style[] = 'height: ' . $atts['fallback']['height'] . 'px';
				( empty( $atts['fallback']['width'] ) ) ? $style[] = 'width: ' . $atts['width'] . 'px' : $style[] = 'width: ' . $atts['fallback']['width'] . 'px';
			}
			else
			{
				/*
				 * If a custom size was not set, use the dimensions saved in the settings.
				 */
				switch ( $atts['image'] )
				{
					case 'photo':
						
						switch ( $atts['preset'])
						{
							case 'entry':
								$style[] = 'height: ' . $connections->options->getImgEntryY() . 'px';
								$style[] = 'width: ' . $connections->options->getImgEntryX() . 'px';
								break;
							case 'profile':
								$style[] = 'height: ' . $connections->options->getImgProfileY() . 'px';
								$style[] = 'width: ' . $connections->options->getImgProfileX() . 'px';
								break;
							case 'thumbnail':
								$style[] = 'height: ' . $connections->options->getImgThumbY() . 'px';
								$style[] = 'width: ' . $connections->options->getImgThumbX() . 'px';
								break;
							default:
								$style[] = 'height: ' . $connections->options->getImgEntryY() . 'px';
								$style[] = 'width: ' . $connections->options->getImgEntryX() . 'px';
								break;
						}
						
						break;
					
					case 'logo':
						$style[] = 'height: ' . $connections->options->getImgLogoY() . 'px';
						$style[] = 'width: ' . $connections->options->getImgLogoX() . 'px';
						break;
				}
			}
			
			switch ( $atts['fallback']['type'] )
			{
				case 'block':
					$style[] = 'display: inline-block';
					
					( empty( $atts['fallback']['string'] ) ) ? $string = '' : $string = '<p>' . $atts['fallback']['string'] . '</p>';
					
					$out = '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image-none"' . ( ( empty($style) ) ? '' : ' style="' . implode('; ', $style) . ';"') . '>' . $string . '</span></span>';
							
					break;
				
				case 'default':
					/*
					 * @todo Enable support for a default image to be set.
					 * NOTE: Use switch for image type to allow a default image for both the image and logo.
					 */
					break;
			}
		}
		
		/*
		 * Either return or echo the string.
		 */
		if ( $atts['return'] ) return ( ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) );
		echo ( ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) );
	}
	
	/**
	 * Returns the name of the entry based on its type wrapped in spans that are classed to conform to the hCard spec.
	 * 
	 * Accepted options for the $atts property are:
	 * 	format (string) Tokens for the parts of the name.
	 * 	return (bool) Return or echo the string. Default is to echo.
	 * 
	 * Example:
	 * 	If an entry is an individual this would return their name as Last Name, First Name
	 * 	
	 * 	$this->getName( array( 'format' => '%last%, %first% %middle%' ) );
	 * 
	 * @param array $atts [optional]
	 * @return string
	 */
	public function getNameBlock($atts = NULL)
	{
		$defaultAtts = array( 'format' => '%prefix% %first% %middle% %last% %suffix%',
							  'return' => FALSE
							);
		
		$atts = $this->validate->attributesArray($defaultAtts, (array) $atts);
		
		$search = array('%prefix%', '%first%', '%middle%', '%last%', '%suffix%');
		$replace = array();
		
		switch ( $this->getEntryType() )
		{
			case 'individual':
				
				( $this->getHonorificPrefix() ) ? $replace[] = '<span class="honorific-prefix">' . $this->getHonorificPrefix() . '</span>' : $replace[] = '';;
				
				( $this->getFirstName() ) ? $replace[] = '<span class="given-name">' . $this->getFirstName() . '</span>' : $replace[] = '';
				
				( $this->getMiddleName() ) ? $replace[] = '<span class="additional-name">' . $this->getMiddleName() . '</span>' : $replace[] = '';
				
				( $this->getLastName() ) ? $replace[] = '<span class="family-name">' . $this->getLastName() . '</span>' : $replace[] = '';
				
				( $this->getHonorificSuffix() ) ? $replace[] = '<span class="honorific-suffix">' . $this->getHonorificSuffix() . '</span>' : $replace[] = '';
				
				$out = '<span class="fn n">' . str_ireplace( $search, $replace, $atts['format'] ) . '</span>';
			break;
			
			case 'organization':
				$out = '<span class="fn org">' . $this->getOrganization() . '</span>';
			break;
			
			case 'family':
				$out = '<span class="fn n"><span class="family-name">' . $this->getFamilyName() . '</span></span>';
			break;
			
			default:
				
				( $this->getHonorificPrefix() ) ? $replace[] = '<span class="honorific-prefix">' . $this->getHonorificPrefix() . '</span>' : $replace[] = '';;
				
				( $this->getFirstName() ) ? $replace[] = '<span class="given-name">' . $this->getFirstName() . '</span>' : $replace[] = '';
				
				( $this->getMiddleName() ) ? $replace[] = '<span class="additional-name">' . $this->getMiddleName() . '</span>' : $replace[] = '';
				
				( $this->getLastName() ) ? $replace[] = '<span class="family-name">' . $this->getLastName() . '</span>' : $replace[] = '';
				
				( $this->getHonorificSuffix() ) ? $replace[] = '<span class="honorific-suffix">' . $this->getHonorificSuffix() . '</span>' : $replace[] = '';
				
				$out = '<span class="fn n">' . str_ireplace( $search, $replace, $atts['format'] ) . '</span>';
			break;
		}
		
		
		if ( $atts['return'] ) return $out;
		echo $out;
	}
	
    public function getFullFirstLastNameBlock()
    {
        return $this->getNameBlock( array('format' => '%prefix% %first% %middle% %last% %suffix%', 'return' => TRUE) );		
    }
        
    public function getFullLastFirstNameBlock()
    {
    	return $this->getNameBlock( array('format' => '%last%, %first% %middle%', 'return' => TRUE) );	
    }
	
	/**
	 * Echos the family members of the family entry type.
	 * 
	 * @deprecated since 0.7.1.0
	 */
	public function getConnectionGroupBlock()
	{
		$this->getFamilyMemberBlock();
	}
	
	/**
	 * Echos the family members of the family entry type.
	 */
	public function getFamilyMemberBlock()
	{
		if ( $this->getFamilyMembers() )
		{
			global $connections;
			
			foreach ($this->getFamilyMembers() as $key => $value)
			{
				$relation = new cnEntry();
				$relation->set($key);
				echo '<span><strong>' . $connections->options->getFamilyRelation($value) . ':</strong> ' . $relation->getFullFirstLastName() . '</span><br />' . "\n";
				unset($relation);
			}
		}
	}
	
	public function getTitleBlock()
	{
		if ($this->getTitle()) return '<span class="title">' . $this->getTitle() . '</span>' . "\n";
	}
	
	public function getOrgUnitBlock()
	{
		if ($this->getOrganization() || $this->getDepartment()) $out = '<div class="org">' . "\n";
			if ($this->getOrganization() && $this->getEntryType() != 'organization') $out .= '<span class="organization-name">' . $this->getOrganization() . '</span><br />' . "\n";
			if ($this->getDepartment()) $out .= '<span class="organization-unit">' . $this->getDepartment() . '</span><br />' . "\n";
		if ($this->getOrganization() || $this->getDepartment()) $out .= '</div>' . "\n";
		
		return $out;
	}
	
	public function getOrganizationBlock()
	{
		if ($this->getOrganization() && $this->getEntryType() != 'organization') return '<span class="org">' . $this->getOrganization() . '</span>' . "\n";
	}
	
	public function getDepartmentBlock()
	{
		if ($this->getDepartment()) return '<span class="org"><span class="organization-unit">' . $this->getDepartment() . '</span></span>' . "\n";
	}
	
	/**
	 * Returns or echos out the addresses per the defined options for the current entry.
	 * 
	 * Accepted option for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry address.
	 * 	type (array) || (string) Retrieve specific address types.
	 * 	city (array) || (string) Retrieve addresses in a specific city.
	 * 	state (array) || (string) Retrieve addresses in a specific state..
	 * 	zipcode (array) || (string) Retrieve addresses in a specific zipcode.
	 * 	country (array) || (string) Retrieve addresses in a specific country.
	 * 	coordinates (array) Retrieve addresses in with specific coordinates. Both latitude and longitude must be supplied.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * @param array $suppliedAttr Accepted values as noted above.
	 * @param bool $cached Returns the cached address data rather than querying the db.
	 * @return string
	 */
	public function getAddressBlock( $suppliedAttr = array(), $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
			$defaultAttr['preferred'] = NULL;
			$defaultAttr['type'] = NULL;
			$defaultAttr['city'] = NULL;
			$defaultAttr['state'] = NULL;
			$defaultAttr['zipcode'] = NULL;
			$defaultAttr['country'] = NULL;
			$defaultAttr['coordinates'] = array();
			$defaultAttr['return'] = FALSE;
			
			$atts = $this->validate->attributesArray($defaultAttr, $suppliedAttr);
			$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		extract( $atts );
		
		$addresses = array();
		
		$addresses = $this->getAddresses( $atts, $cached );
		
		if ( empty($addresses) ) return '';
		
		$out = '';
		
		foreach ($addresses as $address)
		{
			$outCache = array();
			
			$out .= '<div class="adr" style="margin-bottom: 10px;">' . "\n";
			
				if ($address->name != NULL) $out .= '<span class="address_name" style="display: block"><strong>' . $address->name . '</strong></span>' . "\n";
				if ($address->line_1 != NULL) $out .= '<span class="street-address" style="display: block">' . $address->line_1 . '</span>' . "\n";
				if ($address->line_2 != NULL) $out .= '<span class="extended-address" style="display: block">' . $address->line_2 . '</span>' . "\n";
				if ($address->line_3 != NULL) $out .= '<span style="display: block">' . $address->line_3 . '</span>' . "\n";
				
				if ($address->city != NULL) $outCache[] = '<span class="locality">' . $address->city . ',</span>';
				if ($address->state != NULL) $outCache[] = '<span class="region">' . $address->state . '</span>';
				if ($address->zipcode != NULL) $outCache[] = '<span class="postal-code">' . $address->zipcode . '</span>';
				
				if ( ! empty($outCache) ) $out .= '<span style="display: block">' . implode('&nbsp;', $outCache) . '</span>';
				
				if ($address->country != NULL) $out .= '<span class="country-name" style="display: block">' . $address->country . '</span>' . "\n";
			
			$out .= '</div>' . "\n\n";
		}
		
		unset($outCache);
		
		if ( $return ) return $out;
		echo $out;
	}
	
	public function getPhoneNumberBlock()
	{
		if ($this->getPhoneNumbers())
		{
			$out = '<div class="phone-number-block" style="margin-bottom: 10px;">' . "\n";
			foreach ($this->getPhoneNumbers() as $phone) 
			{
				//Type for hCard compatibility. Hidden.
				if ($phone->number != null) $out .=  '<strong>' . $phone->name . ':</strong> <span class="tel">' . $this->gethCardTelType($phone->type) . '<span class="value">' .  $phone->number . '</span></span><br />' . "\n";
			}
			$out .= '</div>' . "\n";
		}
		
		if ( !isset($out) || empty($out) ) $out = '';
		
		return $out;
	}
	
	public function gethCardTelType($data)
    {
        //This is here for compatibility for versions 0.2.24 and earlier;
		switch ($data)
		{
			case 'home':
				$type = '<span class="type" style="display: none;">home</span>';
				break;
			case 'homephone':
				$type = '<span class="type" style="display: none;">home</span>';
				break;
			case 'homefax':
				$type = '<span class="type" style="display: none;">home</span><span class="type" style="display: none;">fax</span>';
				break;
			case 'cell':
				$type = '<span class="type" style="display: none;">cell</span>';
				break;
			case 'cellphone':
				$type = '<span class="type" style="display: none;">cell</span>';
				break;
			case 'work':
				$type = '<span class="type" style="display: none;">work</span>';
				break;
			case 'workphone':
				$type = '<span class="type" style="display: none;">work</span>';
				break;
			case 'workfax':
				$type = '<span class="type" style="display: none;">work</span><span class="type" style="display: none;">fax</span>';
				break;
			case 'fax':
				$type = '<span class="type" style="display: none;">work</span><span class="type" style="display: none;">fax</span>';
				break;
			
			default:
				$type = $data;
			break;
		}
		
		return $type;
    }
	
	public function gethCardAdrType($data)
    {
        //This is here for compatibility for versions 0.2.24 and earlier;
		switch ($data)
		{
			case 'home':
				$type = '<span class="type" style="display: none;">home</span>';
				break;
			case 'work':
				$type = '<span class="type" style="display: none;">work</span>';
				break;
			case 'school':
				$type = '<span class="type" style="display: none;">school</span>';
				break;
			case 'other':
				$type = '<span class="type" style="display: none;">other</span>';
				break;
			
			default:
				if ($this->getEntryType() == 'individual')
				{
					$type = '<span class="type" style="display: none;">home</span>';
				}
				elseif ($this->getEntryType() == 'organization')
				{
					$type = '<span class="type" style="display: none;">work</span>';
				}
			break;
		}
		
		return $type;
    }
	
	public function getEmailAddressBlock()
	{
		if ($this->getEmailAddresses())
		{
			$out = '<div class="email-address-block">' . "\n";
			
			foreach ($this->getEmailAddresses() as $email)
			{
				//Type for hCard compatibility. Hidden.
				if ($email->address != NULL) $out .= '<strong>' . $email->name . ':</strong><br /><span class="email"><span class="type" style="display: none;">INTERNET</span><a class="value" href="mailto:' . $email->address . '">' . $email->address . '</a></span><br /><br />' . "\n";
			}
			
			$out .= '</div>' . "\n";
			
			$out = apply_filters('cn_output_email_addresses', $out);
		}
		
		if ( !isset($out) || empty($out) ) $out = '';
		
		return $out;
	}
	
	public function getImBlock()
	{
		if ($this->getIm())
		{
			/**
			 * @TODO: Out as clickable links using hCard spec.
			 */
			$out = '<div class="im-block" style="margin-bottom: 10px;">' . "\n";
			foreach ($this->getIm() as $imRow)
			{
				if ($imRow->id != NULL) $out .= '<span class="im-item"><strong>' . $imRow->name . ':</strong> ' . $imRow->id . '</span><br />' . "\n";
			}
			$out .= '</div>' . "\n";
		}
		
		if ( !isset($out) || empty($out) ) $out = '';
		
		return $out;
	}
	
	public function getSocialMediaBlock()
	{
		if ($this->getSocialMedia())
		{
			$out = '<div class="social-media-block" style="margin-bottom: 10px;">' . "\n";
			foreach ($this->getSocialMedia() as $socialNetwork)
			{
				if ($socialNetwork->url != null) $out .= '<span class="social-media-item"><a class="url uid ' . $socialNetwork->type . '" href="' . $socialNetwork->url . '" target="_blank" title="' . $socialNetwork->name . '">' . $socialNetwork->name . '</a></span><br />' . "\n";
			}
			$out .= '</div>' . "\n";
		}
		
		if ( !isset($out) || empty($out) ) $out = '';
		
		echo $out;
	}
	
	public function getWebsiteBlock()
	{
		$websites = $this->getWebsites();
		
		if ( ! empty($websites) )
		{
			$out = '<div class="website-block" style="margin-bottom: 10px;">' . "\n";
			foreach ($websites as $website)
			{
				if ($website->url != NULL) $out .= '<span class="website-address" style="display: block"><strong>Website:</strong> <a class="url" href="' . $website->url . '" target="_blank">' . $website->url . '</a></span>' . "\n";
			}
			$out .= "</div>" . "\n";
		}
		
		if ( !isset($out) || empty($out) ) $out = '';
		
		return $out;
	}
	
	public function getBirthdayBlock( $format = 'F jS' )
	{
		//NOTE: The vevent span is for hCalendar compatibility.
		//NOTE: The second birthday span [hidden] is for hCard compatibility.
		//NOTE: The third span series [hidden] is for hCalendar compatibility.
		if ($this->getBirthday()) $out = '<span class="vevent"><span class="birthday"><strong>Birthday:</strong> <abbr class="dtstart" title="' . $this->getBirthday('Ymd') .'">' . $this->getBirthday($format) . '</abbr></span>' .
										 '<span class="bday" style="display:none">' . $this->getBirthday('Y-m-d') . '</span>' .
										 '<span class="summary" style="display:none">Birthday - ' . $this->getFullFirstLastName() . '</span> <span class="uid" style="display:none">' . $this->getBirthday('YmdHis') . '</span> </span><br />' . "\n";
		
		if ( !isset($out) || empty($out) ) $out = '';
		
		return $out;
	}
	
	public function getAnniversaryBlock( $format = 'F jS' )
	{
		//NOTE: The vevent span is for hCalendar compatibility.
		if ($this->getAnniversary()) $out = '<span class="vevent"><span class="anniversary"><strong>Anniversary:</strong> <abbr class="dtstart" title="' . $this->getAnniversary('Ymd') . '">' . $this->getAnniversary($format) . '</abbr></span>' .
											'<span class="summary" style="display:none">Anniversary - ' . $this->getFullFirstLastName() . '</span> <span class="uid" style="display:none">' . $this->getAnniversary('YmdHis') . '</span> </span><br />' . "\n";
		
		if ( !isset($out) || empty($out) ) $out = '';
		
		return $out;
	}
	
	public function getNotesBlock()
	{
		return '<div class="note">' . $this->getNotes() . '</div>' . "\n";
	}
	
	public function getBioBlock()
	{
		return '<div class="bio">' . $this->getBio() . '</div>' . "\n";
	}
	
	/**
	 * Displays the category list in a HTML list or custom format
	 * 
	 * @TODO: Implement $parents.
	 * 
	 * Accepted option for the $atts property are:
	 * 		list == string -- The list type to output. Accepted values are ordered || unordered.
	 * 		separator == string -- The category separator.
	 * 		before == string -- HTML to output before the category list.
	 *  	after == string -- HTML to output after the category list.
	 * 		label == string -- String to display after the before attribute but before the category list.
	 * 		parents == bool -- Display the parents
	 * 		return == TRUE || FALSE -- Return string if set to TRUE instead of echo string.
	 * 
	 * @param array $atts [optional]
	 * @return string
	 */
	public function getCategoryBlock($atts = NULL)
	{
		$defaultAtts = array('list' => 'unordered',
							 'separator' => NULL,
							 'before' => NULL,
							 'after' => NULL,
							 'label' => 'Categories: ',
							 'parents' => FALSE,
							 'return' => FALSE
							);
		
		$atts = $this->validate->attributesArray($defaultAtts, (array) $atts);
		
		$out = '';
		$categories = $this->getCategory();
		
		if ( empty($categories) ) return NULL;
		
		if ( !empty($atts['before']) ) $out .= $atts['before'];
		
		if ( !empty($atts['label']) ) $out .= '<span class="cn_category_label">' . $atts['label'] . '</span>';
		
		if ( empty($atts['separator']) )
		{
			$atts['list'] === 'unordered' ? $out .= '<ul class="cn_category_list">' : $out .= '<ol class="cn_category_list">';
			
			foreach ($categories as $category)
			{
				$out .= '<li class="cn_category" id="cn_category_' . $category->term_id . '">' . $category->name . '</li>';
			}
			
			$atts['list'] === 'unordered' ? $out .= '</ul>' : $out .= '</ol>';
		}
		else
		{
			$i = 0;
			
			foreach ($categories as $category)
			{
				$out .= '<span class="cn_category" id="cn_category_' . $category->term_id . '">' . $category->name . '</span>';
				
				$i++;
				if ( count($categories) > $i ) $out .= $atts['separator'];
			}
		}
		
		if ( !empty($atts['after']) ) $out .= $atts['after'];
		
		if ( $atts['return'] ) return $out;
		
		echo $out;	
	}
	
	/**
	 * Displays the category list for use in the class tag.
	 * 
	 * @param bool $return [optional] Return instead of echo.
	 * @return string
	 */
	public function getCategoryClass($return = FALSE)
	{
		$categories = $this->getCategory();
		
		if ( empty($categories) ) return NULL;
		
		foreach ($categories as $category)
		{
			$out[] = $category->slug;
		}
		
		if ($return) return implode(' ', $out);
		
		echo implode(' ', $out);
		
	}
	
	public function getRevisionDateBlock()
	{
		return '<span class="rev">' . date('Y-m-d', strtotime($this->getUnixTimeStamp())) . 'T' . date('H:i:s', strtotime($this->getUnixTimeStamp())) . 'Z' . '</span>' . "\n";
	}
	
	public function getLastUpdatedStyle()
	{
		$age = (int) abs( time() - strtotime( $this->getUnixTimeStamp() ) );
		if ( $age < 657000 )	// less than one week: red
			$ageStyle = ' color:red; ';
		elseif ( $age < 1314000 )	// one-two weeks: maroon
			$ageStyle = ' color:maroon; ';
		elseif ( $age < 2628000 )	// two weeks to one month: green
			$ageStyle = ' color:green; ';
		elseif ( $age < 7884000 )	// one - three months: blue
			$ageStyle = ' color:blue; ';
		elseif ( $age < 15768000 )	// three to six months: navy
			$ageStyle = ' color:navy; ';
		elseif ( $age < 31536000 )	// six months to a year: black
			$ageStyle = ' color:black; ';
		else						// more than one year: don't show the update age
			$ageStyle = ' display:none; ';
		return $ageStyle;
	}
	
	public function returnToTopAnchor()
	{
		return '<a href="#connections-list-head" title="Return to top."><img src="' . WP_PLUGIN_URL . '/connections/images/uparrow.gif" alt="Return to top."/></a>';
	}
	
}

?>