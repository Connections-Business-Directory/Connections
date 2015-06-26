<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Provides a stateless log by extending WP_Error.
 * For a stateful log @see cnLog.
 *
 * @package     Connections
 * @subpackage  Statelerss Log API
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */
class cnLog_Stateless extends WP_Error {

	/**
	 * @var float
	 */
	private $startTime = 0;

	/**
	 * @var float
	 */
	private $lastBenchTime = 0;

	/**
	 * Add an error or append additional message to an existing error.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param int|string $code    Error code.
	 * @param string     $message Error message.
	 * @param string     $data    Error data.
	 */
	public function add( $code, $message, $data = '' ) {

		if ( ! defined( 'WP_DEBUG' ) || FALSE === WP_DEBUG ) {

			// $this->errors and $this->error_data needs to be brought into scope too.
			// See note below about WP 4.0.

			$error = $this->errors;
			// $this->errors = array();

			$error_data = $this->error_data;
			// $this->error_data = array();

			$error['wp_debug'][]    = __( 'To enable logging, WP_DEBUG must defined and set to TRUE.', 'connections' );
			$error_data['wp_debug'] = '';

			$this->errors = $error;
			$this->error_data = $error_data;
		}

		$execTime = sprintf( '%.6f', microtime( TRUE ) - $this->startTime );
		$tick     = sprintf( '%.6f', 0 );

		if ( $this->lastBenchTime > 0 ) {

			$tick = sprintf( '%.6f', microtime( TRUE ) - $this->lastBenchTime );
		}

		$this->lastBenchTime = microtime( TRUE );

		/*
		 * WordPress >= 4.0 made the errors and error_data vars private and added magic
		 * get/set for backward compatibility. In order to set array data we need to bring
		 * the value of $this->errors into scope (via the magic get()), set the error code and message
		 * and finally save back to $this->errors (via the magic set()).
		 *
		 * NOTE: The same method is used for $this->error_data.
		 */
		$error = $this->errors;
		$error[ $code ][] = "[$execTime : $tick]: $message";
		$this->errors = $error;

		if ( ! empty( $data ) ) {

			$error_data = $this->error_data;
			$error_data[ $code ] = $data;
			$this->error_data = $error_data;
		}

	}

	/**
	 * Return WP_Error messages array as string.
	 *
	 * @access public
	 * @since unknown
	 *
	 * @return string
	 */
	public function __toString() {

		return implode( PHP_EOL, $this->get_error_messages() );
	}
}
