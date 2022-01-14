<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use cnFormatting;
use cnRetrieve;
use cnSanitize;
use cnTerm;
use Connections_Directory\Content_Block;
use Connections_Directory\Utility\_escape;
use function Connections_Directory\Taxonomy\Partial\getTermParents;

/**
 * Class Entry_Categories
 *
 * @package Connections_Directory\Content_Block
 */
class Taxonomy extends Content_Block {

	/**
	 * @since 10.2
	 * @var \Connections_Directory\Taxonomy
	 */
	private $taxonomy;

	/**
	 * Taxonomy constructor.
	 *
	 * @since 10.2
	 *
	 * @param string                          $id
	 * @param \Connections_Directory\Taxonomy $taxonomy
	 * @param array                           $atts
	 */
	public function __construct( $id, $taxonomy, $atts = array() ) {

		//$atts = array(
		//	'name'                => __( 'Entry Categories', 'connections' ),
		//	'register_option'     => false,
		//	'permission_callback' => array( $this, 'permission' ),
		//	'heading'             => __( 'Categories', 'connections' ),
		//);

		parent::__construct( $id, $atts );

		$this->taxonomy = $taxonomy;
		$this->setProperties( $this->defaults() );
	}

	/**
	 * @since 10.2
	 *
	 * @return array {
	 * Optional. An array of arguments.
	 *
	 *     @type string $container_tag    The HTML tag to be used for the Content Block container.
	 *                                    Default: div
	 *     @type string $label_tag        The HTML tag to be used for the taxonomy term label element.
	 *                                    Default: span
	 *     @type string $item_tag         The HTML tag to be used for the taxonomy term element.
	 *                                    Default: span
	 *     @type string $type             The display type to be used to display the taxonomy terms.
	 *                                    Accepts: block|list
	 *                                    Default: block
	 *     @type string $list             If the $type is list, which type?
	 *                                    Accepts: ordered|unordered
	 *                                    Default: unordered
	 *     @type string $label            The label to be displayed before the taxonomy terms.
	 *                                    Default: Categories:
	 *     @type string $separator        The taxonomy term separator used when separating taxonomy terms when $type == list
	 *                                    Default: ', '
	 *     @type string $parent_separator The separator to be used when displaying the taxonomy term's hierarchy.
	 *                                    Default: ' &raquo; '
	 *     @type bool   $link             Whether or not render the taxonomy terms as permalinks.
	 *                                    Default: false
	 *     @type bool   $parents          Whether or not to display the taxonomy term hierarchy.
	 *                                    Default: false
	 *     @type int    $child_of         The taxonomy term ID to retrieve child terms of.
	 *                                    If multiple taxonomies are passed, $child_of is ignored.
	 *                                    Default: 0
	 * }
	 */
	private function defaults() {

		$defaults = array(
			'container_tag'    => 'div',
			'label_tag'        => 'span',
			'item_tag'         => 'span',
			'type'             => 'block',
			'list'             => 'unordered',
			'label'            => $this->taxonomy->getLabels()->content_block_label_colon,
			'separator'        => ', ',
			'parent_separator' => ' &raquo; ',
			// 'before'           => '',
			// 'after'            => '',
			'link'             => false,
			'parents'          => false,
			'child_of'         => 0,
			// 'return'           => FALSE,
		);

		return apply_filters(
			"Connections_Directory/Content_Block/Entry/Taxonomy/{$this->taxonomy->getSlug()}/Attributes",
			$defaults
		);
	}

	/**
	 * @since 9.7
	 *
	 * @param string $property
	 * @param mixed  $value
	 */
	public function set( $property, $value ) {

		if ( in_array( $property, array( 'link', 'parents' ) ) ) {

			cnFormatting::toBoolean( $value );
		}

		parent::set( $property, $value );
	}

	/**
	 * @since 9.7
	 *
	 * @return bool
	 */
	public function permission() {

		return true;
	}

	/**
	 * @since 10.2
	 *
	 * @param cnEntry $entry
	 *
	 * @return array
	 */
	public function getTerms( $entry ) {

		$terms = cnRetrieve::entryTerms( $entry->getId(), $this->taxonomy->getSlug() );

		if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {

			if ( $this->get( 'child_of', 0 ) ) {

				$term_ids = wp_list_pluck( $terms, 'term_id' );

				if ( ! empty( $term_ids ) ) {

					$terms = cnTerm::getTaxonomyTerms(
						$this->taxonomy->getSlug(),
						array(
							'child_of' => $this->get( 'child_of', 0 ),
							'include'  => $term_ids,
						)
					);
				}
			}

			return $terms;
		}

		return array();
	}

