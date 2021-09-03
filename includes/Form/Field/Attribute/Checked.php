<?php

namespace Connections_Directory\Form\Field\Attribute;

/**
 * Trait Checked
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Checked {

	/**
	 * Whether a checkbox/radio/select option is selected.
	 *
	 * @since 10.4
	 * @var bool
	 */
	private $isChecked = false;

	/**
	 * Whether a checkbox/radio/select option is selected.
	 *
	 * @since 10.4
	 *
	 * @return bool
	 */
	public function isChecked() {

		return $this->isChecked;
	}

	/**
	 * Determines whether a checkbox/radio/select option is selected.
	 *
	 * NOTE: This must be called after @see Value::setValue()
	 *
	 * @since 10.4
	 *
	 * @param mixed $value The value saved in the database.
	 *
	 * @return static
	 */
	public function maybeIsChecked( $value ) {

		/*
		 * Convert the supplied value to an array and coerce to a string.
		 * This is done so a single checkbox and checkbox groups
		 * (values for a checkbox group are saved to the database as an array) can be evaluated.
		 */
		$value = is_array( $value ) ? $value : (array) $value;
		$value = array_map( 'strval', $value );

		/**
		 * Similar to the logic in the core WordPress function @see checked()
		 * The field value is coerced to a string for strict comparison.
		 */
		if ( in_array( (string) $this->getValue(), $value, true ) ) {

			$this->setChecked( true );

		} else {

			$this->setChecked( false );
		}

		return $this;
	}

	/**
	 * Set checked property.
	 *
	 * @since 10.4
	 *
	 * @param bool $bool
	 *
	 * @return static
	 */
	public function setChecked( $bool ) {

		$this->isChecked = $bool;

		return $this;
	}
}
