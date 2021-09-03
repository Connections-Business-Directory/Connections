<?php

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Utility\_array;

/**
 * Trait Disabled
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Disabled {

	/**
	 * Whether the Field is disabled.
	 *
	 * @since 10.4
	 *
	 * @return bool
	 */
	public function isDisabled() {

		return _array::get( $this->attributes, 'disabled', false );
	}

	/**
	 * Set whether the Field is disabled.
	 *
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setDisabled( $bool ) {

		if ( $bool ) {

			$this->addAttribute( 'disabled', true );

		} else {

			$this->removeAttribute( 'disabled' );
		}

		return $this;
	}
}
