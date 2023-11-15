<?php
/**
 * Integrate with the Simple History plugin.
 *
 * @link https://wordpress.org/plugins/simple-history/
 *
 * @since 10.4.53
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory\Integration
 * @subpackage Connections_Directory\Integration\Simple_History
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Integration;

use Connections_Directory\Integration\Simple_History\Entry_Logger;
use Connections_Directory\Integration\Simple_History\Term_Logger;

/**
 * Class Simple_History
 *
 * @package Connections_Directory\Integration
 */
final class Simple_History {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 10.4.53
	 *
	 * @var static
	 */
	private static $instance;

	/**
	 * Object constructor.
	 *
	 * @since 10.4.53
	 */
	private function __construct() {
		/* Do nothing here */
	}

	/**
	 * Initiate the Simple History object.
	 *
	 * @since 10.4.53
	 *
	 * @return static
	 */
	public static function init(): Simple_History {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			$self = new self();

			$self->hooks();

			self::$instance = $self;
		}

		return self::$instance;
	}

	/**
	 * Register the hooks.
	 *
	 * @since 10.4.53
	 *
	 * @return void
	 */
	private function hooks() {

		/*
		 * Ensure that the `\Simple_History\Simple_History` class exist before adding its hook.
		 * The reason is that Simple History changed its namespace in 4.0 from `SimpleHistory`
		 * to `\Simple_History\Simple_History`. If a user is using a version older than 4.0,
		 * this will cause a fatal error.
		 */
		if ( class_exists( '\Simple_History\Simple_History' ) ) {

			add_action( 'simple_history/add_custom_logger', array( $this, 'registerLoggers' ) );
		}
	}

	/**
	 * Callback for the `simple_history/add_custom_logger` action hook.
	 *
	 * Register the custom logger.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param \Simple_History\Simple_History $simple_history Simple History instance.
	 *
	 * @return void
	 */
	public function registerLoggers( \Simple_History\Simple_History $simple_history ) {

		if ( ! method_exists( $simple_history, 'register_logger' ) ) {

			return;
		}

		$simple_history->register_logger( Entry_Logger::class );
		$simple_history->register_logger( Term_Logger::class );
	}
}
