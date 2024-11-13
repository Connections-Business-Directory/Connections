<?php
/**
 * Term walker specifically for the `Term_Radio_Group` field.
 *
 * @since      10.4.66
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Walker
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Walker;

use Connections_Directory\Form\Field;
use Connections_Directory\Taxonomy\Term;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Walker;

/**
 * Class Term_Radio_Group_Inputs
 *
 * @package Connections_Directory\Walker
 */
class Term_Radio_Group_Inputs extends Walker {

	/**
	 * Database fields to use.
	 *
	 * @since 10.4.66
	 * @todo  Decouple this
	 * @var array{ id: string, parent: string }
	 */
	public $db_fields = array(
		'parent' => 'parent',
		'id'     => 'term_id',
	);

	/**
	 * The term radio group input fields and start/end level html elements.
	 *
	 * @since 10.4.66
	 *
	 * @var Field\Radio[]|string[]
	 */
	private $inputs = array();

	/**
	 * Return the radio group input fields and start/end level html elements.
	 *
	 * @since 10.4.66
	 *
	 * @return Field\Radio[]|string[]
	 */
	public function getInputs(): array {

		return $this->inputs;
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 10.4.66
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		$attributes = array();
		$classes    = _array::get( $args, 'parent.class', array( "cn-{$args['taxonomy']}-children" ) );
		$tag        = _array::get( $args, 'tags.parent', 'ul' );

		$attributes['class'] = _escape::classNames( $classes );
		$tag                 = _escape::tagName( $tag );

		$pieces = array(
			"<{$tag}",
			_html::stringifyAttributes( $attributes ),
		);

		$this->inputs[] = implode( ' ', array_filter( $pieces ) ) . '>';
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @since 10.4.66
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of terms. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see CN_Walker_Term_Check_List::render().
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

		$tag = _array::get( $args, 'tags.parent', 'ul' );

		$this->inputs[] = "</{$tag}>";
	}

	/**
	 * Starts the element output.
	 *
	 * @since 10.4.66
	 *
	 * @param string $output            Used to append additional content (passed by reference).
	 * @param Term   $data_object       Term data object.
	 * @param int    $depth             Depth of term. Used for padding.
	 * @param array  $args              Uses 'show_count', and 'value_field' keys, if they exist.
	 * @param int    $current_object_id Optional. ID of the current term. Default 0.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {

		$input = Field\Radio::create();

		$count = '';
		$tag   = _array::get( $args, 'tags.child', 'ul' );
		$term  = $data_object;

		/** This filter is documented in includes/template/class.template-walker-term-select.php */
		$name = apply_filters( 'cn_list_cats', $term->name, $term );

		if ( isset( $args['value_field'] ) && isset( $term->{$args['value_field']} ) ) {

			$value_field = $args['value_field'];

		} else {

			$value_field = 'term_id';
		}

		if ( $args['show_count'] ) {
			$count = '&nbsp;&nbsp;(' . number_format_i18n( $term->count ) . ')';
		}

		$input->addClass( "level-{$depth}" )
			  ->setValue( $term->{$value_field} )
			  ->addLabel(
				  Field\Label::create()
							 ->addClass( _array::get( $args, 'label.class', '' ) )
							 ->text( $name . $count ),
				  'implicit/after'
			  )
			  ->prepend( '<' . _escape::tagName( $tag ) . '>' );

		$this->inputs[] = $input;
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * The $args parameter holds additional values that may be used with the child class methods.
	 *
	 * @since 10.4.66
	 *
	 * @param string $output      Used to append additional content (passed by reference).
	 * @param object $data_object The data object.
	 * @param int    $depth       Depth of the item.
	 * @param array  $args        An array of additional arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = array() ) {

		$tag = _array::get( $args, 'tags.child', 'ul' );
		$tag = _escape::tagName( $tag );

		$this->inputs[] = "</{$tag}>";
	}
}
