<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use Connections_Directory\Content_Block;
use Connections_Directory\Utility\_escape;

/**
 * Class Recently_Viewed
 */
class Last_Viewed extends Recently_Viewed {

	/**
	 * @since 9.10
	 * @var string
	 */
	const ID = 'last-viewed';

	/**
	 * Related constructor.
	 *
	 * @since 9.10
	 *
	 * @param $id
	 *
	 * @noinspection PhpMissingParentConstructorInspection*/
	public function __construct( $id ) {

		$atts = array(
			'context'             => 'single',
			'name'                => __( 'Last Viewed Entry', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Last Viewed', 'connections' ),
			'script_handle'       => 'Connections_Directory/Content_Block/Recently_Viewed/Script',
		);

		/*
		 * In this case, do not call the parent constructor because we do not want the parent::hooks() method to run.
		 * But, we still to run the Content_Block constructor, so, lets call it directly.
		 */
		Content_Block::__construct( $id, $atts );
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

		$classNames = array(
			'cn-list',
			'cn-recently-viewed',
		);

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before"
		);

		echo PHP_EOL . '<div class="' . _escape::classNames( $classNames ) . '" id="' . _escape::id( 'recent-content-block-' . parent::ID ) . '" data-limit="1" data-exclude="' . absint( $entry->getId() ) . '"></div>' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		do_action(
			"Connections_Directory/Content_Block/Entry/{$this->shortName}/After"
		);
	}
}
