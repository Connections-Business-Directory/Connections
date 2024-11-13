<?php
/**
 * Generate a taxonomy term radio group field.
 *
 * @since      10.4.66
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
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_sanitize;
use Connections_Directory\Walker\Term_Radio_Group_Inputs;

/**
 * Class Term_Radio_Group
 *
 * @package Connections_Directory\Form\Field
 */
class Term_Radio_Group extends Group {

	/**
	 * An array of Radio Input fields.
	 *
	 * @since 10.4.66
	 *
	 * @var Radio[]|string[]
	 */
	protected $inputs = array();

	/**
	 * The select option fields attributes.
	 *
	 * @see self::setFieldOptions()
	 *
	 * @since 10.4.66
	 *
	 * @var array{
	 *      container: array{
	 *          class: string,
	 *          tag: string,
	 *      },
	 *      depth: int,
	 *      label: array{
	 *          class: string
	 *      },
	 *      pad_count: bool,
	 *      parent: array{
	 *          class: string
	 *      },
	 *      show_count: bool,
	 *      value_field: string,
	 *  }
	 */
	protected $fieldOptions = array(
		'container'   => array(
			'class' => 'cn-term-radio-group',
			'tag'   => 'div',
		),
		'depth'       => 0,
		'label'       => array(
			'class' => '',
		),
		'parent'      => array(
			'class' => '',
		),
		'show_count'  => false,
		'value_field' => 'term_id',
	);

	/**
	 * The taxonomy terms to list as select options.
	 *
	 * @since 10.4.66
	 *
	 * @var string
	 */
	private $taxonomy;

	/**
	 * The taxonomy term query parameters.
	 *
	 * @since 10.4.66
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
	 * @since 10.4.66
	 *
	 * @param array $attributes The field attributes.
	 */
	public function __construct( array $attributes = array() ) {

		$this->taxonomy = _array::get( $attributes, 'taxonomy', 'category' );

		$taxonomy = Taxonomy\Registry::get()->getTaxonomy( $this->taxonomy );

		if ( $taxonomy instanceof Taxonomy ) {

			$this->setName( $taxonomy->getQueryVar() );

			$this->fieldOptions['value_field']     = 'slug';
			$this->queryParameters['hierarchical'] = $taxonomy->isHierarchical();
		}

		$this->fieldOptions['tags'] = $this->getContainerTags();

		parent::__construct( $attributes );
	}

	/**
	 * Set the radio group field input attributes.
	 *
	 * @since 10.4.66
	 *
	 * @phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamName
	 * @param array{
	 *     container: array{
	 *         class: string,
	 *         tag: string,
	 *     },
	 *     depth: int,
	 *     label: array{
	 *         class: string
	 *     },
	 *     pad_count: bool,
	 *     parent: array{
	 *         class: string
	 *     },
	 *     show_count: bool,
	 *     value_field: string,
	 * } $options {
	 *     Optional. An array of arguments.
	 *
	 *     @type array  $container   The container attributes.
	 *                               Default: `class`:'cn-term-radio-group'
	 *                                        `tag`:'div'
	 *     @type int    $depth       Controls how many levels in the hierarchy of terms that are to be included.
	 *                               Default: `0`
	 *                               Accepts: `0` — All terms and child terms.
	 *                               `-1` = All terms displayed flat, not showing the parent/child relationships.
	 *                               `1`  = Show only top level/root parent categories.
	 *                               `n`  = Value of n (int) specifies the depth (or level) to descend in displaying the terms.
	 *     @type array  $label       The radio label element class name.
	 *                               Default: `` (empty string – no class attribute applied to the label)
	 *     @type bool   $pad_count   Whether to pad the quantity of a term's children in the quantity of each term's "count" object
	 *           property. Default: `false`.
	 *     @type array  $parent      The term parent container element attributes.
	 *                               Default: `class`:'cn-{taxonomy}-children'
	 *     @type bool   $show_count  Whether to display the term count.
	 *                               Default: `false`
	 *     @type string $value_field Term field that should be used to populate the 'value' attribute of the option elements.
	 *                               Accepts: Valid term field: 'term_id', 'name', 'slug', 'term_group', 'term_taxonomy_id', 'taxonomy',
	 *                               'description', 'parent', 'count'. Default: `term_id`
	 * }
	 *
	 * @return self
	 */
	public function setFieldOptions( array $options = array() ): self {

		$options = _parse::parameters( $options, $this->fieldOptions );

		$options['depth']      = _sanitize::integer( $options['depth'] );
		$options['show_count'] = _format::toBoolean( $options['show_count'] );

		$this->fieldOptions = $options;

		return $this;
	}

