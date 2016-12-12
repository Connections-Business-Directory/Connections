<?php

/**
 * Helper class to save entry data to the db.
 *
 * @package     Connections
 * @subpackage  cnEntry : DB
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnEntry_DB
 */
class cnEntry_DB {

	/**
	 * The entry ID.
	 *
	 * @since 8.2.6
	 * @var int
	 */
	private $id;

	/**
	 * Set up the object.
	 *
	 * @access public
	 * @since  8.2.6
	 *
	 * @uses   cnEntry_DB::setEntryIDWhereClause()
	 * @uses   cnEntry_DB::setVisibilityWhereClause()
	 *
	 * @param int $id The entry ID.
	 */
	public function __construct( $id ) {

		$this->id = $id;
	}

	/**
	 * Insert an array of data objects into the db. The expected data structure is the output of @see cnEntry::getAddresses()
	 * and similar.
	 *
	 * @access public
	 * @since  8.2.6
	 *
	 * @uses   cnEntry_DB::insertRow()
	 *
	 * @param string $table  The table name in which to insert the data.
	 * @param array  $fields An array which defines the fields in the db to insert and the map to the objects property and the properties format.
	 *                       Example of structure:
	 *                       array( 'table column name' => array( 'key' => 'object property name' , 'format' => '%s|%d|%f' ) )
	 * @param array  $data   An array of data objects to insert into the table.
	 *
	 * @return array
	 */
	public function insert( $table, $fields, $data ) {

		$result = array();

		if ( ! empty( $data ) ) {

			foreach ( $data as $row ) {

				$result[] = $this->insertRow( $table, $fields, $row );
			}
		}

		return $result;
	}

	/**
	 * Insert an array of data objects into the db. The expected data structure is the output of @see cnEntry::getAddresses()
	 * and similar.
	 *
	 * The difference between this method and insert() is that this method will insert multiple rows as one database insert
	 * vs. one database insert per record.
	 *
	 * @access public
	 * @since  8.5.32
	 *
	 * @uses   cnEntry_DB::insertRow()
	 *
	 * @param string $table The table name in which to insert the data.
	 * @param array  $map   An array which defines the fields in the db to insert and the map to the objects property and the properties format.
	 *                      Example of structure:
	 *                      array( 'table column name' => array( 'key' => 'object property name' , 'format' => '%s|%d|%f' ) )
	 * @param array  $rows  An array of data objects to insert into the table.
	 *
	 * @return int|false Number of rows inserted or false on error.
	 */
	public function multisert( $table, $map, $rows ) {

		$result = FALSE;

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! empty( $rows ) ) {

			$values = array();

			foreach ( $rows as $row ) {

				$data = $this->fields( $map, $row );

				$data['value']['entry_id'] = $this->id;
				$data['format'][]          = '%d';

				$format = implode( ', ', $data['format'] );

				$values[] = $wpdb->prepare( '(' . $format . ')', array_values( $data['value'] ) );
			}

			$fields = '`' . implode( '`, `', array_keys( $map ) ) . '`' . ', `entry_id`';
			$values = implode( ', ', $values );

			$sql = "INSERT INTO `$table` ($fields) VALUES $values";

			$result = $wpdb->query( $sql );
		}

