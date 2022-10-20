<?php
/**
 * Get, validate, and sanitize the template request variables.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Template
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_array;

/**
 * Class Template
 *
 * @package Connections_Directory\Request
 */
final class Template extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.31
	 *
	 * @var string
	 */
	protected $key = 'template';

	/**
	 * The input schema.
	 *
	 * @since 10.4.31
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'              => array(
			'template' => '',
			'type'     => '',
		),
		'type'                 => 'object',
		'additionalProperties' => false,
		'properties'           => array(
			'template' => array(
				'type' => 'string',
			),
			'type' => array(
				'type' => 'string',
			),
		),
	);

	/**
	 * Get the request variable.
	 *
	 * @since 10.4.31
	 *
	 * @return array
	 */
	protected function getInput() {

		// Use FILTER_UNSAFE_RAW as the request variables will be validated and sanitized against the schema.
		$options = array(
			'template' => FILTER_UNSAFE_RAW,
			'type'     => FILTER_UNSAFE_RAW,
		);

		return filter_input_array( $this->inputType, $options, true );
	}

	/**
	 * Sanitize template request variables.
	 *
	 * @since 10.4.31
	 *
	 * @param array $unsafe The value to be sanitized.
	 *
	 * @return array
	 */
	protected function sanitize( $unsafe ) {

		$template = _array::get( $unsafe, 'template', '' );
		$type     = _array::get( $unsafe, 'type', '' );

		return array(
			'template' => sanitize_key( $template ),
			'type'     => sanitize_key( $type ),
		);
	}

	/**
	 * Validate the request.
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
