<?php
/**
 * Get, validate, and validate Manage admin page filters request variables.
 *
 * @since 10.4.17
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Manage Filters
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_array;

/**
 * Class Manage_Filter
 *
 * @package Connections_Directory\Request
 */
final class Manage_Filter extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.17
	 *
	 * @var string
	 */
	protected $key = 'manage_filter';

	/**
	 * The input schema.
	 *
	 * @since 10.4.17
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'              => array(
			'category'   => 0,
			'pg'         => 1,
			'status'     => 'all',
			'type'       => 'all',
			'visibility' => 'all',
		),
		'type'                 => 'object',
		'additionalProperties' => true,
		'properties'           => array(
			'category'   => array(
				'type' => 'integer',
			),
			'pg'         => array(
				'type' => 'integer',
			),
			'status'     => array(
				'type' => 'string',
				'enum' => array(
					'all',
					'approved',
					'pending',
				),
			),
			'type'       => array(
				'type' => 'string',
				'enum' => array(
					'all',
					'individual',
					'organization',
					'family',
				),
			),
			'visibility' => array(
				'type' => 'string',
				'enum' => array(
					'all',
					'public',
					'private',
					'unlisted',
				),
			),
		),
	);

	/**
	 * Get the request variable.
	 *
	 * @since 10.4.17
	 *
	 * @return array
	 */
	protected function getInput() {

		// Use FILTER_UNSAFE_RAW as the request variables will be validated and sanitized against the schema.
		$options = array(
			'category'   => FILTER_UNSAFE_RAW,
			'pg'         => FILTER_UNSAFE_RAW,
			'status'     => FILTER_UNSAFE_RAW,
			'type'       => FILTER_UNSAFE_RAW,
			'visibility' => FILTER_UNSAFE_RAW,
		);

		return filter_input_array( $this->inputType, $options, false );
	}

	/**
	 * Sanitize the filters.
	 *
	 * @since 10.4.17
	 *
	 * @param array $unsafe The value to be sanitized.
	 *
	 * @return array
	 */
	protected function sanitize( $unsafe ) {

		$sanitized = array();

		if ( array_key_exists( 'category', $unsafe ) ) {

			_array::set( $sanitized, 'category', absint( $unsafe['category'] ) );
		}

		if ( array_key_exists( 'pg', $unsafe ) ) {

			_array::set( $sanitized, 'pg', absint( $unsafe['pg'] ) );
		}

		if ( array_key_exists( 'status', $unsafe ) ) {

			_array::set( $sanitized, 'status', sanitize_key( $unsafe['status'] ) );
		}

		if ( array_key_exists( 'type', $unsafe ) ) {

			_array::set( $sanitized, 'type', sanitize_key( $unsafe['type'] ) );
		}

		if ( array_key_exists( 'visibility', $unsafe ) ) {

			_array::set( $sanitized, 'visibility', sanitize_key( $unsafe['visibility'] ) );
		}

		return $sanitized;
	}

	/**
	 * Validate the filters.
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
