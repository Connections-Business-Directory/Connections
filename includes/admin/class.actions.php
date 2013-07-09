<?php

/**
 * Class for processing admin action.
 *
 * @package     Connections
 * @subpackage  Admin Actions
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnAdminActions {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.7.5
	 * @var (object)
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.7.5
	 * @see cnAdminActions::init()
	 * @see cnAdminActions();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class, if it has already been initialized, return the intialized instance.
	 *
	 * @access public
	 * @since 0.7.5
	 * @see cnAdminActions()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			self::registerActions();
			self::doActions();

		}

	}

	/**
	 * Return an instance of the class.
	 *
	 * @access public
	 * @since 0.7.5
	 * @return (object) cnAdminActions
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Register admin actions.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses add_action()
	 * @return (void)
	 */
	private static function registerActions() {

		// Entry Actions
		add_action( 'cn_add_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_update_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_copy_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_delete_entry', array( __CLASS__, 'deleteEntry' ) );
		add_action( 'cn_set_status', array( __CLASS__, 'setEntryStatus' ) );

		// Save the user's manage admin page actions.
		add_action( 'cn_manage_actions', array( __CLASS__, 'entryManagement' ) );
		add_action( 'cn_filter', array( __CLASS__, 'userFilter' ) );

		// Role Actions
		add_action( 'cn_update_role_capabilities', array( __CLASS__, 'updateRoleCapabilities' ) );

		// Category Actions
		add_action( 'cn_add_category', array( __CLASS__, 'addCategory' ) );
		add_action( 'cn_update_category', array( __CLASS__, 'updateCategory' ) );
		add_action( 'cn_delete_category', array( __CLASS__, 'deleteCategory' ) );
		add_action( 'cn_category_bulk_actions', array( __CLASS__, 'categoryManagement' ) );

		// Template Actions
		add_action( 'cn_activate_template', array( __CLASS__, 'activateTemplate' ) );
		add_action( 'cn_install_template', array( __CLASS__, 'installTemplate' ) );
		add_action( 'cn_delete_template', array( __CLASS__, 'deleteTemplate' ) );
	}

	/**
	 * Run admin actions.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses do_action()
	 * @return (void)
	 */
	private static function doActions() {

		if ( isset( $_POST['cn-action'] ) ) {

			do_action( 'cn_' . $_POST['cn-action'] );
		}

		if ( isset( $_GET['cn-action'] ) ) {

			do_action( 'cn_' . $_GET['cn-action'] );
		}
	}

	/**
	 * Process controller for action taken by the user.
	 *
	 * @access private
	 * @since 0.7.8
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function entryManagement() {

		$form = new cnFormObjects();
		$queryVar = array();

		check_admin_referer( $form->getNonce( 'cn_manage_actions' ), '_cn_wpnonce' );

		/*
		 * Run user requested actions.
		 */

		// Process user selected filters
		if ( isset( $_POST['filter'] ) || isset( $_GET['filter'] ) ) self::saveUserFilters();

		// Grab the bulk action requesteed by user.
		$action = isset( $_POST['bulk_action'] ) && ( isset( $_POST['action'] ) && ! empty( $_POST['action'] ) ) ? $_POST['action'] : 'none';

		switch ( $action ) {

			case 'delete':

				// Bulk delete entries.
				self::deleteEntryBulk();
				break;

			case 'approve':

				// Bulk approve entries.
				self::setEntryStatusBulk( 'approved' );
				break;

			case 'unapprove':

				// Bulk unapprove entries.
				self::setEntryStatusBulk( 'pending' );
				break;

			case 'public':

				// Set entries to public visibility in bulk.
				self::setEntryVisibilityBulk( 'public' );
				break;

			case 'private':

				// Set entries to private visibility in bulk.
				self::setEntryVisibilityBulk( 'private' );
				break;

			case 'unlisted':

				// Set entries to unlisted visibility in bulk.
				self::setEntryVisibilityBulk( 'unlisted' );
				break;

			default:

				/* None, blank intentially. */

				break;
		}

		/*
		 * Setup the redirect.
		 */

		if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) )
			$queryVar['s'] = urlencode( $_POST['s'] );

		// if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) )
		// 	$queryVar['s'] = urlencode( $_GET['s'] );

		if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) )
			$queryVar['cn-char'] = urlencode( $_GET['cn-char'] );

		/*
		 * Do the redirect.
		 */

		wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( $queryVar, 'admin.php?page=connections_manage' ) ) );

		exit();
	}

	/**
	 * Add / Edit / Update / Copy an entry.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (array)  $data [optional]
	 * @param  (string) $action [optional]
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void) | (bool)
	 */
	public static function processEntry( $data = array(), $action = '' ) {
		global $wpdb, $connections;
		$entry = new cnEntry();
		$form = new cnFormObjects();

		// Set $action. The only valid options are `add` and `update`.
		if ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) $action = $_GET['action'];

		switch ( $action ) {

			case 'add':

				// Setup the redirect URL.
				$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_add';

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'add_entry' ), '_cn_wpnonce' );

				} else {

					cnMessage::set( 'error', 'capability_add' );

					wp_redirect( get_admin_url( get_current_blog_id(), $redirect) );

					exit();
				}

				break;

			case 'update':

				// Setup the redirect URL.
				$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_manage';

				/*
				 * Check whether the current user can edit an entry.
				 */
				if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'update_entry' ), '_cn_wpnonce' );

				} else {

					cnMessage::set( 'error', 'capability_edit' );

					wp_redirect( get_admin_url( get_current_blog_id(), $redirect) );

					exit();
				}

				break;

			default:

				// Shouldn't end up here, but lets bail, just in case.
				return FALSE;

				break;
		}

		// If $data is empty, assume, data was sent via $_POST.
		if ( empty( $data ) ) $data = $_POST;

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
					// return FALSE;
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
					// return FALSE;
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

		// return TRUE;

		wp_redirect( get_admin_url( get_current_blog_id(), $redirect) );

		exit();

	}

	/**
	 * Set the entry status to pending or approved.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (int) $id [optional] Entry ID.
	 * @param (string) $status [optional] The entry status to be assigned.
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function setEntryStatus( $id = '', $status = '' ) {

		// If no entry ID was supplied, check $_GET.
		$id = empty( $id ) && ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) ? absint( $_GET['id'] ) : absint( $id );

		check_admin_referer( 'entry_status_' . $id );

		/*
		 * Check whether the current user can edit an entry.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			//  The permitted statuses.
			$permitted = array( 'pending', 'approved' );

			// If `status` was not supplied, check $_GET.
			if ( ( empty( $status ) ) && ( isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ) ) {

				$status = $_GET['status'];

			}

			// Ensure the supplied status is a permitted status, else default `status` to `pending`.
			// If no `status` was supplied, this will default `status` to `pending`.
			$status = in_array( $status, $permitted ) ? $status : 'pending';

			$entry = new cnEntry();
			$entry->set( $id );

			$entry->setStatus( $status );
			$entry->update();

			switch ( $status ) {

				case 'pending':

					cnMessage::set( 'success', 'form_entry_pending' );
					break;

				case 'approve':

					cnMessage::set( 'success', 'form_entry_approve' );
					break;
			}


		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}

		wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Set the approval status of entries in bulk.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (string) $status The entry status that should be set.
	 * @return (void)
	 */
	public static function setEntryStatusBulk( $status ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'pending', 'approved' );

			if ( ! in_array( $status, $permitted ) ) return;

			// @TODO $_POST['id'] should be a supplied attribute.
			foreach ( $_POST['id'] as $id ) {

				$entry = new cnEntry();

				$entry->set( $id );
				$entry->setStatus( $status );
				$entry->update();
			}

			switch ( $status ) {

				case 'pending':

					cnMessage::set( 'success', 'form_entry_pending_bulk' );
					break;

				case 'approved':

					cnMessage::set( 'success', 'form_entry_approve_bulk' );
					break;
			}

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Set the visibility status of entries in bulk.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (string) $status The entry visibility that should be set.
	 * @return (void)
	 */
	static public function setEntryVisibilityBulk( $visibility ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'public', 'private', 'unlisted' );

			if ( ! in_array( $visibility, $permitted ) ) return;

			// @TODO $_POST['id'] should be a supplied attribute.
			foreach ( $_POST['id'] as $id ) {

				$entry = new cnEntry();

				$entry->set( $id );
				$entry->setVisibility( $visibility );
				$entry->update();
			}

			cnMessage::set( 'success', 'form_entry_visibility_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Delete an entry.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (int) $id [optional] Entry ID.
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function deleteEntry( $id = '' ) {
		global $connections;

		// If no entry ID was supplied, check $_GET.
		$id = empty( $id ) && ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) ? $_GET['id'] : $id;

		check_admin_referer( 'entry_delete_' . $id );

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			$entry = new cnEntry( $connections->retrieve->entry( $id ) );
			$entry->delete( $id );

			cnMessage::set( 'success', 'form_entry_delete' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

		wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Delete entries in bulk.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return (void)
	 */
	public static function deleteEntryBulk() {
		global $connections;

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			// @TODO $POST['id'] should be passed to the method as an attribute.
			if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) return;

			foreach ( $_POST['id'] as $id ) {

				$entry = new cnEntry( $connections->retrieve->entry( $id ) );
				$entry->delete( $id );
			}

			cnMessage::set( 'success', 'form_entry_delete_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

	}

	/**
	 * Process user filteres.
	 *
	 * @access public
	 * @since 0.7.8
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return (void)
	 */
	public static function userFilter() {

		$queryVar = array();

		check_admin_referer( 'filter' );

		self::saveUserFilters();

		/*
		 * Setup the redirect.
		 */

		if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) )
			$queryVar['s'] = urlencode( $_POST['s'] );

		// if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) )
		// 	$queryVar['s'] = urlencode( $_GET['s'] );

		if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) )
			$queryVar['cn-char'] = urlencode( $_GET['cn-char'] );

		/*
		 * Do the redirect.
		 */

		wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( $queryVar, 'admin.php?page=connections_manage' ) ) );

		exit();
	}

	/**
	 * Save user filteres.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return (void)
	 */
	public static function saveUserFilters() {
		global $connections;

		if ( isset( $_POST['entry_type'] ) ) $connections->currentUser->setFilterEntryType( esc_attr( $_POST['entry_type'] ) );
		if ( isset( $_POST['visibility_type'] ) ) $connections->currentUser->setFilterVisibility( esc_attr( $_POST['visibility_type'] ) );

		if ( isset( $_POST['category'] ) && ! empty( $_POST['category'] ) ) $connections->currentUser->setFilterCategory( esc_attr( $_POST['category'] ) );
		if ( isset( $_GET['category'] ) && ! empty( $_GET['category'] ) ) $connections->currentUser->setFilterCategory( esc_attr( $_GET['category'] ) );

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

	/**
	 * Add a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function addCategory() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( $form->getNonce( 'add_category' ), '_cn_wpnonce' );

			$category = new cnCategory();
			$format = new cnFormatting();

			$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
			$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
			$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
			$category->setDescription( $format->sanitizeString( $_POST['category_description'], TRUE ) );

			$category->save();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Update a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function updateCategory() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( $form->getNonce( 'update_category' ), '_cn_wpnonce' );

			$category = new cnCategory();
			$format = new cnFormatting();

			$category->setID( $format->sanitizeString( $_POST['category_id'] ) );
			$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
			$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
			$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
			$category->setDescription( $format->sanitizeString( $_POST['category_description'], TRUE ) );

			$category->update();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Delete a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function deleteCategory() {
		global $connections;

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			$id = esc_attr( $_GET['id'] );
			check_admin_referer( 'category_delete_' . $id );

			$result = $connections->retrieve->category( $id );
			$category = new cnCategory( $result );
			$category->delete();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Bulk category actions.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function categoryManagement() {
		global $connections;

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			$form = new cnFormObjects();

			switch ( $_POST['action'] ) {

				case 'delete':

					check_admin_referer( $form->getNonce( 'bulk_delete_category' ), '_cn_wpnonce' );

					foreach ( (array) $_POST['category'] as $cat_ID ) {

						$cat_ID = esc_attr( $cat_ID );

						$result = $connections->retrieve->category( attribute_escape( $cat_ID ) );
						$category = new cnCategory( $result );
						$category->delete();
					}

					break;
				}

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Install a legacy template.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses unzip_file()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function installTemplate() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

			check_admin_referer( $form->getNonce( 'install_template' ), '_cn_wpnonce' );

			require_once ABSPATH . 'wp-admin/includes/file.php';

			WP_Filesystem();

			if ( unzip_file( $_FILES['template']['tmp_name'], CN_CUSTOM_TEMPLATE_PATH ) ) {
				cnMessage::set( 'success', 'template_installed' );
			} else {
				cnMessage::set( 'error', 'template_install_failed' );
			}

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Activate a template.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function activateTemplate() {
		global $connections;

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

			$templateName = esc_attr( $_GET['template'] );
			check_admin_referer( 'activate_' . $templateName );

			$type = esc_attr( $_GET['type'] );
			$slug = esc_attr( $_GET['template'] );

			$connections->options->setActiveTemplate( $type, $slug );

			$connections->options->saveOptions();

			$connections->setSuccessMessage( 'template_change_active' );

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Delete a template.
	 *
	 * @TODO Move the delete to a generic method in cnFileSystem()
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function deleteTemplate() {

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

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

						} else {

							@unlink( $directory . $file ) or $deleteError = TRUE;
						}

						if ( $deleteError ) return FALSE;
					}
				}

				closedir( $currentDirectory );

				if ( ! rmdir( $directory ) ) return FALSE;

				return TRUE;
			}

			if ( removeDirectory( CN_CUSTOM_TEMPLATE_PATH . '/' . $templateName . '/' ) ) {
				cnMessage::set( 'success', 'template_deleted' );
			} else {
				cnMessage::set( 'error', 'template_delete_failed' );
			}

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Update the role settings.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function updateRoleCapabilities() {
		global $wp_roles;

		$form = new cnFormObjects();

		/*
		 * Check whether user can edit roles
		 */
		if ( current_user_can( 'connections_change_roles' ) ) {

			check_admin_referer( $form->getNonce( 'update_role_settings' ), '_cn_wpnonce' );


			if ( isset( $_POST['roles'] ) ) {

				// Cycle thru each role available because checkboxes do not report a value when not checked.
				foreach ( $wp_roles->get_names() as $role => $name ) {

					if ( ! isset( $_POST['roles'][ $role ] ) ) continue;

					foreach ( $_POST['roles'][ $role ]['capabilities'] as $capability => $grant ) {

						// the admininistrator should always have all capabilities
						if ( $role == 'administrator' ) continue;

						if ( $grant == 'true' ) {
							cnRole::add( $role, $capability );
						} else {
							cnRole::remove( $role, $capability );
						}

					}
				}
			}

			if ( isset( $_POST['reset'] ) ) cnRole::reset( $_POST['reset'] );

			if ( isset( $_POST['reset_all'] ) ) cnRole::reset();

			cnMessage::set( 'success', 'role_settings_updated' );


			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_roles' ) );
			exit();

		} else {

			cnMessage::set( 'error', 'capability_roles' );
		}

	}

}