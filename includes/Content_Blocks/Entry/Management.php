<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use cnURL;
use Connections_Directory\Content_Block;

/**
 * Class Entry_Management
 *
 * @package Connections_Directory\Content_Block
 */
class Management extends Content_Block {

	/**
	 * @since 9.7
	 * @var string
	 */
	const ID = 'entry-management';

	/**
	 * Entry_Management constructor.
	 *
	 * @since 9.7
	 *
	 * @param string $id
	 */
	public function __construct( $id ) {

		$atts = array(
			'name'                => __( 'Entry Management', 'connections' ),
			//'script_handle'       => 'Connections_Directory/Content_Block/Entry_Management/Javascript',
			//'style_handle'        => 'wp-jquery-ui-dialog',
			'permission_callback' => array( $this, 'permission' ),
		);

		parent::__construct( $id, $atts );

		if ( $this->isPermitted() ) {

			$this->set( 'script_handle', 'Connections_Directory/Content_Block/Entry_Management/Javascript' );
			$this->set( 'style_handle', 'wp-jquery-ui-dialog' );
		}

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'registerScripts' ) );
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
			'Connections_Directory/Content_Block/Entry_Management/Javascript',
			$url . "assets/js/cn-entry-management{$min}.js",
			array( 'jquery-ui-dialog', 'wp-api-request' ),
			CN_CURRENT_VERSION,
			TRUE
		);
	}

	/**
	 * Renders the Custom Metadata Fields attached to the entry.
	 *
	 * @since 9.6
	 */
	public function content() {

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$actions = array();

		self::addEditAction( $actions, $entry );
		self::addDeleteAction( $actions, $entry );

		$actions = apply_filters(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Actions",
			$actions,
			$entry
		);

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
			$entry
		);

		echo '<ul>';
		echo '<li>' . implode( '</li><li>', $actions ) . '</li>';
		echo '</ul>';

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
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
