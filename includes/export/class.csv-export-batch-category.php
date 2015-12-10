<?php
/**
 * The batch export the categories as a CSV file.
 *
 * @package     Connections
 * @subpackage  CSV Batch Export Category
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnCSV_Batch_Export_Term
 */
class cnCSV_Batch_Export_Term extends cnCSV_Batch_Export {

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
	 * @since 8.5.5
	 *
	 * @var int
	 */
	public $limit = 2000;

	/**
	 * Define the CSV columns.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @return array $cols All the columns.
	 */
	public function columns() {

		$columns = array(
			'id'        => __( 'ID', 'connections' ),
			'name'      => __( 'Name', 'connections' ),
			'desc'      => __( 'Description', 'connections' ),
			'slug'      => __( 'Slug', 'connections' ),
			'hierarchy' => __( 'Parent', 'connections' ),
		);

		return $columns;
	}

	/**
	 * Get the data being exported.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @return array $data Data for Export
	 */
	public function getData() {

		/** @var wpdb $wpdb */
		//global $wpdb;

		$data   = array();
		$offset = $this->limit * ( $this->step - 1 );

		$results = cnTerm::getTaxonomyTerms(
			$this->type,
			array(
				'hide_empty'   => FALSE,
				'hierarchical' => FALSE,
				'offset'       => $offset,
				'number'       => $this->limit,
			)
		);

		$count = cnTerm::getTaxonomyTerms(
			$this->type,
			array(
				'hide_empty' => FALSE,
				'fields'     => 'count',
			)
		);

		$this->setCount( $count );

		$i = 0;

		$terms = $this->buildHierarchy( $results );

		foreach ( $terms as $term ) {

			$data[ $i ]['id']        = $term->term_id;
			$data[ $i ]['name']      = $term->name;
			$data[ $i ]['desc']      = $term->description;
			$data[ $i ]['slug']      = $term->slug;
			$data[ $i ]['hierarchy'] = $term->hierarchy;

			$i++;
		}

		$data = apply_filters( 'cn_export_get_data', $data );
		$data = apply_filters( 'cn_export_get_data_' . $this->type, $data );

		return $data;
	}

	/**
	 * Build the hierarchy of term descendants as a string.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @param array $terms
	 *
	 * @return array
	 */
	private function buildHierarchy( $terms ) {

		$terms = $this->sort( $terms );

		foreach ( $terms as $term ) {

			//$text = "{$term->name}|{$term->slug}";
			$text = '';

			$this->_buildHierarchy( $term, $text );

			$term->hierarchy = urldecode( $text );
		}

		return $terms;
	}

	/**
	 * Recursive function prepend a terms parent name and slug to the hierarchy string.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @param object $term
	 * @param string $text
	 */
	private function _buildHierarchy( $term, &$text ) {

		if ( $term->parent ) {

			$parent = cnTerm::get( $term->parent, $term->taxonomy );

			if ( 0 == strlen( $text ) ) {

				$text = "{$parent->name}|{$parent->slug}";

			} else {

				$text = "{$parent->name}|{$parent->slug}" . ' > ' . $text;
			}

			$this->_buildHierarchy( $parent, $text );
		}
	}

	/**
	 * Sort the returned terms so the descendant terms are ordered so the come after their parent term.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @param array $terms
	 *
	 * @return array
	 */
	private function sort( $terms ) {

		$grouped = array();

		if ( empty( $terms ) ) {

			return $terms;
		}

		foreach ( $terms as $term ) {

			$grouped[ $term->parent ]->children[ $term->term_id ] = $term;
			$index[ $term->term_id ]                              = $term;
		}

		foreach ( $grouped as $k => $v ) {

			if ( isset( $index[ $k ] ) ) {

				$index[ $k ]->children = $v->children;
				unset( $grouped[ $k ] );
			}
		}

		// Descendant terms will be stored as nested arrays, so the nested array needs to be "flattened".
		return $this->flatten( $grouped );
	}

	/**
	 * Flatten the nested descendant arrays so the terms' children come after the parent term in the array.
	 *
	 * @access public
	 * @since  8.5.5
	 *
	 * @param array $terms
	 *
	 * @return array
	 */
	private function flatten( $terms ) {

		$flat = array();

		foreach ( $terms as $k => $v ) {

			if ( ! empty( $v->children ) ) {

				$children = $v->children;

				unset( $v->children );

				if ( isset( $v->slug ) ) {

					$flat[] = $v;
				}

				$flat = array_merge( $flat, $this->flatten( $children ) );

			} else {

				$flat[] = $v;
			}
		}

		return $flat;
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
