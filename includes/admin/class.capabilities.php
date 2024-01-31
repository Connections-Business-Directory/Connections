<?php
/**
 * Class for registering and managing the capabilities for Connections.
 *
 * @since 0.7.5
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\cnRole
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnRole
 */
class cnRole extends WP_Roles {

	/**
	 * @since 0.7.5
	 *
	 * @var static Instance of this object.
	 */
	private static $instance;

	/**
	 * Main cnRole Instance.
	 *
	 * Insures that only one instance of cnRole exists at any one time.
	 *
	 * @since 0.7.5
	 *
	 * @return static
	 */
	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {

			/*
			 * Initiate an instance of the class.
			 */
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns an array of the default capabilities.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return (array)
	 */
	public static function capabilities() {

		return array(
			'connections_view_menu'            => __( 'View Admin Menu', 'connections' ),
			'connections_view_dashboard'       => __( 'View Dashboard', 'connections' ),
			'connections_manage'               => __( 'View List (Manage)', 'connections' ),
			'connections_add_entry'            => __( 'Add Entry', 'connections' ),
			'connections_add_entry_moderated'  => __( 'Add Entry Moderated', 'connections' ),
			'connections_edit_entry'           => __( 'Edit Entry', 'connections' ),
			'connections_edit_entry_moderated' => __( 'Edit Entry Moderated', 'connections' ),
			'connections_delete_entry'         => __( 'Delete Entry', 'connections' ),
			'connections_view_public'          => __( 'View Public Entries', 'connections' ),
			'connections_view_private'         => __( 'View Private Entries', 'connections' ),
			'connections_view_unlisted'        => __( 'View Unlisted Entries', 'connections' ),
			'connections_edit_categories'      => __( 'Edit Categories', 'connections' ),
			'connections_change_settings'      => __( 'Change Settings', 'connections' ),
			'connections_manage_template'      => __( 'Manage Templates', 'connections' ),
			'connections_change_roles'         => __( 'Change Role Capabilities', 'connections' ),
		);
	}

	/**
	 * Add a capability to a role.
	 *
	 * @since 0.7.5
	 *
	 * @param string $role The role name.
	 * @param string $cap The capability.
	 * @param bool   $grant Whether to grant the capability to the role or not.
	 */
	public static function add( $role, $cap, $grant = true ) {

		// Bring a copy of this into scope.
		$instance = self::getInstance();

		if ( ! self::hasCapability( $role, $cap ) ) {
			$instance->add_cap( $role, $cap, $grant );
		}
	}

	/**
	 * Remove a capability from a role.
	 *
	 * @since 0.7.5
	 *
	 * @param string $role The role name.
	 * @param string $cap  The capability.
	 */
	public static function remove( $role, $cap ) {

		// Bring a copy of this into scope.
		$instance = self::getInstance();

		if ( self::hasCapability( $role, $cap ) ) {
			$instance->remove_cap( $role, $cap );
		}
	}

	/**
	 * Check whether a role has a capability or not.
	 *
	 * @since 0.7.5
	 *
	 * @param string $role The role name.
	 * @param string $cap  The capability.
	 *
	 * @return bool
	 */
	public static function hasCapability( $role, $cap ) {

		// Bring a copy of this into scope.
		$instance = self::getInstance();

		if ( ! isset( $instance->roles[ $role ] ) ) {
			return false;
		}

		$wp_role = new WP_Role( $role, $instance->roles[ $role ]['capabilities'] );

		return $wp_role->has_cap( $cap );
	}

	/**
	 * Reset all user role capabilities back to their default.
	 * If a roles has been supplied, that role will have its capabilities reset to its defaults.
	 *
	 * @since 0.7.5
	 *
	 * @param array $roles [optional]
	 */
	public static function reset( $roles = array() ) {

		// Bring a copy of this into scope.
		$instance = self::getInstance();

		/**
		 * These are the roles that will default to having full access
		 * to all capabilities. This is to maintain plugin behavior that
		 * existed prior to adding role/capability support.
		 */
		$coreRoles = array( 'administrator', 'editor', 'author' );

		/**
		 * If no roles are supplied to the method to reset; the method
		 * will reset the capabilities of all roles defined.
		 */
		if ( empty( $roles ) ) {
			$roles = $instance->get_names();
		}

		$capabilities = self::capabilities();

		foreach ( $roles as $role => $key ) {

			// If the current role is one of the defined core roles, grant them all capabilities.
			$grant = in_array( $role, $coreRoles ) ? true : false;

			if ( in_array( $role, $coreRoles ) ) {

				foreach ( $capabilities as $cap => $name ) {

					if ( ! self::hasCapability( $role, $cap ) ) {
						$instance->add_cap( $role, $cap, $grant );
					}
				}

			} else {

				foreach ( $capabilities as $cap => $name ) {

					if ( self::hasCapability( $role, $cap ) ) {
						$instance->remove_cap( $role, $cap );
					}

				}

			}

			// Ensure all roles can view public entries.
			$instance->add_cap( $role, 'connections_view_public', true );
		}
	}

	/**
	 * Purge all plugin capabilities.
	 *
	 * @since 0.7.5
	 */
	public static function purge() {

		// Bring a copy of this into scope.
		$instance = self::getInstance();

		$roles = $instance->get_names();

		foreach ( $roles as $role => $key ) {

			foreach ( self::capabilities() as $cap => $name ) {

				if ( self::hasCapability( $role, $cap ) ) {
					$instance->remove_cap( $role, $cap );
				}
			}
		}
	}
}
