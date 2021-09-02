<?php

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
	 * @var Field_Label|null
	 */
	protected $label;

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
	 *
	 * @param Field_Label $label
	 * @param string      $position
	 *
	 * @return static
	 */
	public function addLabel( $label, $position = '' ) {

		$this->label = $label;

		if ( in_array( $position, array( 'before', 'after' ), true ) ) {

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

		echo $this->getLabelHTML();

		return $this;
	}

	/**
	 * Get the field label HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getLabelHTML() {

		if ( $this->label instanceof Field_Label ) {

			return $this->label->getHTML();
		}

		return '';
	}
}
