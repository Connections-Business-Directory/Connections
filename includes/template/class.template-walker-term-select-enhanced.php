<?php

/**
 * Class for displaying the term select list for use with the Chosen jQuery plugin.
 *
 * NOTE: This is only for backward compatibility only, this should not be used for new projects.
 *
 * @package     Connections
 * @subpackage  Template Parts : Term Select List - Chosen
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CN_Walker_Term_Select_List_Enhanced
 */
class CN_Walker_Term_Select_List_Enhanced extends Walker {

	/**
	 * What the class handles.
	 *
	 * @see   Walker::$tree_type
	 * @since 8.2.4
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @see   Walker::$db_fields
	 * @since 8.2.4
	 * @todo  Decouple this
	 * @var array
	 */
	public $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	/**
	 * Whether or not to close an open optgroup.
	 *
	 * @since 8.2.4
	 * @var bool
	 */
	private $close_group = FALSE;

	/**
	 * Display or retrieve the HTML select list of terms.
	 *
	 * This is the Connections equivalent of @see wp_dropdown_categories() in WordPress core ../wp-includes/category-template.php
	 *
	 * @access public
	 * @since  8.2.4
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
	 * @type string $taxonomy           The taxonomy tree to display.
	 *                                  Default: 'category'
	 * @type bool   $hierarchical       Whether to include terms that have non-empty descendants, even if 'hide_empty' is set to TRUE.
	 *                                  Default: TRUE
	 * @type string $type               The output type of the categories.
	 *                                  Default: select
	 *                                  Accepts: select || multiselect
	 * @type bool   $group              Whether or not to create option groups using the root parent as the group label.
	 *                                  Default: FALSE
	 * @type bool   $hide_if_empty      Whether or not to show the select if no terms are returned by term query.
	 *                                  Default: FALSE
	 * @type string $name               The select name attribute.
	 *                                  Default: 'cn-cat'
	 * @type string $id                 The select id attribute.
	 *                                  Default: ''
	 * @type array  $class              An array if classes to applied to the select.
	 *                                  Default: array('cn-category-select')
	 * @type array  $style              An array of style to applied inline where the key is the style attribute and the value is the style attribute value.
	 *                                  Default: array()
	 * @type bool   $enhanced           Whether of not apply the required attributes for the Chosen jQuery plugin.
	 *                                  Default: TRUE
	 * @type string $on_change          An inline JavaScript on_change event.
	 *                                  Default: ''
	 *                                  Accepts: Any valid inline JavaScript.
	 * @type int    $tab_index          The tab index of the select.
	 *                                  Default: 0
	 * @type bool   $placeholder_option Whether or not to add a blank <option> item at the top of the list for Chosen/Select2
	 *                                  Default: FALSE
	 * @type bool   $show_select_all    Whether or not to render the $show_option_all option.
	 *                                  Default: TRUE
	 * @type string $show_option_all    A non-blank value causes the display of a link to the directory home page.
	 *                                  Default: ''. The default is not to display a link.
	 *                                  Accepts: Any valid string.
	 * @type string $show_option_none   Set the text to show when no categories are listed.
	 *                                  Default: 'No Categories'
	 *                                  Accepts: Any valid string.
	 * @type string $option_none_value  Value to use when no term is selected.
	 *                                  Default: -1
	 *                                  Accepts: Any valid int/string for an option value attribute.
	 * @type string $default            The default string to show as the first item in the list.
	 *                                  Default: 'Select Category'
	 * @type bool   $show_count         Whether or not to display the category count.
	 *                                  Default: FALSE
	 * @type bool   $hide_empty         Whether or not to display empty terms.
	 *                                  Default: FALSE
	 * @type int    $depth              Controls how many levels in the hierarchy of categories are to be included in the list.
	 *                                  Default: 0
	 *                                  Accepts: 0  - All categories and child categories.
	 *                                           -1 - All Categories displayed  flat, not showing the parent/child relationships.
	 *                                           1  - Show only top level/root parent categories.
	 *                                           n  - Value of n (int) specifies the depth (or level) to descend in displaying the categories.
	 * @type array  $parent_id
	 * @type array  $selected           The selected term IDs.
	 *                                  Default: 0
	 * @type string $label              The label to render with the select.
	 *                                  Default: ''
	 * @type string $before             Content to be render before the label and select.
	 *                                  Default: ''
	 * @type string $after              Content to be render after the label and select.
	 *                                  Default: ''
	 * @type string $layout             Tokens which can be sued to control the order of the label and select.
	 *                                  Default: '%label%%field%'
	 *                                  Accepts: %label% %field%
	 * @type bool   $return             Whether or not to return or echo the resulting HTML.
	 *                                  Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function render( $atts = array() ) {

		$select = '';
		$out    = '';

		$defaults = array(
			'taxonomy'          => 'category',
			'hierarchical'      => TRUE,
			'type'              => 'select',
			'group'             => FALSE,
			'hide_if_empty'     => FALSE,
			'name'              => 'cn-cat',
			'id'                => '',
			'class'             => array('cn-category-select'),
			'style'             => array(),
			'enhanced'          => TRUE,
			'on_change'         => '',
			'tab_index'         => 0,
			'placeholder_option' => TRUE,
			'show_select_all'   => TRUE,
			'show_option_all'   => '',
			'show_option_none'  => '',
			'option_none_value' => -1,
			'default'           => __( 'Select Category', 'connections' ), // This is the data-placeholder select attribute utilized by Chosen.
			'show_count'        => FALSE,
			'hide_empty'        => FALSE,
			'depth'             => 0,
			'parent_id'         => array(),
			'selected'          => 0,
			'label'             => '',
			'before'            => '',
			'after'             => '',
			'layout'            => '%label%%field%',
			'return'            => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( wp_is_mobile() ) {

			$atts['enhanced'] = FALSE;
		}

		// The field parts to be searched for in $atts['layout'].
		$search = array( '%label%', '%field%' );

		// An array to store the replacement strings for the label and field.
		$replace = array();

		$walker = new self;

		$walker->tree_type = $atts['taxonomy'];

		if ( ! isset( $atts['pad_counts'] ) && $atts['show_count'] && $atts['hierarchical'] ) {

			// Padding the counts is ideal, but really, really, bloats the memory required.
			$atts['pad_counts'] = FALSE;
		}

		if ( empty( $atts['parent_id'] ) ) {

			$terms = cnTerm::getTaxonomyTerms( $atts['taxonomy'], array_merge( $atts, array( 'name' => '' ) ) );

		} else {

			$atts['parent_id'] = wp_parse_id_list( $atts['parent_id'] );

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

		if ( ! $atts['hide_if_empty'] || ! empty( $terms ) ) {

			//$out .= PHP_EOL . "<select name='$name' id='$id' class='$class' $tab_index_attribute>" . PHP_EOL;

			// Add the 'cn-enhanced-select' class for the jQuery Chosen Plugin will enhance the drop down.
			if ( $atts['enhanced'] ) $atts['class'] = array_merge( (array) $atts['class'], array('cn-enhanced-select') );

			// Create the field label, if supplied.
			$replace[] = ! empty( $atts['label'] ) ? cnHTML::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => TRUE ) ) : '';

			$select .= sprintf(
				'<select %1$s %2$s name="%3$s"%4$s%5$sdata-placeholder="%6$s"%7$s%8$s>' . PHP_EOL,
				empty( $atts['class'] ) ? '' : cnHTML::attribute( 'class', $atts['class'] ),
				empty( $atts['id'] ) ? '' : cnHTML::attribute( 'id', $atts['id'] ),
				$atts['type'] == 'multiselect' ? esc_attr( $atts['name'] ) . '[]' : esc_attr( $atts['name'] ),
				empty( $atts['style'] ) ? '' : cnHTML::attribute( 'style', $atts['style'] ),
				$atts['type'] == 'multiselect' ? '' : ( empty( $atts['on_change'] ) ? '' : sprintf( ' onchange="%s" ', esc_js( $atts['on_change'] ) ) ),
				esc_attr( $atts['default'] ),
				$atts['type'] == 'multiselect' ? ' MULTIPLE' : '',
				(int) $atts['tab_index'] > 0 ? " tabindex=\"{$atts['tab_index']}\"" : ''
			);

		} else {

			$select .= '';
		}

		if ( empty( $terms ) && ! $atts['hide_if_empty'] && ! empty( $atts['show_option_none'] ) ) {

			/** This filter is documented in includes/template/class.template-walker-term-select.php */
			$show_option_none = apply_filters( 'cn_list_cats', $atts['show_option_none'] );

