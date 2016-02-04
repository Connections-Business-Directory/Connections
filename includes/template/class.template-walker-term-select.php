<?php

/**
 * Class for displaying the term select list.
 *
 * @package     Connections
 * @subpackage  Template Parts : Term Select List
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CN_Walker_Term_Select_List
 */
class CN_Walker_Term_Select_List extends Walker {

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
	 * Display or retrieve the HTML select list of terms.
	 *
	 * This is the Connections equivalent of @see wp_dropdown_categories() in WordPress core ../wp-includes/category-template.php
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @uses   wp_parse_args()
	 * @uses   cnTerm::getTaxonomyTerms()
	 * @uses   esc_attr()
	 * @uses   sanitize_html_class()
	 * @uses   apply_filters()
	 * @uses   Walker::walk()
	 * @uses   selected()
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *     NOTE: Additionally, all valid options as supported in @see cnTerm::getTaxonomyTerms().
	 *
	 * @type string $show_option_all   A non-blank value causes the display of a link to the directory home page.
	 *                                 Default: ''. The default is not to display a link.
	 *                                 Accepts: Any valid string.
	 * @type string $show_option_none  Set the text to show when no categories are listed.
	 *                                 Default: 'No Categories'
	 *                                 Accepts: Any valid string.
	 * @type bool   $show_count        Whether or not to display the category count.
	 *                                 Default: FALSE
	 * @type string $name              The select name attribute.
	 *                                 Default: 'cat'
	 * @type string $id                The select id attribute.
	 *                                 Default: ''
	 * @type string $class             The select class attribute.
	 *                                 Default: 'postform'
	 * @type int    $depth             Controls how many levels in the hierarchy of categories are to be included in the list.
	 *                                 Default: 0
	 *                                 Accepts: 0  - All categories and child categories.
	 *                                          -1 - All Categories displayed  flat, not showing the parent/child relationships.
	 *                                          1  - Show only top level/root parent categories.
	 *                                          n  - Value of n (int) specifies the depth (or level) to descend in displaying the categories.
	 * @type int    $tab_index         The select tab index.
	 *                                 Default: 0
	 * @type string $taxonomy          The taxonomy tree to display.
	 *                                 Default: 'category'
	 *                                 Accepts: Any registered taxonomy.
	 * @type bool   $hide_if_empty     Whether or not to show the select if no terms are returned by term query.
	 *                                 Default: FALSE
	 * @type string $option_none_value Value to use when no term is selected.
	 *                                 Default: -1
	 *                                 Accepts: Any valid int/string for an option value attribute.
	 * @type int    $selected          The selected term ID.
	 * @type bool   $return            Whether or not to return or echo the resulting HTML.
	 *                                 Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function render( $atts = array() ) {

		$out = '';

		$defaults = array(
			'show_option_all'   => '',
			'show_option_none'  => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => FALSE,
			'hide_empty'        => FALSE,
			'name'              => 'cat',
			'id'                => '',
			'class'             => 'postform',
			'depth'             => 0,
			'tab_index'         => 0,
			'taxonomy'          => 'category',
			'hide_if_empty'     => FALSE,
			'option_none_value' => -1,
			'selected'          => 0,
			'return'            => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$walker = new self;

		$walker->tree_type = $atts['taxonomy'];

		if ( ! isset( $atts['pad_counts'] ) && $atts['show_count'] && $atts['hierarchical'] ) {

			$atts['pad_counts'] = TRUE;
		}

		$tab_index_attribute = (int) $atts['tab_index'] > 0 ? " tabindex=\"{$atts['tab_index']}\"" : '';

		$terms = cnTerm::getTaxonomyTerms( $atts['taxonomy'], array_merge( $atts, array( 'name' => '' ) ) );
		$name  = esc_attr( $atts['name'] );
		$class = sanitize_html_class( $atts['class'] );
		$id    = $atts['id'] ? esc_attr( $atts['id'] ) : $name;

		if ( ! $atts['hide_if_empty'] || ! empty( $terms ) ) {

			$out .= PHP_EOL . "<select name='$name' id='$id' class='$class' $tab_index_attribute>" . PHP_EOL;

		} else {

			$out .= '';
		}

		if ( empty( $terms ) && ! $atts['hide_if_empty'] && ! empty( $atts['show_option_none'] ) ) {

			/**
			 * Filter a taxonomy drop-down display element.
			 *
			 * @since 8.2
			 *
			 * @param string $element Taxonomy term name.
			 */
			$show_option_none = apply_filters( 'cn_list_cats', $atts['show_option_none'] );

			$out .= "\t<option value='" . esc_attr( $atts['option_none_value'] ) . "' selected='selected'>$show_option_none</option>\n";
		}

		if ( ! empty( $terms ) ) {

			if ( $atts['show_option_all'] ) {

				/** This filter is documented in includes/template/class.template-walker-term-select.php */
				$show_option_all = apply_filters( 'cn_list_cats', $atts['show_option_all'] );
				$selected        = ( '0' === strval( $atts['selected'] ) ) ? " selected='selected'" : '';
				$out .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}

			if ( $atts['show_option_none'] ) {

				/** This filter is documented in includes/template/class.template-walker-term-select.php */
				$show_option_none = apply_filters( 'cn_list_cats', $atts['show_option_none'] );
				$selected         = selected( $atts['option_none_value'], $atts['selected'], FALSE );
				$out .= "\t<option value='" . esc_attr( $atts['option_none_value'] ) . "'$selected>$show_option_none</option>\n";
			}

			if ( $atts['hierarchical'] ) {

				$depth = $atts['depth'];  // Walk the full depth.
			} else {

				$depth = -1; // Flat.
			}

			$out .= $walker->walk( $terms, $depth, $atts );
		}

		if ( ! $atts['hide_if_empty'] || ! empty( $terms ) ) {
			$out .= "</select>" . PHP_EOL;
		}
		/**
		 * Filter the taxonomy drop-down output.
		 *
		 * @since 8.2
		 *
		 * @param string $out HTML output.
		 * @param array  $atts      Arguments used to build the drop-down.
		 */
		$out = apply_filters( 'cn_dropdown_cats', $out, $atts );

		if ( $atts['return'] ) {

			return $out;
		}

		echo $out;
	}

	/**
	 * Start the element output.
	 *
	 * @see   Walker::start_el()
	 *
	 * @since 2.1.0
	 *
	 * @uses   apply_filters()
	 * @uses   selected()
	 * @uses   number_format_i18n()
	 *
	 * @param string $out   Passed by reference. Used to append additional content.
	 * @param object $term  Category data object.
	 * @param int    $depth Depth of category in reference to parents. Default 0.
	 * @param array  $args  An array of arguments. @see CN_Walker_Term_Select_List::render()
	 * @param int    $id    ID of the current category.
	 */
	public function start_el( &$out, $term, $depth = 0, $args = array(), $id = 0 ) {

		$pad = str_repeat( '&nbsp;', $depth * 3 );

		/** This filter is documented in includes/template/class.template-walker-term-select.php */
		$name = apply_filters( 'cn_list_cats', $term->name, $term );

		$out .= "\t<option class=\"level-$depth\" value=\"" . $term->term_id . "\"";

		$out .= selected( $term->term_id, $args['selected'], FALSE );

		$out .= '>';
		$out .= $pad . $name;

		if ( $args['show_count'] ) {

			$out .= '&nbsp;&nbsp;(' . number_format_i18n( $term->count ) . ')';
		}

		$out .= '</option>' . PHP_EOL;
	}
}
