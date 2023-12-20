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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Connections_Directory\Form\Field;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;

/**
 * Class CN_Walker_Term_Select_List_Enhanced
 */
class CN_Walker_Term_Select_List_Enhanced extends Walker {

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
	 * Whether to close an open optgroup.
	 *
	 * @since 8.2.4
	 * @var bool
	 */
	private $close_group = false;

	/**
	 * Display or retrieve the HTML select list of terms.
	 *
	 * This is the Connections equivalent of {@see wp_dropdown_categories()} in WordPress core ../wp-includes/category-template.php
	 *
	 * @since 8.2.4
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *     NOTE: Additionally, all valid options as supported in {@see cnTerm::getTaxonomyTerms()}.
	 *
	 * @type string $taxonomy           The taxonomy tree to display.
	 *                                  Default: 'category'
	 * @type bool   $hierarchical       Whether to include terms that have non-empty descendants, even if 'hide_empty' is set to TRUE.
	 *                                  Default: TRUE
	 * @type string $type               The output type of the categories.
	 *                                  Default: select
	 *                                  Accepts: select || multiselect
	 * @type bool   $group              Whether to create option groups using the root parent as the group label.
	 *                                  Default: FALSE
	 * @type bool   $hide_if_empty      Whether to show the select if no terms are returned by term query.
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
	 * @type bool   $placeholder_option Whether to add a blank <option> item at the top of the list for Chosen/Select2
	 *                                  Default: FALSE
	 * @type bool   $show_select_all    Whether to render the $show_option_all option.
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
	 * @type bool   $show_count         Whether to display the category count.
	 *                                  Default: FALSE
	 * @type bool   $hide_empty         Whether to display empty terms.
	 *                                  Default: FALSE
	 * @type int    $depth              Controls how many levels in the hierarchy of categories that are to be included in the list.
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
	 * @type string $before             Content to be rendered before the label and select.
	 *                                  Default: ''
	 * @type string $after              Content to be rendered after the label and select.
	 *                                  Default: ''
	 * @type string $layout             Tokens which can be sued to control the order of the label and select.
	 *                                  Default: '%label%%field%'
	 *                                  Accepts: %label% %field%
	 * @type bool   $return             Whether to return or echo the resulting HTML.
	 *                                  Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function render( $atts = array() ) {

		$select = '';
		$out    = '';

		$defaults = array(
			'taxonomy'           => 'category',
			'hierarchical'       => true,
			'type'               => 'select',
			'group'              => false,
			'hide_if_empty'      => false,
			'name'               => 'cn-cat',
			'id'                 => '',
			'class'              => array( 'cn-category-select' ),
			'style'              => array(),
			'enhanced'           => true,
			'on_change'          => '',
			'tab_index'          => 0,
			'placeholder_option' => true,
			'show_select_all'    => true,
			'show_option_all'    => '',
			'show_option_none'   => '',
			'option_none_value'  => -1,
			'default'            => __( 'Select Category', 'connections' ), // This is the data-placeholder select attribute utilized by Chosen.
			'show_count'         => false,
			'hide_empty'         => false,
			'depth'              => 0,
			'parent_id'          => array(),
			'selected'           => 0,
			'label'              => '',
			'before'             => '',
			'after'              => '',
			'layout'             => '%label%%field%',
			'return'             => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( wp_is_mobile() ) {

			$atts['enhanced'] = false;
		}

		// The field parts to be searched for in $atts['layout'].
		$search = array( '%label%', '%field%' );

		// An array to store the replacement strings for the label and field.
		$replace = array();

		$walker = new self();

		$walker->tree_type = $atts['taxonomy'];

		if ( ! isset( $atts['pad_counts'] ) && $atts['show_count'] && $atts['hierarchical'] ) {

			// Padding the counts is ideal, but really, really, bloats the memory required.
			$atts['pad_counts'] = false;
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

		if ( ! $atts['hide_if_empty'] || ! empty( $terms ) ) {

			// $out .= PHP_EOL . "<select name='$name' id='$id' class='$class' $tab_index_attribute>" . PHP_EOL;

			// Add the 'cn-enhanced-select' class for the jQuery Chosen Plugin will enhance the dropdown.
			if ( $atts['enhanced'] ) {
				$atts['class'] = array_merge( (array) $atts['class'], array( 'cn-enhanced-select' ) );
			}

			// Create the field label, if supplied.
			$replace[] = ! empty( $atts['label'] ) ? cnHTML::label( array( 'for' => $atts['id'], 'label' => $atts['label'], 'return' => true ) ) : '';

			$css = _html::stringifyCSSAttributes( $atts['style'] );

			$select .= sprintf(
				'<select %1$s %2$s name="%3$s"%4$s%5$sdata-placeholder="%6$s"%7$s%8$s>' . PHP_EOL,
				empty( $atts['class'] ) ? '' : ' class="' . _escape::classNames( $atts['class'] ) . '"',
				empty( $atts['id'] ) ? '' : ' id="' . _escape::id( $atts['id'] ) . '"',
				'multiselect' == $atts['type'] ? esc_attr( $atts['name'] ) . '[]' : esc_attr( $atts['name'] ),
				empty( $atts['style'] ) ? '' : ' style="' . _escape::css( $css ) . '"',
				'multiselect' == $atts['type'] ? '' : ( empty( $atts['on_change'] ) ? '' : sprintf( ' onchange="%s" ', esc_js( $atts['on_change'] ) ) ),
				esc_attr( $atts['default'] ),
				'multiselect' == $atts['type'] ? ' MULTIPLE' : '',
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

			$placeholder = $walker->generatePlaceholder( $atts );

			if ( 0 < strlen( $placeholder ) ) {

				$select .= "\t{$placeholder}";
			}

			$selectAll = $walker->generateSelectAllOption( $atts );

			if ( 0 < strlen( $selectAll ) ) {

				$select .= "\t{$selectAll}";
			}

			$selectNone = $walker->generateSelectNoneOption( $atts );

			if ( 0 < strlen( $selectNone ) ) {

				$select .= "\t{$selectNone}";
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

				$select             .= "\t" . '</optgroup>' . PHP_EOL;
				$walker->close_group = false;
			}

			$select .= '</select>' . PHP_EOL;

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

		// The dropdown options are escaped as it is being built.
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * If a select enhancement library is being used such as Chosen or Select2,
	 * add the placeholder option to the top of the available options.
	 *
	 * Notes:
	 * - Ticket ID:561312
	 * - Ticket ID:565804
	 * - Ticket ID:591582
	 *
	 * @since 10.4.57
	 *
	 * @param array $atts The `$atts` passed to {@see CN_Walker_Term_Select_List_Enhanced::render()}.
	 *
	 * @return string
	 */
	private function generatePlaceholder( array $atts ): string {

		$html           = '';
		$addPlaceholder = _array::get( $atts, 'placeholder_option', true );
		$isEnhanced     = _array::get( $atts, 'enhanced', true );
		$defaultLabel   = _array::get( $atts, 'default', __( 'Select Category', 'connections' ) );
		$selected       = _array::get( $atts, 'selected', array() );
		$option         = Field\Option::create()->setValue( '' );

		if ( $isEnhanced || $addPlaceholder ) {

			if ( false === $isEnhanced ) {

				// Set the placeholder as the selected option when not filtering by category terms.
				$selected = is_array( $selected ) ? $selected : (array) $selected;

				if ( 0 === count( array_filter( $selected ) ) ) {

					$option->setChecked( true );
				}
			}

			if ( true === wp_is_mobile() ) {

				$option->setDisabled( true );
				$option->addAttribute( 'hidden', 'hidden' );
			}

			$optionLabel = $isEnhanced ? '' : $defaultLabel;
			$option->setText( $optionLabel );

			$html = $option->getHTML();
		}

		return $html;
	}

