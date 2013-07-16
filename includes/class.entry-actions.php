<?php

/**
 * Class for processing entry administration actions.
 *
 * @package     Connections
 * @subpackage  Admin Actions
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnEntry_Action {

	/**
	 * Add an entry.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (array)  $data The data to be used when adding an entry.
	 * @return (bool)
	 */
	public static function add( $data ) {

		return self::process( 'add', $data );
	}

	/**
	 * Update an existing entry.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (int)	$id		The entry ID.
	 * @param  (array)  $data 	The data to be used when updating an entry.
	 * @return (bool)
	 */
	public static function update( $id, $data ) {

		return self::process( 'update', $data, $id );
	}

	/**
	 * Copy an existing entry.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (int)	$id		The entry ID inwhich to duplicate.
	 * @param  (array)  $data [optional] 	The data to be used when duplicating an entry. Will be used add/replace existing data.
	 * @return (bool)
	 */
	public static function copy( $id, $data = array() ) {

		return self::process( 'add', $data, $id );
	}

	/**
	 * Add / Edit / Update / Copy an entry.
	 *
	 * @todo The image logic/processing should be abstracted out of this method into its own class.
	 *
	 * @access private
	 * @since 0.7.8
	 * @param  (string) $action Valid options are: add | update
	 * @param  (array)  $data [optional] The data to be used when adding / editing / duplicating an entry.
	 * @param  (int) $action [optional] If editing/duplicating an entry, the entry ID.
	 * @uses absint()
	 * @return (bool)
	 */
	private static function process( $action, $data = array(), $id = 0 ) {
		global $connections;
		$entry = new cnEntry();

		// The modification file date that image will be deleted. to maintain compatibility with 0.6.2.1 and older.
		$compatiblityDate = mktime( 0, 0, 0, 6, 1, 2010 );

		// If copying/editing an entry, the entry data is loaded into the class
		// properties and then properties are overwritten by the data as needed.
		if ( ! empty( $id ) ) $entry->set( absint( $id ) );

		// Set the default visibility.
		$entry->setVisibility( 'unlisted' );

		if ( isset( $data['entry_type'] ) ) $entry->setEntryType( $data['entry_type'] );
		if ( isset( $data['family_name'] ) ) $entry->setFamilyName( $data['family_name'] );
		( isset( $data['family_member'] ) ) ? $entry->setFamilyMembers( $data['family_member'] ) : $entry->setFamilyMembers( array() );
		if ( isset( $data['honorific_prefix'] ) ) $entry->setHonorificPrefix( $data['honorific_prefix'] );
		if ( isset( $data['first_name'] ) ) $entry->setFirstName( $data['first_name'] );
		if ( isset( $data['middle_name'] ) ) $entry->setMiddleName( $data['middle_name'] );
		if ( isset( $data['last_name'] ) ) $entry->setLastName( $data['last_name'] );
		if ( isset( $data['honorific_suffix'] ) ) $entry->setHonorificSuffix( $data['honorific_suffix'] );
		if ( isset( $data['title'] ) ) $entry->setTitle( $data['title'] );
		if ( isset( $data['organization'] ) ) $entry->setOrganization( $data['organization'] );
		if ( isset( $data['department'] ) ) $entry->setDepartment( $data['department'] );
		if ( isset( $data['contact_first_name'] ) ) $entry->setContactFirstName( $data['contact_first_name'] );
		if ( isset( $data['contact_last_name'] ) ) $entry->setContactLastName( $data['contact_last_name'] );
		( isset( $data['address'] ) ) ? $entry->setAddresses( $data['address'] ) : $entry->setAddresses( array() );
		( isset( $data['phone'] ) ) ? $entry->setPhoneNumbers( $data['phone'] ) : $entry->setPhoneNumbers( array() );
		( isset( $data['email'] ) ) ? $entry->setEmailAddresses( $data['email'] ) : $entry->setEmailAddresses( array() );
		( isset( $data['im'] ) ) ? $entry->setIm( $data['im'] ) : $entry->setIm( array() );
		( isset( $data['social'] ) ) ? $entry->setSocialMedia( $data['social'] ) : $entry->setSocialMedia( array() );
		//( isset($data['website']) ) ? $entry->setWebsites($data['website']) : $entry->setWebsites( array() );
		( isset( $data['link'] ) ) ? $entry->setLinks( $data['link'] ) : $entry->setLinks( array() );
		( isset( $data['date'] ) ) ? $entry->setDates( $data['date'] ) : $entry->setDates( array() );
		if ( isset( $data['birthday_day'] ) && isset( $data['birthday_month'] ) ) $entry->setBirthday( $data['birthday_day'], $data['birthday_month'] );
		if ( isset( $data['anniversary_day'] ) && isset( $data['anniversary_month'] ) ) $entry->setAnniversary( $data['anniversary_day'], $data['anniversary_month'] );
		if ( isset( $data['bio'] ) ) $entry->setBio( $data['bio'] );
		if ( isset( $data['notes'] ) ) $entry->setNotes( $data['notes'] );
		if ( isset( $data['visibility'] ) ) $entry->setVisibility( $data['visibility'] );

		( isset( $data['user'] ) ) ? $entry->setUser( $data['user'] ) : $entry->setUser( 0 );

		// Run any registered filters before processing, passing the $entry object.
		// ? Should the logo, photo and category data be passed too?
		$entry = apply_filters( 'cn_pre_process_' . $action . '-entry', $entry, ( isset( $data['entry_category'] ) ? $data['entry_category'] : array() ) );

		/*
		 * Process the logo upload --> START <--
		 */
		if ( $_FILES['original_logo']['error'] != 4 ) {
			// If an entry is being updated and a new logo is uploaded, the old logo needs to be deleted.
			if ( $entry->getLogoName() != NULL ) {
				@unlink( CN_IMAGE_PATH . $entry->getLogoName() );
			}

			include_once CN_PATH . 'includes/admin/inc.processes.php';

			// Process the newly uploaded logo.
			$logoProcessResults = processLogo();

			// If there were no errors processing the logo, set the values.
			if ( $logoProcessResults ) {
				$entry->setLogoLinked( TRUE );
				$entry->setLogoDisplay( TRUE );
				$entry->setLogoName( $logoProcessResults['name'] );
			} else {
				$entry->setLogoLinked( FALSE );
				$entry->setLogoDisplay( FALSE );
			}
		} else {
			// Don't do this if an entry is being updated.
			if ( $action !== 'update' ) {
				// If an entry is being copied and there is a logo, the logo will be duplicated for the new entry.
				// That way if an entry is deleted, only the entry specific logo will be deleted.
				if ( $entry->getLogoName() != NULL ) $entry->setLogoName( copyImage( $entry->getLogoName() ) );
			}
		}
		/*
		 * Process the logo upload --> END <--
		 */

		/*
		 * If copying an entry, the logo visibility property is set based on the user's choice.
		 * NOTE: This must come after the logo processing.
		 */
		if ( isset( $data['logoOptions'] ) ) {

			switch ( $data['logoOptions'] ) {

				case 'remove':
					$entry->setLogoDisplay( FALSE );
					$entry->setLogoLinked( FALSE );

					/*
					 * Delete logo assigned to the entry.
					 */
					if ( is_file( CN_IMAGE_PATH . $entry->getLogoName() ) ) {
						@unlink( CN_IMAGE_PATH . $entry->getLogoName() );
					}

					$entry->setLogoName( NULL );
					break;

				case 'hidden':
					$entry->setLogoDisplay( FALSE );
					break;

				case 'show':
					$entry->setLogoDisplay( TRUE );
					break;

				default:
					$entry->setLogoDisplay( FALSE );
					break;
			}
		}


		// Process the entry image upload.
		if ( $_FILES['original_image']['error'] != 4 ) {
			// If an entry is being updated and a new image is uploaded, the old images need to be deleted.
			if ( $entry->getImageNameOriginal() != NULL ) {
				if ( $compatiblityDate < @filemtime( CN_IMAGE_PATH . $entry->getImageNameOriginal() ) ) {
					@unlink( CN_IMAGE_PATH . $entry->getImageNameOriginal() );
				}
			}

			if ( $entry->getImageNameThumbnail() != NULL ) {
				if ( $compatiblityDate < @filemtime( CN_IMAGE_PATH . $entry->getImageNameThumbnail() ) ) {
					@unlink( CN_IMAGE_PATH . $entry->getImageNameThumbnail() );

				}
			}

			if ( $entry->getImageNameCard() != NULL ) {
				if ( $compatiblityDate < @filemtime( CN_IMAGE_PATH . $entry->getImageNameCard() ) ) {
					@unlink( CN_IMAGE_PATH . $entry->getImageNameCard() );
				}
			}

			if ( $entry->getImageNameProfile() != NULL ) {
				if ( $compatiblityDate < @filemtime( CN_IMAGE_PATH . $entry->getImageNameProfile() ) ) {
					@unlink( CN_IMAGE_PATH . $entry->getImageNameProfile() );
				}
			}

			include_once CN_PATH . 'includes/admin/inc.processes.php';

			// Process the newly uploaded image.
			$image_proccess_results = processImages();

			// If there were no errors processing the image, set the values.
			if ( $image_proccess_results ) {
				$entry->setImageLinked( true );
				$entry->setImageDisplay( true );
				$entry->setImageNameThumbnail( $image_proccess_results['image_names']['thumbnail'] );
				$entry->setImageNameCard( $image_proccess_results['image_names']['entry'] );
				$entry->setImageNameProfile( $image_proccess_results['image_names']['profile'] );
				$entry->setImageNameOriginal( $image_proccess_results['image_names']['original'] );
			} else {
				$entry->setImageLinked( false );
				$entry->setImageDisplay( false );
			}
		} else {

			include_once CN_PATH . 'includes/admin/inc.processes.php';

			// Don't do this if an entry is being updated.
			if ( $action !== 'update' ) {
				// If an entry is being copied and there is an image, the image will be duplicated for the new entry.
				// That way if an entry is deleted, only the entry specific images will be deleted.
				if ( $entry->getImageNameOriginal() != NULL ) $entry->setImageNameOriginal( copyImage( $entry->getImageNameOriginal() ) );
				if ( $entry->getImageNameThumbnail() != NULL ) $entry->setImageNameThumbnail( copyImage( $entry->getImageNameThumbnail() ) );
				if ( $entry->getImageNameCard() != NULL ) $entry->setImageNameCard( copyImage( $entry->getImageNameCard() ) );
				if ( $entry->getImageNameProfile() != NULL ) $entry->setImageNameProfile( copyImage( $entry->getImageNameProfile() ) );
			}
		}


		// If copying an entry, the image visibility property is set based on the user's choice.
		// NOTE: This must come after the image processing.
		if ( isset( $data['imgOptions'] ) ) {

			switch ( $data['imgOptions'] ) {
				case 'remove':
					$entry->setImageDisplay( false );
					$entry->setImageLinked( false );

					/*
					 * Delete images assigned to the entry.
					 *
					 * Versions previous to 0.6.2.1 did not not make a duplicate copy of images when
					 * copying an entry so it was possible multiple entries could share the same image.
					 * Only images created after the date that version .0.7.0.0 was released will be deleted,
					 * plus a couple weeks for good measure.
					 */


					if ( is_file( CN_IMAGE_PATH . $entry->getImageNameOriginal() ) ) {
						if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $entry->getImageNameOriginal() ) ) {
							@unlink( CN_IMAGE_PATH . $entry->getImageNameOriginal() );
						}
					}

					if ( is_file( CN_IMAGE_PATH . $entry->getImageNameThumbnail() ) ) {
						if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $entry->getImageNameThumbnail() ) ) {
							@unlink( CN_IMAGE_PATH . $entry->getImageNameThumbnail() );

						}
					}

					if ( is_file( CN_IMAGE_PATH . $entry->getImageNameCard() ) ) {
						if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $entry->getImageNameCard() ) ) {
							@unlink( CN_IMAGE_PATH . $entry->getImageNameCard() );
						}
					}

					if ( is_file( CN_IMAGE_PATH . $entry->getImageNameProfile() ) ) {
						if ( $compatiblityDate < filemtime( CN_IMAGE_PATH . $entry->getImageNameProfile() ) ) {
							@unlink( CN_IMAGE_PATH . $entry->getImageNameProfile() );
						}
					}

					$entry->setImageNameOriginal( NULL );
					$entry->setImageNameThumbnail( NULL );
					$entry->setImageNameCard( NULL );
					$entry->setImageNameProfile( NULL );

					break;

				case 'hidden':
					$entry->setImageDisplay( false );
					break;

				case 'show':
					$entry->setImageDisplay( true );
					break;

				default:
					$entry->setImageDisplay( false );
					break;
			}
		}

		switch ( $action ) {

			case 'add':

				// Set moderation status per role capability assigned to the current user.
				if ( current_user_can( 'connections_add_entry' ) ) {
					$entry->setStatus( 'approved' );
					$messageID = 'entry_added';
				} elseif ( current_user_can( 'connections_add_entry_moderated' ) ) {
					$entry->setStatus( 'pending' );
					$messageID = 'entry_added_moderated';
				} else {
					$entry->setStatus( 'pending' );
					$messageID = 'entry_added_moderated';
				}

				// Save the entry to the database. On fail store error message.
				if ( $entry->save() == FALSE ) {

					cnMessage::set( 'error', 'entry_added_failed' );
					return FALSE;

				} else {

					cnMessage::set( 'success', $messageID );
					$entryID = (int) $connections->lastInsertID;
					$entry->setID( $entryID );
				}

				break;

			case 'update':

				// Set moderation status per role capability assigned to the current user.
				if ( current_user_can( 'connections_edit_entry' ) ) {
					$entry->setStatus( 'approved' );
					$messageID = 'entry_updated';
				} elseif ( current_user_can( 'connections_edit_entry_moderated' ) ) {
					$entry->setStatus( 'pending' );
					$messageID = 'entry_updated_moderated';
				} else {
					$entry->setStatus( 'pending' );
					$messageID = 'entry_updated_moderated';
				}

				// Update the entry to the database. On fail store error message.
				if ( $entry->update() == FALSE ) {

					cnMessage::set( 'error', 'entry_updated_failed' );
					return FALSE;

				} else {

					cnMessage::set( 'success', $messageID );
					$entryID = (int) $entry->getId();
				}

				break;
		}

		/*
		 * Save the entry category(ies). If none were checked, send an empty array
		 * which will add the entry to the default category.
		 */
		if ( isset( $data['entry_category'] ) ) {

			$connections->term->setTermRelationships( $entryID, $data['entry_category'], 'category' );

		} else {

			$connections->term->setTermRelationships( $entryID, array(), 'category' );
		}

		// Run any registered post process actions.
		$entry = do_action( 'cn_post_process_' . $action . '-entry', $entry );

		return TRUE;
	}

	/**
	 * Delete one or more entries.
	 *
	 * @todo Complete this method.
	 *
	 * @access private
	 * @since 0.7.8
	 * @param (array | int) $id 	The entry IDs to delete.
	 * @return (bool)
	 */
	public static function delete( $id ) {

		return FALSE;
	}

	/**
	 * Set the status of one or more entries.
	 *
	 * @todo Complete this method.
	 *
	 * @access private
	 * @since 0.7.8
	 * @param (string) $status 		The status to set. Valid options are: approved | pending
	 * @param (array | int) $id 	The entry IDs to set the status.
	 * @return (bool)
	 */
	public static function setStatus( $status, $id ) {

		return FALSE;
	}

	/**
	 * Set the visibility of one or more entries.
	 *
	 * @todo Complete this method.
	 *
	 * @access private
	 * @since 0.7.8
	 * @param (string) $visibility	The visibility to set. Valid options are: public | private | unlisted
	 * @param (array | int) $id 	The entry IDs to set the visibility.
	 * @return (bool)
	 */
	public static function setVisibility( $status, $id ) {

		return FALSE;
	}

}