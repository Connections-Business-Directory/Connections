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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnEntry_vCard
 */
class cnEntry_vCard extends cnEntry_HTML {

	/**
	 * @var File_IMC_Build_Vcard
	 */
	private $vCard;

	/**
	 * Use the Pear File_IMC to build a vCard.
	 *
	 * @link https://pear.php.net/manual/en/package.fileformats.contact-vcard-build.php
	 * @link https://github.com/pear/File_IMC
	 *
	 * Useful references:
	 * @link https://tools.ietf.org/html/rfc6350
	 * @link https://en.wikipedia.org/wiki/VCard
	 * @link https://github.com/jeroendesloovere/vcard
	 * @link https://github.com/evought/VCard-Tools
	 */
	private function setvCardData() {

		$this->vCard = File_IMC::build('vCard' );

		// Set the structured representation of the name. REQUIRED.
		$this->vCard->set(
			'N',
			array(
				'family-name'      => $this->prepare( $this->getLastName( 'display' ) ),
				'given-name'       => $this->prepare( $this->getFirstName( 'display' ) ),
				'additional-name'  => $this->prepare( $this->getMiddleName( 'display' ) ),
				'honorific-prefix' => $this->prepare( $this->getHonorificPrefix( 'display' ) ),
				'honorific-suffix' => $this->prepare( $this->getHonorificSuffix( 'display' ) ),
			)
		)->addParam( 'CHARSET', 'UTF-8' );

		// Set the formatted name. REQUIRED.
		$this->vCard->set( 'FN', $this->prepare( $this->getName() ) )
		            ->addParam( 'CHARSET', 'UTF-8' );

		// Set the job title.
		$this->vCard->set( 'TITLE', $this->prepare( $this->getTitle() ) )
		            ->addParam( 'CHARSET', 'UTF-8' );

		// Set the organization and unit.
		$this->vCard->set(
			'ORG',
			array(
				'organization-name' => $this->prepare( $this->getOrganization( 'display' ) ),
				'organization-unit' => $this->prepare( $this->getDepartment( 'display' ) ),
			)
		)->addParam( 'CHARSET', 'UTF-8' );

		// Set the notes.
		if ( 0 < strlen( $notes = $this->getNotes() ) ) {

			$this->vCard->set( 'NOTE', $this->format->sanitizeString( $notes ) )
			            ->addParam( 'CHARSET', 'UTF-8' );
		}

		$this->setvCardAddresses();
		$this->setvCardGEO();
		$this->setvCardPhoneNumbers();
		$this->setvCardEmailAddresses();
		$this->setvCardWebAddresses();
		$this->setvCardIMIDs();
		$this->setvCardSocialMedia();
		$this->setvCardDates();
		$this->setvCardImages();
		$this->setvCardMeta();
	}

