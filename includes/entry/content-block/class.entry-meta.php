<?php

namespace Connections_Directory\Entry\Content_Block;

use cnOutput;
use cnTemplate;
use Connections_Directory\Entry\Content_Blocks;

/**
 * Class Entry_Meta
 *
 * @package Connections_Directory\Entry\Content_Block
 */
class Entry_Meta {

	/**
	 * @since 9.6
	 */
	public static function add() {

		$atts = array(
			'name'                => __( 'Entry Meta', 'connections' ),
			'render_callback'     => array( __CLASS__, 'render' ),
			'permission_callback' => array( __CLASS__, 'permission' ),
		);

		Content_Blocks::instance()->add( 'entry-meta', $atts );
	}

	/**
	 * @since 9.6
	 *
	 * @param array $block Content Block attributes.
	 *
	 * @return bool
	 */
	public static function permission( $block ) {

		return true;
	}

	/**
	 * Renders the Entry meta Content Block.
	 *
	 * @since 9.6
	 *
	 * @param cnOutput $entry
	 */
	public static function render( $entry ) {

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
			'Connections_Directory/Entry/Content_Block/Entry_Meta/Items',
			$items
		);

		if ( is_array( $items ) && 0 < count( $items ) ) {

			do_action(
				'Connections_Directory/Entry/Content_Block/Entry_Meta/Before',
				$entry
			);

			echo '<ul>';
			echo '<li>' . implode( '</li><li>', $items ) . '</li>';
			echo '</ul>';

			do_action(
				'Connections_Directory/Entry/Content_Block/Entry_Meta/After',
				$entry
			);
		}

	}
}
