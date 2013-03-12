<?php

/**
 * Add or edit and entry.
 *
 * @param array   $data
 * @param string  $action
 * @return bool
 */
function processEntry( $data, $action ) {
	global $wpdb, $connections;
	$entry = new cnEntry();

	//if ( isset($_GET['action']) ) $action = $_GET['action'];

	// The modification file date that image will be deleted. to maintain compatibility with 0.6.2.1 and older.
	$compatiblityDate = mktime( 0, 0, 0, 6, 1, 2010 );

	// If copying/editing an entry, the entry data is loaded into the class
	// properties and then properties are overwritten by the POST data as needed.
	if ( isset( $_GET['id'] ) ) {
		$entry->set( absint( $_GET['id'] ) );
	}

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
				$connections->setErrorMessage( 'entry_added_failed' );
				return FALSE;
			} else {
				$connections->setSuccessMessage( $messageID );
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
				$connections->setErrorMessage( 'entry_updated_failed' );
				return FALSE;
			} else {
				$connections->setSuccessMessage( $messageID );
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

	unset( $entry );

	return TRUE;
}

function copyImage( $image ) {
	// Uses the upload.class.php to handle file uploading and image manipulation.
	// GPL PHP upload class from http://www.verot.net/php_class_upload.htm
	require_once CN_PATH . '/includes/php_class_upload/class.upload.php';

	$source = CN_IMAGE_PATH . $image;

	$process_image = new Upload( $source );
	$process_image->Process( CN_IMAGE_PATH );
	$process_image->file_safe_name  = true;
	$process_image->file_auto_rename = true;
	$image = $process_image->file_dst_name;

	return $image;
}

