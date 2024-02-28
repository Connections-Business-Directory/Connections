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
	 * @param array $attributes The field properties.
	 */
	public function __construct( array $attributes = array() ) {

		parent::__construct( $attributes );

		if ( ! empty( $attributes ) ) {

			$this->defineSchema( _array::get( $attributes, 'schema', array() ) );

			$this->setPrefix( _array::get( $attributes, 'prefix', '' ) );
			$this->addClass( _array::get( $attributes, 'class', '' ) );
			$this->setId( _array::get( $attributes, 'id', '' ) );
			$this->setName( _array::get( $attributes, 'name', '' ) );
			$this->css( _array::get( $attributes, 'style', array() ) );
			$this->addAttributes( _array::get( $attributes, 'attributes', array() ) );
			$this->addData( _array::get( $attributes, 'data', array() ) );
			$this->setDisabled( _array::get( $attributes, 'disabled', false ) );
			$this->setReadOnly( _array::get( $attributes, 'readonly', false ) );
			$this->setRequired( _array::get( $attributes, 'required', false ) );

			$this->setValue( _array::get( $attributes, 'value', '' ) );

			$label = _array::get( $attributes, 'label', '' );

			if ( 0 < strlen( $label ) ) {

				$this->addLabel(
					Field\Label::create()
							   ->setFor( $this->getId() )
							   ->text( $label )
				);
			}

			$this->prepend( _array::get( $attributes, 'prepend', '' ) );
			$this->append( _array::get( $attributes, 'append', '' ) );

			$this->setDefaultValue( _array::get( $attributes, 'default', '' ) );
		}
	}
}
