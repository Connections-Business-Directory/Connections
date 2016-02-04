<?php

/**
 * Class for displaying the term checklist.
 *
 * @package     Connections
 * @subpackage  Template Parts : Term Checklist
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CN_Walker_Term_Check_List
 */
class CN_Walker_Term_Check_List extends Walker {

	/**
	 * What the class handles.
	 *
	 * @see   Walker::$tree_type
	 * @since 8.2
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @see   Walker::$db_fields
	 * @since 8.2
	 * @todo  Decouple this
	 * @var array
	 */
	public $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	/**
	 * Render an checklist of terms.
	 *
	 * This is the Connections equivalent of @see wp_terms_checklist() in WordPress core ..wp-admin/wp-includes/template.php
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *     NOTE: Additionally, all valid options as supported in @see cnTerm::getTaxonomyTerms().
	 *
	 * @type bool   $show_count        Whether or not to display the category count.
	 *                                 Default: FALSE
	 * @type string $name              The select name attribute.
	 *                                 Default: 'cat'
	 * @type int    $depth             Controls how many levels in the hierarchy of categories are to be included in the list.
	 *                                 Default: 0
	 *                                 Accepts: 0  - All categories and child categories.
	 *                                          -1 - All Categories displayed  flat, not showing the parent/child relationships.
	 *                                          1  - Show only top level/root parent categories.
	 *                                          n  - Value of n (int) specifies the depth (or level) to descend in displaying the categories.
	 * @type string $taxonomy          The taxonomy tree to display.
	 *                                 Default: 'category'
	 *                                 Accepts: Any registered taxonomy.
	 * @type mixed  $selected          The selected term ID(s) the term ID or array of term ID/s that are selected.
	 *                                 Default: 0
	 * @type bool   $return            Whether or not to return or echo the resulting HTML.
	 *                                 Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function render( $atts = array() ) {

		$out = '';

		$defaults = array(
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => FALSE,
			'hide_empty'        => FALSE,
			'name'              => 'entry_category',
			'depth'             => 0,
			'taxonomy'          => 'category',
			'selected'          => 0,
			'return'            => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! is_array( $atts['selected'] ) ) {

			$atts['selected'] = array( absint( $atts['selected'] ) );

		} else {

			array_walk( $atts['selected'], 'absint' );
		}

		$walker = new self;

		$walker->tree_type = $atts['taxonomy'];

		// Reset the name attribute so the results from cnTerm::getTaxonomyTerms() are not limited by the select attribute name.
		$terms = cnTerm::getTaxonomyTerms( $atts['taxonomy'], array_merge( $atts, array( 'name' => '' ) ) );

		if ( ! empty( $terms ) ) {

			$out .= '<ul id="' . esc_attr( $atts['taxonomy'] ) . 'checklist" class="' . esc_attr( $walker->tree_type ) . 'checklist form-no-clear">';

				$out .= $walker->walk( $terms, $atts['depth'], $atts );

			$out .= '</ul>';
		}

		if ( $atts['return'] ) {

			return $out;
		}

		echo $out;
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see   Walker:start_lvl()
	 *
	 * @since 8.2
	 *
	 * @param string $out    Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Check_List::render()
	 */
	public function start_lvl( &$out, $depth = 0, $args = array() ) {

		$out .= str_repeat( "\t", $depth ) . '<ul class="children cn-cat-children">' . PHP_EOL;
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see   Walker::end_lvl()
	 *
	 * @since 8.2
	 *
	 * @param string $out    Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Check_List::render()
	 */
	public function end_lvl( &$out, $depth = 0, $args = array() ) {

		$out .= str_repeat( "\t", $depth ) . '</ul>' . PHP_EOL;
	}

	/**
	 * Start the element output.
	 *
	 * @see   Walker::start_el()
	 *
	 * @since 8.2
	 *
	 * @uses   esc_attr()
	 * @uses   checked()
	 * @uses   disabled()
	 * @uses   esc_html()
	 * @uses   number_format_i18n()
	 *
	 * @param string $out    Passed by reference. Used to append additional content.
	 * @param object $term   The current term object.
	 * @param int    $depth  Depth of the term in reference to parents. Default 0.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Check_List::render()
	 * @param int    $id     ID of the current term.
	 */
	public function start_el( &$out, $term, $depth = 0, $args = array(), $id = 0 ) {

		$type = esc_attr( $this->tree_type );
		$name = esc_attr( $args['name'] );

		$out .= PHP_EOL . "<li id='{$type}-{$term->term_id}'>" . '<label class="selectit"><input value="' . $term->term_id . '" type="checkbox" name="' . $name . '[]" id="cn-in-' . $type . '-' . $term->term_id . '"' .
		        checked( in_array( $term->term_id, $args['selected'] ), TRUE, FALSE ) .
		        disabled( empty( $args['disabled'] ), FALSE, FALSE ) . ' /> ' .
		        esc_html( $term->name ) . '</label>';

		if ( $args['show_count'] ) {

			$out .= '&nbsp;&nbsp;(' . number_format_i18n( $term->count ) . ')';
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see   Walker::end_el()
	 *
	 * @since 8.2
	 *
	 * @param string $out      Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see CN_Walker_Term_Check_List::render()
	 */
	public function end_el( &$out, $category, $depth = 0, $args = array() ) {

		$out .= '</li>' . PHP_EOL;
	}

}
