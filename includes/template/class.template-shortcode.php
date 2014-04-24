<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Template Shortcode API.
 *
 * @package     Connections
 * @subpackage  Template Shortcode API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

class cnTemplate_Shortcode {

	private static $shortcode = array();

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
	 * The core template shortcode tags.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   apply_filters()
	 *
	 * @return array An array of the template shortcode tag attributes.
	 */
	private  function tags() {

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
	 * @param  string $tag The template shortcode tag.
	 *
	 * @return array       The template shortcode tag attributes.
	 */
	private function tag( $tag ) {

		// Get the registered template shortcode tags.
		$tags = $this->tags();

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
	 * @uses   regex()
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

		$pattern = $this->regex();

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
	 *
	 * @return string The shortcode search regular expression
	 */
	public function regex( $tags = NULL ) {

		$tagnames = is_null( $tags ) ? array_keys( $this->tags() ) : $tags;

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
	 * @see regex() for details of the match array contents.
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

		$tags = $this->tags();

		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {

			return substr($m[0], 1, -1);
		}

		$tag = $this->tag( $m[2] );
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

				echo $this->do_shortcode( $m[5] );

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

		$return = new cnTemplate_Shortcode( $atts, $content, $results, $template );

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

		$this->html = $this->do_shortcode( cnShortcode::removePBR( $content ) );
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

}
