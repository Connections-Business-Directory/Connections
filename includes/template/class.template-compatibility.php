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

	public static function init() {

		add_filter( 'cn_template_required_js-cmap', array( __CLASS__, 'enqueueChosen' ), 99 );
		add_filter( 'cn_template_required_js-cmap', array( __CLASS__, 'enqueuegoMap' ), 99 );

		add_filter( 'cn_template_required_js-excerpt-plus', array( __CLASS__, 'enqueueChosen' ), 99 );

		add_filter( 'cn_template_required_js-market', array( __CLASS__, 'enqueuegoMap' ), 99 );

		add_filter( 'cn_template_required_js-slim-plus', array( __CLASS__, 'enqueueChosen' ), 99 );

		add_filter( 'cn_template_required_js-tile-plus', array( __CLASS__, 'enqueueChosen' ), 99 );
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
}

cnTemplate_Compatibility::init();
