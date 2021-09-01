<?php

namespace Connections_Directory\Form\Field;

/**
 * Class Checkbox_Group
 *
 * @package Connections_Directory\Form\Field
 */
class Checkbox_Group extends Group {

	/**
	 * An array of Checkbox Input fields.
	 *
	 * @since 10.4
	 * @var Checkbox[]
	 */
	protected $inputs = array();

	/**
	 * Add a Checkbox Input field.
	 *
	 * @since 10.4
	 *
	 * @param Checkbox $input
	 */
	public function addInput( $input ) {

		$this->inputs[] = $input;
	}

	/**
	 * Create Checkbox Input fields from an array.
	 *
	 * @since 10.4
	 *
	 * @param array $inputs
	 *
	 * @return static
	 */
	public function createInputsFromArray( $inputs ) {

		foreach ( $inputs as $properties ) {

			$this->addInput( Checkbox::create( $properties ) );
		}

		return $this;
	}

	/**
	 * Prepare the individual checkbox inputs by setting their properties supplied to the checkbox group
	 * because they are to be applied to the field level and not the checkbox group field container HTML.
	 *
	 * @since 10.4
	 */
	protected function prepareInputs() {

		foreach ( $this->inputs as $field ) {

			$field->setPrefix( $this->getPrefix() );
			$field->addClass( $this->class );
			$field->setId( "{$this->getId()}[{$field->getValue()}]" );
			$field->setName( "{$this->getId()}[]" );
			$field->css( $this->css );
			$field->addData( $this->data );
			$field->setDisabled( $this->isDisabled() );
			$field->setReadOnly( $this->isReadOnly() );
			$field->setRequired( $this->isRequired() );
			$field->maybeIsChecked( $this->getValue() );

			$field->label->setFor( $field->getId() );
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

		return "<{$tags['parent']} class=\"cn-checkbox-group\">$html</{$tags['parent']}>";
	}

	/**
	 * NOTE: Checkboxes have the __toString() magic method,
	 * so they can be imploded since they are stored as an array of Checkboxes.
	 *
	 * @since 10.4
	 *
	 * @param string $tag The checkbox field HTML.
	 */
	private function walkInputs( $tag ) {

		$html = implode( "</{$tag}><{$tag} class=\"cn-checkbox-option\">", $this->inputs );

		return "<{$tag} class=\"cn-checkbox-option\">{$html}</{$tag}>";
	}
}
