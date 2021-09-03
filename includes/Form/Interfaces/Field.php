<?php

namespace Connections_Directory\Form\Interfaces;

use Connections_Directory\Form\Field\Label;

/**
 * Interface Field
 *
 * @package Connections_Directory\Form\Interfaces
 */
interface Field {

	/**
	 * Field constructor.
	 */
	public function __construct();

	/**
	 * @since 10.4
	 * @return static
	 */
	public static function create();

	/**
	 * @since 10.4
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function addAttribute( $key, $value );

	/**
	 * @since  10.4
	 *
	 * @param string $key
	 *
	 * @return static
	 */
	public function removeAttribute( $key );

	/**
	 * @since 10.4
	 *
	 * @param array|string $classNames
	 *
	 * @return static
	 */
	public function addClass( $classNames );

	/**
	 * @since 10.4
	 *
	 * @param string $class
	 *
	 * @return static
	 */
	public function removeClass( $class );

	/**
	 * @since 10.4
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function addData( $key, $value );

	/**
	 * @since 10.4
	 *
	 * @param string $key
	 *
	 * @return static
	 */
	public function removeData( $key );

	/**
	 * @since 10.4
	 * @return string
	 */
	public function getId();

	/**
	 * @since 10.4
	 *
	 * @param string $id
	 *
	 * @return static
	 */
	public function setId( $id );

	/**
	 * @since 10.4
	 * @return string
	 */
	public function getName();

	/**
	 * Set the field name attribute.
	 *
	 * @since 10.4
	 *
	 * @param $name
	 *
	 * @return static
	 */
	public function setName( $name );


	/**
	 * @since 10.4
	 * @return bool
	 */
	public function isDisabled();

	/**
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setDisabled( $bool );

	/**
	 * @since 10.4
	 * @return bool
	 */
	public function isReadOnly();

	/**
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setReadOnly( $bool );

	/**
	 * @since 10.4
	 * @return bool
	 */
	public function isRequired();

	/**
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setRequired( $bool );

	/**
	 * @since 10.4
	 *
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return static
	 */
	public function css( $attribute, $value );

	/**
	 * @since 10.4
	 *
	 * @param string $value
	 *
	 * @return static
	 */
	public function setDefaultValue( $value );

	/**
	 * @since 10.4
	 *
	 * @return mixed
	 */
	public function getDefaultValue();

	/**
	 * @since 10.4
	 * @return string
	 */
	public function getValue();

	/**
	 * @since 10.4
	 *
	 * @param string $value
	 *
	 * @return static
	 */
	public function setValue( $value );

	/**
	 * @since 10.4
	 *
	 * @return array|string
	 */
	public function getPrefix();

	/**
	 * @param array|string $string
	 *
	 * @return static
	 */
	public function setPrefix( $string );

	/**
	 * @since 10.4
	 *
	 * @param Label  $label
	 * @param string $position
	 *
	 * @return static
	 */
	public function addLabel( $label, $position );

	/**
	 * @since 10.4
	 *
	 * @return static
	 */
	public function removeLabel();

	/**
	 * @since 10.4
	 *
	 * @param string $string
	 *
	 * @return static
	 */
	public function prepend( $string );

	/**
	 * @since 10.4
	 *
	 * @param string $string
	 *
	 * @return static
	 */
	public function append( $string );

	/**
	 * Echo the field label HTML.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public function labelHTML();

	/**
	 * Get the field label HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getLabelHTML();

	/**
	 * Echo the field HTML.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public function fieldHTML();

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getFieldHTML();

	/**
	 * Get the field and field label HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getHTML();

	/**
	 * Echo field and field label HTML.
	 *
	 * @since 10.4
	 */
	public function render();

	/**
	 * @since 10.4
	 *
	 * @return string
	 */
	public function __toString();
}