function processImages() {
	if ( !isset( $_FILES['original_image'] ) ) return FALSE;

	global $connections;

	// Uses the upload.class.php to handle file uploading and image manipulation.
	// GPL PHP upload class from http://www.verot.net/php_class_upload.htm
	require_once CN_PATH . '/includes/php_class_upload/class.upload.php';

	$process_image = new Upload( $_FILES['original_image'] );
	$image['source'] = $process_image->file_src_name_body;

	if ( $process_image->uploaded ) {
		// Saves the uploaded image with no changes to the wp_content/connection_images/ dir.
		// If needed this will create the upload dir and chmod it.
		$process_image->allowed    = array( 'image/jpeg', 'image/gif', 'image/png' );
		$process_image->auto_create_dir  = true;
		$process_image->auto_chmod_dir  = true;
		$process_image->file_safe_name  = true;
		$process_image->file_auto_rename = true;
		$process_image->file_name_body_add = '_original';
		$process_image->image_convert  = 'jpg';
		$process_image->jpeg_quality  = 80;
		$process_image->Process( CN_IMAGE_PATH );

		// If the orignal image uploaded and process ok, then create the derivative images.
		if ( $process_image->processed ) {
			$connections->setSuccessMessage( 'image_uploaded' );

			@chmod( CN_IMAGE_PATH . '/' . $process_image->file_dst_name , 0644 );
			$image['original'] = $process_image->file_dst_name;

			// Creates the profile image and saves it to the wp_content/connection_images/ dir.
			// If needed this will create the upload dir and chmod it.
			$process_image->allowed    = array( 'image/jpeg', 'image/gif', 'image/png' );
			$process_image->auto_create_dir  = true;
			$process_image->auto_chmod_dir  = true;
			$process_image->file_safe_name  = true;
			$process_image->file_auto_rename = true;
			$process_image->file_name_body_add = '_profile';
			$process_image->image_convert  = 'jpg';
			$process_image->jpeg_quality  = $connections->options->getImgProfileQuality();
			$process_image->image_resize  = true;
			$process_image->image_ratio_crop = (bool) $connections->options->getImgProfileRatioCrop();
			$process_image->image_ratio_fill = (bool) $connections->options->getImgProfileRatioFill();
			$process_image->image_y    = $connections->options->getImgProfileY();
			$process_image->image_x    = $connections->options->getImgProfileX();
			$process_image->Process( CN_IMAGE_PATH );
			if ( $process_image->processed ) {
				$connections->setSuccessMessage( 'image_profile' );

				@chmod( CN_IMAGE_PATH . '/' . $process_image->file_dst_name , 0644 );
				$image['profile'] = $process_image->file_dst_name;
			} else {
				$connections->setErrorMessage( 'image_profile_failed' );
				//return FALSE;
			}

			/*var_dump($connections->options->getImgProfileQuality());
			var_dump($connections->options->getImgProfileRatioCrop());
			var_dump($connections->options->getImgProfileRatioFill());
			var_dump($connections->options->getImgProfileY());
			var_dump($connections->options->getImgProfileX());
			die;*/

			// Creates the entry image and saves it to the wp_content/connection_images/ dir.
			// If needed this will create the upload dir and chmod it.
			$process_image->allowed    = array( 'image/jpeg', 'image/gif', 'image/png' );
			$process_image->auto_create_dir  = true;
			$process_image->auto_chmod_dir  = true;
			$process_image->file_safe_name  = true;
			$process_image->file_auto_rename = true;
			$process_image->file_name_body_add = '_entry';
			$process_image->image_convert  = 'jpg';
			$process_image->jpeg_quality  = $connections->options->getImgEntryQuality();
			$process_image->image_resize  = true;
			$process_image->image_ratio_crop = (bool) $connections->options->getImgEntryRatioCrop();
			$process_image->image_ratio_fill = (bool) $connections->options->getImgEntryRatioFill();
			$process_image->image_y    = $connections->options->getImgEntryY();
			$process_image->image_x    = $connections->options->getImgEntryX();
			$process_image->Process( CN_IMAGE_PATH );
			if ( $process_image->processed ) {
				$connections->setSuccessMessage( 'image_entry' );

				@chmod( CN_IMAGE_PATH . '/' . $process_image->file_dst_name , 0644 );
				$image['entry'] = $process_image->file_dst_name;
			} else {
				$connections->setErrorMessage( 'image_entry_failed' );
				//return FALSE;
			}

			// Creates the thumbnail image and saves it to the wp_content/connection_images/ dir.
			// If needed this will create the upload dir and chmod it.
			$process_image->allowed    = array( 'image/jpeg', 'image/gif', 'image/png' );
			$process_image->auto_create_dir  = true;
			$process_image->auto_chmod_dir  = true;
			$process_image->file_safe_name  = true;
			$process_image->file_auto_rename = true;
			$process_image->file_name_body_add = '_thumbnail';
			$process_image->image_convert  = 'jpg';
			$process_image->jpeg_quality  = $connections->options->getImgThumbQuality();
			$process_image->image_resize  = true;
			$process_image->image_ratio_crop = (bool) $connections->options->getImgThumbRatioCrop();
			$process_image->image_ratio_fill = (bool) $connections->options->getImgThumbRatioFill();
			$process_image->image_y    = $connections->options->getImgThumbY();
			$process_image->image_x    = $connections->options->getImgThumbX();
			$process_image->Process( CN_IMAGE_PATH );
			if ( $process_image->processed ) {
				$connections->setSuccessMessage( 'image_thumbnail' );

				@chmod( CN_IMAGE_PATH . '/' . $process_image->file_dst_name , 0644 );
				$image['thumbnail'] = $process_image->file_dst_name;
			} else {
				$connections->setErrorMessage( 'image_thumbnail_failed' );
				//return FALSE;
			}
		}
		else {
			$connections->setErrorMessage( 'image_uploaded_failed' );
			return FALSE;
		}

		// Output the debug log.
		if ( $connections->options->getDebug() && is_admin() ) $connections->setRuntimeMessage( 'success_runtime' , $process_image->log );

		$process_image->Clean();

	}
	else {
		$connections->setErrorMessage( 'image_upload_failed' );
		return FALSE;
	}

	return array( 'image_names'=>$image );
}

function processLogo() {
	if ( !isset( $_FILES['original_logo'] ) ) return FALSE;

	global $connections;

	// Uses the upload.class.php to handle file uploading and image manipulation.
	// GPL PHP upload class from http://www.verot.net/php_class_upload.htm
	require_once CN_PATH . '/includes/php_class_upload/class.upload.php';

	$process_logo = new Upload( $_FILES['original_logo'] );

	if ( $process_logo->uploaded ) {
		$connections->setSuccessMessage( 'image_uploaded' );

		// Creates the logo image and saves it to the wp_content/connection_images/ dir.
		// If needed this will create the upload dir and chmod it.
		$process_logo->allowed    = array( 'image/jpeg', 'image/gif', 'image/png' );
		$process_logo->auto_create_dir  = TRUE;
		$process_logo->auto_chmod_dir  = TRUE;
		$process_logo->file_safe_name  = TRUE;
		$process_logo->file_auto_rename  = TRUE;
		$process_logo->file_name_body_add = '_logo';
		$process_logo->image_convert  = 'jpg';
		$process_logo->jpeg_quality   = $connections->options->getImgLogoQuality();
		$process_logo->image_resize   = TRUE;
		$process_logo->image_ratio_crop  = (bool) $connections->options->getImgLogoRatioCrop();
		$process_logo->image_ratio_fill  = (bool) $connections->options->getImgLogoRatioFill();
		$process_logo->image_y    = $connections->options->getImgLogoY();
		$process_logo->image_x    = $connections->options->getImgLogoX();
		$process_logo->Process( CN_IMAGE_PATH );

		if ( $process_logo->processed ) {
			$connections->setSuccessMessage( 'image_thumbnail' );
			if ( $connections->options->getDebug() && is_admin() ) $connections->setRuntimeMessage( 'success_runtime' , $process_logo->log );
			@chmod( CN_IMAGE_PATH . '/' . $process_logo->file_dst_name , 0644 );
			$logo['name'] = $process_logo->file_dst_name;
		} else {
			$connections->setErrorMessage( 'image_thumbnail_failed' );
			return FALSE;
		}

		$process_logo->Clean();

	}
	else {
		$connections->setErrorMessage( 'image_upload_failed' );
		return FALSE;
	}

	return $logo;
}

