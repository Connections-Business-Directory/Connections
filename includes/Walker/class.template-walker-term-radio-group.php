<?php
/**
 * Class for displaying the term list as a radio group.
 *
 * @package     Connections
 * @subpackage  Template Parts : Term Radio Group
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CN_Walker_Term_Radio_Group
 */
class CN_Walker_Term_Radio_Group extends Walker {

	/**
	 * What the class handles.
	 *
	 * @since 8.2.4
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @since 8.2.4
	 * @todo  Decouple this
	 * @var array
	 */
	public $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	/**
	 * Render af checklist of terms.
	 *
	 * This is the Connections equivalent of @see wp_terms_checklist() in WordPress core ..wp-admin/wp-includes/template.php
	 *
	 * @since  8.2.4
	 * @static
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *     NOTE: Additionally, all valid options as supported in @see cnTerm::getTaxonomyTerms().
	 *
	 * @type string $taxonomy        The taxonomy tree to display.
	 *                               Default: 'category'
	 * @type bool   $hierarchical    Whether to include terms that have non-empty descendants, even if 'hide_empty' is set to TRUE.
	 *                               Default: TRUE
	 * @type string $name            The select name attribute.
	 *                               Default: 'cn-cat'
	 * @type bool   $show_select_all Whether to render the $show_option_all option.
	 *                               Default: TRUE
	 * @type string $show_option_all A non-blank value causes the display of a link to the directory home page.
	 *                               Default: ''. The default is not to display a link.
	 *                               Accepts: Any valid string.
	 * @type bool   $show_count      Whether to display the category count.
	 *                               Default: FALSE
	 * @type bool   $hide_empty      Whether to display empty terms.
	 *                               Default: FALSE
	 * @type int    $depth           Controls how many levels in the hierarchy of categories that are to be included in the list.
	 *                               Default: 0
	 *                               Accepts: 0  - All categories and child categories.
	 *                                        -1 - All Categories displayed  flat, not showing the parent/child relationships.
	 *                                        1  - Show only top level/root parent categories.
	 *                                        n  - Value of n (int) specifies the depth (or level) to descend in displaying the categories.
	 * @type array  $parent_id
	 * @type array  $selected        The selected term IDs.
	 *                               Default: 0
	 * @type string $before          Content to render before the label and select.
	 *                               Default: ''
	 * @type string $after           Content to render after the label and select.
	 *                               Default: ''
	 * @type bool $return            Whether to return or echo the resulting HTML.
	 *                               Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function render( $atts = array() ) {

		$out = '';

		$defaults = array(
			'taxonomy'        => 'category',
			'hierarchical'    => true,
			'name'            => 'cn-cat',
			'show_select_all' => true,
			'show_option_all' => __( 'Select Category', 'connections' ),
			'show_count'      => false,
			'hide_empty'      => false,
			'depth'           => 0,
			'parent_id'       => array(),
			'selected'        => 0,
			'before'          => '',
			'after'           => '',
			'return'          => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$walker = new self();

		$walker->tree_type = $atts['taxonomy'];

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

				if ( 0 !== $term->parent ) {
					$term->parent = 0;
				}
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

		if ( ! empty( $terms ) ) {

			$out .= '<ul class="cn-' . esc_attr( $atts['taxonomy'] ) . '-radio-group">' . PHP_EOL;

			if ( $atts['show_select_all'] && $atts['show_option_all'] ) {

				/** This filter is documented in includes/template/class.template-walker-term-select.php */
				$show_option_all = apply_filters( 'cn_list_cats', $atts['show_option_all'] );
				$type            = esc_attr( $walker->tree_type );

				$out .= "<li id='cn-{$type}-0'>" . '<label><input value="0" type="radio" name="' . esc_attr( $atts['name'] ) . '" id="cn-in-' . $type . '-0"' .
						checked( in_array( 0, (array) $atts['selected'] ), true, false ) . ' /> ' .
						esc_html( $show_option_all ) . '</label>';

				$out .= '</li>' . PHP_EOL;
			}

			$out .= $walker->walk( $terms, $atts['depth'], $atts );

			$out .= '</ul>' . PHP_EOL;
		}

		$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] );

		if ( $atts['return'] ) {

			return $out;
		}

		// The radio group options are escaped as they are being built.
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 8.2.4
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Radio_Group::render().
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		$output .= str_repeat( "\t", $depth ) . '<ul class="cn-' . esc_attr( $args['taxonomy'] ) . '-children">' . PHP_EOL;
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @since 8.2.4
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Radio_Group::render().
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

		$output .= str_repeat( "\t", $depth ) . '</ul>' . PHP_EOL;
	}

	/**
	 * Start the element output.
	 *
	 * @since 8.2.4
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $term   The current term object.
	 * @param int    $depth  Depth of the term in reference to parent. Default 0.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Radio_Group::render().
	 * @param int    $id     ID of the current term.
	 */
	public function start_el( &$output, $term, $depth = 0, $args = array(), $id = 0 ) {

		$type = esc_attr( $this->tree_type );
		$name = esc_attr( $args['name'] );

		// Set the option SELECTED attribute if the category is one of the currently selected categories.
		$selected = in_array( $term->term_id, (array) $args['selected'] ) || in_array( $term->slug, (array) $args['selected'], true ) ? ' CHECKED ' : '';

		$output .= str_repeat( "\t", $depth );

		$output .= "<li id='cn-{$type}-{$term->term_id}'>" . '<label><input value="' . $term->term_id . '" type="radio" name="' . $name . '" id="cn-in-' . $type . '-' . $term->term_id . '"' .
				   $selected .
				   disabled( empty( $args['disabled'] ), false, false ) . ' /> ' .
				   esc_html( $term->name );

		if ( $args['show_count'] ) {

			$output .= '&nbsp;(' . number_format_i18n( $term->count ) . ')';
		}

		$output .= '</label>';

		$output .= '</li>' . PHP_EOL;
	}
}
