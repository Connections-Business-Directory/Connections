<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add various actions and filters to ensure proper backward compatibilty
 * with the commercial templates.
 *
 * @package     Connections
 * @subpackage  Template Compatibility
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8.2
 */

class cnTemplate_Compatibility {

	public static function hooks() {

		add_filter( 'cn_template_required_js-cmap', array( __CLASS__, 'enqueueChosen' ), 99 );
		add_filter( 'cn_template_required_js-cmap', array( __CLASS__, 'enqueuegoMap' ), 99 );

		add_filter( 'cn_template_required_js-excerpt-plus', array( __CLASS__, 'enqueueChosen' ), 99 );

		add_filter( 'cn_template_required_js-market', array( __CLASS__, 'enqueuegoMap' ), 99 );

		add_filter( 'cn_template_required_js-slim-plus', array( __CLASS__, 'enqueueChosen' ), 99 );

		add_filter( 'cn_template_required_js-tile-plus', array( __CLASS__, 'enqueueChosen' ), 99 );

		add_filter( 'cn_get_template', array( __CLASS__, 'deprecatedTemplates' ) );
	}

	public static function enqueueChosen( $required ) {

		if ( ! in_array( 'jquery-chosen', $required ) ) $required[] = 'jquery-chosen';

		if ( $key = array_search( 'jquery-chosen-min', $required ) ) unset( $required[ $key ] );

		return $required;
	}

	public static function enqueuegoMap( $required ) {

		if ( ! in_array( 'jquery-gomap', $required ) ) $required[] = 'jquery-gomap';

		if ( $key = array_search( 'jquery-gomap-min', $required ) ) unset( $required[ $key ] );

		return $required;
	}

	/**
	 * Callback for the `cn_get_template` filter which will substitute the default template when a removed template is requested.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public static function deprecatedTemplates( $slug ) {

		$deprecated = array(
			'card-bio',
			'card-single',
			'card-tableformat',
		);

		if ( in_array( $slug, $deprecated ) ) {

			$slug = 'card';
		}

		return $slug;
	}
}
