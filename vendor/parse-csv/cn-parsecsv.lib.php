<?php

/**
 * Class CN_parseCSV
 *
 * @since 8.5.5
 */
class CN_parseCSV extends parseCSV {

	/**
	 * Remove BOM
	 * Strip off BOM (UTF-8)
	 *
	 * @access public
	 * @var bool
	 */
	public $remove_bom = FALSE;

	/**
	 * Parse CSV strings to arrays.
	 *
	 * Override the core class to implement the header fix when using offset.
	 *
	 * There seems to be a bug when using the parseCSV offset option.
	 *  - The previous row is used as the header names rather the the first row.
	 *
	 * @link https://github.com/parsecsv/parsecsv-for-php/issues/42
	 *
	 * @access public
	 * @param  string $data CSV string
	 *
	 * @return array 2D array with CSV data, or false on failure
	 */
	public function parse_string($data = null) {
		if (empty($data)) {
			if ($this->_check_data()) {
				$data = &$this->file_data;
			} else {
				return false;
			}
		}

		$white_spaces = str_replace($this->delimiter, '', " \t\x0B\0");

		$rows = array();
		$row = array();
		$row_count = 0;
		$current = '';
		$head = (!empty($this->fields)) ? $this->fields : array();
		$col = 0;
		$enclosed = false;
		$was_enclosed = false;
		$strlen = strlen($data);

		// force the parser to process end of data as a character (false) when
		// data does not end with a line feed or carriage return character.
		$lch = $data{$strlen - 1};
		if ($lch != "\n" && $lch != "\r") {
			$strlen++;
		}

		// walk through each character
		for ($i = 0; $i < $strlen; $i++) {
			$ch = (isset($data{$i})) ? $data{$i} : false;
			$nch = (isset($data{$i + 1})) ? $data{$i + 1} : false;
			$pch = (isset($data{$i - 1})) ? $data{$i - 1} : false;

			// open/close quotes, and inline quotes
			if ($ch == $this->enclosure) {
				if (!$enclosed) {
					if (ltrim($current, $white_spaces) == '') {
						$enclosed = true;
						$was_enclosed = true;
					} else {
						$this->error = 2;
						$error_row = count($rows) + 1;
						$error_col = $col + 1;
						if (!isset($this->error_info[$error_row . '-' . $error_col])) {
							$this->error_info[$error_row . '-' . $error_col] = array(
								'type' => 2,
								'info' => 'Syntax error found on row ' . $error_row . '. Non-enclosed fields can not contain double-quotes.',
								'row' => $error_row,
								'field' => $error_col,
								'field_name' => (!empty($head[$col])) ? $head[$col] : null,
							);
						}

						$current .= $ch;
					}
				} elseif ($nch == $this->enclosure) {
					$current .= $ch;
					$i++;
				} elseif ($nch != $this->delimiter && $nch != "\r" && $nch != "\n") {
					for ($x = ($i + 1);isset($data{$x}) && ltrim($data{$x}, $white_spaces) == ''; $x++) {}
					if ($data{$x} == $this->delimiter) {
						$enclosed = false;
						$i = $x;
					} else {
						if ($this->error < 1) {
							$this->error = 1;
						}

						$error_row = count($rows) + 1;
						$error_col = $col + 1;
						if (!isset($this->error_info[$error_row . '-' . $error_col])) {
							$this->error_info[$error_row . '-' . $error_col] = array(
								'type' => 1,
								'info' =>
									'Syntax error found on row ' . (count($rows) + 1) . '. ' .
									'A single double-quote was found within an enclosed string. ' .
									'Enclosed double-quotes must be escaped with a second double-quote.',
								'row' => count($rows) + 1,
								'field' => $col + 1,
								'field_name' => (!empty($head[$col])) ? $head[$col] : null,
							);
						}

						$current .= $ch;
						$enclosed = false;
					}
				} else {
					$enclosed = false;
				}

				// end of field/row/csv
			} elseif (($ch == $this->delimiter || $ch == "\n" || $ch == "\r" || $ch === false) && !$enclosed) {
				$key = (!empty($head[$col])) ? $head[$col] : $col;
				$row[$key] = ($was_enclosed) ? $current : trim($current);
				$current = '';
				$was_enclosed = false;
				$col++;

				// end of row
				if ($ch == "\n" || $ch == "\r" || $ch === false) {

					/**
					 * FIX
					 * @link https://github.com/parsecsv/parsecsv-for-php/issues/42
					 */
					if ( $this->heading && empty($head) ) {
						$head = $row;
					} elseif ( $this->_validate_offset($row_count) && $this->_validate_row_conditions($row, $this->conditions) ) {
						if ( empty($this->fields) || (!empty($this->fields) && (($this->heading && $row_count > 0) || !$this->heading)) ) {
							if ( !empty($this->sort_by) && !empty($row[$this->sort_by]) ) {
								if ( isset($rows[$row[$this->sort_by]]) ) {
									$rows[$row[$this->sort_by].'_0'] = &$rows[$row[$this->sort_by]];
									unset($rows[$row[$this->sort_by]]);
									for ( $sn=1; isset($rows[$row[$this->sort_by].'_'.$sn]); $sn++ ) {}
									$rows[$row[$this->sort_by].'_'.$sn] = $row;
								} else $rows[$row[$this->sort_by]] = $row;
							} else $rows[] = $row;
						}
					}

					$row = array();
					$col = 0;
					$row_count++;

					if ($this->sort_by === null && $this->limit !== null && count($rows) == $this->limit) {
						$i = $strlen;
					}

					if ($ch == "\r" && $nch == "\n") {
						$i++;
					}
				}

				// append character to current field
			} else {
				$current .= $ch;
			}
		}

		$this->titles = $head;
		if (!empty($this->sort_by)) {
			$sort_type = SORT_REGULAR;
			if ($this->sort_type == 'numeric') {
				$sort_type = SORT_NUMERIC;
			} elseif ($this->sort_type == 'string') {
				$sort_type = SORT_STRING;
			}

			($this->sort_reverse) ? krsort($rows, $sort_type) : ksort($rows, $sort_type);

			if ($this->offset !== null || $this->limit !== null) {
				$rows = array_slice($rows, ($this->offset === null ? 0 : $this->offset), $this->limit, true);
			}
		}

		if (!$this->keep_file_data) {
			$this->file_data = null;
		}

		return $rows;
	}

