<?php
/**
 * REST API Category Controller.
 *
 * CREDIT: Heavily based on @see WP_REST_Meta_Fields.
 *
 * Handles requests to the category endpoint.
 *
 * @author   Steven A. Zahm
 * @category API
 * @package  Connections
 * @subpackage REST API
 * @since    8.5.34
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage meta values for an object via the REST API.
 *
 * @since 8.5.34
 */
abstract class CN_REST_Meta_Fields extends WP_REST_Meta_Fields {

	/**
	 * Registers the meta field.
	 *
	 * @access public
	 * @since  8.5.34
	 *
	 * @see register_rest_field()
	 */
	public function register_field() {

		register_rest_field(
			$this->get_rest_field_type(),
			'meta',
			array(
				'get_callback'    => array( $this, 'get_value' ),
				'update_callback' => array( $this, 'update_value' ),
				'schema'          => $this->get_field_schema(),
			)
		);
	}

	/**
	 * Retrieves the meta field value.
	 *
	 * @access public
	 * @since  8.5.34
	 *
	 * @param int             $object_id Object ID to fetch meta for.
	 * @param WP_REST_Request $request   Full details about the request.
	 *
	 * @return array Object containing the meta values by name, otherwise WP_Error object.
	 */
	public function get_value( $object_id, $request ) {

		$fields   = $this->get_registered_fields();
		$response = array();

		foreach ( $fields as $meta_key => $args ) {

			$name       = $args['name'];
			$all_values = cnMeta::get( $this->get_meta_type(), $object_id, $meta_key, false );

			if ( $args['single'] ) {

				if ( empty( $all_values ) ) {
					$value = $args['schema']['default'];
				} else {
					$value = $all_values[0];
				}

				$value = $this->prepare_value_for_response( $value, $request, $args );

			} else {

				$value = array();

				foreach ( $all_values as $row ) {

					$value[] = $this->prepare_value_for_response( $row, $request, $args );
				}
			}

			$response[ $name ] = $value;
		}

		return $response;
	}

