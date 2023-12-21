<?php
/**
 * Get, validate, and validate email system information request variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Email System Info
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_array;

/**
 * Class Email_System_Info
 *
 * @package Connections_Directory\Request
 */
final class Email_System_Info extends Input {

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
	protected $key = 'system-info-email';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'              => array(
			'email'   => '',
			'subject' => '',
			'message' => '',
		),
		'type'                 => 'object',
		'additionalProperties' => false,
		'properties'           => array(
			'email'   => array(
				'type'   => 'string',
				'format' => 'email',
			),
			'subject' => array(
				'type' => 'string',
			),
			'message' => array(
				'type' => 'string',
			),
		),
	);

	/**
	 * Get the request variable.
	 *
	 * @since 10.4.8
	 *
	 * @return array
	 */
	protected function getInput() {

		// Use FILTER_UNSAFE_RAW as the request variables will be validated and sanitized against the schema.
		$options = array(
			'email'   => FILTER_UNSAFE_RAW,
			'subject' => FILTER_UNSAFE_RAW,
			'message' => FILTER_UNSAFE_RAW,
		);

		return filter_input_array( $this->inputType, $options, true );
	}

	/**
	 * Sanitize the email fields.
	 *
	 * @since 10.4.8
	 *
	 * @param array $unsafe The value to be sanitized.
	 *
	 * @return array
	 */
	protected function sanitize( $unsafe ) {

		$email   = _array::get( $unsafe, 'email', '' );
		$subject = _array::get( $unsafe, 'subject', '' );
		$message = _array::get( $unsafe, 'message', '' );

		$sanitized = array(
			'email'   => sanitize_email( wp_unslash( $email ) ),
			'subject' => sanitize_text_field( wp_unslash( $subject ) ),
			'message' => sanitize_textarea_field( wp_unslash( $message ) ),
		);

		return $sanitized;
	}

	/**
	 * Validate the email fields.
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
