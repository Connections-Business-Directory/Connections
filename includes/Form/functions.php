<?php

namespace Connections_Directory\Form\Field;

use Connections_Directory\Utility\_array;

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
