<?php
/**
 * Get, validate, and validate search term request variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Search
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Search
 *
 * @package Connections_Directory\Request
 */
class Search extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 's';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'   => '',
		'minLength' => 1,
		'maxLength' => 1600,
		'type'      => 'string',
	);

	/**
	 * Sanitize the search term.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ) {

		return sanitize_text_field( $unsafe );
	}

	/**
	 * Validate the search term.
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
