<?php

/**
 * Class for working with a category object.
 *
 * @package     Connections
 * @subpackage  Category
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnCategory {
	private $id;
	private $name;
	private $slug;
	private $termGroup;
	private $taxonomy;
	private $description;
	private $parent;
	private $count;
	private $children;

	/**
	 * The cnFormmatting class.
	 * @var (object)
	 */
	private $format;

	/**
	 * The cnValidate class.
	 * @var (object)
	 */
	private $validate;

	function __construct( $data = NULL ) {
		if ( isset( $data ) ) {
			if ( isset( $data->term_id ) ) $this->id = $data->term_id;
			if ( isset( $data->name ) ) $this->name = $data->name;
			if ( isset( $data->slug ) ) $this->slug = $data->slug;
			if ( isset( $data->term_group ) ) $this->termGroup = $data->term_group;
			if ( isset( $data->taxonomy ) ) $this->taxonomy = $data->taxonomy;
			if ( isset( $data->description ) ) $this->description = $data->description;
			if ( isset( $data->parent ) ) $this->parent = $data->parent;
			if ( isset( $data->count ) ) $this->count = $data->count;
			if ( isset( $data->children ) ) $this->children = $data->children;
		}

		// Load the validation class.
		$this->validate = new cnValidate();

		// Load the formatting class for sanitizing the get methods.
		$this->format = new cnFormatting();
	}

	/**
	 * Returns $children.
	 *
	 * @see cnCategory::$children
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Sets $children.
	 *
	 * @param object  $children
	 * @see cnCategory::$children
	 */
	public function setChildren( $children ) {
		$this->children = $children;
	}

	/**
	 * Returns $count.
	 *
	 * @see cnCategory::$count
	 */
	public function getCount() {
		return $this->count;
	}

	/**
	 * Sets $count.
	 *
	 * @param object  $count
	 * @see cnCategory::$count
	 */
	public function setCount( $count ) {
		$this->count = $count;
	}

	/**
	 * Returns $description.
	 *
	 * @see cnCategory::$description
	 */
	public function getDescription() {
		return $this->format->sanitizeString( $this->description, TRUE );
	}

	/**
	 * Sets $description.
	 *
	 * @param object  $description
	 * @see cnCategory::$description
	 */
	public function setDescription( $description ) {
		$this->description = $description;
	}

	/**
	 * Echo or returns the category description.
	 *
	 * Registers the global $wp_embed because the run_shortcode method needs
	 * to run before the do_shortcode function for the [embed] shortcode to fire.
	 *
	 * Filters:
	 *   cn_output_default_atts_cat_desc
	 *
	 * @access public
	 * @since 0.7.8
	 * @uses apply_filters()
	 * @uses run_shortcode()
	 * @uses do_shortcode()
	 * @param array $atts [optional]
	 * @return (string)
	 */
	public function getDescriptionBlock( $atts = array() ) {
		global $wp_embed;

		$defaults = array(
			'container_tag' => 'div',
			'before'        => '',
			'after'         => '',
			'return'        => FALSE
		);

		$defaults = apply_filters( 'cn_output_default_atts_cat_desc' , $defaults );

		$atts = $this->validate->attributesArray( $defaults, $atts );

		$out = __( $wp_embed->run_shortcode( $this->getDescription() ) );

		$out = do_shortcode( $out );

		$out = sprintf( '<%1$s class="cn-cat-description">%2$s</%1$s>',
				$atts['container_tag'],
				$out
			);

		if ( $atts['return'] ) return ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
		echo ( "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) ) . $out . ( ( empty( $atts['after'] ) ? '' : $atts['after'] ) ) . "\n";
	}

	/**
	 * Create excerpt from the supplied text. Default is the description.
	 *
	 * Filters:
	 *   cn_cat_excerpt_length => change the default excerpt length of 55 words.
	 *   cn_cat_excerpt_more  => change the default more string of &hellip;
	 *   cn_trim_cat_excerpt  => change returned string
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (string)  $atts [optional]
	 * @param (string)  $text [optional]
	 * @return (string)
	 */
	public function getExcerpt( $atts = array(), $text = NULL ) {

		$defaults = array(
			'length' => apply_filters( 'cn_cat_excerpt_length', 55 ),
			'more'   => apply_filters( 'cn_cat_excerpt_more', '&hellip;' )
		);

		$atts = $this->validate->attributesArray( $defaults, $atts );

		$text = empty( $text ) ? $this->getDescription() : $this->format->sanitizeString( $text, FALSE );

		$words = preg_split( "/[\n\r\t ]+/", $text, $atts['length'] + 1, PREG_SPLIT_NO_EMPTY );

		if ( count( $words ) > $atts['length'] ) {

			array_pop( $words );
			$text = implode( ' ', $words ) . $atts['more'];

		} else {

			$text = implode( ' ', $words );
		}

		return apply_filters( 'cn_trim_cat_excerpt', $text );
	}

	/**
	 * Returns $id.
	 *
	 * @see cnCategory::$id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Sets $id.
	 *
	 * @param object  $id
	 * @see cnCategory::$id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns $name.
	 *
	 * @see cnCategory::$name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets $name.
	 *
	 * @param object  $name
	 * @see cnCategory::$name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * Returns $parent.
	 *
	 * @see cnCategory::$parent
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Sets $parent.
	 *
	 * @param object  $parent
	 * @see cnCategory::$parent
	 */
	public function setParent( $parent ) {
		$this->parent = $parent;
	}

	/**
	 * Returns $slug.
	 *
	 * @see cnCategory::$slug
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Sets $slug.
	 *
	 * @param object  $slug
	 * @see cnCategory::$slug
	 */
	public function setSlug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Returns $taxonomy.
	 *
	 * @see cnCategory::$taxonomy
	 */
	public function getTaxonomy() {
		return $this->taxonomy;
	}

	/**
	 * Sets $taxonomy.
	 *
	 * @param object  $taxonomy
	 * @see cnCategory::$taxonomy
	 */
	public function setTaxonomy( $taxonomy ) {
		$this->taxonomy = $taxonomy;
	}

	/**
	 * Returns $termGroup.
	 *
	 * @see cnCategory::$termGroup
	 */
	public function getTermGroup() {
		return $this->termGroup;
	}

	/**
	 * Sets $termGroup.
	 *
	 * @param object  $termGroup
	 * @see cnCategory::$termGroup
	 */
	public function setTermGroup( $termGroup ) {
		$this->termGroup = $termGroup;
	}

	/**
	 * Saves the category to the database via the cnTerm class.
	 *
	 * @return The success or error message.
	 */
	public function save() {
		global $connections;

		/*$terms = $connections->term->getTermChildrenBy('term_id', $this->parent, 'category');
		var_dump($this->parent);
		var_dump($terms);die;*/

		// If the category already exists, do not let it be created.
		if ( $terms = $connections->term->getTermChildrenBy( 'term_id', $this->parent, 'category' ) ) {
			foreach ( $terms as $term ) {
				if ( $this->name == $term->name ) return $connections->setErrorMessage( 'category_duplicate_name' );
			}
		}

		$attributes['slug'] = $this->slug;
		$attributes['description'] = $this->description;
		$attributes['parent'] = $this->parent;

		// Do not add the uncategorized category
		if ( strtolower( $this->name ) != 'uncategorized' ) {
			if ( $connections->term->addTerm( $this->name, 'category', $attributes ) ) {
				$connections->setSuccessMessage( 'category_added' );
			}
			else {
				$connections->setErrorMessage( 'category_add_failed' );
			}
		}
		else {
			$connections->setErrorMessage( 'category_add_uncategorized' );
		}
	}

	/**
	 * Updates the category to the database via the cnTerm class.
	 *
	 * @access public
	 * @since unknown
	 * @return (bool)
	 */
	public function update() {
		global $connections;
		$duplicate = FALSE;

		$attributes['name'] = $this->name;
		$attributes['slug'] = $this->slug;
		$attributes['parent']= $this->parent;
		$attributes['description'] = $this->description;

		// If the category already exists, do not let it be created.
		if ( $terms = $connections->term->getTermChildrenBy( 'term_id', $this->parent, 'category' ) ) {

			foreach ( $terms as $term ) {

				if ( $this->name == $term->name ) {
					if ( $this->id != $term->term_id ) $duplicate = TRUE;
					break;
				}
			}

			if ( $duplicate ) {
				$connections->setErrorMessage( 'category_duplicate_name' );
				return FALSE;
			}
		}

		// Make sure the category isn't being set to itself as a parent.
		if ( $this->id === $this->parent ) {

			$connections->setErrorMessage( 'category_self_parent' );
			return FALSE;
		}

		// Do not change the uncategorized category
		if ( $this->slug != 'uncategorized' ) {

			if ( $connections->term->updateTerm( $this->id, 'category', $attributes ) ) {

				$connections->setSuccessMessage( 'category_updated' );
				return TRUE;

			} else {

				$connections->setErrorMessage( 'category_update_failed' );
				return FALSE;
			}

		} else {
			$connections->setErrorMessage( 'category_update_uncategorized' );
			return FALSE;
		}

		// Shouldn't get here...
		return FALSE;
	}

	/**
	 * Deletes the category from the database via the cnTerm class.
	 *
	 * @return The success or error message.
	 */
	public function delete() {
		global $connections;

		// Do not delete the uncategorized category
		if ( $this->slug != 'uncategorized' ) {
			if ( $connections->term->deleteTerm( $this->id, $this->parent, 'category' ) ) {
				$connections->setSuccessMessage( 'category_deleted' );
			}
			else {
				$connections->setErrorMessage( 'category_delete_failed' );
			}
		}
		else {
			$connections->setErrorMessage( 'category_delete_uncategorized' );
		}
	}
}