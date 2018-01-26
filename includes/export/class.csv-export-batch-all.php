<?php
/**
 * The batch export the entry data as a CSV file.
 *
 * @package     Connections
 * @subpackage  CSV Batch Export Entry Data
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cnCSV_Batch_Export_Addresses Class
 *
 * @credit Nick Steele <njsteele@gmail.com>
 *
 * @since 8.5.1
 */
class cnCSV_Batch_Export_All extends cnCSV_Batch_Export {

	/**
	 * Export type.
	 *
	 * Used for export-type specific filters/actions.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @var string
	 */
	public $type = 'all';

	/**
	 * The number of records to export per step.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @var int
	 */
	public $limit = 100;

	/**
	 * The fields to export and the export config meta.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @see    cnCSV_Batch_Export_All::initConfig()
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * The column header names.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @var array
	 */
	private $headerNames = array();

	/**
	 * Setup the export.
	 *
	 * @access public
	 * @since  8.5.1
	 */
	public function __construct() {

		@ini_set( 'memory_limit', apply_filters( 'cn_csv_import_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		parent::__construct();

		$this->initConfig();
	}

	/**
	 * Define the CSV columns.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @return array $cols All the columns.
	 */
	public function columns() {

		$columns = array();

		return $columns;
	}

	/**
	 * Setup the fields and export meta configuration.
	 *
	 * @access public
	 * @since  8.5.1
	 */
	private function initConfig() {

		/**
		 * Field export configuration properties:
		 *
		 * @param array {
		 *
		 *     @type string $field  The field id from the table to export.
		 *                          NOTE: The `category` field indicates special processing used to export the categories
		 *                                from the terms table.
		 *                          NOTE: When exporting from CN_ENTRY_TABLE_META, this is to be set to the `meta_key`
		 *                                value to be exported.
		 *     @type string $header The header to use if the field does not have a registered header name.
		 *                          @see cnCSV_Batch_export_All::setHeaderNames()
		 *                          NOTE: Currently only used when exporting the categories into a single cell.
		 *     @type int    $type   The export type. This is used to set the path the export should use to export the field.
		 *                          Accepted values:
		 *                          0 - Export the field $id from the indicated $table.
		 *                              Use to export the data from the core CN_ENTRY_TABLE table.
		 *                          1 - Export the indicated fields in $fields from the indicated $table
		 *                              Use to export data from the supporting field type tables, such as CN_ENTRY_ADDRESS_TABLE, CN_ENTRY_PHONE_TABLE and so on.
		 *                          2 - Export the term into a single field with the term names separated by commas.
		 *                              NOTE: Only use this to export terms from the taxonomy tables.
		 *                          3 - Export the terms into separate cells. One term per cell.
		 *                              NOTE: Only use this to export terms from the taxonomy tables.
		 *                          4 - Export data stored in the CN_ENTRY_TABLE options column for an entry.
		 *                          5 - Export data stored in the CN_ENTRY_TABLE_META by `meta_key` name for an entry.
		 *                          6 - Export terms that are of the defined parent.
		 *                              NOTE: Only use this to export terms from the taxonomy tables.
		 *     @type string $fields The fields to export from the indicated $table.
		 *                          Use to export data from the specified $fields from the supporting field type tables,
		 *                          such as CN_ENTRY_ADDRESS_TABLE, CN_ENTRY_PHONE_TABLE and so on.
		 *                          NOTE: These should be provided as a semi-colon delimited string of field id.
		 *                          NOTE: Leave blank when exporting `meta_key` values from CN_ENTRY_TABLE_META.
		 *     @type string $table  The table to export data from.
		 *     @type string $types  The data types to export.
		 *                          EXAMPLE: If you only want work and home addresses,  put "work;home" in this field,
		 *                                   and addresses of any other type will not be included.
		 * }
		 */

		/**
		 * This example illustrates how to use export type `6`.
		 *
		 * The result will be the term name of term ID 2071 as the column header and the row contents will be term
		 * descendants if they are attached to the the current entry being exported.
		 *
		 * @example
		 *
		 * <code>
		 * $fields[] = array(
		 *     'field'    => 'category',
		 *     'child_of' => 2071,
		 *     'type'     => 6,
		 *     'fields'   => NULL,
		 *     'table'    => CN_TERMS_TABLE,
		 *     'types'    => NULL,
		 * );
		 */

		$fields = array(
			array(
				'field'  => 'id',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'ordo',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'entry_type',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'visibility',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'category',
				'header' => 'Categories',
				'type'   => 2,
				'fields' => NULL,
				'table'  => CN_TERMS_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'family_name',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'honorific_prefix',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'first_name',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'middle_name',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'last_name',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'honorific_suffix',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'title',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'organization',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'department',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'contact_first_name',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'contact_last_name',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'address',
				'type'   => 1,
				'fields' => 'line_1;line_2;line_3;line_4;district;county;city;state;zipcode;country;latitude;longitude;visibility',
				'table'  => CN_ENTRY_ADDRESS_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'phone',
				'type'   => 1,
				'fields' => 'number;visibility',
				'table'  => CN_ENTRY_PHONE_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'email',
				'type'   => 1,
				'fields' => 'address;visibility',
				'table'  => CN_ENTRY_EMAIL_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'social',
				'type'   => 1,
				'fields' => 'url;visibility',
				'table'  => CN_ENTRY_SOCIAL_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'im',
				'type'   => 1,
				'fields' => 'uid;visibility',
				'table'  => CN_ENTRY_MESSENGER_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'links',
				'type'   => 1,
				'fields' => 'url;title;visibility',
				'table'  => CN_ENTRY_LINK_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'dates',
				'type'   => 1,
				'fields' => 'date;visibility',
				'table'  => CN_ENTRY_DATE_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'bio',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'notes',
				'type'   => 0,
				'fields' => NULL,
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			array(
				'field'  => 'options',
				'type'   => 4,
				'fields' => 'image_url;logo_url',
				'table'  => CN_ENTRY_TABLE,
				'types'  => NULL,
			),
			//array(
			//	'field'          => 'meta_data',
			//	'type'           => 0,
			//	'fields'         => NULL,
			//	'table'          => CN_ENTRY_TABLE_META,
			//	'types' => NULL,
			//),
		);

		/**
		 * Allows plugins to alter/add to the field configuration.
		 *
		 * @since 8.5.20
		 *
		 * @param array $fields
		 */
		$this->fields = apply_filters( 'cn_csv_export_fields_config', $fields );
	}

	/**
	 * Returns the fields configuration array.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return array
	 */
	public function getFields() {

		return $this->fields;
	}

	/**
	 * Register the user friendly column header names.
	 *
	 * @access private
	 * @since  8.5.1
	 */
	private function setHeaderNames() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Core fields.
		$fields = array(
			'id'                 => 'Entry ID',
			'ordo'               => 'Order',
			'entry_type'         => 'Entry Type',
			'visibility'         => 'Visibility',
			'family_name'        => 'Family Name',
			'honorific_prefix'   => 'Honorific Prefix',
			'first_name'         => 'First Name',
			'middle_name'        => 'Middle Name',
			'last_name'          => 'Last Name',
			'honorific_suffix'   => 'Honorific Suffix',
			'title'              => 'Title',
			'organization'       => 'Organization',
			'department'         => 'Department',
			'contact_first_name' => 'Contact First Name',
			'contact_last_name'  => 'Contact Last Name',
			'bio'                => 'Biography',
			'notes'              => 'Notes',
		);

		$fields['category'] = 'Categories';

		/*
		 * Build the array of core and extended address fields for mapping during import.
		 */

		$coreAddressTypes = $instance->options->getDefaultAddressValues();
		$addressFields    = array(
			'line_1'     => 'Line One',
			'line_2'     => 'Line Two',
			'line_3'     => 'Line Three',
			'line_4'     => 'Line Four',
			'district'   => 'District',
			'county'     => 'County',
			'city'       => 'City',
			'state'      => 'State',
			'zipcode'    => 'Zipcode',
			'country'    => 'Country',
			'latitude'   => 'Latitude',
			'longitude'  => 'Longitude',
			'visibility' => 'Visibility',
		);

		/*
		 * Add the core address types to the field array.
		 */
		foreach ( $coreAddressTypes as $addressType => $addressName ) {

			foreach ( $addressFields as $addressFieldType => $addressFieldName ) {

				$key = 'address_' . $addressType . '_' . $addressFieldType;

				$fields[ $key ] = $addressName . ' Address | ' . $addressFieldName;
			}
		}

		/*
		 * Build the array of core phone fields for mapping during import.
		 */

		$corePhoneTypes = $instance->options->getDefaultPhoneNumberValues();
		$phoneFields    = array(
			'number'     => 'Number',
			'visibility' => 'Visibility',
		);

		// Add the core phone types to the field array.
		foreach ( $corePhoneTypes as $phoneType => $phoneName ) {

			foreach ( $phoneFields as $phoneFieldType => $phoneFieldName ) {

				$key = 'phone_' . $phoneType . '_' . $phoneFieldType;

				$fields[ $key ] = 'Phone | ' . $phoneName . ' | ' . $phoneFieldName;
			}
		}

		/*
		 * Build the array of core email fields for mapping during import.
		 */

		$coreEmailTypes = $instance->options->getDefaultEmailValues();
		$emailFields    = array(
			'address'    => 'Address',
			'visibility' => 'Visibility',
		);

		// Add the core email types to the field array.
		foreach ( $coreEmailTypes as $emailType => $emailName ) {

			foreach ( $emailFields as $emailFieldType => $emailFieldName ) {

				$key = 'email_' . $emailType . '_' . $emailFieldType;

				$fields[ $key ] = 'Email | ' . $emailName . ' | ' . $emailFieldName;
			}
		}

		/*
		 * Build the array of core IM fields for mapping during import.
		 */

		$coreIMTypes = $instance->options->getDefaultIMValues();
		$IMFields    = array(
			'uid'        => 'User ID',
			'visibility' => 'Visibility',
		);

		// Add the core IM types to the field array.
		foreach ( $coreIMTypes as $IMType => $IMName ) {

			foreach ( $IMFields as $IMFieldType => $IMFieldName ) {

				$key = 'im_' . $IMType . '_' . $IMFieldType;

				$fields[ $key ] = 'Messenger | ' . $IMName . ' | ' . $IMFieldName;
			}
		}

		/*
		 * Build the array of core social media fields for mapping during import.
		 */

		$coreSocialTypes = $instance->options->getDefaultSocialMediaValues();
		$socialFields    = array(
			'url'        => 'URL',
			'visibility' => 'Visibility',
		);

		// Add the core email types to the field array.
		foreach ( $coreSocialTypes as $socialType => $socialName ) {

			foreach ( $socialFields as $socialFieldType => $socialFieldName ) {

				$key = 'social_' . $socialType . '_' . $socialFieldType;

				$fields[ $key ] = 'Social Network | ' . $socialName . ' | ' . $socialFieldName;
			}
		}

		/*
		 * Build the array of core link fields for mapping during import.
		 */

		$coreLinkTypes = $instance->options->getDefaultLinkValues();
		$linkFields    = array(
			'url'        => 'URL',
			'title'      => 'Title',
			'visibility' => 'Visibility',
		);

		// Add the core link types to the field array.
		foreach ( $coreLinkTypes as $linkType => $linkName ) {

			foreach ( $linkFields as $linkFieldType => $linkFieldName ) {

				$key = 'links_' . $linkType . '_' . $linkFieldType;

				$fields[ $key ] = 'Link | ' . $linkName . ' | ' . $linkFieldName;
			}
		}

		/*
		 * Build the array of core date fields for mapping during import.
		 */

		$coreDateTypes = $instance->options->getDateOptions();
		$dateFields    = array(
			'date'       => 'Date',
			'visibility' => 'Visibility',
		);

		// Add the core date types to the field array.
		foreach ( $coreDateTypes as $dateType => $dateName ) {

			foreach ( $dateFields as $dateFieldType => $dateFieldName ) {

				$key = 'dates_' . $dateType . '_' . $dateFieldType;

				$fields[ $key ] = 'Date | ' . $dateName . ' | ' . $dateFieldName;
			}
		}

		$fields['options_image_url'] = 'Photo URL';
		$fields['options_logo_url']  = 'Logo URL';

		$this->headerNames = apply_filters( 'cn_csv_export_fields', $fields );
	}

	/**
	 * Output the CSV column headers.
	 *
	 * @access public
	 * @since  8.5.1
	 */
	public function writeHeaders() {

		$this->setHeaderNames();

		$headers = array();
		$count   = count( $this->fields );

		// Clear the fields and types query caches.
		cnCache::clear( TRUE, 'transient', 'cn-csv' );

		for ( $i = 0; $i < $count; $i++ ) {

			// If there is a special type, export it, otherwise, just draw it
			$header = $this->explodeBreakoutHeader( $this->fields[ $i ] );

			// Trim the hanging comma and space.
			$headers[] = rtrim( $header, ',' );
		}

		$row = implode( ',', $headers );

		// Now write the header...
		$this->write( $row . "\r\n" );
	}

	/**
	 * Breakout the header columns based on the field type.
	 *
	 * @access private
	 * @since  8.5.1
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	private function explodeBreakoutHeader( $atts ) {

		$headers = array();
		$type   = $atts['type'];

		if ( 0 === $type ) {

			$headers[] = $this->escapeAndQuote( $this->exportBreakoutHeaderField( $atts ) );

		} elseif ( 1 === $type ) {

			// Explode all field columns and types...

			$breakoutFields = $this->getFieldsToExport( $atts );
			$breakoutTypes  = $this->getTypesToExport( $atts );

			/*
			 * @todo self::getTypesToExport() can return no types.
			 *
			 * If the field type being exported contains no data, incorrect headers are written.
			 *
			 * Example:
			 *
			 * If no entries have social media networks added, the return types will be empty.
			 * This results in file headers of "Social Url" and "Social Visibility".
			 *
			 * Solution:
			 * Come up with a method where no types are returned, either skip the column or use the default
			 * registered types.
			 */

			foreach ( $breakoutTypes as $type ) {
				foreach ( $breakoutFields as $field ) {
					$headers[] = $this->escapeAndQuote( $this->exportBreakoutHeaderField( $atts, $field, $type ) );
				}
			}

		} elseif ( 2 === $type ) {

			// Joined from another table

			$headers[] = $this->escapeAndQuote( $atts['header'] );

		} elseif ( 3 === $type ) {

			// Breakout a list in the header...

			$count = $this->getTermCount( 'category' );

			// Finally, write a list of fields for each category...
			for ( $i = 0; $i < $count + 1; $i++ ) {

				$headers[] = $this->escapeAndQuote( 'Category' );
			}

		} elseif ( 4 === $type ) {

			$fields = explode( ';', $atts['fields'] );

			foreach ( $fields as $field ) {

				$headers[] = $this->escapeAndQuote( $this->exportBreakoutHeaderField( $atts, $field ) );
			}

		} elseif ( 5 === $type ) {

			$headers[] = $this->escapeAndQuote( $this->exportBreakoutHeaderField( $atts ) );

		} elseif ( 6 === $type ) {

			if ( is_numeric( $atts['child_of'] ) ) {

				$term = cnTerm::get( $atts['child_of'] );

				$headers[] = $this->escapeAndQuote( $term->name );
			}

		} else {

			// Allow plugins to hook into this to provide custom export logic.
			$headers[] = apply_filters( "cn_export_header-{$type}", "Hook into the `cn_export_header-{$type}` filter to provide custom header.", $atts, $this );
		}

		return apply_filters( 'cn_export_headers', implode( ',', $headers ), $headers, $atts, $this );
	}

