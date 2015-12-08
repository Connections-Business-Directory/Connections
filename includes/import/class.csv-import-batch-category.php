<?php
/**
 * Batch Import Category Class.
 *
 * @package     Connections
 * @subpackage  CSV Batch Import Categories
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * cnCSV_Batch_Import Class
 *
 * @since 8.5.5
 */
class cnCSV_Batch_Import_Term extends cnCSV_Batch_Import {

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
	public $type = 'category';

	/**
	 * The number of records to export per step.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @var int
	 */
	public $limit = 100;

	/**
	 * The import logic for the data being imported.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @param array $data
	 */
	public function import( $data ) {

		foreach ( $data as $row ) {

			/**
			 * Increase the PHP max execution limit by 1 second per term.
			 * Should help prevent the import from terminating early unless the host
			 * does not allow the limit to be changed or enforces a "hard" limit.
			 */
			@set_time_limit(1);

			$name      = '';
			$slug      = '';
			$desc      = '';
			$parent_id = 0;
			$parents   = array();

			foreach ( $this->getMap() as $header => $field ) {

				if ( -1 != $field ) {

					switch ( $field ) {

						case 'name':

							$name = $row[ $header ];
							break;

						case 'slug':

							$slug = $row[ $header ];
							break;

						case 'desc':

							$desc = $row[ $header ];
							break;

						case 'parent':

							// Since this is a string, lets make sure there's something there before proceeding.
							if ( 0 < strlen( $row[ $header ] ) ) {

								// Allow the term to be a child of multiple parents.
								$items = array_map( 'trim', explode( ',', $row[ $header ] ) );

								foreach ( $items as $item ) {

									// Since this is a string, lets make sure there's something there before proceeding.
									if ( 0 < strlen( $item ) ) {

										$parent_id = 0;

										/**
										 * $item can be a single category name or a string such as:
										 * Parent > Child > Grandchild
										 */
										$terms = array_map( 'trim', explode( '>', $item ) );
										//error_log( 'Hierarchy: ' . print_r( $terms, TRUE ) );

										$parent_id = $this->importAncestors( $terms, $parent_id );

										$parents[] = $parent_id;
									}
								}

							}

							break;
					}

				}
			}

			if ( ! empty( $name ) ) {

				if ( ! empty( $parents ) ) {

					/**
					 * Term being inserted is being inserted as a child term for multiple parent terms.
					 */

					foreach ( $parents as $parent ) {

						$exists = $this->termExists( $name, $slug, $parent );

						// Term does not exist, create it.
						if ( FALSE === $exists ) {

							$this->insertTerm( $name, $slug, $desc, $parent );
						}

					}

				} else {

					/**
					 * Insert term as a parent or as a child of the supplied parent ID.
					 */

					$exists = $this->termExists( $name, $slug, $parent_id );

					// Term does not exist, create it.
					if ( FALSE === $exists ) {

						$this->insertTerm( $name, $slug, $desc, $parent_id );
					}

				}

			}

		}

	}