	/**
	 * Displays the category list in a HTML list or custom format.
	 *
	 * NOTE: This is the Connections equivalent of @see get_the_category_list() in WordPress core ../wp-includes/category-template.php
	 *
	 * @since 10.2
	 */
	public function content() {

		global $wp_rewrite;

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$terms      = $this->getTerms( $entry );
		$properties = cnSanitize::args( $this->getProperties(), $this->defaults() );
		$count      = count( $terms );
		$html       = '';
		$label      = '';
		$items      = array();

		if ( empty( $terms ) ) {

			return;
		}

		if ( 'list' == $this->get( 'type' ) ) {

			$this->set( 'item_tag', 'li' );
		}

		if ( 0 < strlen( $this->get( 'label', '' ) ) ) {

			$label = sprintf(
				'<%1$s class="cn-term-label">%2$s</%1$s> ',
				_escape::tagName( $this->get( 'label_tag' ) ),
				esc_html( $this->get( 'label', '' ) )
			);
		}

		foreach ( $terms as $term ) {

			$text = '';

			if ( $this->get( 'parents' ) ) {

				// If the term is a root parent, skip.
				if ( 0 !== $term->parent ) {

					$text .= getTermParents(
						$term->parent,
						$this->taxonomy->getSlug(),
						array(
							'link'       => $this->get( 'link' ),
							'separator'  => $this->get( 'parent_separator' ),
							'force_home' => $entry->directoryHome['force_home'],
							'home_id'    => $entry->directoryHome['page_id'],
						)
					);
				}
			}

			if ( $this->get( 'link' ) ) {

				$rel = '';

				if ( 'category' === $this->taxonomy->getSlug() ) {

					$rel = is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ? 'rel="category tag"' : 'rel="category"';
				}

				$url = cnTerm::permalink(
					$term,
					$this->taxonomy->getSlug(),
					array(
						'force_home' => $entry->directoryHome['force_home'],
						'home_id'    => $entry->directoryHome['page_id'],
					)
				);

				$text .= '<a href="' . $url . '" ' . $rel . '>' . esc_html( $term->name ) . '</a>';

			} else {

				$text .= esc_html( $term->name );
			}

			$items[] = apply_filters(
				"Connections_Directory/Content_Block/Entry/Taxonomy/{$this->taxonomy->getSlug()}/Term/Item",
				sprintf(
					'<%1$s class="cn-term-name cn-term-%2$d">%3$s</%1$s>',
					_escape::tagName( $this->get( 'item_tag' ) ),
					$term->term_id,
					$text
				),
				$term,
				$count,
				$properties,
				$this
			);
		}

		/*
		 * Remove NULL, FALSE and empty strings (""), but leave values of 0 (zero).
		 * Filter our these in case someone hooks into the `cn_entry_output_category_item` filter and removes a category
		 * by returning an empty value.
		 */
		$items = array_filter( $items, 'strlen' );

		/**
		 * @since 8.6.12
		 */
		$items = apply_filters(
			"Connections_Directory/Content_Block/Entry/Taxonomy/{$this->taxonomy->getSlug()}/Term/Items",
			$items
		);

		if ( 'list' == $this->get( 'type' ) ) {

			$html .= sprintf(
				'<%1$s class="cn-term-list">%2$s</%1$s>',
				'unordered' === $this->get( 'list' ) ? 'ul' : 'ol',
				implode( '', $items )
			);

		} else {

			$separator = '<span class="cn-term-separator">' . esc_html( $this->get( 'separator' ) ) . '</span>';

			$html .= implode( $separator, $items );
		}

		do_action(
			"Connections_Directory/Content_Block/Entry/Taxonomy/{$this->taxonomy->getSlug()}/Term/Container/Before",
			$entry,
			$items
		);

		echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			"Connections_Directory/Content_Block/Entry/Taxonomy/{$this->taxonomy->getSlug()}/Term/Container",
			sprintf(
				'<%1$s class="cn-terms">%2$s</%1$s>' . PHP_EOL,
				_escape::tagName( $this->get( 'container_tag' ) ),
				$label . $html // Both `$label` and `$html` are escaped.
			),
			$properties
		);

		do_action(
			"Connections_Directory/Content_Block/Entry/Taxonomy/{$this->taxonomy->getSlug()}/Term/Container/After",
			$entry,
			$items
		);
	}
}
