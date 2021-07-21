<?php

use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnValidate
 */
class cnValidate {

	/**
	 * @since unknown
	 *
	 * @deprecated 8.1.6 Use {@see cnSanitize::args()} instead.
	 * @see cnSanitize::args()
	 *
	 * @param array $defaults
	 * @param array $untrusted
	 *
	 * @return array
	 */
	public function attributesArray( $defaults, $untrusted ) {

		_deprecated_function( __METHOD__, '8.1.6', 'cnSanitize::args()' );

		return cnSanitize::args( $untrusted, $defaults );
	}

	/**
	 * Validate the supplied URL.
	 *
	 * return: 1 is returned if good (check for >0 or ==1)
	 * return: 0 is returned if syntax is incorrect
	 * return: -1 is returned if syntax is correct, but url/file does not exist
	 *
	 * @author Luke America
	 * @link http://wpcodesnippets.info/blog/two-useful-php-validation-functions.html
	 *
	 * @since unknown
	 * @deprecated 9.11
	 *
	 * @param string $url
	 * @param bool   $check_exists [optional]
	 *
	 * @return int
	 */
	public function url( $url, $check_exists = TRUE ) {

		_deprecated_function( __METHOD__, '9.11', null );

		/**********************************************************************
		 * Copyright © 2011 Gizmo Digital Fusion (http://wpCodeSnippets.info)
		 * you can redistribute and/or modify this code under the terms of the
		 * GNU GPL v2: http://www.gnu.org/licenses/gpl-2.0.html
		 **********************************************************************/

		// add http:// (here AND in the referenced $url), if needed
		if ( ! $url ) {
			return FALSE;
		}
		if ( FALSE === strpos( $url, ':' ) ) {
			$url = 'http://' . $url;
		}

		// auto-correct backslashes (here AND in the referenced $url)
		$url = str_replace( '\\', '/', $url );

		// convert multi-byte international url's by stripping multi-byte chars
		$url_local = urldecode( $url ) . ' ';

		if ( function_exists( 'mb_strlen' ) ) {

			$len = mb_strlen( $url_local );

			if ( $len !== strlen( $url_local ) ) {

				$convmap   = array( 0x0, 0x2FFFF, 0, 0xFFFF );
				$url_local = mb_decode_numericentity( $url_local, $convmap, 'UTF-8' );
			}
		}

		$url_local = trim( $url_local );

		// now, process pre-encoded MBI's
		$regex    = '#&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);#i';
		$url_test = preg_replace( $regex, '$1', htmlentities( $url_local, ENT_QUOTES, 'UTF-8' ) );

		if ( $url_test != '' ) {

			$url_local = $url_test;
		}

		// test for bracket-enclosed IP address (IPv6) and modify for further testing
		preg_match( '#(?<=\[)(.*?)(?=\])#i', $url, $matches );

		if ( isset( $matches[0] ) && $matches[0] ) {

			$ip = $matches[0];
			if ( ! preg_match( '/^([0-9a-f\.\/:]+)$/', strtolower( $ip ) ) ) {

				return FALSE;
			}

			if ( substr_count( $ip, ':' ) < 2 ) {

				return FALSE;
			}

			$octets = preg_split( '/[:\/]/', $ip );

			foreach ( $octets as $i ) {

				if ( strlen( $i ) > 4 ) {

					return FALSE;
				}
			}

			$ip_adj    = 'x' . str_replace( ':', '_', $ip ) . '.com';
			$url_local = str_replace( '[' . $ip . ']', $ip_adj, $url_local );
		}

		// test for IP address (IPv4)
		$regex = "#^(https?|ftp|news|file)\:\/\/";
		$regex .= "([0-9]{1,3}\.[0-9]{1,3}\.)#";

		if ( preg_match( $regex, $url_local ) ) {

			$regex = "#^(https?|ftps)\:\/\/";
			$regex .= "([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})#";

			if ( ! preg_match( $regex, $url_local ) ) {

				return FALSE;
			}

			$seg = preg_split( '/[.\/]/', $url_local );

			if ( ( $seg[2] > 255 ) || ( $seg[3] > 255 ) || ( $seg[4] > 255 ) || ( $seg[5] > 255 ) ) {

				return FALSE;
			}
		}

		// patch for wikipedia which can have a 2nd colon in the url
		if ( strpos( strtolower( $url_local ), 'wikipedia' ) ) {

			$pos       = strpos( $url_local, ':' );
			$url_left  = substr( $url_local, 0, $pos + 1 );
			$url_right = substr( $url_local, $pos + 1 );
			$url_right = str_replace( ':', '_', $url_right );
			$url_local = $url_left . $url_right;
		}

		// construct the REGEX for standard processing
		// scheme
		$regex = "~^(https?|ftp|news|file)\:\/\/";
		// user and password (optional)
		$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";
		// hostname or IP address
		$regex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,4}";
		// port (optional)
		$regex .= "(\:[0-9]{2,5})?";
		// dir/file path (optional)
		$regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
		// query (optional)
		$regex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";
		// anchor (optional)
		$regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$~";

