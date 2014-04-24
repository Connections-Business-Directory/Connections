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

	private static $filterRegistry = array();

	/**
	 * Register required actions/filters.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @return void
	 */
	public static function init() {

		// Register the core shortcode with the WordPres Shortcode API.
		add_action( 'init', array( __CLASS__, 'register') );

		// add_filter( 'the_posts', array( __CLASS__, 'parse' ), 10, 2 );
		// remove_filter( 'the_content', 'wpautop' );
	}

	/**
	 * Register the core shortcodes.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @uses   add_shortcode()
	 *
	 * @return void
	 */
	public static function register() {

		// Register the core shortcodes.
		add_shortcode( 'connections', array( __CLASS__, 'view' ) );
		add_shortcode( 'upcoming_list', '_upcoming_list' );
		add_shortcode( 'connections_vcard', '_connections_vcard' ); /* Experimental. Do NOT use. */
		add_shortcode( 'connections_qtip', '_connections_qtip' ); /* Experimental. Do NOT use. */
	}

	/**
	 * Experimental code to parse and process the shortcode very early in the
	 * WordPress execution stack. This allows the modification of its attributes
	 * before being processed by the WordPress Shortcode API.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @param  array  $posts
	 * @param  object $WP_Query
	 *
	 * @return array
	 */
	public static function parse( $posts, $WP_Query ) {

		$pattern = get_shortcode_regex();

		// Grab the array containing all query vars registered by Connections.
		$registeredQueryVars = cnRewrite::queryVars( array() );

		foreach ( $posts as $post ) {

			// If we're in the main query, proceed!
			if ( isset( $WP_Query->queried_object_id ) && $WP_Query->queried_object_id == $post->ID ) {

				/*
				 * $matches[0] == An array of all shortcodes that were found with its options.
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
					// if ( isset( $atts['initial_results'] )
					// 	&& strtolower( $atts['initial_results'] ) == 'false'
					// 	&& ! (bool) array_intersect( $registeredQueryVars, array_keys( (array) $WP_Query->query_vars ) )
					// 	)
					// {



					// } else {

					// 	// Rewrite the $atts array to prep it to be imploded.
					// 	array_walk( $atts, create_function( '&$i,$k','$i=" $k=\"$i\"";' ) );

					// 	$replace = '[' . $shortcode . ' ' . implode( ' ', $atts ) . ']';
					// }

					// All returns/end of lines and tabs should be removed so wpautop() doesn't insert <p> and <br> tags in the form output.
					// $replace = str_replace( array( "\r\n", "\r", "\n", "\t" ), array( ' ', ' ', ' ', ' ' ), $replace );

					// Replace the shortcode in the post with a new one based on the changes to $atts.
					// $post->post_content = str_replace( $matches[0][ array_search( $shortcode, $matches[2] ) ], $replace, $post->post_content );
				}

			}

		}

		return $posts;
	}

	/**
	 * Display results based on qurey var `cn-view`.
	 *
	 * @access public
	 * @since  0.7.3
	 * @static
	 * @uses   get_query_var()
	 * @param  array $atts
	 * @param  string $content [optional]
	 *
	 * @return string
	 */
	public static function view( $atts, $content = '', $tag = 'connections' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*$getAllowPublic = $instance->options->getAllowPublic();
		var_dump($getAllowPublic);
		$getAllowPublicOverride = $instance->options->getAllowPublicOverride();
		var_dump($getAllowPublicOverride);
		$getAllowPrivateOverride = $instance->options->getAllowPrivateOverride();
		var_dump($getAllowPrivateOverride);*/

		/*
		 * Only show this message under the following condition:
		 * - ( The user is not logged in AND the 'Login Required' is checked ) AND ( neither of the shortcode visibility overrides are enabled ).
		 */
		if ( ( ! is_user_logged_in() && ! $instance->options->getAllowPublic() ) && ! ( $instance->options->getAllowPublicOverride() || $instance->options->getAllowPrivateOverride() ) ) {
			$message = $instance->settings->get( 'connections', 'connections_login', 'message' );

			// Format and texturize the message.
			$message = wptexturize( wpautop( $message ) );

			// Make any links and such clickable.
			$message = make_clickable( $message );

			// Apply the shortcodes.
			$message = do_shortcode( $message );

			return $message;
		}

		switch ( get_query_var('cn-view') ) {

			case 'submit':

				if ( has_action( 'cn_submit_entry_form' ) ) {

					ob_start();

					do_action( 'cn_submit_entry_form', $atts, $content, $tag );

					return ob_get_clean();

				} else {

					return '<p>' . __( 'Future home of front end submissions.', 'connections' ) . '</p>';
				}

				break;

			case 'landing':

				return '<p>' . __( 'Future home of the landing pages, such a list of categories.', 'connections' ) . '</p>';

				break;

			case 'search':

				if ( has_action( 'cn_submit_search_form' ) ) {

					ob_start();

					do_action( 'cn_submit_search_form', $atts, $content, $tag );

					return ob_get_clean();

				} else {

					return '<p>' . __( 'Future home of the search page.', 'connections' ) . '</p>';
				}

				break;

			case 'results':

				if ( has_action( 'cn_submit_search_results' ) ) {

					ob_start();

					do_action( 'cn_submit_search_results', $atts, $content, $tag );

					return ob_get_clean();

				} else {

					return '<p>' . __( 'Future home of the search results landing page.', 'connections' ) . '</p>';
				}

				break;

			// Show the standard result list.
			case 'list':

				return cnShortcode_Connections::shortcode( $atts, $content );

				break;

			// Show the "View All" result list using the "Names" template.
			case 'all':

				$atts['template'] = 'names';

				return cnShortcode_Connections::shortcode( $atts, $content );

				break;

			// Show the entry detail using a template based on the entry type.
			case 'detail':

				switch ( get_query_var('cn-process') ) {

					case 'edit':

						if ( has_action( 'cn_edit_entry_form' ) ) {

							/*
							 * The `cn_edit_entry_form` action should only be execusted if the user is
							 * logged in and they have the `connections_manage` capability and either the
							 * `connections_edit_entry` or `connections_edit_entry_moderated` capability.
							 */

							if ( is_user_logged_in() &&
								current_user_can( 'connections_manage' ) &&
								( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
								) {

								ob_start();

								do_action( 'cn_edit_entry_form', $atts, $content, $tag );

								return ob_get_clean();

							} else {

								return __( 'You are not authorized to edit entries. Please contact the admin if you received this message in error.', 'connections' );
							}

						}

						break;

					default:

						// Ensure an array is passed the the cnRetrieve::entries method.
						if ( ! is_array( $atts ) ) $atts = (array) $atts;

						$results = $instance->retrieve->entries( $atts );
						//var_dump($results);

						$atts['list_type'] = $instance->settings->get( 'connections', 'connections_display_single', 'template' ) ? $results[0]->entry_type : NULL;

						// Disable the output of the following because they do no make sense to display for a single entry.
						$atts['show_alphaindex']   = FALSE;
						$atts['repeat_alphaindex'] = FALSE;
						$atts['show_alphahead']    = FALSE;

						return cnShortcode_Connections::shortcode( $atts, $content );

						break;
				}

				break;

			// Show the standard result list.
			default:

				return cnShortcode_Connections::shortcode( $atts, $content );

				break;
		}
	}

	/**
	 * The core `connections` shortcode can be used multiple times on a page.
	 * Because of this we need to keep track of the filters that are added
	 * during execution of the shortcode so the filters can be cleared and
	 * not applied to the other instance of the shortcode. This basically limits
	 * the filters to per shortcode instance.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @param  string $tag The action of filter hook tag.
	 *
	 * @return void
	 */
	public static function addFilterRegistry( $tag ) {

		self::$filterRegistry[] = $tag;
	}

	/**
	 * Clear the action/filter registry.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @return void
	 */
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

	/**
	 * Remove end of line characters to support the themes that insist
	 * on running wpautop() AFTER the shortcode filter has been run on
	 * the_content.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @param  string $string The result of executing any of the core Connections shortcodes.
	 *
	 * @return string         The string with all EOL characters removed.
	 */
	public static function removeEOL( $string ) {

		if ( cnSettingsAPI::get( 'connections', 'compatibility', 'strip_rnt' ) ) {

			$search  = array( "\r\n", "\r", "\n", "\t", PHP_EOL );
			$replace = array( ' ' );
			$string  = str_replace( $search, $replace, $string );
		}

		return trim( $string );
	}

	/**
	 * Attemps to intelligently remove <p> and <br> tags added around
	 * the shortcodes by wpautop().
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $content The content captured by the cn_template shortcode.
	 *
	 * @return string
	 */
	public static function removePBR( $content ){

		$content = strtr( $content, array(
			'<p><!--'  => '<!--',
			'--></p>'  => '-->',
			'<p>['     => '[',
			']</p>'    => ']',
			'/]</p>'   => ']',
			']<br />'  => ']',
			'/]<br />' => ']'
			)
		);

		return $content;
	}
}

// Init the class.
cnShortcode::init();
