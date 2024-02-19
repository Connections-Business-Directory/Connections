<?php

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Autocomplete;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_string;

/**
 * Class Text
 *
 * @package Connections_Directory\Form\Field
 */
class Text extends Input {

	use Autocomplete;

	/**
	 * Position of the checkbox field label. Default: `before`
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $labelPosition = 'before';

	/**
	 * The Input field type.
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $type = 'text';

	/**
	 * Create an instance of the field.
	 *
	 * @since 10.4.46
	 *
	 * @param array $attributes The field properties.
	 */
	public function __construct( array $attributes = array() ) {

		parent::__construct( $attributes );

		$this->setAutocomplete( _array::get( $attributes, 'autocomplete', '' ) );
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

		_array::set( $attributes, 'type', _escape::attribute( $this->type ) );
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

		_array::set( $attributes, 'value', _escape::attribute( $this->getValue() ) );

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

		return '<input ' . $this->prepareAttributes() . ' />';
	}
}
