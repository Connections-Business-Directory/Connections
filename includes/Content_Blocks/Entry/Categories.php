<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use cnFormatting;
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
class Categories extends Content_Block {

	/**
	 * The Content Block ID.
	 *
	 * @since 9.7
	 * @var string
	 */
	const ID = 'entry-categories';

	/**
	 * Entry_Categories constructor.
	 *
	 * @param string $id The Content Block ID.
	 */
	public function __construct( $id ) {

		$atts = array(
			'name'                => __( 'Entry Categories', 'connections' ),
			'register_option'     => false,
			'permission_callback' => array( $this, 'permission' ),
			'heading'             => __( 'Categories', 'connections' ),
		);

		parent::__construct( $id, $atts );

		$this->setProperties( $this->defaults() );
	}

	/**
	 * The default properties of the Content Block.
	 *
	 * @since 9.7
	 *
	 * @return array {
	 * Optional. An array of arguments.
	 *
	 *     @type string $container_tag    The HTML tag to be used for the container element.
	 *                                    Default: div
	 *     @type string $label_tag        The HTML tag to be used for the category label element.
	 *                                    Default: span
	 *     @type string $item_tag         The HTML tag to be used for the category element.
	 *                                    Default: span
	 *     @type string $type             The display type to be used to display the categories.
	 *                                    Accepts: block|list
	 *                                    Default: block
	 *     @type string $list             If the $type is list, which type?
	 *                                    Accepts: ordered|unordered
	 *                                    Default: unordered
	 *     @type string $label            The label to be displayed before the categories.
	 *                                    Default: Categories:
	 *     @type string $separator        The category separator used when separating categories when $type == list
	 *                                    Default: ', '
	 *     @type string $parent_separator The separator to be used when displaying the category's hierarchy.
	 *                                    Default: ' &raquo; '
	 *     @type bool   $link             Whether to render the categories as permalinks.
	 *                                    Default: false
	 *     @type bool   $parents          Whether to display the category hierarchy.
	 *                                    Default: false
	 *     @type int    $child_of         Term ID to retrieve child terms of.
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
			'label'            => __( 'Categories:', 'connections' ) . ' ',
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
			'Connections_Directory/Content_Block/Entry/Categories/Attributes',
			$defaults
		);
	}

	/**
	 * Set a Content Block property value by ID.
	 *
	 * @since 9.7
	 *
	 * @param string $property The property name.
	 * @param mixed  $value    The property value.
	 */
	public function set( $property, $value ) {

		if ( in_array( $property, array( 'link', 'parents' ) ) ) {

			cnFormatting::toBoolean( $value );
		}

		parent::set( $property, $value );
	}

	/**
	 * Callback for the `permission_callback` parameter.
	 *
	 * @since 9.7
	 *
	 * @return bool
	 */
	public function permission() {

		return true;
	}

	/**
	 * Displays the category list in an HTML list or custom format.
	 *
	 * NOTE: This is the Connections equivalent of @see get_the_category_list() in WordPress core ../wp-includes/category-template.php
	 *
	 * @since 9.7
	 */
	public function content() {

		global $wp_rewrite;

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$categories = $entry->getCategory( array( 'child_of' => $this->get( 'child_of' ) ) );
		$properties = cnSanitize::args( $this->getProperties(), $this->defaults() );
		$count      = count( $categories );
		$html       = '';
		$label      = '';
		$items      = array();

		if ( empty( $categories ) ) {

			return;
		}

		if ( 'list' == $this->get( 'type' ) ) {

			$this->set( 'item_tag', 'li' );
		}

		if ( 0 < strlen( $this->get( 'label' ) ) ) {

			$label = sprintf(
				'<%1$s class="cn_category_label">%2$s</%1$s> ',
				_escape::tagName( $this->get( 'label_tag' ) ),
				esc_html( $this->get( 'label' ) )
			);
		}

		$i = 1;

		foreach ( $categories as $category ) {

			$text = '';

			if ( $this->get( 'parents' ) ) {

				// If the term is a root parent, skip.
				if ( 0 !== $category->parent ) {

					$text .= getTermParents(
						$category->parent,
						'category',
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

				$rel = is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ? 'rel="category tag"' : 'rel="category"';

				$url = cnTerm::permalink(
					$category,
					'category',
					array(
						'force_home' => $entry->directoryHome['force_home'],
						'home_id'    => $entry->directoryHome['page_id'],
					)
				);

				$text .= '<a href="' . $url . '" ' . $rel . '>' . esc_html( $category->name ) . '</a>';

			} else {

				$text .= esc_html( $category->name );
			}

			$items[] = apply_filters(
				'cn_entry_output_category_item',
				sprintf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					_escape::tagName( $this->get( 'item_tag' ) ),
					// The `cn_category` class is named with an underscore for backward compatibility.
					_escape::classNames( "cn-category-name cn_category cn-category-{$category->term_id} cn-category-{$category->slug}" ),
					// `$text` is escaped.
					$text
				),
				$category,
				$count,
				$i,
				$properties,
				$this
			);

			$i++; // Increment here so the correct value is passed to the filter.
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
		$items = apply_filters( 'cn_entry_output_category_items', $items );

		if ( 'list' == $this->get( 'type' ) ) {

			$classNames = apply_filters( 'cn_entry_output_category_items_class', array( 'cn-category-list' ) );

			$html .= sprintf(
				'<%1$s class="%2$s">%3$s</%1$s>',
				'unordered' === $this->get( 'list' ) ? 'ul' : 'ol',
				_escape::classNames( $classNames ),
				implode( '', $items )
			);

		} else {

			$separator = '<span class="cn-category-separator">' . esc_html( $this->get( 'separator' ) ) . '</span>';

			$html .= implode( $separator, $items );
		}

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
			$entry,
			$items
		);

		echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'cn_entry_output_category_container',
			sprintf(
				'<%1$s class="cn-categories">%2$s</%1$s>' . PHP_EOL,
				_escape::tagName( $this->get( 'container_tag' ) ),
				$label . $html // Both `$label` and `$html` are escaped.
			),
			$properties
		);

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
			$entry,
			$items
		);

		// Restore default parameters.
		$this->setProperties( $this->defaults() );
	}
}