	/**
	 * Break out the header columns based on the field types.
	 *
	 * @access private
	 * @since  8.5.1
	 *
	 * @param array  $atts
	 * @param string $field
	 * @param string $type
	 *
	 * @return string
	 */
	private function exportBreakoutHeaderField( $atts, $field = '', $type = '' ) {

		if ( 0 == strlen( $field ) && 0 == strlen( $type ) ) {

			$slug = $atts['field'];

		} elseif ( 0 == strlen( $type ) ) {

			$slug = $atts['field'] . '_' . $field;

		} else {

			$slug = $atts['field'] . '_' . $type . '_' . $field;
		}

		if ( array_key_exists( $slug, $this->headerNames ) ) {

			return $this->headerNames[ $slug ];
		}

		/**
		 * Should not get here, but if the field is not one of the registered header names, lets create one.
		 */
		$name = ucwords( str_replace( '_', ' ', $slug ) );

		return $name;
	}

	/**
	 * Get the data being exported.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @return array $data Data for export.
	 */
	public function getData() {

		/** @var wpdb $wpdb */
		global $wpdb;

		$offset = $this->limit * ( $this->step - 1 );

		//if ( 2 <= $this->step ) return FALSE;

		$sql = $wpdb->prepare(
			'SELECT SQL_CALC_FOUND_ROWS *
			 FROM ' . CN_ENTRY_TABLE . '
			 WHERE 1=1
			 ORDER BY id
			 LIMIT %d
			 OFFSET %d',
			$this->limit,
			absint( $offset )
		);

		$data = $wpdb->get_results( $sql );

		// The number of rows returned by the last query without the limit clause set
		$found = $wpdb->get_results( 'SELECT FOUND_ROWS()' );
		$this->setCount( (int) $found[0]->{'FOUND_ROWS()'} );

		$data = apply_filters( 'cn_export_get_data', $data );
		$data = apply_filters( 'cn_export_get_data_' . $this->type, $data );

		return $data;
	}