	/**
	 * Generate the HTML for the 'Select All' option.
	 *
	 * When doing a select all, set the show all option value as selected.
	 *
	 * NOTE: This is only done when the select is not being enhanced by a library such as Chosen or Select2.
	 *       In that case the placeholder option should be the default selected item.
	 *
	 * @since 10.4.60
	 *
	 * @param array $atts The `$atts` passed to {@see CN_Walker_Term_Select_List_Enhanced::render()}.
	 *
	 * @return string
	 */
	private function generateSelectAllOption( array $atts ): string {

		$html   = '';
		$label  = _array::get( $atts, 'show_option_all', '' );
		$render = _array::get( $atts, 'show_select_all', true );

		/** This filter is documented in includes/template/class.template-walker-term-select.php */
		$label = apply_filters( 'cn_list_cats', $label );

		// If `show_option_all` is true AND `show_option_all` is not empty, generate the 'Select All' option.
		if ( true === $render && ! empty( $label ) ) {

			$isEnhanced = _array::get( $atts, 'enhanced', true );
			$selected   = _array::get( $atts, 'selected', '0' );
			$isChecked  = ! $isEnhanced && is_numeric( $selected ) && '0' === strval( $selected );
			$html       = Field\Option::create()
									  ->setValue( '0' )
									  ->setChecked( $isChecked )
									  ->setText( $label )
									  ->getHTML();
		}

		return $html;
	}

