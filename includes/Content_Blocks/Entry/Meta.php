<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use Connections_Directory\Content_Block;
use Connections_Directory\Utility\_escape;

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
			'date_added'    => '<strong>' . __( 'Date Added', 'connections' ) . ':</strong> ' . $entry->getDateAdded(),
			'added_by'      => '<strong>' . __( 'Added By', 'connections' ) . ':</strong> ' . $entry->getAddedBy(),
			'date_modified' => '<strong>' . __( 'Modified On', 'connections' ) . ':</strong> ' . $entry->getFormattedTimeStamp(),
			'modified_by'   => '<strong>' . __( 'Modified By', 'connections' ) . ':</strong> ' . $entry->getEditedBy(),
			'id'            => '<strong>' . __( 'Entry ID', 'connections' ) . ':</strong> ' . $entry->getId(),
			'slug'          => '<strong>' . __( 'Entry Slug', 'connections' ) . ':</strong> ' . $entry->getSlug(),
			'visibility'    => '<strong>' . __( 'Visibility', 'connections' ) . ':</strong> ' . $entry->displayVisibilityType(),
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

			$html  = '<ul>';
			$html .= '<li>' . implode( '</li><li>', $items ) . '</li>';
			$html .= '</ul>';

			_escape::html( $html, true );

			do_action(
				"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
				$entry
			);
		}
	}
}
