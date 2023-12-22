<?php
/**
 * Get, validate, and validate taxonomy term request variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Term
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_array;

/**
 * Class Term
 *
 * @package Connections_Directory\Request
 */
final class Term extends Input {

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
	protected $key = 'term';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'              => array(
			'taxonomy'         => 'category',
			'term-id'          => 0,
			'term-name'        => '',
			'term-slug'        => '',
			'term-parent'      => 0,
			'term-description' => '',
		),
		'type'                 => 'object',
		'additionalProperties' => false,
		'properties'           => array(
			'taxonomy'         => array(
				'minLength' => 1,
				'maxLength' => 32,
				'type'      => 'string',
			),
			'term-id'          => array(
				'type'    => 'integer',
				'minimum' => 0,
			),
			'term-name'        => array(
				'type' => 'string',
			),
			'term-slug'        => array(
				'type' => 'string',
			),
			'term-parent'      => array(
				'type'    => 'integer',
				'minimum' => 0,
			),
			'term-description' => array(
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
			'taxonomy'             => FILTER_UNSAFE_RAW,
			'term-id'              => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			),
			'term-name'            => FILTER_UNSAFE_RAW,
			'term-slug'            => FILTER_UNSAFE_RAW,
			'term-parent'          => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'default'   => 0,
					'min_range' => 0,
				),
			),
			'term-description'     => FILTER_UNSAFE_RAW,
			// Legacy request variable keys.
			'term_id'              => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			),
			'term_name'            => FILTER_UNSAFE_RAW,
			'term_slug'            => FILTER_UNSAFE_RAW,
			'term_parent'          => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'default'   => 0,
					'min_range' => 0,
				),
			),
			'term_description'     => FILTER_UNSAFE_RAW,
			// Original category request variable keys.
			'category_id'          => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
			),
			'category_name'        => FILTER_UNSAFE_RAW,
			'category_slug'        => FILTER_UNSAFE_RAW,
			'category_parent'      => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'default'   => 0,
					'min_range' => 0,
				),
			),
			'category_description' => FILTER_UNSAFE_RAW,
		);

		$request = filter_input_array( $this->inputType, $options, false );

		// Map the legacy and original request variable keys to the "modern" keys.
		return array(
			'taxonomy'         => _array::get( $request, 'taxonomy', '' ),
			'term-id'          => (int) _array::get( $request, 'term-id', _array::get( $request, 'term_id', _array::get( $request, 'category_id', 0 ) ) ),
			'term-name'        => _array::get( $request, 'term-name', _array::get( $request, 'term_name', _array::get( $request, 'category_name', '' ) ) ),
			'term-slug'        => _array::get( $request, 'term-slug', _array::get( $request, 'term_slug', _array::get( $request, 'category_slug', '' ) ) ),
			'term-parent'      => (int) _array::get( $request, 'term-parent', _array::get( $request, 'term_parent', _array::get( $request, 'category_parent', 0 ) ) ),
			'term-description' => _array::get( $request, 'term-description', _array::get( $request, 'term_description', _array::get( $request, 'category_description', '' ) ) ),
		);
	}

	/**
	 * Sanitize the taxonomy term fields.
	 *
	 * Term fields are escaped in `cnTerm::insert()` utilizing `sanitize_term()`. Return raw request data.
	 *
	 * @see \cnTerm::insert()
	 *
	 * @since 10.4.8
	 *
	 * @param array $unsafe The value to be sanitized.
	 *
	 * @return array
	 */
	protected function sanitize( $unsafe ) {

		return $unsafe;
	}

	/**
	 * Validate the taxonomy term fields.
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
