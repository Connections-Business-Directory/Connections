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
	 * The cnFormatting class.
	 * @var cnFormatting
	 */
	private $format;

	/**
	 * The cnValidate class.
	 * @var cnValidate
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
	 * @param string  $description
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
	 * @return string
	 */
	public function getDescriptionBlock( $atts = array() ) {

		/** @var WP_Embed $wp_embed */
		global $wp_embed;

		$defaults = array(
			'container_tag' => 'div',
			'before'        => '',
			'after'         => '',
			'return'        => FALSE
		);

		$defaults = apply_filters( 'cn_output_default_atts_cat_desc' , $defaults );

		$atts = $this->validate->attributesArray( $defaults, $atts );

		$out = $wp_embed->run_shortcode( $this->getDescription() );

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
	 * @since  0.7.8
	 *
	 * @param  array  $atts [optional]
	 * @param  string $text [optional]
	 *
	 * @return string
	 */
	public function getExcerpt( $atts = array(), $text = '' ) {

		$defaults = array(
			'length' => apply_filters( 'cn_cat_excerpt_length', 55 ),
			'more'   => apply_filters( 'cn_cat_excerpt_more', '&hellip;' )
		);

		$atts = $this->validate->attributesArray( $defaults, $atts );

		$text = cnString::excerpt( empty( $text ) ? $this->getDescription() : $text, $atts );

		return apply_filters( 'cn_trim_cat_excerpt', $text );
	}

	/**
	 * Returns $id.
	 *
	 * @see cnCategory::$id
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * Sets $id.
	 *
	 * @param int  $id
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
	 * @param string  $name
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
	 * @param int  $parent
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
	 * @param string  $slug
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
	 * @return bool
	 */
	public function save() {

		$args = array(
			'slug'        => $this->slug,
			'description' => $this->description,
			'parent'      => $this->parent,
		);

		remove_filter( 'pre_term_description', 'wp_filter_kses' );
		$result = cnTerm::insert( $this->name, 'category', $args );

		if ( is_wp_error( $result ) ) {

			cnMessage::set( 'error', $result->get_error_message() );
			return FALSE;

		} else {

			cnMessage::set( 'success', 'category_added' );
			return TRUE;
		}

	}

	/**
	 * Updates the category to the database via the cnTerm class.
	 *
	 * @access public
	 * @since unknown
	 * @return bool
	 */
	public function update() {

		$args = array(
			'name'        => $this->name,
			'slug'        => $this->slug,
			'description' => $this->description,
			'parent'      => $this->parent,
		);

		// Make sure the category isn't being set to itself as a parent.
		if ( $this->id === $this->parent ) {

			cnMessage::set( 'error', 'category_self_parent' );
			return FALSE;
		}

		remove_filter( 'pre_term_description', 'wp_filter_kses' );
		$result = cnTerm::update( $this->id, 'category', $args );

		if ( is_wp_error( $result ) ) {

			cnMessage::set( 'error', $result->get_error_message() );
			return FALSE;

		} else {

			cnMessage::set( 'success', 'category_updated' );
			return TRUE;
		}

	}

	/**
	 * Deletes the category from the database via the cnTerm class.
	 *
	 * @return bool The success or error message.
	 */
	public function delete() {

		$default = get_option( 'cn_default_category' );

		if ( $this->id == $default ) {

			cnMessage::set( 'error', 'category_delete_default' );
			return FALSE;
		}

		$result = cnTerm::delete( $this->id, 'category' );

		if ( is_wp_error( $result ) ) {

			cnMessage::set( 'error', $result->get_error_message() );
			return FALSE;

		} else {

			cnMessage::set( 'success', 'category_deleted' );
			return TRUE;
		}

	}

	/**
	 * Returns the current category being viewed.
	 *
	 * @access public
	 * @since  8.5.18
	 * @static
	 *
	 * @return false|cnTerm_Object
	 */
	public static function getCurrent() {

		$current = FALSE;

		if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

			$slug = explode( '/', cnQuery::getVar( 'cn-cat-slug' ) );

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$current = end( $slug );

		} elseif ( $catIDs = cnQuery::getVar( 'cn-cat' ) ) {

			if ( is_array( $catIDs ) ) {

				// If value is a string, strip the white space and covert to an array.
				$catIDs = wp_parse_id_list( $catIDs );

				// Use the first element
				$current = reset( $catIDs );

			} else {

				$current = $catIDs;
			}

		}

		if ( ! empty( $current ) ) {

			if ( ctype_digit( (string) $current ) ) {

				$field = 'id';

			} else {

				$field = 'slug';
			}

			$current = cnTerm::getBy( $field, $current, 'category' );

			// cnTerm::getBy() can return NULL || an instance of WP_Error, so, lets check for that.
			if ( ! $current instanceof cnTerm_Object ) {

				$current = FALSE;
			}

		}

		return $current;
	}
}
