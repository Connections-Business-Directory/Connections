<?php

namespace Connections_Directory;

use cnRewrite;
use Connections_Directory\Taxonomy\Registry;
use Connections_Directory\Utility\_array;
use WP;

/**
 * Class Request
 *
 * @package Connections_Directory
 */
final class Request {

	/**
	 * @since 10.3
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * An associative array where the key is the registered query variable and the value is the parse request value.
	 *
	 * This array will contain only Connections related query variables.
	 *
	 * @since 10.3
	 *
	 * @var array
	 */
	private $queryVars = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 10.3
	 */
	protected function __construct() { /* Do nothing here */ }

	/**
	 * Initialize.
	 *
	 * @since 10.3
	 */
	protected static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self();
		}
	}

	/**
	 * Get the Request instance.
	 *
	 * @since 10.3
	 *
	 * @return Request
	 */
	public static function get() {

		if ( ! self::$instance instanceof self ) {

			self::init();
		}

		return self::$instance;
	}

	/**
	 * Callback for the `parse_request` action.
	 *
	 * @internal
	 * @since 10.3
	 *
	 * @var WP $wp
	 */
	public static function parse( $wp ) {

		$self      = self::get();
		$queryVars = $wp->query_vars;

		$self->queryVars = array_intersect_key( $queryVars, array_flip( cnRewrite::getRegisteredQueryVars() ) );

		$taxonomies = Registry::get()->getTaxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			if ( $taxonomy->getQueryVar() && isset( $queryVars[ $taxonomy->getQueryVar() ] ) ) {

				$self->queryVars[ $taxonomy->getQueryVar() ] = str_replace( ' ', '+', $queryVars[ $taxonomy->getQueryVar() ] );
			}
		}

		// Do not allow non-publicly queryable taxonomies to be queried from the frontend.
		if ( ! is_admin() ) {

			foreach ( $taxonomies as $taxonomy ) {
				/*
				 * Disallow when set to the 'taxonomy' query var.
				 * Non-publicly queryable taxonomies cannot register custom query vars.
				 */
				if ( ! $taxonomy->isPublicQueryable() && isset( $self->queryVars[ $taxonomy->getQueryVar() ] ) ) {

					unset( $self->queryVars[ $taxonomy->getQueryVar() ] );
				}
			}
		}

	}

	/**
	 * Get the request query variables.
	 *
	 * @since 10.3
	 *
	 * @return array
	 */
	public function getQueryVars() {

		return $this->queryVars;
	}

	/**
	 * Retrieves the value of a query variable.
	 *
	 * @since 10.3
	 *
	 * @param string $key     Query variable key.
	 * @param mixed  $default Optional. Value to return if the query variable is not set. Default empty string.
	 *
	 * @return mixed Contents of the query variable.
	 */
	public function getVar( $key, $default = '' ) {

		return _array::get( $this->queryVars, $key, $default );
	}

	/**
	 * Removes a query variable from the parse query variables.
	 *
	 * @since 10.3
	 *
	 * @param string $key Query variable name.
	 */
	public function removeVar( $key ) {

		_array::forget( $this->queryVars, $key );
	}

	/**
	 * Sets the value of a query variable.
	 *
	 * @since 10.3
	 *
	 * @param string $key   Query variable name.
	 * @param mixed  $value Query variable value.
	 */
	public function setVar( $key, $value ) {

		_array::set( $this->queryVars, $key, $value );
	}

	/**
	 * Returns true when the request is AJAX.
	 *
	 * @since 10.3
	 *
	 * @return bool
	 */
	public function isAjax() {

		return wp_doing_ajax();
	}

	/**
	 * Returns true if the request is REST API request.
	 *
	 * @since 10.3
	 *
	 * @return bool
	 */
	public function isRest() {

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );

		return apply_filters( 'Connections_Directory/Request/isREST', $is_rest_api_request );
	}
}
