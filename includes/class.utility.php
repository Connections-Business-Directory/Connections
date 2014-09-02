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

		// Already a bool, return it.
		if ( is_bool( $value ) ) return $value;

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

	/**
	 * Ensures that any hex color is properly hashed.
	 * Otherwise, returns value unaltered.
	 *
	 * This function is borrowed from the class_wp_customize_manager.php
	 * file in WordPress core.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @param  string $color
	 *
	 * @return mixed  string | string
	 */
	public static function maybeHashHEXColor( $color ) {

		if ( $unhashed = cnSanitize::hexColorNoHash( $color ) ) {

			return '#' . $unhashed;
		}

		return $color;
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

	/**
	 * Convert a value within one range to a value within another range, maintaining ratio.
	 *
	 * Coverted Python script from:
	 * @url    http://stackoverflow.com/a/15537393
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @param  float  $x    Original value.
	 * @param  float  $oMin Old minimum.
	 * @param  float  $oMax Old maximum.
	 * @param  float  $nMin New minimum.
	 * @param  float  $nMax New maximum.
	 * @return mixed        bool | float Return false on failure, or return new value within new range.
	 */
	public static function remapRange( $x, $oMin, $oMax, $nMin, $nMax ) {

		#range check
		if ($oMin == $oMax) {
			return FALSE;
		}

		if ($nMin == $nMax) {
			return FALSE;
		}

		#check reversed input range
		$reverseInput = FALSE;
		$oldMin = min( $oMin, $oMax );
		$oldMax = max( $oMin, $oMax );
		if (! $oldMin == $oMin) {
			$reverseInput = TRUE;
		}

		#check reversed output range
		$reverseOutput = FALSE;
		$newMin = min( $nMin, $nMax );
		$newMax = max( $nMin, $nMax );
		if (! $newMin == $nMin) {
			$reverseOutput = TRUE;
		}

		$portion = ($x-$oldMin)*($newMax-$newMin)/($oldMax-$oldMin);

		if ($reverseInput)
			$portion = ($oldMax-$x)*($newMax-$newMin)/($oldMax-$oldMin);

		$result = $portion + $newMin;
		if ($reverseOutput)
			$result = $newMax - $portion;

		return $result;

	}
}

class cnLog extends WP_Error {

	private $startTime = 0;
	private $lastBenchTime = 0;

	public function add( $code, $message, $data = '' ) {

		if ( ! defined( 'WP_DEBUG' ) || WP_DEBUG === FALSE ) {

			$this->errors = array();
			$this->error_data = array();

			$this->errors[ 'wp_debug' ][]   = __( 'To enable logging, WP_DEBUG must defined and set to TRUE.', 'connections' );
			$this->error_data[ 'wp_debug' ] = '';
		}

		$execTime = sprintf( '%.6f', microtime(TRUE) - $this->startTime);
		$tick     = sprintf( '%.6f', 0 );

		if ( $this->lastBenchTime > 0 ) {

			$tick = sprintf( '%.6f', microtime(TRUE) - $this->lastBenchTime );
		}

		$this->lastBenchTime = microtime(TRUE);

		$this->errors[ $code ][] = "[$execTime : $tick]: $message";

		if ( ! empty( $data ) ) {

			$this->error_data[ $code ] = $data;
		}

	}

	public function __toString() {

		return implode( PHP_EOL, $this->get_error_messages() );
	}
}


class cnColor {

