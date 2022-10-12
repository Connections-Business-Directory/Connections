<?php
/**
 * Get, validate, and validate nonce request variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Nonce
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_nonce;

/**
 * Class Nonce
 *
 * @package Connections_Directory\Request
 */
final class Nonce extends Input {

	/**
	 * The request method type.
	 *
	 * @since 10.4.8
	 *
	 * @var int
	 */
	protected $inputType = INPUT_GET;

	/**
	 * The nonce action.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key;

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
	 * Constructor.
	 *
	 * @since 10.4.8
	 *
	 * @param string      $action        Nonce action name.
	 * @param null|string $item          Item name. Use when protecting multiple items on the same page.
	 * @param null|string $queryArgument Key to check for nonce in `$_REQUEST`.
	 */
	public function __construct( $action, $item = null, $queryArgument = null ) {

		$this->action = is_scalar( $item ) ? _nonce::action( $action, $item ) : _nonce::action( $action );
		$this->key    = is_scalar( $queryArgument ) ? $queryArgument : _nonce::NAME;

		parent::__construct();
	}

	/**
	 * Override the parent because Nonce can have multiple instances per request.
	 *
	 * @since 10.4.8
	 *
	 * @param mixed{action: string, item: null|string, queryArgument: null|string} ...$params The input parameters.
	 *
	 * @return Nonce
	 */
	public static function input( ...$params ) {

		return new self( ...$params );
	}

	/**
	 * Sanitize the nonce.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The value to be sanitized.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ) {

		return sanitize_key( $unsafe );
	}

	/**
	 * Validate the nonce.
	 *
	 * This is sufficiently validated against the schema, return `true`.
	 *
	 * @since 10.4.8
	 *
	 * @param mixed $unsafe The raw request value to validate.
	 *
	 * @return true
	 */
	protected function validate( $unsafe ) {

		return true;
	}

	/**
	 * Whether the nonce is valid.
	 *
	 * @since 10.4.8
	 *
	 * @return bool
	 */
	public function isValid() {

		$nonce = wp_verify_nonce( $this->value(), $this->action );

		if ( 1 === $nonce || 2 === $nonce ) {

			return true;
		}

		return false;
	}
}
