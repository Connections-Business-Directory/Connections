<?php

/**
 * Build vCard compliant output from cnEntry.
 *
 * @package     Connections
 * @subpackage  vCard
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnvCard extends cnOutput
{
	private $data;
	private $card;

	private function setvCardData()
	{
		$imageName = $this->getImageNameCard();
		$logoName = $this->getLogoName();

		if ( !empty($imageName) )
		{
			$imagePath = CN_IMAGE_PATH . $imageName;
		}
		else
		{
			$imagePath = NULL;
		}


		if ( !empty($logoName) )
		{
			$logoPath = CN_IMAGE_PATH . $logoName;
		}
		else
		{
			$logoPath = NULL;
		}

		$this->data = array(
							'class'=>null,
							'display_name'=>$this->getFullFirstLastName(),
							'first_name'=>$this->getFirstName(),
							'last_name'=>$this->getLastName(),
							'additional_name'=>$this->getMiddleName(),
							'name_prefix'=>$this->getHonorificPrefix(),
							'name_suffix'=>$this->getHonorificSuffix(),
							'nickname'=>null,
							'title'=>$this->getTitle(),
							'role'=>null,
							'department'=>$this->getDepartment(),
							'company'=>$this->getOrganization(),
							'work_po_box'=>null,
							'work_extended_address'=>null,
							'work_address'=>null,
							'work_city'=>null,
							'work_state'=>null,
							'work_postal_code'=>null,
							'work_country'=>null,
							'home_po_box'=>null,
							'home_extended_address'=>null,
							'home_address'=>null,
							'home_city'=>null,
							'home_state'=>null,
							'home_postal_code'=>null,
							'home_country'=>null,
							'other_po_box'=>null,
							'other_extended_address'=>null,
							'other_address'=>null,
							'other_city'=>null,
							'other_state'=>null,
							'other_postal_code'=>null,
							'other_country'=>null,
							'latitute'=>null,
							'longitude'=>null,
							'work_tel'=>null,
							'home_tel'=>null,
							'home_fax'=>null,
							'cell_tel'=>null,
							'work_fax'=>null,
							'pager_tel'=>null,
							'email1'=>null,
							'email2'=>null,
							'url'=>null,
							'aim'=>null,
							'messenger'=>null,
							'yim'=>null,
							'jabber'=>null,
							'photo'=>$imagePath,
							'logo'=>$logoPath,
							'birthday'=>$this->getBirthday('Y-m-d'),
							'anniversary'=>$this->getAnniversary('Y-m-d'),
							'spouse'=>null,
							'timezone'=>null,
							'revision_date'=>date('Y-m-d H:i:s', strtotime($this->getUnixTimeStamp())),
							'sort_string'=>null,
							'categories'=>$this->getCategory(),
							'note'=>$this->format->sanitizeString( $this->getNotes() )
							);

		$this->setvCardAddresses();
		$this->setvCardGEO();
		$this->setvCardPhoneNumbers();
		$this->setvCardEmailAddresses();
		$this->setvCardWebAddresses();
		$this->setvCardIMIDs();
		$this->buildvCard();
	}

	private function buildvCard()
	{
		if (!$this->data['class']) { $this->data['class'] = "PUBLIC"; }
		if (!$this->data['display_name'])
		{
			$this->data['display_name'] = trim($this->data['first_name']." ".$this->data['last_name']);
		}

		if (!$this->data['sort_string']) { $this->data['sort_string'] = $this->data['last_name']; }
		if (!$this->data['sort_string']) { $this->data['sort_string'] = $this->data['company']; }
		if (!$this->data['timezone']) { $this->data['timezone'] = date("O"); }
		if (!$this->data['revision_date']) { $this->data['revision_date'] = date('Y-m-d H:i:s'); }

		$this->card = "BEGIN:VCARD\r\n";
		$this->card .= "VERSION:3.0\r\n";
		$this->card .= "CLASS:".$this->data['class']."\r\n";
		$this->card .= "PRODID:-//Connections - WordPress Plug-in//Version 1.0//EN\r\n";
		$this->card .= "REV:".$this->data['revision_date']."\r\n";
		$this->card .= "FN;CHARSET=utf-8:".$this->data['display_name']."\r\n";
		$this->card .= "N;CHARSET=utf-8:"
			. $this->data['last_name'].";"
			. $this->data['first_name'].";"
			. $this->data['additional_name'].";"
			. $this->data['name_prefix'].";"
			. $this->data['name_suffix']."\r\n";

		if ($this->data['nickname']) { $this->card .= "NICKNAME;CHARSET=utf-8:".$this->data['nickname']."\r\n"; }
		if ($this->data['title']) { $this->card .= "TITLE;CHARSET=utf-8:".$this->data['title']."\r\n"; }
		if ( $this->data['company'] || $this->data['department'] )
		{
			$this->card .= "ORG;CHARSET=utf-8:" . ( ( empty($this->data['company'] ) ) ? '' : $this->data['company'] ) . ';' . ( ( empty( $this->data['department'] ) ) ? '' : $this->data['department'] );
			$this->card .= "\r\n";
		}

		if ($this->data['work_po_box']
			|| $this->data['work_extended_address']
			|| $this->data['work_address']
			|| $this->data['work_city']
			|| $this->data['work_state']
			|| $this->data['work_postal_code']
			|| $this->data['work_country'])
		{
			$this->card .= "ADR;CHARSET=utf-8;TYPE=work:"
		    . $this->data['work_po_box'].";"
		    . $this->data['work_extended_address'].";"
		    . $this->data['work_address'].";"
		    . $this->data['work_city'].";"
		    . $this->data['work_state'].";"
		    . $this->data['work_postal_code'].";"
		    . $this->data['work_country']."\r\n";
		}

		if ($this->data['home_po_box']
			|| $this->data['home_extended_address']
			|| $this->data['home_address']
			|| $this->data['home_city']
			|| $this->data['home_state']
			|| $this->data['home_postal_code']
			|| $this->data['home_country'])
		{
			$this->card .= "ADR;CHARSET=utf-8;TYPE=home:"
		    . $this->data['home_po_box'].";"
		    . $this->data['home_extended_address'].";"
		    . $this->data['home_address'].";"
		    . $this->data['home_city'].";"
		    . $this->data['home_state'].";"
		    . $this->data['home_postal_code'].";"
		    . $this->data['home_country']."\r\n";
		}

		if ($this->data['other_po_box']
			|| $this->data['other_extended_address']
			|| $this->data['other_address']
			|| $this->data['other_city']
			|| $this->data['other_state']
			|| $this->data['other_postal_code']
			|| $this->data['other_country'])
		{
			$this->card .= "ADR;CHARSET=utf-8;TYPE=other:"
		    . $this->data['other_po_box'].";"
		    . $this->data['other_extended_address'].";"
		    . $this->data['other_address'].";"
		    . $this->data['other_city'].";"
		    . $this->data['other_state'].";"
		    . $this->data['other_postal_code'].";"
		    . $this->data['other_country']."\r\n";
		}

		if ( ( isset( $this->data['latitude'] ) && ! empty( $this->data['latitude'] ) ) && ( isset( $this->data['longitude'] ) && ! empty( $this->data['longitude'] ) ) )
		{
			$this->card .= "GEO:".$this->data['latitude'].";".$this->data['longitude']."\r\n";;
		}

		if ($this->data['email1']) { $this->card .= "EMAIL;CHARSET=utf-8;TYPE=internet:".$this->data['email1']."\r\n"; }
		if ($this->data['email2']) { $this->card .= "EMAIL;CHARSET=utf-8;TYPE=internet:".$this->data['email2']."\r\n"; }
		if ($this->data['work_tel']) { $this->card .= "TEL;CHARSET=utf-8;TYPE=work,voice:".$this->data['work_tel']."\r\n"; }
		if ($this->data['home_tel']) { $this->card .= "TEL;CHARSET=utf-8;TYPE=home,voice:".$this->data['home_tel']."\r\n"; }
		if ($this->data['cell_tel']) { $this->card .= "TEL;CHARSET=utf-8;TYPE=cell,voice:".$this->data['cell_tel']."\r\n"; }
		if ($this->data['work_fax']) { $this->card .= "TEL;CHARSET=utf-8;TYPE=work,fax:".$this->data['work_fax']."\r\n"; }
		if ($this->data['home_fax']) { $this->card .= "TEL;CHARSET=utf-8;TYPE=home,fax:".$this->data['home_fax']."\r\n"; }
		if ($this->data['pager_tel']) { $this->card .= "TEL;CHARSET=utf-8;TYPE=work,pager:".$this->data['pager_tel']."\r\n"; }
		if ($this->data['url']) { $this->card .= "URL;CHARSET=utf-8:".$this->data['url']."\r\n"; }

		// http://tools.ietf.org/html/rfc4770
		if ($this->data['aim']) { $this->card .= "IMPP;CHARSET=utf-8;TYPE=personal:aim:".$this->data['aim']."\r\n"; }
		if ($this->data['aim']) { $this->card .= "X-AIM;CHARSET=utf-8:".$this->data['aim']."\r\n"; }
		if ($this->data['messenger']) { $this->card .= "IMPP;CHARSET=utf-8;TYPE=personal:msn:".$this->data['messenger']."\r\n"; }
		if ($this->data['messenger']) { $this->card .= "X-MSN;CHARSET=utf-8:".$this->data['messenger']."\r\n"; }
		if ($this->data['yim']) { $this->card .= "IMPP;CHARSET=utf-8;TYPE=personal:ymsgr:".$this->data['yim']."\r\n"; }
		if ($this->data['yim']) { $this->card .= "X-YAHOO;CHARSET=utf-8:".$this->data['yim']."\r\n"; }
		if ($this->data['jabber']) { $this->card .= "IMPP;CHARSET=utf-8;TYPE=personal:xmpp:".$this->data['jabber']."\r\n"; }
		if ($this->data['jabber']) { $this->card .= "X-JABBER;CHARSET=utf-8:".$this->data['jabber']."\r\n"; }

		// @TODO: Add social media IDs here.
		// http://tools.ietf.org/html/draft-george-vcarddav-vcard-extension-01

		if ($this->data['birthday']) { $this->card .= "BDAY:".$this->data['birthday']."\r\n"; }
		if ($this->data['anniversary']) { $this->card .= "X-ANNIVERSARY:".$this->data['anniversary']."\r\n"; }
		if ($this->data['spouse']) { $this->card .= "X-SPOUSE;CHARSET=utf-8:".$this->data['spouse']."\r\n"; }
		if ($this->data['role']) { $this->card .= "ROLE;CHARSET=utf-8:".$this->data['role']."\r\n"; }
		if ($this->data['note']) { $this->card .= "NOTE;CHARSET=utf-8:".$this->data['note']."\r\n"; }

		// @Author: http://www.hotscripts.com/forums/php/47729-solved-how-create-vcard-photo.html
		if ($this->data['photo'])
		{
			$imageTypes = array
								(
								    IMAGETYPE_JPEG => 'JPEG',
								    IMAGETYPE_GIF  => 'GIF',
								    IMAGETYPE_PNG  => 'PNG',
								    IMAGETYPE_BMP  => 'BMP'
								);

			if ( $imageInfo = getimagesize( $this->data['photo'] ) AND isset($imageTypes[$imageInfo[2]]) )
			{
			    $photo = base64_encode( file_get_contents($this->data['photo']) );
			    $type  = $imageTypes[$imageInfo[2]];
			}

			//$this->card .= sprintf("PHOTO;ENCODING=BASE64;TYPE=%s:%s\r\n", $type, $photo);
			$this->card .= sprintf("PHOTO;ENCODING=BASE64;TYPE=%s:", $type);

			$i = 0;
			$strphoto = sprintf($photo);

			while($i < strlen($strphoto))
			{
				if( $i%75 == 0 )
				{
			  		$this->card .= "\r\n " . $strphoto[$i];
				}
				else
				{
				  	$this->card .= $strphoto[$i];
				}

				$i++;
			}

			$this->card .= "\r\n";
			//$this->card .= "PHOTO;VALUE=uri:".$this->data['photo']."\r\n";
		}

		if ($this->data['logo'])
		{
			$imageTypes = array
								(
								    IMAGETYPE_JPEG => 'JPEG',
								    IMAGETYPE_GIF  => 'GIF',
								    IMAGETYPE_PNG  => 'PNG',
								    IMAGETYPE_BMP  => 'BMP'
								);

			if ( $imageInfo = getimagesize( $this->data['logo'] ) AND isset($imageTypes[$imageInfo[2]]) )
			{
			    $photo = base64_encode( file_get_contents($this->data['logo']) );
			    $type  = $imageTypes[$imageInfo[2]];
			}

			$this->card .= sprintf("LOGO;ENCODING=BASE64;TYPE=%s:", $type);

			$i = 0;
			$strphoto = sprintf($photo);

			while($i < strlen($strphoto))
			{
				if( $i%75 == 0 )
				{
			  		$this->card .= "\r\n " . $strphoto[$i];
				}
				else
				{
				  	$this->card .= $strphoto[$i];
				}

				$i++;
			}

			$this->card .= "\r\n";
		}

		if ($this->data['categories'])
		{
			$count = count($this->data['categories']);
			$i = 0;

			$this->card .= "CATEGORIES;CHARSET=utf-8:";

			foreach ($this->data['categories'] as $category)
			{
				$this->card .= $category->name;

				$i++;
				if ( $count > $i ) $this->card .= ',';
			}

			$this->card .= "\r\n";

			unset($i);
		}

		$this->card .= "TZ:".$this->data['timezone']."\r\n";
		$this->card .= "END:VCARD\r\n";
	}

	/**
	 * Add the latitude and longitude of the first address to the GEO property
	 * @TODO Should use the preferred address if set and only then use the first geo on the first address.
	 * @return void
	 */
	private function setvCardGEO()
	{
		if ( $this->getAddresses() )
		{
			$address = $this->getAddresses();

			$this->data['latitude'] = $address[0]->latitude;
			$this->data['longitude'] = $address[0]->longitude;
		}
	}

	/**
	 * @TODO When multple addresses of the same type is set for an entry, the last is used because it overwrites
	 * the previous. This should use the preferred address if set and then use the intial address of the specific type.
	 * @return void
	 */
	private function setvCardAddresses()
	{
		if ($this->getAddresses())
		{
			foreach ($this->getAddresses() as $address)
			{
				switch ($address->type)
				{
					case 'home':
						$this->data['home_address'] = $address->line_one;
						$this->data['home_extended_address'] = $address->line_two;
						$this->data['home_city'] = $address->city;
						$this->data['home_state'] = $address->state;
						$this->data['home_postal_code'] = $address->zipcode;
						$this->data['home_country'] = $address->country;
					break;

					case 'work':
						$this->data['work_address'] = $address->line_one;
						$this->data['work_extended_address'] = $address->line_two;
						$this->data['work_city'] = $address->city;
						$this->data['work_state'] = $address->state;
						$this->data['work_postal_code'] = $address->zipcode;
						$this->data['work_country'] = $address->country;
					break;

					case 'school':
						$this->data['other_address'] = $address->line_one;
						$this->data['other_extended_address'] = $address->line_two;
						$this->data['other_city'] = $address->city;
						$this->data['other_state'] = $address->state;
						$this->data['other_postal_code'] = $address->zipcode;
						$this->data['other_country'] = $address->country;
					break;

					case 'other':
						$this->data['other_address'] = $address->line_one;
						$this->data['other_extended_address'] = $address->line_two;
						$this->data['other_city'] = $address->city;
						$this->data['other_state'] = $address->state;
						$this->data['other_postal_code'] = $address->zipcode;
						$this->data['other_country'] = $address->country;
					break;

					default:
						switch ($this->getEntryType())
						{
							case 'individual':
								if ($address->line_one != NULL) $this->data['home_address'] = $address->line_one;
								if ($address->line_two != NULL) $this->data['home_extended_address'] = $address->line_two;
								if ($address->city != NULL) $this->data['home_city'] = $address->city;
								if ($address->state != NULL) $this->data['home_state'] = $address->state;
								if ($address->zipcode != NULL) $this->data['home_postal_code'] = $address->zipcode;
								if ($address->country != NULL) $this->data['home_country'] = $address->country;
							break;

							case 'organization':
								if ($address->line_one != NULL) $this->data['work_address'] = $address->line_one;
								if ($address->line_two != NULL) $this->data['work_extended_address'] = $address->line_two;
								if ($address->city != NULL) $this->data['work_city'] = $address->city;
								if ($address->state != NULL) $this->data['work_state'] = $address->state;
								if ($address->zipcode != NULL) $this->data['work_postal_code'] = $address->zipcode;
								if ($address->country != NULL) $this->data['work_country'] = $address->country;
							break;

							default:
								if ($address->line_one != NULL) $this->data['home_address'] = $address->line_one;
								if ($address->line_two != NULL) $this->data['home_extended_address'] = $address->line_two;
								if ($address->city != NULL) $this->data['home_city'] = $address->city;
								if ($address->state != NULL) $this->data['home_state'] = $address->state;
								if ($address->zipcode != NULL) $this->data['home_postal_code'] = $address->zipcode;
								if ($address->country != NULL) $this->data['home_country'] = $address->country;
							break;
						}
					break;
				}
			}
		}
	}

	private function setvCardPhoneNumbers()
    {

		if ($this->getPhoneNumbers())
		{
			foreach ($this->getPhoneNumbers() as $phone)
			{
				switch ($phone->type)
				{
					case 'home':
						$this->data['home_tel'] = $phone->number;
					break;

					case 'homephone':
						$this->data['home_tel'] = $phone->number;
					break;

					case 'homefax':
						$this->data['home_fax'] = $phone->number;
					break;

					case 'cell':
						$this->data['cell_tel'] = $phone->number;
					break;

					case 'cellphone':
						$this->data['cell_tel'] = $phone->number;
					break;

					case 'work':
						$this->data['work_tel'] = $phone->number;
					break;

					case 'workphone':
						$this->data['work_tel'] = $phone->number;
					break;

					case 'workfax':
						$this->data['work_fax'] = $phone->number;
					break;

					case 'fax':
						$this->data['work_fax'] = $phone->number;
					break;
				}
			}
		}
    }

	private function setvCardEmailAddresses()
	{
		if ($this->getEmailAddresses())
		{
			foreach ($this->getEmailAddresses() as $emailRow)
			{
				switch ($emailRow->type)
				{
					case 'personal':
						$this->data['email1'] = $emailRow->address;
					break;

					case 'work':
						$this->data['email2'] = $emailRow->address;
					break;

					default:
						$this->data['email1'] = $emailRow->address;
					break;
				}
			}

		}
	}

	private function setvCardWebAddresses()
	{
		if ( $this->getWebsites() )
		{
			$website = $this->getWebsites();
			$this->data['url'] = $website[0]->url;

			/*foreach ($this->getWebsites() as $website)
			{
				switch ($website->type)
				{
					case 'personal':
						$this->data['url'] = $website->url;
					break;

					default:
						$this->data['url'] = $website->url;
					break;
				}

				break; // Only return the first website url.
			}*/

		}
	}

	private function setvCardIMIDs()
	{
		if ($this->getIm())
		{
			foreach ($this->getIm() as $imRow)
			{
				switch ($imRow->type)
				{
					case 'aim':
						$this->data['aim'] = $imRow->id;
					break;

					case 'yahoo':
						$this->data['yim'] = $imRow->id;
					break;

					case 'messenger':
						$this->data['messenger'] = $imRow->id;
					break;

					case 'jabber':
						$this->data['jabber'] = $imRow->id;
					break;

					default:
						switch ($imRow->name)
						{
							case 'AIM':
								$this->data['aim'] = $imRow->id;
							break;

							case 'Yahoo IM':
								$this->data['yim'] = $imRow->id;
							break;

							case 'Messenger':
								$this->data['messenger'] = $imRow->id;
							break;

							case 'Jabber / Google Talk':
								$this->data['jabber'] = $imRow->id;
							break;
						}
					break;
				}
			}

		}
	}

	public function getvCard()
	{
		$this->setvCardData();
		return $this->card;
	}

	/**
	 * @access private
	 * @since unknown
	 * @version 1.0
	 * @deprecated
	 * @param array $suppliedAtts [optional]
	 * @return string
	 */
	public function download( $suppliedAtts = array() )
	{
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaultAtts = array( 'anchorText' => 'Add to Address Book',
							  'title' => 'Download vCard',
							  'return' => FALSE
							);

		$atts = $this->validate->attributesArray($defaultAtts, $suppliedAtts);
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		extract($atts);

		$out = $this->vcard( array( 'text' => $anchorText , 'title' => $title , 'return' => TRUE ) );

		//$token = wp_create_nonce('download_vcard_' . $this->getId() );
		//$out = '<a href="' . get_site_url() . '?cntoken=' . $token . '&cnid=' . $this->getId() . '&cnvc=1" title="' . $title . '" rel="nofollow">' . $anchorText . '</a>';

		if ( $return ) return $out; else echo $out;
	}
}