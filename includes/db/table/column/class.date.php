<?php
/**
 * Table column Date class.
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
use IronBound\DB\Exception\InvalidDataForColumnException;

/**
 * Class Date
 *
 * @package Connections\DB\Table\Column
 */
class Date extends DateTime {

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'DATE';
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( empty( $value ) ) {
			return null;
		} elseif ( is_numeric( $value ) ) {
			$value = new \DateTime( "@$value", new \DateTimeZone( 'UTC' ) );
		} elseif ( is_string( $value ) ) {
			$value = str_replace( '-', '/', $value );
			$value = new \DateTime( $value, new \DateTimeZone( 'UTC' ) );
		} elseif ( is_object( $value ) && ! $value instanceof \DateTime && ! $value instanceof \DateTimeInterface ) {
			throw new InvalidDataForColumnException(
				'Non \DateTime object encountered while preparing value.', $this, $value
			);
		} elseif ( is_object( $value ) ) {
			$value->setTimezone( new \DateTimeZone( 'UTC' ) );
		}

		return $value->format( 'Y-m-d' );
	}
}
