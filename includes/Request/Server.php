<?php
/**
 * Get, validate, and validate Server variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Server
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Server_HTTP_Host
 *
 * @package Connections_Directory\Request
 */
abstract class Server extends Input {

	/**
	 * The request method type.
	 *
	 * @since 10.4.8
	 *
	 * @var int
	 */
	protected $inputType = INPUT_SERVER;

	/**
	 * The request filter/flag options.
	 *
	 * @since 10.4.8
	 *
	 * @var int
	 */
	protected $inputFilterOptions = FILTER_NULL_ON_FAILURE;

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default' => '',
		'type'    => 'string',
	);

	/**
	 * Get the request variable.
	 *
	 * @link https://github.com/xwp/stream/issues/254
	 *
	 * @since 10.4.8
	 *
	 * @return string
	 */
	protected function getInput() {

		if ( filter_has_var( $this->inputType, $this->key ) ) {

			$value = filter_input( $this->inputType, $this->key, $this->inputFilter, $this->inputFilterOptions );

		} else {

			if ( isset( $_SERVER[ $this->key ] ) ) {

				$value = filter_var( $_SERVER[ $this->key ], $this->inputFilter, $this->inputFilterOptions );

			} else {

				$value = '';
			}
		}

		return $value;
	}

	/**
	 * Sanitize the server request variable.
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
	 * Validate the server request variable.
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
