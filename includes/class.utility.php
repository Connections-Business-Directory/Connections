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

		$atts['permalink_root'] = $permalink;

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

				//if ( $wp_rewrite->using_permalinks() ) {
				//
				//	// The entry slug is saved in the db urlencoded so we'll expect when the permalink for entry name is
				//	// requested that the entry slug is being used so urlencode() will not be use as not to double encode it.
				//	$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . $atts['slug'] . '/edit' );
				//} else {
				//	$permalink = add_query_arg( array( 'cn-entry-slug' => $atts['slug'] , 'cn-view' => 'detail', 'cn-process' => 'edit' ) , $permalink );
				//}

				$result = Connections_Directory()->retrieve->entry( $atts['slug'] );

				if ( FALSE !== $result ) {

					$actionURL  = 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $result->id;
					$actionName = 'entry_edit_' . $result->id;
					$permalink  = admin_url( wp_nonce_url( $actionURL, $actionName ) );
				}

				break;

			case 'delete':

				$result = Connections_Directory()->retrieve->entry( $atts['slug'] );

				if ( FALSE !== $result ) {

					$actionURL  = 'admin.php?cn-action=delete_entry&id=' . $result->id;
					$actionName = 'entry_delete_' . $result->id;
					$permalink  = admin_url( wp_nonce_url( $actionURL, $actionName ) );
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
	 * @param mixed $object
	 */
	public static function var_dump_error_log( $object = NULL ) {

		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	}
}
