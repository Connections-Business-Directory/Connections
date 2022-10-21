<?php
/**
 * Get, validate, and validate an integer request variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\ID
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class ID
 *
 * @package Connections_Directory\Request
 */
class Integer extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = '';


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
	 * ID constructor.
	 *
	 * @since 10.4.32
	 *
	 * @param string $key The request variable parameter.
	 */
	public function __construct( $key = '' ) {

		if ( is_string( $key ) && 0 < strlen( $key ) ) {

			$this->key = $key;
		}

		parent::__construct();
	}

	/**
	 * Sanitize the ID.
	 *
	 * @since 10.4.8
	 *
	 * @param string|int $unsafe The value to sanitize.
	 *
	 * @return int
	 */
	protected function sanitize( $unsafe ) {

		return absint( $unsafe );
	}

	/**
	 * Validate the ID.
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
