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
	 * @param array $properties The field properties.
	 */
	public function __construct( array $properties = array() ) {

		parent::__construct( $properties );

		if ( ! empty( $properties ) ) {

			$this->defineSchema( _array::get( $properties, 'schema', array() ) );

			$this->setPrefix( _array::get( $properties, 'prefix', '' ) );
			$this->addClass( _array::get( $properties, 'class', '' ) );
			$this->setId( _array::get( $properties, 'id', '' ) );
			$this->setName( _array::get( $properties, 'name', '' ) );
			$this->css( _array::get( $properties, 'style', array() ) );
			$this->addAttributes( _array::get( $properties, 'attributes', array() ) );
			$this->addData( _array::get( $properties, 'data', array() ) );
			$this->setDisabled( _array::get( $properties, 'disabled', false ) );
			$this->setReadOnly( _array::get( $properties, 'readonly', false ) );
			$this->setRequired( _array::get( $properties, 'required', false ) );

			$this->setValue( _array::get( $properties, 'value', '' ) );

			$this->addLabel(
				Field\Label::create()
						   ->setFor( $this->getId() )
						   ->text( _array::get( $properties, 'label', '' ) )
			);

			$this->prepend( _array::get( $properties, 'prepend', '' ) );
			$this->append( _array::get( $properties, 'append', '' ) );

			$this->setDefaultValue( _array::get( $properties, 'default', '' ) );
		}
	}
}
