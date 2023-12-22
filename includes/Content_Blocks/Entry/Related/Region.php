<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Class Region
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Region extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-region';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'region',
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
			'name'                => __( 'Related Entries by State', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by State', 'connections' ),
			'script_handle'       => 'Connections_Directory/Block/Carousel/Script',
			'style_handle'        => 'Connections_Directory/Block/Carousel/Style',
		);

		parent::__construct( $id, $atts );

		$this->setProperties( $this->properties );
		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 9.8
	 */
	private function hooks() {

		// Add the region to the Content Block heading.
		add_filter(
			'Connections_Directory/Entry/Related/Query_Parameters',
			function ( $queryParameters ) {

				if ( is_array( $queryParameters ) && array_key_exists( 'state', $queryParameters ) ) {

					$this->set(
						'heading',
						/* translators: A region such as a state or province. */
						sprintf( __( 'Related by State - %s', 'connections' ), $queryParameters['state'] )
					);
				}

				return $queryParameters;
			}
		);
	}
}
