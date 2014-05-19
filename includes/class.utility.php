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
	 * The permitted tags can be suppled in an array.
	 *
	 * @TODO: Finish the code needed to support the $permittedTags array.
	 *
	 * @param string $string
	 * @param bool $allowHTML [optional]
	 * @param array $permittedTags [optional]
	 * @return string
	 */
	public function sanitizeString( $string, $allowHTML = FALSE, $permittedTags = NULL ) {
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
	 * @param string $value
	 * @return boolean
	 */
	public static function toBoolean( &$value ) {

		switch ( strtolower( $value ) ) {
			case 'yes':
				$value = TRUE;
				break;

			case 'no':
				$value = FALSE;
				break;

			case 'true':
				$value = TRUE;
				break;

			case 'false':
				$value = FALSE;
				break;

			case '1':
				$value = TRUE;
				break;

			case '0':
				$value = FALSE;
				break;

			default:
				$value = NULL;
				break;
		}

		return $value;
	}

	/**
	 * Return localized Yes or No.
	 *
	 * @author Alex Rabe (http://alexrabe.de/)
	 * @since 0.7.1.6
	 *
	 * @param bool $bool
	 * @return return 'Yes' | 'No'
	 */
	public function toYesNo( $bool ) {
		if( $bool ) {
			return __('Yes', 'connections');
		} else {
			return __('No', 'connections');
		}
	}

	/**
	 * JSON encode objects and arrays.
	 *
	 * @access public
	 * @since 0.8
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
	 * @param  mixed   $value The value to decode.
	 * @param  boolean $array [optional] Whether or not the JSON decoded value should an object or an associative array.
	 *
	 * @return mixed
	 */
	public static function maybeJSONdecode( $value, $array = TRUE ) {

		if ( ! is_string( $value ) || strlen( $value ) == 0 ) {

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
}

class cnValidate {

	public function attributesArray( $defaults, $untrusted ) {

		if ( ! is_array( $defaults ) || ! is_array( $untrusted ) ) return $defaults;

		$intersect  = array_intersect_key( $untrusted, $defaults ); // Get data for which is in the valid fields.
		$difference = array_diff_key( $defaults, $untrusted ); // Get default data which is not supplied.

		return array_merge( $intersect, $difference ); // Merge the results. Contains only valid fields of all defaults.
	}

	/**
	 * Validate the supplied URL.
	 *
	 * return: 1 is returned if good (check for >0 or ==1)
	 * return: 0 is returned if syntax is incorrect
	 * return: -1 is returned if syntax is correct, but url/file does not exist
	 *
	 * @author Luke America
	 * @url http://wpcodesnippets.info/blog/two-useful-php-validation-functions.html
	 * @param string $url
	 * @param bool $check_exists [optional]
	 * @return int
	 */
	public function url( $url , $check_exists = TRUE )
	{
		/**********************************************************************
		 Copyright © 2011 Gizmo Digital Fusion (http://wpCodeSnippets.info)
		 you can redistribute and/or modify this code under the terms of the
		 GNU GPL v2: http://www.gnu.org/licenses/gpl-2.0.html
		**********************************************************************/

		 // add http:// (here AND in the referenced $url), if needed
		if (!$url) {return false;}
		if (strpos($url, ':') === false) {$url = 'http://' . $url;}
		// auto-correct backslashes (here AND in the referenced $url)
		$url = str_replace('\\', '/', $url);

		// convert multi-byte international url's by stripping multi-byte chars
		$url_local = urldecode($url) . ' ';

		if ( function_exists( 'mb_strlen' ) )
		{
			$len = mb_strlen($url_local);
			if ($len !== strlen($url_local))
			{
				$convmap = array(0x0, 0x2FFFF, 0, 0xFFFF);
				$url_local = mb_decode_numericentity($url_local, $convmap, 'UTF-8');
			}
		}

		$url_local = trim($url_local);

		// now, process pre-encoded MBI's
		$regex = '#&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);#i';
		$url_test = preg_replace($regex, '$1', htmlentities($url_local, ENT_QUOTES, 'UTF-8'));
		if ($url_test != '') {$url_local = $url_test;}

		// test for bracket-enclosed IP address (IPv6) and modify for further testing
		preg_match('#(?<=\[)(.*?)(?=\])#i', $url, $matches);
		if ( isset($matches[0]) && $matches[0] )
		{
			$ip = $matches[0];
			if (!preg_match('/^([0-9a-f\.\/:]+)$/', strtolower($ip))) {return false;}
			if (substr_count($ip, ':') < 2) {return false;}
			$octets = preg_split('/[:\/]/', $ip);
			foreach ($octets as $i) {if (strlen($i) > 4) {return false;}}
			$ip_adj = 'x' . str_replace(':', '_', $ip) . '.com';
			$url_local = str_replace('[' . $ip . ']', $ip_adj, $url_local);
		}

		// test for IP address (IPv4)
		$regex = "#^(https?|ftp|news|file)\:\/\/";
		$regex .= "([0-9]{1,3}\.[0-9]{1,3}\.)#";
		if (preg_match($regex, $url_local))
		{
			$regex = "#^(https?|ftps)\:\/\/";
			$regex .= "([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})#";
			if (!preg_match($regex, $url_local)) {return false;}
			$seg = preg_split('/[.\/]/', $url_local);
			if (($seg[2] > 255) || ($seg[3] > 255) || ($seg[4] > 255) || ($seg[5] > 255)) {return false;}
		}

		// patch for wikipedia which can have a 2nd colon in the url
		if (strpos(strtolower($url_local), 'wikipedia'))
		{
			$pos = strpos($url_local, ':');
			$url_left = substr($url_local, 0, $pos + 1);
			$url_right = substr($url_local, $pos + 1);
			$url_right = str_replace(':', '_', $url_right);
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
		$is_valid = preg_match($regex, $url_local) > 0;

		// final check for a TLD suffix
		if ($is_valid)
		{
			$url_test = str_replace('-', '_', $url_local);
			$regex = '#^(.*?//)*([\w\.\d]*)(:(\d+))*(/*)(.*)$#';
			preg_match($regex, $url_test, $matches);
			$is_valid = preg_match('#^(.+?)\.+[0-9a-z]{2,4}$#i', $matches[2]) > 0;
		}

		// check if the url/file exists
		if (($check_exists) && ($is_valid))
		{
			$status = array();
			$url_test = str_replace(' ', '%20', $url);
			$handle = curl_init($url_test);
			curl_setopt($handle, CURLOPT_HEADER, true);
			curl_setopt($handle, CURLOPT_NOBODY, true);
			curl_setopt($handle, CURLOPT_FAILONERROR, true);
			curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($handle, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			preg_match('/HTTP\/.* ([0-9]+) .*/', curl_exec($handle) , $status);
			if ($status[1] == 200) {$is_valid = true;}
			else {$is_valid = -1;}
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
	 * @param string $email
	 * @param bool $check_mx [optional]
	 * @return
	 */
	public function email( $email , $check_mx = TRUE )
	{
		/**********************************************************************
		 Copyright © 2011 Gizmo Digital Fusion (http://wpCodeSnippets.info)
		 you can redistribute and/or modify this code under the terms of the
		 GNU GPL v2: http://www.gnu.org/licenses/gpl-2.0.html
		**********************************************************************/

		// check syntax
		$email = trim($email);
		$regex = '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
		$is_valid = preg_match($regex, $email, $matches);

		// NOTE: Windows servers do not offer checkdnsrr until PHP 5.3.
		// So we create the function, if it doesn't exist.
		if(!function_exists('checkdnsrr'))
		{
			function checkdnsrr($host_name='', $rec_type='')
			{
				if(!empty($host_name))
				{
					if(!$rec_type) {$rec_type = 'MX';}
					exec("nslookup -type=$rec_type $host_name", $result);

					// Check each line to find the one that starts with the host name.
					foreach ($result as $line)
					{
						if(eregi("^$host_name", $line))
						{
							return true;
						}
					}
					return false;
				}
				return false;
			}
		}

		// check that the server exists and is setup to handle email accounts
		if (($is_valid) && ($check_mx))
		{
			$at_index = strrpos($email, '@');
			$domain = substr($email, $at_index + 1);
			if (!(checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')))
			{
				$is_valid = -1;
			}
		}

		// exit
		return $is_valid;
	}

	/**
	 * Will return TRUE?FALSE based on current user capability or privacy setting if the user is not logged in to WordPress.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.2.0
	 * @param string $visibilty
	 * @return bool
	 */
	public static function userPermitted($visibilty)
	{
		global $connections;

		if ( is_user_logged_in() )
		{
			if ( ! empty($visibilty) )
			{
				if ( current_user_can('connections_view_public') && $visibilty == 'public' ) return TRUE;
				if ( current_user_can('connections_view_private') && $visibilty == 'private' ) return TRUE;
				if ( ( current_user_can('connections_view_unlisted') && is_admin() ) && $visibilty == 'unlisted' ) return TRUE;

				// If we get here, return FALSE
				return FALSE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			if ( $visibilty == 'unlisted' ) return FALSE;

			if ( $connections->options->getAllowPublic() && $visibilty == 'public' ) return TRUE;
			if ( $connections->options->getAllowPublicOverride() && $visibilty == 'public' ) return TRUE;
			if ( $connections->options->getAllowPrivateOverride() && $visibilty == 'private' ) return TRUE;

			// If we get here, return FALSE
			return FALSE;
		}

		// Shouldn't happen....
		return FALSE;
	}
}

class cnURL {

	/**
	* @param $url
	*     The URL to encode
	*
	* @return
	*     A string containing the encoded URL with disallowed
	*     characters converted to their percentage encodings.
	*
	* @link http://publicmind.in/blog/url-encoding/
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
	 * Take a URL and see if it's prefixed with a protocol, if it's not then it will add the default prefix to the start of the string.
	 *
	 * @access public
	 * @since 0.8
	 * @param  string $url
	 * @param  string $protocal
	 * @return string
	 */
	public static function prefix( $url, $protocal = 'http://' ) {

		if ( ! preg_match( "~^(?:f|ht)tps?://~i", $url ) ) {

			$url = $protocal . $url;
		}

		return $url;
	}

	/**
	 * Create the URL to a file from its absolute system path.
	 *
	 * @access public
	 * @since  0.8
	 * @uses   untrailingslashit()
	 * @uses   wp_normalize_path()
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
	 * @access private
	 * @since 0.7.3
	 * @global $wp_rewrite
	 * @global $post
	 * @uses is_admin()
	 * @uses wp_parse_args()
	 * @uses get_option()
	 * @uses in_the_loop()
	 * @uses is_page()
	 * @uses trailingslashit()
	 * @uses get_permalink()
	 * @uses add_query_arg()
	 * @uses is_front_page()
	 * @param array $atts
	 * @return string
	 */
	public static function permalink( $atts ) {
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
			'return'     => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Get the directory home page ID.
		$homeID = $atts['force_home'] ? cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) : $atts['home_id'];

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		// Create the permalink base based on context where the entry is being displayed.
		if ( in_the_loop() && is_page() ) {

			// Only slash it when using pretty permalinks.
			$permalink = $wp_rewrite->using_permalinks() ? trailingslashit( get_permalink( $homeID ) ) : get_permalink( $homeID );

		} else {

			// If using pretty permalinks get the directory home page ID and slash it, otherwise just add the page_id to the query string.
			if ( $wp_rewrite->using_permalinks() ) {

				$permalink = trailingslashit( get_permalink( $homeID ) );

			} else {

				$permalink = add_query_arg( array( 'page_id' => $homeID, 'p' => FALSE  ), get_permalink() );
			}

		}

		// If on the front page, add the query var for the page ID.
		if ( ! $wp_rewrite->using_permalinks() && is_front_page() ) $permalink = add_query_arg( 'page_id' , $post->ID, $permalink );

		if ( ! empty( $atts['class'] ) ) $piece['class']       = 'class="' . $atts['class'] .'"';
		// if ( ! empty( $atts['slug'] ) ) $piece['id']        = 'id="' . $atts['slug'] .'"';
		if ( ! empty( $atts['title'] ) ) $piece['title']       = 'title="' . $atts['title'] .'"';
		if ( ! empty( $atts['target'] ) ) $piece['target']     = 'target="' . $atts['target'] .'"';
		if ( ! $atts['follow'] ) $piece['follow']              = 'rel="nofollow"';
		if ( ! empty( $atts['rel'] ) ) $piece['rel']           = 'rel="' . $atts['rel'] .'"';
		if ( ! empty( $atts['on_click'] ) ) $piece['on_click'] = 'onClick="' . $atts['on_click'] .'"';

		switch ( $atts['type'] ) {

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
					$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . urlencode( $atts['slug'] ) . '/edit' );
				} else {
					$permalink = add_query_arg( array( 'cn-entry-slug' => $atts['slug'] , 'cn-view' => 'detail', 'cn-process' => 'edit' ) , $permalink );
				}

				break;

			case 'name':

				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . urlencode( $atts['slug'] ) );
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
		}

		$piece['href'] = 'href="' . esc_url( $permalink ) . '"';

		$out = '<a ' . implode(' ', $piece) . '>' . $atts['text'] . '</a>';

		// The class.seo.file is only loaded in the frontend; do not attempt to add the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( TRUE );

		if ( $atts['return'] ) return $out;
		echo $out;
	}
}

class cnUtility {

	/**
	 * Get user IP.
	 *
	 * @access public
	 * @since  0.8
	 * @link   http://stackoverflow.com/a/6718472
	 *
	 * @return string The IP address.
	 */
	public static function getIP() {

		foreach ( array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key ) {

			if ( array_key_exists( $key, $_SERVER ) === TRUE ) {

				foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) {

					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== FALSE ) {

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
	 * @link   http://stackoverflow.com/a/15875555
	 * @link   http://www.php.net/manual/en/function.uniqid.php#94959
	 *
	 * @return string
	 */
	public static function getUUID() {

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {

			$data    = openssl_random_pseudo_bytes(16);

			$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 ); // set version to 0010
			$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10

			return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );

		} else {

			return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
			);

		}

	}
}
