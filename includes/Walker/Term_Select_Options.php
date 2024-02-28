<?php
/**
 * Term walker specifically for the `Select_Term` field.
 *
 * @since      10.4.64
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
use Walker;

/**
 * Class CN_Walker_Term_Select_List
 */
class Term_Select_Options extends Walker {

	/**
	 * Database fields to use.
	 *
	 * @since 10.4.64
	 * @todo  Decouple this
	 * @var array{ id: string, parent: string }
	 */
	public $db_fields = array(
		'parent' => 'parent',
		'id'     => 'term_id',
	);

	/**
	 * The term select option fields.
	 *
	 * @since 10.4.64
	 *
	 * @var Field\Option[]
	 */
	private $options = array();

	/**
	 * Return the term select options fields.
	 *
	 * @since 10.4.64
	 *
	 * @return Field\Option[]
	 */
	public function getOptions(): array {

		return $this->options;
	}

	/**
	 * Starts the element output.
	 *
	 * @since 10.4.64
	 *
	 * @param string $output            Used to append additional content (passed by reference).
	 * @param Term   $data_object       Category data object.
	 * @param int    $depth             Depth of category. Used for padding.
	 * @param array  $args              Uses 'selected', 'show_count', and 'value_field' keys, if they exist.
	 *                                   See wp_dropdown_categories().
	 * @param int    $current_object_id Optional. ID of the current category. Default 0.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {

		$count = '';
		$pad   = str_repeat( '&nbsp;', $depth * 3 );
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

		$this->options[] = Field\Option::create()
									   ->addClass( "level-{$depth}" )
									   ->setValue( $term->{$value_field} )
									   ->setText( $pad . $name . $count );
	}
}
