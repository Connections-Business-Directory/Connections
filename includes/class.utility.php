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

class cnFormatting {
	/**
	 * Sanitize the input string. HTML tags can be permitted.
	 * The permitted tags can be supplied in an array.
	 *
	 * @TODO: Finish the code needed to support the $permittedTags array.
	 *
	 * @param string $string
	 * @param bool $allowHTML [optional]
	 * @param array $permittedTags [optional]
	 * @return string
	 */
	public function sanitizeString( $string, $allowHTML = FALSE, $permittedTags = array() ) {
		// Strip all tags except the permitted.
		if ( ! $allowHTML ) {
			// Ensure all tags are closed. Uses WordPress method balanceTags().
			$balancedText = balanceTags( $string, TRUE );

			$strippedText = strip_tags( $balancedText );

			// Strip all script and style tags.
			$strippedText = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $strippedText );

			// Escape text using the WordPress method and then strip slashes.
			$escapedText = stripslashes( esc_attr( $strippedText ) );

			// Remove line breaks and trim white space.
			$escapedText = preg_replace( '/[\r\n\t ]+/', ' ', $escapedText );

			return trim( $escapedText );
		} else {
			// Strip all script and style tags.
			$strippedText = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
			$strippedText = preg_replace( '/&lt;(script|style).*?&gt;.*?&lt;\/\\1&gt;/si', '', stripslashes( $strippedText ) );

			/*
			 * Use WordPress method make_clickable() to make links clickable and
			 * use kses for filtering.
			 *
			 * http://ottopress.com/2010/wp-quickie-kses/
			 */
			return wptexturize( wpautop( make_clickable( wp_kses_post( $strippedText ) ) ) );
		}

	}

	/**
	 * Uses WordPress function to sanitize the input string.
	 *
	 * Limits the output to alphanumeric characters, underscore (_) and dash (-).
	 * Whitespace becomes a dash.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function sanitizeStringStrong( $string ) {
		$string = str_ireplace( '%', '-', $string ); // Added this because sanitize_title_with_dashes will still allow % to passthru.
		$string = sanitize_title_with_dashes( $string );
		return $string;
	}

	/**
	 * Strips all numeric characters from the supplied string and returns the string.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function stripNonNumeric( $string ) {

		return preg_replace( '/[^0-9]/', '', $string );
	}

	/**
	 * Converts the following strings: yes/no; true/false and 0/1 to boolean values.
	 * If the supplied string does not match one of those values the method will return NULL.
	 *
	 * @access public
	 * @since  unknown
	 * @static
	 *
	 * @param  string|int|bool $value
	 *
	 * @return bool
	 */
	public static function toBoolean( &$value ) {

		// Already a bool, return it.
		if ( is_bool( $value ) ) return $value;

		$value = filter_var( strtolower( $value ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		if ( is_null( $value ) ) {

			$value = FALSE;
		}

		return $value;
	}

	/**
	 * Return localized Yes or No.
	 *
	 * @author Alex Rabe (http://alexrabe.de/)
	 * @since  0.7.1.6
	 *
	 * @param  bool $bool
	 *
	 * @return string Returns 'Yes' | 'No'
	 */
	public static function toYesNo( $bool ) {

		if ( $bool ) {

			return __( 'Yes', 'connections' );

		} else {

			return __( 'No', 'connections' );
		}
	}

	/**
	 * JSON encode objects and arrays.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @param  mixed $value The value to maybe json_encode.
	 *
	 * @return mixed
	 */
	public static function maybeJSONencode( $value ) {

		if ( is_null( $value ) ) {

			return '';
		}

		if ( ! is_scalar( $value ) ) {

			return json_encode( $value );

		} else {

			return $value;
		}
	}

	/**
	 * Maybe json_decode the supplied value.
	 *
	 * @access public
	 * @since 0.8
	 * @static
	 *
	 * @param  mixed   $value The value to decode.
	 * @param  boolean $array [optional] Whether or not the JSON decoded value should an object or an associative array.
	 *
	 * @return mixed
	 */
	public static function maybeJSONdecode( $value, $array = TRUE ) {

		if ( ! is_string( $value ) || 0 == strlen( $value ) ) {

			return $value;
		}

		// A JSON encoded string will start and end with either a square bracket of curly bracket.
		if ( ( $value[0] == '[' && $value[ strlen( $value ) - 1 ] == ']' ) || ( $value[0] == '{' && $value[ strlen( $value ) - 1 ] == '}' ) ) {

			$value = json_decode( $value, $array );
		}

		if ( is_null( $value ) ) {

			return '';

		} else {

			return $value;
		}
	}

	/**
	 * Ensures that any hex color is properly hashed.
	 * Otherwise, returns value unaltered.
	 *
	 * This function is borrowed from the class_wp_customize_manager.php
	 * file in WordPress core.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $color
	 *
	 * @return string
	 */
	public static function maybeHashHEXColor( $color ) {

		if ( $unhashed = cnSanitize::hexColorNoHash( $color ) ) {

			return '#' . $unhashed;
		}

		return $color;
	}

	/**
	 * Create excerpt from the supplied string.
	 *
	 * @access public
	 * @since  8.1.5
	 * @static
	 *
	 * @deprecated 8.2.9 Use {@see cnString::excerpt()} instead.
	 * @see cnString::excerpt()
	 *
	 * @param  string  $string String to create the excerpt from.
	 * @param  array   $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type int    $length       The length, number of words, of the excerpt to create.
	 *                                If set to `p` the excerpt will be the first paragraph, no word limit.
	 *                                Default: 55.
	 *     @type string $more         The string appended to the end of the excerpt when $length is exceeded.
	 *                                Default: &hellip
	 *     @type array  $allowed_tags An array containing the permitted tags.
	 * }
	 *
	 * @return string
	 */
	public static function excerpt( $string, $atts = array() ) {

		return cnString::excerpt( $string, $atts );
	}

	/**
	 * Prepare the placeholders to be used in a IN query clause using @see wpdb::prepare().
	 *
	 * @access public
	 * @since  8.1.5
	 * @static
	 *
	 * @param array  $items The array of items to be used in the IN query clause.
	 * @param string $type  The type of placeholder to be used.
	 *                      Default: %s
	 *                      Accepted: %d, %f, %s
	 *
	 * @return string
	 */
	public static function prepareINPlaceholders( $items, $type = '%s' ) {

		$placeholders = array_fill( 0, count( $items ), $type );

		return implode( ', ', $placeholders );
	}

	/**
	 * Convert supplied string to camelCase.
	 *
	 * @access public
	 * @since  8.5.19
	 * @static
	 *
	 * @link http://stackoverflow.com/a/2792045/5351316
	 *
	 * @param string $string
	 * @param bool   $capitaliseInitial
	 *
	 * @return string
	 */
	public static function toCamelCase( $string, $capitaliseInitial = FALSE ) {

		$string = self::sanitizeStringStrong( $string );
		$string = str_replace( ' ', '', ucwords( str_replace( array( '_', '-' ), ' ', $string ) ) );

		if ( ! $capitaliseInitial ) {

			$string[0] = strtolower( $string[0] );
		}

		return $string;
	}

	/**
	 * Convert a PHP format string to a jQueryUI Datepicker/DateTimepicker compatible datetime format string.
	 *
	 * @access public
	 * @since  8.6.4
	 *
	 * @link http://stackoverflow.com/a/16725290/5351316
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function dateFormatPHPTojQueryUI( $string ) {

		$map = array(
			// PHP Date format character => jQueryUI Datepicker/DateTimepicker format character.
			// Day.
			'd' => 'dd', 'D' => 'D', 'j' => 'd', 'l' => 'DD', 'N' => '', 'S' => '', 'w' => '', 'z' => 'o',
			// Week.
			'W' => '',
			// Month.
			'F' => 'MM', 'm' => 'mm', 'M' => 'M', 'n' => 'm', 't' => '',
			// Year.
			'L' => '', 'o' => '', 'Y' => 'yy', 'y' => 'y',
			// Time.
			'a' => 'tt', 'A' => 'TT', 'B' => '',
			'g' => 'h', 'G' => 'H', 'h' => 'hh', 'H' => 'HH', 'i' => 'mm', 's' => 'ss', 'u' => 'c',
		);

		$format   = '';
		$escaping = FALSE;

		for ( $i = 0; $i < strlen( $string ); $i++ ) {

			$char = $string[ $i ];

			// PHP date format escaping character.
			if ( $char === '\\' ) {

				$i++;

				if ( $escaping ) {

					$format .= $string[ $i ];

				} else {

					$format .= '\'' . $string[ $i ];
				}

				$escaping = TRUE;

			} else {

				if ( $escaping ) {

					$format .= '\'';
					$escaping = FALSE;
				}

				if ( isset( $map[ $char ] ) ) {

					$format .= $map[ $char ];

				} else {

					$format .= $char;
				}
			}
		}

		//If the escaping is still open, make sure to close it. So formatting like this will work: `H\h i\m\i\n`.
		if ( $escaping ) $format .= '\'';

		return $format;
	}
}

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
	 * @access public
	 * @since  0.7.2.0
	 * @static
	 *
	 * @deprecated since 8.6
	 *
	 * @uses   is_user_logged_in()
	 * @uses   current_user_can()
	 * @uses   is_admin()
	 * @uses   cnOptions::loginRequired()
	 * @uses   cnOptions::getAllowPublicOverride()
	 * @uses   cnOptions::getAllowPrivateOverride()
	 *
	 * @param string $visibility
	 *
	 * @return bool
	 */
	public static function userPermitted( $visibility ) {

		return Connections_Directory()->currentUser->canViewVisibility( $visibility );
	}
}

/**
 * Class cnURL
 */
class cnURL {

	/**
	 * @link http://publicmind.in/blog/url-encoding/
	 *
	 * @access public
	 * @since  unknown
	 * @static
	 *
	 * @param string $url The URL to encode
	 *
	 * @return string A string containing the encoded URL with disallowed
	 *                characters converted to their percentage encodings.
	*/
	public static function encode( $url ) {

		$reserved = array(
			":" => '!%3A!ui',
			"/" => '!%2F!ui',
			"?" => '!%3F!ui',
			"#" => '!%23!ui',
			"[" => '!%5B!ui',
			"]" => '!%5D!ui',
			"@" => '!%40!ui',
			"!" => '!%21!ui',
			"$" => '!%24!ui',
			"&" => '!%26!ui',
			"'" => '!%27!ui',
			"(" => '!%28!ui',
			")" => '!%29!ui',
			"*" => '!%2A!ui',
			"+" => '!%2B!ui',
			"," => '!%2C!ui',
			";" => '!%3B!ui',
			"=" => '!%3D!ui',
			"%" => '!%25!ui',
			);

		$url = rawurlencode( $url );
		$url = preg_replace( array_values( $reserved ), array_keys( $reserved ), $url );

		return $url;
	}

	/**
	 * Take a URL and see if it's prefixed with a protocol, if it's not then it will add the default prefix to the
	 * start of the string.
	 *
	 * @todo Refactor to use @see set_url_scheme()
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @param  string $url
	 * @param  string $protocol
	 *
	 * @return string
	 */
	public static function prefix( $url, $protocol = 'http://' ) {

		return parse_url( $url, PHP_URL_SCHEME ) === NULL ? $protocol . $url : $url;
	}

	/**
	 * Remove the protocol from the supplied URL.
	 *
	 * @access public
	 * @since  8.4.2
	 * @static
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function makeRelative( $url ) {

		$url = str_replace(
			array(
				set_url_scheme( home_url(), 'http' ),
				set_url_scheme( home_url(), 'https' ),
			),
			set_url_scheme( home_url(), 'relative' ),
			$url
		);

		return $url;
	}

	/**
	 * Return a URL with the protocol scheme removed to make it a protocol relative URL.
	 *
	 * This is useful when enqueueing CSS and JavaScript files.
	 *
	 * @access public
	 * @since  8.4.3
	 * @static
	 *
	 * @param string $url
	 *
	 * @return mixed string|NULL The URL without the protocol scheme, NULL on error.
	 */
	public static function makeProtocolRelative( $url ) {

		return preg_replace( '(https?://)', '//', $url );
	}

	/**
	 * Create the URL to a file from its absolute system path.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @uses   content_url()
	 * @uses   untrailingslashit()
	 * @uses   wp_normalize_path()
	 *
	 * @param  string $path The file path.
	 *
	 * @return string       The URL to the file.
	 */
	public static function fromPath( $path ) {

		// Get correct URL and path to wp-content.
		$content_url = content_url();
		$content_dir = untrailingslashit( WP_CONTENT_DIR );

		// Fix path on Windows.
		// wp_normalize_path() in WP >= 3.9
		if ( function_exists( 'wp_normalize_path' ) ) {

			$path        = wp_normalize_path( $path );
			$content_dir = wp_normalize_path( $content_dir );

		} else {

			$path = str_replace( '\\', '/', $path );
			$path = preg_replace( '|/+|','/', $path );

			$content_dir = str_replace( '\\', '/', $content_dir );
			$content_dir = preg_replace( '|/+|','/', $content_dir );

		}

		// Create URL.
		$url = str_replace( $content_dir, $content_url, $path );

		return $url;
	}

	/**
	 * Create a permalink.
	 *
	 * @todo Should check to ensure require params are set and valid and pass back WP_Error if they are not.
	 * @todo The type case statement should have a default with an filter attached so this can be pluggable.
	 *
	 * NOTE: When the `name` permalink is being requested, use the entry slug.
	 *       It is saved id the db as URL encoded. If any other strings pass
	 *       for the `name` permalink must be URL encoded.
	 *       All the other permalink types will be URL encoded in this method
	 *       so pass stings without URL encoding.
	 *
	 * @access private
	 * @since  0.7.3
	 * @static
	 *
	 * @global $wp_rewrite
	 * @global $post
	 *
	 * @uses   is_admin()
	 * @uses   wp_parse_args()
	 * @uses   get_option()
	 * @uses   in_the_loop()
	 * @uses   is_page()
	 * @uses   trailingslashit()
	 * @uses   get_permalink()
	 * @uses   add_query_arg()
	 * @uses   is_front_page()
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type string $class      The class to give the anchor.
	 *                              Default: ''
	 *     @type string $text       The anchor text.
	 *                              Default: ''
	 *     @type string $title      The anchor title attribute.
	 *                              Default: ''
	 *     @type bool   $follow     Whether or not the anchor rel should be follow or nofollow
	 *                              Default: TRUE
	 *     @type string $rel        The rel attribute.
	 *                              Default: ''
	 *     @type string $slug       The slug of the object to build the permalink for. ie. the entry slug or term slug.
	 *                              Default: ''
	 *     @type string $on_click   The inline javascript on_click attribute.
	 *                              Default: ''
	 *     @type string $type       The type of permalink to create. ie. name, edit, home
	 *                              Default: 'name'
	 *     @type int    $home_id    The page ID of the directory home.
	 *                              Default: The page set as the Directory Home Page.
	 *     @type bool   $force_home Whether or not to for the page ID to the page ID of the page set as the Directory Home Page.
	 *                              Default: FALSE
	 *     @type string $data       What to return.
	 *                              Default: tag
	 *                              Accepts: tag | url
	 *     @type bool   $return     Whether or not to return or echo the permalink.
	 *                              Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function permalink( $atts ) {

		/** @var $wp_rewrite WP_Rewrite */
		global $wp_rewrite, $post;

		// The class.seo.file is only loaded in the frontend; do not attempt to remove the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( FALSE );

		// The anchor attributes.
		$piece = array();

		$defaults = array(
			'class'      => '',
			'text'       => '',
			'title'      => '',
			'follow'     => TRUE,
			'rel'        => '',
			'slug'       => '',
			'on_click'   => '',
			'type'       => 'name',
			'home_id'    => cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
			'force_home' => FALSE,
			'data'       => 'tag', // Valid: 'tag' | 'url'
			'return'     => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Get the directory home page ID.
		$homeID = $atts['force_home'] ? cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) : $atts['home_id'];

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		// Create the permalink base based on context where the entry is being displayed.
		if ( in_the_loop() && is_page() ) {

			if ( $wp_rewrite->using_permalinks() ) {

				// Only slash it when using pretty permalinks.
				$permalink = trailingslashit( get_permalink( $homeID ) );

			} else {

				$permalink = get_permalink( $homeID );

				// If the current page is the front page, the `page_id` query var must be added because without it, WP
				// will redirect to the blog/posts home page.
				if ( is_front_page() ) {

					$permalink = add_query_arg( 'page_id' , $post->ID, $permalink );
				}
			}

		} else {

			// If using pretty permalinks get the directory home page ID and slash it, otherwise just add the page_id to the query string.
			if ( $wp_rewrite->using_permalinks() ) {

				$permalink = trailingslashit( get_permalink( $homeID ) );

			} else {

				$permalink = add_query_arg( array( 'page_id' => $homeID, 'p' => FALSE  ), get_permalink( $homeID ) );
			}

		}

		if ( ! empty( $atts['class'] ) ) $piece['class']       = 'class="' . $atts['class'] .'"';
		// if ( ! empty( $atts['slug'] ) ) $piece['id']        = 'id="' . $atts['slug'] .'"';
		if ( ! empty( $atts['title'] ) ) $piece['title']       = 'title="' . $atts['title'] .'"';
		if ( ! empty( $atts['target'] ) ) $piece['target']     = 'target="' . $atts['target'] .'"';
		if ( ! $atts['follow'] ) $piece['follow']              = 'rel="nofollow"';
		if ( ! empty( $atts['rel'] ) ) $piece['rel']           = 'rel="' . $atts['rel'] .'"';
		if ( ! empty( $atts['on_click'] ) ) $piece['on_click'] = 'onClick="' . $atts['on_click'] .'"';

		switch ( $atts['type'] ) {

			case 'home':
				break;

			case 'all':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . 'view/all/' );
				} else {
					$permalink = add_query_arg( 'cn-view', 'all' , $permalink );
				}

				break;

			case 'submit':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . 'submit/' );
				} else {
					$permalink = add_query_arg( 'cn-view', 'submit' , $permalink );
				}

				break;

			case 'edit':

				if ( $wp_rewrite->using_permalinks() ) {

					// The entry slug is saved in the db urlencoded so we'll expect when the permalink for entry name is
					// requested that the entry slug is being used so urlencode() will not be use as not to double encode it.
					$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . $atts['slug'] . '/edit' );
				} else {
					$permalink = add_query_arg( array( 'cn-entry-slug' => $atts['slug'] , 'cn-view' => 'detail', 'cn-process' => 'edit' ) , $permalink );
				}

				break;

			case 'name':

				if ( $wp_rewrite->using_permalinks() ) {

					// The entry slug is saved in the db urlencoded so we'll expect when the permalink for entry name is
					// requested that the entry slug is being used so urlencode() will not be use as not to double encode it.
					$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . $atts['slug'] );
				} else {
					$permalink = add_query_arg( array( 'cn-entry-slug' => $atts['slug'] , 'cn-view' => 'detail' ) , $permalink );
				}

				break;

			case 'detail':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . $atts['slug'] . '/detail' );
				} else {
					$permalink = add_query_arg( array( 'cn-entry-slug' => $atts['slug'] , 'cn-view' => 'detail' ) , $permalink );
				}

				break;

			case 'department':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['department_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-department', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'organization':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['organization_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-organization', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'category':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['category_base'] . '/' . $atts['slug'] );
				} else {
					$permalink = add_query_arg( 'cn-cat-slug', $atts['slug'] , $permalink );
				}

				break;

			case 'district':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['district_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-district', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'county':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['county_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-county', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'locality':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['locality_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-locality', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'region':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['region_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-region', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'postal_code':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['postal_code_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-postal-code', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'country':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['country_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-country', urlencode( $atts['slug'] ) , $permalink );
				}

				break;

			case 'character':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['character_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( array( 'cn-char' => urlencode( $atts['slug'] ) ), $permalink );
				}

				break;

			default:

				if ( has_filter( "cn_permalink-{$atts['type']}" ) ) {

					/**
					 * Allow extensions to add custom permalink types.
					 *
					 * The variable portion of the filter name if the permalink type being created.
					 *
					 * @since 8.5.17
					 *
					 * @param string $permalink The permalink.
					 * @param array  $atts      The attributes array.
					 */
					$permalink = apply_filters( "cn_permalink-{$atts['type']}", $permalink, $atts );
				}

				break;
		}

		$permalink = apply_filters( 'cn_permalink', $permalink, $atts );

		if ( 'url' == $atts['data'] ) {

			$out = esc_url( $permalink );

		} else {

			$piece['href'] = 'href="' . esc_url( $permalink ) . '"';

			$out = '<a ' . implode( ' ', $piece ) . '>' . $atts['text'] . '</a>';

		}

		// The class.seo.file is only loaded in the frontend; do not attempt to add the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( TRUE );

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Removes a forward slash from the beginning of he string if it exists.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @param string $string String to remove the  slashes from.
	 *
	 * @return string String without the forward slashes.
	 */
	public static function unpreslashit( $string ) {

		if ( is_string( $string ) && 0 < strlen( $string ) ) {

			$string = ltrim( $string, '/\\' );
		}

		return $string;
	}

	/**
	 * Prepends a forward slash to a string.
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @param string $string String to  prepend the forward slash.
	 *
	 * @return string String with forward slash prepended.
	 */
	public static function preslashit( $string ) {

		if ( is_string( $string ) && 0 < strlen( $string ) ) {

			$string = '/' . self::unpreslashit( $string );
		}

		return $string;
	}
}

/**
 * Class cnUtility
 */
class cnUtility {

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

/**
 * Class cnColor
 */
class cnColor {

	/**
	 * An array of named colors as the key with the value being the RGB values of said color.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 *
	 * @link   http://psoug.org/snippet/CSS_Colornames_to_RGB_values_415.htm
	 * @var    array
	 */
	private static $colors = array(

		// Colors as they are defined in HTML 3.2
		'black'                => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0x00 ),
		'maroon'               => array( 'red' => 0x80, 'green' => 0x00, 'blue' => 0x00 ),
		'green'                => array( 'red' => 0x00, 'green' => 0x80, 'blue' => 0x00 ),
		'olive'                => array( 'red' => 0x80, 'green' => 0x80, 'blue' => 0x00 ),
		'navy'                 => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0x80 ),
		'purple'               => array( 'red' => 0x80, 'green' => 0x00, 'blue' => 0x80 ),
		'teal'                 => array( 'red' => 0x00, 'green' => 0x80, 'blue' => 0x80 ),
		'gray'                 => array( 'red' => 0x80, 'green' => 0x80, 'blue' => 0x80 ),
		'silver'               => array( 'red' => 0xC0, 'green' => 0xC0, 'blue' => 0xC0 ),
		'red'                  => array( 'red' => 0xFF, 'green' => 0x00, 'blue' => 0x00 ),
		'lime'                 => array( 'red' => 0x00, 'green' => 0xFF, 'blue' => 0x00 ),
		'yellow'               => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0x00 ),
		'blue'                 => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0xFF ),
		'fuchsia'              => array( 'red' => 0xFF, 'green' => 0x00, 'blue' => 0xFF ),
		'aqua'                 => array( 'red' => 0x00, 'green' => 0xFF, 'blue' => 0xFF ),
		'white'                => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0xFF ),

		// Additional colors as they are used by Netscape and IE
		'aliceblue'            => array( 'red' => 0xF0, 'green' => 0xF8, 'blue' => 0xFF ),
		'antiquewhite'         => array( 'red' => 0xFA, 'green' => 0xEB, 'blue' => 0xD7 ),
		'aquamarine'           => array( 'red' => 0x7F, 'green' => 0xFF, 'blue' => 0xD4 ),
		'azure'                => array( 'red' => 0xF0, 'green' => 0xFF, 'blue' => 0xFF ),
		'beige'                => array( 'red' => 0xF5, 'green' => 0xF5, 'blue' => 0xDC ),
		'blueviolet'           => array( 'red' => 0x8A, 'green' => 0x2B, 'blue' => 0xE2 ),
		'brown'                => array( 'red' => 0xA5, 'green' => 0x2A, 'blue' => 0x2A ),
		'burlywood'            => array( 'red' => 0xDE, 'green' => 0xB8, 'blue' => 0x87 ),
		'cadetblue'            => array( 'red' => 0x5F, 'green' => 0x9E, 'blue' => 0xA0 ),
		'chartreuse'           => array( 'red' => 0x7F, 'green' => 0xFF, 'blue' => 0x00 ),
		'chocolate'            => array( 'red' => 0xD2, 'green' => 0x69, 'blue' => 0x1E ),
		'coral'                => array( 'red' => 0xFF, 'green' => 0x7F, 'blue' => 0x50 ),
		'cornflowerblue'       => array( 'red' => 0x64, 'green' => 0x95, 'blue' => 0xED ),
		'cornsilk'             => array( 'red' => 0xFF, 'green' => 0xF8, 'blue' => 0xDC ),
		'crimson'              => array( 'red' => 0xDC, 'green' => 0x14, 'blue' => 0x3C ),
		'darkblue'             => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0x8B ),
		'darkcyan'             => array( 'red' => 0x00, 'green' => 0x8B, 'blue' => 0x8B ),
		'darkgoldenrod'        => array( 'red' => 0xB8, 'green' => 0x86, 'blue' => 0x0B ),
		'darkgray'             => array( 'red' => 0xA9, 'green' => 0xA9, 'blue' => 0xA9 ),
		'darkgreen'            => array( 'red' => 0x00, 'green' => 0x64, 'blue' => 0x00 ),
		'darkkhaki'            => array( 'red' => 0xBD, 'green' => 0xB7, 'blue' => 0x6B ),
		'darkmagenta'          => array( 'red' => 0x8B, 'green' => 0x00, 'blue' => 0x8B ),
		'darkolivegreen'       => array( 'red' => 0x55, 'green' => 0x6B, 'blue' => 0x2F ),
		'darkorange'           => array( 'red' => 0xFF, 'green' => 0x8C, 'blue' => 0x00 ),
		'darkorchid'           => array( 'red' => 0x99, 'green' => 0x32, 'blue' => 0xCC ),
		'darkred'              => array( 'red' => 0x8B, 'green' => 0x00, 'blue' => 0x00 ),
		'darksalmon'           => array( 'red' => 0xE9, 'green' => 0x96, 'blue' => 0x7A ),
		'darkseagreen'         => array( 'red' => 0x8F, 'green' => 0xBC, 'blue' => 0x8F ),
		'darkslateblue'        => array( 'red' => 0x48, 'green' => 0x3D, 'blue' => 0x8B ),
		'darkslategray'        => array( 'red' => 0x2F, 'green' => 0x4F, 'blue' => 0x4F ),
		'darkturquoise'        => array( 'red' => 0x00, 'green' => 0xCE, 'blue' => 0xD1 ),
		'darkviolet'           => array( 'red' => 0x94, 'green' => 0x00, 'blue' => 0xD3 ),
		'deeppink'             => array( 'red' => 0xFF, 'green' => 0x14, 'blue' => 0x93 ),
		'deepskyblue'          => array( 'red' => 0x00, 'green' => 0xBF, 'blue' => 0xFF ),
		'dimgray'              => array( 'red' => 0x69, 'green' => 0x69, 'blue' => 0x69 ),
		'dodgerblue'           => array( 'red' => 0x1E, 'green' => 0x90, 'blue' => 0xFF ),
		'firebrick'            => array( 'red' => 0xB2, 'green' => 0x22, 'blue' => 0x22 ),
		'floralwhite'          => array( 'red' => 0xFF, 'green' => 0xFA, 'blue' => 0xF0 ),
		'forestgreen'          => array( 'red' => 0x22, 'green' => 0x8B, 'blue' => 0x22 ),
		'gainsboro'            => array( 'red' => 0xDC, 'green' => 0xDC, 'blue' => 0xDC ),
		'ghostwhite'           => array( 'red' => 0xF8, 'green' => 0xF8, 'blue' => 0xFF ),
		'gold'                 => array( 'red' => 0xFF, 'green' => 0xD7, 'blue' => 0x00 ),
		'goldenrod'            => array( 'red' => 0xDA, 'green' => 0xA5, 'blue' => 0x20 ),
		'greenyellow'          => array( 'red' => 0xAD, 'green' => 0xFF, 'blue' => 0x2F ),
		'honeydew'             => array( 'red' => 0xF0, 'green' => 0xFF, 'blue' => 0xF0 ),
		'hotpink'              => array( 'red' => 0xFF, 'green' => 0x69, 'blue' => 0xB4 ),
		'indianred'            => array( 'red' => 0xCD, 'green' => 0x5C, 'blue' => 0x5C ),
		'indigo'               => array( 'red' => 0x4B, 'green' => 0x00, 'blue' => 0x82 ),
		'ivory'                => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0xF0 ),
		'khaki'                => array( 'red' => 0xF0, 'green' => 0xE6, 'blue' => 0x8C ),
		'lavender'             => array( 'red' => 0xE6, 'green' => 0xE6, 'blue' => 0xFA ),
		'lavenderblush'        => array( 'red' => 0xFF, 'green' => 0xF0, 'blue' => 0xF5 ),
		'lawngreen'            => array( 'red' => 0x7C, 'green' => 0xFC, 'blue' => 0x00 ),
		'lemonchiffon'         => array( 'red' => 0xFF, 'green' => 0xFA, 'blue' => 0xCD ),
		'lightblue'            => array( 'red' => 0xAD, 'green' => 0xD8, 'blue' => 0xE6 ),
		'lightcoral'           => array( 'red' => 0xF0, 'green' => 0x80, 'blue' => 0x80 ),
		'lightcyan'            => array( 'red' => 0xE0, 'green' => 0xFF, 'blue' => 0xFF ),
		'lightgoldenrodyellow' => array( 'red' => 0xFA, 'green' => 0xFA, 'blue' => 0xD2 ),
		'lightgreen'           => array( 'red' => 0x90, 'green' => 0xEE, 'blue' => 0x90 ),
		'lightgrey'            => array( 'red' => 0xD3, 'green' => 0xD3, 'blue' => 0xD3 ),
		'lightpink'            => array( 'red' => 0xFF, 'green' => 0xB6, 'blue' => 0xC1 ),
		'lightsalmon'          => array( 'red' => 0xFF, 'green' => 0xA0, 'blue' => 0x7A ),
		'lightseagreen'        => array( 'red' => 0x20, 'green' => 0xB2, 'blue' => 0xAA ),
		'lightskyblue'         => array( 'red' => 0x87, 'green' => 0xCE, 'blue' => 0xFA ),
		'lightslategray'       => array( 'red' => 0x77, 'green' => 0x88, 'blue' => 0x99 ),
		'lightsteelblue'       => array( 'red' => 0xB0, 'green' => 0xC4, 'blue' => 0xDE ),
		'lightyellow'          => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0xE0 ),
		'limegreen'            => array( 'red' => 0x32, 'green' => 0xCD, 'blue' => 0x32 ),
		'linen'                => array( 'red' => 0xFA, 'green' => 0xF0, 'blue' => 0xE6 ),
		'mediumaquamarine'     => array( 'red' => 0x66, 'green' => 0xCD, 'blue' => 0xAA ),
		'mediumblue'           => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0xCD ),
		'mediumorchid'         => array( 'red' => 0xBA, 'green' => 0x55, 'blue' => 0xD3 ),
		'mediumpurple'         => array( 'red' => 0x93, 'green' => 0x70, 'blue' => 0xD0 ),
		'mediumseagreen'       => array( 'red' => 0x3C, 'green' => 0xB3, 'blue' => 0x71 ),
		'mediumslateblue'      => array( 'red' => 0x7B, 'green' => 0x68, 'blue' => 0xEE ),
		'mediumspringgreen'    => array( 'red' => 0x00, 'green' => 0xFA, 'blue' => 0x9A ),
		'mediumturquoise'      => array( 'red' => 0x48, 'green' => 0xD1, 'blue' => 0xCC ),
		'mediumvioletred'      => array( 'red' => 0xC7, 'green' => 0x15, 'blue' => 0x85 ),
		'midnightblue'         => array( 'red' => 0x19, 'green' => 0x19, 'blue' => 0x70 ),
		'mintcream'            => array( 'red' => 0xF5, 'green' => 0xFF, 'blue' => 0xFA ),
		'mistyrose'            => array( 'red' => 0xFF, 'green' => 0xE4, 'blue' => 0xE1 ),
		'moccasin'             => array( 'red' => 0xFF, 'green' => 0xE4, 'blue' => 0xB5 ),
		'navajowhite'          => array( 'red' => 0xFF, 'green' => 0xDE, 'blue' => 0xAD ),
		'oldlace'              => array( 'red' => 0xFD, 'green' => 0xF5, 'blue' => 0xE6 ),
		'olivedrab'            => array( 'red' => 0x6B, 'green' => 0x8E, 'blue' => 0x23 ),
		'orange'               => array( 'red' => 0xFF, 'green' => 0xA5, 'blue' => 0x00 ),
		'orangered'            => array( 'red' => 0xFF, 'green' => 0x45, 'blue' => 0x00 ),
		'orchid'               => array( 'red' => 0xDA, 'green' => 0x70, 'blue' => 0xD6 ),
		'palegoldenrod'        => array( 'red' => 0xEE, 'green' => 0xE8, 'blue' => 0xAA ),
		'palegreen'            => array( 'red' => 0x98, 'green' => 0xFB, 'blue' => 0x98 ),
		'paleturquoise'        => array( 'red' => 0xAF, 'green' => 0xEE, 'blue' => 0xEE ),
		'palevioletred'        => array( 'red' => 0xDB, 'green' => 0x70, 'blue' => 0x93 ),
		'papayawhip'           => array( 'red' => 0xFF, 'green' => 0xEF, 'blue' => 0xD5 ),
		'peachpuff'            => array( 'red' => 0xFF, 'green' => 0xDA, 'blue' => 0xB9 ),
		'peru'                 => array( 'red' => 0xCD, 'green' => 0x85, 'blue' => 0x3F ),
		'pink'                 => array( 'red' => 0xFF, 'green' => 0xC0, 'blue' => 0xCB ),
		'plum'                 => array( 'red' => 0xDD, 'green' => 0xA0, 'blue' => 0xDD ),
		'powderblue'           => array( 'red' => 0xB0, 'green' => 0xE0, 'blue' => 0xE6 ),
		'rosybrown'            => array( 'red' => 0xBC, 'green' => 0x8F, 'blue' => 0x8F ),
		'royalblue'            => array( 'red' => 0x41, 'green' => 0x69, 'blue' => 0xE1 ),
		'saddlebrown'          => array( 'red' => 0x8B, 'green' => 0x45, 'blue' => 0x13 ),
		'salmon'               => array( 'red' => 0xFA, 'green' => 0x80, 'blue' => 0x72 ),
		'sandybrown'           => array( 'red' => 0xF4, 'green' => 0xA4, 'blue' => 0x60 ),
		'seagreen'             => array( 'red' => 0x2E, 'green' => 0x8B, 'blue' => 0x57 ),
		'seashell'             => array( 'red' => 0xFF, 'green' => 0xF5, 'blue' => 0xEE ),
		'sienna'               => array( 'red' => 0xA0, 'green' => 0x52, 'blue' => 0x2D ),
		'skyblue'              => array( 'red' => 0x87, 'green' => 0xCE, 'blue' => 0xEB ),
		'slateblue'            => array( 'red' => 0x6A, 'green' => 0x5A, 'blue' => 0xCD ),
		'slategray'            => array( 'red' => 0x70, 'green' => 0x80, 'blue' => 0x90 ),
		'snow'                 => array( 'red' => 0xFF, 'green' => 0xFA, 'blue' => 0xFA ),
		'springgreen'          => array( 'red' => 0x00, 'green' => 0xFF, 'blue' => 0x7F ),
		'steelblue'            => array( 'red' => 0x46, 'green' => 0x82, 'blue' => 0xB4 ),
		'tan'                  => array( 'red' => 0xD2, 'green' => 0xB4, 'blue' => 0x8C ),
		'thistle'              => array( 'red' => 0xD8, 'green' => 0xBF, 'blue' => 0xD8 ),
		'tomato'               => array( 'red' => 0xFF, 'green' => 0x63, 'blue' => 0x47 ),
		'turquoise'            => array( 'red' => 0x40, 'green' => 0xE0, 'blue' => 0xD0 ),
		'violet'               => array( 'red' => 0xEE, 'green' => 0x82, 'blue' => 0xEE ),
		'wheat'                => array( 'red' => 0xF5, 'green' => 0xDE, 'blue' => 0xB3 ),
		'whitesmoke'           => array( 'red' => 0xF5, 'green' => 0xF5, 'blue' => 0xF5 ),
		'yellowgreen'          => array( 'red' => 0x9A, 'green' => 0xCD, 'blue' => 0x32 ),
	);

	/**
	 * Convert named color to RGB.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $name Named color.
	 * @param  bool   $returnAsString Whether or not to return the RGB as an array or string, default is array.
	 * @param  string $separator      Used to separate RGB values. Applicable only if second parameter is TRUE.
	 *
	 * @return mixed  array|string  RGB will be turned as an array unless $returnAsString is TRUE.
	 */
	public static function name2rgb( $name, $returnAsString = FALSE, $separator = ',' ) {

		$name = strtolower( $name );

		$rgb = isset( self::$colors[ $name ] ) ? self::$colors[ $name ] : FALSE;

		if ( $rgb ) {

			// Returns the RGB string or associative array.
			return $returnAsString ? implode( $separator, $rgb ) : $rgb;

		} else {

			return FALSE;
		}
	}

	/**
	 * Convert named color to HEX.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $name Named color.
	 *
	 * @return mixed bool|string False if named color does not have a defined color separation.
	 */
	public static function name2hex( $name ) {

		$name = strtolower( $name );
		$rgb  = self::name2rgb( $name, TRUE );

		if ( $rgb ) {

			return self::rgb2hex2rgb( $rgb );

		} else {

			return FALSE;
		}
	}

	/**
	 * Convert HEX to RGB or RGB to HEX.
	 *
	 * @link   http://php.net/manual/en/function.hexdec.php#93835
	 * @link   http://php.net/manual/en/function.hexdec.php#99478
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $color          HEX or RGB string. HEX can be long or short with/without the has. RGB can be separated by space, comma or period.
	 * @param  bool   $returnAsString Whether or not to return the HEX to RGB as an array or string, default is array.
	 * @param  string $separator      Used to separate RGB values. Applicable only if second parameter is TRUE.
	 *
	 * @return mixed  array|bool|string HEX to RGB will be turned as an array unless $returnAsString is TRUE. RGB to HEX will be returned as a string. False on failure.
	 */
	public static function rgb2hex2rgb( $color, $returnAsString = FALSE, $separator = ',' ) {

		if ( ! $color ) return FALSE;

		$color = trim( $color );

		if ( preg_match( "/^[0-9ABCDEFabcdef\#]+$/i", $color ) ) {

			$color = preg_replace( "/[^0-9A-Fa-f]/", '', $color ); // Gets a proper hex string
			$rgb   = array();

			// If a proper hex code, convert using bitwise operation. No overhead... faster
			if ( 6 == strlen( $color ) ) {

				$colorVal     = hexdec( $color );
				$rgb['red']   = 0xFF & ( $colorVal >> 0x10 );
				$rgb['green'] = 0xFF & ( $colorVal >> 0x8 );
				$rgb['blue']  = 0xFF & $colorVal;

				// Returns the RGB string or associative array.
				$out = $returnAsString ? implode( $separator, $rgb ) : $rgb;

			// If shorthand notation, need some string manipulations.
			} elseif ( 3 == strlen( $color ) ) {

				$rgb['red']   = hexdec( str_repeat( substr( $color, 0, 1 ), 2 ) );
				$rgb['green'] = hexdec( str_repeat( substr( $color, 1, 1 ), 2 ) );
				$rgb['blue']  = hexdec( str_repeat( substr( $color, 2, 1 ), 2 ) );

				// Returns the RGB string or associative array.
				$out = $returnAsString ? implode( $separator, $rgb ) : $rgb;

			// Invalid hex color code.
			} else {

				$out = FALSE;
			}

		} elseif ( preg_match( "/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $color ) ) {

			$spr = str_replace( array( ',',' ','.' ), ':', $color );
			$e   = explode( ':', $spr );

			if ( 3 != count( $e ) ) {

				return FALSE;
			}

			$out = '#';

			for ( $i = 0; $i < 3; $i ++ ) {

				$e[ $i ] = dechex( ( $e[ $i ] <= 0 ) ? 0 : ( ( $e[ $i ] >= 255 ) ? 255 : $e[ $i ] ) );
			}

			for ( $i = 0; $i < 3; $i ++ ) {

				$out .= ( ( strlen( $e[ $i ] ) < 2 ) ? '0' : '' ) . $e[ $i ];
			}

			$out = strtoupper( $out );

		} else {

			$out = FALSE;
		}

		return $out;
	}

}

