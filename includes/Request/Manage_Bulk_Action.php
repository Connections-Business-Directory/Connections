<?php
/**
 * Get, validate, and validate the Manage admin page bulk action request variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Manage Bulk Action
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Utility\_array;

/**
 * Class Manage_Bulk_Action
 *
 * @package Connections_Directory\Request
 */
class Manage_Bulk_Action extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 'action';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'   => '',
		'minLength' => 3,
		'maxLength' => 256,
		'type'      => 'string',
		'enum'      => array(
			'approve',
			'unapprove',
			'delete',
			'public',
			'private',
			'unlisted',
		),
	);

	/**
	 * Get the request variable schema.
	 *
	 * @since 10.4.17
	 *
	 * @return array The request variable schema.
	 */
	protected function getSchema() {

		$schema = parent::getSchema();

		$enum = apply_filters(
			'Connections_Directory/Request/Manage_Bulk_Actions/Enum',
			_array::get( $schema, 'enum' )
		);

		return _array::set( $schema, 'enum', $enum );
	}

	/**
	 * Sanitize the admin page key.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ) {

		return sanitize_key( $unsafe );
	}

	/**
	 * Validate the admin page key.
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
