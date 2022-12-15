<?php
/**
 * Helper methods for AJAX action responses.
 *
 * @since 10.4.33
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections_Directory\Hook\Action\Ajax
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Action\Ajax;

/**
 * Trait Response
 *
 * @package Connections_Directory\Hook\Action\Ajax
 */
trait Response {

	/**
	 * AJAX error response.
	 *
	 * @since 10.4.33
	 *
	 * @param string     $message     The response message.
	 * @param array|null $data        The data to include in the response.
	 * @param int|null   $status_code The HTTP status code to output. Default null.
	 */
	private function error( $message, $data = null, $status_code = null ) {

		wp_send_json_error( $this->mergeMessageData( $message, $data ), $status_code );
	}

	/**
	 * AJAX success response.
	 *
	 * @since 10.4.33
	 *
	 * @param string     $message     The response message.
	 * @param array|null $data        The data to include in the response.
	 * @param int|null   $status_code The HTTP status code to output. Default null.
	 */
	private function success( $message, $data = null, $status_code = null ) {

		wp_send_json_success( $this->mergeMessageData( $message, $data ), $status_code );
	}

	/**
	 * Merge the response and data into a single array.
	 *
	 * @since 10.4.33
	 *
	 * @param string     $message The response message.
	 * @param array|null $data    The data to include in the response.
	 *
	 * @return array
	 */
	private function mergeMessageData( $message, $data ) {

		$return = array(
			'message' => $message,
		);

		if ( is_array( $data ) && ! empty( $data ) ) {

			$return = array_merge( $return, $data );
		}

		return $return;
	}
}
