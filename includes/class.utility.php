<?php

use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_string;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	 * @deprecated 9.11
	 *
	 * @param string   $str
	 * @param int|null $len
	 *
	 * @return string
	 */
	public static function numHash( $str, $len = null ) {

		_deprecated_function( __METHOD__, '9.11', '_string::toNumericHash()' );

		return _string::toNumericHash( $str, $len );
	}

	/**
	 * Get user IP.
	 *
	 * @since 0.8
	 * @deprecated 9.11
	 *
	 * @link http://stackoverflow.com/a/6718472
	 *
	 * @return string The IP address.
	 */
	public static function getIP() {

		_deprecated_function( __METHOD__, '9.11', '_::getIP()' );

		return _::getIP();
	}

	/**
	 * Returns v4 compliant UUID.
	 *
	 * @since 0.8
	 * @deprecated 9.11
	 *
	 * @link http://stackoverflow.com/a/15875555
	 * @link http://www.php.net/manual/en/function.uniqid.php#94959
	 *
	 * @return string
	 */
	public static function getUUID() {

		_deprecated_function( __METHOD__, '9.11', '_::getUUID()' );

		return _::getUUID();
	}

	/**
	 * Convert a value within one range to a value within another range, maintaining ratio.
	 *
	 * Converted Python script from:
	 * @link http://stackoverflow.com/a/15537393
	 *
	 * @since 8.1
	 * @deprecated 9.11
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

		_deprecated_function( __METHOD__, '9.11', '_::remapRange()' );

		return _::remapRange( $x, $oMin, $oMax, $nMin, $nMax );
	}
}
