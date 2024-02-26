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

// Exit if accessed directly.
use Connections_Directory\Taxonomy\Registry;

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
	 * @since 8.2
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @since 8.2
	 * @todo  Decouple this
	 * @var array
	 */
	public $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	/**
	 * Render a checklist of terms.
	 *
	 * This is the Connections equivalent of @see wp_terms_checklist() in WordPress core ../wp-admin/wp-includes/template.php
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *     NOTE: Additionally, all valid options as supported in @see cnTerm::getTaxonomyTerms().
	 *
	 * @type bool   $show_count        Whether to display the category count.
	 *                                 Default: FALSE
	 * @type string $name              The select name attribute.
	 *                                 Default: 'cat'
	 * @type int    $depth             Controls how many levels in the hierarchy of categories that are to be included in the list.
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
	 * @type bool   $echo              Whether to echo the HTML.
	 *                                 Default: true
	 * }
	 *
	 * @return string
	 */
	public static function render( $atts = array() ) {

		$out = '';

		/*
		 * The `return` parameter was removed. It was a poor parameter to have in the first place.
		 * This "converts" it to the `echo` parameter which was what the `return` parameter was intended to be.
		 */
		if ( array_key_exists( 'return', $atts ) ) {

			$atts['echo'] = ! (bool) $atts['return'];
		}

		$defaults = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'show_count' => false,
			'hide_empty' => false,
			'name'       => 'entry_category',
			'depth'      => 0,
			'taxonomy'   => 'category',
			'selected'   => 0,
			'echo'       => true,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! is_array( $atts['selected'] ) ) {

			$atts['selected'] = array( absint( $atts['selected'] ) );

		} else {

			array_walk( $atts['selected'], 'absint' );
		}

		$walker = new self();

		$walker->tree_type = $atts['taxonomy'];

		$taxonomy = Registry::get()->getTaxonomy( $atts['taxonomy'] );

		if ( false !== $taxonomy ) {

			// @todo The is_admin() check feel like a hack, revisit this. This should be set when calling the walker, not when rendering it.
			$atts['disabled'] = is_admin() && ! current_user_can( $taxonomy->getCapabilities()->assign_terms );
		}

		// Reset the name attribute so the results from cnTerm::getTaxonomyTerms() are not limited by the select attribute name.
		$terms = cnTerm::getTaxonomyTerms( $atts['taxonomy'], array_merge( $atts, array( 'name' => '' ) ) );

		if ( ! empty( $terms ) ) {

			$out .= '<ul id="' . esc_attr( $atts['taxonomy'] ) . 'checklist" class="categorychecklist form-no-clear">';

				$out .= $walker->walk( $terms, $atts['depth'], $atts );

			$out .= '</ul>';
		}

		if ( true === $atts['echo'] ) {

			// The checklist is escaped as it is being built.
			echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $out;
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 8.2
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Check_List::render().
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		$output .= str_repeat( "\t", $depth ) . '<ul class="children cn-cat-children">' . PHP_EOL;
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @since 8.2
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Check_List::render().
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

		$output .= str_repeat( "\t", $depth ) . '</ul>' . PHP_EOL;
	}

	/**
	 * Start the element output.
	 *
	 * @since 8.2
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $term   The current term object.
	 * @param int    $depth  Depth of the term in reference to parent. Default 0.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Check_List::render().
	 * @param int    $id     ID of the current term.
	 */
	public function start_el( &$output, $term, $depth = 0, $args = array(), $id = 0 ) {

		$type = esc_attr( $this->tree_type );
		$name = esc_attr( $args['name'] );

		$output .= PHP_EOL . "<li id='{$type}-{$term->term_id}'>" . '<label class="selectit"><input value="' . $term->term_id . '" type="checkbox" name="' . $name . '[]" id="cn-in-' . $type . '-' . $term->term_id . '"' .
				   checked( in_array( $term->term_id, $args['selected'] ), true, false ) .
				   disabled( empty( $args['disabled'] ), false, false ) . ' /> ' .
				   esc_html( $term->name ) . '</label>';

		if ( $args['show_count'] ) {

			$output .= '&nbsp;&nbsp;(' . number_format_i18n( $term->count ) . ')';
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @since 8.2
	 *
	 * @param string $out      Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parent. Default 0.
	 * @param array  $args     An array of arguments. @see CN_Walker_Term_Check_List::render().
	 */
	public function end_el( &$out, $category, $depth = 0, $args = array() ) {

		$out .= '</li>' . PHP_EOL;
	}
}