	/**
	 * Updates meta values.
	 *
	 * @access public
	 * @since  8.5.34
	 *
	 * @param array $meta      Array of meta parsed from the request.
	 * @param int   $object_id Object ID to fetch meta for.
	 *
	 * @return WP_Error|null WP_Error if one occurs, null on success.
	 */
	public function update_value( $meta, $object_id ) {
		$fields = $this->get_registered_fields();
		foreach ( $fields as $meta_key => $args ) {
			$name = $args['name'];
			if ( ! array_key_exists( $name, $meta ) ) {
				continue;
			}

			/*
			 * A null value means reset the field, which is essentially deleting it
			 * from the database and then relying on the default value.
			 */
			if ( is_null( $meta[ $name ] ) ) {
				$result = $this->delete_meta_value( $object_id, $meta_key, $name );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
				continue;
			}

			$is_valid = rest_validate_value_from_schema( $meta[ $name ], $args['schema'], 'meta.' . $name );
			if ( is_wp_error( $is_valid ) ) {
				$is_valid->add_data( array( 'status' => 400 ) );
				return $is_valid;
			}

			$value = rest_sanitize_value_from_schema( $meta[ $name ], $args['schema'] );

			if ( $args['single'] ) {
				$result = $this->update_meta_value( $object_id, $meta_key, $name, $value );
			} else {
				$result = $this->update_multi_meta_value( $object_id, $meta_key, $name, $value );
			}

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Deletes a meta value for an object.
	 *
	 * @access protected
	 * @since  8.5.34
	 *
	 * @param int    $object_id Object ID the field belongs to.
	 * @param string $meta_key  Key for the field.
	 * @param string $name      Name for the field that is exposed in the REST API.
	 * @return bool|WP_Error True if meta field is deleted, WP_Error otherwise.
	 */
	protected function delete_meta_value( $object_id, $meta_key, $name ) {

		$meta_type = $this->get_meta_type();

		if ( ! current_user_can( "delete_{$meta_type}_meta", $object_id, $meta_key ) ) {

			return new WP_Error(
				'rest_cannot_delete',
				/* translators: %s: custom field key */
				sprintf( __( 'Sorry, you are not allowed to edit the %s custom field.', 'connections' ), $name ),
				array( 'key' => $name, 'status' => rest_authorization_required_code() )
			);
		}

		if ( ! cnMeta::delete( $meta_type, $object_id, wp_slash( $meta_key ) ) ) {

			return new WP_Error(
				'rest_meta_database_error',
				__( 'Could not delete meta value from database.', 'connections' ),
				array( 'key' => $name, 'status' => WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		return true;
	}

	/**
	 * Updates multiple meta values for an object.
	 *
	 * Alters the list of values in the database to match the list of provided values.
	 *
	 * @access protected
	 * @since  8.5.34
	 *
	 * @param int    $object_id Object ID to update.
	 * @param string $meta_key  Key for the custom field.
	 * @param string $name      Name for the field that is exposed in the REST API.
	 * @param array  $values    List of values to update to.
	 * @return bool|WP_Error True if meta fields are updated, WP_Error otherwise.
	 */
	protected function update_multi_meta_value( $object_id, $meta_key, $name, $values ) {
		$meta_type = $this->get_meta_type();
		if ( ! current_user_can( "edit_{$meta_type}_meta", $object_id, $meta_key ) ) {
			return new WP_Error(
				'rest_cannot_update',
				/* translators: %s: custom field key */
				sprintf( __( 'Sorry, you are not allowed to edit the %s custom field.', 'connections' ), $name ),
				array( 'key' => $name, 'status' => rest_authorization_required_code() )
			);
		}

		$current = get_metadata( $meta_type, $object_id, $meta_key, false );

		$to_remove = $current;
		$to_add    = $values;

		foreach ( $to_add as $add_key => $value ) {
			$remove_keys = array_keys( $to_remove, $value, true );

			if ( empty( $remove_keys ) ) {
				continue;
			}

			if ( count( $remove_keys ) > 1 ) {
				// To remove, we need to remove first, then add, so don't touch.
				continue;
			}

			$remove_key = $remove_keys[0];

			unset( $to_remove[ $remove_key ] );
			unset( $to_add[ $add_key ] );
		}

		// `delete_metadata` removes _all_ instances of the value, so only call once.
		$to_remove = array_unique( $to_remove );

		foreach ( $to_remove as $value ) {
			if ( ! delete_metadata( $meta_type, $object_id, wp_slash( $meta_key ), wp_slash( $value ) ) ) {
				return new WP_Error(
					'rest_meta_database_error',
					__( 'Could not update meta value in database.', 'connections' ),
					array( 'key' => $name, 'status' => WP_Http::INTERNAL_SERVER_ERROR )
				);
			}
		}

		foreach ( $to_add as $value ) {
			if ( ! add_metadata( $meta_type, $object_id, wp_slash( $meta_key ), wp_slash( $value ) ) ) {
				return new WP_Error(
					'rest_meta_database_error',
					__( 'Could not update meta value in database.', 'connections' ),
					array( 'key' => $name, 'status' => WP_Http::INTERNAL_SERVER_ERROR )
				);
			}
		}

		return true;
	}

	/**
	 * Updates a meta value for an object.
	 *
	 * @access protected
	 * @since  8.5.34
	 *
	 * @param int    $object_id Object ID to update.
	 * @param string $meta_key  Key for the custom field.
	 * @param string $name      Name for the field that is exposed in the REST API.
	 * @param mixed  $value     Updated value.
	 * @return bool|WP_Error True if the meta field was updated, WP_Error otherwise.
	 */
	protected function update_meta_value( $object_id, $meta_key, $name, $value ) {
		$meta_type = $this->get_meta_type();
		if ( ! current_user_can( "edit_{$meta_type}_meta", $object_id, $meta_key ) ) {
			return new WP_Error(
				'rest_cannot_update',
				/* translators: %s: custom field key */
				sprintf( __( 'Sorry, you are not allowed to edit the %s custom field.', 'connections' ), $name ),
				array( 'key' => $name, 'status' => rest_authorization_required_code() )
			);
		}

		$meta_key   = wp_slash( $meta_key );
		$meta_value = wp_slash( $value );

		// Do the exact same check for a duplicate value as in update_metadata() to avoid update_metadata() returning false.
		$old_value = get_metadata( $meta_type, $object_id, $meta_key );

		if ( 1 === count( $old_value ) ) {
			if ( $old_value[0] === $meta_value ) {
				return true;
			}
		}

		if ( ! update_metadata( $meta_type, $object_id, $meta_key, $meta_value ) ) {
			return new WP_Error(
				'rest_meta_database_error',
				__( 'Could not update meta value in database.', 'connections' ),
				array( 'key' => $name, 'status' => WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		return true;
	}

	/**
	 * Retrieves all the registered meta fields.
	 *
	 * @access protected
	 * @since  8.5.34
	 *
	 * @return array Registered fields.
	 */
	protected function get_registered_fields() {
		$registered = array();

		foreach ( get_registered_meta_keys( $this->get_meta_type() ) as $name => $args ) {

			if ( empty( $args['show_in_rest'] ) ) {

				continue;
			}

			$rest_args = array();

			if ( is_array( $args['show_in_rest'] ) ) {

				$rest_args = $args['show_in_rest'];
			}

			$default_args = array(
				'name'             => $name,
				'single'           => $args['single'],
				'type'             => ! empty( $args['type'] ) ? $args['type'] : null,
				'schema'           => array(),
				'prepare_callback' => array( $this, 'prepare_value' ),
			);

			$default_schema = array(
				'type'        => $default_args['type'],
				'description' => empty( $args['description'] ) ? '' : $args['description'],
				'default'     => isset( $args['default'] ) ? $args['default'] : null,
			);

			$rest_args           = array_merge( $default_args, $rest_args );
			$rest_args['schema'] = array_merge( $default_schema, $rest_args['schema'] );

			$type = ! empty( $rest_args['type'] ) ? $rest_args['type'] : null;
			$type = ! empty( $rest_args['schema']['type'] ) ? $rest_args['schema']['type'] : $type;

			if ( ! in_array( $type, array( 'string', 'boolean', 'integer', 'number' ) ) ) {
				continue;
			}

			if ( empty( $rest_args['single'] ) ) {
				$rest_args['schema']['items'] = array(
					'type' => $rest_args['type'],
				);
				$rest_args['schema']['type']  = 'array';
			}

			$registered[ $name ] = $rest_args;
		}

		return $registered;
	}

	/**
	 * Retrieves the object's meta schema, conforming to JSON Schema.
	 *
	 * @access protected
	 * @since  8.5.34
	 *
	 * @return array Field schema data.
	 */
	public function get_field_schema() {
		$fields = $this->get_registered_fields();

		$schema = array(
			'description' => __( 'Meta fields.', 'connections' ),
			'type'        => 'object',
			'context'     => array( 'view', 'edit' ),
			'properties'  => array(),
			'arg_options' => array(
				'sanitize_callback' => null,
				'validate_callback' => array( $this, 'check_meta_is_array' ),
			),
		);

		foreach ( $fields as $args ) {
			$schema['properties'][ $args['name'] ] = $args['schema'];
		}

		return $schema;
	}
}
