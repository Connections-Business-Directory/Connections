<?php

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field;
use Connections_Directory\Form\Field\Attribute\Autocomplete;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_string;

/**
 * Class Textarea
 *
 * @package Connections_Directory\Form\Field
 */
class Textarea extends Field {

	use Autocomplete;

	/**
	 * Field constructor.
	 */
	public function __construct() {
		$this->setDefaultValue( '' );
		parent::__construct();
	}

	/**
	 * Prepare the field attributes and stringify them.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	protected function prepareAttributes() {

		$attributes = array();
		$prefix     = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

		$classNames = _string::applyPrefix( $prefix, $this->class );

		/** @var string $id */
		$id = _string::applyPrefix( $prefix, $this->getId() );

		_array::set( $attributes, 'class', _escape::classNames( $classNames ) );
		_array::set( $attributes, 'id', _escape::id( $id ) );
		_array::set( $attributes, 'name', _escape::attribute( $this->getName() ) );
		_array::set( $attributes, 'style', _escape::css( _html::stringifyCSSAttributes( $this->css ) ) );

		if ( $this->isDisabled() ) {
			_array::set( $attributes, 'disabled', 'disabled' );
		}

		if ( $this->isReadOnly() ) {
			_array::set( $attributes, 'readonly', 'readonly' );
		}

		if ( $this->isRequired() && ( ! $this->isReadOnly() && ! $this->isDisabled() ) ) {
			_array::set( $attributes, 'class', "{$attributes['class']} required" );
			_array::set( $attributes, 'required', 'required' );
			_array::set( $attributes, 'aria-required', 'true' );
		}

		// Sort the attributes alphabetically, because, why not.
		ksort( $this->attributes, SORT_NATURAL );

		// Merge the remaining attributes.
		foreach ( $this->attributes as $attribute => $value ) {

			if ( false === array_key_exists( $attribute, $attributes ) ) {

				_array::set( $attributes, $attribute, _escape::attribute( $value ) );
			}
		}

		// Merge in the data attributes.
		$attributes = array_merge( $attributes, _html::prepareDataAttributes( $this->data ) );

		// _array::set( $attributes, 'value', _escape::attribute( $this->getValue() ) );

		return _html::stringifyAttributes( $attributes );
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getFieldHTML() {

		return '<textarea ' . $this->prepareAttributes() . '>' . esc_textarea( $this->getValue() ) . '</textarea>';
	}
}