	/**
	 * An array of named colors as the key with the value being the RGB values of said color.
	 *
	 * @access private
	 * @since  8.1
	 * @url    http://psoug.org/snippet/CSS_Colornames_to_RGB_values_415.htm
	 * @var    array
	 */
	private static $colors = array(

		//  Colors  as  they  are  defined  in  HTML  3.2
		"black"=>array( "red"=>0x00,  "green"=>0x00,  "blue"=>0x00),
		"maroon"=>array( "red"=>0x80,  "green"=>0x00,  "blue"=>0x00),
		"green"=>array( "red"=>0x00,  "green"=>0x80,  "blue"=>0x00),
		"olive"=>array( "red"=>0x80,  "green"=>0x80,  "blue"=>0x00),
		"navy"=>array( "red"=>0x00,  "green"=>0x00,  "blue"=>0x80),
		"purple"=>array( "red"=>0x80,  "green"=>0x00,  "blue"=>0x80),
		"teal"=>array( "red"=>0x00,  "green"=>0x80,  "blue"=>0x80),
		"gray"=>array( "red"=>0x80,  "green"=>0x80,  "blue"=>0x80),
		"silver"=>array( "red"=>0xC0,  "green"=>0xC0,  "blue"=>0xC0),
		"red"=>array( "red"=>0xFF,  "green"=>0x00,  "blue"=>0x00),
		"lime"=>array( "red"=>0x00,  "green"=>0xFF,  "blue"=>0x00),
		"yellow"=>array( "red"=>0xFF,  "green"=>0xFF,  "blue"=>0x00),
		"blue"=>array( "red"=>0x00,  "green"=>0x00,  "blue"=>0xFF),
		"fuchsia"=>array( "red"=>0xFF,  "green"=>0x00,  "blue"=>0xFF),
		"aqua"=>array( "red"=>0x00,  "green"=>0xFF,  "blue"=>0xFF),
		"white"=>array( "red"=>0xFF,  "green"=>0xFF,  "blue"=>0xFF),

		//  Additional  colors  as  they  are  used  by  Netscape  and  IE
		"aliceblue"=>array( "red"=>0xF0,  "green"=>0xF8,  "blue"=>0xFF),
		"antiquewhite"=>array( "red"=>0xFA,  "green"=>0xEB,  "blue"=>0xD7),
		"aquamarine"=>array( "red"=>0x7F,  "green"=>0xFF,  "blue"=>0xD4),
		"azure"=>array( "red"=>0xF0,  "green"=>0xFF,  "blue"=>0xFF),
		"beige"=>array( "red"=>0xF5,  "green"=>0xF5,  "blue"=>0xDC),
		"blueviolet"=>array( "red"=>0x8A,  "green"=>0x2B,  "blue"=>0xE2),
		"brown"=>array( "red"=>0xA5,  "green"=>0x2A,  "blue"=>0x2A),
		"burlywood"=>array( "red"=>0xDE,  "green"=>0xB8,  "blue"=>0x87),
		"cadetblue"=>array( "red"=>0x5F,  "green"=>0x9E,  "blue"=>0xA0),
		"chartreuse"=>array( "red"=>0x7F,  "green"=>0xFF,  "blue"=>0x00),
		"chocolate"=>array( "red"=>0xD2,  "green"=>0x69,  "blue"=>0x1E),
		"coral"=>array( "red"=>0xFF,  "green"=>0x7F,  "blue"=>0x50),
		"cornflowerblue"=>array( "red"=>0x64,  "green"=>0x95,  "blue"=>0xED),
		"cornsilk"=>array( "red"=>0xFF,  "green"=>0xF8,  "blue"=>0xDC),
		"crimson"=>array( "red"=>0xDC,  "green"=>0x14,  "blue"=>0x3C),
		"darkblue"=>array( "red"=>0x00,  "green"=>0x00,  "blue"=>0x8B),
		"darkcyan"=>array( "red"=>0x00,  "green"=>0x8B,  "blue"=>0x8B),
		"darkgoldenrod"=>array( "red"=>0xB8,  "green"=>0x86,  "blue"=>0x0B),
		"darkgray"=>array( "red"=>0xA9,  "green"=>0xA9,  "blue"=>0xA9),
		"darkgreen"=>array( "red"=>0x00,  "green"=>0x64,  "blue"=>0x00),
		"darkkhaki"=>array( "red"=>0xBD,  "green"=>0xB7,  "blue"=>0x6B),
		"darkmagenta"=>array( "red"=>0x8B,  "green"=>0x00,  "blue"=>0x8B),
		"darkolivegreen"=>array( "red"=>0x55,  "green"=>0x6B,  "blue"=>0x2F),
		"darkorange"=>array( "red"=>0xFF,  "green"=>0x8C,  "blue"=>0x00),
		"darkorchid"=>array( "red"=>0x99,  "green"=>0x32,  "blue"=>0xCC),
		"darkred"=>array( "red"=>0x8B,  "green"=>0x00,  "blue"=>0x00),
		"darksalmon"=>array( "red"=>0xE9,  "green"=>0x96,  "blue"=>0x7A),
		"darkseagreen"=>array( "red"=>0x8F,  "green"=>0xBC,  "blue"=>0x8F),
		"darkslateblue"=>array( "red"=>0x48,  "green"=>0x3D,  "blue"=>0x8B),
		"darkslategray"=>array( "red"=>0x2F,  "green"=>0x4F,  "blue"=>0x4F),
		"darkturquoise"=>array( "red"=>0x00,  "green"=>0xCE,  "blue"=>0xD1),
		"darkviolet"=>array( "red"=>0x94,  "green"=>0x00,  "blue"=>0xD3),
		"deeppink"=>array( "red"=>0xFF,  "green"=>0x14,  "blue"=>0x93),
		"deepskyblue"=>array( "red"=>0x00,  "green"=>0xBF,  "blue"=>0xFF),
		"dimgray"=>array( "red"=>0x69,  "green"=>0x69,  "blue"=>0x69),
		"dodgerblue"=>array( "red"=>0x1E,  "green"=>0x90,  "blue"=>0xFF),
		"firebrick"=>array( "red"=>0xB2,  "green"=>0x22,  "blue"=>0x22),
		"floralwhite"=>array( "red"=>0xFF,  "green"=>0xFA,  "blue"=>0xF0),
		"forestgreen"=>array( "red"=>0x22,  "green"=>0x8B,  "blue"=>0x22),
		"gainsboro"=>array( "red"=>0xDC,  "green"=>0xDC,  "blue"=>0xDC),
		"ghostwhite"=>array( "red"=>0xF8,  "green"=>0xF8,  "blue"=>0xFF),
		"gold"=>array( "red"=>0xFF,  "green"=>0xD7,  "blue"=>0x00),
		"goldenrod"=>array( "red"=>0xDA,  "green"=>0xA5,  "blue"=>0x20),
		"greenyellow"=>array( "red"=>0xAD,  "green"=>0xFF,  "blue"=>0x2F),
		"honeydew"=>array( "red"=>0xF0,  "green"=>0xFF,  "blue"=>0xF0),
		"hotpink"=>array( "red"=>0xFF,  "green"=>0x69,  "blue"=>0xB4),
		"indianred"=>array( "red"=>0xCD,  "green"=>0x5C,  "blue"=>0x5C),
		"indigo"=>array( "red"=>0x4B,  "green"=>0x00,  "blue"=>0x82),
		"ivory"=>array( "red"=>0xFF,  "green"=>0xFF,  "blue"=>0xF0),
		"khaki"=>array( "red"=>0xF0,  "green"=>0xE6,  "blue"=>0x8C),
		"lavender"=>array( "red"=>0xE6,  "green"=>0xE6,  "blue"=>0xFA),
		"lavenderblush"=>array( "red"=>0xFF,  "green"=>0xF0,  "blue"=>0xF5),
		"lawngreen"=>array( "red"=>0x7C,  "green"=>0xFC,  "blue"=>0x00),
		"lemonchiffon"=>array( "red"=>0xFF,  "green"=>0xFA,  "blue"=>0xCD),
		"lightblue"=>array( "red"=>0xAD,  "green"=>0xD8,  "blue"=>0xE6),
		"lightcoral"=>array( "red"=>0xF0,  "green"=>0x80,  "blue"=>0x80),
		"lightcyan"=>array( "red"=>0xE0,  "green"=>0xFF,  "blue"=>0xFF),
		"lightgoldenrodyellow"=>array( "red"=>0xFA,  "green"=>0xFA,  "blue"=>0xD2),
		"lightgreen"=>array( "red"=>0x90,  "green"=>0xEE,  "blue"=>0x90),
		"lightgrey"=>array( "red"=>0xD3,  "green"=>0xD3,  "blue"=>0xD3),
		"lightpink"=>array( "red"=>0xFF,  "green"=>0xB6,  "blue"=>0xC1),
		"lightsalmon"=>array( "red"=>0xFF,  "green"=>0xA0,  "blue"=>0x7A),
		"lightseagreen"=>array( "red"=>0x20,  "green"=>0xB2,  "blue"=>0xAA),
		"lightskyblue"=>array( "red"=>0x87,  "green"=>0xCE,  "blue"=>0xFA),
		"lightslategray"=>array( "red"=>0x77,  "green"=>0x88,  "blue"=>0x99),
		"lightsteelblue"=>array( "red"=>0xB0,  "green"=>0xC4,  "blue"=>0xDE),
		"lightyellow"=>array( "red"=>0xFF,  "green"=>0xFF,  "blue"=>0xE0),
		"limegreen"=>array( "red"=>0x32,  "green"=>0xCD,  "blue"=>0x32),
		"linen"=>array( "red"=>0xFA,  "green"=>0xF0,  "blue"=>0xE6),
		"mediumaquamarine"=>array( "red"=>0x66,  "green"=>0xCD,  "blue"=>0xAA),
		"mediumblue"=>array( "red"=>0x00,  "green"=>0x00,  "blue"=>0xCD),
		"mediumorchid"=>array( "red"=>0xBA,  "green"=>0x55,  "blue"=>0xD3),
		"mediumpurple"=>array( "red"=>0x93,  "green"=>0x70,  "blue"=>0xD0),
		"mediumseagreen"=>array( "red"=>0x3C,  "green"=>0xB3,  "blue"=>0x71),
		"mediumslateblue"=>array( "red"=>0x7B,  "green"=>0x68,  "blue"=>0xEE),
		"mediumspringgreen"=>array( "red"=>0x00,  "green"=>0xFA,  "blue"=>0x9A),
		"mediumturquoise"=>array( "red"=>0x48,  "green"=>0xD1,  "blue"=>0xCC),
		"mediumvioletred"=>array( "red"=>0xC7,  "green"=>0x15,  "blue"=>0x85),
		"midnightblue"=>array( "red"=>0x19,  "green"=>0x19,  "blue"=>0x70),
		"mintcream"=>array( "red"=>0xF5,  "green"=>0xFF,  "blue"=>0xFA),
		"mistyrose"=>array( "red"=>0xFF,  "green"=>0xE4,  "blue"=>0xE1),
		"moccasin"=>array( "red"=>0xFF,  "green"=>0xE4,  "blue"=>0xB5),
		"navajowhite"=>array( "red"=>0xFF,  "green"=>0xDE,  "blue"=>0xAD),
		"oldlace"=>array( "red"=>0xFD,  "green"=>0xF5,  "blue"=>0xE6),
		"olivedrab"=>array( "red"=>0x6B,  "green"=>0x8E,  "blue"=>0x23),
		"orange"=>array( "red"=>0xFF,  "green"=>0xA5,  "blue"=>0x00),
		"orangered"=>array( "red"=>0xFF,  "green"=>0x45,  "blue"=>0x00),
		"orchid"=>array( "red"=>0xDA,  "green"=>0x70,  "blue"=>0xD6),
		"palegoldenrod"=>array( "red"=>0xEE,  "green"=>0xE8,  "blue"=>0xAA),
		"palegreen"=>array( "red"=>0x98,  "green"=>0xFB,  "blue"=>0x98),
		"paleturquoise"=>array( "red"=>0xAF,  "green"=>0xEE,  "blue"=>0xEE),
		"palevioletred"=>array( "red"=>0xDB,  "green"=>0x70,  "blue"=>0x93),
		"papayawhip"=>array( "red"=>0xFF,  "green"=>0xEF,  "blue"=>0xD5),
		"peachpuff"=>array( "red"=>0xFF,  "green"=>0xDA,  "blue"=>0xB9),
		"peru"=>array( "red"=>0xCD,  "green"=>0x85,  "blue"=>0x3F),
		"pink"=>array( "red"=>0xFF,  "green"=>0xC0,  "blue"=>0xCB),
		"plum"=>array( "red"=>0xDD,  "green"=>0xA0,  "blue"=>0xDD),
		"powderblue"=>array( "red"=>0xB0,  "green"=>0xE0,  "blue"=>0xE6),
		"rosybrown"=>array( "red"=>0xBC,  "green"=>0x8F,  "blue"=>0x8F),
		"royalblue"=>array( "red"=>0x41,  "green"=>0x69,  "blue"=>0xE1),
		"saddlebrown"=>array( "red"=>0x8B,  "green"=>0x45,  "blue"=>0x13),
		"salmon"=>array( "red"=>0xFA,  "green"=>0x80,  "blue"=>0x72),
		"sandybrown"=>array( "red"=>0xF4,  "green"=>0xA4,  "blue"=>0x60),
		"seagreen"=>array( "red"=>0x2E,  "green"=>0x8B,  "blue"=>0x57),
		"seashell"=>array( "red"=>0xFF,  "green"=>0xF5,  "blue"=>0xEE),
		"sienna"=>array( "red"=>0xA0,  "green"=>0x52,  "blue"=>0x2D),
		"skyblue"=>array( "red"=>0x87,  "green"=>0xCE,  "blue"=>0xEB),
		"slateblue"=>array( "red"=>0x6A,  "green"=>0x5A,  "blue"=>0xCD),
		"slategray"=>array( "red"=>0x70,  "green"=>0x80,  "blue"=>0x90),
		"snow"=>array( "red"=>0xFF,  "green"=>0xFA,  "blue"=>0xFA),
		"springgreen"=>array( "red"=>0x00,  "green"=>0xFF,  "blue"=>0x7F),
		"steelblue"=>array( "red"=>0x46,  "green"=>0x82,  "blue"=>0xB4),
		"tan"=>array( "red"=>0xD2,  "green"=>0xB4,  "blue"=>0x8C),
		"thistle"=>array( "red"=>0xD8,  "green"=>0xBF,  "blue"=>0xD8),
		"tomato"=>array( "red"=>0xFF,  "green"=>0x63,  "blue"=>0x47),
		"turquoise"=>array( "red"=>0x40,  "green"=>0xE0,  "blue"=>0xD0),
		"violet"=>array( "red"=>0xEE,  "green"=>0x82,  "blue"=>0xEE),
		"wheat"=>array( "red"=>0xF5,  "green"=>0xDE,  "blue"=>0xB3),
		"whitesmoke"=>array( "red"=>0xF5,  "green"=>0xF5,  "blue"=>0xF5),
		"yellowgreen"=>array( "red"=>0x9A,  "green"=>0xCD,  "blue"=>0x32)
	);

