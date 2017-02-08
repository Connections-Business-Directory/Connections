<?php
/**
 * The phone model.
 *
 * CREDIT: Iron Bound Designs
 *
 * @link       https://github.com/iron-bound-designs/IronBound-DB
 *
 * @author     Steven A. Zahm
 * @category   Database
 * @package    Connections\DB\Models
 * @since      8.5.34
 */

namespace Connections\DB\Models;

use IronBound\DB\Model;
use Connections\DB\Table\Table;

use IronBound\DB\Relations\HasForeign;

/**
 * Class Phone
 *
 * @package Connections\DB\Models
 */
class Phone extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {

		return $this->id;
	}

	/**
	 * @return HasForeign
	 */
	protected function _entry_relation() {

		return new HasForeign( 'entry', $this, get_class( new Entry() ) );
	}

	/**
	 * Get the table object for this model.
	 *
	 * This must be overwritten by sub-classes.
	 *
	 * @since 1.0
	 *
	 * @returns Table
	 */
	protected static function get_table() {

		return static::$_db_manager->get( 'phone' );
	}
}
