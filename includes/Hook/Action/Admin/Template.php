<?php
/**
 * Activate or delete an installed template.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Hook\Action\Admin
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Action\Admin;

use cnMessage;
use Connections_Directory\Request;

/**
 * Class Template
 *
 * @package Connections_Directory\Hook\Action\Admin
 */
final class Template {

	/**
	 * The template slug.
	 *
	 * @since 10.4.31
	 * @var string
	 */
	private $slug;

	/**
	 * The template type.
	 *
	 * @since 10.4.31
	 * @var string
	 */
	private $type;

	/**
	 * Constructor
	 *
	 * @since 10.4.31
	 */
	public function __construct() {

		$this->parseRequest();
	}

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.31
	 */
	public static function register() {

		add_action( 'cn_activate_template', array( __CLASS__, 'activate' ) );
		add_action( 'cn_delete_template', array( __CLASS__, 'delete' ) );
	}

	/**
	 * Get the validated and sanitize request variables.
	 *
	 * @since 10.4.31
	 */
	private function parseRequest() {

		$request = Request\Template::input()->value();

		$this->slug = $request['template'];
		$this->type = $request['type'];
	}

	/**
	 * Callback for the `cn_activate_template` action.
	 *
	 * Activate a template.
	 *
	 * @internal
	 * @since 10.4.31
	 */
	public static function activate() {

		$action = new self();

		if ( $action->isValid( 'activate' ) ) {

			$instance = Connections_Directory();

			$instance->options->setActiveTemplate( $action->type, $action->slug );
			$instance->options->saveOptions();

			$action->deleteTransient();

			cnMessage::set( 'success', __( 'The default active template has been changed.', 'connections' ) );

		} else {

			cnMessage::set( 'error', __( 'Failed to change the default active template.', 'connections' ) );
		}

		$action->redirect();
	}

	/**
	 * Callback for the `cn_delete_template` action.
	 *
	 * Delete a template.
	 *
	 * @internal
	 * @since 10.4.31
	 */
	public static function delete() {

		$action = new self();

		if ( $action->isValid( 'delete' ) ) {

			if ( $action->deleteFolder( CN_CUSTOM_TEMPLATE_PATH . '/' . $action->slug . '/' ) ) {
				cnMessage::set( 'success', __( 'The template has been deleted.', 'connections' ) );
			} else {
				cnMessage::set( 'error', __( 'The template could not be deleted.', 'connections' ) );
			}

			$action->deleteTransient();

		} else {

			cnMessage::set( 'error', __( 'The template could not be deleted due to invalid request.', 'connections' ) );
		}

		$action->redirect();
	}

	/**
	 * Delete a folder.
	 *
	 * @since 10.4.31
	 *
	 * @param string $directory The path to delete.
	 *
	 * @return bool
	 */
	private function deleteFolder( $directory ) {

		$deleteError      = false;
		$currentDirectory = opendir( $directory );

		while ( ( $file = readdir( $currentDirectory ) ) !== false ) {

			if ( '.' !== $file && '..' !== $file ) {

				chmod( $directory . $file, 0777 ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.chmod_chmod

				if ( is_dir( $directory . $file ) ) {

					chdir( '.' );
					$this->deleteFolder( $directory . $file . '/' );
					rmdir( $directory . $file ) || $deleteError = true; // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir

				} else {

					@unlink( $directory . $file ) || $deleteError = true;
				}

				if ( $deleteError ) {

					return false;
				}
			}
		}

		closedir( $currentDirectory );

		if ( ! rmdir( $directory ) ) { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir

			return false;
		}

		return true;
	}

	/**
	 * Delete the legacy template transient.
	 *
	 * @since 10.4.31
	 */
	private function deleteTransient() {

		delete_transient( 'cn_legacy_templates' );
	}

	/**
	 * Whether the current user has the required role capability and
	 * that the request nonce is valid.
	 *
	 * @since 10.4.31
	 *
	 * @param string $action Nonce action name.
	 *
	 * @return bool
	 */
	private function isValid( $action ) {

		return current_user_can( 'connections_manage_template' ) &&
			   Request\Nonce::input( $action, $this->slug )->isValid();
	}

	/**
	 * Redirect back to admin page.
	 *
	 * @since  10.4.31
	 */
	private function redirect() {

		wp_safe_redirect(
			get_admin_url(
				get_current_blog_id(),
				add_query_arg(
					array(
						'type' => $this->type,
					),
					'admin.php?page=connections_templates'
				)
			)
		);

		exit();
	}
}
