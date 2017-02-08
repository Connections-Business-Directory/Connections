<?php
/**
 * The entry model.
 *
 * @author     Steven A. Zahm
 * @category   Database
 * @package    Connections\DB\Models
 * @since      8.5.34
 */

namespace Connections\DB\Models;

use IronBound\DB\Manager;
use IronBound\DB\Model;
//use Connections\DB\Query\FluentQuery;
use Connections\DB\Collection;
use Connections\DB\Relations\HasMany;
use Connections\DB\Table\Table;

use IronBound\DB\Relations\ManyToManyTerms;

/**
 * Class Entry
 *
 * @package Connections\DB\Models
 *
 * @property int        $id
 * @property Collection $address
 * @property Collection $phone
 * @property Collection $email
 * @property Collection $im
 * @property Collection $social
 * @property Collection $link
 * @property Collection $date
 * @property Collection $term
 */
class Entry extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {

		return $this->id;
	}

	/**
	 * @return HasMany
	 */
	protected function _address_relation() {

		$address = new Address();

		$relation = new HasMany( 'entry_id', get_class( $address ), $this, 'address' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @return HasMany
	 */
	protected function _phone_relation() {

		$address = new Phone();

		$relation = new HasMany( 'entry_id', get_class( $address ), $this, 'phone' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @return HasMany
	 */
	protected function _email_relation() {

		$phone = new Phone();

		$relation = new HasMany( 'entry_id', get_class( $phone ), $this, 'email' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @return HasMany
	 */
	protected function _im_relation() {

		$im = new IM();

		$relation = new HasMany( 'entry_id', get_class( $im ), $this, 'im' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @return HasMany
	 */
	protected function _social_relation() {

		$network = new IM();

		$relation = new HasMany( 'entry_id', get_class( $network ), $this, 'social' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @return HasMany
	 */
	protected function _link_relation() {

		$network = new Link();

		$relation = new HasMany( 'entry_id', get_class( $network ), $this, 'link' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @return HasMany
	 */
	protected function _date_relation() {

		$date = new Date();

		$relation = new HasMany( 'entry_id', get_class( $date ), $this, 'date' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @return ManyToManyTerms
	 */
	protected function _term_relation() {
		/** @noinspection PhpParamsInspection */
		return new ManyToManyTerms( $this, static::$_db_manager->get( 'entry-terms' ), 'term' );
	}

	/**
	 * @param \stdClass[] $items
	 */
	public function add_addresses( array $items = array() ) {

		foreach ( $items as &$row ) {

			$row = \cnSanitize::args( get_object_vars( $row ), Manager::get( 'address' )->get_column_defaults() );

			//$this->address->add( new Address( $address ) );
		}

		$this->address->add_many( $items, new Address() );
	}

	/**
	 * @param \stdClass[] $items
	 */
	public function add_phone_numbers( array $items = array() ) {

		foreach ( $items as $row ) {

			$number = \cnSanitize::args( get_object_vars( $row ), Manager::get( 'phone' )->get_column_defaults() );

			$this->phone->add( new Phone( $number ) );
		}
	}

	/**
	 * @param \stdClass[] $items
	 */
	public function add_email_addresses( array $items = array() ) {

		foreach ( $items as $row ) {

			$address = \cnSanitize::args( get_object_vars( $row ), Manager::get( 'email' )->get_column_defaults() );

			$this->email->add( new Email( $address ) );
		}
	}

	/**
	 * @param \stdClass[] $items
	 */
	public function add_im_ids( array $items = array() ) {

		foreach ( $items as $row ) {

			$id = \cnSanitize::args( get_object_vars( $row ), Manager::get( 'im' )->get_column_defaults() );

			$this->im->add( new IM( $id ) );
		}
	}

	/**
	 * @param \stdClass[] $items
	 */
	public function add_social_networks( array $items = array() ) {

		foreach ( $items as $row ) {

			$id = \cnSanitize::args( get_object_vars( $row ), Manager::get( 'social' )->get_column_defaults() );

			$this->social->add( new Social_Network( $id ) );
		}
	}

	/**
	 * @param \stdClass[] $items
	 */
	public function add_links( array $items = array() ) {

		foreach ( $items as &$row ) {

			$row = \cnSanitize::args( get_object_vars( $row ), Manager::get( 'link' )->get_column_defaults() );

			//$this->address->add( new Link( $link ) );
		}

		$this->link->add_many( $items, new Link() );
	}

	/**
	 * @param \stdClass[] $items
	 */
	public function add_dates( array $items = array() ) {

		foreach ( $items as &$row ) {

			$row = \cnSanitize::args( get_object_vars( $row ), Manager::get( 'date' )->get_column_defaults() );

			//$this->address->add( new Link( $link ) );
		}

		$this->date->add_many( $items, new Date() );
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

		return static::$_db_manager->get( 'entry' );
	}
}
