<?php

/**
 * The base CSV Export Class.
 *
 * @package     Connections
 * @subpackage  CSV Export
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cnCSV_Export Class
 *
 * @since 8.5
 */
class cnCSV_Export {

	/**
	 * Export type.
	 *
	 * Used for export-type specific filters/actions.
	 *
	 * @since 8.5
	 *
	 * @var string
	 */
	public $type = 'default';

	/**
	 * Add double-quotes before/after the supplied string.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function addQuotes( $string ) {

		return '"' . $string . '"';
	}

	/**
	 * Add double-quotes before/after the supplied string and add slashes to the supplied string.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function escapeAndQuote( $string ) {

		return $this->addQuotes( $this->escape( addslashes( $string ) ) );
	}

	/**
	 * Escape the double-quotes.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function escape( $string ) {

		return str_replace( '"', '""', $string );
	}

	/**
	 * Can the current user export.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   apply_filters()
	 * @uses   current_user_can()
	 *
	 * @return bool Whether or not current user can export.
	 */
	public function can_export() {

		return (bool) apply_filters( 'cn_csv_export_capability', current_user_can( 'export' ) );
	}

	/**
	 * Set the CSV columns.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @return array $cols All the columns.
	 */
	public function columns() {

		$columns = array(
			'id'      => __( 'ID', 'connections' ),
			'prefix'  => __( 'Prefix', 'connections' ),
			'first'   => __( 'First', 'connections' ),
			'middle'  => __( 'Middle', 'connections' ),
			'last'    => __( 'Last', 'connections' ),
			'suffix'  => __( 'Suffix', 'connections' ),
			'address' => __( 'Address', 'connections' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the CSV columns.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Export::columns()
	 * @uses   apply_filters()
	 *
	 * @return array $cols Array of the columns names.
	 */
	public function getColumns() {

		$columns = $this->columns();

		return apply_filters( 'csv_export_csv_columns_' . $this->type, $columns );
	}

	/**
	 * Get the data being exported.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   apply_filters()
	 *
	 * @return array $data Data for Export
	 */
	public function getData() {

		// Sample data array.
		$data = array(
			0 => array(
				'id'      => '',
				'prefix'  => '',
				'first'   => __( 'John', 'connections' ),
				'middle'  => __( 'Q.', 'connections' ),
				'last'    => __( 'Doe', 'connections' ),
				'suffix'  => '',
				'address' => 'test@domain.tld',
			),
			1 => array(
				'id'      => '',
				'prefix'  => '',
				'first'   => __( 'Jane', 'connections' ),
				'middle'  => __( 'A.', 'connections' ),
				'last'    => __( 'Doe', 'connections' ),
				'suffix'  => '',
				'address' => 'test@domain.tld',
			),
		);

		$data = apply_filters( 'cn_export_get_data', $data );
		$data = apply_filters( 'cn_export_get_data_' . $this->type, $data );

		return $data;
	}

	/**
	 * Write the CSV headers.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Export::getColumns()
	 */
	public function writeHeaders() {

		$headers = array_map( array( $this, 'addSlashesAndQuote' ), $this->getColumns() );

		echo implode( ',', $headers ) , "\r\n";
	}

	/**
	 * Write the CSV rows.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Export::getData()
	 * @uses   cnCSV_Export::getColumns()
	 * @uses   cnCSV_Export::addQuotes()
	 */
	public function writeRows() {

		$data = $this->getData();
		$cols = $this->getColumns();

		// Output each row
		foreach ( $data as $row ) {

			$count = count( $cols );
			$i     = 1;

			foreach ( $row as $id => $value ) {

				// Make sure the column is valid.
				if ( array_key_exists( $id, $cols ) ) {

					echo $this->addQuotes( $value );
					echo $i == $count ? '' : ',';
					$i ++;
				}
			}

			echo "\r\n";
		}
	}

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since  8.5
	 */
	public function headers() {

		ignore_user_abort( true );

		//if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		//	set_time_limit( 0 );
		//}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=cn-export-' . $this->type . '-' . date( 'm-d-Y' ) . '.csv' );
		header( "Expires: 0" );

		/**
		 * Allow plugins to add additional HTTP headers.
		 *
		 * @since 8.6.6
		 */
		do_action( 'cn_csv_batch_export_download_headers' );
	}

	/**
	 * Write and download the CSV file.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   wp_die()
	 * @uses   cnCSV_Export::headers()
	 * @uses   cnCSV_Export::writeHeaders()
	 * @uses   cnCSV_Export::writeRows()
	 */
	public function download() {

		if ( ! $this->can_export() ) {
			wp_die(
				__( 'You do not have permission to export data.', 'connections' ),
				__( 'Error', 'connections' ),
				array( 'response' => 403 )
			);
		}

		// Set headers.
		$this->headers();

		// Write CSV columns headers.
		$this->writeHeaders();

		// Write CSV rows.
		$this->writeRows();

		die();
	}
}
