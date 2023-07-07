<?php

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Utility\_array;

/**
 * Trait Attributes
 *
 * @package Connections_Directory\Form\Field
 */
trait Attributes {

	/**
	 * An associative array of field attributes where the attribute name is the array key
	 * and the array key value is the attribute value.
	 *
	 * @since 10.4
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Add an attribute to a field.
	 *
	 * @since 10.4
	 *
	 * @param string $key   The attribute name.
	 * @param mixed  $value The attribute value.
	 *
	 * @return static
	 */
	public function addAttribute( $key, $value ) {

		_array::set( $this->attributes, $key, $value );

		return $this;
	}

	/**
	 * Add an array of attributes to a field.
	 *
	 * @since 10.4.46
	 *
	 * @param array $attributes The field attributes.
	 *
	 * @return static
	 */
	public function addAttributes( array $attributes ) {

		foreach ( $attributes as $property => $value ) {

			_array::set( $this->attributes, $property, $value );
		}

		return $this;
	}

	/**
	 * Remove an attribute from a field.
	 *
	 * @since  10.4
	 *
	 * @param string $key The attribute to remove from the field by its name.
	 *
	 * @return static
	 */
	public function removeAttribute( $key ) {

		_array::forget( $this->attributes, $key );

		return $this;
	}
}
