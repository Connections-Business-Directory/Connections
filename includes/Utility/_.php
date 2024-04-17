<?php
/**
 * Utility methods.
 *
 * @since      unknown
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\_\Utility
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Utility;

use cnHTML;
use Connections_Directory\Request;
use DateTimeZone;
use WP_Error;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

/**
 * Class _
 *
 * @package Connections_Directory\Utility
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _ {

	/**
	 * Gets a short class name.
	 *
	 * @since 10.4.46
	 *
	 * @param object|string $class Class name or object.
	 *
	 * @return string
	 */
	public static function getClassShortName( $class ): string {

		return ( new \ReflectionClass( $class ) )->getShortName();
	}

	/**
	 * Checks if the current environment type is set to 'development' or 'local'.
	 *
	 * @see \WP_Site_Health::is_development_environment()
	 *
	 * @since 10.4.7
	 *
	 * @return bool
	 */
	public static function isDevelopmentEnvironment() {

		return in_array( wp_get_environment_type(), array( 'development', 'local' ), true );
	}

	/**
	 * Determine if supplied value is a positive integer.
	 *
	 * Negative integers will return `false`.
	 *
	 * @link https://stackoverflow.com/a/29018655/5351316
	 *
	 * @since 10.4.1
	 * @deprecated 10.4.7
	 *
	 * @param int|string $value Value to validate.
	 *
	 * @return bool
	 */
	public static function isPositiveInteger( $value ) {

		_deprecated_function( __METHOD__, '10.4.7', '_validate::isPositiveInteger()' );

		return _validate::isPositiveInteger( $value );
	}

	/**
	 * Determine if supplied array is a multidimensional array or not.
	 *
	 * @since 8.5.19
	 * @deprecated 9.11
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public static function isDimensionalArray( array $value ) {

		_deprecated_function( __METHOD__, '9.11', '_array::isDimensional()' );

		return _array::isDimensional( $value );
	}

	/**
	 * Recursively implode a multidimensional array.
	 *
	 * @since 8.2
	 * @deprecated 9.11
	 *
	 * @param string $glue
	 * @param array  $pieces
	 *
	 * @return string
	 */
	public static function implodeDeep( $glue, $pieces ) {

		_deprecated_function( __METHOD__, '9.11', '_array::implodeDeep()' );

		return _array::implodeDeep( $glue, $pieces );
	}

	/**
	 * Returns true if the value provided is considered "empty". Allows `0`.
	 *
	 * @since 10.4
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool
	 */
	public static function isEmpty( $value ) {
		return ( ! $value && ! is_numeric( $value ) );
	}

	/**
	 * Returns true if the value provided is considered "not empty". Allows `0`.
	 *
	 * @since 10.4
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool
	 */
	public static function notEmpty( $value ) {
		return ( $value || is_numeric( $value ) );
	}

	/**
	 * Compare desired to the current version of WordPress.
	 *
	 * @since 10.4.11
	 *
	 * @param string $required The version to compare.
	 * @param string $operator The operator.
	 *
	 * @return bool
	 */
	public static function isWPVersion( $required, $operator = '>=' ) {

		global $wp_version;

		// Strip off any -alpha, -beta, -RC, -src suffixes.
		list( $version ) = explode( '-', $wp_version );

		return version_compare( $version, $required, $operator );
	}

	/**
	 * Whether the echo the supplied value.
	 *
	 * NOTE: Ensure content is escaped!
	 *
	 * @since 10.4.66
	 *
	 * @param mixed $value     The value to echo.
	 * @param bool  $maybeEcho Whether to echo the value.
	 *
	 * @return mixed
	 */
	public static function maybeEcho( $value, bool $maybeEcho ) {

		if ( $maybeEcho ) {

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $value;
		}

		return $value;
	}

	/**
	 * Clean up an array, comma- or space-separated list of IDs.
	 *
	 * @since 8.2.9
	 *
	 * @param string|array $list
	 * @param string       $delimiters The characters in which to split the supplied string. Should be preg_split() safe.
	 *                                 Default: '\s,' This will split strings delimited with comma and spaces to an array.
	 *
	 * @return array
	 */
	public static function parseStringList( &$list, $delimiters = '\s,' ) {

		_deprecated_function( __METHOD__, '10.4.26', '_parse::stringList()' );

		return _parse::stringList( $list, $delimiters );
	}

	/**
	 * Wrapper method for @see json_decode().
	 *
	 * On success this will return the decoded JSON. On error, it'll return an instance of @see WP_Error()
	 * with the result of @see json_last_error().
	 *
	 * @since 8.3
	 *
	 * @param string $json  The data to decode.
	 * @param bool   $assoc When TRUE, returned objects will be converted into associative arrays.
	 * @param int    $depth Recursion depth.
	 *
	 * @return array|mixed|WP_Error
	 */
	public static function decodeJSON( $json, $assoc = false, $depth = 512 ) {

		$data = json_decode( $json, $assoc, $depth );

		switch ( json_last_error() ) {

			case JSON_ERROR_NONE:
				$result = $data;
				break;

			case JSON_ERROR_DEPTH:
				$result = new WP_Error( 'json_decode_error', 'Maximum stack depth exceeded.' );
				break;

			case JSON_ERROR_STATE_MISMATCH:
				$result = new WP_Error( 'json_decode_error', 'Underflow or the modes mismatch.' );
				break;

			case JSON_ERROR_CTRL_CHAR:
				$result = new WP_Error( 'json_decode_error', 'Unexpected control character found.' );
				break;

			case JSON_ERROR_SYNTAX:
				$result = new WP_Error( 'json_decode_error', 'Syntax error, malformed JSON.' );
				break;

			case JSON_ERROR_UTF8:
				$result = new WP_Error( 'json_decode_error', 'Malformed UTF-8 characters, possibly incorrectly encoded.' );
				break;

			case JSON_ERROR_RECURSION:
				$result = new WP_Error( 'json_decode_error', 'One or more recursive references in the value to be encoded.' );
				break;

			case JSON_ERROR_INF_OR_NAN:
				$result = new WP_Error( 'json_decode_error', 'One or more NAN or INF values in the value to be encoded.' );
				break;

			case JSON_ERROR_UNSUPPORTED_TYPE:
				$result = new WP_Error( 'json_decode_error', 'A value of a type that cannot be encoded was given.' );
				break;

			default:
				$result = new WP_Error( 'json_decode_error', 'Unknown error.' );
				break;
		}

		return $result;
	}

	/**
	 * JSON encode objects and arrays.
	 *
	 * @since 0.8
	 *
	 * @param mixed $value The value to maybe json_encode.
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
	 * @link https://stackoverflow.com/a/45241792/5351316
	 *
	 * @since 0.8
	 *
	 * @param mixed   $value The value to decode.
	 * @param boolean $array [optional] Whether or not the JSON decoded value should an object or an associative array.
	 *
	 * @return mixed
	 */
	public static function maybeJSONdecode( $value, $array = true ) {

		if ( ! is_string( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return 0 + $value;
		}

		if ( 2 > strlen( $value ) ) {
			return $value;
		}

		if ( 'null' === $value ) {
			return null;
		}

		if ( 'true' === $value ) {
			return true;
		}

		if ( 'false' === $value ) {
			return false;
		}

		if ( '{' !== $value[0] && '[' !== $value[0] && '"' !== $value[0] ) {
			return $value;
		}

		// A JSON encoded string will start and end with either a square bracket of curly bracket.
		// if ( ( $value[0] === '[' && $value[ strlen( $value ) - 1 ] === ']' ) || ( $value[0] === '{' && $value[ strlen( $value ) - 1 ] === '}' ) ) {
		//
		// $value = json_decode( $value, $array );
		// }

		$result = self::decodeJSON( $value, $array );

		if ( is_wp_error( $result ) ) {

			return $value;
		}

		return $result;
	}

	/**
	 * Get user IP.
	 *
	 * @link   http://stackoverflow.com/a/6718472
	 *
	 * @since 0.8
	 *
	 * @return string The IP address.
	 */
	public static function getIP() {

		foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {

			if ( true === array_key_exists( $key, $_SERVER ) ) {

				foreach ( array_map( 'trim', explode( ',', sanitize_text_field( $_SERVER[ $key ] ) ) ) as $ip ) {

					if ( false !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {

						return $ip;
					}
				}
			}
		}

		return '';
	}

	/**
	 * Returns v4 compliant UUID.
	 *
	 * @since 0.8
	 *
	 * @link http://www.php.net/manual/en/function.uniqid.php#94959
	 * @link http://stackoverflow.com/a/15875555
	 *
	 * @return string
	 */
	public static function getUUID() {

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {

			$data = openssl_random_pseudo_bytes( 16 );

			if ( false !== $data ) {

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
	 *
	 * @link http://stackoverflow.com/a/15537393
	 *
	 * @since 8.1
	 *
	 * @param float $x    Original value.
	 * @param float $oMin Old minimum.
	 * @param float $oMax Old maximum.
	 * @param float $nMin New minimum.
	 * @param float $nMax New maximum.
	 *
	 * @return bool|float Return false on failure, or return new value within new range.
	 */
	public static function remapRange( $x, $oMin, $oMax, $nMin, $nMax ) {

		// Range check.
		if ( $oMin == $oMax ) {

			return false;
		}

		if ( $nMin == $nMax ) {

			return false;
		}

		// Check reversed input range.
		$reverseInput = false;
		$oldMin       = min( $oMin, $oMax );
		$oldMax       = max( $oMin, $oMax );

		if ( ! $oldMin == $oMin ) {

			$reverseInput = true;
		}

		// Check reversed output range.
		$reverseOutput = false;
		$newMin        = min( $nMin, $nMax );
		$newMax        = max( $nMin, $nMax );

		if ( ! $newMin == $nMin ) {

			$reverseOutput = true;
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

	/**
	 * If the current request is either AJAX or REST, write the var_dump() to the PHP error log instead of outputting.
	 * The allows the use of var_dump() without corrupting the return results of an AJAX or REST request.
	 *
	 * @since 10.3
	 *
	 * @param mixed ...$value The variables to dump.
	 */
	public static function var_dump( ...$value ) {

		$request = Request::get();

		if ( $request->isAjax() || $request->isRest() ) {

				_::var_dump_to_error_log( $value );

		} else {

			var_dump( ...$value ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
		}
	}

	/**
	 * Dump variable to error log.
	 *
	 * @since 10.4.8
	 *
	 * @param mixed ...$value The variables to dump.
	 */
	public static function var_dump_to_error_log( ...$value ) {

		ob_start();

		var_dump( ...$value ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump

		$buffer = ob_get_clean();

		error_log( trim( $buffer ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Write to the error log.
	 *
	 * @since 10.4.25
	 *
	 * @param mixed  $message     The message or variable to write to the error log.
	 * @param string $destination The file path to write the error log to.
	 */
	public static function error_log( $message, $destination = null ) {

		if ( ! is_string( $message ) ) {

			ob_start();
			var_dump( $message );      //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
			$message = ob_get_clean();
		}

		$format   = 'd-M-Y H:i:s e';
		$datetime = date_create( 'now', new DateTimeZone( 'UTC' ) );

		if ( false === $datetime ) {

			$datetime = gmdate( $format, 0 );
		}

		$formattedDate = $datetime->setTimezone( wp_timezone() )->format( $format );

		$message = sprintf( '[%s] %s%s', $formattedDate, $message, PHP_EOL );

		if ( is_null( $destination ) ) {

			$destination = ini_get( 'error_log' );

			if ( false === $destination ) {

				$destination = ABSPATH . '~error.log';
			}
		}

		error_log( $message, 3, $destination ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Variable export as HTML highlighted code.
	 *
	 * @since 10.4.25
	 *
	 * @param mixed $value The variable to export highlighted. This should include the opening PHP tag.
	 * @param bool  $echo  Whether to output or return the highlighted variable.
	 *
	 * @return null|string The variable representation highlighted when the return parameter is used and evaluates to true. Otherwise, this function will return null.
	 */
	public static function highlight_var_dump( $value, $echo = false ) {

		$highlighted = highlight_string( var_export( $value, true ), true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export

		if ( false === $highlighted ) {

			$highlighted = null;
		}

		$highlighted = "<div>{$highlighted}</div>";

		if ( $echo ) {

			echo $highlighted; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $highlighted;
	}

	/**
	 * @since 10.4.46
	 */
	public static function callstack( $log = false ) {

		$trace = var_export( ( new \Exception() )->getTraceAsString(), true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		$trace = ltrim( $trace, '\'' );
		$trace = rtrim( $trace, '\'' );
		$trace = explode( '#', $trace );

		unset( $trace[0], $trace[1] );

		$trace = PHP_EOL . '#' . implode( "#\r", $trace ) . PHP_EOL;

		if ( $log ) {

			error_log( $trace ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		} else {

			echo $trace; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
