<?php
/**
 * Render a button element.
 *
 * @since 10.4.46
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Form\Field
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Classnames;
use Connections_Directory\Form\Field\Attribute\Data;
use Connections_Directory\Form\Field\Attribute\Disabled;
use Connections_Directory\Form\Field\Attribute\Id;
use Connections_Directory\Form\Field\Attribute\Name;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Style;
use Connections_Directory\Form\Field\Attribute\Value;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_string;

/**
 * Class Button
 *
 * @package Connections_Directory\Form\Field
 */
class Button {

	use Attributes;
	use Classnames;
	use Data;
	use Disabled;
	use Id;
	use Name;
	use Prefix;
	use Style;
	use Value;

	/**
	 * The button text.
	 *
	 * @var string 10.4.46
	 */
	protected $text = '';

	/**
	 * The Input field type.
	 *
	 * @since 10.4.46
	 * @var string
	 */
	protected $type = 'button';

	/**
	 * Field constructor.
	 *
	 * @param array $properties The button properties.
	 */
	public function __construct( array $properties = array() ) {

		$this->setPrefix( _array::get( $properties, 'prefix', '' ) );
		$this->addClass( _array::get( $properties, 'class', '' ) );
		$this->setId( _array::get( $properties, 'id', '' ) );
		$this->setName( _array::get( $properties, 'name', '' ) );
		$this->css( _array::get( $properties, 'style', array() ) );
		$this->addAttributes( _array::get( $properties, 'attributes', array() ) );
		$this->addData( _array::get( $properties, 'data', array() ) );
		$this->setDisabled( _array::get( $properties, 'disabled', false ) );

		$this->setType( _array::get( $properties, 'type', $this->type ) );
		$this->setValue( _array::get( $properties, 'value', '' ) );
		$this->text( _array::get( $properties, 'text', '' ) );
	}

	/**
	 * Create an instance of the Description field.
	 *
	 * @since 10.4.46
	 *
	 * @param array $properties The button properties.
	 *
	 * @return static
	 */
	public static function create( array $properties = array() ): Button {

		return new static( $properties );
	}

	/**
	 * Get the button text.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	public function getText(): string {

		return $this->text;
	}

	/**
	 * Set the button text.
	 *
	 * @since 10.4.46
	 *
	 * @param string $text Button text.
	 *
	 * @return static
	 */
	public function text( string $text ): Button {

		$this->text = $text;

		return $this;
	}

	/**
	 * Set the button type.
	 *
	 * @since 10.4.46
	 *
	 * @param string $type Button type.
	 *
	 * @return static
	 */
	public function setType( string $type ): Button {

		if ( in_array( $type, array( 'button', 'reset', 'submit' ), true ) ) {

			$this->type = $type;
		}

		return $this;
	}

	/**
	 * Prepare the field attributes and stringify them.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	protected function prepareAttributes(): string {

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
	 * Get button HTML.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	public function getHTML(): string {

		return '<button ' . $this->prepareAttributes() . '>' . _escape::html( $this->text ) . '</button>';
	}

	/**
	 * Echo the button HTML.
	 *
	 * @since 10.4.46
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 10.4.46
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
