<?php

use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_string;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnUtility
 */
class cnUtility {

	/**
	 * Returns v4 compliant UUID.
	 *
	 * NOTE: This method may still be in used in older versions of the CSV Import addon.
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
}