	/**
	 * Create CSV data from array
	 *
	 * Override core class to implement fix for incorrect unparsing data when empty cells.
	 * @link https://github.com/parsecsv/parsecsv-for-php/pull/96
	 *
	 * @access public
	 *
	 * @param array  $data      2D array with data
	 * @param array  $fields    field names
	 * @param bool   $append    if true, field names will not be output
	 * @param bool   $is_php    if a php die() call should be put on the first
	 *                          line of the file, this is later ignored when read.
	 * @param string $delimiter field delimiter to use
	 *
	 * @return  string
	 */
	public function unparse( $data = array(), $fields = array(), $append = FALSE, $is_php = FALSE, $delimiter = NULL ) {

		if ( ! is_array( $data ) || empty( $data ) ) {
			$data = &$this->data;
		}
		if ( ! is_array( $fields ) || empty( $fields ) ) {
			$fields = &$this->titles;
		}
		if ( $delimiter === NULL ) {
			$delimiter = $this->delimiter;
		}
		$string = ( $is_php ) ? "<?php header('Status: 403'); die(' '); ?>" . $this->linefeed : '';
		$entry  = array();

		// create heading
		if ( $this->heading && ! $append && ! empty( $fields ) ) {

			foreach ( $fields as $key => $value ) {
				$entry[] = $this->_enclose_value( $value, $delimiter );
			}

			$string .= implode( $delimiter, $entry ) . $this->linefeed;
			$entry  = array();
		}

		// create data
		foreach ( $data as $key => $row ) {

			foreach ( $fields as $field => $fieldname ) {

				$value   = isset( $row[ $fieldname ] ) ? $row[ $fieldname ] : NULL;
				$entry[] = $this->_enclose_value( $value, $delimiter );
			}

			$string .= implode( $delimiter, $entry ) . $this->linefeed;
			$entry  = array();
		}

		if ( $this->convert_encoding ) {

			$string = iconv( $this->input_encoding, $this->output_encoding, $string );
		}

		return $string;
	}

	/**
	 * Load local file or string
	 *
	 * Override the core class to implement BOM stripping.
	 *
	 * @link https://github.com/parsecsv/parsecsv-for-php/pull/40
	 * @link https://github.com/parsecsv/parsecsv-for-php/pull/83
	 *
	 * @access public
	 *
	 * @param  string $input
	 *
	 * @return  bool
	 */
	public function load_data($input = null) {
		$data = null;
		$file = null;
		if (is_null($input)) {
			$file = $this->file;
		} elseif (file_exists($input)) {
			$file = $input;
		} else {
			$data = $input;
		}
		if (!empty($data) || $data = $this->_rfile($file)) {
			if ($this->file != $file) {
				$this->file = $file;
			}
			if (preg_match('/\.php$/i', $file) && preg_match('/<\?.*?\?>(.*)/ims', $data, $strip)) {
				$data = ltrim($strip[1]);
			}
			if ($this->convert_encoding) {
				$data = iconv($this->input_encoding, $this->output_encoding, $data);
			}
			if (substr($data, -1) != "\n") {
				$data .= "\n";
			}

			if($this->remove_bom !== FALSE){
				// strip off BOM (UTF-8)
				if (strpos($data, "\xef\xbb\xbf") !== FALSE) {
					$data = substr($data, 3);
					//break;
				}
				// strip off BOM (UTF-16)
				else if (strpos($data, "\xff\xfe") !== FALSE) {
					$data = substr($data, 2);
					//break;
				}
				// strip off BOM (UTF-16)
				else if (strpos($data, "\xfe\xff") !== FALSE) {
					$data = substr($data, 2);
					//break;
				}
			}

			$this->file_data = &$data;
			return true;
		}
		return false;
	}
}