	/**
	 * @param $string
	 *
	 * @return string
	 */
	protected function prepare( $string ) {

		return html_entity_decode( $string, ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * Get the timezone UTC Offset calculated from the WP `gmt_offset` value.
	 *
	 * @return string
	 */
	protected function getUTCOffset() {

		return cnDate::getWPUTCOffset( 'O' );
	}

	/**
	 * Get image type from integer returned by getimagesize.
	 *
	 * @param $int
	 *
	 * @return string
	 */
	protected function getImageType( $int ) {

		$mime  = image_type_to_mime_type( $int );
		$parts = explode( '/', $mime );

		return strtoupper( array_pop( $parts ) );
	}

	/**
	 * Returns a group ID.
	 *
	 * Set $new to any non-null value to increase the incrementer.
	 *
	 * @param null $new
	 *
	 * @return string
	 */
	protected function getGroupName( $new = NULL ) {

		static $i = 0;

		if ( ! is_null( $new ) ) $i++;

		return "item$i";
	}

	/**
	 * Add the latitude and longitude of the first address to the GEO property
	 *
	 * @return void
	 */
	private function setvCardGEO() {

		$preferred = $this->addresses->getPreferred();
		$first     = $this->addresses->getCollection()->first();

		if ( ! is_null( $preferred ) ) {

			$this->vCard->set(
				'GEO',
				array(
					'latitude'  => $preferred->getLatitude(),
					'longitude' => $preferred->getLongitude()
				)
			);

		} elseif ( ! is_null( $first ) ) {

			$this->vCard->set(
				'GEO',
				array(
					'latitude'  => $first->getLatitude(),
					'longitude' => $first->getLongitude()
				)
			);
		}
	}

	/**
	 * @return void
	 */
	private function setvCardAddresses() {

		if ( $data = $this->getAddresses() ) {

			foreach ( $data as $address ) {

				// Add a new ADR property to the vCard.
				$this->vCard->set(
					'ADR',
					array(
						'street-address'   => $this->prepare( $address->line_1 ),
						'extended-address' => $this->prepare( $address->line_2 ),
						'locality'         => $this->prepare( $address->city ),
						'region'           => $this->prepare( $address->state ),
						'postal-code'      => $this->prepare( $address->zipcode ),
						'country-name'     => $this->prepare( $address->country ),
					),
					'new'
				)->addParam( 'CHARSET', 'UTF-8' );

				// Add the type to the ADR property.
				$this->vCard->addParam( 'TYPE', strtoupper( $address->type ) );

				// If the address is preferred add the flag to the property.
				if ( $address->preferred ) {

					$this->vCard->addParam( 'TYPE', 'PREF' );
				}
			}
		}
	}

	private function setvCardPhoneNumbers() {

		// Map the core Connection phone type to supported vCard types.
		$index = array(
			'home'      => array( 'HOME', 'VOICE' ),
			'homephone' => array( 'HOME', 'VOICE' ),
			'homefax'   => array( 'HOME', 'FAX' ),
			'cell'      => array( 'CELL', 'VOICE', 'TEXT' ),
			'cellphone' => array( 'CELL', 'VOICE', 'TEXT' ),
			'work'      => array( 'WORK', 'VOICE' ),
			'workphone' => array( 'WORK', 'VOICE' ),
			'workfax'   => array( 'WORK', 'FAX' ),
			'fax'       => array( 'WORK', 'VOICE' ),
		);

		if ( $data = $this->getPhoneNumbers() ) {

			foreach ( $data as $phone ) {

				// Add a new TEL property to the vCard.
				$this->vCard->set( 'TEL', $phone->number, 'new' )
				            ->addParam( 'CHARSET', 'UTF-8' );

				// Add the TEL types from the index.
				if ( array_key_exists( $phone->type, $index ) ) {

					foreach ( $index[ $phone->type ] as $type ) {

						$this->vCard->addParam( 'TYPE', $type );
					}

				// If a TEL type is not in the index, just set the type to VOICE
				} else {

					$this->vCard->addParam( 'TYPE', 'VOICE' );
				}

				// If the phone is preferred add the flag to the property.
				if ( $phone->preferred ) {

					$this->vCard->addParam( 'TYPE', 'PREF' );
				}
			}
		}
	}

	private function setvCardEmailAddresses() {

		// Map the core Connection email type to supported vCard types.
		$index = array(
			'personal'  => array( 'HOME', 'INTERNET' ),
			'work'      => array( 'WORK', 'INTERNET' ),
		);

		if ( $data = $this->getEmailAddresses() ) {

			foreach ( $data as $email ) {

				// Add a new EMAIL property to the vCard.
				$this->vCard->set( 'EMAIL', $email->address, 'new' )
				            ->addParam( 'CHARSET', 'UTF-8' );

				// Add the EMAIL types from the index.
				if ( array_key_exists( $email->type, $index ) ) {

					foreach ( $index[ $email->type ] as $type ) {

						$this->vCard->addParam( 'TYPE', $type );
					}

				// If a EMAIL type is not in the index, just set the type to INTERNET
				} else {

					$this->vCard->addParam( 'TYPE', 'INTERNET' );
				}

				// If the email is preferred add the flag to the property.
				if ( $email->preferred ) {

					$this->vCard->addParam( 'TYPE', 'PREF' );
				}
			}

		}
	}

	private function setvCardWebAddresses() {

		// Map the core Connection link type to supported vCard types.
		$index = array(
			'blog'    => array( 'HOME' ),
			'website' => array( 'WORK' ),
		);

		if ( $data = $this->getLinks() ) {

			foreach ( $data as $link ) {

				// Add a new URL property to the vCard.
				$this->vCard->set( 'URL', esc_url( $link->url ), 'new' )
				            ->addParam( 'CHARSET', 'UTF-8' );

				// Add the URL types from the index.
				if ( array_key_exists( $link->type, $index ) ) {

					foreach ( $index[ $link->type ] as $type ) {

						$this->vCard->addParam( 'TYPE', $type );
					}

				// If a URL type is not in the index, just set the type to HOME
				} else {

					$this->vCard->addParam( 'TYPE', 'HOME' );
				}

				// If the email is preferred add the flag to the property.
				if ( $link->preferred ) {

					$this->vCard->addParam( 'TYPE', 'PREF' );
				}
			}
		}
	}

	private function setvCardIMIDs() {

		// IM protocol index. key == Connection IM type => value == protocol
		$index = array(
			'aim'       => 'aim',
			'icq'       => 'icq',
			'jabber'    => 'xmpp',
			'messenger' => 'msnim',
			'skype'     => 'skype',
			'yahoo'     => 'ymsgr',
		);

		// Index of known vCard IMPP extensions. key == Connections IM Type => value == vCard extension.
		$extension = array(
			'aim'       => array( 'X-AIM' ),
			'icq'       => array( 'X-ICQ' ),
			'jabber'    => array( 'X-GOOGLE-TALK', 'X-GTALK', 'X-JABBER' ),
			'messenger' => array( 'X-MSN' ),
			'skype'     => array( 'X-SKYPE', 'X-SKYPE-USERNAME' ),
			'yahoo'     => array( 'X-YAHOO' ),
		);

		if ( $data = $this->getIm() ) {

			foreach ( $data as $im ) {

				// Add the IMPP types from the index.
				if ( array_key_exists( $im->type, $index ) ) {

					// Add a new IMPP property to the vCard.
					$this->vCard->set( 'IMPP', $index[ $im->type ] . ':' . $im->id, 'new' )
					            ->addParam( 'CHARSET', 'UTF-8' );

					$this->vCard->addParam( 'TYPE', 'PERSONAL' );

					// If the IM is preferred add the flag to the property.
					if ( $im->preferred ) {

						$this->vCard->addParam( 'TYPE', 'PREF' );
					}
				}

				// Add the known vCard IMPP extensions from the index.
				if ( array_key_exists( $im->type, $extension ) ) {

					foreach ( $extension[ $im->type ] as $x ) {

						// Add a new IMPP property to the vCard.
						$this->vCard->set( $x, $im->id, 'new' )
						            ->addParam( 'CHARSET', 'UTF-8' );

						$this->vCard->addParam( 'TYPE', 'PERSONAL' );

						// If the IM is preferred add the flag to the property.
						if ( $im->preferred ) {

							$this->vCard->addParam( 'TYPE', 'PREF' );
						}
					}
				}
			}
		}
	}

	/**
	 * @link https://tools.ietf.org/html/draft-ietf-vcarddav-social-networks-00
	 * @link https://alessandrorossini.org/the-sad-story-of-the-vcard-format-and-its-lack-of-interoperability/
	 * @link https://www.quora.com/Does-anyone-here-know-how-the-best-way-to-combine-vcard-contact-information-and-social-site-hyperlinks-in-ONE-QR-code
	 */
	private function setvCardSocialMedia() {

		if ( $data = $this->getSocialMedia() ) {

			foreach ( $data as $network ) {

				$this->vCard->set( 'URL', $network->url, 'new' )->setGroup( $this->getGroupName( 'new' ) );
				$this->vCard->set( 'X-ABLabel', $network->name, 'new' )->setGroup( $this->getGroupName() );
			}
		}
	}

	private function setvCardDates() {

		$anniversary = NULL;
		$birthday    = NULL;

		$day = $this->getDates( array( 'type' => 'anniversary' ) );

		if ( ! empty( $day ) ) {

			$anniversary = date_i18n( 'Y-m-d', strtotime( $day[0]->date ), TRUE );
		}

		$day = $this->getDates( array( 'type' => 'birthday' ) );

		if ( ! empty( $day ) ) {

			$birthday = date_i18n( 'Y-m-d', strtotime( $day[0]->date ), TRUE );
		}

		// Set the anniversary.
		if ( ! is_null( $anniversary ) ) {

			$this->vCard->set( 'X-ANNIVERSARY', $anniversary, 'new' );

			/**
			 * Exports from Apple's AddressBook have X-ABLabel field bracketed by  _$!< >!$_
			 * If the label is one of the predefined ones (e.g. Child, Spouse, Manager, Partner, etc),
			 * but *not* bracketed if the label is a custom label.
			 *
			 * Predefined X-ABLabel values such as "_$!<Spouse>!$_" are localized by AddressBook.app.
			 */
			$this->vCard->set( 'X-ABDATE', $anniversary, 'new' )->setGroup( $this->getGroupName( 'new' ) );
			$this->vCard->set( 'X-ABLabel', '_$!<Anniversary>!$_', 'new' )->setGroup( $this->getGroupName() );
		}

		// Set the birthday.
		if ( ! is_null( $birthday ) ) {

			$this->vCard->set( 'BDAY', $birthday, 'new' );
		}
	}

	private function setvCardImages() {

		// An image or graphic of the logo of the organization that is associated with the individual to which the vCard belongs.
		$logo = $this->getImageMeta( array( 'type' => 'logo' ) );

		if ( ! is_wp_error( $logo ) ) {
			$this->vCard->set( 'LOGO', $logo['path'] )
			            ->addParam( 'TYPE', $this->getImageType( $logo['type'] ) );
		}

		// An image or photograph of the individual associated with the vCard.
		$photo = $this->getImageMeta( array( 'type' => 'photo' ) );

		if ( ! is_wp_error( $photo ) ) {
			$this->vCard->set( 'PHOTO', $photo['path'] )
			            ->addParam( 'TYPE', $this->getImageType( $photo['type'] ) );

			// Support MS Outlook
			$this->vCard->set( 'X-MS-CARDPICTURE', base64_encode( file_get_contents( $photo['path'] ) ) )
			            ->addParam( 'ENCODING', 'B' );
			$this->vCard->addParam( 'TYPE', $this->getImageType( $photo['type'] ) );
		}
	}

	private function setvCardMeta() {

		// Set the identifier for the product that created the vCard object.
		$this->vCard->set( 'PRODID', '-//Connections Business Directory for WordPress//Version 2.0//EN' );

		// Set the sensitivity of the information in the vCard.
		$this->vCard->set( 'CLASS', 'PUBLIC' );

		// Set the timestamp (ISO 8601 formatted UTC date/time) for the last time the vCard was updated.
		//$this->vCard->set( 'REV', date( 'Ymd\THis\Z', $this->getUnixTimeStamp() ) );

		// Set the time zone of the vCard.
		$this->vCard->set( 'TZ', $this->getUTCOffset() );

		// Set the sort string to the last name.
		$this->vCard->set( 'SORT-STRING', $this->prepare( $this->getName( array( 'format' => '%last%' ) ) ) );

		// Set the categories.
		if ( $categories = $this->getCategory() ) {

			$categories = wp_list_pluck( $categories, 'name' );

			$this->vCard->set( 'CATEGORIES', implode( ',', $categories ) )
			            ->addParam( 'CHARSET', 'UTF-8' );
		}
	}

	/**
	 * @return File_IMC_Build_Vcard
	 */
	public function data() {

		$this->setvCardData();

		return $this->vCard;
	}

	public static function download() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$process = cnQuery::getVar( 'cn-process' );
		$token   = cnQuery::getVar( 'cn-token' );
		$id      = absint( cnQuery::getVar( 'cn-id' ) );

		if ( 'vcard' === $process ) {

			$slug = cnQuery::getVar( 'cn-entry-slug' ); //var_dump($slug);

			/*
			 * If the token and id values were set, the link was likely from the admin.
			 * Check for those values and validate the token. The primary reason for this
			 * to be able to download vCards of entries that are set to "Unlisted".
			 */
			if ( ! empty( $id ) && ! empty( $token ) ) {

				if ( ! wp_verify_nonce( $token, 'download_vcard_' . $id ) ) {

					wp_die( 'Invalid vCard Token' );
				}

				$entry = $instance->retrieve->entry( $id );

				// Die if no entry was found.
				if ( empty( $entry ) ) {

					wp_die( __( 'vCard not available for download.', 'connections' ) );
				}

				$vCard = new cnEntry_vCard( $entry ); //var_dump($vCard);die;

			} else {

				$entry = $instance->retrieve->entries( array( 'slug' => $slug ) ); //var_dump($entry);die;

				// Die if no entry was found.
				if ( empty( $entry ) ) {

					wp_die( __( 'vCard not available for download.', 'connections' ) );
				}

				$vCard = new cnEntry_vCard( $entry[0] ); //var_dump($vCard);die;
			}

			$filename = sanitize_file_name( $vCard->getName() ); //var_dump($filename);
			$data     = $vCard->data()->fetch();

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename=' . $filename . '.vcf' );
			header( 'Content-Length: ' . strlen( $data ) );
			header( 'Pragma: public' );
			header( "Pragma: no-cache" );
			header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
			header( 'Cache-Control: private' );

			echo $data;
			exit;
		}
	}
}
