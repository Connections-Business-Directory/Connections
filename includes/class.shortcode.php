<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Common static methods that can be used across all core shortcodes.
 *
 * @package     Connections
 * @subpackage  Shortcode API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

class cnShortcode {

	private static $shortcode = array();

	private static $filterRegistry = array();

	public static function init() {

		// add_filter( 'the_posts', array( __CLASS__, 'parse' ), 10, 2 );

		add_action( 'init', array( __CLASS__, 'register') );
	}

	public static function register() {

		// Register the core shortcodes.
		add_shortcode( 'connections', 'connectionsView' );
		add_shortcode( 'cn_new', array( 'cnShortcode_Connections', 'shortcode' ) ); /* The new [connections] shortcode. */
		add_shortcode( 'connections_list', 'connectionsView' ); /* @deprecated since version 0.7.0.4 */
		add_shortcode( 'upcoming_list', '_upcoming_list' );
		add_shortcode( 'connections_vcard', '_connections_vcard' ); /* Experimental. Do NOT use. */
		add_shortcode( 'connections_qtip', '_connections_qtip' ); /* Experimental. Do NOT use. */
	}

	public static function parse( $posts, $WP_Query ) {

		$pattern   = get_shortcode_regex();

		// Grab the array containing all query vars registered by Connections.
		$registeredQueryVars = cnRewrite::queryVars( array() );

		foreach ( $posts as $post ) {

			// If we're in the main query, proceed!
			if ( isset( $WP_Query->queried_object_id ) && $WP_Query->queried_object_id == $post->ID ) {

				/*
				 * $matches[0] == An array of all shortcode that were found with its options.
				 * $matches[1] == Unknown.
				 * $matches[2] == An array of all shortcode tags that were found.
				 * $matches[3] == An array of the shortcode options that were found.
				 * $matches[4] == Unknown.
				 * $matches[5] == Unknown.
				 * $matches[6] == Unknown.
				 */

				if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) && array_key_exists( 2, $matches ) ) {

					// Build the results in a more usable format.
					foreach ( $matches[2] as $key => $shortcode ) {

						// Parse the shortcode atts.
						self::$shortcode[ $shortcode ] = shortcode_parse_atts( $matches[3][ $key ] );
					}


					// Show the just the search form w/o showing the intial results?
					// If a Connections query var is set, show the results instead.
					if ( isset( $atts['initial_results'] )
						&& strtolower( $atts['initial_results'] ) == 'false'
						&& ! (bool) array_intersect( $registeredQueryVars, array_keys( (array) $WP_Query->query_vars ) )
					   )
					{



					} else {

						// Rewrite the $atts array to prep it to be imploded.
						array_walk( $atts, create_function( '&$i,$k','$i=" $k=\"$i\"";' ) );

						$replace = '[' . $shortcode . ' ' . implode( ' ', $atts ) . ']';
					}

					// All returns/end of lines and tabs should be removed so wpautop() doesn't insert <p> and <br> tags in the form output.
					$replace = str_replace( array( "\r\n", "\r", "\n", "\t" ), array( ' ', ' ', ' ', ' ' ), $replace );

					// Replace the shortcode in the post with something a new one based on you changes to $atts.
					$post->post_content = str_replace( $matches[0][ array_search( $shortcode, $matches[2] ) ], $replace, $post->post_content );
				}

			}

		}

		return $posts;
	}

	public static function exists( $tag ) {

		return array_search( $tag, self::$shortcode ) !== FALSE ? TRUE : FALSE;
	}

	public static function loadTemplate( $atts ) {

		/*
		 * Maybe this should be moved to the cnTemplateFactory class.
		 */

		$atts = apply_filters( 'cn_list_template_init', $atts );

		$defaults = array(
			'list_type'     => NULL,
			'template'      => NULL,
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( ! empty( $atts['list_type'] ) ) {

			$permittedTypes = array( 'individual', 'organization', 'family', 'connection_group');

			// Convert to array. Trim the space characters if present.
			$atts['list_type'] = explode( ',' , str_replace( ' ', '', $atts['list_type'] ) );

			// Set the template type to the first in the entry type from the supplied if multiple list types are provided.
			if ( in_array( $atts['list_type'][0], $permittedTypes ) ) {

				$type = $atts['list_type'][0];

				// Change the list type to family from connection_group to maintain compatibility with versions 0.7.0.4 and earlier.
				if ( $type == 'connection_group' ) $type = 'family';
			}

		} else {

			// If no list type was specified, set the default ALL template.
			$type = 'all';
		}

		/*
		 * If a list type was specified in the shortcode, load the template based on that type.
		 * However, if a specific template was specifed, that should preempt the template to be loaded based on the list type if it was specified..
		 */
		if ( ! empty( $atts['template'] ) ) {

			$template = cnTemplateFactory::getTemplate( $atts['template'] );

		} else {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			$slug     = $instance->options->getActiveTemplate( $type );
			$template = cnTemplateFactory::getTemplate( $slug );
		}

		do_action( 'cn_register_legacy_template_parts' );
		do_action( 'cn_action_include_once-' . $template->getSlug() );
		do_action( 'cn_action_js-' . $template->getSlug() );

		return $template;
	}

	public static function loadTemplateError( $atts ) {

		/*
		 * Maybe this should be moved to the cnTemplateFactory class.
		 */

		$defaults = array(
			'template'      => NULL,
		);

		$atts = shortcode_atts( $defaults, $atts );

		return '<p style="color:red; font-weight:bold; text-align:center;">' . sprintf( __( 'ERROR: Template %1$s not found.', 'connections' ), $atts['template'] ) . '</p>';
	}

	public static function parts() {

		$part = array(
			// 'attr'                => array( 'desc' => __( 'Attributes at time of running', 'connections' ) ),
			'header'               => array(
				'desc'     => __( 'Header', 'connections' ),
				'callback' => array( __CLASS__, 'header' ),
				),
			'content'              => array(
				'desc'     => __( 'Content', 'connections' ),
				'callback' => array( __CLASS__, 'content' ),
				),
			'footer'               => array(
				'desc'     => __( 'Footer', 'connections' ),
				'callback' => array( __CLASS__, 'footer' ),
				),
			'version'              => array(
				'desc'     => __( 'Version', 'connections' ),
				'callback' => array( __CLASS__, 'version' ),
				),
			'db_version'           => array(
				'desc'     => __( 'Database version.', 'connections' ),
				'callback' => array( __CLASS__, 'dbVersion' ),
				),
			'return_to_top'        => array(
				'desc'     => __( 'Return to top.', 'connections' ),
				'callback' => array( __CLASS__, 'returnToTop' ),
				),
			'template_slug'        => array(
				'desc'     => __( 'Template slug.', 'connections' ),
				'callback' => array( __CLASS__, 'templateSlug' ),
				),
			'template_version'     => array(
				'desc'     => __( 'Template version.', 'connections' ) ,
				'callback' => array( __CLASS__, 'templateVersion' ),
				),
			'entry_end'            => array(
				'desc'     => __( 'End of entry', 'connections' ),
				'callback' => array( __CLASS__, 'entryEnd' ),
				),
			'entry_start'          => array(
				'desc'     => __( 'Start of entry', 'connections' ),
				'callback' => array( __CLASS__, 'entryStart' ),
				),
			'entry_before'         => array(
				'desc'     => __( 'Before entry', 'connections' ),
				'callback' => array( __CLASS__, 'entryBefore' ),
				),
			'entry_after'          => array(
				'desc'     => __( 'After entry', 'connections' ),
				'callback' => array( __CLASS__, 'entryAfter' ),
				),
			'card'                 => array(
				'desc'     => __( 'Card', 'connections' ),
				'callback' => array( __CLASS__, 'card' ),
				),
			'cards'                => array(
				'desc'     => __( 'Cards', 'connections' ),
				'callback' => array( __CLASS__, 'cards' ),
				),
			'alternate'            => array(
				'desc'     => __( 'Row alternate class.', 'connections' ),
				'callback' => array( __CLASS__, 'alternate' ),
				),
			'entry_type'           => array(
				'desc'     => __( 'Enrty type.', 'connections' ),
				'callback' => array( __CLASS__, 'entryType' ),
				),
			'entry_slug'           => array(
				'desc'     => __( 'Entry slug.', 'connections' ),
				'callback' => array( __CLASS__, 'entrySlug' ),
				),
			'entry_category_class' => array(
				'desc'     => __( 'Entry category class.', 'connections' ),
				'callback' => array( __CLASS__, 'entryCategoryClass' ),
				),
			'character_index'      => array(
				'desc'     => __( 'Character index.', 'connections' ),
				'callback' => array( __CLASS__, 'characterIndex' ),
				),
		);

		return $part;
	}

	public static function addFilterRegistry( $tag ) {

		self::$filterRegistry[] = $tag;
	}

	public static function clearFilterRegistry() {
		global $wp_filter;

		/*
		 * Remove any filters a template may have added
		 * so it is not run again if more than one template
		 * is in use on the same page.
		 */
		foreach ( self::$filterRegistry as $filter ) {

			if ( isset( $wp_filter[ $filter ] ) ) unset( $wp_filter[ $filter ] );
		}
	}

	public static function removeEOL( $string ) {

		if ( cnSettingsAPI::get( 'connections', 'compatibility', 'strip_rnt' ) ) {

			$search  = array( "\r\n", "\r", "\n", "\t", PHP_EOL );
			$replace = array( ' ' );
			$string  = str_replace( $search, $replace, $string );
		}

		return $string;
	}
}

// Init the class.
cnShortcode::init();