/**
 * Class cnString
 */
class cnString {

	/**
	 * Whether or not a string begins with string segment.
	 *
	 * @author  http://stackoverflow.com/users/63557/mrhus
	 * @license http://creativecommons.org/licenses/by-sa/3.0/
	 * @link    http://stackoverflow.com/a/834355
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $needle
	 * @param  string $haystack
	 *
	 * @return bool
	 */
	public static function startsWith( $needle, $haystack ) {

		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	/**
	 * Whether or not a string ends with string segment.
	 *
	 * @author  http://stackoverflow.com/users/63557/mrhus
	 * @license http://creativecommons.org/licenses/by-sa/3.0/
	 * @link    http://stackoverflow.com/a/834355
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $needle
	 * @param  string $haystack
	 *
	 * @return bool
	 */
	public static function endsWith( $needle, $haystack ) {

		return substr( $haystack, -strlen( $needle ) ) === $needle;
	}

	/**
	 * Remove prefix from string if it exists.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $needle
	 * @param  string $haystack
	 *
	 * @return string
	 */
	public static function removePrefix( $needle, $haystack ) {

		if ( substr( $haystack, 0, strlen( $needle ) ) == $needle ) {

			return substr( $haystack, strlen( $needle ) );
		}

		return $haystack;
	}

	/**
	 * General purpose function to do a little more than just white-space trimming and cleaning, it can do
	 * characters-to-replace and characters-to-replace-with. You can do the following:
	 *
	 * 1. Normalize white-spaces, so that all multiple \r, \n, \t, \r\n, \0, 0x0b, 0x20 and all control characters
	 *    can be replaced with a single space, and also trim from both ends of the string.
	 * 2. Remove all undesired characters.
	 * 3. Remove duplicates.
	 * 4. Replace multiple occurrences of characters with a character or string.
	 *
	 * @link http://pageconfig.com/post/remove-undesired-characters-with-trim_all-php
	 *
	 * @access public
	 * @since  8.1.6
	 *
	 * @uses   wp_slash()
	 *
	 * @param string      $string
	 * @param string|null $what
	 * @param string      $with
	 *
	 * @return string
	 */
	public static function replaceWhatWith( $string, $what = NULL, $with = ' ' ) {

		if ( ! is_string( $string ) ) {
			return '';
		}

		if ( is_null( $what ) ) {

			//	Character      Decimal      Use
			//	"\0"            0           Null Character
			//	"\t"            9           Tab
			//	"\n"           10           New line
			//	"\x0B"         11           Vertical Tab
			//	"\r"           13           New Line in Mac
			//	" "            32           Space

			$what = "\x00-\x20";    //all white-spaces and control chars
		}

		return trim( preg_replace( "/[" . wp_slash( $what ) . "]+/u", $with, $string ), $what );
	}

	/**
	 * Normalize a string. Replace all occurrence of one or more spaces with a single space, remove control characters
	 * and trim whitespace from both ends.
	 *
	 * @access public
	 * @since  8.1.6
	 *
	 * @param string $string The string to normalize.
	 *
	 * @return string
	 */
	public static function normalize( $string ) {

		return cnString::replaceWhatWith( $string );
	}

	/**
	 * Create excerpt from the supplied string.
	 *
	 * NOTE: The `more` string will be inserted before the last HTML tag if one exists.
	 *       If not, it'll be appended to the end of the string.
	 *       If the length is set `p`, the `more` string will not be appended.
	 *
	 * NOTE: The length maybe exceeded in attempt to end the excerpt at the end of a sentence.
	 *
	 * @todo  If the string contains HTML tags, those too will be counted when determining whether or not to append the `more` string. This should be fixed.
	 *
	 * Filters:
	 *   cn_excerpt_length       => change the default excerpt length of 55 words.
	 *   cn_excerpt_more         => change the default more string of &hellip;
	 *   cn_excerpt_allowed_tags => change the allowed HTML tags.
	 *   cn_entry_excerpt        => change returned excerpt
	 *
	 * Credit:
	 * @link http://wordpress.stackexchange.com/a/141136
	 *
	 * @access public
	 * @since  8.1.5
	 * @static
	 *
	 * @param  string  $string String to create the excerpt from.
	 * @param  array   $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type int    $length       The length, number of words, of the excerpt to create.
	 *                                If set to `p` the excerpt will be the first paragraph, no word limit.
	 *                                Default: 55.
	 *     @type string $more         The string appended to the end of the excerpt when $length is exceeded.
	 *                                Default: &hellip
	 *     @type array  $allowed_tags An array containing the permitted tags.
	 * }
	 *
	 * @return string
	 */
	public static function excerpt( $string, $atts = array() ) {

		if ( empty( $string ) || ! is_string( $string ) ) {
			return '';
		}

		$defaults = array(
			'length'       => apply_filters( 'cn_excerpt_length', 55 ),
			'more'         => apply_filters( 'cn_excerpt_more', __( '&hellip;', 'connections' ) ),
			'allowed_tags' => apply_filters(
				'cn_excerpt_allowed_tags',
				array(
					'style',
					'span',
					'br',
					'em',
					'strong',
					'i',
					'ul',
					'ol',
					'li',
					'a',
					'p',
					'img',
					'video',
					'audio',
				)
			),
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Save a copy of the raw text for the filter.
		$raw = $string;

		// Whether or not to append the more string.
		// This is only true if the word count is is more than the word length limit.
		// This is not set if length is set to `p`.
		$appendMore = FALSE;

		// Strip all shortcode from the text.
		$string = strip_shortcodes( $string );

		$string = str_replace( ']]>', ']]&gt;', $string );

		if ( 'p' === $atts['length'] ) {

			$excerpt = substr( $string, 0, strpos( $string, '</p>' ) + 4 );

		} else {

			$string  = self::stripTags( $string, FALSE, '<' . implode( '><', $atts['allowed_tags'] ) . '>' );
			$tokens  = array();
			$excerpt = '';
			$count   = 0;

			// Divide the string into tokens; HTML tags, or words, followed by any whitespace
			preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $string, $tokens );

			foreach ( $tokens[0] as $token ) {

				if ( $count >= $atts['length'] && preg_match( '/[\?\.\!]\s*$/uS', $token ) ) {

					// Limit reached, continue until ? . or ! occur at the end.
					$excerpt .= trim( $token );

					// If the length limit was reached, append the more string.
					$appendMore = TRUE;

					break;
				}

				// Add words to complete sentence.
				$count++;

				// Append what's left of the token.
				$excerpt .= $token;
			}

		}

		/** @noinspection PhpInternalEntityUsedInspection */
		$excerpt = trim( force_balance_tags( $excerpt ) );

		// No need to append the more string if the excerpted string matches the original string.
		if ( trim( $string ) == $excerpt ) {

			$appendMore = FALSE;
		}

		$lastCloseTag = strrpos( $excerpt, '</' );
		$lastSpace    = strrpos( $excerpt, ' ' );

		// Determine if the string ends with a HTML tag or word.
		if ( ( ! preg_match( '/[\s\?\.\!]$/', $excerpt ) ) &&
		     ( FALSE !== $lastCloseTag && ( FALSE !== $lastSpace && $lastCloseTag > $lastSpace ) ) ) {

			// Inside last HTML tag
			if ( $appendMore ) {
				$excerpt = substr_replace( $excerpt, $atts['more'], $lastCloseTag, 0 );
			}

		} else {

			// After the content
			if ( $appendMore ) {
				$excerpt .= $atts['more'];
			}
		}

		return apply_filters( 'cn_excerpt', $excerpt, $raw, $atts );
	}

	/**
	 * Properly strip all HTML tags including script and style.
	 *
	 * This differs from strip_tags() because it removes the contents of
	 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
	 * will return 'something'. wp_strip_all_tags will return ''
	 *
	 * NOTE: This is the Connections equivalent of @see wp_strip_all_tags() in WordPress core ../wp-includes/formatting.php
	 *
	 * This differs from @see wp_strip_all_tags() in that is adds the `$allowed_tags` param to be passed to `strip_tags()`.
	 *
	 * @access public
	 * @since  8.5.22
	 * @static
	 *
	 * @param string $string        String containing HTML tags
	 * @param bool   $remove_breaks Optional. Whether to remove left over line breaks and white space chars
	 * @param string  $allowed_tags Optional. String of tags which will not be stripped.
	 *
	 * @return string The processed string.
	 */
	public static function stripTags( $string, $remove_breaks = FALSE, $allowed_tags = '' ) {

		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
		$string = strip_tags( $string, $allowed_tags );

		if ( $remove_breaks ) {

			$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
		}

		return trim( $string );
	}

	/**
	 * Truncates string.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ellipsis if the text is longer than length.
	 *
	 * Filters:
	 *   cn_excerpt_length       => change the default excerpt length of 55 words.
	 *   cn_excerpt_more         => change the default more string of &hellip;
	 *   cn_excerpt_allowed_tags => change the allowed HTML tags.
	 *   cn_entry_excerpt        => change returned excerpt
	 *
	 * Credit:
	 * @link http://book.cakephp.org/3.0/en/core-libraries/text.html#truncating-text
	 *
	 * @access public
	 * @since  8.5.3
	 * @static
	 *
	 * @param string $string String to truncate.
	 * @param array  $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type int    $length       The length, number of characters to limit the string to.
	 *     @type string $more         The string appended to the end of the excerpt when $length is exceeded.
	 *                                Default: &hellip
	 *     @type bool   $exact        If FALSE, the truncation will occur at the first whitespace after the point at which $length is exceeded.
	 *                                Default: false
	 *     @type bool   $html         If TRUE, HTML tags will be respected and will not be cut off.
	 *                                Default: true
	 *     @type array  $allowed_tags An array containing the permitted tags.
	 * }
	 *
	 * @return string
	 */
	public static function truncate( $string, $atts = array() ) {

		$defaults = array(
			'length'       => apply_filters( 'cn_excerpt_length', 55 ),
			'more'         => apply_filters( 'cn_excerpt_more', __( '&hellip;', 'connections' ) ),
			'exact'        => FALSE,
			'html'         => TRUE,
			'allowed_tags' => apply_filters(
				'cn_excerpt_allowed_tags',
				array(
					'style',
					'br',
					'em',
					'strong',
					'i',
					'ul',
					'ol',
					'li',
					'a',
					'p',
					'img',
					'video',
					'audio',
				)
			),
		);

		if ( ! empty( $defaults['html'] ) && 'utf-8' === strtolower( mb_internal_encoding() ) ) {

			$defaults['ellipsis'] = "\xe2\x80\xa6";
		}

		$atts = wp_parse_args( $atts, $defaults );

		// Strip all shortcode from the text.
		$string = strip_shortcodes( $string );

		// Strip escaped shortcodes.
		$string = str_replace( ']]>', ']]&gt;', $string );

		if ( $atts['html'] ) {

			if ( mb_strlen( preg_replace( '/<.*?>/', '', $string ) ) <= $atts['length'] ) {

				return $string;
			}

			$totalLength = mb_strlen( strip_tags( $atts['more'] ) );
			$openTags    = array();
			$truncate    = '';

			$string = strip_tags( $string, '<' . implode( '><', $atts['allowed_tags'] ) . '>' );

			preg_match_all( '/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $string, $tags, PREG_SET_ORDER );

			foreach ( $tags as $tag ) {

				if ( ! preg_match( '/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2] ) ) {

					if ( preg_match( '/<[\w]+[^>]*>/s', $tag[0] ) ) {

						array_unshift( $openTags, $tag[2] );

					} elseif ( preg_match( '/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag ) ) {

						$pos = array_search( $closeTag[1], $openTags );

						if ( $pos !== FALSE ) {

							array_splice( $openTags, $pos, 1 );
						}
					}

				}

				$truncate .= $tag[1];
				$contentLength = mb_strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3] ) );

				if ( $contentLength + $totalLength > $atts['length'] ) {

					$left           = $atts['length'] - $totalLength;
					$entitiesLength = 0;

					if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE ) ) {

						foreach ( $entities[0] as $entity ) {

							if ( $entity[1] + 1 - $entitiesLength <= $left ) {

								$left --;
								$entitiesLength += mb_strlen( $entity[0] );

							} else {

								break;
							}
						}
					}

					$truncate .= mb_substr( $tag[3], 0, $left + $entitiesLength );
					break;

				} else {

					$truncate .= $tag[3];
					$totalLength += $contentLength;
				}

				if ( $totalLength >= $atts['length'] ) {
					break;
				}

			}

		} else {

			if ( mb_strlen( $string ) <= $atts['length'] ) {

				return $string;
			}

			$truncate = mb_substr( $string, 0, $atts['length'] - mb_strlen( $atts['more'] ) );
		}

