<?php
/**
 * Generate a taxonomy term select field.
 *
 * @since 10.4.64
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Form\Field
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use cnTerm;
use Connections_Directory\Taxonomy;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_sanitize;
use Connections_Directory\Walker\Term_Select_Options;

/**
 * Class Select_Term
 *
 * @package Connections_Directory\Form\Field
 */
final class Select_Term extends Select {

	/**
	 * The default select attributes.
	 *
	 * @since 10.4.64
	 *
	 * @var array
	 */
	protected $attributes = array(
		'name'             => '',
		'id'               => '',
		'class'            => '',
		'tab_index'        => 0,
		'required'         => false,
		'aria_describedby' => '',
	);

	/**
	 * The select option fields attributes.
	 *
	 * @since 10.4.64
	 *
	 * @var array
	 */
	protected $fieldOptions = array(
		// Text to display for showing all terms.
		'show_option_all'   => '',
		// Text to display for showing no categories.
		'show_option_none'  => '',
		// Value to use when no category is selected.
		'option_none_value' => -1,
		// Whether to include post counts. Accepts 0, 1, or their bool equivalents.
		'show_count'        => false,
		// Whether to traverse the taxonomy hierarchy. Accepts 0, 1, or their bool equivalents.
		'hierarchical'      => false,
		// Maximum depth.
		'depth'             => 0,
		// Do not generate HTML if no terms are returned when doing the term query.
		'hide_if_empty'     => true,
		// Term field that should be used to populate the 'value' attribute of the option elements.
		// Accepts any valid term field: 'term_id', 'name', 'slug', 'term_group', 'term_taxonomy_id', 'taxonomy', 'description', 'parent', 'count'.
		// Default 'term_id'.
		'value_field'       => 'term_id',
	);

	/**
	 * The taxonomy terms to list as select options.
	 *
	 * @since 10.4.64
	 *
	 * @var string
	 */
	private $taxonomy;

	/**
	 * The taxonomy term query parameters.
	 *
	 * @since 10.4.64
	 *
	 * @var array
	 */
	private $queryParameters = array(
		'orderby'      => 'name',
		'order'        => 'ASC',
		'hide_empty'   => false,
		'child_of'     => 0,
		'exclude'      => '', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'hierarchical' => false,
	);

	/**
	 * Field constructor.
	 *
	 * @param array $attributes The field attributes.
	 */
	public function __construct( array $attributes = array() ) {

		$this->taxonomy = _array::get( $attributes, 'taxonomy', 'category' );

		$taxonomy = Taxonomy\Registry::get()->getTaxonomy( $this->taxonomy );

		if ( $taxonomy instanceof Taxonomy ) {
			$this->attributes['name']              = $taxonomy->getQueryVar();
			$this->fieldOptions['show_option_all'] = $taxonomy->getLabels()->all_items;
			$this->fieldOptions['hierarchical']    = $taxonomy->isHierarchical();
			$this->fieldOptions['value_field']     = 'slug';
			$this->queryParameters['hierarchical'] = $taxonomy->isHierarchical();
		}

		$this->setFieldOptions();
		$this->setQueryParameters();

		$this->attributes = $this->parseAttributes( $attributes );

		parent::__construct( $this->attributes );
	}

	/**
	 * Parse and prepare the attributes.
	 *
	 * @param array $attributes The attributes.
	 *
	 * @return array
	 */
	protected function parseAttributes( array $attributes ): array {

		$attributes = _parse::parameters( $attributes, $this->attributes );

		$attributes['tab_index'] = _sanitize::integer( $attributes['tab_index'] );

		if ( 0 < strlen( $attributes['aria_describedby'] ) ) {

			$this->addAttribute( 'aria-describedby', $attributes['aria_describedby'] );
		}

		if ( 0 < $attributes['tab_index'] ) {

			$this->addAttribute( 'tabindex', $attributes['tab_index'] );
		}

		_array::forget( $attributes, 'aria_describedby' );
		_array::forget( $attributes, 'tab_index' );

		return $attributes;
	}

