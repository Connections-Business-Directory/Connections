<?php
/**
 * Table column Timestamp class.
 *
 * CREDIT: Iron Bound Designs
 *
 * @link       https://github.com/iron-bound-designs/IronBound-DB
 *
 * @author     Steven A. Zahm
 * @category   Database
 * @package    Connections\DB\Table\Column
 * @since      8.5.34
 */

namespace Connections\DB\Table\Column;

use IronBound\DB\Table\Column\DateTime;

/**
 * Class Timestamp
 *
 * @package Connections\DB\Table\Column
 */
class Timestamp extends DateTime {

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {

		return 'TIMESTAMP';
	}

	///**
	// * @inheritDoc
	// */
	//public function convert_raw_to_value( $raw ) {
	//
	//	if ( empty( $raw ) ) {
	//
	//		return NULL;
	//	}
	//
	//	if ( $raw instanceof \DateTime || ( interface_exists( 'DateTimeInterface' ) && $raw instanceof \DateTimeInterface ) ) {
	//
	//		$date = clone $raw;
	//		$date->setTimezone( new \DateTimeZone( 'UTC' ) );
	//
	//		return $date;
	//	}
	//
	//	try {
	//
	//		return new \DateTime( $raw, new \DateTimeZone( 'UTC' ) );
	//
	//	} catch ( \Exception $e ) {
	//
	//		return NULL;
	//	}
	//}

	///**
	// * @inheritDoc
	// */
	//public function prepare_for_storage( $value ) {
	//
	//	if ( empty( $value ) ) {
	//
	//		return NULL;
	//
	//	} elseif ( is_numeric( $value ) ) {
	//
	//		$value = new \DateTime( "@$value", new \DateTimeZone( 'UTC' ) );
	//
	//	} elseif ( is_string( $value ) ) {
	//
	//		$value = new \DateTime( $value, new \DateTimeZone( 'UTC' ) );
	//
	//	} elseif ( is_object( $value ) && ! $value instanceof \DateTime && ! $value instanceof \DateTimeInterface ) {
	//
	//		throw new \Exception( 'Non DateTime object encountered while preparing value.', $this, $value );
	//
	//	} elseif ( is_object( $value ) ) {
	//
	//		$value->setTimezone( new \DateTimeZone( 'UTC' ) );
	//	}
	//
	//	return $value->format( 'Y-m-d H:i:s' );
	//}
}
