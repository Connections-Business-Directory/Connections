<?php

/**
 * Class cnEntry_Image to manage the logo/photo image Entry attachments.
 *
 * This class is a work in progress and should not be used as it will have breaking changes as it is fully fleshed out.
 *
 * @access private
 * @since  8.19
 */
final class cnEntry_Image {

	/**
	 * @since 8.19
	 * @var cnEntry
	 */
	private $entry;

	/**
	 * cnEntry_Image constructor.
	 *
	 * @param cnEntry $entry
	 */
	public function __construct( cnEntry $entry ) {

		$this->entry = $entry;

		// Move any legacy images and logo, pre 8.1, to the new folder structure.
		$this->processLegacyImages( $this->entry->getImageNameOriginal() );
		$this->processLegacyLogo( $this->entry->getLogoName() );
	}

	/**
	 * Copy or move the originally uploaded image to the new folder structure, post 8.1.
	 *
	 * NOTE: If the original logo already exists in the new folder structure, this will
	 * return TRUE without any further processing.
	 *
	 * NOTE: Versions previous to 0.6.2.1 did not not make a duplicate copy of images when
	 * copying an entry so it was possible multiple entries could share the same image.
	 * Only images created after the date that version .0.7.0.0 was released will be moved,
	 * plus a couple weeks for good measure. Images before that date will be copied instead
	 * so it is available to be copied to the new folder structure, post 8.1, for any other
	 * entries that may require it.
	 *
	 * @access private
	 * @since  8.1
	 * @uses   wp_upload_dir()
	 * @uses   trailingslashit()
	 * @param  string $filename The original image file name.
	 *
	 * @return mixed            bool|WP_Error TRUE on success, an instance of WP_Error on failure.
	 */
	protected function processLegacyImages( $filename ) {
		global $blog_id;

		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$legacyPath = WP_CONTENT_DIR . '/blogs.dir/' . $blog_id . '/connection_images/';

		} else {

			$legacyPath = WP_CONTENT_DIR . '/connection_images/';
		}

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->entry->getSlug() );

		// Ensure the entry slug is not empty in case a user added an entry with no name.
		if ( empty( $slug ) ) return new WP_Error( 'image_empty_slug', __( sprintf( 'Failed to move legacy image %s.', $filename ), 'connections' ), $legacyPath . $filename );

		// Get the core WP uploads info.
		// $uploadInfo = wp_upload_dir();

		// Build the destination image path.
		$path = CN_IMAGE_PATH . $slug . DIRECTORY_SEPARATOR;

		/*
		 * NOTE: is_file() will always return false if teh folder/file does not
		 * have the execution bit set (ie 0775) on some hosts apparently. Need to
		 * come up with an alternative method which may not be possible without using
		 * WP_Filesystem and that causes a whole bunch of issues when credentials are
		 * required.
		 *
		 * Maybe chmodding the path to 0755 first, sounds safe?
		 * @link http://codex.wordpress.org/Changing_File_Permissions#Permission_Scheme_for_WordPress
		 * @link http://stackoverflow.com/a/11005
		 */

		// If the source image already exists in the new folder structure, post 8.1, bail, nothing to do.
		if ( is_file( $path . $filename ) ) {

			return TRUE;
		}

		if ( is_file( $legacyPath . $filename ) ) {

			// The modification file date that image will be deleted to maintain compatibility with 0.6.2.1 and older.
			$compatibilityDate = mktime( 0, 0, 0, 6, 1, 2010 );

			// Build path to the original file.
			$original = $legacyPath . $filename;

			// Get original file info.
			$info = pathinfo( $original );

			// Ensure the destination directory exists.
			if ( cnFileSystem::mkdir( $path ) ) {

				// Copy or move the original image.
				/** @noinspection PhpUsageOfSilenceOperatorInspection */
				if ( $compatibilityDate < @filemtime( $legacyPath . $filename ) ) {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @rename( $original, $path . $filename );

				} else {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @copy( $original, $path . $filename );
				}

				// Delete any of the legacy size variations if the copy/move was successful.
				if ( TRUE === $result ) {

					// NOTE: This is a little greedy as it will also delete any variations of any duplicate images used by other entries.
					// This should be alright because we will not need those variations anyway since they will be made from the original using cnImage.
					$files         = new DirectoryIterator( $legacyPath );
					$filesFiltered = new RegexIterator(
						$files,
						sprintf(
							'/%s(?:_thumbnail|_entry|_profile)(?:_\d+)?\.%s/i',
							preg_quote( preg_replace( '/(?:_original(?:_\d+)?)/i', '', $info['filename'] ) ),
							preg_quote( $info['extension'] )
						)
					);

					foreach ( $filesFiltered as $file ) {

						if ( $file->isDot() ) { continue; }

						/** @noinspection PhpUsageOfSilenceOperatorInspection */
						@unlink( $file->getPathname() );
					}

					return TRUE;
				}

			}

		}

		return new WP_Error( 'image_move_legacy_image_error', __( sprintf( 'Failed to move legacy image %s.', $filename ), 'connections' ), $legacyPath . $filename );
	}

	/**
	 * Copy or move the originally uploaded logo to the new folder structure, post 8.1.
	 *
	 * NOTE: If the original logo already exists in the new folder structure, this will
	 * return TRUE without any further processing.
	 *
	 * NOTE: Versions previous to 0.6.2.1 did not not make a duplicate copy of logos when
	 * copying an entry so it was possible multiple entries could share the same logo.
	 * Only logos created after the date that version .0.7.0.0 was released will be moved,
	 * plus a couple weeks for good measure. Images before that date will be copied instead
	 * so it is available to be copied to the new folder structure, post 8.1, for any other
	 * entries that may require it.
	 *
	 * @access private
	 * @since  8.1
	 * @uses   wp_upload_dir()
	 * @uses   trailingslashit()
	 * @param  string $filename The original logo file name.
	 *
	 * @return mixed            bool|WP_Error TRUE on success, an instance of WP_Error on failure.
	 */
	protected function processLegacyLogo( $filename ) {
		global $blog_id;

		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$legacyPath = WP_CONTENT_DIR . '/blogs.dir/' . $blog_id . '/connection_images/';

		} else {

			$legacyPath = WP_CONTENT_DIR . '/connection_images/';
		}

		// The entry slug is saved in the db URL encoded, so it needs to be decoded.
		$slug = rawurldecode( $this->entry->getSlug() );

		// Ensure the entry slug is not empty in case a user added an entry with no name.
		if ( empty( $slug ) ) return new WP_Error( 'image_empty_slug', __( sprintf( 'Failed to move legacy logo %s.', $filename ), 'connections' ), $legacyPath . $filename );

		// Get the core WP uploads info.
		// $uploadInfo = wp_upload_dir();

		// Build the destination logo path.
		$path = CN_IMAGE_PATH . $slug . DIRECTORY_SEPARATOR;

		// If the source logo already exists in the new folder structure, post 8.1, bail, nothing to do.
		if ( is_file( $path . $filename ) ) {

			return TRUE;
		}

		if ( is_file( $legacyPath . $filename ) ) {

			// The modification file date that logo will be deleted to maintain compatibility with 0.6.2.1 and older.
			$compatibilityDate = mktime( 0, 0, 0, 6, 1, 2010 );

			// Build path to the original file.
			$original = $legacyPath . $filename;

			// Ensure the destination directory exists.
			if ( cnFileSystem::mkdir( $path ) ) {

				// Copy or move the logo.
				/** @noinspection PhpUsageOfSilenceOperatorInspection */
				if ( $compatibilityDate < @filemtime( $legacyPath . $filename ) ) {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @rename( $original, $path . $filename );

				} else {

					/** @noinspection PhpUsageOfSilenceOperatorInspection */
					$result = @copy( $original, $path . $filename );
				}

				if ( TRUE === $result ) return TRUE;
			}

		}

		return new WP_Error( 'image_move_legacy_logo_error', __( sprintf( 'Failed to move legacy logo %s.', $filename ), 'connections' ), $legacyPath . $filename );
	}
}