	/**
	 * Import the term's ancestors.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @param array $terms
	 * @param int   $parent_id
	 *
	 * @return int
	 */
	private function importAncestors( $terms, $parent_id = 0 ) {

		foreach ( $terms as $term ) {

			/**
			 * $term can be the term name and slug:
			 * Name|slug
			 */
			$term = array_map( 'trim', explode( '|', $term ) );
			//error_log( 'Category: ' . print_r( $term, TRUE ) );

			/**
			 * Since we're importing a term's ancestors, if an integer is found, assume it is a term ID of an existing
			 * term and cast the numeric string to an integer that way @see cnCSV_Batch_Import_Term::termExists() will
			 * search by term ID rather than the term name/slug.
			 *
			 * NOTE: If the term ancestors name just happens to be a numeric string that could cause unpredictable
			 * result for the term being inserted and its ancestors.
			 */
			$name = is_numeric( $term[0] )? absint( $term[0] ): $term[0];
			$slug = isset( $term[1] ) && ! empty( $term[1] ) ? $term[1] : '';
			//error_log( 'Slug: ' . print_r( $slug, TRUE ) );
			//error_log( 'Parent ID: ' . print_r( $parent_id, TRUE ) );

			$exists = $this->termExists( $name, $slug, $parent_id );
			//error_log( 'Exists: ' . print_r( $exists, TRUE ) );

			// Term does not exist, create it.
			if ( FALSE === $exists ) {

				$result = $this->insertTerm( $name, $slug, '', $parent_id );

				if ( ! is_wp_error( $result ) ) {

					$parent_id = (int) $result['term_id'];
					//error_log( 'Set Parent ID: ' . print_r( $parent_id, TRUE ) );
				}

			} elseif ( is_int( $exists ) ) {

				$parent_id = $exists;
				//error_log( 'Set Parent ID: ' . print_r( $parent_id, TRUE ) );
			}
		}

		return $parent_id;
	}

	/**
	 * Check if a term exists.
	 *
	 * NOTE: Using this rather than @see cnTern::exists() because it is a bit more efficient and a little more strict when
	 * determining if a term exist or not.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @param int|string  $term The term name or term ID to check.
	 * @param string      $slug The term slug.
	 * @param int         $parent The term parent ID.
	 *
	 * @return bool|int   The parent term ID if it exists, FALSE if it does not.
	 */
	private function termExists( $term, $slug = '', $parent = 0 ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( is_int( $term ) ) {

			if ( 0 == $term ) {

				return FALSE;
			}

			if ( $result = $wpdb->get_row(
				$wpdb->prepare( 'SELECT tt.term_id, tt.term_taxonomy_id FROM ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' as tt ON tt.term_id = t.term_id WHERE t.term_id = %d AND tt.taxonomy = %s',
				                $term,
				                $this->type ),
				ARRAY_A
			)
			) {

				return (int) $result['term_id'];

			} else {

				return FALSE;
			}
		}

		$where  = 'BINARY t.name = %s';
		$fields = array( $term );

		if ( 0 < strlen( $slug ) ) {

			$where    .= ' AND BINARY t.slug = %s';
			$fields[] = sanitize_title( $slug );
		}

		if ( 0 < $parent ) {

			$where .= ' AND tt.parent = %d';
			$fields[] = absint( $parent );
		}

		$where .= ' AND tt.taxonomy = %s';
		$fields[] = $this->type;

		if ( $result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT tt.term_id, tt.term_taxonomy_id FROM ' . CN_TERMS_TABLE . ' AS t INNER JOIN ' . CN_TERM_TAXONOMY_TABLE . ' as tt ON tt.term_id = t.term_id WHERE ' . $where,
				$fields
			),
			ARRAY_A
		)
		) {

			return (int) $result['term_id'];

		} else {

			return FALSE;
		}

	}

	/**
	 * Helper function to insert a new term.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @param string $name   The term name.
	 * @param string $slug   The term slug.
	 * @param string $desc   The term description.
	 * @param int    $parent The term parent ID.
	 *
	 * @return array|WP_Error An array containing the term_id and term_taxonomy_id, WP_Error otherwise.
	 */
	private function insertTerm( $name, $slug = '', $desc = '', $parent = 0 ) {

		$atts = array(
			'slug'        => $slug,
			'description' => $desc,
			'parent'      => $parent,
		);

		$result = cnTerm::insert( $name, $this->type, $atts );

		if ( is_wp_error( $result ) ) {

			error_log( 'Term Import Error: ' . $result->get_error_message() );
			error_log( ' - Name: ' . print_r( $name, TRUE ) );
			error_log( ' - Slug: ' . print_r( $slug, TRUE ) );
			error_log( ' - Parent ID: ' . print_r( $parent, TRUE ) );
		}

		return $result;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @access public
	 * @since  8.5.5
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