		return $result;
	}

	/**
	 * Helper function for @see cnEntry_DB::insert() which insert a single object from the supplied array of objects in to the db.
	 *
	 * @access private
	 * @since  8.2.6
	 *
	 * @global $wpdb
	 *
	 * @uses   cnEntry_DB::fields()
	 * @uses   cnEntry::getId()
	 * @uses   wpdb::insert()
	 *
	 * @param string $table  The table name in which to insert the data.
	 * @param array  $fields An array which defines the fields in the db to insert and the map to the objects property and the properties format.
	 *                       Example of structure:
	 *                       array( 'table column name' => array( 'key' => 'object property name' , 'format' => '%s|%d|%f' ) )
	 * @param array  $row    The object to insert into the table.
	 *
	 * @return int
	 */
	private function insertRow( $table, $fields, $row ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$field = $this->fields( $fields, $row );

		$field['data']['entry_id'] = $this->id;
		$field['format'][]         = '%d';

		$wpdb->insert(
			$table,
			$field['data'],
			$field['format']
		);

		return $wpdb->insert_id;
	}

	/**
	 * An array of objects to update the db. The expected data structure is the output of @see cnEntry::getAddresses()
	 * and similar.
	 *
	 * @access public
	 * @since  8.2.6
	 *
	 * @uses   cnEntry_DB::updateRow()
	 *
	 * @param string $table  The table name in which to insert the data.
	 * @param array  $fields An array which defines the fields in the db to update and the map to the objects property and the properties format.
	 *                       Example of structure:
	 *                       array( 'table column name' => array( 'key' => 'object property name' , 'format' => '%s|%d|%f' ) )
	 * @param array  $data   An array of data objects to update.
	 * @param array  $index  An array defining the unique key ID field name and its format of the data object.
	 *                       Example of structure:
	 *                       array( 'id' => array( 'key' => 'object unique id', 'format' => '%s|%d|%f' ) )
	 *
	 * @return array
	 */
	public function update( $table, $fields, $data, $index )  {

		$result = array();

		if ( ! empty( $data ) ) {

			foreach ( $data as $row ) {

				$result[] = $this->updateRow( $table, $fields, $row, $index );
			}
		}

		return $result;
	}

	/**
	 * Helper function for @see cnEntry_DB::update() which updates the db a single object from the supplied array of objects.
	 *
	 * @access private
	 * @since  8.2.6
	 *
	 * @global $wpdb
	 *
	 * @uses   cnEntry_DB::fields()
	 * @uses   cnEntry::getId()
	 * @uses   wpdb::update()
	 *
	 * @param string $table  The table name in which to insert the data.
	 * @param array  $fields An array which defines the fields in the db to update and the map to the objects property and the properties format.
	 *                       Example of structure:
	 *                       array( 'table column name' => array( 'key' => 'object property name' , 'format' => '%s|%d|%f' ) )
	 * @param array  $row    The object data to update in the table.
	 * @param array  $index  An array defining the unique key ID field name and its format of the data object.
	 *                       Example of structure:
	 *                       array( 'id' => array( 'key' => 'object unique id', 'format' => '%s|%d|%f' ) )
	 *
	 * @return array
	 */
	private function updateRow( $table, $fields, $row, $index ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$field = $this->fields( $fields, $row );
		$key   = $index['id']['key'];

		$field['data']['entry_id'] = $this->id;
		$field['format'][]         = '%d';

		if ( isset( $row->{$key} ) && ! empty( $row->{$key} ) ) {

			$where = $this->fields( $index, $row );

			$wpdb->update(
				$table,
				$field['data'],
				$where['data'],
				$field['format'],
				$where['format']
			);

		}

		return $row->{$key};
	}

	/**
	 * Take an array of objects and will update or insert new into the db based on whether the object has the unique ID set.
	 *
	 * @access public
	 * @since  8.2.6
	 *
	 * @uses   cnEntry_DB::upsertDelete()
	 * @uses   wp_list_pluck()
	 * @uses   cnEntry_DB::updateRow()
	 * @uses   cnEntry_DB::insertRow()
	 *
	 * @param string $table  The table name in which to insert the data.
	 * @param array  $fields An array which defines the fields in the db to update and the map to the objects property and the properties format.
	 *                       Example of structure:
	 *                       array( 'table column name' => array( 'key' => 'object property name' , 'format' => '%s|%d|%f' ) )
	 * @param array  $data   An array of data objects to update/insert.
	 * @param array  $index  An array defining the unique key ID field name and its format of the data object.
	 *                       Example of structure:
	 *                       array( 'id' => array( 'key' => 'object unique id', 'format' => '%s|%d|%f' ) )
	 *
	 * @return array
	 */
	public function upsert( $table, $fields, $data, $index ) {

		$result = array();
		$key    = $index['id']['key'];

		$this->upsertDelete( $table, wp_list_pluck( $data, $key ) );

		if ( ! empty( $data ) ) {

			foreach ( $data as $row ) {

				if ( isset( $row->{$key} ) && ! empty( $row->{$key} ) ) {

					$result[] = $this->updateRow( $table, $fields, $row, $index );

				} else {

					$result[] = $this->insertRow( $table, $fields, $row );
				}

			}
		}

		return $result;
	}

	/**
	 * Create an array to store the which records by visibility the user can edit.
	 * This is done to prevent deleting any records the user isn't permitted view.
	 *
	 * @access private
	 * @since  8.2.6
	 *
	 * @global $wpdb
	 *
	 * @uses   cnEntry_DB::sanitizeID()
	 * @uses   wpdb::query()
	 *
	 * @param string $table The table from which to delete the rows from.
	 * @param array  $data  An array of unique ID/s to delete from the defined table.
	 */
	private function upsertDelete( $table, $data ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$permitted = array();
		$where     = array( 'WHERE 1=1' );

		$where[] = 'AND `entry_id` = "' . $this->id . '"';

		if ( current_user_can( 'connections_view_public' ) ) $permitted[] = 'public';
		if ( current_user_can( 'connections_view_private' ) ) $permitted[] = 'private';
		if ( current_user_can( 'connections_view_unlisted' ) ) $permitted[] = 'unlisted';

		if ( ! empty( $permitted ) ) {

			$where[] = 'AND `visibility` IN (\'' . implode( '\', \'', $permitted ) . '\')';
		}

		$id = cnSanitize::id( $data );

		if ( ! empty( $id ) ) {

			$where[] = 'AND `id` NOT IN ( ' . implode( ', ', $id ) . ' )';
		}

		$wpdb->query( "DELETE FROM `$table` " . implode( ' ', $where ) );
	}

	/**
	 * Helper function which return and array with two keys 'data' and 'format'. These two key will be arrays which can
	 * be supplied as values to {@see wpdb::insert()} and {@see wpdb::update()}.
	 *
	 * @access private
	 * @since  8.2.6
	 *
	 * @param array $fields
	 * @param array $data
	 *
	 * @return array
	 */
	private function fields( $fields, $data ) {

		$out = array();
		$out['data'] = array();
		$out['format'] = array();

		foreach ( $fields as $field => $row ) {

			if ( ( is_array( $data ) && isset( $data[ $row['key'] ] ) ) ||
			     ( is_object( $data ) && isset( $data->{$row['key']} ) ) ) {

				$out['data'][ $field ] = $data->{$row['key']};
				$out['value'][ $field ] = $data->{$row['key']};
				$out['format'][] = $row['format'];
			}
		}

		return $out;
	}
}
