<?php
/**
 * The base CSV Batch Export Class.
 *
 * @package     Connections
 * @subpackage  CSV Batch Export
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cnCSV_Batch_Export Class
 *
 * @since 8.5
 */
class cnCSV_Batch_Export extends cnCSV_Export {

	/**
	 * The file the data is stored in.
	 *
	 * @access private
	 * @since 8.5
	 *
	 * @var string
	 */
	private $file;

	/**
	 * The name of the file the data is stored in.
	 *
	 * @access public
	 * @since 8.5
	 *
	 * @var string
	 */
	public $filename;

	/**
	 * The file type, typically .csv
	 *
	 * @access public
	 * @since 8.5
	 *
	 * @var string
	 */
	public $ext = 'csv';

	/**
	 * The current step being processed.
	 *
	 * @access public
	 * @since 8.5
	 *
	 * @var int
	 */
	public $step;

	/**
	 * The number of records to export per step.
	 *
	 * @access public
	 * @since 8.5
	 *
	 * @var int
	 */
	public $limit = 1000;

	/**
	 * The number of records total to be exported.
	 *
	 * @access private
	 * @since 8.5
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Is the export file writable
	 *
	 * @access public
	 * @since 8.5
	 *
	 * @var bool
	 */
	public $is_writable = TRUE;

	/**
	 *  Is the export file empty
	 *
	 * @access public
	 * @since 8.5
	 *
	 * @var bool
	 */
	public $is_empty = FALSE;

	/**
	 * Init class.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   wp_upload_dir()
	 * @uses   trailingslashit()
	 */
	public function __construct() {

		$upload_dir     = wp_upload_dir();
		$this->filename = 'cn-' . $this->type . '.' . $this->ext;
		$this->file     = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {

			$this->is_writable = FALSE;
		}
	}

	/**
	 * Process a step.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Export::can_export()
	 * @uses   WP_Error()
	 * @uses   cnCSV_Batch_Export::writeHeaders()
	 * @uses   cnCSV_Batch_Export::writeRows()
	 *
	 * @param  int $step The step to process.
	 *
	 * @return mixed bool|WP_Error
	 */
	public function process( $step ) {

		$this->step = $step;

		if ( ! $this->can_export() ) {

			return new WP_Error( 'permission_error', __( 'You do not have permission to export data.', 'connections' ) );
		}

		if ( $this->step < 2 ) {

			// Make sure we start with a fresh file on step 1.
			@unlink( $this->file );
			$this->writeHeaders();
		}

		$rows = $this->writeRows();

		if ( FALSE === $rows ) {

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Output the CSV column headers.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Export::getColumns()
	 * @uses   cnCSV_Batch_Export::write()
	 */
	public function writeHeaders() {

		$headers = array_map( array( $this, 'escapeAndQuote' ), $this->getColumns() );
		$headers = implode( ',', $headers ) . "\r\n";

		$this->write( $headers );
	}

	/**
	 * Print the CSV rows for the current step
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Export::getData()
	 * @uses   cnCSV_Export::getColumns()
	 * @uses   cnCSV_Export::escapeAndQuote()
	 * @uses   cnCSV_Batch_Export::write()
	 *
	 * @return string|false
	 */
	public function writeRows() {

		$row_data = '';
		$data     = $this->getData();
		$cols     = $this->getColumns();

		if ( ! empty( $data ) ) {

			foreach ( $data as $row ) {

				$count = count( $cols );
				$i     = 1;

				foreach ( $row as $col_id => $column ) {

					// Make sure the column is valid.
					if ( array_key_exists( $col_id, $cols ) ) {

						$row_data .= $this->escapeAndQuote( $column );
						$row_data .= $i == $count ? '' : ',';
						$i++;
					}
				}

				$row_data .= "\r\n";
			}

			$this->write( $row_data );

			return $row_data;
		}

		return FALSE;
	}

	/**
	 * Append data to file.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Batch_Export::fileContents()
	 * @uses   cnCSV_Export::getColumns()
	 *
	 * @param $data string The data to add to the file.
	 */
	protected function write( $data = '' ) {

		$file = $this->fileContents();
		$file .= $data;
		@file_put_contents( $this->file, $file );

		// If we have no rows after this step, mark it as an empty export.
		$file_rows    = file( $this->file, FILE_SKIP_EMPTY_LINES );
		$default_cols = $this->getColumns();
		$default_cols = empty( $default_cols ) ? 0 : 1;

		$this->is_empty = count( $file_rows ) == $default_cols ? TRUE : FALSE;
	}

	/**
	 * Set the number of records total to be exported.
	 *
	 * @access protected
	 * @since  8.5
	 *
	 * @param int $count
	 */
	protected function setCount( $count ) {

		$this->count = $count;
	}

	/**
	 * Get the number of records total to be exported.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @return int
	 */
	public function getCount() {

		return $this->count;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @return int
	 */
	public function getPercentageComplete() {

		return 100;
	}

	/**
	 * Retrieve the file data that is being written to.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @return string
	 */
	protected function fileContents() {

		$file = '';

		if ( @file_exists( $this->file ) ) {

			if ( ! is_writeable( $this->file ) ) {
				$this->is_writable = FALSE;
			}

			$file = @file_get_contents( $this->file );

		} else {

			@file_put_contents( $this->file, '' );
			@chmod( $this->file, 0664 );
		}

		return $file;
	}

	/**
	 * Download the CSV file.
	 *
	 * @access public
	 * @since  8.5
	 *
	 * @uses   cnCSV_Export::headers()
	 * @uses   cnCSV_Batch_Export::fileContents()
	 */
	public function download() {

		// Clear the fields and types query caches.
		cnCache::clear( TRUE, 'transient', 'cn-csv' );

		$this->headers();

		$file = $this->fileContents();

		@unlink( $this->file );

		echo $file;

		die();
	}
}
