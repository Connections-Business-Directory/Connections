<?php
/**
 * HasMany relation.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace Connections\DB\Relations;

use Connections\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;

/**
 * Class Has_Many
 *
 * @package Connections\DB\Relations
 */
class HasMany extends \IronBound\DB\Relations\HasMany {

	/**
	 * Fetch results for eager loading.
	 *
	 * @since 2.0
	 *
	 * @param Model[]  $models
	 * @param callable $callback
	 *
	 * @return Collection
	 */
	protected function fetch_results_for_eager_load( array $models, $callback = null ) {

		$cached = array();

		if ( $callback === null ) {
			foreach ( $models as $pk => $model ) {
				$result = $this->load_from_cache( $model );

				if ( $result ) {
					$cached[ $pk ] = $result instanceof Collection ? $result->toArray() : array( $result );
				}
			}

			if ( count( $cached ) === count( $models ) ) {
				return new Collection( $this->flatten( $cached ), false, $this->saver );
			}
		}

		/** @var FluentQuery $query */
		$query = call_user_func( array( $this->related_model, 'query' ) );

		$pks = array_keys( $models );
		$pks = array_diff( $pks, array_keys( $cached ) );
		$pks = array_filter( $pks );

		if ( count( $pks ) === 0 ) {
			return new Collection( array(), false, $this->saver );
		}

		$query->where( $this->foreign_key, true, $pks );

		$this->apply_scopes_for_eager_load( $query );

		if ( $callback ) {
			$callback( $query );
		}

		$results = $query->results()->toArray();
		$cached  = $this->flatten( $cached );

		return new Collection( array_merge( $results, $cached ), false, $this->saver );
	}

	/**
	 * @inheritDoc
	 */
	protected function load_collection_from_cache( array $cached, Model $for ) {

		$models  = array();
		$removed = array();

		foreach ( $cached as $id ) {
			$model = $this->saver->get_model( $id );

			if ( $model ) {
				$models[ $id ] = $model;
			} else {
				$removed[] = $id;
			}
		}

		$diff = array_diff( $cached, $removed );
		wp_cache_set( $for->get_pk(), $diff, $this->get_cache_group() );

		return new Collection( $models, false, $this->saver );
	}

	/**
	 * @inheritDoc
	 */
	private function flatten( $array ) {
		if ( ! is_array( $array ) ) {
			// nothing to do if it's not an array
			return array( $array );
		}

		$result = array();
		foreach ( $array as $value ) {
			// explode the sub-array, and add the parts
			$result = array_merge( $result, $this->flatten( $value ) );
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function wrap_eager_loaded_results( $results ) {
		return new Collection( $results ?: array(), true );
	}
}