	/**
	 * Write the CSV rows for the current step.
	 *
	 * @access public
	 * @since  8.5.1
	 */
	public function writeRows() {

		$results = $this->getData();
		$rows    = '';

		if ( ! empty( $results ) ) {

			// Go through each entry...
			foreach ( $results as $entry ) {

				// Trim the trailing comma and space, then add newline.
				$rows .= rtrim( $this->buildRow( $entry ), ',' ) . "\r\n";
			}

			// Now write the data...
			$this->write( $rows );

			return $rows;
		}

		return FALSE;
	}

	/**
	 * @param stdClass $entry
	 *
	 * @return string
	 */
	public function buildRow( $entry ) {

		$fieldCount = count( $this->fields );
		$row        = array();

		// ...and go through each cell the user wants to export, and match it with the cell in the entry...
		for ( $i = 0; $i < $fieldCount; $i++ ) {

			$type = $this->fields[ $i ]['type'];

			// ...then find out if it's a breakout cell and process it properly...
			if ( 0 === $type ) {

				// If no breakout type is defined, only display the cell data...
				$row[ $i ] = $this->escapeAndQuote( $entry->{ $this->fields[ $i ]['field'] } );

			} elseif ( 1 === $type ) {

				// Export a standard breakout; just list them all in the order requested...
				$row[ $i ] = $this->exportBreakoutCell( $this->fields[ $i ], $entry->id );

			} elseif ( 2 === $type ) {

				// Process category table and list all categories in a single cell...
				$names = array();

				$terms = $this->getTerms( $entry->id, 'category' );

				foreach ( $terms as $term ) {

					$names[] = $term->name;
				}

				$row[ $i ] = $this->escapeAndQuote( implode( ',', $names ) );

			} elseif ( 3 === $type ) {

				$count = $this->getTermCount( 'category' );
				$terms = array();

				// Process the category table by breaking them out in separate cells,
				// Prepare an empty frame of the category cells...
				for ( $j = 0; $j < $count + 1; $j++ ) {

					// Make an array filled with empty cells
					$terms[ $j ] = '"",';
				}

				// Now start filling in the empty cells with data...
				$row[ $i ] = $this->getTerms( $entry->id, 'category' );

				$j = 0;

				foreach ( $row as $result ) {

					$terms[ $j ] = $this->escapeAndQuote( $result->name );

					$j++;
				}

				$row[ $i ] = implode( '', $terms );

			} elseif ( 4 === $type ) {

				// Export breakout data from the serialized option cell.
				$row[ $i ] = $this->exportBreakoutOptionsCell( $this->fields[ $i ], $entry );

			} elseif ( 5 === $type ) {

				$data = '';
				$meta = cnMeta::get( 'entry', $entry->id, $this->fields[ $i ]['field'], TRUE );

				if ( ! empty( $meta ) ) {

					$data = cnFormatting::maybeJSONencode( $meta );

				}

				$row[ $i ] = $this->escapeAndQuote( $data );

			} elseif ( 6 === $type ) {

				$names  = array();
				$parent = $this->fields[ $i ]['child_of'];

				$terms = $this->getTerms( $entry->id, 'category' );

				foreach ( $terms as $term ) {
					//$terms[] = $parent . ':' . $term->term_id;
					if ( cnTerm::isAncestorOf( $parent, $term->term_id, 'category' ) ) $names[] = $term->name;
				}

				$row[ $i ] = $this->escapeAndQuote( implode( ',', $names ) );

			} else {

				// Allow plugins to hook into this to provide custom export logic.
				$row[ $i ] = apply_filters( "cn_export_field-{$type}", "Hook into the `cn_export_field-{$type}` filter.", $entry, $this->fields[ $i ], $this );
			}

		}

		return apply_filters( 'cn_export_build_row', implode( ',', $row ), $row, $entry, $this );
	}

