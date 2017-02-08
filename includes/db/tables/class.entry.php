<?php
/**
 * Define the core entry table.
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

//use IronBound\DB\Manager;
use Connections\DB\Table\Table;
//use IronBound\DB\Table\TimestampedTable;

//use IronBound\DB\Table\Column\Column;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
//use IronBound\DB\Table\Column\ForeignModel;

use Connections\DB\Table\Column\Timestamp;

//use Connections\DB\Models\Address;

/**
 * Class Entry
 *
 * @package Connections\DB\Tables
 */
class Entry extends Table /*implements TimestampedTable*/ {

	/**
	 * Entry constructor.
	 */
	public function __construct() {

		if ( ! defined( 'CN_ENTRY_TABLE' ) ) {

			/** @var string CN_ENTRY_TABLE */
			define( 'CN_ENTRY_TABLE', $this->get_prefix() . $this->get_slug() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {

		return "{$this->get_prefix()}connections_ironbound";
	}

	/**
	 * @return string
	 */
	public function get_slug() {

		return 'entry';
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
			'id'                 => new IntegerBased( 'BIGINT', 'id', array( 'UNSIGNED', 'NOT NULL', 'AUTO_INCREMENT' ), array( 20 ) ),
			'ts'                 => new Timestamp( 'ts' ),
			'date_added'         => new StringBased( 'TINYTEXT', 'date_added', array( 'NOT NULL' ), array() ),
			'ordo'               => new IntegerBased( 'INT', 'ordo', array( 'NOT NULL', 'DEFAULT 0' ), array( 11 ) ),
			'entry_type'         => new StringBased( 'TINYTEXT', 'entry_type', array( 'NOT NULL' ), array() ),
			'visibility'         => new StringBased( 'TINYTEXT', 'visibility', array( 'NOT NULL' ), array() ),
			'slug'               => new StringBased( 'TINYTEXT', 'slug', array( 'NOT NULL' ), array() ),
			'family_name'        => new StringBased( 'TINYTEXT','family_name', array( 'NOT NULL' ), array() ),
			'honorific_prefix'   => new StringBased( 'TINYTEXT', 'honorific_prefix', array( 'NOT NULL' ), array() ),
			'first_name'         => new StringBased( 'TINYTEXT', 'first_name', array( 'NOT NULL' ), array() ),
			'middle_name'        => new StringBased( 'TINYTEXT', 'middle_name', array( 'NOT NULL' ), array() ),
			'last_name'          => new StringBased( 'TINYTEXT', 'last_name', array( 'NOT NULL' ), array() ),
			'honorific_suffix'   => new StringBased( 'TINYTEXT', 'honorific_suffix', array( 'NOT NULL' ), array() ),
			'title'              => new StringBased( 'TINYTEXT', 'title', array( 'NOT NULL' ), array() ),
			'organization'       => new StringBased( 'TINYTEXT', 'organization', array( 'NOT NULL' ), array() ),
			'department'         => new StringBased( 'TINYTEXT', 'department', array( 'NOT NULL' ), array() ),
			'contact_first_name' => new StringBased( 'TINYTEXT', 'contact_first_name', array( 'NOT NULL' ), array() ),
			'contact_last_name'  => new StringBased( 'TINYTEXT', 'contact_last_name', array( 'NOT NULL' ), array() ),
			'addresses'          => new StringBased( 'LONGTEXT', 'addresses', array( 'NOT NULL' ), array() ),
			//'addresses'          => new ForeignModel( 'address', get_class( new Address() ), Manager::get( 'address' ) ),
			'phone_numbers'      => new StringBased( 'LONGTEXT', 'phone_numbers', array( 'NOT NULL' ), array() ),
			'email'              => new StringBased( 'LONGTEXT', 'email', array( 'NOT NULL' ), array() ),
			'im'                 => new StringBased( 'LONGTEXT', 'im', array( 'NOT NULL' ), array() ),
			'social'             => new StringBased( 'LONGTEXT', 'social', array( 'NOT NULL' ), array() ),
			'links'              => new StringBased( 'LONGTEXT', 'links', array( 'NOT NULL' ), array() ),
			'dates'              => new StringBased( 'LONGTEXT', 'dates', array( 'NOT NULL' ), array() ),
			'birthday'           => new StringBased( 'TINYTEXT', 'birthday', array( 'NOT NULL' ), array() ),
			'anniversary'        => new StringBased( 'TINYTEXT', 'anniversary', array( 'NOT NULL' ), array() ),
			'bio'                => new StringBased( 'LONGTEXT', 'bio', array( 'NOT NULL' ), array() ),
			'notes'              => new StringBased( 'LONGTEXT', 'notes', array( 'NOT NULL' ), array() ),
			'options'            => new StringBased( 'LONGTEXT', 'options', array( 'NOT NULL' ), array() ),
			'added_by'           => new IntegerBased( 'BIGINT', 'added_by', array( 'NOT NULL' ), array( 20 ) ),
			'edited_by'          => new IntegerBased( 'BIGINT', 'edited_by', array( 'NOT NULL' ), array( 20 ) ),
			'owner'              => new IntegerBased( 'BIGINT', 'owner', array( 'NOT NULL' ), array( 20 ) ),
			'user'               => new IntegerBased( 'BIGINT', 'user', array( 'NOT NULL' ), array( 20 ) ),
			'status'             => new StringBased( 'VARCHAR', 'status', array( 'NOT NULL' ), array( 20 ) ),
		);
	}

	/**
	 * return array
	 */
	public function get_column_defaults() {

		return array(
			'id'                 => NULL,                        // CREATE / UPDATE Only
			'ts'                 => current_time( 'mysql' ),
			'date_added'         => current_time( 'timestamp' ), // INSERT Only
			'ordo'               => 0,
			'entry_type'         => '',
			'visibility'         => 'public',
			'slug'               => '',
			'family_name'        => '',
			'honorific_prefix'   => '',
			'first_name'         => '',
			'middle_name'        => '',
			'last_name'          => '',
			'honorific_suffix'   => '',
			'title'              => '',
			'organization'       => '',
			'department'         => '',
			'contact_first_name' => '',
			'contact_last_name'  => '',
			'addresses'          => '',
			'phone_numbers'      => '',
			'email'              => '',
			'im'                 => '',
			'social'             => '',
			'links'              => '',
			'dates'              => '',
			'birthday'           => '',
			'anniversary'        => '',
			'bio'                => '',
			'notes'              => '',
			'options'            => '',
			'added_by'           => get_current_user_id(),       // INSERT Only
			'edited_by'          => get_current_user_id(),
			'owner'              => get_current_user_id(),       // INSERT Only
			'user'               => '0',
			'status'             => 'approved',
		);
	}

	/**
	 * @return string
	 */
	public function get_primary_key() {

		return 'id';
	}

	/**
	 * @return string
	 */
	//public function get_relation_key() {
	//
	//	return 'entry';
	//}

	/**
	 * Get the name of the created at column.
	 *
	 * Must correspond to a DateTime column.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	//public function get_created_at_column() {
	//
	//	return 'date_added';
	//}

	/**
	 * Get the name of the updated at column.
	 *
	 * Must correspond to a DateTime column.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	//public function get_updated_at_column() {
	//
	//	return 'ts';
	//}
}
