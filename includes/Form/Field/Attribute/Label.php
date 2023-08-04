<?php

declare( strict_types=1 );

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Form\Field\Label as Field_Label;

/**
 * Trait Label
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Label {

	/**
	 * Instance of Label.
	 *
	 * @since 10.4
	 * @since 10.4.26 Make the property public for access to object methods.
	 *
	 * @var Field_Label|null
	 */
	public $label;

	/**
	 * Position of the checkbox field label. Default: `after`
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $labelPosition = 'after';

	/**
	 * Add an instance of Label to the Field.
	 *
	 * @since 10.4
	 * @since 10.4.39 Add the `implicit` as a valid label position.
	 *
	 * @param Field_Label $label    An instance of the Label object.
	 * @param string      $position Whether the label should come before or after or wrap the computer.
	 *
	 * @return static
	 */
	public function addLabel( $label, $position = '' ) {

		$this->label = $label;

		$this->setLabelPosition( $position );

		return $this;
	}

	/**
	 * Set the label position.
	 *
	 * @since 10.4.46
	 *
	 * @param string $position The label position.
	 *
	 * @return static
	 */
	public function setLabelPosition( string $position ) {

		if ( in_array( $position, array( 'before', 'after', 'implicit', 'implicit/after', 'implicit/before' ), true ) ) {

			$this->labelPosition = $position;
		}

		return $this;
	}

	/**
	 * Remove the Label attached to the Field.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public function removeLabel() {

		$this->label         = null;
		$this->labelPosition = 'after';

		return $this;
	}

	/**
	 * Echo the field label HTML.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public function labelHTML() {

		echo $this->getLabelHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return $this;
	}

	/**
	 * Get the field label HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getLabelHTML(): string {

		if ( $this->label instanceof Field_Label ) {

			return $this->label->getHTML();
		}

		return '';
	}
}
