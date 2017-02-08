<?php
namespace Connections\DB\Query;

use Doctrine\Common\Collections\ArrayCollection;
use Connections\DB\Collection;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class FluentQuery
 *
 * @package Connections\DB\Query
 */
class FluentQuery extends \IronBound\DB\Query\FluentQuery {

	/** @var bool */
	private $has_expressions = false;

	/** @var bool */
	private $select_single = false;

	/**
	 * @inheritDoc
	 */
	public function results( Saver $saver = null ) {

		if ( $this->results ) {
			return $this->results;
		}

		if ( $saver instanceof ModelSaver && $this->model ) {
			$saver->set_model_class( $this->model );
		} elseif ( ! $saver && $this->model ) {
			$saver = new ModelSaver( $this->model );
		}

		$this->make_limit_tag();
		$this->sql = $sql = trim( $this->build_sql() );

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( $this->calc_found_rows ) {

			$count_results = $this->wpdb->get_results( "SELECT FOUND_ROWS() AS COUNT" );

			if ( empty( $count_results ) || empty( $count_results[0] ) ) {
				$this->total = 0;
			} else {
				$this->total = $count_results[0]->COUNT;
			}
		}

		if ( ! $saver || $this->has_expressions || ! $this->select->is_all() ) {

			if ( ! $this->has_expressions && ! $this->select->is_all() ) {

				$columns    = $this->select->get_columns();
				$collection = array();

				foreach ( $results as $result ) {

					if ( $this->select_single ) {
						$column = key( $columns );
						$column = $this->get_short_column_from_qualified( $column );
						$value  = $result[ $column ];
					} else {
						$value = $result;
					}

					$collection[ $result[ $this->table->get_primary_key() ] ] = $value;
				}

				$collection = new ArrayCollection( $collection );
			} elseif ( $this->has_expressions ) {
				$collection = new ArrayCollection( reset( $results ) );
			} else {
				$collection = new ArrayCollection( $results );
			}

			$this->results = $collection;

			return $collection;
		}

		$models = array();

		foreach ( $results as $result ) {

			$model = $saver->make_model( $result );

			if ( $model ) {
				$models[ $saver->get_pk( $model ) ] = $model;
			}
		}

		if ( ! empty( $this->relations ) && ! empty( $models ) ) {
			$this->handle_eager_loading( $models );
		}

		$collection = new Collection( $models, false, $saver );

		$this->results = $collection;

		if ( $this->prime_meta_cache && ( $this->meta_table || ( $this->model && method_exists( $this->model, 'get_meta_table' ) ) ) ) {
			$this->update_meta_cache();
		}

		return $collection;
	}
}
