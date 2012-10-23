<?php

class cnOutput extends cnEntry
{
	/**
	 * Echos the 'Entry Sized' image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getCardImage()
	{
		$this->getImage();
	}
	
	/**
	 * Echos the 'Profile Sized' image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getProfileImage()
	{
		$this->getImage( array( 'image' => 'photo' , 'preset' => 'profile' ) );
	}
	
	/**
	 * Echos the 'Thumbnail Sized' image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getThumbnailImage()
	{
		$this->getImage( array( 'image' => 'photo' , 'preset' => 'thumbnail' ) );
	}
	
	/**
	 * Echos the logo image.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getLogoImage( $atts = array() )
	{
		global $connections;
		
		$this->getImage( array( 'image' => 'logo' ) );
	}
	
	/**
	 * Echo or return the image/logo if associated in a HTML hCard compliant string.
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
	 * 	return (bool) Return or echo the string. Default is to echo.
	 * 
	 * NOTE: If only the height or width was set for a custom image size, the opposite image dimension must be set for
	 * the fallback block. This does not apply if the fallback is the default image.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_image => (array) Register the methods default attributes.
	 * 
	 * @todo Enable support for a default image to be set.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array $atts [optional]
	 * @return string
	 */
	public function getImage( $atts = array() )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'image' => 'photo',
			'preset' => 'entry',
			'fallback' => array(
				'type' => 'none',
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
		
		$defaults = apply_filters( 'cn_output_default_atts_image' , $defaults );
		
		$atts = $this->validate->attributesArray( $defaults , $atts );
		if ( isset($atts['fallback']) && is_array($atts['fallback']) ) $atts['fallback'] = $this->validate->attributesArray( $defaults['fallback'] , $atts['fallback'] );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		global $connections;
		$displayImage = FALSE;
		$style = array();
		$tag = array();
		$anchorStart = '';
		$anchorEnd = '</a>';
		$out = '';
		
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
					$atts['alt'] = __('Photo of', 'connections') . ' ' . $this->getName();
					$atts['title'] = __('Photo of', 'connections') . ' ' . $this->getName();
										
					if ( $customSize )
					{
						$atts['src'] = CN_URL . 'includes/timthumb/timthumb.php?src=' .
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
								if ( is_file( CN_IMAGE_PATH . $this->getImageNameCard() ) )
								{
									$atts['image_size'] = @getimagesize( CN_IMAGE_PATH . $this->getImageNameCard() );
									$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameCard();
								}
								break;
							case 'profile':
								if ( is_file( CN_IMAGE_PATH . $this->getImageNameProfile() ) )
								{
									$atts['image_size'] = @getimagesize( CN_IMAGE_PATH . $this->getImageNameProfile() );
									$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameProfile();
								}
								break;
							case 'thumbnail':
								if ( is_file( CN_IMAGE_PATH . $this->getImageNameThumbnail() ) )
								{
									$atts['image_size'] = @getimagesize( CN_IMAGE_PATH . $this->getImageNameThumbnail() );
									$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameThumbnail();
								}
								break;
							default:
								if ( is_file( CN_IMAGE_PATH . $this->getImageNameThumbnail() ) )
								{
									$atts['image_size'] = @getimagesize( CN_IMAGE_PATH . $this->getImageNameCard() );
									$atts['src'] = CN_IMAGE_BASE_URL . $this->getImageNameCard();
								}
								break;
						}
						
						if ( isset( $atts['image_size'] ) && $atts['image_size'] !== FALSE )
						{
							$atts['width'] = $atts['image_size'][0];
							$atts['height'] = $atts['image_size'][1];
						}
					}
				}
				
				/*
				 * Create the link for the image if one was assigned.
				 */
				$links = $this->getLinks( array( 'image' => TRUE ) );
				
				if ( ! empty($links) )
				{
					$link = $links[0];
					
					$anchorStart = '<a href="' . $link->url . '"' . ( ( empty($link->target) ? '' : ' target="' . $link->target . '"' ) ) . ( ( empty($link->followString) ? '' : ' rel="' . $link->followString . '"' ) ) . '>';
				}
				
			break;
			
			case 'logo':
				if ( $this->getLogoLinked() && $this->getLogoDisplay() )
				{
					$displayImage = TRUE;
					$atts['class'] = 'logo';
					$atts['alt'] = __('Logo for', 'connections') . ' ' . $this->getName();
					$atts['title'] = __('Logo for', 'connections') . ' ' . $this->getName();
					
					if ( $customSize )
					{
						$atts['src'] = CN_URL . 'includes/timthumb/timthumb.php?src=' .
									   CN_IMAGE_BASE_URL . $this->getLogoName() . 
									   ( (empty($atts['height']) ) ? '' : '&amp;h=' . $atts['height'] ) . 
									   ( (empty($atts['width']) ) ? '' : '&amp;w=' . $atts['width'] ) . 
									   ( (empty($atts['zc']) ) ? '' : '&amp;zc=' . $atts['zc'] );
					}
					else
					{
						$atts['src'] = CN_IMAGE_BASE_URL . $this->getLogoName();
						$atts['image_size'] = @getimagesize( CN_IMAGE_PATH . $this->getLogoName() );
						
						if ( $atts['image_size'] !== FALSE )
						{
							$atts['width'] = $atts['image_size'][0];
							$atts['height'] = $atts['image_size'][1];
						}
					}
				}
				
				/*
				 * Create the link for the image if one was assigned.
				 */
				$links = $this->getLinks( array( 'logo' => TRUE ) );
				
				if ( ! empty($links) )
				{
					$link = $links[0];
					
					$anchorStart = '<a href="' . $link->url . '"' . ( ( empty($link->target) ? '' : ' target="' . $link->target . '"' ) ) . ( ( empty($link->followString) ? '' : ' rel="' . $link->followString . '"' ) ) . '>';
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
			
			$out = '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image"' . ( ( empty($style) ) ? '' : ' style="' . implode('; ', $style) . ';"') . '>' . ( ( empty($anchorStart) ) ? '' : $anchorStart ) . '<img ' . implode(' ', $tag) . ' />' . ( ( empty($anchorStart) ) ? '' : $anchorEnd ) . '</span></span>';
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
					 * @TODO Enable support for a default image to be set.
					 * NOTE: Use switch for image type to allow a default image for both the image and logo.
					 */
					break;
			}
		}
		
		/*
		 * Return or echo the string.
		 */
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry name in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	format (string) Tokens for the parts of the name.
	 * 		Permitted Tokens:
	 * 			%prefix%
	 * 			%first%
	 * 			%middle%
	 * 			%last%
	 * 			%suffix%
	 * 	before (string) HTML to output before an address.
	 * 	after (string) HTML to after before an address.
	 * 	return (bool) Return or echo the string. Default is to echo.
	 * 
	 * Example:
	 * 	If an entry is an individual this would return their name as Last Name, First Name
	 * 	
	 * 	$this->getName( array( 'format' => '%last%, %first% %middle%' ) );
	 * 
	 * NOTE: If an entry is a organization/family, this will return the organization/family name instead
	 * 		 ignoring the format attribute because it does not apply.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_name => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array $atts [optional]
	 * @return string
	 */
	public function getNameBlock( $atts = array() )
	{
		global $connections;
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'format' => '%prefix% %first% %middle% %last% %suffix%',
			'link' => FALSE,
			'target' => 'name',
			'before' => '',
			'after' => '',
			'return' => FALSE
		);
		
		$defaults = apply_filters( 'cn_output_default_atts_name' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$search = array('%prefix%', '%first%', '%middle%', '%last%', '%suffix%');
		$replace = array();
		$honorificPrefix = $this->getHonorificPrefix();
		$first = $this->getFirstName();
		$middle = $this->getMiddleName();
		$last = $this->getLastName();
		$honorificSuffix = $this->getHonorificSuffix();
		
		switch ( $this->getEntryType() )
		{
			case 'individual':
				( empty($honorificPrefix) ) ? $replace[] = '' : $replace[] = '<span class="honorific-prefix">' . $honorificPrefix . '</span>';
				
				( empty($first) ) ? $replace[] = '' : $replace[] = '<span class="given-name">' . $first . '</span>';
				
				( empty($middle) ) ? $replace[] = '' : $replace[] = '<span class="additional-name">' . $middle . '</span>';
				
				( empty($last) ) ? $replace[] = '' : $replace[] = '<span class="family-name">' . $last . '</span>';
				
				( empty($honorificSuffix) ) ? $replace[] = '' : $replace[] = '<span class="honorific-suffix">' . $honorificSuffix . '</span>';
				
				$out = '<span class="fn n">' . str_ireplace( $search, $replace, $atts['format'] ) . '</span>';
			break;
			
			case 'organization':
				$out = '<span class="org fn">' . $this->getOrganization() . '</span>';
			break;
			
			case 'family':
				$out = '<span class="fn n"><span class="family-name">' . $this->getFamilyName() . '</span></span>';
			break;
			
			default:
				( empty($honorificPrefix) ) ? $replace[] = '' : $replace[] = '<span class="honorific-prefix">' . $honorificPrefix . '</span>';
				
				( empty($first) ) ? $replace[] = '' : $replace[] = '<span class="given-name">' . $first . '</span>';
				
				( empty($middle) ) ? $replace[] = '' : $replace[] = '<span class="additional-name">' . $middle . '</span>';
				
				( empty($last) ) ? $replace[] = '' : $replace[] = '<span class="family-name">' . $last . '</span>';
				
				( empty($honorificSuffix) ) ? $replace[] = '' : $replace[] = '<span class="honorific-suffix">' . $honorificSuffix . '</span>';
				
				$out = '<span class="fn n">' . str_ireplace( $search, $replace, $atts['format'] ) . '</span>';
			break;
		}
		
		if ( $connections->settings->get('connections', 'connections_link', 'name') )
		{
			$out = $connections->url->permalink( array(
				'type' => $atts['target'],
				'slug' => $this->getSlug(),
				'title' => $this->getName($atts),
				'text' => $out,
				'return' => TRUE
				)
			);
		}
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Returns the Entry's full first and last name.
	 * 
	 * NOTE: If an entry is a organization/family, this will return the organization/family name instead
	 * 		 ignoring the format attribute because it does not apply. 
	 * 
	 * @deprecated since 0.7.2.0
	 * @return string
	 */
    public function getFullFirstLastNameBlock()
    {
        return $this->getNameBlock( array('format' => '%prefix% %first% %middle% %last% %suffix%', 'return' => TRUE) );		
    }
    
	/**
	 * Returns the Entry's full first and last name with the last name first.
	 * 
	 * NOTE: If an entry is a organization/family, this will return the organization/family name instead
	 * 		 ignoring the format attribute because it does not apply. 
	 * 
	 * @deprecated since 0.7.2.0
	 * @return string
	 */ 
    public function getFullLastFirstNameBlock()
    {
    	return $this->getNameBlock( array('format' => '%last%, %first% %middle%', 'return' => TRUE) );	
    }
	
	/**
	 * Echos the family members of the family entry type.
	 * 
	 * @deprecated since 0.7.1.0
	 * @return string
	 */
	public function getConnectionGroupBlock()
	{
		$this->getFamilyMemberBlock();
	}
	
	/**
	 * Echos the family members of the family entry type.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @return string
	 */
	public function getFamilyMemberBlock()
	{
		if ( $this->getFamilyMembers() )
		{
			global $connections;
			
			foreach ($this->getFamilyMembers() as $key => $value)
			{
				$relation = new cnEntry();
				$relationName = '';
				
				$relation->set($key);
				$relationType = $connections->options->getFamilyRelation($value);
				
				$relationName = cnURL::permalink( array(
					'type' => 'name',
					'slug' => $relation->getSlug(),
					'title' => $relation->getName(),
					'text' => $relation->getName(),
					'return' => TRUE
					)
				);
				
				echo '<span><strong>' . $relationType . ':</strong> ' . $relationName . '</span><br />' . "\n";
				unset($relation);
			}
		}
	}
	
	/**
	 * Echo or return the entry's title in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	before (string) HTML to output before an address.
	 * 	after (string) HTML to after before an address.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_title => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @return string
	 */
	public function getTitleBlock( $atts = array() )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'before' => '',
			'after' => '',
			'return' => FALSE
		);
		
		$defaults = apply_filters( 'cn_output_default_atts_title' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$title = $this->getTitle();
		
		if ( ! empty($title) )
		{
			$out .= '<span class="title">' . $title . '</span>';
		}
		else
		{
			return '';
		}
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry's organization and/or departartment in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	before (string) HTML to output before an address.
	 * 	after (string) HTML to after before an address.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_orgunit => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @return string
	 */
	public function getOrgUnitBlock( $atts = array() )
	{
		$out = '';
		$org = $this->getOrganization();
		$dept = $this->getDepartment();
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'before' => '',
			'after' => '',
			'return' => FALSE
		);
		
		$defaults = apply_filters( 'cn_output_default_atts_orgunit' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		if ( ! empty($org) || ! empty($dept) )
		{
			$out .= '<span class="org">';
			if ( ! empty($org) ) $out .= '<span class="organization-name"' . ( ( $this->getEntryType() == 'organization' ) ? ' style="display: none;"' : '' ) . '>' . $org . '</span>';
			if ( ! empty($dept) ) $out .= '<span class="organization-unit">' . $dept . '</span>';
			$out .= '</span>';
		}
		else
		{
			return '';
		}
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Return the entry's organization and/or departartment in a HTML hCard compliant string.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getOrganizationBlock()
	{
		return $this->getOrgUnitBlock( array( 'return' => TRUE ) );
	}
	
	/**
	 * Return the entry's organization and/or departartment in a HTML hCard compliant string.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getDepartmentBlock()
	{
		return $this->getOrgUnitBlock( array( 'return' => TRUE ) );
	}
	
	/**
	 * Echo or return the entry's contact name in a HTML string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	format (string) Tokens for the parts of the name.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%first%
	 * 			%last%
	 * 			%separator%
	 * 	label (string) The label to be displayed for the contact name.
	 * 	separator (string) The separator to use.
	 * 	before (string) HTML to output before an address.
	 * 	after (string) HTML to after before an address.
	 * 	return (bool) Return or echo the string. Default is to echo.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_contact_name => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array $atts [optional]
	 * @return string
	 */
	public function getContactNameBlock( $atts = array() )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'format' => '%label%: %first% %last%',
			'label' => __('Contact', 'connections'),
			'separator' => ':',
			'before' => '',
			'after' => '',
			'return' => FALSE
		);
		
		$defaults = apply_filters( 'cn_output_default_atts_contact_name' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$search = array('%label%','%first%', '%last%' , '%separator%');
		$replace = array();
		$first = $this->getContactFirstName();
		$last = $this->getContactLastName();
		
		if ( empty($first) && empty($last) ) return '';
		
		( empty($first) && empty($last) ) ? $replace[] = '' : $replace[] = '<span class="contact-label">' . $atts['label'] . '</span>';
		
		( empty($first) ) ? $replace[] = '' : $replace[] = '<span class="contact-given-name">' . $first . '</span>';
		
		( empty($last) ) ? $replace[] = '' : $replace[] = '<span class="contact-family-name">' . $last . '</span>';
		
		$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
		
		$out = '<span class="contact-name">' . str_ireplace( $search, $replace, $atts['format'] ) . '</span>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry's addresses in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry address.
	 * 	type (array) || (string) Retrieve specific address types.
	 * 		Permitted Types:
	 * 			home
	 * 			work
	 * 			school
	 * 			other
	 * 	city (array) || (string) Retrieve addresses in a specific city.
	 * 	state (array) || (string) Retrieve addresses in a specific state..
	 * 	zipcode (array) || (string) Retrieve addresses in a specific zipcode.
	 * 	country (array) || (string) Retrieve addresses in a specific country.
	 * 	coordinates (array) Retrieve addresses in with specific coordinates. Both latitude and longitude must be supplied.
	 * 	format (string) The tokens to use to display the address block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%line1%
	 * 			%line2%
	 * 			%line3%
	 * 			%city%
	 * 			%state%
	 * 			%zipcode%
	 * 			%country%
	 * 			%geo%
	 * 			%separator%
	 * 	separator (string) The separator to use.
	 * 	before (string) HTML to output before the addresses.
	 * 	after (string) HTML to after before the addresses.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_address => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @param (bool) [optional] $cached Returns the cached address rather than querying the db.
	 * @return string
	 */
	public function getAddressBlock( $atts = array() , $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['city'] = NULL;
		$defaults['state'] = NULL;
		$defaults['zipcode'] = NULL;
		$defaults['country'] = NULL;
		$defaults['coordinates'] = array();
		$defaults['format'] = '%label% %line1% %line2% %line3% %city% %state%  %zipcode% %country%';
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_address' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$addresses = $this->getAddresses( $atts , $cached );
		$search = array('%label%' , '%line1%' , '%line2%' , '%line3%' , '%city%' , '%state%' , '%zipcode%' , '%country%' , '%geo%' , '%separator%');
		
		if ( empty($addresses) ) return '';
		
		$out .= '<span class="address-block">';
		
		foreach ($addresses as $address)
		{
			$replace = array();
			
			$out .= "\n" . '<span class="adr">';
			
				( empty($address->name) ) ? $replace[] = '' : $replace[] = '<span class="address-name">' . $address->name . '</span>';
				( empty($address->line_1) ) ? $replace[] = '' : $replace[] = '<span class="street-address">' . $address->line_1 . '</span>';
				( empty($address->line_2) ) ? $replace[] = '' : $replace[] = '<span class="street-address">' . $address->line_2 . '</span>';
				( empty($address->line_3) ) ? $replace[] = '' : $replace[] = '<span class="street-address">' . $address->line_3 . '</span>';
				
				( empty($address->city) ) ? $replace[] = '' : $replace[] = '<span class="locality">' . $address->city . '</span>';
				( empty($address->state) ) ? $replace[] = '' : $replace[] = '<span class="region">' . $address->state . '</span>';
				( empty($address->zipcode) ) ? $replace[] = '' : $replace[] = '<span class="postal-code">' . $address->zipcode . '</span>';
				
				( empty($address->country) ) ? $replace[] = '' : $replace[] = '<span class="country-name">' . $address->country . '</span>';
				
				if ( ! empty($address->latitude) || ! empty($address->longitude) )
				{
					 $replace[] = '<span class="geo">' . 
					 	( ( empty($address->latitude) ) ? '' : '<span class="latitude" title="' . $address->latitude . '"><span class="cn-label">' . __('Latitude', 'connections') . ': </span>' . $address->latitude . '</span>' ) . 
						( ( empty($address->longitude) ) ? '' : '<span class="longitude" title="' . $address->longitude . '"><span class="cn-label">' . __('Longitude', 'connections') . ': </span>' . $address->longitude . '</span>' ) . 
						'</span>';
				}
				
				$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
				
				$out .= str_ireplace( $search , $replace , $atts['format'] );
				
				// Set the hCard Address Type.
				$out .= $this->gethCardAdrType($address->type);
				
			$out .= '</span>' . "\n";
		}
		
		$out .= '</span>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return a <div> with the entry's address within the HTML5 data- attribute. To be used for
	 * placing a Google Map in with the jQuery goMap plugin.
	 * 
	 * NOTE: wp_enqueue_script('jquery-gomap-min') must called before use, otherwise just an empty div will be diaplayed.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry address.
	 * 	type (array) || (string) Retrieve specific address types.
	 * 		Permitted Types:
	 * 			home
	 * 			work
	 * 			school
	 * 			other
	 * 	static (bool) Query map via the Google Static Maps API
	 * 	maptype (string) Valid types are: HYBRID, ROADMAP, SATELLITE, TERRAIN
	 * 	zoom (int) Sets the zoom level.
	 * 	height (int) Specifiy the div height in px.
	 * 	width (int) Specifiy the div widdth in px.
	 * 	coordinates (array) Retrieve addresses in with specific coordinates. Both latitude and longitude must be supplied.
	 * 	before (string) HTML to output before the addresses.
	 * 	after (string) HTML to after before the addresses.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * @TODO Add support for the Google Maps API Premier client id.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_contact_name => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @param (bool) [optiona] $cached Returns the cached address rather than querying the db.
	 * @return string
	 */
	public function getMapBlock( $atts = array() , $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['static'] = FALSE;
		$defaults['maptype'] = 'ROADMAP';
		$defaults['zoom'] = 13;
		$defaults['height'] = 400;
		$defaults['width'] = 400;
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_contact_name' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$attr = array();
		$addr = array();
		$geo = array();
		
		// Limit the map type to one of the valid types to prevent user error.
		$permittedMapTypes = array( 'HYBRID', 'ROADMAP', 'SATELLITE', 'TERRAIN' );
		$atts['maptype'] = strtoupper( $atts['maptype'] );
		if ( ! in_array( $atts['maptype'] , $permittedMapTypes ) ) $atts['maptype'] = 'ROADMAP';
		
		// Limit the user specified zoom level to between 0 and 21
		if ( ! in_array( $atts['zoom'] , range(0, 21) ) ) $atts['zoom'] = 13;
		
		// Ensure the requested map size does not exceed the permitted sizes permitted by the Google Static Maps API
		if ( $atts['static'] ) $atts['width'] = ( $atts['width'] <= 640 ) ? $atts['width'] : 640;
		if ( $atts['static'] ) $atts['height'] = ( $atts['height'] <= 640 ) ? $atts['height'] : 640;
		
		$addresses = $this->getAddresses( $atts , $cached );
		
		if ( empty($addresses) ) return '';
		
		if ( ! empty($addresses[0]->line_one) ) $addr[] = $addresses[0]->line_one;
		if ( ! empty($addresses[0]->line_two) ) $addr[] = $addresses[0]->line_two;
		if ( ! empty($addresses[0]->city) ) $addr[] = $addresses[0]->city;
		if ( ! empty($addresses[0]->state) ) $addr[] = $addresses[0]->state;
		if ( ! empty($addresses[0]->zipcode) ) $addr[] = $addresses[0]->zipcode;
		
		if ( ! empty($addresses[0]->latitude) && ! empty($addresses[0]->longitude) )
		{
			$geo['latitude'] = $addresses[0]->latitude;
			$geo['longitude'] = $addresses[0]->longitude;
		}
		
		if ( empty($addr) && empty($geo) ) return '';
		
		if ( $atts['static'] )
		{
			$attr['center'] = ( empty($geo) ) ? implode( ', ' , $addr ) : implode( ',' , $geo );
			$attr['markers'] = $attr['center'];
			$attr['size'] = $atts['width'] . 'x' . $atts['height'];
			$attr['maptype'] = $atts['maptype'];
			$attr['zoom'] = $atts['zoom'];
			//$attr['scale'] = 2;
			$attr['format'] = 'png';
			$attr['sensor'] = 'false';
			
			$out .= '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image" style="height: ' . $atts['height'] . '; width: ' . $atts['width'] . '">';
			$out .= '<img class="map" title="' . $attr['center'] . '" alt="' . $attr['center'] . '" width="' . $atts['width'] . '" height="' . $atts['height'] . '" src="http://maps.googleapis.com/maps/api/staticmap?' . http_build_query( $attr , '' , '&amp;' ) . '"/>';
			$out .= '</span></span>';
		}
		else
		{
			$attr[] = 'id="map-' . $this->getRuid() . '"';
			if ( ! empty($addr) ) $attr[] = 'data-address="' . implode(', ', $addr) .'"';
			if ( ! empty($geo['latitude']) ) $attr[] = 'data-latitude="' . $geo['latitude'] .'"';
			if ( ! empty($geo['longitude']) ) $attr[] = 'data-longitude="' . $geo['longitude'] .'"';
			$attr[] = 'style="' . ( ! empty( $atts['width'] ) ? 'width: ' . $atts['width'] . 'px; ' : '' ) . 'height: ' . $atts['height'] . 'px"';
			$attr[] = 'data-maptype="' . $atts['maptype'] .  '"';
			$attr[] = 'data-zoom="' . $atts['zoom'] .  '"';
			
			$out = '<div ' . implode(' ', $attr) . '></div>';
		}
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry's phone numbers in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry phone number.
	 * 	type (array) || (string) Retrieve specific phone number types.
	 * 		Permitted Types:
	 * 			homephone
	 * 			homefax
	 * 			cellphone
	 * 			workphone
	 * 			workfax
	 * 	format (string) The tokens to use to display the phone number block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%number%
	 * 			%separator%
	 * 	separator (string) The separator to use.
	 * 	before (string) HTML to output before the phone numbers.
	 * 	after (string) HTML to after before the phone numbers.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_phone => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @param (bool) [optional] $cached Returns the cached data rather than querying the db.
	 * @return string
	 */
	public function getPhoneNumberBlock( $atts = array() , $cached = TRUE )
	{
		global $connections;
		
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['format'] = '%label%%separator% %number%';
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_phone' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$phoneNumbers = $this->getPhoneNumbers( $atts , $cached );
		$search = array('%label%' , '%number%' , '%separator%');
		
		if ( empty($phoneNumbers) ) return '';
		
		$out .= '<span class="phone-number-block">';
		
		foreach ( $phoneNumbers as $phone ) 
		{
			$replace = array();
			
			$out .= "\n" . '<span class="tel">';
			
				( empty($phone->name) ) ? $replace[] = '' : $replace[] = '<span class="phone-name">' . $phone->name . '</span>';
				
				if ( empty($phone->number) )
				{
					$replace[] = '';
				} else {
					
					if ( $connections->settings->get('connections', 'connections_link', 'phone') )
					{
						$replace[] = '<a class="value" href="tel:' . $phone->number . '" value="' . preg_replace('/[^0-9]/', '', $phone->number) . '">' . $phone->number . '</a>';
					} else {
						$replace[] = '<span class="value">' . $phone->number . '</span>';
					}
					
				}
				
				$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
				
				$out .= str_ireplace( $search , $replace , $atts['format'] );
				
				// Set the hCard Phone Number Type.
				$out .= $this->gethCardTelType($phone->type);
				
			$out .= '</span>' . "\n";
		}
		
		$out .= '</span>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Returns the entry's telephone type in a HTML hCard compliant string.
	 * 
	 * @url http://microformats.org/wiki/hcard-cheatsheet
	 * @access private
	 * @since unknown
	 * @version 1.0
	 * @param (string) $data
	 * @return string
	 */
	public function gethCardTelType($data)
    {
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
		}
		
		return $type;
    }
	
	/**
	 * Returns the entry's address type in a HTML hCard compliant string.
	 * 
	 * @url http://microformats.org/wiki/adr-cheatsheet#Properties_.28Class_Names.29
	 * @access private
	 * @since unknown
	 * @version 1.0
	 * @param (string) $data
	 * @return string
	 */
	public function gethCardAdrType($adrType)
    {
        switch ($adrType)
		{
			case 'home':
				$type = '<span class="type" style="display: none;">home</span>';
				break;
			case 'work':
				$type = '<span class="type" style="display: none;">work</span>';
				break;
			case 'school':
				$type = '<span class="type" style="display: none;">postal</span>';
				break;
			case 'other':
				$type = '<span class="type" style="display: none;">postal</span>';
				break;
			
			default:
				$type = '<span class="type" style="display: none;">postal</span>';
				break;
		}
		
		return $type;
    }
	
	/**
	 * Echo or return the entry's email addresses in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry email address.
	 * 	type (array) || (string) Retrieve specific email address types.
	 * 		Permitted Types:
	 * 			personal
	 * 			work
	 * 	format (string) The tokens to use to display the email address block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%address%
	 * 			%icon%
	 * 			%separator%
	 * 	title (string) The link title attribute. Accepts tokens.
	 * 		Permitted Tokens:
	 * 			Name tokens:
	 * 				%prefix%
	 * 				%first%
	 * 				%middle%
	 * 				%last%
	 * 				%suffix%
	 * 			Email tokens:
	 * 				%type%
	 * 				%name%
	 * 	size (int) the icon size. Permitted sizes are 16, 24, 32, 48.
	 * 	separator (string) The separator to use.
	 * 	before (string) HTML to output before the email addresses.
	 * 	after (string) HTML to after before the email addresses.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_email => (array) Register the methods default attributes.
	 * 
	 * @access puplic
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @param (bool) [optional] $cached Returns the cached data rather than querying the db.
	 * @return string
	 */
	public function getEmailAddressBlock( $atts = array() , $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['format'] = '%label%%separator% %address%';
		$defaults['title'] = '%first% %last% %type% email.';
		$defaults['size'] = 32;
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_email' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$emailAddresses = $this->getEmailAddresses( $atts , $cached );
		$search = array('%label%' , '%address%' , '%icon%' , '%separator%');
		$iconSizes = array(16, 24, 32, 48);
		
		// Replace the 'Name Tokens' with the entry's name.
		$title = $this->getName( array( 'format' => $atts['title' ] ) );
		
		/*
		 * Ensure the supplied size is valid, if not reset to the default value.
		 */
		( in_array($atts['size'], $iconSizes) ) ? $iconSize = $atts['size'] : $iconSize = 32;
		
		if ( empty($emailAddresses) ) return '';
		
		$out .= '<span class="email-address-block">';
		
		foreach ( $emailAddresses as $email) 
		{
			$replace = array();
			
			$out .= "\n" . '<span class="email">';
				
				// Replace the 'Email Tokens' with the email info.
				$title = str_ireplace( array('%type%', '%name%') , array($email->type, $email->name), $title);
				
				$replace[] = ( empty($email->name) ) ? '' : '<span class="email-name">' . $email->name . '</span>';
				$replace[] = ( empty($email->address) ) ? '' : '<span class="email-address"><a class="value" title="' . $title . '" href="mailto:' . $email->address . '">' . $email->address . '</a></span>';
				$replace[] = ( empty($email->address) ) ? '' : '<span class="email-icon"><a class="value" title="' . $title . '" href="mailto:' . $email->address . '"><image src="' . CN_URL . 'images/icons/mail/mail_' . $iconSize . '.png" height="' . $iconSize . 'px" width="' . $iconSize . 'px"/></a></span>';
				$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
				
				$out .= str_ireplace( $search , $replace , $atts['format'] );
				
				// Set the hCard Email Address Type.
				$out .= '<span class="type" style="display: none;">INTERNET</span>';
				
			$out .= '</span>' . "\n";
		}
		
		$out .= '</span>';
		
		// This filter is required to allow the ROT13 encyption plugin to function.
		$out = apply_filters('cn_output_email_addresses', $out);
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry's IM network IDs in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry IM network.
	 * 	type (array) || (string) Retrieve specific IM network types.
	 * 		Permitted Types:
	 * 			aim
	 * 			yahoo
	 * 			jabber
	 * 			messenger
	 * 			skype
	 * 	format (string) The tokens to use to display the IM network block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%id%
	 * 			%separator%
	 * 	separator (string) The separator to use.
	 * 	before (string) HTML to output before the IM networks.
	 * 	after (string) HTML to after before the IM networks.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_im => (array) Register the methods default attributes.
	 * 
	 * @url http://microformats.org/wiki/hcard-examples#New_Types_of_Contact_Info
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @param (bool) [optiona] $cached Returns the cached data rather than querying the db.
	 * @return string
	 */
	public function getImBlock( $atts = array() , $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['format'] = '%label%%separator% %id%';
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_im' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$networks = $this->getIm( $atts , $cached );
		$search = array('%label%' , '%id%' , '%separator%');
		
		if ( empty($networks) ) return '';
		
		$out .= '<span class="im-network-block">';
		
		foreach ( $networks as $network )
		{
			$replace = array();
			
			$out .= "\n" . '<span class="im-network">';
				
				( empty($network->name) ) ? $replace[] = '' : $replace[] = '<span class="im-name">' . $network->name . '</span>';
				
				switch ( $network->type )
				{
					case 'aim':
						( empty($network->id) ) ? $replace[] = '' : $replace[] = '<a class="url im-id" href="aim:goim?screenname=' . $network->id . '">' . $network->id . '</a>';
						break;
					
					case 'yahoo':
						( empty($network->id) ) ? $replace[] = '' : $replace[] = '<a class="url im-id" href="ymsgr:sendIM?' . $network->id . '">' . $network->id . '</a>';
						break;
						
					case 'jabber':
						( empty($network->id) ) ? $replace[] = '' : $replace[] = '<span class="im-id">' . $network->id . '</span>';
						break;
						
					case 'messenger':
						( empty($network->id) ) ? $replace[] = '' : $replace[] = '<a class="url im-id" href="msnim:chat?contact=' . $network->id . '">' . $network->id . '</a>';
						break;
						
					case 'skype':
						( empty($network->id) ) ? $replace[] = '' : $replace[] = '<a class="url im-id" href="skype:' . $network->id . '?chat">' . $network->id . '</a>';
						break;
					
					case 'icq':
						( empty($network->id) ) ? $replace[] = '' : $replace[] = '<a class="url im-id" type="application/x-icq" href="http://www.icq.com/people/cmd.php?uin=' . $network->id . '&action=message">' . $network->id . '</a>';
						break;
						
					default:
						( empty($network->id) ) ? $replace[] = '' : $replace[] = '<span class="im-id">' . $network->id . '</span>';
						break;
				}
				
				$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
				
				$out .= str_ireplace( $search , $replace , $atts['format'] );
				
			$out .= '</span>' . "\n";
		}
		
		$out .= '</span>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry's social media network IDs in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry social media network.
	 * 	type (array) || (string) Retrieve specific social media network types.
	 * 		Permitted Types:
	 * 			delicious
	 * 			cdbaby
	 * 			facebook
	 * 			flickr
	 * 			itunes
	 * 			linked-in
	 * 			mixcloud
	 * 			myspace
	 * 			podcast
	 * 			reverbnation
	 * 			rss
	 * 			technorati
	 * 			twitter
	 * 			soundcloud
	 * 			vimeo
	 * 			youtube
	 * 	format (string) The tokens to use to display the social media block parts.
	 * 		Permitted Tokens:
	 * 			%title%
	 * 			%url%
	 * 			%icon%
	 * 			%separator%
	 * 	style (string) The icon style to be used.
	 * 		Permitted Styles:
	 * 			wpzoom
	 * 		Permitted Sizes:
	 * 			16
	 * 			24
	 * 			32
	 * 			48
	 * 			64
	 * 	size (int) The icon size to be used.
	 * 	separator (string) The separator to use.
	 * 	before (string) HTML to output before the social media networks.
	 * 	after (string) HTML to after before the social media networks.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_socialmedia => (array) Register the methods default attributes.
	 * 
	 * @url http://microformats.org/wiki/hcard-examples#Site_profiles
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @param (bool) [optional] $cached Returns the cached data rather than querying the db.
	 * @return string
	 */
	public function getSocialMediaBlock( $atts = array() , $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['format'] = '%icon%';
		$defaults['style'] = 'wpzoom';
		$defaults['size'] = 32;
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_socialmedia' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$networks = $this->getSocialMedia( $atts , $cached );
		$search = array('%label%' , '%url%' , '%icon%' , '%separator%');
		
		$iconStyles = array('wpzoom');
		$iconSizes = array(16, 24, 32, 48, 64);
		
		/*
		 * Ensure the supplied icon style and size are valid, if not reset to the default values.
		 */
		( in_array($atts['style'], $iconStyles) ) ? $iconStyle = $atts['style'] : $iconStyle = 'wpzoom';
		( in_array($atts['size'], $iconSizes) ) ? $iconSize = $atts['size'] : $iconSize = 32;
		
		if ( empty($networks) ) return '';
		
		$out = '<span class="social-media-block">';
		
		foreach ( $networks as $network )
		{
			$replace = array();
			$iconClass = array();
			
			/*
			 * Create the icon image class. This array will implode to a string.
			 */
			$iconClass[] = $network->type;
			$iconClass[] = $iconStyle;
			$iconClass[] = 'sz-' . $iconSize;
			
			$out .= '<span class="social-media-network">';
			
				$replace[] = '<a class="url ' . $network->type . '" href="' . $network->url . '" target="_blank" title="' . $network->name . '">' . $network->name . '</a>';
				
				$replace[] = '<a class="url ' . $network->type . '" href="' . $network->url . '" target="_blank" title="' . $network->name . '">' . $network->url . '</a>';
				
				$replace[] = '<a class="url ' . $network->type . '" href="' . $network->url . '" target="_blank" title="' . $network->name . '"><image class="' . implode(' ', $iconClass) . '" src="' . CN_URL . 'images/icons/' . $iconStyle . '/' . $iconSize . '/' . $network->type . '.png" height="' . $iconSize . 'px" width="' . $iconSize . 'px" style="width: ' . $iconSize . 'px; height: ' . $iconSize . 'px;"/></a>';
				
				$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
				
				$out .= str_ireplace( $search , $replace , $atts['format'] );
				
			$out .= '</span>';
		}
		
		$out .= '</span>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Return the entry's websites in a HTML hCard compliant string.
	 * 
	 * @deprecated since 0.7.2.0
	 */
	public function getWebsiteBlock()
	{
		/*
		 * Set some defaults so the result resembles how the previous rendered.
		 */
		return $this->getLinkBlock( array( 'format' => '%label%: %url%' , 'type' => array('personal', 'website') , 'return' => TRUE ) );
	}
	
	/**
	 * Echo or return the entry's links in a HTML hCard compliant string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry link.
	 * 	type (array) || (string) Retrieve specific link types.
	 * 		Permitted Types:
	 * 			website
	 * 			blog
	 * 	format (string) The tokens to use to display the phone number block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%title%
	 * 			%url%
	 * 			%image%
	 * 			%separator%
	 * 	label (string) The label to be displayed for the links.
	 * 	size (string) The valid image sizes. Valid values are: mcr || tny || vsm || sm || lg || xlg
	 * 	separator (string) The separator to use.
	 * 	before (string) HTML to output before the social media networks.
	 * 	after (string) HTML to after before the social media networks.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_link => (array) Register the methods default attributes.
	 * 
	 * @url http://microformats.org/wiki/hcard-examples#Site_profiles
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param (array) $atts Accepted values as noted above.
	 * @param (bool) [optional] $cached Returns the cached data rather than querying the db.
	 * @return string
	 */
	public function getLinkBlock( $atts = array() , $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['format'] = '%label%%separator% %title%';
		$defaults['label'] = NULL;
		$defaults['size'] = 'lg';
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_link' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$links = $this->getLinks( $atts , $cached );
		$search = array('%label%' , '%title%' , '%url%' , '%image%' , '%separator%');
		
		if ( empty($links) ) return '';
		
		$out .= '<span class="link-block">';
		
		foreach ( $links as $link )
		{
			$replace = array();
			$imgBlock = '';
			$queryURL = '';
			$imageTag ='';
			
			$out .= "\n" . '<span class="link ' . $link->type . '">';
			
				if ( empty( $atts['label'] ) )
				{
					$replace[] = ( empty($link->name) ) ? '' : '<span class="link-name">' . $link->name . '</span>';
				}
				else
				{
					$replace[] = '<span class="link-name">' . $atts['label'] . '</span>';
				}
				
				
				( empty($link->title) ) ? $replace[] = '' : $replace[] = '<a class="url" href="' . $link->url . '"' . ( ( empty($link->target) ? '' : ' target="' . $link->target . '"' ) ) . ( ( empty($link->followString) ? '' : ' rel="' . $link->followString . '"' ) ) . '>' . $link->title . '</a>';
				( empty($link->url) ) ? $replace[] = '' : $replace[] = '<a class="url" href="' . $link->url . '"' . ( ( empty($link->target) ? '' : ' target="' . $link->target . '"' ) ) . ( ( empty($link->followString) ? '' : ' rel="' . $link->followString . '"' ) ) . '>' . $link->url . '</a>';
				
				
				// Set the image size; These string values match the valid size for http://www.shrinktheweb.com
				switch ( $atts['size'] )
				{
					case 'mcr':
						$width = 75;
						$height = 56;
						break;
						
					case 'tny':
						$width = 90;
						$height = 68;
						break;
						
					case 'vsm':
						$width = 100;
						$height = 75;
						break;
						
					case 'sm':
						$width = 120;
						$height = 90;
						break;
						
					case 'lg':
						$width = 200;
						$height = 150;
						break;
						
					case 'xlg':
						$width = 320;
						$height = 240;
						break;
				}
				
				if ( $this->validate->url( $link->url , FALSE ) == 1 )
				{
					// Create the query the WordPress for the webshot to be displayed.
					$queryURL = 'http://s.wordpress.com/mshots/v1/' . urlencode($link->url) . '?w=' . $width;
					$imageTag = '<img class="screenshot" alt="' . esc_attr($link->url) . '" width="' . $width . '" src="' . $queryURL . '" />';
					
					$imgBlock .= '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image" style="height: ' . $height . '; width: ' . $width . '">';
					$imgBlock .= '<a class="url" href="' . $link->url . '"' . ( ( empty($link->target) ? '' : ' target="' . $link->target . '"' ) ) . ( ( empty($link->followString) ? '' : ' rel="' . $link->followString . '"' ) ) . '>' . $imageTag . '</a>';
					$imgBlock .= '</span></span>';
					
					$replace[] = $imgBlock;
				}
				else
				{
					$replace[] = '';
				}
				
				$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
				
				$out .= str_ireplace( $search , $replace , $atts['format'] );
				
			$out .= '</span>' . "\n";
		}
		
		$out .= '</span>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	
	/**
	 * Echo or return the entry's dates in a HTML string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	preferred (bool) Retrieve the preferred entry date.
	 * 	type (array) || (string) Retrieve specific date types.
	 * 		Permitted Types:
	 * 			baptism
	 * 			certification
	 * 			employment
	 * 			membership
	 * 			graduate_high_school
	 * 			graduate_college
	 * 			ordination
	 * 
	 * 	format (string) The tokens to use to display the date block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%date%
	 * 			%separator%
	 * 	name_format (string) Tokens for the parts of the name. See cnOutput::getNameBlock
	 * 	date_format (string) See http://php.net/manual/en/function.date.php
	 * 	separator (string) The separator to use between the label and date.
	 * 	before (string) HTML to output before the dates.
	 * 	after (string) HTML to after before the dates.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * Filters:
	 * 	cn_output_default_atts_date => (array) Register the methods default attributes.
	 * 
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @param (array) [optional] $atts Accepted values as noted above.
	 * @param (bool) [optional] $cached Returns the cached data rather than querying the db.
	 * @return string
	 */
	public function getDateBlock( $atts = array() , $cached = TRUE )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['preferred'] = NULL;
		$defaults['type'] = NULL;
		$defaults['format'] = '%label%%separator% %date%';
		$defaults['name_format'] = '%prefix% %first% %middle% %last% %suffix%';
		$defaults['date_format'] = 'F jS Y';
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
		
		$defaults = apply_filters( 'cn_output_default_atts_date' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		$atts['id'] = $this->getId();
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$dates = $this->getDates( $atts , $cached );
		$search = array('%label%' , '%date%' , '%separator%');
		
		if ( empty($dates) ) return '';
		
		$out .= '<span class="date-block">';
		
		foreach ( $dates as $date ) 
		{
			$replace = array();
			$dateObject = new DateTime($date->date);
			
			$out .= "\n" . '<span class="vevent">';
				
				// Hidden elements are to maintain hCalendar spec compatibility
				$replace[] = ( empty($date->name) ) ? '' : '<span class="date-name">' . $date->name . '</span>';
				//$replace[] = ( empty($date->date) ) ? '' : '<span class="dtstart"><span class="value" style="display: none;">' . $dateObject->format( 'Y-m-d' ) . '</span><span class="date-displayed">' . $dateObject->format( $atts['date_format'] ) . '</span></span>';
				$replace[] = ( empty($date->date) ) ? '' : '<abbr class="dtstart" title="' . $dateObject->format('Ymd') .'">' . $dateObject->format( $atts['date_format'] ) . '</abbr><span class="summary" style="display:none">' . $date->name . ' - ' . $this->getName( array( 'format' => $atts['name_format'] ) ) . '</span><span class="uid" style="display:none">' . $dateObject->format( 'YmdHis' ) . '</span>';
				$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
				
				$out .= str_ireplace( $search , $replace , $atts['format'] );
				
			$out .= '</span>' . "\n";
		}
		
		$out .= '</span>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry's birthday in a HTML string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	format (string) The tokens to use to display the date block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%date%
	 * 			%separator%
	 * 	name_format (string) Tokens for the parts of the name. See cnOutput::getNameBlock
	 * 	date_format (string) See http://php.net/manual/en/function.date.php
	 * 	separator (string) The separator to use between the label and date.
	 * 	before (string) HTML to output before the dates.
	 * 	after (string) HTML to after before the dates.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * @access public
	 * @since 0.7.3
	 * @version 2.0
	 * @param string [optional] $format deprecated since 0.7.3
	 * @param (array) [optional] $atts
	 * @return string
	 */
	public function getBirthdayBlock( $format = NULL, $atts = array() )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['format'] = '%label%%separator% %date%';
		$defaults['name_format'] = '%prefix% %first% %middle% %last% %suffix%';
		
		// The $format option has been deprecated since 0.7.3. If it has been supplied override the $defaults['date_format] value.
		$defaults['date_format'] = empty($format) ? 'F jS' : $format;
		
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
				
		$atts = $this->validate->attributesArray($defaults, $atts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$search = array('%label%' , '%date%' , '%separator%');
		$replace = array();
		
		if ( ! $this->getBirthday() ) return '';
		
		/*
		 * NOTE: The vevent span is for hCalendar compatibility.
		 * NOTE: The second birthday span [hidden] is for hCard compatibility.
		 * NOTE: The third span series [hidden] is for hCalendar compatibility.
		 */
		$out .= '<div class="vevent"><span class="birthday">';
			
			$replace[] = '<span class="date-name">' . __('Birthday', 'connections') . '</span>';
			$replace[] = '<abbr class="dtstart" title="' . $this->getBirthday('Ymd') .'">' . $this->getBirthday( $atts['date_format'] ) . '</abbr>';
			$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
			
			$out .= str_ireplace( $search , $replace , $atts['format'] );
			
			$out .= '<span class="bday" style="display:none">' . $this->getBirthday('Y-m-d') . '</span>';
			$out .= '<span class="summary" style="display:none">' . __('Birthday', 'connections') . ' - ' . $this->getName( array( 'format' => $atts['name_format'] ) ) . '</span><span class="uid" style="display:none">' . $this->getBirthday('YmdHis') . '</span>';
		
		$out .= '</div>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or return the entry's anniversary in a HTML string.
	 * 
	 * Accepted options for the $atts property are:
	 * 	format (string) The tokens to use to display the date block parts.
	 * 		Permitted Tokens:
	 * 			%label%
	 * 			%date%
	 * 			%separator%
	 * 	name_format (string) Tokens for the parts of the name. See cnOutput::getNameBlock
	 * 	date_format (string) See http://php.net/manual/en/function.date.php
	 * 	separator (string) The separator to use between the label and date.
	 * 	before (string) HTML to output before the dates.
	 * 	after (string) HTML to after before the dates.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * @access public
	 * @since 0.7.3
	 * @version 2.0
	 * @param string [optional] $format deprecated since 0.7.3
	 * @param (array) [optional] $atts
	 * @return string
	 */
	public function getAnniversaryBlock( $format = NULL, $atts = array() )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults['format'] = '%label%%separator% %date%';
		$defaults['name_format'] = '%prefix% %first% %middle% %last% %suffix%';
		
		// The $format option has been deprecated since 0.7.3. If it has been supplied override the $defaults['date_format] value.
		$defaults['date_format'] = empty($format) ? 'F jS' : $format;
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;
				
		$atts = $this->validate->attributesArray($defaults, $atts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */
		
		$out = '';
		$search = array('%label%' , '%date%' , '%separator%');
		$replace = array();
		
		if ( ! $this->getAnniversary() ) return '';
		
		/*
		 * NOTE: The vevent span is for hCalendar compatibility.
		 * NOTE: The second birthday span [hidden] is for hCard compatibility.
		 * NOTE: The third span series [hidden] is for hCalendar compatibility.
		 */
		$out .= '<div class="vevent"><span class="anniversary">';
			
			$replace[] = '<span class="date-name">' . __('Anniversary', 'connections') . '</span>';
			$replace[] = '<abbr class="dtstart" title="' . $this->getAnniversary('Ymd') .'">' . $this->getAnniversary( $atts['date_format'] ) . '</abbr>';
			$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';
		
			$out .= str_ireplace( $search , $replace , $atts['format'] );
			
			$out .= '<span class="bday" style="display:none">' . $this->getAnniversary('Y-m-d') . '</span>';
			$out .= '<span class="summary" style="display:none">' . __('Anniversary', 'connections') . ' - ' . $this->getName( array( 'format' => $atts['name_format'] ) ) . '</span><span class="uid" style="display:none">' . $this->getAnniversary('YmdHis') . '</span>';
		
		$out .= '</div>';
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
	
	/**
	 * Echo or returns the entry Notes.
	 * 
	 * Registers the global $wp_embed because the run_shortcode method needs 
	 * to run before the do_shortcode function for the [embed] shortcode to fire
	 * 
	 * @TODO Add support for the $atts array.
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array
	 * @return string
	 */
	public function getNotesBlock( $atts = array() )
	{
		global $wp_embed;
		$notes = $wp_embed->run_shortcode( $this->getNotes() );
		
		return "\n" . '<div class="note">' . do_shortcode( $notes ) . '</div>' . "\n";
	}
	
	/**
	 * Echo or returns the entry Bio.
	 * 
	 * Registers the global $wp_embed because the run_shortcode method needs 
	 * to run before the do_shortcode function for the [embed] shortcode to fire
	 * 
	 * @TODO Add support for the $atts array.
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array
	 * @return string
	 */
	public function getBioBlock( $atts = array() )
	{
		global $wp_embed;
		$bio = $wp_embed->run_shortcode( $this->getBio() );
		
		return "\n" . '<div class="bio">' . do_shortcode( $bio ) . '</div>' . "\n";
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
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array $atts [optional]
	 * @return string
	 */
	public function getCategoryBlock( $atts = array() )
	{
		$defaults = array(
			'list' => 'unordered',
			'separator' => NULL,
			'before' => NULL,
			'after' => NULL,
			'label' => 'Categories: ',
			'parents' => FALSE,
			'return' => FALSE
		);
		
		$defaults = apply_filters( 'cn_output_default_atts_category' , $defaults );
		
		$atts = $this->validate->attributesArray($defaults, $atts);
		
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
	 * @access public
	 * @since unknown
	 * @version 1.0
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
	
	/**
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @return string
	 */
	public function getRevisionDateBlock()
	{
		return '<span class="rev">' . date('Y-m-d', strtotime($this->getUnixTimeStamp())) . 'T' . date('H:i:s', strtotime($this->getUnixTimeStamp())) . 'Z' . '</span>' . "\n";
	}
	
	/**
	 * @access private
	 * @since unknown
	 * @version 1.0
	 * @return string
	 */
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
	
	/**
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @deprecated
	 * @return string
	 */
	public function returnToTopAnchor()
	{
		global $connections;
			
		$connections->template->returnToTop();
	}
	
	/**
	 * Outputs the vCard download permalink.
	 * 
	 * Accepted attributes for the $atts array are:
	 * 	class (string) The link class attribute.
	 * 	text (string) The acnhor text.
	 * 	title (string) The link title attribute.
	 * 	format (string) The tokens to use to display the vcard link block parts.
	 * 		Permitted Tokens:
	 * 			%text%
	 * 			%icon%
	 * 	follow (bool) Add add the rel="nofollow" attribute if set to FALSE
	 * 	size (int) The icon size. Valid values are: 16, 24, 32, 48
	 * 	slug (string) The entry's slug ID.
	 * 	before (string) HTML to output before the email addresses.
	 * 	after (string) HTML to after before the email addresses.
	 * 	return (bool) Return string if set to TRUE instead of echo string.
	 * 
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public function vcard( $atts = array() )
	{
		global $wp_rewrite, $connections;
		
		$base = get_option('connections_permalink');
		$name = $base['name_base'];
		$homeID = $connections->settings->get('connections', 'connections_home_page', 'page_id'); // Get the directory home page ID.
		$piece = array();
		$id = FALSE;
		$token = FALSE;
		$iconSizes = array(16, 24, 32, 48);
		$search = array('%text%' , '%icon%');
		
		// These are values will need to be added to the query string in order to download unlisted entries from the admin.
		if ( $this->getVisibility() === 'unlisted' )
		{
			$id = $this->getId();
			$token = wp_create_nonce('download_vcard_' . $this->getId() );
		}
		
		$defaults = array(
			'class' => '',
			'text' => __('Add to Address Book.', 'connections'),
			'title' => __('Download vCard', 'connections'),
			'format' => '%text%',
			'size' => 24,
			'follow' => FALSE,
			'slug' => '',
			'before' => '',
			'after' => '',
			'return' => FALSE
		);
		
		$atts = wp_parse_args( $atts , $defaults );
		
		/*
		 * Ensure the supplied size is valid, if not reset to the default value.
		 */
		( in_array($atts['size'], $iconSizes) ) ? $iconSize = $atts['size'] : $iconSize = 32;
		
		// Create the permalink base based on context where the entry is being displayed.
		if ( in_the_loop() && is_page() )
		{
			$permalink = trailingslashit ( get_permalink() );
		} else {
			$permalink = trailingslashit ( get_permalink($homeID) );
		}
		
		if ( ! empty( $atts['class'] ) ) $piece[] = 'class="' . $atts['class'] .'"';
		if ( ! empty( $atts['slug'] ) ) $piece[] = 'id="' . $atts['slug'] .'"';
		if ( ! empty( $atts['title'] ) ) $piece[] = 'title="' . $atts['title'] .'"';
		if ( ! empty( $atts['target'] ) ) $piece[] = 'target="' . $atts['target'] .'"';
		if ( ! $atts['follow'] ) $piece[] = 'rel="nofollow"';
		
		if ( $wp_rewrite->using_permalinks() )
		{
			
			$piece[] = 'href="' . add_query_arg( array( 'cn-id' => $id , 'cn-token' => $token ) , $permalink . $name . '/' .$this->getSlug() . '/vcard/' ) . '"';
		}
		else
		{
			$piece[] = 'href="' . add_query_arg( array( 'cn-entry-slug' => $this->getSlug() , 'cn-process' => 'vcard' , 'cn-id' => $id , 'cn-token' => $token ) , $permalink ) . '"';
		}
		
		$out = '<span class="vcard-block">';
		
		$replace[] = '<a ' . implode(' ', $piece) . '>' . $atts['text'] . '</a>';
		
		$replace[] = '<a ' . implode(' ', $piece) . '><image src="' . CN_URL . 'images/icons/vcard/vcard_' . $iconSize . '.png" height="' . $iconSize . 'px" width="' . $iconSize . 'px"/></a>';
		
		
		$out .= str_ireplace( $search , $replace , $atts['format'] );
		
		$out .= '</span>';
		
		
		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}
}

?>