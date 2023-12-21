<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Class Category
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Category extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-category';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'taxonomy',
	);

	/**
	 * Related constructor.
	 *
	 * @since 9.8
	 *
	 * @param $id
	 */
	public function __construct( $id ) {

		$atts = array(
			'context'             => 'single',
			'name'                => __( 'Related Entries by Category', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by Category', 'connections' ),
			'script_handle'       => 'Connections_Directory/Block/Carousel/Script',
			'style_handle'        => 'Connections_Directory/Block/Carousel/Style',
		);

		parent::__construct( $id, $atts );

		$this->setProperties( $this->properties );
	}
}