	/**
	 * Export the the entry data from a supporting data table.
	 *
	 * @access private
	 * @since  8.5.1
	 *
	 * @param array $atts
	 * @param int   $id   Entry ID.
	 *
	 * @return string
	 */
	private function exportBreakoutCell( $atts, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$breakoutTypeField = array();
		$breakoutFields    = $this->getFieldsToExport( $atts );
		$breakoutTypes     = $this->getTypesToExport( $atts );

		$countTypes  = count( $breakoutTypes );
		$countFields = count( $breakoutFields );

		// Prepare an empty frame of cells...
		for ( $i = 0; $i < $countTypes; $i++ ) {

			// Go through each type...
			$type = array();

			for ( $j = 0; $j < $countFields; $j++ ) {

				// Go through each field in each type...
				$type[] = '""';
			}

			// Write the type to the type array...
			$breakoutTypeField[ $i ] = implode( ',', $type );
		}

		$sql = $wpdb->prepare(
			'SELECT * FROM ' . $atts['table'].' AS e
			WHERE e.entry_id = %d
			ORDER BY e.order DESC',
			$id
		);

		// Get the data for this breakout...
		$row = $wpdb->get_results( $sql );

		// Go through each breakout record from it's table...
		foreach ( $row as $result ) {

			// Go through all the types that are supposed to be exported...
			for ( $i = 0; $i < $countTypes; $i++ ) {

				$type = array();

				// If the type is in our list, we need to export it...
				if ( $breakoutTypes[ $i ] == $result->type ) {

					// Loop through each field and record it...
					for ( $j = 0; $j < $countFields; $j++ ) {

						$type[] = $this->escapeAndQuote( $result->{$breakoutFields[ $j ]} );
					}

					$breakoutTypeField[ $i ] = implode( ',', $type );
				}
			}
		}

		// Return the breakout type field array (imploded)...
		$record = implode( ',', $breakoutTypeField );

		return $record;
	}

