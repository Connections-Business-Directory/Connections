<?php
/**
 * Generate a select field.
 *
 * @since      10.4
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Form\Field
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_string;

/**
 * Class Select
 *
 * @package Connections_Directory\Form\Field
 */
class Select extends Input {

	/**
	 * The Select field default Option.
	 *
	 * @since 10.4
	 * @var null|Option
	 */
	private $defaultOption = null;

	/**
	 * Whether the Select field is enhanced with Chosen or Select2.
	 *
	 * @since 10.4
	 * @var bool
	 */
	private $isEnhanced = false;

	/**
	 * An array of Select Options fields.
	 *
	 * @since 10.4
	 * @var Option[]
	 */
	private $options = array();

	/**
	 * Whether the Select field is enhanced with Chosen or Select2.
	 *
	 * @since 10.4
	 *
	 * @return bool
	 */
	public function isEnhanced() {

		return $this->isEnhanced;
	}

	/**
	 * Set whether the Select field is enhanced with Chosen or Select2.
	 *
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setEnhanced( $bool ) {

		$this->isEnhanced = $bool;

		return $this;
	}

	/**
	 * Add a default Option to the Select field.
	 *
	 * @since 10.4
	 *
	 * @param Option $option
	 *
	 * @return static
	 */
	public function addDefaultOption( $option ) {

		$this->defaultOption = $option;

		return $this;
	}

	/**
	 * Whether the Select field has a default Option attached.
	 *
	 * @since 10.4
	 *
	 * @return bool
	 */
	public function hasDefaultOption() {

		return $this->defaultOption instanceof Option;
	}

	/**
	 * Removes the default Option from the Select field.
	 *
	 * @since 10.4
	 */
	public function removeDefaultOption() {

		$this->defaultOption = null;
	}

	/**
	 * Add an Option to the Select field.
	 *
	 * @since 10.4
	 *
	 * @param Option $option
	 */
	public function addOption( $option ) {

		$this->options[] = $option;
	}

	/**
	 * Return the array of Option objects.
	 *
	 * @since 10.4.64
	 *
	 * @return Option[]
	 */
	public function getOptions(): array {

		return $this->options;
	}

	/**
	 * Whether option have been added to the select dropdown.
	 *
	 * @since 10.4.64
	 *
	 * @return bool
	 */
	public function hasOptions(): bool {

		return 0 < count( $this->options );
	}

	/**
	 * Create Options fields from an array.
	 *
	 * @since 10.4
	 *
	 * @param array $options
	 *
	 * @return static
	 */
	public function createOptionsFromArray( $options ) {

		foreach ( $options as $properties ) {

			$this->addOption( Option::create( $properties ) );
		}

		return $this;
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

		// Add the 'cn-enhanced-select' class for the jQuery Chosen Plugin will enhance the dropdown.
		if ( $this->isEnhanced() ) {

			$this->addClass( 'cn-enhanced-select' );
		}

		$classNames = _string::applyPrefix( $prefix, $this->class );

		/** @var string $id */
		$id = _string::applyPrefix( $prefix, $this->getId() );

		_array::set( $attributes, 'class', _escape::classNames( $classNames ) );
		_array::set( $attributes, 'id', _escape::id( $id ) );
		_array::set( $attributes, 'name', _escape::attribute( $this->getName() ) );
		_array::set( $attributes, 'style', _escape::css( _html::stringifyCSSAttributes( $this->css ) ) );

		if ( $this->isReadOnly() ) {
			$this->setDisabled( true );
		}

		if ( $this->isDisabled() ) {
			_array::set( $attributes, 'disabled', 'disabled' );
		}

		if ( $this->isRequired() && ( ! $this->isReadOnly() && ! $this->isDisabled() ) ) {
			_array::set( $attributes, 'class', "{$attributes['class']} required" );
			_array::set( $attributes, 'required', 'required' );
			_array::set( $attributes, 'aria-required', 'true' );
		}

		// If the select IS an enhanced Select field.
		if ( $this->hasDefaultOption() && $this->isEnhanced() ) {

			// Create a blank Select field Option.
			$blankOption = Option::create()->setText( '' )->setValue( '' );

			// Prepend the blank option; required for Chosen.
			array_unshift( $this->options, $blankOption );

			// Add a placeholder data attribute required by Chosen.
			$this->addData( 'placeholder', $this->defaultOption->getText() );

		// If the select is NOT an enhanced Select field; prepend the default option to the top of the options array.
		} elseif ( $this->hasDefaultOption() && ! $this->isEnhanced() ) {

			/*
			 * @todo Default option attributes.
			 * The default option should have the `disabled selected hidden` attributes set.
			 * The `selected` attribute should be set only when the select value has not been set
			 * to prevent multiple options from having the `selected` attribute.
			 */

			array_unshift( $this->options, $this->defaultOption );
		}

		// Sort the attributes alphabetically, because, why not.
		ksort( $this->attributes, SORT_NATURAL );

		// Merge the remaining attributes.
		foreach ( $this->attributes as $attribute => $value ) {

			if ( false === array_key_exists( $attribute, $attributes ) ) {

				_array::set( $attributes, $attribute, _escape::attribute( $value ) );
			}
		}

		// Remove the name attribute, the field name will be applied to the hidden field added after the select field.
		// Remove the readonly attribute since it is not a valid attribute for select fields.
		if ( $this->isReadOnly() ) {

			_array::forget( $attributes, 'name' );
			_array::forget( $attributes, 'readonly' );
		}

		// Merge in the data attributes.
		$attributes = array_merge( $attributes, _html::prepareDataAttributes( $this->data ) );

		return _html::stringifyAttributes( $attributes );
	}

	/**
	 * Prepare the individual Options by setting their properties supplied to the Select field.
	 *
	 * @since 10.4
	 */
	protected function prepareOptions() {

		foreach ( $this->options as $field ) {

			$field->setDisabled( $this->isDisabled() );
			$field->maybeIsChecked( $this->getValue() );
		}
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getFieldHTML(): string {

		$this->prepareOptions();

		$attributes = $this->prepareAttributes();
		$options    = implode( '', $this->options );
		$hidden     = '';

		// If the select field is readonly, add a hidden field.
		if ( $this->isReadOnly() ) {

			$hidden = Hidden::create()
							->setName( $this->getName() )
							->setValue( $this->getValue() );
		}

		return "<select {$attributes}>{$options}</select>{$hidden}";
	}
}
