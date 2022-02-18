<?php

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Utility\_array;

/**
 * Trait Readonly
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Read_Only {

	/**
	 * Whether the field is Readonly.
	 *
	 * @since 10.4
	 *
	 * @return bool
	 */
	public function isReadOnly() {

		return _array::get( $this->attributes, 'readonly', false );
	}

	/**
	 * Set whether the field is Readonly.
	 *
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setReadOnly( $bool ) {

		if ( $bool ) {

			$this->addAttribute( 'readonly', true );

		} else {

			$this->removeAttribute( 'readonly' );
		}

		return $this;
	}
}
