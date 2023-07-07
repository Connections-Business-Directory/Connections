<?php

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
 * Class Description
 *
 * @package Connections_Directory\Form\Field
 */
class Description {

	use Classnames;
	use Id;
	use Prefix;
	use Style;

	/**
	 * The HTML element tag that contains the Field Description.
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $tag = 'p';

	/**
	 * The Field Description.
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
	 * Create an instance of the Description field.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public static function create() {

		return new static();
	}

	/**
	 * Get the element tag that contains the Field Description.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getTag() {

		return $this->tag;
	}

	/**
	 * Set the element tag that contains the Field Description.
	 *
	 * @since 10.4
	 *
	 * @param string $tag
	 *
	 * @return static
	 */
	public function setTag( $tag ) {

		$this->tag = $tag;

		return $this;
	}

	/**
	 * Set the description text.
	 *
	 * @since 10.4
	 *
	 * @param string $text Description text.
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

			$tag  = _escape::tagName( $this->tag );
			$html = "<{$tag} " . $this->prepareAttributes() . '>' . _escape::html( $this->text ) . "</{$tag}>";
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
	 * @since 10.4
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
