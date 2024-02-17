<?php
/**
 * Batch Import Class
 *
 * This is the base class for all batch import methods. Each data import type (customers, payments, etc) extend this class.
 *
 * @package     Connections
 * @subpackage  CSV Batch Import
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cnCSV_Batch_Import Class
 *
 * @since 8.5.5
 */
class cnCSV_Batch_Import {

	/**
	 * Export type.
	 *
	 * Used for export-type specific filters/actions.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * The file being imported
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @var string
	 */
	private $file;

	/**
	 * An associative array which defines the mapping of the CSV columns to Connections fields.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @var array
	 */
	private $map = array();

	/**
	 * The current step being processed
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @var int
	 */
	public $step;

	/**
	 * The number of records to export per step.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @var int
	 */
	public $limit = 10;

	/**
	 * The number of records total to be exported.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Get things started
	 *
	 * @param string $file The absolute file path including filename to be imported.
	 *
	 * @since 8.5.5
	 */
	public function __construct( $file ) {

		@ini_set( 'memory_limit', apply_filters( 'cn_csv_import_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		if ( ! class_exists( 'parseCSV' ) ) {

			require_once CN_PATH . 'includes/Libraries/parseCSV/parsecsv.lib.php';
			require_once CN_PATH . 'includes/Libraries/parseCSV/cn-parsecsv.lib.v1.1.php';
		}

		$this->file = $file;
	}

	/**
	 * Can the current user export.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @uses   apply_filters()
	 * @uses   current_user_can()
	 *
	 * @return bool Whether or not current user can export.
	 */
	public function can_import() {

		return (bool) apply_filters( 'cn_csv_import_capability', current_user_can( 'import' ) );
	}

	/**
	 * Get the CSV columns.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @return array|WP_Error The columns in the CSV
	 */
	public function getHeaders() {

		$csv = new CN_parseCSV();
		$csv->remove_bom = true;

		/**
		 * Read only the first five lines of the file and parse for the CSV file headers.
		 */
		$file         = new SplFileObject( $this->file );
		$fileIterator = new LimitIterator( $file, 0, 100 );
		$data         = '';

		foreach ( $fileIterator as $line ) {

			$data .= $line;
		}

		$csv->auto( $data );

		if ( 0 < $csv->error ) {

			error_log( print_r( $csv->error_info, true ) );

			$error = array_shift( $csv->error_info );

			return new WP_Error( 'csv_parse_error', $error['info'], $error );
		}

		return $csv->titles;
	}

	/**
	 * Returns the associative array which defines the mapping of the CSV columns to Connections fields.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @return array
	 */
	public function getMap() {

		return $this->map;
	}

	/**
	 * @access public
	 * @since  8.5.5
	 *
	 * @param array $map An associative array which defines the mapping of the CSV columns to Connections fields.
	 */
	public function setMap( $map ) {

		$this->map = $map;
	}

	/**
	 * Process a step.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @param int $step
	 *
	 * @return bool|WP_Error
	 */
	public function process( $step ) {

		$more = false;

		$this->step = $step;
		$offset     = $this->limit * ( $this->step - 1 );

		$csv = new CN_parseCSV();
		$csv->remove_bom = true;

		/**
		 * Parse the and get the total row count.
		 *
		 * Unfortunately the only way to get this reliably is to parse the entire file because cell data might contain newlines.
		 *
		 * @todo If a large file is supplied, split the file into smaller chucks based on limit and then import each file separately.
		 */
		$csv->auto( $this->file );

		if ( 0 < $csv->error ) {

			error_log( print_r( $csv->error_info, true ) );

			$error = array_shift( $csv->error_info );

			return new WP_Error( 'csv_parse_error', $error['info'], $error );
		}

		$this->setCount( count( $csv->data ) );

		/**
		 * NOTE:
		 * @see parseCSV::parse() does support offset and limit params but it simply uses array_split() in
		 * @see parseCSV::parse_string() on the array rather than reparsing the file with offset/limit.
		 */
		$data = array_slice( $csv->data, $offset, $this->limit, true );

		/**
		 * @todo If a clean CSV could be guaranteed, then something like this could be done and require much less memory.
		 * @link http://stackoverflow.com/a/2809114/5351316
		 */
		//$file         = new SplFileObject( $this->file );
		//$fileIterator = new LimitIterator( $file, $offset, $this->limit + 1 ); // +1 to account for header row.
		//$data         = '';
		//
		//foreach ( $fileIterator as $line ) {
		//
		//	$data .= $line;
		//}
		//
		//$csv->fields = $csv->titles;
		//$csv->parse( $data );

		if ( ! empty( $data ) ) {

			$more = true;
			$this->mapData( $data );
			$this->import( $data );
		}

		return $more;
	}

	/**
	 * Map the CSV rows to Connections field by user supplied key.
	 *
	 * @access private
	 * @since  10.4.4
	 *
	 * @param array $sourceData The parse data from a CSV file.
	 *
	 * @return array
	 */
	private function mapData( &$sourceData ) {

		$map = $this->getMap();

		// Map the CSV data by row to the Connections field defined by the supplied map $csvMap.
		// This does create the array of objects used for the actual import.
		foreach ( $sourceData as $rowIndex => $rowValue ) {

			reset( $map );

			foreach ( $rowValue as $value ) {

				$csvHeader = key( $map );

				$sourceData[ $rowIndex ][ $csvHeader ] = $value;
				next( $map );
			}
		}

		return $sourceData;
	}

	/**
	 * The import logic for the data being imported. This methods should be overridden.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @param array $data
	 */
	public function import( $data ) {

		foreach ( $data as $row ) {

			error_log( print_r( $row, true ) );
		}
	}

	/**
	 * Set the number of records total to be exported.
	 *
	 * @access protected
	 * @since  8.5.5
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
	 * @since  8.5.5
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
	 * @since  8.5.5
	 *
	 * @return int
	 */
	public function getPercentageComplete() {

		return 100;
	}
}
