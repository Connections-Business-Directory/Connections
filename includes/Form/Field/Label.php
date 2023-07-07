<?php
/**
 * Form field Label object.
 *
 * @since 10.4
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Form\Field
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Classnames;
use Connections_Directory\Form\Field\Attribute\Id;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Style;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_string;

/**
 * Class Label
 *
 * @package Connections_Directory\Form\Field
 */
class Label {

	use Classnames;
	use Id;
	use Prefix;
	use Style;

	/**
	 * The field id the Label is attached to.
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $for = '';

	/**
	 * The Label text.
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $text = '';

	/**
	 * Field constructor.
	 */
	public function __construct() {
	}

	/**
	 * Create an instance of the Label.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public static function create() {

		return new static();
	}

	/**
	 * Get the field id that the Label is for.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getFor() {

		return $this->for;
	}

	/**
	 * Set the field id that the Label is for.
	 *
	 * @since 10.4
	 *
	 * @param string $for The for attribute value.
	 *
	 * @return static
	 */
	public function setFor( $for ) {

		$this->for = $for;

		return $this;
	}

	/**
	 * Get the field label text.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	public function getText(): string {

		return $this->text;
	}

	/**
	 * Set the label text.
	 *
	 * @since 10.4
	 *
	 * @param string $text The label text.
	 *
	 * @return static
	 */
	public function text( $text ) {

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

		/** @var string $id */
		$id = _string::applyPrefix( $prefix, $this->getId() );

		_array::set( $attributes, 'class', _escape::classNames( $classNames ) );
		_array::set( $attributes, 'id', _escape::id( $id ) );
		_array::set( $attributes, 'for', _escape::attribute( $this->getFor() ) );
		_array::set( $attributes, 'style', _escape::css( _html::stringifyCSSAttributes( $this->css ) ) );

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

		$html = '';

		if ( 0 < strlen( $this->text ) ) {

			$attributes = $this->prepareAttributes();

			// If there are label attributes; add a leading space.
			if ( 0 < strlen( $attributes ) ) {

				$attributes = " $attributes";
			}

			$html = '<label' . $attributes . '>' . _escape::html( $this->text ) . '</label>';
		}

		return $html;
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
	 * Return object as string.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