function processSetEntryStatus( $status ) {
	$permitted = array( 'pending', 'approved' );
	if ( !in_array( $status, $permitted ) ) return FALSE;

	/*
	 * Check whether the current user can edit entries.
	 */
	if ( current_user_can( 'connections_edit_entry' ) ) {
		global $connections;

		$id = esc_attr( $_GET['id'] );
		check_admin_referer( 'entry_status_' . $id );

		$entry = new cnEntry();
		$entry->set( $id );

		$entry->setStatus( $status );
		$entry->update();
		unset( $entry );

		switch ( $status ) {
		case 'pending':
			$connections->setSuccessMessage( 'form_entry_pending' );
			break;

		case 'approved':
			$connections->setSuccessMessage( 'form_entry_approve' );
			break;
		}
	}
	else {
		$connections->setErrorMessage( 'capability_edit' );
	}
}

function processSetEntryStatuses( $status ) {
	$permitted = array( 'pending', 'approved' );
	if ( !in_array( $status, $permitted ) ) return FALSE;

	/*
	 * Check whether the current user can edit entries.
	 */
	if ( current_user_can( 'connections_edit_entry' ) ) {
		global $connections;

		foreach ( $_POST['entry'] as $id ) {
			$entry = new cnEntry();

			$id = esc_attr( $id );
			$entry->set( $id );

			$entry->setStatus( $status );
			$entry->update();
			unset( $entry );
		}

		switch ( $status ) {
		case 'pending':
			$connections->setSuccessMessage( 'form_entry_pending_bulk' );
			break;

		case 'approved':
			$connections->setSuccessMessage( 'form_entry_approve_bulk' );
			break;
		}
	}
	else {
		$connections->setErrorMessage( 'capability_edit' );
	}
}

function processSetEntryVisibility() {
	$permitted = array( 'public', 'private', 'unlisted' );
	if ( !in_array( $_POST['action'], $permitted ) ) return FALSE;

	/*
	 * Check whether the current user can edit entries.
	 */
	if ( current_user_can( 'connections_edit_entry' ) ) {
		global $connections;

		foreach ( $_POST['entry'] as $id ) {
			$entry = new cnEntry();

			$id = esc_attr( $id );
			$entry->set( $id );

			$entry->setVisibility( $_POST['action'] );
			$entry->update();
			unset( $entry );
		}

		$connections->setSuccessMessage( 'form_entry_visibility_bulk' );
	} else {
		$connections->setErrorMessage( 'capability_edit' );
	}
}

function processSetUserFilter() {
	global $connections;

	if ( isset( $_POST['entry_type'] ) ) $connections->currentUser->setFilterEntryType( $_POST['entry_type'] );
	if ( isset( $_POST['visibility_type'] ) ) $connections->currentUser->setFilterVisibility( $_POST['visibility_type'] );

	if ( isset( $_POST['category'] ) ) $connections->currentUser->setFilterCategory( esc_attr( $_POST['category'] ) );
	if ( !empty( $_GET['category_id'] ) ) $connections->currentUser->setFilterCategory( esc_attr( $_GET['category_id'] ) );

	if ( isset( $_POST['pg'] ) && ! empty( $_POST['pg'] ) ) {
		$page = new stdClass();

		$page->name = 'manage';
		$page->current = absint( $_POST['pg'] );

		$connections->currentUser->setFilterPage( $page );
	}

	if ( isset( $_GET['pg'] ) && ! empty( $_GET['pg'] ) ) {
		$page = new stdClass();

		$page->name = 'manage';
		$page->current = absint( $_GET['pg'] );

		$connections->currentUser->setFilterPage( $page );
	}

	if ( isset( $_POST['settings']['page']['limit'] ) ) {
		$page = new stdClass();

		$page->name = 'manage';
		$page->limit = $_POST['settings']['page']['limit'];

		$connections->currentUser->setFilterPage( $page );
	}
}