		if ( ! $atts['exact'] ) {

			$spacepos = mb_strrpos( $truncate, ' ' );

			if ( $atts['html'] ) {

				$truncateCheck = mb_substr( $truncate, 0, $spacepos );
				$lastOpenTag   = mb_strrpos( $truncateCheck, '<' );
				$lastCloseTag  = mb_strrpos( $truncateCheck, '>' );

				if ( $lastOpenTag > $lastCloseTag ) {

					preg_match_all( '/<[\w]+[^>]*>/s', $truncate, $lastTagMatches );

					$lastTag  = array_pop( $lastTagMatches[0] );
					$spacepos = mb_strrpos( $truncate, $lastTag ) + mb_strlen( $lastTag );
				}

				$bits = mb_substr( $truncate, $spacepos );

				preg_match_all( '/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER );

				if ( ! empty( $droppedTags ) ) {

					if ( ! empty( $openTags ) ) {

						foreach ( $droppedTags as $closingTag ) {

							if ( ! in_array( $closingTag[1], $openTags ) ) {

								array_unshift( $openTags, $closingTag[1] );
							}
						}

					} else {

						foreach ( $droppedTags as $closingTag ) {

							$openTags[] = $closingTag[1];
						}
					}
				}
			}

			$truncate = mb_substr( $truncate, 0, $spacepos );

			// If truncate still empty, then we don't need to count ellipsis in the cut.
			if ( 0 === mb_strlen( $truncate ) ) {

				$truncate = mb_substr( $string, 0, $atts['length'] );
			}
		}

		$truncate .= $atts['more'];

		if ( $atts['html'] ) {

			foreach ( $openTags as $tag ) {

				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	/**
	 * Generate a more truly "random" alpha-numeric string.
	 *
	 * NOTE:  If @see openssl_random_pseudo_bytes() does not exist, this will silently fallback to
	 *        @see cnString::quickRandom().
	 *
	 * Function borrowed from Laravel 4.2
	 * @link https://github.com/laravel/framework/blob/4.2/src/Illuminate/Support/Str.php
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param  int $length
	 *
	 * @return string|WP_Error Random string on success, WP_Error on failure.
	 */
	public static function random( $length = 16 ) {

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {

			$bytes = openssl_random_pseudo_bytes( $length * 2 );

			if ( FALSE === $bytes ) {

				return new WP_Error( 'general_random_string', __( 'Unable to generate random string.', 'connections' ) );
			}

			return substr( str_replace( array( '/', '+', '=' ), '', base64_encode( $bytes ) ), 0, $length );
		}

		return self::quickRandom( $length );
	}

	/**
	 * Generate a "random" alpha-numeric string.
	 *
	 * Should not be considered sufficient for cryptography, etc.
	 *
	 * Function borrowed from Laravel 5.1
	 * @link https://github.com/laravel/framework/blob/5.1/src/Illuminate/Support/Str.php#L270
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param  int $length
	 *
	 * @return string
	 */
	public static function quickRandom( $length = 16 ) {

		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return substr( str_shuffle( str_repeat( $pool, $length ) ), 0, $length );
	}
}

/**
 * Class cnFunction.
 *
 * A collection of useful functions.
 *
 * @access public
 * @since  8.2
 */
class cnFunction {

	/**
	 * Determine if supplied array is a multidimensional array or not.
	 *
	 * @access public
	 * @since  8.5.19
	 * @static
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public static function isDimensionalArray( array $value ) {

		return ! ( count( $value ) === count( $value, COUNT_RECURSIVE ) );
	}

	/**
	 * Recursively implode a multi-dimensional array.
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @param string $glue
	 * @param array  $pieces
	 *
	 * @return string
	 */
	public static function implodeDeep( $glue, $pieces ) {

		$implode = array();

		if ( ! is_array( $pieces ) ) {

			$pieces = array( $pieces );
		}

		foreach ( $pieces as $piece ) {

			if ( is_array( $piece ) ) {

				$implode[] = self::implodeDeep( $glue, $piece );

			} else {

				$implode[] = $piece;
			}
		}

		return implode( $glue, $implode );
	}

	/**
	 * Clean up an array, comma- or space-separated list of IDs.
	 *
	 * @access public
	 * @since  8.2.9
	 * @static
	 *
	 * @param string|array $list
	 *
	 * @param string       $delimiters The characters in which to split the supplied string. Should be preg_split() safe.
	 *                                 Default: '\s,' This will split strings delimited with comma and spaces to an array.
	 *
	 * @return array
	 */
	public static function parseStringList( &$list, $delimiters = '\s,' ) {

		// Convert to an array if the supplied list is not.
		if ( ! is_array( $list ) ) {

			$list = preg_split( '/[' . $delimiters . ']+/', $list );
		}

		// Remove NULL, FALSE and empty strings (""), but leave values of 0 (zero).
		$list = array_filter( $list, 'strlen' );

		// Cleanup any excess whitespace.
		$list = array_map( 'trim', $list );

		// Return only unique values.
		return array_unique( $list );
	}

	/**
	 * Wrapper method for @see json_decode().
	 *
	 * On success this will return the decoded JSON. On error, it'll return an instance of @see WP_Error()
	 * with the result of @see json_last_error().
	 *
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @param string $json  The data to decode.
	 * @param bool   $assoc When TRUE, returned objects will be converted into associative arrays.
	 * @param int    $depth Recursion depth.
	 *
	 * @return array|mixed|WP_Error
	 */
	public static function decodeJSON( $json, $assoc = FALSE, $depth = 512 ) {

		$data = json_decode( $json, $assoc, $depth );

		switch ( json_last_error() ) {

			case JSON_ERROR_NONE:
				$result = $data;
				break;

			case JSON_ERROR_DEPTH:
				$result = new WP_Error( 'json_decode_error', __( 'Maximum stack depth exceeded.', 'connections' ) );
				break;

			case JSON_ERROR_STATE_MISMATCH:
				$result = new WP_Error( 'json_decode_error', __( 'Underflow or the modes mismatch.', 'connections' ) );
				break;

			case JSON_ERROR_CTRL_CHAR:
				$result = new WP_Error( 'json_decode_error', __( 'Unexpected control character found.', 'connections' ) );
				break;

			case JSON_ERROR_SYNTAX:
				$result = new WP_Error( 'json_decode_error', __( 'Syntax error, malformed JSON.', 'connections' ) );
				break;

			case JSON_ERROR_UTF8:
				$result = new WP_Error( 'json_decode_error', __( 'Malformed UTF-8 characters, possibly incorrectly encoded.', 'connections' ) );
				break;

			default:
				$result = new WP_Error( 'json_decode_error', __( 'Unknown error.', 'connections' ) );
				break;
		}

		return $result;
	}

	/**
	 * Escapes HTML attribute value or array of attribute values.
	 *
	 * @access public
	 * @since  8.5.18
	 * @static
	 *
	 * @param array|string $attr
	 *
	 * @param string $glue
	 *
	 * @return array|string
	 */
	public static function escAttributeDeep( $attr, $glue = ' ' ) {

		if ( is_array( $attr ) ) {

			// Ensure all IDs are positive integers.
			$attr = array_map( 'esc_attr', $attr );

			// Remove any empty array values.
			$attr = array_filter( $attr );

			$attr = implode( $glue, $attr );

		} else {

			$attr = esc_attr( $attr );
		}

		return $attr;
	}

	/**
	 * Dump a variable to the error_log file.
	 *
	 * @link https://www.justinsilver.com/technology/writing-to-the-php-error_log-with-var_dump-and-print_r/
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param null $object
	 */
	public static function var_dump_error_log( $object = NULL ) {

		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	}
}

/**
 * Class cnSiteShot
 */
class cnSiteShot {

	/**
	 * The provider API URL.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * var string
	 */
	const API = '//s0.wordpress.com/mshots/v1/';

	/**
	 * The URL to take a screenshot of.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * The width of the screenshot.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var int
	 */
	private $width = 0;

	/**
	 * Whether or not the screenshot should link to the URL.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var bool
	 */
	private $link = TRUE;

	/**
	 * The string applied to the <img> or <a> title attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * The string applied to the <a> alt attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $alt = '';

	/**
	 * Whether or not to add the nofollow rel attribute to the link.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var bool
	 */
	private $follow = FALSE;

	/**
	 * The link target attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $target = '';

	/**
	 * The string/HTML to be prepended to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $before = '';

	/**
	 * The string/HTML to be appended to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $after = '';

	/**
	 * Whether or not to echo or return the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var bool
	 */
	private $return = FALSE;

	/**
	 * Set up the options.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param array $atts
	 */
	public function __construct( $atts = array() ) {

		$defaults = array(
			'url'    => 'connections-pro.com',
			'width'  => 200,
			'link'   => TRUE,
			'title'  => '',
			'alt'    => '',
			'target' => '',
			'follow' => FALSE,
			'before' => '',
			'after'  => '',
			'return' => FALSE,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		$validTargets = array( '_blank', '_self', '_parent', '_top' );

		$this->setURL( $atts['url'] );
		$this->setWidth( $atts['width'] );
		$this->link = is_bool( $atts['link'] ) ? $atts['link'] : TRUE;
		$this->setTitle( $atts['title'] );
		$this->setAlt( $atts['alt'] );
		$this->follow = is_bool( $atts['follow'] ) ? $atts['follow'] : FALSE;
		$this->target = in_array( $atts['target'], $validTargets ) ? $atts['target'] : '_blank';
		$this->before = is_string( $atts['before'] ) && 0 < strlen( $atts['before'] ) ? $atts['before'] : '';
		$this->after  = is_string( $atts['after'] ) && 0 < strlen( $atts['after'] ) ? $atts['after'] : '';
		$this->return = is_bool( $atts['return'] ) ? $atts['return'] : FALSE;
	}

	/**
	 * Set the URL to create a screenshot of.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $url
	 */
	public function setURL( $url ) {

		// If the http protocol is not part of the url, add it.
		$this->url = cnURL::prefix( $url );
	}

	/**
	 * Get the URL of site to take a screenshot of.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @return string|WP_Error
	 */
	public function getURL() {

		if ( FALSE === filter_var( $this->url, FILTER_VALIDATE_URL ) ) {

			return new WP_Error( 'invalid_url', __( 'Invalid URL.', 'connections' ) );
		}

		return esc_url_raw( $this->url );
	}

	/**
	 * The size of the screenshot to request from the provider.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $size
	 *
	 * @return array
	 */
	public function setSize( $size ) {

		// Set the image size; These string values match the valid size for http://www.shrinktheweb.com
		switch ( $size ) {

			case 'mcr':
				$this->setWidth( 75 );
				$height = 56;
				break;

			case 'tny':
				$this->setWidth( 90 );
				$height = 68;
				break;

			case 'vsm':
				$this->setWidth( 100 );
				$height = 75;
				break;

			case 'sm':
				$this->setWidth( 120 );
				$height = 90;
				break;

			case 'lg':
				$this->setWidth( 200 );
				$height = 150;
				break;

			case 'xlg':
				$this->setWidth( 320 );
				$height = 240;
				break;

			default:
				$this->setWidth( 200 );
				$height = 150;
				break;
		}

		return array(
			'width'  => $this->width,
			'height' => $height,
		);
	}

	/**
	 * Set the image width.
	 *
	 * @access private
	 * @since  8.2.5
	 *
	 * @param int $width
	 */
	private function setWidth( $width ) {

		$this->width = absint( $width );
	}

	/**
	 * The string to set the <a> title attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $title
	 */
	public function setTitle( $title ) {

		$this->title = is_string( $title ) && 0 < strlen( $title ) ? cnSanitize::field( 'attribute', $title ) : '';

	}

	/**
	 * The string to set the <a> or <img> alt attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $alt
	 */
	public function setAlt( $alt ) {

		$this->alt = is_string( $alt ) && 0 < strlen( $alt ) ? cnSanitize::field( 'attribute', $alt ) : '';
	}

	/**
	 * Create the provider API request URL.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @return string|WP_Error
	 */
	private function getSource() {

		if ( 0 == strlen( $this->url ) || empty( $this->width ) ) {

			return new WP_Error( 'no_url_or_width', __( 'No URL or width.', 'connections' ) );
		}

		return sprintf( '%1$s%2$s?w=%3$d', self::API, urlencode( $this->url ), $this->width );
	}

	/**
	 * The string/HTML to prepend to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $before
	 */
	public function setBefore( $before ) {

		$this->before = is_string( $before ) && 0 < strlen( $before ) ? $before : '';
	}

	/**
	 * The string/HTML to append to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $after
	 */
	public function setAfter( $after ) {

		$this->after = is_string( $after ) && 0 < strlen( $after ) ? $after : '';
	}

	/**
	 * Render the HTML for the screenshot.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @return string
	 */
	public function render() {

		$imageURI = $this->getSource();
		$url      = $this->getURL();

		if ( is_wp_error( $imageURI ) ) {

			$html = '<p class="cn-error">' . implode( '</p><p class="cn-error">', $imageURI->get_error_messages() ) . '</p>';

		} elseif ( is_wp_error( $url ) ) {

			$html = '<p class="cn-error">' . implode( '</p><p class="cn-error">', $url->get_error_messages() ) . '</p>';

		} else {

			$image = sprintf(
				'<img class="cn-screenshot" src="%1$s" %2$s %3$s width="%4$d"/>',
				$imageURI,
				$this->alt ? 'alt="' . $this->alt . '"' : '',
				! $this->link && $this->title ? 'title="' . $this->title . '"' : '',
				$this->width
			);

			$image = cnString::normalize( $image );

			if ( $this->link ) {

				$link = sprintf(
					'<a class="url" href="%1$s"%2$s %3$s target="%4$s">%5$s</a>',
					$url,
					$this->title ? ' title="' . $this->title . '"' : '',
					$this->follow ? '' : 'rel="nofollow"',
					$this->target,
					$image
				);

				$html = cnString::normalize( $link );

			} else {

				$html = $image;
			}
		}

		$html = $this->before . $html . $this->after . PHP_EOL;

		if ( ! $this->return ) echo $html;
		return $html;
	}
}