	/**
	 * Get the fields to export from the specified table.
	 *
	 * @access private
	 * @since  8.5.1
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	private function getFieldsToExport( $atts ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		// Get an array of each field we need to use...
		$fields = explode( ';', $atts['fields'] );

		// If no breakout field list was specified, include all fields...
		if ( empty( $atts['fields'] ) ) {

			// Terms are handled specially.
			if ( CN_TERMS_TABLE !== $atts['table'] ) {

				$results = cnCache::get( 'fields-' . $atts['table'], 'transient', 'cn-csv' );

				if ( FALSE === $results ) {

					// Get the field names from the SQL schema for the table we're going to use, and plop them into an array...
					$sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = "' . DB_NAME . '" AND table_name = "' . $atts['table'] . '";';

					$results = $wpdb->get_results( $sql );

					cnCache::set(
						'fields-' . $atts['table'],
						$results,
						DAY_IN_SECONDS,
						'transient',
						'cn-csv'
					);
				}

				$i = 0;

				foreach ( $results as $result ) {

					$fields[ $i ] = $result;
					$i ++;
				}
			}
		}

		return $fields;
	}

	/**
	 * Get the field data types to export.
	 *
	 * @access private
	 * @since  8.5.1
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	private function getTypesToExport( $atts ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$skipTables = array( CN_ENTRY_TABLE, CN_TERMS_TABLE );
		$types     = array();

		// You can specify you only want home addresses in an export for example, if nothing is specified,
		// get a list of all types from the breakout's table...
		if ( empty( $atts['types'] ) ) {

			// Get an array of each type we need to use...
			$types = explode( ';', $atts['types'] );

			if ( ! in_array( $atts['table'], $skipTables ) ) {

				$results = cnCache::get( 'types-' . $atts['table'], 'transient', 'cn-csv' );

				if ( FALSE === $results ) {

					$sql = 'SELECT DISTINCT `type` FROM ' . $atts['table'] . ' ORDER BY `type`';

					// Put the result into an array...
					$results = $wpdb->get_results( $sql );

					cnCache::set(
						'types-' . $atts['table'],
						$results,
						DAY_IN_SECONDS,
						'transient',
						'cn-csv'
					);
				}

				// Put a list of types for this breakout into an array...

				$i = 0;

				foreach ( $results as $result ) {

					$types[ $i ] = $result->type;
					$i++;
				}
			}
		}

		return $types;
	}

	/**
	 * Export the values stored in the CN_ENTRY_TABLE options column for an entry.
	 *
	 * @access private
	 * @since  8.5.8
	 *
	 * @param array  $atts The field options set in @see cnCSV_Batch_Export_All::initConfig().
	 * @param object $data The entry data retrieved from @see cnCSV_Batch_Export_All::getData().
	 *
	 * @return string
	 */
	private function exportBreakoutOptionsCell( $atts, $data ) {

		$options = maybe_unserialize( $data->options );
		$options = cnFormatting::maybeJSONdecode( $options );
		$fields  = explode( ';', $atts['fields'] );
		$cell    = array();

		foreach ( $fields as $field ) {

			$url = '';

			switch ( $field ) {

				case 'image_url':

					if ( isset( $options['image']['meta']['original']['name'] ) && ! empty( $options['image']['meta']['original']['name'] ) ) {

						$url = CN_IMAGE_BASE_URL . $data->slug . '/' . $options['image']['meta']['original']['name'];

					} else {

						$entry = new cnEntry( $data );
						$meta  = $entry->getImageMeta();
						$url   = ! is_wp_error( $meta ) ? $meta['url'] : '';
					}

					break;

				case 'logo_url':

					if ( isset( $options['logo']['meta']['name'] ) && ! empty( $options['logo']['meta']['name'] ) ) {

						$url = CN_IMAGE_BASE_URL . $data->slug . '/' . $options['logo']['meta']['name'];

					} else {

						$entry = new cnEntry( $data );
						$meta  = $entry->getImageMeta( array( 'type' => 'logo' ) );
						$url   = ! is_wp_error( $meta ) ? $meta['url'] : '';
					}

					break;
			}

			$cell[] = $this->escapeAndQuote( $url );
		}

		return implode( ',', $cell );
	}

