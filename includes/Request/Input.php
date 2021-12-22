<?php
/**
 * Base abstract class used to get, validate, and sanitize request variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Input
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_array;
use WP_Error;

/**
 * Class Input
 *
 * @package Connections_Directory\Request
 * @since 10.4.4
 */
abstract class Input {

	/**
	 * Object instances.
	 *
	 * @since 10.4.8
	 *
	 * @var static[]
	 */
	private static $instance = array();

	/**
	 * The request filter.
	 *
	 * Use FILTER_UNSAFE_RAW as the request variable will be validated and sanitized against the schema.
	 *
	 * @since 10.4.8
	 *
	 * @var int
	 */
	protected $inputFilter = FILTER_UNSAFE_RAW;

	/**
	 * The request filter/flag options.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $inputFilterOptions = array();

	/**
	 * The request method type.
	 *
	 * @since 10.4.8
	 *
	 * @var int
	 */
	protected $inputType = INPUT_GET;

	/**
	 * The input scheme.
	 *
	 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array();

	/**
	 * The validated and sanitized request variable.
	 *
	 * @since 10.4.8
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Set up the variable object.
	 *
	 * @since 10.4.8
	 */
	public function __construct() {}

	/**
	 * Avoid clone instance.
	 *
	 * @since 10.4.8
	 */
	private function __clone() {}

	/**
	 * Avoid serialize instance.
	 *
	 * @since 10.4.8
	 */
	public function __sleep() {}

	/**
	 * Avoid unserialize instance.
	 *
	 * @since 10.4.8
	 */
	public function __wakeup() {}

	/**
	 * Get the singleton instance of request variable.
	 *
	 * @since 10.4.8
	 *
	 * @param mixed ...$params The input parameters.
	 *
	 * @return static
	 */
	public static function input( ...$params ) {

		if ( ! array_key_exists( static::class, self::$instance ) ) {

			self::$instance[ static::class ] = new static( ...$params );
		}

		return self::$instance[ static::class ];
	}

	/**
	 * Use this method to override the defined input method.
	 *
	 * Some request variables are accessed via both INPUT_GET and INPUT_POST.
	 * For example, ID request variable. It can be passed either through GET or POST depending on context.
	 *
	 * @see $inputType
	 *
	 * @since 10.4.8
	 *
	 * @param int   $method    The input method to get the input variable from.
	 * @param mixed ...$params The input parameters.
	 *
	 * @return static
	 */
	final public static function from( $method, ...$params ) {

		$input            = static::input( ...$params );
		$input->inputType = $method;

		return $input;
	}

	/**
	 * Register as a query variable.
	 *
	 * Call on the `init` or `wp_loaded` action hooks in order to be registered before the filter is applied.
	 *
	 * @since 10.4.8
	 */
	final public static function registerQueryVar() {

		$key = static::input()->key;

		add_filter(
			'Connections_Directory/Rewrite/Query_Vars',
			function( $keys ) use ( $key ) {

				$keys[] = $key;

				return $keys;
			}
		);
	}

	/**
	 * Return the default value set in the request variable schema.
	 *
	 * @since 10.4.8
	 *
	 * @return mixed
	 */
	final protected function getDefault() {

		return _array::get( $this->getSchema(), 'default' );
	}

	/**
	 * Get the request variable.
	 *
	 * @since 10.4.8
	 *
	 * @return mixed
	 */
	protected function getInput() {

		$default = $this->getDefault();

		$value = filter_has_var( $this->inputType, $this->key )
			? filter_input(
				$this->inputType,
				$this->key,
				$this->inputFilter,
				$this->inputFilterOptions
			) : $default;

		if ( is_string( $value ) ) {

			$value = wp_unslash( $value );
		}

		return $value;
	}

	/**
	 * Get the request variable schema.
	 *
	 * @since 10.4.4
	 *
	 * @return array The request variable schema.
	 */
	final protected function getSchema() {

		return array_merge(
			array(
				'default' => null,
			),
			$this->schema
		);
	}

	/**
	 * Validate and sanitize the request variable.
	 *
	 * @since 10.4.8
	 */
	private function process() {

		// No need to validate more than once.
		if ( false === isset( $this->value ) ) {

			$unsafe  = $this->getInput();
			$default = $this->getDefault();
			$isValid = $this->_validate( $unsafe );

			if ( $isValid instanceof WP_Error ) {

				// Failed validation, set the default value from supplied default or from schema.
				$this->value = $default;

			} else {

				$sanitized = $this->_sanitize( $unsafe );

				if ( $sanitized instanceof WP_Error ) {

					// Failed sanitization, set the default value from supplied default or from schema.
					$this->value = $default;

				} else {

					$this->value = $sanitized;
				}

			}

		}
	}

	/**
	 * Sanitize the request variable against the scheme.
	 *
	 * @since 10.4.4
	 *
	 * @param mixed $unsafe The request value to be sanitized.
	 *
	 * @return mixed|WP_Error
	 *
	 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
	 */
	private function _sanitize( $unsafe ) {

		$schema = $this->getSchema();
		$value  = rest_sanitize_value_from_schema( $unsafe, $schema, $this->key );

		if ( $value instanceof WP_Error ) {

			return new WP_Error( 'parameter failed sanitization' );
		}

		return $this->sanitize( $value );
	}

	/**
	 * Sanitize the request variable.
	 *
	 * The value has already had preliminary sanitization applied.
	 * Additional sanitization should be applied as necessary.
	 *
	 * @since 10.4.8
	 *
	 * @param mixed $unsafe The value to be sanitized.
	 *
	 * @return mixed
	 */
	abstract protected function sanitize( $unsafe );

	/**
	 * Validate the request variable against the schema.
	 *
	 * @since 10.4.4
	 *
	 * @param mixed $unsafe The request value to be validated.
	 *
	 * @return true|WP_Error
	 *
	 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
	 */
	private function _validate( $unsafe ) {

		$schema  = $this->getSchema();
		$isValid = rest_validate_value_from_schema( $unsafe, $schema, $this->key );

		if ( $isValid instanceof WP_Error ) {

			return new WP_Error( 'parameter failed validation' );
		}

		return $this->validate( $unsafe );
	}

	/**
	 * Validate the request variable.
	 *
	 * NOTE: The raw request variable is passed for validation. It should be considered unsafe.
	 *
	 * @since 10.4.8
	 *
	 * @param mixed $unsafe The raw request value to validate.
	 *
	 * @return true|WP_Error
	 */
	abstract protected function validate( $unsafe );

	/**
	 * Get the validated and sanitized request variable.
	 *
	 * NOTE: The value must be properly prepared based on context.
	 * Example, escaping for display vs. escaping for editing in a form field vs. preparing for a database insert.
	 *
	 * @since 10.4.4
	 *
	 * @return mixed
	 */
	final public function value() {

		$this->process();

		return $this->value;
	}
}
