<?php
/**
 * Polyfill for the `wp_get_environment_type`.
 *
 * @since 10.4.36
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Polyfill
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

if ( ! function_exists( 'wp_get_environment_type' ) ) :
	/**
	 * Retrieves the current environment type.
	 *
	 * NOTE: This function was added in WP 5.5. Add to be backwards compatible with previous version of WordPress.
	 *
	 * The type can be set via the `WP_ENVIRONMENT_TYPE` global system variable,
	 * or a constant of the same name.
	 *
	 * Possible values are 'local', 'development', 'staging', and 'production'.
	 * If not set, the type defaults to 'production'.
	 *
	 * @since 10.3
	 *
	 * @return string The current environment type.
	 */
	function wp_get_environment_type() {
		static $current_env = '';

		if ( $current_env ) {
			return $current_env;
		}

		$wp_environments = array(
			'local',
			'development',
			'staging',
			'production',
		);

		// Add a note about the deprecated WP_ENVIRONMENT_TYPES constant.
		if ( defined( 'WP_ENVIRONMENT_TYPES' ) && function_exists( '_deprecated_argument' ) ) {
			if ( function_exists( '__' ) ) {
				/* translators: %s: WP_ENVIRONMENT_TYPES */
				$message = sprintf( __( 'The %s constant is no longer supported.', 'connections' ), 'WP_ENVIRONMENT_TYPES' );
			} else {
				$message = sprintf( 'The %s constant is no longer supported.', 'WP_ENVIRONMENT_TYPES' );
			}

			_deprecated_argument(
				'define()',
				'5.5.1',
				esc_html( $message )
			);
		}

		// Check if the environment variable has been set, if `getenv` is available on the system.
		if ( function_exists( 'getenv' ) ) {
			$has_env = getenv( 'WP_ENVIRONMENT_TYPE' );
			if ( false !== $has_env ) {
				$current_env = $has_env;
			}
		}

		// Fetch the environment from a constant, this overrides the global system variable.
		if ( defined( 'WP_ENVIRONMENT_TYPE' ) ) {
			$current_env = WP_ENVIRONMENT_TYPE;
		}

		// Make sure the environment is an allowed one, and not accidentally set to an invalid value.
		if ( ! in_array( $current_env, $wp_environments, true ) ) {
			$current_env = 'production';
		}

		return $current_env;
	}
endif;