	/**
	 * Return an indexed array of objects which contain the term name property.
	 *
	 * @access public
	 * @since  8.5.1
	 *
	 * @param int    $id
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function getTerms( $id, $taxonomy ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT t.term_id, t.name FROM ' . CN_TERMS_TABLE . ' AS t
			 INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id
			 INNER JOIN ' . CN_TERM_RELATIONSHIP_TABLE . ' AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
			 WHERE tt.taxonomy = %s
			 AND tr.entry_id = %d',
			$taxonomy,
			$id
		);

		$results = $wpdb->get_results( $sql );

		return $results;
	}

	/**
	 * Returns the largest number of terms associated to a single entry.
	 *
	 * @access private
	 * @since  8.5.1
	 *
	 * @param string $taxonomy The taxonomy to retrieve the count for.
	 *
	 * @return int
	 */
	private function getTermCount( $taxonomy ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$result = cnCache::get( 'max-term-count', 'transient', 'cn-csv' );

		if ( FALSE === $result ) {

			$sql = $wpdb->prepare(
				'SELECT COUNT(*) AS total
				 FROM ' . CN_TERMS_TABLE . ' AS t
				 INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' AS tt ON t.term_id = tt.term_id
				 INNER JOIN ' . CN_TERM_RELATIONSHIP_TABLE . ' AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
				 WHERE tt.taxonomy = %s
				 GROUP BY tr.entry_id ORDER BY COUNT(*) DESC LIMIT 1',
				$taxonomy
			);

			$result = $wpdb->get_results( $sql );

			cnCache::set(
				'max-term-count',
				$result,
				DAY_IN_SECONDS,
				'transient',
				'cn-csv'
			);
		}

		return $result[0]->total ? $result[0]->total : 0;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @access public
	 * @since  8.5.1
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
