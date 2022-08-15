<?php

namespace Connections_Directory\Form\Field\Attribute;

/**
 * Trait Classnames
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Classnames {

	/**
	 * An array of classnames to be applied to the Field.
	 *
	 * @since 10.4
	 * @var string[]
	 */
	protected $class = array();

	/**
	 * Add a class name or an array of class names to the Field.
	 *
	 * @since 10.4
	 *
	 * @param array|string $classNames
	 *
	 * @return static
	 */
	public function addClass( $classNames ) {

		if ( ! is_array( $classNames ) ) {

			$classNames = array( $classNames );
		}

		foreach ( $classNames as $className ) {

			$className = trim( $className );

			if ( false === array_search( $className, $this->class, true ) && 0 < strlen( $className ) ) {

				array_push( $this->class, $className );
			}
		}

		return $this;
	}

	/**
	 * Remove a class name from the Field.
	 *
	 * @since 10.4
	 *
	 * @param string $class
	 *
	 * @return static
	 */
	public function removeClass( $class ) {

		if ( is_string( $class ) ) {

			$class = trim( $class );

			if ( false !== $key = array_search( $class, $this->class, true ) ) {

				unset( $this->class[ $key ] );
			}
		}

		return $this;
	}
}
