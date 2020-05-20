<?php

namespace Connections_Directory\Entry\Content_Block;

use cnEntry;
use cnOutput;
use cnURL;
use Connections_Directory\Entry\Content_Blocks;

/**
 * Class Entry_Management
 *
 * @package Connections_Directory\Entry\Content_Block
 */
class Entry_Management {

	/**
	 * @since 9.6
	 */
	public static function add() {

		$atts = array(
			'name'                => __( 'Entry Management', 'connections' ),
			'render_callback'     => array( __CLASS__, 'render' ),
			'permission_callback' => array( __CLASS__, 'permission' ),
			'script'              => 'Connections_Directory/Entry/Content_Block/Entry_Management/Javascript',
			'style'               => 'wp-jquery-ui-dialog',
		);

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'registerScripts' ) );

		Content_Blocks::instance()->add( 'entry-management', $atts );
	}

	/**
	 * @since 9.6
	 *
	 * @param array $block Content Block attributes.
	 *
	 * @return bool
	 */
	public static function permission( $block ) {

		return current_user_can( 'connections_manage' );
	}

	/**
	 * Callback for the `wp_enqueue_scripts` action.
	 *
	 * Register the Content Block scripts. They will be enqueued via the `script` and `styles` Content Block attributes.
	 *
	 * @since 9.6
	 */
	public static function registerScripts() {

		// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$url = cnURL::makeProtocolRelative( CN_URL );

		wp_register_script(
			'Connections_Directory/Entry/Content_Block/Entry_Management/Javascript',
			$url . "assets/js/cn-entry-management{$min}.js",
			array( 'jquery-ui-dialog', 'wp-api-request' ),
			CN_CURRENT_VERSION,
			TRUE
		);
	}

	/**
	 * Renders the Custom Fields attached to the entry.
	 *
	 * @since 9.6
	 *
	 * @param cnOutput $entry
	 */
	public static function render( $entry ) {

		$actions = array();

		self::addEditAction( $actions, $entry );
		self::addDeleteAction( $actions, $entry );

		$actions = apply_filters(
			'Connections_Directory/Entry/Content_Block/Entry_Management/Actions',
			$actions
		);

		do_action(
			'Connections_Directory/Entry/Content_Block/Entry_Management/Before',
			$entry
		);

		echo '<ul>';
		echo '<li>' . implode( '</li><li>', $actions ) . '</li>';
		echo '</ul>';

		do_action(
			'Connections_Directory/Entry/Content_Block/Entry_Management/After',
			$entry
		);
	}

	/**
	 * @since 9.6
	 *
	 * @param array   $actions
	 * @param cnEntry $entry
	 *
	 * @return array
	 */
	private static function addEditAction( &$actions, $entry ) {

		$url = $entry->getEditPermalink();

		if ( 0 === strlen( $url ) ) {

			return $actions;
		}

		$actions['edit'] = '<a class="cn-edit-entry" href="' . esc_url( $url ) . '">' . __( 'Edit', 'connections' ) . '</a>';

		return $actions;
	}

	/**
	 * @since 9.6
	 *
	 * @param array   $actions
	 * @param cnEntry $entry
	 *
	 * @return array
	 */
	public static function addDeleteAction( &$actions, $entry ) {

		$url = $entry->getDeletePermalink( 'rest' );

		if ( 0 === strlen( $url ) ) {

			return $actions;
		}

		$actions['delete'] = '<a class="cn-rest-action cn-delete-entry" href="' . esc_url( $url ) . '">' . __( 'Delete', 'connections' ) . '</a>';

		return $actions;
	}
}
