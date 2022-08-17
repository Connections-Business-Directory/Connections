<?php

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_array;

/**
 * Trait Style
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Style {

	/**
	 * An associative array where the array key is the CSS attribute
	 * and the array value is the CSS attribute value.
	 *
	 * @since 10.4
	 * @var array
	 */
	protected $css = array();

	/**
	 * Set a CSS attribute.
	 *
	 * @since 10.4
	 *
	 * @param array|string $attribute
	 * @param string       $value
	 *
	 * @return static
	 */
	public function css( $attribute, $value = '' ) {

		if ( is_array( $attribute ) && 0 < count( $attribute ) ) {

			foreach ( $attribute as $property => $value ) {

				_array::set( $this->css, $property, $value );
			}

		} elseif ( is_string( $attribute ) && _::notEmpty( $value ) ) {

			_array::set( $this->css, $attribute, $value );
		}

		return $this;
	}
}
