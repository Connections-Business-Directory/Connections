<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use Connections_Directory\Content_Block;

/**
 * Class Entry_Meta
 *
 * @package Connections_Directory\Content_Block
 */
class Meta extends Content_Block {

	/**
	 * @since 9.7
	 * @var string
	 */
	const ID = 'entry-meta';

	/**
	 * Entry_Meta constructor.
	 *
	 * @since 9.7
	 *
	 * @param string $id
	 */
	public function __construct( $id ) {

		$atts = array(
			'name'                => __( 'Entry Metadata', 'connections' ),
			'permission_callback' => array( $this, 'permission' ),
		);

		parent::__construct( $id, $atts );
	}

	/**
	 * @since 9.6
	 *
	 * @return bool
	 */
	public function permission() {

		return current_user_can( 'connections_manage' );
	}

	/**
	 * Renders the Entry meta Content Block.
	 *
	 * @since 9.6
	 */
	public function content() {

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$items = array(
			'date_added'    => '<strong>' . esc_html__( 'Date Added', 'connections' ) . ':</strong> ' . $entry->getDateAdded(),
			'added_by'      => '<strong>' . esc_html__( 'Added By', 'connections' ) . ':</strong> ' . $entry->getAddedBy(),
			'date_modified' => '<strong>' . esc_html__( 'Modified On', 'connections' ) . ':</strong> ' . $entry->getFormattedTimeStamp(),
			'modified_by'   => '<strong>' . esc_html__( 'Modified By', 'connections' ) . ':</strong> ' . $entry->getEditedBy(),
			'id'            => '<strong>' . esc_html__( 'Entry ID', 'connections' ) . ':</strong> ' . $entry->getId(),
			'slug'          => '<strong>' . esc_html__( 'Entry Slug', 'connections' ) . ':</strong> ' . $entry->getSlug(),
			'visibility'    => '<strong>' . esc_html__( 'Visibility', 'connections' ) . ':</strong> ' . $entry->displayVisibilityType(),
		);

		$items = apply_filters(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Items",
			$items,
			$entry
		);

		if ( is_array( $items ) && 0 < count( $items ) ) {

			do_action(
				"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
				$entry
			);

			echo '<ul>';
			echo '<li>' . implode( '</li><li>', $items ) . '</li>';
			echo '</ul>';

			do_action(
				"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
				$entry
			);
		}

	}
}
