<?php

namespace Connections_Directory\Form\Field\Attribute;

/**
 * Trait Name
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Name {

	/**
	 * The field name.
	 *
	 * @since  10.4
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Get the Field name.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getName() {

		return $this->name;
	}

	/**
	 * Set the Field name.
	 *
	 * @since 10.4
	 *
	 * @param $name
	 *
	 * @return static
	 */
	public function setName( $name ) {

		$this->name = $name;

		return $this;
	}
}
