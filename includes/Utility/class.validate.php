<?php

use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
