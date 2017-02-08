<?php
/**
 * Define the core addresses table.
 *
 * CREDIT: Iron Bound Designs
 * @link https://github.com/iron-bound-designs/IronBound-DB
 *
 * @author   Steven A. Zahm
 * @category Database
 * @package  Connections\DB\Tables
 * @since    8.5.34
 */

namespace Connections\DB\Tables;

use IronBound\DB\Manager;

use Connections\DB\Table\Table;
//use IronBound\DB\Table\TimestampedTable;

//use IronBound\DB\Table\Column\Column;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\ForeignModel;

use Connections\DB\Models\Entry;

/**
 * Class Address
 *
 * @package Connections\DB\Tables
 */
class Address extends Table {

	/**
	 * @var array
	 */
	protected $primary_keys = array( 'entry_id' );

	/**
	 * Address constructor.
	 */
	public function __construct() {

		if ( ! defined( 'CN_ENTRY_ADDRESS_TABLE' ) ) {

			/** @var string CN_ENTRY_ADDRESS_TABLE */
			define( 'CN_ENTRY_ADDRESS_TABLE', $this->get_prefix() . $this->get_slug() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {

		return "{$this->get_prefix()}connections_address_ironbound";
	}

	/**
	 * @return string
	 */
	public function get_slug() {

		return 'address';
	}

	/**
	 * @inheritdoc
	 */
	public function get_version() {

		return 0.4;
	}

	/**
	 * @return array
	 */
	public function get_columns() {

		return array(
			'id'         => new IntegerBased( 'BIGINT', 'id', array( 'UNSIGNED', 'NOT NULL', 'AUTO_INCREMENT' ), array( 20 ) ),
			//'entry_id'   => new IntegerBased( 'BIGINT', 'entry_id', array( 'UNSIGNED', 'NOT NULL', 'DEFAULT 0' ), array( 20 ) ),
			'entry_id'   => new ForeignModel( 'entry_id', get_class( new Entry() ), Manager::get( 'entry' ) ),
			'order'      => new IntegerBased( 'TINYINT', 'order', array( 'UNSIGNED', 'NOT NULL', 'DEFAULT 0' ), array() ),
			'preferred'  => new IntegerBased( 'TINYINT', 'preferred', array( 'UNSIGNED', 'NOT NULL', 'DEFAULT 0' ), array() ),
			'type'       => new StringBased( 'TINYTEXT', 'type', array( 'NOT NULL' ), array() ),
			'line_1'     => new StringBased( 'TINYTEXT', 'line_1', array( 'NOT NULL' ), array() ),
			'line_2'     => new StringBased( 'TINYTEXT', 'line_2', array( 'NOT NULL' ), array() ),
			'line_3'     => new StringBased( 'TINYTEXT', 'line_3', array( 'NOT NULL' ), array() ),
			'line_4'     => new StringBased( 'TINYTEXT', 'line_4', array( 'NOT NULL' ), array() ),
			'district'   => new StringBased( 'TINYTEXT', 'district', array( 'NOT NULL' ), array() ),
			'county'     => new StringBased( 'TINYTEXT', 'county', array( 'NOT NULL' ), array() ),
			'city'       => new StringBased( 'TINYTEXT', 'city', array( 'NOT NULL' ), array() ),
			'state'      => new StringBased( 'TINYTEXT', 'state', array( 'NOT NULL' ), array() ),
			'zipcode'    => new StringBased( 'TINYTEXT', 'zipcode', array( 'NOT NULL' ), array() ),
			'country'    => new StringBased( 'TINYTEXT', 'country', array( 'NOT NULL' ), array() ),
			'latitude'   => new DecimalBased( 'DECIMAL', 'latitude', array( 'DEFAULT NULL' ), array( '15,12' ) ),
			'longitude'  => new DecimalBased( 'DECIMAL', 'longitude', array( 'DEFAULT NULL' ), array( '15,12' ) ),
			'visibility' => new StringBased( 'TINYTEXT', 'visibility', array( 'NOT NULL' ), array() ),
		);
	}

	/**
	 * return array
	 */
	public function get_column_defaults() {

		return array(
			'id'         => 0,
			'entry_id'   => 0,
			'order'      => 0,
			'preferred'  => 0,
			'type'       => '',
			'line_1'     => '',
			'line_2'     => '',
			'line_3'     => '',
			'line_4'     => '',
			'district'   => '',
			'county'     => '',
			'city'       => '',
			'state'      => '',
			'zipcode'    => '',
			'country'    => '',
			'latitude'   => NULL,
			'longitude'  => NULL,
			'visibility' => 'public',
		);
	}

	/**
	 * @return string
	 */
	public function get_primary_key() {

		return 'id';
	}
}
