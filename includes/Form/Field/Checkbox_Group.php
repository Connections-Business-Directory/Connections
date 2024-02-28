<?php
/**
 * Generate a checkbox group field.
 *
 * @since      10.4
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Form\Field
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Utility\_string;

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
	 * @param Checkbox $input A Checkbox field.
	 *
	 * @return static
	 */
	public function addInput( Checkbox $input ): self {

		$this->inputs[] = $input;

		return $this;
	}

	/**
	 * Create Checkbox Input fields from an array.
	 *
	 * @since 10.4
	 *
	 * @param array $inputs An array to create Checkbox fields for the group.
	 *
	 * @return static
	 */
	public function createInputsFromArray( array $inputs ): self {

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

		$prefix = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

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
	public function getFieldHTML(): string {

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
	private function walkInputs( string $tag ): string {

		$html = implode( "</{$tag}><{$tag} class=\"cn-checkbox-option\">", $this->inputs );

		return "<{$tag} class=\"cn-checkbox-option\">{$html}</{$tag}>";
	}
}
