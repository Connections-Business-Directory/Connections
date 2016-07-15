<?php
/**
 * The batch export the addresses as a CSV file.
 *
 * @package     Connections
 * @subpackage  CSV Batch Export Address
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cnCSV_Batch_Export_Addresses Class
 *
 * @since 8.5
 */
class cnCSV_Batch_Export_Addresses extends cnCSV_Batch_Export {

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
	public $type = 'addresses';

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
			'type'         => __( 'Address Type', 'connections' ),
			'line_1'       => __( 'Line One', 'connections' ),
			'line_2'       => __( 'Line Two', 'connections' ),
			'line_3'       => __( 'Line Three', 'connections' ),
			'line_4'       => __( 'Line Four', 'connections' ),
			'district'     => __( 'District', 'connections' ),
			'county'       => __( 'County', 'connections' ),
			'city'         => __( 'City', 'connections' ),
			'state'        => __( 'State', 'connections' ),
			'zipcode'      => __( 'Zipcode', 'connections' ),
			'country'      => __( 'Country', 'connections' ),
			'latitude'     => __( 'Latitude', 'connections' ),
			'longitude'    => __( 'Longitude', 'connections' ),
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
	 * @return array $data Data for export.
	 */
	public function getData() {

		/** @var wpdb $wpdb */
		global $wpdb;

		$data = array();

		$offset = $this->limit * ( $this->step - 1 );

		$sql = $wpdb->prepare(
			'SELECT SQL_CALC_FOUND_ROWS entry.id, entry.entry_type, entry.family_name, entry.honorific_prefix, entry.first_name, entry.middle_name, entry.last_name, entry.honorific_suffix, entry.organization, address.*
			 FROM ' . CN_ENTRY_TABLE . ' AS entry
			 INNER JOIN ' . CN_ENTRY_ADDRESS_TABLE . ' AS address
			 ON entry.id = address.entry_id
			 WHERE 1=1
			 AND ( address.line_1 != ""
			 OR address.line_2 != ""
			 OR address.line_3 != ""
			 OR address.line_4 != ""
			 OR address.district != ""
			 OR address.county != ""
			 OR address.city != ""
			 OR address.state != ""
			 OR address.zipcode != ""
			 OR address.latitude != 0.000000000000
			 OR address.longitude != 0.000000000000 )
			 ORDER BY address.entry_id, address.order
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
			$data[ $i ]['type']         = $entry->type;
			$data[ $i ]['line_1']       = $entry->line_1;
			$data[ $i ]['line_2']       = $entry->line_2;
			$data[ $i ]['line_3']       = $entry->line_3;
			$data[ $i ]['line_4']       = $entry->line_4;
			$data[ $i ]['district']     = $entry->district;
			$data[ $i ]['county']       = $entry->county;
			$data[ $i ]['city']         = $entry->city;
			$data[ $i ]['state']        = $entry->state;
			$data[ $i ]['zipcode']      = $entry->zipcode;
			$data[ $i ]['country']      = $entry->country;
			$data[ $i ]['latitude']     = $entry->latitude;
			$data[ $i ]['longitude']    = $entry->longitude;
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