	/**
	 * Get the term query parameters.
	 *
	 * @since 10.4.66
	 *
	 * @return array
	 */
	public function getQueryParameters(): array {

		return $this->queryParameters;
	}

	/**
	 * Set the taxonomy term query parameters.
	 *
	 * @see cnTerm::getTaxonomyTerms()
	 *
	 * @since 10.4.66
	 *
	 * @param array $parameters The query parameters.
	 *                          See cnTerm::getTaxonomyTerms() for accepted parameters.
	 *
	 * @return self
	 */
	public function setQueryParameters( array $parameters = array() ): self {

		$parameters = _parse::parameters( $parameters, $this->queryParameters, false );

		$parameters['hide_empty']   = _format::toBoolean( $parameters['hide_empty'] );
		$parameters['hierarchical'] = _format::toBoolean( $parameters['hierarchical'] );

		$this->queryParameters = $parameters;

		return $this;
	}

	/**
	 * Get the taxonomy terms.
	 *
	 * @since 10.4.66
	 *
	 * @return array
	 */
	protected function getTerms(): array {

		$parameters = $this->getQueryParameters();

		if ( isset( $this->fieldOptions['pad_counts'] ) ) {

			$parameters['pad_counts'] = $this->fieldOptions['pad_counts'];

		} elseif ( ! isset( $this->fieldOptions['pad_counts'] )
				   && $this->fieldOptions['show_count']
				   && $this->queryParameters['hierarchical'] ) {

			$parameters['pad_counts'] = true;
		}

		// Avoid clashes with the `name` param of getTaxonomyTerms().
		unset( $parameters['name'] );

		$terms = cnTerm::getTaxonomyTerms( $this->taxonomy, $parameters );

		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Prepare the individual radio inputs by setting their properties supplied to the radio group
	 * because they are to be applied to the field level and not the radio group field container HTML.
	 *
	 * @since 10.4.66
	 */
	protected function prepareInputs() {

		foreach ( $this->inputs as $field ) {

			if ( ! $field instanceof Radio ) {
				continue;
			}

			$field->setPrefix( $this->getPrefix() );
			$field->addClass( $this->class );
			$field->setId( "cn-in-{$this->taxonomy}-{$field->getValue()}" );
			$field->setName( $this->getName() );
			$field->css( $this->css );
			$field->addData( $this->data );
			$field->setDisabled( $this->isDisabled() );
			$field->setReadOnly( $this->isReadOnly() );
			$field->setRequired( $this->isRequired() );
			$field->maybeIsChecked( $this->getValue() );
		}
	}

	/**
	 * Add a Radio Input field and start/end level html elements.
	 *
	 * @since 10.4.66
	 *
	 * @param Radio|string $input A Radio field.
	 *
	 * @return static
	 */
	public function addInput( $input ): self {

		$this->inputs[] = $input;

		return $this;
	}

	/**
	 * Whether the radio group has had inputs added.
	 *
	 * @since 10.4.66
	 *
	 * @return bool
	 */
	public function hasInputs(): bool {

		return 0 < count( $this->inputs );
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4.66
	 *
	 * @return string
	 */
	public function getFieldHTML(): string {

		$terms = $this->getTerms();

		$walker = new Term_Radio_Group_Inputs();

		if ( $this->queryParameters['hierarchical'] ) {
			$depth = $this->fieldOptions['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}

		$args             = $this->fieldOptions;
		$args['tags']     = $this->getContainerTags();
		$args['taxonomy'] = $this->taxonomy;

		$walker->walk( $terms, $depth, $args );

		foreach ( $walker->getInputs() as $input ) {

			$this->addInput( $input );
		}

		if ( ! $this->hasInputs() ) {

			return '';
		}

		$this->prepareInputs();

		/*
		 * NOTE: Radio inputs have the __toString() magic method,
		 * so they can be imploded since they are stored as an array of Radio inputs and strings.
		 */
		$html = implode( '', $this->inputs );

		$classes    = _array::get( $this->fieldOptions, 'container.class' );
		$tag        = _array::get( $this->fieldOptions, 'container.tag' );
		$classNames = _escape::classNames( $classes );
		$tag        = _escape::tagName( $tag );

		return "<{$tag} class=\"{$classNames}\">$html</{$tag}>";
	}
}
