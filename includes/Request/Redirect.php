<?php
/**
 * Get, validate, and validate the `redirect_to` request variable.
 *
 * @since 10.4.49
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Admin Action
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Request;

/**
 * Class Admin_Action
 *
 * @package Connections_Directory\Request
 */
class Redirect extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.49
	 *
	 * @var string
	 */
	protected $key = 'redirect_to';

	/**
	 * The input schema.
	 *
	 * @since 10.4.49
	 *
	 * @var array
	 */
	protected $schema = array(
		'default' => '',
		'type'    => 'string',
		'format'  => 'uri',
	);

	/**
	 * Sanitize the URL.
	 *
	 * @since 10.4.49
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ): string {

		return sanitize_url( $unsafe );
	}

	/**
	 * Validate the URL.
	 *
	 * This is sufficiently validated against the schema, return `true`.
	 *
	 * @since 10.4.49
	 *
	 * @param string $unsafe The raw request value to validate.
	 *
	 * @return true
	 */
	protected function validate( $unsafe ): bool {

		return true;
	}
}
