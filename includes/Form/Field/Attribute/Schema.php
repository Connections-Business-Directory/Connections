<?php
/**
 * The field schema methods.
 *
 * @since 10.4.46
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections_Directory\Form\Field\Attribute
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form\Field\Attribute;

use Connections_Directory\Form\Field\Label as Field_Label;
use Connections_Directory\Utility\_;
use WP_Error;

/**
 * Trait Schema
 *
 * @package Connections_Directory\Form\Field\Attribute
 */
trait Schema {

	/**
	 * An instance of the WP_Error object.
	 *
	 * @since 10.4.46
	 * @var WP_Error
	 */
	protected $error;

	/**
	 * The defined field schema to use for validation.
	 *
	 * @since 10.4.46
	 * @var array
	 */
	protected $schema;

	/**
	 * Define the field schema.
	 *
	 * Supply as an associative array following the WP REST API schema pattern
	 *
	 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#primitive-types
	 *
	 * @since 10.4.46
	 *
	 * @param array $schema The field schema.
	 *
	 * @return static
	 */
	public function defineSchema( array $schema ) {

		$this->schema = $schema;

		return $this;
	}

	/**
	 * Get the defined field schema.
	 *
	 * @since 10.4.46
	 *
	 * @return array
	 */
	public function getSchema(): array {

		return $this->schema;
	}

	/**
	 * Whether the field has schema been defined in the attributes.
	 *
	 * @since 10.4.46
	 *
	 * @return bool
	 */
	public function hasSchema(): bool {

		return ! empty( $this->schema ) && array_key_exists( 'type', $this->schema );
	}

	/**
	 * Get field validation error.
	 *
	 * @since 10.4.46
	 *
	 * @return WP_Error
	 */
	public function getError(): WP_Error {

		return $this->error;
	}

	/**
	 * Whether the field has an error after validation.
	 *
	 * @since 10.4.46
	 *
	 * @return bool
	 */
	public function hasError(): bool {

		return $this->error instanceof WP_Error && $this->error->has_errors();
	}

	/**
	 * Validate field schema.
	 *
	 * @since 10.4.46
	 *
	 * @return bool
	 */
	public function validate(): bool {

		$this->error = new WP_Error();

		if ( ! $this->hasSchema() ) {

			$shortname = strtolower( _::getClassShortName( $this ) );

			$this->error->add(
				"{$shortname}_field_invalid_type",
				/* translators: %1$s: Field name. %2$s: Field type */
				sprintf( __( 'The "type" schema keyword for %1$s, %2$s field is required.', 'connections' ), $this->getName(), $shortname )
			);
		}

		// Set `%1$s` as the REST param, so `sprintf()` can be used to replace it with the field label or name.
		$isValid = rest_validate_value_from_schema( $this->getValue(), $this->getSchema(), '%1$s' );

		if ( $isValid instanceof WP_Error ) {

			$label = $this->label instanceof Field_Label ? $this->label->getText() : $this->getName();

			$this->error->add(
				$isValid->get_error_code(),
				sprintf( $isValid->get_error_message(), $label ),
				$this->getName()
			);
		}

		return $this->hasError();
	}
}
