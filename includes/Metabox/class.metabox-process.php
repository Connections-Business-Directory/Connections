<?php
/**
 * Class for sanitizing and saving the user input from registered metaboxes.
 *
 * NOTE: This is a private class and should not be accessed directly.
 *
 * @package    Connections
 * @subpackage Metabox Processing
 * @copyright  Copyright (c) 2013, Steven A. Zahm
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      0.8
 */

use Connections_Directory\Utility\_sanitize;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnMetabox_Process
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnMetabox_Process {

	/**
	 * The array containing the registered metaboxes.
	 *
	 * @since 0.8
	 * @var array
	 */
	private $metabox = array();

	/**
	 * cnMetabox_Process constructor.
	 *
	 * @param $metabox
	 */
	public function __construct( $metabox ) {

		$this->metabox = $metabox;
	}

	/**
	 * Loops through the registered metaboxes sections and fields
	 * and save or update the metadata according to the current
	 * action being performed.
	 *
	 * @internal
	 * @since 0.8
	 *
	 * @param string $action The action being performed.
	 * @param int    $id     The object ID.
	 */
	public function process( $action, $id ) {

		$sections = isset( $this->metabox['sections'] ) && ! empty( $this->metabox['sections'] ) ? $this->metabox['sections'] : array();
		$fields   = isset( $this->metabox['fields'] ) && ! empty( $this->metabox['fields'] ) ? $this->metabox['fields'] : array();

		// If metabox sections have been registered, loop through them.
		if ( ! empty( $sections ) ) {

			foreach ( $sections as $section ) {

				if ( ! empty( $section['fields'] ) ) {
					$this->save( $action, $id, $section['fields'] );
				}
			}
		}

		// If metabox fields have been supplied, loop through them.
		if ( ! empty( $fields ) ) {

				$this->save( $action, $id, $fields );
		}
	}

	/**
	 * Save and or update the objects metadata
	 * based on the action being performed to the object.
	 *
	 * @since 0.8
	 *
	 * @param string $action The action being performed.
	 * @param int    $id     The object ID.
	 * @param array  $fields An array of the registered fields to save and or update.
	 *
	 * @return bool
	 */
	private function save( $action, $id, $fields ) {

		foreach ( $fields as $field ) {

			/**
			 * Filter field meta before it is inserted into the database.
			 *
			 * @since 8.5.14
			 *
			 * @param array  $field  An array of the registered field attributes.
			 * @param int    $id     The object ID.
			 * @param string $action The action being performed.
			 */
			$field = apply_filters( 'cn_pre_save_meta', $field, $id, $action );

			if ( ! $id = absint( $id ) ) {
				return false;
			}

			/**
			 * Filter to allow meta to not be saved.
			 *
			 * The dynamic portion of the filter name is so saving meta can be skipped based on the field ID.
			 *
			 * @since 8.5.14
			 *
			 * @param false $false Return TRUE to not save the field meta.
			 */
			if ( apply_filters( 'cn_pre_save_meta_skip', false ) || apply_filters( 'cn_pre_save_meta_skip-' . $field['id'], false ) ) {

				continue;
			}

			$value = $this->sanitize(
				$field['type'],
				isset( $_POST[ $field['id'] ] ) ? $_POST[ $field['id'] ] : null, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				isset( $field['options'] ) ? $field['options'] : array(),
				isset( $field['default'] ) ? $field['default'] : null
			);

			switch ( $action ) {

				case 'add':
				case 'copy':
					cnMeta::add( 'entry', $id, $field['id'], $value );
					break;

				case 'update':
					cnMeta::update( 'entry', $id, $field['id'], $value );
					break;
			}
		}
	}

	/**
	 * Sanitize use input based in field type.
	 *
	 * @internal
	 * @since 0.8
	 *
	 * @param string $type    The field type.
	 * @param mixed  $value   The value to sanitize.
	 * @param array  $options
	 * @param null   $default
	 *
	 * @return mixed
	 */
	public function sanitize( $type, $value, $options = array(), $default = null ) {

		switch ( $type ) {

			case 'checkbox':
				$value = cnSanitize::checkbox( $value );
				break;

			case 'checkboxgroup':
				$value = cnSanitize::options( $value, $options, $default );
				break;

			case 'radio':
			case 'radio_inline':
			case 'select':
				$value = cnSanitize::option( $value, $options, $default );
				break;

			case 'text':
				$value = sanitize_text_field( $value );
				break;

			case 'textarea':
				$value = sanitize_textarea_field( $value );
				break;

			case 'number':
				$value = filter_var(
					$value,
					FILTER_SANITIZE_NUMBER_FLOAT,
					array(
						'default' => $default,
						'flags'   => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_SCIENTIFIC,
					)
				);

				if ( false === $value ) {

					$value = '';
				}
				break;

			case 'slider':
				$value = absint( $value );
				break;

			case 'colorpicker':
				$value = _sanitize::hexColor( $value );
				break;

			case 'quicktag':
				$value = cnSanitize::quicktag( $value );
				break;

			case 'rte':
				$value = cnSanitize::html( $value );
				break;

			default:
				$value = apply_filters( 'cn_meta_sanitize_field-' . $type, $value, $options, $default );
				break;
		}

		return $value;
	}
}
