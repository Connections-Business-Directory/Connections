<?php
/**
 * Define the core email address table.
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
//use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\ForeignModel;

use Connections\DB\Models\Entry;

/**
 * Class Email
 *
 * @package Connections\DB\Tables
 */
class Email extends Table {

	/**
	 * @var array
	 */
	protected $primary_keys = array( 'entry_id' );

	/**
	 * Phone constructor.
	 */
	public function __construct() {

		if ( ! defined( 'CN_ENTRY_EMAIL_TABLE' ) ) {

			/** @var string CN_ENTRY_EMAIL_TABLE */
			define( 'CN_ENTRY_EMAIL_TABLE', $this->get_prefix() . $this->get_slug() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {

		return "{$this->get_prefix()}connections_email_ironbound";
	}

	/**
	 * @return string
	 */
	public function get_slug() {

		return 'email';
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
			'address'    => new StringBased( 'TINYTEXT', 'address', array( 'NOT NULL' ), array() ),
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
			'address'    => '',
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
