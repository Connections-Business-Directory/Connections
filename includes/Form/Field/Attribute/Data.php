<?php

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Utility\_array;

/**
 * Trait Data
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Data {

	/**
	 * An array of data attributes to add to the Field.
	 *
	 * @since 10.4
	 * @var array
	 */
	protected $data = array();

	/**
	 * Add a data or an array of data attributes to the Field.
	 *
	 * @since 10.4
	 *
	 * @param array|string $key
	 * @param mixed        $value
	 *
	 * @return static
	 */
	public function addData( $key, $value = null ) {

		if ( is_array( $key ) ) {

			foreach ( $key as $property => $value ) {

				_array::set( $this->data, $property, $value );
			}

		} else {

			_array::set( $this->data, $key, $value );
		}

		return $this;
	}

	/**
	 * Remove a data attribute from the Field.
	 *
	 * @since  10.4
	 *
	 * @param string $key
	 *
	 * @return static
	 */
	public function removeData( $key ) {

		_array::forget( $this->data, $key );

		return $this;
	}
}
