<?php

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Utility\_;

/**
 * Trait Value
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Value {

	/**
	 * The Field default value.
	 *
	 * @since 10.4
	 * @var null
	 */
	protected $defaultValue = null;

	/**
	 * The Field value.
	 *
	 * @since 10.4
	 * @var null
	 */
	protected $value = null;

	/**
	 * Get the Field default value.
	 *
	 * @since 10.4
	 *
	 * @return mixed|null
	 */
	public function getDefaultValue() {

		return $this->defaultValue;
	}

	/**
	 * Set the Field default value.
	 *
	 * @since 10.4
	 *
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function setDefaultValue( $value ) {

		$this->defaultValue = $value;

		return $this;
	}

	/**
	 * Get the Field value.
	 *
	 * @since 10.4
	 *
	 * @return mixed|null
	 */
	public function getValue() {

		$value   = $this->value;
		$default = $this->getDefaultValue();

		if ( _::isEmpty( $value ) ) {

			$value = $default;
		}

		return $value;
	}

	/**
	 * Set the Field value.
	 *
	 * @since 10.4
	 *
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function setValue( $value ) {

		$this->value = $value;

		return $this;
	}
}
