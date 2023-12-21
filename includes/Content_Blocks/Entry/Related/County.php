<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Class County
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class County extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-county';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'county',
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
			'name'                => __( 'Related Entries by County', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by County', 'connections' ),
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

		// Add the county to the Content Block heading.
		add_filter(
			'Connections_Directory/Entry/Related/Query_Parameters',
			function ( $queryParameters ) {

				if ( is_array( $queryParameters ) && array_key_exists( 'county', $queryParameters ) ) {

					$this->set(
						'heading',
						/* translators: County. The largest territorial division for local government within a state of the U.S. */
						sprintf( __( 'Related by County - %s', 'connections' ), $queryParameters['county'] )
					);
				}

				return $queryParameters;
			}
		);
	}
}
