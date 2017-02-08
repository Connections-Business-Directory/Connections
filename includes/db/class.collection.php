<?php
/**
 * Collection class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace Connections\DB;

use IronBound\DB\Model;

/**
 * Class Collection
 *
 * @package Connections\DB
 */
class Collection extends \IronBound\DB\Collection {

	/**
	 * @param array $elements
	 * @param Model $model
	 *
	 * @return bool
	 */
	public function add_many( array $elements, Model $model ) {

		//foreach ( $elements as $element ) {
		//
		//	$this->add( $model->fill( (array) $element ) );
		//}

		foreach ( $elements as $element ) {
			$this->add( $this->saver->make_model( $element ) );
		}

		return TRUE;
	}
}
