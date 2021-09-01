<?php

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Utility\_array;

/**
 * Trait Required
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Required {

	/**
	 * Whether the field is required.
	 *
	 * @since 10.4
	 *
	 * @return bool
	 */
	public function isRequired() {

		return _array::get( $this->attributes, 'required', false );
	}

	/**
	 * Set whether the field is required.
	 *
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setRequired( $bool ) {

		if ( $bool ) {

			$this->addAttribute( 'required', true );

		} else {

			$this->removeAttribute( 'required' );
		}

		return $this;
	}
}
