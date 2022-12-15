<?php
/**
 * Get, validate, and validate logs list table request variables.
 *
 * @since 10.4.33
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\List Table Logs
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
class List_Table_Logs extends Input {

	/**
	 * The request method type.
	 *
	 * @since 10.4.33
	 *
	 * @var int
	 */
	protected $inputType = INPUT_GET;

	/**
	 * The request variable key.
	 *
	 * @since 10.4.33
	 *
	 * @var string
	 */
	protected $key = 'list-table-logs';

	/**
	 * The input schema.
	 *
	 * @since 10.4.33
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'              => array(
			'type'     => '',
			'action'   => '',
			'selected' => array(),
			'paged'    => 1,
		),
		'type'                 => 'object',
		'additionalProperties' => false,
		'properties'           => array(
			'type'     => array(
				'minLength' => 3,
				'maxLength' => 265,
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
	 * @since 10.4.33
	 *
	 * @return array
	 */
	protected function getInput() {

		// Use FILTER_UNSAFE_RAW as the request variables will be validated and sanitized against the schema.
		$options = array(
			'type'     => FILTER_UNSAFE_RAW,
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
	 * @since 10.4.33
	 *
	 * @param array $unsafe The value to sanitize.
	 *
	 * @return array
	 */
	protected function sanitize( $unsafe ) {

		$type     = sanitize_title_with_dashes( _array::get( $unsafe, 'type', '' ) );
		$action   = sanitize_title_with_dashes( _array::get( $unsafe, 'action', '' ) );
		$selected = array_map( 'absint', array_filter( _array::get( $unsafe, 'selected', array() ) ) );
		$paged    = absint( _array::get( $unsafe, 'paged', 1 ) );

		return array(
			'type'     => $type,
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
	 * @since 10.4.33
	 *
	 * @param string $unsafe The raw request value to validate.
	 *
	 * @return true
	 */
	protected function validate( $unsafe ) {

		return true;
	}
}
