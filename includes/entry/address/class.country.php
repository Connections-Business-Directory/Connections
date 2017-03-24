<?php

/**
 * Class cnCountry
 *
 * @since 8.6
 */
final class cnCountry {

	/**
	 * @since 8.6
	 * @var string
	 */
	private $name;

	/**
	 * @since 8.6
	 * @var string
	 */
	private $code;

	/**
	 * @access public
	 * @since  8.6
	 *
	 * @param string $name
	 * @param string $code
	 */
	public function __construct( $name, $code ) {

		$this->name = $name;
		$this->code = $code;
	}

	/**
	 * Returns the country name
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return string
	 */
	public function getName() {

		return $this->name;
	}

	/**
	 * Returns the country ISO code.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return string
	 */
	public function getCode() {

		return $this->code;
	}

	/**
	 * Returns a string with the country name.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getName();
	}
}
