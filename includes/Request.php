<?php
/**
 * Parse the current request for Connections-related query variables.
 *
 * @since      10.3
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Request
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory;

use cnRewrite;
use Connections_Directory\Request\Entry_Initial_Character;
use Connections_Directory\Request\Entry_Search_Term;
use Connections_Directory\Taxonomy\Registry;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_sanitize;
use WP;

/**
 * Class Request
 *
 * @package Connections_Directory
 */
final class Request {

	/**
	 * Instance of the class.
	 *
	 * @since 10.3
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Whether the current request has a Connections-related query.
	 *
	 * @since 10.4.39
	 *
	 * @var bool
	 */
	private $hasQuery;

	/**
	 * Whether the current query is for a single entry.
	 *
	 * @since 10.4.59
	 *
	 * @var bool
	 */
	private $isSingle = false;

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
	protected function __construct() {
		/* Do nothing here */
	}

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
	public static function get(): Request {

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
	 * @param WP $wp Instance of the WP object.
	 */
	public static function parse( WP $wp ) {

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

		foreach ( $self->queryVars as $key => &$value ) {

			switch ( $key ) {

				case 'cn-cat':
					$value = ! empty( $value ) ? _sanitize::integer( $value ) : '';
					break;

				case 'cn-cat-in':
					$value = array_filter( wp_parse_id_list( $value ) );
					break;

				case 'cn-cat-slug':
					$value = ! empty( $value ) ? wp_basename( $value ) : '';
					break;

				case 'cn-country':
				case 'cn-postal-code':
				case 'cn-region':
				case 'cn-locality':
				case 'cn-county':
				case 'cn-district':
				case 'cn-organization':
				case 'cn-department':
					$value = ! empty( $value ) ? urldecode( $value ) : '';
					break;

				case 'cn-char':
					$value = Entry_Initial_Character::input()->value();
					break;

				case 'cn-s':
					$value = Entry_Search_Term::input()->value();
					break;
			}
		}

		$self->hasQuery = ! empty( array_filter( $self->queryVars, array( 'Connections_Directory\Utility\_', 'notEmpty' ) ) );
		$self->isSingle = array_key_exists( 'cn-entry-slug', $self->queryVars ) && 0 < strlen( $queryVars['cn-entry-slug'] );
	}

	/**
	 * Get the request query variables.
	 *
	 * @since 10.3
	 *
	 * @return array
	 */
	public function getQueryVars(): array {

		return $this->queryVars;
	}

	/**
	 * Retrieves the value of a query variable.
	 *
	 * @since 10.3
	 *
	 * @param string $key          Query variable key.
	 * @param mixed  $defaultValue Optional. Value to return if the query variable is not set. Default empty string.
	 *
	 * @return mixed Contents of the query variable.
	 */
	public function getVar( string $key, $defaultValue = '' ) {

		return _array::get( $this->queryVars, $key, $defaultValue );
	}

	/**
	 * Removes a query variable from the parse query variables.
	 *
	 * @since 10.3
	 *
	 * @param string $key Query variable name.
	 */
	public function removeVar( string $key ) {

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
	public function setVar( string $key, $value ) {

		_array::set( $this->queryVars, $key, $value );
	}

	/**
	 * Whether the current request has a query.
	 *
	 * @since 10.4.39
	 *
	 * @return bool
	 */
	public function hasQuery(): bool {

		return $this->hasQuery;
	}

	/**
	 * Returns true when the request is AJAX.
	 *
	 * @since 10.3
	 *
	 * @return bool
	 */
	public function isAjax(): bool {

		return wp_doing_ajax();
	}

	/**
	 * Returns true when processing a WP-CLI request.
	 *
	 * @since 10.4.61
	 *
	 * @return bool
	 */
	public function isCLI(): bool {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Returns true if the request is REST API request.
	 *
	 * @link https://wordpress.stackexchange.com/a/317041/59053
	 * @since 10.3
	 *
	 * @return bool
	 */
	public function isRest(): bool {

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		$is_rest_api_request = ( false !== strpos( $request_uri, $rest_prefix ) );

		return apply_filters( 'Connections_Directory/Request/isREST', $is_rest_api_request );
	}

	/**
	 * Whether search query is requested.
	 *
	 * @since 10.4.39
	 *
	 * @return bool
	 */
	public function isSearch(): bool {

		$query = Entry_Search_Term::input()->value();

		return is_string( $query ) && '' !== $query;
	}

	/**
	 * Whether the current request is for a single entry.
	 *
	 * @since 10.4.59
	 *
	 * @return bool
	 */
	public function isSingle(): bool {

		return $this->isSingle;
	}
}
