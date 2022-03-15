<?php
/**
 * Get, validate, and validate log type request variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Log Type
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Log_Type
 *
 * @package Connections_Directory\Request
 */
class Log_Type extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 'type';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'   => '',
		'minLength' => 3,
		'maxLength' => 256,
		'type'      => 'string',
	);

	/**
	 * Sanitize the CSV Export type.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ) {

		return sanitize_key( $unsafe );
	}

	/**
	 * Validate the CSV Export type.
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
