<?php
/**
 * Get, validate, and sanitize the role capability request variables.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Role_Capability
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use cnRole;
use Connections_Directory\Utility\_array;

/**
 * Class Role_Capability
 *
 * @package Connections_Directory\Request
 */
final class Role_Capability extends Input {

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
	 * @since 10.4.31
	 *
	 * @var string
	 */
	protected $key = 'capabilities';

	/**
	 * The input schema.
	 *
	 * @since 10.4.31
	 *
	 * @var array
	 */
	protected $schema = array(
		'default'              => array(
			'reset'     => array(),
			'reset_all' => false,
			'roles'     => array(),
		),
		'type'                 => 'object',
		'additionalProperties' => false,
		'properties'           => array(
			'reset'     => array(
				'type'                 => array(
					'null',
					'object',
				),
				'additionalProperties' => false,
				'properties'           => array(),
			),
			'reset_all' => array(
				'type' => array(
					'boolean',
					'null',
				),
			),
			'roles'     => array(
				'type'                 => array(
					'null',
					'object',
				),
				'additionalProperties' => false,
				'properties'           => array(),
			),
		),
	);

	/**
	 * Constructor.
	 *
	 * @since 10.4.31
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct() {

		$this->prepareSchema();
	}

	/**
	 * Dynamically build the schema for the role capabilities since this will change
	 * depending on the sites current configuration.
	 */
	private function prepareSchema() {

		$roles = get_editable_roles();

		// Drop the "administrator" role from the schema because that should not be modified.
		_array::forget( $roles, 'administrator' );

		$roleCapabilities = cnRole::capabilities();
		$roleProperties   = array_fill_keys( array_keys( $roleCapabilities ), array( 'type' => 'boolean' ) );

		foreach ( $roles as $slug => $details ) {

			_array::set(
				$this->schema,
				"properties.roles.properties.{$slug}",
				array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'capabilities' => array(
							'type'                 => 'object',
							'additionalProperties' => false,
							'properties'           => $roleProperties,
						),
					),
				)
			);

			_array::set(
				$this->schema,
				'properties.reset.properties',
				$this->getRestProperties( $roles )
			);
		}
	}

	/**
	 * Return the `reset` properties for the schema.
	 *
	 * @since 10.4.31
	 *
	 * @param array $roles Array of arrays containing role information. {@see get_editable_roles()}.
	 *
	 * @return array
	 */
	private function getRestProperties( $roles ) {

		$properties = array();

		foreach ( $roles as $slug => $role ) {

			$properties[ $slug ] = array(
				'type' => 'string',
				'enum' => array( translate_user_role( $role['name'] ) ),
			);
		}

		return $properties;
	}

	/**
	 * Get the request variable.
	 *
	 * @since 10.4.31
	 *
	 * @return array
	 */
	protected function getInput() {

		// Use FILTER_UNSAFE_RAW as the request variables will be validated and sanitized against the schema.
		$options = array(
			'reset'     => array(
				'filter' => FILTER_UNSAFE_RAW,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'reset_all' => FILTER_VALIDATE_BOOLEAN,
			'roles'     => array(
				'filter' => FILTER_UNSAFE_RAW,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
		);

		return filter_input_array( $this->inputType, $options, true );
	}

	/**
	 * Sanitize role capability request variables.
	 *
	 * @since 10.4.31
	 *
	 * @param array $unsafe The value to be sanitized.
	 *
	 * @return array
	 */
	protected function sanitize( $unsafe ) {

		$sanitized   = array();
		$modifyRoles = _array::get( $unsafe, 'roles', array() );
		$resetAll    = _array::get( $unsafe, 'reset_all', false );
		$resetRoles  = _array::get( $unsafe, 'reset', array() );

		if ( is_array( $resetRoles ) ) {

			foreach ( $resetRoles as $roleSlug => $roleName ) {

				$roleName = sanitize_text_field( $roleName );
				$roleSlug = sanitize_key( $roleSlug );

				_array::set( $sanitized, "reset.{$roleSlug}", $roleName );
			}
		}

		_array::set( $sanitized, 'reset_all', rest_sanitize_boolean( $resetAll ) );

		foreach ( $modifyRoles as $roleSlug => $capabilities ) {

			$roleSlug = sanitize_key( $roleSlug );

			foreach ( $capabilities['capabilities'] as $capability => $grant ) {

				$capability = sanitize_key( $capability );

				_array::set( $sanitized, "roles.{$roleSlug}.capabilities.{$capability}", rest_sanitize_boolean( $grant ) );
			}
		}

		return $sanitized;
	}

	/**
	 * Validate the request.
	 *
	 * This is sufficiently validated against the schema, return `true`.
	 *
	 * @since 10.4.31
	 *
	 * @param string $unsafe The raw request value to validate.
	 *
	 * @return true
	 */
	protected function validate( $unsafe ) {

		return true;
	}
}
