<?php
/**
 * Get, validate, and sanitize an array of ID request variables.
 *
 * @since 10.4.17
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Int Array
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Int_Array
 *
 * @package Connections_Directory\Request
 */
class Integer_Array extends Input {

	/**
	 * The request filter.
	 *
	 * @since 10.4.17
	 *
	 * @var int
	 */
	protected $inputFilter = FILTER_VALIDATE_INT;

	/**
	 * The request filter/flag options.
	 *
	 * @since 10.4.17
	 *
	 * @var int
	 */
	protected $inputFilterOptions = FILTER_REQUIRE_ARRAY;

	/**
	 * The request variable key.
	 *
	 * @since 10.4.17
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * The input schema.
	 *
	 * @since 10.4.17
	 *
	 * @var array
	 */
	protected $schema = array(
		'default' => array(),
		'type'    => 'array',
		'items'   => array(
			'type' => 'integer',
		),
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
	 * @since 10.4.17
	 *
	 * @param int[] $unsafe The value to sanitize.
	 *
	 * @return int[]
	 */
	protected function sanitize( $unsafe ) {

		return array_map( 'absint', $unsafe );
	}

	/**
	 * Validate the ID.
	 *
	 * This is sufficiently validated against the schema, return `true`.
	 *
	 * @since 10.4.17
	 *
	 * @param string $unsafe The raw request value to validate.
	 *
	 * @return true
	 */
	protected function validate( $unsafe ) {

		return true;
	}
}
