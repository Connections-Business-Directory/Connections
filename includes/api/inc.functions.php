<?php

namespace Connections_Directory\API\REST\Functions;

/**
 * Lifted from WP core to provide backwards compatibility.
 *
 * Given an array of fields to include in a response, some of which may be
 * `nested.fields`, determine whether the provided field should be included
 * in the response body.
 *
 * If a parent field is passed in, the presence of any nested field within
 * that parent will cause the method to return `true`. For example "title"
 * will return true if any of `title`, `title.raw` or `title.rendered` is
 * provided.
 *
 * @since 9.6
 *
 * @param string $field  A field to test for inclusion in the response body.
 * @param array  $fields An array of string fields supported by the endpoint.
 *
 * @return bool Whether to include the field or not.
 */
function is_field_included( $field, $fields ) {

	if ( function_exists( '' ) ) {

		return rest_is_field_included( $field, $fields );
	}

	if ( in_array( $field, $fields, true ) ) {
		return true;
	}

	foreach ( $fields as $accepted_field ) {

		// Check to see if $field is the parent of any item in $fields.
		// A field "parent" should be accepted if "parent.child" is accepted.
		if ( strpos( $accepted_field, "$field." ) === 0 ) {
			return true;
		}

		// Conversely, if "parent" is accepted, all "parent.child" fields should
		// also be accepted.
		if ( strpos( $field, "$accepted_field." ) === 0 ) {
			return true;
		}

	}

	return false;
}
