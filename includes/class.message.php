<?php

/**
 * Class for registering and displaying action/error messages.
 *
 * @package     Connections
 * @subpackage  Messages
 * @extends		WP_Error
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnMessage extends WP_Error {

	/**
	 * @access private
	 * @since 0.7.5
	 * @var (object) cnMessage stores an instance of this class.
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent class from being loaded more than once.
	 *
	 * @access private
	 * @since 0.7.5
	 * @see cnMessage::getInstance()
	 * @see cnMessage();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Main cnMessage Instance.
	 *
	 * Insures that only one instance of cnMessage exists at any one time.
	 *
	 * @access public
	 * @since 0.7.5
	 * @return object cnMessage
	 */
	public static function getInstance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Add all the predefined action/error messages to the WP_Error class.
	 *
	 * @since 0.7.5
	 * @return void
	 */
	public function init() {

		/**
		 * Add the error messages.
		 */
		$this->add( 'capability_view_entry_list', __( 'You are not authorized to view the entry list. Please contact the admin if you received this message in error.', 'connections' ) );

		$this->add( 'capability_add', __( 'You are not authorized to add entries. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'capability_delete', __( 'You are not authorized to delete entries. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'capability_edit', __( 'You are not authorized to edit entries. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'capability_categories', __( 'You are not authorized to edit the categories. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'capability_settings', __( 'You are not authorized to edit the settings. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'capability_roles', __( 'You are not authorized to edit role capabilities. Please contact the admin if you received this message in error.', 'connections' ) );

		$this->add( 'category_duplicate_name', __( 'The category you are trying to create already exists.', 'connections' ) );
		$this->add( 'category_self_parent', __( 'Category can not be a parent of itself.', 'connections' ) );
		$this->add( 'category_delete_uncategorized', __( 'The Uncategorized category can not be deleted.', 'connections' ) );
		$this->add( 'category_update_uncategorized', __( 'The Uncategorized category can not be altered.', 'connections' ) );
		$this->add( 'category_add_uncategorized', __( 'The Uncategorized category already exists.', 'connections' ) );
		$this->add( 'category_add_failed', __( 'Failed to add category.', 'connections' ) );
		$this->add( 'category_update_failed', __( 'Failed to update category.', 'connections' ) );
		$this->add( 'category_delete_failed', __( 'Failed to delete category.', 'connections' ) );

		$this->add( 'entry_added_failed', __( 'Entry could not be added.', 'connections' ) );
		$this->add( 'entry_updated_failed', __( 'Entry could not be updated.', 'connections' ) );

		$this->add( 'entry_preferred_overridden_address', __( 'Your preferred setting for a address was overridden because another address that you are not permitted to view or edit is set as the preferred address. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'entry_preferred_overridden_phone', __( 'Your preferred setting for a phone was overridden because another phone number that you are not permitted to view or edit is set as the preferred phone number. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'entry_preferred_overridden_email', __( 'Your preferred setting for an email address was overridden because another email address that you are not permitted to view or edit is set as the preferred email address. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'entry_preferred_overridden_im', __( 'Your preferred setting for a IM Network was overridden because another IM Network that you are not permitted to view or edit is set as the preferred IM Network. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'entry_preferred_overridden_social', __( 'Your preferred setting for a social media network was overridden because another social media network that you are not permitted to view or edit is set as the preferred social media network. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'entry_preferred_overridden_link', __( 'Your preferred setting for a link was overridden because another link that you are not permitted to view or edit is set as the preferred link. Please contact the admin if you received this message in error.', 'connections' ) );
		$this->add( 'entry_preferred_overridden_date', __( 'Your preferred setting for a date was overridden because another date that you are not permitted to view or edit is set as the preferred date. Please contact the admin if you received this message in error.', 'connections' ) );

		$this->add( 'image_upload_failed', __( 'Image upload failed.', 'connections' ) );
		$this->add( 'image_uploaded_failed', __( 'Uploaded image could not be saved to the destination folder.', 'connections' ) );
		$this->add( 'image_profile_failed', __( 'Profile image could not be created and/or saved to the destination folder.', 'connections' ) );
		$this->add( 'image_entry_failed', __( 'Entry image could not be created and/or saved to the destination folder.', 'connections' ) );
		$this->add( 'image_thumbnail_failed', __( 'Thumbnail image could not be created and/or saved to the destination folder.', 'connections' ) );

		$this->add( 'template_install_failed', __( 'The template installation has failed.', 'connections' ) );
		$this->add( 'template_delete_failed', __( 'The template could not be deleted.', 'connections' ) );

		$this->add( 'image_path_exists_failed', __( 'Path ../wp-content/connection_images does not seem to exist. Please try deactivating and reactivating Connections.', 'connections' ) );
		$this->add( 'image_path_writeable_failed', __( 'Path ../wp-content/connection_images does not seem to be writeable.', 'connections' ) );

		$this->add( 'template_path_exists_failed', __( 'Path ../wp-content/connections_templates does not seem to exist. Please try deactivating and reactivating Connections.', 'connections' ) );
		$this->add( 'template_path_writeable_failed', __( 'Path ../wp-content/connections_templates does not seem to be writeable.', 'connections' ) );

		$this->add( 'cache_path_exists_failed', __( 'Path ../wp-content/plugins/connections/cache does not seem to exist. Please try deactivating and reactivating Connections.', 'connections' ) );
		$this->add( 'cache_path_writeable_failed', __( 'Path ../wp-content/plugins/connections/cache does not seem to be writeable.', 'connections' ) );

		$this->add( 'home_page_set_failed', __( 'The Connections directory home page has not been set. Please set it now on the Connections : Settings page under the General tab.', 'connections' ) );

		/**
		 * Add the success messages.
		 */
		$this->add( 'form_entry_delete', __( 'The entry has been deleted.', 'connections' ) );
		$this->add( 'form_entry_delete_bulk', __( 'Entry(ies) have been deleted.', 'connections' ) );
		$this->add( 'form_entry_pending', __( 'The entry status have been set to pending.', 'connections' ) );
		$this->add( 'form_entry_pending_bulk', __( 'Entry(ies) status have been set to pending.', 'connections' ) );
		$this->add( 'form_entry_approve', __( 'The entry has been approved.', 'connections' ) );
		$this->add( 'form_entry_approve_bulk', __( 'Entry(ies) have been approved.', 'connections' ) );
		$this->add( 'form_entry_visibility_bulk', __( 'Entry(ies) visibility have been updated.', 'connections' ) );

		$this->add( 'category_deleted', __( 'Category(ies) have been deleted.', 'connections' ) );
		$this->add( 'category_updated', __( 'Category has been updated.', 'connections' ) );
		$this->add( 'category_added', __( 'Category has been added.', 'connections' ) );

		$this->add( 'entry_added', __( 'Entry has been added.', 'connections' ) );
		$this->add( 'entry_added_moderated', __( 'Pending review entry will be added.', 'connections' ) );
		$this->add( 'entry_updated', __( 'Entry has been updated.', 'connections' ) );
		$this->add( 'entry_updated_moderated', __( 'Pending review entry will be updated.', 'connections' ) );

		$this->add( 'image_uploaded', __( 'Uploaded image saved.', 'connections' ) );
		$this->add( 'image_profile', __( 'Profile image created and saved.', 'connections' ) );
		$this->add( 'image_entry', __( 'Entry image created and saved.', 'connections' ) );
		$this->add( 'image_thumbnail', __( 'Thumbnail image created and saved.', 'connections' ) );

		$this->add( 'role_settings_updated', __( 'Role capabilities have been updated.', 'connections' ) );

		$this->add( 'template_change_active', __( 'The default active template has been changed.', 'connections' ) );
		$this->add( 'template_installed', __( 'A new template has been installed.', 'connections' ) );
		$this->add( 'template_deleted', __( 'The template has been deleted.', 'connections' ) );

		/*
		 * DB update message.
		 */
		$this->add( 'db_update_required', __( 'Connections database requires updating.', 'connections' ) . ' ' . '<a class=\"button\" href=\"admin.php?page=connections_manage\">' . __( 'START', 'connections' )  . '</a>' );
	}

	/**
	 * Display the stored action/error messages.
	 *
	 * @access public
	 * @since 0.7.5
	 * @return (string) The action/error message created to match the admin notices style.
	 */
	public static function display() {
		global $connections;

		// Bring a copy of this into scope.
		$instance = self::getInstance();

		$output = '';

		$messages = $connections->currentUser->getMessages();

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $message ) {
				foreach ( $message as $type => $code ) {
					switch ( $type ) {
						case 'error':
							$output .= '<div id="message" class="error"><p><strong>' . __( 'ERROR', 'connections' ) . ': </strong>' . $instance->get_error_message( $code ) . '</p></div>';
							break;

						case 'error_runtime':
							$output .= '<div id="message" class="error"><p><strong>' . __( 'ERROR', 'connections' ) . ': </strong>' . $code . '</p></div>';
							break;

						case 'success':
							$output .= '<div id="message" class="updated fade"><p><strong>' . __( 'SUCCESS', 'connections' ) . ': </strong>' . $instance->get_error_message( $code ) . '</p></div>';
							break;

						case 'success_runtime':
							$output .= '<div id="message" class="updated fade"><p><strong>' . __( 'SUCCESS', 'connections' ) . ': </strong>' . $code . '</p></div>';
							break;
					}
				}
			}
		}

		$connections->currentUser->resetMessages();

		echo $output;
	}

	/**
	 * Create an admin message adding to the admin_notices action hook.
	 *
	 * @access public
	 * @since 0.7.5
	 * @param  (string) $type The $type must be either "error" or "success".
	 * @param  (string) $message The message to be displayed. || A message code registered in self::init().
	 * @return void
	 */
	public static function create( $type , $message ) {

		// Bring a copy of this into scope.
		$instance = self::getInstance();

		// Check to see if $message is one of the registered message codes and if it is, set $message to the actual message rather than the message code.
		if ( 0 < strlen( $instance->get_error_message( $message ) ) ) $message = $instance->get_error_message( $message );

		switch ( $type ) {
			case 'error':
				add_action( 'admin_notices' , create_function( '' , 'echo "<div id=\"message\" class=\"error\"><p><strong>' . __( 'ERROR', 'connections' ) . ': </strong>' . $message . '</p></div>";' ) );
				break;

			case 'success':
				add_action( 'admin_notices' , create_function( '' , 'echo "<div id=\"message\" class=\"updated fade\"><p><strong>' . __( 'SUCCESS', 'connections' ) . ': </strong>' . $message . '</p></div>";' ) );
				break;
		}

	}

	/**
	 * Display an action/error message.
	 *
	 * @access public
	 * @since 0.7.5
	 * @return (string) The action/error message created to match the admin notices style.
	 */
	public static function render( $type, $message ) {

		switch ( $type ) {
			case 'error':
				echo '<div id="message" class="error"><p><strong>' . __( 'ERROR', 'connections' ) . ': </strong>' . $message . '</p></div>';
				break;

			case 'success':
				echo '<div id="message" class="updated fade"><p><strong>' . __( 'SUCCESS', 'connections' ) . ': </strong>' . $message . '</p></div>';
				break;
		}

	}

	/**
	 * Store a custom action/error message.
	 *
	 * @access public
	 * @since 0.7.5
	 * @param  (string) $type The $type must be either "error_runtime" or "success_runtime".
	 * @param  (string) $message The message to be stored.
	 * @return void
	 */
	public static function runtime( $type , $message ) {
		global $connections;

		$connections->currentUser->setMessage( array( $type => $message ) );
	}

	/**
	 * Store a predefined action/error message.
	 *
	 * @access public
	 * @since 0.7.5
	 * @param (string) $type The $type must be either "error" or "success".
	 * @param (string) $code The message code as registered in the constructor.
	 * @return void
	 */
	public static function set( $type , $code ) {
		global $connections;

		$messages = $connections->currentUser->getMessages();

		switch ( $type ) {
			case 'error':
				// If the error message is already stored, no need to store it twice.
				if ( ! in_array( array( 'error' => $code ) , $messages ) ) $connections->currentUser->setMessage( array( 'error' => $code ) );
				break;

			case 'success':
				// If the success message is slready stored, no need to store it twice.
				if ( ! in_array( array( 'success' => $code ) , $messages ) ) $connections->currentUser->setMessage( array( 'success' => $code ) );
				break;
		}

	}

}