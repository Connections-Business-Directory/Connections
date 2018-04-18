<?php

/**
 * Methods to be used to query entries.
 *
 * @package     Connections
 * @subpackage  Query Class
 * @copyright   Copyright (c) 2016, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.14
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnQuery
 */
class cnQuery {

	/**
	 * Retrieve variable in the WP_Query class.
	 *
	 * Wrapper method for the WordPress core `get_query_var()` function. This is a work around for theme's and plugins
	 * which break the WordPress global $wp_query var by unsetting it or overwriting it which break the method call
	 * that `get_query_var()` uses to return the query variable.
	 *
	 * @access public
	 * @since  8.5.14
	 *
	 * @global WP_Query $wp_query Global WP_Query instance.
	 *
	 * @param string $var     The variable key to retrieve.
	 * @param mixed  $default Optional. Value to return if the query variable is not set. Default empty.
	 *
	 * @return mixed Contents of the query variable.
	 */
	public static function getVar( $var, $default = '' ) {

		global $wp_query;

		// Check to see if the global `$wp_query` var is an instance of WP_Query and that the get() method is callable.
		// If it is then when can simply use the get_query_var() function.
		if ( $wp_query instanceof WP_Query && is_callable( array( $wp_query, 'get' ) ) ) {

			return get_query_var( $var, $default );

		// If a theme or plugin broke the global `$wp_query` var, check to see if the $var was parsed and saved in $GLOBALS['wp_query']->query_vars.
		} elseif ( isset( $GLOBALS['wp_query']->query_vars[ $var ] ) ) {

			return $GLOBALS['wp_query']->query_vars[ $var ];

		// We should not reach this, but if we do, lets check the original parsed query vars in $GLOBALS['wp_the_query']->query_vars.
		} elseif ( isset( $GLOBALS['wp_the_query']->query_vars[ $var ] ) ) {

			return $GLOBALS['wp_the_query']->query_vars[ $var ];

		// Ok, if all else fails, check the $_REQUEST super global.
		} elseif ( isset( $_REQUEST[ $var ] ) ) {

			return $_REQUEST[ $var ];
		}

		// Finally, return the $default if it was supplied.
		return $default;
	}

	/**
	 * Set query variable in the WP_Query class.
	 *
	 * Wrapper method for the WordPress core `set_query_var()` function. This is a work around for theme's and plugins
	 * which break the WordPress global $wp_query var by unsetting it or overwriting it which break the method call
	 * that `set_query_var()` uses to set the query variable.
	 *
	 * @access public
	 * @since  8.5.14
	 *
	 * @global WP_Query $wp_query Global WP_Query instance.
	 *
	 * @param string $var   Query variable key.
	 * @param mixed  $value Query variable value.
	 */
	public static function setVar( $var, $value ) {

		global $wp_query;

		if ( $wp_query instanceof WP_Query && is_callable( array( $wp_query, 'get' ) ) ) {

			set_query_var( $var, $value );

		} else {

			$GLOBALS['wp_query']->query_vars[ $var ] = $value;
			$GLOBALS['wp_the_query']->query_vars[ $var ] = $value;
		}
	}

	/**
	 * @access public
	 * @since  8.5.15
	 *
	 * @global wpdb $wpdb
	 *
	 * @param array  $atts
	 *
	 * @return string
	 */
	public static function where( $atts = array() ) {

		global $wpdb;

		$defaults = array(
			'table'     => CN_ENTRY_TABLE,
			'field'     => '',
			'value'     => '',
			'format'    => '%s',
			'relation'  => 'AND',
			'delimiter' => '|',
		);

		$atts = cnSanitize::args( $atts, $defaults );

		$where = '';

		$atts['relation'] = in_array( $atts['relation'], array( 'AND', 'OR' ) ) ? strtoupper( $atts['relation'] ) : 'AND';

		if ( ( is_string( $atts['value'] ) && 0 < strlen( $atts['field'] ) )
		     || ( is_array( $atts['value'] ) && 0 < count( $atts['value'] ) ) ) {

			$value = cnFunction::parseStringList( $atts['value'], $atts['delimiter'] );
			$count = count( $value );

			if ( 1 < $count ) {

				$where = $atts['relation'] . ' ' . $atts['table'] . '.' . $atts['field'] . ' ' . self::in( $value, $atts['format'] );

			} elseif ( 1 == $count ) {

				$where = $wpdb->prepare( $atts['relation'] . ' ' . $atts['table'] . '.' . $atts['field'] . ' = ' . $atts['format'], $value );
			}
		}

		return $where;
	}

	/**
	 * @access public
	 * @since  8.5.15
	 *
	 * @global wpdb $wpdb
	 *
	 * @param array $values
	 * @param array $format
	 * @param bool  $in
	 *
	 * @return string|void
	 */
	public static function in( $values, $format, $in = TRUE ) {

		global $wpdb;

		$relation = $in ? 'IN' : 'NOT IN';

		return $wpdb->prepare( $relation . ' ( ' . implode( ', ', array_fill( 0, count( $values ), $format ) ) . ' )', $values );
	}
}
