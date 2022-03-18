<?php
/**
 * Get, validate, and validate taxonomy list table request variables.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\List Table Taxonomy
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_array;

/**
 * Class List_Table_Taxonomy
 *
 * @package Connections_Directory\Request
 */
class List_Table_Taxonomy extends Input {

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
	protected $key = 'list-table-taxonomy';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'              => array(
			'taxonomy' => 'category',
			'action'   => '',
			'selected' => array(),
			'paged'    => 1,
		),
		'type'                 => 'object',
		'additionalProperties' => false,
		'properties'           => array(
			'taxonomy' => array(
				'minLength' => 1,
				'maxLength' => 32,
				'type'      => 'string',
			),
			'action'   => array(
				'type' => 'string',
			),
			'selected' => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'integer',
				),
			),
			'paged'    => array(
				'type'    => 'integer',
				'minimum' => 1,
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
			'taxonomy' => FILTER_UNSAFE_RAW,
			'action'   => FILTER_UNSAFE_RAW,
			'selected' => array(
				'filter'  => FILTER_VALIDATE_INT,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'options' => array(
					'default'   => 0,
					'min_range' => 0,
				),
			),
			'paged'    => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'default'   => 1,
					'min_range' => 1,
				),
			),
		);

		return filter_input_array( $this->inputType, $options, false );
	}

	/**
	 * Sanitize the list table input request variables.
	 *
	 * @since 10.4.8
	 *
	 * @param array $unsafe The value to sanitize.
	 *
	 * @return array
	 */
	protected function sanitize( $unsafe ) {

		$taxonomy = sanitize_title_with_dashes( _array::get( $unsafe, 'taxonomy', 'category' ) );
		$action   = sanitize_title_with_dashes( _array::get( $unsafe, 'action', '' ) );
		$selected = array_map( 'absint', array_filter( _array::get( $unsafe, 'selected', array() ) ) );
		$paged    = absint( _array::get( $unsafe, 'paged', 1 ) );

		return array(
			'taxonomy' => $taxonomy,
			'action'   => $action,
			'selected' => $selected,
			'paged'    => $paged,
		);
	}

	/**
	 * Validate the list table request variables.
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
