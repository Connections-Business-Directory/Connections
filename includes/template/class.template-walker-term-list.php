<?php

/**
 * Class for displaying the term list.
 *
 * @package     Connections
 * @subpackage  Template Parts : Term List
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.1.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CN_Walker_Term_List
 */
class CN_Walker_Term_List extends Walker {

	/**
	 * What the class handles.
	 *
	 * @see   Walker::$tree_type
	 * @since 8.1.5
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @see   Walker::$db_fields
	 * @since 8.1.6
	 * @todo  Decouple this
	 * @var array
	 */
	public $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	/**
	 * Render an unordered list of categories.
	 *
	 * This is the Connections equivalent of @see wp_list_categories() in WordPress core ../wp-includes/category-template.php
	 *
	 * @access public
	 * @since  8.1.6
	 * @static
	 *
	 * @uses   wp_parse_args()
	 * @uses   cnTerm::getTaxonomyTerms()
	 * @uses   cnURL::permalink()
	 * @uses   Walker::walk()
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *     NOTE: Additionally, all valid options as supported in @see cnTerm::getTaxonomyTerms().
	 *
	 * @type string $show_option_all  A non-blank value causes the display of a link to the directory home page.
	 *                                Default: ''. The default is not to display a link.
	 *                                Accepts: Any valid string.
	 * @type string $show_option_none Set the text to show when no categories are listed.
	 *                                Default: 'No Categories'
	 *                                Accepts: Any valid string.
	 * @type bool   $show_count       Whether or not to display the category count.
	 *                                Default: FALSE
	 * @type int    $depth            Controls how many levels in the hierarchy of categories are to be included in the list.
	 *                                Default: 0
	 *                                Accepts: 0  - All categories and child categories.
	 *                                         -1 - All Categories displayed  flat, not showing the parent/child relationships.
	 *                                         1  - Show only top level/root parent categories.
	 *                                         n  - Value of n (int) specifies the depth (or level) to descend in displaying the categories.
	 * @type string $taxonomy         The taxonomy tree to display.
	 *                                Default: 'category'
	 *                                Accepts: Any registered taxonomy.
	 * @type bool   $return           Whether or not to return or echo the resulting HTML.
	 *                                Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function render( $atts = array() ) {

		$out = '';

		$defaults = array(
			'show_option_all'  => '',
			'show_option_none' => __( 'No categories', 'connections' ),
			'orderby'          => 'name',
			'order'            => 'ASC',
			'show_count'       => FALSE,
			'hide_empty'       => FALSE,
			'child_of'         => 0,
			'exclude'          => array(),
			'hierarchical'     => TRUE,
			'depth'            => 0,
			'taxonomy'         => 'category',
			'return'           => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Provided for backward compatibility.
		$atts['hide_empty'] = isset( $atts['show_empty'] ) && $atts['show_empty'] && ! $atts['hide_empty'] ? TRUE : FALSE;
		$atts['child_of']   = isset( $atts['parent_id'] ) && ! empty( $atts['parent_id'] ) && empty( $atts['child_of'] ) ? $atts['parent_id'] : $atts['child_of'];

		$walker = new self;

		$walker->tree_type = $atts['taxonomy'];

		$out .= '<ul class="cn-cat-tree">';

		$terms = cnTerm::getTaxonomyTerms( $walker->tree_type, $atts );

		if ( empty( $terms ) ) {

			$out .= '<li class="cat-item-none">' . $atts['show_option_none'] . '</li>';

		} else {

			// @todo If viewing a single category set the $atts['current_category'] to the category's ID.
			//if ( get_query_var( 'cn-cat' ) ) {
			//
			//	if ( ! is_array( get_query_var( 'cn-cat' ) ) ) {
			//
			//		$atts['current_category'] = get_query_var( 'cn-cat' );
			//	}
			//}

			if ( ! empty( $atts['show_option_all'] ) ) {

				$out .= '<li class="cat-item-all"><a href="' . cnURL::permalink( array( 'type' => 'home', 'data' => 'url', 'return' => TRUE ) )  . '">' . $atts['show_option_all'] . '</a></li>';
			}

			$out .= $walker->walk( $terms, $atts['depth'], $atts );
		}

		$out .= '</ul>';

		if ( $atts['return'] ) {

			return $out;
		}

		echo $out;
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see   Walker::start_lvl()
	 *
	 * @since 8.1.6
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_List::render()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		$indent = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children cn-cat-children'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see   Walker::end_lvl()
	 *
	 * @since 8.1.6
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_List::render()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see   Walker::start_el()
	 *
	 * @since 8.1.6
	 *
	 * @uses   esc_attr()
	 * @uses   number_format_i18n()
	 * @uses   cnTerm::get()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $term   Term object.
	 * @param int    $depth  Depth of category in reference to parents. Default 0.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_List::render()
	 * @param int    $id     ID of the current term.
	 */
	public function start_el( &$output, $term, $depth = 0, $args = array(), $id = 0 ) {

		$count = ( $args['show_count'] ) ? ' (' . number_format_i18n( $term->count ) . ')' : '';

		$url = cnTerm::permalink( $term, 'category' );

		$link = sprintf( '<a href="%1$s" title="%2$s">%3$s',
		                 $url,
		                 esc_attr( $term->name ),
		                 esc_attr( $term->name . $count ) ) . '</a>';

		$output .= "\t<li";
		$class = 'cat-item cat-item-' . $term->term_id . ' cn-cat-parent';

		if ( ! empty( $args['current_category'] ) ) {

			$_current_category = cnTerm::get( $args['current_category'], $term->taxonomy );

			if ( $term->term_id == $args['current_category'] ) {

				$class .= ' current-cat';

			} elseif ( $term->term_id == $_current_category->parent ) {

				$class .= ' current-cat-parent';
			}
		}

		$output .= ' class="' . $class . '"';
		$output .= ">$link\n";
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see   Walker::end_el()
	 *
	 * @since 8.1.6
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page   Not used.
	 * @param int    $depth  Depth of category.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_List::render()
	 */
	public function end_el( &$output, $page, $depth = 0, $args = array() ) {

		$output .= "</li>\n";
	}

}
