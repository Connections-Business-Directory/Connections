<?php
/**
 * Define the core links table.
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
 * Class Link
 *
 * @package Connections\DB\Tables
 */
class Link extends Table {

	/**
	 * @var array
	 */
	protected $primary_keys = array( 'entry_id' );

	/**
	 * Address constructor.
	 */
	public function __construct() {

		if ( ! defined( 'CN_ENTRY_LINK_TABLE' ) ) {

			/** @var string CN_ENTRY_LINK_TABLE */
			define( 'CN_ENTRY_LINK_TABLE', $this->get_prefix() . $this->get_slug() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {

		return "{$this->get_prefix()}connections_link_ironbound";
	}

	/**
	 * @return string
	 */
	public function get_slug() {

		return 'link';
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
			'title'      => new StringBased( 'TINYTEXT', 'title', array( 'NOT NULL' ), array() ),
			'url'        => new StringBased( 'TINYTEXT', 'url', array( 'NOT NULL' ), array() ),
			'target'     => new StringBased( 'TINYTEXT', 'target', array( 'NOT NULL' ), array() ),
			'follow'     => new StringBased( 'TINYINT', 'follow', array( 'UNSIGNED', 'NOT NULL', 'DEFAULT 0' ), array() ),
			'image'      => new StringBased( 'TINYINT', 'image', array( 'UNSIGNED', 'NOT NULL', 'DEFAULT 0' ), array() ),
			'logo'       => new StringBased( 'TINYINT', 'logo', array( 'UNSIGNED', 'NOT NULL', 'DEFAULT 0' ), array() ),
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
			'title'      => '',
			'url'        => '',
			'target'     => 0,
			'follow'     => 0,
			'image'      => 0,
			'logo'       => 0,
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
