<?php

namespace Connections_Directory\Form\Field;

use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;

/**
 * Helper function to remap legacy field choices to be compatible with the Form Field API.
 *
 * @since 10.4
 *
 * @param array $parameters
 *
 * @return array
 */
function remapOptions( &$parameters ) {

	if ( _array::exists( $parameters, 'options' ) && is_array( $parameters['options'] ) ) {

		$inputs = array();

		foreach ( $parameters['options'] as $value => $label ) {

			$inputs[] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		$parameters['options'] = $inputs;
	}

	return $parameters;
}

/**
 * Prepare form field data attributes.
 *
 * @since 10.4
 *
 * @param array $data
 *
 * @return array
 */
function prepareDataAttributes( $data ) {

	$attributes = array();

	foreach ( $data as $property => $value ) {

		if ( _::isEmpty( $value ) ) {

			continue;
		}

		// String.
		if ( is_string( $value ) ) {

			$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );

			// Boolean.
		} elseif ( is_bool( $value ) ) {

			$value = $value ? 1 : 0;

			// Array|Object.
		} elseif ( is_array( $value ) || is_object( $value ) ) {

			$value = htmlspecialchars( json_encode( $value ), ENT_QUOTES, 'UTF-8' );
		}

		/**
		 * Create valid HTML5 data attributes.
		 *
		 * @link http://stackoverflow.com/a/22753630/5351316
		 * @link https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/dataset
		 */
		$key = 'data-' . sanitize_title_with_dashes( $property );

		$attributes[ $key ] = $value;
	}

	// Sort the attributes alphabetically, because, why not.
	ksort( $attributes, SORT_NATURAL );

	return $attributes;
}

/**
 * Prepare and stringify the style attribute CSS declarations.
 *
 * @since 10.4
 *
 * @param string[] $css An associative array where the key is the CSS property and the value is the CSS property value.
 *
 * @return string
 */
function stringifyCSSAttributes( $css ) {

	// Filter out empty attributes, but allow `0` (zero) values.
	$css = array_filter( $css, '\Connections_Directory\Utility\_::notEmpty' );

	// Sort the attributes alphabetically, because, why not.
	ksort( $css, SORT_NATURAL );

	array_walk(
		$css,
		function( &$value, $property ) {

			$value = _escape::attribute( $property ) . ': ' . _escape::attribute( $value );
		}
	);

	return implode( '; ', $css );
}

/**
 * Stringify form field attributes.
 *
 * @since 10.4
 *
 * @param string[] $attributes
 *
 * @return string
 */
function stringifyAttributes( $attributes ) {

	// Filter out empty attributes, but allow `0` (zero) values.
	$attributes = array_filter( $attributes, '\Connections_Directory\Utility\_::notEmpty' );

	array_walk(
		$attributes,
		function( &$value, $attribute ) {

			// String; do not trim `value` attribute.
			if ( is_string( $value ) ) {

				if ( 'value' !== $attribute ) {

					$v = trim( $value );

				} else {

					$v = htmlspecialchars( $value );
				}

				// Boolean.
			} elseif ( is_bool( $value ) ) {

				$v = $value ? 1 : 0;

				// Array|Object.
			} elseif ( is_array( $value ) || is_object( $value ) ) {

				$v = json_encode( $value );

			} else {

				$v = $value;
			}

			$value = "{$attribute}=\"{$v}\"";
		}
	);

	return implode( ' ', $attributes );
}

