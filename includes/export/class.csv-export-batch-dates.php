<?php
/**
 * The batch export the dates as a CSV file.
 *
 * @package     Connections
 * @subpackage  CSV Batch Export Dates
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cnCSV_Batch_Export_Dates Class
 *
 * @since 8.5
 */
class cnCSV_Batch_Export_Dates extends cnCSV_Batch_Export {

	/**
	 * Export type.
	 *
	 * Used for export-type specific filters/actions.
	 *
	 * @access public
	 * @since 8.5
	 *
	 * @var string
	 */
	public $type = 'dates';

	/**
	 * Define the CSV columns.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @return array $cols All the columns.
	 */
	public function columns() {

		$columns = array(
			'id'           => __( 'ID', 'connections' ),
			'entry_type'   => __( 'Entry Type', 'connections' ),
			'family_name'  => __( 'Family Name', 'connections' ),
			'prefix'       => __( 'Prefix', 'connections' ),
			'first'        => __( 'First', 'connections' ),
			'middle'       => __( 'Middle', 'connections' ),
			'last'         => __( 'Last', 'connections' ),
			'suffix'       => __( 'Suffix', 'connections' ),
			'organization' => __( 'Organization', 'connections' ),
			'date'         => __( 'Date', 'connections' ),
			'type'         => __( 'Type', 'connections' ),
			'preferred'    => __( 'Preferred', 'connections' ),
			'visibility'   => __( 'Visibility', 'connections' ),
		);

		return $columns;
	}

	/**
	 * Get the data being exported.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @return array $data Data for Export
	 */
	public function getData() {

		/** @var wpdb $wpdb */
		global $wpdb;

		$data = array();

		$offset = $this->limit * ( $this->step - 1 );

		$sql = $wpdb->prepare(
			'SELECT SQL_CALC_FOUND_ROWS entry.id, entry.entry_type, entry.family_name, entry.honorific_prefix, entry.first_name, entry.middle_name, entry.last_name, entry.honorific_suffix, entry.organization, date.*
			 FROM ' . CN_ENTRY_TABLE . ' AS entry
			 INNER JOIN ' . CN_ENTRY_DATE_TABLE . ' AS date
			 ON entry.id = date.entry_id
			 WHERE 1=1
			 AND date.date != ""
			 ORDER BY date.entry_id, date.order
			 LIMIT %d
			 OFFSET %d',
			$this->limit,
			absint( $offset )
		);

		$results = $wpdb->get_results( $sql );

		$i = 0;

		foreach ( $results as $entry ) {

			$data[ $i ]['id']           = $entry->entry_id;
			$data[ $i ]['entry_type']   = $entry->entry_type;
			$data[ $i ]['family_name']  = $entry->family_name;
			$data[ $i ]['prefix']       = $entry->honorific_prefix;
			$data[ $i ]['first']        = $entry->first_name;
			$data[ $i ]['middle']       = $entry->middle_name;
			$data[ $i ]['last']         = $entry->last_name;
			$data[ $i ]['suffix']       = $entry->honorific_suffix;
			$data[ $i ]['organization'] = $entry->organization;
			$data[ $i ]['date']         = $entry->date;
			$data[ $i ]['type']         = $entry->type;
			$data[ $i ]['preferred']    = $entry->preferred ? 'yes' : 'no';
			$data[ $i ]['visibility']   = $entry->visibility;

			$i++;
		}

		// The number of rows returned by the last query without the limit clause set
		$found = $wpdb->get_results( 'SELECT FOUND_ROWS()' );
		$this->setCount( (int) $found[0]->{'FOUND_ROWS()'} );

		$data = apply_filters( 'cn_export_get_data', $data );
		$data = apply_filters( 'cn_export_get_data_' . $this->type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @return int
	 */
	public function getPercentageComplete() {

		$count = $this->getCount();

		$percentage = 0;

		if ( 0 < $count ) {

			$percentage = floor( ( ( $this->limit * $this->step ) / $count ) * 100 );
		}

		if ( $percentage > 100 ) {

			$percentage = 100;
		}

		return $percentage;
	}
}
