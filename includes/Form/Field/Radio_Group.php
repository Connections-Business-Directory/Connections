<?php

namespace Connections_Directory\Form\Field;

use Connections_Directory\Utility\_string;

/**
 * Class Text
 *
 * @package Connections_Directory\Form\Field
 */
class Radio_Group extends Group {

	/**
	 * An array of Radio Input fields.
	 *
	 * @since 10.4
	 * @var Radio[]
	 */
	protected $inputs = array();

	/**
	 * Add a Radio Input field.
	 *
	 * @since 10.4
	 *
	 * @param Radio $input
	 *
	 * @return static
	 */
	public function addInput( $input ) {

		$this->inputs[] = $input;

		return $this;
	}

	/**
	 * Create Radio Input fields from an array.
	 *
	 * @since 10.4
	 *
	 * @param array $inputs
	 *
	 * @return static
	 */
	public function createInputsFromArray( $inputs ) {

		foreach ( $inputs as $properties ) {

			$this->addInput( Radio::create( $properties ) );
		}

		return $this;
	}

	/**
	 * Prepare the individual radio inputs by setting their properties supplied to the radio group
	 * because they are to be applied to the field level and not the radio group field container HTML.
	 *
	 * @since 10.4
	 */
	protected function prepareInputs() {

		$prefix = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

		foreach ( $this->inputs as $field ) {

			$class = 0 < count( $field->class ) ? $field->class : $this->class;
			$id    = 0 < strlen( $field->getId() ) ? $field->getId() : "{$this->getId()}[{$field->getValue()}]";
			$name  = 0 < strlen( $field->getName() ) ? $field->getName() : $this->getId();
			$css   = 0 < count( $field->css ) ? $field->css : $this->css;

			$field->setPrefix( $this->getPrefix() );
			$field->addClass( $class );
			$field->setId( $id );
			$field->setName( $name );
			$field->css( $css );
			$field->addData( $this->data );
			$field->setDisabled( $this->isDisabled() );
			$field->setReadOnly( $this->isReadOnly() );
			$field->setRequired( $this->isRequired() );
			$field->maybeIsChecked( $this->getValue() );

			if ( $field->label instanceof Label ) {

				$field->label->setFor( _string::applyPrefix( $prefix, $field->getId() ) );
			}
		}
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getFieldHTML() {

		$tags = $this->getContainerTags();

		$this->prepareInputs();

		$html = $this->walkInputs( $tags['child'] );

		return "<{$tags['parent']} class=\"cn-radio-group\">$html</{$tags['parent']}>";
	}

	/**
	 * NOTE: Radio fields have the __toString() magic method,
	 * so they can be imploded since they are stored as an array of Radio field.
	 *
	 * @since 10.4
	 *
	 * @param string $tag The radio field HTML.
	 */
	private function walkInputs( $tag ) {

		$html = implode( "</{$tag}><{$tag} class=\"cn-radio-option\">", $this->inputs );

		return "<{$tag} class=\"cn-radio-option\">{$html}</{$tag}>";
	}
}
