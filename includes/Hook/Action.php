<?php
/**
 * Run `cn_` namespaced actions.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Hook
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook;

/**
 * Class Action
 *
 * @package Connections_Directory\Hook
 */
final class Action {

	/**
	 * Run admin actions.
	 *
	 * @since 0.7.5
	 * @internal
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 * @phpcs:disable WordPress.Security.NonceVerification.Recommended
	 */
	public static function run() {

		if ( isset( $_POST['cn-action'] ) ) {

			do_action( 'cn_' . sanitize_key( $_POST['cn-action'] ) );
		}

		if ( isset( $_GET['cn-action'] ) ) {

			do_action( 'cn_' . sanitize_key( $_GET['cn-action'] ) );
		}
	}
	// phpcs:enable
}
