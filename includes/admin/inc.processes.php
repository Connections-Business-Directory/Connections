<?php

/**
 * Entry management functions.
 *
 * @package     Connections
 * @subpackage  Entry management functions.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function copyImage( $image ) {
	// Uses the upload.class.php to handle file uploading and image manipulation.
	// GPL PHP upload class from http://www.verot.net/php_class_upload.htm
	require_once CN_PATH . '/includes/libraries/php_class_upload/class.upload.php';

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
	require_once CN_PATH . '/includes/libraries/php_class_upload/class.upload.php';

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
	require_once CN_PATH . '/includes/libraries/php_class_upload/class.upload.php';

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