		// test it
		$is_valid = preg_match( $regex, $url_local ) > 0;

		// final check for a TLD suffix
		if ( $is_valid ) {

			$url_test = str_replace( '-', '_', $url_local );
			$regex    = '#^(.*?//)*([\w\.\d]*)(:(\d+))*(/*)(.*)$#';
			preg_match( $regex, $url_test, $matches );
			$is_valid = preg_match( '#^(.+?)\.+[0-9a-z]{2,4}$#i', $matches[2] ) > 0;
		}

		// check if the url/file exists
		if ( ( $check_exists ) && ( $is_valid ) ) {

			$status   = array();
			$url_test = str_replace( ' ', '%20', $url );
			$handle   = curl_init( $url_test );
			curl_setopt( $handle, CURLOPT_HEADER, TRUE );
			curl_setopt( $handle, CURLOPT_NOBODY, TRUE );
			curl_setopt( $handle, CURLOPT_FAILONERROR, TRUE );
			curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, FALSE );
			curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, FALSE );
			curl_setopt( $handle, CURLOPT_RETURNTRANSFER, TRUE );
			preg_match( '/HTTP\/.* ([0-9]+) .*/', curl_exec( $handle ), $status );

			if ( $status[1] == 200 ) {

				$is_valid = TRUE;
			} else {

				$is_valid = - 1;
			}
		}

		// exit
		return $is_valid;
	}

	/**
	 * Validate the supplied URL.
	 *
	 * return: 1 is returned if good (check for >0 or ==1)
	 * return: 0 is returned if syntax is incorrect
	 * return: -1 is returned if syntax is correct, but email address does not exist
	 *
	 * @author Luke America
	 * @url http://wpcodesnippets.info/blog/two-useful-php-validation-functions.html
	 *
	 * @since unknown
	 * @deprecated 9.11
	 *
	 * @param string $email
	 * @param bool   $check_mx [optional]
	 *
	 * @return int
	 */
	public function email( $email, $check_mx = TRUE ) {

		_deprecated_function( __METHOD__, '9.11', null );

		/**********************************************************************
		 * Copyright © 2011 Gizmo Digital Fusion (http://wpCodeSnippets.info)
		 * you can redistribute and/or modify this code under the terms of the
		 * GNU GPL v2: http://www.gnu.org/licenses/gpl-2.0.html
		 **********************************************************************/

		// check syntax
		$email    = trim( $email );
		$regex    = '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
		$is_valid = preg_match( $regex, $email, $matches );

		// NOTE: Windows servers do not offer checkdnsrr until PHP 5.3.
		// So we create the function, if it doesn't exist.
		if ( ! function_exists( 'checkdnsrr' ) ) {

			function checkdnsrr( $host_name = '', $rec_type = '' ) {

				if ( ! empty( $host_name ) ) {

					if ( ! $rec_type ) {

						$rec_type = 'MX';
					}

					exec( "nslookup -type=$rec_type $host_name", $result );

					// Check each line to find the one that starts with the host name.
					foreach ( $result as $line ) {
						if ( eregi( "^$host_name", $line ) ) {
							return TRUE;
						}
					}

					return FALSE;
				}

				return FALSE;
			}
		}

		// check that the server exists and is setup to handle email accounts
		if ( ( $is_valid ) && ( $check_mx ) ) {

			$at_index = strrpos( $email, '@' );
			$domain   = substr( $email, $at_index + 1 );

			if ( ! ( checkdnsrr( $domain, 'MX' ) || checkdnsrr( $domain, 'A' ) ) ) {

				$is_valid = - 1;
			}
		}

		// exit
		return $is_valid;
	}

	/**
	 * Will return TRUE?FALSE based on current user capability or privacy setting if the user is not logged in to
	 * WordPress.
	 *
	 * @since 0.7.2.0
	 * @deprecated 8.6
	 *
	 * @param string $visibility
	 *
	 * @return bool
	 */
	public static function userPermitted( $visibility ) {

		_deprecated_function( __METHOD__, '8.6', 'cnUser::canViewVisibility()' );

		return Connections_Directory()->currentUser->canViewVisibility( $visibility );
	}
}
