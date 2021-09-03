<?php

namespace Connections_Directory\Form\Field\Attribute;

/**
 * Trait Id
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Id {

	/**
	 * @since 10.1
	 * @var string
	 */
	protected $id = '';

	/**
	 * Get the Field id.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getId() {

		return $this->id;
	}

	/**
	 * Set the Field id.
	 *
	 * @since 10.4
	 *
	 * @param string $id
	 *
	 * @return static
	 */
	public function setId( $id ) {

		$this->id = $id;

		return $this;
	}
}
