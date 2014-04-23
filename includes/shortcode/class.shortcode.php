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

	/**
	 * The resulting content after being process thru the template shortcodes.
	 *
	 * @access private
	 * @since  0.8
	 * @var    string
	 */
	private $html = '';

	/**
	 * The shortcode atts passed by the core `connections` shortcode.
	 *
	 * @access private
	 * @since  0.8
	 * @var    array
	 */
	private $atts = array();

	/**
	 * An instance of the cnTemplate object.
	 *
	 * @access private
	 * @since  0.8
	 * @var    mixed   [ object | bool ] An instance of cnTemplate if one was loaded or FALSE.
	 */
	private $template = FALSE;

	/**
	 * The array containing the results of an entry query.
	 *
	 * @access private
	 * @since  0.8
	 * @var    array
	 */
	private $results = array();

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
		add_shortcode( 'connections', 'connectionsView' );
		add_shortcode( 'upcoming_list', '_upcoming_list' );
		add_shortcode( 'connections_vcard', '_connections_vcard' ); /* Experimental. Do NOT use. */
		add_shortcode( 'connections_qtip', '_connections_qtip' ); /* Experimental. Do NOT use. */
	}

	/**
	 * The core template shortcode tags.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @uses   apply_filters()
	 *
	 * @return array An array of the template shortcode tag attributes.
	 */
	private static function tags() {

		$tags = array(
			'template'           => array(
				'desc'     => __( 'Template', 'connections' ),
				'callback' => array( __CLASS__, 'do_shortcode' ),
				),
			'list_actions'        => array(
				'desc'     => __( 'List Actions', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'listActions' ),
				),
			'head'               => array(
				'desc'     => __( 'Header', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'header' ),
				),
			'body'               => array(
				'desc'     => __( 'Body', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'body' ),
				),
			'no_results'         => array(
				'desc'     => __( 'No results message.', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'noResults' ),
				),
			'cards'              => array(
				'desc'     => __( 'Cards', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'cards' ),
				),

			'card'               => array(
				'desc'     => __( 'Entry.', 'connections' ),
				'callback' => array( __CLASS__, 'processEntry' ),
				),
			'foot'               => array(
				'desc'     => __( 'Footer', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'footer' ),
				),
			'return_to_target'   => array(
				'desc'     => __( 'Return to top.', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'returnToTopTarget' ),
				),
			'character_index'    => array(
				'desc'     => __( 'Character index.', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'index' ),
				),
			'pagination'         => array(
				'desc'     => __( 'Pagination control.', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'pagination' ),
				),
			'search'             => array(
				'desc'     => __( 'Pagination control.', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'search' ),
				),
			'form_open'          => array(
				'desc'     => __( 'Form open.', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'formOpen' ),
				),
			'form_close'         => array(
				'desc'     => __( 'Form open.', 'connections' ),
				'callback' => array( 'cnTemplatePart', 'formClose' ),
				),
		);

		$tags = apply_filters( 'cn_template_shortcodes', $tags );

		// Prefix all the tags to help ensure there are no shortcode tag collisions.
		foreach ( $tags as $key => $value ) {

			$tags[ 'cn_' . $key ] = $value;
			unset( $tags[ $key ] );
		}

		return $tags;
	}

	/**
	 * Retrieve a specific template shortcode tag.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @param  string $tag The template shortcode tag.
	 *
	 * @return array       The template shortcode tag attributes.
	 */
	private static function tag( $tag ) {

		// Get the registered template shortcode tags.
		$tags = self::tags();

		// If the requested part is in the registered template shortcode tags, return its attributes.
		if ( $key = array_key_exists( $tag, $tags ) !== FALSE ) return $tags[ $tag ];

		// The tags was not found, return FALSE.
		return FALSE;
	}

	/**
	 * Search content for template shortcodes and filter shortcodes through their hooks.
	 *
	 * This was lifted from WordPress and tweaked to suit.
	 *
	 * @access public
	 * @since  0.8
	 * @uses   self::regex()
	 * @param  string $content Content to search for shortcodes
	 *
	 * @return string Content with shortcodes filtered out.
	 */
	public function do_shortcode( $content ) {

		$tags = array_keys( self::tags() );

		if ( FALSE === strpos( $content, '[' ) ) {

			return $content;
		}

		if ( empty( $tags ) || ! is_array( $tags ) ) {

			return $content;
		}

		$pattern = self::regex();

		return preg_replace_callback( "/$pattern/s", array( $this, 'do_shortcode_tag' ), $content );
	}

	/**
	 * Retrieve the shortcode regular expression for searching.
	 *
	 * The regular expression combines the shortcode tags in the regular expression
	 * in a regex class.
	 *
	 * The regular expression contains 6 different sub matches to help with parsing.
	 *
	 * 1 - An extra [ to allow for escaping shortcodes with double [[]]
	 * 2 - The shortcode name
	 * 3 - The shortcode argument list
	 * 4 - The self closing /
	 * 5 - The content of a shortcode when it wraps some content.
	 * 6 - An extra ] to allow for escaping shortcodes with double [[]]
	 *
	 * This was lifted from WordPress and tweaked to suit.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return string The shortcode search regular expression
	 */
	public static function regex( $tags = NULL ) {

		$tagnames = is_null( $tags ) ? array_keys( self::tags() ) : $tags;

		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );

		// WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
		// Also, see shortcode_unautop() and shortcode.js.
		return
			  '\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($tagregexp)"                     // 2: Shortcode name
			. '(?![\\w-])'                       // Not followed by word character or hyphen
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			.     '(?:'
			.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
			.     ')*?'
			. ')'
			. '(?:'
			.     '(\\/)'                        // 4: Self closing tag ...
			.     '\\]'                          // ... and closing bracket
			. '|'
			.     '\\]'                          // Closing bracket
			.     '(?:'
			.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			.             '[^\\[]*+'             // Not an opening bracket
			.             '(?:'
			.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			.                 '[^\\[]*+'         // Not an opening bracket
			.             ')*+'
			.         ')'
			.         '\\[\\/\\2\\]'             // Closing shortcode tag
			.     ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}

	/**
	 * Regular Expression callable for do_shortcode() for calling shortcode hook.
	 * @see self::regex() for details of the match array contents.
	 *
	 * This was lifted from WordPress and tweaked to suit.
	 *
	 * @access private
	 * @since  0.8
	 * @param  array $m Regular expression match array
	 *
	 * @return mixed False on failure.
	 */
	private function do_shortcode_tag( $m ) {

		// Normally we'd use shortcode_atts, but that strips keys from $atts that do not exist in $defaults.
		// Since $atts can contain various options for the different callback methods, we'll use wp_parse_args()
		// which will retain the keys and associated values that do not exist in $atts.
		// $atts = wp_parse_args( $atts, $defaults );

		$tags = self::tags();

		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {

			return substr($m[0], 1, -1);
		}

		$tag = self::tag( $m[2] );
		// $atts = shortcode_parse_atts( $m[3] );

		// Check to ensure the shortcode callback is callable.
		if ( ! is_callable( $tag['callback'] ) ) {

			$callback = is_array( $tag['callback'] ) ? implode( '::', $tag['callback'] ) : $tag['callback'];

			return __( sprintf( 'The %s is not a valid callback.', $callback ), 'connections' );
		}

		// Merge the $atts passed by the core `connections`
		// shortcode with this methods $defaults attributes.
		$atts = array_merge( (array) shortcode_parse_atts( $m[3] ), $this->atts );

		ob_start();

		if ( isset( $m[5] ) ) {

			// If the shortcode is an enclosing shortcode, replace the entire contents with the result
			// of any shortcodes found within the content. This provides an override of sorts.
			// The one exception is the `cn_card` shortcode.
			if ( is_string( $m[5] ) && ! empty( $m[5] ) && $m[2] != 'cn_card' ) {

				echo self::do_shortcode( $m[5] );

			} else {

				// enclosing tag - extra parameter
				// return $m[1] . call_user_func( $tag['callback'], $atts, $m[5], $m[2] ) . $m[6];
				echo $m[1] . call_user_func( $tag['callback'], $atts, $this->results, $this->template, $m[5], $m[2] ) . $m[6];

			}

		} else {

			// self-closing tag
			// return $m[1] . call_user_func( $tag['callback'], $atts, NULL,  $m[2] ) . $m[6];
			echo $m[1] . call_user_func( $tag['callback'], $atts, $this->results, $this->template, NULL, $m[2] ) . $m[6];
		}

		return ob_get_clean();
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
	 * The method to be used to process the content thru the shortcode.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  string $content  The content of an enclosing shortcode tag.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function process( $atts, $content, $results, $template ) {

		$return = new cnShortcode( $atts, $content, $results, $template );

		return $return->result();
	}

	/**
	 * Set's up the core shortcode and starts the shortcode
	 * replacement process using the WordPress shortcode API.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  string $content  The content of an enclosing shortcode tag.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	private function __construct( $atts, $content, $results, $template ) {

		// Store the entry query array $results and the cnTemplate object $template
		// so they can be easily passed to the template part shortcode callbacks.
		$this->atts     = $atts;
		$this->template = $template;
		$this->results  = $results;

		$this->html = self::do_shortcode( self::removePBR( $content ) );
	}

	/**
	 * Returns the processed content.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @return string The processed content.
	 */
	private function result() {

		return $this->html;
	}

	/**
	 * This is the callback ran for the `cn_card` shortcode that will process its
	 * content the the cnEntry_Shortcode processor.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 * @param  string $content  The content of the `cn_card` shortcode.
	 *
	 * @return string           The result of the $content being run thru cnEntry_Shortcode::process().
	 */
	public static function processEntry( $atts, $results, $template, $content ) {

		foreach ( $results as $row ) {

			echo cnEntry_Shortcode::process( new cnEntry( $row ), $content );
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
	 * the template shortcodes by wpautop().
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