	/**
	 * Covert named color to RGB.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @param  string $name Named color.
	 * @param  bool   $returnAsString Whether or not to return the RGB as an array or string, default is array.
	 * @param  string $separator      Used to separate RGB values. Applicable only if second parameter is TRUE.
	 *
	 * @return mixed  array | string  RGB will be turned as an array unless $returnAsString is TRUE.
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
	 * Covert named color to HEX.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @param  string $name Named color.
	 *
	 * @return string
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
	 * @access public
	 * @since  8.1
	 * @static
	 * @url    http://php.net/manual/en/function.hexdec.php#93835
	 * @url    http://php.net/manual/en/function.hexdec.php#99478
	 * @param  string $color          HEX or RGB string. HEX can be long or short with/without the has. RGB can be separated by space, comma or period.
	 * @param  bool   $returnAsString Whether or not to return the HEX to RGB as an array or string, default is array.
	 * @param  string $separator      Used to separate RGB values. Applicable only if second parameter is TRUE.
	 *
	 * @return mixed  array | string  HEX to RGB will be turned as an array unless $returnAsString is TRUE. RGB to HEX will be returned as a string.
	 */
	public static function rgb2hex2rgb( $color, $returnAsString = FALSE, $separator = ',' ) {

		if ( ! $color ) return FALSE;

		$color = trim( $color );
		$out   = FALSE;

		if ( preg_match( "/^[0-9ABCDEFabcdef\#]+$/i", $color ) ) {

			$color = preg_replace( "/[^0-9A-Fa-f]/", '', $color ); // Gets a proper hex string
			$rgb   = array();

			// If a proper hex code, convert using bitwise operation. No overhead... faster
			if ( strlen( $color ) == 6 ) {

				$colorVal     = hexdec( $color );
				$rgb['red']   = 0xFF & ( $colorVal >> 0x10 );
				$rgb['green'] = 0xFF & ( $colorVal >> 0x8 );
				$rgb['blue']  = 0xFF & $colorVal;

				// Returns the RGB string or associative array.
				$out = $returnAsString ? implode( $separator, $rgb ) : $rgb;

			// If shorthand notation, need some string manipulations.
			} elseif ( strlen( $color ) == 3 ) {

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

			if ( count( $e ) != 3 ) {

				return FALSE;
			}

			$out = '#';

			for( $i = 0; $i<3 ; $i++ )
				$e[ $i ] = dechex( ( $e[ $i ] <= 0 ) ? 0 : ( ( $e[ $i ] >= 255 ) ? 255 : $e[ $i ] ) );

			for( $i = 0; $i<3; $i++ )
				$out .= ( ( strlen( $e[ $i ] ) < 2) ? '0' : '' ) . $e[ $i ];

			$out = strtoupper( $out );

		} else {

			$out = FALSE;
		}

		return $out;
	}

}

class cnString {

	/**
	 * Whether or not a string begins with string segment.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 * @url    http://stackoverflow.com/a/834355
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
	 * @access public
	 * @since  8.1
	 * @static
	 * @url    http://stackoverflow.com/a/834355
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

}
