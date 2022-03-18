<?php
/**
 * Get, validate, and validate log ID request variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Log ID
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Log_ID
 *
 * @package Connections_Directory\Request
 */
class Log_ID extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 'log_id';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default' => 0,
		'type'    => 'integer',
	);

	/**
	 * Sanitize the Log ID.
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
	 * Validate the Log ID.
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
