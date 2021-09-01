<?php

namespace Connections_Directory\Form\Field\Attribute;

/**
 * Trait Prefix
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Prefix {

	/**
	 * The prefix to be applied to the Field class names and id.
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Get prefix to be applied to the Field class names and id.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getPrefix() {

		return $this->prefix;
	}

	/**
	 * Prefix to be applied to the field `class` and `id` attributes when rendering the field.
	 *
	 * @since 10.4
	 *
	 * @param string $string
	 *
	 * @return static
	 */
	public function setPrefix( $string ) {

		if ( ! is_string( $string ) ) {

			return $this;
		}

		$this->prefix = $string;

		return $this;
	}
}
