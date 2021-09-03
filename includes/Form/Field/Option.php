<?php

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Checked;
use Connections_Directory\Form\Field\Attribute\Disabled;
use Connections_Directory\Form\Field\Attribute\Value;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;

/**
 * Class Label
 *
 * @package Connections_Directory\Form\Field
 */
class Option {

	use Attributes;
	use Checked;
	use Disabled;
	use Value;

	/**
	 * The Option text.
	 *
	 * @var string 10.4
	 */
	protected $text = '';

	/**
	 * Field constructor.
	 */
	public function __construct() {
	}

	/**
	 * Create an instance of the Option field.
	 *
	 * @since 10.4
	 *
	 * @param array $properties
	 *
	 * @return static
	 */
	public static function create( $properties = array() ) {

		$option = new static();

		if ( ! empty( $properties ) ) {

			$option->setValue( _array::get( $properties, 'value', '' ) );
			$option->setText( _array::get( $properties, 'label', '' ) );
		}

		return $option;
	}

	/**
	 * Get the Option text.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getText() {

		return $this->text;
	}

	/**
	 * Set the Option text.
	 *
	 * @since 10.4
	 *
	 * @param $text
	 *
	 * @return static
	 */
	public function setText( $text ) {

		$this->text = $text;

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

		if ( $this->isDisabled() ) {
			_array::set( $attributes, 'disabled', 'disabled' );
		}

		_array::set( $attributes, 'value', _escape::attribute( $this->getValue() ) );

		if ( $this->isChecked() ) {

			_array::set( $attributes, 'selected', 'selected' );
		}

		return stringifyAttributes( $attributes );
	}

	/**
	 * Get the field and field label HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getHTML() {

		$attributes = $this->prepareAttributes();
		$text       = esc_html( $this->text );

		return "<option {$attributes}>{$text}</option>";
	}

	/**
	 * Echo field and field label HTML.
	 *
	 * @since 10.4
	 */
	public function render() {

		echo $this->getHTML();
	}

	/**
	 * @since 10.4
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
