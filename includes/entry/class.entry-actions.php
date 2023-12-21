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

use Connections_Directory\Taxonomy\Registry;
use Connections_Directory\Utility\_array;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnEntry_Action
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnEntry_Action {

	/**
	 * Add an entry.
	 *
	 * @since 0.7.8
	 *
	 * @param array $data The data to be used when adding an entry.
	 *
	 * @return bool
	 */
	public static function add( $data ) {

		return self::process( 'add', $data );
	}

	/**
	 * Update an existing entry.
	 *
	 * @since 0.7.8
	 *
	 * @param int   $id   The entry ID.
	 * @param array $data The data to be used when updating an entry.
	 *
	 * @return bool
	 */
	public static function update( $id, $data ) {

		return self::process( 'update', $data, $id );
	}

	/**
	 * Copy an existing entry.
	 *
	 * @since 0.7.8
	 *
	 * @param int   $id   The entry ID which to duplicate.
	 * @param array $data The data to be used when duplicating an entry. Will be used add/replace existing data.
	 *
	 * @return bool
	 */
	public static function copy( $id, $data = array() ) {

		return self::process( 'add', $data, $id );
	}

	/**
	 * Process an image upload, creating the three size variations and caching them for later use.
	 *
	 * NOTE: The entry slug should be run through rawurldecode() before being passed to this method.
	 *
	 * @since 8.1
	 *
	 * @param string $entrySlug The entry slug.
	 *
	 * @return array|false An associative array containing the details about the uploaded image or false on failure.
	 */
	private static function processImage( $entrySlug ) {

		if ( ! isset( $_FILES['original_image'] ) ) {
			return false;
		}

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( is_wp_error( $img = cnImage::upload( $_FILES['original_image'], $entrySlug ) ) ) {

			cnMessage::set( 'error', implode( '<br />', $img->get_error_messages() ) );
			return false;
		}

		$cropMode = array( 0 => 'none', 1 => 'crop', 2 => 'fill', 3 => 'fit' );

		$large = cnImage::get(
			$img['url'],
			array(
				'crop_mode' => ( $key = array_search( cnSettingsAPI::get( 'connections', 'image_large', 'ratio' ), $cropMode ) ) || 0 === $key ? $key : 2,
				'width'     => cnSettingsAPI::get( 'connections', 'image_large', 'width' ),
				'height'    => cnSettingsAPI::get( 'connections', 'image_large', 'height' ),
				'quality'   => cnSettingsAPI::get( 'connections', 'image_large', 'quality' ),
				'sub_dir'   => $entrySlug,
			),
			'data'
		);

		if ( is_wp_error( $large ) ) {

			cnMessage::set( 'error', implode( '<br />', $large->get_error_messages() ) );
		}

		$medium = cnImage::get(
			$img['url'],
			array(
				'crop_mode' => ( $key = array_search( cnSettingsAPI::get( 'connections', 'image_medium', 'ratio' ), $cropMode ) ) || 0 === $key ? $key : 2,
				'width'     => cnSettingsAPI::get( 'connections', 'image_medium', 'width' ),
				'height'    => cnSettingsAPI::get( 'connections', 'image_medium', 'height' ),
				'quality'   => cnSettingsAPI::get( 'connections', 'image_medium', 'quality' ),
				'sub_dir'   => $entrySlug,
			),
			'data'
		);

		if ( is_wp_error( $medium ) ) {

			cnMessage::set( 'error', implode( '<br />', $large->get_error_messages() ) );
		}

		$thumb = cnImage::get(
			$img['url'],
			array(
				'crop_mode' => ( $key = array_search( cnSettingsAPI::get( 'connections', 'image_thumbnail', 'ratio' ), $cropMode ) ) || 0 === $key ? $key : 2,
				'width'     => cnSettingsAPI::get( 'connections', 'image_thumbnail', 'width' ),
				'height'    => cnSettingsAPI::get( 'connections', 'image_thumbnail', 'height' ),
				'quality'   => cnSettingsAPI::get( 'connections', 'image_thumbnail', 'quality' ),
				'sub_dir'   => $entrySlug,
			),
			'data'
		);

		if ( is_wp_error( $thumb ) ) {

			cnMessage::set( 'error', implode( '<br />', $large->get_error_messages() ) );
		}

		// Output the debug log.
		if ( $instance->options->getDebug() && is_admin() ) {

			if ( ! is_wp_error( $large ) && isset( $large['log'] ) ) {
				cnMessage::runtime( 'notice', 'Large Image Process Log<br/> <pre>' . $large['log'] . '</pre>' );
			}

			if ( ! is_wp_error( $medium ) && isset( $medium['log'] ) ) {
				cnMessage::runtime( 'notice', 'Medium Image Process Log<br/><pre>' . $medium['log'] . '</pre>' );
			}

			if ( ! is_wp_error( $thumb ) && isset( $thumb['log'] ) ) {
				cnMessage::runtime( 'notice', 'Thumbnail Image Process Log<br/><pre>' . $thumb['log'] . '</pre>' );
			}
		}

		return array( 'image_names' => array( 'original' => $img['name'] ), 'image' => array( 'original' => array( 'meta' => $img ) ) );
	}

	/**
	 * Process a logo upload, creating its size variation and caching it for later use.
	 *
	 * NOTE: The entry slug should be run through rawurldecode() before being passed to this method.
	 *
	 * @since 8.1
	 *
	 * @param string $entrySlug The entry slug.
	 *
	 * @return array|false An associative array containing the details about the uploaded logo or false on failure.
	 */
	private static function processLogo( $entrySlug ) {

		if ( ! isset( $_FILES['original_logo'] ) ) {
			return false;
		}

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( is_wp_error( $img = cnImage::upload( $_FILES['original_logo'], $entrySlug ) ) ) {

			cnMessage::set( 'error', implode( '<br />', $img->get_error_messages() ) );
			return false;
		}

		$cropMode = array( 0 => 'none', 1 => 'crop', 2 => 'fill', 3 => 'fit' );

		$logo = cnImage::get(
			$img['url'],
			array(
				'crop_mode' => ( $key = array_search( cnSettingsAPI::get( 'connections', 'image_logo', 'ratio' ), $cropMode ) ) || 0 === $key ? $key : 2,
				'width'     => cnSettingsAPI::get( 'connections', 'image_logo', 'width' ),
				'height'    => cnSettingsAPI::get( 'connections', 'image_logo', 'height' ),
				'quality'   => cnSettingsAPI::get( 'connections', 'image_logo', 'quality' ),
				'sub_dir'   => $entrySlug,
			),
			'data'
		);

		if ( is_wp_error( $logo ) ) {

			cnMessage::set( 'error', implode( '<br />', $logo->get_error_messages() ) );
			return false;
		}

		// Output the debug log.
		if ( $instance->options->getDebug() && is_admin() ) {

			if ( isset( $logo['log'] ) ) {
				cnMessage::runtime( 'notice', 'Logo Image Process Log<br/> <pre>' . $logo['log'] . '</pre>' );
			}
		}

		return $img;
	}

	/**
	 * Copies image from one entry to a new entry.
	 *
	 * NOTE: The entry slug should be run through rawurldecode() before being passed to this method.
	 *
	 * @since 8.1
	 *
	 * @param string $filename       The filename to copy.
	 * @param string $source         The source subdirectory (entry slug) of WP_CONTENT_DIR/CN_IMAGE_DIR_NAME of the image to copy.
	 * @param string $destination    The destination subdirectory (entry slug) of WP_CONTENT_DIR/CN_IMAGE_DIR_NAME of the image to copy.
	 * @param bool   $copyVariations Whether to opy the image variations.
	 *
	 * @return true|WP_Error TRUE on success, an instance of WP_Error on failure.
	 */
	public static function copyImages( $filename, $source, $destination, $copyVariations = true ) {

		// Get the core WP uploads info.
		// $uploadInfo = wp_upload_dir();

		// Build source path to the sub folder in which all the entry's images are saved.
		$sourcePath      = CN_IMAGE_PATH . $source . DIRECTORY_SEPARATOR;
		$sourceImagePath = $sourcePath . $filename;

		// Source file info.
		$sourceImageInfo = pathinfo( $sourceImagePath );

		// Build destination path to the sub folder in which all the entry's images are saved.
		$destinationPath = CN_IMAGE_PATH . $destination . DIRECTORY_SEPARATOR;

		// Create the new folder.
		cnFileSystem::mkdir( $destinationPath );

		if ( realpath( $sourcePath ) ) {

			$files = new DirectoryIterator( $sourcePath );

			foreach ( $files as $file ) {

				if ( $file->isDot() ) {
					continue;
				}

				if ( ! $file->isDir() && $file->isReadable() ) {

					$basename = basename( $file );

					// Skip image variations.
					if ( false === $copyVariations
						 && 1 === preg_match( '~.*-[a-f0-9]{32}\..*~i', $basename )
					) {

						continue;
					}

					$destinationFile = trailingslashit( realpath( $destinationPath ) ) . $basename;

					if ( copy( $file->getPathname(), $destinationFile ) === false ) {

						return new WP_Error( 'image_copy_error', __( 'Image copy failed.', 'connections' ) );
					}
				}
			}
		}

		return true;
	}

	/**
	 * Deletes the image and its variations from an entry.
	 *
	 * NOTE: The entry slug should be run through rawurldecode() before being passed
	 *       to this method as $source.
	 *
	 * @internal
	 * @since 8.1
	 *
	 * @param string $filename The base filename to delete.
	 * @param string $source   The source subdirectory (entry slug) of WP_CONTENT_DIR/CN_IMAGE_DIR_NAME of the images to delete.
	 */
	public static function deleteImages( $filename, $source ) {

		// Ensure neither $filename nor $source are empty. If one is bail.
		if ( empty( $filename ) || empty( $source ) ) {
			return;
		}

		// Get the core WP uploads info.
		// $uploadInfo = wp_upload_dir();

		// Build path to the sub folder in which all the entry's images are saved.
		$path = CN_IMAGE_PATH . $source . DIRECTORY_SEPARATOR;

		// If the $path does not exist, bail.
		if ( ! file_exists( $path ) ) {
			return;
		}

		// Build path to the original file.
		$original = $path . $filename;

		// Get original file info.
		$info = pathinfo( $original );

		// Delete the original uploaded file.
		@unlink( $original );

		// Now, delete any of its variations.
		// @url http://stackoverflow.com/a/18283138
		// foreach ( glob( "$path{$info['filename']}-H?*.{$info['extension']}", GLOB_NOSORT ) as $filename ) {

		// 	@unlink( $filename );
		// }

		// This will match a MD5 hash:  [a-f0-9]{32}
		// @url http://stackoverflow.com/a/21517123
		$files         = new DirectoryIterator( $path );
		$filesFiltered = new RegexIterator( $files, sprintf( '~%s-[a-f0-9]{32}.%s~i', preg_quote( $info['filename'] ), preg_quote( $info['extension'] ) ) );

		foreach ( $filesFiltered as $file ) {

			if ( is_callable( $file, 'isDot' ) ) {

				// isDot() Requires PHP >= 5.3.
				if ( $file->isDot() ) {
					continue;
				}

			} else {

				// Required for PHP 5.2 support.
				if ( basename( $file ) == '..' || basename( $file ) == '.' ) {
					continue;
				}
			}

			@unlink( $file->getPathname() );
		}
	}

	/**
	 * Deletes the image and its size variations from the legacy folder, pre 8.1.
	 *
	 * NOTE: Delete the image its size variations if is saved in the old CN_IMAGE_PATH folder, pre version 8.1
	 *
	 * Versions previous to 0.6.2.1 did not not make a duplicate copy of images when
	 * copying an entry, so it was possible multiple entries could share the same image.
	 * Only images created after the date that version .0.7.0.0 was released will be deleted,
	 * plus a couple of weeks for good measure.
	 *
	 * @internal
	 * @since 8.1
	 *
	 * @param cnEntry $entry An instance the cnEntry object.
	 */
	public static function deleteLegacyImages( $entry ) {
		global $blog_id;

		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$path = WP_CONTENT_DIR . '/sites/' . $blog_id . '/connection_images/';

		} else {

			$path = WP_CONTENT_DIR . '/connection_images/';
		}

		// The modification file date that image will be deleted to maintain compatibility with 0.6.2.1 and older.
		$compatibilityDate = mktime( 0, 0, 0, 6, 1, 2010 );

		if ( $entry->getImageNameOriginal() != null ) {

			if ( is_file( $path . $entry->getImageNameOriginal() ) &&
				$compatibilityDate < @filemtime( $path . $entry->getImageNameOriginal() )
				) {

				@unlink( $path . $entry->getImageNameOriginal() );
			}
		}

		if ( $entry->getImageNameThumbnail() != null ) {

			if ( is_file( $path . $entry->getImageNameThumbnail() ) &&
				$compatibilityDate < @filemtime( $path . $entry->getImageNameThumbnail() )
				) {

				@unlink( $path . $entry->getImageNameThumbnail() );
			}
		}

		if ( $entry->getImageNameCard() != null ) {

			if ( is_file( $path . $entry->getImageNameCard() ) &&
				$compatibilityDate < @filemtime( $path . $entry->getImageNameCard() )
				) {

				@unlink( $path . $entry->getImageNameCard() );
			}
		}

		if ( $entry->getImageNameProfile() != null ) {

			if ( is_file( $path . $entry->getImageNameProfile() ) &&
				$compatibilityDate < @filemtime( $path . $entry->getImageNameProfile() )
				) {

				@unlink( $path . $entry->getImageNameProfile() );
			}
		}
	}

	/**
	 * Deletes the logo from the legacy folder, pre 8.1.
	 *
	 * @internal
	 * @since 8.1
	 *
	 * @param cnEntry $entry An instance the cnEntry object.
	 */
	public static function deleteLegacyLogo( $entry ) {
		global $blog_id;

		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			$path = WP_CONTENT_DIR . '/sites/' . $blog_id . '/connection_images/';

		} else {

			$path = WP_CONTENT_DIR . '/connection_images/';
		}

		if ( $entry->getLogoName() != null &&
			is_file( $path . $entry->getLogoName() )
			) {

			@unlink( $path . $entry->getLogoName() );
		}
	}

	/**
	 * Add / Edit / Copy an entry.
	 *
	 * @since 0.7.8
	 *
	 * @param string $action Valid options are: add | update.
	 * @param array  $data   The data to be used when adding / editing / duplicating an entry.
	 * @param int    $id     If editing/duplicating an entry, the entry ID.
	 *
	 * @return bool|int FALSE on failure. Entry ID on success.
	 */
	private static function process( $action, $data = array(), $id = 0 ) {
		global $connections;

		/** @var cnEntry $entry */
		$entry = new cnEntry();

		// If copying/editing an entry, the entry data is loaded into the class
		// properties and then properties are overwritten by the data as needed.
		if ( ! empty( $id ) ) {
			$entry->set( absint( $id ) );
		}

		isset( $data['order'] ) ? $entry->setOrder( $data['order'] ) : 0;

		if ( isset( $data['entry_type'] ) ) {
			$entry->setEntryType( $data['entry_type'] );
		}

		if ( isset( $data['family_name'] ) ) {
			$entry->setFamilyName( $data['family_name'] );
		}

		( isset( $data['family_member'] ) ) ? $entry->setFamilyMembers( $data['family_member'] ) : $entry->setFamilyMembers( array() );

		if ( isset( $data['honorific_prefix'] ) ) {
			$entry->setHonorificPrefix( $data['honorific_prefix'] );
		}

		if ( isset( $data['first_name'] ) ) {
			$entry->setFirstName( $data['first_name'] );
		}

		if ( isset( $data['middle_name'] ) ) {
			$entry->setMiddleName( $data['middle_name'] );
		}

		if ( isset( $data['last_name'] ) ) {
			$entry->setLastName( $data['last_name'] );
		}

		if ( isset( $data['honorific_suffix'] ) ) {
			$entry->setHonorificSuffix( $data['honorific_suffix'] );
		}

		if ( isset( $data['title'] ) ) {
			$entry->setTitle( $data['title'] );
		}

		if ( isset( $data['organization'] ) ) {
			$entry->setOrganization( $data['organization'] );
		}

		if ( isset( $data['department'] ) ) {
			$entry->setDepartment( $data['department'] );
		}

		if ( isset( $data['contact_first_name'] ) ) {
			$entry->setContactFirstName( $data['contact_first_name'] );
		}

		if ( isset( $data['contact_last_name'] ) ) {
			$entry->setContactLastName( $data['contact_last_name'] );
		}

		( isset( $data['address'] ) ) ? $entry->setAddresses( $data['address'] ) : $entry->setAddresses( array() );
		( isset( $data['phone'] ) ) ? $entry->setPhoneNumbers( $data['phone'] ) : $entry->setPhoneNumbers( array() );
		( isset( $data['email'] ) ) ? $entry->setEmailAddresses( $data['email'] ) : $entry->setEmailAddresses( array() );
		( isset( $data['im'] ) ) ? $entry->setIm( $data['im'] ) : $entry->setIm( array() );
		( isset( $data['social'] ) ) ? $entry->setSocialMedia( $data['social'] ) : $entry->setSocialMedia( array() );
		// ( isset($data['website']) ) ? $entry->setWebsites($data['website']) : $entry->setWebsites( array() );
		( isset( $data['link'] ) ) ? $entry->setLinks( $data['link'] ) : $entry->setLinks( array() );
		( isset( $data['date'] ) ) ? $entry->setDates( $data['date'] ) : $entry->setDates( array() );

		if ( isset( $data['birthday_day'] ) && isset( $data['birthday_month'] ) ) {
			$entry->setBirthday( $data['birthday_day'], $data['birthday_month'] );
		}

		if ( isset( $data['anniversary_day'] ) && isset( $data['anniversary_month'] ) ) {
			$entry->setAnniversary( $data['anniversary_day'], $data['anniversary_month'] );
		}

		if ( isset( $data['bio'] ) ) {
			$entry->setBio( $data['bio'] );
		}

		if ( isset( $data['notes'] ) ) {
			$entry->setNotes( $data['notes'] );
		}

		if ( isset( $data['excerpt'] ) ) {
			$entry->setExcerpt( $data['excerpt'] );
		}

		if ( isset( $data['visibility'] ) ) {
			$entry->setVisibility( $data['visibility'] );
		}

		( isset( $data['user'] ) ) ? $entry->setUser( $data['user'] ) : $entry->getUser();

		switch ( $action ) {

			case 'add':
				// If the entry is being copied, the source slug needs to be copied because it is required
				// in order to copy the source entry images to the new entry.
				if ( ! empty( $id ) ) {

					$sourceEntrySlug = rawurldecode( $entry->getSlug() );

					$entry->setSlug( $entry->getName( array( 'format' => '%first%-%last%' ), 'db' ) );

				// If a new entry is being added, set the unique slug.
				} else {

					$entry->setSlug( $entry->getName( array( 'format' => '%first%-%last%' ), 'db' ) );
				}

				break;

			case 'update':
				// If an entry is being edited, set the new slug, if a new slug was provided.
				if ( isset( $data['slug'] ) && $data['slug'] != $entry->getSlug() ) {

					$entry->setSlug( $data['slug'] );
				}

				break;
		}

		$slug = rawurldecode( $entry->getSlug() );

		/**
		 * Allow the entry object to be modified before being inserted into the database.
		 *
		 * @since 10.4.25
		 *
		 * @param cnEntry $entry
		 * @param string  $action Values are either add|update
		 * @param array   $data   $_POST data.
		 */
		$entry = apply_filters( 'Connections_Directory/Entry/Action/Save', $entry, $action, $data );

		/**
		 * Allow the cnEntry object to be modified base on action.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the current entry action.
		 *
		 * @todo Should the logo, photo and category data be passed too?
		 *
		 * @since unknown
		 * @deprecated 10.4.25 Use `Connections_Directory/Entry/Action/Save`.
		 *
		 * @param cnEntry $entry An instance of the cnEntry object.
		 */
		$entry = apply_filters( "cn_pre_process_{$action}-entry", $entry, ( isset( $data['entry_category'] ) ? $data['entry_category'] : array() ) );
		/** @var cnEntry $entry */

		/*
		 * Process the logo upload --> START <--
		 */
		if ( isset( $_FILES['original_logo'] ) && 4 != $_FILES['original_logo']['error'] ) {

			// If an entry is being updated and a new logo is uploaded, the old logo needs to be deleted.
			// Delete the entry logo.
			self::deleteImages( $entry->getLogoName(), $slug );

			// Delete logo the legacy logo, pre 8.1.
			self::deleteLegacyLogo( $entry );

			// Process the newly uploaded image.
			$result = self::processLogo( $slug );

			// If there were no errors processing the logo, set the values.
			if ( $result ) {

				$entry->setLogoLinked( true );
				$entry->setLogoDisplay( true );
				$entry->setLogoName( $result['name'] );
				$entry->setOriginalLogoMeta( $result );

			} else {

				$entry->setLogoLinked( false );
				$entry->setLogoDisplay( false );
			}
		}

		// Don't do this if an entry is being updated.
		if ( 'update' !== $action ) {

			// If an entry is being copied and there is a logo, the logo will be duplicated for the new entry.
			// That way if an entry is deleted, only the entry specific logo will be deleted.
			if ( $entry->getLogoName() != null && ( isset( $sourceEntrySlug ) && ! empty( $sourceEntrySlug ) ) ) {

				self::copyImages( $entry->getLogoName(), $sourceEntrySlug, $slug );
			}
		}

		/*
		 * If copying an entry, the logo visibility property is set based on the user's choice.
		 * NOTE: This must come after the logo processing.
		 */
		if ( isset( $data['logoOptions'] ) ) {

			switch ( $data['logoOptions'] ) {

				case 'remove':
					$entry->setLogoDisplay( false );
					$entry->setLogoLinked( false );

					// Delete the entry image and its variations.
					self::deleteImages( $entry->getLogoName(), $slug );

					// Delete logo the legacy logo, pre 8.1.
					self::deleteLegacyLogo( $entry );

					$entry->setLogoName( null );
					break;

				case 'hidden':
					$entry->setLogoDisplay( false );
					break;

				case 'show':
					$entry->setLogoDisplay( true );
					break;

				default:
					$entry->setLogoDisplay( false );
					break;
			}
		}
		/*
		 * Process the logo upload --> END <--
		 */

		/*
		 * Process the image upload. --> START <--
		 */
		if ( isset( $_FILES['original_image'] ) && 4 != $_FILES['original_image']['error'] ) {

			// Delete the entry image and its variations.
			self::deleteImages( $entry->getImageNameOriginal(), $slug );

			// Delete any legacy images, pre 8.1, that may exist.
			self::deleteLegacyImages( $entry );

			// Process the newly uploaded image.
			$result = self::processImage( $slug );

			// If there were no errors processing the image, set the values.
			if ( $result ) {

				$entry->setImageLinked( true );
				$entry->setImageDisplay( true );
				$entry->setImageNameOriginal( $result['image_names']['original'] );
				$entry->setOriginalImageMeta( $result['image']['original']['meta'] );

			} else {

				$entry->setImageLinked( false );
				$entry->setImageDisplay( false );
			}

		}

		// Don't do this if an entry is being updated.
		if ( 'update' !== $action ) {

			// If an entry is being copied and there is an image, the image will be duplicated for the new entry.
			// That way if an entry is deleted, only the entry specific images will be deleted.
			if ( $entry->getImageNameOriginal() != null && ( isset( $sourceEntrySlug ) && ! empty( $sourceEntrySlug ) ) ) {

				self::copyImages( $entry->getImageNameOriginal(), $sourceEntrySlug, $slug );
			}
		}

		// If copying an entry, the image visibility property is set based on the user's choice.
		// NOTE: This must come after the image processing.
		if ( isset( $data['imgOptions'] ) ) {

			switch ( $data['imgOptions'] ) {

				case 'remove':
					$entry->setImageDisplay( false );
					$entry->setImageLinked( false );

					// Delete the entry image and its variations.
					self::deleteImages( $entry->getImageNameOriginal(), $slug );

					// Delete any legacy images, pre 8.1, that may exist.
					self::deleteLegacyImages( $entry );

					$entry->setImageNameOriginal( null );

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
		/*
		 * Process the image upload. --> END <--
		 */

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
				if ( $entry->save() === false ) {

					cnMessage::set( 'error', 'entry_added_failed' );
					return false;

				} else {

					cnMessage::set( 'success', $messageID );
					$entryID = (int) $connections->lastInsertID;
					$entry->setId( $entryID );
				}

				break;

			case 'update':
				// Set moderation status per role capability assigned to the current user.
				if ( current_user_can( 'connections_edit_entry' ) ) {

					$currentStatus = $entry->getStatus();

					if ( 'pending' === $currentStatus && current_user_can( 'connections_add_entry_moderated' ) ) {

						$entry->setStatus( 'pending' );
						$messageID = 'entry_updated_moderated';

					} elseif ( 'approved' === $currentStatus && current_user_can( 'connections_add_entry_moderated' ) ) {

						$entry->setStatus( 'approved' );
						$messageID = 'entry_updated';

					} elseif ( 'pending' === $currentStatus && current_user_can( 'connections_add_entry' ) ) {

						$entry->setStatus( 'approved' );
						$messageID = 'entry_updated';

					} elseif ( 'approved' === $currentStatus && current_user_can( 'connections_add_entry' ) ) {

						$entry->setStatus( 'approved' );
						$messageID = 'entry_updated';

					} else {

						// $entry->setStatus( 'pending' );
						// $messageID = 'entry_updated_moderated';
						$messageID = 'entry_updated';
					}

				} elseif ( current_user_can( 'connections_edit_entry_moderated' ) ) {

					$entry->setStatus( 'pending' );
					$messageID = 'entry_updated_moderated';

				} else {

					$entry->setStatus( 'pending' );
					$messageID = 'entry_updated_moderated';
				}

				// Update the entry to the database. On fail store error message.
				if ( $entry->update() === false ) {

					cnMessage::set( 'error', 'entry_updated_failed' );
					return false;

				} else {

					cnMessage::set( 'success', $messageID );
					$entryID = (int) $entry->getId();
				}

				break;
		}

		if ( isset( $entryID ) && 0 !== $entryID ) {

			$taxonomies = Registry::get()->getTaxonomies();

			foreach ( $taxonomies as $taxonomy ) {

				$taxonomySlug = $taxonomy->getSlug();

				if ( 'category' === $taxonomySlug ) {

					$terms = _array::get( $data, 'entry_category', array() );

				} else {

					$terms = _array::get( $data, "taxonomy_terms.{$taxonomySlug}", array() );
				}

				// $terms = $taxonomy->sanitizeTerms( $terms );
				// $terms = apply_filters( "Connections_Directory/Taxonomy/{$taxonomySlug}/Sanitize_Terms", $terms );

				do_action( "Connections_Directory/Taxonomy/{$taxonomySlug}/Attach_Terms", $entry, $terms, $action );
			}

			do_action( 'cn_process_meta-entry', $action, $entryID );

			// Refresh the cnEntry object with any updated taxonomy or metadata
			// that may have been added/updated via actions.
			$entry->set( $entryID );

			/**
			 * Trigger an action to be run after a directory entry being inserted into the database.
			 *
			 * @since 10.4.25
			 *
			 * @param cnEntry $entry
			 * @param string  $action Values are either add|update
			 * @param array   $data   $_POST data.
			 */
			do_action( 'Connections_Directory/Entry/Action/Saved', $entry, $action, $data );

			// Run any registered post process actions.
			do_action( "cn_post_process_{$action}-entry", $entry );

			return $entryID;
		}

		return false;
	}

	/**
	 * Set the status of one or more entries.
	 *
	 * @todo Needs capability check.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param string           $status The status to set.
	 *                                 Valid: approved|pending
	 * @param int|int[]|string $id     The entry IDs to set the status.
	 *                                 Supply as a singe ID, an array of IDs, or a string of comma delimited IDs.
	 *
	 * @return bool
	 */
	public static function status( $status, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$permitted = array( 'pending', 'approved' );

		// Ensure the status being set is permitted.
		if ( ! in_array( $status, $permitted ) ) {
			return false;
		}

		// Make sure $id is not empty.
		if ( empty( $id ) ) {
			return false;
		}

		// Check for and convert to an array.
		$ids = wp_parse_id_list( $id );

		do_action( 'Connections_Directory/Entry/Action/Set_Status/Before', $ids, $status );

		// Create the placeholders for the $id values to be used in $wpdb->prepare().
		$d = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// Sanitize the query, passing values to be sanitized as an array.
		$sql = $wpdb->prepare( 'UPDATE ' . CN_ENTRY_TABLE . ' SET status = %s WHERE id IN (' . $d . ')', array_merge( (array) $status, $ids ) );

		// Run the query.
		$result = $wpdb->query( $sql );

		if ( false !== $result ) {

			/**
			 * Action fired after entries have their status bulk changed.
			 *
			 * @since 8.2.5
			 *
			 * @param array $ids An array of entry IDs that had their status changed.
			 */
			do_action( 'cn_process_status', $ids );
			do_action( 'Connections_Directory/Entry/Action/Set_Status/After', $ids, $status );
		}

		return false !== $result ? true : false;
	}

	/**
	 * Set the visibility of one or more entries.
	 *
	 * @todo Needs capability check.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param string           $visibility The visibility to set.
	 *                                     Valid: public|private|unlisted
	 * @param int|int[]|string $id         The entry IDs to set the visibility.
	 *                                     Supply as a singe ID, an array of IDs, or a string of comma delimited IDs.
	 *
	 * @return bool
	 */
	public static function visibility( $visibility, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$permitted = array( 'public', 'private', 'unlisted' );

		// Ensure the status being set is permitted.
		if ( ! in_array( $visibility, $permitted ) ) {
			return false;
		}

		// Make sure $id is not empty.
		if ( empty( $id ) ) {
			return false;
		}

		// Check for and convert to an array.
		$ids = wp_parse_id_list( $id );

		do_action( 'Connections_Directory/Entry/Action/Set_Visibility/Before', $ids, $visibility );

		// Create the placeholders for the $id values to be used in $wpdb->prepare().
		$d = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// Sanitize the query, passing values to be sanitized as an array.
		$sql = $wpdb->prepare( 'UPDATE ' . CN_ENTRY_TABLE . ' SET visibility = %s WHERE id IN (' . $d . ')', array_merge( (array) $visibility, $ids ) );

		// Run the query.
		$result = $wpdb->query( $sql );

		if ( false !== $result ) {

			/**
			 * Action fired after entries have their visibility bulk changed.
			 *
			 * @since 8.2.5
			 *
			 * @param array $ids An array of entry IDs that had their visibility changed.
			 */
			do_action( 'cn_process_visibility', $ids );
			do_action( 'Connections_Directory/Entry/Action/Set_Visibility/After', $ids, $visibility );
		}

		return false !== $result ? true : false;
	}

	/**
	 * Delete one or more entries.
	 *
	 * @todo Needs capability check.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param int|int[]|string $ids The entry IDs to delete.
	 *                              Supplied as a singe ID, an array of IDs, or a string of comma delimited IDs.
	 *
	 * @return bool
	 */
	public static function delete( $ids ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Make sure $id is not empty.
		if ( empty( $ids ) ) {
			return false;
		}

		// Check for and convert to an array.
		$ids = wp_parse_id_list( $ids );

		foreach ( $ids as $id ) {

			$entry = new cnEntry( $instance->retrieve->entry( $id ) );
			$entry->delete( $id );

			// Delete any metadata associated with the entry.
			self::meta( 'delete', $id );
		}

		/**
		 * Action fired after entries are bulk deleted.
		 *
		 * @since 8.2.5
		 *
		 * @param array $ids An array of entry IDs that were deleted.
		 */
		do_action( 'cn_process_bulk_delete', $ids );

		return true;
	}

	/**
	 * Update the term taxonomy counts of the supplied entry IDs for the supplied taxonomies.
	 *
	 * @since 8.2.5
	 *
	 * @param mixed $ids      array|string An array or comma separated list of entry IDs.
	 * @param mixed $taxonomy array|string An array of taxonomies or taxonomy to update the term taxonomy count.
	 *
	 * @return array|WP_Error An indexed array of term taxonomy IDs which have had their term count updated. WP_Error on failure.
	 */
	public static function updateTermCount( $ids, $taxonomy = 'category' ) {

		// Check for and convert to an array.
		$ids = wp_parse_id_list( $ids );

		$result = cnTerm::getRelationships( $ids, $taxonomy, array( 'fields' => 'tt_ids' ) );

		if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
			cnTerm::updateCount( $result, $taxonomy );
		}

		cnCache::clear( true, 'transient', "cn_{$taxonomy}" );

		return $result;
	}

	/**
	 * Geocode the supplied address.
	 *
	 * @since 0.8
	 *
	 * @param  cnAddress $address An associative array containing the address to geocode.
	 *
	 * @return cnAddress The address that has been geocoded.
	 */
	public static function geoCode( $address ) {

		$query = \Connections_Directory\Model\Format\Address\As_String::format( $address );

		/**
		 * Allow geocoding of addresses to be short-circuited.
		 *
		 * @since 10.4.23
		 *
		 * @param bool      $bool    Whether to geocode the address or not.
		 * @param cnAddress $address The address to be geocoded.
		 */
		$maybeGeoCode = apply_filters( 'Connections_Directory/Entry/Action/Geocode', true, $address );

		/*
		 * If the address is empty, no need to geocode.
		 */
		if ( empty( $query ) || ! $maybeGeoCode ) {

			return $address;
		}

		/*
		 * Only geocode when the latitude and longitude have not been supplied.
		 */
		if ( ! empty( $address['latitude'] ) && ! empty( $address['longitude'] ) ) {

			return $address;
		}

		$APIkey = cnSettingsAPI::get( 'connections', 'google_maps_geocoding_api', 'server_key' );

		if ( 0 < strlen( $APIkey ) ) {

			$query = \Connections_Directory\Geocoder\Query\Address::create( $query );

			$provider = new \Connections_Directory\Geocoder\Provider\Google_Maps\Google_Maps( $APIkey );
			$geocoder = new \Connections_Directory\Geocoder\Geocoder( $provider );

			/** @var cnCollection $result */
			$result = $geocoder->geocode( $query );

			if ( ! is_wp_error( $result ) ) {

				/** @var \Connections_Directory\Model\Address $location */
				$location = $result->first();

				$address['latitude']  = $location->getLatitude();
				$address['longitude'] = $location->getLongitude();
			}

		} else {

			$query = \Connections_Directory\Geocoder\Query\Address::create( $query );

			$locale   = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale   = substr( $locale, 0, 2 );
			$provider = new \Connections_Directory\Geocoder\Provider\Algolia\Algolia();
			$geocoder = new \Connections_Directory\Geocoder\Geocoder( $provider, $locale );

			/** @var cnCollection $result */
			$result = $geocoder->geocode( $query );

			if ( ! is_wp_error( $result ) ) {

				/** @var \Connections_Directory\Model\Address $location */
				$location = $result->first();

				$address['latitude']  = $location->getLatitude();
				$address['longitude'] = $location->getLongitude();
			}
		}

		return $address;
	}

	/**
	 * Add, update or delete the meta of the specified entry ID.
	 *
	 * @since 0.8
	 *
	 * @param string $action The action to be performed.
	 * @param int    $id     The entry ID.
	 * @param array  $meta   An array of metadata the action is to be performed on.
	 *
	 * @return array          The meta IDs of the metadata the action was performed on.
	 */
	public static function meta( $action, $id, $meta = array() ) {

		$metaIDs = array();

		switch ( $action ) {

			case 'add':
				foreach ( $meta as $row ) {

					$metaIDs[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
				}

				break;

			case 'update':
				foreach ( $meta as $metaID => $row ) {

					cnMeta::update( 'entry', $id, $row['key'], $row['value'] );

					$metaIDs[] = $metaID;
				}

				break;

			case 'delete':
				if ( empty( $meta ) ) {

					$meta = cnMeta::get( 'entry', $id );
				}

				if ( $meta ) {

					foreach ( $meta as $key => $value ) {

						cnMeta::delete( 'entry', $id, $key );

						$metaIDs[] = $key;
					}
				}

				break;
		}

		return $metaIDs;
	}

	/**
	 * Purge entry related caches when an entry is added/edited.
	 *
	 * @since 8.1
	 */
	public static function clearCache() {

		cnCache::clear( true, 'transient', 'cn_category' );
		cnCache::clear( true, 'transient', 'cn_relative' );

		/**
		 * Action fired after entry related caches are cleared.
		 *
		 * @since 8.2.5
		 */
		do_action( 'cn_clean_entry_cache' );
	}
}
