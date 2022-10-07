<?php
/**
 * Get, validate, and validate the entry status request variable.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Entry Status
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Admin_Action
 *
 * @package Connections_Directory\Request
 */
class Entry_Status extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.31
	 *
	 * @var string
	 */
	protected $key = 'status';

	/**
	 * The input schema.
	 *
	 * @since 10.4.31
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'   => 'pending',
		'minLength' => 7,
		'maxLength' => 8,
		'type'      => 'string',
		'enum'      => array(
			'approved',
			'pending',
		),
	);

	/**
	 * Sanitize the entry status key.
	 *
	 * @since 10.4.31
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ) {

		return sanitize_key( $unsafe );
	}

	/**
	 * Validate the entry status key.
	 *
	 * This is sufficiently validated against the schema, return `true`.
	 *
	 * @since 10.4.31
	 *
	 * @param string $unsafe The raw request value to validate.
	 *
	 * @return true
	 */
	protected function validate( $unsafe ) {

		return true;
	}
}
