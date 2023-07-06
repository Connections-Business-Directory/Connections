<?php

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field;
use Connections_Directory\Form\Field\Attribute\Schema;
use Connections_Directory\Utility\_array;

/**
 * Class Input
 *
 * @package Connections_Directory\Form\Field
 */
abstract class Input extends Field {

	use Schema;

	/**
	 * Create an instance of the Input field.
	 *
	 * @since 10.4
	 *
	 * @param array $properties
	 *
	 * @return static
	 */
	public static function create( $properties = array() ) {

		$field = new static();

		if ( ! empty( $properties ) ) {

			$field->setPrefix( _array::get( $properties, 'prefix', '' ) );
			$field->addClass( _array::get( $properties, 'class', '' ) );
			$field->setId( _array::get( $properties, 'id', '' ) );
			$field->setName( _array::get( $properties, 'name', '' ) );
			$field->css( _array::get( $properties, 'style', array() ) );
			$field->addData( _array::get( $properties, 'data', array() ) );
			$field->setDisabled( _array::get( $properties, 'disabled', false ) );
			$field->setReadOnly( _array::get( $properties, 'readonly', false ) );
			$field->setRequired( _array::get( $properties, 'required', false ) );

			$field->setValue( _array::get( $properties, 'value', '1' ) );

			$field->addLabel(
				Field\Label::create()
						   ->setFor( $field->getId() )
						   ->text( _array::get( $properties, 'label', '' ) )
			);

			$field->prepend( _array::get( $properties, 'prepend', '' ) );
			$field->append( _array::get( $properties, 'append', '' ) );
		}

		return $field;
	}
}
