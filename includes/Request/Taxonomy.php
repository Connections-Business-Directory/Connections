<?php
/**
 * Get, validate, and validate taxonomy type request variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Taxonomy Type
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Taxonomy
 *
 * @package Connections_Directory\Request
 */
class Taxonomy extends Input {

	/**
	 * The request method type.
	 *
	 * @since 10.4.8
	 *
	 * @var int
	 */
	protected $inputType = INPUT_POST;

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 'taxonomy';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'   => 'category',
		'minLength' => 1,
		'maxLength' => 32,
		'type'      => 'string',
	);

	/**
	 * Sanitize the taxonomy type.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ) {

		return sanitize_title_with_dashes( $unsafe );
	}

	/**
	 * Validate the taxonomy type.
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
