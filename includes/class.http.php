<?php
/**
 * @package     Connections
 * @subpackage  HTTP
 * @copyright   Copyright (c) 2017, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.6.7
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if ( ! class_exists( 'cnHTTP' ) ) :

	/**
	 * Class cnHTTP
	 */
	class cnHTTP {

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

			return (bool) apply_filters( 'cn_sl_api_request_verify_ssl', TRUE );
		}
	}

endif; // End class_exists check.
