<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use cnOutput;
use Connections_Directory\Content_Block;
use WP_Post;

/**
 * Class Recently_Viewed
 */
class Recently_Viewed extends Content_Block {

	/**
	 * @since 9.10
	 * @var string
	 */
	const ID = 'recently-viewed';

	/**
	 * Related constructor.
	 *
	 * @since 9.10
	 *
	 * @param $id
	 */
	public function __construct( $id ) {

		$atts = array(
			'context'             => 'single',
			'name'                => __( 'Recently Viewed Entries', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Recently Viewed', 'connections' ),
		);

		parent::__construct( $id, $atts );

		//$this->setProperties( $this->properties );
		$this->hooks();
	}

	/**
	 * @since 9.10
	 */
	private function hooks() {

		add_action(
			'Connections_Directory/Render/Template/Single_Entry/After',
			array( __CLASS__, 'entryIDJavaScriptObject' ),
			10,
			2
		);
	}

	/**
	 * Callback for the `Connections_Directory/Render/Template/Single_Entry/After` action.
	 *
	 * @since 9.10
	 *
	 * @param array    $atts
	 * @param cnOutput $entry
	 */
	public static function entryIDJavaScriptObject( $atts, $entry ) {

		/** @var WP_Post $post */
		$post = get_queried_object();

		$atts = array(
			'postID'  => $post->ID,
			'entryID' => $entry->getId(),
		);

		$atts = apply_filters(
			"Connections_Directory/Content_Block/Entry/Recently_Viewed/Attributes",
			$atts
		);

		$encoded = wp_json_encode( $atts );

		if ( false === $encoded ) {

			return;
		}

		wp_add_inline_script(
			'frontend',
			"var cnViewing = {$encoded};",
			'before'
		);
	}

	/**
	 * Render the Recently Viewed Content Block.
	 *
	 * @since 9.10
	 */
	public function content() {

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$settings = array(
			'limit' => 5,
		);

		$settings = apply_filters(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Attributes",
			$settings
		);

		$classNames = array(
			'cn-list',
			'cn-recently-viewed',
		);

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
			$settings
		);

		echo PHP_EOL . '<div class="' . implode( ' ', $classNames ) . '" id="' . 'recent-content-block-' . self::ID . '" data-limit="' . absint( $settings['limit'] ) . '" data-exclude="' . $entry->getId() . '"></div>' . PHP_EOL;

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
			$settings
		);
	}
}
