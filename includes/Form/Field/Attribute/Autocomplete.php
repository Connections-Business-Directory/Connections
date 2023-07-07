<?php

declare( strict_types=1 );

namespace Connections_Directory\Form\Field\Attribute;

/**
 * Trait Autocomplete
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Autocomplete {

	/**
	 * @link  https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/autocomplete
	 *
	 * @since 10.4
	 *
	 * @param bool|string $value
	 *
	 * @return static
	 */
	public function setAutocomplete( $value ) {

		if ( true === $value ) {

			$value = 'on';
		}

		if ( $value ) {

			$this->addAttribute( 'autocomplete', $value );

		} else {

			$this->removeAttribute( 'autocomplete' );
		}

		return $this;
	}
}
