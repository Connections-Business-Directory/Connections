<?php
/**
 * REST API Category Controller.
 *
 * CREDIT: Heavily based on @see WP_REST_Term_Meta_Fields.
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
 * Manage meta values for terms via the REST API.
 *
 * @since 8.5.34
 *
 * @see CN_REST_Meta_Fields
 */
class CN_REST_Term_Meta_Fields extends CN_REST_Meta_Fields {

	/**
	 * Taxonomy to register fields for.
	 *
	 * @access protected
	 * @since  8.5.34
	 * @var    string
	 */
	protected $taxonomy;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since  8.5.34
	 *
	 * @param string $taxonomy Taxonomy to register fields for.
	 */
	public function __construct( $taxonomy ) {
		$this->taxonomy = $taxonomy;
	}

	/**
	 * Retrieves the object meta type.
	 *
	 * @access protected
	 * @since  8.5.34
	 *
	 * @return string The meta type.
	 */
	protected function get_meta_type() {

		return 'term';
	}

	/**
	 * Retrieves the type for register_rest_field().
	 *
	 * @access public
	 * @since  8.5.34
	 *
	 * @return string The REST field type.
	 */
	public function get_rest_field_type() {

		return $this->taxonomy;
	}
}
