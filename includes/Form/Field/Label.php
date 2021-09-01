<?php

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Classnames;
use Connections_Directory\Form\Field\Attribute\Id;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Style;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
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
	 * 10.4
	 * @var string
	 */
	protected $for = '';

	/**
	 * The Label text.
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
	 * Create an instance of the Label.
	 *
	 * @sine 10.4
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
	 * @param $for
	 *
	 * @return static
	 */
	public function setFor( $for ) {

		$this->for = $for;

		return $this;
	}

	/**
	 * Set the label text.
	 *
	 * @since 10.4
	 *
	 * @param $text
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
		$id         = _string::applyPrefix( $prefix, $this->getId() );

		_array::set( $attributes, 'class', _escape::classNames( $classNames ) );
		_array::set( $attributes, 'id', _escape::id( $id ) );
		_array::set( $attributes, 'for', _escape::attribute( $this->getFor() ) );
		_array::set( $attributes, 'style', stringifyCSSAttributes( $this->css ) );

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

		$html = '';

		if ( 0 < strlen( $this->text ) ) {

			$html = '<label ' . $this->prepareAttributes() . '>' . _escape::html( $this->text ) . '</label>';
		}

		return $html;
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
