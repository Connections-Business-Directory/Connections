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

		// Run this early, before core WP filters.
		add_filter( 'the_content', array( __CLASS__, 'single' ), 7 );
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

		add_shortcode( 'cn_thumb', array( 'cnThumb', 'shortcode' ) );
		add_shortcode( 'cn_thumbr', array( 'cnThumb_Responsive', 'shortcode' ) );
	}

	/**
	 * Find the shortcode tag within the supplied string.
	 *
	 * @access public
	 * @since  8.4.5
	 * @static
	 *
	 * @param string $tag     The shortcode tag.
	 * @param string $content The string to find the shortcode tag in.
	 * @param string $return  What to return:
	 *                        Default: bool
	 *                        Accepts: atts, bool, matches
	 *
	 * @return array|bool     FALSE if shortcode tag is not found. Array of atts for each instance found or array of all
	 *                        matches found for the supplied shortcode tag.
	 */
	public static function find( $tag, $content, $return = 'bool' ) {

		global $shortcode_tags;

		// Exit early if the shortcode does not exist in content.
		if ( FALSE === strpos( $content, "[$tag" ) && ! isset( $shortcode_tags[ $tag ] ) ) {

			return FALSE;
		}

		// Backup the registered shortcode tags, so they can be restored after searching for the requested shortcode.
		$registeredTags = $shortcode_tags;

		// Set the registered shortcodes to only the shortcode being searched for because this effects the results
		// returned by get_shortcode_regex() as it sets up the pattern to search for all registered shortcodes.
		// Limiting it to only the shortcode being searched for greatly improves this methods accuracy.
		$shortcode_tags = array( $tag => $shortcode_tags[ $tag ] );

		$pattern = get_shortcode_regex();
		$found   = array();

		if ( preg_match_all( '/'. $pattern .'/s', $content, $matches, PREG_SET_ORDER ) ) {

			if ( empty( $matches ) ) {

				return FALSE;
			}

			foreach ( $matches as $shortcode ) {

				/*
				 * $shortcode[0] == If self-closing, The entire shortcode and options, including the opening and closing brackets.
				 *                  If enclosing, The entire shortcode and options, including the opening/closing brackets, content and closing shortcode.
				 * $shortcode[1] == Unknown.
				 * $shortcode[2] == The shortcode tag.
				 * $shortcode[3] == The shortcode options and their values as a string.
				 * $shortcode[4] == Unknown.
				 * $shortcode[5] == If self-closing, unknown.
				 *                  If enclosing, the opening shortcode and options, including the opening/closing brackets and the content.
				 * $shortcode[6] == Unknown.
				 */

				if ( $tag === $shortcode[2] ) {

					$found[] = $shortcode;

				} elseif ( ! empty( $shortcode[5] ) && has_shortcode( $shortcode[5], $tag ) ) {

					$found[] = $shortcode;
				}
			}
		}

		// Restore the registered shortcodes from the backup.
		$shortcode_tags = $registeredTags;

		switch ( $return ) {

			case 'atts':

				$atts = array();

				foreach ( $found as $shortcode ) {

					// Parse the shortcode atts.
					$atts[] = shortcode_parse_atts( $shortcode[3] );
				}

				return $atts;

			case 'bool':
				return ! empty( $found );

			case 'matches':
				return $found;

			default:
				return FALSE;
		}
	}

	/**
	 * Programmatically write a shortcode.
	 *
	 * @access public
	 * @since  8.4.5
	 * @static
	 *
	 * @param string $shortcode The shortcode tag.
	 * @param array  $atts      An associative array where the key is the option name and the value is the option value.
	 *
	 * @return string
	 */
	public static function write( $shortcode, $atts ) {

		// Rewrite the $atts array to prep it to be imploded.
		array_walk( $atts, create_function( '&$i,$k','$i="$k=\"$i\"";' ) );

		return '[' . $shortcode . ' ' . implode( ' ', $atts ) . ']';
	}

	/**
	 * Callback for `the_content` filter.
	 *
	 * Checks for the `cn-entry-slug` query var and if it is set. replace the post content with a shortcode to query
	 * only the queried entry.
	 *
	 * @param string $content Post content.
	 *
	 * @return string
	 */
	public static function single( $content ) {

		$slug    = get_query_var( 'cn-entry-slug' );
		$matches = self::find( 'connections', $content, 'matches' );
		//$x       = $content;

		if ( $slug && $matches ) {

			$atts = shortcode_parse_atts( $matches[0][3] );

			$atts['slug'] = sanitize_title( $slug );

			$shortcode = self::write( 'connections', $atts );

			//$content = str_replace( $matches[0][0], $shortcode, $content );
			$content = $shortcode;
		}

		//return '<!-- [connections]' . print_r( $atts, true ) . ' $content: ' . $content . ' $old: ' . $x .' -->' . $content;
		return $content;
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

			// $WP_Query->queried_object_id -- This will only be set on pages, not posts. Why? Good question!

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


					// Show the just the search form w/o showing the initial results?
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

		$view = get_query_var('cn-view');

		switch ( $view ) {

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
			case 'card':

				return cnShortcode_Connections::shortcode( $atts, $content );

				break;

			// Show the "View All" result list using the "Names" template.
			case 'all':

				// Disable the output of the repeat character index.
				$atts['repeat_alphaindex'] = FALSE;

				// Force the use of the Names template.
				$atts['template'] = 'names';

				return cnShortcode_Connections::shortcode( $atts, $content );

				break;

			// Show the entry detail using a template based on the entry type.
			case 'detail':

				switch ( get_query_var('cn-process') ) {

					case 'edit':

						if ( has_action( 'cn_edit_entry_form' ) ) {

							// Check to see if the entry has been linked to a user ID.
							$entryID = get_user_meta( get_current_user_id(), 'connections_entry_id', TRUE );
							// var_dump( $entryID );

							//
							$results = $instance->retrieve->entries( array( 'status' => 'approved,pending' ) );
							// var_dump( $results );

							/*
							 * The `cn_edit_entry_form` action should only be executed if the user is
							 * logged in and they have the `connections_manage` capability and either the
							 * `connections_edit_entry` or `connections_edit_entry_moderated` capability.
							 */

							if ( is_user_logged_in() &&
								( current_user_can( 'connections_manage' ) || $entryID == $results[0]->id ) &&
								( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
								) {

								ob_start();

								if ( ! current_user_can( 'connections_edit_entry' ) && $results[0]->status == 'pending' ) {

									echo '<p>' . __( 'Your entry submission is currently under review, however, you can continue to make edits to your entry submission while your submission is under review.', 'connections' ) . '</p>';
								}

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

				//return cnShortcode_Connections::shortcode( $atts, $content );

				if ( has_action( "cn_view_$view" ) ) {

					ob_start();

					do_action( "cn_view_$view", $atts, $content, $tag );

					return ob_get_clean();
				}

				break;
		}

		return cnShortcode_Connections::shortcode( $atts, $content );
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
