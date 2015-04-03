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
			'parent_id'        => array(),
			'taxonomy'         => 'category',
			'return'           => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$atts['parent_id'] = wp_parse_id_list( $atts['parent_id'] );

		$walker = new self;

		if ( empty( $atts['parent_id'] ) ) {

			$terms = cnTerm::getTaxonomyTerms( $atts['taxonomy'], $atts );

		} else {

			$terms = cnTerm::getTaxonomyTerms(
				$atts['taxonomy'],
				array_merge( $atts, array( 'include' => $atts['parent_id'], 'child_of' => 0 ) )
			);

			// If any of the `parent_id` is not a root parent (where $term->parent = 0) set it parent ID to `0`
			// so the term tree will be properly constructed.
			foreach ( $terms as $term ) {

				if ( 0 !== $term->parent ) $term->parent = 0;
			}

			foreach ( $atts['parent_id'] as $termID ) {

				$children = cnTerm::getTaxonomyTerms(
					$atts['taxonomy'],
					array_merge( $atts, array( 'child_of' => $termID ) )
				);

				if ( ! is_wp_error( $children ) ) {

					$terms = array_merge( $terms, $children );
				}
			}
		}

		$out .= '<ul class="cn-cat-tree">' . PHP_EOL;

		if ( empty( $terms ) ) {

			$out .= '<li class="cat-item-none">' . $atts['show_option_none'] . '</li>';

		} else {

			if ( get_query_var( 'cn-cat-slug' ) ) {

				$slug = explode( '/', get_query_var( 'cn-cat-slug' ) );

				// If the category slug is a descendant, use the last slug from the URL for the query.
				$atts['current_category'] = end( $slug );

			} elseif ( $catIDs = get_query_var( 'cn-cat' ) ) {

				if ( is_array( $catIDs ) ) {

					// If value is a string, strip the white space and covert to an array.
					$catIDs = wp_parse_id_list( $catIDs );

					// Use the first element
					$atts['current_category'] = reset( $catIDs );

				} else {

					$atts['current_category'] = $catIDs;
				}

			} else {

				$atts['current_category'] = 0;
			}

			if ( ! empty( $atts['show_option_all'] ) ) {

				$out .= '<li class="cat-item-all"><a href="' . cnURL::permalink( array( 'type' => 'home', 'data' => 'url', 'return' => TRUE ) )  . '">' . $atts['show_option_all'] . '</a></li>';
			}

			$out .= $walker->walk( $terms, $atts['depth'], $atts );
		}

		$out .= '</ul>' . PHP_EOL;

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
		$output .= "$indent<ul class='children cn-cat-children'>" . PHP_EOL;
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
		$output .= "$indent</ul>" . PHP_EOL;
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

		$indent = str_repeat( "\t", $depth );

		$count = $args['show_count'] ? '&nbsp;(' . number_format_i18n( $term->count ) . ')' : '';

		$url = cnTerm::permalink( $term, 'category' );

		$link = sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a>',
			$url,
			esc_attr( $term->name ),
			esc_html( $term->name . $count )
		);

		$class = 'cat-item cat-item-' . $term->term_id . ' cn-cat-parent';

		if ( ! empty( $args['current_category'] ) ) {

			if ( is_numeric( $args['current_category'] ) ) {

				$_current_category = cnTerm::get( $args['current_category'], $term->taxonomy );

			} else {

				$_current_category = new stdClass();
				$_current_category->parent = 0;
			}

			if ( $term->slug == $args['current_category'] ) {

				$class .= ' current-cat';

			} elseif ( $term->term_id == $args['current_category'] ) {

				$class .= ' current-cat';

			} elseif ( $term->term_id == $_current_category->parent ) {

				$class .= ' current-cat-parent';
			}
		}

		$output .= "$indent<li" . ' class="' . $class . '"' . ">$link</li>" . PHP_EOL;
	}
}
