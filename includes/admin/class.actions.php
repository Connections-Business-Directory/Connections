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

/**
 * Class cnAdminActions
 */
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
	 * Setup the class, if it has already been initialized, return the initialized instance.
	 *
	 * @access public
	 * @since 0.7.5
	 * @see cnAdminActions()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			self::register();
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
	 * @since  0.7.5
	 *
	 * @uses   add_action()
	 *
	 * @return void
	 */
	private static function register() {

		// Entry Actions
		add_action( 'cn_add_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_update_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_duplicate_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_delete_entry', array( __CLASS__, 'deleteEntry' ) );
		add_action( 'cn_set_status', array( __CLASS__, 'setEntryStatus' ) );

		// Process entry categories.
		add_action( 'cn_process_taxonomy-category', array( __CLASS__, 'processEntryCategory' ), 9, 2 );

		// Entry Meta Action
		add_action( 'cn_process_meta-entry', array( __CLASS__, 'processEntryMeta' ), 9, 2 );

		// Entry Filters
		add_filter( 'cn_set_address', array( 'cnEntry_Action', 'geoCode' ) ); // Geocode the address using Google Geocoding API.

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

		// Term Meta Actions
		add_action( 'cn_delete_term', array( __CLASS__, 'deleteTermMeta' ), 10, 4 );

		// Actions that deal with the system info.
		add_action( 'wp_ajax_download_system_info', array( __CLASS__, 'downloadSystemInfo' ) );
		add_action( 'wp_ajax_email_system_info', array( __CLASS__, 'emailSystemInfo' ) );
		add_action( 'wp_ajax_generate_url', array( __CLASS__, 'generateSystemInfoURL' ) );
		add_action( 'wp_ajax_revoke_url', array( __CLASS__, 'revokeSystemInfoURL' ) );

		// Actions for export/import settings.
		add_action( 'wp_ajax_export_settings', array( __CLASS__, 'downloadSettings' ) );
		add_action( 'wp_ajax_import_settings', array( __CLASS__, 'importSettings' ) );

		// Register the action to delete a single log.
		add_action( 'cn_log_bulk_actions', array( __CLASS__, 'logManagement' ) );
		add_action( 'cn_delete_log', array( __CLASS__, 'deleteLog' ) );
	}

	/**
	 * Run admin actions.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @uses   do_action()
	 *
	 * @return void
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
	 * AJAX callback used to download the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function downloadSystemInfo() {

		check_ajax_referer( 'download_system_info' );

		if ( ! current_user_can( 'install_plugins' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to download system information.', 'connections' ) );
		}

		cnSystem_Info::download();
	}

	/**
	 * AJAX callback to email the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function emailSystemInfo() {

		$form = new cnFormObjects();

		check_ajax_referer( $form->getNonce( 'email_system_info' ), 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {

			wp_send_json( -2 );
		}

		/**
		 * Since email is sent via an ajax request, let's check for the appropriate header.
		 * @link http://davidwalsh.name/detect-ajax
		 */
		if ( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || 'xmlhttprequest' != strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {

			wp_send_json( -3 );
		}

		$user = wp_get_current_user();

		$atts = array(
			'from_email' => $user->user_email,
			'from_name'  => $user->display_name,
			'to_email'   => $_POST['email'],
			'subject'    => $_POST['subject'],
			'message'    => $_POST['message'],
		);

		$response = cnSystem_Info::email( $atts );

		if ( $response ) {

			// Success, send success code.
			wp_send_json( 1 );

		} else {

			/** @var PHPMailer $phpmailer */
			global $phpmailer;

			wp_send_json( $phpmailer->ErrorInfo );
		}
	}

	/**
	 * AJAX callback to create a secret URL for the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function generateSystemInfoURL() {

		if ( ! check_ajax_referer( 'generate_remote_system_info_url', FALSE, FALSE ) ) {

			wp_send_json_error( __( 'Invalid AJAX action or nonce validation failed.', 'connections' ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {

			wp_send_json_error( __( 'You do not have sufficient permissions to perform this action.', 'connections' ) );
		}

		/** @todo need to check the $token is not WP_Error. */
		$token   = cnString::random( 32 );
		$expires = apply_filters( 'cn_system_info_remote_token_expire', DAY_IN_SECONDS * 3 );

		cnCache::set(
			'system_info_remote_token',
			$token,
			$expires,
			'option-cache'
		);

		$url = home_url() . '/?cn-system-info=' . $token;

		wp_send_json_success(
			array(
				'url' => $url,
				'message' => __( 'Secret URL has been created.', 'connections' ),
			)
		);
	}

	/**
	 * AJAX callback to revoke the secret URL for the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function revokeSystemInfoURL() {

		if ( ! check_ajax_referer( 'revoke_remote_system_info_url', FALSE, FALSE ) ) {

			wp_send_json_error( __( 'Invalid AJAX action or nonce validation failed.', 'connections' ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {

			wp_send_json_error( __( 'You do not have sufficient permissions to perform this action.', 'connections' ) );
		}

		cnCache::clear( 'system_info_remote_token', 'option-cache' );

		wp_send_json_success( __( 'Secret URL has been revoked.', 'connections' ) );
	}

	/**
	 * AJAX callback to download the settings in a JSON encoded text file.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function downloadSettings() {

		check_ajax_referer( 'export_settings' );

		if ( ! current_user_can( 'install_plugins' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to export the settings.', 'connections' ) );
		}

		cnSettingsAPI::download();
	}

	/**
	 * AJAX callback to import settings from a JSON encoded text file.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function importSettings() {

		check_ajax_referer( 'import_settings' );

		if ( ! current_user_can( 'install_plugins' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to import the settings.', 'connections' ) );
		}

		if ( 'json' != pathinfo( $_FILES['import_file']['name'], PATHINFO_EXTENSION ) ) {

			wp_send_json( __( 'Please upload a .json file.', 'connections' ) );
		}

		$file = $_FILES['import_file']['tmp_name'];

		if ( empty( $file ) ) {

			wp_send_json( __( 'Please select a file to import.', 'connections' ) );
		}

		$json   = file_get_contents( $file );
		$result = cnSettingsAPI::import( $json );

		if ( TRUE === $result ) {

			wp_send_json( __( 'Settings have been imported.', 'connections' ) );

		} else {

			wp_send_json( $result );
		}
	}

	/**
	 * Process controller for action taken by the user.
	 *
	 * @access private
	 * @since  0.7.8
	 *
	 * @uses   wp_redirect()
	 * @uses   get_admin_url()
	 * @uses   get_current_blog_id()
	 *
	 * @return void
	 */
	public static function entryManagement() {

		$form = new cnFormObjects();
		$queryVar = array();

		check_admin_referer( $form->getNonce( 'cn_manage_actions' ), '_cn_wpnonce' );

		/*
		 * Run user requested actions.
		 */

		// Process user selected filters
		self::saveUserFilters();

		// Grab the bulk action requested by user.
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

				/* None, blank intentionally. */

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
	 * @since  0.7.8
	 *
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @return void
	 */
	public static function processEntry() {

		$form  = new cnFormObjects();

		$action = isset( $_GET['cn-action'] ) ? $_GET['cn-action'] : $_POST['cn-action'];

		// Setup the redirect URL.
		$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_add';

		switch ( $action ) {

			case 'add_entry':

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'add_entry' ), '_cn_wpnonce' );

					cnEntry_Action::add( $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'copy_entry':

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'add_entry' ), '_cn_wpnonce' );

					cnEntry_Action::copy( $_GET['id'], $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'update_entry':

				// Setup the redirect URL.
				$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_manage';

				/*
				 * Check whether the current user can edit an entry.
				 */
				if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'update_entry' ), '_cn_wpnonce' );

					cnEntry_Action::update( $_GET['id'], $_POST );

				} else {

					cnMessage::set( 'error', 'capability_edit' );
				}

				break;
		}

		// do_action( 'cn_process_meta-entry', $action, $id );
		// do_action( 'cn_process_meta-entry-' . $action, $action, $id );

		wp_redirect( get_admin_url( get_current_blog_id(), $redirect) );

		exit();
	}

	/**
	 * Add, update or delete the entry categories.
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $action The action to being performed to an entry.
	 * @param  int    $id     The entry ID.
	 *
	 * @return void
	 */
	public static function processEntryCategory( $action, $id ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * Save the entry category(ies). If none were checked, send an empty array
		 * which will add the entry to the default category.
		 */
		if ( isset( $_POST['entry_category'] ) && ! empty( $_POST['entry_category'] ) ) {

			$instance->term->setTermRelationships( $id, $_POST['entry_category'], 'category' );

		} else {

			$default = get_option( 'cn_default_category' );

			$instance->term->setTermRelationships( $id, $default, 'category' );
		}

	}

	/**
	 * Add, update or delete the entry meta data.
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $action The action to being performed to an entry.
	 * @param  int    $id     The entry ID.
	 *
	 * @return mixed          array | bool  An array of meta IDs or FALSE on failure.
	 */
	public static function processEntryMeta( $action, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! $id = absint( $id ) ) return FALSE;

		$meta       = array();
		$newmeta    = array();
		$metaSelect = array();
		$metaIDs    = array();

		switch ( $action ) {

			case 'add':

				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) {

					foreach ( $_POST['newmeta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						$newmeta[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}
				}

				if ( isset( $_POST['metakeyselect'] ) && $_POST['metakeyselect'] !== '-1' ) {

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] );
				}

				$metaIDs['added'] = array_merge( $newmeta, $metaSelect );

				break;

			case 'copy':

				// Copy any meta associated with the source entry to the new entry.
				if ( isset( $_POST['meta'] ) || ! empty( $_POST['meta'] ) ) {

					foreach ( $_POST['meta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						// Add the meta except for those that the user deleted for this entry.
						if ( $row['value'] !== '::DELETED::' ) $meta[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}
				}

				// Lastly, add any new meta the user may have added.
				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) {

					foreach ( $_POST['newmeta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						$metaIDs[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}

					// $newmeta = cnMeta::add( 'entry', $id, $_POST['newmeta']['0']['key'], $_POST['newmeta']['99']['value'] );
				}

				if ( isset( $_POST['metakeyselect'] ) && $_POST['metakeyselect'] !== '-1' ) {

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] );
				}

				$metaIDs['added'] = array_merge( $meta, $newmeta, $metaSelect );

				break;

			case 'update':

				// Query the meta associated to the entry.
				//$results = cnMeta::get( 'entry', $id );
				$results =  $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, entry_id
							FROM " . CN_ENTRY_TABLE_META . " WHERE entry_id = %d
							ORDER BY meta_key,meta_id", $id ), ARRAY_A );

				if ( $results !== FALSE ) {

					// Loop thru $results removing any custom meta fields. Custom meta fields are considered to be private.
					foreach ( $results as $metaID => $row ) {

						if ( cnMeta::isPrivate( $row['meta_key'] ) ) unset( $results[ $row['meta_id'] ] );
					}

					// Loop thru the associated meta and update any that may have been changed.
					// If the meta id doesn't exist in the $_POST data, assume the user deleted it.
					foreach ( $results as $metaID => $row ) {

						// Update the entry meta if it differs.
						if ( ( isset( $_POST['meta'][ $row['meta_id'] ]['value'] ) && $_POST['meta'][ $row['meta_id'] ]['value'] !== $row['meta_value'] ) ||
							 ( isset( $_POST['meta'][ $row['meta_id'] ]['key'] )   && $_POST['meta'][ $row['meta_id'] ]['key']   !== $row['meta_key']   ) &&
							 ( $_POST['meta'][ $row['meta_id'] ]['value'] !== '::DELETED::' ) ) {

							// If the key begins with an underscore, remove it because those are private.
							//if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

							//cnMeta::update( 'entry', $id, $_POST['meta'][ $row['meta_id'] ]['key'], $_POST['meta'][ $row['meta_id'] ]['value'], $row['meta_value'], $row['meta_key'], $row['meta_id'] );
							cnMeta::updateByID( 'entry', $row['meta_id'], $_POST['meta'][ $row['meta_id'] ]['value'], $_POST['meta'][ $row['meta_id'] ]['key'] );

							$metaIDs['updated'] = $row['meta_id'];
						}

						if ( isset( $_POST['meta'][ $row['meta_id'] ]['value'] ) && $_POST['meta'][ $row['meta_id'] ]['value'] === '::DELETED::' ) {

							// Record entry meta to be deleted.
							cnMeta::deleteByID( 'entry', $row['meta_id'] );

							$metaIDs['deleted'] = $row['meta_id'];
						}

					}
				}

				// Lastly, add any new meta the user may have added.
				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) {

					foreach ( $_POST['newmeta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						$metaIDs[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}

					// $newmeta = cnMeta::add( 'entry', $id, $_POST['newmeta']['0']['key'], $_POST['newmeta']['99']['value'] );
				}

				if ( isset( $_POST['metakeyselect'] ) && $_POST['metakeyselect'] !== '-1' ) {

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] );
				}

				$metaIDs['added'] = array_merge( $newmeta, $metaSelect );

				break;
		}

		return $metaIDs;
	}

	/**
	 * Set the entry status to pending or approved.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @param int $id [optional] Entry ID.
	 * @param string $status [optional] The entry status to be assigned.
	 *
	 * @return void
	 */
	public static function setEntryStatus( $id = 0, $status = '' ) {

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

			cnEntry_Action::status( $status, $id );

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
	 * @since  0.7.8
	 *
	 * @param  string $status The entry status that should be set.
	 *
	 * @return void
	 */
	public static function setEntryStatusBulk( $status ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'pending', 'approved' );

			if ( ! in_array( $status, $permitted ) ) return;

			cnEntry_Action::status( $status, $_POST['id'] );

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
	 * @since  0.7.8
	 *
	 * @param  string $visibility The entry visibility that should be set.
	 *
	 * @return void
	 */
	static public function setEntryVisibilityBulk( $visibility ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'public', 'private', 'unlisted' );

			if ( ! in_array( $visibility, $permitted ) ) return;

			cnEntry_Action::visibility( $visibility, $_POST['id'] );

			cnMessage::set( 'success', 'form_entry_visibility_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Delete an entry.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @param int $id [optional] Entry ID.
	 *
	 * @return void
	 */
	public static function deleteEntry( $id = 0 ) {

		// If no entry ID was supplied, check $_GET.
		$id = empty( $id ) && ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) ? $_GET['id'] : $id;

		check_admin_referer( 'entry_delete_' . $id );

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			cnEntry_Action::delete( $id );

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
	 * @since  0.7.8
	 *
	 * @return void
	 */
	public static function deleteEntryBulk() {

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			// @TODO $POST['id'] should be passed to the method as an attribute.
			if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) return;

			cnEntry_Action::delete( $_POST['id'] );

			cnMessage::set( 'success', 'form_entry_delete_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

	}

	/**
	 * Process user filters.
	 *
	 * @access public
	 * @since 0.7.8
	 *
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @return void
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
	 * @since  0.7.8
	 *
	 * @return void
	 */
	public static function saveUserFilters() {

		/** @var connectionsLoad $connections */
		global $connections;

		// Set the moderation filter for the current user if set in the query string.
		if ( isset( $_GET['status'] ) ) $connections->currentUser->setFilterStatus( $_GET['status'] );

		if ( isset( $_POST['entry_type'] ) ) $connections->currentUser->setFilterEntryType( esc_attr( $_POST['entry_type'] ) );
		if ( isset( $_POST['visibility_type'] ) ) $connections->currentUser->setFilterVisibility( esc_attr( $_POST['visibility_type'] ) );

		if ( isset( $_POST['category'] ) /*&& ! empty( $_POST['category'] )*/ ) $connections->currentUser->setFilterCategory( absint( $_POST['category'] ) );
		if ( isset( $_GET['category'] ) /*&& ! empty( $_GET['category'] )*/ ) $connections->currentUser->setFilterCategory( absint( $_GET['category'] ) );

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
			$format   = new cnFormatting();

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
			$format   = new cnFormatting();

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
	 * Callback to delete the term meta when when a term is deleted.
	 *
	 * @access private
	 * @since  8.2
	 * @static
	 *
	 * @param int    $term          Term ID.
	 * @param int    $tt_id         Term taxonomy ID.
	 * @param string $taxonomy      Taxonomy slug.
	 * @param mixed  $deleted_term  Copy of the already-deleted term, in the form specified
	 *                              by the parent function. WP_Error otherwise.
	 */
	public static function deleteTermMeta( $term, $tt_id, $taxonomy, $deleted_term ) {

		if ( ! is_wp_error( $deleted_term ) ) {

			$meta = cnMeta::get( 'term', $term );

			if ( ! empty( $meta ) ) {

				foreach ( $meta as $key => $value ) {

					cnMeta::delete( 'term', $term, $key );
				}
			}
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

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			switch ( $_POST['action'] ) {

				case 'delete':

					check_admin_referer( 'bulk-terms' );

					foreach ( (array) $_POST['category'] as $id ) {

						$result = $instance->retrieve->category( absint( $id ) );
						$category = new cnCategory( $result );
						$category->delete();
					}

					break;
				}

			$url = get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' );

			if ( isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ) {

				$page = absint( $_REQUEST['paged'] );

				$url = add_query_arg( array( 'paged' => $page ) , $url);
			}

			wp_redirect( $url );

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

		/** @var $connections connectionsLoad */
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

			cnMessage::set( 'success', 'template_change_active' );

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

		/** @var $wp_roles WP_Roles */
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

						// the administrator should always have all capabilities
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

	/**
	 * Callback for the cn_log_bulk_actions hook which processes the action and then redirects back to the current admin page.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   current_user_can()
	 * @uses   check_admin_referer()
	 * @uses   cnLog::delete()
	 * @uses   cnMessage::set()
	 * @uses   add_query_arg()
	 * @uses   wp_get_referer()
	 * @uses   wp_safe_redirect()
	 */
	public static function logManagement() {

		$action = '';

		if ( current_user_can( 'install_plugins' ) ) {

			if ( isset( $_GET['action'] ) && '-1' !== $_GET['action'] ) {

				$action = $_GET['action'];

			} elseif ( isset( $_GET['action2'] ) && '-1' !== $_GET['action2'] ) {

				$action = $_GET['action2'];

			}

			switch ( $action ) {

				case 'delete':

					check_admin_referer( 'bulk-email' );

					foreach ( $_GET['log'] as $id ) {

						cnLog::delete( $id );
					}

					cnMessage::set( 'success', 'log_bulk_delete' );

					break;
			}

		} else {

			cnMessage::set( 'error', 'capability_manage_logs' );
		}

		$url = add_query_arg(
			array(
				'type'      => isset( $_GET['type'] ) && ! empty( $_GET['type'] ) && '-1' !== $_GET['type'] ? $_GET['type'] : FALSE,
				'cn-action' => FALSE,
				'action'    => FALSE,
				'action2'   => FALSE,
			),
			wp_get_referer()
		);

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Callback for the cn_delete_log hook which processes the delete action and then redirects back to the current admin page.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   current_user_can()
	 * @uses   check_admin_referer()
	 * @uses   cnLog::delete()
	 * @uses   cnMessage::set()
	 * @uses   add_query_arg()
	 * @uses   wp_get_referer()
	 * @uses   wp_safe_redirect()
	 */
	public static function deleteLog() {

		if ( current_user_can( 'install_plugins' ) ) {

			$id = 0;

			if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {

				$id = absint( $_GET['id'] );
			}

			check_admin_referer( 'log_delete_' . $id );

			cnLog::delete( $id );

			cnMessage::set( 'success', 'log_delete' );

			$url = add_query_arg(
				array(
					'type' => isset( $_GET['type'] ) && ! empty( $_GET['type'] ) && '-1' !== $_GET['type'] ? $_GET['type'] : FALSE,
				),
				wp_get_referer()
			);

			wp_safe_redirect( $url );
			exit();
		}
	}

}
