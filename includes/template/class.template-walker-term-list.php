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
			'force_home'       => FALSE,
			'home_id'          => cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
			'return'           => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$atts['parent_id'] = wp_parse_id_list( $atts['parent_id'] );

		$walker = new self;

		if ( empty( $atts['parent_id'] ) ) {

			$terms = cnTerm::getTaxonomyTerms( $atts['taxonomy'], array_merge( $atts, array( 'name' => '' ) ) );

		} else {

			$terms = cnTerm::getTaxonomyTerms(
				$atts['taxonomy'],
				array_merge( $atts, array( 'include' => $atts['parent_id'], 'child_of' => 0, 'name' => '' ) )
			);

			// If any of the `parent_id` is not a root parent (where $term->parent = 0) set it parent ID to `0`
			// so the term tree will be properly constructed.
			foreach ( $terms as $term ) {

				if ( 0 !== $term->parent ) $term->parent = 0;
			}

			foreach ( $atts['parent_id'] as $termID ) {

				$children = cnTerm::getTaxonomyTerms(
					$atts['taxonomy'],
					array_merge( $atts, array( 'child_of' => $termID, 'name' => '' ) )
				);

				if ( ! is_wp_error( $children ) ) {

					$terms = array_merge( $terms, $children );
				}
			}
		}

		/**
		 * Allows extensions to add/remove class names to the term tree list.
		 *
		 * @since 8.5.18
		 *
		 * @param array $class The array of class names.
		 * @param array $terms The array of terms.
		 * @param array $atts  The method attributes.
		 */
		$class = apply_filters( 'cn_term_list_class', array( 'cn-cat-tree' ), $terms, $atts );
		$class = cnSanitize::htmlClass( $class );

		$out .= '<ul class="' . cnFunction::escAttributeDeep( $class ) . '">' . PHP_EOL;

		if ( empty( $terms ) ) {

			$out .= '<li class="cat-item-none">' . $atts['show_option_none'] . '</li>';

		} else {

			if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

				$slug = explode( '/', cnQuery::getVar( 'cn-cat-slug' ) );

				// If the category slug is a descendant, use the last slug from the URL for the query.
				$atts['current_category'] = end( $slug );

			} elseif ( $catIDs = cnQuery::getVar( 'cn-cat' ) ) {

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

		/**
		 * Allows extensions to add/remove class names to the children term tree list.
		 *
		 * @since 8.5.18
		 *
		 * @param array $class The array of class names.
		 * @param int   $depth The current term hierarchy depth.
		 * @param array $args  The method attributes.
		 */
		$class = apply_filters( 'cn_term_children_list_class', array( 'children', 'cn-cat-children' ), $depth, $args );
		$class = cnSanitize::htmlClass( $class );

		$output .= $indent . '<ul class="' . cnFunction::escAttributeDeep( $class ) . '">' . PHP_EOL;
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

		$count = $args['show_count'] ? '<span class="cn-cat-count">&nbsp;(' . esc_html( number_format_i18n( $term->count ) ) . ')</span>' : '';

		$url = cnTerm::permalink( $term, 'category', $args );

		$html = sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a>',
			$url,
			esc_attr( $term->name ),
			esc_html( $term->name ) . $count
		);

		/**
		 * Allows extensions to alter the HTML of term list item.
		 *
		 * @since 8.5.18
		 *
		 * @param string        $html  The HTML.
		 * @param cnTerm_Object $term  The current term.
		 * @param int           $depth Depth of category. Used for tab indentation.
		 * @param array         $args  The method attributes.
		 */
		$html = apply_filters( 'cn_term_list_item', $html, $term, $depth, $args );

		$class = array( 'cat-item', 'cat-item-' . $term->term_id, 'cn-cat-parent' );

		$termChildren = cnTerm::getTaxonomyTerms(
			$term->taxonomy,
			array(
				'parent'     => $term->term_id,
				'hide_empty' => FALSE,
				'fields'     => 'count',
				)
		);

		if ( ! empty( $termChildren ) ) {

			$class[] = 'cn-cat-has-children';
		}

		if ( ! empty( $args['current_category'] ) ) {

			if ( is_numeric( $args['current_category'] ) ) {

				$_current_category = cnTerm::get( $args['current_category'], $term->taxonomy );

				// cnTerm::get() can return NULL || an instance of WP_Error, so, lets check for that.
				if ( is_null( $_current_category ) || is_wp_error( $_current_category ) ) {

					$_current_category = new stdClass();
					$_current_category->parent = 0;
				}

			} else {

				$_current_category = new stdClass();
				$_current_category->parent = 0;
			}

			if ( $term->slug == $args['current_category'] ) {

				$class[] = ' current-cat';

			} elseif ( $term->term_id == $args['current_category'] ) {

				$class[] = ' current-cat';

			} elseif ( $term->term_id == $_current_category->parent ) {

				$class[] = ' current-cat-parent';
			}
		}

		/**
		 * Allows extensions to add/remove class names to the current term list item.
		 *
		 * @since 8.5.18
		 *
		 * @param array         $class The array of class names.
		 * @param cnTerm_Object $term  The current term.
		 * @param int           $depth Depth of category. Used for tab indentation.
		 * @param array         $args  The method attributes.
		 */
		$class = apply_filters( 'cn_term_list_item_class', $class, $term, $depth, $args );
		$class = cnSanitize::htmlClass( $class );

		$output .= "$indent<li" . ' class="' . cnFunction::escAttributeDeep( $class ) . '"' . ">$html"; // Do not add EOL here, it'll add unwanted whitespace if terms are inline.
	}

	/**
	 * Ends the element output.
	 *
	 * @see   Walker::start_el()
	 *
	 * @since 8.5.15
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object Term object.
	 * @param int    $depth  Depth of category in reference to parents. Default 0.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_List::render()
	 */
	public function end_el( &$output, $object, $depth = 0, $args = array() ) {

		$output .= "</li>" . PHP_EOL;
	}
}