			$select .= "\t<option value='" . esc_attr( $atts['option_none_value'] ) . "' selected='selected'>$show_option_none</option>" . PHP_EOL;
		}

		if ( ! empty( $terms ) ) {

			if ( $atts['enhanced'] || $atts['placeholder_option'] ) {

				/*
				 * If a select enhancement library is being used such as Chosen or Select2, add the placeholder option
				 * to the top of the available options.
				 *
				 * When doing a select all, set the placeholder option as the default selected option.
				 */
				$selected = ! $atts['enhanced'] && is_numeric( $atts['selected'] ) && '0' === strval( $atts['selected'] ) ? " selected='selected'" : '';

				$select  .= "\t" . '<option value="" ' . $selected . '>' . ( $atts['enhanced'] ? '' : $atts['default'] ) . '</option>';
			}

			if ( $atts['show_select_all'] && $atts['show_option_all'] ) {

				/** This filter is documented in includes/template/class.template-walker-term-select.php */
				$show_option_all = apply_filters( 'cn_list_cats', $atts['show_option_all'] );

				/*
				 * When doing a select all, set the show all option value as selected.
				 *
				 * NOTE: This is only done when the select is not being enhanced by a library such as Chosen or Select2.
				 * In that case the placeholder option should be the default selected item.
				 */
				$selected = ! $atts['enhanced'] && ! isset( $selected ) && is_numeric( $atts['selected'] ) && '0' === strval( $atts['selected'] ) ? " selected='selected'" : '';
				$select  .= "\t<option value='0'$selected>$show_option_all</option>" . PHP_EOL;
			}

			if ( $atts['show_option_none'] ) {

				/** This filter is documented in includes/template/class.template-walker-term-select.php */
				$show_option_none = apply_filters( 'cn_list_cats', $atts['show_option_none'] );
				$selected         = selected( $atts['option_none_value'], $atts['selected'], FALSE );
				$select          .= "\t<option value='" . esc_attr( $atts['option_none_value'] ) . "'$selected>$show_option_none</option>" . PHP_EOL;
			}

			if ( $atts['hierarchical'] ) {

				$depth = $atts['depth'];  // Walk the full depth.

			} else {

				$depth = -1; // Flat.
			}

			$select .= $walker->walk( $terms, $depth, $atts );
		}

		if ( ! $atts['hide_if_empty'] || ! empty( $terms ) ) {

			// If an option group was left open, ensure it is closed before closing the select.
			if ( $walker->close_group ) {

				$select .= "\t" . '</optgroup>' . PHP_EOL;
				$walker->close_group = FALSE;
			}

			$select .= "</select>" . PHP_EOL;

			$replace[] = $select;

			$out = str_ireplace( $search, $replace, $atts['layout'] );

			$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] );
		}

		/**
		 * Filter the taxonomy drop-down output.
		 *
		 * @since 8.2.4
		 *
		 * @param string $out  HTML output.
		 * @param array  $atts Arguments used to build the drop-down.
		 */
		$out = apply_filters( 'cn_dropdown_cats', $out, $atts );

		if ( $atts['return'] ) {

			return $out;
		}

		 echo $out;
	}

	/**
	 * Sets @see CN_Walker_Term_Select_List_Chosen::close_group to true if grouping by parent category.
	 *
	 * @access public
	 * @since  8.2.4
	 * @static
	 *
	 * @see    Walker::start_lvl()
	 *
	 * @param string $out   Passed by reference. Used to append additional content.
	 * @param int    $depth Depth of category in reference to parents. Default 0.
	 * @param array  $args  An array of arguments. @see CN_Walker_Term_Select_List::render()
	 */
	public function start_lvl( &$out, $depth = 0, $args = array() ) {

		if ( $args['group'] && $this->has_children ) {

			$this->close_group = TRUE;
		}
	}

	/**
	 * Sets @see CN_Walker_Term_Select_List_Chosen::close_group to false if grouping by parent category.
	 *
	 * @access public
	 * @since  8.2.4
	 * @static
	 *
	 * @see    Walker::end_lvl()
	 *
	 * @param string $out   Passed by reference. Used to append additional content.
	 * @param int    $depth Depth of category in reference to parents. Default 0.
	 * @param array  $args  An array of arguments. @see CN_Walker_Term_Select_List::render()
	 */
	public function end_lvl( &$out, $depth = 0, $args = array() ) {

		if ( $args['group'] && $this->close_group && 0 === $depth ) {

			$out .= "\t" . '</optgroup>' . PHP_EOL;
			$this->close_group = FALSE;
		}
	}

	/**
	 * Start the element output.
	 *
	 * @access public
	 * @since  8.2.4
	 * @static
	 *
	 * @see    Walker::start_el()
	 *
	 * @uses   apply_filters()
	 * @uses   number_format_i18n()
	 *
	 * @param string $out   Passed by reference. Used to append additional content.
	 * @param object $term  Category data object.
	 * @param int    $depth Depth of category in reference to parents. Default 0.
	 * @param array  $args  An array of arguments. @see CN_Walker_Term_Select_List::render()
	 * @param int    $id    ID of the current category.
	 */
	public function start_el( &$out, $term, $depth = 0, $args = array(), $id = 0 ) {

		if ( ! $args['group'] ) {

			$this->do_el( $out, $term, $depth, $args);

		} elseif ( $args['group'] && 0 === $depth && $this->has_children ) {

			$out .= sprintf( "\t" . '<optgroup label="%1$s">' . PHP_EOL, esc_attr( $term->name ) );

		} elseif ( $args['group'] && 0 < $depth ) {

			$this->do_el( $out, $term, $depth, $args);
		}
	}

	/**
	 * Render the select option for the current term.
	 *
	 * @access public
	 * @since  8.2.4
	 * @static
	 *
	 * @param string $out   Passed by reference. Used to append additional content.
	 * @param object $term  Category data object.
	 * @param int    $depth Depth of category in reference to parents. Default 0.
	 * @param array  $args  An array of arguments. @see CN_Walker_Term_Select_List::render()
	 */
	private function do_el( &$out, $term, $depth, $args ) {

		// The padding in px to indent descendant categories. The 7px is the default pad applied in the CSS which must be taken in to account.
		$pad = ( $depth > 0 ) ? $depth * 12 + 7 : 7;

		// Set the option SELECTED attribute if the category is one of the currently selected categories.
		$selected = in_array( $term->term_id, (array) $args['selected'] ) || in_array( $term->slug, (array) $args['selected'], TRUE ) ? ' SELECTED' : '';

		$out .= sprintf(
			"\t" . '<option class="cn-term-level-%1$d" style="padding-%2$s: %3$dpx !important;" value="%4$s"%5$s>%6$s%7$s%8$s</option>' . PHP_EOL,
			$depth,
			is_rtl() ? 'right' : 'left',
			$pad,
			$term->term_id,
			$selected,
			$args['enhanced'] ? '' : str_repeat( '&nbsp;', $depth * 3 ),
			/** This filter is documented in includes/template/class.template-walker-term-select.php */
			esc_html( apply_filters( 'cn_list_cats', $term->name, $term ) ),
			// Category count to be appended to the category name.
			$args['show_count'] ? '&nbsp;(' . number_format_i18n( $term->count ) . ')' : ''
		);
	}
}
