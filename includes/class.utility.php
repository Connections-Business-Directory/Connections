<?php

/**
 * String sanitation and validation.
 *
 * @package     Connections
 * @subpackage  Sanitation and Validation
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnValidate {

	/**
	 *
	 * @access public
	 * @since  unknown
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
	 * @access public
	 * @since  unknown
	 *
	 * @param string $url
	 * @param bool   $check_exists [optional]
	 *
	 * @return int
	 */
	public function url( $url, $check_exists = TRUE ) {

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
	 * @access public
	 * @since  unknown
	 *
	 * @param string $email
	 * @param bool   $check_mx [optional]
	 *
	 * @return int
	 */
	public function email( $email, $check_mx = TRUE ) {

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
	 * @since  0.7.2.0
	 *
	 * @deprecated since 8.6
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

/**
 * Class cnUtility
 */
class cnUtility {

	/**
	 * Return a number only hash.
	 *
	 * @link https://stackoverflow.com/a/23679870/175071
	 *
	 * @since 9.8
	 *
	 * @param string   $str
	 * @param int|null $len
	 *
	 * @return string
	 */
	public static function numHash( $str, $len = null ) {

		$binhash = md5( $str, true );
		$numhash = unpack( 'N2', $binhash );
		$hash    = $numhash[1] . $numhash[2];

		if ( $len !== null && is_int( $len ) ) {

			$hash = substr( $hash, 0, $len );
		}

		return $hash;
	}

	/**
	 * Get user IP.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @link   http://stackoverflow.com/a/6718472
	 *
	 * @return string The IP address.
	 */
	public static function getIP() {

		foreach ( array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key ) {

			if ( TRUE === array_key_exists( $key, $_SERVER ) ) {

				foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) {

					if ( FALSE !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {

						return $ip;
					}
				}
			}
		}
	}

	/**
	 * Returns v4 compliant UUID.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @link   http://stackoverflow.com/a/15875555
	 * @link   http://www.php.net/manual/en/function.uniqid.php#94959
	 *
	 * @return string
	 */
	public static function getUUID() {

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {

			$data    = openssl_random_pseudo_bytes(16);

			if ( FALSE !== $data ) {

				$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 ); // set version to 0010
				$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10

				return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
			}

		}

		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff )
		);

	}

	/**
	 * Convert a value within one range to a value within another range, maintaining ratio.
	 *
	 * Converted Python script from:
	 * @link   http://stackoverflow.com/a/15537393
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  float  $x    Original value.
	 * @param  float  $oMin Old minimum.
	 * @param  float  $oMax Old maximum.
	 * @param  float  $nMin New minimum.
	 * @param  float  $nMax New maximum.
	 *
	 * @return mixed        bool | float Return false on failure, or return new value within new range.
	 */
	public static function remapRange( $x, $oMin, $oMax, $nMin, $nMax ) {

		#range check
		if ( $oMin == $oMax ) {

			return FALSE;
		}

		if ( $nMin == $nMax ) {

			return FALSE;
		}

		#check reversed input range
		$reverseInput = FALSE;
		$oldMin       = min( $oMin, $oMax );
		$oldMax       = max( $oMin, $oMax );

		if ( ! $oldMin == $oMin ) {

			$reverseInput = TRUE;
		}

		#check reversed output range
		$reverseOutput = FALSE;
		$newMin        = min( $nMin, $nMax );
		$newMax        = max( $nMin, $nMax );

		if ( ! $newMin == $nMin ) {

			$reverseOutput = TRUE;
		}

		$portion = ( $x - $oldMin ) * ( $newMax - $newMin ) / ( $oldMax - $oldMin );

		if ( $reverseInput ) {

			$portion = ( $oldMax - $x ) * ( $newMax - $newMin ) / ( $oldMax - $oldMin );
		}

		$result = $portion + $newMin;

		if ( $reverseOutput ) {

			$result = $newMax - $portion;
		}

		return $result;
	}
}
