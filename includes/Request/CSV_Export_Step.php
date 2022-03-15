<?php
/**
 * Get, validate, and validate CSV Export step request variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\CSV Export Step
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class CSV_Export_Step
 *
 * @package Connections_Directory\Request
 */
class CSV_Export_Step extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 'step';

	/**
	 * The request method type.
	 *
	 * @since 10.4.8
	 *
	 * @var int
	 */
	protected $inputType = INPUT_POST;

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default' => 1,
		'type'    => 'integer',
		'minimum' => 1,
	);

	/**
	 * Sanitize the CSV Export step.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return int
	 */
	protected function sanitize( $unsafe ) {

		return absint( $unsafe );
	}

	/**
	 * Validate the CSV Export step.
	 *
	 * This is sufficiently validated against the schema, return `true`.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The raw request value to validate.
	 *
	 * @return true
	 */
	protected function validate( $unsafe ) {

		return true;
	}
}