	/**
	 * Set the select field options attributes.
	 *
	 * @since 10.4.64
	 *
	 * @param array $options The option field options.
	 *
	 * @return self
	 */
	public function setFieldOptions( array $options = array() ): self {

		$options = _parse::parameters( $options, $this->fieldOptions, false );

		$options['show_count']    = _format::toBoolean( $options['show_count'] );
		$options['hierarchical']  = _format::toBoolean( $options['hierarchical'] );
		$options['hide_if_empty'] = _format::toBoolean( $options['hide_if_empty'] );

		$this->fieldOptions = $options;

		return $this;
	}

	/**
	 * Get the term query parameters.
	 *
	 * @since 10.4.64
	 *
	 * @return array
	 */
	public function getQueryParameters(): array {

		return $this->queryParameters;
	}

	/**
	 * Set the taxonomy term query parameters.
	 *
	 * @since 10.4.64
	 *
	 * @param array $parameters The query parameters.
	 */
	public function setQueryParameters( array $parameters = array() ) {

		$parameters = _parse::parameters( $parameters, $this->queryParameters, false );

		$parameters['hide_empty']   = _format::toBoolean( $parameters['hide_empty'] );
		$parameters['hierarchical'] = _format::toBoolean( $parameters['hierarchical'] );

		$this->queryParameters = $parameters;
	}

	/**
	 * Get the taxonomy terms.
	 *
	 * @since 10.4.64
	 *
	 * @return array
	 */
	protected function getTerms(): array {

		$parameters = $this->getQueryParameters();

		if ( isset( $this->fieldOptions['pad_counts'] ) ) {

			$parameters['pad_counts'] = $this->fieldOptions['pad_counts'];

		} elseif ( ! isset( $this->fieldOptions['pad_counts'] ) && $this->fieldOptions['show_count'] && $this->fieldOptions['hierarchical'] ) {

			$parameters['pad_counts'] = true;
		}

		// Avoid clashes with the `name` param of getTaxonomyTerms().
		unset( $parameters['name'] );

		$terms = cnTerm::getTaxonomyTerms( $this->taxonomy, $parameters );

		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4.64
	 *
	 * @return string
	 */
	public function getFieldHTML(): string {

		$terms = $this->getTerms();

		if ( 0 === count( $terms ) && ! $this->fieldOptions['hide_if_empty'] ) {

			$this->maybeAddOptionNone();
			$this->setValue( $this->fieldOptions['option_none_value'] );

		} elseif ( 0 < count( $terms ) ) {

			$this->maybeAddOptionNone();
			$this->maybeAddOptionShowAll();
		}

		$walker = new Term_Select_Options();

		if ( $this->fieldOptions['hierarchical'] ) {
			$depth = $this->fieldOptions['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}
		$walker->walk( $terms, $depth, $this->fieldOptions );

		foreach ( $walker->getOptions() as $option ) {

			$this->addOption( $option );
		}

		if ( ! $this->hasOptions() ) {

			return '';
		}

		// $this->addClass( "cbd-field--select_{$this->taxonomy}_term" );

		return parent::getFieldHTML();
	}

	/**
	 * Maybe add the "None" option.
	 *
	 * @since 10.4.64
	 */
	protected function maybeAddOptionNone() {

		if ( 0 < strlen( $this->fieldOptions['show_option_none'] ) ) {

			/** This filter is documented in includes/template/class.template-walker-term-select.php */
			$label = apply_filters( 'cn_list_cats', $this->fieldOptions['show_option_none'] );

			$option = Option::create()
							->setValue( $this->fieldOptions['option_none_value'] )
							->setText( $label );

			$this->addOption( $option );
		}
	}

	/**
	 * Maybe add the "Show All" option.
	 *
	 * @since 10.4.64
	 */
	protected function maybeAddOptionShowAll() {

		if ( 0 < strlen( $this->fieldOptions['show_option_all'] ) ) {

			/** This filter is documented in includes/template/class.template-walker-term-select.php */
			$label = apply_filters( 'cn_list_cats', $this->fieldOptions['show_option_all'] );

			$option = Option::create()
							->setValue( 0 )
							->setText( $label );

			$this->addOption( $option );
		}
	}
}
