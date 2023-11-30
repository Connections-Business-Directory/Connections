<?php
/**
 * Generate the HTML for a select option.
 *
 * @since      10.4
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Form\Field\Option
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Checked;
use Connections_Directory\Form\Field\Attribute\Classnames;
use Connections_Directory\Form\Field\Attribute\Disabled;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Style;
use Connections_Directory\Form\Field\Attribute\Value;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_string;

/**
 * Class Option
 *
 * @package Connections_Directory\Form\Field
 */
class Option {

	use Attributes;
	use Checked;
	use Classnames;
	use Disabled;
	use Prefix;
	use Style;
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
		$prefix     = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

		$classNames = _string::applyPrefix( $prefix, $this->class );

		_array::set( $attributes, 'class', _escape::classNames( $classNames ) );
		_array::set( $attributes, 'style', _escape::css( _html::stringifyCSSAttributes( $this->css ) ) );

		if ( $this->isDisabled() ) {
			_array::set( $attributes, 'disabled', 'disabled' );
		}

		if ( $this->isChecked() ) {

			_array::set( $attributes, 'selected', 'selected' );
		}

		// Sort the attributes alphabetically, because, why not.
		ksort( $this->attributes, SORT_NATURAL );

		// Merge the remaining attributes.
		foreach ( $this->attributes as $attribute => $value ) {

			if ( false === array_key_exists( $attribute, $attributes ) ) {

				_array::set( $attributes, $attribute, _escape::attribute( $value ) );
			}
		}

		_array::set( $attributes, 'value', _escape::attribute( $this->getValue() ) );

		return _html::stringifyAttributes( $attributes );
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

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