	/**
	 * Generate the HTML for the 'Select None' option.
	 *
	 * @since 10.4.60
	 *
	 * @param array $atts The `$atts` passed to {@see CN_Walker_Term_Select_List_Enhanced::render()}.
	 *
	 * @return string
	 */
	private function generateSelectNoneOption( array $atts ): string {

		$html   = '';
		$label  = _array::get( $atts, 'show_option_none', '' );
		$render = 0 < strlen( $label );

		if ( true === $render ) {

			/** This filter is documented in includes/template/class.template-walker-term-select.php */
			$label     = apply_filters( 'cn_list_cats', $label );
			$selected  = _array::get( $atts, 'selected', 0 );
			$value     = _array::get( $atts, 'option_none_value', -1 );
			$isChecked = $selected === $value;
			$html      = Field\Option::create()
									 ->setValue( $value )
									 ->setChecked( $isChecked )
									 ->setText( $label )
									 ->getHTML();
		}

		return $html;
	}

	/**
	 * Sets @see CN_Walker_Term_Select_List_Chosen::close_group to true if grouping by parent category.
	 *
	 * @since 8.2.4
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category in reference to parent. Default 0.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Select_List::render().
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		if ( $args['group'] && $this->has_children ) {

			$this->close_group = true;
		}
	}

	/**
	 * Sets @see CN_Walker_Term_Select_List_Chosen::close_group to false if grouping by parent category.
	 *
	 * @since 8.2.4
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category in reference to parent. Default 0.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Select_List::render().
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

		if ( $args['group'] && $this->close_group && 0 === $depth ) {

			$output           .= "\t" . '</optgroup>' . PHP_EOL;
			$this->close_group = false;
		}
	}

	/**
	 * Start the element output.
	 *
	 * @since 8.2.4
	 *
	 * @param string $output            Passed by reference. Used to append additional content.
	 * @param object $data_object       Category data object.
	 * @param int    $depth             Depth of category in reference to parent's. Default 0.
	 * @param array  $args              An array of arguments. @see CN_Walker_Term_Select_List::render().
	 * @param int    $current_object_id ID of the current category.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {

		if ( ! $args['group'] ) {

			$this->do_el( $output, $data_object, $depth, $args );

		} elseif ( $args['group'] && 0 === $depth && $this->has_children ) {

			$output .= sprintf( "\t" . '<optgroup label="%1$s">' . PHP_EOL, esc_attr( $data_object->name ) );

		} elseif ( $args['group'] && 0 < $depth ) {

			$this->do_el( $output, $data_object, $depth, $args );
		}
	}

	/**
	 * Render the select option for the current term.
	 *
	 * @internal
	 * @since 8.2.4
	 *
	 * @param string $out   Passed by reference. Used to append additional content.
	 * @param object $term  Category data object.
	 * @param int    $depth Depth of category in reference to parent. Default 0.
	 * @param array  $args  An array of arguments. {@see CN_Walker_Term_Select_List::render()}.
	 */
	private function do_el( &$out, $term, $depth, $args ) {

		// The padding in px to indent descendant categories. The 7px is the default pad applied in the CSS which must be taken into account.
		$pad     = ( $depth > 0 ) ? $depth * 12 + 7 : 7;
		$padding = is_rtl() ? 'padding-right' : 'padding-left';

		$selected   = _array::get( $args, 'selected', array() );
		$selected   = is_array( $selected ) ? $selected : (array) $selected;
		$isSelected = in_array( $term->term_id, $selected ) || in_array( $term->slug, $selected, true );

		$option = Field\Option::create()->setValue( $term->term_id );

		$option->addClass( "cn-term-level-{$depth}" );
		$option->css( $padding, "{$pad}px !important" );
		$option->setChecked( $isSelected );

		$nbsp  = $args['enhanced'] ? '' : str_repeat( '&nbsp;', $depth * 3 );
		$name  = esc_html( apply_filters( 'cn_list_cats', $term->name, $term ) );
		$count = $args['show_count'] ? '&nbsp;(' . number_format_i18n( $term->count ) . ')' : '';

		$option->setText( $nbsp . $name . $count );

		$out .= $option->getHTML();
	}
}