function processDeleteEntry() {
	/*
	 * Check whether the current user delete an entry.
	 */
	if ( current_user_can( 'connections_delete_entry' ) ) {
		global $connections;

		$id = esc_attr( $_GET['id'] );
		check_admin_referer( 'entry_delete_' . $id );

		$entry = new cnEntry( $connections->retrieve->entry( $id ) );
		$entry->delete( $id );
		$connections->setSuccessMessage( 'form_entry_delete' );
		unset( $entry );
	}
	else {
		$connections->setErrorMessage( 'capability_delete' );
	}
}

function processDeleteEntries() {
	/*
	 * Check whether the current user delete an entry.
	 */
	if ( current_user_can( 'connections_delete_entry' ) ) {
		global $connections;

		if ( empty( $_POST['entry'] ) ) return FALSE;

		if ( current_user_can( 'connections_delete_entry' ) ) {
			$ids = $_POST['entry'];

			foreach ( $ids as $id ) {
				$entry = new cnEntry( $connections->retrieve->entry( $id ) );
				$id = esc_attr( $id );
				$entry->delete( $id );
				unset( $entry );
			}

			$connections->setSuccessMessage( 'form_entry_delete_bulk' );
		}
		else {
			$connections->setErrorMessage( 'capability_delete' );
		}
	}
	else {
		$connections->setErrorMessage( 'capability_delete' );
	}
}

function processAddCategory() {
	$category = new cnCategory();
	$format = new cnFormatting();

	$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
	$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
	$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
	$category->setDescription( $format->sanitizeString( $_POST['category_description'] ) );

	$category->save();
}

function processUpdateCategory() {
	$category = new cnCategory();
	$format = new cnFormatting();

	$category->setID( $format->sanitizeString( $_POST['category_id'] ) );
	$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
	$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
	$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
	$category->setDescription( $format->sanitizeString( $_POST['category_description'] ) );

	$category->update();
}


function processDeleteCategory( $type ) {
	global $connections;

	switch ( $type ) {
	case 'delete':
		$id = esc_attr( $_GET['id'] );
		check_admin_referer( 'category_delete_' . $id );

		$result = $connections->retrieve->category( $id );
		$category = new cnCategory( $result );
		$category->delete();
		break;

	case 'bulk_delete':
		foreach ( (array) $_POST['category'] as $cat_ID ) {
			$cat_ID = esc_attr( $cat_ID );

			$result = $connections->retrieve->category( attribute_escape( $cat_ID ) );
			$category = new cnCategory( $result );
			$category->delete();
		}
		break;
	}
}

function processActivateTemplate() {
	$templateName = esc_attr( $_GET['template'] );
	check_admin_referer( 'activate_' . $templateName );

	global $connections;

	$type = esc_attr( $_GET['type'] );
	$slug = esc_attr( $_GET['template'] );

	$connections->options->setActiveTemplate( $type, $slug );

	$connections->options->saveOptions();
	$connections->setSuccessMessage( 'template_change_active' );

	delete_transient( 'cn_legacy_templates' );
}

function processInstallTemplate() {
	global $connections;
	require_once ABSPATH . 'wp-admin/includes/file.php';

	WP_Filesystem();
	if ( unzip_file( $_FILES['template']['tmp_name'], CN_CUSTOM_TEMPLATE_PATH ) ) {
		$connections->setSuccessMessage( 'template_installed' );
	}
	else {
		$connections->setErrorMessage( 'template_install_failed' );
	}

	delete_transient( 'cn_legacy_templates' );
}

function processDeleteTemplate() {
	global $connections;

	$templateName = esc_attr( $_GET['template'] );
	check_admin_referer( 'delete_' . $templateName );

	function removeDirectory( $directory ) {
		$deleteError = FALSE;
		$currentDirectory = opendir( $directory );

		while ( ( $file = readdir( $currentDirectory ) ) !== FALSE ) {
			if ( $file != "." && $file != ".." ) {
				chmod( $directory . $file, 0777 );

				if ( is_dir( $directory . $file ) ) {
					chdir( '.' );
					removeDirectory( $directory . $file . '/' );
					rmdir( $directory . $file ) or $deleteError = TRUE;
				}
				else {
					@unlink( $directory . $file ) or $deleteError = TRUE;
				}

				if ( $deleteError ) return FALSE;
			}
		}

		closedir( $currentDirectory );
		if ( !rmdir( $directory ) ) return FALSE;

		return TRUE;
	}

	if ( removeDirectory( CN_CUSTOM_TEMPLATE_PATH . '/' . $templateName . '/' ) ) {
		$connections->setSuccessMessage( 'template_deleted' );
	}
	else {
		$connections->setErrorMessage( 'template_delete_failed' );
	}

	delete_transient( 'cn_legacy_templates' );
}