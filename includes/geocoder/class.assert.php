<?php

namespace Connections_Directory\Geocoder;

use InvalidArgumentException;

/**
 * Class Assert
 *
 * @package Connections_Directory\Geocoder
 * @license MIT License
 * @since 8.26
 */
class Assert {

	/**
	 * @since 8.26
	 *
	 * @param float  $value
	 * @param string $message
	 */
	public static function latitude( $value, $message = '' ) {

		self::float( $value, $message );

		if ( $value < - 90 || $value > 90 ) {
			throw new InvalidArgumentException(
				sprintf( $message ? : 'Latitude should be between -90 and 90. Got: %s', $value )
			);
		}
	}

	/**
	 * @since 8.26
	 *
	 * @param float  $value
	 * @param string $message
	 */
	public static function longitude( $value, $message = '' ) {

		self::float( $value, $message );

		if ( $value < - 180 || $value > 180 ) {
			throw new InvalidArgumentException(
				sprintf( $message ? : 'Longitude should be between -180 and 180. Got: %s', $value )
			);
		}
	}

	/**
	 * @since 8.26
	 *
	 * @param mixed  $value
	 * @param string $message
	 */
	public static function notNull( $value, $message = '' ) {

		if ( null === $value ) {
			throw new InvalidArgumentException( sprintf( $message ? : 'Value cannot be null' ) );
		}
	}

	/**
	 * @since 8.26
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	private static function typeToString( $value ) {

		return is_object( $value ) ? get_class( $value ) : gettype( $value );
	}

	/**
	 * @since 8.26
	 *
	 * @param $value
	 * @param $message
	 */
	private static function float( $value, $message ) {

		if ( ! is_float( $value ) ) {
			throw new InvalidArgumentException(
				sprintf( $message ? : 'Expected a float. Got: %s', self::typeToString( $value ) )
			);
		}
	}
}
