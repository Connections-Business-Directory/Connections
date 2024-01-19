<?php
/**
 * Commands for managing the Connections Directory.
 *
 * @since      10.4.61
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\API\CLI\Command
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\API\CLI\Command;

use Exception;
use WP_CLI;
use WP_CLI_Command;

/**
 * Perform database table operations.
 *
 * @package Connections_Directory\API\CLI\Command
 */
final class Core extends WP_CLI_Command {

	/**
	 * Register the `tables` wp-cli command.
	 *
	 * @since 10.4.61
	 *
	 * @throws Exception
	 */
	public static function register() {

		WP_CLI::add_command( 'connections_directory core', __CLASS__ );
	}

	/**
	 * Output the current version.
	 *
	 * ## EXAMPLES
	 *
	 *     # Display the version.
	 *     wp connections_directory core version
	 *
	 * @since 10.4.61
	 *
	 * @return void
	 */
	public function version() {

		WP_CLI::log( \Connections_Directory::VERSION );
	}
}
