<?php
/**
 * @package    Connections
 * @subpackage _http
 * @copyright  Copyright (c) 2017, Steven A. Zahm
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      8.6.7
 */

namespace Connections_Directory\Utility;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class _http
 *
 * @package Connections_Directory\Utility
 */
class _http {

	/**
	 * Returns if the SSL of the store should be verified.
	 *
	 * @access public
	 * @since  8.6.7
	 * @static
	 *
	 * @return bool
	 */
	public static function verifySSL() {

		return (bool) apply_filters( 'cn_sl_api_request_verify_ssl', true );
	}
}
